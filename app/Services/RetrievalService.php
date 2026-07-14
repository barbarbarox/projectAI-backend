<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RetrievalService
{
    protected PineconeService $pineconeService;

    public function __construct(PineconeService $pineconeService)
    {
        $this->pineconeService = $pineconeService;
    }

    /**
     * Mengambil knowledge chunks yang paling relevan menggunakan Pinecone Integrated Embedding.
     * Strategi: semantic search via Pinecone text query (tanpa perlu HuggingFace API).
     *
     * @param string $query Teks pencarian
     * @param int $topK Jumlah hasil teratas
     * @param string|null $source Filter berdasarkan source
     * @return array
     */
    public function retrieve(string $query, int $topK = 3, ?string $source = null): array
    {
        $topK = min($topK, 5); // Maksimal 5 chunk

        // STEP 1: Query langsung ke Pinecone dengan teks (Integrated Embedding)
        $filter = [];
        if ($source) {
            $filter = ['source' => ['$eq' => $source]];
        }

        // Ambil lebih banyak kandidat dari Pinecone untuk re-ranking
        $pineconeTopK = min($topK * 3, 20);
        $matches = $this->pineconeService->queryText($query, $pineconeTopK, $filter);

        if (empty($matches)) {
            Log::warning('Pinecone query returned no matches', ['query' => $query]);
            return [];
        }

        // STEP 2: Filter dan format hasil
        $scored = [];
        foreach ($matches as $match) {
            $semScore = $match['score'] ?? 0; // Pinecone cosine similarity score
            $metadata = $match['metadata'] ?? [];

            // Filter threshold — buang jika semScore < minimum
            if ($semScore < (float) env('RAG_THRESHOLD_MINIMUM', 0.65)) {
                continue;
            }

            $scored[] = [
                'id' => $match['id'],
                'content' => $metadata['content'] ?? ($metadata['text'] ?? ''),
                'source' => $metadata['source'] ?? '',
                'source_id' => $metadata['source_id'] ?? null,
                'title' => $metadata['title'] ?? null,
                'metadata' => $metadata['extra'] ?? [],
                'sem_score' => round($semScore, 4),
                'final_score' => round($semScore, 4),
                'relevansi' => $semScore >= (float) env('RAG_THRESHOLD_TINGGI', 0.78) ? 'tinggi' : 'sedang',
            ];
        }

        // STEP 3: Deduplikasi per source_id
        $deduped = [];
        $seenSourceIds = [];
        foreach ($scored as $item) {
            $sid = $item['source_id'];
            if ($sid && isset($seenSourceIds[$sid])) {
                if ($item['final_score'] > $deduped[$seenSourceIds[$sid]]['final_score']) {
                    $deduped[$seenSourceIds[$sid]] = $item;
                }
            } else {
                $seenSourceIds[$sid] = count($deduped);
                $deduped[] = $item;
            }
        }

        // STEP 4: Sort dan ambil top-k
        usort($deduped, fn($a, $b) => $b['final_score'] <=> $a['final_score']);

        $finalChunks = array_slice($deduped, 0, $topK);

        Log::info('Retrieval Debug (Pinecone Integrated)', [
            'Retrieved_Chunks_Count' => count($finalChunks),
            'Top_Sources' => array_map(fn($c) => $c['title'] ?? 'N/A', $finalChunks),
            'Similarity_Scores' => array_map(fn($c) => $c['sem_score'], $finalChunks)
        ]);

        return $finalChunks;
    }

    /**
     * Evaluasi kualitas chunks yang diambil.
     */
    public function evaluasiKualitas(array $chunks): array
    {
        $tinggiThreshold = (float) env('RAG_THRESHOLD_TINGGI', 0.78);
        $sedangThreshold = (float) env('RAG_THRESHOLD_SEDANG', 0.65);

        $hasTinggi = false;
        $hasSedang = false;

        foreach ($chunks as $chunk) {
            $sem = $chunk['sem_score'] ?? 0;
            if ($sem >= $tinggiThreshold) $hasTinggi = true;
            if ($sem >= $sedangThreshold) $hasSedang = true;
        }

        if ($hasTinggi) {
            return [
                'cukup' => true,
                'kualitas' => 'tinggi',
                'peringatan' => null,
            ];
        }

        if ($hasSedang) {
            return [
                'cukup' => true,
                'kualitas' => 'sedang',
                'peringatan' => 'Beberapa referensi memiliki relevansi sedang. Verifikasi manual disarankan.',
            ];
        }

        return [
            'cukup' => false,
            'kualitas' => 'tidak_cukup',
            'peringatan' => 'Knowledge base tidak memiliki data yang cukup untuk area ini. Penilaian berikut bersifat indikatif dan WAJIB divalidasi oleh security professional.',
        ];
    }
}
