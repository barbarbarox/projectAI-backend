@extends('layouts.app')

@section('title', 'Perbandingan Scan — RedSim')

@push('styles')
<style>
    .compare-card {
        background: linear-gradient(135deg, #080c18 0%, #0d1526 100%);
        border: 1px solid #1e293b;
        border-radius: 12px;
        padding: 1.5rem;
    }
    .score-ring {
        width: 100px; height: 100px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 900; margin: 0 auto 0.5rem; position: relative;
    }
    .score-ring::before {
        content: ''; position: absolute; inset: 0; border-radius: 50%;
        padding: 3px;
        background: conic-gradient(var(--score-color) var(--score-pct), #1e293b var(--score-pct));
        -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 8px), white calc(100% - 8px));
        mask: radial-gradient(farthest-side, transparent calc(100% - 8px), white calc(100% - 8px));
    }
    .delta-badge { display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-weight:700;font-size:.875rem; }
    .delta-up   { background:rgba(34,197,94,.15);color:#4ade80;border:1px solid rgba(34,197,94,.3); }
    .delta-down { background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.3); }
    .delta-same { background:rgba(100,116,139,.15);color:#94a3b8;border:1px solid rgba(100,116,139,.3); }
    .finding-row { display:flex;align-items:center;gap:.5rem;padding:6px 10px;border-radius:6px;font-size:.8rem;margin-bottom:4px; }
    .sev-kritis { background:rgba(185,28,28,.12);border-left:3px solid #b91c1c; }
    .sev-tinggi { background:rgba(234,88,12,.12);border-left:3px solid #ea580c; }
    .metric-row { display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #1e293b;font-size:.85rem; }
    .metric-row:last-child { border-bottom:none; }
    .select-banding { background:#0d1526;border:1px solid #334155;color:#f1f5f9;border-radius:6px;padding:6px 10px;font-size:.85rem; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:#00d4ff">Perbandingan Postur Keamanan</h1>
            <p class="text-sm mt-1" style="color:#64748b">Bandingkan dua scan untuk melihat perkembangan keamanan dari waktu ke waktu</p>
        </div>
        <a href="{{ route('laporan.detail', $scan) }}" class="text-sm px-4 py-2 rounded-lg" style="background:#1e293b;color:#94a3b8;border:1px solid #334155">← Kembali ke Laporan</a>
    </div>

    {{-- Pilih scan --}}
    @if($semuaScan->count() > 0)
    <div class="compare-card mb-6">
        <div class="text-sm font-semibold mb-3" style="color:#94a3b8">PILIH SCAN UNTUK DIBANDINGKAN</div>
        <form method="GET" action="{{ route('laporan.bandingkan', $scan) }}" class="flex items-center gap-3 flex-wrap">
            <select name="banding" class="select-banding flex-1" onchange="this.form.submit()">
                <option value="">— Pilih scan lain —</option>
                @foreach($semuaScan as $s)
                <option value="{{ $s->id }}" {{ ($scanSebelumnya && $scanSebelumnya->id === $s->id) ? 'selected' : '' }}>
                    [{{ $s->created_at->format('d M Y H:i') }}] {{ Str::limit($s->target ?? $s->nama_file, 50) }} | {{ ucfirst($s->mode_scan) }} | Skor: {{ $s->skor_keamanan }}/100
                </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold" style="background:#0284c7;color:#fff">Tampilkan</button>
        </form>
    </div>
    @endif

    @if(!$scanSebelumnya)
    <div class="compare-card text-center py-10">
        <div class="text-4xl mb-3">🔍</div>
        <div class="font-semibold mb-1" style="color:#94a3b8">Belum ada scan lain untuk dibandingkan</div>
        <div class="text-sm" style="color:#475569">Lakukan scan lagi pada target yang sama, atau pilih scan lain dari dropdown di atas.</div>
    </div>
    @else

    @php
        $skorA = $scan->skor_keamanan ?? 0;
        $skorB = $scanSebelumnya->skor_keamanan ?? 0;
        $delta = $skorA - $skorB;
        $countA = ['kritis'=>0,'tinggi'=>0,'sedang'=>0,'rendah'=>0,'info'=>0];
        foreach ($scan->temuan as $t) {
            $sev = strtolower($t->tingkat_keparahan ?? '');
            if (in_array($sev,['kritis','critical'])) $countA['kritis']++;
            elseif (in_array($sev,['tinggi','high'])) $countA['tinggi']++;
            elseif (in_array($sev,['sedang','medium'])) $countA['sedang']++;
            elseif (in_array($sev,['rendah','low'])) $countA['rendah']++;
            else $countA['info']++;
        }
        $countB = ['kritis'=>0,'tinggi'=>0,'sedang'=>0,'rendah'=>0,'info'=>0];
        foreach ($scanSebelumnya->temuan as $t) {
            $sev = strtolower($t->tingkat_keparahan ?? '');
            if (in_array($sev,['kritis','critical'])) $countB['kritis']++;
            elseif (in_array($sev,['tinggi','high'])) $countB['tinggi']++;
            elseif (in_array($sev,['sedang','medium'])) $countB['sedang']++;
            elseif (in_array($sev,['rendah','low'])) $countB['rendah']++;
            else $countB['info']++;
        }
        $totalA = $scan->temuan->count();
        $totalB = $scanSebelumnya->temuan->count();
        $colorA = $skorA>=70?'#22c55e':($skorA>=50?'#eab308':'#ef4444');
        $colorB = $skorB>=70?'#22c55e':($skorB>=50?'#eab308':'#ef4444');
        $dataA  = $scan->data_mentah ?? [];
        $dataB  = $scanSebelumnya->data_mentah ?? [];
    @endphp

    {{-- Score Hero --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="compare-card text-center">
            <div class="text-xs font-bold mb-2" style="color:#00d4ff;letter-spacing:1px">SCAN TERPILIH</div>
            <div class="score-ring mb-2" style="--score-color:{{ $colorA }};--score-pct:{{ $skorA }}%;color:{{ $colorA }}">{{ $skorA }}</div>
            <div class="font-bold text-sm mb-1" style="color:#f1f5f9">{{ Str::limit($scan->target ?? $scan->nama_file, 35) }}</div>
            <div class="text-xs" style="color:#64748b">{{ $scan->created_at->format('d M Y, H:i') }} WIB<br>Mode: {{ ucfirst($scan->mode_scan) }} | {{ $totalA }} Temuan</div>
        </div>
        <div class="compare-card flex flex-col items-center justify-center text-center">
            <div class="text-3xl mb-2">⚡</div>
            <div class="text-sm font-semibold mb-3" style="color:#94a3b8">Perubahan Skor</div>
            @if($delta>0)<div class="delta-badge delta-up text-xl mb-1">+{{ $delta }} ▲</div><div class="text-xs" style="color:#4ade80">Postur keamanan meningkat</div>
            @elseif($delta<0)<div class="delta-badge delta-down text-xl mb-1">{{ $delta }} ▼</div><div class="text-xs" style="color:#f87171">Postur keamanan menurun</div>
            @else<div class="delta-badge delta-same text-xl mb-1">0 ↔</div><div class="text-xs" style="color:#94a3b8">Tidak ada perubahan signifikan</div>@endif
            <div class="mt-4 text-xs" style="color:#475569">Selisih waktu:<br><span style="color:#94a3b8">{{ $scan->created_at->diffForHumans($scanSebelumnya->created_at, true) }}</span></div>
        </div>
        <div class="compare-card text-center" style="border-color:#334155">
            <div class="text-xs font-bold mb-2" style="color:#64748b;letter-spacing:1px">SCAN PEMBANDING</div>
            <div class="score-ring mb-2" style="--score-color:{{ $colorB }};--score-pct:{{ $skorB }}%;color:{{ $colorB }}">{{ $skorB }}</div>
            <div class="font-bold text-sm mb-1" style="color:#94a3b8">{{ Str::limit($scanSebelumnya->target ?? $scanSebelumnya->nama_file, 35) }}</div>
            <div class="text-xs" style="color:#475569">{{ $scanSebelumnya->created_at->format('d M Y, H:i') }} WIB<br>Mode: {{ ucfirst($scanSebelumnya->mode_scan) }} | {{ $totalB }} Temuan</div>
        </div>
    </div>

    {{-- Distribusi Temuan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="compare-card">
            <div class="text-xs font-bold mb-4" style="color:#00d4ff;letter-spacing:1px">DISTRIBUSI TEMUAN — SCAN TERPILIH</div>
            @foreach(['kritis'=>['#b91c1c','KRITIS'],'tinggi'=>['#ea580c','TINGGI'],'sedang'=>['#ca8a04','SEDANG'],'rendah'=>['#0284c7','RENDAH'],'info'=>['#64748b','INFO']] as $k=>[$c,$lbl])
            <div class="metric-row">
                <span style="color:{{ $c }};font-weight:600">{{ $lbl }}</span>
                <div class="flex items-center gap-3">
                    @php $pct = $totalA>0?($countA[$k]/$totalA*100):0; @endphp
                    <div class="h-2 rounded-full overflow-hidden" style="width:100px;background:#1e293b"><div class="h-2 rounded-full" style="width:{{ $pct }}%;background:{{ $c }}"></div></div>
                    <span class="font-bold" style="color:{{ $c }};min-width:24px;text-align:right">{{ $countA[$k] }}</span>
                </div>
            </div>
            @endforeach
            <div class="mt-3 pt-3" style="border-top:1px solid #1e293b">
                <span class="text-xs" style="color:#64748b">Total:</span>
                <span class="text-lg font-bold ml-2" style="color:#f1f5f9">{{ $totalA }}</span>
            </div>
        </div>
        <div class="compare-card" style="border-color:#334155">
            <div class="text-xs font-bold mb-4" style="color:#64748b;letter-spacing:1px">DISTRIBUSI TEMUAN — SCAN PEMBANDING</div>
            @foreach(['kritis'=>['#b91c1c','KRITIS'],'tinggi'=>['#ea580c','TINGGI'],'sedang'=>['#ca8a04','SEDANG'],'rendah'=>['#0284c7','RENDAH'],'info'=>['#64748b','INFO']] as $k=>[$c,$lbl])
            <div class="metric-row">
                <span style="color:{{ $c }};font-weight:600;opacity:.7">{{ $lbl }}</span>
                <div class="flex items-center gap-3">
                    @php $pctB2=$totalB>0?($countB[$k]/$totalB*100):0; $diffK=$countA[$k]-$countB[$k]; @endphp
                    <div class="h-2 rounded-full overflow-hidden" style="width:100px;background:#1e293b"><div class="h-2 rounded-full" style="width:{{ $pctB2 }}%;background:{{ $c }};opacity:.55"></div></div>
                    <span class="font-bold" style="color:#64748b;min-width:24px;text-align:right">{{ $countB[$k] }}</span>
                    @if($diffK<0)<span class="text-xs" style="color:#4ade80">▼{{ abs($diffK) }}</span>@elseif($diffK>0)<span class="text-xs" style="color:#f87171">▲{{ $diffK }}</span>@endif
                </div>
            </div>
            @endforeach
            <div class="mt-3 pt-3" style="border-top:1px solid #1e293b">
                <span class="text-xs" style="color:#475569">Total:</span>
                <span class="text-lg font-bold ml-2" style="color:#64748b">{{ $totalB }}</span>
            </div>
        </div>
    </div>

    {{-- Metrik Kunci --}}
    <div class="compare-card mb-6">
        <div class="text-xs font-bold mb-4" style="color:#94a3b8;letter-spacing:1px">PERBANDINGAN METRIK KUNCI</div>
        <table style="width:100%;border-collapse:collapse">
            <thead><tr style="border-bottom:1px solid #334155">
                <th class="text-left py-2 px-3 text-xs font-semibold" style="color:#64748b">METRIK</th>
                <th class="text-center py-2 px-3 text-xs font-semibold" style="color:#00d4ff">SCAN TERPILIH</th>
                <th class="text-center py-2 px-3 text-xs font-semibold" style="color:#64748b">SCAN PEMBANDING</th>
                <th class="text-center py-2 px-3 text-xs font-semibold" style="color:#94a3b8">DELTA</th>
            </tr></thead>
            <tbody>
            @php
                $rows = [
                    ['Skor Keamanan', $skorA.'/100', $skorB.'/100', $delta, 'score'],
                    ['SSL Valid', !empty($dataA['ssl']['valid'])?'✅ Valid':'❌ Tidak', !empty($dataB['ssl']['valid'])?'✅ Valid':'❌ Tidak', null, 'bool'],
                    ['Skor Headers', ($dataA['security_headers']['skor']??'–').'/100', ($dataB['security_headers']['skor']??'–').'/100', ($dataA['security_headers']['skor']??0)-($dataB['security_headers']['skor']??0), 'score'],
                    ['Total Temuan', $totalA, $totalB, $totalA-$totalB, 'finding'],
                    ['Kritis+Tinggi', $countA['kritis']+$countA['tinggi'], $countB['kritis']+$countB['tinggi'], ($countA['kritis']+$countA['tinggi'])-($countB['kritis']+$countB['tinggi']), 'finding'],
                    ['CORS Rentan', !empty($dataA['cors_config']['vulnerable'])?'⚠ Ya':'✅ Tidak', !empty($dataB['cors_config']['vulnerable'])?'⚠ Ya':'✅ Tidak', null, 'bool'],
                    ['Mode Scan', ucfirst($scan->mode_scan), ucfirst($scanSebelumnya->mode_scan), null, 'info'],
                ];
            @endphp
            @foreach($rows as [$label,$a,$b,$d,$type])
            <tr style="border-bottom:1px solid #1a2235">
                <td class="py-2 px-3 text-sm" style="color:#94a3b8">{{ $label }}</td>
                <td class="py-2 px-3 text-sm text-center font-semibold" style="color:#f1f5f9">{{ $a }}</td>
                <td class="py-2 px-3 text-sm text-center" style="color:#64748b">{{ $b }}</td>
                <td class="py-2 px-3 text-center">
                    @if($d !== null)
                        @if($type==='finding')
                            @if($d<0)<span class="delta-badge delta-up" style="font-size:.75rem">{{ $d }} ▼</span>
                            @elseif($d>0)<span class="delta-badge delta-down" style="font-size:.75rem">+{{ $d }} ▲</span>
                            @else<span class="delta-badge delta-same" style="font-size:.75rem">sama</span>@endif
                        @else
                            @if($d>0)<span class="delta-badge delta-up" style="font-size:.75rem">+{{ $d }} ▲</span>
                            @elseif($d<0)<span class="delta-badge delta-down" style="font-size:.75rem">{{ $d }} ▼</span>
                            @else<span class="delta-badge delta-same" style="font-size:.75rem">sama</span>@endif
                        @endif
                    @else<span style="color:#334155">—</span>@endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- Temuan Kritis Side-by-Side --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="compare-card">
            <div class="text-xs font-bold mb-3" style="color:#00d4ff;letter-spacing:1px">TEMUAN KRITIS & TINGGI — SCAN TERPILIH</div>
            @forelse($scan->temuan->filter(fn($t)=>in_array(strtolower($t->tingkat_keparahan),['kritis','critical','tinggi','high']))->sortByDesc(fn($t)=>in_array(strtolower($t->tingkat_keparahan),['kritis','critical'])?1:0) as $t)
            @php $sev=strtolower($t->tingkat_keparahan);@endphp
            <div class="finding-row sev-{{ in_array($sev,['kritis','critical'])?'kritis':'tinggi' }}">
                <span style="font-size:.7rem;font-weight:700;color:{{ in_array($sev,['kritis','critical'])?'#ef4444':'#f97316' }};text-transform:uppercase;min-width:48px">{{ $sev }}</span>
                <span style="color:#e2e8f0;font-size:.8rem">{{ Str::limit($t->judul, 55) }}</span>
            </div>
            @empty<div class="text-sm py-4 text-center" style="color:#22c55e">✅ Tidak ada temuan kritis/tinggi</div>@endforelse
        </div>
        <div class="compare-card" style="border-color:#334155">
            <div class="text-xs font-bold mb-3" style="color:#64748b;letter-spacing:1px">TEMUAN KRITIS & TINGGI — SCAN PEMBANDING</div>
            @forelse($scanSebelumnya->temuan->filter(fn($t)=>in_array(strtolower($t->tingkat_keparahan),['kritis','critical','tinggi','high']))->sortByDesc(fn($t)=>in_array(strtolower($t->tingkat_keparahan),['kritis','critical'])?1:0) as $t)
            @php $sev=strtolower($t->tingkat_keparahan);@endphp
            <div class="finding-row sev-{{ in_array($sev,['kritis','critical'])?'kritis':'tinggi' }}" style="opacity:.65">
                <span style="font-size:.7rem;font-weight:700;color:{{ in_array($sev,['kritis','critical'])?'#ef4444':'#f97316' }};text-transform:uppercase;min-width:48px">{{ $sev }}</span>
                <span style="color:#94a3b8;font-size:.8rem">{{ Str::limit($t->judul, 55) }}</span>
            </div>
            @empty<div class="text-sm py-4 text-center" style="color:#22c55e">✅ Tidak ada temuan kritis/tinggi</div>@endforelse
        </div>
    </div>

    {{-- Kesimpulan --}}
    <div class="compare-card mb-6" style="border-color:{{ $delta>0?'rgba(34,197,94,.3)':($delta<0?'rgba(239,68,68,.3)':'#334155') }}">
        <div class="flex items-start gap-4">
            <div class="text-3xl">{{ $delta>0?'📈':($delta<0?'📉':'🔄') }}</div>
            <div>
                <div class="font-bold mb-1" style="color:{{ $delta>0?'#4ade80':($delta<0?'#f87171':'#94a3b8') }}">
                    @if($delta>0)Postur Keamanan Meningkat +{{ $delta }} Poin
                    @elseif($delta<0)Postur Keamanan Menurun {{ $delta }} Poin
                    @else Postur Keamanan Stabil @endif
                </div>
                <div class="text-sm" style="color:#94a3b8">
                    @if($delta>0)Skor meningkat dari <strong>{{ $skorB }}</strong> → <strong>{{ $skorA }}</strong>.@if(($countA['kritis']+$countA['tinggi'])<($countB['kritis']+$countB['tinggi'])) Jumlah temuan kritis/tinggi juga berkurang — remediasi berjalan baik.@endif
                    @elseif($delta<0)Skor menurun dari <strong>{{ $skorB }}</strong> → <strong>{{ $skorA }}</strong>. Tinjau kembali konfigurasi yang mungkin berubah.
                    @else Skor tidak berubah. Pantau terus untuk mendeteksi perubahan.@endif
                    Laporan ini merupakan perkiraan berbasis AI — lakukan verifikasi manual untuk konfirmasi final.
                </div>
            </div>
        </div>
    </div>

    @endif

    <div class="flex gap-3 flex-wrap">
        <a href="{{ route('laporan.detail', $scan) }}" class="px-4 py-2 rounded-lg text-sm font-semibold" style="background:#1e293b;color:#94a3b8;border:1px solid #334155">📄 Laporan Lengkap</a>
        <a href="{{ route('laporan.pdf', $scan) }}" class="px-4 py-2 rounded-lg text-sm font-semibold" style="background:#0369a1;color:#fff">📥 Export PDF Scan Ini</a>
        @if(isset($scanSebelumnya) && $scanSebelumnya)
        <a href="{{ route('laporan.pdf', $scanSebelumnya) }}" class="px-4 py-2 rounded-lg text-sm font-semibold" style="background:#334155;color:#94a3b8">📥 Export PDF Scan Pembanding</a>
        @endif
    </div>
</div>
@endsection
