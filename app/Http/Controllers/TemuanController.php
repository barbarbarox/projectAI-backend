<?php

namespace App\Http\Controllers;

use App\Models\Temuan;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
