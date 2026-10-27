<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Simulasi Red Team - {{ $simulasi->nama_skenario }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DejaVu Sans',sans-serif;color:#1a1a2e;font-size:11px;line-height:1.55}
.rh{background:#0a0e1a;color:#fff;padding:22px 28px}
.rh h1{font-size:19px;font-weight:900;color:#dc2626}
.rh .meta{font-size:10px;color:#94a3b8;margin-top:5px}
.rh .meta span{margin-right:16px}
.sg{background:#f8fafc;border-bottom:3px solid #dc2626}
.sg table{width:100%;border-collapse:collapse}
.sg td{text-align:center;padding:14px 8px;border-right:1px solid #e2e8f0}
.sg td:last-child{border-right:none}
.sn{font-size:24px;font-weight:900;line-height:1;color:#1a1a2e;}
.sl{font-size:9px;color:#64748b;margin-top:3px;text-transform:uppercase}
.sec{padding:16px 24px;border-bottom:1px solid #e2e8f0}
.stl{font-size:13px;font-weight:700;color:#0a0e1a;border-left:3px solid #dc2626;padding-left:8px;margin-bottom:10px;text-transform:uppercase}
.bk{display:inline-block;padding:2px 6px;border-radius:3px;font-size:9px;font-weight:700;background:#fca5a5;color:#7f1d1d}
.bt{display:inline-block;padding:2px 6px;border-radius:3px;font-size:9px;font-weight:700;background:#fed7aa;color:#9a3412}
.bs{display:inline-block;padding:2px 6px;border-radius:3px;font-size:9px;font-weight:700;background:#fef08a;color:#854d0e}
.bl{display:inline-block;padding:2px 6px;border-radius:3px;font-size:9px;font-weight:700;background:#bbf7d0;color:#166534}
.sc{background:#0d1117;color:#e2e8f0;border-radius:5px;padding:10px 14px;margin-top:8px;margin-bottom:12px;font-family:monospace;font-size:9.5px;line-height:1.4}
.term-cmd{color:#10b981;font-weight:bold;}
.term-out{color:#94a3b8;}
.term-err{color:#ef4444;}
.term-op{color:#3b82f6;font-style:italic;}
.step-box{border:1px solid #e2e8f0;border-radius:5px;padding:12px 16px;margin-bottom:16px;page-break-inside:avoid;background:#fff}
.step-title{font-size:11px;font-weight:bold;color:#1a1a2e;margin-bottom:4px}
.step-meta{font-size:9px;color:#64748b;margin-bottom:8px}
.step-finding{background:#fef2f2;border-left:3px solid #ef4444;padding:6px 10px;font-size:10px;margin-top:8px;color:#7f1d1d}
.pb{page-break-before:always}
ul.mitigasi { padding-left: 20px; font-size: 10px; }
ul.mitigasi li { margin-bottom: 4px; }
</style>
</head>
<body>

<!-- HEADER -->
<div class="rh">
    <h1>REDSIM - ATTACK SIMULATION REPORT</h1>
    <div class="meta">
        <span>Target: <strong>{{ $scan->target }}</strong></span>
        <span>Date: {{ now()->format('d/m/Y H:i') }}</span>
        <span>ID: {{ substr($simulasi->id, 0, 8) }}</span>
    </div>
</div>

<div class="sg">
    <table>
        <tr>
            <td>
                <div class="sn" style="font-size: 16px;">{{ $simulasi->nama_skenario }}</div>
                <div class="sl">NAMA SKENARIO</div>
            </td>
            <td>
                <div class="sn" style="font-size: 16px; color:#dc2626">{{ ucfirst(str_replace('_', ' ', $hasilAI['tingkat_kesulitan'] ?? 'Tidak Diketahui')) }}</div>
                <div class="sl">TINGKAT KESULITAN</div>
            </td>
            <td>
                <div class="sn">{{ $simulasi->skor_kemungkinan }}/10</div>
                <div class="sl">KEMUNGKINAN</div>
            </td>
            <td>
                <div class="sn">{{ $simulasi->skor_dampak }}/10</div>
                <div class="sl">DAMPAK</div>
            </td>
        </tr>
    </table>
</div>

<!-- LANGKAH-LANGKAH (LOG EKSEKUSI) -->
<div class="sec">
    <div class="stl">Log Eksekusi Serangan</div>
    
    @if(!empty($hasilAI['langkah_simulasi']) && is_array($hasilAI['langkah_simulasi']))
        @foreach($hasilAI['langkah_simulasi'] as $idx => $langkah)
            <div class="step-box">
                <div class="step-title">Langkah {{ $langkah['nomor'] ?? ($idx + 1) }}: {{ $langkah['judul'] ?? '-' }}</div>
                <div class="step-meta">
                    Fase ATT&CK: <strong>{{ $langkah['fase_attck'] ?? '-' }}</strong> | 
                    Tipe: <strong>{{ strtoupper($langkah['tipe'] ?? '-') }}</strong>
                </div>
                
                @if(!empty($langkah['komentar_operator']))
                    <p style="font-size:10px; color:#475569; margin-bottom:8px">
                        <em>"{{ $langkah['komentar_operator'] }}"</em>
                    </p>
                @endif
                
                @if(!empty($langkah['baris_terminal']) && is_array($langkah['baris_terminal']))
                    <div class="sc">
                        @foreach($langkah['baris_terminal'] as $baris)
                            @if($baris['tipe'] === 'command')
                                <div class="term-cmd">$ {{ $baris['teks'] }}</div>
                            @elseif($baris['tipe'] === 'error')
                                <div class="term-err">{{ $baris['teks'] }}</div>
                            @elseif($baris['tipe'] === 'operator' || $baris['tipe'] === 'info')
                                <div class="term-op"># {{ $baris['teks'] }}</div>
                            @else
                                <div class="term-out">{{ $baris['teks'] }}</div>
                            @endif
                        @endforeach
                    </div>
                @endif

                @if(!empty($langkah['temuan_kunci']))
                    <div class="step-finding">
                        <strong>Temuan Kunci:</strong> {{ $langkah['temuan_kunci'] }}
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <p>Tidak ada log langkah simulasi yang tersedia.</p>
    @endif
</div>

<!-- ANALISIS DAMPAK & MITIGASI -->
<div class="sec">
    <div class="stl" style="border-left-color: #0ea5e9;">Analisis AI - Hasil Simulasi</div>
    
    <p style="margin-bottom:8px"><strong>Ringkasan Simulasi:</strong><br>{{ $hasilAI['ringkasan_simulasi'] ?? '-' }}</p>
    <p style="margin-bottom:12px; padding: 10px; background: #fff1f2; border-left: 3px solid #ef4444; color: #7f1d1d;">
        <strong>Analisis Dampak:</strong><br>{{ $hasilAI['analisis_dampak'] ?? '-' }}
    </p>
    
    <p style="margin-bottom:8px"><strong>Vektor Serangan Utama:</strong> {{ $hasilAI['vektor_serangan_utama'] ?? '-' }}</p>

    <p style="margin-top: 12px;"><strong>Rekomendasi Mitigasi:</strong></p>
    <ul class="mitigasi" style="margin-bottom: 16px;">
        @if(!empty($hasilAI['rekomendasi_mitigasi']) && is_array($hasilAI['rekomendasi_mitigasi']))
            @foreach($hasilAI['rekomendasi_mitigasi'] as $mitigasi)
                <li>{{ $mitigasi }}</li>
            @endforeach
        @else
            <li>-</li>
        @endif
    </ul>
    
    <div class="stl" style="border-left-color: #ef4444; margin-top: 20px;">Narasi Teknis (Post-Mortem)</div>
    <div style="font-size: 10px; color: #475569; line-height: 1.6; margin-bottom: 16px;">
        {!! nl2br(e($simulasi->narasi_teknis)) !!}
    </div>
    
    <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                <div class="stl" style="border-left-color: #8b5cf6;">MITRE ATT&CK Mapping</div>
                <ul style="padding-left: 20px; font-size: 10px; color: #475569;">
                    @if(is_array($simulasi->fase_attck))
                        @foreach($simulasi->fase_attck as $fase)
                            <li style="margin-bottom: 4px;">{{ $fase }}</li>
                        @endforeach
                    @else
                        <li>Data fase tidak tersedia</li>
                    @endif
                </ul>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                <div class="stl" style="border-left-color: #64748b;">Kill Chain</div>
                <ol style="padding-left: 20px; font-size: 10px; color: #475569;">
                    @php $rantai = is_array($simulasi->rantai_serangan) ? $simulasi->rantai_serangan : []; @endphp
                    @if(count($rantai) > 0)
                        @foreach($rantai as $step)
                            <li style="margin-bottom: 4px;">{{ $step }}</li>
                        @endforeach
                    @else
                        <li>Data rantai serangan tidak tersedia</li>
                    @endif
                </ol>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
