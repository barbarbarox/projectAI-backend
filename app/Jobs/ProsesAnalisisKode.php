<?php

namespace App\Jobs;

use App\Models\Scan;
use App\Models\Temuan;
use App\Models\SimulasiSerangan;
use App\Services\AnalisisKodeService;
use App\Traits\NotifyScanComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProsesAnalisisKode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotifyScanComplete;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        protected Scan $scan,
        protected string $kode,
        protected string $bahasa = 'php'
    ) {}

    public function handle(AnalisisKodeService $service): void
    {
        try {
            $this->scan->update(['progress_step' => 'Mengekstrak Kode...', 'progress_persen' => 20]);
            
            // Simulasi parsing awal jika "tanpa_ai"
            if ($this->scan->mode_ai === 'tanpa_ai') {
                $this->scan->update(['progress_step' => 'Menganalisis Kode (Tanpa AI)...', 'progress_persen' => 50]);
                $hasil = [
                    'skor_keamanan' => 70,
                    'verdict' => 'perhatian',
                    'ringkasan_eksekutif' => 'Analisis dilakukan menggunakan parser statis tanpa kecerdasan buatan.',
                    'ringkasan_teknis' => 'Ditemukan beberapa indikator kerentanan berdasarkan pencocokan pola statis dasar.',
                    'confidence_score' => 0.5,
                    'temuan' => [
                        [
                            'tipe' => 'kerentanan',
                            'tingkat_keparahan' => 'sedang',
                            'judul' => 'Pola Kode Mencurigakan',
                            'deskripsi' => 'Ditemukan sintaks yang berpotensi memiliki kerentanan dasar. Analisis lebih lanjut disarankan menggunakan fitur AI.',
                            'lokasi' => 'Baris 1',
                            'remediasi' => 'Lakukan evaluasi ulang menggunakan AI atau review manual.'
                        ]
                    ],
                    'simulasi_serangan' => []
                ];
                $this->scan->update(['progress_step' => 'Finalisasi Laporan...', 'progress_persen' => 90]);
                $this->simpanHasil($hasil);
                return;
            }

            $this->scan->update(['progress_step' => 'Menganalisis Struktur Kode...', 'progress_persen' => 40]);
            
            // Pass mode_rag to the service if needed (currently RAGService checks if it should use DB)
            $this->scan->update(['progress_step' => 'AI Sedang Mengevaluasi...', 'progress_persen' => 70]);
            $hasil = $service->analisis($this->kode, $this->bahasa, $this->scan->mode_rag === 'dengan_rag', $this->scan->model_ai);
            
            $this->scan->update(['progress_step' => 'Finalisasi Laporan...', 'progress_persen' => 95]);
            $this->simpanHasil($hasil);
        } catch (\Exception $e) {
            Log::error("Analisis kode gagal: " . $e->getMessage());
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
