@extends('layouts.app')
@section('title', 'Memproses Analisis — RedSim')
@section('header', 'Memproses Analisis')
@section('subheader', 'Harap tunggu sementara sistem menganalisis target Anda')

@section('content')
<div class="max-w-xl mx-auto mt-8" x-data="{
    status: '{{ $scan->status }}',
    step: '{{ $scan->progress_step ?? 'Memulai...' }}',
    persen: {{ $scan->progress_persen ?? 0 }},
    modeAi: '{{ $scan->mode_ai ?? 'dengan_ai' }}',
    error: '{{ $scan->error_message ?? '' }}',
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
                this.error = d.error_message || '';
                if (d.status === 'selesai') {
                    this.persen = 100;
                    this.step = 'Selesai!';
                    clearInterval(this.timer);
                    setTimeout(() => { window.location = '{{ route('analisis.hasil', $scan) }}'; }, 800);
                }
                if (d.status === 'gagal') {
                    clearInterval(this.timer);
                }
            } catch(e) { console.error(e); }
        }, 2000);
    }
}">
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-8 mb-6">
        {{-- Processing State --}}
        <template x-if="status === 'memproses'">
            <div class="text-center">
                <h3 class="text-lg font-semibold mb-1 text-white">Sedang Menganalisis...</h3>
                <p class="text-sm text-[#94a3b8] mb-6" x-text="step"></p>

                {{-- Progress Bar --}}
                <div class="w-full bg-[#0a0e1a] rounded-full h-3 mb-3 overflow-hidden border border-[#1e2d4a]">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] transition-all duration-700 ease-out relative overflow-hidden"
                        :style="'width: ' + persen + '%'">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
                    </div>
                </div>

                <p class="text-xs text-[#64748b]" x-text="persen + '% selesai'"></p>

                {{-- Mode Indicator --}}
                <div class="mt-6 flex justify-center gap-2">
                    <span class="px-3 py-1 rounded-full text-[10px] font-medium"
                        :class="modeAi === 'dengan_ai' ? 'bg-[#00d4ff]/10 text-[#00d4ff]' : 'bg-yellow-500/10 text-yellow-400'"
                        x-text="modeAi === 'dengan_ai' ? '🤖 Dengan AI' : '⚡ Tanpa AI'"></span>
                    <span class="px-3 py-1 rounded-full text-[10px] bg-[#1e2d4a] text-[#94a3b8]">{{ ucfirst($scan->tipe_scan) }}</span>
                </div>

                {{-- Step indicators --}}
                <div class="mt-6 grid grid-cols-4 gap-1">
                    @php
                        $steps = [
                            ['label' => 'Inisialisasi', 'threshold' => 10],
                            ['label' => 'Scanning', 'threshold' => 40],
                            ['label' => 'Analisis', 'threshold' => 70],
                            ['label' => 'Finalisasi', 'threshold' => 95],
                        ];
                    @endphp
                    @foreach($steps as $s)
                    <div class="text-center">
                        <div class="w-full h-1 rounded-full mb-1.5 transition-all duration-500"
                            :class="persen >= {{ $s['threshold'] }} ? 'bg-gradient-to-r from-[#00d4ff] to-[#7c3aed]' : 'bg-[#1e2d4a]'"></div>
                        <p class="text-[10px] transition-colors"
                            :class="persen >= {{ $s['threshold'] }} ? 'text-[#00d4ff]' : 'text-[#475569]'">{{ $s['label'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </template>

        {{-- Failed State --}}
        <template x-if="status === 'gagal'">
            <div class="text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-red-500/10 flex items-center justify-center">
                    <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-red-400 mb-2">Analisis Gagal</h3>
                <p class="text-sm text-[#94a3b8] mb-2">Terjadi kesalahan saat memproses.</p>
                <p x-show="error" class="text-xs text-red-400/70 mb-6 px-4 py-2 rounded-lg bg-red-500/5 border border-red-500/10 inline-block" x-text="error"></p>
                <div class="flex justify-center gap-3 mt-4">
                    <a href="{{ url()->previous() }}" class="px-6 py-2.5 rounded-xl bg-[#1e2d4a] text-sm text-white hover:bg-[#2a3a5c] transition-colors">← Kembali</a>
                    <a href="{{ route('dashboard') }}" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-sm text-white hover:shadow-lg transition-all">Dashboard</a>
                </div>
            </div>
        </template>
    </div>

    {{-- Animated Spinner / Status Visual --}}
    <template x-if="status === 'memproses'">
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-8 overflow-hidden flex justify-center items-center min-h-[300px]">
            <div class="relative flex justify-center items-center w-full h-full">
                
                {{-- INISIALISASI ANIMATION --}}
                    <div x-show="persen < 40" x-transition.opacity class="absolute inset-0 flex items-center justify-center">
                        <div class="anim-inisialisasi relative w-full max-w-[320px] h-[200px] overflow-hidden flex items-center justify-center rounded-xl bg-transparent border border-[#1e2d4a]/50">
                            <div class="nebula"></div>
                            <div class="grid-plane"></div>
                            <div class="stars-container">
                              <div class="star-layer"></div>
                              <div class="star-layer"></div>
                              <div class="star-layer"></div>
                            </div>
                            <div class="button-container scale-75">
                              <div class="button-hexagons">
                                <div class="hexagon"></div><div class="hexagon"></div><div class="hexagon"></div>
                                <div class="hexagon"></div><div class="hexagon"></div><div class="hexagon"></div>
                              </div>
                              <div class="digital-glyphs">
                                <div class="digital-glyph">0x89F2 EXEC PROTOCOL</div>
                                <div class="digital-glyph">SYS.QUANTUM.INIT()</div>
                                <div class="digital-glyph">01011010 01000001 01010000 01010101 01010011 01001011</div>
                                <div class="digital-glyph">HOLO-CONN INITIALIZED</div>
                              </div>
                              <button class="holo-button cursor-default">
                                <div class="button-text">INISIALISASI</div>
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

                    {{-- SCANNING ANIMATION --}}
                    <div x-show="persen >= 40 && persen < 70" x-transition.opacity class="absolute inset-0 flex items-center justify-center">
                        <div class="scan-loader scale-50 md:scale-75">
                          <div class="modelViewPort">
                            <div class="eva">
                              <div class="head">
                                <div class="eyeChamber">
                                  <div class="eye"></div>
                                  <div class="eye"></div>
                                </div>
                              </div>
                              <div class="body">
                                <div class="hand"></div>
                                <div class="hand"></div>
                                <div class="scannerThing"></div>
                                <div class="scannerOrigin"></div>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>

                    {{-- ANALISIS ANIMATION --}}
                    <div x-show="persen >= 70 && persen < 95" x-transition.opacity class="absolute inset-0 flex items-center justify-center">
                        <div class="analisis-main-container w-full h-[200px]">
                          <div class="analisis-loader w-full h-full flex justify-center items-center">
                            <svg viewBox="0 0 800 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-full max-w-[300px]">
                              <defs>
                                <linearGradient id="chipGradient" x1="0" y1="0" x2="0" y2="1">
                                  <stop offset="0%" stop-color="#2d2d2d"></stop>
                                  <stop offset="100%" stop-color="#0f0f0f"></stop>
                                </linearGradient>
                                <linearGradient id="textGradient" x1="0" y1="0" x2="0" y2="1">
                                  <stop offset="0%" stop-color="#eeeeee"></stop>
                                  <stop offset="100%" stop-color="#888888"></stop>
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
                              <text x="400" y="240" font-family="Arial, sans-serif" font-size="22" fill="url(#textGradient)" text-anchor="middle" alignment-baseline="middle">
                                Analisis
                              </text>
                              <circle cx="100" cy="100" r="5" fill="black"></circle>
                              <circle cx="80" cy="180" r="5" fill="black"></circle>
                              <circle cx="60" cy="260" r="5" fill="black"></circle>
                              <circle cx="100" cy="350" r="5" fill="black"></circle>
                              <circle cx="700" cy="90" r="5" fill="black"></circle>
                              <circle cx="740" cy="160" r="5" fill="black"></circle>
                              <circle cx="720" cy="250" r="5" fill="black"></circle>
                              <circle cx="680" cy="340" r="5" fill="black"></circle>
                            </svg>
                          </div>
                        </div>
                    </div>

                    {{-- FINALISASI ANIMATION --}}
                    <div x-show="persen >= 95" x-transition.opacity class="absolute inset-0 flex items-center justify-center">
                        <div class="final-loader-wrapper scale-[1.3] md:scale-[1.8]">
                          <span class="final-loader-letter">F</span>
                          <span class="final-loader-letter">I</span>
                          <span class="final-loader-letter">N</span>
                          <span class="final-loader-letter">A</span>
                          <span class="final-loader-letter">L</span>
                          <span class="final-loader-letter">I</span>
                          <span class="final-loader-letter">S</span>
                          <span class="final-loader-letter">A</span>
                          <span class="final-loader-letter">S</span>
                          <span class="final-loader-letter">I</span>
                          <div class="final-loader"></div>
                        </div>
                    </div>

            </div>
        </div>
    </template>
</div>

<style>
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.animate-shimmer { animation: shimmer 2s infinite; }

/* ========================================= */
/* 1. INISIALISASI CSS */
/* ========================================= */
.stars-container {
  position: absolute; width: 100%; height: 100%;
  perspective: 500px; transform-style: preserve-3d;
}
.star-layer {
  position: absolute; width: 100%; height: 100%;
  top: 0; left: 0; opacity: 0.8;
}
.star-layer:nth-child(1) { transform: translateZ(-50px); animation: star-drift 150s linear infinite; }
.star-layer:nth-child(2) { transform: translateZ(-100px); animation: star-drift 200s linear infinite reverse; opacity: 0.6; }
.star-layer:nth-child(3) { transform: translateZ(-200px); animation: star-drift 250s linear infinite; opacity: 0.4; }

@keyframes star-drift {
  0% { transform: translateZ(-50px) translateY(0); }
  100% { transform: translateZ(-50px) translateY(100%); }
}

.star-layer::before, .star-layer::after { content: ""; position: absolute; width: 100%; height: 100%; }
.star-layer:nth-child(1)::before {
  background-image: radial-gradient(1px 1px at 10% 10%, white 100%, transparent),
    radial-gradient(1px 1px at 20% 20%, white 100%, transparent),
    radial-gradient(2px 2px at 30% 30%, white 100%, transparent),
    radial-gradient(1px 1px at 40% 40%, white 100%, transparent),
    radial-gradient(2px 2px at 50% 50%, white 100%, transparent),
    radial-gradient(1px 1px at 60% 60%, white 100%, transparent),
    radial-gradient(2px 2px at 70% 70%, white 100%, transparent),
    radial-gradient(1px 1px at 80% 80%, white 100%, transparent),
    radial-gradient(2px 2px at 90% 90%, white 100%, transparent),
    radial-gradient(1px 1px at 15% 85%, white 100%, transparent);
}

.nebula {
  position: absolute; width: 120%; height: 120%; top: -10%; left: -10%;
  background: radial-gradient(ellipse at 30% 30%, rgba(63, 0, 113, 0.3) 0%, rgba(63, 0, 113, 0) 70%),
    radial-gradient(ellipse at 70% 60%, rgba(0, 113, 167, 0.3) 0%, rgba(0, 113, 167, 0) 70%),
    radial-gradient(ellipse at 50% 50%, rgba(167, 0, 157, 0.2) 0%, rgba(167, 0, 157, 0) 70%);
  filter: blur(30px); opacity: 0.5; animation: nebula-shift 30s infinite alternate ease-in-out;
}
@keyframes nebula-shift {
  0% { transform: scale(1) rotate(0deg); opacity: 0.3; }
  50% { opacity: 0.5; }
  100% { transform: scale(1.2) rotate(5deg); opacity: 0.4; }
}

.grid-plane {
  position: absolute; width: 200%; height: 200%; top: -50%; left: -50%;
  background-image: linear-gradient(rgba(0, 162, 255, 0.15) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0, 162, 255, 0.15) 1px, transparent 1px);
  background-size: 40px 40px; transform: perspective(500px) rotateX(60deg);
  transform-origin: center; animation: grid-move 20s linear infinite; opacity: 0.3;
}
@keyframes grid-move {
  0% { transform: perspective(500px) rotateX(60deg) translateY(0); }
  100% { transform: perspective(500px) rotateX(60deg) translateY(40px); }
}

.button-container { position: relative; perspective: 1000px; transform-style: preserve-3d; z-index: 10; }
.holo-button {
  position: relative; width: 280px; height: 80px; background: rgba(10, 10, 30, 0.6);
  border: none; color: rgba(0, 221, 255, 0.8); font-family: "Orbitron", sans-serif;
  font-size: 18px; font-weight: 700; letter-spacing: 3px; text-shadow: 0 0 10px rgba(0, 221, 255, 0.5);
  overflow: hidden; transform-style: preserve-3d; transition: all 0.3s ease;
  z-index: 5; border-radius: 2px;
}
.holo-button::before {
  content: ""; position: absolute; top: 2px; left: 2px; right: 2px; bottom: 2px;
  background: linear-gradient(45deg, rgba(0, 0, 40, 0.8) 0%, rgba(0, 20, 50, 0.8) 50%, rgba(5, 10, 30, 0.8) 100%);
  z-index: -1; border-radius: 1px;
}
.holo-glow {
  position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; height: 100%;
  background: radial-gradient(ellipse at center, rgba(0, 221, 255, 0.2) 0%, rgba(0, 0, 0, 0) 70%);
  pointer-events: none; z-index: 1; opacity: 0.7; filter: blur(10px); animation: glow-pulse 3s infinite alternate;
}
@keyframes glow-pulse {
  0% { opacity: 0.4; filter: blur(10px) brightness(0.8); }
  100% { opacity: 0.7; filter: blur(15px) brightness(1.2); }
}
.holo-lines { position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; pointer-events: none; z-index: 2; }
.holo-line { position: absolute; background: rgba(0, 221, 255, 0.5); filter: blur(1px); }
.holo-line:nth-child(1) { top: 0; left: 30px; width: 1.5px; height: 100%; animation: line-pulse 2s infinite alternate; animation-delay: -0.5s; }
.holo-line:nth-child(2) { top: 0; right: 30px; width: 1.5px; height: 100%; animation: line-pulse 2s infinite alternate; animation-delay: -1s; }
.holo-line:nth-child(3) { top: 15px; left: 0; width: 100%; height: 1.5px; animation: line-pulse 2s infinite alternate; animation-delay: -1.5s; }
.holo-line:nth-child(4) { bottom: 15px; left: 0; width: 100%; height: 1.5px; animation: line-pulse 2s infinite alternate; animation-delay: -2s; }
@keyframes line-pulse {
  0% { opacity: 0.3; background: rgba(0, 221, 255, 0.5); }
  100% { opacity: 0.7; background: rgba(255, 0, 222, 0.5); }
}
.scan-line {
  position: absolute; width: 100%; height: 3px;
  background: linear-gradient(90deg, rgba(0,0,0,0) 0%, rgba(0,221,255,0.5) 20%, rgba(255,255,255,0.8) 50%, rgba(255,0,222,0.5) 80%, rgba(0,0,0,0) 100%);
  top: 0; left: 0; filter: blur(1px); opacity: 0; z-index: 2; pointer-events: none; animation: scan 2s infinite;
}
@keyframes scan {
  0% { top: -5px; opacity: 0; }
  15%, 85% { opacity: 0.7; }
  100% { top: 85px; opacity: 0; }
}
.holo-particles { position: absolute; width: 100%; height: 100%; top: 0; left: 0; overflow: hidden; pointer-events: none; z-index: 1; }
.holo-particle { position: absolute; background: white; box-shadow: 0 0 10px rgba(0, 221, 255, 0.7); border-radius: 50%; filter: blur(1px); opacity: 0; animation: particle-float 3s infinite ease-out; }
.holo-particle:nth-child(1) { width: 3px; height: 3px; top: 70%; left: 30%; animation-delay: 0.2s; }
.holo-particle:nth-child(2) { width: 2px; height: 2px; top: 60%; left: 40%; animation-delay: 0.5s; }
.holo-particle:nth-child(3) { width: 4px; height: 4px; top: 80%; left: 60%; animation-delay: 0.8s; }
.holo-particle:nth-child(4) { width: 2px; height: 2px; top: 70%; left: 70%; animation-delay: 1.1s; }
.holo-particle:nth-child(5) { width: 3px; height: 3px; top: 75%; left: 50%; animation-delay: 1.4s; }
.holo-particle:nth-child(6) { width: 2px; height: 2px; top: 65%; left: 65%; animation-delay: 1.7s; }
@keyframes particle-float { 0% { transform: translateY(0) rotate(0deg); opacity: 0; } 10% { opacity: 0.8; } 100% { transform: translateY(-40px) rotate(360deg); opacity: 0; } }
.button-hexagons { position: absolute; width: 380px; height: 180px; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; z-index: 1; }
.hexagon { position: absolute; width: 30px; height: 17.32px; background: transparent; border: 1.5px solid rgba(0, 221, 255, 0.3); transform: rotate(30deg); opacity: 0; animation: hexagon-pulse 3s infinite; }
.hexagon::before, .hexagon::after { content: ""; position: absolute; width: 100%; height: 100%; top: 0; left: 0; border: 1.5px solid rgba(0, 221, 255, 0.3); }
.hexagon::before { transform: rotate(60deg); }
.hexagon::after { transform: rotate(-60deg); }
.hexagon:nth-child(1) { top: 20px; left: 20px; animation-delay: 0.1s; }
.hexagon:nth-child(2) { top: 20px; right: 20px; animation-delay: 0.3s; }
.hexagon:nth-child(3) { bottom: 20px; left: 20px; animation-delay: 0.5s; }
.hexagon:nth-child(4) { bottom: 20px; right: 20px; animation-delay: 0.7s; }
.hexagon:nth-child(5) { top: 50%; left: 0; transform: translate(0, -50%) rotate(30deg); animation-delay: 0.9s; }
.hexagon:nth-child(6) { top: 50%; right: 0; transform: translate(0, -50%) rotate(30deg); animation-delay: 1.1s; }
@keyframes hexagon-pulse { 0% { opacity: 0; transform: rotate(30deg) scale(0.8); } 20%, 80% { opacity: 0.5; transform: rotate(30deg) scale(1); } 100% { opacity: 0; transform: rotate(30deg) scale(1.2); } }
.digital-glyphs { position: absolute; width: 400px; height: 200px; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; z-index: 1; }
.digital-glyph { position: absolute; color: rgba(0, 221, 255, 0.5); font-size: 10px; font-family: "Orbitron", monospace; white-space: nowrap; opacity: 0; animation: glyph-fade 4s infinite; }
.digital-glyph:nth-child(1) { top: -30px; left: 60px; animation-delay: 0.2s; }
.digital-glyph:nth-child(2) { top: -20px; right: 40px; animation-delay: 0.6s; }
.digital-glyph:nth-child(3) { bottom: -30px; left: 80px; animation-delay: 1s; }
.digital-glyph:nth-child(4) { bottom: -20px; right: 60px; animation-delay: 1.4s; }
@keyframes glyph-fade { 0%, 100% { opacity: 0; transform: translateY(0); } 10%, 90% { opacity: 0.7; } 100% { opacity: 0; transform: translateY(-10px); } }
.corner-accents { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; }
.corner-accent { position: absolute; width: 15px; height: 15px; border: 2px solid rgba(0, 221, 255, 0.7); }
.corner-accent:nth-child(1) { top: 0; left: 0; border-right: none; border-bottom: none; }
.corner-accent:nth-child(2) { top: 0; right: 0; border-left: none; border-bottom: none; }
.corner-accent:nth-child(3) { bottom: 0; left: 0; border-right: none; border-top: none; }
.corner-accent:nth-child(4) { bottom: 0; right: 0; border-left: none; border-top: none; }
.button-glitch { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 3; opacity: 0; animation: button-glitch 8s infinite; }
@keyframes button-glitch { 0%, 94%, 96%, 100% { opacity: 0; } 94.5% { opacity: 0.8; transform: translate(5px, -2px) skew(-5deg, 2deg); background: rgba(255, 0, 222, 0.2); } 95% { opacity: 0.8; transform: translate(-5px, 2px) skew(5deg, -2deg); background: rgba(0, 221, 255, 0.2); } 95.5% { opacity: 0.8; transform: translate(2px, 0) skew(-2deg, 0); background: rgba(255, 255, 255, 0.2); } }
.sound-wave { position: absolute; bottom: -30px; left: 0; width: 100%; height: 20px; display: flex; justify-content: center; align-items: center; pointer-events: none; }
.wave-bar { width: 2px; height: 5px; background: rgba(0, 221, 255, 0.5); margin: 0 2px; border-radius: 1px; animation: wave-animation 1.5s infinite; }
@keyframes wave-animation { 0%, 100% { height: 5px; } 50% { height: 15px; } }
.wave-bar:nth-child(1) { animation-delay: 0.1s; } .wave-bar:nth-child(2) { animation-delay: 0.2s; } .wave-bar:nth-child(3) { animation-delay: 0.3s; } .wave-bar:nth-child(4) { animation-delay: 0.4s; } .wave-bar:nth-child(5) { animation-delay: 0.5s; } .wave-bar:nth-child(6) { animation-delay: 0.6s; } .wave-bar:nth-child(7) { animation-delay: 0.7s; } .wave-bar:nth-child(8) { animation-delay: 0.8s; } .wave-bar:nth-child(9) { animation-delay: 0.9s; } .wave-bar:nth-child(10) { animation-delay: 1s; } .wave-bar:nth-child(11) { animation-delay: 0.9s; } .wave-bar:nth-child(12) { animation-delay: 0.8s; } .wave-bar:nth-child(13) { animation-delay: 0.7s; } .wave-bar:nth-child(14) { animation-delay: 0.6s; } .wave-bar:nth-child(15) { animation-delay: 0.5s; }
.button-text { position: relative; z-index: 5; transform-style: preserve-3d; }

/* ========================================= */
/* 2. SCANNING CSS */
/* ========================================= */
.modelViewPort {
  perspective: 1000px; width: 20rem; aspect-ratio: 1; border-radius: 50%;
  display: flex; justify-content: center; align-items: center; background: transparent; overflow: hidden;
}
.eva { --EVA-ROTATION-DURATION: 4s; transform-style: preserve-3d; animation: rotateRight var(--EVA-ROTATION-DURATION) linear infinite alternate; }
.head { position: relative; width: 6rem; height: 4rem; border-radius: 48% 53% 45% 55% / 79% 79% 20% 22%; background: linear-gradient(to right, white 45%, gray); }
.eyeChamber { width: 4.5rem; height: 2.75rem; position: relative; left: 50%; top: 55%; border-radius: 45% 53% 45% 48% / 62% 59% 35% 34%; background-color: #0c203c; box-shadow: 0px 0px 2px 2px white, inset 0px 0px 0px 2px black; transform: translate(-50%, -50%); animation: moveRight var(--EVA-ROTATION-DURATION) linear infinite alternate; }
.eye { width: 1.2rem; height: 1.5rem; position: absolute; border-radius: 50%; }
.eye:first-child { left: 12px; top: 50%; background: repeating-linear-gradient(65deg, #9bdaeb 0px, #9bdaeb 1px, white 2px); box-shadow: inset 0px 0px 5px #04b8d5, 0px 0px 15px 1px #0bdaeb; transform: translate(0, -50%) rotate(-65deg); }
.eye:nth-child(2) { right: 12px; top: 50%; background: repeating-linear-gradient(-65deg, #9bdaeb 0px, #9bdaeb 1px, white 2px); box-shadow: inset 0px 0px 5px #04b8d5, 0px 0px 15px 1px #0bdaeb; transform: translate(0, -50%) rotate(65deg); }
.body { width: 6rem; height: 8rem; position: relative; margin-block-start: 0.25rem; border-radius: 47% 53% 45% 55% / 12% 9% 90% 88%; background: linear-gradient(to right, white 35%, gray); }
.hand { position: absolute; left: -1.5rem; top: 0.75rem; width: 2rem; height: 5.5rem; border-radius: 40%; background: linear-gradient(to left, white 15%, gray); box-shadow: 5px 0px 5px rgba(0, 0, 0, 0.25); transform: rotateY(55deg) rotateZ(10deg); }
.hand:first-child { animation: compensateRotation var(--EVA-ROTATION-DURATION) linear infinite alternate; }
.hand:nth-child(2) { left: 92%; background: linear-gradient(to right, white 15%, gray); transform: rotateY(55deg) rotateZ(-10deg); animation: compensateRotationRight var(--EVA-ROTATION-DURATION) linear infinite alternate; }
.scannerThing { width: 0; height: 0; position: absolute; left: 60%; top: 10%; border-top: 180px solid #9bdaeb; border-left: 250px solid transparent; border-right: 250px solid transparent; transform-origin: top left; mask: linear-gradient(to right, white, transparent 35%); animation: glow 2s cubic-bezier(0.86, 0, 0.07, 1) infinite; }
.scannerOrigin { position: absolute; width: 8px; aspect-ratio: 1; border-radius: 50%; left: 60%; top: 10%; background: #9bdaeb; box-shadow: inset 0px 0px 5px rgba(0, 0, 0, 0.5); animation: moveRight var(--EVA-ROTATION-DURATION) linear infinite; }
@keyframes rotateRight { from { transform: rotateY(0deg); } to { transform: rotateY(25deg); } }
@keyframes moveRight { from { transform: translate(-50%, -50%); } to { transform: translate(-40%, -50%); } }
@keyframes compensateRotation { from { transform: rotateY(55deg) rotateZ(10deg); } to { transform: rotatey(30deg) rotateZ(10deg); } }
@keyframes compensateRotationRight { from { transform: rotateY(55deg) rotateZ(-10deg); } to { transform: rotateY(70deg) rotateZ(-10deg); } }
@keyframes glow { from { opacity: 0; } 20% { opacity: 1; } 45% { transform: rotate(-25deg); } 75% { transform: rotate(5deg); } 100% { opacity: 0; } }

/* ========================================= */
/* 3. ANALISIS CSS */
/* ========================================= */
.trace-bg { stroke: #333; stroke-width: 1.8; fill: none; }
.trace-flow { stroke-width: 1.8; fill: none; stroke-dasharray: 40 400; stroke-dashoffset: 438; filter: drop-shadow(0 0 6px currentColor); animation: flow 3s cubic-bezier(0.5, 0, 0.9, 1) infinite; }
.yellow { stroke: #ffea00; color: #ffea00; }
.blue { stroke: #00ccff; color: #00ccff; }
.green { stroke: #00ff15; color: #00ff15; }
.purple { stroke: #9900ff; color: #9900ff; }
.red { stroke: #ff3300; color: #ff3300; }
@keyframes flow { to { stroke-dashoffset: 0; } }

/* ========================================= */
/* 4. FINALISASI CSS */
/* ========================================= */
.final-loader-wrapper {
  position: relative; display: flex; align-items: center; justify-content: center;
  height: 120px; width: auto; font-family: "Poppins", "Inter", sans-serif;
  font-size: 1em; font-weight: 600; user-select: none; color: #fff;
}
.final-loader {
  position: absolute; top: 0; left: 0; height: 100%; width: 100%; z-index: 1; background-color: transparent;
  mask: repeating-linear-gradient(90deg, transparent 0, transparent 6px, black 7px, black 8px);
}
.final-loader::after {
  content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
  background-image: radial-gradient(circle at 50% 50%, #00d4ff 0%, transparent 50%),
    radial-gradient(circle at 45% 45%, #7c3aed 0%, transparent 45%),
    radial-gradient(circle at 55% 55%, #00d4ff 0%, transparent 45%),
    radial-gradient(circle at 45% 55%, #7c3aed 0%, transparent 45%),
    radial-gradient(circle at 55% 45%, #00d4ff 0%, transparent 45%);
  mask: radial-gradient(circle at 50% 50%, transparent 0%, transparent 10%, black 25%);
  animation: transform-animation 2s infinite alternate, opacity-animation 4s infinite;
  animation-timing-function: cubic-bezier(0.6, 0.8, 0.5, 1);
}
@keyframes transform-animation { 0% { transform: translate(-55%); } 100% { transform: translate(55%); } }
@keyframes opacity-animation { 0%, 100% { opacity: 0; } 15% { opacity: 1; } 65% { opacity: 0; } }
.final-loader-letter { display: inline-block; opacity: 0; animation: loader-letter-anim 4s infinite linear; z-index: 2; }
.final-loader-letter:nth-child(1) { animation-delay: 0.1s; } .final-loader-letter:nth-child(2) { animation-delay: 0.205s; }
.final-loader-letter:nth-child(3) { animation-delay: 0.31s; } .final-loader-letter:nth-child(4) { animation-delay: 0.415s; }
.final-loader-letter:nth-child(5) { animation-delay: 0.521s; } .final-loader-letter:nth-child(6) { animation-delay: 0.626s; }
.final-loader-letter:nth-child(7) { animation-delay: 0.731s; } .final-loader-letter:nth-child(8) { animation-delay: 0.837s; }
.final-loader-letter:nth-child(9) { animation-delay: 0.942s; } .final-loader-letter:nth-child(10) { animation-delay: 1.047s; }
@keyframes loader-letter-anim {
  0% { opacity: 0; }
  5% { opacity: 1; text-shadow: 0 0 4px #00d4ff; transform: scale(1.1) translateY(-2px); }
  20% { opacity: 0.2; }
  100% { opacity: 0; }
}
</style>
@endsection
