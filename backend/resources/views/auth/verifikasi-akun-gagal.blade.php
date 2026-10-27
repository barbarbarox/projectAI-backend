<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Gagal — RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-[#0a0e1a] flex items-center justify-center p-4 antialiased">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-[#00d4ff]/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-[#7c3aed]/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md text-center">
        {{-- Icon --}}
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-[#0f1629] border border-red-500/30 flex items-center justify-center shadow-lg">
            <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
        </div>

        <h2 class="text-2xl font-bold text-white mb-4">Verifikasi Gagal</h2>
        <p class="text-[#94a3b8] mb-6 leading-relaxed">
            {{ $pesan }}
        </p>

        @if(session('success'))
        <div class="mb-6 px-4 py-3 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
            @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        @if(!empty($dapat_kirim_ulang) && $dapat_kirim_ulang)
        {{-- Form kirim ulang --}}
        <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-6 shadow-2xl mb-6">
            <p class="text-sm text-[#94a3b8] mb-4">Masukkan nomor WhatsApp Anda untuk menerima link verifikasi baru.</p>
            <form method="POST" action="{{ route('verifikasi.kirim-ulang') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="phone" class="block text-sm font-medium text-[#94a3b8] mb-2 text-left">Nomor WhatsApp</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                           class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none transition-colors"
                           placeholder="081234567890">
                </div>
                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-[#25D366] to-[#128C7E] text-white font-semibold hover:shadow-lg hover:shadow-[#25D366]/25 transition-all duration-300 hover:-translate-y-0.5">
                    Kirim Ulang Link Verifikasi
                </button>
            </form>
        </div>
        @endif

        <a href="{{ route('masuk') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all hover:-translate-y-0.5">
            Kembali ke Halaman Masuk
        </a>
    </div>
</body>
</html>
