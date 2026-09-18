<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Laporan RedSim</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DejaVu Sans',sans-serif;color:#1a1a2e;font-size:11px;line-height:1.55}
.rh{background:#0a0e1a;color:#fff;padding:22px 28px}
.rh h1{font-size:19px;font-weight:900;color:#00d4ff}
.rh .meta{font-size:10px;color:#94a3b8;margin-top:5px}
.rh .meta span{margin-right:16px}
.sg{background:#f8fafc;border-bottom:3px solid #00d4ff}
.sg table{width:100%;border-collapse:collapse}
.sg td{text-align:center;padding:14px 8px;border-right:1px solid #e2e8f0}
.sg td:last-child{border-right:none}
.sn{font-size:30px;font-weight:900;line-height:1}
.sl{font-size:9px;color:#64748b;margin-top:3px;text-transform:uppercase}
.sec{padding:13px 20px;border-bottom:1px solid #e2e8f0}
.stl{font-size:11px;font-weight:700;color:#0a0e1a;border-left:3px solid #00d4ff;padding-left:7px;margin-bottom:9px;text-transform:uppercase}
.stlr{font-size:11px;font-weight:700;color:#0a0e1a;border-left:3px solid #dc2626;padding-left:7px;margin-bottom:9px;text-transform:uppercase}
.stlg{font-size:11px;font-weight:700;color:#0a0e1a;border-left:3px solid #16a34a;padding-left:7px;margin-bottom:9px;text-transform:uppercase}
.stly{font-size:11px;font-weight:700;color:#0a0e1a;border-left:3px solid #ca8a04;padding-left:7px;margin-bottom:9px;text-transform:uppercase}
.bk{display:inline-block;padding:1px 6px;border-radius:3px;font-size:9px;font-weight:700;background:#fca5a5;color:#7f1d1d}
.bt{display:inline-block;padding:1px 6px;border-radius:3px;font-size:9px;font-weight:700;background:#fed7aa;color:#9a3412}
.bs{display:inline-block;padding:1px 6px;border-radius:3px;font-size:9px;font-weight:700;background:#fef08a;color:#854d0e}
.bl{display:inline-block;padding:1px 6px;border-radius:3px;font-size:9px;font-weight:700;background:#bbf7d0;color:#166534}
.bg{display:inline-block;padding:1px 6px;border-radius:3px;font-size:9px;font-weight:700;background:#dcfce7;color:#166534}
.br{display:inline-block;padding:1px 6px;border-radius:3px;font-size:9px;font-weight:700;background:#fee2e2;color:#991b1b}
.by{display:inline-block;padding:1px 6px;border-radius:3px;font-size:9px;font-weight:700;background:#fef9c3;color:#854d0e}
.bgr{display:inline-block;padding:1px 6px;border-radius:3px;font-size:9px;font-weight:700;background:#f1f5f9;color:#475569}
.it{width:100%;border-collapse:collapse}
.it td{padding:4px 8px;border-bottom:1px solid #f1f5f9;font-size:10px}
.it td:first-child{color:#64748b;width:38%;font-weight:600}
.pt{width:100%;border-collapse:collapse;margin-top:5px}
.pt th{background:#f1f5f9;padding:4px 7px;text-align:left;font-size:9px;font-weight:700;color:#475569;border-bottom:2px solid #e2e8f0}
.pt td{padding:4px 7px;border-bottom:1px solid #f1f5f9;font-size:10px}
.or{background:#fff7ed}
.ho{background:#f0fdf4}
.hm{background:#fff5f5}
.hw{background:#fffbeb}
.fc{border:1px solid #e2e8f0;border-radius:5px;padding:8px 10px;margin-bottom:6px;page-break-inside:avoid}
.fck{border-left:4px solid #7f1d1d;background:#fff5f5}
.fct{border-left:4px solid #ea580c;background:#fff7ed}
.fcs{border-left:4px solid #ca8a04;background:#fefce8}
.fcr{border-left:4px solid #16a34a;background:#f0fdf4}
.sc{background:#0a0e1a;color:#f1f5f9;border-radius:5px;padding:8px 10px;margin-bottom:6px}
.sc2{font-size:10px;font-weight:700;color:#00d4ff;margin-bottom:3px}
.su{column-count:3;column-gap:9px}
.si{font-size:9px;color:#475569;font-family:monospace;padding:1px 0}
.sh{color:#dc2626;font-weight:700}
.mn{font-family:monospace}
.ok{color:#16a34a;font-weight:900}
.fl{color:#dc2626;font-weight:900}
.dc{background:#fff7ed;border:1px solid #fed7aa;border-radius:5px;padding:9px 13px;margin:13px 20px;font-size:9px;color:#92400e}
.pb{page-break-before:always}
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
$dns       = $data['dns_recon']        ?? [];
$ports     = $data['port_scan']        ?? [];
$cors      = $data['cors_config']      ?? [];
$httpMAll  = $data['http_methods']     ?? [];
$subs      = $data['subdomains']       ?? [];
$cfiles    = $data['common_files']     ?? [];
$breakdown = $data['skor_breakdown']   ?? [];
$srvInfo   = $data['server_info']      ?? [];
$methodList= $httpMAll['methods']      ?? [];
$corsTests = $cors['tests']            ?? $cors['results'] ?? [];

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
    $sevMap = ['kritis'=>4,'tinggi'=>3,'sedang'=>2,'rendah'=>1];
    $temuanSorted = $scan->temuan->sortByDesc(function($t) use ($sevMap) {
        return $sevMap[$t->tingkat_keparahan] ?? 0;
    });
}

// Precompute file groups
$expFiles   = collect($cfiles)->filter(function($f){ return !empty($f['terekspos']) || ($f['status'] ?? '') === 'exposed'; });
$hasExposed = $expFiles->count() > 0;
$fGroups    = [];
$catNames   = [
    'source_control' => 'Source Control',
    'config_leak'    => 'Config and Environment',
    'backup'         => 'Backup and Database',
    'debug'          => 'Debug and Info Disclosure',
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

{{-- HEADER --}}
<div class="rh">
    <h1>Laporan Keamanan RedSim</h1>
    <div class="meta">
        <span><strong>Target:</strong> {{ $scan->target ?? $scan->nama_file }}</span>
        <span><strong>Mode:</strong> {{ ucfirst($scan->mode_scan) }}</span>
        <span><strong>Tipe:</strong> {{ ucfirst($scan->tipe_scan) }}</span>
        <span><strong>Tanggal:</strong> {{ $scan->created_at->format('d M Y, H:i') }} WIB</span>
        <span><strong>ID:</strong> #{{ substr($scan->id, 0, 8) }}</span>
    </div>
</div>

{{-- SCORE OVERVIEW --}}
<div class="sg"><table><tr>
    <td>
        <div class="sn" style="color:{{ $skorC }}">{{ $skor }}</div>
        <div class="sl">Skor Reputasi</div>
    </td>
    <td>
        <div class="sn" style="color:{{ $statusC }};font-size:17px;padding-top:9px">{{ $statusLbl }}</div>
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
</tr></table></div>

{{-- BREAKDOWN --}}
@if($isIntens && !empty($breakdown))
<div class="sec">
    <div class="stl">Breakdown Skor Keamanan</div>
    @php
    $bkeys = ['virustotal'=>'VirusTotal','headers'=>'Headers','ssl'=>'SSL/TLS',
              'cookies'=>'Cookies','cors'=>'CORS','http_methods'=>'HTTP Methods','redirects'=>'Redirects'];
    @endphp
    <table style="width:100%;border-collapse:collapse"><tr>
    @foreach($bkeys as $k => $lbl)
    @if(isset($breakdown[$k]['skor']))
    @php $bs=$breakdown[$k]['skor']; $bc=$bs>=70?'#16a34a':($bs>=40?'#ca8a04':'#dc2626'); @endphp
    <td style="text-align:center;border:1px solid #e2e8f0;padding:7px 3px">
        <div style="font-size:19px;font-weight:900;color:{{ $bc }}">{{ $bs }}</div>
        <div style="font-size:8px;color:#64748b">{{ $lbl }}</div>
        <div style="font-size:8px;color:#94a3b8">{{ $breakdown[$k]['bobot'] ?? '' }}</div>
    </td>
    @endif
    @endforeach
    </tr></table>
    <div style="margin-top:6px">
        @if(!empty($breakdown['email_security']['bonus']) && $breakdown['email_security']['bonus'] > 0)
        <span style="display:inline-block;background:#dcfce7;color:#166534;border:1px solid #86efac;border-radius:4px;padding:2px 7px;font-size:9px;margin:2px">+{{ $breakdown['email_security']['bonus'] }} Email Security</span>
        @endif
        @if(!empty($breakdown['open_ports']['penalty']) && $breakdown['open_ports']['penalty'] < 0)
        <span style="display:inline-block;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:4px;padding:2px 7px;font-size:9px;margin:2px">{{ $breakdown['open_ports']['penalty'] }} Port Terbuka</span>
        @endif
        @if(!empty($breakdown['exposed_files']['penalty']) && $breakdown['exposed_files']['penalty'] < 0)
        <span style="display:inline-block;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:4px;padding:2px 7px;font-size:9px;margin:2px">{{ $breakdown['exposed_files']['penalty'] }} File Sensitif</span>
        @endif
    </div>
</div>
@endif

{{-- SSL --}}
@if(!empty($ssl))
<div class="sec">
@php $sslValid = !empty($ssl['valid']); @endphp
<div class="{{ $sslValid ? 'stlg' : 'stlr' }}">Status SSL/TLS</div>
<table class="it">
    <tr><td>Status</td><td>
        @if($sslValid)<span class="ok">&#10003;</span> Valid
        @else<span class="fl">&#10007;</span> Tidak Valid @endif
    </td></tr>
    @if(!empty($ssl['issuer']))<tr><td>Penerbit</td><td>{{ $ssl['issuer'] }}</td></tr>@endif
    @if(isset($ssl['days_until_expiry']))
    <tr><td>Kedaluwarsa</td><td>
    @php $dExp = (int)$ssl['days_until_expiry']; @endphp
    @if($dExp < 0)<span style="color:#dc2626">Kedaluwarsa {{ abs($dExp) }} hari lalu</span>
    @elseif($dExp < 30)<span style="color:#ca8a04">{{ $dExp }} hari lagi &#9888;</span>
    @else{{ $dExp }} hari lagi @endif
    </td></tr>
    @endif
    @if(!empty($ssl['protocol']))<tr><td>Protokol</td><td>{{ $ssl['protocol'] }}
        @if(!empty($ssl['weak_protocol']))<span class="br" style="margin-left:5px">LEMAH</span>@endif
    </td></tr>@endif
    @if(!empty($ssl['cipher_name']))<tr><td>Cipher Suite</td><td class="mn">{{ $ssl['cipher_name'] }}
        @if(!empty($ssl['cipher_bits'])) ({{ $ssl['cipher_bits'] }}-bit)@endif
    </td></tr>@endif
    @if(!empty($ssl['signature_alg']))<tr><td>Signature Algorithm</td><td>{{ $ssl['signature_alg'] }}</td></tr>@endif
</table>
</div>
@endif

{{-- SECURITY HEADERS --}}
@if(!empty($hdrs['headers_keamanan']))
<div class="sec pb">
<div class="stl">Security Headers ({{ count($hdrs['headers_keamanan']) }} Diperiksa)</div>
@foreach($hdrs['headers_keamanan'] as $hName => $hd)
@php $hSt = $hd['status'] ?? 'missing'; $hCl = $hSt==='baik' ? 'ho' : ($hSt==='misconfigured' ? 'hw' : 'hm'); @endphp
<table class="{{ $hCl }}" style="width:100%;border-collapse:collapse;border:1px solid #e2e8f0;margin-bottom:3px"><tr>
    <td style="width:27%;padding:4px 7px;font-weight:700;font-size:10px">
        {{ $hName }}
        @if(!empty($hd['kritis']))<span class="bk" style="margin-left:4px">Kritis</span>@endif
    </td>
    <td style="width:12%;padding:4px;text-align:center">
        @if($hSt==='baik')<span class="bg">ADA</span>
        @elseif($hSt==='misconfigured')<span class="by">SALAH</span>
        @else<span class="br">TIDAK ADA</span>@endif
    </td>
    <td style="width:40%;padding:4px 7px;font-size:9px;color:#64748b">{{ $hd['deskripsi'] ?? '' }}</td>
    <td style="width:21%;padding:4px 7px;font-size:9px;color:#475569;font-style:italic">
        @if(!empty($hd['ada'])){{ mb_substr($hd['nilai'] ?? '', 0, 38) }}@else{{ $hd['catatan'] ?? 'Tidak ada' }}@endif
    </td>
</tr></table>
@endforeach
@if(!empty($hdrs['headers_sensitif']))
<div style="margin-top:8px;padding:6px 8px;background:#fffbeb;border:1px solid #fef08a;border-radius:4px">
    <strong style="font-size:10px;color:#854d0e">&#9888; Header Sensitif Terekspos:</strong>
    @foreach($hdrs['headers_sensitif'] as $sh => $shd)
    <div style="font-size:9px;margin-top:2px"><span class="mn" style="font-weight:700">{{ $sh }}:</span> {{ $shd['nilai'] ?? '' }} &mdash; <span style="color:#64748b">{{ $shd['deskripsi'] ?? '' }}</span></div>
    @endforeach
</div>
@endif
</div>
@endif

{{-- DNS RECON --}}
@if(!empty($dns))
<div class="sec">
<div class="stl">DNS Reconnaissance</div>
<table style="width:100%;border-collapse:collapse"><tr>
    @if(!empty($dns['ip_addresses']))
    <td style="width:33%;vertical-align:top;padding-right:9px">
        <div style="font-weight:700;font-size:9px;color:#64748b;margin-bottom:3px">IP ADDRESS</div>
        @foreach((array)$dns['ip_addresses'] as $ip)
        <div class="mn" style="font-size:10px;margin-bottom:2px">{{ $ip }}</div>
        @endforeach
    </td>
    @endif
    @if(!empty($dns['nameservers']))
    <td style="width:34%;vertical-align:top;padding:0 5px">
        <div style="font-weight:700;font-size:9px;color:#64748b;margin-bottom:3px">NAMESERVERS</div>
        @foreach((array)$dns['nameservers'] as $ns)
        <div class="mn" style="font-size:10px;margin-bottom:2px">{{ $ns }}</div>
        @endforeach
    </td>
    @endif
    @if(!empty($dns['mx_records']))
    <td style="width:33%;vertical-align:top;padding-left:9px">
        <div style="font-weight:700;font-size:9px;color:#64748b;margin-bottom:3px">MX RECORDS</div>
        @foreach((array)$dns['mx_records'] as $mx)
        <div class="mn" style="font-size:10px;margin-bottom:2px">
            @if(is_array($mx))[{{ $mx['priority'] ?? 0 }}] {{ $mx['host'] ?? $mx['target'] ?? '' }}@else{{ $mx }}@endif
        </div>
        @endforeach
    </td>
    @endif
</tr></table>
@if(!empty($dns['email_security']))
@php $es = $dns['email_security']; @endphp
<div style="margin-top:9px">
    <div style="font-weight:700;font-size:9px;color:#64748b;margin-bottom:4px">EMAIL SECURITY</div>
    <table style="width:55%;border-collapse:collapse"><tr>
        @foreach(['spf'=>'SPF','dkim'=>'DKIM','dmarc'=>'DMARC'] as $ek => $el)
        @php $ep = !empty($es[$ek]); @endphp
        <td style="text-align:center;border:1px solid #e2e8f0;padding:4px 7px">
            <div style="font-size:14px;color:{{ $ep ? '#16a34a' : '#dc2626' }}">{{ $ep ? '&#10003;' : '&#10007;' }}</div>
            <div style="font-size:9px;color:#64748b">{{ $el }}</div>
        </td>
        @endforeach
        @php $dp = !empty($dns['dnssec']); @endphp
        <td style="text-align:center;border:1px solid #e2e8f0;padding:4px 7px">
            <div style="font-size:14px;color:{{ $dp ? '#16a34a' : '#dc2626' }}">{{ $dp ? '&#10003;' : '&#10007;' }}</div>
            <div style="font-size:9px;color:#64748b">DNSSEC</div>
        </td>
    </tr></table>
</div>
@endif
</div>
@endif

{{-- PORT SCAN --}}
@if(!empty($ports['ports']))
<div class="sec">
@php
$portList  = $ports['ports'];
$openCount = $ports['open_count'] ?? collect($portList)->where('status','open')->count();
$criticals = $ports['critical_open'] ?? [];
@endphp
<div class="{{ $openCount > 0 ? 'stly' : 'stlg' }}">Port Scan</div>
<div style="font-size:10px;margin-bottom:6px;color:#64748b">{{ $openCount }} port terbuka dari {{ count($portList) }} yang diperiksa</div>
<table class="pt">
    <thead><tr><th>Port</th><th>Layanan</th><th>Deskripsi</th><th>Risiko</th><th>Status</th></tr></thead>
    <tbody>
    @foreach(collect($portList)->sortByDesc(function($p){ return $p['status']==='open' ? 1 : 0; }) as $pt)
    @php $isOpen = ($pt['status'] ?? '') === 'open'; $r = $pt['risiko'] ?? ''; @endphp
    <tr class="{{ $isOpen ? 'or' : '' }}" style="{{ !$isOpen ? 'color:#94a3b8' : '' }}">
        <td><strong>{{ $pt['port'] }}</strong></td>
        <td>{{ $pt['service'] }}</td>
        <td style="font-size:9px;color:#64748b">{{ $pt['deskripsi'] }}</td>
        <td>@if($r==='kritis')<span class="bk">KRITIS</span>@elseif($r==='tinggi')<span class="bt">TINGGI</span>@elseif($r==='sedang')<span class="bs">SEDANG</span>@elseif($r==='rendah')<span class="bl">RENDAH</span>@else<span class="bgr">{{ strtoupper($r) }}</span>@endif</td>
        <td>@if($isOpen)<span class="br">OPEN</span>@else<span style="color:#94a3b8;font-size:9px">Closed</span>@endif</td>
    </tr>
    @endforeach
    </tbody>
</table>
@if(!empty($criticals))
<div style="margin-top:6px;padding:5px 8px;background:#fff5f5;border:1px solid #fca5a5;border-radius:4px;font-size:9px;color:#991b1b">
    &#9888; Port kritis terbuka: {{ implode(', ', $criticals) }}
</div>
@endif
</div>
@endif

{{-- CORS --}}
@if(!empty($cors))
<div class="sec">
@php $corsVuln = !empty($cors['vulnerable']); @endphp
<div class="{{ $corsVuln ? 'stlr' : 'stlg' }}">CORS Configuration</div>
<div style="padding:5px 8px;background:{{ $corsVuln?'#fff5f5':'#f0fdf4' }};border:1px solid {{ $corsVuln?'#fca5a5':'#86efac' }};border-radius:4px;margin-bottom:7px;font-size:10px;color:{{ $corsVuln?'#991b1b':'#166534' }};font-weight:700">
    {{ $corsVuln ? '&#9888; CORS dikonfigurasi dengan tidak aman' : '&#10003; CORS dikonfigurasi dengan aman' }}
</div>
@if(!empty($corsTests))
<table style="width:100%;border-collapse:collapse">
    @foreach($corsTests as $cr)
    @php $ref = !empty($cr['origin_reflected']); @endphp
    <tr style="border-bottom:1px solid #f1f5f9">
        <td style="padding:4px 7px;font-size:10px;font-family:monospace;width:44%">{{ $cr['test_origin'] ?? '' }}</td>
        <td style="padding:4px 7px;font-size:10px;width:30%">@if($ref)<span class="fl">Reflected &#10007;</span>@else<span class="ok">Blocked &#10003;</span>@endif</td>
        <td style="padding:4px 7px;font-size:9px;color:#64748b">ACAO: {{ $cr['allow_origin'] ?? 'null' }}</td>
    </tr>
    @endforeach
</table>
@endif
</div>
@endif

{{-- HTTP METHODS --}}
@if(!empty($methodList))
<div class="sec">
@php $dangerM = collect($methodList)->filter(function($m){ return !empty($m['berbahaya']); }); @endphp
<div class="{{ $dangerM->count() > 0 ? 'stly' : 'stlg' }}">HTTP Method Enumeration</div>
@foreach($dangerM as $dm)
<div style="padding:4px 7px;background:#fffbeb;border-left:3px solid #ca8a04;margin-bottom:4px;font-size:10px;color:#854d0e">
    &#9888; Method {{ $dm['method'] }} diizinkan &mdash; {{ $dm['deskripsi'] ?? '' }}
</div>
@endforeach
<table style="width:100%;border-collapse:collapse;margin-top:7px"><tr>
    @foreach($methodList as $mt)
    @php $ma=$mt['allowed']??false; $md=$mt['berbahaya']??false; $mc=$md?'#dc2626':($ma?'#16a34a':'#94a3b8'); $ml=$md?'BERBAHAYA':($ma?'Allowed':'Blocked'); @endphp
    <td style="text-align:center;border:1px solid #e2e8f0;padding:7px 4px">
        <div style="font-size:12px;font-weight:800;color:{{ $mc }}">{{ $mt['method'] ?? '' }}</div>
        <div style="font-size:9px;color:{{ $mc }};margin-top:2px">{{ $ml }}</div>
    </td>
    @endforeach
</tr></table>
</div>
@endif

{{-- SERVER INFO --}}
@if(!empty($srvInfo))
<div class="sec">
<div class="stl">Informasi Server</div>
<table class="it">
    @if(!empty($srvInfo['ip']))<tr><td>IP Address</td><td class="mn">{{ $srvInfo['ip'] }}</td></tr>@endif
    @if(!empty($srvInfo['server']))<tr><td>Server</td><td>{{ $srvInfo['server'] }}</td></tr>@endif
    @if(!empty($srvInfo['powered_by']))<tr><td>Powered By</td><td>{{ $srvInfo['powered_by'] }}</td></tr>@endif
    @if(!empty($srvInfo['country']))<tr><td>Negara</td><td>{{ $srvInfo['country'] }}</td></tr>@endif
    @if(!empty($srvInfo['asn']))<tr><td>ASN</td><td>{{ $srvInfo['asn'] }}</td></tr>@endif
</table>
</div>
@endif

{{-- SUBDOMAINS --}}
@if(!empty($subs['subdomains']))
<div class="sec pb">
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
<div class="stl">Subdomain Discovery (via crt.sh)</div>
<div style="font-size:10px;color:#64748b;margin-bottom:7px">{{ $totalSubs }} subdomain ditemukan dari Certificate Transparency logs</div>
@if(!empty($hotSubs))
<div style="padding:7px 9px;background:#fff7ed;border:1px solid #fed7aa;border-radius:4px;margin-bottom:9px">
    <div style="font-weight:700;font-size:10px;color:#9a3412;margin-bottom:4px">Subdomain Menarik:</div>
    <div class="su">@foreach($hotSubs as $s)<div class="si sh">{{ $s }}</div>@endforeach</div>
</div>
@endif
<div class="su">
@foreach($allSubs as $s)
@php
$sn = is_array($s) ? ($s['name'] ?? '') : $s;
$sHot = !empty($s['menarik']);
if (!$sHot) { foreach ($interestKw as $kw) { if (str_contains(strtolower($sn), $kw)) { $sHot=true; break; } } }
@endphp
<div class="si {{ $sHot ? 'sh' : '' }}">{{ $sn }}</div>
@endforeach
</div>
</div>
@endif

{{-- SENSITIVE FILES --}}
@if(!empty($cfiles))
<div class="sec">
<div class="{{ $hasExposed ? 'stlr' : 'stlg' }}">Pemeriksaan File Sensitif ({{ count($cfiles) }})</div>
<div style="padding:5px 8px;background:{{ $hasExposed?'#fff5f5':'#f0fdf4' }};border:1px solid {{ $hasExposed?'#fca5a5':'#86efac' }};border-radius:4px;margin-bottom:7px;font-size:10px;color:{{ $hasExposed?'#991b1b':'#166534' }};font-weight:700">
    @if($hasExposed)&#9888; {{ $expFiles->count() }} file sensitif TEREKSPOS!@else&#10003; Tidak ada file sensitif yang terekspos @endif
</div>
@foreach($fGroups as $cat => $catFiles)
<div style="margin-bottom:6px">
    <div style="font-size:10px;font-weight:700;color:#64748b;border-bottom:1px solid #f1f5f9;padding-bottom:2px;margin-bottom:3px">{{ $cat }}</div>
    <table style="width:100%;border-collapse:collapse">
    @foreach(array_chunk($catFiles, 2) as $row)
    <tr>
        @foreach($row as $f)
        @php $exp = !empty($f['terekspos']) || ($f['status'] ?? '') === 'exposed'; @endphp
        <td style="padding:2px 5px;width:50%;font-size:9px">
            <span style="color:{{ $exp?'#dc2626':'#16a34a' }};font-weight:{{ $exp?'700':'400' }}">{{ $exp?'&#10007;':'&#10003;' }}</span>
            <span class="mn" style="margin-left:3px">{{ $f['path'] ?? $f['url'] ?? '' }}</span>
            <span style="color:#94a3b8;margin-left:3px">{{ $exp?'TEREKSPOS':'Aman' }}</span>
        </td>
        @endforeach
    </tr>
    @endforeach
    </table>
</div>
@endforeach
</div>
@endif

{{-- AI ANALYSIS --}}
@if($scan->ringkasan_eksekutif || $scan->ringkasan_teknis || $scan->temuan->count())
<div class="sec pb">
<div class="stl">Analisis AI</div>
@if($scan->ringkasan_eksekutif)
<div style="margin-bottom:9px">
    <div style="font-weight:700;font-size:10px;color:#1e2d4a;margin-bottom:3px">Ringkasan Eksekutif</div>
    <div style="font-size:10px;color:#334155;line-height:1.6">{{ $scan->ringkasan_eksekutif }}</div>
</div>
@endif
@if($scan->ringkasan_teknis)
<div style="margin-bottom:9px;padding:7px 9px;background:#f8fafc;border-left:3px solid #94a3b8;border-radius:0 4px 4px 0">
    <div style="font-weight:700;font-size:10px;color:#1e2d4a;margin-bottom:3px">Ringkasan Teknis</div>
    <div style="font-size:10px;color:#475569;line-height:1.6">{{ $scan->ringkasan_teknis }}</div>
</div>
@endif
@if($temuanSorted->count())
<div style="font-weight:700;font-size:10px;color:#1e2d4a;margin:9px 0 5px">Temuan ({{ $temuanSorted->count() }})</div>
@foreach($temuanSorted as $t)
@php
$sev = $t->tingkat_keparahan;
if ($sev==='kritis')      { $fClass='fc fck'; $bClass='bk'; }
elseif ($sev==='tinggi')  { $fClass='fc fct'; $bClass='bt'; }
elseif ($sev==='sedang')  { $fClass='fc fcs'; $bClass='bs'; }
else                      { $fClass='fc fcr'; $bClass='bl'; }
@endphp
<div class="{{ $fClass }}">
    <div>
        <span class="{{ $bClass }}" style="margin-right:5px">{{ strtoupper($sev) }}</span>
        <strong style="font-size:11px">{{ $t->judul }}</strong>
        @if($t->cwe_id)<span style="color:#94a3b8;font-size:9px;margin-left:5px">({{ $t->cwe_id }})</span>@endif
    </div>
    @if($t->deskripsi)<div style="font-size:10px;color:#475569;margin-top:4px">{{ $t->deskripsi }}</div>@endif
    @if(!empty($t->lokasi))<div style="font-size:9px;color:#94a3b8;margin-top:3px;font-family:monospace">{{ $t->lokasi }}</div>@endif
    @if($t->remediasi)<div style="font-size:10px;color:#1e40af;margin-top:4px"><strong>Remediasi:</strong> {{ $t->remediasi }}</div>@endif
</div>
@endforeach
@endif
</div>
@endif

{{-- ATTACK SIMULATIONS --}}
@if($scan->simulasiSerangan->count())
<div class="sec">
<div class="stlr">Simulasi Serangan</div>
@foreach($scan->simulasiSerangan as $sim)
<div class="sc">
    <div class="sc2">{{ $sim->judul ?? $sim->nama ?? 'Simulasi Serangan' }}</div>
    <div style="font-size:10px;color:#94a3b8">{{ $sim->skenario ?? $sim->deskripsi ?? '' }}</div>
</div>
@endforeach
</div>
@endif

{{-- DISCLAIMER --}}
<div class="dc">
    <strong>&#9888; Penting:</strong>
    Data reputasi bersumber dari VirusTotal dan URLScan.io. Subdomain dari crt.sh Certificate Transparency.
    DNS reconnaissance menggunakan resolver lokal. Port scan hanya memeriksa port umum dengan timeout 2 detik.
    Analisis AI berbasis knowledge base keamanan publik (NVD, MITRE ATT&amp;CK, OWASP, CWE, CISA KEV).
    <strong>Laporan ini tidak menggantikan penetration testing profesional.</strong>
    Digenerate oleh RedSim pada {{ now()->format('d M Y H:i') }} WIB.
</div>

</body>
</html>
