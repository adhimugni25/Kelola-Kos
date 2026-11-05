@extends('layouts.app')

@section('title', $kos->nama.' — KelolaKos')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">{{ $kos->nama }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ $kos->alamat }}, {{ $kos->kota }}</p>
                <p class="mt-2 text-sm text-slate-600">{{ $kos->deskripsi }}</p>
            </div>
            <div class="text-sm text-slate-600">
                <p class="font-medium text-slate-800">Kontak</p>
                <p>{{ $kos->kontak }}</p>
            </div>
        </div>

        @if ($kos->fasilitas_umum)
            <div class="mt-4 rounded-md bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <span class="font-medium text-slate-800">Fasilitas umum:</span> {{ $kos->fasilitas_umum }}
            </div>
        @endif
    </div>

    @unless ($menerimaPemesanan)
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
            Kos ini <strong>belum menerima pemesanan</strong> saat ini. Silakan cek kembali nanti.
        </div>
    @endunless

    @if ($kamarKosong->isNotEmpty())
        <h2 class="mt-8 text-lg font-semibold">Kamar tersedia</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($kamarKosong as $kamar)
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    @if ($kamar->foto_path)
                        <img src="{{ Storage::disk('public')->url($kamar->foto_path) }}" alt="Foto kamar {{ $kamar->nomor }}"
                             class="mb-4 h-40 w-full rounded-lg object-cover">
                    @endif

                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium">Kamar {{ $kamar->nomor }}</p>
                            <p class="text-sm text-slate-600">{{ $kamar->tipe }}</p>
                        </div>
                        <p class="text-sm font-semibold text-emerald-700">
                            Rp {{ number_format((float) $kamar->harga_bulanan, 0, ',', '.') }}/bulan
                        </p>
                    </div>

                    @if ($kamar->fasilitas)
                        <p class="mt-3 text-xs text-slate-500">{{ $kamar->fasilitas }}</p>
                    @endif

                    <div class="mt-4">
                        @auth
                            @if (auth()->user()->isPenghuni())
                                @if (Route::has('penghuni.pemesanan.create'))
                                    <a href="{{ route('penghuni.pemesanan.create', ['kamar' => $kamar->id]) }}"
                                       class="block rounded-md bg-emerald-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-emerald-700">
                                        Ajukan sewa
                                    </a>
                                @else
                                    <span class="block rounded-md bg-slate-100 px-4 py-2 text-center text-sm text-slate-500">Ajukan sewa segera hadir</span>
                                @endif
                            @else
                                <span class="block rounded-md bg-slate-100 px-4 py-2 text-center text-sm text-slate-500">
                                    Hanya akun penghuni yang bisa mengajukan
                                </span>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                               class="block rounded-md bg-emerald-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-emerald-700">
                                Masuk untuk mengajukan sewa
                            </a>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
