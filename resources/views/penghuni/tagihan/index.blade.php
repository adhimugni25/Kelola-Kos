@extends('layouts.app')

@section('title', 'Tagihan Saya — KelolaKos')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Tagihan saya</h1>

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
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Kos / kamar</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Jatuh tempo</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($daftarTagihan as $tagihan)
                    <tr>
                        <td class="px-4 py-3 font-medium">
                            {{ $tagihan->periode_bulan }}/{{ $tagihan->periode_tahun }}
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ $tagihan->pemesanan->kos->nama }}</div>
                            <div class="text-xs text-slate-500">Kamar {{ $tagihan->pemesanan->kamar->nomor }}</div>
                        </td>
                        <td class="px-4 py-3">Rp {{ number_format((float) $tagihan->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $tagihan->jatuh_tempo->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $warnaStatus[$tagihan->status] ?? 'bg-slate-100' }}">
                                {{ str_replace('_', ' ', ucfirst($tagihan->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('penghuni.tagihan.show', $tagihan) }}" class="text-emerald-700 hover:underline">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada tagihan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
