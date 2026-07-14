<?php

namespace App\Http\Controllers\Analisis;

use App\Http\Controllers\Controller;
use App\Jobs\ProsesAnalisisKode;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KodeController extends Controller
{
    public function form()
    {
        return view('analisis.kode');
    }

    public function proses(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|min:10',
            'bahasa' => 'required|in:php,javascript,python,java,go,ruby',
            'mode_ai' => 'nullable|in:dengan_ai,tanpa_ai',
            'mode_rag' => 'nullable|in:dengan_rag,tanpa_rag',
        ], [
            'kode.required' => 'Kode sumber wajib diisi.',
            'kode.min' => 'Kode sumber minimal 10 karakter.',
            'bahasa.required' => 'Bahasa pemrograman wajib dipilih.',
        ]);

        $user = Auth::user();
        $modeAi = $request->input('mode_ai', 'dengan_ai');
        $modeRag = $request->input('mode_rag', 'dengan_rag');
        $modelAi = $request->input('model_ai');

        $scan = Scan::create([
            'user_id' => Auth::id(),
            'tipe_scan' => 'kode',
            'mode_scan' => 'otomatis',
            'mode_ai' => $modeAi,
            'mode_rag' => $modeRag,
            'model_ai' => $modelAi,
            'target' => 'Analisis Kode ' . ucfirst($request->bahasa),
            'status' => 'memproses',
            'progress_step' => 'Memulai inisialisasi Job...',
            'progress_persen' => 5,
        ]);

        $user->increment('scan_count_today');

        ProsesAnalisisKode::dispatch($scan, $request->kode, $request->bahasa);

        return redirect()->route('analisis.status', $scan->id);
    }
}
