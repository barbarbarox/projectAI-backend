<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 — Terlalu Banyak Permintaan | RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Montserrat:wght@700;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh; background: #0a0e1a;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            font-family: 'Inter', sans-serif; overflow: hidden; position: relative;
        }
        body::before {
            content: ''; position: fixed; top: -150px; right: -150px; width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(234,179,8,0.08) 0%, transparent 70%); pointer-events: none;
        }
        .icon-wrapper {
            position: relative; width: 110px; height: 110px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 2rem;
        }
        @keyframes spin-slow { to { transform: rotate(360deg); } }
        @keyframes pulse-scale { 0%,100% { transform: scale(1); } 50% { transform: scale(1.1); } }
        .icon-ring {
            position: absolute; inset: 0; border-radius: 50%;
            border: 2px dashed rgba(234,179,8,0.3);
            animation: spin-slow 8s linear infinite;
        }
        .icon-ring-2 {
            position: absolute; inset: 10px; border-radius: 50%;
            border: 1px solid rgba(234,179,8,0.15);
            animation: spin-slow 12s linear infinite reverse;
        }
        .clock-icon {
            width: 64px; height: 64px;
            animation: pulse-scale 2s ease-in-out infinite;
            filter: drop-shadow(0 0 16px rgba(234,179,8,0.5));
        }
        .error-content { text-align: center; padding: 0 1.5rem; max-width: 520px; position: relative; z-index: 2; }
        .error-code {
            font-family: 'Montserrat', sans-serif; font-size: 1rem; font-weight: 700; letter-spacing: 4px;
            background: linear-gradient(90deg, #eab308, #f97316);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 0.5rem;
        }
        .error-title { font-size: 1.75rem; font-weight: 800; color: #fff; margin-bottom: 0.75rem; }
        .error-desc { color: #64748b; font-size: 0.9rem; line-height: 1.7; margin-bottom: 1.5rem; }
        .retry-info {
            display: inline-block; background: rgba(234,179,8,0.08);
            border: 1px solid rgba(234,179,8,0.25); border-radius: 10px;
            padding: 0.75rem 1.5rem; margin-bottom: 1.75rem;
        }
        .retry-label { font-size: 0.75rem; color: #a16207; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 1px; }
        #countdown { font-family: 'Montserrat', sans-serif; font-size: 2rem; font-weight: 900; color: #eab308; }
        .btn-home {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 0.7rem 1.75rem; border-radius: 12px;
            background: linear-gradient(135deg, #eab308, #f97316);
            color: white; font-weight: 600; font-size: 0.9rem;
            text-decoration: none; transition: all 0.3s;
            box-shadow: 0 0 20px rgba(234,179,8,0.2);
        }
        .btn-home:hover { transform: translateY(-3px); box-shadow: 0 0 30px rgba(234,179,8,0.35); }
        .text_429 {
            position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
            font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 28vw;
            color: rgba(234,179,8,0.025); pointer-events: none; user-select: none; z-index: 0; white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="text_429">429</div>
    <div class="icon-wrapper">
        <div class="icon-ring"></div>
        <div class="icon-ring-2"></div>
        <svg class="clock-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="9" stroke="#eab308" stroke-width="1.5"/>
            <path d="M12 7v5l3 3" stroke="#eab308" stroke-width="1.75" stroke-linecap="round"/>
        </svg>
    </div>
    <div class="error-content">
        <p class="error-code">ERROR 429</p>
        <h1 class="error-title">Terlalu Banyak Permintaan</h1>
        <p class="error-desc">
            Anda telah mengirim terlalu banyak permintaan dalam waktu singkat.<br>
            Sistem RedSim menerapkan rate limiting untuk menjaga kualitas layanan.
        </p>
        <div class="retry-info">
            <div class="retry-label">Coba lagi dalam</div>
            <div id="countdown">60</div>
        </div>
        <br>
        <a href="/" class="btn-home">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Ke Beranda
        </a>
    </div>
    <script>
        let retryAfter = parseInt('{{ $exception->getHeaders()["Retry-After"] ?? 60 }}') || 60;
        const el = document.getElementById('countdown');
        const tick = () => {
            if (retryAfter <= 0) { location.reload(); return; }
            el.textContent = retryAfter + 's';
            retryAfter--;
            setTimeout(tick, 1000);
        };
        tick();
    </script>
</body>
</html>
