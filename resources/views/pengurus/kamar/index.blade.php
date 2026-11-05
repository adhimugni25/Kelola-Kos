@extends('layouts.app')

@section('title', 'Kamar — KelolaKos')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Kamar</h1>
            <p class="mt-1 text-sm text-slate-600">{{ auth()->user()->kos?->nama }}</p>
        </div>
        <a href="{{ route('pengurus.kamar.create') }}"
           class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            Tambah kamar
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Foto</th>
                    <th class="px-4 py-3">Nomor</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Harga / bulan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($daftarKamar as $kamar)
                    <tr>
                        <td class="px-4 py-3">
                            @if ($kamar->foto_path)
                                <img src="{{ Storage::disk('public')->url($kamar->foto_path) }}" alt="Foto kamar {{ $kamar->nomor }}"
                                     class="h-12 w-16 rounded object-cover">
                            @else
                                <div class="h-12 w-16 rounded bg-slate-100"></div>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $kamar->nomor }}</td>
                        <td class="px-4 py-3">{{ $kamar->tipe }}</td>
                        <td class="px-4 py-3">Rp {{ number_format((float) $kamar->harga_bulanan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @php
                                $warna = match ($kamar->status) {
                                    'kosong' => 'bg-emerald-100 text-emerald-700',
                                    'terisi' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-slate-200 text-slate-600',
                                };
                            @endphp
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $warna }}">{{ ucfirst($kamar->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('pengurus.kamar.edit', $kamar) }}" class="text-emerald-700 hover:underline">Ubah</a>
                            <form method="POST" action="{{ route('pengurus.kamar.destroy', $kamar) }}" class="inline"
                                  data-konfirmasi="Hapus kamar ini?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-3 text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada kamar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
