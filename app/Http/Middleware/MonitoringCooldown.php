<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class MonitoringCooldown
{
    /**
     * Cooldown dalam detik (15 menit).
     */
    private const COOLDOWN_SECONDS = 900;

    public function handle(Request $request, Closure $next): Response
    {
        $user        = Auth::user();
        $cooldownKey = "monitor_cooldown:{$user->id}";
        $expiresKey  = "monitor_cooldown_expires:{$user->id}";

        if (Cache::has($cooldownKey)) {
            $expiresAt = Cache::get($expiresKey, time());
            $remaining = max(0, $expiresAt - time());
            $minutes   = floor($remaining / 60);
            $seconds   = $remaining % 60;

            $formatted = $minutes > 0
                ? "{$minutes} menit {$seconds} detik"
                : "{$seconds} detik";

            return back()->with('error',
                "⏱️ Cooldown aktif. Silakan tunggu <strong>{$formatted}</strong> lagi sebelum memulai monitoring scan berikutnya."
            )->with('cooldown_remaining', $remaining);
        }

        return $next($request);
    }

    /**
     * Set cooldown setelah scan berhasil dimulai.
     * Dipanggil dari controller setelah job berhasil di-dispatch.
     */
    public static function setCooldown(int $userId): void
    {
        $expiresAt = time() + self::COOLDOWN_SECONDS;
        Cache::put("monitor_cooldown:{$userId}", true, self::COOLDOWN_SECONDS);
        Cache::put("monitor_cooldown_expires:{$userId}", $expiresAt, self::COOLDOWN_SECONDS);
    }

    /**
     * Cek sisa cooldown tanpa memblokir (untuk UI).
     */
    public static function getRemainingSeconds(int $userId): int
    {
        $expiresAt = Cache::get("monitor_cooldown_expires:{$userId}", 0);
        return max(0, $expiresAt - time());
    }

    /**
     * Cek apakah user sedang dalam cooldown.
     */
    public static function isOnCooldown(int $userId): bool
    {
        return Cache::has("monitor_cooldown:{$userId}");
    }
}
