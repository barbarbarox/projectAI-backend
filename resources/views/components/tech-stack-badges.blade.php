{{--
    Tech Stack Badges Component
    Usage: @include('components.tech-stack-badges', ['techStack' => $scan->tech_stack, 'screenshot' => $scan->data_mentah['screenshot_url'] ?? null])
--}}
@php
    $ts = $techStack ?? [];
    if (is_string($ts)) $ts = json_decode($ts, true) ?? [];

    // Badge config: [key, label, icon_type, color_class]
    $badgeGroups = [
        // Row 1: Infrastructure
        'cdn'         => ['CDN',          'cloud',   'cyan'],
        'server'      => ['Server',        'server',  'slate'],
        'os'          => ['OS',            'chip',    'slate'],
        'tls_version' => ['TLS',           'lock',    'emerald'],
        'cloud'       => ['Cloud',         'cloud2',  'blue'],
        // Row 2: Stack
        'language'    => ['Bahasa',        'code',    'violet'],
        'framework'   => ['Framework',     'layers',  'purple'],
        'cms'         => ['CMS',           'grid',    'amber'],
        // Row 3: Frontend & Analytics
        'js_libs'     => ['JS Library',   'bolt',    'yellow'],
        'analytics'   => ['Analytics',    'chart',   'pink'],
    ];

    $colorMap = [
        'cyan'    => ['bg' => 'rgba(0,212,255,0.08)',  'border' => 'rgba(0,212,255,0.18)',  'text' => '#00d4ff',  'dot' => '#00d4ff'],
        'slate'   => ['bg' => 'rgba(100,116,139,0.08)','border' => 'rgba(100,116,139,0.2)', 'text' => '#94a3b8',  'dot' => '#64748b'],
        'emerald' => ['bg' => 'rgba(52,211,153,0.08)', 'border' => 'rgba(52,211,153,0.2)',  'text' => '#34d399',  'dot' => '#10b981'],
        'blue'    => ['bg' => 'rgba(59,130,246,0.08)', 'border' => 'rgba(59,130,246,0.2)',  'text' => '#60a5fa',  'dot' => '#3b82f6'],
        'violet'  => ['bg' => 'rgba(139,92,246,0.08)', 'border' => 'rgba(139,92,246,0.2)',  'text' => '#a78bfa',  'dot' => '#8b5cf6'],
        'purple'  => ['bg' => 'rgba(168,85,247,0.08)', 'border' => 'rgba(168,85,247,0.2)',  'text' => '#c084fc',  'dot' => '#a855f7'],
        'amber'   => ['bg' => 'rgba(245,158,11,0.08)', 'border' => 'rgba(245,158,11,0.2)',  'text' => '#fbbf24',  'dot' => '#f59e0b'],
        'yellow'  => ['bg' => 'rgba(234,179,8,0.08)',  'border' => 'rgba(234,179,8,0.2)',   'text' => '#facc15',  'dot' => '#eab308'],
        'pink'    => ['bg' => 'rgba(236,72,153,0.08)', 'border' => 'rgba(236,72,153,0.2)',  'text' => '#f472b6',  'dot' => '#ec4899'],
    ];
@endphp

@if(!empty($ts) || !empty($screenshot))
<div class="ts-panel">
    {{-- Screenshot Preview --}}
    @if(!empty($screenshot))
    <div class="ts-screenshot-wrap mb-4">
        <div class="ts-screenshot-header">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            <span>Visual Evidence</span>
            <a href="{{ $screenshot }}" target="_blank" rel="noopener" class="ts-screenshot-link">Buka ↗</a>
        </div>
        <div class="ts-screenshot-img-wrap">
            <img src="{{ $screenshot }}"
                 alt="Screenshot target"
                 class="ts-screenshot-img"
                 onerror="this.parentElement.style.display='none'">
            <div class="ts-screenshot-overlay">
                <span class="ts-screenshot-badge">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                    Live Capture
                </span>
            </div>
        </div>
    </div>
    @endif

    {{-- Tech Stack Badges --}}
    @if(!empty($ts))
    <div class="ts-header">
        <svg class="w-3.5 h-3.5 text-[#00d4ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
        <span>Tech Stack Terdeteksi</span>
        @php $totalItems = 0; foreach($ts as $v) { $totalItems += is_array($v) ? count($v) : 1; } @endphp
        <span class="ts-count">{{ $totalItems }} komponen</span>
    </div>

    <div class="ts-badges-wrap">
        @foreach($badgeGroups as $key => [$label, $icon, $color])
            @if(!empty($ts[$key]))
                @php
                    $values = is_array($ts[$key]) ? $ts[$key] : [$ts[$key]];
                    $c = $colorMap[$color];
                @endphp
                @foreach($values as $val)
                <div class="ts-badge"
                    style="background:{{ $c['bg'] }}; border-color:{{ $c['border'] }}; color:{{ $c['text'] }};">
                    <span class="ts-badge-dot" style="background:{{ $c['dot'] }};"></span>
                    <span class="ts-badge-cat">{{ $label }}</span>
                    <span class="ts-badge-val">{{ $val }}</span>
                </div>
                @endforeach
            @endif
        @endforeach

        {{-- Country / ASN --}}
        @if(!empty($ts['country']))
        <div class="ts-badge" style="background:rgba(100,116,139,0.06); border-color:rgba(100,116,139,0.15); color:#64748b;">
            <span class="ts-badge-dot" style="background:#475569;"></span>
            <span class="ts-badge-cat">Country</span>
            <span class="ts-badge-val">{{ $ts['country'] }}</span>
        </div>
        @endif
        @if(!empty($ts['asn']))
        <div class="ts-badge" style="background:rgba(100,116,139,0.06); border-color:rgba(100,116,139,0.15); color:#64748b;">
            <span class="ts-badge-dot" style="background:#475569;"></span>
            <span class="ts-badge-cat">ASN</span>
            <span class="ts-badge-val" title="{{ $ts['asn'] }}">{{ Str::limit($ts['asn'], 30) }}</span>
        </div>
        @endif
    </div>
    @endif
</div>

<style>
.ts-panel {
    background: #0a0e1a;
    border: 1px solid #1e2d4a;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}
.ts-header {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 12px; font-size: 12px; font-weight: 600; color: #94a3b8;
}
.ts-count {
    margin-left: auto; font-size: 10px; color: #475569;
    background: #111827; padding: 1px 8px; border-radius: 999px;
    border: 1px solid #1e2d4a;
}
.ts-badges-wrap {
    display: flex; flex-wrap: wrap; gap: 6px;
}
.ts-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 6px; border: 1px solid;
    font-size: 10px; font-weight: 500; white-space: nowrap;
    transition: opacity 0.2s;
}
.ts-badge:hover { opacity: 0.85; }
.ts-badge-dot {
    width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0;
}
.ts-badge-cat {
    opacity: 0.55; font-size: 9px; text-transform: uppercase; letter-spacing: 0.04em;
}
.ts-badge-val {
    font-weight: 600;
}
/* Screenshot */
.ts-screenshot-wrap { border: 1px solid #1e2d4a; border-radius: 10px; overflow: hidden; }
.ts-screenshot-header {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 12px; background: #0f1629;
    font-size: 10px; font-weight: 600; color: #475569;
    border-bottom: 1px solid #1e2d4a;
}
.ts-screenshot-link {
    margin-left: auto; color: #00d4ff; font-size: 10px;
    text-decoration: none; opacity: 0.7;
}
.ts-screenshot-link:hover { opacity: 1; }
.ts-screenshot-img-wrap {
    position: relative; max-height: 180px; overflow: hidden;
    background: #0a0e1a;
}
.ts-screenshot-img {
    width: 100%; display: block;
    object-fit: cover; object-position: top;
    filter: brightness(0.9);
    transition: filter 0.3s;
}
.ts-screenshot-img-wrap:hover .ts-screenshot-img { filter: brightness(1); }
.ts-screenshot-overlay {
    position: absolute; bottom: 8px; right: 8px;
}
.ts-screenshot-badge {
    display: inline-flex; align-items: center; gap: 4px;
    background: rgba(0,0,0,0.7); color: #34d399;
    font-size: 9px; font-weight: 600; padding: 2px 8px; border-radius: 999px;
    border: 1px solid rgba(52,211,153,0.3); backdrop-filter: blur(4px);
}
</style>
@endif
