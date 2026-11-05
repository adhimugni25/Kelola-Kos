@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran — KelolaKos')

@section('content')
    <h1 class="mb-1 text-2xl font-semibold">Verifikasi pembayaran</h1>
    <p class="mb-6 text-sm text-slate-600">{{ auth()->user()->kos?->nama }}</p>

    @php
        $warnaStatus = [
            'menunggu' => 'bg-sky-100 text-sky-700',
            'diterima' => 'bg-emerald-100 text-emerald-700',
            'ditolak' => 'bg-red-100 text-red-700',
        ];
    @endphp

    <div class="space-y-4">
        @forelse ($daftarPembayaran as $pembayaran)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-medium">
                            {{ $pembayaran->tagihan->pemesanan->user->name }}
                            — Kamar {{ $pembayaran->tagihan->pemesanan->kamar->nomor }}
                        </p>
                        <p class="mt-1 text-sm text-slate-600">
                            Periode {{ $pembayaran->tagihan->periode_bulan }}/{{ $pembayaran->tagihan->periode_tahun }}
                            · Rp {{ number_format((float) $pembayaran->jumlah, 0, ',', '.') }}
                            · {{ $pembayaran->metodePembayaran?->nama }}
                        </p>
                        <p class="text-xs text-slate-500">
                            Diunggah {{ $pembayaran->created_at->format('d/m/Y H:i') }}
                        </p>
                        @if ($pembayaran->catatan_penghuni)
                            <p class="mt-2 text-sm text-slate-500">{{ $pembayaran->catatan_penghuni }}</p>
                        @endif
                        @if ($pembayaran->catatan_verifikasi)
                            <p class="mt-2 text-sm text-red-600">{{ $pembayaran->catatan_verifikasi }}</p>
                        @endif
                    </div>

                    <div class="text-right">
                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $warnaStatus[$pembayaran->status] ?? 'bg-slate-100' }}">
                            {{ ucfirst($pembayaran->status) }}
                        </span>
                        <div class="mt-2">
                            <a href="{{ route('pembayaran.bukti', $pembayaran) }}" target="_blank" rel="noopener"
                               class="text-sm text-emerald-700 hover:underline">Lihat bukti</a>
                        </div>
                    </div>
                </div>

                @if ($pembayaran->status === 'menunggu')
                    <div class="mt-4 flex flex-wrap items-start gap-4 border-t border-slate-100 pt-4">
                        <form method="POST" action="{{ route('pengurus.pembayaran.terima', $pembayaran) }}"
                              data-konfirmasi="Terima pembayaran ini? Tagihan akan ditandai lunas.">
                            @csrf
                            <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                Terima
                            </button>
                        </form>

                        <form method="POST" action="{{ route('pengurus.pembayaran.tolak', $pembayaran) }}"
                              class="flex flex-1 flex-wrap items-end gap-3">
                            @csrf
                            <div class="min-w-[220px] flex-1">
                                <label class="mb-1 block text-xs text-slate-600">Alasan penolakan (wajib)</label>
                                <input type="text" name="catatan_verifikasi" required maxlength="500"
                                       class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                            </div>
                            <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                Tolak
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                Belum ada pembayaran masuk.
            </div>
        @endforelse
    </div>
@endsection
