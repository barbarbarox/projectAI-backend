<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Handle an incoming request.
     * Update user last_seen_at if logged in (max sekali per menit).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $now = now();
            
            // Update jika belum ada last_seen_at atau jika sudah lebih dari 1 menit
            if (!$user->last_seen_at || $user->last_seen_at->diffInMinutes($now) >= 1) {
                // Gunakan query builder agar tidak memicu event (menghemat performance)
                \DB::table('users')->where('id', $user->id)->update([
                    'last_seen_at' => $now,
                ]);
            }
        }

        return $next($request);
    }
}
