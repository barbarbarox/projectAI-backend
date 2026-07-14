<?php

namespace App\Http\Controllers;

use App\Models\Scan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $scans = Scan::where('user_id', $user->id)->latest()->limit(5)->get();

        $stats = [
            'total_scan' => Scan::where('user_id', $user->id)->count(),
            'scan_selesai' => Scan::where('user_id', $user->id)->where('status', 'selesai')->count(),
            'scan_hari_ini' => $user->scan_count_today,
            'batas_harian' => (int) env('MAX_SCANS_PER_DAY_FREE', 10),
            'total_poin' => $user->total_poin,
        ];

        $distribusiSeverity = [
            'kritis' => Scan::where('user_id', $user->id)->where('status', 'selesai')->whereHas('temuan', fn($q) => $q->where('tingkat_keparahan', 'kritis'))->count(),
            'tinggi' => Scan::where('user_id', $user->id)->where('status', 'selesai')->whereHas('temuan', fn($q) => $q->where('tingkat_keparahan', 'tinggi'))->count(),
            'sedang' => Scan::where('user_id', $user->id)->where('status', 'selesai')->whereHas('temuan', fn($q) => $q->where('tingkat_keparahan', 'sedang'))->count(),
            'rendah' => Scan::where('user_id', $user->id)->where('status', 'selesai')->whereHas('temuan', fn($q) => $q->where('tingkat_keparahan', 'rendah'))->count(),
        ];

        return view('dashboard', compact('user', 'scans', 'stats', 'distribusiSeverity'));
    }
}
