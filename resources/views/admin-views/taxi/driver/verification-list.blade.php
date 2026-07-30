@extends('layouts.admin.app')

@section('title', 'Verificación del conductor')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-filter-list-outlined"></i>
                        {{ 'Verificación del conductor' }} <span
                            class="badge badge-soft-dark ml-2">{{ $drivers->total() }}</span></h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">{{ 'Lista de conductores' }}</h5>
                            <form action="{{ url()->current() }}" method="GET">
                                <div class="input-group input-group-merge input-group-flush">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="tio-search"></i>
                                        </div>
                                    </div>
                                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                                        placeholder="{{ 'Buscar por nombre, correo electrónico o teléfono' }}" aria-label="Search"
                                        value="{{ $search }}" required>
                                    <button type="submit" class="btn btn-primary">{{ 'buscar' }}</button>
                                </div>
                            </form>
                            <!-- Unfold -->
                            <div class="hs-unfold">
                                <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle" href="javascript:;"
                                    data-hs-unfold-options='{
                                            "target": "#usersExportDropdown",
                                            "type": "css-animation"
                                            }'>
                                    <i class="tio-filter-list"></i> {{ 'Filtrar por estado' }}
                                </a>

                                <div id="usersExportDropdown"
                                    class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                    <a class="dropdown-item" href="{{ route('admin.taxi.drivers.verification.index') }}">
                                        {{ 'Todo' }}
                                    </a>
                                    <a class="dropdown-item"
                                        href="{{ route('admin.taxi.drivers.verification.index', ['status' => 'pending']) }}">
                                        {{ 'Pendiente de verificación' }}
                                    </a>
                                    <a class="dropdown-item"
                                        href="{{ route('admin.taxi.drivers.verification.index', ['status' => 'verified']) }}">
                                        {{ 'Verificado' }}
                                    </a>
                                </div>
                            </div>
                            <!-- End Unfold -->
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ '#' }}</th>
                                    <th>{{ 'Información del conductor' }}</th>
                                    <th>{{ 'Contacto' }}</th>
                                    <th>{{ 'Estado' }}</th>
                                    <th>{{ 'Comportamiento' }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($drivers as $key => $dm)
                                    <tr>
                                        <td>{{ $drivers->firstItem() + $key }}</td>
                                        <td>
                                            <a href="{{ route('admin.taxi.drivers.verification.show', $dm['id']) }}"
                                                class="media align-items-center">
                                                <img class="avatar avatar-lg mr-3 onerror-image"
                                                    src="{{ \App\CentralLogics\Helpers::get_full_url('delivery-man', $dm['image'] ?? '', $dm->storage->first()?->value ?? 'public', 'profile') }}"
                                                    data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                                                    alt="{{ $dm['f_name'] }} {{ $dm['l_name'] }}">
                                                <div class="media-body">
                                                    <h5 class="text-hover-primary mb-0">{{ $dm['f_name'] }} {{ $dm['l_name'] }}
                                                    </h5>
                                                    <span class="text-body font-size-sm">{{ $dm['email'] }}</span>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <a class="d-block text-body" href="tel:{{ $dm['phone'] }}">{{ $dm['phone'] }}</a>
                                        </td>
                                        <td>
                                            @if ($dm->taxi_is_verified)
                                                <span class="badge badge-soft-success">{{ 'Verificado' }}</span>
                                            @else
                                                <span class="badge badge-soft-warning">{{ 'Pendiente' }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-white"
                                                href="{{ route('admin.taxi.drivers.verification.show', $dm['id']) }}"
                                                title="{{ 'Ver detalles' }}">
                                                <i class="tio-visible"></i> {{ 'Detalles' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- End Table -->

                    <!-- Footer -->
                    <div class="card-footer">
                        {!! $drivers->links() !!}
                    </div>
                    <!-- End Footer -->
                </div>
            </div>
        </div>
    </div>
@endsection