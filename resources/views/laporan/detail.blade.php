@extends('layouts.app')
@section('title', 'Laporan #' . substr($scan->id, 0, 8) . ' — RedSim')
@section('header', 'Laporan Analisis')
@section('subheader', 'Hasil pemindaian ' . ucfirst($scan->tipe_scan) . ' — ' . $scan->created_at->format('d M Y H:i'))

@section('content')
<div class="space-y-6">
    {{-- Summary --}}
    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-1 rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6 text-center">
            <x-security-gauge :skor="$scan->skor_keamanan ?? 0" />
            <p class="mt-3 text-sm font-semibold {{ ($scan->verdict ?? '') === 'aman' ? 'text-green-400' : (($scan->verdict ?? '') === 'perhatian' ? 'text-yellow-400' : 'text-red-400') }}">
                {{ $scan->verdict_label }}
            </p>
            @if($scan->confidence_score)
            <p class="text-xs text-[#64748b] mt-1">Kepercayaan: {{ round($scan->confidence_score * 100) }}%</p>
            @endif
            <a href="{{ route('laporan.pdf', $scan) }}" class="inline-block mt-4 px-4 py-2 rounded-lg bg-[#1e2d4a] text-xs text-[#94a3b8] hover:bg-[#2a3a5c] transition-colors">📄 Unduh PDF</a>
        </div>
        <div class="md:col-span-2 space-y-4">
            @if($scan->ringkasan_eksekutif)
            <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
                <h3 class="text-sm font-semibold text-[#00d4ff] mb-3">Ringkasan Eksekutif</h3>
                <p class="text-sm text-[#94a3b8] leading-relaxed whitespace-pre-line">{{ $scan->ringkasan_eksekutif }}</p>
            </div>
            @endif
            @if($scan->ringkasan_teknis)
            <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
                <h3 class="text-sm font-semibold text-[#7c3aed] mb-3">Ringkasan Teknis</h3>
                <p class="text-sm text-[#94a3b8] leading-relaxed whitespace-pre-line">{{ $scan->ringkasan_teknis }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Temuan --}}
    @if($scan->temuan->count())
    <div>
        <h3 class="text-lg font-semibold mb-4">Temuan ({{ $scan->temuan->count() }})</h3>
        <div class="space-y-3">
            @foreach($scan->temuan->sortByDesc(fn($t) => ['kritis'=>5,'tinggi'=>4,'sedang'=>3,'rendah'=>2,'info'=>1][$t->tingkat_keparahan] ?? 0) as $temuan)
                <x-finding-card :temuan="$temuan" />
            @endforeach
        </div>
    </div>
    @endif

    {{-- Simulasi Serangan --}}
    @if($scan->simulasiSerangan->count())
    <div>
        <h3 class="text-lg font-semibold mb-4">Simulasi Serangan</h3>
        <div class="space-y-4">
            @foreach($scan->simulasiSerangan as $sim)
            <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-semibold text-red-400">{{ $sim->nama_skenario }}</h4>
                    @if($sim->profil_penyerang)
                    <span class="px-3 py-1 rounded-full text-xs bg-red-500/10 text-red-400 border border-red-500/20">{{ str_replace('_', ' ', ucfirst($sim->profil_penyerang)) }}</span>
                    @endif
                </div>
                @if($sim->fase_attck)
                <x-attack-timeline :aktif="$sim->fase_attck ?? []" />
                @endif
                @if($sim->narasi_teknis)
                <div class="mt-4 p-4 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a]">
                    <p class="text-sm text-[#94a3b8]">{{ $sim->narasi_teknis }}</p>
                </div>
                @endif
                <div class="flex gap-4 mt-3 text-xs text-[#64748b]">
                    @if($sim->skor_kemungkinan)<span>Kemungkinan: <b class="text-orange-400">{{ round($sim->skor_kemungkinan * 100) }}%</b></span>@endif
                    @if($sim->skor_dampak)<span>Dampak: <b class="text-red-400">{{ round($sim->skor_dampak * 100) }}%</b></span>@endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- RAG Source Info Box --}}
    <x-rag-source-info :scan="$scan" />

    {{-- Disclaimer --}}
    <div class="rounded-xl bg-yellow-500/5 border border-yellow-500/20 p-4">
        <p class="text-xs text-yellow-400/80">⚠️ Penilaian ini dihasilkan AI berbasis knowledge base keamanan publik (NVD, MITRE ATT&CK, OWASP, CWE, CISA KEV). Tidak menggantikan penetration testing profesional. Gunakan sebagai panduan awal, bukan keputusan final.</p>
    </div>
</div>
@endsection
