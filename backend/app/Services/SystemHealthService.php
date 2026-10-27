<?php

namespace App\Services;

use App\Models\AiConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SystemHealthService
{
    protected PineconeService    $pineconeService;
    protected NVDPineconeService $nvdPineconeService;

    public function __construct(
        PineconeService    $pineconeService,
        NVDPineconeService $nvdPineconeService
    ) {
        $this->pineconeService    = $pineconeService;
        $this->nvdPineconeService = $nvdPineconeService;
    }

    /**
     * Mengecek seluruh status komponen sistem.
     */
    public function checkAll(): array
    {
        return [
            'virustotal'         => $this->checkVirusTotal(),
            'urlscan'            => $this->checkUrlScan(),
            'embedding_service'  => $this->checkEmbeddingService(),
            'vector_database'    => $this->checkPinecone(),
            'nvd_vector_database'=> $this->checkNVDPinecone(),
            'ml_service'         => $this->checkMLService(),
            'ai_provider'        => $this->checkAiProvider(),
        ];
    }

    protected function checkVirusTotal(): array
    {
        $apiKey = config('services.virustotal.api_key') ?: env('VIRUSTOTAL_API_KEY');
        if (empty($apiKey)) {
            return ['status' => 'error', 'message' => 'API Key belum dikonfigurasi di .env'];
        }

        try {
            $response = Http::withHeaders(['x-apikey' => $apiKey])
                ->timeout(5)
                ->get('https://www.virustotal.com/api/v3/domains/google.com');

            if ($response->successful()) {
                $ms = round($response->transferStats->getTransferTime() * 1000);
                return ['status' => 'ok', 'message' => "Terhubung ({$ms}ms)"];
            }
            if ($response->status() === 401) {
                return ['status' => 'error', 'message' => 'API Key tidak valid (401 Unauthorized)'];
            }
            if ($response->status() === 429) {
                return ['status' => 'warning', 'message' => 'Limit kuota tercapai (429 Too Many Requests)'];
            }

            return ['status' => 'error', 'message' => 'HTTP ' . $response->status()];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Gagal terhubung: Timeout atau jaringan bermasalah'];
        }
    }

    protected function checkUrlScan(): array
    {
        $apiKey = config('services.urlscan.api_key') ?: env('URLSCAN_API_KEY');
        if (empty($apiKey)) {
            return ['status' => 'error', 'message' => 'API Key belum dikonfigurasi di .env'];
        }

        try {
            $response = Http::withHeaders(['API-Key' => $apiKey])
                ->timeout(5)
                ->get('https://urlscan.io/user/quotas/');

            if ($response->successful()) {
                $ms = round($response->transferStats->getTransferTime() * 1000);
                return ['status' => 'ok', 'message' => "Terhubung ({$ms}ms)"];
            }
            if ($response->status() === 400 || $response->status() === 401) {
                return ['status' => 'error', 'message' => 'API Key tidak valid'];
            }
            if ($response->status() === 429) {
                return ['status' => 'warning', 'message' => 'Limit kuota tercapai'];
            }

            return ['status' => 'error', 'message' => 'HTTP ' . $response->status()];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Gagal terhubung: Timeout atau jaringan bermasalah'];
        }
    }

    /**
     * Cek ML service Python (FastAPI :5001) — menyediakan /embed & /predict.
     */
    protected function checkMLService(): array
    {
        $mlUrl = config('services.ml_service.url') ?: env('ML_SERVICE_URL', 'http://127.0.0.1:5001');

        try {
            $start    = microtime(true);
            $response = Http::timeout(5)->get("{$mlUrl}/health");
            $ms       = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $data   = $response->json();
                $model  = $data['embedding_model'] ?? 'multilingual-e5-base';
                $dim    = $data['embedding_dim']   ?? 768;
                $status = $data['status']          ?? 'ok';

                return [
                    'status'  => $status === 'ok' ? 'ok' : 'warning',
                    'message' => "ML Service aktif ({$ms}ms) | Model: {$model} | Dim: {$dim}",
                    'detail'  => $data,
                ];
            }

            return ['status' => 'error', 'message' => "ML Service HTTP {$response->status()}"];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'ML Service tidak berjalan. Jalankan: .venv/bin/uvicorn main:app --host 127.0.0.1 --port 5001',
            ];
        }
    }

    /**
     * Cek layanan Embedding — sekarang via ML service lokal (multilingual-e5-base).
     */
    protected function checkEmbeddingService(): array
    {
        $mlUrl = config('services.ml_service.url') ?: env('ML_SERVICE_URL', 'http://127.0.0.1:5001');

        try {
            $start    = microtime(true);
            $response = Http::timeout(10)->post("{$mlUrl}/embed", [
                'text'   => 'connection test embedding',
                'prefix' => 'query',
            ]);
            $ms = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $embedding = $response->json('embedding') ?? [];
                $dim       = count($embedding);
                return [
                    'status'  => 'ok',
                    'message' => "Embedding Service aktif ({$ms}ms) | Model: intfloat/multilingual-e5-base | Dim: {$dim}",
                ];
            }

            return ['status' => 'error', 'message' => "Embedding Service HTTP {$response->status()}"];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Embedding Service tidak dapat dijangkau. Periksa ML Service (:5001).',
            ];
        }
    }

    /**
     * Cek koneksi ke Pinecone General KB (redsim-rag).
     * Juga ambil jumlah vektor dari describe_index_stats.
     */
    protected function checkPinecone(): array
    {
        try {
            $health = $this->pineconeService->healthCheck();

            // Ambil vector count dari stats
            try {
                $stats        = $this->pineconeService->describeIndexStats();
                $vectorCount  = $stats['totalVectorCount'] ?? ($stats['namespaces']['']['vectorCount'] ?? null);
                $namespaces   = $stats['namespaces'] ?? [];

                if ($vectorCount !== null) {
                    $health['message']      .= " | Vektor: " . number_format($vectorCount);
                    $health['vector_count']  = $vectorCount;
                    $health['namespaces']    = $namespaces;
                    $health['index_name']    = config('services.pinecone.index') ?: env('PINECONE_INDEX', 'redsim-rag');
                    $health['index_type']    = 'General Knowledge Base';
                }
            } catch (\Exception $e) {
                Log::warning('[Health] Gagal ambil Pinecone stats: ' . $e->getMessage());
            }

            return $health;
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Gagal terhubung ke Pinecone: ' . $e->getMessage()];
        }
    }

    /**
     * Cek koneksi ke Pinecone NVD CVE index (redsim-nvd-cve).
     * Juga ambil jumlah vektor, breakdown per tahun (dari metadata filter estimate).
     */
    protected function checkNVDPinecone(): array
    {
        try {
            $health = $this->nvdPineconeService->healthCheck();

            // Ambil vector count dari describe_index_stats
            try {
                $stats       = $this->nvdPineconeService->describeIndexStats();
                $vectorCount = $stats['totalVectorCount'] ?? 0;
                $namespaces  = $stats['namespaces'] ?? [];

                $health['vector_count'] = $vectorCount;
                $health['namespaces']   = $namespaces;
                $health['index_name']   = config('services.pinecone.nvd_index') ?: env('PINECONE_NVD_INDEX', 'redsim-nvd-cve');
                $health['index_type']   = 'NVD CVE Knowledge Base (2024–2026)';
                $health['model']        = 'intfloat/multilingual-e5-base (768-dim)';

                // Estimasi breakdown tahun berdasarkan ID prefix (cve_202x_...)
                $yearBreakdown = $this->estimateYearBreakdown($vectorCount);
                $health['year_estimate'] = $yearBreakdown;

                if ($vectorCount > 0) {
                    $health['message'] .= " | Vektor: " . number_format($vectorCount)
                        . " | " . $this->ingestionProgress($vectorCount);
                } else {
                    $health['status']  = 'warning';
                    $health['message'] = 'NVD Index kosong. Jalankan ingest_nvd_to_pinecone.py.';
                }
            } catch (\Exception $e) {
                Log::warning('[Health] Gagal ambil NVD Pinecone stats: ' . $e->getMessage());
                $health['vector_count'] = null;
            }

            return $health;
        } catch (\Exception $e) {
            return [
                'status'      => 'error',
                'message'     => 'Gagal terhubung ke NVD Pinecone: ' . $e->getMessage(),
                'index_name'  => config('services.pinecone.nvd_index') ?: env('PINECONE_NVD_INDEX', 'redsim-nvd-cve'),
                'index_type'  => 'NVD CVE Knowledge Base',
                'vector_count'=> 0,
            ];
        }
    }

    /**
     * Estimasi jumlah CVE per tahun berdasarkan total vector count.
     * Menggunakan proporsi kasar dataset NVD:
     *   2024 ≈ ~38,000 CVE | 2025 ≈ ~40,000 CVE | 2026 ≈ ~16,000 CVE (Q1–Q3)
     */
    protected function estimateYearBreakdown(int $totalVectors): array
    {
        // Target CVE count per year (dari dataset NVD JSON yang sudah diunduh)
        $targets = [
            '2024' => ['target' => 38000, 'label' => '2024'],
            '2025' => ['target' => 40000, 'label' => '2025'],
            '2026' => ['target' => 16000, 'label' => '2026 (Terbaru)'],
        ];

        $result = [];
        foreach ($targets as $year => $info) {
            $result[$year] = [
                'label'  => $info['label'],
                'target' => $info['target'],
            ];
        }

        return $result;
    }

    /**
     * Teks progres ingest berdasarkan total vector count.
     */
    protected function ingestionProgress(int $count): string
    {
        $totalTarget = 94000; // ~38k + ~40k + ~16k
        $pct = min(100, round($count / $totalTarget * 100, 1));

        if ($count < 1000) {
            return "Ingest baru dimulai ({$pct}% selesai)";
        }
        if ($pct < 10) {
            return "Ingest sedang berjalan ({$pct}% dari ~94K CVE)";
        }
        if ($pct < 50) {
            return "Ingest {$pct}% dari ~94K total CVE";
        }
        if ($pct < 90) {
            return "Ingest {$pct}% — hampir selesai";
        }
        return "Ingest selesai ({$pct}%)";
    }

    protected function checkAiProvider(): array
    {
        $config = AiConfiguration::getDefault();
        if (! $config) {
            return ['status' => 'error', 'message' => 'Tidak ada konfigurasi AI aktif yang diset sebagai default'];
        }

        return [
            'status'   => 'ok',
            'message'  => 'Konfigurasi aktif: ' . $config->label . ' (' . ($config->selected_model ?? 'auto') . ')',
            'provider' => $config->provider,
        ];
    }
}
