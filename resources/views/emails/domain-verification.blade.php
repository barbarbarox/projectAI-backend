<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Domain — RedSim</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #0a0e1a; color: #e2e8f0; margin: 0; padding: 0; }
        .wrapper { max-width: 560px; margin: 40px auto; padding: 0 20px; }
        .card { background: #0f1629; border: 1px solid #1e2d4a; border-radius: 16px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #7c3aed 0%, #00d4ff 100%); padding: 32px 36px; text-align: center; }
        .header img { width: 48px; margin-bottom: 12px; }
        .header h1 { margin: 0; font-size: 22px; color: #fff; font-weight: 700; letter-spacing: -0.5px; }
        .header p { margin: 6px 0 0; font-size: 13px; color: rgba(255,255,255,0.8); }
        .body { padding: 32px 36px; }
        .body p { font-size: 14px; color: #94a3b8; line-height: 1.7; margin: 0 0 16px; }
        .domain-tag { display: inline-block; background: #1e2d4a; border: 1px solid #2d4a7a; border-radius: 8px; padding: 6px 14px; font-family: monospace; font-size: 14px; color: #00d4ff; margin-bottom: 20px; }
        .code-box { background: #0a0e1a; border: 2px dashed #7c3aed; border-radius: 12px; padding: 24px; text-align: center; margin: 24px 0; }
        .code-box p { font-size: 12px; color: #64748b; margin: 0 0 8px; text-transform: uppercase; letter-spacing: 1px; }
        .code-box .code { font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #fff; font-family: 'Courier New', monospace; background: linear-gradient(135deg, #7c3aed, #00d4ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .warning { background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 10px; padding: 14px 16px; margin: 20px 0; }
        .warning p { font-size: 12px; color: #fbbf24; margin: 0; }
        .footer { padding: 20px 36px; border-top: 1px solid #1e2d4a; text-align: center; }
        .footer p { font-size: 12px; color: #475569; margin: 0; line-height: 1.6; }
        .footer strong { color: #7c3aed; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <h1>🛡️ RedSim Security Platform</h1>
            <p>Verifikasi Kepemilikan Domain</p>
        </div>
        <div class="body">
            <p>Halo,</p>
            <p>Anda menerima email ini karena ada permintaan verifikasi kepemilikan domain:</p>
            <span class="domain-tag">{{ $dv->domain }}</span>

            <p>Masukkan kode di bawah ini pada halaman verifikasi RedSim untuk membuktikan bahwa Anda memiliki akses ke domain tersebut:</p>

            <div class="code-box">
                <p>Kode Verifikasi Anda</p>
                <div class="code">{{ $dv->kode_email }}</div>
            </div>

            <div class="warning">
                <p>⏰ Kode ini berlaku hingga <strong>{{ $dv->expires_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</strong>.<br>
                Jika Anda tidak meminta verifikasi ini, abaikan email ini.</p>
            </div>

            <p style="font-size: 12px; color: #475569;">Demi keamanan, jangan bagikan kode ini kepada siapapun termasuk tim RedSim.</p>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh <strong>RedSim Security Platform</strong>.<br>
            Jangan membalas email ini.</p>
        </div>
    </div>
</div>
</body>
</html>
