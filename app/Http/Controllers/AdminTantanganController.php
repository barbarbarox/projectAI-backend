<?php

namespace App\Http\Controllers;

use App\Models\Tantangan;
use App\Services\GeminiService;
use App\Services\RAGService;
use Illuminate\Http\Request;

class AdminTantanganController extends Controller
{
    public function index()
    {
        $tantangan = Tantangan::orderBy('created_at', 'desc')->get();
        return view('admin.tantangan.index', compact('tantangan'));
    }

    public function create()
    {
        return view('admin.tantangan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'poin' => 'required|integer|min:1',
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit',
            'pilihan_a' => 'required|string',
            'pilihan_b' => 'required|string',
            'pilihan_c' => 'required|string',
            'pilihan_d' => 'required|string',
            'jawaban_benar' => 'required|in:A,B,C,D',
            'penjelasan' => 'nullable|string',
        ]);

        $pilihan = [
            'A' => $request->pilihan_a,
            'B' => $request->pilihan_b,
            'C' => $request->pilihan_c,
            'D' => $request->pilihan_d,
        ];

        Tantangan::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tipe' => 'pilihan_ganda', // DIWAJIBKAN PILIHAN GANDA
            'kategori' => 'Keamanan Siber',
            'pilihan_jawaban' => $pilihan,
            'jawaban_benar' => $request->jawaban_benar,
            'penjelasan' => $request->penjelasan,
            'poin' => $request->poin,
            'tingkat_kesulitan' => $request->tingkat_kesulitan,
            'is_aktif' => true,
        ]);

        return redirect()->route('admin.tantangan.index')->with('success', 'Soal berhasil ditambahkan.');
    }

    public function edit(Tantangan $tantangan)
    {
        return view('admin.tantangan.edit', compact('tantangan'));
    }

    public function update(Request $request, Tantangan $tantangan)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'poin' => 'required|integer|min:1',
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit',
            'pilihan_a' => 'required|string',
            'pilihan_b' => 'required|string',
            'pilihan_c' => 'required|string',
            'pilihan_d' => 'required|string',
            'jawaban_benar' => 'required|in:A,B,C,D',
            'penjelasan' => 'nullable|string',
        ]);

        $pilihan = [
            'A' => $request->pilihan_a,
            'B' => $request->pilihan_b,
            'C' => $request->pilihan_c,
            'D' => $request->pilihan_d,
        ];

        $tantangan->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tipe' => 'pilihan_ganda',
            'pilihan_jawaban' => $pilihan,
            'jawaban_benar' => $request->jawaban_benar,
            'penjelasan' => $request->penjelasan,
            'poin' => $request->poin,
            'tingkat_kesulitan' => $request->tingkat_kesulitan,
            'is_aktif' => $request->boolean('is_aktif', true),
        ]);

        return redirect()->route('admin.tantangan.index')->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Tantangan $tantangan)
    {
        $tantangan->delete();
        return redirect()->route('admin.tantangan.index')->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Generate soal edukasi menggunakan AI (dengan atau tanpa RAG).
     */
    public function generateAi(Request $request)
    {
        $request->validate([
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit',
            'topik' => 'nullable|string|max:255',
            'jumlah_soal' => 'nullable|integer|min:1|max:5',
            'use_rag' => 'required|in:ya,tidak',
        ]);

        $tingkat = $request->tingkat_kesulitan;
        $topik = $request->input('topik', 'keamanan siber umum');
        $jumlah = $request->input('jumlah_soal', 1);
        $useRag = $request->use_rag === 'ya';

        $konteks = '';
        $ragSources = [];

        if ($useRag) {
            try {
                $ragService = app(RAGService::class);
                $ragData = $ragService->getKonteks("cybersecurity education quiz {$topik}", 5);
                $konteks = $ragData['konteks'] ?? '';
                $ragSources = $ragData['chunks'] ?? [];
            } catch (\Exception $e) {
                \Log::warning('RAG untuk generate soal gagal: ' . $e->getMessage());
                $konteks = '';
            }
        }

        $prompt = $this->buildQuizPrompt($tingkat, $topik, $jumlah, $konteks, $useRag);

        try {
            $geminiService = app(GeminiService::class);
            $responseRaw = $geminiService->generate($prompt);
            $soalList = json_decode($responseRaw, true);

            if (!is_array($soalList) || empty($soalList)) {
                // Mungkin wrapper: { "soal": [...] }
                if (is_array($soalList) && isset($soalList['soal'])) {
                    $soalList = $soalList['soal'];
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'AI tidak menghasilkan soal dalam format yang valid.',
                    ], 422);
                }
            }

            // Pastikan format benar
            $formattedSoal = [];
            foreach ($soalList as $s) {
                $formattedSoal[] = [
                    'judul' => $s['judul'] ?? 'Soal Keamanan Siber',
                    'deskripsi' => $s['deskripsi'] ?? '',
                    'pilihan_a' => $s['pilihan_a'] ?? $s['pilihan']['A'] ?? '',
                    'pilihan_b' => $s['pilihan_b'] ?? $s['pilihan']['B'] ?? '',
                    'pilihan_c' => $s['pilihan_c'] ?? $s['pilihan']['C'] ?? '',
                    'pilihan_d' => $s['pilihan_d'] ?? $s['pilihan']['D'] ?? '',
                    'jawaban_benar' => strtoupper($s['jawaban_benar'] ?? 'A'),
                    'penjelasan' => $s['penjelasan'] ?? '',
                    'poin' => $s['poin'] ?? ($tingkat === 'sulit' ? 20 : ($tingkat === 'sedang' ? 15 : 10)),
                    'tingkat_kesulitan' => $tingkat,
                ];
            }

            return response()->json([
                'success' => true,
                'soal' => $formattedSoal,
                'rag_used' => $useRag,
                'rag_sources' => array_map(fn($r) => [
                    'source' => $r['source'] ?? 'General',
                    'title' => $r['title'] ?? null,
                    'score' => round(($r['sem_score'] ?? 0) * 100),
                ], $ragSources),
            ]);
        } catch (\Exception $e) {
            \Log::error('Generate soal AI gagal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi AI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build prompt untuk generate soal.
     */
    protected function buildQuizPrompt(string $tingkat, string $topik, int $jumlah, string $konteks, bool $useRag): string
    {
        $ragSection = '';
        if ($useRag && !empty($konteks)) {
            $ragSection = <<<RAG

KNOWLEDGE BASE (gunakan informasi ini sebagai referensi utama untuk membuat soal):
{$konteks}

INSTRUKSI RAG:
- Buat soal yang mengacu pada informasi dari knowledge base di atas
- Pastikan jawaban benar dapat diverifikasi dari knowledge base
- Sertakan referensi teknis yang akurat
RAG;
        }

        $poinDefault = $tingkat === 'sulit' ? 20 : ($tingkat === 'sedang' ? 15 : 10);

        return <<<PROMPT
SYSTEM:
Anda adalah pembuat soal edukasi keamanan siber profesional untuk platform RedSim.
Semua soal dan jawaban WAJIB ditulis dalam Bahasa Indonesia yang baku.
{$ragSection}

TUGAS:
Buat {$jumlah} soal pilihan ganda tentang topik: {$topik}
Tingkat kesulitan: {$tingkat}

ATURAN:
1. Soal harus relevan dengan keamanan siber / cybersecurity
2. Setiap soal HARUS memiliki 4 pilihan (A, B, C, D)
3. Hanya ada SATU jawaban benar
4. Berikan penjelasan mengapa jawaban tersebut benar
5. Tingkat kesulitan "{$tingkat}":
   - mudah: pengetahuan dasar keamanan siber, terminologi, konsep umum
   - sedang: penerapan teknis, analisis kasus, pemahaman mendalam
   - sulit: skenario kompleks, exploit teknis, analisis forensik mendalam
6. Judul soal harus ringkas dan deskriptif (maksimal 100 karakter)

OUTPUT dalam JSON array valid (tanpa markdown wrapper):
[
  {
    "judul": "judul singkat soal",
    "deskripsi": "pertanyaan lengkap / kasus soal",
    "pilihan_a": "isi pilihan A",
    "pilihan_b": "isi pilihan B",
    "pilihan_c": "isi pilihan C",
    "pilihan_d": "isi pilihan D",
    "jawaban_benar": "A|B|C|D",
    "penjelasan": "penjelasan mengapa jawaban tersebut benar",
    "poin": {$poinDefault}
  }
]
PROMPT;
    }
}

