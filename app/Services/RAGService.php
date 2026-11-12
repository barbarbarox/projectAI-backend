<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class RAGService
{
    protected RetrievalService $retrievalService;
    protected GeminiService $geminiService;
    protected InformationControlService $infoControlService;
    protected NVDRAGService $nvdRagService;

    public function __construct(
        RetrievalService $retrievalService,
        GeminiService $geminiService,
        InformationControlService $infoControlService,
        NVDRAGService $nvdRagService
    ) {
        $this->retrievalService   = $retrievalService;
        $this->geminiService      = $geminiService;
        $this->infoControlService = $infoControlService;
        $this->nvdRagService      = $nvdRagService;
    }

    public function getKonteks(string $query, int $topK = 3, ?string $src = null, bool $includeNvd = true): array
    {
        $chunks = $this->retrievalService->retrieve($query, $topK, $src);
        $evaluasi = $this->retrievalService->evaluasiKualitas($chunks);

        $konteks = '';
        $maxChars = 12000; // Sekitar 3000 token (estimasi 1 token = 4 chars)
        $charCount = 0;
        $finalChunks = [];

        foreach ($chunks as $chunk) {
            $chunkLength = strlen($chunk['content'] ?? '');
            if ($charCount + $chunkLength > $maxChars) {
                // Jika sudah melewati batas, truncate sisa teks dan hentikan
                $sisaChars = $maxChars - $charCount;
                if ($sisaChars > 100) {
                    $konteks .= mb_substr($chunk['content'], 0, $sisaChars) . "...(truncated)";
                    $finalChunks[] = $chunk;
                }
                break;
            }
            $konteks .= ($chunk['content'] ?? '') . "\n\n---\n\n";
            $charCount += $chunkLength;
            $finalChunks[] = $chunk;
        }

        $nvdChunks = [];
        if ($includeNvd) {
            try {
                $nvdResult = $this->nvdRagService->searchCVE($query, topK: $topK);
                if (!empty($nvdResult['chunks'])) {
                    $nvdChunks = $nvdResult['chunks'];
                    $konteks .= "\n\n=== REFERENSI CVE NVD TERBARU (2024–2026) ===\n" . ($nvdResult['konteks'] ?? '');
                    $finalChunks = array_merge($finalChunks, $nvdChunks);
                }
            } catch (\Exception $e) {
                Log::warning('[Dual RAG] NVD RAG retrieval gagal di getKonteks: ' . $e->getMessage());
            }
        }

        $estimatedTokens = ceil(strlen($konteks) / 4);

        Log::info('RAG Debug', [
            'General_Chunks' => count($chunks),
            'NVD_Chunks' => count($nvdChunks),
            'Total_Chunks' => count($finalChunks),
            'Context_Length' => strlen($konteks),
            'Estimated_Tokens' => $estimatedTokens,
            'Query' => $query
        ]);

        return [
            'konteks' => $konteks,
            'chunks' => $finalChunks,
            'general_chunks' => $chunks,
            'nvd_chunks' => $nvdChunks,
            'cukup' => $evaluasi['cukup'] || !empty($nvdChunks),
            'kualitas' => !empty($nvdChunks) ? 'tinggi' : $evaluasi['kualitas'],
            'peringatan' => $evaluasi['peringatan'],
            'estimasi_token' => $estimatedTokens
        ];
    }

    public function analisis(
        string $query,
        string $tugas,
        bool $useRag = true,
        ?string $modelOverride = null,
        ?int $userId = null,
        ?string $customKonteks = null,
        ?array $customChunks = null
    ): array
    {
        $modelOverride = !empty($modelOverride) ? trim($modelOverride) : null;
        $konteks    = '';
        $nvdKonteks = '';
        $peringatan = "Knowledge base tidak digunakan (Analisis berdasarkan AI dasar).";
        $allChunks  = [];

        if ($useRag) {
            if ($customKonteks !== null) {
                $konteks = $customKonteks;
                $peringatan = "Knowledge base terarah dan korelasi NVD CVE digunakan.";
                $allChunks = $customChunks ?? [];
            } else {
                // RAG #1: General knowledge base
                $ragData    = $this->getKonteks($query, topK: 3, includeNvd: false);
                $konteks    = $ragData['konteks'];
                $peringatan = $ragData['peringatan'] ?? "Knowledge base memiliki data yang cukup untuk analisis ini.";
                $allChunks  = $ragData['chunks'] ?? [];

                // RAG #2: NVD CVE database
                try {
                    $nvdRagData = $this->nvdRagService->searchCVE($query, topK: 5);
                    if (!empty($nvdRagData['konteks'])) {
                        $nvdKonteks = "\n\n=== REFERENSI CVE NVD (" . $nvdRagData['total'] . " entri relevan) ===\n" . $nvdRagData['konteks'];
                        $allChunks  = array_merge($allChunks, $nvdRagData['chunks'] ?? []);
                    }
                } catch (\Exception $e) {
                    Log::warning('[Dual RAG] NVD RAG gagal di RAGService: ' . $e->getMessage());
                }
            }
        }

        $prompt = $this->buildPrompt($konteks . $nvdKonteks, $tugas, $peringatan);
        try {
            $responseRaw = $this->geminiService->generate($prompt, $modelOverride, true, $userId);
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

        if ($useRag && !empty($allChunks)) {
            $hasil['rag_references'] = $allChunks;
        }

        return $this->infoControlService->filter($hasil);
    }

    protected function buildPrompt(string $konteks, string $tugas, string $peringatan): string
    {
        $konteksBlock = !empty(trim($konteks))
            ? "DATABASE PENGETAHUAN & REFERENSI KERENTANAN (RAG/CVE):\n{$konteks}\n"
            : "CATATAN: Knowledge base eksternal tidak aktif atau tidak ada referensi khusus.\n";

        return <<<PROMPT
SYSTEM:
Anda adalah Senior Penetration Tester, Red Team Specialist, dan Analis Keamanan Siber RedSim.
PENTING: Seluruh jawaban, ringkasan, nama kerentanan, dan narasi WAJIB ditulis dalam Bahasa Indonesia yang baku dan profesional.

{$konteksBlock}

PANDUAN ANALISIS:
- Manfaatkan referensi CVE, CWE, CAPEC, dan knowledge base di atas untuk memperkaya analisis kerentanan teknis dan skenario serangan.
- Evaluasi data target, fingerprint layanan/OS, dan indikator keamanan yang diberikan dalam Tugas Analisis secara mendalam.
- Jika terdeteksi komponen yang memiliki celah keamanan yang cocok dengan referensi CVE (seperti PHP2Shell, Brotli decoder RCE, SharePoint ToolShell, dsb), cantumkan CVE ID dan detail mitigasi konkret.

QUESTION / TUGAS ANALISIS:
{$tugas}
PROMPT;
    }
}
