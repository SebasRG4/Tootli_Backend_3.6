@extends('layouts.admin.app')

@section('title','repartidores')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/delivery-man.png')}}" class="w--26" alt="">
                </span>
                <span>{{'Repartidor'}}</span>
            </h1>
        </div>
        <!-- End Page Header -->
        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2 border-0">
                <div class="search--button-wrapper justify-content-end">
                    <h5 class="card-title mr-auto">
                        {{'lista de repartidor'}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$deliveryMen->total()}}</span>
                    </h5>
                    <div class="min--200">
                        <select name="filter" class="form-control js-select2-custom set-filter" data-filter="filter"
                        data-url="{{ url()->full() }}">
                            <option  value="all">{{ 'Todos los tipos' }}</option>
                            <option {{  request()?->get('filter') == 'active' ? 'selected' : '' }}  value="active">{{ 'En línea' }}</option>
                            <option  {{  request()?->get('filter') == 'inactive' ? 'selected' : '' }} value="inactive">{{ 'Desconectado' }}</option>
                            <option {{  request()?->get('filter') == 'blocked' ? 'selected' : '' }}  value="blocked">{{ 'Suspendido' }}</option>
                        </select>
                    </div>
                    <div class="min--200">
                        <select name="job_type" class="form-control js-select2-custom set-filter" data-filter="job_type"
                        data-url="{{ url()->full() }}">
                            <option  value="all">{{ 'Todos los tipos de trabajo' }}</option>
                            <option  {{ request()?->get('job_type') == 'freelancer' ? 'selected' : '' }} value="freelancer">{{ 'Persona de libre dedicación' }}</option>
                            <option {{  request()?->get('job_type') == 'salary_base' ? 'selected' : '' }}  value="salary_base">{{ 'Base salarial' }}</option>
                        </select>
                    </div>
                    @if(!isset(auth('admin')->user()->zone_id))
                    <div class="min--200">
                        <select name="zone_id" class="form-control js-select2-custom set-filter" data-filter="zone_id"
                        data-url="{{ url()->full() }}">
                            <option value="all">{{ 'Todas las Zonas' }}</option>
                            @foreach(\App\Models\Zone::orderBy('name')->get() as $z)
                                <option
                                    value="{{$z['id']}}" {{isset($zone) && $zone->id == $z['id']?'selected':''}}>
                                    {{$z['name']}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <form class="search-form">
                        <div class="input-group input--group">
                            <input id="datatableSearch_" type="search" name="search" class="form-control h--45px"
                            placeholder="{{'ej: DM nombre correo electrónico o teléfono'}}" value="{{ request()->get('search') }}" aria-label="Search" required>
                            <button type="submit" class="btn btn--secondary h--45px"><i class="tio-search"></i></button>

                        </div>
                        <!-- End Search -->
                    </form>
                    @if(request()->get('search'))
                    <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{'reiniciar'}}</button>
                    @endif

                    <!-- Unfold -->
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle h--45px min-height-40" href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                            <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.users.delivery-man.export', ['type'=>'excel',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ 'sobresalir' }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.users.delivery-man.export', ['type'=>'csv',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ 'csv' }}
                            </a>
                        </div>
                    </div>
                    <!-- End Unfold -->
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
                    <thead class="thead-light">
                    <tr>
                        <th class="border-0 text-capitalize">{{'SL'}}</th>
                        <th class="border-0 text-capitalize">{{'nombre'}}</th>
                        <th class="border-0 text-capitalize">{{'información de contacto'}}</th>
                        <th class="border-0 text-capitalize">{{'zona'}}</th>
                        <th class="border-0 text-capitalize">{{'servicios'}}</th>
                        <th class="border-0 text-capitalize">{{'Total de pedidos completados'}}</th>
                        <th class="border-0 text-capitalize">{{'estado de disponibilidad'}}</th>
                        <th class="border-0 text-capitalize">{{'Estado'}}</th>
                        <th class="border-0 text-center text-capitalize">{{'acción'}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($deliveryMen as $key=>$dm)
                        <tr>
                            <td>{{$key+$deliveryMen->firstItem()}}</td>
                            <td>
                                <a class="table-rest-info max-w-400px min-w-220" href="{{route('admin.users.delivery-man.preview',[$dm['id']])}}">
                                    <img class="onerror-image" data-onerror-image="{{asset('assets/admin/img/160x160/img1.jpg')}}"
                                    src="{{$dm['image_full_url'] }}"
                                    alt="{{$dm['f_name']}} {{$dm['l_name']}}">
                                    <div class="info">
                                        <h5 class="text-hover-primary line--limit-2 text-wrap mb-0">{{$dm['f_name'].' '.$dm['l_name']}}</h5>
                                        <span class="d-block text-body">
                                            <span class="rating">
                                            <i class="tio-star"></i> {{count($dm->rating)>0?number_format($dm->rating[0]->average, 1, '.', ' '):0}}
                                            </span>
                                        </span>
                                    </div>
                                </a>
                            </td>
                            <td>
                                <a class="deco-none" href="tel:{{$dm['phone']}}">{{$dm['phone']}}</a>
                            </td>
                            <td>
                                @if($dm->zone)
                                <label class="text--title font-medium mb-0">{{$dm->zone->name}}</label>
                                @else
                                <label class="text--title font-medium mb-0">{{'zona eliminada'}}</label>
                                @endif
                            </td>
                            <td>
                                @if($dm->can_deliver)
                                <span class="badge badge-soft-info mb-1">{{'entrega'}}</span>
                                @endif
                                @if($dm->can_drive_taxi)
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge badge-soft-warning">{{'Taxi'}}</span>
                                    @if($dm->taxi_is_verified)
                                        <i class="tio-checkmark-circle text-success" title="{{'verificado'}}"></i>
                                    @else
                                        <i class="tio-warning text-warning" title="{{'inconfirmado'}}"></i>
                                    @endif
                                </div>
                                @endif
                            </td>
                            <td>
                                <a class="deco-none" href="{{route('admin.users.delivery-man.preview',['id'=> $dm['id'],'tab' => 'transaction' ])}}">{{count($dm['order_transaction'])}}</a>
                            </td>
                            <td>
                                <div>
                                    {{'órdenes actualmente asignadas'}} : {{$dm->current_orders}}
                                </div>
                                <div>
                                    {{'estado activo'}} :
                                    @if($dm->application_status == 'approved')
                                        @if($dm->active)
                                        <strong class="text-capitalize text-primary">{{'en línea'}}</strong>
                                        @else
                                        <strong class="text-capitalize text-secondary">{{'desconectado'}}</strong>
                                        @endif
                                    @elseif ($dm->application_status == 'denied')
                                        <strong class="text-capitalize text-danger">{{'denegado'}}</strong>
                                    @else
                                        <strong class="text-capitalize text-info">{{'Pendiente'}}</strong>
                                    @endif
                                </div>
                            </td>

                            <td>
                                @if ($dm->status == 1)
                                <strong class="text-capitalize text-primary">{{'Activo'}}</strong>
                                @else
                                <strong class="text-capitalize text-danger">{{'Suspendido'}}</strong>

                                @endif

                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn action-btn btn--warning btn-outline-warning"
                                            href="{{route('admin.users.delivery-man.preview',[$dm['id']])}}"
                                            title="{{ 'vista' }}"><i
                                                class="tio-visible-outlined"></i>
                                        </a>
                                    <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.users.delivery-man.edit',[$dm['id']])}}" title="{{'editar'}}"><i class="tio-edit"></i>
                                        </a>
                                        <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="delivery-man-{{$dm['id']}}" data-message="{{ '¿Quieres eliminar a este repartidor?' }}" title="{{'borrar'}}"><i class="tio-delete-outlined"></i>
                                    </a>
                                    <form action="{{route('admin.users.delivery-man.delete',[$dm['id']])}}" method="post" id="delivery-man-{{$dm['id']}}">
                                        @csrf @method('delete')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
                @if(count($deliveryMen) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    {!! $deliveryMen->links() !!}
                </div>
                @if(count($deliveryMen) === 0)
                <div class="empty--data">
                    <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                    <h5>
                        {{'no se encontraron datos'}}
                    </h5>
                </div>
                @endif
            <!-- End Table -->
        </div>
        <!-- End Card -->
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

        $('#search-form').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.users.delivery-man.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#set-rows').html(data.view);
                    $('#itemCount').html(data.count);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush
