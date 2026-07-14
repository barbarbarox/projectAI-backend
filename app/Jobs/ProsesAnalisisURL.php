<?php

namespace App\Jobs;

use App\Models\Scan;
use App\Models\Temuan;
use App\Models\SimulasiSerangan;
use App\Services\ScanBiasaService;
use App\Services\ScanIntensService;
use App\Services\AnalisisURLService;
use App\Traits\NotifyScanComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProsesAnalisisURL implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotifyScanComplete;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        protected Scan $scan,
        protected string $url,
        protected string $modeScan = 'biasa'
    ) {}

    public function handle(
        ScanBiasaService $scanBiasa,
        ScanIntensService $scanIntens,
        \App\Services\RAGService $ragService
    ): void {
        try {
            $this->scan->update(['progress_step' => 'Memulai Pemindaian...', 'progress_persen' => 10]);

            $dataMentah = [];
            
            if ($this->modeScan === 'intens') {
                $this->scan->update(['progress_step' => 'Pemindaian Intensif Berjalan...', 'progress_persen' => 40]);
                $dataMentah = $scanIntens->scan($this->url);
            } else {
                $this->scan->update(['progress_step' => 'Pemindaian Biasa Berjalan...', 'progress_persen' => 40]);
                $dataMentah = $scanBiasa->scan($this->url);
            }

            $useAi = $this->scan->mode_ai !== 'tanpa_ai';
            $useRag = $this->scan->mode_rag === 'dengan_rag';

            if (!$useAi) {
                $this->scan->update(['progress_step' => 'Finalisasi Laporan (Tanpa AI)...', 'progress_persen' => 90]);
                $this->scan->update([
                    'status' => 'selesai',
                    'data_mentah' => $dataMentah,
                    'skor_keamanan' => $dataMentah['skor_reputasi'] ?? 50,
                    'teknologi_terdeteksi' => $dataMentah['teknologi'] ?? [],
                    'progress_step' => 'Selesai!',
                    'progress_persen' => 100,
                    'selesai_at' => now(),
                ]);
                $this->notifyScanCompleteViaWa($this->scan);
                return;
            }

            $this->scan->update(['progress_step' => 'AI Sedang Menganalisis Hasil...', 'progress_persen' => 70]);
            
            $tugas = "Analisis keamanan URL: {$this->url}\n\nData mentah dari scanner:\n" . json_encode($dataMentah) . "\n\nBerikan penilaian keamanan lengkap termasuk kerentanan, risiko, dan rekomendasi.";
            $hasilAi = $ragService->analisis($this->url, $tugas, $useRag, $this->scan->model_ai);

            $this->scan->update(['progress_step' => 'Finalisasi Laporan...', 'progress_persen' => 95]);

            $this->scan->update([
                'status' => 'selesai',
                'data_mentah' => $dataMentah,
                'skor_keamanan' => $hasilAi['skor_keamanan'] ?? ($dataMentah['skor_reputasi'] ?? 50),
                'verdict' => $hasilAi['verdict'] ?? null,
                'ringkasan_eksekutif' => $hasilAi['ringkasan_eksekutif'] ?? null,
                'ringkasan_teknis' => $hasilAi['ringkasan_teknis'] ?? null,
                'confidence_score' => $hasilAi['confidence_score'] ?? null,
                'rag_references' => $hasilAi['rag_references'] ?? null,
                'teknologi_terdeteksi' => $dataMentah['teknologi'] ?? [],
                'progress_step' => 'Selesai!',
                'progress_persen' => 100,
                'selesai_at' => now(),
            ]);

            $this->notifyScanCompleteViaWa($this->scan);

            foreach ($hasilAi['temuan'] ?? [] as $t) {
                Temuan::create(array_merge(['scan_id' => $this->scan->id], array_intersect_key($t, array_flip([
                    'tipe','tingkat_keparahan','judul','deskripsi','lokasi','nomor_baris',
                    'kode_rentan','kode_aman','cve_id','cwe_id','teknik_attck',
                    'remediasi','estimasi_usaha','tingkat_kepercayaan',
                ]))));
            }

            foreach ($hasilAi['simulasi_serangan'] ?? [] as $s) {
                SimulasiSerangan::create(array_merge(['scan_id' => $this->scan->id], array_intersect_key($s, array_flip([
                    'nama_skenario','profil_penyerang','narasi_teknis','narasi_eksekutif',
                    'skor_kemungkinan','skor_dampak','rantai_serangan','fase_attck',
                ]))));
            }

        } catch (\Exception $e) {
            Log::error("Analisis URL gagal: " . $e->getMessage());
            $this->scan->update(['status' => 'gagal', 'error_message' => $e->getMessage()]);
        }
    }
}
