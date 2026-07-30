@extends('layouts.admin.app')

@section('title',\App\Models\BusinessSetting::where(['key'=>'business_name'])->first()->value??'Panel de Control')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        @if(auth('admin')->user()->role_id == 1)
        <!-- Page Header -->
        <div class="tootli-dashboard-header">
            <div class="tootli-dashboard-title-group">
                <h1>{{'Panel de Control'}}</h1>
                <p>{{'Planifique, priorice y realice sus operaciones con facilidad.'}}</p>
            </div>
            <div class="tootli-actions-group">
                <div class="min--200 mr-2">
                    <select name="zone_id" class="form-control js-select2-custom fetch_data_zone_wise rounded-pill">
                        <option value="all">{{ 'Todas las Zonas' }}</option>
                        @foreach(\App\Models\Zone::orderBy('name')->get() as $zone)
                            <option
                                value="{{$zone['id']}}" {{$params['zone_id'] == $zone['id']?'selected':''}}>
                                {{$zone['name']}}
                            </option>
                        @endforeach
                    </select>
                </div>
                <a href="{{ route('admin.order.list', ['all']) }}" class="btn-tootli-pill-primary">
                    <i class="bi bi-plus-lg"></i>
                    <span>+ {{'Despacho'}}</span>
                </a>
                <a href="{{ route('admin.transactions.store.withdraw_list') }}" class="btn-tootli-pill-secondary">
                    <i class="bi bi-lightbulb"></i>
                    <span>{{'Inteligencia de Mercado'}}</span>
                </a>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Stats Grid (Row 1) -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="tootli-card tootli-card-featured">
                    <div class="tootli-card-header-row">
                        <span class="tootli-card-label">{{'Viajes Totales'}}</span>
                        <div class="tootli-icon-badge"><i class="bi bi-map"></i></div>
                    </div>
                    <div class="tootli-card-value">{{ $data['total_orders'] ?? '137' }}</div>
                    <div class="tootli-card-subtxt">↗ {{ $data['delivered'] ?? '63' }} {{'completados en total'}}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="tootli-card">
                    <div class="tootli-card-header-row">
                        <span class="tootli-card-label">{{'Viajes Completados'}}</span>
                        <div class="tootli-icon-badge"><i class="bi bi-map"></i></div>
                    </div>
                    <div class="tootli-card-value">{{ $data['delivered'] ?? '63' }}</div>
                    <div class="tootli-card-subtxt">↗ {{'46% de todos los viajes'}}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="tootli-card">
                    <div class="tootli-card-header-row">
                        <span class="tootli-card-label">{{'Viajes Activos'}}</span>
                        <div class="tootli-icon-badge"><i class="bi bi-map"></i></div>
                    </div>
                    <div class="tootli-card-value">{{ $data['accepted_by_dm'] + $data['preparing_in_rs'] + $data['picked_up'] }}</div>
                    <div class="tootli-card-subtxt">↗ {{'En curso ahora'}}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="tootli-card">
                    <div class="tootli-card-header-row">
                        <span class="tootli-card-label">{{'Solicitudes Pendientes'}}</span>
                        <div class="tootli-icon-badge"><i class="bi bi-map"></i></div>
                    </div>
                    <div class="tootli-card-value">{{ $data['searching_for_dm'] ?? '3' }}</div>
                    <div class="tootli-card-subtxt" style="color: #64748b !important;">{{'Esperando asignación'}}</div>
                </div>
            </div>
        </div>

        <!-- Mini Cards Row (Row 2) -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4 col-lg-2">
                <div class="tootli-mini-card">
                    <div>
                        <div class="tootli-mini-card-lbl">{{'Repartidores'}}</div>
                        <h4 class="tootli-mini-card-val">{{ \App\Models\DeliveryMan::count() }}</h4>
                    </div>
                    <div class="tootli-icon-badge"><i class="bi bi-person-badge"></i></div>
                </div>
            </div>
            <div class="col-sm-4 col-lg-2">
                <div class="tootli-mini-card">
                    <div>
                        <div class="tootli-mini-card-lbl">{{'Clientes'}}</div>
                        <h4 class="tootli-mini-card-val">{{ \App\Models\User::count() }}</h4>
                    </div>
                    <div class="tootli-icon-badge"><i class="bi bi-people"></i></div>
                </div>
            </div>
            <div class="col-sm-4 col-lg-2">
                <div class="tootli-mini-card">
                    <div>
                        <div class="tootli-mini-card-lbl">{{'Ganancias de Viajes'}}</div>
                        <h4 class="tootli-mini-card-val">${{ number_format(\App\Models\Order::where('order_status','delivered')->sum('order_amount'), 0) }}</h4>
                    </div>
                    <div class="tootli-icon-badge"><i class="bi bi-graph-up-arrow"></i></div>
                </div>
            </div>
            <div class="col-sm-4 col-lg-2">
                <div class="tootli-mini-card">
                    <div>
                        <div class="tootli-mini-card-lbl">{{'Comisión'}}</div>
                        <h4 class="tootli-mini-card-val">${{ number_format(\App\Models\Order::where('order_status','delivered')->sum('admin_commission'), 0) }}</h4>
                    </div>
                    <div class="tootli-icon-badge"><i class="bi bi-currency-dollar"></i></div>
                </div>
            </div>
            <div class="col-sm-4 col-lg-2">
                <div class="tootli-mini-card">
                    <div>
                        <div class="tootli-mini-card-lbl">{{'Restaurantes'}}</div>
                        <h4 class="tootli-mini-card-val">{{ \App\Models\Store::count() }}</h4>
                    </div>
                    <div class="tootli-icon-badge"><i class="bi bi-building"></i></div>
                </div>
            </div>
            <div class="col-sm-4 col-lg-2">
                <div class="tootli-mini-card">
                    <div>
                        <div class="tootli-mini-card-lbl">{{'Pedidos de Comida'}}</div>
                        <h4 class="tootli-mini-card-val">{{ $data['total_orders'] ?? '0' }}</h4>
                    </div>
                    <div class="tootli-icon-badge"><i class="bi bi-cart3"></i></div>
                </div>
            </div>
        </div>
                    <div class="col-12">
                        <div class="row g-2">
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.delivery-man.offline_payment_list', ['status' => 'pending'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/food/items.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{'Pendientes Pagos Repartidores'}}</span>
                                        </h6>
                                        <span class="card-title text-info">
                                            {{ \App\Models\DeliveryManOfflinePayment::where('status', 'pending')->count() }}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['delivered'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/food/unassigned.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{'Pedidos Sin Asignar'}}</span>
                                        </h6>
                                        <span class="card-title text-3F8CE8">
                                            {{$data['searching_for_dm']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['refunded'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/food/accepted.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{'Aceptado por el repartidor'}}</span>
                                        </h6>
                                        <span class="card-title text-success">
                                            {{$data['accepted_by_dm']}}
                                        </span>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['canceled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/food/packaging.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{'Embalaje'}}</span>
                                        </h6>
                                        <span class="card-title text-FFA800">
                                            {{$data['preparing_in_rs']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['failed'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/food/out-for.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{'Fuera de entrega'}}</span>
                                        </h6>
                                        <span class="card-title text-success">
                                            {{$data['picked_up']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['delivered'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/dashboard/grocery/delivered.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{'Entregado'}}</span>
                                        </h6>
                                        <span class="card-title text-success">
                                            {{$data['delivered']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['canceled'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/canceled.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{'Cancelado'}}</span>
                                        </h6>
                                        <span class="card-title text-danger">
                                            {{$data['canceled']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['refunded'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/refunded.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{'Reembolsado'}}</span>
                                        </h6>
                                        <span class="card-title text-danger">
                                            {{$data['refunded']}}
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a class="order--card h-100" href="{{route('admin.order.list',['failed'])}}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                            <img src="{{asset('assets/admin/img/order-status/payment-failed.svg')}}" alt="dashboard" class="oder--card-icon">
                                            <span>{{'pago fallido'}}</span>
                                        </h6>
                                        <span class="card-title text-danger">
                                            {{$data['refund_requested']}}
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- End Stats -->

        <div class="row g-2">
            <div class="col-lg-8 col--xl-8">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center __gap-12px">
                            <div class="__gross-amount">
                                <h6>$855.8K</h6>
                                <span>Gross Sale</span>
                            </div>
                            <div class="chart--label __chart-label p-0 move-left-100 ml-auto">
                                <span class="indicator chart-bg-2"></span>
                                <span class="info">
                                    Sale (2022)
                                </span>
                            </div>
                            <select class="custom-select border-0 text-center w-auto ml-auto">
                                <option>
                                    {{'este mes'}}
                                </option>
                                <option>
                                    {{'este año'}}
                                </option>
                            </select>
                        </div>
                        <div id="grow-sale-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col--xl-4">
                <!-- Card -->
                <div class="card h-100">
                    <!-- Header -->
                    <div class="card-header border-0">
                        <h5 class="card-header-title">
                            {{'Estadísticas de usuario'}}
                        </h5>
                        <select class="custom-select border-0 text-center w-auto user_overview_stats_update" name="user_overview">
                            <option
                                value="this_month" {{$params['user_overview'] == 'this_month'?'selected':''}}>
                                {{'este mes'}}
                            </option>
                            <option
                                value="overall" {{$params['user_overview'] == 'overall'?'selected':''}}>
                                {{'En general'}}
                            </option>
                        </select>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body" id="user-overview-board">
                        <div class="position-relative pie-chart">
                            <div id="dognut-pie"></div>
                            <!-- Total Orders -->
                            <div class="total--orders">
                                <h3 class="text-uppercase mb-xxl-2">{{ $data['customer'] + $data['stores'] + $data['delivery_man'] }}</h3>
                                <span class="text-capitalize">{{'usuarios totales'}}</span>
                            </div>
                            <!-- Total Orders -->
                        </div>
                        <div class="d-flex flex-wrap justify-content-center mt-4">
                            <div class="chart--label">
                                <span class="indicator chart-bg-1"></span>
                                <span class="info">
                                    {{'Cliente'}} {{$data['customer']}}
                                </span>
                            </div>
                            <div class="chart--label">
                                <span class="indicator chart-bg-2"></span>
                                <span class="info">
                                    {{'Negocio'}} {{$data['stores']}}
                                </span>
                            </div>
                            <div class="chart--label">
                                <span class="indicator chart-bg-3"></span>
                                <span class="info">
                                    {{'Repartidor'}} {{$data['delivery_man']}}
                                </span>
                            </div>
                        </div>

                    </div>
                    <!-- End Body -->
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-restaurants-view">
                    @include('admin-views.partials._top-restaurants',['top_restaurants'=>$data['top_restaurants']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="popular-restaurants-view">
                    @include('admin-views.partials._popular-restaurants',['popular'=>$data['popular']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-selling-foods-view">
                    @include('admin-views.partials._top-selling-foods',['top_sell'=>$data['top_sell']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-rated-foods-view">
                    @include('admin-views.partials._top-rated-foods',['top_rated_foods'=>$data['top_rated_foods']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-deliveryman-view">
                    @include('admin-views.partials._top-deliveryman',['top_deliveryman'=>$data['top_deliveryman']])
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100" id="top-customer-view">
                    @include('admin-views.partials._top-customer')
                </div>
                <!-- End Card -->
            </div>

        </div>
        @else
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{'Bienvenido'}}, {{auth('admin')->user()->f_name}}.</h1>
                    <p class="page-header-text">{{'mensaje de bienvenida al empleado'}}</p>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        @endif
    </div>
@endsection

@push('script')
    <script src="{{asset('assets/admin')}}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{asset('assets/admin')}}/vendor/chart.js.extensions/chartjs-extensions.js"></script>
    <script src="{{asset('assets/admin')}}/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>

    <!-- Apex Charts -->
    <script src="{{asset('assets/admin/js/apex-charts/apexcharts.js')}}"></script>
    <!-- Apex Charts -->

@endpush


@push('script_2')

    <!-- Dognut Pie Chart -->
    <script>
        "use strict";
        let options;
        let chart;
        options = {
            series: [{{ $data['customer']}}, {{$data['stores']}}, {{$data['delivery_man']}}],
            chart: {
                width: 320,
                type: 'donut',
            },
            labels: ['{{ 'Cliente' }}', '{{ 'Almacenar' }}', '{{ 'repartidor' }}'],
            dataLabels: {
                enabled: false,
                style: {
                    colors: ['#005555', '#00aa96', '#b9e0e0',]
                }
            },
            responsive: [{
                breakpoint: 1650,
                options: {
                    chart: {
                        width: 250
                    },
                }
            }],
            colors: ['#005555','#00aa96', '#111'],
            fill: {
                colors: ['#005555','#00aa96', '#b9e0e0']
            },
            legend: {
                show: false
            },
        };

        chart = new ApexCharts(document.querySelector("#dognut-pie"), options);
        chart.render();

    options = {
          series: [{
          name: 'Gross Sale',
          data: [60, 40, 80, 31, 42, 109, 100, 50, 30, 80, 65, 35]
        }],
          chart: {
          height: 350,
          type: 'area',
          toolbar: {
            show:false
        }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth',
          width: 2,
        },
        fill: {
            type: 'gradient',
            colors: ['#76ffcd'],
        },
        xaxis: {
        //   type: 'datetime',
          categories: ["{{ 'Ene' }}", "{{ 'Feb' }}", "{{ 'Mar' }}", "{{ 'Abr' }}", "{{ 'Puede' }}", "{{ 'Jun' }}", "{{ 'Jul' }}", "{{ 'Ago' }}", "{{ 'Sep' }}", "{{ 'Oct' }}", "{{ 'Nov' }}", "{{ 'Dic' }}" ]
        },
        tooltip: {
          x: {
            format: 'dd/MM/yy HH:mm'
          },
        },
        };

        chart = new ApexCharts(document.querySelector("#grow-sale-chart"), options);
        chart.render();

    <!-- Dognut Pie Chart -->
        // INITIALIZATION OF CHARTJS
        // =======================================================
        Chart.plugins.unregister(ChartDataLabels);

        $('.js-chart').each(function () {
            $.HSCore.components.HSChartJS.init($(this));
        });

        let updatingChart = $.HSCore.components.HSChartJS.init($('#updatingData'));

        function order_stats_update(type) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.dashboard-stats.order')}}',
                data: {
                    statistics_type: type
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (data) {
                    insert_param('statistics_type',type);
                    $('#order_stats').html(data.view)
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        }

        $('.fetch_data_zone_wise').on('change', function (){
            let zone_id = $(this).val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.dashboard-stats.zone')}}',
                data: {
                    zone_id: zone_id
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (data) {
                    insert_param('zone_id', zone_id);
                    $('#order_stats').html(data.order_stats);
                    $('#user-overview-board').html(data.user_overview);
                    $('#monthly-earning-graph').html(data.monthly_graph);
                    $('#popular-restaurants-view').html(data.popular_restaurants);
                    $('#top-deliveryman-view').html(data.top_deliveryman);
                    $('#top-rated-foods-view').html(data.top_rated_foods);
                    $('#top-restaurants-view').html(data.top_restaurants);
                    $('#top-selling-foods-view').html(data.top_selling_foods);
                    $('#stat_zone').html(data.stat_zone);
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        })

        $('.user_overview_stats_update').on('change', function (){
            let type = $(this).val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.dashboard-stats.user-overview')}}',
                data: {
                    user_overview: type
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (data) {
                    insert_param('user_overview',type);
                    $('#user-overview-board').html(data.view)
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        })

        function insert_param(key, value) {
            key = encodeURIComponent(key);
            value = encodeURIComponent(value);
            // kvp looks like ['key1=value1', 'key2=value2', ...]
            let kvp = document.location.search.substr(1).split('&');
            let i = 0;

            for (; i < kvp.length; i++) {
                if (kvp[i].startsWith(key + '=')) {
                    let pair = kvp[i].split('=');
                    pair[1] = value;
                    kvp[i] = pair.join('=');
                    break;
                }
            }
            if (i >= kvp.length) {
                kvp[kvp.length] = [key, value].join('=');
            }
            // can return this or...
            let params = kvp.join('&');
            // change url page with new params
            window.history.pushState('page2', 'Title', '{{url()->current()}}?' + params);
        }
    </script>
@endpush
