@extends('layouts.admin.app')

@section('title', 'informe de gastos')

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/report.png') }}" class="w--22" alt="">
                </span>
                <span>
                    {{ 'informe de gastos' }}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="light-card mb-3 d-flex gap-3 rounded align-items-center p-3 fs-12">
            <img width="18" src="{{ asset('assets/admin/img/icons/intel.png') }}" alt="">
            {{ 'Este informe mostrará todos los pedidos en los que se ha utilizado el descuento de administrador. Los descuentos para administradores son: entrega gratuita, descuento en tienda, descuento en cupón y descuentos en artículos (parciales según la comisión del pedido).' }}
        </div>

        <div class="card mb-20">
            <div class="card-body">
                <h4 class="mb-3">{{ 'Filtrar datos' }}</h4>
                <form action="{{ route('admin.transactions.report.set-date') }}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <select name="module_id" class="form-control js-select2-custom set-filter" data-url="{{ url()->full() }}" data-filter="module_id"
                                title="{{ 'seleccionar módulos' }}">
                                <option value="" {{ !request('module_id') ? 'selected' : '' }}>
                                    {{ 'todos los módulos' }}</option>
                                @foreach (\App\Models\Module::notParcel()->get() as $module)
                                    <option value="{{ $module->id }}"
                                        {{ request('module_id') == $module->id ? 'selected' : '' }}>
                                        {{ $module['module_name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <select name="zone_id" class="form-control js-select2-custom set-filter" data-url="{{ url()->full() }}" data-filter="zone_id">
                                <option value="all">{{ 'Todas las Zonas' }}</option>
                                @foreach (\App\Models\Zone::orderBy('name')->get() as $z)
                                    <option value="{{ $z['id'] }}"
                                        {{ isset($zone) && $zone->id == $z['id'] ? 'selected' : '' }}>
                                        {{ $z['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <select name="store_id"
                                data-placeholder="{{ 'seleccionar proveedor' }}"
                                class="js-data-example-ajax form-control set-filter" data-url="{{ url()->full() }}" data-filter="store_id">
                                @if (isset($store))
                                    <option value="{{ $store->id }}" selected>{{ $store->name }}</option>
                                @else
                                    <option value="all" selected>{{ 'todos los proveedores' }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <select name="customer_id"
                                data-placeholder="{{ 'seleccionar cliente' }}"
                                class="js-data-example-ajax-2 form-control set-filter" data-url="{{ url()->full() }}" data-filter="customer_id">
                                @if (isset($customer))
                                    <option value="{{ $customer->id }}" selected>{{ $customer->f_name . ' ' .$customer->l_name }}</option>
                                @else
                                    <option value="all" selected>{{ 'todos los clientes' }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <select class="form-control js-select2-custom set-filter" data-url="{{ url()->full() }}" data-filter="type" name="type">
                                <option value="all" {{ isset($type) && $type == 'all' ? 'selected' : '' }}>
                                    {{ 'Todo tipo' }}</option>
                                <option value="add_fund_bonus" {{ isset($type) && $type == 'add_fund_bonus' ? 'selected' : '' }}>
                                    {{ 'agregar bono de fondo' }}</option>
                                <option value="free_delivery" {{ isset($type) && $type == 'free_delivery' ? 'selected' : '' }}>
                                    {{ 'entrega gratuita' }}</option>
                                <option value="coupon_discount" {{ isset($type) && $type == 'coupon_discount' ? 'selected' : '' }}>
                                    {{ 'cupón de descuento' }}</option>
                                <option value="discount_on_product" {{ isset($type) && $type == 'discount_on_product' ? 'selected' : '' }}>
                                    {{ 'descuento en el producto' }}</option>
                                <option value="flash_sale_discount" {{ isset($type) && $type == 'flash_sale_discount' ? 'selected' : '' }}>
                                    {{ 'descuento de venta flash' }}</option>
                                <option value="CashBack" {{ isset($type) && $type == 'CashBack' ? 'selected' : '' }}>
                                    {{ 'Reembolso' }}</option>
                                <option value="referral_discount" {{ isset($type) && $type == 'referral_discount' ? 'selected' : '' }}>
                                    {{ 'Descuento por recomendación' }}</option>
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <select class="form-control js-select2-custom set-filter" data-url="{{ url()->full() }}" data-filter="filter" name="filter">
                                <option value="all_time" {{ isset($filter) && $filter == 'all_time' ? 'selected' : '' }}>
                                    {{ 'Todo el tiempo' }}</option>
                                <option value="this_year" {{ isset($filter) && $filter == 'this_year' ? 'selected' : '' }}>
                                    {{ 'este año' }}</option>
                                <option value="previous_year"
                                    {{ isset($filter) && $filter == 'previous_year' ? 'selected' : '' }}>
                                    {{ 'Año anterior' }}</option>
                                <option value="this_month"
                                    {{ isset($filter) && $filter == 'this_month' ? 'selected' : '' }}>
                                    {{ 'este mes' }}</option>
                                <option value="this_week" {{ isset($filter) && $filter == 'this_week' ? 'selected' : '' }}>
                                    {{ 'Esta semana' }}</option>
                                <option value="custom" {{ isset($filter) && $filter == 'custom' ? 'selected' : '' }}>
                                    {{ 'Costumbre' }}</option>
                            </select>
                        </div>
                        @if (isset($filter) && $filter == 'custom')
                            <div class="col-sm-6 col-md-3">

                                <input type="date" name="from" id="from_date" class="form-control"
                                    placeholder="{{ 'Fecha de inicio' }}"
                                    {{ session()->has('from_date') ? 'value=' . session('from_date') : '' }} required>

                            </div>
                            <div class="col-sm-6 col-md-3">

                                <input type="date" name="to" id="to_date" class="form-control"
                                    placeholder="{{ 'Fecha de finalización' }}"
                                    {{ session()->has('to_date') ? 'value=' . session('to_date') : '' }} required>

                            </div>
                        @endif
                        <div class="col-sm-6 col-md-3 ml-auto">
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn--primary h--45px min-w-100px">{{ 'Filtrar' }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @php
            $from = session('from_date') . ' 00:00:00';
            $to = session('to_date') . ' 23:59:59';
        @endphp

        <!-- End Stats -->
        <!-- Card -->
        <div class="card mt-3">
            <!-- Header -->
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper">
                    <h3 class="card-title d-flex align-items-center gap-2">
                        {{ 'listas de gastos' }}
                        <span class="badge badge-soft-secondary" id="countItems">{{ $expense->total() }}</span>
                    </h3>
                    <form class="search-form theme-style">
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input name="search" type="search" value="{{ request()?->search ?? null}}" class="form-control" placeholder="{{ 'Buscar por ID de pedido' }}">
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>

                    @if(request()->get('search'))
                        <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{'reiniciar'}}</button>
                    @endif
                    <!-- Static Export Button -->
                    <div class="hs-unfold ml-3">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn font--sm"
                            href="javascript:;"
                            data-hs-unfold-options="{
                                &quot;target&quot;: &quot;#usersExportDropdown&quot;,
                                &quot;type&quot;: &quot;css-animation&quot;
                            }"
                            data-hs-unfold-target="#usersExportDropdown" data-hs-unfold-invoker="">
                            <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right hs-unfold-content-initialized hs-unfold-css-animation animated hs-unfold-reverse-y hs-unfold-hidden">

                            <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                            <a id="export-excel" class="dropdown-item" href="{{route('admin.transactions.report.expense-export', ['export_type'=>'excel',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ 'sobresalir' }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('admin.transactions.report.expense-export', ['export_type'=>'csv',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ 'csv' }}
                            </a>
                        </div>
                    </div>
                    <!-- Static Export Button -->
                </div>
            </div>
            <!-- End Header -->

            <!-- Body -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless middle-align __txt-14px">
                        <thead class="thead-light white--space-false">
                            <tr>
                                <th class="border-0">{{'SL'}}</th>
                                <th class="border-0">{{'identificación del pedido'}}</th>
                                @if (addon_published_status('Rental'))
                                <th class="border-0">{{'identificación del viaje'}}</th>
                                @endif
                                <th class="border-0">{{'Fecha y hora'}}</th>
                                <th class="border-0">{{ 'Tipo de gasto' }}</th>
                                <th class="text-center" >{{ 'Nombre del cliente' }}</th>
                                <th class="border-0 text-right pr-xl-5">
                                    <div class="pr-xl-5">
                                        {{'monto del gasto'}}
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach ($expense as $key => $exp)
                            <tr>
                                <td scope="row">{{$key+$expense->firstItem()}}</td>
                                <td>
                                    @if ($exp->order)

                                    <div>
                                        <a class="text-dark" href="{{ route('admin.order.details', ['id' => $exp->order->id,'module_id'=>$exp->order->module_id]) }}">{{ $exp['order_id'] }}</a>
                                    </div>
                                    @else
                                    <label class="badge badge-primary">{{'Otros gastos'}}</label>
                                    @endif
                                </td>
                                @if (addon_published_status('Rental'))
                                <td>
                                    @if ($exp->trip)

                                    <div>
                                        <a class="text-dark" href="{{ route('admin.rental.trip.details', $exp->trip->id) }}">{{ $exp['trip_id'] }}</a>
                                    </div>
                                    @else
                                    <label class="badge badge-primary">{{'Otros gastos'}}</label>
                                    @endif
                                </td>
                                @endif
                                <td>
                                    {{date('Y-m-d '.config('timeformat'),strtotime($exp->created_at))}}
                                </td>
                                <td><label>{{ucwords('{$exp[\'tipo\']}')}}</label></td>
                                <td class="text-center">
                                    @if ($exp->order)

                                    @if($exp->order?->is_guest)
                                    @php($customer_details = json_decode($exp->order['delivery_address'],true))
                                    <strong>{{$customer_details['contact_person_name']}}</strong>

                                    @elseif($exp->order?->customer)

                                    {{$exp->order?->customer['f_name'].' '.$exp->order?->customer['l_name']}}
                                    @else
                                        <label
                                            class="badge badge-danger">{{'datos de cliente no válidos'}}</label>
                                    @endif

                                    @elseif($exp->trip)
                                    @if ($exp?->trip?->customer)

                                        {{ $exp?->trip?->customer?->fullName }}

                                        @elseif($exp?->trip?->user_info['contact_person_name'])
                                            <div class="font-medium">
                                                {{$exp?->trip?->user_info['contact_person_name'] }}
                                            </div>
                                        @else
                                            {{ 'Usuario invitado' }}
                                        @endif


                                    @elseif ($exp['type'] == 'add_fund_bonus')
                                    {{ $exp->user->f_name.' '.$exp->user->l_name }}
                                    @else
                                    <label class="badge badge-danger">{{'datos de cliente no válidos'}}</label>

                                    @endif
                                </td>
                                <td class="text-right pr-xl-5">
                                    <div class="pr-xl-5">
                                        {{\App\CentralLogics\Helpers::format_currency($exp['amount'])}}
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- End Table -->


                @if (count($expense) !== 0)
                    <hr>
                    <div class="page-area">
                        {!! $expense->withQueryString()->links() !!}
                    </div>
                @endif
                @if (count($expense) === 0)
                    <div class="empty--data">
                        <img src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                        <h5>
                            {{ 'no se encontraron datos' }}
                        </h5>
                    </div>
                @endif
            </div>
            <!-- End Body -->
        </div>
        <!-- End Card -->
    </div>
@endsection

@push('script')
@endpush

@push('script_2')
    <script src="{{ asset('assets/admin') }}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{ asset('assets/admin') }}/vendor/chartjs-chart-matrix/dist/chartjs-chart-matrix.min.js">
    </script>
    <script src="{{ asset('assets/admin') }}/js/hs.chartjs-matrix.js"></script>
    <script src="{{ asset('assets/admin') }}/js/view-pages/admin-reports.js"></script>
    <script>
        "use strict";
        $(document).on('ready', function() {
            $('.js-data-example-ajax').select2({
                ajax: {
                    url: '{{ url('/') }}/admin/store/get-stores',
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            // all:true,
                            @if (isset($zone))
                                zone_ids: [{{ $zone->id }}],
                            @endif
                            @if (request('module_id'))
                                module_id: {{ request('module_id') }},
                            @endif
                            page: params.page
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    __port: function(params, success, failure) {
                        let $request = $.ajax(params);

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    }
                }
            });

            $('.js-data-example-ajax-2').select2({
                ajax: {
                    url: '{{ url('/') }}/admin/customer/select-list',
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            // all:true,
                            @if (isset($zone))
                                zone_ids: [{{ $zone->id }}],
                            @endif
                            @if (request('module_id'))
                                module_id: {{ request('module_id') }},
                            @endif
                            @if (request('store_id'))
                                store_id: {{ request('store_id') }},
                            @endif
                            page: params.page
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    __port: function(params, success, failure) {
                        let $request = $.ajax(params);

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    }
                }
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
                url: '{{route('admin.transactions.report.expense-report-search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#set-rows').html(data.view);
                    $('#countItems').html(data.count);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush

