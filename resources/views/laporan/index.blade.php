@extends('layouts.app')
@section('title', 'Riwayat Scan — RedSim')
@section('header', 'Riwayat Scan')
@section('subheader', 'Semua riwayat pemindaian keamanan Anda')

@section('content')
<div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] overflow-hidden">
    <div class="divide-y divide-[#1e2d4a]">
        @forelse($scans as $scan)
        <a href="{{ $scan->status === 'selesai' ? route('laporan.detail', $scan) : '#' }}" class="flex items-center justify-between px-6 py-4 hover:bg-[#1e2d4a]/30 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $scan->tipe_scan === 'kode' ? 'bg-[#00d4ff]/10' : ($scan->tipe_scan === 'url' ? 'bg-[#7c3aed]/10' : 'bg-green-500/10') }}">
                    <span class="text-lg">{{ $scan->tipe_scan === 'kode' ? '💻' : ($scan->tipe_scan === 'url' ? '🔗' : '📦') }}</span>
                </div>
                <div>
                    <p class="text-sm font-medium">{{ Str::limit($scan->target ?? $scan->nama_file ?? 'Scan #' . substr($scan->id, 0, 8), 60) }}</p>
                    <p class="text-xs text-[#64748b]">{{ ucfirst($scan->tipe_scan) }} · {{ $scan->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($scan->skor_keamanan !== null)
                <span class="text-sm font-bold {{ $scan->skor_keamanan >= 70 ? 'text-green-400' : ($scan->skor_keamanan >= 50 ? 'text-yellow-400' : 'text-red-400') }}">{{ $scan->skor_keamanan }}</span>
                @endif
                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $scan->status === 'selesai' ? 'bg-green-500/10 text-green-400' : ($scan->status === 'gagal' ? 'bg-red-500/10 text-red-400' : 'bg-yellow-500/10 text-yellow-400') }}">
                    {{ $scan->status_label }}
                </span>
            </div>
        </a>
        @empty
        <div class="px-6 py-16 text-center">
            <p class="text-[#64748b]">Belum ada riwayat pemindaian.</p>
        </div>
        @endforelse
    </div>
    @if($scans->hasPages())
    <div class="px-6 py-4 border-t border-[#1e2d4a]">{{ $scans->links() }}</div>
    @endif
</div>
@endsection
