<?php

namespace App\Services;

use App\Models\DomainVerification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DomainVerificationService
{
    /**
     * Generate verifikasi baru untuk domain.
     */
    public function generateVerification(User $user, string $domain): DomainVerification
    {
        // Hapus verifikasi pending yang sudah expired untuk domain & user ini
        DomainVerification::where('user_id', $user->id)
            ->where('domain', $domain)
            ->where('status', 'pending')
            ->where('expires_at', '<', now())
            ->delete();

        $token = 'redsim-verify-' . Str::uuid() . '-' . time();
        $namaFile = 'redsim-' . substr(md5($token), 0, 12) . '.html';

        return DomainVerification::create([
            'user_id' => $user->id,
            'domain' => $domain,
            'token' => $token,
            'nama_file' => $namaFile,
            'status' => 'pending',
            'expires_at' => now()->addHours(24),
        ]);
    }

    /**
     * Verifikasi kepemilikan domain.
     */
    public function verify(DomainVerification $dv): array
    {
        if ($dv->isExpired()) {
            $dv->update(['status' => 'expired']);
            return ['berhasil' => false, 'pesan' => 'Token verifikasi sudah expired. Silakan generate ulang.'];
        }

        if ($dv->status === 'verified') {
            return ['berhasil' => true, 'pesan' => 'Domain sudah terverifikasi sebelumnya.'];
        }

        // Coba HTTPS dulu, fallback ke HTTP
        $urls = [
            "https://{$dv->domain}/{$dv->nama_file}",
            "http://{$dv->domain}/{$dv->nama_file}",
        ];

        foreach ($urls as $url) {
            try {
                $response = Http::timeout(10)->withOptions([
                    'verify' => false,
                ])->get($url);

                if ($response->successful() && str_contains($response->body(), $dv->token)) {
                    $dv->update([
                        'status' => 'verified',
                        'verified_at' => now(),
                    ]);
                    return ['berhasil' => true, 'pesan' => 'Domain berhasil diverifikasi!'];
                }
            } catch (\Exception $e) {
                Log::debug("Verifikasi gagal untuk {$url}: " . $e->getMessage());
                continue;
            }
        }

        return [
            'berhasil' => false,
            'pesan' => 'File verifikasi tidak ditemukan atau token tidak cocok. Pastikan file sudah diupload ke root directory website.',
        ];
    }

    /**
     * Cek apakah domain sudah terverifikasi untuk user.
     */
    public function isVerified(string $domain, int $userId): bool
    {
        return DomainVerification::verifiedForUser($domain, $userId)->exists();
    }

    /**
     * Generate isi file HTML verifikasi.
     */
    public function generateHtmlContent(string $token): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head><title>RedSim Verification</title></head>
<body>
<!-- RedSim Ownership Verification -->
<meta name="redsim-token" content="{$token}">
<p>File verifikasi RedSim. Jangan hapus file ini.</p>
</body>
</html>
HTML;
    }

    /**
     * Ekstrak domain dari URL.
     */
    public static function extractDomain(string $url): string
    {
        $parsed = parse_url($url);
        return $parsed['host'] ?? $url;
    }
}
