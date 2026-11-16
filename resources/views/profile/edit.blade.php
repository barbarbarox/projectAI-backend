@extends('layouts.app')
@section('title', 'Profil & Keamanan — RedSim')
@section('page_feature', 'profil')

@section('content')
@php
    $handle    = '@' . strtolower(str_replace(' ', '', $user->name));
    $tierLabel = match($user->tier ?? 'gratis') {
        'pro'   => 'Pro',
        'admin' => 'Admin',
        default => 'Gratis',
    };
    $tierColor = match($user->tier ?? 'gratis') {
        'pro'   => ['bg' => 'rgba(239,68,68,0.15)', 'border' => 'rgba(239,68,68,0.35)', 'text' => '#fca5a5'],
        'admin' => ['bg' => 'rgba(234,179,8,0.12)',  'border' => 'rgba(234,179,8,0.3)',   'text' => '#fde047'],
        default => ['bg' => 'rgba(239,68,68,0.10)',  'border' => 'rgba(239,68,68,0.25)',  'text' => '#f87171'],
    };
    $quotaPct  = $batasHarian > 0 && $batasHarian < 999
        ? min(100, round(($scanHariIni / $batasHarian) * 100))
        : 0;
    $initials  = strtoupper(substr($user->name, 0, 1));
@endphp

{{-- ══════════════════════════════
     Page Heading
══════════════════════════════ --}}
<div class="mb-6 flex items-end justify-between">
    <div>
        <h1 class="text-3xl font-bold text-white">Profil &amp; keamanan</h1>
        <p class="mt-1 text-sm" style="color:#888;">Kelola identitas dan akses Anda di RedSim.</p>
    </div>
    <span style="font-family:'IBM Plex Mono',monospace;font-size:10px;color:#555;letter-spacing:0.12em;text-transform:uppercase;">ACCOUNT / PROFIL</span>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-5 px-4 py-3 rounded-xl text-sm flex items-center gap-2"
     style="background:rgba(74,222,128,0.08);border:1px solid rgba(74,222,128,0.2);color:#4ade80;">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif
@if(session('error') || $errors->any())
<div class="mb-5 px-4 py-3 rounded-xl text-sm" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);color:#f87171;">
    @if(session('error')){{ session('error') }}@endif
    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
</div>
@endif

{{-- ══════════════════════════════
     Identity Overview — Full Width
══════════════════════════════ --}}
<div class="relative rounded-2xl mb-6 overflow-hidden"
     style="background:#161616;border:1px solid #2a2a2a;border-left:3px solid #ef4444;">

    <div class="flex flex-wrap items-center gap-0 px-8 py-7">
        {{-- Avatar --}}
        <div class="relative shrink-0 mr-5">
            @if($user->avatar)
                <img src="{{ $user->avatar }}" alt="Avatar"
                     class="w-[88px] h-[88px] rounded-full object-cover"
                     style="border:2px solid #3a1f1f;">
            @else
                <div class="w-[88px] h-[88px] rounded-full flex items-center justify-center text-3xl font-black"
                     style="background:linear-gradient(135deg,#dc2626,#991b1b);color:#fff;border:2px solid #3a1f1f;">
                    {{ $initials }}
                </div>
            @endif
            <span class="absolute bottom-1 right-1 w-3 h-3 rounded-full"
                  style="background:#22c55e;border:2px solid #161616;"></span>
        </div>

        {{-- Name block --}}
        <div class="min-w-0 mr-8">
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-2xl font-bold text-white">{{ $user->name }}</h2>
                <span class="px-2.5 py-0.5 rounded text-[11px] font-semibold"
                      style="background:{{ $tierColor['bg'] }};border:1px solid {{ $tierColor['border'] }};color:{{ $tierColor['text'] }};">
                    {{ $tierLabel }}
                </span>
            </div>
            <p class="text-sm mt-1" style="color:#64748b;">{{ $handle }}</p>
            <div class="flex items-center gap-1.5 mt-2">
                <span class="w-2 h-2 rounded-full" style="background:#22c55e;"></span>
                <span class="text-xs" style="color:#64748b;">Online</span>
            </div>
        </div>

        {{-- Vertical divider --}}
        <div class="hidden lg:block self-stretch w-px mr-8" style="background:#2a2a2a;min-height:60px;"></div>

        {{-- Email info --}}
        <div class="hidden lg:block">
            <div style="font-family:'IBM Plex Mono',monospace;font-size:10px;color:#555;letter-spacing:0.12em;text-transform:uppercase;margin-bottom:8px;">Email Akun</div>
            <div class="text-base font-medium text-white mb-1">{{ $user->email }}</div>
            <div class="text-xs" style="color:#64748b;">Akun pribadi</div>
        </div>

        <div class="flex-1"></div>

        {{-- Change photo --}}
        <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" id="avatarForm" class="shrink-0">
            @csrf
            <label for="avatarInput"
                   class="flex items-center gap-2 px-5 py-2.5 rounded-lg cursor-pointer transition-all text-sm font-medium text-white"
                   style="background:#1b1b1b;border:1px solid #2a2a2a;"
                   onmouseover="this.style.borderColor='#ef4444';this.style.color='#ef4444'" onmouseout="this.style.borderColor='#2a2a2a';this.style.color='#fff'">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Ubah foto
                <input id="avatarInput" type="file" name="avatar" class="hidden" accept="image/*"
                       onchange="document.getElementById('avatarForm').submit()">
            </label>
        </form>
    </div>
</div>

{{-- ══════════════════════════════
     Main 2-Column Layout
     LEFT  (3/5 = 60%): Profil Info + Paket
     RIGHT (2/5 = 40%): Password — tall
══════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

    {{-- ── LEFT COLUMN ── --}}
    <div class="lg:col-span-3 flex flex-col gap-6">

        {{-- Card: Informasi Profil --}}
        <div class="rounded-xl overflow-hidden" style="background:#161616;border:1px solid #242424;">
            {{-- Header --}}
            <div class="flex items-center gap-3 px-6 pt-5 pb-4">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                     style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.2);">
                    <svg class="w-4 h-4" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Informasi profil</h3>
                    <p class="text-xs" style="color:#555;">Detail pribadi yang terhubung dengan akun Anda.</p>
                </div>
            </div>

            {{-- Divider --}}
            <div style="height:1px;background:#242424;"></div>

            {{-- Form --}}
            <form method="POST" action="{{ route('profile.update') }}" class="px-6 pt-5 pb-6">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-5 mb-6">
                    {{-- Nama Pengguna --}}
                    <div>
                        <label class="block text-xs font-medium mb-2" style="color:#64748b;">Nama pengguna</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-4 py-3 rounded-lg text-sm focus:outline-none"
                               style="background:#111111;border:1px solid #2a2a2a;color:#f1f5f9;font-family:'JetBrains Mono',monospace;"
                               onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#2a2a2a'">
                    </div>
                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="block text-xs font-medium mb-2" style="color:#64748b;">Nama lengkap</label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}"
                               class="w-full px-4 py-3 rounded-lg text-sm focus:outline-none"
                               style="background:#111111;border:1px solid #2a2a2a;color:#f1f5f9;"
                               onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#2a2a2a'">
                    </div>
                    {{-- Email --}}
                    <div>
                        <label class="block text-xs font-medium mb-2" style="color:#64748b;">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-4 py-3 rounded-lg text-sm focus:outline-none"
                               style="background:#111111;border:1px solid #2a2a2a;color:#f1f5f9;"
                               onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#2a2a2a'">
                    </div>
                    {{-- WhatsApp --}}
                    <div>
                        <label class="block text-xs font-medium mb-2" style="color:#64748b;">Nomor WhatsApp (OTP)</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="w-full px-4 py-3 rounded-lg text-sm focus:outline-none"
                               style="background:#111111;border:1px solid #2a2a2a;color:#f1f5f9;font-family:'JetBrains Mono',monospace;"
                               onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#2a2a2a'"
                               placeholder="08xx...">
                        <p class="text-[10px] mt-1.5" style="color:#555;">Digunakan untuk masuk melalui OTP WhatsApp.</p>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4" style="border-top:1px solid #242424;">
                    <p class="text-xs" style="color:#555;">Pastikan informasi Anda selalu terbaru.</p>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white transition-all"
                            style="background:#e33445;"
                            onmouseover="this.style.background='#c0202f'" onmouseout="this.style.background='#e33445'">
                        Simpan perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Card: Paket & Penggunaan --}}
        <div class="rounded-xl" style="background:#161616;border:1px solid #242424;">
            <div class="px-6 py-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Paket &amp; penggunaan</h3>
                        <p class="text-xs mt-0.5" style="color:#555;">Pantau kuota analisis keamanan Anda.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded text-[10px] font-semibold uppercase"
                          style="background:{{ $tierColor['bg'] }};border:1px solid {{ $tierColor['border'] }};color:{{ $tierColor['text'] }};font-family:'IBM Plex Mono',monospace;letter-spacing:0.08em;">
                        Paket {{ $tierLabel }}
                    </span>
                </div>

                <div style="border-top:1px solid #242424;padding-top:16px;">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm" style="color:#888;">Pemindaian hari ini</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-black text-white" style="font-family:'IBM Plex Mono',monospace;">{{ $scanHariIni }}</span>
                            <span class="text-xs" style="color:#555;">/ {{ $batasHarian < 999 ? $batasHarian . ' scan' : '∞' }}</span>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div style="height:4px;background:#2a2a2a;border-radius:2px;overflow:hidden;margin-bottom:8px;">
                        <div style="width:{{ $quotaPct }}%;height:100%;background:{{ $quotaPct >= 90 ? '#ef4444' : ($quotaPct >= 70 ? '#fbbf24' : '#dc2626') }};border-radius:2px;transition:width 0.5s ease;"></div>
                    </div>

                    @if($scanHariIni === 0)
                        <p class="text-xs" style="color:#555;">Belum ada kuota yang digunakan hari ini.</p>
                    @else
                        <p class="text-xs" style="color:#888;">{{ $quotaPct }}% kuota harian telah digunakan.</p>
                    @endif

                    <div style="font-family:'IBM Plex Mono',monospace;font-size:10px;color:#444;letter-spacing:0.12em;text-transform:uppercase;margin-top:12px;">KUOTA HARIAN</div>
                </div>

                @if(($user->tier ?? 'gratis') === 'gratis')
                <div class="mt-4 pt-4 flex items-center justify-between" style="border-top:1px solid #242424;">
                    <p class="text-xs" style="color:#555;">Upgrade ke Pro untuk scan tak terbatas &amp; fitur lanjutan.</p>
                    <a href="#" class="px-3 py-1.5 rounded text-xs font-semibold text-white transition-all"
                       style="background:linear-gradient(135deg,#dc2626,#991b1b);"
                       onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                        Upgrade
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── RIGHT COLUMN — Ubah kata sandi (tall) ── --}}
    <div class="lg:col-span-2 rounded-xl" style="background:#161616;border:1px solid #242424;" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
        {{-- Header --}}
        <div class="flex items-center gap-3 px-6 pt-5 pb-4">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                 style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.2);">
                <svg class="w-4 h-4" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Ubah kata sandi</h3>
                <p class="text-xs" style="color:#555;">Jaga akses akun tetap dalam kendali Anda.</p>
            </div>
        </div>

        <div style="height:1px;background:#242424;"></div>

        <div class="px-6 pt-5 pb-6">
            {{-- Security advice box --}}
            <div class="flex gap-3 p-3.5 rounded-xl mb-5"
                 style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.18);">
                <svg class="w-4 h-4 shrink-0 mt-0.5" style="color:#ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <div>
                    <div class="text-xs font-semibold mb-0.5" style="color:#fca5a5;">Lindungi akun RedSim Anda</div>
                    <p class="text-[11px] leading-relaxed" style="color:#888;">Pilih kata sandi unik yang tidak digunakan di akun lain.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')

                <div class="flex flex-col gap-4">
                    {{-- Current Password --}}
                    <div>
                        <label class="block text-xs font-medium mb-2" style="color:#64748b;">Kata sandi saat ini</label>
                        <div class="relative">
                            <input :type="showCurrent ? 'text' : 'password'" name="current_password" required
                                   class="w-full px-4 py-3 pr-11 rounded-lg text-sm focus:outline-none"
                                   style="background:#111111;border:1px solid #2a2a2a;color:#f1f5f9;"
                                   onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#2a2a2a'"
                                   placeholder="Masukkan kata sandi saat ini">
                            <button type="button" @click="showCurrent = !showCurrent"
                                    class="absolute right-3 top-1/2 -translate-y-1/2" style="color:#555;">
                                <svg x-show="!showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label class="block text-xs font-medium mb-2" style="color:#64748b;">Kata sandi baru</label>
                        <div class="relative">
                            <input :type="showNew ? 'text' : 'password'" name="password" required
                                   class="w-full px-4 py-3 pr-11 rounded-lg text-sm focus:outline-none"
                                   style="background:#111111;border:1px solid #2a2a2a;color:#f1f5f9;"
                                   onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#2a2a2a'"
                                   placeholder="Buat kata sandi baru">
                            <button type="button" @click="showNew = !showNew"
                                    class="absolute right-3 top-1/2 -translate-y-1/2" style="color:#555;">
                                <svg x-show="!showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Confirm --}}
                    <div>
                        <label class="block text-xs font-medium mb-2" style="color:#64748b;">Konfirmasi kata sandi baru</label>
                        <div class="relative">
                            <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required
                                   class="w-full px-4 py-3 pr-11 rounded-lg text-sm focus:outline-none"
                                   style="background:#111111;border:1px solid #2a2a2a;color:#f1f5f9;"
                                   onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#2a2a2a'"
                                   placeholder="Ulangi kata sandi baru">
                            <button type="button" @click="showConfirm = !showConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2" style="color:#555;">
                                <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-[11px] mt-4" style="color:#555;">Tips: gunakan kombinasi huruf, angka, dan simbol.</p>

                <div class="mt-5 pt-5" style="border-top:1px solid #242424;">
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-semibold text-white transition-all"
                            style="background:#e33445;"
                            onmouseover="this.style.background='#c0202f'" onmouseout="this.style.background='#e33445'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Ubah kata sandi
                    </button>
                    <p class="text-center mt-3 text-[10px]" style="color:#555;">
                        Jangan bagikan kata sandi atau kode OTP Anda.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Footer --}}
<div class="mt-6 flex items-center justify-between pt-4" style="border-top:1px solid #242424;">
    <div class="flex items-center gap-3">
        <span style="font-family:'IBM Plex Mono',monospace;font-size:11px;font-weight:700;color:#f1f5f9;letter-spacing:0.08em;">REDSIM</span>
        <span class="text-xs" style="color:#555;">Analisis keamanan. Simulasi serangan. Perbaikan.</span>
    </div>
    <span style="font-family:'IBM Plex Mono',monospace;font-size:10px;color:#444;">Pengaturan akun pribadi</span>
</div>

@endsection
