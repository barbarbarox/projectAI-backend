@props(['temuan'])

@php
    // Helper: label severity → warna Tailwind
    $sevColor = fn($sev) => match(strtolower($sev ?? '')) {
        'kritis', 'critical' => ['bg' => 'bg-red-500/15',    'border' => 'border-red-500/40',    'text' => 'text-red-400',    'dot' => 'bg-red-500'],
        'tinggi', 'high'     => ['bg' => 'bg-orange-500/15', 'border' => 'border-orange-500/40', 'text' => 'text-orange-400', 'dot' => 'bg-orange-500'],
        'sedang', 'medium'   => ['bg' => 'bg-yellow-500/15', 'border' => 'border-yellow-500/40', 'text' => 'text-yellow-400', 'dot' => 'bg-yellow-500'],
        'rendah', 'low'      => ['bg' => 'bg-sky-500/15',    'border' => 'border-sky-500/40',    'text' => 'text-sky-400',    'dot' => 'bg-sky-500'],
        default              => ['bg' => 'bg-slate-500/15',  'border' => 'border-slate-500/40',  'text' => 'text-slate-400',  'dot' => 'bg-slate-500'],
    };

    $llmColor  = $sevColor($temuan->tingkat_keparahan);
    $mlColor   = $temuan->ml_predicted_severity ? $sevColor($temuan->ml_predicted_severity) : null;
    $konColor  = $temuan->konsensus_severity    ? $sevColor($temuan->konsensus_severity)    : null;

    $agreement = $temuan->ml_predicted_severity
        ? strtolower($temuan->ml_predicted_severity) === match(strtolower($temuan->tingkat_keparahan ?? '')) {
            'kritis' => 'critical', 'tinggi' => 'high',
            'sedang' => 'medium',  'rendah' => 'low',
            default  => strtolower($temuan->tingkat_keparahan ?? '')
          }
        : false;

    $llmLabel = strtoupper(match(strtolower($temuan->tingkat_keparahan ?? '')) {
        'kritis' => 'CRITICAL', 'tinggi' => 'HIGH',
        'sedang' => 'MEDIUM',  'rendah' => 'LOW', 'info' => 'INFO',
        default  => $temuan->tingkat_keparahan ?? '-'
    });
@endphp

<div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] overflow-hidden" x-data="{ buka: false }">
    <button @click="buka = !buka" class="w-full px-5 py-4 flex items-center justify-between hover:bg-[#1e2d4a]/30 transition-colors text-left">
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <x-severity-badge :tingkat="$temuan->tingkat_keparahan" />
            <div class="min-w-0">
                <p class="text-sm font-medium truncate">{{ $temuan->judul }}</p>
                <p class="text-xs text-[#64748b] mt-0.5">
                    @if($temuan->cwe_id) · {{ $temuan->cwe_id }} @endif
                    @if($temuan->cve_id)
                        · <a href="{{ route('analisis.cve-intel') }}?cve={{ urlencode($temuan->cve_id) }}" 
                             target="_blank" 
                             @click.stop 
                             class="text-[#00d4ff] hover:text-[#38bdf8] hover:underline font-mono inline-flex items-center gap-0.5 font-medium transition-colors"
                             title="Buka di CVE Intelligence Dashboard">
                             {{ $temuan->cve_id }}
                             <svg class="w-2.5 h-2.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                          </a>
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0 ml-3">
            {{-- ML Badge kecil di header (hanya jika ML ada) --}}
            @if($temuan->ml_predicted_severity)
                <span class="hidden sm:flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold
                    {{ $mlColor['bg'] }} {{ $mlColor['text'] }} {{ $mlColor['border'] }} border">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/></svg>
                    ML: {{ $temuan->ml_predicted_severity }}
                </span>
                @if($agreement)
                    <span class="hidden sm:flex items-center gap-1 text-[10px] text-emerald-400 font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Agree
                    </span>
                @else
                    <span class="hidden sm:flex items-center gap-1 text-[10px] text-amber-400 font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        Differ
                    </span>
                @endif
            @endif
            <svg class="w-5 h-5 text-[#64748b] transition-transform" :class="buka && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
        </div>
    </button>

    <div x-show="buka" x-collapse class="px-5 pb-5 space-y-4 border-t border-[#1e2d4a]">

        @if($temuan->deskripsi)
        <div class="pt-4">
            <h4 class="text-xs font-semibold text-[#64748b] uppercase mb-2">Deskripsi</h4>
            <p class="text-sm text-[#94a3b8]">{{ $temuan->deskripsi }}</p>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════
             🌲 ML VERDICT CARD — Pilar 1: LLM vs Ensemble ML
             Hanya muncul jika ML service sudah mengembalikan hasil
             ══════════════════════════════════════════════════════ --}}
        @if($temuan->ml_predicted_severity)
        <div class="rounded-xl border border-[#1e3a5f]/60 bg-gradient-to-br from-[#0a1628] to-[#0d1f3c] p-4 space-y-3">

            {{-- Header ML Card --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md bg-blue-500/20 border border-blue-500/30 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                    </div>
                    <span class="text-xs font-semibold text-blue-300 tracking-wide uppercase">Hybrid AI Verdict</span>
                </div>
                <span class="text-[10px] text-[#64748b]">Ensemble ML (RF+XGB) · 297K CVE samples</span>
            </div>

            {{-- LLM vs ML columns --}}
            <div class="grid grid-cols-2 gap-3">
                {{-- LLM --}}
                <div class="rounded-lg p-3 {{ $llmColor['bg'] }} border {{ $llmColor['border'] }}">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <svg class="w-3 h-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/></svg>
                        <span class="text-[10px] font-semibold text-[#64748b] uppercase tracking-wider">LLM Assessment</span>
                    </div>
                    <div class="text-base font-bold {{ $llmColor['text'] }}">{{ $llmLabel }}</div>
                    <div class="text-[10px] text-[#64748b] mt-0.5">Gemini / Groq</div>
                </div>

                {{-- ML Model --}}
                <div class="rounded-lg p-3 {{ $mlColor['bg'] }} border {{ $mlColor['border'] }}">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/></svg>
                        <span class="text-[10px] font-semibold text-[#64748b] uppercase tracking-wider">ML Model (RF)</span>
                    </div>
                    <div class="text-base font-bold {{ $mlColor['text'] }}">{{ $temuan->ml_predicted_severity }}</div>
                    <div class="text-[10px] text-[#64748b] mt-0.5">
                        Confidence: {{ number_format(($temuan->ml_confidence ?? 0) * 100, 1) }}%
                    </div>
                </div>
            </div>

            {{-- Confidence Bar --}}
            @if($temuan->ml_confidence)
            <div class="space-y-1">
                <div class="flex justify-between text-[10px] text-[#64748b]">
                    <span>Model Confidence</span>
                    <span class="{{ $temuan->ml_confidence >= 0.8 ? 'text-emerald-400' : ($temuan->ml_confidence >= 0.6 ? 'text-yellow-400' : 'text-red-400') }} font-semibold">
                        {{ number_format($temuan->ml_confidence * 100, 1) }}%
                    </span>
                </div>
                <div class="h-1.5 w-full bg-[#1e2d4a] rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700
                        {{ $temuan->ml_confidence >= 0.8 ? 'bg-emerald-500' : ($temuan->ml_confidence >= 0.6 ? 'bg-yellow-500' : 'bg-red-500') }}"
                        style="width: {{ number_format($temuan->ml_confidence * 100, 1) }}%">
                    </div>
                </div>
            </div>
            @endif

            {{-- Probability distribution (mini bar chart) --}}
            @if($temuan->ml_class_probabilities && is_array($temuan->ml_class_probabilities))
            <div class="space-y-1.5">
                <span class="text-[10px] text-[#64748b] uppercase font-semibold tracking-wider">Distribusi Probabilitas</span>
                @php
                    $probColors = [
                        'CRITICAL' => 'bg-red-500',
                        'HIGH'     => 'bg-orange-500',
                        'MEDIUM'   => 'bg-yellow-500',
                        'LOW'      => 'bg-sky-500',
                        'NONE'     => 'bg-slate-500',
                    ];
                    $probs = $temuan->ml_class_probabilities;
                    arsort($probs);
                @endphp
                @foreach($probs as $kelas => $prob)
                @php $pct = round($prob * 100, 1); @endphp
                @if($pct > 0.5)
                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-[#64748b] w-16 shrink-0">{{ $kelas }}</span>
                    <div class="flex-1 h-1 bg-[#1e2d4a] rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $probColors[$kelas] ?? 'bg-slate-500' }}"
                             style="width: {{ $pct }}%; opacity: {{ $kelas === $temuan->ml_predicted_severity ? '1' : '0.45' }}">
                        </div>
                    </div>
                    <span class="text-[10px] w-9 text-right shrink-0
                        {{ $kelas === $temuan->ml_predicted_severity ? 'text-white font-semibold' : 'text-[#64748b]' }}">
                        {{ $pct }}%
                    </span>
                </div>
                @endif
                @endforeach
            </div>
            @endif

            {{-- Konsensus footer --}}
            <div class="flex items-center justify-between pt-2 border-t border-[#1e2d4a]">
                <div class="flex items-center gap-2">
                    @if($agreement)
                        <div class="flex items-center gap-1.5 text-emerald-400 text-xs font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            LLM & ML Sepakat
                        </div>
                    @else
                        <div class="flex items-center gap-1.5 text-amber-400 text-xs font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            Prediksi Berbeda
                        </div>
                    @endif
                </div>
                @if($temuan->konsensus_severity)
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] text-[#64748b]">Konsensus:</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                        {{ $konColor['bg'] }} {{ $konColor['text'] }} {{ $konColor['border'] }} border tracking-wide">
                        {{ $temuan->konsensus_severity }}
                    </span>
                </div>
                @endif
            </div>
        </div>
        @endif
        {{-- ══════════════════════════════ END ML VERDICT CARD ══════════════════════════════ --}}

        @if($temuan->kode_rentan || $temuan->kode_aman)
        <div class="grid md:grid-cols-2 gap-3">
            @if($temuan->kode_rentan)
            <div>
                <h4 class="text-xs font-semibold text-red-400 mb-2 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Kode Rentan</h4>
                <pre class="p-3 rounded-lg bg-red-500/5 border border-red-500/20 text-xs text-red-300 overflow-x-auto"><code>{{ $temuan->kode_rentan }}</code></pre>
            </div>
            @endif
            @if($temuan->kode_aman)
            <div>
                <h4 class="text-xs font-semibold text-green-400 mb-2 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Kode Aman</h4>
                <pre class="p-3 rounded-lg bg-green-500/5 border border-green-500/20 text-xs text-green-300 overflow-x-auto"><code>{{ $temuan->kode_aman }}</code></pre>
            </div>
            @endif
        </div>
        @endif
        @if($temuan->remediasi)
        <div>
            <h4 class="text-xs font-semibold text-[#00d4ff] mb-2 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Remediasi</h4>
            <p class="text-sm text-[#94a3b8]">{{ $temuan->remediasi }}</p>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════
             🔍 EVIDENCE / BUKTI — Panel Koleksi Bukti per Temuan
        ══════════════════════════════════════════════════════════ --}}
        <div x-data="evidencePanel('{{ $temuan->id }}', {{ $temuan->evidence->count() }})" class="evidence-panel">

            {{-- Header --}}
            <div class="ev-panel-header" @click="open = !open; if(open && !loaded) load()">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div class="ev-icon-wrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="ev-panel-title">Bukti / Evidence</span>
                    <span class="ev-count-badge" x-text="count + ' item'"></span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span x-show="count === 0" class="ev-empty-hint">Belum ada bukti</span>
                    <svg class="w-4 h-4" style="color:#475569;transition:transform 0.2s;" :style="open && 'transform:rotate(180deg)'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            {{-- Panel Body --}}
            <div x-show="open" x-collapse class="ev-panel-body">

                {{-- Loading --}}
                <div x-show="loading" class="ev-loading">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span>Memuat bukti...</span>
                </div>

                {{-- Evidence List --}}
                <template x-if="!loading">
                    <div class="space-y-3">

                        {{-- Grid Evidence Items --}}
                        <template x-if="items.length > 0">
                            <div class="ev-grid">
                                <template x-for="ev in items" :key="ev.id">
                                    <div class="ev-item" :class="ev.is_verified && 'ev-item--verified'">

                                        {{-- Screenshot / Image --}}
                                        <template x-if="ev.tipe === 'screenshot' || ev.tipe === 'url_capture' || ev.tipe === 'manual'">
                                            <div class="ev-img-wrap">
                                                <img :src="ev.display_url"
                                                     :alt="ev.label"
                                                     class="ev-img"
                                                     loading="lazy"
                                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                                <div class="ev-img-error" style="display:none;">
                                                    <svg class="w-6 h-6" style="color:#334155;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                                                    <span style="font-size:10px;color:#475569;">Gagal muat gambar</span>
                                                </div>
                                                <a :href="ev.display_url" target="_blank" class="ev-img-fullscreen" title="Buka fullscreen">↗</a>
                                            </div>
                                        </template>

                                        {{-- Text Evidence --}}
                                        <template x-if="ev.tipe === 'http_response' || ev.tipe === 'file_content'">
                                            <div class="ev-text-wrap">
                                                <pre class="ev-text-content" x-text="ev.meta?.content?.slice(0, 500) + (ev.meta?.content?.length > 500 ? '\n...' : '')"></pre>
                                            </div>
                                        </template>

                                        {{-- Item Footer --}}
                                        <div class="ev-item-footer">
                                            <div style="flex:1;min-width:0;">
                                                <div class="ev-item-label" x-text="ev.label || ev.tipe_label"></div>
                                                <div class="ev-item-meta">
                                                    <span class="ev-type-badge" x-text="ev.tipe_label"></span>
                                                    <template x-if="ev.is_verified">
                                                        <span class="ev-verified-badge">✓ Verified</span>
                                                    </template>
                                                    <span x-text="ev.created_at" style="color:#334155;"></span>
                                                </div>
                                            </div>
                                            <div style="display:flex;gap:4px;flex-shrink:0;">
                                                {{-- Verify Toggle --}}
                                                <button @click="toggleVerify(ev)"
                                                        :title="ev.is_verified ? 'Unverify' : 'Tandai Verified'"
                                                        class="ev-action-btn" :class="ev.is_verified ? 'ev-action-btn--active' : ''">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </button>
                                                {{-- Delete --}}
                                                <button @click="deleteEvidence(ev.id)" title="Hapus" class="ev-action-btn ev-action-btn--danger">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Empty State --}}
                        <template x-if="items.length === 0">
                            <div class="ev-empty">
                                <svg class="w-8 h-8 mx-auto mb-2" style="color:#1e293b;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                                <p>Belum ada bukti untuk temuan ini.</p>
                                <p style="color:#334155;">Klik tombol di bawah untuk menambahkan bukti.</p>
                            </div>
                        </template>

                        {{-- Action Toolbar --}}
                        <div class="ev-toolbar">
                            {{-- Auto Screenshot --}}
                            <button @click="captureScreenshot()"
                                    :disabled="capturing"
                                    class="ev-btn ev-btn--primary">
                                <template x-if="!capturing">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                                </template>
                                <template x-if="capturing">
                                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                </template>
                                <span x-text="capturing ? 'Capturing...' : 'Auto Screenshot'"></span>
                            </button>

                            {{-- Custom URL --}}
                            <button @click="showUrlInput = !showUrlInput" class="ev-btn ev-btn--secondary">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9"/></svg>
                                URL
                            </button>

                            {{-- Upload --}}
                            <button @click="$refs.fileInput.click()" class="ev-btn ev-btn--secondary">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Upload
                            </button>
                            <input type="file" x-ref="fileInput" accept="image/*" class="hidden" @change="uploadFile($event)">

                            {{-- Teks/Kode --}}
                            <button @click="showTextInput = !showTextInput" class="ev-btn ev-btn--secondary">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Teks
                            </button>
                        </div>

                        {{-- URL Input (expandable) --}}
                        <div x-show="showUrlInput" x-collapse class="ev-input-wrap">
                            <input x-model="customUrl" type="url" placeholder="https://contoh.com/path-rentan"
                                   class="ev-input" @keydown.enter="captureCustomUrl()">
                            <input x-model="urlLabel" type="text" placeholder="Label (opsional)"
                                   class="ev-input">
                            <button @click="captureCustomUrl()" :disabled="!customUrl || capturing"
                                    class="ev-btn ev-btn--primary w-full">Capture URL</button>
                        </div>

                        {{-- Text Input (expandable) --}}
                        <div x-show="showTextInput" x-collapse class="ev-input-wrap">
                            <select x-model="textTipe" class="ev-input">
                                <option value="http_response">HTTP Response</option>
                                <option value="file_content">File / Path Content</option>
                                <option value="manual">Catatan Manual</option>
                            </select>
                            <input x-model="textLabel" type="text" placeholder="Label/Judul bukti" class="ev-input">
                            <textarea x-model="textContent" placeholder="Tempel isi HTTP response, konten file, atau catatan di sini..." rows="4"
                                      class="ev-input" style="font-family:monospace;font-size:11px;"></textarea>
                            <button @click="addText()" :disabled="!textLabel || !textContent || capturing"
                                    class="ev-btn ev-btn--primary w-full">Simpan Bukti Teks</button>
                        </div>

                        {{-- Error Msg --}}
                        <div x-show="errorMsg" class="ev-error" x-text="errorMsg"></div>

                    </div>
                </template>
            </div>
        </div>
        {{-- ═══════════════════════ END EVIDENCE PANEL ═══════════════════════ --}}

        {{-- Exploit Payload Generator (Red Team) --}}
        <div x-data="{ 
            loadingPayload: false, 
            payloadData: null, 
            errorPayload: null,
            generatePayload() {
                this.loadingPayload = true;
                this.errorPayload = null;
                fetch('{{ route('payload.generate', $temuan->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    this.loadingPayload = false;
                    if(data.error) throw new Error(data.error);
                    this.payloadData = data;
                })
                .catch(err => {
                    this.loadingPayload = false;
                    this.errorPayload = err.message;
                });
            }
        }" class="mt-4 border-t border-[#1e2d4a] pt-4">
            
            <button x-show="!payloadData && !loadingPayload" @click="generatePayload()" class="px-4 py-2 bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white text-xs font-medium rounded-lg transition-colors flex items-center gap-2 border border-red-500/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Generate Weaponized Payload
            </button>

            <div x-show="loadingPayload" class="flex items-center gap-2 text-sm text-red-400 animate-pulse">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Meracik payload spesifik target...
            </div>

            <div x-show="errorPayload" class="text-xs text-red-400" x-text="errorPayload"></div>

            <div x-show="payloadData" class="space-y-3 bg-black/50 p-4 rounded-xl border border-red-500/30" style="display: none;">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-red-500 flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> EXPLOIT PAYLOAD (<span x-text="payloadData?.tipe_payload"></span>)</h4>
                </div>
                
                <div>
                    <span class="text-[10px] text-[#64748b] uppercase font-bold tracking-wider">Perintah Eksekusi</span>
                    <div class="relative mt-1 group">
                        <pre class="p-3 bg-black rounded-lg border border-slate-800 text-xs text-green-400 overflow-x-auto"><code x-text="payloadData?.perintah_eksekusi"></code></pre>
                    </div>
                </div>

                <div x-show="payloadData?.kode_script">
                    <span class="text-[10px] text-[#64748b] uppercase font-bold tracking-wider">Script (Opsional)</span>
                    <pre class="mt-1 p-3 bg-black rounded-lg border border-slate-800 text-xs text-cyan-400 overflow-x-auto"><code x-text="payloadData?.kode_script"></code></pre>
                </div>

                <div>
                    <span class="text-[10px] text-[#64748b] uppercase font-bold tracking-wider">Cara Kerja</span>
                    <p class="mt-1 text-xs text-[#94a3b8] leading-relaxed" x-text="payloadData?.penjelasan_cara_kerja"></p>
                </div>

                <div class="p-2 bg-red-500/10 rounded border border-red-500/20 flex gap-2">
                    <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <p class="text-[11px] text-red-400" x-text="payloadData?.peringatan"></p>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between gap-4 text-xs text-[#64748b] pt-2 border-t border-[#1e2d4a] flex-wrap">
            <div class="flex items-center gap-4">
                @if($temuan->estimasi_usaha)<span>Usaha: <b class="text-[#94a3b8]">{{ ucfirst($temuan->estimasi_usaha) }}</b></span>@endif
                @if($temuan->tingkat_kepercayaan)<span>Kepercayaan: <b class="text-[#94a3b8]">{{ ucfirst($temuan->tingkat_kepercayaan) }}</b></span>@endif
                @if($temuan->status && $temuan->status !== 'baru')
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase {{ $temuan->status === 'selesai' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' }}">
                        {{ str_replace('_', ' ', $temuan->status) }}
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @if($temuan->insiden)
                    <a href="{{ route('insiden.show', $temuan->insiden->id) }}"
                       class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[11px] font-mono flex items-center gap-1.5 transition-colors"
                       title="Lihat Tiket Insiden">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $temuan->insiden->nomor_insiden }} ({{ ucfirst($temuan->insiden->status) }})</span>
                    </a>
                @else
                    <form method="POST" action="{{ route('insiden.store-from-temuan', $temuan->id) }}">
                        @csrf
                        <button type="submit"
                                class="px-2.5 py-1 rounded-lg bg-gradient-to-r from-red-600/30 to-amber-600/30 hover:from-red-600 hover:to-amber-600 text-amber-200 hover:text-white border border-amber-500/30 text-[11px] font-semibold flex items-center gap-1.5 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Eskalasi ke Insiden</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ═══ Evidence Panel CSS (loaded once, safe to repeat) ═══ --}}
@once
<style>
/* ── Evidence Panel Container ── */
.evidence-panel {
    border: 1px solid #1e2d4a;
    border-radius: 10px;
    overflow: hidden;
}
.ev-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 14px;
    background: rgba(10,14,26,0.8);
    cursor: pointer;
    user-select: none;
    transition: background 0.2s;
}
.ev-panel-header:hover { background: rgba(30,41,59,0.5); }
.ev-icon-wrap {
    width: 22px; height: 22px;
    border-radius: 6px;
    background: rgba(0,212,255,0.08);
    border: 1px solid rgba(0,212,255,0.18);
    display: flex; align-items: center; justify-content: center;
    color: #00d4ff;
}
.ev-panel-title { font-size: 11px; font-weight: 600; color: #94a3b8; }
.ev-count-badge {
    font-size: 10px; color: #475569;
    background: #111827; padding: 1px 7px;
    border-radius: 999px; border: 1px solid #1e2d4a;
}
.ev-empty-hint { font-size: 10px; color: #334155; font-style: italic; }

/* ── Panel Body ── */
.ev-panel-body {
    padding: 12px;
    border-top: 1px solid #1e2d4a;
    background: #070b16;
}
.ev-loading {
    display: flex; align-items: center; gap: 8px;
    font-size: 11px; color: #475569; padding: 8px 0;
}

/* ── Grid ── */
.ev-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 10px;
}
.ev-item {
    border: 1px solid #1e2d4a;
    border-radius: 8px;
    overflow: hidden;
    background: #0a0e1a;
    transition: border-color 0.2s;
}
.ev-item--verified { border-color: rgba(52,211,153,0.35); }
.ev-item:hover { border-color: #334155; }

/* ── Image evidence ── */
.ev-img-wrap { position: relative; height: 110px; overflow: hidden; background: #0f1629; }
.ev-img { width: 100%; height: 100%; object-fit: cover; object-position: top; display: block; transition: filter 0.3s; }
.ev-img-wrap:hover .ev-img { filter: brightness(1.1); }
.ev-img-error {
    height: 100%; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 4px;
}
.ev-img-fullscreen {
    position: absolute; top: 6px; right: 6px;
    background: rgba(0,0,0,0.6); color: #94a3b8;
    font-size: 11px; padding: 1px 6px; border-radius: 5px;
    text-decoration: none; backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,0.08);
    opacity: 0; transition: opacity 0.2s;
}
.ev-img-wrap:hover .ev-img-fullscreen { opacity: 1; }

/* ── Text evidence ── */
.ev-text-wrap { padding: 8px; background: #0f1629; max-height: 90px; overflow: hidden; }
.ev-text-content { font-size: 10px; color: #64748b; font-family: 'JetBrains Mono', monospace; white-space: pre-wrap; word-break: break-all; margin: 0; line-height: 1.5; }

/* ── Item Footer ── */
.ev-item-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 6px 8px;
    border-top: 1px solid #111827;
    gap: 4px;
}
.ev-item-label { font-size: 10px; font-weight: 600; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ev-item-meta { display: flex; align-items: center; gap: 4px; margin-top: 2px; flex-wrap: wrap; }
.ev-type-badge {
    font-size: 9px; padding: 0 5px;
    background: rgba(30,41,59,0.8); color: #475569;
    border-radius: 3px; border: 1px solid #1e293b;
}
.ev-verified-badge {
    font-size: 9px; color: #34d399; font-weight: 600;
}

/* ── Action Buttons ── */
.ev-action-btn {
    width: 22px; height: 22px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 5px; border: 1px solid #1e2d4a;
    background: transparent; cursor: pointer; color: #475569;
    transition: all 0.15s;
}
.ev-action-btn:hover { background: rgba(30,41,59,0.8); color: #94a3b8; }
.ev-action-btn--active { border-color: rgba(52,211,153,0.4); color: #34d399 !important; background: rgba(52,211,153,0.08); }
.ev-action-btn--danger:hover { border-color: rgba(239,68,68,0.4); color: #f87171; background: rgba(239,68,68,0.08); }

/* ── Empty State ── */
.ev-empty { text-align: center; padding: 20px 10px; font-size: 11px; color: #475569; line-height: 1.7; }

/* ── Toolbar ── */
.ev-toolbar { display: flex; flex-wrap: wrap; gap: 6px; }
.ev-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 10px; border-radius: 7px; font-size: 11px; font-weight: 500;
    cursor: pointer; border: 1px solid; transition: all 0.15s;
}
.ev-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.ev-btn--primary {
    background: rgba(0,212,255,0.07); border-color: rgba(0,212,255,0.25); color: #00d4ff;
}
.ev-btn--primary:hover:not(:disabled) { background: rgba(0,212,255,0.15); }
.ev-btn--secondary {
    background: rgba(30,41,59,0.6); border-color: #1e293b; color: #64748b;
}
.ev-btn--secondary:hover { background: rgba(30,41,59,0.9); color: #94a3b8; }
.w-full { width: 100%; justify-content: center; }

/* ── Inputs ── */
.ev-input-wrap { display: flex; flex-direction: column; gap: 6px; padding-top: 4px; }
.ev-input {
    width: 100%; box-sizing: border-box;
    background: #0f1629; border: 1px solid #1e293b; border-radius: 7px;
    padding: 6px 10px; font-size: 11px; color: #e2e8f0;
    transition: border-color 0.2s; outline: none;
}
.ev-input:focus { border-color: rgba(0,212,255,0.4); }
.ev-input select, textarea.ev-input { resize: vertical; }

/* ── Error ── */
.ev-error { font-size: 11px; color: #f87171; background: rgba(220,38,38,0.08); padding: 6px 10px; border-radius: 6px; border: 1px solid rgba(220,38,38,0.2); }
</style>

<script>
function evidencePanel(temuanId, initialCount) {
    return {
        temuanId,
        open: false,
        loaded: false,
        loading: false,
        capturing: false,
        items: [],
        count: initialCount,
        errorMsg: '',
        showUrlInput: false,
        showTextInput: false,
        customUrl: '',
        urlLabel: '',
        textTipe: 'http_response',
        textLabel: '',
        textContent: '',

        csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        },

        async load() {
            this.loading = true;
            this.loaded = true;
            try {
                const r = await fetch(`/evidence/${this.temuanId}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }
                });
                const d = await r.json();
                this.items = d.evidence ?? [];
                this.count = this.items.length;
            } catch (e) {
                this.errorMsg = 'Gagal memuat bukti.';
            }
            this.loading = false;
        },

        async captureScreenshot() {
            this.capturing = true;
            this.errorMsg = '';
            try {
                const r = await fetch(`/evidence/${this.temuanId}/capture-screenshot`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }
                });
                const d = await r.json();
                if (d.success) {
                    const idx = this.items.findIndex(i => i.id === d.evidence.id);
                    if (idx >= 0) this.items[idx] = d.evidence;
                    else this.items.push(d.evidence);
                    this.count = this.items.length;
                } else {
                    this.errorMsg = d.error ?? 'Gagal capture screenshot.';
                }
            } catch (e) { this.errorMsg = 'Gagal menghubungi server.'; }
            this.capturing = false;
        },

        async captureCustomUrl() {
            if (!this.customUrl) return;
            this.capturing = true;
            this.errorMsg = '';
            try {
                const r = await fetch(`/evidence/${this.temuanId}/capture-url`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    body: JSON.stringify({ url: this.customUrl, label: this.urlLabel || null })
                });
                const d = await r.json();
                if (d.success) {
                    this.items.push(d.evidence);
                    this.count = this.items.length;
                    this.customUrl = ''; this.urlLabel = ''; this.showUrlInput = false;
                } else { this.errorMsg = d.error ?? 'Gagal capture URL.'; }
            } catch (e) { this.errorMsg = 'Gagal menghubungi server.'; }
            this.capturing = false;
        },

        async uploadFile(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            this.capturing = true;
            this.errorMsg = '';
            const fd = new FormData();
            fd.append('file', file);
            fd.append('label', file.name);
            fd.append('_token', this.csrf());
            try {
                const r = await fetch(`/evidence/${this.temuanId}/upload`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    body: fd
                });
                const d = await r.json();
                if (d.success) {
                    this.items.push(d.evidence);
                    this.count = this.items.length;
                } else { this.errorMsg = d.error ?? 'Gagal upload.'; }
            } catch (e) { this.errorMsg = 'Gagal upload file.'; }
            this.capturing = false;
            event.target.value = '';
        },

        async addText() {
            if (!this.textLabel || !this.textContent) return;
            this.capturing = true;
            this.errorMsg = '';
            try {
                const r = await fetch(`/evidence/${this.temuanId}/add-text`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                    body: JSON.stringify({ tipe: this.textTipe, label: this.textLabel, content: this.textContent })
                });
                const d = await r.json();
                if (d.success) {
                    this.items.push(d.evidence);
                    this.count = this.items.length;
                    this.textLabel = ''; this.textContent = ''; this.showTextInput = false;
                } else { this.errorMsg = d.error ?? 'Gagal menyimpan teks.'; }
            } catch (e) { this.errorMsg = 'Gagal menghubungi server.'; }
            this.capturing = false;
        },

        async toggleVerify(ev) {
            try {
                const r = await fetch(`/evidence-item/${ev.id}/verify`, {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }
                });
                const d = await r.json();
                if (d.success) {
                    const idx = this.items.findIndex(i => i.id === ev.id);
                    if (idx >= 0) this.items[idx].is_verified = d.is_verified;
                }
            } catch (e) { this.errorMsg = 'Gagal toggle verify.'; }
        },

        async deleteEvidence(evId) {
            if (!confirm('Hapus bukti ini?')) return;
            try {
                const r = await fetch(`/evidence-item/${evId}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() }
                });
                const d = await r.json();
                if (d.success) {
                    this.items = this.items.filter(i => i.id !== evId);
                    this.count = this.items.length;
                }
            } catch (e) { this.errorMsg = 'Gagal menghapus.'; }
        }
    };
}
</script>
@endonce
