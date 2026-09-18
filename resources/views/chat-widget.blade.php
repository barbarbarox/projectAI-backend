    {{-- Floating Chat Widget --}}
    <div x-data="chatApp()" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
        
        {{-- Chat Popup --}}
        <div x-show="isOpen" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-10 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-10 scale-95"
             class="mb-4 w-[400px] h-[550px] max-h-[80vh] max-w-[calc(100vw-3rem)] bg-[#0f1629] border border-[#1e2d4a] rounded-2xl shadow-2xl flex flex-col overflow-hidden" style="display: none;">
            
            {{-- Header --}}
            <div class="bg-[#1e2d4a]/50 border-b border-[#1e2d4a] px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="text-sm font-medium text-green-400">AI Assistant</span>
                </div>
                <button @click="isOpen = false" class="text-[#94a3b8] hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Chat History --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-container">
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#00d4ff] to-[#7c3aed] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="bg-[#1e2d4a]/30 border border-[#1e2d4a] rounded-2xl rounded-tl-sm px-4 py-3 max-w-[85%]">
                        <p class="text-xs leading-relaxed text-[#e2e8f0]">
                            Halo! Saya adalah AI Assistant RedSim. Saya terhubung dengan basis pengetahuan keamanan sistem. Silakan tanyakan apapun terkait keamanan, kerentanan, atau status sistem.
                        </p>
                    </div>
                </div>

                <template x-for="(msg, index) in messages" :key="index">
                    <div class="flex gap-3" :class="msg.role === 'user' ? 'flex-row-reverse' : ''">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                             :class="msg.role === 'user' ? 'bg-[#1e2d4a]' : 'bg-gradient-to-br from-[#00d4ff] to-[#7c3aed]'">
                            <template x-if="msg.role === 'user'">
                                <span class="text-xs font-bold">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                            </template>
                            <template x-if="msg.role === 'ai'">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </template>
                        </div>
                        <div class="rounded-2xl px-4 py-3 max-w-[85%] break-words overflow-x-auto"
                             :class="msg.role === 'user' ? 'bg-[#00d4ff]/10 border border-[#00d4ff]/20 text-white rounded-tr-sm' : 'bg-[#1e2d4a]/30 border border-[#1e2d4a] text-[#e2e8f0] rounded-tl-sm'">
                            <div class="text-xs leading-relaxed md-content chat-md-content" x-html="formatMessage(msg.content, msg.role)"></div>
                            <template x-if="msg.role === 'ai' && msg.ragUsed">
                                <div class="mt-2 pt-2 border-t border-[#1e2d4a]/50 text-[10px] text-[#00d4ff] flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Referensi RAG Digunakan
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <div class="flex gap-3" x-show="isLoading" style="display: none;">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#00d4ff] to-[#7c3aed] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div class="bg-[#1e2d4a]/30 border border-[#1e2d4a] rounded-2xl rounded-tl-sm px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-[#00d4ff] animate-bounce" style="animation-delay: 0s"></div>
                            <div class="w-1.5 h-1.5 rounded-full bg-[#00d4ff] animate-bounce" style="animation-delay: 0.2s"></div>
                            <div class="w-1.5 h-1.5 rounded-full bg-[#00d4ff] animate-bounce" style="animation-delay: 0.4s"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Input Area --}}
            <div class="p-3 bg-[#0a0e1a] border-t border-[#1e2d4a]">
                <form @submit.prevent="sendMessage" class="relative">
                    <input type="text" x-model="input" placeholder="Tanyakan sesuatu..." :disabled="isLoading"
                           class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl pl-3 pr-10 py-2.5 text-xs text-white focus:outline-none focus:border-[#00d4ff]/50 focus:ring-1 focus:ring-[#00d4ff]/50 transition-all disabled:opacity-50">
                    <button type="submit" :disabled="!input.trim() || isLoading"
                            class="absolute right-1.5 top-1.5 p-1 rounded-lg bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
                <div class="mt-2">
                    <select x-model="selectedModel" class="w-full px-2 py-1 rounded-lg bg-[#0f1629] border border-[#1e2d4a] text-[10px] text-[#94a3b8] hover:text-white focus:text-white focus:border-[#00d4ff] focus:outline-none transition-colors">
                        <option value="">{{ $defaultModelName ?? 'Gunakan Model Default' }}</option>
                        @if(isset($availableModels))
                            @foreach($availableModels as $m)
                                <option value="{{ $m['id'] }}">{{ $m['name'] }} ({{ $m['provider'] }})</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>
        
        {{-- Floating Trigger Button --}}
        <button @click="isOpen = !isOpen" 
                class="w-14 h-14 rounded-full bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white shadow-[0_0_20px_rgba(0,212,255,0.3)] hover:shadow-[0_0_25px_rgba(124,58,237,0.5)] transition-all hover:scale-105 flex items-center justify-center relative">
            <svg x-show="!isOpen" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <svg x-show="isOpen" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <div x-show="!isOpen" class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-red-500 border-2 border-[#0a0e1a]"></div>
        </button>
    </div>
