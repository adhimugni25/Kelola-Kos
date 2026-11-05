@extends('layouts.app')

@section('title', 'Detail Pengajuan — KelolaKos')

@section('content')
    <a href="{{ route('pengurus.pemesanan.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Kembali</a>

    <h1 class="mt-3 text-2xl font-semibold">Pengajuan kamar {{ $pemesanan->kamar->nomor }}</h1>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Pemesan</p>
            <p class="mt-1 font-medium">{{ $pemesanan->user->name }}</p>
            <p class="text-sm text-slate-600">{{ $pemesanan->user->email }}</p>
            <p class="text-sm text-slate-600">{{ $pemesanan->user->phone }}</p>

            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Mulai</dt>
                    <dd>{{ $pemesanan->tanggal_mulai->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Durasi</dt>
                    <dd>{{ $pemesanan->durasi_bulan }} bulan</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Status</dt>
                    <dd>{{ ucfirst($pemesanan->status) }}</dd>
                </div>
                @if ($pemesanan->berakhir_pada)
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Berakhir</dt>
                        <dd>{{ $pemesanan->berakhir_pada->format('d/m/Y') }}</dd>
                    </div>
                @endif
            </dl>

            @if ($pemesanan->catatan)
                <p class="mt-4 rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-600">{{ $pemesanan->catatan }}</p>
            @endif
        </div>

        <div class="space-y-4 lg:col-span-2">
            @if ($pemesanan->status === 'menunggu')
                <form method="POST" action="{{ route('pengurus.pemesanan.setujui', $pemesanan) }}"
                      class="rounded-xl border border-slate-200 bg-white p-5">
                    @csrf
                    <h2 class="font-semibold">Setujui pengajuan</h2>
                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="tanggal_mulai" class="mb-1 block text-sm font-medium">Tanggal mulai final</label>
                            <input id="tanggal_mulai" name="tanggal_mulai" type="date" required
                                   value="{{ $pemesanan->tanggal_mulai->toDateString() }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label for="durasi_bulan" class="mb-1 block text-sm font-medium">Durasi (bulan)</label>
                            <input id="durasi_bulan" name="durasi_bulan" type="number" min="1" max="24" required
                                   value="{{ $pemesanan->durasi_bulan }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <button type="submit" class="mt-4 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Setujui &amp; buat tagihan pertama
                    </button>
                </form>

                <form method="POST" action="{{ route('pengurus.pemesanan.tolak', $pemesanan) }}"
                      class="rounded-xl border border-slate-200 bg-white p-5">
                    @csrf
                    <h2 class="font-semibold">Tolak pengajuan</h2>
                    <textarea name="alasan_penolakan" rows="2" required maxlength="1000"
                              placeholder="Alasan penolakan (wajib)"
                              class="mt-3 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"></textarea>
                    <button type="submit" class="mt-3 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Tolak
                    </button>
                </form>
            @endif

            @if ($pemesanan->status === 'aktif')
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <h2 class="font-semibold">Tagihan</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Selesaikan semua tagihan sebelum menandai masa sewa berakhir.
                    </p>
                    <form method="POST" action="{{ route('pengurus.pemesanan.selesai', $pemesanan) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="rounded-md border border-slate-300 px-4 py-2 text-sm">
                            Tandai masa sewa selesai
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
