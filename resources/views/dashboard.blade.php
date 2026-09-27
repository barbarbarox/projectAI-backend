@extends('layouts.app')

@section('title', 'Dashboard — RedSim')
@section('header', 'Dashboard')
@section('subheader', 'Selamat datang kembali, ' . Auth::user()->name)

@section('content')
<div class="space-y-6">

    {{-- ══ Stats Cards ══ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Total Scan --}}
        <div class="rs-card rs-card-security p-5 rounded-xl group transition-all duration-300 hover:-translate-y-0.5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
                     style="background:rgba(220,38,38,0.12);">
                    <svg class="w-5 h-5" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="text-[10px] uppercase tracking-wider font-semibold" style="color:#64748b;">Total</span>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $stats['total_scan'] }}</p>
            <p class="text-xs" style="color:#64748b;">Total Pemindaian</p>
        </div>

        {{-- Scan Selesai --}}
        <div class="rs-card p-5 rounded-xl group transition-all duration-300 hover:-translate-y-0.5"
             style="transition:all 300ms ease;">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
                     style="background:rgba(34,197,94,0.10);">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-[10px] uppercase tracking-wider font-semibold" style="color:#64748b;">Selesai</span>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $stats['scan_selesai'] }}</p>
            <p class="text-xs" style="color:#64748b;">Scan Selesai</p>
        </div>

        {{-- Scan Hari Ini --}}
        <div class="rs-card rs-card-ai p-5 rounded-xl group transition-all duration-300 hover:-translate-y-0.5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
                     style="background:rgba(6,182,212,0.10);">
                    <svg class="w-5 h-5" style="color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-[10px] uppercase tracking-wider font-semibold" style="color:#64748b;">Hari Ini</span>
            </div>
            <p class="text-3xl font-bold text-white mb-1">
                {{ $stats['scan_hari_ini'] }}
                <span class="text-sm font-normal" style="color:#64748b;">/{{ $stats['batas_harian'] }}</span>
            </p>
            <p class="text-xs" style="color:#64748b;">Scan Hari Ini</p>
            {{-- Progress bar --}}
            <div class="mt-3 h-1 rounded-full overflow-hidden" style="background:#1e293b;">
                @php $pct = $stats['batas_harian'] > 0 ? min(100, round($stats['scan_hari_ini'] / $stats['batas_harian'] * 100)) : 0; @endphp
                <div class="h-full rounded-full transition-all duration-500"
                     style="width:{{ $pct }}%;background:linear-gradient(90deg,#06b6d4,#3b82f6);"></div>
            </div>
        </div>

        {{-- Poin Edukasi --}}
        <div class="rs-card p-5 rounded-xl group transition-all duration-300 hover:-translate-y-0.5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
                     style="background:rgba(234,179,8,0.10);">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <span class="text-[10px] uppercase tracking-wider font-semibold" style="color:#64748b;">Poin</span>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $stats['total_poin'] }}</p>
            <p class="text-xs" style="color:#64748b;">Poin Edukasi</p>
        </div>
    </div>

    {{-- ══ Quick Action Modules ══ --}}
    <div>
        <h3 class="text-sm font-semibold uppercase tracking-wider mb-3" style="color:#64748b;">Modul Analisis</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Analisis Kode --}}
            <a href="{{ route('analisis.kode') }}"
               class="scan-module-card group rs-card rs-card-security rounded-xl p-5 flex flex-col gap-3 cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
                         style="background:rgba(220,38,38,0.12);">
                        <svg class="w-5 h-5" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white text-sm">Analisis Kode</h3>
                        <span class="badge-security text-[9px]">Security</span>
                    </div>
                </div>
                <p class="text-xs leading-relaxed" style="color:#64748b;">Tempel kode untuk dianalisis celah keamanannya menggunakan AI.</p>
                <div class="flex items-center gap-1 text-xs font-medium mt-auto" style="color:#ef4444;">
                    Mulai Scan <svg class="w-3 h-3 ml-0.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            {{-- Pemindai URL --}}
            <a href="{{ route('analisis.url') }}"
               class="scan-module-card group rs-card rs-card-security rounded-xl p-5 flex flex-col gap-3 cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
                         style="background:rgba(220,38,38,0.12);">
                        <svg class="w-5 h-5" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white text-sm">Pemindai URL</h3>
                        <span class="badge-security text-[9px]">Security</span>
                    </div>
                </div>
                <p class="text-xs leading-relaxed" style="color:#64748b;">Pindai keamanan website & domain secara real-time.</p>
                <div class="flex items-center gap-1 text-xs font-medium mt-auto" style="color:#ef4444;">
                    Mulai Scan <svg class="w-3 h-3 ml-0.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>


            {{-- Leaderboard / AI --}}
            <a href="/edukasi/leaderboard"
               class="scan-module-card ai-module group rs-card rs-card-ai rounded-xl p-5 flex flex-col gap-3 cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
                         style="background:rgba(6,182,212,0.12);">
                        <svg class="w-5 h-5" style="color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white text-sm">Leaderboard</h3>
                        <span class="badge-ai text-[9px]">EDU</span>
                    </div>
                </div>
                <p class="text-xs leading-relaxed" style="color:#64748b;">Lihat peringkat & poin Anda di tantangan keamanan.</p>
                <div class="flex items-center gap-1 text-xs font-medium mt-auto" style="color:#22d3ee;">
                    Lihat Peringkat <svg class="w-3 h-3 ml-0.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
        </div>
    </div>

    {{-- ══ Recent Scans Table ══ --}}
    <div class="rs-card rounded-xl overflow-hidden">
        <div class="px-6 py-4 flex items-center justify-between" style="border-bottom:1px solid #1e293b;">
            <div class="flex items-center gap-3">
                <h3 class="font-semibold text-white">Pemindaian Terakhir</h3>
                <span class="badge-security">Riwayat</span>
            </div>
            <a href="{{ route('laporan.index') }}"
               class="text-xs font-medium transition-colors duration-200 hover:underline" style="color:#ef4444;">
                Lihat Semua →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="rs-table">
                <thead>
                    <tr>
                        <th>Target / File</th>
                        <th>Tipe</th>
                        <th>Waktu</th>
                        <th>Skor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scans as $scan)
                    <tr class="cursor-pointer" onclick="window.location='{{ $scan->status === 'selesai' ? route('laporan.detail', $scan) : '#' }}'">
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center
                                            {{ $scan->tipe_scan === 'kode' ? '' : ($scan->tipe_scan === 'url' ? '' : '') }}"
                                     style="{{ $scan->tipe_scan === 'kode'
                                         ? 'background:rgba(220,38,38,0.10);'
                                         : ($scan->tipe_scan === 'url'
                                             ? 'background:rgba(220,38,38,0.10);'
                                             : 'background:rgba(34,197,94,0.10);') }}">
                                    @if($scan->tipe_scan === 'kode')
                                    <svg class="w-4 h-4" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                    </svg>
                                    @elseif($scan->tipe_scan === 'url')
                                    <svg class="w-4 h-4" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3"/>
                                    </svg>
                                    @else
                                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4"/>
                                    </svg>
                                    @endif
                                </div>
                                <span class="text-sm font-medium text-white truncate max-w-xs">
                                    {{ Str::limit($scan->target ?? $scan->nama_file ?? 'Analisis ' . $scan->tipe_scan, 55) }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold uppercase"
                                  style="{{ $scan->tipe_scan === 'zip'
                                      ? 'background:rgba(34,197,94,0.10);color:#4ade80;'
                                      : 'background:rgba(220,38,38,0.10);color:#f87171;' }}">
                                {{ $scan->tipe_scan }}
                            </span>
                        </td>
                        <td class="text-xs" style="color:#64748b;">{{ $scan->created_at->diffForHumans() }}</td>
                        <td>
                            @if($scan->skor_keamanan !== null)
                            <span class="text-sm font-bold {{ $scan->skor_keamanan >= 70 ? 'text-green-400' : ($scan->skor_keamanan >= 50 ? 'text-yellow-400' : 'text-red-400') }}">
                                {{ $scan->skor_keamanan }}<span class="text-xs font-normal text-slate-600">/100</span>
                            </span>
                            @else
                            <span style="color:#334155;">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $scan->status === 'selesai' ? 'bg-green-500/10 text-green-400' : ($scan->status === 'gagal' ? 'bg-red-500/10 text-red-400' : 'bg-yellow-500/10 text-yellow-400') }}">
                                {{ $scan->status_label }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-14 text-center">
                            <svg class="w-12 h-12 mx-auto mb-3" style="color:#1e293b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm" style="color:#64748b;">Belum ada pemindaian. Mulai analisis pertama Anda!</p>
                            <a href="{{ route('analisis.url') }}"
                               class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all duration-300 hover:shadow-[0_0_15px_rgba(220,38,38,0.35)]"
                               style="background:linear-gradient(135deg,#dc2626,#991b1b);">
                                Mulai Scan Pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
