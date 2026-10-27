<?php

namespace App\Services;

class DefaultLoginDetectionService
{
    /**
     * Audit keamanan portal login dan antarmuka administratif yang ditemukan.
     */
    public function auditPortals(array $commonFilesResults, string $baseUrl, array $wafCdn = []): array
    {
        $portals = [];
        $hasWaf = !empty($wafCdn['detected']) || in_array('Cloudflare', $wafCdn['providers'] ?? []);
        $isHttps = str_starts_with(strtolower($baseUrl), 'https://');

        $criticalExposures = 0;
        $totalExposed = 0;

        foreach ($commonFilesResults as $item) {
            $isLoginPath = in_array($item['kategori'] ?? '', ['admin_panel', 'enterprise'])
                || in_array($item['path'] ?? '', ['/login', '/admin', '/wp-admin', '/phpmyadmin', '/adminer', '/actuator/env', '/administrator']);

            if (!$isLoginPath) {
                continue;
            }

            $isExposed = (bool) ($item['terekspos'] ?? false);
            $statusCode = $item['status'] ?? 0;

            if ($isExposed || in_array($statusCode, [200, 401, 403])) {
                $totalExposed++;

                $isDbManager = in_array($item['path'], ['/phpmyadmin', '/adminer']);
                $isActuator = str_contains($item['path'], '/actuator');
                
                $riskLevel = 'rendah';
                if ($isDbManager || $isActuator) {
                    $riskLevel = 'kritis';
                    $criticalExposures++;
                } elseif (in_array($item['path'], ['/admin', '/wp-admin', '/administrator'])) {
                    $riskLevel = 'sedang';
                }

                $issues = [];
                if ($isDbManager && $statusCode === 200) {
                    $issues[] = 'Antarmuka manajemen database terekspos langsung ke internet publik tanpa IP whitelist';
                }
                if (!$hasWaf) {
                    $issues[] = 'Tidak terdeteksi WAF / proteksi mitigasi brute-force';
                }
                if (!$isHttps) {
                    $issues[] = 'Transmisi kredensial tidak dienkripsi (menggunakan HTTP non-SSL)';
                }

                $portals[] = [
                    'path'        => $item['path'],
                    'url'         => rtrim($baseUrl, '/') . $item['path'],
                    'deskripsi'   => $item['deskripsi'] ?? 'Administrative Portal',
                    'status_http' => $statusCode,
                    'terbuka'     => $isExposed,
                    'risiko'      => $riskLevel,
                    'has_waf'     => $hasWaf,
                    'is_https'    => $isHttps,
                    'issues'      => $issues,
                ];
            }
        }

        $posture = 'aman';
        if ($criticalExposures > 0) {
            $posture = 'kritis';
        } elseif ($totalExposed > 0) {
            $posture = 'waspada';
        }

        return [
            'total_portals_found' => count($portals),
            'critical_count'      => $criticalExposures,
            'posture'             => $posture,
            'has_waf'             => $hasWaf,
            'portals'             => $portals,
            'recommendations'     => $this->generateRecommendations($portals, $hasWaf, $isHttps),
        ];
    }

    protected function generateRecommendations(array $portals, bool $hasWaf, bool $isHttps): array
    {
        $recs = [];
        if (!empty($portals)) {
            $recs[] = 'Aktifkan Multi-Factor Authentication (MFA/2FA) wajib untuk seluruh akun administratif.';
            $recs[] = 'Terapkan pembatasan IP (IP Whitelisting) atau koneksi VPN untuk mengakses portal /admin dan database manager.';
        }
        if (!$hasWaf) {
            $recs[] = 'Pasang Web Application Firewall (WAF) seperti Cloudflare atau ModSecurity untuk proteksi rate-limiting & mitigasi brute-force credential stuffing.';
        }
        if (!$isHttps) {
            $recs[] = 'Wajibkan pengalihan seluruh akses login ke HTTPS untuk melindungi kebocoran username & password di jaringan.';
        }
        return $recs;
    }
}
