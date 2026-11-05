@extends('layouts.app')

@section('title', 'Akun Pengurus — KelolaKos')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Akun pengurus</h1>
            <p class="mt-1 text-sm text-slate-600">Satu akun pengurus terikat ke satu kos.</p>
        </div>
        <a href="{{ route('admin.pengurus.create') }}"
           class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            Tambah pengurus
        </a>
    </div>

    <div class="space-y-4">
        @forelse ($daftarPengurus as $pengurus)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-medium">{{ $pengurus->name }}</p>
                        <p class="text-sm text-slate-600">{{ $pengurus->email }}</p>
                        <p class="text-xs text-slate-500">Kos: {{ $pengurus->kos?->nama ?? '-' }}</p>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        @if ($pengurus->is_active)
                            <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700">Aktif</span>
                        @else
                            <span class="rounded-full bg-slate-200 px-2 py-1 text-xs font-medium text-slate-600">Nonaktif</span>
                        @endif
                        <form method="POST" action="{{ route('admin.pengurus.toggle', $pengurus) }}">
                            @csrf
                            <button type="submit" class="text-slate-600 hover:underline">
                                {{ $pengurus->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </div>
                </div>

                <details class="mt-3 text-sm">
                    <summary class="cursor-pointer text-emerald-700">Reset password</summary>
                    <form method="POST" action="{{ route('admin.pengurus.reset', $pengurus) }}" class="mt-3 flex flex-wrap items-end gap-3">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs text-slate-600">Password baru</label>
                            <input type="password" name="password" required
                                   class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-slate-600">Ulangi</label>
                            <input type="password" name="password_confirmation" required
                                   class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                        </div>
                        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm text-white hover:bg-slate-700">
                            Simpan password
                        </button>
                    </form>
                </details>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                Belum ada akun pengurus.
            </div>
        @endforelse
    </div>
@endsection
