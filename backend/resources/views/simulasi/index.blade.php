@extends('layouts.app')

@section('title', 'Simulasi Serangan — RedSim')
@section('header', 'Simulasi Serangan (Red Team)')
@section('subheader', 'Target: ' . $scan->target)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Back & Header --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('laporan.detail', $scan->id) }}" class="flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Laporan
        </a>
    </div>

    {{-- Info Card --}}
    <div class="p-6 rounded-xl border border-red-500/20" style="background: linear-gradient(135deg, rgba(220,38,38,0.1) 0%, rgba(2,6,9,1) 100%);">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-red-500/20 rounded-lg shrink-0">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-white mb-2">Simulasi Eksploitasi (Red Team Mode)</h3>
                <p class="text-slate-400 text-sm leading-relaxed mb-4">
                    Pilih skenario serangan yang telah digenerate oleh AI berdasarkan celah yang ditemukan pada target <strong>{{ $scan->target }}</strong>. Simulasi ini akan memberikan narasi teknis, proof of concept (PoC), dan gambaran rantai serangan (attack chain) yang realistis.
                </p>
                @if($scan->simulasiSerangan->isEmpty())
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Tidak ada skenario serangan yang terdeteksi. Sistem relatif aman!
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Grid Skenario --}}
    @if($scan->simulasiSerangan->isNotEmpty())
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach($scan->simulasiSerangan as $index => $sim)
        <a href="{{ route('simulasi.show', [$scan->id, $sim->id]) }}" 
           class="group p-5 rounded-xl border border-slate-800 bg-slate-900/50 hover:bg-slate-800 hover:border-slate-700 transition-all duration-300">
            
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-800 text-slate-400 group-hover:bg-red-500/20 group-hover:text-red-400 transition-colors">
                        <span class="text-sm font-bold">#{{ $index + 1 }}</span>
                    </div>
                    <div>
                        <h4 class="text-white font-semibold text-lg group-hover:text-cyan-400 transition-colors">{{ $sim->nama_skenario }}</h4>
                        <p class="text-xs text-slate-500">Profil: {{ $sim->profil_penyerang }}</p>
                    </div>
                </div>
            </div>

            <p class="text-sm text-slate-400 line-clamp-3 mb-5">
                {{ $sim->narasi_eksekutif }}
            </p>

            <div class="flex flex-wrap gap-2 mb-4">
                @if(is_array($sim->fase_attck))
                    @foreach(array_slice($sim->fase_attck, 0, 3) as $fase)
                    <span class="px-2 py-1 bg-slate-800 rounded text-[10px] text-slate-300 border border-slate-700">
                        {{ $fase }}
                    </span>
                    @endforeach
                    @if(count($sim->fase_attck) > 3)
                        <span class="px-2 py-1 bg-slate-800 rounded text-[10px] text-slate-400 border border-slate-700">
                            +{{ count($sim->fase_attck) - 3 }}
                        </span>
                    @endif
                @endif
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                <div class="flex gap-4 text-xs">
                    <div>
                        <span class="text-slate-500 block mb-0.5">Kemungkinan</span>
                        <span class="font-medium {{ $sim->skor_kemungkinan >= 7 ? 'text-red-400' : ($sim->skor_kemungkinan >= 4 ? 'text-yellow-400' : 'text-green-400') }}">
                            {{ $sim->skor_kemungkinan }}/10
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">Dampak</span>
                        <span class="font-medium {{ $sim->skor_dampak >= 7 ? 'text-red-400' : ($sim->skor_dampak >= 4 ? 'text-yellow-400' : 'text-green-400') }}">
                            {{ $sim->skor_dampak }}/10
                        </span>
                    </div>
                </div>
                <div class="text-sm font-medium text-cyan-400 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                    Lihat Detail
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

</div>
@endsection
