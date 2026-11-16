<?php

namespace App\Http\Controllers;

use App\Models\Temuan;
use App\Models\Scan;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TemuanController extends Controller
{
    /**
     * Hub Seluruh Temuan Keamanan (All Findings Hub).
     * Mengagregasikan seluruh temuan kerentanan dari semua scan milik user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Temuan::with(['scan', 'insiden', 'resolver']);

        // Filter berdasarkan user pemilik scan (kecuali admin)
        if ($user->tier !== 'admin') {
            $query->whereHas('scan', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Filter Tingkat Keparahan
        if ($request->filled('keparahan') && $request->keparahan !== 'all') {
            $query->where('tingkat_keparahan', $request->keparahan);
        }

        // Filter Status Resolusi
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter Tipe Scan (url, kode, log, zip)
        if ($request->filled('tipe_scan') && $request->tipe_scan !== 'all') {
            $query->whereHas('scan', function ($q) use ($request) {
                $q->where('tipe_scan', $request->tipe_scan);
            });
        }

        // Filter Pencarian Teks
        if ($request->filled('q')) {
            $q = '%' . trim($request->q) . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('judul', 'ilike', $q)
                    ->orWhere('deskripsi', 'ilike', $q)
                    ->orWhere('cve_id', 'ilike', $q)
                    ->orWhere('cwe_id', 'ilike', $q)
                    ->orWhere('lokasi', 'ilike', $q);
            });
        }

        // Base query untuk statistik
        $baseStatsQuery = Temuan::query();
        if ($user->tier !== 'admin') {
            $baseStatsQuery->whereHas('scan', fn($q) => $q->where('user_id', $user->id));
        }

        $totalCount    = (clone $baseStatsQuery)->count();
        $kritisCount   = (clone $baseStatsQuery)->where('tingkat_keparahan', 'kritis')->count();
        $tinggiCount   = (clone $baseStatsQuery)->where('tingkat_keparahan', 'tinggi')->count();
        $selesaiCount  = (clone $baseStatsQuery)->where('status', 'selesai')->count();
        $inProgCount   = (clone $baseStatsQuery)->where('status', 'dalam_penanganan')->count();
        $baruCount     = (clone $baseStatsQuery)->where('status', 'baru')->count();

        $stats = [
            'total'           => $totalCount,
            'kritis'          => $kritisCount,
            'tinggi'          => $tinggiCount,
            'kritis_tinggi'   => $kritisCount + $tinggiCount,
            'baru'            => $baruCount,
            'dalam_penanganan'=> $inProgCount,
            'selesai'         => $selesaiCount,
            'resolution_rate' => $totalCount > 0 ? round(($selesaiCount / $totalCount) * 100, 1) : 0,
        ];

        // Urutkan severity: kritis -> tinggi -> sedang -> rendah -> info
        $temuans = $query->orderByRaw("
            CASE tingkat_keparahan
                WHEN 'kritis' THEN 1
                WHEN 'tinggi' THEN 2
                WHEN 'sedang' THEN 3
                WHEN 'rendah' THEN 4
                ELSE 5
            END ASC
        ")->latest()->paginate(15)->withQueryString();

        return view('laporan.all-findings', compact('temuans', 'stats'));
    }

    /**
     * Tandai temuan sebagai Selesai / Resolved.
     */
    public function resolve(Request $request, Temuan $temuan)
    {
        $user = Auth::user();

        // Pastikan kepemilikan melalui scan
        if ($user->tier !== 'admin' && $temuan->scan && $temuan->scan->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $notes = $request->input('notes', 'Ditandai selesai setelah verifikasi patch.');

        $temuan->status = 'selesai';
        $temuan->resolved_at = now();
        $temuan->resolved_by = $user->id;
        $temuan->resolution_notes = $notes;
        $temuan->save();

        // Jika terhubung dengan insiden, perbarui status insiden juga
        if ($temuan->insiden) {
            $temuan->insiden->status = 'resolved';
            $temuan->insiden->resolved_at = now();
            if (empty($temuan->insiden->solusi_remediasi)) {
                $temuan->insiden->solusi_remediasi = $notes;
            }
            $temuan->insiden->save();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Temuan berhasil ditandai sebagai selesai.',
                'temuan'  => $temuan,
            ]);
        }

        return redirect()->back()->with('pesan', [
            'tipe' => 'sukses',
            'isi'  => "Temuan '{$temuan->judul}' berhasil ditandai sebagai Selesai.",
        ]);
    }

    /**
     * Buka kembali temuan yang sudah selesai (Reopen).
     */
    public function reopen(Request $request, Temuan $temuan)
    {
        $user = Auth::user();

        if ($user->tier !== 'admin' && $temuan->scan && $temuan->scan->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $temuan->status = 'baru';
        $temuan->resolved_at = null;
        $temuan->resolved_by = null;
        $temuan->save();

        if ($temuan->insiden) {
            $temuan->insiden->status = 'open';
            $temuan->insiden->resolved_at = null;
            $temuan->insiden->save();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Temuan berhasil dibuka kembali.',
                'temuan'  => $temuan,
            ]);
        }

        return redirect()->back()->with('pesan', [
            'tipe' => 'info',
            'isi'  => "Status temuan '{$temuan->judul}' dikembalikan menjadi Baru.",
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    // AI REMEDIASI — Generate panduan perbaikan per temuan via AI
    // ══════════════════════════════════════════════════════════════════

    /**
     * Generate panduan perbaikan AI untuk satu temuan.
     * Jika sudah pernah di-generate, return dari cache (DB).
     * Jika belum, panggil GeminiService → simpan → return.
     *
     * POST /temuan/{temuan}/ai-remediasi
     */
    public function generateAiRemediasi(Request $request, Temuan $temuan)
    {
        // 1. Ownership check
        $user = Auth::user();
        if ($user->tier !== 'admin' && $temuan->scan && $temuan->scan->user_id !== $user->id) {
            return response()->json(['error' => 'Akses tidak diizinkan.'], 403);
        }

        // 2. Cache hit — sudah pernah di-generate, langsung return
        if ($temuan->ai_remediasi && !$request->boolean('regenerate')) {
            return response()->json([
                'cached' => true,
                'data'   => $temuan->ai_remediasi,
            ]);
        }

        // 3. Build prompt dari konteks temuan nyata
        $prompt = $this->buildRemediasiPrompt($temuan);

        try {
            // 4. Panggil GeminiService (pakai token user via OAuth jika tersedia)
            $geminiService = app(GeminiService::class);
            $rawJson = $geminiService->generate($prompt, null, true, $user->id);

            // 5. Parse JSON (bersihkan markdown codeblock jika ada)
            $data = $this->parseAiJson($rawJson);

            if (!$data || empty($data['langkah_perbaikan'])) {
                Log::warning('AI Remediasi: JSON tidak valid', [
                    'temuan_id' => $temuan->id,
                    'raw'       => substr($rawJson, 0, 500),
                ]);
                return response()->json([
                    'error' => 'AI tidak menghasilkan panduan yang valid. Silakan coba lagi.',
                ], 422);
            }

            // 6. Simpan ke DB (cache) — langkah ini gratis untuk request berikutnya
            $temuan->update(['ai_remediasi' => $data]);

            return response()->json([
                'cached' => false,
                'data'   => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('AI Remediasi gagal: ' . $e->getMessage(), ['temuan_id' => $temuan->id]);

            $pesan = 'Gagal menghubungi layanan AI.';
            if (str_contains($e->getMessage(), '429')) {
                $pesan = 'Batas permintaan AI tercapai (Rate Limit). Silakan tunggu beberapa saat.';
            } elseif (str_contains($e->getMessage(), '401') || str_contains($e->getMessage(), '403')) {
                $pesan = 'API Key AI tidak valid. Periksa konfigurasi AI di halaman Profil.';
            }

            return response()->json(['error' => $pesan], 500);
        }
    }

    /**
     * Susun prompt AI dengan semua konteks temuan yang tersedia.
     */
    private function buildRemediasiPrompt(Temuan $temuan): string
    {
        $scan       = $temuan->scan;
        $tipe       = $scan?->tipe_scan ?? 'tidak diketahui';
        $target     = $scan?->target ?? $scan?->nama_file ?? 'tidak diketahui';
        $keparahan  = strtoupper($temuan->tingkat_keparahan ?? 'TIDAK DIKETAHUI');
        $cve        = $temuan->cve_id  ? "CVE: {$temuan->cve_id}"   : '';
        $cwe        = $temuan->cwe_id  ? "CWE: {$temuan->cwe_id}"   : '';
        $capec      = $temuan->capec_id ? "CAPEC: {$temuan->capec_id}" : '';
        $attck      = $temuan->teknik_attck ?? '';
        $kodeRentan = $temuan->kode_rentan ? "\n\nKode Rentan:\n```\n{$temuan->kode_rentan}\n```" : '';
        $lokasi     = $temuan->lokasi ?? '';
        $deskripsi  = $temuan->deskripsi ?? '';

        $refs = implode(' | ', array_filter([$cve, $cwe, $capec]));

        return <<<PROMPT
Kamu adalah Security Engineer senior yang membuat panduan perbaikan kelemahan keamanan dalam Bahasa Indonesia.

KONTEKS TEMUAN:
- Judul: {$temuan->judul}
- Tingkat Keparahan: {$keparahan}
- Tipe Scan: {$tipe}
- Target/Sumber: {$target}
- Lokasi: {$lokasi}
- Referensi: {$refs}
- Teknik ATT&CK: {$attck}
- Deskripsi: {$deskripsi}{$kodeRentan}

INSTRUKSI:
1. Buat panduan perbaikan yang SPESIFIK, bukan generik — sesuaikan dengan konteks temuan di atas.
2. Jika ada kode rentan, sertakan contoh kode yang sudah diperbaiki.
3. Estimasi waktu yang realistis.
4. Seluruh respons dalam BAHASA INDONESIA.
5. Langkah perbaikan 2-5 langkah, dari yang paling penting.

FORMAT JSON (HANYA JSON, tanpa markdown):
{
  "ringkasan": "Penjelasan singkat kelemahan dalam bahasa awam (1-2 kalimat)",
  "dampak_jika_tidak_diperbaiki": "Risiko konkret jika dibiarkan (1-2 kalimat)",
  "langkah_perbaikan": [
    {
      "urutan": 1,
      "judul": "Judul langkah singkat",
      "detail": "Penjelasan lengkap cara memperbaiki",
      "kode_contoh": "// Kode contoh fix (opsional, kosongkan jika tidak relevan)",
      "bahasa": "php"
    }
  ],
  "referensi": ["OWASP A03:2021", "CWE-79"],
  "estimasi_waktu": "1-2 jam",
  "tingkat_kesulitan": "mudah|sedang|sulit",
  "tools_disarankan": ["Nama tool jika relevan"]
}
PROMPT;
    }

    /**
     * Parse JSON dari response AI — toleran terhadap markdown codeblock.
     */
    private function parseAiJson(string $raw): ?array
    {
        $clean = trim($raw);

        // Bersihkan ```json ... ``` atau ``` ... ```
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $clean, $m)) {
            $clean = trim($m[1]);
        }

        $data = json_decode($clean, true);

        // Fallback: cari kurung kurawal terluar
        if (!is_array($data)) {
            $start = strpos($clean, '{');
            $end   = strrpos($clean, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $data = json_decode(substr($clean, $start, $end - $start + 1), true);
            }
        }

        return is_array($data) ? $data : null;
    }
}
