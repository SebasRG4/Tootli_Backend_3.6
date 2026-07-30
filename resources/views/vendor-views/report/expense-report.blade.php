@extends('layouts.vendor.app')

@section('title', 'informe de gastos')

@push('css_or_js')
@endpush

@section('content')
    @php
        $vendorData = \App\CentralLogics\Helpers::get_store_data();
        $vendor = $vendorData?->module_type;
        $title = $vendor == 'rental' ? 'Provider' : 'Store';
        $orderOrTrip = $vendor == 'rental' ? 'trip' : 'order';
        $type = $vendor == 'rental' ? 'vehicle' : 'item';
    @endphp
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    {{-- <img src="{{ asset('assets/admin/img/report.png') }}" class="w--22" alt=""> --}}
                </span>
                <span>
                    {{ 'informe de gastos' }}
                </span>
            </h1>
            <div class="__page-header-txt mt-3">
                {{ 'Este informe mostrará todos los \'.$orderOrTrip.\' en el que el \'.$title.\' Se ha utilizado el descuento. El \'.$título.\' Los descuentos son: entrega gratuita, cupón de descuento y \'.$tipo\'. descuentos (parciales según comisión \'.$orderOrTrip.\').' }}
            </div>

        </div>
        <!-- End Page Header -->

        <div class="card mb-20">
            <div class="card-body">
                <h4 class="">{{ 'Buscar datos' }}</h4>
                <form method="get">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <select class="form-control set-filter" name="filter"
                                    data-url="{{ url()->full() }}" data-filter="filter">
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
                                class="btn btn-primary btn-block h--45px">{{ 'Filtrar' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- End Stats -->
        <!-- Card -->
        <div class="card mt-3">
            <!-- Header -->
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper">
                    <h3 class="card-title">
                        {{ 'listas de gastos' }} <span
                            class="badge badge-soft-secondary" id="countItems">{{ $expense->total() }}</span>
                    </h3>
                    <form  class="search-form">
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input name="search" value="{{ request()->search ?? null }}"   type="search" class="form-control" placeholder="{{ 'Buscar por ID de pedido' }}">
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                        </div>
                        <!-- End Search -->
                    </form>
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
                            <a id="export-excel" class="dropdown-item" href="{{route('vendor.report.expense-export', ['type'=>'excel',request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ 'sobresalir' }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="{{route('vendor.report.expense-export', ['type'=>'csv',request()->getQueryString()])}}">
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
                                <th >{{'SL'}}</th>
                                @if($module_type == 'rental')
                                <th class="text-center" >{{'identificación del viaje'}}</th>
                                @else
                                <th class="text-center" >{{'identificación del pedido'}}</th>
                                @endif
                                <th class="text-center" >{{'Fecha y hora'}}</th>
                                <th class="text-center" >{{ 'Tipo de gasto' }}</th>
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
                                @if($module_type == 'rental')
                                    <td class="text-center" >
                                        @if (isset($exp['trip_id']))
                                            <a href="{{route('vendor.trip.details',['id'=>$exp['trip_id']])}}">{{$exp['trip_id']}}</a>
                                        @else
                                            <label class="badge badge-danger">{{'datos de viaje no válidos'}}</label>
                                        @endif
                                    </td>
                                @else
                                    <td class="text-center" >
                                        @if (isset($exp['order_id']))
                                            <a href="{{route('vendor.order.details',['id'=>$exp['order_id']])}}">{{$exp['order_id']}}</a>
                                        @else
                                            <label class="badge badge-danger">{{'datos de pedido no válidos'}}</label>
                                        @endif
                                    </td>
                                @endif
                                <td class="text-center">
                                    {{date('Y-m-d '.config('timeformat'),strtotime($exp->created_at))}}
                                </td>
                                <td class="text-center" >
                                    {{Str::title('{$exp[\'tipo\']}')}}</td>




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
            </div>            <!-- End Body -->
        </div>
        <!-- End Card -->
    </div>
@endsection

@push('script')
@endpush

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/vendor/report.js"></script>
@endpush

