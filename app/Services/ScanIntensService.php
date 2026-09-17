<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScanIntensService
{
    protected VirusTotalService $vtService;
    protected URLScanService $urlScanService;

    public function __construct(VirusTotalService $vtService, URLScanService $urlScanService)
    {
        $this->vtService = $vtService;
        $this->urlScanService = $urlScanService;
    }

    /**
     * Scan Mode 2 (Intens) — Full penetration testing, tanpa sensor.
     */
    public function scan(string $url): array
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        $vtData = $this->vtService->getUrlReport($url) ?? [];
        $urlscanData = $this->submitAndPollUrlscan($url);
        $headerData = $this->analyzeHeaders($url);
        $sslData = $this->analyzeSSL($url);
        $commonFiles = $this->checkCommonFiles($url);
        $dnsData = $this->analyzeDNS($host);
        $portData = $this->scanCommonPorts($host);
        $cookieData = $this->analyzeCookies($url);
        $corsData = $this->analyzeCORS($url);
        $httpMethods = $this->enumerateHTTPMethods($url);
        $redirectData = $this->analyzeRedirects($url);
        $subdomains = $this->enumerateSubdomains($host);

        return $this->buildReport(
            $url, $vtData, $urlscanData, $headerData, $sslData,
            $commonFiles, $dnsData, $portData, $cookieData, $corsData,
            $httpMethods, $redirectData, $subdomains
        );
    }

    protected function submitAndPollUrlscan(string $url): array
    {
        // 1. Coba cari hasil scan terbaru di cache (dalam 24 jam terakhir)
        $cachedResult = $this->urlScanService->searchCachedScan($url);
        if ($cachedResult) {
            return $cachedResult;
        }

        // 2. Jika tidak ada di cache, buat scan baru (live)
        $submission = $this->urlScanService->submit($url, 'private');
        if (!$submission || empty($submission['uuid'])) {
            return [];
        }

        // Tunggu maksimal 45 detik
        $result = $this->urlScanService->pollResult($submission['uuid'], 45);
        return $result ?? [];
    }

    // =========================================================================
    // MODUL 1: SECURITY HEADERS (Expanded — 15 headers)
    // =========================================================================

    /**
     * Analisis HTTP security headers lengkap — 15 header kritis.
     */
    public function analyzeHeaders(string $url): array
    {
        try {
            $response = Http::timeout(10)->withOptions([
                'verify' => false,
            ])->get($url);

            $allHeaders = [];
            foreach ($response->headers() as $name => $values) {
                $allHeaders[$name] = $values[0] ?? '';
            }

            // 15 security headers dengan bobot poin
            $securityHeaders = [
                'Content-Security-Policy'            => ['poin' => 15, 'kritis' => true,  'deskripsi' => 'Mencegah XSS dan injection dengan membatasi sumber konten'],
                'Strict-Transport-Security'           => ['poin' => 15, 'kritis' => true,  'deskripsi' => 'Memaksa koneksi HTTPS, mencegah downgrade attack'],
                'X-Frame-Options'                     => ['poin' => 10, 'kritis' => true,  'deskripsi' => 'Mencegah clickjacking dengan melarang embedding di iframe'],
                'X-Content-Type-Options'              => ['poin' => 10, 'kritis' => true,  'deskripsi' => 'Mencegah MIME-type sniffing attack'],
                'Referrer-Policy'                     => ['poin' => 8,  'kritis' => false, 'deskripsi' => 'Mengontrol informasi referrer yang dikirim ke situs lain'],
                'Permissions-Policy'                  => ['poin' => 8,  'kritis' => false, 'deskripsi' => 'Membatasi akses fitur browser (kamera, mikrofon, geolocation)'],
                'X-XSS-Protection'                    => ['poin' => 5,  'kritis' => false, 'deskripsi' => 'Filter XSS bawaan browser (legacy, tetap relevan)'],
                'X-DNS-Prefetch-Control'              => ['poin' => 3,  'kritis' => false, 'deskripsi' => 'Mengontrol DNS prefetching untuk mencegah kebocoran informasi'],
                'X-Download-Options'                  => ['poin' => 3,  'kritis' => false, 'deskripsi' => 'Mencegah IE membuka file download langsung dalam konteks situs'],
                'X-Permitted-Cross-Domain-Policies'   => ['poin' => 3,  'kritis' => false, 'deskripsi' => 'Mengontrol kebijakan cross-domain untuk Flash/PDF'],
                'Cross-Origin-Embedder-Policy'        => ['poin' => 5,  'kritis' => false, 'deskripsi' => 'Mengontrol embedding resource cross-origin (COEP)'],
                'Cross-Origin-Opener-Policy'          => ['poin' => 5,  'kritis' => false, 'deskripsi' => 'Mengisolasi browsing context dari cross-origin (COOP)'],
                'Cross-Origin-Resource-Policy'        => ['poin' => 5,  'kritis' => false, 'deskripsi' => 'Membatasi siapa yang bisa memuat resource ini (CORP)'],
                'Cache-Control'                       => ['poin' => 3,  'kritis' => false, 'deskripsi' => 'Mengontrol caching untuk mencegah data sensitif tersimpan'],
                'X-Robots-Tag'                        => ['poin' => 2,  'kritis' => false, 'deskripsi' => 'Mengontrol indexing mesin pencari (mencegah eksposur)'],
            ];

            $headerEvaluation = [];
            $skor = 0;
            $totalPoin = 0;

            foreach ($securityHeaders as $name => $meta) {
                $totalPoin += $meta['poin'];
                $lowerName = strtolower($name);
                $exists = isset($allHeaders[$lowerName]) || isset($allHeaders[$name]);
                $value = $allHeaders[$lowerName] ?? $allHeaders[$name] ?? null;

                $status = 'missing';
                $evaluasi = null;
                if ($exists) {
                    $evaluasi = $this->evaluateHeaderValue($name, $value);
                    $status = $evaluasi['baik'] ? 'baik' : 'misconfigured';
                    $skor += $evaluasi['baik'] ? $meta['poin'] : (int)($meta['poin'] * 0.5);
                }

                $headerEvaluation[$name] = [
                    'ada'       => $exists,
                    'nilai'     => $value,
                    'status'    => $status,
                    'poin'      => $exists ? ($evaluasi['baik'] ?? false ? $meta['poin'] : (int)($meta['poin'] * 0.5)) : 0,
                    'max_poin'  => $meta['poin'],
                    'kritis'    => $meta['kritis'],
                    'deskripsi' => $meta['deskripsi'],
                    'catatan'   => $evaluasi['catatan'] ?? ($exists ? null : 'Header tidak ditemukan'),
                ];
            }

            // Deteksi headers sensitif yang expose info server
            $sensitiveHeaders = [
                'server'            => 'Mengekspos software dan versi web server',
                'x-powered-by'     => 'Mengekspos framework/bahasa backend',
                'x-aspnet-version' => 'Mengekspos versi ASP.NET',
                'x-generator'      => 'Mengekspos CMS/generator yang digunakan',
                'x-debug'          => 'Mode debug aktif di production',
                'x-runtime'        => 'Mengekspos waktu eksekusi (timing attack)',
                'x-request-id'     => 'Informasi internal request tracking',
                'via'              => 'Mengekspos proxy/gateway internal',
            ];

            $exposedHeaders = [];
            foreach ($sensitiveHeaders as $sh => $desc) {
                if (isset($allHeaders[$sh])) {
                    $exposedHeaders[$sh] = [
                        'nilai'     => $allHeaders[$sh],
                        'deskripsi' => $desc,
                    ];
                }
            }

            // Skor dinormalisasi ke 100
            $skorNormalized = $totalPoin > 0 ? (int) round(($skor / $totalPoin) * 100) : 0;

            return [
                'skor'             => $skorNormalized,
                'skor_mentah'      => $skor,
                'total_poin'       => $totalPoin,
                'headers_keamanan' => $headerEvaluation,
                'headers_sensitif' => $exposedHeaders,
                'semua_headers'    => $allHeaders,
            ];
        } catch (\Exception $e) {
            Log::warning('Header analysis intens gagal: ' . $e->getMessage());
            return ['skor' => 0, 'skor_mentah' => 0, 'total_poin' => 100, 'headers_keamanan' => [], 'headers_sensitif' => [], 'semua_headers' => []];
        }
    }

    protected function evaluateHeaderValue(string $name, ?string $value): array
    {
        if (empty($value)) return ['baik' => false, 'catatan' => 'Nilai kosong'];

        return match ($name) {
            'X-Content-Type-Options' => [
                'baik'    => strtolower($value) === 'nosniff',
                'catatan' => strtolower($value) === 'nosniff' ? null : 'Harus bernilai "nosniff"',
            ],
            'X-Frame-Options' => [
                'baik'    => in_array(strtoupper($value), ['DENY', 'SAMEORIGIN']),
                'catatan' => in_array(strtoupper($value), ['DENY', 'SAMEORIGIN']) ? null : 'Gunakan DENY atau SAMEORIGIN',
            ],
            'Strict-Transport-Security' => [
                'baik'    => str_contains(strtolower($value), 'max-age=') && (int) preg_replace('/.*max-age=(\d+).*/i', '$1', $value) >= 31536000,
                'catatan' => (int) preg_replace('/.*max-age=(\d+).*/i', '$1', $value) >= 31536000 ? null : 'max-age harus minimal 31536000 (1 tahun)',
            ],
            'Content-Security-Policy' => [
                'baik'    => !str_contains($value, "'unsafe-inline'") && !str_contains($value, "'unsafe-eval'"),
                'catatan' => (str_contains($value, "'unsafe-inline'") || str_contains($value, "'unsafe-eval'")) ? 'Mengandung unsafe-inline atau unsafe-eval (melemahkan CSP)' : null,
            ],
            'Referrer-Policy' => [
                'baik'    => in_array(strtolower($value), ['no-referrer', 'strict-origin', 'strict-origin-when-cross-origin', 'same-origin', 'no-referrer-when-downgrade', 'origin', 'origin-when-cross-origin']),
                'catatan' => null,
            ],
            'X-XSS-Protection' => [
                'baik'    => str_starts_with($value, '1'),
                'catatan' => str_starts_with($value, '1') ? null : 'Harus diset "1; mode=block"',
            ],
            'Cache-Control' => [
                'baik'    => str_contains(strtolower($value), 'no-store') || str_contains(strtolower($value), 'private'),
                'catatan' => (str_contains(strtolower($value), 'no-store') || str_contains(strtolower($value), 'private')) ? null : 'Pertimbangkan no-store atau private untuk data sensitif',
            ],
            'Cross-Origin-Opener-Policy' => [
                'baik'    => in_array(strtolower($value), ['same-origin', 'same-origin-allow-popups']),
                'catatan' => null,
            ],
            'Cross-Origin-Embedder-Policy' => [
                'baik'    => strtolower($value) === 'require-corp',
                'catatan' => strtolower($value) === 'require-corp' ? null : 'Gunakan require-corp untuk isolasi penuh',
            ],
            'Cross-Origin-Resource-Policy' => [
                'baik'    => in_array(strtolower($value), ['same-origin', 'same-site']),
                'catatan' => null,
            ],
            default => ['baik' => true, 'catatan' => null],
        };
    }

    // =========================================================================
    // MODUL 2: SSL/TLS ANALYSIS (Enhanced)
    // =========================================================================

    /**
     * Analisis SSL/TLS secara detail — termasuk cipher dan key strength.
     */
    public function analyzeSSL(string $url): array
    {
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';

        if (empty($host) || ($parsed['scheme'] ?? '') !== 'https') {
            return ['valid' => false, 'status' => 'tidak_https', 'detail' => 'URL tidak menggunakan HTTPS'];
        }

        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'capture_peer_cert_chain' => true,
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);
            $socket = @stream_socket_client(
                "ssl://{$host}:443", $errno, $errstr, 10,
                STREAM_CLIENT_CONNECT, $context
            );

            if (!$socket) {
                return ['valid' => false, 'status' => 'gagal_koneksi', 'detail' => $errstr];
            }

            $params = stream_context_get_params($socket);
            $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate'] ?? '');

            // TLS version & cipher info
            $meta = stream_get_meta_data($socket);
            $protocol = $meta['crypto']['protocol'] ?? 'unknown';
            $cipherName = $meta['crypto']['cipher_name'] ?? 'unknown';
            $cipherBits = $meta['crypto']['cipher_bits'] ?? 0;
            fclose($socket);

            if (!$cert) {
                return ['valid' => false, 'status' => 'sertifikat_error', 'detail' => 'Tidak dapat membaca sertifikat'];
            }

            $expiry = $cert['validTo_time_t'] ?? 0;
            $validFrom = $cert['validFrom_time_t'] ?? 0;
            $daysUntilExpiry = max(0, (int) ceil(($expiry - time()) / 86400));
            $issuer = $cert['issuer']['O'] ?? $cert['issuer']['CN'] ?? 'Tidak diketahui';
            $subject = $cert['subject']['CN'] ?? 'Tidak diketahui';
            $isExpired = $expiry < time();
            $isSelfSigned = ($cert['issuer']['CN'] ?? '') === ($cert['subject']['CN'] ?? '')
                && empty($cert['issuer']['O']);

            // Chain validation
            $chain = $params['options']['ssl']['peer_certificate_chain'] ?? [];
            $chainValid = count($chain) > 1;

            // SAN (Subject Alternative Names)
            $san = [];
            if (!empty($cert['extensions']['subjectAltName'])) {
                $sanRaw = $cert['extensions']['subjectAltName'];
                preg_match_all('/DNS:([^,\s]+)/', $sanRaw, $matches);
                $san = $matches[1] ?? [];
            }

            // Signature algorithm
            $sigAlg = $cert['signatureTypeSN'] ?? $cert['signatureTypeLN'] ?? 'unknown';

            // Weak protocol/cipher check
            $weakProtocol = in_array(strtolower($protocol), ['sslv2', 'sslv3', 'tlsv1', 'tlsv1.0', 'tlsv1.1']);
            $weakCipher = $cipherBits < 128 || str_contains(strtolower($cipherName), 'rc4') || str_contains(strtolower($cipherName), 'des');
            $weakSignature = str_contains(strtolower($sigAlg), 'sha1') || str_contains(strtolower($sigAlg), 'md5');

            $status = 'valid';
            $masalah = [];
            if ($isExpired) { $status = 'expired'; $masalah[] = 'Sertifikat sudah expired'; }
            if ($isSelfSigned) { $status = 'self_signed'; $masalah[] = 'Sertifikat self-signed'; }
            if ($weakProtocol) { $masalah[] = "Protokol lemah: {$protocol}"; }
            if ($weakCipher) { $masalah[] = "Cipher suite lemah: {$cipherName} ({$cipherBits}-bit)"; }
            if ($weakSignature) { $masalah[] = "Signature algorithm lemah: {$sigAlg}"; }
            if ($daysUntilExpiry < 30 && !$isExpired) { $masalah[] = "Sertifikat akan expired dalam {$daysUntilExpiry} hari"; }

            return [
                'valid'             => !$isExpired && !$isSelfSigned && !$weakProtocol,
                'status'            => $status,
                'issuer'            => $issuer,
                'subject'           => $subject,
                'valid_from'        => date('Y-m-d H:i:s', $validFrom),
                'expires_at'        => date('Y-m-d H:i:s', $expiry),
                'days_until_expiry' => $daysUntilExpiry,
                'protocol'          => $protocol,
                'cipher_name'       => $cipherName,
                'cipher_bits'       => $cipherBits,
                'signature_alg'     => $sigAlg,
                'chain_valid'       => $chainValid,
                'chain_length'      => count($chain),
                'san'               => array_slice($san, 0, 20),
                'weak_protocol'     => $weakProtocol,
                'weak_cipher'       => $weakCipher,
                'weak_signature'    => $weakSignature,
                'masalah'           => $masalah,
            ];
        } catch (\Exception $e) {
            Log::warning('SSL analysis intens gagal: ' . $e->getMessage());
            return ['valid' => false, 'status' => 'error', 'detail' => $e->getMessage()];
        }
    }

    // =========================================================================
    // MODUL 3: COMMON FILES / SENSITIVE PATH ENUMERATION (Expanded — 35+ paths)
    // =========================================================================

    /**
     * Cek file dan path sensitif yang mungkin terekspos — 35+ path.
     */
    public function checkCommonFiles(string $url): array
    {
        $parsed = parse_url($url);
        $base = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');

        $paths = [
            // Source Control Exposure
            ['path' => '/.git/HEAD',          'kategori' => 'source_control',  'risiko' => 'kritis',  'deskripsi' => 'Git repository HEAD — source code leak'],
            ['path' => '/.git/config',        'kategori' => 'source_control',  'risiko' => 'kritis',  'deskripsi' => 'Git config — remote URL & credentials'],
            ['path' => '/.svn/entries',       'kategori' => 'source_control',  'risiko' => 'kritis',  'deskripsi' => 'SVN repository — source code leak'],
            ['path' => '/.hg/',               'kategori' => 'source_control',  'risiko' => 'tinggi',  'deskripsi' => 'Mercurial repository'],
            ['path' => '/.bzr/',              'kategori' => 'source_control',  'risiko' => 'tinggi',  'deskripsi' => 'Bazaar repository'],

            // Config & Environment Leaks
            ['path' => '/.env',               'kategori' => 'config_leak',     'risiko' => 'kritis',  'deskripsi' => 'Environment file — API keys, DB credentials'],
            ['path' => '/.env.bak',           'kategori' => 'config_leak',     'risiko' => 'kritis',  'deskripsi' => 'Backup environment file'],
            ['path' => '/wp-config.php.bak',  'kategori' => 'config_leak',     'risiko' => 'kritis',  'deskripsi' => 'WordPress config backup — DB credentials'],
            ['path' => '/config.php',         'kategori' => 'config_leak',     'risiko' => 'tinggi',  'deskripsi' => 'Configuration file PHP'],
            ['path' => '/configuration.php',  'kategori' => 'config_leak',     'risiko' => 'tinggi',  'deskripsi' => 'Joomla config file'],
            ['path' => '/web.config',         'kategori' => 'config_leak',     'risiko' => 'tinggi',  'deskripsi' => 'IIS web server config'],
            ['path' => '/.htaccess',          'kategori' => 'config_leak',     'risiko' => 'sedang',  'deskripsi' => 'Apache config — rewrite rules, auth'],
            ['path' => '/.htpasswd',          'kategori' => 'config_leak',     'risiko' => 'kritis',  'deskripsi' => 'Apache password file — hashed credentials'],

            // Backup & Database Dumps
            ['path' => '/backup.sql',         'kategori' => 'backup',          'risiko' => 'kritis',  'deskripsi' => 'Database backup SQL dump'],
            ['path' => '/db.sql',             'kategori' => 'backup',          'risiko' => 'kritis',  'deskripsi' => 'Database dump'],
            ['path' => '/dump.sql',           'kategori' => 'backup',          'risiko' => 'kritis',  'deskripsi' => 'Database dump'],
            ['path' => '/backup.zip',         'kategori' => 'backup',          'risiko' => 'kritis',  'deskripsi' => 'Site backup archive'],
            ['path' => '/backup.tar.gz',      'kategori' => 'backup',          'risiko' => 'kritis',  'deskripsi' => 'Site backup tarball'],

            // Debug & Information Disclosure
            ['path' => '/phpinfo.php',        'kategori' => 'debug',           'risiko' => 'tinggi',  'deskripsi' => 'PHP info — full server configuration'],
            ['path' => '/info.php',           'kategori' => 'debug',           'risiko' => 'tinggi',  'deskripsi' => 'PHP info variant'],
            ['path' => '/server-status',      'kategori' => 'debug',           'risiko' => 'tinggi',  'deskripsi' => 'Apache server status — request info'],
            ['path' => '/server-info',        'kategori' => 'debug',           'risiko' => 'tinggi',  'deskripsi' => 'Apache server info — module list'],
            ['path' => '/elmah.axd',          'kategori' => 'debug',           'risiko' => 'tinggi',  'deskripsi' => 'ASP.NET error log viewer'],
            ['path' => '/_debugbar',          'kategori' => 'debug',           'risiko' => 'sedang',  'deskripsi' => 'Laravel debug bar'],
            ['path' => '/telescope',          'kategori' => 'debug',           'risiko' => 'sedang',  'deskripsi' => 'Laravel Telescope debug tool'],

            // Admin Panels
            ['path' => '/admin',              'kategori' => 'admin_panel',     'risiko' => 'sedang',  'deskripsi' => 'Admin panel generik'],
            ['path' => '/wp-admin',           'kategori' => 'admin_panel',     'risiko' => 'sedang',  'deskripsi' => 'WordPress admin panel'],
            ['path' => '/administrator',      'kategori' => 'admin_panel',     'risiko' => 'sedang',  'deskripsi' => 'Joomla/CMS admin panel'],
            ['path' => '/login',              'kategori' => 'admin_panel',     'risiko' => 'rendah',  'deskripsi' => 'Login page'],
            ['path' => '/dashboard',          'kategori' => 'admin_panel',     'risiko' => 'sedang',  'deskripsi' => 'Dashboard page'],
            ['path' => '/phpmyadmin',         'kategori' => 'admin_panel',     'risiko' => 'kritis',  'deskripsi' => 'phpMyAdmin — database management'],
            ['path' => '/adminer',            'kategori' => 'admin_panel',     'risiko' => 'kritis',  'deskripsi' => 'Adminer — database management'],

            // API Documentation
            ['path' => '/swagger',            'kategori' => 'api_docs',        'risiko' => 'sedang',  'deskripsi' => 'Swagger UI — API documentation'],
            ['path' => '/swagger.json',       'kategori' => 'api_docs',        'risiko' => 'sedang',  'deskripsi' => 'Swagger JSON spec'],
            ['path' => '/api-docs',           'kategori' => 'api_docs',        'risiko' => 'sedang',  'deskripsi' => 'API documentation'],
            ['path' => '/openapi.json',       'kategori' => 'api_docs',        'risiko' => 'sedang',  'deskripsi' => 'OpenAPI spec file'],
            ['path' => '/graphql',            'kategori' => 'api_docs',        'risiko' => 'sedang',  'deskripsi' => 'GraphQL endpoint (introspection)'],

            // Web Standard Files
            ['path' => '/robots.txt',         'kategori' => 'web_standard',    'risiko' => 'info',    'deskripsi' => 'Robots.txt — bisa mengekspos path sensitif'],
            ['path' => '/sitemap.xml',        'kategori' => 'web_standard',    'risiko' => 'info',    'deskripsi' => 'Sitemap — struktur website'],
            ['path' => '/crossdomain.xml',    'kategori' => 'web_standard',    'risiko' => 'sedang',  'deskripsi' => 'Flash cross-domain policy'],
            ['path' => '/.well-known/security.txt', 'kategori' => 'web_standard', 'risiko' => 'info', 'deskripsi' => 'Security contact information'],
        ];

        $results = [];
        foreach ($paths as $item) {
            try {
                $response = Http::timeout(5)->withOptions([
                    'verify' => false,
                    'allow_redirects' => ['max' => 2],
                ])->get($base . $item['path']);

                $statusCode = $response->status();
                $bodySize = strlen($response->body());
                $terekspos = $statusCode === 200 && $bodySize > 0;

                // Beberapa path false-positive jika redirect ke homepage
                if ($terekspos && $bodySize > 50000) {
                    // Kemungkinan redirect ke homepage, bukan file asli
                    $terekspos = false;
                }

                $results[] = [
                    'path'      => $item['path'],
                    'kategori'  => $item['kategori'],
                    'risiko'    => $item['risiko'],
                    'deskripsi' => $item['deskripsi'],
                    'status'    => $statusCode,
                    'terekspos' => $terekspos,
                    'size'      => $bodySize,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'path'      => $item['path'],
                    'kategori'  => $item['kategori'],
                    'risiko'    => $item['risiko'],
                    'deskripsi' => $item['deskripsi'],
                    'status'    => 0,
                    'terekspos' => false,
                    'error'     => 'Timeout/Error',
                ];
            }
        }

        return $results;
    }

    // =========================================================================
    // MODUL 4: DNS RECONNAISSANCE (Baru)
    // =========================================================================

    /**
     * DNS Reconnaissance — resolve records & deteksi email security.
     */
    public function analyzeDNS(string $host): array
    {
        if (empty($host)) {
            return ['error' => 'Host kosong'];
        }

        try {
            $records = [];
            $recordTypes = [DNS_A, DNS_AAAA, DNS_MX, DNS_NS, DNS_TXT, DNS_CNAME, DNS_SOA];
            $typeLabels = [DNS_A => 'A', DNS_AAAA => 'AAAA', DNS_MX => 'MX', DNS_NS => 'NS', DNS_TXT => 'TXT', DNS_CNAME => 'CNAME', DNS_SOA => 'SOA'];

            foreach ($recordTypes as $type) {
                $result = @dns_get_record($host, $type);
                if ($result) {
                    foreach ($result as $r) {
                        $records[] = array_merge($r, ['type_label' => $typeLabels[$type] ?? $r['type'] ?? 'UNKNOWN']);
                    }
                }
            }

            // Email security analysis dari TXT records
            $txtRecords = array_filter($records, fn($r) => ($r['type'] ?? '') === 'TXT');
            $spf = null;
            $dkim = false;
            $dmarc = null;

            foreach ($txtRecords as $txt) {
                $val = $txt['txt'] ?? '';
                if (str_starts_with(strtolower($val), 'v=spf1')) {
                    $spf = $val;
                }
            }

            // Check DMARC
            $dmarcRecords = @dns_get_record("_dmarc.{$host}", DNS_TXT);
            if ($dmarcRecords) {
                foreach ($dmarcRecords as $dr) {
                    $val = $dr['txt'] ?? '';
                    if (str_starts_with(strtolower($val), 'v=dmarc1')) {
                        $dmarc = $val;
                    }
                }
            }

            // Check DKIM (common selectors)
            $dkimSelectors = ['default', 'google', 'k1', 's1', 'selector1', 'selector2', 'dkim', 'mail'];
            foreach ($dkimSelectors as $sel) {
                $dkimResult = @dns_get_record("{$sel}._domainkey.{$host}", DNS_TXT);
                if ($dkimResult && count($dkimResult) > 0) {
                    $dkim = true;
                    break;
                }
            }

            // CDN/WAF Detection dari CNAME
            $cdnWaf = $this->detectCDNFromRecords($records);

            // IP addresses
            $ips = array_values(array_unique(array_map(fn($r) => $r['ip'] ?? $r['ipv6'] ?? null, array_filter($records, fn($r) => in_array($r['type'] ?? '', ['A', 'AAAA'])))));
            $ips = array_filter($ips);

            // DNSSEC check
            $dnssec = $this->checkDNSSEC($host);

            // Nameservers
            $nameservers = array_values(array_unique(array_map(fn($r) => $r['target'] ?? null, array_filter($records, fn($r) => ($r['type'] ?? '') === 'NS'))));
            $nameservers = array_filter($nameservers);

            // MX records
            $mxRecords = array_map(fn($r) => ['host' => $r['target'] ?? '', 'priority' => $r['pri'] ?? 0], array_filter($records, fn($r) => ($r['type'] ?? '') === 'MX'));

            return [
                'ip_addresses'   => array_values($ips),
                'nameservers'    => array_values($nameservers),
                'mx_records'     => array_values($mxRecords),
                'spf'            => $spf,
                'dkim'           => $dkim,
                'dmarc'          => $dmarc,
                'dnssec'         => $dnssec,
                'cdn_waf'        => $cdnWaf,
                'total_records'  => count($records),
                'email_security' => [
                    'spf'   => !empty($spf),
                    'dkim'  => $dkim,
                    'dmarc' => !empty($dmarc),
                    'skor'  => (!empty($spf) ? 33 : 0) + ($dkim ? 34 : 0) + (!empty($dmarc) ? 33 : 0),
                ],
            ];
        } catch (\Exception $e) {
            Log::warning('DNS analysis gagal: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    protected function detectCDNFromRecords(array $records): array
    {
        $cdnPatterns = [
            'cloudflare'    => 'Cloudflare',
            'akamai'        => 'Akamai',
            'fastly'        => 'Fastly',
            'cloudfront'    => 'AWS CloudFront',
            'azureedge'     => 'Azure CDN',
            'googleapis'    => 'Google Cloud',
            'incapdns'      => 'Imperva/Incapsula',
            'sucuri'        => 'Sucuri WAF',
            'stackpath'     => 'StackPath',
            'edgekey'       => 'Akamai EdgeKey',
            'edgesuite'     => 'Akamai EdgeSuite',
            'awsglobalaccelerator' => 'AWS Global Accelerator',
        ];

        $detected = [];
        foreach ($records as $record) {
            $target = strtolower($record['target'] ?? $record['host'] ?? '');
            foreach ($cdnPatterns as $pattern => $name) {
                if (str_contains($target, $pattern)) {
                    $detected[] = $name;
                }
            }
        }

        return array_unique($detected);
    }

    protected function checkDNSSEC(string $host): bool
    {
        $dsRecords = @dns_get_record($host, DNS_ANY);
        if ($dsRecords) {
            foreach ($dsRecords as $r) {
                if (($r['type'] ?? '') === 'RRSIG' || ($r['type'] ?? '') === 'DNSKEY') {
                    return true;
                }
            }
        }
        return false;
    }

    // =========================================================================
    // MODUL 5: PORT SCANNING (Baru)
    // =========================================================================

    /**
     * Scan port-port kritis — 23 port umum.
     */
    public function scanCommonPorts(string $host): array
    {
        if (empty($host)) {
            return ['error' => 'Host kosong'];
        }

        $ports = [
            21   => ['service' => 'FTP',           'risiko' => 'tinggi',  'deskripsi' => 'File Transfer Protocol — sering tanpa enkripsi'],
            22   => ['service' => 'SSH',           'risiko' => 'sedang',  'deskripsi' => 'Secure Shell — aman tapi bisa brute-forced'],
            23   => ['service' => 'Telnet',        'risiko' => 'kritis',  'deskripsi' => 'Telnet — cleartext, sangat tidak aman'],
            25   => ['service' => 'SMTP',          'risiko' => 'sedang',  'deskripsi' => 'Email server — bisa digunakan untuk spam relay'],
            53   => ['service' => 'DNS',           'risiko' => 'rendah',  'deskripsi' => 'DNS server'],
            80   => ['service' => 'HTTP',          'risiko' => 'rendah',  'deskripsi' => 'Web server (tidak terenkripsi)'],
            110  => ['service' => 'POP3',          'risiko' => 'tinggi',  'deskripsi' => 'Email retrieval — cleartext'],
            143  => ['service' => 'IMAP',          'risiko' => 'tinggi',  'deskripsi' => 'Email access — cleartext'],
            443  => ['service' => 'HTTPS',         'risiko' => 'rendah',  'deskripsi' => 'Web server (terenkripsi)'],
            445  => ['service' => 'SMB',           'risiko' => 'kritis',  'deskripsi' => 'Server Message Block — target ransomware'],
            993  => ['service' => 'IMAPS',         'risiko' => 'rendah',  'deskripsi' => 'IMAP terenkripsi'],
            995  => ['service' => 'POP3S',         'risiko' => 'rendah',  'deskripsi' => 'POP3 terenkripsi'],
            1433 => ['service' => 'MSSQL',         'risiko' => 'kritis',  'deskripsi' => 'Microsoft SQL Server — seharusnya tidak publik'],
            3306 => ['service' => 'MySQL',         'risiko' => 'kritis',  'deskripsi' => 'MySQL database — seharusnya tidak publik'],
            3389 => ['service' => 'RDP',           'risiko' => 'kritis',  'deskripsi' => 'Remote Desktop — target utama serangan'],
            5432 => ['service' => 'PostgreSQL',    'risiko' => 'kritis',  'deskripsi' => 'PostgreSQL database — seharusnya tidak publik'],
            5900 => ['service' => 'VNC',           'risiko' => 'kritis',  'deskripsi' => 'Virtual Network Computing — remote desktop'],
            6379 => ['service' => 'Redis',         'risiko' => 'kritis',  'deskripsi' => 'Redis database — sering tanpa autentikasi'],
            8080 => ['service' => 'HTTP-Alt',      'risiko' => 'sedang',  'deskripsi' => 'Web server alternatif / proxy'],
            8443 => ['service' => 'HTTPS-Alt',     'risiko' => 'sedang',  'deskripsi' => 'HTTPS alternatif'],
            8888 => ['service' => 'HTTP-Alt-2',    'risiko' => 'sedang',  'deskripsi' => 'Web server alternatif / Jupyter'],
            9200 => ['service' => 'Elasticsearch', 'risiko' => 'kritis',  'deskripsi' => 'Elasticsearch — sering tanpa autentikasi'],
            27017 => ['service' => 'MongoDB',      'risiko' => 'kritis',  'deskripsi' => 'MongoDB — sering tanpa autentikasi'],
        ];

        $results = [];
        $openCount = 0;
        $criticalOpen = [];

        foreach ($ports as $port => $info) {
            $open = false;
            try {
                $socket = @fsockopen($host, $port, $errno, $errstr, 2);
                if ($socket) {
                    $open = true;
                    fclose($socket);
                }
            } catch (\Exception $e) {
                // Port closed or filtered
            }

            if ($open) {
                $openCount++;
                if (in_array($info['risiko'], ['kritis', 'tinggi'])) {
                    $criticalOpen[] = "{$port}/{$info['service']}";
                }
            }

            $results[] = [
                'port'      => $port,
                'service'   => $info['service'],
                'status'    => $open ? 'open' : 'closed',
                'risiko'    => $info['risiko'],
                'deskripsi' => $info['deskripsi'],
            ];
        }

        return [
            'ports'          => $results,
            'open_count'     => $openCount,
            'critical_open'  => $criticalOpen,
        ];
    }

    // =========================================================================
    // MODUL 6: COOKIE SECURITY ANALYSIS (Baru)
    // =========================================================================

    /**
     * Analisis keamanan cookies — Secure, HttpOnly, SameSite.
     */
    public function analyzeCookies(string $url): array
    {
        try {
            $response = Http::timeout(10)->withOptions([
                'verify' => false,
            ])->get($url);

            $setCookieHeaders = $response->header('Set-Cookie');
            if (empty($setCookieHeaders)) {
                // Coba ambil dari array headers
                $allHeaders = $response->headers();
                $setCookieHeaders = $allHeaders['set-cookie'] ?? $allHeaders['Set-Cookie'] ?? [];
            }

            if (is_string($setCookieHeaders)) {
                $setCookieHeaders = [$setCookieHeaders];
            }

            if (empty($setCookieHeaders)) {
                return ['cookies' => [], 'total' => 0, 'skor' => 100, 'masalah' => []];
            }

            $cookies = [];
            $masalah = [];
            $totalSkor = 0;

            foreach ($setCookieHeaders as $cookieStr) {
                $parts = explode(';', $cookieStr);
                $nameValue = trim($parts[0] ?? '');
                $eqPos = strpos($nameValue, '=');
                $name = $eqPos !== false ? trim(substr($nameValue, 0, $eqPos)) : $nameValue;

                $attributes = strtolower($cookieStr);
                $secure = str_contains($attributes, 'secure');
                $httpOnly = str_contains($attributes, 'httponly');
                $sameSite = null;
                if (preg_match('/samesite\s*=\s*(strict|lax|none)/i', $cookieStr, $m)) {
                    $sameSite = ucfirst(strtolower($m[1]));
                }

                // Path
                $path = '/';
                if (preg_match('/path\s*=\s*([^;]+)/i', $cookieStr, $m)) {
                    $path = trim($m[1]);
                }

                // Skor per cookie (0-100)
                $cookieSkor = 100;
                $cookieMasalah = [];

                if (!$secure) {
                    $cookieSkor -= 30;
                    $cookieMasalah[] = 'Flag Secure tidak diset — cookie dikirim via HTTP';
                    $masalah[] = "Cookie '{$name}': tidak memiliki flag Secure";
                }
                if (!$httpOnly) {
                    $cookieSkor -= 30;
                    $cookieMasalah[] = 'Flag HttpOnly tidak diset — rentan XSS cookie theft';
                    $masalah[] = "Cookie '{$name}': tidak memiliki flag HttpOnly";
                }
                if ($sameSite === null || $sameSite === 'None') {
                    $cookieSkor -= 20;
                    $cookieMasalah[] = 'SameSite tidak diset/None — rentan CSRF';
                    $masalah[] = "Cookie '{$name}': SameSite tidak protektif";
                }

                // Deteksi session cookie tanpa proteksi
                $isSessionCookie = preg_match('/(session|sess|sid|token|auth|jwt|csrf)/i', $name);
                if ($isSessionCookie && (!$secure || !$httpOnly)) {
                    $cookieSkor -= 20;
                    $cookieMasalah[] = 'Session cookie tanpa proteksi penuh — risiko tinggi';
                    $masalah[] = "⚠️ Session cookie '{$name}' tanpa proteksi lengkap";
                }

                $cookieSkor = max(0, $cookieSkor);
                $totalSkor += $cookieSkor;

                $cookies[] = [
                    'name'            => $name,
                    'secure'          => $secure,
                    'httponly'        => $httpOnly,
                    'samesite'        => $sameSite,
                    'path'            => $path,
                    'is_session'      => (bool) $isSessionCookie,
                    'skor'            => $cookieSkor,
                    'masalah'         => $cookieMasalah,
                ];
            }

            $avgSkor = count($cookies) > 0 ? (int) round($totalSkor / count($cookies)) : 100;

            return [
                'cookies' => $cookies,
                'total'   => count($cookies),
                'skor'    => $avgSkor,
                'masalah' => $masalah,
            ];
        } catch (\Exception $e) {
            Log::warning('Cookie analysis gagal: ' . $e->getMessage());
            return ['cookies' => [], 'total' => 0, 'skor' => 100, 'masalah' => [], 'error' => $e->getMessage()];
        }
    }

    // =========================================================================
    // MODUL 7: CORS MISCONFIGURATION DETECTION (Baru)
    // =========================================================================

    /**
     * Deteksi miskonfigurasi CORS — arbitrary origin reflection, credentials.
     */
    public function analyzeCORS(string $url): array
    {
        try {
            $testOrigins = [
                'https://evil.com',
                'https://attacker.example.com',
                'null',
            ];

            $results = [];
            $vulnerable = false;
            $masalah = [];

            foreach ($testOrigins as $origin) {
                $response = Http::timeout(10)->withOptions([
                    'verify' => false,
                ])->withHeaders([
                    'Origin' => $origin,
                ])->get($url);

                $acao = $response->header('Access-Control-Allow-Origin');
                $acac = $response->header('Access-Control-Allow-Credentials');
                $acam = $response->header('Access-Control-Allow-Methods');
                $acah = $response->header('Access-Control-Allow-Headers');

                $reflected = $acao === $origin;
                $wildcardOrigin = $acao === '*';
                $credentialsAllowed = strtolower($acac ?? '') === 'true';

                $result = [
                    'test_origin'           => $origin,
                    'allow_origin'          => $acao,
                    'allow_credentials'     => $credentialsAllowed,
                    'allow_methods'         => $acam,
                    'allow_headers'         => $acah,
                    'origin_reflected'      => $reflected,
                    'wildcard_origin'       => $wildcardOrigin,
                ];

                if ($reflected) {
                    $vulnerable = true;
                    $masalah[] = "Server merefleksikan arbitrary origin '{$origin}' — rentan CORS exploitation";
                    if ($credentialsAllowed) {
                        $masalah[] = "⚠️ KRITIS: Origin reflected + Allow-Credentials:true — data pengguna bisa dicuri cross-origin";
                    }
                }

                if ($wildcardOrigin && $credentialsAllowed) {
                    $vulnerable = true;
                    $masalah[] = "⚠️ Wildcard origin (*) dengan credentials — miskonfigurasi serius";
                }

                if ($acao === 'null') {
                    $vulnerable = true;
                    $masalah[] = "Server menerima 'null' origin — bisa dieksploitasi via sandboxed iframe";
                }

                $results[] = $result;
            }

            return [
                'tests'      => $results,
                'vulnerable' => $vulnerable,
                'masalah'    => array_unique($masalah),
                'skor'       => $vulnerable ? 0 : 100,
            ];
        } catch (\Exception $e) {
            Log::warning('CORS analysis gagal: ' . $e->getMessage());
            return ['tests' => [], 'vulnerable' => false, 'masalah' => [], 'skor' => 100, 'error' => $e->getMessage()];
        }
    }

    // =========================================================================
    // MODUL 8: HTTP METHOD ENUMERATION (Baru)
    // =========================================================================

    /**
     * Enumerasi HTTP methods yang diizinkan server.
     */
    public function enumerateHTTPMethods(string $url): array
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD', 'TRACE'];
        $dangerousMethods = ['TRACE' => 'Cross-Site Tracing (XST)', 'PUT' => 'File upload tanpa autentikasi', 'DELETE' => 'File deletion tanpa autentikasi'];

        $results = [];
        $allowedMethods = [];
        $masalah = [];

        foreach ($methods as $method) {
            try {
                $response = Http::timeout(5)->withOptions([
                    'verify' => false,
                ])->send($method, $url);

                $status = $response->status();
                $allowed = !in_array($status, [405, 501]);

                if ($method === 'OPTIONS') {
                    $allowHeader = $response->header('Allow');
                    if ($allowHeader) {
                        $serverAllowed = array_map('trim', explode(',', $allowHeader));
                        foreach ($serverAllowed as $sm) {
                            if (!in_array(strtoupper($sm), $allowedMethods)) {
                                $allowedMethods[] = strtoupper($sm);
                            }
                        }
                    }
                }

                if ($allowed) {
                    $allowedMethods[] = $method;
                }

                $isDangerous = isset($dangerousMethods[$method]) && $allowed;
                if ($isDangerous) {
                    $masalah[] = "Method {$method} diizinkan — {$dangerousMethods[$method]}";
                }

                $results[] = [
                    'method'     => $method,
                    'status'     => $status,
                    'allowed'    => $allowed,
                    'berbahaya'  => $isDangerous,
                    'deskripsi'  => $dangerousMethods[$method] ?? null,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'method'    => $method,
                    'status'    => 0,
                    'allowed'   => false,
                    'berbahaya' => false,
                    'error'     => 'Timeout',
                ];
            }
        }

        $allowedMethods = array_unique($allowedMethods);

        return [
            'methods'         => $results,
            'allowed_methods' => array_values($allowedMethods),
            'masalah'         => $masalah,
            'skor'            => empty($masalah) ? 100 : max(0, 100 - count($masalah) * 30),
        ];
    }

    // =========================================================================
    // MODUL 9: REDIRECT CHAIN ANALYSIS (Baru)
    // =========================================================================

    /**
     * Analisis rantai redirect — deteksi open redirect & mixed content.
     */
    public function analyzeRedirects(string $url): array
    {
        try {
            $chain = [];
            $currentUrl = $url;
            $maxRedirects = 10;
            $httpToHttps = false;
            $httpsToHttp = false;
            $masalah = [];

            for ($i = 0; $i < $maxRedirects; $i++) {
                $response = Http::timeout(5)->withOptions([
                    'verify' => false,
                    'allow_redirects' => false,
                ])->get($currentUrl);

                $statusCode = $response->status();
                $location = $response->header('Location');

                $chain[] = [
                    'url'    => $currentUrl,
                    'status' => $statusCode,
                    'next'   => $location,
                ];

                if (!$location || !in_array($statusCode, [301, 302, 303, 307, 308])) {
                    break;
                }

                // Resolve relative URLs
                if (!str_starts_with($location, 'http')) {
                    $parsed = parse_url($currentUrl);
                    $location = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . $location;
                }

                // Check HTTP → HTTPS upgrade
                $currentScheme = parse_url($currentUrl, PHP_URL_SCHEME);
                $nextScheme = parse_url($location, PHP_URL_SCHEME);
                if ($currentScheme === 'http' && $nextScheme === 'https') {
                    $httpToHttps = true;
                }
                if ($currentScheme === 'https' && $nextScheme === 'http') {
                    $httpsToHttp = true;
                    $masalah[] = "Redirect dari HTTPS ke HTTP — downgrade attack, data bisa disadap";
                }

                // Check open redirect (redirect ke domain berbeda)
                $currentHost = parse_url($currentUrl, PHP_URL_HOST);
                $nextHost = parse_url($location, PHP_URL_HOST);
                if ($currentHost !== $nextHost) {
                    $masalah[] = "Redirect ke domain berbeda: {$currentHost} → {$nextHost}";
                }

                $currentUrl = $location;
            }

            // Check if original URL has HTTP → HTTPS redirect
            $usesHttpUpgrade = false;
            if (str_starts_with($url, 'https://')) {
                $httpVersion = str_replace('https://', 'http://', $url);
                try {
                    $httpResponse = Http::timeout(5)->withOptions([
                        'verify' => false,
                        'allow_redirects' => false,
                    ])->get($httpVersion);
                    $httpLocation = $httpResponse->header('Location');
                    $usesHttpUpgrade = $httpLocation && str_starts_with($httpLocation, 'https://');
                } catch (\Exception $e) {
                    // Tidak bisa cek
                }
            }

            return [
                'chain'             => $chain,
                'total_redirects'   => max(0, count($chain) - 1),
                'http_to_https'     => $httpToHttps,
                'https_to_http'     => $httpsToHttp,
                'uses_http_upgrade' => $usesHttpUpgrade,
                'masalah'           => $masalah,
                'skor'              => empty($masalah) && !$httpsToHttp ? 100 : max(0, 100 - count($masalah) * 25 - ($httpsToHttp ? 30 : 0)),
            ];
        } catch (\Exception $e) {
            Log::warning('Redirect analysis gagal: ' . $e->getMessage());
            return ['chain' => [], 'total_redirects' => 0, 'masalah' => [], 'skor' => 100, 'error' => $e->getMessage()];
        }
    }

    // =========================================================================
    // MODUL 10: SUBDOMAIN ENUMERATION VIA CRT.SH (Baru)
    // =========================================================================

    /**
     * Subdomain enumeration via Certificate Transparency logs (crt.sh).
     */
    public function enumerateSubdomains(string $host): array
    {
        if (empty($host)) {
            return ['subdomains' => [], 'total' => 0];
        }

        try {
            // Ambil base domain (menghapus www. dll)
            $baseDomain = preg_replace('/^(www\.)+/i', '', $host);

            $response = Http::timeout(15)->get("https://crt.sh/?q=%.{$baseDomain}&output=json");

            if (!$response->successful()) {
                return ['subdomains' => [], 'total' => 0, 'error' => 'crt.sh tidak merespons'];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return ['subdomains' => [], 'total' => 0, 'error' => 'Format respons tidak valid'];
            }

            $subdomains = [];
            foreach ($data as $entry) {
                $names = explode("\n", $entry['name_value'] ?? '');
                foreach ($names as $name) {
                    $name = strtolower(trim($name));
                    // Bersihkan wildcard
                    $name = ltrim($name, '*.');
                    if (!empty($name) && str_ends_with($name, $baseDomain) && $name !== $baseDomain) {
                        $subdomains[$name] = [
                            'name'         => $name,
                            'first_seen'   => $entry['entry_timestamp'] ?? null,
                            'issuer'       => $entry['issuer_name'] ?? null,
                        ];
                    }
                }
            }

            // Sort & limit
            $subdomains = array_values($subdomains);
            usort($subdomains, fn($a, $b) => strcmp($a['name'], $b['name']));
            $subdomains = array_slice($subdomains, 0, 50);

            // Kategorisasi subdomain
            $interesting = [];
            $interestingPatterns = [
                'dev', 'staging', 'stage', 'test', 'qa', 'uat', 'demo',
                'admin', 'panel', 'dashboard', 'internal', 'vpn', 'api',
                'mail', 'webmail', 'ftp', 'git', 'gitlab', 'jenkins',
                'ci', 'cd', 'jira', 'confluence', 'grafana', 'kibana',
                'monitoring', 'db', 'database', 'backup', 'old', 'legacy',
            ];

            foreach ($subdomains as &$sub) {
                $sub['menarik'] = false;
                foreach ($interestingPatterns as $pattern) {
                    if (str_contains($sub['name'], $pattern)) {
                        $sub['menarik'] = true;
                        $interesting[] = $sub['name'];
                        break;
                    }
                }
            }

            return [
                'subdomains'  => $subdomains,
                'total'       => count($subdomains),
                'interesting' => array_unique($interesting),
            ];
        } catch (\Exception $e) {
            Log::warning('Subdomain enumeration gagal: ' . $e->getMessage());
            return ['subdomains' => [], 'total' => 0, 'error' => $e->getMessage()];
        }
    }

    // =========================================================================
    // BUILD REPORT — Scoring & Aggregation
    // =========================================================================

    protected function buildReport(
        string $url, array $vtData, array $urlscanData, array $headerData,
        array $sslData, array $commonFiles, array $dnsData, array $portData,
        array $cookieData, array $corsData, array $httpMethods,
        array $redirectData, array $subdomains
    ): array {
        // === SKOR REPUTASI (Granular) ===

        // 1. VirusTotal Score (30%)
        $vtStats = $vtData['last_analysis_stats'] ?? [];
        $malicious = $vtStats['malicious'] ?? 0;
        $suspicious = $vtStats['suspicious'] ?? 0;
        $total = array_sum($vtStats) ?: 1;
        $vtScore = max(0, 100 - (($malicious * 3 + $suspicious) / $total * 100));

        // 2. Header Score (20%) — sudah dinormalisasi ke 100
        $headerScore = $headerData['skor'] ?? 0;

        // 3. SSL Score (10%)
        $sslScore = 0;
        if (($sslData['valid'] ?? false)) {
            $sslScore = 100;
            if ($sslData['weak_protocol'] ?? false) $sslScore -= 40;
            if ($sslData['weak_cipher'] ?? false) $sslScore -= 30;
            if ($sslData['weak_signature'] ?? false) $sslScore -= 20;
            if (($sslData['days_until_expiry'] ?? 365) < 30) $sslScore -= 10;
        }

        // 4. Cookie Score (10%)
        $cookieScore = $cookieData['skor'] ?? 100;

        // 5. CORS Score (10%)
        $corsScore = $corsData['skor'] ?? 100;

        // 6. HTTP Methods Score (5%)
        $httpMethodScore = $httpMethods['skor'] ?? 100;

        // 7. Redirect Score (5%)
        $redirectScore = $redirectData['skor'] ?? 100;

        // Penalties
        $exposedSensitive = array_filter($commonFiles, fn($f) => ($f['terekspos'] ?? false) && in_array($f['risiko'], ['kritis', 'tinggi']));
        $exposedPenalty = count($exposedSensitive) * 5;

        $criticalPorts = $portData['critical_open'] ?? [];
        $portPenalty = count($criticalPorts) * 3;

        // DNS Email Security Bonus
        $emailSecurityBonus = 0;
        if (!empty($dnsData['email_security'])) {
            $emailSkor = $dnsData['email_security']['skor'] ?? 0;
            if ($emailSkor >= 100) $emailSecurityBonus = 5;
            elseif ($emailSkor >= 66) $emailSecurityBonus = 3;
        }

        // Hitung skor akhir
        $skorReputasi = (int) round(
            ($vtScore * 0.30) +
            ($headerScore * 0.20) +
            ($sslScore * 0.10) +
            ($cookieScore * 0.10) +
            ($corsScore * 0.10) +
            ($httpMethodScore * 0.05) +
            ($redirectScore * 0.05)
            + $emailSecurityBonus
            - $exposedPenalty
            - $portPenalty
        );
        $skorReputasi = min(100, max(0, $skorReputasi));

        // === STATUS ===
        if ($malicious > 3 || count($exposedSensitive) >= 3 || ($corsData['vulnerable'] ?? false && count($criticalPorts) > 2)) {
            $status = 'berbahaya';
        } elseif ($malicious > 0 || $suspicious > 1 || count($exposedSensitive) >= 1 || ($corsData['vulnerable'] ?? false) || count($criticalPorts) > 0) {
            $status = 'mencurigakan';
        } else {
            $status = 'aman';
        }

        // === TEKNOLOGI ===
        $teknologi = [];
        $pageInfo = $urlscanData['page'] ?? [];
        if (!empty($pageInfo['server'])) $teknologi[] = $pageInfo['server'];
        if (!empty($pageInfo['asnname'])) $teknologi[] = 'ASN: ' . $pageInfo['asnname'];

        foreach ($urlscanData['meta']['processors'] ?? [] as $proc) {
            foreach ($proc['data'] ?? [] as $item) {
                if (!empty($item['app'])) $teknologi[] = $item['app'];
            }
        }

        // Tambahkan CDN/WAF dari DNS
        foreach ($dnsData['cdn_waf'] ?? [] as $cdn) {
            $teknologi[] = "CDN/WAF: {$cdn}";
        }

        $teknologi = array_unique(array_slice($teknologi, 0, 30));

        // === SERVER INFO ===
        $serverInfo = [
            'ip'      => $pageInfo['ip'] ?? ($dnsData['ip_addresses'][0] ?? null),
            'asn'     => $pageInfo['asn'] ?? null,
            'asnname' => $pageInfo['asnname'] ?? null,
            'country' => $pageInfo['country'] ?? null,
            'server'  => $pageInfo['server'] ?? null,
        ];

        // === RESOURCES ===
        $resources = [];
        foreach (array_slice($urlscanData['data']['requests'] ?? [], 0, 50) as $req) {
            $r = $req['request'] ?? [];
            $resp = $req['response'] ?? [];
            $resources[] = [
                'url'    => $r['request']['url'] ?? '',
                'method' => $r['request']['method'] ?? 'GET',
                'type'   => $resp['response']['mimeType'] ?? '',
                'status' => $resp['response']['status'] ?? 0,
                'size'   => $resp['dataLength'] ?? 0,
            ];
        }

        // External links
        $links = array_slice($urlscanData['data']['links'] ?? [], 0, 30);

        // Console messages
        $consoleMessages = array_slice($urlscanData['data']['console'] ?? [], 0, 10);

        // Screenshot
        $screenshotUrl = null;
        if (!empty($urlscanData['task']['uuid'])) {
            $screenshotUrl = $this->urlScanService->getScreenshotUrl($urlscanData['task']['uuid']);
        }

        // === SKOR BREAKDOWN ===
        $skorBreakdown = [
            'virustotal'     => ['skor' => (int) round($vtScore), 'bobot' => '30%'],
            'headers'        => ['skor' => $headerScore, 'bobot' => '20%'],
            'ssl'            => ['skor' => $sslScore, 'bobot' => '10%'],
            'cookies'        => ['skor' => $cookieScore, 'bobot' => '10%'],
            'cors'           => ['skor' => $corsScore, 'bobot' => '10%'],
            'http_methods'   => ['skor' => $httpMethodScore, 'bobot' => '5%'],
            'redirects'      => ['skor' => $redirectScore, 'bobot' => '5%'],
            'email_security' => ['bonus' => $emailSecurityBonus],
            'exposed_files'  => ['penalty' => -$exposedPenalty],
            'open_ports'     => ['penalty' => -$portPenalty],
        ];

        return [
            'skor_reputasi'    => $skorReputasi,
            'skor_breakdown'   => $skorBreakdown,
            'status'           => $status,
            'ssl'              => $sslData,
            'security_headers' => $headerData,
            'virustotal' => [
                'stats'           => $vtStats,
                'reputation'      => $vtData['reputation'] ?? 0,
                'total_votes'     => $vtData['total_votes'] ?? [],
                'categories'      => $vtData['categories'] ?? [],
                'malicious_count' => $malicious,
                'total_engines'   => $total,
            ],
            'server_info'       => $serverInfo,
            'teknologi'         => $teknologi,
            'resources'         => $resources,
            'external_links'    => $links,
            'console_messages'  => $consoleMessages,
            'common_files'      => $commonFiles,
            'dns_recon'         => $dnsData,
            'port_scan'         => $portData,
            'cookie_security'   => $cookieData,
            'cors_config'       => $corsData,
            'http_methods'      => $httpMethods,
            'redirect_chain'    => $redirectData,
            'subdomains'        => $subdomains,
            'screenshot_url'    => $screenshotUrl,
        ];
    }
}
