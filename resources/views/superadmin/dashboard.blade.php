@extends('layouts.app')

@section('title', 'Dashboard Admin — KelolaKos')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Dashboard super admin</h1>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Kos aktif</p>
            <p class="mt-2 text-3xl font-semibold">{{ $totalKosAktif }}</p>
            <p class="mt-1 text-xs text-slate-500">dari {{ $totalKos }} kos terdaftar</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Total kamar</p>
            <p class="mt-2 text-3xl font-semibold">{{ $totalKamar }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Akun pengurus</p>
            <p class="mt-2 text-3xl font-semibold">{{ $totalPengurus }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Akun penghuni</p>
            <p class="mt-2 text-3xl font-semibold">{{ $totalPenghuni }}</p>
        </div>
    </div>

    <div class="mt-6 flex gap-3 text-sm">
        <a href="{{ route('admin.kos.index') }}" class="rounded-md bg-emerald-600 px-4 py-2 font-semibold text-white hover:bg-emerald-700">
            Kelola kos
        </a>
        <a href="{{ route('admin.pengurus.index') }}" class="rounded-md border border-slate-300 px-4 py-2">
            Kelola pengurus
        </a>
    </div>
@endsection
