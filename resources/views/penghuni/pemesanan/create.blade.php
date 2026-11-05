@extends('layouts.app')

@section('title', 'Ajukan Sewa — KelolaKos')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Ajukan sewa</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-1">
            <p class="text-sm text-slate-500">{{ $kamar->kos->nama }}</p>
            <p class="mt-1 text-lg font-semibold">Kamar {{ $kamar->nomor }}</p>
            <p class="text-sm text-slate-600">{{ $kamar->tipe }}</p>
            <p class="mt-3 text-sm font-semibold text-emerald-700">
                Rp {{ number_format((float) $kamar->harga_bulanan, 0, ',', '.') }}/bulan
            </p>
            @if ($kamar->fasilitas)
                <p class="mt-3 text-xs text-slate-500">{{ $kamar->fasilitas }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('penghuni.pemesanan.store') }}"
              class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 lg:col-span-2">
            @csrf
            <input type="hidden" name="kamar_id" value="{{ $kamar->id }}">

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="tanggal_mulai" class="mb-1 block text-sm font-medium">Tanggal mulai</label>
                    <input id="tanggal_mulai" name="tanggal_mulai" type="date" required
                           value="{{ old('tanggal_mulai', now()->toDateString()) }}"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="durasi_bulan" class="mb-1 block text-sm font-medium">Durasi (bulan)</label>
                    <input id="durasi_bulan" name="durasi_bulan" type="number" min="1" max="24" required
                           value="{{ old('durasi_bulan', 12) }}"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label for="catatan" class="mb-1 block text-sm font-medium">Catatan untuk pengurus</label>
                <textarea id="catatan" name="catatan" rows="3" maxlength="1000"
                          class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('catatan') }}</textarea>
            </div>

            <p class="text-xs text-slate-500">
                Sewa bulan pertama dihitung penuh tanpa prorata. Kamar baru dikunci setelah pengurus menyetujui.
            </p>

            <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Kirim pengajuan
            </button>
        </form>
    </div>
@endsection
