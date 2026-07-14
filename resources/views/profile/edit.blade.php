@extends('layouts.app')
@section('title', 'Edit Profil — RedSim')
@section('header', 'Edit Profil')
@section('subheader', 'Kelola informasi pribadi dan pengaturan keamanan Anda')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile-card.css') }}">
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Profile Card Component --}}
    <div class="pc-card-wrapper mb-8" style="--pc-inner-gradient: linear-gradient(145deg,#60496e8c 0%,#71C4FF44 100%);">
        <div class="pc-behind"></div>
        <div class="pc-card-shell">
            <section class="pc-card">
                <div class="pc-inside">
                    <div class="pc-shine"></div>
                    <div class="pc-glare"></div>
                    <div class="pc-content pc-avatar-content">
                        @if($user->avatar)
                        <img class="avatar" src="{{ $user->avatar }}" alt="Avatar">
                        @else
                        <div class="avatar bg-gradient-to-br from-[#00d4ff] to-[#7c3aed] flex items-center justify-center text-4xl font-bold text-white rounded-full aspect-square" style="width: 100px; margin: 0 auto; top: 20px;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        @endif
                        <div class="pc-user-info">
                            <div class="pc-user-details">
                                <div class="pc-mini-avatar">
                                    @if($user->avatar)
                                    <img src="{{ $user->avatar }}" alt="Avatar">
                                    @else
                                    <div class="w-full h-full bg-gradient-to-br from-[#00d4ff] to-[#7c3aed] flex items-center justify-center text-xs font-bold text-white">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    @endif
                                </div>
                                <div class="pc-user-text">
                                    <div class="pc-handle">{{ '@' . strtolower(str_replace(' ', '', $user->name)) }}</div>
                                    <div class="pc-status">Online</div>
                                </div>
                            </div>
                            <button class="pc-contact-btn" type="button">Contact Me</button>
                        </div>
                    </div>
                    <div class="pc-content">
                        <div class="pc-details">
                            <h3>{{ $user->name }}</h3>
                            <p>{{ ucfirst($user->tier ?? 'Pengguna Biasa') }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Informasi Profil -->
    <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-8">
        <h3 class="text-lg font-semibold mb-4 text-white">Informasi Profil</h3>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Nama Pengguna</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Nomor WhatsApp (OTP)</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required class="w-full px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#25D366] focus:outline-none" placeholder="08xxxx">
                    <p class="text-xs text-[#64748b] mt-1">Nomor ini digunakan untuk masuk menggunakan OTP WhatsApp.</p>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white font-semibold hover:shadow-lg transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <!-- Ubah Kata Sandi -->
    <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-8">
        <h3 class="text-lg font-semibold mb-4 text-white">Ubah Kata Sandi</h3>
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Kata Sandi Saat Ini</label>
                    <input type="password" name="current_password" required class="w-full md:w-1/2 px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Kata Sandi Baru</label>
                    <input type="password" name="password" required class="w-full md:w-1/2 px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#94a3b8] mb-2">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="password_confirmation" required class="w-full md:w-1/2 px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white focus:border-[#00d4ff] focus:outline-none">
                </div>
            </div>

            <div class="flex justify-start pt-4">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#1e2d4a] text-white font-semibold hover:bg-gray-700 transition-colors border border-[#1e2d4a]">Ubah Kata Sandi</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/profile-card.js') }}"></script>
@endpush
@endsection
