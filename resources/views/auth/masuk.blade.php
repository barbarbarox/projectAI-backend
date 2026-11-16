<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto+Mono:wght@400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              background: '#0a0a0d',
              surface:    '#151316',
              header:     '#100f11',
              card:       '#191316',
              'border-default': '#30292e',
              'border-active':  '#44353e',
              'border-focus':   '#a24758',
              'brand-red':  '#f04452',
              'brand-red2': '#e33445',
              'text-primary': '#f5f3f4',
              'text-muted':   '#aaa2a8',
              'text-dim':     '#91868d',
            },
            fontFamily: {
              sans: ['Inter', 'sans-serif'],
              mono: ['"Roboto Mono"', 'monospace'],
            },
          }
        }
      }
    </script>
    <script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
      *, *::before, *::after { -webkit-font-smoothing: antialiased; box-sizing: border-box; }
      a { text-decoration: none; }
      input::placeholder { color: #91868d; }
      input[type="checkbox"] { appearance: none; -webkit-appearance: none; }
      input[type="checkbox"]:checked { background-color: #e33445; border-color: #e33445; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3E%3C/svg%3E"); background-size: 100%; }
    </style>
</head>
<body class="bg-background font-sans min-h-screen">

  {{-- ═══════════════════════════════════════════════════════
       HEADER
  ══════════════════════════════════════════════════════════ --}}
  <header class="w-full bg-header border-b border-border-default h-[68px] flex items-center px-6 lg:px-10">
    <div class="max-w-7xl w-full mx-auto flex items-center justify-between">
      {{-- Brand --}}
      <div class="flex items-center gap-2">
        <div class="w-[3px] h-[18px] bg-brand-red rounded-sm"></div>
        <span class="text-brand-red font-bold text-[22px] leading-none">RED</span>
        <span class="text-text-primary font-bold text-[22px] leading-none">SIM</span>
        <span class="font-mono text-text-dim text-[10px] ml-2 hidden sm:block">AI SECURITY PLATFORM</span>
      </div>
      {{-- Tagline --}}
      <p class="text-text-muted text-[11px] hidden lg:block">
        Analisis keamanan. Simulasi serangan. Perbaikan.
      </p>
    </div>
  </header>

  {{-- ═══════════════════════════════════════════════════════
       MAIN — full-screen centered container
  ══════════════════════════════════════════════════════════ --}}
  <main class="min-h-[calc(100vh-68px-48px)] flex items-center justify-center p-4 sm:p-6 lg:p-10">
    <div class="max-w-7xl w-full mx-auto">

      {{-- Grid: left brand card + right form --}}
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-stretch">

        {{-- ─── LEFT: Brand Story Card ─────────────────────── --}}
        <div class="hidden lg:flex flex-col justify-between h-full rounded-2xl overflow-hidden border border-[#41232d] p-12"
             style="background: linear-gradient(122deg, rgba(43,23,30,1) 0%, rgba(20,17,20,1) 100%); min-height: 580px;">

          {{-- Top section --}}
          <div>
            {{-- Eyebrow --}}
            <div class="flex items-center gap-2 mb-8">
              <div class="w-[6px] h-[6px] rounded-full bg-brand-red"></div>
              <span class="font-mono text-[#f19ba6] text-[11px] tracking-wider">SECURITY STARTS HERE</span>
            </div>
            {{-- Hero text --}}
            <div class="mb-6">
              <h1 class="font-semibold text-text-primary text-[46px] leading-[1.2]">
                Kenali celah.<br>Uji pertahanan.
              </h1>
              <span class="font-semibold text-[#f56675] text-[46px] leading-[1.2]">Perkuat sistem.</span>
            </div>
            <p class="text-[#bdaab3] text-[15px] leading-[1.6]">
              Satu ruang untuk memahami risiko, menguji keamanan,<br>
              dan menemukan langkah perbaikan web Anda.
            </p>
          </div>

          {{-- Middle: Feature list --}}
          <div class="border-t border-[#482c36] pt-8 space-y-6">
            {{-- Feature 1 --}}
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-11 h-11 rounded-[10px] bg-[#2b1d24] border border-[#4e2d39] flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f19ba6" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
              </div>
              <div class="flex-1">
                <p class="font-medium text-text-primary text-[15px]">Analisis keamanan</p>
                <p class="text-[#b8a4ae] text-[12px] mt-0.5">Temukan celah sebelum menjadi ancaman.</p>
              </div>
              <span class="font-mono text-[#957582] text-[11px] self-center">01</span>
            </div>
            {{-- Feature 2 --}}
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-11 h-11 rounded-[10px] bg-[#2b1d24] border border-[#4e2d39] flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f19ba6" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
              </div>
              <div class="flex-1">
                <p class="font-medium text-text-primary text-[15px]">Simulasi serangan</p>
                <p class="text-[#b8a4ae] text-[12px] mt-0.5">Uji ketahanan web dalam skenario serangan.</p>
              </div>
              <span class="font-mono text-[#957582] text-[11px] self-center">02</span>
            </div>
            {{-- Feature 3 --}}
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-11 h-11 rounded-[10px] bg-[#2b1d24] border border-[#4e2d39] flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f19ba6" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <div class="flex-1">
                <p class="font-medium text-text-primary text-[15px]">Rekomendasi perbaikan</p>
                <p class="text-[#b8a4ae] text-[12px] mt-0.5">Ubah hasil analisis menjadi langkah nyata.</p>
              </div>
              <span class="font-mono text-[#957582] text-[11px] self-center">03</span>
            </div>
          </div>

          {{-- Bottom footer tag --}}
          <div>
            <p class="font-mono text-[#a0828e] text-[10px]">REDSIM / DEFEND WITH INSIGHT</p>
          </div>
        </div>

        {{-- ─── RIGHT: Login Form Card ──────────────────────── --}}
        <div class="flex flex-col justify-center" x-data="{ showPw: false }">

          {{-- Form header --}}
          <div class="mb-6">
            <span class="font-mono text-[#f58b99] text-[10px] tracking-wider uppercase">SELAMAT DATANG KEMBALI</span>
            <h2 class="text-text-primary font-semibold text-[28px] sm:text-[30px] mt-2 mb-1">Masuk ke RedSim</h2>
            <p class="text-text-muted text-[14px]">Masukkan kredensial Anda untuk melanjutkan.</p>
          </div>

          {{-- Notice box --}}
          <div class="flex items-start gap-3 bg-[#26191f] border border-[#4b2b36] rounded-lg px-4 py-3 mb-6">
            <svg class="w-5 h-5 text-[#f2c3cc] flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <div>
              <p class="text-[#f2c3cc] text-[13px] font-medium leading-snug">Masuk untuk mengakses fitur RedSim.</p>
              <p class="text-[#bea3ae] text-[11px] mt-0.5">Gunakan akun yang sudah terdaftar.</p>
            </div>
          </div>

          {{-- Error / Success alerts --}}
          @if(session('error'))
          <div class="mb-4 px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-300 text-[13px]">
              {{ session('error') }}
          </div>
          @endif
          @if(session('success'))
          <div class="mb-4 px-4 py-3 bg-green-500/10 border border-green-500/30 rounded-lg text-green-300 text-[13px]">
              {{ session('success') }}
          </div>
          @endif
          @if($errors->any() && !$errors->has('throttle'))
          <div class="mb-4 px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-300 text-[13px] space-y-0.5">
              @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
          </div>
          @endif
          @if($errors->has('throttle'))
          <div class="mb-4 px-4 py-3 bg-red-500/15 border border-red-500/40 rounded-lg text-red-300 text-[13px]">
              {{ $errors->first('throttle') }}
          </div>
          @endif

          {{-- Form --}}
          <form id="loginForm" method="POST" action="{{ route('masuk') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

            {{-- Email --}}
            <div>
              <label for="email" class="block text-text-muted text-[13px] font-medium mb-2">Email</label>
              <input
                type="email" id="email" name="email"
                value="{{ old('email') }}"
                placeholder="email@contoh.com"
                autocomplete="email"
                class="w-full h-12 bg-[#100e11] border border-border-active rounded-lg px-4 text-text-primary text-[14px] outline-none transition-colors focus:border-border-focus"
              >
            </div>

            {{-- Password --}}
            <div>
              <label for="password" class="block text-text-muted text-[13px] font-medium mb-2">Kata sandi</label>
              <div class="relative flex items-center">
                <input
                  :type="showPw ? 'text' : 'password'"
                  id="password" name="password"
                  placeholder="••••••••"
                  autocomplete="current-password"
                  class="w-full h-12 bg-[#100e11] border border-border-active rounded-lg pl-4 pr-11 text-text-primary text-[14px] outline-none transition-colors focus:border-border-focus"
                >
                <button type="button" @click="showPw = !showPw"
                  class="absolute right-3 text-text-dim hover:text-text-muted transition-colors">
                  <svg x-show="!showPw" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  <svg x-show="showPw"  width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
              </div>
            </div>

            {{-- Remember me + Forgot password --}}
            <div class="flex items-center justify-between">
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}
                  class="w-4 h-4 rounded bg-[#100e11] border border-[#67525f] cursor-pointer">
                <span class="text-text-muted text-[12px]">Ingat saya</span>
              </label>
              <a href="{{ route('lupa-password') }}" class="text-[#f68d99] text-[12px] font-medium hover:text-[#f04452] transition-colors">
                Lupa kata sandi?
              </a>
            </div>

            {{-- Submit --}}
            <button type="submit"
              class="w-full h-12 bg-brand-red2 hover:bg-[#c0202f] text-white font-semibold text-[14px] rounded-lg transition-colors flex items-center justify-center gap-2">
              Masuk
            </button>
          </form>

          {{-- Divider --}}
          <div class="flex items-center gap-4 my-6">
            <div class="flex-1 h-px bg-border-default"></div>
            <span class="text-text-dim text-[12px]">atau</span>
            <div class="flex-1 h-px bg-border-default"></div>
          </div>

          {{-- Google sign in --}}
          <a href="{{ route('oauth.google') }}"
            class="w-full h-12 bg-[#191619] border border-[#44373f] hover:border-border-focus rounded-lg flex items-center justify-center gap-3 transition-colors">
            <span class="text-text-primary font-bold text-[18px] leading-none">G</span>
            <span class="text-text-primary font-semibold text-[14px]">Masuk dengan Google</span>
          </a>

          {{-- Register link --}}
          <p class="text-center mt-6 text-[13px]">
            <span class="text-text-muted">Belum punya akun? </span>
            <a href="{{ route('daftar') }}" class="text-[#f68d99] font-semibold hover:text-[#f04452] transition-colors">Daftar gratis</a>
          </p>
        </div>
        {{-- end right --}}

      </div>{{-- end grid --}}
    </div>{{-- end max-w --}}
  </main>

  {{-- ═══════════════════════════════════════════════════════
       FOOTER
  ══════════════════════════════════════════════════════════ --}}
  <footer class="h-12 flex items-center px-6 lg:px-10 border-t border-border-default">
    <div class="max-w-7xl w-full mx-auto flex items-center gap-6">
      <span class="text-text-muted font-semibold text-[10px]">REDSIM</span>
      <span class="text-text-dim text-[10px]">Platform analisis keamanan web</span>
      <span class="text-text-dim text-[10px] ml-auto">Gunakan hanya pada sistem yang Anda miliki atau diizinkan.</span>
    </div>
  </footer>

<script>
grecaptcha.ready(function() {
    grecaptcha.execute('{{ env("RECAPTCHA_SITE_KEY") }}', {action: 'login'}).then(function(token) {
        document.getElementById('g-recaptcha-response').value = token;
    });
});
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    grecaptcha.execute('{{ env("RECAPTCHA_SITE_KEY") }}', {action: 'login'}).then(function(token) {
        document.getElementById('g-recaptcha-response').value = token;
        document.getElementById('loginForm').submit();
    });
});
</script>
</body>
</html>
