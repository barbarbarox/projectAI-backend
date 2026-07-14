@props(['skor' => 0])
@php
    $radius = 54;
    $circumference = 2 * M_PI * $radius;
    $offset = $circumference - ($skor / 100) * $circumference;
    $color = match(true) {
        $skor >= 90 => '#22c55e',
        $skor >= 70 => '#84cc16',
        $skor >= 50 => '#eab308',
        $skor >= 30 => '#f97316',
        default => '#ef4444',
    };
@endphp

<div class="relative inline-flex items-center justify-center">
    <svg class="w-32 h-32 -rotate-90" viewBox="0 0 120 120">
        <circle cx="60" cy="60" r="{{ $radius }}" stroke="#1e2d4a" stroke-width="8" fill="none"/>
        <circle cx="60" cy="60" r="{{ $radius }}" stroke="{{ $color }}" stroke-width="8" fill="none"
                stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                style="transition: stroke-dashoffset 1s ease-in-out"/>
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        <span class="text-3xl font-black" style="color: {{ $color }}">{{ $skor }}</span>
        <span class="text-xs text-[#64748b]">/ 100</span>
    </div>
</div>
