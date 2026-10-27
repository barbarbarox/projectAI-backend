{{-- RAG Source Info Box Component --}}
{{-- Usage: <x-rag-source-info :scan="$scan" /> --}}

@props(['scan'])

@php
    $ragRefs = $scan->rag_references ?? [];
    $hasRag = is_array($ragRefs) && count($ragRefs) > 0;
    $modeRag = $scan->mode_rag ?? 'dengan_rag';
    $modeAi = $scan->mode_ai ?? 'dengan_ai';
    
    $sourceLabels = [
        'cisa-kev' => ['label' => 'CISA KEV', 'code' => 'KEV', 'color' => 'text-red-400 bg-red-500/10 border-red-500/20'],
        'attck' => ['label' => 'MITRE ATT&CK', 'code' => 'ATT&CK', 'color' => 'text-purple-400 bg-purple-500/10 border-purple-500/20'],
        'cwe' => ['label' => 'CWE', 'code' => 'CWE', 'color' => 'text-orange-400 bg-orange-500/10 border-orange-500/20'],
        'capec' => ['label' => 'CAPEC', 'code' => 'CAPEC', 'color' => 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20'],
        'owasp-cheatsheet' => ['label' => 'OWASP', 'code' => 'OWASP', 'color' => 'text-blue-400 bg-blue-500/10 border-blue-500/20'],
        'nvd-cve' => ['label' => 'NVD CVE', 'code' => 'CVE', 'color' => 'text-cyan-400 bg-cyan-500/10 border-cyan-500/20'],
        'huggingface' => ['label' => 'HuggingFace Dataset', 'code' => 'HF', 'color' => 'text-amber-400 bg-amber-500/10 border-amber-500/20'],
    ];

    $uniqueSources = $hasRag ? collect($ragRefs)->pluck('source')->unique()->filter()->values() : collect();
    $avgScore = $hasRag ? round(collect($ragRefs)->avg(fn($r) => $r['sem_score'] ?? $r['score'] ?? 0) * 100) : 0;
    $totalChunks = $hasRag ? count($ragRefs) : 0;
    $highRelevance = $hasRag ? collect($ragRefs)->filter(fn($r) => ($r['relevansi'] ?? '') === 'tinggi')->count() : 0;
@endphp

<div class="rounded-2xl bg-gradient-to-br from-[#0f1629] to-[#0a0e1a] border border-[#1e2d4a] p-6 relative overflow-hidden">
    {{-- Background decoration --}}
    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-[#00d4ff]/5 to-transparent rounded-bl-full"></div>
    
    <div class="relative">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#00d4ff]/20 to-[#7c3aed]/20 flex items-center justify-center text-cyan-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Sumber Data RAG (Knowledge Base)</h3>
                    <p class="text-[10px] text-[#64748b]">Informasi basis pengetahuan yang digunakan dalam analisis</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($modeRag === 'dengan_rag')
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-500/10 text-green-400 border border-green-500/20 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span> RAG Aktif
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-500/10 text-gray-400 border border-gray-500/20">
                        RAG Nonaktif
                    </span>
                @endif
            </div>
        </div>

        @if($modeRag !== 'dengan_rag')
            {{-- RAG Not Used --}}
            <div class="rounded-xl bg-[#0a0e1a]/50 border border-dashed border-[#1e2d4a] p-4 text-center">
                <div class="w-8 h-8 mx-auto mb-2 rounded-full bg-slate-800/80 flex items-center justify-center text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm text-[#94a3b8]">Analisis ini dilakukan <span class="text-yellow-400 font-medium">tanpa RAG</span></p>
                <p class="text-xs text-[#475569] mt-1">AI menggunakan pengetahuan dasar bawaan tanpa referensi knowledge base tambahan.</p>
            </div>
        @elseif(!$hasRag)
            {{-- RAG Enabled but no references found --}}
            <div class="rounded-xl bg-yellow-500/5 border border-dashed border-yellow-500/20 p-4 text-center">
                <div class="w-8 h-8 mx-auto mb-2 rounded-full bg-yellow-500/10 flex items-center justify-center text-yellow-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <p class="text-sm text-yellow-400">RAG aktif, tetapi tidak ditemukan referensi yang relevan</p>
                <p class="text-xs text-[#475569] mt-1">Knowledge base tidak memiliki data yang cukup relevan untuk query ini. Hasil analisis mungkin bersifat indikatif.</p>
            </div>
        @else
            {{-- RAG Stats --}}
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-3 text-center">
                    <div class="text-lg font-black text-[#00d4ff]">{{ $totalChunks }}</div>
                    <p class="text-[10px] text-[#64748b]">Dokumen Referensi</p>
                </div>
                <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-3 text-center">
                    <div class="text-lg font-black {{ $avgScore >= 75 ? 'text-green-400' : ($avgScore >= 60 ? 'text-yellow-400' : 'text-red-400') }}">{{ $avgScore }}%</div>
                    <p class="text-[10px] text-[#64748b]">Rata-rata Relevansi</p>
                </div>
                <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-3 text-center">
                    <div class="text-lg font-black text-[#7c3aed]">{{ $uniqueSources->count() }}</div>
                    <p class="text-[10px] text-[#64748b]">Sumber Data</p>
                </div>
            </div>

            {{-- Source Badges --}}
            <div class="mb-4">
                <p class="text-xs text-[#94a3b8] mb-2 font-medium">Sumber Data yang Digunakan:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($uniqueSources as $src)
                        @php
                            $cleanSrc = str_replace('hf:', '', $src);
                            $info = $sourceLabels[$cleanSrc] ?? ['label' => strtoupper($cleanSrc), 'code' => strtoupper($cleanSrc), 'color' => 'text-[#94a3b8] bg-[#1e2d4a]/50 border-[#1e2d4a]'];
                        @endphp
                        <div class="px-3 py-1.5 rounded-lg border flex items-center gap-2 {{ $info['color'] }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            <span class="text-xs font-semibold">{{ $info['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Detailed References (Collapsible) --}}
            <div x-data="{ showDetail: false }">
                <button @click="showDetail = !showDetail" 
                        class="flex items-center gap-2 text-xs text-[#00d4ff] hover:text-[#00d4ff]/80 transition-colors mb-3">
                    <svg class="w-3.5 h-3.5 transition-transform" :class="showDetail && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span x-text="showDetail ? 'Sembunyikan detail referensi' : 'Lihat detail referensi (' + {{ $totalChunks }} + ' dokumen)'"></span>
                </button>

                <div x-show="showDetail" x-collapse>
                    <p class="text-[10px] text-[#64748b] italic mb-2">* Dokumen sumber asli mungkin berbahasa Inggris dari database internasional (NVD, MITRE, dll).</p>
                    <div class="space-y-2 max-h-[400px] overflow-y-auto pr-1">
                        @foreach(array_slice($ragRefs, 0, 5) as $i => $ref)
                        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-3 text-sm hover:border-[#00d4ff]/30 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold text-[#475569]">#{{ $i + 1 }}</span>
                                    @php
                                        $refSrc = str_replace('hf:', '', $ref['source'] ?? 'General');
                                        $refInfo = $sourceLabels[$refSrc] ?? ['label' => ucfirst($refSrc), 'code' => strtoupper($refSrc), 'color' => 'text-[#7c3aed] bg-[#7c3aed]/10 border-[#7c3aed]/20'];
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium border {{ $refInfo['color'] }}">
                                        {{ $refInfo['label'] }}
                                    </span>
                                </div>
                                @php
                                    $score = round(($ref['sem_score'] ?? $ref['score'] ?? 0) * 100);
                                    $scoreColor = $score >= 78 ? 'text-green-400' : ($score >= 65 ? 'text-yellow-400' : 'text-red-400');
                                @endphp
                                <span class="text-xs {{ $scoreColor }} font-medium">{{ $score }}% relevan</span>
                            </div>
                            @if(!empty($ref['title']))
                                <p class="text-xs font-medium text-[#e2e8f0] mb-1">{{ $ref['title'] }}</p>
                            @endif
                            <p class="text-[#64748b] text-xs leading-relaxed line-clamp-2 hover:line-clamp-none transition-all cursor-pointer">{{ $ref['content'] ?? '' }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
