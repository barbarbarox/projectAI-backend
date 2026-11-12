@extends('layouts.app')

@section('title', 'Detail Monitor — ' . ($monitor->nama ?? $monitor->clean_url))
@section('header', $monitor->nama ?? $monitor->clean_url)
@section('subheader', 'History pemeriksaan & alert integritas')
@section('page_feature', 'monitoring')

@section('content')
<div class="space-y-6">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-emerald-300 border border-emerald-500/25" style="background:rgba(16,185,129,0.08);">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Monitor Info Card ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Main info --}}
        <div class="lg:col-span-2 rounded-2xl border p-5" style="background:rgba(15,23,42,0.6); border-color:#1e293b;">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ $monitor->nama ?? $monitor->clean_url }}</h2>
                    <a href="{{ $monitor->target_url }}" target="_blank" rel="noopener noreferrer"
                       class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors flex items-center gap-1 mt-0.5">
                        {{ $monitor->clean_url }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-lg border font-medium {{ $monitor->status_badge['class'] }}">
                    {{ $monitor->status_badge['label'] }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                <div class="px-3 py-2.5 rounded-xl text-center" style="background:rgba(255,255,255,0.02); border:1px solid #1e293b;">
                    <p class="text-xs text-slate-500 mb-1">Interval</p>
                    <p class="text-sm font-bold text-white">{{ $monitor->interval_menit }}m</p>
                </div>
                <div class="px-3 py-2.5 rounded-xl text-center" style="background:rgba(255,255,255,0.02); border:1px solid #1e293b;">
                    <p class="text-xs text-slate-500 mb-1">Threshold</p>
                    <p class="text-sm font-bold text-white">{{ $monitor->threshold_pct }}%</p>
                </div>
                <div class="px-3 py-2.5 rounded-xl text-center" style="background:rgba(255,255,255,0.02); border:1px solid #1e293b;">
                    <p class="text-xs text-slate-500 mb-1">Konten</p>
                    <p class="text-sm font-bold text-white">{{ $monitor->is_dynamic_content ? 'Dinamis' : 'Statis' }}</p>
                </div>
                <div class="px-3 py-2.5 rounded-xl text-center" style="background:rgba(255,255,255,0.02); border:1px solid #1e293b;">
                    <p class="text-xs text-slate-500 mb-1">Notif WA</p>
                    <p class="text-sm font-bold {{ $monitor->alert_wa ? 'text-emerald-400' : 'text-slate-400' }}">
                        {{ $monitor->alert_wa ? 'Aktif' : 'Nonaktif' }}
                    </p>
                </div>
            </div>

            {{-- Timestamps --}}
            <div class="text-xs text-slate-500 space-y-1">
                <p>Terakhir diperiksa: <span class="text-slate-300">{{ $monitor->last_checked_at ? $monitor->last_checked_at->format('d M Y, H:i') . ' WIB' : 'Belum diperiksa' }}</span></p>
                <p>Pemeriksaan berikutnya: <span class="text-slate-300">{{ $monitor->next_check_at ? $monitor->next_check_at->format('d M Y, H:i') . ' WIB (' . $monitor->next_check_at->diffForHumans() . ')' : '-' }}</span></p>
                <p>Monitor dibuat: <span class="text-slate-300">{{ $monitor->created_at->format('d M Y, H:i') }} WIB</span></p>
            </div>

            @if($monitor->status === 'error' && $monitor->error_message)
                <div class="flex items-start gap-2 mt-4 px-3 py-2.5 rounded-xl text-xs text-red-300" style="background:rgba(220,38,38,0.08); border:1px solid rgba(220,38,38,0.2);">
                    <svg class="w-3.5 h-3.5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Error: {{ $monitor->error_message }}</span>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="space-y-3">
            {{-- Baseline screenshot --}}
            @if($monitor->currentBaseline && $monitor->currentBaseline->screenshot_path)
                <div class="rounded-2xl border overflow-hidden" style="border-color:#1e293b;">
                    <img src="{{ asset('storage/' . $monitor->currentBaseline->screenshot_path) }}"
                         alt="Baseline screenshot" class="w-full h-36 object-cover object-top">
                    <p class="text-xs text-slate-500 px-3 py-2">Screenshot baseline · {{ $monitor->currentBaseline->snapshot_at->diffForHumans() }}</p>
                </div>
            @else
                <div class="rounded-2xl border p-4 text-center" style="background:rgba(15,23,42,0.4); border-color:#1e293b;">
                    <p class="text-xs text-slate-500">Screenshot baseline belum tersedia</p>
                </div>
            @endif

            <form action="{{ route('monitoring.toggle-pause', $monitor->id) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium border transition-all
                    {{ $monitor->status === 'active' ? 'text-amber-400 border-amber-500/30 hover:bg-amber-500/10' : 'text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/10' }}">
                    {{ $monitor->status === 'active' ? '⏸ Jeda Monitor' : '▶ Aktifkan Monitor' }}
                </button>
            </form>

            <form action="{{ route('monitoring.update-baseline', $monitor->id) }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-indigo-400 border border-indigo-500/30 hover:bg-indigo-500/10 transition-all">
                    🔄 Perbarui Baseline
                </button>
            </form>

            <form action="{{ route('monitoring.destroy', $monitor->id) }}" method="POST"
                  onsubmit="return confirm('Hapus monitor ini beserta semua data terkait?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-red-400 border border-red-500/20 hover:bg-red-500/10 transition-all">
                    🗑 Hapus Monitor
                </button>
            </form>

            <a href="{{ route('monitoring.index') }}" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 transition-all">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- ── Alert History ────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border" style="background:rgba(15,23,42,0.6); border-color:#1e293b;">
        <div class="px-5 py-4 border-b border-slate-700/50">
            <h3 class="text-base font-semibold text-white">History Alert</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ $alerts->total() }} total alert</p>
        </div>

        @if($alerts->isEmpty())
            <div class="px-5 py-12 text-center">
                <div class="text-4xl mb-3">✅</div>
                <p class="text-white font-medium text-sm">Tidak Ada Alert</p>
                <p class="text-xs text-slate-500 mt-1">Website tampak bersih. Belum ada perubahan mencurigakan yang terdeteksi.</p>
            </div>
        @else
            <div class="divide-y divide-slate-800">
                @foreach($alerts as $alert)
                    <a href="{{ route('monitoring.alert.show', $alert->id) }}"
                       class="flex items-center gap-4 px-5 py-4 hover:bg-slate-800/30 transition-all group">
                        {{-- Severity icon --}}
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-base">
                            {{ $alert->severity_badge['icon'] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-sm font-medium {{ $alert->alert_type === 'clean' ? 'text-slate-400' : 'text-white' }}">
                                    {{ $alert->alert_type_label }}
                                </span>
                                <span class="text-xs px-1.5 py-0.5 rounded border {{ $alert->severity_badge['class'] }}">
                                    {{ $alert->severity_badge['label'] }}
                                </span>
                                @if(!$alert->is_acknowledged && $alert->alert_type !== 'clean')
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500">
                                {{ $alert->detected_at->format('d M Y, H:i') }} WIB
                                @if($alert->diff_score > 0)
                                    · Skor Perubahan: <span class="font-medium text-slate-400">{{ $alert->diff_score }}/100</span>
                                @endif
                            </p>
                        </div>
                        <svg class="w-4 h-4 text-slate-600 group-hover:text-slate-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endforeach
            </div>
            @if($alerts->hasPages())
                <div class="px-5 py-4 border-t border-slate-700/50">
                    {{ $alerts->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
