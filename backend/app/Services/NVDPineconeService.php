<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * NVDPineconeService
 *
 * Service Pinecone terpisah untuk index NVD CVE (redsim-nvd-cve).
 * Dibuat terpisah dari PineconeService agar kedua index benar-benar
 * terisolasi dan konfigurasinya independen.
 */
class NVDPineconeService
{
    protected string $apiKey;
    protected string $indexName;
    protected string $baseUrl;

    public function __construct()
    {
        // Gunakan API key TERPISAH khusus untuk NVD CVE index
        $this->apiKey    = config('services.pinecone.nvd_api_key') ?: (config('services.pinecone.api_key') ?: env('PINECONE_NVD_API_KEY', env('PINECONE_API_KEY', '')));
        $this->indexName = config('services.pinecone.nvd_index') ?: env('PINECONE_NVD_INDEX', 'redsim-nvd-cve');
        $this->baseUrl   = '';
    }

    /**
     * Resolve Pinecone host URL untuk index NVD CVE.
     */
    protected function getBaseUrl(): string
    {
        // Cache host URL 24 jam — hindari resolve ulang tiap scan (menghemat 15-45 detik)
        $cacheKey = "pinecone_nvd_host_{$this->indexName}";

        if (!empty($this->baseUrl)) {
            return $this->baseUrl;
        }

        $cached = Cache::get($cacheKey);
        if ($cached) {
            $this->baseUrl = $cached;
            return $this->baseUrl;
        }

        try {
            $response = Http::timeout(8) // 8s cukup, kurangi dari 15s
                ->retry(1, 0)           // 1 percobaan saja — gagal langsung skip
                ->withHeaders(['Api-Key' => $this->apiKey])
                ->get("https://api.pinecone.io/indexes/{$this->indexName}");

            if ($response->successful()) {
                $host = $response->json('host');
                if ($host) {
                    $this->baseUrl = "https://{$host}";
                    Cache::put($cacheKey, $this->baseUrl, now()->addHours(24));
                    Log::info("[NVD Pinecone] Host resolved & cached: {$this->baseUrl}");
                    return $this->baseUrl;
                }
            }

            Log::error("[NVD Pinecone] Index info error: {$response->status()} - {$response->body()}");
        } catch (\Exception $e) {
            Log::error("[NVD Pinecone] Host resolve failed: " . $e->getMessage());
        }

        throw new RuntimeException(
            "Tidak dapat terhubung ke Pinecone NVD index '{$this->indexName}'. " .
            "Buat index dengan dimensi 768 di dashboard Pinecone, atau jalankan artisan nvd:create-index."
        );
    }

    /**
     * Upsert vectors ke Pinecone NVD index.
     *
     * @param array $vectors Array of ['id' => string, 'values' => float[], 'metadata' => array]
     */
    public function upsert(array $vectors): bool
    {
        $baseUrl = $this->getBaseUrl();

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Api-Key'      => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/vectors/upsert", [
                    'vectors' => $vectors,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error("[NVD Pinecone] Upsert error: {$response->status()} - {$response->body()}");
            return false;
        } catch (\Exception $e) {
            Log::error("[NVD Pinecone] Upsert failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch upsert dengan chunking (limit 100 vectors per request).
     *
     * @param array $vectors
     * @param int   $batchSize
     * @return array ['berhasil' => int, 'gagal' => int]
     */
    public function batchUpsert(array $vectors, int $batchSize = 100): array
    {
        $stats = ['berhasil' => 0, 'gagal' => 0];

        foreach (array_chunk($vectors, $batchSize) as $batch) {
            if ($this->upsert($batch)) {
                $stats['berhasil'] += count($batch);
            } else {
                $stats['gagal'] += count($batch);
            }
            usleep(100000); // 100ms rate-limiting
        }

        return $stats;
    }

    /**
     * Embed teks via ML service Python lokal (multilingual-e5-base, 768-dim).
     *
     * @param string $text
     * @return float[]|null  null jika gagal
     */
    protected function embedViaMLService(string $text): ?array
    {
        $mlServiceUrl = config('services.ml_service.url') ?: env('ML_SERVICE_URL', 'http://127.0.0.1:5001');

        try {
            $response = Http::timeout(15)
                ->post("{$mlServiceUrl}/embed", [
                    'text'   => $text,
                    'prefix' => 'query',   // "query: " prefix untuk multilingual-e5
                ]);

            if ($response->successful()) {
                return $response->json('embedding');
            }

            Log::warning("[NVD Pinecone] ML embed error: {$response->status()}");
            return null;
        } catch (\Exception $e) {
            Log::warning("[NVD Pinecone] ML embed failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Query vectors via dense vector search (multilingual-e5-base embedding).
     *
     * @param string $queryText  Teks pencarian
     * @param int    $topK       Jumlah hasil teratas
     * @param array  $filter     Filter metadata (optional), e.g. ['severity' => ['$eq' => 'CRITICAL']]
     * @return array
     */
    public function queryText(string $queryText, int $topK = 10, array $filter = []): array
    {
        // Embed teks via ML service Python
        $vector = $this->embedViaMLService($queryText);
        if (!$vector) {
            Log::error('[NVD Pinecone] Gagal embed query text via ML service.');
            return [];
        }

        return $this->queryVector($vector, $topK, $filter);
    }

    /**
     * Query menggunakan pre-computed dense vector.
     *
     * @param float[] $vector   Vector 768-dim
     * @param int     $topK
     * @param array   $filter
     * @return array
     */
    public function queryVector(array $vector, int $topK = 10, array $filter = []): array
    {
        $baseUrl = $this->getBaseUrl();

        $payload = [
            'vector'          => $vector,
            'topK'            => $topK,
            'includeMetadata' => true,
            'namespace'       => 'default',
        ];

        if (!empty($filter)) {
            $payload['filter'] = $filter;
        }

        try {
            $response = Http::timeout(20)
                ->retry(1, 0)
                ->withHeaders([
                    'Api-Key'      => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/query", $payload);

            if ($response->successful()) {
                $matches = $response->json('matches') ?? [];

                return array_map(function ($m) {
                    $meta = $m['metadata'] ?? [];
                    return [
                        'id'       => $m['id'] ?? '',
                        'score'    => $m['score'] ?? 0,
                        'metadata' => array_merge(
                            $meta,
                            ['content' => $meta['text'] ?? '']
                        ),
                    ];
                }, $matches);
            }

            Log::error("[NVD Pinecone] Query error: {$response->status()} - {$response->body()}");
            return [];
        } catch (\Exception $e) {
            Log::error("[NVD Pinecone] Query failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Upsert teks langsung ke Pinecone NVD index (Integrated Embeddings via NDJSON).
     *
     * @param array  $records   Array of ['id' => string, 'text' => string, 'metadata' => array]
     * @return bool
     */
    public function upsertText(array $records): bool
    {
        $baseUrl = $this->getBaseUrl();

        $ndjson = '';
        foreach ($records as $record) {
            $row = ['_id' => $record['id'], 'text' => $record['text']];
            if (!empty($record['metadata'])) {
                $row = array_merge($row, $record['metadata']);
            }
            $ndjson .= json_encode($row) . "\n";
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Api-Key'                => $this->apiKey,
                    'Content-Type'           => 'application/x-ndjson',
                    'X-Pinecone-Api-Version' => '2024-10',
                ])
                ->withBody($ndjson, 'application/x-ndjson')
                ->post("{$baseUrl}/records/namespaces/default/upsert");

            if ($response->successful()) {
                return true;
            }

            Log::error("[NVD Pinecone] Text upsert error: {$response->status()} - {$response->body()}");
            return false;
        } catch (\Exception $e) {
            Log::error("[NVD Pinecone] Text upsert failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Health check untuk index NVD CVE.
     */
    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Api-Key' => $this->apiKey])
                ->get("https://api.pinecone.io/indexes/{$this->indexName}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status'    => 'ok',
                    'message'   => "NVD CVE Index '{$this->indexName}' aktif | " .
                                   "Dimensi: " . ($data['dimension'] ?? '?') . " | " .
                                   "Status: " . (($data['status']['ready'] ?? false) ? 'Ready' : 'Not Ready'),
                    'dimension' => $data['dimension'] ?? null,
                    'ready'     => $data['status']['ready'] ?? false,
                ];
            }

            if ($response->status() === 404) {
                return [
                    'status'  => 'error',
                    'message' => "NVD Index '{$this->indexName}' tidak ditemukan. " .
                                 "Jalankan: php artisan nvd:create-index",
                ];
            }

            return [
                'status'  => 'error',
                'message' => "Pinecone API error: HTTP {$response->status()}",
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => "Gagal terhubung ke NVD Pinecone: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Statistik index NVD CVE.
     */
    public function describeIndexStats(): array
    {
        $baseUrl = $this->getBaseUrl();

        try {
            // Pinecone Serverless: GET /describe_index_stats
            $response = Http::timeout(10)
                ->withHeaders([
                    'Api-Key'      => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->get("{$baseUrl}/describe_index_stats");

            if ($response->successful()) {
                return $response->json();
            }

            // Fallback ke POST jika GET tidak didukung
            $response = Http::timeout(10)
                ->withHeaders([
                    'Api-Key'      => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/describe_index_stats", new \stdClass());

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => "HTTP {$response->status()}: {$response->body()}"];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
