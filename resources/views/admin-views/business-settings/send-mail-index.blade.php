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
                            <a href="{{route('admin.business-settings.third-party.mail-config')}}" class="nav-link pb-2 px-0 pb-sm-3">
                                <img src="{{asset('assets/admin/img/mail-config.png')}}" alt="">
                                <span>{{'Configuración de correo'}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('admin.business-settings.third-party.test')}}" class="nav-link pb-2 px-0 pb-sm-3 active">
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
                    <div class="tab-pane fade show active" id="test-mail">
                        <div class="row">
                            <div class="col-lg-8">
                                <form class="" action="javascript:">
                                    <label class="form-label">{{'Correo electrónico'}}</label>
                                    <div class="row gx-3 gy-1">
                                        <div class="col-md-8 col-sm-7">
                                            <div>
                                                <label for="test-email" class="sr-only">
                                                    {{ 'correo' }}</label>
                                                <input type="email" id="test-email" class="form-control"
                                                    placeholder="{{ 'Ex:' }} jhon@email.com">
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-sm-5">
                                            <button type="button"  class="btn btn--primary h--45px btn-block send-mail" data-toggle="modal" data-target="#sent-mail-modal">
                                                <i class="tio-telegram"></i>
                                                {{ 'enviar correo' }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
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
        function ValidateEmail(inputText) {
            let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
            return !!inputText.match(mailformat);
        }

        $(document).on('click', '.send-mail', function () {
            @if(env('APP_MODE') =='demo')
            toastr.info('{{ '¡La opción de actualización está deshabilitada para la demostración!' }}', {
                CloseButton: true,
                ProgressBar: true
            });
            @else

            if (ValidateEmail($('#test-email').val())) {
                Swal.fire({
                    title: '{{ '¿Está seguro?' }}?',
                    text: "{{ 'Se enviará un correo de prueba a su correo electrónico.' }}!",
                    showCancelButton: true,
                    confirmButtonColor: '#00868F',
                    cancelButtonColor: 'secondary',
                    confirmButtonText: '{{ 'Sí' }}!'
                }).then((result) => {
                    if (result.value) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url: "{{ route('admin.business-settings.third-party.mail.send') }}",
                            method: 'GET',
                            data: {
                                "email": $('#test-email').val()
                            },
                            beforeSend: function() {
                                $('#loading').show();
                            },
                            success: function(data) {
                                if (data.success === 2) {
                                    toastr.error(
                                        '{{ 'error de configuración de correo electrónico' }} !!'
                                    );
                                } else if (data.success === 1) {
                                    toastr.success(
                                        '{{ 'correo electrónico configurado perfectamente!' }}!'
                                    );
                                } else {
                                    toastr.info(
                                        '{{ 'el estado del correo electrónico no está activo' }}!'
                                    );
                                }
                            },
                            complete: function() {
                                $('#loading').hide();

                            }
                        });
                    }
                })
            } else {
                toastr.error('{{ 'dirección de correo electrónico no válida' }} !!');
            }

            @endif

        });

    </script>
@endpush
