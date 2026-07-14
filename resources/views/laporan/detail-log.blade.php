@extends('layouts.app')
@section('title', 'Hasil Analisis Log — RedSim')
@section('header', 'Hasil Analisis Log')
@section('subheader', $scan->target . ' — ' . $scan->created_at->format('d M Y H:i'))

@section('content')
@php
    $data = $scan->data_mentah ?? [];
    $stats = $data['stats'] ?? [];
    $anomali = $data['anomali_mentah'] ?? [];
    $timeline = $data['incident_timeline'] ?? [];
    $iocList = $data['ioc_list'] ?? [];
    $attck = $data['attck_mapping'] ?? [];
    $rekomendasi = $data['rekomendasi'] ?? [];
    $severity = $data['tingkat_keparahan'] ?? 'rendah';
    $sevColor = match($severity) { 'kritis' => 'bg-red-500', 'tinggi' => 'bg-orange-500', 'sedang' => 'bg-yellow-500', default => 'bg-green-500' };
@endphp

<div class="space-y-6">
    {{-- Section 1: Ringkasan Eksekutif --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <div class="flex items-center gap-3 mb-4">
            <h3 class="text-sm font-semibold text-[#00d4ff]">📊 Ringkasan Eksekutif</h3>
            <span class="px-3 py-1 rounded-full text-xs font-bold text-white {{ $sevColor }}">{{ strtoupper($severity) }}</span>
        </div>
        @if($data['ringkasan_insiden'] ?? $scan->ringkasan_eksekutif)
        <p class="text-sm text-[#94a3b8] whitespace-pre-line leading-relaxed">{{ $data['ringkasan_insiden'] ?? $scan->ringkasan_eksekutif }}</p>
        @else
        <p class="text-sm text-[#64748b]">Analisis belum tersedia.</p>
        @endif
    </div>

    {{-- Section 2: Statistik Log --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4">📈 Statistik Log</h3>
        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 text-center">
                <div class="text-2xl font-black text-[#00d4ff]">{{ number_format($stats['total_baris'] ?? 0) }}</div>
                <p class="text-xs text-[#64748b] mt-1">Total Baris</p>
            </div>
            <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 text-center">
                <div class="text-2xl font-black {{ ($stats['total_anomali'] ?? 0) > 10 ? 'text-red-400' : 'text-yellow-400' }}">{{ $stats['total_anomali'] ?? 0 }}</div>
                <p class="text-xs text-[#64748b] mt-1">Total Anomali</p>
            </div>
            <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 text-center">
                <div class="text-2xl font-black text-[#7c3aed]">{{ $data['format_terdeteksi'] ?? '?' }}</div>
                <p class="text-xs text-[#64748b] mt-1">Format Log</p>
            </div>
        </div>

        @if(!empty($stats['anomali_per_kategori']))
        <div class="grid md:grid-cols-4 gap-2 mb-4">
            @foreach($stats['anomali_per_kategori'] as $kat => $count)
            <div class="px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-center">
                <div class="text-sm font-bold {{ $count > 0 ? 'text-red-400' : 'text-green-400' }}">{{ $count }}</div>
                <p class="text-[10px] text-[#64748b]">{{ str_replace('_', ' ', ucfirst($kat)) }}</p>
            </div>
            @endforeach
        </div>
        @endif

        <div class="grid md:grid-cols-2 gap-4">
            @if(!empty($stats['top_ips']))
            <div>
                <h4 class="text-xs font-semibold text-[#94a3b8] mb-2">Top 5 IP</h4>
                @foreach($stats['top_ips'] as $ip => $count)
                <div class="flex justify-between px-3 py-1.5 rounded text-xs {{ $count > 50 ? 'text-red-400' : 'text-[#94a3b8]' }}">
                    <code>{{ $ip }}</code><span>{{ $count }}x</span>
                </div>
                @endforeach
            </div>
            @endif
            @if(!empty($stats['top_paths']))
            <div>
                <h4 class="text-xs font-semibold text-[#94a3b8] mb-2">Top 5 Path</h4>
                @foreach($stats['top_paths'] as $path => $count)
                <div class="flex justify-between px-3 py-1.5 rounded text-xs text-[#94a3b8]">
                    <code class="truncate max-w-[200px]">{{ $path }}</code><span>{{ $count }}x</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Section 3: Incident Timeline --}}
    @if(!empty($timeline))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4">🕐 Kronologi Insiden</h3>
        <div class="space-y-3">
            @foreach($timeline as $event)
            <div class="flex gap-4 px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a]">
                <div class="text-xs text-[#64748b] whitespace-nowrap font-mono">{{ $event['waktu'] ?? '-' }}</div>
                <div>
                    <p class="text-sm text-[#e2e8f0]">{{ $event['kejadian'] ?? '' }}</p>
                    @if(!empty($event['indicator']))
                    <p class="text-xs text-yellow-400 mt-1">⚠️ {{ $event['indicator'] }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Section 4: IOC List --}}
    @if(!empty($iocList))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4">🎯 Indicators of Compromise (IOC)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-[#64748b] border-b border-[#1e2d4a]">
                    <th class="text-left py-2 px-3">Tipe</th>
                    <th class="text-left py-2 px-3">Nilai</th>
                    <th class="text-left py-2 px-3">Keterangan</th>
                </tr></thead>
                <tbody>
                @foreach($iocList as $ioc)
                <tr class="border-b border-[#1e2d4a]/50 hover:bg-[#1e2d4a]/20">
                    <td class="py-2 px-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/10 text-red-400">{{ strtoupper($ioc['tipe'] ?? '') }}</span></td>
                    <td class="py-2 px-3 font-mono text-xs text-[#e2e8f0] cursor-pointer" onclick="navigator.clipboard.writeText(this.textContent.trim())" title="Klik untuk copy">{{ $ioc['nilai'] ?? '' }}</td>
                    <td class="py-2 px-3 text-xs text-[#94a3b8]">{{ $ioc['keterangan'] ?? '' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Section 5: ATT&CK Mapping --}}
    @if(!empty($attck))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4">🗺️ MITRE ATT&CK Mapping</h3>
        <div class="space-y-3">
            @foreach($attck as $tech)
            <div class="px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a]">
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#7c3aed]/10 text-[#7c3aed]">{{ $tech['technique_id'] ?? '' }}</span>
                    <span class="text-sm font-medium text-[#e2e8f0]">{{ $tech['nama'] ?? '' }}</span>
                </div>
                <p class="text-xs text-[#94a3b8]">{{ $tech['relevansi'] ?? '' }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Section 6: Rekomendasi --}}
    @if(!empty($rekomendasi))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4">📋 Rekomendasi</h3>
        @php
            $grouped = collect($rekomendasi)->groupBy('prioritas');
            $prioColors = ['segera' => 'border-red-500/30 bg-red-500/5', 'minggu_ini' => 'border-orange-500/30 bg-orange-500/5', 'bulan_ini' => 'border-yellow-500/30 bg-yellow-500/5'];
            $prioBadge = ['segera' => 'bg-red-500 text-white', 'minggu_ini' => 'bg-orange-500 text-white', 'bulan_ini' => 'bg-yellow-500 text-black'];
            $prioLabel = ['segera' => '🚨 Segera', 'minggu_ini' => '⚡ Minggu Ini', 'bulan_ini' => '📅 Bulan Ini'];
        @endphp
        @foreach(['segera', 'minggu_ini', 'bulan_ini'] as $prio)
            @if($grouped->has($prio))
            <div class="mb-4">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-2 {{ $prioBadge[$prio] ?? '' }}">{{ $prioLabel[$prio] ?? ucfirst($prio) }}</span>
                @foreach($grouped[$prio] as $rek)
                <div class="px-4 py-3 rounded-xl border mb-2 {{ $prioColors[$prio] ?? 'border-[#1e2d4a]' }}">
                    <p class="text-sm text-[#e2e8f0] font-medium">{{ $rek['tindakan'] ?? '' }}</p>
                    <p class="text-xs text-[#94a3b8] mt-1">{{ $rek['alasan'] ?? '' }}</p>
                </div>
                @endforeach
            </div>
            @endif
        @endforeach
    </div>
    @endif

    {{-- RAG Source Info Box --}}
    <x-rag-source-info :scan="$scan" />

    {{-- Section 7: Disclaimer AI --}}
    <div class="rounded-xl bg-yellow-500/5 border border-yellow-500/20 p-4">
        <p class="text-xs text-yellow-400/80">⚠️ Analisis ini berbasis AI dan RAG knowledge base. Verifikasi manual tetap diperlukan. Tidak menggantikan investigasi forensik profesional.</p>
    </div>

    {{-- Actions --}}
    <div class="flex gap-3">
        <a href="{{ route('laporan.pdf', $scan) }}" class="px-4 py-2 rounded-lg bg-[#1e2d4a] text-xs text-[#94a3b8] hover:bg-[#2a3a5c] transition-colors">📄 Unduh PDF</a>
        <a href="{{ route('laporan.index') }}" class="px-4 py-2 rounded-lg bg-[#1e2d4a] text-xs text-[#94a3b8] hover:bg-[#2a3a5c] transition-colors">📋 Riwayat Scan</a>
    </div>
</div>
@endsection
