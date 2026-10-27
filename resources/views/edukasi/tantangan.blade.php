@extends('layouts.app')
@section('title', 'Tantangan Keamanan — RedSim')
@section('header', 'Tantangan Keamanan')
@section('subheader', 'Uji kemampuan siber kamu: Quiz, Kode, Skenario, Kriptografi, hingga Forensik File')

@section('content')
@php
    $formatMeta = [
        'quiz'       => ['label' => 'Quiz',        'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'color' => 'text-blue-400',   'bg' => 'bg-blue-500/10',   'border' => 'border-blue-500/30'],
        'kode'       => ['label' => 'Kode',        'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>', 'color' => 'text-green-400',  'bg' => 'bg-green-500/10',  'border' => 'border-green-500/30'],
        'skenario'   => ['label' => 'Skenario',    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>', 'color' => 'text-yellow-400', 'bg' => 'bg-yellow-500/10', 'border' => 'border-yellow-500/30'],
        'ciphertext' => ['label' => 'Kriptografi', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>', 'color' => 'text-purple-400', 'bg' => 'bg-purple-500/10', 'border' => 'border-purple-500/30'],
        'file'       => ['label' => 'Forensik',    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 'color' => 'text-orange-400', 'bg' => 'bg-orange-500/10', 'border' => 'border-orange-500/30'],
    ];
    $sulitColor = ['mudah' => 'text-green-400 bg-green-500/10', 'sedang' => 'text-yellow-400 bg-yellow-500/10', 'sulit' => 'text-red-400 bg-red-500/10'];
@endphp

<div class="space-y-6">

    {{-- ===== FILTER & SORT BAR ===== --}}
    <form method="GET" action="{{ route('edukasi.tantangan') }}" id="filterForm">
        <div class="bg-[#0f1629] rounded-2xl border border-[#1e2d4a] p-4">
            <div class="flex flex-wrap gap-3 items-center">

                {{-- Search --}}
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#475569]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul soal..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white placeholder-[#475569] focus:border-[#00d4ff] focus:outline-none"
                           onchange="document.getElementById('filterForm').submit()">
                </div>

                {{-- Kategori / Format --}}
                <select name="kategori" onchange="document.getElementById('filterForm').submit()"
                        class="px-3 py-2 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none">
                    <option value="">Semua Format</option>
                    @foreach($formatMeta as $key => $meta)
                    <option value="{{ $key }}" {{ request('kategori') == $key ? 'selected' : '' }}>
                        {{ $meta['label'] }}
                        @if(isset($statsFormat[$key])) ({{ $statsFormat[$key] }}) @endif
                    </option>
                    @endforeach
                </select>

                {{-- Tingkat --}}
                <select name="tingkat" onchange="document.getElementById('filterForm').submit()"
                        class="px-3 py-2 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none">
                    <option value="">Semua Level</option>
                    <option value="mudah"  {{ request('tingkat') == 'mudah'  ? 'selected' : '' }}>Mudah</option>
                    <option value="sedang" {{ request('tingkat') == 'sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="sulit"  {{ request('tingkat') == 'sulit'  ? 'selected' : '' }}>Sulit</option>
                </select>

                {{-- Sort --}}
                <select name="sort" onchange="document.getElementById('filterForm').submit()"
                        class="px-3 py-2 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none">
                    <option value="terbaru"   {{ $sort == 'terbaru'   ? 'selected' : '' }}>Terbaru</option>
                    <option value="poin_desc" {{ $sort == 'poin_desc' ? 'selected' : '' }}>Poin Terbesar</option>
                    <option value="poin_asc"  {{ $sort == 'poin_asc'  ? 'selected' : '' }}>Poin Terkecil</option>
                    <option value="mudah"     {{ $sort == 'mudah'     ? 'selected' : '' }}>Termudah Dulu</option>
                    <option value="sulit"     {{ $sort == 'sulit'     ? 'selected' : '' }}>Tersulit Dulu</option>
                </select>

                {{-- Total --}}
                <span class="text-xs text-[#475569] ml-auto whitespace-nowrap">{{ $tantanganList->count() }} tantangan</span>
            </div>

            {{-- Quick filter badges --}}
            <div class="flex gap-2 flex-wrap mt-3">
                <a href="{{ route('edukasi.tantangan', array_merge(request()->except('kategori'), ['sort' => $sort])) }}"
                   class="px-3 py-1 rounded-full text-xs font-medium transition-all {{ !request('kategori') ? 'bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40' : 'bg-[#1e2d4a] text-[#64748b] hover:text-white' }}">
                    Semua
                </a>
                @foreach($formatMeta as $key => $meta)
                <a href="{{ route('edukasi.tantangan', array_merge(request()->except('kategori'), ['kategori' => $key, 'sort' => $sort])) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium transition-all {{ request('kategori') == $key ? $meta['color'] . ' ' . $meta['bg'] . ' border ' . $meta['border'] : 'bg-[#1e2d4a] text-[#64748b] hover:text-white' }}">
                    {!! $meta['icon'] !!}
                    <span>{{ $meta['label'] }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </form>

    {{-- ===== GRID TANTANGAN ===== --}}
    @if($tantanganList->isEmpty())
    <div class="text-center py-20 text-[#475569]">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#0f1629] border border-[#1e2d4a] flex items-center justify-center text-slate-500">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <p class="text-lg font-semibold text-[#94a3b8]">Tidak ada tantangan ditemukan</p>
        <p class="text-sm mt-1">Coba ubah filter atau kata kunci pencarian.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($tantanganList as $t)
        @php
            $sudah  = in_array($t->id, $sudahDijawab);
            $fmt    = $formatMeta[$t->format_soal ?? 'quiz'] ?? $formatMeta['quiz'];
            $sc     = $sulitColor[$t->tingkat_kesulitan] ?? 'text-gray-400 bg-gray-500/10';
        @endphp
        <a href="{{ route('edukasi.tantangan.detail', $t) }}"
           class="group relative flex flex-col bg-[#0f1629] border rounded-2xl p-5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg cursor-pointer overflow-hidden
                  {{ $sudah ? 'border-green-500/30 hover:shadow-green-500/10' : 'border-[#1e2d4a] hover:border-[#00d4ff]/50 hover:shadow-[#00d4ff]/10' }}">

            {{-- Completed ribbon --}}
            @if($sudah)
            <div class="absolute top-0 right-0">
                <div class="bg-green-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-bl-xl">✓ SELESAI</div>
            </div>
            @endif

            {{-- Header --}}
            <div class="flex items-start gap-3 mb-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 {{ $fmt['bg'] }} {{ $fmt['color'] }}">
                    {!! $fmt['icon'] !!}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $fmt['bg'] }} {{ $fmt['color'] }}">
                            {{ $fmt['label'] }}
                        </span>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $sc }}">
                            {{ ucfirst($t->tingkat_kesulitan) }}
                        </span>
                    </div>
                    <h3 class="text-sm font-semibold text-white leading-snug line-clamp-2 group-hover:text-[#00d4ff] transition-colors">
                        {{ $t->judul }}
                    </h3>
                </div>
            </div>

            {{-- Deskripsi singkat --}}
            <p class="text-xs text-[#64748b] leading-relaxed line-clamp-2 mb-4 flex-1">
                {{ Str::limit($t->deskripsi, 100) }}
            </p>

            {{-- Footer --}}
            <div class="flex items-center justify-between pt-3 border-t border-[#1e2d4a]">
                <div class="flex items-center gap-3">
                    {{-- Poin badge --}}
                    <span class="flex items-center gap-1 text-xs font-bold {{ $sudah ? 'text-green-400' : 'text-[#00d4ff]' }}">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        {{ $t->poin }} poin
                    </span>
                    @if($t->kategori)
                    <span class="text-[10px] text-[#475569]">{{ $t->kategori }}</span>
                    @endif
                </div>
                <span class="text-xs {{ $sudah ? 'text-green-400' : 'text-[#475569] group-hover:text-[#00d4ff]' }} transition-colors flex items-center gap-1">
                    {{ $sudah ? 'Lihat Hasil' : 'Kerjakan' }}
                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
