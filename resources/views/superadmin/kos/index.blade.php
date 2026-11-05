@extends('layouts.app')

@section('title', 'Manajemen Kos — KelolaKos')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Daftar kos</h1>
            <p class="mt-1 text-sm text-slate-600">Setiap kos punya pengurus dan data yang terpisah.</p>
        </div>
        <a href="{{ route('admin.kos.create') }}"
           class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            Tambah kos
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Kota</th>
                    <th class="px-4 py-3">Kamar</th>
                    <th class="px-4 py-3">Pengurus</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($daftarKos as $kos)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $kos->nama }}</div>
                            <div class="text-xs text-slate-500">/k/{{ $kos->slug }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $kos->kota }}</td>
                        <td class="px-4 py-3">{{ $kos->kamar_count }}</td>
                        <td class="px-4 py-3">{{ $kos->pengurus_count }}</td>
                        <td class="px-4 py-3">
                            @if ($kos->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-200 px-2 py-1 text-xs font-medium text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.kos.edit', $kos) }}" class="text-emerald-700 hover:underline">Ubah</a>
                            <form method="POST" action="{{ route('admin.kos.toggle', $kos) }}" class="inline">
                                @csrf
                                <button type="submit" class="ml-3 text-slate-600 hover:underline">
                                    {{ $kos->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada kos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
