<?php

namespace App\Services;

use App\Mail\DomainVerificationMail;
use App\Models\DomainVerification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DomainVerificationService
{
    /**
     * Generate verifikasi baru untuk domain dengan metode tertentu.
     */
    public function generateVerification(User $user, string $domain, string $metode = 'file_upload'): DomainVerification
    {
        // Hapus verifikasi pending yang sudah expired untuk domain & user ini
        DomainVerification::where('user_id', $user->id)
            ->where('domain', $domain)
            ->where('status', 'pending')
            ->where('expires_at', '<', now())
            ->delete();

        $token     = 'redsim-verify-' . Str::uuid() . '-' . time();
        $namaFile  = 'redsim-' . substr(md5($token), 0, 12) . '.html';
        $kodeEmail = null;

        // Jika metode email, buat kode 6 digit yang mudah dibaca
        if ($metode === 'email_domain') {
            $kodeEmail = strtoupper(Str::random(3)) . '-' . rand(100, 999);
        }

        return DomainVerification::create([
            'user_id'            => $user->id,
            'domain'             => $domain,
            'token'              => $token,
            'nama_file'          => $namaFile,
            'metode_verifikasi'  => $metode,
            'kode_email'         => $kodeEmail,
            'status'             => 'pending',
            'expires_at'         => now()->addHours(24),
        ]);
    }

    /**
     * Kirim email verifikasi ke alamat email domain.
     * Mengembalikan array ['berhasil', 'pesan', 'email_tujuan'].
     */
    public function kirimEmailVerifikasi(DomainVerification $dv, string $emailTujuan): array
    {
        // Pastikan email menggunakan domain yang bersangkutan
        $emailDomain = substr(strrchr($emailTujuan, '@'), 1);
        if ($emailDomain !== $dv->domain) {
            return [
                'berhasil'     => false,
                'pesan'        => "Email harus menggunakan domain {$dv->domain} (contoh: admin@{$dv->domain}).",
                'email_tujuan' => null,
            ];
        }

        // Simpan email tujuan ke record
        $dv->update(['email_verifikasi' => $emailTujuan]);

        try {
            Mail::to($emailTujuan)->send(new DomainVerificationMail($dv));

            return [
                'berhasil'     => true,
                'pesan'        => "Kode verifikasi berhasil dikirim ke {$emailTujuan}.",
                'email_tujuan' => $emailTujuan,
            ];
        } catch (\Exception $e) {
            Log::error("Gagal mengirim email verifikasi ke {$emailTujuan}: " . $e->getMessage());
            return [
                'berhasil'     => false,
                'pesan'        => 'Gagal mengirim email. Pastikan server email sudah dikonfigurasi.',
                'email_tujuan' => null,
            ];
        }
    }

    /**
     * Verifikasi kepemilikan domain sesuai metode yang dipilih.
     */
    public function verify(DomainVerification $dv, ?string $inputKode = null): array
    {
        if ($dv->isExpired()) {
            $dv->update(['status' => 'expired']);
            return ['berhasil' => false, 'pesan' => 'Token verifikasi sudah expired. Silakan generate ulang.'];
        }

        if ($dv->status === 'verified') {
            return ['berhasil' => true, 'pesan' => 'Domain sudah terverifikasi sebelumnya.'];
        }

        return match ($dv->metode_verifikasi) {
            'dns_txt'      => $this->verifyDnsTxt($dv),
            'meta_tag'     => $this->verifyMetaTag($dv),
            'email_domain' => $this->verifyEmailKode($dv, $inputKode),
            default        => $this->verifyFileUpload($dv),  // file_upload
        };
    }

    // ------------------------------------------------------------------
    // Metode: File Upload
    // ------------------------------------------------------------------

    protected function verifyFileUpload(DomainVerification $dv): array
    {
        $urls = [
            "https://{$dv->domain}/{$dv->nama_file}",
            "http://{$dv->domain}/{$dv->nama_file}",
        ];

        foreach ($urls as $url) {
            try {
                $response = Http::timeout(10)->withOptions(['verify' => false])->get($url);

                if ($response->successful() && str_contains($response->body(), $dv->token)) {
                    return $this->tandaiVerified($dv);
                }
            } catch (\Exception $e) {
                Log::debug("Verifikasi file_upload gagal untuk {$url}: " . $e->getMessage());
                continue;
            }
        }

        return [
            'berhasil' => false,
            'pesan'    => 'File verifikasi tidak ditemukan atau token tidak cocok. Pastikan file sudah diupload ke root directory website.',
        ];
    }

    // ------------------------------------------------------------------
    // Metode: DNS TXT Record
    // ------------------------------------------------------------------

    protected function verifyDnsTxt(DomainVerification $dv): array
    {
        try {
            $records = dns_get_record($dv->domain, DNS_TXT);

            if ($records === false) {
                return ['berhasil' => false, 'pesan' => 'Tidak dapat membaca DNS records untuk domain ini.'];
            }

            foreach ($records as $record) {
                $txtValue = $record['txt'] ?? $record['entries'][0] ?? '';
                if (str_contains($txtValue, $dv->token)) {
                    return $this->tandaiVerified($dv);
                }
            }
        } catch (\Exception $e) {
            Log::error("DNS lookup gagal untuk {$dv->domain}: " . $e->getMessage());
            return ['berhasil' => false, 'pesan' => 'Terjadi kesalahan saat memeriksa DNS. Coba lagi beberapa saat.'];
        }

        return [
            'berhasil' => false,
            'pesan'    => 'TXT record verifikasi tidak ditemukan. Pastikan DNS sudah dipropagasi (bisa memakan waktu hingga 48 jam).',
        ];
    }

    // ------------------------------------------------------------------
    // Metode: Meta Tag HTML
    // ------------------------------------------------------------------

    protected function verifyMetaTag(DomainVerification $dv): array
    {
        $urls = [
            "https://{$dv->domain}",
            "http://{$dv->domain}",
        ];

        foreach ($urls as $url) {
            try {
                $response = Http::timeout(15)->withOptions(['verify' => false])->get($url);

                if ($response->successful()) {
                    $html = $response->body();
                    // Cari <meta name="redsim-verification" content="TOKEN">
                    if (preg_match('/<meta\s[^>]*name=["\']redsim-verification["\']\s[^>]*content=["\']([^"\']+)["\']/i', $html, $matches)
                        || preg_match('/<meta\s[^>]*content=["\']([^"\']+)["\']\s[^>]*name=["\']redsim-verification["\']/i', $html, $matches)
                    ) {
                        if (trim($matches[1]) === $dv->token) {
                            return $this->tandaiVerified($dv);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::debug("Verifikasi meta_tag gagal untuk {$url}: " . $e->getMessage());
                continue;
            }
        }

        return [
            'berhasil' => false,
            'pesan'    => 'Meta tag verifikasi tidak ditemukan di halaman utama website atau nilai token tidak cocok.',
        ];
    }

    // ------------------------------------------------------------------
    // Metode: Email Domain
    // ------------------------------------------------------------------

    protected function verifyEmailKode(DomainVerification $dv, ?string $inputKode): array
    {
        if (empty($dv->email_verifikasi)) {
            return ['berhasil' => false, 'pesan' => 'Email verifikasi belum dikirim. Kirim kode terlebih dahulu.'];
        }

        if (empty($inputKode)) {
            return ['berhasil' => false, 'pesan' => 'Kode verifikasi wajib diisi.'];
        }

        if (strtoupper(trim($inputKode)) !== strtoupper($dv->kode_email)) {
            return ['berhasil' => false, 'pesan' => 'Kode verifikasi tidak cocok. Periksa kembali kode yang Anda terima.'];
        }

        return $this->tandaiVerified($dv);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Tandai domain verification sebagai verified.
     */
    protected function tandaiVerified(DomainVerification $dv): array
    {
        $dv->update([
            'status'      => 'verified',
            'verified_at' => now(),
        ]);
        return ['berhasil' => true, 'pesan' => 'Domain berhasil diverifikasi! Scan intens sedang diproses.'];
    }

    /**
     * Cek apakah domain sudah terverifikasi untuk user.
     */
    public function isVerified(string $domain, int|string $userId): bool
    {
        return DomainVerification::verifiedForUser($domain, $userId)->exists();
    }

    /**
     * Generate isi file HTML verifikasi (untuk metode file_upload).
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
