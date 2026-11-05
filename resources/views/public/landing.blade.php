@extends('layouts.public')

@section('title', 'KelolaKos — Kelola kos lebih rapi')

@push('styles')
    @include('public.partials.home.map-styles')
@endpush

@section('content')
    @include('public.partials.home.hero')
    @include('public.partials.home.map')
    @include('public.partials.home.rooms')
    @include('public.partials.home.dashboard-preview')
    @include('public.partials.home.faq')
    @include('public.partials.home.cta')
@endsection


