<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Laporan Absensi Karyawan</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f1f5f2;color:#17211c;font:12px/1.45 Arial,sans-serif}.sheet{max-width:1200px;margin:28px auto;padding:32px;background:#fff}.top{display:flex;justify-content:space-between;align-items:flex-start;gap:20px}.eyebrow{color:#567765;font-size:11px;font-weight:bold;letter-spacing:.12em;text-transform:uppercase}h1{margin:5px 0 4px;font-size:24px}.muted,.detail{color:#65716b}.actions button{border:0;border-radius:8px;background:#567765;color:#fff;padding:10px 14px;font-weight:bold;cursor:pointer}.meta{display:flex;flex-wrap:wrap;gap:24px;margin:22px 0;padding:14px 0;border-top:1px solid #dfe6e1;border-bottom:1px solid #dfe6e1}.meta strong{display:block;margin-top:3px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #d8dfda;padding:8px;text-align:left;vertical-align:top}th{background:#eff4ef;font-size:10px;text-transform:uppercase;letter-spacing:.04em}tbody tr:nth-child(even){background:#fafcfb}.detail{display:block;font-size:10px;margin-top:2px;overflow-wrap:anywhere}.time{font-size:15px;font-weight:bold}.footer{margin-top:18px;color:#65716b;font-size:11px}@media print{body{background:#fff;font-size:10px}.sheet{max-width:none;margin:0;padding:0}.actions{display:none}th,td{padding:5px;print-color-adjust:exact;-webkit-print-color-adjust:exact}@page{size:landscape;margin:10mm}}
    </style>
</head>
<body><main class="sheet">
    <div class="top"><div><div class="eyebrow">HadirKantor · Laporan</div><h1>Detail Absensi Karyawan</h1><div class="muted">Periode {{ \Illuminate\Support\Carbon::parse($filters['from'])->translatedFormat('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($filters['to'])->translatedFormat('d M Y') }}</div></div><div class="actions"><button type="button" onclick="window.print()">Cetak / Simpan PDF</button></div></div>
    <div class="meta"><div>Lokasi filter<strong>{{ $officeName }}</strong></div><div>Status<strong>{{ $filters['status'] ?: 'Semua status' }}</strong></div><div>Karyawan<strong>{{ $filters['q'] ?: 'Semua karyawan' }}</strong></div><div>Jumlah catatan<strong>{{ number_format($records->count()) }}</strong></div></div>
    <table><thead><tr><th>Tanggal</th><th>Karyawan</th><th>NIK / Departemen</th><th>Absen masuk</th><th>Absen pulang</th><th>Status</th></tr></thead><tbody>
    @forelse ($records as $row)
        <tr>
            <td>{{ $row->attendance_date->translatedFormat('d M Y') }}</td>
            <td>{{ $row->user?->name ?? 'Pengguna dihapus' }}<span class="detail">{{ $row->user?->username ?? '—' }}</span></td>
            <td>{{ $row->user?->nik ?: '—' }}<span class="detail">{{ $row->user?->department?->name ?: '—' }}</span></td>
            <td><span class="time">{{ $row->check_in?->format('H:i') ?? 'Belum absen' }}</span><span class="detail">{{ $row->office?->name ?? 'Lokasi tidak tersedia' }}</span><span class="detail">Jarak: {{ $row->distance_meters !== null ? number_format((float) $row->distance_meters, 0, ',', '.') . ' m' : '—' }}</span><span class="detail">Koordinat: {{ $row->latitude !== null && $row->longitude !== null ? $row->latitude . ', ' . $row->longitude : '—' }}</span></td>
            <td><span class="time">{{ $row->check_out?->format('H:i') ?? 'Belum absen' }}</span><span class="detail">{{ $row->checkOutOffice?->name ?? ($row->check_out ? ($row->office?->name ?? 'Lokasi tidak tersedia') : '—') }}</span><span class="detail">Jarak: {{ $row->check_out_distance_meters !== null ? number_format((float) $row->check_out_distance_meters, 0, ',', '.') . ' m' : '—' }}</span><span class="detail">Koordinat: {{ $row->check_out_latitude !== null && $row->check_out_longitude !== null ? $row->check_out_latitude . ', ' . $row->check_out_longitude : '—' }}</span></td>
            <td>{{ $row->status }}</td>
        </tr>
    @empty<tr><td colspan="6">Tidak ada data untuk filter ini.</td></tr>@endforelse
    </tbody></table><p class="footer">Dicetak pada {{ $printedAt->translatedFormat('d M Y H:i') }} · {{ $records->count() }} baris</p>
</main><script>window.addEventListener('load',()=>window.print());</script></body></html>
