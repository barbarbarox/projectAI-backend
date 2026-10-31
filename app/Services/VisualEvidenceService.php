<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VisualEvidenceService
{
    /**
     * Dapatkan URL capture screenshot untuk target URL.
     * Menggunakan strategi bertingkat:
     *   1. URLScan UUID jika tersedia (gratis, paling akurat)
     *   2. ScreenshotOne API jika terkonfigurasi di env
     *   3. Fallback gratis ke thum.io
     *
     * CATATAN PENTING — thum.io & URL proxy lainnya:
     *   URL target HARUS di-append RAW (mentah), bukan di-urlencode().
     *   urlencode() menghasilkan %2F untuk slash dan %3A untuk colon
     *   yang menyebabkan HTTP 400 "encoded slash not allowed"
     *   di server proxy / Tomcat di belakang layanan screenshot.
     */
    public function getScreenshotUrl(string $url, ?string $urlscanUuid = null): string
    {
        // ── Strategi 1: URLScan.io UUID ─────────────────────────────────
        if (!empty($urlscanUuid)) {
            return "https://urlscan.io/screenshots/{$urlscanUuid}.png";
        }

        // ── Strategi 2: ScreenshotOne API ───────────────────────────────
        $screenshotOneKey = config('services.screenshotone.key', env('SCREENSHOTONE_API_KEY'));
        if (!empty($screenshotOneKey)) {
            // http_build_query OK di sini: URL target masuk sebagai query param (bukan path)
            $params = http_build_query([
                'access_key'          => $screenshotOneKey,
                'url'                 => $url,
                'viewport_width'      => 1280,
                'viewport_height'     => 720,
                'device_scale_factor' => 1,
                'format'              => 'jpg',
                'full_page'           => 'false',
            ]);
            return "https://api.screenshotone.com/take?{$params}";
        }

        // ── Strategi 3: thum.io (gratis, tanpa API key) ─────────────────
        // Format: https://image.thum.io/get/{options}/{targetUrl}
        // URL target HARUS di-append mentah — jangan urlencode() / rawurlencode()
        $cleanUrl = $this->sanitizeUrl($url);
        return "https://image.thum.io/get/width/1280/crop/720/noanimate/" . $cleanUrl;
    }

    /**
     * Unduh screenshot dan simpan ke local storage.
     * Jika download gagal, kembalikan remote URL sebagai fallback.
     */
    public function captureAndStore(string $url, ?string $urlscanUuid = null): ?string
    {
        try {
            $remoteUrl = $this->getScreenshotUrl($url, $urlscanUuid);
            $response  = Http::timeout(10)->get($remoteUrl);

            if ($response->successful() && strlen($response->body()) > 2048) {
                $filename = 'screenshots/' . md5($url) . '_' . time() . '.jpg';
                Storage::disk('public')->put($filename, $response->body());
                return Storage::disk('public')->url($filename);
            }
        } catch (\Throwable $e) {
            Log::info('VisualEvidenceService: download gagal, pakai remote URL. ' . $e->getMessage());
        }

        return $this->getScreenshotUrl($url, $urlscanUuid);
    }

    /**
     * Sanitasi URL target agar aman sebagai path suffix di thum.io.
     *
     * BOLEH: Trim spasi, pastikan ada skema, encode spasi/karakter kontrol.
     * TIDAK BOLEH: urlencode() atau rawurlencode() — akan encode : dan /
     *              menjadi %3A%2F%2F sehingga server proxy reject dengan 400.
     */
    private function sanitizeUrl(string $url): string
    {
        $url = trim($url);

        // Tambah skema default jika tidak ada
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        // Encode HANYA karakter yang benar-benar bermasalah:
        // spasi, <, >, ", {, }, |, \, ^, `
        // Slash (/), colon (:), tanda tanya (?), pagar (#) dll TIDAK di-encode
        $url = preg_replace_callback(
            '/[\x00-\x1f\x7f\s<>"{}|\\\\^`]/',
            static fn($m) => rawurlencode($m[0]),
            $url
        );

        return $url;
    }
}
