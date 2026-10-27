<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * MLPredictionService — HTTP client ke Python Ensemble ML microservice v2.0
 *
 * Upgrade dari single Random Forest → Weighted Soft Voting Ensemble
 *   - Random Forest (Minggu 5): Macro F1 = 0.9320, weight = 0.40
 *   - XGBoost       (Minggu 6): Macro F1 = 0.9595, weight = 0.60
 *
 * Backward compatible: field ml_predicted_severity & ml_confidence tetap ada.
 * Field baru: ensemble_severity, rf.severity, xgb.severity, models_agree, dll.
 *
 * Graceful degradation: jika ML service tidak tersedia, scan tetap berjalan.
 */
class MLPredictionService
{
    protected string $baseUrl;
    protected int    $timeout;
    protected bool   $enabled;

    public function __construct()
    {
        $this->baseUrl = config('services.ml_service.url', env('ML_SERVICE_URL', 'http://127.0.0.1:5001'));
        $this->timeout = (int) config('services.ml_service.timeout', 8);
        $this->enabled = (bool) config('services.ml_service.enabled', true);
    }

    /**
     * Prediksi severity dari satu temuan menggunakan Ensemble ML (RF + XGBoost).
     *
     * @param  array  $temuan Data temuan: tipe, tingkat_keparahan, cwe_id, deskripsi, dll.
     * @return array|null Hasil prediksi atau null jika service tidak tersedia.
     *
     * Return structure (v2.0):
     * [
     *   // Ensemble (primary)
     *   'ensemble_severity'      => 'CRITICAL',
     *   'ensemble_confidence'    => 0.9234,
     *   'ensemble_probabilities' => ['CRITICAL' => 0.92, ...],
     *
     *   // Per-model detail
     *   'rf'  => ['severity' => 'CRITICAL', 'confidence' => 0.87, 'class_probabilities' => [...]],
     *   'xgb' => ['severity' => 'CRITICAL', 'confidence' => 0.96, 'class_probabilities' => [...]],
     *   'models_agree'           => true,
     *   'disagreement_delta'     => 0.09,
     *
     *   // LLM konsensus
     *   'llm_severity_normalized'=> 'CRITICAL',
     *   'konsensus'              => 'CRITICAL',
     *   'agreement'              => true,
     *
     *   // Feature importance
     *   'top_features'           => [...],
     *
     *   // Backward compat
     *   'ml_predicted_severity'  => 'CRITICAL',   // same as ensemble_severity
     *   'ml_confidence'          => 0.9234,        // same as ensemble_confidence
     *   'class_probabilities'    => [...],          // same as ensemble_probabilities
     * ]
     */
    public function predict(array $temuan): ?array
    {
        if (! $this->enabled) {
            Log::debug('[MLPredictionService] Disabled via config.');
            return null;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->post("{$this->baseUrl}/predict", [
                    'tipe'              => $temuan['tipe']               ?? 'lainnya',
                    'tingkat_keparahan' => $temuan['tingkat_keparahan']  ?? 'sedang',
                    'cwe_id'            => $temuan['cwe_id']             ?? '',
                    'cve_id'            => $temuan['cve_id']             ?? '',
                    'deskripsi'         => substr($temuan['deskripsi'] ?? '', 0, 2000),
                    'tautan_referensi'  => $temuan['tautan_referensi']  ?? '',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('[MLPredictionService] Ensemble prediksi berhasil', [
                    'tipe'               => $temuan['tipe']              ?? '-',
                    'llm_severity'       => $temuan['tingkat_keparahan'] ?? '-',
                    'rf_severity'        => data_get($data, 'rf.severity',    '-'),
                    'xgb_severity'       => data_get($data, 'xgb.severity',   '-'),
                    'ensemble_severity'  => $data['ensemble_severity']   ?? '-',
                    'ensemble_confidence'=> $data['ensemble_confidence']  ?? 0,
                    'models_agree'       => $data['models_agree']        ?? null,
                    'konsensus'          => $data['konsensus']           ?? '-',
                    'agreement'          => $data['agreement']           ?? false,
                ]);

                // Kirim alert WhatsApp jika CRITICAL terdeteksi dengan confidence tinggi
                $this->maybeAlertCritical($data, $temuan);

                return $data;
            }

            Log::warning('[MLPredictionService] Response tidak sukses', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 200),
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('[MLPredictionService] Service tidak terjangkau — scan tetap berlanjut tanpa ML.', [
                'error' => $e->getMessage(),
                'url'   => $this->baseUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('[MLPredictionService] Error tidak terduga: ' . $e->getMessage());
        }

        return null; // Graceful degradation
    }

    /**
     * Prediksi batch — loop per temuan.
     *
     * @param  array  $temuanList
     * @return array  Array of prediction results (null untuk yang gagal)
     */
    public function predictBatch(array $temuanList): array
    {
        $results = [];
        foreach ($temuanList as $temuan) {
            $results[] = $this->predict($temuan);
        }
        return $results;
    }

    /**
     * Cek apakah ML service berjalan dan KEDUA model siap.
     * Di-cache 30 detik untuk menghindari overhead pada setiap scan.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if (! $this->enabled) {
            return false;
        }

        return Cache::remember('ml_service_available', 30, function () {
            try {
                $response = Http::timeout(3)->get("{$this->baseUrl}/health");
                $isOk = $response->successful() && ($response->json('status') === 'ok');

                if ($isOk) {
                    Log::debug('[MLPredictionService] Health check OK — Ensemble v2.0 aktif');
                } else {
                    Log::warning('[MLPredictionService] Health check gagal', [
                        'status' => $response->status(),
                    ]);
                }

                return $isOk;
            } catch (\Exception) {
                return false;
            }
        });
    }

    /**
     * Ambil info model dari ML service (untuk ditampilkan di dashboard).
     * Endpoint /info mengembalikan detail RF + XGBoost + ensemble config.
     *
     * @return array|null
     */
    public function getModelInfo(): ?array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/info");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('[MLPredictionService] Gagal ambil model info: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Ambil ringkasan model aktif (endpoint /models).
     *
     * @return array|null
     */
    public function getActiveModels(): ?array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/models");
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('[MLPredictionService] Gagal ambil model list: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Helper: Format severity ke label Indonesia untuk UI RedSim.
     *
     * @param  string  $severity  English: CRITICAL/HIGH/MEDIUM/LOW/NONE
     * @return string  Indonesian: kritis/tinggi/sedang/rendah/info
     */
    public static function toIndonesian(string $severity): string
    {
        return match (strtoupper(trim($severity))) {
            'CRITICAL' => 'kritis',
            'HIGH'     => 'tinggi',
            'MEDIUM'   => 'sedang',
            'LOW'      => 'rendah',
            default    => 'info',
        };
    }

    /**
     * Kirim notifikasi WhatsApp jika Ensemble mendeteksi CRITICAL dengan confidence tinggi.
     * Fase 3: Alert otomatis untuk temuan berbahaya.
     *
     * @param  array  $prediction  Hasil prediksi dari ML service
     * @param  array  $temuan      Data temuan asli
     * @return void
     */
    protected function maybeAlertCritical(array $prediction, array $temuan): void
    {
        $ensembleSev  = $prediction['ensemble_severity']  ?? '';
        $ensembleConf = $prediction['ensemble_confidence'] ?? 0;
        $konsensus    = $prediction['konsensus']           ?? '';
        $modelsAgree  = $prediction['models_agree']        ?? false;

        // Hanya alert jika: Ensemble=CRITICAL, Konsensus=CRITICAL, confidence>85%, kedua model sepakat
        if ($ensembleSev !== 'CRITICAL' || $konsensus !== 'CRITICAL' || $ensembleConf < 0.85 || ! $modelsAgree) {
            return;
        }

        // Throttle: max 1 alert per tipe temuan per 10 menit
        $cacheKey = 'ml_alert_critical_' . md5($temuan['tipe'] ?? '');
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, true, 600);

        try {
            /** @var \App\Services\WhatsappService $wa */
            $wa = app(WhatsappService::class);

            $rfConf  = round(($prediction['rf']['confidence']  ?? 0) * 100, 1);
            $xgbConf = round(($prediction['xgb']['confidence'] ?? 0) * 100, 1);
            $conf    = round($ensembleConf * 100, 1);
            $tipe    = $temuan['tipe']  ?? '-';
            $cweId   = $temuan['cwe_id'] ?? '-';
            $desc    = substr($temuan['deskripsi'] ?? '', 0, 200);

            $message = "🚨 *RedSim — Peringatan CRITICAL* 🚨\n\n"
                . "Ensemble ML mendeteksi temuan berkategori KRITIS\n\n"
                . "📊 *Hasil Analisis:*\n"
                . "• Tipe: {$tipe}\n"
                . "• CWE: {$cweId}\n"
                . "• Ensemble: CRITICAL ({$conf}%)\n"
                . "• Random Forest: CRITICAL ({$rfConf}%)\n"
                . "• XGBoost: CRITICAL ({$xgbConf}%)\n"
                . "• Konsensus: ✅ Semua model sepakat\n\n"
                . "📝 *Deskripsi:*\n"
                . "{$desc}...\n\n"
                . "⚡ *Rekomendasi:* Patching segera dalam 24–48 jam\n\n"
                . "_RedSim Ensemble ML Alert | " . now()->format('d M Y H:i') . "_";

            $adminPhone = config('services.whatsapp.admin_number') ?: env('OPENWA_SENDER_NUMBER');
            if (!empty($adminPhone)) {
                $wa->sendText($adminPhone, $message);
                Log::info('[MLPredictionService] WA Alert CRITICAL terkirim', [
                    'tipe'       => $tipe,
                    'confidence' => $conf . '%',
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[MLPredictionService] Gagal kirim WA alert: ' . $e->getMessage());
        }
    }
}
