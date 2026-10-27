<?php

namespace App\Http\Controllers;

use App\Models\Scan;
use App\Models\Temuan;
use Illuminate\Support\Facades\DB;

class BenchmarkController extends Controller
{
    public function index()
    {
        // ──────────────────────────────────────────────────────────
        // 1. TOTAL COUNTS
        // ──────────────────────────────────────────────────────────
        $totalTemuan     = Temuan::count();
        $totalMlPrediksi = Temuan::whereNotNull('ml_predicted_severity')->count();
        $totalScans      = Scan::where('status', 'selesai')->count();

        // ──────────────────────────────────────────────────────────
        // 2. AGREEMENT RATE  (LLM == ML setelah normalisasi)
        // ──────────────────────────────────────────────────────────
        $severityMap = [
            'kritis' => 'CRITICAL', 'critical' => 'CRITICAL',
            'tinggi' => 'HIGH',     'high'     => 'HIGH',
            'sedang' => 'MEDIUM',   'medium'   => 'MEDIUM',
            'rendah' => 'LOW',      'low'      => 'LOW',
        ];

        $temuanDenganML = Temuan::whereNotNull('ml_predicted_severity')
            ->whereNotNull('tingkat_keparahan')
            ->get(['tingkat_keparahan', 'ml_predicted_severity', 'ml_confidence', 'konsensus_severity']);

        $agreeCount = 0;
        foreach ($temuanDenganML as $t) {
            $llmNorm = strtoupper($severityMap[strtolower($t->tingkat_keparahan)] ?? $t->tingkat_keparahan);
            $mlNorm  = strtoupper($t->ml_predicted_severity);
            if ($llmNorm === $mlNorm) $agreeCount++;
        }

        $totalCompared  = $temuanDenganML->count();
        $agreementRate  = $totalCompared > 0 ? round(($agreeCount / $totalCompared) * 100, 1) : 0;
        $avgMlConfidence = $totalCompared > 0
            ? round($temuanDenganML->avg('ml_confidence') * 100, 1)
            : 0;

        // ──────────────────────────────────────────────────────────
        // 3. DISTRIBUSI SEVERITY — LLM vs ML
        // ──────────────────────────────────────────────────────────
        $sevOrder  = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW', 'NONE'];

        $llmDist = [];
        $mlDist  = [];
        foreach ($sevOrder as $sev) {
            $llmDist[$sev] = 0;
            $mlDist[$sev]  = 0;
        }

        foreach ($temuanDenganML as $t) {
            $llm = strtoupper($severityMap[strtolower($t->tingkat_keparahan)] ?? $t->tingkat_keparahan);
            $ml  = strtoupper($t->ml_predicted_severity);
            if (isset($llmDist[$llm])) $llmDist[$llm]++;
            if (isset($mlDist[$ml]))   $mlDist[$ml]++;
        }

        // ──────────────────────────────────────────────────────────
        // 4. CONFUSION MATRIX (LLM-axis vs ML-axis)
        // ──────────────────────────────────────────────────────────
        $confusionMatrix = [];
        $labels = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'];
        foreach ($labels as $l) {
            foreach ($labels as $m) {
                $confusionMatrix[$l][$m] = 0;
            }
        }
        foreach ($temuanDenganML as $t) {
            $llm = strtoupper($severityMap[strtolower($t->tingkat_keparahan)] ?? $t->tingkat_keparahan);
            $ml  = strtoupper($t->ml_predicted_severity);
            if (in_array($llm, $labels) && in_array($ml, $labels)) {
                $confusionMatrix[$llm][$ml]++;
            }
        }

        // ──────────────────────────────────────────────────────────
        // 5. TREND AGREEMENT — 7 hari terakhir
        // ──────────────────────────────────────────────────────────
        $trendDays = 7;
        $trend = [];
        for ($i = $trendDays - 1; $i >= 0; $i--) {
            $date  = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('d/m');
            $rows  = Temuan::whereDate('created_at', $date)
                ->whereNotNull('ml_predicted_severity')
                ->whereNotNull('tingkat_keparahan')
                ->get(['tingkat_keparahan', 'ml_predicted_severity']);

            $dayTotal = $rows->count();
            $dayAgree = 0;
            foreach ($rows as $t) {
                $llmN = strtoupper($severityMap[strtolower($t->tingkat_keparahan)] ?? $t->tingkat_keparahan);
                $mlN  = strtoupper($t->ml_predicted_severity);
                if ($llmN === $mlN) $dayAgree++;
            }

            $trend[] = [
                'label'     => $label,
                'total'     => $dayTotal,
                'agree'     => $dayAgree,
                'rate'      => $dayTotal > 0 ? round(($dayAgree / $dayTotal) * 100, 1) : null,
            ];
        }

        // ──────────────────────────────────────────────────────────
        // 6. TIPE TEMUAN — mana yang paling sering "differ"
        // ──────────────────────────────────────────────────────────
        $differByType = Temuan::select('tipe', DB::raw('count(*) as total'))
            ->whereNotNull('ml_predicted_severity')
            ->whereNotNull('tingkat_keparahan')
            ->whereRaw("UPPER(ml_predicted_severity) != UPPER(tingkat_keparahan)")
            ->groupBy('tipe')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ──────────────────────────────────────────────────────────
        // 7. SCAN TERBARU dengan DATA ML
        // ──────────────────────────────────────────────────────────
        $recentScans = Scan::with(['temuan' => function ($q) {
                $q->whereNotNull('ml_predicted_severity');
            }])
            ->where('status', 'selesai')
            ->whereHas('temuan', fn($q) => $q->whereNotNull('ml_predicted_severity'))
            ->latest('selesai_at')
            ->limit(5)
            ->get()
            ->map(function ($scan) use ($severityMap) {
                $temuanML = $scan->temuan;
                $agree = $temuanML->filter(function ($t) use ($severityMap) {
                    $llm = strtoupper($severityMap[strtolower($t->tingkat_keparahan)] ?? $t->tingkat_keparahan);
                    return $llm === strtoupper($t->ml_predicted_severity);
                })->count();
                $total = $temuanML->count();
                return [
                    'scan'          => $scan,
                    'total_ml'      => $total,
                    'agree'         => $agree,
                    'agreement_pct' => $total > 0 ? round(($agree / $total) * 100) : 0,
                    'avg_conf'      => $total > 0 ? round($temuanML->avg('ml_confidence') * 100, 1) : 0,
                ];
            });

        return view('benchmark.index', compact(
            'totalTemuan', 'totalMlPrediksi', 'totalScans', 'totalCompared',
            'agreementRate', 'avgMlConfidence',
            'llmDist', 'mlDist', 'sevOrder', 'labels',
            'confusionMatrix', 'trend', 'differByType', 'recentScans'
        ));
    }

    /**
     * Export data benchmark ke CSV untuk keperluan laporan/skripsi.
     */
    public function exportCsv()
    {
        $severityMap = [
            'kritis' => 'CRITICAL', 'critical' => 'CRITICAL',
            'tinggi' => 'HIGH',     'high'     => 'HIGH',
            'sedang' => 'MEDIUM',   'medium'   => 'MEDIUM',
            'rendah' => 'LOW',      'low'      => 'LOW',
        ];

        $rows = Temuan::with('scan:id,target,tipe_scan,selesai_at')
            ->whereNotNull('ml_predicted_severity')
            ->whereNotNull('tingkat_keparahan')
            ->latest()
            ->limit(5000)
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="redsim_benchmark_' . now()->format('YmdHis') . '.csv"',
        ];

        $callback = function () use ($rows, $severityMap) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'scan_id', 'target', 'tipe_scan', 'tipe_temuan',
                'llm_severity', 'llm_severity_normalized',
                'ml_severity', 'ml_confidence',
                'konsensus_severity', 'agreement', 'created_at'
            ]);
            foreach ($rows as $t) {
                $llmNorm = strtoupper($severityMap[strtolower($t->tingkat_keparahan)] ?? $t->tingkat_keparahan);
                $mlNorm  = strtoupper($t->ml_predicted_severity);
                fputcsv($handle, [
                    $t->scan_id,
                    $t->scan->target ?? '',
                    $t->scan->tipe_scan ?? '',
                    $t->tipe,
                    $t->tingkat_keparahan,
                    $llmNorm,
                    $mlNorm,
                    round($t->ml_confidence * 100, 2) . '%',
                    $t->konsensus_severity ?? '',
                    $llmNorm === $mlNorm ? 'AGREE' : 'DIFFER',
                    $t->created_at,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
