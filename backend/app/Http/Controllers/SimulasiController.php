<?php

namespace App\Http\Controllers;

use App\Models\Scan;
use App\Models\SimulasiSerangan;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SimulasiController extends Controller
{
    /**
     * Tampilkan daftar simulasi serangan untuk suatu scan.
     */
    public function index(Scan $scan)
    {
        abort_if($scan->user_id !== Auth::id(), 403);
        
        $scan->load('simulasiSerangan');
        
        return view('simulasi.index', compact('scan'));
    }

    /**
     * Tampilkan detail simulasi serangan.
     */
    public function show(Scan $scan, SimulasiSerangan $simulasi)
    {
        abort_if($scan->user_id !== Auth::id(), 403);
        abort_if($simulasi->scan_id !== $scan->id, 404);

        return view('simulasi.show', compact('scan', 'simulasi'));
    }

    /**
     * Jalankan simulasi dengan AI — menghasilkan command output realistis (AJAX).
     */
    public function runAI(Request $request, Scan $scan, SimulasiSerangan $simulasi)
    {
        abort_if($scan->user_id !== Auth::id(), 403);
        abort_if($simulasi->scan_id !== $scan->id, 404);

        try {
            $geminiService = app(GeminiService::class);

            $rantai = is_array($simulasi->rantai_serangan) ? $simulasi->rantai_serangan : [];
            $faseAttck = is_array($simulasi->fase_attck) ? implode(', ', $simulasi->fase_attck) : '';
            $target = $scan->target;
            $namaTarget = parse_url($target, PHP_URL_HOST) ?? $target;

            $prompt = <<<PROMPT
Kamu adalah Red Team AI yang membuat simulasi serangan siber EDUKATIF dalam Bahasa Indonesia, berdasarkan BUKTI NYATA dari hasil pemindaian keamanan.

KONTEKS SKENARIO:
- Nama Skenario: {$simulasi->nama_skenario}
- Profil Penyerang: {$simulasi->profil_penyerang}
- Target: {$target}
- Host Target: {$namaTarget}
- Narasi Eksekutif: {$simulasi->narasi_eksekutif}
- Rantai Serangan Berdasarkan Scan: {$this->formatRantai($rantai)}
- Fase MITRE ATT&CK: {$faseAttck}
- Skor Kemungkinan: {$simulasi->skor_kemungkinan}/10
- Skor Dampak: {$simulasi->skor_dampak}/10

INSTRUKSI WAJIB — KEJUJURAN EVIDENCE:
1. Setiap langkah WAJIB memiliki field "evidence_type":
   - "verified_raw": langkah yang menampilkan output command yang BENAR-BENAR mencerminkan data scan nyata (DNS lookup, header check, port yang benar-benar ditemukan di scan)
   - "generated_scenario": langkah yang merupakan skenario hipotetis logis yang DISUSUN AI berdasarkan temuan, tetapi BELUM DIEKSEKUSI atau DIVERIFIKASI secara nyata
2. DILARANG membuat klaim eksploitasi konkret ("cookie stolen", "payload executed", "akses berhasil") kecuali temuan yang mendasarinya berseverity TINGGI/KRITIS yang sudah terverifikasi. Untuk temuan RENDAH/INFORMASI: batasi narasi ke "potensi risiko" dan "vektor yang mungkin dieksploitasi jika kondisi terpenuhi".
3. Langkah recon/scan yang menampilkan data nyata dari scan (IP, header, port) → gunakan data yang KONSISTEN dengan informasi target yang ada, bukan data rekaan.
4. Buat 4-5 langkah simulasi yang logis dan edukatif.
5. Seluruh narasi dalam BAHASA INDONESIA.

FORMAT JSON (HANYA JSON, tanpa markdown):
{
  "langkah_simulasi": [
    {
      "nomor": 1,
      "judul": "Judul Fase (Bahasa Indonesia)",
      "fase_attck": "Nama Teknik MITRE ATT&CK",
      "tipe": "recon|scan|exploit|post_exploit|exfil|cleanup",
      "evidence_type": "verified_raw|generated_scenario",
      "komentar_operator": "Komentar operator — jika evidence_type=generated_scenario, awali dengan [SKENARIO HIPOTETIS] ...",
      "baris_terminal": [
        {
          "tipe": "prompt|command|output|success|error|warning|info",
          "teks": "isi baris terminal"
        }
      ],
      "temuan_kunci": "Apa yang ditemukan/dianalisis pada langkah ini (Bahasa Indonesia)"
    }
  ],
  "ringkasan_simulasi": "Ringkasan edukatif simulasi dalam Bahasa Indonesia (2-3 kalimat). WAJIB menyebut bahwa langkah-langkah bertipe generated_scenario adalah skenario hipotetis.",
  "analisis_dampak": "Analisis dampak POTENSIAL jika skenario terjadi (Bahasa Indonesia) — gunakan framing 'berpotensi' bukan 'telah terjadi'",
  "rekomendasi_mitigasi": [
    "Rekomendasi mitigasi 1 (Bahasa Indonesia)",
    "Rekomendasi mitigasi 2 (Bahasa Indonesia)"
  ],
  "tingkat_kesulitan": "mudah|sedang|sulit|sangat_sulit",
  "vektor_serangan_utama": "Vektor serangan yang paling relevan berdasarkan temuan nyata (Bahasa Indonesia)"
}
PROMPT;

            $responseRaw = $geminiService->generate($prompt, $scan->model_ai ?? null, true, Auth::id());
            
            // Bersihkan markdown codeblock jika model membungkus dalam ```json ... ```
            $cleanJson = trim($responseRaw);
            if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $cleanJson, $matches)) {
                $cleanJson = trim($matches[1]);
            }

            $hasilAI = json_decode($cleanJson, true);

            // Fallback jika json_decode gagal: cari kurung kurawal terluar { ... }
            if (!is_array($hasilAI) || empty($hasilAI['langkah_simulasi'])) {
                $start = strpos($cleanJson, '{');
                $end   = strrpos($cleanJson, '}');
                if ($start !== false && $end !== false && $end > $start) {
                    $sub = substr($cleanJson, $start, $end - $start + 1);
                    $hasilAI = json_decode($sub, true);
                }
            }

            if (!is_array($hasilAI) || empty($hasilAI['langkah_simulasi'])) {
                Log::error('Simulasi AI: Response tidak valid', ['raw' => substr($responseRaw, 0, 1000)]);
                return response()->json([
                    'sukses' => false,
                    'pesan'  => 'AI tidak menghasilkan data simulasi yang valid. Silakan coba lagi.',
                ], 422);
            }

            return response()->json([
                'sukses' => true,
                'data'   => $hasilAI,
            ]);

        } catch (\Exception $e) {
            Log::error('Simulasi AI gagal: ' . $e->getMessage());

            $pesan = 'Gagal menjalankan simulasi AI.';
            if (str_contains($e->getMessage(), '429')) {
                $pesan = 'Batas API AI tercapai (Rate Limit). Silakan tunggu beberapa saat.';
            } elseif (str_contains($e->getMessage(), '401') || str_contains($e->getMessage(), '403')) {
                $pesan = 'API Key tidak valid atau tidak memiliki akses. Periksa konfigurasi AI Anda.';
            }

            return response()->json(['sukses' => false, 'pesan' => $pesan], 500);
        }
    }

    private function formatRantai(array $rantai): string
    {
        if (empty($rantai)) return 'Tidak ada data rantai serangan';
        $lines = [];
        foreach ($rantai as $idx => $step) {
            $lines[] = ($idx + 1) . '. ' . $step;
        }
        return implode("\n", $lines);
    }

    /**
     * Export hasil simulasi ke PDF custom.
     */
    public function exportPDF(Request $request, Scan $scan, SimulasiSerangan $simulasi)
    {
        abort_if($scan->user_id !== Auth::id(), 403);
        abort_if($simulasi->scan_id !== $scan->id, 404);

        $hasilAI = json_decode($request->input('hasilAI'), true);
        
        if (!$hasilAI || empty($hasilAI['langkah_simulasi'])) {
            return back()->with('error', 'Data simulasi tidak valid atau belum dijalankan.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('simulasi.pdf', compact('scan', 'simulasi', 'hasilAI'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("RedSim-Simulasi-" . substr($scan->id, 0, 8) . ".pdf");
    }
}
