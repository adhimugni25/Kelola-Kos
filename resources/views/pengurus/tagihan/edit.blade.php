@extends('layouts.app')

@section('title', 'Ubah Tagihan — KelolaKos')

@section('content')
    <a href="{{ route('pengurus.tagihan.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Kembali</a>
    <h1 class="mt-3 mb-6 text-2xl font-semibold">
        Ubah tagihan {{ $tagihan->periode_bulan }}/{{ $tagihan->periode_tahun }}
    </h1>

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('pengurus.tagihan.update', $tagihan) }}"
          class="max-w-2xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')

        <input type="hidden" name="pemesanan_id" value="{{ $tagihan->pemesanan_id }}">
        <input type="hidden" name="periode_bulan" value="{{ $tagihan->periode_bulan }}">
        <input type="hidden" name="periode_tahun" value="{{ $tagihan->periode_tahun }}">

        <div class="rounded-md bg-slate-50 px-4 py-3 text-sm text-slate-600">
            Kamar {{ $tagihan->pemesanan->kamar->nomor }} — {{ $tagihan->pemesanan->user->name }}
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="nominal_sewa" class="mb-1 block text-sm font-medium">Nominal sewa (Rp)</label>
                <input id="nominal_sewa" name="nominal_sewa" type="number" min="0" step="1000" required
                       value="{{ old('nominal_sewa', (float) $tagihan->nominal_sewa) }}"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="biaya_tambahan" class="mb-1 block text-sm font-medium">Biaya tambahan (Rp)</label>
                <input id="biaya_tambahan" name="biaya_tambahan" type="number" min="0" step="1000"
                       value="{{ old('biaya_tambahan', (float) $tagihan->biaya_tambahan) }}"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label for="keterangan_tambahan" class="mb-1 block text-sm font-medium">Keterangan biaya tambahan</label>
            <input id="keterangan_tambahan" name="keterangan_tambahan" type="text" maxlength="255"
                   value="{{ old('keterangan_tambahan', $tagihan->keterangan_tambahan) }}"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>

        <div>
            <label for="jatuh_tempo" class="mb-1 block text-sm font-medium">Jatuh tempo</label>
            <input id="jatuh_tempo" name="jatuh_tempo" type="date" required
                   value="{{ old('jatuh_tempo', $tagihan->jatuh_tempo->toDateString()) }}"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>

        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            Simpan perubahan
        </button>
    </form>
@endsection
