@extends('layouts.app')

@section('title', 'Manajemen Insiden & Remediasi — RedSim SOC')
@section('header', 'Insiden Keamanan & Remediasi')
@section('subheader', 'Pelacakan siklus hidup kerentanan, investigasi insiden, dan verifikasi resolusi keamanan aset')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    {{-- Alert Flash Message --}}
    @if(session('pesan'))
    <div class="p-4 rounded-xl border flex items-center justify-between gap-3 text-sm transition-all
        {{ session('pesan')['tipe'] === 'sukses' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-cyan-500/10 border-cyan-500/30 text-cyan-300' }}">
        <div class="flex items-center gap-2.5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('pesan')['isi'] }}</span>
        </div>
    </div>
    @endif

    {{-- STATS CARDS ROW --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5">
        {{-- Total --}}
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 flex flex-col justify-between">
            <span class="text-xs text-[#64748b] font-medium uppercase tracking-wider">Total Insiden</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-white">{{ $stats['total'] }}</span>
                <span class="text-xs text-[#475569]">All-time</span>
            </div>
        </div>

        {{-- Open --}}
        <div class="rounded-xl bg-amber-500/5 border border-amber-500/20 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs text-amber-400/90 font-medium uppercase tracking-wider">Open (Baru)</span>
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-amber-400">{{ $stats['open'] }}</span>
                <span class="text-[11px] text-amber-400/60">Perlu Tindakan</span>
            </div>
        </div>

        {{-- In Progress (Investigating + Patching) --}}
        <div class="rounded-xl bg-cyan-500/5 border border-cyan-500/20 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs text-cyan-400/90 font-medium uppercase tracking-wider">Dalam Penanganan</span>
                <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-cyan-400">{{ $stats['investigating'] }}</span>
                <span class="text-[11px] text-cyan-400/60">Sedang Dikerjakan</span>
            </div>
        </div>

        {{-- Resolved --}}
        <div class="rounded-xl bg-emerald-500/5 border border-emerald-500/20 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs text-emerald-400/90 font-medium uppercase tracking-wider">Terselesaikan</span>
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-emerald-400">{{ $stats['resolved'] }}</span>
                <span class="text-[11px] text-emerald-400/60">Telah Diverifikasi</span>
            </div>
        </div>

        {{-- Kritis Aktif --}}
        <div class="col-span-2 md:col-span-1 rounded-xl bg-red-500/10 border border-red-500/30 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs text-red-400 font-bold uppercase tracking-wider">Kritis Aktif</span>
                @if($stats['kritis'] > 0)
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                @endif
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-red-400">{{ $stats['kritis'] }}</span>
                <span class="text-[11px] text-red-400/70">Prioritas Tinggi</span>
            </div>
        </div>
    </div>

    {{-- FILTER & SEARCH TOOLBAR --}}
    <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('insiden.index') }}" class="w-full flex-1 flex flex-wrap items-center gap-3">
            {{-- Search Bar --}}
            <div class="relative flex-1 min-w-[220px]">
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Cari nomor insiden, judul, target, PIC..."
                       class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3.5 py-2 text-xs text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff] pl-9">
                <svg class="w-4 h-4 text-[#475569] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            {{-- Status Filter --}}
            <select name="status"
                    onchange="this.form.submit()"
                    class="bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#00d4ff]">
                <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                <option value="investigating" {{ request('status') === 'investigating' ? 'selected' : '' }}>Investigating</option>
                <option value="patching" {{ request('status') === 'patching' ? 'selected' : '' }}>Patching</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>

            {{-- Keparahan Filter --}}
            <select name="keparahan"
                    onchange="this.form.submit()"
                    class="bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#00d4ff]">
                <option value="all" {{ request('keparahan') === 'all' || !request('keparahan') ? 'selected' : '' }}>Semua Keparahan</option>
                <option value="kritis" {{ request('keparahan') === 'kritis' ? 'selected' : '' }}>Kritis</option>
                <option value="tinggi" {{ request('keparahan') === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                <option value="sedang" {{ request('keparahan') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                <option value="rendah" {{ request('keparahan') === 'rendah' ? 'selected' : '' }}>Rendah</option>
            </select>

            <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#1e293b] hover:bg-[#334155] text-xs text-white font-medium transition-colors">
                Filter
            </button>
            @if(request()->anyFilled(['q', 'status', 'keparahan']))
            <a href="{{ route('insiden.index') }}" class="text-xs text-[#64748b] hover:text-[#94a3b8] transition-colors">
                Reset
            </a>
            @endif
        </form>

        {{-- Create Action Button --}}
        <div class="shrink-0">
            <a href="{{ route('insiden.create') }}"
               class="px-4 py-2 rounded-xl bg-gradient-to-r from-red-600 to-amber-600 hover:from-red-500 hover:to-amber-500 text-white text-xs font-semibold shadow-lg shadow-red-950/40 flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Insiden Baru</span>
            </a>
        </div>
    </div>

    {{-- INCIDENT LIST TABLE --}}
    <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#1e2d4a] bg-[#0f1629]/80 text-[11px] text-[#64748b] uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Insiden</th>
                        <th class="py-3 px-4">Judul Insiden & Target</th>
                        <th class="py-3 px-4">Keparahan</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">PIC / Assignee</th>
                        <th class="py-3 px-4">Dibuat</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d4a]/50 text-xs">
                    @forelse($insidens as $inc)
                    @php
                        // Helper styling keparahan
                        $sevBadge = match($inc->tingkat_keparahan) {
                            'kritis' => ['bg' => 'bg-red-500/15',    'border' => 'border-red-500/30',    'text' => 'text-red-400',    'label' => 'KRITIS'],
                            'tinggi' => ['bg' => 'bg-orange-500/15', 'border' => 'border-orange-500/30', 'text' => 'text-orange-400', 'label' => 'TINGGI'],
                            'sedang' => ['bg' => 'bg-yellow-500/15', 'border' => 'border-yellow-500/30', 'text' => 'text-yellow-400', 'label' => 'SEDANG'],
                            default  => ['bg' => 'bg-sky-500/15',    'border' => 'border-sky-500/30',    'text' => 'text-sky-400',    'label' => 'RENDAH'],
                        };

                        // Helper styling status
                        $statusBadge = match($inc->status) {
                            'open'          => ['bg' => 'bg-amber-500/15',   'border' => 'border-amber-500/30',   'text' => 'text-amber-400',   'label' => 'Open',          'dot' => 'bg-amber-400 animate-pulse'],
                            'investigating' => ['bg' => 'bg-purple-500/15',  'border' => 'border-purple-500/30',  'text' => 'text-purple-400',  'label' => 'Investigating', 'dot' => 'bg-purple-400'],
                            'patching'      => ['bg' => 'bg-cyan-500/15',    'border' => 'border-cyan-500/30',    'text' => 'text-cyan-400',    'label' => 'Patching',      'dot' => 'bg-cyan-400'],
                            'resolved'      => ['bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/30', 'text' => 'text-emerald-400', 'label' => 'Resolved',      'dot' => 'bg-emerald-400'],
                            'closed'        => ['bg' => 'bg-slate-500/15',   'border' => 'border-slate-500/30',   'text' => 'text-slate-400',   'label' => 'Closed',        'dot' => 'bg-slate-400'],
                            default         => ['bg' => 'bg-slate-500/15',   'border' => 'border-slate-500/30',   'text' => 'text-slate-400',   'label' => $inc->status,    'dot' => 'bg-slate-400'],
                        };
                    @endphp
                    <tr class="hover:bg-[#0f1629]/50 transition-colors">
                        {{-- No. Insiden --}}
                        <td class="py-3 px-4 font-mono font-semibold text-[#00d4ff] whitespace-nowrap">
                            <a href="{{ route('insiden.show', $inc->id) }}" class="hover:underline flex items-center gap-1.5">
                                <span>{{ $inc->nomor_insiden }}</span>
                                <svg class="w-3 h-3 text-[#475569]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </td>

                        {{-- Judul & Target --}}
                        <td class="py-3 px-4">
                            <div class="max-w-md">
                                <a href="{{ route('insiden.show', $inc->id) }}" class="font-medium text-white hover:text-[#00d4ff] transition-colors truncate block">
                                    {{ $inc->judul }}
                                </a>
                                <div class="text-[11px] text-[#64748b] flex items-center gap-2 mt-0.5 flex-wrap">
                                    @if($inc->target)
                                    <span class="font-mono text-slate-400 truncate max-w-xs">{{ $inc->target }}</span>
                                    @endif
                                    @if($inc->temuan_id)
                                    <span class="px-1.5 py-0.2 rounded text-[10px] bg-red-500/10 text-red-400 border border-red-500/20">Temuan Scan</span>
                                    @elseif($inc->scan_id)
                                    <span class="px-1.5 py-0.2 rounded text-[10px] bg-purple-500/10 text-purple-400 border border-purple-500/20">Subdomain/Scan</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Keparahan --}}
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $sevBadge['bg'] }} {{ $sevBadge['border'] }} {{ $sevBadge['text'] }}">
                                {{ $sevBadge['label'] }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold border flex items-center gap-1.5 w-max {{ $statusBadge['bg'] }} {{ $statusBadge['border'] }} {{ $statusBadge['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusBadge['dot'] }}"></span>
                                <span>{{ $statusBadge['label'] }}</span>
                            </span>
                        </td>

                        {{-- PIC / Assignee --}}
                        <td class="py-3 px-4 whitespace-nowrap text-slate-300">
                            {{ $inc->assigned_to ?? 'Belum Ditugaskan' }}
                        </td>

                        {{-- Dibuat --}}
                        <td class="py-3 px-4 whitespace-nowrap text-[#64748b] text-[11px]">
                            {{ $inc->created_at->format('d M Y, H:i') }}
                        </td>

                        {{-- Actions --}}
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('insiden.show', $inc->id) }}"
                                   class="px-2.5 py-1 rounded-lg bg-[#1e293b] hover:bg-[#334155] text-[11px] text-[#94a3b8] hover:text-white transition-colors">
                                    Detail
                                </a>
                                <form method="POST" action="{{ route('insiden.destroy', $inc->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus insiden ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded-lg hover:bg-red-500/10 text-[#64748b] hover:text-red-400 transition-colors" title="Hapus Insiden">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-[#64748b]">
                            <div class="max-w-sm mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-center mx-auto text-[#64748b]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-slate-300">Belum ada insiden keamanan yang terdaftar</p>
                                <p class="text-xs text-[#64748b]">Insiden dapat dibuat secara otomatis dari temuan scan kerentanan atau dibuat secara manual.</p>
                                <div class="pt-2">
                                    <a href="{{ route('insiden.create') }}" class="px-3.5 py-1.5 rounded-lg bg-[#1e293b] hover:bg-[#334155] text-xs text-white font-medium transition-colors inline-block">
                                        Buat Insiden Baru
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($insidens->hasPages())
        <div class="p-4 border-t border-[#1e2d4a]">
            {{ $insidens->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
