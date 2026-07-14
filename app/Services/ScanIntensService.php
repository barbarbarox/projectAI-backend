<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScanIntensService
{
    protected VirusTotalService $vtService;
    protected URLScanService $urlScanService;

    public function __construct(VirusTotalService $vtService, URLScanService $urlScanService)
    {
        $this->vtService = $vtService;
        $this->urlScanService = $urlScanService;
    }

    /**
     * Scan Mode 2 (Intens) — Tanpa sensor, semua data mentah.
     */
    public function scan(string $url): array
    {
        $vtData = $this->vtService->getUrlReport($url) ?? [];
        $urlscanData = $this->submitAndPollUrlscan($url);
        $headerData = $this->analyzeHeaders($url);
        $sslData = $this->analyzeSSL($url);
        $commonFiles = $this->checkCommonFiles($url);

        return $this->buildReport($url, $vtData, $urlscanData, $headerData, $sslData, $commonFiles);
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

    /**
     * Analisis HTTP headers lengkap.
     */
    public function analyzeHeaders(string $url): array
    {
        try {
            $response = Http::timeout(10)->withOptions([
                'verify' => false,
            ])->get($url);

            $allHeaders = [];
            foreach ($response->headers() as $name => $values) {
                $allHeaders[$name] = $values[0] ?? '';
            }

            $securityHeaders = [
                'Content-Security-Policy' => 20,
                'Strict-Transport-Security' => 20,
                'X-Frame-Options' => 15,
                'X-Content-Type-Options' => 15,
                'Referrer-Policy' => 15,
                'Permissions-Policy' => 15,
            ];

            $headerEvaluation = [];
            $skor = 0;

            foreach ($securityHeaders as $name => $poin) {
                $lowerName = strtolower($name);
                $exists = isset($allHeaders[$lowerName]) || isset($allHeaders[$name]);
                $value = $allHeaders[$lowerName] ?? $allHeaders[$name] ?? null;

                $status = 'missing';
                if ($exists) {
                    $status = $this->evaluateHeaderValue($name, $value) ? 'baik' : 'misconfigured';
                    $skor += $poin;
                }

                $headerEvaluation[$name] = [
                    'ada' => $exists,
                    'nilai' => $value,
                    'status' => $status,
                    'poin' => $exists ? $poin : 0,
                ];
            }

            // Deteksi headers sensitif yang expose info server
            $sensitiveHeaders = ['server', 'x-powered-by', 'x-aspnet-version', 'x-generator'];
            $exposedHeaders = [];
            foreach ($sensitiveHeaders as $sh) {
                if (isset($allHeaders[$sh])) {
                    $exposedHeaders[$sh] = $allHeaders[$sh];
                }
            }

            return [
                'skor' => $skor,
                'headers_keamanan' => $headerEvaluation,
                'headers_sensitif' => $exposedHeaders,
                'semua_headers' => $allHeaders,
            ];
        } catch (\Exception $e) {
            Log::warning('Header analysis intens gagal: ' . $e->getMessage());
            return ['skor' => 0, 'headers_keamanan' => [], 'headers_sensitif' => [], 'semua_headers' => []];
        }
    }

    protected function evaluateHeaderValue(string $name, ?string $value): bool
    {
        if (empty($value)) return false;

        return match ($name) {
            'X-Content-Type-Options' => strtolower($value) === 'nosniff',
            'X-Frame-Options' => in_array(strtoupper($value), ['DENY', 'SAMEORIGIN']),
            'Referrer-Policy' => !empty($value),
            default => true,
        };
    }

    /**
     * Analisis SSL/TLS secara detail.
     */
    public function analyzeSSL(string $url): array
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        if (empty($host) || ($parsed['scheme'] ?? '') !== 'https') {
            return ['valid' => false, 'status' => 'tidak_https', 'detail' => 'URL tidak menggunakan HTTPS'];
        }

        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'capture_peer_cert_chain' => true,
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
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

            // TLS version
            $meta = stream_get_meta_data($socket);
            $protocol = $meta['crypto']['protocol'] ?? 'unknown';
            fclose($socket);

            if (!$cert) {
                return ['valid' => false, 'status' => 'sertifikat_error', 'detail' => 'Tidak dapat membaca sertifikat'];
            }

            $expiry = $cert['validTo_time_t'] ?? 0;
            $validFrom = $cert['validFrom_time_t'] ?? 0;
            $daysUntilExpiry = max(0, (int) ceil(($expiry - time()) / 86400));
            $issuer = $cert['issuer']['O'] ?? $cert['issuer']['CN'] ?? 'Tidak diketahui';
            $subject = $cert['subject']['CN'] ?? 'Tidak diketahui';
            $isExpired = $expiry < time();
            $isSelfSigned = ($cert['issuer']['CN'] ?? '') === ($cert['subject']['CN'] ?? '')
                && empty($cert['issuer']['O']);

            // Chain validation
            $chain = $params['options']['ssl']['peer_certificate_chain'] ?? [];
            $chainValid = count($chain) > 1;

            $status = 'valid';
            if ($isExpired) $status = 'expired';
            elseif ($isSelfSigned) $status = 'self_signed';

            return [
                'valid' => !$isExpired && !$isSelfSigned,
                'status' => $status,
                'issuer' => $issuer,
                'subject' => $subject,
                'valid_from' => date('Y-m-d H:i:s', $validFrom),
                'expires_at' => date('Y-m-d H:i:s', $expiry),
                'days_until_expiry' => $daysUntilExpiry,
                'protocol' => $protocol,
                'chain_valid' => $chainValid,
                'chain_length' => count($chain),
            ];
        } catch (\Exception $e) {
            Log::warning('SSL analysis intens gagal: ' . $e->getMessage());
            return ['valid' => false, 'status' => 'error', 'detail' => $e->getMessage()];
        }
    }

    /**
     * Cek file umum yang mungkin terekspos.
     */
    public function checkCommonFiles(string $url): array
    {
        $parsed = parse_url($url);
        $base = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');

        $paths = [
            '/robots.txt',
            '/sitemap.xml',
            '/.git/HEAD',
            '/wp-config.php.bak',
            '/.env',
            '/phpinfo.php',
            '/admin',
            '/wp-admin',
            '/administrator',
        ];

        $results = [];
        foreach ($paths as $path) {
            try {
                $response = Http::timeout(5)->withOptions([
                    'verify' => false,
                    'allow_redirects' => ['max' => 2],
                ])->get($base . $path);

                $results[] = [
                    'path' => $path,
                    'status' => $response->status(),
                    'terekspos' => $response->status() === 200,
                    'size' => strlen($response->body()),
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'path' => $path,
                    'status' => 0,
                    'terekspos' => false,
                    'error' => 'Timeout',
                ];
            }
        }

        return $results;
    }

    protected function buildReport(string $url, array $vtData, array $urlscanData, array $headerData, array $sslData, array $commonFiles): array
    {
        // Skor reputasi
        $vtStats = $vtData['last_analysis_stats'] ?? [];
        $malicious = $vtStats['malicious'] ?? 0;
        $suspicious = $vtStats['suspicious'] ?? 0;
        $total = array_sum($vtStats) ?: 1;
        $vtScore = max(0, 100 - (($malicious * 3 + $suspicious) / $total * 100));

        $headerScore = $headerData['skor'] ?? 0;
        $sslBonus = ($sslData['valid'] ?? false) ? 15 : 0;

        // Penalty untuk file sensitif terekspos
        $exposedCount = count(array_filter($commonFiles, fn($f) => $f['terekspos'] && in_array($f['path'], ['/.git/HEAD', '/.env', '/wp-config.php.bak', '/phpinfo.php'])));
        $exposedPenalty = $exposedCount * 10;

        $skorReputasi = min(100, max(0, (int) round(($vtScore * 0.45) + ($headerScore * 0.35) + $sslBonus - $exposedPenalty)));

        // Status
        if ($malicious > 3 || $exposedCount >= 2) $status = 'berbahaya';
        elseif ($malicious > 0 || $suspicious > 1 || $exposedCount >= 1) $status = 'mencurigakan';
        else $status = 'aman';

        // Teknologi dari URLScan
        $teknologi = [];
        $pageInfo = $urlscanData['page'] ?? [];
        if (!empty($pageInfo['server'])) $teknologi[] = $pageInfo['server'];
        if (!empty($pageInfo['asnname'])) $teknologi[] = 'ASN: ' . $pageInfo['asnname'];

        foreach ($urlscanData['meta']['processors'] ?? [] as $proc) {
            foreach ($proc['data'] ?? [] as $item) {
                if (!empty($item['app'])) $teknologi[] = $item['app'];
            }
        }
        $teknologi = array_unique(array_slice($teknologi, 0, 20));

        // Server info (intens — tidak disensor)
        $serverInfo = [
            'ip' => $pageInfo['ip'] ?? null,
            'asn' => $pageInfo['asn'] ?? null,
            'asnname' => $pageInfo['asnname'] ?? null,
            'country' => $pageInfo['country'] ?? null,
            'server' => $pageInfo['server'] ?? null,
        ];

        // Resources dari URLScan
        $resources = [];
        foreach (array_slice($urlscanData['data']['requests'] ?? [], 0, 50) as $req) {
            $r = $req['request'] ?? [];
            $resp = $req['response'] ?? [];
            $resources[] = [
                'url' => $r['request']['url'] ?? '',
                'method' => $r['request']['method'] ?? 'GET',
                'type' => $resp['response']['mimeType'] ?? '',
                'status' => $resp['response']['status'] ?? 0,
                'size' => $resp['dataLength'] ?? 0,
            ];
        }

        // External links
        $links = array_slice($urlscanData['data']['links'] ?? [], 0, 30);

        // Console messages
        $consoleMessages = array_slice($urlscanData['data']['console'] ?? [], 0, 10);

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
            'server_info' => $serverInfo,
            'teknologi' => $teknologi,
            'resources' => $resources,
            'external_links' => $links,
            'console_messages' => $consoleMessages,
            'common_files' => $commonFiles,
            'screenshot_url' => $screenshotUrl,
        ];
    }
}
