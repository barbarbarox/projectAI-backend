@extends('layouts.app')
@section('title', 'Pemindaian URL — RedSim')
@section('header', 'Pemindaian URL')
@section('subheader', 'Masukkan URL untuk analisis keamanan')

@section('content')
<div class="max-w-2xl" x-data="{ mode: 'biasa' }">
    <form method="POST" action="{{ route('analisis.url') }}" class="space-y-6">
        @csrf
        {{-- URL Input --}}
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
            <label for="url" class="block text-sm font-medium text-[#94a3b8] mb-2">URL Target</label>
            <input type="url" id="url" name="url" value="{{ old('url') }}" required
                   class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none"
                   placeholder="https://contoh.com">
            <p class="mt-2 text-xs text-[#64748b]">Masukkan URL lengkap termasuk https://</p>
        </div>

        {{-- Mode Selection --}}
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
            <p class="text-sm font-medium text-[#94a3b8] mb-4">Mode Pemindaian</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Mode 1: Scan Biasa --}}
                <label class="relative cursor-pointer" @click="mode = 'biasa'">
                    <input type="radio" name="mode_scan" value="biasa" class="peer sr-only" checked x-bind:checked="mode === 'biasa'">
                    <div class="rounded-xl border-2 p-4 transition-all duration-200 peer-checked:border-[#00d4ff] peer-checked:bg-[#00d4ff]/5 border-[#1e2d4a] hover:border-[#1e2d4a]/80">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-[#00d4ff]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <h4 class="font-semibold text-sm">Scan Biasa</h4>
                        </div>
                        <p class="text-xs text-[#64748b] leading-relaxed">Pemindaian cepat menggunakan VirusTotal & URLScan. Tidak butuh verifikasi kepemilikan.</p>
                        <div class="mt-3 flex flex-wrap gap-1">
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-[#00d4ff]/10 text-[#00d4ff]">Reputasi Domain</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-[#00d4ff]/10 text-[#00d4ff]">SSL Check</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-[#00d4ff]/10 text-[#00d4ff]">Security Headers</span>
                        </div>
                    </div>
                </label>

                {{-- Mode 2: Scan Intens --}}
                <label class="relative cursor-pointer" @click="mode = 'intens'">
                    <input type="radio" name="mode_scan" value="intens" class="peer sr-only" x-bind:checked="mode === 'intens'">
                    <div class="rounded-xl border-2 p-4 transition-all duration-200 peer-checked:border-[#7c3aed] peer-checked:bg-[#7c3aed]/5 border-[#1e2d4a] hover:border-[#1e2d4a]/80">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-[#7c3aed]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#7c3aed]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <h4 class="font-semibold text-sm">Scan Intens</h4>
                        </div>
                        <p class="text-xs text-[#64748b] leading-relaxed">Pemindaian mendalam. Membutuhkan verifikasi kepemilikan domain terlebih dahulu.</p>
                        <div class="mt-3 flex flex-wrap gap-1">
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-[#7c3aed]/10 text-[#7c3aed]">File Sensitif</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-[#7c3aed]/10 text-[#7c3aed]">IP & ASN</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-[#7c3aed]/10 text-[#7c3aed]">Analisis AI</span>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Info Mode Intens --}}
            <div x-show="mode === 'intens'" x-transition class="mt-4 p-3 rounded-lg bg-[#7c3aed]/5 border border-[#7c3aed]/20">
                <p class="text-xs text-[#7c3aed] flex items-start gap-1.5"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> <span>Scan Intens memerlukan verifikasi kepemilikan domain. Anda akan diminta mengupload file HTML ke root directory website.</span></p>
            </div>
        </div>

        {{-- AI & RAG Options --}}
        @include('components.ai-options')

        <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300">
            <div class="flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg> Mulai Pemindaian</div>
        </button>
    </form>
</div>
@endsection
