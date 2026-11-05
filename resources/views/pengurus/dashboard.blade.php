@extends('layouts.app')

@section('title', 'Dashboard Pengurus — KelolaKos')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Dashboard pengurus</h1>
            <p class="mt-1 text-sm text-slate-600">{{ auth()->user()->kos?->nama ?? 'Kos belum terhubung' }}</p>
        </div>
        <a href="{{ route('pengurus.laporan.index') }}"
           class="rounded-md border border-slate-300 px-4 py-2 text-sm">Lihat laporan</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Kamar terisi</p>
            <p class="mt-2 text-3xl font-semibold">{{ $ringkasan['kamar_terisi'] }}</p>
            <p class="mt-1 text-xs text-slate-500">
                dari {{ $ringkasan['total_kamar'] }} kamar ({{ $ringkasan['tingkat_keterisian'] }}% terisi)
            </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Kamar kosong</p>
            <p class="mt-2 text-3xl font-semibold">{{ $ringkasan['kamar_kosong'] }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $ringkasan['kamar_perbaikan'] }} kamar dalam perbaikan</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Pendapatan bulan ini</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-700">
                Rp {{ number_format((float) $ringkasan['pendapatan_bulan_ini'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-500">Diakui saat pembayaran diverifikasi</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Tagihan belum lunas</p>
            <p class="mt-2 text-2xl font-semibold">
                Rp {{ number_format((float) $ringkasan['tagihan_belum_lunas'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-500">{{ $ringkasan['pembayaran_menunggu'] }} pembayaran menunggu verifikasi</p>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3 text-sm">
        <a href="{{ route('pengurus.tagihan.index') }}" class="rounded-md bg-emerald-600 px-4 py-2 font-semibold text-white hover:bg-emerald-700">
            Kelola tagihan
        </a>
        <a href="{{ route('pengurus.pembayaran.index') }}" class="rounded-md border border-slate-300 px-4 py-2">
            Verifikasi pembayaran
        </a>
        <a href="{{ route('pengurus.pemesanan.index') }}" class="rounded-md border border-slate-300 px-4 py-2">
            Pengajuan sewa
        </a>
        <a href="{{ route('pengurus.kamar.index') }}" class="rounded-md border border-slate-300 px-4 py-2">
            Kamar
        </a>
    </div>
@endsection
