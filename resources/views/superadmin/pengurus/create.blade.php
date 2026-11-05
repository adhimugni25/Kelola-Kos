@extends('layouts.app')

@section('title', 'Tambah Pengurus — KelolaKos')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Tambah akun pengurus</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.pengurus.store') }}" class="max-w-2xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf

        <div>
            <label for="name" class="mb-1 block text-sm font-medium">Nama</label>
            <input id="name" name="name" type="text" required value="{{ old('name') }}"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="phone" class="mb-1 block text-sm font-medium">No. HP</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label for="kos_id" class="mb-1 block text-sm font-medium">Kos yang dikelola</label>
            <select id="kos_id" name="kos_id" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                <option value="">Pilih kos</option>
                @foreach ($daftarKos as $kos)
                    <option value="{{ $kos->id }}" @selected(old('kos_id') == $kos->id)>{{ $kos->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password awal</label>
                <input id="password" name="password" type="password" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium">Ulangi password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Simpan
            </button>
            <a href="{{ route('admin.pengurus.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Batal</a>
        </div>
    </form>
@endsection
