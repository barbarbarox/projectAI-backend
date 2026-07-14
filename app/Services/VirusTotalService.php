<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VirusTotalService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('VIRUSTOTAL_API_KEY', '');
    }

    /**
     * Submit URL untuk scan baru.
     */
    public function scanUrl(string $url): ?array
    {
        try {
            $response = Http::withHeaders([
                'x-apikey' => $this->apiKey,
            ])->asForm()->post('https://www.virustotal.com/api/v3/urls', [
                'url' => $url,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            Log::warning('VirusTotal scan gagal: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('VirusTotal error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ambil laporan URL yang sudah ada.
     */
    public function getUrlReport(string $url): ?array
    {
        try {
            $urlId = rtrim(base64_encode($url), '=');

            $response = Http::withHeaders([
                'x-apikey' => $this->apiKey,
            ])->get("https://www.virustotal.com/api/v3/urls/{$urlId}");

            if ($response->successful()) {
                $data = $response->json('data.attributes') ?? [];
                return [
                    'last_analysis_stats' => $data['last_analysis_stats'] ?? [],
                    'last_analysis_results' => $data['last_analysis_results'] ?? [],
                    'reputation' => $data['reputation'] ?? 0,
                    'total_votes' => $data['total_votes'] ?? [],
                    'last_http_response_code' => $data['last_http_response_code'] ?? null,
                    'last_analysis_date' => $data['last_analysis_date'] ?? null,
                    'categories' => $data['categories'] ?? [],
                ];
            }

            // Jika belum ada report, submit scan dulu
            if ($response->status() === 404) {
                $scanResult = $this->scanUrl($url);
                if ($scanResult) {
                    // Tunggu sebentar lalu coba ambil lagi
                    sleep(5);
                    return $this->getUrlReport($url);
                }
            }

            Log::warning('VirusTotal report gagal: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('VirusTotal report error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Ambil hasil analisis berdasarkan analysis ID.
     */
    public function getAnalysis(string $analysisId): ?array
    {
        try {
            $response = Http::withHeaders([
                'x-apikey' => $this->apiKey,
            ])->get("https://www.virustotal.com/api/v3/analyses/{$analysisId}");

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('VirusTotal analysis error: ' . $e->getMessage());
            return null;
        }
    }
}
