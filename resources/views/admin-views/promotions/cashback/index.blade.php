@extends('layouts.admin.app')

@section('title','Oferta de reembolso')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/Create_Cashback_Offer.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'Crear oferta de reembolso'}}
                </span>
            </h1>
            {{-- <div class="text--primary-2 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                <strong class="mr-2">{{'¡Mira cómo funciona!'}}</strong>
                <div class="blinkings">
                    <i class="tio-info-outined"></i>
                </div>
            </div> --}}
        </div>

        <!-- End Page Header -->
        <div class="row g-2">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body" id="form_data">
                        <form id="cashback-submit" action="{{route('admin.users.cashback.store')}}" method="POST">
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
                                </div>

                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="lang_form" id="default-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="default_title">{{ 'título' }}
                                                ({{ 'Por defecto' }})
                                                <span class="form-label-secondary text-danger"
                                                      data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ 'Requerido.'}}"> *
                                            </span>
                                            </label>
                                            <input required type="text" value="{{ old('title.0') }}" name="title[]" maxlength="254" id="default_title"
                                                class="form-control" placeholder="{{ 'Eid Dhamaka' }}" >
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                        @foreach ($language as $key => $lang)
                                            <div class="d-none lang_form"
                                                id="{{ $lang }}-form">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="{{ $lang }}_title">{{ 'título' }}
                                                        ({{ strtoupper($lang) }})
                                                    </label>
                                                    <input type="text" name="title[]" maxlength="254"  value="{{ old('title.'.$key+1) }}" id="{{ $lang }}_title"
                                                        class="form-control" placeholder="{{ 'Eid Dhamaka' }}"
                                                         >
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                            </div>
                                        @endforeach
                                    @else
                                        <div id="default-form">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'título' }} ({{ 'por defecto' }})</label>
                                                <input type="text" name="title[]" maxlength="254" class="form-control"
                                                    placeholder="{{ 'Eid Dhamaka' }}">
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-4 col-lg-4 col-sm-6" id="customer_wise">
                                    <div class="form-group">
                                        <label class="input-label" for="select_customer">{{'seleccionar cliente'}}
                                            <span class="form-label-secondary text-danger"
                                                  data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ 'Requerido.'}}"> *
                                            </span></label>
                                        <select required name="customer_id[]" id="select_customer"
                                            class="form-control js-select2-custom"
                                            multiple="multiple" data-placeholder="{{'seleccionar cliente'}}">
                                            <option   value="all">{{'todo'}} </option>
                                        @foreach(\App\Models\User::get(['id','f_name','l_name']) as $user)
                                            <option class="select_customer_option" value="{{$user->id}}" {{ (isset($customer) && is_numeric($customer) && ($customer == $user->id))?'selected':'' }}>{{$user->f_name.' '.$user->l_name}}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>



                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{'Tipo de reembolso'}} <span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Requerido.'}}"> *
                                            </span></label>
                                        <select name="cashback_type" class="form-control" id="cashback_type" required>
                                            <option {{ old('cashback_type')  == 'percentage' ? "selected": '' }} value="percentage">{{'porcentaje'}} (%)</option>
                                            <option {{ old('cashback_type')  == 'amount' ? "selected": '' }}  value="amount">{{'cantidad'}} {{ \App\CentralLogics\Helpers::currency_symbol() }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{'Monto del reembolso'}}

                                            <span class="{{ old('cashback_type')  == 'percentage' ||  old('cashback_type') == null  ? '': 'd-none' }} " id="percentage">(%)</span>
                                            <span  class=" {{ old('cashback_type')  == 'amount' && old('cashback_type') !== null ? '': 'd-none' }} " id='cuttency_symbol'>({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            </span>

                                            <span
                                            class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Establezca el valor del porcentaje/monto del reembolso que se transferirá a la billetera del cliente cuando se complete el pedido.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                        <span class="form-label-secondary text-danger"
                                        data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ 'Requerido.'}}"> *
                                        </span>

                                        </label>
                                        <input type="number" value="{{  old('cashback_amount') }}" step="0.01" min="1" max="100"  placeholder="{{ 'Ej: 100' }}"  name="cashback_amount" id="Cash_back_amount" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{'Compra Mínima'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            <span class="form-label-secondary text-danger"
                                                  data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ 'Requerido.'}}"> *
                                            </span></label>
                                        <input type="number" step="0.01" id="min_purchase" value="{{  old('min_purchase') }}" required name="min_purchase" value="0" min="0" max="999999999999.99" class="form-control"
                                             placeholder="{{ 'Ej: 100' }}">
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="max_discount">{{'Reembolso máximo'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                        <input type="number"   placeholder="{{ 'Ej: 100' }}" step="0.01" min="0" value="{{  old('cashback_type')  == 'percentage' ?  old('max_discount') : null }}" max="999999999999.99" name="max_discount" id="max_discount" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{'Fecha de inicio'}}
                                            <span class="form-label-secondary text-danger"
                                                  data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ 'Requerido.'}}"> *
                                            </span></label>
                                        <input type="date" name="start_date" value="{{  old('start_date') }}" class="form-control" id="date_from" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{'Fecha de finalización'}}
                                            <span class="form-label-secondary text-danger"
                                                  data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ 'Requerido.'}}"> *
                                            </span></label>
                                        <input type="date" name="end_date"  value="{{  old('end_date') }}" class="form-control" id="date_to" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{'Límite para el mismo usuario'}}
                                            <span class="form-label-secondary text-danger"
                                                  data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ 'Requerido.'}}"> *
                                            </span></label>
                                        <input type="number" step="1" required  value="{{  old('same_user_limit') }}" name="same_user_limit" value="0" min="0" max="9999999" class="form-control"
                                             placeholder="{{ 'Ej: 5' }}">
                                    </div>
                                </div>

                            </div>
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn" class="btn btn--reset">{{'reiniciar'}}</button>
                                <button type="submit" class="btn btn--primary cashback-submit">{{'entregar'}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">{{'Lista de reembolsos'}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$cashbacks->total()}}</span></h5>
                            <form  class="search-form min--270">
                                <!-- Search -->
                                <div class="input-group input--group">
                                    <input id="datatableSearch" type="search" name="search" value="{{ request()?->search }}" class="form-control" placeholder="{{ 'Ej: buscar por título' }}" aria-label="{{'buscar aquí'}}">
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
                                <th class="border-0">{{'Nombre'}}</th>
                                <th class="border-0">{{'Tipo de reembolso'}}</th>
                                <th class="border-0">{{'Cantidad'}}</th>
                                <th class="border-0">{{'Duración'}}</th>
                                <th class="border-0 text-center">{{'Total usado'}}</th>
                                <th class="border-0">{{'estado'}}</th>
                                <th class="border-0 text-center">{{'acción'}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($cashbacks as $key=>$bonus)
                                <tr>
                                    <td>{{$key+$cashbacks->firstItem()}}</td>
                                    <td>
                                    <span class="d-block font-size-sm text-body" title="{{ $bonus['title'] }}">
                                    {{Str::limit($bonus['title'],25,'...')}}
                                    </span>
                                    </td>


                                    <td>{{ translate($bonus['cashback_type']) }}</td>
                                    <td> {{  $bonus['cashback_type'] == 'amount' ? \App\CentralLogics\Helpers::format_currency($bonus['cashback_amount']) : $bonus['cashback_amount'] .' %' }}</td>
                                    <td> {{\App\CentralLogics\Helpers::date_format($bonus->start_date)}} -  {{\App\CentralLogics\Helpers::date_format($bonus->end_date)  }}</td>

                                    <td class="text-center">{{ $bonus['total_used']  }}</td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="bonusCheckbox{{$bonus->id}}">
                                            <input type="checkbox" data-url="{{route('admin.users.cashback.status',[$bonus['id'],$bonus->status?0:1])}}" class="toggle-switch-input redirect-url" id="bonusCheckbox{{$bonus->id}}" {{$bonus->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">

                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.users.cashback.update',[$bonus['id']])}}" title="{{'editar reembolso'}}"><i class="tio-edit"></i>
                                            </a>
                                            {{-- <a class="btn action-btn btn--primary btn-outline-primary edit_cashback" data-id="{{$bonus['id']}}"  href="javascript:;" title="{{'editar reembolso'}}"><i class="tio-edit"></i>
                                            </a> --}}
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="bonus-{{$bonus['id']}}" data-message="{{ '¿Quieres eliminar este Cashback?' }}" title="{{'eliminar bono'}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.users.cashback.delete',[$bonus['id']])}}"
                                            method="post" id="bonus-{{$bonus['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        @if(count($cashbacks) !== 0)
                        <hr>
                        @endif
                        <div class="page-area">
                            {!! $cashbacks->links() !!}
                        </div>
                        @if(count($cashbacks) === 0)
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
<script src="{{asset('assets/admin')}}/js/view-pages/cashback-index.js"></script>
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

//     $('.edit_cashback').on('click', function (e) {

//     let url = "{{ route('admin.users.cashback.update', ['id']) }}";
//         url = url.replace('id', $(this).data("id"));
//     e.preventDefault();
//     $.ajaxSetup({
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         }
//     });
//     $.ajax({
//         type: "GET",
//         url: url,
//         cache: false,
//         beforeSend: function () {
//             $('#loading').show();
//         },
//         success: function (data) {
//             $('#form_data').html(data.view);
//         },
//         complete: function () {
//             $('#loading').hide();
//         },
//         error: function(xhr, textStatus, errorThrown) {
//             console.error("Error:", textStatus, errorThrown);
//         }
//     });
// });

</script>
@endpush
