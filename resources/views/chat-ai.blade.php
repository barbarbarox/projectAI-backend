@extends('layouts.app')
@section('title', 'AI Chat — RedSim')
@section('header', 'AI Security Assistant')
@section('subheader', 'Berkomunikasi dengan AI yang terhubung dengan basis pengetahuan keamanan RedSim')
@section('page_feature', 'chat-ai')

@section('content')
<div class="flex flex-col h-[calc(100vh-12rem)] rounded-xl overflow-hidden" style="background:#0f172a;border:1px solid #1e293b;" x-data="chatApp()">
    
    {{-- Status Banner --}}
    <div class="px-5 py-3 flex items-center justify-between" style="background:rgba(6,182,212,0.05);border-bottom:1px solid #1e293b;">
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></div>
            <span class="text-sm font-semibold" style="color:#22d3ee;">AI Security Assistant</span>
            <span class="badge-ai">RAG Aktif</span>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-xs flex items-center gap-1.5" style="color:#64748b;">
                <svg class="w-3.5 h-3.5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Terhubung ke basis pengetahuan keamanan
            </div>
        </div>
    </div>

    {{-- Chat History --}}
    <div class="flex-1 overflow-y-auto p-6 space-y-6" id="chat-container">
        {{-- Welcome Message --}}
        <div class="flex gap-4">
            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style="background:linear-gradient(135deg,#06b6d4,#3b82f6);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="rounded-2xl rounded-tl-sm px-5 py-4 max-w-[82%]" style="background:rgba(6,182,212,0.06);border:1px solid rgba(6,182,212,0.15);">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-semibold" style="color:#22d3ee;">RedSim AI</span>
                    <span class="badge-ai">AI</span>
                </div>
                <p class="text-sm leading-relaxed" style="color:#e2e8f0;">
                    Halo! Saya adalah AI Assistant RedSim. Saya terhubung dengan basis pengetahuan keamanan sistem. Silakan tanyakan apapun terkait keamanan, kerentanan, atau hasil pemindaian.
                </p>
            </div>
        </div>

        {{-- Dynamic Messages --}}
        <template x-for="(msg, index) in messages" :key="index">
            <div class="flex gap-4" :class="msg.role === 'user' ? 'flex-row-reverse' : ''">
                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                     :style="msg.role === 'user' ? 'background:#0f172a;border:1px solid #1e293b;' : 'background:linear-gradient(135deg,#06b6d4,#3b82f6);'">
                    <template x-if="msg.role === 'user'">
                        <span class="text-sm font-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </template>
                    <template x-if="msg.role === 'ai'">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </template>
                </div>
                <div class="rounded-2xl px-5 py-4 max-w-[92%] sm:max-w-[85%] break-words"
                     :style="msg.role === 'user' ? 'background:rgba(220,38,38,0.07);border:1px solid rgba(220,38,38,0.2);color:#f1f5f9;border-radius:1rem 0.25rem 1rem 1rem;' : 'background:rgba(6,182,212,0.06);border:1px solid rgba(6,182,212,0.15);color:#e2e8f0;border-radius:0.25rem 1rem 1rem 1rem;'">
                    
                    <div class="text-sm leading-relaxed md-content" x-html="formatMessage(msg.content, msg.role)"></div>
                    
                    <template x-if="msg.role === 'ai' && msg.ragUsed">
                        <div class="mt-3 pt-2.5 flex flex-wrap items-center gap-1.5" style="border-top:1px solid rgba(6,182,212,0.15);">
                            <div class="flex items-center gap-1 text-[11px] font-semibold text-cyan-400">
                                <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Dual RAG:</span>
                            </div>

                            <template x-if="msg.ragDetails && msg.ragDetails.general_count > 0">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium bg-emerald-500/10 text-emerald-300 border border-emerald-500/30">
                                    <svg class="w-3 h-3 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <span>RAG Umum (<span x-text="msg.ragDetails.general_count"></span> chunk)</span>
                                </span>
                            </template>

                            <template x-if="msg.ragDetails && msg.ragDetails.nvd_count > 0">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-medium bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">
                                    <svg class="w-3 h-3 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    <span>NVD CVE: <span x-text="msg.ragDetails.nvd_cves.join(', ')"></span></span>
                                </span>
                            </template>

                            <template x-if="!msg.ragDetails">
                                <span class="text-[10px] font-semibold text-cyan-400">Referensi RAG Digunakan</span>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- Loading indicator --}}
        <div class="flex gap-4" x-show="isLoading" style="display: none;">
            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style="background:linear-gradient(135deg,#06b6d4,#3b82f6);">
                <svg class="w-5 h-5 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <div class="rounded-2xl rounded-tl-sm px-5 py-4" style="background:rgba(6,182,212,0.06);border:1px solid rgba(6,182,212,0.15);">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full animate-bounce" style="background:#06b6d4;animation-delay:0s"></div>
                    <div class="w-2 h-2 rounded-full animate-bounce" style="background:#06b6d4;animation-delay:0.18s"></div>
                    <div class="w-2 h-2 rounded-full animate-bounce" style="background:#06b6d4;animation-delay:0.36s"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Input Area --}}
    <div class="p-4" style="background:#020609;border-top:1px solid #1e293b;">
        <form @submit.prevent="sendMessage" class="relative">
            <input type="text" x-model="input"
                   placeholder="Tanyakan tentang keamanan, kerentanan, atau hasil scan..."
                   :disabled="isLoading"
                   class="w-full pl-4 pr-12 py-3.5 text-sm text-white placeholder-slate-600 rounded-xl transition-all duration-200 disabled:opacity-50"
                   style="background:#000;border:1px solid #1e293b;font-family:'JetBrains Mono',monospace;outline:none;"
                   onfocus="this.style.borderColor='rgba(6,182,212,0.5)';this.style.boxShadow='0 0 0 3px rgba(6,182,212,0.1)'"
                   onblur="this.style.borderColor='#1e293b';this.style.boxShadow='none'">
            <button type="submit" :disabled="!input.trim() || isLoading"
                    class="absolute right-2 top-2 p-2 rounded-lg text-white transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed"
                    style="background:linear-gradient(135deg,#06b6d4,#3b82f6);"
                    :style="(input.trim() && !isLoading) ? 'box-shadow:0 0 10px rgba(6,182,212,0.35);' : ''">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </form>
        <div class="mt-3 flex flex-col sm:flex-row items-center justify-between gap-3">
            <select x-model="selectedModel"
                    class="px-3 py-1.5 rounded-lg text-xs transition-colors max-w-full sm:max-w-[320px] w-full sm:w-auto text-slate-300 font-mono"
                    style="background:#0a0e1a;border:1px solid #1e2d4a;outline:none;"
                    onfocus="this.style.borderColor='rgba(6,182,212,0.6)';this.style.boxShadow='0 0 0 2px rgba(6,182,212,0.15)'"
                    onblur="this.style.borderColor='#1e2d4a';this.style.boxShadow='none'">
                <option value="">{{ $defaultModelName ?? 'Gunakan Model Default' }}</option>
                @foreach($availableModels as $m)
                    <option value="{{ $m['id'] }}">{{ $m['name'] }} ({{ $m['provider'] }})</option>
                @endforeach
            </select>
            <span class="text-[10px] flex items-center gap-1" style="color:#475569;">
                <svg class="w-3 h-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                AI mungkin memberikan jawaban yang tidak akurat. Verifikasi info penting.
            </span>
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
.md-content { color: #e2e8f0; word-wrap: break-word; overflow-wrap: break-word; font-size: 0.9125rem; line-height: 1.7; }
.md-content > *:first-child { margin-top: 0 !important; }
.md-content > *:last-child { margin-bottom: 0 !important; }
.md-content p { margin-bottom: 0.85rem; line-height: 1.7; color: #e2e8f0; }

/* Bold, Italic, Underline, Strikethrough */
.md-content strong, .md-content b { font-weight: 700 !important; color: #ffffff !important; }
.md-content em, .md-content i { font-style: italic !important; color: #94a3b8 !important; }
.md-content u, .md-content ins { text-decoration: underline !important; text-underline-offset: 3px !important; text-decoration-color: #38bdf8 !important; color: #f1f5f9 !important; }
.md-content s, .md-content del { text-decoration: line-through !important; color: #64748b !important; }

/* Headings */
.md-content h1 { font-size: 1.45rem; font-weight: 800; margin: 1.4rem 0 0.75rem; color: #ffffff; line-height: 1.3; border-bottom: 1px solid #1e293b; padding-bottom: 0.4rem; }
.md-content h2 { font-size: 1.25rem; font-weight: 700; margin: 1.25rem 0 0.625rem; color: #ffffff; border-bottom: 1px solid #1e293b; padding-bottom: 0.35rem; }
.md-content h3 { font-size: 1.1rem; font-weight: 700; margin: 1rem 0 0.5rem; color: #38bdf8; }
.md-content h4 { font-size: 0.95rem; font-weight: 600; margin: 0.875rem 0 0.375rem; color: #e2e8f0; }
.md-content h5, .md-content h6 { font-size: 0.875rem; font-weight: 600; color: #94a3b8; margin: 0.75rem 0 0.25rem; }

/* Lists with clear bullet points and numbers */
.md-content ul { list-style-type: disc !important; padding-left: 1.5rem !important; margin: 0.75rem 0 1rem !important; }
.md-content ol { list-style-type: decimal !important; padding-left: 1.5rem !important; margin: 0.75rem 0 1rem !important; }
.md-content li { display: list-item !important; margin-bottom: 0.4rem !important; line-height: 1.65 !important; color: #e2e8f0; }
.md-content li::marker { color: #38bdf8 !important; font-weight: 700; }
.md-content li > ul { list-style-type: circle !important; margin-top: 0.3rem !important; margin-bottom: 0.3rem !important; }
.md-content li > ol { list-style-type: lower-alpha !important; margin-top: 0.3rem !important; margin-bottom: 0.3rem !important; }
.md-content li > p { margin-bottom: 0.3rem !important; }

/* Links */
.md-content a { color: #22d3ee !important; text-decoration: underline !important; text-underline-offset: 2px !important; }
.md-content a:hover { color: #38bdf8 !important; }

/* Media & Blockquote */
.md-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 1rem 0; border: 1px solid #1e2d4a; }
.md-content blockquote {
    border-left: 3px solid #06b6d4 !important;
    margin: 1rem 0 !important;
    padding: 0.75rem 1.1rem !important;
    background: rgba(6, 182, 212, 0.08) !important;
    border-radius: 0 0.5rem 0.5rem 0 !important;
    color: #cbd5e1 !important;
    border-top: 1px solid rgba(6, 182, 212, 0.15) !important;
    border-bottom: 1px solid rgba(6, 182, 212, 0.15) !important;
    border-right: 1px solid rgba(6, 182, 212, 0.15) !important;
}
.md-content blockquote p { margin-bottom: 0.25rem !important; }
.md-content blockquote p:last-child { margin-bottom: 0 !important; }
.md-content hr { border: none !important; border-top: 1px solid #26364b !important; margin: 1.5rem 0 !important; }

/* Inline Code */
.md-content code:not(pre code) {
    background: #111a2e !important;
    color: #38bdf8 !important;
    border: 1px solid #26364b !important;
    padding: 0.18em 0.45em !important;
    border-radius: 0.375rem !important;
    font-family: 'JetBrains Mono', ui-monospace, monospace !important;
    font-size: 0.85em !important;
    word-break: break-word;
}

/* Code Blocks & Code Box */
.md-code-block {
    margin: 1.25rem 0 !important;
    border-radius: 0.75rem !important;
    overflow: hidden !important;
    border: 1px solid #26364b !important;
    background: #080c16 !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4) !important;
}
.md-code-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    background: #0f172a !important;
    padding: 0.5rem 1rem !important;
    border-bottom: 1px solid #1e293b !important;
}
.md-code-lang {
    font-size: 0.6875rem !important;
    font-weight: 600 !important;
    color: #38bdf8 !important;
    font-family: 'JetBrains Mono', monospace !important;
    text-transform: uppercase !important;
    letter-spacing: 0.08em !important;
}
.md-copy-btn {
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.375rem !important;
    font-size: 0.6875rem !important;
    font-weight: 500 !important;
    color: #94a3b8 !important;
    background: rgba(30, 41, 59, 0.7) !important;
    border: 1px solid #334155 !important;
    border-radius: 0.375rem !important;
    padding: 0.25rem 0.625rem !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
}
.md-copy-btn:hover {
    color: #ffffff !important;
    border-color: #38bdf8 !important;
    background: rgba(56, 189, 248, 0.15) !important;
}
.md-content pre {
    background: #080c16 !important;
    margin: 0 !important;
    padding: 1rem 1.25rem !important;
    overflow-x: auto !important;
    border-radius: 0 0 0.75rem 0.75rem !important;
}
.md-content pre code {
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
    font-size: 0.8125rem !important;
    line-height: 1.65 !important;
    font-family: 'JetBrains Mono', ui-monospace, monospace !important;
}

/* Tables Styling with Clear Borders & Headers */
.md-table-wrap {
    overflow-x: auto !important;
    margin: 1.25rem 0 !important;
    border-radius: 0.75rem !important;
    border: 1px solid #26364b !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3) !important;
    background: #090e18 !important;
}
.md-content table {
    width: 100% !important;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    font-size: 0.8125rem !important;
    text-align: left !important;
    border: 1px solid #26364b !important;
    min-width: 450px !important;
}
.md-content thead {
    background: #111a2e !important;
}
.md-content th {
    padding: 0.75rem 1rem !important;
    font-weight: 700 !important;
    color: #38bdf8 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    font-size: 0.75rem !important;
    border: 1px solid #26364b !important;
    white-space: nowrap !important;
}
.md-content td {
    padding: 0.75rem 1rem !important;
    color: #cbd5e1 !important;
    border: 1px solid #1e293b !important;
    line-height: 1.6 !important;
    vertical-align: top !important;
}
.md-content tbody tr:nth-child(even) {
    background: rgba(17, 26, 46, 0.45) !important;
}
.md-content tbody tr:nth-child(odd) {
    background: rgba(8, 14, 24, 0.65) !important;
}
.md-content tbody tr:hover {
    background: rgba(56, 189, 248, 0.09) !important;
}

/* Think details */
.md-think { margin-bottom: 1rem; border: 1px solid #334155; border-radius: 0.625rem; overflow: hidden; }
.md-think summary { padding: 0.5rem 1rem; background: #161b22; cursor: pointer; font-size: 0.8125rem; color: #8b949e; display: flex; align-items: center; gap: 0.5rem; }
.md-think summary:hover { background: #1e2d4a; }
.md-think-body { padding: 1rem; font-size: 0.8125rem; color: #8b949e; border-top: 1px solid #334155; background: rgba(13,17,23,0.5); }
</style>

<script>
(function() {
    'use strict';

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function buildCodeBlock(code, lang) {
        var label = (lang || 'code').trim();
        var highlighted = escapeHtml(code);
        if (typeof hljs !== 'undefined') {
            try {
                if (label && label !== 'code' && hljs.getLanguage(label)) {
                    highlighted = hljs.highlight(code, { language: label }).value;
                } else {
                    highlighted = hljs.highlightAuto(code).value;
                }
            } catch(e) { highlighted = escapeHtml(code); }
        }
        return '<div class="md-code-block"><div class="md-code-header"><span class="md-code-lang">' + escapeHtml(label) + '</span>' +
            '<button type="button" class="md-copy-btn" data-code="' + encodeURIComponent(code) + '">' +
            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>' +
            '<span>Salin</span></button></div><pre><code class="hljs">' + highlighted + '</code></pre></div>';
    }

    function initMarkdownRenderer() {
        if (typeof marked === 'undefined' || typeof DOMPurify === 'undefined') {
            setTimeout(initMarkdownRenderer, 50);
            return;
        }

        // Deteksi versi marked: v9+ menggunakan token object, sebelumnya string params
        var markedVersion = (marked.marked && marked.marked.version) || marked.version || '0';
        var majorVersion = parseInt(markedVersion.split('.')[0], 10) || 0;

        // marked.use() tersedia sejak v1.1.0 dan merupakan cara paling aman di semua versi
        marked.use({
            gfm: true,
            breaks: true,
            renderer: {
                // code: dipanggil dengan (token) di v9+, atau (code, lang, escaped) di versi lama
                code: function(tokenOrCode, langOrUndefined, escaped) {
                    var code, lang;
                    if (tokenOrCode && typeof tokenOrCode === 'object') {
                        // marked v9+ — token object
                        code = tokenOrCode.text || '';
                        lang = tokenOrCode.lang || '';
                    } else {
                        // marked v1-v8 — string params
                        code = tokenOrCode || '';
                        lang = langOrUndefined || '';
                    }
                    return buildCodeBlock(code, lang);
                },

                // codespan: inline code
                codespan: function(tokenOrCode) {
                    var text = (tokenOrCode && typeof tokenOrCode === 'object') ? (tokenOrCode.text || '') : (tokenOrCode || '');
                    return '<code>' + text + '</code>';
                },

                // table
                table: function(tokenOrHeader, bodyOrUndefined) {
                    if (tokenOrHeader && typeof tokenOrHeader === 'object' && tokenOrHeader.header) {
                        // marked v9+ token
                        var token = tokenOrHeader;
                        var theadCells = token.header.map(function(cell) {
                            var align = cell.align ? ' style="text-align:' + cell.align + '"' : '';
                            return '<th' + align + '>' + cell.text + '</th>';
                        }).join('');
                        var thead = '<tr>' + theadCells + '</tr>';
                        var tbody = token.rows.map(function(row) {
                            var cells = row.map(function(cell, i) {
                                var align = (token.header[i] && token.header[i].align) ? ' style="text-align:' + token.header[i].align + '"' : '';
                                return '<td' + align + '>' + cell.text + '</td>';
                            }).join('');
                            return '<tr>' + cells + '</tr>';
                        }).join('');
                        return '<div class="md-table-wrap"><table><thead>' + thead + '</thead><tbody>' + tbody + '</tbody></table></div>';
                    }
                    // marked v8 dan lebih lama — string params
                    return '<div class="md-table-wrap"><table><thead>' + (tokenOrHeader || '') + '</thead><tbody>' + (bodyOrUndefined || '') + '</tbody></table></div>';
                },

                // tablerow (hanya dipanggil di versi lama)
                tablerow: function(content) {
                    return '<tr>' + content + '</tr>';
                },

                // tablecell (hanya dipanggil di versi lama)
                tablecell: function(content, flags) {
                    if (typeof content === 'object') return ''; // skip jika token object (v9 tidak memanggil ini)
                    var tag = (flags && flags.header) ? 'th' : 'td';
                    var align = (flags && flags.align) ? ' style="text-align:' + flags.align + '"' : '';
                    return '<' + tag + align + '>' + content + '</' + tag + '>';
                }
            }
        });

        var purifyConfig = {
            ADD_TAGS: ['svg', 'path', 'rect', 'circle', 'details', 'summary', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'u', 'ins', 's', 'del', 'mark'],
            ADD_ATTR: ['viewBox', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'd', 'cx', 'cy', 'r', 'x', 'y', 'width', 'height', 'rx', 'ry', 'data-code', 'class', 'type', 'checked', 'disabled', 'style'],
            FORBID_ATTR: ['onerror', 'onload', 'onclick', 'onmouseover'],
            ALLOW_DATA_ATTR: true
        };

        window.MarkdownRenderer = {
            parse: function(text) {
                if (!text) return '';
                var result = '';
                // Tangani blok <think>...</think> (untuk model reasoning)
                text = text.replace(/<think>([\s\S]*?)<\/think>/g, function(m, thought) {
                    var inner = '';
                    try { inner = DOMPurify.sanitize(marked.parse(thought.trim()), purifyConfig); } catch(e) { inner = escapeHtml(thought); }
                    result += '<details class="md-think"><summary><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>Alur Berpikir AI</summary><div class="md-think-body md-content">' + inner + '</div></details>';
                    return '';
                });
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
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.md-copy-btn');
        if (!btn) return;
        var encoded = btn.getAttribute('data-code');
        if (!encoded) return;
        navigator.clipboard.writeText(decodeURIComponent(encoded)).then(function() {
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
        }).catch(function() {
            // Fallback jika clipboard API tidak tersedia
            var spanEl = btn.querySelector('span');
            if (spanEl) { spanEl.textContent = 'Gagal!'; setTimeout(function() { spanEl.textContent = 'Salin'; }, 1500); }
        });
    });

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
            if (window.MarkdownRenderer && typeof window.MarkdownRenderer.parse === 'function') {
                return window.MarkdownRenderer.parse(text);
            }
            if (typeof marked !== 'undefined') {
                try {
                    var html = marked.parse(text);
                    if (typeof DOMPurify !== 'undefined') {
                        html = DOMPurify.sanitize(html);
                    }
                    return html;
                } catch(e) {}
            }
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
                    body: JSON.stringify({ message: userMsg, model_ai: this.selectedModel })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    this.messages.push({
                        role: 'ai',
                        content: data.message,
                        ragUsed: data.rag_used,
                        ragDetails: data.rag_details || null
                    });
                } else {
                    this.messages.push({ role: 'ai', content: 'Maaf, terjadi kesalahan: ' + (data.message || 'Unknown error'), ragUsed: false });
                }
            } catch (error) {
                this.messages.push({ role: 'ai', content: 'Gagal terhubung ke server.', ragUsed: false });
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
