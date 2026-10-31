<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RedSim — Platform Keamanan Siber Hybrid AI+ML. Analisis kerentanan URL & kode, simulasi serangan MITRE ATT&CK, CVSSv3 Calculator, dan benchmark LLM vs Machine Learning.">
    <title>RedSim — Platform Keamanan Siber Hybrid AI + ML</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&family=Montserrat:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing-animations.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ===== BASE OVERRIDES ===== */
        html {
            overflow-x: hidden;
            max-width: 100vw;
            width: 100%;
            scroll-behavior: smooth;
        }
        body {
            overflow-x: hidden;
            max-width: 100vw;
            width: 100%;
            position: relative;
            background: transparent !important;
            padding-top: 78px;
        }
        @media (max-width: 640px) {
            body { padding-top: 68px; }
        }
        *, ::before, ::after {
            box-sizing: border-box;
        }
        section {
            background: transparent !important;
            overflow-x: clip;
            max-width: 100%;
            width: 100%;
        }
        #ghost-fibers-bg { z-index: 0; }
        #splash-screen { background: #050816 !important; }
        .cs-card {
            background: rgba(15, 22, 41, 0.60) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .kb-card, .kb-card-inner {
            background: rgba(15, 22, 41, 0.55) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* ======================================================
           CARD NAV — CardNav-style glassmorphism navbar
           ====================================================== */
        :root {
            --cn-panel: rgba(6, 8, 18, 0.88);
            --cn-solid: #060812;
            --cn-edge: rgba(255,255,255,0.08);
            --cn-highlight: rgba(255,255,255,0.07);
            --cn-shadow: 0 24px 48px -20px rgba(0,0,0,0.9), 0 6px 16px -8px rgba(0,0,0,0.6);
            --cn-text: #e8edf5;
            --cn-muted: rgba(180,200,225,0.65);
            --cn-hover: rgba(255,255,255,0.08);
            --cn-ring: rgba(0,212,255,0.5);
            --cn-tile: linear-gradient(180deg, rgba(10,15,35,0.95) 0%, rgba(6,8,20,0.98) 100%);
            --cn-tile-rim: inset 0 0 0 1px rgba(255,255,255,0.07), inset 0 1px 0 rgba(255,255,255,0.1), 0 4px 10px -4px rgba(0,0,0,0.8);
            --cn-cta: linear-gradient(135deg, #00d4ff 0%, #7c3aed 100%);
            --cn-cta-ink: #ffffff;
            --cn-cta-shadow: inset 0 1px 0 rgba(255,255,255,0.22), 0 6px 18px -6px rgba(0,212,255,0.4);
            --cn-cta-hover: brightness(1.12);
            --cn-blur: blur(24px) saturate(1.6);
            --cn-max: 960px;
            --cn-bar: 62px;
            --cn-inset: 8px;
            --cn-gap: 7px;
            --cn-radius: 20px;
        }

        @media (max-width: 640px) {
            :root {
                --cn-bar: 56px;
                --cn-radius: 16px;
                --cn-inset: 6px;
            }
        }

        /* Wrapper positioning — always 100% width, never overflowing */
        .cn-root {
            position: fixed;
            top: 0; left: 0; right: 0;
            width: 100%;
            z-index: 200;
            padding: 10px 16px;
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
            box-sizing: border-box;
        }
        @media (max-width: 640px) {
            .cn-root {
                padding: 8px 10px;
            }
        }
        .cn-root-inner {
            width: 100%;
            max-width: var(--cn-max);
            margin: 0 auto;
            position: relative;
            box-sizing: border-box;
        }

        /* Panel — the pill bar */
        .cn-panel {
            position: relative;
            width: 100%;
            border-radius: var(--cn-radius);
            background: var(--cn-panel);
            backdrop-filter: var(--cn-blur);
            -webkit-backdrop-filter: var(--cn-blur);
            box-shadow: var(--cn-shadow);
            overflow: visible;
            transition: border-radius 0.35s cubic-bezier(0.22,1,0.36,1), background 0.2s ease, box-shadow 0.2s ease;
            box-sizing: border-box;
        }
        .cn-panel-rim {
            position: absolute;
            inset: 0;
            border-radius: inherit;
            box-shadow: inset 0 0 0 1px var(--cn-edge), inset 0 1px 0 var(--cn-highlight);
            pointer-events: none;
            z-index: 3;
        }

        /* Bar — flex layout for perfect responsiveness */
        .cn-bar {
            height: var(--cn-bar);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 var(--cn-inset);
            position: relative;
            z-index: 2;
            width: 100%;
            box-sizing: border-box;
        }

        /* Logo */
        .cn-logo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            padding: 0 8px;
            color: var(--cn-text);
            flex-shrink: 0;
        }
        .cn-logo img { width: 26px; height: 26px; object-fit: contain; }
        @media (min-width: 641px) {
            .cn-logo img { width: 28px; height: 28px; }
            .cn-logo { gap: 9px; padding: 0 10px; }
        }
        .cn-logo-text {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 1.05rem;
            background: linear-gradient(90deg, #00d4ff, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Desktop links row */
        .cn-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2px;
        }

        /* Each nav item — may be plain link or dropdown trigger */
        .cn-item { position: relative; }
        .cn-item-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: calc(var(--cn-bar) - var(--cn-inset) * 2);
            padding: 0 14px;
            border: 0;
            border-radius: calc(var(--cn-radius) - var(--cn-inset));
            background: none;
            color: var(--cn-muted);
            font-family: inherit;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.18s, color 0.18s;
            outline: none;
        }
        .cn-item-btn:hover, .cn-item-btn.active { color: var(--cn-text); background: var(--cn-hover); }
        .cn-item-btn.leaderboard { color: #facc15; }
        .cn-item-btn.leaderboard:hover { background: rgba(250,204,21,0.08); }
        .cn-item-btn.red-lab { color: #fca5a5; }
        .cn-item-btn.red-lab:hover { background: rgba(220,38,38,0.08); }

        /* Chevron inside dropdown trigger */
        .cn-chevron {
            width: 14px; height: 14px;
            opacity: 0.5;
            transition: transform 0.3s cubic-bezier(0.22,1,0.36,1), opacity 0.2s;
            flex-shrink: 0;
        }
        .cn-item.open .cn-chevron { transform: rotate(180deg); opacity: 0.8; }

        /* Card dropdown panel */
        .cn-dropdown {
            position: absolute;
            top: calc(100% + 6px);
            left: 50%;
            transform: translateX(-50%);
            min-width: 260px;
            background: var(--cn-panel);
            backdrop-filter: var(--cn-blur);
            -webkit-backdrop-filter: var(--cn-blur);
            border-radius: 16px;
            box-shadow: var(--cn-shadow);
            border: 1px solid var(--cn-edge);
            padding: var(--cn-inset);
            display: none;
            z-index: 100;
            animation: cnDropIn 0.22s cubic-bezier(0.22,1,0.36,1) both;
        }
        @keyframes cnDropIn {
            from { opacity:0; transform: translateX(-50%) translateY(-8px) scale(0.97); }
            to   { opacity:1; transform: translateX(-50%) translateY(0) scale(1); }
        }
        .cn-item.open .cn-dropdown { display: block; }

        /* Individual card inside dropdown */
        .cn-card {
            background: var(--cn-tile);
            box-shadow: var(--cn-tile-rim);
            border-radius: 12px;
            padding: 14px 14px 12px;
            margin-bottom: 6px;
        }
        .cn-card:last-child { margin-bottom: 0; }
        .cn-card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--cn-text);
            letter-spacing: -0.01em;
            margin-bottom: 3px;
        }
        .cn-card-desc {
            font-size: 12px;
            color: var(--cn-muted);
            line-height: 1.4;
            margin: 0 0 10px;
        }
        .cn-card-links {
            display: flex;
            flex-direction: column;
            gap: 2px;
            list-style: none;
            margin: 0; padding: 0;
        }
        .cn-card-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 4px 2px;
            font-size: 13px;
            font-weight: 500;
            color: rgba(180,200,225,0.75);
            text-decoration: none;
            border-radius: 6px;
            transition: color 0.15s, background 0.15s;
        }
        .cn-card-link:hover { color: var(--cn-text); }
        .cn-card-link svg { flex-shrink: 0; opacity: 0.6; transition: opacity 0.15s, transform 0.3s cubic-bezier(0.22,1,0.36,1); }
        .cn-card-link:hover svg { opacity: 1; transform: translate(2px,-2px); }

        /* CTA Button (right side) */
        .cn-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: calc(var(--cn-bar) - var(--cn-inset) * 2);
            padding: 0 18px;
            border: 0;
            border-radius: calc(var(--cn-radius) - var(--cn-inset));
            background: var(--cn-cta);
            box-shadow: var(--cn-cta-shadow);
            color: var(--cn-cta-ink);
            font-family: inherit;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            transition: filter 0.2s, transform 0.2s;
            outline: none;
        }
        .cn-cta:hover { filter: var(--cn-cta-hover); transform: translateY(-1px); }
        .cn-ghost {
            display: inline-flex;
            align-items: center;
            height: calc(var(--cn-bar) - var(--cn-inset) * 2);
            padding: 0 16px;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: calc(var(--cn-radius) - var(--cn-inset));
            color: var(--cn-muted);
            font-family: inherit;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            margin-right: 6px;
        }
        .cn-ghost:hover { color: var(--cn-text); border-color: rgba(255,255,255,0.2); background: rgba(255,255,255,0.05); }
        .cn-actions { display: flex; align-items: center; justify-self: end; }

        /* Hamburger */
        .cn-hamburger {
            display: none;
            justify-self: end;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 38px; height: 38px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.09);
            background: rgba(255,255,255,0.04);
            cursor: pointer;
            gap: 5px;
            transition: all 0.2s ease;
            margin-right: 4px;
        }
        .cn-hamburger:hover { border-color: rgba(0,212,255,0.3); background: rgba(0,212,255,0.06); }
        .cn-hamburger span {
            display: block;
            width: 17px; height: 1.5px;
            background: rgba(190,205,225,0.75);
            border-radius: 2px;
            transition: all 0.28s cubic-bezier(0.22,1,0.36,1);
        }
        .cn-hamburger.open span:nth-child(1) { transform: translateY(6.5px) rotate(45deg); }
        .cn-hamburger.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .cn-hamburger.open span:nth-child(3) { transform: translateY(-6.5px) rotate(-45deg); }

        /* Mobile drawer */
        .cn-mobile-drawer {
            display: none;
            position: fixed;
            top: 72px; left: 12px; right: 12px;
            width: calc(100% - 24px);
            max-width: var(--cn-max);
            margin: 0 auto;
            z-index: 199;
            background: rgba(6,8,18,0.98);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 16px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.85);
            max-height: calc(100dvh - 86px);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            box-sizing: border-box;
            animation: cnMobileIn 0.22s cubic-bezier(0.22,1,0.36,1) both;
        }
        @keyframes cnMobileIn {
            from { opacity:0; transform: translateY(-10px) scale(0.98); }
            to   { opacity:1; transform: translateY(0) scale(1); }
        }
        .cn-mobile-drawer.open { display: block; }
        .cn-mobile-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 13px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(180,200,225,0.75);
            text-decoration: none;
            transition: all 0.15s;
            margin-bottom: 2px;
        }
        .cn-mobile-link:hover { color:#fff; background: rgba(255,255,255,0.06); }
        .cn-mobile-link.yellow { color: #facc15; }
        .cn-mobile-link.red    { color: #fca5a5; }
        .cn-mobile-link.muted  { color: rgba(180,200,225,0.45); font-size: 0.78rem; }
        .cn-mobile-sep { height:1px; background:rgba(255,255,255,0.06); margin: 8px 0; }
        .cn-mobile-section-label {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(180,200,225,0.3);
            padding: 6px 13px 4px;
        }
        .cn-mobile-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
        }
        .cn-mobile-btn {
            display: block;
            text-align: center;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .cn-mobile-btn.ghost  { color:rgba(180,200,225,0.75); border:1px solid rgba(255,255,255,0.1); }
        .cn-mobile-btn.ghost:hover  { color:#fff; background:rgba(255,255,255,0.05); }
        .cn-mobile-btn.primary { color:#fff; background:linear-gradient(135deg,#00d4ff 0%,#7c3aed 100%); box-shadow:0 0 18px rgba(0,212,255,0.18); }
        .cn-mobile-btn.primary:hover { box-shadow:0 0 26px rgba(0,212,255,0.38); }

        /* Responsive — hide desktop links on screens <= 1024px */
        @media (max-width: 1024px) {
            .cn-links { display: none !important; }
            .cn-actions { display: none !important; }
            .cn-hamburger { display: flex !important; }
        }
        @media (min-width: 1025px) {
            .cn-mobile-drawer { display: none !important; }
            .cn-hamburger { display: none !important; }
        }

        /* ===== NEON CTA BUTTON ===== */
        .neon-btn-container { display:flex; justify-content:center; margin-top:10px; max-width:100%; }
        .neon-btn-wrap { font-family:'Montserrat',sans-serif; font-style:italic; font-weight:bold; font-size:1.1em; max-width:100%; }
        .neon-btn {
            width: min(340px, calc(100vw - 36px));
            max-width: 100%;
            height: 60px;
            cursor: pointer;
            display: flex; justify-content: center; align-items: center;
            padding: 0.5em 1.5em;
            background: transparent;
            position: relative;
            overflow: hidden;
            -webkit-clip-path: polygon(6% 0, 93% 0, 100% 8%, 100% 86%, 90% 89%, 88% 100%, 5% 100%, 0% 85%);
            clip-path: polygon(6% 0, 93% 0, 100% 8%, 100% 86%, 90% 89%, 88% 100%, 5% 100%, 0% 85%);
            text-decoration: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .neon-btn .neon-span {
            display: flex;
            -webkit-clip-path: polygon(6% 0, 93% 0, 100% 8%, 100% 86%, 90% 89%, 88% 100%, 5% 100%, 0% 85%);
            clip-path: polygon(6% 0, 93% 0, 100% 8%, 100% 86%, 90% 89%, 88% 100%, 5% 100%, 0% 85%);
            position: absolute; inset: 1px;
            background-color: #0a0a1a;
            z-index: 1;
            transition: all 0.3s ease;
        }
        .neon-btn .neon-txt {
            position: relative; z-index: 2;
            color: aliceblue;
            font-size: 0.95em;
            text-shadow: 0px 0px 1px #4090b5, 0px 0px 1px #9e30a9, 0 0 1px white;
            display: flex; align-items: center; gap: 0.5rem;
            white-space: nowrap;
            transition: text-shadow 0.3s ease;
        }
        .neon-btn::before {
            content: "";
            position: absolute; height: 300px; aspect-ratio: 1.5/1;
            box-shadow: -17px -19px 20px #9e30a9;
            background-image: conic-gradient(#9e30a9, transparent, transparent, transparent, transparent, transparent, transparent, #9e30a9);
            animation: neonRotate 4s linear infinite -2s;
            z-index: 0;
        }
        .neon-btn::after {
            content: "";
            position: absolute; height: 300px; aspect-ratio: 1.5/1;
            box-shadow: -17px -19px 10px #4090b5;
            background-image: conic-gradient(#4090b5, transparent, transparent, transparent, transparent, transparent, transparent, transparent, #4090b5);
            animation: neonRotate 4s linear infinite;
            z-index: 0;
        }
        @keyframes neonRotate { 0% { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .neon-btn:hover .neon-txt { text-shadow: 2px 4px 1px #9e30a9, 2px 2px 1px #4090b5, 0 0 20px rgba(255,255,255,0.6); }
        .neon-btn:hover .neon-span {
            inset: 2px;
            background: repeating-linear-gradient(to bottom, transparent 0%, #4090b5 1px, #4090b5 3px, #4090b5 5px, #4090b5 4px, transparent 0.5%), repeating-linear-gradient(to left, hsl(295,60%,12%) 100%, hsl(295,60%,12%) 100%);
            box-shadow: inset 0 40px 20px hsl(296,59%,10%);
        }

        /* ===== NEOBRUTALIST CYBERPUNK CARDS ===== */
        .neo-card-swap-container {
            position: relative;
            width: min(380px, calc(100vw - 32px));
            max-width: 100%;
            height: 300px;
            flex-shrink: 0;
            margin: 0 auto;
        }
        .neo-card {
            font-family: 'Montserrat', sans-serif;
            width: min(350px, calc(100vw - 44px));
            max-width: 100%;
            min-height: 200px;
            background: var(--card-bg, #ff66a3);
            border: 3px solid #000;
            box-shadow: 6px 6px 0 #000;
            overflow: hidden;
            transition: all 0.25s ease;
            position: absolute;
            top: 50%; left: 50%;
            box-sizing: border-box;
        }
        @media (min-width: 641px) {
            .neo-card {
                width: 360px;
                min-height: 210px;
                box-shadow: 10px 10px 0 #000;
            }
            .neo-card:hover { box-shadow: 14px 14px 0 #000; }
        }
        /* Scanlines */
        .neo-card::after {
            content: '';
            position: absolute; inset: 0;
            background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.07) 2px, rgba(0,0,0,0.07) 4px);
            pointer-events: none; z-index: 5;
        }
        /* Glitch overlay on hover */
        .neo-card::before {
            content: '';
            position: absolute; inset: 0;
            background: var(--glitch-color, rgba(0,212,255,0.15));
            opacity: 0; mix-blend-mode: screen;
            z-index: 10; pointer-events: none;
        }
        .neo-card:hover::before { animation: cardGlitch 0.4s steps(2) infinite; }
        @keyframes cardGlitch {
            0%   { opacity:0; transform:translate(0,0) skewX(0deg); clip-path:none; }
            20%  { opacity:0.6; transform:translate(-3px,1px) skewX(-3deg); clip-path:polygon(0 15%,100% 15%,100% 35%,0 35%); }
            40%  { opacity:0; transform:translate(3px,-2px) skewX(2deg); clip-path:none; }
            60%  { opacity:0.5; transform:translate(-2px,3px) skewX(1deg); clip-path:polygon(0 55%,100% 55%,100% 75%,0 75%); }
            80%  { opacity:0; }
            100% { opacity:0; transform:translate(0,0) skewX(0deg); }
        }
        .neo-card-head {
            font-size: 12px; font-weight: 900;
            height: 32px; background: #fff;
            padding: 0 12px; color: #000;
            border-bottom: 3px solid #000;
            display: flex; align-items: center; justify-content: space-between;
            position: relative; z-index: 6;
        }
        .neo-card-head-tag {
            font-size: 9px; font-weight: 800;
            padding: 2px 6px; border: 2px solid #000;
            background: #000; color: var(--tag-color, #00d4ff);
            letter-spacing: 0.1em; text-transform: uppercase;
        }
        .neo-card-body {
            padding: 12px 14px;
            font-size: 12px; font-weight: 600; color: #000;
            position: relative; z-index: 6;
        }
        .neo-card-body p { margin-bottom: 10px; line-height: 1.5; font-size: 11.5px; font-weight: 600; }
        .neo-card-icon { width: 34px; height: 34px; margin-bottom: 8px; display: flex; align-items: center; justify-content: center; }
        .neo-card-btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 11px;
            border: 3px solid #000;
            box-shadow: 3px 3px 0 #000;
            font-weight: 800; font-size: 10px;
            background: var(--btn-bg, #4ade80);
            cursor: pointer; transition: all 0.2s ease;
            text-decoration: none; color: #000;
            letter-spacing: 0.05em;
            font-family: 'Montserrat', sans-serif;
        }
        .neo-card-btn:hover { translate: 2px 2px; box-shadow: 1px 1px 0 #000; }
        .neo-card-btn:active { translate: 3px 3px; box-shadow: none; }
        .neo-card-1 { --card-bg:#ff2d78; --glitch-color:rgba(0,212,255,0.25); }
        .neo-card-2 { --card-bg:#00d4ff; --glitch-color:rgba(255,45,120,0.25); }
        .neo-card-3 { --card-bg:#7c3aed; --glitch-color:rgba(0,255,136,0.25); }
        .neo-card-4 { --card-bg:#00ff88; --glitch-color:rgba(255,0,120,0.25); }
        .neo-card-5 { --card-bg:#f97316; --glitch-color:rgba(124,58,237,0.25); }

        /* ===== TERMINAL KNOWLEDGE BASE ===== */
        .terminal-card {
            background: #1a1b26;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(0,212,255,0.08);
            overflow: hidden;
            max-width: 680px;
            width: 100%;
            margin: 0 auto;
            font-family: 'JetBrains Mono','Courier New',monospace;
            box-sizing: border-box;
        }
        .terminal-titlebar {
            height: 36px; background: #0d0e17;
            display: flex; align-items: center; padding: 0 12px; gap: 8px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .terminal-dots { display:flex; align-items:center; gap:6px; }
        .terminal-dot { width:11px; height:11px; border-radius:50%; cursor:pointer; transition:filter 0.2s; }
        .terminal-dot:hover { filter:brightness(1.3); }
        .terminal-dot.red { background:#ff5f57; }
        .terminal-dot.yellow { background:#febc2e; }
        .terminal-dot.green { background:#28c840; }
        .terminal-title { flex:1; text-align:center; color:rgba(255,255,255,0.35); font-size:0.7rem; font-family:'JetBrains Mono',monospace; }
        .terminal-body {
            padding: 12px;
            min-height: 240px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            box-sizing: border-box;
        }
        @media (min-width: 641px) {
            .terminal-body { padding: 16px; min-height: 260px; }
        }
        .terminal-line { display:flex; align-items:flex-start; gap:8px; margin-bottom:6px; font-size:0.8rem; }
        .terminal-prompt { color:#00d4ff; white-space:nowrap; flex-shrink:0; }
        .terminal-cmd { color:#e2e8f0; white-space:nowrap; }
        .terminal-output { padding:8px 0; }
        .terminal-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
        }
        @media (min-width: 480px) {
            .terminal-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 6px 10px;
            }
        }
        @media (min-width: 768px) {
            .terminal-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 4px 8px;
            }
        }
        .terminal-item { display:flex; align-items:center; gap:5px; font-size:0.75rem; padding:2px 0; }
        .terminal-item-name { font-weight:700; letter-spacing:0.02em; }
        .terminal-item-desc { font-size:0.62rem; color:rgba(255,255,255,0.35); padding-left:18px; margin-bottom:4px; font-family:'JetBrains Mono',monospace; }
        .terminal-cursor {
            display:inline-block; width:10px; height:16px;
            background:#00d4ff; margin-left:2px;
            animation:termBlink 0.9s ease-in-out infinite;
            vertical-align:middle; border-radius:1px;
        }
        @keyframes termBlink { 0%,100%{opacity:1} 50%{opacity:0} }
        .term-cyan{color:#00d4ff} .term-green{color:#4ade80} .term-yellow{color:#fbbf24}
        .term-pink{color:#f472b6} .term-purple{color:#a78bfa} .term-orange{color:#fb923c}
        .term-white{color:#f1f5f9} .term-dim{color:rgba(255,255,255,0.3)}
    </style>
</head>
<body class="min-h-screen text-[#e2e8f0] antialiased overflow-x-hidden">

    {{-- ========== SPLASH SCREEN ========== --}}
    <div id="splash-screen">
        <div style="display:flex;flex-direction:column;align-items:center;">
            <div class="splash-text"></div>
            <div class="splash-subtitle">Platform Keamanan Berbasis AI</div>
        </div>
    </div>

    {{-- ========== GHOST FIBERS BACKGROUND ========== --}}
    <div id="ghost-fibers-bg" style="position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none;"><canvas id="ghost-fibers-canvas" style="width:100%;height:100%;display:block;"></canvas></div>

    {{-- ========== CARD NAV — Premium Glassmorphism Navbar ========== --}}
    <div class="cn-root" id="cn-root">
        <div class="cn-root-inner">
            <div class="cn-panel" id="cn-panel">
                <div class="cn-bar">
                    {{-- Logo --}}
                    <a href="/" class="cn-logo">
                        <img src="{{ asset('logo-redsim.svg') }}" alt="RedSim Logo">
                        <span class="cn-logo-text">RedSim</span>
                    </a>

                    {{-- Desktop Nav Links --}}
                    <div class="cn-links" id="cn-links">

                        {{-- Platform dropdown --}}
                        <div class="cn-item" id="cn-item-platform">
                            <button type="button" class="cn-item-btn" onclick="cnToggle('cn-item-platform')" aria-expanded="false">
                                Platform
                                <svg class="cn-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div class="cn-dropdown">
                                <div class="cn-card">
                                    <div class="cn-card-title">Fitur Utama</div>
                                    <p class="cn-card-desc">Analisis kerentanan dan simulasi serangan berbasis AI + ML.</p>
                                    <ul class="cn-card-links">
                                        <li><a href="#fitur" class="cn-card-link" onclick="cnCloseAll()">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 17 17 7"/><polyline points="7 7 17 7 17 17"/></svg>Fitur Unggulan</a></li>
                                        <li><a href="#pilar" class="cn-card-link" onclick="cnCloseAll()">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 17 17 7"/><polyline points="7 7 17 7 17 17"/></svg>AI &amp; ML Hybrid</a></li>
                                        <li><a href="#kb" class="cn-card-link" onclick="cnCloseAll()">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 17 17 7"/><polyline points="7 7 17 7 17 17"/></svg>Knowledge Base</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Red Team Lab --}}
                        <a href="/red-team-lab" class="cn-item-btn red-lab">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                            Red Team Lab
                        </a>

                        {{-- Leaderboard --}}
                        <a href="/edukasi/leaderboard" class="cn-item-btn leaderboard">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            Leaderboard
                        </a>

                        {{-- Legal dropdown --}}
                        <div class="cn-item" id="cn-item-legal">
                            <button type="button" class="cn-item-btn" onclick="cnToggle('cn-item-legal')" aria-expanded="false">
                                Legal
                                <svg class="cn-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div class="cn-dropdown">
                                <div class="cn-card">
                                    <div class="cn-card-title">Kebijakan &amp; Hukum</div>
                                    <p class="cn-card-desc">Informasi penggunaan layanan dan perlindungan data Anda.</p>
                                    <ul class="cn-card-links">
                                        <li><a href="/ketentuan-layanan" class="cn-card-link">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 17 17 7"/><polyline points="7 7 17 7 17 17"/></svg>Ketentuan Layanan</a></li>
                                        <li><a href="/kebijakan-privasi" class="cn-card-link">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 17 17 7"/><polyline points="7 7 17 7 17 17"/></svg>Kebijakan Privasi</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>{{-- end cn-links --}}

                    {{-- Desktop CTA + Hamburger --}}
                    <div class="cn-actions">
                        @auth
                            <a href="{{ route('dashboard') }}" class="cn-cta">Dashboard</a>
                        @else
                            <a href="{{ route('masuk') }}" class="cn-ghost">Masuk</a>
                            <a href="{{ route('daftar') }}" class="cn-cta">Daftar Gratis</a>
                        @endauth
                    </div>

                    <button type="button" class="cn-hamburger" id="cn-hamburger" aria-label="Toggle menu" onclick="cnMobileToggle()">
                        <span></span><span></span><span></span>
                    </button>
                </div>
                <span class="cn-panel-rim"></span>
            </div>{{-- end cn-panel --}}
        </div>{{-- end cn-root-inner --}}
    </div>{{-- end cn-root --}}

    {{-- Mobile Drawer --}}
    <div class="cn-mobile-drawer" id="cn-mobile-drawer">
        <div class="cn-mobile-section-label">Platform</div>
        <a href="#fitur" class="cn-mobile-link" onclick="cnMobileClose()">Fitur Unggulan</a>
        <a href="#pilar" class="cn-mobile-link" onclick="cnMobileClose()">AI &amp; ML Hybrid</a>
        <a href="#kb" class="cn-mobile-link" onclick="cnMobileClose()">Knowledge Base</a>
        <div class="cn-mobile-sep"></div>
        <a href="/red-team-lab" class="cn-mobile-link red">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
            Red Team Lab
        </a>
        <a href="/edukasi/leaderboard" class="cn-mobile-link yellow">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            Leaderboard
        </a>
        <div class="cn-mobile-sep"></div>
        <div class="cn-mobile-section-label">Legal</div>
        <a href="/ketentuan-layanan" class="cn-mobile-link">Ketentuan Layanan</a>
        <a href="/kebijakan-privasi" class="cn-mobile-link">Kebijakan Privasi</a>
        <div class="cn-mobile-sep"></div>
        <div class="cn-mobile-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="cn-mobile-btn primary">Ke Dashboard</a>
            @else
                <a href="{{ route('masuk') }}" class="cn-mobile-btn ghost">Masuk</a>
                <a href="{{ route('daftar') }}" class="cn-mobile-btn primary">Daftar Gratis</a>
            @endauth
        </div>
    </div>

    {{-- ========== HERO SECTION ========== --}}
    <section class="relative z-10 px-4 sm:px-6 pt-14 pb-20 sm:pt-16 sm:pb-24 overflow-hidden">
        <div class="max-w-4xl mx-auto text-center landing-hero-content">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#00d4ff]/10 border border-[#00d4ff]/20 mb-8 glow-pulse">
                <div class="w-2 h-2 rounded-full bg-[#00d4ff] animate-pulse"></div>
                <span class="text-xs font-medium text-[#00d4ff]">Platform Keamanan Siber Hybrid AI + ML</span>
            </div>

            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black leading-tight mb-6" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0; width: 100%; max-width: 100%; overflow: hidden;">
                <div id="tech-text-container" style="width: 100%; height: 90px; position: relative; max-width: 800px; display: flex; justify-content: center; overflow: hidden;"></div>
                <div id="stroke-text-container" style="width: 100%; position: relative; max-width: 600px; display: flex; justify-content: center; margin-top: -24px; overflow: hidden;"></div>
            </h1>

            {{-- Text Typing Welcome --}}
            <div class="text-base sm:text-lg md:text-xl text-[#94a3b8] max-w-2xl mx-auto mb-10 leading-relaxed min-h-[3em]">
                <div id="welcome-texttype" class="texttype-wrap"></div>
            </div>

            {{-- NEON CTA Button --}}
            <div class="neon-btn-container">
                <div class="neon-btn-wrap">
                    <a href="{{ route('daftar') }}" class="neon-btn">
                        <span class="neon-span"></span>
                        <span class="neon-txt">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            MULAI ANALISIS GRATIS
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== FEATURES (CardSwap) ========== --}}
    <section id="fitur" class="relative z-10 px-4 sm:px-6 py-20 sm:py-24 overflow-hidden">
        <div class="max-w-6xl mx-auto">
            <div class="card-swap-section">
                {{-- Left: info text --}}
                <div class="card-swap-info flex-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#7c3aed]/15 border border-[#7c3aed]/30 mb-6">
                        <div class="w-1.5 h-1.5 rounded-full bg-[#7c3aed]"></div>
                        <span class="text-xs font-semibold text-[#a78bfa] tracking-wider uppercase">Fitur Unggulan</span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 leading-tight text-white">
                        Semua yang Anda butuhkan<br>
                        <span class="text-white">dalam satu platform</span>
                    </h2>
                    <p class="text-[#94a3b8] max-w-md leading-relaxed mb-8">
                        Platform hybrid AI+ML pertama untuk keamanan siber — menggabungkan kecerdasan LLM (Gemini/Groq) dengan model Machine Learning terlatih pada 297K+ data CVE.
                    </p>
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3 text-sm text-[#94a3b8]">
                            <div class="w-5 h-5 rounded-full bg-[#00d4ff]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            Analisis kode &amp; URL real-time dengan AI
                        </div>
                        <div class="flex items-center gap-3 text-sm text-[#94a3b8]">
                            <div class="w-5 h-5 rounded-full bg-[#7c3aed]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-[#a78bfa]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            Simulasi serangan MITRE ATT&amp;CK + Kill Chain
                        </div>
                        <div class="flex items-center gap-3 text-sm text-[#94a3b8]">
                            <div class="w-5 h-5 rounded-full bg-[#22c55e]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-[#22c55e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            Benchmark LLM vs ML + Ekspor laporan PDF
                        </div>
                        <div class="flex items-center gap-3 text-sm text-[#94a3b8]">
                            <div class="w-5 h-5 rounded-full bg-[#ef4444]/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            Red Team Lab: CVSSv3.1 Calculator + Attack Visualizer
                        </div>
                    </div>
                </div>

                {{-- Right: Neo Brutalist Cyberpunk Card Swap --}}
                <div class="neo-card-swap-container" id="neo-card-swap">
                    {{-- Card 1: Analisis Kode --}}
                    <div class="neo-card neo-card-1">
                        <div class="neo-card-head">
                            <span>⟨ ANALISIS KODE /⟩</span>
                            <span class="neo-card-head-tag" style="color:#ff2d78;">AI-POWERED</span>
                        </div>
                        <div class="neo-card-body">
                            <div class="neo-card-icon">
                                <svg width="28" height="28" fill="none" stroke="#000" stroke-width="2.5" viewBox="0 0 24 24" style="display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            </div>
                            <p>Tempel kode sumber dan dapatkan analisis kerentanan mendalam dengan remediasi yang jelas dan actionable.</p>
                            <a href="{{ route('daftar') }}" class="neo-card-btn" style="--btn-bg:#00d4ff;">▶ SCAN SEKARANG</a>
                        </div>
                    </div>
                    {{-- Card 2: Pemindaian URL --}}
                    <div class="neo-card neo-card-2">
                        <div class="neo-card-head">
                            <span>⟨ SCAN URL /⟩</span>
                            <span class="neo-card-head-tag" style="color:#00d4ff;">THREAT DETECT</span>
                        </div>
                        <div class="neo-card-body">
                            <div class="neo-card-icon">
                                <svg width="28" height="28" fill="none" stroke="#000" stroke-width="2.5" viewBox="0 0 24 24" style="display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                            </div>
                            <p>Pindai URL dan website untuk mendeteksi phishing, malware, dan miskonfigurasi keamanan secara instan.</p>
                            <a href="{{ route('daftar') }}" class="neo-card-btn" style="--btn-bg:#7c3aed; color:#fff;">▶ PINDAI URL</a>
                        </div>
                    </div>
                    {{-- Card 3: Red Team Lab --}}
                    <div class="neo-card neo-card-3">
                        <div class="neo-card-head">
                            <span>⟨ RED TEAM LAB /⟩</span>
                            <span class="neo-card-head-tag" style="color:#a78bfa;">PILAR 3</span>
                        </div>
                        <div class="neo-card-body" style="color:#fff;">
                            <div class="neo-card-icon">
                                <svg width="28" height="28" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24" style="display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                            </div>
                            <p style="color:rgba(255,255,255,0.85);">CVSSv3.1 Calculator interaktif, Attack Path Visualizer berbasis Mermaid.js, dan MITRE ATT&amp;CK heatmap.</p>
                            <a href="{{ route('daftar') }}" class="neo-card-btn" style="--btn-bg:#00ff88;">▶ BUKA LAB</a>
                        </div>
                    </div>
                    {{-- Card 4: Simulasi Serangan --}}
                    <div class="neo-card neo-card-4">
                        <div class="neo-card-head">
                            <span>⟨ ATTACK SIM /⟩</span>
                            <span class="neo-card-head-tag" style="color:#00ff88;">MITRE</span>
                        </div>
                        <div class="neo-card-body">
                            <div class="neo-card-icon">
                                <svg width="28" height="28" fill="none" stroke="#000" stroke-width="2.5" viewBox="0 0 24 24" style="display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <p>Visualisasi skenario serangan berdasarkan kerentanan yang ditemukan dengan framework MITRE ATT&amp;CK Enterprise v15.</p>
                            <a href="{{ route('daftar') }}" class="neo-card-btn" style="--btn-bg:#ff2d78; color:#fff;">▶ SIMULASI</a>
                        </div>
                    </div>
                    {{-- Card 5: Benchmark LLM vs ML --}}
                    <div class="neo-card neo-card-5">
                        <div class="neo-card-head">
                            <span>⟨ LLM vs ML /⟩</span>
                            <span class="neo-card-head-tag" style="color:#f97316;">RISET</span>
                        </div>
                        <div class="neo-card-body">
                            <div class="neo-card-icon">
                                <svg width="28" height="28" fill="none" stroke="#000" stroke-width="2.5" viewBox="0 0 24 24" style="display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <p>Dashboard perbandingan real-time antara prediksi LLM dan Ensemble ML (RF + XGBoost). Confusion matrix, agreement rate, dan export CSV.</p>
                            <a href="{{ route('daftar') }}" class="neo-card-btn" style="--btn-bg:#7c3aed; color:#fff;">▶ BENCHMARK</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== NEW PILLARS SECTION ========== --}}
    <section id="pilar" class="relative z-10 px-4 sm:px-6 py-20 sm:py-24 border-t border-[#1e2d4a] overflow-hidden">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-14">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#7c3aed]/10 border border-[#7c3aed]/20 mb-6">
                    <div class="w-2 h-2 rounded-full bg-[#7c3aed] animate-pulse"></div>
                    <span class="text-xs font-semibold text-[#a78bfa] tracking-wider uppercase">Tiga Pilar Inovasi</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white">
                    Lebih dari Sekadar Scanner Biasa
                </h2>
                <p class="text-[#94a3b8] max-w-xl mx-auto">
                    RedSim menggabungkan AI, Machine Learning, dan framework keamanan industri untuk pengalaman penilaian keamanan yang komprehensif.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Pilar 1 --}}
                <div class="rounded-2xl border border-blue-500/20 bg-gradient-to-br from-blue-950/30 to-slate-950 p-6 hover:border-blue-500/40 transition-colors">
                    <div class="w-11 h-11 rounded-xl bg-blue-500/15 border border-blue-500/25 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3l14 9-14 9V3z"/>
                        </svg>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-500/15 text-blue-400 border border-blue-500/20">PILAR 1</span>
                    </div>
                    <h3 class="text-base font-bold text-white mb-2">Hybrid AI + ML Verdict</h3>
                    <p class="text-sm text-[#94a3b8] leading-relaxed mb-4">
                        Setiap temuan dinilai oleh dua sistem: LLM (Gemini/Groq) dan model <strong class="text-blue-300">Ensemble ML (RF + XGBoost)</strong> terlatih pada 297K+ data CVE. Sistem menghasilkan konsensus severity yang lebih akurat.
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-400">Ensemble ML</span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-400">297K CVE Samples</span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-400">Confidence Score</span>
                    </div>
                </div>

                {{-- Pilar 2 --}}
                <div class="rounded-2xl border border-purple-500/20 bg-gradient-to-br from-purple-950/30 to-slate-950 p-6 hover:border-purple-500/40 transition-colors">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/15 border border-purple-500/25 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-500/15 text-purple-400 border border-purple-500/20">PILAR 2</span>
                    </div>
                    <h3 class="text-base font-bold text-white mb-2">Benchmark LLM vs ML</h3>
                    <p class="text-sm text-[#94a3b8] leading-relaxed mb-4">
                        Dashboard riset dengan <strong class="text-purple-300">Agreement Rate real-time</strong>, Confusion Matrix 4×4, distribusi severity per model, trend 7 hari, dan ekspor CSV untuk analisis akademik.
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-400">Confusion Matrix</span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-400">Agreement Rate</span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-400">Export CSV</span>
                    </div>
                </div>

                {{-- Pilar 3 --}}
                <div class="rounded-2xl border border-red-500/20 bg-gradient-to-br from-red-950/20 to-slate-950 p-6 hover:border-red-500/40 transition-colors">
                    <div class="w-11 h-11 rounded-xl bg-red-500/15 border border-red-500/25 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                        </svg>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-500/15 text-red-400 border border-red-500/20">PILAR 3</span>
                    </div>
                    <h3 class="text-base font-bold text-white mb-2">Red Team Lab</h3>
                    <p class="text-sm text-[#94a3b8] leading-relaxed mb-4">
                        <strong class="text-red-300">CVSSv3.1 Calculator</strong> dengan formula resmi FIRST.org, Attack Path Visualizer (Mermaid.js), MITRE ATT&amp;CK Heatmap interaktif, dan Composite Risk Score transparan.
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-400">CVSSv3.1</span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-400">Kill Chain</span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-slate-800 text-slate-400">MITRE Heatmap</span>
                    </div>
                </div>
            </div>

            {{-- Stats row --}}
            <div class="mt-10 grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                <div class="rounded-xl border border-[#1e2d4a] bg-[#0a0e1a]/60 p-4 sm:p-5 text-center">
                    <div class="text-xl sm:text-2xl font-black text-white">297K+</div>
                    <div class="text-xs text-[#475569] mt-1">CVE Training Data (ML)</div>
                </div>
                <div class="rounded-xl border border-[#1e2d4a] bg-[#0a0e1a]/60 p-4 sm:p-5 text-center">
                    <div class="text-xl sm:text-2xl font-black text-white">14</div>
                    <div class="text-xs text-[#475569] mt-1">MITRE ATT&CK Tactics</div>
                </div>
                <div class="rounded-xl border border-[#1e2d4a] bg-[#0a0e1a]/60 p-4 sm:p-5 text-center">
                    <div class="text-xl sm:text-2xl font-black text-white">3+</div>
                    <div class="text-xs text-[#475569] mt-1">LLM Provider Didukung</div>
                </div>
                <div class="rounded-xl border border-[#1e2d4a] bg-[#0a0e1a]/60 p-4 sm:p-5 text-center">
                    <div class="text-xl sm:text-2xl font-black text-white">CVSSv3.1</div>
                    <div class="text-xs text-[#475569] mt-1">Standard Internasional</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== PRATINJAU ANALISIS SECTION ========== --}}
    <section id="pratinjau" class="relative z-10 px-4 sm:px-6 py-20 sm:py-24 border-t border-[#1e2d4a] overflow-hidden">
        <style>
            /* ===== PRATINJAU ANALISIS STYLES ===== */
            .pratinjau-wrap {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 40px;
                width: 100%;
                box-sizing: border-box;
            }
            @media (min-width: 1024px) {
                .pratinjau-wrap {
                    flex-direction: row;
                    align-items: center;
                    justify-content: center;
                    gap: 64px;
                }
            }
            .pratinjau-info {
                flex: 1;
                max-width: 440px;
                width: 100%;
                text-align: left;
                box-sizing: border-box;
            }
            .pratinjau-info p.subtitle {
                color: #94a3b8;
                line-height: 1.7;
                margin-top: 16px;
                font-size: 0.95rem;
            }
            .pratinjau-info .feature-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
                margin-top: 24px;
            }
            .pratinjau-info .feature-list li {
                display: flex;
                align-items: center;
                gap: 10px;
                font-size: 0.875rem;
                color: #94a3b8;
                list-style: none;
            }
            .pratinjau-info .feature-list li .check-icon {
                flex-shrink: 0;
                width: 20px; height: 20px;
                border-radius: 50%;
                display: flex; align-items: center; justify-content: center;
            }
            /* === The analysis card === */
            .analysis-card {
                width: 100%;
                max-width: 580px;
                background: #0d1620;
                border-radius: 14px;
                border: 1px solid #223340;
                overflow: hidden;
                box-shadow: 0 24px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(82,222,240,0.06);
                flex-shrink: 0;
                font-family: inherit;
                box-sizing: border-box;
            }
            /* Header */
            .ac-header {
                min-height: 52px;
                background: #111e2a;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px 14px;
                border-bottom: 1px solid #223340;
                gap: 8px;
                box-sizing: border-box;
            }
            @media (min-width: 641px) {
                .ac-header { padding: 0 20px; }
            }
            .ac-header-left {
                display: flex;
                align-items: center;
                gap: 8px;
                min-width: 0;
                flex: 1;
            }
            .ac-header-icon {
                width: 17px; height: 17px;
                color: #52def0;
                flex-shrink: 0;
            }
            .ac-header-title {
                font-weight: 600;
                font-size: 13px;
                color: #eef5fa;
                font-family: 'Inter', sans-serif;
                white-space: nowrap;
            }
            .ac-header-sep {
                font-size: 12px;
                color: #718697;
            }
            .ac-header-file {
                font-family: 'JetBrains Mono', monospace;
                font-size: 11px;
                color: #a5b6c6;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .ac-header-badge {
                background: #102d37;
                border-radius: 5px;
                padding: 4px 8px;
                font-family: 'JetBrains Mono', monospace;
                font-weight: 500;
                font-size: 10px;
                color: #52def0;
                letter-spacing: 0.04em;
                flex-shrink: 0;
            }
            /* Nav Tabs */
            .ac-nav {
                height: 48px;
                display: flex;
                align-items: center;
                gap: 16px;
                padding: 0 14px;
                border-bottom: 1px solid #223340;
                overflow-x: auto;
                scrollbar-width: none;
                -webkit-overflow-scrolling: touch;
                box-sizing: border-box;
            }
            .ac-nav::-webkit-scrollbar { display: none; }
            @media (min-width: 641px) {
                .ac-nav { padding: 0 20px; gap: 26px; }
            }
            .ac-tab {
                display: flex;
                align-items: center;
                gap: 7px;
                height: 100%;
                font-size: 12px;
                font-family: 'Inter', sans-serif;
                color: #718697;
                background: transparent;
                border: none;
                border-bottom: 2px solid transparent;
                cursor: pointer;
                padding: 0;
                outline: none;
                transition: color 0.2s;
                font-weight: 400;
                white-space: nowrap;
                flex-shrink: 0;
            }
            .ac-tab:hover { color: #a5b6c6; }
            .ac-tab.active {
                color: #52def0;
                font-weight: 600;
                border-bottom-color: #52def0;
            }
            .ac-tab-icon {
                width: 14px; height: 14px;
                flex-shrink: 0;
            }
            /* Body */
            .ac-body {
                display: flex;
                flex-direction: column;
                gap: 16px;
                padding: 14px;
                box-sizing: border-box;
            }
            @media (min-width: 641px) {
                .ac-body { padding: 20px; }
            }
            /* Code block */
            .ac-code-block {
                background: #09121b;
                border-radius: 8px;
                padding: 14px;
                display: flex;
                flex-direction: column;
                gap: 8px;
                overflow: hidden;
                box-sizing: border-box;
            }
            @media (min-width: 641px) {
                .ac-code-block { padding: 16px; }
            }
            .ac-code-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .ac-code-lang {
                font-family: 'JetBrains Mono', monospace;
                font-size: 10px;
                color: #718697;
                letter-spacing: 0.04em;
            }
            .ac-copy-btn {
                width: 13px; height: 13px;
                background: transparent;
                border: none;
                cursor: pointer;
                padding: 0;
                color: #718697;
                transition: color 0.2s;
                flex-shrink: 0;
            }
            .ac-copy-btn:hover { color: #52def0; }
            .ac-copy-btn svg { width: 13px; height: 13px; }
            .ac-pre {
                margin: 0;
                font-family: 'JetBrains Mono', monospace;
                font-size: 11.5px;
                line-height: 1.85;
                white-space: pre-wrap;
                word-break: break-all;
                overflow-x: auto;
            }
            .ln { color: #718697; user-select: none; }
            .kw { color: #52def0; }
            .st { color: #f57580; }
            .tx { color: #a5b6c6; }
            /* Alert block */
            .ac-alert {
                background: #34202a;
                border-radius: 8px;
                padding: 14px;
                display: flex;
                align-items: flex-start;
                gap: 12px;
                overflow: hidden;
                box-sizing: border-box;
            }
            @media (min-width: 641px) {
                .ac-alert { padding: 16px; gap: 16px; }
            }
            .ac-alert-icon {
                width: 24px; height: 24px;
                flex-shrink: 0;
                color: #f57580;
                margin-top: 1px;
            }
            .ac-alert-body { flex: 1; min-width: 0; }
            .ac-alert-top {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 8px;
                flex-wrap: wrap;
            }
            .ac-alert-title {
                font-family: 'Inter', sans-serif;
                font-size: 14px;
                color: #eef5fa;
                font-weight: 500;
            }
            .ac-alert-severity {
                font-family: 'JetBrains Mono', monospace;
                font-size: 10px;
                color: #f57580;
                font-weight: 600;
                letter-spacing: 0.04em;
                flex-shrink: 0;
            }
            .ac-alert-meta {
                font-family: 'JetBrains Mono', monospace;
                font-size: 10px;
                color: #a5b6c6;
                margin-top: 6px;
            }
            /* Method badges row */
            .ac-methods {
                display: flex;
                gap: 10px;
                flex-direction: column;
            }
            @media (min-width: 520px) {
                .ac-methods {
                    flex-direction: row;
                    gap: 12px;
                }
            }
            .ac-method-card {
                flex: 1;
                min-width: 0;
                border: 1px solid #223340;
                border-radius: 8px;
                padding: 12px;
                display: flex;
                flex-direction: column;
                gap: 7px;
                overflow: hidden;
                box-sizing: border-box;
            }
            .ac-method-top {
                display: flex;
                align-items: center;
                gap: 6px;
            }
            .ac-method-dot {
                width: 5px; height: 5px;
                border-radius: 50%;
                background: #52def0;
                flex-shrink: 0;
            }
            .ac-method-label {
                font-family: 'JetBrains Mono', monospace;
                font-size: 9px;
                color: #52def0;
                font-weight: 400;
                letter-spacing: 0.04em;
            }
            .ac-method-desc {
                font-family: 'Inter', sans-serif;
                font-size: 11px;
                color: #a5b6c6;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            /* Recommendation block */
            .ac-reco {
                display: flex;
                align-items: flex-start;
                gap: 10px;
            }
            .ac-reco-icon {
                width: 16px; height: 16px;
                color: #52def0;
                flex-shrink: 0;
                margin-top: 1px;
            }
            .ac-reco-body { flex: 1; min-width: 0; }
            .ac-reco-title {
                font-family: 'Inter', sans-serif;
                font-weight: 600;
                font-size: 11px;
                color: #eef5fa;
            }
            .ac-reco-text {
                font-family: 'Inter', sans-serif;
                font-size: 11px;
                color: #a5b6c6;
                margin-top: 4px;
                line-height: 1.5;
            }
            /* Footer bar */
            .ac-footer {
                height: 35px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 14px;
                border-top: 1px solid #223340;
                box-sizing: border-box;
            }
            @media (min-width: 641px) {
                .ac-footer { padding: 0 20px; }
            }
            .ac-footer-label {
                font-family: 'JetBrains Mono', monospace;
                font-size: 9px;
                color: #718697;
                letter-spacing: 0.04em;
            }
            /* Floating glow behind card */
            .pratinjau-glow {
                position: absolute;
                width: min(480px, 90vw);
                height: min(480px, 90vw);
                background: radial-gradient(ellipse, rgba(82,222,240,0.07) 0%, transparent 70%);
                border-radius: 50%;
                pointer-events: none;
                right: 0; top: 50%;
                transform: translate(0, -50%);
                z-index: 0;
            }
        </style>

        <div class="max-w-6xl mx-auto relative overflow-hidden">
            <!-- Section heading -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#52def0]/10 border border-[#52def0]/20 mb-6">
                    <div class="w-2 h-2 rounded-full bg-[#52def0] animate-pulse"></div>
                    <span class="text-xs font-semibold text-[#52def0] tracking-wider uppercase">Live Demo Preview</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white">
                    Lihat Cara Kerja
                    <span class="text-white">Analisis Keamanan</span>
                </h2>
                <p class="text-[#94a3b8] max-w-xl mx-auto">
                    Pratinjau interaktif ruang analisis RedSim — scan kode, deteksi URL berbahaya, dan simulasi serangan dalam satu workspace.
                </p>
            </div>

            <div class="pratinjau-wrap">
                <!-- Left: info text -->
                <div class="pratinjau-info">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#52def0]/10 border border-[#52def0]/25 mb-5">
                        <div class="w-1.5 h-1.5 rounded-full bg-[#52def0]"></div>
                        <span class="text-xs font-semibold text-[#52def0] tracking-wider uppercase">Workspace Analisis</span>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold leading-tight text-white">
                        Deteksi kerentanan<br>
                        <span class="text-white">sebelum dieksploitasi</span>
                    </h3>
                    <p class="subtitle">
                        Mesin analisis RedSim menggabungkan kekuatan LLM (Gemini/Groq) dan Machine Learning untuk mengidentifikasi kerentanan kode seperti SQL Injection, XSS, dan IDOR secara akurat.
                    </p>
                    <ul class="feature-list">
                        <li>
                            <span class="check-icon bg-[#52def0]/15">
                                <svg class="w-3 h-3 text-[#52def0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            Analisis kode multi-bahasa (Python, PHP, JS, Go, dst.)
                        </li>
                        <li>
                            <span class="check-icon bg-[#a78bfa]/15">
                                <svg class="w-3 h-3 text-[#a78bfa]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            Klasifikasi severity CVSS dengan confidence score
                        </li>
                        <li>
                            <span class="check-icon bg-[#f57580]/15">
                                <svg class="w-3 h-3 text-[#f57580]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            Rekomendasi remediasi actionable setiap temuan
                        </li>
                        <li>
                            <span class="check-icon bg-[#22c55e]/15">
                                <svg class="w-3 h-3 text-[#22c55e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            Pemetaan teknik MITRE ATT&amp;CK otomatis
                        </li>
                    </ul>
                    <div class="mt-8">
                        <a href="{{ route('daftar') }}" class="cyber-btn-primary" style="display:inline-flex;align-items:center;gap:8px;font-size:0.875rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            Coba Gratis Sekarang
                        </a>
                    </div>
                </div>

                <!-- Right: Analysis Card (converted from React) -->
                <div class="pratinjau-glow" aria-hidden="true"></div>
                <div class="analysis-card" role="region" aria-label="Pratinjau analisis keamanan kode">

                    <!-- Header -->
                    <header class="ac-header">
                        <div class="ac-header-left">
                            <svg class="ac-header-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                            <span class="ac-header-title">Ruang analisis</span>
                            <span class="ac-header-sep" aria-hidden="true">/</span>
                            <span class="ac-header-file">autentikasi.py</span>
                        </div>
                        <span class="ac-header-badge" aria-label="Demo produk">DEMO PRODUK</span>
                    </header>

                    <!-- Nav tabs -->
                    <nav class="ac-nav" aria-label="Mode analisis">
                        <button type="button" class="ac-tab active" data-tab="code" role="tab" aria-selected="true" aria-controls="ac-panel-code" id="ac-tab-code">
                            <svg class="ac-tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                            Analisis kode
                        </button>
                        <button type="button" class="ac-tab" data-tab="url" role="tab" aria-selected="false" aria-controls="ac-panel-url" id="ac-tab-url">
                            Analisis URL
                        </button>
                        <button type="button" class="ac-tab" data-tab="attack" role="tab" aria-selected="false" aria-controls="ac-panel-attack" id="ac-tab-attack">
                            Simulasi serangan
                        </button>
                    </nav>

                    <!-- Panel: Analisis Kode -->
                    <div id="ac-panel-code" role="tabpanel" aria-labelledby="ac-tab-code">
                        <div class="ac-body">
                            <!-- Code snippet -->
                            <article class="ac-code-block">
                                <div class="ac-code-top">
                                    <span class="ac-code-lang">PYTHON / KODE SUMBER</span>
                                    <button type="button" class="ac-copy-btn" id="ac-copy-btn" aria-label="Salin kode sumber" title="Salin kode sumber">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </div>
                                <pre class="ac-pre" aria-label="Contoh kode Python"><code><span class="ln">01  </span><span class="kw">def</span><span class="tx"> cari_pengguna(nama):
</span><span class="ln">02  </span><span class="tx">    kueri = </span><span class="st">"SELECT * FROM users WHERE name = '"</span><span class="tx">
</span><span class="ln">03  </span><span class="tx">    kueri += nama + </span><span class="st">"'"</span><span class="tx">
</span><span class="ln">04  </span><span class="tx">    </span><span class="kw">return</span><span class="tx"> db.execute(kueri)</span></code></pre>
                            </article>

                            <!-- SQL Injection alert -->
                            <article class="ac-alert" aria-labelledby="ac-vuln-title">
                                <div class="ac-alert-icon" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div class="ac-alert-body">
                                    <div class="ac-alert-top">
                                        <h3 id="ac-vuln-title" class="ac-alert-title">Potensi SQL Injection</h3>
                                        <span class="ac-alert-severity">TINGGI</span>
                                    </div>
                                    <p class="ac-alert-meta">CWE-89 · Validasi input &amp; kueri database</p>
                                </div>
                            </article>

                            <!-- Method badges -->
                            <div class="ac-methods">
                                <article class="ac-method-card">
                                    <div class="ac-method-top">
                                        <div class="ac-method-dot" aria-hidden="true"></div>
                                        <span class="ac-method-label">LLM / GEMINI · GROQ</span>
                                    </div>
                                    <div class="ac-method-desc">Konteks kode dianalisis</div>
                                </article>
                                <article class="ac-method-card">
                                    <div class="ac-method-top">
                                        <div class="ac-method-dot" aria-hidden="true"></div>
                                        <span class="ac-method-label">ML / RANDOM FOREST</span>
                                    </div>
                                    <div class="ac-method-desc">Pola kerentanan dikenali</div>
                                </article>
                            </div>

                            <!-- Recommendation -->
                            <aside class="ac-reco">
                                <div class="ac-reco-icon" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <div class="ac-reco-body">
                                    <div class="ac-reco-title">Rekomendasi remediasi</div>
                                    <p class="ac-reco-text">Gunakan kueri berparameter untuk memisahkan input dari perintah SQL.</p>
                                </div>
                            </aside>
                        </div>
                    </div>

                    <!-- Panel: Analisis URL (placeholder) -->
                    <div id="ac-panel-url" role="tabpanel" aria-labelledby="ac-tab-url" style="display:none;">
                        <div class="ac-body" style="min-height: 280px; display:flex; align-items:center; justify-content:center;">
                            <div style="text-align:center; color:#718697;">
                                <svg style="width:36px;height:36px;margin:0 auto 10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                <p style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#52def0;margin-bottom:6px;">ANALISIS URL AKTIF</p>
                                <p style="font-size:12px;">Masukkan URL untuk mendeteksi phishing,<br>malware, dan miskonfigurasi keamanan.</p>
                                <a href="{{ route('daftar') }}" style="display:inline-flex;align-items:center;gap:6px;margin-top:16px;padding:8px 20px;background:#102d37;border:1px solid #223340;border-radius:8px;font-family:'JetBrains Mono',monospace;font-size:11px;color:#52def0;text-decoration:none;transition:all 0.2s;">
                                    Coba Sekarang →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Panel: Simulasi Serangan (placeholder) -->
                    <div id="ac-panel-attack" role="tabpanel" aria-labelledby="ac-tab-attack" style="display:none;">
                        <div class="ac-body" style="min-height: 280px; display:flex; align-items:center; justify-content:center;">
                            <div style="text-align:center; color:#718697;">
                                <svg style="width:36px;height:36px;margin:0 auto 10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <p style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#f57580;margin-bottom:6px;">MITRE ATT&amp;CK ENGINE</p>
                                <p style="font-size:12px;">Visualisasi serangan berbasis kerentanan yang<br>ditemukan dari framework MITRE ATT&amp;CK v15.</p>
                                <a href="{{ route('daftar') }}" style="display:inline-flex;align-items:center;gap:6px;margin-top:16px;padding:8px 20px;background:#2a1020;border:1px solid #3d2030;border-radius:8px;font-family:'JetBrains Mono',monospace;font-size:11px;color:#f57580;text-decoration:none;transition:all 0.2s;">
                                    Simulasi Sekarang →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Footer bar -->
                    <footer class="ac-footer">
                        <span class="ac-footer-label">ILUSTRASI HASIL · BUKAN DATA PEMINDAIAN LANGSUNG</span>
                        <svg style="width:12px;height:12px;color:#718697;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </footer>
                </div>
            </div>
        </div>

        <script>
        (function() {
            // Tab switching
            var tabs = document.querySelectorAll('.ac-tab');
            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    var target = this.dataset.tab;
                    tabs.forEach(function(t) {
                        t.classList.remove('active');
                        t.setAttribute('aria-selected', 'false');
                    });
                    this.classList.add('active');
                    this.setAttribute('aria-selected', 'true');

                    // Hide all panels
                    ['code','url','attack'].forEach(function(id) {
                        var panel = document.getElementById('ac-panel-' + id);
                        if (panel) panel.style.display = 'none';
                    });
                    var activePanel = document.getElementById('ac-panel-' + target);
                    if (activePanel) activePanel.style.display = '';
                });
            });

            // Copy button
            var copyBtn = document.getElementById('ac-copy-btn');
            if (copyBtn) {
                copyBtn.addEventListener('click', function() {
                    var code = "def cari_pengguna(nama):\n    kueri = \"SELECT * FROM users WHERE name = '\"\n    kueri += nama + \"'\"\n    return db.execute(kueri)";
                    navigator.clipboard.writeText(code).then(function() {
                        copyBtn.setAttribute('aria-label', 'Kode berhasil disalin');
                        copyBtn.setAttribute('title', 'Kode berhasil disalin');
                        copyBtn.querySelector('svg').style.color = '#52def0';
                        setTimeout(function() {
                            copyBtn.setAttribute('aria-label', 'Salin kode sumber');
                            copyBtn.setAttribute('title', 'Salin kode sumber');
                            copyBtn.querySelector('svg').style.color = '';
                        }, 1500);
                    }).catch(function() {});
                });
            }
        })();
        </script>
    </section>

    {{-- ========== KNOWLEDGE BASE (Terminal Style) ========== --}}
    <section id="kb" class="relative z-10 px-4 sm:px-6 py-16 sm:py-24 border-t border-[#1e2d4a] overflow-hidden">
        <div class="max-w-4xl mx-auto text-center">
            <div class="relative inline-block mb-3">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#00d4ff]/10 border border-[#00d4ff]/20">
                    <div class="relative">
                        <div class="w-2 h-2 rounded-full bg-[#00d4ff]"></div>
                        <div class="absolute inset-0 w-2 h-2 rounded-full bg-[#00d4ff] animate-ping opacity-60"></div>
                    </div>
                    <span class="text-xs font-semibold text-[#00d4ff] tracking-wider uppercase">Knowledge Base</span>
                </div>
            </div>
            <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white">
                Didukung
                <span class="text-white">Knowledge Base Terpercaya</span>
            </h2>
            <p class="text-[#94a3b8] mb-4 max-w-lg mx-auto">
                Analisis kami berbasis data dari sumber keamanan siber internasional terkemuka.
            </p>
            <div class="flex justify-center items-center gap-4 sm:gap-8 mb-10 sm:mb-12 flex-wrap">
                <div class="kb-stat text-center" style="animation-delay:0.2s">
                    <div class="text-2xl font-black text-white">200K+</div>
                    <div class="text-xs text-[#475569] mt-0.5">CVE Entries</div>
                </div>
                <div class="w-px h-8 bg-[#1e2d4a] hidden sm:block"></div>
                <div class="kb-stat text-center" style="animation-delay:0.35s">
                    <div class="text-2xl font-black text-white">14+</div>
                    <div class="text-xs text-[#475569] mt-0.5">MITRE Tactics</div>
                </div>
                <div class="w-px h-8 bg-[#1e2d4a] hidden sm:block"></div>
                <div class="kb-stat text-center" style="animation-delay:0.5s">
                    <div class="text-2xl font-black text-white">1000+</div>
                    <div class="text-xs text-[#475569] mt-0.5">Kerentanan CWE</div>
                </div>
            </div>

            {{-- Terminal Card --}}
            <div class="terminal-card">
                <div class="terminal-titlebar">
                    <div class="terminal-dots">
                        <div class="terminal-dot red"></div>
                        <div class="terminal-dot yellow"></div>
                        <div class="terminal-dot green"></div>
                    </div>
                    <div class="terminal-title">root@redsim: ~/knowledge-base</div>
                    <div style="width:52px;"></div>
                </div>
                <div class="terminal-body">
                    <div class="terminal-line">
                        <span class="terminal-prompt">root@redsim:~$</span>
                        <span class="terminal-cmd" id="term-cmd-1"></span>
                    </div>
                    <div class="terminal-output" id="term-output-1" style="display:none;">
                        <div class="terminal-grid" style="margin-bottom:10px;">
                            <div>
                                <div class="terminal-item"><span class="term-cyan" style="font-size:0.65rem;font-weight:700;letter-spacing:0.02em;">[DIR]</span>&nbsp;<span class="terminal-item-name term-cyan">MITRE_ATTACK/</span></div>
                                <div class="terminal-item-desc">Taktik &amp; teknik serangan</div>
                            </div>
                            <div>
                                <div class="terminal-item"><span class="term-yellow" style="font-size:0.65rem;font-weight:700;letter-spacing:0.02em;">[DIR]</span>&nbsp;<span class="terminal-item-name term-yellow">NVD_CVE/</span></div>
                                <div class="terminal-item-desc">200K+ kerentanan nasional</div>
                            </div>
                            <div>
                                <div class="terminal-item"><span class="term-green" style="font-size:0.65rem;font-weight:700;letter-spacing:0.02em;">[DIR]</span>&nbsp;<span class="terminal-item-name term-green">OWASP/</span></div>
                                <div class="terminal-item-desc">Standar keamanan web global</div>
                            </div>
                            <div>
                                <div class="terminal-item"><span class="term-pink" style="font-size:0.65rem;font-weight:700;letter-spacing:0.02em;">[DIR]</span>&nbsp;<span class="terminal-item-name term-pink">CISA_KEV/</span></div>
                                <div class="terminal-item-desc">Kerentanan dieksploitasi aktif</div>
                            </div>
                            <div>
                                <div class="terminal-item"><span class="term-purple" style="font-size:0.65rem;font-weight:700;letter-spacing:0.02em;">[DIR]</span>&nbsp;<span class="terminal-item-name term-purple">CWE/</span></div>
                                <div class="terminal-item-desc">Enumerasi kelemahan umum</div>
                            </div>
                            <div>
                                <div class="terminal-item"><span class="term-orange" style="font-size:0.65rem;font-weight:700;letter-spacing:0.02em;">[DIR]</span>&nbsp;<span class="terminal-item-name term-orange">CAPEC/</span></div>
                                <div class="terminal-item-desc">Pola serangan &amp; eksploitasi</div>
                            </div>
                        </div>
                        <div style="font-size:0.68rem; color:rgba(255,255,255,0.22); text-align:left; font-family:'JetBrains Mono',monospace; margin-bottom:8px;">
                            6 directories, 0 files &nbsp;|&nbsp; total: 200,000+ records indexed
                        </div>
                    </div>
                    <div class="terminal-line" id="term-line-2" style="display:none;">
                        <span class="terminal-prompt">root@redsim:~$</span>
                        <span class="terminal-cmd" id="term-cmd-2"></span>
                    </div>
                    <div class="terminal-output" id="term-output-2" style="display:none;">
                        <div style="font-family:'JetBrains Mono',monospace; font-size:0.73rem; text-align:left; line-height:1.9;">
                            <div><span class="term-green">STATUS</span>  <span class="term-white">:</span> <span class="term-cyan">ONLINE</span> <span class="term-dim">— all systems operational</span></div>
                            <div><span class="term-green">SYNC</span>    <span class="term-white">:</span> <span class="term-yellow">{{ now()->format('Y-m-d H:i') }} UTC</span></div>
                            <div><span class="term-green">ENTRIES</span> <span class="term-white">:</span> <span class="term-pink">200,847</span> <span class="term-dim">CVE |</span> <span class="term-purple">1,284</span> <span class="term-dim">CWE |</span> <span class="term-orange">568</span> <span class="term-dim">CAPEC</span></div>
                            <div><span class="term-green">MITRE</span>   <span class="term-white">:</span> <span class="term-cyan">14 tactics</span> <span class="term-dim">|</span> <span class="term-cyan">196 techniques</span></div>
                        </div>
                    </div>
                    <div class="terminal-line" id="term-cursor-line">
                        <span class="terminal-prompt">root@redsim:~$</span>
                        <span class="terminal-cursor"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========== FOOTER ========== --}}
    <footer class="relative z-10 px-4 sm:px-6 py-10 border-t border-[#1e2d4a] overflow-hidden" style="background:rgba(5,8,22,0.4)!important;backdrop-filter:blur(8px);">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center gap-2 mb-3">
                        <img src="{{ asset('logo-redsim.svg') }}" alt="RedSim" class="w-6 h-6">
                        <span class="font-black text-sm" style="background:linear-gradient(90deg,#00d4ff,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">RedSim</span>
                    </div>
                    <p class="text-xs text-[#475569] leading-relaxed">Platform keamanan siber hybrid AI+ML untuk analisis kerentanan, simulasi serangan, dan riset keamanan.</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider mb-3">Fitur</p>
                    <div class="space-y-2">
                        <a href="{{ route('daftar') }}" class="block text-xs text-[#475569] hover:text-[#94a3b8] transition-colors">Analisis URL</a>
                        <a href="{{ route('daftar') }}" class="block text-xs text-[#475569] hover:text-[#94a3b8] transition-colors">Analisis Kode</a>
                        <a href="{{ route('daftar') }}" class="block text-xs text-[#475569] hover:text-[#94a3b8] transition-colors">Simulasi Serangan</a>
                        <a href="{{ route('daftar') }}" class="block text-xs text-[#475569] hover:text-[#94a3b8] transition-colors">Red Team Lab</a>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider mb-3">Riset</p>
                    <div class="space-y-2">
                        <a href="{{ route('daftar') }}" class="block text-xs text-[#475569] hover:text-[#94a3b8] transition-colors">Benchmark LLM vs ML</a>
                        <a href="{{ route('daftar') }}" class="block text-xs text-[#475569] hover:text-[#94a3b8] transition-colors">CVSSv3.1 Calculator</a>
                        <a href="{{ route('daftar') }}" class="block text-xs text-[#475569] hover:text-[#94a3b8] transition-colors">MITRE ATT&CK Matrix</a>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748b] uppercase tracking-wider mb-3">Komunitas</p>
                    <div class="space-y-2">
                        <a href="/edukasi/leaderboard" class="block text-xs text-[#475569] hover:text-[#94a3b8] transition-colors">Leaderboard</a>
                        <a href="{{ route('edukasi.index') }}" class="block text-xs text-[#475569] hover:text-[#94a3b8] transition-colors">Hub Edukasi</a>
                        <a href="{{ route('chat-ai') }}" class="block text-xs text-[#475569] hover:text-[#94a3b8] transition-colors">Chat AI</a>
                    </div>
                </div>
            </div>
            <div class="border-t border-[#1e2d4a] pt-6 flex flex-col md:flex-row items-center justify-between gap-3">
                <p class="text-xs text-[#475569]">&copy; {{ date('Y') }} RedSim. Platform Keamanan Siber Hybrid AI + ML.</p>
                <div class="flex items-center gap-4">
                    <span class="text-[10px] px-2 py-1 rounded-full bg-green-500/10 text-green-400 border border-green-500/20">● Sistem Online</span>
                    <span class="text-xs text-[#475569]">Knowledge Base diperbarui {{ now()->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- ========== SCRIPTS ========== --}}
    {{-- GSAP for CardSwap --}}
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    {{-- LightRays uses pure vanilla WebGL (no CDN needed) --}}
    <script src="{{ asset('js/animations.js') }}"></script>
    <script src="{{ asset('js/landing-texts.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Init Splash Screen
            initSplash(function() {});

            // 2. Init Ghost Fibers Background (WebGL1)
            (function() {
                var canvas = document.getElementById('ghost-fibers-canvas');
                if (!canvas) return;

                // Force WebGL1 — avoids GLSL ES 3.00 attribute/in keyword mismatch
                var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                if (!gl) { console.warn('GridScan: WebGL not supported'); return; }
                
                var ext = gl.getExtension('OES_standard_derivatives');
                if (!ext) { console.warn('GridScan: OES_standard_derivatives not supported'); }

                // ---- GLSL ES 1.00 Vertex Shader ----
                var vsSource = [
                    'attribute vec2 aPos;',
                    'void main(){gl_Position=vec4(aPos,0.0,1.0);}'
                ].join('\n');

                // ---- GLSL ES 1.00 Fragment Shader ----
                var fsSource = [
                    '#extension GL_OES_standard_derivatives : enable',
                    'precision highp float;',
                    'uniform vec3 iResolution;',
                    'uniform float iTime;',
                    'uniform vec2 uSkew;',
                    'uniform float uTilt;',
                    'uniform float uYaw;',
                    'uniform float uLineThickness;',
                    'uniform vec3 uLinesColor;',
                    'uniform vec3 uScanColor;',
                    'uniform float uGridScale;',
                    'uniform float uLineStyle;',
                    'uniform float uLineJitter;',
                    'uniform float uScanOpacity;',
                    'uniform float uScanDirection;',
                    'uniform float uNoise;',
                    'uniform float uBloomOpacity;',
                    'uniform float uScanGlow;',
                    'uniform float uScanSoftness;',
                    'uniform float uPhaseTaper;',
                    'uniform float uScanDuration;',
                    'uniform float uScanDelay;',
                    'uniform float uLightMode;',
                    'uniform float uScanStarts[8];',
                    'uniform float uScanCount;',
                    'const int MAX_SCANS = 8;',
                    'float smoother01(float a, float b, float x){',
                    '  float t = clamp((x - a) / max(1e-5, (b - a)), 0.0, 1.0);',
                    '  return t * t * t * (t * (t * 6.0 - 15.0) + 10.0);',
                    '}',
                    'void mainImage(out vec4 fragColor, in vec2 fragCoord) {',
                    '    vec2 p = (2.0 * fragCoord - iResolution.xy) / iResolution.y;',
                    '    vec3 ro = vec3(0.0);',
                    '    vec3 rd = normalize(vec3(p, 2.0));',
                    '    float cR = cos(uTilt), sR = sin(uTilt);',
                    '    rd.xy = mat2(cR, -sR, sR, cR) * rd.xy;',
                    '    float cY = cos(uYaw), sY = sin(uYaw);',
                    '    rd.xz = mat2(cY, -sY, sY, cY) * rd.xz;',
                    '    vec2 skew = clamp(uSkew, vec2(-0.7), vec2(0.7));',
                    '    rd.xy += skew * rd.z;',
                    '    vec3 color = vec3(0.0);',
                    '    float minT = 1e20;',
                    '    float gridScale = max(1e-5, uGridScale);',
                    '    float fadeStrength = 2.0;',
                    '    vec2 gridUV = vec2(0.0);',
                    '    float hitIsY = 1.0;',
                    '    for (int i = 0; i < 4; i++) {',
                    '        float isY = float(i < 2);',
                    '        float pos = mix(-0.2, 0.2, float(i)) * isY + mix(-0.5, 0.5, float(i - 2)) * (1.0 - isY);',
                    '        float num = pos - (isY * ro.y + (1.0 - isY) * ro.x);',
                    '        float den = isY * rd.y + (1.0 - isY) * rd.x;',
                    '        float t = num / den;',
                    '        vec3 h = ro + rd * t;',
                    '        float depthBoost = smoothstep(0.0, 3.0, h.z);',
                    '        h.xy += skew * 0.15 * depthBoost;',
                    '        bool use = t > 0.0 && t < minT;',
                    '        gridUV = use ? mix(h.zy, h.xz, isY) / gridScale : gridUV;',
                    '        minT = use ? t : minT;',
                    '        hitIsY = use ? isY : hitIsY;',
                    '    }',
                    '    vec3 hit = ro + rd * minT;',
                    '    float dist = length(hit - ro);',
                    '    float jitterAmt = clamp(uLineJitter, 0.0, 1.0);',
                    '    if (jitterAmt > 0.0) {',
                    '        vec2 j = vec2(sin(gridUV.y * 2.7 + iTime * 1.8), cos(gridUV.x * 2.3 - iTime * 1.6)) * (0.15 * jitterAmt);',
                    '        gridUV += j;',
                    '    }',
                    '    float fx = fract(gridUV.x);',
                    '    float fy = fract(gridUV.y);',
                    '    float ax = min(fx, 1.0 - fx);',
                    '    float ay = min(fy, 1.0 - fy);',
                    '    float wx = fwidth(gridUV.x);',
                    '    float wy = fwidth(gridUV.y);',
                    '    float halfPx = max(0.0, uLineThickness) * 0.5;',
                    '    float tx = halfPx * wx;',
                    '    float ty = halfPx * wy;',
                    '    float aax = wx;',
                    '    float aay = wy;',
                    '    float lineX = 1.0 - smoothstep(tx, tx + aax, ax);',
                    '    float lineY = 1.0 - smoothstep(ty, ty + aay, ay);',
                    '    float primaryMask = max(lineX, lineY);',
                    '    vec2 gridUV2 = (hitIsY > 0.5 ? hit.xz : hit.zy) / gridScale;',
                    '    if (jitterAmt > 0.0) {',
                    '        vec2 j2 = vec2(cos(gridUV2.y * 2.1 - iTime * 1.4), sin(gridUV2.x * 2.5 + iTime * 1.7)) * (0.15 * jitterAmt);',
                    '        gridUV2 += j2;',
                    '    }',
                    '    float fx2 = fract(gridUV2.x);',
                    '    float fy2 = fract(gridUV2.y);',
                    '    float ax2 = min(fx2, 1.0 - fx2);',
                    '    float ay2 = min(fy2, 1.0 - fy2);',
                    '    float wx2 = fwidth(gridUV2.x);',
                    '    float wy2 = fwidth(gridUV2.y);',
                    '    float tx2 = halfPx * wx2;',
                    '    float ty2 = halfPx * wy2;',
                    '    float aax2 = wx2;',
                    '    float aay2 = wy2;',
                    '    float lineX2 = 1.0 - smoothstep(tx2, tx2 + aax2, ax2);',
                    '    float lineY2 = 1.0 - smoothstep(ty2, ty2 + aay2, ay2);',
                    '    float altMask = max(lineX2, lineY2);',
                    '    float edgeDistX = min(abs(hit.x - (-0.5)), abs(hit.x - 0.5));',
                    '    float edgeDistY = min(abs(hit.y - (-0.2)), abs(hit.y - 0.2));',
                    '    float edgeDist = mix(edgeDistY, edgeDistX, hitIsY);',
                    '    float edgeGate = 1.0 - smoothstep(gridScale * 0.5, gridScale * 2.0, edgeDist);',
                    '    altMask *= edgeGate;',
                    '    float lineMask = max(primaryMask, altMask);',
                    '    float fade = exp(-dist * fadeStrength);',
                    '    float dur = max(0.05, uScanDuration);',
                    '    float del = max(0.0, uScanDelay);',
                    '    float scanZMax = 2.0;',
                    '    float widthScale = max(0.1, uScanGlow);',
                    '    float sigma = max(0.001, 0.18 * widthScale * uScanSoftness);',
                    '    float sigmaA = sigma * 2.0;',
                    '    float combinedPulse = 0.0;',
                    '    float combinedAura = 0.0;',
                    '    float cycle = dur + del;',
                    '    float tCycle = mod(iTime, cycle);',
                    '    float scanPhase = clamp((tCycle - del) / dur, 0.0, 1.0);',
                    '    float phase = scanPhase;',
                    '    if (uScanDirection > 0.5 && uScanDirection < 1.5) {',
                    '        phase = 1.0 - phase;',
                    '    } else if (uScanDirection > 1.5) {',
                    '        float t2 = mod(max(0.0, iTime - del), 2.0 * dur);',
                    '        phase = (t2 < dur) ? (t2 / dur) : (1.0 - (t2 - dur) / dur);',
                    '    }',
                    '    float scanZ = phase * scanZMax;',
                    '    float dz = abs(hit.z - scanZ);',
                    '    float lineBand = exp(-0.5 * (dz * dz) / (sigma * sigma));',
                    '    float taper = clamp(uPhaseTaper, 0.0, 0.49);',
                    '    float headW = taper;',
                    '    float tailW = taper;',
                    '    float headFade = smoother01(0.0, headW, phase);',
                    '    float tailFade = 1.0 - smoother01(1.0 - tailW, 1.0, phase);',
                    '    float phaseWindow = headFade * tailFade;',
                    '    float pulseBase = lineBand * phaseWindow;',
                    '    combinedPulse += pulseBase * clamp(uScanOpacity, 0.0, 1.0);',
                    '    float auraBand = exp(-0.5 * (dz * dz) / (sigmaA * sigmaA));',
                    '    combinedAura += (auraBand * 0.25) * phaseWindow * clamp(uScanOpacity, 0.0, 1.0);',
                    '    ',
                    '    float lineVis = lineMask;',
                    '    vec3 gridCol = uLinesColor * lineVis * fade;',
                    '    vec3 scanCol = uScanColor * combinedPulse;',
                    '    vec3 scanAura = uScanColor * combinedAura;',
                    '    color = gridCol + scanCol + scanAura;',
                    '    float n = fract(sin(dot(gl_FragCoord.xy + vec2(iTime * 123.4), vec2(12.9898,78.233))) * 43758.5453123);',
                    '    color += (n - 0.5) * uNoise;',
                    '    color = clamp(color, 0.0, 1.0);',
                    '    float alpha = clamp(max(lineVis, combinedPulse), 0.0, 1.0);',
                    '    float gx = 1.0 - smoothstep(tx * 2.0, tx * 2.0 + aax * 2.0, ax);',
                    '    float gy = 1.0 - smoothstep(ty * 2.0, ty * 2.0 + aay * 2.0, ay);',
                    '    float halo = max(gx, gy) * fade;',
                    '    alpha = max(alpha, halo * clamp(uBloomOpacity, 0.0, 1.0));',
                    '    fragColor = vec4(color, alpha);',
                    '}',
                    'void main(){',
                    '    vec4 c;',
                    '    mainImage(c, gl_FragCoord.xy);',
                    '    gl_FragColor = c;',
                    '}'
                ].join('\n');

                function compileShader(type, src) {
                    var s = gl.createShader(type);
                    gl.shaderSource(s, src);
                    gl.compileShader(s);
                    if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) {
                        console.error('GridScan shader error:', gl.getShaderInfoLog(s));
                        return null;
                    }
                    return s;
                }

                var vs = compileShader(gl.VERTEX_SHADER, vsSource);
                var fs = compileShader(gl.FRAGMENT_SHADER, fsSource);
                if (!vs || !fs) return;

                var prog = gl.createProgram();
                gl.attachShader(prog, vs);
                gl.attachShader(prog, fs);
                gl.linkProgram(prog);
                if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) {
                    console.error('GridScan link error:', gl.getProgramInfoLog(prog));
                    return;
                }
                gl.useProgram(prog);

                var buf = gl.createBuffer();
                gl.bindBuffer(gl.ARRAY_BUFFER, buf);
                gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1, 3,-1, -1,3]), gl.STATIC_DRAW);

                var aPos = gl.getAttribLocation(prog, 'aPos');
                if (aPos >= 0) {
                    gl.enableVertexAttribArray(aPos);
                    gl.vertexAttribPointer(aPos, 2, gl.FLOAT, false, 0, 0);
                }

                var loc_iResolution = gl.getUniformLocation(prog, 'iResolution');
                var loc_iTime = gl.getUniformLocation(prog, 'iTime');
                var loc_uSkew = gl.getUniformLocation(prog, 'uSkew');
                var loc_uTilt = gl.getUniformLocation(prog, 'uTilt');
                var loc_uYaw = gl.getUniformLocation(prog, 'uYaw');
                var loc_uLineThickness = gl.getUniformLocation(prog, 'uLineThickness');
                var loc_uLinesColor = gl.getUniformLocation(prog, 'uLinesColor');
                var loc_uScanColor = gl.getUniformLocation(prog, 'uScanColor');
                var loc_uGridScale = gl.getUniformLocation(prog, 'uGridScale');
                var loc_uLineStyle = gl.getUniformLocation(prog, 'uLineStyle');
                var loc_uLineJitter = gl.getUniformLocation(prog, 'uLineJitter');
                var loc_uScanOpacity = gl.getUniformLocation(prog, 'uScanOpacity');
                var loc_uScanDirection = gl.getUniformLocation(prog, 'uScanDirection');
                var loc_uNoise = gl.getUniformLocation(prog, 'uNoise');
                var loc_uBloomOpacity = gl.getUniformLocation(prog, 'uBloomOpacity');
                var loc_uScanGlow = gl.getUniformLocation(prog, 'uScanGlow');
                var loc_uScanSoftness = gl.getUniformLocation(prog, 'uScanSoftness');
                var loc_uPhaseTaper = gl.getUniformLocation(prog, 'uPhaseTaper');
                var loc_uScanDuration = gl.getUniformLocation(prog, 'uScanDuration');
                var loc_uScanDelay = gl.getUniformLocation(prog, 'uScanDelay');
                var loc_uLightMode = gl.getUniformLocation(prog, 'uLightMode');
                var loc_uScanCount = gl.getUniformLocation(prog, 'uScanCount');

                // Configuration matching GridScan React parameters (but red theme)
                gl.uniform1f(loc_uLineThickness, 1.0);
                gl.uniform3f(loc_uLinesColor, 0.20, 0.12, 0.12); // darker red-grey
                gl.uniform3f(loc_uScanColor, 0.9, 0.2, 0.3); // vivid red/pink
                gl.uniform1f(loc_uGridScale, 0.1);
                gl.uniform1f(loc_uLineStyle, 0.0); // solid
                gl.uniform1f(loc_uLineJitter, 0.1);
                gl.uniform1f(loc_uScanOpacity, 0.4);
                gl.uniform1f(loc_uScanDirection, 2.0); // pingpong
                gl.uniform1f(loc_uNoise, 0.01);
                gl.uniform1f(loc_uBloomOpacity, 0.6);
                gl.uniform1f(loc_uScanGlow, 0.5);
                gl.uniform1f(loc_uScanSoftness, 2.0);
                gl.uniform1f(loc_uPhaseTaper, 0.9);
                gl.uniform1f(loc_uScanDuration, 2.0);
                gl.uniform1f(loc_uScanDelay, 2.0);
                gl.uniform1f(loc_uLightMode, 0.0);
                gl.uniform1f(loc_uScanCount, 0.0);

                var targetSkew = [0, 0], currSkew = [0, 0];
                var targetTilt = 0, currTilt = 0;
                var targetYaw = 0, currYaw = 0;

                document.addEventListener('mousemove', function(e) {
                    var nx = (e.clientX / window.innerWidth) * 2 - 1;
                    var ny = -(e.clientY / window.innerHeight) * 2 + 1;
                    var sensitivity = 0.55;
                    var skewScale = 0.06 + (0.2 - 0.06) * sensitivity;
                    var tiltScale = 0.12 + (0.3 - 0.12) * sensitivity;
                    var yawScale = 0.1 + (0.28 - 0.1) * sensitivity;
                    targetSkew = [nx * skewScale, ny * skewScale * 1.4];
                    targetTilt = -ny * tiltScale;
                    targetYaw = nx * yawScale;
                });

                function resize() {
                    var w = window.innerWidth;
                    var h = window.innerHeight;
                    var dpr = Math.min(window.devicePixelRatio || 1, 2);
                    canvas.width  = w * dpr;
                    canvas.height = h * dpr;
                    gl.viewport(0, 0, canvas.width, canvas.height);
                    gl.uniform3f(loc_iResolution, canvas.width, canvas.height, dpr);
                }
                window.addEventListener('resize', resize);
                resize();

                var startTime = performance.now();
                function render() {
                    var now = performance.now();
                    var time = (now - startTime) * 0.001;
                    
                    // Smooth damp
                    var dt = 0.016;
                    currSkew[0] += (targetSkew[0] - currSkew[0]) * 5.0 * dt;
                    currSkew[1] += (targetSkew[1] - currSkew[1]) * 5.0 * dt;
                    currTilt += (targetTilt - currTilt) * 5.0 * dt;
                    currYaw += (targetYaw - currYaw) * 5.0 * dt;

                    gl.uniform1f(loc_iTime, time);
                    gl.uniform2f(loc_uSkew, currSkew[0], currSkew[1]);
                    gl.uniform1f(loc_uTilt, currTilt);
                    gl.uniform1f(loc_uYaw, currYaw);

                    gl.drawArrays(gl.TRIANGLES, 0, 3);
                    requestAnimationFrame(render);
                }
                requestAnimationFrame(render);
            })();

            // 3. Init Decrypted Text Loop
            initDecryptedTextLoop(
                document.getElementById('welcome-texttype'),
                [
                    'Selamat datang di RedSim — platform keamanan siber hybrid AI + ML.',
                    'Analisis URL dan kode sumber secara otomatis, didukung LLM dan Ensemble ML.',
                    'Temukan celah keamanan sebelum peretas menemukannya.',
                    'Benchmark LLM vs ML real-time — kontribusi riset keamanan siber nyata.',
                    'Red Team Lab: CVSSv3.1 Calculator, Attack Path Visualizer, MITRE ATT&CK Heatmap.'
                ],
                { speed: 50, pauseDuration: 2500, maxIterations: 15 }
            );

            // 4. Init TechText
            initTechText(document.getElementById('tech-text-container'), {
                text: 'Penilaian Keamanan',
                fontSize: 60, // scaled down to fit typically h1 sizing without going too big
                color: '#ffffff',
                accentColor: '#E5334C', // red accent
                dashLength: 4,
                dashGap: 2,
                reach: 150,
                specks: 10
            });

            // 5. Init StrokeText
            initStrokeText(document.getElementById('stroke-text-container'), {
                text: 'Sistem Anda',
                fontSize: 50,
                strokeColor: '#E5334C', // red accent
                fillColor: '#ffffff',
                strokeWidth: 1.5,
                drawDuration: 2.0,
                fillDelay: 0.2
            });


            // 7. Init KB Animation
            initKBAnimation();

            // 5. Animate KB stats when in view
            var statsObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                    }
                });
            }, { threshold: 0.2 });
            document.querySelectorAll('.kb-stat').forEach(function(el) {
                el.style.animationPlayState = 'paused';
                statsObserver.observe(el);
            });

            // 6. Neo Brutalist Card Swap
            (function() {
                var container = document.getElementById('neo-card-swap');
                if (!container || typeof gsap === 'undefined') return;
                var cards = Array.from(container.querySelectorAll('.neo-card'));
                if (!cards.length) return;
                
                function getDims() {
                    var card = cards[0];
                    var w = card ? card.offsetWidth : 350;
                    var h = card ? card.offsetHeight : 210;
                    return { W: w || 350, H: h || 210 };
                }

                var dims = getDims();
                var W = dims.W, H = dims.H;

                cards.forEach(function(card, i) {
                    gsap.set(card, {
                        x: -W/2, y: -H/2 + i * 8,
                        rotation: (i - 2) * 2.5,
                        zIndex: cards.length - i,
                        opacity: i === 0 ? 1 : 0.82 - i * 0.1
                    });
                });
                var current = 0;
                function swapNext() {
                    var total = cards.length;
                    var topCard = cards[current % total];
                    var isMobile = window.innerWidth <= 640;
                    var flyX = isMobile ? Math.min(260, window.innerWidth * 0.65) : 520;
                    var currentDims = getDims();
                    var curW = currentDims.W;
                    var curH = currentDims.H;

                    gsap.to(topCard, {
                        x: -curW/2 + flyX, y: -curH/2 - (isMobile ? 40 : 80),
                        rotation: 18, opacity: 0,
                        duration: 0.45, ease: 'power2.in',
                        onComplete: function() {
                            gsap.set(topCard, {
                                x: -curW/2, y: -curH/2 + (total - 1) * 8,
                                rotation: (total - 3) * 2.5,
                                zIndex: 1, opacity: 0.55
                            });
                            current = (current + 1) % total;
                            for (var j = 0; j < total; j++) {
                                var idx = (current + j) % total;
                                var card = cards[idx];
                                gsap.to(card, {
                                    x: -curW/2, y: -curH/2 + j * 8,
                                    rotation: (j - 2) * 2.5,
                                    zIndex: total - j,
                                    opacity: j === 0 ? 1 : 0.82 - j * 0.1,
                                    duration: 0.4, ease: 'back.out(1.2)',
                                    delay: j * 0.04
                                });
                            }
                        }
                    });
                }
                var swapInterval = setInterval(swapNext, 3500);

                window.addEventListener('resize', function() {
                    var newDims = getDims();
                    var curW = newDims.W;
                    var curH = newDims.H;
                    var total = cards.length;
                    for (var j = 0; j < total; j++) {
                        var idx = (current + j) % total;
                        var card = cards[idx];
                        gsap.set(card, {
                            x: -curW/2, y: -curH/2 + j * 8
                        });
                    }
                });
            })();

            // 7. Terminal Typewriter
            (function() {
                function typeText(el, text, speed, cb) {
                    var i = 0; el.textContent = '';
                    var t = setInterval(function() {
                        el.textContent += text[i]; i++;
                        if (i >= text.length) { clearInterval(t); if (cb) setTimeout(cb, 350); }
                    }, speed);
                }
                var termObs = new IntersectionObserver(function(entries) {
                    if (!entries[0].isIntersecting) return;
                    termObs.disconnect();
                    var cmd1 = document.getElementById('term-cmd-1');
                    var out1 = document.getElementById('term-output-1');
                    var line2 = document.getElementById('term-line-2');
                    var cmd2 = document.getElementById('term-cmd-2');
                    var out2 = document.getElementById('term-output-2');
                    var cursorLine = document.getElementById('term-cursor-line');
                    cursorLine.style.display = 'none';
                    setTimeout(function() {
                        typeText(cmd1, 'ls /redsim/knowledgebase', 40, function() {
                            out1.style.display = 'block';
                            setTimeout(function() {
                                line2.style.display = 'flex';
                                typeText(cmd2, 'cat /redsim/knowledgebase/.status', 38, function() {
                                    out2.style.display = 'block';
                                    setTimeout(function() { cursorLine.style.display = 'flex'; }, 300);
                                });
                            }, 700);
                        });
                    }, 500);
                }, { threshold: 0.3 });
                var terminal = document.querySelector('.terminal-card');
                if (terminal) termObs.observe(terminal);
            })();
        });

        // ── 8. CardNav — Dropdown & Mobile Drawer JS ─────────────────
        // Dropdown toggle
        window.cnToggle = function(itemId) {
            var item = document.getElementById(itemId);
            if (!item) return;
            var isOpen = item.classList.contains('open');
            cnCloseAll();
            if (!isOpen) {
                item.classList.add('open');
                var btn = item.querySelector('.cn-item-btn');
                if (btn) btn.setAttribute('aria-expanded', 'true');
            }
        };

        window.cnCloseAll = function() {
            document.querySelectorAll('.cn-item.open').forEach(function(el) {
                el.classList.remove('open');
                var btn = el.querySelector('.cn-item-btn');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            });
        };

        // Mobile drawer toggle
        var cnDrawer = document.getElementById('cn-mobile-drawer');
        var cnHamburger = document.getElementById('cn-hamburger');
        var cnDrawerOpen = false;

        window.cnMobileToggle = function() {
            cnDrawerOpen = !cnDrawerOpen;
            if (cnDrawerOpen) {
                cnDrawer && cnDrawer.classList.add('open');
                cnHamburger && cnHamburger.classList.add('open');
                document.body.style.overflow = 'hidden';
            } else {
                cnMobileClose();
            }
        };

        window.cnMobileClose = function() {
            cnDrawerOpen = false;
            cnDrawer && cnDrawer.classList.remove('open');
            cnHamburger && cnHamburger.classList.remove('open');
            document.body.style.overflow = '';
        };

        // Close dropdown & drawer on outside click
        document.addEventListener('click', function(e) {
            var root = document.getElementById('cn-root');
            if (root && !root.contains(e.target)) {
                cnCloseAll();
            }
            if (cnDrawerOpen && cnDrawer && !cnDrawer.contains(e.target) && cnHamburger && !cnHamburger.contains(e.target)) {
                cnMobileClose();
            }
        });

        // Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') { cnCloseAll(); cnMobileClose(); }
        });

        // Close drawer on desktop resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024 && cnDrawerOpen) {
                cnMobileClose();
            }
        });

        // Close drawer on scroll > 60px
        var cnLastScroll = window.scrollY;
        window.addEventListener('scroll', function() {
            if (Math.abs(window.scrollY - cnLastScroll) > 60 && cnDrawerOpen) cnMobileClose();
            cnLastScroll = window.scrollY;

            // Scroll shadow on navbar
            var panel = document.getElementById('cn-panel');
            if (panel) {
                if (window.scrollY > 20) {
                    panel.style.background = 'rgba(4,5,14,0.96)';
                    panel.style.boxShadow = '0 24px 48px -20px rgba(0,0,0,0.95), 0 6px 16px -8px rgba(0,0,0,0.7), inset 0 0 0 1px rgba(0,212,255,0.06)';
                } else {
                    panel.style.background = '';
                    panel.style.boxShadow = '';
                }
            }
        }, { passive: true });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                var targetId = this.getAttribute('href');
                var target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    cnCloseAll();
                    setTimeout(function() {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 80);
                }
            });
        });
    </script>
</body>
</html>
