<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Scan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    public function index()
    {
        $scans = Scan::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('laporan.index', compact('scans'));
    }

    public function detail(Scan $scan)
    {
        abort_if($scan->user_id !== Auth::id(), 403);

        $scan->load(['temuan.evidence', 'simulasiSerangan']);

        // Jika scan tipe log, gunakan view khusus
        if ($scan->tipe_scan === 'url' && in_array($scan->mode_scan, ['biasa', 'intens'])) {
            return view('analisis.hasil-url', compact('scan'));
        }

        if ($scan->mode_scan === 'log') {
            return view('laporan.detail-log', compact('scan'));
        }

        return view('laporan.detail', compact('scan'));
    }

    public function status(Scan $scan)
    {
        abort_if($scan->user_id !== Auth::id(), 403);

        // Safeguard: Jika scan masih 'memproses' lebih dari 15 menit dan tidak ada di jobs queue, tandai gagal
        if ($scan->status === 'memproses' && $scan->updated_at && $scan->updated_at->lt(now()->subMinutes(15))) {
            $hasJob = \DB::table('jobs')->where('payload', 'like', "%{$scan->id}%")->exists();
            if (!$hasJob) {
                $scan->update([
                    'status' => 'gagal',
                    'progress_step' => 'Gagal',
                    'error_message' => 'Proses pemindaian terhenti karena antrean worker terputus atau melebihi batas waktu (timeout). Silakan ulangi pemindaian.',
                    'updated_at' => now(),
                ]);
            }
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'status'         => $scan->status,
                'skor_keamanan'  => $scan->skor_keamanan,
                'progress_step'  => $scan->progress_step,
                'progress_persen'=> $scan->progress_persen ?? 0,
                'pipeline_stage' => $scan->pipeline_stage ?? 0,
                'error_message'  => $scan->error_message,
            ]);
        }

        return view('analisis.status', compact('scan'));
    }

    public function hasil(Scan $scan)
    {
        abort_if($scan->user_id !== Auth::id(), 403);

        $scan->load(['temuan.evidence', 'simulasiSerangan']);

        // Redirect URL scans ke hasil-url view
        if ($scan->tipe_scan === 'url' && in_array($scan->mode_scan, ['biasa', 'intens'])) {
            return view('analisis.hasil-url', compact('scan'));
        }

        // Redirect log scans ke detail-log view
        if ($scan->mode_scan === 'log') {
            return view('laporan.detail-log', compact('scan'));
        }

        return view('laporan.detail', compact('scan'));
    }

    public function exportPDF(Scan $scan)
    {
        abort_if($scan->user_id !== Auth::id(), 403);

        $scan->load(['temuan.evidence', 'simulasiSerangan']);

        // === AUDIT LOG: PDF laporan diunduh ===
        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'report_exported_pdf',
            'description' => "Laporan PDF diunduh untuk Scan #{$scan->id} | Target: {$scan->target} | Mode: {$scan->mode_scan}",
        ]);

        $pdf = Pdf::loadView('laporan.pdf', compact('scan'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("redsim-laporan-" . substr($scan->id, 0, 8) . ".pdf");
    }

    public function bandingkan(Scan $scan, \Illuminate\Http\Request $request)
    {
        abort_if($scan->user_id !== Auth::id(), 403);

        $scan->load(['temuan.evidence']);

        // Ambil scan lain milik user yang sama dan target sama (atau scan lain untuk dipilih)
        $scanTarget = $scan->target ?? $scan->nama_file;

        // Scan lain dengan target serupa (milik user ini)
        $scanSebelumnya = Scan::where('user_id', Auth::id())
            ->where('id', '!=', $scan->id)
            ->where('status', 'selesai')
            ->where(function ($q) use ($scanTarget) {
                $q->where('target', $scanTarget)
                  ->orWhere('target', 'like', '%' . parse_url($scanTarget, PHP_URL_HOST) . '%');
            })
            ->latest()
            ->first();

        // Jika ada scan_id di query string (user memilih manual)
        if ($request->query('banding')) {
            $pilihan = Scan::where('user_id', Auth::id())
                ->where('id', $request->query('banding'))
                ->where('status', 'selesai')
                ->first();
            if ($pilihan) {
                $scanSebelumnya = $pilihan;
            }
        }

        if ($scanSebelumnya) {
            $scanSebelumnya->load(['temuan.evidence']);
        }

        // Semua scan selesai milik user (untuk dropdown pilih banding)
        $semuaScan = Scan::where('user_id', Auth::id())
            ->where('id', '!=', $scan->id)
            ->where('status', 'selesai')
            ->select('id', 'target', 'nama_file', 'mode_scan', 'created_at', 'skor_keamanan')
            ->latest()
            ->limit(20)
            ->get();

        return view('laporan.bandingkan', compact('scan', 'scanSebelumnya', 'semuaScan'));
    }

    public function destroy(Scan $scan)
    {
        abort_if($scan->user_id !== Auth::id(), 403);

        // Jangan hapus scan yang sedang diproses
        if ($scan->status === 'memproses' || $scan->status === 'antri') {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Scan sedang diproses, tidak dapat dihapus.'], 422);
            }
            return back()->with('error', 'Scan sedang diproses, tidak dapat dihapus.');
        }

        $target = $scan->target ?? $scan->nama_file ?? 'Scan #' . substr($scan->id, 0, 8);

        // Hapus file jika ada
        if ($scan->nama_file && \Storage::exists('uploads/' . $scan->nama_file)) {
            \Storage::delete('uploads/' . $scan->nama_file);
        }

        // Cascade delete temuan & simulasi (via DB constraint atau manual)
        $scan->temuan()->delete();
        $scan->simulasiSerangan()->delete();
        $scan->delete();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'scan_deleted',
            'description' => "Riwayat scan dihapus: {$target}",
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('laporan.index')->with('success', "Riwayat scan \"{$target}\" berhasil dihapus.");
    }
}
