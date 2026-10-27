<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('app.env') === 'local' ? env('RECAPTCHA_SITE_KEY') : env('RECAPTCHA_SITE_KEY') }}"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0010;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
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

        /* Main container wrapping logo + form + emitter */
        #auth-wrapper {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }

        /* Logo area */
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

        /* The #Container style from Uiverse — adapted */
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
            gap: 1rem;
            animation: float 3s ease-in-out infinite;
            width: 100%;
        }

        #login-lable {
            text-align: center;
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 8px;
            text-shadow: 0px 0px 20px rgba(255, 80, 120, 0.9), 0px 0px 40px rgba(80, 80, 255, 0.5);
            margin-bottom: 0.25rem;
        }

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

        .form-content:hover {
            background: rgba(80, 30, 200, 0.25);
        }

        .form-content::placeholder {
            font-weight: 300;
            color: rgba(255, 255, 255, 0.6);
            letter-spacing: 0.1rem;
        }

        .form-extras {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8rem;
        }

        .form-extras label {
            display: flex;
            align-items: center;
            gap: 6px;
            color: rgba(255,255,255,0.7);
            cursor: pointer;
        }

        .form-extras a {
            color: #ff6090;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .form-extras a:hover { color: #ff90b0; text-decoration: underline; }

        .btn-submit {
            cursor: pointer;
            height: 3.2rem;
            padding: 0;
            color: white;
            font-size: 1.1em;
            font-weight: 600;
            letter-spacing: 0.25rem;
            border: 2px solid rgba(255,255,255,0.6);
            border-radius: 8px;
            background: linear-gradient(135deg, #cc0040cc, #2200eecc 50%, #ff0055cc);
            transition: all 0.3s;
            box-shadow: 0 0 20px rgba(200, 0, 80, 0.4);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, #ee0050, #3300ff 50%, #ff0070);
            box-shadow: 0 0 12px 4px rgba(255, 0, 80, 0.5), 0 0 24px rgba(80, 0, 255, 0.4);
        }

        /* Divider */
        .auth-divider {
            position: relative;
            margin: 1.25rem 0 0.75rem;
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

        /* Alert styles */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.82rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-error {
            background: rgba(255,30,30,0.12);
            border: 1px solid rgba(255,60,60,0.4);
            color: #ff9090;
        }
        .alert-warning {
            background: rgba(255,160,0,0.1);
            border: 1px solid rgba(255,160,0,0.3);
            color: #ffc060;
        }
        .alert-success {
            background: rgba(0,200,100,0.1);
            border: 1px solid rgba(0,200,100,0.3);
            color: #60ffaa;
        }

        /* Rays SVG — recolored to red-blue neon */
        #rays svg path {
            /* gradient handled inline */
        }

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

        @keyframes bounce-in {
            0%   { opacity:0; transform:translateY(-12px) scale(0.95); }
            60%  { transform:translateY(4px) scale(1.02); }
            100% { opacity:1; transform:translateY(0) scale(1); }
        }
        .animate-bounce-in { animation: bounce-in 0.5s ease-out; }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 8px rgba(255,30,30,0.3); }
            50%       { box-shadow: 0 0 24px rgba(255,30,30,0.6); }
        }
        .animate-pulse-glow { animation: pulse-glow 2s ease-in-out infinite; }

        /* Bottom emitter area padding */
        .emitter-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
            <h2>Masuk ke RedSim</h2>
            <p>Masukkan kredensial Anda untuk melanjutkan</p>
        </div>

        <!-- Uiverse Container -->
        <div id="Container" style="width:100%;">

            <form class="form" method="POST" action="{{ route('masuk') }}">
                @csrf
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                <div id="login-lable">LOGIN</div>

                {{-- Alerts --}}
                @if($errors->has('throttle'))
                <div class="alert alert-error animate-bounce-in animate-pulse-glow" x-data="{ show: true }" x-show="show">
                    <span style="font-size:1.2rem;flex-shrink:0;">⛔</span>
                    <div style="flex:1;">
                        <p style="font-weight:700;font-size:0.9rem;">Sesi Diblokir!</p>
                        <p style="margin-top:2px;opacity:0.85;">{{ $errors->first('throttle') }}</p>
                    </div>
                    <button type="button" @click="show = false" style="color:rgba(255,100,100,0.6);font-size:1.1rem;background:none;border:none;cursor:pointer;">&times;</button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-warning animate-bounce-in" x-data="{ show: true }" x-show="show">
                    <span style="font-size:1.2rem;flex-shrink:0;">🔐</span>
                    <div style="flex:1;">
                        <p style="font-weight:600;">Login dulu boss!</p>
                        <p style="font-size:0.78rem;margin-top:2px;opacity:0.8;">{{ session('error') }}</p>
                    </div>
                    <button type="button" @click="show = false" style="color:rgba(255,180,0,0.6);font-size:1.1rem;background:none;border:none;cursor:pointer;">&times;</button>
                </div>
                @endif

                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any() && !$errors->has('throttle'))
                <div class="alert alert-error">
                    @foreach($errors->all() as $error)
                        @if($error !== $errors->first('throttle'))
                        <p>{{ $error }}</p>
                        @endif
                    @endforeach
                </div>
                @endif

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-content" type="email" id="email" name="email"
                           value="{{ old('email') }}" required autofocus
                           placeholder="nama@email.com">
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <input class="form-content" type="password" id="password" name="password"
                           required placeholder="••••••••">
                </div>

                <!-- Extras -->
                <div class="form-extras">
                    <label>
                        <input type="checkbox" name="remember"
                               style="width:14px;height:14px;accent-color:#ff3060;">
                        Ingat saya
                    </label>
                    <a href="{{ route('lupa-password') }}">Lupa kata sandi?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-submit">MASUK</button>

                <!-- Divider -->
                <div class="auth-divider"><span>atau</span></div>

                <!-- Google -->
                <a href="{{ route('oauth.google') }}" class="btn-google">
                    <svg width="20" height="20" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Masuk dengan Google
                </a>

            </form>

            <!-- Rays SVG — recolored red to blue neon -->
            <div id="rays">
                <svg fill="none" viewBox="0 0 299 152" height="9em" width="18em" xmlns="http://www.w3.org/2000/svg">
                    <path fill="url(#rays_grad_login)" d="M149.5 152H133.42L9.53674e-07 4.70132e-06H149.5L299 4.70132e-06L165.58 152H149.5Z"></path>
                    <defs>
                        <linearGradient gradientUnits="userSpaceOnUse" y2="12.1981" x2="150.12" y1="152" x1="149.5" id="rays_grad_login">
                            <stop stop-color="#FF003C"></stop>
                            <stop stop-opacity="0" stop-color="#6633FF" offset="1"></stop>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <!-- Emitter SVG — recolored -->
            <div id="emiter">
                <svg fill="none" viewBox="0 0 160 61" height="61" width="160" xmlns="http://www.w3.org/2000/svg">
                    <g filter="url(#filter0_di_login)">
                        <path fill="#1a0a1a" d="M80 27.9997C121.974 27.9997 156 22.4032 156 15.4996L156 40.4998C156 47.4034 121.974 52.9998 80 52.9998C38.0265 52.9998 4.00028 47.4034 4 40.4998V40.4998V15.51C4.0342 22.4089 38.0474 27.9997 80 27.9997Z" clip-rule="evenodd" fill-rule="evenodd"></path>
                    </g>
                    <ellipse fill="url(#emitter_radial_login)" ry="4.80773" rx="28.3956" cy="17.4236" cx="80"></ellipse>
                    <g filter="url(#filter1_i_login)">
                        <path fill="#2a1030" d="M80 28.0002C121.974 28.0002 156 22.4037 156 15.5001C156 8.59648 121.974 3 80 3C38.0264 3 4 8.59648 4 15.5001C4 22.4037 38.0264 28.0002 80 28.0002ZM80.0001 20.308C96.1438 20.308 109.231 18.1555 109.231 15.5002C109.231 12.845 96.1438 10.6925 80.0001 10.6925C63.8564 10.6925 50.7693 12.845 50.7693 15.5002C50.7693 18.1555 63.8564 20.308 80.0001 20.308Z" clip-rule="evenodd" fill-rule="evenodd"></path>
                    </g>
                    <g filter="url(#filter2_di_login)">
                        <path fill="#a63060" d="M106.725 17.4505C108.336 16.8543 109.231 16.1943 109.231 15.4999C109.231 12.8446 96.1438 10.6921 80.0001 10.6921C63.8564 10.6921 50.7693 12.8446 50.7693 15.4999C50.7693 16.1943 51.6645 16.8543 53.2752 17.4504C53.275 17.4414 53.2748 17.4323 53.2748 17.4232C53.2748 14.768 65.2401 12.6155 80.0001 12.6155C94.7601 12.6155 106.725 14.768 106.725 17.4232C106.725 17.4323 106.725 17.4414 106.725 17.4505Z" clip-rule="evenodd" fill-rule="evenodd"></path>
                    </g>
                    <defs>
                        <filter color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse" height="45.5002" width="160" y="15.4996" x="0" id="filter0_di_login">
                            <feFlood result="BackgroundImageFix" flood-opacity="0"></feFlood>
                            <feColorMatrix result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" type="matrix" in="SourceAlpha"></feColorMatrix>
                            <feOffset dy="4"></feOffset>
                            <feGaussianBlur stdDeviation="2"></feGaussianBlur>
                            <feComposite operator="out" in2="hardAlpha"></feComposite>
                            <feColorMatrix values="0 0 0 0 0.620833 0 0 0 0 0.620833 0 0 0 0 0.620833 0 0 0 0.25 0" type="matrix"></feColorMatrix>
                            <feBlend result="effect1_dropShadow_login" in2="BackgroundImageFix" mode="normal"></feBlend>
                            <feBlend result="shape" in2="effect1_dropShadow_login" in="SourceGraphic" mode="normal"></feBlend>
                            <feColorMatrix result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" type="matrix" in="SourceAlpha"></feColorMatrix>
                            <feOffset></feOffset>
                            <feGaussianBlur stdDeviation="8"></feGaussianBlur>
                            <feComposite k3="1" k2="-1" operator="arithmetic" in2="hardAlpha"></feComposite>
                            <feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 1 0" type="matrix"></feColorMatrix>
                            <feBlend result="effect2_innerShadow_login" in2="shape" mode="normal"></feBlend>
                        </filter>
                        <filter color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse" height="25.0002" width="152" y="3" x="4" id="filter1_i_login">
                            <feFlood result="BackgroundImageFix" flood-opacity="0"></feFlood>
                            <feBlend result="shape" in2="BackgroundImageFix" in="SourceGraphic" mode="normal"></feBlend>
                            <feColorMatrix result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" type="matrix" in="SourceAlpha"></feColorMatrix>
                            <feMorphology result="effect1_innerShadow_login" in="SourceAlpha" operator="erode" radius="3"></feMorphology>
                            <feOffset></feOffset>
                            <feGaussianBlur stdDeviation="6.5"></feGaussianBlur>
                            <feComposite k3="1" k2="-1" operator="arithmetic" in2="hardAlpha"></feComposite>
                            <feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 1 0" type="matrix"></feColorMatrix>
                            <feBlend result="effect1_innerShadow_login" in2="shape" mode="normal"></feBlend>
                        </filter>
                        <filter color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse" height="26.7583" width="78.4615" y="0.692139" x="40.7693" id="filter2_di_login">
                            <feFlood result="BackgroundImageFix" flood-opacity="0"></feFlood>
                            <feColorMatrix result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" type="matrix" in="SourceAlpha"></feColorMatrix>
                            <feMorphology result="effect1_dropShadow_login2" in="SourceAlpha" operator="dilate" radius="2"></feMorphology>
                            <feOffset></feOffset>
                            <feGaussianBlur stdDeviation="4"></feGaussianBlur>
                            <feComposite operator="out" in2="hardAlpha"></feComposite>
                            <feColorMatrix values="0 0 0 0 1 0 0 0 0 0 0 0 0 0 0.2 0 0 0 1 0" type="matrix"></feColorMatrix>
                            <feBlend result="effect1_dropShadow_login2" in2="BackgroundImageFix" mode="color-dodge"></feBlend>
                            <feBlend result="shape" in2="effect1_dropShadow_login2" in="SourceGraphic" mode="normal"></feBlend>
                            <feColorMatrix result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" type="matrix" in="SourceAlpha"></feColorMatrix>
                            <feMorphology result="effect2_innerShadow_login2" in="SourceAlpha" operator="erode" radius="1"></feMorphology>
                            <feOffset></feOffset>
                            <feGaussianBlur stdDeviation="2"></feGaussianBlur>
                            <feComposite k3="1" k2="-1" operator="arithmetic" in2="hardAlpha"></feComposite>
                            <feColorMatrix values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.52 0" type="matrix"></feColorMatrix>
                            <feBlend result="effect2_innerShadow_login2" in2="shape" mode="normal"></feBlend>
                        </filter>
                        <radialGradient gradientTransform="translate(80 17.4236) rotate(90) scale(6.25004 36.9143)" gradientUnits="userSpaceOnUse" r="1" cy="0" cx="0" id="emitter_radial_login">
                            <stop stop-color="#FF003C"></stop>
                            <stop stop-color="#3300FF" offset="0.901042"></stop>
                        </radialGradient>
                    </defs>
                </svg>
            </div>
        </div>

        <!-- Footer link -->
        <div class="auth-footer" style="margin-top: 6rem;">
            Belum punya akun?
            <a href="{{ route('daftar') }}">Daftar sekarang</a>
        </div>

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
