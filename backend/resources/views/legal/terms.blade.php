<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat & Ketentuan Layanan — RedSim</title>
    <meta name="description" content="Syarat dan Ketentuan Layanan RedSim — Platform Penilaian Keamanan Sistem Berbasis AI">
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
            <a href="{{ url('/privacy-policy') }}" class="text-xs text-slate-400 hover:text-white transition-colors">Kebijakan Privasi</a>
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
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(6,182,212,0.12);">
                <svg class="w-5 h-5" style="color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Syarat &amp; Ketentuan Layanan</h1>
                <p class="text-xs" style="color:#64748b;">Terakhir diperbarui: {{ date('d F Y') }}</p>
            </div>
        </div>
        <div class="p-3 rounded-lg text-xs" style="background:rgba(220,38,38,0.05);border:1px solid rgba(220,38,38,0.15);color:#64748b;">
            Dengan menggunakan layanan <strong class="text-white">RedSim</strong> di <a href="https://redsim.my.id" style="color:#22d3ee;">redsim.my.id</a>, Anda menyetujui syarat dan ketentuan berikut. Harap baca dengan saksama sebelum menggunakan platform kami.
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-4xl mx-auto px-6 pb-16">
        <div class="prose">

            <h2>1. Penerimaan Syarat</h2>
            <p>Dengan mengakses atau menggunakan platform RedSim, Anda menyatakan bahwa Anda telah membaca, memahami, dan menyetujui untuk terikat oleh syarat dan ketentuan ini. Jika Anda tidak setuju, harap hentikan penggunaan layanan kami.</p>

            <h2>2. Deskripsi Layanan</h2>
            <p>RedSim adalah platform analisis keamanan siber berbasis kecerdasan buatan (AI) yang menyediakan:</p>
            <ul>
                <li><strong>Pemindai URL</strong> — Analisis potensi ancaman pada URL yang dicurigai</li>
                <li><strong>Analisis Log</strong> — Pemindaian log sistem untuk mendeteksi aktivitas mencurigakan</li>
                <li><strong>Analisis Kode</strong> — Pemeriksaan kerentanan pada kode sumber</li>
                <li><strong>Platform Edukasi</strong> — Materi pembelajaran dan tantangan keamanan siber</li>
                <li><strong>AI Chat Assistant</strong> — Asisten AI untuk konsultasi keamanan</li>
            </ul>

            <h2>3. Pendaftaran dan Akun</h2>
            <h3>3.1 Kelayakan</h3>
            <p>Layanan RedSim tersedia untuk pengguna yang:</p>
            <ul>
                <li>Berusia minimal 13 tahun (atau usia legal di yurisdiksi Anda)</li>
                <li>Memiliki alamat email yang valid</li>
                <li>Tidak pernah diblokir dari platform kami sebelumnya</li>
            </ul>

            <h3>3.2 Keamanan Akun</h3>
            <p>Anda bertanggung jawab untuk menjaga kerahasiaan kata sandi dan keamanan akun Anda. Segera laporkan kepada kami jika terjadi penggunaan akun yang tidak sah.</p>

            <h2>4. Penggunaan yang Diizinkan</h2>
            <p>Anda diizinkan menggunakan RedSim untuk:</p>
            <ul>
                <li>Menganalisis sistem, URL, dan kode <strong>yang Anda miliki atau memiliki izin</strong> untuk dianalisis</li>
                <li>Belajar dan meningkatkan pengetahuan keamanan siber</li>
                <li>Mengikuti tantangan dan kompetisi keamanan yang disediakan platform</li>
            </ul>

            <h2>5. Penggunaan yang Dilarang</h2>
            <p>Anda <strong>dilarang keras</strong> menggunakan RedSim untuk:</p>
            <ul>
                <li>Menganalisis atau menyerang sistem yang bukan milik Anda tanpa izin</li>
                <li>Melakukan aktivitas ilegal, penipuan, atau kejahatan siber</li>
                <li>Mencoba mengakses data pengguna lain tanpa otorisasi</li>
                <li>Melakukan <em>reverse engineering</em> atau mengekstrak data dari platform</li>
                <li>Membebani server secara berlebihan (DDoS, scraping massal, dll.)</li>
                <li>Menyebarkan malware, phishing, atau konten berbahaya</li>
                <li>Menggunakan layanan untuk tujuan komersial tanpa izin tertulis</li>
            </ul>

            <h2>6. Integrasi Google OAuth (Fitur AI)</h2>
            <p>Fitur koneksi model AI melalui Google OAuth bersifat opsional. Dengan mengaktifkan fitur ini, Anda:</p>
            <ul>
                <li>Memberikan izin kepada RedSim untuk mengakses Gemini API atas nama Anda</li>
                <li>Memahami bahwa penggunaan API tunduk pada <a href="https://ai.google.dev/terms" target="_blank">Syarat Layanan Google AI</a></li>
                <li>Bertanggung jawab atas biaya penggunaan API Gemini (jika berlaku)</li>
                <li>Dapat mencabut akses kapan saja dari pengaturan profil atau akun Google Anda</li>
            </ul>

            <h2>7. Batasan Penggunaan (Free Tier)</h2>
            <p>Akun gratis dibatasi oleh:</p>
            <ul>
                <li>Jumlah scan harian sesuai kuota yang berlaku</li>
                <li>Akses fitur dasar (tanpa fitur premium)</li>
                <li>Penyimpanan riwayat scan terbatas</li>
            </ul>
            <p>Batas kuota dapat berubah sewaktu-waktu sesuai kebijakan platform.</p>

            <h2>8. Hak Kekayaan Intelektual</h2>
            <p>Seluruh konten, desain, logo, dan kode sumber RedSim adalah milik tim pengembang RedSim dan dilindungi hak cipta. Pengguna tidak diizinkan menyalin, mendistribusikan, atau memodifikasi tanpa izin tertulis.</p>
            <p>Hasil analisis yang dihasilkan untuk akun Anda adalah milik Anda dan dapat Anda unduh atau ekspor.</p>

            <h2>9. Penafian (Disclaimer)</h2>
            <p>RedSim menyediakan layanan analisis keamanan berbasis AI <strong>"sebagaimana adanya"</strong>. Kami tidak menjamin:</p>
            <ul>
                <li>Keakuratan 100% dari setiap hasil analisis AI</li>
                <li>Ketersediaan layanan tanpa gangguan</li>
                <li>Bahwa sistem Anda sepenuhnya aman berdasarkan hasil analisis kami</li>
            </ul>
            <p>Hasil analisis RedSim bersifat informatif dan <strong>tidak menggantikan audit keamanan profesional</strong>.</p>

            <h2>10. Batasan Tanggung Jawab</h2>
            <p>RedSim tidak bertanggung jawab atas kerugian yang timbul akibat:</p>
            <ul>
                <li>Penggunaan hasil analisis yang tidak tepat</li>
                <li>Keputusan bisnis berdasarkan rekomendasi AI</li>
                <li>Gangguan layanan di luar kendali kami</li>
                <li>Tindakan pihak ketiga terhadap sistem Anda</li>
            </ul>

            <h2>11. Penghentian Layanan</h2>
            <p>Kami berhak menangguhkan atau menghentikan akun Anda jika terbukti melanggar syarat ini tanpa pemberitahuan sebelumnya. Pengguna yang bertindak dengan itikad baik dapat mengajukan keberatan melalui email kami.</p>

            <h2>12. Perubahan Syarat</h2>
            <p>Kami dapat memperbarui syarat ini sewaktu-waktu. Perubahan signifikan akan diumumkan melalui email atau notifikasi dalam aplikasi. Penggunaan berlanjut setelah perubahan dianggap sebagai penerimaan Anda.</p>

            <h2>13. Hukum yang Berlaku</h2>
            <p>Syarat ini diatur oleh hukum Republik Indonesia. Setiap sengketa akan diselesaikan melalui musyawarah atau yurisdiksi pengadilan yang berlaku di Indonesia.</p>

            <h2>14. Hubungi Kami</h2>
            <p>Untuk pertanyaan terkait syarat layanan:</p>
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
            <a href="{{ url('/privacy-policy') }}" class="hover:text-white transition-colors">Kebijakan Privasi</a>
            <span>·</span>
            <span style="color:#22d3ee;">Syarat Layanan</span>
        </div>
        <p class="mt-2 text-xs" style="color:#334155;">© {{ date('Y') }} RedSim. Hak cipta dilindungi.</p>
    </footer>
</body>
</html>
