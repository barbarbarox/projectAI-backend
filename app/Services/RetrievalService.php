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
     * Mengambil knowledge chunks yang paling relevan.
     * Mendukung 2 strategi:
     * - 'dense': Semantic search murni via Pinecone Integrated Embedding (multilingual-e5-large)
     * - 'hybrid': Gabungan Dense semantic search + BM25 keyword matching re-ranking
     *
     * @param string $query Teks pencarian
     * @param int $topK Jumlah hasil teratas
     * @param string|null $source Filter berdasarkan source
     * @return array
     */
    public function retrieve(string $query, int $topK = 3, ?string $source = null): array
    {
        $topK = min($topK, 5); // Maksimal 5 chunk

        $strategy = strtolower(env('RAG_STRATEGY', 'hybrid'));
        $semWeight = (float) env('RAG_HYBRID_SEMANTIC_WEIGHT', 0.7);
        $kwWeight = (float) env('RAG_HYBRID_KEYWORD_WEIGHT', 0.3);
        $minThreshold = (float) env('RAG_THRESHOLD_MINIMUM', 0.65);
        $tinggiThreshold = (float) env('RAG_THRESHOLD_TINGGI', 0.78);

        // Normalisasi bobot jika strategi hybrid
        if ($strategy === 'hybrid') {
            $totalWeight = $semWeight + $kwWeight;
            if ($totalWeight > 0) {
                $semWeight /= $totalWeight;
                $kwWeight /= $totalWeight;
            } else {
                $semWeight = 0.7;
                $kwWeight = 0.3;
            }
        }

        // STEP 1: Query kandidat ke Pinecone (Integrated Embedding)
        $filter = [];
        if ($source) {
            $filter = ['source' => ['$eq' => $source]];
        }

        // Ambil kandidat lebih banyak dari Pinecone untuk re-ranking
        $pineconeTopK = min($topK * 4, 25);
        $matches = $this->pineconeService->queryText($query, $pineconeTopK, $filter);

        if (empty($matches)) {
            Log::warning('Pinecone query returned no matches', ['query' => $query]);
            return [];
        }

        // STEP 2: Filter awal berdasarkan similarity minimum
        $candidates = [];
        foreach ($matches as $match) {
            $semScore = (float) ($match['score'] ?? 0);
            if ($semScore < $minThreshold) {
                continue;
            }

            $metadata = $match['metadata'] ?? [];
            $candidates[] = [
                'id' => $match['id'],
                'content' => $metadata['content'] ?? ($metadata['text'] ?? ''),
                'source' => $metadata['source'] ?? '',
                'source_id' => $metadata['source_id'] ?? null,
                'title' => $metadata['title'] ?? null,
                'metadata' => $metadata['extra'] ?? [],
                'sem_score' => round($semScore, 4),
            ];
        }

        if (empty($candidates)) {
            return [];
        }

        // STEP 3: Hitung skor sesuai strategi (Dense vs Hybrid)
        if ($strategy === 'hybrid') {
            // Hitung skor BM25 untuk semua kandidat
            $kwScores = $this->calculateBm25Scores($query, $candidates);

            foreach ($candidates as $idx => &$candidate) {
                $kwScore = $kwScores[$idx] ?? 0.0;
                $semScore = $candidate['sem_score'];
                $finalScore = ($semWeight * $semScore) + ($kwWeight * $kwScore);

                $candidate['kw_score'] = round($kwScore, 4);
                $candidate['final_score'] = round($finalScore, 4);
                $candidate['relevansi'] = $finalScore >= $tinggiThreshold ? 'tinggi' : 'sedang';
                $candidate['strategy'] = 'hybrid';
            }
            unset($candidate);
        } else {
            // Dense-only
            foreach ($candidates as &$candidate) {
                $candidate['kw_score'] = 0.0;
                $candidate['final_score'] = $candidate['sem_score'];
                $candidate['relevansi'] = $candidate['sem_score'] >= $tinggiThreshold ? 'tinggi' : 'sedang';
                $candidate['strategy'] = 'dense';
            }
            unset($candidate);
        }

        // STEP 4: Deduplikasi per source_id jika ada
        $deduped = [];
        $seenSourceIds = [];
        foreach ($candidates as $item) {
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

        // STEP 5: Re-rank & sorting berdasarkan final_score tertinggi
        usort($deduped, fn($a, $b) => $b['final_score'] <=> $a['final_score']);

        $finalChunks = array_slice($deduped, 0, $topK);

        Log::info('Retrieval Debug (Pinecone RAG)', [
            'Strategy' => $strategy,
            'Retrieved_Chunks_Count' => count($finalChunks),
            'Top_Sources' => array_map(fn($c) => $c['title'] ?? 'N/A', $finalChunks),
            'Final_Scores' => array_map(fn($c) => $c['final_score'], $finalChunks),
            'Semantic_Scores' => array_map(fn($c) => $c['sem_score'], $finalChunks),
            'Keyword_Scores' => array_map(fn($c) => $c['kw_score'] ?? 0.0, $finalChunks),
        ]);

        return $finalChunks;
    }

    /**
     * Hitung BM25 scores untuk sekumpulan kandidat dokumen terhadap query.
     * Menggunakan parameter standar Okapi BM25: k1 = 1.2, b = 0.75.
     * Hasil dinormalisasi ke rentang [0.0, 1.0].
     *
     * @param string $query
     * @param array $candidates List of documents with 'title' and 'content'
     * @return array List of normalized float scores in the same order as candidates
     */
    protected function calculateBm25Scores(string $query, array $candidates): array
    {
        $n = count($candidates);
        if ($n === 0) {
            return [];
        }

        $queryTokens = $this->tokenize($query);
        if (empty($queryTokens)) {
            return array_fill(0, $n, 0.0);
        }

        // Tokenize semua dokumen kandidat (gabungkan title + content untuk relevance maksimal)
        $docTokens = [];
        $docLengths = [];
        $totalLength = 0;

        foreach ($candidates as $c) {
            $text = ($c['title'] ?? '') . ' ' . ($c['content'] ?? '');
            $tokens = $this->tokenize($text);
            $docTokens[] = array_count_values($tokens);
            $len = count($tokens);
            $docLengths[] = $len;
            $totalLength += $len;
        }

        $avgdl = $totalLength / max(1, $n);

        // Hitung Document Frequency (DF) untuk setiap term query
        $df = [];
        foreach ($queryTokens as $term) {
            $count = 0;
            foreach ($docTokens as $tfMap) {
                if (isset($tfMap[$term])) {
                    $count++;
                }
            }
            $df[$term] = $count;
        }

        // BM25 parameters
        $k1 = 1.2;
        $b = 0.75;

        $rawScores = [];
        foreach ($candidates as $i => $c) {
            $score = 0.0;
            $tfMap = $docTokens[$i];
            $dl = $docLengths[$i];

            foreach ($queryTokens as $term) {
                if (!isset($tfMap[$term])) {
                    continue;
                }

                $termFreq = $tfMap[$term];
                $docFreq = $df[$term] ?? 0;

                // Okapi BM25 IDF: ln(1 + (N - df + 0.5) / (df + 0.5))
                $idf = log(1.0 + ($n - $docFreq + 0.5) / ($docFreq + 0.5));

                // BM25 term weight
                $num = $termFreq * ($k1 + 1.0);
                $denom = $termFreq + $k1 * (1.0 - $b + $b * ($dl / max(1.0, $avgdl)));
                $score += $idf * ($num / max(0.001, $denom));
            }

            // Bonus exact match query string di dalam judul atau isi
            $fullDocText = strtolower(($c['title'] ?? '') . ' ' . ($c['content'] ?? ''));
            if (str_contains($fullDocText, strtolower(trim($query)))) {
                $score *= 1.25;
            }

            $rawScores[$i] = $score;
        }

        // Normalisasi ke rentang [0.0, 1.0]
        $maxScore = max($rawScores);
        if ($maxScore <= 0) {
            return array_fill(0, $n, 0.0);
        }

        return array_map(fn($s) => round($s / $maxScore, 4), $rawScores);
    }

    /**
     * Tokenisasi teks untuk BM25 matching (lowercase, stopword filter).
     *
     * @param string $text
     * @return array
     */
    protected function tokenize(string $text): array
    {
        $clean = preg_replace('/[^a-zA-Z0-9_\-]/', ' ', strtolower($text));
        $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);

        $stopwords = [
            'the', 'is', 'at', 'which', 'on', 'in', 'a', 'an', 'and', 'or', 'to', 'of',
            'for', 'with', 'by', 'from', 'be', 'it', 'as', 'that', 'this', 'are', 'was',
            'dan', 'di', 'ke', 'dari', 'yang', 'untuk', 'pada', 'adalah', 'ini', 'itu',
            'dengan', 'atau', 'dalam', 'bisa', 'dapat', 'oleh', 'akan', 'juga'
        ];
        $stopwordsMap = array_flip($stopwords);

        $filtered = [];
        foreach ($words as $w) {
            if (strlen($w) >= 2 && !isset($stopwordsMap[$w])) {
                $filtered[] = $w;
            }
        }

        return $filtered;
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
