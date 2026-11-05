@extends('layouts.app')

@section('title', 'Pengajuan Saya — KelolaKos')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Pengajuan sewa saya</h1>

    @php
        $warnaStatus = [
            'menunggu' => 'bg-amber-100 text-amber-700',
            'aktif' => 'bg-emerald-100 text-emerald-700',
            'ditolak' => 'bg-red-100 text-red-700',
            'selesai' => 'bg-slate-200 text-slate-600',
            'dibatalkan' => 'bg-slate-200 text-slate-600',
        ];
    @endphp

    <div class="space-y-4">
        @forelse ($daftarPemesanan as $pemesanan)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-medium">{{ $pemesanan->kos->nama }} — Kamar {{ $pemesanan->kamar->nomor }}</p>
                        <p class="mt-1 text-sm text-slate-600">
                            Mulai {{ $pemesanan->tanggal_mulai->format('d/m/Y') }}
                            · {{ $pemesanan->durasi_bulan }} bulan
                            @if ($pemesanan->berakhir_pada)
                                · berakhir {{ $pemesanan->berakhir_pada->format('d/m/Y') }}
                            @endif
                        </p>
                        @if ($pemesanan->catatan)
                            <p class="mt-2 text-sm text-slate-500">{{ $pemesanan->catatan }}</p>
                        @endif
                        @if ($pemesanan->alasan_penolakan)
                            <p class="mt-2 text-sm text-red-600">{{ $pemesanan->alasan_penolakan }}</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $warnaStatus[$pemesanan->status] ?? 'bg-slate-100' }}">
                            {{ ucfirst($pemesanan->status) }}
                        </span>

                        @if ($pemesanan->status === 'menunggu')
                            <form method="POST" action="{{ route('penghuni.pemesanan.batal', $pemesanan) }}">
                                @csrf
                                <button type="submit" class="text-sm text-red-600 hover:underline">Batalkan</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                Belum ada pengajuan. Buka halaman kos untuk mengajukan sewa.
            </div>
        @endforelse
    </div>
@endsection
