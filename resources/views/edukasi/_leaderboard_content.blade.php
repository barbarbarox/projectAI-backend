{{-- ══ Podium Top 5 ══ --}}
<div class="{{ $isAuth ? 'rs-card rounded-xl p-8 overflow-hidden relative' : 'lb-card relative overflow-hidden' }}">
    {{-- Ambient glow --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full opacity-[0.04]"
             style="background:#ef4444;filter:blur(80px);"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 rounded-full opacity-[0.04]"
             style="background:#06b6d4;filter:blur(70px);"></div>
    </div>

    <div class="flex items-center justify-center gap-2.5 mb-8 relative z-10">
        <div class="w-8 h-8 rounded-lg bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center text-yellow-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
        </div>
        <h3 class="text-lg font-bold text-white">Top 5 — Hall of Fame</h3>
    </div>

    @if($leaders->count() >= 3)
    {{-- Desktop Podium --}}
    <div class="hidden md:flex items-end justify-center gap-6 relative z-10 mb-6" style="min-height:300px;">

        {{-- #2 Silver --}}
        @if($leaders->count() >= 2)
        <div class="flex flex-col items-center w-40">
            <div class="relative mb-3">
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-black text-white shadow-lg ring-4"
                     style="background:linear-gradient(135deg,#d1d5db,#6b7280);ring-color:rgba(156,163,175,0.3);">
                    @if(isset($leaders[1]->avatar) && $leaders[1]->avatar)
                    <img src="{{ $leaders[1]->avatar }}" class="w-full h-full rounded-full object-cover" referrerpolicy="no-referrer">
                    @else
                    {{ strtoupper(substr($leaders[1]->name, 0, 1)) }}
                    @endif
                </div>
                <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full flex items-center justify-center text-xs font-black text-white shadow"
                     style="background:linear-gradient(135deg,#d1d5db,#6b7280);">2</div>
            </div>
            <p class="text-sm font-semibold text-white text-center truncate w-full">{{ $leaders[1]->nama_lengkap ?? $leaders[1]->name }}</p>
            <p class="text-xs text-yellow-400 font-bold mt-0.5">{{ $leaders[1]->total_poin }} poin</p>
            <div class="w-full mt-3 rounded-t-xl" style="height:110px;background:linear-gradient(to top,rgba(107,114,128,.3),rgba(156,163,175,.08));border:1px solid rgba(156,163,175,.15);border-bottom:none;"></div>
        </div>
        @endif

        {{-- #1 Gold --}}
        <div class="flex flex-col items-center w-44">
            <div class="relative mb-3">
                <div class="w-20 h-20 rounded-full flex items-center justify-center text-2xl font-black text-white shadow-xl ring-4 animate-pulse-slow"
                     style="background:linear-gradient(135deg,#fbbf24,#d97706);box-shadow:0 0 25px rgba(251,191,36,.35);ring-color:rgba(251,191,36,.3);">
                    @if(isset($leaders[0]->avatar) && $leaders[0]->avatar)
                    <img src="{{ $leaders[0]->avatar }}" class="w-full h-full rounded-full object-cover" referrerpolicy="no-referrer">
                    @else
                    {{ strtoupper(substr($leaders[0]->name, 0, 1)) }}
                    @endif
                </div>
                <div class="absolute -top-5 left-1/2 -translate-x-1/2 w-7 h-7 rounded-full bg-yellow-500/20 border border-yellow-500/40 flex items-center justify-center text-yellow-300">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                </div>
                <div class="absolute -top-1 -left-2 w-6 h-6 rounded-full flex items-center justify-center text-xs font-black text-white shadow"
                     style="background:linear-gradient(135deg,#fbbf24,#d97706);">1</div>
            </div>
            <p class="text-base font-bold text-white text-center truncate w-full">{{ $leaders[0]->nama_lengkap ?? $leaders[0]->name }}</p>
            <p class="text-sm text-yellow-400 font-bold mt-0.5">{{ $leaders[0]->total_poin }} poin</p>
            <div class="w-full mt-3 rounded-t-xl" style="height:155px;background:linear-gradient(to top,rgba(217,119,6,.25),rgba(251,191,36,.05));border:1px solid rgba(251,191,36,.2);border-bottom:none;"></div>
        </div>

        {{-- #3 Bronze --}}
        @if($leaders->count() >= 3)
        <div class="flex flex-col items-center w-40">
            <div class="relative mb-3">
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-black text-white shadow-lg ring-4"
                     style="background:linear-gradient(135deg,#b45309,#92400e);ring-color:rgba(180,83,9,.25);">
                    @if(isset($leaders[2]->avatar) && $leaders[2]->avatar)
                    <img src="{{ $leaders[2]->avatar }}" class="w-full h-full rounded-full object-cover" referrerpolicy="no-referrer">
                    @else
                    {{ strtoupper(substr($leaders[2]->name, 0, 1)) }}
                    @endif
                </div>
                <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full flex items-center justify-center text-xs font-black text-white shadow"
                     style="background:linear-gradient(135deg,#b45309,#92400e);">3</div>
            </div>
            <p class="text-sm font-semibold text-white text-center truncate w-full">{{ $leaders[2]->nama_lengkap ?? $leaders[2]->name }}</p>
            <p class="text-xs text-yellow-400 font-bold mt-0.5">{{ $leaders[2]->total_poin }} poin</p>
            <div class="w-full mt-3 rounded-t-xl" style="height:82px;background:linear-gradient(to top,rgba(146,64,14,.3),rgba(180,83,9,.06));border:1px solid rgba(180,83,9,.15);border-bottom:none;"></div>
        </div>
        @endif
    </div>

    {{-- #4 & #5 Cards --}}
    @if($leaders->count() >= 4)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4 relative z-10">
        @foreach($leaders->slice(3, 2) as $idx => $leader)
        <div class="flex items-center gap-4 px-5 py-4 rounded-xl transition-all duration-200"
             style="background:rgba(2,6,9,0.6);border:1px solid #1e293b;backdrop-filter:blur(8px);">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
                 style="background:#0f172a;color:#64748b;border:1px solid #1e293b;">
                {{ $idx + 4 }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ $leader->nama_lengkap ?? $leader->name }}</p>
            </div>
            <span class="text-sm font-bold text-yellow-400 flex-shrink-0">{{ $leader->total_poin }} poin</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Mobile Podium --}}
    <div class="md:hidden space-y-2 relative z-10">
        @foreach($leaders->take(5) as $idx => $leader)
        @php
            $podiumColors = [
                'background:linear-gradient(135deg,#fbbf24,#d97706);',
                'background:linear-gradient(135deg,#d1d5db,#6b7280);',
                'background:linear-gradient(135deg,#b45309,#92400e);',
                'background:#0f172a;color:#64748b;',
                'background:#0f172a;color:#64748b;',
            ];
        @endphp
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200"
             style="{{ $idx < 3
                 ? 'background:rgba(2,6,9,0.7);border:1px solid ' . ($idx === 0 ? 'rgba(251,191,36,.2)' : ($idx === 1 ? 'rgba(156,163,175,.15)' : 'rgba(180,83,9,.15)'))
                 : 'background:transparent;' }}">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                 style="{{ $podiumColors[$idx] ?? 'background:#0f172a;color:#64748b;' }}">
                {{ $idx + 1 }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ $leader->nama_lengkap ?? $leader->name }}</p>
            </div>
            <span class="text-xs font-bold text-yellow-400 flex-shrink-0">{{ $leader->total_poin }} poin</span>
        </div>
        @endforeach
    </div>

    @else
    <div class="text-center py-14 relative z-10">
        <div class="w-12 h-12 mx-auto mb-3 rounded-xl flex items-center justify-center bg-yellow-500/10 border border-yellow-500/20 text-yellow-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="text-sm" style="color:#94a3b8;">Belum ada cukup peserta. Jadilah yang pertama menyelesaikan tantangan!</p>
    </div>
    @endif
</div>

{{-- ══ Full Ranking Table ══ --}}
<div class="{{ $isAuth ? 'rs-card rounded-xl overflow-hidden' : 'lb-card overflow-hidden' }}">
    <div class="px-6 py-4 flex items-center justify-between" style="border-bottom:1px solid rgba(255,255,255,0.06);">
        <div class="flex items-center gap-3">
            <h4 class="font-semibold text-white">Peringkat Lengkap</h4>
            @if($isAuth)
            <span class="badge-security">{{ $leaders->count() }} pengguna</span>
            @else
            <span style="display:inline-flex;align-items:center;padding:2px 10px;background:rgba(0,212,255,0.08);border:1px solid rgba(0,212,255,0.2);border-radius:99px;font-size:.72rem;font-weight:600;color:#00d4ff;">{{ $leaders->count() }} pengguna</span>
            @endif
        </div>
    </div>

    <table class="{{ $isAuth ? 'rs-table' : '' }}" style="{{ $isAuth ? '' : 'width:100%;border-collapse:collapse;' }}">
        <thead>
            <tr>
                <th style="{{ $isAuth ? 'width:60px;' : 'width:60px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:rgba(180,200,225,.35);padding:8px 12px;text-align:left;' }}">#</th>
                <th style="{{ $isAuth ? '' : 'font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:rgba(180,200,225,.35);padding:8px 12px;text-align:left;' }}">Pengguna</th>
                <th class="text-right" style="{{ $isAuth ? '' : 'font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:rgba(180,200,225,.35);padding:8px 12px;' }}">Poin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaders as $idx => $leader)
            <tr style="{{ $isAuth ? '' : 'border-top:1px solid rgba(255,255,255,0.04);' }}{{ Auth::check() && $leader->id === Auth::id() ? 'border-left:2px solid #06b6d4;background:rgba(6,182,212,0.04);' : '' }}">
                <td style="{{ $isAuth ? '' : 'padding:10px 12px;font-size:.875rem;' }}">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold"
                         style="{{ $idx === 0
                             ? 'background:linear-gradient(135deg,#fbbf24,#d97706);color:#fff;'
                             : ($idx === 1
                                 ? 'background:linear-gradient(135deg,#d1d5db,#6b7280);color:#fff;'
                                 : ($idx === 2
                                     ? 'background:linear-gradient(135deg,#b45309,#92400e);color:#fff;'
                                     : 'background:#0f172a;color:#64748b;border:1px solid #1e293b;')) }}">
                        {{ $idx + 1 }}
                    </div>
                </td>
                <td style="{{ $isAuth ? '' : 'padding:10px 12px;font-size:.875rem;' }}">
                    <p class="text-sm font-medium truncate {{ Auth::check() && $leader->id === Auth::id() ? 'text-cyan-400' : 'text-white' }}">
                        {{ $leader->nama_lengkap ?? $leader->name }}
                        @if(Auth::check() && $leader->id === Auth::id())
                        @if($isAuth)
                        <span class="ml-1 badge-ai text-[9px]">Anda</span>
                        @else
                        <span style="margin-left:4px;display:inline-flex;align-items:center;padding:1px 6px;background:rgba(0,212,255,0.1);border:1px solid rgba(0,212,255,0.25);border-radius:99px;font-size:.65rem;font-weight:700;color:#00d4ff;">Anda</span>
                        @endif
                        @endif
                    </p>
                </td>
                <td class="text-right" style="{{ $isAuth ? '' : 'padding:10px 12px;font-size:.875rem;' }}">
                    <span class="text-sm font-bold text-yellow-400">{{ $leader->total_poin }}</span>
                    <span class="text-xs ml-1" style="color:#64748b;">poin</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="py-10 text-center text-sm" style="color:#64748b;">Belum ada data peringkat.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- CTA for auth layout --}}
@if($isAuth)
<div class="text-center">
    <a href="/edukasi/tantangan"
       class="btn-security inline-flex items-center gap-2"
       style="padding:0.75rem 1.75rem;font-size:0.9375rem;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        <span>Ikuti Tantangan Sekarang</span>
    </a>
</div>
@endif

<style>
@keyframes pulse-slow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(251,204,21,0.4); }
    50%       { box-shadow: 0 0 20px 5px rgba(251,204,21,0.12); }
}
.animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
</style>
