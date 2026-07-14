<?php

namespace App\Http\Controllers\Analisis;

use App\Http\Controllers\Controller;
use App\Jobs\ProsesAnalisisLog;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function form()
    {
        return view('analisis.log');
    }

    public function proses(Request $request)
    {
        $request->validate([
            'input_mode' => 'required|in:file,teks',
            'tipe_log' => 'required|string',
            'log_file' => 'required_if:input_mode,file|file|mimes:log,txt,csv|max:10240',
            'log_teks' => 'required_if:input_mode,teks|nullable|string|max:5000000',
            'mode_ai' => 'nullable|in:dengan_ai,tanpa_ai',
            'mode_rag' => 'nullable|in:dengan_rag,tanpa_rag',
        ], [
            'log_file.required_if' => 'File log wajib diupload.',
            'log_file.mimes' => 'Format file harus .log, .txt, atau .csv.',
            'log_file.max' => 'Ukuran file maksimal 10MB.',
            'log_teks.required_if' => 'Teks log wajib diisi.',
        ]);

        $user = Auth::user();
        $filePath = null;
        $logContent = '';

        if ($request->input_mode === 'file') {
            $file = $request->file('log_file');
            $filePath = $file->store('logs', 'local');
            $logContent = file_get_contents($file->getRealPath());
            $namaFile = $file->getClientOriginalName();
        } else {
            $logContent = $request->log_teks;
            $namaFile = 'paste-log-' . now()->format('Ymd-His') . '.txt';
        }

        $scan = Scan::create([
            'user_id' => $user->id,
            'tipe_scan' => 'url', // reuse existing type
            'mode_scan' => 'log',
            'target' => $namaFile,
            'nama_file' => $namaFile,
            'status' => 'memproses',
            'mode_ai' => $request->input('mode_ai', 'dengan_ai'),
            'mode_rag' => $request->input('mode_rag', 'dengan_rag'),
            'model_ai' => $request->input('model_ai'),
            'progress_step' => 'Inisialisasi...',
            'progress_persen' => 5,
        ]);

        $user->increment('scan_count_today');

        ProsesAnalisisLog::dispatch($scan, $logContent, $request->tipe_log, $filePath);

        return redirect()->route('analisis.status', $scan->id);
    }
}
