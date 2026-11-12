<?php

namespace App\Console\Commands;

use App\Jobs\WebsiteMonitoringCheckJob;
use App\Models\WebsiteMonitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitoringDispatchCommand extends Command
{
    protected $signature   = 'monitor:dispatch';
    protected $description = 'Dispatch monitoring check jobs untuk semua monitor yang sudah jatuh tempo.';

    public function handle(): void
    {
        $due = WebsiteMonitor::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('next_check_at')
                  ->orWhere('next_check_at', '<=', now());
            })
            ->get();

        if ($due->isEmpty()) {
            return;
        }

        $this->info("Mendispatch {$due->count()} monitoring job...");

        foreach ($due as $monitor) {
            WebsiteMonitoringCheckJob::dispatch($monitor->id);
            Log::info("monitor:dispatch → job dispatched untuk monitor #{$monitor->id} [{$monitor->target_url}]");
        }

        $this->info("Selesai.");
    }
}
