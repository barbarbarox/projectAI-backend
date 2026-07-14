@extends('layouts.app')
@section('title', 'Verifikasi Domain — RedSim')
@section('header', 'Verifikasi Kepemilikan Domain')
@section('subheader', 'Upload file verifikasi ke website Anda')

@section('content')
<div class="max-w-2xl space-y-6">
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
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Download File Verifikasi
        </a>
    </div>

    {{-- Step 2: Upload Instructions --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-[#7c3aed]/20 flex items-center justify-center text-sm font-bold text-[#7c3aed]">2</div>
            <h3 class="font-semibold">Upload ke Website Anda</h3>
        </div>
        <p class="text-sm text-[#94a3b8] mb-3">Upload file tersebut ke root directory website sehingga bisa diakses melalui:</p>
        <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-4">
            <code class="text-sm text-[#00d4ff] break-all">https://{{ $dv->domain }}/{{ $dv->nama_file }}</code>
        </div>
        <div class="mt-4 p-3 rounded-lg bg-yellow-500/5 border border-yellow-500/20">
            <p class="text-xs text-yellow-400/80 flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Token berlaku sampai {{ $dv->expires_at->format('d M Y H:i') }} WIB</p>
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
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Saya Sudah Upload — Verifikasi Sekarang
            </button>
        </form>
    </div>
</div>
@endsection
