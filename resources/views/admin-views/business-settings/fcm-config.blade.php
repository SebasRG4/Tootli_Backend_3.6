@extends('layouts.admin.app')

@section('title','Configuración de FCM')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/firebase.png')}}" class="w--26" alt="">
                </span>
                <span>{{'configuración de notificaciones push de Firebase'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <?php
        $mod_type = 'grocery';
        if(request('module_type')){
            $mod_type = request('module_type');
        }
        ?>
        <div class="card">
            <div class="card-header card-header-shadow pb-0">
                <div class="d-flex flex-wrap justify-content-between w-100 row-gap-1">
                    <ul class="nav nav-tabs nav--tabs border-0 gap-2">
                        <li class="nav-item mr-2 mr-md-4">
                            <a href="{{ route('admin.business-settings.fcm-index') }}" class="nav-link pb-2 px-0 pb-sm-3" data-slide="1">
                                <img src="{{asset('assets/admin/img/notify.png')}}" alt="">
                                <span>{{'Notificación push'}}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.business-settings.fcm-config') }}" class="nav-link pb-2 px-0 pb-sm-3 active" data-slide="2">
                                <img src="{{asset('assets/admin/img/firebase2.png')}}" alt="">
                                <span>{{'Configuración de base de fuego'}}</span>
                            </a>
                        </li>
                    </ul>
                    <div class="py-1">
                        <div class="tab--content">
                            <div class="item show text--primary-2 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#firebase-modal">
                                <strong class="mr-2">{{'Dónde obtener esta información'}}</strong>
                                <div class="blinkings">
                                    <i class="tio-info-outined"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="firebase">
                        <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.update-fcm'):'javascript:'}}" method="post"
                                enctype="multipart/form-data">
                            @csrf
{{--                            @php($key=\App\Models\BusinessSetting::where('key','push_notification_key')->first())--}}
{{--                            <div class="form-group">--}}
{{--                                <label class="input-label"--}}
{{--                                        for="push_notification_key">{{'clave del servidor'}}</label>--}}
{{--                                <textarea id="push_notification_key" name="push_notification_key" class="form-control" placeholder="{{'Ej: AAAAaBcDeFgHiJkLmNoPqRsTuVwXyZ0123456789'}}"--}}
{{--                                            required>{{env('APP_MODE')!='demo'?$key->value??'':''}}</textarea>--}}
{{--                            </div>--}}
                            @php($serviceFileContent = \App\CentralLogics\Helpers::get_business_settings('push_notification_service_file_content'))
                            <div class="form-group">
                                <label class="input-label">{{'contenido del archivo de servicio'}}
                                    <i class="tio-info cursor-pointer" data-toggle="tooltip" data-placement="top"
                                       title="{{ 'seleccione y copie todo el contenido del archivo de servicio y agréguelo aquí' }}">
                                    </i>
                                </label>
                                <textarea name="push_notification_service_file_content" class="form-control" rows="15"
                                          required>{{env('APP_MODE')!='demo'?($serviceFileContent?json_encode($serviceFileContent):''):''}}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="input-label" for="apiKey">{{'clave API'}}</label>
                                <div class="d-flex">
                                    <input type="text" id="apiKey" value="{{$fcm_credentials['apiKey']??''}}"
                                        name="apiKey" class="form-control" placeholder="{{ 'Ej: abcd1234efgh5678ijklmnop90qrstuvwxYZ' }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4 col-sm-6">
                                    @php($project_id=\App\Models\BusinessSetting::where('key','fcm_project_id')->first())
                                    <div class="form-group">
                                        <label class="input-label" for="projectId">{{'ID del proyecto FCM'}}</label>
                                        <div class="d-flex">
                                            <input id="projectId" type="text" value="{{$project_id->value??''}}"
                                                name="projectId" class="form-control" placeholder="{{ 'Ej: mi-aplicación-increíble-12345' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label  class="input-label" for="authDomain">{{'dominio de autenticación'}}</label>
                                        <div class="d-flex">
                                            <input id="authDomain" type="text" value="{{$fcm_credentials['authDomain']??''}}"
                                                name="authDomain" class="form-control" placeholder="{{ 'Ej: mi-awesome-app.firebase.com' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="storageBucket">{{'cubo de almacenamiento'}}</label>
                                        <div class="d-flex">
                                            <input id="storageBucket" type="text" value="{{$fcm_credentials['storageBucket']??''}}"
                                                name="storageBucket" class="form-control" placeholder="{{ 'Ej: mi-awesome-app.apps.com' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="messagingSenderId">{{'ID del remitente de mensajes'}}</label>
                                        <div class="d-flex">
                                            <input id="messagingSenderId" type="text" value="{{$fcm_credentials['messagingSenderId'] ?? ''}}"
                                                name="messagingSenderId" class="form-control" placeholder="{{ 'Ej: 1234567890' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="appId">{{'identificación de la aplicación'}}</label>
                                        <div class="d-flex">
                                            <input id="appId" type="text" value="{{$fcm_credentials['appId']??''}}"
                                                name="appId" class="form-control" placeholder="{{ 'Ej: 9876543210' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="measurementId">{{'identificación de medición'}}</label>
                                        <div class="d-flex">
                                            <input id="measurementId" type="text" value="{{$fcm_credentials['measurementId']??''}}"
                                                name="measurementId" class="form-control" placeholder="{{ 'Ej: F-12345678' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="btn--container justify-content-end">
                                <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn--primary call-demo">{{'entregar'}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Firebase Modal -->
        <div class="modal fade" id="firebase-modal">
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
                                        <img src="{{asset('assets/admin/img/firebase/slide-1.png')}}" alt="" class="mb-20">
                                        <h5 class="modal-title">{{'Ir a la consola de Firebase'}}</h5>
                                    </div>
                                    <ul>
                                        <li>
                                            {{'Abra su navegador web y vaya a Firebase Console'}}
                                            <a href="#" class="text--underline">
                                                {{'(https://console.firebase.google.com/)'}}
                                            </a>
                                        </li>
                                        <li>
                                            {{'Seleccione el proyecto para el que desea configurar FCM desde el panel de Firebase Console.'}}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="item">
                                <div class="mb-20">
                                    <div class="text-center">
                                        <img src="{{asset('assets/admin/img/firebase/slide-2.png')}}" alt="" class="mb-20">
                                        <h5 class="modal-title">{{'Vaya a Configuración del proyecto'}}</h5>
                                    </div>
                                    <ul>
                                        <li>
                                            {{'En el menú de la izquierda, haga clic en el ícono de ajustes "Configuración" y luego seleccione "Configuración del proyecto" en el menú desplegable.'}}
                                        </li>
                                        <li>
                                            {{'En la página de configuración del proyecto, haga clic en la pestaña "Mensajería en la nube" en el menú superior.'}}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="item">
                                <div class="mb-20">
                                    <div class="text-center">
                                        <img src="{{asset('assets/admin/img/firebase/slide-3.png')}}" alt="" class="mb-20">
                                        <h5 class="modal-title">{{'¡Obtenga toda la información solicitada!'}}</h5>
                                    </div>
                                    <ul>
                                        <li>
                                            {{'En la página de configuración del Proyecto Firebase, haga clic en la pestaña "General" en el menú superior.'}}
                                        </li>
                                        <li>
                                            {{'En la sección "Tus aplicaciones", haz clic en la aplicación "Web" para la que deseas configurar FCM.'}}
                                        </li>
                                        <li>
                                            {{'Luego obtenga la clave API, el ID del proyecto FCM, el dominio de autenticación, el depósito de almacenamiento y el ID del remitente de mensajes.'}}
                                        </li>
                                    </ul>
                                    <p>
                                        {{'Nota: asegúrese de utilizar la información obtenida de forma segura y de acuerdo con la documentación, los términos de servicio y las leyes y regulaciones aplicables de Firebase y FCM.'}}
                                    </p>
                                    <div class="btn-wrap">
                                        <button type="submit" class="btn btn--primary w-100" data-dismiss="modal" data-toggle="modal" data-target="#firebase-modal-2">{{'Entiendo'}}</button>
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

    </div>
@endsection

