@extends('layouts.app')

@section('title', 'Tambah Monitor — RedSim')
@section('header', 'Tambah Monitor Baru')
@section('subheader', 'Monitoring berkala membutuhkan verifikasi domain')
@section('page_feature', 'monitoring')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Info banner --}}
    <div class="flex items-start gap-3 px-4 py-3.5 rounded-xl border text-sm"
         style="background:rgba(99,102,241,0.06); border-color:rgba(99,102,241,0.2);">
        <svg class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="text-indigo-200">
            <strong>Monitoring berkala wajib verifikasi domain.</strong>
            Sistem akan mengarahkan Anda ke proses verifikasi jika domain belum diverifikasi sebelumnya.
        </div>
    </div>

    {{-- Cooldown warning --}}
    @if($cooldownRemaining > 0)
        @php
            $mins = floor($cooldownRemaining / 60);
            $secs = $cooldownRemaining % 60;
            $label = $mins > 0 ? "{$mins} menit {$secs} detik" : "{$secs} detik";
        @endphp
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-amber-300 border border-amber-500/25"
             style="background:rgba(245,158,11,0.08);">
            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Cooldown aktif. Tunggu <strong>{{ $label }}</strong> sebelum membuat monitor baru.
        </div>
    @endif

    {{-- Form Card --}}
    <form action="{{ route('monitoring.store') }}" method="POST" class="space-y-5">
        @csrf
        <div class="rounded-2xl border p-6 space-y-5" style="background:rgba(15,23,42,0.6); border-color:#1e293b;">

            <h3 class="text-base font-semibold text-white border-b border-slate-700/50 pb-3">Detail Target</h3>

            {{-- URL Target --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">URL Target <span class="text-red-400">*</span></label>
                <input type="url" name="target_url" value="{{ old('target_url') }}"
                       placeholder="https://example.com"
                       class="w-full px-4 py-2.5 rounded-xl text-sm text-white border placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/50 transition-all"
                       style="background:rgba(15,23,42,0.8); border-color:#334155;"
                       required>
                @error('target_url')
                    <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Monitor --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Nama Monitor <span class="text-slate-500">(opsional)</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}"
                       placeholder="misal: Website Kantor, Portal Desa, dll"
                       class="w-full px-4 py-2.5 rounded-xl text-sm text-white border placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/50 transition-all"
                       style="background:rgba(15,23,42,0.8); border-color:#334155;"
                       maxlength="100">
            </div>

            <h3 class="text-base font-semibold text-white border-b border-slate-700/50 pb-3 pt-2">Konfigurasi Monitoring</h3>

            {{-- Interval --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">
                    Interval Pemeriksaan <span class="text-red-400">*</span>
                    <span class="text-slate-500 font-normal">(minimum 15 menit)</span>
                </label>
                <select name="interval_menit" class="w-full px-4 py-2.5 rounded-xl text-sm text-white border focus:outline-none focus:ring-1 focus:ring-indigo-500/50 transition-all"
                        style="background:rgba(15,23,42,0.8); border-color:#334155;">
                    <option value="15"  {{ old('interval_menit') == 15  ? 'selected' : '' }}>Setiap 15 menit</option>
                    <option value="30"  {{ old('interval_menit', 60) == 30 ? 'selected' : '' }}>Setiap 30 menit</option>
                    <option value="60"  {{ old('interval_menit', 60) == 60 ? 'selected' : '' }}>Setiap 1 jam (disarankan)</option>
                    <option value="360" {{ old('interval_menit') == 360 ? 'selected' : '' }}>Setiap 6 jam</option>
                    <option value="720" {{ old('interval_menit') == 720 ? 'selected' : '' }}>Setiap 12 jam</option>
                    <option value="1440"{{ old('interval_menit') == 1440? 'selected' : '' }}>Setiap 24 jam</option>
                </select>
            </div>

            {{-- Threshold --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">
                    Sensitivitas Alert
                    <span class="text-slate-500 font-normal">(% perubahan minimum untuk trigger alert)</span>
                </label>
                <select name="threshold_pct" class="w-full px-4 py-2.5 rounded-xl text-sm text-white border focus:outline-none focus:ring-1 focus:ring-indigo-500/50 transition-all"
                        style="background:rgba(15,23,42,0.8); border-color:#334155;">
                    <option value="5"  {{ old('threshold_pct') == 5  ? 'selected' : '' }}>Tinggi — Alert di 5% perubahan</option>
                    <option value="10" {{ old('threshold_pct', 10) == 10 ? 'selected' : '' }}>Normal — Alert di 10% perubahan (disarankan)</option>
                    <option value="25" {{ old('threshold_pct') == 25 ? 'selected' : '' }}>Rendah — Alert di 25% perubahan</option>
                    <option value="50" {{ old('threshold_pct') == 50 ? 'selected' : '' }}>Sangat Rendah — Alert di 50% perubahan</option>
                </select>
            </div>

            {{-- Dynamic Content Toggle --}}
            <div class="flex items-center justify-between px-4 py-3 rounded-xl" style="background:rgba(255,255,255,0.02); border:1px solid #1e293b;">
                <div>
                    <p class="text-sm font-medium text-white">Mode Konten Dinamis</p>
                    <p class="text-xs text-slate-400 mt-0.5">Aktifkan untuk website berita, e-commerce, atau blog yang kontennya sering berubah normal. Hanya elemen statis yang dibandingkan.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer ml-4 shrink-0">
                    <input type="hidden" name="is_dynamic_content" value="0">
                    <input type="checkbox" name="is_dynamic_content" value="1" class="sr-only peer" {{ old('is_dynamic_content') ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            {{-- WhatsApp Alert Toggle --}}
            <div class="flex items-center justify-between px-4 py-3 rounded-xl" style="background:rgba(255,255,255,0.02); border:1px solid #1e293b;">
                <div>
                    <p class="text-sm font-medium text-white">Notifikasi WhatsApp</p>
                    <p class="text-xs text-slate-400 mt-0.5">Kirim pesan WhatsApp jika terdeteksi perubahan mencurigakan.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer ml-4 shrink-0">
                    <input type="hidden" name="alert_wa" value="0">
                    <input type="checkbox" name="alert_wa" value="1" class="sr-only peer" {{ old('alert_wa', true) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                </label>
            </div>

            {{-- Disclaimer --}}
            <div class="flex items-start gap-3 px-4 py-3 rounded-xl" style="background:rgba(220,38,38,0.06); border:1px solid rgba(220,38,38,0.15);">
                <input type="checkbox" name="disclaimer_check" id="disclaimer_check" value="1"
                       class="w-4 h-4 mt-0.5 rounded accent-red-500 shrink-0" required>
                <label for="disclaimer_check" class="text-xs text-slate-300 cursor-pointer leading-relaxed">
                    Saya menyatakan bahwa saya memiliki <strong class="text-white">izin sah</strong> untuk memantau website yang ditentukan di atas. Penggunaan fitur ini untuk memantau website tanpa izin merupakan pelanggaran Syarat Layanan RedSim.
                </label>
            </div>
            @error('disclaimer_check')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('monitoring.index') }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 transition-all">
                ← Kembali
            </a>
            <button type="submit"
                    {{ $cooldownRemaining > 0 ? 'disabled' : '' }}
                    class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 disabled:opacity-40 disabled:cursor-not-allowed"
                    style="background:linear-gradient(135deg,#dc2626,#7c3aed);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Aktifkan Monitor
            </button>
        </div>
    </form>
</div>
@endsection
