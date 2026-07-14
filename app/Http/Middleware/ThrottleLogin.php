<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLogin
{
    /**
     * Rate Limiting Login — Escalating Block Duration.
     *
     * Setiap kali login gagal berulang, durasi blokir meningkat:
     * - 5 percobaan gagal  → blokir 2 menit
     * - 10 percobaan gagal → blokir 5 menit
     * - 15 percobaan gagal → blokir 15 menit
     * - 20+ percobaan gagal → blokir 30 menit
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya berlaku untuk POST request (submit form login)
        if ($request->isMethod('post')) {
            $key = $this->getThrottleKey($request);
            $lockKey = $key . ':locked_until';
            $attemptsKey = $key . ':attempts';

            // Cek apakah sedang diblokir
            $lockedUntil = Cache::get($lockKey);
            if ($lockedUntil && now()->lt($lockedUntil)) {
                $sisaDetik = now()->diffInSeconds($lockedUntil);
                $sisaMenit = ceil($sisaDetik / 60);
                $sisaFormatted = $this->formatSisa($sisaDetik);

                Log::warning("Login throttled for IP: {$request->ip()}, email: {$request->input('email')}");

                return back()
                    ->withErrors([
                        'throttle' => "⛔ Sesi Anda diblokir selama {$sisaFormatted} karena terlalu banyak percobaan login yang gagal. Silakan coba lagi nanti.",
                    ])
                    ->onlyInput('email');
            }
        }

        $response = $next($request);

        // Setelah proses login, cek apakah gagal (redirect back = gagal)
        if ($request->isMethod('post') && $response->isRedirect()) {
            $key = $this->getThrottleKey($request);
            $attemptsKey = $key . ':attempts';
            $lockKey = $key . ':locked_until';

            // Cek apakah redirect back (login gagal) atau ke dashboard (sukses)
            $redirectUrl = $response->headers->get('Location');
            $masukUrl = route('masuk');

            // Jika redirect ke halaman selain masuk = login sukses, reset counter
            if (!str_contains($redirectUrl, '/masuk') && !str_contains($redirectUrl, '/login')) {
                Cache::forget($attemptsKey);
                Cache::forget($lockKey);
            } else {
                // Login gagal — increment attempts
                $attempts = Cache::get($attemptsKey, 0) + 1;
                Cache::put($attemptsKey, $attempts, now()->addHours(1));

                // Tentukan durasi blokir berdasarkan jumlah percobaan
                $blockDuration = $this->getBlockDuration($attempts);
                if ($blockDuration > 0) {
                    $lockedUntil = now()->addMinutes($blockDuration);
                    Cache::put($lockKey, $lockedUntil, $lockedUntil);

                    Log::warning("Login blocked: IP={$request->ip()}, Email={$request->input('email')}, Attempts={$attempts}, BlockMinutes={$blockDuration}");

                    $sisaFormatted = $this->formatSisa($blockDuration * 60);

                    return back()
                        ->withErrors([
                            'throttle' => "⛔ Sesi Anda diblokir selama {$sisaFormatted} karena {$attempts} kali percobaan login gagal. Waktu blokir akan meningkat jika kesalahan terus terjadi.",
                        ])
                        ->onlyInput('email');
                }
            }
        }

        return $response;
    }

    /**
     * Tentukan durasi blokir berdasarkan jumlah percobaan gagal.
     */
    protected function getBlockDuration(int $attempts): int
    {
        return match (true) {
            $attempts >= 20 => 30,   // 30 menit
            $attempts >= 15 => 15,   // 15 menit
            $attempts >= 10 => 5,    // 5 menit
            $attempts >= 5  => 2,    // 2 menit
            default => 0,            // Belum diblokir
        };
    }

    /**
     * Generate throttle key berdasarkan IP + email.
     */
    protected function getThrottleKey(Request $request): string
    {
        $email = strtolower($request->input('email', 'unknown'));
        $ip = $request->ip();
        return 'login_throttle:' . sha1($ip . '|' . $email);
    }

    /**
     * Format sisa waktu blokir ke format yang user-friendly.
     */
    protected function formatSisa(int $detik): string
    {
        if ($detik >= 60) {
            $menit = ceil($detik / 60);
            return "{$menit} menit";
        }
        return "{$detik} detik";
    }
}
