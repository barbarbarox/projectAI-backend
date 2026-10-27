<?php

namespace App\Http\Controllers;

use App\Models\AssetGroup;
use App\Models\AssetGroupTarget;
use App\Models\Scan;
use App\Jobs\ProsesAnalisisURL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetGroupController extends Controller
{
    /**
     * Tampilkan daftar grup aset ASM pengguna.
     */
    public function index()
    {
        $user = Auth::user();
        $query = AssetGroup::with(['targets.lastScan']);

        if ($user->tier !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $groups = $query->latest()->get();

        // Metrik Agregasi
        $totalTargets = $groups->sum(fn($g) => $g->targets->count());
        $allScores = $groups->flatMap->targets->pluck('last_skor_keamanan')->filter();
        $avgScore = $allScores->isNotEmpty() ? (int) round($allScores->avg()) : null;
        $criticalTargets = $groups->flatMap->targets->filter(fn($t) => $t->last_skor_keamanan !== null && $t->last_skor_keamanan < 50)->count();

        $stats = [
            'total_groups'     => $groups->count(),
            'total_targets'    => $totalTargets,
            'avg_score'        => $avgScore,
            'critical_targets' => $criticalTargets,
        ];

        return view('analisis.asset-groups.index', compact('groups', 'stats'));
    }

    /**
     * Form buat grup aset baru.
     */
    public function create()
    {
        return view('analisis.asset-groups.create');
    }

    /**
     * Simpan grup aset baru beserta daftar target awal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'        => 'required|string|max:100',
            'deskripsi'   => 'nullable|string|max:500',
            'environment' => 'required|in:production,staging,development,internal',
            'warna'       => 'required|in:indigo,cyan,emerald,amber,purple,rose',
            'targets_raw' => 'nullable|string',
        ]);

        $group = AssetGroup::create([
            'user_id'     => Auth::id(),
            'nama'        => $validated['nama'],
            'deskripsi'   => $validated['deskripsi'] ?? null,
            'environment' => $validated['environment'],
            'warna'       => $validated['warna'],
        ]);

        // Parsing target awal jika ada (dipisahkan baris baru / koma)
        if (!empty($validated['targets_raw'])) {
            $rawLines = preg_split('/[\r\n,]+/', $validated['targets_raw']);
            foreach ($rawLines as $line) {
                $url = trim($line);
                if (empty($url)) continue;

                // Format protocol jika belum ada
                if (!preg_match('#^https?://#i', $url)) {
                    $url = 'https://' . $url;
                }

                AssetGroupTarget::create([
                    'asset_group_id' => $group->id,
                    'target_url'     => $url,
                    'label'          => null,
                ]);
            }
        }

        return redirect()->route('asset-groups.show', $group->id)->with('pesan', [
            'tipe' => 'sukses',
            'isi'  => "Grup Aset '{$group->nama}' berhasil dibuat.",
        ]);
    }

    /**
     * Tampilkan detail grup aset, daftar target, dan status keamanan.
     */
    public function show(AssetGroup $assetGroup)
    {
        $user = Auth::user();
        if ($user->tier !== 'admin' && $assetGroup->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $assetGroup->load(['targets.lastScan']);

        return view('analisis.asset-groups.show', compact('assetGroup'));
    }

    /**
     * Perbarui metadata grup aset.
     */
    public function update(Request $request, AssetGroup $assetGroup)
    {
        $user = Auth::user();
        if ($user->tier !== 'admin' && $assetGroup->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $validated = $request->validate([
            'nama'        => 'required|string|max:100',
            'deskripsi'   => 'nullable|string|max:500',
            'environment' => 'required|in:production,staging,development,internal',
            'warna'       => 'required|in:indigo,cyan,emerald,amber,purple,rose',
        ]);

        $assetGroup->update($validated);

        return redirect()->route('asset-groups.show', $assetGroup->id)->with('pesan', [
            'tipe' => 'sukses',
            'isi'  => 'Informasi grup aset berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus grup aset beserta seluruh relasinya.
     */
    public function destroy(AssetGroup $assetGroup)
    {
        $user = Auth::user();
        if ($user->tier !== 'admin' && $assetGroup->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $nama = $assetGroup->nama;
        $assetGroup->delete();

        return redirect()->route('asset-groups.index')->with('pesan', [
            'tipe' => 'sukses',
            'isi'  => "Grup aset '{$nama}' berhasil dihapus.",
        ]);
    }

    /**
     * Tambah target baru ke dalam grup aset.
     */
    public function addTarget(Request $request, AssetGroup $assetGroup)
    {
        $user = Auth::user();
        if ($user->tier !== 'admin' && $assetGroup->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $validated = $request->validate([
            'target_url' => 'required|string|max:255',
            'label'      => 'nullable|string|max:100',
        ]);

        $url = trim($validated['target_url']);
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        AssetGroupTarget::create([
            'asset_group_id' => $assetGroup->id,
            'target_url'     => $url,
            'label'          => $validated['label'] ?? null,
        ]);

        return redirect()->route('asset-groups.show', $assetGroup->id)->with('pesan', [
            'tipe' => 'sukses',
            'isi'  => "Target '{$url}' berhasil ditambahkan ke grup.",
        ]);
    }

    /**
     * Hapus satu target dari dalam grup.
     */
    public function removeTarget(AssetGroupTarget $target)
    {
        $user = Auth::user();
        $group = $target->group;

        if ($user->tier !== 'admin' && $group->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $url = $target->target_url;
        $target->delete();

        return redirect()->route('asset-groups.show', $group->id)->with('pesan', [
            'tipe' => 'sukses',
            'isi'  => "Target '{$url}' berhasil dihapus dari grup.",
        ]);
    }

    /**
     * Pemindaian Massal (Batch Scanning) untuk seluruh target dalam grup aset.
     */
    public function batchScan(Request $request, AssetGroup $assetGroup)
    {
        $user = Auth::user();
        if ($user->tier !== 'admin' && $assetGroup->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $targets = $assetGroup->targets;
        if ($targets->isEmpty()) {
            return redirect()->route('asset-groups.show', $assetGroup->id)->with('pesan', [
                'tipe' => 'error',
                'isi'  => 'Tidak ada target dalam grup aset ini untuk dipindai.',
            ]);
        }

        $modeScan = $request->input('mode_scan', 'biasa');
        $dispatchedCount = 0;

        foreach ($targets as $target) {
            $url = $target->target_url;

            $scan = Scan::create([
                'user_id'         => $user->id,
                'tipe_scan'       => 'url',
                'mode_scan'       => $modeScan,
                'target'          => $url,
                'status'          => 'memproses',
                'progress_step'   => 'Inisialisasi Batch Scan...',
                'progress_persen' => 5,
            ]);

            $user->increment('scan_count_today');
            ProsesAnalisisURL::dispatch($scan, $url, $modeScan);

            $target->update([
                'last_scan_id'    => $scan->id,
                'last_scanned_at' => now(),
            ]);

            $dispatchedCount++;
        }

        return redirect()->route('asset-groups.show', $assetGroup->id)->with('pesan', [
            'tipe' => 'sukses',
            'isi'  => "Berhasil menjadwalkan pemindaian massal untuk {$dispatchedCount} target dalam grup '{$assetGroup->nama}'.",
        ]);
    }
}
