@extends('layouts.app')

@section('title', 'Hub Seluruh Temuan Keamanan — RedSim')
@section('header', 'Hub Seluruh Temuan Keamanan')
@section('subheader', 'Agregasi terpusat seluruh kerentanan dari pemindaian URL, repositori kode, dan log sistem Anda')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    {{-- Alert Flash Message --}}
    @if(session('pesan'))
    <div class="p-4 rounded-xl border flex items-center justify-between gap-3 text-sm
        {{ session('pesan')['tipe'] === 'sukses' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-cyan-500/10 border-cyan-500/30 text-cyan-300' }}">
        <div class="flex items-center gap-2.5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('pesan')['isi'] }}</span>
        </div>
    </div>
    @endif

    {{-- STATS CARDS ROW --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5">
        {{-- Total Temuan --}}
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 flex flex-col justify-between">
            <span class="text-xs text-[#64748b] font-medium uppercase tracking-wider">Total Temuan</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-white">{{ $stats['total'] }}</span>
                <span class="text-xs text-[#475569]">All Assets</span>
            </div>
        </div>

        {{-- Kritis & Tinggi --}}
        <div class="rounded-xl bg-red-500/10 border border-red-500/30 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs text-red-400 font-bold uppercase tracking-wider">Kritis & Tinggi</span>
                @if($stats['kritis_tinggi'] > 0)
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                @endif
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-red-400">{{ $stats['kritis_tinggi'] }}</span>
                <span class="text-[11px] text-red-400/70">{{ $stats['kritis'] }} Kritis · {{ $stats['tinggi'] }} Tinggi</span>
            </div>
        </div>

        {{-- Baru (Unassigned) --}}
        <div class="rounded-xl bg-amber-500/5 border border-amber-500/20 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs text-amber-400/90 font-medium uppercase tracking-wider">Belum Ditangani</span>
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-amber-400">{{ $stats['baru'] }}</span>
                <span class="text-[11px] text-amber-400/60">Temuan Baru</span>
            </div>
        </div>

        {{-- Dalam Penanganan --}}
        <div class="rounded-xl bg-cyan-500/5 border border-cyan-500/20 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs text-cyan-400/90 font-medium uppercase tracking-wider">Dalam Proses</span>
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-cyan-400">{{ $stats['dalam_penanganan'] }}</span>
                <span class="text-[11px] text-cyan-400/60">Patching/Investigasi</span>
            </div>
        </div>

        {{-- Selesai / Resolution Rate --}}
        <div class="col-span-2 md:col-span-1 rounded-xl bg-emerald-500/10 border border-emerald-500/30 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs text-emerald-400 font-bold uppercase tracking-wider">Tingkat Resolusi</span>
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-emerald-400">{{ $stats['resolution_rate'] }}%</span>
                <span class="text-[11px] text-emerald-400/70">{{ $stats['selesai'] }} Selesai</span>
            </div>
        </div>
    </div>

    {{-- FILTER & SEARCH TOOLBAR --}}
    <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4">
        <form method="GET" action="{{ route('temuan.index') }}" class="flex flex-wrap items-center gap-3">
            {{-- Search Input --}}
            <div class="relative flex-1 min-w-[220px]">
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Cari judul, CVE-ID (CVE-2024-...), CWE, endpoint lokasi..."
                       class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3.5 py-2 text-xs text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff] pl-9">
                <svg class="w-4 h-4 text-[#475569] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            {{-- Keparahan Filter --}}
            <select name="keparahan"
                    onchange="this.form.submit()"
                    class="bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#00d4ff]">
                <option value="all" {{ request('keparahan') === 'all' || !request('keparahan') ? 'selected' : '' }}>Semua Keparahan</option>
                <option value="kritis" {{ request('keparahan') === 'kritis' ? 'selected' : '' }}>Kritis</option>
                <option value="tinggi" {{ request('keparahan') === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                <option value="sedang" {{ request('keparahan') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                <option value="rendah" {{ request('keparahan') === 'rendah' ? 'selected' : '' }}>Rendah</option>
                <option value="info" {{ request('keparahan') === 'info' ? 'selected' : '' }}>Info</option>
            </select>

            {{-- Status Filter --}}
            <select name="status"
                    onchange="this.form.submit()"
                    class="bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#00d4ff]">
                <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                <option value="baru" {{ request('status') === 'baru' ? 'selected' : '' }}>Baru</option>
                <option value="dalam_penanganan" {{ request('status') === 'dalam_penanganan' ? 'selected' : '' }}>Dalam Penanganan</option>
                <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai (Resolved)</option>
            </select>

            {{-- Tipe Scan Filter --}}
            <select name="tipe_scan"
                    onchange="this.form.submit()"
                    class="bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#00d4ff]">
                <option value="all" {{ request('tipe_scan') === 'all' || !request('tipe_scan') ? 'selected' : '' }}>Semua Sumber</option>
                <option value="url" {{ request('tipe_scan') === 'url' ? 'selected' : '' }}>URL Scanner</option>
                <option value="kode" {{ request('tipe_scan') === 'kode' ? 'selected' : '' }}>Analisis Kode (SAST)</option>
                <option value="log" {{ request('tipe_scan') === 'log' ? 'selected' : '' }}>Log Analyzer</option>
            </select>

            <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#1e293b] hover:bg-[#334155] text-xs text-white font-medium transition-colors">
                Terapkan
            </button>
            @if(request()->anyFilled(['q', 'keparahan', 'status', 'tipe_scan']))
            <a href="{{ route('temuan.index') }}" class="text-xs text-[#64748b] hover:text-[#94a3b8] transition-colors">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- FINDINGS LIST --}}
    <div class="space-y-4">
        @forelse($temuans as $temuan)
        @php
            $sevBadge = match($temuan->tingkat_keparahan) {
                'kritis' => ['bg' => 'bg-red-500/15',    'border' => 'border-red-500/40',    'text' => 'text-red-400',    'label' => 'KRITIS'],
                'tinggi' => ['bg' => 'bg-orange-500/15', 'border' => 'border-orange-500/40', 'text' => 'text-orange-400', 'label' => 'TINGGI'],
                'sedang' => ['bg' => 'bg-yellow-500/15', 'border' => 'border-yellow-500/40', 'text' => 'text-yellow-400', 'label' => 'SEDANG'],
                'rendah' => ['bg' => 'bg-sky-500/15',    'border' => 'border-sky-500/40',    'text' => 'text-sky-400',    'label' => 'RENDAH'],
                default  => ['bg' => 'bg-slate-500/15',  'border' => 'border-slate-500/40',  'text' => 'text-slate-400',  'label' => 'INFO'],
            };

            $statusBadge = match($temuan->status) {
                'selesai'          => ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/30', 'text' => 'text-emerald-400', 'label' => 'Selesai'],
                'dalam_penanganan' => ['bg' => 'bg-cyan-500/10',    'border' => 'border-cyan-500/30',    'text' => 'text-cyan-400',    'label' => 'Dalam Penanganan'],
                default            => ['bg' => 'bg-amber-500/10',   'border' => 'border-amber-500/30',   'text' => 'text-amber-400',   'label' => 'Baru'],
            };
        @endphp

        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-5 transition-all hover:border-slate-700 space-y-4"
             x-data="{ openDetail: false }">
            
            {{-- Top summary row --}}
            <div class="flex items-start justify-between flex-wrap gap-3">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold border shrink-0 {{ $sevBadge['bg'] }} {{ $sevBadge['border'] }} {{ $sevBadge['text'] }}">
                        {{ $sevBadge['label'] }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-semibold text-white truncate">
                                {{ $temuan->judul }}
                            </h3>

                            @if($temuan->cve_id)
                            <a href="{{ route('analisis.cve-intel') }}?cve={{ urlencode($temuan->cve_id) }}"
                               target="_blank"
                               class="text-[11px] font-mono text-[#00d4ff] hover:underline inline-flex items-center gap-1 font-medium bg-[#0f1629] px-2 py-0.5 rounded border border-[#1e2d4a]">
                                <span>{{ $temuan->cve_id }}</span>
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            @endif

                            @if($temuan->cwe_id)
                            <span class="text-[10px] font-mono text-[#64748b] bg-[#0f1629] px-1.5 py-0.5 rounded border border-[#1e2d4a]">
                                {{ $temuan->cwe_id }}
                            </span>
                            @endif
                        </div>

                        {{-- Metadata row: Target, Scan Date, Status --}}
                        <div class="flex items-center gap-3 text-xs text-[#64748b] mt-1.5 flex-wrap">
                            @if($temuan->scan)
                            <a href="{{ route('laporan.detail', $temuan->scan->id) }}"
                               class="text-slate-400 hover:text-white transition-colors font-mono flex items-center gap-1">
                                <span class="uppercase text-[10px] px-1.5 py-0.2 rounded bg-slate-800 text-slate-300 font-semibold">{{ $temuan->scan->tipe_scan }}</span>
                                <span class="truncate max-w-xs">{{ $temuan->scan->target ?? $temuan->scan->nama_file }}</span>
                            </a>
                            <span>·</span>
                            @endif

                            <span>Ditemukan {{ $temuan->created_at->format('d M Y') }}</span>

                            @if($temuan->lokasi)
                            <span>·</span>
                            <span class="font-mono text-slate-400 truncate max-w-xs" title="{{ $temuan->lokasi }}">
                                Lokasi: {{ $temuan->lokasi }}
                            </span>
                            @endif

                            @if($temuan->ml_predicted_severity)
                            <span>·</span>
                            <span class="text-[10px] text-emerald-400">ML: {{ $temuan->ml_predicted_severity }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Status & Actions --}}
                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    {{-- Status Badge --}}
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold border {{ $statusBadge['bg'] }} {{ $statusBadge['border'] }} {{ $statusBadge['text'] }}">
                        {{ $statusBadge['label'] }}
                    </span>

                    {{-- Escalated to Incident Badge / Button --}}
                    @if($temuan->insiden)
                    <a href="{{ route('insiden.show', $temuan->insiden->id) }}"
                       class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[11px] font-mono flex items-center gap-1 transition-colors">
                        <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $temuan->insiden->nomor_insiden }}</span>
                    </a>
                    @else
                    <form method="POST" action="{{ route('insiden.store-from-temuan', $temuan->id) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-2.5 py-1 rounded-lg bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white border border-red-500/30 text-[11px] font-semibold transition-all flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Eskalasi Insiden</span>
                        </button>
                    </form>
                    @endif

                    {{-- Toggle Resolve / Reopen --}}
                    @if($temuan->status !== 'selesai')
                    <form method="POST" action="{{ route('temuan.resolve', $temuan->id) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="px-2.5 py-1 rounded-lg bg-emerald-600/20 hover:bg-emerald-600 text-emerald-400 hover:text-white border border-emerald-500/30 text-[11px] font-semibold transition-all flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Tandai Selesai</span>
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('temuan.reopen', $temuan->id) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-[#94a3b8] hover:text-white text-[11px] transition-colors"
                                title="Buka Kembali Temuan">
                            Buka Ulang
                        </button>
                    </form>
                    @endif

                    {{-- Detail Expand Toggle --}}
                    <button @click="openDetail = !openDetail"
                            class="p-1 rounded-lg bg-[#1e293b] hover:bg-[#334155] text-slate-400 hover:text-white transition-colors">
                        <svg class="w-4 h-4 transition-transform" :class="openDetail && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Expandable Detail Drawer --}}
            <div x-show="openDetail" x-collapse class="pt-4 border-t border-[#1e2d4a] space-y-3">
                @if($temuan->deskripsi)
                <div>
                    <h4 class="text-xs font-semibold text-[#64748b] uppercase tracking-wider mb-1">Deskripsi Kerentanan</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">{{ $temuan->deskripsi }}</p>
                </div>
                @endif

                @if($temuan->remediasi)
                <div class="p-3 rounded-lg bg-[#0f1629] border border-[#1e2d4a]">
                    <h4 class="text-xs font-semibold text-[#00d4ff] flex items-center gap-1.5 mb-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Panduan Remediasi</span>
                    </h4>
                    <p class="text-xs text-slate-300 leading-relaxed">{{ $temuan->remediasi }}</p>
                </div>
                @endif

                @if($temuan->status === 'selesai' && $temuan->resolved_at)
                <div class="text-[11px] text-emerald-400 bg-emerald-500/5 p-2.5 rounded border border-emerald-500/20 flex items-center justify-between">
                    <span>Diselesaikan pada {{ $temuan->resolved_at->format('d M Y, H:i') }} WIB oleh {{ $temuan->resolver?->name ?? 'User' }}</span>
                    @if($temuan->resolution_notes)
                    <span class="italic text-slate-300">"{{ $temuan->resolution_notes }}"</span>
                    @endif
                </div>
                @endif
            </div>

        </div>
        @empty
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] py-12 text-center text-[#64748b]">
            <div class="max-w-sm mx-auto space-y-3">
                <div class="w-12 h-12 rounded-xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-center mx-auto text-[#64748b]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <p class="text-sm font-medium text-slate-300">Tidak ada temuan kerentanan yang sesuai kriteria</p>
                <p class="text-xs text-[#64748b]">Coba sesuaikan kata kunci pencarian atau ubah filter keparahan dan status resolusi.</p>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($temuans->hasPages())
    <div class="pt-4">
        {{ $temuans->links() }}
    </div>
    @endif

</div>
@endsection
