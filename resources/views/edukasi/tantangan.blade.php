@extends('layouts.app')
@section('title', 'Tantangan Keamanan — RedSim')
@section('header', 'Tantangan Keamanan')
@section('subheader', 'Temukan bug dan perbaiki kode rentan untuk mendapatkan poin')

@section('content')
<div class="space-y-4">
    @foreach($tantanganList as $t)
    @php $sudah = in_array($t->id, $sudahDijawab); @endphp
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] overflow-hidden {{ $sudah ? 'opacity-60' : '' }}" x-data="{ buka: false }">
        <button @click="buka = !buka" class="w-full px-6 py-4 flex items-center justify-between hover:bg-[#1e2d4a]/30 transition-colors text-left">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $t->tingkat_kesulitan === 'mudah' ? 'bg-green-500/10' : ($t->tingkat_kesulitan === 'sedang' ? 'bg-yellow-500/10' : 'bg-red-500/10') }}">
                    <span class="text-sm font-bold {{ $t->tingkat_kesulitan === 'mudah' ? 'text-green-400' : ($t->tingkat_kesulitan === 'sedang' ? 'text-yellow-400' : 'text-red-400') }}">{{ $t->poin }}p</span>
                </div>
                <div>
                    <p class="text-sm font-semibold">{{ $t->judul }}</p>
                    <p class="text-xs text-[#64748b]">{{ ucfirst($t->tingkat_kesulitan) }} · {{ strtoupper($t->bahasa_pemrograman ?? 'umum') }} @if($t->referensi_cwe) · {{ $t->referensi_cwe }} @endif</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($sudah)<span class="text-xs text-green-400">✓ Selesai</span>@endif
                <svg class="w-5 h-5 text-[#64748b] transition-transform" :class="buka && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </button>
        <div x-show="buka" x-collapse class="px-6 pb-6 border-t border-[#1e2d4a]">
            <p class="text-sm text-[#94a3b8] mt-4 mb-4">{{ $t->deskripsi }}</p>
            @if($t->kode_soal)
            <pre class="p-4 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-xs text-[#e2e8f0] font-mono overflow-x-auto mb-4"><code>{{ $t->kode_soal }}</code></pre>
            @endif
            @unless($sudah)
            <form method="POST" action="/edukasi/tantangan/{{ $t->id }}/jawab" class="space-y-4">
                @csrf
                @if(is_array($t->pilihan_jawaban))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($t->pilihan_jawaban as $huruf => $teks)
                        <label class="flex items-start gap-3 p-4 rounded-xl border border-[#1e2d4a] bg-[#0a0e1a] hover:border-[#00d4ff] hover:bg-[#00d4ff]/5 cursor-pointer transition-colors">
                            <input type="radio" name="jawaban" value="{{ $huruf }}" required class="mt-1 accent-[#00d4ff]">
                            <div>
                                <span class="font-bold text-[#00d4ff] mr-2">{{ $huruf }}.</span>
                                <span class="text-sm text-[#e2e8f0]">{{ $teks }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                @endif
                <div class="flex justify-end mt-4">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white text-sm font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all">Jawab Tantangan</button>
                </div>
            </form>
            @endunless
        </div>
    </div>
    @endforeach
</div>
@endsection
