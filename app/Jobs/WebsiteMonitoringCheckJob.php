<?php

namespace App\Jobs;

use App\Models\WebsiteMonitor;
use App\Models\WebsiteBaseline;
use App\Models\WebsiteIntegrityAlert;
use App\Services\WebsiteIntegrityService;
use App\Services\DefacementDetectionService;
use App\Services\GeminiService;
use App\Services\WhatsappService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WebsiteMonitoringCheckJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Jangan retry otomatis — 1x gagal langsung catat error.
     */
    public int $tries   = 1;
    public int $timeout = 90;

    public function __construct(
        protected int $monitorId
    ) {
        // Gunakan queue terpisah agar tidak ganggu scan utama
        $this->onQueue('monitoring');
    }

    public function handle(
        WebsiteIntegrityService $integrityService,
        DefacementDetectionService $defacementService,
        GeminiService $geminiService,
        WhatsappService $waService
    ): void {
        $monitor = WebsiteMonitor::with('currentBaseline', 'user')->find($this->monitorId);

        if (!$monitor) {
            Log::warning("MonitoringJob: monitor #{$this->monitorId} tidak ditemukan.");
            return;
        }

        if ($monitor->status !== 'active') {
            return;
        }

        Log::info("MonitoringJob: mulai cek [{$monitor->target_url}]");

        try {
            // 1. Fetch snapshot terkini
            $snapshot = $integrityService->fetchSnapshot($monitor->target_url, $monitor->is_dynamic_content);

            if (isset($snapshot['error'])) {
                $this->handleError($monitor, $snapshot['error']);
                return;
            }

            // 2. Dapatkan atau buat baseline
            $baseline = $monitor->currentBaseline;

            if (!$baseline) {
                // Belum ada baseline — simpan snapshot pertama sebagai baseline
                $this->createBaseline($monitor, $snapshot, $integrityService);
                $monitor->scheduleNextCheck();
                Log::info("MonitoringJob: baseline awal dibuat untuk monitor #{$monitor->id}");
                return;
            }

            // 3. Tambahkan deteksi defacement keyword ke snapshot
            $defacementResult = $defacementService->detect($snapshot['raw_html'] ?? '', $monitor->target_url);

            // 4. Bandingkan snapshot dengan baseline
            $diff = $integrityService->compareWithBaseline(
                $snapshot,
                $baseline->toArray(),
                $monitor->is_dynamic_content
            );

            // Tambahkan sinyal defacement ke diff jika terdeteksi
            if ($defacementResult['is_defaced']) {
                $diff['has_changes'] = true;
                $diff['diff_score']  = max($diff['diff_score'], 70);
                $diff['severity']    = 'critical';
                $diff['alert_type']  = 'defacement';
                $diff['changes'][]   = [
                    'type'     => 'defacement',
                    'severity' => 'critical',
                    'label'    => 'Keyword Defacement Terdeteksi: ' . $defacementResult['summary'],
                    'keywords' => $defacementResult['detected_signatures'],
                ];
            }

            // 5. Hanya buat alert jika ada perubahan di atas threshold
            if ($diff['has_changes'] && $diff['diff_score'] >= $monitor->threshold_pct) {
                $this->createAlert($monitor, $diff, $snapshot, $defacementResult, $geminiService, $waService);

                // Update baseline jika perubahan > 50% (kemungkinan redesign sah, bukan attack)
                // Tapi tandai untuk review manual terlebih dahulu — jangan auto-update baseline kritis
            }

            // 6. Reset error counter & jadwalkan pemeriksaan berikutnya
            $monitor->update(['consecutive_errors' => 0, 'error_message' => null]);
            $monitor->scheduleNextCheck();

            Log::info("MonitoringJob: selesai [{$monitor->target_url}] diff_score={$diff['diff_score']}");

        } catch (\Throwable $e) {
            $this->handleError($monitor, $e->getMessage());
            Log::error("MonitoringJob error [{$monitor->target_url}]: " . $e->getMessage());
        }
    }

    /**
     * Buat baseline awal dari snapshot pertama.
     */
    protected function createBaseline(WebsiteMonitor $monitor, array $snapshot, WebsiteIntegrityService $integrityService): void
    {
        // Ambil screenshot untuk baseline
        $screenshotPath = $integrityService->captureScreenshot($monitor->target_url);

        WebsiteBaseline::create([
            'monitor_id'          => $monitor->id,
            'snapshot_at'         => now(),
            'content_hash'        => $snapshot['content_hash'],
            'title'               => $snapshot['title'],
            'h1_texts'            => $snapshot['h1_texts'],
            'canonical_url'       => $snapshot['canonical_url'],
            'meta_description'    => $snapshot['meta_description'],
            'link_hash'           => $snapshot['link_hash'],
            'external_scripts'    => $snapshot['external_scripts'],
            'external_iframes'    => $snapshot['external_iframes'],
            'inline_scripts_hash' => $snapshot['inline_scripts_hash'],
            'screenshot_path'     => $screenshotPath,
            'is_current'          => true,
            'created_at'          => now(),
        ]);
    }

    /**
     * Buat alert dan kirim notifikasi WhatsApp.
     */
    protected function createAlert(
        WebsiteMonitor $monitor,
        array $diff,
        array $snapshot,
        array $defacementResult,
        GeminiService $geminiService,
        WhatsappService $waService
    ): void {
        $aiAnalysis  = null;
        $aiRiskScore = null;

        // Panggil AI hanya jika severity >= high
        if (in_array($diff['severity'], ['high', 'critical'])) {
            try {
                $aiResult    = $this->analyzeWithAI($diff, $defacementResult, $monitor, $geminiService);
                $aiAnalysis  = $aiResult['analysis'] ?? null;
                $aiRiskScore = $aiResult['risk_score'] ?? null;
            } catch (\Throwable $e) {
                Log::warning("MonitoringJob: AI analysis gagal: " . $e->getMessage());
            }
        }

        $alert = WebsiteIntegrityAlert::create([
            'monitor_id'    => $monitor->id,
            'user_id'       => $monitor->user_id,
            'check_type'    => 'scheduled',
            'target_url'    => $monitor->target_url,
            'detected_at'   => now(),
            'alert_type'    => $diff['alert_type'],
            'severity'      => $diff['severity'],
            'diff_score'    => $diff['diff_score'],
            'evidence'      => [
                'changes'         => $diff['changes'],
                'defacement'      => $defacementResult,
                'snapshot_title'  => $snapshot['title'],
                'new_scripts'     => collect($snapshot['external_scripts'] ?? [])->pluck('src')->toArray(),
                'new_iframes'     => collect($snapshot['external_iframes'] ?? [])->pluck('src')->toArray(),
            ],
            'ai_analysis'   => $aiAnalysis,
            'ai_risk_score' => $aiRiskScore,
            'is_read'       => false,
            'created_at'    => now(),
        ]);

        // Kirim WhatsApp jika diaktifkan
        if ($monitor->alert_wa && $monitor->user?->phone) {
            $sent = $waService->sendIntegrityAlert($monitor->user->phone, [
                'monitor_name' => $monitor->nama ?? $monitor->clean_url,
                'target_url'   => $monitor->target_url,
                'alert_type'   => $alert->alert_type_label,
                'severity'     => $diff['severity'],
                'diff_score'   => $diff['diff_score'],
                'ai_summary'   => $aiAnalysis ? substr($aiAnalysis, 0, 300) : null,
                'alert_id'     => $alert->id,
            ]);

            if ($sent) {
                $alert->update(['notif_wa_sent_at' => now()]);
            }
        }
    }

    /**
     * Analisis perubahan menggunakan Gemini AI.
     */
    protected function analyzeWithAI(
        array $diff,
        array $defacementResult,
        WebsiteMonitor $monitor,
        GeminiService $geminiService
    ): array {
        $changesText = collect($diff['changes'])->map(fn($c) => "- [{$c['severity']}] {$c['label']}")->implode("\n");

        $prompt = <<<PROMPT
Kamu adalah analis keamanan web. Analisis perubahan yang terdeteksi pada website berikut:

**Target:** {$monitor->target_url}
**Tipe Monitoring:** Website Integrity Check
**Diff Score:** {$diff['diff_score']}/100

**Perubahan yang Terdeteksi:**
{$changesText}

**Deteksi Defacement Keywords:** {$defacementResult['summary']}

Berikan analisis singkat dalam format JSON dengan struktur:
{
  "analysis": "Penjelasan singkat (max 400 karakter) apa yang berubah, apakah mencurigakan, dan kemungkinan dampaknya",
  "risk_score": 0-100,
  "kemungkinan_penyebab": "Kemungkinan penyebab perubahan",
  "rekomendasi": "Tindakan yang disarankan (1-2 kalimat)"
}

PENTING: Jangan klaim bahwa website PASTI diretas. Gunakan bahasa kehati-hatian ("terindikasi", "perlu diverifikasi", "perlu investigasi").
PROMPT;

        $raw = $geminiService->generate($prompt, null, true, $monitor->user_id);

        // Parse JSON response
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            // Coba ekstrak JSON dari respons yang mungkin ada teks tambahan
            if (preg_match('/\{.*\}/s', $raw, $m)) {
                $decoded = json_decode($m[0], true);
            }
        }

        return [
            'analysis'   => $decoded['analysis']   ?? $raw,
            'risk_score' => $decoded['risk_score']  ?? null,
        ];
    }

    /**
     * Tangani error saat fetch/check.
     */
    protected function handleError(WebsiteMonitor $monitor, string $errorMessage): void
    {
        $newErrorCount = $monitor->consecutive_errors + 1;
        $newStatus     = $newErrorCount >= 5 ? 'error' : 'active';

        $monitor->update([
            'consecutive_errors' => $newErrorCount,
            'error_message'      => $errorMessage,
            'status'             => $newStatus,
            'next_check_at'      => now()->addMinutes($monitor->interval_menit),
            'last_checked_at'    => now(),
        ]);

        // Jika 5x error berturut-turut → beri tahu user
        if ($newStatus === 'error' && $monitor->user?->phone) {
            try {
                app(WhatsappService::class)->sendText(
                    $monitor->user->phone,
                    "⚠️ *RedSim Monitoring Error*\n\nMonitor *{$monitor->nama}* ({$monitor->clean_url}) " .
                    "gagal diakses 5x berturut-turut dan telah dijeda otomatis.\n\n" .
                    "Error: {$errorMessage}\n\nSilakan periksa monitor Anda di dashboard RedSim."
                );
            } catch (\Throwable $e) {
                // Abaikan error notifikasi
            }
        }
    }
}
