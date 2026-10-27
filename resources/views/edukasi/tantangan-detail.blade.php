@extends('layouts.app')
@section('title', $tantangan->judul . ' — RedSim CTF')

@php
    $format = $tantangan->format_soal ?? 'quiz';
    $konten = is_array($tantangan->konten_tambahan) ? $tantangan->konten_tambahan : [];
    $pilihan = is_array($tantangan->pilihan_jawaban) ? $tantangan->pilihan_jawaban : [];
    $sudah = $sudahDijawab !== null;
    $isBenar = $sudahDijawab?->is_benar ?? false;

    $formatMeta = [
        'quiz'       => ['label' => 'Quiz Pilihan Ganda', 'color' => 'text-blue-400',   'bg' => 'bg-blue-500/10',   'border' => 'border-blue-500/30'],
        'kode'       => ['label' => 'Analisis Kode',      'color' => 'text-green-400',  'bg' => 'bg-green-500/10',  'border' => 'border-green-500/30'],
        'skenario'   => ['label' => 'Skenario Insiden',   'color' => 'text-yellow-400', 'bg' => 'bg-yellow-500/10', 'border' => 'border-yellow-500/30'],
        'ciphertext' => ['label' => 'Kriptografi',        'color' => 'text-purple-400', 'bg' => 'bg-purple-500/10', 'border' => 'border-purple-500/30'],
        'file'       => ['label' => 'Forensik File',      'color' => 'text-orange-400', 'bg' => 'bg-orange-500/10', 'border' => 'border-orange-500/30'],
    ];
    $fmt = $formatMeta[$format] ?? $formatMeta['quiz'];
    $sulitColor = ['mudah' => 'text-green-400 bg-green-500/10', 'sedang' => 'text-yellow-400 bg-yellow-500/10', 'sulit' => 'text-red-400 bg-red-500/10'];
    $sc = $sulitColor[$tantangan->tingkat_kesulitan] ?? '';
    $bahasa = $konten['bahasa'] ?? $tantangan->bahasa_kode ?? 'plaintext';
@endphp

@section('header', $tantangan->judul)
@section('subheader', $fmt['label'] . ' · ' . ucfirst($tantangan->tingkat_kesulitan) . ' · ' . $tantangan->poin . ' Poin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="tantanganSolver()">

    {{-- ===== BREADCRUMB ===== --}}
    <div class="flex items-center gap-2 text-sm text-[#475569]">
        <a href="{{ route('edukasi.tantangan') }}" class="hover:text-[#00d4ff] transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Semua Tantangan
        </a>
        <span>/</span>
        <span class="{{ $fmt['color'] }} font-medium">{{ $fmt['label'] }}</span>
    </div>

    {{-- ===== STATUS ALERT ===== --}}
    @if(session('success'))
    <div class="flex items-start gap-3.5 px-5 py-4 rounded-2xl bg-green-500/10 border border-green-500/30 text-green-400">
        <div class="w-8 h-8 rounded-xl bg-green-500/20 border border-green-500/30 flex items-center justify-center shrink-0 mt-0.5 text-green-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <p class="font-semibold text-green-300">Jawaban Benar!</p>
            <p class="text-sm mt-0.5 text-green-300/90">{{ session('success') }}</p>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-start gap-3.5 px-5 py-4 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-400">
        <div class="w-8 h-8 rounded-xl bg-red-500/20 border border-red-500/30 flex items-center justify-center shrink-0 mt-0.5 text-red-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <div>
            <p class="font-semibold text-red-300">Belum Tepat</p>
            <p class="text-sm mt-0.5 text-red-300/90">{{ session('error') }}</p>
            @if($tantangan->penjelasan)
            <p class="text-sm mt-2 text-[#94a3b8]"><span class="font-semibold text-white">Penjelasan:</span> {{ $tantangan->penjelasan }}</p>
            @endif
        </div>
    </div>
    @endif

    {{-- ===== HEADER CARD ===== --}}
    <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl {{ $fmt['bg'] }} shrink-0">{{ $fmt['icon'] }}</div>
            <div class="flex-1">
                <div class="flex flex-wrap gap-2 mb-2">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $fmt['bg'] }} {{ $fmt['color'] }} {{ $fmt['border'] }} border">{{ $fmt['label'] }}</span>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $sc }}">{{ ucfirst($tantangan->tingkat_kesulitan) }}</span>
                    @if($sudah)
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $isBenar ? 'bg-green-500/15 text-green-400 border border-green-500/30' : 'bg-red-500/15 text-red-400 border border-red-500/30' }}">
                        {{ $isBenar ? '✓ Dijawab Benar' : '✗ Sudah Dijawab' }}
                    </span>
                    @endif
                </div>
                <div class="flex items-center gap-4 text-sm text-[#64748b]">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-[#00d4ff]" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <span class="font-bold text-[#00d4ff]">{{ $tantangan->poin }} poin</span>
                    </span>
                    @if($tantangan->kategori)
                    <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>{{ $tantangan->kategori }}</span>
                    @endif
                    @if($tantangan->referensi_cwe)
                    <span class="text-[10px] px-2 py-0.5 rounded bg-[#1e2d4a]">{{ $tantangan->referensi_cwe }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SOAL / DESKRIPSI ===== --}}
    <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-6">
        <h2 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Deskripsi Soal
        </h2>
        <p class="text-sm text-[#94a3b8] leading-relaxed whitespace-pre-wrap">{{ $tantangan->deskripsi }}</p>
    </div>

    {{-- ===== FORMAT: KODE ===== --}}
    @if($format === 'kode' && !empty($konten['kode']))
    <div class="bg-[#0f1629] rounded-2xl border {{ $fmt['border'] }} overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2.5 bg-[#070b14] border-b border-[#1e2d4a]">
            <div class="flex items-center gap-3">
                <div class="flex gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-[#ff5f57]"></span>
                    <span class="w-3 h-3 rounded-full bg-[#ffbd2e]"></span>
                    <span class="w-3 h-3 rounded-full bg-[#28c941]"></span>
                </div>
                <span class="text-xs text-[#475569] font-mono">{{ strtoupper($bahasa) }}</span>
            </div>
            <button onclick="copyToClipboard('kode-soal')" class="flex items-center gap-1.5 text-xs text-[#64748b] hover:text-[#00d4ff] transition-colors px-2 py-1 rounded hover:bg-[#1e2d4a]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span id="copy-btn-kode">Salin Kode</span>
            </button>
        </div>
        <pre id="kode-soal" class="px-5 py-4 text-sm text-[#e2e8f0] font-mono overflow-x-auto leading-relaxed"><code>{{ $konten['kode'] }}</code></pre>
    </div>
    @endif

    {{-- ===== FORMAT: SKENARIO ===== --}}
    @if($format === 'skenario' && !empty($konten['poin_skenario']))
    <div class="bg-[#0f1629] rounded-2xl border border-yellow-500/30 p-6">
        <h3 class="text-sm font-semibold text-yellow-400 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Kronologi Kejadian
        </h3>
        <div class="space-y-3">
            @foreach($konten['poin_skenario'] as $idx => $poin)
            <div class="flex gap-3 items-start">
                <div class="w-7 h-7 rounded-full bg-yellow-500/20 border border-yellow-500/40 flex items-center justify-center shrink-0 mt-0.5">
                    <span class="text-xs font-bold text-yellow-400">{{ $idx + 1 }}</span>
                </div>
                <p class="text-sm text-[#94a3b8] leading-relaxed pt-0.5">{{ $poin }}</p>
            </div>
            @if(!$loop->last)
            <div class="ml-3.5 w-0.5 h-3 bg-yellow-500/20"></div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- ===== FORMAT: CIPHERTEXT ===== --}}
    @if($format === 'ciphertext' && !empty($konten['ciphertext']))
    <div class="bg-[#0f1629] rounded-2xl border border-purple-500/30 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2.5 bg-[#070b14] border-b border-[#1e2d4a]">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span class="text-xs text-[#475569] font-mono tracking-widest">CIPHERTEXT</span>
            </div>
            <button onclick="copyToClipboard('cipher-content')" class="flex items-center gap-1.5 text-xs text-[#64748b] hover:text-purple-400 transition-colors px-2 py-1 rounded hover:bg-[#1e2d4a]">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Salin Ciphertext
            </button>
        </div>
        <div id="cipher-content" class="px-5 py-4 font-mono text-yellow-300 text-sm leading-relaxed break-all">{{ $konten['ciphertext'] }}</div>
        @if(!empty($konten['hint']))
        <div class="px-5 py-3 border-t border-[#1e2d4a] flex items-start gap-2">
            <svg class="w-4 h-4 text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            <p class="text-xs text-[#94a3b8]"><span class="text-yellow-400 font-semibold">Hint:</span> {{ $konten['hint'] }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- ===== FORMAT: FILE ===== --}}
    @if($format === 'file')
    <div class="bg-[#0f1629] rounded-2xl border border-orange-500/30 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2.5 bg-[#070b14] border-b border-[#1e2d4a]">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-xs text-[#475569] font-mono">FILE CONTENT — SIMULATE</span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="copyToClipboard('file-content')" class="flex items-center gap-1.5 text-xs text-[#64748b] hover:text-orange-400 transition-colors px-2 py-1 rounded hover:bg-[#1e2d4a]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Salin
                </button>
                <button onclick="downloadAsFile('file-content', 'challenge_file.txt')" class="flex items-center gap-1.5 text-xs text-orange-400 hover:text-orange-300 transition-colors px-2 py-1 rounded bg-orange-500/10 hover:bg-orange-500/20 border border-orange-500/30">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Unduh File
                </button>
            </div>
        </div>
        <pre id="file-content" class="px-5 py-4 text-green-300 font-mono text-xs leading-relaxed overflow-x-auto max-h-72">{{ $konten['petunjuk'] ?? '' }}</pre>
        @if(!empty($konten['hint']))
        <div class="px-5 py-3 border-t border-[#1e2d4a] flex items-start gap-2">
            <svg class="w-4 h-4 text-orange-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            <p class="text-xs text-[#94a3b8]"><span class="text-orange-400 font-semibold">Hint:</span> {{ $konten['hint'] }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- ===== FORM JAWABAN ===== --}}
    @if(!$sudah)
    <div class="bg-[#0f1629] rounded-2xl border {{ session('error') ? 'border-red-500/40' : 'border-[#1e2d4a]' }} p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-base font-semibold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg> Jawaban Kamu
            </h2>
            @if(session('error'))
            <span class="text-xs text-red-400 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
                Belum tepat, coba lagi!
            </span>
            @endif
        </div>
        <form method="POST" action="{{ route('edukasi.tantangan.jawab', $tantangan) }}" class="space-y-4">
            @csrf

            @if($format === 'quiz' && !empty($pilihan))
            {{-- Quiz: radio pilihan ganda --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($pilihan as $huruf => $teks)
                @php $dipilih = old('jawaban') === $huruf; @endphp
                <label class="group flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition-all
                              {{ $dipilih ? 'border-red-500/60 bg-red-500/5' : 'border-[#1e2d4a] bg-[#0a0e1a] hover:border-[#00d4ff] hover:bg-[#00d4ff]/5' }}
                              has-[:checked]:border-[#00d4ff] has-[:checked]:bg-[#00d4ff]/10">
                    <input type="radio" name="jawaban" value="{{ $huruf }}" required
                           class="mt-1 accent-[#00d4ff] shrink-0" {{ $dipilih ? 'checked' : '' }}>
                    <div>
                        <span class="font-bold text-[#00d4ff] mr-1.5">{{ $huruf }}.</span>
                        <span class="text-sm text-[#e2e8f0]">{{ $teks }}</span>
                    </div>
                </label>
                @endforeach
            </div>

            @elseif($format === 'skenario')
            {{-- Skenario: textarea panjang --}}
            <div>
                <p class="text-xs text-[#64748b] mb-2">Analisis skenario di atas dan tuliskan jawaban/identifikasi ancaman Anda:</p>
                <textarea name="jawaban" rows="5" required placeholder="Jelaskan analisis Anda terhadap skenario di atas..."
                          class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border {{ session('error') ? 'border-red-500/40' : 'border-[#1e2d4a]' }} text-sm text-white placeholder-[#475569] focus:border-[#7c3aed] focus:outline-none resize-y">{{ old('jawaban') }}</textarea>
            </div>

            @elseif($format === 'ciphertext')
            {{-- Ciphertext: input plaintext --}}
            <div>
                <p class="text-xs text-[#64748b] mb-2">Masukkan hasil dekripsi (plaintext) dari ciphertext di atas:</p>
                <div class="relative">
                    <input type="text" name="jawaban" required
                           value="{{ old('jawaban') }}"
                           placeholder="Jawaban / Plaintext / Flag CTF{...}"
                           class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border {{ session('error') ? 'border-red-500/40' : 'border-[#1e2d4a]' }} text-white font-mono placeholder-[#475569] focus:border-[#7c3aed] focus:outline-none pr-24">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-[#475569]">plaintext</span>
                </div>
            </div>

            @elseif($format === 'kode')
            {{-- Kode: input nama kerentanan / flag --}}
            <div>
                <p class="text-xs text-[#64748b] mb-2">Identifikasi kerentanan pada kode di atas, atau masukkan flag jika tersedia:</p>
                <input type="text" name="jawaban" required
                       value="{{ old('jawaban') }}"
                       placeholder="Contoh: SQL Injection, XSS, Buffer Overflow, CTF{...}"
                       class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border {{ session('error') ? 'border-red-500/40' : 'border-[#1e2d4a]' }} text-white placeholder-[#475569] focus:border-[#00d4ff] focus:outline-none">
            </div>

            @elseif($format === 'file')
            {{-- File: input flag --}}
            <div>
                <p class="text-xs text-[#64748b] mb-2">Analisis file di atas dan temukan flag atau jawaban yang tersembunyi:</p>
                <input type="text" name="jawaban" required
                       value="{{ old('jawaban') }}"
                       placeholder="Flag / Jawaban yang ditemukan (CTF{...})"
                       class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border {{ session('error') ? 'border-red-500/40' : 'border-[#1e2d4a]' }} text-white font-mono placeholder-[#475569] focus:border-[#00d4ff] focus:outline-none">
            </div>

            @else
            {{-- Fallback --}}
            <textarea name="jawaban" rows="3" required placeholder="Masukkan jawaban Anda..."
                      class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border {{ session('error') ? 'border-red-500/40' : 'border-[#1e2d4a]' }} text-sm text-white placeholder-[#475569] focus:border-[#00d4ff] focus:outline-none">{{ old('jawaban') }}</textarea>
            @endif

            <div class="flex justify-between items-center pt-2">
                <a href="{{ route('edukasi.tantangan') }}" class="text-sm text-[#475569] hover:text-white transition-colors">← Kembali</a>
                <button type="submit" class="px-7 py-2.5 rounded-xl text-white font-semibold text-sm hover:shadow-lg transition-all flex items-center gap-2"
                        style="background:linear-gradient(135deg,#00d4ff,#7c3aed)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Kirim Jawaban
                </button>
            </div>
        </form>
    </div>



    @else
    {{-- Sudah dijawab: tampilkan hasil --}}
    <div class="bg-[#0f1629] rounded-2xl border {{ $isBenar ? 'border-green-500/30' : 'border-red-500/30' }} p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 {{ $isBenar ? 'bg-green-500/10 border border-green-500/20 text-green-400' : 'bg-red-500/10 border border-red-500/20 text-red-400' }}">
                @if($isBenar)
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @else
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                @endif
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-white mb-1">{{ $isBenar ? 'Tantangan Diselesaikan!' : 'Jawaban Sudah Dicatat' }}</h3>
                <p class="text-sm text-[#94a3b8] mb-3">Jawaban Anda: <code class="px-2 py-0.5 rounded bg-[#0a0e1a] text-[#00d4ff] font-mono">{{ $sudahDijawab->jawaban_user }}</code></p>
                @if(!$isBenar)
                <p class="text-sm text-[#94a3b8]">Jawaban benar: <code class="px-2 py-0.5 rounded bg-[#0a0e1a] text-green-400 font-mono">{{ $tantangan->jawaban_benar }}</code></p>
                @endif
                @if($tantangan->penjelasan)
                <div class="mt-3 p-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a]">
                    <p class="text-xs text-[#64748b] mb-1 font-semibold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Penjelasan:</span>
                    </p>
                    <p class="text-sm text-[#94a3b8]">{{ $tantangan->penjelasan }}</p>
                </div>
                @endif
                <div class="mt-4 flex gap-3">
                    <a href="{{ route('edukasi.tantangan') }}" class="px-4 py-2 rounded-xl bg-[#1e2d4a] text-sm text-white hover:bg-[#2a3a5c] transition-colors">← Tantangan Lain</a>
                    @if($isBenar)
                    <a href="{{ route('edukasi.tantangan') }}?sort=sulit" class="px-4 py-2 rounded-xl text-sm text-white transition-colors" style="background:linear-gradient(135deg,#00d4ff,#7c3aed)">Tantangan Lebih Sulit →</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@php
    $showMemeModal = false;
    $memeType = '';
    $memeUrl = '';
    $memeMsg = '';
    
    if (session('error')) {
        $showMemeModal = true;
        $memeType = 'salah';
        $kataProvokatif = ['YAHAHA SALAH! 🤣', 'WKWKWK NT NT 😂', 'COBA LAGI DEK! 🤡', 'KOK BISA SALAH? 💀', 'BELAJAR LAGI SANA! 🤓', 'YAKIN ANAK IT? 🤔'];
        $memeMsg = $kataProvokatif[array_rand($kataProvokatif)];
    } elseif (session('success')) {
        $showMemeModal = true;
        $memeType = 'benar';
        $memeMsg = 'MANTAP! JAWABAN BENAR! 🎉';
    }

    if ($showMemeModal) {
        $memeDir = public_path('meme/' . $memeType);
        if (is_dir($memeDir)) {
            $files = \Illuminate\Support\Facades\File::files($memeDir);
            if (count($files) > 0) {
                $randomFile = $files[array_rand($files)];
                $memeUrl = asset('meme/' . $memeType . '/' . $randomFile->getFilename());
            }
        }
    }
@endphp

@if($showMemeModal && $memeUrl)
<div x-data="{ open: true }" x-show="open" 
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md" 
     style="display: none;">
    <div @click.outside="open = false" 
         class="relative max-w-2xl w-full bg-[#0a0e1a] border-4 rounded-3xl overflow-hidden flex flex-col items-center p-6 text-center transform transition-all"
         :class="{'border-red-500 animate-heboh shadow-[0_0_150px_rgba(255,0,0,0.6)]': '{{ $memeType }}' === 'salah', 'border-green-500 animate-bounce-2s shadow-[0_0_150px_rgba(0,255,0,0.6)]': '{{ $memeType }}' === 'benar'}">
         
         <button @click="open = false" class="absolute top-4 right-4 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-colors z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
         </button>

         <h2 class="text-4xl md:text-6xl font-black mb-6 uppercase tracking-wider {{ $memeType === 'salah' ? 'text-red-500 drop-shadow-[0_0_15px_rgba(239,68,68,0.8)]' : 'text-green-500 drop-shadow-[0_0_15px_rgba(34,197,94,0.8)]' }}">
             {{ $memeMsg }}
         </h2>
         
         <div class="relative w-full max-h-[50vh] flex justify-center mb-4">
             <img src="{{ $memeUrl }}" alt="Meme {{ $memeType }}" class="max-h-[50vh] object-contain rounded-xl shadow-2xl border-4 {{ $memeType === 'salah' ? 'border-red-500/50' : 'border-green-500/50' }}">
         </div>
         
         @if($memeType === 'salah')
            <p class="text-red-400 font-bold text-lg">Tetap semangat, jangan menyerah! (atau mungkin menyerah saja? 🤫)</p>
         @endif
    </div>
</div>

<style>
@keyframes shake {
  0%, 100% { transform: translateX(0) scale(1); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-15px) rotate(-3deg) scale(1.05); }
  20%, 40%, 60%, 80% { transform: translateX(15px) rotate(3deg) scale(1.05); }
}
@keyframes custom-bounce {
  0%, 100% { transform: translateY(-25%); animation-timing-function: cubic-bezier(0.8,0,1,1); }
  50% { transform: none; animation-timing-function: cubic-bezier(0,0,0.2,1); }
}
.animate-heboh {
  /* Bergetar selama 2 detik (0.2s * 10 iterasi) lalu berhenti */
  animation: shake 0.2s 10;
}
.animate-bounce-2s {
  /* Memantul selama 2 detik (1s * 2 iterasi) lalu berhenti */
  animation: custom-bounce 1s 2;
}
</style>
@endif

<script>
function copyToClipboard(elementId) {
    const el = document.getElementById(elementId);
    const text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text.trim()).then(() => {
        const feedback = document.querySelector(`#copy-btn-${elementId.replace('-content','').replace('-soal','kode')}`);
        const btn = el.closest('.rounded-2xl')?.querySelector('button[onclick*="copyToClipboard"]');
        if (btn) {
            const orig = btn.innerHTML;
            btn.innerHTML = btn.innerHTML.replace(/Salin[^<]*/g, '✓ Disalin!');
            btn.classList.add('text-green-400');
            setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('text-green-400'); }, 2000);
        }
    }).catch(() => {
        // Fallback
        const range = document.createRange();
        range.selectNode(el);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
    });
}

function downloadAsFile(elementId, filename) {
    const el = document.getElementById(elementId);
    const text = el.innerText || el.textContent;
    const blob = new Blob([text.trim()], { type: 'text/plain' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename;
    a.click();
    URL.revokeObjectURL(a.href);
}

document.addEventListener('alpine:init', () => {
    Alpine.data('tantanganSolver', () => ({}));
});
</script>
@endsection
