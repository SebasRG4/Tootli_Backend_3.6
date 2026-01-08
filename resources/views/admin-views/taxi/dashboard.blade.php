@extends('layouts.admin.app')

@section('title', translate('Taxi Dashboard'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-car"></i> {{ translate('Taxi Dashboard') }}
                    </h1>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">{{ translate('Total Drivers') }}</h6>
                        <h2 class="card-title text-inherit">{{ $stats['total_drivers'] }}</h2>
                        <span class="badge badge-soft-success">{{ $stats['online_drivers'] }}
                            {{ translate('Online') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">{{ translate('Total Vehicles') }}</h6>
                        <h2 class="card-title text-inherit">{{ $stats['total_vehicles'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">{{ translate('Total Rides') }}</h6>
                        <h2 class="card-title text-inherit">{{ $stats['total_rides'] }}</h2>
                        <span class="badge badge-soft-warning">{{ $stats['pending_rides'] }}
                            {{ translate('Pending') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2">{{ translate('Total Earnings') }}</h6>
                        <h2 class="card-title text-inherit">${{ number_format($stats['total_earnings'], 2) }}</h2>
                        <span class="badge badge-soft-info">{{ $stats['completed_rides'] }}
                            {{ translate('Completed') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row gx-2 gx-lg-3 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-header-title">{{ translate('Quick Actions') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.taxi.drivers') }}" class="btn btn-primary btn-block">
                                    <i class="tio-user"></i> {{ translate('Manage Drivers') }}
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.taxi.vehicles') }}" class="btn btn-info btn-block">
                                    <i class="tio-car"></i> {{ translate('Manage Vehicles') }}
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.taxi.fare-config') }}" class="btn btn-success btn-block">
                                    <i class="tio-dollar"></i> {{ translate('Fare Configuration') }}
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('admin.taxi.rides') }}" class="btn btn-warning btn-block">
                                    <i class="tio-route"></i> {{ translate('View Rides') }}
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
                <h5 class="card-header-title">{{ translate('Recent Rides') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('ID') }}</th>
                                <th>{{ translate('User') }}</th>
                                <th>{{ translate('Driver') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Fare') }}</th>
                                <th>{{ translate('Date') }}</th>
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
                                    <td colspan="6" class="text-center py-4">{{ translate('No rides yet') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection