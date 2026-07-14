<?php

namespace App\Jobs;

use App\Models\Scan;
use App\Models\Temuan;
use App\Models\SimulasiSerangan;
use App\Services\AnalisisLogService;
use App\Traits\NotifyScanComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProsesAnalisisLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotifyScanComplete;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(
        protected Scan $scan,
        protected string $logContent,
        protected string $tipeLog = 'auto',
        protected ?string $filePath = null
    ) {}

    public function handle(AnalisisLogService $service): void
    {
        try {
            $this->scan->update(['progress_step' => 'Membaca dan Memparsing Log...', 'progress_persen' => 20]);
            
            $useAi = $this->scan->mode_ai !== 'tanpa_ai';
            $useRag = $this->scan->mode_rag === 'dengan_rag';
            
            if (!$useAi) {
                $this->scan->update(['progress_step' => 'Analisis Heuristik (Tanpa AI)...', 'progress_persen' => 50]);
            } else {
                $this->scan->update(['progress_step' => 'Mendeteksi Anomali dengan AI...', 'progress_persen' => 50]);
            }
            
            $hasil = $service->analisis($this->logContent, $this->tipeLog, $useAi, $useRag, $this->scan->model_ai);
            
            $this->scan->update(['progress_step' => 'Finalisasi Laporan...', 'progress_persen' => 90]);

            $this->scan->update([
                'status' => 'selesai',
                'skor_keamanan' => $hasil['skor_keamanan'] ?? null,
                'verdict' => $hasil['verdict'] ?? null,
                'ringkasan_eksekutif' => $hasil['ringkasan_eksekutif'] ?? null,
                'ringkasan_teknis' => $hasil['ringkasan_teknis'] ?? null,
                'confidence_score' => $hasil['confidence_score'] ?? null,
                'rag_references' => $hasil['rag_references'] ?? null,
                'data_mentah' => [
                    'format_terdeteksi' => $hasil['format_terdeteksi'] ?? 'unknown',
                    'stats' => $hasil['stats'] ?? [],
                    'anomali_mentah' => $hasil['anomali_mentah'] ?? [],
                    'incident_timeline' => $hasil['incident_timeline'] ?? [],
                    'ioc_list' => $hasil['ioc_list'] ?? [],
                    'attck_mapping' => $hasil['attck_mapping'] ?? [],
                    'rekomendasi' => $hasil['rekomendasi'] ?? [],
                    'tingkat_keparahan' => $hasil['tingkat_keparahan'] ?? 'rendah',
                    'ringkasan_insiden' => $hasil['ringkasan_insiden'] ?? '',
                ],
                'selesai_at' => now(),
            ]);

            $this->notifyScanCompleteViaWa($this->scan);

            // Simpan temuan dari AI
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
            Log::error("Analisis Log gagal: " . $e->getMessage());
            $this->scan->update(['status' => 'gagal', 'error_message' => $e->getMessage()]);
        } finally {
            // Hapus file setelah analisis
            if ($this->filePath && Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
        }
    }
}
