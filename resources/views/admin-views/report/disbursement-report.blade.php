@extends('layouts.admin.app')

@section('title', 'informe de desembolso')

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/report/new/disburstment.png')}}" class="w--22" alt="">
            </span>
                <span>{{ 'Informe de desembolso' }}</span>
            </h1>
            <ul class="nav nav-tabs mb-4 border-0 pt-2">
                <li class="nav-item">
                    <a class="nav-link {{ $tab=='store'?'active':'' }}" href="{{ route('admin.transactions.report.disbursement_report',  ['tab' => 'store']) }}" >{{ 'Desembolsos de la tienda' }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab=='delivery_man'?'active':'' }}" href="{{ route('admin.transactions.report.disbursement_report',  ['tab' => 'delivery_man']) }}">{{ 'Desembolsos del repartidor' }}</a>
                </li>
            </ul>
        </div>
        <!-- Reports -->
        <div class="disbursement-report mb-20">
            <div class="__card-3 rebursement-item">
                <img src="{{asset('assets/admin/img/report/new/trx1.png')}}" class="icon" alt="report/new">
                <h3 class="title text-008958">{{\App\CentralLogics\Helpers::format_currency($pending)}}
                </h3>
                <h6 class="subtitle">{{ 'Desembolsos pendientes' }}</h6>
                <div class="info-icon" data-toggle="tooltip" data-placement="top" data-original-title="{{ 'Todas las solicitudes de desembolso pendientes que requieren la acción del administrador (completar/cancelar).' }}">
                    <img src="{{asset('assets/admin/img/report/new/info1.png')}}" alt="report/new">
                </div>
            </div>

            <div class="__card-3 rebursement-item">
                <img src="{{asset('assets/admin/img/report/new/trx5.png')}}" class="icon" alt="report/new">
                <h3 class="title text-FF7E0D">{{\App\CentralLogics\Helpers::format_currency($completed)}}
                </h3>
                <h6 class="subtitle">{{ 'Desembolsos completados' }}</h6>
                <div class="info-icon" data-toggle="tooltip" data-placement="top" data-original-title="{{ 'Se completa el monto del desembolso.' }}">
                    <img src="{{asset('assets/admin/img/report/new/info5.png')}}" alt="report/new">
                </div>
            </div>

            <div class="__card-3 rebursement-item">
                <img src="{{asset('assets/admin/img/report/new/trx3.png')}}" class="icon" alt="report/new">
                <h3 class="title text-FF5A54">{{\App\CentralLogics\Helpers::format_currency($canceled)}}
                </h3>
                <h6 class="subtitle">{{ 'Transacciones canceladas' }}</h6>
                <div class="info-icon" data-toggle="tooltip" data-placement="top" data-original-title="{{ 'Consulta aquí todos los montos de desembolsos cancelados.' }}">
                    <img src="{{asset('assets/admin/img/report/new/info3.png')}}" alt="report/new">
                </div>
            </div>
        </div>

        <div class="card mb-20">
            <div class="card-body">
                <h4 class="">{{ 'Buscar datos' }}</h4>
                <form method="get">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <select name="zone_id" class="form-control js-select2-custom set-filter" data-url="{{ url()->full() }}" data-filter="zone_id" id="zone">
                                <option value="all">{{ 'Todas las Zonas' }}</option>
                                @foreach (\App\Models\Zone::orderBy('name')->get(['id','name']) as $z)
                                    <option value="{{ $z['id'] }}"
                                        {{ isset($zone) && $zone->id == $z['id'] ? 'selected' : '' }}>
                                        {{ $z['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if($tab=='store')
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
                            <select name="store_id" data-url="{{ url()->full() }}" data-filter="store_id"
                                    data-placeholder="{{ 'seleccionar tienda' }}"
                                    class="js-data-example-ajax form-control set-filter">
                                @if (isset($store))
                                    <option value="{{ $store->id }}" selected>{{ $store->name }}</option>
                                @else
                                    <option value="all" selected>{{ 'todas las tiendas' }}</option>
                                @endif
                            </select>
                        </div>
                        @else
                        <div class="col-sm-6 col-md-3">
                            <select name="deliveryman_id" id="deliveryman" data-url="{{ url()->full() }}" data-filter="deliveryman_id"
                                    data-placeholder="{{ 'seleccionar repartidor' }}"
                                    class="js-data-example-ajax form-control set-filter">
                                @if (isset($delivery_man))
                                    <option value="{{ $delivery_man->id }}" selected>{{ $delivery_man->name }}</option>
                                @else
                                    <option value="all" selected>{{ 'todos los repartidores' }}</option>
                                @endif
                            </select>
                        </div>
                        @endif
                        <div class="col-sm-6 col-md-3">
                            <select name="payment_method_id" data-url="{{ url()->full() }}" data-filter="payment_method_id"
                                    data-placeholder="{{ 'seleccione el método de pago' }}"
                                    class="form-control js-select2-custom set-filter">
                                <option value="all">{{'Todos los métodos de pago'}}</option>
                                @foreach($withdrawal_methods as $item)
                                    <option value="{{$item['id']}}" {{ isset($payment_method_id) && $payment_method_id == $item['id'] ? 'selected' : '' }}>{{$item['method_name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <select name="status" data-url="{{ url()->full() }}" data-filter="status"
                                    data-placeholder="{{ 'seleccionar estado' }}"
                                    class="form-control js-select2-custom set-filter">
                                <option value="all" {{ isset($status) && $status == 'all' ? 'selected' : '' }}>{{'Todo el estado'}}</option>
                                <option value="pending" {{ isset($status) && $status == 'pending' ? 'selected' : '' }}>{{ 'Pendiente' }}</option>
                                <option value="completed" {{ isset($status) && $status == 'completed' ? 'selected' : '' }}>{{ 'terminado' }}</option>
                                <option value="canceled" {{ isset($status) && $status == 'canceled' ? 'selected' : '' }}>{{ 'Cancelado' }}</option>
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <select class="form-control set-filter" name="filter" data-url="{{ url()->full() }}" data-filter="filter">
                                <option value="all_time" {{ isset($filter) && $filter == 'all_time' ? 'selected' : '' }}>
                                    {{ 'Todo el tiempo' }}</option>
                                <option value="this_year" {{ isset($filter) && $filter == 'this_year' ? 'selected' : '' }}>
                                    {{ 'Este Año' }}</option>
                                <option value="previous_year"
                                    {{ isset($filter) && $filter == 'previous_year' ? 'selected' : '' }}>
                                    {{ 'Año anterior' }}</option>
                                <option value="this_month"
                                    {{ isset($filter) && $filter == 'this_month' ? 'selected' : '' }}>
                                    {{ 'Este Mes' }}</option>
                                <option value="this_week" {{ isset($filter) && $filter == 'this_week' ? 'selected' : '' }}>
                                    {{ 'Esta Semana' }}</option>
                                <option value="custom" {{ isset($filter) && $filter == 'custom' ? 'selected' : '' }}>
                                    {{ 'Costumbre' }}</option>
                            </select>
                        </div>
                        @if (isset($filter) && $filter == 'custom')
                            <div class="col-sm-6 col-md-3">
                                <input type="date" name="from" id="from_date" class="form-control"
                                       placeholder="{{ 'Fecha de inicio' }}"
                                       value={{ $from ? $from  : '' }} required>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <input type="date" name="to" id="to_date" class="form-control"
                                       placeholder="{{ 'Fecha de finalización' }}"
                                       value={{ $to ? $to  : '' }}  required>
                            </div>
                        @endif
                        <div class="col-sm-6 col-md-3 ml-auto">
                            <button type="submit"
                                    class="btn btn-primary btn-block">{{ 'Filtrar' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-header border-0 py-2">
            <div class="search--button-wrapper">
                <h2 class="card-title">
                    {{ 'Desembolsos totales' }} <span class="badge badge-soft-secondary ml-2" id="countItems">{{ $disbursements->total() }}</span>
                </h2>
                <form class="search-form">
                    <!-- Search -->
                    <div class="input--group input-group input-group-merge input-group-flush">
                        <input class="form-control" value="{{ request()?->search  ?? null }}" placeholder="{{ 'buscar por identificación' }}" name="search">
                        <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                    </div>
                    <!-- End Search -->
                </form>
                <!-- Static Export Button -->
                <div class="hs-unfold ml-3">
                    <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm" href="javascript:;"
                       data-hs-unfold-options='{
                            "target": "#usersExportDropdown",
                            "type": "css-animation"
                        }'>
                        <i class="tio-download-to mr-1"></i> {{'exportar'}}
                    </a>
                    <div id="usersExportDropdown"
                         class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                        <span class="dropdown-header">{{'opciones de descarga'}}</span>
                        <a id="export-excel" class="dropdown-item" href="{{route('admin.transactions.report.disbursement_report_export', ['type'=>'excel','tab'=>$tab,request()->getQueryString()])}}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('assets/admin')}}/svg/components/excel.svg" alt="Image Description">
                            {{'sobresalir'}}
                        </a>
                        <a id="export-csv" class="dropdown-item" href="{{route('admin.transactions.report.disbursement_report_export', ['type'=>'excel','tab'=>$tab,request()->getQueryString()])}}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('assets/admin')}}/svg/components/placeholder-csv-format.svg" alt="Image Description">
                            {{'csv'}}
                        </a>
                    </div>
                </div>
                <!-- Static Export Button -->
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-thead-bordered table-align-middle card-table">
                    <thead>
                    <tr>
                        <th>{{ 'SL' }}</th>
                        <th>{{ 'identificación' }}</th>
                        @if($tab=='store')
                        <th>{{ 'Información de la tienda' }}</th>
                        @else
                        <th>{{ 'Información del repartidor' }}</th>
                        @endif
                        <th>{{ 'creado en' }}</th>
                        <th>{{ 'Monto desembolsado' }}</th>
                        <th>{{ 'Método de pago' }}</th>
                        <th>{{ 'estado' }}</th>
                        <th>
                            <div class="text-center">
                                {{ 'acción' }}
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($disbursements as $key => $disbursement)
                        <tr>
                            <td>
                                <span class="font-weight-bold">{{ $key+1 }}</span>
                            </td>
                            <td>
                                #{{ $disbursement->disbursement_id }}
                            </td>
                            @if($tab=='store')
                            <td>
                                <a href="{{route('admin.store.view', [$disbursement->store->id, 'module_id'=>$disbursement->store->module_id])}}" alt="view store"
                                   class="table-rest-info">
                                    <div class="info">
                                            <span class="d-block text-body">
                                                {{ Str::limit($disbursement->store->name, 20, '...') }}<br>
                                            </span>
                                    </div>
                                </a>
                            </td>
                            @else
                                <td>
                                    <a href="{{route('admin.users.delivery-man.preview', $disbursement->delivery_man_id)}}"
                                       class="table-rest-info">
                                        <div class="info">
                                            <span class="d-block text-body">
                                                {{$disbursement->delivery_man->f_name.' '.$disbursement->delivery_man->l_name}}
                                            </span>
                                        </div>
                                    </a>
                                </td>
                            @endif
                            <td>
                                {{ \App\CentralLogics\Helpers::time_date_format($disbursement->created_at)  }}
                            </td>
                            <td>
                                {{\App\CentralLogics\Helpers::format_currency($disbursement['disbursement_amount'])}}
                            </td>
                            <td>
                                <div>
                                    {{$disbursement->withdraw_method?->method_name ?? 'N / A'}}
                                </div>
                            </td>
                            <td>
                                @if($disbursement->status=='pending')
                                    <label class="badge badge-soft-primary">{{ 'Pendiente' }}</label>
                                @elseif($disbursement->status=='completed')
                                    <label class="badge badge-soft-success">{{ 'Terminado' }}</label>
                                @else
                                    <label class="badge badge-soft-danger">{{ 'Cancelado' }}</label>
                                @endif
                            </td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn btn-sm btn--primary btn-outline-primary action-btn" data-toggle="modal" data-target="#payment-info-{{$disbursement->id}}" title="View Details">
                                        <i class="tio-visible"></i>
                                    </a>
                                </div>
                            </td>
                            @if($tab=='store')
                            <div class="modal fade" id="payment-info-{{$disbursement->id}}">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header pb-4">
                                            <button type="button" class="payment-modal-close btn-close border-0 outline-0 bg-transparent" data-dismiss="modal">
                                                <i class="tio-clear"></i>
                                            </button>
                                            <div class="w-100 text-center">
                                                <h2 class="mb-2">{{ 'Información de pago' }}</h2>
                                                <div>
                                                    <span class="mr-2">{{ 'ID de desembolso' }}</span>
                                                    <strong>#{{$disbursement->disbursement_id}}</strong>
                                                </div>
                                                <div class="mt-2">
                                                    <span class="mr-2">{{ 'estado' }}</span>
                                                    @if($disbursement->status=='pending')
                                                        <label class="badge badge-soft-primary">{{ 'Pendiente' }}</label>
                                                    @elseif($disbursement->status=='completed')
                                                        <label class="badge badge-soft-success">{{ 'Terminado' }}</label>
                                                    @else
                                                        <label class="badge badge-soft-danger">{{ 'Cancelado' }}</label>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-body">
                                            <div class="card shadow--card-2">
                                                <div class="card-body">
                                                    <div class="d-flex flex-wrap payment-info-modal-info p-xl-4">
                                                        <div class="item">
                                                            <h5>{{ 'Información de la tienda' }}</h5>
                                                            <ul class="item-list">
                                                                <li class="d-flex flex-wrap">
                                                                    <span class="name">{{ 'nombre' }}</span>
                                                                    <span>:</span>
                                                                    <strong>{{$disbursement?->store?->name}}</strong>
                                                                </li>
                                                                <li class="d-flex flex-wrap">
                                                                    <span class="name">{{ 'contacto' }}</span>
                                                                    <span>:</span>
                                                                    <strong>{{$disbursement?->store?->phone}}</strong>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="item">
                                                            <h5>{{ 'Información del propietario' }}</h5>
                                                            <ul class="item-list">
                                                                <li class="d-flex flex-wrap">
                                                                    <span class="name">{{ 'nombre' }}</span>
                                                                    <span>:</span>
                                                                    <strong>{{$disbursement->store->vendor->f_name}} {{$disbursement->store->vendor->l_name}}</strong>
                                                                </li>
                                                                <li class="d-flex flex-wrap">
                                                                    <span class="name">{{ 'correo electrónico' }}</span>
                                                                    <span>:</span>
                                                                    <strong>{{$disbursement->store->vendor->email}}</strong>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="item w-100">
                                                            <h5>{{ 'Información de la cuenta' }}</h5>
                                                            <ul class="item-list">
                                                                <li class="d-flex flex-wrap">
                                                                    <span class="name">{{ 'método de pago' }}</span><strong>{{$disbursement->withdraw_method->method_name}}</strong>
                                                                </li>
                                                                <li class="d-flex flex-wrap">
                                                                    <span class="name">{{ 'cantidad' }}</span>
                                                                    <strong>{{\App\CentralLogics\Helpers::format_currency($disbursement['disbursement_amount'])}}</strong>
                                                                </li>
                                                                @forelse(json_decode($disbursement->withdraw_method->method_fields, true) as $key=> $item)
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{  translate($key) }}</span>
                                                                        <strong>{{$item}}</strong>
                                                                    </li>
                                                                @empty

                                                                @endforelse

                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                                <div class="modal fade" id="payment-info-{{$disbursement->id}}">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header pb-4">
                                                <button type="button" class="payment-modal-close btn-close border-0 outline-0 bg-transparent" data-dismiss="modal">
                                                    <i class="tio-clear"></i>
                                                </button>
                                                <div class="w-100 text-center">
                                                    <h2 class="mb-2">{{ 'Información de pago' }}</h2>
                                                    <div>
                                                        <span class="mr-2">{{ 'ID de desembolso' }}</span>
                                                        <strong>#{{$disbursement->disbursement_id}}</strong>
                                                    </div>
                                                    <div class="mt-2">
                                                        <span class="mr-2">{{ 'estado' }}</span>
                                                        @if($disbursement->status=='pending')
                                                            <label class="badge badge-soft-primary">{{ 'Pendiente' }}</label>
                                                        @elseif($disbursement->status=='completed')
                                                            <label class="badge badge-soft-success">{{ 'Terminado' }}</label>
                                                        @else
                                                            <label class="badge badge-soft-danger">{{ 'Cancelado' }}</label>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-body">
                                                <div class="card shadow--card-2">
                                                    <div class="card-body">
                                                        <div class="d-flex flex-wrap payment-info-modal-info p-xl-4">
                                                            <div class="item">
                                                                <h5>{{ 'Información del repartidor' }}</h5>
                                                                <ul class="item-list">
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ 'nombre' }}</span>
                                                                        <span>:</span>
                                                                        <strong>{{$disbursement->delivery_man->f_name.' '.$disbursement->delivery_man->l_name}}</strong>
                                                                    </li>
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ 'contacto' }}</span>
                                                                        <span>:</span>
                                                                        <strong>{{$disbursement?->delivery_man?->phone}}</strong>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="item">

                                                            </div>
                                                            <div class="item w-100">
                                                                <h5>{{ 'Información de la cuenta' }}</h5>
                                                                <ul class="item-list">
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ 'método de pago' }}</span><strong>{{$disbursement->withdraw_method?->method_name ?? 'N / A'}}</strong>
                                                                    </li>
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{ 'cantidad' }}</span>
                                                                        <strong>{{\App\CentralLogics\Helpers::format_currency($disbursement['disbursement_amount'])}}</strong>
                                                                    </li>
                                                                    @forelse(json_decode($disbursement->withdraw_method?->method_fields, true) ?? [] as $key=> $item)
                                                                        <li class="d-flex flex-wrap">
                                                                            <span class="name">{{  translate($key) }}</span>
                                                                            <strong>{{$item}}</strong>
                                                                        </li>
                                                                    @empty

                                                                    @endforelse

                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                @if(count($disbursements) === 0)
                    <div class="empty--data">
                        <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                        <h5>
                            {{'no se encontraron datos'}}
                        </h5>
                    </div>
                @endif
            </div>
        </div>
        <div class="page-area px-4 pb-3">
            <div class="d-flex align-items-center justify-content-end">
                <div>
                    {!!$disbursements->links()!!}
                </div>
            </div>
        </div>

    </div>
@endsection

@push('script')
@endpush

@push('script_2')
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
                            all:true,
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

            $('#deliveryman').select2({
                ajax: {
                    url: '{{url('/')}}/admin/users/delivery-man/get-deliverymen',
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    __port: function (params, success, failure) {
                        let $request = $.ajax(params);

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    }
                }
            });
        });
    </script>
@endpush

