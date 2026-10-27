@php
    $sections = [
        [
            'id' => 'navigasi',
            'label' => 'Navigasi',
            'children' => [
                [
                    'value' => 'dashboard',
                    'label' => 'Beranda',
                    'url' => route('dashboard'),
                    'active' => request()->routeIs('dashboard'),
                    'icon' => 'home',
                ],
            ],
        ],
        [
            'id' => 'analisis',
            'label' => 'Analisis Keamanan',
            'children' => [
                [
                    'value' => 'analisis.url',
                    'label' => 'Pemindai URL',
                    'url' => route('analisis.url'),
                    'active' => request()->routeIs('analisis.url') || request()->routeIs('analisis.url.*'),
                    'icon' => 'search',
                ],
                [
                    'value' => 'analisis.log',
                    'label' => 'Analisis Log',
                    'url' => route('analisis.log'),
                    'active' => request()->routeIs('analisis.log') || request()->routeIs('analisis.log.*'),
                    'icon' => 'file-text',
                ],
                [
                    'value' => 'analisis.cve-intel',
                    'label' => 'CVE Intelligence',
                    'url' => route('analisis.cve-intel'),
                    'active' => request()->routeIs('analisis.cve-intel') || request()->routeIs('analisis.cve-intel.*'),
                    'icon' => 'shield-alert',
                    'badge' => 'ML',
                    'badge_class' => 'bg-blue-500/20 text-blue-400 border border-blue-500/30',
                ],
                [
                    'value' => 'threat-hunt.index',
                    'label' => 'Threat Hunt by CVE',
                    'url' => route('threat-hunt.index'),
                    'active' => request()->routeIs('threat-hunt.*'),
                    'icon' => 'radar',
                    'badge' => 'ASM',
                    'badge_class' => 'bg-red-500/20 text-red-400 border border-red-500/30 font-bold',
                ],
                [
                    'value' => 'analisis.kode',
                    'label' => 'Analisis Kode',
                    'url' => route('analisis.kode'),
                    'active' => request()->routeIs('analisis.kode') || request()->routeIs('analisis.kode.*'),
                    'icon' => 'code',
                ],
                [
                    'value' => 'laporan.index',
                    'label' => 'Riwayat Scan',
                    'url' => route('laporan.index'),
                    'active' => request()->routeIs('laporan.*'),
                    'icon' => 'history',
                ],
                [
                    'value' => 'temuan.index',
                    'label' => 'Hub Temuan',
                    'url' => route('temuan.index'),
                    'active' => request()->routeIs('temuan.*'),
                    'icon' => 'findings',
                ],
                [
                    'value' => 'insiden.index',
                    'label' => 'Insiden & Remediasi',
                    'url' => route('insiden.index'),
                    'active' => request()->routeIs('insiden.*'),
                    'icon' => 'incident',
                    'badge' => 'SOC',
                    'badge_class' => 'bg-amber-500/20 text-amber-400 border border-amber-500/30 font-semibold',
                ],
                [
                    'value' => 'red-team-lab',
                    'label' => 'Red Team Lab',
                    'url' => route('red-team-lab'),
                    'active' => request()->routeIs('red-team-lab'),
                    'icon' => 'terminal',
                    'badge' => 'LAB',
                    'badge_class' => 'bg-red-500/20 text-red-400 border border-red-500/30',
                ],
            ],
        ],
        [
            'id' => 'edukasi',
            'label' => 'Edukasi',
            'children' => [
                [
                    'value' => 'edukasi.index',
                    'label' => 'Hub Edukasi',
                    'url' => route('edukasi.index'),
                    'active' => request()->routeIs('edukasi.*') && !request()->is('*/leaderboard'),
                    'icon' => 'book',
                ],
                [
                    'value' => 'edukasi.leaderboard',
                    'label' => 'Leaderboard',
                    'url' => url('/edukasi/leaderboard'),
                    'active' => request()->is('*/leaderboard'),
                    'icon' => 'trophy',
                ],
            ],
        ],
        [
            'id' => 'ai',
            'label' => 'AI Tools',
            'children' => [
                [
                    'value' => 'profile.ai',
                    'label' => 'Model AI',
                    'url' => route('profile.ai'),
                    'active' => request()->routeIs('profile.ai'),
                    'icon' => 'sparkles',
                    'badge' => 'AI',
                    'badge_class' => 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30',
                ],
            ],
        ],
    ];

    if (Auth::check() && Auth::user()->tier === 'admin') {
        $sections[] = [
            'id' => 'admin',
            'label' => 'Administrator',
            'is_admin' => true,
            'children' => [
                [
                    'value' => 'admin.index',
                    'label' => 'Dashboard Admin',
                    'url' => route('admin.index'),
                    'active' => request()->routeIs('admin.index'),
                    'icon' => 'adjustments',
                ],
                [
                    'value' => 'admin.benchmark',
                    'label' => 'Benchmark Riset',
                    'url' => route('admin.benchmark'),
                    'active' => request()->routeIs('admin.benchmark*'),
                    'icon' => 'chart',
                    'badge' => 'RISET',
                    'badge_class' => 'bg-purple-500/20 text-purple-400 border border-purple-500/30',
                ],
                [
                    'value' => 'admin.users.index',
                    'label' => 'Manajemen Pengguna',
                    'url' => route('admin.users.index'),
                    'active' => request()->routeIs('admin.users.*'),
                    'icon' => 'users',
                ],
                [
                    'value' => 'admin.ai-config',
                    'label' => 'Konfigurasi AI',
                    'url' => route('admin.ai-config'),
                    'active' => request()->routeIs('admin.ai-config'),
                    'icon' => 'brain',
                    'badge' => 'AI',
                    'badge_class' => 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30',
                ],
                [
                    'value' => 'admin.wa-settings',
                    'label' => 'WhatsApp Gateway',
                    'url' => route('admin.wa-settings'),
                    'active' => request()->routeIs('admin.wa-settings*'),
                    'icon' => 'chat',
                    'badge' => 'WA',
                    'badge_class' => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
                ],
                [
                    'value' => 'admin.tantangan.index',
                    'label' => 'Soal Edukasi',
                    'url' => route('admin.tantangan.index'),
                    'active' => request()->routeIs('admin.tantangan.*'),
                    'icon' => 'book-open',
                ],
                [
                    'value' => 'admin.kesehatan-sistem',
                    'label' => 'Kesehatan Sistem',
                    'url' => route('admin.kesehatan-sistem'),
                    'active' => request()->routeIs('admin.kesehatan-sistem'),
                    'icon' => 'activity',
                ],
                [
                    'value' => 'admin.audit-log',
                    'label' => 'Audit Log',
                    'url' => route('admin.audit-log'),
                    'active' => request()->routeIs('admin.audit-log'),
                    'icon' => 'shield-check',
                ],
            ],
        ];
    }

    $activeSectionIndex = 0;
    $activeItemValue = 'dashboard';
    $openIndices = [];

    foreach ($sections as $sIdx => $sec) {
        $secHasActive = false;
        foreach ($sec['children'] as $kid) {
            if (!empty($kid['active'])) {
                $activeSectionIndex = $sIdx;
                $activeItemValue = $kid['value'];
                $secHasActive = true;
                break;
            }
        }
        if ($secHasActive) {
            $openIndices[] = $sIdx;
        }
    }
    if (empty($openIndices)) {
        $openIndices[] = 0;
    }

    $defaultOpenJson = json_encode($openIndices);
@endphp

<nav class="branched-menu"
     x-data="redsimBranchedMenu({
         defaultOpen: {{ $defaultOpenJson }},
         activeSection: {{ $activeSectionIndex }},
         activeValue: '{{ $activeItemValue }}'
     })"
     style="--bm-w: 240px; --bm-ink: #f5f5f5; --bm-accent: #00d4ff; --bm-line: #27354f; --bm-font: 13.5px; --bm-row: 36px; --bm-indent: 40px; --bm-line-w: 1.5; --bm-draw: 400ms; --bm-fold: 300ms;">
     
    {{-- Active gliding marker --}}
    <span x-ref="marker" class="branched-menu__marker" :data-on="markerOn" aria-hidden="true"></span>

    @foreach($sections as $i => $section)
        @php
            $kids = $section['children'] ?? [];
            $kidsCount = count($kids);
            $bodyH = 12 + $kidsCount * 36;
            $trunkLineD = "M 14 0 V " . (14 + 36 * ($kidsCount - 1));
            $isAdminSection = !empty($section['is_admin']);
        @endphp

        <div class="branched-menu__section"
             :data-open="isOpen({{ $i }}) ? '' : undefined">
             
            {{-- Section Head Header --}}
            <button type="button"
                    x-ref="head_{{ $i }}"
                    class="branched-menu__head"
                    @click="toggle({{ $i }})"
                    aria-expanded="isOpen({{ $i }})"
                    style="{{ $isAdminSection ? 'color: #f87171;' : '' }}">
                <span class="branched-menu__head-title">
                    @if($isAdminSection)
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 shadow-[0_0_6px_rgba(239,68,68,0.8)]"></span>
                    @endif
                    <span>{{ $section['label'] }}</span>
                </span>
                <svg class="branched-menu__head-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            {{-- Section Foldable Body --}}
            <div class="branched-menu__body">
                <div class="branched-menu__fold">
                    <div class="branched-menu__tree" style="height: {{ $bodyH }}px;">
                        {{-- SVG Circuit & Tree Branch Lines --}}
                        <svg class="branched-menu__lines" width="40" height="{{ $bodyH }}" aria-hidden="true">
                            {{-- Base Trunk Vertical Line --}}
                            <path class="branched-menu__base" d="{{ $trunkLineD }}" />
                            
                            {{-- Base Branches for each child --}}
                            @foreach($kids as $k => $kid)
                                @php
                                    $branchD = "M 14 " . (14 + 36 * $k) . " A 10 10 0 0 0 24 " . (24 + 36 * $k) . " H 32";
                                @endphp
                                <path class="branched-menu__base" d="{{ $branchD }}" />
                            @endforeach

                            {{-- Glowing Reach Lines for Active Child --}}
                            @foreach($kids as $k => $kid)
                                @php
                                    $reachD = "M 14 0 V " . (14 + 36 * $k) . " A 10 10 0 0 0 24 " . (24 + 36 * $k) . " H 32";
                                    $reachLen = round(37.71 + 36 * $k, 2);
                                @endphp
                                <path class="branched-menu__reach"
                                      d="{{ $reachD }}"
                                      style="stroke-dasharray: {{ $reachLen }}; stroke-dashoffset: {{ $kid['active'] ? '0' : $reachLen }}; {{ $isAdminSection && $kid['active'] ? 'stroke: #ef4444; filter: drop-shadow(0 0 3px rgba(239,68,68,0.6));' : '' }}" />
                            @endforeach
                        </svg>

                        {{-- Child Menu Items --}}
                        @foreach($kids as $k => $kid)
                            <a href="{{ $kid['url'] }}"
                               class="branched-menu__item"
                               {{ $kid['active'] ? 'data-active aria-current=page' : '' }}
                               style="{{ $isAdminSection && $kid['active'] ? 'color:#f87171; background:rgba(239,68,68,0.09); border-left-color:#ef4444;' : '' }}"
                               tabindex="0">
                                
                                <span class="branched-menu__icon" aria-hidden="true">
                                    @if($kid['icon'] === 'home')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    @elseif($kid['icon'] === 'search')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    @elseif($kid['icon'] === 'file-text')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @elseif($kid['icon'] === 'shield-alert')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
                                    @elseif($kid['icon'] === 'code')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                    @elseif($kid['icon'] === 'radar')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 2a10 10 0 1010 10A10 10 0 0012 2zm0 4a6 6 0 106 6 6 6 0 00-6-6zm0 4a2 2 0 102 2 2 2 0 00-2-2zm0-8v2m0 16v2m-10-10h2m16 0h2"/></svg>
                                    @elseif($kid['icon'] === 'history')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    @elseif($kid['icon'] === 'findings')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    @elseif($kid['icon'] === 'incident')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    @elseif($kid['icon'] === 'terminal')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                                    @elseif($kid['icon'] === 'book')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    @elseif($kid['icon'] === 'trophy')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                    @elseif($kid['icon'] === 'sparkles' || $kid['icon'] === 'brain')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @elseif($kid['icon'] === 'adjustments')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                    @elseif($kid['icon'] === 'chart')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    @elseif($kid['icon'] === 'users')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    @elseif($kid['icon'] === 'chat')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    @elseif($kid['icon'] === 'book-open')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    @elseif($kid['icon'] === 'activity')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    @elseif($kid['icon'] === 'shield-check')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    @endif
                                </span>

                                <span class="branched-menu__label">{{ $kid['label'] }}</span>

                                @if(!empty($kid['badge']))
                                    <span class="branched-menu__badge ml-auto {{ $kid['badge_class'] ?? 'bg-slate-800 text-slate-300' }}">
                                        {{ $kid['badge'] }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</nav>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('redsimBranchedMenu', (config) => ({
        openSections: new Set(config.defaultOpen || [0]),
        activeSection: config.activeSection ?? 0,
        activeValue: config.activeValue || '',
        markerOn: false,

        init() {
            this.$nextTick(() => {
                this.placeMarker(false);
            });
            window.addEventListener('resize', () => {
                this.placeMarker(false);
            });
        },

        isOpen(idx) {
            return this.openSections.has(idx);
        },

        toggle(idx) {
            if (this.openSections.has(idx)) {
                this.openSections.delete(idx);
            } else {
                this.openSections.add(idx);
            }
            this.$nextTick(() => {
                this.placeMarker(true);
            });
        },

        placeMarker(glide = true) {
            const marker = this.$refs.marker;
            const head = this.$refs['head_' + this.activeSection];
            if (!marker || !head) {
                this.markerOn = false;
                return;
            }
            const isShown = this.activeSection >= 0 && this.openSections.has(this.activeSection);
            if (!glide) marker.style.transition = 'none';
            if (isShown) {
                marker.style.top = `${head.offsetTop + (head.offsetHeight - 16) / 2}px`;
            }
            this.markerOn = isShown;
            if (!glide) {
                void marker.offsetHeight;
                marker.style.transition = '';
            }
        }
    }));
});
</script>
