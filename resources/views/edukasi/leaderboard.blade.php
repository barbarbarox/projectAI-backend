@extends('layouts.app')
@section('title', 'Papan Peringkat — RedSim')
@section('header', 'Papan Peringkat')
@section('subheader', 'Pengguna terbaik berdasarkan poin tantangan keamanan.')

@section('content')
{{-- ══ Stat Komunitas (desain referensi: ringkasan-komunitas) ══ --}}
@php
    $totalPengguna  = $leaders->count();
    $poinTertinggi  = $leaders->isNotEmpty() ? $leaders->first()->total_poin : 0;
@endphp

<div class="lb-ringkasan-komunitas">

    {{-- Stat: Total Pengguna --}}
    <div class="lb-stat">
        <div class="lb-stat__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
        </div>
        <div class="lb-stat__data">
            <div class="lb-stat__nilai">{{ $totalPengguna }}</div>
            <div class="lb-stat__label">Total pengguna</div>
        </div>
    </div>

    <div class="lb-stat-pemisah"></div>

    {{-- Stat: Poin Tertinggi --}}
    <div class="lb-stat">
        <div class="lb-stat__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </div>
        <div class="lb-stat__data">
            <div class="lb-stat__nilai">{{ $poinTertinggi }}</div>
            <div class="lb-stat__label">Poin tertinggi</div>
        </div>
    </div>

</div>

{{-- ══ Konten Leaderboard Utama ══ --}}
@include('edukasi._leaderboard_content', ['leaders' => $leaders, 'isAuth' => true])

{{-- ══ Stat Community Styles ══ --}}
<style>
.lb-ringkasan-komunitas {
    display: inline-flex;
    align-items: center;
    gap: 28px;
    padding: 0 0 8px;
    margin-bottom: 8px;
}
.lb-stat {
    display: flex;
    align-items: center;
    gap: 12px;
}
.lb-stat__icon {
    width: 20px;
    height: 20px;
    color: #686a77;
    flex-shrink: 0;
}
.lb-stat__icon svg {
    width: 100%;
    height: 100%;
}
.lb-stat__data {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.lb-stat__nilai {
    font-family: 'IBM Plex Mono', 'Courier New', monospace;
    font-weight: 500;
    font-size: 22px;
    color: #f6f6f8;
    line-height: 1;
}
.lb-stat__label {
    font-family: 'Inter', system-ui, sans-serif;
    font-weight: 400;
    font-size: 12px;
    color: #a0a1ac;
    line-height: 1;
}
.lb-stat-pemisah {
    width: 1px;
    height: 44px;
    background: #292a31;
    flex-shrink: 0;
}
</style>
@endsection
