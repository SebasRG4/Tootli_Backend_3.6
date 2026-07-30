@extends('layouts.admin.app')

@section('title','página de inicio del administrador')

@section('content')
<div class="content container-fluid">
    <div class="page-header pb-0">
        <div class="d-flex flex-wrap justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/landing.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{ 'páginas de inicio de administración' }}
                </span>
            </h1>
            <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                <strong class="mr-2">{{'¡Mira cómo funciona!'}}</strong>
                <div>
                    <i class="tio-info-outined"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-20 mt-2">
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            @include('admin-views.business-settings.landing-page-settings.top-menu-links.admin-landing-page-links')
        </div>
    </div>
    @php($earning_title=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','admin_landing_page')->where('key','earning_title')->first())
    @php($earning_sub_title=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','admin_landing_page')->where('key','earning_sub_title')->first())
    @php($earning_seller_image=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','admin_landing_page')->where('key','earning_seller_image')->first())
    @php($earning_delivery_image=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','admin_landing_page')->where('key','earning_delivery_image')->first())
    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
    @php($language = $language->value ?? null)
    @php($defaultLang = str_replace('_', '-', app()->getLocale()))
    @if($language)
        <ul class="nav nav-tabs mb-4 border-0">
            <li class="nav-item">
                <a class="nav-link lang_link active"
                href="#"
                id="default-link">{{'por defecto'}}</a>
            </li>
            @foreach (json_decode($language) as $lang)
                <li class="nav-item">
                    <a class="nav-link lang_link"
                        href="#"
                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                </li>
            @endforeach
        </ul>
    @endif
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <form action="{{ route('admin.business-settings.admin-landing-page-settings', 'earning-title') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <h5 class="card-title mb-3">
                    <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{'Descargar el contenido de la sección de la aplicación del usuario'}}</span>
                </h5>
                <div class="card mb-3">
                    <div class="card-body">
                        @if ($language)
                            <div class="row g-3 lang_form" id="default-form">
                                <div class="col-sm-6">
                                    <label for="earning_title" class="form-label">{{'Título'}} ({{ 'por defecto' }})<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 40 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span>
                                        <span class="form-label-secondary text-danger"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Requerido.'}}"> *
                                                </span></label>
                                                <input required id="earning_title" type="text" maxlength="40" name="earning_title[]" class="form-control" value="{{$earning_title?->getRawOriginal('value')}}" placeholder="{{'título aquí...'}}">
                                </div>
                                <div class="col-sm-6">
                                    <label for="sub-text" class="form-label">{{'Subtítulo'}} ({{ 'por defecto' }})<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span>
                                        <span class="form-label-secondary text-danger"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Requerido.'}}"> *
                                                </span></label>
                                                <input required id="sub-text" type="text" maxlength="80" name="earning_sub_title[]" class="form-control" value="{{$earning_sub_title?->getRawOriginal('value')}}" placeholder="{{'subtítulo aquí...'}}">
                                </div>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                                @foreach(json_decode($language) as $lang)
                                <?php
                                if(isset($earning_title->translations)&&count($earning_title->translations)){
                                        $earning_title_translate = [];
                                        foreach($earning_title->translations as $t)
                                        {
                                            if($t->locale == $lang && $t->key=='earning_title'){
                                                $earning_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }

                                    }
                                if(isset($earning_sub_title->translations)&&count($earning_sub_title->translations)){
                                        $earning_sub_title_translate = [];
                                        foreach($earning_sub_title->translations as $t)
                                        {
                                            if($t->locale == $lang && $t->key=='earning_sub_title'){
                                                $earning_sub_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }

                                    }
                                    ?>
                                    <div class="row g-3 d-none lang_form" id="{{$lang}}-form">
                                        <div class="col-sm-6">
                                            <label for="earning_title" class="form-label">{{'Título'}} ({{strtoupper($lang)}})<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 40 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input id="earning_title" type="text" maxlength="40" name="earning_title[]" class="form-control" value="{{ $earning_title_translate[$lang]['value']?? '' }}" placeholder="{{'título aquí...'}}">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="sub-title" class="form-label">{{'Subtítulo'}} ({{strtoupper($lang)}})<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input id="sub-title" type="text" maxlength="80" name="earning_sub_title[]" class="form-control" value="{{ $earning_sub_title_translate[$lang]['value']?? '' }}" placeholder="{{'subtítulo aquí...'}}">
                                        </div>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                            @else
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label for="earning-title" class="form-label">{{'Título'}}<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 40 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input  id="earning-title" type="text" maxlength="40" name="earning_title[]" class="form-control" placeholder="{{'título aquí...'}}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="earning-sub-title" class="form-label">{{'Subtítulo'}}<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input id="earning-sub-title" type="text" maxlength="80" name="earning_sub_title[]" class="form-control" placeholder="{{'subtítulo aquí...'}}">
                                    </div>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif
                        <div class="btn--container justify-content-end mt-20">
                            <button type="reset" class="btn btn--reset mb-2">{{'Reiniciar'}}</button>
                            <button type="submit"   class="btn btn--primary mb-2">{{'Ahorrar'}}</button>
                        </div>
                    </div>
                </div>
            </form>
            <form action="{{ route('admin.business-settings.admin-landing-page-settings', 'earning-seller-link') }}" method="POST" enctype="multipart/form-data">
                @php($seller_app_links = \App\Models\DataSetting::where(['key'=>'seller_app_earning_links','type'=>'admin_landing_page'])->first())
                @php($seller_app_links = isset($seller_app_links->value)?json_decode($seller_app_links->value, true):null)

                @csrf
                <h5 class="card-title mb-3">
                    <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{'Descargar la sección de aplicaciones de la tienda'}}</span>
                </h5>
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label d-block mb-3">
                                    {{'Bandera'}}  <span class="text--primary">{{'(tamaño: 3:1)'}}</span>
                                    <span class="form-label-secondary text-danger"
                                          data-toggle="tooltip" data-placement="right"
                                          data-original-title="{{ 'Requerido.'}}"> *
                                                </span>
                                    <div class="fs-12 opacity-70">
                                        {{ translate(IMAGE_FORMAT.' ' . 'Less Than 2MB') }}
                                    </div>
                                </label>
                                <label class="upload-img-3 m-0 d-block">
                                    <div class="position-relative">
                                    <div class="img">
                                        <img  src="{{\App\CentralLogics\Helpers::get_full_url('earning', $earning_seller_image?->value?? '', $earning_seller_image?->storage[0]?->value ?? 'public','upload_image_4')}}"


                                        data-onerror-image="{{asset('assets/admin/img/upload-4.png')}}"
                                        class="vertical-img mw-100 vertical onerror-image" alt="">

                                    </div>
                                    <input class="upload-file__input single_file_input" accept="{{IMAGE_EXTENSION}}" type="file" name="earning_seller_image"  hidden>
                                    @if (isset($earning_seller_image['value']))
                                            <span id="earning_seller_img" class="remove_image_button remove-image dynamic-checkbox"
                                            data-id="earning_seller_img" data-image-off="{{ asset('assets/admin/img/delete-confirmation.png') }}"
                                            data-title="{{'¡Advertencia!'}}"
                                            data-text="<p>{{'¿Estás seguro de que deseas eliminar esta imagen?'}}</p>"
                                                > <i class="tio-clear"></i></span>
                                            @endif
                                        </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <h5 class="card-title mb-2">
                                    <img src="{{asset('assets/admin/img/playstore.png')}}" class="mr-2" alt="">
                                    {{'Botón de tienda de juegos'}}
                                </h5>
                                <div class="__bg-F8F9FC-card">
                                    <div class="form-group mb-md-0">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label  for="playstore_url" class="form-label text-capitalize m-0">
                                                {{'Enlace de descarga'}}
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Cuando esté deshabilitado, el botón de descarga de Play Store estará oculto en la página de inicio.' }}">
                                                    <i class="tio-info-outined"></i>
                                                </span>
                                            </label>
                                            <label  class="toggle-switch toggle-switch-sm m-0">
                                                <input type="checkbox" name="playstore_url_status"
                                                       data-id="play-store-seller-status"
                                                       data-type="toggle"
                                                       data-image-on='{{asset('assets/admin/img/modal')}}/play-store-on.png'
                                                       data-image-off="{{asset('assets/admin/img/modal')}}/play-store-off.png"
                                                       data-title-on="{{'¿Quieres habilitar el botón Play Store para la aplicación Store?'}}"
                                                       data-title-off="{{'¿Quieres desactivar el botón Play Store para la aplicación Store?'}}"
                                                       data-text-on="<p>{{'Si está habilitado, el botón de descarga de la aplicación Store será visible en la página de inicio.'}}</p>"
                                                       data-text-off="<p>{{'Si está deshabilitado, este botón estará oculto en la página de destino.'}}</p>"
                                                       id="play-store-seller-status" class="status toggle-switch-input dynamic-checkbox-toggle" value="1" {{(isset($seller_app_links) && $seller_app_links['playstore_url_status'])?'checked':''}}>
                                                <span class="toggle-switch-label text mb-0">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </div>
                                        <input id="playstore_url" type="url" placeholder="{{'Ej: https://play.google.com/store/apps'}}" class="form-control h--45px" name="playstore_url" value="{{ $seller_app_links['playstore_url'] ?? ''}}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="card-title mb-2">
                                    <img src="{{asset('assets/admin/img/ios.png')}}" class="mr-2" alt="">
                                    {{'Botón de la tienda de aplicaciones'}}
                                </h5>
                                <div class="__bg-F8F9FC-card">
                                    <div class="form-group mb-md-0">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="apple_store_url" class="form-label text-capitalize m-0">
                                                {{'Enlace de descarga'}}
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Cuando esté deshabilitado, el botón de descarga de la App Store estará oculto en la página de inicio.' }}">
                                                    <i class="tio-info-outined"></i>
                                                </span>
                                            </label>
                                            <label class="toggle-switch toggle-switch-sm m-0">
                                                <input type="checkbox" name="apple_store_url_status"

                                                       data-id="apple-seller-status"
                                                       data-type="toggle"
                                                       data-image-on='{{asset('assets/admin/img/modal')}}/apple-on.png'
                                                       data-image-off="{{asset('assets/admin/img/modal')}}/apple-off.png"
                                                       data-title-on="{{'¿Quiere habilitar el botón App Store para la aplicación Store?'}}"
                                                       data-title-off="{{'Quiere desactivar el botón App Store para la aplicación Store'}}"
                                                       data-text-on="<p>{{'Si está habilitado, el botón de descarga de la aplicación Store será visible en la página de inicio.'}}</p>"
                                                       data-text-off="<p>{{'Si está deshabilitado, este botón estará oculto en la página de destino.'}}</p>"
                                                       id="apple-seller-status" class="status toggle-switch-input dynamic-checkbox-toggle"

                                                       value="1" {{(isset($seller_app_links) && $seller_app_links['apple_store_url_status'])?'checked':''}}>
                                                <span class="toggle-switch-label text mb-0">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </div>
                                        <input type="url" id="apple_store_url" placeholder="{{'Ejemplo: https://www.apple.com/app-store/'}}" class="form-control h--45px" name="apple_store_url" value="{{ $seller_app_links['apple_store_url'] ?? ''}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn--container justify-content-end mt-20">
                            <button type="reset" class="btn btn--reset mb-2">{{'Reiniciar'}}</button>
                            <button type="submit"   class="btn btn--primary mb-2">{{'Ahorrar'}}</button>
                        </div>
                    </div>
                </div>
            </form>
            <form  id="earning_seller_img_form" action="{{ route('admin.remove_image') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{  $earning_seller_image?->id}}" >
                {{-- <input type="hidden" name="json" value="1" > --}}
                <input type="hidden" name="model_name" value="DataSetting" >
                <input type="hidden" name="image_path" value="earning" >
                <input type="hidden" name="field_name" value="value" >
            </form>
            <form action="{{ route('admin.business-settings.admin-landing-page-settings', 'earning-dm-link') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @php($dm_app_links = \App\Models\DataSetting::where(['key'=>'dm_app_earning_links','type'=>'admin_landing_page'])->first())
                @php($dm_app_links = isset($dm_app_links->value)?json_decode($dm_app_links->value, true):null)

                <h5 class="card-title mt-3 mb-3">
                    <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span> <span>{{'Descargar la sección de la aplicación Deliveryman'}}</span>
                </h5>
                <div class="card">
                    <div class="card-body">

                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label d-block mb-3">
                                    {{'Bandera'}}  <span class="text--primary">{{'(tamaño: 3:1)'}}</span>
                                    <span class="form-label-secondary text-danger"
                                          data-toggle="tooltip" data-placement="right"
                                          data-original-title="{{ 'Requerido.'}}"> *
                                                </span>
                                    <div class="fs-12 opacity-70">
                                        {{ translate(IMAGE_FORMAT.' ' . 'Less Than 2MB') }}
                                    </div>
                                </label>
                                <label class="upload-img-3 m-0 d-block">
                                    <div class="position-relative">
                                    <div class="img">

                                        <img src="{{\App\CentralLogics\Helpers::get_full_url('earning', $earning_delivery_image?->value?? '', $earning_delivery_image?->storage[0]?->value ?? 'public','upload_image_4')}}"

                                        data-onerror-image="{{asset('assets/admin/img/upload-4.png')}}" class="vertical-img mw-100 vertical onerror-image" alt="">
                                    </div>
                                        <input class="upload-file__input single_file_input" accept="{{IMAGE_EXTENSION}}" type="file" name="earning_delivery_image"  hidden>
                                            @if (isset($earning_delivery_image['value']))
                                            <span id="earning_delivery_img" class="remove_image_button  remove-image dynamic-checkbox"
                                                  data-id="earning_delivery_img"
                                                  data-image-off="{{ asset('assets/admin/img/delete-confirmation.png') }}"
                                                  data-title="{{'¡Advertencia!'}}"
                                                  data-text="<p>{{'¿Estás seguro de que deseas eliminar esta imagen?'}}</p>"
                                                > <i class="tio-clear"></i></span>
                                            @endif
                                        </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <h5 class="card-title mb-2">
                                    <img src="{{asset('assets/admin/img/playstore.png')}}" class="mr-2" alt="">
                                    {{'Botón de tienda de juegos'}}
                                </h5>
                                <div class="__bg-F8F9FC-card">
                                    <div class="form-group mb-md-0">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label  for="playstore_url_dm" class="form-label text-capitalize m-0">
                                                {{'Enlace de descarga'}}
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Cuando esté deshabilitado, el botón de descarga de Play Store estará oculto en la página de inicio.' }}">
                                                    <i class="tio-info-outined"></i>
                                                </span>
                                            </label>
                                            <label class="toggle-switch toggle-switch-sm m-0">
                                                <input type="checkbox" name="playstore_url_status"
                                                       data-id="play-store-dm-status"
                                                       data-type="toggle"
                                                       data-image-on="{{ asset('assets/admin/img/modal/play-store-on.png') }}"
                                                       data-image-off="{{ asset('assets/admin/img/modal/play-store-off.png') }}"
                                                       data-title-on="{{ '¿Quieres habilitar el botón Play Store para la aplicación Deliveryman?' }}"
                                                       data-title-off="{{ '¿Quieres desactivar el botón Play Store para la aplicación Deliveryman?' }}"
                                                       data-text-on="<p>{{ 'Si está habilitado, el botón de descarga de la aplicación Deliveryman será visible en la página de destino.' }}</p>"
                                                       data-text-off="<p>{{ 'Si está deshabilitado, este botón estará oculto en la página de destino.' }}</p>"
                                                       id="play-store-dm-status"
                                                       class="status toggle-switch-input dynamic-checkbox-toggle"


                                                       value="1" {{(isset($dm_app_links) && $dm_app_links['playstore_url_status'])?'checked':''}}>
                                                <span class="toggle-switch-label text mb-0">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </div>
                                        <input id="playstore_url_dm" type="url" placeholder="{{'Ej: https://play.google.com/store/apps'}}" class="form-control h--45px" name="playstore_url" value="{{ $dm_app_links['playstore_url'] ?? ''}}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="card-title mb-2">
                                    <img src="{{asset('assets/admin/img/ios.png')}}" class="mr-2" alt="">
                                    {{'Botón de la tienda de aplicaciones'}}
                                </h5>
                                <div class="__bg-F8F9FC-card">
                                    <div class="form-group mb-md-0">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="apple_store_url_dm" class="form-label text-capitalize m-0">
                                                {{'Enlace de descarga'}}
                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Cuando esté deshabilitado, el botón de descarga de la App Store estará oculto en la página de inicio.' }}">
                                                    <i class="tio-info-outined"></i>
                                                </span>
                                            </label>
                                            <label class="toggle-switch toggle-switch-sm m-0">
                                                <input type="checkbox" name="apple_store_url_status"
                                                       data-id="apple-dm-status"
                                                       data-type="toggle"
                                                       data-image-on="{{ asset('assets/admin/img/modal/apple-on.png') }}"
                                                       data-image-off="{{ asset('assets/admin/img/modal/apple-off.png') }}"
                                                       data-title-on="{{ '¿Quiere habilitar el botón App Store para la aplicación Deliveryman?' }}"
                                                       data-title-off="{{ '¿Quiere desactivar el botón App Store para la aplicación Deliveryman?' }}"
                                                       data-text-on="<p>{{ 'Si está habilitado, el botón de descarga de la aplicación Deliveryman será visible en la página de destino.' }}</p>"
                                                       data-text-off="<p>{{ 'Si está deshabilitado, este botón estará oculto en la página de destino.' }}</p>"
                                                       id="apple-dm-status"
                                                       class="status toggle-switch-input dynamic-checkbox-toggle"


                                                       value="1" {{(isset($dm_app_links) && $dm_app_links['apple_store_url_status'])?'checked':''}}>
                                                <span class="toggle-switch-label text mb-0">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </div>
                                        <input id="apple_store_url_dm" type="url" placeholder="{{'Ejemplo: https://www.apple.com/app-store/'}}" class="form-control h--45px" name="apple_store_url" value="{{ $dm_app_links['apple_store_url']?? ''}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn--container justify-content-end mt-20">
                            <button type="reset" class="btn btn--reset mb-2">{{'Reiniciar'}}</button>
                            <button type="submit"   class="btn btn--primary mb-2">{{'Ahorrar'}}</button>
                        </div>
                    </div>
                </div>
            </form>
             <form  id="earning_delivery_img_form" action="{{ route('admin.remove_image') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{  $earning_delivery_image?->id}}" >
                <input type="hidden" name="model_name" value="DataSetting" >
                <input type="hidden" name="image_path" value="earning" >
                <input type="hidden" name="field_name" value="value" >
            </form>

        </div>
    </div>
</div>
    <!-- How it Works -->
    @include('admin-views.business-settings.landing-page-settings.partial.how-it-work')
@endsection

