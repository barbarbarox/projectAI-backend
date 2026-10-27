<?php

namespace App\Services;

/**
 * TechStackFingerprinter
 *
 * Menganalisis data mentah hasil scan dan menghasilkan tech stack terstruktur
 * berformat JSON yang bisa ditampilkan sebagai badge di UI dan digunakan
 * oleh ThreatHunt untuk CVE matching.
 *
 * Output format:
 * [
 *   'cdn'         => 'Cloudflare',
 *   'waf'         => 'Cloudflare WAF',
 *   'server'      => 'nginx/1.18.0',
 *   'language'    => ['PHP/8.1'],
 *   'framework'   => ['Laravel'],
 *   'cms'         => null,
 *   'analytics'   => ['Google Analytics'],
 *   'cloud'       => 'DigitalOcean',
 *   'os'          => 'Ubuntu',
 *   'database'    => null,
 *   'js_libs'     => ['jQuery', 'React'],
 *   'cdn_assets'  => ['jsDelivr', 'Cloudflare CDN'],
 *   'security'    => ['HSTS', 'CSP'],
 *   'tls_version' => 'TLS 1.3',
 *   'country'     => 'SG',
 *   'asn'         => 'AS13335 Cloudflare',
 *   'screenshot'  => 'https://...',
 * ]
 */
class TechStackFingerprinter
{
    // =========================================================================
    // SIGNATURE MAPS
    // =========================================================================

    private array $cdnSignatures = [
        'cloudflare'        => 'Cloudflare',
        'cf-ray'            => 'Cloudflare',
        'cloudfront'        => 'AWS CloudFront',
        'x-amz-cf-id'       => 'AWS CloudFront',
        'fastly'            => 'Fastly',
        'akamai'            => 'Akamai',
        'x-akamai'          => 'Akamai',
        'bunny'             => 'BunnyCDN',
        'x-bunnycdn'        => 'BunnyCDN',
        'varnish'           => 'Varnish Cache',
        'x-varnish'         => 'Varnish Cache',
        'sucuri'            => 'Sucuri',
        'x-sucuri-id'       => 'Sucuri',
        'imperva'           => 'Imperva',
        'incap_ses'         => 'Imperva Incapsula',
        'x-cdn'             => 'CDN',
        'x-cache'           => 'Cache Layer',
    ];

    private array $serverSignatures = [
        'nginx'        => 'nginx',
        'apache'       => 'Apache',
        'microsoft-iis'=> 'Microsoft IIS',
        'iis'          => 'Microsoft IIS',
        'litespeed'    => 'LiteSpeed',
        'openresty'    => 'OpenResty',
        'caddy'        => 'Caddy',
        'gunicorn'     => 'Gunicorn',
        'uvicorn'      => 'Uvicorn',
        'tornado'      => 'Tornado',
    ];

    private array $languageSignatures = [
        // Header-based
        'php'       => 'PHP',
        'python'    => 'Python',
        'ruby'      => 'Ruby',
        'java'      => 'Java',
        'node'      => 'Node.js',
        'nodejs'    => 'Node.js',
        '.net'      => 'ASP.NET',
        'asp.net'   => 'ASP.NET',
        'go'        => 'Go',
        'perl'      => 'Perl',
        // Cookie-based
        'phpsessid'     => 'PHP',
        'jsessionid'    => 'Java',
        'asp.net_sessionid' => 'ASP.NET',
        'rack.session'  => 'Ruby',
        'laravel_session' => 'PHP/Laravel',
    ];

    private array $frameworkSignatures = [
        'laravel'     => 'Laravel',
        'symfony'     => 'Symfony',
        'codeigniter' => 'CodeIgniter',
        'yii'         => 'Yii',
        'django'      => 'Django',
        'flask'       => 'Flask',
        'fastapi'     => 'FastAPI',
        'rails'       => 'Ruby on Rails',
        'express'     => 'Express.js',
        'next'        => 'Next.js',
        'nuxt'        => 'Nuxt.js',
        'gatsby'      => 'Gatsby',
        'spring'      => 'Spring Boot',
        'strapi'      => 'Strapi',
    ];

    private array $cmsSignatures = [
        'wordpress'  => 'WordPress',
        'wp-content' => 'WordPress',
        'wp-json'    => 'WordPress',
        'joomla'     => 'Joomla',
        'drupal'     => 'Drupal',
        'typo3'      => 'TYPO3',
        'magento'    => 'Magento',
        'shopify'    => 'Shopify',
        'wix'        => 'Wix',
        'squarespace'=> 'Squarespace',
        'ghost'      => 'Ghost',
        'strapi'     => 'Strapi (Headless)',
        'contentful' => 'Contentful',
    ];

    private array $analyticsSignatures = [
        'google-analytics'  => 'Google Analytics',
        'googletagmanager'  => 'Google Tag Manager',
        'gtag'              => 'Google Analytics 4',
        'hotjar'            => 'Hotjar',
        'mixpanel'          => 'Mixpanel',
        'segment'           => 'Segment',
        'amplitude'         => 'Amplitude',
        'matomo'            => 'Matomo',
        'heap'              => 'Heap Analytics',
        'intercom'          => 'Intercom',
        'crisp'             => 'Crisp Chat',
        'freshchat'         => 'Freshchat',
        'clarity'           => 'Microsoft Clarity',
        'sentry'            => 'Sentry',
        'datadog'           => 'Datadog RUM',
        'newrelic'          => 'New Relic',
    ];

    private array $jsLibSignatures = [
        'jquery'        => 'jQuery',
        'react'         => 'React',
        'vue'           => 'Vue.js',
        'angular'       => 'Angular',
        'svelte'        => 'Svelte',
        'alpine'        => 'Alpine.js',
        'bootstrap'     => 'Bootstrap',
        'tailwind'      => 'Tailwind CSS',
        'lodash'        => 'Lodash',
        'axios'         => 'Axios',
        'socket.io'     => 'Socket.IO',
        'three'         => 'Three.js',
        'chart'         => 'Chart.js',
        'moment'        => 'Moment.js',
        'dayjs'         => 'Day.js',
        'framer'        => 'Framer Motion',
    ];

    private array $cloudSignatures = [
        'amazon'     => 'AWS',
        'amazonaws'  => 'AWS',
        'google'     => 'Google Cloud',
        'googleapi'  => 'Google Cloud',
        'azure'      => 'Microsoft Azure',
        'digitalocean' => 'DigitalOcean',
        'linode'     => 'Akamai Cloud (Linode)',
        'vultr'      => 'Vultr',
        'hetzner'    => 'Hetzner',
        'ovh'        => 'OVHcloud',
        'netlify'    => 'Netlify',
        'vercel'     => 'Vercel',
        'render'     => 'Render',
        'heroku'     => 'Heroku',
        'fly.io'     => 'Fly.io',
    ];

    private array $osSignatures = [
        'ubuntu'    => 'Ubuntu',
        'debian'    => 'Debian',
        'centos'    => 'CentOS',
        'rhel'      => 'RHEL',
        'fedora'    => 'Fedora',
        'windows'   => 'Windows Server',
        'win'       => 'Windows Server',
        'freebsd'   => 'FreeBSD',
        'unix'      => 'Unix',
    ];

    // =========================================================================
    // MAIN METHOD
    // =========================================================================

    /**
     * Build structured tech stack from raw scan data.
     *
     * @param array $headerData  Output dari ScanIntensService::analyzeHeaders()
     * @param array $dnsData     Output dari ScanIntensService::analyzeDNS()
     * @param array $urlscanData Raw URLScan API response
     * @param array $serverInfo  Output dari buildReport() 'server_info'
     * @param array $sslData     Output dari ScanIntensService::analyzeSSL()
     * @param array $cookieData  Output dari ScanIntensService::analyzeCookies()
     */
    public function fingerprint(
        array $headerData = [],
        array $dnsData = [],
        array $urlscanData = [],
        array $serverInfo = [],
        array $sslData = [],
        array $cookieData = []
    ): array {
        $allHeaders = array_change_key_case($headerData['semua_headers'] ?? [], CASE_LOWER);
        $exposedHeaders = $headerData['headers_sensitif'] ?? [];

        $result = [
            'cdn'        => null,
            'waf'        => null,
            'server'     => null,
            'language'   => [],
            'framework'  => [],
            'cms'        => null,
            'analytics'  => [],
            'js_libs'    => [],
            'cloud'      => null,
            'os'         => null,
            'database'   => null,
            'tls_version'=> null,
            'country'    => null,
            'asn'        => null,
            'screenshot' => null,
        ];

        // --- CDN / WAF detection from DNS + headers ---
        foreach ($dnsData['cdn_waf'] ?? [] as $cdnName) {
            $lower = strtolower($cdnName);
            foreach ($this->cdnSignatures as $sig => $label) {
                if (str_contains($lower, $sig)) {
                    if (!$result['cdn']) $result['cdn'] = $label;
                    break;
                }
            }
        }
        // Also check response headers for CDN markers
        foreach (array_keys($allHeaders) as $headerName) {
            foreach ($this->cdnSignatures as $sig => $label) {
                if (str_contains($headerName, $sig) && !$result['cdn']) {
                    $result['cdn'] = $label;
                }
            }
        }

        // --- Server detection ---
        $serverStr = strtolower($serverInfo['server'] ?? $allHeaders['server'] ?? '');
        if ($serverStr) {
            foreach ($this->serverSignatures as $sig => $label) {
                if (str_contains($serverStr, $sig)) {
                    // Keep version if available (e.g. "nginx/1.18.0")
                    preg_match('/(' . preg_quote($sig, '/') . '[\/\s]?[\d.]+)/i', $serverStr, $m);
                    $result['server'] = $m[1] ?? $label;
                    break;
                }
            }
        }

        // --- Language from X-Powered-By header ---
        $poweredBy = strtolower($allHeaders['x-powered-by'] ?? '');
        if ($poweredBy) {
            foreach ($this->languageSignatures as $sig => $label) {
                if (str_contains($poweredBy, $sig) && !in_array($label, $result['language'])) {
                    $result['language'][] = $label;
                }
            }
        }

        // --- Language from cookies ---
        $cookieNames = array_map('strtolower', array_column($cookieData['cookies'] ?? [], 'name'));
        $setCookieHeader = strtolower($allHeaders['set-cookie'] ?? '');
        $allCookieStr = implode(' ', $cookieNames) . ' ' . $setCookieHeader;
        foreach ($this->languageSignatures as $sig => $label) {
            if (str_contains($allCookieStr, $sig) && !in_array($label, $result['language'])) {
                $result['language'][] = $label;
            }
        }

        // --- Framework from URLScan processors / headers / cookies ---
        $urlscanApps = [];
        foreach ($urlscanData['meta']['processors'] ?? [] as $proc) {
            foreach ($proc['data'] ?? [] as $item) {
                if (!empty($item['app'])) $urlscanApps[] = strtolower($item['app']);
            }
        }
        $allTechStr = implode(' ', $urlscanApps) . ' ' . $allCookieStr . ' ' . $poweredBy;
        foreach ($this->frameworkSignatures as $sig => $label) {
            if (str_contains($allTechStr, $sig) && !in_array($label, $result['framework'])) {
                $result['framework'][] = $label;
            }
        }

        // --- CMS ---
        $urlPath = strtolower($urlscanData['page']['url'] ?? '');
        $allContentStr = $allTechStr . ' ' . $urlPath;
        foreach ($this->cmsSignatures as $sig => $label) {
            if (str_contains($allContentStr, $sig)) {
                $result['cms'] = $label;
                break;
            }
        }

        // --- JS Libraries from URLScan resource URLs ---
        $resourceUrls = '';
        foreach (array_slice($urlscanData['data']['requests'] ?? [], 0, 100) as $req) {
            $resourceUrls .= strtolower($req['request']['url'] ?? '') . ' ';
        }
        foreach ($this->jsLibSignatures as $sig => $label) {
            if (str_contains($resourceUrls, $sig) && !in_array($label, $result['js_libs'])) {
                $result['js_libs'][] = $label;
            }
        }

        // --- Analytics from resource URLs ---
        foreach ($this->analyticsSignatures as $sig => $label) {
            if (str_contains($resourceUrls, $sig) && !in_array($label, $result['analytics'])) {
                $result['analytics'][] = $label;
            }
        }

        // --- Cloud provider from ASN / IP info ---
        $asnName = strtolower($serverInfo['asnname'] ?? $urlscanData['page']['asnname'] ?? '');
        foreach ($this->cloudSignatures as $sig => $label) {
            if (str_contains($asnName, $sig)) {
                $result['cloud'] = $label;
                break;
            }
        }

        // --- OS from Server header ---
        $serverFull = strtolower($allHeaders['server'] ?? '');
        foreach ($this->osSignatures as $sig => $label) {
            if (str_contains($serverFull, $sig)) {
                $result['os'] = $label;
                break;
            }
        }

        // --- TLS Version ---
        if (!empty($sslData['tls_version'])) {
            $result['tls_version'] = strtoupper($sslData['tls_version']);
        } elseif ($sslData['valid'] ?? false) {
            $result['tls_version'] = 'TLS';
        }

        // --- Country & ASN ---
        $result['country'] = $serverInfo['country'] ?? $urlscanData['page']['country'] ?? null;
        if ($serverInfo['asn'] ?? null) {
            $result['asn'] = ($serverInfo['asn'] ?? '') . ' ' . ($serverInfo['asnname'] ?? '');
        }

        // --- Screenshot from URLScan ---
        if (!empty($urlscanData['task']['uuid'])) {
            $result['screenshot'] = 'https://urlscan.io/screenshots/' . $urlscanData['task']['uuid'] . '.png';
        }

        // Clean up empty arrays
        foreach (['language', 'framework', 'js_libs', 'analytics'] as $k) {
            $result[$k] = array_values(array_unique($result[$k]));
        }

        return array_filter($result, fn($v) => $v !== null && $v !== []);
    }
}
