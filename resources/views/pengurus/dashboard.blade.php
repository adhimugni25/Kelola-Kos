@extends('layouts.app')

@section('title', 'Dashboard Pengurus — KelolaKos')

@section('content')
    <h1 class="text-2xl font-semibold">Dashboard pengurus</h1>
    <p class="mt-2 text-slate-600">{{ auth()->user()->kos?->nama ?? 'Kos belum terhubung' }}</p>
@endsection
