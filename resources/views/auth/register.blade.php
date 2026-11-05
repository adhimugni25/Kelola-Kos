@extends('layouts.guest')

@section('title', 'Daftar — KelolaKos')

@section('content')
    <h1 class="mb-6 text-xl font-semibold">Daftar akun penghuni</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="mb-1 block text-sm font-medium">Nama lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-medium">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        </div>

        <div>
            <label for="phone" class="mb-1 block text-sm font-medium">No. HP</label>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Password</label>
            <input id="password" type="password" name="password" required
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            <p class="mt-1 text-xs text-slate-500">Minimal 8 karakter, mengandung huruf dan angka.</p>
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium">Ulangi password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        </div>

        <button type="submit" class="w-full rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            Daftar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:underline">Masuk</a>
    </p>
@endsection
