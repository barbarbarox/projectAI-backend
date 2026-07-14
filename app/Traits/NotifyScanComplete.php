<?php

namespace App\Traits;

use App\Models\Scan;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;

trait NotifyScanComplete
{
    /**
     * Kirim notifikasi scan selesai via WhatsApp.
     */
    protected function notifyScanCompleteViaWa(Scan $scan): void
    {
        try {
            $user = $scan->user;
            if (!$user || empty($user->phone)) return;

            $fonnte = app(FonnteService::class);
            $fonnte->sendScanComplete(
                $user->phone,
                $user->name,
                $scan->target ?? 'Unknown',
                ucfirst($scan->tipe_scan ?? 'scan'),
                $scan->skor_keamanan
            );
        } catch (\Exception $e) {
            Log::warning("Gagal kirim notifikasi scan WA: " . $e->getMessage());
        }
    }
}
