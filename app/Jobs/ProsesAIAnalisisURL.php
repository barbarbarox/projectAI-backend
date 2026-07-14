<?php

namespace App\Jobs;

use App\Models\Scan;
use App\Models\Temuan;
use App\Models\SimulasiSerangan;
use App\Services\RAGService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProsesAIAnalisisURL implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 180;

    public function __construct(protected Scan $scan) {}

    public function handle(RAGService $ragService): void
    {
        try {
            $dataMentah = $this->scan->data_mentah ?? [];
            if (empty($dataMentah)) {
                throw new \RuntimeException('Data mentah scan tidak tersedia.');
            }

            // Build deskripsi untuk RAG query
            $teknologi = implode(', ', $dataMentah['teknologi'] ?? []);
            $status = $dataMentah['status'] ?? 'unknown';
            $headerScore = $dataMentah['security_headers']['skor'] ?? 0;
            $vtMalicious = $dataMentah['virustotal']['malicious_count'] ?? 0;

            // Common files yang terekspos
            $exposed = [];
            foreach ($dataMentah['common_files'] ?? [] as $f) {
                if ($f['terekspos'] ?? false) $exposed[] = $f['path'];
            }

            $queryRAG = "web security vulnerabilities {$teknologi}";
            if (!empty($exposed)) {
                $queryRAG .= ' exposed files: ' . implode(', ', $exposed);
            }

            $tugas = <<<TUGAS
Analisis keamanan URL: {$this->scan->target}

Data scan:
- Status reputasi: {$status}
- Skor security headers: {$headerScore}/100
- VirusTotal flags: {$vtMalicious} vendor
- Teknologi terdeteksi: {$teknologi}
- File sensitif terekspos: {$this->formatExposedFiles($dataMentah)}
- SSL: {$this->formatSSL($dataMentah)}
- Headers sensitif: {$this->formatSensitiveHeaders($dataMentah)}

Data lengkap scan:
{$this->formatFullData($dataMentah)}

Berikan penilaian keamanan lengkap termasuk risiko spesifik berdasarkan data di atas.
TUGAS;

            $hasil = $ragService->analisis($queryRAG, $tugas);

            $this->scan->update([
                'verdict' => $hasil['verdict'] ?? null,
                'ringkasan_eksekutif' => $hasil['ringkasan_eksekutif'] ?? null,
                'ringkasan_teknis' => $hasil['ringkasan_teknis'] ?? null,
                'confidence_score' => $hasil['confidence_score'] ?? null,
                'skor_keamanan' => $hasil['skor_keamanan'] ?? $this->scan->skor_keamanan,
            ]);

            foreach ($hasil['temuan'] ?? [] as $t) {
                Temuan::create(array_merge(['scan_id' => $this->scan->id], array_intersect_key($t, array_flip([
                    'tipe','tingkat_keparahan','judul','deskripsi','lokasi','nomor_baris',
                    'kode_rentan','kode_aman','cve_id','cwe_id','teknik_attck',
                    'remediasi','estimasi_usaha','tingkat_kepercayaan',
                ]))));
            }

            foreach ($hasil['simulasi_serangan'] ?? [] as $s) {
                SimulasiSerangan::create(array_merge(['scan_id' => $this->scan->id], array_intersect_key($s, array_flip([
                    'nama_skenario','profil_penyerang','narasi_teknis','narasi_eksekutif',
                    'skor_kemungkinan','skor_dampak','rantai_serangan','fase_attck',
                ]))));
            }
        } catch (\Exception $e) {
            Log::error("AI Analisis URL gagal: " . $e->getMessage());
            $this->scan->update(['error_message' => 'Analisis AI gagal: ' . $e->getMessage()]);
        }
    }

    protected function formatExposedFiles(array $data): string
    {
        $files = [];
        foreach ($data['common_files'] ?? [] as $f) {
            if ($f['terekspos'] ?? false) $files[] = $f['path'];
        }
        return empty($files) ? 'Tidak ada' : implode(', ', $files);
    }

    protected function formatSSL(array $data): string
    {
        $ssl = $data['ssl'] ?? [];
        return ($ssl['status'] ?? 'unknown') . ', ' . ($ssl['issuer'] ?? '') . ', expires: ' . ($ssl['expires_at'] ?? 'N/A');
    }

    protected function formatSensitiveHeaders(array $data): string
    {
        $headers = $data['security_headers']['headers_sensitif'] ?? [];
        if (empty($headers)) return 'Tidak ada';
        $parts = [];
        foreach ($headers as $name => $value) {
            $parts[] = "{$name}: {$value}";
        }
        return implode(', ', $parts);
    }

    protected function formatFullData(array $data): string
    {
        // Kirim ringkasan, bukan raw JSON penuh (agar tidak melebihi token limit)
        $summary = [];
        $summary[] = 'VT Stats: ' . json_encode($data['virustotal']['stats'] ?? []);
        $summary[] = 'Header Score: ' . ($data['security_headers']['skor'] ?? 0);

        if (!empty($data['server_info'])) {
            $summary[] = 'Server: ' . json_encode($data['server_info']);
        }

        $commonFiles = [];
        foreach ($data['common_files'] ?? [] as $f) {
            $commonFiles[] = $f['path'] . ' => ' . ($f['terekspos'] ? 'TEREKSPOS' : 'aman');
        }
        if (!empty($commonFiles)) {
            $summary[] = 'Common Files: ' . implode(', ', $commonFiles);
        }

        return implode("\n", $summary);
    }
}
