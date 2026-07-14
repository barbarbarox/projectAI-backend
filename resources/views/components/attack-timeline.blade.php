@props(['faseList' => [], 'aktif' => []])
@php
    $semuaFase = ['reconnaissance','resource-development','initial-access','execution','persistence','privilege-escalation','defense-evasion','credential-access','discovery','lateral-movement','collection','command-and-control','exfiltration','impact'];
    $labelId = [
        'reconnaissance' => 'Pengintaian',
        'resource-development' => 'Pengembangan Sumber Daya',
        'initial-access' => 'Akses Awal',
        'execution' => 'Eksekusi',
        'persistence' => 'Persistensi',
        'privilege-escalation' => 'Eskalasi Hak',
        'defense-evasion' => 'Penghindaran Deteksi',
        'credential-access' => 'Akses Kredensial',
        'discovery' => 'Penemuan',
        'lateral-movement' => 'Pergerakan Lateral',
        'collection' => 'Pengumpulan',
        'command-and-control' => 'Komando & Kontrol',
        'exfiltration' => 'Eksfiltrasi',
        'impact' => 'Dampak',
    ];
@endphp

<div class="overflow-x-auto pb-2">
    <div class="flex items-center gap-1 min-w-max">
        @foreach($semuaFase as $idx => $fase)
        @php $isActive = in_array($fase, $aktif); @endphp
        <div class="flex items-center">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold {{ $isActive ? 'bg-red-500 text-white shadow-lg shadow-red-500/30' : 'bg-[#1e2d4a] text-[#64748b]' }}">{{ $idx + 1 }}</div>
                <p class="text-[9px] mt-1 text-center max-w-[60px] {{ $isActive ? 'text-red-400' : 'text-[#475569]' }}">{{ $labelId[$fase] ?? $fase }}</p>
            </div>
            @if($idx < count($semuaFase) - 1)
            <div class="w-4 h-0.5 {{ $isActive && in_array($semuaFase[$idx+1] ?? '', $aktif) ? 'bg-red-500' : 'bg-[#1e2d4a]' }} mx-0.5 mt-[-12px]"></div>
            @endif
        </div>
        @endforeach
    </div>
</div>
