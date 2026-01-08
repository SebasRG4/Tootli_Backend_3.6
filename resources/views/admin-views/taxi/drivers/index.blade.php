@extends('layouts.admin.app')

@section('title', translate('Taxi Drivers'))

@push('css_or_js')
    <link href="{{ asset('public/assets/admin/css/select2.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-user"></i> {{ translate('Taxi Drivers') }}
                        <span class="badge badge-soft-dark ml-2">{{ $drivers->total() }}</span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addDriverModal">
                        <i class="tio-add"></i> {{ translate('Add Driver') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Search/Filter -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.taxi.drivers') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control"
                                placeholder="{{ translate('Search by name or phone') }}" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">{{ translate('All Status') }}</option>
                                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>
                                    {{ translate('Available') }}</option>
                                <option value="busy" {{ request('status') == 'busy' ? 'selected' : '' }}>
                                    {{ translate('Busy') }}</option>
                                <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>
                                    {{ translate('Offline') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">{{ translate('Search') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Drivers Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('ID') }}</th>
                                <th>{{ translate('Driver') }}</th>
                                <th>{{ translate('Contact') }}</th>
                                <th>{{ translate('Vehicle') }}</th>
                                <th>{{ translate('Zone') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Rating') }}</th>
                                <th>{{ translate('Verified') }}</th>
                                <th>{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($drivers as $driver)
                                <tr>
                                    <td>{{ $driver->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-circle mr-3">
                                                <img class="avatar-img"
                                                    src="{{ $driver->user->imageFullUrl ?? asset('public/assets/admin/img/160x160/img1.jpg') }}"
                                                    alt="">
                                            </div>
                                            <div>
                                                <span class="d-block font-weight-bold">{{ $driver->user->f_name ?? '' }}
                                                    {{ $driver->user->l_name ?? '' }}</span>
                                                <small class="text-muted">{{ $driver->total_rides }}
                                                    {{ translate('rides') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-block">{{ $driver->user->phone ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $driver->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        @if($driver->vehicle)
                                            <span class="d-block">{{ $driver->vehicle->brand }} {{ $driver->vehicle->model }}</span>
                                            <small class="text-muted">{{ $driver->vehicle->plate }} -
                                                {{ ucfirst($driver->vehicle->type) }}</small>
                                        @else
                                            <span class="badge badge-soft-warning">{{ translate('No vehicle') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $driver->zone->name ?? 'N/A' }}</td>
                                    <td>
                                        <span
                                            class="badge badge-soft-{{ $driver->status == 'available' ? 'success' : ($driver->status == 'busy' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($driver->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <i class="tio-star text-warning"></i> {{ number_format($driver->rating, 1) }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.taxi.drivers.toggle-verification', $driver->id) }}"
                                            class="btn btn-sm {{ $driver->is_verified ? 'btn-success' : 'btn-outline-secondary' }}">
                                            {{ $driver->is_verified ? translate('Verified') : translate('Verify') }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                                data-toggle="dropdown">
                                                <i class="tio-more-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#editDriverModal{{ $driver->id }}">
                                                    <i class="tio-edit"></i> {{ translate('Edit') }}
                                                </a>
                                                <form action="{{ route('admin.taxi.drivers.delete', $driver->id) }}"
                                                    method="POST" onsubmit="return confirm('{{ translate('Are you sure?') }}')">
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
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">{{ translate('No drivers found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $drivers->links() }}
            </div>
        </div>
    </div>

    <!-- Add Driver Modal -->
    <div class="modal fade" id="addDriverModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Add New Driver') }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('admin.taxi.drivers.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ translate('Select User') }} *</label>
                            <select name="user_id" class="form-control" id="userSearch" required>
                                <option value="">{{ translate('Search user...') }}</option>
                            </select>
                            <small class="text-muted">{{ translate('Search by name, phone or email') }}</small>
                        </div>
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
                                    <label>{{ translate('Vehicle') }}</label>
                                    <select name="vehicle_id" class="form-control">
                                        <option value="">{{ translate('No vehicle') }}</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">{{ $vehicle->brand }} {{ $vehicle->model }}
                                                ({{ $vehicle->plate }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ translate('License Number') }}</label>
                                    <input type="text" name="license_number" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ translate('License Expiry') }}</label>
                                    <input type="date" name="license_expiry" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_verified" class="custom-control-input" id="isVerified">
                                <label class="custom-control-label"
                                    for="isVerified">{{ translate('Mark as Verified') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ translate('Add Driver') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#userSearch').select2({
                dropdownParent: $('#addDriverModal'),
                ajax: {
                    url: '{{ route("admin.taxi.drivers.search-users") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { search: params.term };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(function (user) {
                                return {
                                    id: user.id,
                                    text: user.f_name + ' ' + user.l_name + ' (' + user.phone + ')'
                                };
                            })
                        };
                    }
                },
                minimumInputLength: 2
            });
        });
    </script>
@endpush