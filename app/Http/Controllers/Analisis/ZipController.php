<?php

namespace App\Http\Controllers\Analisis;

use App\Http\Controllers\Controller;
use App\Jobs\ProsesAnalisisZIP;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ZipController extends Controller
{
    public function form()
    {
        return view('analisis.zip');
    }

    public function proses(Request $request)
    {
        $maxSize = (int) env('MAX_FILE_SIZE_MB', 50) * 1024;

        $request->validate([
            'file_zip' => "required|file|mimes:zip|max:{$maxSize}",
            'mode_ai' => 'nullable|in:dengan_ai,tanpa_ai',
            'mode_rag' => 'nullable|in:dengan_rag,tanpa_rag',
        ], [
            'file_zip.required' => 'File ZIP wajib diunggah.',
            'file_zip.mimes' => 'File harus berformat ZIP.',
            'file_zip.max' => 'Ukuran file maksimal ' . env('MAX_FILE_SIZE_MB', 50) . 'MB.',
        ]);

        $user = Auth::user();
        $file = $request->file('file_zip');
        $path = $file->store('uploads/zip', 'local');
        $fullPath = storage_path('app/private/' . $path);

        $modeAi = $request->input('mode_ai', 'dengan_ai');
        $modeRag = $request->input('mode_rag', 'dengan_rag');
        $modelAi = $request->input('model_ai');

        $scan = Scan::create([
            'user_id' => $user->id,
            'tipe_scan' => 'zip',
            'nama_file' => $file->getClientOriginalName(),
            'target' => $file->getClientOriginalName(),
            'status' => 'memproses',
            'mode_ai' => $modeAi,
            'mode_rag' => $modeRag,
            'model_ai' => $modelAi,
            'progress_step' => 'Inisialisasi...',
            'progress_persen' => 5,
        ]);

        $user->increment('scan_count_today');

        ProsesAnalisisZIP::dispatch($scan, $fullPath);

        return redirect()->route('analisis.status', $scan->id);
    }
}
