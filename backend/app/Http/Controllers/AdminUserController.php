<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('status')) {
            if ($request->status === 'pending') {
                $query->where('is_verified', false);
            } elseif ($request->status === 'verified') {
                $query->where('is_verified', true);
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $pendingCount = User::where('is_verified', false)->count();

        return view('admin.users.index', compact('users', 'pendingCount'));
    }

    public function verify(User $user, WhatsappService $wa)
    {
        $user->update(['is_verified' => true]);
        
        if ($user->phone) {
            $message = "✅ *Akun Anda Telah Disetujui!*\n\n"
                     . "Halo *{$user->name}*,\n"
                     . "Pendaftaran Anda di platform RedSim telah disetujui oleh Administrator.\n\n"
                     . "Silakan login menggunakan akun Anda.\n"
                     . "Terima kasih!\n\n— Tim RedSim";
            $wa->sendMessage($user->phone, $message);
        }
        
        return back()->with('success', "Akun pengguna {$user->name} berhasil disetujui.");
    }

    public function resendNotification(User $user, WhatsappService $wa)
    {
        if (!$user->phone) {
            return back()->with('error', "Pengguna ini belum mendaftarkan nomor WhatsApp.");
        }

        if ($user->is_verified) {
            return back()->with('info', "Pengguna ini sudah terverifikasi.");
        }

        // Generate verification token baru dan kirim link via WA
        $verificationToken = Str::random(64);
        $user->update([
            'verification_token' => $verificationToken,
            'verification_expires_at' => now()->addMinutes(30),
        ]);

        $verificationUrl = route('verifikasi-akun', ['token' => $verificationToken]);
        $wa->sendVerificationLink($user->phone, $user->name, $verificationUrl);

        return back()->with('success', "Link verifikasi berhasil dikirim ulang ke WhatsApp {$user->name}.");
    }

    public function unverify(User $user)
    {
        $user->update(['is_verified' => false]);
        return back()->with('success', "Akun pengguna {$user->name} telah dibatalkan persetujuannya.");
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Pengguna berhasil dihapus.');
    }

    // ========== WHATSAPP SETTINGS ==========

    public function waSettings()
    {
        $settings = AppSetting::whereIn('key', [
            'wa_sender_number',
            'openwa_base_url',
            'openwa_api_key',
            'openwa_session_id',
        ])->pluck('value', 'key');

        return view('admin.wa-settings', compact('settings'));
    }

    public function updateWaSettings(Request $request)
    {
        $request->validate([
            'wa_sender_number' => 'nullable|string|max:20',
            'openwa_base_url' => 'required|string|max:255',
            'openwa_api_key' => 'nullable|string|max:255',
            'openwa_session_id' => 'required|string|max:100',
        ]);

        AppSetting::setValue('wa_sender_number', $request->input('wa_sender_number') ?? '');
        AppSetting::setValue('openwa_base_url', $request->input('openwa_base_url') ?? 'http://localhost:3000');
        AppSetting::setValue('openwa_api_key', $request->input('openwa_api_key') ?? '');
        AppSetting::setValue('openwa_session_id', $request->input('openwa_session_id') ?? 'default');

        return back()->with('success', 'Konfigurasi WhatsApp berhasil disimpan.');
    }

    public function testWaMessage(Request $request, WhatsappService $wa)
    {
        $request->validate([
            'test_phone' => 'required|string|max:20',
        ]);

        $sent = $wa->sendMessage(
            $request->test_phone,
            "🧪 *Tes Pesan RedSim*\n\nIni adalah pesan percobaan dari sistem RedSim.\nKoneksi WhatsApp berhasil! ✅\n\n— Tim RedSim"
        );

        if ($sent) {
            return back()->with('success', 'Pesan tes berhasil dikirim!');
        }

        return back()->with('error', 'Gagal mengirim pesan. Periksa konfigurasi OpenWA.');
    }

    public function checkWaStatus(WhatsappService $wa)
    {
        $status = $wa->checkSessionStatus();
        return response()->json($status);
    }
}
