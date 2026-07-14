<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LogParserService
{
    /**
     * Deteksi format log secara otomatis berdasarkan pattern.
     */
    public function detectFormat(string $content): string
    {
        $lines = array_filter(array_slice(explode("\n", $content), 0, 20));

        foreach ($lines as $line) {
            // Apache/Nginx access log
            if (preg_match('/^\d+\.\d+\.\d+\.\d+ .+ \[.+\] ".+" \d+ \d+/', $line)) {
                return 'apache';
            }
            // Laravel log
            if (preg_match('/^\[\d{4}-\d{2}-\d{2}.*\] \w+\.\w+:/', $line)) {
                return 'laravel';
            }
            // Auth.log / syslog
            if (preg_match('/\w+\s+\d+ \d+:\d+:\d+ .+ (sshd|sudo|login|systemd)\[/', $line)) {
                return 'authlog';
            }
            // Nginx error log
            if (preg_match('/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2} \[/', $line)) {
                return 'nginx_error';
            }
            // Windows Event Log (text format)
            if (preg_match('/^(Information|Warning|Error|Critical)\s+\d{1,2}\/\d{1,2}\/\d{4}/', $line)) {
                return 'windows_event';
            }
            // Syslog
            if (preg_match('/^<\d+>/', $line) || preg_match('/^\w{3}\s+\d+\s+\d{2}:\d{2}:\d{2}/', $line)) {
                return 'syslog';
            }
        }

        return 'custom';
    }

    /**
     * Parse log content berdasarkan format.
     */
    public function parse(string $content, string $format): array
    {
        $lines = array_filter(explode("\n", $content));
        $entries = [];

        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $entry = match ($format) {
                'apache', 'nginx' => $this->parseAccessLog($line),
                'laravel' => $this->parseLaravelLog($line),
                'authlog' => $this->parseAuthLog($line),
                'nginx_error' => $this->parseNginxError($line),
                'syslog' => $this->parseSyslog($line),
                'windows_event' => $this->parseWindowsEvent($line),
                default => $this->parseGeneric($line),
            };

            if ($entry) {
                $entry['line_number'] = $i + 1;
                $entry['raw'] = substr($line, 0, 500);
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    protected function parseAccessLog(string $line): ?array
    {
        // Format: IP - user [timestamp] "METHOD path HTTP/x.x" status size "referer" "UA"
        $pattern = '/^(\S+) \S+ \S+ \[([^\]]+)\] "(\S+) (\S+) \S+" (\d+) (\S+)(?:\s+"([^"]*)")?(?:\s+"([^"]*)")?/';

        if (!preg_match($pattern, $line, $m)) {
            return null;
        }

        return [
            'timestamp' => $m[2] ?? null,
            'ip' => $m[1] ?? null,
            'method' => $m[3] ?? null,
            'path' => $m[4] ?? null,
            'status_code' => (int) ($m[5] ?? 0),
            'size' => $m[6] ?? null,
            'referer' => $m[7] ?? null,
            'user_agent' => $m[8] ?? null,
            'message' => null,
        ];
    }

    protected function parseLaravelLog(string $line): ?array
    {
        // Format: [2024-01-15 10:30:45] local.ERROR: Message
        $pattern = '/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}[^\]]*)\] (\w+)\.(\w+): (.+)/';

        if (!preg_match($pattern, $line, $m)) {
            return ['timestamp' => null, 'ip' => null, 'method' => null, 'path' => null, 'status_code' => null, 'user_agent' => null, 'message' => $line];
        }

        return [
            'timestamp' => $m[1] ?? null,
            'ip' => null,
            'method' => null,
            'path' => null,
            'status_code' => null,
            'user_agent' => null,
            'message' => $m[4] ?? null,
            'level' => $m[3] ?? null,
            'channel' => $m[2] ?? null,
        ];
    }

    protected function parseAuthLog(string $line): ?array
    {
        // Format: Jan 15 10:30:45 hostname sshd[1234]: message
        $pattern = '/^(\w+ +\d+ \d+:\d+:\d+) (\S+) (\S+)\[(\d+)\]: (.+)/';

        if (!preg_match($pattern, $line, $m)) {
            return null;
        }

        // Coba ekstrak IP dari pesan
        $ip = null;
        if (preg_match('/(?:from|for) (\d+\.\d+\.\d+\.\d+)/', $m[5], $ipMatch)) {
            $ip = $ipMatch[1];
        }

        return [
            'timestamp' => $m[1] ?? null,
            'ip' => $ip,
            'method' => null,
            'path' => null,
            'status_code' => null,
            'user_agent' => null,
            'message' => $m[5] ?? null,
            'hostname' => $m[2] ?? null,
            'service' => $m[3] ?? null,
        ];
    }

    protected function parseNginxError(string $line): ?array
    {
        $pattern = '/^(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}) \[(\w+)\] .+?: (.+)/';

        if (!preg_match($pattern, $line, $m)) {
            return null;
        }

        $ip = null;
        if (preg_match('/client: (\d+\.\d+\.\d+\.\d+)/', $m[3], $ipMatch)) {
            $ip = $ipMatch[1];
        }

        return [
            'timestamp' => $m[1] ?? null,
            'ip' => $ip,
            'method' => null,
            'path' => null,
            'status_code' => null,
            'user_agent' => null,
            'message' => $m[3] ?? null,
            'level' => $m[2] ?? null,
        ];
    }

    protected function parseSyslog(string $line): ?array
    {
        $pattern = '/^(\w{3}\s+\d+\s+\d{2}:\d{2}:\d{2}) (\S+) (.+)/';
        if (!preg_match($pattern, $line, $m)) {
            return ['timestamp' => null, 'ip' => null, 'method' => null, 'path' => null, 'status_code' => null, 'user_agent' => null, 'message' => $line];
        }

        return [
            'timestamp' => $m[1] ?? null,
            'ip' => null,
            'method' => null,
            'path' => null,
            'status_code' => null,
            'user_agent' => null,
            'message' => $m[3] ?? null,
            'hostname' => $m[2] ?? null,
        ];
    }

    protected function parseWindowsEvent(string $line): ?array
    {
        return [
            'timestamp' => null,
            'ip' => null,
            'method' => null,
            'path' => null,
            'status_code' => null,
            'user_agent' => null,
            'message' => $line,
        ];
    }

    protected function parseGeneric(string $line): ?array
    {
        // Coba ekstrak timestamp
        $timestamp = null;
        if (preg_match('/\d{4}[-\/]\d{2}[-\/]\d{2}[T ]\d{2}:\d{2}:\d{2}/', $line, $m)) {
            $timestamp = $m[0];
        }

        // Coba ekstrak IP
        $ip = null;
        if (preg_match('/\b(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\b/', $line, $m)) {
            $ip = $m[1];
        }

        return [
            'timestamp' => $timestamp,
            'ip' => $ip,
            'method' => null,
            'path' => null,
            'status_code' => null,
            'user_agent' => null,
            'message' => $line,
        ];
    }
}
