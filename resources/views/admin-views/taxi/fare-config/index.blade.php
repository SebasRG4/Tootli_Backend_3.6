@extends('layouts.admin.app')

@section('title', translate('Taxi Fare Configuration'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-dollar"></i> {{ translate('Fare Configuration') }}
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addFareModal">
                        <i class="tio-add"></i> {{ translate('Add Fare Config') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Fare Configs Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('Zone') }}</th>
                                <th>{{ translate('Vehicle Type') }}</th>
                                <th>{{ translate('Base Fare') }}</th>
                                <th>{{ translate('Per KM') }}</th>
                                <th>{{ translate('Per Min') }}</th>
                                <th>{{ translate('Minimum') }}</th>
                                <th>{{ translate('Surge') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fares as $fare)
                                <tr>
                                    <td>{{ $fare->zone->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-soft-{{ $fare->vehicle_type == 'premium' ? 'warning' : ($fare->vehicle_type == 'comfort' ? 'info' : 'secondary') }}">
                                            {{ ucfirst($fare->vehicle_type) }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($fare->base_fare, 2) }}</td>
                                    <td>${{ number_format($fare->per_km_rate, 2) }}</td>
                                    <td>${{ number_format($fare->per_min_rate, 2) }}</td>
                                    <td>${{ number_format($fare->minimum_fare, 2) }}</td>
                                    <td>
                                        @if($fare->surge_enabled)
                                            <span class="badge badge-soft-warning">{{ translate('Up to') }} {{ $fare->max_surge_multiplier }}x</span>
                                        @else
                                            <span class="badge badge-soft-secondary">{{ translate('Disabled') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $fare->status ? 'success' : 'danger' }}">
                                            {{ $fare->status ? translate('Active') : translate('Inactive') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                <i class="tio-more-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editFareModal{{ $fare->id }}">
                                                    <i class="tio-edit"></i> {{ translate('Edit') }}
                                                </a>
                                                <form action="{{ route('admin.taxi.fare-config.delete', $fare->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="tio-delete"></i> {{ translate('Delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Modal for each fare -->
                                <div class="modal fade" id="editFareModal{{ $fare->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ translate('Edit Fare Config') }}</h5>
                                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                            </div>
                                            <form action="{{ route('admin.taxi.fare-config.update', $fare->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('Base Fare') }} *</label>
                                                                <input type="number" step="0.01" name="base_fare" class="form-control" value="{{ $fare->base_fare }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('Per KM Rate') }} *</label>
                                                                <input type="number" step="0.01" name="per_km_rate" class="form-control" value="{{ $fare->per_km_rate }}" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('Per Minute Rate') }} *</label>
                                                                <input type="number" step="0.01" name="per_min_rate" class="form-control" value="{{ $fare->per_min_rate }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('Minimum Fare') }} *</label>
                                                                <input type="number" step="0.01" name="minimum_fare" class="form-control" value="{{ $fare->minimum_fare }}" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('Cancellation Fee') }}</label>
                                                                <input type="number" step="0.01" name="cancellation_fee" class="form-control" value="{{ $fare->cancellation_fee }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ translate('Max Surge') }}</label>
                                                                <input type="number" step="0.1" name="max_surge_multiplier" class="form-control" value="{{ $fare->max_surge_multiplier }}" min="1" max="5">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" name="surge_enabled" class="custom-control-input" id="surgeEnabled{{ $fare->id }}" {{ $fare->surge_enabled ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="surgeEnabled{{ $fare->id }}">{{ translate('Enable Surge Pricing') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" name="status" class="custom-control-input" id="fareStatus{{ $fare->id }}" {{ $fare->status ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="fareStatus{{ $fare->id }}">{{ translate('Active') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                                                    <button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">{{ translate('No fare configurations found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $fares->links() }}
            </div>
        </div>
    </div>

    <!-- Add Fare Modal -->
    <div class="modal fade" id="addFareModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Add Fare Configuration') }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('admin.taxi.fare-config.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ translate('Zone') }} *</label>
                                    <select name="zone_id" class="form-control" required>
                                        <option value="">{{ translate('Select Zone') }}</option>
                                        @foreach($zones as $zone)
                                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ translate('Vehicle Type') }} *</label>
                                    <select name="vehicle_type" class="form-control" required>
                                        <option value="economy">{{ translate('Economy') }}</option>
                                        <option value="comfort">{{ translate('Comfort') }}</option>
                                        <option value="premium">{{ translate('Premium') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ translate('Base Fare') }} *</label>
                                    <input type="number" step="0.01" name="base_fare" class="form-control" value="25" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ translate('Per KM Rate') }} *</label>
                                    <input type="number" step="0.01" name="per_km_rate" class="form-control" value="8" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ translate('Per Minute Rate') }} *</label>
                                    <input type="number" step="0.01" name="per_min_rate" class="form-control" value="2" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ translate('Minimum Fare') }} *</label>
                                    <input type="number" step="0.01" name="minimum_fare" class="form-control" value="35" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ translate('Cancellation Fee') }}</label>
                                    <input type="number" step="0.01" name="cancellation_fee" class="form-control" value="20">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ translate('Max Surge Multiplier') }}</label>
                                    <input type="number" step="0.1" name="max_surge_multiplier" class="form-control" value="2.0" min="1" max="5">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="surge_enabled" class="custom-control-input" id="newSurgeEnabled" checked>
                                <label class="custom-control-label" for="newSurgeEnabled">{{ translate('Enable Surge Pricing') }}</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="status" class="custom-control-input" id="newFareStatus" checked>
                                <label class="custom-control-label" for="newFareStatus">{{ translate('Active') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ translate('Add Configuration') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
