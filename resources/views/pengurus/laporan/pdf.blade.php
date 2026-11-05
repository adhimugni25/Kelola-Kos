<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .muted { color: #6b7280; margin: 0 0 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; }
        th { background: #f3f4f6; text-align: left; }
        .angka { text-align: right; }
        tfoot td { font-weight: bold; background: #f9fafb; }
        .ringkasan { margin-top: 10px; }
        .ringkasan td { border: none; padding: 2px 0; }
    </style>
</head>
<body>
    <h1>Laporan Pendapatan — {{ $kos?->nama ?? 'KelolaKos' }}</h1>
    <p class="muted">
        Periode {{ $laporan['dari'] }} sampai {{ $laporan['sampai'] }} ·
        Dicetak {{ $dicetakPada->format('d/m/Y H:i') }}
    </p>

    <table class="ringkasan">
        <tr>
            <td>Total pendapatan diterima</td>
            <td class="angka">Rp {{ number_format((float) $laporan['total'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Jumlah transaksi</td>
            <td class="angka">{{ $laporan['jumlah_transaksi'] }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th class="angka">Jumlah transaksi</th>
                <th class="angka">Pendapatan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($laporan['per_bulan'] as $baris)
                <tr>
                    <td>{{ $baris['label'] }}</td>
                    <td class="angka">{{ $baris['jumlah_transaksi'] }}</td>
                    <td class="angka">{{ number_format((float) $baris['total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="angka">{{ $laporan['jumlah_transaksi'] }}</td>
                <td class="angka">{{ number_format((float) $laporan['total'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
