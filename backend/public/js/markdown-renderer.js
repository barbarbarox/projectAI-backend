(function() {
    'use strict';

    function initMarkdownRenderer() {
        if (typeof marked === 'undefined' || typeof hljs === 'undefined' || typeof DOMPurify === 'undefined') {
            setTimeout(initMarkdownRenderer, 50);
            return;
        }

        var renderer = new marked.Renderer();

        renderer.table = function(header, body) {
            return '<div class="md-table-wrap"><table><thead>' + header + '</thead><tbody>' + body + '</tbody></table></div>';
        };
        renderer.tablerow = function(content) { return '<tr>' + content + '</tr>'; };
        renderer.tablecell = function(content, flags) {
            var tag = flags.header ? 'th' : 'td';
            var align = flags.align ? ' style="text-align:' + flags.align + '"' : '';
            return '<' + tag + align + '>' + content + '</' + tag + '>';
        };

        renderer.code = function(code, infostring, escaped) {
            var lang = (infostring || '').trim().split(/\s+/)[0] || '';
            var label = lang || 'code';
            var highlighted = code;
            if (lang && hljs.getLanguage(lang)) {
                try { highlighted = hljs.highlight(code, { language: lang }).value; } catch(e) { highlighted = escapeHtml(code); }
            } else {
                try { highlighted = hljs.highlightAuto(code).value; } catch(e) { highlighted = escapeHtml(code); }
            }
            return '<div class="md-code-block"><div class="md-code-header"><span class="md-code-lang">' + escapeHtml(label) + '</span>' +
                '<button type="button" class="md-copy-btn" data-code="' + encodeURIComponent(code) + '">' +
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>' +
                '<span>Salin</span></button></div><pre><code class="hljs">' + highlighted + '</code></pre></div>';
        };
        renderer.codespan = function(text) { return '<code>' + text + '</code>'; };

        marked.setOptions({ renderer: renderer, gfm: true, breaks: true, pedantic: false, smartypants: false });

        var purifyConfig = {
            ADD_TAGS: ['svg', 'path', 'rect', 'details', 'summary'],
            ADD_ATTR: ['viewBox', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'd', 'x', 'y', 'width', 'height', 'rx', 'ry', 'data-code', 'class', 'type', 'checked', 'disabled'],
            FORBID_ATTR: ['onerror', 'onload', 'onclick', 'onmouseover'],
            ALLOW_DATA_ATTR: true
        };

        window.MarkdownRenderer = {
            parse: function(text) {
                if (!text) return '';
                var result = '';
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
        console.log('[RedSim] Markdown renderer initialized');
    }

    function escapeHtml(str) { return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.md-copy-btn');
        if (!btn) return;
        var encoded = btn.getAttribute('data-code');
        if (!encoded) return;
        navigator.clipboard.writeText(decodeURIComponent(encoded)).then(function() {
            var spanEl = btn.querySelector('span');
            if (spanEl) { spanEl.textContent = 'Tersalin!'; btn.style.color = '#3fb950'; btn.style.borderColor = '#3fb950'; setTimeout(function() { spanEl.textContent = 'Salin'; btn.style.color = ''; btn.style.borderColor = ''; }, 2000); }
        });
    });

    initMarkdownRenderer();
})();
