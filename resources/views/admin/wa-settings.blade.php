@extends('layouts.app')
@section('title', 'Konfigurasi WhatsApp — Admin RedSim')
@section('header', 'Konfigurasi WhatsApp')
@section('subheader', 'Kelola koneksi OpenWA Gateway dan nomor pengirim pesan')

@section('content')
<div class="space-y-6 max-w-3xl">

    {{-- Status Koneksi --}}
    <div class="rs-card rounded-xl p-6" x-data="{ status: null, loading: true }" x-init="
        fetch('{{ route('admin.wa-settings.status') }}')
            .then(r => r.json())
            .then(d => { status = d; loading = false; })
            .catch(() => { status = { status: 'error', message: 'Gagal mengecek status' }; loading = false; })
    ">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                     style="background:rgba(6,182,212,0.12);">
                    <svg class="w-5 h-5" style="color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Status OpenWA Gateway</h3>
                    <p class="text-xs" style="color:#64748b;">Koneksi real-time ke server WhatsApp</p>
                </div>
            </div>
            <div>
                <template x-if="loading">
                    <span class="px-3 py-1 rounded-full text-xs font-medium animate-pulse"
                          style="background:rgba(234,179,8,0.10);color:#fbbf24;">Mengecek...</span>
                </template>
                <template x-if="!loading && status && status.status === 'ready'">
                    <span class="px-3 py-1 rounded-full text-xs font-medium"
                          style="background:rgba(34,197,94,0.10);color:#4ade80;">● Terhubung</span>
                </template>
                <template x-if="!loading && status && status.status !== 'ready'">
                    <span class="px-3 py-1 rounded-full text-xs font-medium"
                          style="background:rgba(239,68,68,0.10);color:#f87171;">● Tidak Terhubung</span>
                </template>
            </div>
        </div>
        <template x-if="!loading && status">
            <div class="terminal-block text-xs" style="max-height:100px;overflow-y:auto;">
                <pre x-text="JSON.stringify(status, null, 2)"></pre>
            </div>
        </template>
    </div>

    {{-- Konfigurasi OpenWA --}}
    <form method="POST" action="{{ route('admin.wa-settings.update') }}" class="space-y-5">
        @csrf

        <div class="rs-card rounded-xl p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                     style="background:rgba(220,38,38,0.12);">
                    <svg class="w-5 h-5" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Konfigurasi Server</h3>
                    <p class="text-xs" style="color:#64748b;">Pengaturan koneksi ke OpenWA API Gateway</p>
                </div>
            </div>

            <div class="space-y-4">
                {{-- Base URL --}}
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color:#94a3b8;">
                        URL Server OpenWA <span class="text-red-400">*</span>
                    </label>
                    <input type="url" name="openwa_base_url"
                           value="{{ old('openwa_base_url', $settings['openwa_base_url'] ?? 'http://localhost:3000') }}"
                           required
                           class="w-full px-4 py-2.5 rounded-lg text-sm text-white placeholder-slate-600 transition-all duration-200"
                           style="background:#000;border:1px solid #1e293b;font-family:'JetBrains Mono',monospace;outline:none;"
                           placeholder="http://localhost:3000"
                           onfocus="this.style.borderColor='rgba(220,38,38,0.5)';this.style.boxShadow='0 0 0 3px rgba(220,38,38,0.1)'"
                           onblur="this.style.borderColor='#1e293b';this.style.boxShadow='none'">
                    <p class="mt-1 text-[10px]" style="color:#475569;">URL dimana OpenWA Gateway berjalan</p>
                    @error('openwa_base_url')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- API Key --}}
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color:#94a3b8;">
                        API Key
                    </label>
                    <input type="password" name="openwa_api_key"
                           value="{{ old('openwa_api_key', $settings['openwa_api_key'] ?? '') }}"
                           class="w-full px-4 py-2.5 rounded-lg text-sm text-white placeholder-slate-600 transition-all duration-200"
                           style="background:#000;border:1px solid #1e293b;font-family:'JetBrains Mono',monospace;outline:none;"
                           placeholder="Masukkan API Key OpenWA"
                           onfocus="this.style.borderColor='rgba(220,38,38,0.5)';this.style.boxShadow='0 0 0 3px rgba(220,38,38,0.1)'"
                           onblur="this.style.borderColor='#1e293b';this.style.boxShadow='none'">
                    <p class="mt-1 text-[10px]" style="color:#475569;">API Key dari dashboard OpenWA (role: operator)</p>
                    @error('openwa_api_key')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Session ID --}}
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color:#94a3b8;">
                        Session ID <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="openwa_session_id"
                           value="{{ old('openwa_session_id', $settings['openwa_session_id'] ?? 'default') }}"
                           required
                           class="w-full px-4 py-2.5 rounded-lg text-sm text-white placeholder-slate-600 transition-all duration-200"
                           style="background:#000;border:1px solid #1e293b;font-family:'JetBrains Mono',monospace;outline:none;"
                           placeholder="default"
                           onfocus="this.style.borderColor='rgba(220,38,38,0.5)';this.style.boxShadow='0 0 0 3px rgba(220,38,38,0.1)'"
                           onblur="this.style.borderColor='#1e293b';this.style.boxShadow='none'">
                    <p class="mt-1 text-[10px]" style="color:#475569;">Nama sesi WhatsApp yang digunakan di OpenWA</p>
                    @error('openwa_session_id')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nomor Pengirim --}}
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color:#94a3b8;">
                        Nomor WhatsApp Pengirim
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4" style="color:#22d3ee;" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                            </svg>
                        </div>
                        <input type="text" name="wa_sender_number"
                               value="{{ old('wa_sender_number', $settings['wa_sender_number'] ?? '') }}"
                               class="w-full pl-10 pr-4 py-2.5 rounded-lg text-sm text-white placeholder-slate-600 transition-all duration-200"
                               style="background:#000;border:1px solid #1e293b;font-family:'JetBrains Mono',monospace;outline:none;"
                               placeholder="628xxxxxxxxxx"
                               onfocus="this.style.borderColor='rgba(6,182,212,0.5)';this.style.boxShadow='0 0 0 3px rgba(6,182,212,0.1)'"
                               onblur="this.style.borderColor='#1e293b';this.style.boxShadow='none'">
                    </div>
                    <p class="mt-1 text-[10px]" style="color:#475569;">Nomor WA yang terhubung ke OpenWA (format: 628xxx)</p>
                    @error('wa_sender_number')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Submit --}}
            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="btn-security">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Konfigurasi
                </button>
            </div>
        </div>
    </form>

    {{-- Tes Kirim Pesan --}}
    <div class="rs-card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                 style="background:rgba(6,182,212,0.12);">
                <svg class="w-5 h-5" style="color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Tes Kirim Pesan</h3>
                <p class="text-xs" style="color:#64748b;">Kirim pesan percobaan untuk memastikan koneksi berfungsi</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.wa-settings.test') }}" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label class="block text-xs font-medium mb-1.5" style="color:#94a3b8;">Nomor Tujuan</label>
                <input type="text" name="test_phone" required
                       class="w-full px-4 py-2.5 rounded-lg text-sm text-white placeholder-slate-600 transition-all duration-200"
                       style="background:#000;border:1px solid #1e293b;font-family:'JetBrains Mono',monospace;outline:none;"
                       placeholder="628xxxxxxxxxx"
                       onfocus="this.style.borderColor='rgba(6,182,212,0.5)';this.style.boxShadow='0 0 0 3px rgba(6,182,212,0.1)'"
                       onblur="this.style.borderColor='#1e293b';this.style.boxShadow='none'">
            </div>
            <button type="submit" class="btn-ai" style="padding:0.625rem 1.25rem;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Kirim Tes
            </button>
        </form>
    </div>

    {{-- Panduan Setup --}}
    <div class="rs-card rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-cyan-500/10 border border-cyan-500/20 text-cyan-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </span>
            <h3 class="text-sm font-semibold text-white">Panduan Setup OpenWA</h3>
        </div>

        <div class="space-y-3 text-xs leading-relaxed" style="color:#94a3b8;">
            <div class="flex gap-3">
                <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-[10px] font-bold"
                      style="background:rgba(220,38,38,0.12);color:#ef4444;">1</span>
                <div>
                    <p class="font-medium text-white mb-0.5">Clone & Jalankan OpenWA</p>
                    <div class="terminal-block mt-1 text-[11px]">git clone https://github.com/rmyndharis/OpenWA.git
cd OpenWA
docker compose up -d</div>
                </div>
            </div>
            <div class="flex gap-3">
                <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-[10px] font-bold"
                      style="background:rgba(220,38,38,0.12);color:#ef4444;">2</span>
                <div>
                    <p class="font-medium text-white mb-0.5">Buka Dashboard OpenWA</p>
                    <p>Akses <code class="text-green-400 bg-black px-1 rounded font-mono">http://localhost:3000</code> lalu buat sesi WhatsApp baru dan scan QR code.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-[10px] font-bold"
                      style="background:rgba(220,38,38,0.12);color:#ef4444;">3</span>
                <div>
                    <p class="font-medium text-white mb-0.5">Buat API Key</p>
                    <p>Di dashboard OpenWA, buat API key dengan role <code class="text-cyan-400 bg-black px-1 rounded font-mono">operator</code>. Salin dan paste di form di atas.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-[10px] font-bold"
                      style="background:rgba(220,38,38,0.12);color:#ef4444;">4</span>
                <div>
                    <p class="font-medium text-white mb-0.5">Tes Koneksi</p>
                    <p>Gunakan form "Tes Kirim Pesan" di atas untuk memastikan semuanya berfungsi.</p>
                </div>
            </div>
        </div>

        <div class="mt-4 p-3 rounded-lg flex items-start gap-2"
             style="background:rgba(234,179,8,0.06);border:1px solid rgba(234,179,8,0.2);">
            <svg class="w-4 h-4 shrink-0 mt-0.5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-xs leading-relaxed text-yellow-400/80">
                <strong class="text-yellow-400">Penting:</strong> Gunakan nomor WhatsApp khusus/dedicated.
                Jangan gunakan nomor pribadi utama karena ada risiko pembatasan dari WhatsApp jika mengirim pesan otomatis dalam jumlah besar.
            </p>
        </div>
    </div>
</div>
@endsection
