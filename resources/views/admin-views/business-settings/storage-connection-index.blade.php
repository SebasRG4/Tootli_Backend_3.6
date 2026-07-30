@extends('layouts.admin.app')

@section('title', 'Conexión de almacenamiento')


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/captcha.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'configuración de credenciales de conexión de almacenamiento'}}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.third-party-links')
        </div>
        <!-- End Page Header -->
        <div class="card border-0">
            <div class="card-header card-header-shadow">
                <h5 class="card-title align-items-center">
                    {{'Configuración de conexión de almacenamiento'}}
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        @php($config=\App\CentralLogics\Helpers::get_business_settings('local_storage')??1)
                        <form action="{{route('admin.business-settings.third-party.storage_connection_update',['local_storage'])}}"
                              method="post" id="local_storage_status_form">
                            @csrf
                            <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    <span class="line--limit-1">
                                        {{'Almacenamiento local'}}
                                    </span>
                                    <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip" data-placement="right" data-original-title="{{'Si está habilitado, el sistema almacenará todos los archivos e imágenes en el almacenamiento local.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="Veg/non-veg toggle"> * </span>
                                </span>
                                <input type="hidden" name="toggle_type" value="local_storage">
                                <input
                                    type="checkbox" id="local_storage_status"
                                    data-id="local_storage_status"
                                    data-type="status"
                                    data-image-on="{{ asset('assets/admin/img/modal/local_storage.png') }}"
                                    data-image-off="{{ asset('assets/admin/img/modal/local_storage.png') }}"
                                    data-title-on="{{ 'Activando la opción de almacenamiento local' }}"
                                    data-title-off="{{ 'Desactivando la opción de almacenamiento local' }}"
                                    data-text-on="<p>{{ 'El sistema almacenará todos los archivos e imágenes en el almacenamiento local.' }}</p>"
                                    data-text-off="<p>{{ 'El sistema no almacenará todos los archivos e imágenes en el almacenamiento local' }}</p>"
                                    class="status toggle-switch-input dynamic-checkbox"
                                    name="status" value="1" {{$config?($config==1?'checked':''):''}}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </form>
                    </div>
                    <div class="col-md-4">
                        @php($config=\App\CentralLogics\Helpers::get_business_settings('3rd_party_storage'))
                        <form action="{{route('admin.business-settings.third-party.storage_connection_update',['3rd_party_storage'])}}"
                              method="post" id="3rd_party_storage_status_form">
                            @csrf
                            <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                <span class="pr-1 d-flex align-items-center switch--label">
                                    <span class="line--limit-1">
                                        {{'Almacenamiento de terceros'}}
                                    </span>
                                    <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip" data-placement="right" data-original-title="{{'Si está habilitado, el sistema almacenará todos los archivos e imágenes en un almacenamiento de terceros.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="Veg/non-veg toggle"> * </span>
                                </span>
                                <input type="hidden" name="toggle_type" value="3rd_party_storage">
                                <input
                                    type="checkbox" id="3rd_party_storage_status"
                                    data-id="3rd_party_storage_status"
                                    data-type="status"
                                    data-image-on="{{ asset('assets/admin/img/modal/3rd_party_storage.png') }}"
                                    data-image-off="{{ asset('assets/admin/img/modal/3rd_party_storage.png') }}"
                                    data-title-on="{{ 'Activando la opción de almacenamiento de terceros' }}"
                                    data-title-off="{{ 'Desactivando la opción de almacenamiento de terceros' }}"
                                    data-text-on="<p>{{ 'El sistema almacenará todos los archivos e imágenes en un almacenamiento de terceros' }}</p>"
                                    data-text-off="<p>{{ 'El sistema no almacenará todos los archivos e imágenes en un almacenamiento de terceros' }}</p>"
                                    class="status toggle-switch-input dynamic-checkbox"
                                    name="status" value="1" {{$config?($config==1?'checked':''):''}}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        @php($config=\App\CentralLogics\Helpers::get_business_settings('s3_credential'))
        <div class="card mt-3">
            <div class="p-4 card-header-shadow">
                <h4 class="card-title align-items-center">
                    {{'Credencial S3'}}
                </h4>
                <span>{{ 'El ID de clave de acceso es un identificador de acceso público que se utiliza para autenticar solicitudes a S3.' }} <a target="_blank" href="https://aws.amazon.com/s3/">{{ 'Más información' }}</a></span>            </div>
            <div class="card-body">
                <div class="mt-2 px-3">
                    <form
                        action="{{env('APP_MODE')!='demo'?route('admin.business-settings.third-party.storage_connection_update',['storage_connection']):'javascript:'}}"
                        method="post">
                        @csrf
                                <div class="border pt-5 radius-10 row mb-3">
                                    <div class="col-lg-4 col-sm-6 p-10px">
                                        <label for="key" class="form-label">{{'llave'}}</label>
                                    </div>
                                    <div class="col-lg-8 col-sm-6">
                                        <div class="form-group">
                                            <input required id="key" type="text" class="form-control mb-2" name="key"
                                                   value="{{env('APP_MODE')!='demo'?$config['key']??"":''}}">

                                        </div>
                                    </div>
                                </div>
                                <div class="border pt-5 radius-10 row mb-3">
                                    <div class="col-lg-4 col-sm-6 p-10px">
                                        <label for="secret" class="form-label">{{'secreto'}}</label>
                                    </div>
                                    <div class="col-lg-8 col-sm-6">
                                        <div class="form-group">
                                            <input required id="secret" type="text" class="form-control mb-2" name="secret"
                                                   value="{{env('APP_MODE')!='demo'?$config['secret']??"":''}}">

                                        </div>
                                    </div>
                                </div>
                                <div class="border pt-5 radius-10 row mb-3">
                                    <div class="col-lg-4 col-sm-6 p-10px">
                                        <label for="region" class="form-label">{{'región'}}</label>
                                    </div>
                                    <div class="col-lg-8 col-sm-6">
                                        <div class="form-group">
                                            <input required id="region" type="text" class="form-control mb-2" name="region"
                                                   value="{{env('APP_MODE')!='demo'?$config['region']??"":''}}">

                                        </div>
                                    </div>
                                </div>
                                <div class="border pt-5 radius-10 row mb-3">
                                    <div class="col-lg-4 col-sm-6 p-10px">
                                        <label for="bucket" class="form-label">{{'balde'}}</label>
                                    </div>
                                    <div class="col-lg-8 col-sm-6">
                                        <div class="form-group">
                                            <input required id="bucket" type="text" class="form-control mb-2" name="bucket"
                                                   value="{{env('APP_MODE')!='demo'?$config['bucket']??"":''}}">

                                        </div>
                                    </div>
                                </div>
                                <div class="border pt-5 radius-10 row mb-3">
                                    <div class="col-lg-4 col-sm-6 p-10px">
                                        <label for="url" class="form-label">{{'URL'}}</label>
                                    </div>
                                    <div class="col-lg-8 col-sm-6">
                                        <div class="form-group">
                                            <input required id="url" type="text" class="form-control mb-2" name="url"
                                                   value="{{env('APP_MODE')!='demo'?$config['url']??"":''}}">

                                        </div>
                                    </div>
                                </div>
                                <div class="border pt-5 radius-10 row mb-3">
                                    <div class="col-lg-4 col-sm-6 p-10px">
                                        <label for="end_point" class="form-label">{{'punto final'}}</label>
                                    </div>
                                    <div class="col-lg-8 col-sm-6">
                                        <div class="form-group">
                                            <input required id="end_point" type="text" class="form-control mb-2" name="end_point"
                                                   value="{{env('APP_MODE')!='demo'?$config['end_point']??"":''}}">

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
    </div>



@endsection
