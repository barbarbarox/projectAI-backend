<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="RedSim — Platform Penilaian Keamanan Sistem Berbasis AI">
    @hasSection('page_feature')
    <meta name="page-feature" content="@yield('page_feature')">
    @endif
    <title>@yield('title', 'RedSim — Platform Keamanan AI')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/magic-bento.css') }}">
    <link rel="stylesheet" href="{{ asset('css/branched-menu.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-screen antialiased" style="background-color:#020609;color:#f1f5f9;"
    x-data="{
        sidebarOpen: window.innerWidth >= 1024,
        isMobile: window.innerWidth < 1024
    }"
    @resize.window="isMobile = window.innerWidth < 1024; if (!isMobile) sidebarOpen = true">
    <div class="flex min-h-screen">

        {{-- ══════════════ SIDEBAR ══════════════ --}}
        {{-- Mobile Overlay Backdrop --}}
        <div x-show="sidebarOpen && isMobile"
             x-transition:enter="transition-opacity ease-in-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in-out duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 lg:hidden"
             style="background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);"
             x-cloak></div>

        <aside class="fixed left-0 top-0 h-full w-64 flex flex-col z-50 transition-transform duration-300"
               style="background-color:#080c18;border-right:1px solid #1e293b;"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            {{-- Logo --}}
            <div class="px-5 py-5" style="border-bottom:1px solid #1e293b;">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    {{-- RedSim Shield Icon --}}
                    <img src="{{ asset('logo-redsim1:1.svg') }}" alt="RedSim Logo" class="w-10 h-10 rounded-xl transition-all duration-300 group-hover:shadow-[0_0_15px_rgba(220,38,38,0.45)] object-contain">
                    <div>
                        <h1 class="text-base font-bold tracking-tight" style="background:linear-gradient(135deg,#ef4444,#06b6d4);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;">RedSim</h1>
                        <p class="text-[10px] uppercase tracking-widest" style="color:#475569;">AI Security Platform</p>
                    </div>
                </a>
            </div>

            {{-- Navigation (BranchedMenu Circuit Tree) --}}
            <div class="flex-1 px-3 py-3 overflow-y-auto overflow-x-hidden">
                <x-branched-menu />
            </div>

            {{-- User + Logout --}}
            <div class="px-3 py-4" style="border-top:1px solid #1e293b;">
                @auth
                <div class="flex items-center gap-3 px-3 py-2 mb-3">
                    @if(Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatar }}" class="w-8 h-8 rounded-full object-cover ring-2 ring-red-600/30" alt="Avatar" referrerpolicy="no-referrer">
                    @else
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0"
                         style="background:linear-gradient(135deg,#dc2626,#06b6d4);">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold truncate text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs truncate" style="color:#64748b;">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-1.5">
                    <a href="{{ route('profile.edit') }}"
                       class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-300 transition-all duration-200 hover:text-white hover:bg-slate-800/70"
                       style="border:1px solid #1e293b;">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Edit Profil</span>
                    </a>
                    <form method="POST" action="{{ route('keluar') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 text-red-400 hover:text-red-300 hover:bg-red-950/40"
                                style="border:1px solid rgba(220,38,38,0.15);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
                @else
                <div class="flex flex-col gap-2">
                    <a href="{{ route('masuk') }}"
                       class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-red-400 transition-all duration-200 hover:bg-red-950/40"
                       style="border:1px solid rgba(239,68,68,0.25);">
                        Masuk
                    </a>
                    <a href="{{ route('daftar') }}"
                       class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold text-white transition-all duration-300 hover:shadow-[0_0_15px_rgba(220,38,38,0.35)]"
                       style="background:linear-gradient(135deg,#dc2626,#991b1b);">
                        Daftar Gratis
                    </a>
                </div>
                @endauth
            </div>
        </aside>

        {{-- ══════════════ MAIN CONTENT ══════════════ --}}
        <main class="flex-1 min-h-screen transition-all duration-300" :style="sidebarOpen && !isMobile ? 'margin-left:256px;' : 'margin-left:0;'">

            {{-- Top Bar --}}
            <header class="sticky top-0 z-40 px-6 py-3.5" style="background:rgba(2,6,9,0.85);backdrop-filter:blur(20px);border-bottom:1px solid #1e293b;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        {{-- Sidebar toggle --}}
                        <button @click="sidebarOpen = !sidebarOpen"
                                class="p-1.5 rounded-lg text-slate-500 hover:text-white hover:bg-slate-800/60 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div>
                            <h2 class="text-base font-semibold text-white leading-tight">@yield('header', 'Dashboard')</h2>
                            <p class="text-xs leading-tight mt-0.5" style="color:#64748b;">@yield('subheader', '')</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- ══ Tombol Panduan (muncul di semua halaman yang punya page_feature) ══ --}}
                        @hasSection('page_feature')
                        @php $pf = trim(\Illuminate\Support\Facades\View::yieldContent('page_feature')); @endphp
                        @if($pf && $pf !== 'welcome')
                        <button data-feature="{{ $pf }}"
                                onclick="window.dispatchEvent(new CustomEvent('open-help', { detail: { feature: this.dataset.feature } }))"
                                class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 hover:scale-105 group"
                                style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#f87171;"
                                title="Panduan penggunaan halaman ini">
                            <svg class="w-3.5 h-3.5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Panduan</span>
                        </button>
                        {{-- Mobile version --}}
                        <button data-feature="{{ $pf }}"
                                onclick="window.dispatchEvent(new CustomEvent('open-help', { detail: { feature: this.dataset.feature } }))"
                                class="sm:hidden flex items-center justify-center w-8 h-8 rounded-lg transition-all"
                                style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#f87171;"
                                title="Panduan">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                        @endif
                        @endif

                        {{-- AI Status indicator --}}
                        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium"
                             style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);color:#34d399;">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                            AI Aktif
                        </div>

                        @auth
                        {{-- Monitoring Alert Badge --}}
                        @php
                            $monitoringUnreadCount = \App\Models\WebsiteIntegrityAlert::query()
                                ->where('user_id', Auth::id())
                                ->where('is_read', false)
                                ->where('alert_type', '!=', 'clean')
                                ->count();
                        @endphp
                        @if($monitoringUnreadCount > 0)
                        <a href="{{ route('monitoring.alerts') }}"
                           class="relative flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-amber-400 transition-all hover:bg-amber-500/10"
                           style="background:rgba(245,158,11,0.06); border:1px solid rgba(245,158,11,0.25);"
                           title="{{ $monitoringUnreadCount }} alert monitoring belum dibaca">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span>{{ $monitoringUnreadCount }}</span>
                            <span class="hidden sm:inline text-amber-300/70">Alert</span>
                        </a>
                        @endif

                        <div class="text-xs px-3 py-1.5 rounded-lg" style="background:rgba(15,23,42,0.8);border:1px solid #1e293b;color:#64748b;">
                            Scan hari ini:
                            <span class="font-bold" style="color:#ef4444;">{{ Auth::user()->scan_count_today }}/{{ env('MAX_SCANS_PER_DAY_FREE', 10) }}</span>
                        </div>
                        @else
                        <a href="{{ route('masuk') }}" class="text-xs text-red-400 hover:text-red-300 underline transition-colors">
                            Masuk untuk Analisis
                        </a>
                        @endauth
                    </div>
                </div>
            </header>


            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="mx-6 mt-4 px-4 py-3 rounded-lg text-sm" x-data="{ show: true }" x-show="show" x-transition
                 style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.25);color:#4ade80;">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-green-400/50 hover:text-green-400 transition-colors shrink-0">&times;</button>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mx-6 mt-4 px-4 py-3 rounded-lg text-sm" x-data="{ show: true }" x-show="show" x-transition
                 style="background:rgba(220,38,38,0.08);border:1px solid rgba(220,38,38,0.3);color:#f87171;">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-red-400/50 hover:text-red-400 transition-colors shrink-0">&times;</button>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="mx-6 mt-4 px-4 py-3 rounded-lg text-sm"
                 style="background:rgba(220,38,38,0.08);border:1px solid rgba(220,38,38,0.3);color:#f87171;">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Page Content --}}
            <div class="p-6">
                @yield('content')
            </div>
        </main>
    </div>

    @include('chat-widget')

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="{{ asset('js/magic-bento.js') }}"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/9.1.6/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>

    <style>
    /* ═══════════════════════════════════════════
       MARKDOWN CONTENT — Chat Widget
    ═══════════════════════════════════════════ */
    .chat-md-content { color: #e2e8f0; word-wrap: break-word; overflow-wrap: break-word; }
    .chat-md-content > *:first-child { margin-top: 0; }
    .chat-md-content > *:last-child  { margin-bottom: 0; }
    .chat-md-content p  { margin-bottom: 0.5rem; line-height: 1.6; }
    .chat-md-content strong { font-weight: 700; color: #fff; }
    .chat-md-content em { font-style: italic; color: #cbd5e1; }
    .chat-md-content u, .chat-md-content ins { text-decoration: underline; text-underline-offset: 2px; text-decoration-color: #38bdf8; color: #f1f5f9; }
    .chat-md-content s, .chat-md-content del { text-decoration: line-through; color: #64748b; }
    .chat-md-content h1, .chat-md-content h2,
    .chat-md-content h3, .chat-md-content h4 { font-weight: 700; margin: 0.75rem 0 0.5rem; color: #fff; }
    .chat-md-content ul { list-style-type: disc !important; padding-left: 1.25rem !important; margin: 0.5rem 0 !important; }
    .chat-md-content ol { list-style-type: decimal !important; padding-left: 1.25rem !important; margin: 0.5rem 0 !important; }
    .chat-md-content li { display: list-item !important; margin-bottom: 0.25rem; }
    .chat-md-content li::marker { color: #38bdf8 !important; font-weight: 700; }
    .chat-md-content a  { color: #22d3ee; text-decoration: underline; }
    .chat-md-content img { max-width: 100%; border-radius: 0.5rem; margin: 0.5rem 0; }
    .chat-md-content blockquote {
        border-left: 3px solid #06b6d4;
        margin: 0.5rem 0;
        padding: 0.5rem 0.75rem;
        background: rgba(6,182,212,0.06);
        border-radius: 0 0.375rem 0.375rem 0;
    }
    .chat-md-content code:not(pre code) {
        background: #111a2e;
        padding: 0.15em 0.35em;
        border-radius: 3px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.85em;
        color: #38bdf8;
        border: 1px solid #26364b;
    }
    .chat-md-content pre {
        background: #080c16;
        margin: 0.5rem 0;
        padding: 0.75rem 1rem;
        overflow-x: auto;
        border-radius: 0.5rem;
        border: 1px solid #1e293b;
    }
    .md-code-block { margin: 0.75rem 0; border-radius: 0.5rem; overflow: hidden; border: 1px solid #1e293b; background: #080c16; }
    .md-code-block pre { margin: 0 !important; border: none !important; border-radius: 0 !important; padding: 0.75rem 1rem !important; }
    .md-code-block pre code { background: transparent !important; border: none !important; padding: 0 !important; font-size: 0.75rem !important; }
    .md-code-header { display: flex; align-items: center; justify-content: space-between; background: #0d1527; padding: 0.35rem 0.75rem; border-bottom: 1px solid #1e293b; }
    .md-code-lang    { font-size: 0.65rem; color: #38bdf8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; font-family: monospace; }
    .md-copy-btn { display: flex; align-items: center; gap: 0.25rem; font-size: 0.65rem; color: #94a3b8; background: rgba(30, 41, 59, 0.6); border: 1px solid #334155; border-radius: 0.25rem; padding: 0.15rem 0.5rem; cursor: pointer; transition: all .2s; }
    .md-copy-btn:hover { color: #fff; border-color: #38bdf8; background: rgba(56, 189, 248, 0.15); }
    .md-table-wrap { overflow-x: auto; margin: 0.75rem 0; border-radius: 0.5rem; border: 1px solid #26364b; }
    .chat-md-content table { width: 100%; border-collapse: collapse; font-size: 0.75rem; border: 1px solid #26364b; }
    .chat-md-content th { padding: 0.5rem 0.75rem; background: #111a2e; color: #38bdf8; font-weight: 700; text-align: left; border: 1px solid #26364b; text-transform: uppercase; }
    .chat-md-content td { padding: 0.5rem 0.75rem; color: #cbd5e1; border: 1px solid #1e293b; }
    .chat-md-content tr:nth-child(even) td { background: rgba(17, 26, 46, 0.45); }
    .chat-md-content tr:hover td { background: rgba(56, 189, 248, 0.08); }

    /* ═══════════════════════════════════════════
       SCAN PROGRESS INDICATOR
    ═══════════════════════════════════════════ */
    .scanning-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        background: rgba(6,182,212,0.1);
        border: 1px solid rgba(6,182,212,0.25);
        color: #22d3ee;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .scanning-indicator .dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #06b6d4;
        animation: scan-dot 1.2s ease-in-out infinite;
    }
    @keyframes scan-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.3; transform: scale(0.6); }
    }

    /* ═══════════════════════════════════════════
       SIDEBAR NAV — active states (duplicated for inline usage)
    ═══════════════════════════════════════════ */
    .nav-active-security {
        background: rgba(220,38,38,0.10) !important;
        color: #ef4444 !important;
        border: 1px solid rgba(220,38,38,0.25) !important;
    }
    .nav-active-default {
        background: rgba(6,182,212,0.10) !important;
        color: #06b6d4 !important;
        border: 1px solid rgba(6,182,212,0.20) !important;
    }
    .badge-ai {
        display: inline-flex;
        align-items: center;
        padding: 0.1rem 0.4rem;
        border-radius: 9999px;
        background: rgba(6,182,212,0.12);
        color: #22d3ee;
        font-size: 0.6rem;
        font-weight: 700;
        border: 1px solid rgba(6,182,212,0.22);
        letter-spacing: 0.06em;
        text-transform: uppercase;
        line-height: 1.2;
    }
    </style>

    <script src="{{ asset('js/markdown-renderer.js') }}"></script>
    <script src="{{ asset('js/chat-widget.js') }}"></script>

    @stack('scripts')

    {{-- Global Feature Help Modal --}}
    @include('components.feature-help-modal')
</body>
</html>
