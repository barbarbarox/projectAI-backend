@extends('layouts.app')
@section('title', 'Red Team Lab — CVSSv3 Calculator & Attack Visualizer')
@section('header', '🔴 Red Team Lab')
@section('subheader', 'CVSSv3.1 Calculator · Attack Path Visualizer · MITRE ATT&CK Matrix · Risk Scoring')

@section('content')
<div class="max-w-7xl mx-auto pb-24 space-y-8"
     x-data="redTeamLab()"
     x-init="init()">

    {{-- ══════════════════════════════════════════════
         SECTION 1: CVSSv3.1 REAL-TIME CALCULATOR
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

        {{-- ── LEFT: Form Calculator ── --}}
        <div class="xl:col-span-3 p-6 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-red-500/20 border border-red-500/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-white">CVSSv3.1 Base Score Calculator</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 ml-10">Formula resmi FIRST.org — diupdate real-time</p>
                </div>
                <button @click="resetForm()" class="text-xs text-slate-500 hover:text-slate-300 transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reset
                </button>
            </div>

            {{-- Metric Selector Grid --}}
            @php
                $metricLabels = [
                    'AV' => ['label' => 'Attack Vector',           'icon' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3', 'color' => 'red'],
                    'AC' => ['label' => 'Attack Complexity',       'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'color' => 'orange'],
                    'PR' => ['label' => 'Privileges Required',     'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z', 'color' => 'amber'],
                    'UI' => ['label' => 'User Interaction',        'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color' => 'yellow'],
                    'S'  => ['label' => 'Scope',                   'icon' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064', 'color' => 'cyan'],
                    'C'  => ['label' => 'Confidentiality Impact',  'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'color' => 'purple'],
                    'I'  => ['label' => 'Integrity Impact',        'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => 'blue'],
                    'A'  => ['label' => 'Availability Impact',     'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => 'green'],
                ];
                $optionLabels = [
                    'AV' => ['N'=>'Network','A'=>'Adjacent','L'=>'Local','P'=>'Physical'],
                    'AC' => ['L'=>'Low','H'=>'High'],
                    'PR' => ['N'=>'None','L'=>'Low','H'=>'High'],
                    'UI' => ['N'=>'None','R'=>'Required'],
                    'S'  => ['U'=>'Unchanged','C'=>'Changed'],
                    'C'  => ['N'=>'None','L'=>'Low','H'=>'High'],
                    'I'  => ['N'=>'None','L'=>'Low','H'=>'High'],
                    'A'  => ['N'=>'None','L'=>'Low','H'=>'High'],
                ];
                $defaults = ['AV'=>'N','AC'=>'L','PR'=>'N','UI'=>'N','S'=>'U','C'=>'H','I'=>'H','A'=>'H'];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($metricLabels as $key => $meta)
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-400 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-{{ $meta['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}"/>
                        </svg>
                        {{ $meta['label'] }}
                    </label>
                    <div class="flex gap-1.5 flex-wrap">
                        @foreach($optionLabels[$key] as $optKey => $optLabel)
                        <button @click="setMetric('{{ $key }}', '{{ $optKey }}')"
                                :class="form['{{ $key }}'] === '{{ $optKey }}'
                                    ? 'bg-{{ $meta['color'] }}-500/20 border-{{ $meta['color'] }}-500/60 text-{{ $meta['color'] }}-300 ring-1 ring-{{ $meta['color'] }}-500/40'
                                    : 'bg-slate-800/60 border-slate-700 text-slate-500 hover:border-slate-600 hover:text-slate-300'"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-all duration-150">
                            {{ $optKey }}: {{ $optLabel }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Risk Score Extra Inputs --}}
            <div class="mt-5 pt-4 border-t border-slate-800">
                <div class="text-xs font-semibold text-slate-400 mb-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    RedSim Composite Risk Score — Parameter Tambahan
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[11px] text-slate-500 block mb-1">Exploitability Score <span class="text-slate-600">(0–1)</span></label>
                        <input type="range" min="0" max="1" step="0.1" x-model="form.exploitability"
                               @input="calculate()"
                               class="w-full h-1.5 bg-slate-700 rounded-lg accent-orange-500">
                        <div class="text-xs text-orange-400 font-bold mt-1" x-text="parseFloat(form.exploitability).toFixed(1)"></div>
                    </div>
                    <div>
                        <label class="text-[11px] text-slate-500 block mb-1">Asset Value <span class="text-slate-600">(0–1)</span></label>
                        <input type="range" min="0" max="1" step="0.1" x-model="form.asset_value"
                               @input="calculate()"
                               class="w-full h-1.5 bg-slate-700 rounded-lg accent-purple-500">
                        <div class="text-xs text-purple-400 font-bold mt-1" x-text="parseFloat(form.asset_value).toFixed(1)"></div>
                    </div>
                </div>
            </div>

            {{-- Vector String Output --}}
            <div class="mt-4 p-3 rounded-xl bg-black/50 border border-slate-700 flex items-center gap-2">
                <span class="text-[10px] text-slate-600 shrink-0">VECTOR:</span>
                <code class="text-xs text-cyan-400 font-mono flex-1 truncate" x-text="vectorString"></code>
                <button @click="copyVector()" class="shrink-0 text-slate-500 hover:text-slate-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── RIGHT: Score Display ── --}}
        <div class="xl:col-span-2 space-y-4">

            {{-- Score Gauge --}}
            <div class="p-6 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950 text-center">
                <div class="text-xs text-slate-500 uppercase tracking-widest mb-4">CVSSv3.1 Base Score</div>
                <div class="relative inline-flex items-center justify-center w-36 h-36 mx-auto mb-4">
                    <svg class="w-36 h-36 -rotate-90" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="54" fill="none" stroke="#1e293b" stroke-width="10"/>
                        <circle cx="60" cy="60" r="54" fill="none"
                                :stroke="severityColor"
                                stroke-width="10"
                                stroke-linecap="round"
                                :stroke-dasharray="339.3"
                                :stroke-dashoffset="339.3 - (cvssScore / 10) * 339.3"
                                style="transition: stroke-dashoffset 0.5s ease, stroke 0.3s ease"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <div class="text-4xl font-black" :style="'color: ' + severityColor" x-text="cvssScore.toFixed(1)"></div>
                        <div class="text-xs font-bold mt-1" :style="'color: ' + severityColor" x-text="severity"></div>
                    </div>
                </div>

                {{-- ISS / ESS breakdown --}}
                <div class="grid grid-cols-2 gap-3 text-center mt-2">
                    <div class="p-2 rounded-lg bg-slate-800/50">
                        <div class="text-lg font-bold text-blue-400" x-text="iss"></div>
                        <div class="text-[10px] text-slate-500">Impact Sub-Score</div>
                    </div>
                    <div class="p-2 rounded-lg bg-slate-800/50">
                        <div class="text-lg font-bold text-orange-400" x-text="esc"></div>
                        <div class="text-[10px] text-slate-500">Exploit Sub-Score</div>
                    </div>
                </div>
            </div>

            {{-- Risk Score --}}
            <div class="p-5 rounded-2xl border border-purple-500/20 bg-gradient-to-br from-purple-950/30 to-slate-950">
                <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">Composite Risk Score</div>
                <div class="text-xs text-slate-600 mb-3">RedSim Formula: (CVSS×0.4) + (Exploit×0.3) + (Asset×0.3)</div>
                <div class="flex items-end gap-3">
                    <div class="text-4xl font-black text-purple-400" x-text="riskScore.toFixed(2)"></div>
                    <div class="text-xl text-slate-600 mb-1">/5.0</div>
                    <div class="ml-auto px-3 py-1 rounded-full text-xs font-bold"
                         :class="{
                             'bg-red-500/20 text-red-300 border border-red-500/30': riskLevel === 'EXTREME',
                             'bg-orange-500/20 text-orange-300 border border-orange-500/30': riskLevel === 'HIGH',
                             'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30': riskLevel === 'MODERATE',
                             'bg-sky-500/20 text-sky-300 border border-sky-500/30': riskLevel === 'LOW',
                             'bg-slate-500/20 text-slate-400 border border-slate-500/30': riskLevel === 'MINIMAL',
                         }"
                         x-text="riskLevel">
                    </div>
                </div>

                {{-- Formula breakdown --}}
                <div class="mt-3 pt-3 border-t border-slate-800 space-y-1.5">
                    <div class="flex justify-between text-[11px]">
                        <span class="text-slate-500">CVSS (<span x-text="cvssScore.toFixed(1)"></span>/10) × 0.4</span>
                        <span class="text-blue-400 font-mono" x-text="formulaCvss"></span>
                    </div>
                    <div class="flex justify-between text-[11px]">
                        <span class="text-slate-500">Exploitability (<span x-text="parseFloat(form.exploitability).toFixed(1)"></span>) × 0.3</span>
                        <span class="text-orange-400 font-mono" x-text="formulaExploit"></span>
                    </div>
                    <div class="flex justify-between text-[11px]">
                        <span class="text-slate-500">Asset Value (<span x-text="parseFloat(form.asset_value).toFixed(1)"></span>) × 0.3</span>
                        <span class="text-purple-400 font-mono" x-text="formulaAsset"></span>
                    </div>
                    <div class="flex justify-between text-[11px] pt-1 border-t border-slate-800">
                        <span class="text-slate-400 font-semibold">Total (×5 scaled)</span>
                        <span class="text-white font-bold font-mono" x-text="riskScore.toFixed(2) + ' / 5.0'"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         SECTION 2: ATTACK PATH VISUALIZER
    ══════════════════════════════════════════════ --}}
    <div class="p-6 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950">
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 rounded-lg bg-orange-500/20 border border-orange-500/30 flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-bold text-white">Attack Path Visualizer</h2>
                </div>
                <p class="text-xs text-slate-500 ml-10">Buat dan visualisasikan rantai serangan menggunakan notasi ATT&CK Kill Chain</p>
            </div>
            <div class="flex gap-2">
                <button @click="loadTemplate('web')" class="px-3 py-1.5 text-xs rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:text-white hover:border-slate-600 transition-colors">Web App</button>
                <button @click="loadTemplate('ransomware')" class="px-3 py-1.5 text-xs rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:text-white hover:border-slate-600 transition-colors">Ransomware</button>
                <button @click="loadTemplate('apts')" class="px-3 py-1.5 text-xs rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:text-white hover:border-slate-600 transition-colors">APT-style</button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Attack Chain Builder --}}
            <div>
                <div class="text-xs font-semibold text-slate-400 mb-3 uppercase tracking-wider">Rantai Serangan</div>
                <div class="space-y-2" id="attack-chain">
                    <template x-for="(step, idx) in attackChain" :key="idx">
                        <div class="flex items-start gap-2">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5"
                                 :style="'background:' + getTacticColor(step.tactic) + '30; border: 1px solid ' + getTacticColor(step.tactic) + '60; color:' + getTacticColor(step.tactic)"
                                 x-text="idx + 1"></div>
                            <div class="flex-1 grid grid-cols-5 gap-1.5">
                                <select x-model="step.tactic" @change="renderMermaid()"
                                        class="col-span-2 bg-slate-800 border border-slate-700 rounded-lg px-2 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-slate-500">
                                    @foreach($mitreTactics as $tid => $tactic)
                                    <option value="{{ $tid }}">{{ $tactic['name'] }}</option>
                                    @endforeach
                                </select>
                                <input x-model="step.action" @input="renderMermaid()" placeholder="Deskripsi serangan..." type="text"
                                       class="col-span-3 bg-slate-800 border border-slate-700 rounded-lg px-2 py-1.5 text-xs text-slate-300 placeholder-slate-600 focus:outline-none focus:border-slate-500"/>
                            </div>
                            <button @click="removeStep(idx)" class="text-slate-600 hover:text-red-400 transition-colors shrink-0 mt-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <button @click="addStep()" class="mt-3 w-full py-2 rounded-xl border border-dashed border-slate-700 text-xs text-slate-500 hover:border-slate-500 hover:text-slate-300 transition-colors flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Langkah
                </button>
            </div>

            {{-- Mermaid Diagram Output --}}
            <div>
                <div class="text-xs font-semibold text-slate-400 mb-3 uppercase tracking-wider">Diagram Alur Serangan</div>
                <div class="rounded-xl bg-white/5 border border-slate-700 p-4 min-h-[200px] overflow-auto"
                     id="mermaid-output">
                    <div class="mermaid text-sm" id="mermaid-diagram" x-text="mermaidCode"></div>
                </div>
                <div class="mt-2 flex gap-2">
                    <button @click="copyMermaid()" class="px-3 py-1.5 text-xs rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:text-white transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Salin Kode Mermaid
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         SECTION 3: MITRE ATT&CK MATRIX HEATMAP
    ══════════════════════════════════════════════ --}}
    <div class="p-6 rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-8 h-8 rounded-lg bg-cyan-500/20 border border-cyan-500/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold text-white">MITRE ATT&CK Matrix — Kill Chain Mapping</h2>
        </div>
        <p class="text-xs text-slate-500 mb-5 ml-10">Klik taktik untuk highlight. Warna menunjukkan fase dalam rantai serangan.</p>

        <div class="overflow-x-auto pb-2">
            <div class="flex gap-2 min-w-max">
                @foreach($mitreTactics as $tid => $tactic)
                <div class="w-32 shrink-0">
                    {{-- Header Taktik --}}
                    <div class="px-2 py-2 rounded-t-lg text-center cursor-pointer transition-all"
                         :style="selectedTactic === '{{ $tid }}'
                             ? 'background: {{ $tactic['color'] }}40; border: 1px solid {{ $tactic['color'] }}80'
                             : 'background: {{ $tactic['color'] }}15; border: 1px solid {{ $tactic['color'] }}30'"
                         @click="selectedTactic = selectedTactic === '{{ $tid }}' ? null : '{{ $tid }}'">
                        <div class="text-[8px] font-mono text-slate-500">{{ $tid }}</div>
                        <div class="text-[10px] font-bold leading-tight mt-0.5" style="color: {{ $tactic['color'] }}">
                            {{ $tactic['name'] }}
                        </div>
                    </div>
                    {{-- Teknik --}}
                    <div class="space-y-1 mt-1">
                        @foreach($tactic['techniques'] as $i => $tech)
                        <div class="px-2 py-1.5 rounded-md text-center transition-all cursor-default"
                             :style="selectedTactic === '{{ $tid }}'
                                 ? 'background: {{ $tactic['color'] }}25; border: 1px solid {{ $tactic['color'] }}40'
                                 : 'background: #0f172a; border: 1px solid #1e293b'"
                             x-tooltip="'{{ $tech }}'">
                            <div class="text-[9px] font-mono font-bold" style="color: {{ $tactic['color'] }}">{{ $tech }}</div>
                            @php
                                $names = ['T1595'=>'Active Scan','T1592'=>'Gather Victim','T1589'=>'Gather Victim ID','T1591'=>'Gather Org','T1598'=>'Phishing Info','T1583'=>'Acquire Infra','T1584'=>'Compromise Infra','T1585'=>'Est Accounts','T1586'=>'Compromise Acc','T1587'=>'Dev Capabilities','T1190'=>'Exploit Public App','T1566'=>'Phishing','T1078'=>'Valid Accounts','T1189'=>'Drive-by','T1195'=>'Supply Chain','T1059'=>'Command Line','T1203'=>'Client Exploit','T1072'=>'Software Deploy','T1106'=>'API Exec','T1129'=>'Module Load','T1053'=>'Sched Task','T1136'=>'Create Account','T1505'=>'Server Software','T1543'=>'Create Service','T1574'=>'Hijack Flow','T1548'=>'Abuse Elevation','T1134'=>'Token Manip','T1068'=>'Privilege Esc','T1611'=>'Escape to Host','T1070'=>'Indicator Remove','T1562'=>'Disable Defense','T1027'=>'Obfuscation','T1055'=>'Process Inject','T1110'=>'Brute Force','T1555'=>'Cred Stores','T1552'=>'Unsecured Creds','T1539'=>'Steal Cookies','T1557'=>'MITM','T1082'=>'Sys Info','T1083'=>'File Discovery','T1046'=>'Port Scan','T1018'=>'Remote Discovery','T1057'=>'Process Discovery','T1021'=>'Remote Service','T1210'=>'Remote Exploit','T1534'=>'Internal Spear','T1550'=>'Alt Auth Material','T1563'=>'Remote Session','T1005'=>'Data from Local','T1039'=>'Data from Network','T1025'=>'Data from Drive','T1114'=>'Email Collection','T1071'=>'C2 Protocol','T1090'=>'Proxy','T1095'=>'Non-App Layer','T1102'=>'Web Service','T1132'=>'Data Encoding','T1041'=>'Exfil over C2','T1048'=>'Exfil Alt Proto','T1052'=>'Exfil Physical','T1030'=>'Data Transfer Limit','T1567'=>'Exfil Web Service','T1485'=>'Data Destruction','T1486'=>'Ransomware','T1489'=>'Service Stop','T1491'=>'Defacement','T1499'=>'Endpoint DoS'];
                            @endphp
                            <div class="text-[8px] text-slate-600 truncate leading-tight mt-0.5">{{ $names[$tech] ?? $tech }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="mt-4 p-3 rounded-xl bg-slate-800/30 border border-slate-700 flex items-start gap-2">
            <svg class="w-4 h-4 text-cyan-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-slate-400">
                Klik header taktik untuk highlight semua teknik di kolom tersebut. Teknik yang dipilih di Attack Chain Builder
                akan otomatis ter-highlight di matrix ini. Data ini menggunakan framework <strong class="text-slate-300">MITRE ATT&CK Enterprise v15</strong>.
            </p>
        </div>
    </div>

</div>

{{-- Mermaid.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script>
mermaid.initialize({
    startOnLoad: false,
    theme: 'dark',
    themeVariables: {
        primaryColor: '#1e293b',
        primaryTextColor: '#e2e8f0',
        primaryBorderColor: '#334155',
        lineColor: '#64748b',
        secondaryColor: '#0f172a',
        tertiaryColor: '#020617',
        edgeLabelBackground: '#1e293b',
        fontSize: '11px',
    },
    flowchart: { curve: 'basis', htmlLabels: true }
});

function redTeamLab() {
    return {
        form: {
            AV: 'N', AC: 'L', PR: 'N', UI: 'N', S: 'U', C: 'H', I: 'H', A: 'H',
            exploitability: 0.8,
            asset_value: 0.8,
        },
        cvssScore: 0, severity: 'NONE', severityColor: '#64748b',
        iss: 0, esc: 0,
        riskScore: 0, riskLevel: 'MINIMAL',
        formulaCvss: '0.000', formulaExploit: '0.000', formulaAsset: '0.000',
        vectorString: 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H',
        attackChain: [],
        mermaidCode: '',
        selectedTactic: null,
        calcTimeout: null,

        tacticColors: @json(collect($mitreTactics)->mapWithKeys(fn($t, $k) => [$k => $t['color']])),
        tacticNames:  @json(collect($mitreTactics)->mapWithKeys(fn($t, $k) => [$k => $t['name']])),

        init() {
            this.attackChain = [
                { tactic: 'TA0001', action: 'Eksploitasi SQL Injection di /login' },
                { tactic: 'TA0006', action: 'Dump credential dari database' },
                { tactic: 'TA0004', action: 'Login sebagai admin dengan credential curian' },
                { tactic: 'TA0010', action: 'Exfiltrate data sensitif pengguna' },
            ];
            this.calculate();
            this.renderMermaid();
        },

        setMetric(key, val) {
            this.form[key] = val;
            this.calculate();
            this.updateVectorString();
        },

        updateVectorString() {
            this.vectorString = `CVSS:3.1/AV:${this.form.AV}/AC:${this.form.AC}/PR:${this.form.PR}/UI:${this.form.UI}/S:${this.form.S}/C:${this.form.C}/I:${this.form.I}/A:${this.form.A}`;
        },

        calculate() {
            clearTimeout(this.calcTimeout);
            this.calcTimeout = setTimeout(() => {
                this.updateVectorString();
                fetch('{{ route("cvss.calculate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form)
                })
                .then(r => r.json())
                .then(d => {
                    if (!d.success) return;
                    this.cvssScore = d.cvss.score;
                    this.severity  = d.cvss.severity;
                    this.iss       = d.cvss.iss;
                    this.esc       = d.cvss.esc;
                    const sc = { 'CRITICAL':'#dc2626', 'HIGH':'#ea580c', 'MEDIUM':'#ca8a04', 'LOW':'#3b82f6', 'NONE':'#64748b' };
                    this.severityColor = sc[this.severity] || '#64748b';
                    if (d.risk) {
                        this.riskScore  = d.risk.risk_score;
                        this.riskLevel  = d.risk.risk_level;
                        this.formulaCvss    = d.risk.formula.cvss_contrib.toFixed(3);
                        this.formulaExploit = d.risk.formula.exploit_contrib.toFixed(3);
                        this.formulaAsset   = d.risk.formula.asset_contrib.toFixed(3);
                    }
                })
                .catch(() => {});
            }, 150);
        },

        resetForm() {
            this.form = { AV:'N', AC:'L', PR:'N', UI:'N', S:'U', C:'N', I:'N', A:'N', exploitability: 0.5, asset_value: 0.5 };
            this.calculate();
        },

        copyVector() {
            navigator.clipboard.writeText(this.vectorString);
        },

        getTacticColor(tid) {
            return this.tacticColors[tid] || '#64748b';
        },

        addStep() {
            this.attackChain.push({ tactic: 'TA0001', action: '' });
        },

        removeStep(idx) {
            this.attackChain.splice(idx, 1);
            this.renderMermaid();
        },

        renderMermaid() {
            if (this.attackChain.length < 1) {
                this.mermaidCode = '';
                return;
            }
            let code = 'flowchart LR\n';
            this.attackChain.forEach((step, i) => {
                const action = (step.action || 'Step ' + (i+1)).replace(/"/g, "'").substring(0, 35);
                const tname  = (this.tacticNames[step.tactic] || step.tactic).substring(0, 15);
                code += `    N${i}["<b>${i+1}. ${action}</b><br/><small>${tname}</small>"]\n`;
                code += `    style N${i} fill:${this.getTacticColor(step.tactic)}25,stroke:${this.getTacticColor(step.tactic)}80,color:#e2e8f0\n`;
                if (i > 0) {
                    code += `    N${i-1} --> N${i}\n`;
                }
            });
            this.mermaidCode = code;
            this.$nextTick(() => {
                const el = document.getElementById('mermaid-diagram');
                if (el) {
                    el.removeAttribute('data-processed');
                    el.innerHTML = code;
                    mermaid.init(undefined, el);
                }
            });
        },

        copyMermaid() {
            navigator.clipboard.writeText(this.mermaidCode);
        },

        loadTemplate(type) {
            const templates = {
                web: [
                    { tactic: 'TA0043', action: 'Recon — enumerate direktori & endpoint' },
                    { tactic: 'TA0001', action: 'Exploit SQLi di form login' },
                    { tactic: 'TA0006', action: 'Dump tabel users dari database' },
                    { tactic: 'TA0004', action: 'Login admin dengan kredensial curian' },
                    { tactic: 'TA0010', action: 'Exfiltrate data PII pengguna' },
                ],
                ransomware: [
                    { tactic: 'TA0001', action: 'Phishing email dengan attachment malicious' },
                    { tactic: 'TA0002', action: 'Eksekusi macro VBA untuk download payload' },
                    { tactic: 'TA0003', action: 'Tambah scheduled task untuk persistence' },
                    { tactic: 'TA0008', action: 'Lateral movement via SMB/WMI' },
                    { tactic: 'TA0040', action: 'Enkripsi file + drop ransom note' },
                ],
                apts: [
                    { tactic: 'TA0042', action: 'Develop custom C2 implant' },
                    { tactic: 'TA0001', action: 'Supply chain compromise via vendor update' },
                    { tactic: 'TA0003', action: 'Install backdoor di sistem target' },
                    { tactic: 'TA0005', action: 'Disable EDR & logging' },
                    { tactic: 'TA0009', action: 'Collection dokumen sensitif' },
                    { tactic: 'TA0011', action: 'C2 via HTTPS ke CDN yang dikendalikan' },
                    { tactic: 'TA0010', action: 'Exfil via DNS tunneling' },
                ],
            };
            this.attackChain = templates[type] || [];
            this.renderMermaid();
        },
    };
}
</script>
@endsection
