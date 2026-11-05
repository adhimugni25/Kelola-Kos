@extends('layouts.app')

@section('title', 'Pengajuan Sewa — KelolaKos')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Pengajuan sewa</h1>

    @php
        $warnaStatus = [
            'menunggu' => 'bg-amber-100 text-amber-700',
            'aktif' => 'bg-emerald-100 text-emerald-700',
            'ditolak' => 'bg-red-100 text-red-700',
            'selesai' => 'bg-slate-200 text-slate-600',
            'dibatalkan' => 'bg-slate-200 text-slate-600',
        ];
    @endphp

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Pemesan</th>
                    <th class="px-4 py-3">Kamar</th>
                    <th class="px-4 py-3">Mulai</th>
                    <th class="px-4 py-3">Durasi</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($daftarPemesanan as $pemesanan)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $pemesanan->user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $pemesanan->user->phone }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $pemesanan->kamar->nomor }}</td>
                        <td class="px-4 py-3">{{ $pemesanan->tanggal_mulai->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $pemesanan->durasi_bulan }} bulan</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $warnaStatus[$pemesanan->status] ?? 'bg-slate-100' }}">
                                {{ ucfirst($pemesanan->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('pengurus.pemesanan.show', $pemesanan) }}" class="text-emerald-700 hover:underline">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada pengajuan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
