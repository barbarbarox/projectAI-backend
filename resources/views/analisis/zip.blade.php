@extends('layouts.app')
@section('title', 'Analisis ZIP — RedSim')
@section('header', 'Analisis ZIP')
@section('subheader', 'Unggah file ZIP proyek untuk analisis keamanan')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('analisis.zip') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
            <label for="file_zip" class="block text-sm font-medium text-[#94a3b8] mb-4">File ZIP Proyek</label>
            <div class="border-2 border-dashed border-[#1e2d4a] rounded-xl p-8 text-center hover:border-[#00d4ff]/30 transition-colors relative" x-data="{ fileName: '' }">
                <svg class="w-12 h-12 text-[#1e2d4a] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <p class="text-sm text-[#94a3b8] mb-2" x-text="fileName || 'Klik untuk memilih atau seret file ZIP'"></p>
                <p class="text-xs text-[#64748b]">Maksimal {{ env('MAX_FILE_SIZE_MB', 50) }}MB</p>
                <input type="file" id="file_zip" name="file_zip" accept=".zip" required
                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                       @change="fileName = $event.target.files[0]?.name || ''">
            </div>
        </div>

        {{-- AI & RAG Options --}}
        @include('components.ai-options')

        <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300">
             Mulai Analisis
        </button>
    </form>
</div>
@endsection
