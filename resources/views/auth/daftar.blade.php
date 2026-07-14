<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('app.env') === 'local' ? env('RECAPTCHA_SITE_KEY') : env('RECAPTCHA_SITE_KEY') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-[#0a0e1a] flex items-center justify-center p-4 antialiased">
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
            <h2 class="mt-4 text-2xl font-bold text-white">Buat Akun RedSim</h2>
            <p class="mt-2 text-sm text-[#94a3b8]">Daftar gratis untuk memulai analisis keamanan</p>
        </div>

        <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-8 shadow-2xl">
            @if($errors->any())
            <div class="mb-6 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('daftar') }}" class="space-y-5" x-data="{
                step: 1, 
                totalSteps: 3,
                direction: 1,
                nextStep() { 
                    if(this.step < this.totalSteps) { 
                        this.direction = 1;
                        this.step++; 
                    } 
                },
                prevStep() { 
                    if(this.step > 1) { 
                        this.direction = -1;
                        this.step--; 
                    } 
                }
            }">
                @csrf
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                
                {{-- STEP INDICATOR --}}
                <div class="flex items-center justify-between mb-8 relative">
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-[#1e2d4a] rounded-full z-0"></div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-[#5227ff] rounded-full z-0 transition-all duration-500" :style="`width: ${((step - 1) / (totalSteps - 1)) * 100}%`"></div>
                    
                    <template x-for="i in totalSteps" :key="i">
                        <div class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full font-bold text-xs transition-colors duration-300"
                             :class="step >= i ? 'bg-[#5227ff] text-white shadow-[0_0_10px_#5227ff]' : 'bg-[#1e2d4a] text-[#a3a3a3]'">
                             <span x-text="i"></span>
                        </div>
                    </template>
                </div>

                {{-- FORM STEPS WRAPPER --}}
                <div class="relative overflow-hidden min-h-[300px]">
                    {{-- STEP 1: Akun --}}
                    <div x-show="step === 1" 
                         x-transition:enter="transition-all ease-out duration-500"
                         x-transition:enter-start="opacity-0 scale-95 blur-sm translate-y-4"
                         x-transition:enter-end="opacity-100 scale-100 blur-0 translate-y-0"
                         x-transition:leave="transition-all ease-in duration-300 absolute inset-0 z-0"
                         x-transition:leave-start="opacity-100 scale-100 blur-0"
                         x-transition:leave-end="opacity-0 scale-105 blur-sm"
                         class="space-y-5 relative z-10">
                        <h3 class="text-white font-bold text-lg mb-2">Informasi Akun</h3>
                        <div>
                            <label for="name" class="block text-sm font-medium text-[#94a3b8] mb-2">Nama Pengguna</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                                   class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none transition-colors"
                                   placeholder="nama_pengguna">
                        </div>
                        <div>
                            <label for="nama_lengkap" class="block text-sm font-medium text-[#94a3b8] mb-2">Nama Lengkap <span class="text-[#475569]">(opsional)</span></label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                                   class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none transition-colors"
                                   placeholder="Nama Lengkap Anda">
                        </div>
                        <button type="button" @click="nextStep()" class="mt-4 w-full py-3 rounded-xl bg-[#5227ff] text-white font-semibold hover:bg-[#431ce3] transition-colors">
                            Lanjutkan
                        </button>
                    </div>

                    {{-- STEP 2: Kontak --}}
                    <div x-show="step === 2" style="display: none;"
                         x-transition:enter="transition-all ease-out duration-500"
                         x-transition:enter-start="opacity-0 scale-95 blur-sm translate-y-4"
                         x-transition:enter-end="opacity-100 scale-100 blur-0 translate-y-0"
                         x-transition:leave="transition-all ease-in duration-300 absolute inset-0 z-0"
                         x-transition:leave-start="opacity-100 scale-100 blur-0"
                         x-transition:leave-end="opacity-0 scale-105 blur-sm"
                         class="space-y-5 relative z-10">
                        <h3 class="text-white font-bold text-lg mb-2">Kontak</h3>
                        <div>
                            <label for="email" class="block text-sm font-medium text-[#94a3b8] mb-2">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                   class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none transition-colors"
                                   placeholder="nama@email.com">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-[#94a3b8] mb-2">Nomor WhatsApp</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                                   class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none transition-colors"
                                   placeholder="081234567890">
                        </div>
                        
                        <div class="flex gap-3 mt-4">
                            <button type="button" @click="prevStep()" class="w-1/3 py-3 rounded-xl bg-[#1e2d4a] text-white font-semibold hover:bg-[#2a3b5a] transition-colors">
                                Kembali
                            </button>
                            <button type="button" @click="nextStep()" class="w-2/3 py-3 rounded-xl bg-[#5227ff] text-white font-semibold hover:bg-[#431ce3] transition-colors">
                                Lanjutkan
                            </button>
                        </div>
                    </div>

                    {{-- STEP 3: Keamanan --}}
                    <div x-show="step === 3" style="display: none;"
                         x-transition:enter="transition-all ease-out duration-500"
                         x-transition:enter-start="opacity-0 scale-95 blur-sm translate-y-4"
                         x-transition:enter-end="opacity-100 scale-100 blur-0 translate-y-0"
                         x-transition:leave="transition-all ease-in duration-300 absolute inset-0 z-0"
                         x-transition:leave-start="opacity-100 scale-100 blur-0"
                         x-transition:leave-end="opacity-0 scale-105 blur-sm"
                         class="space-y-5 relative z-10">
                        <h3 class="text-white font-bold text-lg mb-2">Keamanan</h3>
                        {{-- Alpine.js block untuk evaluasi kekuatan password --}}
                        <div x-data="passwordStrength()">
                            <label for="password" class="block text-sm font-medium text-[#94a3b8] mb-2">Kata Sandi</label>
                            <input type="password" id="password" name="password" required x-model="password" @input="checkStrength"
                                   class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none transition-colors"
                                   placeholder="Minimal 8 karakter">
                            
                            {{-- Visual Progress Bar --}}
                            <div class="mt-3 w-full bg-[#1e2d4a] rounded-full h-1.5 flex overflow-hidden">
                                <div class="h-full transition-all duration-300" :class="barColor" :style="'width: ' + scorePercent + '%'"></div>
                            </div>
        
                            {{-- Checklist Kriteria --}}
                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                <div class="flex items-center gap-1.5" :class="hasLength ? 'text-green-400' : 'text-[#64748b]'">
                                    <svg x-show="hasLength" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span x-show="!hasLength" class="w-3.5 h-3.5 inline-block border border-current rounded-full opacity-50"></span>
                                    Minimal 8 karakter
                                </div>
                                <div class="flex items-center gap-1.5" :class="hasMixedCase ? 'text-green-400' : 'text-[#64748b]'">
                                    <svg x-show="hasMixedCase" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span x-show="!hasMixedCase" class="w-3.5 h-3.5 inline-block border border-current rounded-full opacity-50"></span>
                                    Huruf besar & kecil
                                </div>
                                <div class="flex items-center gap-1.5" :class="hasNumber ? 'text-green-400' : 'text-[#64748b]'">
                                    <svg x-show="hasNumber" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span x-show="!hasNumber" class="w-3.5 h-3.5 inline-block border border-current rounded-full opacity-50"></span>
                                    Mengandung angka
                                </div>
                                <div class="flex items-center gap-1.5" :class="hasSymbol ? 'text-green-400' : 'text-[#64748b]'">
                                    <svg x-show="hasSymbol" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span x-show="!hasSymbol" class="w-3.5 h-3.5 inline-block border border-current rounded-full opacity-50"></span>
                                    Karakter spesial
                                </div>
                            </div>
                        </div>
        
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-[#94a3b8] mb-2 mt-2">Konfirmasi Kata Sandi</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required
                                   class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none transition-colors"
                                   placeholder="Ulangi kata sandi">
                        </div>
                        <div class="flex gap-3 mt-4">
                            <button type="button" @click="prevStep()" class="w-1/3 py-3 rounded-xl bg-[#1e2d4a] text-white font-semibold hover:bg-[#2a3b5a] transition-colors">
                                Kembali
                            </button>
                            <button type="submit" class="w-2/3 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300 hover:-translate-y-0.5">
                                Daftar Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-[#1e2d4a]"></div></div>
                <div class="relative flex justify-center"><span class="bg-[#0f1629] px-4 text-xs text-[#64748b]">atau</span></div>
            </div>

            <a href="{{ route('oauth.google') }}" class="w-full flex items-center justify-center gap-3 py-3 rounded-xl bg-white hover:bg-gray-100 transition-colors text-gray-700 font-medium text-sm">
                <svg class="w-5 h-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                Daftar dengan Google
            </a>

        <p class="text-center mt-6 text-sm text-[#94a3b8]">
            Sudah punya akun?
            <a href="{{ route('masuk') }}" class="text-[#00d4ff] hover:underline font-medium">Masuk di sini</a>
        </p>
    </div>

    <script>
        // Alpine.js component untuk password strength
        document.addEventListener('alpine:init', () => {
            Alpine.data('passwordStrength', () => ({
                password: '',
                score: 0,
                hasLength: false,
                hasMixedCase: false,
                hasNumber: false,
                hasSymbol: false,
                
                checkStrength() {
                    let pw = this.password;
                    this.hasLength = pw.length >= 8;
                    this.hasMixedCase = /[a-z]/.test(pw) && /[A-Z]/.test(pw);
                    this.hasNumber = /[0-9]/.test(pw);
                    this.hasSymbol = /[^A-Za-z0-9]/.test(pw);
                    
                    let newScore = 0;
                    if (this.hasLength) newScore += 25;
                    if (this.hasMixedCase) newScore += 25;
                    if (this.hasNumber) newScore += 25;
                    if (this.hasSymbol) newScore += 25;
                    
                    this.score = newScore;
                },
                
                get scorePercent() {
                    return this.password.length === 0 ? 0 : this.score;
                },
                
                get barColor() {
                    if (this.score === 0) return 'bg-transparent';
                    if (this.score <= 25) return 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.8)]';
                    if (this.score <= 50) return 'bg-orange-500 shadow-[0_0_8px_rgba(249,115,22,0.8)]';
                    if (this.score <= 75) return 'bg-yellow-400 shadow-[0_0_8px_rgba(250,204,21,0.8)]';
                    return 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.8)]';
                }
            }))
        });

        // reCAPTCHA init
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                grecaptcha.ready(function() {
                    grecaptcha.execute('{{ env("RECAPTCHA_SITE_KEY") }}', {action: 'register'}).then(function(token) {
                        document.getElementById('g-recaptcha-response').value = token;
                        form.submit();
                    });
                });
            });
        });
    </script>
</body>
</html>
