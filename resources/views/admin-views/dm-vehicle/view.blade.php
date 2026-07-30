@extends('layouts.admin.app')

@section('title','vista del vehículo')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-12">
                    <h1 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{ asset('assets/admin/img/delivery-man.png') }}" alt="public">
                        </div>
                        <span>
                            {{ 'tipo de vehículo' }}: {{$vehicle->type}}
                        </span>
                    </h1>
                </div>
            </div>
        </div>


        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header py-2">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">{{ 'Repartidor' }}<span
                                    class="badge badge-soft-dark ml-2" id="itemCount">{{ $deliveryMen->total() }}</span>
                            </h5>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom fz--14px">
                        <table id="columnSearchDatatable"
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                            data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true,
                                 "paging":false
                               }'>
                            <thead class="thead-light">
                                <th class="text-capitalize">{{ 'SL' }}</th>
                                <th class="text-capitalize w-20p">{{'nombre'}}</th>
                                <th class="text-capitalize">{{ 'contacto' }}</th>
                                {{-- <th class="text-capitalize">{{'zona'}}</th> --}}
                                <th class="text-capitalize text-center">{{ 'Órdenes totales' }}</th>
                                <th class="text-capitalize">{{'estado de disponibilidad'}}</th>
                                <th class="text-capitalize text-center w-110px">{{'acción'}}</th>
                            </thead>

                            <tbody id="set-rows">
                                @foreach($deliveryMen as $key=>$dm)
                                    <tr>
                                        <td>{{$key+$deliveryMen->firstItem()}}</td>
                                        <td>
                                            <a class="table-rest-info" href="{{route('admin.users.delivery-man.preview',[$dm['id']])}}">
                                                <img class="onerror-image" data-onerror-image="{{asset('assets/admin/img/160x160/img1.jpg')}}"
                                                src="{{ $dm['image_full_url'] }}"
                                                alt="{{$dm['f_name']}} {{$dm['l_name']}}">
                                                <div class="info">
                                                    <h5 class="text-hover-primary mb-0">{{$dm['f_name'].' '.$dm['l_name']}}</h5>
                                                    <span class="d-block text-body">
                                                        <!-- Rating -->
                                                        <span class="rating">
                                                            <i class="tio-star"></i> {{count($dm->rating)>0?number_format($dm->rating[0]->average, 1, '.', ' '):0}}
                                                        </span>
                                                        <!-- Rating -->
                                                    </span>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <a class="deco-none" href="tel:{{$dm['phone']}}">{{$dm['phone']}}</a>
                                        </td>

                                        <!-- Static Data -->
                                        <td class="text-center">
                                            <div class="pr-3">
                                                {{ $dm->orders ? count($dm->orders):0 }}
                                            </div>
                                        </td>
                                        <!-- Static Data -->
                                        <td>
                                            <div>
                                                <!-- Status -->
                                                {{ 'Órdenes asignadas actualmente' }} : {{$dm->current_orders}}
                                                <!-- Status -->
                                            </div>
                                            @if($dm->application_status == 'approved')
                                                @if($dm->active)
                                                <div>
                                                    {{ 'Estado activo' }} : <strong class="text-primary text-capitalize">{{'en línea'}}</strong>
                                                </div>
                                                @else
                                                <div>
                                                    {{ 'Estado activo' }} : <strong class="text-secondary text-capitalize">{{'desconectado'}}</strong>
                                                </div>
                                                @endif
                                            @elseif ($dm->application_status == 'denied')
                                                <div>
                                                    {{ 'Estado activo' }} : <strong class="text-danger text-capitalize">{{'denegado'}}</strong>
                                                </div>
                                            @else
                                                <div>
                                                    {{ 'Estado activo' }} : <strong class="text-info text-capitalize">{{'no aprobado'}}</strong>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn btn-sm btn--primary btn-outline-primary action-btn" href="{{route('admin.users.delivery-man.edit',[$dm['id']])}}" title="{{'editar'}}"><i class="tio-edit"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if (count($deliveryMen) === 0)
                            <div class="empty--data">
                                <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                                <h5>
                                    {{ 'no se encontraron datos' }}
                                </h5>
                            </div>
                        @endif
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    {!! $deliveryMen->appends(request()->all())->links() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>







@endsection

@push('script_2')
    <script>
        "use strict";

        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('keyup', function () {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function () {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>
@endpush
