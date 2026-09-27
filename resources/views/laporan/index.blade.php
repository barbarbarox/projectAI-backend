@extends('layouts.app')
@section('title', 'Riwayat Scan — RedSim')
@section('header', 'Riwayat Scan')
@section('subheader', 'Semua riwayat pemindaian keamanan Anda')

@section('content')
<div class="rs-card rounded-xl overflow-hidden">
    <div class="px-6 py-4 flex items-center justify-between" style="border-bottom:1px solid #1e293b;">
        <div class="flex items-center gap-3">
            <h3 class="font-semibold text-white">Riwayat Pemindaian</h3>
            <span class="badge-security">Security Log</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="rs-table">
            <thead>
                <tr>
                    <th>Target / File</th>
                    <th>Tipe</th>
                    <th>Tanggal</th>
                    <th>Skor</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($scans as $scan)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center"
                                 style="{{ $scan->tipe_scan === 'zip'
                                     ? 'background:rgba(34,197,94,0.10);'
                                     : 'background:rgba(220,38,38,0.10);' }}">
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
                            <span class="text-sm font-medium text-white truncate max-w-xs font-mono" style="font-family:'JetBrains Mono',monospace;font-size:0.8125rem;">
                                {{ Str::limit($scan->target ?? $scan->nama_file ?? 'Scan #' . substr($scan->id, 0, 8), 60) }}
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
                    <td class="text-xs" style="color:#64748b;font-family:monospace;">
                        {{ $scan->created_at->format('d M Y H:i') }}
                    </td>
                    <td>
                        @if($scan->skor_keamanan !== null)
                        <span class="text-sm font-bold {{ $scan->skor_keamanan >= 70 ? 'text-green-400' : ($scan->skor_keamanan >= 50 ? 'text-yellow-400' : 'text-red-400') }}">
                            {{ $scan->skor_keamanan }}
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
                    <td>
                        @if($scan->status === 'selesai')
                        <a href="{{ route('laporan.detail', $scan) }}"
                           class="inline-flex items-center gap-1 text-xs font-medium transition-colors duration-200 hover:underline"
                           style="color:#ef4444;">
                            Detail
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-16 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3" style="color:#1e293b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm mb-4" style="color:#64748b;">Belum ada riwayat pemindaian.</p>
                        <a href="{{ route('analisis.url') }}"
                           class="btn-security inline-flex">
                            Mulai Scan Pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($scans->hasPages())
    <div class="px-6 py-4" style="border-top:1px solid #1e293b;">
        {{ $scans->links() }}
    </div>
    @endif
</div>
@endsection
