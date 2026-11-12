<?php

namespace App\Services;

use App\Models\AiConfiguration;
use App\Models\UserAiConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected AIProviderService $aiProviderService;

    public function __construct(AIProviderService $aiProviderService)
    {
        $this->aiProviderService = $aiProviderService;
    }

    /**
     * Generate response menggunakan AI provider.
     * Prioritas: User config → Admin config (DB) → .env fallback.
     */
    public function generate(string $prompt, ?string $modelOverride = null, bool $isJson = true, ?int $userId = null): string
    {
        $modelOverride = !empty($modelOverride) ? trim($modelOverride) : null;

        // 1. PRIORITAS PERTAMA: Config AI milik user sendiri
        if ($userId) {
            $userConfig = UserAiConfig::getActiveForUser($userId);

            if ($userConfig) {
                try {
                    // Refresh OAuth token kalau expired
                    if ($userConfig->method === 'oauth_google' && $userConfig->isTokenExpired()) {
                        $userConfig = $this->refreshGoogleToken($userConfig);
                    } elseif ($userConfig->method === 'oauth_openai' && $userConfig->isTokenExpired()) {
                        $userConfig = $this->refreshOpenAIToken($userConfig);
                    }

                    $apiKey = $userConfig->getActiveKey();
                    $provider = $userConfig->provider;
                    // Pastikan model tidak pernah string kosong (bisa terjadi jika selected_model = "")
                    $rawModel = !empty($modelOverride) ? trim($modelOverride) : (trim($userConfig->selected_model ?? '') ?: null);
                    $model = $rawModel ?: (($provider === 'google') ? 'gemini-3.5-flash-lite' : 'openai/gpt-oss-120b');

                    if ($apiKey) {
                        // Track usage
                        $userConfig->increment('total_requests');
                        $userConfig->update(['last_used_at' => now()]);

                        Log::info('AI Request via User Config', [
                            'user_id' => $userId,
                            'provider' => $provider,
                            'model' => $model,
                            'method' => $userConfig->method,
                        ]);

                        return $this->aiProviderService->generate($provider, $apiKey, $model, $prompt, $isJson);
                    }
                } catch (\Throwable $e) {
                    Log::warning("User AI config gagal (user_id: {$userId}), mencoba fallback: " . $e->getMessage());
                    // Lanjutkan ke fallback berikutnya agar analisis tetap selesai
                }
            }
        }

        // 2. PRIORITAS KEDUA: Config dari admin (database)
        $config = AiConfiguration::getActiveWithModel();

        if ($config) {
            $provider = $config->detected_provider ?? $config->provider;
            $apiKey = $config->api_key;
            $model = (!empty($modelOverride)) ? $modelOverride : $config->selected_model;

            // Jika fallback ke provider admin tapi modelOverride berasal dari provider lain:
            if ($provider === 'groq' && (empty($model) || str_contains(strtolower($model), 'gemini'))) {
                $model = $config->selected_model ?: 'openai/gpt-oss-120b';
            } elseif ($provider === 'google' && (empty($model) || !str_contains(strtolower($model), 'gemini'))) {
                $model = $config->selected_model ?: 'gemini-3.5-flash-lite';
            }

            try {
                return $this->aiProviderService->generate($provider, $apiKey, $model, $prompt, $isJson);
            } catch (\Throwable $e) {
                Log::warning("AI provider {$provider} gagal, mencoba fallback ke .env: " . $e->getMessage());
                // Lanjutkan ke fallback .env
            }
        }

        // 3. FALLBACK TERAKHIR: gunakan key dari .env
        return $this->generateFromEnv($prompt, $modelOverride, $isJson);
    }

    protected function generateFromEnv(string $prompt, ?string $modelOverride = null, bool $isJson = true): string
    {
        $modelOverride = !empty($modelOverride) ? trim($modelOverride) : null;
        $apiKey = env('GEMINI_API_KEY', '');
        $defaultModel = env('GEMINI_MODEL', 'gemini-3.5-flash-lite');
        $model = (!empty($modelOverride)) ? $modelOverride : $defaultModel;

        // Deteksi provider berdasarkan key
        $isGroqKey   = str_starts_with($apiKey, 'gsk_');
        $isNvidiaKey = str_starts_with($apiKey, 'nvapi-') || str_starts_with($apiKey, '$NVIDIA');
        $isGeminiModel = str_contains(strtolower($model), 'gemini');

        // Jika model adalah Gemini tapi key adalah Groq/NVIDIA → coba admin Google key dulu
        if ($isGeminiModel && ($isGroqKey || $isNvidiaKey)) {
            $googleKey = env('GOOGLE_GEMINI_API_KEY', '');
            if (!empty($googleKey)) {
                try {
                    $geminiModel = in_array($model, ['gemini-1.5-flash', 'gemini-2.5-flash']) ? 'gemini-3.5-flash-lite' : $model;
                    return $this->aiProviderService->generate('google', $googleKey, $geminiModel, $prompt, $isJson);
                } catch (\Throwable $e) {
                    Log::warning('[GeminiService] Google fallback dengan GOOGLE_GEMINI_API_KEY gagal: ' . $e->getMessage());
                }
            }
            // Coba dari admin DB config (Google)
            try {
                $googleAdminKey = $this->aiProviderService->getGoogleAdminApiKeyPublic();
                if ($googleAdminKey) {
                    $geminiModel = in_array($model, ['gemini-1.5-flash', 'gemini-2.5-flash']) ? 'gemini-3.5-flash-lite' : $model;
                    return $this->aiProviderService->generate('google', $googleAdminKey, $geminiModel, $prompt, $isJson);
                }
            } catch (\Throwable $e) {
                Log::warning('[GeminiService] Google DB fallback gagal: ' . $e->getMessage());
            }
            // Tidak ada Google key yang tersedia → fallback model Groq default
            $model = 'openai/gpt-oss-120b';
            Log::warning("[GeminiService] Model Gemini ({$modelOverride}) tidak bisa diakses. Fallback ke Groq dengan model default: {$model}");
        }

        if ($isGroqKey) {
            $groqModel = (!empty($model) && !str_contains(strtolower($model), 'gemini')) ? $model : 'openai/gpt-oss-120b';
            return $this->aiProviderService->generate('groq', $apiKey, $groqModel, $prompt, $isJson);
        }

        if ($isNvidiaKey) {
            return $this->aiProviderService->generate('nvidia', $apiKey, $model, $prompt, $isJson);
        }

        // Panggil Google direct HANYA jika key berformat Google
        if (!empty($apiKey) && (str_starts_with($apiKey, 'AIza') || str_starts_with($apiKey, 'AQ.'))) {
            $googleModel = (!empty($model) && str_contains(strtolower($model), 'gemini') && !in_array($model, ['gemini-1.5-flash', 'gemini-2.5-flash']))
                ? $model
                : 'gemini-3.5-flash-lite';
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$googleModel}:generateContent?key={$apiKey}";
            
            $generationConfig = [
                'temperature' => 0.1,
                'maxOutputTokens' => 8192,
            ];
            
            if ($isJson) {
                $generationConfig['responseMimeType'] = 'application/json';
            }

            $response = Http::timeout(120)->post($url, [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => $generationConfig,
            ]);

            if (!$response->successful()) {
                Log::error('Gemini API error: ' . $response->body());
                throw new \RuntimeException('Gemini API gagal: ' . $response->status());
            }

            return $response->json('candidates.0.content.parts.0.text') ?? '{}';
        }

        throw new \RuntimeException('Tidak ada API Key AI yang valid untuk memproses permintaan.');
    }


    /**
     * Refresh Google OAuth token yang expired.
     */
    public function refreshGoogleToken(UserAiConfig $config): UserAiConfig
    {
        if (!$config->oauth_refresh_token) {
            throw new \RuntimeException('Tidak ada refresh token untuk Google OAuth.');
        }

        $clientId = config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID');
        $clientSecret = config('services.google.client_secret') ?: env('GOOGLE_CLIENT_SECRET');

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $config->oauth_refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            Log::error('Google OAuth refresh gagal: ' . $response->body());
            throw new \RuntimeException('Gagal refresh Google OAuth token.');
        }

        $data = $response->json();

        $config->update([
            'oauth_access_token' => $data['access_token'],
            'oauth_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
            'last_verified_at' => now(),
        ]);

        return $config->fresh();
    }

    /**
     * Refresh OpenAI OAuth token yang expired.
     */
    protected function refreshOpenAIToken(UserAiConfig $config): UserAiConfig
    {
        if (!$config->oauth_refresh_token) {
            throw new \RuntimeException('Tidak ada refresh token untuk OpenAI OAuth.');
        }

        $response = Http::asForm()->post('https://auth.openai.com/oauth/token', [
            'client_id' => env('OPENAI_OAUTH_CLIENT_ID'),
            'client_secret' => env('OPENAI_OAUTH_CLIENT_SECRET'),
            'refresh_token' => $config->oauth_refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            Log::error('OpenAI OAuth refresh gagal: ' . $response->body());
            throw new \RuntimeException('Gagal refresh OpenAI OAuth token.');
        }

        $data = $response->json();

        $config->update([
            'oauth_access_token' => $data['access_token'],
            'oauth_refresh_token' => $data['refresh_token'] ?? $config->oauth_refresh_token,
            'oauth_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
            'last_verified_at' => now(),
        ]);

        return $config->fresh();
    }

    /**
     * Ambil daftar model yang tersedia.
     * Termasuk model dari user config jika $userId diberikan.
     */
    public function getAvailableModels(?int $userId = null): array
    {
        $models = [];

        // Model dari user config sendiri (prioritas tampil pertama)
        if ($userId) {
            $userConfig = UserAiConfig::getActiveForUser($userId);
            if ($userConfig && !empty($userConfig->available_models)) {
                foreach ($userConfig->available_models as $m) {
                    $models[] = [
                        'id' => $m['id'],
                        'name' => ($userConfig->provider_label ?? ucfirst($userConfig->provider)) . ' — ' . ($m['name'] ?? $m['id']),
                        'provider' => $userConfig->provider,
                        'source' => 'user',
                    ];
                }
            }
        }

        // Model dari database admin configurations
        $configs = AiConfiguration::where('is_active', true)->orderByDesc('is_default')->get();
        foreach ($configs as $config) {
            $models[] = [
                'id' => $config->selected_model,
                'name' => $config->label . ' (' . $config->selected_model . ')',
                'provider' => $config->detected_provider ?? $config->provider,
                'source' => 'db',
                'config_id' => $config->id,
            ];
        }

        // Model dari .env (selalu tersedia)
        $envKey = env('GEMINI_API_KEY');
        if ($envKey) {
            $envModel = env('GEMINI_MODEL', 'gemini-1.5-pro');
            $exists = collect($models)->contains('id', $envModel);
            if (!$exists) {
                $models[] = [
                    'id' => $envModel,
                    'name' => 'System Default (' . $envModel . ')',
                    'provider' => str_starts_with($envKey, 'gsk_') ? 'groq' : (str_starts_with($envKey, '$NVIDIA') || str_starts_with($envKey, 'nvapi-') ? 'nvidia' : 'google'),
                    'source' => 'env',
                ];
            }
        }

        return $models;
    }
}
