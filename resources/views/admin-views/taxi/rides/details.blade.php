@extends('layouts.admin.app')

@section('title', 'Detalles del viaje')

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
                        {{ 'Conducir' }} #{{ $ride->id }}
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
                        <h5 class="card-header-title">{{ 'Estado del viaje' }}</h5>
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
                                <strong>{{ 'Cancelado por' }}:</strong> {{ ucfirst($ride->cancelled_by) }}<br>
                                @if($ride->cancellation_reason)
                                    <strong>{{ 'Razón' }}:</strong> {{ $ride->cancellation_reason }}
                                @endif
                            </div>
                        @endif

                        @if(!in_array($ride->status, ['completed', 'cancelled']))
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-primary btn-block" type="button" data-toggle="collapse"
                                    data-target="#collapseStatus" aria-expanded="false" aria-controls="collapseStatus">
                                    {{ 'Cambiar estado' }}
                                </button>
                                <div class="collapse mt-2" id="collapseStatus">
                                    <form action="{{ route('admin.taxi.rides.update-status', $ride->id) }}" method="POST">
                                        @csrf
                                        <div class="form-group mb-2">
                                            <select name="status" class="form-control">
                                                <option value="pending" {{ $ride->status == 'pending' ? 'selected' : '' }}>
                                                    {{ 'Pendiente' }}</option>
                                                <option value="accepted" {{ $ride->status == 'accepted' ? 'selected' : '' }}>
                                                    {{ 'Aceptado' }}</option>
                                                <option value="arriving" {{ $ride->status == 'arriving' ? 'selected' : '' }}>
                                                    {{ 'Llegando' }}</option>
                                                <option value="arrived" {{ $ride->status == 'arrived' ? 'selected' : '' }}>
                                                    {{ 'Llegó' }}</option>
                                                <option value="in_progress" {{ $ride->status == 'in_progress' ? 'selected' : '' }}>{{ 'En curso' }}</option>
                                                <option value="completed" {{ $ride->status == 'completed' ? 'selected' : '' }}>
                                                    {{ 'Terminado' }}</option>
                                                <option value="cancelled" {{ $ride->status == 'cancelled' ? 'selected' : '' }}>
                                                    {{ 'Cancelado' }}</option>
                                            </select>
                                        </div>
                                        <button type="submit"
                                            class="btn btn-primary btn-sm btn-block">{{ 'Estado de actualización' }}</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Route Info -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-header-title">{{ 'Información de ruta' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted">{{ 'Ubicación de recogida' }}</label>
                                    <p class="font-weight-bold mb-1">{{ $ride->pickup_address }}</p>
                                    <small class="text-muted">{{ $ride->pickup_lat }}, {{ $ride->pickup_lng }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted">{{ 'Lugar de entrega' }}</label>
                                    <p class="font-weight-bold mb-1">{{ $ride->dropoff_address }}</p>
                                    <small class="text-muted">{{ $ride->dropoff_lat }}, {{ $ride->dropoff_lng }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h4 mb-0">{{ number_format($ride->estimated_distance_km, 1) }} km</div>
                                    <small class="text-muted">{{ 'Distancia' }}</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h4 mb-0">{{ $ride->estimated_duration_min }} min</div>
                                    <small class="text-muted">{{ 'Est. Duración' }}</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <span
                                        class="badge badge-soft-{{ $ride->vehicle_type == 'premium' ? 'warning' : ($ride->vehicle_type == 'comfort' ? 'info' : 'secondary') }}">
                                        {{ ucfirst($ride->vehicle_type) }}
                                    </span>
                                    <small class="d-block text-muted mt-1">{{ 'Tipo de vehículo' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-header-title">{{ 'Línea de tiempo' }}</h5>
                    </div>
                    <div class="card-body">
                        <ul class="step step-icon-sm">
                            <li class="step-item">
                                <div class="step-content-wrapper">
                                    <span class="step-icon step-icon-soft-success"><i
                                            class="tio-checkmark-circle"></i></span>
                                    <div class="step-content">
                                        <h6 class="mb-0">{{ 'Viaje solicitado' }}</h6>
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
                                            <h6 class="mb-0">{{ 'Aceptado por el conductor' }}</h6>
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
                                            <h6 class="mb-0">{{ 'El conductor llegó' }}</h6>
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
                                            <h6 class="mb-0">{{ 'Viaje iniciado' }}</h6>
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
                                            <h6 class="mb-0">{{ 'Terminado' }}</h6>
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
                                            <h6 class="mb-0">{{ 'Cancelado' }}</h6>
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
                        <h5 class="card-header-title">{{ 'Cliente' }}</h5>
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
                        <h5 class="card-header-title">{{ 'Conductor' }}</h5>
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
                            <p class="text-muted text-center">{{ 'Ningún conductor asignado' }}</p>
                        @endif
                    </div>
                </div>

                <!-- Fare Details -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-header-title">{{ 'Detalles de la tarifa' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ 'Tarifa estimada' }}</span>
                            <span>${{ number_format($ride->estimated_fare, 2) }}</span>
                        </div>
                        @if($ride->surge_multiplier > 1)
                            <div class="d-flex justify-content-between mb-2 text-warning">
                                <span>{{ 'Aumento' }}</span>
                                <span>{{ $ride->surge_multiplier }}x</span>
                            </div>
                        @endif
                        @if($ride->tip > 0)
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>{{ 'Consejo' }}</span>
                                <span>+ ${{ number_format($ride->tip, 2) }}</span>
                            </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between font-weight-bold">
                            <span>{{ 'Tarifa final' }}</span>
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
                            <h5 class="card-header-title">{{ 'Calificaciones' }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <small class="text-muted d-block">{{ 'Calificación del conductor' }}</small>
                                    @if($ride->driver_rating)
                                        <span class="h4"><i class="tio-star text-warning"></i> {{ $ride->driver_rating }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                                <div class="col-6 text-center">
                                    <small class="text-muted d-block">{{ 'Calificación del usuario' }}</small>
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