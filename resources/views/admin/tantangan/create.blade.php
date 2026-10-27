@extends('layouts.app')
@section('title', 'Tambah Soal — Admin RedSim')
@section('header', 'Tambah Soal Edukasi / CTF')
@section('subheader', 'Buat soal keamanan dalam berbagai format: Quiz, Kode, Skenario, Ciphertext, atau File')

@section('content')
<div x-data="ctfCreator()" class="max-w-4xl mx-auto space-y-6">
    {{-- Error Messages --}}
    @if($errors->any())
    <div class="px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
        <ul class="list-disc pl-4">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ===== PANEL AI GENERATOR ===== --}}
    <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 cursor-pointer" @click="showAiForm = !showAiForm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(124,58,237,.25),rgba(236,72,153,.25))">
                    <svg class="w-5 h-5 text-[#ec4899]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Buat Soal Otomatis dengan AI</h3>
                    <p class="text-xs text-[#64748b]">Gunakan AI untuk membuat soal dalam format apapun, dengan atau tanpa RAG.</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-[#94a3b8] transition-transform duration-200" :class="showAiForm ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>

        <div x-show="showAiForm" x-collapse class="px-6 pb-6 pt-1 border-t border-[#1e2d4a] space-y-4">
            {{-- Row 1: Topik + Random --}}
            <div>
                <label class="block text-xs font-medium text-[#94a3b8] mb-1.5">Topik Spesifik</label>
                <div class="flex gap-2">
                    <input type="text" x-model="aiTopik" placeholder="Contoh: SQL Injection, AES Encryption..."
                           class="flex-1 px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#7c3aed] focus:outline-none">
                    <button type="button" @click="pickRandomTopik()" :disabled="isPickingTopik"
                            class="px-3 py-2 rounded-lg bg-[#7c3aed]/20 border border-[#7c3aed]/40 text-[#a78bfa] text-xs font-medium hover:bg-[#7c3aed]/30 transition-colors flex items-center gap-1.5 whitespace-nowrap disabled:opacity-50">
                        <span x-show="!isPickingTopik">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </span>
                        <span x-show="isPickingTopik" class="w-3.5 h-3.5 border-2 border-[#a78bfa]/30 border-t-[#a78bfa] rounded-full animate-spin"></span>
                        Soal Pilihan AI
                    </button>
                </div>
                <p class="text-[10px] text-[#475569] mt-1" x-show="aiTopik">
                    Topik: <span class="text-[#a78bfa]" x-text="aiTopik"></span>
                </p>
            </div>

            {{-- Row 2: Format + Kesulitan --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-[#94a3b8] mb-1.5">Format Soal</label>
                    <select x-model="aiFormat" class="w-full px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#7c3aed] focus:outline-none">
                        <option value="quiz">Quiz Pilihan Ganda</option>
                        <option value="kode">Analisis Kode (Code Box)</option>
                        <option value="skenario">Skenario Poin-per-Poin</option>
                        <option value="ciphertext">Ciphertext / Kriptografi</option>
                        <option value="file">Analisis File (Investigasi)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-[#94a3b8] mb-1.5">Tingkat Kesulitan</label>
                    <select x-model="aiTingkat" class="w-full px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#7c3aed] focus:outline-none">
                        <option value="mudah">Mudah</option>
                        <option value="sedang">Sedang</option>
                        <option value="sulit">Sulit</option>
                    </select>
                </div>
            </div>

            {{-- Row 3: Metode RAG --}}
            <div>
                <label class="block text-xs font-medium text-[#94a3b8] mb-2">Metode Pembuatan</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="aiUseRag" value="ya" class="accent-[#7c3aed]">
                        <span class="text-sm text-white">Gunakan RAG <span class="text-[10px] bg-green-500/20 text-green-400 px-1.5 py-0.5 rounded ml-1">Disarankan</span></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="aiUseRag" value="tidak" class="accent-[#7c3aed]">
                        <span class="text-sm text-[#94a3b8]">Tanpa RAG (AI Default)</span>
                    </label>
                </div>
            </div>

            {{-- Action row --}}
            <div class="flex items-center justify-between pt-2 border-t border-[#1e2d4a]">
                <span class="text-xs text-red-400" x-text="aiError"></span>
                <button @click="generateSoal()" :disabled="isLoading"
                        class="px-5 py-2 rounded-xl text-white text-sm font-semibold disabled:opacity-50 flex items-center gap-2 transition-all"
                        style="background:linear-gradient(135deg,#7c3aed,#ec4899)">
                    <span x-show="isLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span x-text="isLoading ? 'Memproses...' : 'Generate Soal dengan AI'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== FORM UTAMA ===== --}}
    <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-8">
        <form method="POST" action="{{ route('admin.tantangan.store') }}" class="space-y-6" id="formSoal" @submit.prevent="submitForm">
            @csrf

            {{-- SELECTOR FORMAT SOAL --}}
            <div>
                <label class="block text-sm font-medium text-[#94a3b8] mb-3">Format Soal</label>
                <div class="grid grid-cols-5 gap-2">
                    <template x-for="f in formatList" :key="f.value">
                        <button type="button" @click="form.format_soal = f.value"
                                class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border text-center transition-all"
                                :class="form.format_soal === f.value
                                    ? 'border-[#7c3aed] bg-[#7c3aed]/15 text-white'
                                    : 'border-[#1e2d4a] bg-[#0a0e1a] text-[#64748b] hover:border-[#7c3aed]/50'">
                            <span class="w-5 h-5 flex items-center justify-center text-slate-300" x-html="f.icon"></span>
                            <span class="text-[10px] font-medium leading-tight" x-text="f.label"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- INFO FORMAT --}}
            <div class="text-xs text-[#64748b] px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a]">
                <span x-text="formatDesc[form.format_soal]"></span>
            </div>

            {{-- JUDUL + KESULITAN + POIN --}}
            <div>
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Judul Soal</label>
                <input type="text" name="judul" x-model="form.judul" required
                       class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none"
                       placeholder="Judul singkat soal atau challenge">
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Tingkat Kesulitan</label>
                    <select name="tingkat_kesulitan" x-model="form.tingkat_kesulitan" required
                            class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
                        <option value="mudah">Mudah</option>
                        <option value="sedang">Sedang</option>
                        <option value="sulit">Sulit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Poin</label>
                    <input type="number" name="poin" x-model="form.poin" required min="1"
                           class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
                </div>
            </div>

            {{-- DESKRIPSI / PERTANYAAN --}}
            <div>
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">
                    <span x-text="form.format_soal === 'quiz' ? 'Pertanyaan / Kasus Soal' : 'Instruksi / Pertanyaan Utama'"></span>
                </label>
                <textarea name="deskripsi" x-model="form.deskripsi" rows="4" required
                          class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none"
                          placeholder="Tuliskan pertanyaan atau instruksi yang harus dijawab oleh user..."></textarea>
            </div>

            {{-- ===== FORMAT: KODE ===== --}}
            <div x-show="form.format_soal === 'kode'" class="space-y-4">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-[#94a3b8]">Kode untuk Dianalisis</label>
                    <select x-model="form.bahasa_kode" class="px-3 py-1.5 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-[#94a3b8] focus:border-[#00d4ff] focus:outline-none">
                        <option value="php">PHP</option>
                        <option value="javascript">JavaScript</option>
                        <option value="python">Python</option>
                        <option value="java">Java</option>
                        <option value="bash">Bash/Shell</option>
                        <option value="sql">SQL</option>
                        <option value="c">C/C++</option>
                    </select>
                </div>
                <div class="relative">
                    <div class="flex items-center gap-2 px-4 py-2 bg-[#070b14] rounded-t-xl border border-b-0 border-[#1e2d4a]">
                        <div class="flex gap-1.5"><span class="w-3 h-3 rounded-full bg-[#ff5f57]"></span><span class="w-3 h-3 rounded-full bg-[#ffbd2e]"></span><span class="w-3 h-3 rounded-full bg-[#28c941]"></span></div>
                        <span class="text-xs text-[#475569] ml-1" x-text="form.bahasa_kode + ' code'"></span>
                    </div>
                    <textarea name="konten_kode" x-model="form.konten_kode" rows="10"
                              class="w-full px-4 py-3 rounded-b-xl bg-[#070b14] border border-[#1e2d4a] text-green-400 font-mono text-sm focus:border-[#00d4ff] focus:outline-none resize-y"
                              placeholder="// Tempelkan kode yang mengandung celah keamanan di sini...&#10;// User akan diminta menganalisis dan menemukan kerentanannya."></textarea>
                </div>
            </div>

            {{-- ===== FORMAT: SKENARIO ===== --}}
            <div x-show="form.format_soal === 'skenario'" class="space-y-3">
                <label class="block text-sm font-medium text-[#94a3b8]">Poin-Poin Skenario</label>
                <div class="space-y-2" id="skenarioPoinContainer">
                    <template x-for="(poin, idx) in form.skenario_poin" :key="idx">
                        <div class="flex gap-2 items-start">
                            <span class="text-xs text-[#7c3aed] font-bold mt-3 w-6 shrink-0" x-text="(idx+1) + '.'"></span>
                            <input type="text" x-model="form.skenario_poin[idx]"
                                   class="flex-1 px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#7c3aed] focus:outline-none"
                                   :placeholder="'Langkah ' + (idx+1) + ': Deskripsi kejadian pada tahap ini...'">
                            <button type="button" @click="removeSkenarioPoin(idx)" class="mt-2 text-red-400 hover:text-red-300 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addSkenarioPoin()"
                        class="flex items-center gap-1.5 text-xs text-[#7c3aed] hover:text-[#a78bfa] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Poin Skenario
                </button>
            </div>

            {{-- ===== FORMAT: CIPHERTEXT ===== --}}
            <div x-show="form.format_soal === 'ciphertext'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Teks Terenkripsi (Ciphertext)</label>
                    <div class="relative">
                        <div class="flex items-center px-4 py-2 bg-[#070b14] rounded-t-xl border border-b-0 border-[#1e2d4a]">
                            <svg class="w-4 h-4 text-[#7c3aed] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span class="text-xs text-[#475569]">CIPHERTEXT</span>
                        </div>
                        <textarea name="konten_ciphertext" x-model="form.konten_ciphertext" rows="4"
                                  class="w-full px-4 py-3 rounded-b-xl bg-[#070b14] border border-[#1e2d4a] text-yellow-300 font-mono text-sm focus:border-[#7c3aed] focus:outline-none"
                                  placeholder="VGhpcyBpcyBhIHRlc3QgY2lwaGVydGV4dA==&#10;atau&#10;Khoor Zruog (Caesar cipher)"></textarea>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Petunjuk/Hint (Opsional)</label>
                    <input type="text" name="hint_cipher" x-model="form.hint_cipher"
                           class="w-full px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#7c3aed] focus:outline-none"
                           placeholder="Contoh: Gunakan Base64, atau pergeseran 3...">
                </div>
            </div>

            {{-- ===== FORMAT: FILE ===== --}}
            <div x-show="form.format_soal === 'file'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Konten/Petunjuk File (Simulasi)</label>
                    <p class="text-xs text-[#475569] mb-2">Karena ini platform web, simulasikan excerpt/konten file yang relevan agar user bisa menganalisisnya.</p>
                    <div class="relative">
                        <div class="flex items-center px-4 py-2 bg-[#070b14] rounded-t-xl border border-b-0 border-[#1e2d4a]">
                            <svg class="w-4 h-4 text-[#00d4ff] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="text-xs text-[#475569]">FILE CONTENT / LOG EXCERPT</span>
                        </div>
                        <textarea name="petunjuk_file" x-model="form.petunjuk_file" rows="8"
                                  class="w-full px-4 py-3 rounded-b-xl bg-[#070b14] border border-[#1e2d4a] text-green-300 font-mono text-sm focus:border-[#00d4ff] focus:outline-none"
                                  placeholder="[2024-01-15 14:32:01] ERROR: Failed login attempt from 192.168.1.100&#10;[2024-01-15 14:32:05] WARNING: Multiple auth failures detected&#10;..."></textarea>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Hint (Opsional)</label>
                    <input type="text" name="hint_file" x-model="form.hint_file"
                           class="w-full px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none"
                           placeholder="Petunjuk tambahan untuk menemukan flag/jawaban...">
                </div>
            </div>

            {{-- ===== FORMAT: QUIZ — PILIHAN JAWABAN ===== --}}
            <div x-show="form.format_soal === 'quiz'" class="space-y-4 pt-4 border-t border-[#1e2d4a]">
                <h4 class="font-medium text-white text-sm">Pilihan Jawaban</h4>
                <template x-for="opt in ['A','B','C','D']" :key="opt">
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-[#00d4ff] w-6" x-text="opt + '.'"></span>
                        <input type="text" :name="'pilihan_' + opt.toLowerCase()" x-model="form['pilihan_' + opt.toLowerCase()]"
                               :required="form.format_soal === 'quiz'"
                               class="flex-1 px-4 py-2 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none"
                               :placeholder="'Isi pilihan ' + opt">
                    </div>
                </template>

                <div class="pt-3 border-t border-[#1e2d4a]">
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Jawaban Benar</label>
                    <div class="flex gap-4">
                        <template x-for="opt in ['A','B','C','D']" :key="opt">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" :name="'jawaban_benar_quiz'" :value="opt" x-model="form.jawaban_benar" class="accent-[#00d4ff]">
                                <span class="font-bold text-sm" x-text="opt"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            {{-- ===== JAWABAN BENAR (untuk format non-quiz) ===== --}}
            <div x-show="form.format_soal !== 'quiz'" class="pt-4 border-t border-[#1e2d4a]">
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">
                    Jawaban Benar / Flag
                    <span class="text-[10px] text-[#475569] ml-1">
                        (untuk format kode: nama kerentanan; cipher: plaintext; file: flag CTF{...}; skenario: analisis teks bebas)
                    </span>
                </label>
                <input type="text" x-model="form.jawaban_benar"
                       :required="form.format_soal !== 'quiz'"
                       class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none"
                       placeholder="Jawaban benar / plaintext / flag (CTF{...})">
            </div>

            {{-- PENJELASAN --}}
            <div>
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Penjelasan (Opsional)</label>
                <textarea name="penjelasan" x-model="form.penjelasan" rows="3"
                          class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none"
                          placeholder="Penjelasan mengapa itu jawaban benar, teknik yang digunakan, dll..."></textarea>
            </div>

            {{-- TOMBOL --}}
            <div class="flex gap-3 justify-end pt-4 border-t border-[#1e2d4a]">
                <a href="{{ route('admin.tantangan.index') }}" class="px-6 py-2.5 rounded-xl bg-[#1e2d4a] text-white font-medium hover:bg-[#2a3a5c] transition-colors">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-white font-semibold hover:shadow-lg transition-all" style="background:linear-gradient(135deg,#00d4ff,#7c3aed)">
                    Simpan Soal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('ctfCreator', () => ({
        showAiForm: false,
        isLoading: false,
        isPickingTopik: false,
        aiTopik: '',
        aiTingkat: 'sedang',
        aiUseRag: 'ya',
        aiFormat: 'quiz',
        aiError: '',

        formatList: [
            { value: 'quiz',       icon: '<svg class=\'w-5 h-5 text-purple-400\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>', label: 'Quiz\nPilihan Ganda' },
            { value: 'kode',       icon: '<svg class=\'w-5 h-5 text-cyan-400\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4\'/></svg>', label: 'Kode\nAnalisis' },
            { value: 'skenario',   icon: '<svg class=\'w-5 h-5 text-orange-400\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2\'/></svg>', label: 'Skenario\nKronologis' },
            { value: 'ciphertext', icon: '<svg class=\'w-5 h-5 text-pink-400\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z\'/></svg>', label: 'Cipher\nKriptografi' },
            { value: 'file',       icon: '<svg class=\'w-5 h-5 text-emerald-400\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z\'/></svg>', label: 'File\nInvestigasi' },
        ],

        formatDesc: {
            quiz:       'Format klasik pilihan ganda (A/B/C/D). Cocok untuk tes pengetahuan dasar dan konsep.',
            kode:       'Soal menyajikan kode program dalam code box. User menganalisis celah keamanan atau bug kritis di dalamnya.',
            skenario:   'Soal dalam bentuk skenario insiden keamanan yang disajikan poin per poin secara kronologis.',
            ciphertext: 'Tantangan kriptografi: user harus memecahkan teks terenkripsi untuk menemukan plaintext atau flag.',
            file:       'Tantangan forensik: user menganalisis konten file (log, hex dump, pcap, dll) untuk menemukan flag atau bukti.',
        },

        form: {
            judul: '{{ old("judul", "") }}',
            deskripsi: '{{ old("deskripsi", "") }}',
            poin: '{{ old("poin", "15") }}',
            tingkat_kesulitan: '{{ old("tingkat_kesulitan", "sedang") }}',
            format_soal: '{{ old("format_soal", "quiz") }}',
            pilihan_a: '', pilihan_b: '', pilihan_c: '', pilihan_d: '',
            jawaban_benar: 'A',
            penjelasan: '',
            bahasa_kode: 'php',
            konten_kode: '',
            skenario_poin: ['', '', ''],
            konten_ciphertext: '',
            hint_cipher: '',
            petunjuk_file: '',
            hint_file: '',
        },

        addSkenarioPoin() { this.form.skenario_poin.push(''); },
        removeSkenarioPoin(idx) {
            if (this.form.skenario_poin.length > 1) this.form.skenario_poin.splice(idx, 1);
        },

        async pickRandomTopik() {
            this.isPickingTopik = true;
            try {
                const r = await fetch('{{ route("admin.tantangan.random-topik") }}', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
                });
                const d = await r.json();
                this.aiTopik = d.topik || '';
            } catch(e) { console.error(e); } finally { this.isPickingTopik = false; }
        },

        async generateSoal() {
            this.isLoading = true;
            this.aiError = '';
            try {
                const payload = btoa(encodeURIComponent(JSON.stringify({
                    topik: this.aiTopik || '',
                    tingkat_kesulitan: this.aiTingkat,
                    use_rag: this.aiUseRag,
                    format_soal: this.aiFormat,
                    jumlah_soal: 1,
                })));

                const r = await fetch('{{ route("admin.tantangan.generate-ai") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
                    body: JSON.stringify({ payload }),
                });

                const data = await r.json();

                if (r.ok && data.success && data.soal && data.soal.length > 0) {
                    const s = data.soal[0];
                    // Isi form dari hasil AI
                    this.form.judul             = s.judul || '';
                    this.form.deskripsi         = s.deskripsi || '';
                    this.form.penjelasan        = s.penjelasan || '';
                    this.form.poin              = s.poin || 15;
                    this.form.tingkat_kesulitan = s.tingkat_kesulitan || this.aiTingkat;
                    this.form.format_soal       = s.format_soal || this.aiFormat;
                    this.form.jawaban_benar     = s.jawaban_benar || '';

                    // Topik yang benar-benar dipakai AI
                    if (data.topik) this.aiTopik = data.topik;

                    // Format-specific
                    if (s.format_soal === 'quiz') {
                        this.form.pilihan_a = s.pilihan_a || '';
                        this.form.pilihan_b = s.pilihan_b || '';
                        this.form.pilihan_c = s.pilihan_c || '';
                        this.form.pilihan_d = s.pilihan_d || '';
                    } else if (s.format_soal === 'kode') {
                        this.form.konten_kode  = s.konten_kode || '';
                        this.form.bahasa_kode  = s.bahasa_kode || 'php';
                    } else if (s.format_soal === 'skenario') {
                        this.form.skenario_poin = Array.isArray(s.skenario_poin) ? s.skenario_poin : [''];
                    } else if (s.format_soal === 'ciphertext') {
                        this.form.konten_ciphertext = s.konten_ciphertext || '';
                        this.form.hint_cipher       = s.hint_cipher || '';
                    } else if (s.format_soal === 'file') {
                        this.form.petunjuk_file = s.petunjuk_file || '';
                        this.form.hint_file     = s.hint_file || '';
                    }

                    this.showAiForm = false;
                } else {
                    this.aiError = data.message || 'Gagal menghasilkan soal. Coba lagi.';
                }
            } catch(e) {
                console.error(e);
                this.aiError = 'Kesalahan jaringan/sistem.';
            } finally {
                this.isLoading = false;
            }
        },

        submitForm(e) {
            const form = e.target;
            const data = {
                judul:             this.form.judul,
                deskripsi:         this.form.deskripsi,
                poin:              this.form.poin,
                tingkat_kesulitan: this.form.tingkat_kesulitan,
                format_soal:       this.form.format_soal,
                jawaban_benar:     this.form.jawaban_benar,
                penjelasan:        this.form.penjelasan,
                _token:            document.querySelector('input[name="_token"]').value,
            };

            if (this.form.format_soal === 'quiz') {
                data.pilihan_a = this.form.pilihan_a;
                data.pilihan_b = this.form.pilihan_b;
                data.pilihan_c = this.form.pilihan_c;
                data.pilihan_d = this.form.pilihan_d;
            } else if (this.form.format_soal === 'kode') {
                data.konten_kode = this.form.konten_kode;
                data.bahasa_kode = this.form.bahasa_kode;
            } else if (this.form.format_soal === 'skenario') {
                data.skenario_poin = this.form.skenario_poin.filter(p => p.trim());
            } else if (this.form.format_soal === 'ciphertext') {
                data.konten_ciphertext = this.form.konten_ciphertext;
                data.hint_cipher       = this.form.hint_cipher;
            } else if (this.form.format_soal === 'file') {
                data.petunjuk_file = this.form.petunjuk_file;
                data.hint_file     = this.form.hint_file;
            }

            const payload = btoa(encodeURIComponent(JSON.stringify(data)));
            form.querySelectorAll('input:not([name="_token"]), textarea, select').forEach(el => el.removeAttribute('name'));
            const h = document.createElement('input');
            h.type = 'hidden'; h.name = 'payload'; h.value = payload;
            form.appendChild(h);
            form.submit();
        },
    }));
});
</script>
@endsection
