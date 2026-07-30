@extends('layouts.admin.app')

@section('title', 'Lista de revisión')

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{ 'Lista de revisión' }} <span
                            class="badge badge-soft-dark ml-2">{{ $reviews->total() }}</span></h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{ 'Lista de revisión' }} </h5>
                    <form action="{{ url()->current() }}">
                        <div class="input-group input--group">
                            <input id="datatableSearch_" type="search" name="search" class="form-control"
                                placeholder="{{ 'Buscar por nombre del revisor o comentario' }}"
                                aria-label="{{ 'buscar' }}" value="{{ $search }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">{{ 'buscar' }}</button>
                            </div>
                        </div>
                    </form>

                    <!-- Filter by Store -->
                    <form action="{{ url()->current() }}" method="GET" class="ml-2">
                        <select name="store_id" class="form-control js-select2-custom" onchange="this.form.submit()">
                            <option value="">{{ 'Todos los restaurantes' }}</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ $store_id == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table id="columnSearchDatatable"
                    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                    data-hs-datatables-options='{
                            "order": [],
                            "orderCellsTop": true,
                            "paging": false
                        }'>
                    <thead class="thead-light">
                        <tr>
                            <th>{{ 'SL' }}</th>
                            <th>{{ 'Producto' }}</th>
                            <th>{{ 'crítico' }}</th>
                            <th>{{ 'revisar' }}</th>
                            <th>{{ 'clasificación' }}</th>
                            <th>{{ 'estado' }}</th>
                            <th>{{ 'acción' }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($reviews as $key => $review)
                            <tr>
                                <td>{{ $reviews->firstItem() + $key }}</td>
                                <td>
                                    @if ($review->item)
                                        <a href="{{ route('admin.item.view', [$review->item['id']]) }}">
                                            {{ Str::limit($review->item['name'], 20, '...') }}
                                        </a>
                                    @else
                                        <label class="badge badge-soft-danger">{{ 'Artículo eliminado' }}</label>
                                    @endif
                                </td>
                                <td>
                                    @if ($review->customer)
                                        <a href="{{ route('admin.customer.view', [$review->user_id]) }}">
                                            {{ $review->customer['f_name'] . ' ' . $review->customer['l_name'] }}
                                        </a>
                                    @else
                                        <label class="badge badge-soft-danger">{{ 'Cliente eliminado' }}</label>
                                    @endif
                                </td>
                                <td>
                                    <p class="text-wrap" style="max-width: 300px;">
                                        {{ Str::limit($review->comment, 80, '...') }}
                                    </p>
                                </td>
                                <td>
                                    <label class="badge badge-soft-info">
                                        {{ $review->rating }} <i class="tio-star"></i>
                                    </label>
                                </td>
                                <td>
                                    @if ($review->status == 1)
                                        <label class="badge badge-soft-success">{{ 'Activo' }}</label>
                                    @else
                                        <label class="badge badge-soft-danger">{{ 'Obstruido' }}</label>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                            href="{{ route('admin.sabores.reviews.edit', [$review['id']]) }}">
                                            <i class="tio-edit"></i>
                                        </a>
                                        <a class="btn btn-sm btn--danger btn-outline-danger action-btn" href="javascript:"
                                            onclick="form_alert('review-{{ $review['id'] }}','{{ '¿Quieres eliminar esta reseña?' }}')">
                                            <i class="tio-delete-outlined"></i>
                                        </a>
                                        <form action="{{ route('admin.sabores.reviews.delete', ['id' => $review['id']]) }}"
                                            method="post" id="review-{{ $review['id'] }}">
                                            @csrf @method('delete')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- End Table -->

            <!-- Footer -->
            <div class="card-footer">
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-sm-auto">
                        <div class="d-flex justify-content-center justify-content-sm-end">
                            <!-- Pagination -->
                            {!! $reviews->links() !!}
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Footer -->
        </div>
        <!-- End Card -->
    </div>
@endsection