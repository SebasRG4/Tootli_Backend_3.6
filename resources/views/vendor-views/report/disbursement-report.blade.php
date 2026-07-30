@extends('layouts.vendor.app')

@section('title', 'Informe de desembolso')

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
        </div>
        <!-- Reports -->
        <div class="disbursement-report mb-20">
            <div class="__card-3 rebursement-item">
                <img src="{{asset('assets/admin/img/report/new/trx1.png')}}" class="icon" alt="report/new">
                <h3 class="title text-008958">{{\App\CentralLogics\Helpers::format_currency($pending)}}
                </h3>
                <h6 class="subtitle">{{ 'Desembolsos pendientes' }}</h6>
                <div class="info-icon" data-toggle="tooltip" data-placement="top" data-original-title="{{ 'Cuando el desembolso está pendiente el importe total del pedido pasa a este apartado.' }}">
                    <img src="{{asset('assets/admin/img/report/new/info1.png')}}" alt="report/new">
                </div>
            </div>

            <div class="__card-3 rebursement-item">
                <img src="{{asset('assets/admin/img/report/new/trx5.png')}}" class="icon" alt="report/new">
                <h3 class="title text-FF7E0D">{{\App\CentralLogics\Helpers::format_currency($completed)}}
                </h3>
                <h6 class="subtitle">{{ 'Desembolsos completados' }}</h6>
                <div class="info-icon" data-toggle="tooltip" data-placement="top" data-original-title="{{ 'Cuando se completa el desembolso, el importe total del pedido pasa a esta sección.' }}">
                    <img src="{{asset('assets/admin/img/report/new/info5.png')}}" alt="report/new">
                </div>
            </div>

            <div class="__card-3 rebursement-item">
                <img src="{{asset('assets/admin/img/report/new/trx3.png')}}" class="icon" alt="report/new">
                <h3 class="title text-FF5A54">{{\App\CentralLogics\Helpers::format_currency($canceled)}}
                </h3>
                <h6 class="subtitle">{{ 'Transacciones canceladas' }}</h6>
                <div class="info-icon" data-toggle="tooltip" data-placement="top" data-original-title="{{ 'Cuando se cancela el desembolso, el importe total del pedido pasa a esta sección.' }}">
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
                            <select class="form-control set-filter" name="filter"
                                    data-url="{{ url()->full() }}" data-filter="filter">
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
                        <a id="export-excel" class="dropdown-item" href="{{route('vendor.report.disbursement-report-export', ['type'=>'excel',request()->getQueryString()])}}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('assets/admin')}}/svg/components/excel.svg" alt="Image Description">
                            {{'sobresalir'}}
                        </a>
                        <a id="export-csv" class="dropdown-item" href="{{route('vendor.report.disbursement-report-export', ['type'=>'excel',request()->getQueryString()])}}">
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
                        <th>{{ 'creado en' }}</th>
                        <th>{{ 'Monto desembolsado' }}</th>
                        <th>{{ 'Método de pago' }}</th>
                        <th>{{ 'estado' }}</th>
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
                            <td>
                                {{ \App\CentralLogics\Helpers::time_date_format($disbursement->created_at)  }}
                            </td>
                            <td>
                                {{\App\CentralLogics\Helpers::format_currency($disbursement['disbursement_amount'])}}
                            </td>
                            <td>
                                <div>
                                    {{$disbursement->withdraw_method->method_name}}
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
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if (count($disbursements) === 0)

                    <div class="empty--data">
                         <img src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
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
    <script src="{{asset('assets/admin')}}/js/view-pages/vendor/report.js"></script>
@endpush

