@extends('layouts.app')

@section('title', 'Benchmark LLM vs ML — RedSim')
@section('header', 'Benchmark Panel')
@section('subheader', 'Perbandingan empiris prediksi LLM (Gemini/Groq) vs Ensemble ML (Random Forest + XGBoost — 297K NVD CVE)')

@section('content')
<div class="max-w-7xl mx-auto pb-20 space-y-8">

    {{-- ===== TOP KPI CARDS ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Agreement Rate --}}
        <div class="p-5 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 rounded-full opacity-5"
                 style="background: radial-gradient(circle, #10b981, transparent); transform: translate(30%, -30%);"></div>
            <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">Agreement Rate</div>
            <div class="text-4xl font-black mb-1 {{ $agreementRate >= 80 ? 'text-green-400' : ($agreementRate >= 60 ? 'text-yellow-400' : 'text-red-400') }}">
                {{ $agreementRate }}%
            </div>
            <div class="text-xs text-slate-500">LLM & ML setuju pada severity</div>
            <div class="mt-3 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full {{ $agreementRate >= 80 ? 'bg-green-500' : ($agreementRate >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                     style="width: {{ $agreementRate }}%"></div>
            </div>
        </div>

        {{-- Total Diprediksi ML --}}
        <div class="p-5 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 rounded-full opacity-5"
                 style="background: radial-gradient(circle, #06b6d4, transparent); transform: translate(30%, -30%);"></div>
            <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">ML Predictions</div>
            <div class="text-4xl font-black text-cyan-400 mb-1">{{ number_format($totalMlPrediksi) }}</div>
            <div class="text-xs text-slate-500">dari {{ number_format($totalTemuan) }} total temuan</div>
            <div class="mt-3 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                @php $pct = $totalTemuan > 0 ? round(($totalMlPrediksi / $totalTemuan) * 100) : 0; @endphp
                <div class="h-full rounded-full bg-cyan-500" style="width: {{ $pct }}%"></div>
            </div>
        </div>

        {{-- Avg ML Confidence --}}
        <div class="p-5 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 rounded-full opacity-5"
                 style="background: radial-gradient(circle, #8b5cf6, transparent); transform: translate(30%, -30%);"></div>
            <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">Avg ML Confidence</div>
            <div class="text-4xl font-black text-purple-400 mb-1">{{ $avgMlConfidence }}%</div>
            <div class="text-xs text-slate-500">kepercayaan rata-rata Ensemble ML</div>
            <div class="mt-3 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-purple-500" style="width: {{ $avgMlConfidence }}%"></div>
            </div>
        </div>

        {{-- Total Scan --}}
        <div class="p-5 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 rounded-full opacity-5"
                 style="background: radial-gradient(circle, #f59e0b, transparent); transform: translate(30%, -30%);"></div>
            <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">Total Scan Selesai</div>
            <div class="text-4xl font-black text-amber-400 mb-1">{{ number_format($totalScans) }}</div>
            <div class="text-xs text-slate-500">{{ number_format($totalCompared) }} dibandingkan LLM vs ML</div>
            <div class="mt-3">
                <a href="{{ route('admin.benchmark.csv') }}"
                   class="inline-flex items-center gap-1.5 text-xs text-amber-400 hover:text-amber-300 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export CSV untuk Skripsi
                </a>
            </div>
        </div>
    </div>

    {{-- ===== SYSTEM INFO ===== --}}
    <div class="p-4 rounded-xl border border-cyan-500/20 bg-cyan-500/5 flex items-start gap-3">
        <div class="text-cyan-400 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <div class="text-sm font-semibold text-cyan-300 mb-1">Ensemble ML: Random Forest (40%) + XGBoost (60%) — 297.000+ sampel NVD CVE 2020–2026</div>
            <div class="text-xs text-slate-400">
                Ditraining pada dataset CVE (National Vulnerability Database) menggunakan 47 fitur CVSS. Memprediksi tingkat keparahan (CRITICAL/HIGH/MEDIUM/LOW/NONE) menggunakan metode <em>Soft Voting</em>.
                Divalidasi silang dengan prediksi LLM (Gemini/Groq) pada setiap temuan secara real-time. Data ini bisa dijadikan bahan <strong class="text-slate-300">Bab 4 Laporan / Skripsi</strong> sebagai pengujian empiris.
            </div>
        </div>
    </div>

    {{-- ===== DISTRIBUSI SEVERITY: LLM vs ML ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Bar Chart: Distribusi --}}
        <div class="p-6 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <div class="text-sm font-bold text-white">Distribusi Prediksi Severity</div>
                    <div class="text-xs text-slate-500 mt-0.5">LLM (Gemini) vs Ensemble ML (RF+XGB)</div>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-blue-500 inline-block"></span>LLM</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-sm bg-emerald-500 inline-block"></span>ML</span>
                </div>
            </div>

            @php
                $sevColors = [
                    'CRITICAL' => ['bg-red-600',    'bg-red-400/30'],
                    'HIGH'     => ['bg-orange-500', 'bg-orange-400/30'],
                    'MEDIUM'   => ['bg-yellow-500', 'bg-yellow-400/30'],
                    'LOW'      => ['bg-sky-500',    'bg-sky-400/30'],
                    'NONE'     => ['bg-slate-500',  'bg-slate-700/30'],
                ];
                $maxVal = max(array_merge(array_values($llmDist), array_values($mlDist), [1]));
            @endphp

            <div class="space-y-4">
                @foreach($sevOrder as $sev)
                    @php
                        $lv  = $llmDist[$sev] ?? 0;
                        $mv  = $mlDist[$sev] ?? 0;
                        $lp  = round(($lv / $maxVal) * 100);
                        $mp  = round(($mv / $maxVal) * 100);
                        $c   = $sevColors[$sev][0];
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
                            <span class="font-mono font-semibold">{{ $sev }}</span>
                            <span class="text-slate-600">LLM: {{ $lv }} | ML: {{ $mv }}</span>
                        </div>
                        <div class="space-y-1">
                            <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-blue-500 transition-all" style="width: {{ $lp }}%"></div>
                            </div>
                            <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $mp }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Confusion Matrix --}}
        <div class="p-6 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950">
            <div class="mb-5">
                <div class="text-sm font-bold text-white">Confusion Matrix</div>
                <div class="text-xs text-slate-500 mt-0.5">Baris = LLM Prediction, Kolom = ML Prediction</div>
            </div>

            @php
                $matMax = 1;
                foreach($confusionMatrix as $row) {
                    foreach($row as $val) {
                        $matMax = max($matMax, $val);
                    }
                }
                $matColors = ['CRITICAL'=>'#dc2626','HIGH'=>'#ea580c','MEDIUM'=>'#ca8a04','LOW'=>'#0ea5e9'];
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr>
                            <th class="text-left text-slate-600 pb-2 pr-2 text-[10px] font-normal">LLM ↓ / ML →</th>
                            @foreach($labels as $col)
                                <th class="text-center pb-2 font-mono text-[10px]" style="color: {{ $matColors[$col] ?? '#64748b' }}">{{ substr($col,0,3) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($labels as $row)
                        <tr>
                            <td class="pr-2 py-1.5 font-mono text-[10px] font-semibold" style="color: {{ $matColors[$row] ?? '#64748b' }}">{{ substr($row,0,3) }}</td>
                            @foreach($labels as $col)
                                @php
                                    $val = $confusionMatrix[$row][$col] ?? 0;
                                    $isDiag = $row === $col;
                                    $intensity = $matMax > 0 ? $val / $matMax : 0;
                                    $alpha = round($intensity * 0.7, 2);
                                @endphp
                                <td class="text-center py-1.5 px-1">
                                    <div class="rounded-md py-1 px-1 text-xs font-bold min-w-[32px] inline-block
                                                {{ $isDiag ? 'ring-1 ring-green-500/40' : '' }}"
                                         style="background: rgba({{ $isDiag ? '16,185,129' : '220,38,38' }}, {{ $alpha }}); color: {{ $val > 0 ? 'white' : '#475569' }}">
                                        {{ $val }}
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3 text-[10px] text-slate-600 flex items-center gap-2">
                <span class="w-3 h-3 rounded-sm bg-green-500/40 ring-1 ring-green-500 inline-block"></span>
                <span>Diagonal hijau = prediksi setuju (agreement)</span>
            </div>
        </div>
    </div>

    {{-- ===== TREND 7 HARI ===== --}}
    <div class="p-6 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950">
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="text-sm font-bold text-white">Trend Agreement Rate — 7 Hari Terakhir</div>
                <div class="text-xs text-slate-500 mt-0.5">Persentase prediksi LLM & ML yang sama per hari</div>
            </div>
        </div>

        @if(collect($trend)->sum('total') > 0)
            <div class="flex items-end gap-2 h-32">
                @foreach($trend as $day)
                    @php
                        $rate = $day['rate'];
                        $height = $rate !== null ? max(4, $rate) : 4;
                        $color = $rate === null ? 'bg-slate-800' : ($rate >= 80 ? 'bg-green-500' : ($rate >= 60 ? 'bg-yellow-500' : 'bg-red-500'));
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="text-[9px] text-slate-400 font-mono">
                            {{ $rate !== null ? $rate.'%' : '-' }}
                        </div>
                        <div class="w-full rounded-t-sm {{ $color }} transition-all relative group" style="height: {{ $height }}%">
                            {{-- Tooltip --}}
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 bg-slate-800 border border-slate-700 rounded px-2 py-1 text-[9px] text-slate-300 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                {{ $day['agree'] }}/{{ $day['total'] }} setuju
                            </div>
                        </div>
                        <div class="text-[9px] text-slate-600 font-mono">{{ $day['label'] }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-slate-500 text-sm">
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl flex items-center justify-center bg-slate-800/60 border border-slate-700/60">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                Belum ada data ML prediction dalam 7 hari terakhir.<br>
                <span class="text-xs text-slate-600">Jalankan scan untuk mengumpulkan data benchmark.</span>
            </div>
        @endif
    </div>

    {{-- ===== BOTTOM ROW: Tipe Differ + Recent Scans ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Tipe temuan paling sering differ --}}
        <div class="p-6 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950">
            <div class="text-sm font-bold text-white mb-1">Top Kategori Temuan yang "Differ"</div>
            <div class="text-xs text-slate-500 mb-5">Tipe temuan yang paling sering LLM & ML tidak setuju</div>

            @if($differByType->isNotEmpty())
                @php $maxDiffer = $differByType->max('total'); @endphp
                <div class="space-y-3">
                    @foreach($differByType as $item)
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-300 font-medium capitalize">{{ str_replace('_', ' ', $item->tipe) }}</span>
                                <span class="text-red-400 font-bold">{{ $item->total }} perbedaan</span>
                            </div>
                            <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-red-500/70"
                                     style="width: {{ $maxDiffer > 0 ? round(($item->total / $maxDiffer) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-slate-500 text-xs">
                    <div class="w-9 h-9 mx-auto mb-2 rounded-xl flex items-center justify-center bg-slate-800/60 border border-slate-700/60">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    Belum ada data perbedaan prediksi.<br>LLM & ML selalu setuju, atau belum ada scan ML.
                </div>
            @endif
        </div>

        {{-- Scan terbaru dengan ML --}}
        <div class="p-6 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950">
            <div class="text-sm font-bold text-white mb-1">Scan Terbaru dengan ML Prediction</div>
            <div class="text-xs text-slate-500 mb-5">5 scan terakhir yang menggunakan validasi ML</div>

            @if($recentScans->isNotEmpty())
                <div class="space-y-3">
                    @foreach($recentScans as $item)
                        @php
                            $scan = $item['scan'];
                            $ap   = $item['agreement_pct'];
                            $apColor = $ap >= 80 ? 'text-green-400' : ($ap >= 60 ? 'text-yellow-400' : 'text-red-400');
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/40 border border-slate-700/50">
                            <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold {{ $apColor }} shrink-0">
                                {{ $ap }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-medium text-white truncate">{{ $scan->target }}</div>
                                <div class="text-[10px] text-slate-500">
                                    ML: {{ $item['total_ml'] }} temuan | Setuju: {{ $item['agree'] }} | Conf: {{ $item['avg_conf'] }}%
                                </div>
                            </div>
                            <a href="{{ route('analisis.hasil', $scan->id) }}"
                               class="text-[10px] text-slate-500 hover:text-slate-300 transition-colors shrink-0">Lihat →</a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-slate-500 text-xs">
                    <div class="w-9 h-9 mx-auto mb-2 rounded-xl flex items-center justify-center bg-slate-800/60 border border-slate-700/60">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    Belum ada scan dengan data ML prediction.<br>Jalankan scan URL/Kode baru untuk mengumpulkan data.
                </div>
            @endif
        </div>
    </div>

    {{-- ===== CATATAN RISET / AKADEMIK ===== --}}
    <div class="p-6 rounded-2xl border border-purple-500/20 bg-purple-500/5">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-purple-500/10 border border-purple-500/20 text-purple-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5"/></svg>
            </div>
            <div>
                <div class="text-sm font-bold text-purple-300 mb-2">Nilai Akademik — Pilar 2: Kontribusi Riset Asli</div>
                <div class="text-xs text-slate-400 space-y-1.5">
                    <p>• <strong class="text-slate-300">Matkul Metode Penelitian:</strong> Data agreement rate, confusion matrix, dan distribusi severity di atas adalah pengujian empiris LLM vs ML yang dapat langsung dimasukkan ke <strong class="text-slate-300">Bab 4 Laporan</strong>.</p>
                    <p>• <strong class="text-slate-300">Rumusan Masalah terjawab:</strong> "Sejauh mana efektivitas sistem analisis keamanan berbasis RAG+LLM dibanding baseline ML?" → Jawaban: Agreement Rate {{ $agreementRate }}%, Avg ML Confidence {{ $avgMlConfidence }}%.</p>
                    <p>• <strong class="text-slate-300">Gap Riset Terisi:</strong> Paper 2025-2026 membandingkan LLM vs SAST, tapi RedSim membandingkan LLM vs Ensemble ML (Random Forest + XGBoost) yang ditraining dari data CVE — ini kontribusi orisinal.</p>
                    <p>• <strong class="text-slate-300">Export CSV:</strong> Data dapat diexport untuk analisis lanjut di Python/R (precision, recall, F1-score per kelas severity).</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
