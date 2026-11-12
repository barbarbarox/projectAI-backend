@extends('layouts.app')

@section('title', $insiden->nomor_insiden . ' — ' . $insiden->judul)
@section('header', 'Detail Insiden ' . $insiden->nomor_insiden)
@section('subheader', 'Pelacakan triase, investigasi mendalam, dan verifikasi perbaikan kerentanan')
@section('page_feature', 'insiden')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12" x-data="{ editing: false }">

    {{-- Breadcrumb Navigation --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-2 text-xs text-[#64748b]">
            <a href="{{ route('insiden.index') }}" class="hover:text-[#00d4ff] transition-colors">Manajemen Insiden</a>
            <span>/</span>
            <span class="text-white font-mono">{{ $insiden->nomor_insiden }}</span>
        </div>

        <div class="flex items-center gap-2">
            @if($insiden->scan_id)
            <a href="{{ route('laporan.detail', $insiden->scan_id) }}"
               class="px-3 py-1.5 rounded-lg bg-[#1e293b] hover:bg-[#334155] text-xs text-slate-300 hover:text-white transition-colors flex items-center gap-1.5 border border-[#334155]/60">
                <svg class="w-3.5 h-3.5 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Buka Laporan Scan</span>
            </a>
            @endif

            <form method="POST" action="{{ route('insiden.destroy', $insiden->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tiket insiden ini?');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-3 py-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-xs text-red-400 border border-red-500/30 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Hapus Insiden</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('pesan'))
    <div class="p-4 rounded-xl border flex items-center justify-between gap-3 text-sm
        {{ session('pesan')['tipe'] === 'sukses' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-cyan-500/10 border-cyan-500/30 text-cyan-300' }}">
        <div class="flex items-center gap-2.5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('pesan')['isi'] }}</span>
        </div>
    </div>
    @endif

    {{-- TOP INCIDENT BANNER & LIFECYCLE STEPPER --}}
    <div class="rounded-2xl bg-[#0a0e1a] border border-[#1e2d4a] p-6 shadow-xl relative overflow-hidden space-y-6">
        @php
            $sevBadge = match($insiden->tingkat_keparahan) {
                'kritis' => ['bg' => 'bg-red-500/15',    'border' => 'border-red-500/40',    'text' => 'text-red-400',    'label' => 'KRITIS'],
                'tinggi' => ['bg' => 'bg-orange-500/15', 'border' => 'border-orange-500/40', 'text' => 'text-orange-400', 'label' => 'TINGGI'],
                'sedang' => ['bg' => 'bg-yellow-500/15', 'border' => 'border-yellow-500/40', 'text' => 'text-yellow-400', 'label' => 'SEDANG'],
                default  => ['bg' => 'bg-sky-500/15',    'border' => 'border-sky-500/40',    'text' => 'text-sky-400',    'label' => 'RENDAH'],
            };

            $stages = [
                'open'          => ['label' => '1. Open',          'desc' => 'Identifikasi & Triase'],
                'investigating' => ['label' => '2. Investigating', 'desc' => 'Analisis Akar Masalah'],
                'patching'      => ['label' => '3. Patching',      'desc' => 'Penerapan Solusi'],
                'resolved'      => ['label' => '4. Resolved',      'desc' => 'Verifikasi Selesai'],
                'closed'        => ['label' => '5. Closed',        'desc' => 'Tiket Ditutup'],
            ];

            $stageKeys = array_keys($stages);
            $currentIdx = array_search($insiden->status, $stageKeys);
            if ($currentIdx === false) $currentIdx = 0;
        @endphp

        <div class="flex items-start justify-between flex-wrap gap-4">
            <div class="space-y-1.5 max-w-3xl">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="px-2.5 py-1 rounded-lg bg-[#0f1629] border border-[#1e2d4a] text-xs font-mono font-bold text-[#00d4ff]">
                        {{ $insiden->nomor_insiden }}
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $sevBadge['bg'] }} {{ $sevBadge['border'] }} {{ $sevBadge['text'] }}">
                        {{ $sevBadge['label'] }}
                    </span>
                    @if($insiden->target)
                    <span class="text-xs font-mono text-slate-400 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3"/></svg>
                        <span>{{ $insiden->target }}</span>
                    </span>
                    @endif
                </div>

                <h1 class="text-xl font-bold text-white tracking-wide">
                    {{ $insiden->judul }}
                </h1>
                <p class="text-xs text-[#64748b]">
                    Dilaporkan pada {{ $insiden->created_at->format('d M Y, H:i') }} WIB oleh {{ $insiden->user?->name ?? 'System' }}
                    @if($insiden->resolved_at)
                    · <span class="text-emerald-400 font-medium">Diselesaikan pada {{ $insiden->resolved_at->format('d M Y, H:i') }} WIB</span>
                    @endif
                </p>
            </div>

            {{-- Assignee Box --}}
            <div class="p-3.5 rounded-xl bg-[#0f1629] border border-[#1e2d4a] text-right shrink-0">
                <span class="text-[10px] text-[#64748b] uppercase tracking-wider block">Penanggung Jawab (PIC)</span>
                <span class="text-xs font-bold text-white mt-0.5 block">{{ $insiden->assigned_to ?? 'Belum Ditugaskan' }}</span>
            </div>
        </div>

        {{-- VISUAL LIFECYCLE STEPPER --}}
        <div class="pt-4 border-t border-[#1e2d4a]">
            <span class="text-[10px] text-[#64748b] uppercase tracking-wider font-semibold block mb-3">
                Siklus Penanganan Insiden (Incident Response Lifecycle)
            </span>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                @foreach($stages as $key => $st)
                @php
                    $stepIdx = array_search($key, $stageKeys);
                    $isPassed = $stepIdx < $currentIdx;
                    $isCurrent = $stepIdx === $currentIdx;
                @endphp
                <form method="POST" action="{{ route('insiden.update', $insiden->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="{{ $key }}">
                    <button type="submit"
                            title="Klik untuk ubah status ke {{ $st['label'] }}"
                            class="w-full text-left p-3 rounded-xl border transition-all relative overflow-hidden group
                            {{ $isCurrent
                                ? 'bg-cyan-500/10 border-[#00d4ff] shadow-[0_0_15px_rgba(0,212,255,0.2)]'
                                : ($isPassed
                                    ? 'bg-emerald-500/5 border-emerald-500/30 hover:border-emerald-500/60'
                                    : 'bg-[#0f1629]/60 border-[#1e2d4a] hover:border-slate-700 opacity-60 hover:opacity-100') }}">
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold
                                {{ $isCurrent ? 'text-[#00d4ff]' : ($isPassed ? 'text-emerald-400' : 'text-slate-400') }}">
                                {{ $st['label'] }}
                            </span>
                            @if($isPassed)
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @elseif($isCurrent)
                                <span class="w-2 h-2 rounded-full bg-[#00d4ff] animate-ping"></span>
                            @endif
                        </div>
                        <p class="text-[10px] text-[#64748b] mt-1">{{ $st['desc'] }}</p>
                    </button>
                </form>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 2-COL MAIN GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT COLUMN (2 Cols): Investigation, Remediation & Verification --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. Deskripsi & Dampak --}}
            <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Deskripsi Kerentanan & Dampak</span>
                    </h3>
                </div>
                <div class="text-xs text-slate-300 leading-relaxed bg-[#0f1629] p-4 rounded-xl border border-[#1e2d4a]/80 whitespace-pre-line font-sans">
                    {{ $insiden->deskripsi ?: 'Tidak ada rincian deskripsi tambahan yang dicatat.' }}
                </div>
            </div>

            {{-- 2. Tautan Temuan Scan (Jika Ditautkan ke Temuan) --}}
            @if($insiden->temuan)
            @php $t = $insiden->temuan; @endphp
            <div class="rounded-xl bg-[#0a0e1a] border border-red-500/30 p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-red-400 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Bukti Temuan Otomatis (Scan Finding)</span>
                    </h3>
                    @if($t->cve_id)
                    <a href="{{ route('analisis.cve-intel') }}?cve={{ urlencode($t->cve_id) }}"
                       target="_blank"
                       class="text-[11px] font-mono text-[#00d4ff] hover:underline flex items-center gap-1">
                        <span>{{ $t->cve_id }}</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    @endif
                </div>

                <div class="p-3.5 rounded-xl bg-[#0f1629] border border-[#1e2d4a] text-xs space-y-2">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="font-bold text-white">{{ $t->judul }}</span>
                        <span class="text-[10px] text-[#64748b]">
                            @if($t->cwe_id) {{ $t->cwe_id }} · @endif
                            @if($t->lokasi) Endpoint: <span class="text-slate-300 font-mono">{{ $t->lokasi }}</span> @endif
                        </span>
                    </div>

                    @if($t->kode_rentan)
                    <div class="mt-2">
                        <span class="text-[10px] text-red-400 font-semibold uppercase">Potongan Kode / Respons Rentan</span>
                        <pre class="mt-1 p-2.5 rounded bg-black/60 border border-red-500/20 text-xs font-mono text-red-300 overflow-x-auto"><code>{{ $t->kode_rentan }}</code></pre>
                    </div>
                    @endif

                    @if($t->kode_aman)
                    <div class="mt-2">
                        <span class="text-[10px] text-emerald-400 font-semibold uppercase">Rekomendasi Kode Aman</span>
                        <pre class="mt-1 p-2.5 rounded bg-black/60 border border-emerald-500/20 text-xs font-mono text-emerald-300 overflow-x-auto"><code>{{ $t->kode_aman }}</code></pre>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- 3. Investigasi & Remediasi Form Editor --}}
            <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Catatan Investigasi & Tindakan Remediasi</span>
                    </h3>
                </div>

                <form method="POST" action="{{ route('insiden.update', $insiden->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-1.5">
                                Status Insiden
                            </label>
                            <select name="status" class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#00d4ff]">
                                <option value="open" {{ $insiden->status === 'open' ? 'selected' : '' }}>Open (Baru)</option>
                                <option value="investigating" {{ $insiden->status === 'investigating' ? 'selected' : '' }}>Investigating (Analisis Tim)</option>
                                <option value="patching" {{ $insiden->status === 'patching' ? 'selected' : '' }}>Patching (Perbaikan Kode)</option>
                                <option value="resolved" {{ $insiden->status === 'resolved' ? 'selected' : '' }}>Resolved (Sudah Ditambal)</option>
                                <option value="closed" {{ $insiden->status === 'closed' ? 'selected' : '' }}>Closed (Selesai & Tertutup)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-1.5">
                                PIC Penanganan
                            </label>
                            <input type="text"
                                   name="assigned_to"
                                   value="{{ old('assigned_to', $insiden->assigned_to) }}"
                                   placeholder="Nama PIC"
                                   class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#00d4ff]">
                        </div>
                    </div>

                    {{-- Catatan Investigasi --}}
                    <div>
                        <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-1.5">
                            Catatan Investigasi & Temuan Forensik
                        </label>
                        <textarea name="catatan_investigasi"
                                  rows="4"
                                  placeholder="Tuliskan catatan teknis hasil reproduksi kerentanan, CVE correlation, log server, payload..."
                                  class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl p-3 text-xs text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff] leading-relaxed">{{ old('catatan_investigasi', $insiden->catatan_investigasi) }}</textarea>
                    </div>

                    {{-- Solusi Remediasi --}}
                    <div>
                        <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-1.5">
                            Solusi Remediasi & Langkah Perbaikan
                        </label>
                        <textarea name="solusi_remediasi"
                                  rows="4"
                                  placeholder="Dokumentasikan commit patch, update dependency, konfigurasi Nginx/Apache, atau DNS fix..."
                                  class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl p-3 text-xs text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff] leading-relaxed">{{ old('solusi_remediasi', $insiden->solusi_remediasi) }}</textarea>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white text-xs font-semibold shadow-lg shadow-cyan-950/40 transition-all flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Perbarui Data Insiden</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- RIGHT COLUMN (1 Col): Verification Checklist & Audit Meta --}}
        <div class="space-y-6">

            {{-- 1. Penutupan & Checklist Verifikasi SOC --}}
            <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-5 space-y-4">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Checklist Verifikasi Resolusi</span>
                </h3>
                <p class="text-[11px] text-[#64748b]">
                    Pastikan standar keamanan telah dipenuhi sebelum mengubah status insiden menjadi <b>Resolved</b> atau <b>Closed</b>.
                </p>

                <div class="space-y-2.5 text-xs text-slate-300">
                    <label class="flex items-start gap-2.5 p-2 rounded-lg bg-[#0f1629] border border-[#1e2d4a] cursor-pointer hover:bg-[#1e293b]/50 transition-colors">
                        <input type="checkbox" class="mt-0.5 rounded border-[#1e2d4a] text-[#00d4ff] focus:ring-0">
                        <span class="text-[11px]">Patch kode sumber / konfigurasi telah diterapkan di staging/production</span>
                    </label>

                    <label class="flex items-start gap-2.5 p-2 rounded-lg bg-[#0f1629] border border-[#1e2d4a] cursor-pointer hover:bg-[#1e293b]/50 transition-colors">
                        <input type="checkbox" class="mt-0.5 rounded border-[#1e2d4a] text-[#00d4ff] focus:ring-0">
                        <span class="text-[11px]">Pemindaian ulang (re-scan) mengonfirmasi kerentanan sudah hilang</span>
                    </label>

                    <label class="flex items-start gap-2.5 p-2 rounded-lg bg-[#0f1629] border border-[#1e2d4a] cursor-pointer hover:bg-[#1e293b]/50 transition-colors">
                        <input type="checkbox" class="mt-0.5 rounded border-[#1e2d4a] text-[#00d4ff] focus:ring-0">
                        <span class="text-[11px]">DNS dangling record / CNAME telah dihapus (khusus takeover risk)</span>
                    </label>

                    <label class="flex items-start gap-2.5 p-2 rounded-lg bg-[#0f1629] border border-[#1e2d4a] cursor-pointer hover:bg-[#1e293b]/50 transition-colors">
                        <input type="checkbox" class="mt-0.5 rounded border-[#1e2d4a] text-[#00d4ff] focus:ring-0">
                        <span class="text-[11px]">Tidak ada dampak regresi fungsionalitas aplikasi</span>
                    </label>
                </div>
            </div>

            {{-- 2. Audit Timeline & SLA Tracker --}}
            <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-5 space-y-4">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Timeline & Metrik SLA</span>
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-[#0f1629] border border-[#1e2d4a]">
                        <span class="text-[#64748b]">Waktu Dibuat:</span>
                        <span class="text-white font-mono">{{ $insiden->created_at->format('d/m/Y H:i') }}</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-[#0f1629] border border-[#1e2d4a]">
                        <span class="text-[#64748b]">Pembaruan Terakhir:</span>
                        <span class="text-white font-mono">{{ $insiden->updated_at->diffForHumans() }}</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-[#0f1629] border border-[#1e2d4a]">
                        <span class="text-[#64748b]">Status Resolusi:</span>
                        @if($insiden->resolved_at)
                            <span class="text-emerald-400 font-semibold font-mono">{{ $insiden->resolved_at->diffForHumans($insiden->created_at, true) }} (Selesai)</span>
                        @else
                            <span class="text-amber-400 font-semibold font-mono">Masih Berjalan ({{ $insiden->created_at->diffForHumans(null, true) }})</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 3. Shortcut Pindai Ulang Target --}}
            @if($insiden->target)
            <div class="rounded-xl bg-gradient-to-br from-indigo-950/40 to-[#0a0e1a] border border-indigo-500/30 p-5 space-y-3">
                <span class="text-xs font-bold text-indigo-400 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Verifikasi Ulang Target</span>
                </span>
                <p class="text-[11px] text-indigo-200/80 leading-relaxed">
                    Jalankan pemindaian ulang otomatis pada target untuk memverifikasi apakah celah keamanan ini masih terbuka.
                </p>
                <a href="{{ route('analisis.url') }}?target={{ urlencode(str_starts_with($insiden->target, 'http') ? $insiden->target : 'https://' . $insiden->target) }}"
                   class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-colors flex items-center justify-center gap-2 shadow-lg shadow-indigo-950/40">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Mulai Pemindaian Ulang</span>
                </a>
            </div>
            @endif

        </div>

    </div>

</div>
@endsection
