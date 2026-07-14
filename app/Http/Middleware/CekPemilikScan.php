<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekPemilikScan
{
    public function handle(Request $request, Closure $next): Response
    {
        $scan = $request->route('scan');

        if (!$scan) {
            return $next($request);
        }

        // Jika scan bukan milik user yang login
        if ($scan->user_id !== auth()->id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Akses ditolak.',
                ], 403);
            }
            abort(403, 'Kamu tidak memiliki akses ke laporan ini.');
        }

        return $next($request);
    }
}
