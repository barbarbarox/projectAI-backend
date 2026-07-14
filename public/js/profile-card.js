document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.querySelector('.pc-card-wrapper');
    const shell = document.querySelector('.pc-card-shell');

    if (!wrap || !shell) return;

    const clamp = (v, min = 0, max = 100) => Math.min(Math.max(v, min), max);
    const round = (v, precision = 3) => parseFloat(v.toFixed(precision));
    const adjust = (v, fMin, fMax, tMin, tMax) => round(tMin + ((tMax - tMin) * (v - fMin)) / (fMax - fMin));

    const ANIMATION_CONFIG = {
        INITIAL_DURATION: 1200,
        INITIAL_X_OFFSET: 70,
        INITIAL_Y_OFFSET: 60,
        ENTER_TRANSITION_MS: 180
    };

    let rafId = null;
    let running = false;
    let lastTs = 0;

    let currentX = 0;
    let currentY = 0;
    let targetX = 0;
    let targetY = 0;

    const DEFAULT_TAU = 0.14;
    const INITIAL_TAU = 0.6;
    let initialUntil = 0;
    let enterTimer = null;
    let leaveRaf = null;

    const setVarsFromXY = (x, y) => {
        const width = shell.clientWidth || 1;
        const height = shell.clientHeight || 1;

        const percentX = clamp((100 / width) * x);
        const percentY = clamp((100 / height) * y);

        const centerX = percentX - 50;
        const centerY = percentY - 50;

        const properties = {
            '--pc-pointer-x': `${percentX}%`,
            '--pc-pointer-y': `${percentY}%`,
            '--pc-background-x': `${adjust(percentX, 0, 100, 35, 65)}%`,
            '--pc-background-y': `${adjust(percentY, 0, 100, 35, 65)}%`,
            '--pc-pointer-from-center': `${clamp(Math.hypot(percentY - 50, percentX - 50) / 50, 0, 1)}`,
            '--pc-pointer-from-top': `${percentY / 100}`,
            '--pc-pointer-from-left': `${percentX / 100}`,
            '--pc-rotate-x': `${round(-(centerX / 5))}deg`,
            '--pc-rotate-y': `${round(centerY / 4)}deg`
        };

        for (const [k, v] of Object.entries(properties)) wrap.style.setProperty(k, v);
    };

    const step = ts => {
        if (!running) return;
        if (lastTs === 0) lastTs = ts;
        const dt = (ts - lastTs) / 1000;
        lastTs = ts;

        const tau = ts < initialUntil ? INITIAL_TAU : DEFAULT_TAU;
        const k = 1 - Math.exp(-dt / tau);

        currentX += (targetX - currentX) * k;
        currentY += (targetY - currentY) * k;

        setVarsFromXY(currentX, currentY);

        const stillFar = Math.abs(targetX - currentX) > 0.05 || Math.abs(targetY - currentY) > 0.05;

        if (stillFar || document.hasFocus()) {
            rafId = requestAnimationFrame(step);
        } else {
            running = false;
            lastTs = 0;
            if (rafId) {
                cancelAnimationFrame(rafId);
                rafId = null;
            }
        }
    };

    const start = () => {
        if (running) return;
        running = true;
        lastTs = 0;
        rafId = requestAnimationFrame(step);
    };

    const getOffsets = (evt, el) => {
        const rect = el.getBoundingClientRect();
        return { x: evt.clientX - rect.left, y: evt.clientY - rect.top };
    };

    shell.addEventListener('pointerenter', (event) => {
        shell.classList.add('active');
        shell.classList.add('entering');
        if (enterTimer) window.clearTimeout(enterTimer);
        enterTimer = window.setTimeout(() => {
            shell.classList.remove('entering');
        }, ANIMATION_CONFIG.ENTER_TRANSITION_MS);

        const { x, y } = getOffsets(event, shell);
        targetX = x;
        targetY = y;
        start();
    });

    shell.addEventListener('pointermove', (event) => {
        const { x, y } = getOffsets(event, shell);
        targetX = x;
        targetY = y;
        start();
    });

    shell.addEventListener('pointerleave', () => {
        targetX = shell.clientWidth / 2;
        targetY = shell.clientHeight / 2;
        start();

        const checkSettle = () => {
            const settled = Math.hypot(targetX - currentX, targetY - currentY) < 0.6;
            if (settled) {
                shell.classList.remove('active');
                leaveRaf = null;
            } else {
                leaveRaf = requestAnimationFrame(checkSettle);
            }
        };
        if (leaveRaf) cancelAnimationFrame(leaveRaf);
        leaveRaf = requestAnimationFrame(checkSettle);
    });

    // Initialize animation to center
    const initialX = (shell.clientWidth || 0) - ANIMATION_CONFIG.INITIAL_X_OFFSET;
    const initialY = ANIMATION_CONFIG.INITIAL_Y_OFFSET;
    currentX = initialX;
    currentY = initialY;
    setVarsFromXY(currentX, currentY);
    targetX = shell.clientWidth / 2;
    targetY = shell.clientHeight / 2;
    
    initialUntil = performance.now() + ANIMATION_CONFIG.INITIAL_DURATION;
    start();
});
