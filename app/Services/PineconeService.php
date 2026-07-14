<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PineconeService
{
    protected string $apiKey;
    protected string $indexName;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('PINECONE_API_KEY', '');
        $this->indexName = env('PINECONE_INDEX', 'redsim-rag');

        // Pinecone Serverless URL format
        // Akan di-resolve saat pertama kali digunakan
        $this->baseUrl = '';
    }

    /**
     * Resolve Pinecone host URL dari API.
     */
    protected function getBaseUrl(): string
    {
        if (!empty($this->baseUrl)) {
            return $this->baseUrl;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Api-Key' => $this->apiKey])
                ->get("https://api.pinecone.io/indexes/{$this->indexName}");

            if ($response->successful()) {
                $host = $response->json('host');
                if ($host) {
                    $this->baseUrl = "https://{$host}";
                    Log::info("Pinecone host resolved: {$this->baseUrl}");
                    return $this->baseUrl;
                }
            }

            Log::error("Pinecone index info error: {$response->status()} - {$response->body()}");
        } catch (\Exception $e) {
            Log::error("Pinecone host resolve failed: " . $e->getMessage());
        }

        throw new RuntimeException(
            "Tidak dapat terhubung ke Pinecone. Pastikan PINECONE_API_KEY dan PINECONE_INDEX valid di .env. " .
            "Buat index '{$this->indexName}' dengan dimensi 768 di dashboard Pinecone."
        );
    }

    /**
     * Upsert vectors ke Pinecone.
     *
     * @param array $vectors Array of ['id' => string, 'values' => array, 'metadata' => array]
     * @return bool
     */
    public function upsert(array $vectors): bool
    {
        $baseUrl = $this->getBaseUrl();

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/vectors/upsert", [
                    'vectors' => $vectors,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error("Pinecone upsert error: {$response->status()} - {$response->body()}");
            return false;
        } catch (\Exception $e) {
            Log::error("Pinecone upsert failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch upsert dengan chunking (Pinecone limit 100 vectors per request).
     *
     * @param array $vectors
     * @param int $batchSize
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

            // Rate limiting antar batch
            usleep(100000); // 100ms
        }

        return $stats;
    }

    /**
     * Query vectors (similarity search).
     *
     * @param array $queryVector Array 768 float
     * @param int $topK Jumlah hasil teratas
     * @param array $filter Filter metadata (optional)
     * @param bool $includeMetadata
     * @return array
     */
    public function query(array $queryVector, int $topK = 10, array $filter = [], bool $includeMetadata = true): array
    {
        $baseUrl = $this->getBaseUrl();

        $payload = [
            'vector' => $queryVector,
            'topK' => $topK,
            'includeMetadata' => $includeMetadata,
        ];

        if (!empty($filter)) {
            $payload['filter'] = $filter;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/query", $payload);

            if ($response->successful()) {
                return $response->json('matches') ?? [];
            }

            Log::error("Pinecone query error: {$response->status()} - {$response->body()}");
            return [];
        } catch (\Exception $e) {
            Log::error("Pinecone query failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Upsert teks langsung ke Pinecone (Integrated Embeddings).
     *
     * @param array $records Array of ['id' => string, 'text' => string, 'metadata' => array]
     * @param string $namespace
     * @return bool
     */
    public function upsertText(array $records, string $namespace = 'default'): bool
    {
        $baseUrl = $this->getBaseUrl();

        // Format NDJSON (Newline Delimited JSON)
        $ndjson = '';
        foreach ($records as $record) {
            $row = [
                '_id' => $record['id'],
                'text' => $record['text'] // Pinecone akan melakukan embed pada field text
            ];
            if (!empty($record['metadata'])) {
                $row = array_merge($row, $record['metadata']);
            }
            $ndjson .= json_encode($row) . "\n";
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/x-ndjson',
                    'X-Pinecone-Api-Version' => '2024-10', // Versi API yang mendukung Integrated Inference
                ])
                ->withBody($ndjson, 'application/x-ndjson')
                ->post("{$baseUrl}/records/namespaces/{$namespace}/upsert");

            if ($response->successful()) {
                return true;
            }

            Log::error("Pinecone text upsert error: {$response->status()} - {$response->body()}");
            return false;
        } catch (\Exception $e) {
            Log::error("Pinecone text upsert failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Query teks langsung ke Pinecone (Integrated Embeddings).
     *
     * @param string $queryText Teks pencarian
     * @param int $topK Jumlah hasil teratas
     * @param array $filter Filter metadata (optional)
     * @param string $namespace
     * @return array
     */
    public function queryText(string $queryText, int $topK = 10, array $filter = [], string $namespace = 'default'): array
    {
        $baseUrl = $this->getBaseUrl();

        $payload = [
            'query' => [
                'inputs' => ['text' => $queryText],
                'top_k' => $topK,
            ],
            'fields' => ['text'],
        ];

        if (!empty($filter)) {
            $payload['query']['filter'] = $filter;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'X-Pinecone-Api-Version' => '2025-01',
                ])
                ->post("{$baseUrl}/records/namespaces/{$namespace}/search", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $hits = $data['result']['hits'] ?? [];

                // Normalisasi format agar kompatibel dengan RetrievalService
                $matches = [];
                foreach ($hits as $hit) {
                    $fields = $hit['fields'] ?? [];
                    $matches[] = [
                        'id' => $hit['_id'] ?? '',
                        'score' => $hit['_score'] ?? 0,
                        'metadata' => array_merge(
                            $fields,
                            ['content' => $fields['text'] ?? '']
                        ),
                    ];
                }
                return $matches;
            }

            Log::error("Pinecone text query error: {$response->status()} - {$response->body()}");
            return [];
        } catch (\Exception $e) {
            Log::error("Pinecone text query failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete vectors by IDs.
     *
     * @param array $ids Array of vector IDs
     * @return bool
     */
    public function delete(array $ids): bool
    {
        $baseUrl = $this->getBaseUrl();

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/vectors/delete", [
                    'ids' => $ids,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Pinecone delete failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete semua vectors di index (HATI-HATI!).
     *
     * @return bool
     */
    public function deleteAll(): bool
    {
        $baseUrl = $this->getBaseUrl();

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/vectors/delete", [
                    'deleteAll' => true,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Pinecone deleteAll failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cek status koneksi Pinecone.
     *
     * @return array ['status' => string, 'message' => string]
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
                    'status' => 'ok',
                    'message' => "Pinecone Index '{$this->indexName}' aktif | " .
                                 "Dimensi: {$data['dimension']} | " .
                                 "Metric: {$data['metric']} | " .
                                 "Status: " . ($data['status']['ready'] ?? false ? 'Ready' : 'Not Ready'),
                    'total_vectors' => $data['status']['ready'] ?? false ? 'Ready' : 'Initializing',
                ];
            }

            if ($response->status() === 404) {
                return [
                    'status' => 'error',
                    'message' => "Index '{$this->indexName}' tidak ditemukan. Buat index dengan dimensi 768 di dashboard Pinecone.",
                ];
            }

            return [
                'status' => 'error',
                'message' => "Pinecone API error: HTTP {$response->status()}",
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => "Gagal terhubung ke Pinecone: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Cek statistik index.
     */
    public function describeIndexStats(): array
    {
        $baseUrl = $this->getBaseUrl();

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/describe_index_stats", []);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => "HTTP {$response->status()}"];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
