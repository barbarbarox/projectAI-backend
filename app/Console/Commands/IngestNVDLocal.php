<?php

namespace App\Console\Commands;

use App\Services\NVDPineconeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * IngestNVDLocal
 *
 * Ingest dataset NVD CVE dari file JSON lokal ke Pinecone index `redsim-nvd-cve`.
 * Jauh lebih cepat dari API karena data sudah ada di disk.
 *
 * Format file: nvdcve-2.0-{year}.json/nvdcve-2.0-{year}.json
 * Lokasi     : NVD_DATASET_PATH di .env
 *
 * Penggunaan:
 *   php artisan nvd:ingest-local --year=2024
 *   php artisan nvd:ingest-local --year=2025
 *   php artisan nvd:ingest-local --year=2026
 *   php artisan nvd:ingest-local             → semua tahun (2024–2026)
 *   php artisan nvd:ingest-local --severity=CRITICAL --year=2024
 *   php artisan nvd:ingest-local --dry-run --year=2024
 */
class IngestNVDLocal extends Command
{
    protected $signature = 'nvd:ingest-local
                            {--year=* : Tahun CVE (default: 2024 2025 2026)}
                            {--severity= : Filter severity (CRITICAL, HIGH, MEDIUM, LOW)}
                            {--batch-size=50 : Jumlah records per batch ke Pinecone}
                            {--dry-run : Hitung saja tanpa upload}
                            {--skip-existing : Skip jika ada error upload, lanjut batch berikutnya}';

    protected $description = 'Ingest NVD CVE dari file JSON lokal ke Pinecone redsim-nvd-cve';

    private int $totalIngested = 0;
    private int $totalSkipped  = 0;
    private int $totalFailed   = 0;

    public function __construct(protected NVDPineconeService $nvdPinecone)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->displayBanner();

        $years     = $this->option('year') ?: ['2024', '2025', '2026'];
        $severity  = $this->option('severity') ? strtoupper($this->option('severity')) : null;
        $batchSize = (int) $this->option('batch-size');
        $isDryRun  = (bool) $this->option('dry-run');

        $datasetPath = env('NVD_DATASET_PATH', base_path('../../matkul-kecerdasan-buatan/dataset'));

        // Cek Pinecone
        if (!$isDryRun) {
            $this->info('🔗 Memeriksa koneksi Pinecone NVD index...');
            try {
                $health = $this->nvdPinecone->healthCheck();
                if ($health['status'] !== 'ok') {
                    $this->error("❌ " . $health['message']);
                    return self::FAILURE;
                }
                $this->line("   ✅ " . $health['message']);
            } catch (\Exception $e) {
                $this->error("❌ Pinecone error: " . $e->getMessage());
                return self::FAILURE;
            }
        }

        $this->newLine();

        foreach ($years as $year) {
            $filePath = "{$datasetPath}/nvdcve-2.0-{$year}.json/nvdcve-2.0-{$year}.json";

            if (!file_exists($filePath)) {
                $this->error("❌ File tidak ditemukan: {$filePath}");
                continue;
            }

            $this->info("📂 Memproses file: nvdcve-2.0-{$year}.json");
            $this->processFile($filePath, $year, $severity, $batchSize, $isDryRun);
            $this->newLine();
        }

        $this->displaySummary($isDryRun);
        return self::SUCCESS;
    }

    /**
     * Proses satu file JSON NVD CVE.
     */
    private function processFile(
        string  $filePath,
        string  $year,
        ?string $severityFilter,
        int     $batchSize,
        bool    $isDryRun
    ): void {
        $fileSize = round(filesize($filePath) / 1024 / 1024, 1);
        $this->line("   📄 Ukuran file: {$fileSize} MB — membaca...");

        $data = json_decode(file_get_contents($filePath), true);
        if (!$data || empty($data['vulnerabilities'])) {
            $this->error("   ❌ Gagal parse atau file kosong.");
            return;
        }

        $vulnerabilities = $data['vulnerabilities'];
        $total           = count($vulnerabilities);
        $this->line("   📊 Total CVE: " . number_format($total));

        // Filter severity jika diminta
        if ($severityFilter) {
            $vulnerabilities = array_filter($vulnerabilities, function ($v) use ($severityFilter) {
                $metrics = $v['cve']['metrics'] ?? [];
                $sev = $this->extractSeverity($metrics);
                return strtoupper($sev) === $severityFilter;
            });
            $vulnerabilities = array_values($vulnerabilities);
            $filtered        = count($vulnerabilities);
            $this->line("   🔍 Setelah filter '{$severityFilter}': {$filtered} CVEs");
        }

        if (empty($vulnerabilities)) {
            $this->warn("   ⚠️ Tidak ada CVE yang cocok dengan filter.");
            return;
        }

        // Format semua CVE menjadi records
        $records = $this->formatCVEs($vulnerabilities);
        $this->line("   ✂️  Records siap: " . count($records));

        if ($isDryRun) {
            $this->line("   [DRY RUN] Akan upload " . count($records) . " records ke Pinecone.");
            $this->totalIngested += count($records);
            return;
        }

        // Upload dalam batch
        $batches    = array_chunk($records, $batchSize);
        $totalBatch = count($batches);
        $this->line("   📤 Upload: " . count($records) . " records dalam {$totalBatch} batch (@{$batchSize})");

        $bar = $this->output->createProgressBar($totalBatch);
        $bar->setFormat(" %current%/%max% [%bar%] %percent:3s%% — %message%");
        $bar->start();

        foreach ($batches as $i => $batch) {
            $bar->setMessage("Batch " . ($i + 1) . "/{$totalBatch}");
            try {
                $ok = $this->nvdPinecone->upsertText($batch);
                if ($ok) {
                    $this->totalIngested += count($batch);
                } else {
                    $this->totalFailed += count($batch);
                }
            } catch (\Exception $e) {
                $this->totalFailed += count($batch);
                Log::error("[NVD Ingest] Batch {$i} gagal: " . $e->getMessage());
            }
            $bar->advance();
            usleep(200000); // 200ms rate limiting
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✅ Tahun {$year}: {$this->totalIngested} CVE berhasil diingest.");
    }

    /**
     * Format array vulnerabilities NVD menjadi records Pinecone.
     *
     * @return array Array of ['id', 'text', 'metadata']
     */
    private function formatCVEs(array $vulnerabilities): array
    {
        $records = [];

        foreach ($vulnerabilities as $vuln) {
            $cve   = $vuln['cve'] ?? [];
            $cveId = $cve['id'] ?? null;
            if (!$cveId) continue;

            // Metadata dasar
            $published = substr($cve['published'] ?? '', 0, 10);
            $modified  = substr($cve['lastModified'] ?? '', 0, 10);

            // Deskripsi (Bahasa Inggris)
            $description = '';
            foreach ($cve['descriptions'] ?? [] as $desc) {
                if (($desc['lang'] ?? '') === 'en') {
                    $description = trim($desc['value'] ?? '');
                    break;
                }
            }
            if (empty($description)) continue; // Skip CVE tanpa deskripsi

            // CVSS Severity & Score
            $metrics  = $cve['metrics'] ?? [];
            $severity = $this->extractSeverity($metrics);
            $cvssScore  = $this->extractCVSSScore($metrics);
            $cvssVector = $this->extractCVSSVector($metrics);

            // CWE
            $cweIds = [];
            foreach ($cve['weaknesses'] ?? [] as $w) {
                foreach ($w['description'] ?? [] as $d) {
                    $val = $d['value'] ?? '';
                    if ($d['lang'] === 'en' && str_starts_with($val, 'CWE-')) {
                        $cweIds[] = $val;
                    }
                }
            }
            $cweStr = implode(', ', array_unique($cweIds)) ?: 'N/A';

            // Affected Products CPE (max 5)
            $products = [];
            foreach ($cve['configurations'] ?? [] as $config) {
                foreach ($config['nodes'] ?? [] as $node) {
                    foreach ($node['cpeMatch'] ?? [] as $cpe) {
                        if ($cpe['vulnerable'] ?? false) {
                            // Format: vendor:product:version dari CPE string
                            $parts = explode(':', $cpe['criteria'] ?? '');
                            if (count($parts) >= 5) {
                                $vendor  = $parts[3] ?? '';
                                $product = $parts[4] ?? '';
                                $version = $parts[5] ?? '*';
                                if ($vendor && $product) {
                                    $products[] = "{$vendor}/{$product}" . ($version !== '*' ? " {$version}" : '');
                                }
                            }
                        }
                    }
                }
            }
            $products   = array_unique($products);
            $productStr = implode('; ', array_slice($products, 0, 5));
            if (count($products) > 5) {
                $productStr .= ' (+' . (count($products) - 5) . ' more)';
            }

            // References (max 3)
            $refs = [];
            foreach ($cve['references'] ?? [] as $ref) {
                if (!empty($ref['url'])) {
                    $refs[] = $ref['url'];
                    if (count($refs) >= 3) break;
                }
            }
            $refStr = implode('; ', $refs);

            // Build text chunk (full context untuk embedding)
            $cvssInfo = $cvssScore !== null
                ? "CVSS Score: {$cvssScore}" . ($cvssVector ? " | Vector: {$cvssVector}" : '')
                : 'CVSS: N/A';

            $text  = "CVE ID: {$cveId}\n";
            $text .= "Severity: {$severity}\n";
            $text .= "{$cvssInfo}\n";
            $text .= "CWE: {$cweStr}\n";
            $text .= "Published: {$published} | Modified: {$modified}\n";
            $text .= "Description: {$description}";
            if ($productStr) $text .= "\nAffected: {$productStr}";
            if ($refStr)     $text .= "\nReferences: {$refStr}";

            $records[] = [
                'id'   => strtolower(str_replace(['-', ' '], ['_', '_'], $cveId)),
                'text' => $text,
                'metadata' => [
                    'cve_id'     => $cveId,
                    'severity'   => strtoupper($severity),
                    'cvss_score' => $cvssScore,
                    'cwe_id'     => $cweStr,
                    'published'  => $published,
                    'source'     => 'nvd-cve',
                ],
            ];
        }

        return $records;
    }

    private function extractSeverity(array $metrics): string
    {
        foreach (['cvssMetricV31', 'cvssMetricV30', 'cvssMetricV2'] as $key) {
            if (!empty($metrics[$key][0])) {
                $m = $metrics[$key][0];
                return $m['cvssData']['baseSeverity']
                    ?? $m['baseSeverity']
                    ?? 'NONE';
            }
        }
        return 'NONE';
    }

    private function extractCVSSScore(array $metrics): ?float
    {
        foreach (['cvssMetricV31', 'cvssMetricV30', 'cvssMetricV2'] as $key) {
            if (!empty($metrics[$key][0])) {
                $score = $metrics[$key][0]['cvssData']['baseScore']
                    ?? $metrics[$key][0]['baseScore']
                    ?? null;
                return $score !== null ? (float) $score : null;
            }
        }
        return null;
    }

    private function extractCVSSVector(array $metrics): ?string
    {
        foreach (['cvssMetricV31', 'cvssMetricV30'] as $key) {
            if (!empty($metrics[$key][0])) {
                return $metrics[$key][0]['cvssData']['vectorString'] ?? null;
            }
        }
        return null;
    }

    private function displayBanner(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║    NVD CVE Local Ingest → Pinecone redsim-nvd-cve   ║');
        $this->info('║    Source: File JSON Lokal (102k+ CVEs 2024–2026)   ║');
        $this->info('╚══════════════════════════════════════════════════════╝');
        $this->newLine();
        $nvdKey = env('PINECONE_NVD_API_KEY', '');
        $this->line('   NVD API Key  : ' . substr($nvdKey, 0, 20) . '...');
        $this->line('   Index Target : ' . env('PINECONE_NVD_INDEX', 'redsim-nvd-cve'));
        $this->line('   Dataset Path : ' . env('NVD_DATASET_PATH', 'N/A'));
        $this->newLine();
    }

    private function displaySummary(bool $isDryRun): void
    {
        $this->info('══════════════════════════════════════════');
        if ($isDryRun) {
            $this->info("  [DRY RUN] Total yang akan diupload: {$this->totalIngested}");
        } else {
            $this->info("  ✅ Total berhasil : " . number_format($this->totalIngested) . " CVE");
            if ($this->totalFailed > 0) {
                $this->warn("  ⚠️  Total gagal   : " . number_format($this->totalFailed) . " CVE");
            }
        }
        $this->info('══════════════════════════════════════════');
    }
}
