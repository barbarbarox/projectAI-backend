<?php

namespace App\Http\Controllers;

use App\Models\Scan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $scans = Scan::where('user_id', $user->id)->latest()->limit(5)->get();

        $batasHarian = $user->tier === 'gratis' ? (int) env('MAX_SCANS_PER_DAY_FREE', 10) : 999;

        // 1 query aggregate — jauh lebih cepat dari 7 query terpisah
        $aggr = DB::table('scans')
            ->where('user_id', $user->id)
            ->selectRaw("
                COUNT(*) as total_scan,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as scan_selesai
            ")
            ->first();

        $stats = [
            'total_scan'    => (int) ($aggr->total_scan ?? 0),
            'scan_selesai'  => (int) ($aggr->scan_selesai ?? 0),
            'scan_hari_ini' => $user->tier === 'gratis' ? min($user->scan_count_today, $batasHarian) : $user->scan_count_today,
            'batas_harian'  => $user->tier === 'gratis' ? $batasHarian : '∞',
            'total_poin'    => $user->total_poin,
        ];

        // Severity distribution — 1 query dengan GROUP BY
        $severityRows = DB::table('temuan')
            ->join('scans', 'scans.id', '=', 'temuan.scan_id')
            ->where('scans.user_id', $user->id)
            ->where('scans.status', 'selesai')
            ->selectRaw("tingkat_keparahan, COUNT(*) as jumlah")
            ->groupBy('tingkat_keparahan')
            ->pluck('jumlah', 'tingkat_keparahan');

        $distribusiSeverity = [
            'kritis' => (int) ($severityRows['kritis'] ?? 0),
            'tinggi' => (int) ($severityRows['tinggi'] ?? 0),
            'sedang' => (int) ($severityRows['sedang'] ?? 0),
            'rendah' => (int) ($severityRows['rendah'] ?? 0),
        ];

        return view('dashboard', compact('user', 'scans', 'stats', 'distribusiSeverity'));
    }
}
