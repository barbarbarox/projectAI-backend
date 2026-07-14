<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class EmbeddingService
{
    protected string $apiToken;
    protected string $model;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiToken = env('HUGGINGFACE_API_TOKEN', '');
        $this->model = env('HUGGINGFACE_MODEL', 'intfloat/multilingual-e5-base');
        $this->baseUrl = "https://api-inference.huggingface.co/pipeline/feature-extraction/{$this->model}";
    }

    /**
     * Generate embedding untuk satu teks.
     *
     * @param string $teks Teks yang akan di-embed
     * @param string $mode 'query' untuk pencarian, 'passage' untuk dokumen
     * @return array Array 768 float
     * @throws RuntimeException
     */
    public function generateEmbedding(string $teks, string $mode = 'passage'): array
    {
        $prefix = $mode === 'query' ? 'query: ' : 'passage: ';
        $inputText = $prefix . $teks;

        $maxRetries = 3;
        $attempt = 0;

        while ($attempt <= $maxRetries) {
            try {
                $response = Http::timeout(60)
                    ->withHeaders([
                        'Authorization' => "Bearer {$this->apiToken}",
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->baseUrl, [
                        'inputs' => $inputText,
                        'options' => [
                            'wait_for_model' => true,
                        ],
                    ]);

                if ($response->successful()) {
                    $embedding = $response->json();

                    // HuggingFace mengembalikan array langsung untuk single input
                    if (is_array($embedding) && !empty($embedding)) {
                        // Jika hasilnya nested (array of arrays), ambil yang pertama
                        if (is_array($embedding[0] ?? null) && is_array($embedding[0][0] ?? null)) {
                            // Token-level embeddings, perlu di-average (mean pooling)
                            return $this->meanPooling($embedding[0]);
                        }
                        // Jika sudah flat array of floats
                        if (is_float($embedding[0]) || is_int($embedding[0])) {
                            return $this->normalizeEmbedding($embedding);
                        }
                        // Nested satu level: sentence embedding
                        if (is_array($embedding[0]) && (is_float($embedding[0][0] ?? null) || is_int($embedding[0][0] ?? null))) {
                            return $this->normalizeEmbedding($embedding[0]);
                        }
                    }

                    Log::warning("Unexpected HuggingFace response structure", [
                        'type' => gettype($embedding),
                        'sample' => json_encode(array_slice($embedding ?? [], 0, 3)),
                    ]);
                    throw new RuntimeException("Format respons HuggingFace tidak dikenali.");
                }

                // Model masih loading (503)
                if ($response->status() === 503) {
                    $waitTime = $response->json('estimated_time') ?? 20;
                    Log::info("HuggingFace model loading, menunggu {$waitTime}s...");
                    sleep(min((int) ceil($waitTime), 30));
                    $attempt++;
                    continue;
                }

                // Rate limit (429)
                if ($response->status() === 429) {
                    Log::warning("HuggingFace rate limited, menunggu 5s...");
                    sleep(5);
                    $attempt++;
                    continue;
                }

                Log::warning("HuggingFace API error (attempt {$attempt}): HTTP {$response->status()} - {$response->body()}");
            } catch (RuntimeException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::warning("HuggingFace connection failed (attempt {$attempt}): " . $e->getMessage());
            }

            $attempt++;
            if ($attempt <= $maxRetries) {
                usleep(1000000); // 1s delay
            }
        }

        throw new RuntimeException(
            "Embedding service (HuggingFace) tidak tersedia setelah {$maxRetries} percobaan. " .
            "Pastikan HUGGINGFACE_API_TOKEN valid di .env"
        );
    }

    /**
     * Generate embedding untuk banyak teks sekaligus (batch).
     *
     * @param array $teksList Array of strings
     * @param string $mode 'query' atau 'passage'
     * @return array Array of arrays (setiap elemen = 768 float)
     * @throws RuntimeException
     */
    public function generateBatchEmbedding(array $teksList, string $mode = 'passage'): array
    {
        $prefix = $mode === 'query' ? 'query: ' : 'passage: ';
        $texts = array_map(fn($t) => $prefix . $t, $teksList);

        // HuggingFace Inference API mendukung batch via array inputs
        // Namun untuk free tier, kita proses dalam batch kecil
        $batchSize = 8; // HuggingFace free tier optimal
        $allEmbeddings = [];

        foreach (array_chunk($texts, $batchSize) as $batchIndex => $batch) {
            $maxRetries = 3;
            $attempt = 0;
            $batchResult = null;

            while ($attempt <= $maxRetries) {
                try {
                    $response = Http::timeout(120)
                        ->withHeaders([
                            'Authorization' => "Bearer {$this->apiToken}",
                            'Content-Type' => 'application/json',
                        ])
                        ->post($this->baseUrl, [
                            'inputs' => $batch,
                            'options' => [
                                'wait_for_model' => true,
                            ],
                        ]);

                    if ($response->successful()) {
                        $result = $response->json();

                        if (is_array($result) && !empty($result)) {
                            $batchEmbeddings = [];
                            foreach ($result as $item) {
                                if (is_array($item) && is_array($item[0] ?? null) && is_array($item[0][0] ?? null)) {
                                    // Token-level, perlu mean pooling per item
                                    $batchEmbeddings[] = $this->meanPooling($item);
                                } elseif (is_array($item) && (is_float($item[0] ?? null) || is_int($item[0] ?? null))) {
                                    $batchEmbeddings[] = $this->normalizeEmbedding($item);
                                } elseif (is_array($item) && is_array($item[0] ?? null)) {
                                    $batchEmbeddings[] = $this->normalizeEmbedding($item[0]);
                                }
                            }
                            $batchResult = $batchEmbeddings;
                            break;
                        }
                    }

                    if ($response->status() === 503) {
                        $waitTime = $response->json('estimated_time') ?? 20;
                        sleep(min((int) ceil($waitTime), 30));
                        $attempt++;
                        continue;
                    }

                    if ($response->status() === 429) {
                        sleep(5);
                        $attempt++;
                        continue;
                    }

                    Log::warning("HuggingFace batch error: HTTP {$response->status()}");
                } catch (\Exception $e) {
                    Log::warning("HuggingFace batch connection failed (attempt {$attempt}): " . $e->getMessage());
                }

                $attempt++;
                if ($attempt <= $maxRetries) {
                    usleep(1000000);
                }
            }

            if ($batchResult === null) {
                throw new RuntimeException("Batch embedding gagal setelah {$maxRetries} percobaan (batch #{$batchIndex})");
            }

            $allEmbeddings = array_merge($allEmbeddings, $batchResult);

            // Rate limiting: jeda antar batch
            if ($batchIndex < count(array_chunk($texts, $batchSize)) - 1) {
                usleep(200000); // 200ms delay antar batch
            }
        }

        return $allEmbeddings;
    }

    /**
     * Mean pooling untuk token-level embeddings.
     * Menghitung rata-rata semua token embedding menjadi satu vektor kalimat.
     */
    protected function meanPooling(array $tokenEmbeddings): array
    {
        $dim = count($tokenEmbeddings[0]);
        $numTokens = count($tokenEmbeddings);
        $result = array_fill(0, $dim, 0.0);

        foreach ($tokenEmbeddings as $token) {
            for ($i = 0; $i < $dim; $i++) {
                $result[$i] += $token[$i];
            }
        }

        for ($i = 0; $i < $dim; $i++) {
            $result[$i] /= $numTokens;
        }

        return $this->normalizeEmbedding($result);
    }

    /**
     * L2-normalize embedding vector (agar cosine similarity = dot product).
     */
    protected function normalizeEmbedding(array $embedding): array
    {
        $norm = 0.0;
        foreach ($embedding as $val) {
            $norm += $val * $val;
        }
        $norm = sqrt($norm);

        if ($norm == 0) return $embedding;

        return array_map(fn($v) => $v / $norm, $embedding);
    }
}
