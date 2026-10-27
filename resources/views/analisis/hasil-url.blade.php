@extends('layouts.app')
@section('title', 'Hasil Scan URL — RedSim')
@section('header', 'Hasil Pemindaian URL')
@section('subheader', ucfirst($scan->mode_scan) . ' — ' . $scan->target)

@section('content')
@php
    $data = $scan->data_mentah ?? [];
    $isIntens = $scan->mode_scan === 'intens';
    $hasAI = $scan->verdict !== null;
@endphp

<div class="space-y-6">
    {{-- Skor Reputasi Overview --}}
    <div class="grid md:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6 text-center">
            @php
                $skor = $data['skor_reputasi'] ?? 0;
                $color = $skor >= 70 ? '#22c55e' : ($skor >= 50 ? '#eab308' : '#ef4444');
            @endphp
            <div class="text-4xl font-black" style="color: {{ $color }}">{{ $skor }}</div>
            <p class="text-xs text-[#64748b] mt-1">Skor Reputasi</p>
        </div>
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6 text-center">
            @php
                $status = $data['status'] ?? 'unknown';
                $statusColor = match($status) { 'aman' => 'text-green-400', 'mencurigakan' => 'text-yellow-400', 'berbahaya' => 'text-red-400', default => 'text-gray-400' };
                $statusLabel = match($status) { 'aman' => 'Aman', 'mencurigakan' => 'Mencurigakan', 'berbahaya' => 'Berbahaya', default => 'Tidak Diketahui' };
            @endphp
            <div class="text-lg font-bold {{ $statusColor }} flex items-center justify-center gap-2">
                @if($status === 'aman') <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                @elseif($status === 'mencurigakan') <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                @elseif($status === 'berbahaya') <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                @else <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                @endif
                {{ $statusLabel }}
            </div>
            <p class="text-xs text-[#64748b] mt-1">Status Keamanan</p>
        </div>
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6 text-center">
            <div class="text-lg font-bold text-[#00d4ff]">{{ $data['virustotal']['malicious_count'] ?? 0 }}/{{ $data['virustotal']['total_engines'] ?? 0 }}</div>
            <p class="text-xs text-[#64748b] mt-1">Vendor VT Flag</p>
        </div>
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6 text-center">
            <div class="text-lg font-bold {{ ($data['security_headers']['skor'] ?? 0) >= 70 ? 'text-green-400' : (($data['security_headers']['skor'] ?? 0) >= 40 ? 'text-yellow-400' : 'text-red-400') }}">{{ $data['security_headers']['skor'] ?? 0 }}/100</div>
            <p class="text-xs text-[#64748b] mt-1">Skor Headers</p>
        </div>
    </div>

    {{-- Skor Breakdown (Intens Only) --}}
    @if($isIntens && !empty($data['skor_breakdown']))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg> Breakdown Skor Keamanan</h3>
        @php $breakdown = $data['skor_breakdown']; @endphp
        <div class="grid md:grid-cols-3 lg:grid-cols-5 gap-3">
            @foreach(['virustotal' => 'VirusTotal', 'headers' => 'Headers', 'ssl' => 'SSL/TLS', 'cookies' => 'Cookies', 'cors' => 'CORS', 'http_methods' => 'HTTP Methods', 'redirects' => 'Redirects'] as $key => $label)
            @if(isset($breakdown[$key]))
            <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-3 text-center">
                @php
                    $bSkor = $breakdown[$key]['skor'] ?? 0;
                    $bColor = $bSkor >= 70 ? 'text-green-400' : ($bSkor >= 40 ? 'text-yellow-400' : 'text-red-400');
                @endphp
                <div class="text-lg font-bold {{ $bColor }}">{{ $bSkor }}</div>
                <p class="text-xs text-[#64748b]">{{ $label }}</p>
                <p class="text-[10px] text-[#475569]">{{ $breakdown[$key]['bobot'] ?? '' }}</p>
            </div>
            @endif
            @endforeach
        </div>
        {{-- Bonus & Penalties --}}
        <div class="flex flex-wrap gap-2 mt-3">
            @if(($breakdown['email_security']['bonus'] ?? 0) > 0)
            <span class="text-xs px-2 py-1 rounded-lg bg-green-500/10 text-green-400 border border-green-500/20">+{{ $breakdown['email_security']['bonus'] }} Email Security</span>
            @endif
            @if(($breakdown['exposed_files']['penalty'] ?? 0) < 0)
            <span class="text-xs px-2 py-1 rounded-lg bg-red-500/10 text-red-400 border border-red-500/20">{{ $breakdown['exposed_files']['penalty'] }} File Sensitif</span>
            @endif
            @if(($breakdown['open_ports']['penalty'] ?? 0) < 0)
            <span class="text-xs px-2 py-1 rounded-lg bg-red-500/10 text-red-400 border border-red-500/20">{{ $breakdown['open_ports']['penalty'] }} Port Terbuka</span>
            @endif
        </div>
    </div>
    @endif

    {{-- SSL Certificate --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg> Status SSL/TLS</h3>
        @php $ssl = $data['ssl'] ?? []; @endphp
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-[#64748b]">Status</p>
                @php
                    $sslStatus = $ssl['status'] ?? 'unknown';
                    $sslColor = match($sslStatus) { 'valid' => 'text-green-400', 'expired' => 'text-red-400', 'self_signed' => 'text-yellow-400', default => 'text-gray-400' };
                    $sslLabel = match($sslStatus) { 'valid' => 'Valid', 'expired' => 'Expired', 'self_signed' => 'Self-Signed', 'tidak_https' => 'Tidak HTTPS', default => 'Error' };
                @endphp
                <p class="text-sm font-medium {{ $sslColor }} flex items-center gap-1.5">
                    @if($sslStatus === 'valid') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    @elseif($sslStatus === 'expired') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    @elseif($sslStatus === 'self_signed') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    @elseif($sslStatus === 'tidak_https') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                    @endif
                    {{ $sslLabel }}
                </p>
            </div>
            @if(!empty($ssl['issuer']))
            <div>
                <p class="text-xs text-[#64748b]">Penerbit</p>
                <p class="text-sm font-medium">{{ $ssl['issuer'] }}</p>
            </div>
            @endif
            @if(!empty($ssl['days_until_expiry']))
            <div>
                <p class="text-xs text-[#64748b]">Kedaluwarsa</p>
                <p class="text-sm font-medium {{ $ssl['days_until_expiry'] < 30 ? 'text-yellow-400' : 'text-green-400' }}">{{ $ssl['days_until_expiry'] }} hari lagi</p>
            </div>
            @endif
            @if($isIntens && !empty($ssl['protocol']))
            <div>
                <p class="text-xs text-[#64748b]">Protokol</p>
                <p class="text-sm font-medium {{ ($ssl['weak_protocol'] ?? false) ? 'text-red-400' : '' }}">{{ $ssl['protocol'] }} @if($ssl['weak_protocol'] ?? false) <span class="text-xs text-red-400 font-semibold">[Lemah]</span> @endif</p>
            </div>
            @endif
            @if($isIntens && !empty($ssl['cipher_name']))
            <div>
                <p class="text-xs text-[#64748b]">Cipher Suite</p>
                <p class="text-sm font-medium {{ ($ssl['weak_cipher'] ?? false) ? 'text-red-400' : '' }}">{{ $ssl['cipher_name'] }} ({{ $ssl['cipher_bits'] ?? '?' }}-bit)</p>
            </div>
            @endif
            @if($isIntens && !empty($ssl['signature_alg']))
            <div>
                <p class="text-xs text-[#64748b]">Signature Algorithm</p>
                <p class="text-sm font-medium {{ ($ssl['weak_signature'] ?? false) ? 'text-red-400' : '' }}">{{ $ssl['signature_alg'] }}</p>
            </div>
            @endif
        </div>
        @if($isIntens && !empty($ssl['masalah']))
        <div class="mt-3 space-y-1">
            @foreach($ssl['masalah'] as $masalah)
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-500/5 border border-red-500/20">
                <svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span class="text-xs text-red-400">{{ $masalah }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Security Headers --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg> Security Headers ({{ $isIntens ? '15' : '6' }} Diperiksa)</h3>
        @php $headers = $isIntens ? ($data['security_headers']['headers_keamanan'] ?? []) : ($data['security_headers']['headers'] ?? []); @endphp
        <div class="space-y-2">
            @foreach($headers as $name => $info)
            <div class="flex items-center justify-between px-4 py-2.5 rounded-lg {{ ($info['ada'] ?? false) ? (($info['status'] ?? '') === 'baik' ? 'bg-green-500/5 border border-green-500/20' : 'bg-yellow-500/5 border border-yellow-500/20') : 'bg-red-500/5 border border-red-500/20' }}">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    @if(($info['ada'] ?? false) && ($info['status'] ?? '') === 'baik')
                        <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    @elseif(($info['ada'] ?? false) && ($info['status'] ?? '') === 'misconfigured')
                        <svg class="w-4 h-4 text-yellow-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    @else
                        <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    @endif
                    <div class="min-w-0">
                        <span class="text-sm font-medium flex items-center gap-2">{{ $name }} @if($info['kritis'] ?? false)<span class="text-[10px] px-1.5 py-0.5 rounded bg-red-500/20 text-red-300">Kritis</span>@endif</span>
                        @if($isIntens && !empty($info['deskripsi']))
                        <p class="text-[11px] text-[#64748b] truncate">{{ $info['deskripsi'] }}</p>
                        @endif
                        @if($isIntens && !empty($info['catatan']))
                        <p class="text-[11px] text-yellow-400">{{ $info['catatan'] }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 ml-3">
                    @if($info['ada'] ?? false)
                    <span class="text-xs {{ ($info['status'] ?? '') === 'baik' ? 'text-green-400' : 'text-yellow-400' }}">+{{ $info['poin'] ?? 0 }}@if($isIntens)/{{ $info['max_poin'] ?? 0 }}@endif</span>
                    @else
                    <span class="text-xs text-red-400">Tidak ada</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @if($isIntens && !empty($data['security_headers']['headers_sensitif']))
        <div class="mt-4 pt-4 border-t border-[#1e2d4a]">
            <h4 class="text-xs font-semibold text-yellow-400 mb-2 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Headers yang Expose Informasi</h4>
            @foreach($data['security_headers']['headers_sensitif'] as $name => $info)
            <div class="flex items-center justify-between px-4 py-2 rounded-lg bg-yellow-500/5 border border-yellow-500/20 mb-1">
                <div>
                    <span class="text-sm text-yellow-400">{{ $name }}</span>
                    @if(is_array($info))
                    <p class="text-[11px] text-[#64748b]">{{ $info['deskripsi'] ?? '' }}</p>
                    @endif
                </div>
                <span class="text-xs text-[#94a3b8]">{{ is_array($info) ? ($info['nilai'] ?? '') : $info }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- DNS Reconnaissance (Intens Only) --}}
    @if($isIntens && !empty($data['dns_recon']) && empty($data['dns_recon']['error']))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#7c3aed] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg> DNS Reconnaissance</h3>
        @php $dns = $data['dns_recon']; @endphp
        <div class="grid md:grid-cols-2 gap-4">
            {{-- IP Addresses --}}
            @if(!empty($dns['ip_addresses']))
            <div>
                <p class="text-xs text-[#64748b] mb-1">IP Address</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($dns['ip_addresses'] as $ip)
                    <span class="px-2 py-1 rounded bg-[#1e2d4a] text-xs text-[#94a3b8] font-mono">{{ $ip }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Nameservers --}}
            @if(!empty($dns['nameservers']))
            <div>
                <p class="text-xs text-[#64748b] mb-1">Nameservers</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($dns['nameservers'] as $ns)
                    <span class="px-2 py-1 rounded bg-[#1e2d4a] text-xs text-[#94a3b8]">{{ $ns }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- MX Records --}}
            @if(!empty($dns['mx_records']))
            <div>
                <p class="text-xs text-[#64748b] mb-1">MX Records</p>
                @foreach($dns['mx_records'] as $mx)
                <div class="text-xs text-[#94a3b8]"><span class="text-[#64748b]">[{{ $mx['priority'] ?? '?' }}]</span> {{ $mx['host'] ?? '' }}</div>
                @endforeach
            </div>
            @endif

            {{-- CDN/WAF Detection --}}
            @if(!empty($dns['cdn_waf']))
            <div>
                <p class="text-xs text-[#64748b] mb-1">CDN/WAF Terdeteksi</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($dns['cdn_waf'] as $cdn)
                    <span class="px-2 py-1 rounded bg-blue-500/10 text-xs text-blue-400 border border-blue-500/20">{{ $cdn }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Email Security --}}
        @if(!empty($dns['email_security']))
        <div class="mt-4 pt-4 border-t border-[#1e2d4a]">
            <h4 class="text-xs font-semibold text-[#64748b] mb-2">Email Security</h4>
            <div class="flex flex-wrap gap-2">
                @foreach(['spf' => 'SPF', 'dkim' => 'DKIM', 'dmarc' => 'DMARC'] as $key => $label)
                <span class="px-3 py-1.5 rounded-lg text-xs font-medium {{ ($dns['email_security'][$key] ?? false) ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                    @if($dns['email_security'][$key] ?? false) ✓ @else ✗ @endif {{ $label }}
                </span>
                @endforeach
                <span class="px-3 py-1.5 rounded-lg text-xs font-medium {{ ($dns['dnssec'] ?? false) ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' }}">
                    @if($dns['dnssec'] ?? false) ✓ @else ✗ @endif DNSSEC
                </span>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Port Scan (Intens Only) --}}
    @if($isIntens && !empty($data['port_scan']['ports']))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#7c3aed] mb-1 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg> Port Scan</h3>
        <p class="text-xs text-[#64748b] mb-4">{{ $data['port_scan']['open_count'] ?? 0 }} port terbuka dari {{ count($data['port_scan']['ports']) }} yang diperiksa</p>

        @php $openPorts = array_filter($data['port_scan']['ports'], fn($p) => $p['status'] === 'open'); @endphp

        @if(count($openPorts) > 0)
        <div class="space-y-2">
            @foreach($openPorts as $port)
            @php
                $portRisikoColor = match($port['risiko'] ?? 'rendah') {
                    'kritis' => 'border-red-500/30 bg-red-500/5',
                    'tinggi' => 'border-orange-500/30 bg-orange-500/5',
                    'sedang' => 'border-yellow-500/30 bg-yellow-500/5',
                    default => 'border-[#1e2d4a] bg-[#0a0e1a]',
                };
                $portBadgeColor = match($port['risiko'] ?? 'rendah') {
                    'kritis' => 'bg-red-500/20 text-red-400',
                    'tinggi' => 'bg-orange-500/20 text-orange-400',
                    'sedang' => 'bg-yellow-500/20 text-yellow-400',
                    default => 'bg-green-500/20 text-green-400',
                };
            @endphp
            <div class="flex items-center justify-between px-4 py-2.5 rounded-lg {{ $portRisikoColor }} border">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-mono font-bold text-[#00d4ff]">{{ $port['port'] }}</span>
                    <span class="text-sm font-medium">{{ $port['service'] }}</span>
                    <span class="text-[11px] text-[#64748b]">{{ $port['deskripsi'] }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] px-2 py-0.5 rounded {{ $portBadgeColor }} uppercase font-semibold">{{ $port['risiko'] }}</span>
                    <span class="text-xs text-green-400 font-medium">OPEN</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-4 py-3 rounded-lg bg-green-500/5 border border-green-500/20 text-center">
            <span class="text-xs text-green-400">✓ Tidak ada port berisiko yang terbuka</span>
        </div>
        @endif

        @if(!empty($data['port_scan']['critical_open']))
        <div class="mt-3 px-4 py-2 rounded-lg bg-red-500/10 border border-red-500/30 flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p class="text-xs text-red-400 font-semibold">Port kritis terbuka: {{ implode(', ', $data['port_scan']['critical_open']) }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Cookie Security (Intens Only) --}}
    @if($isIntens && !empty($data['cookie_security']['cookies']))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#7c3aed] mb-1 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Cookie Security</h3>
        <p class="text-xs text-[#64748b] mb-4">{{ $data['cookie_security']['total'] ?? 0 }} cookies ditemukan — Skor: <span class="{{ ($data['cookie_security']['skor'] ?? 100) >= 70 ? 'text-green-400' : (($data['cookie_security']['skor'] ?? 100) >= 40 ? 'text-yellow-400' : 'text-red-400') }}">{{ $data['cookie_security']['skor'] ?? 100 }}/100</span></p>

        <div class="space-y-2">
            @foreach($data['cookie_security']['cookies'] as $cookie)
            <div class="px-4 py-3 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium font-mono {{ ($cookie['is_session'] ?? false) ? 'text-yellow-400' : 'text-[#94a3b8]' }}">{{ $cookie['name'] }} @if($cookie['is_session'] ?? false)<span class="text-[10px] px-1.5 py-0.5 rounded bg-yellow-500/20 text-yellow-300 ml-1">Session</span>@endif</span>
                    <span class="text-xs {{ ($cookie['skor'] ?? 0) >= 70 ? 'text-green-400' : (($cookie['skor'] ?? 0) >= 40 ? 'text-yellow-400' : 'text-red-400') }}">{{ $cookie['skor'] ?? 0 }}/100</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="text-[10px] px-2 py-0.5 rounded {{ ($cookie['secure'] ?? false) ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">{{ ($cookie['secure'] ?? false) ? '✓' : '✗' }} Secure</span>
                    <span class="text-[10px] px-2 py-0.5 rounded {{ ($cookie['httponly'] ?? false) ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">{{ ($cookie['httponly'] ?? false) ? '✓' : '✗' }} HttpOnly</span>
                    <span class="text-[10px] px-2 py-0.5 rounded {{ ($cookie['samesite'] ?? null) && $cookie['samesite'] !== 'None' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' }}">SameSite: {{ $cookie['samesite'] ?? 'Tidak diset' }}</span>
                </div>
                @if(!empty($cookie['masalah']))
                <div class="mt-2 space-y-1">
                    @foreach($cookie['masalah'] as $m)
                    <p class="text-[11px] text-red-400">• {{ $m }}</p>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- CORS Configuration (Intens Only) --}}
    @if($isIntens && !empty($data['cors_config']))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#7c3aed] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg> CORS Configuration</h3>

        @if($data['cors_config']['vulnerable'] ?? false)
        <div class="px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/30 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p class="text-sm text-red-400 font-semibold">CORS Vulnerable — Server menerima origin berbahaya</p>
        </div>
        @else
        <div class="px-4 py-3 rounded-lg bg-green-500/5 border border-green-500/20 mb-4">
            <p class="text-sm text-green-400">✓ CORS dikonfigurasi dengan aman</p>
        </div>
        @endif

        @if(!empty($data['cors_config']['masalah']))
        <div class="space-y-1 mb-3">
            @foreach($data['cors_config']['masalah'] as $m)
            <div class="flex items-start gap-2 px-3 py-1.5 rounded bg-red-500/5 border border-red-500/20">
                <svg class="w-3.5 h-3.5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span class="text-xs text-red-400">{{ $m }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <div class="space-y-2">
            @foreach($data['cors_config']['tests'] ?? [] as $test)
            <div class="flex items-center justify-between px-4 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a]">
                <div>
                    <span class="text-xs text-[#64748b]">Origin:</span> <code class="text-xs text-[#94a3b8]">{{ $test['test_origin'] }}</code>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium {{ ($test['origin_reflected'] ?? false) ? 'text-red-400' : 'text-green-400' }}">{{ ($test['origin_reflected'] ?? false) ? 'Reflected [Risk]' : 'Blocked ✓' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- HTTP Methods (Intens Only) --}}
    @if($isIntens && !empty($data['http_methods']['methods']))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#7c3aed] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg> HTTP Method Enumeration</h3>

        @if(!empty($data['http_methods']['masalah']))
        <div class="mb-3 space-y-1">
            @foreach($data['http_methods']['masalah'] as $m)
            <div class="px-3 py-1.5 rounded bg-red-500/5 border border-red-500/20 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="text-xs text-red-400">{{ $m }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <div class="flex flex-wrap gap-2">
            @foreach($data['http_methods']['methods'] as $method)
            @php
                $methodColor = 'bg-[#1e2d4a] text-[#64748b] border-[#1e2d4a]';
                if ($method['allowed'] ?? false) {
                    $methodColor = ($method['berbahaya'] ?? false)
                        ? 'bg-red-500/10 text-red-400 border-red-500/30'
                        : 'bg-green-500/10 text-green-400 border-green-500/20';
                }
            @endphp
            <div class="px-3 py-2 rounded-lg border {{ $methodColor }} text-center">
                <p class="text-sm font-mono font-bold">{{ $method['method'] }}</p>
                <p class="text-[10px]">{{ ($method['allowed'] ?? false) ? ($method['berbahaya'] ?? false ? 'BERBAHAYA' : 'Allowed') : 'Blocked' }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Redirect Chain (Intens Only) --}}
    @if($isIntens && !empty($data['redirect_chain']['chain']) && count($data['redirect_chain']['chain']) > 1)
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#7c3aed] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg> Redirect Chain ({{ $data['redirect_chain']['total_redirects'] ?? 0 }} redirect)</h3>

        @if(!empty($data['redirect_chain']['masalah']))
        <div class="mb-3 space-y-1">
            @foreach($data['redirect_chain']['masalah'] as $m)
            <div class="px-3 py-1.5 rounded bg-red-500/5 border border-red-500/20 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="text-xs text-red-400">{{ $m }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <div class="space-y-1">
            @foreach($data['redirect_chain']['chain'] as $i => $step)
            <div class="flex items-center gap-2">
                <span class="text-[10px] px-1.5 py-0.5 rounded {{ in_array($step['status'], [301, 302, 303, 307, 308]) ? 'bg-yellow-500/20 text-yellow-400' : ($step['status'] === 200 ? 'bg-green-500/20 text-green-400' : 'bg-[#1e2d4a] text-[#64748b]') }}">{{ $step['status'] }}</span>
                <code class="text-xs text-[#94a3b8] truncate">{{ $step['url'] }}</code>
            </div>
            @if(!$loop->last)
            <div class="flex items-center gap-2 pl-3">
                <svg class="w-3 h-3 text-[#475569]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            </div>
            @endif
            @endforeach
        </div>

        <div class="flex gap-2 mt-3">
            @if($data['redirect_chain']['uses_http_upgrade'] ?? false)
            <span class="text-[10px] px-2 py-1 rounded bg-green-500/10 text-green-400 border border-green-500/20">✓ HTTP→HTTPS Upgrade</span>
            @endif
            @if($data['redirect_chain']['https_to_http'] ?? false)
            <span class="text-[10px] px-2 py-1 rounded bg-red-500/10 text-red-400 border border-red-500/20 inline-flex items-center gap-1">
                <svg class="w-3 h-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>HTTPS→HTTP Downgrade</span>
            </span>
            @endif
        </div>
    </div>
    @endif

    {{-- Tech Stack + Screenshot (Fase C.1 + C.2) --}}
    @php
        $techStackData = $scan->tech_stack;
        if (empty($techStackData) && !empty($data['tech_stack_structured'])) {
            $techStackData = $data['tech_stack_structured'];
        }
        $screenshotEvidence = $data['screenshot_url'] ?? null;
    @endphp
    @include('components.tech-stack-badges', [
        'techStack'  => $techStackData,
        'screenshot' => $screenshotEvidence,
    ])


    {{-- Server Info (Intens Only) --}}
    @if($isIntens && !empty($data['server_info']))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#7c3aed] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> Informasi Server</h3>
        <div class="grid md:grid-cols-2 gap-4">
            @foreach(['ip' => 'IP Address', 'asn' => 'ASN', 'asnname' => 'Nama ASN', 'country' => 'Negara', 'server' => 'Server Software'] as $key => $label)
            @if(!empty($data['server_info'][$key]))
            <div>
                <p class="text-xs text-[#64748b]">{{ $label }}</p>
                <p class="text-sm font-medium">{{ $data['server_info'][$key] }}</p>
            </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Subdomain Discovery & Takeover (Fase B.1 & B.3) --}}
    @if($isIntens && !empty($data['subdomains']['subdomains']))
    @include('components.subdomain-discovery', ['subdomainData' => $data['subdomains'] ?? [], 'scan' => $scan])
    @endif

    {{-- Common Files (Intens Only) --}}
    @if($isIntens && !empty($data['common_files']))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#7c3aed] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg> Pemeriksaan File & Path Sensitif ({{ count($data['common_files']) }})</h3>

        @php
            $exposed = array_filter($data['common_files'], fn($f) => $f['terekspos'] ?? false);
            $kategoris = [];
            foreach ($data['common_files'] as $f) {
                $kat = $f['kategori'] ?? 'lainnya';
                $kategoris[$kat][] = $f;
            }
            $kategoriLabels = [
                'source_control' => 'Source Control',
                'config_leak' => 'Config & Environment',
                'backup' => 'Backup & Database',
                'debug' => 'Debug & Info Disclosure',
                'admin_panel' => 'Admin Panels',
                'api_docs' => 'API Documentation',
                'web_standard' => 'Web Standard Files',
            ];
        @endphp

        @if(count($exposed) > 0)
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/30 flex items-center gap-2">
            <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p class="text-sm text-red-400 font-semibold">{{ count($exposed) }} file/path sensitif terekspos!</p>
        </div>
        @else
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-500/5 border border-green-500/20">
            <p class="text-sm text-green-400">✓ Tidak ada file sensitif yang terekspos</p>
        </div>
        @endif

        @foreach($kategoris as $kat => $files)
        <div class="mb-3">
            <h4 class="text-xs font-semibold text-[#64748b] mb-1.5">{{ $kategoriLabels[$kat] ?? ucfirst($kat) }}</h4>
            <div class="space-y-1">
                @foreach($files as $file)
                <div class="flex items-center justify-between px-4 py-2 rounded-lg {{ ($file['terekspos'] ?? false) ? 'bg-red-500/5 border border-red-500/20' : 'bg-[#0a0e1a] border border-[#1e2d4a]' }}">
                    <div class="flex items-center gap-2 min-w-0">
                        <code class="text-xs {{ ($file['terekspos'] ?? false) ? 'text-red-400' : 'text-[#94a3b8]' }}">{{ $file['path'] }}</code>
                        @if($file['terekspos'] ?? false)
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-{{ $file['risiko'] === 'kritis' ? 'red' : ($file['risiko'] === 'tinggi' ? 'orange' : 'yellow') }}-500/20 text-{{ $file['risiko'] === 'kritis' ? 'red' : ($file['risiko'] === 'tinggi' ? 'orange' : 'yellow') }}-400 uppercase font-semibold shrink-0">{{ $file['risiko'] }}</span>
                        @endif
                    </div>
                    <span class="text-xs shrink-0 ml-2 {{ ($file['terekspos'] ?? false) ? 'text-red-400 font-semibold' : 'text-green-400' }} flex items-center gap-1">
                        @if($file['terekspos'] ?? false)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> TEREKSPOS
                        @else
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Aman
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif



    {{-- AI Analysis Button / Results --}}
    @if($isIntens || $scan->mode_scan === 'biasa')
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        @if($hasAI)
            {{-- AI Results --}}
            <h3 class="text-sm font-semibold text-[#7c3aed] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> Analisis AI</h3>
            @if($scan->ringkasan_eksekutif)
            <div class="mb-4 p-4 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a]">
                <h4 class="text-xs font-semibold text-[#00d4ff] mb-2">Ringkasan Eksekutif</h4>
                <p class="text-sm text-[#94a3b8] whitespace-pre-line">{{ $scan->ringkasan_eksekutif }}</p>
            </div>
            @endif
            @if($scan->ringkasan_teknis)
            <div class="p-4 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a]">
                <h4 class="text-xs font-semibold text-[#7c3aed] mb-2">Ringkasan Teknis</h4>
                <p class="text-sm text-[#94a3b8] whitespace-pre-line">{{ $scan->ringkasan_teknis }}</p>
            </div>
            @endif

            {{-- Temuan AI --}}
            @if($scan->temuan->count())
            <div class="mt-4">
                <h4 class="text-sm font-semibold mb-3">Temuan ({{ $scan->temuan->count() }})</h4>
                <div class="space-y-3">
                    @foreach($scan->temuan->sortByDesc(fn($t) => ['kritis'=>5,'tinggi'=>4,'sedang'=>3,'rendah'=>2,'info'=>1][$t->tingkat_keparahan] ?? 0) as $temuan)
                        <x-finding-card :temuan="$temuan" />
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Simulasi Serangan --}}
            @if($scan->simulasiSerangan->count())
            <div class="mt-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold">Simulasi Serangan (Red Team)</h4>
                    <a href="{{ route('simulasi.index', $scan->id) }}" class="px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white text-xs font-medium rounded-lg transition-colors flex items-center gap-1.5 shadow-[0_0_10px_rgba(220,38,38,0.3)]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Jalankan Simulasi
                    </a>
                </div>
                @foreach($scan->simulasiSerangan as $sim)
                <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 mb-2">
                    <h5 class="font-semibold text-red-400 text-sm">{{ $sim->nama_skenario }}</h5>
                    @if($sim->narasi_teknis)
                    <p class="text-xs text-[#94a3b8] mt-2">{{ $sim->narasi_teknis }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        @else
            {{-- Button to trigger AI --}}
            <div class="text-center">
                <h3 class="text-sm font-semibold mb-2 flex items-center justify-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> Ingin Analisis Lebih Dalam?</h3>
                <p class="text-xs text-[#64748b] mb-4">Gunakan AI + Knowledge Base keamanan untuk analisis mendalam.</p>
                
                @if(Auth::user()->aiConfig)
                <form method="POST" action="{{ route('analisis.url.ai-analisis', $scan->id) }}">
                    @csrf
                    <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#ec4899] text-white font-semibold hover:shadow-lg hover:shadow-[#7c3aed]/25 transition-all duration-300 flex items-center justify-center gap-2 mx-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> Analisis dengan AI
                    </button>
                </form>
                @else
                <div class="inline-flex flex-col items-center p-4 rounded-xl border border-yellow-500/20 bg-yellow-500/5">
                    <p class="text-xs text-yellow-400 mb-3"><svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Anda perlu mengonfigurasi model AI terlebih dahulu untuk fitur ini.</p>
                    <a href="{{ route('profile.ai') }}" class="px-6 py-2 rounded-lg bg-white text-gray-900 font-semibold text-sm hover:bg-gray-100 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Hubungkan Google untuk AI
                    </a>
                </div>
                @endif
            </div>
        @endif
    </div>
    @endif

    {{-- RAG Source Info Box --}}
    <x-rag-source-info :scan="$scan" />

    {{-- Disclaimer --}}
    <div class="rounded-xl bg-yellow-500/5 border border-yellow-500/20 p-4 flex gap-2">
        <svg class="w-4 h-4 text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <p class="text-xs text-yellow-400/80">Data reputasi bersumber dari VirusTotal dan URLScan.io. @if($isIntens)Subdomain dari crt.sh Certificate Transparency. DNS reconnaissance menggunakan resolver lokal. Port scan hanya memeriksa port umum dengan timeout 2 detik. @endif @if($hasAI)Analisis AI berbasis knowledge base keamanan publik (NVD, MITRE ATT&CK, OWASP, CWE, CISA KEV). @endif Tidak menggantikan penetration testing profesional.</p>
    </div>

    {{-- Action Buttons --}}
    <div class="glitch-container mt-4 mb-2">
        <button @click="$dispatch('open-ai-chat')" class="glitch-radio-wrapper active bg-transparent border-0 p-0 text-left cursor-pointer" style="outline: none;">
            <div class="glitch-btn">
                Tanya AI<span aria-hidden="">_</span>
                <span class="glitch-btn__glitch" aria-hidden="">_Tanya_AI_</span>
                <label class="glitch-number">ai</label>
            </div>
        </button>
        <a href="{{ route('laporan.pdf', $scan) }}" class="glitch-radio-wrapper">
            <div class="glitch-btn">
                Unduh PDF<span aria-hidden=""></span>
                <span class="glitch-btn__glitch" aria-hidden="">_PDF_DOC</span>
                <label class="glitch-number">r2</label>
            </div>
        </a>
        <a href="{{ route('laporan.bandingkan', $scan) }}" class="glitch-radio-wrapper">
            <div class="glitch-btn">
                Bandingkan<span aria-hidden=""></span>
                <span class="glitch-btn__glitch" aria-hidden="">_CMP_DIFF</span>
                <label class="glitch-number">r3</label>
            </div>
        </a>
    </div>
</div>

<style>
.glitch-container {
  display: flex;
  flex-direction: row;
  gap: 15px;
}

.glitch-radio-wrapper {
  position: relative;
  height: 38px;
  min-width: 140px;
  margin: 3px;
  display: block;
  text-decoration: none;
}

.glitch-btn {
  --primary: #ff184c;
  --shadow-primary: #fded00;
  --color: white;
  --font-size: 9px;
  --shadow-primary-hue: 180;
  --shadow-secondary-hue: 60;
  --shadow-secondary: hsl(var(--shadow-secondary-hue), 90%, 60%);
  --clip: polygon(11% 0, 95% 0, 100% 25%, 90% 90%, 95% 90%, 85% 90%, 85% 100%, 7% 100%, 0 80%);
  --border: 5px;
  --shimmy-distance: 5;
  --clip-one: polygon(0 2%, 100% 2%, 100% 95%, 95% 95%, 95% 90%, 85% 90%, 85% 95%, 8% 95%, 0 70%);
  --clip-two: polygon(0 78%, 100% 78%, 100% 100%, 95% 100%, 95% 90%, 85% 90%, 85% 100%, 8% 100%, 0 78%);
  --clip-three: polygon(0 44%, 100% 44%, 100% 54%, 95% 54%, 95% 54%, 85% 54%, 85% 54%, 8% 54%, 0 54%);
  --clip-four: polygon(0 0, 100% 0, 100% 0, 95% 0, 95% 0, 85% 0, 85% 0, 8% 0, 0 0);
  --clip-five: polygon(0 0, 100% 0, 100% 0, 95% 0, 95% 0, 85% 0, 85% 0, 8% 0, 0 0);
  --clip-six: polygon(0 40%, 100% 40%, 100% 85%, 95% 85%, 95% 85%, 85% 85%, 85% 85%, 8% 85%, 0 70%);
  --clip-seven: polygon(0 63%, 100% 63%, 100% 80%, 95% 80%, 95% 80%, 85% 80%, 85% 80%, 8% 80%, 0 70%);
  color: var(--color);
  text-transform: uppercase;
  font-size: var(--font-size);
  letter-spacing: 3px;
  position: relative;
  font-weight: 900;
  width: 100%;
  height: 100%;
  line-height: 38px;
  text-align: center;
  transition: background 0.2s, 0.3s;
}

.glitch-radio-wrapper.active .glitch-btn {
  --primary: #8B00FF;
  --shadow-primary: #00e572;
}

.glitch-radio-wrapper:hover .glitch-btn {
  --primary: #cc133c;
  --font-size: 11px;
}

.glitch-btn:after, .glitch-btn:before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  clip-path: var(--clip);
  z-index: -1;
}

.glitch-btn:before {
  background: var(--shadow-primary);
  transform: translate(var(--border), 0);
}

.glitch-btn:after {
  background: var(--primary);
}

.glitch-btn__glitch {
  position: absolute;
  top: calc(var(--border) * -1);
  left: calc(var(--border) * -1);
  right: calc(var(--border) * -1);
  bottom: calc(var(--border) * -1);
  background: var(--shadow-primary);
  text-shadow: 2px 2px var(--shadow-primary), -2px -2px var(--shadow-secondary);
  clip-path: var(--clip);
  animation: glitch-anim 2s infinite;
  display: none;
}

.glitch-radio-wrapper:hover .glitch-btn__glitch {
  display: block;
}

.glitch-radio-wrapper.active .glitch-btn__glitch {
  display: block;
  animation: glitch-anim 5s infinite;
}

.glitch-btn__glitch:before {
  content: '';
  position: absolute;
  top: calc(var(--border) * 1);
  right: calc(var(--border) * 1);
  bottom: calc(var(--border) * 1);
  left: calc(var(--border) * 1);
  clip-path: var(--clip);
  background: var(--primary);
  z-index: -1;
}

@keyframes glitch-anim {
  0% { clip-path: var(--clip-one); }
  2%, 8% { clip-path: var(--clip-two); transform: translate(calc(var(--shimmy-distance) * -1%), 0); }
  6% { clip-path: var(--clip-two); transform: translate(calc(var(--shimmy-distance) * 1%), 0); }
  9% { clip-path: var(--clip-two); transform: translate(0, 0); }
  10% { clip-path: var(--clip-three); transform: translate(calc(var(--shimmy-distance) * 1%), 0); }
  13% { clip-path: var(--clip-three); transform: translate(0, 0); }
  14%, 21% { clip-path: var(--clip-four); transform: translate(calc(var(--shimmy-distance) * 1%), 0); }
  25% { clip-path: var(--clip-five); transform: translate(calc(var(--shimmy-distance) * 1%), 0); }
  30% { clip-path: var(--clip-five); transform: translate(calc(var(--shimmy-distance) * -1%), 0); }
  35%, 45% { clip-path: var(--clip-six); transform: translate(calc(var(--shimmy-distance) * -1%)); }
  40% { clip-path: var(--clip-six); transform: translate(calc(var(--shimmy-distance) * 1%)); }
  50% { clip-path: var(--clip-six); transform: translate(0, 0); }
  55% { clip-path: var(--clip-seven); transform: translate(calc(var(--shimmy-distance) * 1%), 0); }
  60% { clip-path: var(--clip-seven); transform: translate(0, 0); }
  31%, 61%, 100% { clip-path: var(--clip-four); }
}

.glitch-number {
  background: var(--shadow-primary);
  color: #323232;
  font-size: 5.5px;
  font-weight: 700;
  letter-spacing: 1px;
  position: absolute;
  width: 15px;
  height: 6px;
  top: 0;
  right: 5%;
  line-height: 6.2px;
}
</style>
@endsection
