@extends('layouts.app')

@section('title', 'Threat Hunt by CVE — RedSim ASM')
@section('header', 'Threat Hunting by CVE')
@section('subheader', 'Deteksi proaktif paparan kerentanan CVE global ke seluruh aset dan subdomain Anda')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12"
     x-data="{
        query: @js($initialQuery),
        targetScanId: @js($initialScanId ?? ''),
        loading: false,
        results: @js($initialResults),
        error: null,

        async runHunt(customQuery = null) {
            if (customQuery) {
                this.query = customQuery;
            }
            if (!this.query || !this.query.trim()) {
                this.error = 'Masukkan CVE ID atau nama teknologi untuk memulai hunting.';
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                const res = await fetch('{{ route('threat-hunt.execute') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        query: this.query,
                        scan_id: this.targetScanId || null
                    })
                });

                const json = await res.json();
                if (json.success) {
                    this.results = json.data;
                } else {
                    this.error = json.message || 'Terjadi kesalahan saat menjalankan hunting.';
                }
            } catch (e) {
                this.error = 'Koneksi ke server gagal. Coba beberapa saat lagi.';
            } finally {
                this.loading = false;
            }
        }
     }">

    {{-- ===== HERO & SEARCH PANEL ===== --}}
    <div class="rounded-2xl bg-gradient-to-br from-[#080c18] via-[#0d1526] to-[#080c18] border border-[#1e2d4a] p-6 shadow-2xl relative overflow-hidden">
        {{-- Ambient decorative glow --}}
        <div class="absolute -top-24 -right-24 w-80 h-80 bg-red-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-cyan-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400 shadow-[0_0_15px_rgba(239,68,68,0.2)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>Threat Hunt Engine</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-red-500/20 text-red-400 border border-red-500/30 font-semibold uppercase">ASM Intelligence</span>
                    </h2>
                    <p class="text-xs text-[#94a3b8]">Cari kerentanan spesifik (CVE) atau nama komponen untuk memeriksa apakah ada aset web, port, atau subdomain yang rentan.</p>
                </div>
            </div>

            {{-- Hunt Form --}}
            <form @submit.prevent="runHunt()" class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-7 relative">
                    <input type="text"
                           x-model="query"
                           placeholder="Contoh: CVE-2024-4577, PHP CGI, OpenSSH, Next.js, Apache..."
                           class="w-full bg-[#0a0e1a] border border-[#1e2d4a] focus:border-red-500 rounded-xl px-4 py-3 text-sm text-white placeholder-[#475569] focus:outline-none transition-all pl-10 shadow-inner">
                    <svg class="w-4 h-4 text-[#64748b] absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <div class="md:col-span-3">
                    <select x-model="targetScanId"
                            class="w-full bg-[#0a0e1a] border border-[#1e2d4a] focus:border-cyan-400 rounded-xl px-3 py-3 text-xs text-slate-300 focus:outline-none transition-all font-mono">
                        <option value="">🌐 Semua Aset & Subdomain (Global)</option>
                        @foreach($userScans as $s)
                        <option value="{{ $s->id }}">
                            {{ Str::limit($s->target ?? $s->nama_file, 30) }} (Skor: {{ $s->skor_keamanan }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <button type="submit"
                            :disabled="loading"
                            class="w-full h-full min-h-[46px] rounded-xl font-semibold text-xs text-white transition-all duration-200 flex items-center justify-center gap-2 shadow-[0_0_20px_rgba(239,68,68,0.3)] hover:shadow-[0_0_25px_rgba(239,68,68,0.5)] active:scale-[0.98]"
                            :class="loading ? 'bg-red-800 cursor-not-allowed opacity-75' : 'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500'">
                        <template x-if="!loading">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Mulai Hunt</span>
                            </span>
                        </template>
                        <template x-if="loading">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <span>Scanning...</span>
                            </span>
                        </template>
                    </button>
                </div>
            </form>

            {{-- Error banner --}}
            <template x-if="error">
                <div class="mt-3 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-xs text-red-400 flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="error"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- ===== TRENDING THREAT PRESETS ===== --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                <span>Trending CVE & Exploits (1-Click Hunt)</span>
            </h3>
            <span class="text-[11px] text-[#64748b]">Klik salah satu kartu di bawah untuk auto-hunting</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($trendingCves as $tc)
            <div @click="runHunt('{{ $tc['cve_id'] }}')"
                 class="group p-4 rounded-xl bg-[#0f1629] border border-[#1e2d4a] hover:border-red-500/50 hover:bg-[#131d36] transition-all cursor-pointer relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-mono font-bold text-red-400 group-hover:text-red-300 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ $tc['cve_id'] }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $tc['severity'] === 'CRITICAL' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-orange-500/20 text-orange-400 border border-orange-500/30' }}">
                        CVSS {{ $tc['cvss'] }}
                    </span>
                </div>
                <h4 class="text-xs font-semibold text-white group-hover:text-cyan-400 transition-colors line-clamp-1 mb-1">{{ $tc['title'] }}</h4>
                <p class="text-[11px] text-[#64748b] line-clamp-2 leading-relaxed mb-3">{{ $tc['description'] }}</p>
                <div class="flex items-center justify-between text-[10px] text-[#475569] font-mono pt-2 border-t border-[#1e2d4a]">
                    <span>Target: {{ $tc['component'] }}</span>
                    <span class="text-red-400 group-hover:translate-x-1 transition-transform">Hunt →</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ===== HUNT RESULTS SECTION ===== --}}
    <template x-if="results">
        <div class="space-y-5 animate-fadeIn">

            {{-- 1. Threat Intel Card --}}
            <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6 shadow-xl">
                <div class="flex items-start justify-between flex-wrap gap-4 mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-mono font-bold text-red-400" x-text="results.cve_info.cve_id"></span>
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded"
                                  :class="results.cve_info.severity === 'CRITICAL' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-orange-500/20 text-orange-400 border border-orange-500/30'"
                                  x-text="results.cve_info.severity + ' (CVSS ' + results.cve_info.cvss + ')'"></span>
                        </div>
                        <h3 class="text-base font-bold text-white" x-text="results.cve_info.title"></h3>
                        <p class="text-xs text-[#94a3b8] mt-1 max-w-3xl leading-relaxed" x-text="results.cve_info.description"></p>
                    </div>

                    {{-- Metrics Pill Row --}}
                    <div class="flex items-center gap-3">
                        <div class="px-4 py-2.5 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-center">
                            <div class="text-xl font-bold font-mono text-white" x-text="results.total_scanned"></div>
                            <div class="text-[10px] uppercase tracking-wider text-[#64748b]">Aset Dicek</div>
                        </div>
                        <div class="px-4 py-2.5 rounded-xl bg-red-950/40 border border-red-500/30 text-center">
                            <div class="text-xl font-bold font-mono text-red-400" x-text="results.affected_count"></div>
                            <div class="text-[10px] uppercase tracking-wider text-red-300">Terindikasi Rentan</div>
                        </div>
                        <div class="px-4 py-2.5 rounded-xl bg-emerald-950/30 border border-emerald-500/30 text-center">
                            <div class="text-xl font-bold font-mono text-emerald-400" x-text="results.clean_count"></div>
                            <div class="text-[10px] uppercase tracking-wider text-emerald-300">Aman</div>
                        </div>
                    </div>
                </div>

                <div class="text-[11px] text-[#475569] font-mono flex items-center justify-between border-t border-[#1e2d4a] pt-3">
                    <span>Komponen Target: <span class="text-slate-300" x-text="results.cve_info.component"></span></span>
                    <span>Waktu Eksekusi: <span class="text-cyan-400" x-text="results.hunting_time_ms + ' ms'"></span></span>
                </div>
            </div>

            {{-- 2. Affected Targets List --}}
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Aset Terindikasi Rentan Terhadap Ancaman Ini (<span x-text="results.affected_count"></span>)</span>
                </h3>

                <template x-if="results.affected_count > 0">
                    <div class="space-y-3">
                        <template x-for="(asset, idx) in results.affected_assets" :key="idx">
                            <div class="p-5 rounded-2xl bg-[#0f1629] border border-red-500/30 shadow-lg relative overflow-hidden transition-all hover:border-red-500/60">
                                <div class="absolute top-0 left-0 bottom-0 w-1 bg-red-500"></div>

                                <div class="flex items-start justify-between flex-wrap gap-4">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2.5 flex-wrap mb-1">
                                            <h4 class="text-sm font-bold text-white font-mono" x-text="asset.target"></h4>
                                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded"
                                                  :class="{
                                                    'bg-red-500/20 text-red-400 border border-red-500/40': asset.confidence === 'Tinggi',
                                                    'bg-yellow-500/20 text-yellow-400 border border-yellow-500/40': asset.confidence === 'Sedang',
                                                    'bg-slate-700/50 text-slate-300 border border-slate-600': asset.confidence === 'Rendah'
                                                  }"
                                                  x-text="'Confidence ' + asset.confidence + ' (' + asset.confidence_score + '%)'"></span>
                                            <span class="text-[11px] text-[#64748b]" x-text="'Dipindai: ' + asset.scan_date"></span>
                                        </div>

                                        {{-- Evidence indicators --}}
                                        <div class="mt-3 space-y-1.5">
                                            <div class="text-xs font-semibold text-slate-300">Indikator Bukti yang Cocok:</div>
                                            <template x-for="(ind, iIdx) in asset.indicators" :key="iIdx">
                                                <div class="text-xs text-red-300/90 flex items-center gap-2 bg-red-950/20 px-3 py-1.5 rounded-lg border border-red-900/30">
                                                    <svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span x-text="ind"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex items-center gap-2 shrink-0">
                                        <a :href="'/laporan/' + asset.scan_id"
                                           class="px-3 py-2 rounded-xl bg-[#1e293b] hover:bg-[#334155] text-xs font-semibold text-slate-200 transition-colors flex items-center gap-1.5 border border-[#334155]">
                                            <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Lihat Laporan</span>
                                        </a>
                                        <a :href="'/analisis/url?target=' + encodeURIComponent(asset.target)"
                                           class="px-3 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-xs font-semibold text-white transition-colors flex items-center gap-1.5 shadow-[0_0_12px_rgba(220,38,38,0.3)]">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            <span>Pindai Ulang</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="results.affected_count === 0">
                    <div class="p-8 rounded-2xl bg-[#0f1629] border border-emerald-500/20 text-center">
                        <div class="w-12 h-12 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-white mb-1">Semua Aset Bersih!</h4>
                        <p class="text-xs text-[#64748b]">Tidak ada aset atau subdomain terdaftar yang cocok dengan indikator kerentanan <span class="text-cyan-400 font-mono" x-text="results.cve_info.cve_id"></span>.</p>
                    </div>
                </template>
            </div>

            {{-- 3. Clean Assets (Accordion/Summary) --}}
            <template x-if="results.clean_count > 0">
                <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 text-xs text-[#64748b]"
                     x-data="{ open: false }">
                    <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="font-semibold text-slate-300"><span x-text="results.clean_count"></span> Aset Lainnya Dinyatakan Aman</span>
                        </span>
                        <span class="text-[10px] text-cyan-400" x-text="open ? 'Tutup ▲' : 'Lihat Daftar ▼'"></span>
                    </div>

                    <div x-show="open" class="mt-3 pt-3 border-t border-[#1e2d4a] space-y-1" x-cloak>
                        <template x-for="(ca, cIdx) in results.clean_assets" :key="cIdx">
                            <div class="flex items-center justify-between py-1 px-2 rounded hover:bg-[#0f1629]">
                                <span class="font-mono text-slate-400" x-text="ca.target"></span>
                                <span class="text-[10px] text-emerald-400/90 font-mono">CLEAN</span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </template>

</div>
@endsection
