@extends('layouts.admin.app')

@section('title','editar cupón')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/edit.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'actualización de cupón'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.coupon.update',[$coupon['id']])}}" method="post" class="custom-validation">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            @if($language)
                                <ul class="nav nav-tabs mb-4">
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
                                        <label class="input-label" for="default_title">{{'título'}} ({{'por defecto'}})</label>
                                        <input type="text" name="title[]" id="default_title" class="form-control" placeholder="{{'nuevo cupón'}}" value="{{$coupon?->getRawOriginal('title')}}"  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="default">
                                </div>
                                @foreach($language as $lang)
                                    <?php
                                        if(count($coupon['translations'])){
                                            $translate = [];
                                            foreach($coupon['translations'] as $t)
                                            {
                                                if($t->locale == $lang && $t->key=="title"){
                                                    $translate[$lang]['title'] = $t->value;
                                                }
                                            }
                                        }
                                    ?>
                                    <div class="d-none lang_form" id="{{$lang}}-form">
                                        <div class="form-group error-wrapper">
                                            <label class="input-label" for="{{$lang}}_title">{{'título'}} ({{strtoupper($lang)}})</label>
                                            <input type="text" name="title[]" id="{{$lang}}_title" class="form-control" placeholder="{{'nuevo cupón'}}" value="{{$translate[$lang]['title']??''}}"  required>
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{$lang}}">
                                    </div>
                                @endforeach
                            @else
                            <div id="default-form">
                                <div class="form-group error-wrapper">
                                    <label class="input-label" for="exampleFormControlInput1">{{'título'}} ({{ 'por defecto' }})</label>
                                    <input type="text" name="title[]" class="form-control" placeholder="{{'nuevo cupón'}}" value="{{$coupon['title']}}" maxlength="100">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            </div>
                            @endif
                        </div>
                        <div class="col-md-4 col-lg-3 col-sm-6">
                            <div class="form-group m-0 error-wrapper">
                                <label class="input-label" for="exampleFormControlInput1">{{'tipo de cupón'}}</label>
                                <select name="coupon_type" id="coupon_type" class="form-control" required>
                                    <option value="store_wise" {{$coupon['coupon_type']=='store_wise'?'selected':''}}>{{'tienda sabia'}}</option>
                                    <option value="zone_wise" {{$coupon['coupon_type']=='zone_wise'?'selected':''}}>{{'zona sabia'}}</option>
                                    <option value="free_delivery" {{$coupon['coupon_type']=='free_delivery'?'selected':''}}>{{'entrega gratuita'}}</option>
                                    <option value="first_order" {{$coupon['coupon_type']=='first_order'?'selected':''}}>{{'primer orden'}}</option>
                                    <option value="default" {{$coupon['coupon_type']=='default'?'selected':''}}>{{'por defecto'}}</option>
                                    <option value="dineout" {{$coupon['coupon_type']=='dineout'?'selected':''}}>{{'cenar fuera'}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3 col-sm-6" id="store_wise">
                            <div class="form-group m-0 error-wrapper">
                                    <label class="input-label" for="exampleFormControlSelect1">{{'Negocio'}}<span
                                            class="input-label-secondary"></span></label>
                                    <select name="store_ids[]" class="js-data-example-ajax form-control"  title="Select Restaurant">
                                    @if($coupon->coupon_type == 'store_wise' || $coupon->coupon_type == 'dineout')
                                    @php($store=\App\Models\Store::find(json_decode($coupon->data)[0]))
                                        @if($store)
                                        <option value="{{$store->id}}">{{$store->name}}</option>
                                        @endif
                                    @else
                                    <option selected>{{ 'Seleccionar tienda' }}</option>
                                    @endif
                                    </select>
                                </div>
                        </div>
                        <div class="col-md-4 col-lg-3 col-sm-6"  id="zone_wise">
                            <div class="form-group m-0 error-wrapper">
                                <label class="input-label" for="exampleFormControlInput1">{{'seleccionar zona'}}</label>
                                <select name="zone_ids[]" id="choice_zones"
                                    class="form-control multiple-select2"
                                    multiple="multiple" placeholder="{{'seleccionar zona'}}">
                                @foreach($zones as $zone)
                                    <option value="{{$zone->id}}" {{($coupon->coupon_type=='zone_wise'&&json_decode($coupon->data))?(in_array($zone->id, json_decode($coupon->data))?'selected':''):''}}>{{$zone->name}}</option>
                                @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-4 col-lg-3 col-sm-6 error-wrapper" id="customer_wise" style="display: {{$coupon['coupon_type'] =='zone_wise' || $coupon['coupon_type'] =='first_order' ?'none':'block'}}">
                            <label class="input-label" for="select_customer">{{'seleccionar cliente'}}</label>
                            <select name="customer_ids[]" id="select_customer"
                                class="form-control multiple-select2"
                                multiple="multiple" placeholder="{{'seleccionar cliente'}}">
                                <option value="all" {{in_array('all', json_decode($coupon->customer_id))?'selected':''}}>{{'todo'}} </option>
                                @foreach(\App\Models\User::get(['id','f_name','l_name']) as $user)
                                <option value="{{$user->id}}" {{in_array($user->id, json_decode($coupon->customer_id))?'selected':''}}>{{$user->f_name.' '.$user->l_name}}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-3 col-sm-6">
                            <div class="form-group m-0 error-wrapper">
                                <label class="input-label" for="exampleFormControlInput1">{{'código'}}</label>
                                <input type="text" name="code" class="form-control" value="{{$coupon['code']}}"
                                       placeholder="{{\Illuminate\Support\Str::random(8)}}" required maxlength="100">
                            </div>
                        </div>
                        <div id="limit_for_same_user" class="col-md-4 col-lg-3 col-sm-6">
                            <div class="form-group m-0 error-wrapper">
                                <label class="input-label" for="limit">{{'límite para el mismo usuario'}}</label>
                                <input type="number" name="limit" id="coupon_limit" data-value="{{$coupon['limit']}}" value="{{$coupon['limit']}}" class="form-control" max="100"
                                       placeholder="{{ 'EJ: 10' }}">
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3 col-sm-6">
                            <div class="form-group m-0 error-wrapper">
                                <label class="input-label" for="">{{'fecha de inicio'}}</label>
                                <input type="date" name="start_date" class="form-control" id="date_from" placeholder="{{'seleccionar fecha'}}" value="{{date('Y-m-d',strtotime($coupon['start_date']))}}">
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3 col-sm-6">
                            <div class="form-group m-0 error-wrapper">
                                <label class="input-label" for="date_to">{{'fecha de caducidad'}}</label>
                                <input type="date" name="expire_date" class="form-control" placeholder="{{'seleccionar fecha'}}" id="date_to" value="{{date('Y-m-d',strtotime($coupon['expire_date']))}}"
                                       data-hs-flatpickr-options='{
                                     "dateFormat": "Y-m-d"
                                   }'>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3 col-sm-6">
                            <div class="form-group m-0 error-wrapper">
                                <label class="input-label" for="discount_type">{{'tipo de descuento'}}</label>
                                <select name="discount_type" id="discount_type" class="form-control">
                                    <option value="amount" {{$coupon['discount_type']=='amount'?'selected':''}}>{{'cantidad'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                    </option>
                                    <option value="percent" {{$coupon['discount_type']=='percent'?'selected':''}}>
                                        {{'por ciento'}} (%)
                                    </option>
                                </select>
                            </div>
                        </div>
                            <div class="col-md-4 col-lg-3 col-sm-6">
                            <div class="form-group m-0 error-wrapper">
                                <label class="input-label" for="exampleFormControlInput1">{{'compra mínima'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                <input type="number" id="min_purchase" name="min_purchase" step="0.01" value="{{$coupon['min_purchase']}}"
                                       min="0" max="999999999999.99" class="form-control"
                                       placeholder="100">
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3 col-sm-6">
                            <div class="form-group m-0 error-wrapper">
                                <label class="input-label" for="discount">{{'descuento'}}
                                    <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Actualmente necesitas gestionar el descuento con el Restaurante.' }}">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </label>
                                <input type="number" id="discount" min="1" max="999999999999.99" step="0.01" value="{{$coupon['discount']}}"
                                       name="discount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-3 col-sm-6">
                            <div class="form-group m-0 error-wrapper">
                                <label class="input-label" for="exampleFormControlInput1">{{'descuento máximo'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                <input type="number" min="0" max="999999999999.99" step="0.01" value="{{$coupon['max_discount']}}" name="max_discount" id="max_discount" class="form-control" {{$coupon['discount_type']=='amount'?'readonly="readonly"':''}}>
                            </div>
                        </div>

                    </div>
                    <div class="btn--container justify-content-end mt-4">
                        <button type="reset" id="reset_btn" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="submit" class="btn btn--primary">{{'actualizar'}}</button>
                    </div>
                </form>
            </div>
            <!-- End Table -->
        </div>
    </div>
    <input type="hidden" id="min-purchase-toast" value="{{ 'El monto del descuento no puede ser mayor que el monto mínimo de compra' }}">

@endsection

@push('script_2')
<script src="{{asset('assets/admin')}}/js/view-pages/coupon-edit.js"></script>
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

        coupon_type_change('{{$coupon->coupon_type}}');

        $(document).on('ready', function () {
            let module_id = 0;
            $('#date_from').attr('max','{{date("Y-m-d",strtotime($coupon["expire_date"]))}}');
            $('#date_to').attr('min','{{date("Y-m-d",strtotime($coupon["start_date"]))}}');
            @if($coupon['discount_type']=='amount')
            $('#max_discount').attr("readonly","true");
            $('#max_discount').val(0);
            @endif


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
                        let $request = $.ajax(params);

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    }
                }
            });
            // INITIALIZATION OF FLATPICKR
            // =======================================================
            $('.js-flatpickr').each(function () {
                $.HSCore.components.HSFlatpickr.init($(this));
            });
        });



    </script>
@endpush
