@extends('layouts.app')

@section('title', 'Ubah Kamar — KelolaKos')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Ubah kamar</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('pengurus.kamar.update', $kamar) }}" enctype="multipart/form-data"
          class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')
        @include('pengurus.kamar._form', ['kamar' => $kamar])

        <div class="mt-6 flex gap-3">
            <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Simpan perubahan
            </button>
            <a href="{{ route('pengurus.kamar.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Batal</a>
        </div>
    </form>
@endsection
