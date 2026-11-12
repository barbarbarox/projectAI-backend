<?php

namespace App\Http\Controllers;

use App\Http\Middleware\MonitoringCooldown;
use App\Jobs\WebsiteMonitoringCheckJob;
use App\Models\DomainVerification;
use App\Models\WebsiteBaseline;
use App\Models\WebsiteIntegrityAlert;
use App\Models\WebsiteMonitor;
use App\Services\DomainVerificationService;
use App\Services\SSRFProtectionService;
use App\Services\WebsiteIntegrityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebsiteMonitorController extends Controller
{
    private const MAX_MONITORS = 2;
    private const MIN_INTERVAL = 15; // menit

    // ─────────────────────────────────────────────────────────────────────────
    //  Dashboard: daftar semua monitor milik user
    // ─────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $user     = Auth::user();
        $monitors = WebsiteMonitor::with(['currentBaseline', 'latestAlert'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $totalUnread = WebsiteIntegrityAlert::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $cooldownRemaining = MonitoringCooldown::getRemainingSeconds($user->id);

        return view('monitoring.index', compact('monitors', 'totalUnread', 'cooldownRemaining'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Form buat monitor baru
    // ─────────────────────────────────────────────────────────────────────────
    public function create()
    {
        $user = Auth::user();

        // Cek batas maksimal
        $activeCount = WebsiteMonitor::where('user_id', $user->id)->count();
        if ($activeCount >= self::MAX_MONITORS) {
            return redirect()->route('monitoring.index')
                ->with('error', "Batas maksimal " . self::MAX_MONITORS . " monitor telah tercapai. Hapus salah satu monitor untuk membuat yang baru.");
        }

        $cooldownRemaining = MonitoringCooldown::getRemainingSeconds($user->id);

        return view('monitoring.create', compact('cooldownRemaining'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Simpan monitor baru
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $validated = $request->validate([
            'target_url'         => 'required|url|max:2048',
            'nama'               => 'nullable|string|max:100',
            'interval_menit'     => "required|integer|min:" . self::MIN_INTERVAL . "|max:1440",
            'is_dynamic_content' => 'nullable|boolean',
            'threshold_pct'      => 'nullable|integer|min:1|max:50',
            'alert_wa'           => 'nullable|boolean',
            'disclaimer_check'   => 'accepted',
        ], [
            'target_url.required'      => 'URL target wajib diisi.',
            'target_url.url'           => 'Format URL tidak valid. Pastikan menggunakan http:// atau https://',
            'interval_menit.min'       => "Interval minimum adalah " . self::MIN_INTERVAL . " menit.",
            'disclaimer_check.accepted'=> 'Anda harus menyetujui pernyataan penggunaan.',
        ]);

        // Cek batas maksimal monitor
        $activeCount = WebsiteMonitor::where('user_id', $user->id)->count();
        if ($activeCount >= self::MAX_MONITORS) {
            return back()->with('error', "Batas maksimal " . self::MAX_MONITORS . " monitor telah tercapai.");
        }

        // SSRF Protection
        $ssrf = app(SSRFProtectionService::class)->validate($validated['target_url']);
        if (!$ssrf['valid']) {
            return back()->withInput()->withErrors(['target_url' => 'URL tidak diizinkan: ' . $ssrf['reason']]);
        }

        // Verifikasi domain — wajib untuk monitoring berkala
        $domain = DomainVerificationService::extractDomain($validated['target_url']);
        if (!app(DomainVerificationService::class)->isVerified($domain, $user->id)) {
            return redirect()->route('analisis.url.pilih-metode-verifikasi', [
                'url'  => $validated['target_url'],
                'from' => 'monitoring',
            ])->with('info', "Domain *{$domain}* belum diverifikasi. Verifikasi terlebih dahulu untuk mengaktifkan monitoring berkala.");
        }

        // Cek cooldown (middleware sudah cek, ini double-check untuk store)
        if (MonitoringCooldown::isOnCooldown($user->id)) {
            $remaining = MonitoringCooldown::getRemainingSeconds($user->id);
            $minutes   = ceil($remaining / 60);
            return back()->with('error', "Cooldown aktif. Tunggu sekitar {$minutes} menit lagi.");
        }

        // Buat monitor
        $monitor = WebsiteMonitor::create([
            'user_id'            => $user->id,
            'target_url'         => $validated['target_url'],
            'nama'               => $validated['nama'] ?? null,
            'status'             => 'active',
            'interval_menit'     => (int) $validated['interval_menit'],
            'is_dynamic_content' => (bool) ($validated['is_dynamic_content'] ?? false),
            'threshold_pct'      => (int) ($validated['threshold_pct'] ?? 10),
            'alert_wa'           => (bool) ($validated['alert_wa'] ?? true),
            'next_check_at'      => now(), // Langsung check pertama kali
        ]);

        // Dispatch job pertama untuk buat baseline
        WebsiteMonitoringCheckJob::dispatch($monitor->id);

        // Set cooldown 15 menit
        MonitoringCooldown::setCooldown($user->id);

        return redirect()->route('monitoring.show', $monitor->id)
            ->with('success', "Monitor berhasil dibuat! Baseline awal sedang diambil. Pemeriksaan berikutnya dalam {$monitor->interval_menit} menit.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Detail monitor + history alert
    // ─────────────────────────────────────────────────────────────────────────
    public function show(WebsiteMonitor $monitor)
    {
        abort_if($monitor->user_id !== Auth::id(), 403);

        $monitor->load('currentBaseline');

        $alerts = WebsiteIntegrityAlert::where('monitor_id', $monitor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Tandai semua alert monitor ini sebagai sudah dibaca
        WebsiteIntegrityAlert::where('monitor_id', $monitor->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('monitoring.show', compact('monitor', 'alerts'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Quick Defacement Check (tanpa verifikasi, satu kali scan)
    // ─────────────────────────────────────────────────────────────────────────
    public function quickCheck(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'target_url'       => 'required|url|max:2048',
            'disclaimer_check' => 'accepted',
        ], [
            'target_url.required'      => 'URL target wajib diisi.',
            'target_url.url'           => 'Format URL tidak valid.',
            'disclaimer_check.accepted'=> 'Anda harus menyetujui pernyataan penggunaan.',
        ]);

        $url = $request->target_url;

        // SSRF Protection
        $ssrf = app(SSRFProtectionService::class)->validate($url);
        if (!$ssrf['valid']) {
            return back()->withInput()->withErrors(['target_url' => 'URL tidak diizinkan: ' . $ssrf['reason']]);
        }

        // Cek cooldown
        if (MonitoringCooldown::isOnCooldown($user->id)) {
            $remaining = MonitoringCooldown::getRemainingSeconds($user->id);
            $minutes   = ceil($remaining / 60);
            return back()->with('error', "⏱️ Cooldown aktif. Tunggu sekitar {$minutes} menit lagi.");
        }

        // Jalankan check langsung (sync, bukan queue)
        try {
            $integrityService  = app(WebsiteIntegrityService::class);
            $defacementService = app(\App\Services\DefacementDetectionService::class);

            $snapshot = $integrityService->fetchSnapshot($url, false);

            if (isset($snapshot['error'])) {
                return back()->with('error', 'Gagal mengakses URL: ' . $snapshot['error']);
            }

            $defacementResult = $defacementService->detect($snapshot['raw_html'] ?? '', $url);

            // Tentukan severity & alert type
            $severity  = $defacementResult['is_defaced'] ? 'critical' : 'info';
            $alertType = $defacementResult['is_defaced'] ? 'defacement' : 'clean';

            // Simpan ke database sebagai quick_check
            $alert = WebsiteIntegrityAlert::create([
                'monitor_id'  => null,
                'user_id'     => $user->id,
                'check_type'  => 'quick_check',
                'target_url'  => $url,
                'detected_at' => now(),
                'alert_type'  => $alertType,
                'severity'    => $severity,
                'diff_score'  => $defacementResult['is_defaced'] ? 80 : 0,
                'evidence'    => [
                    'defacement'     => $defacementResult,
                    'snapshot_title' => $snapshot['title'] ?? '',
                    'external_scripts' => collect($snapshot['external_scripts'] ?? [])->pluck('src')->toArray(),
                    'external_iframes' => collect($snapshot['external_iframes'] ?? [])->pluck('src')->toArray(),
                ],
                'is_read'     => true, // Quick check langsung dibaca
                'created_at'  => now(),
            ]);

            // Set cooldown
            MonitoringCooldown::setCooldown($user->id);

            return redirect()->route('monitoring.alert.show', $alert->id)
                ->with('success', 'Quick Check selesai!');

        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi error: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Pause / Resume monitor
    // ─────────────────────────────────────────────────────────────────────────
    public function togglePause(WebsiteMonitor $monitor)
    {
        abort_if($monitor->user_id !== Auth::id(), 403);

        $newStatus = $monitor->status === 'active' ? 'paused' : 'active';
        $monitor->update([
            'status'       => $newStatus,
            'next_check_at'=> $newStatus === 'active' ? now()->addMinutes($monitor->interval_menit) : null,
        ]);

        $label = $newStatus === 'active' ? 'diaktifkan kembali' : 'dijeda';
        return back()->with('success', "Monitor berhasil {$label}.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Hapus monitor
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy(WebsiteMonitor $monitor)
    {
        abort_if($monitor->user_id !== Auth::id(), 403);

        // File screenshot baseline juga akan terhapus via cascadeOnDelete di DB
        // Tapi kita hapus file fisiknya juga
        foreach ($monitor->baselines as $baseline) {
            if ($baseline->screenshot_path && \Storage::disk('public')->exists($baseline->screenshot_path)) {
                \Storage::disk('public')->delete($baseline->screenshot_path);
            }
        }

        $monitor->delete(); // Cascade hapus baselines & alerts via FK

        return redirect()->route('monitoring.index')
            ->with('success', 'Monitor berhasil dihapus.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Semua alert user (semua monitor)
    // ─────────────────────────────────────────────────────────────────────────
    public function alerts()
    {
        $user = Auth::user();

        $alerts = WebsiteIntegrityAlert::where('user_id', $user->id)
            ->with('monitor')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Tandai semua sebagai dibaca
        WebsiteIntegrityAlert::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('monitoring.alerts', compact('alerts'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Detail satu alert
    // ─────────────────────────────────────────────────────────────────────────
    public function showAlert(WebsiteIntegrityAlert $alert)
    {
        abort_if($alert->user_id !== Auth::id(), 403);

        $alert->update(['is_read' => true]);
        $alert->load('monitor');

        return view('monitoring.alert-detail', compact('alert'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Acknowledge alert (perubahan wajar)
    // ─────────────────────────────────────────────────────────────────────────
    public function acknowledgeAlert(WebsiteIntegrityAlert $alert)
    {
        abort_if($alert->user_id !== Auth::id(), 403);

        $alert->update(['is_acknowledged' => true, 'is_read' => true]);

        return back()->with('success', 'Alert telah dikonfirmasi sebagai perubahan wajar.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Update baseline ke snapshot terkini (setelah perubahan dikonfirmasi wajar)
    // ─────────────────────────────────────────────────────────────────────────
    public function updateBaseline(WebsiteMonitor $monitor)
    {
        abort_if($monitor->user_id !== Auth::id(), 403);

        // Cek cooldown
        if (MonitoringCooldown::isOnCooldown(Auth::id())) {
            return back()->with('error', 'Cooldown aktif. Tunggu sebentar sebelum memperbarui baseline.');
        }

        // Dispatch job untuk buat baseline baru
        // Tandai semua baseline lama sebagai non-current
        WebsiteBaseline::where('monitor_id', $monitor->id)->update(['is_current' => false]);

        // Set next_check_at ke now agar job segera dijalankan
        $monitor->update(['next_check_at' => now(), 'consecutive_errors' => 0]);
        WebsiteMonitoringCheckJob::dispatch($monitor->id);

        MonitoringCooldown::setCooldown(Auth::id());

        return back()->with('success', 'Baseline sedang diperbarui. Proses ini akan selesai dalam beberapa detik.');
    }
}
