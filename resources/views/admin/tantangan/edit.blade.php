@extends('layouts.app')
@section('title', 'Edit Soal — Admin RedSim')
@section('header', 'Edit Soal Edukasi / CTF')
@section('subheader', 'Perbarui soal yang sudah ada')

@section('content')
@php
    $format = $tantangan->format_soal ?? 'quiz';
    $pilihan = is_array($tantangan->pilihan_jawaban) ? $tantangan->pilihan_jawaban : [];
    $konten = is_array($tantangan->konten_tambahan) ? $tantangan->konten_tambahan : [];
    $formatLabels = [
        'quiz'       => 'Quiz Pilihan Ganda',
        'kode'       => 'Analisis Kode',
        'skenario'   => 'Skenario Kronologis',
        'ciphertext' => 'Ciphertext / Kriptografi',
        'file'       => 'Analisis File',
    ];
    $skenarioPoinJson = json_encode($konten['poin_skenario'] ?? ['']);
@endphp
<div x-data="ctfEditor()" class="max-w-4xl mx-auto bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-8">
    @if($errors->any())
    <div class="mb-6 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
        <ul class="list-disc pl-4">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-[#1e2d4a]">
        <span class="px-3 py-1 rounded-lg text-xs font-medium bg-[#7c3aed]/20 text-[#a78bfa]">{{ $formatLabels[$format] ?? $format }}</span>
        <span class="text-xs text-[#475569]">Format tidak bisa diubah setelah dibuat</span>
    </div>

    <form method="POST" action="{{ route('admin.tantangan.update', $tantangan) }}" class="space-y-6" @submit.prevent="submitForm">
        @csrf @method('PUT')
        <input type="hidden" name="format_soal" value="{{ $format }}">

        <div>
            <label class="block text-sm font-medium text-[#94a3b8] mb-2">Judul Soal</label>
            <input type="text" name="judul" value="{{ old('judul', $tantangan->judul) }}" required
                   class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Tingkat Kesulitan</label>
                <select name="tingkat_kesulitan" required class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
                    <option value="mudah" {{ $tantangan->tingkat_kesulitan == 'mudah' ? 'selected' : '' }}>Mudah</option>
                    <option value="sedang" {{ $tantangan->tingkat_kesulitan == 'sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="sulit" {{ $tantangan->tingkat_kesulitan == 'sulit' ? 'selected' : '' }}>Sulit</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Poin</label>
                <input type="number" name="poin" value="{{ old('poin', $tantangan->poin) }}" required min="1"
                       class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-[#94a3b8] mb-2">Instruksi / Pertanyaan</label>
            <textarea name="deskripsi" rows="4" required
                      class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">{{ old('deskripsi', $tantangan->deskripsi) }}</textarea>
        </div>

        @if($format === 'kode')
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <label class="block text-sm font-medium text-[#94a3b8]">Kode untuk Dianalisis</label>
                <select name="bahasa_kode" class="px-3 py-1.5 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-[#94a3b8] focus:border-[#00d4ff] focus:outline-none">
                    @foreach(['php','javascript','python','java','bash','sql','c'] as $lang)
                    <option value="{{ $lang }}" {{ ($konten['bahasa'] ?? 'php') == $lang ? 'selected' : '' }}>{{ strtoupper($lang) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-[#070b14] rounded-t-xl border border-b-0 border-[#1e2d4a]">
                <div class="flex gap-1.5"><span class="w-3 h-3 rounded-full bg-[#ff5f57]"></span><span class="w-3 h-3 rounded-full bg-[#ffbd2e]"></span><span class="w-3 h-3 rounded-full bg-[#28c941]"></span></div>
            </div>
            <textarea name="konten_kode" rows="10"
                      class="w-full px-4 py-3 rounded-b-xl bg-[#070b14] border border-[#1e2d4a] text-green-400 font-mono text-sm focus:border-[#00d4ff] focus:outline-none resize-y">{{ old('konten_kode', $konten['kode'] ?? '') }}</textarea>
        </div>
        @endif

        @if($format === 'skenario')
        <div class="space-y-3" x-data="{ poin: {{ $skenarioPoinJson }} }">
            <label class="block text-sm font-medium text-[#94a3b8]">Poin-Poin Skenario</label>
            <template x-for="(p, idx) in poin" :key="idx">
                <div class="flex gap-2 items-start">
                    <span class="text-xs text-[#7c3aed] font-bold mt-3 w-6 shrink-0" x-text="(idx+1)+'.'"></span>
                    <input type="text" :name="'skenario_poin[]'" x-model="poin[idx]"
                           class="flex-1 px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#7c3aed] focus:outline-none">
                    <button type="button" @click="poin.splice(idx,1)" class="mt-2 text-red-400 hover:text-red-300 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
            <button type="button" @click="poin.push('')" class="flex items-center gap-1.5 text-xs text-[#7c3aed] hover:text-[#a78bfa]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Poin
            </button>
        </div>
        @endif

        @if($format === 'ciphertext')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Teks Terenkripsi</label>
                <div class="flex items-center px-4 py-2 bg-[#070b14] rounded-t-xl border border-b-0 border-[#1e2d4a]">
                    <span class="text-xs text-[#475569]">CIPHERTEXT</span>
                </div>
                <textarea name="konten_ciphertext" rows="4"
                          class="w-full px-4 py-3 rounded-b-xl bg-[#070b14] border border-[#1e2d4a] text-yellow-300 font-mono text-sm focus:border-[#7c3aed] focus:outline-none">{{ old('konten_ciphertext', $konten['ciphertext'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Hint (Opsional)</label>
                <input type="text" name="hint_cipher" value="{{ old('hint_cipher', $konten['hint'] ?? '') }}"
                       class="w-full px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#7c3aed] focus:outline-none">
            </div>
        </div>
        @endif

        @if($format === 'file')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Konten/Petunjuk File</label>
                <div class="flex items-center px-4 py-2 bg-[#070b14] rounded-t-xl border border-b-0 border-[#1e2d4a]">
                    <span class="text-xs text-[#475569]">FILE CONTENT</span>
                </div>
                <textarea name="petunjuk_file" rows="8"
                          class="w-full px-4 py-3 rounded-b-xl bg-[#070b14] border border-[#1e2d4a] text-green-300 font-mono text-sm focus:border-[#00d4ff] focus:outline-none">{{ old('petunjuk_file', $konten['petunjuk'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Hint (Opsional)</label>
                <input type="text" name="hint_file" value="{{ old('hint_file', $konten['hint'] ?? '') }}"
                       class="w-full px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none">
            </div>
        </div>
        @endif

        @if($format === 'quiz')
        <div class="space-y-4 pt-4 border-t border-[#1e2d4a]">
            <h4 class="font-medium text-white text-sm">Pilihan Jawaban</h4>
            @foreach(['A','B','C','D'] as $opt)
            <div class="flex items-center gap-3">
                <span class="font-bold text-[#00d4ff] w-6">{{ $opt }}.</span>
                <input type="text" name="pilihan_{{ strtolower($opt) }}" value="{{ old('pilihan_'.strtolower($opt), $pilihan[$opt] ?? '') }}" required
                       class="flex-1 px-4 py-2 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none">
            </div>
            @endforeach
            <div class="pt-3 border-t border-[#1e2d4a]">
                <label class="block text-sm font-medium text-[#94a3b8] mb-2">Jawaban Benar</label>
                <div class="flex gap-4">
                    @foreach(['A','B','C','D'] as $opt)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="jawaban_benar" value="{{ $opt }}"
                               {{ old('jawaban_benar', strtoupper($tantangan->jawaban_benar)) == $opt ? 'checked' : '' }} required class="accent-[#00d4ff]">
                        <span class="font-bold">{{ $opt }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        @else
        <div class="pt-4 border-t border-[#1e2d4a]">
            <label class="block text-sm font-medium text-[#94a3b8] mb-2">Jawaban Benar / Flag</label>
            <input type="text" name="jawaban_benar" value="{{ old('jawaban_benar', $tantangan->jawaban_benar) }}" required
                   class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-[#94a3b8] mb-2">Penjelasan (Opsional)</label>
            <textarea name="penjelasan" rows="3"
                      class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">{{ old('penjelasan', $tantangan->penjelasan) }}</textarea>
        </div>

        <div class="flex gap-3 justify-end pt-4 border-t border-[#1e2d4a]">
            <a href="{{ route('admin.tantangan.index') }}" class="px-6 py-2.5 rounded-xl bg-[#1e2d4a] text-white font-medium hover:bg-[#2a3a5c] transition-colors">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-white font-semibold hover:shadow-lg transition-all" style="background:linear-gradient(135deg,#00d4ff,#7c3aed)">
                Perbarui Soal
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('ctfEditor', () => ({
        submitForm(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Collect skenario_poin[] array
            const skenarioPoin = formData.getAll('skenario_poin[]');
            if (skenarioPoin.length > 0) data.skenario_poin = skenarioPoin.filter(p => p.trim());

            const payload = btoa(encodeURIComponent(JSON.stringify(data)));
            form.querySelectorAll('input:not([name="_token"]):not([name="_method"]), textarea, select').forEach(el => el.removeAttribute('name'));
            const h = document.createElement('input');
            h.type = 'hidden'; h.name = 'payload'; h.value = payload;
            form.appendChild(h);
            form.submit();
        }
    }));
});
</script>
@endsection
