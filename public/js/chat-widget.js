function chatApp() {
    return {
        isOpen: false,
        messages: [],
        input: '',
        isLoading: false,
        selectedModel: '',
        
        init() {
            // Optional: Listen for keyboard shortcut or events to open
        },
        
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
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
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
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const response = await fetch('/chat-ai', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: userMsg, model_ai: this.selectedModel })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    this.messages.push({ role: 'ai', content: data.message, ragUsed: data.rag_used });
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
