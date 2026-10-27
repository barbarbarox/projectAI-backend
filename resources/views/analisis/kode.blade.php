@extends('layouts.app')
@section('title', 'Analisis Kode — RedSim')
@section('header', 'Analisis Kode')
@section('subheader', 'Tempel kode sumber untuk analisis kerentanan keamanan')

@section('content')
<div class="max-w-4xl">
    <form method="POST" action="{{ route('analisis.kode') }}" class="space-y-6">
        @csrf
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
            <div class="flex items-center justify-between mb-4">
                <label for="bahasa" class="block text-sm font-medium text-[#94a3b8]">Bahasa Pemrograman</label>
                <button type="button"
                        @click="$dispatch('open-help', { feature: 'analisis-kode' })"
                        class="w-6 h-6 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center transition-colors group"
                        title="Bantuan Analisis Kode">
                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>
            </div>
            <div class="mb-4">
                <select id="bahasa" name="bahasa" class="w-full md:w-64 px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none">
                    <option value="php">PHP</option>
                    <option value="javascript">JavaScript</option>
                    <option value="python">Python</option>
                    <option value="java">Java</option>
                    <option value="go">Go</option>
                    <option value="ruby">Ruby</option>
                </select>
            </div>
            <div>
                <label for="kode" class="block text-sm font-medium text-[#94a3b8] mb-2">Kode Sumber</label>
                <textarea id="kode" name="kode" rows="18" required
                    class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white font-mono text-sm placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none resize-y"
                    placeholder="Tempel kode sumber Anda di sini...">{{ old('kode') }}</textarea>
            </div>
        </div>

        {{-- AI & RAG Options --}}
        @include('components.ai-options')

        <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300 hover:-translate-y-0.5 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Mulai Analisis
        </button>
    </form>
</div>
@endsection
