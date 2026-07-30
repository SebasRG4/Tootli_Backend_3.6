@extends('layouts.admin.app')

@section('title','Método de pago')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        @php
        $currency= \App\Models\BusinessSetting::where('key','currency')->first()?->value?? 'USD';
        $checkCurrency = \App\CentralLogics\Helpers::checkCurrency($currency);
        $currency_symbol =\App\CentralLogics\Helpers::currency_symbol();

    @endphp

        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/payment.png')}}" class="w--22" alt="">
                </span>
                <span>
                    {{'configuración de pasarela de pago'}}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.third-party-links')
            <div class="d-flex flex-wrap justify-content-end align-items-center flex-grow-1">
                <div class="blinkings trx_top active">
                    <i class="tio-info-outined"></i>
                    <div class="business-notes">
                        <h6><img src="{{asset('assets/admin/img/notes.png')}}" alt=""> {{'Nota'}}</h6>
                        <div>
                            {{'Sin configurar esta sección, la funcionalidad no funcionará correctamente. Por lo tanto, todo el sistema no funcionará como estaba planeado.'}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="card border-0">
            <div class="card-header card-header-shadow">
                <h5 class="card-title align-items-center">
                    <img src="{{asset('assets/admin/img/payment-method.png')}}" class="mr-1" alt="">
                    {{'Método de pago'}}
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        @php($config=\App\CentralLogics\Helpers::get_business_settings('cash_on_delivery'))
                        <form action="{{route('admin.business-settings.third-party.payment-method-update',['cash_on_delivery'])}}"
                              method="post" id="cash_on_delivery_status_form">
                            @csrf
                            <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    <span class="line--limit-1">
                                        {{'Contra reembolso'}}
                                    </span>
                                    <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip" data-placement="right" data-original-title="{{'Si está habilitado, los clientes podrán seleccionar COD como método de pago durante el pago.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="Veg/non-veg toggle"> * </span>
                                </span>
                                <input type="hidden" name="toggle_type" value="cash_on_delivery">
                                <input
                                    type="checkbox" id="cash_on_delivery_status"
                                    data-id="cash_on_delivery_status"
                                    data-type="status"
                                    data-image-on="{{ asset('assets/admin/img/modal/digital-payment-on.png') }}"
                                    data-image-off="{{ asset('assets/admin/img/modal/digital-payment-off.png') }}"
                                    data-title-on="{{ 'Activando la opción Contra reembolso' }}"
                                    data-title-off="{{ 'Desactivando la opción de pago contra reembolso' }}"
                                    data-text-on="<p>{{ 'Los clientes no podrán seleccionar COD como método de pago durante el proceso de pago. Revise su configuración y habilite COD si desea ofrecer esta opción de pago a los clientes.' }}</p>"
                                    data-text-off="<p>{{ 'Los clientes podrán seleccionar COD como método de pago durante el proceso de pago.' }}</p>"
                                    class="status toggle-switch-input dynamic-checkbox"
                                    name="status" value="1" {{$config?($config['status']==1?'checked':''):''}}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </form>
                    </div>
                    <div class="col-md-4">
                        @php($digital_payment=\App\CentralLogics\Helpers::get_business_settings('digital_payment'))
                        <form action="{{route('admin.business-settings.third-party.payment-method-update',['digital_payment'])}}"
                              method="post" id="digital_payment_status_form">
                            @csrf
                            <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    <span class="line--limit-1">
                                        {{'pago digital'}}
                                    </span>
                                    <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip" data-placement="right" data-original-title="{{'Si está habilitado, los clientes podrán seleccionar el pago digital como método de pago durante el pago.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="Veg/non-veg toggle"> * </span>
                                </span>
                                <input type="hidden" name="toggle_type" value="digital_payment">
                                <input  type="checkbox" id="digital_payment_status"
                                        data-id="digital_payment_status"
                                        data-type="status"
                                        data-image-on="{{ asset('assets/admin/img/modal/digital-payment-on.png') }}"
                                        data-image-off="{{ asset('assets/admin/img/modal/digital-payment-off.png') }}"
                                        data-title-on="{{ 'Activando la opción de pago digital' }}"
                                        data-title-off="{{ 'Desactivando la opción de pago digital' }}"
                                        data-text-on="<p>{{ 'Los clientes no podrán seleccionar el pago digital como método de pago durante el proceso de pago. Revise su configuración y habilite el pago digital si desea ofrecer esta opción de pago a los clientes.' }}</p>"
                                        data-text-off="<p>{{ 'Los clientes podrán seleccionar el pago digital como método de pago durante el proceso de pago.' }}</p>"
                                        class="status toggle-switch-input dynamic-checkbox"
                                        name="status" value="1" {{$digital_payment?($digital_payment['status']==1?'checked':''):''}}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </form>
                    </div>
                    <div class="col-md-4">
                        @php($Offline_Payment=\App\CentralLogics\Helpers::get_business_settings('offline_payment_status'))
                        <form action="{{route('admin.business-settings.third-party.payment-method-update',['offline_payment_status'])}}"
                              method="post" id="offline_payment_status_form">
                            @csrf
                            <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    <span class="line--limit-1">
                                        {{'Pago sin conexión'}}
                                    </span>
                                    <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip" data-placement="right" data-original-title="{{'Si está habilitado, los clientes podrán seleccionar el pago fuera de línea como método de pago durante el proceso de pago.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="Veg/non-veg toggle"> * </span>
                                </span>
                                <input type="hidden" name="toggle_type" value="offline_payment_status" >
                                <input  type="checkbox" id="offline_payment_status"
                                        data-id="offline_payment_status"
                                        data-type="status"
                                        data-image-on="{{ asset('assets/admin/img/modal/digital-payment-on.png') }}"
                                        data-image-off="{{ asset('assets/admin/img/modal/digital-payment-off.png') }}"
                                        data-title-on="{{ 'Activando la opción de pago sin conexión' }}"
                                        data-title-off="{{ 'Desactivando la opción de pago sin conexión' }}"
                                        data-text-on="<p>{{ 'Los clientes no podrán seleccionar Pago sin conexión como método de pago durante el proceso de pago. Revise su configuración y habilite el pago sin conexión si desea ofrecer esta opción de pago a los clientes.' }}</p>"
                                        data-text-off="<p>{{ 'Los clientes podrán seleccionar Pago sin conexión como método de pago durante el proceso de pago.' }}</p>"
                                        class="status toggle-switch-input dynamic-checkbox"

                                        name="status" value="1" {{$Offline_Payment == 1?'checked':''}}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @if($published_status == 1)
            <br>
            <div>
                <div class="card">
                    <div class="card-body d-flex flex-wrap justify-content-around">
                        <h4 class="w-50 flex-grow-1 module-warning-text">
                            <i class="tio-info-outined"></i>
                            {{ 'Su configuración de pago actual está deshabilitada porque ha habilitado el complemento de la pasarela de pago. Para visitar la configuración de su pasarela de pago actualmente activa, siga el enlace.' }}</h4>
                        <div>
                            <a href="{{!empty($payment_url) ? $payment_url : ''}}" class="btn btn-outline-primary"> <i class="tio-settings"></i> {{'Configuración'}}</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($digital_payment && $digital_payment['status'] ==1 && $checkCurrency !== true )
        <br>
        <div>
            <div class="card">
                <div class="bg--3 px-5 pb-2 card-body d-flex flex-wrap justify-content-around">
                    <p class="w-50 fs-15 text-danger flex-grow-1 ">
                        <i class="tio-info-outined"></i>
                    {{ translate($checkCurrency).' '. 'No es compatible con su actual' }}   {{ $currency }}({{$currency_symbol  }}) {{ 'Moneda, por lo que los usuarios no pueden utilizar estas opciones de pago digital como pago en los sitios web y aplicaciones.' }}</p>

                </div>
            </div>
        </div>
        @elseif ($digital_payment && $digital_payment['status'] ==1 && $data_values->where('is_active',1  )->count()  == 0)
        <br>
        <div>
            <div class="card">
                <div class="bg--3 px-5 pb-2 card-body d-flex flex-wrap justify-content-around">
                    <p class="w-50 fs-15 text-danger flex-grow-1 ">
                        <i class="tio-info-outined"></i>
                    {{ 'Actualmente, no existe ningún método de pago digital configurado que admita' }}   {{ $currency }}({{$currency_symbol  }}),{{ 'por lo tanto, los usuarios no pueden ver las opciones de pago digital en sus sitios web y aplicaciones. Debes activar al menos un método de pago digital que admita' }}   {{ $currency }}({{$currency_symbol  }}) {{ 'de lo contrario, todos los usuarios no podrán pagar mediante pagos digitales.' }}</p>

                </div>
            </div>
        </div>

        @endif
        @php($is_published = $published_status == 1 ? 'inactive' : '')
        <!-- Tab Content -->
        <div class="row digital_payment_methods  {{ $is_published }} mt-3 g-3">
            @foreach($data_values->sortByDesc('is_active') as $payment_key => $payment)
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.third-party.payment-method-update'):'javascript:'}}" method="POST"
                              id="{{$payment->key_name}}-form" enctype="multipart/form-data">
                            @csrf
                            <div class="card-header d-flex flex-wrap align-content-around">
                                <h5>
                                    <span class="text-uppercase">{{str_replace('_',' ',$payment->key_name)}}</span>
                                </h5>
                                <label  id="span_on_{{ $payment->key_name }}" class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                    <span
                                        class="mr-2 switch--custom-label-text text-primary on text-uppercase">{{ 'en' }}</span>
                                    <span class="mr-2 switch--custom-label-text off text-uppercase">{{ 'apagado' }}</span>
                                    <input id="add_check_{{ $payment->key_name }}"  type="checkbox" name="status" value="1" data-gateway="{{ $payment->key_name }}" data-status="{{ $payment['is_active'] }}"
                                           class="toggle-switch-input  {{ \App\CentralLogics\Helpers::checkCurrency($payment->key_name , 'payment_gateway') === true && $payment['is_active']  ? 'open-warning-modal' : ''}} " {{$payment['is_active']==1?'checked':''}}>
                                    <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                </label>
                            </div>

                            @php($additional_data = $payment['additional_data'] != null ? json_decode($payment['additional_data']) : [])
                            <div class="card-body">
                                <div class="payment--gateway-img">
                                    <img  id="{{$payment->key_name}}-image-preview" class="__height-80 onerror-image"
                                          data-onerror-image="{{asset('assets/admin/img/payment/placeholder.png')}}"

                                          @if ($additional_data != null)
                                              src="{{ \App\CentralLogics\Helpers::get_full_url('payment_modules/gateway_image',$additional_data?->gateway_image,$additional_data?->storage ?? 'public') }}"

                                          @else
                                              src="{{asset('assets/admin/img/payment/placeholder.png')}}"
                                          @endif



                                          alt="public">
                                </div>

                                <input name="gateway" value="{{$payment->key_name}}" class="d-none">

                                @php($mode=$data_values->where('key_name',$payment->key_name)->first()->live_values['mode'])
                                <div class="form-floating mb-2" >
                                    <select class="js-select form-control theme-input-style w-100" name="mode">
                                        <option value="live" {{$mode=='live'?'selected':''}}>{{ 'Vivir' }}</option>
                                        <option value="test" {{$mode=='test'?'selected':''}}>{{ 'Prueba' }}</option>
                                    </select>
                                </div>

                                @php($skip=['gateway','mode','status','supported_country'])
                                @foreach($data_values->where('key_name',$payment->key_name)->first()->live_values as $key=>$value)
                                    @if(!in_array($key,$skip))
                                        <div class="form-floating mb-2" >
                                            <label for="{{$payment_key}}-{{$key}}"
                                                   class="form-label">{{ucwords(str_replace('_',' ',$key))}}
                                                *</label>
                                            <input id="{{$payment_key}}-{{$key}}" type="text" class="form-control"
                                                   name="{{$key}}"
                                                   placeholder="{{ucwords(str_replace('_',' ',$key))}} *"
                                                   value="{{env('APP_ENV')=='demo'?'':$value}}">
                                        </div>
                                    @endif
                                @endforeach

                                @if($payment['key_name'] == 'paystack')
                                    <div class="form-floating mb-2" >
                                        <label for="Callback_Url" class="form-label">{{'URL de devolución de llamada'}}</label>
                                        <input id="Callback_Url" type="text"
                                               class="form-control"
                                               placeholder="{{'URL de devolución de llamada'}} *"
                                               readonly
                                               value="{{env('APP_ENV')=='demo'?'': route('paystack.callback')}}" {{$is_published}}>
                                    </div>
                                @endif

                                @php($supportedCountry = $payment->live_values)
                                    @if ( $payment['key_name'] == 'mercadopago')
                                @php($supportedCountry = isset($supportedCountry['supported_country']) ? $supportedCountry['supported_country'] : ['argentina'])
                            <label for="{{ $payment->key_name }}-title" class="form-label">
                                {{ 'País admitido' }} *
                            </label>
                            <div class="mb-4">
                                <select class="form-control w-100" name="supported_country">
                                    <option value="egypt" {{$supportedCountry == 'egypt'?'selected':''}}>
                                        {{ 'Egipto' }}
                                    </option>
                                    <option value="PAK" {{$supportedCountry == 'PAK'?'selected':''}}>
                                        {{ 'Pakistán' }}
                                    </option>
                                    <option value="KSA" {{$supportedCountry == 'KSA'?'selected':''}}>
                                        {{ 'Arabia Saudita' }}
                                    </option>
                                    <option value="oman" {{$supportedCountry == 'oman'?'selected':''}}>
                                        {{ 'Omán' }}
                                    </option>
                                    <option value="UAE" {{$supportedCountry == 'UAE'?'selected':''}}>
                                        {{ 'Emiratos Árabes Unidos' }}
                                    </option>

                                    <option value="argentina" {{$supportedCountry == 'argentina'?'selected':''}}>
                                        {{ 'Argentina' }}
                                    </option>
                                    <option value="brasil" {{$supportedCountry == 'brasil'?'selected':''}}>
                                        {{ 'brasil' }}
                                    </option>
                                    <option value="mexico" {{$supportedCountry == 'mexico'?'selected':''}}>
                                        {{ 'México' }}
                                    </option>
                                    <option value="uruguay" {{$supportedCountry == 'uruguay'?'selected':''}}>
                                        {{ 'Uruguay' }}
                                    </option>
                                    <option value="colombia" {{$supportedCountry == 'colombia'?'selected':''}}>
                                        {{ 'Colombia' }}
                                    </option>
                                    <option value="chile" {{$supportedCountry == 'chile'?'selected':''}}>
                                        {{ 'Chile' }}
                                    </option>
                                    <option value="peru" {{$supportedCountry == 'peru'?'selected':''}}>
                                        {{ 'Perú' }}
                                    </option>
                                </select>
                            </div>
                        @endif


                                <div class="form-floating mb-2" >
                                    <label for="payment_gateway_title-{{$payment_key}}"
                                           class="form-label">{{'título de la pasarela de pago'}}</label>
                                    <input type="text" class="form-control"
                                           name="gateway_title" id="payment_gateway_title-{{$payment_key}}"
                                           placeholder="{{'título de la pasarela de pago'}}"
                                           value="{{$additional_data != null ? $additional_data->gateway_title : ''}}">
                                </div>

                                <div class="form-floating mb-2" >
                                    <label for="exampleFormControlInput1"
                                           class="form-label">{{'logo'}}</label>
                                    <input type="file" class="form-control logo" name="gateway_image" data-id="{{$payment->key_name}}" id="{{$payment->key_name}}-image" accept=".webp, .jpg, .png, .jpeg|image/*">
                                </div>

                                <div class="text-right mt-2 "  >
                                    <button type="submit" class="btn btn-primary px-5">{{'ahorrar'}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <!-- End Tab Content -->
    </div>


    <div class="modal fade" id="payment-gateway-warning-modal">
        <div class="modal-dialog modal-dialog-centered status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img width="80" src="{{  asset('assets/admin/img/modal/gateway.png') }}" class="mb-20">
                                <h5 class="modal-title"></h5>
                            </div>
                            <div class="text-center" >
                                <h3 > {{ '¿Estás seguro? ¿Quieres desactivar?'}} <span id="gateway_name"></span> {{ 'como método de Pago Digital?' }}</h3>
                                <div > <p>{{ 'Debes activar al menos un método de pago digital que admita'}} {{ $currency }} {{ '. De lo contrario, los clientes no pueden pagar mediante pagos digitales desde la aplicación y los sitios web. Y además los restaurantes no pueden pagarte digitalmente.' }}</h3></p></div>
                            </div>

                            <div class="text-center mb-4" >
                                <a class="text--underline" href="{{ route('admin.business-settings.business-setup') }}"> {{ 'Ver configuración de moneda.' }}</a>
                            </div>
                            </div>

                        <div class="btn--container justify-content-center">
                            <button data-dismiss="modal"  class="btn btn--cancel min-w-120" >{{'Cancelar'}}</button>
                            <button data-dismiss="modal"  id="confirm-currency-change" type="button"  class="btn btn--primary min-w-120">{{'DE ACUERDO'}}</button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('script_2')
    <script src="{{asset('assets/admin/js/view-pages/business-settings-payment-page.js')}}"></script>
    <script>
        "use strict";


        $(document).on('click', '.open-warning-modal', function(event) {

            const elements = document.querySelectorAll('.open-warning-modal');
            const count = elements.length;

            if(elements.length === 1){

                let gateway = $(this).data('gateway');
                if ($(this).is(':checked') === false) {
                    event.preventDefault();
                    $('#payment-gateway-warning-modal').modal('show');
                    var formated_text=  gateway.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
                    $('#gateway_name').attr('data-gateway_key', gateway).html(formated_text);
                    $(this).data('originalEvent', event);
                }
            }


        });

    $(document).on('click', '#confirm-currency-change', function() {
    var gatewayName =   $('#gateway_name').data('gateway_key');
    if (gatewayName) {
    $('#span_on_' + gatewayName).removeClass('checked');
    }

    var originalEvent = $('.open-warning-modal[data-gateway="' + gatewayName + '"]').data('originalEvent');
    if (originalEvent) {
    var newEvent = $.Event(originalEvent);
    $(originalEvent.target).trigger(newEvent);
    }

    $('#payment-gateway-warning-modal').modal('hide');
    });

    $(".logo").change(function() {
    let viewer = $(this).data('id');
    if (this.files && this.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
            $('#' + viewer + '-image-preview').attr('src', e.target.result);
        }
        reader.readAsDataURL(this.files[0]);
    }
    });


        @if(!isset($digital_payment) || $digital_payment['status']==0)
        $('.digital_payment_methods').hide();
        @endif
    </script>
@endpush
