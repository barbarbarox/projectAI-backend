(function() {
    'use strict';

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#39;');
    }

    function buildCodeBlock(code, lang) {
        code = code || '';
        lang = (lang || '').trim().split(/\s+/)[0] || '';
        var label = lang || 'code';
        var highlighted = escapeHtml(code);

        if (typeof hljs !== 'undefined') {
            try {
                if (label && label !== 'code' && hljs.getLanguage(label)) {
                    highlighted = hljs.highlight(code, { language: label }).value;
                } else {
                    highlighted = hljs.highlightAuto(code).value;
                }
            } catch(e) {
                highlighted = escapeHtml(code);
            }
        }

        return '<div class="md-code-block"><div class="md-code-header">' +
            '<span class="md-code-lang">' + escapeHtml(label) + '</span>' +
            '<button type="button" class="md-copy-btn" data-code="' + encodeURIComponent(code) + '">' +
            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>' +
            '<span>Salin</span></button></div>' +
            '<pre><code class="hljs">' + highlighted + '</code></pre></div>';
    }

    function initMarkdownRenderer() {
        if (typeof marked === 'undefined' || typeof DOMPurify === 'undefined') {
            setTimeout(initMarkdownRenderer, 50);
            return;
        }

        marked.use({
            gfm: true,
            breaks: true,
            renderer: {
                // code block (marked v9 token vs older string args)
                code: function(tokenOrCode, langOrUndefined, escaped) {
                    var code, lang;
                    if (tokenOrCode && typeof tokenOrCode === 'object') {
                        code = tokenOrCode.text || '';
                        lang = tokenOrCode.lang || '';
                    } else {
                        code = tokenOrCode || '';
                        lang = langOrUndefined || '';
                    }
                    return buildCodeBlock(code, lang);
                },

                // inline code
                codespan: function(tokenOrCode) {
                    var text = (tokenOrCode && typeof tokenOrCode === 'object') ? (tokenOrCode.text || '') : (tokenOrCode || '');
                    return '<code>' + escapeHtml(text) + '</code>';
                },

                // table (marked v9 token vs older string args)
                table: function(tokenOrHeader, bodyOrUndefined) {
                    if (tokenOrHeader && typeof tokenOrHeader === 'object' && tokenOrHeader.header) {
                        var token = tokenOrHeader;
                        var theadCells = token.header.map(function(cell) {
                            var align = cell.align ? ' style="text-align:' + cell.align + '"' : '';
                            return '<th' + align + '>' + (cell.text || '') + '</th>';
                        }).join('');
                        var thead = '<tr>' + theadCells + '</tr>';
                        var tbody = (token.rows || []).map(function(row) {
                            var cells = row.map(function(cell, i) {
                                var align = (token.header[i] && token.header[i].align) ? ' style="text-align:' + token.header[i].align + '"' : '';
                                return '<td' + align + '>' + (cell.text || '') + '</td>';
                            }).join('');
                            return '<tr>' + cells + '</tr>';
                        }).join('');
                        return '<div class="md-table-wrap"><table><thead>' + thead + '</thead><tbody>' + tbody + '</tbody></table></div>';
                    }
                    return '<div class="md-table-wrap"><table><thead>' + (tokenOrHeader || '') + '</thead><tbody>' + (bodyOrUndefined || '') + '</tbody></table></div>';
                },

                tablerow: function(content) {
                    return '<tr>' + content + '</tr>';
                },

                tablecell: function(content, flags) {
                    if (typeof content === 'object') return '';
                    var tag = (flags && flags.header) ? 'th' : 'td';
                    var align = (flags && flags.align) ? ' style="text-align:' + flags.align + '"' : '';
                    return '<' + tag + align + '>' + content + '</' + tag + '>';
                }
            }
        });

        var purifyConfig = {
            ADD_TAGS: ['svg', 'path', 'rect', 'circle', 'details', 'summary', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'u', 'ins', 's', 'del', 'mark', 'span', 'div', 'pre', 'code', 'button'],
            ADD_ATTR: ['viewBox', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'd', 'cx', 'cy', 'r', 'x', 'y', 'width', 'height', 'rx', 'ry', 'data-code', 'class', 'type', 'checked', 'disabled', 'style'],
            FORBID_ATTR: ['onerror', 'onload', 'onclick', 'onmouseover'],
            ALLOW_DATA_ATTR: true
        };

        window.MarkdownRenderer = {
            parse: function(text) {
                if (!text) return '';
                var result = '';
                // Tangani blok <think>...</think> (jika ada alur berpikir model reasoning)
                text = text.replace(/<think>([\s\S]*?)<\/think>/g, function(m, thought) {
                    var inner = '';
                    try {
                        inner = DOMPurify.sanitize(marked.parse(thought.trim()), purifyConfig);
                    } catch(e) {
                        inner = escapeHtml(thought);
                    }
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
        console.log('[RedSim] Markdown renderer initialized successfully');
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
            var spanEl = btn.querySelector('span');
            if (spanEl) {
                spanEl.textContent = 'Gagal!';
                setTimeout(function() { spanEl.textContent = 'Salin'; }, 1500);
            }
        });
    });

    initMarkdownRenderer();
})();
