<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekRateLimitScan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Reset counter jika sudah hari baru
        if ($user->last_scan_reset === null || $user->last_scan_reset->lt(today())) {
            $user->update([
                'scan_count_today' => 0,
                'last_scan_reset' => today(),
            ]);
            $user->refresh();
        }

        $batas = (int) env('MAX_SCANS_PER_DAY_FREE', 10);

        if ($user->scan_count_today >= $batas && $user->tier === 'gratis') {
            return back()->with('error', "Batas scan harian tercapai ({$batas} scan/hari). Coba lagi besok.");
        }

        return $next($request);
    }
}
