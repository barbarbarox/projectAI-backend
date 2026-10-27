<?php

namespace App\Http\Controllers\Analisis;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use App\Services\ThreatHuntService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ThreatHuntController extends Controller
{
    protected ThreatHuntService $huntService;

    public function __construct(ThreatHuntService $huntService)
    {
        $this->huntService = $huntService;
    }

    /**
     * Tampilkan halaman utama Threat Hunt.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $trendingCves = ThreatHuntService::TRENDING_CVES;

        // Ambil daftar scan selesai untuk dropdown filter target
        $userScans = Scan::where('user_id', $user->id)
            ->where('status', 'selesai')
            ->latest('selesai_at')
            ->limit(50)
            ->get(['id', 'target', 'nama_file', 'selesai_at', 'skor_keamanan']);

        $initialQuery = $request->query('cve') ?? $request->query('q') ?? '';
        $initialScanId = $request->query('scan_id');

        $initialResults = null;
        if (!empty($initialQuery)) {
            $initialResults = $this->huntService->hunt($initialQuery, $user->id, $initialScanId);
        }

        return view('analisis.threat-hunt.index', [
            'trendingCves'   => $trendingCves,
            'userScans'      => $userScans,
            'initialQuery'   => $initialQuery,
            'initialScanId'  => $initialScanId,
            'initialResults' => $initialResults,
        ]);
    }

    /**
     * Endpoint API / AJAX untuk eksekusi Threat Hunt secara dinamis.
     */
    public function execute(Request $request): JsonResponse
    {
        $request->validate([
            'query'   => 'required|string|max:100',
            'scan_id' => 'nullable|uuid',
        ]);

        $query = $request->input('query');
        $scanId = $request->input('scan_id');

        $results = $this->huntService->hunt($query, $request->user()->id, $scanId);

        return response()->json([
            'success' => true,
            'data'    => $results,
        ]);
    }
}
