<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak | RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Montserrat:wght@700;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: #0a0e1a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            top: -150px; right: -150px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(220,38,38,0.08) 0%, transparent 70%);
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -150px; left: -150px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(124,58,237,0.06) 0%, transparent 70%);
            pointer-events: none;
        }
        .shield-container { position: relative; margin-bottom: 2rem; }
        .shield-ring {
            position: absolute;
            inset: -10px;
            border-radius: 50%;
            border: 1px solid rgba(220,38,38,0.2);
            animation: pulse-ring 2s ease-out infinite;
        }
        .shield-ring:nth-child(2) { animation-delay: 0.5s; inset: -20px; }
        .shield-ring:nth-child(3) { animation-delay: 1s; inset: -30px; }
        @keyframes pulse-ring {
            0% { opacity: 0.6; transform: scale(1); }
            100% { opacity: 0; transform: scale(1.2); }
        }
        @keyframes lock-shake {
            0%, 100% { transform: translateX(0); }
            10% { transform: translateX(-4px); }
            20% { transform: translateX(4px); }
            30% { transform: translateX(-3px); }
            40% { transform: translateX(3px); }
            50% { transform: translateX(-2px); }
            60% { transform: translateX(2px); }
        }
        .shield-svg {
            width: 100px; height: 100px;
            animation: lock-shake 4s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(220,38,38,0.4));
        }
        .error-content {
            text-align: center; padding: 0 1.5rem;
            max-width: 520px; position: relative; z-index: 2;
        }
        .error-code {
            font-family: 'Montserrat', sans-serif; font-size: 1rem; font-weight: 700;
            letter-spacing: 4px;
            background: linear-gradient(90deg, #dc2626, #7c3aed);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 0.5rem;
        }
        .error-title { font-size: 1.75rem; font-weight: 800; color: #fff; margin-bottom: 0.75rem; }
        .error-desc { color: #64748b; font-size: 0.9rem; line-height: 1.7; margin-bottom: 1.5rem; }
        .error-detail {
            display: inline-block; background: rgba(220,38,38,0.08);
            border: 1px solid rgba(220,38,38,0.2); border-radius: 8px;
            padding: 0.5rem 1rem; font-size: 0.8rem; color: #f87171;
            font-family: monospace; margin-bottom: 1.5rem;
        }
        .btn-group { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; }
        .btn-home {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 0.7rem 1.75rem; border-radius: 12px;
            background: linear-gradient(135deg, #dc2626, #7c3aed);
            color: white; font-weight: 600; font-size: 0.9rem;
            text-decoration: none; transition: all 0.3s;
            box-shadow: 0 0 20px rgba(220,38,38,0.2);
        }
        .btn-home:hover { transform: translateY(-3px); box-shadow: 0 0 30px rgba(220,38,38,0.35); }
        .btn-back {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 0.7rem 1.75rem; border-radius: 12px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: #94a3b8; font-weight: 600; font-size: 0.9rem;
            text-decoration: none; transition: all 0.3s;
        }
        .btn-back:hover { background: rgba(255,255,255,0.09); color: white; transform: translateY(-2px); }
        .text_403 {
            position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
            font-family: 'Montserrat', sans-serif; font-weight: 900;
            font-size: 28vw; color: rgba(220,38,38,0.03);
            pointer-events: none; user-select: none; z-index: 0; white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="text_403">403</div>
    <div class="shield-container">
        <div class="shield-ring"></div>
        <div class="shield-ring"></div>
        <div class="shield-ring"></div>
        <svg class="shield-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L4 6v6c0 5.25 3.5 10.15 8 11.5C16.5 22.15 20 17.25 20 12V6L12 2z"
                  fill="rgba(220,38,38,0.15)" stroke="#dc2626" stroke-width="1.5"/>
            <line x1="9" y1="9" x2="15" y2="15" stroke="#dc2626" stroke-width="2" stroke-linecap="round"/>
            <line x1="15" y1="9" x2="9" y2="15" stroke="#dc2626" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </div>
    <div class="error-content">
        <p class="error-code">ERROR 403</p>
        <h1 class="error-title">Akses Ditolak</h1>
        <p class="error-desc">
            Anda tidak memiliki izin untuk mengakses halaman ini.<br>
            Pastikan Anda sudah masuk dengan akun yang tepat atau hubungi administrator.
        </p>
        <div class="error-detail">HTTP 403 — Forbidden</div>
        <div class="btn-group">
            <a href="javascript:history.back()" class="btn-back">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
            <a href="/" class="btn-home">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
