@extends('layouts.app')
@section('title', 'Verifikasi Domain — RedSim')
@section('header', 'Verifikasi Kepemilikan Domain')

@php
    $metode = $dv->metode_verifikasi;
    $metodeLabel = [
        'dns_txt'      => 'DNS TXT Record',
        'meta_tag'     => 'Meta Tag HTML',
        'email_domain' => 'Email Domain',
        'file_upload'  => 'Upload File HTML',
    ][$metode] ?? 'Verifikasi';
@endphp

@section('subheader', 'Metode: ' . $metodeLabel . ' — ' . $dv->domain)

@section('content')
<div class="max-w-2xl space-y-5">

    @if(session('success'))
    <div class="rounded-xl bg-green-500/10 border border-green-500/30 p-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-green-400">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="rounded-xl bg-red-500/10 border border-red-500/30 p-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-red-400">{{ session('error') }}</p>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{--  METODE: DNS TXT RECORD                                      --}}
    {{-- ============================================================ --}}
    @if($metode === 'dns_txt')

    {{-- Step 1: Tambah TXT Record --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-sm font-bold text-[#7c3aed]">1</div>
            <h3 class="font-semibold">Buka Panel DNS Domain Anda</h3>
        </div>
        <p class="text-sm text-[#94a3b8] mb-4">Masuk ke panel kontrol domain (Cloudflare, cPanel, Namecheap, dll) dan tambahkan TXT record berikut:</p>
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 space-y-3">
            <div>
                <p class="text-xs text-[#64748b] mb-1">Type</p>
                <code class="text-sm font-mono text-[#10b981]">TXT</code>
            </div>
            <div>
                <p class="text-xs text-[#64748b] mb-1">Name / Host</p>
                <code class="text-sm font-mono text-[#00d4ff]">@ (atau {{ $dv->domain }})</code>
            </div>
            <div>
                <p class="text-xs text-[#64748b] mb-1">Value / Content</p>
                <div class="flex items-center gap-2">
                    <code class="text-xs font-mono text-[#e2e8f0] bg-[#0a0e1a] border border-[#1e2d4a] rounded-lg px-3 py-2 flex-1 break-all" id="dns-value">{{ $dv->token }}</code>
                    <button onclick="copyText('dns-value')" class="flex-shrink-0 p-2 rounded-lg bg-[#1e2d4a] hover:bg-[#7c3aed]/30 transition-colors" title="Salin">
                        <svg class="w-4 h-4 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                </div>
            </div>
            <div>
                <p class="text-xs text-[#64748b] mb-1">TTL</p>
                <code class="text-sm font-mono text-[#94a3b8]">3600 (atau Auto)</code>
            </div>
        </div>
        <div class="mt-4 p-3 rounded-lg bg-yellow-500/5 border border-yellow-500/20">
            <p class="text-xs text-yellow-400/80 flex items-start gap-1.5">
                <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Perubahan DNS memerlukan waktu propagasi 1–48 jam. Token berlaku hingga <strong>{{ $dv->expires_at->format('d M Y H:i') }} WIB</strong>.
            </p>
        </div>
    </div>

    {{-- Step 2: Verifikasi --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-sm font-bold text-[#7c3aed]">2</div>
            <h3 class="font-semibold">Verifikasi Sekarang</h3>
        </div>
        <p class="text-sm text-[#94a3b8] mb-4">Setelah TXT record ditambahkan dan propagasi selesai, klik tombol di bawah.</p>
        <form method="POST" action="{{ route('analisis.url.proses-verifikasi', $dv->id) }}">
            @csrf
            <input type="hidden" name="url" value="https://{{ $dv->domain }}">
            <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Cek DNS & Verifikasi Sekarang
            </button>
        </form>
    </div>

    {{-- ============================================================ --}}
    {{--  METODE: META TAG HTML                                        --}}
    {{-- ============================================================ --}}
    @elseif($metode === 'meta_tag')

    {{-- Step 1: Salin Meta Tag --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-sm font-bold text-[#7c3aed]">1</div>
            <h3 class="font-semibold">Salin Meta Tag Berikut</h3>
        </div>
        <p class="text-sm text-[#94a3b8] mb-4">Salin tag HTML di bawah ini dan tempelkan di dalam bagian <code class="text-[#00d4ff]">&lt;head&gt;</code> halaman utama (homepage) website Anda.</p>
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4">
            <div class="flex items-start gap-2">
                <code class="text-xs font-mono text-[#e2e8f0] flex-1 break-all leading-relaxed" id="meta-tag-value">&lt;meta name="redsim-verification" content="{{ $dv->token }}"&gt;</code>
                <button onclick="copyText('meta-tag-value')" class="flex-shrink-0 p-2 rounded-lg bg-[#1e2d4a] hover:bg-[#7c3aed]/30 transition-colors" title="Salin">
                    <svg class="w-4 h-4 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Step 2: Instruksi Penempatan --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-sm font-bold text-[#7c3aed]">2</div>
            <h3 class="font-semibold">Tambahkan ke Halaman Utama</h3>
        </div>
        <p class="text-sm text-[#94a3b8] mb-3">Contoh penempatan yang benar:</p>
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4">
            <pre class="text-xs font-mono text-[#94a3b8] overflow-x-auto"><span class="text-[#64748b]">&lt;!DOCTYPE html&gt;</span>
<span class="text-[#64748b]">&lt;html&gt;</span>
<span class="text-[#00d4ff]">&lt;head&gt;</span>
  <span class="text-[#64748b]">&lt;title&gt;Website Anda&lt;/title&gt;</span>
  <span class="text-green-400">&lt;meta name="redsim-verification" content="{{ Str::limit($dv->token, 30, '...') }}"&gt;</span>  <span class="text-yellow-500">← tambahkan di sini</span>
<span class="text-[#00d4ff]">&lt;/head&gt;</span>
<span class="text-[#64748b]">&lt;body&gt;...&lt;/body&gt;</span>
<span class="text-[#64748b]">&lt;/html&gt;</span></pre>
        </div>
        <div class="mt-4 p-3 rounded-lg bg-yellow-500/5 border border-yellow-500/20">
            <p class="text-xs text-yellow-400/80">⏰ Token berlaku hingga <strong>{{ $dv->expires_at->format('d M Y H:i') }} WIB</strong>.</p>
        </div>
    </div>

    {{-- Step 3: Verifikasi --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-sm font-bold text-[#7c3aed]">3</div>
            <h3 class="font-semibold">Verifikasi Sekarang</h3>
        </div>
        <p class="text-sm text-[#94a3b8] mb-4">Setelah meta tag ditambahkan dan halaman ter-publish, klik tombol di bawah.</p>
        <form method="POST" action="{{ route('analisis.url.proses-verifikasi', $dv->id) }}">
            @csrf
            <input type="hidden" name="url" value="https://{{ $dv->domain }}">
            <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Cek Meta Tag & Verifikasi Sekarang
            </button>
        </form>
    </div>

    {{-- ============================================================ --}}
    {{--  METODE: EMAIL DOMAIN                                         --}}
    {{-- ============================================================ --}}
    @elseif($metode === 'email_domain')

    {{-- Step 1: Kirim Kode ke Email --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-sm font-bold text-[#7c3aed]">1</div>
            <h3 class="font-semibold">Kirim Kode ke Email Domain Anda</h3>
        </div>
        <p class="text-sm text-[#94a3b8] mb-4">
            Masukkan alamat email yang menggunakan domain <strong class="text-[#00d4ff]">{{ $dv->domain }}</strong>.
            Kode verifikasi akan dikirim ke alamat tersebut.
        </p>
        <form method="POST" action="{{ route('analisis.url.kirim-email-verifikasi', $dv->id) }}" class="space-y-3">
            @csrf
            <div class="flex gap-2">
                <input type="email"
                    name="email_tujuan"
                    placeholder="admin@{{ $dv->domain }}"
                    value="{{ $dv->email_verifikasi ?? old('email_tujuan') }}"
                    class="flex-1 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] px-4 py-2.5 text-sm text-[#e2e8f0] placeholder-[#475569] focus:outline-none focus:border-[#7c3aed]/60 focus:ring-1 focus:ring-[#7c3aed]/30 transition-colors"
                    required>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#7c3aed] text-white text-sm font-medium hover:bg-[#6d28d9] transition-colors flex items-center gap-2 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Kirim Kode
                </button>
            </div>
            @error('email_tujuan')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
        </form>
        @if($dv->email_verifikasi)
        <div class="mt-3 p-3 rounded-lg bg-green-500/5 border border-green-500/20 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-xs text-green-400">Kode sudah dikirim ke <strong>{{ $dv->email_verifikasi }}</strong>. Periksa inbox (dan folder spam).</p>
        </div>
        @endif
    </div>

    {{-- Step 2: Masukkan Kode --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-sm font-bold text-[#7c3aed]">2</div>
            <h3 class="font-semibold">Masukkan Kode Verifikasi</h3>
        </div>
        <p class="text-sm text-[#94a3b8] mb-4">Masukkan kode 6 karakter yang Anda terima di email.</p>
        <form method="POST" action="{{ route('analisis.url.proses-verifikasi', $dv->id) }}" class="space-y-4">
            @csrf
            <input type="hidden" name="url" value="https://{{ $dv->domain }}">
            <input type="text"
                name="kode_verifikasi"
                placeholder="XXX-000"
                maxlength="7"
                class="w-full text-center rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] px-4 py-3 text-xl font-mono font-bold tracking-[0.4em] text-[#e2e8f0] placeholder-[#475569] focus:outline-none focus:border-[#7c3aed]/60 focus:ring-1 focus:ring-[#7c3aed]/30 uppercase transition-colors"
                style="letter-spacing: 0.4em;"
                oninput="this.value = this.value.toUpperCase()">
            @error('kode_verifikasi')
                <p class="text-xs text-red-400 text-center">{{ $message }}</p>
            @enderror
            <button type="submit" @if(!$dv->email_verifikasi) disabled @endif
                class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300 flex items-center justify-center gap-2 {{ !$dv->email_verifikasi ? 'opacity-40 cursor-not-allowed' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Verifikasi dengan Kode
            </button>
        </form>
        <div class="mt-4 p-3 rounded-lg bg-yellow-500/5 border border-yellow-500/20">
            <p class="text-xs text-yellow-400/80">⏰ Token berlaku hingga <strong>{{ $dv->expires_at->format('d M Y H:i') }} WIB</strong>.</p>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{--  METODE: FILE UPLOAD (default)                               --}}
    {{-- ============================================================ --}}
    @else

    {{-- Step 1: Download File --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-sm font-bold text-[#7c3aed]">1</div>
            <h3 class="font-semibold">Download File Verifikasi</h3>
        </div>
        <p class="text-sm text-[#94a3b8] mb-4">Download file HTML berikut dan upload ke root directory website Anda.</p>
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4 mb-4">
            <p class="text-xs text-[#64748b] mb-1">Nama file:</p>
            <code class="text-sm text-[#00d4ff]">{{ $dv->nama_file }}</code>
        </div>
        <a href="{{ route('analisis.url.download-verifikasi', $dv->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#7c3aed] text-white text-sm font-medium hover:bg-[#6d28d9] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download File Verifikasi
        </a>
    </div>

    {{-- Step 2: Upload Instructions --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-sm font-bold text-[#7c3aed]">2</div>
            <h3 class="font-semibold">Upload ke Website Anda</h3>
        </div>
        <p class="text-sm text-[#94a3b8] mb-3">Upload file ke root directory sehingga dapat diakses melalui:</p>
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4">
            <code class="text-sm text-[#00d4ff] break-all">https://{{ $dv->domain }}/{{ $dv->nama_file }}</code>
        </div>
        <div class="mt-4 p-3 rounded-lg bg-yellow-500/5 border border-yellow-500/20">
            <p class="text-xs text-yellow-400/80">⏰ Token berlaku hingga <strong>{{ $dv->expires_at->format('d M Y H:i') }} WIB</strong>.</p>
        </div>
    </div>

    {{-- Step 3: Verify --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-sm font-bold text-[#7c3aed]">3</div>
            <h3 class="font-semibold">Verifikasi Sekarang</h3>
        </div>
        <p class="text-sm text-[#94a3b8] mb-4">Setelah file diupload, klik tombol di bawah untuk memverifikasi.</p>
        <form method="POST" action="{{ route('analisis.url.proses-verifikasi', $dv->id) }}">
            @csrf
            <input type="hidden" name="url" value="https://{{ $dv->domain }}">
            <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Saya Sudah Upload — Verifikasi Sekarang
            </button>
        </form>
    </div>

    @endif

    {{-- Link ganti metode --}}
    <div class="text-center">
        <a href="{{ route('analisis.url.pilih-metode-verifikasi', ['url' => 'https://' . $dv->domain]) }}"
            class="text-sm text-[#64748b] hover:text-[#94a3b8] transition-colors underline underline-offset-4">
            ← Ganti metode verifikasi
        </a>
    </div>

</div>

<script>
function copyText(elementId) {
    const el = document.getElementById(elementId);
    const text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = el.nextElementSibling;
        if (btn) {
            btn.innerHTML = '<svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            setTimeout(() => {
                btn.innerHTML = '<svg class="w-4 h-4 text-[#94a3b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';
            }, 2000);
        }
    }).catch(() => {});
}
</script>
@endsection
