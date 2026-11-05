@extends('layouts.app')

@section('title', 'Tagihan — KelolaKos')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Tagihan</h1>
            <p class="mt-1 text-sm text-slate-600">{{ auth()->user()->kos?->nama }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('pengurus.tagihan.bulan-ini') }}">
                @csrf
                <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Buat tagihan bulan ini
                </button>
            </form>
            <a href="{{ route('pengurus.tagihan.create') }}"
               class="rounded-md border border-slate-300 px-4 py-2 text-sm">Buat manual</a>
        </div>
    </div>

    @php
        $warnaStatus = [
            'belum_bayar' => 'bg-amber-100 text-amber-700',
            'menunggu_verifikasi' => 'bg-sky-100 text-sky-700',
            'lunas' => 'bg-emerald-100 text-emerald-700',
            'dibatalkan' => 'bg-slate-200 text-slate-600',
        ];
    @endphp

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Penghuni</th>
                    <th class="px-4 py-3">Kamar</th>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Jatuh tempo</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($daftarTagihan as $tagihan)
                    <tr>
                        <td class="px-4 py-3">{{ $tagihan->pemesanan->user->name }}</td>
                        <td class="px-4 py-3">{{ $tagihan->pemesanan->kamar->nomor }}</td>
                        <td class="px-4 py-3">{{ $tagihan->periode_bulan }}/{{ $tagihan->periode_tahun }}</td>
                        <td class="px-4 py-3">Rp {{ number_format((float) $tagihan->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $tagihan->jatuh_tempo->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $warnaStatus[$tagihan->status] ?? 'bg-slate-100' }}">
                                {{ str_replace('_', ' ', ucfirst($tagihan->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($tagihan->status === 'belum_bayar')
                                <a href="{{ route('pengurus.tagihan.edit', $tagihan) }}" class="text-emerald-700 hover:underline">Ubah</a>
                                <form method="POST" action="{{ route('pengurus.tagihan.batal', $tagihan) }}" class="inline"
                                      onsubmit="return confirm('Batalkan tagihan ini?')">
                                    @csrf
                                    <button type="submit" class="ml-3 text-red-600 hover:underline">Batalkan</button>
                                </form>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada tagihan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
