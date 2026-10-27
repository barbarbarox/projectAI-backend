@extends('layouts.app')
@section('title', 'Kesehatan Sistem — Admin RedSim')
@section('header', 'Kesehatan Sistem')
@section('subheader', 'Pantau status seluruh komponen sistem RedSim secara real-time')

@section('content')
<div class="space-y-6" x-data="healthMonitor()" x-init="init()">

    {{-- Header actions --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-[#64748b]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span x-text="'Terakhir dicek: ' + checkedAt"></span>
        </div>
        <button @click="refresh()" :disabled="loading"
            class="flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-[#00d4ff]/10 text-[#00d4ff] border border-[#00d4ff]/20 hover:bg-[#00d4ff]/20 transition disabled:opacity-50">
            <svg class="w-4 h-4" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span x-text="loading ? 'Memeriksa...' : 'Refresh Status'"></span>
        </button>
    </div>

    {{-- Status Pengguna --}}
    <div class="grid md:grid-cols-2 gap-4">
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6 flex items-center justify-between">
            <div>
                <p class="text-sm text-[#94a3b8] mb-1">Pengguna Online <span class="text-xs">(15m terakhir)</span></p>
                <div class="text-3xl font-black text-green-400">{{ $usersOnline }}</div>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center text-green-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
        </div>
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6 flex items-center justify-between">
            <div>
                <p class="text-sm text-[#94a3b8] mb-1">Pengguna Offline</p>
                <div class="text-3xl font-black text-[#64748b]">{{ $usersOffline }}</div>
            </div>
            <div class="w-12 h-12 rounded-full bg-[#1e2d4a] flex items-center justify-center text-[#94a3b8]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </div>
        </div>
    </div>

    @php
        $statusColor = fn($s) => match($s) {
            'ok'      => 'text-green-400 bg-green-500/10 border-green-500/30',
            'warning' => 'text-yellow-400 bg-yellow-500/10 border-yellow-500/30',
            default   => 'text-red-400 bg-red-500/10 border-red-500/30',
        };
        $statusBadge = fn($s) => match($s) {
            'ok'      => '<span class="text-xs font-bold px-2 py-0.5 rounded-full bg-green-500/20 text-green-400">AKTIF</span>',
            'warning' => '<span class="text-xs font-bold px-2 py-0.5 rounded-full bg-yellow-500/20 text-yellow-400">PERINGATAN</span>',
            default   => '<span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-500/20 text-red-400">ERROR</span>',
        };
        $statusIcon = fn($s) => match($s) {
            'ok'      => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
            'warning' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            default   => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
        };
    @endphp

    {{-- ─── RAG & Vector Database Section ──────────────────────────────────── --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-5 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582 4 8 4m0 0s8-1.79 8-4"/></svg>
            Vector Database & RAG
        </h3>
        <div class="space-y-3">

            {{-- General KB Pinecone --}}
            <div class="p-4 rounded-xl border {{ $statusColor($healthStatus['vector_database']['status']) }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-semibold text-sm">Pinecone — General Knowledge Base</h4>
                            {!! $statusBadge($healthStatus['vector_database']['status']) !!}
                        </div>
                        <p class="text-xs opacity-80 mb-1">Index: <code class="font-mono">{{ env('PINECONE_INDEX', 'redsim-rag') }}</code> | multilingual-e5-large</p>
                        <p class="text-xs opacity-70">{{ $healthStatus['vector_database']['message'] }}</p>
                        @if(isset($healthStatus['vector_database']['vector_count']))
                            <div class="mt-2 text-xs opacity-60">
                                📊 <strong>{{ number_format($healthStatus['vector_database']['vector_count']) }}</strong> vektor tersimpan
                            </div>
                        @endif
                    </div>
                    <div>{!! $statusIcon($healthStatus['vector_database']['status']) !!}</div>
                </div>
            </div>

            {{-- NVD CVE Pinecone — dengan progress bar ingest --}}
            <div class="p-4 rounded-xl border {{ $statusColor($healthStatus['nvd_vector_database']['status']) }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-semibold text-sm">Pinecone — NVD CVE Knowledge Base <span class="text-xs font-normal opacity-70">(2024–2026)</span></h4>
                            {!! $statusBadge($healthStatus['nvd_vector_database']['status']) !!}
                        </div>
                        <p class="text-xs opacity-80 mb-1">Index: <code class="font-mono">{{ env('PINECONE_NVD_INDEX', 'redsim-nvd-cve') }}</code> | multilingual-e5-base (768-dim)</p>
                        <p class="text-xs opacity-70 mb-2">{{ $healthStatus['nvd_vector_database']['message'] }}</p>

                        @php
                            $nvdCount  = $healthStatus['nvd_vector_database']['vector_count'] ?? 0;
                            $nvdTarget = 94000;
                            $nvdPct    = $nvdCount > 0 ? min(100, round($nvdCount / $nvdTarget * 100, 1)) : 0;
                        @endphp

                        {{-- Ingest Progress Bar --}}
                        <div class="mt-1">
                            <div class="flex justify-between text-xs opacity-60 mb-1">
                                <span>📥 Ingest Progress: {{ number_format($nvdCount) }} / ~{{ number_format($nvdTarget) }} CVE</span>
                                <span class="font-bold">{{ $nvdPct }}%</span>
                            </div>
                            <div class="w-full bg-black/30 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500
                                    {{ $nvdPct >= 80 ? 'bg-green-400' : ($nvdPct >= 30 ? 'bg-yellow-400' : 'bg-[#00d4ff]') }}"
                                    style="width: {{ $nvdPct }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs opacity-50 mt-1">
                                <span>2024</span>
                                <span>2025</span>
                                <span>2026</span>
                            </div>
                        </div>

                        {{-- Live counter via AJAX --}}
                        <div class="mt-2 text-xs opacity-60 flex items-center gap-2">
                            <span>🔄 Live:</span>
                            <span class="font-mono font-bold" x-text="nvdVectorCount ?? '{{ number_format($nvdCount) }}'"></span>
                            <span>vektor</span>
                            <span x-show="nvdLoading" class="text-[#00d4ff] animate-pulse">↻ memuat...</span>
                        </div>
                    </div>
                    <div>{!! $statusIcon($healthStatus['nvd_vector_database']['status']) !!}</div>
                </div>
            </div>

        </div>
    </div>

    {{-- ─── ML Service & Embedding ──────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-5 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            ML Service & Embedding
        </h3>
        <div class="space-y-3">

            {{-- ML Service Python --}}
            <div class="p-4 rounded-xl border {{ $statusColor($healthStatus['ml_service']['status']) }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-semibold text-sm">ML Prediction Service (Python FastAPI)</h4>
                            {!! $statusBadge($healthStatus['ml_service']['status']) !!}
                        </div>
                        <p class="text-xs opacity-80 mb-1">Ensemble: Random Forest + XGBoost | Port :5001</p>
                        <p class="text-xs opacity-70">{{ $healthStatus['ml_service']['message'] }}</p>
                        @if(isset($healthStatus['ml_service']['detail']['models']))
                            <div class="mt-2 flex gap-3 text-xs opacity-60">
                                @foreach($healthStatus['ml_service']['detail']['models'] as $name => $m)
                                    <span>{{ ucwords(str_replace('_', ' ', $name)) }}: {{ $m['n_estimators'] ?? '?' }} estimators (W={{ $m['weight'] ?? '?' }})</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div>{!! $statusIcon($healthStatus['ml_service']['status']) !!}</div>
                </div>
            </div>

            {{-- Embedding Service --}}
            <div class="p-4 rounded-xl border {{ $statusColor($healthStatus['embedding_service']['status']) }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-semibold text-sm">Embedding Service (multilingual-e5-base)</h4>
                            {!! $statusBadge($healthStatus['embedding_service']['status']) !!}
                        </div>
                        <p class="text-xs opacity-80 mb-1">Model: intfloat/multilingual-e5-base | Dimensi: 768</p>
                        <p class="text-xs opacity-70">{{ $healthStatus['embedding_service']['message'] }}</p>
                    </div>
                    <div>{!! $statusIcon($healthStatus['embedding_service']['status']) !!}</div>
                </div>
            </div>

        </div>
    </div>

    {{-- ─── API Eksternal ────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-5 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            API & Layanan Eksternal
        </h3>
        <div class="space-y-3">

            {{-- AI Provider --}}
            <div class="flex items-center justify-between p-4 rounded-xl border {{ $statusColor($healthStatus['ai_provider']['status']) }}">
                <div>
                    <h4 class="font-semibold text-sm mb-1">Model AI Utama (LLM)</h4>
                    <p class="text-xs opacity-80">{{ $healthStatus['ai_provider']['message'] }}</p>
                </div>
                <div>{!! $statusIcon($healthStatus['ai_provider']['status']) !!}</div>
            </div>

            {{-- VirusTotal --}}
            <div class="flex items-center justify-between p-4 rounded-xl border {{ $statusColor($healthStatus['virustotal']['status']) }}">
                <div>
                    <h4 class="font-semibold text-sm mb-1">VirusTotal API</h4>
                    <p class="text-xs opacity-80">{{ $healthStatus['virustotal']['message'] }}</p>
                </div>
                <div>{!! $statusIcon($healthStatus['virustotal']['status']) !!}</div>
            </div>

            {{-- URLScan --}}
            <div class="flex items-center justify-between p-4 rounded-xl border {{ $statusColor($healthStatus['urlscan']['status']) }}">
                <div>
                    <h4 class="font-semibold text-sm mb-1">URLScan.io API</h4>
                    <p class="text-xs opacity-80">{{ $healthStatus['urlscan']['message'] }}</p>
                </div>
                <div>{!! $statusIcon($healthStatus['urlscan']['status']) !!}</div>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
function healthMonitor() {
    return {
        loading: false,
        nvdLoading: false,
        nvdVectorCount: null,
        checkedAt: '{{ now()->format("H:i:s") }}',

        init() {
            // Auto-refresh NVD vector count setiap 30 detik saat ingest berjalan
            this.fetchNVDCount();
            setInterval(() => this.fetchNVDCount(), 30000);
        },

        async fetchNVDCount() {
            this.nvdLoading = true;
            try {
                const res = await fetch('{{ route("api.nvd-health") }}');
                if (res.ok) {
                    const data = await res.json();
                    if (data.vector_count !== undefined) {
                        this.nvdVectorCount = new Intl.NumberFormat('id-ID').format(data.vector_count);
                    }
                }
            } catch(e) {}
            this.nvdLoading = false;
        },

        async refresh() {
            this.loading = true;
            try {
                await this.fetchNVDCount();
                // Reload halaman untuk refresh semua status
                setTimeout(() => window.location.reload(), 500);
            } catch(e) {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
@endsection
