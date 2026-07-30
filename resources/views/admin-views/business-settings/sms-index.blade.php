@extends('layouts.admin.app')

@section('title','configuración del módulo del sistema')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/sms.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'configuración de puerta de enlace SMS'}}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.third-party-links')
        </div>
        <!-- End Page Header -->

        <div class="row g-3">

            @if ($published_status == 1)
                <div class="col-md-12 mb-3">
                    <div class="card">
                        <div class="card-body  d-flex flex-wrap  justify-content-around">
                            <h4  class="w-50 flex-grow-1 module-warning-text">
                                <i class="tio-info-outined"></i>
                                {{ 'Su configuración de SMS actual está deshabilitada porque ha habilitado el complemento de puerta de enlace de SMS. Para visitar la configuración de su puerta de enlace de SMS actualmente activa, siga el enlace.' }}
                                </h4>
                                <div>
                                    <a href="{{!empty($payment_url) ? $payment_url : ''}}" class="btn btn-outline-primary"> <i class="tio-settings"></i> {{'Configuración'}}</a>
                                </div>
                        </div>
                    </div>
                </div>
            @endif
            @php($is_published = $published_status == 1 ? 'inactive' : '')
            @foreach($data_values as $gateway)
            <div class="col-md-6 digital_payment_methods  {{ $is_published }} mb-3" >
                <div class="card">
                    <div class="card-header">
                        <h4 class="page-title">{{translate($gateway->key_name)}}</h4>
                    </div>
                    <div class="card-body p-30">
                        <form action="{{route('admin.business-settings.third-party.sms-module-update',[$gateway->key_name])}}" method="POST"
                                id="{{$gateway->key_name}}-form" enctype="multipart/form-data">
                            @csrf
                            @method('post')
                        <div class="discount-type">
                                <div class="d-flex align-items-center gap-4 gap-xl-5 mb-30">
                                    <div class="custom-radio">
                                        <input class="{{ \App\Models\BusinessSetting::where('key', 'firebase_otp_verification')->first()?->value == 1 ? 'firebase-check' : '' }} "  type="radio" id="{{$gateway->key_name}}-active"
                                                name="status"
                                                value="1" {{$data_values->where('key_name',$gateway->key_name)->first()->live_values['status']?'checked':''}}>
                                        <label
                                            for="{{$gateway->key_name}}-active"> {{ 'Activo' }}</label>
                                    </div>
                                    <div class="custom-radio">
                                        <input type="radio" id="{{$gateway->key_name}}-inactive"
                                                name="status"
                                                value="0" {{$data_values->where('key_name',$gateway->key_name)->first()->live_values['status']?'':'checked'}}>
                                        <label
                                            for="{{$gateway->key_name}}-inactive"> {{ 'Inactivo' }}</label>
                                    </div>
                                </div>

                                <input name="gateway" value="{{$gateway->key_name}}" class="d-none">
                                <input name="mode" value="live" class="d-none">

                                @php($skip=['gateway','mode','status'])
                                @foreach($data_values->where('key_name',$gateway->key_name)->first()->live_values as $key=>$value)
                                    @if(!in_array($key,$skip))
                                        <div class="form-floating mb-30 mt-30 text-capitalize">
                                            <label for="{{$key}}" class="form-label">{{translate($key)}}  {{ $gateway->key_name == 'alphanet_sms' &&  $key == 'sender_id'? '('. 'Opcional' .')' : '*'}}  </label>
                                            <input id="{{$key}}" type="text" class="form-control"
                                                   name="{{$key}}"
                                                   placeholder=" {{ $key == 'otp_template' ?  'Su PIN de seguridad es'. ' #OTP#' : translate($key) .' *'   }}    "
                                                   value="{{env('APP_ENV')=='demo'?'':$value}}">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn--primary demo_check">
                                {{ 'Actualizar' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

            </div>
        </div>


        <div class="modal fade" id="firebase-modal">
            <div class="modal-dialog status-warning-modal">
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
                                    <img src="{{ asset('assets/admin/img/modal/order-delivery-verification-on.png') }}" class="mb-20">
                                    <h5 class="modal-title" >{{ 'Nota' }} </h5>
                                </div>
                                <div class="text-center">
                                    <p class="text--danger" >
                                        {{ 'Actualmente su sistema FireBase OTP está activo. Los usuarios no recibirán ninguna OTP de esta puerta de enlace SMS' }}
                                    </p>
                                </div>
                            </div>
                            <div class="btn--container justify-content-center">
                                <button type="button"  data-dismiss="modal" class="btn btn--primary min-w-120 confirm-Status-Toggle" data-dismiss="modal" >{{'DE ACUERDO'}}</button>
                                {{-- <button type="button" class="btn btn--cancel min-w-120" data-dismiss="modal">
                                    {{'Cancelar'}}
                                </button> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>











@endsection
@push('script_2')

    <script>

    $(document).on('click', '.firebase-check', function(event) {
        // event.preventDefault();
        $('#firebase-modal').modal('show');
    });

    </script>
@endpush



