@extends('layouts.app')
@section('title', 'Hub Edukasi — RedSim')
@section('header', 'Hub Edukasi')
@section('subheader', 'Pelajari keamanan siber melalui tantangan dan ensiklopedia')

@section('content')
<div class="grid md:grid-cols-3 gap-6">
    <a href="/edukasi/tantangan" class="group p-6 rounded-2xl bg-[#0f1629] border border-[#1e2d4a] hover:border-[#00d4ff]/30 transition-all duration-300 hover:-translate-y-1">
        <div class="w-12 h-12 rounded-xl bg-[#00d4ff]/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <span class="text-2xl">🏆</span>
        </div>
        <h3 class="text-lg font-semibold mb-2">Tantangan Keamanan</h3>
        <p class="text-sm text-[#94a3b8]">Temukan bug dan perbaiki kode yang rentan. Dapatkan poin dan naik peringkat!</p>
    </a>
    <a href="/edukasi/ensiklopedia" class="group p-6 rounded-2xl bg-[#0f1629] border border-[#1e2d4a] hover:border-[#7c3aed]/30 transition-all duration-300 hover:-translate-y-1">
        <div class="w-12 h-12 rounded-xl bg-[#7c3aed]/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <span class="text-2xl">📖</span>
        </div>
        <h3 class="text-lg font-semibold mb-2">Ensiklopedia</h3>
        <p class="text-sm text-[#94a3b8]">Database lengkap kerentanan, teknik serangan, dan panduan keamanan.</p>
    </a>
    <a href="/edukasi/leaderboard" class="group p-6 rounded-2xl bg-[#0f1629] border border-[#1e2d4a] hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-1">
        <div class="w-12 h-12 rounded-xl bg-yellow-500/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
            <span class="text-2xl">🥇</span>
        </div>
        <h3 class="text-lg font-semibold mb-2">Papan Peringkat</h3>
        <p class="text-sm text-[#94a3b8]">Lihat peringkat pengguna berdasarkan poin tantangan keamanan.</p>
    </a>
</div>
@endsection
