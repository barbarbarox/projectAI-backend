<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="RedSim — Platform Penilaian Keamanan Sistem Berbasis AI">
    <title>@yield('title', 'RedSim — Platform Keamanan AI')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/magic-bento.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0a0e1a] text-[#e2e8f0] antialiased" x-data="{ sidebarOpen: true }">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="fixed left-0 top-0 h-full w-64 bg-[#080c18] border-r border-[#1e2d4a] flex flex-col z-50 transition-transform duration-300"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

            {{-- Logo --}}
            <div class="px-6 py-5 border-b border-[#1e2d4a]">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#00d4ff] to-[#7c3aed] flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] bg-clip-text text-transparent">RedSim</h1>
                        <p class="text-[10px] text-[#64748b] tracking-wide uppercase">Keamanan AI</p>
                    </div>
                </a>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-[#00d4ff]/10 text-[#00d4ff] border border-[#00d4ff]/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Beranda</span>
                </a>
                <a href="{{ route('analisis.url') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('analisis.url') ? 'bg-[#00d4ff]/10 text-[#00d4ff] border border-[#00d4ff]/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Pemindai URL</span>
                </a>
                <a href="{{ route('analisis.log') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('analisis.log') ? 'bg-[#00d4ff]/10 text-[#00d4ff] border border-[#00d4ff]/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Analisis Log</span>
                </a>
                <a href="{{ route('analisis.zip') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('analisis.zip') ? 'bg-[#00d4ff]/10 text-[#00d4ff] border border-[#00d4ff]/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Pemindai ZIP</span>
                </a>
                <a href="{{ route('analisis.kode') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('analisis.kode') ? 'bg-[#00d4ff]/10 text-[#00d4ff] border border-[#00d4ff]/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    <span>Analisis Kode</span>
                </a>
                <a href="{{ route('edukasi.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('edukasi.*') && !request()->is('*/leaderboard') ? 'bg-[#00d4ff]/10 text-[#00d4ff] border border-[#00d4ff]/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Hub Edukasi</span>
                </a>
                <a href="/edukasi/leaderboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->is('*/leaderboard') ? 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    <span>Leaderboard</span>
                </a>
                <a href="{{ route('laporan.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('laporan.*') ? 'bg-[#00d4ff]/10 text-[#00d4ff] border border-[#00d4ff]/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Riwayat Scan</span>
                </a>
                <a href="{{ route('profile.ai') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('profile.ai') ? 'bg-[#00d4ff]/10 text-[#00d4ff] border border-[#00d4ff]/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Model AI</span>
                </a>
                @if(Auth::check() && Auth::user()->tier === 'admin')
                <div class="pt-4 mt-4 border-t border-[#1e2d4a]">
                    <p class="px-3 text-xs font-semibold text-[#64748b] uppercase tracking-wider mb-2">Administrator</p>
                    <a href="{{ route('admin.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('admin.index') ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span>Dashboard Admin</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.users.*') ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Manajemen Pengguna</span>
                    </a>
                    <a href="{{ route('admin.ai-config') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('admin.ai-config') ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Konfigurasi AI</span>
                    </a>
                    <a href="{{ route('admin.tantangan.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.tantangan.*') ? 'bg-[#1e2d4a]/50 text-[#00d4ff] font-medium' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/30 hover:text-white transition-colors' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Soal Edukasi</span>
                    </a>
                    <a href="{{ route('admin.kesehatan-sistem') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('admin.kesehatan-sistem') ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <span>Kesehatan Sistem</span>
                    </a>
                    <a href="{{ route('admin.audit-log') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('admin.audit-log') ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Audit Log</span>
                    </a>
                    <a href="{{ route('admin.chat-test') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('admin.chat-test') ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50 hover:text-[#e2e8f0]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span>Uji Coba AI & RAG</span>
                    </a>
                </div>
                @endif
            </nav>

            {{-- User + Logout --}}
            <div class="px-3 py-4 border-t border-[#1e2d4a]">
                @auth
                <div class="flex items-center gap-3 px-3 py-2 mb-2">
                    @if(Auth::user()->avatar)
                    <img src="{{ Auth::user()->avatar }}" class="w-8 h-8 rounded-full object-cover" alt="Avatar" referrerpolicy="no-referrer">
                    @else
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#00d4ff] to-[#7c3aed] flex items-center justify-center text-xs font-bold text-white">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-[#64748b] truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('profile.edit') }}" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-[#e2e8f0] bg-[#1e2d4a]/50 hover:bg-[#1e2d4a] transition-all duration-200 border border-[#1e2d4a]">
                        <svg class="w-5 h-5 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Edit Profil</span>
                    </a>
                    <form method="POST" action="{{ route('keluar') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-red-400 bg-red-900/20 hover:bg-red-900/40 transition-all duration-200 border border-red-500/10 hover:border-red-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar</span>
                    </button>
                    </form>
                </div>
                @else
                <div class="flex flex-col gap-2">
                    <a href="{{ route('masuk') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm text-[#00d4ff] bg-[#00d4ff]/10 hover:bg-[#00d4ff]/20 transition-all duration-200 border border-[#00d4ff]/20">
                        Masuk
                    </a>
                    <a href="{{ route('daftar') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm text-white bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] hover:shadow-lg transition-all duration-200">
                        Daftar Gratis
                    </a>
                </div>
                @endauth
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 ml-64 min-h-screen">
            {{-- Top Bar --}}
            <header class="sticky top-0 z-40 bg-[#0a0e1a]/80 backdrop-blur-xl border-b border-[#1e2d4a] px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">@yield('header', 'Dashboard')</h2>
                        <p class="text-xs text-[#64748b] mt-0.5">@yield('subheader', '')</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @auth
                        <div class="text-xs text-[#64748b] bg-[#0f1629] px-3 py-1.5 rounded-lg border border-[#1e2d4a]">
                            Scan hari ini: <span class="text-[#00d4ff] font-semibold">{{ Auth::user()->scan_count_today }}/{{ env('MAX_SCANS_PER_DAY_FREE', 10) }}</span>
                        </div>
                        @else
                        <a href="{{ route('masuk') }}" class="text-xs text-[#00d4ff] hover:underline">Masuk untuk Analisis</a>
                        @endauth
                    </div>
                </div>
            </header>

            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button @click="show = false" class="text-green-400/50 hover:text-green-400">&times;</button>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-center justify-between">
                    <span>{{ session('error') }}</span>
                    <button @click="show = false" class="text-red-400/50 hover:text-red-400">&times;</button>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
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
    /* ===== Markdown Content Styling for Widget ===== */
    .chat-md-content { color: #e2e8f0; word-wrap: break-word; overflow-wrap: break-word; }
    .chat-md-content > *:first-child { margin-top: 0; }
    .chat-md-content > *:last-child { margin-bottom: 0; }
    .chat-md-content p { margin-bottom: 0.5rem; line-height: 1.5; }
    .chat-md-content strong { font-weight: 700; color: #fff; }
    .chat-md-content em { font-style: italic; color: #cbd5e1; }
    .chat-md-content h1, .chat-md-content h2, .chat-md-content h3, .chat-md-content h4 { font-weight: 700; margin: 0.75rem 0 0.5rem; color: #fff; }
    .chat-md-content ul { list-style: disc; padding-left: 1.25rem; margin: 0.5rem 0; }
    .chat-md-content ol { list-style: decimal; padding-left: 1.25rem; margin: 0.5rem 0; }
    .chat-md-content a { color: #00d4ff; text-decoration: underline; }
    .chat-md-content img { max-width: 100%; border-radius: 0.5rem; margin: 0.5rem 0; }
    .chat-md-content blockquote { border-left: 2px solid #00d4ff; margin: 0.5rem 0; padding: 0.5rem 0.75rem; background: rgba(0,212,255,0.05); }
    .chat-md-content code:not(pre code) { background: #1e293b; padding: 0.15em 0.3em; border-radius: 3px; font-family: monospace; font-size: 0.9em; color: #7dd3fc; }
    .chat-md-content pre { background: #0d1117; margin: 0.5rem 0; padding: 0.75rem; overflow-x: auto; border-radius: 0.5rem; }
    .md-code-block { margin: 0.5rem 0; border-radius: 0.5rem; overflow: hidden; border: 1px solid #30363d; background: #0d1117; }
    .md-code-header { display: flex; align-items: center; justify-content: space-between; background: #161b22; padding: 0.25rem 0.5rem; border-bottom: 1px solid #30363d; }
    .md-code-lang { font-size: 0.6rem; color: #8b949e; text-transform: uppercase; }
    .md-copy-btn { display: flex; align-items: center; gap: 0.25rem; font-size: 0.6rem; color: #8b949e; background: none; border: 1px solid #30363d; border-radius: 0.25rem; padding: 0.15rem 0.4rem; cursor: pointer; }
    .md-table-wrap { overflow-x: auto; margin: 0.5rem 0; }
    .chat-md-content table { width: 100%; border-collapse: collapse; font-size: 0.75rem; }
    .chat-md-content th { padding: 0.4rem 0.5rem; background: #161b22; color: #e6edf3; text-align: left; border-bottom: 1px solid #30363d; }
    .chat-md-content td { padding: 0.4rem 0.5rem; color: #c9d1d9; border-bottom: 1px solid #21262d; }
    </style>
    
    <script src="{{ asset('js/markdown-renderer.js') }}"></script>
    <script src="{{ asset('js/chat-widget.js') }}"></script>

    @stack('scripts')
</body>
</html>
