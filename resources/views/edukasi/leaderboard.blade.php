@extends('layouts.app')
@section('title', 'Papan Peringkat — RedSim')
@section('header', 'Papan Peringkat')
@section('subheader', 'Peringkat pengguna berdasarkan poin tantangan keamanan')

@section('content')
<div class="space-y-6">

    {{-- ══ Podium Top 5 ══ --}}
    <div class="rs-card rounded-xl p-8 overflow-hidden relative">
        {{-- Ambient glow --}}
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full opacity-[0.04]"
                 style="background:#ef4444;filter:blur(80px);"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 rounded-full opacity-[0.04]"
                 style="background:#06b6d4;filter:blur(70px);"></div>
        </div>

        <div class="flex items-center justify-center gap-3 mb-8 relative z-10">
            <span class="text-2xl">🏆</span>
            <h3 class="text-lg font-bold text-white">Top 5 — Hall of Fame</h3>
        </div>

        @if($leaders->count() >= 3)
        {{-- Desktop Podium --}}
        <div class="hidden md:flex items-end justify-center gap-6 relative z-10 mb-6" style="min-height:300px;">

            {{-- #2 Silver --}}
            @if($leaders->count() >= 2)
            <div class="flex flex-col items-center w-40">
                <div class="relative mb-3">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-black text-white shadow-lg ring-4"
                         style="background:linear-gradient(135deg,#d1d5db,#6b7280);ring-color:rgba(156,163,175,0.3);">
                        @if($leaders[1]->avatar)
                        <img src="{{ $leaders[1]->avatar }}" class="w-full h-full rounded-full object-cover" referrerpolicy="no-referrer">
                        @else
                        {{ strtoupper(substr($leaders[1]->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full flex items-center justify-center text-xs font-black text-white shadow"
                         style="background:linear-gradient(135deg,#d1d5db,#6b7280);">2</div>
                </div>
                <p class="text-sm font-semibold text-white text-center truncate w-full">{{ $leaders[1]->nama_lengkap ?? $leaders[1]->name }}</p>
                <p class="text-xs text-yellow-400 font-bold mt-0.5">{{ $leaders[1]->total_poin }} poin</p>
                <div class="w-full mt-3 rounded-t-xl" style="height:110px;background:linear-gradient(to top,rgba(107,114,128,.3),rgba(156,163,175,.08));border:1px solid rgba(156,163,175,.15);border-bottom:none;"></div>
            </div>
            @endif

            {{-- #1 Gold --}}
            <div class="flex flex-col items-center w-44">
                <div class="relative mb-3">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center text-2xl font-black text-white shadow-xl ring-4 animate-pulse-slow"
                         style="background:linear-gradient(135deg,#fbbf24,#d97706);box-shadow:0 0 25px rgba(251,191,36,.35);ring-color:rgba(251,191,36,.3);">
                        @if($leaders[0]->avatar)
                        <img src="{{ $leaders[0]->avatar }}" class="w-full h-full rounded-full object-cover" referrerpolicy="no-referrer">
                        @else
                        {{ strtoupper(substr($leaders[0]->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 text-2xl select-none">👑</div>
                    <div class="absolute -top-1 -left-2 w-6 h-6 rounded-full flex items-center justify-center text-xs font-black text-white shadow"
                         style="background:linear-gradient(135deg,#fbbf24,#d97706);">1</div>
                </div>
                <p class="text-base font-bold text-white text-center truncate w-full">{{ $leaders[0]->nama_lengkap ?? $leaders[0]->name }}</p>
                <p class="text-sm text-yellow-400 font-bold mt-0.5">{{ $leaders[0]->total_poin }} poin</p>
                <div class="w-full mt-3 rounded-t-xl" style="height:155px;background:linear-gradient(to top,rgba(217,119,6,.25),rgba(251,191,36,.05));border:1px solid rgba(251,191,36,.2);border-bottom:none;"></div>
            </div>

            {{-- #3 Bronze --}}
            @if($leaders->count() >= 3)
            <div class="flex flex-col items-center w-40">
                <div class="relative mb-3">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-black text-white shadow-lg ring-4"
                         style="background:linear-gradient(135deg,#b45309,#92400e);ring-color:rgba(180,83,9,.25);">
                        @if($leaders[2]->avatar)
                        <img src="{{ $leaders[2]->avatar }}" class="w-full h-full rounded-full object-cover" referrerpolicy="no-referrer">
                        @else
                        {{ strtoupper(substr($leaders[2]->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full flex items-center justify-center text-xs font-black text-white shadow"
                         style="background:linear-gradient(135deg,#b45309,#92400e);">3</div>
                </div>
                <p class="text-sm font-semibold text-white text-center truncate w-full">{{ $leaders[2]->nama_lengkap ?? $leaders[2]->name }}</p>
                <p class="text-xs text-yellow-400 font-bold mt-0.5">{{ $leaders[2]->total_poin }} poin</p>
                <div class="w-full mt-3 rounded-t-xl" style="height:82px;background:linear-gradient(to top,rgba(146,64,14,.3),rgba(180,83,9,.06));border:1px solid rgba(180,83,9,.15);border-bottom:none;"></div>
            </div>
            @endif
        </div>

        {{-- #4 & #5 Cards --}}
        @if($leaders->count() >= 4)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4 relative z-10">
            @foreach($leaders->slice(3, 2) as $idx => $leader)
            <div class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all duration-200 hover:shadow-[0_0_10px_rgba(220,38,38,0.12)]"
                 style="background:rgba(2,6,9,0.6);border:1px solid #1e293b;backdrop-filter:blur(8px);">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
                     style="background:#0f172a;color:#64748b;border:1px solid #1e293b;">
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

        {{-- Mobile Podium --}}
        <div class="md:hidden space-y-2 relative z-10">
            @foreach($leaders->take(5) as $idx => $leader)
            @php
                $podiumColors = [
                    'background:linear-gradient(135deg,#fbbf24,#d97706);',
                    'background:linear-gradient(135deg,#d1d5db,#6b7280);',
                    'background:linear-gradient(135deg,#b45309,#92400e);',
                    'background:#0f172a;color:#64748b;',
                    'background:#0f172a;color:#64748b;',
                ];
                $medals = ['👑', '🥈', '🥉', '', ''];
            @endphp
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 hover:bg-slate-800/40"
                 style="{{ $idx < 3
                     ? 'background:rgba(2,6,9,0.7);border:1px solid ' . ($idx === 0 ? 'rgba(251,191,36,.2)' : ($idx === 1 ? 'rgba(156,163,175,.15)' : 'rgba(180,83,9,.15)'))
                     : 'background:transparent;' }}">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                     style="{{ $podiumColors[$idx] ?? 'background:#0f172a;color:#64748b;' }}">
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
        <div class="text-center py-14 relative z-10">
            <p class="text-4xl mb-3">🏆</p>
            <p class="text-sm" style="color:#94a3b8;">Belum ada cukup peserta. Jadilah yang pertama menyelesaikan tantangan!</p>
        </div>
        @endif
    </div>

    {{-- ══ Full Ranking Table ══ --}}
    <div class="rs-card rounded-xl overflow-hidden">
        <div class="px-6 py-4 flex items-center justify-between" style="border-bottom:1px solid #1e293b;">
            <div class="flex items-center gap-3">
                <h4 class="font-semibold text-white">Peringkat Lengkap</h4>
                <span class="badge-security">{{ $leaders->count() }} pengguna</span>
            </div>
        </div>

        <table class="rs-table">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th>Pengguna</th>
                    <th class="text-right">Poin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaders as $idx => $leader)
                <tr class="{{ Auth::check() && $leader->id === Auth::id() ? 'border-l-2' : '' }}"
                    style="{{ Auth::check() && $leader->id === Auth::id()
                        ? 'border-left-color:#06b6d4;background:rgba(6,182,212,0.04);'
                        : '' }}">
                    <td>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold"
                             style="{{ $idx === 0
                                 ? 'background:linear-gradient(135deg,#fbbf24,#d97706);color:#fff;'
                                 : ($idx === 1
                                     ? 'background:linear-gradient(135deg,#d1d5db,#6b7280);color:#fff;'
                                     : ($idx === 2
                                         ? 'background:linear-gradient(135deg,#b45309,#92400e);color:#fff;'
                                         : 'background:#0f172a;color:#64748b;border:1px solid #1e293b;')) }}">
                            {{ $idx + 1 }}
                        </div>
                    </td>
                    <td>
                        <p class="text-sm font-medium truncate {{ Auth::check() && $leader->id === Auth::id() ? 'text-cyan-400' : 'text-white' }}">
                            {{ $leader->nama_lengkap ?? $leader->name }}
                            @if(Auth::check() && $leader->id === Auth::id())
                            <span class="ml-1 badge-ai text-[9px]">Anda</span>
                            @endif
                        </p>
                    </td>
                    <td class="text-right">
                        <span class="text-sm font-bold text-yellow-400">{{ $leader->total_poin }}</span>
                        <span class="text-xs ml-1" style="color:#64748b;">poin</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-10 text-center text-sm" style="color:#64748b;">Belum ada data peringkat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- CTA --}}
    <div class="text-center">
        @auth
        <a href="/edukasi/tantangan"
           class="btn-security inline-flex"
           style="padding:0.75rem 1.75rem;font-size:0.9375rem;">
            🎯 Ikuti Tantangan Sekarang
        </a>
        @else
        <a href="{{ route('masuk') }}"
           class="btn-security inline-flex"
           style="padding:0.75rem 1.75rem;font-size:0.9375rem;">
            🔐 Masuk untuk Ikut Tantangan
        </a>
        @endauth
    </div>
</div>

<style>
@keyframes pulse-slow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(251,204,21,0.4); }
    50%       { box-shadow: 0 0 20px 5px rgba(251,204,21,0.12); }
}
.animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
</style>
@endsection
