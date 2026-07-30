@extends('layouts.admin.app')

@section('title','bonificaciones')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/add.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'configuración de bonificación de billetera'}}
                </span>
            </h1>
            <div class="text--primary-2 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                <strong class="mr-2">{{'¡Mira cómo funciona!'}}</strong>
                <div class="blinkings">
                    <i class="tio-info-outined"></i>
                </div>
            </div>
        </div>

        <!-- End Page Header -->
        <div class="row g-2">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.users.customer.wallet.bonus.store')}}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    @if ($language)
                                    <ul class="nav nav-tabs mb-3 border-0">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active"
                                            href="#"
                                            id="default-link">{{'por defecto'}}</a>
                                        </li>
                                        @foreach ($language as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link"
                                                    href="#"
                                                    id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="lang_form" id="default-form">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="default_title">{{ 'Título de bonificación' }}
                                                        ({{ 'Por defecto' }})   <span class="form-label-secondary text-danger"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Requerido.'}}"> *
                                                        </span>
                                                    </label>
                                                    <input type="text" name="title[]" id="default_title"
                                                        class="form-control" placeholder="{{ 'Ej: EID Dhamaka' }}"
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="default_description">{{ 'Breve descripción' }}
                                                        ({{ 'Por defecto' }}) <span class="form-label-secondary text-danger"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Requerido.'}}"> *
                                                        </span>
                                                    </label>
                                                    <input type="text" name="description[]" id="default_description"
                                                        class="form-control" placeholder="{{ 'Ej: EID Dhamaka' }}"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                        @foreach ($language as $lang)
                                            <div class="d-none lang_form"
                                                id="{{ $lang }}-form">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label class="input-label"
                                                                for="{{ $lang }}_title">{{ 'Título de bonificación' }}
                                                                ({{ strtoupper($lang) }})
                                                            </label>
                                                            <input type="text" name="title[]" id="{{ $lang }}_title"
                                                                class="form-control" placeholder="{{ 'Ej: EID Dhamaka' }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label class="input-label"
                                                                for="{{ $lang }}_description">{{ 'Breve descripción' }}
                                                                ({{ strtoupper($lang) }})
                                                            </label>
                                                            <input type="text" name="description[]" id="{{ $lang }}_description"
                                                                class="form-control" placeholder="{{ 'Ej: EID Dhamaka' }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                            </div>
                                        @endforeach
                                    @else
                                        <div id="default-form">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'Título de bonificación' }} ({{ 'por defecto' }})</label>
                                                <input type="text" name="title[]" class="form-control"
                                                placeholder="{{ 'Ej: EID Dhamaka' }}">
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{'Tipo de bonificación'}} <span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Requerido.'}}"> *
                                            </span></label>
                                        <select name="bonus_type" class="form-control" id="bonus_type" required>
                                            <option value="percentage">{{'porcentaje'}} (%)</option>
                                            <option value="amount">{{'cantidad'}} {{ \App\CentralLogics\Helpers::currency_symbol() }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{'Monto del bono'}}

                                            <span  class="d-none" id='cuttency_symbol'>({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            </span>
                                            <span id="percentage">(%)</span>

                                            <span
                                            class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Establezca el monto/porcentaje de bonificación que recibirá un cliente después de agregar dinero a su billetera.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                        <span class="form-label-secondary text-danger"
                                        data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ 'Requerido.'}}"> *
                                        </span>

                                        </label>
                                        <input type="number" step="0.01" min="1" max="999999999999.99"  placeholder="{{ 'Ej: 100' }}"  name="bonus_amount" id="bonus_amount" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{'Monto mínimo de dinero agregado'}}

                                                         ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            <span
                                            class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Establezca la cantidad mínima de dinero agregado para que un cliente sea elegible para el bono.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                        <span class="form-label-secondary text-danger"
                                        data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ 'Requerido.'}}"> *
                                        </span>
                                        </label>
                                        <input type="number" step="0.01" min="1" max="999999999999.99" placeholder="{{ 'Ej: 10' }}" name="minimum_add_amount" id="minimum_add_amount" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{'Bonificación máxima'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            <span
                                            class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Establezca el monto máximo de bonificación que un cliente puede recibir por agregar dinero a su billetera.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>

                                        </label>
                                        <input type="number" step="0.01" min="1" max="999999999999.99"  placeholder="{{ 'Ej: 1000' }}" name="maximum_bonus_amount" id="maximum_bonus_amount" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{'fecha de inicio'}}<span class="form-label-secondary text-danger"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Requerido.'}}"> *
                                                        </span></label>
                                        <input type="date" name="start_date" class="form-control" id="date_from" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{'fecha de caducidad'}}<span class="form-label-secondary text-danger"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Requerido.'}}"> *
                                                        </span></label>
                                        <input type="date" name="end_date" class="form-control" id="date_to" required>
                                    </div>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn" class="btn btn--reset">{{'reiniciar'}}</button>
                                <button type="submit" class="btn btn--primary">{{'entregar'}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">{{'lista de bonificación'}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$bonuses->total()}}</span></h5>
                            <form id="dataSearch" class="search-form min--270">
                            @csrf
                                <!-- Search -->
                                <div class="input-group input--group">
                                    <input id="datatableSearch" type="search" name="search" class="form-control" placeholder="{{ 'Ej: buscar por título extra' }}" aria-label="{{'buscar aquí'}}">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom" id="table-div">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                "order": [],
                                "orderCellsTop": true,

                                "entries": "#datatableEntries",
                                "isResponsive": false,
                                "isShowPaging": false,
                                "paging":false
                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th class="border-0">{{'SL'}}</th>
                                <th class="border-0">{{'título de bonificación'}}</th>
                                <th class="border-0">{{'información de bonificación'}}</th>
                                <th class="border-0">{{'monto del bono'}}</th>
                                <th class="border-0">{{'comenzó el'}}</th>
                                <th class="border-0">{{'caduca el'}}</th>
                                <th class="border-0">{{'estado'}}</th>
                                <th class="border-0 text-center">{{'acción'}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($bonuses as $key=>$bonus)
                                <tr>
                                    <td>{{$key+$bonuses->firstItem()}}</td>
                                    <td>
                                    <span class="d-block font-size-sm text-body">
                                    {{Str::limit($bonus['title'],25,'...')}}
                                    </span>
                                    </td>
                                    <td>{{ 'cantidad mínima agregada' }} -    {{\App\CentralLogics\Helpers::format_currency($bonus['minimum_add_amount'])}} <br>
                                        {{ 'bonificación máxima' }} - {{\App\CentralLogics\Helpers::format_currency($bonus['maximum_bonus_amount'])}}</td>
                                    <td>{{$bonus->bonus_type == 'amount'?\App\CentralLogics\Helpers::format_currency($bonus['bonus_amount']): $bonus['bonus_amount'].' (%)'}}</td>
                                    <td>{{ \Carbon\Carbon::parse($bonus->start_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($bonus->end_date)->format('d M Y') }}</td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="bonusCheckbox{{$bonus->id}}">
                                            <input type="checkbox" data-url="{{route('admin.users.customer.wallet.bonus.status',[$bonus['id'],$bonus->status?0:1])}}" class="toggle-switch-input redirect-url" id="bonusCheckbox{{$bonus->id}}" {{$bonus->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">

                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.users.customer.wallet.bonus.update',[$bonus['id']])}}" title="{{'bono de edición'}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="bonus-{{$bonus['id']}}" data-message="{{ '¿Quieres eliminar este bono?' }}" title="{{'eliminar bono'}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.users.customer.wallet.bonus.delete',[$bonus['id']])}}"
                                            method="post" id="bonus-{{$bonus['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        @if(count($bonuses) !== 0)
                        <hr>
                        @endif
                        <div class="page-area">
                            {!! $bonuses->links() !!}
                        </div>
                        @if(count($bonuses) === 0)
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
            <!-- End Table -->
        </div>
    </div>
    <div class="modal fade" id="how-it-works">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="single-item-slider owl-carousel">
                        <div class="item">
                            <div class="mb-20">
                                <div class="text-center">
                                    <img src="{{asset('assets/admin/img/image_127.png')}}" alt="" class="mb-20">
                                    <h5 class="modal-title">{{'¡El bono de billetera solo se aplica cuando un cliente agrega fondos a la billetera a través de una pasarela de pago externa!'}}</h5>
                                </div>
                                <ul>
                                    <li>
                                        {{ 'El cliente recibirá un monto adicional en su billetera además del monto que agregó desde otras pasarelas de pago. El monto del bono se deducirá de la billetera del administrador y se considerará un gasto administrativo.' }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="slide-counter"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/wallet-bonus-index.js"></script>
<script>
    "use strict";
    $(document).on('ready', function () {
        // INITIALIZATION OF DATATABLES
        // =======================================================
        let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'), {
            select: {
                style: 'multi',
                classMap: {
                    checkAll: '#datatableCheckAll',
                    counter: '#datatableCounter',
                    counterInfo: '#datatableCounterInfo'
                }
            },
            language: {
                zeroRecords: '<div class="text-center p-4">' +
                '<img class="w-7rem mb-3" src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">' +

                '</div>'
            }
        });
    });

    $('#dataSearch').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.users.customer.wallet.bonus.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#table-div').html(data.view);
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
