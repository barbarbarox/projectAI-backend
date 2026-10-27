<?php

namespace App\Http\Controllers;

use App\Services\CvssCalculatorService;
use Illuminate\Http\Request;

class CvssController extends Controller
{
    /**
     * AJAX: Hitung CVSSv3.1 Base Score dari form input.
     * POST /api/cvss/calculate
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'AV' => 'required|in:N,A,L,P',
            'AC' => 'required|in:L,H',
            'PR' => 'required|in:N,L,H',
            'UI' => 'required|in:N,R',
            'S'  => 'required|in:U,C',
            'C'  => 'required|in:N,L,H',
            'I'  => 'required|in:N,L,H',
            'A'  => 'required|in:N,L,H',
            // Risk Score tambahan
            'exploitability' => 'nullable|numeric|min:0|max:1',
            'asset_value'    => 'nullable|numeric|min:0|max:1',
        ]);

        try {
            $result = CvssCalculatorService::calculate($validated);

            // Tambahkan Risk Score jika ada input exploitability & asset_value
            $riskResult = null;
            if (isset($validated['exploitability']) && isset($validated['asset_value'])) {
                $riskResult = CvssCalculatorService::calculateRiskScore(
                    $result['score'],
                    (float) $validated['exploitability'],
                    (float) $validated['asset_value']
                );
            }

            return response()->json([
                'success'     => true,
                'cvss'        => $result,
                'risk'        => $riskResult,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * GET: Halaman full CVSSv3 Calculator + Attack Path Visualizer + MITRE.
     * Route: /red-team-lab
     */
    public function lab()
    {
        $metrics = CvssCalculatorService::getMetrics();

        // MITRE ATT&CK Tactics + contoh teknik
        $mitreTactics = [
            'TA0043' => ['name' => 'Reconnaissance',        'color' => '#6366f1', 'techniques' => ['T1595','T1592','T1589','T1591','T1598']],
            'TA0042' => ['name' => 'Resource Development',  'color' => '#8b5cf6', 'techniques' => ['T1583','T1584','T1585','T1586','T1587']],
            'TA0001' => ['name' => 'Initial Access',         'color' => '#dc2626', 'techniques' => ['T1190','T1566','T1078','T1189','T1195']],
            'TA0002' => ['name' => 'Execution',              'color' => '#ea580c', 'techniques' => ['T1059','T1203','T1072','T1106','T1129']],
            'TA0003' => ['name' => 'Persistence',            'color' => '#d97706', 'techniques' => ['T1053','T1136','T1505','T1543','T1574']],
            'TA0004' => ['name' => 'Privilege Escalation',  'color' => '#ca8a04', 'techniques' => ['T1548','T1134','T1068','T1078','T1611']],
            'TA0005' => ['name' => 'Defense Evasion',        'color' => '#16a34a', 'techniques' => ['T1070','T1562','T1027','T1055','T1078']],
            'TA0006' => ['name' => 'Credential Access',     'color' => '#0891b2', 'techniques' => ['T1110','T1555','T1552','T1539','T1557']],
            'TA0007' => ['name' => 'Discovery',              'color' => '#2563eb', 'techniques' => ['T1082','T1083','T1046','T1018','T1057']],
            'TA0008' => ['name' => 'Lateral Movement',      'color' => '#7c3aed', 'techniques' => ['T1021','T1210','T1534','T1550','T1563']],
            'TA0009' => ['name' => 'Collection',             'color' => '#db2777', 'techniques' => ['T1005','T1039','T1025','T1114','T1557']],
            'TA0011' => ['name' => 'Command & Control',     'color' => '#9f1239', 'techniques' => ['T1071','T1090','T1095','T1102','T1132']],
            'TA0010' => ['name' => 'Exfiltration',           'color' => '#991b1b', 'techniques' => ['T1041','T1048','T1052','T1030','T1567']],
            'TA0040' => ['name' => 'Impact',                 'color' => '#7f1d1d', 'techniques' => ['T1485','T1486','T1489','T1491','T1499']],
        ];

        return view('red-team-lab.index', compact('metrics', 'mitreTactics'));
    }
}
