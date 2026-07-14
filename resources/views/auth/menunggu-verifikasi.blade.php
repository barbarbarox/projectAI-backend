<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menunggu Verifikasi — RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0a0e1a] flex items-center justify-center p-4 antialiased">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-[#00d4ff]/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-[#7c3aed]/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md text-center">
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-[#0f1629] border border-[#1e2d4a] flex items-center justify-center shadow-lg">
            <svg class="w-10 h-10 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>

        <h2 class="text-2xl font-bold text-white mb-4">Menunggu Verifikasi</h2>
        <p class="text-[#94a3b8] mb-8 leading-relaxed">
            Terima kasih telah mendaftar di RedSim! Akun Anda telah berhasil dibuat, namun saat ini <strong class="text-white">sedang menunggu persetujuan dari Administrator</strong>.
            <br><br>
            Silakan coba masuk secara berkala untuk mengecek status persetujuan Anda.
        </p>

        <a href="{{ route('masuk') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all hover:-translate-y-0.5">
            Kembali ke Halaman Masuk
        </a>
    </div>
</body>
</html>
