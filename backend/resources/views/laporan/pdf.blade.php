<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Laporan Keamanan RedSim - {{ $scan->target ?? $scan->nama_file }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; color: #1a1a2e; font-size: 10px; line-height: 1.5; background: #ffffff; }

/* Header */
.rh { background: #0a0e1a; color: #ffffff; padding: 18px 24px; border-bottom: 3px solid #00d4ff; }
.rh h1 { font-size: 18px; font-weight: 900; color: #00d4ff; letter-spacing: 0.5px; }
.rh .meta { font-size: 9px; color: #94a3b8; margin-top: 6px; }
.rh .meta span { margin-right: 14px; }

/* Score Grid */
.sg { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
.sg table { width: 100%; border-collapse: collapse; }
.sg td { text-align: center; padding: 12px 6px; border-right: 1px solid #e2e8f0; width: 25%; }
.sg td:last-child { border-right: none; }
.sn { font-size: 26px; font-weight: 900; line-height: 1.1; }
.sl { font-size: 8.5px; color: #64748b; margin-top: 2px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }

/* Sections */
.sec { padding: 12px 18px; border-bottom: 1px solid #e2e8f0; }
.stl { font-size: 10.5px; font-weight: 700; color: #0a0e1a; border-left: 3px solid #00d4ff; padding-left: 6px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px; }
.stlr { font-size: 10.5px; font-weight: 700; color: #991b1b; border-left: 3px solid #dc2626; padding-left: 6px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px; }
.stlg { font-size: 10.5px; font-weight: 700; color: #166534; border-left: 3px solid #16a34a; padding-left: 6px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px; }
.stly { font-size: 10.5px; font-weight: 700; color: #854d0e; border-left: 3px solid #ca8a04; padding-left: 6px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px; }
.stlp { font-size: 10.5px; font-weight: 700; color: #581c87; border-left: 3px solid #7c3aed; padding-left: 6px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px; }

/* Badges */
.bk { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8.5px; font-weight: 700; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.bt { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8.5px; font-weight: 700; background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
.bs { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8.5px; font-weight: 700; background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
.bl { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8.5px; font-weight: 700; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.bg { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8.5px; font-weight: 700; background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.br { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8.5px; font-weight: 700; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.by { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8.5px; font-weight: 700; background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
.bgr { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8.5px; font-weight: 700; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
.bp { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8.5px; font-weight: 700; background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; }

/* Tables */
.it { width: 100%; border-collapse: collapse; }
.it td { padding: 4px 6px; border-bottom: 1px solid #f1f5f9; font-size: 9.5px; }
.it td:first-child { color: #64748b; width: 32%; font-weight: 600; }
.pt { width: 100%; border-collapse: collapse; margin-top: 4px; }
.pt th { background: #f1f5f9; padding: 4px 6px; text-align: left; font-size: 8.5px; font-weight: 700; color: #475569; border-bottom: 2px solid #cbd5e1; }
.pt td { padding: 4px 6px; border-bottom: 1px solid #f1f5f9; font-size: 9px; }
.or { background: #fff7ed; }
.ho { background: #f0fdf4; }
.hm { background: #fff5f5; }
.hw { background: #fffbeb; }

/* Finding Card */
.fc { border: 1px solid #cbd5e1; border-radius: 4px; padding: 8px 10px; margin-bottom: 8px; page-break-inside: avoid; background: #ffffff; }
.fck { border-left: 4px solid #b91c1c; }
.fct { border-left: 4px solid #ea580c; }
.fcs { border-left: 4px solid #ca8a04; }
.fcr { border-left: 4px solid #0284c7; }

/* ML Card */
.ml-box { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 7px 9px; margin: 6px 0; }
.ml-box table { width: 100%; border-collapse: collapse; }
.ml-tag { font-size: 7.5px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 2px; }
.ml-val { font-size: 11px; font-weight: 900; }

/* Prob Bar */
.pbar-bg { background: #e2e8f0; height: 5px; border-radius: 2px; overflow: hidden; margin-top: 2px; }
.pbar-fill { height: 5px; }

/* Code Blocks */
.code-wrap { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 5px; }
.code-wrap td { width: 50%; vertical-align: top; padding: 0 3px; }
.code-box { font-family: monospace; font-size: 8px; padding: 5px 7px; border-radius: 3px; line-height: 1.35; white-space: pre-wrap; word-break: break-all; }
.code-vuln { background: #fff5f5; border: 1px solid #fca5a5; color: #991b1b; }
.code-safe { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }

/* RAG Reference Box */
.rag-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; margin-bottom: 5px; }

/* Simulasi Box */
.sim-box { background: #0a0e1a; color: #f1f5f9; border-radius: 5px; padding: 10px 12px; margin-bottom: 8px; page-break-inside: avoid; }
.sim-title { font-size: 11px; font-weight: 700; color: #00d4ff; margin-bottom: 4px; }
.sim-step { padding: 4px 6px; margin-top: 3px; background: #131c31; border-left: 2px solid #ef4444; border-radius: 0 3px 3px 0; font-size: 8.5px; color: #cbd5e1; }

/* Columns */
.su { column-count: 3; column-gap: 8px; }
.si { font-size: 8.5px; color: #475569; font-family: monospace; padding: 1px 0; }
.sh { color: #dc2626; font-weight: 700; }
.mn { font-family: monospace; font-size: 8.5px; }

/* Generic Text Helpers */
.ok { color: #16a34a; font-weight: 700; }
.fl { color: #dc2626; font-weight: 700; }
.dc { background: #fffbeb; border: 1px solid #fef08a; border-radius: 4px; padding: 8px 12px; margin: 12px 18px; font-size: 8.5px; color: #854d0e; }
.pb { page-break-before: always; }
</style>
</head>
<body>

@php
$data      = $scan->data_mentah ?? [];
$isIntens  = $scan->mode_scan === 'intens';
$skor      = $data['skor_reputasi'] ?? ($scan->skor_keamanan ?? 0);
$status    = $data['status'] ?? '';
$vt        = $data['virustotal']       ?? [];
$ssl       = $data['ssl']              ?? [];
$hdrs      = $data['security_headers'] ?? [];
$cookies   = $data['cookie_security']  ?? [];
$dns       = $data['dns_recon']        ?? [];
$ports     = $data['port_scan']        ?? [];
$cors      = $data['cors_config']      ?? [];
$httpMAll  = $data['http_methods']     ?? [];
$subs      = $data['subdomains']       ?? [];
$cfiles    = $data['common_files']     ?? [];
$breakdown = $data['skor_breakdown']   ?? [];
$srvInfo   = $data['server_info']      ?? [];
$tekno     = $data['teknologi']        ?? [];
$redirects = $data['redirect_chain']   ?? [];
$methodList= $httpMAll['methods']      ?? [];
$corsTests = $cors['tests']            ?? $cors['results'] ?? [];
$ragRefs   = $scan->rag_references    ?? [];

if ($skor >= 70)      { $skorC = '#16a34a'; }
elseif ($skor >= 50)  { $skorC = '#ca8a04'; }
else                  { $skorC = '#dc2626'; }

if ($status === 'aman')          { $statusLbl = 'Aman';           $statusC = '#16a34a'; }
elseif ($status === 'mencurigakan') { $statusLbl = 'Mencurigakan'; $statusC = '#ca8a04'; }
elseif ($status === 'berbahaya') { $statusLbl = 'Berbahaya';      $statusC = '#dc2626'; }
else                             { $statusLbl = 'Tidak Diketahui'; $statusC = '#64748b'; }

$hs = $hdrs['skor'] ?? 0;
if ($hs >= 70)      { $hc = '#16a34a'; }
elseif ($hs >= 40)  { $hc = '#ca8a04'; }
else                { $hc = '#dc2626'; }

$interestKw = ['cpanel','mail','webmail','admin','dev','staging','test','api','ftp','ssh','vpn','internal'];

// Pre-sort temuan by severity
$temuanSorted = collect([]);
if ($scan->temuan->count()) {
    $sevMap = ['kritis'=>4, 'critical'=>4, 'tinggi'=>3, 'high'=>3, 'sedang'=>2, 'medium'=>2, 'rendah'=>1, 'low'=>1, 'info'=>0];
    $temuanSorted = $scan->temuan->sortByDesc(function($t) use ($sevMap) {
        return $sevMap[strtolower($t->tingkat_keparahan ?? '')] ?? 0;
    });
}

// Precompute file groups
$expFiles   = collect($cfiles)->filter(function($f){ return !empty($f['terekspos']) || ($f['status'] ?? '') === 'exposed'; });
$hasExposed = $expFiles->count() > 0;
$fGroups    = [];
$catNames   = [
    'source_control' => 'Source Control',
    'config_leak'    => 'Config & Environment',
    'backup'         => 'Backup & Database',
    'debug'          => 'Debug & Info Disclosure',
    'admin_panel'    => 'Admin Panels',
    'api_docs'       => 'API Documentation',
    'web_standard'   => 'Web Standard Files',
];
foreach ($cfiles as $f) {
    $cat = $f['kategori'] ?? $f['category'] ?? 'Lainnya';
    $lbl = $catNames[$cat] ?? $cat;
    $fGroups[$lbl][] = $f;
}
@endphp

{{-- HEADER UTAMA --}}
<div class="rh">
    <table style="width:100%;border-collapse:collapse">
        <tr>
            <td style="vertical-align:middle">
                <h1>REDSIM SECURITY PLATFORM</h1>
                <div style="font-size:10px;color:#cbd5e1;font-weight:700;margin-top:2px">Laporan Komprehensif Analisis Keamanan URL</div>
            </td>
            <td style="text-align:right;vertical-align:middle">
                <span style="display:inline-block;background:#0ea5e9;color:#0a0e1a;font-weight:900;padding:2px 8px;border-radius:3px;font-size:9px">AI-ASSISTED SECURITY ASSESSMENT</span>
                <div style="font-size:7.5px;color:#94a3b8;margin-top:2px;text-align:right">Hybrid LLM + Ensemble ML | Bukan Pentest Profesional</div>
            </td>
        </tr>
    </table>
    <div class="meta">
        <span><strong>Target:</strong> {{ $scan->target ?? $scan->nama_file }}</span>
        <span><strong>Mode:</strong> {{ ucfirst($scan->mode_scan) }}</span>
        <span><strong>Tipe:</strong> {{ ucfirst($scan->tipe_scan) }}</span>
        <span><strong>Tanggal:</strong> {{ $scan->created_at->format('d M Y, H:i') }} WIB</span>
        <span><strong>ID:</strong> #{{ substr($scan->id, 0, 8) }}</span>
    </div>
</div>

{{-- SCORE OVERVIEW --}}
<div class="sg">
    <table>
        <tr>
            <td>
                <div class="sn" style="color:{{ $skorC }}">{{ $skor }}</div>
                <div class="sl">Skor Reputasi</div>
            </td>
            <td>
                <div class="sn" style="color:{{ $statusC }};font-size:18px;padding-top:7px">{{ $statusLbl }}</div>
                <div class="sl">Status Keamanan</div>
            </td>
            <td>
                <div class="sn" style="color:#0284c7">{{ $vt['malicious_count'] ?? 0 }}/{{ $vt['total_engines'] ?? 0 }}</div>
                <div class="sl">Vendor VT Flag</div>
            </td>
            <td>
                <div class="sn" style="color:{{ $hc }}">{{ $hs }}/100</div>
                <div class="sl">Skor Headers</div>
            </td>
        </tr>
    </table>
</div>

{{-- BREAKDOWN SKOR KEAMANAN --}}
@if($isIntens && !empty($breakdown))
<div class="sec">
    <div class="stl">Breakdown Skor Keamanan</div>
    @php
    $bkeys = [
        'virustotal'   => 'VirusTotal',
        'headers'      => 'Headers',
        'ssl'          => 'SSL/TLS',
        'cookies'      => 'Cookies',
        'cors'         => 'CORS',
        'http_methods' => 'HTTP Methods',
        'redirects'    => 'Redirects'
    ];
    @endphp
    <table style="width:100%;border-collapse:collapse">
        <tr>
        @foreach($bkeys as $k => $lbl)
        @if(isset($breakdown[$k]['skor']))
        @php 
            $bs = $breakdown[$k]['skor']; 
            $bc = $bs >= 70 ? '#16a34a' : ($bs >= 40 ? '#ca8a04' : '#dc2626'); 
        @endphp
        <td style="text-align:center;border:1px solid #e2e8f0;padding:6px 3px;background:#ffffff">
            <div style="font-size:17px;font-weight:900;color:{{ $bc }}">{{ $bs }}</div>
            <div style="font-size:8px;color:#475569;font-weight:600">{{ $lbl }}</div>
            <div style="font-size:7.5px;color:#94a3b8">{{ $breakdown[$k]['bobot'] ?? '' }}</div>
        </td>
        @endif
        @endforeach
        </tr>
    </table>
    <div style="margin-top:6px">
        @if(!empty($breakdown['email_security']['bonus']) && $breakdown['email_security']['bonus'] > 0)
        <span class="bg" style="margin-right:4px">+{{ $breakdown['email_security']['bonus'] }} Bonus Email Security</span>
        @endif
        @if(!empty($breakdown['open_ports']['penalty']) && $breakdown['open_ports']['penalty'] < 0)
        <span class="br" style="margin-right:4px">{{ $breakdown['open_ports']['penalty'] }} Penalti Port Terbuka</span>
        @endif
        @if(!empty($breakdown['exposed_files']['penalty']) && $breakdown['exposed_files']['penalty'] < 0)
        <span class="br" style="margin-right:4px">{{ $breakdown['exposed_files']['penalty'] }} Penalti File Sensitif</span>
        @endif
    </div>
</div>
@endif

{{-- TEKNOLOGI TERDETEKSI --}}
@if(!empty($tekno))
<div class="sec">
    <div class="stl">Teknologi Terdeteksi (Stack & Fingerprinting)</div>
    <div style="margin-top:2px">
        @foreach($tekno as $tItem)
        <span style="display:inline-block;background:#0a0e1a;color:#00d4ff;border:1px solid #1e293b;border-radius:4px;padding:3px 8px;font-size:9px;font-weight:700;margin-right:5px;margin-bottom:3px">
            {{ $tItem }}
        </span>
        @endforeach
    </div>
</div>
@endif

{{-- SSL / TLS --}}
@if(!empty($ssl))
<div class="sec">
@php $sslValid = !empty($ssl['valid']); @endphp
<div class="{{ $sslValid ? 'stlg' : 'stlr' }}">Status SSL / TLS</div>
<table class="it">
    <tr>
        <td>Status Sertifikat</td>
        <td>
            @if($sslValid)
                <span class="bg">[VALID] Terenkripsi & Terpercaya</span>
            @else
                <span class="br">[TIDAK VALID] Sertifikat Bermasalah</span>
            @endif
        </td>
    </tr>
    @if(!empty($ssl['issuer']))<tr><td>Penerbit (CA)</td><td><strong>{{ $ssl['issuer'] }}</strong></td></tr>@endif
    @if(isset($ssl['days_until_expiry']))
    <tr>
        <td>Masa Berlaku</td>
        <td>
            @php $dExp = (int)$ssl['days_until_expiry']; @endphp
            @if($dExp < 0)<span class="br">Kedaluwarsa {{ abs($dExp) }} hari lalu</span>
            @elseif($dExp < 30)<span class="by">{{ $dExp }} hari lagi (Segera Perbarui)</span>
            @else<span class="bg">{{ $dExp }} hari lagi</span>@endif
        </td>
    </tr>
    @endif
    @if(!empty($ssl['protocol']))
    <tr>
        <td>Protokol</td>
        <td>
            {{ $ssl['protocol'] }}
            @if(!empty($ssl['weak_protocol']))<span class="br" style="margin-left:5px">LEMAH</span>@endif
        </td>
    </tr>
    @endif
    @if(!empty($ssl['cipher_name']))
    <tr>
        <td>Cipher Suite</td>
        <td class="mn">{{ $ssl['cipher_name'] }}@if(!empty($ssl['cipher_bits'])) ({{ $ssl['cipher_bits'] }}-bit)@endif</td>
    </tr>
    @endif
    @if(!empty($ssl['signature_alg']))
    <tr><td>Signature Algorithm</td><td>{{ $ssl['signature_alg'] }}</td></tr>
    @endif
</table>
</div>
@endif

{{-- COOKIE SECURITY AUDIT (INTENS) --}}
@if($isIntens && !empty($cookies['cookies']))
<div class="sec">
    @php
        $cSkor = $cookies['skor'] ?? 100;
        $cClass = $cSkor >= 70 ? 'stlg' : ($cSkor >= 40 ? 'stly' : 'stlr');
    @endphp
    <div class="{{ $cClass }}">Cookie Security Audit ({{ count($cookies['cookies']) }} Cookie Diperiksa &mdash; Skor: {{ $cSkor }}/100)</div>
    <table class="pt">
        <thead>
            <tr>
                <th style="width:24%">Nama Cookie</th>
                <th style="width:13%">Skor</th>
                <th style="width:15%">Secure Flag</th>
                <th style="width:15%">HttpOnly</th>
                <th style="width:15%">SameSite</th>
                <th style="width:18%">Kategori</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cookies['cookies'] as $ck)
            @php
                $isSec  = !empty($ck['secure']);
                $isHttp = !empty($ck['httponly']);
                $sSite  = $ck['samesite'] ?? 'Tidak Diset';
                $cSc    = $ck['skor'] ?? 0;
            @endphp
            <tr>
                <td>
                    <strong class="mn">{{ $ck['name'] }}</strong>
                    @if(!empty($ck['is_session']))
                    <span class="bs" style="font-size:7.5px;margin-left:2px">Session</span>
                    @endif
                </td>
                <td>
                    <span style="font-weight:700;color:{{ $cSc >= 70 ? '#16a34a' : ($cSc >= 40 ? '#ca8a04' : '#dc2626') }}">
                        {{ $cSc }}/100
                    </span>
                </td>
                <td>
                    @if($isSec)<span class="bg">[YA] Secure</span>@else<span class="br">[TIDAK] Insecure</span>@endif
                </td>
                <td>
                    @if($isHttp)<span class="bg">[YA] HttpOnly</span>@else<span class="br">[TIDAK] No</span>@endif
                </td>
                <td>
                    <span class="bgr">{{ $sSite }}</span>
                </td>
                <td>
                    <span style="color:#64748b;font-size:8px">{{ !empty($ck['is_session']) ? 'Autentikasi/Sesi' : 'Tracking/Pref' }}</span>
                </td>
            </tr>
            @if(!empty($ck['masalah']))
            <tr style="background:#fff7ed">
                <td colspan="6" style="padding:3px 6px;color:#9a3412;font-size:8.5px">
                    @foreach($ck['masalah'] as $m)
                    <div>&bull; {{ $m }}</div>
                    @endforeach
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- SECURITY HEADERS --}}
@if(!empty($hdrs['headers_keamanan']))
<div class="sec pb">
    <div class="stl">Security Headers ({{ count($hdrs['headers_keamanan']) }} Diperiksa &mdash; Skor: {{ $hs }}/100)</div>
    @foreach($hdrs['headers_keamanan'] as $hName => $hd)
    @php 
        $hSt = $hd['status'] ?? 'missing'; 
        $hCl = $hSt === 'baik' ? 'ho' : ($hSt === 'misconfigured' ? 'hw' : 'hm'); 
    @endphp
    <table class="{{ $hCl }}" style="width:100%;border-collapse:collapse;border:1px solid #e2e8f0;margin-bottom:3px">
        <tr>
            <td style="width:28%;padding:3px 6px;font-weight:700;font-size:9.5px">
                {{ $hName }}
                @if(!empty($hd['kritis']))<span class="bk" style="margin-left:3px">Kritis</span>@endif
            </td>
            <td style="width:12%;padding:3px;text-align:center">
                @if($hSt === 'baik')<span class="bg">ADA</span>
                @elseif($hSt === 'misconfigured')<span class="by">SALAH</span>
                @else<span class="br">TIDAK ADA</span>@endif
            </td>
            <td style="width:38%;padding:3px 6px;font-size:8.5px;color:#64748b">{{ $hd['deskripsi'] ?? '' }}</td>
            <td style="width:22%;padding:3px 6px;font-size:8.5px;color:#334155;font-style:italic">
                @if(!empty($hd['ada'])){{ mb_substr($hd['nilai'] ?? '', 0, 36) }}@else{{ $hd['catatan'] ?? 'Header tidak ditemukan' }}@endif
            </td>
        </tr>
    </table>
    @endforeach

    @if(!empty($hdrs['headers_sensitif']))
    <div style="margin-top:6px;padding:5px 8px;background:#fffbeb;border:1px solid #fef08a;border-radius:4px">
        <strong style="font-size:9.5px;color:#854d0e">&#9888; Header Sensitif Terekspos:</strong>
        @foreach($hdrs['headers_sensitif'] as $sh => $shd)
        <div style="font-size:8.5px;margin-top:2px">
            <span class="mn" style="font-weight:700">{{ $sh }}:</span> {{ $shd['nilai'] ?? '' }} 
            &mdash; <span style="color:#64748b">{{ $shd['deskripsi'] ?? '' }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif

{{-- DNS RECON & EMAIL SECURITY --}}
@if(!empty($dns))
<div class="sec">
    <div class="stl">DNS Reconnaissance & Email Security</div>
    <table style="width:100%;border-collapse:collapse">
        <tr>
            @if(!empty($dns['ip_addresses']))
            <td style="width:33%;vertical-align:top;padding-right:6px">
                <div style="font-weight:700;font-size:8.5px;color:#64748b;margin-bottom:2px">IP ADDRESS</div>
                @foreach((array)$dns['ip_addresses'] as $ip)
                <div class="mn" style="font-size:9px;margin-bottom:1px">{{ $ip }}</div>
                @endforeach
            </td>
            @endif
            @if(!empty($dns['nameservers']))
            <td style="width:34%;vertical-align:top;padding:0 4px">
                <div style="font-weight:700;font-size:8.5px;color:#64748b;margin-bottom:2px">NAMESERVERS</div>
                @foreach((array)$dns['nameservers'] as $ns)
                <div class="mn" style="font-size:9px;margin-bottom:1px">{{ $ns }}</div>
                @endforeach
            </td>
            @endif
            @if(!empty($dns['mx_records']))
            <td style="width:33%;vertical-align:top;padding-left:6px">
                <div style="font-weight:700;font-size:8.5px;color:#64748b;margin-bottom:2px">MX RECORDS</div>
                @foreach((array)$dns['mx_records'] as $mx)
                <div class="mn" style="font-size:9px;margin-bottom:1px">
                    @if(is_array($mx))[{{ $mx['priority'] ?? 0 }}] {{ $mx['host'] ?? $mx['target'] ?? '' }}@else{{ $mx }}@endif
                </div>
                @endforeach
            </td>
            @endif
        </tr>
    </table>

    @if(!empty($dns['email_security']))
    @php $es = $dns['email_security']; @endphp
    <div style="margin-top:8px">
        <div style="font-weight:700;font-size:8.5px;color:#64748b;margin-bottom:3px">POSTUR KEAMANAN EMAIL (ANTI-SPOOFING)</div>
        <table style="width:65%;border-collapse:collapse">
            <tr>
                @foreach(['spf'=>'SPF','dkim'=>'DKIM','dmarc'=>'DMARC'] as $ek => $el)
                @php $ep = !empty($es[$ek]); @endphp
                <td style="text-align:center;border:1px solid #e2e8f0;padding:3px 6px;background:{{ $ep ? '#f0fdf4' : '#fff5f5' }}">
                    <div style="font-size:10px;font-weight:900;color:{{ $ep ? '#16a34a' : '#dc2626' }}">
                        {{ $ep ? '[VALID]' : '[TIDAK ADA]' }}
                    </div>
                    <div style="font-size:8px;color:#475569;font-weight:600">{{ $el }}</div>
                </td>
                @endforeach
                @php $dp = !empty($dns['dnssec']); @endphp
                <td style="text-align:center;border:1px solid #e2e8f0;padding:3px 6px;background:{{ $dp ? '#f0fdf4' : '#fff5f5' }}">
                    <div style="font-size:10px;font-weight:900;color:{{ $dp ? '#16a34a' : '#dc2626' }}">
                        {{ $dp ? '[AKTIF]' : '[NONAKTIF]' }}
                    </div>
                    <div style="font-size:8px;color:#475569;font-weight:600">DNSSEC</div>
                </td>
            </tr>
        </table>
    </div>
    @endif
</div>
@endif

{{-- PORT SCAN --}}
@if(!empty($ports['ports']))
<div class="sec">
    @php
        $portList  = $ports['ports'];
        $openPorts = collect($portList)->filter(fn($p) => ($p['status'] ?? '') === 'open');
        $openCount = $ports['open_count'] ?? $openPorts->count();
        $criticals = $ports['critical_open'] ?? [];
    @endphp
    <div class="{{ $openCount > 0 ? 'stly' : 'stlg' }}">
        Port Scan ({{ $openCount }} Port Terbuka dari {{ count($portList) }} Diperiksa)
    </div>
    
    @if($openPorts->count() > 0)
    <table class="pt">
        <thead>
            <tr>
                <th style="width:12%">Port</th>
                <th style="width:18%">Layanan</th>
                <th style="width:44%">Deskripsi</th>
                <th style="width:14%">Risiko</th>
                <th style="width:12%">Status</th>
            </tr>
        </thead>
        <tbody>
        @foreach($openPorts as $pt)
        @php $r = $pt['risiko'] ?? ''; @endphp
        <tr class="or">
            <td><strong>{{ $pt['port'] }}</strong></td>
            <td>{{ $pt['service'] }}</td>
            <td style="font-size:8.5px;color:#475569">{{ $pt['deskripsi'] }}</td>
            <td>
                @if($r==='kritis')<span class="bk">KRITIS</span>
                @elseif($r==='tinggi')<span class="bt">TINGGI</span>
                @elseif($r==='sedang')<span class="bs">SEDANG</span>
                @elseif($r==='rendah')<span class="bl">RENDAH</span>
                @else<span class="bgr">{{ strtoupper($r) }}</span>@endif
            </td>
            <td><span class="br">OPEN</span></td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <div style="padding:5px 8px;background:#f0fdf4;border:1px solid #86efac;border-radius:4px;font-size:9px;color:#166534">
        [AMAN] Tidak ada port berisiko atau terbuka dari 23 port umum yang diperiksa.
    </div>
    @endif

    <div style="font-size:8px;color:#64748b;margin-top:4px">
        Port tertutup (aman): {{ count($portList) - $openCount }} port (FTP, SSH, Telnet, SMTP, MySQL, Postgres, Redis, MongoDB, dll).
    </div>

    @if(!empty($criticals))
    <div style="margin-top:5px;padding:4px 7px;background:#fff5f5;border:1px solid #fca5a5;border-radius:4px;font-size:8.5px;color:#991b1b">
        &#9888; Port kritis terbuka: {{ implode(', ', $criticals) }}
    </div>
    @endif
</div>
@endif

{{-- CORS & HTTP METHODS & REDIRECTS --}}
@if(!empty($cors) || !empty($methodList) || !empty($redirects['chain']))
<div class="sec">
    <div class="stl">CORS, HTTP Methods & Redirect Chain</div>
    
    {{-- CORS --}}
    @if(!empty($cors))
    @php $corsVuln = !empty($cors['vulnerable']); @endphp
    <div style="margin-bottom:6px">
        <div style="font-weight:700;font-size:9px;color:#334155;margin-bottom:2px">
            CORS Posture: 
            @if($corsVuln)<span class="br">[BERBAHAYA] Origin Reflected</span>
            @else<span class="bg">[AMAN] Strict / Origin Terproteksi</span>@endif
        </div>
        @if(!empty($corsTests))
        <table style="width:100%;border-collapse:collapse">
            @foreach($corsTests as $cr)
            @php $ref = !empty($cr['origin_reflected']); @endphp
            <tr>
                <td style="padding:2px 4px;font-size:8.5px;font-family:monospace;width:45%">{{ $cr['test_origin'] ?? '' }}</td>
                <td style="padding:2px 4px;font-size:8.5px;width:25%">
                    @if($ref)<span class="fl">[REFLECTED]</span>@else<span class="ok">[BLOCKED]</span>@endif
                </td>
                <td style="padding:2px 4px;font-size:8px;color:#64748b">ACAO: {{ $cr['allow_origin'] ?? 'null' }}</td>
            </tr>
            @endforeach
        </table>
        @endif
    </div>
    @endif

    {{-- HTTP Methods --}}
    @if(!empty($methodList))
    <div style="margin-top:6px">
        <div style="font-weight:700;font-size:9px;color:#334155;margin-bottom:3px">HTTP Method Enumeration:</div>
        <table style="width:100%;border-collapse:collapse">
            <tr>
            @foreach($methodList as $mt)
            @php 
                $ma = $mt['allowed'] ?? false; 
                $md = $mt['berbahaya'] ?? false; 
                $mc = $md ? '#dc2626' : ($ma ? '#16a34a' : '#64748b'); 
                $ml = $md ? 'BAHAYA' : ($ma ? 'Allowed' : 'Blocked'); 
            @endphp
            <td style="text-align:center;border:1px solid #e2e8f0;padding:4px 2px;background:{{ $md ? '#fff5f5' : ($ma ? '#f0fdf4' : '#f8fafc') }}">
                <div style="font-size:10px;font-weight:800;color:{{ $mc }}">{{ $mt['method'] ?? '' }}</div>
                <div style="font-size:7.5px;color:{{ $mc }}">{{ $ml }}</div>
            </td>
            @endforeach
            </tr>
        </table>
    </div>
    @endif

    {{-- Redirect Chain --}}
    @if(!empty($redirects['chain']) && count($redirects['chain']) > 1)
    <div style="margin-top:6px;padding:4px 7px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px">
        <div style="font-weight:700;font-size:8.5px;color:#334155">
            Rantai Redirect ({{ $redirects['total_redirects'] ?? 0 }} Pengalihan):
        </div>
        @foreach($redirects['chain'] as $st)
        <div style="font-size:8.5px;font-family:monospace;margin-top:1px">
            <span class="bgr">{{ $st['status'] ?? '' }}</span> {{ $st['url'] ?? '' }}
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif

{{-- SUBDOMAINS (JIKA ADA) --}}
@if(!empty($subs['subdomains']))
<div class="sec">
    @php
        $allSubs   = $subs['subdomains'];
        $totalSubs = $subs['total'] ?? count($allSubs);
        $hotSubs   = [];
        foreach ($allSubs as $s) {
            $sn = is_array($s) ? ($s['name'] ?? '') : $s;
            $isH = !empty($s['menarik']);
            if (!$isH) {
                foreach ($interestKw as $kw) {
                    if (str_contains(strtolower($sn), $kw)) { $isH = true; break; }
                }
            }
            if ($isH) { $hotSubs[] = $sn; }
        }
    @endphp
    <div class="stl">Subdomain Discovery (via crt.sh &mdash; {{ $totalSubs }} Ditemukan)</div>
    @if(!empty($hotSubs))
    <div style="padding:5px 8px;background:#fff7ed;border:1px solid #fed7aa;border-radius:4px;margin-bottom:6px">
        <strong style="font-size:9px;color:#9a3412">Subdomain Sensitif / Menarik:</strong>
        <div class="su" style="margin-top:3px">
            @foreach(array_slice($hotSubs, 0, 15) as $hs)
            <div class="si sh">{{ $hs }}</div>
            @endforeach
        </div>
    </div>
    @endif
    <div class="su">
        @foreach(array_slice($allSubs, 0, 24) as $s)
        @php $sn = is_array($s) ? ($s['name'] ?? '') : $s; @endphp
        <div class="si">{{ $sn }}</div>
        @endforeach
    </div>
</div>
@endif

{{-- PEMERIKSAAN FILE & PATH SENSITIF --}}
@if(!empty($cfiles))
<div class="sec">
    <div class="{{ $hasExposed ? 'stlr' : 'stlg' }}">
        Pemeriksaan File &amp; Path Sensitif ({{ count($cfiles) }} Path Diperiksa)
    </div>
    <div style="padding:4px 7px;background:{{ $hasExposed ? '#fff5f5' : '#f0fdf4' }};border:1px solid {{ $hasExposed ? '#fca5a5' : '#86efac' }};border-radius:4px;margin-bottom:6px;font-size:9px;color:{{ $hasExposed ? '#991b1b' : '#166534' }};font-weight:700">
        @if($hasExposed)
            [PERINGATAN] {{ $expFiles->count() }} file/path sensitif TEREKSPOS ke publik!
        @else
            [AMAN] Tidak ada file sensitif (.env, backup, git, dll) yang terekspos ke publik.
        @endif
    </div>
    @foreach($fGroups as $cat => $catFiles)
    <div style="margin-bottom:5px">
        <div style="font-size:9px;font-weight:700;color:#64748b;border-bottom:1px solid #f1f5f9;padding-bottom:1px;margin-bottom:2px">
            {{ $cat }}
        </div>
        <table style="width:100%;border-collapse:collapse">
        @foreach(array_chunk($catFiles, 2) as $row)
        <tr>
            @foreach($row as $f)
            @php $exp = !empty($f['terekspos']) || ($f['status'] ?? '') === 'exposed'; @endphp
            <td style="padding:2px 4px;width:50%;font-size:8.5px">
                @if($exp)
                    <span class="fl">[EXPOSED]</span>
                    <strong class="mn" style="color:#b91c1c">{{ $f['path'] ?? $f['url'] ?? '' }}</strong>
                @else
                    <span class="ok">[AMAN]</span>
                    <span class="mn" style="color:#64748b">{{ $f['path'] ?? $f['url'] ?? '' }}</span>
                @endif
            </td>
            @endforeach
        </tr>
        @endforeach
        </table>
    </div>
    @endforeach
</div>
@endif

{{-- ANALISIS AI & TEMUAN (HYBRID ML VERDICT) --}}
@if($scan->ringkasan_eksekutif || $scan->ringkasan_teknis || $temuanSorted->count())
<div class="sec pb">
    <div class="stl">Analisis AI &amp; Intelijen Kerentanan</div>
    
    @if($scan->ringkasan_eksekutif)
    <div style="margin-bottom:8px">
        <div style="font-weight:700;font-size:9.5px;color:#0a0e1a;margin-bottom:2px">Ringkasan Eksekutif</div>
        <div style="font-size:9px;color:#334155;line-height:1.55">{{ $scan->ringkasan_eksekutif }}</div>
    </div>
    @endif

    @if($scan->ringkasan_teknis)
    <div style="margin-bottom:10px;padding:6px 9px;background:#f8fafc;border-left:3px solid #64748b;border-radius:0 4px 4px 0">
        <div style="font-weight:700;font-size:9.5px;color:#0a0e1a;margin-bottom:2px">Ringkasan Teknis Postur Keamanan</div>
        <div style="font-size:9px;color:#475569;line-height:1.55">{{ $scan->ringkasan_teknis }}</div>
    </div>
    @endif

    @if($temuanSorted->count())
    <div style="font-weight:700;font-size:10px;color:#0a0e1a;margin:10px 0 6px">
        Daftar Temuan Keamanan &amp; Hybrid AI Verdict ({{ $temuanSorted->count() }})
    </div>

    @foreach($temuanSorted as $t)
    @php
        $sev = strtolower($t->tingkat_keparahan ?? 'sedang');
        if ($sev === 'kritis' || $sev === 'critical')     { $fClass = 'fc fck'; $bClass = 'bk'; }
        elseif ($sev === 'tinggi' || $sev === 'high')     { $fClass = 'fc fct'; $bClass = 'bt'; }
        elseif ($sev === 'sedang' || $sev === 'medium')   { $fClass = 'fc fcs'; $bClass = 'bs'; }
        else                                              { $fClass = 'fc fcr'; $bClass = 'bl'; }

        $mlSev = strtoupper($t->ml_predicted_severity ?? '');
        $konSev = strtoupper($t->konsensus_severity ?? '');
        $conf = $t->ml_confidence ?? 0;
        $probs = is_array($t->ml_class_probabilities) ? $t->ml_class_probabilities : [];
        if ($probs) { arsort($probs); }

        $isAgree = $mlSev && ($mlSev === strtoupper(match($sev) {
            'kritis', 'critical' => 'CRITICAL',
            'tinggi', 'high'     => 'HIGH',
            'sedang', 'medium'   => 'MEDIUM',
            'rendah', 'low'      => 'LOW',
            default              => $sev
        }));
    @endphp
    <div class="{{ $fClass }}">
        {{-- Title Row --}}
        <table style="width:100%;border-collapse:collapse">
            <tr>
                <td style="vertical-align:top">
                    <span class="{{ $bClass }}" style="margin-right:4px">{{ strtoupper($sev) }}</span>
                    <strong style="font-size:10.5px;color:#0f172a">{{ $t->judul }}</strong>
                    @if($t->cwe_id)<span style="color:#64748b;font-size:8.5px;margin-left:4px">[{{ $t->cwe_id }}]</span>@endif
                    @if($t->cve_id)<span style="color:#0284c7;font-size:8.5px;margin-left:4px">[{{ $t->cve_id }}]</span>@endif
                </td>
                @if($mlSev)
                <td style="text-align:right;vertical-align:top;width:35%">
                    <span class="bp" style="font-size:8px">ML: {{ $mlSev }}</span>
                    @if($isAgree)
                        <span class="bg" style="font-size:8px">LLM &amp; ML Sepakat</span>
                    @else
                        <span class="by" style="font-size:8px">Prediksi Berbeda</span>
                    @endif
                </td>
                @endif
            </tr>
        </table>

        {{-- Deskripsi --}}
        @if($t->deskripsi)
        <div style="font-size:9px;color:#475569;margin-top:4px;line-height:1.45">{{ $t->deskripsi }}</div>
        @endif

        {{-- HYBRID ML VERDICT CARD (PILAR 1) --}}
        @if($mlSev)
        <div class="ml-box">
            <table style="width:100%;border-collapse:collapse;margin-bottom:4px">
                <tr>
                    <td style="width:33%;padding:3px;border-right:1px solid #cbd5e1">
                        <div class="ml-tag">LLM Assessment</div>
                        <div class="ml-val" style="color:{{ $sev==='kritis'||$sev==='tinggi'?'#b91c1c':'#ca8a04' }}">{{ strtoupper($sev) }}</div>
                        <div style="font-size:7.5px;color:#64748b">Gemini / Groq AI</div>
                    </td>
                    <td style="width:33%;padding:3px;border-right:1px solid #cbd5e1;padding-left:6px">
                        <div class="ml-tag">Ensemble ML (RF+XGB)</div>
                        <div class="ml-val" style="color:#0369a1">{{ $mlSev }}</div>
                        <div style="font-size:7.5px;color:#64748b">Confidence: {{ number_format($conf * 100, 1) }}%</div>
                    </td>
                    <td style="width:34%;padding:3px;padding-left:6px">
                        <div class="ml-tag">Konsensus Akhir</div>
                        <div class="ml-val" style="color:{{ $konSev==='CRITICAL'||$konSev==='HIGH'?'#b91c1c':'#ca8a04' }}">{{ $konSev ?: $mlSev }}</div>
                        <div style="font-size:7.5px;color:#64748b">{{ $isAgree ? 'Verified Consensus' : 'Review Diperlukan' }}</div>
                    </td>
                </tr>
            </table>

            {{-- Probabilities Mini Bar --}}
            @if(!empty($probs))
            <div style="margin-top:3px;border-top:1px dashed #cbd5e1;padding-top:3px">
                <span class="ml-tag" style="display:inline-block;margin-right:6px">Distribusi Probabilitas:</span>
                @foreach($probs as $kls => $pr)
                @php $pct = round($pr * 100, 1); @endphp
                @if($pct > 0.5)
                <span style="font-size:8px;color:#334155;margin-right:8px">
                    <strong>{{ $kls }}:</strong> {{ $pct }}%
                </span>
                @endif
                @endforeach
            </div>
            @endif
        </div>
        @endif

        {{-- KODE RENTAN & KODE AMAN --}}
        @if($t->kode_rentan || $t->kode_aman)
        <table class="code-wrap">
            <tr>
                @if($t->kode_rentan)
                <td>
                    <div style="font-weight:700;font-size:8px;color:#991b1b;margin-bottom:1px">&#10007; Kode Rentan:</div>
                    <div class="code-box code-vuln">{{ $t->kode_rentan }}</div>
                </td>
                @endif
                @if($t->kode_aman)
                <td>
                    <div style="font-weight:700;font-size:8px;color:#166534;margin-bottom:1px">&#10003; Kode Aman (Rekomendasi):</div>
                    <div class="code-box code-safe">{{ $t->kode_aman }}</div>
                </td>
                @endif
            </tr>
        </table>
        @endif

        {{-- Remediasi & Metadata --}}
        @if($t->remediasi)
        <div style="font-size:9px;color:#1e40af;margin-top:4px">
            <strong>Remediasi:</strong> {{ $t->remediasi }}
        </div>
        @endif

        <div style="font-size:8px;color:#64748b;margin-top:4px">
            @if(!empty($t->lokasi))<span>Lokasi: <span class="mn">{{ $t->lokasi }}</span></span> &bull; @endif
            @if(!empty($t->estimasi_usaha))<span>Usaha: <strong>{{ ucfirst($t->estimasi_usaha) }}</strong></span> &bull; @endif
            @if(!empty($t->tingkat_kepercayaan))<span>Kepercayaan: <strong>{{ ucfirst($t->tingkat_kepercayaan) }}</strong></span> &bull; @endif
            @if(!empty($t->mitre_attack))<span>MITRE ATT&amp;CK: <strong style="color:#7c3aed">{{ $t->mitre_attack }}</strong></span>@endif
        </div>
    </div>
    @endforeach
    @endif
</div>
@endif

{{-- SUMBER DATA RAG (KNOWLEDGE BASE) --}}
@if(!empty($ragRefs))
<div class="sec">
    @php
        $avgScore = round(collect($ragRefs)->avg(fn($r) => $r['sem_score'] ?? $r['score'] ?? 0) * 100);
        $uniqueSources = collect($ragRefs)->pluck('source')->unique()->filter()->values();
    @endphp
    <div class="stlp">Sumber Data RAG (Knowledge Base &mdash; {{ count($ragRefs) }} Dokumen Digunakan)</div>
    <div style="font-size:8.5px;color:#475569;margin-bottom:6px">
        Analisis AI diperkuat oleh Retrieval-Augmented Generation (RAG) dengan rata-rata relevansi semantik <strong>{{ $avgScore }}%</strong> dari basis pengetahuan terverifikasi:
        @foreach($uniqueSources as $src)
            <span class="bp" style="margin-left:3px">{{ strtoupper(str_replace('hf:', '', $src)) }}</span>
        @endforeach
    </div>

    @foreach(array_slice($ragRefs, 0, 4) as $i => $ref)
    @php
        $score = round(($ref['sem_score'] ?? $ref['score'] ?? 0) * 100);
        $sColor = $score >= 75 ? '#166534' : ($score >= 60 ? '#854d0e' : '#991b1b');
        $sBg = $score >= 75 ? '#dcfce7' : ($score >= 60 ? '#fef9c3' : '#fee2e2');
    @endphp
    <div class="rag-item">
        <table style="width:100%;border-collapse:collapse">
            <tr>
                <td>
                    <span class="bp" style="font-size:8px">#{{ $i + 1 }} {{ strtoupper($ref['source'] ?? 'RAG') }}</span>
                    <strong style="font-size:9px;color:#0f172a;margin-left:4px">{{ $ref['title'] ?? 'Dokumen Referensi' }}</strong>
                </td>
                <td style="text-align:right;width:25%">
                    <span style="font-size:8px;font-weight:700;color:{{ $sColor }};background:{{ $sBg }};padding:1px 5px;border-radius:2px">
                        {{ $score }}% Relevan
                    </span>
                </td>
            </tr>
        </table>
        <div style="font-size:8px;color:#64748b;margin-top:3px;line-height:1.4">
            {{ Str::limit($ref['content'] ?? '', 220) }}
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- SIMULASI SERANGAN (RED TEAM) --}}
@if($scan->simulasiSerangan->count())
<div class="sec">
    <div class="stlr">Simulasi Skenario Serangan (Red Team Killchain)</div>
    @foreach($scan->simulasiSerangan as $sim)
    <div class="sim-box">
        <table style="width:100%;border-collapse:collapse">
            <tr>
                <td>
                    <div class="sim-title">{{ $sim->nama_skenario ?? 'Simulasi Serangan' }}</div>
                </td>
                <td style="text-align:right;vertical-align:top">
                    <span class="br" style="font-size:8px">KEMUNGKINAN: {{ $sim->skor_kemungkinan ?? 0 }}/10</span>
                    <span class="br" style="font-size:8px;margin-left:3px">DAMPAK: {{ $sim->skor_dampak ?? 0 }}/10</span>
                </td>
            </tr>
        </table>

        <div style="font-size:8.5px;color:#94a3b8;margin-bottom:6px">
            <strong>Profil Penyerang:</strong> <span style="color:#00d4ff">{{ ucfirst(str_replace('_', ' ', $sim->profil_penyerang ?? 'Oportunistis')) }}</span> &bull;
            <strong>Target:</strong> <span class="mn" style="color:#fef08a">{{ $scan->target }}</span>
        </div>

        @if($sim->narasi_eksekutif)
        <div style="font-size:9px;color:#e2e8f0;line-height:1.45;margin-bottom:5px">
            <strong>Ringkasan Skenario:</strong> {{ $sim->narasi_eksekutif }}
        </div>
        @endif

        @if($sim->narasi_teknis)
        <div style="font-size:8.5px;color:#cbd5e1;line-height:1.4;margin-bottom:6px">
            <strong>Analisis Vektor:</strong> {{ $sim->narasi_teknis }}
        </div>
        @endif

        {{-- Langkah Serangan (Attack Chain) --}}
        @if(!empty($sim->rantai_serangan) && is_array($sim->rantai_serangan))
        <div style="margin-top:6px">
            <div style="font-size:8.5px;font-weight:700;color:#f87171;margin-bottom:2px">RANTAI SERANGAN (KILLCHAIN):</div>
            @foreach($sim->rantai_serangan as $step)
            <div class="sim-step">{{ $step }}</div>
            @endforeach
        </div>
        @endif

        {{-- Fase ATT&CK --}}
        @if(!empty($sim->fase_attck) && is_array($sim->fase_attck))
        <div style="margin-top:6px">
            <span style="font-size:8px;color:#94a3b8;font-weight:700">MITRE ATT&amp;CK TACTICS:</span>
            @foreach($sim->fase_attck as $fase)
            <span style="display:inline-block;background:#1e293b;color:#f1f5f9;border:1px solid #334155;border-radius:2px;padding:1px 5px;font-size:7.5px;margin-left:3px">
                {{ $fase }}
            </span>
            @endforeach
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- DISCLAIMER --}}
<div class="dc">
    <strong>&#9888; Penting &amp; Batasan Tanggung Jawab:</strong>
    Data reputasi bersumber dari VirusTotal dan URLScan.io. Subdomain dari crt.sh Certificate Transparency.
    DNS reconnaissance menggunakan resolver lokal. Port scan hanya memeriksa port umum dengan timeout cepat.
    Analisis AI berbasis knowledge base keamanan publik (NVD, MITRE ATT&amp;CK, OWASP, CWE, CISA KEV) serta model Ensemble ML (Random Forest + XGBoost).
    <strong>Laporan ini merupakan penilaian keamanan berbasis AI untuk deteksi dini dan edukasi, bukan penetration testing profesional menyeluruh.</strong>
    Skenario serangan yang tertulis adalah <em>simulasi ilustratif</em> yang disusun AI berdasarkan temuan — bukan bukti eksploitasi yang telah dikonfirmasi.
    Jangan gunakan laporan ini sebagai satu-satunya dasar pengambilan keputusan keamanan kritis. Konsultasikan dengan penguji keamanan profesional jika diperlukan.
    Digenerate secara otomatis oleh RedSim Security Engine pada {{ now()->format('d M Y, H:i') }} WIB.
</div>

</body>
</html>
