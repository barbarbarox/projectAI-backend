<?php

namespace App\Services;

/**
 * CVSSv3.1 Base Score Calculator
 *
 * Mengimplementasikan formula resmi FIRST CVSSv3.1
 * Ref: https://www.first.org/cvss/specification-document
 */
class CvssCalculatorService
{
    // ── Attack Vector ───────────────────────────────────────────────
    const AV = [
        'N' => ['label' => 'Network',          'val' => 0.85, 'desc' => 'Dapat dieksploitasi dari jarak jauh via jaringan'],
        'A' => ['label' => 'Adjacent Network', 'val' => 0.62, 'desc' => 'Butuh akses ke jaringan lokal yang sama'],
        'L' => ['label' => 'Local',             'val' => 0.55, 'desc' => 'Perlu akses lokal ke sistem'],
        'P' => ['label' => 'Physical',          'val' => 0.20, 'desc' => 'Perlu akses fisik ke perangkat'],
    ];

    // ── Attack Complexity ────────────────────────────────────────────
    const AC = [
        'L' => ['label' => 'Low',  'val' => 0.77, 'desc' => 'Tidak diperlukan kondisi khusus'],
        'H' => ['label' => 'High', 'val' => 0.44, 'desc' => 'Diperlukan kondisi atau konfigurasi khusus'],
    ];

    // ── Privileges Required ──────────────────────────────────────────
    // Note: nilai PR berubah jika Scope = Changed
    const PR = [
        'N' => ['label' => 'None',  'val_u' => 0.85, 'val_c' => 0.85, 'desc' => 'Tidak butuh hak akses'],
        'L' => ['label' => 'Low',   'val_u' => 0.62, 'val_c' => 0.68, 'desc' => 'Butuh hak akses dasar'],
        'H' => ['label' => 'High',  'val_u' => 0.27, 'val_c' => 0.50, 'desc' => 'Butuh hak akses tinggi'],
    ];

    // ── User Interaction ─────────────────────────────────────────────
    const UI = [
        'N' => ['label' => 'None',     'val' => 0.85, 'desc' => 'Tidak butuh interaksi user'],
        'R' => ['label' => 'Required', 'val' => 0.62, 'desc' => 'Butuh tindakan user tertentu'],
    ];

    // ── Scope ────────────────────────────────────────────────────────
    const SCOPE = [
        'U' => ['label' => 'Unchanged', 'desc' => 'Dampak terbatas pada komponen yang diserang'],
        'C' => ['label' => 'Changed',   'desc' => 'Dampak menyebar ke komponen lain'],
    ];

    // ── CIA Impact ───────────────────────────────────────────────────
    const CIA = [
        'N' => ['label' => 'None',   'val' => 0.00, 'desc' => 'Tidak ada dampak'],
        'L' => ['label' => 'Low',    'val' => 0.22, 'desc' => 'Dampak terbatas'],
        'H' => ['label' => 'High',   'val' => 0.56, 'desc' => 'Dampak signifikan/total'],
    ];

    /**
     * Hitung CVSSv3.1 Base Score dari vector components.
     *
     * @param array $v ['AV'=>'N','AC'=>'L','PR'=>'N','UI'=>'N','S'=>'U','C'=>'H','I'=>'H','A'=>'H']
     * @return array  ['score'=>float, 'severity'=>string, 'vector_string'=>string, 'iss'=>float, 'esc'=>float, 'breakdown'=>array]
     */
    public static function calculate(array $v): array
    {
        // Validasi input
        $required = ['AV', 'AC', 'PR', 'UI', 'S', 'C', 'I', 'A'];
        foreach ($required as $k) {
            if (!isset($v[$k])) {
                throw new \InvalidArgumentException("Missing CVSS component: $k");
            }
        }

        $scopeChanged = $v['S'] === 'C';

        // Nilai numerik
        $avVal  = self::AV[$v['AV']]['val']  ?? 0;
        $acVal  = self::AC[$v['AC']]['val']  ?? 0;
        $prKey  = $scopeChanged ? 'val_c' : 'val_u';
        $prVal  = self::PR[$v['PR']][$prKey] ?? 0;
        $uiVal  = self::UI[$v['UI']]['val']  ?? 0;
        $cVal   = self::CIA[$v['C']]['val']  ?? 0;
        $iVal   = self::CIA[$v['I']]['val']  ?? 0;
        $aVal   = self::CIA[$v['A']]['val']  ?? 0;

        // ISS — Impact Sub-Score
        $iss = 1 - (1 - $cVal) * (1 - $iVal) * (1 - $aVal);

        // ESS — Exploitability Sub-Score
        $ess = 8.22 * $avVal * $acVal * $prVal * $uiVal;

        // Impact Score
        if ($scopeChanged) {
            $impact = 7.52 * ($iss - 0.029) - 3.25 * pow($iss - 0.02, 15);
        } else {
            $impact = 6.42 * $iss;
        }

        // Base Score
        if ($impact <= 0) {
            $baseScore = 0.0;
        } else {
            if ($scopeChanged) {
                $raw = min(1.08 * ($impact + $ess), 10);
            } else {
                $raw = min($impact + $ess, 10);
            }
            // Roundup ke 1 desimal
            $baseScore = ceil($raw * 10) / 10;
        }

        // Severity rating
        $severity = match(true) {
            $baseScore === 0.0              => 'NONE',
            $baseScore <= 3.9               => 'LOW',
            $baseScore <= 6.9               => 'MEDIUM',
            $baseScore <= 8.9               => 'HIGH',
            default                         => 'CRITICAL',
        };

        // Vector string
        $vectorString = "CVSS:3.1/AV:{$v['AV']}/AC:{$v['AC']}/PR:{$v['PR']}/UI:{$v['UI']}/S:{$v['S']}/C:{$v['C']}/I:{$v['I']}/A:{$v['A']}";

        return [
            'score'         => $baseScore,
            'severity'      => $severity,
            'vector_string' => $vectorString,
            'iss'           => round($iss, 3),
            'esc'           => round($ess, 3),
            'impact_score'  => round($impact, 3),
            'components'    => [
                'AV' => ['key' => $v['AV'], 'label' => self::AV[$v['AV']]['label'] ?? $v['AV'], 'val' => $avVal, 'desc' => self::AV[$v['AV']]['desc'] ?? ''],
                'AC' => ['key' => $v['AC'], 'label' => self::AC[$v['AC']]['label'] ?? $v['AC'], 'val' => $acVal, 'desc' => self::AC[$v['AC']]['desc'] ?? ''],
                'PR' => ['key' => $v['PR'], 'label' => self::PR[$v['PR']]['label'] ?? $v['PR'], 'val' => $prVal, 'desc' => self::PR[$v['PR']]['desc'] ?? ''],
                'UI' => ['key' => $v['UI'], 'label' => self::UI[$v['UI']]['label'] ?? $v['UI'], 'val' => $uiVal, 'desc' => self::UI[$v['UI']]['desc'] ?? ''],
                'S'  => ['key' => $v['S'],  'label' => self::SCOPE[$v['S']]['label'] ?? $v['S'], 'val' => null, 'desc' => self::SCOPE[$v['S']]['desc'] ?? ''],
                'C'  => ['key' => $v['C'],  'label' => self::CIA[$v['C']]['label'] ?? $v['C'], 'val' => $cVal, 'desc' => self::CIA[$v['C']]['desc'] ?? ''],
                'I'  => ['key' => $v['I'],  'label' => self::CIA[$v['I']]['label'] ?? $v['I'], 'val' => $iVal, 'desc' => self::CIA[$v['I']]['desc'] ?? ''],
                'A'  => ['key' => $v['A'],  'label' => self::CIA[$v['A']]['label'] ?? $v['A'], 'val' => $aVal, 'desc' => self::CIA[$v['A']]['desc'] ?? ''],
            ],
        ];
    }

    /**
     * Hitung RedSim Composite Risk Score (formula transparan).
     *
     * Risk Score = (CVSS × 0.4) + (Exploitability × 0.3) + (Asset Value × 0.3)
     */
    public static function calculateRiskScore(float $cvssScore, float $exploitability, float $assetValue): array
    {
        // Normalisasi ke 0-1
        $cvssNorm  = $cvssScore / 10;
        $exploitN  = min(max($exploitability, 0), 1);
        $assetN    = min(max($assetValue, 0), 1);

        $riskRaw   = ($cvssNorm * 0.4) + ($exploitN * 0.3) + ($assetN * 0.3);
        $riskScore = round(min($riskRaw * 5, 5), 2);  // scale ke 0-5

        $riskLevel = match(true) {
            $riskScore >= 4.5  => 'EXTREME',
            $riskScore >= 3.5  => 'HIGH',
            $riskScore >= 2.0  => 'MODERATE',
            $riskScore >= 1.0  => 'LOW',
            default            => 'MINIMAL',
        };

        return [
            'risk_score'       => $riskScore,
            'risk_level'       => $riskLevel,
            'formula'          => [
                'cvss_contrib'        => round($cvssNorm * 0.4, 3),
                'exploit_contrib'     => round($exploitN * 0.3, 3),
                'asset_contrib'       => round($assetN * 0.3, 3),
            ],
            'inputs' => [
                'cvss_score'     => $cvssScore,
                'exploitability' => $exploitN,
                'asset_value'    => $assetN,
            ],
        ];
    }

    /**
     * Definisi metric yang bisa digunakan oleh front-end.
     */
    public static function getMetrics(): array
    {
        return [
            'AV' => ['label' => 'Attack Vector',          'options' => self::AV],
            'AC' => ['label' => 'Attack Complexity',       'options' => self::AC],
            'PR' => ['label' => 'Privileges Required',     'options' => array_map(fn($v) => [...$v, 'val' => $v['val_u']], self::PR)],
            'UI' => ['label' => 'User Interaction',        'options' => self::UI],
            'S'  => ['label' => 'Scope',                   'options' => self::SCOPE],
            'C'  => ['label' => 'Confidentiality Impact',  'options' => self::CIA],
            'I'  => ['label' => 'Integrity Impact',        'options' => self::CIA],
            'A'  => ['label' => 'Availability Impact',     'options' => self::CIA],
        ];
    }
}
