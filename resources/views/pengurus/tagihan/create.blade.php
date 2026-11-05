@extends('layouts.app')

@section('title', 'Buat Tagihan — KelolaKos')

@section('content')
    <a href="{{ route('pengurus.tagihan.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Kembali</a>
    <h1 class="mt-3 mb-6 text-2xl font-semibold">Buat tagihan manual</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($daftarPemesanan->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
            Belum ada sewa aktif. Setujui pengajuan terlebih dahulu.
        </div>
    @else
        <form method="POST" action="{{ route('pengurus.tagihan.store') }}"
              class="max-w-2xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            @csrf

            <div>
                <label for="pemesanan_id" class="mb-1 block text-sm font-medium">Sewa</label>
                <select id="pemesanan_id" name="pemesanan_id" required
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Pilih sewa</option>
                    @foreach ($daftarPemesanan as $pemesanan)
                        <option value="{{ $pemesanan->id }}" @selected(old('pemesanan_id') == $pemesanan->id)>
                            Kamar {{ $pemesanan->kamar->nomor }} — {{ $pemesanan->user->name }}
                            (Rp {{ number_format((float) $pemesanan->harga_bulanan, 0, ',', '.') }}/bulan)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="periode_bulan" class="mb-1 block text-sm font-medium">Bulan</label>
                    <input id="periode_bulan" name="periode_bulan" type="number" min="1" max="12" required
                           value="{{ old('periode_bulan', now()->month) }}"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="periode_tahun" class="mb-1 block text-sm font-medium">Tahun</label>
                    <input id="periode_tahun" name="periode_tahun" type="number" min="2020" max="2100" required
                           value="{{ old('periode_tahun', now()->year) }}"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="nominal_sewa" class="mb-1 block text-sm font-medium">Nominal sewa (Rp)</label>
                    <input id="nominal_sewa" name="nominal_sewa" type="number" min="0" step="1000" required
                           value="{{ old('nominal_sewa') }}"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="biaya_tambahan" class="mb-1 block text-sm font-medium">Biaya tambahan (Rp)</label>
                    <input id="biaya_tambahan" name="biaya_tambahan" type="number" min="0" step="1000"
                           value="{{ old('biaya_tambahan', 0) }}"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label for="keterangan_tambahan" class="mb-1 block text-sm font-medium">Keterangan biaya tambahan</label>
                <input id="keterangan_tambahan" name="keterangan_tambahan" type="text" maxlength="255"
                       value="{{ old('keterangan_tambahan') }}"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="jatuh_tempo" class="mb-1 block text-sm font-medium">Jatuh tempo</label>
                <input id="jatuh_tempo" name="jatuh_tempo" type="date" required
                       value="{{ old('jatuh_tempo', now()->addDays(7)->toDateString()) }}"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Simpan tagihan
            </button>
        </form>
    @endif
@endsection
