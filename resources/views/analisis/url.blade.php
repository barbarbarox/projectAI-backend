@extends('layouts.app')
@section('title', 'Pemindaian URL — RedSim')
@section('header', 'Pemindaian URL')
@section('subheader', 'Konfigurasikan target dan analisis keamanan mendalam berbasis AI.')

@section('content')
@php
    $scanToday = Auth::user()->scan_count_today ?? 0;
    $maxScan = (int) env('MAX_SCANS_PER_DAY_FREE', 10);
    $scanPercent = min(100, max(0, round(($scanToday / max(1, $maxScan)) * 100)));
@endphp

<div class="pemindaian-URL"
     x-data="urlScannerForm({
         initialUrl: '{{ old('url', request('target') ?? request('url')) }}',
         initialModeScan: '{{ old('mode_scan', 'biasa') }}',
         initialModeAi: '{{ old('mode_ai', 'dengan_ai') }}',
         initialModeRag: '{{ old('mode_rag', 'dengan_rag') }}',
         initialModelAi: '{{ old('model_ai', '') }}',
         initialDisclaimer: {{ old('disclaimer_check') ? 'true' : 'false' }}
     })"
     x-init="init()">

    {{-- ════════════════════════════════════════════════════════════════════════
         KEPALA HALAMAN (Page Header Banner)
         ════════════════════════════════════════════════════════════════════════ --}}
    <div class="kepala-halaman">
        <div class="pengantar-halaman">
            <div class="konteks-produk">REDSIM / ANALISIS KEAMANAN</div>
            <h1 class="judul-halaman">Pemindaian URL</h1>
            <p class="deskripsi-halaman">Konfigurasikan target dan analisis keamanan mendalam berbasis AI.</p>
        </div>

        <div class="status-layanan-dan">
            {{-- AI Aktif Pill --}}
            <div class="status-AI" :class="aiStatus === 'available' ? 'border-[#244a41] bg-[#102b2a]' : (aiStatus === 'checking' ? 'border-[#26364b] bg-[#101b2b]' : 'border-red-900/40 bg-red-950/20')">
                <div class="div" :class="aiStatus === 'available' ? 'bg-[#62d8ab] shadow-[0_0_8px_#62d8ab]' : (aiStatus === 'checking' ? 'bg-[#55d9ed] animate-pulse' : 'bg-red-400')"></div>
                <span class="status-layanan" :class="aiStatus === 'available' ? 'text-[#62d8ab]' : (aiStatus === 'checking' ? 'text-[#55d9ed]' : 'text-red-400')" x-text="aiStatus === 'available' ? 'AI Aktif' : (aiStatus === 'checking' ? 'Memeriksa...' : 'AI Terbatas')">AI Aktif</span>
            </div>

            {{-- Daily Usage Counter --}}
            <div class="penggunaan-harian">
                <div class="div-2">
                    <span class="label-penggunaan">Scan hari ini</span>
                    <p class="nilai-penggunaan">
                        <span class="text-wrapper">{{ $scanToday }}</span>
                        <span class="span"> / {{ $maxScan }}</span>
                    </p>
                </div>
                <div class="kuota-penggunaan">
                    <div class="kuota-terpakai" style="width: {{ $scanPercent }}%;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Error Banner --}}
    @if($errors->any())
    <div class="w-full p-4 rounded-xl border border-red-500/30 bg-red-500/10 text-red-300 text-sm flex items-start gap-3">
        <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div class="flex-1">
            <p class="font-semibold text-red-200 mb-1">Perhatian saat konfigurasi:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-red-300/90">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Main Form Container --}}
    <form method="POST" action="{{ route('analisis.url') }}" class="w-full flex flex-col gap-6" id="formPemindaianUrl">
        @csrf

        {{-- ════════════════════════════════════════════════════════════════════════
             2-COLUMN SECTION: FORMULIR & RINGKASAN
             ════════════════════════════════════════════════════════════════════════ --}}
        <div class="konfigurasi">

            {{-- ── LEFT COLUMN: FORMULIR KONFIGURASI ── --}}
            <div class="formulir-konfigurasi">

                {{-- ──────── STEP 01: Target & Mode Pemindaian ──────── --}}
                <div class="target-dan-mode">
                    <div class="kepala-konfigurasi">
                        <div class="langkah-konfigurasi">
                            <span class="nomor-langkah">01</span>
                        </div>
                        <div class="keterangan-bagian">
                            <h2 class="text-wrapper-2">Target &amp; mode pemindaian</h2>
                            <p class="petunjuk-bagian">Tentukan URL dan kedalaman pemeriksaan yang Anda butuhkan.</p>
                        </div>
                    </div>

                    {{-- Target URL Input --}}
                    <div class="div-3">
                        <div class="div-2">
                            <label for="url" class="label-kolom">
                                <span class="text-wrapper">URL Target </span>
                                <span class="text-wrapper-3">*</span>
                            </label>
                            <button type="button"
                                    @click="$dispatch('open-help', { feature: 'pemindai-url' })"
                                    class="circle-help hover:opacity-80 transition-opacity"
                                    title="Panduan Target URL">
                                <svg class="vector w-3.5 h-3.5 text-[#55d9ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>
                        </div>

                        <div class="masukan-URL-kosong">
                            <div class="vector-wrapper">
                                <svg class="img w-4 h-4 text-[#55d9ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9"/>
                                </svg>
                            </div>
                            <input type="url"
                                   id="url"
                                   name="url"
                                   x-model="targetUrl"
                                   required
                                   autocomplete="url"
                                   placeholder="https://contoh.com"
                                   class="bg-transparent border-none outline-none text-[#edf4fc] font-mono text-sm w-full placeholder-[#73869f]">
                        </div>
                        <p class="petunjuk-URL">
                            <span class="text-wrapper-4">Masukkan URL lengkap termasuk </span>
                            <span class="text-wrapper-5">https://</span>
                        </p>
                    </div>

                    {{-- Mode Selection Cards --}}
                    <div class="pemilihan-mode">
                        <label class="text-wrapper-6">Mode Pemindaian</label>
                        <div class="div-4 flex-col sm:flex-row">
                            {{-- Option 1: Scan Biasa --}}
                            <div class="cursor-pointer transition-all duration-200"
                                 :class="modeScan === 'biasa' ? 'pilihan-konfigurasi' : 'pilihan-konfigurasi-2'"
                                 @click="modeScan = 'biasa'">
                                <input type="radio" name="mode_scan" value="biasa" class="sr-only" :checked="modeScan === 'biasa'">
                                
                                <div class="identitas-pilihan">
                                    <div class="vector-wrapper">
                                        <svg class="vector-2 w-4 h-4 text-[#55d9ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <span class="nama-pilihan">Scan Biasa</span>
                                    <div class="tombol-radio">
                                        <div x-show="modeScan === 'biasa'" class="pilihan-aktif"></div>
                                    </div>
                                </div>

                                <div class="kategori-pilihan">STANDAR</div>
                                <p class="deskripsi-pilihan">Pemindaian cepat menggunakan VirusTotal &amp; URLScan. Tidak butuh verifikasi kepemilikan.</p>

                                <div class="cakupan-pilihan">
                                    <div class="label-cakupan"><span class="nama-cakupan">Reputasi Domain</span></div>
                                    <div class="label-cakupan"><span class="nama-cakupan">SSL Check</span></div>
                                    <div class="label-cakupan"><span class="nama-cakupan">Security Headers</span></div>
                                </div>
                            </div>

                            {{-- Option 2: Scan Intens --}}
                            <div class="cursor-pointer transition-all duration-200"
                                 :class="modeScan === 'intens' ? 'pilihan-konfigurasi' : 'pilihan-konfigurasi-2'"
                                 @click="modeScan = 'intens'">
                                <input type="radio" name="mode_scan" value="intens" class="sr-only" :checked="modeScan === 'intens'">
                                
                                <div class="identitas-pilihan">
                                    <div class="vector-wrapper">
                                        <svg class="vector-3 w-4 h-4 text-[#55d9ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <span class="nama-pilihan">Scan Intens</span>
                                    <div class="tombol-radio" :class="modeScan === 'intens' ? 'border-[#55d9ed]' : 'border-[#73869f]'">
                                        <div x-show="modeScan === 'intens'" class="pilihan-aktif"></div>
                                    </div>
                                </div>

                                <div :class="modeScan === 'intens' ? 'kategori-pilihan' : 'kategori-pilihan-2'">DIDUKUNG AI</div>
                                <p class="deskripsi-pilihan">Pemindaian mendalam. Membutuhkan verifikasi kepemilikan domain terlebih dahulu.</p>

                                <div class="cakupan-pilihan">
                                    <div :class="modeScan === 'intens' ? 'label-cakupan' : 'nama-cakupan-wrapper'"><span :class="modeScan === 'intens' ? 'nama-cakupan' : 'nama-cakupan-2'">File Sensitif</span></div>
                                    <div :class="modeScan === 'intens' ? 'label-cakupan' : 'nama-cakupan-wrapper'"><span :class="modeScan === 'intens' ? 'nama-cakupan' : 'nama-cakupan-2'">IP &amp; ASN</span></div>
                                    <div :class="modeScan === 'intens' ? 'label-cakupan' : 'nama-cakupan-wrapper'"><span :class="modeScan === 'intens' ? 'nama-cakupan' : 'nama-cakupan-2'">Analisis AI</span></div>
                                </div>
                            </div>
                        </div>

                        {{-- Notice when Intens is chosen --}}
                        <div x-show="modeScan === 'intens'" x-transition class="w-full mt-2 p-3.5 rounded-lg border border-[#369cae]/40 bg-[#102c3b]/60 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-[#55d9ed] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-[#55d9ed] leading-relaxed">
                                <strong>Catatan Scan Intens:</strong> Memerlukan verifikasi kepemilikan domain. Anda akan diarahkan ke langkah verifikasi DNS atau meta-tag/file HTML sebelum pemindaian aktif berjalan.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ──────── STEP 02: Opsi Analisis AI ──────── --}}
                <div class="opsi-analisis-AI">
                    <div class="kepala-konfigurasi">
                        <div class="langkah-konfigurasi">
                            <span class="nomor-langkah">02</span>
                        </div>
                        <div class="keterangan-bagian">
                            <h2 class="text-wrapper-2">Opsi Analisis AI</h2>
                            <p class="petunjuk-bagian">Atur bantuan AI, model analisis, dan sumber pengetahuan.</p>
                        </div>
                    </div>

                    {{-- AI Connection Status Bar --}}
                    <div class="koneksi-penyedia-AI" :class="aiStatus === 'available' ? 'bg-[#102b2a] border border-[#244a41]' : (aiStatus === 'checking' ? 'bg-[#101b2b] border border-[#26364b]' : 'bg-red-950/20 border border-red-900/30')">
                        <div class="div" :class="aiStatus === 'available' ? 'bg-[#62d8ab]' : (aiStatus === 'checking' ? 'bg-[#55d9ed] animate-pulse' : 'bg-red-400')"></div>
                        <span class="penyedia-aktif" :class="aiStatus === 'available' ? 'text-[#62d8ab]' : (aiStatus === 'checking' ? 'text-[#a7b7cb]' : 'text-red-300')" x-text="aiMessage">AI terhubung: google (gemini-3.5-flash-lite)</span>
                        <span class="status-koneksi" :class="aiStatus === 'available' ? 'text-[#62d8ab]' : (aiStatus === 'checking' ? 'text-[#55d9ed]' : 'text-red-400')" x-text="aiStatus === 'available' ? 'TERHUBUNG' : (aiStatus === 'checking' ? 'MEMERIKSA' : 'OFFLINE')">TERHUBUNG</span>
                    </div>

                    {{-- AI Mode (Scan + AI vs Scan Tanpa AI) --}}
                    <div class="div-4 flex-col sm:flex-row">
                        {{-- Mode Scan + AI --}}
                        <div class="cursor-pointer transition-all duration-200"
                             :class="modeAi === 'dengan_ai' ? 'pilihan-konfigurasi' : 'pilihan-konfigurasi-2'"
                             @click="modeAi = 'dengan_ai'">
                            <input type="radio" name="mode_ai" value="dengan_ai" class="sr-only" :checked="modeAi === 'dengan_ai'">
                            <div class="identitas-pilihan">
                                <div class="vector-wrapper">
                                    <svg class="vector-4 w-4 h-4 text-[#55d9ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <span class="nama-pilihan">Scan + AI</span>
                                <div class="tombol-radio">
                                    <div x-show="modeAi === 'dengan_ai'" class="pilihan-aktif"></div>
                                </div>
                            </div>
                            <p class="deskripsi-pilihan">Analisis intens menggunakan script eksploitasi + RAG + AI untuk analisis mendalam.</p>
                        </div>

                        {{-- Mode Scan Tanpa AI --}}
                        <div class="cursor-pointer transition-all duration-200"
                             :class="modeAi === 'tanpa_ai' ? 'pilihan-konfigurasi' : 'pilihan-konfigurasi-2'"
                             @click="modeAi = 'tanpa_ai'">
                            <input type="radio" name="mode_ai" value="tanpa_ai" class="sr-only" :checked="modeAi === 'tanpa_ai'">
                            <div class="identitas-pilihan">
                                <div class="vector-wrapper">
                                    <svg class="vector-5 w-4 h-4 text-[#73869f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <span class="nama-pilihan">Scan Tanpa AI</span>
                                <div class="tombol-radio" :class="modeAi === 'tanpa_ai' ? 'border-[#55d9ed]' : 'border-[#73869f]'">
                                    <div x-show="modeAi === 'tanpa_ai'" class="pilihan-aktif"></div>
                                </div>
                            </div>
                            <p class="deskripsi-pilihan">Analisis menggunakan script eksploitasi/parsing saja. Lebih cepat, tanpa AI.</p>
                        </div>
                    </div>

                    {{-- Model Selector (Available when AI enabled) --}}
                    <div class="div-3 transition-opacity duration-200" :class="modeAi === 'tanpa_ai' ? 'opacity-40 pointer-events-none' : ''">
                        <label class="text-wrapper-7">Model AI yang Digunakan</label>
                        <div class="relative w-full">
                            <select name="model_ai"
                                    x-model="selectedModel"
                                    @change="updateModelName()"
                                    class="pemilih-model w-full bg-[#0b1422] border border-[#26364b] rounded-lg px-3.5 py-2.5 text-[#edf4fc] text-xs font-sans appearance-none outline-none focus:border-[#55d9ed] cursor-pointer">
                                <template x-if="availableModels.length === 0">
                                    <option :value="selectedModel" x-text="selectedModelName || 'Google AI (Gemini) — Default'"></option>
                                </template>
                                <template x-for="m in availableModels" :key="m.id">
                                    <option :value="m.id" x-text="m.name + ' (' + m.provider + ')'" :selected="m.id === selectedModel"></option>
                                </template>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                                <svg class="w-4 h-4 text-[#55d9ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Knowledge Base Source (RAG) --}}
                    <div class="div-3 transition-opacity duration-200" :class="modeAi === 'tanpa_ai' ? 'opacity-40 pointer-events-none' : ''">
                        <label class="text-wrapper-7">Sumber Knowledge Base (RAG)</label>
                        <div class="div-4 flex-col sm:flex-row">
                            {{-- Option 1: Dengan RAG --}}
                            <div class="cursor-pointer transition-all duration-200"
                                 :class="modeRag === 'dengan_rag' ? 'pilihan-konfigurasi' : 'pilihan-konfigurasi-2'"
                                 @click="modeRag = 'dengan_rag'">
                                <input type="radio" name="mode_rag" value="dengan_rag" class="sr-only" :checked="modeRag === 'dengan_rag'">
                                <div class="identitas-pilihan">
                                    <div class="vector-wrapper">
                                        <svg class="vector-7 w-4 h-4 text-[#55d9ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                                    <span class="nama-pilihan">Dengan RAG</span>
                                    <div class="tombol-radio">
                                        <div x-show="modeRag === 'dengan_rag'" class="pilihan-aktif"></div>
                                    </div>
                                </div>
                                <div class="deskripsi-pilihan">Analisis menggunakan dataset terpercaya:</div>
                                <div class="cakupan-pilihan">
                                    <div class="label-cakupan"><span class="nama-cakupan">NVD-CVE</span></div>
                                    <div class="label-cakupan"><span class="nama-cakupan">MITRE ATT&amp;CK</span></div>
                                    <div class="label-cakupan"><span class="nama-cakupan">CAPEC</span></div>
                                    <div class="label-cakupan"><span class="nama-cakupan">CWE</span></div>
                                    <div class="label-cakupan"><span class="nama-cakupan">OWASP</span></div>
                                </div>
                            </div>

                            {{-- Option 2: Tanpa RAG --}}
                            <div class="cursor-pointer transition-all duration-200"
                                 :class="modeRag === 'tanpa_rag' ? 'pilihan-konfigurasi' : 'pilihan-konfigurasi-2'"
                                 @click="modeRag = 'tanpa_rag'">
                                <input type="radio" name="mode_rag" value="tanpa_rag" class="sr-only" :checked="modeRag === 'tanpa_rag'">
                                <div class="identitas-pilihan">
                                    <div class="vector-wrapper">
                                        <svg class="vector-8 w-4 h-4 text-[#73869f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <span class="nama-pilihan">Tanpa RAG</span>
                                    <div class="tombol-radio" :class="modeRag === 'tanpa_rag' ? 'border-[#55d9ed]' : 'border-[#73869f]'">
                                        <div x-show="modeRag === 'tanpa_rag'" class="pilihan-aktif"></div>
                                    </div>
                                </div>
                                <p class="deskripsi-pilihan">Analisis berdasarkan pengetahuan AI saja tanpa mengacu dataset lokal.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── RIGHT COLUMN: RINGKASAN & PANDUAN (Sticky) ── --}}
            <div class="ringkasan-dan">

                {{-- Card 1: Ringkasan Konfigurasi --}}
                <div class="ringkasan">
                    <div class="aksen-ringkasan"></div>
                    <div class="isi-ringkasan">
                        <div class="div-5">
                            <h3 class="text-wrapper-2">Ringkasan konfigurasi</h3>
                            <div class="vector-wrapper">
                                <svg class="vector-9 w-4 h-4 text-[#55d9ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Target Display --}}
                        <div class="target-ringkasan">
                            <span class="label-target">URL TARGET</span>
                            <div class="div-6 w-full overflow-hidden">
                                <div class="img-wrapper shrink-0">
                                    <svg class="vector-10 w-3.5 h-3.5 text-[#55d9ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                </div>
                                <span x-show="!targetUrl" class="target-belum-diisi">Belum diisi</span>
                                <span x-show="targetUrl" x-text="targetUrl" class="text-sm font-mono text-[#55d9ed] truncate block w-full" :title="targetUrl"></span>
                            </div>
                        </div>

                        {{-- Configuration Details --}}
                        <div class="pilihan-saat-ini">
                            <div class="rincian-konfigurasi">
                                <span class="text-wrapper-8">Mode pemindaian</span>
                                <div class="nilai-konfigurasi">
                                    <div class="penanda-pilihan"></div>
                                    <span class="nilai-rincian" x-text="modeScan === 'biasa' ? 'Scan Biasa' : 'Scan Intens'">Scan Biasa</span>
                                </div>
                            </div>

                            <div class="rincian-konfigurasi">
                                <span class="text-wrapper-8">Analisis</span>
                                <div class="nilai-konfigurasi">
                                    <div class="penanda-pilihan"></div>
                                    <span class="nilai-rincian" x-text="modeAi === 'dengan_ai' ? 'Scan + AI' : 'Scan Tanpa AI'">Scan + AI</span>
                                </div>
                            </div>

                            <div class="rincian-konfigurasi">
                                <span class="text-wrapper-8">Sumber pengetahuan</span>
                                <div class="nilai-konfigurasi">
                                    <div class="penanda-pilihan"></div>
                                    <span class="nilai-rincian" x-text="modeAi === 'tanpa_ai' ? 'Tidak aktif' : (modeRag === 'dengan_rag' ? 'Dengan RAG' : 'Tanpa RAG')">Dengan RAG</span>
                                </div>
                            </div>

                            <div class="rincian-konfigurasi">
                                <span class="text-wrapper-8">Model AI</span>
                                <div class="nilai-konfigurasi">
                                    <span class="text-wrapper-7 text-xs truncate max-w-[170px]" x-text="modeAi === 'tanpa_ai' ? '—' : (selectedModelName || 'Gemini Flash')">Gemini 3.5 Flash Lite</span>
                                </div>
                            </div>

                            <div class="rincian-konfigurasi" style="border-bottom: none;">
                                <span class="text-wrapper-8">Otorisasi pengguna</span>
                                <div class="nilai-konfigurasi">
                                    <span class="text-xs font-medium" :class="disclaimerAccepted ? 'text-[#62d8ab]' : 'text-[#73869f]'" x-text="disclaimerAccepted ? 'Disetujui' : 'Belum disetujui'">Belum disetujui</span>
                                </div>
                            </div>
                        </div>

                        {{-- Readiness Hint --}}
                        <div class="pengingat-persiapan">
                            <div class="img-wrapper shrink-0 mt-0.5">
                                <svg class="vector-10 w-4 h-4 text-[#ebc27b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <p class="petunjuk-mulai">Lengkapi URL target dan persetujuan otorisasi sebelum memulai pemindaian.</p>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Panduan Pemindaian --}}
                <div class="panduan-pemindaian">
                    <h3 class="judul-panduan">Sebelum Anda mulai</h3>

                    <div class="div-4 items-start">
                        <div class="lambang-panduan shrink-0">
                            <div class="img-wrapper">
                                <svg class="vector-11 w-4 h-4 text-[#55d9ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="isi-panduan">
                            <h4 class="text-wrapper-6">Pemeriksaan cepat, tanpa verifikasi</h4>
                            <p class="keterangan-panduan">Scan Biasa menggunakan VirusTotal &amp; URLScan untuk memeriksa reputasi domain, SSL, dan security headers.</p>
                        </div>
                    </div>

                    <div class="div-4 items-start">
                        <div class="lambang-panduan shrink-0">
                            <div class="img-wrapper">
                                <svg class="vector-12 w-4 h-4 text-[#55d9ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="isi-panduan">
                            <h4 class="text-wrapper-6">Verifikasi untuk Scan Intens</h4>
                            <p class="keterangan-panduan">Verifikasi kepemilikan domain diperlukan terlebih dahulu untuk pemindaian mendalam, termasuk file sensitif serta IP &amp; ASN.</p>
                        </div>
                    </div>

                    <div class="div-4 items-start">
                        <div class="lambang-panduan shrink-0">
                            <div class="img-wrapper">
                                <svg class="vector-13 w-4 h-4 text-[#55d9ed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        </div>
                        <div class="isi-panduan">
                            <h4 class="text-wrapper-6">Konteks tambahan dari RAG</h4>
                            <p class="keterangan-panduan">Dengan RAG, analisis AI mengacu pada dataset NVD-CVE, MITRE ATT&amp;CK, CAPEC, CWE, dan OWASP.</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- ════════════════════════════════════════════════════════════════════════
             BOTTOM SECTION: PERSETUJUAN & AKSI
             ════════════════════════════════════════════════════════════════════════ --}}
        <div class="persetujuan-dan">
            {{-- Otorisasi & Tanggung Jawab Pengguna --}}
            <div class="otorisasi-dan">
                <div class="shield-alert shrink-0">
                    <svg class="vector-14 w-5 h-5 text-[#ebc27b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="persetujuan-pengguna">
                    <p class="judul-legal">Otorisasi &amp; Tanggung Jawab Pengguna</p>
                    <label class="div-4 items-start cursor-pointer group">
                        <input type="checkbox"
                               id="disclaimer_check"
                               name="disclaimer_check"
                               x-model="disclaimerAccepted"
                               required
                               class="mt-1 w-4 h-4 rounded bg-[#0b1422] border border-[#7d806e] text-[#55d9ed] focus:ring-0 focus:ring-offset-0 cursor-pointer">
                        <p class="pernyataan-otorisasi">
                            <span class="text-wrapper-4">Saya menyatakan bahwa saya </span>
                            <span class="text-wrapper-9">memiliki izin eksplisit</span>
                            <span class="text-wrapper-4"> untuk melakukan pemindaian keamanan terhadap URL yang dimasukkan. Pemindaian terhadap sistem milik orang lain tanpa izin merupakan </span>
                            <span class="text-wrapper-10">tindakan ilegal.</span>
                            <span class="text-wrapper-4"> Saya bertanggung jawab penuh atas penggunaan tool ini sesuai dengan </span>
                            <a href="{{ route('legal.ketentuan') }}" target="_blank" class="text-wrapper-11">Ketentuan Layanan</a>
                            <span class="text-wrapper-4"> RedSim.</span>
                        </p>
                    </label>
                </div>
            </div>

            {{-- Action Row --}}
            <div class="div-5 flex-col sm:flex-row gap-4 items-center">
                <div class="div-6">
                    <div class="img-wrapper shrink-0">
                        <svg class="vector-13 w-4 h-4 text-[#73869f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <p class="text-wrapper-8">Gunakan hanya pada sistem milik Anda atau dengan izin pemilik.</p>
                </div>

                <button type="submit"
                        class="mulai-pemindaian group hover:shadow-[0_0_20px_rgba(85,217,237,0.35)] transition-all duration-200">
                    <div class="vector-wrapper">
                        <svg class="vector-15 w-4 h-4 text-[#0b1422]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <span class="label-tombol">Mulai Pemindaian</span>
                    <div class="img-wrapper transition-transform duration-200 group-hover:translate-x-0.5">
                        <svg class="vector-16 w-3.5 h-3.5 text-[#0b1422]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </div>
                </button>
            </div>
        </div>

    </form>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════
     SCOPED STYLESHEET (Faithful port of provided reference CSS)
     ════════════════════════════════════════════════════════════════════════════ --}}
<style>
/* Base Outer Container */
.pemindaian-URL {
  display: flex;
  flex-direction: column;
  min-height: 1000px;
  align-items: flex-start;
  gap: 24px;
  padding: 32px 36px;
  position: relative;
  background-color: #080f19;
  border-radius: 16px;
  border: 1px solid #26364b;
  font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  box-sizing: border-box;
}

/* Page Header */
.pemindaian-URL .kepala-halaman {
  justify-content: space-between;
  padding: 0 0 24px;
  border-bottom: 1px solid #26364b;
  display: flex;
  align-items: center;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  gap: 20px;
  flex-wrap: wrap;
}

.pemindaian-URL .pengantar-halaman {
  display: inline-flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 6px;
  position: relative;
  flex: 1;
  min-width: 260px;
}

.pemindaian-URL .konteks-produk {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", "JetBrains Mono", monospace;
  font-weight: 500;
  color: #55d9ed;
  font-size: 11px;
  letter-spacing: 0.13px;
  line-height: normal;
  text-transform: uppercase;
}

.pemindaian-URL .judul-halaman {
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #edf4fc;
  font-size: 28px;
  letter-spacing: -0.32px;
  position: relative;
  width: fit-content;
  line-height: 1.2;
  margin: 0;
}

.pemindaian-URL .deskripsi-halaman {
  width: fit-content;
  font-size: 14px;
  line-height: normal;
  position: relative;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  color: #a7b7cb;
  letter-spacing: 0;
  margin: 0;
}

.pemindaian-URL .status-layanan-dan {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  position: relative;
  flex: 0 0 auto;
}

.pemindaian-URL .status-AI {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  position: relative;
  flex: 0 0 auto;
  background-color: #102b2a;
  border-radius: 100px;
  overflow: hidden;
  border: 1px solid #244a41;
}

.pemindaian-URL .div {
  position: relative;
  width: 6px;
  height: 6px;
  background-color: #62d8ab;
  border-radius: 3px;
}

.pemindaian-URL .status-layanan {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 500;
  color: #62d8ab;
  font-size: 12px;
  letter-spacing: 0;
  line-height: normal;
}

.pemindaian-URL .penggunaan-harian {
  display: flex;
  flex-direction: column;
  width: 186px;
  align-items: flex-start;
  gap: 8px;
  padding: 12px 14px;
  position: relative;
  background-color: #101b2b;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #26364b;
}

.pemindaian-URL .div-2 {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .label-penggunaan {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  color: #a7b7cb;
  font-size: 12px;
  letter-spacing: 0;
  line-height: normal;
}

.pemindaian-URL .nilai-penggunaan {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", "JetBrains Mono", monospace;
  font-weight: 500;
  font-size: 13px;
  letter-spacing: 0;
  line-height: normal;
  margin: 0;
}

.pemindaian-URL .text-wrapper {
  color: #edf4fc;
}

.pemindaian-URL .span {
  color: #73869f;
}

.pemindaian-URL .kuota-penggunaan {
  display: flex;
  align-items: flex-start;
  align-self: stretch;
  width: 100%;
  background-color: #26364b;
  overflow: hidden;
  height: 3px;
  position: relative;
  border-radius: 2px;
}

.pemindaian-URL .kuota-terpakai {
  background-color: #55d9ed;
  height: 3px;
  position: relative;
  border-radius: 2px;
  transition: width 0.3s ease;
}

/* 2-Column Grid */
.pemindaian-URL .konfigurasi {
  display: flex;
  align-items: flex-start;
  gap: 24px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

@media (max-width: 1024px) {
  .pemindaian-URL .konfigurasi {
    flex-direction: column;
  }
}

.pemindaian-URL .formulir-konfigurasi {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 20px;
  position: relative;
  flex: 1;
  width: 100%;
  flex-grow: 1;
}

/* Sections */
.pemindaian-URL .target-dan-mode {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 20px;
  padding: 24px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  background-color: #101b2b;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #26364b;
}

.pemindaian-URL .kepala-konfigurasi {
  gap: 12px;
  display: flex;
  align-items: center;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .langkah-konfigurasi {
  display: flex;
  width: 32px;
  height: 32px;
  align-items: center;
  justify-content: center;
  position: relative;
  background-color: #152237;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #26364b;
  shrink: 0;
}

.pemindaian-URL .nomor-langkah {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", "JetBrains Mono", monospace;
  font-weight: 600;
  color: #55d9ed;
  font-size: 12px;
  letter-spacing: 0;
  line-height: normal;
}

.pemindaian-URL .keterangan-bagian {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 3px;
  position: relative;
  flex: 1;
  flex-grow: 1;
}

.pemindaian-URL .text-wrapper-2 {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #edf4fc;
  font-size: 16px;
  letter-spacing: 0;
  line-height: normal;
  margin: 0;
}

.pemindaian-URL .petunjuk-bagian {
  align-self: stretch;
  color: #a7b7cb;
  line-height: normal;
  position: relative;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  font-size: 12px;
  letter-spacing: 0;
  margin: 0;
}

.pemindaian-URL .div-3 {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 8px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .label-kolom {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 600;
  font-size: 13px;
  letter-spacing: 0;
  line-height: normal;
}

.pemindaian-URL .text-wrapper-3 {
  color: #55d9ed;
}

.pemindaian-URL .circle-help {
  position: relative;
  width: 15px;
  height: 15px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
}

.pemindaian-URL .masukan-URL-kosong {
  display: flex;
  height: 48px;
  align-items: center;
  gap: 12px;
  padding: 0 14px;
  position: relative;
  align-self: stretch;
  width: 100%;
  background-color: #0b1422;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #26364b;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.pemindaian-URL .masukan-URL-kosong:focus-within {
  border-color: #55d9ed;
  box-shadow: 0 0 0 2px rgba(85,217,237,0.15);
}

.pemindaian-URL .vector-wrapper {
  position: relative;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.pemindaian-URL .petunjuk-URL {
  width: fit-content;
  line-height: normal;
  position: relative;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  font-size: 12px;
  letter-spacing: 0;
  margin: 0;
}

.pemindaian-URL .text-wrapper-4 {
  color: #a7b7cb;
}

.pemindaian-URL .text-wrapper-5 {
  font-family: "IBM Plex Mono", "JetBrains Mono", monospace;
  color: #55d9ed;
}

.pemindaian-URL .pemilihan-mode {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .text-wrapper-6 {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #edf4fc;
  font-size: 13px;
  letter-spacing: 0;
  line-height: normal;
  margin: 0;
}

.pemindaian-URL .div-4 {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

/* Option Cards (Active vs Inactive) */
.pemindaian-URL .pilihan-konfigurasi {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;
  padding: 16px;
  position: relative;
  flex: 1;
  align-self: stretch;
  flex-grow: 1;
  background-color: #102c3b;
  border-radius: 8px;
  overflow: hidden;
  border: 1.5px solid #369cae;
  box-shadow: 0 0 12px rgba(85,217,237,0.12);
}

.pemindaian-URL .pilihan-konfigurasi-2 {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;
  padding: 16px;
  position: relative;
  flex: 1;
  align-self: stretch;
  flex-grow: 1;
  background-color: #0b1422;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #26364b;
}

.pemindaian-URL .pilihan-konfigurasi-2:hover {
  border-color: #3e5370;
}

.pemindaian-URL .identitas-pilihan {
  display: flex;
  align-items: center;
  gap: 10px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .nama-pilihan {
  flex: 1;
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #edf4fc;
  font-size: 14px;
  position: relative;
  letter-spacing: 0;
  line-height: normal;
}

.pemindaian-URL .tombol-radio {
  display: flex;
  width: 16px;
  height: 16px;
  align-items: center;
  justify-content: center;
  position: relative;
  border-radius: 100px;
  overflow: hidden;
  border: 1.5px solid #55d9ed;
  flex-shrink: 0;
}

.pemindaian-URL .pilihan-aktif {
  position: relative;
  width: 8px;
  height: 8px;
  background-color: #55d9ed;
  border-radius: 4px;
}

.pemindaian-URL .kategori-pilihan {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #55d9ed;
  font-size: 10px;
  letter-spacing: 0.06px;
  line-height: normal;
  white-space: nowrap;
}

.pemindaian-URL .kategori-pilihan-2 {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #a7b7cb;
  font-size: 10px;
  letter-spacing: 0.06px;
  line-height: normal;
  white-space: nowrap;
}

.pemindaian-URL .deskripsi-pilihan {
  align-self: stretch;
  font-size: 12px;
  line-height: 1.5;
  position: relative;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  color: #a7b7cb;
  letter-spacing: 0;
  margin: 0;
}

.pemindaian-URL .cakupan-pilihan {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  gap: 6px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .label-cakupan {
  display: inline-flex;
  align-items: flex-start;
  padding: 4px 7px;
  position: relative;
  flex: 0 0 auto;
  background-color: #194050;
  border-radius: 4px;
  overflow: hidden;
}

.pemindaian-URL .nama-cakupan {
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 500;
  color: #55d9ed;
  font-size: 10px;
  white-space: nowrap;
  position: relative;
  letter-spacing: 0;
  line-height: normal;
}

.pemindaian-URL .nama-cakupan-wrapper {
  display: inline-flex;
  align-items: flex-start;
  padding: 4px 7px;
  position: relative;
  flex: 0 0 auto;
  background-color: #152237;
  border-radius: 4px;
  overflow: hidden;
}

.pemindaian-URL .nama-cakupan-2 {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 500;
  color: #a7b7cb;
  font-size: 10px;
  letter-spacing: 0;
  line-height: normal;
  white-space: nowrap;
}

/* Step 02: AI Options */
.pemindaian-URL .opsi-analisis-AI {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 18px;
  padding: 24px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  background-color: #101b2b;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #26364b;
}

.pemindaian-URL .koneksi-penyedia-AI {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 10px 14px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  border-radius: 6px;
  overflow: hidden;
}

.pemindaian-URL .penyedia-aktif {
  position: relative;
  flex: 1;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  font-size: 12px;
  letter-spacing: 0;
  line-height: normal;
}

.pemindaian-URL .status-koneksi {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 600;
  font-size: 10px;
  letter-spacing: 0.05em;
  line-height: normal;
  white-space: nowrap;
}

.pemindaian-URL .text-wrapper-7 {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 500;
  color: #edf4fc;
  font-size: 13px;
  letter-spacing: 0;
  line-height: normal;
  margin: 0;
}

.pemindaian-URL .img-wrapper {
  position: relative;
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Right Column (Sticky & Cards) */
.pemindaian-URL .ringkasan-dan {
  display: flex;
  flex-direction: column;
  width: 420px;
  align-items: flex-start;
  gap: 20px;
  position: relative;
  flex-shrink: 0;
}

@media (max-width: 1024px) {
  .pemindaian-URL .ringkasan-dan {
    width: 100%;
  }
}

.pemindaian-URL .ringkasan {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  background-color: #101b2b;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #26364b;
}

.pemindaian-URL .aksen-ringkasan {
  position: relative;
  align-self: stretch;
  width: 100%;
  height: 3px;
  background-color: #55d9ed;
}

.pemindaian-URL .isi-ringkasan {
  gap: 16px;
  padding: 24px;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  position: relative;
}

.pemindaian-URL .div-5 {
  justify-content: space-between;
  display: flex;
  align-items: center;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .target-ringkasan {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 8px;
  padding: 14px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  background-color: #0b1422;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #26364b;
}

.pemindaian-URL .label-target {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 500;
  color: #a7b7cb;
  font-size: 11px;
  letter-spacing: 0.05em;
  line-height: normal;
  white-space: nowrap;
}

.pemindaian-URL .div-6 {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  position: relative;
  flex: 0 0 auto;
}

.pemindaian-URL .target-belum-diisi {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  color: #73869f;
  font-size: 13px;
  letter-spacing: 0;
  line-height: normal;
}

.pemindaian-URL .pilihan-saat-ini {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .rincian-konfigurasi {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 0;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  border-bottom: 1px solid #26364b;
}

.pemindaian-URL .text-wrapper-8 {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  color: #a7b7cb;
  font-size: 12px;
  letter-spacing: 0;
  line-height: normal;
  margin: 0;
}

.pemindaian-URL .nilai-konfigurasi {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  position: relative;
  flex: 0 0 auto;
}

.pemindaian-URL .penanda-pilihan {
  position: relative;
  width: 5px;
  height: 5px;
  background-color: #55d9ed;
  border-radius: 2.5px;
}

.pemindaian-URL .nilai-rincian {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 500;
  color: #55d9ed;
  font-size: 13px;
  letter-spacing: 0;
  line-height: normal;
}

.pemindaian-URL .pengingat-persiapan {
  display: flex;
  align-items: flex-start;
  gap: 9px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  padding: 10px 12px;
  background: rgba(235, 194, 123, 0.05);
  border: 1px solid rgba(235, 194, 123, 0.18);
  border-radius: 8px;
}

.pemindaian-URL .petunjuk-mulai {
  flex: 1;
  color: #a7b7cb;
  line-height: 1.5;
  position: relative;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  font-size: 12px;
  letter-spacing: 0;
  margin: 0;
}

/* Guide Card */
.pemindaian-URL .panduan-pemindaian {
  flex-direction: column;
  gap: 18px;
  padding: 24px;
  background-color: #101b2b;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #26364b;
  display: flex;
  align-items: flex-start;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .judul-panduan {
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #edf4fc;
  font-size: 15px;
  letter-spacing: 0;
  white-space: nowrap;
  position: relative;
  width: fit-content;
  line-height: normal;
  margin: 0;
}

.pemindaian-URL .lambang-panduan {
  display: flex;
  width: 32px;
  height: 32px;
  align-items: center;
  justify-content: center;
  position: relative;
  background-color: #152237;
  border-radius: 6px;
  overflow: hidden;
  border: 1px solid #26364b;
}

.pemindaian-URL .isi-panduan {
  gap: 4px;
  flex: 1;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  position: relative;
}

.pemindaian-URL .keterangan-panduan {
  position: relative;
  align-self: stretch;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  color: #a7b7cb;
  font-size: 12px;
  letter-spacing: 0;
  line-height: 1.5;
  margin: 0;
}

/* Bottom Legal & Submit Section */
.pemindaian-URL .persetujuan-dan {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 20px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .otorisasi-dan {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 20px 24px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  background-color: #25251e;
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #4b4430;
}

.pemindaian-URL .shield-alert {
  position: relative;
  width: 22px;
  height: 22px;
}

.pemindaian-URL .persetujuan-pengguna {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;
  position: relative;
  flex: 1;
  flex-grow: 1;
}

.pemindaian-URL .judul-legal {
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #ebc27b;
  font-size: 14px;
  letter-spacing: 0;
  position: relative;
  width: fit-content;
  line-height: normal;
  margin: 0;
}

.pemindaian-URL .pernyataan-otorisasi {
  position: relative;
  flex: 1;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  font-size: 13px;
  letter-spacing: 0;
  line-height: 1.6;
  margin: 0;
}

.pemindaian-URL .text-wrapper-9 {
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #edf4fc;
}

.pemindaian-URL .text-wrapper-10 {
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #ebc27b;
}

.pemindaian-URL .text-wrapper-11 {
  color: #55d9ed;
  text-decoration: underline;
  cursor: pointer;
}

.pemindaian-URL .text-wrapper-11:hover {
  color: #8be3f3;
}

.pemindaian-URL .mulai-pemindaian {
  display: inline-flex;
  height: 48px;
  align-items: center;
  gap: 10px;
  padding: 0 24px;
  position: relative;
  flex: 0 0 auto;
  background-color: #55d9ed;
  border-radius: 8px;
  overflow: hidden;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pemindaian-URL .mulai-pemindaian:hover {
  background-color: #6ee5f7;
  transform: translateY(-1px);
}

.pemindaian-URL .label-tombol {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #0b1422;
  font-size: 14px;
  letter-spacing: 0;
  line-height: normal;
}
</style>

{{-- ════════════════════════════════════════════════════════════════════════════
     ALPINE LOGIC FOR REAL-TIME REACTIVITY
     ════════════════════════════════════════════════════════════════════════════ --}}
<script>
function urlScannerForm(config) {
    return {
        targetUrl: config.initialUrl || '',
        modeScan: config.initialModeScan || 'biasa',
        modeAi: config.initialModeAi || 'dengan_ai',
        modeRag: config.initialModeRag || 'dengan_rag',
        selectedModel: config.initialModelAi || '',
        selectedModelName: 'Gemini 3.5 Flash Lite',
        disclaimerAccepted: Boolean(config.initialDisclaimer),
        aiStatus: 'checking',
        aiMessage: 'Memeriksa status AI...',
        aiProvider: '',
        aiModel: '',
        availableModels: [],

        init() {
            this.checkAiHealth();
        },

        async checkAiHealth() {
            try {
                const r = await fetch('{{ route("api.ai-health") }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const d = await r.json();
                this.aiStatus = d.status;
                this.aiMessage = d.message || 'AI terhubung';
                this.aiProvider = d.provider || 'google';
                this.aiModel = d.model || 'gemini-2.5-flash';
                this.availableModels = d.available_models || [];

                if (!this.selectedModel && d.model) {
                    this.selectedModel = d.model;
                }
                this.updateModelName();

                if (d.status !== 'available') {
                    // Optional fallback behavior
                }
            } catch (e) {
                this.aiStatus = 'error';
                this.aiMessage = 'Gagal memeriksa koneksi AI.';
                this.aiProvider = 'google';
                this.aiModel = 'offline';
            }
        },

        updateModelName() {
            if (!this.selectedModel) {
                this.selectedModelName = this.aiModel || 'Gemini 3.5 Flash Lite';
                return;
            }
            const found = this.availableModels.find(m => m.id === this.selectedModel);
            if (found) {
                this.selectedModelName = found.name;
            } else {
                this.selectedModelName = this.selectedModel;
            }
        }
    }
}
</script>
@endsection
