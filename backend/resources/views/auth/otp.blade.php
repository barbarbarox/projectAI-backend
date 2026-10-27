<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login OTP WhatsApp — RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('app.env') === 'local' ? env('RECAPTCHA_SITE_KEY') : env('RECAPTCHA_SITE_KEY') }}"></script>
</head>
<body class="min-h-screen bg-[#0a0e1a] flex items-center justify-center p-4 antialiased">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-[#00d4ff]/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-[#7c3aed]/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">
        <div class="text-center mb-8">
            <a href="{{ route('beranda') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('logo-redsim1:1.svg') }}" alt="RedSim Logo" class="w-12 h-12 rounded-xl shadow-lg shadow-[#00d4ff]/20 object-contain">
            </a>
            <h2 class="mt-4 text-2xl font-bold text-white">Login OTP WhatsApp</h2>
            <p class="mt-2 text-sm text-[#94a3b8]">Masukkan nomor WhatsApp Anda untuk menerima kode OTP</p>
        </div>

        <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-8 shadow-2xl">
            @if($errors->any())
            <div class="mb-6 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('otp.kirim') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-[#94a3b8] mb-2">Nomor WhatsApp</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required autofocus
                           class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#25D366] focus:ring-1 focus:ring-[#25D366]/50 focus:outline-none transition-colors"
                           placeholder="Contoh: 081234567890">
                </div>
                
                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-[#25D366] to-[#128C7E] text-white font-semibold hover:shadow-lg hover:shadow-[#25D366]/25 transition-all duration-300 hover:-translate-y-0.5">
                    Kirim Kode OTP
                </button>
            </form>

            <a href="{{ route('masuk') }}" class="w-full mt-4 flex items-center justify-center gap-2 py-3 rounded-xl bg-[#1e2d4a]/50 hover:bg-[#1e2d4a] transition-colors text-[#94a3b8] hover:text-white font-medium text-sm border border-[#1e2d4a]">
                Kembali ke Login Email
            </a>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ env("RECAPTCHA_SITE_KEY") }}', {action: 'send_otp'}).then(function(token) {
                        document.getElementById('g-recaptcha-response').value = token;
                        form.submit();
                    });
                });
            });
        });
    </script>
</body>
</html>
