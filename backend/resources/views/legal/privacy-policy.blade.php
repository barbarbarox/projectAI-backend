<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi — RedSim</title>
    <meta name="description" content="Kebijakan Privasi RedSim — Platform Penilaian Keamanan Sistem Berbasis AI">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        * { box-sizing: border-box; }
        body { background-color: #020609; color: #f1f5f9; font-family: 'Inter', sans-serif; margin: 0; }
        .prose h2 { color: #ef4444; font-size: 1.05rem; font-weight: 700; margin: 2rem 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #1e293b; }
        .prose h3 { color: #22d3ee; font-size: 0.9rem; font-weight: 600; margin: 1.25rem 0 0.5rem; }
        .prose p { color: #94a3b8; line-height: 1.8; margin-bottom: 0.75rem; font-size: 0.88rem; }
        .prose ul { color: #94a3b8; padding-left: 1.5rem; margin-bottom: 0.75rem; }
        .prose ul li { margin-bottom: 0.4rem; font-size: 0.88rem; line-height: 1.7; list-style: disc; }
        .prose a { color: #22d3ee; text-decoration: underline; }
        .prose strong { color: #e2e8f0; }
        .prose code { background: #0d1117; padding: 0.1em 0.4em; border-radius: 4px; font-family: 'JetBrains Mono', monospace; font-size: 0.8em; color: #4ade80; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen">

    {{-- Navbar --}}
    <nav class="sticky top-0 z-50 px-6 py-4 flex items-center justify-between" style="background:rgba(2,6,9,0.92);backdrop-filter:blur(20px);border-bottom:1px solid #1e293b;">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <img src="{{ asset('logo-redsim1:1.svg') }}" alt="RedSim" class="w-9 h-9 rounded-xl object-contain">
            <span class="text-base font-bold" style="background:linear-gradient(135deg,#ef4444,#06b6d4);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;">RedSim</span>
        </a>
        <div class="flex items-center gap-4">
            <a href="{{ url('/terms') }}" class="text-xs text-slate-400 hover:text-white transition-colors">Syarat Layanan</a>
            @auth
            <a href="{{ route('dashboard') }}" class="text-xs px-3 py-1.5 rounded-lg font-medium text-white" style="background:linear-gradient(135deg,#dc2626,#991b1b);">Dashboard</a>
            @else
            <a href="{{ route('masuk') }}" class="text-xs px-3 py-1.5 rounded-lg font-medium text-cyan-400" style="border:1px solid rgba(6,182,212,0.25);">Masuk</a>
            @endauth
        </div>
    </nav>

    {{-- Hero --}}
    <div class="max-w-4xl mx-auto px-6 pt-12 pb-4">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(220,38,38,0.12);">
                <svg class="w-5 h-5" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Kebijakan Privasi</h1>
                <p class="text-xs" style="color:#64748b;">Terakhir diperbarui: {{ date('d F Y') }}</p>
            </div>
        </div>
        <div class="p-3 rounded-lg text-xs" style="background:rgba(6,182,212,0.05);border:1px solid rgba(6,182,212,0.15);color:#64748b;">
            Kebijakan ini menjelaskan bagaimana <strong class="text-white">RedSim</strong> mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda saat menggunakan layanan kami di <a href="https://redsim.my.id" style="color:#22d3ee;">redsim.my.id</a>.
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-4xl mx-auto px-6 pb-16">
        <div class="prose">

            <h2>1. Informasi yang Kami Kumpulkan</h2>
            <h3>1.1 Informasi Akun</h3>
            <p>Saat mendaftar ke RedSim, kami mengumpulkan:</p>
            <ul>
                <li>Nama lengkap dan alamat email</li>
                <li>Kata sandi (disimpan dalam bentuk terenkripsi)</li>
                <li>Foto profil (jika menggunakan Google OAuth)</li>
                <li>Nomor WhatsApp (opsional, untuk notifikasi)</li>
            </ul>

            <h3>1.2 Informasi Penggunaan Layanan</h3>
            <p>Kami secara otomatis mencatat data penggunaan, termasuk:</p>
            <ul>
                <li>URL dan log sistem yang Anda kirimkan untuk dianalisis</li>
                <li>Hasil analisis keamanan yang dihasilkan oleh AI</li>
                <li>Riwayat interaksi dengan fitur AI dan chatbot</li>
                <li>Data kemajuan belajar pada fitur edukasi dan tantangan</li>
            </ul>

            <h3>1.3 Data OAuth Google (Fitur AI — Opsional)</h3>
            <p>Jika Anda memilih menghubungkan akun Google untuk mengakses model AI Gemini, kami mengumpulkan:</p>
            <ul>
                <li><strong>Nama dan email Google</strong> — untuk identifikasi akun</li>
                <li><strong>OAuth Access Token</strong> — untuk memanggil Google Gemini API atas nama Anda</li>
                <li><strong>OAuth Refresh Token</strong> — untuk memperbarui akses tanpa login ulang</li>
            </ul>
            <p>Token OAuth <strong>hanya digunakan</strong> untuk memanggil Gemini API sesuai permintaan Anda dan <strong>tidak pernah dibagikan</strong> kepada pihak ketiga. Anda dapat mencabut akses kapan saja melalui <a href="https://myaccount.google.com/permissions" target="_blank">Pengaturan Akun Google</a>.</p>

            <h2>2. Cara Kami Menggunakan Informasi</h2>
            <p>Informasi yang kami kumpulkan digunakan untuk:</p>
            <ul>
                <li>Menyediakan dan meningkatkan layanan analisis keamanan berbasis AI</li>
                <li>Menghasilkan laporan dan rekomendasi keamanan yang dipersonalisasi</li>
                <li>Mengirim notifikasi penting terkait akun dan keamanan</li>
                <li>Melacak kemajuan belajar Anda di platform edukasi</li>
                <li>Mencegah penyalahgunaan dan aktivitas penipuan</li>
                <li>Mematuhi kewajiban hukum yang berlaku di Indonesia</li>
            </ul>

            <h2>3. Penyimpanan dan Keamanan Data</h2>
            <p>Semua data disimpan dengan perlindungan berlapis:</p>
            <ul>
                <li>Enkripsi data saat transit menggunakan <code>HTTPS/TLS</code></li>
                <li>Enkripsi kata sandi menggunakan algoritma <code>bcrypt</code></li>
                <li>Token OAuth dienkripsi sebelum disimpan di database</li>
                <li>Akses database dibatasi hanya untuk sistem internal</li>
                <li>Audit log untuk seluruh aktivitas sensitif</li>
            </ul>

            <h2>4. Berbagi Informasi dengan Pihak Ketiga</h2>
            <p>Kami <strong>tidak menjual atau memperdagangkan</strong> data pribadi Anda. Data hanya dibagikan dengan:</p>
            <ul>
                <li><strong>Google LLC (Gemini API)</strong> — jika Anda mengaktifkan fitur AI OAuth Google</li>
                <li><strong>Penyedia infrastruktur hosting</strong> — untuk operasional server</li>
                <li><strong>Otoritas hukum Indonesia</strong> — jika diwajibkan oleh peraturan perundang-undangan</li>
            </ul>

            <h2>5. Hak-Hak Anda</h2>
            <p>Sesuai prinsip perlindungan data, Anda memiliki hak untuk:</p>
            <ul>
                <li><strong>Akses</strong> — Meminta salinan data pribadi yang kami simpan</li>
                <li><strong>Koreksi</strong> — Memperbarui data tidak akurat melalui halaman profil</li>
                <li><strong>Penghapusan</strong> — Meminta penghapusan akun dan seluruh data Anda</li>
                <li><strong>Pencabutan OAuth</strong> — Mencabut akses token Google kapan saja dari pengaturan profil atau dari Google Account</li>
            </ul>

            <h2>6. Cookie</h2>
            <p>RedSim menggunakan cookie yang diperlukan untuk:</p>
            <ul>
                <li>Menjaga status login (session cookie)</li>
                <li>Perlindungan CSRF (Cross-Site Request Forgery)</li>
                <li>Menyimpan preferensi tampilan pengguna</li>
            </ul>
            <p>Kami tidak menggunakan cookie pelacakan iklan.</p>

            <h2>7. Retensi Data</h2>
            <p>Data Anda disimpan selama akun aktif. Jika akun dihapus:</p>
            <ul>
                <li>Data profil dihapus dalam <strong>30 hari</strong></li>
                <li>Audit log keamanan disimpan hingga <strong>2 tahun</strong></li>
            </ul>

            <h2>8. Perubahan Kebijakan</h2>
            <p>Kami dapat memperbarui kebijakan ini sewaktu-waktu. Perubahan signifikan akan dinotifikasikan melalui email atau notifikasi dalam aplikasi.</p>

            <h2>9. Hubungi Kami</h2>
            <p>Untuk pertanyaan terkait privasi:</p>
            <ul>
                <li><strong>Email:</strong> <a href="mailto:bks.akbarmaulana@gmail.com">bks.akbarmaulana@gmail.com</a></li>
                <li><strong>Website:</strong> <a href="https://redsim.my.id">https://redsim.my.id</a></li>
            </ul>
        </div>
    </div>

    {{-- Footer --}}
    <footer style="border-top:1px solid #1e293b;" class="py-6 px-6 text-center">
        <div class="flex flex-wrap items-center justify-center gap-4 text-xs" style="color:#475569;">
            <a href="{{ url('/') }}" class="hover:text-white transition-colors">Beranda</a>
            <span>·</span>
            <span style="color:#22d3ee;">Kebijakan Privasi</span>
            <span>·</span>
            <a href="{{ url('/terms') }}" class="hover:text-white transition-colors">Syarat Layanan</a>
        </div>
        <p class="mt-2 text-xs" style="color:#334155;">© {{ date('Y') }} RedSim. Hak cipta dilindungi.</p>
    </footer>
</body>
</html>
