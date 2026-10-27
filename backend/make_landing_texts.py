import os

js_code = """
// --- TechText ---
function initTechText(container, options = {}) {
    const text = options.text || 'React Bits';
    const fontFamily = options.fontFamily || getComputedStyle(container).fontFamily || 'sans-serif';
    const fontWeight = options.fontWeight || 600;
    const fontSize = options.fontSize || 150;
    const letterSpacing = options.letterSpacing !== undefined ? options.letterSpacing : -0.05;
    const color = options.color || '#ffffff';
    const accentColor = options.accentColor || '#ffffff';
    const reach = options.reach || 200;
    const softness = options.softness || 0.7;
    const dashLength = options.dashLength || 4;
    const dashGap = options.dashGap || 2;
    const strokeWidth = options.strokeWidth || 1.5;
    const lineStyle = options.lineStyle || 'dashed';
    const reveal = options.reveal || 'letter';
    const specks = options.specks || 15;
    const selection = options.selection !== undefined ? options.selection : true;
    const labels = options.labels !== undefined ? options.labels : true;
    const draggable = options.draggable !== undefined ? options.draggable : true;
    const sweep = options.sweep !== undefined ? options.sweep : true;
    const speed = options.speed || 1;

    const LABEL_FONT = '10px ui-monospace, SFMono-Regular, Menlo, Consolas, monospace';
    const FALLOFF_STEPS = 8;
    const SPRING = 320;
    const DAMPING = 22;

    const approach = (current, target, dt, seconds) => current + (target - current) * (1 - Math.exp(-dt / seconds));
    const hexToRgb = hex => {
        let h = String(hex || '').replace('#', '');
        if (h.length === 3) h = h.replace(/./g, c => c + c);
        const n = parseInt(h.slice(0, 6), 16);
        return isNaN(n) ? [255, 255, 255] : [(n >> 16) & 255, (n >> 8) & 255, n & 255];
    };
    const rgba = (hex, alpha) => {
        const [r, g, b] = hexToRgb(hex);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    };
    const noise = (...values) => {
        let h = 2166136261;
        for (const value of values) {
            h = Math.imul(h ^ (value | 0), 16777619);
            h ^= h >>> 13;
            h = Math.imul(h, 0x5bd1e995);
            h ^= h >>> 15;
        }
        return (h >>> 0) / 4294967296;
    };
    const signed = value => (value > 0 ? `+${value}` : value < 0 ? `−${-value}` : '0');

    container.style.position = 'relative';
    container.style.touchAction = 'pan-y';
    container.style.userSelect = 'none';

    const canvas = document.createElement('canvas');
    canvas.style.display = 'block';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    container.appendChild(canvas);

    const ctx = canvas.getContext('2d');
    const scratch = document.createElement('canvas');
    const scratchCtx = scratch.getContext('2d');

    const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let width = 1, height = 1, dpr = 1, raf = 0;
    let last = performance.now();
    let visible = true, alive = true;
    let layoutKey = '', requestedFont = '';
    let word = null, glyphs = [];
    let presence = 0, clock = 0, pulse = 0;
    let placed = false, dragging = -1;
    const pointer = { x: 0, y: 0, inside: false };
    const grab = { x: 0, y: 0 };
    const lens = { x: 0, y: 0 };
    const frame = { x1: 0, y1: 0, x2: 0, y2: 0, alpha: 0, index: -1 };

    const fontFor = (size) => `${fontWeight} ${size}px ${fontFamily}`;
    const setFont = (target, size) => {
        target.font = fontFor(size);
        if ('letterSpacing' in target) target.letterSpacing = `${letterSpacing * size}px`;
        target.textAlign = 'left';
        target.textBaseline = 'alphabetic';
    };

    const sprite = (view, glyph, stroke) => {
        const pad = Math.ceil(strokeWidth * 2 + 4);
        const left = glyph.box.x1 - pad;
        const top = glyph.box.y1 - pad;
        const w = glyph.box.x2 - glyph.box.x1 + pad * 2;
        const h = glyph.box.y2 - glyph.box.y1 + pad * 2;
        const image = document.createElement('canvas');
        image.width = Math.max(1, Math.ceil(w * dpr));
        image.height = Math.max(1, Math.ceil(h * dpr));
        const c = image.getContext('2d');
        if (!c) return { image, left, top };
        c.setTransform(dpr, 0, 0, dpr, -left * dpr, -top * dpr);
        setFont(c, view.size);
        if (stroke) {
            c.lineJoin = 'round';
            c.lineWidth = strokeWidth * 2;
            c.lineCap = 'butt';
            c.strokeStyle = color;
            if (lineStyle !== 'solid') c.setLineDash([Math.max(1, dashLength), Math.max(1, dashGap)]);
            c.strokeText(glyph.char, glyph.x, view.baseline);
            c.setLineDash([]);
            c.globalCompositeOperation = 'destination-out';
            c.fillStyle = '#000000';
            c.fillText(glyph.char, glyph.x, view.baseline);
            c.globalCompositeOperation = 'source-over';
        } else {
            c.fillStyle = color;
            c.fillText(glyph.char, glyph.x, view.baseline);
        }
        return { image, left, top };
    };

    const ensureLayout = () => {
        const key = [text, fontFamily, fontWeight, fontSize, letterSpacing, color, dashLength, dashGap, strokeWidth, lineStyle, width, height, dpr].join('|');
        if (key === layoutKey && word) return word;
        layoutKey = key;
        const wanted = fontFor(64);
        if (document.fonts && wanted !== requestedFont) {
            requestedFont = wanted;
            document.fonts.load(wanted, text).then(() => { layoutKey = ''; wake(); });
        }

        const probe = scratchCtx;
        setFont(probe, fontSize);
        let m = probe.measureText(text);
        const fit = Math.min(
            1,
            (width * 0.9) / Math.max(m.actualBoundingBoxLeft + m.actualBoundingBoxRight, 1),
            (height * 0.66) / Math.max(m.actualBoundingBoxAscent + m.actualBoundingBoxDescent, 1)
        );
        const size = fontSize * fit;
        setFont(probe, size);
        m = probe.measureText(text);
        const inkWidth = m.actualBoundingBoxLeft + m.actualBoundingBoxRight;
        const inkHeight = m.actualBoundingBoxAscent + m.actualBoundingBoxDescent;
        const x = (width - inkWidth) / 2 + m.actualBoundingBoxLeft;
        const baseline = (height - inkHeight) / 2 + m.actualBoundingBoxAscent;
        const next = {
            size,
            baseline,
            left: x - m.actualBoundingBoxLeft,
            right: x + m.actualBoundingBoxRight,
            top: baseline - m.actualBoundingBoxAscent,
            bottom: baseline + m.actualBoundingBoxDescent
        };
        word = next;

        const chars = Array.from(text);
        const previous = glyphs;
        glyphs = [];
        let prefix = '';
        chars.forEach((char, i) => {
            prefix += char;
            const own = probe.measureText(char);
            const gx = x + probe.measureText(prefix).width - own.width;
            if (!char.trim()) return;
            const base = {
                char,
                x: gx,
                box: {
                    x1: gx - own.actualBoundingBoxLeft,
                    y1: baseline - own.actualBoundingBoxAscent,
                    x2: gx + own.actualBoundingBoxRight,
                    y2: baseline + own.actualBoundingBoxDescent
                }
            };
            const kept = previous[glyphs.length];
            glyphs.push({
                ...base,
                offset: kept && kept.char === char ? kept.offset : { x: 0, y: 0 },
                velocity: { x: 0, y: 0 },
                outline: 0,
                index: i,
                fill: sprite(next, base, false),
                dashes: sprite(next, base, true)
            });
        });
        dragging = -1;
        frame.index = -1;
        return next;
    };

    const glyphAt = (x, y) => {
        if (!word || y < word.top - 24 || y > word.bottom + 24) return -1;
        let best = -1;
        let bestDistance = Infinity;
        glyphs.forEach((glyph, i) => {
            const x1 = glyph.box.x1 + glyph.offset.x;
            const x2 = glyph.box.x2 + glyph.offset.x;
            const d = x < x1 ? x1 - x : x > x2 ? x - x2 : 0;
            if (d < bestDistance) {
                bestDistance = d;
                best = i;
            }
        });
        return bestDistance < 28 ? best : -1;
    };

    const falloff = (target, cx, cy, radius, strength, softness) => {
        const inner = Math.min(1, Math.max(0, 1 - softness));
        const gradient = target.createRadialGradient(cx, cy, 0, cx, cy, radius);
        gradient.addColorStop(0, `rgba(0, 0, 0, ${strength})`);
        if (inner > 0.995) {
            gradient.addColorStop(0.995, `rgba(0, 0, 0, ${strength})`);
            gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
            return gradient;
        }
        for (let i = 0; i <= FALLOFF_STEPS; i++) {
            const t = i / FALLOFF_STEPS;
            const eased = t * t * (3 - 2 * t);
            gradient.addColorStop(inner + (1 - inner) * t, `rgba(0, 0, 0, ${strength * (1 - eased)})`);
        }
        return gradient;
    };

    const blit = (target, art, dx, dy, originX, originY) => {
        target.drawImage(
            art.image,
            Math.round((art.left + dx) * dpr - originX),
            Math.round((art.top + dy) * dpr - originY)
        );
    };

    const drawReveal = () => {
        const radius = reach * dpr;
        const cx = lens.x * dpr;
        const cy = lens.y * dpr;
        ctx.globalCompositeOperation = 'destination-out';
        ctx.fillStyle = falloff(ctx, cx, cy, radius, presence, softness);
        ctx.fillRect(cx - radius, cy - radius, radius * 2, radius * 2);
        ctx.globalCompositeOperation = 'source-over';

        const x0 = Math.max(0, Math.floor(cx - radius));
        const y0 = Math.max(0, Math.floor(cy - radius));
        const x1 = Math.min(canvas.width, Math.ceil(cx + radius));
        const y1 = Math.min(canvas.height, Math.ceil(cy + radius));
        if (x1 <= x0 || y1 <= y0) return;
        const w = x1 - x0;
        const h = y1 - y0;
        if (scratch.width < w || scratch.height < h) {
            scratch.width = Math.max(scratch.width, w);
            scratch.height = Math.max(scratch.height, h);
        }
        scratchCtx.setTransform(1, 0, 0, 1, 0, 0);
        scratchCtx.globalCompositeOperation = 'source-over';
        scratchCtx.clearRect(0, 0, w, h);
        for (const glyph of glyphs) blit(scratchCtx, glyph.dashes, glyph.offset.x, glyph.offset.y, x0, y0);
        scratchCtx.globalCompositeOperation = 'destination-in';
        scratchCtx.fillStyle = falloff(scratchCtx, cx - x0, cy - y0, radius, 1, softness);
        scratchCtx.fillRect(0, 0, w, h);
        scratchCtx.globalCompositeOperation = 'source-over';
        ctx.globalAlpha = presence;
        ctx.drawImage(scratch, 0, 0, w, h, x0, y0, w, h);
        ctx.globalAlpha = 1;
    };

    const crisp = value => (Math.round(value * dpr) + 0.5) / dpr;

    const perimeterPoint = (distance, w, h) => {
        let d = ((distance % (2 * (w + h))) + 2 * (w + h)) % (2 * (w + h));
        if (d < w) return [frame.x1 + d, frame.y1, 0, -1];
        d -= w;
        if (d < h) return [frame.x2, frame.y1 + d, 1, 0];
        d -= h;
        if (d < w) return [frame.x2 - d, frame.y2, 0, 1];
        d -= w;
        return [frame.x1, frame.y2 - d, -1, 0];
    };

    const drawSpecks = (a) => {
        const w = frame.x2 - frame.x1;
        const h = frame.y2 - frame.y1;
        if (w < 2 || h < 2) return;
        const perimeter = 2 * (w + h);
        const seed = frame.index + 1;
        const grid = 3;

        for (let k = 0; k < specks; k++) {
            const period = 0.5 + noise(seed, k, 11) * 1.2;
            const t = pulse / period + noise(seed, k, 17);
            const cycle = Math.floor(t);
            const life = t - cycle;
            if (life > 0.7) continue;
            const [px, py, nx, ny] = perimeterPoint(noise(seed, k, cycle) * perimeter, w, h);
            const pick = noise(seed, k, cycle, 2);
            const size = pick < 0.46 ? 2 : pick < 0.7 ? 3 : pick < 0.84 ? 5 : pick < 0.94 ? 8 : 11;
            const large = size >= 8;
            const out = (large ? 9 : 4) + Math.floor(noise(seed, k, cycle, 1) * 5) * grid;
            const x = frame.x1 + Math.round((px + nx * out - frame.x1) / grid) * grid;
            const y = frame.y1 + Math.round((py + ny * out - frame.y1) / grid) * grid;
            const tone = noise(seed, k, cycle, 3);
            const blink = life < 0.06 || (life > 0.32 && life < 0.36) ? 0.35 : 1;
            const alpha = a * (large ? 0.3 + 0.4 * tone : 0.3 + 0.6 * tone) * blink;
            const left = Math.round(x - size / 2);
            const top = Math.round(y - size / 2);
            if (tone < 0.26 || (large && tone < 0.78)) {
                ctx.strokeStyle = rgba(accentColor, alpha);
                ctx.strokeRect(left + 0.5, top + 0.5, size, size);
                if (large && tone > 0.5) {
                    ctx.fillStyle = rgba(accentColor, alpha);
                    ctx.fillRect(Math.round(x) - 1, Math.round(y) - 1, 2, 2);
                }
            } else {
                ctx.fillStyle = rgba(accentColor, alpha);
                ctx.fillRect(left, top, size, size);
            }
        }

        for (let j = 0; j < 2; j++) {
            const head = (pulse * 0.42 * speed + j * 0.5) * perimeter;
            for (let i = 0; i < 4; i++) {
                const [x, y] = perimeterPoint(head - i * 6, w, h);
                const size = i === 0 ? 3 : 2;
                ctx.fillStyle = rgba(accentColor, a * [0.95, 0.55, 0.32, 0.16][i]);
                ctx.fillRect(Math.round(x - size / 2), Math.round(y - size / 2), size, size);
            }
        }
    };

    const drawFrame = () => {
        const glyph = glyphs[frame.index];
        if (!glyph || frame.alpha < 0.01) return;
        const a = frame.alpha;
        const x1 = crisp(frame.x1);
        const y1 = crisp(frame.y1);
        const x2 = crisp(frame.x2);
        const y2 = crisp(frame.y2);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        const moved = Math.hypot(glyph.offset.x, glyph.offset.y);
        if (moved > 1) {
            const hx = (glyph.box.x1 + glyph.box.x2) / 2;
            const hy = (glyph.box.y1 + glyph.box.y2) / 2;
            ctx.beginPath();
            ctx.moveTo(hx, hy);
            ctx.lineTo(hx + glyph.offset.x, hy + glyph.offset.y);
            ctx.setLineDash([3, 4]);
            ctx.lineWidth = 1;
            ctx.strokeStyle = rgba(accentColor, 0.45 * a);
            ctx.stroke();
            ctx.setLineDash([]);
            ctx.beginPath();
            ctx.rect(Math.round(hx) - 2, Math.round(hy) - 2, 4, 4);
            ctx.fillStyle = rgba(accentColor, 0.7 * a);
            ctx.fill();
        }

        ctx.beginPath();
        ctx.rect(x1, y1, x2 - x1, y2 - y1);
        ctx.lineWidth = 1;
        ctx.strokeStyle = rgba(accentColor, 0.5 * a);
        ctx.stroke();

        ctx.beginPath();
        for (const [cx, cy] of [[x1, y1], [x2, y1], [x2, y2], [x1, y2]]) {
            ctx.rect(Math.round(cx) - 2, Math.round(cy) - 2, 5, 5);
        }
        ctx.fillStyle = rgba(accentColor, 0.95 * a);
        ctx.fill();

        if (specks > 0) {
            ctx.lineWidth = 1;
            drawSpecks(a);
        }

        if (!labels) return;
        ctx.font = LABEL_FONT;
        ctx.textAlign = 'left';
        ctx.textBaseline = 'bottom';
        ctx.fillStyle = rgba(accentColor, 0.62 * a);
        const label = moved > 1
            ? `${signed(Math.round(glyph.offset.x))}, ${signed(Math.round(-glyph.offset.y))}`
            : `${glyph.char}  ${Math.round(glyph.box.x2 - glyph.box.x1)} × ${Math.round(glyph.box.y2 - glyph.box.y1)}`;
        ctx.fillText(label, Math.round(frame.x1), Math.round(frame.y1) - 7);
    };

    const tick = now => {
        raf = 0;
        const dt = Math.min(0.05, Math.max(0.001, (now - last) / 1000));
        last = now;
        const view = ensureLayout();

        const sweeping = sweep && !reducedMotion && !pointer.inside && dragging < 0;
        if (sweeping) clock += dt * speed;
        pulse += dt;
        let targetX = pointer.x;
        let targetY = pointer.y;
        if (sweeping) {
            targetX = view.left + (view.right - view.left) * (0.5 - 0.5 * Math.cos(clock * 0.45));
            targetY = view.top + (view.bottom - view.top) * (0.45 + 0.1 * Math.sin(clock * 0.8));
        }
        const active = pointer.inside || sweeping || dragging >= 0;
        if (active && !placed) {
            lens.x = targetX;
            lens.y = targetY;
        }
        if (active) {
            const lag = pointer.inside ? 0.05 : 0.22;
            lens.x = approach(lens.x, targetX, dt, lag);
            lens.y = approach(lens.y, targetY, dt, lag);
        }
        placed = active;
        presence = approach(presence, reveal === 'area' && active && dragging < 0 ? 1 : 0, dt, 0.16);

        let moving = false;
        glyphs.forEach((glyph, i) => {
            if (i === dragging) {
                glyph.offset.x = approach(glyph.offset.x, pointer.x - grab.x, dt, 0.03);
                glyph.offset.y = approach(glyph.offset.y, pointer.y - grab.y, dt, 0.03);
                glyph.velocity.x = 0;
                glyph.velocity.y = 0;
                moving = true;
                return;
            }
            const { offset, velocity } = glyph;
            if (Math.abs(offset.x) < 0.05 && Math.abs(offset.y) < 0.05 && Math.hypot(velocity.x, velocity.y) < 0.5) {
                offset.x = 0;
                offset.y = 0;
                velocity.x = 0;
                velocity.y = 0;
                return;
            }
            velocity.x += (-SPRING * offset.x - DAMPING * velocity.x) * dt;
            velocity.y += (-SPRING * offset.y - DAMPING * velocity.y) * dt;
            offset.x += velocity.x * dt;
            offset.y += velocity.y * dt;
            moving = true;
        });

        const focus = dragging >= 0 ? dragging : active ? glyphAt(lens.x, lens.y) : -1;
        if (focus >= 0 && selection) {
            const glyph = glyphs[focus];
            const bx1 = glyph.box.x1 + glyph.offset.x - 6;
            const by1 = glyph.box.y1 + glyph.offset.y - 6;
            const bx2 = glyph.box.x2 + glyph.offset.x + 6;
            const by2 = glyph.box.y2 + glyph.offset.y + 6;
            if (frame.index < 0 || frame.alpha < 0.02) {
                frame.x1 = bx1;
                frame.y1 = by1;
                frame.x2 = bx2;
                frame.y2 = by2;
            }
            const glide = focus === dragging ? 0.02 : 0.08;
            frame.x1 = approach(frame.x1, bx1, dt, glide);
            frame.y1 = approach(frame.y1, by1, dt, glide);
            frame.x2 = approach(frame.x2, bx2, dt, glide);
            frame.y2 = approach(frame.y2, by2, dt, glide);
            frame.index = focus;
        }
        frame.alpha = approach(frame.alpha, focus >= 0 && selection ? 1 : 0, dt, 0.1);

        glyphs.forEach((glyph, i) => {
            const target = reveal === 'letter' && i === focus && i !== dragging ? 1 : 0;
            glyph.outline = approach(glyph.outline, target, dt, 0.09);
            if (Math.abs(glyph.outline - target) > 0.002) moving = true;
            else glyph.outline = target;
        });

        if (draggable) container.style.cursor = dragging >= 0 ? 'grabbing' : focus >= 0 && pointer.inside ? 'grab' : '';

        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.globalCompositeOperation = 'source-over';
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (const glyph of glyphs) {
            const moved = Math.hypot(glyph.offset.x, glyph.offset.y);
            if (moved > 1) {
                ctx.globalAlpha = Math.min(1, moved / 24) * 0.55;
                blit(ctx, glyph.dashes, 0, 0, 0, 0);
                ctx.globalAlpha = 1;
            }
        }
        for (const glyph of glyphs) {
            if (glyph.outline < 0.999) {
                ctx.globalAlpha = 1 - glyph.outline;
                blit(ctx, glyph.fill, glyph.offset.x, glyph.offset.y, 0, 0);
            }
            if (glyph.outline > 0.001) {
                ctx.globalAlpha = glyph.outline;
                blit(ctx, glyph.dashes, glyph.offset.x, glyph.offset.y, 0, 0);
            }
            ctx.globalAlpha = 1;
        }
        if (presence > 0.001) drawReveal();
        drawFrame();

        const settling = moving || Math.abs(presence - (reveal === 'area' && active && dragging < 0 ? 1 : 0)) > 0.002 || (frame.alpha > 0.01 && frame.alpha < 0.99);
        if ((active || settling) && visible && alive) raf = requestAnimationFrame(tick);
    };

    const wake = () => {
        if (raf || !visible || !alive) return;
        last = performance.now();
        raf = requestAnimationFrame(tick);
    };

    const resize = () => {
        width = Math.max(1, container.clientWidth);
        height = Math.max(1, container.clientHeight);
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        layoutKey = '';
        wake();
    };

    const locate = e => {
        const rect = container.getBoundingClientRect();
        pointer.x = e.clientX - rect.left;
        pointer.y = e.clientY - rect.top;
    };
    const onMove = e => {
        locate(e);
        pointer.inside = true;
        wake();
    };
    const onLeave = () => {
        if (dragging >= 0) return;
        pointer.inside = false;
        wake();
    };
    const onDown = e => {
        locate(e);
        pointer.inside = true;
        if (draggable && (e.pointerType !== 'mouse' || e.button === 0)) {
            const index = glyphAt(pointer.x, pointer.y);
            if (index >= 0) {
                dragging = index;
                grab.x = pointer.x - glyphs[index].offset.x;
                grab.y = pointer.y - glyphs[index].offset.y;
                if (container.setPointerCapture) container.setPointerCapture(e.pointerId);
            }
        }
        wake();
    };
    const onUp = e => {
        if (dragging >= 0) {
            dragging = -1;
            if (container.releasePointerCapture) container.releasePointerCapture(e.pointerId);
            const rect = container.getBoundingClientRect();
            pointer.inside = e.clientX >= rect.left && e.clientX <= rect.right && e.clientY >= rect.top && e.clientY <= rect.bottom;
        }
        wake();
    };

    container.addEventListener('pointermove', onMove, { passive: true });
    container.addEventListener('pointerenter', onMove, { passive: true });
    container.addEventListener('pointerdown', onDown, { passive: true });
    container.addEventListener('pointerup', onUp, { passive: true });
    container.addEventListener('pointercancel', onUp, { passive: true });
    container.addEventListener('pointerleave', onLeave, { passive: true });
    window.addEventListener('resize', resize);
    if (document.fonts) document.fonts.ready.then(() => { layoutKey = ''; wake(); });

    resize();
}

// --- StrokeText ---
function initStrokeText(container, options = {}) {
    const text = options.text || 'Draw Attention';
    const strokeColor = options.strokeColor || '#A78BFA';
    const fillColor = options.fillColor || '#F8FAFC';
    const strokeWidth = options.strokeWidth || 1.4;
    const drawDuration = options.drawDuration || 1.6;
    const fillDelay = options.fillDelay || 0.2;
    const stagger = options.stagger || 0.05;
    const ease = options.ease || 'power2.out';
    const fontSize = options.fontSize || 128;
    const fontWeight = options.fontWeight || 800;
    const letterSpacing = options.letterSpacing !== undefined ? options.letterSpacing : -4;

    const characters = text.split('');
    const dash = Math.max(fontSize * 7, 200);

    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', `0 ${-fontSize} 600 ${fontSize * 1.3}`);
    svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
    svg.style.display = 'block';
    svg.style.width = '100%';
    svg.style.height = `${Math.round(fontSize * 1.3)}px`;

    const clipId = 'stroke-text-wipe-' + Math.random().toString(36).substr(2, 9);
    const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
    const clipPath = document.createElementNS('http://www.w3.org/2000/svg', 'clipPath');
    clipPath.setAttribute('id', clipId);
    clipPath.setAttribute('clipPathUnits', 'userSpaceOnUse');
    const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
    rect.setAttribute('x', '0');
    rect.setAttribute('y', -fontSize);
    rect.setAttribute('width', '0');
    rect.setAttribute('height', fontSize * 1.5);
    clipPath.appendChild(rect);
    defs.appendChild(clipPath);
    svg.appendChild(defs);

    const strokeGroup = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    strokeGroup.setAttribute('fill', 'none');
    strokeGroup.setAttribute('stroke', strokeColor);
    strokeGroup.setAttribute('stroke-width', strokeWidth);
    strokeGroup.setAttribute('stroke-linejoin', 'round');
    strokeGroup.setAttribute('stroke-linecap', 'round');
    strokeGroup.style.fontSize = `${fontSize}px`;
    strokeGroup.style.fontWeight = fontWeight;
    strokeGroup.style.letterSpacing = `${letterSpacing}px`;

    const fillGroup = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    fillGroup.setAttribute('fill', fillColor);
    fillGroup.setAttribute('stroke', 'none');
    fillGroup.setAttribute('clip-path', `url(#${clipId})`);
    fillGroup.style.fontSize = `${fontSize}px`;
    fillGroup.style.fontWeight = fontWeight;
    fillGroup.style.letterSpacing = `${letterSpacing}px`;

    characters.forEach(char => {
        const ts1 = document.createElementNS('http://www.w3.org/2000/svg', 'tspan');
        ts1.textContent = char;
        ts1.classList.add('st-stroke');
        strokeGroup.appendChild(ts1);

        const ts2 = document.createElementNS('http://www.w3.org/2000/svg', 'tspan');
        ts2.textContent = char;
        ts2.classList.add('st-fill');
        fillGroup.appendChild(ts2);
    });

    svg.appendChild(strokeGroup);
    svg.appendChild(fillGroup);
    container.appendChild(svg);
    container.style.display = 'block';
    container.style.width = '100%';
    container.style.lineHeight = '0';

    if (typeof gsap === 'undefined') return;

    setTimeout(() => {
        const bbox = strokeGroup.getBBox();
        const box = { x: bbox.x, y: bbox.y, width: bbox.width, height: bbox.height };
        rect.setAttribute('x', box.x - strokeWidth);
        rect.setAttribute('y', box.y - strokeWidth);
        rect.setAttribute('height', box.height + strokeWidth * 2);

        const strokes = svg.querySelectorAll('.st-stroke');
        const fills = svg.querySelectorAll('.st-fill');

        gsap.set(strokes, { strokeDasharray: dash, strokeDashoffset: dash });
        gsap.set(fills, { opacity: 1 });
        gsap.set(rect, { attr: { width: 0 } });

        const tl = gsap.timeline({ repeat: -1, repeatDelay: 2 });
        tl.to(strokes, { strokeDashoffset: 0, duration: drawDuration, ease, stagger }, 0);
        tl.to(rect, { attr: { width: box.width + strokeWidth * 2 }, duration: Math.max(0.4, drawDuration * 0.5), ease: 'power2.inOut' }, drawDuration + fillDelay);
    }, 100);
}

// --- DecryptedText ---
function initDecryptedTextLoop(container, texts, options = {}) {
    const speed = options.speed || 50;
    const pauseDuration = options.pauseDuration || 2500;
    const maxIterations = options.maxIterations || 10;
    const characters = options.characters || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_+';
    const availableChars = characters.split('');

    let currentTextIndex = 0;
    let interval = null;
    
    function shuffleText(originalText, currentRevealed) {
        return originalText.split('').map((char, i) => {
            if (char === ' ') return ' ';
            if (currentRevealed.has(i)) return originalText[i];
            return availableChars[Math.floor(Math.random() * availableChars.length)];
        }).join('');
    }

    function animateToNext() {
        const text = texts[currentTextIndex];
        const revealed = new Set();
        const textLength = text.length;
        
        container.textContent = shuffleText(text, revealed);
        
        let iters = 0;
        interval = setInterval(() => {
            iters++;
            if (iters < maxIterations) {
                // Randomly reveal some
                for (let i = 0; i < textLength; i++) {
                    if (Math.random() > 0.8) revealed.add(i);
                }
                container.textContent = shuffleText(text, revealed);
            } else {
                for (let i = 0; i < textLength; i++) revealed.add(i);
                container.textContent = text;
                clearInterval(interval);
                
                setTimeout(() => {
                    currentTextIndex = (currentTextIndex + 1) % texts.length;
                    animateToNext();
                }, pauseDuration);
            }
        }, speed);
    }
    
    animateToNext();
}

window.initTechText = initTechText;
window.initStrokeText = initStrokeText;
window.initDecryptedTextLoop = initDecryptedTextLoop;
"""
with open('public/js/landing-texts.js', 'w') as f:
    f.write(js_code)
print("JS file created.")
