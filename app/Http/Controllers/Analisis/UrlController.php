<?php

namespace App\Http\Controllers\Analisis;

use App\Http\Controllers\Controller;
use App\Jobs\ProsesAIAnalisisURL;
use App\Jobs\ProsesAnalisisURL;
use App\Models\DomainVerification;
use App\Models\Scan;
use App\Services\DomainVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UrlController extends Controller
{
    public function form()
    {
        return view('analisis.url');
    }

    /**
     * Proses submit URL — Mode Biasa atau Intens.
     */
    public function proses(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'mode_scan' => 'required|in:biasa,intens',
            'mode_ai' => 'nullable|in:dengan_ai,tanpa_ai',
            'mode_rag' => 'nullable|in:dengan_rag,tanpa_rag',
        ], [
            'url.required' => 'URL wajib diisi.',
            'url.url' => 'Format URL tidak valid.',
        ]);

        $user = Auth::user();
        $modeScan = $request->mode_scan;
        $url = $request->url;
        $modeAi = $request->input('mode_ai', 'dengan_ai');
        $modeRag = $request->input('mode_rag', 'dengan_rag');
        $modelAi = $request->input('model_ai');

        // Jika intens, redirect ke flow verifikasi
        if ($modeScan === 'intens') {
            $domain = DomainVerificationService::extractDomain($url);
            $dvService = app(DomainVerificationService::class);

            // Cek apakah domain sudah terverifikasi
            if ($dvService->isVerified($domain, $user->id)) {
                // Langsung scan intens
                $scan = Scan::create([
                    'user_id' => $user->id,
                    'tipe_scan' => 'url',
                    'mode_scan' => 'intens',
                    'mode_ai' => $modeAi,
                    'mode_rag' => $modeRag,
                    'model_ai' => $modelAi,
                    'is_verified_domain' => true,
                    'target' => $url,
                    'status' => 'memproses',
                    'progress_step' => 'Inisialisasi...',
                    'progress_persen' => 5,
                ]);
                $user->increment('scan_count_today');
                ProsesAnalisisURL::dispatch($scan, $url, 'intens');
                return redirect()->route('analisis.status', $scan->id);
            }

            // Belum terverifikasi — generate verifikasi
            return redirect()->route('analisis.url.generate-verifikasi', ['url' => $url]);
        }

        // Mode Biasa
        $scan = Scan::create([
            'user_id' => $user->id,
            'tipe_scan' => 'url',
            'mode_scan' => 'biasa',
            'mode_ai' => $modeAi,
            'mode_rag' => $modeRag,
            'model_ai' => $modelAi,
            'target' => $url,
            'status' => 'memproses',
            'progress_step' => 'Inisialisasi...',
            'progress_persen' => 5,
        ]);

        $user->increment('scan_count_today');
        ProsesAnalisisURL::dispatch($scan, $url, 'biasa');

        return redirect()->route('analisis.status', $scan->id);
    }

    /**
     * Generate verifikasi domain — tampilkan instruksi.
     */
    public function generateVerifikasi(Request $request)
    {
        $request->validate(['url' => 'required|url']);

        $user = Auth::user();
        $url = $request->url;
        $domain = DomainVerificationService::extractDomain($url);

        $dvService = app(DomainVerificationService::class);

        // Cek apakah sudah ada pending verification
        $existing = DomainVerification::where('user_id', $user->id)
            ->where('domain', $domain)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return redirect()->route('analisis.url.verifikasi', $existing->id);
        }

        $dv = $dvService->generateVerification($user, $domain);

        return redirect()->route('analisis.url.verifikasi', $dv->id);
    }

    /**
     * Tampilkan halaman instruksi verifikasi.
     */
    public function formVerifikasi(DomainVerification $domainVerification)
    {
        abort_if($domainVerification->user_id !== Auth::id(), 403);

        $dvService = app(DomainVerificationService::class);
        $htmlContent = $dvService->generateHtmlContent($domainVerification->token);

        return view('analisis.verifikasi-domain', [
            'dv' => $domainVerification,
            'htmlContent' => $htmlContent,
        ]);
    }

    /**
     * Proses verifikasi kepemilikan domain.
     */
    public function prosesVerifikasi(Request $request, DomainVerification $domainVerification)
    {
        abort_if($domainVerification->user_id !== Auth::id(), 403);

        $dvService = app(DomainVerificationService::class);
        $result = $dvService->verify($domainVerification);

        if ($result['berhasil']) {
            // Buat scan intens dan dispatch job
            $url = $request->input('url', 'https://' . $domainVerification->domain);
            $user = Auth::user();

            $scan = Scan::create([
                'user_id' => $user->id,
                'tipe_scan' => 'url',
                'mode_scan' => 'intens',
                'is_verified_domain' => true,
                'target' => $url,
                'status' => 'memproses',
            ]);
            $user->increment('scan_count_today');

            ProsesAnalisisURL::dispatch($scan, $url, 'intens');

            return redirect()->route('analisis.status', $scan->id)
                ->with('success', $result['pesan']);
        }

        return back()->with('error', $result['pesan']);
    }

    /**
     * Download file HTML verifikasi.
     */
    public function downloadVerifikasi(DomainVerification $domainVerification)
    {
        abort_if($domainVerification->user_id !== Auth::id(), 403);

        $dvService = app(DomainVerificationService::class);
        $htmlContent = $dvService->generateHtmlContent($domainVerification->token);

        return response($htmlContent)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="' . $domainVerification->nama_file . '"');
    }

    /**
     * Trigger AI analysis pada scan yang sudah selesai.
     */
    public function aiAnalisis(Scan $scan)
    {
        abort_if($scan->user_id !== Auth::id(), 403);
        abort_if($scan->status !== 'selesai', 422, 'Scan belum selesai.');
        abort_if(empty($scan->data_mentah), 422, 'Data mentah scan tidak tersedia.');

        ProsesAIAnalisisURL::dispatch($scan);

        return back()->with('success', 'Analisis AI sedang diproses. Halaman akan diperbarui otomatis.');
    }

    /**
     * Tampilkan halaman hasil URL scan (Mode 1 & 2).
     */
    public function hasilUrl(Scan $scan)
    {
        abort_if($scan->user_id !== Auth::id(), 403);

        $scan->load(['temuan', 'simulasiSerangan']);

        return view('analisis.hasil-url', compact('scan'));
    }
}
