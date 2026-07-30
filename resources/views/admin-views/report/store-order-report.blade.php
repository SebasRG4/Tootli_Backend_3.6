@extends('layouts.admin.app')

@section('title', 'Informe de la tienda')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')

    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header report-page-header">
            <div class="d-flex">
                <img src="{{ asset('assets/admin/img/store-report.svg') }}" class="page-header-icon" alt="">
                <div class="w-0 flex-grow-1 pl-3">
                    <h1 class="page-header-title m-0">
                        {{ 'Informe inteligente de la tienda' }}
                    </h1>
                    <span>
                        {{ 'Supervise los análisis e informes comerciales de la tienda' }}
                    </span>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Page Header Menu -->
        <ul class="nav nav-tabs page-header-tabs mb-2">
            <li class="nav-item">
                <a href="{{ route('admin.transactions.report.store-summary-report') }}"
                    class="nav-link">{{ 'Informe resumido' }}</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.transactions.report.store-sales-report') }}"
                    class="nav-link">{{ 'Informe de ventas' }}</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.transactions.report.store-order-report') }}"
                    class="nav-link active">{{ 'Informe de pedido' }}</a>
            </li>
        </ul>

        <div class="card filter--card">
            <div class="card-body p-xl-5">
                <h5 class="form-label m-0 mb-3">
                    {{ 'Filtrar datos' }}
                </h5>
                <form action="{{ route('admin.transactions.report.set-date') }}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4 col-sm-6">
                            <select name="zone_id" class="form-control js-select2-custom set-filter" data-url="{{ url()->full() }}" data-filter="zone_id" id="zone">
                                <option value="all">{{ 'Todas las Zonas' }}</option>
                                @foreach (\App\Models\Zone::orderBy('name')->get() as $z)
                                    <option value="{{ $z['id'] }}"
                                        {{ isset($zone) && $zone->id == $z['id'] ? 'selected' : '' }}>
                                        {{ $z['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <select name="store_id"
                                data-placeholder="{{ 'seleccionar tienda' }}"
                                class="js-data-example-ajax form-control set-filter" data-url="{{ url()->full() }}" data-filter="store_id">
                                @if (isset($store))
                                    <option value="{{ $store->id }}" selected>{{ $store->name }}</option>
                                @else
                                    <option value="all" selected>{{ 'todas las tiendas' }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <select class="form-control set-filter" data-url="{{ url()->full() }}" data-filter="filter" name="filter">
                                <option value="all_time" {{ isset($filter) && $filter == 'all_time' ? 'selected' : '' }}>
                                    {{ 'Todo el tiempo' }}</option>
                                <option value="this_year" {{ isset($filter) && $filter == 'this_year' ? 'selected' : '' }}>
                                    {{ 'este año' }}</option>
                                <option value="previous_year"
                                    {{ isset($filter) && $filter == 'previous_year' ? 'selected' : '' }}>{{ 'Año anterior' }}
                                </option>
                                <option value="this_month"
                                    {{ isset($filter) && $filter == 'this_month' ? 'selected' : '' }}>{{ 'este mes' }}</option>
                                <option value="this_week" {{ isset($filter) && $filter == 'this_week' ? 'selected' : '' }}>
                                    {{ 'Esta semana' }}</option>
                                <option value="custom" {{ isset($filter) && $filter == 'custom' ? 'selected' : '' }}>
                                    {{ 'Costumbre' }}</option>
                            </select>
                        </div>
                        @if (isset($filter) && $filter == 'custom')
                        <div class="col-md-4 col-sm-6">
                            <input type="date" name="from" id="from_date"
                                {{ session()->has('from_date') ? 'value=' . session('from_date') : '' }}
                                class="form-control" required>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <input type="date" name="to" id="to_date"
                                {{ session()->has('to_date') ? 'value=' . session('to_date') : '' }} class="form-control"
                                required>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <button type="submit" class="btn btn--primary btn-block">{{ 'mostrar datos' }}</button>
                        </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>


        <div class="store-report-content mt-11px">
            <div class="left-content">
                <div class="left-content-card">
                    <img src="{{ asset('assets/admin/img/report/cart.svg') }}" alt="">
                    <div class="info">
                        <h4 class="subtitle">{{ $orders->total() }}</h4>
                        <h6 class="subtext">{{ 'Orden total' }}</h6>
                    </div>
                </div>
                <div class="left-content-card">
                    <img src="{{ asset('assets/admin/img/report/total-order.svg') }}" alt="">
                    <div class="info">
                        <h4 class="subtitle">{{ \App\CentralLogics\Helpers::number_format_short($total_order_amount) }}
                        </h4>
                        <h6 class="subtext">{{ 'monto total del pedido' }}</h6>
                    </div>
                    <div class="coupon__discount w-100 text-right d-flex justify-content-between">
                        <div>
                            <strong class="text-danger">{{ \App\CentralLogics\Helpers::number_format_short($total_canceled) }}</strong>
                            <div>{{ 'Cancelado' }}</div>
                        </div>
                        <div>
                            <strong>{{ \App\CentralLogics\Helpers::number_format_short($total_ongoing) }}</strong>
                            <div>
                                {{ 'Incompleto' }}
                            </div>
                        </div>
                        <div>
                            <strong class="text-success">{{ \App\CentralLogics\Helpers::number_format_short($total_delivered) }}</strong>
                            <div>
                                {{ 'Terminado' }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="left-content-card">
                    <img src="{{ asset('assets/admin/img/report/total-discount.svg') }}" alt="">
                    <div class="info">
                        <h4 class="subtitle">
                            {{ \App\CentralLogics\Helpers::number_format_short($total_coupon_discount + $total_product_discount) }}
                        </h4>
                        <h6 class="subtext">{{ 'Descuento total otorgado' }}</h6>
                    </div>
                    <div class="coupon__discount w-100 text-right d-flex justify-content-between">
                        <div>
                            <strong>{{ \App\CentralLogics\Helpers::number_format_short($total_coupon_discount) }}</strong>
                            <div>{{ 'cupón de descuento' }}</div>
                        </div>
                        <div>
                            <strong>{{ \App\CentralLogics\Helpers::number_format_short($total_product_discount) }}</strong>
                            <div>
                                {{ 'Descuento de producto' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="center-chart-area">
                <div class="center-chart-header">
                    <h4 class="title">{{ 'Órdenes totales' }}</h4>
                    <h5 class="subtitle">{{ 'Valor promedio del pedido:' }}
                        {{ $orders->count() > 0 ? \App\CentralLogics\Helpers::number_format_short($total_order_amount / $orders->total()) : 0 }}
                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                    data-placement="right"
                    data-original-title="{{ 'Valor medio de todo tipo de pedidos.' }}">
                    <i class="tio-info-outined"></i>
                </span>
                    </h5>
                </div>
                <canvas id="updatingData" class="store-center-chart"
                    data-hs-chartjs-options='{
                "type": "bar",
                "data": {
                  "labels": [{{ implode(',', $label) }}],
                  "datasets": [{
                    "data": [{{ implode(',', $data) }}],
                    "backgroundColor": "#82CFCF",
                    "hoverBackgroundColor": "#82CFCF",
                    "borderColor": "#82CFCF"
                  }]
                },
                "options": {
                  "scales": {
                    "yAxes": [{
                      "gridLines": {
                        "color": "#e7eaf3",
                        "drawBorder": false,
                        "zeroLineColor": "#e7eaf3"
                      },
                      "ticks": {
                        "beginAtZero": true,
                        "stepSize": {{ceil((array_sum($data)/10000))*2000}},
                        "fontSize": 12,
                        "fontColor": "#97a4af",
                        "fontFamily": "Open Sans, sans-serif",
                        "padding": 5,
                        "postfix": " {{ \App\CentralLogics\Helpers::currency_symbol() }}"
                      }
                    }],
                    "xAxes": [{
                      "gridLines": {
                        "display": false,
                        "drawBorder": false
                      },
                      "ticks": {
                        "fontSize": 12,
                        "fontColor": "#97a4af",
                        "fontFamily": "Open Sans, sans-serif",
                        "padding": 5
                      },
                      "categoryPercentage": 0.3,
                      "maxBarThickness": "10"
                    }]
                  },
                  "cornerRadius": 5,
                  "tooltips": {
                    "prefix": " ",
                    "hasIndicator": true,
                    "mode": "index",
                    "intersect": false
                  },
                  "hover": {
                    "mode": "nearest",
                    "intersect": true
                  }
                }
              }'>
                </canvas>
            </div>
            <div class="right-content">
                <!-- Dognut Pie -->
                <div class="card h-100 bg-white payment-statistics-shadow">
                    <div class="card-header border-0 ">
                        <h5 class="card-title">
                            <span>{{ 'estadísticas de pedidos' }}</span>
                        </h5>
                    </div>
                    <div class="card-body px-0 pt-0">
                        <div class="position-relative pie-chart">
                            <div id="dognut-pie"></div>
                            <!-- Total Orders -->
                            <div class="total--orders">
                                <h3>{{ $orders->total() }}
                                </h3>
                                <span>{{ 'Pedidos' }}</span>
                            </div>
                            <!-- Total Orders -->
                        </div>
                        <div class="apex-legends">
                            <div class="before-bg-107980">
                                <span>{{ 'Total cancelado' }}
                                    ({{ $total_canceled_count }})</span>
                            </div>
                            <div class="before-bg-56B98F">
                                <span>{{ 'Total en curso' }} (
                                    {{ $total_ongoing_count }})</span>
                            </div>
                            <div class="before-bg-E5F5F1">
                                <span>{{ 'Total entregado' }}
                                    ({{ $total_delivered_count }})</span>
                            </div>
                        </div>
                        <div class="earning-statistics-content mt-3">
                            <a href="{{ route('admin.order.list', ['all']) }}" class="trx-btn">{{ 'Ver todos los pedidos' }}</a>
                        </div>
                    </div>
                </div>
                <!-- Dognut Pie -->
            </div>
        </div>

        <div class="mt-11px card">
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{ 'Ventas totales' }}</h5>
                    <form class="search-form">
                        <!-- Search -->
                        {{-- @csrf --}}
                        <div class="input-group input--group">
                            <input id="datatableSearch_" type="search" name="search" class="form-control"
                                placeholder="{{ 'Buscar por DNI..' }}"
                                aria-label="{{ 'buscar' }}" value="{{ request()?->search ?? null}}" required>
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>

                        </div>
                        <!-- End Search -->
                    </form>
                    <!-- Unfold -->
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40"
                            href="javascript:;"
                            data-hs-unfold-options='{
                                "target": "#usersExportDropdown",
                                "type": "css-animation"
                            }'>
                            <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                            <a id="export-excel" class="dropdown-item"
                                href="{{ route('admin.transactions.report.store-order-report-export', ['type' => 'excel', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ 'sobresalir' }}
                            </a>
                            <a id="export-csv" class="dropdown-item"
                                href="{{ route('admin.transactions.report.store-order-report-export', ['type' => 'csv', request()->getQueryString()]) }}">
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
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless middle-align __txt-14px">
                        <thead class="thead-light white--space-false">
                            <tr>
                                <th class="border-top border-bottom text-capitalize">{{ 'SL' }}</th>
                                <th class="border-top border-bottom text-capitalize">{{ 'ID de pedido' }}</th>
                                <th class="border-top border-bottom text-capitalize">{{ 'Fecha del pedido' }}</th>
                                <th class="border-top border-bottom text-capitalize">{{ 'Información del cliente' }}</th>
                                <th class="border-top border-bottom text-capitalize">{{ 'Monto total' }}</th>
                                <th class="border-top border-bottom text-capitalize text-center">
                                    {{ 'Descuento' }}</th>
                                <th class="border-top border-bottom text-capitalize text-center">{{ 'Impuesto' }}
                                </th>
                                <th class="border-top border-bottom text-capitalize text-center">
                                    {{ 'Cargo de entrega' }}</th>
                                <th class="border-top border-bottom text-capitalize text-center">
                                    {{ \App\CentralLogics\Helpers::get_business_data('additional_charge_name')??'cargo adicional' }}</th>
                                <th class="border-top border-bottom text-capitalize text-center">{{ 'Acción' }}
                                </th>
                            </tr>
                        </thead>
                        <tbody id="set-rows">
                            @foreach ($orders as $key => $order)
                                <tr class="status-{{ $order['order_status'] }} class-all">
                                    <td class="">
                                        {{ $key + $orders->firstItem() }}
                                    </td>
                                    <td class="table-column-pl-0">
                                        <a
                                            href="{{ route('admin.order.details', ['id' => $order['id'],'module_id'=>$order['module_id']]) }}">{{ $order['id'] }}</a>
                                    </td>
                                    <td>
                                        <div>
                                            <div>
                                                {{ date('d M Y', strtotime($order['created_at'])) }}
                                            </div>
                                            <div class="d-block text-uppercase">
                                                {{ date(config('timeformat'), strtotime($order['created_at'])) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($order->is_guest)
                                        @php($customer_details = json_decode($order['delivery_address'],true))
                                        <strong>{{$customer_details['contact_person_name']}}</strong>
                                        <div>{{$customer_details['contact_person_number']}}</div>
                                        @elseif ($order->customer)
                                        <a class="text-body text-capitalize"
                                            href="{{ route('admin.transactions.customer.view', [$order['user_id']]) }}">
                                            <strong>{{ $order->customer['f_name'] . ' ' . $order->customer['l_name'] }}</strong>
                                            <div>{{ $order->customer['phone'] }}</div>
                                        </a>
                                        @else
                                            <label class="badge badge-danger">{{ 'datos de cliente no válidos' }}</label>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-right mw--85px">
                                            <div>
                                                {{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']) }}
                                            </div>
                                            @if ($order->payment_status == 'paid')
                                                <strong class="text-success">
                                                    {{ 'pagado' }}
                                                </strong>
                                            @else
                                                <strong class="text-danger">
                                                    {{ 'no pagado' }}
                                                </strong>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center mw--85px">
                                        {{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount'] + $order['store_discount_amount']  + $order['ref_bonus_amount']) }}
                                    </td>
                                    <td class="text-center mw--85px">
                                        {{ \App\CentralLogics\Helpers::number_format_short($order['total_tax_amount']) }}
                                    </td>
                                    <td class="text-center mw--85px">
                                        {{ \App\CentralLogics\Helpers::number_format_short($order['original_delivery_charge']) }}
                                    </td>
                                    <td class="text-center mw--85px">
                                        {{ \App\CentralLogics\Helpers::number_format_short($order['additional_charge']) }}
                                    </td>

                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="ml-2 btn btn-sm btn--warning btn-outline-warning action-btn"
                                                href="{{ route('admin.order.details', ['id' => $order['id'],'module_id'=>$order['module_id']]) }}">
                                                <i class="tio-invisible"></i>
                                            </a>
                                            <a class="ml-2 btn btn-sm btn--primary btn-outline-primary action-btn"
                                                href="{{ route('admin.transactions.order.generate-invoice', ['id' => $order['id']]) }}">
                                                <i class="tio-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- End Table -->


                @if (count($orders) !== 0)
                    <hr>
                    <div class="page-area">
                        {!! $orders->withQueryString()->links() !!}
                    </div>
                @endif
                @if (count($orders) === 0)
                    <div class="empty--data">
                        <img src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                        <h5>
                            {{ 'no se encontraron datos' }}
                        </h5>
                    </div>
                @endif
            </div>
        </div>


    </div>

@endsection


@push('script')
    <!-- Apex Charts -->
@endpush


@push('script_2')
    <script src="{{ asset('assets/admin') }}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{ asset('assets/admin') }}/vendor/chart.js.extensions/chartjs-extensions.js"></script>
    <script
        src="{{ asset('assets/admin') }}/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js">
    </script>


    <!-- Apex Charts -->
    <script src="{{ asset('assets/admin/js/apex-charts/apexcharts.js') }}"></script>
    <!-- Dognut Pie Chart -->
    <script>
        "use strict";
        let options = {
            series: [{{ $total_canceled_count}}, {{ $total_ongoing_count}}, {{ $total_delivered_count }}],
            chart: {
                width: 320,
                type: 'donut',
            },
            labels: ['{{ 'Total cancelado' }} ({{ $total_canceled_count}})',
                '{{ 'Total en curso' }} ({{ $total_ongoing_count}})',
                '{{ 'Total entregado' }}  ({{ $total_delivered_count }})'
            ],
            dataLabels: {
                enabled: false,
                style: {
                    colors: ['#ffffff', '#ffffff', '#107980']
                }
            },
            responsive: [{
                breakpoint: 1650,
                options: {
                    chart: {
                        width: 260
                    },
                }
            }],
            colors: ['#107980', '#56B98F', '#111'],
            fill: {
                colors: ['#107980', '#56B98F', '#E5F5F1']
            },
            legend: {
                show: false
            },
        };

        let chart = new ApexCharts(document.querySelector("#dognut-pie"), options);
        chart.render();

    <!-- Dognut Pie Chart -->



        // Bar Charts
        Chart.plugins.unregister(ChartDataLabels);

        $('.js-chart').each(function() {
            $.HSCore.components.HSChartJS.init($(this));
        });

        let updatingChart = $.HSCore.components.HSChartJS.init($('#updatingData'));

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

        $('#search-form').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.transactions.report.store-order-report-search') }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    $('#set-rows').html(data.view);
                    $('.page-area').hide();
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush
