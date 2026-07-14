<?php

namespace App\Console\Commands;

use App\Services\IngestionService;
use Generator;
use Illuminate\Console\Command;

class IngestDataset extends Command
{
    protected $signature = 'redsim:ingest {--source= : Source spesifik (cisa-kev, attck, cwe, capec, owasp-cheatsheet, nvd-cve)}';
    protected $description = 'Ingest dataset keamanan ke knowledge base MySQL';

    protected IngestionService $ingestionService;

    public function __construct(IngestionService $ingestionService)
    {
        parent::__construct();
        $this->ingestionService = $ingestionService;
    }

    public function handle(): int
    {
        $source = $this->option('source');
        $datasetPath = env('DATASET_PATH', base_path('../dataset'));
        $allStats = [];

        $sources = $source
            ? [$source]
            : ['cisa-kev', 'attck', 'cwe', 'capec', 'owasp-cheatsheet', 'nvd-cve'];

        foreach ($sources as $src) {
            $this->info("📥 Memproses: {$src}...");
            try {
                $generator = match ($src) {
                    'cisa-kev' => $this->cisaKev($datasetPath),
                    'attck' => $this->mitreAttck($datasetPath),
                    'cwe' => $this->cwe($datasetPath),
                    'capec' => $this->capec($datasetPath),
                    'owasp-cheatsheet' => $this->owaspCheatsheets($datasetPath),
                    'nvd-cve' => $this->nvdCve($datasetPath),
                    default => throw new \RuntimeException("Source tidak dikenal: {$src}"),
                };

                $stats = $this->ingestionService->ingestDariGenerator($src, $generator, function ($s, $n) {
                    $this->line("  ✓ [{$s}] {$n} chunks");
                });

                $allStats[$src] = $stats;
                $this->info("  ✅ {$src}: {$stats['berhasil']} berhasil, {$stats['gagal']} gagal");
            } catch (\Exception $e) {
                $this->error("  ❌ {$src} error: " . $e->getMessage());
                $allStats[$src] = ['berhasil' => 0, 'gagal' => 0];
            }
        }

        // Ringkasan
        $rows = [];
        $totalOk = 0;
        $totalErr = 0;
        foreach ($allStats as $name => $s) {
            $rows[] = [$name, $s['berhasil'], $s['gagal']];
            $totalOk += $s['berhasil'];
            $totalErr += $s['gagal'];
        }
        $rows[] = ['TOTAL', $totalOk, $totalErr];

        $this->newLine();
        $this->table(['Dataset', 'Berhasil', 'Gagal'], $rows);

        return 0;
    }

    protected function cisaKev(string $path): Generator
    {
        $file = $path . '/known_exploited_vulnerabilities.json';
        if (!file_exists($file)) { $this->warn("File tidak ditemukan: {$file}"); return; }
        $data = json_decode(file_get_contents($file), true);
        foreach ($data['vulnerabilities'] ?? [] as $item) {
            yield [
                'teks' => "{$item['cveID']} - {$item['vulnerabilityName']}: {$item['shortDescription']}. Tindakan wajib: {$item['requiredAction']}.",
                'source_id' => $item['cveID'],
                'title' => $item['vulnerabilityName'],
                'metadata' => [
                    'severity' => 'kritis',
                    'ransomware' => $item['knownRansomwareCampaignUse'] ?? 'Unknown',
                ],
            ];
        }
    }

    protected function mitreAttck(string $path): Generator
    {
        $file = $path . '/attack-stix-data/enterprise-attack/enterprise-attack.json';
        if (!file_exists($file)) { $this->warn("File tidak ditemukan: {$file}"); return; }
        $data = json_decode(file_get_contents($file), true);
        foreach ($data['objects'] ?? [] as $obj) {
            if (($obj['type'] ?? '') !== 'attack-pattern') continue;
            if (!empty($obj['revoked'])) continue;
            $extId = $obj['external_references'][0]['external_id'] ?? '';
            yield [
                'teks' => "{$extId} {$obj['name']}: " . substr($obj['description'] ?? '', 0, 600),
                'source_id' => $extId,
                'title' => $obj['name'],
                'metadata' => [
                    'tactic' => $obj['kill_chain_phases'][0]['phase_name'] ?? 'unknown',
                    'platforms' => implode(',', $obj['x_mitre_platforms'] ?? []),
                ],
            ];
        }
    }

    protected function cwe(string $path): Generator
    {
        $file = $path . '/cwec_v4.20.xml';
        if (!file_exists($file)) { $this->warn("File tidak ditemukan: {$file}"); return; }
        $xml = simplexml_load_file($file);
        foreach ($xml->Weaknesses->Weakness ?? [] as $w) {
            $desc = strip_tags((string) ($w->Description ?? ''));
            $ext = strip_tags((string) ($w->Extended_Description ?? ''));
            yield [
                'teks' => "CWE-{$w['ID']} {$w['Name']}: {$desc}. {$ext}",
                'source_id' => 'CWE-' . $w['ID'],
                'title' => (string) $w['Name'],
                'metadata' => ['cwe_id' => 'CWE-' . $w['ID']],
            ];
        }
    }

    protected function capec(string $path): Generator
    {
        $file = $path . '/capec_latest.xml';
        if (!file_exists($file)) { $this->warn("File tidak ditemukan: {$file}"); return; }
        $xml = simplexml_load_file($file);
        foreach ($xml->Attack_Patterns->Attack_Pattern ?? [] as $ap) {
            $desc = strip_tags((string) ($ap->Description ?? ''));
            yield [
                'teks' => "CAPEC-{$ap['ID']} {$ap['Name']}: {$desc}",
                'source_id' => 'CAPEC-' . $ap['ID'],
                'title' => (string) $ap['Name'],
                'metadata' => ['capec_id' => 'CAPEC-' . $ap['ID']],
            ];
        }
    }

    protected function owaspCheatsheets(string $path): Generator
    {
        $dir = $path . '/CheatSheetSeries/cheatsheets/';
        if (!is_dir($dir)) { $this->warn("Direktori tidak ditemukan: {$dir}"); return; }
        $files = glob($dir . '*.md');
        foreach ($files as $file) {
            $konten = file_get_contents($file);
            $namaFile = basename($file, '.md');
            $sections = preg_split('/^#{1,2} /m', $konten, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($sections as $idx => $section) {
                $trimmed = trim($section);
                if (strlen($trimmed) < 100) continue;
                yield [
                    'teks' => substr($trimmed, 0, 800),
                    'source_id' => $namaFile . '-' . $idx,
                    'title' => $namaFile,
                    'metadata' => ['file' => $namaFile, 'section' => $idx],
                ];
            }
        }
    }

    protected function nvdCve(string $path): Generator
    {
        $allFiles = scandir($path);
        $nvdFiles = array_filter($allFiles, fn($f) =>
            str_starts_with(trim($f), 'nvdcve-') && str_ends_with(trim($f), '.json')
        );

        // NVD files might be directories containing the actual JSON
        foreach ($nvdFiles as $file) {
            $fullPath = $path . '/' . trim($file);

            // Check if it's a directory (extracted zip)
            if (is_dir($fullPath)) {
                $innerFiles = glob($fullPath . '/*.json');
                if (empty($innerFiles)) continue;
                $fullPath = $innerFiles[0];
            }

            if (!file_exists($fullPath) || is_dir($fullPath)) continue;

            $this->line("  📄 Membaca: " . basename($fullPath));
            $data = json_decode(file_get_contents($fullPath), true);
            if (!$data) continue;

            foreach ($data['vulnerabilities'] ?? [] as $item) {
                $cve = $item['cve'] ?? [];
                $score = $cve['metrics']['cvssMetricV31'][0]['cvssData']['baseScore']
                    ?? $cve['metrics']['cvssMetricV30'][0]['cvssData']['baseScore']
                    ?? 0;

                if ($score < 7.0) continue;

                $desc = collect($cve['descriptions'] ?? [])
                    ->firstWhere('lang', 'en')['value'] ?? '';

                if (empty($desc)) continue;

                yield [
                    'teks' => "{$cve['id']}: {$desc}",
                    'source_id' => $cve['id'],
                    'title' => $cve['id'],
                    'metadata' => [
                        'cvss_score' => $score,
                        'cwe' => $cve['weaknesses'][0]['description'][0]['value'] ?? null,
                    ],
                ];
            }
        }
    }
}
