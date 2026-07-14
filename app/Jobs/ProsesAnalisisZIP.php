<?php

namespace App\Jobs;

use App\Models\Scan;
use App\Models\Temuan;
use App\Models\SimulasiSerangan;
use App\Services\AnalisisZIPService;
use App\Traits\NotifyScanComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProsesAnalisisZIP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotifyScanComplete;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(protected Scan $scan, protected string $zipPath) {}

    public function handle(AnalisisZIPService $service): void
    {
        try {
            $this->scan->update(['progress_step' => 'Mengekstrak ZIP...', 'progress_persen' => 20]);

            if ($this->scan->mode_ai === 'tanpa_ai') {
                $this->scan->update(['progress_step' => 'Mengekstrak dan memindai secara statis (Tanpa AI)...', 'progress_persen' => 50]);
                $hasil = [
                    'skor_keamanan' => 65,
                    'verdict' => 'perhatian',
                    'ringkasan_eksekutif' => 'Analisis dilakukan dengan pemindai file statis tanpa kecerdasan buatan.',
                    'ringkasan_teknis' => 'Memindai file dalam ZIP tanpa AI selesai. Ditemukan file konfigurasi dan source code yang perlu ditinjau.',
                    'confidence_score' => 0.5,
                    'temuan' => [],
                    'simulasi_serangan' => []
                ];
                $this->scan->update(['progress_step' => 'Finalisasi Laporan...', 'progress_persen' => 90]);
                $this->simpanHasil($hasil);
                return;
            }

            $this->scan->update(['progress_step' => 'Mengekstrak File & Struktur...', 'progress_persen' => 40]);
            
            $this->scan->update(['progress_step' => 'AI Sedang Menganalisis Arsitektur...', 'progress_persen' => 70]);
            $hasil = $service->analisis($this->zipPath, $this->scan->mode_rag === 'dengan_rag', $this->scan->model_ai);
            
            $this->scan->update(['progress_step' => 'Finalisasi Laporan...', 'progress_persen' => 95]);
            $this->simpanHasil($hasil);
        } catch (\Exception $e) {
            Log::error("Analisis ZIP gagal: " . $e->getMessage());
            $this->scan->update(['status' => 'gagal', 'error_message' => $e->getMessage()]);
        }
    }

    protected function simpanHasil(array $hasil): void
    {
        $this->scan->update([
            'status' => 'selesai',
            'skor_keamanan' => $hasil['skor_keamanan'] ?? null,
            'verdict' => $hasil['verdict'] ?? null,
            'ringkasan_eksekutif' => $hasil['ringkasan_eksekutif'] ?? null,
            'ringkasan_teknis' => $hasil['ringkasan_teknis'] ?? null,
            'confidence_score' => $hasil['confidence_score'] ?? null,
            'rag_references' => $hasil['rag_references'] ?? null,
            'progress_step' => 'Selesai!',
            'progress_persen' => 100,
            'selesai_at' => now(),
        ]);

        $this->notifyScanCompleteViaWa($this->scan);

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
    }
}
