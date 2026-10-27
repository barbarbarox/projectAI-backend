    {{-- Floating Chat Widget --}}
    <div x-data="chatApp()" @open-ai-chat.window="isOpen = true" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
        
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
        
        {{-- Uiverse.io AI Button (Floating Trigger) --}}
        <style>
            /* ===== AI BUTTON UIVERSE STYLE ===== */
            .ai-chat-wrapper {
                --accent-1: #a855f7;
                --accent-2: #7c3aed;
                --accent-3: #c084fc;
                --accent-4: #8b5cf6;
                --light-1: rgba(124, 58, 237, 0.55);
                --light-2: rgba(0, 212, 255, 0.45);
                --duration: 2s;
                --transition: 3s infinite;

                position: relative;
                display: flex;
                justify-content: center;
                align-items: center;
                width: max-content;
                height: max-content;
            }

            /* Periodic idle animation every few seconds */
            .ai-chat-wrapper.idle-anim .ai-chat-bg {
                filter: drop-shadow(0px 0px 4px var(--accent-3));
            }
            .ai-chat-wrapper.idle-anim .ai-chat-line {
                animation: ai-dash var(--duration) ease-out infinite;
            }
            .ai-chat-wrapper.idle-anim .ai-chat-line:nth-child(odd) {
                animation-direction: normal;
                animation-delay: calc(var(--duration) * 0.1);
            }
            .ai-chat-wrapper.idle-anim .ai-chat-dot {
                animation: ai-dot-opac calc(var(--duration) * 0.5) linear infinite;
                fill: var(--accent-1);
            }
            .ai-chat-wrapper.idle-anim .ai-chat-dot:nth-child(odd) {
                animation-delay: calc(var(--duration) * 0.1);
            }
            .ai-chat-wrapper.idle-anim .ai-chat-light-1,
            .ai-chat-wrapper.idle-anim .ai-chat-light-2 {
                opacity: 0.7;
            }

            .ai-chat-light-1,
            .ai-chat-light-2 {
                pointer-events: none;
                position: absolute;
                top: -20px;
                left: -30px;
                width: 100px;
                height: 70px;
                border-radius: 50%;
                background-color: var(--light-1);
                filter: blur(27px);
                z-index: 1;
                transition: opacity var(--transition) ease, top var(--transition) ease, left var(--transition) ease;
                opacity: 0.4;
            }
            .ai-chat-light-2 {
                top: auto;
                bottom: -20px;
                left: auto;
                right: -20px;
                width: 80px;
                height: 40px;
                background-color: var(--light-2);
                transition-duration: calc(var(--transition) * 2);
                z-index: -1;
                opacity: 0.25;
            }

            .ai-chat-bg {
                position: absolute;
                z-index: -1;
                fill: none;
                transition: filter var(--transition) ease;
            }

            .ai-chat-line-bg {
                stroke: rgba(255,255,255,0.25);
                stroke-width: 0.3;
            }

            .ai-chat-line {
                stroke: none;
                stroke-width: 0;
                stroke-dasharray: 39 10;
                transition: stroke var(--transition) ease-in-out;
            }

            .ai-chat-dot {
                fill: #a855f7;
                opacity: 0.4;
                transition: fill var(--transition) ease;
            }

            .ai-chat-btn {
                width: max-content;
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background-color: #0a0e1a;
                background-image: url(data:image/png;base64,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),
                    linear-gradient(to bottom, #1e1040, #0a0e1a);
                color: #c4b5fd;
                border: 2px solid #4c1d95;
                font-size: 9px;
                font-weight: 600;
                font-family: "Inter", sans-serif;
                letter-spacing: 0.5px;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow:
                    0 0 30px -12px var(--light-1),
                    inset -1px -1px 1px 1px #000,
                    inset -2px -2px 2px 1px rgba(255,255,255,0.1),
                    inset 1px 1px 1px 1px #000,
                    inset 2px 2px 2px 1px rgba(255,255,255,0.1);
                position: relative;
                overflow: hidden;
            }

            .ai-chat-btn .txt-1 {
                position: absolute;
                opacity: 1;
                transition: opacity 0.5s ease;
                text-align: center;
                line-height: 1.2;
            }

            .ai-chat-btn .txt-2 {
                position: absolute;
                opacity: 0;
                transition: opacity 0.8s ease;
                text-align: center;
                line-height: 1.2;
            }

            /* HOVER STATE */
            .ai-chat-btn:hover {
                color: #e9d5ff;
                border-color: #7c3aed;
                text-shadow: 0 0 10px var(--light-1);
                box-shadow:
                    0 0 20px rgba(124,58,237,0.5),
                    inset -1px -1px 1px 1px #000,
                    inset 1px 1px 1px 1px #000;
                transform: scale(1.05);
            }
            .ai-chat-wrapper:has(.ai-chat-btn:hover) .ai-chat-dot {
                fill: #c4b5fd;
                opacity: 0.8;
            }
            .ai-chat-wrapper:has(.ai-chat-btn:hover) .ai-chat-light-1,
            .ai-chat-wrapper:has(.ai-chat-btn:hover) .ai-chat-light-2 {
                opacity: 0.7;
            }
            .ai-chat-wrapper:has(.ai-chat-btn:hover) .ai-chat-light-1 {
                top: -30px;
                left: -40px;
            }
            .ai-chat-wrapper:has(.ai-chat-btn:hover) .ai-chat-light-2 {
                bottom: -30px;
                right: -30px;
            }

            /* ACTIVE/OPEN STATE (chat is open = isActive) */
            .ai-chat-btn.is-active {
                background-color: #120b2e;
                border-color: #7c3aed;
                animation: ai-pulse var(--duration) ease-in-out infinite;
                color: #e9d5ff;
            }
            .ai-chat-btn.is-active .txt-1 { opacity: 0; }
            .ai-chat-btn.is-active .txt-2 { opacity: 1; animation: ai-pulse-text var(--duration) ease-in-out infinite; }
            .ai-chat-wrapper:has(.ai-chat-btn.is-active) .ai-chat-dot {
                animation: ai-dot-opac calc(var(--duration) * 0.5) linear infinite;
                fill: var(--accent-1);
            }
            .ai-chat-wrapper:has(.ai-chat-btn.is-active) .ai-chat-dot:nth-child(odd) {
                animation-delay: calc(var(--duration) * 0.1);
            }
            .ai-chat-wrapper:has(.ai-chat-btn.is-active) .ai-chat-line {
                animation: ai-dash var(--duration) ease-out infinite;
            }
            .ai-chat-wrapper:has(.ai-chat-btn.is-active) .ai-chat-line:nth-child(odd) {
                animation-direction: normal;
                animation-delay: calc(var(--duration) * 0.1);
            }
            .ai-chat-wrapper:has(.ai-chat-btn.is-active) .ai-chat-bg {
                filter: drop-shadow(0px 0px 6px var(--accent-3));
            }
            .ai-chat-wrapper:has(.ai-chat-btn.is-active) .ai-chat-light-1,
            .ai-chat-wrapper:has(.ai-chat-btn.is-active) .ai-chat-light-2 {
                opacity: 0.8;
            }

            /* Notification badge */
            .ai-notif-badge {
                position: absolute;
                top: -4px;
                right: -4px;
                width: 14px;
                height: 14px;
                border-radius: 50%;
                background: #ef4444;
                border: 2px solid #0a0e1a;
            }

            @keyframes ai-pulse-text {
                0%, 100% { text-shadow: 0 0 2px #0000; }
                50% { text-shadow: 0 0 16px var(--accent-3); }
            }
            @keyframes ai-dot-opac {
                0%, 100% { opacity: 0.1; }
                20% { opacity: 1; }
            }
            @keyframes ai-pulse {
                0%, 100% {
                    box-shadow: 0 0 20px var(--light-2), inset -1px -1px 1px 1px #000, inset 1px 1px 1px 1px #000;
                    border-color: #0000;
                }
                50% {
                    box-shadow: 0 0 60px 0px var(--light-1), inset -1px -1px 1px 1px #000, inset 1px 1px 1px 1px #000;
                }
                85% { border-color: var(--accent-2); }
            }
            @keyframes ai-dash {
                0% { stroke-width: 0; stroke-dashoffset: 10; stroke: var(--accent-4); }
                50% { stroke-width: 0.5; stroke-dashoffset: 88; stroke: var(--accent-2); }
                100% { stroke-width: 0; stroke-dashoffset: 166; stroke: var(--accent-4); }
            }
        </style>

        {{-- AI Button with Uiverse style --}}
        <div class="ai-chat-wrapper" id="ai-chat-wrapper">
            <div class="ai-chat-light-1"></div>
            <div class="ai-chat-light-2"></div>

            <button class="ai-chat-btn"
                    :class="{ 'is-active': isOpen || isLoading }"
                    @click="isOpen = !isOpen"
                    id="ai-float-btn"
                    title="Chat dengan AI">
                {{-- Close icon when open --}}
                <span class="txt-1" x-show="!isOpen" style="font-size:8px;display:flex;flex-direction:column;align-items:center;gap:1px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="opacity:0.9;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    AI
                </span>
                <span class="txt-2" style="font-size:7px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 1px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Tutup
                </span>
                <div class="ai-notif-badge" x-show="!isOpen"></div>
            </button>

            {{-- Uiverse SVG Background --}}
            <svg class="ai-chat-bg" xmlns="http://www.w3.org/2000/svg" viewBox="70 70 160 160" width="300" height="300">
                {{-- Background lines --}}
                <line class="ai-chat-line-bg" x1="150" y1="143.58" x2="150" y2="97.31"></line>
                <line class="ai-chat-line-bg" x1="157.98" y1="143.58" x2="157.98" y2="112.08"></line>
                <line class="ai-chat-line-bg" x1="142.02" y1="143.58" x2="142.02" y2="118.08"></line>
                <line class="ai-chat-line-bg" x1="130.05" y1="143.58" x2="130.05" y2="133.79"></line>
                <line class="ai-chat-line-bg" x1="138.03" y1="143.58" x2="138.03" y2="131.83"></line>
                <line class="ai-chat-line-bg" x1="146.01" y1="143.58" x2="146.01" y2="129.15"></line>
                <line class="ai-chat-line-bg" x1="153.99" y1="143.58" x2="153.99" y2="129.15"></line>
                <line class="ai-chat-line-bg" x1="161.97" y1="143.58" x2="161.97" y2="131.83"></line>
                <line class="ai-chat-line-bg" x1="169.95" y1="143.58" x2="169.95" y2="133.79"></line>
                <path class="ai-chat-line-bg" d="M126.06,143.58v-10.37c0-.72-.29-1.41-.8-1.92l-4.28-4.28c-.51-.51-.8-1.2-.8-1.92v-12.37"></path>
                <path class="ai-chat-line-bg" d="M165.96,143.58v-18.37c0-.72.29-1.41.8-1.92l4.28-4.28c.51-.51.8-1.2.8-1.92v-14.37"></path>
                <path class="ai-chat-line-bg" d="M173.94,143.58v-10.37c0-.72.29-1.41.8-1.92l4.28-4.28c.51-.51.8-1.2.8-1.92v-10.69"></path>
                <path class="ai-chat-line-bg" d="M134.04,143.58v-20.37c0-.72-.29-1.41-.8-1.92l-4.28-4.28c-.51-.51-.8-1.2-.8-1.92v-9.37"></path>
                <line class="ai-chat-line-bg" x1="176.04" y1="150" x2="217.32" y2="150"></line>
                <path class="ai-chat-line-bg" d="M176.04,153.99h13.37c.72,0,1.41.29,1.92.8l4.28,4.28c.51.51,1.2.8,1.92.8h14.37"></path>
                <path class="ai-chat-line-bg" d="M176.04,146.01h15.37c.72,0,1.41-.29,1.92-.8l4.28-4.28c.51-.51,1.2-.8,1.92-.8h9.37"></path>
                <line class="ai-chat-line-bg" x1="123.96" y1="150" x2="82.68" y2="150"></line>
                <path class="ai-chat-line-bg" d="M123.96,146.01h-13.37c-.72,0-1.41-.29-1.92-.8l-4.28-4.28c-.51-.51-1.2-.8-1.92-.8h-14.37"></path>
                <path class="ai-chat-line-bg" d="M123.96,153.99h-15.37c-.72,0-1.41.29-1.92.8l-4.28,4.28c-.51.51-1.2.8-1.92.8h-9.37"></path>
                <line class="ai-chat-line-bg" x1="150" y1="156.42" x2="150" y2="202.69"></line>
                <line class="ai-chat-line-bg" x1="142.02" y1="156.42" x2="142.02" y2="187.92"></line>
                <line class="ai-chat-line-bg" x1="157.98" y1="156.42" x2="157.98" y2="181.92"></line>
                <line class="ai-chat-line-bg" x1="169.95" y1="156.42" x2="169.95" y2="166.21"></line>
                <line class="ai-chat-line-bg" x1="161.97" y1="156.42" x2="161.97" y2="168.17"></line>
                <line class="ai-chat-line-bg" x1="153.99" y1="156.42" x2="153.99" y2="170.85"></line>
                <line class="ai-chat-line-bg" x1="146.01" y1="156.42" x2="146.01" y2="170.85"></line>
                <line class="ai-chat-line-bg" x1="138.03" y1="156.42" x2="138.03" y2="168.17"></line>
                <line class="ai-chat-line-bg" x1="130.05" y1="156.42" x2="130.05" y2="166.21"></line>
                <path class="ai-chat-line-bg" d="M173.94,156.42v10.37c0,.72.29,1.41.8,1.92l4.28,4.28c.51.51.8,1.2.8,1.92v12.37"></path>
                <path class="ai-chat-line-bg" d="M134.04,156.42v18.37c0,.72-.29,1.41-.8,1.92l-4.28,4.28c-.51.51-.8,1.2-.8,1.92v14.37"></path>
                <path class="ai-chat-line-bg" d="M126.06,156.42v10.37c0,.72-.29,1.41-.8,1.92l-4.28,4.28c-.51.51-.8,1.2-.8,1.92v10.69"></path>
                <path class="ai-chat-line-bg" d="M165.96,156.42v20.37c0,.72.29,1.41.8,1.92l4.28,4.28c.51.51.8,1.2.8,1.92v9.37"></path>

                {{-- Animated lines --}}
                <line class="ai-chat-line" x1="150" y1="143.58" x2="150" y2="97.31"></line>
                <line class="ai-chat-line" x1="157.98" y1="143.58" x2="157.98" y2="112.08"></line>
                <line class="ai-chat-line" x1="142.02" y1="143.58" x2="142.02" y2="118.08"></line>
                <line class="ai-chat-line" x1="130.05" y1="143.58" x2="130.05" y2="133.79"></line>
                <line class="ai-chat-line" x1="138.03" y1="143.58" x2="138.03" y2="131.83"></line>
                <line class="ai-chat-line" x1="146.01" y1="143.58" x2="146.01" y2="129.15"></line>
                <line class="ai-chat-line" x1="153.99" y1="143.58" x2="153.99" y2="129.15"></line>
                <line class="ai-chat-line" x1="161.97" y1="143.58" x2="161.97" y2="131.83"></line>
                <line class="ai-chat-line" x1="169.95" y1="143.58" x2="169.95" y2="133.79"></line>
                <path class="ai-chat-line" d="M126.06,143.58v-10.37c0-.72-.29-1.41-.8-1.92l-4.28-4.28c-.51-.51-.8-1.2-.8-1.92v-12.37"></path>
                <path class="ai-chat-line" d="M165.96,143.58v-18.37c0-.72.29-1.41.8-1.92l4.28-4.28c.51-.51.8-1.2.8-1.92v-14.37"></path>
                <path class="ai-chat-line" d="M173.94,143.58v-10.37c0-.72.29-1.41.8-1.92l4.28-4.28c.51-.51.8-1.2.8-1.92v-10.69"></path>
                <path class="ai-chat-line" d="M134.04,143.58v-20.37c0-.72-.29-1.41-.8-1.92l-4.28-4.28c-.51-.51-.8-1.2-.8-1.92v-9.37"></path>
                <line class="ai-chat-line" x1="176.04" y1="150" x2="217.32" y2="150"></line>
                <path class="ai-chat-line" d="M176.04,153.99h13.37c.72,0,1.41.29,1.92.8l4.28,4.28c.51.51,1.2.8,1.92.8h14.37"></path>
                <path class="ai-chat-line" d="M176.04,146.01h15.37c.72,0,1.41-.29,1.92-.8l4.28-4.28c.51-.51,1.2-.8,1.92-.8h9.37"></path>
                <line class="ai-chat-line" x1="123.96" y1="150" x2="82.68" y2="150"></line>
                <path class="ai-chat-line" d="M123.96,146.01h-13.37c-.72,0-1.41-.29-1.92-.8l-4.28-4.28c-.51-.51-1.2-.8-1.92-.8h-14.37"></path>
                <path class="ai-chat-line" d="M123.96,153.99h-15.37c-.72,0-1.41.29-1.92.8l-4.28,4.28c-.51.51-1.2.8-1.92.8h-9.37"></path>
                <line class="ai-chat-line" x1="150" y1="156.42" x2="150" y2="202.69"></line>
                <line class="ai-chat-line" x1="142.02" y1="156.42" x2="142.02" y2="187.92"></line>
                <line class="ai-chat-line" x1="157.98" y1="156.42" x2="157.98" y2="181.92"></line>
                <line class="ai-chat-line" x1="169.95" y1="156.42" x2="169.95" y2="166.21"></line>
                <line class="ai-chat-line" x1="161.97" y1="156.42" x2="161.97" y2="168.17"></line>
                <line class="ai-chat-line" x1="153.99" y1="156.42" x2="153.99" y2="170.85"></line>
                <line class="ai-chat-line" x1="146.01" y1="156.42" x2="146.01" y2="170.85"></line>
                <line class="ai-chat-line" x1="138.03" y1="156.42" x2="138.03" y2="168.17"></line>
                <line class="ai-chat-line" x1="130.05" y1="156.42" x2="130.05" y2="166.21"></line>
                <path class="ai-chat-line" d="M173.94,156.42v10.37c0,.72.29,1.41.8,1.92l4.28,4.28c.51.51.8,1.2.8,1.92v12.37"></path>
                <path class="ai-chat-line" d="M134.04,156.42v18.37c0,.72-.29,1.41-.8,1.92l-4.28,4.28c-.51.51-.8,1.2-.8,1.92v14.37"></path>
                <path class="ai-chat-line" d="M126.06,156.42v10.37c0,.72-.29,1.41-.8,1.92l-4.28,4.28c-.51.51-.8,1.2-.8,1.92v10.69"></path>
                <path class="ai-chat-line" d="M165.96,156.42v20.37c0,.72.29,1.41.8,1.92l4.28,4.28c.51.51.8,1.2.8,1.92v9.37"></path>

                {{-- Dots --}}
                <circle class="ai-chat-dot" cx="150" cy="96.13" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="157.98" cy="110.91" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="142.02" cy="116.91" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="157.98" cy="183.09" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="171.83" cy="101.54" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="179.81" cy="113.23" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="128.17" cy="104.54" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="120.19" cy="111.54" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="150" cy="203.87" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="142.02" cy="189.09" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="128.17" cy="198.46" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="120.19" cy="186.77" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="171.83" cy="195.46" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="179.81" cy="188.46" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="210.08" cy="140.14" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="218.49" cy="150" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="213.08" cy="159.86" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="89.92" cy="159.86" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="81.51" cy="150" r="1.17"></circle>
                <circle class="ai-chat-dot" cx="86.92" cy="140.14" r="1.17"></circle>
            </svg>
        </div>

        <script>
            // Periodic idle animation — fires every 6 seconds for 2.5 seconds
            (function() {
                const wrapper = document.getElementById('ai-chat-wrapper');
                const btn = document.getElementById('ai-float-btn');
                if (!wrapper || !btn) return;

                function triggerIdle() {
                    // Only fire idle anim when chat is closed and not loading
                    if (!btn.classList.contains('is-active')) {
                        wrapper.classList.add('idle-anim');
                        setTimeout(() => {
                            wrapper.classList.remove('idle-anim');
                        }, 2500);
                    }
                }

                // First idle after 4 seconds, then repeat every 8 seconds
                setTimeout(() => {
                    triggerIdle();
                    setInterval(triggerIdle, 8000);
                }, 4000);
            })();
        </script>
    </div>
