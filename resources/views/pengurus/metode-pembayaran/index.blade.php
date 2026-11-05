@extends('layouts.app')

@section('title', 'Metode Pembayaran — KelolaKos')

@section('content')
    <h1 class="mb-1 text-2xl font-semibold">Metode pembayaran</h1>
    <p class="mb-6 text-sm text-slate-600">
        Rekening atau e-wallet yang ditampilkan ke penghuni saat membayar tagihan.
    </p>

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
        <form method="POST" action="{{ route('pengurus.metode-pembayaran.store') }}"
              class="space-y-4 rounded-xl border border-slate-200 bg-white p-5">
            @csrf

            <div>
                <label for="tipe" class="mb-1 block text-sm font-medium">Tipe</label>
                <select id="tipe" name="tipe" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="bank" @selected(old('tipe') === 'bank')>Bank</option>
                    <option value="ewallet" @selected(old('tipe') === 'ewallet')>E-wallet</option>
                </select>
            </div>

            <div>
                <label for="nama" class="mb-1 block text-sm font-medium">Nama</label>
                <input id="nama" name="nama" type="text" required maxlength="100" value="{{ old('nama') }}"
                       placeholder="BCA / DANA"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="nomor" class="mb-1 block text-sm font-medium">Nomor rekening / HP</label>
                <input id="nomor" name="nomor" type="text" required maxlength="50" value="{{ old('nomor') }}"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="atas_nama" class="mb-1 block text-sm font-medium">Atas nama</label>
                <input id="atas_nama" name="atas_nama" type="text" required maxlength="100" value="{{ old('atas_nama') }}"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300">
                Aktif
            </label>

            <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Tambah metode
            </button>
        </form>

        <div class="space-y-3 lg:col-span-2">
            @forelse ($daftarMetode as $metode)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4">
                    <div>
                        <p class="font-medium">{{ $metode->nama }} — {{ $metode->nomor }}</p>
                        <p class="text-sm text-slate-600">{{ $metode->atas_nama }}</p>
                        <p class="text-xs uppercase tracking-wide text-slate-500">{{ $metode->tipe }}</p>
                    </div>

                    <div class="flex items-center gap-3 text-sm">
                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $metode->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $metode->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>

                        <form method="POST" action="{{ route('pengurus.metode-pembayaran.toggle', $metode) }}">
                            @csrf
                            <button type="submit" class="text-slate-600 hover:underline">
                                {{ $metode->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('pengurus.metode-pembayaran.destroy', $metode) }}"
                              data-konfirmasi="Hapus metode pembayaran ini?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                    Belum ada metode pembayaran.
                </div>
            @endforelse
        </div>
    </div>
@endsection
