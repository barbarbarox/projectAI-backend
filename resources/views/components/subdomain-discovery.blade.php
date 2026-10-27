@props(['subdomainData' => [], 'scan' => null])

@php
    $data = $subdomainData ?? [];
    $subdomains = $data['subdomains'] ?? [];
    $total = $data['total'] ?? count($subdomains);
    $liveCount = $data['live_count'] ?? count(array_filter($subdomains, fn($s) => !empty($s['is_alive'])));
    $takeoverRisks = $data['takeover_risks'] ?? array_filter($subdomains, fn($s) => !empty($s['takeover_risk']));
    $interesting = $data['interesting'] ?? array_filter($subdomains, fn($s) => !empty($s['is_interesting']) || !empty($s['menarik']));
    $sources = $data['sources_summary'] ?? [];
@endphp

@if(!empty($subdomains))
<div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6 shadow-xl relative overflow-hidden"
     x-data="{
        filter: 'all',
        search: '',
        items: @js(array_values($subdomains)),
        get filtered() {
            return this.items.filter(item => {
                const name = (item.subdomain || item.name || '').toLowerCase();
                const ip = (item.ip_address || '').toLowerCase();
                const cname = (item.cname || '').toLowerCase();
                const matchSearch = !this.search || name.includes(this.search.toLowerCase()) || ip.includes(this.search.toLowerCase()) || cname.includes(this.search.toLowerCase());

                if (!matchSearch) return false;
                if (this.filter === 'live') return item.is_alive;
                if (this.filter === 'takeover') return item.takeover_risk;
                if (this.filter === 'interesting') return item.is_interesting || item.menarik;
                return true;
            });
        }
     }">

    {{-- Subtle top ambient line --}}
    <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-purple-500 via-cyan-400 to-indigo-500"></div>

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4 mb-4">
        <div>
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
                <span>Asset Discovery & Subdomain Recon</span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-300 border border-purple-500/30 font-mono">ASM Engine</span>
            </h3>
            <p class="text-xs text-[#64748b] mt-1">
                Multi-source discovery (Certificate Transparency, HackerTarget & DNS) dengan resolusi DNS, live HTTP probing & verifikasi dangling CNAME.
            </p>
        </div>

        {{-- Stats Pill Row --}}
        <div class="flex items-center gap-2 flex-wrap">
            <span class="px-2.5 py-1 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-xs font-mono text-slate-300 flex items-center gap-1.5">
                <span class="text-[#00d4ff] font-bold">{{ $total }}</span> Total
            </span>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-xs font-mono text-emerald-400 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-bold">{{ $liveCount }}</span> Live
            </span>
            @if(count($takeoverRisks) > 0)
            <span class="px-2.5 py-1 rounded-lg bg-red-500/20 border border-red-500/40 text-xs font-mono text-red-400 flex items-center gap-1.5 shadow-[0_0_12px_rgba(239,68,68,0.3)]">
                <svg class="w-3.5 h-3.5 text-red-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="font-bold">{{ count($takeoverRisks) }}</span> Takeover Risk!
            </span>
            @endif
        </div>
    </div>

    {{-- TAKE OVER ALERT BANNER (If any detected) --}}
    @if(count($takeoverRisks) > 0)
    <div class="mb-5 p-4 rounded-xl bg-red-950/40 border border-red-500/40 text-red-200">
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-red-500/20 border border-red-500/30 flex items-center justify-center shrink-0 text-red-400 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-red-400 flex items-center gap-2">
                    <span>PERINGATAN KRITIS: Subdomain Takeover Terdeteksi!</span>
                    <span class="px-2 py-0.5 rounded text-[10px] bg-red-500/30 text-white font-mono uppercase">CVSS 8.5+</span>
                </h4>
                <p class="text-xs text-red-300/90 mt-1 leading-relaxed">
                    Ditemukan record CNAME yang menunjuk ke layanan pihak ketiga (cloud hosting) yang tidak aktif atau belum diklaim (dangling CNAME). Penyerang dapat mendaftarkan nama resource tersebut untuk membajak seluruh subdomain dan mencuri cookie/session pengguna.
                </p>
                <div class="mt-2 space-y-1">
                    @foreach($takeoverRisks as $tr)
                    <div class="text-xs font-mono bg-red-900/30 px-2.5 py-1.5 rounded border border-red-800/40 flex items-center justify-between flex-wrap gap-2">
                        <span class="text-white font-semibold">{{ $tr['subdomain'] ?? $tr['name'] }}</span>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-red-300 text-[11px]">CNAME: <span class="text-amber-300">{{ $tr['cname'] ?? '-' }}</span> ({{ $tr['takeover_service'] ?? 'Layanan Cloud' }})</span>
                            @if($scan)
                            <form method="POST" action="{{ route('insiden.store-from-takeover') }}" class="inline">
                                @csrf
                                <input type="hidden" name="scan_id" value="{{ $scan->id }}">
                                <input type="hidden" name="subdomain" value="{{ $tr['subdomain'] ?? $tr['name'] }}">
                                <input type="hidden" name="cname" value="{{ $tr['cname'] ?? '' }}">
                                <input type="hidden" name="takeover_service" value="{{ $tr['takeover_service'] ?? '' }}">
                                <button type="submit"
                                        class="px-2 py-0.5 rounded bg-red-600 hover:bg-red-500 text-white text-[10px] font-semibold transition-colors flex items-center gap-1 shadow-sm">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Eskalasi Insiden</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Filter & Search Bar --}}
    <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
        {{-- Filter Buttons --}}
        <div class="flex items-center gap-1.5 p-1 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a]">
            <button @click="filter = 'all'"
                    class="px-3 py-1 rounded-lg text-xs font-medium transition-all"
                    :class="filter === 'all' ? 'bg-[#1e293b] text-white shadow-sm font-semibold' : 'text-[#64748b] hover:text-[#94a3b8]'">
                Semua ({{ $total }})
            </button>
            <button @click="filter = 'live'"
                    class="px-3 py-1 rounded-lg text-xs font-medium transition-all flex items-center gap-1"
                    :class="filter === 'live' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-semibold' : 'text-[#64748b] hover:text-[#94a3b8]'">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Live ({{ $liveCount }})
            </button>
            @if(count($takeoverRisks) > 0)
            <button @click="filter = 'takeover'"
                    class="px-3 py-1 rounded-lg text-xs font-medium transition-all flex items-center gap-1"
                    :class="filter === 'takeover' ? 'bg-red-500/20 text-red-300 border border-red-500/30 font-semibold' : 'text-red-400/80 hover:text-red-300'">
                Takeover Risk ({{ count($takeoverRisks) }})
            </button>
            @endif
            @if(count($interesting) > 0)
            <button @click="filter = 'interesting'"
                    class="px-3 py-1 rounded-lg text-xs font-medium transition-all"
                    :class="filter === 'interesting' ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30 font-semibold' : 'text-[#64748b] hover:text-[#94a3b8]'">
                Menarik ({{ count($interesting) }})
            </button>
            @endif
        </div>

        {{-- Search Input --}}
        <div class="relative w-full sm:w-64">
            <input type="text"
                   x-model="search"
                   placeholder="Cari subdomain, IP, CNAME..."
                   class="w-full bg-[#0a0e1a] border border-[#1e2d4a] rounded-xl px-3 py-1.5 text-xs text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff] pl-8">
            <svg class="w-3.5 h-3.5 text-[#475569] absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
    </div>

    {{-- Subdomain List / Table --}}
    <div class="rounded-xl border border-[#1e2d4a] overflow-hidden bg-[#0a0e1a]/70">
        <div class="max-h-96 overflow-y-auto divide-y divide-[#1e2d4a]/60">
            <template x-for="(sub, idx) in filtered" :key="idx">
                <div class="p-3 transition-colors hover:bg-[#0f1629] flex items-center justify-between gap-3 flex-wrap"
                     :class="{
                        'bg-red-950/20 border-l-2 border-red-500': sub.takeover_risk,
                        'border-l-2 border-yellow-500/60': !sub.takeover_risk && (sub.is_interesting || sub.menarik)
                     }">
                    {{-- Left info --}}
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- Status Dot --}}
                        <div class="shrink-0">
                            <template x-if="sub.takeover_risk">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block animate-ping"></span>
                            </template>
                            <template x-if="!sub.takeover_risk && sub.is_alive">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                            </template>
                            <template x-if="!sub.takeover_risk && !sub.is_alive">
                                <span class="w-2 h-2 rounded-full bg-[#475569] inline-block"></span>
                            </template>
                        </div>

                        {{-- Subdomain name & details --}}
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a :href="'https://' + (sub.subdomain || sub.name)"
                                   target="_blank"
                                   rel="noopener"
                                   class="text-xs font-mono font-medium hover:underline flex items-center gap-1"
                                   :class="sub.takeover_risk ? 'text-red-400 font-bold' : (sub.is_alive ? 'text-white' : 'text-[#94a3b8]')">
                                    <span x-text="sub.subdomain || sub.name"></span>
                                    <svg class="w-3 h-3 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>

                                {{-- HTTP Status badge --}}
                                <template x-if="sub.status_http">
                                    <span class="text-[10px] font-mono px-1.5 py-0.2 rounded"
                                          :class="{
                                            'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20': sub.status_http >= 200 && sub.status_http < 300,
                                            'bg-blue-500/10 text-blue-400 border border-blue-500/20': sub.status_http >= 300 && sub.status_http < 400,
                                            'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20': sub.status_http >= 400 && sub.status_http < 500,
                                            'bg-red-500/10 text-red-400 border border-red-500/20': sub.status_http >= 500
                                          }"
                                          x-text="'HTTP ' + sub.status_http"></span>
                                </template>

                                {{-- Takeover badge --}}
                                <template x-if="sub.takeover_risk">
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-red-500/20 text-red-300 border border-red-500/40 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        TAKEOVER RISK (<span x-text="sub.takeover_service || 'Cloud'"></span>)
                                    </span>
                                </template>

                                {{-- Interesting badge --}}
                                <template x-if="sub.is_interesting || sub.menarik">
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 font-mono">
                                        PRIORITAS RECON
                                    </span>
                                </template>
                            </div>

                            {{-- Metadata row: IP, CNAME, Server, Title --}}
                            <div class="flex items-center gap-3 text-[11px] text-[#64748b] mt-0.5 flex-wrap">
                                <template x-if="sub.ip_address">
                                    <span class="font-mono text-slate-400" x-text="'IP: ' + sub.ip_address"></span>
                                </template>
                                <template x-if="sub.cname">
                                    <span class="font-mono text-cyan-400/90 flex items-center gap-1">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                        CNAME: <span x-text="sub.cname"></span>
                                    </span>
                                </template>
                                <template x-if="sub.server_header">
                                    <span class="text-slate-400" x-text="'Server: ' + sub.server_header"></span>
                                </template>
                                <template x-if="sub.page_title">
                                    <span class="text-slate-400 italic truncate max-w-xs" x-text="'“' + sub.page_title + '”'"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Right Action: Quick Scan Button --}}
                    <div class="shrink-0 flex items-center gap-2">
                        <a :href="'{{ route('analisis.url') }}?target=' + encodeURIComponent('https://' + (sub.subdomain || sub.name))"
                           class="px-2.5 py-1 rounded-lg bg-[#1e293b] hover:bg-[#334155] text-[11px] text-[#94a3b8] hover:text-white transition-colors flex items-center gap-1 border border-[#334155]/60">
                            <svg class="w-3 h-3 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <span>Pindai Target</span>
                        </a>
                    </div>
                </div>
            </template>

            {{-- Empty filtered state --}}
            <template x-if="filtered.length === 0">
                <div class="py-8 text-center text-xs text-[#64748b]">
                    Tidak ada subdomain yang cocok dengan kriteria filter atau pencarian.
                </div>
            </template>
        </div>
    </div>
</div>
@endif
