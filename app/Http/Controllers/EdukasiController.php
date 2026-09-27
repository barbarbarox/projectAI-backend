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

    public function tantangan(Request $request)
    {
        $query = Tantangan::where('is_aktif', true);

        // Filter kategori / format
        if ($request->filled('kategori')) {
            $query->where('format_soal', $request->kategori);
        }

        // Filter tingkat kesulitan
        if ($request->filled('tingkat')) {
            $query->where('tingkat_kesulitan', $request->tingkat);
        }

        // Pencarian judul
        if ($request->filled('q')) {
            $query->where('judul', 'like', '%' . $request->q . '%');
        }

        // Urutan — gunakan CASE WHEN (kompatibel MySQL & PostgreSQL)
        $sort = $request->input('sort', 'terbaru');
        match($sort) {
            'poin_asc'  => $query->orderBy('poin'),
            'poin_desc' => $query->orderByDesc('poin'),
            'mudah'     => $query->orderByRaw("CASE tingkat_kesulitan WHEN 'mudah' THEN 1 WHEN 'sedang' THEN 2 WHEN 'sulit' THEN 3 ELSE 4 END"),
            'sulit'     => $query->orderByRaw("CASE tingkat_kesulitan WHEN 'sulit' THEN 1 WHEN 'sedang' THEN 2 WHEN 'mudah' THEN 3 ELSE 4 END"),
            default     => $query->orderByDesc('created_at'),
        };

        $tantanganList  = $query->get();
        $sudahDijawab   = PoinUser::where('user_id', Auth::id())->pluck('tantangan_id')->toArray();

        // Statistik per format untuk badge filter
        $statsFormat = Tantangan::where('is_aktif', true)
            ->selectRaw('format_soal, COUNT(*) as total')
            ->groupBy('format_soal')
            ->pluck('total', 'format_soal');

        return view('edukasi.tantangan', compact('tantanganList', 'sudahDijawab', 'statsFormat', 'sort'));
    }

    /**
     * Halaman detail satu tantangan (halaman dedicated per soal).
     */
    public function detailTantangan(Tantangan $tantangan)
    {
        if (!$tantangan->is_aktif) {
            abort(404);
        }

        $query = PoinUser::where('user_id', Auth::id())
            ->where('tantangan_id', $tantangan->id);
            
        // Jika format quiz, satu kali coba langsung terkunci (baik benar maupun salah)
        // Jika non-quiz, hanya terkunci jika sudah BENAR
        if (($tantangan->format_soal ?? 'quiz') !== 'quiz') {
            $query->where('is_benar', true);
        }

        $sudahDijawab = $query->first();

        return view('edukasi.tantangan-detail', compact('tantangan', 'sudahDijawab'));
    }

    public function jawab(Request $request, Tantangan $tantangan)
    {
        $format = $tantangan->format_soal ?? 'quiz';

        $request->validate(['jawaban' => 'required|string|max:2000']);

        // Pengecekan apakah sudah terkunci
        $query = PoinUser::where('user_id', Auth::id())
            ->where('tantangan_id', $tantangan->id);
            
        if ($format !== 'quiz') {
            $query->where('is_benar', true);
        }

        if ($query->exists()) {
            return back()->with('info', 'Anda sudah menyelesaikan/mencoba tantangan ini.');
        }

        $jawabanUser  = trim($request->jawaban);
        $jawabanBenar = trim($tantangan->jawaban_benar);

        // Logika perbandingan berdasarkan format
        $isBenar = match($format) {
            'quiz'     => strtoupper($jawabanUser) === strtoupper($jawabanBenar),
            'skenario' => $this->checkJawabanSkenario($jawabanUser, $tantangan, Auth::id()),
            default    => $this->checkJawaban($jawabanUser, $jawabanBenar),
        };

        // Jika quiz: selalu simpan (benar atau salah). Jika non-quiz: hanya simpan jika benar.
        if ($format === 'quiz' || $isBenar) {
            PoinUser::create([
                'user_id'        => Auth::id(),
                'tantangan_id'   => $tantangan->id,
                'poin_diperoleh' => $isBenar ? $tantangan->poin : 0,
                'jawaban_user'   => $jawabanUser,
                'is_benar'       => $isBenar,
                'selesai_at'     => now(),
            ]);

            if ($isBenar) {
                return back()->with('success', "🎉 Benar! Anda mendapat {$tantangan->poin} poin.");
            } else {
                return back()->with('error', "❌ Salah! Kesempatan habis untuk soal kuis ini.");
            }
        }

        // Jawaban salah untuk NON-QUIZ — beri tahu tapi tidak simpan, form tetap aktif
        return back()
            ->with('error', '❌ Jawaban belum tepat. Coba lagi!')
            ->withInput();
    }

    /**
     * Evaluasi jawaban Skenario (essay) menggunakan AI (GeminiService).
     * Menggunakan konfigurasi AI milik user.
     */
    protected function checkJawabanSkenario(string $userJawaban, Tantangan $tantangan, int $userId): bool
    {
        $geminiService = app(\App\Services\GeminiService::class);
        $konten = is_array($tantangan->konten_tambahan) ? json_encode($tantangan->konten_tambahan) : $tantangan->konten_tambahan;
        
        $prompt = "Anda adalah sistem juri otomatis untuk tantangan keamanan siber (CTF).
Judul: {$tantangan->judul}
Deskripsi Skenario: {$tantangan->deskripsi}
Data Tambahan: {$konten}

KUNCI JAWABAN (Ground Truth):
{$tantangan->jawaban_benar}

JAWABAN PESERTA:
\"{$userJawaban}\"

TUGAS ANDA:
Evaluasi apakah JAWABAN PESERTA benar dan substansial menjawab tantangan berdasarkan KUNCI JAWABAN.
Abaikan kesalahan ketik minor (typo), perbedaan huruf besar/kecil, atau perbedaan gaya bahasa/parafrase asalkan inti teknisnya sama dan benar.

Berikan respons dalam format JSON murni TANPA markdown block (```json):
{
    \"is_benar\": true atau false
}";

        try {
            $response = $geminiService->generate($prompt, null, true, $userId);
            // Bersihkan markdown jika terbawa
            $response = preg_replace('/```json|```/', '', $response);
            $result = json_decode(trim($response), true);
            return isset($result['is_benar']) && $result['is_benar'] === true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Evaluation error for skenario: ' . $e->getMessage());
            // Fallback ke pengecekan manual jika AI gagal/API key tidak valid
            return $this->checkJawaban($userJawaban, $tantangan->jawaban_benar);
        }
    }

    /**
     * Perbandingan jawaban fleksibel untuk format non-quiz.
     * Menormalkan whitespace, case, dan strip CTF{} wrapper.
     */
    protected function checkJawaban(string $user, string $benar): bool
    {
        $normalize = fn($s) => strtolower(trim(preg_replace('/\s+/', '', $s)));

        // Strip CTF{...} wrapper jika ada
        $stripCtf = fn($s) => preg_replace('/^ctf\{(.*)\}$/i', '$1', $s);

        return $normalize($user) === $normalize($benar)
            || $normalize($stripCtf($user)) === $normalize($stripCtf($benar));
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
