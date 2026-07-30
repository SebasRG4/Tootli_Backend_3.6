@extends('layouts.vendor.app')

@section('title','billetera de la tienda')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{asset('assets/admin/img/image_90.png')}}" alt="public">
                        </div>
                        <span>
                            {{'billetera de la tienda'}}
                        </span>
                    </h2>
                </div>
            </div>
        </div>
        <!-- End Page Header -->


        <?php
        $wallet = \App\Models\StoreWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
        if(isset($wallet)==false){
            \Illuminate\Support\Facades\DB::table('store_wallets')->insert([
                'vendor_id'=>\App\CentralLogics\Helpers::get_vendor_id(),
                'created_at'=>now(),
                'updated_at'=>now()
            ]);
            $wallet = \App\Models\StoreWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
        }
        ?>
        @include('vendor-views.wallet.partials._balance_data',['wallet'=>$wallet])


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
                        <a id="export-excel" class="dropdown-item" href="{{route('vendor.wallet.export', ['type'=>'excel',request()->getQueryString()])}}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('assets/admin')}}/svg/components/excel.svg" alt="Image Description">
                            {{'sobresalir'}}
                        </a>
                        <a id="export-csv" class="dropdown-item" href="{{route('vendor.wallet.export', ['type'=>'csv',request()->getQueryString()])}}">
                            <img class="avatar avatar-xss avatar-4by3 mr-2" src="{{asset('assets/admin')}}/svg/components/placeholder-csv-format.svg" alt="Image Description">
                            {{'csv'}}
                        </a>

                    </div>
                </div>
                <!-- Static Export Button -->

                <!-- Action button after check table row -->
                <div id="action-section" class="d--none">
                    <button class="btn btn-danger btn-outline-danger" id="cancel">{{ 'Cancelar' }}</button>
                    <button class="btn btn-success" id="complete">{{ 'completo' }}</button>
                </div>
                <!-- Action button after check table row -->

            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-thead-bordered table-align-middle card-table">
                    <thead>
                    <tr>
                        <th>{{ 'SL' }}</th>
                        <th>{{ 'IDENTIFICACIÓN' }}</th>
                        <th>{{ 'Creado en' }}</th>
                        <th>{{ 'Monto desembolsado' }}</th>
                        <th>{{ 'Método de pago' }}</th>
                        <th>{{ 'Fecha de pago' }}</th>
                        <th>{{ 'estado' }}</th>
                        <th>
                            <div class="text-center">
                                {{ 'acción' }}
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($disbursements as $key => $store)

                        <tr>
                            <td>
                                <span class="font-weight-bold">{{$key+ $disbursements->firstItem()}}</span>
                            </td>
                            <td>
                                <span class="font-weight-bold">{{$store->disbursement_id}}</span>
                            </td>
                            <td>
                                {{ \App\CentralLogics\Helpers::time_date_format( $store->created_at )  }}

                            </td>

                            <td>
                                {{\App\CentralLogics\Helpers::format_currency($store['disbursement_amount'])}}
                            </td>
                            <td>
                                <div>
                                    {{$store->withdraw_method->method_name}}
                                </div>
                            </td>
                            <td>
                                @php($store_disbursement_waiting_time = (int) \App\Models\BusinessSetting::where('key', 'store_disbursement_waiting_time')->first()?->value ?? 0)
                                <div>
                                    {{ $store->created_at->addDays($store_disbursement_waiting_time)->format('d-M-y')  }}
                                    <small>
                                        {{  'Estimado' }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                @if($store->status=='pending')
                                    <label class="badge badge-soft-primary">{{ 'Pendiente' }}</label>
                                @elseif($store->status=='completed')
                                    <label class="badge badge-soft-success">{{ 'Terminado' }}</label>
                                @else
                                    <label class="badge badge-soft-danger">{{ 'Cancelado' }}</label>
                                @endif
                            </td>


                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn btn-sm btn--primary btn-outline-primary action-btn" data-toggle="modal" data-target="#payment-info-{{$store->id}}" title="{{ 'Ver detalles' }}">
                                        <i class="tio-visible"></i>
                                    </a>

                                </div>
                            </td>
                            <div class="modal fade" id="payment-info-{{$store->id}}">
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
                                                    <strong>#{{$store->disbursement_id}}</strong>
                                                </div>
                                                <div class="mt-2">
                                                    <span class="mr-2">{{ 'estado' }}</span>
                                                    @if($store->status=='pending')
                                                        <label class="badge badge-soft-primary">{{ 'Pendiente' }}</label>
                                                    @elseif($store->status=='completed')
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
                                                                    <strong>{{$store?->store?->name}}</strong>
                                                                </li>
                                                                <li class="d-flex flex-wrap">
                                                                    <span class="name">{{ 'contacto' }}</span>
                                                                    <span>:</span>
                                                                    <strong>{{$store?->store?->phone}}</strong>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="item">
                                                            <h5>{{ 'Información del propietario' }}</h5>
                                                            <ul class="item-list">
                                                                <li class="d-flex flex-wrap">
                                                                    <span class="name">{{ 'nombre' }}</span>
                                                                    <span>:</span>
                                                                    <strong>{{$store->store->vendor->f_name}} {{$store->store->vendor->l_name}}</strong>
                                                                </li>
                                                                <li class="d-flex flex-wrap">
                                                                    <span class="name">{{ 'correo electrónico' }}</span>
                                                                    <span>:</span>
                                                                    <strong>{{$store->store->vendor->email}}</strong>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="item w-100">
                                                            <h5>{{ 'Información de la cuenta' }}</h5>
                                                            <ul class="item-list">
                                                                <li class="d-flex flex-wrap">
                                                                    <span class="name">{{ 'método de pago' }}</span>
                                                                    <span>:</span>
                                                                    <strong>{{$store->withdraw_method->method_name}}</strong>
                                                                </li>
                                                                <li class="d-flex flex-wrap">
                                                                    <span class="name">{{ 'cantidad' }}</span>
                                                                    <span>:</span>
                                                                    <strong>{{\App\CentralLogics\Helpers::format_currency($store['disbursement_amount'])}}</strong>
                                                                </li>
                                                                @forelse(json_decode($store->withdraw_method->method_fields, true) as $key=> $item)
                                                                    <li class="d-flex flex-wrap">
                                                                        <span class="name">{{  translate($key) }}</span>
                                                                        <span>:</span>
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
        <div class="card-footer pt-0 border-0">
            {{$disbursements->links()}}
        </div>
    </div>

    <div class="modal fade" id="payment_model" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{'Pagar en línea'}}  </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>
                <form action="{{ route('vendor.wallet.make_payment') }}" method="POST" class="needs-validation">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" value="{{ \App\CentralLogics\Helpers::get_store_id() }}" name="store_id"/>
                        <input type="hidden" value="{{ abs($wallet->collected_cash) }}" name="amount"/>
                        <h5 class="mb-5 ">{{ 'Pagar en línea' }} &nbsp; <small>({{ 'Forma más rápida y segura de pagar la factura' }})</small></h5>
                        <div class="row g-3">
                            @forelse ($data as $item)
                                <div class="col-sm-6">
                                    <div class="d-flex gap-3 align-items-center">
                                        <input type="radio" required id="{{$item['gateway'] }}" name="payment_gateway" value="{{$item['gateway'] }}">
                                        <label for="{{$item['gateway'] }}" class="d-flex align-items-center gap-3 mb-0">
                                            <img height="24" src="{{ asset('storage/app/public/payment_modules/gateway_image/'. $item['gateway_image']) }}" alt="">
                                            {{ $item['gateway_title'] }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <h1>{{ 'no se encontró ninguna pasarela de pago' }}</h1>
                            @endforelse
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button id="reset_btn" type="reset" data-dismiss="modal" class="btn btn-secondary" >{{ 'Cerca' }} </button>
                        <button type="submit" class="btn btn-primary">{{ 'Proceder' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>


    <div class="modal fade" id="Adjust_wallet" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{'Ajustar billetera'}}  </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>
                <form action="{{ route('vendor.wallet.make_wallet_adjustment') }}" method="POST" class="needs-validation">
                    <div class="modal-body">
                        @csrf
                        <h5 class="mb-5 ">{{ 'Esto ajustará el efectivo recaudado en función de sus ganancias.' }} </h5>
                    </div>

                    <div class="modal-footer">
                        <button id="reset_btn" type="reset" data-dismiss="modal" class="btn btn-secondary" >{{ 'Cerca' }} </button>
                        <button type="submit" class="btn btn-primary">{{ 'Proceder' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/vendor/wallet-method.js"></script>
    <script>
        "use strict";
        $('#withdraw_method').on('change', function () {
    $('#submit_button').attr("disabled","true");
    let method_id = this.value;

    // Set header if need any otherwise remove setup part
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: "{{route('vendor.wallet.method-list')}}" + "?method_id=" + method_id,
        data: {},
        processData: false,
        contentType: false,
        type: 'get',
        success: function (response) {
            $('#submit_button').removeAttr('disabled');
            let method_fields = response.content.method_fields;
            $("#method-filed__div").html("");
            method_fields.forEach((element, index) => {
                $("#method-filed__div").append(`
                    <div class="form-group mt-2">
                        <label for="wr_num" class="fz-16 text-capitalize c1 mb-2">${element.input_name.replaceAll('_', ' ')}</label>
                        <input type="${element.input_type == 'phone' ? 'number' : element.input_type  }" class="form-control" name="${element.input_name}" placeholder="${element.placeholder}" ${element.is_required === 1 ? 'required' : ''}>
                    </div>
                `);
            })

        },
        error: function () {

        }
    });
});


$('.payment-warning').on('click',function (event ){
            event.preventDefault();
            toastr.info(
                "{{ 'Actualmente, no hay opciones de pago disponibles. Comuníquese con el administrador con respecto a cualquier proceso de pago o consulta.' }}", {
                    CloseButton: true,
                    ProgressBar: true
                });
        });
$(document).ready(function() {
    $("#withdraw_form").on("submit", function(event) {
        $('#set_disable').attr('disabled', true);

    });
});
    </script>
@endpush
