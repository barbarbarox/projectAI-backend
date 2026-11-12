<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $sessionId;
    protected string $senderNumber;

    public function __construct()
    {
        // Semua konfigurasi bisa diatur dari admin panel (tabel app_settings), fallback ke .env
        $this->baseUrl = rtrim($this->getSettingValue('openwa_base_url', env('OPENWA_BASE_URL', 'http://localhost:3000')), '/');
        $this->apiKey = $this->getSettingValue('openwa_api_key', env('OPENWA_API_KEY', ''));
        $this->sessionId = $this->getSettingValue('openwa_session_id', env('OPENWA_SESSION_ID', 'default'));
        $this->senderNumber = $this->getSettingValue('wa_sender_number', env('OPENWA_SENDER_NUMBER', ''));
    }

    /**
     * Ambil nilai setting dari database, fallback ke default.
     */
    protected function getSettingValue(string $key, mixed $default = null): string
    {
        try {
            return AppSetting::getValue($key, $default) ?? $default;
        } catch (\Exception $e) {
            return $default ?? '';
        }
    }


    /**
     * Alias method untuk sendText agar kompatibel dengan pemanggil service.
     */
    public function sendText(string $phone, string $message): bool
    {
        return $this->sendMessage($phone, $message);
    }

    /**
     * Kirim pesan WhatsApp via OpenWA API Gateway.
     *
     * @param string $phone Nomor telepon tujuan (format: 628xxxxxxxxx)
     * @param string $message Isi pesan
     * @return bool
     */
    public function sendMessage(string $phone, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::error('OpenWA API key belum dikonfigurasi.');
            return false;
        }

        // Pastikan format nomor menggunakan @c.us suffix untuk OpenWA
        $chatId = $this->formatChatId($phone);

        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->getApiUrl("/sessions/{$this->sessionId}/messages/send-text"), [
                'chatId' => $chatId,
                'text' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("OpenWA pesan terkirim ke: {$phone}", ['response' => $data]);
                return true;
            }

            Log::error("OpenWA HTTP error: {$response->status()} - {$response->body()}");
            return false;
        } catch (\Exception $e) {
            Log::error("OpenWA send failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format nomor telepon ke chatId OpenWA (628xxx → 628xxx@c.us)
     */
    protected function formatChatId(string $phone): string
    {
        // Hapus karakter non-numerik
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Konversi 08xx → 628xx
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Konversi +62 → 62 (jika masih ada prefix +)
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        return $phone . '@c.us';
    }

    /**
     * Pastikan Base URL selalu diakhiri dengan /api jika belum ada
     */
    protected function getApiUrl(string $endpoint): string
    {
        $base = rtrim($this->baseUrl, '/');
        if (!str_ends_with($base, '/api')) {
            $base .= '/api';
        }
        return $base . $endpoint;
    }

    /**
     * Cek status koneksi sesi WhatsApp di OpenWA.
     */
    public function checkSessionStatus(): array
    {
        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
            ])->get($this->getApiUrl("/sessions/{$this->sessionId}"));

            if ($response->successful()) {
                $json = $response->json();
                if (is_array($json)) {
                    return $json;
                }
                return ['status' => 'error', 'message' => 'Response bukan JSON. Pastikan Base URL sudah benar dan mengarah ke API.'];
            }

            return ['status' => 'error', 'message' => $response->body()];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Kirim kode OTP via WhatsApp.
     */
    public function sendOtp(string $phone, string $otpCode): bool
    {
        $message = "🔐 *Kode OTP RedSim*\n\n"
            . "Kode verifikasi Anda: *{$otpCode}*\n\n"
            . "Kode ini berlaku selama 5 menit.\n"
            . "Jangan bagikan kode ini kepada siapapun.\n\n"
            . "— Tim RedSim";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Kirim notifikasi login berhasil via WhatsApp.
     */
    public function sendLoginNotification(string $phone, string $userName, string $method = 'email'): bool
    {
        $waktu = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB';

        $message = "🔔 *Notifikasi Login RedSim*\n\n"
            . "Halo *{$userName}*,\n\n"
            . "Akun Anda baru saja berhasil login ke sistem RedSim.\n\n"
            . "📅 Waktu: {$waktu}\n"
            . "🔑 Metode: {$method}\n\n"
            . "Jika ini bukan Anda, segera amankan akun Anda.\n\n"
            . "— Tim RedSim";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Kirim notifikasi scan selesai via WhatsApp.
     */
    public function sendScanComplete(string $phone, string $userName, string $target, string $tipeScan, ?int $skorKeamanan = null): bool
    {
        $waktu = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB';
        $skorText = $skorKeamanan !== null ? "{$skorKeamanan}/100" : 'N/A';

        $message = "✅ *Hasil Scan RedSim*\n\n"
            . "Halo *{$userName}*,\n\n"
            . "Scan Anda telah selesai diproses!\n\n"
            . "📌 Target: {$target}\n"
            . "🔍 Tipe: {$tipeScan}\n"
            . "🛡️ Skor Keamanan: {$skorText}\n"
            . "📅 Waktu: {$waktu}\n\n"
            . "Silakan cek dashboard RedSim untuk melihat hasil lengkap.\n\n"
            . "— Tim RedSim";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Kirim link reset password via WhatsApp.
     */
    public function sendPasswordResetLink(string $phone, string $resetUrl): bool
    {
        $message = "🔐 *Reset Password RedSim*\n\n"
            . "Anda meminta untuk mereset kata sandi akun RedSim Anda.\n\n"
            . "Klik link berikut untuk mengatur kata sandi baru:\n"
            . "{$resetUrl}\n\n"
            . "⏰ Link ini berlaku selama *5 menit*.\n"
            . "Jika Anda tidak meminta reset password, abaikan pesan ini.\n\n"
            . "— Tim RedSim";

        return $this->sendMessage($phone, $message);
    }

    /**
     * Kirim notifikasi alert integritas website via WhatsApp.
     *
     * @param string $phone     Nomor tujuan
     * @param array  $alertData ['monitor_name','target_url','alert_type','severity','diff_score','ai_summary','alert_id']
     */
    public function sendIntegrityAlert(string $phone, array $alertData): bool
    {
        $icon = match ($alertData['severity'] ?? 'info') {
            'critical' => '🚨',
            'high'     => '⚠️',
            'warning'  => '⚡',
            default    => 'ℹ️',
        };

        $severityLabel = match ($alertData['severity'] ?? 'info') {
            'critical' => 'KRITIS',
            'high'     => 'TINGGI',
            'warning'  => 'PERINGATAN',
            default    => 'INFO',
        };

        $waktu    = now()->setTimezone('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB';
        $diffScore = $alertData['diff_score'] ?? 0;
        $name      = $alertData['monitor_name'] ?? $alertData['target_url'] ?? '-';
        $alertType = $alertData['alert_type'] ?? 'Perubahan Terdeteksi';

        $message = "{$icon} *ALERT INTEGRITAS WEBSITE — RedSim*\n\n"
            . "Monitor : *{$name}*\n"
            . "Target  : {$alertData['target_url']}\n"
            . "Waktu   : {$waktu}\n"
            . "Tipe    : {$alertType}\n"
            . "Risiko  : *{$severityLabel}* (Skor Perubahan: {$diffScore}/100)\n";

        if (!empty($alertData['ai_summary'])) {
            $message .= "\n📋 *Ringkasan AI:*\n{$alertData['ai_summary']}\n";
        }

        $message .= "\n🔗 Lihat detail di dashboard RedSim untuk mengambil tindakan.\n\n— Tim RedSim";

        return $this->sendMessage($phone, $message);
    }
}

