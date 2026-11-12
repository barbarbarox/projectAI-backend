@extends('layouts.app')

@section('title', 'Buat Grup Aset Baru — RedSim ASM')
@section('header', 'Buat Grup Aset Baru')
@section('subheader', 'Kelompokkan URL dan domain target untuk pemantauan terpusat dan pemindaian massal')
@section('page_feature', 'asset-groups')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 pb-12">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-[#64748b]">
        <a href="{{ route('asset-groups.index') }}" class="hover:text-[#00d4ff] transition-colors">Grup Aset</a>
        <span>/</span>
        <span class="text-white">Buat Grup Baru</span>
    </div>

    @if($errors->any())
    <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs space-y-1">
        <div class="font-bold flex items-center gap-1.5 text-red-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Terdapat beberapa kesalahan:
        </div>
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="rounded-2xl bg-[#0a0e1a] border border-[#1e2d4a] p-6 shadow-xl relative overflow-hidden">
        {{-- Neon top line --}}
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-indigo-500 via-cyan-500 to-purple-500"></div>

        <form method="POST" action="{{ route('asset-groups.store') }}" class="space-y-6">
            @csrf

            {{-- Nama Grup --}}
            <div>
                <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                    Nama Grup Aset <span class="text-red-400">*</span>
                </label>
                <input type="text"
                       name="nama"
                       required
                       value="{{ old('nama') }}"
                       placeholder="Contoh: Infrastruktur Produksi E-Commerce"
                       class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff]">
            </div>

            {{-- 2 Col: Environment & Tema Warna --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Environment --}}
                <div>
                    <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                        Lingkungan (Environment) <span class="text-red-400">*</span>
                    </label>
                    <select name="environment"
                            required
                            class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-[#00d4ff]">
                        <option value="production" {{ old('environment') === 'production' ? 'selected' : '' }}>Production (Sistem Utama / Publik)</option>
                        <option value="staging" {{ old('environment') === 'staging' ? 'selected' : '' }}>Staging / QA (Pra-Rilis)</option>
                        <option value="development" {{ old('environment') === 'development' ? 'selected' : '' }}>Development (Pengembangan)</option>
                        <option value="internal" {{ old('environment') === 'internal' ? 'selected' : '' }}>Internal / Intranet</option>
                    </select>
                </div>

                {{-- Tema Warna Tag --}}
                <div>
                    <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                        Aksen Warna Label <span class="text-red-400">*</span>
                    </label>
                    <select name="warna"
                            required
                            class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-[#00d4ff]">
                        <option value="indigo" {{ old('warna', 'indigo') === 'indigo' ? 'selected' : '' }}>Indigo (Cyber Blue)</option>
                        <option value="cyan" {{ old('warna') === 'cyan' ? 'selected' : '' }}>Cyan (Neon Sky)</option>
                        <option value="emerald" {{ old('warna') === 'emerald' ? 'selected' : '' }}>Emerald (Green Shield)</option>
                        <option value="amber" {{ old('warna') === 'amber' ? 'selected' : '' }}>Amber (Warning Gold)</option>
                        <option value="purple" {{ old('warna') === 'purple' ? 'selected' : '' }}>Purple (Dark Royal)</option>
                        <option value="rose" {{ old('warna') === 'rose' ? 'selected' : '' }}>Rose (Red Critical)</option>
                    </select>
                </div>
            </div>

            {{-- Deskripsi --}}
            <div>
                <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                    Deskripsi Kelompok Aset
                </label>
                <textarea name="deskripsi"
                          rows="2"
                          placeholder="Jelaskan peran atau cakupan aset digital dalam kelompok ini..."
                          class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-4 py-2.5 text-xs text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff] leading-relaxed">{{ old('deskripsi') }}</textarea>
            </div>

            {{-- Daftar Target Awal (Textarea) --}}
            <div>
                <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-1">
                    Daftar Target Awal (Opsional)
                </label>
                <p class="text-[11px] text-[#64748b] mb-2">
                    Masukkan URL atau domain target, pisahkan dengan baris baru (Enter). Anda juga dapat menambahkannya nanti.
                </p>
                <textarea name="targets_raw"
                          rows="5"
                          placeholder="https://example.com&#10;api.example.com&#10;auth.example.com&#10;dashboard.example.com"
                          class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl p-3.5 text-xs font-mono text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff] leading-relaxed">{{ old('targets_raw') }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#1e2d4a]">
                <a href="{{ route('asset-groups.index') }}"
                   class="px-5 py-2.5 rounded-xl bg-[#1e293b] hover:bg-[#334155] text-xs font-medium text-slate-300 hover:text-white transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-500 hover:to-cyan-500 text-white text-xs font-semibold shadow-lg shadow-indigo-950/40 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan & Buat Grup Aset</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
