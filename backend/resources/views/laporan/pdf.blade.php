<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Laporan RedSim #{{ substr($scan->id, 0, 8) }}</title>
<style>body{font-family:sans-serif;color:#333;font-size:12px;line-height:1.6}h1{color:#0a0e1a;border-bottom:2px solid #00d4ff;padding-bottom:10px}h2{color:#1e2d4a;margin-top:20px}.badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:bold}.kritis{background:#fecaca;color:#991b1b}.tinggi{background:#fed7aa;color:#9a3412}.sedang{background:#fef08a;color:#854d0e}.rendah{background:#bbf7d0;color:#166534}.card{border:1px solid #ddd;border-radius:8px;padding:12px;margin:8px 0}.score{font-size:48px;font-weight:900;text-align:center;margin:20px 0}.disclaimer{background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:10px;margin-top:20px;font-size:10px}</style></head>
<body>
<h1>Laporan Keamanan RedSim</h1>
<p><b>Tipe:</b> {{ ucfirst($scan->tipe_scan) }} | <b>Target:</b> {{ $scan->target ?? $scan->nama_file }} | <b>Tanggal:</b> {{ $scan->created_at->format('d M Y H:i') }}</p>

<div class="score" style="color:{{ ($scan->skor_keamanan ?? 0) >= 70 ? '#16a34a' : (($scan->skor_keamanan ?? 0) >= 50 ? '#ca8a04' : '#dc2626') }}">{{ $scan->skor_keamanan ?? '-' }}/100</div>
<p style="text-align:center"><b>Verdict:</b> {{ $scan->verdict_label }}</p>

@if($scan->ringkasan_eksekutif)<h2>Ringkasan Eksekutif</h2><p>{{ $scan->ringkasan_eksekutif }}</p>@endif
@if($scan->ringkasan_teknis)<h2>Ringkasan Teknis</h2><p>{{ $scan->ringkasan_teknis }}</p>@endif

@if($scan->temuan->count())
<h2>Temuan ({{ $scan->temuan->count() }})</h2>
@foreach($scan->temuan as $t)
<div class="card">
    <span class="badge {{ $t->tingkat_keparahan }}">{{ strtoupper($t->tingkat_keparahan) }}</span>
    <b>{{ $t->judul }}</b> @if($t->cwe_id)({{ $t->cwe_id }})@endif
    @if($t->deskripsi)<p>{{ $t->deskripsi }}</p>@endif
    @if($t->remediasi)<p><b>Remediasi:</b> {{ $t->remediasi }}</p>@endif
</div>
@endforeach
@endif

<div class="disclaimer">Penilaian ini dihasilkan AI. Tidak menggantikan penetration testing profesional.</div>
</body></html>
