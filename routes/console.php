<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ──── Website Integrity Monitoring Scheduler ────────────────────────────────
// Dispatch monitoring jobs tiap menit (job hanya berjalan jika monitor jatuh tempo)
Schedule::command('monitor:dispatch')
    ->everyMinute()
    ->withoutOverlapping()  // Pastikan tidak tumpang tindih jika run lebih dari 1 menit
    ->runInBackground();

// Cleanup screenshot & baseline usang tiap hari jam 03:00
Schedule::command('monitor:cleanup')
    ->dailyAt('03:00')
    ->withoutOverlapping();

