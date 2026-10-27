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
     * 1. URLScan UUID jika tersedia
     * 2. ScreenshotOne API jika terkonfigurasi di env/config
     * 3. Fallback gratis ke thum.io (1280x720 crop)
     */
    public function getScreenshotUrl(string $url, ?string $urlscanUuid = null): string
    {
        if (!empty($urlscanUuid)) {
            return "https://urlscan.io/screenshots/{$urlscanUuid}.png";
        }

        $screenshotOneKey = config('services.screenshotone.key', env('SCREENSHOTONE_API_KEY'));
        if (!empty($screenshotOneKey)) {
            $query = http_build_query([
                'access_key'          => $screenshotOneKey,
                'url'                 => $url,
                'viewport_width'      => 1280,
                'viewport_height'     => 720,
                'device_scale_factor' => 1,
                'format'              => 'jpg',
            ]);
            return "https://api.screenshotone.com/take?{$query}";
        }

        // Fallback gratis thum.io (cepat & tanpa API key)
        $encoded = urlencode($url);
        return "https://image.thum.io/get/width/1280/crop/720/noanimate/url/{$encoded}";
    }

    /**
     * Unduh dan simpan screenshot ke local storage jika diperlukan.
     */
    public function captureAndStore(string $url, ?string $urlscanUuid = null): ?string
    {
        try {
            $remoteUrl = $this->getScreenshotUrl($url, $urlscanUuid);
            $response = Http::timeout(8)->get($remoteUrl);

            if ($response->successful() && strlen($response->body()) > 1024) {
                $filename = 'screenshots/' . md5($url) . '_' . time() . '.jpg';
                Storage::disk('public')->put($filename, $response->body());
                return Storage::disk('public')->url($filename);
            }
        } catch (\Throwable $e) {
            Log::info("VisualEvidenceService fallback ke remote URL: " . $e->getMessage());
        }

        return $this->getScreenshotUrl($url, $urlscanUuid);
    }
}
