@extends('layouts.admin.app')

@section('title', 'API de terceros')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/api.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'API de terceros'}}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.third-party-links')
        </div>
        <!-- End Page Header -->
        <div class="card">
        @php($map_api_key=\App\Models\BusinessSetting::where(['key'=>'map_api_key'])->first())
        @php($map_api_key=$map_api_key?$map_api_key->value:null)

        @php($map_api_key_server=\App\Models\BusinessSetting::where(['key'=>'map_api_key_server'])->first())
        @php($map_api_key_server=$map_api_key_server?$map_api_key_server->value:null)
            <div class="card-header card-header-shadow border-0 align-items-center">
                <h5 class="card-title align-items-center text--title">
                    {{'Configuración de la API de mapas de Google'}}
                </h5>
                <div class="blinkings active lg-top">
                    <i class="tio-info-outined"></i>
                    <div class="business-notes">
                        <h6><img src="{{asset('assets/admin/img/notes.png')}}" alt=""> {{'Nota'}}</h6>
                        <div>
                            {{translate('Without configuring this section map functionality will not work properly. Thus the whole
                                 system will not work as it planned')}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert--primary d-flex" role="alert">
                    <div class="alert--icon">
                        <i class="tio-info"></i>
                    </div>
                    <div>
                        {{'sugerencia de API de mapa sugerencia de API de mapa 2'}}
                    </div>
                </div>
                <div class="py-1"></div>
                <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.third-party.config-update'):'javascript:'}}" method="post"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row gy-3">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="map_api_key" class="input-label">{{'clave de API de mapa'}} ({{'cliente'}})</label>
                                <input id="map_api_key" type="text" placeholder="{{'clave de API de mapa'}} ({{'cliente'}})" class="form-control" name="map_api_key"
                                    value="{{env('APP_MODE')!='demo'?$map_api_key??'':''}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="map_api_key_server" class="input-label">{{'clave de API de mapa'}} ({{'servidor'}})</label>
                                <input id="map_api_key_server" type="text" placeholder="{{'clave de API de mapa'}} ({{'servidor'}})" class="form-control" name="map_api_key_server"
                                    value="{{env('APP_MODE')!='demo'?$map_api_key_server??'':''}}" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="btn--container justify-content-end">
                                <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"  class="btn btn--primary call-demo">{{'ahorrar'}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

