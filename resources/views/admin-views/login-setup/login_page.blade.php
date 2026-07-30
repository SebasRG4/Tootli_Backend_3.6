@extends('layouts.admin.app')

@section('title','configuración de la página de inicio de sesión')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/app.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'configuración de inicio de sesión'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <ul class="nav nav-tabs border-0 nav--tabs nav--pills mb-4">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.business-settings.login-settings.index') }}">{{'Inicio de sesión del cliente'}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.business-settings.login_url_page') }}">{{'URL de la página de inicio de sesión del panel'}}</a>
            </li>
        </ul>

        <form action="{{route('admin.business-settings.login-settings.update')}}" method="post">
            @csrf
            <div class="card">
                <div class="card-header">
                    <div>
                        <h4 class="mb-1">
                            {{'Configurar la opción de inicio de sesión'}}
                        </h4>
                        <p class="fs-12 m-0">
                            {{'La opción que seleccione el cliente tendrá la opción de iniciar sesión.'}}
                        </p>
                    </div>
                </div>
                <div class="card-body pt-3">

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-md-4">
                            <label class="form-check form--check form--check--inline border rounded">
                                <span class="user-select-none form-check-label flex-grow-1">
                                    {{'Iniciar sesión manualmente'}}
                                    <span data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ 'Al habilitar el inicio de sesión manual, los clientes tendrán la opción de crear una cuenta e iniciar sesión utilizando las credenciales y contraseña necesarias en la aplicación y el sitio web.' }}">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </span>
                                <input class="form-check-input login-option-type" type="checkbox" name="manual_login_status" id="customer-manual-login" value="1" {{ (isset($data['manual_login_status']) && $data['manual_login_status'] == '1')? 'checked':'' }}>
                            </label>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="form-check form--check form--check--inline border rounded">
                                <span class="user-select-none form-check-label flex-grow-1">
                                    {{'Iniciar sesión OTP'}}
                                    <span data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ 'Con OTP Login, los clientes pueden iniciar sesión utilizando su número de teléfono. mientras que los nuevos clientes pueden crear cuentas al instante.' }}">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </span>
                                <input class="form-check-input login-option-type" type="checkbox" name="otp_login_status" id="customer-otp-login" value="1" {{ (isset($data['otp_login_status']) && $data['otp_login_status'] == '1')? 'checked':'' }}>
                            </label>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <label class="form-check form--check form--check--inline border rounded">
                                <span class="user-select-none form-check-label flex-grow-1">
                                    {{'Inicio de sesión en redes sociales'}}
                                    <span data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ 'Con Social Login, los clientes pueden iniciar sesión utilizando credenciales de redes sociales. mientras que los nuevos clientes pueden crear cuentas al instante.' }}">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                </span>
                                <input class="form-check-input login-option-type" type="checkbox" name="social_login_status" id="customer-social-login" value="1" {{ (isset($data['social_login_status']) && $data['social_login_status'] == '1')? 'checked':'' }}>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4 social-media-login-container " style="display: {{ (isset($data['social_login_status']) && $data['social_login_status'] == '1')? '':'none' }}" id="social-login-area">
                        <div class="mb-3">
                            <h4 class="mb-1">
                                {{'Configuración de inicio de sesión en redes sociales'}}
                            </h4>
                            <a href="{{route('admin.business-settings.third-party.social-login.view')}}" class="fs-12 c1 text-underline fw-semibold" target="_blank">
                                {{'Conecte el sistema de inicio de sesión de terceros desde aquí'}}
                            </a>
                        </div>
                        <div class="bg-light p-4 rounded">
                            <h4 class="mb-1">
                                {{'Elige las redes sociales'}}
                            </h4>
                            <div class="row g-3">
                                <div class="col-sm-6 col-md-4">
                                    <label class="form-check form--check form--check--inline border rounded">
                                        <span class="user-select-none form-check-label flex-grow-1">
                                            {{'Google'}}
                                            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ 'Al habilitar el inicio de sesión de Google, los clientes pueden iniciar sesión en el sitio utilizando sus credenciales de Gmail existentes.' }}">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </span>
                                        <input type="checkbox" name="google_login_status" id="google_login_status" value="1" {{ (isset($data['google_login_status']) && $data['google_login_status'] == '1')? 'checked':'' }} class="form-check-input social-media-status-checkbox">
                                    </label>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <label class="form-check form--check form--check--inline border rounded">
                                        <span class="user-select-none form-check-label flex-grow-1">
                                            {{'Facebook'}}
                                            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ 'Al habilitar el inicio de sesión con Facebook, los clientes pueden iniciar sesión en el sitio utilizando sus credenciales de Facebook existentes.' }}">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </span>
                                        <input type="checkbox" name="facebook_login_status" id="facebook_login_status" value="1" {{ (isset($data['facebook_login_status']) && $data['facebook_login_status'] == '1')? 'checked':'' }} class="form-check-input social-media-status-checkbox">
                                    </label>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <label class="form-check form--check form--check--inline border rounded">
                                        <span class="user-select-none form-check-label flex-grow-1">
                                            {{'Manzana'}}
                                            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ 'Al habilitar el inicio de sesión de Apple, los clientes pueden iniciar sesión en el sitio utilizando sus credenciales de inicio de sesión de Apple existentes, solo para dispositivos Apple.' }}">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </span>
                                        <input type="checkbox" name="apple_login_status" value="1" {{ (isset($data['apple_login_status']) && $data['apple_login_status'] == '1')? 'checked':'' }} class="form-check-input social-media-status-checkbox">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="mb-3">
                            <h4 class="mb-1">
                                {{'Verificación'}}
                            </h4>
                            <p class="fs-12">
                                {{'La opción que seleccione a continuación deberá ser verificada por el cliente desde la aplicación/sitio web del cliente.'}}
                            </p>
                        </div>
                        <div class="bg-light p-4 rounded">
                            <div class="row g-3">
                                <div class="col-sm-6 col-md-4">
                                    <label class="form-check form--check form--check--inline border rounded">
                                        <span class="user-select-none form-check-label flex-grow-1">
                                            {{'Verificación de correo electrónico'}}
                                            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ 'Si la verificación de correo electrónico está activada, los Clientes deben verificar su dirección de correo electrónico con una OTP para completar el proceso de registro.' }}">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </span>
                                        <input type="checkbox" name="email_verification_status" value="1" {{ (isset($data['email_verification_status']) && $data['email_verification_status'] == '1')? 'checked':'' }} class="form-check-input social-media-status-checkbox">
                                    </label>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <label class="form-check form--check form--check--inline border rounded">
                                        <span class="user-select-none form-check-label flex-grow-1 me-4 d-block">
                                            {{'Verificación de número de teléfono'}}
                                            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ 'Si la verificación del número de teléfono está activada, los clientes deben verificar su número de teléfono con una OTP para completar el proceso de registro.' }}">
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </span>
                                        <input type="checkbox" name="phone_verification_status" id="phone_verification" value="1" {{ (isset($data['phone_verification_status']) && $data['phone_verification_status'] == '1')? 'checked':'' }} class="form-check-input social-media-status-checkbox">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset">{{ 'reiniciar' }}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn--primary {{env('APP_MODE')!='demo'?'':'call-demo'}}">{{'entregar'}}</button>
                    </div>

                </div>
            </div>
        </form>
    </div>


    <div class="modal fade" id="select-one-method-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog status-warning-modal text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-0"><b></b>
                    <div class="text-center mb-20">
                        <img src="{{asset('assets/admin/img/modal/package-status-disable.png')}}" alt="" class="mb-20">
                        <h5 class="modal-title">{{'¡Alerta importante!'}}</h5>
                    </div>
                    <p>{{ 'Al menos un método de inicio de sesión debe permanecer activo para el cliente; de lo contrario, no podrán iniciar sesión en el sistema.' }}</p>
                </div>
                <div class="modal-footer justify-content-center border-0">
                    <a type="button" class="btn btn--primary mw-300px" data-dismiss="modal">{{'bueno'}}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sms-config-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog status-warning-modal text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-0"><b></b>
                    <div class="text-center mb-20">
                        <img src="{{asset('assets/admin/img/sms-configuration.svg')}}" alt="" class="mb-20 img--80">
                        <h5 class="modal-title">{{'Configure la configuración de SMS primero'}}</h5>
                    </div>
                    <p>{{ 'Parece que tu configuración de SMS aún no está configurada. Para habilitar el sistema OTP, primero configure la configuración de SMS.' }}</p>
                </div>
                <div class="modal-footer justify-content-center border-0">
                    <a type="button" class="btn btn--primary w-100 mw-300px" href="{{ route('admin.business-settings.third-party.sms-module') }}" target="_blank">{{'Ir a Configuración de SMS'}}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="select-one-method-android-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog status-warning-modal text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-0"><b></b>
                    <div class="text-center mb-20">
                        <img src="{{asset('assets/admin/img/modal/package-status-disable.png')}}" alt="" class="mb-20">
                        <h5 class="modal-title">{{'¡Alerta importante!'}}</h5>
                    </div>
                    <p>{{ 'Si activa solo el inicio de sesión social como método de inicio de sesión, debe habilitar al menos una opción entre Google y Facebook para usuarios de Android.' }}</p>
                </div>
                <div class="modal-footer justify-content-center border-0">
                    <a type="button" class="btn btn--primary mw-300px" data-dismiss="modal">{{'bueno'}}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="select-one-method-social-login-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog status-warning-modal text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-0"><b></b>
                    <div class="text-center mb-20">
                        <img src="{{asset('assets/admin/img/modal/package-status-disable.png')}}" alt="" class="mb-20">
                        <h5 class="modal-title">{{'¡Alerta importante!'}}</h5>
                    </div>
                    <p>{{ 'Si está activando el inicio de sesión social como método de inicio de sesión, debe habilitar al menos una opción entre Google, Facebook y Apple.' }}</p>
                </div>
                <div class="modal-footer justify-content-center border-0">
                    <a type="button" class="btn btn--primary mw-300px" data-dismiss="modal">{{'bueno'}}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="setup-google-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog status-warning-modal text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-0"><b></b>
                    <div class="text-center mb-20">
                        <img src="{{asset('assets/admin/img/modal/google.png')}}" alt="" class="mb-20">
                        <h5 class="modal-title">{{'Configure la configuración de Google primero'}}</h5>
                    </div>
                    <p>{{ 'Parece que tu configuración de inicio de sesión de Google aún no está configurada. Para habilitar la opción de inicio de sesión de Google, primero configure la configuración de Google.' }}</p>
                </div>
                <div class="modal-footer justify-content-center border-0">
                    <a type="button" class="btn btn--primary mw-300px" href="{{route('admin.business-settings.third-party.social-login.view')}}" target="_blank">{{'Ir a configuración de Google'}}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="setup-facebook-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog status-warning-modal text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-0"><b></b>
                    <div class="text-center mb-20">
                        <img src="{{asset('assets/admin/img/modal/facebook.png')}}" alt="" class="mb-20">
                        <h5 class="modal-title">{{'Configure la configuración de Facebook primero'}}</h5>
                    </div>
                    <p>{{ 'Parece que tu configuración de inicio de sesión de Facebook aún no está configurada. Para habilitar la opción de inicio de sesión de Facebook, primero configure la configuración de Facebook.' }}</p>
                </div>
                <div class="modal-footer justify-content-center border-0">
                    <a type="button" class="btn btn--primary mw-300px" href="{{route('admin.business-settings.third-party.social-login.view')}}" target="_blank">{{'Ir a configuración de Facebook'}}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="setup-apple-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog status-warning-modal text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-0"><b></b>
                    <div class="text-center mb-20">
                        <img src="{{asset('assets/admin/img/modal/apple.png')}}" alt="" class="mb-20">
                        <h5 class="modal-title">{{'Configure la configuración de Apple primero'}}</h5>
                    </div>
                    <p>{{ 'Parece que tu configuración de inicio de sesión de Apple aún no está configurada. Para habilitar la opción de inicio de sesión de Apple, primero configure la configuración de Apple.' }}</p>
                </div>
                <div class="modal-footer justify-content-center border-0">
                    <a type="button" class="btn btn--primary mw-300px" href="{{route('admin.business-settings.third-party.social-login.view')}}" target="_blank">{{'Ir a configuración de Apple'}}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="sms-config-verification-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog status-warning-modal text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-0"><b></b>
                    <div class="text-center mb-20">
                        <img src="{{asset('assets/admin/img/sms-configuration.svg')}}" alt="" class="mb-20 img--80">
                        <h5 class="modal-title">{{'Configure la configuración de SMS primero'}}</h5>
                    </div>
                    <p>{{ 'Parece que tu configuración de SMS aún no está configurada. Para habilitar la verificación del teléfono, primero configure la configuración de SMS.' }}</p>
                </div>
                <div class="modal-footer justify-content-center border-0">
                    <a type="button" class="btn btn--primary w-100 mw-300px" href="{{ route('admin.business-settings.third-party.sms-module') }}" target="_blank">{{'Ir a Configuración de SMS'}}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="mail-config-verification-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog status-warning-modal text-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-0"><b></b>
                    <div class="text-center mb-20">
                        <img src="{{asset('assets/admin/img/sms-configuration.svg')}}" alt="" class="mb-20 img--80">
                        <h5 class="modal-title">{{'Configure primero la configuración del correo electrónico'}}</h5>
                    </div>
                    <p>{{ 'Parece que tu configuración de correo electrónico aún no está configurada. Para habilitar la verificación por correo electrónico, primero configure la configuración de SMS.' }}</p>
                </div>
                <div class="modal-footer justify-content-center border-0">
                    <a type="button" class="btn btn--primary w-100 mw-300px" href="{{ route('admin.business-settings.third-party.mail-config') }}" target="_blank">{{'Ir a configuración de correo'}}</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script type="text/javascript">
        $(document).ready(function() {
            @if (session('select-one-method'))
            $('#select-one-method-modal').modal('show');
            @endif
            @if (session('sms-config'))
            $('#sms-config-modal').modal('show');
            @endif
            @if (session('select-one-method-android'))
            $('#select-one-method-android-modal').modal('show');
            @endif
            @if (session('select-one-method-social-login'))
            $('#select-one-method-social-login-modal').modal('show');
            @endif
            @if (session('setup-google'))
            $('#setup-google-modal').modal('show');
            @endif
            @if (session('setup-facebook'))
            $('#setup-facebook-modal').modal('show');
            @endif
            @if (session('setup-apple'))
            $('#setup-apple-modal').modal('show');
            @endif
            @if (session('sms-config-verification'))
            $('#sms-config-verification-modal').modal('show');
            @endif
            @if (session('email-config-verification'))
            $('#email-config-verification-modal').modal('show');
            @endif

            $("#customer-social-login").change(function(e) {
                if ($(this).is(':checked')) {
                    $('#social-login-area').show();
                } else {
                    $('#social-login-area').hide();
                }
            });
        });
    </script>
@endpush
