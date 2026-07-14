<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Kesalahan Server</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[#0a0e1a] flex items-center justify-center antialiased">
    <div class="text-center px-6">
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center">
            <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h1 class="text-7xl font-black text-red-400 mb-2">500</h1>
        <h2 class="text-xl font-semibold text-white mb-3">Kesalahan Server Internal</h2>
        <p class="text-[#94a3b8] mb-8 max-w-md mx-auto">Terjadi kesalahan pada server. Tim teknis kami telah diberitahu. Silakan coba lagi nanti.</p>
        <a href="/" class="inline-block px-6 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all">← Kembali ke Beranda</a>
    </div>
</body>
</html>
