<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScanBiasaService
{
    protected VirusTotalService $vtService;
    protected URLScanService $urlScanService;

    public function __construct(VirusTotalService $vtService, URLScanService $urlScanService)
    {
        $this->vtService = $vtService;
        $this->urlScanService = $urlScanService;
    }

    /**
     * Scan Mode 1 (Biasa) — Tanpa AI, dengan sensor output.
     */
    public function scan(string $url): array
    {
        $vtData = $this->vtService->getUrlReport($url) ?? [];
        $urlscanData = $this->submitAndPollUrlscan($url);
        $headerData = $this->analyzeHeaders($url);
        $sslData = $this->analyzeSSL($url);

        return $this->buildReport($url, $vtData, $urlscanData, $headerData, $sslData);
    }

    protected function submitAndPollUrlscan(string $url): array
    {
        // 1. Coba cari hasil scan terbaru di cache (dalam 24 jam terakhir)
        $cachedResult = $this->urlScanService->searchCachedScan($url);
        if ($cachedResult) {
            return $cachedResult;
        }

        // 2. Jika tidak ada di cache, buat scan baru (live)
        $submission = $this->urlScanService->submit($url, 'private');
        if (!$submission || empty($submission['uuid'])) {
            return [];
        }

        // Tunggu maksimal 45 detik
        $result = $this->urlScanService->pollResult($submission['uuid'], 45);
        return $result ?? [];
    }

    protected function analyzeHeaders(string $url): array
    {
        try {
            $response = Http::timeout(10)->withOptions([
                'verify' => false,
            ])->get($url);

            $headers = $response->headers();
            $requiredHeaders = [
                'Content-Security-Policy' => 20,
                'Strict-Transport-Security' => 20,
                'X-Frame-Options' => 15,
                'X-Content-Type-Options' => 15,
                'Referrer-Policy' => 15,
                'Permissions-Policy' => 15,
            ];

            $headerResults = [];
            $skor = 0;

            foreach ($requiredHeaders as $name => $poin) {
                $exists = isset($headers[strtolower($name)]) || isset($headers[$name]);
                $value = $headers[strtolower($name)][0] ?? $headers[$name][0] ?? null;
                $headerResults[$name] = [
                    'ada' => $exists,
                    'nilai' => $exists ? $value : null,
                    'poin' => $exists ? $poin : 0,
                ];
                if ($exists) $skor += $poin;
            }

            return [
                'skor' => $skor,
                'headers' => $headerResults,
            ];
        } catch (\Exception $e) {
            Log::warning('Header analysis gagal: ' . $e->getMessage());
            return ['skor' => 0, 'headers' => []];
        }
    }

    protected function analyzeSSL(string $url): array
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        if (empty($host) || ($parsed['scheme'] ?? '') !== 'https') {
            return ['valid' => false, 'status' => 'tidak_https', 'detail' => 'URL tidak menggunakan HTTPS'];
        }

        try {
            $context = stream_context_create([
                'ssl' => ['capture_peer_cert' => true, 'verify_peer' => true, 'verify_peer_name' => true],
            ]);
            $socket = @stream_socket_client(
                "ssl://{$host}:443", $errno, $errstr, 10,
                STREAM_CLIENT_CONNECT, $context
            );

            if (!$socket) {
                return ['valid' => false, 'status' => 'gagal_koneksi', 'detail' => $errstr];
            }

            $params = stream_context_get_params($socket);
            $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate'] ?? '');
            fclose($socket);

            if (!$cert) {
                return ['valid' => false, 'status' => 'sertifikat_error', 'detail' => 'Tidak dapat membaca sertifikat'];
            }

            $expiry = $cert['validTo_time_t'] ?? 0;
            $daysUntilExpiry = max(0, (int) ceil(($expiry - time()) / 86400));
            $issuer = $cert['issuer']['O'] ?? $cert['issuer']['CN'] ?? 'Tidak diketahui';
            $isExpired = $expiry < time();
            $isSelfSigned = ($cert['issuer']['CN'] ?? '') === ($cert['subject']['CN'] ?? '');

            $status = 'valid';
            if ($isExpired) $status = 'expired';
            elseif ($isSelfSigned) $status = 'self_signed';

            return [
                'valid' => !$isExpired && !$isSelfSigned,
                'status' => $status,
                'issuer' => $issuer,
                'expires_at' => date('Y-m-d H:i:s', $expiry),
                'days_until_expiry' => $daysUntilExpiry,
            ];
        } catch (\Exception $e) {
            Log::warning('SSL analysis gagal: ' . $e->getMessage());
            return ['valid' => false, 'status' => 'error', 'detail' => $e->getMessage()];
        }
    }

    protected function buildReport(string $url, array $vtData, array $urlscanData, array $headerData, array $sslData): array
    {
        // Hitung skor reputasi
        $vtStats = $vtData['last_analysis_stats'] ?? [];
        $malicious = $vtStats['malicious'] ?? 0;
        $suspicious = $vtStats['suspicious'] ?? 0;
        $total = array_sum($vtStats) ?: 1;
        $vtScore = max(0, 100 - (($malicious * 3 + $suspicious) / $total * 100));

        $headerScore = $headerData['skor'] ?? 0;
        $sslBonus = ($sslData['valid'] ?? false) ? 15 : 0;
        $skorReputasi = min(100, (int) round(($vtScore * 0.5) + ($headerScore * 0.35) + $sslBonus));

        // Tentukan status
        if ($malicious > 3) $status = 'berbahaya';
        elseif ($malicious > 0 || $suspicious > 1) $status = 'mencurigakan';
        else $status = 'aman';

        // Deteksi teknologi dari URLScan
        $teknologi = [];
        $pageInfo = $urlscanData['page'] ?? [];
        if (!empty($pageInfo['server'])) $teknologi[] = $pageInfo['server'];

        foreach ($urlscanData['meta']['processors'] ?? [] as $proc) {
            foreach ($proc['data'] ?? [] as $item) {
                if (!empty($item['app'])) $teknologi[] = $item['app'];
            }
        }
        $teknologi = array_unique(array_slice($teknologi, 0, 15));

        // Screenshot
        $screenshotUrl = null;
        if (!empty($urlscanData['task']['uuid'])) {
            $screenshotUrl = $this->urlScanService->getScreenshotUrl($urlscanData['task']['uuid']);
        }

        return [
            'skor_reputasi' => $skorReputasi,
            'status' => $status,
            'ssl' => $sslData,
            'security_headers' => $headerData,
            'virustotal' => [
                'stats' => $vtStats,
                'reputation' => $vtData['reputation'] ?? 0,
                'total_votes' => $vtData['total_votes'] ?? [],
                'categories' => $vtData['categories'] ?? [],
                'malicious_count' => $malicious,
                'total_engines' => $total,
            ],
            'teknologi' => $teknologi,
            'screenshot_url' => $screenshotUrl,
        ];
    }
}
