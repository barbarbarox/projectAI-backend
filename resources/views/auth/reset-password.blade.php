<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi — RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                <img src="{{ asset('logo-redsim1:1.svg') }}" alt="RedSim Logo" class="w-12 h-12 rounded-xl shadow-lg shadow-[#00d4ff]/20 object-contain">
            </a>
            <h2 class="mt-4 text-2xl font-bold text-white">Atur Ulang Kata Sandi</h2>
            <p class="mt-2 text-sm text-[#94a3b8]">Silakan buat kata sandi baru untuk akun Anda.</p>
        </div>

        <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-8 shadow-2xl">
            @if($errors->any())
            <div class="mb-6 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('reset-password.proses', $token) }}" class="space-y-5">
                @csrf
                
                {{-- Alpine.js block untuk evaluasi kekuatan password --}}
                <div x-data="passwordStrength()">
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-medium text-[#94a3b8]">Kata Sandi Baru</label>
                        <button type="button" @click="generatePassword()" class="text-xs text-[#00d4ff] hover:text-[#00b0d4] flex items-center gap-1 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                            Generate Password
                        </button>
                    </div>
                    
                    {{-- Modal/Section Generator --}}
                    <div x-show="showGenerator" class="mb-4 p-4 bg-[#1e2d4a]/50 border border-[#00d4ff]/30 rounded-xl space-y-3" style="display: none;">
                        <p class="text-sm text-[#94a3b8]">Password Acak:</p>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly x-model="generatedPassword" class="w-full bg-[#0a0e1a] border border-[#1e2d4a] rounded-lg px-3 py-2 text-white font-mono text-sm focus:outline-none">
                            <button type="button" @click="copyPassword()" class="px-3 py-2 bg-[#5227ff] rounded-lg text-white text-sm hover:bg-[#431ce3] transition-colors whitespace-nowrap">Copy</button>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-[#94a3b8] mt-2 cursor-pointer">
                            <input type="checkbox" x-model="copied" class="rounded bg-[#0a0e1a] border-[#1e2d4a] text-[#5227ff] focus:ring-[#5227ff]">
                            Saya sudah copy passwordnya
                        </label>
                        <button type="button" @click="usePassword()" :disabled="!copied"
                                class="w-full mt-2 py-2 rounded-lg text-white text-sm transition-colors font-medium disabled:opacity-50"
                                :class="copied ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-600 cursor-not-allowed'">
                            Gunakan password ini
                        </button>
                    </div>
                    <input type="password" id="password" name="password" required x-model="password" @input="checkStrength" autofocus
                           class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none transition-colors"
                           placeholder="Minimal 8 karakter, kombinasi kompleks">
                    
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
                    <label for="password_confirmation" class="block text-sm font-medium text-[#94a3b8] mb-2 mt-2">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none transition-colors"
                           placeholder="Ulangi kata sandi baru">
                </div>
                
                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300 hover:-translate-y-0.5 mt-2">
                    Simpan Kata Sandi
                </button>
            </form>
        </div>
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
                showGenerator: false,
                generatedPassword: '',
                copied: false,
                
                generatePassword() {
                    const length = 12;
                    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~`|}{[]:;?><,./-=";
                    let retVal = "";
                    do {
                        retVal = "";
                        for (let i = 0, n = charset.length; i < length; ++i) {
                            retVal += charset.charAt(Math.floor(Math.random() * n));
                        }
                    } while (!(
                        retVal.length >= 8 &&
                        /[a-z]/.test(retVal) &&
                        /[A-Z]/.test(retVal) &&
                        /[0-9]/.test(retVal) &&
                        /[^A-Za-z0-9]/.test(retVal)
                    ));
                    this.generatedPassword = retVal;
                    this.showGenerator = true;
                    this.copied = false;
                },
                
                copyPassword() {
                    navigator.clipboard.writeText(this.generatedPassword);
                },
                
                usePassword() {
                    this.password = this.generatedPassword;
                    document.getElementById('password_confirmation').value = this.generatedPassword;
                    this.checkStrength();
                    this.showGenerator = false;
                },
                
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
    </script>
</body>
</html>
