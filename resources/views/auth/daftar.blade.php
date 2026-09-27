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
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0010;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
            padding: 2rem 1rem;
        }

        /* Animated background blobs */
        .bg-blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }
        .bg-blob-1 {
            width: 400px; height: 400px;
            background: rgba(255, 0, 80, 0.12);
            top: -100px; right: -100px;
        }
        .bg-blob-2 {
            width: 400px; height: 400px;
            background: rgba(0, 80, 255, 0.12);
            bottom: -100px; left: -100px;
        }
        .bg-blob-3 {
            width: 200px; height: 200px;
            background: rgba(180, 0, 255, 0.08);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
        }

        #auth-wrapper {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 440px;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .auth-logo img {
            width: 52px; height: 52px;
            border-radius: 14px;
            box-shadow: 0 0 24px rgba(255, 0, 80, 0.4), 0 0 48px rgba(0, 80, 255, 0.2);
        }
        .auth-logo h2 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 0.75rem;
            letter-spacing: 2px;
        }
        .auth-logo p {
            color: #94a3b8;
            font-size: 0.85rem;
            margin-top: 0.3rem;
        }

        /* Uiverse Container */
        #Container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            width: 100%;
        }

        #rays {
            z-index: 2;
            position: relative;
            bottom: -1.5em;
            animation: rays 2s ease-in-out infinite;
        }

        .form {
            position: relative;
            top: 5em;
            padding: 2rem;
            z-index: 3;
            display: flex;
            flex-direction: column;
            border-radius: 0.75rem;
            border: 2px solid rgba(255, 255, 255, 0.5);
            background: rgba(120, 0, 60, 0.32);
            box-shadow:
                0px 0px 64px 0px rgba(255, 30, 80, 0.35) inset,
                0px 0px 32px rgba(80, 30, 255, 0.3) inset,
                0px 0px 20px rgba(255, 30, 80, 0.2);
            backdrop-filter: blur(12px);
            gap: 0.75rem;
            animation: float 3s ease-in-out infinite;
            width: 100%;
        }

        #register-label {
            text-align: center;
            color: white;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 6px;
            text-shadow: 0px 0px 20px rgba(255, 80, 120, 0.9), 0px 0px 40px rgba(80, 80, 255, 0.5);
            margin-bottom: 0.25rem;
        }

        /* ===== STEP INDICATOR ===== */
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            margin-bottom: 1rem;
        }
        .step-track {
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 100%; height: 3px;
            background: rgba(255,255,255,0.1);
            border-radius: 99px;
            z-index: 0;
        }
        .step-progress {
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            height: 3px;
            border-radius: 99px;
            z-index: 0;
            background: linear-gradient(90deg, #ff003c, #5533ff);
            box-shadow: 0 0 10px rgba(255, 0, 60, 0.6);
            transition: width 0.5s ease;
        }
        .step-dot {
            position: relative;
            z-index: 10;
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            transition: all 0.3s;
            color: rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.08);
            border: 2px solid rgba(255,255,255,0.15);
        }
        .step-dot.active {
            background: linear-gradient(135deg, #ff003c, #3300ff);
            color: white;
            border-color: rgba(255,255,255,0.5);
            box-shadow: 0 0 14px rgba(255, 0, 60, 0.7), 0 0 28px rgba(80, 0, 255, 0.4);
        }

        /* Step section headers */
        .step-title {
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
            text-shadow: 0 0 10px rgba(255,80,100,0.4);
        }

        /* Form inputs */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .form-content {
            height: 3em;
            padding: 0 12px;
            color: white;
            letter-spacing: 1px;
            font-weight: 500;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.6);
            background: rgba(200, 30, 80, 0.2);
            box-shadow: 0px 0px 1px 3px rgba(255, 80, 120, 0.25) inset, 0px 4px 4px 0px rgba(20, 10, 60, 0.4);
            text-shadow: 0px 1px 4px rgb(243, 243, 243);
            font-size: 0.95rem;
            transition: all 0.3s;
            width: 100%;
            outline: none;
        }

        .form-content:focus-visible,
        .form-content:focus {
            outline: none;
            background: rgba(80, 30, 200, 0.35);
            box-shadow: 0px 0px 0px 3px rgba(100, 120, 255, 0.5), 0px 0px 16px rgba(80, 80, 255, 0.4);
            border-color: rgba(120, 140, 255, 0.9);
        }
        .form-content:hover { background: rgba(80, 30, 200, 0.25); }
        .form-content::placeholder {
            font-weight: 300;
            color: rgba(255, 255, 255, 0.6);
            letter-spacing: 0.1rem;
        }

        /* Buttons */
        .btn-primary {
            cursor: pointer;
            height: 3rem;
            padding: 0;
            color: white;
            font-size: 1em;
            font-weight: 600;
            letter-spacing: 0.2rem;
            border: 2px solid rgba(255,255,255,0.6);
            border-radius: 8px;
            background: linear-gradient(135deg, #cc0040cc, #2200eecc 50%, #ff0055cc);
            transition: all 0.3s;
            box-shadow: 0 0 20px rgba(200, 0, 80, 0.4);
            width: 100%;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, #ee0050, #3300ff 50%, #ff0070);
            box-shadow: 0 0 12px 4px rgba(255, 0, 80, 0.5), 0 0 24px rgba(80, 0, 255, 0.4);
        }

        .btn-secondary {
            cursor: pointer;
            height: 3rem;
            padding: 0;
            color: rgba(255,255,255,0.8);
            font-size: 0.9em;
            font-weight: 600;
            letter-spacing: 0.1rem;
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            background: rgba(255,255,255,0.06);
            transition: all 0.3s;
            flex: 1;
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.12);
            border-color: rgba(255,255,255,0.3);
        }

        .btn-submit {
            cursor: pointer;
            height: 3rem;
            padding: 0;
            color: white;
            font-size: 1em;
            font-weight: 600;
            letter-spacing: 0.2rem;
            border: 2px solid rgba(255,255,255,0.6);
            border-radius: 8px;
            background: linear-gradient(135deg, #cc0040cc, #2200eecc 50%, #ff0055cc);
            transition: all 0.3s;
            box-shadow: 0 0 20px rgba(200, 0, 80, 0.4);
            flex: 2;
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, #ee0050, #3300ff 50%, #ff0070);
            box-shadow: 0 0 12px 4px rgba(255, 0, 80, 0.5), 0 0 24px rgba(80, 0, 255, 0.4);
        }

        .btn-row {
            display: flex;
            gap: 0.6rem;
            margin-top: 0.5rem;
        }

        /* Password strength */
        .strength-bar-bg {
            width: 100%;
            height: 5px;
            background: rgba(255,255,255,0.1);
            border-radius: 99px;
            overflow: hidden;
            margin-top: 8px;
        }
        .strength-bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 0.3s, background 0.3s;
        }
        .strength-criteria {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-top: 8px;
            font-size: 0.75rem;
        }
        .criteria-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: rgba(255,255,255,0.35);
            transition: color 0.3s;
        }
        .criteria-item.met {
            color: #60ffaa;
        }
        .criteria-check {
            width: 14px; height: 14px;
            border-radius: 50%;
            border: 1px solid currentColor;
            opacity: 0.5;
            flex-shrink: 0;
        }
        .criteria-item.met .criteria-check {
            display: none;
        }
        .criteria-tick {
            display: none;
            width: 14px; height: 14px;
            flex-shrink: 0;
        }
        .criteria-item.met .criteria-tick {
            display: block;
        }

        /* Password generator */
        .pw-generator-box {
            background: rgba(40, 10, 80, 0.5);
            border: 1px solid rgba(100, 80, 255, 0.3);
            border-radius: 10px;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
        }
        .pw-generator-box p {
            color: rgba(255,255,255,0.6);
            font-size: 0.78rem;
            margin-bottom: 6px;
        }
        .pw-gen-row {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .pw-gen-input {
            flex: 1;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 6px;
            padding: 6px 10px;
            color: white;
            font-family: monospace;
            font-size: 0.82rem;
            outline: none;
        }
        .pw-gen-copy {
            padding: 6px 12px;
            background: linear-gradient(135deg, #cc0040, #3300ff);
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }
        .pw-use-btn {
            width: 100%;
            margin-top: 8px;
            padding: 6px;
            border-radius: 6px;
            border: none;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .pw-use-btn.ready {
            background: #22c55e;
            color: white;
        }
        .pw-use-btn:disabled {
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.3);
            cursor: not-allowed;
        }

        .btn-gen-link {
            background: none;
            border: none;
            cursor: pointer;
            color: #ff6090;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 0;
            transition: color 0.2s;
        }
        .btn-gen-link:hover { color: #ff90b0; }

        /* Divider */
        .auth-divider {
            position: relative;
            margin: 1rem 0 0.75rem;
            text-align: center;
        }
        .auth-divider::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            width: 100%; height: 1px;
            background: rgba(255,255,255,0.15);
        }
        .auth-divider span {
            position: relative;
            background: rgba(120, 0, 60, 0.5);
            padding: 0 12px;
            color: rgba(255,255,255,0.4);
            font-size: 0.75rem;
            backdrop-filter: blur(4px);
        }

        /* Google button */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 0.7rem;
            border-radius: 8px;
            background: rgba(255,255,255,0.95);
            color: #333;
            font-weight: 500;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        .btn-google:hover {
            background: #fff;
            box-shadow: 0 0 20px rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.5);
        }
        .auth-footer a {
            color: #6699ff;
            font-weight: 600;
            text-decoration: none;
        }
        .auth-footer a:hover { text-decoration: underline; color: #88aaff; }

        /* Alert */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.82rem;
            margin-bottom: 0.5rem;
        }
        .alert-error {
            background: rgba(255,30,30,0.12);
            border: 1px solid rgba(255,60,60,0.4);
            color: #ff9090;
        }

        /* Checkbox */
        input[type="checkbox"] {
            accent-color: #ff3060;
        }

        /* Emitter */
        #emiter { display: flex; justify-content: center; }

        @keyframes float {
            0%   { top: 5em; }
            50%  { top: calc(5em - 10px); }
            100% { top: 5em; }
        }
        @keyframes rays {
            0%   { opacity: 0.5; }
            50%  { opacity: 1; }
            100% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <!-- Background blobs -->
    <div class="bg-blob bg-blob-1"></div>
    <div class="bg-blob bg-blob-2"></div>
    <div class="bg-blob bg-blob-3"></div>

    <div id="auth-wrapper">

        <!-- Logo -->
        <div class="auth-logo">
            <a href="{{ route('beranda') }}">
                <img src="{{ asset('logo-redsim1:1.svg') }}" alt="RedSim Logo">
            </a>
            <h2>Buat Akun RedSim</h2>
            <p>Daftar gratis untuk memulai analisis keamanan</p>
        </div>

        <div id="Container" style="width:100%;">

            <form class="form" method="POST" action="{{ route('daftar') }}"
                  x-data="{
                      step: 1,
                      totalSteps: 3,
                      nextStep() { if(this.step < this.totalSteps) this.step++; },
                      prevStep() { if(this.step > 1) this.step--; },
                      progressWidth() { return ((this.step - 1) / (this.totalSteps - 1)) * 100 + '%'; }
                  }">
                @csrf
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                <div id="register-label">DAFTAR</div>

                @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                {{-- STEP INDICATOR --}}
                <div class="step-indicator">
                    <div class="step-track"></div>
                    <div class="step-progress" :style="`width: ${progressWidth()}`"></div>

                    <template x-for="i in totalSteps" :key="i">
                        <div class="step-dot" :class="{ 'active': step >= i }">
                            <span x-text="i"></span>
                        </div>
                    </template>
                </div>

                {{-- STEPS CONTAINER --}}
                <div style="position:relative; overflow:hidden; min-height:280px;">

                    {{-- STEP 1: Informasi Akun --}}
                    <div x-show="step === 1"
                         x-transition:enter="transition-all ease-out duration-500"
                         x-transition:enter-start="opacity-0 scale-95 blur-sm translate-y-4"
                         x-transition:enter-end="opacity-100 scale-100 blur-0 translate-y-0"
                         x-transition:leave="transition-all ease-in duration-300 absolute inset-0"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-105 blur-sm"
                         style="position:relative;">
                        <p class="step-title">📋 Informasi Akun</p>
                        <div style="display:flex;flex-direction:column;gap:0.75rem;">
                            <div class="form-group">
                                <label class="form-label" for="name">Nama Pengguna</label>
                                <input class="form-content" type="text" id="name" name="name"
                                       value="{{ old('name') }}" required autofocus
                                       placeholder="nama_pengguna">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="nama_lengkap">
                                    Nama Lengkap <span style="opacity:0.5;">(opsional)</span>
                                </label>
                                <input class="form-content" type="text" id="nama_lengkap" name="nama_lengkap"
                                       value="{{ old('nama_lengkap') }}"
                                       placeholder="Nama Lengkap Anda">
                            </div>
                            <button type="button" @click="nextStep()" class="btn-primary" style="margin-top:0.5rem;">
                                LANJUTKAN →
                            </button>
                        </div>
                    </div>

                    {{-- STEP 2: Kontak --}}
                    <div x-show="step === 2" style="display:none; position:relative;"
                         x-transition:enter="transition-all ease-out duration-500"
                         x-transition:enter-start="opacity-0 scale-95 blur-sm translate-y-4"
                         x-transition:enter-end="opacity-100 scale-100 blur-0 translate-y-0"
                         x-transition:leave="transition-all ease-in duration-300 absolute inset-0"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-105 blur-sm">
                        <p class="step-title">📡 Kontak</p>
                        <div style="display:flex;flex-direction:column;gap:0.75rem;">
                            <div class="form-group">
                                <label class="form-label" for="email">Email</label>
                                <input class="form-content" type="email" id="email" name="email"
                                       value="{{ old('email') }}" required
                                       placeholder="nama@email.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="phone">Nomor WhatsApp</label>
                                <input class="form-content" type="text" id="phone" name="phone"
                                       value="{{ old('phone') }}" required
                                       placeholder="081234567890">
                            </div>
                            <div class="btn-row">
                                <button type="button" @click="prevStep()" class="btn-secondary">← Kembali</button>
                                <button type="button" @click="nextStep()" class="btn-submit">Lanjutkan →</button>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 3: Keamanan --}}
                    <div x-show="step === 3" style="display:none; position:relative;"
                         x-transition:enter="transition-all ease-out duration-500"
                         x-transition:enter-start="opacity-0 scale-95 blur-sm translate-y-4"
                         x-transition:enter-end="opacity-100 scale-100 blur-0 translate-y-0"
                         x-transition:leave="transition-all ease-in duration-300 absolute inset-0"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-105 blur-sm">
                        <p class="step-title">🔐 Keamanan</p>
                        <div x-data="passwordStrength()" style="display:flex;flex-direction:column;gap:0.6rem;">

                            <!-- Generate Password Header -->
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <label class="form-label" for="password">Kata Sandi</label>
                                <button type="button" @click="generatePassword()" class="btn-gen-link">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                    Generate Password
                                </button>
                            </div>

                            <!-- Generator Box -->
                            <div x-show="showGenerator" class="pw-generator-box" style="display:none;">
                                <p>Password Acak:</p>
                                <div class="pw-gen-row">
                                    <input type="text" readonly x-model="generatedPassword" class="pw-gen-input">
                                    <button type="button" @click="copyPassword()" class="pw-gen-copy">Copy</button>
                                </div>
                                <label style="display:flex;align-items:center;gap:6px;font-size:0.78rem;color:rgba(255,255,255,0.6);margin-top:6px;cursor:pointer;">
                                    <input type="checkbox" x-model="copied"> Saya sudah copy passwordnya
                                </label>
                                <button type="button" @click="usePassword()" :disabled="!copied"
                                        class="pw-use-btn" :class="copied ? 'ready' : ''" :disabled="!copied">
                                    Gunakan password ini
                                </button>
                            </div>

                            <!-- Password Input -->
                            <input class="form-content" type="password" id="password" name="password"
                                   required x-model="password" @input="checkStrength"
                                   placeholder="Minimal 8 karakter">

                            <!-- Strength Bar -->
                            <div class="strength-bar-bg">
                                <div class="strength-bar-fill" :class="barColor" :style="'width:' + scorePercent + '%'"></div>
                            </div>

                            <!-- Criteria Checklist -->
                            <div class="strength-criteria">
                                <div class="criteria-item" :class="{ 'met': hasLength }">
                                    <span class="criteria-check"></span>
                                    <svg class="criteria-tick" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    Minimal 8 karakter
                                </div>
                                <div class="criteria-item" :class="{ 'met': hasMixedCase }">
                                    <span class="criteria-check"></span>
                                    <svg class="criteria-tick" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    Huruf besar & kecil
                                </div>
                                <div class="criteria-item" :class="{ 'met': hasNumber }">
                                    <span class="criteria-check"></span>
                                    <svg class="criteria-tick" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    Mengandung angka
                                </div>
                                <div class="criteria-item" :class="{ 'met': hasSymbol }">
                                    <span class="criteria-check"></span>
                                    <svg class="criteria-tick" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    Karakter spesial
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-group" style="margin-top:0.25rem;">
                                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                                <input class="form-content" type="password" id="password_confirmation"
                                       name="password_confirmation" required
                                       placeholder="Ulangi kata sandi">
                            </div>

                            <!-- Navigation buttons -->
                            <div class="btn-row">
                                <button type="button" @click="prevStep()" class="btn-secondary">← Kembali</button>
                                <button type="submit" class="btn-submit">DAFTAR ✓</button>
                            </div>
                        </div>
                    </div>

                </div>{{-- end steps container --}}

                <!-- Divider & Google -->
                <div class="auth-divider"><span>atau</span></div>

                <a href="{{ route('oauth.google') }}" class="btn-google">
                    <svg width="20" height="20" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Daftar dengan Google
                </a>

            </form>

            <!-- Rays SVG -->
            <div id="rays">
                <svg fill="none" viewBox="0 0 299 152" height="9em" width="18em" xmlns="http://www.w3.org/2000/svg">
                    <path fill="url(#rays_grad_reg)" d="M149.5 152H133.42L9.53674e-07 4.70132e-06H149.5L299 4.70132e-06L165.58 152H149.5Z"></path>
                    <defs>
                        <linearGradient gradientUnits="userSpaceOnUse" y2="12.1981" x2="150.12" y1="152" x1="149.5" id="rays_grad_reg">
                            <stop stop-color="#FF003C"></stop>
                            <stop stop-opacity="0" stop-color="#6633FF" offset="1"></stop>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <!-- Emitter SVG -->
            <div id="emiter">
                <svg fill="none" viewBox="0 0 160 61" height="61" width="160" xmlns="http://www.w3.org/2000/svg">
                    <g filter="url(#f0_reg)">
                        <path fill="#1a0a1a" d="M80 27.9997C121.974 27.9997 156 22.4032 156 15.4996L156 40.4998C156 47.4034 121.974 52.9998 80 52.9998C38.0265 52.9998 4.00028 47.4034 4 40.4998V40.4998V15.51C4.0342 22.4089 38.0474 27.9997 80 27.9997Z" clip-rule="evenodd" fill-rule="evenodd"></path>
                    </g>
                    <ellipse fill="url(#er_reg)" ry="4.80773" rx="28.3956" cy="17.4236" cx="80"></ellipse>
                    <g filter="url(#f1_reg)">
                        <path fill="#2a1030" d="M80 28.0002C121.974 28.0002 156 22.4037 156 15.5001C156 8.59648 121.974 3 80 3C38.0264 3 4 8.59648 4 15.5001C4 22.4037 38.0264 28.0002 80 28.0002ZM80.0001 20.308C96.1438 20.308 109.231 18.1555 109.231 15.5002C109.231 12.845 96.1438 10.6925 80.0001 10.6925C63.8564 10.6925 50.7693 12.845 50.7693 15.5002C50.7693 18.1555 63.8564 20.308 80.0001 20.308Z" clip-rule="evenodd" fill-rule="evenodd"></path>
                    </g>
                    <g filter="url(#f2_reg)">
                        <path fill="#a63060" d="M106.725 17.4505C108.336 16.8543 109.231 16.1943 109.231 15.4999C109.231 12.8446 96.1438 10.6921 80.0001 10.6921C63.8564 10.6921 50.7693 12.8446 50.7693 15.4999C50.7693 16.1943 51.6645 16.8543 53.2752 17.4504C53.275 17.4414 53.2748 17.4323 53.2748 17.4232C53.2748 14.768 65.2401 12.6155 80.0001 12.6155C94.7601 12.6155 106.725 14.768 106.725 17.4232C106.725 17.4323 106.725 17.4414 106.725 17.4505Z" clip-rule="evenodd" fill-rule="evenodd"></path>
                    </g>
                    <defs>
                        <filter color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse" height="45.5002" width="160" y="15.4996" x="0" id="f0_reg">
                            <feFlood result="BackgroundImageFix" flood-opacity="0"></feFlood>
                            <feColorMatrix result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" type="matrix" in="SourceAlpha"></feColorMatrix>
                            <feOffset dy="4"></feOffset><feGaussianBlur stdDeviation="2"></feGaussianBlur>
                            <feComposite operator="out" in2="hardAlpha"></feComposite>
                            <feColorMatrix values="0 0 0 0 0.62 0 0 0 0 0.62 0 0 0 0 0.62 0 0 0 0.25 0" type="matrix"></feColorMatrix>
                            <feBlend result="e1" in2="BackgroundImageFix" mode="normal"></feBlend>
                            <feBlend result="shape" in2="e1" in="SourceGraphic" mode="normal"></feBlend>
                            <feColorMatrix result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" type="matrix" in="SourceAlpha"></feColorMatrix>
                            <feOffset></feOffset><feGaussianBlur stdDeviation="8"></feGaussianBlur>
                            <feComposite k3="1" k2="-1" operator="arithmetic" in2="hardAlpha"></feComposite>
                            <feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 1 0" type="matrix"></feColorMatrix>
                            <feBlend result="e2" in2="shape" mode="normal"></feBlend>
                        </filter>
                        <filter color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse" height="25.0002" width="152" y="3" x="4" id="f1_reg">
                            <feFlood result="BackgroundImageFix" flood-opacity="0"></feFlood>
                            <feBlend result="shape" in2="BackgroundImageFix" in="SourceGraphic" mode="normal"></feBlend>
                            <feColorMatrix result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" type="matrix" in="SourceAlpha"></feColorMatrix>
                            <feMorphology result="e1_reg" in="SourceAlpha" operator="erode" radius="3"></feMorphology>
                            <feOffset></feOffset><feGaussianBlur stdDeviation="6.5"></feGaussianBlur>
                            <feComposite k3="1" k2="-1" operator="arithmetic" in2="hardAlpha"></feComposite>
                            <feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 1 0" type="matrix"></feColorMatrix>
                            <feBlend result="e1_reg_out" in2="shape" mode="normal"></feBlend>
                        </filter>
                        <filter color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse" height="26.7583" width="78.4615" y="0.692139" x="40.7693" id="f2_reg">
                            <feFlood result="BackgroundImageFix" flood-opacity="0"></feFlood>
                            <feColorMatrix result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" type="matrix" in="SourceAlpha"></feColorMatrix>
                            <feMorphology result="e1_r2" in="SourceAlpha" operator="dilate" radius="2"></feMorphology>
                            <feOffset></feOffset><feGaussianBlur stdDeviation="4"></feGaussianBlur>
                            <feComposite operator="out" in2="hardAlpha"></feComposite>
                            <feColorMatrix values="0 0 0 0 1 0 0 0 0 0 0 0 0 0 0.2 0 0 0 1 0" type="matrix"></feColorMatrix>
                            <feBlend result="e1_r2_out" in2="BackgroundImageFix" mode="color-dodge"></feBlend>
                            <feBlend result="shape" in2="e1_r2_out" in="SourceGraphic" mode="normal"></feBlend>
                            <feColorMatrix result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" type="matrix" in="SourceAlpha"></feColorMatrix>
                            <feMorphology result="e2_r2" in="SourceAlpha" operator="erode" radius="1"></feMorphology>
                            <feOffset></feOffset><feGaussianBlur stdDeviation="2"></feGaussianBlur>
                            <feComposite k3="1" k2="-1" operator="arithmetic" in2="hardAlpha"></feComposite>
                            <feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.52 0" type="matrix"></feColorMatrix>
                            <feBlend result="e2_r2_out" in2="shape" mode="normal"></feBlend>
                        </filter>
                        <radialGradient gradientTransform="translate(80 17.4236) rotate(90) scale(6.25004 36.9143)" gradientUnits="userSpaceOnUse" r="1" cy="0" cx="0" id="er_reg">
                            <stop stop-color="#FF003C"></stop>
                            <stop stop-color="#3300FF" offset="0.901042"></stop>
                        </radialGradient>
                    </defs>
                </svg>
            </div>
        </div>

        <div class="auth-footer" style="margin-top: 6rem;">
            Sudah punya akun?
            <a href="{{ route('masuk') }}">Masuk di sini</a>
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
                    if (this.score <= 25) return 'bg-red-600';
                    if (this.score <= 50) return 'bg-orange-500';
                    if (this.score <= 75) return 'bg-yellow-400';
                    return 'bg-green-500';
                }
            }))
        });

        // reCAPTCHA
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

    <style>
        /* Strength bar color override since Tailwind purges dynamic classes */
        .bg-red-600 { background-color: #dc2626; box-shadow: 0 0 8px rgba(220,38,38,0.7); }
        .bg-orange-500 { background-color: #f97316; box-shadow: 0 0 8px rgba(249,115,22,0.7); }
        .bg-yellow-400 { background-color: #facc15; box-shadow: 0 0 8px rgba(250,204,21,0.7); }
        .bg-green-500 { background-color: #22c55e; box-shadow: 0 0 8px rgba(34,197,94,0.7); }
    </style>
</body>
</html>
