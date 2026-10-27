<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * SSRFProtectionService
 *
 * Mencegah Server-Side Request Forgery (SSRF) pada fitur scan URL.
 * Memvalidasi bahwa URL target tidak mengarah ke:
 * - IP private/internal (RFC 1918, loopback, link-local)
 * - Metadata cloud (169.254.169.254 AWS/GCP, dll)
 * - Domain internal RedSim sendiri
 *
 * Berdasarkan rekomendasi OWASP SSRF Prevention Cheat Sheet.
 */
class SSRFProtectionService
{
    /**
     * Range IP yang diblokir (private, loopback, link-local, reserved).
     */
    private const BLOCKED_RANGES = [
        // Loopback
        '127.0.0.0/8',
        '::1/128',

        // Private RFC 1918
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',

        // Link-local (AWS/GCP metadata, APIPA)
        '169.254.0.0/16',
        'fe80::/10',

        // Shared address space RFC 6598
        '100.64.0.0/10',

        // Loopback IPv6
        '::1/128',
        '0:0:0:0:0:0:0:1/128',

        // Unspecified
        '0.0.0.0/8',

        // Documentation ranges (RFC 5737)
        '192.0.2.0/24',
        '198.51.100.0/24',
        '203.0.113.0/24',

        // Broadcast
        '255.255.255.255/32',
    ];

    /**
     * Domain/host yang diblokir secara eksplisit (cloud metadata endpoints).
     */
    private const BLOCKED_HOSTS = [
        'localhost',
        'metadata.google.internal',
        'metadata.goog',
        '169.254.169.254',     // AWS/GCP/Azure/DO instance metadata
        'fd00:ec2::254',        // AWS metadata IPv6
        '100.100.100.200',      // Alibaba Cloud metadata
        '192.0.0.192',         // Azure IMDS
    ];

    /**
     * Skema yang diizinkan (hanya HTTP/HTTPS publik).
     */
    private const ALLOWED_SCHEMES = ['http', 'https'];

    /**
     * Port yang diizinkan untuk di-scan (hanya port web standar).
     */
    private const ALLOWED_PORTS = [80, 443, 8080, 8443, 3000, 5000];

    /**
     * Validasi URL sebelum digunakan sebagai target scan.
     *
     * @param  string $url URL yang ingin di-scan
     * @return array{valid: bool, reason: string|null}
     */
    public function validate(string $url): array
    {
        // 1. Pastikan URL bisa di-parse
        $parsed = @parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return ['valid' => false, 'reason' => 'URL tidak valid atau tidak dapat di-parse.'];
        }

        $scheme = strtolower($parsed['scheme'] ?? '');
        $host   = strtolower($parsed['host']);
        $port   = isset($parsed['port']) ? (int) $parsed['port'] : ($scheme === 'https' ? 443 : 80);

        // 2. Cek skema
        if (!in_array($scheme, self::ALLOWED_SCHEMES)) {
            return ['valid' => false, 'reason' => "Skema '{$scheme}' tidak diizinkan. Gunakan http atau https."];
        }

        // 3. Cek port (hanya jika explicit port diberikan)
        if (isset($parsed['port']) && !in_array($port, self::ALLOWED_PORTS)) {
            return ['valid' => false, 'reason' => "Port {$port} tidak diizinkan untuk di-scan."];
        }

        // 4. Cek domain/host yang diblokir eksplisit
        if (in_array($host, self::BLOCKED_HOSTS)) {
            Log::warning('SSRF attempt blocked', ['url' => $url, 'host' => $host, 'reason' => 'blocked_host']);
            return ['valid' => false, 'reason' => 'Host yang dituju tidak diizinkan.'];
        }

        // 5. Cek apakah host adalah IP langsung
        $ip = null;
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ip = $host;
        } else {
            // Resolve DNS — cegah DNS rebinding
            $resolvedIps = $this->resolveHost($host);
            if (empty($resolvedIps)) {
                return ['valid' => false, 'reason' => 'Host tidak dapat di-resolve. Periksa URL atau coba lagi.'];
            }
            // Cek semua IP hasil resolusi
            foreach ($resolvedIps as $resolvedIp) {
                $check = $this->isIpBlocked($resolvedIp);
                if ($check) {
                    Log::warning('SSRF attempt blocked via DNS resolution', [
                        'url'      => $url,
                        'host'     => $host,
                        'resolved' => $resolvedIp,
                        'reason'   => 'private_ip_via_dns',
                    ]);
                    return ['valid' => false, 'reason' => 'URL mengarah ke alamat IP internal yang tidak diizinkan.'];
                }
            }
            $ip = $resolvedIps[0] ?? null;
        }

        // 6. Cek IP langsung terhadap range blokir
        if ($ip !== null && $this->isIpBlocked($ip)) {
            Log::warning('SSRF attempt blocked', ['url' => $url, 'ip' => $ip, 'reason' => 'blocked_ip_range']);
            return ['valid' => false, 'reason' => 'URL mengarah ke alamat IP internal yang tidak diizinkan.'];
        }

        return ['valid' => true, 'reason' => null];
    }

    /**
     * Resolve hostname ke daftar IP address.
     */
    private function resolveHost(string $host): array
    {
        $ips = [];

        // IPv4
        $records4 = @dns_get_record($host, DNS_A);
        if ($records4) {
            foreach ($records4 as $r) {
                if (!empty($r['ip'])) $ips[] = $r['ip'];
            }
        }

        // IPv6
        $records6 = @dns_get_record($host, DNS_AAAA);
        if ($records6) {
            foreach ($records6 as $r) {
                if (!empty($r['ipv6'])) $ips[] = $r['ipv6'];
            }
        }

        // Fallback ke gethostbyname jika DNS lookup gagal
        if (empty($ips)) {
            $resolved = @gethostbyname($host);
            if ($resolved !== $host && filter_var($resolved, FILTER_VALIDATE_IP)) {
                $ips[] = $resolved;
            }
        }

        return array_unique($ips);
    }

    /**
     * Cek apakah IP berada di dalam range yang diblokir.
     */
    private function isIpBlocked(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return true; // Reject invalid IPs
        }

        foreach (self::BLOCKED_RANGES as $cidr) {
            if ($this->ipInRange($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek apakah IP ada dalam CIDR range.
     */
    private function ipInRange(string $ip, string $cidr): bool
    {
        try {
            [$subnet, $prefixLength] = explode('/', $cidr) + [null, null];
            if ($subnet === null || $prefixLength === null) return false;

            // Handle IPv6
            if (str_contains($ip, ':') || str_contains($subnet, ':')) {
                if (!str_contains($ip, ':') || !str_contains($subnet, ':')) return false;
                $ipBin     = inet_pton($ip);
                $subnetBin = inet_pton($subnet);
                if ($ipBin === false || $subnetBin === false) return false;
                $prefixLength = (int) $prefixLength;
                for ($i = 0; $i < $prefixLength; $i++) {
                    $byteIdx = (int) ($i / 8);
                    $bitIdx  = 7 - ($i % 8);
                    if ((ord($ipBin[$byteIdx]) >> $bitIdx & 1) !== (ord($subnetBin[$byteIdx]) >> $bitIdx & 1)) {
                        return false;
                    }
                }
                return true;
            }

            // Handle IPv4
            $ipLong     = ip2long($ip);
            $subnetLong = ip2long($subnet);
            if ($ipLong === false || $subnetLong === false) return false;
            $mask = ~((1 << (32 - (int) $prefixLength)) - 1);
            return ($ipLong & $mask) === ($subnetLong & $mask);

        } catch (\Throwable) {
            return false;
        }
    }
}
