@extends('layouts.app')
@section('title', 'Riwayat Scan — RedSim')
@section('header', 'Riwayat Scan')
@section('subheader', 'Kelola semua riwayat pemindaian keamanan Anda')

@section('content')
<div x-data="scanHistory()" class="space-y-4">

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 flex-wrap">
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
                    style="background:rgba(30,41,59,0.8);border:1px solid #1e293b;color:#94a3b8;">
                <span x-text="selected.length === deletableScanIds.length && deletableScanIds.length > 0 ? 'Batalkan Semua' : 'Pilih Semua'"></span>
            </button>
        </div>
        <div class="text-xs" style="color:#475569;">{{ $scans->total() }} total riwayat</div>
    </div>

    {{-- Table Card --}}
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
                        <th class="w-10">
                            <input type="checkbox" class="w-4 h-4 rounded"
                                   style="accent-color:#ef4444;cursor:pointer;"
                                   @change="toggleSelectAll()"
                                   :checked="selected.length > 0 && selected.length === deletableScanIds.length"
                                   :indeterminate.prop="selected.length > 0 && selected.length < deletableScanIds.length">
                        </th>
                        <th>Target / File</th>
                        <th>Tipe</th>
                        <th>Tanggal</th>
                        <th>Skor</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scans as $scan)
                    @php $isDeletable = !in_array($scan->status, ['memproses', 'antri']); @endphp
                    <tr :class="selected.includes('{{ $scan->id }}') ? 'bg-red-500/5' : ''" class="transition-colors">
                        <td>
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
                        <td>
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
                        <td>
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold uppercase"
                                  style="{{ $scan->tipe_scan === 'zip' ? 'background:rgba(34,197,94,0.10);color:#4ade80;' : 'background:rgba(220,38,38,0.10);color:#f87171;' }}">
                                {{ $scan->tipe_scan }}
                            </span>
                        </td>
                        <td class="text-xs" style="color:#64748b;font-family:monospace;">{{ $scan->created_at->format('d M Y H:i') }}</td>
                        <td>
                            @if($scan->skor_keamanan !== null)
                            <span class="text-sm font-bold {{ $scan->skor_keamanan >= 70 ? 'text-green-400' : ($scan->skor_keamanan >= 50 ? 'text-yellow-400' : 'text-red-400') }}">{{ $scan->skor_keamanan }}</span>
                            @else<span style="color:#334155;">—</span>@endif
                        </td>
                        <td>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $scan->status === 'selesai' ? 'bg-green-500/10 text-green-400' : ($scan->status === 'gagal' ? 'bg-red-500/10 text-red-400' : 'bg-yellow-500/10 text-yellow-400') }}">
                                {{ $scan->status_label }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                @if($scan->status === 'selesai')
                                <a href="{{ route('laporan.detail', $scan) }}"
                                   class="inline-flex items-center gap-1 text-xs font-medium transition-colors hover:underline" style="color:#ef4444;">
                                    Detail <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <a href="{{ route('laporan.bandingkan', $scan) }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-medium transition-colors hover:text-cyan-300" style="color:#06b6d4;" title="Bandingkan">
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
        <div class="px-6 py-4" style="border-top:1px solid #1e293b;">{{ $scans->links() }}</div>
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
             style="background:#0f1629;border:1px solid #1e2d4a;box-shadow:0 25px 50px rgba(0,0,0,0.5);">
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
                <button @click="showModal = false" class="flex-1 py-2.5 rounded-xl text-sm font-medium transition-all" style="background:rgba(30,41,59,0.6);border:1px solid #1e293b;color:#94a3b8;">Batal</button>
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
