@extends('layouts.app')

@section('title', 'Website Integrity Monitor — RedSim')
@section('header', 'Integrity Monitor')
@section('subheader', 'Pantau & deteksi perubahan mencurigakan pada website target')
@section('page_feature', 'monitoring')

@section('content')
<div class="space-y-6" x-data="monitoringDashboard()">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-emerald-300 border border-emerald-500/25"
             style="background: rgba(16,185,129,0.08);">
            <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{!! session('success') !!}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-red-300 border border-red-500/25"
             style="background: rgba(220,38,38,0.08);">
            <svg class="w-4 h-4 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>{!! session('error') !!}</span>
        </div>
    @endif

    {{-- ── Header bar ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white">Monitor Aktif</h2>
            <p class="text-sm mt-0.5" style="color:#64748b;">
                {{ $monitors->count() }} / 2 slot monitor digunakan
                @if($totalUnread > 0)
                    &nbsp;·&nbsp;
                    <span class="text-amber-400 font-medium">{{ $totalUnread }} alert belum dibaca</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Tombol Quick Check --}}
            <button @click="showQuickCheck = !showQuickCheck"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all border text-amber-400 hover:text-amber-300"
                    style="background:rgba(245,158,11,0.08); border-color:rgba(245,158,11,0.3);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Quick Check
            </button>
            {{-- Tombol Tambah Monitor --}}
            @if($monitors->count() < 2)
                <a href="{{ route('monitoring.create') }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
                   style="background:linear-gradient(135deg,#dc2626,#7c3aed);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Monitor
                </a>
            @else
                <span class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-slate-500 border border-slate-700 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Slot Penuh (2/2)
                </span>
            @endif
        </div>
    </div>

    {{-- ── Quick Check Panel (collapsible) ────────────────────────────────── --}}
    <div x-show="showQuickCheck" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         class="rounded-2xl border p-5 space-y-4"
         style="background:rgba(245,158,11,0.05); border-color:rgba(245,158,11,0.2);">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(245,158,11,0.15);">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-amber-300">Quick Defacement Check</p>
                <p class="text-xs text-slate-400">Scan sekali tanpa verifikasi domain. Cooldown 15 menit setelah scan.</p>
            </div>
        </div>

        @if($cooldownRemaining > 0)
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-amber-300 border border-amber-500/25"
                 style="background:rgba(245,158,11,0.08);">
                <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Cooldown aktif. Sisa: <strong x-text="formatSeconds({{ $cooldownRemaining }})"></strong></span>
            </div>
        @else
            <form action="{{ route('monitoring.quick-check') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="url" name="target_url" placeholder="https://contoh.com"
                       class="flex-1 px-4 py-2.5 rounded-xl text-sm text-white border placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-500/50"
                       style="background:rgba(15,23,42,0.6); border-color:#334155;" required>
                <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                    <input type="checkbox" name="disclaimer_check" value="1" required class="w-3.5 h-3.5 rounded accent-amber-500">
                    Saya punya izin untuk scan ini
                </label>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-amber-600 hover:bg-amber-500 transition-colors">
                    Scan Sekarang
                </button>
            </form>
        @endif
    </div>

    {{-- ── Monitor Cards Grid ───────────────────────────────────────────────── --}}
    @if($monitors->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center rounded-2xl border border-dashed border-slate-700" style="background:rgba(15,23,42,0.3);">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background:rgba(99,102,241,0.1);">
                <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.4" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h3 class="text-white font-semibold text-lg mb-2">Belum Ada Monitor</h3>
            <p class="text-slate-400 text-sm max-w-sm mb-6">Tambahkan monitor untuk memantau integritas website secara otomatis. Maksimal 2 monitor per akun.</p>
            <a href="{{ route('monitoring.create') }}"
               class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
               style="background:linear-gradient(135deg,#dc2626,#7c3aed);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Monitor Pertama
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($monitors as $monitor)
            <div class="rounded-2xl border p-5 transition-all hover:border-slate-600 group"
                 style="background:rgba(15,23,42,0.6); border-color:#1e293b;">
                {{-- Monitor header --}}
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                             style="background:{{ $monitor->status === 'active' ? 'rgba(16,185,129,0.12)' : ($monitor->status === 'error' ? 'rgba(239,68,68,0.12)' : 'rgba(100,116,139,0.12)') }};">
                            <svg class="w-5 h-5 {{ $monitor->status === 'active' ? 'text-emerald-400' : ($monitor->status === 'error' ? 'text-red-400' : 'text-slate-400') }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ $monitor->nama ?? $monitor->clean_url }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $monitor->clean_url }}</p>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-lg border font-medium shrink-0 {{ $monitor->status_badge['class'] }}">
                        {{ $monitor->status_badge['label'] }}
                    </span>
                </div>

                {{-- Stats --}}
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="text-center px-2 py-2 rounded-lg" style="background:rgba(255,255,255,0.02);">
                        <p class="text-xs text-slate-500 mb-0.5">Interval</p>
                        <p class="text-sm font-semibold text-white">{{ $monitor->interval_menit }}m</p>
                    </div>
                    <div class="text-center px-2 py-2 rounded-lg" style="background:rgba(255,255,255,0.02);">
                        <p class="text-xs text-slate-500 mb-0.5">Alert</p>
                        <p class="text-sm font-semibold {{ $monitor->unread_alerts_count > 0 ? 'text-amber-400' : 'text-white' }}">{{ $monitor->unread_alerts_count }}</p>
                    </div>
                    <div class="text-center px-2 py-2 rounded-lg" style="background:rgba(255,255,255,0.02);">
                        <p class="text-xs text-slate-500 mb-0.5">Terakhir Cek</p>
                        <p class="text-xs font-medium text-white">
                            {{ $monitor->last_checked_at ? $monitor->last_checked_at->diffForHumans() : 'Belum' }}
                        </p>
                    </div>
                </div>

                {{-- Latest alert --}}
                @if($monitor->latestAlert && $monitor->latestAlert->alert_type !== 'clean')
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg mb-4 text-xs"
                         style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.15);">
                        <span>{{ $monitor->latestAlert->severity_badge['icon'] }}</span>
                        <span class="text-red-300 font-medium">{{ $monitor->latestAlert->alert_type_label }}</span>
                        <span class="text-slate-500 ml-auto">{{ $monitor->latestAlert->detected_at->diffForHumans() }}</span>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('monitoring.show', $monitor->id) }}"
                       class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Detail
                    </a>
                    <form action="{{ route('monitoring.toggle-pause', $monitor->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium border transition-all
                                {{ $monitor->status === 'active' ? 'text-amber-400 border-amber-500/30 hover:bg-amber-500/10' : 'text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10' }}">
                            @if($monitor->status === 'active')
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Jeda
                            @else
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Aktifkan
                            @endif
                        </button>
                    </form>
                    <form action="{{ route('monitoring.destroy', $monitor->id) }}" method="POST"
                          onsubmit="return confirm('Hapus monitor ini? Semua data baseline dan alert akan ikut dihapus.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium text-red-400 border border-red-500/20 hover:bg-red-500/10 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- ── Link ke semua alert ──────────────────────────────────────────────── --}}
    @if($totalUnread > 0)
        <div class="flex items-center justify-between px-5 py-4 rounded-2xl border"
             style="background:rgba(245,158,11,0.05); border-color:rgba(245,158,11,0.2);">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⚠️</span>
                <div>
                    <p class="text-sm font-semibold text-amber-300">{{ $totalUnread }} Alert Belum Dibaca</p>
                    <p class="text-xs text-slate-400">Ada perubahan yang perlu ditinjau pada monitor Anda.</p>
                </div>
            </div>
            <a href="{{ route('monitoring.alerts') }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold text-amber-300 border border-amber-500/30 hover:bg-amber-500/10 transition-all">
                Lihat Semua Alert →
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
function monitoringDashboard() {
    return {
        showQuickCheck: false,
        cooldownSeconds: {{ $cooldownRemaining }},
        timerInterval: null,

        init() {
            if (this.cooldownSeconds > 0) {
                this.timerInterval = setInterval(() => {
                    this.cooldownSeconds = Math.max(0, this.cooldownSeconds - 1);
                    if (this.cooldownSeconds <= 0) clearInterval(this.timerInterval);
                }, 1000);
            }
        },

        formatSeconds(s) {
            const m = Math.floor(s / 60);
            const sec = s % 60;
            return m > 0 ? `${m} menit ${sec} detik` : `${sec} detik`;
        }
    };
}
</script>
@endpush
@endsection
