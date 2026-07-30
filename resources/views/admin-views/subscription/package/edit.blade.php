@extends('layouts.admin.app')

@section('title','Suscripción')

@section('subscription_index')
active
@endsection

@section('content')

    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center py-2">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="d-flex align-items-start">
                        <img src="{{asset('assets/admin/img/create-package-icon.png')}}" width="24" alt="img">
                        <div class="w-0 flex-grow pl-2">
                            <h1 class="page-header-title">{{'Paquete de suscripción'}}</h1>
                            <div class="page-header-text">{{ 'Actualizar paquetes de suscripciones para el modelo comercial de suscripción' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-20">
            <div class="card-header">
                <div class="w-100 d-flex flex-wrap align-items-start gap-2">
                    <img src="{{asset('assets/admin/img/material-symbols_featured-play-list.png')}}" width="18" alt="img" class="mt-1">
                    <div class="w-0 flex-grow">
                        <h5 class="text--title card-title">{{ 'Información del paquete' }}</h5>
                        <div class="fz-12px">{{ 'Proporcionar información del paquete de suscripciones' }}</div>
                    </div>
                </div>
            </div>

    <form action="{{ route('admin.business-settings.subscriptionackage.update',$subscriptionackage->id) }}" method="post">
        @csrf
        @method('put')

                <div class="card-body">
                        @if ($language)
                        <ul class="nav nav-tabs mb-3">
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
                        @endif

                    <div class="row g-3">

                        <div class="col-lg-4 col-sm-6 lang_form" id="default-form">
                            <div class="form-group mb-0">
                                <label class="form-label input-label"
                                for="name">{{ 'Nombre del paquete' }} ({{ 'Por defecto' }})</label>
                                <input type="text" name="package_name[]" class="form-control" id="name" maxlength="191"  value="{{ $subscriptionackage?->getRawOriginal('package_name') }}"
                                placeholder="{{ 'Nombre del paquete' }}"
                                >
                            <input type="hidden" name="lang[]" value="default">
                            </div>
                        </div>

                        @if($language)
                                @foreach($language as $key => $lang)

                                <?php
                                if(count($subscriptionackage['translations'])){
                                    $translate = [];
                                    foreach($subscriptionackage['translations'] as $t)
                                    {
                                        if($t->locale == $lang && $t->key=="package_name"){
                                            $translate[$lang]['package_name'] = $t->value;
                                        }
                                    }
                                }
                            ?>


                                <div class="col-lg-4 col-sm-6  d-none lang_form" id="{{$lang}}-form">
                                    <div class="form-group mb-0">
                                        <label class="form-label input-label"
                                        for="{{$lang}}_title">{{ 'Nombre del paquete' }} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="package_name[]" class="form-control" id="{{$lang}}_title" maxlength="191"  value="{{ $translate[$lang]['package_name']??'' }}"
                                        placeholder="{{ 'Nombre del paquete' }}"
                                        >
                                        <input type="hidden" name="lang[]" value="{{$lang}}">
                                    </div>
                                </div>
                                @endforeach
                        @endif


                        <div class="col-lg-4 col-sm-6">
                            <div class="form-group">
                                <label class="input-label">{{ 'Precio del paquete' }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                <input type="number" value="{{ $subscriptionackage->price }}" name="package_price" required  min="0.01" step="0.01" max="999999999" class="form-control" placeholder="{{ 'Ej: 300' }}">
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6">
                            <div class="form-group">
                                <label class="input-label">{{ 'Validez del paquete' }} {{ 'Días' }}</label>
                                <input type="number"   min="1" max="999999999"  value="{{ $subscriptionackage->validity }}"  required name="package_validity"  class="form-control" placeholder="{{ 'Ej: 365' }}">
                            </div>
                        </div>


                        <div class="col-lg-4 col-sm-6 lang_form default-form" >
                            <div class="form-group m-0">
                                <label class="form-label input-label   text-capitalize"
                                    for="package_info">{{ 'información del paquete' }}</label>
                                <textarea class="form-control" placeholder="{{ 'Ej: relación calidad-precio' }}"  name="text[]" id="package_info">{{ $subscriptionackage?->getRawOriginal('text')  }}</textarea>
                            </div>
                        </div>

                        @if($language)
                        @foreach($language as $lang)
                        <?php
                        if(count($subscriptionackage['translations'])){
                            $text = [];
                            foreach($subscriptionackage['translations'] as $t)
                            {
                                if($t->locale == $lang && $t->key=="text"){
                                    $text[$lang]['text'] = $t->value;
                                }
                            }
                        }
                    ?>
                        <div class="col-lg-4 col-sm-6 d-none lang_form" id="{{$lang}}-form1">
                            <div class="form-group m-0">
                                <label class="form-label input-label   text-capitalize"
                                    for="package_info">{{ 'información del paquete' }} ({{strtoupper($lang)}})</label>
                                <textarea class="form-control" name="text[]" placeholder="{{ 'Ej: relación calidad-precio' }}" id="package_info">{{ $text[$lang]['text']??''}}</textarea>
                            </div>
                        </div>
                        @endforeach
                        @endif

                    </div>
                </div>
            </div>
            <div class="card mb-20">
                <div class="card-header">
                    <div class="w-100 d-flex flex-wrap align-items-start gap-2">
                        <img src="{{asset('assets/admin/img/material-symbols_featured-play-list-2.png')}}" alt="img" class="mt-1">
                        <div class="w-0 flex-grow">
                            <h5 class="text--title card-title d-flex gap-3 flex-wrap mb-1">
                                <div>
                                    {{ 'Características disponibles del paquete' }}
                                </div>
                                <label class="form-group form-check form--check">
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                    <span class="form-check-label text-dark font-regular text-14">{{ 'Seleccionar todo' }}</span>
                                </label>
                            </h5>
                            <div class="fz-12px">{{ 'Marca la característica que deseas ofrecer en este paquete.' }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="check--item-wrapper check--item-wrapper-2 mt-0">
                       @if ($subscriptionackage->module_type !== 'rental')

                       <div class="check-item">
                           <label class="form-group form-check form--check">
                               <input type="checkbox" class="form-check-input package-available-feature"  {{ $subscriptionackage->pos == 1 ? 'checked' : '' }} name="pos_system" value="1">
                               <span class="form-check-label text-dark">{{ 'sistema pos' }}</span>
                           </label>
                       </div>
                       <div class="check-item">
                           <label class="form-group form-check form--check">
                               <input type="checkbox" class="form-check-input package-available-feature" {{ $subscriptionackage->self_delivery == 1 ? 'checked' : '' }}  name="self_delivery" value="1">
                               <span class="form-check-label text-dark">{{ 'autoentrega' }}</span>
                           </label>
                       </div>
                       @endif
                        <div class="check-item">
                            <label class="form-group form-check form--check">
                                <input type="checkbox" class="form-check-input package-available-feature" {{ ($subscriptionackage->tootli_direct ?? 0) == 1 ? 'checked' : '' }}  name="tootli_direct" value="1">
                                <span class="form-check-label text-dark">
                                    Tootli Direct
                                    <span class="badge badge-soft-primary ml-1" style="font-size: 10px;">Envíos a domicilio POS</span>
                                </span>
                            </label>
                        </div>
                        <div class="check-item">
                            <label class="form-group form-check form--check">
                                <input type="checkbox" class="form-check-input package-available-feature" {{ $subscriptionackage->mobile_app == 1 ? 'checked' : '' }}  name="mobile_app" value="1" >
                                <span class="form-check-label text-dark">{{ 'Aplicación móvil' }}</span>
                            </label>
                        </div>
                        <div class="check-item">
                            <label class="form-group form-check form--check">
                                <input type="checkbox" class="form-check-input package-available-feature" {{ $subscriptionackage->review == 1 ? 'checked' : '' }}  name="review" value="1" >
                                <span class="form-check-label text-dark">{{ 'revisar' }}</span>
                            </label>
                        </div>
                        <div class="check-item">
                            <label class="form-group form-check form--check">
                                <input type="checkbox" class="form-check-input package-available-feature" {{ $subscriptionackage->chat == 1 ? 'checked' : '' }}  name="chat" value="1" >
                                <span class="form-check-label text-dark">{{ 'charlar' }}</span>
                            </label>
                        </div>

                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="w-100 d-flex flex-wrap align-items-start gap-2">
                        <img src="{{asset('assets/admin/img/bx_category.png')}}" alt="img" class="mt-1">
                        <div class="w-0 flex-grow">
                            <h5 class="text--title card-title d-flex gap-3 flex-wrap mb-1">
                                <div>
                                    {{ 'Establecer límite' }}
                                </div>
                            </h5>
                            <div class="fz-12px">{{  $subscriptionackage->module_type == 'rental' && addon_published_status('Rental') ? 'Establecer límite máximo de viaje y vehículo para este paquete' :'Establecer límite máximo de pedidos y productos para este paquete' }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3">
                        <div class="__bg-F8F9FC-card p-0">
                            <div class="card-body">
                                <div class="limit-item-card">
                                    <div class="form-group mb-0">
                                        <label class="form-label text-capitalize">{{$subscriptionackage->module_type == 'rental' && addon_published_status('Rental') ? 'Límite máximo de viaje':'Límite máximo de pedido' }}</label>
                                        <div class="d-flex flex-wrap items-center gap-2">
                                            <div class="resturant-type-group p-0">
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input limit-input" type="radio" {{ $subscriptionackage->max_order == 'unlimited' ? 'checked' : '' }}  name="minimum_order_limit" >
                                                    <span class="form-check-label">
                                                        {{ 'Ilimitado' }} ({{ 'Por defecto' }})
                                                    </span>
                                                </label>
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input limit-input"  {{ $subscriptionackage->max_order != 'unlimited' ? 'checked' : '' }}  type="radio" name="minimum_order_limit" value="Use_Limit">
                                                    <span class="form-check-label">
                                                        {{ 'Límite de uso' }}
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="custom-limit-box">
                                                <input id="max_order" type="number" value="{{ $subscriptionackage->max_order == 'unlimited' ? null : $subscriptionackage->max_order }}" name="max_order" min="1" step="1" max="999999999" class="form-control max_required" placeholder="{{ 'Ej: 1000' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="__bg-F8F9FC-card p-0">
                            <div class="card-body">
                                <div class="limit-item-card">
                                    <div class="form-group mb-0">
                                        <label class="form-label text-capitalize">{{$subscriptionackage->module_type == 'rental' && addon_published_status('Rental') ?  'Límite máximo de vehículos':'Límite máximo de artículos' }}</label>
                                        <div class="d-flex flex-wrap items-center gap-2">
                                            <div class="resturant-type-group p-0">
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input limit-input" type="radio" {{ $subscriptionackage->max_product == 'unlimited' ? 'checked' : '' }} name="maximum_item_limit" >
                                                    <span class="form-check-label">
                                                        {{ 'Ilimitado' }} ({{ 'Por defecto' }})
                                                    </span>
                                                </label>
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input limit-input" {{ $subscriptionackage->max_product != 'unlimited' ? 'checked' : '' }}  type="radio" name="maximum_item_limit" value="Use_Limit" >
                                                    <span class="form-check-label">
                                                        {{ 'Límite de uso' }}
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="custom-limit-box">
                                                <input  id="max_product" type="number" value="{{ $subscriptionackage->max_product == 'unlimited' ? null : $subscriptionackage->max_product }}" name="max_product" min="1" step="1" max="999999999" class="form-control max_required" placeholder="{{ 'Ej: 1000' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn--container justify-content-end mt-20">
                <button type="reset" id="reset_btn" class="btn btn--reset">
                    {{ 'reiniciar' }}
                </button>
                <button type="submit" class="btn btn--primary">{{ 'entregar' }}</button>
            </div>

        </form>


    </div>




@endsection

@push('script_2')

<script>
"use strict";
    $('#select-all').on('change', function(){
        if($(this).is(':checked')){
            $('.package-available-feature').prop('checked', true);
        }else{
            $('.package-available-feature').prop('checked', false);
        }
    })
    $('.package-available-feature').on('change', function(){
        if($(this).is(':checked')){
            if($('.package-available-feature').length == $('.package-available-feature:checked').length){
                $('#select-all').prop('checked', true);
            }
        }else{
            $('#select-all').prop('checked', false);
        }
    }).trigger('change');

    $('.limit-input').on('change', function() {

        var closestLimitItemCard = $(this).closest('.limit-item-card');
        var isChecked = $(this).is(':checked');
        if (isChecked) {
            if ($(this).val() == 'Use_Limit') {
                closestLimitItemCard.find('.custom-limit-box').show();
                closestLimitItemCard.find('.max_required').prop('required', true);
            } else {
                closestLimitItemCard.find('.custom-limit-box').hide();
                closestLimitItemCard.find('.max_required').removeAttr('required');
            }
        }
    }).trigger('change');



    $(document).on("click", "#reset_btn", function () {
    setTimeout(reset, 10);
    });

    function reset(){
    $('.limit-input').trigger('change');
    }

</script>

@endpush

