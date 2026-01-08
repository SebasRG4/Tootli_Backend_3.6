@extends('layouts.admin.app')

@section('title', translate('Taxi Rides'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-route"></i> {{ translate('Rides') }}
                        <span class="badge badge-soft-dark ml-2">{{ $rides->total() }}</span>
                    </h1>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.taxi.rides') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">{{ translate('All Status') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                    {{ translate('Pending') }}</option>
                                <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>
                                    {{ translate('Accepted') }}</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                                    {{ translate('In Progress') }}</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                    {{ translate('Completed') }}</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                    {{ translate('Cancelled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"
                                placeholder="{{ translate('From') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"
                                placeholder="{{ translate('To') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">{{ translate('Filter') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rides Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('ID') }}</th>
                                <th>{{ translate('User') }}</th>
                                <th>{{ translate('Driver') }}</th>
                                <th>{{ translate('Route') }}</th>
                                <th>{{ translate('Distance') }}</th>
                                <th>{{ translate('Fare') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Payment') }}</th>
                                <th>{{ translate('Date') }}</th>
                                <th>{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rides as $ride)
                                <tr>
                                    <td>#{{ $ride->id }}</td>
                                    <td>
                                        <span class="d-block">{{ $ride->user->f_name ?? '' }}
                                            {{ $ride->user->l_name ?? '' }}</span>
                                        <small class="text-muted">{{ $ride->user->phone ?? '' }}</small>
                                    </td>
                                    <td>
                                        @if($ride->driver)
                                            {{ $ride->driver->user->f_name ?? 'N/A' }}
                                        @else
                                            <span class="badge badge-soft-secondary">{{ translate('Pending') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            <strong>{{ translate('From') }}:</strong>
                                            {{ Str::limit($ride->pickup_address, 30) }}<br>
                                            <strong>{{ translate('To') }}:</strong> {{ Str::limit($ride->dropoff_address, 30) }}
                                        </small>
                                    </td>
                                    <td>{{ number_format($ride->estimated_distance_km, 1) }} km</td>
                                    <td>
                                        <strong>${{ number_format($ride->final_fare ?? $ride->estimated_fare, 2) }}</strong>
                                        @if($ride->surge_multiplier > 1)
                                            <span class="badge badge-soft-warning">{{ $ride->surge_multiplier }}x</span>
                                        @endif
                                    </td>
                                    <td>
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
                                        <span class="badge badge-soft-{{ $statusColors[$ride->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $ride->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $ride->payment_status == 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($ride->payment_method) }}
                                        </span>
                                    </td>
                                    <td>{{ $ride->created_at->format('M d, H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.taxi.rides.details', $ride->id) }}"
                                            class="btn btn-outline-info btn-sm">
                                            <i class="tio-visible"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">{{ translate('No rides found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $rides->links() }}
            </div>
        </div>
    </div>
@endsection