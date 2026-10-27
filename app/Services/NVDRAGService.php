<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * NVDRAGService
 *
 * RAG Service khusus untuk knowledge base NVD CVE (2024–2026).
 * Melakukan semantic search ke Pinecone index `redsim-nvd-cve`
 * dan mengembalikan konteks CVE yang relevan untuk disertakan
 * bersama konteks RAG umum ke LLM.
 *
 * Digunakan bersama RAGService (dual-RAG pattern).
 */
class NVDRAGService
{
    protected NVDPineconeService $pinecone;

    public function __construct(NVDPineconeService $pinecone)
    {
        $this->pinecone = $pinecone;
    }

    /**
     * Cari CVE yang relevan dari NVD knowledge base.
     *
     * @param string      $query    Query teks (ringkasan anomali, tipe serangan, dll.)
     * @param int         $topK     Jumlah CVE teratas yang diambil
     * @param string|null $severity Filter severity (CRITICAL, HIGH, MEDIUM, LOW) — optional
     * @param array       $techContext Teknologi yang terdeteksi — untuk relevance filtering
     * @return array ['konteks' => string, 'chunks' => array, 'total' => int]
     */
    public function searchCVE(string $query, int $topK = 5, ?string $severity = null, array $techContext = []): array
    {
        $topK  = min($topK, (int) env('NVD_RAG_TOP_K', 5));

        $filter = [];
        if ($severity) {
            $filter['severity'] = ['$eq' => strtoupper($severity)];
        }

        // Ambil lebih banyak kandidat untuk re-ranking dan technology filtering
        $candidateK = min($topK * 4, 20);
        $matches    = $this->pinecone->queryText($query, $candidateK, $filter);

        if (empty($matches)) {
            Log::info('[NVD RAG] Tidak ada CVE yang relevan ditemukan.', ['query' => $query]);
            return [
                'konteks' => '',
                'chunks'  => [],
                'total'   => 0,
            ];
        }

        // Threshold adaptif: jika ada tech context, pakai threshold lebih ketat
        $threshold = !empty($techContext)
            ? (float) env('NVD_RAG_THRESHOLD_TECH', 0.70)   // ada konteks teknologi
            : (float) env('NVD_RAG_THRESHOLD', 0.60);        // fallback umum

        $filtered = array_filter($matches, fn($m) => ($m['score'] ?? 0) >= $threshold);

        // Post-filter: buang CVE yang tidak relevan dengan teknologi terdeteksi
        if (!empty($techContext)) {
            $filtered = $this->filterByTechnologyRelevance($filtered, $techContext);
        }

        // Re-rank: prioritaskan CVE dengan severity lebih tinggi jika score mirip
        usort($filtered, function ($a, $b) {
            $scoreDiff = ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
            if ($scoreDiff !== 0) return $scoreDiff;
            return $this->severityOrder($b['metadata']['severity'] ?? '')
                <=> $this->severityOrder($a['metadata']['severity'] ?? '');
        });

        $finalChunks = array_slice(array_values($filtered), 0, $topK);

        // Format konteks teks untuk dikirim ke LLM
        $konteks = $this->buildKonteks($finalChunks);

        Log::info('[NVD RAG] CVE retrieved', [
            'query'        => substr($query, 0, 100),
            'total_hits'   => count($matches),
            'after_filter' => count($filtered),
            'final'        => count($finalChunks),
            'threshold'    => $threshold,
            'tech_context' => array_keys($techContext),
            'cve_ids'      => array_map(
                fn($c) => $c['metadata']['cve_id'] ?? $c['id'],
                $finalChunks
            ),
        ]);

        return [
            'konteks' => $konteks,
            'chunks'  => $this->normalizeChunks($finalChunks),
            'total'   => count($finalChunks),
        ];
    }

    /**
     * Cari CVE dengan context teknologi terdeteksi (shortcut untuk integrasi).
     *
     * @param  string  $query
     * @param  array   $fingerprint  Output dari UrlVulnerabilityCorrelator::extractFingerprint()
     * @param  int     $topK
     * @return array
     */
    public function searchCVEForTechnology(string $query, array $fingerprint, int $topK = 5): array
    {
        $techContext = $this->buildTechContext($fingerprint);
        return $this->searchCVE($query, $topK, techContext: $techContext);
    }

    /**
     * Cari CVE berdasarkan daftar CVE ID secara eksplisit (exact lookup).
     * Berguna ketika LLM atau ML sudah menyebut CVE ID tertentu.
     *
     * @param array $cveIds  Array of CVE ID strings, e.g. ['CVE-2024-1234', 'CVE-2025-5678']
     * @return array ['konteks' => string, 'chunks' => array]
     */
    public function lookupCVEIds(array $cveIds): array
    {
        if (empty($cveIds)) {
            return ['konteks' => '', 'chunks' => []];
        }

        $allChunks = [];

        // Pinecone Integrated Embeddings tidak support fetch by ID langsung,
        // jadi kita query teks dengan CVE ID sebagai query
        foreach ($cveIds as $cveId) {
            $matches = $this->pinecone->queryText($cveId, 3, [
                'cve_id' => ['$eq' => strtoupper($cveId)],
            ]);

            foreach ($matches as $match) {
                if (($match['score'] ?? 0) >= 0.55) {
                    $allChunks[] = $match;
                }
            }
        }

        if (empty($allChunks)) {
            return ['konteks' => '', 'chunks' => []];
        }

        // Deduplikasi
        $seen      = [];
        $deduped   = [];
        foreach ($allChunks as $chunk) {
            $cveId = $chunk['metadata']['cve_id'] ?? $chunk['id'];
            if (!isset($seen[$cveId])) {
                $seen[$cveId] = true;
                $deduped[]    = $chunk;
            }
        }

        $konteks = $this->buildKonteks($deduped);

        return [
            'konteks' => $konteks,
            'chunks'  => $this->normalizeChunks($deduped),
        ];
    }

    /**
     * Build konteks teks dari array matches untuk dikirim ke LLM.
     */
    protected function buildKonteks(array $chunks): string
    {
        if (empty($chunks)) {
            return '';
        }

        $lines = [];
        foreach ($chunks as $i => $chunk) {
            $meta   = $chunk['metadata'] ?? [];
            $cveId  = $meta['cve_id']    ?? ($chunk['id'] ?? "CVE-#" . ($i + 1));
            $sev    = $meta['severity']  ?? 'N/A';
            $cvss   = $meta['cvss_score'] ?? 'N/A';
            $cwe    = $meta['cwe_id']    ?? 'N/A';
            $pub    = $meta['published'] ?? 'N/A';
            $score  = round($chunk['score'] ?? 0, 3);

            // Gunakan field 'content' atau 'text' sebagai deskripsi lengkap
            $desc = $meta['content'] ?? ($meta['text'] ?? ($meta['description'] ?? 'Deskripsi tidak tersedia.'));

            $lines[] = "### {$cveId} [Relevance: {$score}]";
            $lines[] = "- **Severity**: {$sev} (CVSS: {$cvss})";
            $lines[] = "- **CWE**: {$cwe}";
            $lines[] = "- **Published**: {$pub}";
            $lines[] = "- **Detail**: {$desc}";
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Normalize chunks ke format standar yang kompatibel dengan RAGService.
     */
    protected function normalizeChunks(array $chunks): array
    {
        $result = [];
        foreach ($chunks as $chunk) {
            $meta = $chunk['metadata'] ?? [];
            $result[] = [
                'id'        => $chunk['id'] ?? '',
                'content'   => $meta['content'] ?? ($meta['text'] ?? ''),
                'source'    => 'nvd-cve',
                'source_id' => $meta['cve_id'] ?? null,
                'title'     => $meta['cve_id'] ?? 'NVD CVE',
                'metadata'  => [
                    'severity'   => $meta['severity']   ?? null,
                    'cvss_score' => $meta['cvss_score'] ?? null,
                    'cwe_id'     => $meta['cwe_id']     ?? null,
                    'published'  => $meta['published']  ?? null,
                ],
                'sem_score'   => round($chunk['score'] ?? 0, 4),
                'final_score' => round($chunk['score'] ?? 0, 4),
                'relevansi'   => ($chunk['score'] ?? 0) >= 0.78 ? 'tinggi' : 'sedang',
            ];
        }
        return $result;
    }

    /**
     * Urutan severity untuk tie-breaking (lebih rendah = lebih berbahaya).\
     */
    protected function severityOrder(string $severity): int
    {
        return match (strtoupper($severity)) {
            'CRITICAL' => 0,
            'HIGH'     => 1,
            'MEDIUM'   => 2,
            'LOW'      => 3,
            default    => 4,
        };
    }

    /**
     * Post-retrieval filter: buang CVE yang tidak relevan dengan teknologi yang terdeteksi.
     *
     * Jika CVE description HANYA membahas teknologi yang sama sekali berbeda dari target
     * (misalnya Cisco IOS / VMware untuk situs PHP), chunk tersebut dibuang.
     *
     * Logic: sebuah CVE lolos jika:
     *   - Deskripsinya mengandung ≥1 keyword dari teknologi yang terdeteksi, ATAU
     *   - Deskripsinya mengandung keyword umum (web, http, application) & CWE yang relevan
     *
     * @param  array  $chunks      Raw Pinecone matches
     * @param  array  $techContext Output dari buildTechContext()
     * @return array  Filtered chunks
     */
    protected function filterByTechnologyRelevance(array $chunks, array $techContext): array
    {
        if (empty($techContext)) {
            return $chunks;
        }

        // Flatten semua keyword teknologi yang terdeteksi
        $techKeywords = array_merge(...array_values($techContext));
        $techKeywords = array_map('strtolower', $techKeywords);

        // Keyword yang diblokir jika tidak ada teknologi terkait terdeteksi
        $blockedIfMissing = [
            'vmware'    => ['vmware', 'vsphere', 'vcenter', 'esxi', 'vrealize'],
            'cisco'     => ['cisco', 'ios xe', 'catalyst', 'asa firewall'],
            'windows'   => ['active directory', 'windows server', 'hyper-v', 'exchange server'],
            'oracle'    => ['oracle database', 'weblogic', 'jdk vulnerability'],
            'sap'       => ['sap netweaver', 'sap fiori'],
            'fortinet'  => ['fortinet', 'fortigate', 'fortissl'],
            'palo_alto' => ['palo alto', 'pan-os', 'globalprotect'],
        ];

        // Cek apakah ada keyword block yang harus diterapkan
        $activeBlocks = [];
        foreach ($blockedIfMissing as $group => $blockWords) {
            $groupDetected = false;
            foreach ($techKeywords as $kw) {
                foreach ($blockWords as $bw) {
                    if (str_contains($kw, $bw) || str_contains($bw, $kw)) {
                        $groupDetected = true;
                        break 2;
                    }
                }
            }
            if (!$groupDetected) {
                $activeBlocks[$group] = $blockWords;
            }
        }

        $filtered = [];
        foreach ($chunks as $chunk) {
            $meta    = $chunk['metadata'] ?? [];
            $content = strtolower($meta['content'] ?? ($meta['text'] ?? ($meta['description'] ?? '')));
            $cveId   = strtolower($meta['cve_id'] ?? '');

            // Jika content kosong, lewati filter
            if (empty($content)) {
                $filtered[] = $chunk;
                continue;
            }

            // Cek apakah CVE ini diblok (teknologinya tidak ada di target)
            $blocked = false;
            foreach ($activeBlocks as $group => $blockWords) {
                $matchCount = 0;
                foreach ($blockWords as $bw) {
                    if (str_contains($content, $bw)) {
                        $matchCount++;
                    }
                }
                // Jika 2+ keyword block grup ini ada di CVE → blokir
                if ($matchCount >= 2) {
                    $blocked = true;
                    Log::debug('[NVD RAG] CVE diblok karena tidak relevan dengan fingerprint', [
                        'cve_id' => $cveId,
                        'block_group' => $group,
                        'match_count' => $matchCount,
                    ]);
                    break;
                }
            }

            if (!$blocked) {
                $filtered[] = $chunk;
            }
        }

        return $filtered;
    }

    /**
     * Bangun technology context array dari fingerprint untuk digunakan di relevance filter.
     *
     * @param  array  $fingerprint  Output dari UrlVulnerabilityCorrelator::extractFingerprint()
     * @return array  Map teknologi → array keyword
     */
    protected function buildTechContext(array $fingerprint): array
    {
        $context = [];

        // Web server
        $webServerName = strtolower($fingerprint['web_server']['name'] ?? '');
        if ($webServerName && $webServerName !== 'unknown') {
            $context['web_server'] = [$webServerName];
            if ($fingerprint['web_server']['version'] ?? null) {
                $context['web_server'][] = $webServerName . ' ' . $fingerprint['web_server']['version'];
            }
        }

        // Runtime / language
        $runtime = strtolower($fingerprint['runtime']['name'] ?? '');
        if ($runtime) {
            $context['runtime'] = [$runtime];
            if ($fingerprint['runtime']['version'] ?? null) {
                $context['runtime'][] = $runtime . ' ' . $fingerprint['runtime']['version'];
            }
        }

        // OS
        $os = strtolower($fingerprint['web_server']['os'] ?? '');
        if ($os) {
            $context['os'] = [$os, 'linux'];
        }

        // SharePoint
        if ($fingerprint['is_sharepoint'] ?? false) {
            $context['sharepoint'] = ['sharepoint', 'microsoft iis', 'asp.net'];
        }

        // Teknologi lain dari teknologi_raw
        $otherTech = array_map('strtolower', $fingerprint['teknologi_raw'] ?? []);
        if (!empty($otherTech)) {
            $context['other'] = array_slice($otherTech, 0, 8);
        }

        return $context;
    }

    /**
     * Health check: apakah NVD Pinecone index tersedia dan terisi.
     */
    public function healthCheck(): array
    {
        return $this->pinecone->healthCheck();
    }

    /**
     * Statistik index NVD CVE.
     */
    public function stats(): array
    {
        return $this->pinecone->describeIndexStats();
    }
}
