<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebsiteIntegrityService
{
    /**
     * Fetch URL dan buat snapshot elemen penting.
     * Untuk target dinamis (is_dynamic = true), hanya ekstrak elemen statis.
     */
    public function fetchSnapshot(string $url, bool $isDynamic = false): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'RedSim-IntegrityBot/1.0 (Security Monitoring)',
                'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->timeout(20)->get($url);

            if (!$response->successful()) {
                return ['error' => "HTTP {$response->status()} saat mengakses URL."];
            }

            $html = $response->body();
            if (empty($html)) {
                return ['error' => 'Respons HTML kosong.'];
            }

            return $this->extractElements($html, $url, $isDynamic);
        } catch (\Throwable $e) {
            Log::warning("WebsiteIntegrityService::fetchSnapshot gagal [{$url}]: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Ekstrak elemen kunci dari HTML.
     */
    public function extractElements(string $html, string $url, bool $isDynamic = false): array
    {
        $lowerHtml = strtolower($html);

        // --- Title ---
        $title = '';
        if (preg_match('#<title[^>]*>(.*?)</title>#is', $html, $m)) {
            $title = trim(strip_tags($m[1]));
        }

        // --- H1 texts ---
        $h1Texts = [];
        if (preg_match_all('#<h1[^>]*>(.*?)</h1>#is', $html, $m)) {
            foreach ($m[1] as $h) {
                $text = trim(strip_tags($h));
                if ($text) $h1Texts[] = $text;
            }
        }

        // --- Canonical URL ---
        $canonical = '';
        if (preg_match('#<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']+)["\']#i', $html, $m)) {
            $canonical = trim($m[1]);
        }

        // --- Meta description ---
        $metaDesc = '';
        if (preg_match('#<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\']#i', $html, $m)) {
            $metaDesc = trim($m[1]);
        }

        // --- External scripts ---
        $externalScripts = [];
        $baseHost = parse_url($url, PHP_URL_HOST) ?? '';
        if (preg_match_all('#<script[^>]+src=["\']([^"\']+)["\'][^>]*>#i', $html, $m)) {
            foreach ($m[1] as $src) {
                $srcHost = parse_url($src, PHP_URL_HOST) ?? '';
                if ($srcHost && $srcHost !== $baseHost) {
                    $externalScripts[] = [
                        'src'  => $src,
                        'host' => $srcHost,
                        'hash' => hash('sha256', $src),
                    ];
                }
            }
        }

        // --- Inline scripts hash ---
        $inlineScriptsHash = [];
        if (preg_match_all('#<script(?![^>]+src=)[^>]*>(.*?)</script>#is', $html, $m)) {
            foreach ($m[1] as $inlineCode) {
                $code = trim($inlineCode);
                if (strlen($code) > 20) { // Abaikan script sangat pendek
                    $inlineScriptsHash[] = hash('sha256', $code);
                }
            }
        }

        // --- External iframes ---
        $externalIframes = [];
        if (preg_match_all('#<iframe[^>]+src=["\']([^"\']+)["\'][^>]*>#i', $html, $m)) {
            foreach ($m[1] as $src) {
                $srcHost = parse_url($src, PHP_URL_HOST) ?? '';
                if ($srcHost && $srcHost !== $baseHost) {
                    $externalIframes[] = [
                        'src'  => $src,
                        'host' => $srcHost,
                    ];
                }
            }
        }

        // --- External links hash (hanya untuk konten non-dinamis) ---
        $linkHash = null;
        if (!$isDynamic) {
            $externalLinks = [];
            if (preg_match_all('#<a[^>]+href=["\']([^"\']+)["\'][^>]*>#i', $html, $m)) {
                foreach ($m[1] as $href) {
                    $hrefHost = parse_url($href, PHP_URL_HOST) ?? '';
                    if ($hrefHost && $hrefHost !== $baseHost) {
                        $externalLinks[] = $hrefHost; // Hanya host, abaikan query params
                    }
                }
                sort($externalLinks);
                $linkHash = hash('sha256', implode('|', array_unique($externalLinks)));
            }
        }

        // --- Meta refresh (tanda redirect mencurigakan) ---
        $hasMetaRefresh = (bool) preg_match('#<meta[^>]+http-equiv=["\']refresh["\']#i', $html);

        // --- Content hash (full body, hanya non-dinamis) ---
        $contentHash = null;
        if (!$isDynamic) {
            // Hapus whitespace berlebih sebelum hash untuk mengurangi false positive kosmetik
            $normalized = preg_replace('/\s+/', ' ', strip_tags($html));
            $contentHash = hash('sha256', $normalized ?? '');
        }

        return [
            'content_hash'        => $contentHash,
            'title'               => $title,
            'h1_texts'            => $h1Texts,
            'canonical_url'       => $canonical,
            'meta_description'    => $metaDesc,
            'link_hash'           => $linkHash,
            'external_scripts'    => $externalScripts,
            'external_iframes'    => $externalIframes,
            'inline_scripts_hash' => $inlineScriptsHash,
            'has_meta_refresh'    => $hasMetaRefresh,
            'html_length'         => strlen($html),
            'raw_html'            => $html, // Untuk defacement keyword detection
        ];
    }

    /**
     * Bandingkan snapshot terkini dengan baseline.
     * Kembalikan diff score (0-100) dan detail perubahan.
     */
    public function compareWithBaseline(array $current, array $baseline, bool $isDynamic = false): array
    {
        $changes    = [];
        $diffPoints = 0;
        $maxPoints  = 0;

        // 1. Cek title (bobot tinggi, 20 poin)
        $maxPoints += 20;
        if ($current['title'] !== ($baseline['title'] ?? '')) {
            $changes[] = [
                'type'      => 'title_changed',
                'severity'  => 'high',
                'label'     => 'Judul Halaman Berubah',
                'before'    => $baseline['title'] ?? '(kosong)',
                'after'     => $current['title'],
            ];
            $diffPoints += 20;
        }

        // 2. Cek H1 (15 poin)
        $maxPoints += 15;
        $baseH1 = $baseline['h1_texts'] ?? [];
        $currH1 = $current['h1_texts'] ?? [];
        if ($baseH1 !== $currH1) {
            $newH1     = array_diff($currH1, $baseH1);
            $removedH1 = array_diff($baseH1, $currH1);
            if ($newH1 || $removedH1) {
                $changes[] = [
                    'type'     => 'h1_changed',
                    'severity' => 'warning',
                    'label'    => 'Heading Utama (H1) Berubah',
                    'added'    => array_values($newH1),
                    'removed'  => array_values($removedH1),
                ];
                $diffPoints += 15;
            }
        }

        // 3. Cek script eksternal baru (bobot tertinggi, 30 poin)
        $maxPoints += 30;
        $baseScripts = collect($baseline['external_scripts'] ?? [])->pluck('src')->toArray();
        $currScripts = collect($current['external_scripts'] ?? [])->pluck('src')->toArray();
        $newScripts  = array_diff($currScripts, $baseScripts);
        if ($newScripts) {
            $changes[] = [
                'type'     => 'script_inject',
                'severity' => 'critical',
                'label'    => 'Script Eksternal Baru Terdeteksi',
                'srcs'     => array_values($newScripts),
            ];
            $diffPoints += min(30, count($newScripts) * 15);
        }

        // 4. Cek iframe eksternal baru (25 poin)
        $maxPoints += 25;
        $baseIframes = collect($baseline['external_iframes'] ?? [])->pluck('src')->toArray();
        $currIframes = collect($current['external_iframes'] ?? [])->pluck('src')->toArray();
        $newIframes  = array_diff($currIframes, $baseIframes);
        if ($newIframes) {
            $changes[] = [
                'type'     => 'iframe_inject',
                'severity' => 'critical',
                'label'    => 'Iframe Asing Baru Terdeteksi',
                'srcs'     => array_values($newIframes),
            ];
            $diffPoints += min(25, count($newIframes) * 15);
        }

        // 5. Cek meta refresh baru (15 poin)
        $maxPoints += 15;
        if ($current['has_meta_refresh'] && !($baseline['has_meta_refresh'] ?? false)) {
            $changes[] = [
                'type'     => 'meta_refresh',
                'severity' => 'high',
                'label'    => 'Meta Refresh Baru Terdeteksi (Potensi Redirect Paksa)',
            ];
            $diffPoints += 15;
        }

        // 6. Cek content hash (hanya non-dinamis, 10 poin)
        if (!$isDynamic) {
            $maxPoints += 10;
            if ($current['content_hash'] && $baseline['content_hash'] &&
                $current['content_hash'] !== $baseline['content_hash']) {
                // Hitung estimasi % perubahan berdasarkan panjang HTML
                $lengthDiff = abs(($current['html_length'] ?? 0) - ($baseline['html_length'] ?? 0));
                $lengthPct  = $baseline['html_length'] > 0
                    ? min(100, round($lengthDiff / $baseline['html_length'] * 100))
                    : 0;

                if ($lengthPct > 5) { // Abaikan perubahan < 5%
                    $changes[] = [
                        'type'          => 'content_change',
                        'severity'      => $lengthPct > 30 ? 'high' : 'warning',
                        'label'         => "Konten Halaman Berubah (~{$lengthPct}%)",
                        'length_before' => $baseline['html_length'] ?? 0,
                        'length_after'  => $current['html_length'] ?? 0,
                        'change_pct'    => $lengthPct,
                    ];
                    $diffPoints += min(10, (int) ($lengthPct / 10));
                }
            }
        }

        // Hitung diff score 0-100
        $diffScore = $maxPoints > 0 ? min(100, (int) round(($diffPoints / $maxPoints) * 100)) : 0;

        // Tentukan tipe alert dominan
        $alertType = $this->determineAlertType($changes);

        // Tentukan severity tertinggi
        $severity = $this->determineSeverity($changes, $diffScore);

        return [
            'has_changes' => count($changes) > 0,
            'diff_score'  => $diffScore,
            'alert_type'  => $alertType,
            'severity'    => $severity,
            'changes'     => $changes,
        ];
    }

    /**
     * Tentukan tipe alert paling dominan dari daftar perubahan.
     */
    protected function determineAlertType(array $changes): string
    {
        $priority = ['defacement', 'script_inject', 'iframe_inject', 'meta_refresh',
                     'redirect_change', 'title_changed', 'h1_changed', 'content_change'];

        $types = array_column($changes, 'type');

        foreach ($priority as $p) {
            if (in_array($p, $types)) {
                return match ($p) {
                    'title_changed', 'h1_changed', 'content_change' => 'content_change',
                    'meta_refresh'                                    => 'redirect_change',
                    default                                           => $p,
                };
            }
        }

        return 'content_change';
    }

    /**
     * Tentukan severity tertinggi.
     */
    protected function determineSeverity(array $changes, int $diffScore): string
    {
        $severities = array_column($changes, 'severity');

        if (in_array('critical', $severities) || $diffScore >= 70) return 'critical';
        if (in_array('high', $severities)     || $diffScore >= 40) return 'high';
        if (in_array('warning', $severities)  || $diffScore >= 15) return 'warning';
        return 'info';
    }

    /**
     * Download dan simpan screenshot ke storage lokal.
     * Menggunakan VisualEvidenceService yang sudah ada.
     */
    public function captureScreenshot(string $url, string $folder = 'monitoring'): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'RedSim-IntegrityBot/1.0',
            ])->timeout(15)->get(
                "https://image.thum.io/get/width/1280/crop/720/noanimate/" . $url
            );

            if ($response->successful() && strlen($response->body()) > 2048) {
                $filename = "{$folder}/" . md5($url) . '_' . time() . '.jpg';
                Storage::disk('public')->put($filename, $response->body());
                return $filename;
            }
        } catch (\Throwable $e) {
            Log::info("WebsiteIntegrityService::captureScreenshot gagal: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Deteksi apakah halaman kemungkinan berisi konten dinamis tinggi.
     */
    public function looksLikeDynamic(string $html): bool
    {
        $indicators = [
            'last updated', 'last modified', 'posted on', 'published',
            'breaking news', 'live updates', 'just now', 'minutes ago',
            'tanggal artikel', 'diperbarui', 'diterbitkan',
        ];
        $lower = strtolower($html);
        $count = 0;
        foreach ($indicators as $ind) {
            if (str_contains($lower, $ind)) $count++;
        }
        return $count >= 2;
    }
}
