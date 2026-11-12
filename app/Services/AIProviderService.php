<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIProviderService
{
    /**
     * Deteksi provider dari API key dan ambil model yang tersedia.
     */
    public function detectAndListModels(string $apiKey): array
    {
        // Cek prefix key terlebih dahulu untuk akurasi dan kecepatan tinggi
        if (str_starts_with($apiKey, 'gsk_')) {
            $groq = $this->tryGroq($apiKey);
            if ($groq['detected']) return $groq;
        }
        if (str_starts_with($apiKey, 'nvapi-')) {
            $nvidia = $this->tryNvidia($apiKey);
            if ($nvidia['detected']) return $nvidia;
        }
        if (str_starts_with($apiKey, 'sk-ant-')) {
            $anthropic = $this->tryAnthropic($apiKey);
            if ($anthropic['detected']) return $anthropic;
        }
        if (str_starts_with($apiKey, 'AIza') || str_starts_with($apiKey, 'AQ.')) {
            $google = $this->tryGoogleAI($apiKey);
            if ($google['detected']) return $google;
        }
        if (str_starts_with($apiKey, 'sk-or-')) {
            $openrouter = $this->tryOpenRouter($apiKey);
            if ($openrouter['detected']) return $openrouter;
        }
        if (str_starts_with($apiKey, 'sk-')) {
            $openai = $this->tryOpenAI($apiKey);
            if ($openai['detected']) return $openai;
        }

        // Coba Google AI Studio / Gemini
        $google = $this->tryGoogleAI($apiKey);
        if ($google['detected']) return $google;

        // Coba OpenAI
        $openai = $this->tryOpenAI($apiKey);
        if ($openai['detected']) return $openai;

        // Coba Anthropic
        $anthropic = $this->tryAnthropic($apiKey);
        if ($anthropic['detected']) return $anthropic;

        // Coba Groq
        $groq = $this->tryGroq($apiKey);
        if ($groq['detected']) return $groq;

        // Coba NVIDIA NIM (hanya jika bukan key provider lain)
        if (!str_starts_with($apiKey, 'gsk_')) {
            $nvidia = $this->tryNvidia($apiKey);
            if ($nvidia['detected']) return $nvidia;
        }

        // Coba OpenRouter
        $openrouter = $this->tryOpenRouter($apiKey);
        if ($openrouter['detected']) return $openrouter;

        return [
            'detected' => false,
            'provider' => 'tidak_dikenal',
            'label' => 'Provider Tidak Dikenal',
            'models' => [],
            'error' => 'API key tidak dapat dikenali. Pastikan key valid.',
        ];
    }

    protected function tryGoogleAI(string $apiKey): array
    {
        try {
            $response = Http::timeout(10)->get(
                "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}"
            );

            if ($response->successful()) {
                $data = $response->json();
                $models = collect($data['models'] ?? [])
                    ->filter(function($m) {
                        $name = strtolower($m['name'] ?? '');
                        if (!str_contains($name, 'gemini')) return false;
                        if (!in_array('generateContent', $m['supportedGenerationMethods'] ?? [])) return false;
                        
                        // Buang model non-teks, eksperimental, dan yang diketahui tidak bekerja
                        $exclude = [
                            'nano', 'banana', 'vision', 'image', 'audio', 'video',
                            'tts', 'embedding', 'aqa', 'omni', 'computer', 'learnlm',
                            'native-audio', 'preview-tts',
                        ];
                        foreach ($exclude as $kw) {
                            if (str_contains($name, $kw)) return false;
                        }
                        
                        // Buang versi 3.7 dan 3.8 yang diketahui tidak bekerja
                        if (preg_match('/gemini-3\.(7|8)/', $name)) return false;
                        
                        return true;
                    })
                    ->map(function($m) {
                        $id = str_replace('models/', '', $m['name']);
                        $displayName = $m['displayName'] ?? $m['name'];
                        
                        if (str_contains(strtolower($id), 'pro')) {
                            $displayName .= ' (Pro / Limit Ketat)';
                        } elseif (str_contains(strtolower($id), 'flash')) {
                            $displayName .= ' (Free Tier / Stabil)';
                        }
                        
                        return [
                            'id' => $id,
                            'name' => $displayName,
                            'description' => $m['description'] ?? '',
                            'input_token_limit' => $m['inputTokenLimit'] ?? null,
                            'output_token_limit' => $m['outputTokenLimit'] ?? null,
                        ];
                    })
                    ->values()
                    ->toArray();

                return [
                    'detected' => true,
                    'provider' => 'google',
                    'label' => 'Google AI Studio (Gemini)',
                    'models' => $models,
                    'error' => null,
                ];
            }
        } catch (\Exception $e) {
            Log::debug('Google AI detection failed: ' . $e->getMessage());
        }

        return ['detected' => false];
    }

    protected function tryOpenAI(string $apiKey): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
                ->get('https://api.openai.com/v1/models');

            if ($response->successful()) {
                $data = $response->json();
                $models = collect($data['data'] ?? [])
                    ->filter(function($m) {
                        $id = strtolower($m['id'] ?? '');
                        
                        // Hanya terima model chat/reasoning yang berguna
                        $allowedPrefixes = ['gpt-4', 'gpt-3.5', 'chatgpt', 'o1', 'o3', 'o4'];
                        $isAllowed = false;
                        foreach ($allowedPrefixes as $prefix) {
                            if (str_starts_with($id, $prefix)) {
                                $isAllowed = true;
                                break;
                            }
                        }
                        if (!$isAllowed) return false;
                        
                        // Buang model non-teks / khusus
                        $exclude = [
                            'audio', 'realtime', 'transcribe', 'tts',
                            'dall-e', 'whisper', 'embedding', 'search',
                            'moderation', 'instruct',
                        ];
                        foreach ($exclude as $kw) {
                            if (str_contains($id, $kw)) return false;
                        }
                        
                        return true;
                    })
                    ->map(function($m) {
                        $id = $m['id'];
                        $displayName = $id;
                        
                        // Beri label tier
                        if (str_contains($id, 'gpt-4o-mini') || str_contains($id, 'gpt-3.5')) {
                            $displayName .= ' (Murah / Hemat)';
                        } elseif (str_contains($id, 'gpt-4o') || str_contains($id, 'gpt-4.1-mini') || str_contains($id, 'gpt-4.1-nano')) {
                            $displayName .= ' (Standar)';
                        } elseif (str_starts_with($id, 'o1') || str_starts_with($id, 'o3') || str_starts_with($id, 'o4')) {
                            $displayName .= ' (Reasoning / Pro)';
                        } elseif (str_contains($id, 'gpt-4.1')) {
                            $displayName .= ' (Pro)';
                        }
                        
                        return [
                            'id' => $id,
                            'name' => $displayName,
                            'description' => 'OpenAI ' . $id,
                        ];
                    })
                    ->sortBy('id')
                    ->values()
                    ->toArray();

                return [
                    'detected' => true,
                    'provider' => 'openai',
                    'label' => 'OpenAI',
                    'models' => $models,
                    'error' => null,
                ];
            }
        } catch (\Exception $e) {
            Log::debug('OpenAI detection failed: ' . $e->getMessage());
        }

        return ['detected' => false];
    }

    protected function tryAnthropic(string $apiKey): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                ])
                ->get('https://api.anthropic.com/v1/models');

            if ($response->successful()) {
                $data = $response->json();
                $models = collect($data['data'] ?? [])
                    ->map(fn($m) => [
                        'id' => $m['id'],
                        'name' => $m['display_name'] ?? $m['id'],
                        'description' => 'Anthropic ' . ($m['display_name'] ?? $m['id']),
                    ])
                    ->values()
                    ->toArray();

                return [
                    'detected' => true,
                    'provider' => 'anthropic',
                    'label' => 'Anthropic (Claude)',
                    'models' => $models,
                    'error' => null,
                ];
            }
        } catch (\Exception $e) {
            Log::debug('Anthropic detection failed: ' . $e->getMessage());
        }

        return ['detected' => false];
    }

    protected function tryOpenRouter(string $apiKey): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
                ->get('https://openrouter.ai/api/v1/models');

            if ($response->successful()) {
                $data = $response->json();
                $models = collect($data['data'] ?? [])
                    ->take(50)
                    ->map(fn($m) => [
                        'id' => $m['id'],
                        'name' => $m['name'] ?? $m['id'],
                        'description' => $m['description'] ?? '',
                    ])
                    ->values()
                    ->toArray();

                return [
                    'detected' => true,
                    'provider' => 'openrouter',
                    'label' => 'OpenRouter',
                    'models' => $models,
                    'error' => null,
                ];
            }
        } catch (\Exception $e) {
            Log::debug('OpenRouter detection failed: ' . $e->getMessage());
        }

        return ['detected' => false];
    }

    protected function tryGroq(string $apiKey): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
                ->get('https://api.groq.com/openai/v1/models');

            if ($response->successful()) {
                $data = $response->json();
                $models = collect($data['data'] ?? [])
                    ->map(fn($m) => [
                        'id' => $m['id'],
                        'name' => $m['id'],
                        'description' => 'Groq ' . $m['id'],
                    ])
                    ->sortBy('id')
                    ->values()
                    ->toArray();

                // Fallback custom model if the user specified one that isn't listed
                if (!collect($models)->contains('id', 'openai/gpt-oss-120b')) {
                    $models[] = [
                        'id' => 'openai/gpt-oss-120b',
                        'name' => 'openai/gpt-oss-120b',
                        'description' => 'Custom Model (Groq)'
                    ];
                }

                return [
                    'detected' => true,
                    'provider' => 'groq',
                    'label' => 'Groq',
                    'models' => $models,
                    'error' => null,
                ];
            }
        } catch (\Exception $e) {
            Log::debug('Groq detection failed: ' . $e->getMessage());
        }

        return ['detected' => false];
    }

    protected function tryNvidia(string $apiKey): array
    {
        if (str_starts_with($apiKey, 'gsk_') || str_starts_with($apiKey, 'sk-') || str_starts_with($apiKey, 'AIza') || str_starts_with($apiKey, 'AQ.')) {
            return ['detected' => false];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
                ->get('https://integrate.api.nvidia.com/v1/models');

            if ($response->successful()) {
                $data = $response->json();
                $models = collect($data['data'] ?? [])
                    ->map(fn($m) => [
                        'id' => $m['id'],
                        'name' => $m['id'],
                        'description' => 'NVIDIA ' . $m['id'],
                    ])
                    ->sortBy('id')
                    ->values()
                    ->toArray();

                // Pastikan deepseek-v4-pro tersedia
                if (!collect($models)->contains('id', 'deepseek-ai/deepseek-v4-pro')) {
                    $models[] = [
                        'id' => 'deepseek-ai/deepseek-v4-pro',
                        'name' => 'deepseek-ai/deepseek-v4-pro',
                        'description' => 'DeepSeek V4 Pro (NVIDIA)'
                    ];
                }

                return [
                    'detected' => true,
                    'provider' => 'nvidia',
                    'label' => 'NVIDIA NIM',
                    'models' => $models,
                    'error' => null,
                ];
            }
        } catch (\Exception $e) {
            Log::debug('NVIDIA detection failed: ' . $e->getMessage());
        }

        // Kalau gagal baca endpoint model API, tapi key di-force sebagai NVIDIA
        if (str_starts_with($apiKey, 'nvapi-')) {
            return [
                'detected' => true,
                'provider' => 'nvidia',
                'label' => 'NVIDIA NIM (Offline)',
                'models' => [
                    ['id' => 'deepseek-ai/deepseek-v4-pro', 'name' => 'deepseek-ai/deepseek-v4-pro', 'description' => 'DeepSeek V4 Pro'],
                    ['id' => 'meta/llama-3.1-405b-instruct', 'name' => 'meta/llama-3.1-405b-instruct', 'description' => 'Llama 3.1 405B'],
                ],
                'error' => null,
            ];
        }

        return ['detected' => false];
    }

    /**
     * Generate response menggunakan provider yang terdeteksi.
     */
    public function generate(string $provider, string $apiKey, string $model, string $prompt, bool $isJson = true): string
    {
        return match ($provider) {
            'google' => $this->generateGoogle($apiKey, $model, $prompt, $isJson),
            'openai' => $this->generateOpenAI($apiKey, $model, $prompt, $isJson),
            'anthropic' => $this->generateAnthropic($apiKey, $model, $prompt, $isJson),
            'openrouter' => $this->generateOpenRouter($apiKey, $model, $prompt, $isJson),
            'groq' => $this->generateGroq($apiKey, $model, $prompt, $isJson),
            'nvidia' => $this->generateNvidia($apiKey, $model, $prompt, $isJson),
            default => throw new \RuntimeException("Provider tidak didukung: {$provider}"),
        };
    }

    protected function generateGoogle(string $apiKey, string $model, string $prompt, bool $isJson = true): string
    {
        $maxTokens = max((int) env('AI_MAX_TOKENS', 4096), 4096);

        // Guard: model tidak boleh kosong
        if (empty(trim($model))) {
            $model = 'gemini-3.5-flash-lite';
            Log::warning('[generateGoogle] Model kosong terdeteksi, fallback ke gemini-3.5-flash-lite');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $headers = ['Content-Type' => 'application/json'];

        // Deteksi apakah ini OAuth Bearer Token (ya29.xxx atau panjang > 200 karakter)
        $isOAuth = str_starts_with($apiKey, 'ya29.') || strlen($apiKey) > 200;

        if ($isOAuth) {
            // Karena scope OAuth sekarang hanya untuk identitas (agar tidak ada warning unverified app),
            // kita gunakan Admin API Key (dari env) untuk melakukan generate aktual ke Gemini API.
            $adminApiKey = $this->getGoogleAdminApiKey();
            if ($adminApiKey) {
                $url .= "?key={$adminApiKey}";
            } else {
                Log::error('Google OAuth AI: tidak ada Admin API Key (GOOGLE_GEMINI_API_KEY) yang ditemukan.');
                throw new \RuntimeException('Sistem tidak memiliki Admin API Key untuk Google AI.');
            }
        } else {
            // API key biasa: tambahkan sebagai query param
            $url .= "?key={$apiKey}";
        }

        $generationConfig = [
            'temperature' => 0.5,
            'maxOutputTokens' => $maxTokens,
        ];

        if ($isJson) {
            $generationConfig['responseMimeType'] = 'application/json';
        }

        // Retry otomatis jika terkena 503 (Overloaded) atau 429 (Rate Limit)
        $response = Http::timeout(120)
            ->retry(3, 2000, function ($exception, $request) {
                if ($exception instanceof \Illuminate\Http\Client\RequestException && $exception->response) {
                    return in_array($exception->response->status(), [429, 500, 502, 503, 504]);
                }
                return true;
            }, throw: false)
            ->withHeaders($headers)
            ->post($url, [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => $generationConfig,
            ]);

        // Jika model spesifik overload (503) dan bukan gemini-3.5-flash-lite, coba fallback ke gemini-3.5-flash-lite yang stabil
        if (!$response->successful() && $response->status() === 503 && !str_contains($model, 'gemini-3.5-flash-lite')) {
            Log::warning("[Google AI] Model {$model} overloaded (503). Mencoba fallback otomatis ke gemini-3.5-flash-lite...");
            $fallbackUrl = preg_replace('/models\/[^:]+:/', 'models/gemini-3.5-flash-lite:', $url);
            $response = Http::timeout(120)
                ->withHeaders($headers)
                ->post($fallbackUrl, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => $generationConfig,
                ]);
        }

        if (!$response->successful()) {
            Log::error('Google AI Error (isOAuth=' . ($isOAuth ? 'true' : 'false') . '): ' . $response->body());
            throw new \RuntimeException('Google AI gagal: ' . $response->status());
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '{}';
    }

    /**
     * Ambil Google API key dari env atau admin config (database).
     * Digunakan untuk scope peruserquota yang memerlukan developer key + OAuth user token.
     */
    protected function getGoogleAdminApiKey(): ?string
    {
        // Prioritas 1: env khusus GOOGLE_GEMINI_API_KEY (Google AI Studio key)
        $envKey = env('GOOGLE_GEMINI_API_KEY', '');
        if ($envKey && (str_starts_with($envKey, 'AIza') || str_starts_with($envKey, 'AQ.') || strlen($envKey) > 30)) {
            return $envKey;
        }

        // Prioritas 2: env GEMINI_API_KEY jika berformat Google (AIza)
        $geminiKey = env('GEMINI_API_KEY', '');
        if ($geminiKey && str_starts_with($geminiKey, 'AIza')) {
            return $geminiKey;
        }

        // Prioritas 3: cari dari admin config di database
        try {
            $configs = \App\Models\AiConfiguration::where('is_active', true)
                ->where(function($q) {
                    $q->where('provider', 'google')
                      ->orWhere('detected_provider', 'google');
                })
                ->orderByDesc('is_default')
                ->get();

            foreach ($configs as $config) {
                $key = $config->api_key;
                // Google AI Studio key selalu dimulai dengan 'AIza' atau 'AQ.'
                if ($key && (str_starts_with($key, 'AIza') || str_starts_with($key, 'AQ.'))) {
                    return $key;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Gagal ambil Google admin API key dari DB: ' . $e->getMessage());
        }

        return null;
    }


    /**
     * Public accessor untuk getGoogleAdminApiKey (digunakan oleh GeminiService).
     */
    public function getGoogleAdminApiKeyPublic(): ?string
    {
        return $this->getGoogleAdminApiKey();
    }

    protected function generateOpenAI(string $apiKey, string $model, string $prompt, bool $isJson = true): string
    {
        $maxTokens = (int) env('AI_MAX_TOKENS', 2048);
        $payload = [
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.5,
            'max_tokens' => $maxTokens,
        ];
        if ($isJson) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::timeout(120)
            ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->post('https://api.openai.com/v1/chat/completions', $payload);
        if (!$response->successful()) throw new \RuntimeException('OpenAI gagal: ' . $response->status());
        return $response->json('choices.0.message.content') ?? '{}';
    }

    protected function generateAnthropic(string $apiKey, string $model, string $prompt, bool $isJson = true): string
    {
        $maxTokens = (int) env('AI_MAX_TOKENS', 2048);
        $promptText = $isJson ? $prompt . "\n\nIMPORTANT: Return valid JSON only." : $prompt;
        
        $response = Http::timeout(120)
            ->withHeaders(['x-api-key' => $apiKey, 'anthropic-version' => '2023-06-01', 'content-type' => 'application/json'])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => $maxTokens,
                'messages' => [['role' => 'user', 'content' => $promptText]],
            ]);
        if (!$response->successful()) throw new \RuntimeException('Anthropic gagal: ' . $response->status());
        return $response->json('content.0.text') ?? '{}';
    }

    protected function generateOpenRouter(string $apiKey, string $model, string $prompt, bool $isJson = true): string
    {
        $maxTokens = (int) env('AI_MAX_TOKENS', 2048);
        $payload = [
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.5,
            'max_tokens' => $maxTokens,
        ];

        $response = Http::timeout(120)
            ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->post('https://openrouter.ai/api/v1/chat/completions', $payload);
        if (!$response->successful()) throw new \RuntimeException('OpenRouter gagal: ' . $response->status());
        return $response->json('choices.0.message.content') ?? '{}';
    }

    protected function generateGroq(string $apiKey, string $model, string $prompt, bool $isJson = true): string
    {
        $maxTokens = (int) env('AI_MAX_TOKENS', 2048);
        
        // Safety check for tokens
        $estimatedTokens = (int) ceil(strlen($prompt) / 4);
        
        if ($estimatedTokens + $maxTokens > 8000) {
            $allowedPromptTokens = 8000 - $maxTokens;
            $allowedChars = $allowedPromptTokens * 4;
            $prompt = mb_substr($prompt, 0, $allowedChars) . "\n\n[SYSTEM: CONTEXT TRUNCATED DUE TO TOKEN LIMIT]";
            $estimatedTokens = $allowedPromptTokens;
            
            Log::warning('Groq Token Truncation', [
                'Original_Estimated_Tokens' => ceil(strlen($prompt) / 4),
                'New_Estimated_Tokens' => $estimatedTokens,
                'Max_Tokens' => $maxTokens
            ]);
        }
        
        Log::info('Groq Request Debug', [
            'Model' => $model,
            'Max_Tokens' => $maxTokens,
            'Prompt_Chars' => strlen($prompt),
            'Estimated_Tokens' => $estimatedTokens,
        ]);

        $payload = [
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.5,
            'max_tokens' => $maxTokens,
        ];
        
        if ($isJson) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::timeout(120)
            ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->post('https://api.groq.com/openai/v1/chat/completions', $payload);
            
        if (!$response->successful() && $isJson) {
            // Jika json_object tidak didukung model ini, coba tanpa response_format
            $response = Http::timeout(120)
                ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [['role' => 'user', 'content' => $prompt . "\n\nIMPORTANT: Return valid JSON only. Do not wrap in markdown blocks. Just raw JSON."]],
                    'temperature' => 0.5,
                    'max_tokens' => $maxTokens,
                ]);
        }
        
        if (!$response->successful()) throw new \RuntimeException('Groq gagal: ' . $response->body());
        
        $text = $response->json('choices.0.message.content') ?? '{}';
        // Clean markdown backticks if any
        if ($isJson) {
            $text = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $text);
            return preg_replace('/```\s*(.*?)\s*```/s', '$1', $text);
        }
        return $text;
    }

    protected function generateNvidia(string $apiKey, string $model, string $prompt, bool $isJson = true): string
    {
        $maxTokens = (int) env('AI_MAX_TOKENS', 16384);
        
        $payload = [
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 1,
            'top_p' => 0.95,
            'max_tokens' => $maxTokens,
            'stream' => false,
        ];
        
        // Deepseek specific kwargs as requested
        if (str_contains($model, 'deepseek')) {
            $payload['chat_template_kwargs'] = ['thinking' => false];
        }
        
        if ($isJson) {
            $payload['messages'][0]['content'] .= "\n\nIMPORTANT: Return valid JSON only.";
        }

        $response = Http::timeout(300)
            ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->post('https://integrate.api.nvidia.com/v1/chat/completions', $payload);
            
        if (!$response->successful()) throw new \RuntimeException('NVIDIA gagal: ' . $response->body());
        
        $text = $response->json('choices.0.message.content') ?? '{}';
        
        if ($isJson) {
            $text = preg_replace('/```json\s*(.*?)\s*```/s', '$1', $text);
            return preg_replace('/```\s*(.*?)\s*```/s', '$1', $text);
        }
        
        return $text;
    }
}
