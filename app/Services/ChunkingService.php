<?php

namespace App\Services;

class ChunkingService
{
    /**
     * Memecah teks panjang menjadi potongan kecil yang bermakna.
     * Menggunakan algoritma recursive chunking.
     *
     * @param string $teks Teks yang akan dipecah
     * @param int $chunkSize Ukuran maksimum chunk
     * @param int $overlap Jumlah karakter overlap antar chunk
     * @return array Array of string chunks
     */
    public function chunk(string $teks, int $chunkSize = 600, int $overlap = 100): array
    {
        // Hapus whitespace berlebihan
        $teks = preg_replace('/\s+/', ' ', trim($teks));

        if (strlen($teks) <= $chunkSize) {
            return strlen($teks) >= 100 ? [$teks] : [];
        }

        $rawChunks = $this->recursiveSplit($teks, $chunkSize);

        // Gabungkan potongan kecil (< 100 karakter) dengan sebelumnya
        $merged = [];
        foreach ($rawChunks as $chunk) {
            $chunk = trim($chunk);
            if (empty($chunk)) continue;

            if (count($merged) > 0 && strlen($chunk) < 100) {
                $merged[count($merged) - 1] .= ' ' . $chunk;
            } else {
                $merged[] = $chunk;
            }
        }

        // Terapkan overlap
        $result = [];
        for ($i = 0; $i < count($merged); $i++) {
            $current = $merged[$i];

            if ($i > 0 && $overlap > 0) {
                $prevChunk = $merged[$i - 1];
                $overlapText = substr($prevChunk, -$overlap);
                $current = $overlapText . ' ' . $current;
            }

            $current = trim($current);

            // Validasi: setiap chunk antara 100 - 800 karakter
            if (strlen($current) >= 100 && strlen($current) <= 800) {
                $result[] = $current;
            } elseif (strlen($current) > 800) {
                $result[] = substr($current, 0, 800);
            }
            // Buang chunk < 100 karakter
        }

        return $result;
    }

    /**
     * Split teks secara rekursif menggunakan separator bertingkat.
     */
    protected function recursiveSplit(string $teks, int $chunkSize): array
    {
        if (strlen($teks) <= $chunkSize) {
            return [$teks];
        }

        $separators = [
            "/\n#{1,2} /",     // Heading Markdown
            "/\n\n/",          // Paragraf baru
            "/\n/",            // Baris baru
            "/[.!?] /",       // Akhir kalimat
            "/ /",             // Spasi (last resort)
        ];

        foreach ($separators as $separator) {
            $parts = preg_split($separator, $teks, -1, PREG_SPLIT_NO_EMPTY);

            if (count($parts) > 1) {
                $chunks = [];
                $currentChunk = '';

                foreach ($parts as $part) {
                    $part = trim($part);
                    if (empty($part)) continue;

                    if (strlen($currentChunk . ' ' . $part) <= $chunkSize) {
                        $currentChunk = $currentChunk ? $currentChunk . ' ' . $part : $part;
                    } else {
                        if (!empty($currentChunk)) {
                            $chunks[] = $currentChunk;
                        }
                        // Jika bagian ini sendiri > chunkSize, split lagi
                        if (strlen($part) > $chunkSize) {
                            $subChunks = $this->recursiveSplit($part, $chunkSize);
                            $chunks = array_merge($chunks, $subChunks);
                            $currentChunk = '';
                        } else {
                            $currentChunk = $part;
                        }
                    }
                }

                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                }

                if (count($chunks) > 0) {
                    return $chunks;
                }
            }
        }

        // Fallback: hard split
        $chunks = [];
        for ($i = 0; $i < strlen($teks); $i += $chunkSize) {
            $chunks[] = substr($teks, $i, $chunkSize);
        }
        return $chunks;
    }
}
