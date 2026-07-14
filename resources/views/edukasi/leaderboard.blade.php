@extends('layouts.app')
@section('title', 'Papan Peringkat — RedSim')
@section('header', 'Papan Peringkat')
@section('subheader', 'Peringkat pengguna berdasarkan poin tantangan keamanan')

@section('content')
<div class="space-y-8">
    {{-- Podium Top 5 --}}
    <div class="rounded-2xl bg-gradient-to-br from-[#0f1629] to-[#1a1040] border border-[#1e2d4a] p-8 overflow-hidden relative">
        <div class="absolute top-0 left-0 w-full h-full opacity-5">
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-yellow-500 rounded-full blur-[120px]"></div>
            <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-[#00d4ff] rounded-full blur-[100px]"></div>
        </div>

        <h3 class="text-center text-lg font-bold text-white mb-8 relative z-10">🏆 Top 5 — Hall of Fame</h3>

        @if($leaders->count() >= 3)
        {{-- Desktop Podium --}}
        <div class="hidden md:flex items-end justify-center gap-4 relative z-10 mb-6" style="min-height: 320px;">
            {{-- #2 —  Silver --}}
            @if($leaders->count() >= 2)
            <div class="flex flex-col items-center w-40">
                <div class="relative mb-3">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-gray-300 to-gray-500 flex items-center justify-center text-2xl font-black text-white shadow-lg shadow-gray-500/30 ring-4 ring-gray-400/30">
                        @if($leaders[1]->avatar)
                        <img src="{{ $leaders[1]->avatar }}" class="w-full h-full rounded-full object-cover" referrerpolicy="no-referrer">
                        @else
                        {{ strtoupper(substr($leaders[1]->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-gradient-to-br from-gray-300 to-gray-500 flex items-center justify-center text-xs font-black text-white shadow-md">2</div>
                </div>
                <p class="text-sm font-semibold text-white text-center truncate w-full">{{ $leaders[1]->nama_lengkap ?? $leaders[1]->name }}</p>
                <p class="text-xs text-yellow-400 font-bold mt-0.5">{{ $leaders[1]->total_poin }} poin</p>
                <div class="w-full mt-3 rounded-t-xl bg-gradient-to-t from-gray-600/40 to-gray-500/20 border border-gray-500/20 border-b-0" style="height: 120px;"></div>
            </div>
            @endif

            {{-- #1 — Gold --}}
            <div class="flex flex-col items-center w-44">
                <div class="relative mb-3">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-yellow-400 to-amber-600 flex items-center justify-center text-3xl font-black text-white shadow-lg shadow-yellow-500/40 ring-4 ring-yellow-400/40 animate-pulse-slow">
                        @if($leaders[0]->avatar)
                        <img src="{{ $leaders[0]->avatar }}" class="w-full h-full rounded-full object-cover" referrerpolicy="no-referrer">
                        @else
                        {{ strtoupper(substr($leaders[0]->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="absolute -top-3 -right-1 text-2xl">👑</div>
                    <div class="absolute -top-2 -left-2 w-7 h-7 rounded-full bg-gradient-to-br from-yellow-400 to-amber-600 flex items-center justify-center text-xs font-black text-white shadow-md">1</div>
                </div>
                <p class="text-base font-bold text-white text-center truncate w-full">{{ $leaders[0]->nama_lengkap ?? $leaders[0]->name }}</p>
                <p class="text-sm text-yellow-400 font-bold mt-0.5">{{ $leaders[0]->total_poin }} poin</p>
                <div class="w-full mt-3 rounded-t-xl bg-gradient-to-t from-yellow-600/30 to-yellow-500/10 border border-yellow-500/20 border-b-0" style="height: 160px;"></div>
            </div>

            {{-- #3 — Bronze --}}
            @if($leaders->count() >= 3)
            <div class="flex flex-col items-center w-40">
                <div class="relative mb-3">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-600 to-amber-800 flex items-center justify-center text-2xl font-black text-white shadow-lg shadow-amber-700/30 ring-4 ring-amber-600/30">
                        @if($leaders[2]->avatar)
                        <img src="{{ $leaders[2]->avatar }}" class="w-full h-full rounded-full object-cover" referrerpolicy="no-referrer">
                        @else
                        {{ strtoupper(substr($leaders[2]->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-gradient-to-br from-amber-600 to-amber-800 flex items-center justify-center text-xs font-black text-white shadow-md">3</div>
                </div>
                <p class="text-sm font-semibold text-white text-center truncate w-full">{{ $leaders[2]->nama_lengkap ?? $leaders[2]->name }}</p>
                <p class="text-xs text-yellow-400 font-bold mt-0.5">{{ $leaders[2]->total_poin }} poin</p>
                <div class="w-full mt-3 rounded-t-xl bg-gradient-to-t from-amber-800/40 to-amber-700/15 border border-amber-700/20 border-b-0" style="height: 90px;"></div>
            </div>
            @endif
        </div>

        {{-- #4 & #5 Cards --}}
        @if($leaders->count() >= 4)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4 relative z-10">
            @foreach($leaders->slice(3, 2) as $idx => $leader)
            <div class="flex items-center gap-4 px-5 py-4 rounded-xl bg-[#0a0e1a]/60 border border-[#1e2d4a] backdrop-blur-sm hover:border-[#00d4ff]/20 transition-all">
                <div class="w-10 h-10 rounded-full bg-[#1e2d4a] flex items-center justify-center text-sm font-bold text-[#64748b] flex-shrink-0">
                    {{ $idx + 4 }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ $leader->nama_lengkap ?? $leader->name }}</p>
                </div>
                <span class="text-sm font-bold text-yellow-400 flex-shrink-0">{{ $leader->total_poin }} poin</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Mobile Podium (simplified list) --}}
        <div class="md:hidden space-y-2 relative z-10">
            @foreach($leaders->take(5) as $idx => $leader)
            @php
                $colors = ['from-yellow-400 to-amber-600', 'from-gray-300 to-gray-500', 'from-amber-600 to-amber-800', 'from-[#1e2d4a] to-[#1e2d4a]', 'from-[#1e2d4a] to-[#1e2d4a]'];
                $medals = ['👑', '🥈', '🥉', '', ''];
            @endphp
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl {{ $idx < 3 ? 'bg-[#0a0e1a]/80 border border-' . ($idx === 0 ? 'yellow-500/30' : ($idx === 1 ? 'gray-400/30' : 'amber-600/30')) : 'bg-[#0a0e1a]/40' }}">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br {{ $colors[$idx] ?? 'from-[#1e2d4a] to-[#1e2d4a]' }} flex items-center justify-center text-sm font-bold text-white flex-shrink-0">
                    {{ $idx + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ $medals[$idx] ?? '' }} {{ $leader->nama_lengkap ?? $leader->name }}</p>
                </div>
                <span class="text-xs font-bold text-yellow-400 flex-shrink-0">{{ $leader->total_poin }} poin</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center text-[#94a3b8] py-12 relative z-10">
            <p class="text-4xl mb-3">🏆</p>
            <p>Belum ada cukup peserta. Jadilah yang pertama menyelesaikan tantangan!</p>
        </div>
        @endif
    </div>

    {{-- Full Ranking Table --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#1e2d4a] flex items-center justify-between">
            <h4 class="font-semibold text-white">Peringkat Lengkap</h4>
            <span class="text-xs text-[#64748b]">{{ $leaders->count() }} pengguna</span>
        </div>
        <div class="divide-y divide-[#1e2d4a]">
            @forelse($leaders as $idx => $leader)
            <div class="flex items-center justify-between px-6 py-3.5 hover:bg-[#1e2d4a]/20 transition-colors {{ Auth::check() && $leader->id === Auth::id() ? 'bg-[#00d4ff]/5 border-l-2 border-l-[#00d4ff]' : '' }}">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0
                        {{ $idx === 0 ? 'bg-gradient-to-br from-yellow-400 to-amber-600 text-white' : '' }}
                        {{ $idx === 1 ? 'bg-gradient-to-br from-gray-300 to-gray-500 text-white' : '' }}
                        {{ $idx === 2 ? 'bg-gradient-to-br from-amber-600 to-amber-800 text-white' : '' }}
                        {{ $idx > 2 ? 'bg-[#1e2d4a] text-[#64748b]' : '' }}">
                        {{ $idx + 1 }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium truncate {{ Auth::check() && $leader->id === Auth::id() ? 'text-[#00d4ff]' : 'text-white' }}">
                            {{ $leader->nama_lengkap ?? $leader->name }}
                            @if(Auth::check() && $leader->id === Auth::id())
                            <span class="text-[10px] text-[#00d4ff] ml-1">(Anda)</span>
                            @endif
                        </p>
                    </div>
                </div>
                <span class="text-sm font-bold text-yellow-400">{{ $leader->total_poin }} poin</span>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-[#94a3b8]">Belum ada data peringkat.</div>
            @endforelse
        </div>
    </div>

    <div class="text-center">
        @auth
        <a href="/edukasi/tantangan" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all hover:-translate-y-0.5">
            🎯 Ikuti Tantangan Sekarang
        </a>
        @else
        <a href="{{ route('masuk') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all hover:-translate-y-0.5">
            🔐 Masuk untuk Ikut Tantangan
        </a>
        @endauth
    </div>
</div>

<style>
@keyframes pulse-slow { 0%, 100% { box-shadow: 0 0 0 0 rgba(250, 204, 21, 0.4); } 50% { box-shadow: 0 0 20px 5px rgba(250, 204, 21, 0.1); } }
.animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
</style>
@endsection
