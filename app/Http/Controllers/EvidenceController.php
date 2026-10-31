<?php

namespace App\Http\Controllers;

use App\Models\Temuan;
use App\Models\TemuanEvidence;
use App\Services\VisualEvidenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvidenceController extends Controller
{
    public function __construct(
        private readonly VisualEvidenceService $evidenceService
    ) {}

    /**
     * Ambil semua evidence untuk satu temuan (JSON).
     */
    public function index(Temuan $temuan): JsonResponse
    {
        $this->otorisasiTemuan($temuan);

        return response()->json([
            'evidence' => $temuan->evidence->map(fn($e) => $this->formatEvidence($e)),
        ]);
    }

    /**
     * Auto-capture screenshot dari URL temuan menggunakan VisualEvidenceService.
     * POST /evidence/{temuan}/capture-screenshot
     */
    public function captureScreenshot(Temuan $temuan): JsonResponse
    {
        $this->otorisasiTemuan($temuan);

        // Cari URL target dari temuan atau scan induknya
        $scan = $temuan->scan;
        $targetUrl = $scan?->target ?? $temuan->lokasi ?? null;

        if (!$targetUrl || !filter_var($targetUrl, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'URL target tidak valid atau tidak ditemukan.'], 422);
        }

        try {
            // Cek apakah sudah ada screenshot auto
            $existing = $temuan->evidence()
                ->where('tipe', 'screenshot')
                ->where('label', 'Auto Capture')
                ->first();

            $urlscanUuid = $scan?->data_mentah['urlscan_uuid'] ?? null;
            $remoteUrl = $this->evidenceService->getScreenshotUrl($targetUrl, $urlscanUuid);

            // Coba download dan simpan lokal
            $filePath = $this->tryDownloadScreenshot($remoteUrl, $temuan->id);

            if ($existing) {
                $existing->update([
                    'remote_url' => $remoteUrl,
                    'file_path'  => $filePath ?? $existing->file_path,
                    'meta'       => ['target_url' => $targetUrl, 'source' => $this->getScreenshotSource($urlscanUuid)],
                ]);
                $ev = $existing->fresh();
            } else {
                $ev = TemuanEvidence::create([
                    'temuan_id'  => $temuan->id,
                    'scan_id'    => $temuan->scan_id,
                    'tipe'       => 'screenshot',
                    'label'      => 'Auto Capture',
                    'remote_url' => $remoteUrl,
                    'file_path'  => $filePath,
                    'meta'       => ['target_url' => $targetUrl, 'source' => $this->getScreenshotSource($urlscanUuid)],
                    'is_verified' => false,
                ]);
            }

            return response()->json([
                'success'  => true,
                'evidence' => $this->formatEvidence($ev),
            ]);
        } catch (\Throwable $e) {
            Log::error('EvidenceController@captureScreenshot: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengambil screenshot: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Capture screenshot dari URL kustom yang dimasukkan pengguna.
     * POST /evidence/{temuan}/capture-url
     */
    public function captureUrl(Request $request, Temuan $temuan): JsonResponse
    {
        $this->otorisasiTemuan($temuan);
        $request->validate([
            'url'   => 'required|url|max:2000',
            'label' => 'nullable|string|max:200',
        ]);

        try {
            $url = $request->url;
            $label = $request->label ?: 'URL Capture';
            $remoteUrl = $this->evidenceService->getScreenshotUrl($url);
            $filePath = $this->tryDownloadScreenshot($remoteUrl, $temuan->id);

            $ev = TemuanEvidence::create([
                'temuan_id'  => $temuan->id,
                'scan_id'    => $temuan->scan_id,
                'tipe'       => 'url_capture',
                'label'      => $label,
                'remote_url' => $remoteUrl,
                'file_path'  => $filePath,
                'meta'       => ['captured_url' => $url],
                'is_verified' => false,
            ]);

            return response()->json([
                'success'  => true,
                'evidence' => $this->formatEvidence($ev),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Gagal capture URL: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Tambah evidence teks (HTTP response, kode, dll) secara manual.
     * POST /evidence/{temuan}/add-text
     */
    public function addText(Request $request, Temuan $temuan): JsonResponse
    {
        $this->otorisasiTemuan($temuan);
        $request->validate([
            'tipe'    => 'required|in:http_response,file_content,manual',
            'label'   => 'required|string|max:200',
            'content' => 'required|string|max:50000',
        ]);

        $ev = TemuanEvidence::create([
            'temuan_id'  => $temuan->id,
            'scan_id'    => $temuan->scan_id,
            'tipe'       => $request->tipe,
            'label'      => $request->label,
            'remote_url' => null,
            'file_path'  => null,
            'meta'       => [
                'content'    => $request->content,
                'added_by'   => Auth::id(),
                'added_at'   => now()->toIso8601String(),
            ],
            'is_verified' => false,
        ]);

        return response()->json([
            'success'  => true,
            'evidence' => $this->formatEvidence($ev),
        ]);
    }

    /**
     * Upload file image sebagai evidence.
     * POST /evidence/{temuan}/upload
     */
    public function upload(Request $request, Temuan $temuan): JsonResponse
    {
        $this->otorisasiTemuan($temuan);
        $request->validate([
            'file'  => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120', // max 5MB
            'label' => 'nullable|string|max:200',
        ]);

        $file = $request->file('file');
        $path = $file->store("evidence/{$temuan->id}", 'public');

        $ev = TemuanEvidence::create([
            'temuan_id'  => $temuan->id,
            'scan_id'    => $temuan->scan_id,
            'tipe'       => 'manual',
            'label'      => $request->label ?: ($file->getClientOriginalName()),
            'remote_url' => null,
            'file_path'  => $path,
            'meta'       => [
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
                'uploaded_by'   => Auth::id(),
            ],
            'is_verified' => true, // upload manual = already verified
        ]);

        return response()->json([
            'success'  => true,
            'evidence' => $this->formatEvidence($ev),
        ]);
    }

    /**
     * Toggle status is_verified.
     * PATCH /evidence/{evidence}/verify
     */
    public function verify(TemuanEvidence $evidence): JsonResponse
    {
        $this->otorisasiEvidence($evidence);

        $evidence->update(['is_verified' => !$evidence->is_verified]);

        return response()->json([
            'success'     => true,
            'is_verified' => $evidence->is_verified,
        ]);
    }

    /**
     * Hapus satu evidence.
     * DELETE /evidence/{evidence}
     */
    public function destroy(TemuanEvidence $evidence): JsonResponse
    {
        $this->otorisasiEvidence($evidence);
        $evidence->delete(); // boot() akan hapus file lokal
        return response()->json(['success' => true]);
    }

    // ── Private Helpers ───────────────────────────────────────────────────

    private function otorisasiTemuan(Temuan $temuan): void
    {
        $scan = $temuan->scan;
        if (!$scan || $scan->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }
    }

    private function otorisasiEvidence(TemuanEvidence $evidence): void
    {
        $scan = $evidence->scan;
        if (!$scan || $scan->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }
    }

    private function tryDownloadScreenshot(string $remoteUrl, string $temuanId): ?string
    {
        try {
            $response = Http::timeout(8)->get($remoteUrl);
            if ($response->successful() && strlen($response->body()) > 1024) {
                $filename = "evidence/{$temuanId}/screenshot_" . Str::random(8) . '.jpg';
                Storage::disk('public')->put($filename, $response->body());
                return $filename;
            }
        } catch (\Throwable $e) {
            Log::info('EvidenceController: Gagal download screenshot, pakai remote URL. ' . $e->getMessage());
        }
        return null;
    }

    private function getScreenshotSource(?string $urlscanUuid): string
    {
        if ($urlscanUuid) return 'URLScan.io';
        if (config('services.screenshotone.key')) return 'ScreenshotOne';
        return 'thum.io';
    }

    private function formatEvidence(TemuanEvidence $e): array
    {
        return [
            'id'          => $e->id,
            'tipe'        => $e->tipe,
            'tipe_label'  => $e->tipe_label,
            'label'       => $e->label,
            'display_url' => $e->display_url,
            'remote_url'  => $e->remote_url,
            'meta'        => $e->meta,
            'is_verified' => $e->is_verified,
            'created_at'  => $e->created_at?->diffForHumans(),
        ];
    }
}
