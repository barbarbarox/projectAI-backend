<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected string $token;

    public function __construct()
    {
        $this->token = env('FONNTE_TOKEN', '');
    }

    /**
     * Kirim pesan WhatsApp via Fonnte API.
     *
     * @param string $phone Nomor telepon tujuan (format: 628xxxxxxxxx)
     * @param string $message Isi pesan
     * @return bool
     */
    public function sendMessage(string $phone, string $message): bool
    {
        if (empty($this->token)) {
            Log::error('Fonnte token belum dikonfigurasi.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? false) === true) {
                    Log::info("Fonnte OTP terkirim ke: {$phone}");
                    return true;
                }
                Log::warning("Fonnte response error: " . json_encode($data));
                return false;
            }

            Log::error("Fonnte HTTP error: {$response->status()} - {$response->body()}");
            return false;
        } catch (\Exception $e) {
            Log::error("Fonnte send failed: " . $e->getMessage());
            return false;
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
}

