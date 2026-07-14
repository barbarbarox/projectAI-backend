@extends('layouts.app')
@section('title', 'Memproses Analisis — RedSim')
@section('header', 'Memproses Analisis')
@section('subheader', 'Harap tunggu sementara sistem menganalisis target Anda')

@section('content')
<div class="max-w-xl mx-auto mt-8" x-data="{
    status: '{{ $scan->status }}',
    step: '{{ $scan->progress_step ?? 'Memulai...' }}',
    persen: {{ $scan->progress_persen ?? 0 }},
    modeAi: '{{ $scan->mode_ai ?? 'dengan_ai' }}',
    error: '{{ $scan->error_message ?? '' }}',
    timer: null,
    init() {
        if (this.status === 'selesai') {
            window.location = '{{ route('analisis.hasil', $scan) }}';
            return;
        }
        this.timer = setInterval(async () => {
            try {
                const r = await fetch('{{ route('analisis.status', $scan) }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const d = await r.json();
                this.status = d.status;
                this.step = d.progress_step || this.step;
                this.persen = d.progress_persen || this.persen;
                this.error = d.error_message || '';
                if (d.status === 'selesai') {
                    this.persen = 100;
                    this.step = 'Selesai!';
                    clearInterval(this.timer);
                    setTimeout(() => { window.location = '{{ route('analisis.hasil', $scan) }}'; }, 800);
                }
                if (d.status === 'gagal') {
                    clearInterval(this.timer);
                }
            } catch(e) { console.error(e); }
        }, 2000);
    }
}">
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-8">
        {{-- Processing State --}}
        <template x-if="status === 'memproses'">
            <div class="text-center">
                {{-- Animated Spinner --}}
                <div class="w-20 h-20 mx-auto mb-6 relative">
                    <div class="absolute inset-0 rounded-full border-4 border-[#1e2d4a]"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-t-[#00d4ff] border-r-transparent border-b-transparent border-l-transparent animate-spin"></div>
                    <div class="absolute inset-2 rounded-full border-4 border-t-transparent border-r-[#7c3aed] border-b-transparent border-l-transparent animate-spin" style="animation-direction: reverse; animation-duration: 1.5s;"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xs font-bold text-[#00d4ff]" x-text="persen + '%'"></span>
                    </div>
                </div>

                <h3 class="text-lg font-semibold mb-1 text-white">Sedang Menganalisis...</h3>
                <p class="text-sm text-[#94a3b8] mb-6" x-text="step"></p>

                {{-- Progress Bar --}}
                <div class="w-full bg-[#0a0e1a] rounded-full h-3 mb-3 overflow-hidden border border-[#1e2d4a]">
                    <div class="h-full rounded-full bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] transition-all duration-700 ease-out relative overflow-hidden"
                        :style="'width: ' + persen + '%'">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
                    </div>
                </div>

                <p class="text-xs text-[#64748b]" x-text="persen + '% selesai'"></p>

                {{-- Mode Indicator --}}
                <div class="mt-6 flex justify-center gap-2">
                    <span class="px-3 py-1 rounded-full text-[10px] font-medium"
                        :class="modeAi === 'dengan_ai' ? 'bg-[#00d4ff]/10 text-[#00d4ff]' : 'bg-yellow-500/10 text-yellow-400'"
                        x-text="modeAi === 'dengan_ai' ? '🤖 Dengan AI' : '⚡ Tanpa AI'"></span>
                    <span class="px-3 py-1 rounded-full text-[10px] bg-[#1e2d4a] text-[#94a3b8]">{{ ucfirst($scan->tipe_scan) }}</span>
                </div>

                {{-- Step indicators --}}
                <div class="mt-6 grid grid-cols-4 gap-1">
                    @php
                        $steps = [
                            ['label' => 'Inisialisasi', 'threshold' => 10],
                            ['label' => 'Scanning', 'threshold' => 40],
                            ['label' => 'Analisis', 'threshold' => 70],
                            ['label' => 'Finalisasi', 'threshold' => 95],
                        ];
                    @endphp
                    @foreach($steps as $s)
                    <div class="text-center">
                        <div class="w-full h-1 rounded-full mb-1.5 transition-all duration-500"
                            :class="persen >= {{ $s['threshold'] }} ? 'bg-gradient-to-r from-[#00d4ff] to-[#7c3aed]' : 'bg-[#1e2d4a]'"></div>
                        <p class="text-[10px] transition-colors"
                            :class="persen >= {{ $s['threshold'] }} ? 'text-[#00d4ff]' : 'text-[#475569]'">{{ $s['label'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </template>

        {{-- Failed State --}}
        <template x-if="status === 'gagal'">
            <div class="text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-red-500/10 flex items-center justify-center">
                    <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-red-400 mb-2">Analisis Gagal</h3>
                <p class="text-sm text-[#94a3b8] mb-2">Terjadi kesalahan saat memproses.</p>
                <p x-show="error" class="text-xs text-red-400/70 mb-6 px-4 py-2 rounded-lg bg-red-500/5 border border-red-500/10 inline-block" x-text="error"></p>
                <div class="flex justify-center gap-3 mt-4">
                    <a href="{{ url()->previous() }}" class="px-6 py-2.5 rounded-xl bg-[#1e2d4a] text-sm text-white hover:bg-[#2a3a5c] transition-colors">← Kembali</a>
                    <a href="{{ route('dashboard') }}" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-sm text-white hover:shadow-lg transition-all">Dashboard</a>
                </div>
            </div>
        </template>
    </div>
</div>

<style>
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.animate-shimmer { animation: shimmer 2s infinite; }
</style>
@endsection
