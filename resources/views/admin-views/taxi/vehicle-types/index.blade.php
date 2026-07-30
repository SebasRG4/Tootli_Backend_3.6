@extends('layouts.admin.app')

@section('title', 'Tipos de vehículos')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <i class="tio-car"></i> {{ 'Tipos de vehículos' }}
                    </h1>
                </div>
                <div class="col-sm-auto">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addTypeModal">
                        <i class="tio-add"></i> {{ 'Agregar tipo de vehículo' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Vehicle Types Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ 'Imagen' }}</th>
                                <th>{{ 'Babosa' }}</th>
                                <th>{{ 'Nombre' }}</th>
                                <th>{{ 'Pasajeros máximos' }}</th>
                                <th>{{ 'Orden' }}</th>
                                <th>{{ 'Estado' }}</th>
                                <th>{{ 'Comportamiento' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($types as $type)
                                <tr>
                                    <td>
                                        @if($type->image)
                                            <img src="{{ asset('storage/taxi_vehicle_type/'.$type->image) }}" 
                                                 alt="{{ $type->name }}" 
                                                 style="max-width: 50px; max-height: 40px; object-fit: contain;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $type->slug }}</code></td>
                                    <td>{{ $type->name }}</td>
                                    <td>{{ $type->max_passengers }}</td>
                                    <td>{{ $type->sort_order }}</td>
                                    <td>
                                        <span class="badge badge-{{ $type->status ? 'success' : 'danger' }}">
                                            {{ $type->status ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                <i class="tio-more-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editTypeModal{{ $type->id }}">
                                                    <i class="tio-edit"></i> {{ 'Editar' }}
                                                </a>
                                                <form action="{{ route('admin.taxi.vehicle-types.delete', $type->id) }}" method="POST" onsubmit="return confirm('{{ '¿Está seguro?' }}')">
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

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editTypeModal{{ $type->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ 'Editar tipo de vehículo' }}</h5>
                                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                            </div>
                                            <form action="{{ route('admin.taxi.vehicle-types.update', $type->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ 'Babosa' }} *</label>
                                                                <input type="text" name="slug" class="form-control" value="{{ $type->slug }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ 'Nombre' }} *</label>
                                                                <input type="text" name="name" class="form-control" value="{{ $type->name }}" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ 'Pasajeros máximos' }} *</label>
                                                                <input type="number" name="max_passengers" class="form-control" value="{{ $type->max_passengers }}" min="1" max="10" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ 'Orden de clasificación' }}</label>
                                                                <input type="number" name="sort_order" class="form-control" value="{{ $type->sort_order }}" min="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>{{ 'Descripción' }}</label>
                                                        <textarea name="description" class="form-control" rows="2">{{ $type->description }}</textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>{{ 'Imagen' }}</label>
                                                        @if($type->image)
                                                            <div class="mb-2">
                                                                <img src="{{ asset('storage/taxi_vehicle_type/'.$type->image) }}" style="max-width: 100px;">
                                                            </div>
                                                        @endif
                                                        <input type="file" name="image" class="form-control" accept="image/*">
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" name="status" class="custom-control-input" id="typeStatus{{ $type->id }}" {{ $type->status ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="typeStatus{{ $type->id }}">{{ 'Activo' }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ 'Cancelar' }}</button>
                                                    <button type="submit" class="btn btn-primary">{{ 'Actualizar' }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">{{ 'No se encontraron tipos de vehículos' }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $types->links() }}
            </div>
        </div>
    </div>

    <!-- Add Type Modal -->
    <div class="modal fade" id="addTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ 'Agregar tipo de vehículo' }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('admin.taxi.vehicle-types.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ 'Babosa' }} *</label>
                                    <input type="text" name="slug" class="form-control" placeholder="e.g. suv, motorbike" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ 'Nombre' }} *</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. SUV, Moto" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ 'Pasajeros máximos' }} *</label>
                                    <input type="number" name="max_passengers" class="form-control" value="4" min="1" max="10" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ 'Orden de clasificación' }}</label>
                                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ 'Descripción' }}</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>{{ 'Imagen' }}</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="status" class="custom-control-input" id="newTypeStatus" checked>
                                <label class="custom-control-label" for="newTypeStatus">{{ 'Activo' }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ 'Cancelar' }}</button>
                        <button type="submit" class="btn btn-primary">{{ 'Agregar tipo' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
