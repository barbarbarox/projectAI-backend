{{-- AI & RAG Options Component --}}
{{-- Include this in analysis forms with: @include('components.ai-options') --}}
<div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6" x-data="aiOptions()" x-init="checkAiHealth()">
    <p class="text-sm font-medium text-[#94a3b8] mb-4 flex items-center gap-2">
        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Opsi Analisis AI
    </p>

    {{-- AI Status Indicator --}}
    <div class="mb-4 p-3 rounded-xl border transition-all duration-300"
        :class="aiStatus === 'checking' ? 'bg-[#0a0e1a] border-[#1e2d4a]' :
                aiStatus === 'available' ? 'bg-green-500/5 border-green-500/20' :
                'bg-red-500/5 border-red-500/20'">
        <div class="flex items-center gap-3">
            <template x-if="aiStatus === 'checking'">
                <div class="w-5 h-5 rounded-full border-2 border-[#1e2d4a] border-t-[#00d4ff] animate-spin flex-shrink-0"></div>
            </template>
            <template x-if="aiStatus === 'available'">
                <div class="w-5 h-5 rounded-full bg-green-500/20 flex items-center justify-center flex-shrink-0">
                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                </div>
            </template>
            <template x-if="aiStatus === 'unavailable' || aiStatus === 'error'">
                <div class="w-5 h-5 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0">
                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                </div>
            </template>
            <div>
                <p class="text-xs font-medium" :class="aiStatus === 'available' ? 'text-green-400' : (aiStatus === 'checking' ? 'text-[#94a3b8]' : 'text-red-400')" x-text="aiMessage"></p>
                <p x-show="aiProvider" class="text-[10px] text-[#64748b] mt-0.5" x-text="aiProvider + ' — ' + aiModel"></p>
            </div>
        </div>
    </div>

    {{-- Mode Analisis --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
        {{-- Mode 1: Dengan AI --}}
        <label class="relative cursor-pointer" :class="aiStatus !== 'available' ? 'opacity-50 pointer-events-none' : ''">
            <input type="radio" name="mode_ai" value="dengan_ai" class="peer sr-only" x-model="modeAi" :disabled="aiStatus !== 'available'">
            <div class="rounded-xl border-2 p-4 transition-all duration-200 peer-checked:border-[#00d4ff] peer-checked:bg-[#00d4ff]/5 border-[#1e2d4a] hover:border-[#1e2d4a]/80">
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-7 h-7 rounded-lg bg-[#00d4ff]/10 border border-[#00d4ff]/25 flex items-center justify-center text-cyan-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h4 class="font-semibold text-sm text-white">Scan + AI</h4>
                </div>
                <p class="text-[11px] text-[#64748b] leading-relaxed">Analisis intens menggunakan script eksploitasi + RAG + AI untuk analisis mendalam.</p>
            </div>
        </label>

        {{-- Mode 2: Tanpa AI --}}
        <label class="relative cursor-pointer">
            <input type="radio" name="mode_ai" value="tanpa_ai" class="peer sr-only" x-model="modeAi">
            <div class="rounded-xl border-2 p-4 transition-all duration-200 peer-checked:border-yellow-500 peer-checked:bg-yellow-500/5 border-[#1e2d4a] hover:border-[#1e2d4a]/80">
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-7 h-7 rounded-lg bg-yellow-500/10 border border-yellow-500/25 flex items-center justify-center text-yellow-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h4 class="font-semibold text-sm text-white">Scan Tanpa AI</h4>
                </div>
                <p class="text-[11px] text-[#64748b] leading-relaxed">Analisis menggunakan script eksploitasi/parsing saja. Lebih cepat, tanpa AI.</p>
            </div>
        </label>
    </div>

    {{-- RAG Dataset Options (only if AI selected) --}}
    <div x-show="modeAi === 'dengan_ai'" x-transition class="space-y-4">
        
        {{-- Pilihan Model AI --}}
        <template x-if="availableModels.length > 0">
            <div>
                <p class="text-xs font-medium text-[#94a3b8] mb-2 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Model AI yang Digunakan
                </p>
                <select name="model_ai" x-model="selectedModel" class="w-full px-4 py-2.5 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/40 focus:outline-none transition-colors">
                    <template x-for="m in availableModels" :key="m.id">
                        <option :value="m.id" x-text="m.name + ' (' + m.provider + ')'" :selected="m.id === selectedModel"></option>
                    </template>
                </select>
            </div>
        </template>

        <div>
            <p class="text-xs font-medium text-[#94a3b8] mb-2 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Sumber Knowledge Base (RAG)
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="relative cursor-pointer">
                    <input type="radio" name="mode_rag" value="dengan_rag" class="peer sr-only" x-model="modeRag" checked>
                    <div class="rounded-xl border-2 p-3 transition-all duration-200 peer-checked:border-[#7c3aed] peer-checked:bg-[#7c3aed]/5 border-[#1e2d4a] hover:border-[#1e2d4a]/80">
                        <div class="flex items-center gap-2 mb-1.5">
                            <div class="w-5 h-5 rounded-md bg-[#7c3aed]/20 flex items-center justify-center text-[#7c3aed]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <h4 class="font-semibold text-xs text-white">Dengan RAG</h4>
                        </div>
                        <p class="text-[10px] text-[#64748b] leading-relaxed">Analisis menggunakan dataset terpercaya:</p>
                        <div class="mt-1.5 flex flex-wrap gap-1">
                            <span class="px-1.5 py-0.5 rounded text-[9px] bg-red-500/10 text-red-400 font-medium">NVD-CVE</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] bg-orange-500/10 text-orange-400 font-medium">MITRE ATT&CK</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] bg-blue-500/10 text-blue-400 font-medium">CAPEC</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] bg-purple-500/10 text-purple-400 font-medium">CWE</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] bg-green-500/10 text-green-400 font-medium">OWASP</span>
                        </div>
                    </div>
                </label>
                <label class="relative cursor-pointer">
                    <input type="radio" name="mode_rag" value="tanpa_rag" class="peer sr-only" x-model="modeRag">
                    <div class="rounded-xl border-2 p-3 transition-all duration-200 peer-checked:border-[#7c3aed] peer-checked:bg-[#7c3aed]/5 border-[#1e2d4a] hover:border-[#1e2d4a]/80">
                        <div class="flex items-center gap-2 mb-1.5">
                            <div class="w-5 h-5 rounded-md bg-slate-800 flex items-center justify-center text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h4 class="font-semibold text-xs text-white">Tanpa RAG</h4>
                        </div>
                        <p class="text-[10px] text-[#64748b] leading-relaxed">Analisis berdasarkan pengetahuan AI saja tanpa mengacu dataset lokal.</p>
                    </div>
                </label>
            </div>
        </div>
    </div>
</div>

<script>
function aiOptions() {
    return {
        aiStatus: 'checking',
        aiMessage: 'Memeriksa koneksi AI...',
        aiProvider: '',
        aiModel: '',
        modeAi: 'dengan_ai',
        modeRag: 'dengan_rag',
        availableModels: [],
        selectedModel: '',
        async checkAiHealth() {
            try {
                const r = await fetch('{{ route("api.ai-health") }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const d = await r.json();
                this.aiStatus = d.status;
                this.aiMessage = d.message;
                this.aiProvider = d.provider || '';
                this.aiModel = d.model || '';
                this.availableModels = d.available_models || [];
                this.selectedModel = d.model || '';
                if (d.status !== 'available') {
                    this.modeAi = 'tanpa_ai';
                }
            } catch (e) {
                this.aiStatus = 'error';
                this.aiMessage = 'Gagal memeriksa koneksi AI.';
                this.modeAi = 'tanpa_ai';
            }
        }
    }
}
</script>
