<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KelolaKos')</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-full items-center justify-center px-4 py-10 text-slate-800 antialiased">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <a href="{{ route('home') }}" class="text-2xl font-bold text-emerald-700">KelolaKos</a>
            <p class="mt-1 text-sm text-slate-500">Kelola kos lebih rapi dan aman.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @yield('content')
        </div>
    </div>
</body>
</html>
