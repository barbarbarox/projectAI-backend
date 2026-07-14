<?php

namespace App\Services;

class LogAnomalyDetectorService
{
    public function detect(array $entries): array
    {
        return [
            'brute_force' => $this->detectBruteForce($entries),
            'scanning' => $this->detectScanning($entries),
            'injection' => $this->detectInjection($entries),
            'suspicious_ua' => $this->detectSuspiciousUA($entries),
        ];
    }

    public function detectBruteForce(array $entries): array
    {
        $ipCounts = [];
        $ipFailures = [];
        foreach ($entries as $e) {
            $ip = $e['ip'] ?? null;
            if (!$ip) continue;
            $ipCounts[$ip] = ($ipCounts[$ip] ?? 0) + 1;
            $status = $e['status_code'] ?? null;
            $msg = strtolower($e['message'] ?? '');
            if (in_array($status, [401, 403]) || str_contains($msg, 'failed') || str_contains($msg, 'invalid')) {
                $ipFailures[$ip] = ($ipFailures[$ip] ?? 0) + 1;
            }
        }
        $anomalies = [];
        foreach ($ipCounts as $ip => $count) {
            if ($count > 50) {
                $anomalies[] = ['tipe' => 'high_frequency', 'ip' => $ip, 'jumlah' => $count, 'keterangan' => "IP {$ip} melakukan {$count} request"];
            }
        }
        foreach ($ipFailures as $ip => $count) {
            if ($count > 5) {
                $anomalies[] = ['tipe' => 'auth_failure', 'ip' => $ip, 'jumlah' => $count, 'keterangan' => "IP {$ip} memiliki {$count} percobaan autentikasi gagal"];
            }
        }
        return $anomalies;
    }

    public function detectScanning(array $entries): array
    {
        $sensitif = ['/.env','/.git','/.git/HEAD','/wp-config.php','/phpinfo.php','/admin','/wp-admin','/shell','/cmd'];
        $anomalies = [];
        $ip404 = [];
        foreach ($entries as $e) {
            $path = strtolower($e['path'] ?? '');
            $ip = $e['ip'] ?? 'unknown';
            foreach ($sensitif as $sp) {
                if (str_contains($path, $sp)) {
                    $anomalies[] = ['tipe' => 'sensitive_path', 'ip' => $ip, 'path' => $e['path'], 'keterangan' => "Akses path sensitif {$e['path']} dari {$ip}"];
                    break;
                }
            }
            if (str_contains($path, '../')) {
                $anomalies[] = ['tipe' => 'path_traversal', 'ip' => $ip, 'path' => $e['path'], 'keterangan' => "Path traversal dari {$ip}"];
            }
            if (($e['status_code'] ?? null) === 404) $ip404[$ip] = ($ip404[$ip] ?? 0) + 1;
        }
        foreach ($ip404 as $ip => $count) {
            if ($count > 20) {
                $anomalies[] = ['tipe' => '404_flood', 'ip' => $ip, 'jumlah' => $count, 'keterangan' => "IP {$ip}: {$count} error 404"];
            }
        }
        return $anomalies;
    }

    public function detectInjection(array $entries): array
    {
        $sql = ['select ','union ','drop ','insert ','or 1=1',"or '1'='1",'information_schema'];
        $xss = ['<script','javascript:','onerror=','onload=','alert(','document.cookie'];
        $cmd = ['cmd=','exec=','system(','passthru(','shell_exec(','/bin/sh','/bin/bash'];
        $anomalies = [];
        foreach ($entries as $e) {
            $combined = strtolower(($e['path'] ?? '') . ' ' . ($e['user_agent'] ?? '') . ' ' . ($e['message'] ?? ''));
            $ip = $e['ip'] ?? 'unknown';
            foreach ($sql as $p) { if (str_contains($combined, $p)) { $anomalies[] = ['tipe' => 'sql_injection', 'ip' => $ip, 'pattern' => $p, 'keterangan' => "SQL Injection: pattern '{$p}'"]; break; } }
            foreach ($xss as $p) { if (str_contains($combined, $p)) { $anomalies[] = ['tipe' => 'xss', 'ip' => $ip, 'pattern' => $p, 'keterangan' => "XSS: pattern '{$p}'"]; break; } }
            foreach ($cmd as $p) { if (str_contains($combined, $p)) { $anomalies[] = ['tipe' => 'command_injection', 'ip' => $ip, 'pattern' => $p, 'keterangan' => "Command Injection: pattern '{$p}'"]; break; } }
        }
        return array_values(array_unique($anomalies, SORT_REGULAR));
    }

    public function detectSuspiciousUA(array $entries): array
    {
        $scanners = ['sqlmap','nikto','nmap','masscan','zgrab','gobuster','dirbuster','hydra','nuclei'];
        $anomalies = [];
        $emptyUA = [];
        foreach ($entries as $e) {
            $ua = strtolower($e['user_agent'] ?? '');
            $ip = $e['ip'] ?? 'unknown';
            if (isset($e['user_agent']) && (empty($ua) || $ua === '-')) { $emptyUA[$ip] = ($emptyUA[$ip] ?? 0) + 1; continue; }
            foreach ($scanners as $s) { if (str_contains($ua, $s)) { $anomalies[] = ['tipe' => 'known_scanner', 'ip' => $ip, 'scanner' => $s, 'keterangan' => "Scanner {$s} dari {$ip}"]; break; } }
        }
        foreach ($emptyUA as $ip => $count) { if ($count > 5) { $anomalies[] = ['tipe' => 'empty_ua', 'ip' => $ip, 'jumlah' => $count, 'keterangan' => "IP {$ip}: {$count} request tanpa User-Agent"]; } }
        return $anomalies;
    }

    public function buildSummary(array $anomalies): string
    {
        $all = array_merge($anomalies['brute_force'] ?? [], $anomalies['scanning'] ?? [], $anomalies['injection'] ?? [], $anomalies['suspicious_ua'] ?? []);
        $types = array_unique(array_column($all, 'tipe'));
        $ips = array_unique(array_filter(array_column($all, 'ip')));
        $parts = [];
        if (!empty($types)) $parts[] = 'attack techniques: ' . implode(', ', $types);
        if (!empty($ips)) $parts[] = 'suspicious IPs: ' . implode(', ', array_slice($ips, 0, 10));
        return implode("\n", $parts) ?: 'general log analysis';
    }

    public function buildStats(array $entries, array $anomalies): array
    {
        $ipCounts = [];
        $pathCounts = [];
        $statusCounts = [];
        foreach ($entries as $e) {
            if ($e['ip'] ?? null) $ipCounts[$e['ip']] = ($ipCounts[$e['ip']] ?? 0) + 1;
            if ($e['path'] ?? null) $pathCounts[$e['path']] = ($pathCounts[$e['path']] ?? 0) + 1;
            if ($e['status_code'] ?? null) $statusCounts[$e['status_code']] = ($statusCounts[$e['status_code']] ?? 0) + 1;
        }
        arsort($ipCounts); arsort($pathCounts);
        $anomaliPerKat = [];
        foreach ($anomalies as $k => $v) $anomaliPerKat[$k] = count($v);
        return [
            'total_baris' => count($entries),
            'anomali_per_kategori' => $anomaliPerKat,
            'total_anomali' => array_sum($anomaliPerKat),
            'top_ips' => array_slice($ipCounts, 0, 5, true),
            'top_paths' => array_slice($pathCounts, 0, 5, true),
            'status_codes' => $statusCounts,
        ];
    }
}
