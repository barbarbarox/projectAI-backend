<?php

namespace App\Http\Controllers;

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

        $scan->load(['temuan', 'simulasiSerangan']);

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

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'status' => $scan->status,
                'skor_keamanan' => $scan->skor_keamanan,
                'progress_step' => $scan->progress_step,
                'progress_persen' => $scan->progress_persen ?? 0,
                'error_message' => $scan->error_message,
            ]);
        }

        return view('analisis.status', compact('scan'));
    }

    public function hasil(Scan $scan)
    {
        abort_if($scan->user_id !== Auth::id(), 403);

        $scan->load(['temuan', 'simulasiSerangan']);

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

        $scan->load(['temuan', 'simulasiSerangan']);

        $pdf = Pdf::loadView('laporan.pdf', compact('scan'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("redsim-laporan-" . substr($scan->id, 0, 8) . ".pdf");
    }
}
