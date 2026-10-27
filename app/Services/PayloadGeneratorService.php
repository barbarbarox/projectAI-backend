<?php

namespace App\Services;

use App\Models\Temuan;

class PayloadGeneratorService
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function generateForTemuan(Temuan $temuan, ?int $userId = null): array
    {
        // Bangun prompt spesifik untuk Red Team Payload Generation
        $prompt = $this->buildPrompt($temuan);

        // Panggil AI langsung via GeminiService
        // Kirim userId agar menggunakan API key milik user jika tersedia
        $aiResponse = $this->geminiService->generate($prompt, null, true, $userId);

        // AI diharapkan membalas dengan JSON
        $data = $this->parseResponse($aiResponse);

        return $data;
    }

    protected function buildPrompt(Temuan $temuan): string
    {
        $scan = $temuan->scan;
        
        return <<<PROMPT
Anda adalah Senior Red Team Operator dan Exploit Developer.
Tugas Anda adalah meracik *weaponized payload* (exploit payload siap pakai) berdasarkan temuan kerentanan berikut.

INFORMASI TARGET:
- URL Target / Nama: {$scan->target}
- Tipe Pemindaian: {$scan->tipe_scan}
- Framework/Teknologi terdeteksi: (Silakan asumsikan dari konteks jika tidak ada)

DETAIL KERENTANAN:
- Judul: {$temuan->judul}
- Keparahan: {$temuan->tingkat_keparahan}
- Lokasi: {$temuan->lokasi}
- CWE: {$temuan->cwe_id}
- Deskripsi: {$temuan->deskripsi}
- Kode Rentan:
{$temuan->kode_rentan}

INSTRUKSI OUTPUT:
Anda WAJIB merespon HANYA dengan JSON valid tanpa markdown formatting tambahan atau penjelasan di luar JSON. Struktur JSON harus seperti ini:
{
    "tipe_payload": "bash|python|sqlmap|curl|raw_http",
    "perintah_eksekusi": "Perintah lengkap untuk menjalankan exploit di terminal (misal curl -X POST ...)",
    "kode_script": "Jika butuh script python/bash, taruh di sini. Jika cukup perintah oneliner, kosongkan.",
    "penjelasan_cara_kerja": "Penjelasan singkat bagaimana payload ini mengeksploitasi kerentanan",
    "peringatan": "Risiko saat menjalankan payload ini (contoh: dapat mengubah data database, DoS)"
}

Ingat, JANGAN berikan saran perbaikan. Berikan EXPLOIT PAYLOAD yang nyata dan siap pakai.
PROMPT;
    }

    protected function parseResponse(string $response): array
    {
        // Cari blok JSON dalam teks
        if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
            $jsonString = $matches[0];
            $decoded = json_decode($jsonString, true);
        } else {
            $decoded = json_decode($response, true);
        }

        if (!$decoded) {
            // Fallback jika AI gagal format JSON
            return [
                'tipe_payload' => 'manual',
                'perintah_eksekusi' => '',
                'kode_script' => $response,
                'penjelasan_cara_kerja' => 'Format output AI tidak dapat diparsing. Berikut respon raw.',
                'peringatan' => 'Harap tinjau script secara manual.'
            ];
        }

        return $decoded;
    }
}
