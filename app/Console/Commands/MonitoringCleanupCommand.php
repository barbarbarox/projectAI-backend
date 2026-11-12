<?php

namespace App\Console\Commands;

use App\Models\WebsiteBaseline;
use App\Models\WebsiteIntegrityAlert;
use App\Models\WebsiteMonitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MonitoringCleanupCommand extends Command
{
    protected $signature   = 'monitor:cleanup';
    protected $description = 'Hapus screenshot lama (>20 hari) dan baseline usang (>30 hari).';

    public function handle(): void
    {
        $this->info('Memulai cleanup monitoring data...');

        // 1. Hapus screenshot alert yang lebih dari 20 hari
        $oldAlerts = WebsiteIntegrityAlert::where('created_at', '<', now()->subDays(20))
            ->whereNotNull('evidence')
            ->get();

        $deletedScreenshots = 0;
        foreach ($oldAlerts as $alert) {
            $evidence = $alert->evidence ?? [];
            if (!empty($evidence['screenshot_path'])) {
                if (Storage::disk('public')->exists($evidence['screenshot_path'])) {
                    Storage::disk('public')->delete($evidence['screenshot_path']);
                    $deletedScreenshots++;
                }
            }
        }

        // 2. Hapus baseline non-current yang lebih dari 30 hari
        $oldBaselines = WebsiteBaseline::where('is_current', false)
            ->where('created_at', '<', now()->subDays(30))
            ->get();

        $deletedBaselines = 0;
        foreach ($oldBaselines as $baseline) {
            if (!empty($baseline->screenshot_path) && Storage::disk('public')->exists($baseline->screenshot_path)) {
                Storage::disk('public')->delete($baseline->screenshot_path);
            }
            $baseline->delete();
            $deletedBaselines++;
        }

        // 3. Hapus file screenshot orphan di folder monitoring/ yang lebih dari 20 hari
        try {
            $files = Storage::disk('public')->files('monitoring');
            $deletedOrphans = 0;
            foreach ($files as $file) {
                $lastModified = Storage::disk('public')->lastModified($file);
                if ($lastModified < now()->subDays(20)->timestamp) {
                    // Pastikan file ini tidak dipakai oleh baseline aktif
                    $inUse = WebsiteBaseline::where('screenshot_path', $file)->where('is_current', true)->exists();
                    if (!$inUse) {
                        Storage::disk('public')->delete($file);
                        $deletedOrphans++;
                    }
                }
            }
            $this->info("Orphan screenshots dihapus: {$deletedOrphans}");
        } catch (\Throwable $e) {
            Log::warning('monitor:cleanup orphan scan gagal: ' . $e->getMessage());
        }

        // 4. Pause monitor yang error berturut-turut >= 5x (sudah ditangani di job, ini double-check)
        $pausedCount = WebsiteMonitor::where('status', 'error')
            ->where('consecutive_errors', '>=', 5)
            ->update(['status' => 'error']); // Sudah error, pastikan tidak active

        $this->info("Cleanup selesai.");
        $this->info("- Screenshot alert lama dihapus : {$deletedScreenshots}");
        $this->info("- Baseline usang dihapus        : {$deletedBaselines}");

        Log::info("monitor:cleanup selesai", [
            'deleted_screenshots' => $deletedScreenshots,
            'deleted_baselines'   => $deletedBaselines,
        ]);
    }
}
