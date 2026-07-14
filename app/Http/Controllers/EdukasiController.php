<?php

namespace App\Http\Controllers;

use App\Models\PoinUser;
use App\Models\Tantangan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EdukasiController extends Controller
{
    public function index()
    {
        return view('edukasi.index');
    }

    public function ensiklopedia()
    {
        return view('edukasi.ensiklopedia');
    }

    public function tantangan()
    {
        $tantanganList = Tantangan::where('is_aktif', true)->get();
        $sudahDijawab = PoinUser::where('user_id', Auth::id())->pluck('tantangan_id')->toArray();

        return view('edukasi.tantangan', compact('tantanganList', 'sudahDijawab'));
    }

    public function jawab(Request $request, Tantangan $tantangan)
    {
        $request->validate(['jawaban' => 'required|string|in:A,B,C,D']);

        $sudahDijawab = PoinUser::where('user_id', Auth::id())
            ->where('tantangan_id', $tantangan->id)->exists();

        if ($sudahDijawab) {
            return back()->with('error', 'Anda sudah menjawab tantangan ini.');
        }

        $jawabanUser = trim($request->jawaban);
        $jawabanBenar = trim($tantangan->jawaban_benar);
        $isBenar = (strtoupper($jawabanUser) === strtoupper($jawabanBenar));

        PoinUser::create([
            'user_id' => Auth::id(),
            'tantangan_id' => $tantangan->id,
            'poin_diperoleh' => $isBenar ? $tantangan->poin : 0,
            'jawaban_user' => $jawabanUser,
            'is_benar' => $isBenar,
            'selesai_at' => now(),
        ]);

        $pesan = $isBenar
            ? "Benar! Anda mendapat {$tantangan->poin} poin."
            : "Salah. Jawaban yang benar adalah {$jawabanBenar}: {$tantangan->penjelasan}";

        return back()->with($isBenar ? 'success' : 'error', $pesan);
    }

    public function leaderboard()
    {
        $leaders = User::select('users.id', 'users.name', 'users.nama_lengkap')
            ->selectRaw('COALESCE(SUM(poin_user.poin_diperoleh), 0) as total_poin')
            ->leftJoin('poin_user', function ($join) {
                $join->on('users.id', '=', 'poin_user.user_id')
                    ->where('poin_user.is_benar', true);
            })
            ->groupBy('users.id', 'users.name', 'users.nama_lengkap')
            ->orderByDesc('total_poin')
            ->limit(20)
            ->get();

        return view('edukasi.leaderboard', compact('leaders'));
    }
}
