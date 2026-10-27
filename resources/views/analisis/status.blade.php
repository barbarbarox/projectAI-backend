@extends('layouts.app')
@section('title', 'Memproses Analisis — RedSim')
@section('header', 'Memproses Analisis')
@section('subheader', 'Sistem sedang menganalisis target secara menyeluruh')

@section('content')
@php
    $stages = \App\Models\Scan::pipelineStages();
    $isBiasa = $scan->mode_scan !== 'intens';
    // Untuk scan biasa, hanya tampilkan 4 tahap (tanpa fingerprint intens & SSL intens)
    $activeStages = $isBiasa ? [1,2,3,5,6] : [1,2,3,4,5,6];
@endphp

<div class="max-w-3xl mx-auto mt-6 pb-12" x-data="{
    status: '{{ $scan->status }}',
    step: '{{ $scan->progress_step ?? 'Memulai...' }}',
    persen: {{ $scan->progress_persen ?? 0 }},
    pipelineStage: {{ $scan->pipeline_stage ?? 0 }},
    modeAi: '{{ $scan->mode_ai ?? 'dengan_ai' }}',
    modeScan: '{{ $scan->mode_scan ?? 'biasa' }}',
    error: @js($scan->error_message ?? ''),
    timer: null,
    init() {
        if (this.status === 'selesai') {
            window.location = '{{ route('analisis.hasil', $scan) }}';
            return;
        }
        this.timer = setInterval(async () => {
            try {
                const r = await fetch('{{ route('analisis.status', $scan) }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const d = await r.json();
                this.status = d.status;
                this.step = d.progress_step || this.step;
                this.persen = d.progress_persen || this.persen;
                this.pipelineStage = d.pipeline_stage || this.pipelineStage;
                this.error = d.error_message || '';
                if (d.status === 'selesai') {
                    this.persen = 100;
                    this.pipelineStage = 6;
                    this.step = 'Selesai!';
                    clearInterval(this.timer);
                    setTimeout(() => { window.location = '{{ route('analisis.hasil', $scan) }}'; }, 1000);
                }
                if (d.status === 'gagal') { clearInterval(this.timer); }
            } catch(e) { console.error(e); }
        }, 2000);
    },
    stageState(n) {
        if (this.status === 'selesai' || n < this.pipelineStage) return 'done';
        if (n === this.pipelineStage) return 'active';
        return 'pending';
    }
}">

    {{-- ===== HEADER CARD: Status & Progress ===== --}}
    <div class="pipeline-card mb-5 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-semibold text-white flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <template x-if="status === 'memproses'">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                        </template>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                            :class="status === 'gagal' ? 'bg-red-500' : status === 'selesai' ? 'bg-emerald-400' : 'bg-cyan-400'"></span>
                    </span>
                    <span x-text="status === 'gagal' ? 'Analisis Gagal' : status === 'selesai' ? 'Analisis Selesai' : 'Analisis Berjalan'"></span>
                </h2>
                <p class="text-xs text-[#64748b] mt-0.5" x-text="step"></p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-mono font-bold text-white" x-text="persen + '%'"></div>
                <div class="text-[10px] text-[#475569]">selesai</div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="w-full bg-[#0a0e1a] rounded-full h-2 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-700 ease-out relative overflow-hidden"
                :class="status === 'gagal' ? 'bg-red-500' : 'bg-gradient-to-r from-[#00d4ff] to-[#7c3aed]'"
                :style="'width: ' + persen + '%'">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
            </div>
        </div>

        {{-- Mode badges --}}
        <div class="mt-3 flex items-center gap-2 flex-wrap">
            <span class="pipeline-badge-cyan text-[10px]">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                {{ ucfirst($scan->mode_scan) }}
            </span>
            <span class="pipeline-badge text-[10px]" :class="modeAi === 'dengan_ai' ? 'pipeline-badge-purple' : 'pipeline-badge-dim'">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span x-text="modeAi === 'dengan_ai' ? 'AI Aktif' : 'Tanpa AI'"></span>
            </span>
            <span class="pipeline-badge-dim text-[10px]">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                {{ $scan->target }}
            </span>
        </div>
    </div>

    {{-- ===== PIPELINE STAGES ===== --}}
    <template x-if="status !== 'gagal'">
        <div class="pipeline-card mb-5 p-6">
            <div class="flex items-center gap-2 mb-5">
                <svg class="w-4 h-4 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <h3 class="text-sm font-semibold text-white">Pipeline Analisis</h3>
                <span class="ml-auto text-[10px] text-[#475569]">{{ count($activeStages) }} tahap</span>
            </div>

            <div class="space-y-3">
                @foreach($stages as $n => $stage)
                @if(in_array($n, $activeStages))
                <div class="pipeline-stage-row"
                    :class="{
                        'is-done':    {{ $n }} < pipelineStage || ({{ $n }} === pipelineStage && status === 'selesai'),
                        'is-active':  {{ $n }} === pipelineStage && status === 'memproses',
                        'is-pending': {{ $n }} > pipelineStage
                    }">

                    {{-- Icon --}}
                    <div class="stage-icon-wrap">
                        {{-- Done check --}}
                        <template x-if="{{ $n }} < pipelineStage || ({{ $n }} === pipelineStage && status === 'selesai')">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        {{-- Active spinner --}}
                        <template x-if="{{ $n }} === pipelineStage && status === 'memproses'">
                            <svg class="w-4 h-4 text-cyan-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                        </template>
                        {{-- Pending dot --}}
                        <template x-if="{{ $n }} > pipelineStage">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#2a3a5c] inline-block"></span>
                        </template>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-semibold stage-label">{{ $stage['label'] }}</span>
                            <template x-if="{{ $n }} === pipelineStage && status === 'memproses'">
                                <span class="stage-running-badge">Berjalan</span>
                            </template>
                            <template x-if="{{ $n }} < pipelineStage || ({{ $n }} === pipelineStage && status === 'selesai')">
                                <span class="stage-done-badge">Selesai</span>
                            </template>
                        </div>
                        <p class="text-[10px] text-[#475569] mt-0.5 stage-desc">{{ $stage['desc'] }}</p>
                    </div>

                    {{-- Stage number --}}
                    <span class="stage-number">{{ array_search($n, $activeStages) + 1 }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </template>

    {{-- ===== ANIMATION PANEL ===== --}}
    <template x-if="status === 'memproses'">
        <div class="pipeline-card overflow-hidden flex justify-center items-center min-h-[260px] relative">

            {{-- Stage 1: Rekon -- radar/satellite animation --}}
            <div x-show="pipelineStage <= 1" x-transition:enter="transition opacity duration-500"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                class="absolute inset-0 flex items-center justify-center">
                <div class="anim-inisialisasi relative w-full max-w-[320px] h-[200px] overflow-hidden flex items-center justify-center rounded-xl bg-transparent border border-[#1e2d4a]/50">
                    <div class="nebula"></div>
                    <div class="grid-plane"></div>
                    <div class="stars-container">
                        <div class="star-layer"></div><div class="star-layer"></div><div class="star-layer"></div>
                    </div>
                    <div class="button-container scale-75">
                        <div class="button-hexagons">
                            <div class="hexagon"></div><div class="hexagon"></div><div class="hexagon"></div>
                            <div class="hexagon"></div><div class="hexagon"></div><div class="hexagon"></div>
                        </div>
                        <div class="digital-glyphs">
                            <div class="digital-glyph">DNS_RECON.INIT()</div>
                            <div class="digital-glyph">WHOIS QUERY → TARGET</div>
                            <div class="digital-glyph">01001001 01010000 00100000 01000111 01000101 01001111</div>
                            <div class="digital-glyph">ASSET_MAP.BUILDING</div>
                        </div>
                        <button class="holo-button cursor-default">
                            <div class="button-text">REKON ASET</div>
                            <div class="holo-glow"></div>
                            <div class="button-glitch"></div>
                            <div class="corner-accents">
                                <div class="corner-accent"></div><div class="corner-accent"></div>
                                <div class="corner-accent"></div><div class="corner-accent"></div>
                            </div>
                            <div class="holo-lines">
                                <div class="holo-line"></div><div class="holo-line"></div>
                                <div class="holo-line"></div><div class="holo-line"></div>
                            </div>
                            <div class="scan-line"></div>
                            <div class="holo-particles">
                                <div class="holo-particle"></div><div class="holo-particle"></div><div class="holo-particle"></div>
                                <div class="holo-particle"></div><div class="holo-particle"></div><div class="holo-particle"></div>
                            </div>
                        </button>
                        <div class="sound-wave">
                            <div class="wave-bar"></div><div class="wave-bar"></div><div class="wave-bar"></div>
                            <div class="wave-bar"></div><div class="wave-bar"></div><div class="wave-bar"></div>
                            <div class="wave-bar"></div><div class="wave-bar"></div><div class="wave-bar"></div>
                            <div class="wave-bar"></div><div class="wave-bar"></div><div class="wave-bar"></div>
                            <div class="wave-bar"></div><div class="wave-bar"></div><div class="wave-bar"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stage 2-4: Scanning animation (tech stack, keamanan, SSL) --}}
            <div x-show="pipelineStage >= 2 && pipelineStage <= 4" x-transition:enter="transition opacity duration-500"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                class="absolute inset-0 flex items-center justify-center">
                <div class="scan-loader scale-50 md:scale-75">
                    <div class="modelViewPort">
                        <div class="eva">
                            <div class="head">
                                <div class="eyeChamber">
                                    <div class="eye"></div><div class="eye"></div>
                                </div>
                            </div>
                            <div class="body">
                                <div class="hand"></div><div class="hand"></div>
                                <div class="scannerThing"></div>
                                <div class="scannerOrigin"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stage 5: Threat Intelligence / ML animation --}}
            <div x-show="pipelineStage === 5" x-transition:enter="transition opacity duration-500"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                class="absolute inset-0 flex items-center justify-center">
                <div class="analisis-main-container w-full h-[200px]">
                    <div class="analisis-loader w-full h-full flex justify-center items-center">
                        <svg viewBox="0 0 800 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-full max-w-[300px]">
                            <defs>
                                <linearGradient id="chipGradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#2d2d2d"></stop><stop offset="100%" stop-color="#0f0f0f"></stop>
                                </linearGradient>
                                <linearGradient id="textGradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#eeeeee"></stop><stop offset="100%" stop-color="#888888"></stop>
                                </linearGradient>
                                <linearGradient id="pinGradient" x1="1" y1="0" x2="0" y2="0">
                                    <stop offset="0%" stop-color="#bbbbbb"></stop>
                                    <stop offset="50%" stop-color="#888888"></stop>
                                    <stop offset="100%" stop-color="#555555"></stop>
                                </linearGradient>
                            </defs>
                            <g id="traces">
                                <path d="M100 100 H200 V210 H326" class="trace-bg"></path>
                                <path d="M100 100 H200 V210 H326" class="trace-flow purple"></path>
                                <path d="M80 180 H180 V230 H326" class="trace-bg"></path>
                                <path d="M80 180 H180 V230 H326" class="trace-flow blue"></path>
                                <path d="M60 260 H150 V250 H326" class="trace-bg"></path>
                                <path d="M60 260 H150 V250 H326" class="trace-flow yellow"></path>
                                <path d="M100 350 H200 V270 H326" class="trace-bg"></path>
                                <path d="M100 350 H200 V270 H326" class="trace-flow green"></path>
                                <path d="M700 90 H560 V210 H474" class="trace-bg"></path>
                                <path d="M700 90 H560 V210 H474" class="trace-flow blue"></path>
                                <path d="M740 160 H580 V230 H474" class="trace-bg"></path>
                                <path d="M740 160 H580 V230 H474" class="trace-flow green"></path>
                                <path d="M720 250 H590 V250 H474" class="trace-bg"></path>
                                <path d="M720 250 H590 V250 H474" class="trace-flow red"></path>
                                <path d="M680 340 H570 V270 H474" class="trace-bg"></path>
                                <path d="M680 340 H570 V270 H474" class="trace-flow yellow"></path>
                            </g>
                            <rect x="330" y="190" width="140" height="100" rx="20" ry="20" fill="url(#chipGradient)" stroke="#222" stroke-width="3" filter="drop-shadow(0 0 6px rgba(0,0,0,0.8))"></rect>
                            <g>
                                <rect x="322" y="205" width="8" height="10" fill="url(#pinGradient)" rx="2"></rect>
                                <rect x="322" y="225" width="8" height="10" fill="url(#pinGradient)" rx="2"></rect>
                                <rect x="322" y="245" width="8" height="10" fill="url(#pinGradient)" rx="2"></rect>
                                <rect x="322" y="265" width="8" height="10" fill="url(#pinGradient)" rx="2"></rect>
                            </g>
                            <g>
                                <rect x="470" y="205" width="8" height="10" fill="url(#pinGradient)" rx="2"></rect>
                                <rect x="470" y="225" width="8" height="10" fill="url(#pinGradient)" rx="2"></rect>
                                <rect x="470" y="245" width="8" height="10" fill="url(#pinGradient)" rx="2"></rect>
                                <rect x="470" y="265" width="8" height="10" fill="url(#pinGradient)" rx="2"></rect>
                            </g>
                            <text x="400" y="240" font-family="Arial, sans-serif" font-size="16" fill="url(#textGradient)" text-anchor="middle" alignment-baseline="middle">CVE CORR.</text>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Stage 6: Finalisasi AI --}}
            <div x-show="pipelineStage >= 6" x-transition:enter="transition opacity duration-500"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                class="absolute inset-0 flex items-center justify-center">
                <div class="final-loader-wrapper scale-[1.3] md:scale-[1.8]">
                    <span class="final-loader-letter">A</span>
                    <span class="final-loader-letter">N</span>
                    <span class="final-loader-letter">A</span>
                    <span class="final-loader-letter">L</span>
                    <span class="final-loader-letter">I</span>
                    <span class="final-loader-letter">S</span>
                    <span class="final-loader-letter">I</span>
                    <span class="final-loader-letter">S</span>
                    <span class="final-loader-letter">&nbsp;</span>
                    <span class="final-loader-letter">A</span>
                    <span class="final-loader-letter">I</span>
                    <div class="final-loader"></div>
                </div>
            </div>

        </div>
    </template>

    {{-- ===== FAILED STATE ===== --}}
    <template x-if="status === 'gagal'">
        <div class="pipeline-card p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-5 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h3 class="text-base font-semibold text-red-400 mb-2">Analisis Gagal</h3>
            <p class="text-sm text-[#94a3b8] mb-2">Terjadi kesalahan saat memproses.</p>
            <p x-show="error" class="text-xs text-red-400/70 mb-6 px-4 py-2 rounded-lg bg-red-500/5 border border-red-500/10 inline-block" x-text="error"></p>
            <div class="flex justify-center gap-3 mt-4">
                <a href="{{ url()->previous() }}" class="px-5 py-2.5 rounded-xl bg-[#1e2d4a] text-sm text-white hover:bg-[#2a3a5c] transition-colors">← Kembali</a>
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-sm text-white hover:shadow-lg transition-all">Dashboard</a>
            </div>
        </div>
    </template>

</div>

<style>
/* ======================================================= */
/* PIPELINE CARD BASE                                       */
/* ======================================================= */
.pipeline-card {
    background: #0f1629;
    border: 1px solid #1e2d4a;
    border-radius: 1rem;
}

/* ======================================================= */
/* BADGES                                                   */
/* ======================================================= */
.pipeline-badge-cyan {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 10px; border-radius: 999px;
    background: rgba(0,212,255,0.08); color: #00d4ff;
    border: 1px solid rgba(0,212,255,0.15); font-weight: 500;
}
.pipeline-badge-purple {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 10px; border-radius: 999px;
    background: rgba(124,58,237,0.1); color: #a78bfa;
    border: 1px solid rgba(124,58,237,0.2); font-weight: 500;
}
.pipeline-badge-dim {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 10px; border-radius: 999px;
    background: #111827; color: #64748b;
    border: 1px solid #1e2d4a; font-weight: 500;
}

/* ======================================================= */
/* PIPELINE STAGE ROW                                       */
/* ======================================================= */
.pipeline-stage-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 14px; border-radius: 10px;
    border: 1px solid transparent;
    transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
    background: transparent;
}
.pipeline-stage-row.is-active {
    background: rgba(0,212,255,0.04);
    border-color: rgba(0,212,255,0.18);
    box-shadow: 0 0 20px rgba(0,212,255,0.06);
}
.pipeline-stage-row.is-done {
    background: rgba(52,211,153,0.03);
    border-color: rgba(52,211,153,0.1);
}
.pipeline-stage-row.is-pending {
    opacity: 0.4;
}

.stage-icon-wrap {
    width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px;
    flex-shrink: 0;
}
.is-done .stage-icon-wrap    { background: rgba(52,211,153,0.1); }
.is-active .stage-icon-wrap  { background: rgba(0,212,255,0.12); }
.is-pending .stage-icon-wrap { background: #1e2d4a; }

.stage-label {
    transition: color 0.3s;
    color: #94a3b8;
}
.is-done .stage-label   { color: #34d399; }
.is-active .stage-label { color: #ffffff; }

.stage-desc { transition: color 0.3s; }
.is-active .stage-desc { color: #64748b; }

.stage-running-badge {
    font-size: 9px; font-weight: 600; letter-spacing: 0.05em;
    padding: 1px 6px; border-radius: 999px;
    background: rgba(0,212,255,0.12); color: #00d4ff;
    border: 1px solid rgba(0,212,255,0.2);
    animation: pulse-badge 1.5s ease-in-out infinite;
}
@keyframes pulse-badge {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.stage-done-badge {
    font-size: 9px; font-weight: 600;
    padding: 1px 6px; border-radius: 999px;
    background: rgba(52,211,153,0.1); color: #34d399;
    border: 1px solid rgba(52,211,153,0.15);
}

.stage-number {
    font-size: 10px; font-family: 'JetBrains Mono', monospace;
    color: #334155; flex-shrink: 0;
}
.is-done .stage-number   { color: #065f46; }
.is-active .stage-number { color: #0e7490; }

/* ======================================================= */
/* PROGRESS BAR SHIMMER                                     */
/* ======================================================= */
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.animate-shimmer { animation: shimmer 2s infinite; }

/* ======================================================= */
/* STAGE 1: REKON ANIMATION (kept from original)           */
/* ======================================================= */
.stars-container { position: absolute; width: 100%; height: 100%; perspective: 500px; transform-style: preserve-3d; }
.star-layer { position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0.8; }
.star-layer:nth-child(1) { transform: translateZ(-50px); animation: star-drift 150s linear infinite; }
.star-layer:nth-child(2) { transform: translateZ(-100px); animation: star-drift 200s linear infinite reverse; opacity: 0.6; }
.star-layer:nth-child(3) { transform: translateZ(-200px); animation: star-drift 250s linear infinite; opacity: 0.4; }
@keyframes star-drift { 0% { transform: translateZ(-50px) translateY(0); } 100% { transform: translateZ(-50px) translateY(100%); } }
.star-layer::before, .star-layer::after { content: ""; position: absolute; width: 100%; height: 100%; }
.star-layer:nth-child(1)::before { background-image: radial-gradient(1px 1px at 10% 10%, white 100%, transparent), radial-gradient(1px 1px at 20% 20%, white 100%, transparent), radial-gradient(2px 2px at 30% 30%, white 100%, transparent), radial-gradient(1px 1px at 40% 40%, white 100%, transparent), radial-gradient(2px 2px at 50% 50%, white 100%, transparent), radial-gradient(1px 1px at 60% 60%, white 100%, transparent), radial-gradient(2px 2px at 70% 70%, white 100%, transparent), radial-gradient(1px 1px at 80% 80%, white 100%, transparent), radial-gradient(2px 2px at 90% 90%, white 100%, transparent), radial-gradient(1px 1px at 15% 85%, white 100%, transparent); }
.nebula { position: absolute; width: 120%; height: 120%; top: -10%; left: -10%; background: radial-gradient(ellipse at 30% 30%, rgba(63,0,113,0.3) 0%, rgba(63,0,113,0) 70%), radial-gradient(ellipse at 70% 60%, rgba(0,113,167,0.3) 0%, rgba(0,113,167,0) 70%); filter: blur(30px); opacity: 0.5; animation: nebula-shift 30s infinite alternate ease-in-out; }
@keyframes nebula-shift { 0% { transform: scale(1) rotate(0deg); opacity:0.3; } 50% { opacity: 0.5; } 100% { transform: scale(1.2) rotate(5deg); opacity:0.4; } }
.grid-plane { position: absolute; width: 200%; height: 200%; top: -50%; left: -50%; background-image: linear-gradient(rgba(0,162,255,0.15) 1px, transparent 1px), linear-gradient(90deg, rgba(0,162,255,0.15) 1px, transparent 1px); background-size: 40px 40px; transform: perspective(500px) rotateX(60deg); transform-origin: center; animation: grid-move 20s linear infinite; opacity: 0.3; }
@keyframes grid-move { 0% { transform: perspective(500px) rotateX(60deg) translateY(0); } 100% { transform: perspective(500px) rotateX(60deg) translateY(40px); } }
.button-container { position: relative; perspective: 1000px; transform-style: preserve-3d; z-index: 10; }
.holo-button { position: relative; width: 280px; height: 80px; background: rgba(10,10,30,0.6); border: none; color: rgba(0,221,255,0.8); font-family: "Orbitron", sans-serif; font-size: 18px; font-weight: 700; letter-spacing: 3px; text-shadow: 0 0 10px rgba(0,221,255,0.5); overflow: hidden; transform-style: preserve-3d; transition: all 0.3s ease; z-index: 5; border-radius: 2px; }
.holo-button::before { content: ""; position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px; background: linear-gradient(45deg, rgba(0,0,40,0.8) 0%, rgba(0,20,50,0.8) 50%, rgba(5,10,30,0.8) 100%); z-index: -1; border-radius: 1px; }
.holo-glow { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 100%; height: 100%; background: radial-gradient(ellipse at center, rgba(0,221,255,0.2) 0%, rgba(0,0,0,0) 70%); pointer-events: none; z-index: 1; opacity: 0.7; filter: blur(10px); animation: glow-pulse 3s infinite alternate; }
@keyframes glow-pulse { 0% { opacity: 0.4; filter: blur(10px) brightness(0.8); } 100% { opacity: 0.7; filter: blur(15px) brightness(1.2); } }
.holo-lines { position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; pointer-events: none; z-index: 2; }
.holo-line { position: absolute; background: rgba(0,221,255,0.5); filter: blur(1px); }
.holo-line:nth-child(1) { top: 0; left: 30px; width: 1.5px; height: 100%; animation: line-pulse 2s infinite alternate; animation-delay:-0.5s; }
.holo-line:nth-child(2) { top: 0; right: 30px; width: 1.5px; height: 100%; animation: line-pulse 2s infinite alternate; animation-delay:-1s; }
.holo-line:nth-child(3) { top: 15px; left: 0; width: 100%; height: 1.5px; animation: line-pulse 2s infinite alternate; animation-delay:-1.5s; }
.holo-line:nth-child(4) { bottom: 15px; left: 0; width: 100%; height: 1.5px; animation: line-pulse 2s infinite alternate; animation-delay:-2s; }
@keyframes line-pulse { 0% { opacity: 0.3; background: rgba(0,221,255,0.5); } 100% { opacity: 0.7; background: rgba(255,0,222,0.5); } }
.scan-line { position: absolute; width: 100%; height: 3px; background: linear-gradient(90deg, rgba(0,0,0,0) 0%, rgba(0,221,255,0.5) 20%, rgba(255,255,255,0.8) 50%, rgba(255,0,222,0.5) 80%, rgba(0,0,0,0) 100%); top: 0; left: 0; filter: blur(1px); opacity: 0; z-index: 2; pointer-events: none; animation: scan 2s infinite; }
@keyframes scan { 0% { top:-5px; opacity:0; } 15%, 85% { opacity:0.7; } 100% { top:85px; opacity:0; } }
.holo-particles { position: absolute; width: 100%; height: 100%; top: 0; left: 0; overflow: hidden; pointer-events: none; z-index: 1; }
.holo-particle { position: absolute; background: white; box-shadow: 0 0 10px rgba(0,221,255,0.7); border-radius: 50%; filter: blur(1px); opacity: 0; animation: particle-float 3s infinite ease-out; }
.holo-particle:nth-child(1) { width:3px; height:3px; top:70%; left:30%; animation-delay:0.2s; }
.holo-particle:nth-child(2) { width:2px; height:2px; top:60%; left:40%; animation-delay:0.5s; }
.holo-particle:nth-child(3) { width:4px; height:4px; top:80%; left:60%; animation-delay:0.8s; }
.holo-particle:nth-child(4) { width:2px; height:2px; top:70%; left:70%; animation-delay:1.1s; }
.holo-particle:nth-child(5) { width:3px; height:3px; top:75%; left:50%; animation-delay:1.4s; }
.holo-particle:nth-child(6) { width:2px; height:2px; top:65%; left:65%; animation-delay:1.7s; }
@keyframes particle-float { 0% { transform:translateY(0) rotate(0deg); opacity:0; } 10% { opacity:0.8; } 100% { transform:translateY(-40px) rotate(360deg); opacity:0; } }
.button-hexagons { position: absolute; width: 380px; height: 180px; top: 50%; left: 50%; transform: translate(-50%,-50%); pointer-events: none; z-index: 1; }
.hexagon { position: absolute; width: 30px; height: 17.32px; background: transparent; border: 1.5px solid rgba(0,221,255,0.3); transform: rotate(30deg); opacity: 0; animation: hexagon-pulse 3s infinite; }
.hexagon::before, .hexagon::after { content: ""; position: absolute; width: 100%; height: 100%; top: 0; left: 0; border: 1.5px solid rgba(0,221,255,0.3); }
.hexagon::before { transform: rotate(60deg); }
.hexagon::after { transform: rotate(-60deg); }
.hexagon:nth-child(1) { top:20px; left:20px; animation-delay:0.1s; }
.hexagon:nth-child(2) { top:20px; right:20px; animation-delay:0.3s; }
.hexagon:nth-child(3) { bottom:20px; left:20px; animation-delay:0.5s; }
.hexagon:nth-child(4) { bottom:20px; right:20px; animation-delay:0.7s; }
.hexagon:nth-child(5) { top:50%; left:0; transform:translate(0,-50%) rotate(30deg); animation-delay:0.9s; }
.hexagon:nth-child(6) { top:50%; right:0; transform:translate(0,-50%) rotate(30deg); animation-delay:1.1s; }
@keyframes hexagon-pulse { 0% { opacity:0; transform:rotate(30deg) scale(0.8); } 20%, 80% { opacity:0.5; transform:rotate(30deg) scale(1); } 100% { opacity:0; transform:rotate(30deg) scale(1.2); } }
.digital-glyphs { position: absolute; width: 400px; height: 200px; top: 50%; left: 50%; transform: translate(-50%,-50%); pointer-events: none; z-index: 1; }
.digital-glyph { position: absolute; color: rgba(0,221,255,0.5); font-size: 10px; font-family: "Orbitron", monospace; white-space: nowrap; opacity: 0; animation: glyph-fade 4s infinite; }
.digital-glyph:nth-child(1) { top:-30px; left:60px; animation-delay:0.2s; }
.digital-glyph:nth-child(2) { top:-20px; right:40px; animation-delay:0.6s; }
.digital-glyph:nth-child(3) { bottom:-30px; left:80px; animation-delay:1s; }
.digital-glyph:nth-child(4) { bottom:-20px; right:60px; animation-delay:1.4s; }
@keyframes glyph-fade { 0%, 100% { opacity:0; transform:translateY(0); } 10%, 90% { opacity:0.7; } 100% { opacity:0; transform:translateY(-10px); } }
.corner-accents { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; }
.corner-accent { position: absolute; width: 15px; height: 15px; border: 2px solid rgba(0,221,255,0.7); }
.corner-accent:nth-child(1) { top:0; left:0; border-right:none; border-bottom:none; }
.corner-accent:nth-child(2) { top:0; right:0; border-left:none; border-bottom:none; }
.corner-accent:nth-child(3) { bottom:0; left:0; border-right:none; border-top:none; }
.corner-accent:nth-child(4) { bottom:0; right:0; border-left:none; border-top:none; }
.button-glitch { position: absolute; top:0; left:0; width:100%; height:100%; z-index:3; opacity:0; animation: button-glitch 8s infinite; }
@keyframes button-glitch { 0%, 94%, 96%, 100% { opacity:0; } 94.5% { opacity:0.8; transform:translate(5px,-2px) skew(-5deg,2deg); background:rgba(255,0,222,0.2); } 95% { opacity:0.8; transform:translate(-5px,2px) skew(5deg,-2deg); background:rgba(0,221,255,0.2); } 95.5% { opacity:0.8; transform:translate(2px,0) skew(-2deg,0); background:rgba(255,255,255,0.2); } }
.sound-wave { position: absolute; bottom:-30px; left:0; width:100%; height:20px; display:flex; justify-content:center; align-items:center; pointer-events:none; }
.wave-bar { width:2px; height:5px; background:rgba(0,221,255,0.5); margin:0 2px; border-radius:1px; animation:wave-animation 1.5s infinite; }
@keyframes wave-animation { 0%, 100% { height:5px; } 50% { height:15px; } }
.wave-bar:nth-child(1) { animation-delay:0.1s; } .wave-bar:nth-child(2) { animation-delay:0.2s; } .wave-bar:nth-child(3) { animation-delay:0.3s; } .wave-bar:nth-child(4) { animation-delay:0.4s; } .wave-bar:nth-child(5) { animation-delay:0.5s; } .wave-bar:nth-child(6) { animation-delay:0.6s; } .wave-bar:nth-child(7) { animation-delay:0.7s; } .wave-bar:nth-child(8) { animation-delay:0.8s; } .wave-bar:nth-child(9) { animation-delay:0.9s; } .wave-bar:nth-child(10) { animation-delay:1s; } .wave-bar:nth-child(11) { animation-delay:0.9s; } .wave-bar:nth-child(12) { animation-delay:0.8s; } .wave-bar:nth-child(13) { animation-delay:0.7s; } .wave-bar:nth-child(14) { animation-delay:0.6s; } .wave-bar:nth-child(15) { animation-delay:0.5s; }
.button-text { position: relative; z-index: 5; transform-style: preserve-3d; }

/* ======================================================= */
/* STAGE 2-4: SCANNING ANIMATION                           */
/* ======================================================= */
.modelViewPort { perspective: 1000px; width: 20rem; aspect-ratio: 1; border-radius: 50%; display: flex; justify-content: center; align-items: center; background: transparent; overflow: hidden; }
.eva { --EVA-ROTATION-DURATION: 4s; transform-style: preserve-3d; animation: rotateRight var(--EVA-ROTATION-DURATION) linear infinite alternate; }
.head { position: relative; width: 6rem; height: 4rem; border-radius: 48% 53% 45% 55% / 79% 79% 20% 22%; background: linear-gradient(to right, white 45%, gray); }
.eyeChamber { width: 4.5rem; height: 2.75rem; position: relative; left: 50%; top: 55%; border-radius: 45% 53% 45% 48% / 62% 59% 35% 34%; background-color: #0c203c; box-shadow: 0px 0px 2px 2px white, inset 0px 0px 0px 2px black; transform: translate(-50%, -50%); animation: moveRight var(--EVA-ROTATION-DURATION) linear infinite alternate; }
.eye { width: 1.2rem; height: 1.5rem; position: absolute; border-radius: 50%; }
.eye:first-child { left: 12px; top: 50%; background: repeating-linear-gradient(65deg, #9bdaeb 0px, #9bdaeb 1px, white 2px); box-shadow: inset 0px 0px 5px #04b8d5, 0px 0px 15px 1px #0bdaeb; transform: translate(0,-50%) rotate(-65deg); }
.eye:nth-child(2) { right: 12px; top: 50%; background: repeating-linear-gradient(-65deg, #9bdaeb 0px, #9bdaeb 1px, white 2px); box-shadow: inset 0px 0px 5px #04b8d5, 0px 0px 15px 1px #0bdaeb; transform: translate(0,-50%) rotate(65deg); }
.body { width: 6rem; height: 8rem; position: relative; margin-block-start: 0.25rem; border-radius: 47% 53% 45% 55% / 12% 9% 90% 88%; background: linear-gradient(to right, white 35%, gray); }
.hand { position: absolute; left: -1.5rem; top: 0.75rem; width: 2rem; height: 5.5rem; border-radius: 40%; background: linear-gradient(to left, white 15%, gray); box-shadow: 5px 0px 5px rgba(0,0,0,0.25); transform: rotateY(55deg) rotateZ(10deg); }
.hand:first-child { animation: compensateRotation var(--EVA-ROTATION-DURATION) linear infinite alternate; }
.hand:nth-child(2) { left: 92%; background: linear-gradient(to right, white 15%, gray); transform: rotateY(55deg) rotateZ(-10deg); animation: compensateRotationRight var(--EVA-ROTATION-DURATION) linear infinite alternate; }
.scannerThing { width: 0; height: 0; position: absolute; left: 60%; top: 10%; border-top: 180px solid #9bdaeb; border-left: 250px solid transparent; border-right: 250px solid transparent; transform-origin: top left; mask: linear-gradient(to right, white, transparent 35%); animation: glow 2s cubic-bezier(0.86,0,0.07,1) infinite; }
.scannerOrigin { position: absolute; width: 8px; aspect-ratio: 1; border-radius: 50%; left: 60%; top: 10%; background: #9bdaeb; box-shadow: inset 0px 0px 5px rgba(0,0,0,0.5); animation: moveRight var(--EVA-ROTATION-DURATION) linear infinite; }
@keyframes rotateRight { from { transform: rotateY(0deg); } to { transform: rotateY(25deg); } }
@keyframes moveRight { from { transform: translate(-50%,-50%); } to { transform: translate(-40%,-50%); } }
@keyframes compensateRotation { from { transform: rotateY(55deg) rotateZ(10deg); } to { transform: rotatey(30deg) rotateZ(10deg); } }
@keyframes compensateRotationRight { from { transform: rotateY(55deg) rotateZ(-10deg); } to { transform: rotateY(70deg) rotateZ(-10deg); } }
@keyframes glow { from { opacity:0; } 20% { opacity:1; } 45% { transform:rotate(-25deg); } 75% { transform:rotate(5deg); } 100% { opacity:0; } }

/* ======================================================= */
/* STAGE 5: THREAT INTELLIGENCE (circuit chip)             */
/* ======================================================= */
.trace-bg { stroke: #333; stroke-width: 1.8; fill: none; }
.trace-flow { stroke-width: 1.8; fill: none; stroke-dasharray: 40 400; stroke-dashoffset: 438; filter: drop-shadow(0 0 6px currentColor); animation: flow 3s cubic-bezier(0.5,0,0.9,1) infinite; }
.yellow { stroke: #ffea00; color: #ffea00; }
.blue { stroke: #00ccff; color: #00ccff; }
.green { stroke: #00ff15; color: #00ff15; }
.purple { stroke: #9900ff; color: #9900ff; }
.red { stroke: #ff3300; color: #ff3300; }
@keyframes flow { to { stroke-dashoffset: 0; } }

/* ======================================================= */
/* STAGE 6: FINALISASI AI                                  */
/* ======================================================= */
.final-loader-wrapper { position: relative; display: flex; align-items: center; justify-content: center; height: 120px; width: auto; font-family: "Poppins", "Inter", sans-serif; font-size: 1em; font-weight: 600; user-select: none; color: #fff; }
.final-loader { position: absolute; top: 0; left: 0; height: 100%; width: 100%; z-index: 1; background-color: transparent; mask: repeating-linear-gradient(90deg, transparent 0, transparent 6px, black 7px, black 8px); }
.final-loader::after { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: radial-gradient(circle at 50% 50%, #00d4ff 0%, transparent 50%), radial-gradient(circle at 45% 45%, #7c3aed 0%, transparent 45%), radial-gradient(circle at 55% 55%, #00d4ff 0%, transparent 45%), radial-gradient(circle at 45% 55%, #7c3aed 0%, transparent 45%), radial-gradient(circle at 55% 45%, #00d4ff 0%, transparent 45%); mask: radial-gradient(circle at 50% 50%, transparent 0%, transparent 10%, black 25%); animation: transform-animation 2s infinite alternate, opacity-animation 4s infinite; animation-timing-function: cubic-bezier(0.6,0.8,0.5,1); }
@keyframes transform-animation { 0% { transform: translate(-55%); } 100% { transform: translate(55%); } }
@keyframes opacity-animation { 0%, 100% { opacity:0; } 15% { opacity:1; } 65% { opacity:0; } }
.final-loader-letter { display: inline-block; opacity: 0; animation: loader-letter-anim 4s infinite linear; z-index: 2; }
.final-loader-letter:nth-child(1)  { animation-delay:0.1s; }
.final-loader-letter:nth-child(2)  { animation-delay:0.205s; }
.final-loader-letter:nth-child(3)  { animation-delay:0.31s; }
.final-loader-letter:nth-child(4)  { animation-delay:0.415s; }
.final-loader-letter:nth-child(5)  { animation-delay:0.521s; }
.final-loader-letter:nth-child(6)  { animation-delay:0.626s; }
.final-loader-letter:nth-child(7)  { animation-delay:0.731s; }
.final-loader-letter:nth-child(8)  { animation-delay:0.837s; }
.final-loader-letter:nth-child(9)  { animation-delay:0.942s; }
.final-loader-letter:nth-child(10) { animation-delay:1.047s; }
.final-loader-letter:nth-child(11) { animation-delay:1.15s; }
@keyframes loader-letter-anim { 0% { opacity:0; } 5% { opacity:1; text-shadow:0 0 4px #00d4ff; transform:scale(1.1) translateY(-2px); } 20% { opacity:0.2; } 100% { opacity:0; } }
</style>
@endsection
