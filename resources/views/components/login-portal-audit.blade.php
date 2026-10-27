@php
    $audit = $loginAudit ?? [];
    $portals = $audit['portals'] ?? [];
    $totalFound = count($portals);
    $posture = $audit['posture'] ?? 'aman';
    $hasWaf = (bool) ($audit['has_waf'] ?? false);
@endphp

@if($totalFound > 0)
<div class="rounded-2xl bg-[#0a0e1a] border border-[#1e2d4a] p-6 shadow-xl space-y-5">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap border-b border-[#1e2d4a] pb-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </span>
                <h3 class="text-sm font-bold text-white">
                    Audit Portal Login & Akses Administratif ({{ $totalFound }} Endpoint)
                </h3>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono border {{ $posture === 'kritis' ? 'bg-red-500/15 border-red-500/40 text-red-400 font-bold' : ($posture === 'waspada' ? 'bg-amber-500/15 border-amber-500/40 text-amber-400 font-bold' : 'bg-emerald-500/15 border-emerald-500/40 text-emerald-400 font-bold') }}">
                    STATUS: {{ strtoupper($posture) }}
                </span>
            </div>
            <p class="text-xs text-[#64748b] mt-1">Evaluasi keamanan antarmuka login dan manajemen yang terdeteksi terbuka ke internet publik.</p>
        </div>

        {{-- Proteksi WAF Indicator --}}
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#0f172a] border border-[#1e2d4a] text-xs">
                <span class="w-2 h-2 rounded-full {{ $hasWaf ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                <span class="text-[#64748b]">WAF / Anti-Bruteforce:</span>
                <strong class="{{ $hasWaf ? 'text-emerald-400' : 'text-amber-400' }}">
                    {{ $hasWaf ? 'Terdeteksi' : 'Tidak Terdeteksi' }}
                </strong>
            </div>
        </div>
    </div>

    {{-- Portals Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#0f172a]/70 text-[#64748b] border-b border-[#1e2d4a] font-mono uppercase text-[10px] tracking-wider">
                    <th class="py-2.5 px-3">Path Endpoint</th>
                    <th class="py-2.5 px-3">Tipe / Layanan</th>
                    <th class="py-2.5 px-3 text-center">Status HTTP</th>
                    <th class="py-2.5 px-3">Tingkat Risiko</th>
                    <th class="py-2.5 px-3">Evaluasi Keamanan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e2d4a]/50">
                @foreach($portals as $portal)
                <tr class="hover:bg-[#0f172a]/40 transition-colors">
                    <td class="py-3 px-3 font-mono">
                        <a href="{{ $portal['url'] }}" target="_blank" rel="noopener noreferrer"
                           class="text-white hover:text-cyan-400 font-medium inline-flex items-center gap-1">
                            <span>{{ $portal['path'] }}</span>
                            <svg class="w-3 h-3 text-[#475569]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </td>
                    <td class="py-3 px-3 text-[#94a3b8]">
                        {{ $portal['deskripsi'] }}
                    </td>
                    <td class="py-3 px-3 text-center font-mono">
                        <span class="px-2 py-0.5 rounded text-[10px] {{ $portal['status_http'] === 200 ? 'bg-red-500/10 text-red-400 border border-red-500/30' : 'bg-slate-800 text-slate-300' }}">
                            HTTP {{ $portal['status_http'] }}
                        </span>
                    </td>
                    <td class="py-3 px-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase {{ $portal['risiko'] === 'kritis' ? 'bg-red-500/20 text-red-300 border border-red-500/40' : ($portal['risiko'] === 'sedang' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-slate-700 text-slate-300') }}">
                            {{ $portal['risiko'] }}
                        </span>
                    </td>
                    <td class="py-3 px-3">
                        @if(!empty($portal['issues']))
                        <ul class="space-y-0.5 text-[11px] text-amber-300">
                            @foreach($portal['issues'] as $iss)
                            <li class="flex items-center gap-1.5">
                                <span class="text-amber-400 font-bold shrink-0">!</span>
                                <span>{{ $iss }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <span class="text-emerald-400 text-[11px] flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Terlindungi akses dasar</span>
                        </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Rekomendasi Hardening --}}
    @if(!empty($audit['recommendations']))
    <div class="p-3.5 rounded-xl bg-[#0f172a] border border-[#1e2d4a] space-y-1.5">
        <span class="text-[11px] text-[#64748b] font-medium uppercase tracking-wider">Rekomendasi Pengamanan Akses Login:</span>
        <ul class="space-y-1 text-xs text-slate-300">
            @foreach($audit['recommendations'] as $rec)
            <li class="flex items-start gap-2">
                <span class="text-cyan-400 font-bold shrink-0">→</span>
                <span>{{ $rec }}</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endif
