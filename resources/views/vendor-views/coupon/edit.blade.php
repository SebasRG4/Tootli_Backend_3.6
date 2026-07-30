@extends('layouts.vendor.app')

@section('title','Cupón de actualización')

@section('content')
@php($store_data = \App\CentralLogics\Helpers::get_store_data())

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-edit"></i> {{'actualización de cupón'}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="{{route('vendor.coupon.update',[$coupon['id']])}}" method="post" class="custom-validation">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                                    @php($language = $language->value ?? null)
                                    @php($defaultLang = str_replace('_', '-', app()->getLocale()))
                                    @if($language)
                                        <ul class="nav nav-tabs mb-4">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active"
                                                href="#"
                                                id="default-link">{{'por defecto'}}</a>
                                            </li>
                                            @foreach (json_decode($language) as $lang)
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
                                                <input type="text" name="title[]" id="default_title" class="form-control" placeholder="{{'nuevo cupón'}}" value="{{$coupon?->getRawOriginal('title')}}" required>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                        @foreach(json_decode($language) as $lang)
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
                                                    <input type="text" name="title[]" id="{{$lang}}_title" class="form-control" placeholder="{{'nuevo cupón'}}" value="{{$translate[$lang]['title']??''}}"  >
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                            </div>
                                        @endforeach
                                    @else
                                    <div id="default-form">
                                        <div class="form-group error-wrapper">
                                            <label class="input-label" for="title">{{'título'}} ({{ 'por defecto' }})</label>
                                            <input type="text" name="title[]" id="title" class="form-control" placeholder="{{'nuevo cupón'}}" value="{{$coupon['title']}}" maxlength="100" required>
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                    @endif
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group error-wrapper">
                                <label class="input-label" for="coupon_type">{{'tipo de cupón'}}</label>
                                <select id="coupon_type" name="coupon_type" class="form-control" >
                                    @if ($store_data->sub_self_delivery == 1)
                                    <option value="free_delivery" {{$coupon['coupon_type']=='free_delivery'?'selected':''}}>{{'entrega gratuita'}}</option>
                                    @endif
                                    <option value="default" {{$coupon['coupon_type']=='default'?'selected':''}}>{{'por defecto'}}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group error-wrapper">
                                <label class="input-label" for="coupon_code">{{'código'}}</label>
                                <input id="coupon_code" type="text" name="code" class="form-control" value="{{$coupon['code']}}"
                                        placeholder="{{\Illuminate\Support\Str::random(8)}}" required maxlength="100">
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group error-wrapper">
                                <label class="input-label" for="coupon_limit">{{'límite para el mismo usuario'}}</label>
                                <input type="number" name="limit" id="coupon_limit" value="{{$coupon['limit']}}" class="form-control" max="100"
                                        placeholder="{{ 'Ex :' }} 10">
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group error-wrapper">
                                <label class="input-label" for="date_from">{{'fecha de inicio'}}</label>
                                <input type="date" name="start_date" class="form-control" id="date_from" placeholder="{{'seleccionar fecha'}}" value="{{date('Y-m-d',strtotime($coupon['start_date']))}}">
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group error-wrapper">
                                <label class="input-label" for="date_to">{{'fecha de caducidad'}}</label>
                                <input type="date" name="expire_date" class="form-control" placeholder="{{'seleccionar fecha'}}" id="date_to" value="{{date('Y-m-d',strtotime($coupon['expire_date']))}}"
                                        data-hs-flatpickr-options='{
                                        "dateFormat": "Y-m-d"
                                    }'>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group error-wrapper">
                                <label class="input-label" for="discount_type">{{'tipo de descuento'}}</label>
                                <select name="discount_type" id="discount_type" class="form-control" {{$coupon['coupon_type']=='free_delivery'?'disabled':''}}>
                                    <option value="amount" {{$coupon['discount_type']=='amount'?'selected':''}}>
                                        {{ 'cantidad'.' ('.\App\CentralLogics\Helpers::currency_symbol().')'  }}
                                    </option>
                                    <option value="percent" {{$coupon['discount_type']=='percent'?'selected':''}}>
                                        {{ 'por ciento'.' (%)' }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group error-wrapper">
                                <label class="input-label" for="discount">{{'descuento'}} </label>
                                <input type="number" id="discount" min="1" max="999999999999.99" step="0.01" value="{{$coupon['discount']}}"
                                        name="discount" class="form-control" required {{$coupon['coupon_type']=='free_delivery'?'readonly':''}}>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group error-wrapper">
                                <label class="input-label" for="max_discount">{{'descuento máximo'}}</label>
                                <input type="number" min="0" max="999999999999.99" step="0.01"
                                        value="{{$coupon['max_discount']}}" name="max_discount" id="max_discount" class="form-control" {{$coupon['coupon_type']=='free_delivery'?'readonly':''}}>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group error-wrapper">
                                <label class="input-label" for="min_purchase">{{'compra mínima'}}</label>
                                <input id="min_purchase" type="number" name="min_purchase" step="0.01" value="{{$coupon['min_purchase']}}"
                                        min="0" max="999999999999.99" class="form-control"
                                        placeholder="100">
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button id="reset_btn" type="button" class="btn btn--reset location-reload" >{{'reiniciar'}}</button>
                        <button type="submit" class="btn btn--primary">{{'actualizar'}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('assets/admin/js/view-pages/vendor-coupon.js')}}"></script>
    <script>
        "use strict";
        $(document).on('ready', function () {
            $('#date_from').attr('max','{{date("Y-m-d",strtotime($coupon["expire_date"]))}}');
            $('#date_to').attr('min','{{date("Y-m-d",strtotime($coupon["start_date"]))}}');
        });
    </script>


@endpush
