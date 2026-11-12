@extends('layouts.app')
@section('title', 'Uji Coba AI & RAG — Admin RedSim')
@section('header', 'Uji Coba AI & RAG')
@section('subheader', 'Berkomunikasi dengan AI yang terhubung dengan basis pengetahuan RedSim')

@section('content')
<div class="flex flex-col h-[calc(100vh-12rem)] bg-[#0f1629] border border-[#1e2d4a] rounded-2xl overflow-hidden" x-data="chatApp()">
    
    {{-- Status Banner --}}
    <div class="bg-[#1e2d4a]/50 border-b border-[#1e2d4a] px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
            <span class="text-sm font-medium text-green-400">Sistem AI Siap</span>
        </div>
        <div class="flex items-center gap-4">

            <div class="text-xs text-[#94a3b8] flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Mode RAG Aktif
            </div>
        </div>
    </div>

    {{-- Chat History --}}
    <div class="flex-1 overflow-y-auto p-6 space-y-6" id="chat-container">
        {{-- Welcome Message --}}
        <div class="flex gap-4">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#00d4ff] to-[#7c3aed] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="bg-[#1e2d4a]/30 border border-[#1e2d4a] rounded-2xl rounded-tl-sm px-5 py-4 max-w-[80%]">
                <p class="text-sm leading-relaxed text-[#e2e8f0]">
                    Halo! Saya adalah AI Assistant RedSim. Saya terhubung dengan basis pengetahuan keamanan sistem. Silakan tanyakan apapun terkait keamanan, kerentanan, atau status sistem.
                </p>
            </div>
        </div>

        {{-- Dynamic Messages --}}
        <template x-for="(msg, index) in messages" :key="index">
            <div class="flex gap-4" :class="msg.role === 'user' ? 'flex-row-reverse' : ''">
                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                     :class="msg.role === 'user' ? 'bg-[#1e2d4a]' : 'bg-gradient-to-br from-[#00d4ff] to-[#7c3aed]'">
                    <template x-if="msg.role === 'user'">
                        <span class="text-sm font-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </template>
                    <template x-if="msg.role === 'ai'">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </template>
                </div>
                <div class="rounded-2xl px-5 py-4 max-w-[80%] break-words"
                     :class="msg.role === 'user' ? 'bg-[#00d4ff]/10 border border-[#00d4ff]/20 text-white rounded-tr-sm' : 'bg-[#1e2d4a]/30 border border-[#1e2d4a] text-[#e2e8f0] rounded-tl-sm'">
                    
                    <div class="text-sm leading-relaxed md-content" x-html="formatMessage(msg.content, msg.role)"></div>
                    
                    <template x-if="msg.role === 'ai' && msg.ragUsed">
                        <div class="mt-3 pt-3 border-t border-[#1e2d4a]/50 text-xs text-[#00d4ff] flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Referensi RAG Digunakan
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- Loading indicator --}}
        <div class="flex gap-4" x-show="isLoading" style="display: none;">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#00d4ff] to-[#7c3aed] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <div class="bg-[#1e2d4a]/30 border border-[#1e2d4a] rounded-2xl rounded-tl-sm px-5 py-4">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-[#00d4ff] animate-bounce" style="animation-delay: 0s"></div>
                    <div class="w-2 h-2 rounded-full bg-[#00d4ff] animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="w-2 h-2 rounded-full bg-[#00d4ff] animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Input Area --}}
    <div class="p-4 bg-[#0a0e1a] border-t border-[#1e2d4a]">
        <form @submit.prevent="sendMessage" class="relative">
            <input type="text" x-model="input" placeholder="Tanyakan sesuatu..." :disabled="isLoading"
                   class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl pl-4 pr-12 py-3.5 text-sm text-white focus:outline-none focus:border-[#00d4ff]/50 focus:ring-1 focus:ring-[#00d4ff]/50 transition-all disabled:opacity-50">
            
            <button type="submit" :disabled="!input.trim() || isLoading"
                    class="absolute right-2 top-2 p-1.5 rounded-lg bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
        </form>
        <div class="mt-3 flex flex-col sm:flex-row items-center justify-between gap-3">
            <select x-model="selectedModel" class="px-3 py-1.5 rounded-lg bg-[#0f1629] border border-[#1e2d4a] text-xs text-[#94a3b8] hover:text-white focus:text-white focus:border-[#00d4ff] focus:outline-none transition-colors max-w-full sm:max-w-[300px] w-full sm:w-auto">
                <option value="">{{ $defaultModelName ?? 'Gunakan Model Default' }}</option>
                @foreach($availableModels as $m)
                    <option value="{{ $m['id'] }}">{{ $m['name'] }} ({{ $m['provider'] }})</option>
                @endforeach
            </select>
            <span class="text-[10px] text-[#64748b]">AI mungkin memberikan jawaban yang tidak akurat. Verifikasi info penting.</span>
        </div>
    </div>
</div>

@push('scripts')
{{-- Pinned versions for API stability --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/marked/9.1.6/marked.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>

<style>
/* ===== Markdown Content Styling ===== */
.md-content { color: #e2e8f0; word-wrap: break-word; overflow-wrap: break-word; }
.md-content > *:first-child { margin-top: 0; }
.md-content > *:last-child { margin-bottom: 0; }

/* Paragraphs */
.md-content p { margin-bottom: 0.875rem; line-height: 1.7; }
.md-content p:last-child { margin-bottom: 0; }

/* Bold & Italic */
.md-content strong { font-weight: 700; color: #fff; }
.md-content em { font-style: italic; color: #cbd5e1; }

/* Headings */
.md-content h1 { font-size: 1.5rem; font-weight: 800; margin: 1.5rem 0 0.75rem; color: #fff; line-height: 1.3; }
.md-content h2 { font-size: 1.3rem; font-weight: 700; margin: 1.25rem 0 0.625rem; color: #fff; border-bottom: 1px solid #1e2d4a; padding-bottom: 0.4rem; }
.md-content h3 { font-size: 1.125rem; font-weight: 700; margin: 1rem 0 0.5rem; color: #fff; }
.md-content h4 { font-size: 1rem; font-weight: 600; margin: 0.875rem 0 0.375rem; color: #e2e8f0; }

/* Lists */
.md-content ul { list-style: disc; padding-left: 1.5rem; margin: 0.5rem 0 1rem; }
.md-content ol { list-style: decimal; padding-left: 1.5rem; margin: 0.5rem 0 1rem; }
.md-content li { margin-bottom: 0.35rem; line-height: 1.6; }
.md-content li > ul, .md-content li > ol { margin-top: 0.25rem; margin-bottom: 0; }
.md-content li > p { margin-bottom: 0.25rem; }

/* Links */
.md-content a { color: #00d4ff; text-decoration: underline; text-underline-offset: 2px; }
.md-content a:hover { color: #38bdf8; }

/* Images */
.md-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 1rem 0; border: 1px solid #1e2d4a; }

/* Blockquote */
.md-content blockquote { border-left: 3px solid #00d4ff; margin: 1rem 0; padding: 0.625rem 1rem; background: rgba(0,212,255,0.05); border-radius: 0 0.375rem 0.375rem 0; color: #94a3b8; }
.md-content blockquote p { margin-bottom: 0.25rem; }
.md-content blockquote p:last-child { margin-bottom: 0; }

/* Horizontal Rule */
.md-content hr { border: none; border-top: 1px solid #334155; margin: 1.5rem 0; }

/* Inline Code */
.md-content code:not(pre code) {
    background: #1e293b; padding: 0.15em 0.4em; border-radius: 4px;
    font-family: 'Fira Code', ui-monospace, SFMono-Regular, monospace;
    font-size: 0.85em; color: #7dd3fc; border: 1px solid #334155;
}

/* Code Block: reset code inside pre */
.md-content pre code {
    background: none !important; border: none !important; padding: 0 !important;
    color: inherit !important; font-size: 0.8125rem !important; line-height: 1.6;
    font-family: 'Fira Code', ui-monospace, SFMono-Regular, monospace;
}
.md-content pre {
    background: #0d1117; margin: 0; padding: 1rem 1.25rem; overflow-x: auto;
    border-radius: 0 0 0.625rem 0.625rem;
}

/* Code Block Wrapper */
.md-code-block { margin: 1rem 0; border-radius: 0.625rem; overflow: hidden; border: 1px solid #30363d; background: #0d1117; }
.md-code-header {
    display: flex; align-items: center; justify-content: space-between;
    background: #161b22; padding: 0.5rem 1rem; border-bottom: 1px solid #30363d;
}
.md-code-lang { font-size: 0.6875rem; color: #8b949e; font-family: monospace; text-transform: uppercase; letter-spacing: 0.05em; }
.md-copy-btn {
    display: flex; align-items: center; gap: 0.375rem; font-size: 0.6875rem;
    color: #8b949e; background: none; border: 1px solid #30363d; border-radius: 0.375rem;
    padding: 0.25rem 0.625rem; cursor: pointer; transition: all 0.2s;
}
.md-copy-btn:hover { color: #e6edf3; border-color: #8b949e; background: #21262d; }

/* ===== Table Styling ===== */
.md-table-wrap { overflow-x: auto; margin: 1rem 0; border-radius: 0.625rem; border: 1px solid #30363d; }
.md-content table { width: 100%; border-collapse: collapse; min-width: 400px; }
.md-content thead { background: #161b22; }
.md-content th {
    padding: 0.75rem 1rem; font-weight: 600; color: #e6edf3; text-align: left;
    font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;
    border-bottom: 2px solid #30363d; white-space: nowrap;
}
.md-content td { padding: 0.625rem 1rem; color: #c9d1d9; border-bottom: 1px solid #21262d; font-size: 0.8125rem; }
.md-content tbody tr:hover { background: rgba(177,186,196,0.04); }
.md-content tbody tr:last-child td { border-bottom: none; }

/* Checkbox */
.md-content input[type="checkbox"] { margin-right: 0.5em; accent-color: #00d4ff; pointer-events: none; }

/* Think block */
.md-think { margin-bottom: 1rem; border: 1px solid #334155; border-radius: 0.625rem; overflow: hidden; }
.md-think summary { padding: 0.5rem 1rem; background: #161b22; cursor: pointer; font-size: 0.8125rem; color: #8b949e; display: flex; align-items: center; gap: 0.5rem; }
.md-think summary:hover { background: #1e2d4a; }
.md-think-body { padding: 1rem; font-size: 0.8125rem; color: #8b949e; border-top: 1px solid #334155; background: rgba(13,17,23,0.5); }
</style>

<script>
(function() {
    'use strict';

    // ---- Wait for all libs to be ready ----
    function initMarkdownRenderer() {
        if (typeof marked === 'undefined' || typeof hljs === 'undefined' || typeof DOMPurify === 'undefined') {
            setTimeout(initMarkdownRenderer, 50);
            return;
        }

        // ---- Custom Renderer (marked v9 API) ----
        const renderer = new marked.Renderer();

        // Table: wrap in scrollable container
        renderer.table = function(header, body) {
            return '<div class="md-table-wrap"><table><thead>' + header + '</thead><tbody>' + body + '</tbody></table></div>';
        };

        renderer.tablerow = function(content) {
            return '<tr>' + content + '</tr>';
        };

        renderer.tablecell = function(content, flags) {
            var tag = flags.header ? 'th' : 'td';
            var align = flags.align ? ' style="text-align:' + flags.align + '"' : '';
            return '<' + tag + align + '>' + content + '</' + tag + '>';
        };

        // Code block: styled with copy button
        renderer.code = function(code, infostring, escaped) {
            var lang = (infostring || '').trim().split(/\s+/)[0] || '';
            var label = lang || 'code';
            var highlighted = code;

            if (lang && hljs.getLanguage(lang)) {
                try { highlighted = hljs.highlight(code, { language: lang }).value; }
                catch (e) { highlighted = escapeHtml(code); }
            } else {
                try { highlighted = hljs.highlightAuto(code).value; }
                catch (e) { highlighted = escapeHtml(code); }
            }

            var raw = escapeHtml(code);

            return '<div class="md-code-block">' +
                '<div class="md-code-header">' +
                    '<span class="md-code-lang">' + escapeHtml(label) + '</span>' +
                    '<button type="button" class="md-copy-btn" data-code="' + encodeURIComponent(code) + '">' +
                        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>' +
                        '<span>Salin</span>' +
                    '</button>' +
                '</div>' +
                '<pre><code class="hljs">' + highlighted + '</code></pre>' +
            '</div>';
        };

        // Inline code
        renderer.codespan = function(text) {
            return '<code>' + text + '</code>';
        };

        // Configure marked
        marked.setOptions({
            renderer: renderer,
            gfm: true,
            breaks: true,
            pedantic: false,
            smartypants: false
        });

        // ---- DOMPurify config ----
        var purifyConfig = {
            ADD_TAGS: ['svg', 'path', 'rect', 'details', 'summary'],
            ADD_ATTR: ['viewBox', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'd', 'x', 'y', 'width', 'height', 'rx', 'ry', 'data-code', 'class', 'type', 'checked', 'disabled'],
            FORBID_ATTR: ['onerror', 'onload', 'onclick', 'onmouseover'],
            ALLOW_DATA_ATTR: true
        };

        // ---- Build the renderer API ----
        window.MarkdownRenderer = {
            parse: function(text) {
                if (!text) return '';

                var result = '';

                // Extract <think> blocks
                text = text.replace(/<think>([\s\S]*?)<\/think>/g, function(m, thought) {
                    var inner = '';
                    try { inner = DOMPurify.sanitize(marked.parse(thought.trim()), purifyConfig); }
                    catch(e) { inner = escapeHtml(thought); }
                    result += '<details class="md-think"><summary>' +
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>' +
                        'Alur Berpikir AI</summary>' +
                        '<div class="md-think-body md-content">' + inner + '</div></details>';
                    return '';
                });

                // Parse remaining markdown
                try {
                    var html = marked.parse(text);
                    html = DOMPurify.sanitize(html, purifyConfig);
                    result += html;
                } catch(e) {
                    console.error('Markdown parse error:', e);
                    result += '<p>' + escapeHtml(text) + '</p>';
                }

                return result;
            }
        };

        console.log('[RedSim] Markdown renderer initialized');
    }

    function escapeHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ---- Copy Code via Event Delegation ----
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.md-copy-btn');
        if (!btn) return;
        var encoded = btn.getAttribute('data-code');
        if (!encoded) return;
        var code = decodeURIComponent(encoded);
        navigator.clipboard.writeText(code).then(function() {
            var spanEl = btn.querySelector('span');
            if (spanEl) {
                spanEl.textContent = 'Tersalin!';
                btn.style.color = '#3fb950';
                btn.style.borderColor = '#3fb950';
                setTimeout(function() {
                    spanEl.textContent = 'Salin';
                    btn.style.color = '';
                    btn.style.borderColor = '';
                }, 2000);
            }
        });
    });

    // Start init
    initMarkdownRenderer();
})();

function chatApp() {
    return {
        messages: [],
        input: '',
        isLoading: false,
        selectedModel: '',
        
        formatMessage(text, role) {
            if (role === 'user') return this.escapeHtml(text);
            if (window.MarkdownRenderer) return window.MarkdownRenderer.parse(text);
            return this.escapeHtml(text);
        },

        escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
        },

        scrollToBottom() {
            setTimeout(() => {
                const container = document.getElementById('chat-container');
                container.scrollTop = container.scrollHeight;
            }, 50);
        },

        async sendMessage() {
            if (!this.input.trim() || this.isLoading) return;

            const userMsg = this.input.trim();
            this.messages.push({ role: 'user', content: userMsg });
            this.input = '';
            this.isLoading = true;
            this.scrollToBottom();

            try {
                const response = await fetch('{{ route('chat-ai.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        message: userMsg,
                        model_ai: this.selectedModel
                    })
                });

                const data = await response.json();
                
                if (data.status === 'success') {
                    this.messages.push({ 
                        role: 'ai', 
                        content: data.message,
                        ragUsed: data.rag_used
                    });
                } else {
                    this.messages.push({ 
                        role: 'ai', 
                        content: 'Maaf, terjadi kesalahan: ' + (data.message || 'Unknown error'),
                        ragUsed: false
                    });
                }
            } catch (error) {
                this.messages.push({ 
                    role: 'ai', 
                    content: 'Gagal terhubung ke server.',
                    ragUsed: false
                });
            } finally {
                this.isLoading = false;
                this.scrollToBottom();
            }
        }
    }
}
</script>
@endpush
@endsection
