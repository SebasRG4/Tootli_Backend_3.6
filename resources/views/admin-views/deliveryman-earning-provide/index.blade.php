@extends('layouts.admin.app')

@section('title','repartidores ganando proporcionar')

@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Heading -->
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/report.png')}}" class="w--22" alt="">
            </span>
            <span>
                {{'proporcionar a los repartidores ganancias'}}
            </span>
        </h1>
    </div>
    <!-- Page Heading -->
    <div class="card">
        <div class="card-body">
            <form action="{{route('admin.transactions.provide-deliveryman-earnings.store')}}" method='post' id="add_transaction">
                @csrf
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="form-group mb-0">
                            <label class="form-label" for="deliveryman">{{'Repartidor'}}<span class="input-label-secondary"></span></label>
                            <select id="deliveryman" name="deliveryman_id" data-placeholder="{{'seleccionar repartidor'}}" data-url="{{url('/')}}/admin/users/delivery-man/get-account-data/" data-type="deliveryman" class="form-control account-data" title="Select deliveryman">

                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group mb-0">
                            <label class="form-label" for="amount">{{'cantidad'}}<span class="input-label-secondary" id="account_info"></span></label>
                            <input class="form-control" type="number" min="1" step="0.01" name="amount" id="amount" max="999999999999.99" placeholder="{{'ex 100'}}">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group mb-0">
                            <label class="form-label" for="method">{{'método'}}<span class="input-label-secondary"></span></label>
                            <input class="form-control" type="text" name="method" id="method" required maxlength="191" placeholder="{{'ex efectivo'}}">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group mb-0">
                            <label class="form-label" for="ref">{{'referencia'}}<span class="input-label-secondary"></span></label>
                            <input  class="form-control" type="text" name="ref" id="ref" maxlength="191" placeholder="{{'ex cobrar efectivo'}}">
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="btn--container justify-content-end">
                            <button class="btn btn--reset" type="reset" id="reset_btn">{{'reiniciar'}}</button>
                            <button class="btn btn--primary" type="submit">{{'ahorrar'}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header py-2 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">
                            <span class="card-header-icon">
                                <i class="tio-user"></i>
                            </span>
                            <span>
                                {{ 'repartidores que ganan proporcionan tabla'}}
                            </span>
                            <span class="badge badge-soft-secondary" id="itemCount">
                                ({{ $provide_dm_earning->total() }})
                            </span>
                        </h5>

                        <form class="search-form">
                        {{-- @csrf --}}
                            <!-- Search -->
                            <div class="input-group input--group">
                                <input id="datatableSearch" name="search" type="search" class="form-control h--40px" placeholder="{{'Ej: buscar repartidor'}}" value="{{ request()?->search ?? null}}" aria-label="{{'buscar aquí'}}">
                                <button type="submit" class="btn btn--secondary h--40px"><i class="tio-search"></i></button>
                            </div>
                            <!-- End Search -->
                        </form>

                        <!-- Unfold -->
                        <div class="hs-unfold mr-2">
                            <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40" href="javascript:;"
                                data-hs-unfold-options='{
                                        "target": "#usersExportDropdown",
                                        "type": "css-animation"
                                    }'>
                                <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                            </a>

                            <div id="usersExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                                <a id="export-excel" class="dropdown-item" href="{{route('admin.transactions.export-deliveryman-earning', ['type'=>'excel',request()->getQueryString()])}}">
                                    <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                        alt="Image Description">
                                    {{ 'sobresalir' }}
                                </a>
                                <a id="export-csv" class="dropdown-item" href="{{route('admin.transactions.export-deliveryman-earning', ['type'=>'csv',request()->getQueryString()])}}">
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
                        <table id="datatable"
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">{{'SL'}}</th>
                                    <th class="border-0">{{'nombre'}}</th>
                                    <th class="border-0">{{'recibido en'}}</th>
                                    <th class="border-0">{{'cantidad'}}</th>
                                    <th class="border-0">{{'método'}}</th>
                                    <th class="border-0">{{'referencia'}}</th>
                                </tr>
                            </thead>
                            <tbody id="set-rows">
                            @foreach($provide_dm_earning as $k=>$at)
                                <tr>
                                    <td>{{$k+$provide_dm_earning->firstItem()}}</td>
                                    <td>@if($at->delivery_man)<a href="{{route('admin.users.delivery-man.preview', $at->delivery_man_id)}}">{{$at->delivery_man->f_name.' '.$at->delivery_man->l_name}}</a> @else <label class="text-capitalize text-danger">{{'repartidor eliminado'}}</label> @endif </td>
                                    <td>{{\App\CentralLogics\Helpers::time_date_format($at->created_at)}}</td>
                                    <td>{{\App\CentralLogics\Helpers::format_currency($at['amount'])}}</td>
                                    <td>{{$at['method']}}</td>
                                    @if(  $at['ref'] == 'delivery_man_wallet_adjustment_full')
                                        <td>{{ 'billetera ajustada' }}</td>
                                    @elseif( $at['ref'] == 'delivery_man_wallet_adjustment_partial')
                                        <td>{{ 'cartera ajustada parcialmente' }}</td>
                                    @else
                                        <td>{{$at['ref']}}</td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(count($provide_dm_earning) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    {!! $provide_dm_earning->links() !!}
                </div>
                @if(count($provide_dm_earning) === 0)
                <div class="empty--data">
                    <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                    <h5>
                        {{'no se encontraron datos'}}
                    </h5>
                </div>
                @endif
            </div>
        </div>
     </div>
</div>
@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/deliveryman-earning-provide.js"></script>
<script>
    "use strict";

    $('#deliveryman').select2({
        ajax: {
            url: '{{url('/')}}/admin/users/delivery-man/get-deliverymen',
            data: function (params) {
                return {
                    q: params.term, // search term
                    earning: true,
                    page: params.page
                };
            },
            processResults: function (data) {
                return {
                results: data
                };
            },
            __port: function (params, success, failure) {
                var $request = $.ajax(params);

                $request.then(success);
                $request.fail(failure);

                return $request;
            }
        }
    });

    $('#add_transaction').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.post({
            url: '{{route('admin.transactions.provide-deliveryman-earnings.store')}}',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.errors) {
                    for (var i = 0; i < data.errors.length; i++) {
                        toastr.error(data.errors[i].message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                } else {
                    toastr.success('{{'transacción guardada'}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    setTimeout(function () {
                        location.href = '{{route('admin.transactions.provide-deliveryman-earnings.index')}}';
                    }, 2000);
                }
            }
        });
    });

    function getAccountData(route, data_id, type)
    {
        $.get({
                url: route+data_id,
                dataType: 'json',
                success: function (data) {
                    $('#account_info').html('({{'efectivo en mano'}}: '+data.cash_in_hand+' {{'saldo de ganancias'}}: '+data.earning_balance+')');
                },
            });
    }
</script>
@endpush
