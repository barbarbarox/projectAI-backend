<?php

namespace App\Console\Commands;

use App\Services\NVDPineconeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IngestNVDCVE
 *
 * Artisan command untuk mendownload dan meng-ingest data NVD CVE (2024–2026)
 * dari NVD API v2.0 ke Pinecone index `redsim-nvd-cve`.
 *
 * Penggunaan:
 *   php artisan nvd:ingest                         → Download semua tahun (2024–2026)
 *   php artisan nvd:ingest --year=2025             → Hanya tahun 2025
 *   php artisan nvd:ingest --year=2026 --severity=CRITICAL  → Filter CRITICAL saja
 *   php artisan nvd:ingest --dry-run               → Hanya hitung, tidak upload
 *   php artisan nvd:ingest --resume                → Lanjut dari CVE terakhir yang gagal
 *
 * NVD API v2.0 Docs: https://nvd.nist.gov/developers/vulnerabilities
 */
class IngestNVDCVE extends Command
{
    protected $signature = 'nvd:ingest
                            {--year=* : Tahun CVE yang di-download (default: 2024 2025 2026)}
                            {--severity= : Filter severity (CRITICAL, HIGH, MEDIUM, LOW)}
                            {--batch-size=50 : Jumlah CVE per batch upload ke Pinecone}
                            {--start-date= : Tanggal mulai (format: YYYY-MM-DD)}
                            {--end-date= : Tanggal akhir (format: YYYY-MM-DD)}
                            {--dry-run : Hanya download dan hitung, tidak upload ke Pinecone}
                            {--resume : Lanjut dari progress terakhir (skip CVE yang sudah diupload)}';

    protected $description = 'Download dan ingest NVD CVE (2024–2026) ke Pinecone index redsim-nvd-cve';

    // NVD API v2.0
    private const NVD_API_BASE = 'https://services.nvd.nist.gov/rest/json/cves/2.0';
    private const RESULTS_PER_PAGE = 2000;  // Max per request NVD API
    private const REQUEST_DELAY_MS = 600;   // Delay antar request (NVD rate limit: ~50 req/30s tanpa key)

    private string $apiKey;
    private int    $totalIngested = 0;
    private int    $totalFailed   = 0;

    public function __construct(protected NVDPineconeService $nvdPinecone)
    {
        parent::__construct();
        $this->apiKey = env('NVD_API_KEY', '');
    }

    public function handle(): int
    {
        $this->displayBanner();

        // Tentukan tahun yang akan didownload
        $years = $this->option('year');
        if (empty($years)) {
            $years = ['2024', '2025', '2026'];
        }

        $severity = $this->option('severity');
        $batchSize = (int) $this->option('batch-size');
        $isDryRun  = $this->option('dry-run');
        $isResume  = $this->option('resume');

        // Validasi koneksi Pinecone (skip jika dry-run)
        if (!$isDryRun) {
            $this->info('🔗 Memeriksa koneksi Pinecone NVD index...');
            $health = $this->nvdPinecone->healthCheck();
            if ($health['status'] !== 'ok') {
                $this->error("❌ Pinecone NVD index tidak tersedia: " . $health['message']);
                $this->error('   Buat index "redsim-nvd-cve" di dashboard Pinecone terlebih dahulu.');
                $this->error('   Spesifikasi: Dimensi 768, Metric cosine, Serverless');
                return self::FAILURE;
            }
            $this->line("   ✅ " . $health['message']);
        }

        $this->newLine();

        foreach ($years as $year) {
            $this->info("📅 Memproses CVE tahun: {$year}");
            $this->processYear((string)$year, $severity, $batchSize, $isDryRun, $isResume);
            $this->newLine();
        }

        // Ringkasan akhir
        $this->displaySummary($isDryRun);

        return self::SUCCESS;
    }

    /**
     * Proses satu tahun CVE.
     */
    private function processYear(
        string  $year,
        ?string $severityFilter,
        int     $batchSize,
        bool    $isDryRun,
        bool    $isResume
    ): void {
        // NVD API v2.0 membutuhkan format: YYYY-MM-DDTHH:MM:SS.000 +00:00
        $startDate = "{$year}-01-01T00:00:00.000 +00:00";
        $endDate   = "{$year}-12-31T23:59:59.000 +00:00";

        // Override jika --start-date/--end-date diset
        if ($this->option('start-date') && $year === date('Y', strtotime($this->option('start-date')))) {
            $startDate = $this->option('start-date') . 'T00:00:00.000 +00:00';
        }
        if ($this->option('end-date') && $year === date('Y', strtotime($this->option('end-date')))) {
            $endDate = $this->option('end-date') . 'T23:59:59.000 +00:00';
        }

        $startIndex = 0;
        $totalResults = null;

        do {
            $this->line("   📥 Mengambil halaman offset={$startIndex}...");

            $response = $this->fetchNVDPage($startDate, $endDate, $startIndex, $severityFilter);

            if ($response === null) {
                $this->error("   ❌ Gagal mengambil data NVD untuk tahun {$year} (offset={$startIndex})");
                break;
            }

            if ($totalResults === null) {
                $totalResults = $response['totalResults'] ?? 0;
                $this->line("   📊 Total CVE ditemukan: " . number_format($totalResults));
            }

            $vulnerabilities = $response['vulnerabilities'] ?? [];
            if (empty($vulnerabilities)) break;

            // Proses dan format CVEs
            $chunks = $this->formatCVEs($vulnerabilities);

            if ($isDryRun) {
                $this->line("   [DRY RUN] Akan upload " . count($chunks) . " CVE chunks.");
            } else {
                // Upload ke Pinecone dalam batch
                $this->uploadBatch($chunks, $batchSize, $year);
            }

            $startIndex += count($vulnerabilities);

            // Progress bar
            if ($totalResults > 0) {
                $pct = min(100, round(($startIndex / $totalResults) * 100));
                $this->line("   ⏳ Progress: {$startIndex}/{$totalResults} ({$pct}%)");
            }

            // Rate limiting NVD API
            usleep(self::REQUEST_DELAY_MS * 1000);

        } while ($totalResults !== null && $startIndex < $totalResults);

        $this->info("   ✅ Selesai tahun {$year}: {$this->totalIngested} CVE berhasil diingest.");
    }

    /**
     * Ambil satu halaman CVE dari NVD API v2.0.
     */
    private function fetchNVDPage(
        string  $startDate,
        string  $endDate,
        int     $startIndex,
        ?string $severityFilter
    ): ?array {
        $params = [
            'pubStartDate' => $startDate,
            'pubEndDate'   => $endDate,
            'startIndex'   => $startIndex,
            'resultsPerPage' => self::RESULTS_PER_PAGE,
        ];

        if ($severityFilter) {
            $params['cvssV3Severity'] = strtoupper($severityFilter);
        }

        try {
            $headers = ['Accept' => 'application/json'];
            if (!empty($this->apiKey)) {
                $headers['apiKey'] = $this->apiKey;
            }

            // Bangun URL manual agar tanggal dengan '+00:00' tidak ter-encode ganda oleh Http client
            $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
            $url = self::NVD_API_BASE . '?' . $queryString;

            $response = Http::timeout(60)
                ->retry(3, 2000)
                ->withHeaders($headers)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            $this->error("   NVD API Error: HTTP {$response->status()} - " . substr($response->body(), 0, 200));
            return null;
        } catch (\Exception $e) {
            $this->error("   NVD API Exception: " . $e->getMessage());
            Log::error('[IngestNVDCVE] Fetch failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Format array CVE dari NVD API menjadi array chunks siap upload ke Pinecone.
     *
     * @param array $vulnerabilities Array dari NVD API
     * @return array Array of ['id', 'text', 'metadata'] untuk Pinecone
     */
    private function formatCVEs(array $vulnerabilities): array
    {
        $chunks = [];

        foreach ($vulnerabilities as $vuln) {
            $cve  = $vuln['cve'] ?? [];
            $cveId = $cve['id'] ?? null;

            if (!$cveId) continue;

            // Ekstrak metadata
            $published  = substr($cve['published'] ?? '', 0, 10);
            $modified   = substr($cve['lastModified'] ?? '', 0, 10);
            $status     = $cve['vulnStatus'] ?? 'N/A';

            // Deskripsi (ambil yang Bahasa Inggris)
            $description = '';
            foreach ($cve['descriptions'] ?? [] as $desc) {
                if ($desc['lang'] === 'en') {
                    $description = $desc['value'] ?? '';
                    break;
                }
            }

            // CVSS v3.x Severity
            $severity  = 'NONE';
            $cvssScore = null;
            $cvssVector = null;
            $metrics = $cve['metrics'] ?? [];
            foreach (['cvssMetricV31', 'cvssMetricV30'] as $metricKey) {
                if (!empty($metrics[$metricKey][0])) {
                    $m          = $metrics[$metricKey][0];
                    $cvssData   = $m['cvssData'] ?? [];
                    $severity   = $cvssData['baseSeverity']  ?? $m['baseSeverity']  ?? 'NONE';
                    $cvssScore  = $cvssData['baseScore']     ?? $m['baseScore']     ?? null;
                    $cvssVector = $cvssData['vectorString']  ?? null;
                    break;
                }
            }

            // CWE
            $cweIds = [];
            foreach ($cve['weaknesses'] ?? [] as $weakness) {
                foreach ($weakness['description'] ?? [] as $d) {
                    if ($d['lang'] === 'en' && !empty($d['value']) && $d['value'] !== 'NVD-CWE-Other') {
                        $cweIds[] = $d['value'];
                    }
                }
            }
            $cweStr = implode(', ', array_unique($cweIds)) ?: 'N/A';

            // Affected Products / CPE (max 5)
            $affectedProducts = [];
            foreach ($cve['configurations'] ?? [] as $config) {
                foreach ($config['nodes'] ?? [] as $node) {
                    foreach ($node['cpeMatch'] ?? [] as $cpe) {
                        if ($cpe['vulnerable'] ?? false) {
                            $affectedProducts[] = $cpe['criteria'] ?? '';
                        }
                    }
                }
            }
            $affectedProducts = array_unique($affectedProducts);
            $productsStr = implode('; ', array_slice($affectedProducts, 0, 5));
            if (count($affectedProducts) > 5) {
                $productsStr .= ' (dan ' . (count($affectedProducts) - 5) . ' lainnya)';
            }

            // References (max 3)
            $refs = [];
            foreach ($cve['references'] ?? [] as $ref) {
                $refs[] = $ref['url'] ?? '';
                if (count($refs) >= 3) break;
            }
            $refsStr = implode(', ', array_filter($refs));

            // Build teks chunk untuk embedding (full context)
            $text = $this->buildCVEText(
                cveId: $cveId,
                severity: $severity,
                cvssScore: $cvssScore,
                cvssVector: $cvssVector,
                cweStr: $cweStr,
                published: $published,
                modified: $modified,
                description: $description,
                productsStr: $productsStr,
                refsStr: $refsStr
            );

            $chunks[] = [
                'id'   => strtolower(str_replace('-', '_', $cveId)),
                'text' => $text,
                'metadata' => [
                    'cve_id'     => $cveId,
                    'severity'   => strtoupper($severity),
                    'cvss_score' => $cvssScore ? (float) $cvssScore : null,
                    'cwe_id'     => $cweStr,
                    'published'  => $published,
                    'source'     => 'nvd-cve',
                ],
            ];
        }

        return $chunks;
    }

    /**
     * Build teks chunk CVE yang kaya untuk embedding.
     */
    private function buildCVEText(
        string  $cveId,
        string  $severity,
        ?float  $cvssScore,
        ?string $cvssVector,
        string  $cweStr,
        string  $published,
        string  $modified,
        string  $description,
        string  $productsStr,
        string  $refsStr
    ): string {
        $cvssInfo = $cvssScore !== null
            ? "CVSS Score: {$cvssScore}" . ($cvssVector ? " ({$cvssVector})" : '')
            : "CVSS: Not Available";

        $productsInfo = $productsStr ? "\nAffected Products: {$productsStr}" : '';
        $refsInfo     = $refsStr ? "\nReferences: {$refsStr}" : '';

        return <<<TEXT
CVE ID: {$cveId}
Severity: {$severity}
{$cvssInfo}
CWE: {$cweStr}
Published: {$published} | Last Modified: {$modified}
Description: {$description}{$productsInfo}{$refsInfo}
TEXT;
    }

    /**
     * Upload chunks ke Pinecone dalam batch.
     */
    private function uploadBatch(array $chunks, int $batchSize, string $year): void
    {
        foreach (array_chunk($chunks, $batchSize) as $batchIdx => $batch) {
            $success = $this->nvdPinecone->upsertText($batch);
            if ($success) {
                $this->totalIngested += count($batch);
                $this->line("   ✅ Batch " . ($batchIdx + 1) . ": " . count($batch) . " CVE diunggah.");
            } else {
                $this->totalFailed += count($batch);
                $this->error("   ❌ Batch " . ($batchIdx + 1) . ": Gagal upload " . count($batch) . " CVE.");
                Log::error("[IngestNVDCVE] Batch gagal. Year={$year}, BatchIdx={$batchIdx}");
            }
            // Rate limit Pinecone
            usleep(200000); // 200ms
        }
    }

    private function displayBanner(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║        NVD CVE Ingestion → Pinecone redsim-nvd-cve  ║');
        $this->info('║        NVD API v2.0 + Pinecone Integrated Embeddings ║');
        $this->info('╚══════════════════════════════════════════════════════╝');
        $this->newLine();

        $apiKeyStatus = !empty($this->apiKey) ? '✅ Tersedia' : '⚠️ Tidak ada (rate limit ketat)';
        $this->line("   NVD API Key: {$apiKeyStatus}");
        $this->line("   Pinecone Index Target: " . env('PINECONE_NVD_INDEX', 'redsim-nvd-cve'));
        $this->newLine();
    }

    private function displaySummary(bool $isDryRun): void
    {
        $this->info('═══════════════════════════════════════');
        if ($isDryRun) {
            $this->info('  [DRY RUN] — Tidak ada data yang diupload.');
        } else {
            $this->info("  ✅ Total berhasil diingest : {$this->totalIngested} CVE");
            if ($this->totalFailed > 0) {
                $this->warn("  ⚠️  Total gagal           : {$this->totalFailed} CVE");
            }
        }
        $this->info('═══════════════════════════════════════');
    }
}
