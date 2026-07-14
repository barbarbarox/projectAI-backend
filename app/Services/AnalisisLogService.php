<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AnalisisLogService
{
    protected LogParserService $parser;
    protected LogAnomalyDetectorService $detector;
    protected RAGService $ragService;

    public function __construct(LogParserService $parser, LogAnomalyDetectorService $detector, RAGService $ragService)
    {
        $this->parser = $parser;
        $this->detector = $detector;
        $this->ragService = $ragService;
    }

    public function analisis(string $content, string $tipeLog = 'auto', bool $useAi = true, bool $useRag = true, ?string $modelOverride = null): array
    {
        // Step 1: Detect format
        $format = ($tipeLog === 'auto' || $tipeLog === 'Deteksi Otomatis')
            ? $this->parser->detectFormat($content)
            : $this->mapTipeToFormat($tipeLog);

        // Step 2: Parse
        $entries = $this->parser->parse($content, $format);
        if (empty($entries)) {
            return ['error' => 'Tidak ada entry log yang berhasil diparsing.', 'format' => $format, 'stats' => ['total_baris' => 0]];
        }

        // Step 3: Detect anomalies
        $anomalies = $this->detector->detect($entries);
        $stats = $this->detector->buildStats($entries, $anomalies);
        $summary = $this->detector->buildSummary($anomalies);

        // Step 4: RAG + AI (Only if useAi is true)
        if (!$useAi) {
            $hasil = $this->fallbackResult($stats, $anomalies);
        } else {
            $tugas = $this->buildLogPrompt($format, $stats, $anomalies, $summary, $entries);

            try {
                $ragData = ['konteks' => '', 'peringatan' => null, 'chunks' => []];
                if ($useRag) {
                    $ragData = $this->ragService->getKonteks($summary);
                } else {
                    $ragData['peringatan'] = "Knowledge base tidak digunakan (Analisis berdasarkan AI dasar).";
                }

                $prompt = $this->buildFullPrompt($ragData['konteks'], $tugas, $ragData['peringatan'] ?? null);

                $gemini = app(GeminiService::class);
                $responseRaw = $gemini->generate($prompt, $modelOverride);
                $hasil = json_decode($responseRaw, true);

                if (!is_array($hasil)) {
                    Log::error('Log analysis: Gagal parse AI response');
                    $hasil = $this->fallbackResult($stats, $anomalies);
                }

                // Propagate RAG references
                if ($useRag && !empty($ragData['chunks'])) {
                    $hasil['rag_references'] = $ragData['chunks'];
                }
            } catch (\Exception $e) {
                Log::error('Log analysis AI error: ' . $e->getMessage());
                $hasil = $this->fallbackResult($stats, $anomalies);
            }
        }

        $hasil['format_terdeteksi'] = $format;
        $hasil['stats'] = $stats;
        $hasil['anomali_mentah'] = $anomalies;

        return $hasil;
    }

    protected function mapTipeToFormat(string $tipe): string
    {
        return match ($tipe) {
            'Apache Access Log' => 'apache',
            'Nginx Access Log', 'Nginx Error Log' => 'nginx',
            'Laravel Application Log' => 'laravel',
            'Linux auth.log / syslog' => 'authlog',
            'Windows Event Log' => 'windows_event',
            default => 'custom',
        };
    }

    protected function buildLogPrompt(string $format, array $stats, array $anomalies, string $summary, array $entries): string
    {
        $allAnomalies = array_merge($anomalies['brute_force'] ?? [], $anomalies['scanning'] ?? [], $anomalies['injection'] ?? [], $anomalies['suspicious_ua'] ?? []);

        $anomalyText = '';
        foreach (array_slice($allAnomalies, 0, 30) as $a) {
            $anomalyText .= "- [{$a['tipe']}] {$a['keterangan']}\n";
        }

        $sampleEntries = '';
        foreach (array_slice($entries, 0, 10) as $e) {
            $sampleEntries .= ($e['raw'] ?? '') . "\n";
        }

        return <<<TUGAS
Analisis log keamanan format: {$format}
Total baris: {$stats['total_baris']}
Total anomali: {$stats['total_anomali']}

ANOMALI TERDETEKSI:
{$anomalyText}

SAMPLE LOG ENTRIES:
{$sampleEntries}

Berikan analisis insiden keamanan lengkap dalam format JSON:
{
  "ringkasan_insiden": "narasi 2-3 paragraf",
  "tingkat_keparahan": "kritis|tinggi|sedang|rendah",
  "skor_keamanan": 0-100,
  "verdict": "aman|perhatian|berbahaya",
  "confidence_score": 0.0-1.0,
  "incident_timeline": [{"waktu":"timestamp","kejadian":"deskripsi","indicator":"anomali"}],
  "ioc_list": [{"tipe":"ip|url|user_agent|pattern","nilai":"string","keterangan":"alasan mencurigakan"}],
  "attck_mapping": [{"technique_id":"Txxxx","nama":"nama teknik","relevansi":"penjelasan"}],
  "rekomendasi": [{"prioritas":"segera|minggu_ini|bulan_ini","tindakan":"apa","alasan":"kenapa"}],
  "ringkasan_eksekutif": "ringkasan non-teknis",
  "ringkasan_teknis": "ringkasan untuk developer"
}
TUGAS;
    }

    protected function buildFullPrompt(string $konteks, string $tugas, ?string $peringatan): string
    {
        $warn = $peringatan ?? 'Knowledge base memiliki data yang cukup.';
        return <<<PROMPT
Kamu adalah analis keamanan siber profesional RedSim, spesialis forensik log.
Berikan analisis yang akurat dan berbasis data.

ATURAN:
1. Gunakan HANYA informasi dari KNOWLEDGE BASE di bawah.
2. DILARANG mengarang CVE/CWE/ATT&CK ID yang tidak ada di knowledge base.
3. Jika tidak cukup data: nyatakan ketidakpastian.
4. Tulis SEMUA output dalam Bahasa Indonesia.
5. confidence_score: data cukup 0.75-1.0, sedang 0.50-0.74, tidak cukup 0.20-0.49

STATUS KNOWLEDGE BASE:
{$warn}

KNOWLEDGE BASE:
{$konteks}

TUGAS:
{$tugas}

OUTPUT JSON valid saja, tanpa markdown.
PROMPT;
    }

    protected function fallbackResult(array $stats, array $anomalies): array
    {
        $total = $stats['total_anomali'] ?? 0;
        $severity = $total > 20 ? 'tinggi' : ($total > 5 ? 'sedang' : 'rendah');
        $verdict = $total > 20 ? 'berbahaya' : ($total > 5 ? 'perhatian' : 'aman');
        $skor = max(0, 100 - ($total * 3));

        return [
            'ringkasan_insiden' => "Ditemukan {$total} anomali dalam log. Analisis AI tidak tersedia.",
            'tingkat_keparahan' => $severity,
            'skor_keamanan' => $skor,
            'verdict' => $verdict,
            'confidence_score' => 0.3,
            'incident_timeline' => [],
            'ioc_list' => [],
            'attck_mapping' => [],
            'rekomendasi' => [],
            'ringkasan_eksekutif' => "Ditemukan {$total} anomali keamanan.",
            'ringkasan_teknis' => 'Analisis AI gagal. Lihat data anomali mentah.',
        ];
    }
}
