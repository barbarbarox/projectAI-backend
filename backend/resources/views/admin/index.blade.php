@extends('layouts.app')
@section('title', 'Panel Admin — RedSim')
@section('header', 'Panel Admin')
@section('subheader', 'Kelola platform dan konfigurasi AI')

@section('content')
<div class="space-y-6">
    {{-- Stats --}}
    <div class="bento-section grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="magic-bento-card p-5 rounded-2xl bg-[#0f1629] border border-[#1e2d4a]">
            <p class="text-xs text-[#64748b] mb-1">Total Pengguna</p>
            <p class="text-2xl font-bold text-white">{{ $stats['total_users'] }}</p>
        </div>
        <div class="magic-bento-card p-5 rounded-2xl bg-[#0f1629] border border-[#1e2d4a]">
            <p class="text-xs text-[#64748b] mb-1">Total Scan</p>
            <p class="text-2xl font-bold text-white">{{ $stats['total_scans'] }}</p>
        </div>
        <div class="magic-bento-card p-5 rounded-2xl bg-[#0f1629] border border-[#1e2d4a]">
            <p class="text-xs text-[#64748b] mb-1">Scan Hari Ini</p>
            <p class="text-2xl font-bold text-white">{{ $stats['scans_hari_ini'] }}</p>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="bento-section grid md:grid-cols-2 gap-4">
        <a href="/admin/ai-config" class="magic-bento-card group p-6 rounded-2xl bg-gradient-to-br from-[#7c3aed]/5 to-[#7c3aed]/10 border border-[#7c3aed]/20">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#7c3aed]/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-[#7c3aed]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-white">Konfigurasi AI</h3>
                    <p class="text-xs text-[#94a3b8]">Kelola API key, provider, dan model AI</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Current AI Configs --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#1e2d4a]">
            <h3 class="font-semibold">Konfigurasi AI Aktif</h3>
        </div>
        <div class="divide-y divide-[#1e2d4a]">
            @forelse($aiConfigs as $config)
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full {{ $config->is_active ? 'bg-green-400' : 'bg-red-400' }}"></div>
                    <div>
                        <p class="text-sm font-medium">{{ $config->label }}</p>
                        <p class="text-xs text-[#64748b]">{{ $config->detected_provider }} · {{ $config->selected_model }}</p>
                    </div>
                </div>
                @if($config->is_default)
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-[#00d4ff]/10 text-[#00d4ff] border border-[#00d4ff]/20">Default</span>
                @endif
            </div>
            @empty
            <div class="px-6 py-8 text-center text-[#64748b] text-sm">
                Belum ada konfigurasi AI. Menggunakan default dari .env (Gemini).
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
