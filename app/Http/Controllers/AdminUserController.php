<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FonnteService;
use Illuminate\Http\Request;

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

    public function verify(User $user, FonnteService $fonnte)
    {
        $user->update(['is_verified' => true]);
        
        if ($user->phone) {
            $message = "✅ *Akun Anda Telah Disetujui!*\n\n"
                     . "Halo *{$user->name}*,\n"
                     . "Pendaftaran Anda di platform RedSim telah disetujui oleh Administrator.\n\n"
                     . "Silakan login menggunakan akun Anda.\n"
                     . "Terima kasih!\n\n— Tim RedSim";
            $fonnte->sendMessage($user->phone, $message);
        }
        
        return back()->with('success', "Akun pengguna {$user->name} berhasil disetujui.");
    }

    public function resendNotification(User $user, FonnteService $fonnte)
    {
        if (!$user->phone) {
            return back()->with('error', "Pengguna ini belum mendaftarkan nomor WhatsApp.");
        }
        
        $message = "⏳ *Menunggu Persetujuan Admin*\n\n"
                 . "Halo *{$user->name}*,\n"
                 . "Akun Anda saat ini sedang menunggu persetujuan dari Admin. "
                 . "Mohon bersabar, kami akan segera memproses pendaftaran Anda.\n\n"
                 . "— Tim RedSim";
                 
        $fonnte->sendMessage($user->phone, $message);
        return back()->with('success', "Pemberitahuan berhasil dikirim ulang ke WhatsApp {$user->name}.");
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
}
