@extends('layouts.app')

@section('title', 'Dashboard — KelolaKos')

@section('content')
    <h1 class="text-2xl font-semibold">Dashboard penghuni</h1>
    <p class="mt-2 text-slate-600">Selamat datang, {{ auth()->user()->name }}.</p>
@endsection
