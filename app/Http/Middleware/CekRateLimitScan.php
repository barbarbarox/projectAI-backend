<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CekRateLimitScan
{
    /**
     * Maks scan per menit (anti-burst/spam).
     */
    private const MAX_PER_MINUTE = 3;

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // === Per-menit burst throttle ===
        $minuteKey   = "scan_burst:{$user->id}:" . floor(time() / 60);
        $burstCount  = (int) Cache::get($minuteKey, 0);

        if ($burstCount >= self::MAX_PER_MINUTE) {
            return back()->with('error',
                'Terlalu cepat! Mohon tunggu beberapa saat sebelum melakukan scan lagi (maks ' . self::MAX_PER_MINUTE . ' scan/menit).'
            );
        }

        // Increment burst counter (TTL 90 detik untuk jendela yang aman)
        Cache::put($minuteKey, $burstCount + 1, 90);

        // === Daily limit check ===
        // Reset counter jika sudah hari baru
        if ($user->last_scan_reset === null || $user->last_scan_reset->lt(today())) {
            $user->update([
                'scan_count_today' => 0,
                'last_scan_reset'  => today(),
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
