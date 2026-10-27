<?php

namespace App\Http\Controllers;

use App\Models\Tantangan;
use App\Services\GeminiService;
use App\Services\RAGService;
use Illuminate\Http\Request;

class AdminTantanganController extends Controller
{
    /** Daftar topik keamanan siber untuk random pick */
    protected const TOPIK_LIST = [
        'SQL Injection', 'Cross-Site Scripting (XSS)', 'CSRF Attack',
        'Buffer Overflow', 'Phishing & Social Engineering', 'Man-in-the-Middle Attack',
        'Brute Force Attack', 'Password Cracking', 'Kriptografi Simetris',
        'Kriptografi Asimetris', 'Hash Function & Collision', 'Caesar Cipher',
        'RSA Encryption', 'JWT Security', 'OAuth & OpenID Connect',
        'SSL/TLS Protocol', 'DNS Spoofing', 'ARP Poisoning',
        'Privilege Escalation', 'Remote Code Execution', 'OWASP Top 10',
        'Network Scanning (Nmap)', 'Forensik Digital', 'Steganografi',
        'Reverse Engineering', 'Analisis Malware', 'Ransomware',
        'Docker Security', 'Cloud Security', 'Zero-Day Vulnerability',
    ];

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
        if ($request->has('payload')) {
            $decoded = json_decode(urldecode(base64_decode($request->payload)), true);
            if (is_array($decoded)) {
                $request->merge($decoded);
            }
        }

        $format = $request->input('format_soal', 'quiz');

        $rules = [
            'judul'             => 'required|string|max:255',
            'deskripsi'         => 'required|string',
            'poin'              => 'required|integer|min:1',
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit',
            'format_soal'       => 'required|in:quiz,kode,skenario,ciphertext,file',
            'jawaban_benar'     => 'required|string',
            'penjelasan'        => 'nullable|string',
        ];

        // Pilihan jawaban hanya wajib untuk format quiz
        if ($format === 'quiz') {
            $rules['pilihan_a'] = 'required|string';
            $rules['pilihan_b'] = 'required|string';
            $rules['pilihan_c'] = 'required|string';
            $rules['pilihan_d'] = 'required|string';
        }

        $request->validate($rules);

        $pilihan = null;
        if ($format === 'quiz') {
            $pilihan = [
                'A' => $request->pilihan_a,
                'B' => $request->pilihan_b,
                'C' => $request->pilihan_c,
                'D' => $request->pilihan_d,
            ];
        }

        // Konten tambahan sesuai format
        $kontenTambahan = null;
        switch ($format) {
            case 'kode':
                $kontenTambahan = [
                    'kode'   => $request->input('konten_kode', ''),
                    'bahasa' => $request->input('bahasa_kode', 'php'),
                ];
                break;
            case 'skenario':
                $poin_skenario = $request->input('skenario_poin', []);
                if (is_string($poin_skenario)) {
                    $poin_skenario = array_filter(explode("\n", $poin_skenario));
                }
                $kontenTambahan = ['poin_skenario' => array_values($poin_skenario)];
                break;
            case 'ciphertext':
                $kontenTambahan = [
                    'ciphertext' => $request->input('konten_ciphertext', ''),
                    'hint'       => $request->input('hint_cipher', ''),
                ];
                break;
            case 'file':
                $kontenTambahan = [
                    'petunjuk' => $request->input('petunjuk_file', ''),
                    'hint'     => $request->input('hint_file', ''),
                ];
                break;
        }

        Tantangan::create([
            'judul'             => $request->judul,
            'deskripsi'         => $request->deskripsi,
            'tipe'              => 'pilihan_ganda',
            'format_soal'       => $format,
            'kategori'          => 'Keamanan Siber',
            'pilihan_jawaban'   => $pilihan,
            'konten_tambahan'   => $kontenTambahan,
            'jawaban_benar'     => $request->jawaban_benar,
            'penjelasan'        => $request->penjelasan,
            'poin'              => $request->poin,
            'tingkat_kesulitan' => $request->tingkat_kesulitan,
            'is_aktif'          => true,
        ]);

        return redirect()->route('admin.tantangan.index')->with('success', 'Soal berhasil ditambahkan.');
    }

    public function edit(Tantangan $tantangan)
    {
        return view('admin.tantangan.edit', compact('tantangan'));
    }

    public function update(Request $request, Tantangan $tantangan)
    {
        if ($request->has('payload')) {
            $decoded = json_decode(urldecode(base64_decode($request->payload)), true);
            if (is_array($decoded)) {
                $request->merge($decoded);
            }
        }

        $format = $request->input('format_soal', $tantangan->format_soal ?? 'quiz');

        $rules = [
            'judul'             => 'required|string|max:255',
            'deskripsi'         => 'required|string',
            'poin'              => 'required|integer|min:1',
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit',
            'format_soal'       => 'required|in:quiz,kode,skenario,ciphertext,file',
            'jawaban_benar'     => 'required|string',
            'penjelasan'        => 'nullable|string',
        ];

        if ($format === 'quiz') {
            $rules['pilihan_a'] = 'required|string';
            $rules['pilihan_b'] = 'required|string';
            $rules['pilihan_c'] = 'required|string';
            $rules['pilihan_d'] = 'required|string';
        }

        $request->validate($rules);

        $pilihan = null;
        if ($format === 'quiz') {
            $pilihan = [
                'A' => $request->pilihan_a,
                'B' => $request->pilihan_b,
                'C' => $request->pilihan_c,
                'D' => $request->pilihan_d,
            ];
        }

        $kontenTambahan = null;
        switch ($format) {
            case 'kode':
                $kontenTambahan = [
                    'kode'   => $request->input('konten_kode', ''),
                    'bahasa' => $request->input('bahasa_kode', 'php'),
                ];
                break;
            case 'skenario':
                $poin_skenario = $request->input('skenario_poin', []);
                if (is_string($poin_skenario)) {
                    $poin_skenario = array_filter(explode("\n", $poin_skenario));
                }
                $kontenTambahan = ['poin_skenario' => array_values($poin_skenario)];
                break;
            case 'ciphertext':
                $kontenTambahan = [
                    'ciphertext' => $request->input('konten_ciphertext', ''),
                    'hint'       => $request->input('hint_cipher', ''),
                ];
                break;
            case 'file':
                $kontenTambahan = [
                    'petunjuk' => $request->input('petunjuk_file', ''),
                    'hint'     => $request->input('hint_file', ''),
                ];
                break;
        }

        $tantangan->update([
            'judul'             => $request->judul,
            'deskripsi'         => $request->deskripsi,
            'tipe'              => 'pilihan_ganda',
            'format_soal'       => $format,
            'pilihan_jawaban'   => $pilihan,
            'konten_tambahan'   => $kontenTambahan,
            'jawaban_benar'     => $request->jawaban_benar,
            'penjelasan'        => $request->penjelasan,
            'poin'              => $request->poin,
            'tingkat_kesulitan' => $request->tingkat_kesulitan,
            'is_aktif'          => $request->boolean('is_aktif', true),
        ]);

        return redirect()->route('admin.tantangan.index')->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Tantangan $tantangan)
    {
        $tantangan->delete();
        return redirect()->route('admin.tantangan.index')->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Endpoint: pilih topik secara acak (tanpa AI).
     */
    public function randomTopik()
    {
        $topik = self::TOPIK_LIST[array_rand(self::TOPIK_LIST)];
        return response()->json(['topik' => $topik]);
    }

    /**
     * Generate soal edukasi menggunakan AI (mendukung semua format CTF).
     */
    public function generateAi(Request $request)
    {
        if ($request->has('payload')) {
            $decoded = json_decode(urldecode(base64_decode($request->payload)), true);
            if (is_array($decoded)) {
                $request->merge($decoded);
            }
        }

        $request->validate([
            'tingkat_kesulitan' => 'required|in:mudah,sedang,sulit',
            'topik'             => 'nullable|string|max:255',
            'format_soal'       => 'required|in:quiz,kode,skenario,ciphertext,file',
            'jumlah_soal'       => 'nullable|integer|min:1|max:5',
            'use_rag'           => 'required|in:ya,tidak',
        ]);

        $tingkat    = $request->tingkat_kesulitan;
        $topik      = $request->input('topik') ?: self::TOPIK_LIST[array_rand(self::TOPIK_LIST)];
        $jumlah     = $request->input('jumlah_soal', 1);
        $useRag     = $request->use_rag === 'ya';
        $formatSoal = $request->format_soal;

        $konteks    = '';
        $ragSources = [];

        if ($useRag) {
            try {
                $ragService = app(RAGService::class);
                $ragData    = $ragService->getKonteks("cybersecurity education {$formatSoal} {$topik}", 5);
                $konteks    = $ragData['konteks'] ?? '';
                $ragSources = $ragData['chunks'] ?? [];
            } catch (\Exception $e) {
                \Log::warning('RAG untuk generate soal gagal: ' . $e->getMessage());
            }
        }

        $prompt = $this->buildPromptByFormat($tingkat, $topik, $jumlah, $konteks, $useRag, $formatSoal);

        try {
            $geminiService = app(GeminiService::class);
            $responseRaw   = $geminiService->generate($prompt, null, true, auth()->id());

            // Bersihkan markdown wrapper jika ada
            $responseRaw = preg_replace('/^```json\s*/i', '', trim($responseRaw));
            $responseRaw = preg_replace('/\s*```$/i', '', $responseRaw);

            $soalList = json_decode($responseRaw, true);

            if (!is_array($soalList) || empty($soalList)) {
                if (is_array($soalList) && isset($soalList['soal'])) {
                    $soalList = $soalList['soal'];
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'AI tidak menghasilkan soal dalam format yang valid. Response: ' . substr($responseRaw, 0, 200),
                    ], 422);
                }
            }

            $poinDefault = $tingkat === 'sulit' ? 20 : ($tingkat === 'sedang' ? 15 : 10);
            $formattedSoal = [];

            foreach ($soalList as $s) {
                $entry = [
                    'judul'             => $s['judul'] ?? 'Soal CTF Keamanan Siber',
                    'deskripsi'         => $s['deskripsi'] ?? '',
                    'jawaban_benar'     => $s['jawaban_benar'] ?? '',
                    'penjelasan'        => $s['penjelasan'] ?? '',
                    'poin'              => $s['poin'] ?? $poinDefault,
                    'tingkat_kesulitan' => $tingkat,
                    'format_soal'       => $formatSoal,
                    'topik_digunakan'   => $topik,
                ];

                switch ($formatSoal) {
                    case 'quiz':
                        $entry['pilihan_a'] = $s['pilihan_a'] ?? $s['pilihan']['A'] ?? '';
                        $entry['pilihan_b'] = $s['pilihan_b'] ?? $s['pilihan']['B'] ?? '';
                        $entry['pilihan_c'] = $s['pilihan_c'] ?? $s['pilihan']['C'] ?? '';
                        $entry['pilihan_d'] = $s['pilihan_d'] ?? $s['pilihan']['D'] ?? '';
                        $entry['jawaban_benar'] = strtoupper($s['jawaban_benar'] ?? 'A');
                        break;
                    case 'kode':
                        $entry['konten_kode'] = $s['kode'] ?? $s['konten_kode'] ?? '';
                        $entry['bahasa_kode'] = $s['bahasa'] ?? $s['bahasa_kode'] ?? 'php';
                        break;
                    case 'skenario':
                        $entry['skenario_poin'] = $s['skenario_poin'] ?? $s['poin_skenario'] ?? [];
                        break;
                    case 'ciphertext':
                        $entry['konten_ciphertext'] = $s['ciphertext'] ?? $s['konten_ciphertext'] ?? '';
                        $entry['hint_cipher']       = $s['hint'] ?? $s['hint_cipher'] ?? '';
                        break;
                    case 'file':
                        $entry['petunjuk_file'] = $s['petunjuk'] ?? $s['petunjuk_file'] ?? '';
                        $entry['hint_file']     = $s['hint'] ?? $s['hint_file'] ?? '';
                        break;
                }

                $formattedSoal[] = $entry;
            }

            return response()->json([
                'success'     => true,
                'soal'        => $formattedSoal,
                'rag_used'    => $useRag,
                'topik'       => $topik,
                'rag_sources' => array_map(fn($r) => [
                    'source' => $r['source'] ?? 'General',
                    'title'  => $r['title'] ?? null,
                    'score'  => round(($r['sem_score'] ?? 0) * 100),
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
     * Build prompt berdasarkan format soal yang dipilih.
     */
    protected function buildPromptByFormat(
        string $tingkat,
        string $topik,
        int $jumlah,
        string $konteks,
        bool $useRag,
        string $format
    ): string {
        $ragSection  = '';
        if ($useRag && !empty($konteks)) {
            $ragSection = <<<RAG

KNOWLEDGE BASE (gunakan sebagai referensi utama):
{$konteks}

INSTRUKSI RAG:
- Buat soal yang mengacu pada knowledge base di atas
- Pastikan jawaban benar dapat diverifikasi dari sumber tersebut
RAG;
        }

        $poinDefault = $tingkat === 'sulit' ? 20 : ($tingkat === 'sedang' ? 15 : 10);

        $base = <<<BASE
SYSTEM:
Anda adalah pembuat soal edukasi keamanan siber / CTF profesional untuk platform RedSim.
Semua teks WAJIB ditulis dalam Bahasa Indonesia. Output HARUS berupa JSON array valid TANPA markdown wrapper.
{$ragSection}

TUGAS: Buat {$jumlah} soal bertopik "{$topik}" dengan tingkat kesulitan "{$tingkat}".
Poin default: {$poinDefault}
BASE;

        $formatSpec = match($format) {
            'quiz' => <<<SPEC

FORMAT SOAL: Pilihan Ganda (Quiz)
OUTPUT JSON ARRAY:
[{
  "judul": "judul singkat (<100 karakter)",
  "deskripsi": "pertanyaan/kasus lengkap",
  "pilihan_a": "pilihan A",
  "pilihan_b": "pilihan B",
  "pilihan_c": "pilihan C",
  "pilihan_d": "pilihan D",
  "jawaban_benar": "A|B|C|D",
  "penjelasan": "penjelasan jawaban benar",
  "poin": {$poinDefault}
}]
SPEC,
            'kode' => <<<SPEC

FORMAT SOAL: Analisis Kode (Code Challenge)
- Buat soal yang menyajikan sebuah snippet kode yang mengandung celah keamanan atau bug kritis
- User harus mengidentifikasi kerentanannya atau menjawab pertanyaan tentang kode tersebut
- Sertakan kode yang realistis dan relevan dengan topik
OUTPUT JSON ARRAY:
[{
  "judul": "judul singkat (<100 karakter)",
  "deskripsi": "instruksi/pertanyaan tentang kode di bawah ini",
  "kode": "// kode yang harus dianalisis (bisa multi-baris, gunakan \\n untuk newline)",
  "bahasa": "php|javascript|python|java|bash|sql",
  "jawaban_benar": "jawaban teks bebas / nama kerentanan / flag format CTF{...}",
  "penjelasan": "penjelasan kerentanan dan cara exploitnya",
  "poin": {$poinDefault}
}]
SPEC,
            'skenario' => <<<SPEC

FORMAT SOAL: Skenario Poin-per-Poin (Scenario Challenge)
- Buat soal berbentuk skenario insiden keamanan yang disajikan dalam poin-poin kronologis
- User harus menganalisis skenario dan menjawab pertanyaan di bagian deskripsi
OUTPUT JSON ARRAY:
[{
  "judul": "judul singkat skenario (<100 karakter)",
  "deskripsi": "Pertanyaan utama yang harus dijawab berdasarkan skenario di bawah ini:",
  "skenario_poin": [
    "Langkah 1: ...",
    "Langkah 2: ...",
    "Langkah 3: ...",
    "Langkah 4: ..."
  ],
  "jawaban_benar": "jawaban teks bebas yang menjelaskan analisis skenario",
  "penjelasan": "penjelasan mendalam tentang skenario dan taktik yang digunakan attacker",
  "poin": {$poinDefault}
}]
SPEC,
            'ciphertext' => <<<SPEC

FORMAT SOAL: Dekripsi Ciphertext (Cryptography Challenge)
- Buat soal berupa teks terenkripsi yang harus dipecahkan oleh user
- Gunakan cipher klasik atau modern yang relevan dengan topik (Caesar, Vigenere, Base64, ROT13, XOR, dll)
- Jawaban benar adalah teks asli (plaintext) setelah didekripsi
OUTPUT JSON ARRAY:
[{
  "judul": "judul singkat challenge (<100 karakter)",
  "deskripsi": "instruksi untuk user: apa yang harus dilakukan, metode cipher apa yang digunakan (boleh diberi hint atau menjadi bagian misteri)",
  "ciphertext": "TEKS_TERENKRIPSI_DI_SINI",
  "hint": "hint tambahan jika diperlukan (opsional, boleh kosong)",
  "jawaban_benar": "plaintext hasil dekripsi yang benar",
  "penjelasan": "cara mendekripsi ciphertext tersebut langkah per langkah",
  "poin": {$poinDefault}
}]
SPEC,
            'file' => <<<SPEC

FORMAT SOAL: Analisis File (File Investigation Challenge)
- Buat soal di mana user diberi petunjuk tentang file yang harus dianalisis
- Buat skenario realistis seperti analisis log, analisis memory dump, analisis pcap, dll
- Karena ini platform web, berikan petunjuk detail tentang apa yang ada di dalam file fiktif tersebut
  sehingga user bisa menjawab meski tanpa file asli (simulasikan konten file dalam petunjuk)
OUTPUT JSON ARRAY:
[{
  "judul": "judul singkat challenge (<100 karakter)",
  "deskripsi": "instruksi untuk user: jenis file apa, apa yang harus ditemukan/dianalisis",
  "petunjuk": "simulasi konten/excerpt dari file yang relevan yang bisa dianalisis user (mis: potongan log, hex dump, dll)",
  "hint": "petunjuk/hint tambahan untuk membantu user menemukan flag/jawaban",
  "jawaban_benar": "flag atau jawaban yang harus ditemukan user (format CTF{...} atau teks bebas)",
  "penjelasan": "cara menemukan jawaban dari file tersebut dan teknik forensik yang digunakan",
  "poin": {$poinDefault}
}]
SPEC,
        };

        return $base . $formatSpec;
    }
}
