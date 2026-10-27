<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi WhatsApp — RedSim</title>
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
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-[#0f1629] border border-[#1e2d4a] flex items-center justify-center shadow-lg">
            <svg class="w-10 h-10 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        </div>

        <h2 class="text-2xl font-bold text-white mb-4">Cek WhatsApp Anda</h2>
        <p class="text-[#94a3b8] mb-6 leading-relaxed">
            Kami telah mengirimkan <strong class="text-white">link verifikasi</strong> ke nomor WhatsApp Anda.
            <br><br>
            Klik link tersebut untuk mengaktifkan akun Anda.
            <br>
            <span class="text-[#f59e0b] text-sm">⏰ Link berlaku selama 30 menit.</span>
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

        {{-- Form kirim ulang verifikasi --}}
        <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-6 shadow-2xl mb-6" x-data="{ showResend: false }">
            <p class="text-sm text-[#94a3b8] mb-4">Belum menerima link? Kirim ulang link verifikasi.</p>

            <button @click="showResend = !showResend" class="text-[#00d4ff] hover:underline font-medium text-sm mb-4">
                <span x-show="!showResend">Kirim Ulang Link Verifikasi</span>
                <span x-show="showResend" style="display:none">Sembunyikan</span>
            </button>

            <form method="POST" action="{{ route('verifikasi.kirim-ulang') }}" class="space-y-4" x-show="showResend" x-transition style="display:none">
                @csrf
                <div>
                    <label for="phone" class="block text-sm font-medium text-[#94a3b8] mb-2 text-left">Nomor WhatsApp</label>
                    <input type="text" id="phone" name="phone" value="{{ session('phone', old('phone')) }}" required
                           class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none transition-colors"
                           placeholder="081234567890">
                </div>
                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-[#25D366] to-[#128C7E] text-white font-semibold hover:shadow-lg hover:shadow-[#25D366]/25 transition-all duration-300 hover:-translate-y-0.5">
                    Kirim Ulang Link Verifikasi
                </button>
            </form>
        </div>

        <a href="{{ route('masuk') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all hover:-translate-y-0.5">
            Kembali ke Halaman Masuk
        </a>
    </div>
</body>
</html>
