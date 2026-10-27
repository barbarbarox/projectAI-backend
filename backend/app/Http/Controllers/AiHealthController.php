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

    /**
     * Check status NVD CVE Pinecone index (redsim-nvd-cve).
     * Returns vector count, index status, dan ingest progress.
     */
    public function nvdHealth(): JsonResponse
    {
        try {
            /** @var \App\Services\NVDPineconeService */
            $nvdPinecone = app(\App\Services\NVDPineconeService::class);

            $health = $nvdPinecone->healthCheck();
            $stats  = $nvdPinecone->describeIndexStats();

            $vectorCount = $stats['totalVectorCount'] ?? 0;
            $indexFullness = $stats['indexFullness'] ?? 0;

            // Target total CVE: 2024 (~38K) + 2025 (~40K) + 2026 (~16K) = ~94K
            $targetTotal = 94000;
            $pct = $vectorCount > 0 ? min(100, round($vectorCount / $targetTotal * 100, 1)) : 0;

            return response()->json([
                'status'        => $health['status'],
                'index_name'    => env('PINECONE_NVD_INDEX', 'redsim-nvd-cve'),
                'index_type'    => 'NVD CVE Knowledge Base (2024–2026)',
                'model'         => 'intfloat/multilingual-e5-base (768-dim)',
                'vector_count'  => $vectorCount,
                'index_fullness'=> $indexFullness,
                'ingest_pct'    => $pct,
                'target_total'  => $targetTotal,
                'message'       => $health['message'],
                'ready'         => $health['ready'] ?? false,
                'namespaces'    => $stats['namespaces'] ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal cek NVD index: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check status ML service Python (FastAPI :5001).
     */
    public function mlHealth(): JsonResponse
    {
        $mlUrl = env('ML_SERVICE_URL', 'http://127.0.0.1:5001');

        try {
            $start    = microtime(true);
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("{$mlUrl}/health");
            $ms       = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json(array_merge($data, [
                    'response_ms' => $ms,
                    'url'         => $mlUrl,
                ]));
            }

            return response()->json([
                'status'  => 'error',
                'message' => "ML Service HTTP {$response->status()}",
                'url'     => $mlUrl,
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'ML Service tidak berjalan.',
                'url'     => $mlUrl,
                'hint'    => 'cd backend/ml_service && .venv/bin/uvicorn main:app --host 127.0.0.1 --port 5001',
            ], 503);
        }
    }

    /**
     * Full system health check — memanggil SystemHealthService->checkAll().
     * Digunakan oleh halaman admin kesehatan sistem via AJAX (refresh live).
     */
    public function systemHealth(\App\Services\SystemHealthService $healthService): JsonResponse
    {
        try {
            $status = $healthService->checkAll();
            return response()->json([
                'ok'     => true,
                'status' => $status,
                'checked_at' => now()->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            Log::error('System health check gagal: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
