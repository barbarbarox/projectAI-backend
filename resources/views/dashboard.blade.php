@extends('layouts.app')

@section('title', 'Dashboard — RedSim')
@section('header', 'Dashboard')
@section('subheader', 'Selamat datang kembali, ' . Auth::user()->name)

@section('content')
<div class="space-y-6">
    {{-- Stats Cards --}}
    <div class="bento-section grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="magic-bento-card p-5 rounded-2xl bg-[#0f1629] border border-[#1e2d4a] transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-[#00d4ff]/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <span class="text-xs text-[#64748b]">Total</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ $stats['total_scan'] }}</p>
            <p class="text-xs text-[#64748b] mt-1">Total Pemindaian</p>
        </div>
        <div class="magic-bento-card p-5 rounded-2xl bg-[#0f1629] border border-[#1e2d4a] transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs text-[#64748b]">Selesai</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ $stats['scan_selesai'] }}</p>
            <p class="text-xs text-[#64748b] mt-1">Scan Selesai</p>
        </div>
        <div class="magic-bento-card p-5 rounded-2xl bg-[#0f1629] border border-[#1e2d4a] transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-[#7c3aed]/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#7c3aed]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="text-xs text-[#64748b]">Hari Ini</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ $stats['scan_hari_ini'] }}<span class="text-sm font-normal text-[#64748b]">/{{ $stats['batas_harian'] }}</span></p>
            <p class="text-xs text-[#64748b] mt-1">Scan Hari Ini</p>
        </div>
        <div class="magic-bento-card p-5 rounded-2xl bg-[#0f1629] border border-[#1e2d4a] transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-yellow-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <span class="text-xs text-[#64748b]">Poin</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ $stats['total_poin'] }}</p>
            <p class="text-xs text-[#64748b] mt-1">Poin Edukasi</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bento-section grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('analisis.kode') }}" class="magic-bento-card group p-5 rounded-2xl bg-gradient-to-br from-[#00d4ff]/5 to-[#00d4ff]/10 border border-[#00d4ff]/20 flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-[#00d4ff]/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <h3 class="font-semibold text-white">Analisis Kode</h3>
            </div>
            <p class="text-xs text-[#94a3b8]">Tempel kode untuk dianalisis</p>
        </a>
        <a href="{{ route('analisis.url') }}" class="magic-bento-card group p-5 rounded-2xl bg-gradient-to-br from-[#7c3aed]/5 to-[#7c3aed]/10 border border-[#7c3aed]/20 flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-[#7c3aed]/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-[#7c3aed]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                </div>
                <h3 class="font-semibold text-white">Pemindai URL</h3>
            </div>
            <p class="text-xs text-[#94a3b8]">Pindai keamanan website</p>
        </a>
        <a href="{{ route('analisis.zip') }}" class="magic-bento-card group p-5 rounded-2xl bg-gradient-to-br from-green-500/5 to-green-500/10 border border-green-500/20 flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="font-semibold text-white">Analisis ZIP</h3>
            </div>
            <p class="text-xs text-[#94a3b8]">Unggah proyek ZIP</p>
        </a>
        <a href="/edukasi/leaderboard" class="magic-bento-card group p-5 rounded-2xl bg-gradient-to-br from-yellow-500/5 to-yellow-500/10 border border-yellow-500/20 flex flex-col justify-between">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-yellow-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <h3 class="font-semibold text-white">Leaderboard</h3>
            </div>
            <p class="text-xs text-[#94a3b8]">Lihat peringkat & poin Anda</p>
        </a>
    </div>

    {{-- Recent Scans --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#1e2d4a] flex items-center justify-between">
            <h3 class="font-semibold">Pemindaian Terakhir</h3>
            <a href="{{ route('laporan.index') }}" class="text-xs text-[#00d4ff] hover:underline">Lihat Semua →</a>
        </div>
        <div class="divide-y divide-[#1e2d4a]">
            @forelse($scans as $scan)
            <a href="{{ $scan->status === 'selesai' ? route('laporan.detail', $scan) : '#' }}" class="flex items-center justify-between px-6 py-4 hover:bg-[#1e2d4a]/30 transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $scan->tipe_scan === 'kode' ? 'bg-[#00d4ff]/10' : ($scan->tipe_scan === 'url' ? 'bg-[#7c3aed]/10' : 'bg-green-500/10') }}">
                        @if($scan->tipe_scan === 'kode')
                        <svg class="w-5 h-5 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        @elseif($scan->tipe_scan === 'url')
                        <svg class="w-5 h-5 text-[#7c3aed]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3"/></svg>
                        @else
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4"/></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium">{{ Str::limit($scan->target ?? $scan->nama_file ?? 'Analisis ' . $scan->tipe_scan, 50) }}</p>
                        <p class="text-xs text-[#64748b]">{{ $scan->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($scan->skor_keamanan !== null)
                    <span class="text-sm font-bold {{ $scan->skor_keamanan >= 70 ? 'text-green-400' : ($scan->skor_keamanan >= 50 ? 'text-yellow-400' : 'text-red-400') }}">{{ $scan->skor_keamanan }}/100</span>
                    @endif
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $scan->status === 'selesai' ? 'bg-green-500/10 text-green-400' : ($scan->status === 'gagal' ? 'bg-red-500/10 text-red-400' : 'bg-yellow-500/10 text-yellow-400') }}">
                        {{ $scan->status_label }}
                    </span>
                </div>
            </a>
            @empty
            <div class="px-6 py-12 text-center">
                <svg class="w-12 h-12 text-[#1e2d4a] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="text-[#64748b] text-sm">Belum ada pemindaian. Mulai analisis pertama Anda!</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
