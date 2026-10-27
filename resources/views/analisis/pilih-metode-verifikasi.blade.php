@extends('layouts.app')
@section('title', 'Pilih Metode Verifikasi — RedSim')
@section('header', 'Verifikasi Kepemilikan Domain')
@section('subheader', 'Pilih cara untuk membuktikan bahwa Anda adalah pemilik website ini')

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- Info domain --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-5 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-[#00d4ff]/10 border border-[#00d4ff]/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
        </div>
        <div>
            <p class="text-xs text-[#64748b] mb-0.5">Domain yang akan diverifikasi</p>
            <p class="font-mono font-semibold text-[#00d4ff]">{{ $domain }}</p>
        </div>
    </div>

    {{-- Penjelasan --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-5">
        <p class="text-sm text-[#94a3b8]">
            Verifikasi kepemilikan diperlukan untuk melindungi website milik orang lain dari pemindaian intens tanpa izin.
            Pilih metode yang paling mudah Anda lakukan berdasarkan akses yang Anda miliki.
        </p>
    </div>

    {{-- Grid Pilihan Metode --}}
    <form method="POST" action="{{ route('analisis.url.generate-verifikasi') }}" id="form-pilih-metode">
        @csrf
        <input type="hidden" name="url" value="{{ $url }}">
        <input type="hidden" name="metode" id="input-metode" value="">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- 1. DNS TXT Record --}}
            <button type="button" onclick="pilihMetode('dns_txt')"
                class="metode-card text-left rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-5 hover:border-[#7c3aed]/60 hover:bg-[#7c3aed]/5 transition-all duration-300 cursor-pointer group"
                id="card-dns_txt">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-[#7c3aed]/10 border border-[#7c3aed]/20 flex items-center justify-center flex-shrink-0 group-hover:bg-[#7c3aed]/20 transition-colors">
                        <svg class="w-5 h-5 text-[#7c3aed]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-semibold text-sm">DNS TXT Record</h3>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-green-500/10 text-green-400 border border-green-500/20">Paling Aman</span>
                        </div>
                        <p class="text-xs text-[#64748b] leading-relaxed">Tambahkan TXT record pada konfigurasi DNS domain Anda. Cocok untuk pemilik domain penuh.</p>
                        <div class="mt-3 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs text-[#64748b]">Propagasi DNS bisa 1–48 jam</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3 h-0.5 rounded-full bg-gradient-to-r from-[#7c3aed] to-transparent opacity-0 group-hover:opacity-100 transition-opacity" id="bar-dns_txt"></div>
            </button>

            {{-- 2. Meta Tag HTML --}}
            <button type="button" onclick="pilihMetode('meta_tag')"
                class="metode-card text-left rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-5 hover:border-[#00d4ff]/60 hover:bg-[#00d4ff]/5 transition-all duration-300 cursor-pointer group"
                id="card-meta_tag">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-[#00d4ff]/10 border border-[#00d4ff]/20 flex items-center justify-center flex-shrink-0 group-hover:bg-[#00d4ff]/20 transition-colors">
                        <svg class="w-5 h-5 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-semibold text-sm">Meta Tag HTML</h3>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-[#00d4ff]/10 text-[#00d4ff] border border-[#00d4ff]/20">Mudah</span>
                        </div>
                        <p class="text-xs text-[#64748b] leading-relaxed">Tambahkan tag meta khusus pada halaman utama website. Cocok untuk developer atau pengguna CMS.</p>
                        <div class="mt-3 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span class="text-xs text-[#64748b]">Instan, tanpa menunggu propagasi</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3 h-0.5 rounded-full bg-gradient-to-r from-[#00d4ff] to-transparent opacity-0 group-hover:opacity-100 transition-opacity" id="bar-meta_tag"></div>
            </button>

            {{-- 3. Email Domain --}}
            <button type="button" onclick="pilihMetode('email_domain')"
                class="metode-card text-left rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-5 hover:border-[#f59e0b]/60 hover:bg-[#f59e0b]/5 transition-all duration-300 cursor-pointer group"
                id="card-email_domain">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-[#f59e0b]/10 border border-[#f59e0b]/20 flex items-center justify-center flex-shrink-0 group-hover:bg-[#f59e0b]/20 transition-colors">
                        <svg class="w-5 h-5 text-[#f59e0b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-semibold text-sm">Email Domain</h3>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-[#f59e0b]/10 text-[#f59e0b] border border-[#f59e0b]/20">Tanpa Akses Server</span>
                        </div>
                        <p class="text-xs text-[#64748b] leading-relaxed">Terima kode verifikasi melalui email yang menggunakan domain Anda (contoh: admin@domain.com).</p>
                        <div class="mt-3 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs text-[#64748b]">Tidak perlu mengubah file atau DNS</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3 h-0.5 rounded-full bg-gradient-to-r from-[#f59e0b] to-transparent opacity-0 group-hover:opacity-100 transition-opacity" id="bar-email_domain"></div>
            </button>

            {{-- 4. Upload File HTML --}}
            <button type="button" onclick="pilihMetode('file_upload')"
                class="metode-card text-left rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-5 hover:border-[#10b981]/60 hover:bg-[#10b981]/5 transition-all duration-300 cursor-pointer group"
                id="card-file_upload">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-[#10b981]/10 border border-[#10b981]/20 flex items-center justify-center flex-shrink-0 group-hover:bg-[#10b981]/20 transition-colors">
                        <svg class="w-5 h-5 text-[#10b981]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-semibold text-sm">Upload File HTML</h3>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-[#10b981]/10 text-[#10b981] border border-[#10b981]/20">Klasik</span>
                        </div>
                        <p class="text-xs text-[#64748b] leading-relaxed">Download file HTML verifikasi dan upload ke root directory hosting Anda.</p>
                        <div class="mt-3 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span class="text-xs text-[#64748b]">Memerlukan akses FTP / hosting panel</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3 h-0.5 rounded-full bg-gradient-to-r from-[#10b981] to-transparent opacity-0 group-hover:opacity-100 transition-opacity" id="bar-file_upload"></div>
            </button>

        </div>

        {{-- Tombol Lanjutkan --}}
        <div class="mt-6">
            <button type="submit" id="btn-lanjutkan"
                class="w-full px-6 py-3.5 rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#00d4ff] text-white font-semibold transition-all duration-300 flex items-center justify-center gap-2 opacity-40 cursor-not-allowed"
                disabled>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                <span id="btn-teks">Pilih salah satu metode di atas</span>
            </button>
        </div>
    </form>

</div>

<script>
const metodeLabels = {
    dns_txt:      'Lanjutkan dengan DNS TXT Record',
    meta_tag:     'Lanjutkan dengan Meta Tag HTML',
    email_domain: 'Lanjutkan dengan Email Domain',
    file_upload:  'Lanjutkan dengan Upload File HTML',
};

const metodeColors = {
    dns_txt:      'border-[#7c3aed]',
    meta_tag:     'border-[#00d4ff]',
    email_domain: 'border-[#f59e0b]',
    file_upload:  'border-[#10b981]',
};

let selected = null;

function pilihMetode(metode) {
    // Reset semua kartu
    document.querySelectorAll('.metode-card').forEach(card => {
        card.classList.remove('ring-2', 'ring-[#7c3aed]', 'ring-[#00d4ff]', 'ring-[#f59e0b]', 'ring-[#10b981]');
        card.classList.remove('border-[#7c3aed]', 'border-[#00d4ff]', 'border-[#f59e0b]', 'border-[#10b981]');
        card.classList.add('border-[#1e2d4a]');
    });

    // Highlight kartu yang dipilih
    const card = document.getElementById('card-' + metode);
    card.classList.remove('border-[#1e2d4a]');
    card.classList.add('ring-2');

    const ringColors = {
        dns_txt: 'ring-[#7c3aed]', meta_tag: 'ring-[#00d4ff]',
        email_domain: 'ring-[#f59e0b]', file_upload: 'ring-[#10b981]',
    };
    card.classList.add(ringColors[metode]);

    // Update form & tombol
    document.getElementById('input-metode').value = metode;
    selected = metode;

    const btn = document.getElementById('btn-lanjutkan');
    btn.disabled = false;
    btn.classList.remove('opacity-40', 'cursor-not-allowed');
    btn.classList.add('hover:shadow-lg', 'hover:shadow-[#7c3aed]/25');

    document.getElementById('btn-teks').textContent = metodeLabels[metode];
}
</script>
@endsection
