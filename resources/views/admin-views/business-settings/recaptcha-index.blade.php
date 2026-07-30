@extends('layouts.admin.app')

@section('title', 'Configuración de reCaptcha')


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/captcha.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'Configuración de credenciales reCaptcha'}}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.third-party-links')
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-header">
                <h4 class="m-0">
                    {{'Información recaptcha de Google'}}
                </h4>
                <button type="button" class="btn btn--primary btn-outline-primary btn-sm px-3" data-toggle="modal" data-target="#setup-information">
                    {{'Información de configuración de credenciales'}} <i class="tio-info"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-soft-secondary">
                    <div class="d-flex gap-2">
                        <div class="w-0 flex-grow-1">
                            <h4 class="m-0">{{ 'La versión V3 ya está disponible. Debe configurarse para ReCAPTCHA V3' }}</h4>
                            <div>{{ 'Debe configurar para la versión V3. De lo contrario, el reCAPTCHA predeterminado se mostrará automáticamente' }}</div>
                        </div>
                        <div>
                            <button type="button" class="btn p-0 text-danger" data-dismiss="alert">
                                <i class="tio-clear"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @php($config=\App\CentralLogics\Helpers::get_business_settings('recaptcha'))
                <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.third-party.recaptcha_update',['recaptcha']):'javascript:'}}" method="post">
                    @csrf
                    <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-4">
                        <span class="pr-1 d-flex align-items-center switch--label">
                            <span class="line--limit-1">
                                @if (isset($config) && $config['status'] == 1)
                                {{'Estado de ReCAPTCHA Apagado'}}
                                @else
                                {{'Estado de ReCAPTCHA activado'}}
                                @endif
                            </span>
                        </span>
                        <input type="checkbox"
                                data-id="recaptcha_status"
                                data-type="toggle"
                                data-image-on="{{ asset('assets/admin/img/modal/important-recapcha.png') }}"
                                data-image-off="{{ asset('assets/admin/img/modal/warning-recapcha.png') }}"
                                data-title-on="{{ '¡Importante!' }}"
                                data-title-off="{{ '¡Advertencia!' }}"
                                data-text-on="<p>{{ 'reCAPTCHA ahora está habilitado para mayor seguridad. Es posible que se solicite a los usuarios que completen un desafío reCAPTCHA para verificar su identidad humana y protegerse contra spam y actividades maliciosas.' }}</p>"
                                data-text-off="<p>{{ 'Deshabilitar reCAPTCHA puede dejar su sitio web vulnerable al spam y a actividades maliciosas y sospechar que un usuario puede ser un bot. Se recomienda encarecidamente mantener reCAPTCHA habilitado para garantizar la seguridad e integridad de su sitio web.' }}</p>"
                                class="status toggle-switch-input dynamic-checkbox-toggle"
                                name="status" id="recaptcha_status" value="1" {{isset($config) && $config['status'] == 1 ? 'checked':''}}>
                        <span class="toggle-switch-label text p-0">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="site_key" class="form-label">{{'Clave del sitio'}}</label><br>
                                <input id="site_key" type="text" class="form-control" name="site_key"
                                        value="{{env('APP_MODE')!='demo'?$config['site_key']??"":''}}">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="site_key" class="form-label">{{'clave secreta'}}</label><br>
                                <input id="site_key" type="text" class="form-control" name="secret_key"
                                        value="{{env('APP_MODE')!='demo'?$config['secret_key']??"":''}}">
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn--primary call-demo">{{'ahorrar'}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="setup-information" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-0">
                    <div class="text-center">
                        <img src="{{asset('assets/admin/img/icons/wallet.png')}}" width="80" alt="">
                    </div>
                    <h4 class="modal-title">Instructions</h4>
                    <ol class="list-gap-5 fs-13 mt-3">
                        <li>{{'Ir a la página de Credenciales'}}
                            ({{'Hacer clic'}} <a
                                href="https://www.google.com/recaptcha/admin/create"
                                target="_blank">{{'aquí'}}</a>)
                        </li>
                        <li>{{'Añadir un'}}
                            <b>{{'etiqueta'}}</b> {{'(Ejemplo: etiqueta de prueba)'}}
                        </li>
                        <li>
                            {{'Seleccione reCAPTCHA v3 como'}}
                            <b>{{'Tipo reCAPTCHA'}}</b>
                            ({{'Subtipo: No soy un robot Casilla de verificación'}}
                            )
                        </li>
                        <li>
                            {{'Agregar'}}
                            <b>{{'dominio'}}</b>
                            {{'(Por ejemplo: demo.6amtech.com)'}}
                        </li>
                        <li>
                            {{'Registrarse'}}
                            <b>{{'Acepta los Términos de Servicio de reCAPTCHA'}}</b>
                        </li>
                        <li>
                            {{'Prensa'}}
                            <b>{{'Entregar'}}</b>
                        </li>
                        <li>{{'Copiar'}} <b>{{ 'Sitio' }}
                                {{ 'Llave' }}</b> {{'y'}} <b>{{ 'Secreto' }}
                                {{ 'Llave' }}</b>, {{'pegue la entrada archivada a continuación y'}}
                            <b>{{ 'Ahorrar' }}</b>.
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


@endsection
