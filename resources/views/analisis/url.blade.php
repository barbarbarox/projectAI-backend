@extends('layouts.app')
@section('title', 'Pemindaian URL — RedSim')
@section('header', 'Pemindaian URL')
@section('subheader', 'Konfigurasikan target dan analisis keamanan mendalam berbasis AI.')
@section('page_feature', 'pemindai-url')

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
            <div class="status-AI"
                 :class="aiStatus === 'available' ? 'border-[#3a1f1f] bg-[#1b1212]' : (aiStatus === 'checking' ? 'border-[#2a2a2a] bg-[#161616]' : 'border-red-900/40 bg-red-950/20')">
                <div class="div"
                     :class="aiStatus === 'available' ? 'bg-[#ef4444] shadow-[0_0_8px_rgba(239,68,68,0.5)]' : (aiStatus === 'checking' ? 'bg-[#ef4444] animate-pulse' : 'bg-red-400')"></div>
                <span class="status-layanan"
                      :class="aiStatus === 'available' ? 'text-[#ef4444]' : (aiStatus === 'checking' ? 'text-[#ef4444]' : 'text-red-400')"
                      x-text="aiStatus === 'available' ? 'AI Aktif' : (aiStatus === 'checking' ? 'Memeriksa...' : 'AI Terbatas')">AI Aktif</span>
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
                                <svg class="vector w-3.5 h-3.5 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>
                        </div>

                        <div class="masukan-URL-kosong">
                            <div class="vector-wrapper">
                                <svg class="img w-4 h-4 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                   class="bg-transparent border-none outline-none text-[#edf4fc] font-mono text-sm w-full placeholder-[#555]">
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
                                        <svg class="vector-2 w-4 h-4 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <svg class="vector-3 w-4 h-4 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <span class="nama-pilihan">Scan Intens</span>
                                    <div class="tombol-radio" :class="modeScan === 'intens' ? 'border-[#ef4444]' : 'border-[#555]'">
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
                        <div x-show="modeScan === 'intens'" x-transition class="w-full mt-2 p-3.5 rounded-lg border border-[#6e2a36]/40 bg-[#1b1212]/60 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-[#ef4444] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-[#ef4444] leading-relaxed">
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
                    <div class="koneksi-penyedia-AI"
                         :class="aiStatus === 'available' ? 'bg-[#1b1212] border border-[#3a1f1f]' : (aiStatus === 'checking' ? 'bg-[#161616] border border-[#2a2a2a]' : 'bg-red-950/20 border border-red-900/30')">
                        <div class="div"
                             :class="aiStatus === 'available' ? 'bg-[#ef4444]' : (aiStatus === 'checking' ? 'bg-[#ef4444] animate-pulse' : 'bg-red-400')"></div>
                        <span class="penyedia-aktif"
                              :class="aiStatus === 'available' ? 'text-[#ef4444]' : (aiStatus === 'checking' ? 'text-[#888]' : 'text-red-300')"
                              x-text="aiMessage">AI terhubung: google (gemini-3.5-flash-lite)</span>
                        <span class="status-koneksi"
                              :class="aiStatus === 'available' ? 'text-[#ef4444]' : (aiStatus === 'checking' ? 'text-[#ef4444]' : 'text-red-400')"
                              x-text="aiStatus === 'available' ? 'TERHUBUNG' : (aiStatus === 'checking' ? 'MEMERIKSA' : 'OFFLINE')">TERHUBUNG</span>
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
                                    <svg class="vector-4 w-4 h-4 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    <svg class="vector-5 w-4 h-4 text-[#555]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <span class="nama-pilihan">Scan Tanpa AI</span>
                                <div class="tombol-radio" :class="modeAi === 'tanpa_ai' ? 'border-[#ef4444]' : 'border-[#555]'">
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
                                    class="pemilih-model w-full bg-[#111111] border border-[#2a2a2a] rounded-lg px-3.5 py-2.5 text-[#edf4fc] text-xs font-sans appearance-none outline-none focus:border-[#ef4444] cursor-pointer">
                                <template x-if="availableModels.length === 0">
                                    <option :value="selectedModel" x-text="selectedModelName || 'Google AI (Gemini) — Default'"></option>
                                </template>
                                <template x-for="m in availableModels" :key="m.id">
                                    <option :value="m.id" x-text="m.name + ' (' + m.provider + ')'" :selected="m.id === selectedModel"></option>
                                </template>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                                <svg class="w-4 h-4 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <svg class="vector-7 w-4 h-4 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <svg class="vector-8 w-4 h-4 text-[#555]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <span class="nama-pilihan">Tanpa RAG</span>
                                    <div class="tombol-radio" :class="modeRag === 'tanpa_rag' ? 'border-[#ef4444]' : 'border-[#555]'">
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
                                <svg class="vector-9 w-4 h-4 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Target Display --}}
                        <div class="target-ringkasan">
                            <span class="label-target">URL TARGET</span>
                            <div class="div-6 w-full overflow-hidden">
                                <div class="img-wrapper shrink-0">
                                    <svg class="vector-10 w-3.5 h-3.5 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                </div>
                                <span x-show="!targetUrl" class="target-belum-diisi">Belum diisi</span>
                                <span x-show="targetUrl" x-text="targetUrl" class="text-sm font-mono text-[#ef4444] truncate block w-full" :title="targetUrl"></span>
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
                                    <span class="text-xs font-medium"
                                          :class="disclaimerAccepted ? 'text-[#ef4444]' : 'text-[#555]'"
                                          x-text="disclaimerAccepted ? 'Disetujui' : 'Belum disetujui'">Belum disetujui</span>
                                </div>
                            </div>
                        </div>

                        {{-- Readiness Hint --}}
                        <div class="pengingat-persiapan">
                            <div class="img-wrapper shrink-0 mt-0.5">
                                <svg class="vector-10 w-4 h-4 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <svg class="vector-11 w-4 h-4 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <svg class="vector-12 w-4 h-4 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <svg class="vector-13 w-4 h-4 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <svg class="vector-14 w-5 h-5 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                               class="mt-1 w-4 h-4 rounded bg-[#111111] border border-[#3a3a3a] text-[#ef4444] focus:ring-0 focus:ring-offset-0 cursor-pointer">
                        <p class="pernyataan-otorisasi">
                            <span class="text-wrapper-4">Saya menyatakan bahwa saya </span>
                            <span class="text-wrapper-9">memiliki izin eksplisit</span>
                            <span class="text-wrapper-4"> untuk melakukan pemindaian keamanan terhadap URL yang dimasukkan. Pemindaian terhadap sistem milik orang lain tanpa izin merupakan </span>
                            <span class="text-wrapper-10">tindakan ilegal.</span>
                            <span class="text-wrapper-4"> Saya bertanggung jawab penuh atas penggunaan tool ini sesuai dengan </span>
                            <a href="{{ route('ketentuan-layanan') }}" target="_blank" class="text-wrapper-11">Ketentuan Layanan</a>
                            <span class="text-wrapper-4"> RedSim.</span>
                        </p>
                    </label>
                </div>
            </div>

            {{-- Action Row --}}
            <div class="div-5 flex-col sm:flex-row gap-4 items-center">
                <div class="div-6">
                    <div class="img-wrapper shrink-0">
                        <svg class="vector-13 w-4 h-4 text-[#555]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <p class="text-wrapper-8">Gunakan hanya pada sistem milik Anda atau dengan izin pemilik.</p>
                </div>

                <button type="submit"
                        class="mulai-pemindaian group hover:shadow-[0_0_20px_rgba(239,68,68,0.35)] transition-all duration-200">
                    <div class="vector-wrapper">
                        <svg class="vector-15 w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <span class="label-tombol">Mulai Pemindaian</span>
                    <div class="img-wrapper transition-transform duration-200 group-hover:translate-x-0.5">
                        <svg class="vector-16 w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </div>
                </button>
            </div>
        </div>

    </form>
</div>

<style>
/* ═══════════════════════════════════════════════════════════════
   PEMINDAIAN URL — RED THEME (Rombak Besar-Besaran)
   Color Palette:
   - Page bg    : #111216
   - Card bg    : #161616
   - Input bg   : #111111
   - Border     : #2a2a2a
   - Accent red : #ef4444 / #dc2626
   - Active card: #1b1212 bg / #6e2a36 border
   - Text muted : #888 / #666 / #555
   ═══════════════════════════════════════════════════════════════ */

/* Page Root */
.pemindaian-URL {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 24px;
  padding: 32px;
  position: relative;
  background-color: #111216;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #2a2a2a;
  width: 100%;
}

@media (max-width: 768px) {
  .pemindaian-URL {
    padding: 20px 16px;
    border-radius: 12px;
  }
}

/* Page Header */
.pemindaian-URL .kepala-halaman {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  padding-bottom: 24px;
  border-bottom: 1px solid #242424;
  flex-wrap: wrap;
}

.pemindaian-URL .pengantar-halaman {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 6px;
  position: relative;
  flex: 1;
  flex-grow: 1;
  min-width: 200px;
}

.pemindaian-URL .konteks-produk {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", monospace;
  font-weight: 500;
  color: #ef4444;
  font-size: 11px;
  letter-spacing: 0.15em;
  line-height: normal;
  margin: 0;
  text-transform: uppercase;
}

.pemindaian-URL .judul-halaman {
  position: relative;
  align-self: stretch;
  font-family: "Inter", sans-serif;
  font-weight: 700;
  color: #f6f6f8;
  font-size: 28px;
  letter-spacing: -0.02em;
  line-height: 1.2;
  margin: 0;
}

.pemindaian-URL .deskripsi-halaman {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  color: #666;
  font-size: 14px;
  letter-spacing: 0;
  line-height: 1.5;
  margin: 0;
}

/* Header Right: AI Status & Usage */
.pemindaian-URL .status-layanan-dan {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 10px;
  position: relative;
  flex: 0 0 auto;
}

.pemindaian-URL .status-AI {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  position: relative;
  flex: 0 0 auto;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid;
  transition: all 0.3s ease;
}

.pemindaian-URL .status-AI .div {
  position: relative;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.pemindaian-URL .status-layanan {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", monospace;
  font-weight: 500;
  font-size: 11px;
  letter-spacing: 0.08em;
  line-height: normal;
  white-space: nowrap;
}

/* Daily Usage */
.pemindaian-URL .penggunaan-harian {
  display: flex;
  align-items: center;
  gap: 12px;
  position: relative;
  flex: 0 0 auto;
  padding: 8px 14px;
  background-color: #161616;
  border-radius: 8px;
  border: 1px solid #2a2a2a;
}

.pemindaian-URL .div-2 {
  display: flex;
  align-items: center;
  gap: 8px;
  position: relative;
  flex: 0 0 auto;
}

.pemindaian-URL .label-penggunaan {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  color: #666;
  font-size: 11px;
  letter-spacing: 0;
  line-height: normal;
  white-space: nowrap;
}

.pemindaian-URL .nilai-penggunaan {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", monospace;
  font-size: 12px;
  letter-spacing: 0;
  line-height: normal;
  margin: 0;
  white-space: nowrap;
}

.pemindaian-URL .nilai-penggunaan .text-wrapper {
  font-weight: 600;
  color: #f6f6f8;
}

.pemindaian-URL .nilai-penggunaan .span {
  font-weight: 400;
  color: #555;
}

.pemindaian-URL .kuota-penggunaan {
  position: relative;
  width: 80px;
  height: 4px;
  background-color: #2a2a2a;
  border-radius: 2px;
  overflow: hidden;
  flex-shrink: 0;
}

.pemindaian-URL .kuota-terpakai {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  background: linear-gradient(90deg, #ef4444, #dc2626);
  border-radius: 2px;
  transition: width 0.4s ease;
}

/* 2-Column Layout */
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

/* Left Column: Form */
.pemindaian-URL .formulir-konfigurasi {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 20px;
  position: relative;
  flex: 1;
  flex-grow: 1;
  min-width: 0;
}

/* Step Cards */
.pemindaian-URL .target-dan-mode,
.pemindaian-URL .opsi-analisis-AI {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 20px;
  padding: 24px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  background-color: #161616;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #2a2a2a;
}

/* Step Header */
.pemindaian-URL .kepala-konfigurasi {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .langkah-konfigurasi {
  display: flex;
  width: 36px;
  height: 36px;
  align-items: center;
  justify-content: center;
  position: relative;
  flex: 0 0 auto;
  background-color: #1f1414;
  border-radius: 6px;
  border: 1px solid #2a2a2a;
}

.pemindaian-URL .nomor-langkah {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", monospace;
  font-weight: 700;
  color: #ef4444;
  font-size: 13px;
  letter-spacing: 0.05em;
  line-height: normal;
}

.pemindaian-URL .keterangan-bagian {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 4px;
  position: relative;
  flex: 1;
  flex-grow: 1;
}

.pemindaian-URL .text-wrapper-2 {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #f6f6f8;
  font-size: 15px;
  letter-spacing: 0;
  line-height: normal;
  white-space: nowrap;
  margin: 0;
}

.pemindaian-URL .petunjuk-bagian {
  position: relative;
  align-self: stretch;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  color: #666;
  font-size: 13px;
  letter-spacing: 0;
  line-height: 1.5;
  margin: 0;
}

/* URL Input Field Container */
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
  display: flex;
  align-items: center;
  gap: 6px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .label-kolom .text-wrapper {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 500;
  color: #aaa;
  font-size: 12px;
  letter-spacing: 0.03em;
  line-height: normal;
}

.pemindaian-URL .label-kolom .text-wrapper-3 {
  color: #ef4444;
  font-size: 12px;
  font-weight: 700;
}

.pemindaian-URL .circle-help {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  background: transparent;
  border: none;
  padding: 0;
  cursor: pointer;
  margin-left: auto;
}

.pemindaian-URL .masukan-URL-kosong {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  background-color: #111111;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #2a2a2a;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.pemindaian-URL .masukan-URL-kosong:focus-within {
  border-color: #ef4444;
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.15);
}

.pemindaian-URL .vector-wrapper {
  position: relative;
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.pemindaian-URL .petunjuk-URL {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-size: 11px;
  letter-spacing: 0;
  line-height: normal;
  margin: 0;
}

.pemindaian-URL .petunjuk-URL .text-wrapper-4 {
  color: #555;
}

.pemindaian-URL .petunjuk-URL .text-wrapper-5 {
  color: #ef4444;
  font-family: "IBM Plex Mono", monospace;
  font-weight: 500;
}

/* Mode Selection Label */
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
  font-weight: 500;
  color: #aaa;
  font-size: 12px;
  letter-spacing: 0.03em;
  line-height: normal;
  text-transform: uppercase;
}

.pemindaian-URL .text-wrapper-7 {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 500;
  color: #aaa;
  font-size: 12px;
  letter-spacing: 0.03em;
  line-height: normal;
}

/* Option Cards row */
.pemindaian-URL .div-4 {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

/* Active option card */
.pemindaian-URL .pilihan-konfigurasi {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;
  padding: 16px;
  position: relative;
  flex: 1;
  flex-grow: 1;
  background-color: #1b1212;
  border-radius: 8px;
  overflow: hidden;
  border: 1.5px solid #6e2a36;
  box-shadow: 0 0 12px rgba(239, 68, 68, 0.12);
}

/* Inactive option card */
.pemindaian-URL .pilihan-konfigurasi-2 {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;
  padding: 16px;
  position: relative;
  flex: 1;
  flex-grow: 1;
  background-color: #111111;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #2a2a2a;
  transition: border-color 0.2s ease;
}

.pemindaian-URL .pilihan-konfigurasi-2:hover {
  border-color: #3a2020;
}

/* Option Card internals */
.pemindaian-URL .identitas-pilihan {
  display: flex;
  align-items: center;
  gap: 8px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
}

.pemindaian-URL .nama-pilihan {
  position: relative;
  flex: 1;
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #f6f6f8;
  font-size: 13px;
  letter-spacing: 0;
  line-height: normal;
}

/* Radio button */
.pemindaian-URL .tombol-radio {
  position: relative;
  width: 16px;
  height: 16px;
  border-radius: 8px;
  border: 1.5px solid #ef4444;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.pemindaian-URL .pilihan-aktif {
  position: relative;
  width: 8px;
  height: 8px;
  background-color: #ef4444;
  border-radius: 4px;
}

/* Category label */
.pemindaian-URL .kategori-pilihan {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", monospace;
  font-weight: 500;
  color: #ef4444;
  font-size: 10px;
  letter-spacing: 0.12em;
  line-height: normal;
  text-transform: uppercase;
}

.pemindaian-URL .kategori-pilihan-2 {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", monospace;
  font-weight: 500;
  color: #444;
  font-size: 10px;
  letter-spacing: 0.12em;
  line-height: normal;
  text-transform: uppercase;
}

.pemindaian-URL .deskripsi-pilihan {
  position: relative;
  align-self: stretch;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  color: #666;
  font-size: 12px;
  letter-spacing: 0;
  line-height: 1.5;
  margin: 0;
}

/* Scope tags */
.pemindaian-URL .cakupan-pilihan {
  display: flex;
  align-items: center;
  gap: 6px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  flex-wrap: wrap;
}

.pemindaian-URL .label-cakupan {
  display: flex;
  align-items: center;
  gap: 3px;
  padding: 3px 8px;
  position: relative;
  flex: 0 0 auto;
  background-color: #2b161c;
  border-radius: 4px;
  border: 1px solid #3a1f1f;
}

.pemindaian-URL .nama-cakupan {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", monospace;
  font-weight: 500;
  color: #ef4444;
  font-size: 10px;
  letter-spacing: 0.08em;
  line-height: normal;
  text-transform: uppercase;
}

.pemindaian-URL .nama-cakupan-wrapper {
  display: flex;
  align-items: center;
  gap: 3px;
  padding: 3px 8px;
  position: relative;
  flex: 0 0 auto;
  background-color: #1a1a1a;
  border-radius: 4px;
  border: 1px solid #2a2a2a;
}

.pemindaian-URL .nama-cakupan-2 {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", monospace;
  font-weight: 500;
  color: #444;
  font-size: 10px;
  letter-spacing: 0.08em;
  line-height: normal;
  text-transform: uppercase;
}

/* AI Connection Bar */
.pemindaian-URL .koneksi-penyedia-AI {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  position: relative;
  align-self: stretch;
  width: 100%;
  flex: 0 0 auto;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.pemindaian-URL .koneksi-penyedia-AI .div {
  position: relative;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.pemindaian-URL .penyedia-aktif {
  position: relative;
  flex: 1;
  font-family: "IBM Plex Mono", monospace;
  font-weight: 400;
  font-size: 11px;
  letter-spacing: 0.03em;
  line-height: normal;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.pemindaian-URL .status-koneksi {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", monospace;
  font-weight: 600;
  font-size: 10px;
  letter-spacing: 0.15em;
  line-height: normal;
  flex-shrink: 0;
  text-transform: uppercase;
}

/* Model Selector */
.pemindaian-URL .pemilih-model {
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.pemindaian-URL .pemilih-model:focus {
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.15);
}

/* img-wrapper generic */
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
  background-color: #161616;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #2a2a2a;
}

.pemindaian-URL .aksen-ringkasan {
  position: relative;
  align-self: stretch;
  width: 100%;
  height: 3px;
  background: linear-gradient(90deg, #ef4444, #dc2626);
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
  background-color: #111111;
  border-radius: 6px;
  overflow: hidden;
  border: 1px solid #2a2a2a;
}

.pemindaian-URL .label-target {
  position: relative;
  width: fit-content;
  font-family: "IBM Plex Mono", monospace;
  font-weight: 500;
  color: #555;
  font-size: 10px;
  letter-spacing: 0.12em;
  line-height: normal;
  white-space: nowrap;
  text-transform: uppercase;
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
  color: #444;
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
  border-bottom: 1px solid #242424;
}

.pemindaian-URL .text-wrapper-8 {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 400;
  color: #666;
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
  background-color: #ef4444;
  border-radius: 2.5px;
}

.pemindaian-URL .nilai-rincian {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 500;
  color: #ef4444;
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
  background: rgba(239, 68, 68, 0.05);
  border: 1px solid rgba(239, 68, 68, 0.18);
  border-radius: 6px;
}

.pemindaian-URL .petunjuk-mulai {
  flex: 1;
  color: #666;
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
  background-color: #161616;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #2a2a2a;
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
  color: #f6f6f8;
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
  background-color: #1f1414;
  border-radius: 6px;
  overflow: hidden;
  border: 1px solid #2a2a2a;
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
  color: #666;
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
  background-color: #1b1212;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #3a1f1f;
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
  color: #ef4444;
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
  color: #f6f6f8;
}

.pemindaian-URL .text-wrapper-10 {
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #ef4444;
}

.pemindaian-URL .text-wrapper-11 {
  color: #ef4444;
  text-decoration: underline;
  cursor: pointer;
  transition: color 0.2s ease;
}

.pemindaian-URL .text-wrapper-11:hover {
  color: #f87171;
}

.pemindaian-URL .text-wrapper-4 {
  color: #888;
}

/* Submit Button */
.pemindaian-URL .mulai-pemindaian {
  display: inline-flex;
  height: 48px;
  align-items: center;
  gap: 10px;
  padding: 0 24px;
  position: relative;
  flex: 0 0 auto;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  border-radius: 8px;
  overflow: hidden;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pemindaian-URL .mulai-pemindaian:hover {
  background: linear-gradient(135deg, #f87171, #ef4444);
  transform: translateY(-1px);
}

.pemindaian-URL .label-tombol {
  position: relative;
  width: fit-content;
  font-family: "Inter", sans-serif;
  font-weight: 600;
  color: #fff;
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
