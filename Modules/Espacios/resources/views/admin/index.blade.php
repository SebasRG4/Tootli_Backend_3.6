@extends('layouts.admin.app')

@section('title', 'Gestión de Espacios')

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title"><i class="tio-home-work"></i> Espacios <span class="badge badge-soft-dark ml-2">{{ $listings->total() }}</span></h1>
            </div>
            <div class="col-sm-auto">
                <a class="btn btn-primary" href="{{ route('admin.espacios.create') }}">
                    <i class="tio-add"></i> Agregar Nuevo Espacio
                </a>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <div class="row gx-2 gx-lg-3">
        <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
            <!-- Card -->
            <div class="card">
                <!-- Header -->
                <div class="card-header border-0 py-2">
                    <div class="search--button-wrapper justify-content-end">
                        <form action="{{ route('admin.espacios.index') }}" method="GET">
                            <div class="input-group input--group">
                                <input id="datatableSearch_" type="search" name="search"
                                       value="{{ $search }}"
                                       class="form-control"
                                       placeholder="Buscar por título o ciudad" aria-label="Buscar">
                                <button type="submit" class="btn btn--secondary">
                                    <i class="tio-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- End Header -->

                <!-- Table -->
                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable"
                           class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                           style="width: 100%">
                        <thead class="thead-light">
                        <tr>
                            <th class="border-0">#</th>
                            <th class="border-0">Imagen</th>
                            <th class="border-0">Nombre</th>
                            <th class="border-0">Vendedor / Tienda</th>
                            <th class="border-0">Ubicación</th>
                            <th class="border-0">Precio/Noche</th>
                            <th class="border-0 text-center">Estado</th>
                            <th class="border-0 text-center">Acciones</th>
                        </tr>
                        </thead>

                        <tbody id="set-rows">
                        @foreach($listings as $key=>$listing)
                            <tr>
                                <td>{{ $listings->firstItem() + $key }}</td>
                                <td>
                                    <img class="img--50" onerror="this.src='{{ asset('public/assets/admin/img/160x160/img1.jpg') }}'"
                                         src="{{ $listing->cover_image_url ?? asset('public/assets/admin/img/160x160/img1.jpg') }}" alt="cover">
                                </td>
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{ Str::limit($listing->title, 20, '...') }}
                                    </span>
                                </td>
                                <td>
                                    @if($listing->store)
                                        <a href="{{ route('admin.store.view', [$listing->store_id]) }}" class="text-body font-weight-bold">
                                            {{ $listing->store->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Sin Vendedor</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{ $listing->city }}
                                    </span>
                                </td>
                                <td>
                                    ${{ number_format($listing->price_per_night, 2) }}
                                </td>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{ $listing->id }}">
                                        <input type="checkbox" onclick="location.href='{{route('admin.espacios.status', ['id'=>$listing->id, 'status'=>$listing->status == 'active' ? 'inactive' : 'active'])}}'" class="toggle-switch-input" id="stocksCheckbox{{ $listing->id }}" {{ $listing->status == 'active' ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                           href="{{ route('admin.espacios.edit', [$listing->id]) }}" title="Editar">
                                            <i class="tio-edit"></i>
                                        </a>
                                        <a class="btn btn-sm btn--danger btn-outline-danger action-btn" href="javascript:"
                                           onclick="form_alert('espacio-{{ $listing->id }}','¿Estás seguro de eliminar este espacio?')" title="Eliminar">
                                            <i class="tio-delete-outlined"></i>
                                        </a>
                                        <form action="{{ route('admin.espacios.delete', [$listing->id]) }}"
                                              method="post" id="espacio-{{ $listing->id }}">
                                            @csrf @method('delete')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if(count($listings) === 0)
                        <div class="empty--data">
                            <img src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                            <h5>No se encontraron datos</h5>
                        </div>
                    @endif
                </div>
                <!-- End Table -->

                <div class="card-footer">
                    <div class="page-area px-4 pb-3">
                        <div class="d-flex align-items-center justify-content-end">
                            <div>
                                {!! $listings->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Card -->
        </div>
    </div>
</div>
@endsection
