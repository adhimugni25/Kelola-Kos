<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KelolaKos')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="antialiased">
    <div class="public-shell">
        <a class="skip-link" href="#konten">Lewati ke konten</a>

        <header class="site-header">
            <div class="site-header-inner">
                <a class="brand" href="{{ route('home') }}">
                    <span class="brand-mark">K</span>KelolaKos
                </a>

                <nav class="nav-pill" aria-label="Navigasi utama">
                    <a class="nav-link is-active" aria-current="page" href="#beranda">Beranda</a>
                    <a class="nav-link" href="#jaringan">Jaringan</a>
                    <a class="nav-link" href="#kamar">Kamar</a>
                    <a class="nav-link" href="#pengurus">Pengurus</a>
                    <a class="nav-link" href="#faq">FAQ</a>
                </nav>

                <div class="nav-actions">
                    @auth
                        @php
                            $dashboardRoute = auth()->user()->isSuperAdmin()
                                ? route('admin.dashboard')
                                : (auth()->user()->isPengurus() ? route('pengurus.dashboard') : route('penghuni.dashboard'));
                        @endphp
                        <a class="nav-masuk" href="{{ $dashboardRoute }}">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                            @csrf
                            <button class="pill-dark" type="submit">Keluar</button>
                        </form>
                    @else
                        <a class="nav-masuk" href="{{ route('login') }}">Masuk</a>
                        <a class="pill-dark" href="{{ route('register') }}">Daftar</a>
                    @endauth

                    <button class="burger" type="button" data-menu-toggle
                            aria-label="Buka menu" aria-controls="menu-publik" aria-expanded="false">
                        <i></i><i></i><i></i>
                    </button>
                </div>
            </div>

            <div class="site-drawer" id="menu-publik" hidden>
                <a href="#beranda">Beranda</a>
                <a href="#jaringan">Jaringan</a>
                <a href="#kamar">Kamar</a>
                <a href="#pengurus">Pengurus</a>
                <a href="#faq">FAQ</a>
                @auth
                    <a href="{{ $dashboardRoute }}">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Masuk</a>
                @endauth
                <p>Akun pengurus dibuat oleh admin.</p>
            </div>
        </header>

        <main id="konten">
            @if (session('status'))
                <div class="alert alert-sukses">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-gagal">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
