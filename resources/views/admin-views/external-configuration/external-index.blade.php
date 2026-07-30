@extends('layouts.admin.app')

@section('title', 'Configuración e integración de viajes compartidos.')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
            <div>
                <h1 class="page-header-title m-0">
                    <span>
                        {{'Configuración e integración de viajes compartidos.'}}
                    </span>
                </h1>
                <p class="m-0">
                    {{'conectar el sistema drivemond con 6ammart'}}
                </p>
            </div>
            <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center" type="button" data-toggle="modal"
                 data-target="#how-it-works">
                <strong class="mr-2">{{'cómo funciona la configuración'}}</strong>
                <div>
                    <i class="tio-info-outined"></i>
                </div>
            </div>
        </div>
        <!-- Page Header -->

        <!-- End Page Header -->
        <form action="{{ route('admin.business-settings.external-system.update-drivemond-configuration') }}"
              method="post"
              enctype="multipart/form-data">
            @csrf
            <div class="row g-2">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @php($activationMode = \App\Models\ExternalConfiguration::where('key', 'activation_mode')->first())
                            @php($activationMode = $activationMode ? $activationMode->value : 0)
                            <div class="border rounded d-flex flex-wrap gap-2 align-items-center p-3 p-sm-4">
                                <div class="w-160px flex-grow-1">
                                    <h5>{{'Modo de activación'}}</h5>
                                    <p class="fs-12 m-0">
                                        {{'Habilite el interruptor para activar el software comprado: viaje compartido Drivemond en el sistema 6amMart. Debe ingresar la información correcta para asegurarse de que la funcionalidad funcione correctamente.'}}
                                    </p>
                                </div>
                                <label class="toggle-switch toggle-switch-sm">
                                    <input type="checkbox" value="1" class="toggle-switch-input" name="activation_mode"
                                           id="websocket" {{ $activationMode == 1 ? 'checked' : '' }}>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                            <div class="row g-4 mt-2">
                                <div class="col-md-12">
                                    @php($drivemondBaseUrl = \App\Models\ExternalConfiguration::where('key', 'drivemond_base_url')->first())
                                    <div class="p-3 p-sm-4 bg-soft-secondary rounded">
                                        <label class="form-label">{{ 'URL base del sistema de viajes compartidos' }}
                                            <i class="tio-info-outined text-primary"
                                               title="{{'Necesita obtener el software comprado: la URL base de Drivemond Ride Sharing para insertarla en este campo de entrada.'}}"
                                               data-toggle="tooltip"></i>
                                        </label>
                                        <input type="url" id="drivemondBaseUrl" name="drivemond_base_url"
                                               value="{{ $drivemondBaseUrl->value ?? '' }}"
                                               class="form-control"
                                               placeholder="{{ 'Ejemplo: https://drivemond.com' }}"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    @php($drivemondToken = \App\Models\ExternalConfiguration::where('key', 'drivemond_token')->first())
                                    <div class="p-3 p-sm-4 bg-soft-secondary rounded">
                                        <label class="form-label">{{ 'Token del sistema de viajes compartidos' }}
                                            <i class="tio-info-outined text-primary"
                                               title="{{'Desde el software adquirido: página de configuración e integración de comercio electrónico del panel de administración de Drivemond Ride Sharing, copie el token del sistema e insértelo en este campo de entrada.'}}"
                                               data-toggle="tooltip"></i>
                                        </label>
                                        <input id="drivemondToken" maxlength="64" minlength="64" type="text"
                                               value="{{ $drivemondToken->value ?? '' }}" name="drivemond_token"
                                               class="form-control"
                                               placeholder="{{ 'introduzca el token de drivemond' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    @php($systemSelfToken = \App\Models\ExternalConfiguration::where('key', 'system_self_token')->first())
                                    <div class="p-3 p-sm-4 bg-soft-secondary rounded">
                                        <label
                                            class="form-label">{{ (\App\CentralLogics\Helpers::get_business_data('business_name') ?? "6amMart" ) . ' ' .'Ficha del sistema' }}
                                            <i class="tio-info-outined text-primary"
                                               title="{{ 'Haga clic en el botón Generar token. Generará automáticamente el token del sistema 6amMart y lo insertará en el campo de entrada.' }}"
                                               data-toggle="tooltip"></i>
                                        </label>
                                        <div class="input-group input-token-group">
                                            <div class="position-relative">
                                                <input id="systemSelfToken" maxlength="64" minlength="64" type="text"
                                                       value="{{ $systemSelfToken->value ?? '' }}"
                                                       name="system_self_token" class="form-control"
                                                       placeholder="{{ 'generar token propio del sistema' }}"
                                                       required>
                                                <a href="javascript:void(0)" class="generate-code text-primary"
                                                   id="copyButton"><i class="tio-copy"></i> </a>
                                            </div>
                                            <a href="javascript:void(0)" class="btn btn--primary input-group-text"
                                               id="generateSystemSelfToken">{{'generar token'}} <i
                                                    class="tio-code"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end mt-20">
                                <button type="reset" id="reset_btn"
                                        class="btn btn--reset">{{ 'reiniciar' }}</button>
                                <button type="submit" id="submit"
                                        class="btn btn--primary">{{ 'guardar información' }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- How It Works Modal --}}
        <div class="modal fade how-it-works-modal" id="how-it-works">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header px-2 pt-2">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                    <div class="modal-body pb-5 pt-0 px-lg-5">
                        <h4 class="mb-3">{{'¿Cómo funciona?'}} ?</h4>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="">
                                    <img src="{{asset('assets/admin/img/how-it-works/Step-1.svg')}}" alt=""
                                         class="mb-20">
                                    <div class="how-it-count">
                                        <span>1</span>
                                    </div>
                                    <h5 class="mb-2">{{'Inserción de URL base del sistema de viajes compartidos'}}</h5>
                                    <p>
                                        {{'Al principio, es necesario insertar la URL base del software de implementación: viaje compartido de Drivemond.'}}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <img src="{{asset('assets/admin/img/how-it-works/step-3.svg')}}" alt=""
                                         class="mb-20">
                                    <div class="how-it-count">
                                        <span>2</span>
                                    </div>
                                    <h5 class="mb-2">{{'Entrada de token del sistema de viajes compartidos'}}</h5>
                                    <p>
                                        {{'Visite el viaje compartido de Drivemond'}} <a
                                            href="{{\App\CentralLogics\Helpers::get_external_data('drivemond_base_url')?  (\App\CentralLogics\Helpers::get_external_data('drivemond_base_url').'/admin/auth/login') : "#"}}"
                                            class="text-underline text-primary">{{'Panel de administración'}}</a>
                                        <br>
                                        {{'Vaya a "Sección de gestión empresarial → Configuración e integración de comercio electrónico"'}}
                                        <br>
                                        {{'Copiar lo generado'}}
                                        <strong>{{'Ficha del sistema Drivemond'}}</strong>{{'y péguelo aquí en el campo de entrada Token del sistema de viajes compartidos.'}}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <img src="{{asset('assets/admin/img/how-it-works/step-2.svg')}}" alt=""
                                         class="mb-20">
                                    <div class="how-it-count">
                                        <span>3</span>
                                    </div>
                                    <h5 class="mb-2">{{(\App\CentralLogics\Helpers::get_business_data('business_name') ?? "6amMart" ) . ' ' .'Generación de token del sistema'}}</h5>
                                    <p>
                                        {{'Por último, haga clic en el'}}
                                        <strong>{{'Generar ficha'}}</strong>
                                        {{'botón para la generación automática de tokens y péguelo en el campo de entrada de'}}
                                        {{(\App\CentralLogics\Helpers::get_business_data('business_name') ?? "6amMart" ) . ' ' .'Generación de token del sistema'}}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="pb-1">
                            <i class="text-dark">{{'Nota: Siga los mismos pasos en Drivemond para conectar exitosamente 6amMart con Drivemond'}}</i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
