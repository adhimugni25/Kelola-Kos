@extends('layouts.app')

@section('title', 'Impor Data — KelolaKos')

@section('content')
    <h1 class="mb-1 text-2xl font-semibold">Impor data</h1>
    <p class="mb-6 text-sm text-slate-600">
        Migrasi data dari sistem lama lewat berkas CSV. Ukuran maksimal 5 MB dan dibatasi tiga unggahan per jam.
        Seluruh baris divalidasi lebih dulu; jika ada satu kesalahan, tidak ada data yang disimpan.
    </p>

    @if ($errors->has('impor'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Berkas belum bisa diimpor:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->get('impor') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($errors->has('berkas'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first('berkas') }}
        </div>
    @endif

    @if (session('kredensial'))
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-5">
            <h2 class="font-semibold text-amber-900">Akun penghuni baru — catat kata sandi sekarang</h2>
            <p class="mt-1 text-sm text-amber-800">
                Kata sandi hanya ditampilkan sekali pada halaman ini. Serahkan ke penghuni melalui kanal yang aman.
            </p>

            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-amber-800">
                        <tr>
                            <th class="py-2 pr-4">Nama</th>
                            <th class="py-2 pr-4">Email</th>
                            <th class="py-2">Kata sandi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-200">
                        @foreach (session('kredensial') as $akun)
                            <tr>
                                <td class="py-2 pr-4">{{ $akun['nama'] }}</td>
                                <td class="py-2 pr-4">{{ $akun['email'] }}</td>
                                <td class="py-2 font-mono">{{ $akun['password'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-semibold">Impor kamar</h2>
            <p class="mt-1 text-sm text-slate-600">
                Kolom: <span class="font-mono text-xs">nomor, tipe, harga_bulanan, fasilitas, status</span>.
                Status diisi <span class="font-mono text-xs">kosong</span>, <span class="font-mono text-xs">terisi</span>,
                atau <span class="font-mono text-xs">perbaikan</span>.
            </p>

            <a href="{{ route('pengurus.impor.kamar.template') }}"
               class="mt-3 inline-block text-sm text-emerald-700 hover:underline">
                Unduh template kamar (CSV)
            </a>

            <form method="POST" action="{{ route('pengurus.impor.kamar') }}" enctype="multipart/form-data"
                  class="mt-4 space-y-3">
                @csrf

                <div>
                    <label for="berkas_kamar" class="mb-1 block text-sm font-medium">Berkas CSV</label>
                    <input id="berkas_kamar" name="berkas" type="file" required accept=".csv,text/csv"
                           class="w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm">
                </div>

                <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Impor kamar
                </button>
            </form>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-semibold">Impor penghuni aktif</h2>
            <p class="mt-1 text-sm text-slate-600">
                Kolom: <span class="font-mono text-xs">nama, email, no_hp, nomor_kamar, tanggal_mulai, durasi_bulan</span>.
                Kamar harus kosong dan tanggal mulai tidak boleh di masa depan.
            </p>

            <a href="{{ route('pengurus.impor.penghuni.template') }}"
               class="mt-3 inline-block text-sm text-emerald-700 hover:underline">
                Unduh template penghuni (CSV)
            </a>

            <form method="POST" action="{{ route('pengurus.impor.penghuni') }}" enctype="multipart/form-data"
                  class="mt-4 space-y-3">
                @csrf

                <div>
                    <label for="berkas_penghuni" class="mb-1 block text-sm font-medium">Berkas CSV</label>
                    <input id="berkas_penghuni" name="berkas" type="file" required accept=".csv,text/csv"
                           class="w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm">
                </div>

                <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Impor penghuni
                </button>
            </form>

            <p class="mt-4 text-xs text-slate-500">
                Akun baru dibuat dengan kata sandi acak. Sistem tidak membuat tagihan otomatis; tekan
                &ldquo;Buat tagihan bulan ini&rdquo; setelah impor selesai.
            </p>
        </section>
    </div>
@endsection
