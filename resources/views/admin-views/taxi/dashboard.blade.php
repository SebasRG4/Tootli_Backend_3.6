@extends('layouts.admin.app')

@section('title', 'Panel de taxis')

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-car"></i> {{ 'Panel de taxis' }}
                    </h1>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">{{ 'Conductores totales' }}</h6>
                        <h2 class="card-title text-inherit">{{ $stats['total_drivers'] }}</h2>
                        <span class="badge badge-soft-success">{{ $stats['online_drivers'] }}
                            {{ 'En línea' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">{{ 'Vehículos totales' }}</h6>
                        <h2 class="card-title text-inherit">{{ $stats['total_vehicles'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">{{ 'Viajes totales' }}</h6>
                        <h2 class="card-title text-inherit">{{ $stats['total_rides'] }}</h2>
                        <span class="badge badge-soft-warning">{{ $stats['pending_rides'] }}
                            {{ 'Pendiente' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">{{ 'Ganancias totales' }}</h6>
                        <h2 class="card-title text-inherit">${{ number_format($stats['total_earnings'], 2) }}</h2>
                        <span class="badge badge-soft-info">{{ $stats['completed_rides'] }}
                            {{ 'Terminado' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row gx-2 gx-lg-3 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-header-title">{{ 'Acciones rápidas' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.taxi.drivers') }}" class="btn btn-primary btn-block">
                                    <i class="tio-user"></i> {{ 'Administrar controladores' }}
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.taxi.vehicles') }}" class="btn btn-info btn-block">
                                    <i class="tio-car"></i> {{ 'Administrar vehículos' }}
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.taxi.fare-config') }}" class="btn btn-success btn-block">
                                    <i class="tio-dollar"></i> {{ 'Configuración de tarifas' }}
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.taxi.rides') }}" class="btn btn-warning btn-block">
                                    <i class="tio-route"></i> {{ 'Ver paseos' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Rides -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-header-title">{{ 'Viajes recientes' }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ 'IDENTIFICACIÓN' }}</th>
                                <th>{{ 'Usuario' }}</th>
                                <th>{{ 'Conductor' }}</th>
                                <th>{{ 'Estado' }}</th>
                                <th>{{ 'Tarifa' }}</th>
                                <th>{{ 'Fecha' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentRides as $ride)
                                <tr>
                                    <td>#{{ $ride->id }}</td>
                                    <td>{{ $ride->user->f_name ?? 'N/A' }} {{ $ride->user->l_name ?? '' }}</td>
                                    <td>{{ $ride->driver->user->f_name ?? 'Pending' }}</td>
                                    <td>
                                        <span
                                            class="badge badge-soft-{{ $ride->status == 'completed' ? 'success' : ($ride->status == 'cancelled' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($ride->status) }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($ride->final_fare ?? $ride->estimated_fare, 2) }}</td>
                                    <td>{{ $ride->created_at->format('M d, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">{{ 'Aún no hay viajes' }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection