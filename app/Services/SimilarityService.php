<?php

namespace App\Services;

class SimilarityService
{
    /**
     * Hitung cosine similarity antara dua vector embedding.
     * Karena embedding sudah dinormalisasi (magnitude = 1),
     * cukup menggunakan dot product.
     *
     * @param array $vectorA Array 768 float
     * @param array $vectorB Array 768 float
     * @return float Nilai antara -1.0 sampai 1.0
     */
    public function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dotProduct = 0.0;
        $count = min(count($vectorA), count($vectorB));

        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
        }

        return (float) $dotProduct;
    }

    /**
     * Rank chunks berdasarkan similarity terhadap query embedding.
     *
     * @param array $queryEmbedding Array 768 float
     * @param array $chunks Array of objects with 'embedding' field (JSON string)
     * @return array Chunks yang sudah di-sort DESC berdasarkan similarity
     */
    public function rankChunks(array $queryEmbedding, array $chunks): array
    {
        $ranked = [];

        foreach ($chunks as $chunk) {
            $embeddingChunk = is_string($chunk->embedding)
                ? json_decode($chunk->embedding, true)
                : $chunk->embedding;

            $similarity = $this->cosineSimilarity($queryEmbedding, $embeddingChunk);

            $item = (array) $chunk;
            $item['similarity'] = $similarity;
            $ranked[] = $item;
        }

        usort($ranked, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return $ranked;
    }
}
