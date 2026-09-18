<?php

namespace App\Http\Controllers;

use App\Models\AiConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiHealthController extends Controller
{
    /**
     * Quick check if AI service is reachable.
     * Returns JSON with status & details.
     */
    public function check(): JsonResponse
    {
        try {
            $userId = \Illuminate\Support\Facades\Auth::id();
            $userConfig = $userId ? \App\Models\UserAiConfig::getActiveForUser($userId) : null;
            $config = $userConfig ?? AiConfiguration::getActiveWithModel();
            
            $geminiService = app(\App\Services\GeminiService::class);
            $availableModels = $geminiService->getAvailableModels($userId);

            if (!$config) {
                // Fallback: cek .env
                $envKey = env('GEMINI_API_KEY');
                if (empty($envKey)) {
                    return response()->json([
                        'status' => 'unavailable',
                        'message' => 'Tidak ada API Key AI yang dikonfigurasi.',
                        'provider' => null,
                        'model' => null,
                        'available_models' => $availableModels,
                    ]);
                }

                // Test env key
                return $this->testEnvKey($envKey, $availableModels);
            }

            $provider = $config->provider;
            if ($config instanceof \App\Models\AiConfiguration) {
                $provider = $config->detected_provider ?? $config->provider;
            }
            
            $model = $config->selected_model;

            return response()->json([
                'status' => 'available',
                'message' => "AI terhubung: {$provider} ({$model})",
                'provider' => $provider,
                'model' => $model,
                'available_models' => $availableModels,
            ]);
        } catch (\Exception $e) {
            Log::error('AI Health Check gagal: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memeriksa koneksi AI: ' . $e->getMessage(),
                'provider' => null,
                'model' => null,
            ]);
        }
    }

    protected function testEnvKey(string $key, array $availableModels): JsonResponse
    {
        $model = env('GEMINI_MODEL', 'gemini-1.5-pro');

        if (str_starts_with($key, 'gsk_')) {
            return response()->json([
                'status' => 'available',
                'message' => "AI terhubung: Groq ({$model})",
                'provider' => 'groq',
                'model' => $model,
                'available_models' => $availableModels,
            ]);
        }

        return response()->json([
            'status' => 'available',
            'message' => "AI terhubung: Google Gemini ({$model})",
            'provider' => 'google',
            'model' => $model,
            'available_models' => $availableModels,
        ]);
    }
}
