@extends('layouts.admin.app')

@section('title', 'Configuración de inicio de sesión social')


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/captcha.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'Configuración de inicio de sesión social'}}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.third-party-links')
        </div>
        <!-- End Page Header -->

        <div class="row g-3">
            @if (isset($socialLoginServices))
            @foreach ($socialLoginServices as $socialLoginService)
                    <div class="col-md-6">
                        <form
                        action="{{route('admin.social-login.update',[$socialLoginService['login_medium']])}}"
                        method="post">
                        @csrf
                        <div class="card">
                            <div class="card-header card-header-shadow">
                                <h5 class="card-title align-items-center">
                                    <img src="{{asset('assets/admin/img')}}/{{$socialLoginService['login_medium']}}.png" class="mr-1 w-20" alt="">
                                    {{translate('messages.'.$socialLoginService['login_medium'])}}
                                </h5>
                                <label class="toggle-switch toggle-switch-sm p-0">
                                    <span class="d-flex align-items-center switch--label">
                                        <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip" data-placement="right" data-original-title="Lorem ipsum dolor set amet"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="Veg/non-veg toggle"> * </span>
                                    </span>
                                    <input id="{{$socialLoginService['login_medium']}}_status"

                                           data-id="{{$socialLoginService['login_medium']}}_status"
                                           data-type="toggle"
                                           data-image-on="{{asset('assets/admin/img/modal')}}/{{$socialLoginService['login_medium']}}-on.png"
                                           data-image-off="{{asset('assets/admin/img/modal')}}/{{$socialLoginService['login_medium']}}-off.png"
                                           data-title-on="{{'\'.$socialLoginService[\'medio de inicio de sesión\'])}} {{translate(\'Inicio de sesión activado'}}"
                                           data-title-off="{{'\'.$socialLoginService[\'medio de inicio de sesión\'])}} {{translate(\'Inicio de sesión desactivado'}}"
                                           data-text-on="<p>{{'\'.$socialLoginService[\'medio de inicio de sesión\'])}} {{translate(\'El inicio de sesión ahora está habilitado. Los clientes podrán registrarse o iniciar sesión usando sus cuentas de redes sociales.'}}</p>"
                                           data-text-off="<p>{{'\'.$socialLoginService[\'medio de inicio de sesión\'])}} {{translate(\'El inicio de sesión ahora está deshabilitado. Los clientes no podrán registrarse ni iniciar sesión usando sus cuentas de redes sociales. Tenga en cuenta que esto puede afectar la experiencia del usuario y el proceso de registro/inicio de sesión.'}}</p>"
                                           class="status toggle-switch-input dynamic-checkbox-toggle"


                                           type="checkbox" name="status" value="1" {{$socialLoginService['status']==1?'checked' :''}}>
                                    <span class="toggle-switch-label text p-0">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-end">
                                    <div class="text--primary-2 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#{{$socialLoginService['login_medium']}}-modal">
                                        <strong class="mr-2 text--underline">{{'Configuración de credenciales'}}</strong>
                                        <div class="blinkings">
                                            <i class="tio-info-outined"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{'uri de devolución de llamada'}}</label>
                                    <div class="position-relative">
                                        <span class="btn-right-fixed copy-to-clipboard" data-id="#id_{{$socialLoginService['login_medium']}}"><i class="tio-copy"></i></span>
                                        <span class="form-control h-unset" id="id_{{$socialLoginService['login_medium']}}">{{ url('/') }}/customer/auth/login/{{$socialLoginService['login_medium']}}/callback</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="client_id" class="form-label">{{'identificación del cliente'}}</label>
                                    <input id="client_id" type="text" class="form-control" name="client_id" value="{{ $socialLoginService['client_id'] }}">
                                </div>
                                <div class="form-group">
                                    <label for="client_secret"
                                        class="form-label">{{'secreto del cliente'}}</label>
                                    <input id="client_secret" type="text" class="form-control" name="client_secret"
                                            value="{{ $socialLoginService['client_secret'] }}">
                                </div>
                                <div class="btn--container justify-content-end">
                                    <button type="reset" class="btn btn--reset mb-2">{{'Reiniciar'}}</button>
                                    <button type="submit" class="btn btn--primary mb-2 call-demo">{{'ahorrar'}}</button>
                                </div>
                                </div>
                            </div>
                        </form>
                    </div>
            @endforeach
            @endif
            @if (isset($appleLoginServices))
            @foreach ($appleLoginServices as $appleLoginService)
                    <div class="col-md-6">
                        <div class="card">
                            <form
                            action="{{route('admin.apple-login.update',[$appleLoginService['login_medium']])}}"
                            method="post" enctype="multipart/form-data">
                            @csrf
                                <div class="card-header card-header-shadow">
                                    <h5 class="card-title align-items-center">
                                        <img src="{{asset('assets/admin/img/apple.png')}}" class="mr-1 w--20" alt="">
                                        {{translate('messages.'.$appleLoginService['login_medium'])}}
                                    </h5>
                                    <label class="toggle-switch toggle-switch-sm p-0">
                                        <span class="d-flex align-items-center switch--label">
                                            <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip" data-placement="right" data-original-title="Lorem ipsum dolor set amet"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="Veg/non-veg toggle"> * </span>
                                        </span>
                                        <input  id="{{$appleLoginService['login_medium']}}_status"
                                               data-id="{{$appleLoginService['login_medium']}}_status"
                                               data-type="toggle"
                                               data-image-on="{{asset('assets/admin/img/modal')}}/{{$appleLoginService['login_medium']}}-on.png"
                                               data-image-off="{{asset('assets/admin/img/modal')}}/{{$appleLoginService['login_medium']}}-off.png"
                                               data-title-on="{{'\'.$appleLoginService[\'medio de inicio de sesión\'])}} {{translate(\'Inicio de sesión activado'}}"
                                               data-title-off="{{'\'.$appleLoginService[\'medio de inicio de sesión\'])}} {{translate(\'Inicio de sesión desactivado'}}"
                                               data-text-on="<p>{{'\'.$appleLoginService[\'medio de inicio de sesión\'])}} {{translate(\'El inicio de sesión ahora está habilitado. Los clientes podrán registrarse o iniciar sesión usando sus cuentas de redes sociales.'}}</p>"
                                               data-text-off="<p>{{'\'.$appleLoginService[\'medio de inicio de sesión\'])}} {{translate(\'El inicio de sesión ahora está deshabilitado. Los clientes no podrán registrarse ni iniciar sesión usando sus cuentas de redes sociales. Tenga en cuenta que esto puede afectar la experiencia del usuario y el proceso de registro/inicio de sesión.'}}</p>"
                                               class="status toggle-switch-input dynamic-checkbox-toggle"


                                               type="checkbox" name="status" value="1" {{$appleLoginService['status']==1?'checked' :''}}>
                                        <span class="toggle-switch-label text p-0">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                                <div class="card-body text-{{Session::get('direction') === "rtl" ? 'right' : 'left'}}">
                                    <div class="d-flex justify-content-end">
                                        <div class="text--primary-2 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#{{$appleLoginService['login_medium']}}-modal">
                                            <strong class="mr-2 text--underline">{{'Configuración de credenciales'}}</strong>
                                            <div class="blinkings">
                                                <i class="tio-info-outined"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="client_id"
                                            class="form-label">{{'ID de cliente para web'}}</label>
                                        <input id="client_id" type="text" class="form-control" name="client_id"
                                            value="{{ $appleLoginService['client_id'] }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="client_id_app"
                                            class="form-label">{{'ID de cliente para la aplicación'}}</label>
                                        <input id="client_id_app" type="text" class="form-control" name="client_id_app"
                                            value="{{ $appleLoginService['client_id_app']??'' }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="team_id"
                                            class="form-label">{{'identificación del equipo'}}</label>
                                        <input id="team_id" type="text" class="form-control" name="team_id"
                                            value="{{ $appleLoginService['team_id'] }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="key_id"
                                            class="form-label">{{'identificación clave'}}</label>
                                        <input id="key_id" type="text" class="form-control" name="key_id"
                                            value="{{ $appleLoginService['key_id'] }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="key_id"
                                            class="form-label">{{'URL de redireccionamiento para Flutter Web'}}</label>
                                        <input id="redirect_url_flutter" type="url" class="form-control" name="redirect_url_flutter"
                                            value="{{ $appleLoginService['redirect_url_flutter']??'' }}">
                                    </div>
                                    <div class="form-group">
                                        <label for="key_id"
                                            class="form-label">{{'URL de redireccionamiento para reaccionar web'}}</label>
                                        <input id="redirect_url_react" type="url" class="form-control" name="redirect_url_react"
                                            value="{{ $appleLoginService['redirect_url_react']??'' }}">
                                    </div>
                                    <div class="form-group">
                                        <label
                                            class="form-label">{{'archivo de servicio'}} {{ $appleLoginService['service_file']?'(Ya existe)':'' }}</label>
                                        <input type="file" accept=".p8" class="form-control" name="service_file"
                                            value="{{ 'storage/app/public/apple-login/'.$appleLoginService['service_file'] }}">
                                    </div>
                                    <div class="btn--container justify-content-end">
                                        <button type="reset" class="btn btn--reset mb-2">{{'Reiniciar'}}</button>
                                        <button type="submit" class="btn btn--primary mb-2 call-demo">{{'ahorrar'}}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
            @endforeach
            @endif
        </div>
    </div>

        <!-- Google -->
        <div class="modal fade" id="google-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog status-warning-modal">
                <div class="modal-content {{Session::get('direction') === "rtl" ? 'text-right' : 'text-left'}}">
                    <div class="modal-header pb-0">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pb-0">
                        <div class="text-center mb-20">
                            <img src="{{asset('assets/admin/img/modal/google.png')}}" alt="" class="mb-20">
                            <h5 class="modal-title">{{'instrucciones de configuración de la API de Google'}}</h5>
                        </div>
                        <ol>
                            <li>{{'ir a la página de credenciales'}} ({{'hacer clic'}} <a href="https://console.cloud.google.com/apis/credentials" target="_blank">{{'aquí'}}</a>)</li>
                            <li>{{'hacer clic'}} <b>{{'crear credenciales'}}</b> > <b>{{'identificación del cliente de autenticación'}}</b>.</li>
                            <li>{{'seleccione el'}} <b>{{'aplicación web'}}</b> {{'tipo'}}.</li>
                            <li>{{'nombra tu cliente de autenticación'}}</li>
                            <li>{{'hacer clic'}} <b>{{'agregar uri'}}</b> {{'de'}} <b>{{'uris de redireccionamiento autorizados'}}</b> , {{'proporcionar el'}} <code>{{'uri de devolución de llamada'}}</code> {{'desde abajo y haga clic'}} <b>{{'creado'}}</b></li>
                            <li>{{'Copiar'}} <b>{{'identificación del cliente'}}</b> {{'y'}} <b>{{'secreto del cliente'}}</b>, {{'pasado en el campo de entrada a continuación y'}} <b>Save</b>.</li>
                        </ol>
                    </div>
                    <div class="modal-footer justify-content-center border-0">
                        <button type="button" class="btn btn--primary w-100 mw-300px" data-dismiss="modal">{{'Entiendo'}}</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Facebook -->
        <div class="modal fade" id="facebook-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog status-warning-modal">
                <div class="modal-content {{Session::get('direction') === "rtl" ? 'text-right' : 'text-left'}}">
                    <div class="modal-header pb-0">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pb-0"><b></b>
                        <div class="text-center mb-20">
                            <img src="{{asset('assets/admin/img/modal/facebook.png')}}" alt="" class="mb-20">
                            <h5 class="modal-title">{{'instrucción de configuración de API de Facebook'}}</h5>
                        </div>
                        <ol>
                            <li>{{'ir a la página de desarrollador de Facebook'}} (<a href="https://developers.facebook.com/apps/" target="_blank">{{'haga clic aquí'}}</a>)</li>
                            <li>{{'ir a'}} <b>{{'empezar'}}</b> {{'desde la barra de navegación'}}</li>
                            <li>{{'desde la pestaña de registro presione'}} <b>{{'continuar'}}</b> <small>({{'si es necesario'}})</small></li>
                            <li>{{'proporcione el correo electrónico principal y presione'}} <b>{{'Confirmar correo electrónico'}}</b> <small>({{'si es necesario'}})</small></li>
                            <li>{{'en la sección acerca de seleccionar'}} <b>{{'otro'}}</b> {{'y presione'}} <b>{{'registro completo'}}</b></li>

                            <li><b>{{'crear aplicación'}}</b> > {{'seleccione un tipo de aplicación y presione'}} <b>{{'próximo'}}</b></li>
                            <li>{{'Complete el formulario de detalles y presione'}} <b>{{'crear aplicación'}}</b></li><br/>

                            <li>{{'forma'}} <b>{{'iniciar sesión en facebook'}}</b> {{'prensa'}} <b>{{'configuración'}}</b></li>
                            <li>{{'seleccionar'}} <b>{{'web'}}</b></li>
                            <li>{{'proporcionar'}} <b>{{'URL del sitio'}}</b> <small>({{'URL base del sitio.'}}: https://example.com)</small> > <b>{{'ahorrar'}}</b></li><br/>
                            <li>{{'ahora ve a'}} <b>{{'configuración'}}</b> {{'forma'}} <b>{{'iniciar sesión en facebook'}}</b> ({{'barra lateral izquierda'}})</li>
                            <li>{{'asegúrese de comprobar'}} <b>{{'inicio de sesión de autenticación del cliente'}}</b> <small>({{'debe en'}})</small></li>
                            <li>{{'proporcionar'}} <code>{{'uris de redirección de autenticación válida'}}</code> {{'desde abajo y haga clic'}} <b>{{'guardar cambios'}}</b></li>

                            <li>{{'ahora ve a'}} <b>{{'configuración'}}</b> ({{'desde la barra lateral izquierda'}}) > <b>{{'básico'}}</b></li>
                            <li>{{'llena el formulario y presiona'}} <b>{{'guardar cambios'}}</b></li>
                            <li>{{'ahora copia'}} <b>{{'identificación del cliente'}}</b> & <b>{{'secreto del cliente'}}</b>, {{'pasado en el campo de entrada a continuación y'}} <b>{{'ahorrar'}}</b>.</li>
                        </ol>
                    </div>
                    <div class="modal-footer justify-content-center border-0">
                        <button type="button" class="btn btn--primary w-100 mw-300px" data-dismiss="modal">{{'Entiendo'}}</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Apple -->
        <div class="modal fade" id="apple-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog status-warning-modal">
                <div class="modal-content {{Session::get('direction') === "rtl" ? 'text-right' : 'text-left'}}">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pb-0"><b></b>
                        <div class="text-center mb-20">
                            <img src="{{asset('assets/admin/img/modal/apple.png')}}" alt="" class="mb-20">
                            <h5 class="modal-title">{{'instrucción del conjunto de API de Apple'}}</h5>
                        </div>
                        <ol>
                            <li>{{'Ir a la página de desarrollador de Apple'}} (<a href="https://developer.apple.com/account/resources/identifiers/list" target="_blank">{{'haga clic aquí'}}</a>)</li>
                            <li>{{'Aquí en la esquina superior izquierda puedes ver el'}} <b>{{ 'ID del equipo' }}</b> {{ '[Nombre de la cuenta de desarrollador de Apple: ID del equipo]'}}</li>
                            <li>{{'Haga clic en el ícono Más -> seleccione ID de aplicaciones -> haga clic en Continuar'}}</li>
                            <li>{{'Ponga una descripción y también un identificador (identificador que se usó para la aplicación) y este es el'}} <b>{{ 'ID de cliente' }}</b> </li>
                            <li>{{'Haga clic en Continuar y descargue el archivo en el dispositivo llamado AuthKey ID.p8 (guárdelo de forma segura y se utilizará para notificaciones automáticas).'}} </li>
                            <li>{{'Nuevamente haga clic en el ícono Más -> seleccione ID de servicio -> haga clic en Continuar'}} </li>
                            <li>{{'Introduzca una descripción y también un identificador y Continuar'}} </li>
                            <li>{{'Descargue el archivo en el dispositivo llamado'}} <b>{{ 'ID de clave de autenticación.p8' }}</b> {{'[Este es el archivo de ID de clave de servicio y también después de AuthKey que es el ID de clave]'}}</li>
                        </ol>
                    </div>
                    <div class="modal-footer justify-content-center border-0">
                        <button type="button" class="btn btn--primary w-100 mw-300px" data-dismiss="modal">{{'Entiendo'}}</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Twitter -->
        <div class="modal fade" id="twitter-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content {{Session::get('direction') === "rtl" ? 'text-right' : 'text-left'}}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">{{'instrucciones de configuración de la API de Twitter'}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body"><b></b>
                        {{'La instrucción estará disponible muy pronto.'}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--primary" data-dismiss="modal">{{'cerca'}}</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Modal Ends--}}



@endsection
@push('script_2')
    <script>
        "use strict";
        $(document).on('click', '.copy-to-clipboard', function () {
            let id=  $(this).data('id');
            let $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(id).text()).select();
            document.execCommand("copy");
            $temp.remove();
            toastr.success("{{'Copiado al portapapeles'}}");

        });

    </script>

@endpush
