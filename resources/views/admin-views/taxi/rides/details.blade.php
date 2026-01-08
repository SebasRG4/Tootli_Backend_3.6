@extends('layouts.admin.app')

@section('title', translate('Ride Details'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <a href="{{ route('admin.taxi.rides') }}"
                            class="btn btn-icon btn-ghost-secondary rounded-circle mr-2">
                            <i class="tio-arrow-left"></i>
                        </a>
                        {{ translate('Ride') }} #{{ $ride->id }}
                    </h1>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Status Card -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-header-title">{{ translate('Ride Status') }}</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $statusColors = [
                                'pending' => 'secondary',
                                'accepted' => 'info',
                                'arriving' => 'primary',
                                'arrived' => 'primary',
                                'in_progress' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            ];
                        @endphp
                        <div class="text-center">
                            <span class="badge badge-{{ $statusColors[$ride->status] ?? 'secondary' }} p-3"
                                style="font-size: 1.2rem;">
                                {{ ucfirst(str_replace('_', ' ', $ride->status)) }}
                            </span>
                        </div>

                        @if($ride->status == 'cancelled')
                            <div class="alert alert-danger mt-3">
                                <strong>{{ translate('Cancelled by') }}:</strong> {{ ucfirst($ride->cancelled_by) }}<br>
                                @if($ride->cancellation_reason)
                                    <strong>{{ translate('Reason') }}:</strong> {{ $ride->cancellation_reason }}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Route Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-header-title">{{ translate('Route Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted">{{ translate('Pickup Location') }}</label>
                                    <p class="font-weight-bold mb-1">{{ $ride->pickup_address }}</p>
                                    <small class="text-muted">{{ $ride->pickup_lat }}, {{ $ride->pickup_lng }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted">{{ translate('Dropoff Location') }}</label>
                                    <p class="font-weight-bold mb-1">{{ $ride->dropoff_address }}</p>
                                    <small class="text-muted">{{ $ride->dropoff_lat }}, {{ $ride->dropoff_lng }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h4 mb-0">{{ number_format($ride->estimated_distance_km, 1) }} km</div>
                                    <small class="text-muted">{{ translate('Distance') }}</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h4 mb-0">{{ $ride->estimated_duration_min }} min</div>
                                    <small class="text-muted">{{ translate('Est. Duration') }}</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <span
                                        class="badge badge-soft-{{ $ride->vehicle_type == 'premium' ? 'warning' : ($ride->vehicle_type == 'comfort' ? 'info' : 'secondary') }}">
                                        {{ ucfirst($ride->vehicle_type) }}
                                    </span>
                                    <small class="d-block text-muted mt-1">{{ translate('Vehicle Type') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-header-title">{{ translate('Timeline') }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="step step-icon-sm">
                            <li class="step-item">
                                <div class="step-content-wrapper">
                                    <span class="step-icon step-icon-soft-success"><i
                                            class="tio-checkmark-circle"></i></span>
                                    <div class="step-content">
                                        <h6 class="mb-0">{{ translate('Ride Requested') }}</h6>
                                        <small class="text-muted">{{ $ride->created_at->format('M d, Y H:i:s') }}</small>
                                    </div>
                                </div>
                            </li>
                            @if($ride->accepted_at)
                                <li class="step-item">
                                    <div class="step-content-wrapper">
                                        <span class="step-icon step-icon-soft-success"><i
                                                class="tio-checkmark-circle"></i></span>
                                        <div class="step-content">
                                            <h6 class="mb-0">{{ translate('Accepted by Driver') }}</h6>
                                            <small class="text-muted">{{ $ride->accepted_at->format('M d, Y H:i:s') }}</small>
                                        </div>
                                    </div>
                                </li>
                            @endif
                            @if($ride->arrived_at)
                                <li class="step-item">
                                    <div class="step-content-wrapper">
                                        <span class="step-icon step-icon-soft-success"><i
                                                class="tio-checkmark-circle"></i></span>
                                        <div class="step-content">
                                            <h6 class="mb-0">{{ translate('Driver Arrived') }}</h6>
                                            <small class="text-muted">{{ $ride->arrived_at->format('M d, Y H:i:s') }}</small>
                                        </div>
                                    </div>
                                </li>
                            @endif
                            @if($ride->started_at)
                                <li class="step-item">
                                    <div class="step-content-wrapper">
                                        <span class="step-icon step-icon-soft-success"><i
                                                class="tio-checkmark-circle"></i></span>
                                        <div class="step-content">
                                            <h6 class="mb-0">{{ translate('Ride Started') }}</h6>
                                            <small class="text-muted">{{ $ride->started_at->format('M d, Y H:i:s') }}</small>
                                        </div>
                                    </div>
                                </li>
                            @endif
                            @if($ride->completed_at)
                                <li class="step-item">
                                    <div class="step-content-wrapper">
                                        <span class="step-icon step-icon-soft-success"><i
                                                class="tio-checkmark-circle"></i></span>
                                        <div class="step-content">
                                            <h6 class="mb-0">{{ translate('Completed') }}</h6>
                                            <small class="text-muted">{{ $ride->completed_at->format('M d, Y H:i:s') }}</small>
                                        </div>
                                    </div>
                                </li>
                            @endif
                            @if($ride->cancelled_at)
                                <li class="step-item">
                                    <div class="step-content-wrapper">
                                        <span class="step-icon step-icon-soft-danger"><i class="tio-clear-circle"></i></span>
                                        <div class="step-content">
                                            <h6 class="mb-0">{{ translate('Cancelled') }}</h6>
                                            <small class="text-muted">{{ $ride->cancelled_at->format('M d, Y H:i:s') }}</small>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- User Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-header-title">{{ translate('Customer') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-circle mr-3">
                                <img class="avatar-img"
                                    src="{{ $ride->user->imageFullUrl ?? asset('public/assets/admin/img/160x160/img1.jpg') }}"
                                    alt="">
                            </div>
                            <div>
                                <span class="d-block font-weight-bold">{{ $ride->user->f_name ?? '' }}
                                    {{ $ride->user->l_name ?? '' }}</span>
                                <small class="text-muted">{{ $ride->user->phone ?? '' }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Driver Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-header-title">{{ translate('Driver') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($ride->driver)
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-circle mr-3">
                                    <img class="avatar-img"
                                        src="{{ $ride->driver->user->imageFullUrl ?? asset('public/assets/admin/img/160x160/img1.jpg') }}"
                                        alt="">
                                </div>
                                <div>
                                    <span class="d-block font-weight-bold">{{ $ride->driver->user->f_name ?? '' }}
                                        {{ $ride->driver->user->l_name ?? '' }}</span>
                                    <small class="text-muted">{{ $ride->driver->user->phone ?? '' }}</small>
                                </div>
                            </div>
                            @if($ride->driver->vehicle)
                                <div class="bg-light p-3 rounded">
                                    <strong>{{ $ride->driver->vehicle->brand }} {{ $ride->driver->vehicle->model }}</strong>
                                    <span class="d-block text-muted">{{ $ride->driver->vehicle->plate }} -
                                        {{ $ride->driver->vehicle->color }}</span>
                                </div>
                            @endif
                        @else
                            <p class="text-muted text-center">{{ translate('No driver assigned') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Fare Details -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-header-title">{{ translate('Fare Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ translate('Estimated Fare') }}</span>
                            <span>${{ number_format($ride->estimated_fare, 2) }}</span>
                        </div>
                        @if($ride->surge_multiplier > 1)
                            <div class="d-flex justify-content-between mb-2 text-warning">
                                <span>{{ translate('Surge') }}</span>
                                <span>{{ $ride->surge_multiplier }}x</span>
                            </div>
                        @endif
                        @if($ride->tip > 0)
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>{{ translate('Tip') }}</span>
                                <span>+ ${{ number_format($ride->tip, 2) }}</span>
                            </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between font-weight-bold">
                            <span>{{ translate('Final Fare') }}</span>
                            <span class="h5 mb-0">${{ number_format($ride->final_fare ?? $ride->estimated_fare, 2) }}</span>
                        </div>
                        <div class="mt-3">
                            <span class="badge badge-{{ $ride->payment_status == 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($ride->payment_method) }} - {{ ucfirst($ride->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Ratings -->
                @if($ride->status == 'completed')
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-header-title">{{ translate('Ratings') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <small class="text-muted d-block">{{ translate('Driver Rating') }}</small>
                                    @if($ride->driver_rating)
                                        <span class="h4"><i class="tio-star text-warning"></i> {{ $ride->driver_rating }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                                <div class="col-6 text-center">
                                    <small class="text-muted d-block">{{ translate('User Rating') }}</small>
                                    @if($ride->user_rating)
                                        <span class="h4"><i class="tio-star text-warning"></i> {{ $ride->user_rating }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection