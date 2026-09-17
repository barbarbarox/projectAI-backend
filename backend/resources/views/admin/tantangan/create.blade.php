@extends('layouts.app')
@section('title', 'Tambah Soal — Admin RedSim')
@section('header', 'Tambah Soal Edukasi')
@section('subheader', 'Buat soal pilihan ganda baru untuk menguji pengguna')

@section('content')
<div x-data="aiGenerator()" class="max-w-3xl mx-auto space-y-6">
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

    {{-- AI Generation Box --}}
    <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-6 mb-6">
        <div class="flex items-center justify-between cursor-pointer" @click="showAiForm = !showAiForm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#7c3aed]/20 to-[#ec4899]/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#ec4899]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Buat Soal Otomatis dengan AI</h3>
                    <p class="text-xs text-[#64748b]">Gunakan kecerdasan buatan untuk merumuskan soal siber.</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-[#94a3b8] transition-transform" :class="showAiForm ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>

        <div x-show="showAiForm" x-collapse class="mt-5 pt-5 border-t border-[#1e2d4a]">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-[#94a3b8] mb-1">Topik Spesifik</label>
                    <input type="text" x-model="aiTopik" placeholder="Contoh: Phishing, SQL Injection..." class="w-full px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#7c3aed] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[#94a3b8] mb-1">Kesulitan</label>
                    <select x-model="aiTingkat" class="w-full px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#7c3aed] focus:outline-none">
                        <option value="mudah">Mudah</option>
                        <option value="sedang">Sedang</option>
                        <option value="sulit">Sulit</option>
                    </select>
                </div>
            </div>
            <div class="mb-5">
                <label class="block text-xs font-medium text-[#94a3b8] mb-2">Metode Pembuatan</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="aiUseRag" value="ya" class="accent-[#7c3aed]">
                        <span class="text-sm">Gunakan RAG (Knowledge Base) <span class="text-[10px] bg-green-500/20 text-green-400 px-1.5 py-0.5 rounded ml-1">Disarankan</span></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="aiUseRag" value="tidak" class="accent-[#7c3aed]">
                        <span class="text-sm text-[#94a3b8]">Tanpa RAG (AI Default)</span>
                    </label>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-red-400" x-text="aiError"></span>
                <button @click="generateSoal" :disabled="isLoading" class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#7c3aed] to-[#ec4899] text-white text-sm font-semibold hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span x-show="isLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <div x-show="!isLoading" class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> <span>Generate Soal</span></div>
                    <span x-show="isLoading">Sedang Memproses...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Form Utama --}}
    <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-8">
        <form method="POST" action="{{ route('admin.tantangan.store') }}" class="space-y-5" id="formSoal">
            @csrf
            <div>
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Judul Soal</label>
                <input type="text" name="judul" x-model="form.judul" required class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Deskripsi Soal (Cerita/Kasus)</label>
                <textarea name="deskripsi" x-model="form.deskripsi" rows="4" required class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Poin</label>
                    <input type="number" name="poin" x-model="form.poin" required class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Tingkat Kesulitan</label>
                    <select name="tingkat_kesulitan" x-model="form.tingkat_kesulitan" required class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
                        <option value="mudah">Mudah</option>
                        <option value="sedang">Sedang</option>
                        <option value="sulit">Sulit</option>
                    </select>
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-[#1e2d4a]">
                <h4 class="font-medium text-white">Pilihan Jawaban</h4>
                <div class="flex items-center gap-3">
                    <span class="font-bold text-[#00d4ff] w-6">A.</span>
                    <input type="text" name="pilihan_a" x-model="form.pilihan_a" required class="flex-1 px-4 py-2 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none">
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-bold text-[#00d4ff] w-6">B.</span>
                    <input type="text" name="pilihan_b" x-model="form.pilihan_b" required class="flex-1 px-4 py-2 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none">
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-bold text-[#00d4ff] w-6">C.</span>
                    <input type="text" name="pilihan_c" x-model="form.pilihan_c" required class="flex-1 px-4 py-2 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none">
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-bold text-[#00d4ff] w-6">D.</span>
                    <input type="text" name="pilihan_d" x-model="form.pilihan_d" required class="flex-1 px-4 py-2 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none">
                </div>
            </div>

            <div class="pt-4 border-t border-[#1e2d4a]">
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Jawaban Benar</label>
                <div class="flex gap-4">
                    @foreach(['A','B','C','D'] as $opt)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="jawaban_benar" value="{{ $opt }}" x-model="form.jawaban_benar" required class="accent-[#00d4ff]">
                        <span class="font-bold">{{ $opt }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Penjelasan (Opsional)</label>
                <textarea name="penjelasan" x-model="form.penjelasan" rows="2" class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none" placeholder="Penjelasan mengapa jawaban tersebut benar"></textarea>
            </div>

            <div class="flex gap-3 justify-end pt-4 border-t border-[#1e2d4a]">
                <a href="{{ route('admin.tantangan.index') }}" class="px-6 py-2.5 rounded-xl bg-[#1e2d4a] text-white font-medium hover:bg-[#2a3a5c] transition-colors">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg transition-all">Simpan Soal</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('aiGenerator', () => ({
        showAiForm: false,
        isLoading: false,
        aiTopik: '',
        aiTingkat: 'sedang',
        aiUseRag: 'ya',
        aiError: '',
        
        form: {
            judul: '{{ old('judul', '') }}',
            deskripsi: '{{ old('deskripsi', '') }}',
            poin: '{{ old('poin', '15') }}',
            tingkat_kesulitan: '{{ old('tingkat_kesulitan', 'sedang') }}',
            pilihan_a: '{{ old('pilihan_a', '') }}',
            pilihan_b: '{{ old('pilihan_b', '') }}',
            pilihan_c: '{{ old('pilihan_c', '') }}',
            pilihan_d: '{{ old('pilihan_d', '') }}',
            jawaban_benar: '{{ old('jawaban_benar', 'A') }}',
            penjelasan: '{{ old('penjelasan', '') }}'
        },

        async generateSoal() {
            this.isLoading = true;
            this.aiError = '';

            try {
                const response = await fetch('{{ route('admin.tantangan.generate-ai') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        topik: this.aiTopik || 'keamanan siber umum',
                        tingkat_kesulitan: this.aiTingkat,
                        use_rag: this.aiUseRag,
                        jumlah_soal: 1
                    })
                });

                const data = await response.json();

                if (response.ok && data.success && data.soal && data.soal.length > 0) {
                    const soal = data.soal[0];
                    this.form.judul = soal.judul;
                    this.form.deskripsi = soal.deskripsi;
                    this.form.pilihan_a = soal.pilihan_a;
                    this.form.pilihan_b = soal.pilihan_b;
                    this.form.pilihan_c = soal.pilihan_c;
                    this.form.pilihan_d = soal.pilihan_d;
                    this.form.jawaban_benar = soal.jawaban_benar;
                    this.form.penjelasan = soal.penjelasan;
                    this.form.poin = soal.poin;
                    this.form.tingkat_kesulitan = soal.tingkat_kesulitan;
                    
                    this.showAiForm = false; // tutup box AI jika berhasil
                    // flash success
                } else {
                    this.aiError = data.message || 'Gagal menghasilkan soal dari AI. Coba lagi.';
                }
            } catch (error) {
                console.error(error);
                this.aiError = 'Terjadi kesalahan jaringan/sistem.';
            } finally {
                this.isLoading = false;
            }
        }
    }));
});
</script>
@endsection
