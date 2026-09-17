<?php

namespace App\Services;

use App\Models\AiConfiguration;
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
     * Generate response menggunakan AI provider aktif.
     * Mendukung multi-provider: Google, OpenAI, Anthropic, OpenRouter.
     */
    public function generate(string $prompt, ?string $modelOverride = null, bool $isJson = true): string
    {
        // Cek apakah ada konfigurasi AI aktif di database
        $config = AiConfiguration::getActiveWithModel();

        if ($config) {
            $provider = $config->detected_provider ?? $config->provider;
            $apiKey = $config->api_key;
            $model = $modelOverride ?? $config->selected_model;

            try {
                return $this->aiProviderService->generate($provider, $apiKey, $model, $prompt, $isJson);
            } catch (\Exception $e) {
                Log::error("AI provider {$provider} gagal: " . $e->getMessage());
                // Fallback ke env default
            }
        }

        // Fallback: gunakan Gemini dari .env
        return $this->generateFromEnv($prompt, $modelOverride, $isJson);
    }

    protected function generateFromEnv(string $prompt, ?string $modelOverride = null, bool $isJson = true): string
    {
        $apiKey = env('GEMINI_API_KEY', '');
        $model = $modelOverride ?? env('GEMINI_MODEL', 'gemini-1.5-pro');

        if (str_starts_with($apiKey, 'gsk_')) {
            return $this->aiProviderService->generate('groq', $apiKey, $model, $prompt, $isJson);
        }

        if (str_starts_with($apiKey, '$NVIDIA') || str_starts_with($apiKey, 'nvapi-')) {
            return $this->aiProviderService->generate('nvidia', $apiKey, $model, $prompt, $isJson);
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
        
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

    /**
     * Ambil daftar model yang tersedia untuk dipilih user.
     */
    public function getAvailableModels(): array
    {
        $models = [];

        // Dari database configurations (Model yang di-inputkan admin)
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

        // Dari .env (selalu tersedia)
        $envKey = env('GEMINI_API_KEY');
        if ($envKey) {
            $envModel = env('GEMINI_MODEL', 'gemini-1.5-pro');
            // Hindari duplikasi jika admin sudah menambahkan model yang sama
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
