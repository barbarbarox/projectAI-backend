<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use ZipArchive;

class AnalisisZIPService
{
    protected RAGService $ragService;

    public function __construct(RAGService $ragService)
    {
        $this->ragService = $ragService;
    }

    public function analisis(string $zipPath, bool $useRag = true, ?string $modelOverride = null): array
    {
        $kodeGabungan = $this->ekstrakDanBaca($zipPath);
        $tugas = "Analisis keamanan kode dari file ZIP berikut:\n\n{$kodeGabungan}\n\nIdentifikasi semua kerentanan, berikan skor keamanan, dan simulasi serangan yang mungkin.";
        $hasil = $this->ragService->analisis($kodeGabungan, $tugas, $useRag, $modelOverride);

        // Hapus file ZIP setelah analisis
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        return $hasil;
    }

    protected function ekstrakDanBaca(string $zipPath): string
    {
        $zip = new ZipArchive;
        $kode = '';
        $ekstensiValid = ['php', 'js', 'py', 'java', 'html', 'css', 'json', 'xml', 'yml', 'yaml', 'env', 'sql', 'ts', 'jsx', 'tsx', 'vue', 'rb', 'go', 'rs'];

        if ($zip->open($zipPath) === true) {
            for ($i = 0; $i < min($zip->numFiles, 50); $i++) {
                $nama = $zip->getNameIndex($i);
                $ext = strtolower(pathinfo($nama, PATHINFO_EXTENSION));

                if (in_array($ext, $ekstensiValid)) {
                    $isi = $zip->getFromIndex($i);
                    if ($isi && strlen($isi) < 50000) {
                        $kode .= "\n\n--- File: {$nama} ---\n" . substr($isi, 0, 10000);
                    }
                }
            }
            $zip->close();
        }

        return $kode ?: 'Tidak ada file kode yang dapat dibaca dari ZIP.';
    }
}
