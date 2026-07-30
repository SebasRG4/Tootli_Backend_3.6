@extends('layouts.admin.app')

@section('title','cupones')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/add.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'Agregar nuevo cupón'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row g-2">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.coupon.store')}}" method="POST" class="">
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
                                        <div class="form-group error-wrapper">
                                            <label class="input-label"
                                                for="default_title">{{ 'título' }}
                                                (Default)
                                            </label>
                                            <input type="text" value="{{ old('title.0') }}" name="title[]" id="default_title" required
                                                class="form-control" placeholder="{{ 'nuevo cupón' }}" >
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                        @foreach ($language as $key => $lang)
                                            <div class="d-none lang_form"
                                                id="{{ $lang }}-form">
                                                <div class="form-group error-wrapper">
                                                    <label class="input-label"
                                                        for="{{ $lang }}_title">{{ 'título' }}
                                                        ({{ strtoupper($lang) }})
                                                    </label>
                                                    <input type="text" name="title[]"   value="{{ old('title.' . $key+1) }}" id="{{ $lang }}_title"
                                                        class="form-control" placeholder="{{ 'nuevo cupón' }}"
                                                         >
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                            </div>
                                        @endforeach
                                    @else
                                        <div id="default-form">
                                            <div class="form-group error-wrapper">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'título' }} ({{ 'por defecto' }})</label>
                                                <input type="text" name="title[]" class="form-control"
                                                    placeholder="{{ 'nuevo cupón' }}">
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-4 col-lg-3 col-sm-6">
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="exampleFormControlInput1">{{'tipo de cupón'}}</label>
                                        <select name="coupon_type" id="coupon_type" class="form-control" required>
                                            <option disabled selected>---{{'Seleccione tipo de cupón'}}---</option>
                                            <option value="store_wise">{{'tienda sabia'}}</option>
                                            <option value="zone_wise">{{'zona sabia'}}</option>
                                            <option value="free_delivery">{{'entrega gratuita'}}</option>
                                            <option value="first_order">{{'primer orden'}}</option>
                                            <option value="default">{{'por defecto'}}</option>
                                            <option value="dineout">{{'cenar fuera'}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-3 col-sm-6" id="store_wise">
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="exampleFormControlSelect1">{{'Negocio'}}<span
                                                class="input-label-secondary"></span></label>
                                        <select name="store_ids[]" id="store_id" class="js-data-example-ajax form-control" data-placeholder="{{'seleccionar tienda'}}" title="{{'seleccionar tienda'}}">
                                            <option disabled selected>---{{'seleccionar tienda'}}---</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-3 col-sm-6" id="zone_wise">
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="exampleFormControlInput1">{{'seleccionar zona'}}</label>
                                        <select name="zone_ids[]" id="choice_zones"
                                            class="form-control multiple-select2"
                                            multiple="multiple" data-placeholder="{{'seleccionar zona'}}">
                                        @foreach($zones as $zone)
                                            <option value="{{$zone->id}}">{{$zone->name}}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6 col-sm-6" id="customer_wise">

                                    <div class="form-group pickup-zone-tag error-wrapper">
                                        <label class="input-label" for="select_customer">{{'seleccionar cliente'}}</label>
                                        <select name="customer_ids[]" id="select_customer"
                                            class="form-control  multiple-select2" multiple="multiple" data-placeholder="{{'seleccionar cliente'}}">
                                            <option  value="all">{{'todo'}} </option>
                                            @foreach(\App\Models\User::withoutGlobalScopes()->get(['id','f_name','l_name']) as $user)
                                            <option class="select_customer_option" value="{{$user->id}}" {{ (isset($customer) && is_numeric($customer) && ($customer == $user->id))?'selected':'' }}>{{$user->f_name.' '.$user->l_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-3 col-sm-6">
                                    <div class="form-group error-wrapper">
                                        <div class="d-flex justify-content-between">
                                            <label class="input-label" for="exampleFormControlInput1">{{'código'}}</label>
                                            {{-- <label class="input-label generate-code" id="generate_code"><i class="tio-hand-draw"></i>{{'Generar código'}}</label> --}}
                                        </div>

                                        <input type="text" name="code" class="form-control" value="{{ old('code') }}"
                                            placeholder="{{\Illuminate\Support\Str::random(8)}}" required maxlength="100">
                                    </div>
                                </div>
                                <div id="limit_for_same_user" class="col-md-4 col-lg-3 col-sm-6">
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="exampleFormControlInput1">{{'límite para el mismo usuario'}}</label>
                                        <input type="number" name="limit" value="{{ old('limit') }}" id="coupon_limit" class="form-control" placeholder="EX: 10" min="1" max="100">
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-3 col-sm-6">
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="exampleFormControlInput1">{{'fecha de inicio'}}</label>
                                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control" id="date_from" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-3 col-sm-6">
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="exampleFormControlInput1">{{'fecha de caducidad'}}</label>
                                        <input type="date" name="expire_date" value="{{ old('expire_date') }}" class="form-control" id="date_to" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-3 col-sm-6">
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="exampleFormControlInput1">{{'tipo de descuento'}}</label>
                                        <select name="discount_type" class="form-control" id="discount_type" required>
                                            <option value="amount">{{'cantidad'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            </option>
                                            <option value="percent">{{'por ciento'}} (%)</option>
                                        </select>
                                    </div>
                                </div>
                                   <div class="col-md-4 col-lg-3 col-sm-6">
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="exampleFormControlInput1">{{'compra mínima'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                        <input type="number" step="0.01" id="min_purchase" value="{{ old('min_purchase') ?? 0 }}" name="min_purchase"   min="0" max="999999999999.99" class="form-control"
                                            placeholder="100">
                                    </div>
                                </div>

                                <div class="col-md-4 col-lg-3 col-sm-6">
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="exampleFormControlInput1">{{'descuento'}}
                                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ 'Actualmente necesitas gestionar el descuento con la Tienda.' }}">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </label>
                                        <input type="number" step="0.01" min="1" max="999999999999.99" value="{{ old('discount') }}" name="discount" id="discount" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-3 col-sm-6">
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="max_discount">{{'descuento máximo'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                        <input type="number" step="0.01" min="0" value="{{ old('max_discount')?? 0 }}"  max="999999999999.99" name="max_discount" id="max_discount" class="form-control" readonly>
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
                            <h5 class="card-title">{{'lista de cupones'}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$coupons->total()}}</span></h5>
                            <form class="search-form min--270">

                                <!-- Search -->
                                <div class="input-group input--group">
                                    <input id="datatableSearch" type="search" name="search" value="{{ request()?->search ?? null }}" class="form-control" placeholder="{{ 'Ej: Título o código del cupón' }}" aria-label="{{'buscar aquí'}}">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                            @if(request()->get('search'))
                            <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{'reiniciar'}}</button>
                            @endif


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
                                    <a id="export-excel" class="dropdown-item" href="
                                        {{ route('admin.coupon.coupon_export', ['type' => 'excel', request()->getQueryString()]) }}
                                        ">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                            alt="Image Description">
                                        {{ 'sobresalir' }}
                                    </a>
                                    <a id="export-csv" class="dropdown-item" href="
                                    {{ route('admin.coupon.coupon_export', ['type' => 'csv', request()->getQueryString()]) }}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ asset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                            alt="Image Description">
                                        .{{ 'csv' }}
                                    </a>
                                </div>
                            </div>
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
                                <th class="border-0">{{'título'}}</th>
                                <th class="border-0">{{'código'}}</th>
                                <th class="border-0">{{'tipo'}}</th>
                                <th class="border-0">{{'usos totales'}}</th>
                                <th class="border-0">{{'compra mínima'}}</th>
                                <th class="border-0">{{'descuento máximo'}}</th>
                                <th class="border-0">{{'descuento'}}</th>
                                <th class="border-0">{{'tipo de descuento'}}</th>
                                <th class="border-0">{{'fecha de inicio'}}</th>
                                <th class="border-0">{{'fecha de caducidad'}}</th>
                                <th class="border-0">{{'estado'}}</th>
                                <th class="border-0 text-center">{{'acción'}}</th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($coupons as $key=>$coupon)
                                <tr>
                                    <td>{{$key+$coupons->firstItem()}}</td>
                                    <td>
                                    <span title="{{ $coupon['title'] }}" class="d-block font-size-sm text-body">
                                    {{Str::limit($coupon['title'],15,'...')}}
                                    </span>
                                    </td>
                                    <td>{{$coupon['code']}}</td>

                                    <td>{{translate('messages.'.$coupon->coupon_type)}}</td>
                                    <td>{{$coupon->total_uses}}</td>
                                    <td>{{\App\CentralLogics\Helpers::format_currency($coupon['min_purchase'])}}</td>
                                    <td>{{\App\CentralLogics\Helpers::format_currency($coupon['max_discount'])}}</td>
                                    <td>{{$coupon['discount']}}</td>
                                    <td>{{translate($coupon['discount_type'])}} {{ $coupon['discount_type'] == 'amount' ? (\App\CentralLogics\Helpers::currency_symbol())  : ( $coupon['discount_type'] == 'percent' ? ("%") : '')}}</td>
                                    <td>{{ \App\CentralLogics\Helpers::date_format($coupon['start_date']) }}</td>
                                    <td>{{\App\CentralLogics\Helpers::date_format($coupon['expire_date'])}}</td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="couponCheckbox{{$coupon->id}}">
                                            <input type="checkbox" data-url="{{route('admin.coupon.status',[$coupon['id'],$coupon->status?0:1])}}" class="toggle-switch-input redirect-url" id="couponCheckbox{{$coupon->id}}" {{$coupon->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="ml-2 btn btn-sm btn--warning btn-outline-warning action-btn data-info-show" href="#0" data-toggle="modal" data-target="#coupon_btn"
                                            data-id="{{$coupon['id']}}"
                                            data-url="{{route('admin.coupon.viewCoupon',[$coupon['id']])}}"
                                            >
                                                <i class="tio-invisible"></i>
                                            </a>
                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.coupon.update',[$coupon['id']])}}"title="{{'editar cupón'}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="coupon-{{$coupon['id']}}" data-message="{{ '¿Quieres eliminar este cupón?' }}" title="{{'eliminar cupón'}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.coupon.delete',[$coupon['id']])}}"
                                            method="post" id="coupon-{{$coupon['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                        @if(count($coupons) !== 0)
                        <hr>
                        @endif
                        <div class="page-area">
                            {!! $coupons->links() !!}
                        </div>
                        @if(count($coupons) === 0)
                        <div class="empty--data">
                            <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                            <h5>
                                {{'no se encontraron datos'}}
                            </h5>
                        </div>
                        @endif
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>

<!-- Coupon Details Modal -->
<div class="modal shedule-modal fade" id="coupon_btn" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content pb-1">
      <div class="d-flex align-items-center justify-content-between gap-2 py-3 px-3">
        <p class="m-0 d-xl-block d-none"></p>
        <div class="text-center">
            <h3 class="title-clr mb-0">{{ 'Detalles del cupón' }}</h3>
        </div>
        <button type="button" class="close bg-light w-30px h-30 rounded-circle" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div id="data-view">

      </div>

    </div>
  </div>
</div>
    <input type="hidden" id="min-purchase-toast" value="{{ 'El monto del descuento no puede ser mayor que el monto mínimo de compra' }}">

@endsection

@push('script_2')
<script src="{{asset('assets/admin')}}/js/view-pages/coupon-index.js"></script>
<script>
    "use strict";

        function coupon_type_change(coupon_type) {
            $('#zone_wise, #store_wise, #customer_wise').hide();
            $('#coupon_limit').attr("readonly", false);
            $('#limit_for_same_user').removeClass('d-none');
            switch (coupon_type) {
                case 'zone_wise':
                    $('#zone_wise').show();
                    break;

                case 'store_wise':
                    $('#store_wise').show();
                    $('#customer_wise').show();
                    break;
                
                case 'dineout':
                    $('#store_wise').show();
                    $('#customer_wise').show();
                    break;

                case 'first_order':
                    $('#coupon_limit').val(1).attr("readonly", true);
                    $('#limit_for_same_user').addClass('d-none');
                    break;

                default:
                    $('#customer_wise').show();
                    $('#coupon_limit').val($('#coupon_limit').data('value')).attr("readonly", false);
                    $('#limit_for_same_user').removeClass('d-none');
                    break;
            }

            if (coupon_type === 'free_delivery') {
                $('#discount_type').attr("disabled", true).val("").trigger("change");
                $('#max_discount, #discount').val(0).attr("readonly", true);
            } else {
                $('#discount_type').removeAttr("disabled").attr("required", true);
                $('#max_discount, #discount').removeAttr("readonly");
            }

            if ($('#discount_type').val() === 'amount') {
                $('#max_discount').val(0).attr("readonly", true);
            } else if($('#discount_type').val() === 'percent') {
                $('#max_discount').removeAttr("readonly");
            }
        }

$(document).on('click', '.copy-to-clipboard', function () {
    copyToClipboardById($(this).data('id'));
});

function copyToClipboardById(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        navigator.clipboard.writeText(element.value)
            .then(() => {
                toastr.success('Copied to clipboard!');
            })
            .catch(() => {
                toastr.error('Failed to copy!');
            });
    } else {
        toastr.warning('Element not found.');
    }
}
    $(document).on('ready', function () {

        let module_id = {{Config::get('module.current_module_id')}};

        $('.js-data-example-ajax').select2({
            ajax: {
                url: '{{url('/')}}/admin/store/get-stores',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        module_id: module_id
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

    });
    $('#select_customer').on('change', function () {
        let customer = $(this).val();
        if (Array.isArray(customer) && customer.includes("all")) {
            $('.select_customer_option').prop('disabled', true);
            customer = ["all"];
            $(this).val(customer);
        } else {
            $('.select_customer_option').prop('disabled', false);
        }
    });

      $(document).on('click', '.data-info-show', function() {
            let id = $(this).data('id');
            let url = $(this).data('url');
            $('#content-disable').addClass('disabled');
            fetch_data(id, url)
        })



        function fetch_data(id, url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function() {
                    $('#data-view').empty();
                    $('#loading').show()
                },
                success: function(data) {
                    $("#data-view").append(data.view);
                },
                complete: function() {
                    $('#loading').hide()
                }
            })
        }


    </script>
@endpush
