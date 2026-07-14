@extends('layouts.app')
@section('title', 'Kesehatan Sistem — Admin RedSim')
@section('header', 'Kesehatan Sistem')
@section('subheader', 'Pantau status API dan aktivitas pengguna')

@section('content')
<div class="space-y-6">
    
    {{-- Status Pengguna --}}
    <div class="grid md:grid-cols-2 gap-4">
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6 flex items-center justify-between">
            <div>
                <p class="text-sm text-[#94a3b8] mb-1">Pengguna Online (15m terakhir)</p>
                <div class="text-3xl font-black text-green-400">{{ $usersOnline }}</div>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center text-green-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
        </div>
        <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6 flex items-center justify-between">
            <div>
                <p class="text-sm text-[#94a3b8] mb-1">Pengguna Offline</p>
                <div class="text-3xl font-black text-[#64748b]">{{ $usersOffline }}</div>
            </div>
            <div class="w-12 h-12 rounded-full bg-[#1e2d4a] flex items-center justify-center text-[#94a3b8]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </div>
        </div>
    </div>

    {{-- Status API & Layanan Eksternal --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="text-sm font-semibold text-[#00d4ff] mb-4 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> Status Layanan & API Eksternal</h3>
        <div class="space-y-3">
            
            @php
                $statusColor = function($status) {
                    return match($status) {
                        'ok' => 'text-green-400 bg-green-500/10 border-green-500/20',
                        'warning' => 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20',
                        default => 'text-red-400 bg-red-500/10 border-red-500/20',
                    };
                };
                
                $statusIcon = function($status) {
                    return match($status) {
                        'ok' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
                        'warning' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
                        default => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
                    };
                };
            @endphp

            {{-- AI Provider --}}
            <div class="flex items-center justify-between p-4 rounded-xl border {{ $statusColor($healthStatus['ai_provider']['status']) }}">
                <div>
                    <h4 class="font-semibold text-sm mb-1">Model AI Utama</h4>
                    <p class="text-xs opacity-80">{{ $healthStatus['ai_provider']['message'] }}</p>
                </div>
                <div>{!! $statusIcon($healthStatus['ai_provider']['status']) !!}</div>
            </div>

            {{-- VirusTotal --}}
            <div class="flex items-center justify-between p-4 rounded-xl border {{ $statusColor($healthStatus['virustotal']['status']) }}">
                <div>
                    <h4 class="font-semibold text-sm mb-1">VirusTotal API</h4>
                    <p class="text-xs opacity-80">{{ $healthStatus['virustotal']['message'] }}</p>
                </div>
                <div>{!! $statusIcon($healthStatus['virustotal']['status']) !!}</div>
            </div>

            {{-- URLScan --}}
            <div class="flex items-center justify-between p-4 rounded-xl border {{ $statusColor($healthStatus['urlscan']['status']) }}">
                <div>
                    <h4 class="font-semibold text-sm mb-1">URLScan.io API</h4>
                    <p class="text-xs opacity-80">{{ $healthStatus['urlscan']['message'] }}</p>
                </div>
                <div>{!! $statusIcon($healthStatus['urlscan']['status']) !!}</div>
            </div>

            {{-- Integrated Embedding --}}
            <div class="flex items-center justify-between p-4 rounded-xl border {{ $statusColor($healthStatus['embedding_service']['status']) }}">
                <div>
                    <h4 class="font-semibold text-sm mb-1">Pinecone Integrated Embedding</h4>
                    <p class="text-xs opacity-80">{{ $healthStatus['embedding_service']['message'] }}</p>
                </div>
                <div>{!! $statusIcon($healthStatus['embedding_service']['status']) !!}</div>
            </div>

            {{-- Pinecone Vector DB --}}
            <div class="flex items-center justify-between p-4 rounded-xl border {{ $statusColor($healthStatus['vector_database']['status']) }}">
                <div>
                    <h4 class="font-semibold text-sm mb-1">Pinecone Vector Database</h4>
                    <p class="text-xs opacity-80">{{ $healthStatus['vector_database']['message'] }}</p>
                </div>
                <div>{!! $statusIcon($healthStatus['vector_database']['status']) !!}</div>
            </div>

        </div>
    </div>
</div>
@endsection
