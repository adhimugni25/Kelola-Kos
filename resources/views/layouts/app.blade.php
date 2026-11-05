<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KelolaKos')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-800 antialiased">
    <div class="min-h-full">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                <a href="{{ route('home') }}" class="text-lg font-semibold text-emerald-700">KelolaKos</a>
                @auth
                    <nav class="hidden items-center gap-4 text-sm text-slate-600 md:flex">
                        @if (auth()->user()->isPengurus())
                            <a href="{{ route('pengurus.kamar.index') }}" class="hover:text-emerald-700">Kamar</a>
                            <a href="{{ route('pengurus.pemesanan.index') }}" class="hover:text-emerald-700">Pemesanan</a>
                            <a href="{{ route('pengurus.tagihan.index') }}" class="hover:text-emerald-700">Tagihan</a>
                            <a href="{{ route('pengurus.pembayaran.index') }}" class="hover:text-emerald-700">Pembayaran</a>
                            <a href="{{ route('pengurus.metode-pembayaran.index') }}" class="hover:text-emerald-700">Metode</a>
                            <a href="{{ route('pengurus.laporan.index') }}" class="hover:text-emerald-700">Laporan</a>
                            <a href="{{ route('pengurus.impor.index') }}" class="hover:text-emerald-700">Impor</a>
                        @elseif (auth()->user()->isPenghuni())
                            <a href="{{ route('penghuni.pemesanan.index') }}" class="hover:text-emerald-700">Pengajuan</a>
                            <a href="{{ route('penghuni.tagihan.index') }}" class="hover:text-emerald-700">Tagihan</a>
                        @else
                            <a href="{{ route('admin.kos.index') }}" class="hover:text-emerald-700">Kos</a>
                            <a href="{{ route('admin.pengurus.index') }}" class="hover:text-emerald-700">Pengurus</a>
                        @endif
                    </nav>

                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-slate-600">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-white hover:bg-slate-700">
                                Keluar
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-3 text-sm">
                        <a href="{{ route('login') }}" class="text-slate-600 hover:text-emerald-700">Masuk</a>
                        <a href="{{ route('register') }}" class="rounded-md bg-emerald-600 px-3 py-1.5 text-white hover:bg-emerald-700">
                            Daftar
                        </a>
                    </div>
                @endauth
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
