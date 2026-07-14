<?php

namespace App\Services;

class InformationControlService
{
    public function filter(array $hasil): array
    {
        // Proses semua string dalam array secara rekursif
        $hasil = $this->filterRecursive($hasil);

        // Disclaimer wajib
        $hasil['disclaimer'] = 'Penilaian ini dihasilkan AI berbasis knowledge base keamanan publik (NVD, MITRE ATT&CK, OWASP, CWE, CISA KEV). Tidak menggantikan penetration testing profesional. Gunakan sebagai panduan awal, bukan keputusan final.';

        return $hasil;
    }

    protected function filterRecursive(mixed $data): mixed
    {
        if (is_string($data)) {
            return $this->filterString($data);
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->filterRecursive($value);
            }
        }

        return $data;
    }

    protected function filterString(string $text): string
    {
        // ATURAN 1: Sensor credential
        $text = preg_replace('/password\s*[=:]\s*[\'\"]\S{6,}[\'\"]/i', '[KREDENSIAL DISEMBUNYIKAN — WAJIB dirotasi]', $text);
        $text = preg_replace('/api[_\-]?key\s*[=:]\s*[\'\"]\S{20,}[\'\"]/i', '[KREDENSIAL DISEMBUNYIKAN — WAJIB dirotasi]', $text);
        $text = preg_replace('/secret\s*[=:]\s*[\'\"]\S{10,}[\'\"]/i', '[KREDENSIAL DISEMBUNYIKAN — WAJIB dirotasi]', $text);
        $text = preg_replace('/:\/\/\w+:\w+@[\w.]+/', '[KREDENSIAL DISEMBUNYIKAN — WAJIB dirotasi]', $text);

        // ATURAN 2: Sensor IP internal
        $text = preg_replace('/\b(192\.168\.|10\.\d+\.|172\.(1[6-9]|2\d|3[01])\.)\d+\.\d+\b/', '[IP INTERNAL]', $text);

        // ATURAN 3: Batasi detail eksploitasi
        $text = preg_replace('/(shellcode|metasploit payload|reverse shell payload)/i', '[Detail teknis dihapus]', $text);

        return $text;
    }
}
