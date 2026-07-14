<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Generator;

class IngestionService
{
    protected ChunkingService $chunkingService;
    protected PineconeService $pineconeService;

    public function __construct(
        ChunkingService $chunkingService,
        PineconeService $pineconeService
    ) {
        $this->chunkingService = $chunkingService;
        $this->pineconeService = $pineconeService;
    }

    /**
     * Ingest data dari Generator ke Pinecone (Integrated Embedding) + knowledge_chunks (PostgreSQL).
     *
     * @param string $source Nama sumber (cisa-kev, attck, cwe, capec, owasp-cheatsheet, nvd-cve)
     * @param Generator $items Generator yang yield item dataset
     * @param callable|null $progressCallback Callback untuk progress reporting
     * @return array ['berhasil' => int, 'gagal' => int]
     */
    public function ingestDariGenerator(string $source, Generator $items, ?callable $progressCallback = null): array
    {
        $stats = ['berhasil' => 0, 'gagal' => 0];
        $batch = [];

        foreach ($items as $item) {
            $chunks = $this->chunkingService->chunk($item['teks']);

            foreach ($chunks as $chunkIdx => $chunk) {
                $batch[] = [
                    'teks' => $chunk,
                    'source' => $source,
                    'source_id' => $item['source_id'] ?? null,
                    'title' => $item['title'] ?? null,
                    'metadata' => $item['metadata'] ?? [],
                    'index' => $chunkIdx,
                ];

                if (count($batch) >= 50) {
                    $this->prosesBatch($batch, $stats);

                    if ($progressCallback && $stats['berhasil'] % 100 < 50) {
                        $progressCallback($source, $stats['berhasil']);
                    }

                    $batch = [];
                }
            }
        }

        // Proses sisa batch
        if (!empty($batch)) {
            $this->prosesBatch($batch, $stats);
        }

        return $stats;
    }

    /**
     * Proses satu batch: kirim teks langsung ke Pinecone (Integrated Embedding)
     * + simpan metadata ke PostgreSQL.
     */
    protected function prosesBatch(array $batch, array &$stats): void
    {
        try {
            // Siapkan data untuk Pinecone Integrated Embedding (kirim teks langsung)
            $pineconeRecords = [];
            $dbRows = [];

            foreach ($batch as $i => $item) {
                $vectorId = $this->generateVectorId($item['source'], $item['source_id'], $item['index']);

                $pineconeRecords[] = [
                    'id' => $vectorId,
                    'text' => $item['teks'], // Pinecone akan embed teks ini secara otomatis
                    'metadata' => [
                        'source' => $item['source'],
                        'source_id' => $item['source_id'] ?? '',
                        'title' => $item['title'] ?? '',
                        'chunk_index' => (string) $item['index'],
                    ],
                ];

                // Simpan juga ke PostgreSQL (untuk referensi/backup)
                $dbRows[] = [
                    'content' => $item['teks'],
                    'embedding' => '[]', // Tidak perlu simpan embedding di DB lagi
                    'source' => $item['source'],
                    'source_id' => $item['source_id'],
                    'title' => $item['title'],
                    'metadata' => json_encode($item['metadata']),
                    'chunk_index' => $item['index'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Upsert teks ke Pinecone (Integrated Embedding — Pinecone otomatis embed)
            $pineconeResult = $this->pineconeService->upsertText($pineconeRecords);

            // Insert ke PostgreSQL (knowledge_chunks)
            DB::table('knowledge_chunks')->insert($dbRows);

            $stats['berhasil'] += count($batch);

            Log::info("Batch ingested: " . count($pineconeRecords) . " records ke Pinecone (Integrated), " . count($dbRows) . " rows ke DB");
        } catch (\Exception $e) {
            Log::warning("Batch gagal: " . $e->getMessage());
            $stats['gagal'] += count($batch);
        }
    }

    /**
     * Generate ID unik untuk vector di Pinecone.
     */
    protected function generateVectorId(string $source, ?string $sourceId, int $chunkIndex): string
    {
        $base = $source . ':' . ($sourceId ?? 'unknown') . ':' . $chunkIndex;
        return md5($base); // 32-char hex ID yang konsisten
    }
}
