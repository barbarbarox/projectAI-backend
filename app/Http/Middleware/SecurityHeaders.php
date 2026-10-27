<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware SecurityHeaders
 *
 * Menambahkan HTTP security headers penting pada setiap respons.
 * Direkomendasikan berdasarkan audit keamanan (skor header 36/100).
 *
 * CATATAN CSP: Saat ini menggunakan Content-Security-Policy-Report-Only
 * untuk observasi. Setelah memverifikasi tidak ada JS yang terblokir,
 * ubah ke Content-Security-Policy (enforce mode).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // ── HSTS: paksa HTTPS selama 1 tahun ──────────────────────────────
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains; preload'
        );

        // ── Cegah MIME-type sniffing ───────────────────────────────────────
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // ── Frame options: hanya izinkan dari origin yang sama ─────────────
        if (! $response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        // ── Referrer Policy ────────────────────────────────────────────────
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ── Permissions Policy (batasi fitur browser yang tidak dipakai) ───
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );

        // ── X-XSS-Protection: hapus (OWASP sudah tidak merekomendasikan) ──
        $response->headers->remove('X-XSS-Protection');

        // ── Content Security Policy (Report-Only mode) ────────────────────
        // TAHAP OBSERVASI: Monitor dulu, baru enforce setelah tidak ada pelanggaran
        $cspValue = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https:",
            "frame-src 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests",
        ]);

        $response->headers->set('Content-Security-Policy-Report-Only', $cspValue);

        return $response;
    }
}
