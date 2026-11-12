@extends('layouts.app')
@section('title', 'Analisis Log — RedSim')
@section('header', 'Analisis Log')
@section('subheader', 'Upload atau tempel log server untuk analisis keamanan berbasis AI')
@section('page_feature', 'analisis-log')

@section('content')
<div class="max-w-3xl" x-data="{ tab: 'file' }">
    <form method="POST" action="{{ route('analisis.log') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Tab Selector --}}
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-slate-400">Sumber Log</p>
                <button type="button"
                        @click="$dispatch('open-help', { feature: 'analisis-log' })"
                        class="w-6 h-6 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center transition-colors group"
                        title="Bantuan Analisis Log">
                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>
            </div>

            <div class="flex gap-2 mb-6">
                <button type="button" @click="tab = 'file'"
                        :class="tab === 'file' ? 'bg-[#00d4ff]/10 text-[#00d4ff] border-[#00d4ff]/30' : 'text-[#94a3b8] border-[#1e2d4a] hover:bg-[#1e2d4a]/50'"
                        class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium border transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Upload File Log
                </button>
                <button type="button" @click="tab = 'teks'"
                        :class="tab === 'teks' ? 'bg-[#00d4ff]/10 text-[#00d4ff] border-[#00d4ff]/30' : 'text-[#94a3b8] border-[#1e2d4a] hover:bg-[#1e2d4a]/50'"
                        class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium border transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Tempel Teks Log
                </button>
            </div>

            <input type="hidden" name="input_mode" :value="tab">

            {{-- Tab 1: Upload File --}}
            <div x-show="tab === 'file'" x-transition>
                <label class="block">
                    <span class="text-sm text-slate-400">File Log</span>
                    <input type="file" name="log_file" accept=".log,.txt,.csv"
                           class="mt-2 block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-[#1e2d4a] file:text-slate-200 hover:file:bg-[#2a3a5c]">
                </label>
                <p class="mt-2 text-xs text-slate-500">Format: .log, .txt, .csv — Maks. 10MB</p>
                <div class="mt-3 p-3 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a]">
                    <p class="text-xs text-slate-500 font-medium mb-1">Format yang didukung:</p>
                    <div class="flex flex-wrap gap-1 text-[10px]">
                        <span class="px-2 py-0.5 rounded bg-[#1e2d4a] text-slate-400">Apache Access</span>
                        <span class="px-2 py-0.5 rounded bg-[#1e2d4a] text-slate-400">Nginx</span>
                        <span class="px-2 py-0.5 rounded bg-[#1e2d4a] text-slate-400">Laravel</span>
                        <span class="px-2 py-0.5 rounded bg-[#1e2d4a] text-slate-400">auth.log</span>
                        <span class="px-2 py-0.5 rounded bg-[#1e2d4a] text-slate-400">syslog</span>
                        <span class="px-2 py-0.5 rounded bg-[#1e2d4a] text-slate-400">Windows Event</span>
                    </div>
                </div>
            </div>

            {{-- Tab 2: Paste Teks --}}
            <div x-show="tab === 'teks'" x-transition>
                <label class="block">
                    <span class="text-sm text-slate-400">Teks Log</span>
                    <textarea name="log_teks" rows="15"
                              class="mt-2 w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white text-xs font-mono placeholder-[#475569] focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none resize-y"
                              placeholder="Tempel isi log di sini...&#10;&#10;Contoh Apache:&#10;192.168.1.100 - - [15/Jan/2024:10:30:45 +0700] &quot;GET /admin HTTP/1.1&quot; 403 4897"></textarea>
                </label>
            </div>
        </div>

        {{-- Tipe Log --}}
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
            <label for="tipe_log" class="block text-sm font-medium text-slate-400 mb-2">Tipe Log</label>
            <select name="tipe_log" id="tipe_log"
                    class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:ring-1 focus:ring-[#00d4ff]/50 focus:outline-none">
                <option value="auto" selected>Deteksi Otomatis</option>
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

        <button type="submit"
                class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all duration-300 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Mulai Analisis Log
        </button>
    </form>
</div>
@endsection
