@extends('layouts.admin.app')

@section('title', 'Verificación OTP de Firebase')


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/firebase_auth.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'Verificación OTP de Firebase'}}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.third-party-links')
                <div class="">
                    <div class="text--primary-2  mx-4 d-flex flex-wrap justify-content-end align-items-center" type="button" data-toggle="modal" data-target="#instructionsModal">
                        <strong class="mr-2">{{'Cómo funciona'}}</strong>
                        <div class="blinkings">
                            <i class="tio-info-outined"></i>
                        </div>
                    </div>
                </div>
            </div>
        <!-- End Page Header -->



        <form
            action="{{env('APP_MODE')!='demo'?route('admin.business-settings.third-party.firebase_otp_update',['recaptcha']):'javascript:'}}"
            method="post">
            @csrf
            <div class="row g-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-6 col-sm-6">
                                    @php($firebase_otp_verification = \App\Models\BusinessSetting::where('key', 'firebase_otp_verification')->first())
                                    @php($firebase_otp_verification = $firebase_otp_verification ? $firebase_otp_verification->value : '')
                                    <div class="form-group mb-0">

                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{ 'Estado de verificación de Firebase OTP' }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                      data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ 'Si este campo está activo, los clientes obtienen la OTP a través de Firebase.' }}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'verificación de base de fuego otp' }}"> *
                                                </span>
                                            </span>
                                            <input type="checkbox"
                                                   data-id="firebase_otp_verification"
                                                   data-type="toggle"
                                                   data-image-on="{{ asset('assets/admin/img/modal/order-delivery-verification-on.png') }}"
                                                   data-image-off="{{ asset('assets/admin/img/modal/order-delivery-verification-off.png') }}"
                                                   data-title-on="<strong>{{'¿Quieres habilitar la verificación OTP de Firebase?'}}</strong>"
                                                   data-title-off="<strong>{{'¿Quiere desactivar la verificación OTP de Firebase?'}}</strong> "
                                                   data-text-on="<p>{{ 'Con Firebase OTP habilitado, los códigos de verificación se enviarán a través de Firebase.' .' </p>' .'  <p>   <strong>
                                            Note: ' . 'Habilitar Firebase OTP significa que los usuarios no recibirán códigos de verificación por correo electrónico o SMS aunque esos métodos estén activados.' .'</strong>'}}</p>"
                                                   data-text-off="<p>{{ 'Si desactiva Firebase OTP, los usuarios ya no recibirán códigos de verificación a través de Firebase. Debes activar la verificación por correo electrónico o SMS como alternativa.' }}</p>"
                                                   class="status toggle-switch-input dynamic-checkbox-toggle"
                                                   value="1"
                                                   name="firebase_otp_verification" id="firebase_otp_verification"
                                                {{ $firebase_otp_verification == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6">
                                    @php($firebase_web_api_key = \App\Models\BusinessSetting::where('key', 'firebase_web_api_key')->first())
                                    <div class="form-group mb-0">
                                        <label class=" input-label text-capitalize"
                                               for="firebase_web_api_key">
                                            <span>
                                                {{ 'Clave API web' }}
                                            </span>

                                            {{-- <span class="form-label-secondary"
                                                  data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ 'Ingrese la cantidad máxima de efectivo que pueden retener las tiendas. Si este número excede, las tiendas serán suspendidas y no recibirán ningún pedido.' }}"><img
                                                    src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="{{ 'dm cancelar sugerencia de pedido' }}"></span> --}}
                                        </label>
                                        <input type="text" name="firebase_web_api_key" class="form-control"
                                               id="firebase_web_api_key"
                                               value="{{ $firebase_web_api_key ? $firebase_web_api_key->value : '' }}"  required>
                                    </div>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end mt-20">
                                <button type="reset" class="btn btn--reset">{{ 'reiniciar' }}</button>
                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                        class="btn btn--primary call-demo">{{ 'guardar información' }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>




    <div class="modal fade" id="instructionsModal" tabindex="-1" aria-labelledby="instructionsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-end">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center my-5">
                        <img src="{{ asset('assets/admin/img/modal/bell.png') }}">
                    </div>

                    <h5 class="modal-title my-3" id="instructionsModalLabel">{{'Instrucciones'}}</h5>
                    <p>{{ 'Para configurar OTP en Firebase, primero debe crear un proyecto de Firebase. Si aún no ha creado ningún proyecto para su aplicación, cree un proyecto primero.' }}
                    </p>
                    <p>{{ 'Ahora ve al' }} <a href="https://console.firebase.google.com/" target="_blank">Firebase console </a>{{ 'y sigue las instrucciones a continuación' }} -</p>
                    <ol class="d-flex flex-column __gap-5px __instructions">
                        <li>{{ 'Vaya a su proyecto de Firebase.' }}</li>
                        <li>{{ 'Navegue hasta el menú Construir en la barra lateral izquierda y seleccione Autenticación.' }}</li>
                        <li>{{ 'Inicie el proyecto y vaya a la pestaña Método de inicio de sesión.' }}</li>
                        <li>{{ 'En la sección Proveedores de inicio de sesión, seleccione la opción Teléfono.' }}</li>
                        <li>{{ 'Asegúrese de habilitar el método Teléfono y presione guardar.' }}</li>
                    </ol>
                </div>
            </div>
        </div>
        </div>

    @endsection
