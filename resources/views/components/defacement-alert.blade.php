@php
    $defacement = $defacementData ?? [];
    $isDefaced = (bool) ($defacement['is_defaced'] ?? false);
    $type = $defacement['type'] ?? 'clean';
    $severity = $defacement['severity'] ?? 'info';
@endphp

@if($isDefaced)
<div class="rounded-2xl {{ $severity === 'kritis' ? 'bg-red-500/10 border-red-500/40 shadow-red-950/20' : 'bg-amber-500/10 border-amber-500/40 shadow-amber-950/20' }} border p-6 shadow-xl relative overflow-hidden space-y-4">
    {{-- Glow background --}}
    <div class="absolute -top-12 -right-12 w-48 h-48 {{ $severity === 'kritis' ? 'bg-red-500/10' : 'bg-amber-500/10' }} rounded-full blur-3xl pointer-events-none"></div>

    {{-- Top Alert Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap relative z-10">
        <div class="flex items-start gap-3">
            <div class="p-2.5 rounded-xl {{ $severity === 'kritis' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }} shrink-0">
                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold uppercase tracking-wider {{ $severity === 'kritis' ? 'bg-red-500 text-white' : 'bg-amber-500 text-black' }}">
                        {{ $severity === 'kritis' ? 'PERINGATAN KRITIS' : 'PERINGATAN KEAMANAN' }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-black/40 text-slate-300 border border-white/10">
                        {{ $type === 'classic_defacement' ? 'Web Defacement' : ($type === 'hidden_backlinks' ? 'Cloaked SEO Injections' : 'SEO-Spam Judi Online') }}
                    </span>
                    <span class="text-xs text-[#94a3b8]">
                        Confidence: <strong class="text-white capitalize">{{ $defacement['confidence'] ?? 'Tinggi' }}</strong>
                    </span>
                </div>
                <h3 class="text-base font-bold text-white mt-1.5">
                    {{ $type === 'classic_defacement' ? 'Indikasi Peretasan Web Defacement Terdeteksi' : 'Indikasi Injeksi Konten Perjudian Ilegal / Slot Gacor Terdeteksi' }}
                </h3>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed max-w-3xl">
                    {{ $defacement['summary'] ?? 'Ditemukan tanda-tanda modifikasi konten tidak sah atau injeksi kata kunci promosi perjudian ilegal di halaman web target.' }}
                </p>
            </div>
        </div>

        {{-- 1-Click Escalate to Incident --}}
        @if(isset($scan))
        <a href="{{ route('insiden.create') }}?judul={{ urlencode('Insiden Defacement/SEO-Spam pada ' . $scan->target) }}&severity={{ $severity }}"
           class="px-4 py-2 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white text-xs font-semibold shadow-lg shadow-red-950/40 flex items-center gap-2 shrink-0 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Eskalasi ke Insiden SOC</span>
        </a>
        @endif
    </div>

    {{-- Detected Signatures & Keywords --}}
    @if(!empty($defacement['detected_signatures']))
    <div class="space-y-1.5 pt-2 border-t border-white/10">
        <span class="text-[11px] text-[#94a3b8] font-medium uppercase tracking-wider">Signature & Kata Kunci Terdeteksi:</span>
        <div class="flex items-center gap-1.5 flex-wrap">
            @foreach($defacement['detected_signatures'] as $sig)
            <span class="px-2.5 py-1 rounded-md text-xs font-mono font-medium {{ ($sig['kategori'] ?? '') === 'classic_defacement' ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                "{{ $sig['kata'] ?? '' }}"
            </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Evidence Snippets --}}
    @if(!empty($defacement['evidence_snippets']))
    <div class="space-y-1.5 pt-2">
        <span class="text-[11px] text-[#94a3b8] font-medium uppercase tracking-wider">Kutipan Potongan Konten Terinfeksi:</span>
        <div class="space-y-1">
            @foreach($defacement['evidence_snippets'] as $snippet)
            <div class="p-2.5 rounded-lg bg-[#0a0e1a]/80 border border-[#1e2d4a] text-xs font-mono text-slate-300 break-all leading-relaxed">
                {{ $snippet }}
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@else
{{-- Clean State Badge --}}
<div class="rounded-xl bg-[#0a0e1a] border border-emerald-500/30 p-4 flex items-center justify-between gap-4 flex-wrap">
    <div class="flex items-center gap-3">
        <span class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </span>
        <div>
            <h4 class="text-xs font-bold text-white flex items-center gap-2">
                <span>Integritas Konten: Bersih dari Defacement & SEO-Spam</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">VERIFIED CLEAN</span>
            </h4>
            <p class="text-[11px] text-[#64748b] mt-0.5">Tidak ditemukan pola hacktivism defacement tradisional maupun injeksi kata kunci perjudian ilegal / slot gacor pada halaman web target.</p>
        </div>
    </div>
</div>
@endif
