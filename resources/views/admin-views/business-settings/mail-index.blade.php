@extends('layouts.admin.app')

@section('title', 'configuración de correo')

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/email.png')}}" class="w--26" alt="">
                </span>
                <span>{{ 'configuración de correo smtp' }}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.third-party-links')
        </div>
        <!-- End Page Header -->

        <div class="card min-h-60vh">
            <div class="card-header card-header-shadow pb-0">
                <div class="d-flex flex-wrap justify-content-between w-100 row-gap-1">
                    <ul class="nav nav-tabs nav--tabs border-0 gap-2">
                        <li class="nav-item mr-2 mr-md-4">
                            <a href="{{route('admin.business-settings.third-party.mail-config')}}" class="nav-link pb-2 px-0 pb-sm-3 active">
                                <img src="{{asset('assets/admin/img/mail-config.png')}}" alt="">
                                <span>{{'Configuración de correo'}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('admin.business-settings.third-party.test')}}" class="nav-link pb-2 px-0 pb-sm-3">
                                <img src="{{asset('assets/admin/img/test-mail.png')}}" alt="">
                                <span>{{'Enviar correo de prueba'}}</span>
                            </a>
                        </li>
                    </ul>
                    <div class="py-1">
                        <div class="text--primary-2 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#works-modal">
                            <strong class="mr-2">{{'Cómo funciona'}}</strong>
                            <div class="blinkings">
                                <i class="tio-info-outined"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="mail-config">
                        @php($config = \App\Models\BusinessSetting::where(['key' => 'mail_config'])->first())
                        @php($data = $config ? json_decode($config['value'], true) : null)

                        <form action="{{route('admin.business-settings.third-party.mail-config-status')}}"
                        method="post" id="mail-config-disable_form">
                        @csrf
                            <div class="form-group text-center d-flex flex-wrap align-items-center">
                                <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-2">
                                    <span class="pr-1 d-flex align-items-center switch--label text--primary">
                                        <span class="line--limit-1">
                                            {{isset($data) && isset($data['status'])&&$data['status']==1?'Apagar':'Encender'}}
                                        </span>
                                    </span>

                                    <?php
                                        if (App\Models\BusinessSetting::where('key', 'firebase_otp_verification')->first()?->value == 1) {
                                            $text= "<p class=text--danger>" .'NOTA: Actualmente su sistema FireBase OTP está activo. Los usuarios no recibirán ningún correo electrónico relacionado con OTP.' ."</p>" ;
                                        }
                                    ?>


                                    <input id="mail-config-disable" type="checkbox"
                                           data-id="mail-config-disable"
                                            data-type="status"
                                            data-image-on="{{ asset('assets/admin/img/modal/mail-success.png') }}"
                                            data-image-off="{{ asset('assets/admin/img/modal/mail-warning.png') }}"
                                            data-title-on="{{ '¡Importante!' }}"
                                            data-title-off="{{ '¡Advertencia!' }}"
                                            data-text-on="<p>{{ 'Habilitar los servicios de configuración de correo permitirá que el sistema envíe correos electrónicos. Asegúrese de haber configurado correctamente los ajustes SMTP para evitar posibles problemas con la entrega de correo electrónico.' }}</p>
                                            {{ $text ?? '' }} "
                                            data-text-off="<p>{{ 'Deshabilitar los servicios de configuración de correo evitará que el sistema envíe correos electrónicos. Desactive este servicio únicamente si tiene intención de suspender temporalmente el envío de correo electrónico. Tenga en cuenta que esto puede afectar la funcionalidad del sistema que depende de la comunicación por correo electrónico.' }}</p>"
                                            class="status toggle-switch-input dynamic-checkbox"


                                           name="status" value="1" {{isset($data) && isset($data['status'])&&$data['status']==1?'checked':''}}>
                                    <span class="toggle-switch-label text p-0">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <small>{{'*Al desactivar la configuración de correo, todos sus servicios de correo se desactivarán.'}}</small>
                            </div>
                        </form>
                        <form action="javascript:"
                            method="post" id="mail-config-form" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="status" value="{{(isset($data)&& isset($data['status'])) ? $data['status']:0 }}">
                            <div class="disable-on-turn-of {{isset($data) && isset($data['status'])&&$data['status']==1?'':'inactive'}}">
                                <div class="row g-3">
                                    <div class="col-sm-12">
                                        <div class="form-group mb-0">
                                            <label for="name" class="form-label">{{ 'nombre del remitente' }}</label><br>
                                            <input id="name" type="text" placeholder="{{ 'Ex:' }} Alex" class="form-control" name="name"
                                                value="{{ env('APP_MODE') != 'demo' ? $data['name'] ?? '' : '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label for="host" class="form-label">{{ 'anfitrión' }}</label><br>
                                            <input id="host" type="text" class="form-control" name="host" placeholder="{{'Ej: correo.6am.one'}}"
                                                value="{{ env('APP_MODE') != 'demo' ? $data['host'] ?? '' : '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label for="driver" class="form-label">{{ 'conductor' }}</label><br>
                                            <input id="driver" type="text" class="form-control" name="driver" placeholder="{{'Ej: smtp'}}"
                                                value="{{ env('APP_MODE') != 'demo' ? $data['driver'] ?? '' : '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label for="port" class="form-label">{{ 'puerto' }}</label><br>
                                            <input id="port" type="text" class="form-control" name="port" placeholder="{{'Ej: 587'}}"
                                                value="{{ env('APP_MODE') != 'demo' ? $data['port'] ?? '' : '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group mb-0">
                                            <label for="username" class="form-label">{{ 'nombre de usuario' }}</label><br>
                                            <input id="username" type="text" placeholder="{{ 'Ex:' }} ex@yahoo.com" class="form-control" name="username"
                                                value="{{ env('APP_MODE') != 'demo' ? $data['username'] ?? '' : '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label for="email" class="form-label">{{ 'identificación de correo electrónico' }}</label><br>
                                            <input id="email" type="text" placeholder="{{ 'Ex:' }} ex@yahoo.com" class="form-control" name="email"
                                                value="{{ env('APP_MODE') != 'demo' ? $data['email_id'] ?? '' : '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label for="encryption" class="form-label">{{ 'cifrado' }}</label><br>
                                            <input id="encryption" type="text" placeholder="{{ 'Ex:' }} tls" class="form-control" name="encryption"
                                                value="{{ env('APP_MODE') != 'demo' ? $data['encryption'] ?? '' : '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label for="password" class="form-label">{{ 'Contraseña' }}</label><br>
                                            <input id="password" type="text" class="form-control" name="password" placeholder="{{'Ej: 5+ personajes'}}"
                                                value="{{ env('APP_MODE') != 'demo' ? $data['password'] ?? '' : '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="btn--container justify-content-end">
                                            <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                                            <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                            class="btn btn--primary call-demo"
                                            >{{ 'ahorrar' }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mail Sent -->
    <div class="modal fade" id="sent-mail-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <div class="text-center mb-20">
                        <img src="{{asset('assets/admin/img/sent-mail-box.png')}}" alt="" class="mb-20">
                        <h5 class="modal-title">{{'¡Felicidades! ¡Su correo SMTP se ha configurado correctamente!'}}</h5>
                        <p class="txt">
                            {{'¡Vaya al correo de prueba para comprobar que funciona perfectamente o no!'}}
                        </p>
                    </div>
                    <div class="btn--container justify-content-center">
                        <a href="{{route('admin.business-settings.third-party.test')}}" class="btn btn--primary min-w-120">
                            <img src="{{asset('assets/admin/img/paper-plane.png')}}" alt=""> {{'Enviar correo de prueba'}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Data Modal -->
    <div class="modal fade" id="update-data-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <div class="text-center mb-20">
                        <img src="{{asset('assets/admin/img/mail-config/save-data.png')}}" alt="" class="mb-20">
                        <h5 class="modal-title">{{'¿Enviar un correo de prueba a su correo electrónico?'}}</h5>
                        <p class="txt">
                            {{'Se enviará un correo de prueba a su correo electrónico para confirmar que funciona perfectamente.'}}
                        </p>
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="submit" class="btn btn--primary min-w-120" data-dismiss="modal">
                            {{'Enviar correo de prueba'}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- How it Works Modal -->
    <div class="modal fade" id="works-modal">
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
                                    <img src="{{asset('assets/admin/img/mail-config/slide-1.png')}}" alt="" class="mb-20">
                                    <h5 class="modal-title">{{'Buscar detalles del servidor SMTP'}}</h5>
                                </div>
                                <ul>
                                    <li>
                                        {{'Póngase en contacto con su proveedor de servicios de correo electrónico o administrador de TI para obtener los detalles del servidor SMTP, como el nombre de host, el puerto, el nombre de usuario y la contraseña.'}}
                                    </li>
                                    <li>
                                        {{'Nota: Si no está seguro de dónde encontrar estos detalles, consulte la documentación del proveedor de correo electrónico o los recursos de soporte para obtener orientación.'}}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="item">
                            <div class="mb-20">
                                <div class="text-center">
                                    <img src="{{asset('assets/admin/img/mail-config/slide-2.png')}}" alt="" class="mb-20">
                                    <h5 class="modal-title">{{'Configurar los ajustes SMTP'}}</h5>
                                </div>
                                <ul>
                                    <li>
                                        {{'Vaya a la página de configuración de correo SMTP en el panel de administración.'}}
                                    </li>
                                    <li>
                                        {{'Ingrese los detalles del servidor SMTP obtenidos, incluido el nombre de host, el puerto, el nombre de usuario y la contraseña.'}}
                                    </li>
                                    <li>
                                        {{'Elija el método de cifrado adecuado (por ejemplo, SSL, TLS) si es necesario. Guarde la configuración.'}}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="item">
                            <div class="mb-20">
                                <div class="text-center">
                                    <img src="{{asset('assets/admin/img/mail-config/slide-3.png')}}" alt="" class="mb-20">
                                    <h5 class="modal-title">{{'Probar la conexión SMTP'}}</h5>
                                </div>
                                <ul>
                                    <li>
                                        {{'Haga clic en el botón "Enviar correo de prueba" para verificar la conexión SMTP.'}}
                                    </li>
                                    <li>
                                        {{'Si tiene éxito, verá un mensaje de confirmación que indica que la conexión está funcionando bien.'}}
                                    </li>
                                    <li>
                                        {{'De lo contrario, vuelva a verificar su configuración SMTP e inténtelo nuevamente.'}}
                                    </li>
                                    <li>
                                        {{'Nota: Si no está seguro acerca de la configuración SMTP, comuníquese con su proveedor de servicios de correo electrónico o administrador de TI para obtener ayuda.'}}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="item">
                            <div class="mw-353px mb-20 mx-auto">
                                <div class="text-center">
                                    <img src="{{asset('assets/admin/img/mail-config/slide-4.png')}}" alt="" class="mb-20">
                                    <h5 class="modal-title">{{'Habilitar configuración de correo'}}</h5>
                                </div>
                                <ul class="px-3">
                                    <li>
                                        {{'Si la prueba de conexión SMTP es exitosa, ahora puede habilitar los servicios de configuración de correo colocando el interruptor en "ON".'}}
                                    </li>
                                    <li>
                                        {{'Esto permitirá que el sistema envíe correos electrónicos utilizando la configuración SMTP configurada.'}}
                                    </li>
                                </ul>
                                <div class="btn-wrap">
                                    <button type="submit" class="btn btn--primary w-100" data-dismiss="modal">{{'Entiendo'}}</button>
                                </div>
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

<script>
    "use strict";
    const disableMailConf = () => {
        if($('#mail-config-disable').is(':checked')) {
            $('.disable-on-turn-of').removeClass('inactive')
        }else {
            $('.disable-on-turn-of').addClass('inactive')
            }
        }

        $('#mail-config-disable').on('change', function(){
            disableMailConf()
        })

        $('#mail-config-form').submit(function(){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('admin.business-settings.third-party.mail-config') }}",
                method: 'POST',
                data: $('#mail-config-form').serialize(),
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function() {
                    toastr.success('{{ 'configuración actualizada exitosamente' }}');
                    $('#sent-mail-modal').modal('show');
                },
                complete: function() {
                    $('#loading').hide();
                }
            });
        })
    </script>
@endpush
