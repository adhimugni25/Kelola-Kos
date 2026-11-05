@extends('layouts.app')

@section('title', 'Laporan Pendapatan — KelolaKos')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Laporan pendapatan</h1>
            <p class="mt-1 text-sm text-slate-600">
                Basis kas: hanya pembayaran yang sudah diverifikasi yang dihitung.
            </p>
        </div>

        <div class="flex gap-3 text-sm">
            <a href="{{ route('pengurus.laporan.csv', ['dari' => $laporan['dari'], 'sampai' => $laporan['sampai']]) }}"
               class="rounded-md border border-slate-300 px-4 py-2">Unduh CSV</a>
            <a href="{{ route('pengurus.laporan.pdf', ['dari' => $laporan['dari'], 'sampai' => $laporan['sampai']]) }}"
               class="rounded-md bg-slate-800 px-4 py-2 text-white">Unduh PDF</a>
        </div>
    </div>

    <form method="GET" action="{{ route('pengurus.laporan.index') }}"
          class="mb-6 flex flex-wrap items-end gap-4 rounded-xl border border-slate-200 bg-white p-5">
        <div>
            <label for="dari" class="mb-1 block text-sm font-medium">Dari tanggal</label>
            <input id="dari" name="dari" type="date" value="{{ $laporan['dari'] }}"
                   class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="sampai" class="mb-1 block text-sm font-medium">Sampai tanggal</label>
            <input id="sampai" name="sampai" type="date" value="{{ $laporan['sampai'] }}"
                   class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>
        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            Terapkan
        </button>
    </form>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Total pendapatan</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-700">
                Rp {{ number_format((float) $laporan['total'], 0, ',', '.') }}
            </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Jumlah transaksi</p>
            <p class="mt-2 text-2xl font-semibold">{{ $laporan['jumlah_transaksi'] }}</p>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="mb-4 font-semibold">Grafik pendapatan per bulan</h2>
        <canvas id="grafik-pendapatan" height="120"></canvas>
        @php
            $grafikPendapatan = [
                'labels' => array_column($laporan['per_bulan'], 'label'),
                'nilai' => array_map(fn ($baris) => (float) $baris['total'], $laporan['per_bulan']),
            ];
        @endphp
        <script type="application/json" id="data-pendapatan">{!! json_encode($grafikPendapatan, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Bulan</th>
                    <th class="px-4 py-3">Jumlah transaksi</th>
                    <th class="px-4 py-3 text-right">Pendapatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($laporan['per_bulan'] as $baris)
                    <tr>
                        <td class="px-4 py-3">{{ $baris['label'] }}</td>
                        <td class="px-4 py-3">{{ $baris['jumlah_transaksi'] }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format((float) $baris['total'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 font-semibold">
                <tr>
                    <td class="px-4 py-3">Total</td>
                    <td class="px-4 py-3">{{ $laporan['jumlah_transaksi'] }}</td>
                    <td class="px-4 py-3 text-right">Rp {{ number_format((float) $laporan['total'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
