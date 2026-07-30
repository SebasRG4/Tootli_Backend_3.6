@extends('layouts.admin.app')

@section('title','nuevas solicitudes de unión')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title"><i class="tio-filter-list"></i> {{'nuevas solicitudes de unión'}}</h1>
            <div class="page-header-select-wrapper">

                @if(!isset(auth('admin')->user()->zone_id))
                <div class="select-item">
                    <select name="zone_id" class="form-control js-select2-custom set-filter" data-url="{{url()->full()}}" data-filter="zone_id">
                        <option value="" {{!request('zone_id')?'selected':''}}>{{ 'Todas las Zonas' }}</option>
                        @foreach(\App\Models\Zone::orderBy('name')->get() as $z)
                            <option
                                    value="{{$z['id']}}" {{isset($zone) && $zone->id == $z['id']?'selected':''}}>
                                {{$z['name']}}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
                        <!-- Nav -->
                        <ul class="nav nav-tabs mb-3 border-0 nav--tabs nav--pills">
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('admin.store.pending-requests') }}"   aria-disabled="true">{{'tiendas pendientes'}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.store.deny-requests') }}"  aria-disabled="true">{{'tiendas denegadas'}}</a>
                            </li>
                        </ul>
                        <!-- End Nav -->
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2">
                <div class="search--button-wrapper">
                    <h4 class="card-title text-title">{{'lista de tiendas'}} <span class="badge badge-soft-dark ml-2" id="itemCount">{{$stores->total()}}</span></h4>

                    <div class="d-flex align-items-center gap-3 flex-sm-nowrap flex-wrap">
                        <form action="javascript:" id="search-form" class="search-form w-100">
                            @csrf
                            <div class="input-group input--group">
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                        placeholder="{{'ej: buscar nombre de tienda'}}" value="{{isset($search_by) ? $search_by : ''}}" aria-label="{{'buscar'}}" required>
                                <button type="submit" class="btn btn--primary"><i class="tio-search"></i></button>
                            </div>
                        </form>
                        <div>
                            <div class="hs-unfold mr-2">
                                <a class="js-hs-unfold-invoker btn btn-sm btn-white d-inline-flex text-title font-medium dropdown-toggle min-height-40" href="javascript:;"
                                    data-hs-unfold-options='{
                                            "target": "#usersExportDropdown",
                                            "type": "css-animation"
                                        }'>
                                    <i class="tio-download-to mr-1 text-title"></i> {{ 'exportar' }}
                                </a>
                                <div id="usersExportDropdown"
                                    class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                    <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                                    <a id="export-excel" class="dropdown-item" href="{{route('admin.business-settings.module.export', ['type'=>'excel',request()->getQueryString()])}}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                            alt="Image Description">
                                        {{ 'sobresalir' }}
                                    </a>
                                    <a id="export-csv" class="dropdown-item" href="{{route('admin.business-settings.module.export', ['type'=>'csv',request()->getQueryString()])}}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ asset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                            alt="Image Description">
                                        .{{ 'csv' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
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
                            "paging":false

                        }'>
                    <thead class="bg-table-head">
                    <tr>
                        <th class="border-0">{{'SL'}}</th>
                        <th class="border-0">{{'almacenar información'}}</th>
                        <th class="border-0">{{'módulo'}}</th>
                        <th class="border-0">{{'información del propietario'}}</th>
                        <th class="border-0">{{'zona'}}</th>
                        <th class="text-uppercase border-0">{{'estado'}}</th>
                        <th class="border-0 text-center">{{'acción'}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($stores as $key=>$store)
                        <tr>
                            <td>{{$key+$stores->firstItem()}}</td>
                            <td>
                                <div>
                                    <a href="{{route('admin.store.view', $store->id)}}" class="table-rest-info" alt="view store">
                                        <img class="img--60 rounded broder onerror-image" data-onerror-image="{{asset('assets/admin/img/160x160/img1.jpg')}}"
                                        src="{{ $store['logo_full_url'] ?? asset('assets/admin/img/160x160/img1.jpg') }}" >
                                        <div class="info"><div class="text--title">
                                            {{Str::limit($store->name,20,'...')}}
                                            </div>
                                            <div class="font-light">
                                                {{'identificación'}}:{{$store->id}}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{Str::limit($store->module->module_name,20,'...')}}
                                </span>
                            </td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{Str::limit($store->vendor->f_name.' '.$store->vendor->l_name,20,'...')}}
                                </span>
                                <div>
                                    {{$store['phone']}}
                                </div>
                            </td>
                            <td>
                                {{$store->zone?$store->zone->name:'zona eliminada'}}
                            </td>

                            <td>
                                @if(isset($store->vendor->status))
                                    @if($store->vendor->status)
                                        <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$store->id}}">
                                            <input type="checkbox" data-url="{{route('admin.store.status',[$store->id,$store->status?0:1])}}" data-message="{{'quieres cambiar el estado de esta tienda'}}" class="toggle-switch-input status_change_alert" id="stocksCheckbox{{$store->id}}" {{$store->status?'checked':''}}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                    @else
                                    <span class="badge badge-soft-danger">{{'denegado'}}</span>
                                    @endif
                                @else
                                    <span class="badge badge-soft-danger">{{'Pendiente'}}</span>
                                @endif
                            </td>

                            <td>
                                <div class="btn--container justify-content-center">

                                    <a class="btn action-btn btn-outline-theme-dark"
                                    href="{{route('admin.store.edit',[$store['id'],'pending'=>1])}}" title="{{'editar tienda'}}"><i class="tio-edit"></i>
                                    </a>


                                    @if($store->vendor->status == 0)
                                        <a class="btn action-btn btn--primary btn-outline-primary float-right swal_fire_alert" data-toggle="tooltip" data-placement="top"
                                        data-original-title="{{ 'aprobar' }}"
                                       data-title="{{'está seguro ?'}}"
                                       data-image_url="{{ asset('assets/admin/img/off-danger.png') }}"
                                       data-confirm_button_text="{{ 'Sí' }}"
                                       data-cancel_button_text="{{ 'No' }}"
                                       data-message="{{'desea aprobar la solicitud de incorporación del proveedor.'}}"
                                        data-url="{{route('admin.store.application',[$store['id'],1])}}"
                                            href="javascript:"><i class="tio-done font-weight-bold"></i></a>
                                    @endif
                                    @if (!isset($store->vendor->status))
                                        <button class="btn action-btn btn--danger btn-outline-danger float-right"
                                        data-original-title="{{ 'Rechazar' }}" data-toggle="modal" data-target="#confirmation-reason-btn{{ $store->id }}"
                                        data-message="{{'quieres rechazar esta solicitud'}}"
                                            href="javascript:"><i class="tio-clear font-weight-bold"></i></button>
                                    @endif
                                </div>



    <!-- Confiramtion Reason Modal -->
    <div class="modal shedule-modal fade" id="confirmation-reason-btn{{ $store->id }}" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content pb-2 max-w-500">
                <form action="{{ route('admin.store.application', [$store['id'], 0]) }}" method="get">
                <div class="modal-header">
                    <button type="button"
                        class="close bg-modal-btn w-30px h-30 rounded-circle position-absolute right-0 top-0 m-2 z-2"
                        data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <img src="{{ asset('assets/admin/img/delete-confirmation.png') }}" alt="icon"
                            class="mb-3">
                        <h3 class="mb-2">{{ 'Está seguro ?' }}</h3>
                        <p class="mb-0">{{ '¿Quiere rechazar esta solicitud de incorporación?' }}</p>
                    </div>
                    <div class="px-3 mt-4">
                        <h5 class="mb-2">{{ 'Razón' }}</h5>
                        <textarea name="rejection_note" id="" class="form-control" rows="2" required
                            placeholder="{{ 'Escriba aquí el motivo del rechazo...' }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 gap-2">
                    <button type="button" class="btn min-w-120px btn--reset" data-dismiss="modal">{{ 'No' }}</button>
                    <button type="submit" class="btn min-w-120px btn--primary">{{ 'Sí' }}</button>
                </div>
            </form>
            </div>
        </div>
    </div>


                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
            <!-- End Table -->
                @if(count($stores) !== 0)
                <hr>
                @endif
                {{-- <div class="d-flex align-items-center justify-content-end gap-24 flex-wrap px-3 pb-3">
                    <div class="d-flex aign-items-center gap-4">
                        <p class="text-dark m-0 lh-1">1-5 of 13</p>
                        <div class="d-flex align-items-center gap-3">
                            <a class="text-dark fs-16 disabled" href=""><i class="tio-chevron-left"></i></a>
                            <a class="text-dark fs-16" href=""><i class="tio-chevron-right"></i></a>
                        </div>
                    </div>
                    <div class="page-area">
                        <p>Your Pagination hare</p>
                    </div>
                    <div class="page-area">
                        {!! $stores->withQueryString()->links() !!}
                    </div>
                </div> --}}
                <div class="page-area">
                    {!! $stores->withQueryString()->links() !!}
                </div>
                @if(count($stores) === 0)
                <div class="empty--data">
                    <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                    <h5>
                        {{'no se encontraron datos'}}
                    </h5>
                </div>
                @endif
        </div>
        <!-- End Card -->
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";
        $('.status_change_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            status_change_alert(url, message, event)
        })
        function status_change_alert(url, message, e) {
            e.preventDefault();
            Swal.fire({
                title: '{{ '¿Está seguro?' }}' ,
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{'No'}}',
                confirmButtonText: '{{'Sí'}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href=url;
                }
            })
        }
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
         $('.swal_fire_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            let title = $(this).data('title');
            let imageUrl = $(this).data('image_url');
            let cancelButtonText = $(this).data('cancel_button_text');
            let confirmButtonText = $(this).data('confirm_button_text');
            swalFire(url,title, message, imageUrl,cancelButtonText, confirmButtonText)
        })

        $('#search-form').on('submit', function () {
            let formData = new FormData(this);
            set_filter('{!! url()->full() !!}',formData.get('search'),'search_by')
        });
    </script>
@endpush
