<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Papan peringkat RedSim — lihat siapa yang memimpin tantangan keamanan siber.">
    <title>Papan Peringkat — RedSim</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Montserrat:wght@400;600;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, ::before, ::after { box-sizing: border-box; }
        html, body { background:#050816; min-height:100vh; overflow-x:hidden; max-width:100vw; width:100%; }
        body { padding-top:80px; font-family:'Inter',sans-serif; }
        .lb-bg { position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 50% at 50% 0%,rgba(0,212,255,.07) 0%,transparent 60%),radial-gradient(ellipse 60% 40% at 80% 70%,rgba(124,58,237,.06) 0%,transparent 55%),#050816; }
        :root { --cn-panel:rgba(6,8,18,.82);--cn-edge:rgba(255,255,255,.08);--cn-highlight:rgba(255,255,255,.07);--cn-shadow:0 24px 48px -20px rgba(0,0,0,.9),0 6px 16px -8px rgba(0,0,0,.6);--cn-text:#e8edf5;--cn-muted:rgba(180,200,225,.55);--cn-hover:rgba(255,255,255,.06);--cn-tile:linear-gradient(180deg,rgba(10,15,35,.95) 0%,rgba(6,8,20,.98) 100%);--cn-tile-rim:inset 0 0 0 1px rgba(255,255,255,.07),inset 0 1px 0 rgba(255,255,255,.1),0 4px 10px -4px rgba(0,0,0,.8);--cn-cta:linear-gradient(135deg,#00d4ff 0%,#7c3aed 100%);--cn-cta-ink:#fff;--cn-cta-shadow:inset 0 1px 0 rgba(255,255,255,.22),0 6px 18px -6px rgba(0,212,255,.4);--cn-blur:blur(24px) saturate(1.6);--cn-max:960px;--cn-bar:62px;--cn-inset:8px;--cn-radius:20px; }
        .cn-root{position:fixed;top:0;left:0;right:0;z-index:200;padding:10px 20px;font-family:'Inter',sans-serif;-webkit-tap-highlight-color:transparent;box-sizing:border-box;width:100%;}
        .cn-root-inner{max-width:var(--cn-max);margin:0 auto;position:relative;width:100%;box-sizing:border-box;}
        .cn-panel{position:relative;border-radius:var(--cn-radius);background:var(--cn-panel);backdrop-filter:var(--cn-blur);-webkit-backdrop-filter:var(--cn-blur);box-shadow:var(--cn-shadow);overflow:visible}
        .cn-panel-rim{position:absolute;inset:0;border-radius:inherit;box-shadow:inset 0 0 0 1px var(--cn-edge),inset 0 1px 0 var(--cn-highlight);pointer-events:none;z-index:3}
        .cn-bar{height:var(--cn-bar);display:flex;justify-content:space-between;align-items:center;padding:0 var(--cn-inset);position:relative;z-index:2;width:100%;box-sizing:border-box;}
        .cn-logo{display:inline-flex;align-items:center;gap:9px;text-decoration:none;padding:0 10px;color:var(--cn-text)}
        .cn-logo img{width:28px;height:28px;object-fit:contain}
        .cn-logo-text{font-family:'Montserrat',sans-serif;font-weight:900;font-size:1.05rem;background:linear-gradient(90deg,#00d4ff,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .cn-links{display:flex;align-items:center;justify-content:center;gap:2px}
        .cn-item{position:relative}
        .cn-item-btn{display:inline-flex;align-items:center;gap:6px;height:calc(var(--cn-bar) - var(--cn-inset)*2);padding:0 14px;border:0;border-radius:calc(var(--cn-radius) - var(--cn-inset));background:none;color:var(--cn-muted);font-family:inherit;font-size:.82rem;font-weight:500;text-decoration:none;cursor:pointer;white-space:nowrap;transition:background .18s,color .18s;outline:none}
        .cn-item-btn:hover,.cn-item-btn.active{color:var(--cn-text);background:var(--cn-hover)}
        .cn-item-btn.leaderboard{color:#facc15}.cn-item-btn.leaderboard:hover{background:rgba(250,204,21,.08)}
        .cn-item-btn.red-lab{color:#fca5a5}.cn-item-btn.red-lab:hover{background:rgba(220,38,38,.08)}
        .cn-chevron{width:14px;height:14px;opacity:.5;transition:transform .3s cubic-bezier(.22,1,.36,1),opacity .2s;flex-shrink:0}
        .cn-item.open .cn-chevron{transform:rotate(180deg);opacity:.8}
        .cn-dropdown{position:absolute;top:calc(100% + 6px);left:50%;transform:translateX(-50%);min-width:260px;background:var(--cn-panel);backdrop-filter:var(--cn-blur);-webkit-backdrop-filter:var(--cn-blur);border-radius:16px;box-shadow:var(--cn-shadow);border:1px solid var(--cn-edge);padding:var(--cn-inset);display:none;z-index:100;animation:cnDropIn .22s cubic-bezier(.22,1,.36,1) both}
        @keyframes cnDropIn{from{opacity:0;transform:translateX(-50%) translateY(-8px) scale(.97)}to{opacity:1;transform:translateX(-50%) translateY(0) scale(1)}}
        .cn-item.open .cn-dropdown{display:block}
        .cn-card{background:var(--cn-tile);box-shadow:var(--cn-tile-rim);border-radius:12px;padding:14px 14px 12px;margin-bottom:6px}
        .cn-card:last-child{margin-bottom:0}
        .cn-card-title{font-size:15px;font-weight:600;color:var(--cn-text);margin-bottom:3px}
        .cn-card-desc{font-size:12px;color:var(--cn-muted);line-height:1.4;margin:0 0 10px}
        .cn-card-links{display:flex;flex-direction:column;gap:2px;list-style:none;margin:0;padding:0}
        .cn-card-link{display:inline-flex;align-items:center;gap:7px;padding:4px 2px;font-size:13px;font-weight:500;color:rgba(180,200,225,.75);text-decoration:none;border-radius:6px;transition:color .15s}
        .cn-card-link:hover{color:var(--cn-text)}
        .cn-card-link svg{flex-shrink:0;opacity:.6;transition:opacity .15s,transform .3s cubic-bezier(.22,1,.36,1)}
        .cn-card-link:hover svg{opacity:1;transform:translate(2px,-2px)}
        .cn-cta{display:inline-flex;align-items:center;justify-content:center;height:calc(var(--cn-bar) - var(--cn-inset)*2);padding:0 18px;border:0;border-radius:calc(var(--cn-radius) - var(--cn-inset));background:var(--cn-cta);box-shadow:var(--cn-cta-shadow);color:var(--cn-cta-ink);font-family:inherit;font-size:.82rem;font-weight:700;text-decoration:none;cursor:pointer;white-space:nowrap;transition:filter .2s,transform .2s;outline:none}
        .cn-cta:hover{filter:brightness(1.12);transform:translateY(-1px)}
        .cn-ghost{display:inline-flex;align-items:center;height:calc(var(--cn-bar) - var(--cn-inset)*2);padding:0 16px;border:1px solid rgba(255,255,255,.1);border-radius:calc(var(--cn-radius) - var(--cn-inset));color:var(--cn-muted);font-family:inherit;font-size:.82rem;font-weight:600;text-decoration:none;transition:all .2s;margin-right:6px}
        .cn-ghost:hover{color:var(--cn-text);border-color:rgba(255,255,255,.2);background:rgba(255,255,255,.05)}
        .cn-actions{display:flex;align-items:center;justify-self:end}
        .cn-hamburger{display:none;justify-self:end;flex-direction:column;justify-content:center;align-items:center;width:38px;height:38px;border-radius:50%;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.04);cursor:pointer;gap:5px;transition:all .2s;margin-right:4px}
        .cn-hamburger:hover{border-color:rgba(0,212,255,.3);background:rgba(0,212,255,.06)}
        .cn-hamburger span{display:block;width:17px;height:1.5px;background:rgba(190,205,225,.75);border-radius:2px;transition:all .28s cubic-bezier(.22,1,.36,1)}
        .cn-hamburger.open span:nth-child(1){transform:translateY(6.5px) rotate(45deg)}
        .cn-hamburger.open span:nth-child(2){opacity:0;transform:scaleX(0)}
        .cn-hamburger.open span:nth-child(3){transform:translateY(-6.5px) rotate(-45deg)}
        .cn-mobile-drawer{display:none;position:fixed;top:80px;left:12px;right:12px;width:calc(100% - 24px);box-sizing:border-box;z-index:199;background:rgba(6,8,18,.97);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:14px;box-shadow:0 8px 40px rgba(0,0,0,.75);max-height:calc(100dvh - 100px);overflow-y:auto;animation:cnMobileIn .22s cubic-bezier(.22,1,.36,1) both}
        @keyframes cnMobileIn{from{opacity:0;transform:translateY(-10px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}
        .cn-mobile-drawer.open{display:block}
        .cn-mobile-link{display:flex;align-items:center;gap:10px;padding:11px 13px;border-radius:12px;font-size:.875rem;font-weight:500;color:rgba(180,200,225,.75);text-decoration:none;transition:all .15s;margin-bottom:2px}
        .cn-mobile-link:hover{color:#fff;background:rgba(255,255,255,.06)}
        .cn-mobile-link.yellow{color:#facc15}.cn-mobile-link.red{color:#fca5a5}
        .cn-mobile-sep{height:1px;background:rgba(255,255,255,.06);margin:8px 0}
        .cn-mobile-section-label{font-size:.7rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(180,200,225,.3);padding:6px 13px 4px}
        .cn-mobile-actions{display:flex;flex-direction:column;gap:8px;margin-top:10px}
        .cn-mobile-btn{display:block;text-align:center;padding:12px;border-radius:12px;font-size:.875rem;font-weight:600;text-decoration:none;transition:all .2s}
        .cn-mobile-btn.ghost{color:rgba(180,200,225,.75);border:1px solid rgba(255,255,255,.1)}
        .cn-mobile-btn.ghost:hover{color:#fff;background:rgba(255,255,255,.05)}
        .cn-mobile-btn.primary{color:#fff;background:linear-gradient(135deg,#00d4ff 0%,#7c3aed 100%);box-shadow:0 0 18px rgba(0,212,255,.18)}
        .cn-mobile-btn.primary:hover{box-shadow:0 0 26px rgba(0,212,255,.38)}
        @media(max-width:1024px){.cn-links{display:none!important}.cn-actions{display:none!important}.cn-hamburger{display:flex!important}}
        @media(min-width:1025px){.cn-mobile-drawer{display:none!important}}
        @media(max-width:640px){:root{--cn-bar:56px;--cn-radius:16px;}.cn-root{padding:8px 10px;}.cn-mobile-drawer{top:72px;left:10px;right:10px;width:calc(100% - 20px);max-height:calc(100dvh - 86px);}}
        /* Leaderboard Page Styles */
        .lb-page{position:relative;z-index:10;max-width:820px;margin:0 auto;padding:40px 20px 80px}
        .lb-header{text-align:center;margin-bottom:36px}
        .lb-badge{display:inline-flex;align-items:center;gap:8px;padding:6px 16px;border-radius:99px;background:rgba(250,204,21,.08);border:1px solid rgba(250,204,21,.2);margin-bottom:16px}
        .lb-badge span{font-size:.78rem;font-weight:600;color:#facc15;letter-spacing:.04em;text-transform:uppercase}
        .lb-title{font-family:'Montserrat',sans-serif;font-size:2.4rem;font-weight:900;color:#fff;margin-bottom:8px;line-height:1.1}
        @media(max-width:600px){.lb-title{font-size:1.75rem}}
        .lb-subtitle{font-size:.95rem;color:rgba(180,200,225,.6)}
        .lb-cta-section{text-align:center;margin-top:36px}
        .lb-cta-btn{display:inline-flex;align-items:center;gap:10px;padding:14px 32px;border-radius:99px;background:linear-gradient(135deg,#00d4ff,#7c3aed);color:#fff;font-weight:700;font-size:.95rem;text-decoration:none;box-shadow:0 0 24px rgba(0,212,255,.25);transition:all .2s}
        .lb-cta-btn:hover{box-shadow:0 0 36px rgba(0,212,255,.4);transform:translateY(-2px)}
        .lb-ghost-btn{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:99px;border:1px solid rgba(255,255,255,.12);color:rgba(180,200,225,.75);font-weight:600;font-size:.9rem;text-decoration:none;margin-left:12px;transition:all .2s}
        .lb-ghost-btn:hover{color:#fff;border-color:rgba(255,255,255,.25);background:rgba(255,255,255,.05)}
        @media(max-width:500px){.lb-ghost-btn{margin-left:0;margin-top:8px}.lb-cta-section{display:flex;flex-direction:column;align-items:center;gap:8px}}
        @keyframes pulse-slow{0%,100%{box-shadow:0 0 0 0 rgba(251,204,21,.4)}50%{box-shadow:0 0 20px 5px rgba(251,204,21,.12)}}
        .animate-pulse-slow{animation:pulse-slow 3s ease-in-out infinite}
    </style>
</head>
<body>
    <div class="lb-bg"></div>

    <!-- CardNav -->
    <div class="cn-root" id="cn-root">
        <div class="cn-root-inner">
            <div class="cn-panel" id="cn-panel">
                <div class="cn-bar">
                    <a href="/" class="cn-logo">
                        <img src="{{ asset('logo-redsim.svg') }}" alt="RedSim Logo">
                        <span class="cn-logo-text">RedSim</span>
                    </a>
                    <div class="cn-links" id="cn-links">
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
                                        <li><a href="/#fitur" class="cn-card-link"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 17 17 7"/><polyline points="7 7 17 7 17 17"/></svg>Fitur Unggulan</a></li>
                                        <li><a href="/#pilar" class="cn-card-link"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 17 17 7"/><polyline points="7 7 17 7 17 17"/></svg>AI &amp; ML Hybrid</a></li>
                                        <li><a href="/#kb" class="cn-card-link"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 17 17 7"/><polyline points="7 7 17 7 17 17"/></svg>Knowledge Base</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <a href="/red-team-lab" class="cn-item-btn red-lab">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                            Red Team Lab
                        </a>
                        <a href="/edukasi/leaderboard" class="cn-item-btn leaderboard active">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            Leaderboard
                        </a>
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
                                        <li><a href="/ketentuan-layanan" class="cn-card-link"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 17 17 7"/><polyline points="7 7 17 7 17 17"/></svg>Ketentuan Layanan</a></li>
                                        <li><a href="/kebijakan-privasi" class="cn-card-link"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 17 17 7"/><polyline points="7 7 17 7 17 17"/></svg>Kebijakan Privasi</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cn-actions">
                        @auth
                            <a href="{{ route('dashboard') }}" class="cn-cta">Dashboard</a>
                        @else
                            <a href="{{ route('masuk') }}" class="cn-ghost">Masuk</a>
                            <a href="{{ route('daftar') }}" class="cn-cta">Daftar Gratis</a>
                        @endauth
                    </div>
                    <button type="button" class="cn-hamburger" id="cn-hamburger" onclick="cnMobileToggle()">
                        <span></span><span></span><span></span>
                    </button>
                </div>
                <span class="cn-panel-rim"></span>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer -->
    <div class="cn-mobile-drawer" id="cn-mobile-drawer">
        <div class="cn-mobile-section-label">Platform</div>
        <a href="/#fitur" class="cn-mobile-link" onclick="cnMobileClose()">Fitur Unggulan</a>
        <a href="/#pilar" class="cn-mobile-link" onclick="cnMobileClose()">AI &amp; ML Hybrid</a>
        <a href="/#kb" class="cn-mobile-link" onclick="cnMobileClose()">Knowledge Base</a>
        <div class="cn-mobile-sep"></div>
        <a href="/red-team-lab" class="cn-mobile-link red">Red Team Lab</a>
        <a href="/edukasi/leaderboard" class="cn-mobile-link yellow">Leaderboard</a>
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

    <main class="lb-page">
        <div class="lb-header">
            <div class="lb-badge">
                <svg width="13" height="13" fill="none" stroke="#facc15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                <span>Papan Peringkat</span>
            </div>
            <h1 class="lb-title">Hall of Fame</h1>
            <p class="lb-subtitle">Peringkat pengguna berdasarkan poin tantangan keamanan siber</p>
        </div>

        @include('edukasi._leaderboard_content', ['leaders' => $leaders, 'isAuth' => false])

        <div class="lb-cta-section">
            @auth
                <a href="/edukasi/tantangan" class="lb-cta-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Buka Modul Tantangan
                </a>
                <a href="{{ route('dashboard') }}" class="lb-ghost-btn">
                    Ke Dashboard
                </a>
            @else
                <a href="{{ route('daftar') }}" class="lb-cta-btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Daftar &amp; Ikut Tantangan
                </a>
                <a href="{{ route('masuk') }}" class="lb-ghost-btn">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Sudah punya akun? Masuk
                </a>
            @endauth
        </div>
    </main>

    <script>
        window.cnToggle = function(id) {
            var el = document.getElementById(id);
            if (!el) return;
            var was = el.classList.contains('open');
            cnCloseAll();
            if (!was) { el.classList.add('open'); var b = el.querySelector('.cn-item-btn'); if(b) b.setAttribute('aria-expanded','true'); }
        };
        window.cnCloseAll = function() {
            document.querySelectorAll('.cn-item.open').forEach(function(el){
                el.classList.remove('open'); var b=el.querySelector('.cn-item-btn'); if(b) b.setAttribute('aria-expanded','false');
            });
        };
        var cnD = document.getElementById('cn-mobile-drawer'), cnH = document.getElementById('cn-hamburger'), cnO = false;
        window.cnMobileToggle = function() { cnO=!cnO; if(cnO){cnD&&cnD.classList.add('open');cnH&&cnH.classList.add('open');document.body.style.overflow='hidden';}else cnMobileClose(); };
        window.cnMobileClose = function() { cnO=false;cnD&&cnD.classList.remove('open');cnH&&cnH.classList.remove('open');document.body.style.overflow=''; };
        document.addEventListener('click', function(e) {
            var r=document.getElementById('cn-root'); if(r&&!r.contains(e.target)) cnCloseAll();
            if(cnO&&cnD&&!cnD.contains(e.target)&&cnH&&!cnH.contains(e.target)) cnMobileClose();
        });
        document.addEventListener('keydown', function(e){ if(e.key==='Escape'){cnCloseAll();cnMobileClose();} });
        window.addEventListener('resize', function() { if(window.innerWidth > 1024 && cnO) cnMobileClose(); });
        var ls=window.scrollY;
        window.addEventListener('scroll', function(){
            if(Math.abs(window.scrollY-ls)>60&&cnO) cnMobileClose(); ls=window.scrollY;
            var p=document.getElementById('cn-panel');
            if(p){ p.style.background=window.scrollY>20?'rgba(4,5,14,.96)':''; p.style.boxShadow=window.scrollY>20?'0 24px 48px -20px rgba(0,0,0,.95),0 6px 16px -8px rgba(0,0,0,.7),inset 0 0 0 1px rgba(0,212,255,.06)':''; }
        },{passive:true});
    </script>
</body>
</html>
