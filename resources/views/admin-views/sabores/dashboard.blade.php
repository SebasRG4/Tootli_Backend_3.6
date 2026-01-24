@extends('layouts.admin.app')

@section('title', translate('Sabores de la Ciudad Dashboard'))

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/dashboard.png') }}" class="w--20" alt="">
                </span>
                <span>{{ translate('Sabores de la Ciudad Dashboard') }}</span>
            </h1>
        </div>
        <!-- End Page Header -->

        <!-- Stats Cards -->
        <div class="row g-2">
            <!-- Total Restaurants -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle">{{ translate('Total Restaurants') }}</h6>
                        <div class="row align-items-center gx-2 mb-1">
                            <div class="col-6">
                                <h2 class="card-title text-hover-primary mb-0">{{ $total_restaurants }}</h2>
                            </div>
                            <div class="col-6">
                                <div class="chartjs-custom" style="height: 25px">
                                    <i class="tio-restaurant" style="font-size: 30px; color: #4CAF50;"></i>
                                </div>
                            </div>
                        </div>
                        <span class="badge badge-soft-success">
                            <i class="tio-trending-up"></i> {{ $restaurants_with_reservations }}
                            {{ translate('accept reservations') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total Reservations -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle">{{ translate('Total Reservations') }}</h6>
                        <div class="row align-items-center gx-2 mb-1">
                            <div class="col-6">
                                <h2 class="card-title text-hover-primary mb-0">{{ $total_reservations }}</h2>
                            </div>
                            <div class="col-6">
                                <div class="chartjs-custom" style="height: 25px">
                                    <i class="tio-calendar" style="font-size: 30px; color: #2196F3;"></i>
                                </div>
                            </div>
                        </div>
                        <span class="badge badge-soft-info">
                            <i class="tio-calendar-day"></i> {{ $today_reservations }} {{ translate('today') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pending Reservations -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle">{{ translate('Pending Reservations') }}</h6>
                        <div class="row align-items-center gx-2 mb-1">
                            <div class="col-6">
                                <h2 class="card-title text-hover-primary mb-0">{{ $pending_reservations }}</h2>
                            </div>
                            <div class="col-6">
                                <div class="chartjs-custom" style="height: 25px">
                                    <i class="tio-time" style="font-size: 30px; color: #FF9800;"></i>
                                </div>
                            </div>
                        </div>
                        <span class="badge badge-soft-warning">
                            <i class="tio-checkmark-circle"></i> {{ $confirmed_reservations }} {{ translate('confirmed') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Upcoming Reservations -->
            <div class="col-sm-6 col-lg-3">
                <div class="card card-hover-shadow h-100">
                    <div class="card-body">
                        <h6 class="card-subtitle">{{ translate('Upcoming (7 days)') }}</h6>
                        <div class="row align-items-center gx-2 mb-1">
                            <div class="col-6">
                                <h2 class="card-title text-hover-primary mb-0">{{ $upcoming_reservations }}</h2>
                            </div>
                            <div class="col-6">
                                <div class="chartjs-custom" style="height: 25px">
                                    <i class="tio-arrow-forward" style="font-size: 30px; color: #9C27B0;"></i>
                                </div>
                            </div>
                        </div>
                        <span class="badge badge-soft-primary">
                            <i class="tio-done"></i> {{ $completed_reservations }} {{ translate('completed') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Stats Cards -->

        <div class="row g-2 mt-3">
            <!-- Recent Reservations -->
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-header-title">
                            <i class="tio-calendar-month"></i>
                            {{ translate('Recent Reservations') }}
                        </h5>
                        <a href="{{ route('admin.sabores.reservations') }}" class="btn btn-sm btn-outline-primary">
                            {{ translate('View All') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ translate('Code') }}</th>
                                        <th>{{ translate('Customer') }}</th>
                                        <th>{{ translate('Restaurant') }}</th>
                                        <th>{{ translate('Date & Time') }}</th>
                                        <th>{{ translate('Party Size') }}</th>
                                        <th>{{ translate('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recent_reservations as $reservation)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.sabores.reservations.details', $reservation->id) }}">
                                                    {{ $reservation->confirmation_code }}
                                                </a>
                                            </td>
                                            <td>
                                                {{ $reservation->user->f_name }} {{ $reservation->user->l_name }}
                                            </td>
                                            <td>{{ $reservation->store->name }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') }}
                                                <br>
                                                <small
                                                    class="text-muted">{{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}</small>
                                            </td>
                                            <td>{{ $reservation->party_size }} {{ translate('people') }}</td>
                                            <td>
                                                @if($reservation->status == 'pending')
                                                    <span class="badge badge-soft-warning">{{ translate('Pending') }}</span>
                                                @elseif($reservation->status == 'confirmed')
                                                    <span class="badge badge-soft-success">{{ translate('Confirmed') }}</span>
                                                @elseif($reservation->status == 'completed')
                                                    <span class="badge badge-soft-primary">{{ translate('Completed') }}</span>
                                                @else
                                                    <span class="badge badge-soft-danger">{{ translate('Cancelled') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">{{ translate('No reservations yet') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Restaurants -->
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-header-title">
                            <i class="tio-star"></i>
                            {{ translate('Top Restaurants') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled list-unstyled-py-3">
                            @forelse($top_restaurants as $restaurant)
                                <li>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm avatar-circle mr-3">
                                            <img class="avatar-img" src="{{ $restaurant->logo_full_url }}"
                                                onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'"
                                                alt="{{ $restaurant->name }}">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="text-hover-primary mb-0">{{ $restaurant->name }}</h5>
                                            <span class="font-size-sm text-body">
                                                {{ $restaurant->reservations_count }} {{ translate('reservations') }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center">{{ translate('No data available') }}</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-2 mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-header-title">
                            <i class="tio-dashboard"></i>
                            {{ translate('Quick Actions') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="{{ route('admin.sabores.reservations', ['status' => 'pending']) }}"
                                    class="btn btn-outline-warning btn-block">
                                    <i class="tio-time"></i> {{ translate('Pending Reservations') }}
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.sabores.restaurants') }}"
                                    class="btn btn-outline-primary btn-block">
                                    <i class="tio-restaurant"></i> {{ translate('Manage Restaurants') }}
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.sabores.coupons') }}" class="btn btn-outline-success btn-block">
                                    <i class="tio-gift"></i> {{ translate('View Coupons') }}
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.sabores.analytics') }}" class="btn btn-outline-info btn-block">
                                    <i class="tio-chart-bar-4"></i> {{ translate('View Analytics') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
@endpush