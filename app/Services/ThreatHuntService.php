<?php

namespace App\Services;

use App\Models\Scan;
use App\Models\AssetSubdomain;
use Illuminate\Support\Facades\Log;

/**
 * ThreatHuntService
 *
 * Mesin Threat Hunting berbasis CVE & NVD Intelligence.
 * Menghubungkan intelijen kerentanan NVD RAG dengan seluruh tech stack
 * dan aset digital yang terdaftar pada sistem untuk mendeteksi paparan ancaman.
 */
class ThreatHuntService
{
    protected NVDRAGService $nvdRag;

    /**
     * Database CVE Populer / Trending untuk Quick Hunt Preset.
     */
    public const TRENDING_CVES = [
        [
            'cve_id'      => 'CVE-2024-4577',
            'title'       => 'PHP CGI Argument Injection RCE',
            'severity'    => 'CRITICAL',
            'cvss'        => 9.8,
            'component'   => 'PHP (CGI/Windows/Apache)',
            'description' => 'Kerentanan bypass validasi karakter pada PHP-CGI memungkinkan remote attacker mengeksekusi kode perintah sistem (RCE).',
            'keywords'    => ['php', 'apache', 'cgi'],
            'ports'       => [80, 443, 8080],
        ],
        [
            'cve_id'      => 'CVE-2024-6387',
            'title'       => 'OpenSSH regreSSHion Pre-Auth RCE',
            'severity'    => 'HIGH',
            'cvss'        => 8.1,
            'component'   => 'OpenSSH (Linux/glibc)',
            'description' => 'Signal handler race condition pada OpenSSH server memungkinkan penyerang tanpa autentikasi memperoleh akses root pada sistem berbasis glibc.',
            'keywords'    => ['openssh', 'ssh', 'ubuntu', 'debian', 'linux'],
            'ports'       => [22, 2222],
        ],
        [
            'cve_id'      => 'CVE-2024-34351',
            'title'       => 'Next.js Server Actions SSRF',
            'severity'    => 'HIGH',
            'cvss'        => 7.5,
            'component'   => 'Next.js / Node.js',
            'description' => 'Server Actions pada Next.js rentan terhadap Server-Side Request Forgery saat memproses header Host pada redirect internal.',
            'keywords'    => ['next.js', 'nextjs', 'node.js', 'react'],
            'ports'       => [80, 443, 3000],
        ],
        [
            'cve_id'      => 'CVE-2023-29357',
            'title'       => 'Microsoft SharePoint Pre-Auth Admin Bypass',
            'severity'    => 'CRITICAL',
            'cvss'        => 9.8,
            'component'   => 'Microsoft SharePoint / IIS',
            'description' => 'Penyerang dapat memalsukan token JWT dan melakukan bypass autentikasi menjadi Administrator pada server Microsoft SharePoint.',
            'keywords'    => ['sharepoint', 'microsoft-iis', 'iis', 'asp.net'],
            'ports'       => [80, 443, 8080],
        ],
        [
            'cve_id'      => 'CVE-2021-41773',
            'title'       => 'Apache HTTP Server Path Traversal & RCE',
            'severity'    => 'CRITICAL',
            'cvss'        => 9.8,
            'component'   => 'Apache 2.4.49 / 2.4.50',
            'description' => 'Kelemahan normalisasi path URL memungkinkan penyerang membaca file di luar web root (path traversal) atau eksekusi CGI script.',
            'keywords'    => ['apache', 'httpd'],
            'ports'       => [80, 443],
        ],
        [
            'cve_id'      => 'CVE-2024-21892',
            'title'       => 'Node.js Permission Model Bypass & Code Execution',
            'severity'    => 'HIGH',
            'cvss'        => 7.8,
            'component'   => 'Node.js',
            'description' => 'Kelemahan Permission Model pada runtime Node.js memungkinkan penyerang mengeksekusi kode tak diotorisasi via manipulasi path.',
            'keywords'    => ['node.js', 'nodejs', 'express'],
            'ports'       => [80, 443, 3000, 5000],
        ],
    ];

    public function __construct(NVDRAGService $nvdRag)
    {
        $this->nvdRag = $nvdRag;
    }

    /**
     * Jalankan Threat Hunt mencari paparan CVE ke seluruh aset pengguna.
     *
     * @param string      $query   CVE ID (e.g. "CVE-2024-4577") atau kata kunci teknologi
     * @param int         $userId  ID pengguna
     * @param string|null $scanId  Scan ID spesifik (opsional)
     * @return array
     */
    public function hunt(string $query, int $userId, ?string $scanId = null): array
    {
        $startTime = microtime(true);
        $cleanQuery = trim($query);

        // 1. Ekstrak Intelijen CVE dari Preset atau NVD RAG
        $cveInfo = $this->resolveCveIntelligence($cleanQuery);

        // 2. Kumpulkan seluruh target & scan milik user
        $scanQuery = Scan::where('user_id', $userId)
            ->where('status', 'selesai')
            ->latest('selesai_at');

        if ($scanId) {
            $scanQuery->where('id', $scanId);
        }

        $scans = $scanQuery->with('subdomains')->get();

        $affectedAssets = [];
        $cleanAssets    = [];
        $totalTargets   = 0;

        foreach ($scans as $scan) {
            $totalTargets++;
            $evaluation = $this->evaluateAssetAgainstCve($scan, $cveInfo);

            if ($evaluation['is_affected']) {
                $affectedAssets[] = [
                    'scan_id'           => $scan->id,
                    'target'            => $scan->target ?? $scan->nama_file,
                    'scan_date'         => $scan->selesai_at?->format('d M Y H:i'),
                    'confidence'        => $evaluation['confidence'], // tinggi, sedang, rendah
                    'confidence_score'  => $evaluation['confidence_score'],
                    'matched_component' => $evaluation['matched_component'],
                    'evidence'          => $evaluation['evidence'],
                    'indicators'        => $evaluation['indicators'],
                    'subdomains_count'  => $scan->subdomains->count(),
                    'skor_keamanan'     => $scan->skor_keamanan,
                ];
            } else {
                $cleanAssets[] = [
                    'scan_id'   => $scan->id,
                    'target'    => $scan->target ?? $scan->nama_file,
                    'scan_date' => $scan->selesai_at?->format('d M Y H:i'),
                    'status'    => 'Tidak Terpengaruh',
                ];
            }
        }

        // Urutkan aset terpengaruh berdasarkan skor confidence tertinggi
        usort($affectedAssets, fn($a, $b) => $b['confidence_score'] <=> $a['confidence_score']);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'query'            => $cleanQuery,
            'cve_info'         => $cveInfo,
            'total_scanned'    => $totalTargets,
            'affected_count'   => count($affectedAssets),
            'clean_count'      => count($cleanAssets),
            'affected_assets'  => $affectedAssets,
            'clean_assets'     => $cleanAssets,
            'hunting_time_ms'  => $duration,
        ];
    }

    /**
     * Dapatkan detail intelijen CVE baik dari katalog trending lokal atau NVD Pinecone RAG.
     */
    protected function resolveCveIntelligence(string $query): array
    {
        $upperQuery = strtoupper($query);

        // Cek preset trending terlebih dahulu
        foreach (self::TRENDING_CVES as $preset) {
            if ($preset['cve_id'] === $upperQuery || str_contains(strtolower($preset['title']), strtolower($query))) {
                return $preset;
            }
        }

        // Query ke NVD RAG Service
        try {
            $ragResult = $this->nvdRag->searchCVE($query, 3);
            if (!empty($ragResult['chunks'])) {
                $topChunk = $ragResult['chunks'][0];
                $meta = $topChunk['metadata'] ?? [];

                $cveId = $meta['cve_id'] ?? (preg_match('/CVE-\d{4}-\d+/i', $query, $m) ? strtoupper($m[0]) : 'THREAT-INTEL');
                $keywords = $this->extractKeywordsFromText(($meta['description'] ?? '') . ' ' . $query);

                return [
                    'cve_id'      => $cveId,
                    'title'       => $meta['cve_id'] ? "{$meta['cve_id']} Vulnerability" : "Kerentanan Terkait {$query}",
                    'severity'    => $meta['severity'] ?? 'HIGH',
                    'cvss'        => (float) ($meta['cvss_v3_score'] ?? 7.5),
                    'component'   => $meta['component'] ?? 'Komponen Teridentifikasi',
                    'description' => $meta['description'] ?? "Analisis kerentanan berbasis NVD RAG untuk query: {$query}",
                    'keywords'    => $keywords,
                    'ports'       => $this->inferPortsFromKeywords($keywords),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning("[ThreatHunt] NVD RAG lookup warning: " . $e->getMessage());
        }

        // Fallback jika tidak ditemukan di NVD
        $keywords = $this->extractKeywordsFromText($query);
        return [
            'cve_id'      => preg_match('/CVE-\d{4}-\d+/i', $query, $m) ? strtoupper($m[0]) : 'KORELASI-KHUSUS',
            'title'       => "Pencarian Kerentanan: {$query}",
            'severity'    => 'MEDIUM',
            'cvss'        => 6.5,
            'component'   => implode(', ', $keywords) ?: 'Komponen Web',
            'description' => "Pencarian ancaman keamanan untuk mendeteksi keberadaan komponen {$query} pada seluruh aset.",
            'keywords'    => $keywords,
            'ports'       => $this->inferPortsFromKeywords($keywords),
        ];
    }

    /**
     * Evaluasi aset/scan tertentu terhadap kriteria CVE.
     */
    protected function evaluateAssetAgainstCve(Scan $scan, array $cveInfo): array
    {
        $techStack = $scan->tech_stack ?? [];
        $raw = $scan->data_mentah ?? [];
        $teknologi = $scan->teknologi_terdeteksi ?? [];

        $keywords = array_map('strtolower', $cveInfo['keywords'] ?? []);
        $targetPorts = $cveInfo['ports'] ?? [];

        $matchedIndicators = [];
        $score = 0;

        // 1. Cek Structured Tech Stack
        $server = strtolower($techStack['server'] ?? ($raw['server_info']['server'] ?? ''));
        $frameworks = array_map('strtolower', (array) ($techStack['framework'] ?? []));
        $languages  = array_map('strtolower', (array) ($techStack['language'] ?? []));
        $cms        = strtolower($techStack['cms'] ?? '');
        $cdn        = strtolower($techStack['cdn'] ?? '');

        // 2. Cek List Teknologi Umum
        $allTech = array_map('strtolower', (array) $teknologi);

        foreach ($keywords as $kw) {
            if (empty($kw) || strlen($kw) < 2) continue;

            // Match Server Header
            if (str_contains($server, $kw)) {
                $matchedIndicators[] = "Web Server mengekspos banner: '{$techStack['server']}' (mengandung {$kw})";
                $score += 35;
            }

            // Match Framework
            foreach ($frameworks as $fw) {
                if (str_contains($fw, $kw)) {
                    $matchedIndicators[] = "Framework aplikasi terdeteksi: '{$fw}'";
                    $score += 30;
                }
            }

            // Match Language / Runtime
            foreach ($languages as $lang) {
                if (str_contains($lang, $kw)) {
                    $matchedIndicators[] = "Bahasa pemrograman / runtime terdeteksi: '{$lang}'";
                    $score += 30;
                }
            }

            // Match CMS
            if (!empty($cms) && str_contains($cms, $kw)) {
                $matchedIndicators[] = "CMS terdeteksi: '{$techStack['cms']}'";
                $score += 25;
            }

            // Match List Teknologi
            foreach ($allTech as $t) {
                if (str_contains($t, $kw)) {
                    $matchedIndicators[] = "Teknologi terdeteksi pada HTTP response: '{$t}'";
                    $score += 20;
                }
            }
        }

        // 3. Cek Open Ports jika CVE relevan terhadap service tertentu (misal SSH port 22)
        $openPorts = array_column($raw['port_scan']['ports'] ?? [], 'port');
        if (!empty($targetPorts) && !empty($openPorts)) {
            $commonPorts = array_intersect($targetPorts, $openPorts);
            if (!empty($commonPorts)) {
                $matchedIndicators[] = "Port layanan terkait CVE terbuka pada target: " . implode(', ', $commonPorts);
                $score += 15;
            }
        }

        // 4. Cek Temuan Scan yang sudah ada (apakah CVE sudah pernah dicatat di temuan)
        $existingFinding = $scan->temuan()->where(function($q) use ($cveInfo, $keywords) {
            $q->where('cve_id', 'like', '%' . ($cveInfo['cve_id'] ?? '---') . '%')
              ->orWhere(function($subQ) use ($keywords) {
                  foreach ($keywords as $kw) {
                      $subQ->orWhere('judul', 'like', "%{$kw}%");
                  }
              });
        })->first();

        if ($existingFinding) {
            $matchedIndicators[] = "Temuan terkonfirmasi pada laporan scan: '{$existingFinding->judul}'";
            $score += 40;
        }

        $matchedIndicators = array_values(array_unique($matchedIndicators));
        $isAffected = !empty($matchedIndicators);

        $confidence = match(true) {
            $score >= 50 => 'Tinggi',
            $score >= 25 => 'Sedang',
            default      => 'Rendah',
        };

        return [
            'is_affected'       => $isAffected,
            'confidence'        => $confidence,
            'confidence_score'  => min($score, 100),
            'matched_component' => !empty($matchedIndicators) ? ($cveInfo['component'] ?? 'Komponen Target') : null,
            'evidence'          => implode('; ', $matchedIndicators),
            'indicators'        => $matchedIndicators,
        ];
    }

    /**
     * Ekstrak kata kunci relevan dari teks deskripsi.
     */
    protected function extractKeywordsFromText(string $text): array
    {
        $known = [
            'php', 'nginx', 'apache', 'openssh', 'ssh', 'laravel', 'wordpress',
            'sharepoint', 'next.js', 'node.js', 'react', 'vue', 'brotli', 'iis',
            'microsoft-iis', 'mysql', 'postgres', 'redis', 'docker', 'kubernetes',
            'grafana', 'kibana', 'cpanel', 'tomcat', 'spring', 'django', 'flask',
        ];

        $lower = strtolower($text);
        $found = [];

        foreach ($known as $k) {
            if (str_contains($lower, $k)) {
                $found[] = $k;
            }
        }

        return $found ?: array_slice(array_filter(explode(' ', preg_replace('/[^a-z0-9]/', ' ', $lower))), 0, 3);
    }

    /**
     * Simpulkan port standar berdasarkan kata kunci.
     */
    protected function inferPortsFromKeywords(array $keywords): array
    {
        $ports = [];
        foreach ($keywords as $kw) {
            if (in_array($kw, ['ssh', 'openssh'])) $ports[] = 22;
            if (in_array($kw, ['apache', 'nginx', 'iis', 'php', 'http'])) {
                $ports[] = 80;
                $ports[] = 443;
            }
            if (in_array($kw, ['mysql'])) $ports[] = 3306;
            if (in_array($kw, ['postgres'])) $ports[] = 5432;
            if (in_array($kw, ['redis'])) $ports[] = 6379;
        }
        return array_values(array_unique($ports));
    }
}
