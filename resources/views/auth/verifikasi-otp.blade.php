<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP — RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0a0e1a] flex items-center justify-center p-4 antialiased">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-[#00d4ff]/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-[#7c3aed]/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white">Verifikasi OTP</h2>
            <p class="mt-2 text-sm text-[#94a3b8]">Masukkan 6 digit kode yang dikirim ke WhatsApp Anda ({{ $phone }})</p>
        </div>

        <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-8 shadow-2xl">
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

            <form method="POST" action="{{ route('otp.proses-verifikasi') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="phone" value="{{ $phone }}">
                
                <div>
                    <label for="otp" class="block text-sm font-medium text-[#94a3b8] mb-2 text-center">Kode 6 Digit</label>
                    <input type="text" id="otp" name="otp" required autofocus maxlength="6" pattern="\d{6}"
                           class="w-full text-center tracking-[0.5em] text-2xl px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#25D366] focus:ring-1 focus:ring-[#25D366]/50 focus:outline-none transition-colors"
                           placeholder="••••••">
                </div>
                
                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-[#25D366] to-[#128C7E] text-white font-semibold hover:shadow-lg hover:shadow-[#25D366]/25 transition-all duration-300 hover:-translate-y-0.5">
                    Verifikasi & Masuk
                </button>
            </form>

            <p class="text-center mt-6 text-sm text-[#94a3b8]">
                Tidak menerima kode?
                <a href="{{ route('otp') }}" class="text-[#25D366] hover:underline font-medium">Coba lagi</a>
            </p>
        </div>
    </div>
</body>
</html>
