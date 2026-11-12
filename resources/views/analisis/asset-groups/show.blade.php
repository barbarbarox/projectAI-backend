@extends('layouts.app')

@section('title', $assetGroup->nama . ' — Grup Aset ASM — RedSim')
@section('header', 'Detail Grup Aset: ' . $assetGroup->nama)
@section('subheader', 'Manajemen target, pemantauan postur keamanan terpadu, dan orkestrasi pemindaian massal')
@section('page_feature', 'asset-groups')

@section('content')
@php
    $theme = $assetGroup->color_theme;
    $envBadge = match($assetGroup->environment) {
        'production'  => ['bg' => 'bg-red-500/15',    'border' => 'border-red-500/40',    'text' => 'text-red-400',    'label' => 'PRODUCTION'],
        'staging'     => ['bg' => 'bg-amber-500/15',  'border' => 'border-amber-500/40',  'text' => 'text-amber-400',  'label' => 'STAGING'],
        'development' => ['bg' => 'bg-cyan-500/15',   'border' => 'border-cyan-500/40',   'text' => 'text-cyan-400',   'label' => 'DEV'],
        default       => ['bg' => 'bg-slate-500/15',  'border' => 'border-slate-500/40',  'text' => 'text-slate-400',  'label' => 'INTERNAL'],
    };

    $targets = $assetGroup->targets;
    $totalTargets = $targets->count();
    $scannedTargets = $targets->filter(fn($t) => $t->last_scanned_at !== null)->count();
    $scannedPercentage = $totalTargets > 0 ? (int) round(($scannedTargets / $totalTargets) * 100) : 0;

    $avgScore = $assetGroup->rata_rata_skor;
    $criticalCount = $targets->filter(fn($t) => $t->last_skor_keamanan !== null && $t->last_skor_keamanan < 50)->count();
@endphp

<div class="max-w-7xl mx-auto space-y-6 pb-12">

    {{-- Flash Message --}}
    @if(session('pesan'))
    <div class="p-4 rounded-xl border flex items-center justify-between gap-3 text-sm
        {{ session('pesan')['tipe'] === 'sukses' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-red-500/10 border-red-500/30 text-red-300' }}">
        <div class="flex items-center gap-2.5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('pesan')['isi'] }}</span>
        </div>
    </div>
    @endif

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <a href="{{ route('asset-groups.index') }}"
           class="inline-flex items-center gap-2 text-xs font-medium text-[#64748b] hover:text-[#00d4ff] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Daftar Grup Aset</span>
        </a>

        <div class="flex items-center gap-2">
            {{-- Tombol Edit Info --}}
            <button onclick="document.getElementById('modal-edit-grup').classList.remove('hidden')"
                    class="px-3 py-1.5 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] hover:border-slate-600 text-xs text-[#94a3b8] hover:text-white transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Grup</span>
            </button>

            {{-- Tombol Hapus Grup --}}
            <form action="{{ route('asset-groups.destroy', $assetGroup->id) }}" method="POST"
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus grup aset ini beserta seluruh targetnya? Tindakan ini tidak dapat dibatalkan.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/30 hover:bg-red-500/20 text-xs text-red-400 transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Hapus</span>
                </button>
            </form>
        </div>
    </div>

    {{-- GROUP HERO BANNER --}}
    <div class="rounded-2xl bg-[#0a0e1a] border border-[#1e2d4a] p-6 relative overflow-hidden shadow-2xl">
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r {{ $theme['gradient'] }}"></div>
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-cyan-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-start justify-between gap-6 flex-wrap relative z-10">
            <div class="space-y-3 max-w-2xl">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="px-2.5 py-1 rounded-md text-[11px] font-mono font-bold tracking-wider border {{ $envBadge['bg'] }} {{ $envBadge['border'] }} {{ $envBadge['text'] }}">
                        {{ $envBadge['label'] }}
                    </span>
                    <span class="px-2.5 py-1 rounded-md text-[11px] font-mono border {{ $theme['bg'] }} {{ $theme['border'] }} {{ $theme['text'] }} capitalize">
                        Tema: {{ $assetGroup->warna }}
                    </span>
                    <span class="text-xs text-[#64748b]">
                        Dibuat {{ $assetGroup->created_at->diffForHumans() }}
                    </span>
                </div>

                <div>
                    <h1 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                        <span>{{ $assetGroup->nama }}</span>
                    </h1>
                    <p class="text-sm text-[#94a3b8] mt-1.5 leading-relaxed">
                        {{ $assetGroup->deskripsi ?: 'Tidak ada deskripsi tambahan untuk grup aset ini.' }}
                    </p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-3 flex-wrap">
                {{-- Tambah Target Button --}}
                <button onclick="document.getElementById('modal-tambah-target').classList.remove('hidden')"
                        class="px-4 py-2.5 rounded-xl bg-[#0f172a] border border-[#334155] hover:border-cyan-500/50 text-white text-xs font-semibold shadow-md flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Target</span>
                </button>

                {{-- Batch Scan Button --}}
                <button onclick="document.getElementById('modal-batch-scan').classList.remove('hidden')"
                        class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white text-xs font-semibold shadow-lg shadow-cyan-950/40 flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Pindai Seluruh Grup</span>
                </button>

                {{-- Threat Hunt Grup Link --}}
                <a href="{{ route('threat-hunt.index') }}"
                   class="px-3.5 py-2.5 rounded-xl bg-[#0f172a] border border-[#1e2d4a] hover:border-indigo-500/50 text-[#94a3b8] hover:text-indigo-300 text-xs font-medium flex items-center gap-1.5 transition-all"
                   title="Buka Dasbor Threat Hunt untuk menganalisis CVE pada grup ini">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Threat Hunt</span>
                </a>
            </div>
        </div>
    </div>

    {{-- POSTURE METRICS ROW --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Target --}}
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 flex flex-col justify-between">
            <span class="text-xs text-[#64748b] font-medium uppercase tracking-wider">Total Target Terdaftar</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-white">{{ $totalTargets }}</span>
                <span class="text-xs text-[#475569]">Asset Host</span>
            </div>
        </div>

        {{-- Cakupan Pemindaian --}}
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 flex flex-col justify-between">
            <span class="text-xs text-[#64748b] font-medium uppercase tracking-wider">Cakupan Pemindaian</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-[#00d4ff]">{{ $scannedPercentage }}%</span>
                <span class="text-xs text-[#475569]">{{ $scannedTargets }} dari {{ $totalTargets }} host</span>
            </div>
        </div>

        {{-- Rata-rata Skor ASM --}}
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 flex flex-col justify-between">
            <span class="text-xs text-[#64748b] font-medium uppercase tracking-wider">Rata-rata Skor Keamanan</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono {{ ($avgScore ?? 0) >= 70 ? 'text-emerald-400' : (($avgScore ?? 0) >= 50 ? 'text-amber-400' : 'text-red-400') }}">
                    {{ $avgScore !== null ? $avgScore . '/100' : 'Belum Ada' }}
                </span>
                <span class="text-xs text-[#475569]">Posture Index</span>
            </div>
        </div>

        {{-- Target Kritis --}}
        <div class="rounded-xl bg-red-500/10 border border-red-500/30 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs text-red-400 font-bold uppercase tracking-wider">Target Berisiko Tinggi</span>
                @if($criticalCount > 0)
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                @endif
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-red-400">{{ $criticalCount }}</span>
                <span class="text-[11px] text-red-400/70">Skor &lt; 50</span>
            </div>
        </div>
    </div>

    {{-- TARGETS MANAGEMENT TABLE --}}
    <div class="rounded-2xl bg-[#0a0e1a] border border-[#1e2d4a] overflow-hidden shadow-xl">
        <div class="p-5 border-b border-[#1e2d4a] flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Daftar Target Host & Endpoint ({{ $totalTargets }})</span>
                </h2>
                <p class="text-xs text-[#64748b] mt-0.5">Seluruh domain, subdomain, dan API yang diawasi di bawah grup aset ini.</p>
            </div>

            <button onclick="document.getElementById('modal-tambah-target').classList.remove('hidden')"
                    class="px-3.5 py-2 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 text-xs font-semibold flex items-center gap-1.5 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Tambah Target Baru</span>
            </button>
        </div>

        @if($targets->isEmpty())
        <div class="p-12 text-center space-y-4">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-white">Belum Ada Target Terdaftar</h3>
                <p class="text-xs text-[#64748b] max-w-md mx-auto mt-1">Tambahkan URL domain, subdomain, atau API aplikasi Anda ke dalam grup ini untuk mulai memantau keamanan secara berkala.</p>
            </div>
            <button onclick="document.getElementById('modal-tambah-target').classList.remove('hidden')"
                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 text-white text-xs font-semibold shadow-lg transition-all">
                Tambah Target Pertama
            </button>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0f172a]/70 text-[#64748b] border-b border-[#1e2d4a] font-mono uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Target Host / URL</th>
                        <th class="py-3 px-4">Label Peran</th>
                        <th class="py-3 px-4">Pindaian Terakhir</th>
                        <th class="py-3 px-4 text-center">Skor Keamanan</th>
                        <th class="py-3 px-4">Verdict Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d4a]/60">
                    @foreach($targets as $target)
                    <tr class="hover:bg-[#0f172a]/50 transition-colors">
                        {{-- Target Host / URL --}}
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2.5">
                                <span class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                </span>
                                <div class="min-w-0">
                                    <a href="{{ $target->target_url }}" target="_blank" rel="noopener noreferrer"
                                       class="font-mono font-medium text-white hover:text-cyan-400 transition-colors inline-flex items-center gap-1 group">
                                        <span class="truncate max-w-xs md:max-w-md">{{ $target->target_url }}</span>
                                        <svg class="w-3 h-3 text-[#475569] group-hover:text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            </div>
                        </td>

                        {{-- Label Peran --}}
                        <td class="py-3.5 px-4">
                            @if($target->label)
                            <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-slate-800 text-slate-300 border border-slate-700">
                                {{ $target->label }}
                            </span>
                            @else
                            <span class="text-[#475569] italic">-</span>
                            @endif
                        </td>

                        {{-- Pindaian Terakhir --}}
                        <td class="py-3.5 px-4 text-[#94a3b8] font-mono">
                            @if($target->last_scanned_at)
                                <div class="flex flex-col">
                                    <span class="text-white">{{ $target->last_scanned_at->diffForHumans() }}</span>
                                    <span class="text-[10px] text-[#475569]">{{ $target->last_scanned_at->format('d M Y H:i') }}</span>
                                </div>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                    Belum Pernah
                                </span>
                            @endif
                        </td>

                        {{-- Skor Keamanan --}}
                        <td class="py-3.5 px-4 text-center">
                            @if($target->last_skor_keamanan !== null)
                                @php
                                    $score = $target->last_skor_keamanan;
                                    $scoreColor = $score >= 70 ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30' : ($score >= 50 ? 'text-amber-400 bg-amber-500/10 border-amber-500/30' : 'text-red-400 bg-red-500/10 border-red-500/30');
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded-full font-mono font-bold text-xs border {{ $scoreColor }}">
                                    {{ $score }}/100
                                </span>
                            @else
                                <span class="text-[#475569] font-mono">-</span>
                            @endif
                        </td>

                        {{-- Verdict Status --}}
                        <td class="py-3.5 px-4">
                            @if($target->last_verdict)
                                @php
                                    $v = strtoupper($target->last_verdict);
                                    $verdictBadge = match(true) {
                                        str_contains($v, 'AMAN') || str_contains($v, 'SECURE') || str_contains($v, 'BERSIH') => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/30'],
                                        str_contains($v, 'WASPADA') || str_contains($v, 'PERINGATAN') || str_contains($v, 'SUSPICIOUS') => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/30'],
                                        default => ['bg' => 'bg-red-500/10', 'text' => 'text-red-400', 'border' => 'border-red-500/30'],
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono font-medium border {{ $verdictBadge['bg'] }} {{ $verdictBadge['border'] }} {{ $verdictBadge['text'] }}">
                                    {{ $target->last_verdict }}
                                </span>
                            @else
                                <span class="text-[#475569] font-mono">-</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- Pindai Langsung --}}
                                <a href="{{ route('analisis.url') }}?target={{ urlencode($target->target_url) }}"
                                   class="p-1.5 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 transition-colors"
                                   title="Pindai URL ini di Analisis URL">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>

                                {{-- Lihat Laporan Terakhir --}}
                                @if($target->last_scan_id)
                                <a href="{{ route('analisis.hasil', $target->last_scan_id) }}"
                                   class="p-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 transition-colors"
                                   title="Buka Laporan Hasil Scan Terakhir">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                                @endif

                                {{-- Hapus Target --}}
                                <form action="{{ route('asset-groups.remove-target', $target->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus target {{ $target->clean_url }} dari grup ini?');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 transition-colors"
                                            title="Hapus Target dari Grup">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

{{-- ========================================================================= --}}
{{-- MODAL TAMBAH TARGET --}}
{{-- ========================================================================= --}}
<div id="modal-tambah-target" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm hidden">
    <div class="rounded-2xl bg-[#0a0e1a] border border-[#1e2d4a] w-full max-w-md p-6 space-y-5 shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-[#1e2d4a]">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Target Baru</span>
            </h3>
            <button onclick="document.getElementById('modal-tambah-target').classList.add('hidden')"
                    class="text-[#64748b] hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('asset-groups.add-target', $assetGroup->id) }}" method="POST" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-white">Target URL / Domain <span class="text-red-400">*</span></label>
                <div class="relative">
                    <input type="text" name="target_url" required placeholder="https://api.domain.com atau domain.com"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-[#0f172a] border border-[#1e2d4a] text-white text-xs font-mono placeholder-[#475569] focus:outline-none focus:border-cyan-500">
                </div>
                <p class="text-[11px] text-[#64748b]">Format didukung: domain utama, subdomain, atau path API lengkap.</p>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-white">Label / Peran Komponen <span class="text-[#64748b]">(Opsional)</span></label>
                <input type="text" name="label" placeholder="Contoh: Gateway API, Frontend Web, Auth Service"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-[#0f172a] border border-[#1e2d4a] text-white text-xs placeholder-[#475569] focus:outline-none focus:border-cyan-500">
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#1e2d4a]">
                <button type="button" onclick="document.getElementById('modal-tambah-target').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl bg-[#0f172a] border border-[#1e2d4a] text-xs text-[#94a3b8] hover:text-white">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white text-xs font-semibold shadow-lg">
                    Tambahkan Target
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL BATCH SCAN (PINDAI SELURUH GRUP) --}}
{{-- ========================================================================= --}}
<div id="modal-batch-scan" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm hidden">
    <div class="rounded-2xl bg-[#0a0e1a] border border-[#1e2d4a] w-full max-w-lg p-6 space-y-5 shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-[#1e2d4a]">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Pemindaian Massal (Batch Scan ASM)</span>
            </h3>
            <button onclick="document.getElementById('modal-batch-scan').classList.add('hidden')"
                    class="text-[#64748b] hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('asset-groups.batch-scan', $assetGroup->id) }}" method="POST" class="space-y-4">
            @csrf

            <div class="p-3.5 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-xs text-cyan-300 space-y-1">
                <div class="font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Orkestrasi Pemindaian Asinkron</span>
                </div>
                <p class="text-[#94a3b8] text-[11px] leading-relaxed">
                    Sistem akan membuat tugas pindaian otomatis untuk <strong class="text-white">{{ $totalTargets }} target host</strong> dalam grup ini dan menjalankannya di background queue worker.
                </p>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-semibold text-white">Pilih Mode Pemindaian</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="cursor-pointer border border-[#1e2d4a] rounded-xl p-3 bg-[#0f172a] hover:border-cyan-500/50 has-[:checked]:border-cyan-500 has-[:checked]:bg-cyan-500/10 transition-all flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">Mode Cepat (Biasa)</span>
                            <input type="radio" name="mode_scan" value="biasa" checked class="text-cyan-500 focus:ring-0">
                        </div>
                        <p class="text-[11px] text-[#64748b] mt-1.5 leading-relaxed">Pemeriksaan HTTP, Header keamanan, SSL, dan analisis cepat.</p>
                    </label>

                    <label class="cursor-pointer border border-[#1e2d4a] rounded-xl p-3 bg-[#0f172a] hover:border-cyan-500/50 has-[:checked]:border-cyan-500 has-[:checked]:bg-cyan-500/10 transition-all flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">Mode Intensif (ASM)</span>
                            <input type="radio" name="mode_scan" value="intens" class="text-cyan-500 focus:ring-0">
                        </div>
                        <p class="text-[11px] text-[#64748b] mt-1.5 leading-relaxed">Subdomain enumeration, Port scan, Dangling takeover check, dan AI synthesis.</p>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#1e2d4a]">
                <button type="button" onclick="document.getElementById('modal-batch-scan').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl bg-[#0f172a] border border-[#1e2d4a] text-xs text-[#94a3b8] hover:text-white">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white text-xs font-semibold shadow-lg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Luncurkan Pemindaian Massal</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL EDIT INFO GRUP --}}
{{-- ========================================================================= --}}
<div id="modal-edit-grup" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm hidden">
    <div class="rounded-2xl bg-[#0a0e1a] border border-[#1e2d4a] w-full max-w-lg p-6 space-y-5 shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-[#1e2d4a]">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Edit Informasi Grup Aset</span>
            </h3>
            <button onclick="document.getElementById('modal-edit-grup').classList.add('hidden')"
                    class="text-[#64748b] hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('asset-groups.update', $assetGroup->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-white">Nama Kelompok Aset <span class="text-red-400">*</span></label>
                <input type="text" name="nama" required value="{{ old('nama', $assetGroup->nama) }}"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-[#0f172a] border border-[#1e2d4a] text-white text-xs focus:outline-none focus:border-cyan-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-white">Lingkungan (Environment) <span class="text-red-400">*</span></label>
                    <select name="environment" required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#0f172a] border border-[#1e2d4a] text-white text-xs focus:outline-none focus:border-cyan-500">
                        <option value="production" {{ $assetGroup->environment === 'production' ? 'selected' : '' }}>🔴 Production</option>
                        <option value="staging" {{ $assetGroup->environment === 'staging' ? 'selected' : '' }}>🟡 Staging / QA</option>
                        <option value="development" {{ $assetGroup->environment === 'development' ? 'selected' : '' }}>🔵 Development</option>
                        <option value="internal" {{ $assetGroup->environment === 'internal' ? 'selected' : '' }}>⚪ Internal Network</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-white">Tema Warna Aksen <span class="text-red-400">*</span></label>
                    <select name="warna" required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#0f172a] border border-[#1e2d4a] text-white text-xs focus:outline-none focus:border-cyan-500">
                        <option value="cyan" {{ $assetGroup->warna === 'cyan' ? 'selected' : '' }}>Cyan Cyber</option>
                        <option value="indigo" {{ $assetGroup->warna === 'indigo' ? 'selected' : '' }}>Indigo Electric</option>
                        <option value="emerald" {{ $assetGroup->warna === 'emerald' ? 'selected' : '' }}>Emerald Defense</option>
                        <option value="amber" {{ $assetGroup->warna === 'amber' ? 'selected' : '' }}>Amber Warning</option>
                        <option value="purple" {{ $assetGroup->warna === 'purple' ? 'selected' : '' }}>Purple AI</option>
                        <option value="rose" {{ $assetGroup->warna === 'rose' ? 'selected' : '' }}>Rose Red Team</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-white">Deskripsi Grup</label>
                <textarea name="deskripsi" rows="3"
                          class="w-full px-3.5 py-2.5 rounded-xl bg-[#0f172a] border border-[#1e2d4a] text-white text-xs placeholder-[#475569] focus:outline-none focus:border-cyan-500">{{ old('deskripsi', $assetGroup->deskripsi) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#1e2d4a]">
                <button type="button" onclick="document.getElementById('modal-edit-grup').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl bg-[#0f172a] border border-[#1e2d4a] text-xs text-[#94a3b8] hover:text-white">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 text-white text-xs font-semibold shadow-lg">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
