<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KelolaKos')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <div class="app-shell">
        <a class="skip-link" href="#konten">Lewati ke konten</a>

        <header class="app-header">
            <div class="app-header-inner">
                <a class="brand" href="{{ route('home') }}">
                    <span class="brand-mark">K</span>KelolaKos
                </a>

                @auth
                    @php
                        $user = auth()->user();
                        $roleLabel = $user->isSuperAdmin() ? 'Super admin' : ($user->isPengurus() ? 'Pengurus' : 'Penghuni');
                        $pilihanNav = $user->isSuperAdmin()
                            ? [
                                ['admin.kos.index', 'Kos'],
                                ['admin.pengurus.index', 'Pengurus'],
                            ]
                            : ($user->isPengurus()
                                ? [
                                    ['pengurus.kamar.index', 'Kamar'],
                                    ['pengurus.pemesanan.index', 'Pemesanan'],
                                    ['pengurus.tagihan.index', 'Tagihan'],
                                    ['pengurus.pembayaran.index', 'Pembayaran'],
                                    ['pengurus.metode-pembayaran.index', 'Metode'],
                                    ['pengurus.laporan.index', 'Laporan'],
                                    ['pengurus.impor.index', 'Impor'],
                                ]
                                : [
                                    ['penghuni.pemesanan.index', 'Pengajuan'],
                                    ['penghuni.tagihan.index', 'Tagihan'],
                                ]);
                    @endphp
                    <nav class="app-nav" aria-label="Navigasi aplikasi">
                        @foreach ($pilihanNav as [$route, $label])
                            <a href="{{ route($route) }}"
                               @if (request()->route()->named($route)) class="is-active" aria-current="page" @endif>{{ $label }}</a>
                        @endforeach
                    </nav>

                    <div class="app-user">
                        <span class="role-label">{{ ucfirst($user->kos?->nama ?? $roleLabel) }}</span>
                        @php $dashboardKe = $user->isSuperAdmin() ? 'admin.dashboard' : ($user->isPengurus() ? 'pengurus.dashboard' : 'penghuni.dashboard'); @endphp
                        <a class="btn-ghost btn-sm" href="{{ route($dashboardKe) }}">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-primary btn-sm">Keluar</button>
                        </form>
                    </div>
                @else
                    <div class="app-user">
                        <a class="btn-ghost btn-sm" href="{{ route('login') }}">Masuk</a>
                        <a class="btn-primary btn-sm" href="{{ route('register') }}">Daftar</a>
                    </div>
                @endauth
            </div>
        </header>

        <main id="konten" class="app-main">
            @if (session('status'))
                <div class="alert alert-sukses" role="status">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-gagal" role="alert">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
