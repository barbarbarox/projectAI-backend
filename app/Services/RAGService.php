<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class RAGService
{
    protected RetrievalService $retrievalService;
    protected GeminiService $geminiService;
    protected InformationControlService $infoControlService;

    public function __construct(
        RetrievalService $retrievalService,
        GeminiService $geminiService,
        InformationControlService $infoControlService
    ) {
        $this->retrievalService = $retrievalService;
        $this->geminiService = $geminiService;
        $this->infoControlService = $infoControlService;
    }

    public function getKonteks(string $query, int $topK = 3, ?string $src = null): array
    {
        $chunks = $this->retrievalService->retrieve($query, $topK, $src);
        $evaluasi = $this->retrievalService->evaluasiKualitas($chunks);

        $konteksArray = array_column($chunks, 'content');
        $konteks = '';
        $maxChars = 12000; // Sekitar 3000 token (estimasi 1 token = 4 chars)
        $charCount = 0;
        $finalChunks = [];

        foreach ($chunks as $chunk) {
            $chunkLength = strlen($chunk['content']);
            if ($charCount + $chunkLength > $maxChars) {
                // Jika sudah melewati batas, truncate sisa teks dan hentikan
                $sisaChars = $maxChars - $charCount;
                if ($sisaChars > 100) {
                    $konteks .= mb_substr($chunk['content'], 0, $sisaChars) . "...(truncated)";
                    $finalChunks[] = $chunk;
                }
                break;
            }
            $konteks .= $chunk['content'] . "\n\n---\n\n";
            $charCount += $chunkLength;
            $finalChunks[] = $chunk;
        }

        $estimatedTokens = ceil(strlen($konteks) / 4);

        Log::info('RAG Debug', [
            'Retrieved_Chunks' => count($finalChunks),
            'Context_Length' => strlen($konteks),
            'Estimated_Tokens' => $estimatedTokens,
            'Query' => $query
        ]);

        return [
            'konteks' => $konteks,
            'chunks' => $finalChunks,
            'cukup' => $evaluasi['cukup'],
            'kualitas' => $evaluasi['kualitas'],
            'peringatan' => $evaluasi['peringatan'],
            'estimasi_token' => $estimatedTokens
        ];
    }

    public function analisis(string $query, string $tugas, bool $useRag = true, ?string $modelOverride = null): array
    {
        $konteks = '';
        $peringatan = "Knowledge base tidak digunakan (Analisis berdasarkan AI dasar).";
        
        if ($useRag) {
            $ragData = $this->getKonteks($query);
            $konteks = $ragData['konteks'];
            $peringatan = $ragData['peringatan'] ?? "Knowledge base memiliki data yang cukup untuk analisis ini.";
        }

        $prompt = $this->buildPrompt($konteks, $tugas, $peringatan);
        try {
            $responseRaw = $this->geminiService->generate($prompt, $modelOverride);
            $hasil = json_decode($responseRaw, true);
            
            if (!is_array($hasil)) {
                Log::error('Gagal parse response AI: ' . substr($responseRaw, 0, 500));
                $hasil = [
                    'skor_keamanan' => 50, 'verdict' => 'perhatian',
                    'ringkasan_eksekutif' => 'Analisis tidak dapat diproses. Response AI tidak valid.',
                    'ringkasan_teknis' => 'Response AI tidak berformat JSON.',
                    'confidence_score' => 0.0, 'temuan' => [], 'simulasi_serangan' => [],
                ];
            }
        } catch (\Exception $e) {
            Log::error('AI Error di RAGService: ' . $e->getMessage());
            
            $pesanError = 'Gagal terhubung ke penyedia AI.';
            if (str_contains($e->getMessage(), '429')) {
                $pesanError = 'Limit API AI telah tercapai (Rate Limit / Quota Exceeded). Mohon tunggu beberapa saat atau ganti API key.';
            } elseif (str_contains($e->getMessage(), '401') || str_contains($e->getMessage(), '403')) {
                $pesanError = 'API key AI tidak valid atau ditolak.';
            }

            $hasil = [
                'skor_keamanan' => 50, 'verdict' => 'perhatian',
                'ringkasan_eksekutif' => "⚠️ Sistem gagal melakukan analisis AI: $pesanError",
                'ringkasan_teknis' => "Error Detail: " . $e->getMessage(),
                'confidence_score' => 0.0, 'temuan' => [], 'simulasi_serangan' => [],
            ];
        }

        if ($useRag && isset($ragData['chunks'])) {
            $hasil['rag_references'] = $ragData['chunks'];
        }

        return $this->infoControlService->filter($hasil);
    }

    protected function buildPrompt(string $konteks, string $tugas, string $peringatan): string
    {
        return <<<PROMPT
SYSTEM:
Anda adalah analis keamanan siber profesional RedSim.
PENTING: Seluruh jawaban, ringkasan, nama kerentanan, dan narasi WAJIB ditulis dalam Bahasa Indonesia yang baku dan profesional.

Gunakan hanya informasi dari context yang diberikan.
Jika informasi tidak ditemukan dalam context, katakan bahwa data tidak tersedia.

CONTEXT:
{$konteks}

QUESTION:
Tugas Analisis:
{$tugas}

Keluarkan OUTPUT dalam JSON valid:
{"skor_keamanan":0-100,"verdict":"aman|perhatian|berbahaya","ringkasan_eksekutif":"string","ringkasan_teknis":"string","confidence_score":0.0-1.0,"temuan":[{"tipe":"kerentanan|miskonfigurasi|eksposur_data|ketergantungan_rentan|kriptografi_lemah|injeksi|autentikasi_lemah|otorisasi_lemah|lainnya","tingkat_keparahan":"kritis|tinggi|sedang|rendah|info","judul":"string","deskripsi":"string","lokasi":"string","kode_rentan":"string","remediasi":"string"}],"simulasi_serangan":[{"nama_skenario":"string","narasi_teknis":"string","skor_kemungkinan":0.0-1.0,"skor_dampak":0.0-1.0}]}
PROMPT;
    }
}
