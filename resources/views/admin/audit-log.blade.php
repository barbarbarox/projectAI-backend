@extends('layouts.app')
@section('title', 'Audit Log — Admin RedSim')
@section('header', 'Audit Log')
@section('subheader', 'Catatan aktivitas sistem dan pengguna')

@section('content')
<div class="space-y-6">
    
    {{-- Filter Form --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <form method="GET" action="{{ route('admin.audit-log') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-xs text-[#94a3b8] mb-1">Cari Pengguna</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau email..." class="w-full px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none">
            </div>
            <div class="flex-1">
                <label class="block text-xs text-[#94a3b8] mb-1">Filter Tindakan</label>
                <select name="action" class="w-full px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:border-[#00d4ff] focus:outline-none">
                    <option value="">Semua Tindakan</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 rounded-lg bg-[#7c3aed] text-white text-sm font-semibold hover:bg-[#6d28d9] transition-colors">Terapkan Filter</button>
                @if(request()->hasAny(['search', 'action']))
                    <a href="{{ route('admin.audit-log') }}" class="ml-2 px-4 py-2 rounded-lg bg-[#1e2d4a] text-sm text-[#94a3b8] hover:text-white transition-colors">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Log Table --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#94a3b8]">
                <thead class="text-xs uppercase bg-[#1e2d4a]/50 text-[#e2e8f0]">
                    <tr>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Pengguna</th>
                        <th class="px-6 py-4">Tindakan</th>
                        <th class="px-6 py-4">Deskripsi</th>
                        <th class="px-6 py-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d4a]">
                    @forelse($logs as $log)
                        <tr class="hover:bg-[#1e2d4a]/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td class="px-6 py-4">
                                @if($log->user)
                                    <div class="text-[#e2e8f0]">{{ $log->user->name }}</div>
                                    <div class="text-xs text-[#64748b]">{{ $log->user->email }}</div>
                                @else
                                    <span class="italic text-[#64748b]">Sistem / Guest</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#7c3aed]/10 text-[#7c3aed]">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs">{{ $log->description ?? '-' }}</td>
                            <td class="px-6 py-4 font-mono text-xs">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center">Tidak ada catatan audit yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-[#1e2d4a]">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
