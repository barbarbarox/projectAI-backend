@extends('layouts.app')
@section('title', 'Manajemen Pengguna — Admin RedSim')
@section('header', 'Manajemen Pengguna')
@section('subheader', 'Kelola pendaftaran dan persetujuan akun pengguna')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ !request('status') ? 'bg-[#1e2d4a] text-white' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50' }}">Semua Pengguna</a>
        <a href="{{ route('admin.users.index', ['status' => 'pending']) }}" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request('status') === 'pending' ? 'bg-yellow-500/20 text-yellow-500 border border-yellow-500/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50' }}">
            Menunggu Persetujuan
            @if($pendingCount > 0)
            <span class="px-1.5 py-0.5 rounded-md bg-yellow-500 text-black text-xs font-bold">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.users.index', ['status' => 'verified']) }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ request('status') === 'verified' ? 'bg-green-500/20 text-green-500 border border-green-500/20' : 'text-[#94a3b8] hover:bg-[#1e2d4a]/50' }}">Disetujui</a>
    </div>

    @if(session('success'))
    <div class="px-4 py-3 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="overflow-x-auto bg-[#0f1629] rounded-2xl border border-[#1e2d4a]">
        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase bg-[#1e2d4a]/50 text-[#94a3b8]">
                <tr>
                    <th class="px-6 py-4 rounded-tl-xl">Nama Pengguna</th>
                    <th class="px-6 py-4">Kontak</th>
                    <th class="px-6 py-4">Pendaftaran</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-center rounded-tr-xl">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#1e2d4a]">
                @forelse($users as $user)
                <tr class="hover:bg-[#1e2d4a]/30 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-medium text-white">{{ $user->name }}</p>
                        @if($user->nama_lengkap)
                        <p class="text-xs text-[#64748b]">{{ $user->nama_lengkap }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-[#e2e8f0]">{{ $user->email }}</p>
                        <p class="text-xs text-[#25D366]">{{ $user->phone ?? '-' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-[#e2e8f0]">{{ $user->created_at->format('d M Y') }}</p>
                        <p class="text-xs text-[#64748b]">{{ $user->created_at->format('H:i') }}</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($user->is_verified)
                        <span class="px-3 py-1 bg-green-500/10 text-green-400 border border-green-500/20 rounded-full text-xs font-bold">Disetujui</span>
                        @else
                        <span class="px-3 py-1 bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 rounded-full text-xs font-bold flex items-center justify-center gap-1 inline-flex">
                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 animate-pulse"></span> Pending
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 flex gap-2 justify-center">
                        @if(!$user->is_verified)
                        <form action="{{ route('admin.users.verify', $user) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="px-3 py-1.5 bg-green-500/10 text-green-500 hover:bg-green-500/20 rounded text-xs font-semibold transition-colors">Setujui</button>
                        </form>
                        <form action="{{ route('admin.users.resendNotification', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-blue-500/10 text-blue-500 hover:bg-blue-500/20 rounded text-xs font-semibold transition-colors">Kirim WA</button>
                        </form>
                        @else
                        <form action="{{ route('admin.users.unverify', $user) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="px-3 py-1.5 bg-orange-500/10 text-orange-500 hover:bg-orange-500/20 rounded text-xs font-semibold transition-colors">Batalkan</button>
                        </form>
                        @endif
                        
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini secara permanen?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-red-500/10 text-red-500 hover:bg-red-500/20 rounded text-xs font-semibold transition-colors">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-[#94a3b8]">
                        Tidak ada pengguna ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
