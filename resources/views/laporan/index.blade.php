@extends('layouts.app')
@section('title', 'Riwayat Scan — RedSim')
@section('header', 'Riwayat Scan')
@section('subheader', 'Kelola semua riwayat pemindaian keamanan Anda')
@section('page_feature', 'laporan')

@section('content')
@php
$totalRiwayat = $scans->total();
$selesai = $scans->getCollection()->where('status', 'selesai')->count();
$gagal = $scans->getCollection()->where('status', 'gagal')->count();
$avgSkor = $scans->getCollection()->whereNotNull('skor_keamanan')->avg('skor_keamanan');
@endphp

<div class="space-y-4">

{{-- SUB-NAVIGATION TABS (RIWAYAT | INSIDEN | RED TEAM) --}}
<div class="flex items-center justify-between gap-4 flex-wrap pb-2" style="border-bottom:1px solid #242424;">
    <div class="flex items-center gap-2">
        <a href="{{ route('laporan.index') }}"
           class="px-4 py-1.5 rounded-full text-xs font-semibold text-white transition-all"
           style="background:#ef4444; box-shadow:0 0 12px rgba(239,68,68,0.4);">
            Riwayat
        </a>
        <a href="{{ route('insiden.index') }}"
           class="px-4 py-1.5 rounded-full text-xs font-medium transition-all hover:bg-white/5"
           style="color:#94a3b8;">
            Insiden
        </a>
        <a href="{{ route('red-team-lab') }}"
           class="px-4 py-1.5 rounded-full text-xs font-medium transition-all hover:bg-white/5"
           style="color:#94a3b8;">
            Red Team Lab
        </a>
    </div>
    <button onclick="window.dispatchEvent(new CustomEvent('open-help', { detail: { feature: 'laporan' } }))"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
            style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Panduan Fitur
    </button>
</div>

{{-- Stat Cards Row --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    {{-- Total --}}
    <div style="background:#161616;border:1px solid #242424;border-radius:12px;padding:20px;">
        <div style="font-family:'IBM Plex Mono',monospace;font-size:10px;color:#555;letter-spacing:0.12em;text-transform:uppercase;margin-bottom:8px;">TOTAL RIWAYAT</div>
        <div style="font-size:28px;font-weight:800;color:#f6f6f8;font-family:'IBM Plex Mono',monospace;">{{ $totalRiwayat }}</div>
    </div>
    {{-- Avg Score --}}
    <div style="background:#1f1a08;border:1px solid #4b3a10;border-radius:12px;padding:20px;">
        <div style="font-family:'IBM Plex Mono',monospace;font-size:10px;color:#ca8a04;letter-spacing:0.12em;text-transform:uppercase;margin-bottom:8px;">RATA-RATA SKOR</div>
        <div style="font-size:28px;font-weight:800;color:#fbbf24;font-family:'IBM Plex Mono',monospace;">{{ $avgSkor ? round($avgSkor) : '—' }}</div>
    </div>
    {{-- Selesai --}}
    <div style="background:#081a0f;border:1px solid #14532d;border-radius:12px;padding:20px;">
        <div style="font-family:'IBM Plex Mono',monospace;font-size:10px;color:#16a34a;letter-spacing:0.12em;text-transform:uppercase;margin-bottom:8px;">SCAN SELESAI</div>
        <div style="font-size:28px;font-weight:800;color:#4ade80;font-family:'IBM Plex Mono',monospace;">{{ $selesai }}</div>
    </div>
    {{-- Gagal --}}
    <div style="background:#1b1212;border:1px solid #3a1f1f;border-radius:12px;padding:20px;">
        <div style="font-family:'IBM Plex Mono',monospace;font-size:10px;color:#ef4444;letter-spacing:0.12em;text-transform:uppercase;margin-bottom:8px;">SCAN GAGAL</div>
        <div style="font-size:28px;font-weight:800;color:#ef4444;font-family:'IBM Plex Mono',monospace;">{{ $gagal }}</div>
    </div>
</div>

<div x-data="scanHistory()" class="space-y-4">

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3"
         style="background:#161616;border:1px solid #242424;border-radius:12px;padding:14px 20px;margin-bottom:4px;">
        <div class="flex items-center gap-2 flex-wrap flex-1">
            <button x-show="selected.length > 0"
                    x-cloak
                    @click="confirmBulkDelete()"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all"
                    style="background:rgba(220,38,38,0.12);border:1px solid rgba(220,38,38,0.3);color:#f87171;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus (<span x-text="selected.length"></span>)
            </button>
            <button x-show="deletableScanIds.length > 0"
                    x-cloak
                    @click="toggleSelectAll()"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all"
                    style="background:#161616;border:1px solid #2a2a2a;color:#aaa;border-radius:8px;padding:8px 14px;font-size:12px;">
                <span x-text="selected.length === deletableScanIds.length && deletableScanIds.length > 0 ? 'Batalkan Semua' : 'Pilih Semua'"></span>
            </button>
            {{-- Search & Filter --}}
            <form method="GET" action="{{ route('laporan.index') }}" class="flex items-center gap-2 flex-wrap">
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Cari target..."
                           style="background:#111111;border:1px solid #2a2a2a;border-radius:8px;padding:7px 12px 7px 32px;font-size:12px;color:#ccc;outline:none;width:200px;"
                           onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#2a2a2a'">
                    <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2" style="color:#555;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <select name="status" onchange="this.form.submit()"
                        style="background:#111111;border:1px solid #2a2a2a;border-radius:8px;padding:7px 12px;font-size:12px;color:#ccc;outline:none;">
                    <option value="">Semua Status</option>
                    <option value="selesai"   {{ request('status')==='selesai'   ? 'selected':'' }}>Selesai</option>
                    <option value="gagal"     {{ request('status')==='gagal'     ? 'selected':'' }}>Gagal</option>
                    <option value="memproses" {{ request('status')==='memproses' ? 'selected':'' }}>Memproses</option>
                </select>
                @if(request()->anyFilled(['q','status']))
                <a href="{{ route('laporan.index') }}" style="font-size:11px;color:#666;">Reset</a>
                @endif
            </form>
        </div>
        <div style="color:#555;font-size:12px;white-space:nowrap;">{{ $scans->total() }} total riwayat</div>
    </div>

    {{-- Table Card --}}
    <div style="background:#161616;border:1px solid #242424;border-radius:12px;overflow:hidden;">
        <div class="px-6 py-4 flex items-center justify-between" style="border-bottom:1px solid #2a2a2a;">
            <div class="flex items-center gap-3">
                <h3 class="font-semibold text-white">Riwayat Pemindaian</h3>
                <span style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#ef4444;font-family:'IBM Plex Mono',monospace;font-size:10px;letter-spacing:0.1em;padding:3px 10px;border-radius:4px;">SECURITY LOG</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table style="width:100%;border-collapse:collapse;">
                <thead style="background:#111111;border-bottom:1px solid #242424;">
                    <tr>
                        <th style="padding:10px 16px;font-size:10px;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;width:40px;">
                            <input type="checkbox" class="w-4 h-4 rounded"
                                   style="accent-color:#ef4444;cursor:pointer;"
                                   @change="toggleSelectAll()"
                                   :checked="selected.length > 0 && selected.length === deletableScanIds.length"
                                   :indeterminate.prop="selected.length > 0 && selected.length < deletableScanIds.length">
                        </th>
                        <th style="padding:10px 16px;font-size:10px;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;text-align:left;">TARGET / FILE</th>
                        <th style="padding:10px 16px;font-size:10px;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;text-align:left;">TIPE</th>
                        <th style="padding:10px 16px;font-size:10px;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;text-align:left;">TANGGAL</th>
                        <th style="padding:10px 16px;font-size:10px;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;text-align:left;">SKOR</th>
                        <th style="padding:10px 16px;font-size:10px;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;text-align:left;">STATUS</th>
                        <th style="padding:10px 16px;font-size:10px;color:#555;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;text-align:left;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scans as $scan)
                    @php $isDeletable = !in_array($scan->status, ['memproses', 'antri']); @endphp
                    <tr :class="selected.includes('{{ $scan->id }}') ? 'bg-red-500/5' : ''"
                        style="border-bottom:1px solid #1e1e1e;transition:background 0.15s;"
                        onmouseover="if(!this.classList.contains('bg-red-500/5'))this.style.background='#1b1212'"
                        onmouseout="if(!this.classList.contains('bg-red-500/5'))this.style.background='transparent'">
                        <td style="padding:12px 16px;">
                            @if($isDeletable)
                            <input type="checkbox"
                                   class="w-4 h-4 rounded"
                                   style="accent-color:#ef4444;cursor:pointer;"
                                   value="{{ $scan->id }}"
                                   @change="toggleSelect('{{ $scan->id }}')"
                                   :checked="selected.includes('{{ $scan->id }}')">
                            @else
                            <span class="w-4 h-4 flex items-center justify-center opacity-30" title="Sedang diproses">
                                <svg class="w-3 h-3 text-yellow-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center"
                                     style="{{ $scan->tipe_scan === 'zip' ? 'background:rgba(34,197,94,0.10);' : 'background:rgba(220,38,38,0.10);' }}">
                                    @if($scan->tipe_scan === 'kode')
                                    <svg class="w-4 h-4" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                    @elseif($scan->tipe_scan === 'url')
                                    <svg class="w-4 h-4" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3"/></svg>
                                    @else
                                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4"/></svg>
                                    @endif
                                </div>
                                <span class="text-sm font-medium text-white truncate max-w-xs" style="font-family:'JetBrains Mono',monospace;font-size:0.8125rem;">
                                    {{ Str::limit($scan->target ?? $scan->nama_file ?? 'Scan #' . substr($scan->id, 0, 8), 55) }}
                                </span>
                            </div>
                        </td>
                        <td style="padding:12px 16px;">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold uppercase"
                                  style="background:rgba(220,38,38,0.10);color:#f87171;">
                                {{ $scan->tipe_scan }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;font-size:12px;color:#64748b;font-family:monospace;">{{ $scan->created_at->format('d M Y H:i') }}</td>
                        <td style="padding:12px 16px;">
                            @if($scan->skor_keamanan !== null)
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="font-size:13px;font-weight:700;{{ $scan->skor_keamanan >= 70 ? 'color:#4ade80' : ($scan->skor_keamanan >= 50 ? 'color:#fbbf24' : 'color:#ef4444') }}">{{ $scan->skor_keamanan }}</span>
                                <div style="width:50px;height:4px;background:#2a2a2a;border-radius:2px;overflow:hidden;">
                                    <div style="width:{{ $scan->skor_keamanan }}%;height:100%;background:{{ $scan->skor_keamanan >= 70 ? '#4ade80' : ($scan->skor_keamanan >= 50 ? '#fbbf24' : '#ef4444') }};border-radius:2px;"></div>
                                </div>
                            </div>
                            @else<span style="color:#333;">—</span>@endif
                        </td>
                        <td style="padding:12px 16px;">
                            @if($scan->status === 'selesai')
                            <span style="background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.3);color:#4ade80;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                {{ $scan->status_label }}
                            </span>
                            @elseif($scan->status === 'gagal')
                            <span style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                {{ $scan->status_label }}
                            </span>
                            @else
                            <span style="background:rgba(234,179,8,0.1);border:1px solid rgba(234,179,8,0.3);color:#fbbf24;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                {{ $scan->status_label }}
                            </span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;">
                            <div class="flex items-center gap-3">
                                @if($scan->status === 'selesai')
                                <a href="{{ route('laporan.detail', $scan) }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium transition-colors hover:underline" style="color:#ef4444;">
                                    Detail <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <a href="{{ route('laporan.bandingkan', $scan) }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-medium transition-colors" style="color:#ef4444;" title="Bandingkan">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                </a>
                                @endif
                                @if($isDeletable)
                                <button @click="confirmDelete('{{ $scan->id }}', '{{ addslashes(Str::limit($scan->target ?? $scan->nama_file ?? 'Scan #' . substr($scan->id, 0, 8), 40)) }}')"
                                        class="inline-flex items-center gap-1 text-xs font-medium transition-colors hover:text-red-400" style="color:#475569;" title="Hapus">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <svg class="w-12 h-12 mx-auto mb-3" style="color:#1e293b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm mb-4" style="color:#64748b;">Belum ada riwayat pemindaian.</p>
                            <a href="{{ route('analisis.url') }}" class="btn-security inline-flex">Mulai Scan Pertama</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($scans->hasPages())
        <div class="px-6 py-4" style="border-top:1px solid #2a2a2a;">{{ $scans->links() }}</div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-show="showModal" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[200] flex items-center justify-center p-4"
         style="background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);">
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             @click.outside="showModal = false"
             class="relative w-full max-w-sm rounded-2xl p-6"
             style="background:#161616;border:1px solid #242424;box-shadow:0 25px 50px rgba(0,0,0,0.5);">
            <div class="flex items-start gap-4 mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                     style="background:rgba(220,38,38,0.12);border:1px solid rgba(220,38,38,0.25);">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-white mb-1">Hapus Riwayat Scan</h3>
                    <p class="text-sm" style="color:#94a3b8;">Tindakan ini tidak dapat dibatalkan. Data scan, temuan, dan simulasi terkait akan dihapus permanen.</p>
                    <p x-show="deleteTarget" x-cloak class="mt-2 text-xs font-mono px-2 py-1 rounded" style="background:rgba(0,0,0,0.4);color:#f87171;" x-text="`Target: ${deleteTarget}`"></p>
                    <p x-show="bulkCount > 1" x-cloak class="mt-2 text-xs" style="color:#f87171;" x-text="`Akan menghapus ${bulkCount} riwayat scan.`"></p>
                </div>
            </div>
            <div class="flex gap-3">
                <button @click="showModal = false" class="flex-1 py-2.5 rounded-xl text-sm font-medium transition-all" style="background:rgba(30,41,59,0.6);border:1px solid #2a2a2a;color:#94a3b8;">Batal</button>
                <button @click="executeDelete()" :disabled="deleting" class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-all disabled:opacity-60" style="background:linear-gradient(135deg,#dc2626,#991b1b);color:#fff;">
                    <span x-show="!deleting">Hapus Permanen</span>
                    <span x-show="deleting" class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Menghapus...
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── PANDUAN RIWAYAT SCAN (MATCHING POPUP MODAL PETUNJUK STYLE) ── --}}
    <div class="rounded-xl p-4 flex items-start justify-between gap-4 flex-wrap mt-2"
         style="background:#1b1212; border:1px solid #3a1f1f;">
        <div class="flex items-start gap-3.5 max-w-3xl">
            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                 style="border:1.5px solid #ef4444; color:#ef4444; font-size:11px; font-weight:800;">!</div>
            <div>
                <p style="color:#ef4444; font-size:0.62rem; letter-spacing:0.14em; text-transform:uppercase; font-weight:700; font-family:'IBM Plex Mono',monospace; margin-bottom:0.25rem;">
                    PANDUAN RIWAYAT &amp; LAPORAN SCAN
                </p>
                <p style="color:#888; font-size:0.78rem; line-height:1.65;">
                    Semua riwayat pemindaian tersimpan di sini. Klik <strong style="color:#ccc;">Detail</strong> untuk membuka laporan lengkap, unduh PDF, atau gunakan fitur
                    <strong style="color:#ccc;">Bandingkan</strong> untuk melihat perubahan skor antar waktu. Gunakan filter Status dan kolom pencarian untuk menemukan scan tertentu.
                </p>
            </div>
        </div>
        <button onclick="window.dispatchEvent(new CustomEvent('open-help', { detail: { feature: 'laporan' } }))"
                class="px-4 py-2 rounded-lg text-xs font-semibold text-white transition-all hover:opacity-90 shrink-0"
                style="background:linear-gradient(135deg,#ef4444,#dc2626); box-shadow:0 2px 10px rgba(239,68,68,0.3);">
            Buka Panduan
        </button>
    </div>

</div>

@push('scripts')
<script>
function scanHistory() {
    return {
        selected: [],
        showModal: false,
        deleteId: null,
        deleteTarget: '',
        bulkCount: 0,
        deleting: false,
        deletableScanIds: @json($scans->filter(fn($s) => !in_array($s->status, ['memproses', 'antri']))->pluck('id')->values()),

        toggleSelect(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(s => s !== id);
            } else {
                this.selected.push(id);
            }
        },

        toggleSelectAll() {
            if (this.selected.length === this.deletableScanIds.length && this.deletableScanIds.length > 0) {
                this.selected = [];
            } else {
                this.selected = [...this.deletableScanIds];
            }
        },

        confirmDelete(id, target) {
            this.deleteId = id;
            this.deleteTarget = target;
            this.bulkCount = 1;
            this.showModal = true;
        },

        confirmBulkDelete() {
            this.deleteId = null;
            this.deleteTarget = '';
            this.bulkCount = this.selected.length;
            this.showModal = true;
        },

        async executeDelete() {
            this.deleting = true;
            const ids = this.deleteId ? [this.deleteId] : [...this.selected];
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            try {
                for (const id of ids) {
                    await fetch(`/laporan/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                    });
                }
                window.location.reload();
            } catch (e) {
                this.deleting = false;
                this.showModal = false;
                alert('Gagal menghapus. Silakan coba lagi.');
            }
        }
    }
}
</script>
@endpush
@endsection
