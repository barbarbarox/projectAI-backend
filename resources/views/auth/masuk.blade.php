<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('app.env') === 'local' ? env('RECAPTCHA_SITE_KEY') : env('RECAPTCHA_SITE_KEY') }}"></script>
</head>
<body class="min-h-screen bg-[#0a0e1a] flex items-center justify-center p-4 antialiased">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @keyframes bounce-in { 0% { opacity:0; transform:translateY(-12px) scale(0.95); } 60% { transform:translateY(4px) scale(1.02); } 100% { opacity:1; transform:translateY(0) scale(1); } }
        .animate-bounce-in { animation: bounce-in 0.5s ease-out; }
        @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 8px rgba(239,68,68,0.3); } 50% { box-shadow: 0 0 20px rgba(239,68,68,0.6); } }
        .animate-pulse-glow { animation: pulse-glow 2s ease-in-out infinite; }
    </style>
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-[#00d4ff]/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-[#7c3aed]/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">
        <div class="text-center mb-8">
            <a href="{{ route('beranda') }}" class="inline-flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#00d4ff] to-[#7c3aed] flex items-center justify-center shadow-lg shadow-[#00d4ff]/20">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </a>
            <h2 class="mt-4 text-2xl font-bold text-white">Masuk ke RedSim</h2>
            <p class="mt-2 text-sm text-[#94a3b8]">Masukkan kredensial Anda untuk melanjutkan</p>
        </div>

        <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-8 shadow-2xl">
            {{-- Rate Limit / Throttle Warning --}}
            @if($errors->has('throttle'))
            <div class="mb-6 px-4 py-4 rounded-xl bg-red-500/10 border border-red-500/40 text-red-300 text-sm animate-bounce-in animate-pulse-glow" x-data="{ show: true }" x-show="show">
                <div class="flex items-start gap-3">
                    <span class="text-2xl flex-shrink-0">⛔</span>
                    <div class="flex-1">
                        <p class="font-bold text-red-200 text-base">Sesi Diblokir!</p>
                        <p class="text-red-400 mt-1">{{ $errors->first('throttle') }}</p>
                    </div>
                    <button @click="show = false" class="text-red-400/50 hover:text-red-300 flex-shrink-0">&times;</button>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 px-4 py-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-sm flex items-center gap-3 animate-bounce-in" x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <span class="text-2xl flex-shrink-0">🔐</span>
                <div class="flex-1">
                    <p class="font-semibold text-amber-200">Login dulu boss!</p>
                    <p class="text-xs text-amber-400/80 mt-0.5">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-amber-400/50 hover:text-amber-300 flex-shrink-0">&times;</button>
            </div>
            @endif

            @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any() && !$errors->has('throttle'))
            <div class="mb-6 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                @foreach($errors->all() as $error)
                @if($error !== $errors->first('throttle'))
                <p>{{ $error }}</p>
                @endif
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('masuk') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                <div>
                    <label for="email" class="block text-sm font-medium text-[#94a3b8] mb-2">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none transition-colors"
                           placeholder="nama@email.com">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-[#94a3b8] mb-2">Kata Sandi</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none transition-colors"
                           placeholder="••••••••">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-[#0a0e1a] border-[#1e2d4a] text-[#00d4ff] focus:ring-[#00d4ff]/50">
                        <span class="text-sm text-[#94a3b8]">Ingat saya</span>
                    </label>
                    <a href="{{ route('lupa-password') }}" class="text-sm text-[#00d4ff] hover:underline font-medium">Lupa kata sandi?</a>
                </div>
                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300 hover:-translate-y-0.5">
                    Masuk
                </button>
            </form>

            <a href="{{ route('otp') }}" class="w-full mt-4 flex items-center justify-center gap-2 py-3 rounded-xl bg-[#1e2d4a]/50 hover:bg-[#1e2d4a] transition-colors text-white font-medium text-sm border border-[#1e2d4a]">
                <svg class="w-5 h-5 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                Masuk dengan WhatsApp OTP
            </a>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-[#1e2d4a]"></div></div>
                <div class="relative flex justify-center"><span class="bg-[#0f1629] px-4 text-xs text-[#64748b]">atau</span></div>
            </div>

            <a href="{{ route('oauth.google') }}" class="w-full flex items-center justify-center gap-3 py-3 rounded-xl bg-white hover:bg-gray-100 transition-colors text-gray-700 font-medium text-sm">
                <svg class="w-5 h-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                Masuk dengan Google
            </a>
        </div>

        <p class="text-center mt-6 text-sm text-[#94a3b8]">
            Belum punya akun?
            <a href="{{ route('daftar') }}" class="text-[#00d4ff] hover:underline font-medium">Daftar sekarang</a>
        </p>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ env("RECAPTCHA_SITE_KEY") }}', {action: 'login'}).then(function(token) {
                        document.getElementById('g-recaptcha-response').value = token;
                        form.submit();
                    });
                });
            });
        });
    </script>
</body>
</html>
