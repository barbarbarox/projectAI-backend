{{-- ═══════════════════════════════════════════════════
     RESIZABLE CHAT AI SIDEBAR — RIGHT PANEL
     Like VS Code / Antigravity sidebar. Opens from right,
     resizable via drag handle, page content shifts.
════════════════════════════════════════════════════ --}}

<style>
/* ── Chat Sidebar Container ── */
#ai-sidebar {
    position: fixed;
    top: 0;
    right: 0;
    height: 100vh;
    width: 380px;
    min-width: 280px;
    max-width: 720px;
    background: #080c18;
    border-left: 1px solid #1e293b;
    display: flex;
    flex-direction: column;
    z-index: 45;
    transform: translateX(100%);
    transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.28s ease;
    box-shadow: none;
    will-change: transform;
}
#ai-sidebar.is-open {
    transform: translateX(0);
    box-shadow: -8px 0 32px rgba(0,0,0,0.5);
}

/* ── Resize Handle ── */
#ai-resize-handle {
    position: absolute;
    left: -4px;
    top: 0;
    width: 8px;
    height: 100%;
    cursor: ew-resize;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}
#ai-sidebar:hover #ai-resize-handle,
#ai-resize-handle.dragging {
    opacity: 1;
}
#ai-resize-handle::after {
    content: '';
    display: block;
    width: 2px;
    height: 48px;
    border-radius: 99px;
    background: linear-gradient(180deg, #00d4ff, #7c3aed);
    opacity: 0.7;
}
#ai-resize-handle:hover::after {
    height: 64px;
    opacity: 1;
}

/* ── Sidebar Header ── */
#ai-sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 14px;
    height: 48px;
    border-bottom: 1px solid #1e293b;
    background: rgba(15,22,40,0.9);
    backdrop-filter: blur(12px);
    flex-shrink: 0;
}

/* ── Chat Body ── */
#ai-chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    scroll-behavior: smooth;
}
#ai-chat-body::-webkit-scrollbar { width: 4px; }
#ai-chat-body::-webkit-scrollbar-track { background: transparent; }
#ai-chat-body::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
#ai-chat-body::-webkit-scrollbar-thumb:hover { background: #334155; }

/* ── Chat Input Footer ── */
#ai-chat-footer {
    flex-shrink: 0;
    padding: 10px 12px;
    border-top: 1px solid #1e293b;
    background: rgba(8,12,24,0.95);
}

/* ── Floating Trigger Button ── */
#ai-trigger-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 44;
    width: 50px;
    height: 50px;
    border-radius: 14px;
    background: linear-gradient(135deg, #7c3aed, #00d4ff);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(124,58,237,0.4);
    transition: transform 0.2s, box-shadow 0.2s, right 0.28s cubic-bezier(0.4,0,0.2,1);
    outline: none;
}
#ai-trigger-btn:hover {
    transform: scale(1.08);
    box-shadow: 0 6px 28px rgba(124,58,237,0.55);
}
#ai-trigger-btn.sidebar-open {
    right: calc(var(--sidebar-w, 380px) + 12px);
}

/* ── Notification dot ── */
.ai-notif-dot {
    position: absolute;
    top: -3px;
    right: -3px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #ef4444;
    border: 2px solid #020609;
    animation: pulse-notif 2s ease-in-out infinite;
}
@keyframes pulse-notif {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.3); opacity: 0.7; }
}

/* ── Message Bubbles ── */
.ai-msg-row { display: flex; gap: 8px; align-items: flex-start; }
.ai-msg-row.user { flex-direction: row-reverse; }
.ai-avatar {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 11px;
    font-weight: 700;
}
.ai-bubble {
    max-width: 85%;
    padding: 8px 12px;
    border-radius: 12px;
    font-size: 12px;
    line-height: 1.6;
    word-break: break-word;
    overflow-wrap: break-word;
}
.ai-bubble.from-ai {
    background: rgba(30,45,74,0.5);
    border: 1px solid #1e2d4a;
    border-top-left-radius: 3px;
    color: #e2e8f0;
}
.ai-bubble.from-user {
    background: rgba(0,212,255,0.08);
    border: 1px solid rgba(0,212,255,0.2);
    border-top-right-radius: 3px;
    color: #fff;
}

/* ── Typing Indicator ── */
.typing-dots span {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #00d4ff;
    animation: typing-bounce 1.2s ease-in-out infinite;
}
.typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing-bounce {
    0%, 80%, 100% { transform: translateY(0); opacity: 0.4; }
    40% { transform: translateY(-5px); opacity: 1; }
}

/* ── RAG Badge ── */
.rag-badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 10px;
    color: #00d4ff;
    margin-top: 4px;
    padding-top: 4px;
    border-top: 1px solid rgba(30,45,74,0.6);
}

/* ── Mobile Overlay ── */
#ai-mobile-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(3px);
    z-index: 44;
}
@media (max-width: 768px) {
    #ai-sidebar { width: 100vw !important; max-width: 100vw; }
    #ai-sidebar.is-open ~ #ai-mobile-overlay,
    #ai-mobile-overlay.visible { display: block; }
    #ai-trigger-btn.sidebar-open { right: 12px; }
    #ai-resize-handle { display: none; }
}
</style>

{{-- Mobile Overlay --}}
<div id="ai-mobile-overlay" onclick="aiSidebar.close()"></div>

{{-- ═══ SIDEBAR PANEL ═══ --}}
<div id="ai-sidebar" role="complementary" aria-label="AI Assistant">

    {{-- Resize Handle (drag from left edge) --}}
    <div id="ai-resize-handle" aria-hidden="true"></div>

    {{-- Header --}}
    <div id="ai-sidebar-header">
        <div style="display:flex;align-items:center;gap:8px;">
            <div style="width:8px;height:8px;border-radius:50%;background:#4ade80;animation:pulse-notif 2s infinite;flex-shrink:0;"></div>
            <span style="font-size:13px;font-weight:600;color:#fff;">AI Assistant</span>
            <span style="font-size:10px;color:#475569;font-family:'JetBrains Mono',monospace;" id="ai-model-badge">—</span>
        </div>
        <div style="display:flex;align-items:center;gap:6px;">
            {{-- Clear chat --}}
            <button onclick="aiSidebar.clearChat()"
                    title="Bersihkan percakapan"
                    style="background:none;border:none;cursor:pointer;color:#475569;padding:4px;border-radius:6px;transition:color 0.2s;"
                    onmouseover="this.style.color='#94a3b8'" onmouseout="this.style.color='#475569'">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
            {{-- Close --}}
            <button onclick="aiSidebar.close()"
                    title="Tutup"
                    style="background:none;border:none;cursor:pointer;color:#475569;padding:4px;border-radius:6px;transition:color 0.2s;"
                    onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='#475569'">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Chat Messages --}}
    <div id="ai-chat-body">
        {{-- Welcome message --}}
        <div class="ai-msg-row">
            <div class="ai-avatar" style="background:linear-gradient(135deg,#00d4ff,#7c3aed);">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="ai-bubble from-ai">
                Halo! Saya AI Assistant RedSim. Terhubung dengan basis pengetahuan keamanan sistem. Tanyakan apapun terkait keamanan, kerentanan, atau status sistem.
            </div>
        </div>

        {{-- Dynamic messages rendered by JS --}}
        <div id="ai-messages-container"></div>

        {{-- Typing Indicator --}}
        <div id="ai-typing-indicator" style="display:none;" class="ai-msg-row">
            <div class="ai-avatar" style="background:linear-gradient(135deg,#00d4ff,#7c3aed);">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="ai-bubble from-ai" style="padding:10px 14px;">
                <div class="typing-dots" style="display:flex;gap:4px;align-items:center;">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer: Input + Model Selector --}}
    <div id="ai-chat-footer">
        <form id="ai-chat-form" onsubmit="aiSidebar.sendMessage(event)" style="position:relative;margin-bottom:8px;">
            <textarea id="ai-chat-input"
                      placeholder="Tanyakan sesuatu... (Enter kirim, Shift+Enter baris baru)"
                      rows="1"
                      style="width:100%;box-sizing:border-box;background:#0f1629;border:1px solid #1e293b;border-radius:10px;padding:9px 40px 9px 12px;font-size:12px;color:#fff;resize:none;outline:none;line-height:1.5;font-family:inherit;transition:border-color 0.2s,max-height 0.2s;max-height:120px;overflow-y:auto;"
                      onfocus="this.style.borderColor='rgba(0,212,255,0.5)'"
                      onblur="this.style.borderColor='#1e293b'"
                      onkeydown="aiSidebar.handleKeydown(event)"
                      oninput="aiSidebar.autoResize(this)"></textarea>
            <button type="submit" id="ai-send-btn"
                    style="position:absolute;right:6px;bottom:6px;width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#00d4ff,#7c3aed);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:opacity 0.2s;"
                    title="Kirim">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </button>
        </form>
        <select id="ai-model-select"
                onchange="aiSidebar.changeModel(this.value)"
                style="width:100%;padding:5px 8px;border-radius:8px;background:#0f1629;border:1px solid #1e293b;font-size:10px;color:#64748b;cursor:pointer;transition:border-color 0.2s;outline:none;"
                onfocus="this.style.borderColor='rgba(0,212,255,0.4)'"
                onblur="this.style.borderColor='#1e293b'">
            <option value="">{{ $defaultModelName ?? 'Gunakan Model Default' }}</option>
            @if(isset($availableModels))
                @foreach($availableModels as $m)
                    <option value="{{ $m['id'] }}">{{ $m['name'] }} ({{ $m['provider'] }})</option>
                @endforeach
            @endif
        </select>
    </div>
</div>

{{-- ═══ FLOATING TRIGGER BUTTON ═══ --}}
<button id="ai-trigger-btn" onclick="aiSidebar.toggle()" title="Chat dengan AI Assistant" aria-label="Buka AI Chat">
    <svg id="ai-trigger-icon-open" width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#fff;">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
    </svg>
    <svg id="ai-trigger-icon-close" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#fff;display:none;">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    <span class="ai-notif-dot" id="ai-notif-dot"></span>
</button>

<script>
const aiSidebar = (function() {
    const sidebar = document.getElementById('ai-sidebar');
    const triggerBtn = document.getElementById('ai-trigger-btn');
    const iconOpen = document.getElementById('ai-trigger-icon-open');
    const iconClose = document.getElementById('ai-trigger-icon-close');
    const notifDot = document.getElementById('ai-notif-dot');
    const messagesContainer = document.getElementById('ai-messages-container');
    const chatBody = document.getElementById('ai-chat-body');
    const input = document.getElementById('ai-chat-input');
    const typingIndicator = document.getElementById('ai-typing-indicator');
    const modelSelect = document.getElementById('ai-model-select');
    const modelBadge = document.getElementById('ai-model-badge');
    const mobileOverlay = document.getElementById('ai-mobile-overlay');

    let isOpen = false;
    let isLoading = false;
    let messages = [];
    let selectedModel = '';
    let sidebarWidth = parseInt(localStorage.getItem('aiSidebarWidth') || '380');

    // Apply saved width
    sidebar.style.width = sidebarWidth + 'px';

    // Update CSS variable for trigger btn positioning
    function updateSidebarWidthVar(w) {
        document.documentElement.style.setProperty('--sidebar-w', w + 'px');
    }
    updateSidebarWidthVar(sidebarWidth);

    // ── Open / Close ──
    function open() {
        isOpen = true;
        sidebar.classList.add('is-open');
        triggerBtn.classList.add('sidebar-open');
        iconOpen.style.display = 'none';
        iconClose.style.display = 'block';
        notifDot.style.display = 'none';
        if (window.innerWidth <= 768) {
            mobileOverlay.classList.add('visible');
        }
        scrollToBottom();
    }

    function close() {
        isOpen = false;
        sidebar.classList.remove('is-open');
        triggerBtn.classList.remove('sidebar-open');
        iconOpen.style.display = 'block';
        iconClose.style.display = 'none';
        mobileOverlay.classList.remove('visible');
    }

    function toggle() { isOpen ? close() : open(); }

    // ── Resize Logic ──
    const handle = document.getElementById('ai-resize-handle');
    let resizing = false, startX = 0, startW = 0;

    handle.addEventListener('mousedown', (e) => {
        resizing = true;
        startX = e.clientX;
        startW = sidebar.offsetWidth;
        handle.classList.add('dragging');
        document.body.style.cursor = 'ew-resize';
        document.body.style.userSelect = 'none';
        e.preventDefault();
    });

    document.addEventListener('mousemove', (e) => {
        if (!resizing) return;
        const delta = startX - e.clientX;
        const newW = Math.max(280, Math.min(720, startW + delta));
        sidebar.style.width = newW + 'px';
        updateSidebarWidthVar(newW);
        if (triggerBtn.classList.contains('sidebar-open')) {
            triggerBtn.style.right = (newW + 12) + 'px';
        }
    });

    document.addEventListener('mouseup', () => {
        if (resizing) {
            resizing = false;
            handle.classList.remove('dragging');
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            sidebarWidth = sidebar.offsetWidth;
            localStorage.setItem('aiSidebarWidth', sidebarWidth);
        }
    });

    // ── Scroll ──
    function scrollToBottom() {
        setTimeout(() => { chatBody.scrollTop = chatBody.scrollHeight; }, 60);
    }

    // ── Render Message ──
    function renderMessage(role, content, ragUsed = false) {
        const userInitial = '{{ substr(Auth::user()->name ?? "U", 0, 1) }}';
        const row = document.createElement('div');
        row.className = `ai-msg-row ${role === 'user' ? 'user' : ''}`;

        const avatar = document.createElement('div');
        avatar.className = 'ai-avatar';
        if (role === 'user') {
            avatar.style.cssText = 'background:rgba(30,41,59,0.8);border:1px solid #1e293b;color:#94a3b8;';
            avatar.textContent = userInitial;
        } else {
            avatar.style.cssText = 'background:linear-gradient(135deg,#00d4ff,#7c3aed);color:#fff;';
            avatar.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>';
        }

        const bubble = document.createElement('div');
        bubble.className = `ai-bubble ${role === 'user' ? 'from-user' : 'from-ai'}`;

        if (role === 'ai' && typeof formatMessage === 'function') {
            bubble.innerHTML = formatMessage(content, 'ai');
            if (ragUsed) {
                const badge = document.createElement('div');
                badge.className = 'rag-badge';
                badge.innerHTML = '<svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Referensi RAG Digunakan';
                bubble.appendChild(badge);
            }
        } else {
            const p = document.createElement('div');
            p.className = 'chat-md-content';
            p.textContent = content;
            bubble.appendChild(p);
        }

        row.appendChild(avatar);
        row.appendChild(bubble);
        messagesContainer.appendChild(row);
        scrollToBottom();
    }

    // ── Auto Resize Textarea ──
    function autoResize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    }

    function handleKeydown(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('ai-chat-form').dispatchEvent(new Event('submit'));
        }
    }

    // ── Send Message ──
    async function sendMessage(e) {
        if (e) e.preventDefault();
        const text = input.value.trim();
        if (!text || isLoading) return;

        messages.push({ role: 'user', content: text });
        renderMessage('user', text);
        input.value = '';
        input.style.height = 'auto';

        isLoading = true;
        typingIndicator.style.display = 'flex';
        scrollToBottom();

        try {
            const resp = await fetch('/chat-ai', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: text, model_ai: selectedModel || null }),
            });

            const data = await resp.json();
            typingIndicator.style.display = 'none';

            if (data.status === 'success') {
                messages.push({ role: 'ai', content: data.message, ragUsed: data.rag_used });
                renderMessage('ai', data.message, data.rag_used);
            } else {
                renderMessage('ai', data.message || 'Terjadi kesalahan. Coba lagi.');
            }
        } catch (err) {
            typingIndicator.style.display = 'none';
            renderMessage('ai', 'Gagal terhubung ke server. Periksa koneksi Anda.');
        }
        isLoading = false;
    }

    // ── Model change ──
    function changeModel(modelId) {
        selectedModel = modelId;
        const opt = modelSelect.options[modelSelect.selectedIndex];
        modelBadge.textContent = opt ? opt.text.split(' (')[0].split(' ').slice(-2).join(' ') : '—';
    }

    // ── Clear Chat ──
    function clearChat() {
        messages = [];
        messagesContainer.innerHTML = '';
    }

    // ── Listen for open event from other pages ──
    window.addEventListener('open-ai-chat', () => open());

    // Init model badge
    if (modelSelect.options.length > 0) {
        const txt = modelSelect.options[0]?.text || '';
        modelBadge.textContent = txt.split(' (')[0].slice(-20) || '—';
    }

    return { open, close, toggle, sendMessage, clearChat, handleKeydown, autoResize, changeModel };
})();
</script>
