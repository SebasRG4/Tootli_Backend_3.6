@extends('layouts.admin.app')

@section('title','configuración de la aplicación')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/setting.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'configuración de la aplicación'}}
                </span>
            </h1>
            {{-- <div class="text--primary-2 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                <strong class="mr-2">{{'¡Mira cómo funciona!'}}</strong>
                <div class="blinkings">
                    <i class="tio-info-outined"></i>
                </div>
            </div> --}}
        </div>
        <!-- End Page Header -->

        @php($app_minimum_version_android=\App\Models\BusinessSetting::where(['key'=>'app_minimum_version_android'])->first())
        @php($app_minimum_version_android=$app_minimum_version_android?$app_minimum_version_android->value:null)

        @php($app_url_android=\App\Models\BusinessSetting::where(['key'=>'app_url_android'])->first())
        @php($app_url_android=$app_url_android?$app_url_android->value:null)

        @php($app_minimum_version_ios=\App\Models\BusinessSetting::where(['key'=>'app_minimum_version_ios'])->first())
        @php($app_minimum_version_ios=$app_minimum_version_ios?$app_minimum_version_ios->value:null)

        @php($app_url_ios=\App\Models\BusinessSetting::where(['key'=>'app_url_ios'])->first())
        @php($app_url_ios=$app_url_ios?$app_url_ios->value:null)

        @php($app_minimum_version_android_store=\App\Models\BusinessSetting::where(['key'=>'app_minimum_version_android_store'])->first())
        @php($app_minimum_version_android_store=$app_minimum_version_android_store?$app_minimum_version_android_store->value:null)
        @php($app_url_android_store=\App\Models\BusinessSetting::where(['key'=>'app_url_android_store'])->first())
        @php($app_url_android_store=$app_url_android_store?$app_url_android_store->value:null)

        @php($app_minimum_version_ios_store=\App\Models\BusinessSetting::where(['key'=>'app_minimum_version_ios_store'])->first())
        @php($app_minimum_version_ios_store=$app_minimum_version_ios_store?$app_minimum_version_ios_store->value:null)
        @php($app_url_ios_store=\App\Models\BusinessSetting::where(['key'=>'app_url_ios_store'])->first())
        @php($app_url_ios_store=$app_url_ios_store?$app_url_ios_store->value:null)

        @php($app_minimum_version_android_deliveryman=\App\Models\BusinessSetting::where(['key'=>'app_minimum_version_android_deliveryman'])->first())
        @php($app_minimum_version_android_deliveryman=$app_minimum_version_android_deliveryman?$app_minimum_version_android_deliveryman->value:null)
        @php($app_url_android_deliveryman=\App\Models\BusinessSetting::where(['key'=>'app_url_android_deliveryman'])->first())
        @php($app_url_android_deliveryman=$app_url_android_deliveryman?$app_url_android_deliveryman->value:null)

        @php($app_minimum_version_ios_deliveryman=\App\Models\BusinessSetting::where(['key'=>'app_minimum_version_ios_deliveryman'])->first())
        @php($app_minimum_version_ios_deliveryman=$app_minimum_version_ios_deliveryman?$app_minimum_version_ios_deliveryman->value:null)
        @php($app_url_ios_deliveryman=\App\Models\BusinessSetting::where(['key'=>'app_url_ios_deliveryman'])->first())
        @php($app_url_ios_deliveryman=$app_url_ios_deliveryman?$app_url_ios_deliveryman->value:null)

        <form action="{{route('admin.business-settings.app-settings')}}" method="post"
        enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="type" value="user_app" >
            <h5 class="card-title mb-3 pt-3">
                <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{ 'Control de versiones de la aplicación de usuario' }}</span>
            </h5>
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{asset('assets/admin/img/andriod.png')}}" class="mr-2" alt="">
                                {{ 'para android' }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label  for="app_minimum_version_android" class="form-label">
                                        {{'Versión mínima de la aplicación de usuario'}} ({{'androide'}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'La versión mínima de la aplicación de usuario requerida para la funcionalidad de la aplicación.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <input id="app_minimum_version_android" type="number" placeholder="{{'versión mínima de la aplicación'}}" class="form-control" step="0.001" name="app_minimum_version_android"
                                        value="{{env('APP_MODE')!='demo'?$app_minimum_version_android??'':''}}">
                                </div>
                                <div class="form-group mb-md-0">
                                    <label for="app_url_android" class="form-label">
                                        {{'Descargar URL para la aplicación de usuario'}} ({{'androide'}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Los usuarios descargarán la última versión de la aplicación de usuario utilizando esta URL.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <input id="app_url_android" type="text" placeholder="{{'URL de la aplicación'}}" class="form-control" name="app_url_android"
                                        value="{{env('APP_MODE')!='demo'?$app_url_android??'':''}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{asset('assets/admin/img/ios.png')}}" class="mr-2" alt="">
                                {{ 'Para iOS' }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label  for="app_minimum_version_ios" class="form-label">{{'Versión mínima de la aplicación de usuario'}} ({{'ios'}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'La versión mínima de la aplicación de usuario requerida para la funcionalidad de la aplicación.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <input id="app_minimum_version_ios" type="number" placeholder="{{'versión mínima de la aplicación'}}" class="form-control" step="0.001" name="app_minimum_version_ios"
                                        value="{{env('APP_MODE')!='demo'?$app_minimum_version_ios??'':''}}">
                                </div>
                                <div class="form-group mb-md-0">
                                    <label for="app_url_ios" class="form-label">
                                        {{'Descargar URL para la aplicación de usuario'}} ({{'ios'}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Los usuarios descargarán la última versión de la aplicación de usuario utilizando esta URL.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <input id="app_url_ios" type="text" placeholder="{{'URL de la aplicación'}}" class="form-control" name="app_url_ios"
                                        value="{{env('APP_MODE')!='demo'?$app_url_ios??'':''}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"  class="btn btn--primary call-demo">{{'entregar'}}</button>
                    </div>
                </div>
            </div>
        </form>


        <form action="{{route('admin.business-settings.app-settings')}}" method="post"
        enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="type" value="store_app" >
            <h5 class="card-title mb-3 pt-4">
                <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{ 'Control de versiones de la aplicación de la tienda' }}</span>
            </h5>
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{asset('assets/admin/img/andriod.png')}}" class="mr-2" alt="">
                                {{ 'para android' }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label  for="app_minimum_version_android_store" class="form-label text-capitalize">{{'Versión mínima de la aplicación de la tienda para la tienda'}} ({{'androide'}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'La versión mínima de la aplicación de la tienda requerida para la funcionalidad de la aplicación.' }}">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                    </label>
                                    <input id="app_minimum_version_android_store" type="number" placeholder="{{'versión mínima de la aplicación'}}" class="form-control h--45px" name="app_minimum_version_android_store"
                                        step="0.001"   min="0" value="{{env('APP_MODE')!='demo'?$app_minimum_version_android_store??'':''}}">
                                </div>
                                <div class="form-group mb-md-0">
                                    <label for="app_url_android_store" class="form-label text-capitalize">
                                        {{'Descargar URL para la aplicación Store para la tienda'}} ({{'androide'}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Los usuarios descargarán la última aplicación de la tienda utilizando esta URL.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <input id="app_url_android_store" type="text" placeholder="{{'Descargar URL'}}" class="form-control h--45px" name="app_url_android_store"
                                        value="{{env('APP_MODE')!='demo'?$app_url_android_store??'':''}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{asset('assets/admin/img/ios.png')}}" class="mr-2" alt="">
                                {{ 'Para iOS' }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label for="app_minimum_version_ios_store" class="form-label text-capitalize">{{'Versión mínima de la aplicación de la tienda'}} ({{'ios'}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'La versión mínima de la aplicación de la tienda requerida para la funcionalidad de la aplicación.' }}">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                    </label>
                                    <input id="app_minimum_version_ios_store" type="number" placeholder="{{'versión mínima de la aplicación'}}" class="form-control h--45px" name="app_minimum_version_ios_store"
                                    step="0.001"  min="0" value="{{env('APP_MODE')!='demo'?$app_minimum_version_ios_store??'':''}}">
                                </div>
                                <div class="form-group mb-md-0">
                                    <label for="app_url_ios_store" class="form-label text-capitalize">
                                        {{'Descargar URL para la aplicación Store'}} ({{'ios'}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Los usuarios descargarán la última versión de la aplicación de la tienda utilizando esta URL.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <input id="app_url_ios_store" type="text" placeholder="{{'Descargar URL'}}" class="form-control h--45px" name="app_url_ios_store"
                                    value="{{env('APP_MODE')!='demo'?$app_url_ios_store??'':''}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"  class="btn btn--primary call-demo"  >{{'entregar'}}</button>
                    </div>
                </div>
            </div>
        </form>


        <form action="{{route('admin.business-settings.app-settings')}}" method="post"
        enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="type" value="deliveryman_app" >
            <h5 class="card-title mb-3 pt-4">
                <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{ 'Control de versiones de la aplicación Deliveryman' }}</span>
            </h5>
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{asset('assets/admin/img/andriod.png')}}" class="mr-2" alt="">
                                {{ 'para android' }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label for="app_minimum_version_android_deliveryman" class="form-label text-capitalize">{{'Versión mínima de la aplicación Deliveryman'}} ({{'androide'}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'La versión mínima de la aplicación de repartidor requerida para la funcionalidad de la aplicación.' }}">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                    </label>
                                    <input type="number" id="app_minimum_version_android_deliveryman" placeholder="{{'versión mínima de la aplicación'}}" class="form-control h--45px" name="app_minimum_version_android_deliveryman"
                                        step="0.001"   min="0" value="{{env('APP_MODE')!='demo'?$app_minimum_version_android_deliveryman??'':''}}">
                                </div>
                                <div class="form-group mb-md-0">
                                    <label for="app_url_android_deliveryman"  class="form-label text-capitalize">
                                        {{'Descargar URL para la aplicación Deliveryman'}} ({{'androide'}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Los usuarios descargarán la última versión de la aplicación de repartidor utilizando esta URL.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <input type="text" id="app_url_android_deliveryman" placeholder="{{'Descargar URL'}}" class="form-control h--45px" name="app_url_android_deliveryman"
                                    value="{{env('APP_MODE')!='demo'?$app_url_android_deliveryman??'':''}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">
                                <img src="{{asset('assets/admin/img/ios.png')}}" class="mr-2" alt="">
                                {{ 'Para iOS' }}
                            </h5>
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group">
                                    <label  for="app_minimum_version_ios_deliveryman" class="form-label text-capitalize">{{'Versión mínima de la aplicación Deliveryman'}} ({{'ios'}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'La versión mínima de la aplicación de repartidor requerida para la funcionalidad de la aplicación.' }}">
                                        <i class="tio-info-outined"></i>
                                    </span>
                                    </label>
                                    <input id="app_minimum_version_ios_deliveryman" type="number" placeholder="{{'versión mínima de la aplicación'}}" class="form-control h--45px" name="app_minimum_version_ios_deliveryman"
                                    step="0.001"  min="0" value="{{env('APP_MODE')!='demo'?$app_minimum_version_ios_deliveryman??'':''}}">
                                </div>
                                <div class="form-group mb-md-0">
                                    <label for="app_url_ios_deliveryman" class="form-label text-capitalize">
                                        {{'Descargar URL para la aplicación Deliveryman'}} ({{'ios'}})
                                        <span class="input-label-secondary text--title" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Los usuarios descargarán la última versión de la aplicación de repartidor utilizando esta URL.' }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </label>
                                    <input id="app_url_ios_deliveryman" type="text" placeholder="{{'Descargar URL'}}" class="form-control h--45px" name="app_url_ios_deliveryman"
                                    value="{{env('APP_MODE')!='demo'?$app_url_ios_deliveryman??'':''}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"  class="btn btn--primary call-demo">{{'entregar'}}</button>
                    </div>
                </div>
            </div>
        </form>

        @php($donation_button_status=\App\Models\BusinessSetting::where(['key'=>'donation_button_status'])->first())
        @php($donation_button_status=$donation_button_status?$donation_button_status->value:0)

        @php($donation_button_image=\App\Models\BusinessSetting::where(['key'=>'donation_button_image'])->first())
        @php($donation_button_image=$donation_button_image?$donation_button_image->value:'')

        <form action="{{route('admin.business-settings.app-settings')}}" method="post"
        enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="type" value="donation_settings" >
            <h5 class="card-title mb-3 pt-4">
                <span class="card-header-icon mr-2"><i class="tio-gift"></i></span> <span>{{ 'Configuración de donación' }}</span>
            </h5>
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="donation_button_status">{{'Estado del botón de donación'}}</label>
                                <select name="donation_button_status" id="donation_button_status" class="form-control">
                                    <option value="1" {{$donation_button_status == 1 ? 'selected' : ''}}>{{'Activo'}}</option>
                                    <option value="0" {{$donation_button_status == 0 ? 'selected' : ''}}>{{'Inactivo'}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{'Imagen del botón de donación'}}</label>
                                <div class="custom-file">
                                    <input type="file" name="donation_button_image" id="donationFile" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label" for="donationFile">{{'Elija archivo'}}</label>
                                </div>
                                <center class="mt-3">
                                    <img class="upload-img-view" id="donationViewer"
                                        src="{{\App\CentralLogics\Helpers::get_full_url('business', $donation_button_image, 'public', 'aspect_1')}}"
                                        alt="donation image" style="max-height: 120px; object-fit: contain;"/>
                                </center>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"  class="btn btn--primary call-demo">{{'entregar'}}</button>
                    </div>
                </div>
            </div>
        </form>

    </div>

@endsection

@push('script_2')
    <script>
        function readDonationURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#donationViewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#donationFile").change(function () {
            readDonationURL(this);
        });
    </script>
@endpush
