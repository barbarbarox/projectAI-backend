<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class URLScanService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('URLSCAN_API_KEY', '');
    }

    /**
     * Cari hasil scan yang sudah ada di URLScan (cache).
     * Jika ada scan dalam 24 jam terakhir, kita pakai hasilnya agar tidak perlu menunggu 45 detik.
     */
    public function searchCachedScan(string $url): ?array
    {
        try {
            // Encode tanda kutip untuk query: page.url:"https://example.com"
            $query = urlencode('page.url:"' . $url . '"');
            
            $response = Http::withHeaders([
                'API-Key' => $this->apiKey,
            ])->timeout(10)->get("https://urlscan.io/api/v1/search/?q={$query}&size=1");

            if ($response->successful()) {
                $data = $response->json();
                $results = $data['results'] ?? [];
                
                if (count($results) > 0) {
                    $latestScan = $results[0];
                    // Pastikan umurnya kurang dari 24 jam (86400 detik)
                    // URLScan menyimpan 'time' dalam ISO format
                    $scanTime = strtotime($latestScan['task']['time'] ?? '');
                    if ($scanTime && (time() - $scanTime) < 86400) {
                        Log::info("URLScan cache hit untuk: {$url} (UUID: {$latestScan['_id']})");
                        return $this->getResult($latestScan['_id']);
                    }
                }
            }
            return null;
        } catch (\Exception $e) {
            Log::warning('URLScan search error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Submit URL untuk scan (Live).
     */
    public function submit(string $url, string $visibility = 'private'): ?array
    {
        try {
            $response = Http::withHeaders([
                'API-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://urlscan.io/api/v1/scan/', [
                'url' => $url,
                'visibility' => $visibility,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('URLScan submit gagal: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('URLScan error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Polling hasil scan sampai tersedia atau timeout.
     */
    public function pollResult(string $uuid, int $timeout = 45): ?array
    {
        $start = time();
        $interval = 3;

        while ((time() - $start) < $timeout) {
            $result = $this->getResult($uuid);
            if ($result !== null) {
                return $result;
            }
            sleep($interval);
        }

        Log::warning("URLScan polling timeout untuk UUID: {$uuid}");
        return null;
    }

    /**
     * Ambil hasil scan berdasarkan UUID.
     */
    public function getResult(string $uuid): ?array
    {
        try {
            $response = Http::get("https://urlscan.io/api/v1/result/{$uuid}/");
            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('URLScan result error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ambil screenshot URL dari URLScan.
     */
    public function getScreenshotUrl(string $uuid): string
    {
        return "https://urlscan.io/screenshots/{$uuid}.png";
    }
}
