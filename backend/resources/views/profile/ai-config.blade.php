@extends('layouts.app')
@section('title', 'Pengaturan Model AI — RedSim')
@section('header', 'Pengaturan Model AI')
@section('subheader', 'Hubungkan model AI Anda sendiri untuk analisis keamanan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="rounded-xl bg-green-500/10 border border-green-500/30 p-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="text-sm text-green-400">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-xl bg-red-500/10 border border-red-500/30 p-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        <span class="text-sm text-red-400">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Status Saat Ini --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            Status AI Saat Ini
        </h3>

        @if($config)
            {{-- User punya config sendiri --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#7c3aed] to-[#ec4899] flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold">{{ $config->provider_label }}</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $config->method === 'oauth_google' ? 'bg-blue-500/20 text-blue-400' : 'bg-green-500/20 text-green-400' }}">{{ $config->method_label }}</span>
                        </div>
                        <p class="text-xs text-[#94a3b8] font-mono mt-0.5">{{ $config->selected_model }}</p>
                        <div class="flex items-center gap-3 mt-1">
                            <span class="text-[10px] text-[#64748b]">{{ $config->total_requests }} request</span>
                            @if($config->last_used_at)
                            <span class="text-[10px] text-[#64748b]">Terakhir: {{ $config->last_used_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('profile.ai.destroy') }}" onsubmit="return confirm('Yakin ingin menghapus konfigurasi AI Anda? Sistem akan menggunakan model default admin.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-lg bg-red-500/10 text-red-400 text-xs hover:bg-red-500/20 transition-colors border border-red-500/20">
                        <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus Config
                    </button>
                </form>
            </div>

            {{-- Model Selector (jika punya beberapa model) --}}
            @if(!empty($config->available_models) && count($config->available_models) > 1)
            <div class="mt-4 pt-4 border-t border-[#1e2d4a]">
                <label class="text-xs text-[#64748b] block mb-2">Ganti Model:</label>
                <div class="flex gap-2">
                    <select id="modelSelector" class="flex-1 bg-[#0a0e1a] border border-[#1e2d4a] rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-[#7c3aed]">
                        @foreach($config->available_models as $m)
                        <option value="{{ $m['id'] }}" {{ $config->selected_model === $m['id'] ? 'selected' : '' }}>{{ $m['name'] ?? $m['id'] }}</option>
                        @endforeach
                    </select>
                    <button onclick="updateModel()" class="px-4 py-2 rounded-lg bg-[#7c3aed] text-white text-xs font-semibold hover:bg-[#6d28d9] transition-colors">Simpan</button>
                    <button onclick="refreshModelList()" id="btnRefreshModels" class="px-3 py-2 rounded-lg bg-[#1e2d4a] text-[#94a3b8] text-xs hover:bg-[#2a3a5c] hover:text-white transition-colors" title="Refresh daftar model dari API">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                </div>
            </div>
            @endif

            {{-- Test All Models Button --}}
            @if(!empty($config->available_models) && count($config->available_models) > 0)
            <div class="mt-4 pt-4 border-t border-[#1e2d4a]">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <span class="text-xs font-semibold text-[#94a3b8]">Cek Model Aktif</span>
                        <p class="text-[10px] text-[#475569]">Test koneksi ke setiap model untuk melihat mana yang bisa digunakan saat ini.</p>
                    </div>
                    <button onclick="testAllModels()" id="btnTestModels" class="px-4 py-2 rounded-lg bg-[#1e2d4a] text-[#00d4ff] text-xs font-semibold hover:bg-[#2a3a5c] transition-colors whitespace-nowrap flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Cek Semua Model
                    </button>
                </div>

                {{-- Test Progress --}}
                <div id="testProgress" class="hidden">
                    <div class="flex items-center gap-3 py-4">
                        <div class="inline-block w-5 h-5 border-2 border-[#7c3aed] border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-xs text-[#94a3b8]" id="testProgressText">Menguji semua model... (ini membutuhkan waktu)</span>
                    </div>
                </div>

                {{-- Test Results --}}
                <div id="testResults" class="hidden space-y-2">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-[#94a3b8]" id="testSummary"></span>
                        <button onclick="removeFailedModels()" id="btnRemoveFailed" class="hidden px-3 py-1.5 rounded bg-red-500/10 text-red-400 text-[10px] font-semibold hover:bg-red-500/20 transition-colors border border-red-500/20">
                            Hapus Model Error
                        </button>
                    </div>
                    <div id="testResultsList" class="space-y-1.5 max-h-80 overflow-y-auto pr-1"></div>
                </div>
            </div>
            @endif

        @else
            {{-- User pakai default admin --}}
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#1e2d4a] flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#64748b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <span class="text-sm font-bold text-[#64748b]">Model Default Sistem (Admin)</span>
                    <p class="text-xs text-[#475569] mt-0.5">Anda menggunakan model AI yang disediakan admin. Hubungkan model AI Anda sendiri di bawah.</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Metode 1: Input API Key --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-1 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            Hubungkan via API Key
        </h3>
        <p class="text-xs text-[#64748b] mb-4">Masukkan API key dari provider AI pilihan Anda. Sistem akan otomatis mendeteksi provider dan menampilkan model yang tersedia.</p>

        <div class="space-y-4">
            <div>
                <label class="text-xs text-[#64748b] block mb-1.5">API Key</label>
                <div class="flex gap-2">
                    <input type="password" id="apiKeyInput" placeholder="sk-... / AIza... / gsk_... / nvapi-..." class="flex-1 bg-[#0a0e1a] border border-[#1e2d4a] rounded-lg px-4 py-2.5 text-sm text-white placeholder-[#475569] focus:outline-none focus:border-[#7c3aed] font-mono">
                    <button onclick="detectProvider()" id="btnDetect" class="px-5 py-2.5 rounded-lg bg-[#1e2d4a] text-[#00d4ff] text-xs font-semibold hover:bg-[#2a3a5c] transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Deteksi Provider
                    </button>
                </div>
                <p class="text-[10px] text-[#475569] mt-1.5">Didukung: Google AI Studio, OpenAI, Anthropic, NVIDIA NIM, Groq, OpenRouter</p>
            </div>

            {{-- Detection Result (hidden initially) --}}
            <div id="detectionResult" class="hidden">
                <div id="detectionLoading" class="hidden py-8 text-center">
                    <div class="inline-block w-6 h-6 border-2 border-[#7c3aed] border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs text-[#64748b] mt-2">Mendeteksi provider...</p>
                </div>
                <div id="detectionSuccess" class="hidden">
                    <div class="rounded-xl bg-green-500/5 border border-green-500/20 p-4 mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-sm text-green-400 font-semibold" id="detectedProviderLabel"></span>
                        </div>
                        <p class="text-xs text-[#64748b]" id="detectedModelCount"></p>
                    </div>
                    <form method="POST" action="{{ route('profile.ai.store') }}">
                        @csrf
                        <input type="hidden" name="api_key" id="formApiKey">
                        <input type="hidden" name="provider" id="formProvider">
                        <input type="hidden" name="available_models" id="formAvailableModels">
                        <div class="mb-4">
                            <label class="text-xs text-[#64748b] block mb-1.5">Pilih Model</label>
                            <select name="selected_model" id="modelSelect" class="w-full bg-[#0a0e1a] border border-[#1e2d4a] rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#7c3aed]"></select>
                        </div>
                        <div class="mb-4">
                            <label class="text-xs text-[#64748b] block mb-1.5">Label (opsional)</label>
                            <input type="text" name="label" placeholder="e.g. AI Pribadi Saya" class="w-full bg-[#0a0e1a] border border-[#1e2d4a] rounded-lg px-4 py-2.5 text-sm text-white placeholder-[#475569] focus:outline-none focus:border-[#7c3aed]">
                        </div>
                        <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#ec4899] text-white font-semibold text-sm hover:shadow-lg hover:shadow-[#7c3aed]/25 transition-all duration-300">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Konfigurasi
                        </button>
                    </form>
                </div>
                <div id="detectionError" class="hidden rounded-xl bg-red-500/5 border border-red-500/20 p-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span class="text-sm text-red-400" id="detectionErrorMsg"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Metode 2: OAuth Login --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-1 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
            Hubungkan via OAuth
        </h3>
        <p class="text-xs text-[#64748b] mb-4">Login dengan akun Google atau OpenAI Anda untuk mengakses model AI secara langsung.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Google OAuth --}}
            <a href="{{ route('profile.ai.oauth.google') }}" class="flex items-center gap-3 px-5 py-3.5 rounded-xl bg-white/5 border border-[#1e2d4a] hover:bg-white/10 hover:border-[#4285F4] transition-all duration-300 group">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                <div>
                    <span class="text-sm font-semibold text-white group-hover:text-[#4285F4] transition-colors">Google AI</span>
                    <p class="text-[10px] text-[#64748b]">Model Gemini • Ada Free Tier</p>
                </div>
            </a>

            {{-- OpenAI OAuth --}}
            <a href="{{ route('profile.ai.oauth.openai') }}" class="flex items-center gap-3 px-5 py-3.5 rounded-xl bg-white/5 border border-[#1e2d4a] hover:bg-white/10 hover:border-[#10a37f] transition-all duration-300 group">
                <div class="w-5 h-5 shrink-0 rounded-sm bg-[#10a37f] flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.667zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/></svg>
                </div>
                <div>
                    <span class="text-sm font-semibold text-white group-hover:text-[#10a37f] transition-colors">OpenAI</span>
                    <p class="text-[10px] text-[#64748b]">Model GPT & o-series • Berbayar</p>
                </div>
            </a>
        </div>

        <p class="text-[10px] text-[#475569] mt-3 flex items-start gap-1.5">
            <svg class="w-3 h-3 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>OpenAI memerlukan akun berbayar (API credit). Jika belum punya, gunakan API Key dari <a href="https://platform.openai.com/api-keys" target="_blank" class="text-[#10a37f] hover:underline">platform.openai.com</a> sebagai gantinya.</span>
        </p>
    </div>

    {{-- Info Box --}}
    <div class="rounded-xl bg-[#0f1629] border border-[#1e2d4a] p-5">
        <h4 class="text-xs font-semibold text-[#64748b] mb-3 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Informasi
        </h4>
        <ul class="space-y-2 text-xs text-[#64748b]">
            <li class="flex items-start gap-2"><span class="text-[#475569] mt-0.5">•</span> Jika Anda tidak menghubungkan model AI sendiri, sistem akan menggunakan model default yang disediakan admin.</li>
            <li class="flex items-start gap-2"><span class="text-[#475569] mt-0.5">•</span> API key Anda disimpan secara <span class="text-green-400 font-medium">terenkripsi</span> di database dan tidak pernah ditampilkan kembali.</li>
            <li class="flex items-start gap-2"><span class="text-[#475569] mt-0.5">•</span> Anda hanya dapat memiliki <span class="text-[#00d4ff] font-medium">1 konfigurasi aktif</span>. Menambahkan config baru akan menggantikan yang lama.</li>
            <li class="flex items-start gap-2"><span class="text-[#475569] mt-0.5">•</span> Untuk Google OAuth, pastikan akun Google Anda memiliki akses ke Generative Language API.</li>
        </ul>
    </div>

    {{-- Back link --}}
    <div>
        <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 text-xs text-[#64748b] hover:text-[#00d4ff] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Profil
        </a>
    </div>
</div>

@push('scripts')
<script>
function detectProvider() {
    const apiKey = document.getElementById('apiKeyInput').value.trim();
    if (!apiKey || apiKey.length < 10) {
        alert('Masukkan API key yang valid (minimal 10 karakter).');
        return;
    }

    const resultDiv = document.getElementById('detectionResult');
    const loadingDiv = document.getElementById('detectionLoading');
    const successDiv = document.getElementById('detectionSuccess');
    const errorDiv = document.getElementById('detectionError');

    resultDiv.classList.remove('hidden');
    loadingDiv.classList.remove('hidden');
    successDiv.classList.add('hidden');
    errorDiv.classList.add('hidden');

    document.getElementById('btnDetect').disabled = true;

    fetch('{{ route("profile.ai.detect") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ api_key: apiKey })
    })
    .then(res => res.json())
    .then(data => {
        loadingDiv.classList.add('hidden');
        document.getElementById('btnDetect').disabled = false;

        if (data.detected) {
            successDiv.classList.remove('hidden');
            document.getElementById('detectedProviderLabel').textContent = 'Terdeteksi: ' + data.label;
            document.getElementById('detectedModelCount').textContent = data.models.length + ' model tersedia';

            document.getElementById('formApiKey').value = apiKey;
            document.getElementById('formProvider').value = data.provider;
            document.getElementById('formAvailableModels').value = JSON.stringify(data.models);

            const select = document.getElementById('modelSelect');
            select.innerHTML = '';
            data.models.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.name || m.id;
                select.appendChild(opt);
            });
        } else {
            errorDiv.classList.remove('hidden');
            document.getElementById('detectionErrorMsg').textContent = data.error || 'API key tidak dapat dikenali.';
        }
    })
    .catch(err => {
        loadingDiv.classList.add('hidden');
        errorDiv.classList.remove('hidden');
        document.getElementById('detectionErrorMsg').textContent = 'Gagal menghubungi server: ' + err.message;
        document.getElementById('btnDetect').disabled = false;
    });
}

function updateModel() {
    const model = document.getElementById('modelSelector').value;
    fetch('{{ route("profile.ai.update-model") }}', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ selected_model: model })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Gagal memperbarui model.');
        }
    });
}

function refreshModelList() {
    const btn = document.getElementById('btnRefreshModels');
    btn.disabled = true;
    btn.innerHTML = '<div class="inline-block w-4 h-4 border-2 border-[#94a3b8] border-t-transparent rounded-full animate-spin"></div>';

    fetch('{{ route("profile.ai.refresh-models") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Daftar model berhasil diperbarui! (' + data.count + ' model tersedia)');
            location.reload();
        } else {
            alert(data.error || 'Gagal refresh model.');
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
        }
    })
    .catch(err => {
        alert('Gagal: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
    });
}

let failedModelIds = [];

function testAllModels() {
    const btn = document.getElementById('btnTestModels');
    const btnRemove = document.getElementById('btnRemoveFailed');
    const progress = document.getElementById('testProgress');
    const results = document.getElementById('testResults');
    const resultsList = document.getElementById('testResultsList');
    const summary = document.getElementById('testSummary');

    btn.disabled = true;
    btn.innerHTML = '<div class="inline-block w-3 h-3 border-2 border-[#00d4ff] border-t-transparent rounded-full animate-spin"></div> Menguji...';
    btnRemove.classList.add('hidden');
    progress.classList.remove('hidden');
    results.classList.add('hidden');
    resultsList.innerHTML = '';
    failedModelIds = [];

    fetch('{{ route("profile.ai.test-models") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        progress.classList.add('hidden');
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Cek Semua Model';

        if (data.error) {
            summary.textContent = '❌ ' + data.error;
            results.classList.remove('hidden');
            return;
        }

        summary.textContent = `✅ ${data.working}/${data.total} model aktif — Provider: ${data.provider}`;
        results.classList.remove('hidden');

        // Sort: working first, then errors
        const sorted = (data.results || []).sort((a, b) => {
            if (a.status === 'ok' && b.status !== 'ok') return -1;
            if (a.status !== 'ok' && b.status === 'ok') return 1;
            return 0;
        });

        sorted.forEach(r => {
            const isOk = r.status === 'ok';
            if (!isOk) {
                failedModelIds.push(r.id);
            }
            
            const row = document.createElement('div');
            row.className = `flex items-center justify-between px-3 py-2 rounded-lg ${isOk ? 'bg-green-500/5 border border-green-500/15' : 'bg-red-500/5 border border-red-500/15'}`;
            row.innerHTML = `
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-sm ${isOk ? 'text-green-400' : 'text-red-400'} shrink-0">${isOk ? '✓' : '✗'}</span>
                    <span class="text-xs text-white font-mono truncate">${r.id}</span>
                </div>
                <span class="text-[10px] ${isOk ? 'text-green-400' : 'text-red-400'} shrink-0 ml-2">${r.message}</span>
            `;
            resultsList.appendChild(row);
        });

        if (failedModelIds.length > 0) {
            btnRemove.classList.remove('hidden');
        }
    })
    .catch(err => {
        progress.classList.add('hidden');
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Cek Semua Model';
        summary.textContent = '❌ Gagal menghubungi server: ' + err.message;
        results.classList.remove('hidden');
    });
}

function removeFailedModels() {
    if (failedModelIds.length === 0) return;
    
    if (!confirm(`Apakah Anda yakin ingin menghapus ${failedModelIds.length} model yang error dari daftar? (Anda dapat memulihkannya kembali dengan tombol Refresh)`)) {
        return;
    }
    
    const btn = document.getElementById('btnRemoveFailed');
    btn.disabled = true;
    btn.textContent = 'Menghapus...';

    fetch('{{ route("profile.ai.remove-models") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ model_ids: failedModelIds })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Berhasil menghapus model error. Halaman akan dimuat ulang.');
            location.reload();
        } else {
            alert(data.error || 'Gagal menghapus model.');
            btn.disabled = false;
            btn.textContent = 'Hapus Model Error';
        }
    })
    .catch(err => {
        alert('Gagal: ' + err.message);
        btn.disabled = false;
        btn.textContent = 'Hapus Model Error';
    });
}
</script>
@endpush
@endsection
