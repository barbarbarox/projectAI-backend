@extends('layouts.app')

@section('title', 'Grup Aset & Manajemen Permukaan Serangan (ASM) — RedSim')
@section('header', 'Grup Aset & Multi-Target ASM')
@section('subheader', 'Kelompokkan domain, API, dan infrastruktur target Anda untuk pemantauan postur keamanan terpadu dan pemindaian massal')
@section('page_feature', 'asset-groups')

@section('content')
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

    {{-- TOP SUMMARY METRICS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Groups --}}
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 flex flex-col justify-between">
            <span class="text-xs text-[#64748b] font-medium uppercase tracking-wider">Total Grup Aset</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-white">{{ $stats['total_groups'] }}</span>
                <span class="text-xs text-[#475569]">Kelompok</span>
            </div>
        </div>

        {{-- Total Targets --}}
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 flex flex-col justify-between">
            <span class="text-xs text-[#64748b] font-medium uppercase tracking-wider">Target Dipantau</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-[#00d4ff]">{{ $stats['total_targets'] }}</span>
                <span class="text-xs text-[#475569]">Host / URL</span>
            </div>
        </div>

        {{-- Rata-rata Skor Keamanan --}}
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 flex flex-col justify-between">
            <span class="text-xs text-[#64748b] font-medium uppercase tracking-wider">Rata-rata Skor ASM</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono {{ ($stats['avg_score'] ?? 0) >= 70 ? 'text-emerald-400' : (($stats['avg_score'] ?? 0) >= 50 ? 'text-amber-400' : 'text-red-400') }}">
                    {{ $stats['avg_score'] !== null ? $stats['avg_score'] . '/100' : '-' }}
                </span>
                <span class="text-xs text-[#475569]">Agregasi Postur</span>
            </div>
        </div>

        {{-- Target Kritis --}}
        <div class="rounded-xl bg-red-500/10 border border-red-500/30 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs text-red-400 font-bold uppercase tracking-wider">Target Berisiko</span>
                @if($stats['critical_targets'] > 0)
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                @endif
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-red-400">{{ $stats['critical_targets'] }}</span>
                <span class="text-[11px] text-red-400/70">Skor &lt; 50</span>
            </div>
        </div>
    </div>

    {{-- HEADER TOOLBAR --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <span>Daftar Kelompok Aset Digital</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-indigo-500/10 text-indigo-300 border border-indigo-500/30">ASM Framework</span>
            </h2>
            <p class="text-xs text-[#64748b] mt-0.5">Kelola aset produksi, staging, dan subdomain organisasi dalam satu dasbor terpusat.</p>
        </div>

        <a href="{{ route('asset-groups.create') }}"
           class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 text-white text-xs font-semibold shadow-lg shadow-indigo-950/40 flex items-center gap-2 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Buat Grup Aset Baru</span>
        </a>
    </div>

    {{-- ASSET GROUPS CARDS GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($groups as $grp)
        @php
            $theme = $grp->color_theme;
            $envBadge = match($grp->environment) {
                'production'  => ['bg' => 'bg-red-500/15',    'border' => 'border-red-500/40',    'text' => 'text-red-400',    'label' => 'PRODUCTION'],
                'staging'     => ['bg' => 'bg-amber-500/15',  'border' => 'border-amber-500/40',  'text' => 'text-amber-400',  'label' => 'STAGING'],
                'development' => ['bg' => 'bg-cyan-500/15',   'border' => 'border-cyan-500/40',   'text' => 'text-cyan-400',   'label' => 'DEV'],
                default       => ['bg' => 'bg-slate-500/15',  'border' => 'border-slate-500/40',  'text' => 'text-slate-400',  'label' => 'INTERNAL'],
            };
            $avg = $grp->rata_rata_skor;
        @endphp

        <div class="rounded-2xl bg-[#0a0e1a] border border-[#1e2d4a] hover:border-slate-700 p-5 transition-all relative overflow-hidden flex flex-col justify-between group shadow-xl">
            {{-- Top Accent Line --}}
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r {{ $theme['gradient'] }}"></div>

            <div class="space-y-4">
                {{-- Group Header & Environment Badge --}}
                <div class="flex items-start justify-between gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $envBadge['bg'] }} {{ $envBadge['border'] }} {{ $envBadge['text'] }}">
                        {{ $envBadge['label'] }}
                    </span>

                    {{-- Avg Score Badge --}}
                    @if($avg !== null)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold border
                        {{ $avg >= 70 ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : ($avg >= 50 ? 'bg-amber-500/10 text-amber-400 border-amber-500/30' : 'bg-red-500/10 text-red-400 border-red-500/30') }}">
                        Skor: {{ $avg }}/100
                    </span>
                    @else
                    <span class="text-[10px] text-[#64748b]">Belum dipindai</span>
                    @endif
                </div>

                {{-- Group Title & Description --}}
                <div>
                    <h3 class="text-base font-bold text-white group-hover:text-[#00d4ff] transition-colors truncate">
                        {{ $grp->nama }}
                    </h3>
                    <p class="text-xs text-[#64748b] mt-1 line-clamp-2 leading-relaxed">
                        {{ $grp->deskripsi ?: 'Tidak ada deskripsi kelompok aset.' }}
                    </p>
                </div>

                {{-- Targets List Preview (Max 3) --}}
                <div class="space-y-1.5 pt-2 border-t border-[#1e2d4a]/70">
                    <div class="flex items-center justify-between text-[11px] text-[#64748b]">
                        <span>Daftar Target:</span>
                        <span class="font-mono text-slate-300 font-semibold">{{ $grp->targets->count() }} Target</span>
                    </div>

                    <div class="space-y-1">
                        @forelse($grp->targets->take(3) as $tgt)
                        <div class="text-xs font-mono bg-[#0f1629] px-2.5 py-1.5 rounded-lg border border-[#1e2d4a] flex items-center justify-between text-slate-300 truncate">
                            <span class="truncate">{{ $tgt->clean_url }}</span>
                            @if($tgt->last_skor_keamanan !== null)
                            <span class="text-[10px] shrink-0 font-bold ml-2 {{ $tgt->last_skor_keamanan >= 70 ? 'text-emerald-400' : 'text-amber-400' }}">
                                {{ $tgt->last_skor_keamanan }}
                            </span>
                            @endif
                        </div>
                        @empty
                        <div class="text-[11px] text-[#64748b] italic py-1">
                            Belum ada target yang ditambahkan.
                        </div>
                        @endforelse

                        @if($grp->targets->count() > 3)
                        <span class="text-[10px] text-[#64748b] block text-right">
                            +{{ $grp->targets->count() - 3 }} target lainnya
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Footer Action Buttons --}}
            <div class="pt-4 mt-4 border-t border-[#1e2d4a] flex items-center justify-between gap-2">
                <a href="{{ route('asset-groups.show', $grp->id) }}"
                   class="flex-1 text-center py-2 rounded-xl bg-[#1e293b] hover:bg-[#334155] text-xs font-semibold text-white transition-colors flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Kelola & Pindai Massal</span>
                </a>

                <form method="POST" action="{{ route('asset-groups.destroy', $grp->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus grup aset ini beserta seluruh targetnya?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-xl hover:bg-red-500/10 text-[#64748b] hover:text-red-400 transition-colors" title="Hapus Grup">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full rounded-2xl bg-[#0a0e1a] border border-[#1e2d4a] py-16 text-center text-[#64748b]">
            <div class="max-w-md mx-auto space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center mx-auto text-indigo-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Belum Ada Grup Aset yang Dibuat</h3>
                    <p class="text-xs text-[#64748b] mt-1 leading-relaxed">
                        Kelompokkan target pemindaian Anda (misal: "Web Produksi", "Microservices API", "Server Cabang") untuk mempermudah monitoring keamanan dan pemindaian batch sekaligus.
                    </p>
                </div>
                <div>
                    <a href="{{ route('asset-groups.create') }}"
                       class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 text-white text-xs font-semibold shadow-lg shadow-indigo-950/40 inline-flex items-center gap-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Buat Grup Aset Pertama Anda</span>
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>

</div>
@endsection
