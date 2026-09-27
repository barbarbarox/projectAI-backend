@extends('layouts.app')
@section('title', 'Hub Edukasi — RedSim')
@section('header', 'Hub Edukasi')
@section('subheader', 'Pelajari keamanan siber melalui tantangan interaktif dan ensiklopedia')

@section('content')
<div class="grid md:grid-cols-3 gap-5">

    {{-- Tantangan Keamanan --}}
    <a href="/edukasi/tantangan"
       class="scan-module-card rs-card rs-card-security rounded-xl p-6 flex flex-col gap-4 group cursor-pointer">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
             style="background:rgba(220,38,38,0.12);">
            <span class="text-2xl select-none">🏆</span>
        </div>
        <div>
            <div class="flex items-center gap-2 mb-2">
                <h3 class="text-base font-semibold text-white">Tantangan Keamanan</h3>
                <span class="badge-security text-[9px]">CTF</span>
            </div>
            <p class="text-sm leading-relaxed" style="color:#94a3b8;">
                Temukan bug dan perbaiki kode yang rentan. Dapatkan poin dan naik peringkat!
            </p>
        </div>
        <div class="flex items-center gap-1 text-xs font-medium mt-auto" style="color:#ef4444;">
            Mulai Tantangan
            <svg class="w-3 h-3 ml-0.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>

    {{-- Ensiklopedia --}}
    <a href="/edukasi/ensiklopedia"
       class="scan-module-card ai-module rs-card rs-card-ai rounded-xl p-6 flex flex-col gap-4 group cursor-pointer">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
             style="background:rgba(6,182,212,0.12);">
            <span class="text-2xl select-none">📖</span>
        </div>
        <div>
            <div class="flex items-center gap-2 mb-2">
                <h3 class="text-base font-semibold text-white">Ensiklopedia</h3>
                <span class="badge-ai text-[9px]">AI</span>
            </div>
            <p class="text-sm leading-relaxed" style="color:#94a3b8;">
                Database lengkap kerentanan, teknik serangan, dan panduan keamanan siber.
            </p>
        </div>
        <div class="flex items-center gap-1 text-xs font-medium mt-auto" style="color:#22d3ee;">
            Jelajahi
            <svg class="w-3 h-3 ml-0.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>

    {{-- Leaderboard --}}
    <a href="/edukasi/leaderboard"
       class="scan-module-card rs-card rounded-xl p-6 flex flex-col gap-4 group cursor-pointer"
       style="transition:all 300ms ease;"
       onmouseenter="this.style.boxShadow='0 0 18px rgba(234,179,8,0.18)';this.style.borderColor='rgba(234,179,8,0.3)'"
       onmouseleave="this.style.boxShadow='';this.style.borderColor=''">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
             style="background:rgba(234,179,8,0.10);">
            <span class="text-2xl select-none">🥇</span>
        </div>
        <div>
            <div class="flex items-center gap-2 mb-2">
                <h3 class="text-base font-semibold text-white">Papan Peringkat</h3>
                <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold uppercase"
                      style="background:rgba(234,179,8,0.10);color:#fbbf24;border:1px solid rgba(234,179,8,0.2);">Ranking</span>
            </div>
            <p class="text-sm leading-relaxed" style="color:#94a3b8;">
                Lihat peringkat pengguna berdasarkan poin tantangan keamanan.
            </p>
        </div>
        <div class="flex items-center gap-1 text-xs font-medium mt-auto text-yellow-400">
            Lihat Peringkat
            <svg class="w-3 h-3 ml-0.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>
</div>
@endsection
