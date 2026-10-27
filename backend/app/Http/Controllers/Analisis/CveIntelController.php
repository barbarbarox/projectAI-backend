<?php

namespace App\Http\Controllers\Analisis;

use App\Http\Controllers\Controller;
use App\Services\MLPredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * CveIntelController — CVE Intelligence Dashboard
 *
 * Fase 2 RedSim Enhancement: Halaman analisis CVE instan menggunakan
 * Ensemble ML (RF + XGBoost) tanpa menunggu skor NIST manual.
 */
class CveIntelController extends Controller
{
    public function __construct(
        protected MLPredictionService $ml
    ) {}

    /** Tampilkan form CVE Intelligence */
    public function form()
    {
        $mlStatus = $this->ml->isAvailable();
        return view('analisis.cve-intel', compact('mlStatus'));
    }

    /**
     * Analisis CVE ID atau CVSS vector via AJAX.
     * Returns JSON — dikonsumsi oleh Alpine.js di front-end.
     */
    public function analisis(Request $request)
    {
        $request->validate([
            'mode'    => 'required|in:cve_id,manual',
            'cve_id'  => 'required_if:mode,cve_id|nullable|string|max:30',
            'tipe'    => 'required_if:mode,manual|nullable|string|max:50',
            'tingkat' => 'required_if:mode,manual|nullable|string|max:20',
            'cwe_id'  => 'nullable|string|max:20',
            'deskripsi' => 'nullable|string|max:2000',
        ]);

        $mode = $request->input('mode');

        // ── Mode: CVE ID lookup ──────────────────────────────────────────────
        if ($mode === 'cve_id') {
            $cveId  = strtoupper(trim($request->input('cve_id')));
            $nvdData = $this->fetchNvdData($cveId);

            if (! $nvdData) {
                return response()->json([
                    'success' => false,
                    'message' => "CVE ID '{$cveId}' tidak ditemukan di NVD. Cek kembali formatnya (contoh: CVE-2024-12345).",
                ], 404);
            }

            $temuan = $this->nvdToTemuan($nvdData, $cveId);
            $prediction = $this->ml->predict($temuan);

            return response()->json([
                'success'    => true,
                'mode'       => 'cve_id',
                'cve_id'     => $cveId,
                'nvd'        => $nvdData,
                'temuan'     => $temuan,
                'prediction' => $prediction,
                'patch_deadline' => $this->patchDeadline($prediction['ensemble_severity'] ?? 'MEDIUM'),
            ]);
        }

        // ── Mode: Manual CVSS input ──────────────────────────────────────────
        $temuan = [
            'tipe'              => $request->input('tipe', 'lainnya'),
            'tingkat_keparahan' => $request->input('tingkat', 'sedang'),
            'cwe_id'            => $request->input('cwe_id', ''),
            'cve_id'            => '',
            'deskripsi'         => $request->input('deskripsi', ''),
            'tautan_referensi'  => '',
        ];

        $prediction = $this->ml->predict($temuan);

        return response()->json([
            'success'        => true,
            'mode'           => 'manual',
            'temuan'         => $temuan,
            'prediction'     => $prediction,
            'patch_deadline' => $this->patchDeadline($prediction['ensemble_severity'] ?? 'MEDIUM'),
        ]);
    }

    /** Fetch data CVE dari NVD API (cached 1 jam) */
    private function fetchNvdData(string $cveId): ?array
    {
        return Cache::remember("nvd_{$cveId}", 3600, function () use ($cveId) {
            try {
                $headers = ['Accept' => 'application/json'];
                $nvdKey = env('NVD_API_KEY');
                if (!empty($nvdKey)) {
                    $headers['apiKey'] = $nvdKey;
                }

                $response = Http::timeout(12)
                    ->withHeaders($headers)
                    ->get("https://services.nvd.nist.gov/rest/json/cves/2.0", [
                        'cveId' => $cveId,
                    ]);

                if (! $response->successful()) {
                    return null;
                }

                $data = $response->json();
                $vuln = data_get($data, 'vulnerabilities.0.cve');
                if (! $vuln) return null;

                $cvssV40 = data_get($vuln, 'metrics.cvssMetricV40.0.cvssData');
                $cvssV31 = data_get($vuln, 'metrics.cvssMetricV31.0.cvssData');
                $cvssV30 = data_get($vuln, 'metrics.cvssMetricV30.0.cvssData');
                $cvss    = $cvssV31 ?? $cvssV40 ?? $cvssV30;

                return [
                    'id'          => $vuln['id'],
                    'description' => data_get($vuln, 'descriptions.0.value', '-'),
                    'published'   => substr($vuln['published'] ?? '', 0, 10),
                    'modified'    => substr($vuln['lastModified'] ?? '', 0, 10),
                    'status'      => $vuln['vulnStatus'] ?? '-',
                    'cvss_score'  => $cvss['baseScore'] ?? null,
                    'cvss_severity'=> $cvss['baseSeverity'] ?? null,
                    'cvss_vector' => $cvss['vectorString'] ?? null,
                    'attack_vector'       => $cvss['attackVector'] ?? null,
                    'attack_complexity'   => $cvss['attackComplexity'] ?? null,
                    'privileges_required' => $cvss['privilegesRequired'] ?? null,
                    'user_interaction'    => $cvss['userInteraction'] ?? null,
                    'scope'               => $cvss['scope'] ?? null,
                    'conf_impact'         => $cvss['confidentialityImpact'] ?? null,
                    'integ_impact'        => $cvss['integrityImpact'] ?? null,
                    'avail_impact'        => $cvss['availabilityImpact'] ?? null,
                    'cwe'         => data_get($vuln, 'weaknesses.0.description.0.value', null),
                    'references'  => collect(data_get($vuln, 'references', []))->take(3)->pluck('url')->toArray(),
                ];
            } catch (\Exception $e) {
                Log::warning("[CveIntel] Gagal fetch NVD: {$e->getMessage()}");
                return null;
            }
        });
    }

    /** Konversi data NVD → format temuan untuk MLPredictionService */
    private function nvdToTemuan(array $nvd, string $cveId): array
    {
        // Map CVSS severity ke tingkat_keparahan Indonesia
        $sevMap = [
            'CRITICAL' => 'kritis',
            'HIGH'     => 'tinggi',
            'MEDIUM'   => 'sedang',
            'LOW'      => 'rendah',
            'NONE'     => 'info',
        ];

        return [
            'tipe'              => 'kerentanan',
            'tingkat_keparahan' => $sevMap[strtoupper($nvd['cvss_severity'] ?? 'MEDIUM')] ?? 'sedang',
            'cwe_id'            => $nvd['cwe'] ?? '',
            'cve_id'            => $cveId,
            'deskripsi'         => $nvd['description'] ?? '',
            'tautan_referensi'  => implode(' ', $nvd['references'] ?? []),
        ];
    }

    /** Deadline patching berdasarkan severity */
    private function patchDeadline(string $severity): array
    {
        return match(strtoupper($severity)) {
            'CRITICAL' => ['hari' => 24,  'label' => '24 jam',   'color' => '#ff4444', 'urgency' => 'SEGERA'],
            'HIGH'     => ['hari' => 72,  'label' => '72 jam',   'color' => '#ff8800', 'urgency' => 'PRIORITAS'],
            'MEDIUM'   => ['hari' => 168, 'label' => '7 hari',   'color' => '#ffc107', 'urgency' => 'TERENCANA'],
            'LOW'      => ['hari' => 720, 'label' => '30 hari',  'color' => '#28a745', 'urgency' => 'RUTIN'],
            default    => ['hari' => 0,   'label' => 'Opsional', 'color' => '#6c757d', 'urgency' => 'RENDAH'],
        };
    }
}
