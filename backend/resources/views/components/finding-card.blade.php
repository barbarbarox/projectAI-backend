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
        <div class="flex items-center gap-4 text-xs text-[#64748b]">
            @if($temuan->estimasi_usaha)<span>Usaha: <b class="text-[#94a3b8]">{{ ucfirst($temuan->estimasi_usaha) }}</b></span>@endif
            @if($temuan->tingkat_kepercayaan)<span>Kepercayaan: <b class="text-[#94a3b8]">{{ ucfirst($temuan->tingkat_kepercayaan) }}</b></span>@endif
        </div>
    </div>

</div>

