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
    {{-- Skor Reputasi --}}
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
                <p class="text-sm font-medium">{{ $ssl['protocol'] }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Security Headers --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg> Security Headers</h3>
        @php $headers = $isIntens ? ($data['security_headers']['headers_keamanan'] ?? []) : ($data['security_headers']['headers'] ?? []); @endphp
        <div class="space-y-2">
            @foreach($headers as $name => $info)
            <div class="flex items-center justify-between px-4 py-2.5 rounded-lg {{ ($info['ada'] ?? false) ? 'bg-green-500/5 border border-green-500/20' : 'bg-red-500/5 border border-red-500/20' }}">
                <div class="flex items-center gap-3">
                    @if($info['ada'] ?? false)
                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    @else
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    @endif
                    <span class="text-sm font-medium">{{ $name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    @if($info['ada'] ?? false)
                    <span class="text-xs text-green-400">+{{ $info['poin'] ?? 0 }} poin</span>
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
            @foreach($data['security_headers']['headers_sensitif'] as $name => $value)
            <div class="flex items-center justify-between px-4 py-2 rounded-lg bg-yellow-500/5 border border-yellow-500/20 mb-1">
                <span class="text-sm text-yellow-400">{{ $name }}</span>
                <span class="text-xs text-[#94a3b8]">{{ $value }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Teknologi --}}
    @if(!empty($data['teknologi']))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg> Teknologi Terdeteksi</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($data['teknologi'] as $tech)
            <span class="px-3 py-1.5 rounded-lg bg-[#1e2d4a] text-sm text-[#94a3b8]">{{ $tech }}</span>
            @endforeach
        </div>
    </div>
    @endif

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

    {{-- Common Files (Intens Only) --}}
    @if($isIntens && !empty($data['common_files']))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#7c3aed] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg> Pemeriksaan File Umum</h3>
        <div class="space-y-2">
            @foreach($data['common_files'] as $file)
            <div class="flex items-center justify-between px-4 py-2.5 rounded-lg {{ ($file['terekspos'] ?? false) ? 'bg-red-500/5 border border-red-500/20' : 'bg-[#0a0e1a] border border-[#1e2d4a]' }}">
                <code class="text-sm {{ ($file['terekspos'] ?? false) ? 'text-red-400' : 'text-[#94a3b8]' }}">{{ $file['path'] }}</code>
                <span class="text-xs {{ ($file['terekspos'] ?? false) ? 'text-red-400 font-semibold' : 'text-green-400' }} flex items-center gap-1.5">
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
    @endif

    {{-- Screenshot --}}
    @if(!empty($data['screenshot_url']))
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Screenshot Website</h3>
        <div class="rounded-xl overflow-hidden border border-[#1e2d4a]">
            <img src="{{ $data['screenshot_url'] }}" alt="Screenshot {{ $scan->target }}" class="w-full" loading="lazy" onerror="this.parentElement.innerHTML='<p class=\'p-8 text-center text-sm text-[#64748b]\'>Screenshot tidak tersedia</p>'">
        </div>
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
                <h4 class="text-sm font-semibold mb-3">Simulasi Serangan</h4>
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
                <form method="POST" action="{{ route('analisis.url.ai-analisis', $scan->id) }}">
                    @csrf
                    <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#ec4899] text-white font-semibold hover:shadow-lg hover:shadow-[#7c3aed]/25 transition-all duration-300 flex items-center justify-center gap-2 mx-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> Analisis dengan AI
                    </button>
                </form>
            </div>
        @endif
    </div>
    @endif

    {{-- RAG Source Info Box --}}
    <x-rag-source-info :scan="$scan" />

    {{-- Disclaimer --}}
    <div class="rounded-xl bg-yellow-500/5 border border-yellow-500/20 p-4 flex gap-2">
        <svg class="w-4 h-4 text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <p class="text-xs text-yellow-400/80">Data reputasi bersumber dari VirusTotal dan URLScan.io. @if($hasAI)Analisis AI berbasis knowledge base keamanan publik (NVD, MITRE ATT&CK, OWASP, CWE, CISA KEV). @endif Tidak menggantikan penetration testing profesional.</p>
    </div>

    {{-- PDF Download --}}
    <div class="flex gap-3">
    <div class="flex gap-3">
        <a href="{{ route('laporan.detail', $scan) }}" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1e2d4a] text-xs text-[#94a3b8] hover:bg-[#2a3a5c] transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg> Lihat Laporan Lengkap</a>
        <a href="{{ route('laporan.pdf', $scan) }}" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1e2d4a] text-xs text-[#94a3b8] hover:bg-[#2a3a5c] transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Unduh PDF</a>
    </div>
</div>
@endsection
