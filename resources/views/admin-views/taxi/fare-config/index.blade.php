@extends('layouts.admin.app')

@section('title', 'Configuración de Tarifas de Taxi')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-dollar"></i> Configuración de Tarifas
                    </h1>
                    <p class="text-muted mb-0">Configura las tarifas por zona y tipo de vehículo</p>
                </div>
                <div class="col-sm-auto">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addFareModal">
                        <i class="tio-add"></i> Agregar Tarifa
                    </button>
                </div>
            </div>
        </div>

        <!-- Zone Cards -->
        @foreach($zones as $zone)
            @php
                $zoneFares = $fares->get($zone->id) ?? collect();
                $configuredTypes = $zoneFares->pluck('vehicle_type_id')->toArray();
            @endphp
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="tio-location-marker text-primary"></i>
                        {{ $zone->name }}
                        <span class="badge badge-soft-info ml-2">{{ $zoneFares->count() }} tipos configurados</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($zoneFares->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-borderless table-thead-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tipo de Vehículo</th>
                                        <th>Pasajeros</th>
                                        <th>Tarifa Base</th>
                                        <th>Por KM</th>
                                        <th>Por Min</th>
                                        <th>Mínimo</th>
                                        <th>Surge</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($zoneFares as $fare)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($fare->vehicleType && $fare->vehicleType->image)
                                                        <img src="{{ asset('storage/taxi_vehicle_type/'.$fare->vehicleType->image) }}" 
                                                             alt="{{ $fare->vehicleType->name }}" 
                                                             style="width: 40px; height: 30px; object-fit: contain; margin-right: 10px;">
                                                    @endif
                                                    <span class="font-weight-bold">{{ $fare->vehicleType->name ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-soft-secondary">
                                                    <i class="tio-user"></i> {{ $fare->vehicleType->max_passengers ?? 4 }}
                                                </span>
                                            </td>
                                            <td>${{ number_format($fare->base_fare, 2) }}</td>
                                            <td>${{ number_format($fare->per_km_rate, 2) }}</td>
                                            <td>${{ number_format($fare->per_min_rate, 2) }}</td>
                                            <td>${{ number_format($fare->minimum_fare, 2) }}</td>
                                            <td>
                                                @if($fare->surge_enabled)
                                                    <span class="badge badge-soft-warning">Hasta {{ $fare->max_surge_multiplier }}x</span>
                                                @else
                                                    <span class="badge badge-soft-secondary">Desactivado</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $fare->status ? 'success' : 'danger' }}">
                                                    {{ $fare->status ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editFareModal{{ $fare->id }}">
                                                    <i class="tio-edit"></i>
                                                </button>
                                                <form action="{{ route('admin.taxi.fare-config.delete', $fare->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="tio-delete"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editFareModal{{ $fare->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            Editar: {{ $zone->name }} - {{ $fare->vehicleType->name ?? '' }}
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                    </div>
                                                    <form action="{{ route('admin.taxi.fare-config.update', $fare->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Tarifa Base *</label>
                                                                        <input type="number" step="0.01" name="base_fare" class="form-control" value="{{ $fare->base_fare }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Tarifa por KM *</label>
                                                                        <input type="number" step="0.01" name="per_km_rate" class="form-control" value="{{ $fare->per_km_rate }}" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Tarifa por Minuto *</label>
                                                                        <input type="number" step="0.01" name="per_min_rate" class="form-control" value="{{ $fare->per_min_rate }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Tarifa Mínima *</label>
                                                                        <input type="number" step="0.01" name="minimum_fare" class="form-control" value="{{ $fare->minimum_fare }}" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Tarifa de Cancelación</label>
                                                                        <input type="number" step="0.01" name="cancellation_fee" class="form-control" value="{{ $fare->cancellation_fee }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Máximo Surge</label>
                                                                        <input type="number" step="0.1" name="max_surge_multiplier" class="form-control" value="{{ $fare->max_surge_multiplier }}" min="1" max="5">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" name="surge_enabled" class="custom-control-input" id="surgeEnabled{{ $fare->id }}" {{ $fare->surge_enabled ? 'checked' : '' }}>
                                                                    <label class="custom-control-label" for="surgeEnabled{{ $fare->id }}">Habilitar Precio Dinámico</label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" name="status" class="custom-control-input" id="fareStatus{{ $fare->id }}" {{ $fare->status ? 'checked' : '' }}>
                                                                    <label class="custom-control-label" for="fareStatus{{ $fare->id }}">Activo</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn btn-primary">Actualizar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="tio-warning-outlined" style="font-size: 2rem;"></i>
                            <p class="mb-0">No hay tipos de vehículo configurados para esta zona</p>
                            <small>Haz clic en "Agregar Tarifa" para añadir uno</small>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add Fare Modal -->
    <div class="modal fade" id="addFareModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Configuración de Tarifa</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('admin.taxi.fare-config.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Zona *</label>
                                    <select name="zone_id" class="form-control" required>
                                        <option value="">Selecciona Zona</option>
                                        @foreach($zones as $zone)
                                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tipo de Vehículo *</label>
                                    <select name="vehicle_type_id" class="form-control" required>
                                        <option value="">Selecciona Tipo</option>
                                        @foreach($vehicleTypes as $vType)
                                            <option value="{{ $vType->id }}">{{ $vType->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tarifa Base *</label>
                                    <input type="number" step="0.01" name="base_fare" class="form-control" value="25" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tarifa por KM *</label>
                                    <input type="number" step="0.01" name="per_km_rate" class="form-control" value="8" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tarifa por Minuto *</label>
                                    <input type="number" step="0.01" name="per_min_rate" class="form-control" value="2" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tarifa Mínima *</label>
                                    <input type="number" step="0.01" name="minimum_fare" class="form-control" value="35" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tarifa de Cancelación</label>
                                    <input type="number" step="0.01" name="cancellation_fee" class="form-control" value="20">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Máximo Multiplicador Surge</label>
                                    <input type="number" step="0.1" name="max_surge_multiplier" class="form-control" value="2.0" min="1" max="5">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="surge_enabled" class="custom-control-input" id="newSurgeEnabled" checked>
                                <label class="custom-control-label" for="newSurgeEnabled">Habilitar Precio Dinámico</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="status" class="custom-control-input" id="newFareStatus" checked>
                                <label class="custom-control-label" for="newFareStatus">Activo</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Agregar Configuración</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
