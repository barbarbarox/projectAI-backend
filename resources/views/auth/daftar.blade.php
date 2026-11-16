<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto+Mono:wght@400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              background: '#0a0a0d',
              header:     '#100f11',
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
    </style>
</head>
<body class="bg-background font-sans min-h-screen">

  {{-- ═══════════════════════════════════════════════════════
       HEADER
  ══════════════════════════════════════════════════════════ --}}
  <header class="w-full bg-header border-b border-border-default h-[68px] flex items-center px-6 lg:px-10">
    <div class="max-w-7xl w-full mx-auto flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="w-[3px] h-[18px] bg-brand-red rounded-sm"></div>
        <span class="text-brand-red font-bold text-[22px] leading-none">RED</span>
        <span class="text-text-primary font-bold text-[22px] leading-none">SIM</span>
        <span class="font-mono text-text-dim text-[10px] ml-2 hidden sm:block">AI SECURITY PLATFORM</span>
      </div>
      <p class="text-text-muted text-[11px] hidden lg:block">
        Analisis keamanan. Simulasi serangan. Perbaikan.
      </p>
    </div>
  </header>

  {{-- ═══════════════════════════════════════════════════════
       MAIN
  ══════════════════════════════════════════════════════════ --}}
  <main class="min-h-[calc(100vh-68px-48px)] flex items-center justify-center p-4 sm:p-6 lg:p-10">
    <div class="max-w-7xl w-full mx-auto">

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-stretch">

        {{-- ─── LEFT: Brand Story Card ─────────────────────── --}}
        <div class="hidden lg:flex flex-col justify-between h-full rounded-2xl overflow-hidden border border-[#41232d] p-12"
             style="background: linear-gradient(122deg, rgba(43,23,30,1) 0%, rgba(20,17,20,1) 100%); min-height: 580px;">

          <div>
            <div class="flex items-center gap-2 mb-8">
              <div class="w-[6px] h-[6px] rounded-full bg-brand-red"></div>
              <span class="font-mono text-[#f19ba6] text-[11px] tracking-wider">SECURITY STARTS HERE</span>
            </div>
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

          <div class="border-t border-[#482c36] pt-8 space-y-6">
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

          <div>
            <p class="font-mono text-[#a0828e] text-[10px]">REDSIM / DEFEND WITH INSIGHT</p>
          </div>
        </div>

        {{-- ─── RIGHT: Register Form ────────────────────────── --}}
        <div class="flex flex-col justify-center"
          x-data="{
            step: 1,
            showPw: false,
            showPwConf: false,
            name: '{{ old('name') }}',
            nama_lengkap: '{{ old('nama_lengkap') }}',
            email: '{{ old('email') }}',
            phone: '{{ old('phone') }}',
            password: '',
            passwordStrength: 0,
            checkStrength(val) {
              let s = 0;
              if (val.length >= 8) s++;
              if (/[A-Z]/.test(val)) s++;
              if (/[0-9]/.test(val)) s++;
              if (/[^A-Za-z0-9]/.test(val)) s++;
              this.passwordStrength = s;
            }
          }">

          {{-- Form header --}}
          <div class="mb-6">
            <span class="font-mono text-[#f58b99] text-[10px] tracking-wider uppercase">MULAI DENGAN AKUN GRATIS</span>
            <h2 class="text-text-primary font-semibold text-[28px] sm:text-[30px] mt-2 mb-1">Buat akun RedSim</h2>
            <p class="text-text-muted text-[14px]">Daftar gratis untuk memulai analisis keamanan.</p>
          </div>

          {{-- Error messages --}}
          @if($errors->any())
          <div class="mb-5 px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-300 text-[13px] space-y-0.5">
              @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
          </div>
          @endif

          {{-- ── Progress Steps ───────────────────────────── --}}
          <div class="flex items-center mb-6">
            {{-- Step 1 --}}
            <div class="flex-shrink-0 w-8 h-8 rounded-full border flex items-center justify-center font-semibold text-[13px] transition-colors"
                 :style="step >= 1 ? 'background-color:#e33445;border-color:#f45b6c;color:#fff' : 'background-color:#1b161a;border-color:#4a3943;color:#aaa2a8'">
              1
            </div>
            <div class="flex-1 h-px mx-1 transition-colors" :style="step >= 2 ? 'background-color:#60404d' : 'background-color:#30292e'"></div>
            {{-- Step 2 --}}
            <div class="flex-shrink-0 w-8 h-8 rounded-full border flex items-center justify-center font-semibold text-[13px] transition-colors"
                 :style="step >= 2 ? 'background-color:#e33445;border-color:#f45b6c;color:#fff' : 'background-color:#1b161a;border-color:#4a3943;color:#aaa2a8'">
              2
            </div>
            <div class="flex-1 h-px mx-1 transition-colors" :style="step >= 3 ? 'background-color:#60404d' : 'background-color:#30292e'"></div>
            {{-- Step 3 --}}
            <div class="flex-shrink-0 w-8 h-8 rounded-full border flex items-center justify-center font-semibold text-[13px] transition-colors"
                 :style="step >= 3 ? 'background-color:#e33445;border-color:#f45b6c;color:#fff' : 'background-color:#1b161a;border-color:#4a3943;color:#aaa2a8'">
              3
            </div>
          </div>

          {{-- Step label --}}
          <div class="flex items-center justify-between mb-5">
            <span class="text-text-primary font-semibold text-[16px]"
                  x-text="step === 1 ? 'Informasi akun' : step === 2 ? 'Email & kata sandi' : 'Konfirmasi'"></span>
            <span class="text-text-muted text-[11px]" x-text="`Langkah ${step} dari 3`"></span>
          </div>

          {{-- ── FORM ─────────────────────────────────────── --}}
          <form id="registerForm" method="POST" action="{{ route('daftar') }}">
            @csrf
            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            <input type="hidden" name="name"          :value="name">
            <input type="hidden" name="nama_lengkap"  :value="nama_lengkap">
            <input type="hidden" name="email"         :value="email">
            <input type="hidden" name="phone"         :value="phone">

            {{-- ════ STEP 1 ════ --}}
            <div x-show="step === 1" class="space-y-5">
              <div>
                <label class="block text-text-muted text-[13px] font-medium mb-2">Nama pengguna</label>
                <input type="text" x-model="name" placeholder="barox" autocomplete="username"
                  class="w-full h-12 bg-[#100e11] border border-border-focus rounded-lg px-4 text-text-primary text-[14px] outline-none transition-colors focus:border-border-focus">
              </div>
              <div>
                <label class="block text-text-muted text-[13px] font-medium mb-2">Nama lengkap <span class="text-text-dim font-normal">(opsional)</span></label>
                <input type="text" x-model="nama_lengkap" placeholder="Nama lengkap Anda" autocomplete="name"
                  class="w-full h-12 bg-[#100e11] border border-border-active rounded-lg px-4 text-text-primary text-[14px] outline-none transition-colors focus:border-border-focus">
                <p class="text-text-dim text-[11px] mt-1.5">Nama lengkap dapat Anda tambahkan nanti.</p>
              </div>
              <button type="button" @click="step = 2"
                class="w-full h-12 bg-brand-red2 hover:bg-[#c0202f] text-white font-semibold text-[14px] rounded-lg transition-colors">
                Lanjutkan &nbsp;→
              </button>
            </div>

            {{-- ════ STEP 2 ════ --}}
            <div x-show="step === 2" class="space-y-5">
              <div>
                <label class="block text-text-muted text-[13px] font-medium mb-2">Email</label>
                <input type="email" x-model="email" placeholder="email@contoh.com" autocomplete="email"
                  class="w-full h-12 bg-[#100e11] border border-border-focus rounded-lg px-4 text-text-primary text-[14px] outline-none transition-colors focus:border-border-focus">
              </div>
              <div>
                <label class="block text-text-muted text-[13px] font-medium mb-2">Nomor WhatsApp <span class="text-text-dim font-normal">(OTP)</span></label>
                <input type="text" x-model="phone" placeholder="08xx..." autocomplete="tel"
                  class="w-full h-12 bg-[#100e11] border border-border-active rounded-lg px-4 text-text-primary text-[14px] outline-none transition-colors focus:border-border-focus">
              </div>
              <div>
                <label class="block text-text-muted text-[13px] font-medium mb-2">Kata sandi</label>
                <div class="relative flex items-center">
                  <input :type="showPw ? 'text' : 'password'" name="password" x-model="password"
                    @input="checkStrength($event.target.value)"
                    placeholder="Min. 8 karakter" autocomplete="new-password"
                    class="w-full h-12 bg-[#100e11] border border-border-active rounded-lg pl-4 pr-11 text-text-primary text-[14px] outline-none transition-colors focus:border-border-focus">
                  <button type="button" @click="showPw=!showPw" class="absolute right-3 text-text-dim hover:text-text-muted transition-colors">
                    <svg x-show="!showPw" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showPw"  width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                  </button>
                </div>
                {{-- Password strength meter --}}
                <div class="flex gap-1 mt-2">
                  <template x-for="i in 4">
                    <div class="flex-1 h-[3px] rounded-full transition-colors"
                         :style="`background:${i <= passwordStrength ? (passwordStrength <= 1 ? '#ef4444' : passwordStrength <= 2 ? '#fbbf24' : '#22c55e') : '#2a2a2a'}`">
                    </div>
                  </template>
                </div>
                <p class="text-[10px] text-text-dim mt-1"
                   x-text="passwordStrength <= 1 ? 'Lemah' : passwordStrength <= 2 ? 'Sedang' : passwordStrength <= 3 ? 'Kuat' : 'Sangat kuat'"></p>
              </div>
              <div class="flex gap-3">
                <button type="button" @click="step = 1"
                  class="flex-1 h-12 bg-[#261d23] border border-[#513440] hover:bg-[#3a2530] text-[#f0b8c4] font-semibold text-[14px] rounded-lg transition-colors">
                  ← Kembali
                </button>
                <button type="button" @click="step = 3"
                  class="flex-1 h-12 bg-brand-red2 hover:bg-[#c0202f] text-white font-semibold text-[14px] rounded-lg transition-colors">
                  Lanjutkan →
                </button>
              </div>
            </div>

            {{-- ════ STEP 3 ════ --}}
            <div x-show="step === 3" class="space-y-5">
              <div>
                <label class="block text-text-muted text-[13px] font-medium mb-2">Konfirmasi kata sandi</label>
                <div class="relative flex items-center">
                  <input :type="showPwConf ? 'text' : 'password'" name="password_confirmation"
                    placeholder="Ulangi kata sandi" autocomplete="new-password"
                    class="w-full h-12 bg-[#100e11] border border-border-focus rounded-lg pl-4 pr-11 text-text-primary text-[14px] outline-none transition-colors focus:border-border-focus">
                  <button type="button" @click="showPwConf=!showPwConf" class="absolute right-3 text-text-dim hover:text-text-muted transition-colors">
                    <svg x-show="!showPwConf" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showPwConf"  width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                  </button>
                </div>
              </div>
              <div class="flex gap-3">
                <button type="button" @click="step = 2"
                  class="flex-1 h-12 bg-[#261d23] border border-[#513440] hover:bg-[#3a2530] text-[#f0b8c4] font-semibold text-[14px] rounded-lg transition-colors">
                  ← Kembali
                </button>
                <button type="submit"
                  class="flex-1 h-12 bg-brand-red2 hover:bg-[#c0202f] text-white font-semibold text-[14px] rounded-lg transition-colors">
                  Buat akun
                </button>
              </div>
            </div>
          </form>

          {{-- Divider --}}
          <div class="flex items-center gap-4 my-6">
            <div class="flex-1 h-px bg-border-default"></div>
            <span class="text-text-dim text-[12px]">atau</span>
            <div class="flex-1 h-px bg-border-default"></div>
          </div>

          {{-- Google sign up --}}
          <a href="{{ route('oauth.google') }}"
            class="w-full h-12 bg-[#191619] border border-[#44373f] hover:border-border-focus rounded-lg flex items-center justify-center gap-3 transition-colors">
            <span class="text-text-primary font-bold text-[18px] leading-none">G</span>
            <span class="text-text-primary font-semibold text-[14px]">Daftar dengan Google</span>
          </a>

          {{-- Login link --}}
          <p class="text-center mt-6 text-[13px]">
            <span class="text-text-muted">Sudah punya akun? </span>
            <a href="{{ route('masuk') }}" class="text-[#f68d99] font-semibold hover:text-[#f04452] transition-colors">Masuk</a>
          </p>

        </div>{{-- end right --}}
      </div>{{-- end grid --}}
    </div>{{-- end max-w --}}
  </main>

  {{-- FOOTER --}}
  <footer class="h-12 flex items-center px-6 lg:px-10 border-t border-border-default">
    <div class="max-w-7xl w-full mx-auto flex items-center gap-6">
      <span class="text-text-muted font-semibold text-[10px]">REDSIM</span>
      <span class="text-text-dim text-[10px]">Platform analisis keamanan web</span>
      <span class="text-text-dim text-[10px] ml-auto">Gunakan hanya pada sistem yang Anda miliki atau diizinkan.</span>
    </div>
  </footer>

<script>
grecaptcha.ready(function() {
    grecaptcha.execute('{{ env("RECAPTCHA_SITE_KEY") }}', {action: 'register'}).then(function(token) {
        document.getElementById('g-recaptcha-response').value = token;
    });
});
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    grecaptcha.execute('{{ env("RECAPTCHA_SITE_KEY") }}', {action: 'register'}).then(function(token) {
        document.getElementById('g-recaptcha-response').value = token;
        document.getElementById('registerForm').submit();
    });
});
</script>
</body>
</html>
