@props(['tingkat' => 'info'])
@php
    $classes = match($tingkat) {
        'kritis' => 'bg-red-500/10 text-red-400 border border-red-500/30',
        'tinggi' => 'bg-orange-500/10 text-orange-400 border border-orange-500/30',
        'sedang' => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/30',
        'rendah' => 'bg-green-500/10 text-green-400 border border-green-500/30',
        'info' => 'bg-blue-500/10 text-blue-400 border border-blue-500/30',
        default => 'bg-gray-500/10 text-gray-400 border border-gray-500/30',
    };
    $label = match($tingkat) {
        'kritis' => 'Kritis',
        'tinggi' => 'Tinggi',
        'sedang' => 'Sedang',
        'rendah' => 'Rendah',
        'info' => 'Info',
        default => $tingkat,
    };
@endphp
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $classes }}">{{ $label }}</span>
