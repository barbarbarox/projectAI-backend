@extends('layouts.app')
@section('title', 'Analisis Log — RedSim')
@section('header', 'Analisis Log')
@section('subheader', 'Upload atau paste log untuk analisis keamanan AI')

@section('content')
<div class="max-w-3xl" x-data="{ tab: 'file' }">
    <form method="POST" action="{{ route('analisis.log') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Tab Selector --}}
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
            <p class="text-sm font-medium text-[#94a3b8] mb-4">Sumber Log</p>
            <div class="flex gap-2 mb-6">
                <button type="button" @click="tab = 'file'" :class="tab === 'file' ? 'bg-[#00d4ff]/10 text-[#00d4ff] border-[#00d4ff]/30' : 'text-[#94a3b8] border-[#1e2d4a] hover:bg-[#1e2d4a]/50'" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium border transition-all">
                    📁 Upload File Log
                </button>
                <button type="button" @click="tab = 'teks'" :class="tab === 'teks' ? 'bg-[#00d4ff]/10 text-[#00d4ff] border-[#00d4ff]/30' : 'text-[#94a3b8] border-[#1e2d4a] hover:bg-[#1e2d4a]/50'" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium border transition-all">
                    📋 Paste Teks Log
                </button>
            </div>

            <input type="hidden" name="input_mode" :value="tab">

            {{-- Tab 1: Upload File --}}
            <div x-show="tab === 'file'" x-transition>
                <label class="block">
                    <span class="text-sm text-[#94a3b8]">File Log</span>
                    <input type="file" name="log_file" accept=".log,.txt,.csv" class="mt-2 block w-full text-sm text-[#94a3b8] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-[#1e2d4a] file:text-[#e2e8f0] hover:file:bg-[#2a3a5c]">
                </label>
                <p class="mt-2 text-xs text-[#64748b]">Format: .log, .txt, .csv — Maks. 10MB</p>
                <div class="mt-3 p-3 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a]">
                    <p class="text-xs text-[#64748b] font-medium mb-1">Format yang didukung:</p>
                    <div class="flex flex-wrap gap-1 text-[10px]">
                        <span class="px-2 py-0.5 rounded bg-[#1e2d4a] text-[#94a3b8]">Apache Access</span>
                        <span class="px-2 py-0.5 rounded bg-[#1e2d4a] text-[#94a3b8]">Nginx</span>
                        <span class="px-2 py-0.5 rounded bg-[#1e2d4a] text-[#94a3b8]">Laravel</span>
                        <span class="px-2 py-0.5 rounded bg-[#1e2d4a] text-[#94a3b8]">auth.log</span>
                        <span class="px-2 py-0.5 rounded bg-[#1e2d4a] text-[#94a3b8]">syslog</span>
                        <span class="px-2 py-0.5 rounded bg-[#1e2d4a] text-[#94a3b8]">Windows Event</span>
                    </div>
                </div>
            </div>

            {{-- Tab 2: Paste Teks --}}
            <div x-show="tab === 'teks'" x-transition>
                <label class="block">
                    <span class="text-sm text-[#94a3b8]">Teks Log</span>
                    <textarea name="log_teks" rows="15" class="mt-2 w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white text-xs font-mono placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none resize-y" placeholder="Paste isi log di sini...&#10;&#10;Contoh Apache:&#10;192.168.1.100 - - [15/Jan/2024:10:30:45 +0700] &quot;GET /admin HTTP/1.1&quot; 403 4897 &quot;-&quot; &quot;Mozilla/5.0&quot;"></textarea>
                </label>
            </div>
        </div>

        {{-- Tipe Log --}}
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
            <label for="tipe_log" class="block text-sm font-medium text-[#94a3b8] mb-2">Tipe Log</label>
            <select name="tipe_log" id="tipe_log" class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none">
                <option value="auto" selected>🔍 Deteksi Otomatis</option>
                <option value="Apache Access Log">Apache Access Log</option>
                <option value="Nginx Access Log">Nginx Access Log / Error Log</option>
                <option value="Laravel Application Log">Laravel Application Log</option>
                <option value="Linux auth.log / syslog">Linux auth.log / syslog</option>
                <option value="Windows Event Log">Windows Event Log (format teks)</option>
                <option value="Log Kustom">Log Kustom</option>
            </select>
        </div>

        {{-- AI & RAG Options --}}
        @include('components.ai-options')

        <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300">
            🔍 Mulai Analisis Log
        </button>
    </form>
</div>
@endsection
