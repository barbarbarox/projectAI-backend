<?php

namespace App\Http\Controllers;

use App\Models\UserAiConfig;
use App\Services\AIProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserAiConfigController extends Controller
{
    /**
     * Halaman pengaturan AI user.
     */
    public function index()
    {
        $user = Auth::user();
        $config = UserAiConfig::getActiveForUser($user->id);

        return view('profile.ai-config', [
            'user' => $user,
            'config' => $config,
        ]);
    }

    /**
     * Auto-detect provider dari API key.
     */
    public function detectKey(Request $request, AIProviderService $aiProvider)
    {
        $request->validate(['api_key' => 'required|string|min:10']);

        $result = $aiProvider->detectAndListModels($request->api_key);

        return response()->json($result);
    }

    /**
     * Simpan konfigurasi AI user (API Key).
     */
    public function store(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string|min:10',
            'provider' => 'required|string|in:google,openai,anthropic,nvidia,groq,openrouter',
            'selected_model' => 'required|string',
            'available_models' => 'nullable|string', // JSON string
            'label' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();

        // Deactivate existing config
        UserAiConfig::where('user_id', $user->id)->update(['is_active' => false]);

        // Create new active config
        $config = UserAiConfig::create([
            'user_id' => $user->id,
            'method' => 'api_key',
            'provider' => $request->provider,
            'label' => $request->label ?: $this->generateLabel($request->provider),
            'api_key' => $request->api_key,
            'selected_model' => $request->selected_model,
            'available_models' => $request->available_models ? json_decode($request->available_models, true) : null,
            'is_active' => true,
            'last_verified_at' => now(),
        ]);

        return redirect()->route('profile.ai')->with('success', 'Konfigurasi AI berhasil disimpan! Model: ' . $config->selected_model);
    }

    /**
     * Hapus konfigurasi AI user → kembali ke admin default.
     */
    public function destroy()
    {
        $user = Auth::user();
        UserAiConfig::where('user_id', $user->id)->delete();

        return redirect()->route('profile.ai')->with('success', 'Konfigurasi AI dihapus. Menggunakan model default sistem.');
    }

    /**
     * Redirect ke Google OAuth untuk AI access.
     */
    public function redirectGoogleAI(Request $request)
    {
        $clientId = config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID');

        if (empty($clientId)) {
            return redirect()->route('profile.ai')->with('error', 'GOOGLE_CLIENT_ID belum dikonfigurasi di server.');
        }

        $request->session()->put('google_ai_csrf', $state = \Illuminate\Support\Str::random(40));

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => url('/profil/ai/oauth/google/callback'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    }

    /**
     * Handle Google OAuth callback untuk AI.
     */
    public function handleGoogleAICallback(Request $request)
    {
        // Verify CSRF state
        if ($request->state !== $request->session()->pull('google_ai_csrf')) {
            return redirect()->route('profile.ai')->with('error', 'Invalid OAuth state. Silakan coba lagi.');
        }

        if ($request->has('error')) {
            return redirect()->route('profile.ai')->with('error', 'Google OAuth dibatalkan: ' . $request->error);
        }

        try {
            $clientId = config('services.google.client_id') ?: env('GOOGLE_CLIENT_ID');
            $clientSecret = config('services.google.client_secret') ?: env('GOOGLE_CLIENT_SECRET');

            // Exchange code for tokens
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $request->code,
                'redirect_uri' => url('/profil/ai/oauth/google/callback'),
                'grant_type' => 'authorization_code',
            ]);

            if (!$tokenResponse->successful()) {
                Log::error('Google AI OAuth token exchange gagal: ' . $tokenResponse->body());
                return redirect()->route('profile.ai')->with('error', 'Gagal mendapatkan token dari Google.');
            }

            $tokens = $tokenResponse->json();
            $accessToken = $tokens['access_token'];
            $refreshToken = $tokens['refresh_token'] ?? null;
            $expiresIn = $tokens['expires_in'] ?? 3600;

            // Gunakan Admin API Key untuk mengambil daftar model, bukan token OAuth user (karena scope OAuth sekarang hanya untuk identitas login)
            $adminApiKey = env('GOOGLE_GEMINI_API_KEY') ?: \App\Models\AiConfiguration::where('provider', 'google')->value('api_key');
            
            if ($adminApiKey) {
                $modelsResponse = Http::timeout(15)->get("https://generativelanguage.googleapis.com/v1beta/models?key={$adminApiKey}");
            } else {
                // Fallback kosong agar gagal dan menggunakan hardcoded models
                $modelsResponse = Http::response([], 401);
            }

            // Daftar model Gemini stabil yang selalu bisa dipakai
            $fallbackModels = [
                ['id' => 'gemini-3.5-flash', 'name' => 'Gemini 3.5 Flash (Terbaru)'],
                ['id' => 'gemini-2.5-flash', 'name' => 'Gemini 2.5 Flash (Stabil)'],
                ['id' => 'gemini-2.5-pro', 'name' => 'Gemini 2.5 Pro (Pro / Akurat)'],
                ['id' => 'gemini-3.6-flash', 'name' => 'Gemini 3.6 Flash'],
                ['id' => 'gemini-3.7-flash', 'name' => 'Gemini 3.7 Flash'],
                ['id' => 'gemini-3.8-flash', 'name' => 'Gemini 3.8 Flash'],
            ];

            $availableModels = [];
            $defaultModel = 'gemini-3.5-flash';

            if ($modelsResponse->successful()) {
                $modelsData = $modelsResponse->json();
                $availableModels = collect($modelsData['models'] ?? [])
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
                        ];
                    })
                    ->values()
                    ->toArray();

                if (!empty($availableModels)) {
                    // Pilih model terbaik yang tersedia
                    $preferred = collect($availableModels)->first(fn($m) => str_contains($m['id'], '3.5'))
                        ?? collect($availableModels)->first(fn($m) => str_contains($m['id'], '2.5'))
                        ?? $availableModels[0];
                    $defaultModel = $preferred['id'];
                } else {
                    // Fallback ke daftar stabil jika list dari API kosong setelah filter
                    $availableModels = $fallbackModels;
                }
            } else {
                Log::warning('Gagal mengambil daftar model Google dengan Admin API Key (menggunakan fallback)');
                $availableModels = $fallbackModels;
            }

            $user = Auth::user();

            // Deactivate existing config
            UserAiConfig::where('user_id', $user->id)->update(['is_active' => false]);

            // Create OAuth config
            UserAiConfig::create([
                'user_id' => $user->id,
                'method' => 'oauth_google',
                'provider' => 'google',
                'label' => 'Google AI (OAuth)',
                'oauth_access_token' => $accessToken,
                'oauth_refresh_token' => $refreshToken,
                'oauth_expires_at' => now()->addSeconds($expiresIn),
                'available_models' => $availableModels,
                'selected_model' => $defaultModel,
                'is_active' => true,
                'last_verified_at' => now(),
            ]);

            return redirect()->route('profile.ai')->with('success', 'Google AI berhasil dihubungkan! Model: ' . $defaultModel);

        } catch (\Exception $e) {
            Log::error('Google AI OAuth error: ' . $e->getMessage());
            return redirect()->route('profile.ai')->with('error', 'Gagal menghubungkan Google AI: ' . $e->getMessage());
        }
    }

    /**
     * Update model yang dipilih (AJAX).
     */
    public function updateModel(Request $request)
    {
        $request->validate(['selected_model' => 'required|string']);

        $user = Auth::user();
        $config = UserAiConfig::getActiveForUser($user->id);

        if (!$config) {
            return response()->json(['error' => 'Tidak ada konfigurasi AI aktif.'], 404);
        }

        $config->update(['selected_model' => $request->selected_model]);

        return response()->json(['success' => true, 'model' => $request->selected_model]);
    }

    /**
     * Hapus model-model tertentu dari daftar available_models pengguna (AJAX).
     */
    public function removeModels(Request $request)
    {
        $request->validate([
            'model_ids' => 'required|array',
            'model_ids.*' => 'string'
        ]);

        $user = Auth::user();
        $config = UserAiConfig::getActiveForUser($user->id);

        if (!$config || empty($config->available_models)) {
            return response()->json(['error' => 'Tidak ada konfigurasi AI aktif.'], 404);
        }

        $modelsToRemove = $request->model_ids;
        $updatedModels = collect($config->available_models)->filter(function($m) use ($modelsToRemove) {
            return !in_array($m['id'], $modelsToRemove);
        })->values()->toArray();

        $dataToUpdate = ['available_models' => $updatedModels];

        // Jika model yang terpilih ikut terhapus, set ke null agar diganti default
        if (in_array($config->selected_model, $modelsToRemove)) {
            $dataToUpdate['selected_model'] = null;
        }

        $config->update($dataToUpdate);

        return response()->json([
            'success' => true,
            'count' => count($updatedModels)
        ]);
    }

    /**
     * Refresh daftar model dari API provider (AJAX).
     */
    public function refreshModels(Request $request)
    {
        $user = Auth::user();
        $config = UserAiConfig::getActiveForUser($user->id);

        if (!$config) {
            return response()->json(['error' => 'Tidak ada konfigurasi AI aktif.'], 404);
        }

        // Refresh token jika expired
        if ($config->method === 'oauth_google' && $config->isTokenExpired()) {
            try {
                $geminiService = app(\App\Services\GeminiService::class);
                $config = $geminiService->refreshGoogleToken($config);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Gagal refresh token: ' . $e->getMessage()], 401);
            }
        }

        $apiKey = $config->getActiveKey();

        if ($config->provider === 'google') {
            // Tentukan URL berdasarkan metode
            if ($config->method === 'oauth_google') {
                $adminApiKey = env('GOOGLE_GEMINI_API_KEY') ?: \App\Models\AiConfiguration::where('provider', 'google')->value('api_key');
                if ($adminApiKey) {
                    $modelsResponse = Http::timeout(10)->get("https://generativelanguage.googleapis.com/v1beta/models?key={$adminApiKey}");
                } else {
                    return response()->json(['error' => 'Admin API key belum dikonfigurasi untuk Google AI.'], 500);
                }
            } else {
                $modelsResponse = Http::timeout(10)->get(
                    "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}"
                );
            }

            if (!$modelsResponse->successful()) {
                return response()->json(['error' => 'Gagal mengambil daftar model dari Google.'], 500);
            }

            $modelsData = $modelsResponse->json();
            $availableModels = collect($modelsData['models'] ?? [])
                ->filter(function($m) {
                    $name = strtolower($m['name'] ?? '');
                    if (!str_contains($name, 'gemini')) return false;
                    if (!in_array('generateContent', $m['supportedGenerationMethods'] ?? [])) return false;
                    
                    $exclude = [
                        'nano', 'banana', 'vision', 'image', 'audio', 'video',
                        'tts', 'embedding', 'aqa', 'omni', 'computer', 'learnlm',
                        'native-audio', 'preview-tts',
                    ];
                    foreach ($exclude as $kw) {
                        if (str_contains($name, $kw)) return false;
                    }
                    
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
                    ];
                })
                ->values()
                ->toArray();

            $config->update(['available_models' => $availableModels]);

            return response()->json([
                'success' => true,
                'models' => $availableModels,
                'count' => count($availableModels),
            ]);
        }

        return response()->json(['error' => 'Refresh model hanya didukung untuk provider Google saat ini.'], 400);
    }

    /**
     * Redirect ke OpenAI OAuth.
     */
    public function redirectOpenAI(Request $request)
    {
        $clientId = env('OPENAI_OAUTH_CLIENT_ID');
        
        if (empty($clientId)) {
            return redirect()->route('profile.ai')->with('error', 'OpenAI OAuth belum dikonfigurasi. Silakan gunakan API Key saja — bisa didapat dari platform.openai.com/api-keys');
        }

        $request->session()->put('openai_ai_csrf', $state = \Illuminate\Support\Str::random(40));

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => url('/profil/ai/oauth/openai/callback'),
            'response_type' => 'code',
            'scope' => 'openid profile email model.request.read model.request.write',
            'state' => $state,
        ]);

        return redirect('https://auth.openai.com/authorize?' . $params);
    }

    /**
     * Handle OpenAI OAuth callback.
     */
    public function handleOpenAICallback(Request $request)
    {
        if ($request->state !== $request->session()->pull('openai_ai_csrf')) {
            return redirect()->route('profile.ai')->with('error', 'Invalid OAuth state. Silakan coba lagi.');
        }

        if ($request->has('error')) {
            return redirect()->route('profile.ai')->with('error', 'OpenAI OAuth dibatalkan: ' . $request->error);
        }

        try {
            $tokenResponse = Http::asForm()->post('https://auth.openai.com/oauth/token', [
                'client_id' => env('OPENAI_OAUTH_CLIENT_ID'),
                'client_secret' => env('OPENAI_OAUTH_CLIENT_SECRET'),
                'code' => $request->code,
                'redirect_uri' => url('/profil/ai/oauth/openai/callback'),
                'grant_type' => 'authorization_code',
            ]);

            if (!$tokenResponse->successful()) {
                Log::error('OpenAI OAuth token exchange gagal: ' . $tokenResponse->body());
                return redirect()->route('profile.ai')->with('error', 'Gagal mendapatkan token dari OpenAI.');
            }

            $tokens = $tokenResponse->json();
            $accessToken = $tokens['access_token'];
            $refreshToken = $tokens['refresh_token'] ?? null;
            $expiresIn = $tokens['expires_in'] ?? 3600;

            // List available models using the OAuth token
            $modelsResponse = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
            ])->get('https://api.openai.com/v1/models');

            $availableModels = [];
            $defaultModel = 'gpt-4o-mini';

            if ($modelsResponse->successful()) {
                $modelsData = $modelsResponse->json();
                $availableModels = collect($modelsData['data'] ?? [])
                    ->filter(function($m) {
                        $id = strtolower($m['id'] ?? '');
                        
                        $allowedPrefixes = ['gpt-4', 'gpt-3.5', 'chatgpt', 'o1', 'o3', 'o4'];
                        $isAllowed = false;
                        foreach ($allowedPrefixes as $prefix) {
                            if (str_starts_with($id, $prefix)) {
                                $isAllowed = true;
                                break;
                            }
                        }
                        if (!$isAllowed) return false;
                        
                        $exclude = ['audio', 'realtime', 'transcribe', 'tts', 'dall-e', 'whisper', 'embedding', 'search', 'moderation', 'instruct'];
                        foreach ($exclude as $kw) {
                            if (str_contains($id, $kw)) return false;
                        }
                        
                        return true;
                    })
                    ->map(function($m) {
                        $id = $m['id'];
                        $displayName = $id;
                        
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
                        ];
                    })
                    ->sortBy('id')
                    ->values()
                    ->toArray();

                if (!empty($availableModels)) {
                    $preferred = collect($availableModels)->first(fn($m) => str_contains($m['id'], 'gpt-4o-mini'))
                        ?? collect($availableModels)->first(fn($m) => str_contains($m['id'], 'gpt-4o'))
                        ?? $availableModels[0];
                    $defaultModel = $preferred['id'];
                }
            }

            $user = Auth::user();

            // Deactivate existing config
            UserAiConfig::where('user_id', $user->id)->update(['is_active' => false]);

            // Create OAuth config
            UserAiConfig::create([
                'user_id' => $user->id,
                'method' => 'oauth_openai',
                'provider' => 'openai',
                'label' => 'OpenAI (OAuth)',
                'oauth_access_token' => $accessToken,
                'oauth_refresh_token' => $refreshToken,
                'oauth_expires_at' => now()->addSeconds($expiresIn),
                'available_models' => $availableModels,
                'selected_model' => $defaultModel,
                'is_active' => true,
                'last_verified_at' => now(),
            ]);

            return redirect()->route('profile.ai')->with('success', 'OpenAI berhasil dihubungkan! Model: ' . $defaultModel);

        } catch (\Exception $e) {
            Log::error('OpenAI OAuth error: ' . $e->getMessage());
            return redirect()->route('profile.ai')->with('error', 'Gagal menghubungkan OpenAI: ' . $e->getMessage());
        }
    }

    /**
     * Test semua model yang tersedia (AJAX) — kirim prompt singkat ke setiap model.
     */
    public function testModels(Request $request, AIProviderService $aiProvider)
    {
        $user = Auth::user();
        $config = UserAiConfig::getActiveForUser($user->id);

        if (!$config || empty($config->available_models)) {
            return response()->json(['error' => 'Tidak ada konfigurasi AI aktif atau model kosong.'], 404);
        }

        // Refresh token jika expired
        if ($config->method === 'oauth_google' && $config->isTokenExpired()) {
            try {
                $geminiService = app(\App\Services\GeminiService::class);
                $config = $geminiService->refreshGoogleToken($config);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Gagal refresh token: ' . $e->getMessage()], 401);
            }
        }

        $apiKey = $config->getActiveKey();
        $provider = $config->provider;
        $results = [];

        foreach ($config->available_models as $model) {
            $modelId = $model['id'];
            try {
                $response = $aiProvider->generate(
                    $provider,
                    $apiKey,
                    $modelId,
                    'Jawab dengan satu kata: "OK"',
                    false // non-JSON agar lebih ringan
                );

                $results[] = [
                    'id' => $modelId,
                    'name' => $model['name'] ?? $modelId,
                    'status' => 'ok',
                    'message' => 'Berhasil',
                ];
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                // Singkatkan pesan error
                if (str_contains($msg, '503')) {
                    $msg = 'Server sibuk (503)';
                } elseif (str_contains($msg, '400')) {
                    $msg = 'Model tidak mendukung teks (400)';
                } elseif (str_contains($msg, '429')) {
                    $msg = 'Rate limit tercapai (429)';
                } elseif (str_contains($msg, '404')) {
                    $msg = 'Model tidak ditemukan (404)';
                }

                $results[] = [
                    'id' => $modelId,
                    'name' => $model['name'] ?? $modelId,
                    'status' => 'error',
                    'message' => $msg,
                ];
            }

            // Delay antar request agar tidak kena rate-limit
            usleep(300000); // 300ms
        }

        return response()->json([
            'provider' => $provider,
            'total' => count($results),
            'working' => count(array_filter($results, fn($r) => $r['status'] === 'ok')),
            'results' => $results,
        ]);
    }

    protected function generateLabel(string $provider): string
    {
        return match ($provider) {
            'google' => 'Google AI Studio',
            'openai' => 'OpenAI',
            'anthropic' => 'Anthropic Claude',
            'nvidia' => 'NVIDIA NIM',
            'groq' => 'Groq',
            'openrouter' => 'OpenRouter',
            default => ucfirst($provider),
        };
    }
}

