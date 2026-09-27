<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RedSim — Platform Penilaian Keamanan Sistem Berbasis AI. Analisis kerentanan kode, URL, dan file dengan teknologi AI canggih.">
    <title>RedSim — Platform Penilaian Keamanan Sistem Berbasis AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&family=Montserrat:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing-animations.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ===== BASE OVERRIDES ===== */
        html, body { background: transparent !important; }
        #ghost-fibers-bg { z-index: 0; }
        section { background: transparent !important; }
        #splash-screen { background: #050816 !important; }
        .cs-card {
            background: rgba(15, 22, 41, 0.60) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .kb-card, .kb-card-inner {
            background: rgba(15, 22, 41, 0.55) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* ===== BODY TOP PADDING FOR FIXED NAV ===== */
        body { padding-top: 76px; }

        /* ===== CYBERPUNK PILL NAVBAR ===== */
        .cyber-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            padding: 12px 24px;
        }
        .cyber-nav-inner {
            max-width: 920px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(6, 8, 18, 0.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 999px;
            padding: 7px 10px 7px 16px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.55), inset 0 0 0 1px rgba(0,212,255,0.04);
        }
        .cyber-nav-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            flex-shrink: 0;
        }
        .cyber-nav-logo img { width: 30px; height: 30px; object-fit: contain; }
        .cyber-nav-logo span {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 1.05rem;
            background: linear-gradient(90deg, #00d4ff, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .cyber-nav-links {
            display: flex;
            align-items: center;
            gap: 2px;
        }
        .cyber-nav-link {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 6px 13px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 500;
            color: rgba(190,205,225,0.65);
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .cyber-nav-link:hover { color: #fff; background: rgba(255,255,255,0.06); }
        .cyber-nav-link.leaderboard { color: #facc15; }
        .cyber-nav-link.leaderboard:hover { background: rgba(250,204,21,0.08); }
        .cyber-nav-actions { display: flex; align-items: center; gap: 6px; }
        .cyber-btn-ghost {
            padding: 7px 15px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(190,205,225,0.75);
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.09);
            transition: all 0.2s ease;
        }
        .cyber-btn-ghost:hover { color:#fff; border-color:rgba(255,255,255,0.2); background:rgba(255,255,255,0.05); }
        .cyber-btn-primary {
            padding: 7px 17px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            background: linear-gradient(135deg, #00d4ff 0%, #7c3aed 100%);
            box-shadow: 0 0 18px rgba(0,212,255,0.18);
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .cyber-btn-primary:hover { box-shadow: 0 0 28px rgba(0,212,255,0.38); transform: translateY(-1px); }

        /* ===== NEON CTA BUTTON ===== */
        .neon-btn-container { display:flex; justify-content:center; margin-top:10px; }
        .neon-btn-wrap { font-family:'Montserrat',sans-serif; font-style:italic; font-weight:bold; font-size:1.1em; }
        .neon-btn {
            width: 340px; height: 64px;
            cursor: pointer;
            display: flex; justify-content: center; align-items: center;
            padding: 0.5em 4em;
            background: transparent;
            position: relative;
            overflow: hidden;
            -webkit-clip-path: polygon(6% 0, 93% 0, 100% 8%, 100% 86%, 90% 89%, 88% 100%, 5% 100%, 0% 85%);
            clip-path: polygon(6% 0, 93% 0, 100% 8%, 100% 86%, 90% 89%, 88% 100%, 5% 100%, 0% 85%);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .neon-btn .neon-span {
            display: flex;
            -webkit-clip-path: polygon(6% 0, 93% 0, 100% 8%, 100% 86%, 90% 89%, 88% 100%, 5% 100%, 0% 85%);
            clip-path: polygon(6% 0, 93% 0, 100% 8%, 100% 86%, 90% 89%, 88% 100%, 5% 100%, 0% 85%);
            position: absolute; inset: 1px;
            background-color: #0a0a1a;
            z-index: 1;
            transition: all 0.3s ease;
        }
        .neon-btn .neon-txt {
            position: relative; z-index: 2;
            color: aliceblue;
            font-size: 1em;
            text-shadow: 0px 0px 1px #4090b5, 0px 0px 1px #9e30a9, 0 0 1px white;
            display: flex; align-items: center; gap: 0.5rem;
            white-space: nowrap;
            transition: text-shadow 0.3s ease;
        }
        .neon-btn::before {
            content: "";
            position: absolute; height: 300px; aspect-ratio: 1.5/1;
            box-shadow: -17px -19px 20px #9e30a9;
            background-image: conic-gradient(#9e30a9, transparent, transparent, transparent, transparent, transparent, transparent, #9e30a9);
            animation: neonRotate 4s linear infinite -2s;
            z-index: 0;
        }
        .neon-btn::after {
            content: "";
            position: absolute; height: 300px; aspect-ratio: 1.5/1;
            box-shadow: -17px -19px 10px #4090b5;
            background-image: conic-gradient(#4090b5, transparent, transparent, transparent, transparent, transparent, transparent, transparent, #4090b5);
            animation: neonRotate 4s linear infinite;
            z-index: 0;
        }
        @keyframes neonRotate { 0% { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .neon-btn:hover .neon-txt { text-shadow: 2px 4px 1px #9e30a9, 2px 2px 1px #4090b5, 0 0 20px rgba(255,255,255,0.6); }
        .neon-btn:hover .neon-span {
            inset: 2px;
            background: repeating-linear-gradient(to bottom, transparent 0%, #4090b5 1px, #4090b5 3px, #4090b5 5px, #4090b5 4px, transparent 0.5%), repeating-linear-gradient(to left, hsl(295,60%,12%) 100%, hsl(295,60%,12%) 100%);
            box-shadow: inset 0 40px 20px hsl(296,59%,10%);
        }

        /* ===== NEOBRUTALIST CYBERPUNK CARDS ===== */
        .neo-card-swap-container { position:relative; width:380px; height:300px; flex-shrink:0; }
        .neo-card {
            font-family: 'Montserrat', sans-serif;
            width: 360px; min-height: 210px;
            background: var(--card-bg, #ff66a3);
            border: 3px solid #000;
            box-shadow: 10px 10px 0 #000;
            overflow: hidden;
            transition: all 0.25s ease;
            position: absolute;
            top: 50%; left: 50%;
        }
        .neo-card:hover { box-shadow: 14px 14px 0 #000; }
        /* Scanlines */
        .neo-card::after {
            content: '';
            position: absolute; inset: 0;
            background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.07) 2px, rgba(0,0,0,0.07) 4px);
            pointer-events: none; z-index: 5;
        }
        /* Glitch overlay on hover */
        .neo-card::before {
            content: '';
            position: absolute; inset: 0;
            background: var(--glitch-color, rgba(0,212,255,0.15));
            opacity: 0; mix-blend-mode: screen;
            z-index: 10; pointer-events: none;
        }
        .neo-card:hover::before { animation: cardGlitch 0.4s steps(2) infinite; }
        @keyframes cardGlitch {
            0%   { opacity:0; transform:translate(0,0) skewX(0deg); clip-path:none; }
            20%  { opacity:0.6; transform:translate(-3px,1px) skewX(-3deg); clip-path:polygon(0 15%,100% 15%,100% 35%,0 35%); }
            40%  { opacity:0; transform:translate(3px,-2px) skewX(2deg); clip-path:none; }
            60%  { opacity:0.5; transform:translate(-2px,3px) skewX(1deg); clip-path:polygon(0 55%,100% 55%,100% 75%,0 75%); }
            80%  { opacity:0; }
            100% { opacity:0; transform:translate(0,0) skewX(0deg); }
        }
        .neo-card-head {
            font-size: 12px; font-weight: 900;
            height: 32px; background: #fff;
            padding: 0 12px; color: #000;
            border-bottom: 3px solid #000;
            display: flex; align-items: center; justify-content: space-between;
            position: relative; z-index: 6;
        }
        .neo-card-head-tag {
            font-size: 9px; font-weight: 800;
            padding: 2px 6px; border: 2px solid #000;
            background: #000; color: var(--tag-color, #00d4ff);
            letter-spacing: 0.1em; text-transform: uppercase;
        }
        .neo-card-body {
            padding: 12px 14px;
            font-size: 12px; font-weight: 600; color: #000;
            position: relative; z-index: 6;
        }
        .neo-card-body p { margin-bottom: 10px; line-height: 1.5; font-size: 11.5px; font-weight: 600; }
        .neo-card-icon { font-size: 1.8rem; margin-bottom: 6px; }
        .neo-card-btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 11px;
            border: 3px solid #000;
            box-shadow: 3px 3px 0 #000;
            font-weight: 800; font-size: 10px;
            background: var(--btn-bg, #4ade80);
            cursor: pointer; transition: all 0.2s ease;
            text-decoration: none; color: #000;
            letter-spacing: 0.05em;
            font-family: 'Montserrat', sans-serif;
        }
        .neo-card-btn:hover { translate: 2px 2px; box-shadow: 1px 1px 0 #000; }
        .neo-card-btn:active { translate: 3px 3px; box-shadow: none; }
        .neo-card-1 { --card-bg:#ff2d78; --glitch-color:rgba(0,212,255,0.25); }
        .neo-card-2 { --card-bg:#00d4ff; --glitch-color:rgba(255,45,120,0.25); }
        .neo-card-3 { --card-bg:#7c3aed; --glitch-color:rgba(0,255,136,0.25); }
        .neo-card-4 { --card-bg:#00ff88; --glitch-color:rgba(255,0,120,0.25); }
        .neo-card-5 { --card-bg:#f97316; --glitch-color:rgba(124,58,237,0.25); }

        /* ===== TERMINAL KNOWLEDGE BASE ===== */
        .terminal-card {
            background: #1a1b26;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(0,212,255,0.08);
            overflow: hidden;
            max-width: 680px;
            margin: 0 auto;
            font-family: 'JetBrains Mono','Courier New',monospace;
        }
        .terminal-titlebar {
            height: 36px; background: #0d0e17;
            display: flex; align-items: center; padding: 0 12px; gap: 8px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .terminal-dots { display:flex; align-items:center; gap:6px; }
        .terminal-dot { width:11px; height:11px; border-radius:50%; cursor:pointer; transition:filter 0.2s; }
        .terminal-dot:hover { filter:brightness(1.3); }
        .terminal-dot.red { background:#ff5f57; }
        .terminal-dot.yellow { background:#febc2e; }
        .terminal-dot.green { background:#28c840; }
        .terminal-title { flex:1; text-align:center; color:rgba(255,255,255,0.35); font-size:0.7rem; font-family:'JetBrains Mono',monospace; }
        .terminal-body { padding:16px; min-height:260px; }
        .terminal-line { display:flex; align-items:flex-start; gap:8px; margin-bottom:6px; font-size:0.8rem; }
        .terminal-prompt { color:#00d4ff; white-space:nowrap; flex-shrink:0; }
        .terminal-cmd { color:#e2e8f0; white-space:nowrap; }
        .terminal-output { padding:8px 0; }
        .terminal-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:4px 8px; }
        .terminal-item { display:flex; align-items:center; gap:5px; font-size:0.75rem; padding:2px 0; }
        .terminal-item-name { font-weight:700; letter-spacing:0.02em; }
        .terminal-item-desc { font-size:0.62rem; color:rgba(255,255,255,0.35); padding-left:18px; margin-bottom:4px; font-family:'JetBrains Mono',monospace; }
        .terminal-cursor {
            display:inline-block; width:10px; height:16px;
            background:#00d4ff; margin-left:2px;
            animation:termBlink 0.9s ease-in-out infinite;
            vertical-align:middle; border-radius:1px;
        }
        @keyframes termBlink { 0%,100%{opacity:1} 50%{opacity:0} }
        .term-cyan{color:#00d4ff} .term-green{color:#4ade80} .term-yellow{color:#fbbf24}
        .term-pink{color:#f472b6} .term-purple{color:#a78bfa} .term-orange{color:#fb923c}
        .term-white{color:#f1f5f9} .term-dim{color:rgba(255,255,255,0.3)}
    </style>
</head>
<body class="min-h-screen text-[#e2e8f0] antialiased overflow-x-hidden">

    {{-- ========== SPLASH SCREEN ========== --}}
    <div id="splash-screen">
        <div style="display:flex;flex-direction:column;align-items:center;">
            <div class="splash-text"></div>
            <div class="splash-subtitle">Platform Keamanan Berbasis AI</div>
        </div>
    </div>

    {{-- ========== GHOST FIBERS BACKGROUND ========== --}}
    <div id="ghost-fibers-bg" style="position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none;"><canvas id="ghost-fibers-canvas" style="width:100%;height:100%;display:block;"></canvas></div>

    {{-- ========== CYBERPUNK NAVIGATION ========== --}}
    <nav class="cyber-nav">
        <div class="cyber-nav-inner">
            <a href="/" class="cyber-nav-logo">
                <img src="{{ asset('logo-redsim.svg') }}" alt="RedSim Logo">
                <span>RedSim</span>
            </a>
            <div class="cyber-nav-links hidden md:flex">
                <a href="/edukasi/leaderboard" class="cyber-nav-link leaderboard">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    Leaderboard
                </a>
                <a href="#fitur" class="cyber-nav-link">Fitur</a>
                <a href="#kb" class="cyber-nav-link">Knowledge Base</a>
            </div>
            <div class="cyber-nav-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="cyber-btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('masuk') }}" class="cyber-btn-ghost">Masuk</a>
                    <a href="{{ route('daftar') }}" class="cyber-btn-primary">Daftar Gratis</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ========== HERO SECTION ========== --}}
    <section class="relative z-10 px-6 pt-16 pb-24">
        <div class="max-w-4xl mx-auto text-center landing-hero-content">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#00d4ff]/10 border border-[#00d4ff]/20 mb-8 glow-pulse">
                <div class="w-2 h-2 rounded-full bg-[#00d4ff] animate-pulse"></div>
                <span class="text-xs font-medium text-[#00d4ff]">Platform Keamanan Berbasis AI</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-black leading-tight mb-6" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0;">
                <div id="tech-text-container" style="width: 100%; height: 100px; position: relative; max-width: 800px; display: flex; justify-content: center;"></div>
                <div id="stroke-text-container" style="width: 100%; position: relative; max-width: 600px; display: flex; justify-content: center; margin-top: -30px;"></div>
            </h1>

            {{-- Text Typing Welcome --}}
            <div class="text-lg md:text-xl text-[#94a3b8] max-w-2xl mx-auto mb-10 leading-relaxed min-h-[3em]">
                <div id="welcome-texttype" class="texttype-wrap"></div>
            </div>

            {{-- NEON CTA Button --}}
            <div class="neon-btn-container">
                <div class="neon-btn-wrap">
                    <a href="{{ route('daftar') }}" class="neon-btn">
                        <span class="neon-span"></span>
                        <span class="neon-txt">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            MULAI ANALISIS GRATIS
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== FEATURES (CardSwap) ========== --}}
    <section id="fitur" class="relative z-10 px-6 py-24">
        <div class="max-w-6xl mx-auto">
            <div class="card-swap-section">
                {{-- Left: info text --}}
                <div class="card-swap-info flex-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#7c3aed]/15 border border-[#7c3aed]/30 mb-6">
                        <div class="w-1.5 h-1.5 rounded-full bg-[#7c3aed]"></div>
                        <span class="text-xs font-semibold text-[#a78bfa] tracking-wider uppercase">Fitur Unggulan</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 leading-tight">
                        Semua yang Anda butuhkan<br>
                        <span class="gradient-text-anim">dalam satu platform</span>
                    </h2>
                    <p class="text-[#94a3b8] max-w-md leading-relaxed mb-8">
                        Dilengkapi teknologi AI dan knowledge base keamanan siber dari sumber terpercaya dunia. Analisis menyeluruh dalam hitungan detik.
                    </p>
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3 text-sm text-[#94a3b8]">
                            <div class="w-5 h-5 rounded-full bg-[#00d4ff]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            Analisis kode, URL, dan file ZIP
                        </div>
                        <div class="flex items-center gap-3 text-sm text-[#94a3b8]">
                            <div class="w-5 h-5 rounded-full bg-[#7c3aed]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-[#a78bfa]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            Simulasi serangan MITRE ATT&CK
                        </div>
                        <div class="flex items-center gap-3 text-sm text-[#94a3b8]">
                            <div class="w-5 h-5 rounded-full bg-[#22c55e]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-[#22c55e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            Ekspor laporan PDF profesional
                        </div>
                    </div>
                </div>

                {{-- Right: Neo Brutalist Cyberpunk Card Swap --}}
                <div class="neo-card-swap-container" id="neo-card-swap">
                    {{-- Card 1: Analisis Kode --}}
                    <div class="neo-card neo-card-1">
                        <div class="neo-card-head">
                            <span>⟨ ANALISIS KODE /⟩</span>
                            <span class="neo-card-head-tag" style="color:#ff2d78;">AI-POWERED</span>
                        </div>
                        <div class="neo-card-body">
                            <div class="neo-card-icon">💻</div>
                            <p>Tempel kode sumber dan dapatkan analisis kerentanan mendalam dengan remediasi yang jelas dan actionable.</p>
                            <a href="{{ route('daftar') }}" class="neo-card-btn" style="--btn-bg:#00d4ff;">▶ SCAN SEKARANG</a>
                        </div>
                    </div>
                    {{-- Card 2: Pemindaian URL --}}
                    <div class="neo-card neo-card-2">
                        <div class="neo-card-head">
                            <span>⟨ SCAN URL /⟩</span>
                            <span class="neo-card-head-tag" style="color:#00d4ff;">THREAT DETECT</span>
                        </div>
                        <div class="neo-card-body">
                            <div class="neo-card-icon">🌐</div>
                            <p>Pindai URL dan website untuk mendeteksi phishing, malware, dan miskonfigurasi keamanan secara instan.</p>
                            <a href="{{ route('daftar') }}" class="neo-card-btn" style="--btn-bg:#7c3aed; color:#fff;">▶ PINDAI URL</a>
                        </div>
                    </div>
                    {{-- Card 3: Analisis ZIP --}}
                    <div class="neo-card neo-card-3">
                        <div class="neo-card-head">
                            <span>⟨ PROJECT SCAN /⟩</span>
                            <span class="neo-card-head-tag" style="color:#a78bfa;">FULL SCAN</span>
                        </div>
                        <div class="neo-card-body" style="color:#fff;">
                            <div class="neo-card-icon">📦</div>
                            <p style="color:rgba(255,255,255,0.85);">Unggah file ZIP proyek lengkap untuk analisis keamanan komprehensif seluruh kode sumber sekaligus.</p>
                            <a href="{{ route('daftar') }}" class="neo-card-btn" style="--btn-bg:#00ff88;">▶ UPLOAD ZIP</a>
                        </div>
                    </div>
                    {{-- Card 4: Simulasi Serangan --}}
                    <div class="neo-card neo-card-4">
                        <div class="neo-card-head">
                            <span>⟨ ATTACK SIM /⟩</span>
                            <span class="neo-card-head-tag" style="color:#00ff88;">MITRE</span>
                        </div>
                        <div class="neo-card-body">
                            <div class="neo-card-icon">⚔️</div>
                            <p>Visualisasi skenario serangan berdasarkan kerentanan yang ditemukan dengan framework MITRE ATT&CK.</p>
                            <a href="{{ route('daftar') }}" class="neo-card-btn" style="--btn-bg:#ff2d78; color:#fff;">▶ SIMULASI</a>
                        </div>
                    </div>
                    {{-- Card 5: Laporan PDF --}}
                    <div class="neo-card neo-card-5">
                        <div class="neo-card-head">
                            <span>⟨ PDF REPORT /⟩</span>
                            <span class="neo-card-head-tag" style="color:#f97316;">EXPORT</span>
                        </div>
                        <div class="neo-card-body">
                            <div class="neo-card-icon">📋</div>
                            <p>Unduh laporan analisis keamanan lengkap dalam format PDF profesional untuk keperluan audit dan dokumentasi.</p>
                            <a href="{{ route('daftar') }}" class="neo-card-btn" style="--btn-bg:#7c3aed; color:#fff;">▶ EKSPOR PDF</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== KNOWLEDGE BASE (Terminal Style) ========== --}}
    <section id="kb" class="relative z-10 px-6 py-24 border-t border-[#1e2d4a]">
        <div class="max-w-4xl mx-auto text-center">
            <div class="relative inline-block mb-3">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#00d4ff]/10 border border-[#00d4ff]/20">
                    <div class="relative">
                        <div class="w-2 h-2 rounded-full bg-[#00d4ff]"></div>
                        <div class="absolute inset-0 w-2 h-2 rounded-full bg-[#00d4ff] animate-ping opacity-60"></div>
                    </div>
                    <span class="text-xs font-semibold text-[#00d4ff] tracking-wider uppercase">Knowledge Base</span>
                </div>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold mb-4">
                Didukung
                <span class="gradient-text-anim">Knowledge Base Terpercaya</span>
            </h2>
            <p class="text-[#94a3b8] mb-4 max-w-lg mx-auto">
                Analisis kami berbasis data dari sumber keamanan siber internasional terkemuka.
            </p>
            <div class="flex justify-center gap-8 mb-12">
                <div class="kb-stat text-center" style="animation-delay:0.2s">
                    <div class="text-2xl font-black gradient-text-anim">200K+</div>
                    <div class="text-xs text-[#475569] mt-0.5">CVE Entries</div>
                </div>
                <div class="w-px bg-[#1e2d4a]"></div>
                <div class="kb-stat text-center" style="animation-delay:0.35s">
                    <div class="text-2xl font-black gradient-text-anim">14+</div>
                    <div class="text-xs text-[#475569] mt-0.5">MITRE Tactics</div>
                </div>
                <div class="w-px bg-[#1e2d4a]"></div>
                <div class="kb-stat text-center" style="animation-delay:0.5s">
                    <div class="text-2xl font-black gradient-text-anim">1000+</div>
                    <div class="text-xs text-[#475569] mt-0.5">Kerentanan CWE</div>
                </div>
            </div>

            {{-- Terminal Card --}}
            <div class="terminal-card">
                <div class="terminal-titlebar">
                    <div class="terminal-dots">
                        <div class="terminal-dot red"></div>
                        <div class="terminal-dot yellow"></div>
                        <div class="terminal-dot green"></div>
                    </div>
                    <div class="terminal-title">root@redsim: ~/knowledge-base</div>
                    <div style="width:52px;"></div>
                </div>
                <div class="terminal-body">
                    <div class="terminal-line">
                        <span class="terminal-prompt">root@redsim:~$</span>
                        <span class="terminal-cmd" id="term-cmd-1"></span>
                    </div>
                    <div class="terminal-output" id="term-output-1" style="display:none;">
                        <div class="terminal-grid" style="margin-bottom:10px;">
                            <div>
                                <div class="terminal-item"><span class="term-cyan">📁</span><span class="terminal-item-name term-cyan">MITRE_ATTACK/</span></div>
                                <div class="terminal-item-desc">Taktik &amp; teknik serangan</div>
                            </div>
                            <div>
                                <div class="terminal-item"><span class="term-yellow">📁</span><span class="terminal-item-name term-yellow">NVD_CVE/</span></div>
                                <div class="terminal-item-desc">200K+ kerentanan nasional</div>
                            </div>
                            <div>
                                <div class="terminal-item"><span class="term-green">📁</span><span class="terminal-item-name term-green">OWASP/</span></div>
                                <div class="terminal-item-desc">Standar keamanan web global</div>
                            </div>
                            <div>
                                <div class="terminal-item"><span class="term-pink">📁</span><span class="terminal-item-name term-pink">CISA_KEV/</span></div>
                                <div class="terminal-item-desc">Kerentanan dieksploitasi aktif</div>
                            </div>
                            <div>
                                <div class="terminal-item"><span class="term-purple">📁</span><span class="terminal-item-name term-purple">CWE/</span></div>
                                <div class="terminal-item-desc">Enumerasi kelemahan umum</div>
                            </div>
                            <div>
                                <div class="terminal-item"><span class="term-orange">📁</span><span class="terminal-item-name term-orange">CAPEC/</span></div>
                                <div class="terminal-item-desc">Pola serangan &amp; eksploitasi</div>
                            </div>
                        </div>
                        <div style="font-size:0.68rem; color:rgba(255,255,255,0.22); text-align:left; font-family:'JetBrains Mono',monospace; margin-bottom:8px;">
                            6 directories, 0 files &nbsp;|&nbsp; total: 200,000+ records indexed
                        </div>
                    </div>
                    <div class="terminal-line" id="term-line-2" style="display:none;">
                        <span class="terminal-prompt">root@redsim:~$</span>
                        <span class="terminal-cmd" id="term-cmd-2"></span>
                    </div>
                    <div class="terminal-output" id="term-output-2" style="display:none;">
                        <div style="font-family:'JetBrains Mono',monospace; font-size:0.73rem; text-align:left; line-height:1.9;">
                            <div><span class="term-green">STATUS</span>  <span class="term-white">:</span> <span class="term-cyan">ONLINE</span> <span class="term-dim">— all systems operational</span></div>
                            <div><span class="term-green">SYNC</span>    <span class="term-white">:</span> <span class="term-yellow">{{ now()->format('Y-m-d H:i') }} UTC</span></div>
                            <div><span class="term-green">ENTRIES</span> <span class="term-white">:</span> <span class="term-pink">200,847</span> <span class="term-dim">CVE |</span> <span class="term-purple">1,284</span> <span class="term-dim">CWE |</span> <span class="term-orange">568</span> <span class="term-dim">CAPEC</span></div>
                            <div><span class="term-green">MITRE</span>   <span class="term-white">:</span> <span class="term-cyan">14 tactics</span> <span class="term-dim">|</span> <span class="term-cyan">196 techniques</span></div>
                        </div>
                    </div>
                    <div class="terminal-line" id="term-cursor-line">
                        <span class="terminal-prompt">root@redsim:~$</span>
                        <span class="terminal-cursor"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== FOOTER ========== --}}
    <footer class="relative z-10 px-6 py-8 border-t border-[#1e2d4a]" style="background:rgba(5,8,22,0.4)!important;backdrop-filter:blur(8px);">
        <div class="max-w-6xl mx-auto text-center">
            <p class="text-sm text-[#64748b]">&copy; {{ date('Y') }} RedSim. Platform Penilaian Keamanan Sistem Berbasis AI.</p>
        </div>
    </footer>

    {{-- ========== SCRIPTS ========== --}}
    {{-- GSAP for CardSwap --}}
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    {{-- LightRays uses pure vanilla WebGL (no CDN needed) --}}
    <script src="{{ asset('js/animations.js') }}"></script>
    <script src="{{ asset('js/landing-texts.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Init Splash Screen
            initSplash(function() {});

            // 2. Init Ghost Fibers Background (WebGL1)
            (function() {
                var canvas = document.getElementById('ghost-fibers-canvas');
                if (!canvas) return;

                // Force WebGL1 — avoids GLSL ES 3.00 attribute/in keyword mismatch
                var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                if (!gl) { console.warn('GridScan: WebGL not supported'); return; }
                
                var ext = gl.getExtension('OES_standard_derivatives');
                if (!ext) { console.warn('GridScan: OES_standard_derivatives not supported'); }

                // ---- GLSL ES 1.00 Vertex Shader ----
                var vsSource = [
                    'attribute vec2 aPos;',
                    'void main(){gl_Position=vec4(aPos,0.0,1.0);}'
                ].join('\n');

                // ---- GLSL ES 1.00 Fragment Shader ----
                var fsSource = [
                    '#extension GL_OES_standard_derivatives : enable',
                    'precision highp float;',
                    'uniform vec3 iResolution;',
                    'uniform float iTime;',
                    'uniform vec2 uSkew;',
                    'uniform float uTilt;',
                    'uniform float uYaw;',
                    'uniform float uLineThickness;',
                    'uniform vec3 uLinesColor;',
                    'uniform vec3 uScanColor;',
                    'uniform float uGridScale;',
                    'uniform float uLineStyle;',
                    'uniform float uLineJitter;',
                    'uniform float uScanOpacity;',
                    'uniform float uScanDirection;',
                    'uniform float uNoise;',
                    'uniform float uBloomOpacity;',
                    'uniform float uScanGlow;',
                    'uniform float uScanSoftness;',
                    'uniform float uPhaseTaper;',
                    'uniform float uScanDuration;',
                    'uniform float uScanDelay;',
                    'uniform float uLightMode;',
                    'uniform float uScanStarts[8];',
                    'uniform float uScanCount;',
                    'const int MAX_SCANS = 8;',
                    'float smoother01(float a, float b, float x){',
                    '  float t = clamp((x - a) / max(1e-5, (b - a)), 0.0, 1.0);',
                    '  return t * t * t * (t * (t * 6.0 - 15.0) + 10.0);',
                    '}',
                    'void mainImage(out vec4 fragColor, in vec2 fragCoord) {',
                    '    vec2 p = (2.0 * fragCoord - iResolution.xy) / iResolution.y;',
                    '    vec3 ro = vec3(0.0);',
                    '    vec3 rd = normalize(vec3(p, 2.0));',
                    '    float cR = cos(uTilt), sR = sin(uTilt);',
                    '    rd.xy = mat2(cR, -sR, sR, cR) * rd.xy;',
                    '    float cY = cos(uYaw), sY = sin(uYaw);',
                    '    rd.xz = mat2(cY, -sY, sY, cY) * rd.xz;',
                    '    vec2 skew = clamp(uSkew, vec2(-0.7), vec2(0.7));',
                    '    rd.xy += skew * rd.z;',
                    '    vec3 color = vec3(0.0);',
                    '    float minT = 1e20;',
                    '    float gridScale = max(1e-5, uGridScale);',
                    '    float fadeStrength = 2.0;',
                    '    vec2 gridUV = vec2(0.0);',
                    '    float hitIsY = 1.0;',
                    '    for (int i = 0; i < 4; i++) {',
                    '        float isY = float(i < 2);',
                    '        float pos = mix(-0.2, 0.2, float(i)) * isY + mix(-0.5, 0.5, float(i - 2)) * (1.0 - isY);',
                    '        float num = pos - (isY * ro.y + (1.0 - isY) * ro.x);',
                    '        float den = isY * rd.y + (1.0 - isY) * rd.x;',
                    '        float t = num / den;',
                    '        vec3 h = ro + rd * t;',
                    '        float depthBoost = smoothstep(0.0, 3.0, h.z);',
                    '        h.xy += skew * 0.15 * depthBoost;',
                    '        bool use = t > 0.0 && t < minT;',
                    '        gridUV = use ? mix(h.zy, h.xz, isY) / gridScale : gridUV;',
                    '        minT = use ? t : minT;',
                    '        hitIsY = use ? isY : hitIsY;',
                    '    }',
                    '    vec3 hit = ro + rd * minT;',
                    '    float dist = length(hit - ro);',
                    '    float jitterAmt = clamp(uLineJitter, 0.0, 1.0);',
                    '    if (jitterAmt > 0.0) {',
                    '        vec2 j = vec2(sin(gridUV.y * 2.7 + iTime * 1.8), cos(gridUV.x * 2.3 - iTime * 1.6)) * (0.15 * jitterAmt);',
                    '        gridUV += j;',
                    '    }',
                    '    float fx = fract(gridUV.x);',
                    '    float fy = fract(gridUV.y);',
                    '    float ax = min(fx, 1.0 - fx);',
                    '    float ay = min(fy, 1.0 - fy);',
                    '    float wx = fwidth(gridUV.x);',
                    '    float wy = fwidth(gridUV.y);',
                    '    float halfPx = max(0.0, uLineThickness) * 0.5;',
                    '    float tx = halfPx * wx;',
                    '    float ty = halfPx * wy;',
                    '    float aax = wx;',
                    '    float aay = wy;',
                    '    float lineX = 1.0 - smoothstep(tx, tx + aax, ax);',
                    '    float lineY = 1.0 - smoothstep(ty, ty + aay, ay);',
                    '    float primaryMask = max(lineX, lineY);',
                    '    vec2 gridUV2 = (hitIsY > 0.5 ? hit.xz : hit.zy) / gridScale;',
                    '    if (jitterAmt > 0.0) {',
                    '        vec2 j2 = vec2(cos(gridUV2.y * 2.1 - iTime * 1.4), sin(gridUV2.x * 2.5 + iTime * 1.7)) * (0.15 * jitterAmt);',
                    '        gridUV2 += j2;',
                    '    }',
                    '    float fx2 = fract(gridUV2.x);',
                    '    float fy2 = fract(gridUV2.y);',
                    '    float ax2 = min(fx2, 1.0 - fx2);',
                    '    float ay2 = min(fy2, 1.0 - fy2);',
                    '    float wx2 = fwidth(gridUV2.x);',
                    '    float wy2 = fwidth(gridUV2.y);',
                    '    float tx2 = halfPx * wx2;',
                    '    float ty2 = halfPx * wy2;',
                    '    float aax2 = wx2;',
                    '    float aay2 = wy2;',
                    '    float lineX2 = 1.0 - smoothstep(tx2, tx2 + aax2, ax2);',
                    '    float lineY2 = 1.0 - smoothstep(ty2, ty2 + aay2, ay2);',
                    '    float altMask = max(lineX2, lineY2);',
                    '    float edgeDistX = min(abs(hit.x - (-0.5)), abs(hit.x - 0.5));',
                    '    float edgeDistY = min(abs(hit.y - (-0.2)), abs(hit.y - 0.2));',
                    '    float edgeDist = mix(edgeDistY, edgeDistX, hitIsY);',
                    '    float edgeGate = 1.0 - smoothstep(gridScale * 0.5, gridScale * 2.0, edgeDist);',
                    '    altMask *= edgeGate;',
                    '    float lineMask = max(primaryMask, altMask);',
                    '    float fade = exp(-dist * fadeStrength);',
                    '    float dur = max(0.05, uScanDuration);',
                    '    float del = max(0.0, uScanDelay);',
                    '    float scanZMax = 2.0;',
                    '    float widthScale = max(0.1, uScanGlow);',
                    '    float sigma = max(0.001, 0.18 * widthScale * uScanSoftness);',
                    '    float sigmaA = sigma * 2.0;',
                    '    float combinedPulse = 0.0;',
                    '    float combinedAura = 0.0;',
                    '    float cycle = dur + del;',
                    '    float tCycle = mod(iTime, cycle);',
                    '    float scanPhase = clamp((tCycle - del) / dur, 0.0, 1.0);',
                    '    float phase = scanPhase;',
                    '    if (uScanDirection > 0.5 && uScanDirection < 1.5) {',
                    '        phase = 1.0 - phase;',
                    '    } else if (uScanDirection > 1.5) {',
                    '        float t2 = mod(max(0.0, iTime - del), 2.0 * dur);',
                    '        phase = (t2 < dur) ? (t2 / dur) : (1.0 - (t2 - dur) / dur);',
                    '    }',
                    '    float scanZ = phase * scanZMax;',
                    '    float dz = abs(hit.z - scanZ);',
                    '    float lineBand = exp(-0.5 * (dz * dz) / (sigma * sigma));',
                    '    float taper = clamp(uPhaseTaper, 0.0, 0.49);',
                    '    float headW = taper;',
                    '    float tailW = taper;',
                    '    float headFade = smoother01(0.0, headW, phase);',
                    '    float tailFade = 1.0 - smoother01(1.0 - tailW, 1.0, phase);',
                    '    float phaseWindow = headFade * tailFade;',
                    '    float pulseBase = lineBand * phaseWindow;',
                    '    combinedPulse += pulseBase * clamp(uScanOpacity, 0.0, 1.0);',
                    '    float auraBand = exp(-0.5 * (dz * dz) / (sigmaA * sigmaA));',
                    '    combinedAura += (auraBand * 0.25) * phaseWindow * clamp(uScanOpacity, 0.0, 1.0);',
                    '    ',
                    '    float lineVis = lineMask;',
                    '    vec3 gridCol = uLinesColor * lineVis * fade;',
                    '    vec3 scanCol = uScanColor * combinedPulse;',
                    '    vec3 scanAura = uScanColor * combinedAura;',
                    '    color = gridCol + scanCol + scanAura;',
                    '    float n = fract(sin(dot(gl_FragCoord.xy + vec2(iTime * 123.4), vec2(12.9898,78.233))) * 43758.5453123);',
                    '    color += (n - 0.5) * uNoise;',
                    '    color = clamp(color, 0.0, 1.0);',
                    '    float alpha = clamp(max(lineVis, combinedPulse), 0.0, 1.0);',
                    '    float gx = 1.0 - smoothstep(tx * 2.0, tx * 2.0 + aax * 2.0, ax);',
                    '    float gy = 1.0 - smoothstep(ty * 2.0, ty * 2.0 + aay * 2.0, ay);',
                    '    float halo = max(gx, gy) * fade;',
                    '    alpha = max(alpha, halo * clamp(uBloomOpacity, 0.0, 1.0));',
                    '    fragColor = vec4(color, alpha);',
                    '}',
                    'void main(){',
                    '    vec4 c;',
                    '    mainImage(c, gl_FragCoord.xy);',
                    '    gl_FragColor = c;',
                    '}'
                ].join('\n');

                function compileShader(type, src) {
                    var s = gl.createShader(type);
                    gl.shaderSource(s, src);
                    gl.compileShader(s);
                    if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) {
                        console.error('GridScan shader error:', gl.getShaderInfoLog(s));
                        return null;
                    }
                    return s;
                }

                var vs = compileShader(gl.VERTEX_SHADER, vsSource);
                var fs = compileShader(gl.FRAGMENT_SHADER, fsSource);
                if (!vs || !fs) return;

                var prog = gl.createProgram();
                gl.attachShader(prog, vs);
                gl.attachShader(prog, fs);
                gl.linkProgram(prog);
                if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) {
                    console.error('GridScan link error:', gl.getProgramInfoLog(prog));
                    return;
                }
                gl.useProgram(prog);

                var buf = gl.createBuffer();
                gl.bindBuffer(gl.ARRAY_BUFFER, buf);
                gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1, 3,-1, -1,3]), gl.STATIC_DRAW);

                var aPos = gl.getAttribLocation(prog, 'aPos');
                if (aPos >= 0) {
                    gl.enableVertexAttribArray(aPos);
                    gl.vertexAttribPointer(aPos, 2, gl.FLOAT, false, 0, 0);
                }

                var loc_iResolution = gl.getUniformLocation(prog, 'iResolution');
                var loc_iTime = gl.getUniformLocation(prog, 'iTime');
                var loc_uSkew = gl.getUniformLocation(prog, 'uSkew');
                var loc_uTilt = gl.getUniformLocation(prog, 'uTilt');
                var loc_uYaw = gl.getUniformLocation(prog, 'uYaw');
                var loc_uLineThickness = gl.getUniformLocation(prog, 'uLineThickness');
                var loc_uLinesColor = gl.getUniformLocation(prog, 'uLinesColor');
                var loc_uScanColor = gl.getUniformLocation(prog, 'uScanColor');
                var loc_uGridScale = gl.getUniformLocation(prog, 'uGridScale');
                var loc_uLineStyle = gl.getUniformLocation(prog, 'uLineStyle');
                var loc_uLineJitter = gl.getUniformLocation(prog, 'uLineJitter');
                var loc_uScanOpacity = gl.getUniformLocation(prog, 'uScanOpacity');
                var loc_uScanDirection = gl.getUniformLocation(prog, 'uScanDirection');
                var loc_uNoise = gl.getUniformLocation(prog, 'uNoise');
                var loc_uBloomOpacity = gl.getUniformLocation(prog, 'uBloomOpacity');
                var loc_uScanGlow = gl.getUniformLocation(prog, 'uScanGlow');
                var loc_uScanSoftness = gl.getUniformLocation(prog, 'uScanSoftness');
                var loc_uPhaseTaper = gl.getUniformLocation(prog, 'uPhaseTaper');
                var loc_uScanDuration = gl.getUniformLocation(prog, 'uScanDuration');
                var loc_uScanDelay = gl.getUniformLocation(prog, 'uScanDelay');
                var loc_uLightMode = gl.getUniformLocation(prog, 'uLightMode');
                var loc_uScanCount = gl.getUniformLocation(prog, 'uScanCount');

                // Configuration matching GridScan React parameters (but red theme)
                gl.uniform1f(loc_uLineThickness, 1.0);
                gl.uniform3f(loc_uLinesColor, 0.20, 0.12, 0.12); // darker red-grey
                gl.uniform3f(loc_uScanColor, 0.9, 0.2, 0.3); // vivid red/pink
                gl.uniform1f(loc_uGridScale, 0.1);
                gl.uniform1f(loc_uLineStyle, 0.0); // solid
                gl.uniform1f(loc_uLineJitter, 0.1);
                gl.uniform1f(loc_uScanOpacity, 0.4);
                gl.uniform1f(loc_uScanDirection, 2.0); // pingpong
                gl.uniform1f(loc_uNoise, 0.01);
                gl.uniform1f(loc_uBloomOpacity, 0.6);
                gl.uniform1f(loc_uScanGlow, 0.5);
                gl.uniform1f(loc_uScanSoftness, 2.0);
                gl.uniform1f(loc_uPhaseTaper, 0.9);
                gl.uniform1f(loc_uScanDuration, 2.0);
                gl.uniform1f(loc_uScanDelay, 2.0);
                gl.uniform1f(loc_uLightMode, 0.0);
                gl.uniform1f(loc_uScanCount, 0.0);

                var targetSkew = [0, 0], currSkew = [0, 0];
                var targetTilt = 0, currTilt = 0;
                var targetYaw = 0, currYaw = 0;

                document.addEventListener('mousemove', function(e) {
                    var nx = (e.clientX / window.innerWidth) * 2 - 1;
                    var ny = -(e.clientY / window.innerHeight) * 2 + 1;
                    var sensitivity = 0.55;
                    var skewScale = 0.06 + (0.2 - 0.06) * sensitivity;
                    var tiltScale = 0.12 + (0.3 - 0.12) * sensitivity;
                    var yawScale = 0.1 + (0.28 - 0.1) * sensitivity;
                    targetSkew = [nx * skewScale, ny * skewScale * 1.4];
                    targetTilt = -ny * tiltScale;
                    targetYaw = nx * yawScale;
                });

                function resize() {
                    var w = window.innerWidth;
                    var h = window.innerHeight;
                    var dpr = Math.min(window.devicePixelRatio || 1, 2);
                    canvas.width  = w * dpr;
                    canvas.height = h * dpr;
                    gl.viewport(0, 0, canvas.width, canvas.height);
                    gl.uniform3f(loc_iResolution, canvas.width, canvas.height, dpr);
                }
                window.addEventListener('resize', resize);
                resize();

                var startTime = performance.now();
                function render() {
                    var now = performance.now();
                    var time = (now - startTime) * 0.001;
                    
                    // Smooth damp
                    var dt = 0.016;
                    currSkew[0] += (targetSkew[0] - currSkew[0]) * 5.0 * dt;
                    currSkew[1] += (targetSkew[1] - currSkew[1]) * 5.0 * dt;
                    currTilt += (targetTilt - currTilt) * 5.0 * dt;
                    currYaw += (targetYaw - currYaw) * 5.0 * dt;

                    gl.uniform1f(loc_iTime, time);
                    gl.uniform2f(loc_uSkew, currSkew[0], currSkew[1]);
                    gl.uniform1f(loc_uTilt, currTilt);
                    gl.uniform1f(loc_uYaw, currYaw);

                    gl.drawArrays(gl.TRIANGLES, 0, 3);
                    requestAnimationFrame(render);
                }
                requestAnimationFrame(render);
            })();

            // 3. Init Decrypted Text Loop
            initDecryptedTextLoop(
                document.getElementById('welcome-texttype'),
                [
                    'Selamat datang di RedSim — garda terdepan keamanan digital Anda.',
                    'Analisis kerentanan kode, URL, dan proyek secara otomatis dengan AI.',
                    'Temukan celah keamanan sebelum peretas menemukannya.',
                    'Didukung knowledge base dari MITRE, OWASP, dan NVD.',
                    'Lindungi sistem Anda dengan kecerdasan buatan terpercaya.'
                ],
                { speed: 50, pauseDuration: 2500, maxIterations: 15 }
            );

            // 4. Init TechText
            initTechText(document.getElementById('tech-text-container'), {
                text: 'Penilaian Keamanan',
                fontSize: 60, // scaled down to fit typically h1 sizing without going too big
                color: '#ffffff',
                accentColor: '#E5334C', // red accent
                dashLength: 4,
                dashGap: 2,
                reach: 150,
                specks: 10
            });

            // 5. Init StrokeText
            initStrokeText(document.getElementById('stroke-text-container'), {
                text: 'Sistem Anda',
                fontSize: 50,
                strokeColor: '#E5334C', // red accent
                fillColor: '#ffffff',
                strokeWidth: 1.5,
                drawDuration: 2.0,
                fillDelay: 0.2
            });

            // 4. Init KB Animation
            initKBAnimation();

            // 5. Animate KB stats when in view
            var statsObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                    }
                });
            }, { threshold: 0.2 });
            document.querySelectorAll('.kb-stat').forEach(function(el) {
                el.style.animationPlayState = 'paused';
                statsObserver.observe(el);
            });

            // 6. Neo Brutalist Card Swap
            (function() {
                var container = document.getElementById('neo-card-swap');
                if (!container || typeof gsap === 'undefined') return;
                var cards = Array.from(container.querySelectorAll('.neo-card'));
                if (!cards.length) return;
                var W = 360, H = 210;
                cards.forEach(function(card, i) {
                    gsap.set(card, {
                        x: -W/2, y: -H/2 + i * 8,
                        rotation: (i - 2) * 2.5,
                        zIndex: cards.length - i,
                        opacity: i === 0 ? 1 : 0.82 - i * 0.1
                    });
                });
                var current = 0;
                function swapNext() {
                    var total = cards.length;
                    var topCard = cards[current % total];
                    gsap.to(topCard, {
                        x: -W/2 + 520, y: -H/2 - 80,
                        rotation: 18, opacity: 0,
                        duration: 0.45, ease: 'power2.in',
                        onComplete: function() {
                            gsap.set(topCard, {
                                x: -W/2, y: -H/2 + (total - 1) * 8,
                                rotation: (total - 3) * 2.5,
                                zIndex: 1, opacity: 0.55
                            });
                            current = (current + 1) % total;
                            for (var j = 0; j < total; j++) {
                                var idx = (current + j) % total;
                                var card = cards[idx];
                                gsap.to(card, {
                                    x: -W/2, y: -H/2 + j * 8,
                                    rotation: (j - 2) * 2.5,
                                    zIndex: total - j,
                                    opacity: j === 0 ? 1 : 0.82 - j * 0.1,
                                    duration: 0.4, ease: 'back.out(1.2)',
                                    delay: j * 0.04
                                });
                            }
                        }
                    });
                }
                setInterval(swapNext, 3500);
            })();

            // 7. Terminal Typewriter
            (function() {
                function typeText(el, text, speed, cb) {
                    var i = 0; el.textContent = '';
                    var t = setInterval(function() {
                        el.textContent += text[i]; i++;
                        if (i >= text.length) { clearInterval(t); if (cb) setTimeout(cb, 350); }
                    }, speed);
                }
                var termObs = new IntersectionObserver(function(entries) {
                    if (!entries[0].isIntersecting) return;
                    termObs.disconnect();
                    var cmd1 = document.getElementById('term-cmd-1');
                    var out1 = document.getElementById('term-output-1');
                    var line2 = document.getElementById('term-line-2');
                    var cmd2 = document.getElementById('term-cmd-2');
                    var out2 = document.getElementById('term-output-2');
                    var cursorLine = document.getElementById('term-cursor-line');
                    cursorLine.style.display = 'none';
                    setTimeout(function() {
                        typeText(cmd1, 'ls /redsim/knowledgebase', 40, function() {
                            out1.style.display = 'block';
                            setTimeout(function() {
                                line2.style.display = 'flex';
                                typeText(cmd2, 'cat /redsim/knowledgebase/.status', 38, function() {
                                    out2.style.display = 'block';
                                    setTimeout(function() { cursorLine.style.display = 'flex'; }, 300);
                                });
                            }, 700);
                        });
                    }, 500);
                }, { threshold: 0.3 });
                var terminal = document.querySelector('.terminal-card');
                if (terminal) termObs.observe(terminal);
            })();
        });
    </script>
</body>
</html>
