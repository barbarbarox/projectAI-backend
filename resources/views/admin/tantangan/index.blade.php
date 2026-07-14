@extends('layouts.app')
@section('title', 'Manajemen Edukasi — Admin RedSim')
@section('header', 'Manajemen Soal Edukasi')
@section('subheader', 'Kelola daftar tantangan pilihan ganda untuk pengguna')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold">Daftar Soal Edukasi</h3>
        <a href="{{ route('admin.tantangan.create') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white text-sm font-semibold hover:shadow-lg hover:shadow-[#00d4ff]/25 transition-all">
            + Tambah Soal
        </a>
    </div>

    @if(session('success'))
    <div class="px-4 py-3 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase bg-[#1e2d4a]/50 text-[#94a3b8]">
                <tr>
                    <th class="px-6 py-4 rounded-tl-xl">ID</th>
                    <th class="px-6 py-4">Judul</th>
                    <th class="px-6 py-4">Poin</th>
                    <th class="px-6 py-4">Tingkat Kesulitan</th>
                    <th class="px-6 py-4">Jawaban Benar</th>
                    <th class="px-6 py-4 text-center rounded-tr-xl">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e2d4a]">
                @foreach($tantangan as $t)
                <tr class="bg-[#0f1629] hover:bg-[#1e2d4a]/30 transition-colors">
                    <td class="px-6 py-4 font-medium">{{ $t->id }}</td>
                    <td class="px-6 py-4">{{ $t->judul }}</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 bg-[#00d4ff]/10 text-[#00d4ff] rounded text-xs font-bold">{{ $t->poin }}</span></td>
                    <td class="px-6 py-4">{{ ucfirst($t->tingkat_kesulitan) }}</td>
                    <td class="px-6 py-4 font-bold text-green-400">{{ $t->jawaban_benar }}</td>
                    <td class="px-6 py-4 flex gap-2 justify-center">
                        <a href="{{ route('admin.tantangan.edit', $t) }}" class="px-3 py-1.5 bg-yellow-500/10 text-yellow-500 hover:bg-yellow-500/20 rounded text-xs font-semibold transition-colors">Edit</a>
                        <form action="{{ route('admin.tantangan.destroy', $t) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus soal ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-red-500/10 text-red-500 hover:bg-red-500/20 rounded text-xs font-semibold transition-colors">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
