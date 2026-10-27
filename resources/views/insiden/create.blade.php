@extends('layouts.app')

@section('title', 'Buat Insiden Keamanan Baru — RedSim')
@section('header', 'Buat Insiden Baru')
@section('subheader', 'Daftarkan tiket penanganan kerentanan dan remediasi keamanan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">

    {{-- Breadcrumb Navigation --}}
    <div class="flex items-center gap-2 text-xs text-[#64748b]">
        <a href="{{ route('insiden.index') }}" class="hover:text-[#00d4ff] transition-colors">Manajemen Insiden</a>
        <span>/</span>
        <span class="text-white">Buat Insiden</span>
    </div>

    @if($errors->any())
    <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs space-y-1">
        <div class="font-bold flex items-center gap-1.5 text-red-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Terdapat beberapa kesalahan input:
        </div>
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="rounded-2xl bg-[#0a0e1a] border border-[#1e2d4a] p-6 shadow-xl relative overflow-hidden">
        {{-- Top neon line --}}
        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-red-500 via-amber-500 to-cyan-500"></div>

        <form method="POST" action="{{ route('insiden.store') }}" class="space-y-6">
            @csrf

            @if($prefillTemuan)
                <input type="hidden" name="temuan_id" value="{{ $prefillTemuan->id }}">
                <input type="hidden" name="scan_id" value="{{ $prefillTemuan->scan_id }}">
                <div class="p-3.5 rounded-xl bg-red-950/30 border border-red-500/30 text-xs text-red-300 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-red-400 uppercase tracking-wider text-[10px]">Terkait Temuan Scan:</span>
                        <p class="font-semibold text-white mt-0.5">{{ $prefillTemuan->judul }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-red-500/20 text-red-300">
                        {{ strtoupper($prefillTemuan->tingkat_keparahan) }}
                    </span>
                </div>
            @endif

            {{-- Row: Judul Insiden --}}
            <div>
                <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                    Judul Insiden <span class="text-red-400">*</span>
                </label>
                <input type="text"
                       name="judul"
                       required
                       value="{{ old('judul', $prefillTemuan ? '[TEMUAN] ' . $prefillTemuan->judul : '') }}"
                       placeholder="Contoh: [CRITICAL] SQL Injection pada Endpoint /api/v1/auth"
                       class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-4 py-2.5 text-sm text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff]">
            </div>

            {{-- 3-Col Grid: Target, Keparahan, Status --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Target Asset --}}
                <div>
                    <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                        Target Aset / Host
                    </label>
                    <input type="text"
                           name="target"
                           value="{{ old('target', $prefillTemuan?->scan?->target ?? $prefillScan?->target ?? '') }}"
                           placeholder="https://example.com atau app.local"
                           class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff]">
                </div>

                {{-- Tingkat Keparahan --}}
                <div>
                    <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                        Tingkat Keparahan <span class="text-red-400">*</span>
                    </label>
                    <select name="tingkat_keparahan"
                            required
                            class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-[#00d4ff]">
                        @php
                            $defaultSev = old('tingkat_keparahan', $prefillTemuan ? strtolower($prefillTemuan->tingkat_keparahan) : 'sedang');
                        @endphp
                        <option value="kritis" {{ $defaultSev === 'kritis' ? 'selected' : '' }}>Kritis (CVSS 9.0 - 10.0)</option>
                        <option value="tinggi" {{ $defaultSev === 'tinggi' ? 'selected' : '' }}>Tinggi (CVSS 7.0 - 8.9)</option>
                        <option value="sedang" {{ $defaultSev === 'sedang' ? 'selected' : '' }}>Sedang (CVSS 4.0 - 6.9)</option>
                        <option value="rendah" {{ $defaultSev === 'rendah' ? 'selected' : '' }}>Rendah (CVSS 0.1 - 3.9)</option>
                    </select>
                </div>

                {{-- Status Awal --}}
                <div>
                    <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                        Status Awal
                    </label>
                    <select name="status"
                            class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-[#00d4ff]">
                        <option value="open" {{ old('status') === 'open' ? 'selected' : '' }}>Open (Baru Teridentifikasi)</option>
                        <option value="investigating" {{ old('status') === 'investigating' ? 'selected' : '' }}>Investigating (Analisis Tim)</option>
                        <option value="patching" {{ old('status') === 'patching' ? 'selected' : '' }}>Patching (Perbaikan Kode)</option>
                        <option value="resolved" {{ old('status') === 'resolved' ? 'selected' : '' }}>Resolved (Sudah Ditambal)</option>
                        <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed (Selesai & Tertutup)</option>
                    </select>
                </div>
            </div>

            {{-- 2-Col Grid: PIC & Tautkan Scan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- PIC / Assigned to --}}
                <div>
                    <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                        Penanggung Jawab (PIC / Tim)
                    </label>
                    <input type="text"
                           name="assigned_to"
                           value="{{ old('assigned_to', auth()->user()->nama_lengkap ?? auth()->user()->name) }}"
                           placeholder="Nama Pengembang atau Security Engineer"
                           class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff]">
                </div>

                {{-- Tautkan ke Scan --}}
                @if(!$prefillTemuan)
                <div>
                    <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                        Tautkan ke Riwayat Scan (Opsional)
                    </label>
                    <select name="scan_id"
                            class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-[#00d4ff]">
                        <option value="">-- Tidak Ditautkan --</option>
                        @foreach($scans as $s)
                        <option value="{{ $s->id }}" {{ (old('scan_id') ?? $prefillScan?->id) === $s->id ? 'selected' : '' }}>
                            {{ Str::limit($s->target ?? $s->nama_file, 45) }} ({{ $s->created_at->format('d M Y') }})
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            {{-- Deskripsi Masalah --}}
            <div>
                <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                    Deskripsi Kerentanan / Dampak
                </label>
                <textarea name="deskripsi"
                          rows="4"
                          placeholder="Jelaskan bagaimana kerentanan ini dapat dieksploitasi oleh aktor ancaman dan potensi dampak kerugiannya..."
                          class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-4 py-3 text-xs text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff] leading-relaxed">{{ old('deskripsi', $prefillTemuan?->deskripsi ?? '') }}</textarea>
            </div>

            {{-- Catatan Investigasi --}}
            <div>
                <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                    Catatan Investigasi & Temuan Teknis
                </label>
                <textarea name="catatan_investigasi"
                          rows="3"
                          placeholder="Detail teknis hasil triase, CVE/CWE terkait, payload pengujian, log jejak serangan..."
                          class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-4 py-3 text-xs text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff] leading-relaxed">{{ old('catatan_investigasi', $prefillTemuan ? "Lokasi: " . ($prefillTemuan->lokasi ?? '-') . ($prefillTemuan->cve_id ? " | CVE: " . $prefillTemuan->cve_id : '') : '') }}</textarea>
            </div>

            {{-- Solusi Remediasi / Action Plan --}}
            <div>
                <label class="block text-xs font-semibold text-[#94a3b8] uppercase tracking-wider mb-2">
                    Solusi Remediasi & Langkah Mitigasi
                </label>
                <textarea name="solusi_remediasi"
                          rows="3"
                          placeholder="Panduan perbaikan kode, update patch dependensi, atau rekonfigurasi server yang wajib dilakukan..."
                          class="w-full bg-[#0f1629] border border-[#1e2d4a] rounded-xl px-4 py-3 text-xs text-white placeholder-[#475569] focus:outline-none focus:border-[#00d4ff] leading-relaxed">{{ old('solusi_remediasi', $prefillTemuan?->remediasi ?? '') }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#1e2d4a]">
                <a href="{{ route('insiden.index') }}"
                   class="px-5 py-2.5 rounded-xl bg-[#1e293b] hover:bg-[#334155] text-xs font-medium text-slate-300 hover:text-white transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-amber-600 hover:from-red-500 hover:to-amber-500 text-white text-xs font-semibold shadow-lg shadow-red-950/40 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan & Buka Tiket Insiden</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
