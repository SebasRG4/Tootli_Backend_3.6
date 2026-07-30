@extends('layouts.admin.app')

@section('title', 'Panel de Control')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ 'Panel Sabores de la Ciudad' }}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Stats Cards -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <a class="resturant-card dashboard--card" href="{{ route('admin.sabores.restaurants') }}">
                    <h4 class="subtitle">{{ 'Restaurantes totales' }}</h4>
                    <span class="h3 mb-0">
                        {{ \App\Models\Store::whereHas('module', function($q) { $q->where('module_type', 'sabores'); })->count() }}
                    </span>
                    <img class="resturant-icon" src="{{ asset('assets/admin/img/dashboard/store.png') }}" alt="dashboard">
                </a>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <a class="resturant-card dashboard--card" href="{{ route('admin.sabores.reservations') }}">
                    <h4 class="subtitle">{{ 'Reservas totales' }}</h4>
                    <span class="h3 mb-0">{{ \App\Models\Reservation::count() }}</span>
                    <img class="resturant-icon" src="{{ asset('assets/admin/img/dashboard/order.png') }}" alt="dashboard">
                </a>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <a class="resturant-card dashboard--card" href="{{ route('admin.sabores.reservations', ['status' => 'pending']) }}">
                    <h4 class="subtitle">{{ 'Reservas Pendientes' }}</h4>
                    <span class="h3 mb-0">{{ \App\Models\Reservation::where('status', 'pending')->count() }}</span>
                    <img class="resturant-icon" src="{{ asset('assets/admin/img/dashboard/pending.png') }}" alt="dashboard">
                </a>
            </div>

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
                <div class="resturant-card dashboard--card">
                    <h4 class="subtitle">{{ 'Reservas de hoy' }}</h4>
                    <span class="h3 mb-0">{{ \App\Models\Reservation::whereDate('reservation_date', today())->count() }}</span>
                    <img class="resturant-icon" src="{{ asset('assets/admin/img/dashboard/today.png') }}" alt="dashboard">
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-header-title">{{ 'Acceso rápido' }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.sabores.dashboard') }}" class="btn btn-outline-primary btn-block">
                            <i class="tio-home-vs-1-outlined"></i> {{ 'Panel de Control' }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.sabores.reservations') }}" class="btn btn-outline-info btn-block">
                            <i class="tio-calendar"></i> {{ 'Reservas' }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.sabores.restaurants') }}" class="btn btn-outline-success btn-block">
                            <i class="tio-restaurant"></i> {{ 'Restaurantes' }}
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('admin.sabores.analytics') }}" class="btn btn-outline-warning btn-block">
                            <i class="tio-chart-bar-4"></i> {{ 'Analítica' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Welcome Message -->
        <div class="card">
            <div class="card-body text-center py-5">
                <img src="{{ asset('assets/admin/img/dashboard/welcome.png') }}" alt="welcome" class="mb-3" style="max-width: 200px;">
                <h3>{{ 'Bienvenidos a Sabores de la Ciudad' }}</h3>
                <p class="text-muted">{{ 'Gestiona tus reservas en restaurantes y descubre las mejores experiencias gastronómicas de tu ciudad.' }}</p>
                <a href="{{ route('admin.sabores.reservations') }}" class="btn btn-primary">
                    {{ 'Ver Reservas' }}
                </a>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
@endpush
