<?php

use App\Http\Middleware\CekRateLimitScan;
use App\Http\Middleware\CekAdmin;
use App\Http\Middleware\CekPemilikScan;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\UpdateLastSeen::class,
        ]);
        
        $middleware->alias([
            'cek.ratelimit' => \App\Http\Middleware\CekRateLimitScan::class,
            'cek.admin' => \App\Http\Middleware\CekAdmin::class,
            'scan.owner' => \App\Http\Middleware\CekPemilikScan::class,
            'throttle.login' => \App\Http\Middleware\ThrottleLogin::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->expectsJson()) {
                return null;
            }
            session()->flash('error', 'Login dulu boss! 🔐 Silakan masuk untuk mengakses fitur ini.');
            return route('masuk');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
