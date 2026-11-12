@extends('layouts.app')
@section('title', 'Manajemen Insiden & Remediasi — RedSim SOC')
@section('header', 'Insiden Keamanan & Remediasi')
@section('subheader', 'Pelacakan siklus hidup kerentanan, investigasi insiden, dan verifikasi resolusi keamanan aset')
@section('page_feature', 'insiden')

@section('content')
<div class="space-y-6 pb-12 relative">

    {{-- SUB-NAVIGATION TABS (RIWAYAT | INSIDEN | RED TEAM) --}}
    <div class="flex items-center justify-between gap-4 flex-wrap pb-2 border-b border-[#242424]">
        <div class="flex items-center gap-2">
            <a href="{{ route('laporan.index') }}"
               class="px-4 py-1.5 rounded-full text-xs font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                Riwayat
            </a>
            <a href="{{ route('insiden.index') }}"
               class="px-4 py-1.5 rounded-full text-xs font-semibold text-white shadow-lg transition-all"
               style="background:#ef4444;box-shadow:0 0 12px rgba(239,68,68,0.4);">
                Insiden
            </a>
            <a href="{{ route('red-team-lab') }}"
               class="px-4 py-1.5 rounded-full text-xs font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                Red Team Lab
            </a>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.dispatchEvent(new CustomEvent('open-help', { detail: { feature: 'insiden' } }))"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
                    style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Panduan Fitur</span>
            </button>
        </div>
    </div>

    {{-- Alert Flash Message --}}
    @if(session('pesan'))
    <div class="p-4 rounded-xl border flex items-center justify-between gap-3 text-sm transition-all
        {{ session('pesan')['tipe'] === 'sukses' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-[#ef4444]/10 border-[#ef4444]/30 text-[#ef4444]' }}">
        <div class="flex items-center gap-2.5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('pesan')['isi'] }}</span>
        </div>
    </div>
    @endif

    {{-- HERO / COMMAND CENTER BANNER --}}
    <div class="relative overflow-hidden rounded-2xl mb-6" style="background: radial-gradient(circle at 85% 30%, rgba(239,68,68,0.2) 0%, transparent 50%), linear-gradient(135deg, #180909 0%, #200b0b 50%, #120606 100%); border: 1px solid #3a1f1f; border-top: 3px solid #ef4444;">
        {{-- Decorative Radar Circles & Background Grid --}}
        <div class="absolute inset-0 opacity-10 pointer-events-none"
             style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 32px 32px;"></div>
        <div style="position:absolute;top:-40px;right:-40px;width:240px;height:240px;border:1px solid rgba(239,68,68,0.2);border-radius:50%;pointer-events:none;"></div>
        <div style="position:absolute;top:0px;right:0px;width:160px;height:160px;border:1px solid rgba(239,68,68,0.15);border-radius:50%;pointer-events:none;"></div>

        <div class="relative z-10 p-8 flex items-center justify-between gap-6 flex-wrap">
            <div class="flex-1 min-w-[280px]">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full mb-4" style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.35);">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                    <span style="font-family:'IBM Plex Mono',monospace;font-size:10px;color:#ef4444;letter-spacing:0.15em;">PUSAT KOMANDO INSIDEN</span>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2 leading-tight">
                    Ada <span style="color:#ef4444;">{{ $stats['open'] + $stats['investigating'] }} insiden aktif</span> yang membutuhkan perhatian Anda
                </h1>
                <p class="text-sm mb-6" style="color:#888;">{{ $stats['kritis'] }} di antaranya berstatus kritis. Tangani dari yang paling berisiko agar permukaan serangan cepat menyempit.</p>
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="{{ route('insiden.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:scale-105" style="background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 4px 15px rgba(239,68,68,0.35);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Buat Insiden Baru
                    </a>
                    @if($stats['kritis'] > 0)
                    <a href="{{ route('insiden.index', ['keparahan'=>'kritis','status'=>'open']) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium transition-all hover:bg-red-500/20" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;">
                        Lihat yang kritis &rarr;
                    </a>
                    @endif
                </div>
            </div>

            {{-- Tachometer / Speedometer Gauge --}}
            <div class="shrink-0 text-center hidden md:block">
                @php
                    $riskScoreVal = min(99, intval(($stats['kritis'] / max(1,$stats['total'])) * 100 * 1.3 + 50));
                    // Semi-circle arc: length is pi * r = 3.14159 * 50 = 157.08
                    $arcLen = 157.08;
                    $dashoffset = $arcLen - ($riskScoreVal / 100) * $arcLen;
                @endphp
                <div class="relative w-36 h-28 mx-auto flex flex-col items-center justify-end">
                    <svg class="w-36 h-24 overflow-visible" viewBox="0 0 120 70">
                        {{-- Background Track Arc --}}
                        <path d="M 10 65 A 50 50 0 0 1 110 65"
                              fill="none" stroke="#2a1a1a" stroke-width="8" stroke-linecap="round"/>
                        {{-- Foreground Glowing Risk Arc --}}
                        <path d="M 10 65 A 50 50 0 0 1 110 65"
                              fill="none" stroke="#ef4444" stroke-width="8" stroke-linecap="round"
                              stroke-dasharray="{{ $arcLen }}"
                              stroke-dashoffset="{{ $dashoffset }}"
                              style="filter: drop-shadow(0 0 8px rgba(239,68,68,0.85)); transition: stroke-dashoffset 0.8s ease;"/>
                        {{-- Decorative indicator tick --}}
                        <circle cx="60" cy="15" r="2" fill="#ef4444" opacity="0.6"/>
                    </svg>
                    <div class="mt-[-28px] text-center z-10">
                        <div class="text-3xl font-black text-white tracking-tight" style="line-height:1;">{{ $riskScoreVal }}</div>
                        <div class="text-[9px] font-bold uppercase tracking-wider mt-1" style="font-family:'IBM Plex Mono',monospace;letter-spacing:0.12em;">
                            <span style="color:#aaa;">SKOR</span> <span style="color:#ef4444;">RISIKO</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STATS CARDS ROW WITH REALISTIC SPARKLINE WAVES --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5">
        {{-- Total --}}
        <div class="bg-[#161616] border border-[#242424] rounded-xl p-4 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs text-[#64748b] font-medium uppercase tracking-wider">Total Insiden</span>
                <div class="w-6 h-6 rounded-md bg-[#222] flex items-center justify-center text-[#888]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-white">{{ $stats['total'] }}</span>
                <span class="text-xs text-[#555]">All-time</span>
            </div>
            {{-- Wavy Sparkline --}}
            <div class="mt-2 h-7 relative overflow-hidden">
                <svg viewBox="0 0 100 24" preserveAspectRatio="none" class="w-full h-full">
                    <path d="M0,20 Q20,16 40,18 T70,12 T100,10 L100,24 L0,24 Z" fill="rgba(255,255,255,0.06)"/>
                    <path d="M0,20 Q20,16 40,18 T70,12 T100,10" fill="none" stroke="#888" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        {{-- Open --}}
        <div class="bg-[#1f1a0a] border border-[#4b3a0a] rounded-xl p-4 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs text-amber-400/90 font-medium uppercase tracking-wider">Open (Baru)</span>
                <div class="w-6 h-6 rounded-md bg-amber-500/15 flex items-center justify-center text-amber-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-amber-400">{{ $stats['open'] }}</span>
                <span class="text-[11px] text-amber-400/70">Perlu Tindakan</span>
            </div>
            {{-- Wavy Sparkline --}}
            <div class="mt-2 h-7 relative overflow-hidden">
                <svg viewBox="0 0 100 24" preserveAspectRatio="none" class="w-full h-full">
                    <path d="M0,20 Q25,12 50,18 T100,14 L100,24 L0,24 Z" fill="rgba(251,191,36,0.12)"/>
                    <path d="M0,20 Q25,12 50,18 T100,14" fill="none" stroke="#fbbf24" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        {{-- In Progress --}}
        <div class="rounded-xl p-4 flex flex-col justify-between relative overflow-hidden" style="background:#0d1218;border:1px solid #1a2424;">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium uppercase tracking-wider" style="color:rgba(167,139,250,0.9);">Dalam Penanganan</span>
                <div class="w-6 h-6 rounded-md flex items-center justify-center" style="background:rgba(167,139,250,0.15);color:#a78bfa;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono" style="color:#a78bfa;">{{ $stats['investigating'] }}</span>
                <span class="text-[11px]" style="color:rgba(167,139,250,0.7);">Sedang Dikerjakan</span>
            </div>
            {{-- Wavy Sparkline --}}
            <div class="mt-2 h-7 relative overflow-hidden">
                <svg viewBox="0 0 100 24" preserveAspectRatio="none" class="w-full h-full">
                    <path d="M0,22 Q30,14 60,19 T100,12 L100,24 L0,24 Z" fill="rgba(167,139,250,0.12)"/>
                    <path d="M0,22 Q30,14 60,19 T100,12" fill="none" stroke="#a78bfa" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        {{-- Resolved --}}
        <div class="bg-[#0a1a10] border border-[#1a3a20] rounded-xl p-4 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs text-emerald-400/90 font-medium uppercase tracking-wider">Terselesaikan</span>
                <div class="w-6 h-6 rounded-md bg-emerald-500/15 flex items-center justify-center text-emerald-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-emerald-400">{{ $stats['resolved'] }}</span>
                <span class="text-[11px] text-emerald-400/70">Telah Diverifikasi</span>
            </div>
            {{-- Wavy Sparkline --}}
            <div class="mt-2 h-7 relative overflow-hidden">
                <svg viewBox="0 0 100 24" preserveAspectRatio="none" class="w-full h-full">
                    <path d="M0,22 Q25,18 55,14 T100,8 L100,24 L0,24 Z" fill="rgba(52,211,153,0.12)"/>
                    <path d="M0,22 Q25,18 55,14 T100,8" fill="none" stroke="#34d399" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        {{-- Kritis Aktif --}}
        <div class="col-span-2 md:col-span-1 rounded-xl bg-red-500/10 border border-red-500/30 p-4 flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs text-red-400 font-bold uppercase tracking-wider">Kritis Aktif</span>
                <div class="w-6 h-6 rounded-md bg-red-500/20 flex items-center justify-center text-red-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold font-mono text-red-400">{{ $stats['kritis'] }}</span>
                <span class="text-[11px] text-red-400/80">Prioritas Tinggi</span>
            </div>
            {{-- Wavy Sparkline --}}
            <div class="mt-2 h-7 relative overflow-hidden">
                <svg viewBox="0 0 100 24" preserveAspectRatio="none" class="w-full h-full">
                    <path d="M0,18 Q20,12 40,20 T80,10 T100,16 L100,24 L0,24 Z" fill="rgba(239,68,68,0.18)"/>
                    <path d="M0,18 Q20,12 40,20 T80,10 T100,16" fill="none" stroke="#ef4444" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW: Tren Insiden + Distribusi Keparahan --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Tren Insiden (line chart) --}}
        <div class="lg:col-span-2 rounded-xl p-5" style="background:#161616;border:1px solid #242424;">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div style="font-family:'IBM Plex Mono',monospace;font-size:10px;color:#555;letter-spacing:0.12em;text-transform:uppercase;">Tren Insiden</div>
                    <div class="text-sm font-semibold text-white mt-0.5">7 hari terakhir</div>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 rounded" style="background:#ef4444;display:inline-block;"></span><span style="color:#888;">Dibuat</span></span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 rounded" style="background:#4ade80;display:inline-block;"></span><span style="color:#888;">Selesai</span></span>
                </div>
            </div>
            {{-- SVG Line Chart --}}
            @php
                $dibuat  = [2, 3, 5, 4, 6, 4, 3];
                $selesai = [1, 2, 3, 3, 4, 3, 2];
                $hari    = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
                $maxVal  = max(max($dibuat), max($selesai), 1);
                $svgW = 500; $svgH = 120; $padL = 20; $padR = 20; $padT = 10; $padB = 24;
                $chartW = $svgW - $padL - $padR;
                $chartH = $svgH - $padT - $padB;
                $n = count($dibuat);
                $xStep = $chartW / ($n - 1);
                // Build points
                $ptsDibuat  = [];
                $ptsSelesai = [];
                for ($i = 0; $i < $n; $i++) {
                    $x = $padL + $i * $xStep;
                    $ptsDibuat[]  = [$x, $padT + $chartH - ($dibuat[$i]  / $maxVal) * $chartH];
                    $ptsSelesai[] = [$x, $padT + $chartH - ($selesai[$i] / $maxVal) * $chartH];
                }
                $polyDibuat  = implode(' ', array_map(fn($p) => "{$p[0]},{$p[1]}", $ptsDibuat));
                $polySelesai = implode(' ', array_map(fn($p) => "{$p[0]},{$p[1]}", $ptsSelesai));
                // Area paths
                $baseY       = $padT + $chartH;
                $lastDX      = $ptsDibuat[$n-1][0];
                $firstDX     = $ptsDibuat[0][0];
                $lastSX      = $ptsSelesai[$n-1][0];
                $firstSX     = $ptsSelesai[0][0];
                $areaDibuat  = "M {$ptsDibuat[0][0]},{$ptsDibuat[0][1]} " . implode(' ', array_map(fn($p) => "L {$p[0]},{$p[1]}", array_slice($ptsDibuat,1))) . " L {$lastDX},{$baseY} L {$firstDX},{$baseY} Z";
                $areaSelesai = "M {$ptsSelesai[0][0]},{$ptsSelesai[0][1]} " . implode(' ', array_map(fn($p) => "L {$p[0]},{$p[1]}", array_slice($ptsSelesai,1))) . " L {$lastSX},{$baseY} L {$firstSX},{$baseY} Z";
            @endphp
            <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" style="width:100%;height:140px;" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="gradDibuat" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#ef4444" stop-opacity="0.3"/>
                        <stop offset="100%" stop-color="#ef4444" stop-opacity="0.02"/>
                    </linearGradient>
                    <linearGradient id="gradSelesai" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#4ade80" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#4ade80" stop-opacity="0.02"/>
                    </linearGradient>
                </defs>
                {{-- Grid lines --}}
                @for($g=0;$g<=3;$g++)
                    @php $gy = $padT + ($g/3)*$chartH; @endphp
                    <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $svgW-$padR }}" y2="{{ $gy }}" stroke="#2a2a2a" stroke-width="0.5"/>
                @endfor
                {{-- Area fills --}}
                <path d="{{ $areaDibuat }}"  fill="url(#gradDibuat)"/>
                <path d="{{ $areaSelesai }}" fill="url(#gradSelesai)"/>
                {{-- Lines --}}
                <polyline points="{{ $polyDibuat }}"  fill="none" stroke="#ef4444" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                <polyline points="{{ $polySelesai }}" fill="none" stroke="#4ade80" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                {{-- Dots --}}
                @foreach($ptsDibuat as $p)
                    <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="3.5" fill="#161616" stroke="#ef4444" stroke-width="2"/>
                @endforeach
                @foreach($ptsSelesai as $p)
                    <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="3.5" fill="#161616" stroke="#4ade80" stroke-width="2"/>
                @endforeach
                {{-- X labels --}}
                @foreach($hari as $idx => $h)
                    @php $lx = $padL + $idx * $xStep; @endphp
                    <text x="{{ $lx }}" y="{{ $svgH - 4 }}" text-anchor="middle" font-size="9" fill="#555" font-family="IBM Plex Mono,monospace">{{ $h }}</text>
                @endforeach
            </svg>
        </div>

        {{-- Distribusi Keparahan (donut chart) --}}
        <div class="rounded-xl p-5" style="background:#161616;border:1px solid #242424;">
            <div style="font-family:'IBM Plex Mono',monospace;font-size:10px;color:#555;letter-spacing:0.12em;text-transform:uppercase;margin-bottom:4px;">Distribusi Keparahan</div>
            @php
                $distKritis = $stats['kritis'] ?? 0;
                $distTinggi = max(0, ($stats['open'] ?? 0) - $distKritis);
                $distSedang = max(0, ($stats['investigating'] ?? 0));
                $distRendah = max(0, ($stats['resolved'] ?? 0));
                $distTotal  = max(1, $distKritis + $distTinggi + $distSedang + $distRendah);
                // Donut slices (circumference of r=40: 251.3)
                $circ = 251.3;
                $offsets = [];
                $pcts = [$distKritis/$distTotal, $distTinggi/$distTotal, $distSedang/$distTotal, $distRendah/$distTotal];
                $colors = ['#ef4444','#f97316','#fbbf24','#8b5cf6'];
                $labels = ['Kritis','Tinggi','Sedang','Rendah'];
                $counts = [$distKritis,$distTinggi,$distSedang,$distRendah];
                $dash = 0;
                foreach($pcts as $pct) { $offsets[] = $dash; $dash += $pct * $circ; }
            @endphp
            <div class="flex items-center gap-4 mt-3">
                {{-- Donut --}}
                <div class="relative shrink-0" style="width:88px;height:88px;">
                    <svg viewBox="0 0 100 100" width="88" height="88" style="transform:rotate(-90deg)">
                        @foreach($pcts as $i => $pct)
                        <circle cx="50" cy="50" r="40"
                            fill="none"
                            stroke="{{ $colors[$i] }}"
                            stroke-width="18"
                            stroke-dasharray="{{ $pct * $circ }} {{ $circ }}"
                            stroke-dashoffset="-{{ $offsets[$i] }}"
                            opacity="0.85"/>
                        @endforeach
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <div class="text-lg font-black text-white">{{ $stats['total'] }}</div>
                        <div style="font-size:9px;color:#666;letter-spacing:0.08em;font-family:'IBM Plex Mono',monospace;">TOTAL</div>
                    </div>
                </div>
                {{-- Legend --}}
                <div class="flex flex-col gap-2 flex-1">
                    @foreach($labels as $i => $lbl)
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background:{{ $colors[$i] }};"></span>
                            <span style="color:#888;">{{ $lbl }}</span>
                        </span>
                        <span class="font-bold font-mono" style="color:{{ $colors[$i] }};">{{ $counts[$i] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER & SEARCH TOOLBAR --}}
    <div class="rounded-xl bg-[#161616] border border-[#242424] p-4 flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('insiden.index') }}" class="w-full flex-1 flex flex-wrap items-center gap-3">
            {{-- Search Bar --}}
            <div class="relative flex-1 min-w-[220px]">
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Cari nomor insiden, judul, target, PIC..."
                       class="w-full bg-[#111111] border border-[#2a2a2a] rounded-xl px-3.5 py-2 text-xs text-white placeholder-[#475569] focus:outline-none focus:border-[#ef4444] pl-9">
                <svg class="w-4 h-4 text-[#475569] absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            {{-- Status Filter --}}
            <select name="status"
                    onchange="this.form.submit()"
                    class="bg-[#111111] border border-[#2a2a2a] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#ef4444]">
                <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                <option value="investigating" {{ request('status') === 'investigating' ? 'selected' : '' }}>Investigating</option>
                <option value="patching" {{ request('status') === 'patching' ? 'selected' : '' }}>Patching</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>

            {{-- Keparahan Filter --}}
            <select name="keparahan"
                    onchange="this.form.submit()"
                    class="bg-[#111111] border border-[#2a2a2a] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-[#ef4444]">
                <option value="all" {{ request('keparahan') === 'all' || !request('keparahan') ? 'selected' : '' }}>Semua Keparahan</option>
                <option value="kritis" {{ request('keparahan') === 'kritis' ? 'selected' : '' }}>Kritis</option>
                <option value="tinggi" {{ request('keparahan') === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                <option value="sedang" {{ request('keparahan') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                <option value="rendah" {{ request('keparahan') === 'rendah' ? 'selected' : '' }}>Rendah</option>
            </select>

            <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#1f1414] hover:bg-[#2a1a1a] border border-[#3a1f1f] text-xs text-[#ef4444] font-medium transition-colors">
                Filter
            </button>
            @if(request()->anyFilled(['q', 'status', 'keparahan']))
            <a href="{{ route('insiden.index') }}" class="text-xs text-[#64748b] hover:text-[#94a3b8] transition-colors">
                Reset
            </a>
            @endif
        </form>

        {{-- Create Action Button --}}
        <div class="shrink-0">
            <a href="{{ route('insiden.create') }}"
               class="px-4 py-2 rounded-xl bg-gradient-to-r from-red-600 to-red-800 hover:from-red-500 hover:to-red-700 text-white text-xs font-semibold shadow-lg shadow-red-950/40 flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Insiden Baru</span>
            </a>
        </div>
    </div>

    {{-- INCIDENT LIST TABLE --}}
    <div class="rounded-xl bg-[#161616] border border-[#242424] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#242424] bg-[#111111] text-[11px] text-[#64748b] uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Insiden</th>
                        <th class="py-3 px-4">Judul Insiden &amp; Target</th>
                        <th class="py-3 px-4">Keparahan</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">PIC / Assignee</th>
                        <th class="py-3 px-4">Dibuat</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#242424]/50 text-xs">
                    @forelse($insidens as $inc)
                    @php
                        // Helper styling keparahan
                        $sevBadge = match($inc->tingkat_keparahan) {
                            'kritis' => ['bg' => 'bg-red-500/15',    'border' => 'border-red-500/30',    'text' => 'text-red-400',    'label' => 'KRITIS'],
                            'tinggi' => ['bg' => 'bg-orange-500/15', 'border' => 'border-orange-500/30', 'text' => 'text-orange-400', 'label' => 'TINGGI'],
                            'sedang' => ['bg' => 'bg-yellow-500/15', 'border' => 'border-yellow-500/30', 'text' => 'text-yellow-400', 'label' => 'SEDANG'],
                            default  => ['bg' => 'bg-indigo-500/15',  'border' => 'border-indigo-500/30',  'text' => 'text-indigo-400',  'label' => 'RENDAH'],
                        };

                        // Helper styling status
                        $statusBadge = match($inc->status) {
                            'open'          => ['bg' => 'bg-amber-500/15',   'border' => 'border-amber-500/30',   'text' => 'text-amber-400',   'label' => 'Open',          'dot' => 'bg-amber-400 animate-pulse'],
                            'investigating' => ['bg' => 'bg-purple-500/15',  'border' => 'border-purple-500/30',  'text' => 'text-purple-400',  'label' => 'Investigating', 'dot' => 'bg-purple-400'],
                            'patching'      => ['bg' => 'bg-violet-500/15',  'border' => 'border-violet-500/30',  'text' => 'text-violet-400',  'label' => 'Patching',      'dot' => 'bg-violet-400'],
                            'resolved'      => ['bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/30', 'text' => 'text-emerald-400', 'label' => 'Resolved',      'dot' => 'bg-emerald-400'],
                            'closed'        => ['bg' => 'bg-zinc-700/40',    'border' => 'border-zinc-600/40',    'text' => 'text-zinc-400',    'label' => 'Closed',        'dot' => 'bg-zinc-500'],
                            default         => ['bg' => 'bg-zinc-700/40',    'border' => 'border-zinc-600/40',    'text' => 'text-zinc-400',    'label' => $inc->status,    'dot' => 'bg-zinc-500'],
                        };
                    @endphp
                    <tr class="hover:bg-[#1b1212]/50 transition-colors">
                        {{-- No. Insiden --}}
                        <td class="py-3 px-4 font-mono font-semibold text-[#ef4444] whitespace-nowrap">
                            <a href="{{ route('insiden.show', $inc->id) }}" class="hover:underline flex items-center gap-1.5">
                                <span>{{ $inc->nomor_insiden }}</span>
                                <svg class="w-3 h-3 text-[#475569]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </td>

                        {{-- Judul & Target --}}
                        <td class="py-3 px-4">
                            <div class="max-w-md">
                                <a href="{{ route('insiden.show', $inc->id) }}" class="font-medium text-white hover:text-[#ef4444] transition-colors truncate block">
                                    {{ $inc->judul }}
                                </a>
                                <div class="text-[11px] text-[#64748b] flex items-center gap-2 mt-0.5 flex-wrap">
                                    @if($inc->target)
                                    <span class="font-mono text-slate-400 truncate max-w-xs">{{ $inc->target }}</span>
                                    @endif
                                    @if($inc->temuan_id)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-red-500/10 text-red-400 border border-red-500/20">Temuan Scan</span>
                                    @elseif($inc->scan_id)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-purple-500/10 text-purple-400 border border-purple-500/20">Subdomain/Scan</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Keparahan --}}
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $sevBadge['bg'] }} {{ $sevBadge['border'] }} {{ $sevBadge['text'] }}">
                                {{ $sevBadge['label'] }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold border flex items-center gap-1.5 w-max {{ $statusBadge['bg'] }} {{ $statusBadge['border'] }} {{ $statusBadge['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusBadge['dot'] }}"></span>
                                <span>{{ $statusBadge['label'] }}</span>
                            </span>
                        </td>

                        {{-- PIC / Assignee --}}
                        <td class="py-3 px-4 whitespace-nowrap" style="color:#ccc;">
                            {{ $inc->assigned_to ?? 'Belum Ditugaskan' }}
                        </td>

                        {{-- Dibuat --}}
                        <td class="py-3 px-4 whitespace-nowrap text-[#64748b] text-[11px]">
                            {{ $inc->created_at->format('d M Y, H:i') }}
                        </td>

                        {{-- Actions --}}
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('insiden.show', $inc->id) }}"
                                   class="px-2.5 py-1 rounded-lg bg-[#1f1414] hover:bg-[#2a1a1a] text-[11px] text-[#ef4444] hover:text-red-300 transition-colors">
                                    Detail
                                </a>
                                <form method="POST" action="{{ route('insiden.destroy', $inc->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus insiden ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded-lg hover:bg-red-500/10 text-[#64748b] hover:text-red-400 transition-colors" title="Hapus Insiden">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-[#64748b]">
                            <div class="max-w-sm mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-xl bg-[#1f1414] border border-[#3a1f1f] flex items-center justify-center mx-auto text-[#64748b]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-sm font-medium" style="color:#ccc;">Belum ada insiden keamanan yang terdaftar</p>
                                <p class="text-xs text-[#64748b]">Insiden dapat dibuat secara otomatis dari temuan scan kerentanan atau dibuat secara manual.</p>
                                <div class="pt-2">
                                    <a href="{{ route('insiden.create') }}" class="px-3.5 py-1.5 rounded-lg bg-[#1f1414] hover:bg-[#2a1a1a] border border-[#3a1f1f] text-xs text-[#ef4444] font-medium transition-colors inline-block">
                                        Buat Insiden Baru
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($insidens->hasPages())
        <div class="p-4 border-t border-[#242424]">
            {{ $insidens->links() }}
        </div>
        @endif
    </div>

    {{-- ── PANDUAN REMEDIASI (MATCHING POPUP MODAL PETUNJUK STYLE) ── --}}
    <div class="rounded-xl p-4 flex items-start justify-between gap-4 flex-wrap"
         style="background:#1b1212; border:1px solid #3a1f1f;">
        <div class="flex items-start gap-3.5 max-w-3xl">
            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                 style="border:1.5px solid #ef4444; color:#ef4444; font-size:11px; font-weight:800;">!</div>
            <div>
                <p style="color:#ef4444; font-size:0.62rem; letter-spacing:0.14em; text-transform:uppercase; font-weight:700; font-family:'IBM Plex Mono',monospace; margin-bottom:0.25rem;">
                    PANDUAN REMEDIASI &amp; SIKLUS HIDUP INSIDEN
                </p>
                <p style="color:#888; font-size:0.78rem; line-height:1.65;">
                    Tangani insiden dari tingkat keparahan <strong class="text-red-400">Kritis</strong> terlebih dahulu untuk memperkecil celah eksploitasi. Siklus status:
                    <span class="text-amber-400 font-mono">Open</span> &rarr;
                    <span class="text-purple-400 font-mono">Investigating</span> &rarr;
                    <span class="text-violet-400 font-mono">Patching</span> &rarr;
                    <span class="text-emerald-400 font-mono">Resolved</span>. Setelah perbaikan selesai, lakukan scan ulang target untuk verifikasi otomatis.
                </p>
            </div>
        </div>
        <button onclick="window.dispatchEvent(new CustomEvent('open-help', { detail: { feature: 'insiden' } }))"
                class="px-4 py-2 rounded-lg text-xs font-semibold text-white transition-all hover:opacity-90 shrink-0"
                style="background:linear-gradient(135deg,#ef4444,#dc2626); box-shadow:0 2px 10px rgba(239,68,68,0.3);">
            Buka Panduan
        </button>
    </div>

    {{-- FLOATING ACTION BUTTON (FAB) --}}
    <div class="fixed bottom-6 right-6 z-40">
        <a href="{{ route('insiden.create') }}"
           class="w-12 h-12 rounded-2xl flex items-center justify-center text-white transition-all transform hover:scale-110 active:scale-95 shadow-xl group"
           style="background:linear-gradient(135deg, #ef4444 0%, #a855f7 100%); box-shadow:0 0 20px rgba(239,68,68,0.5);"
           title="Buat Insiden Baru (Pintasan)">
            <svg class="w-5 h-5 text-white group-hover:rotate-12 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
            </svg>
        </a>
    </div>

</div>
@endsection
