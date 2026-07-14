<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RedSim — Platform Penilaian Keamanan Sistem Berbasis AI. Analisis kerentanan kode, URL, dan file dengan teknologi AI canggih.">
    <title>RedSim — Platform Penilaian Keamanan Sistem Berbasis AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing-animations.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#050816] text-[#e2e8f0] antialiased overflow-x-hidden">

    {{-- ========== SPLASH SCREEN ========== --}}
    <div id="splash-screen">
        <div style="display:flex;flex-direction:column;align-items:center;">
            <div class="splash-text"></div>
            <div class="splash-subtitle">Platform Keamanan Berbasis AI</div>
        </div>
    </div>

    {{-- ========== LIGHT RAYS BACKGROUND ========== --}}
    <div class="lightrays-wrap" id="lightrays-bg"></div>

    {{-- ========== NAVIGATION ========== --}}
    <nav class="relative z-50 px-6 py-5">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#00d4ff] to-[#7c3aed] flex items-center justify-center shadow-lg shadow-[#00d4ff]/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="text-xl font-bold gradient-text-anim">RedSim</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="/edukasi/leaderboard" class="hidden md:flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-yellow-400 hover:bg-yellow-400/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg> Leaderboard
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white text-sm font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300">Dashboard</a>
                @else
                    <a href="{{ route('masuk') }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-[#94a3b8] hover:text-white transition-colors">Masuk</a>
                    <a href="{{ route('daftar') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white text-sm font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300 hover:-translate-y-0.5">Daftar Gratis</a>
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

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-black leading-tight mb-6">
                <span class="text-white">Penilaian Keamanan</span><br>
                <span class="gradient-text-anim">Sistem Anda</span>
            </h1>

            {{-- Text Typing Welcome --}}
            <div class="text-lg md:text-xl text-[#94a3b8] max-w-2xl mx-auto mb-10 leading-relaxed min-h-[3em]">
                <div id="welcome-texttype" class="texttype-wrap"></div>
            </div>

            {{-- CTA Button with BorderGlow --}}
            <div class="flex justify-center">
                <div class="border-glow-card" id="cta-btn-wrap" style="width: auto; margin-top: 10px;">
                    <span class="edge-light"></span>
                    <div class="border-glow-inner">
                        <a href="{{ route('daftar') }}" class="cta-link" style="padding-left: 3rem; padding-right: 3rem; justify-content: center;">
                            Mulai Analisis Gratis
                            <svg class="w-5 h-5 cta-link-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    </div>
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

                {{-- Right: CardSwap --}}
                <div class="flex-shrink-0 flex items-center justify-center" style="width: 500px; height: 480px; position: relative;">
                    <div id="card-swap-container" class="card-swap-container" style="width:440px;height:280px;">
                        {{-- Card 1: Analisis Kode --}}
                        <div class="cs-card" style="border-color: rgba(0,212,255,0.2);">
                            <div class="cs-card-icon" style="background: rgba(0,212,255,0.1);">
                                <svg style="color:#00d4ff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            </div>
                            <h3>Analisis Kode</h3>
                            <p>Tempel kode sumber dan dapatkan analisis kerentanan mendalam dengan remediasi yang jelas dan actionable.</p>
                            <div class="cs-card-tag" style="background: rgba(0,212,255,0.1); color: #00d4ff;">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                                Real-time AI
                            </div>
                        </div>

                        {{-- Card 2: Pemindaian URL --}}
                        <div class="cs-card" style="border-color: rgba(124,58,237,0.2);">
                            <div class="cs-card-icon" style="background: rgba(124,58,237,0.1);">
                                <svg style="color:#a78bfa;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            </div>
                            <h3>Pemindaian URL</h3>
                            <p>Pindai URL dan website untuk mendeteksi phishing, malware, dan miskonfigurasi keamanan secara instan.</p>
                            <div class="cs-card-tag" style="background: rgba(124,58,237,0.1); color: #a78bfa;">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Threat Detection
                            </div>
                        </div>

                        {{-- Card 3: Analisis ZIP --}}
                        <div class="cs-card" style="border-color: rgba(34,197,94,0.2);">
                            <div class="cs-card-icon" style="background: rgba(34,197,94,0.1);">
                                <svg style="color:#22c55e;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <h3>Analisis Proyek ZIP</h3>
                            <p>Unggah file ZIP proyek lengkap untuk analisis keamanan komprehensif seluruh kode sumber sekaligus.</p>
                            <div class="cs-card-tag" style="background: rgba(34,197,94,0.1); color: #22c55e;">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                                Full Project Scan
                            </div>
                        </div>

                        {{-- Card 4: Simulasi Serangan --}}
                        <div class="cs-card" style="border-color: rgba(249,115,22,0.2);">
                            <div class="cs-card-icon" style="background: rgba(249,115,22,0.1);">
                                <svg style="color:#f97316;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h3>Simulasi Serangan</h3>
                            <p>Visualisasi skenario serangan berdasarkan kerentanan yang ditemukan dengan framework MITRE ATT&CK.</p>
                            <div class="cs-card-tag" style="background: rgba(249,115,22,0.1); color: #f97316;">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/></svg>
                                MITRE ATT&CK
                            </div>
                        </div>

                        {{-- Card 5: Laporan PDF --}}
                        <div class="cs-card" style="border-color: rgba(124,58,237,0.2);">
                            <div class="cs-card-icon" style="background: rgba(124,58,237,0.1);">
                                <svg style="color:#c084fc;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <h3>Ekspor Laporan PDF</h3>
                            <p>Unduh laporan analisis keamanan lengkap dalam format PDF profesional untuk keperluan audit dan dokumentasi.</p>
                            <div class="cs-card-tag" style="background: rgba(124,58,237,0.1); color: #c084fc;">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/></svg>
                                PDF Export
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== KNOWLEDGE BASE ========== --}}
    <section class="relative z-10 px-6 py-24 border-t border-[#1e2d4a]">
        <div class="max-w-4xl mx-auto text-center">
            {{-- Header with animated badge --}}
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

            {{-- Stats row --}}
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

            {{-- KB Cards grid --}}
            @php
            $kbSources = [
                ['name' => 'MITRE ATT&CK', 'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>', 'desc' => 'Taktik & teknik serangan siber', 'delay' => 0],
                ['name' => 'NVD (CVE)', 'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>', 'desc' => 'Database kerentanan nasional', 'delay' => 80],
                ['name' => 'OWASP', 'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>', 'desc' => 'Standar keamanan web global', 'delay' => 160],
                ['name' => 'CISA KEV', 'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>', 'desc' => 'Kerentanan yang dieksploitasi aktif', 'delay' => 240],
                ['name' => 'CWE', 'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>', 'desc' => 'Enumerasi kelemahan umum', 'delay' => 320],
                ['name' => 'CAPEC', 'icon' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', 'desc' => 'Pola serangan & eksploitasi', 'delay' => 400],
            ];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($kbSources as $src)
                <div class="kb-card" data-delay="{{ $src['delay'] }}">
                    <div class="kb-card-inner">
                        <div class="kb-shimmer"></div>
                        <span class="kb-icon">{!! $src['icon'] !!}</span>
                        <p class="kb-name">{{ $src['name'] }}</p>
                        <p class="kb-desc">{{ $src['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ========== FOOTER ========== --}}
    <footer class="relative z-10 px-6 py-8 border-t border-[#1e2d4a]">
        <div class="max-w-6xl mx-auto text-center">
            <p class="text-sm text-[#64748b]">&copy; {{ date('Y') }} RedSim. Platform Penilaian Keamanan Sistem Berbasis AI.</p>
        </div>
    </footer>

    {{-- ========== SCRIPTS ========== --}}
    {{-- GSAP for CardSwap --}}
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    {{-- LightRays uses pure vanilla WebGL (no CDN needed) --}}
    <script src="{{ asset('js/animations.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Init Splash Screen
            initSplash(function() {});

            // 2. Init Light Rays Background (pure vanilla WebGL)
            var lrContainer = document.getElementById('lightrays-bg');
            if (lrContainer) {
                initLightRays(lrContainer, {
                    raysOrigin: 'top-center',
                    raysColor: [0.2, 0.7, 1.0],
                    raysSpeed: 0.8,
                    lightSpread: 0.45,
                    rayLength: 2.5,
                    followMouse: true,
                    mouseInfluence: 0.12,
                    noiseAmount: 0,
                    distortion: 0,
                    pulsating: false,
                    fadeDistance: 1.2,
                    saturation: 0.9,
                });
            }

            // 3. Init TextType Welcome
            initTextType(
                document.getElementById('welcome-texttype'),
                [
                    'Selamat datang di RedSim — garda terdepan keamanan digital Anda.',
                    'Analisis kerentanan kode, URL, dan proyek secara otomatis dengan AI.',
                    'Temukan celah keamanan sebelum peretas menemukannya.',
                    'Didukung knowledge base dari MITRE, OWASP, dan NVD.',
                    'Lindungi sistem Anda dengan kecerdasan buatan terpercaya.'
                ],
                { typingSpeed: 45, deletingSpeed: 25, pauseDuration: 2500, loop: true }
            );

            // 4. Init BorderGlow on CTA button
            initBorderGlow('#cta-btn-wrap');

            // 5. Init CardSwap for features section
            var cardSwapContainer = document.getElementById('card-swap-container');
            if (cardSwapContainer && typeof gsap !== 'undefined') {
                initCardSwap(cardSwapContainer, {
                    cardDistance: 55,
                    verticalDistance: 65,
                    delay: 4500,
                    skewAmount: 5,
                });
            }

            // 6. Init Knowledge Base animation
            initKBAnimation();

            // 7. Animate KB stats when in view
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
        });
    </script>
</body>
</html>
