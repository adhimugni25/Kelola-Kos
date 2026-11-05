@extends('layouts.public')

@section('title', 'KelolaKos — Kelola kos lebih rapi')

@push('styles')
    @include('public.partials.home.map-styles')
@endpush

@section('content')
    @include('public.partials.home.hero')
    @include('public.partials.home.map')
@endsection


