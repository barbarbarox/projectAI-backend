@extends('layouts.app')

@section('title', 'Semua Alert Integritas — RedSim')
@section('header', 'Alert Integritas')
@section('subheader', 'Semua deteksi perubahan dari seluruh monitor Anda')
@section('page_feature', 'alert-integritas')

@section('content')
<div class="space-y-5">

    {{-- Stats row --}}
    @php
        $criticalCount = $alerts->getCollection()->where('severity', 'critical')->count();
        $highCount     = $alerts->getCollection()->where('severity', 'high')->count();
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-2xl border px-4 py-3" style="background:rgba(15,23,42,0.6); border-color:#1e293b;">
            <p class="text-xs text-slate-500 mb-1">Total Alert</p>
            <p class="text-2xl font-bold text-white">{{ $alerts->total() }}</p>
        </div>
        <div class="rounded-2xl border px-4 py-3" style="background:rgba(220,38,38,0.05); border-color:rgba(220,38,38,0.2);">
            <p class="text-xs text-red-400 mb-1">Kritis</p>
            <p class="text-2xl font-bold text-red-300">{{ $alerts->getCollection()->where('severity', 'critical')->count() }}</p>
        </div>
        <div class="rounded-2xl border px-4 py-3" style="background:rgba(249,115,22,0.05); border-color:rgba(249,115,22,0.2);">
            <p class="text-xs text-orange-400 mb-1">Tinggi</p>
            <p class="text-2xl font-bold text-orange-300">{{ $alerts->getCollection()->where('severity', 'high')->count() }}</p>
        </div>
        <div class="rounded-2xl border px-4 py-3" style="background:rgba(16,185,129,0.05); border-color:rgba(16,185,129,0.2);">
            <p class="text-xs text-emerald-400 mb-1">Bersih</p>
            <p class="text-2xl font-bold text-emerald-300">{{ $alerts->getCollection()->where('alert_type', 'clean')->count() }}</p>
        </div>
    </div>

    {{-- Alert list --}}
    <div class="rounded-2xl border overflow-hidden" style="background:rgba(15,23,42,0.6); border-color:#1e293b;">
        <div class="px-5 py-4 border-b border-slate-700/50 flex items-center justify-between">
            <h3 class="text-base font-semibold text-white">Semua Alert</h3>
            <a href="{{ route('monitoring.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">← Dashboard</a>
        </div>

        @if($alerts->isEmpty())
            <div class="px-5 py-16 text-center">
                <div class="text-5xl mb-4">🛡️</div>
                <p class="text-white font-semibold text-lg">Semua Bersih</p>
                <p class="text-slate-400 text-sm mt-2">Belum ada perubahan yang terdeteksi pada website yang dimonitor.</p>
            </div>
        @else
            <div class="divide-y divide-slate-800">
                @foreach($alerts as $alert)
                    <a href="{{ route('monitoring.alert.show', $alert->id) }}"
                       class="flex items-center gap-4 px-5 py-4 hover:bg-slate-800/30 transition-all group {{ !$alert->is_read ? 'border-l-2 border-amber-500' : '' }}">

                        {{-- Severity indicator --}}
                        <div class="text-xl shrink-0">{{ $alert->severity_badge['icon'] }}</div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-sm font-semibold text-white">{{ $alert->alert_type_label }}</span>
                                <span class="text-xs px-1.5 py-0.5 rounded border {{ $alert->severity_badge['class'] }}">
                                    {{ $alert->severity_badge['label'] }}
                                </span>
                                <span class="text-xs px-1.5 py-0.5 rounded border text-slate-400 border-slate-700">
                                    {{ $alert->check_type === 'quick_check' ? 'Quick Check' : 'Scheduled' }}
                                </span>
                                @if($alert->is_acknowledged)
                                    <span class="text-xs px-1.5 py-0.5 rounded border bg-emerald-500/10 text-emerald-400 border-emerald-500/25">✓ Dikonfirmasi</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 truncate">
                                {{ $alert->clean_url }}
                                @if($alert->monitor)
                                    · Monitor: <span class="text-slate-300">{{ $alert->monitor->nama ?? $alert->monitor->clean_url }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-slate-600 mt-0.5">{{ $alert->detected_at->format('d M Y, H:i') }} WIB · {{ $alert->detected_at->diffForHumans() }}</p>
                        </div>

                        @if($alert->diff_score > 0)
                            <div class="text-center shrink-0">
                                <p class="text-lg font-bold {{ $alert->diff_score >= 70 ? 'text-red-400' : ($alert->diff_score >= 40 ? 'text-orange-400' : 'text-amber-400') }}">
                                    {{ $alert->diff_score }}
                                </p>
                                <p class="text-xs text-slate-500">skor</p>
                            </div>
                        @endif

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
