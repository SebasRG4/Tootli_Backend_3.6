@extends('layouts.admin.app')

@section('title', 'Vehículos taxi')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-car"></i> {{ 'Vehículos' }}
                        <span class="badge badge-soft-dark ml-2">{{ $vehicles->total() }}</span>
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addVehicleModal">
                        <i class="tio-add"></i> {{ 'Agregar vehículo' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.taxi.vehicles') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control"
                                placeholder="{{ 'Búsqueda por placa, marca, modelo' }}"
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="type" class="form-control">
                                <option value="">{{ 'Todos los tipos' }}</option>
                                <option value="economy" {{ request('type') == 'economy' ? 'selected' : '' }}>
                                    {{ 'Economía' }}</option>
                                <option value="comfort" {{ request('type') == 'comfort' ? 'selected' : '' }}>
                                    {{ 'Comodidad' }}</option>
                                <option value="premium" {{ request('type') == 'premium' ? 'selected' : '' }}>
                                    {{ 'De primera calidad' }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">{{ 'Filtrar' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Vehicles Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ 'IDENTIFICACIÓN' }}</th>
                                <th>{{ 'Vehículo' }}</th>
                                <th>{{ 'Lámina' }}</th>
                                <th>{{ 'Tipo' }}</th>
                                <th>{{ 'Color' }}</th>
                                <th>{{ 'Año' }}</th>
                                <th>{{ 'Asientos' }}</th>
                                <th>{{ 'Conductor' }}</th>
                                <th>{{ 'Estado' }}</th>
                                <th>{{ 'Comportamiento' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vehicles as $vehicle)
                                <tr>
                                    <td>{{ $vehicle->id }}</td>
                                    <td>
                                        <span class="font-weight-bold">{{ $vehicle->brand }}</span>
                                        <span class="d-block text-muted">{{ $vehicle->model }}</span>
                                    </td>
                                    <td><code>{{ $vehicle->plate }}</code></td>
                                    <td>
                                        <span
                                            class="badge badge-soft-{{ $vehicle->type == 'premium' ? 'warning' : ($vehicle->type == 'comfort' ? 'info' : 'secondary') }}">
                                            {{ ucfirst($vehicle->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $vehicle->color }}</td>
                                    <td>{{ $vehicle->year ?? '-' }}</td>
                                    <td>{{ $vehicle->seats }}</td>
                                    <td>
                                        @if($vehicle->driver)
                                            {{ $vehicle->driver->user->f_name ?? '' }}
                                        @else
                                            <span class="badge badge-soft-secondary">{{ 'No asignado' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $vehicle->status ? 'success' : 'danger' }}">
                                            {{ $vehicle->status ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                                data-toggle="dropdown">
                                                <i class="tio-more-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#editVehicleModal{{ $vehicle->id }}">
                                                    <i class="tio-edit"></i> {{ 'Editar' }}
                                                </a>
                                                <form action="{{ route('admin.taxi.vehicles.delete', $vehicle->id) }}"
                                                    method="POST" onsubmit="return confirm('{{ '¿Está seguro?' }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="tio-delete"></i> {{ 'Borrar' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">{{ 'No se encontraron vehículos' }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $vehicles->links() }}
            </div>
        </div>
    </div>

    <!-- Add Vehicle Modal -->
    <div class="modal fade" id="addVehicleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ 'Agregar nuevo vehículo' }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('admin.taxi.vehicles.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ 'Marca' }} *</label>
                                    <input type="text" name="brand" class="form-control" required
                                        placeholder="Toyota, Honda...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ 'Modelo' }} *</label>
                                    <input type="text" name="model" class="form-control" required
                                        placeholder="Corolla, Civic...">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ 'Lámina' }} *</label>
                                    <input type="text" name="plate" class="form-control" required placeholder="ABC-123">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ 'Tipo' }} *</label>
                                    <select name="type" class="form-control" required>
                                        <option value="economy">{{ 'Economía' }}</option>
                                        <option value="comfort">{{ 'Comodidad' }}</option>
                                        <option value="premium">{{ 'De primera calidad' }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ 'Color' }} *</label>
                                    <input type="text" name="color" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ 'Año' }}</label>
                                    <input type="number" name="year" class="form-control" min="2000"
                                        max="{{ date('Y') + 1 }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ 'Asientos' }}</label>
                                    <input type="number" name="seats" class="form-control" value="4" min="1" max="10">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ 'Imagen' }}</label>
                            <input type="file" name="image" class="form-control-file" accept="image/*">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="status" class="custom-control-input" id="vehicleStatus"
                                    checked>
                                <label class="custom-control-label" for="vehicleStatus">{{ 'Activo' }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ 'Cancelar' }}</button>
                        <button type="submit" class="btn btn-primary">{{ 'Agregar vehículo' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection