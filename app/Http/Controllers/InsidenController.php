<?php

namespace App\Http\Controllers;

use App\Models\Insiden;
use App\Models\Temuan;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InsidenController extends Controller
{
    /**
     * Tampilkan daftar insiden keamanan.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Insiden::with(['scan', 'temuan']);

        if ($user->tier !== 'admin') {
            $query->where('user_id', $user->id);
        }

        // Filter Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter Keparahan
        if ($request->filled('keparahan') && $request->keparahan !== 'all') {
            $query->where('tingkat_keparahan', $request->keparahan);
        }

        // Pencarian Teks
        if ($request->filled('q')) {
            $q = '%' . trim($request->q) . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('nomor_insiden', 'ilike', $q)
                    ->orWhere('judul', 'ilike', $q)
                    ->orWhere('target', 'ilike', $q)
                    ->orWhere('assigned_to', 'ilike', $q);
            });
        }

        // Statistik Insiden
        $baseStatsQuery = Insiden::query();
        if ($user->tier !== 'admin') {
            $baseStatsQuery->where('user_id', $user->id);
        }

        $stats = [
            'total'         => (clone $baseStatsQuery)->count(),
            'open'          => (clone $baseStatsQuery)->where('status', 'open')->count(),
            'investigating' => (clone $baseStatsQuery)->whereIn('status', ['investigating', 'patching'])->count(),
            'resolved'      => (clone $baseStatsQuery)->whereIn('status', ['resolved', 'closed'])->count(),
            'kritis'        => (clone $baseStatsQuery)->where('tingkat_keparahan', 'kritis')->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];

        $insidens = $query->latest()->paginate(15)->withQueryString();

        return view('insiden.index', compact('insidens', 'stats'));
    }

    /**
     * Form buat insiden manual.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $scans = Scan::where('user_id', $user->id)->latest()->take(30)->get();

        $prefillScan = null;
        if ($request->filled('scan_id')) {
            $prefillScan = Scan::where('id', $request->scan_id)
                ->when($user->tier !== 'admin', fn($q) => $q->where('user_id', $user->id))
                ->first();
        }

        $prefillTemuan = null;
        if ($request->filled('temuan_id')) {
            $prefillTemuan = Temuan::with('scan')->where('id', $request->temuan_id)->first();
        }

        return view('insiden.create', compact('scans', 'prefillScan', 'prefillTemuan'));
    }

    /**
     * Simpan insiden baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'               => 'required|string|max:255',
            'deskripsi'           => 'nullable|string',
            'tingkat_keparahan'   => 'required|in:kritis,tinggi,sedang,rendah',
            'status'              => 'nullable|in:open,investigating,patching,resolved,closed',
            'target'              => 'nullable|string|max:255',
            'assigned_to'         => 'nullable|string|max:100',
            'catatan_investigasi' => 'nullable|string',
            'solusi_remediasi'    => 'nullable|string',
            'scan_id'             => 'nullable|string|exists:scans,id',
            'temuan_id'           => 'nullable|string|exists:temuan,id',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'open';

        if (in_array($validated['status'], ['resolved', 'closed'])) {
            $validated['resolved_at'] = now();
        }

        $insiden = Insiden::create($validated);

        // Jika ditautkan ke temuan, sinkronisasi status temuan
        if (!empty($validated['temuan_id'])) {
            $temuan = Temuan::find($validated['temuan_id']);
            if ($temuan) {
                $temuan->status = in_array($validated['status'], ['resolved', 'closed']) ? 'selesai' : 'dalam_penanganan';
                if ($temuan->status === 'selesai') {
                    $temuan->resolved_at = now();
                    $temuan->resolved_by = Auth::id();
                    $temuan->resolution_notes = $validated['solusi_remediasi'] ?? 'Diselesaikan via insiden ' . $insiden->nomor_insiden;
                }
                $temuan->save();
            }
        }

        return redirect()->route('insiden.show', $insiden->id)->with('pesan', [
            'tipe' => 'sukses',
            'isi'  => "Insiden {$insiden->nomor_insiden} berhasil dibuat.",
        ]);
    }

    /**
     * 1-Click Konversi Temuan Kerentanan menjadi Insiden Keamanan.
     */
    public function storeFromTemuan(Request $request, Temuan $temuan)
    {
        $user = Auth::user();

        // Pastikan kepemilikan temuan melalui scan
        if ($user->tier !== 'admin' && $temuan->scan && $temuan->scan->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Cek apakah insiden sudah pernah dibuat untuk temuan ini
        $existing = Insiden::where('temuan_id', $temuan->id)->first();
        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Insiden sudah pernah dibuat sebelumnya.',
                    'url'     => route('insiden.show', $existing->id),
                    'insiden' => $existing,
                ]);
            }
            return redirect()->route('insiden.show', $existing->id)->with('pesan', [
                'tipe' => 'info',
                'isi'  => "Insiden {$existing->nomor_insiden} sudah terdaftar untuk temuan ini.",
            ]);
        }

        // Buat insiden otomatis
        $insiden = Insiden::create([
            'user_id'             => $user->id,
            'temuan_id'           => $temuan->id,
            'scan_id'             => $temuan->scan_id,
            'judul'               => '[TEMUAN] ' . $temuan->judul,
            'deskripsi'           => $temuan->deskripsi ?? 'Insiden keamanan di-eskalasi dari temuan otomatis pemindai.',
            'tingkat_keparahan'   => in_array(strtolower($temuan->tingkat_keparahan), ['kritis', 'tinggi', 'sedang', 'rendah'])
                                        ? strtolower($temuan->tingkat_keparahan)
                                        : 'sedang',
            'status'              => 'open',
            'target'              => $temuan->scan?->target ?? $temuan->lokasi ?? 'Target URL',
            'assigned_to'         => $user->nama_lengkap ?? $user->name,
            'solusi_remediasi'    => $temuan->remediasi,
            'catatan_investigasi' => "Diekstrak dari scan " . ($temuan->scan_id ? substr($temuan->scan_id, 0, 8) : '-') . 
                                     ($temuan->cve_id ? " | CVE: {$temuan->cve_id}" : '') .
                                     ($temuan->cwe_id ? " | CWE: {$temuan->cwe_id}" : ''),
        ]);

        // Update status temuan menjadi dalam_penanganan
        $temuan->status = 'dalam_penanganan';
        $temuan->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Insiden {$insiden->nomor_insiden} berhasil dibuat.",
                'url'     => route('insiden.show', $insiden->id),
                'insiden' => $insiden,
            ]);
        }

        return redirect()->route('insiden.show', $insiden->id)->with('pesan', [
            'tipe' => 'sukses',
            'isi'  => "Insiden {$insiden->nomor_insiden} berhasil dibuat dari temuan.",
        ]);
    }

    /**
     * 1-Click Konversi Subdomain Takeover Risk menjadi Insiden Keamanan.
     */
    public function storeFromTakeover(Request $request)
    {
        $validated = $request->validate([
            'scan_id'          => 'required|string|exists:scans,id',
            'subdomain'        => 'required|string|max:255',
            'cname'            => 'nullable|string|max:255',
            'takeover_service' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $scan = Scan::findOrFail($validated['scan_id']);

        if ($user->tier !== 'admin' && $scan->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $subdomain = $validated['subdomain'];
        $cname = $validated['cname'] ?? '-';
        $service = $validated['takeover_service'] ?? 'Cloud Hosting';

        // Cek duplikasi insiden takeover pada subdomain yang sama
        $existing = Insiden::where('scan_id', $scan->id)
            ->where('target', $subdomain)
            ->where('status', '!=', 'closed')
            ->first();

        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Insiden takeover untuk subdomain ini sudah ada.',
                    'url'     => route('insiden.show', $existing->id),
                    'insiden' => $existing,
                ]);
            }
            return redirect()->route('insiden.show', $existing->id)->with('pesan', [
                'tipe' => 'info',
                'isi'  => "Insiden {$existing->nomor_insiden} sudah aktif untuk subdomain {$subdomain}.",
            ]);
        }

        $insiden = Insiden::create([
            'user_id'             => $user->id,
            'scan_id'             => $scan->id,
            'judul'               => "[TAKEOVER RISK] Dangling CNAME pada {$subdomain}",
            'deskripsi'           => "Ditemukan risiko Subdomain Takeover pada aset '{$subdomain}'. Record CNAME menunjuk ke '{$cname}' ({$service}) namun resource terkait di cloud belum didaftarkan atau sudah kedaluwarsa. Penyerang dapat membajak host ini untuk pencurian session dan phishing.",
            'tingkat_keparahan'   => 'kritis',
            'status'              => 'open',
            'target'              => $subdomain,
            'assigned_to'         => $user->nama_lengkap ?? $user->name,
            'solusi_remediasi'    => "1. Hapus DNS record CNAME untuk {$subdomain} dari DNS zone management Anda jika subdomain tidak lagi digunakan.\n2. Atau, klaim kembali nama resource {$cname} pada akun provider {$service} Anda untuk mengunci kepemilikan.",
            'catatan_investigasi' => "Terdeteksi oleh ASM Subdomain Discovery Engine.\nCNAME: {$cname}\nLayanan Terkait: {$service}",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Insiden {$insiden->nomor_insiden} berhasil dibuat.",
                'url'     => route('insiden.show', $insiden->id),
                'insiden' => $insiden,
            ]);
        }

        return redirect()->route('insiden.show', $insiden->id)->with('pesan', [
            'tipe' => 'sukses',
            'isi'  => "Insiden Subdomain Takeover {$insiden->nomor_insiden} berhasil dibuat.",
        ]);
    }

    /**
     * Tampilkan detail insiden, timeline & form penanganan.
     */
    public function show(Insiden $insiden)
    {
        $user = Auth::user();
        if ($user->tier !== 'admin' && $insiden->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $insiden->load(['scan', 'temuan', 'user']);

        return view('insiden.show', compact('insiden'));
    }

    /**
     * Perbarui status, catatan investigasi atau solusi remediasi insiden.
     */
    public function update(Request $request, Insiden $insiden)
    {
        $user = Auth::user();
        if ($user->tier !== 'admin' && $insiden->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $validated = $request->validate([
            'status'              => 'required|in:open,investigating,patching,resolved,closed',
            'tingkat_keparahan'   => 'nullable|in:kritis,tinggi,sedang,rendah',
            'assigned_to'         => 'nullable|string|max:100',
            'catatan_investigasi' => 'nullable|string',
            'solusi_remediasi'    => 'nullable|string',
        ]);

        $oldStatus = $insiden->status;
        $newStatus = $validated['status'];

        if (in_array($newStatus, ['resolved', 'closed']) && !in_array($oldStatus, ['resolved', 'closed'])) {
            $validated['resolved_at'] = now();
        } elseif (!in_array($newStatus, ['resolved', 'closed'])) {
            $validated['resolved_at'] = null;
        }

        $insiden->update($validated);

        // Jika ada temuan terkait, sinkronkan statusnya
        if ($insiden->temuan) {
            $temuan = $insiden->temuan;
            if (in_array($newStatus, ['resolved', 'closed'])) {
                $temuan->status = 'selesai';
                $temuan->resolved_at = now();
                $temuan->resolved_by = $user->id;
                $temuan->resolution_notes = $validated['solusi_remediasi'] ?? $temuan->resolution_notes;
            } else {
                $temuan->status = 'dalam_penanganan';
                $temuan->resolved_at = null;
                $temuan->resolved_by = null;
            }
            $temuan->save();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status insiden berhasil diperbarui.',
                'insiden' => $insiden,
            ]);
        }

        return redirect()->route('insiden.show', $insiden->id)->with('pesan', [
            'tipe' => 'sukses',
            'isi'  => 'Status insiden berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus insiden.
     */
    public function destroy(Insiden $insiden)
    {
        $user = Auth::user();
        if ($user->tier !== 'admin' && $insiden->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $nomor = $insiden->nomor_insiden;

        // Reset status temuan jika ada
        if ($insiden->temuan) {
            $insiden->temuan->status = 'baru';
            $insiden->temuan->resolved_at = null;
            $insiden->temuan->resolved_by = null;
            $insiden->temuan->save();
        }

        $insiden->delete();

        return redirect()->route('insiden.index')->with('pesan', [
            'tipe' => 'sukses',
            'isi'  => "Insiden {$nomor} berhasil dihapus.",
        ]);
    }
}
