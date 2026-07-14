@extends('layouts.app')
@section('title', 'Ensiklopedia Keamanan — RedSim')
@section('header', 'Ensiklopedia Keamanan')
@section('subheader', 'Database pengetahuan keamanan siber')

@section('content')
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    <div class="p-6 rounded-2xl bg-[#0f1629] border border-[#1e2d4a]">
        <h3 class="font-semibold text-[#00d4ff] mb-2">MITRE ATT&CK</h3>
        <p class="text-sm text-[#94a3b8]">Framework taktik dan teknik serangan siber yang digunakan oleh penyerang di seluruh dunia.</p>
    </div>
    <div class="p-6 rounded-2xl bg-[#0f1629] border border-[#1e2d4a]">
        <h3 class="font-semibold text-[#7c3aed] mb-2">CWE (Common Weakness Enumeration)</h3>
        <p class="text-sm text-[#94a3b8]">Daftar kelemahan perangkat lunak dan perangkat keras yang diidentifikasi secara global.</p>
    </div>
    <div class="p-6 rounded-2xl bg-[#0f1629] border border-[#1e2d4a]">
        <h3 class="font-semibold text-green-400 mb-2">OWASP</h3>
        <p class="text-sm text-[#94a3b8]">Panduan keamanan aplikasi web dari Open Web Application Security Project.</p>
    </div>
    <div class="p-6 rounded-2xl bg-[#0f1629] border border-[#1e2d4a]">
        <h3 class="font-semibold text-red-400 mb-2">NVD (CVE)</h3>
        <p class="text-sm text-[#94a3b8]">Database kerentanan nasional berisi ribuan CVE yang teridentifikasi dan terdokumentasi.</p>
    </div>
    <div class="p-6 rounded-2xl bg-[#0f1629] border border-[#1e2d4a]">
        <h3 class="font-semibold text-orange-400 mb-2">CISA KEV</h3>
        <p class="text-sm text-[#94a3b8]">Katalog kerentanan yang diketahui sedang dieksploitasi secara aktif oleh penyerang.</p>
    </div>
    <div class="p-6 rounded-2xl bg-[#0f1629] border border-[#1e2d4a]">
        <h3 class="font-semibold text-yellow-400 mb-2">CAPEC</h3>
        <p class="text-sm text-[#94a3b8]">Enumerasi dan klasifikasi pola serangan umum yang dapat digunakan untuk menyerang sistem.</p>
    </div>
</div>
@endsection
