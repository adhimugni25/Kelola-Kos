@extends('layouts.app')

@section('title', 'KelolaKos — Kelola kos lebih rapi')

@section('content')
    <section class="rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-800 px-8 py-16 text-white">
        <h1 class="max-w-2xl text-3xl font-bold sm:text-4xl">Kelola kos lebih rapi, aman, dan transparan.</h1>
        <p class="mt-4 max-w-2xl text-emerald-50">
            KelolaKos membantu pengurus mencatat kamar, menerima pengajuan sewa, membuat tagihan bulanan,
            dan memverifikasi bukti pembayaran. Penghuni bisa melihat tagihan dan status sewanya kapan saja.
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            @auth
                <a href="{{ auth()->user()->isSuperAdmin() ? route('admin.dashboard') : (auth()->user()->isPengurus() ? route('pengurus.dashboard') : route('penghuni.dashboard')) }}"
                   class="rounded-md bg-white px-5 py-2.5 text-sm font-semibold text-emerald-700">
                    Buka dashboard
                </a>
            @else
                <a href="{{ route('register') }}" class="rounded-md bg-white px-5 py-2.5 text-sm font-semibold text-emerald-700">
                    Daftar sebagai penghuni
                </a>
                <a href="{{ route('login') }}" class="rounded-md border border-emerald-300 px-5 py-2.5 text-sm font-semibold text-white">
                    Masuk
                </a>
            @endauth
        </div>
    </section>

    <section class="mt-10 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-emerald-700">Pengajuan sewa</p>
            <p class="mt-2 text-sm text-slate-600">Penghuni mengajukan kamar lewat halaman publik kos, pengurus menyetujui dari dashboard.</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-emerald-700">Tagihan otomatis</p>
            <p class="mt-2 text-sm text-slate-600">Tagihan bulanan dibuat dari harga saat persetujuan, tanpa prorata yang membingungkan.</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-emerald-700">Bukti pembayaran privat</p>
            <p class="mt-2 text-sm text-slate-600">Bukti transfer hanya bisa dilihat pengurus kos terkait, tidak lewat URL publik.</p>
        </div>
    </section>

    <p class="mt-8 text-sm text-slate-500">{{ $jumlahKos }} kos aktif terdaftar di platform ini.</p>
@endsection
