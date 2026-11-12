<?php

namespace App\Services;

class DefacementDetectionService
{
    /**
     * Pola kata kunci Defacement Tradisional & Hacktivism
     */
    protected array $defacementKeywords = [
        'hacked by',
        'defaced by',
        'pwned by',
        'owned by',
        'security team',
        'cyber army',
        'indonesian hacker',
        'c99shell',
        'r57shell',
        'b374k',
        'alfa team',
        'we are anonymous',
        'greets to:',
        'touched by',
        'hacked and defaced',
        'root@security',
        '0day was here',
    ];

    /**
     * Pola kata kunci Judi Online / Slot Gacor (Indonesian SEO-Spam)
     */
    protected array $judolKeywords = [
        'slot gacor',
        'slot online',
        'situs judi',
        'judi online',
        'pragmatic play',
        'maxwin',
        'zeus slot',
        'mahjong ways',
        'deposit pulsa tanpa potongan',
        'deposit dana tanpa potongan',
        'rtp live',
        'rtp slot',
        'togel online',
        'bandar togel',
        'link alternatif slot',
        'agen judi online',
        'sbobet88',
        'poker online resmi',
        'kasino online',
        'bocoran admin jarwo',
        'slot maxwin hari ini',
        'scatter hitam',
        'slot anti rungkad',
    ];

    /**
     * Pola CSS hidden injection (teknik cloaking untuk menipu Google/SEO)
     */
    protected array $hiddenInjectionPatterns = [
        '#<[^>]+style\s*=\s*["\'][^"\']*(display\s*:\s*none|visibility\s*:\s*hidden|font-size\s*:\s*0|opacity\s*:\s*0|position\s*:\s*absolute;\s*left\s*:\s*-999)[^"\']*["\'][^>]*>(.*?)<\/[^>]+>#is',
    ];

    /**
     * Analisis konten HTML dari URL target untuk mendeteksi defacement atau injeksi SEO-spam judol.
     */
    public function detect(string $html, string $url = ''): array
    {
        if (empty($html)) {
            return [
                'is_defaced'          => false,
                'status'              => 'clean',
                'severity'            => 'info',
                'type'                => 'clean',
                'confidence'          => 'rendah',
                'detected_signatures' => [],
                'evidence_snippets'   => [],
                'hidden_spam_count'   => 0,
                'total_keywords'      => 0,
                'summary'             => 'Tidak ada konten HTML untuk dianalisis.',
            ];
        }

        $lowerHtml = strtolower($html);
        $detectedKeywords = [];
        $evidenceSnippets = [];

        // 1. Cek Classic Defacement
        $defacementFound = [];
        foreach ($this->defacementKeywords as $kw) {
            if (str_contains($lowerHtml, $kw)) {
                $defacementFound[] = $kw;
                $detectedKeywords[] = [
                    'kategori' => 'classic_defacement',
                    'kata'     => $kw,
                    'label'    => 'Hacktivism / Defacement Signature',
                ];
                $snippet = $this->extractSnippet($html, $kw);
                if ($snippet) {
                    $evidenceSnippets[] = $snippet;
                }
            }
        }

        // 2. Cek Judol / Slot SEO-Spam
        $judolFound = [];
        foreach ($this->judolKeywords as $kw) {
            if (str_contains($lowerHtml, $kw)) {
                $judolFound[] = $kw;
                $detectedKeywords[] = [
                    'kategori' => 'seo_spam_judol',
                    'kata'     => $kw,
                    'label'    => 'Injeksi Kata Kunci Judi Online / Slot',
                ];
                $snippet = $this->extractSnippet($html, $kw);
                if ($snippet) {
                    $evidenceSnippets[] = $snippet;
                }
            }
        }

        // 3. Cek Hidden CSS Spam Injections
        $hiddenMatches = 0;
        foreach ($this->hiddenInjectionPatterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches)) {
                foreach ($matches[2] as $hiddenText) {
                    $lowerHidden = strtolower($hiddenText);
                    foreach ($this->judolKeywords as $kw) {
                        if (str_contains($lowerHidden, $kw)) {
                            $hiddenMatches++;
                            $detectedKeywords[] = [
                                'kategori' => 'hidden_judol_injection',
                                'kata'     => $kw . ' (Cloaked/Hidden CSS)',
                                'label'    => 'Injeksi Backlink Tersembunyi (Cloaking)',
                            ];
                            $evidenceSnippets[] = 'Injeksi Elemen Tersembunyi: ' . substr(strip_tags($hiddenText), 0, 160);
                        }
                    }
                }
            }
        }

        $isDefaced = count($defacementFound) > 0 || count($judolFound) >= 2 || $hiddenMatches > 0;
        $type = 'clean';
        $severity = 'info';
        $confidence = 'rendah';
        $summary = 'Website bersih dari indikasi peretasan defacement maupun injeksi SEO-spam.';

        if (count($defacementFound) > 0) {
            $isDefaced = true;
            $type = 'classic_defacement';
            $severity = 'kritis';
            $confidence = count($defacementFound) >= 2 ? 'tinggi' : 'sedang';
            $summary = 'PERINGATAN KRITIS: Terdeteksi tanda peretasan web defacement tradisional (' . implode(', ', array_slice($defacementFound, 0, 3)) . '). Konten tampilan website kemungkinan telah dimodifikasi oleh peretas.';
        } elseif (count($judolFound) >= 2 || $hiddenMatches > 0) {
            $isDefaced = true;
            $type = $hiddenMatches > 0 ? 'hidden_backlinks' : 'seo_spam_judol';
            $severity = 'tinggi';
            $confidence = (count($judolFound) >= 3 || $hiddenMatches > 0) ? 'tinggi' : 'sedang';
            $summary = 'PERINGATAN KEAMANAN: Terdeteksi injeksi SEO-Spam / Judi Online (' . count($judolFound) . ' kata kunci terdeteksi' . ($hiddenMatches > 0 ? ", {$hiddenMatches} elemen cloaked" : '') . '). Situs disusupi konten perjudian ilegal.';
        }

        return [
            'is_defaced'          => $isDefaced,
            'status'              => $isDefaced ? 'vulnerable' : 'clean',
            'severity'            => $severity,
            'type'                => $type,
            'confidence'          => $confidence,
            'detected_signatures' => array_slice($detectedKeywords, 0, 10),
            'evidence_snippets'   => array_slice(array_unique(array_filter($evidenceSnippets)), 0, 5),
            'hidden_spam_count'   => $hiddenMatches,
            'total_keywords'      => count($defacementFound) + count($judolFound),
            'summary'             => $summary,
        ];
    }

    protected function extractSnippet(string $html, string $keyword): string
    {
        $pos = stripos($html, $keyword);
        if ($pos === false) return '';
        $start = max(0, $pos - 40);
        $length = min(strlen($html) - $start, 130);
        $snippet = substr($html, $start, $length);
        return '...' . strip_tags($snippet) . '...';
    }

    /**
     * Deteksi script injection baru dibanding baseline.
     * Mengembalikan array src script yang ada di $currentScripts tapi tidak di $baselineScripts.
     */
    public function detectScriptInjection(array $currentScripts, array $baselineScripts): array
    {
        $new = array_diff($currentScripts, $baselineScripts);

        // Filter: hanya asing (bukan same-origin atau CDN resmi dikenal)
        $trustedDomains = [
            'googleapis.com', 'gstatic.com', 'google.com', 'jquery.com',
            'cloudflare.com', 'bootstrapcdn.com', 'jsdelivr.net', 'unpkg.com',
            'cdnjs.cloudflare.com', 'ajax.googleapis.com',
        ];

        $suspicious = [];
        foreach ($new as $src) {
            $isTrusted = false;
            foreach ($trustedDomains as $trusted) {
                if (str_contains($src, $trusted)) {
                    $isTrusted = true;
                    break;
                }
            }
            if (!$isTrusted) {
                $suspicious[] = $src;
            }
        }

        return $suspicious;
    }

    /**
     * Deteksi iframe injection baru.
     * Mengembalikan array iframe src yang baru dan tidak ada di baseline.
     */
    public function detectIframeInjection(array $currentIframes, array $baselineIframes): array
    {
        return array_values(array_diff($currentIframes, $baselineIframes));
    }

    /**
     * Deteksi meta refresh mencurigakan dalam HTML.
     * Mengembalikan array ['found' => bool, 'url' => string|null, 'delay' => int].
     */
    public function detectMetaRefresh(string $html): array
    {
        // Cari <meta http-equiv="refresh" content="...">
        if (!preg_match(
            '#<meta\s[^>]*http-equiv=["\']refresh["\'][^>]*content=["\'](\d+)(?:;\s*url=([^"\']+))?["\'][^>]*>#i',
            $html,
            $m
        )) {
            // Coba urutan atribut terbalik
            if (!preg_match(
                '#<meta\s[^>]*content=["\'](\d+)(?:;\s*url=([^"\']+))?["\'][^>]*http-equiv=["\']refresh["\'][^>]*>#i',
                $html,
                $m
            )) {
                return ['found' => false, 'url' => null, 'delay' => 0];
            }
        }

        $delay    = (int) ($m[1] ?? 0);
        $url      = trim($m[2] ?? '');

        // Hanya curigai jika delay sangat kecil (< 3 detik) dan ada URL redirect
        $suspicious = $delay < 3 && !empty($url);

        return [
            'found'      => $suspicious,
            'url'        => $url ?: null,
            'delay'      => $delay,
        ];
    }
}
