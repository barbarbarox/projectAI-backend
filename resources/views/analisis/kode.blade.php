@extends('layouts.app')
@section('title', 'Analisis Kode — RedSim')
@section('header', 'Analisis Kode')
@section('subheader', 'Tempel kode sumber untuk analisis kerentanan keamanan')

@section('content')
<div class="max-w-4xl">
    <form method="POST" action="{{ route('analisis.kode') }}" class="space-y-6">
        @csrf
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
            <div class="mb-4">
                <label for="bahasa" class="block text-sm font-medium text-[#94a3b8] mb-2">Bahasa Pemrograman</label>
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

        <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300 hover:-translate-y-0.5">
            🔍 Mulai Analisis
        </button>
    </form>
</div>
@endsection
