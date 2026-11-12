@extends('layouts.app')

@section('title', 'Detail Alert — RedSim')
@section('header', 'Detail Alert Integritas')
@section('subheader', $alert->alert_type_label . ' · ' . $alert->detected_at->format('d M Y, H:i') . ' WIB')
@section('page_feature', 'alert-integritas')

@section('content')
<div class="space-y-5 max-w-4xl">

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-emerald-300 border border-emerald-500/25" style="background:rgba(16,185,129,0.08);">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Summary Banner ──────────────────────────────────────────────────── --}}
    @php
        $severityBg = match($alert->severity) {
            'critical' => 'rgba(220,38,38,0.08)',
            'high'     => 'rgba(249,115,22,0.08)',
            'warning'  => 'rgba(245,158,11,0.08)',
            default    => 'rgba(99,102,241,0.08)',
        };
        $severityBorder = match($alert->severity) {
            'critical' => 'rgba(220,38,38,0.25)',
            'high'     => 'rgba(249,115,22,0.25)',
            'warning'  => 'rgba(245,158,11,0.25)',
            default    => 'rgba(99,102,241,0.25)',
        };
    @endphp

    <div class="rounded-2xl border p-5" style="background:{{ $severityBg }}; border-color:{{ $severityBorder }};">
        <div class="flex items-start gap-4">
            <div class="text-4xl">{{ $alert->severity_badge['icon'] }}</div>
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <h2 class="text-lg font-bold text-white">{{ $alert->alert_type_label }}</h2>
                    <span class="text-xs px-2 py-1 rounded-lg border font-semibold {{ $alert->severity_badge['class'] }}">
                        {{ $alert->severity_badge['label'] }}
                    </span>
                    <span class="text-xs px-2 py-1 rounded-lg border text-slate-400 border-slate-700">
                        {{ $alert->check_type === 'quick_check' ? 'Quick Check' : 'Pemeriksaan Terjadwal' }}
                    </span>
                    @if($alert->is_acknowledged)
                        <span class="text-xs px-2 py-1 rounded-lg border bg-emerald-500/10 text-emerald-400 border-emerald-500/25">✓ Dikonfirmasi Wajar</span>
                    @endif
                </div>
                <p class="text-sm text-slate-300">
                    Target: <a href="{{ $alert->target_url }}" target="_blank" class="text-indigo-400 hover:text-indigo-300">{{ $alert->target_url }}</a>
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    Terdeteksi: {{ $alert->detected_at->format('d M Y, H:i:s') }} WIB
                    @if($alert->monitor)
                        · Monitor: <a href="{{ route('monitoring.show', $alert->monitor->id) }}" class="text-indigo-400 hover:text-indigo-300">{{ $alert->monitor->nama ?? $alert->monitor->clean_url }}</a>
                    @endif
                </p>
            </div>
            @if($alert->diff_score > 0)
                <div class="text-center shrink-0">
                    <div class="text-3xl font-black {{ $alert->diff_score >= 70 ? 'text-red-400' : ($alert->diff_score >= 40 ? 'text-orange-400' : 'text-amber-400') }}">
                        {{ $alert->diff_score }}
                    </div>
                    <p class="text-xs text-slate-500">Skor Perubahan</p>
                    <p class="text-xs text-slate-600">/ 100</p>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        @if(!$alert->is_acknowledged && $alert->alert_type !== 'clean')
            <div class="mt-4 pt-4 border-t flex flex-wrap gap-3" style="border-color:rgba(255,255,255,0.07);">
                <form action="{{ route('monitoring.alert.acknowledge', $alert->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/10 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Konfirmasi Perubahan Wajar
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- ── Perubahan yang Terdeteksi ───────────────────────────────────────── --}}
    @php $evidence = $alert->evidence ?? []; @endphp
    @php $changes  = $evidence['changes'] ?? []; @endphp

    @if(count($changes) > 0)
        <div class="rounded-2xl border" style="background:rgba(15,23,42,0.6); border-color:#1e293b;">
            <div class="px-5 py-4 border-b border-slate-700/50">
                <h3 class="text-base font-semibold text-white">🔍 Perubahan yang Terdeteksi</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ count($changes) }} perubahan diidentifikasi</p>
            </div>
            <div class="divide-y divide-slate-800">
                @foreach($changes as $change)
                    @php
                        $changeSeverityColor = match($change['severity'] ?? 'info') {
                            'critical' => 'text-red-300',
                            'high'     => 'text-orange-300',
                            'warning'  => 'text-amber-300',
                            default    => 'text-blue-300',
                        };
                        $changeIcon = match($change['type'] ?? '') {
                            'script_inject', 'iframe_inject' => '⚡',
                            'defacement'                     => '🚨',
                            'title_changed'                  => '📝',
                            'h1_changed'                     => '📋',
                            'meta_refresh'                   => '↪️',
                            'content_change'                 => '📄',
                            default                          => '🔔',
                        };
                    @endphp
                    <div class="px-5 py-4">
                        <div class="flex items-start gap-3">
                            <span class="text-lg shrink-0">{{ $changeIcon }}</span>
                            <div class="flex-1">
                                <p class="text-sm font-semibold {{ $changeSeverityColor }}">{{ $change['label'] ?? ucfirst($change['type'] ?? '-') }}</p>

                                {{-- Tampilkan detail berdasarkan tipe --}}
                                @if(!empty($change['srcs']))
                                    <div class="mt-2 space-y-1">
                                        @foreach($change['srcs'] as $src)
                                            <div class="flex items-center gap-2 text-xs font-mono px-2 py-1 rounded" style="background:rgba(220,38,38,0.08);">
                                                <span class="text-red-400">+</span>
                                                <span class="text-red-200 break-all">{{ $src }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if(!empty($change['before']) || !empty($change['after']))
                                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @if(!empty($change['before']))
                                            <div class="text-xs px-3 py-2 rounded-lg" style="background:rgba(220,38,38,0.06);">
                                                <p class="text-red-400 font-semibold mb-1 text-[10px] uppercase tracking-wider">Sebelum</p>
                                                <p class="text-slate-300 break-words">{{ $change['before'] }}</p>
                                            </div>
                                        @endif
                                        @if(!empty($change['after']))
                                            <div class="text-xs px-3 py-2 rounded-lg" style="background:rgba(16,185,129,0.06);">
                                                <p class="text-emerald-400 font-semibold mb-1 text-[10px] uppercase tracking-wider">Sesudah</p>
                                                <p class="text-slate-300 break-words">{{ $change['after'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if(!empty($change['keywords']))
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach(array_slice($change['keywords'], 0, 10) as $kw)
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-red-500/15 text-red-300 border border-red-500/25 font-mono">{{ $kw }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                @if(!empty($change['change_pct']))
                                    <p class="text-xs text-slate-400 mt-1">Perubahan konten ~{{ $change['change_pct'] }}%</p>
                                @endif
                            </div>
                            <span class="text-xs px-1.5 py-0.5 rounded border shrink-0 {{ match($change['severity'] ?? 'info') {
                                'critical' => 'text-red-400 bg-red-500/10 border-red-500/25',
                                'high'     => 'text-orange-400 bg-orange-500/10 border-orange-500/25',
                                'warning'  => 'text-amber-400 bg-amber-500/10 border-amber-500/25',
                                default    => 'text-blue-400 bg-blue-500/10 border-blue-500/25',
                            } }}">{{ $change['severity'] ?? 'info' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── AI Analysis ──────────────────────────────────────────────────────── --}}
    @if($alert->ai_analysis)
        <div class="rounded-2xl border p-5" style="background:linear-gradient(135deg,rgba(99,102,241,0.05),rgba(139,92,246,0.05)); border-color:rgba(99,102,241,0.2);">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                <h3 class="text-sm font-semibold text-indigo-300">Analisis AI</h3>
                @if($alert->ai_risk_score !== null)
                    <span class="ml-auto text-xs px-2 py-0.5 rounded-lg font-bold {{ $alert->ai_risk_score >= 70 ? 'text-red-300 bg-red-500/15' : ($alert->ai_risk_score >= 40 ? 'text-orange-300 bg-orange-500/15' : 'text-emerald-300 bg-emerald-500/15') }}">
                        Risk Score: {{ $alert->ai_risk_score }}/100
                    </span>
                @endif
            </div>
            <div class="text-sm text-slate-300 leading-relaxed whitespace-pre-line">{{ $alert->ai_analysis }}</div>
        </div>
    @endif

    {{-- ── Defacement Evidence ──────────────────────────────────────────────── --}}
    @php $defacement = $evidence['defacement'] ?? []; @endphp
    @if(!empty($defacement['detected_signatures']))
        <div class="rounded-2xl border p-5" style="background:rgba(220,38,38,0.06); border-color:rgba(220,38,38,0.2);">
            <h3 class="text-sm font-semibold text-red-300 mb-3">🚨 Keyword Defacement Terdeteksi</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($defacement['detected_signatures'] as $sig)
                    <span class="text-xs px-2.5 py-1 rounded-lg font-mono bg-red-500/15 text-red-200 border border-red-500/25">{{ $sig }}</span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Script & Iframe Evidence ─────────────────────────────────────────── --}}
    @if(!empty($evidence['new_scripts']) || !empty($evidence['new_iframes']))
        <div class="rounded-2xl border p-5" style="background:rgba(15,23,42,0.6); border-color:#1e293b;">
            <h3 class="text-sm font-semibold text-white mb-3">📋 Script & Iframe Baru</h3>
            @if(!empty($evidence['new_scripts']))
                <div class="mb-3">
                    <p class="text-xs text-slate-400 mb-1.5 font-medium">Script Asing Baru:</p>
                    @foreach($evidence['new_scripts'] as $src)
                        <div class="text-xs font-mono px-3 py-1.5 rounded mb-1 text-orange-200 break-all" style="background:rgba(249,115,22,0.08);">{{ $src }}</div>
                    @endforeach
                </div>
            @endif
            @if(!empty($evidence['new_iframes']))
                <div>
                    <p class="text-xs text-slate-400 mb-1.5 font-medium">Iframe Asing Baru:</p>
                    @foreach($evidence['new_iframes'] as $src)
                        <div class="text-xs font-mono px-3 py-1.5 rounded mb-1 text-red-200 break-all" style="background:rgba(220,38,38,0.08);">{{ $src }}</div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- Navigation --}}
    <div class="flex items-center justify-between">
        @if($alert->monitor)
            <a href="{{ route('monitoring.show', $alert->monitor->id) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 transition-all">
                ← Detail Monitor
            </a>
        @else
            <a href="{{ route('monitoring.alerts') }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 transition-all">
                ← Semua Alert
            </a>
        @endif
        <a href="{{ route('monitoring.index') }}"
           class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 transition-all">
            Dashboard Monitor
        </a>
    </div>
</div>
@endsection
