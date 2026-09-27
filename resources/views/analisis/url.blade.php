@extends('layouts.app')
@section('title', 'Pemindaian URL — RedSim')
@section('header', 'Pemindaian URL')
@section('subheader', 'Masukkan URL untuk analisis keamanan mendalam berbasis AI')

@section('content')
<div class="max-w-2xl" x-data="{ mode: 'biasa' }">
    <form method="POST" action="{{ route('analisis.url') }}" class="space-y-5">
        @csrf

        {{-- URL Input Card --}}
        <div class="rs-card rounded-xl p-6">
            <label for="url" class="block text-sm font-medium mb-2" style="color:#94a3b8;">
                URL Target
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4" style="color:#475569;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9"/>
                    </svg>
                </div>
                <input type="url" id="url" name="url" value="{{ old('url') }}" required
                       class="w-full pl-10 pr-4 py-3 rounded-lg text-white placeholder-slate-600 transition-all duration-200"
                       style="background:#000;border:1px solid #1e293b;font-family:'JetBrains Mono',monospace;font-size:0.875rem;outline:none;"
                       placeholder="https://contoh.com"
                       onfocus="this.style.borderColor='rgba(220,38,38,0.5)';this.style.boxShadow='0 0 0 3px rgba(220,38,38,0.1)'"
                       onblur="this.style.borderColor='#1e293b';this.style.boxShadow='none'">
            </div>
            <p class="mt-2 text-xs" style="color:#475569;">Masukkan URL lengkap termasuk <code class="text-green-400 bg-black px-1 rounded font-mono">https://</code></p>
        </div>

        {{-- Mode Selection --}}
        <div class="rs-card rounded-xl p-6">
            <div class="flex items-center gap-2 mb-5">
                <svg class="w-4 h-4" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm font-semibold text-white">Mode Pemindaian</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Mode 1: Scan Biasa --}}
                <label class="relative cursor-pointer group" @click="mode = 'biasa'">
                    <input type="radio" name="mode_scan" value="biasa" class="peer sr-only" checked x-bind:checked="mode === 'biasa'">
                    <div class="rounded-xl border-2 p-4 transition-all duration-200 select-none
                                peer-checked:shadow-[0_0_12px_rgba(220,38,38,0.2)]"
                         :style="mode === 'biasa'
                             ? 'border-color:rgba(220,38,38,0.5);background:rgba(220,38,38,0.05);'
                             : 'border-color:#1e293b;background:transparent;'">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                                 style="background:rgba(220,38,38,0.12);">
                                <svg class="w-4.5 h-4.5" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-sm text-white">Scan Biasa</h4>
                                <span class="badge-security text-[9px]">Standard</span>
                            </div>
                        </div>
                        <p class="text-xs leading-relaxed mb-3" style="color:#64748b;">
                            Pemindaian cepat menggunakan VirusTotal &amp; URLScan. Tidak butuh verifikasi kepemilikan.
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium" style="background:rgba(220,38,38,0.10);color:#f87171;">Reputasi Domain</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium" style="background:rgba(220,38,38,0.10);color:#f87171;">SSL Check</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium" style="background:rgba(220,38,38,0.10);color:#f87171;">Security Headers</span>
                        </div>
                    </div>
                </label>

                {{-- Mode 2: Scan Intens --}}
                <label class="relative cursor-pointer group" @click="mode = 'intens'">
                    <input type="radio" name="mode_scan" value="intens" class="peer sr-only" x-bind:checked="mode === 'intens'">
                    <div class="rounded-xl border-2 p-4 transition-all duration-200 select-none
                                peer-checked:shadow-[0_0_12px_rgba(6,182,212,0.2)]"
                         :style="mode === 'intens'
                             ? 'border-color:rgba(6,182,212,0.5);background:rgba(6,182,212,0.05);'
                             : 'border-color:#1e293b;background:transparent;'">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                                 style="background:rgba(6,182,212,0.12);">
                                <svg class="w-4.5 h-4.5" style="color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-sm text-white">Scan Intens</h4>
                                <span class="badge-ai text-[9px]">AI Enhanced</span>
                            </div>
                        </div>
                        <p class="text-xs leading-relaxed mb-3" style="color:#64748b;">
                            Pemindaian mendalam. Membutuhkan verifikasi kepemilikan domain terlebih dahulu.
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium" style="background:rgba(6,182,212,0.10);color:#67e8f9;">File Sensitif</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium" style="background:rgba(6,182,212,0.10);color:#67e8f9;">IP &amp; ASN</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-medium" style="background:rgba(6,182,212,0.10);color:#67e8f9;">Analisis AI</span>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Info Mode Intens --}}
            <div x-show="mode === 'intens'" x-transition class="mt-4 p-3 rounded-lg flex items-start gap-2"
                 style="background:rgba(6,182,212,0.06);border:1px solid rgba(6,182,212,0.2);">
                <svg class="w-4 h-4 shrink-0 mt-0.5" style="color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs leading-relaxed" style="color:#22d3ee;">
                    Scan Intens memerlukan verifikasi kepemilikan domain. Anda akan diminta mengupload file HTML ke root directory website.
                </p>
            </div>
        </div>

        {{-- AI & RAG Options --}}
        @include('components.ai-options')

        {{-- Submit --}}
        <button type="submit"
                class="btn-security w-full sm:w-auto justify-center"
                style="padding:0.75rem 2rem;font-size:0.9375rem;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Mulai Pemindaian
        </button>
    </form>
</div>
@endsection
