<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SubdomainEnumerationService
 *
 * Menggabungkan multi-source passive discovery (Certificate Transparency via crt.sh,
 * HackerTarget API, dan DNS brute-force ringan) dengan DNS resolution,
 * HTTP probing langsung, dan deteksi Subdomain Takeover otomatis.
 */
class SubdomainEnumerationService
{
    /**
     * Database signature untuk deteksi dangling CNAME / Subdomain Takeover.
     */
    protected array $takeoverSignatures = [
        'github.io' => [
            'service'     => 'GitHub Pages',
            'fingerprints' => ["There isn't a GitHub Pages site here", "404 There isn't a GitHub Pages site here"],
        ],
        'herokuapp.com' => [
            'service'     => 'Heroku',
            'fingerprints' => ['No such app', 'Heroku | No such app', 'There is no app configured at that hostname'],
        ],
        'amazonaws.com' => [
            'service'     => 'AWS S3 / CloudFront',
            'fingerprints' => ['NoSuchBucket', 'The specified bucket does not exist', 'Bad Request: ERROR: The request could not be satisfied'],
        ],
        'azurewebsites.net' => [
            'service'     => 'Microsoft Azure Web App',
            'fingerprints' => ['Error 404 - Web app not found', '404 - Web app not found'],
        ],
        'cloudapp.net' => [
            'service'     => 'Microsoft Azure CloudApp',
            'fingerprints' => ['Error 404 - Web app not found'],
        ],
        'fastly.net' => [
            'service'     => 'Fastly CDN',
            'fingerprints' => ['Fastly error: unknown domain', 'Details: cache-'],
        ],
        'myshopify.com' => [
            'service'     => 'Shopify',
            'fingerprints' => ['Sorry, this shop is currently unavailable', 'Only one step left!'],
        ],
        'surge.sh' => [
            'service'     => 'Surge.sh',
            'fingerprints' => ['project not found', '404: Page not found'],
        ],
        'pantheonsite.io' => [
            'service'     => 'Pantheon CMS',
            'fingerprints' => ['404 error unknown site', 'The gods are wise'],
        ],
        'wordpress.com' => [
            'service'     => 'WordPress.com',
            'fingerprints' => ['Do you want to register', 'doesn\'t exist'],
        ],
        'ghost.io' => [
            'service'     => 'Ghost Publishing',
            'fingerprints' => ['The thing you were looking for is no longer here'],
        ],
        'bitbucket.io' => [
            'service'     => 'Bitbucket Cloud',
            'fingerprints' => ['Repository not found'],
        ],
        'readme.io' => [
            'service'     => 'ReadMe Documentation',
            'fingerprints' => ['Project doesnt exist', '404: Project not found'],
        ],
        'fly.io' => [
            'service'     => 'Fly.io',
            'fingerprints' => ['404 Not Found', 'Could not find app'],
        ],
        'fly.dev' => [
            'service'     => 'Fly.io',
            'fingerprints' => ['404 Not Found', 'Could not find app'],
        ],
    ];

    /**
     * Pola kata kunci subdomain berisiko tinggi / menarik untuk pentester.
     */
    protected array $interestingKeywords = [
        'dev', 'staging', 'stage', 'test', 'qa', 'uat', 'demo',
        'admin', 'panel', 'dashboard', 'internal', 'vpn', 'api',
        'mail', 'webmail', 'ftp', 'git', 'gitlab', 'jenkins',
        'ci', 'cd', 'jira', 'confluence', 'grafana', 'kibana',
        'monitoring', 'db', 'database', 'backup', 'old', 'legacy',
        'secret', 'corp', 'auth', 'sso', 'login', 'portal', 'cpanel',
    ];

    /**
     * Common subdomains untuk DNS dictionary lookup cepat.
     */
    protected array $commonPrefixes = [
        'www', 'api', 'dev', 'staging', 'mail', 'admin', 'app', 'portal',
        'vpn', 'test', 'beta', 'auth', 'webmail', 'cpanel', 'cdn', 'static',
        'blog', 'shop', 'docs', 'secure', 'status', 'git', 'support', 'internal',
    ];

    /**
     * Enumerate subdomains dari berbagai sumber, cek DNS, live status, dan takeover risk.
     *
     * @param string $host Host target (misal: "example.com" atau "sub.example.com")
     * @param int $maxCandidates Batas maksimum subdomain yang diproses secara mendalam
     * @return array
     */
    public function enumerate(string $host, int $maxCandidates = 60): array
    {
        $baseDomain = $this->extractBaseDomain($host);
        if (empty($baseDomain)) {
            return [
                'subdomains'      => [],
                'total'           => 0,
                'live_count'      => 0,
                'takeover_risks'  => [],
                'interesting'     => [],
                'sources_summary' => [],
            ];
        }

        $candidates = [];
        $sourcesSummary = ['crt_sh' => 0, 'hackertarget' => 0, 'dns_wordlist' => 0];

        // 1. Certificate Transparency (crt.sh)
        $crtSubs = $this->queryCrtSh($baseDomain);
        $sourcesSummary['crt_sh'] = count($crtSubs);
        foreach ($crtSubs as $sub) {
            $candidates[$sub] = ['source' => 'crt.sh'];
        }

        // 2. Passive DNS via HackerTarget (fallback & tambahan)
        $htSubs = $this->queryHackerTarget($baseDomain);
        $sourcesSummary['hackertarget'] = count($htSubs);
        foreach ($htSubs as $sub => $info) {
            if (!isset($candidates[$sub])) {
                $candidates[$sub] = array_merge(['source' => 'HackerTarget'], $info);
            } elseif (!empty($info['ip']) && empty($candidates[$sub]['ip'])) {
                $candidates[$sub]['ip'] = $info['ip'];
            }
        }

        // 3. DNS Wordlist probing untuk prefixes penting
        $wordlistSubs = $this->probeCommonDns($baseDomain);
        $sourcesSummary['dns_wordlist'] = count($wordlistSubs);
        foreach ($wordlistSubs as $sub => $info) {
            if (!isset($candidates[$sub])) {
                $candidates[$sub] = array_merge(['source' => 'DNS Probing'], $info);
            }
        }

        // Ambil domain unik dan limit
        $subdomainKeys = array_keys($candidates);
        usort($subdomainKeys, fn($a, $b) => strcmp($a, $b));
        $selectedKeys = array_slice($subdomainKeys, 0, $maxCandidates);

        // 4. Proses Resolusi DNS, Live HTTP Probing, dan Subdomain Takeover Check
        $processed = [];
        $takeoverRisks = [];
        $interesting = [];
        $liveCount = 0;

        foreach ($selectedKeys as $subdomain) {
            $item = $this->inspectSubdomain($subdomain, $baseDomain, $candidates[$subdomain] ?? []);

            if ($item['is_alive']) {
                $liveCount++;
            }
            if ($item['takeover_risk']) {
                $takeoverRisks[] = $item;
            }
            if ($item['is_interesting']) {
                $interesting[] = $item['subdomain'];
            }

            $processed[] = $item;
        }

        // Urutkan: Subdomain yang takeover_risk pertama, lalu is_alive, lalu nama
        usort($processed, function ($a, $b) {
            if ($a['takeover_risk'] !== $b['takeover_risk']) {
                return $b['takeover_risk'] <=> $a['takeover_risk'];
            }
            if ($a['is_alive'] !== $b['is_alive']) {
                return $b['is_alive'] <=> $a['is_alive'];
            }
            return strcmp($a['subdomain'], $b['subdomain']);
        });

        return [
            'subdomains'      => $processed,
            'total'           => count($processed),
            'live_count'      => $liveCount,
            'takeover_risks'  => $takeoverRisks,
            'interesting'     => array_values(array_unique($interesting)),
            'sources_summary' => $sourcesSummary,
            'base_domain'     => $baseDomain,
        ];
    }

    /**
     * Inspeksi mendalam satu subdomain (DNS records, CNAME takeover, HTTP probe).
     */
    public function inspectSubdomain(string $subdomain, string $baseDomain, array $meta = []): array
    {
        $dnsInfo = $this->resolveDnsRecords($subdomain);
        $ip = $dnsInfo['ip'] ?? ($meta['ip'] ?? null);
        $cname = $dnsInfo['cname'] ?? null;

        $takeoverCheck = $this->checkTakeoverRisk($subdomain, $cname);

        $httpInfo = [
            'is_alive'       => false,
            'status_http'    => null,
            'server_header'  => null,
            'page_title'     => null,
        ];

        // Jika memiliki IP atau CNAME, lakukan probing HTTP cepat (2s timeout)
        if (!empty($ip) || !empty($cname)) {
            $httpInfo = $this->probeHttpEndpoint($subdomain);
        }

        // Cek kategori interesting
        $isInteresting = false;
        foreach ($this->interestingKeywords as $keyword) {
            if (str_contains(strtolower($subdomain), $keyword)) {
                $isInteresting = true;
                break;
            }
        }

        return [
            'subdomain'         => $subdomain,
            'root_domain'       => $baseDomain,
            'ip_address'        => $ip,
            'cname'             => $cname,
            'status_http'       => $httpInfo['status_http'],
            'is_alive'          => $httpInfo['is_alive'],
            'server_header'     => $httpInfo['server_header'],
            'page_title'        => $httpInfo['page_title'],
            'takeover_risk'     => $takeoverCheck['risk'],
            'takeover_service'  => $takeoverCheck['service'],
            'takeover_evidence' => $takeoverCheck['evidence'],
            'is_interesting'    => $isInteresting,
            'source'            => $meta['source'] ?? 'crt.sh',
        ];
    }

    /**
     * Resolusi DNS A dan CNAME record.
     */
    protected function resolveDnsRecords(string $subdomain): array
    {
        $ip = null;
        $cname = null;

        try {
            // DNS CNAME
            $cnameRecords = @dns_get_record($subdomain, DNS_CNAME);
            if (!empty($cnameRecords) && isset($cnameRecords[0]['target'])) {
                $cname = rtrim($cnameRecords[0]['target'], '.');
            }

            // DNS A Record
            $aRecords = @dns_get_record($subdomain, DNS_A);
            if (!empty($aRecords) && isset($aRecords[0]['ip'])) {
                $ip = $aRecords[0]['ip'];
            } elseif (empty($ip)) {
                $resolvedIp = @gethostbyname($subdomain);
                if ($resolvedIp !== $subdomain && filter_var($resolvedIp, FILTER_VALIDATE_IP)) {
                    $ip = $resolvedIp;
                }
            }
        } catch (\Throwable $e) {
            // Fallback gethostbyname jika dns_get_record gagal
            $resolvedIp = @gethostbyname($subdomain);
            if ($resolvedIp !== $subdomain && filter_var($resolvedIp, FILTER_VALIDATE_IP)) {
                $ip = $resolvedIp;
            }
        }

        return ['ip' => $ip, 'cname' => $cname];
    }

    /**
     * Cek apakah target CNAME rentan terhadap Subdomain Takeover (Dangling CNAME).
     */
    protected function checkTakeoverRisk(string $subdomain, ?string $cname): array
    {
        if (empty($cname)) {
            return ['risk' => false, 'service' => null, 'evidence' => null];
        }

        $cnameLower = strtolower($cname);
        $matchedService = null;
        $fingerprints = [];

        foreach ($this->takeoverSignatures as $domainPattern => $info) {
            if (str_contains($cnameLower, $domainPattern)) {
                $matchedService = $info['service'];
                $fingerprints = $info['fingerprints'];
                break;
            }
        }

        if (!$matchedService) {
            return ['risk' => false, 'service' => null, 'evidence' => null];
        }

        // Lakukan verifikasi HTTP/HTTPS apakah benar-benar dangling/unclaimed
        $evidence = null;
        $confirmedRisk = false;

        foreach (["https://{$subdomain}", "http://{$subdomain}"] as $url) {
            try {
                $resp = Http::timeout(3)->withOptions(['verify' => false])->get($url);
                $body = $resp->body();

                foreach ($fingerprints as $fp) {
                    if (str_contains($body, $fp)) {
                        $confirmedRisk = true;
                        $evidence = "CNAME mengarah ke {$cname} ({$matchedService}). Respons web mengandung fingerprint takeover: \"{$fp}\"";
                        break 2;
                    }
                }
            } catch (\Throwable $e) {
                // Connection refused atau timeout bisa mengindikasikan service tidak aktif
            }
        }

        // Jika DNS CNAME mengarah ke cloud provider tapi DNS resolving A record kosong (NXDOMAIN)
        if (!$confirmedRisk) {
            $cnameIp = @gethostbyname($cname);
            if ($cnameIp === $cname) {
                $confirmedRisk = true;
                $evidence = "CNAME mengarah ke {$cname} ({$matchedService}), namun domain target tidak memiliki IP (NXDOMAIN / Dangling CNAME).";
            }
        }

        return [
            'risk'     => $confirmedRisk,
            'service'  => $confirmedRisk ? $matchedService : null,
            'evidence' => $evidence,
        ];
    }

    /**
     * Quick HTTP probe untuk mendapatkan status kode, server header, dan page title.
     */
    protected function probeHttpEndpoint(string $subdomain): array
    {
        $status = null;
        $server = null;
        $title = null;
        $isAlive = false;

        foreach (["https://{$subdomain}", "http://{$subdomain}"] as $url) {
            try {
                $resp = Http::timeout(2.5)->withOptions([
                    'verify' => false,
                    'allow_redirects' => ['max' => 2],
                ])->get($url);

                $status = $resp->status();
                $server = $resp->header('Server') ?: $resp->header('server');
                $isAlive = true;

                // Ekstrak title HTML
                $html = substr($resp->body(), 0, 4096);
                if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
                    $title = html_entity_decode(trim(preg_replace('/\s+/', ' ', $m[1])));
                    $title = mb_substr($title, 0, 100);
                }

                break; // Berhasil merespons
            } catch (\Throwable $e) {
                continue;
            }
        }

        return [
            'is_alive'      => $isAlive,
            'status_http'   => $status,
            'server_header' => $server ? mb_substr($server, 0, 80) : null,
            'page_title'    => $title,
        ];
    }

    /**
     * Query Certificate Transparency logs via crt.sh.
     */
    protected function queryCrtSh(string $baseDomain): array
    {
        try {
            $resp = Http::timeout(7)->get("https://crt.sh/?q=%.{$baseDomain}&output=json");
            if (!$resp->successful()) {
                return [];
            }

            $entries = $resp->json();
            if (!is_array($entries)) {
                return [];
            }

            $found = [];
            foreach ($entries as $entry) {
                $names = explode("\n", $entry['name_value'] ?? '');
                foreach ($names as $name) {
                    $name = strtolower(trim($name));
                    $name = ltrim($name, '*.');
                    if (!empty($name) && str_ends_with($name, $baseDomain) && $name !== $baseDomain) {
                        $found[$name] = true;
                    }
                }
            }

            return array_keys($found);
        } catch (\Throwable $e) {
            Log::info("crt.sh lookup skipped: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Query HackerTarget Hostsearch API.
     */
    protected function queryHackerTarget(string $baseDomain): array
    {
        try {
            $resp = Http::timeout(6)->get("https://api.hackertarget.com/hostsearch/?q={$baseDomain}");
            if (!$resp->successful()) {
                return [];
            }

            $body = trim($resp->body());
            if (str_contains($body, 'error') || str_contains($body, 'API count exceeded')) {
                return [];
            }

            $results = [];
            $lines = explode("\n", $body);
            foreach ($lines as $line) {
                $parts = explode(',', trim($line));
                if (count($parts) >= 1) {
                    $host = strtolower(trim($parts[0]));
                    $ip = isset($parts[1]) ? trim($parts[1]) : null;
                    if (str_ends_with($host, $baseDomain) && $host !== $baseDomain) {
                        $results[$host] = ['ip' => $ip];
                    }
                }
            }

            return $results;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Probing DNS langsung untuk subdomain-subdomain penting.
     */
    protected function probeCommonDns(string $baseDomain): array
    {
        $results = [];
        foreach ($this->commonPrefixes as $prefix) {
            $candidate = "{$prefix}.{$baseDomain}";
            $ip = @gethostbyname($candidate);
            if ($ip !== $candidate && filter_var($ip, FILTER_VALIDATE_IP)) {
                $results[$candidate] = ['ip' => $ip];
            }
        }
        return $results;
    }

    /**
     * Ekstrak domain dasar (menghilangkan subdomain / port / www).
     */
    public function extractBaseDomain(string $urlOrHost): string
    {
        $host = parse_url($urlOrHost, PHP_URL_HOST) ?: $urlOrHost;
        $host = strtolower(trim(preg_replace('/:\d+$/', '', $host)));
        $host = preg_replace('/^www\./', '', $host);

        // Jika IP, kembalikan apa adanya
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $host;
        }

        // Parsing domain parts
        $parts = explode('.', $host);
        if (count($parts) <= 2) {
            return $host;
        }

        // Tangani ccTLD seperti .co.id, .ac.id, .gov.uk
        $ccTlds = ['co.id', 'ac.id', 'go.id', 'or.id', 'sch.id', 'mil.id', 'web.id', 'co.uk', 'com.au', 'co.jp'];
        $lastTwo = implode('.', array_slice($parts, -2));

        if (in_array($lastTwo, $ccTlds) && count($parts) >= 3) {
            return implode('.', array_slice($parts, -3));
        }

        return implode('.', array_slice($parts, -2));
    }
}
