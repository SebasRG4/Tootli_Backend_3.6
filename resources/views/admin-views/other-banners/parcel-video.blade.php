@extends('layouts.admin.app')

@section('title', 'bandera')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/3rd-party.png')}}" class="w--26" alt="">
            </span>
            <span>
                {{'Configuración de otro contenido promocional'}}
            </span>
        </h1>
    </div>
    <div class="mb-20 mt-2">
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            @include('admin-views.other-banners.partial.parcel-links')
        </div>
    </div>
    @php($content1_title = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id', Config::get('module.current_module_id'))->where('type', 'video_banner_content')->where('key', 'content1_title')->first())
    @php($content1_subtitle = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id', Config::get('module.current_module_id'))->where('type', 'video_banner_content')->where('key', 'content1_subtitle')->first())
    @php($content2_title = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id', Config::get('module.current_module_id'))->where('type', 'video_banner_content')->where('key', 'content2_title')->first())
    @php($content2_subtitle = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id', Config::get('module.current_module_id'))->where('type', 'video_banner_content')->where('key', 'content2_subtitle')->first())
    @php($content3_title = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id', Config::get('module.current_module_id'))->where('type', 'video_banner_content')->where('key', 'content3_title')->first())
    @php($content3_subtitle = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id', Config::get('module.current_module_id'))->where('type', 'video_banner_content')->where('key', 'content3_subtitle')->first())
    @php($section_title = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id', Config::get('module.current_module_id'))->where('type', 'video_banner_content')->where('key', 'section_title')->first())
    @php($banner_type = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id', Config::get('module.current_module_id'))->where('type', 'video_banner_content')->where('key', 'banner_type')->first())
    @php($banner_video = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id', Config::get('module.current_module_id'))->where('type', 'video_banner_content')->where('key', 'banner_video')->first())
    @php($banner_video_content = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id', Config::get('module.current_module_id'))->where('type', 'video_banner_content')->where('key', 'banner_video_content')->first())
    @php($banner_image = \App\Models\ModuleWiseBanner::withoutGlobalScope('translate')->where('module_id', Config::get('module.current_module_id'))->where('type', 'video_banner_content')->where('key', 'banner_image')->first())
    @php($awsUrl = config('filesystems.disks.s3.url'))
    @php($awsBucket = config('filesystems.disks.s3.bucket'))
    @php($awsBaseURL = rtrim($awsUrl, '/') . '/' . ltrim($awsBucket . '/'))
    @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
    @php($language = $language->value ?? null)
    @if ($language)
        <ul class="nav nav-tabs mb-4 border-0">
            <li class="nav-item">
                <a class="nav-link lang_link active" href="#" id="default-link">{{ 'por defecto' }}</a>
            </li>
            @foreach (json_decode($language) as $lang)
                <li class="nav-item">
                    <a class="nav-link lang_link" href="#"
                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                </li>
            @endforeach
        </ul>
    @endif
    <div class="tab-content">
        <div class="tab-pane fade show active">
            <form action="{{ route('admin.promotional-banner.video-image-store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="card mb-3">
                    <h5 class="card-title p-3">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span>
                        <span>{{ 'Vídeo/Imagen' }}</span>
                    </h5>
                    <div class="card-body">
                        <div class="row g-4">
                            @if ($language)
                                <div class="col-md-6 lang_form default-form">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="section_title" class="form-label">{{ 'Título de la sección' }}
                                                ({{ 'por defecto' }})<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <input type="text" id="section_title" maxlength="20" name="section_title[]"
                                                value="{{ $section_title?->getRawOriginal('value') }}" class="form-control"
                                                placeholder="{{ 'Ej:Ingrese el título de la sección' }}">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                @foreach (json_decode($language) as $lang)
                                                        <?php
                                    if (isset($section_title->translations) && count($section_title->translations)) {
                                        $section_title_translate = [];
                                        foreach ($section_title->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'section_title') {
                                                $section_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                                                ?>
                                                        <div class="col-md-6 d-none lang_form" id="{{ $lang }}-form1">
                                                            <div class="row g-3">
                                                                <div class="col-12">
                                                                    <label for="section_title{{$lang}}"
                                                                        class="form-label">{{ 'Título de la sección' }}
                                                                        ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                                            data-toggle="tooltip" data-placement="right"
                                                                            data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                                                            <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                                alt="">
                                                                        </span></label>
                                                                    <input type="text" id="section_title{{$lang}}" maxlength="20"
                                                                        name="section_title[]"
                                                                        value="{{ $section_title_translate[$lang]['value'] ?? '' }}"
                                                                        class="form-control"
                                                                        placeholder="{{ 'Ej:Ingrese el título de la sección' }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                @endforeach
                            @endif
                            <div class="col-sm-12 col-lg-6">
                                <div class="form-group mb-0">
                                    <label class="input-label text-capitalize d-flex alig-items-center"><span
                                            class="line--limit-1">{{ 'Subir contenido' }}
                                        </span>
                                    </label>
                                    <div class="resturant-type-group border">
                                        <label class="form-check form--check mr-2 mr-md-4">
                                            <input class="form-check-input" type="radio" value="video"
                                                name="banner_type" {{ $banner_type ? ($banner_type->value == 'video' ? 'checked' : '') : '' }}>
                                            <span class="form-check-label">
                                                {{'URL del vídeo de YouTube'}} <span class="input-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{'Vaya a YouTube, haga clic en la opción compartir y luego aparecerá una ventana emergente para compartir. Seleccione insertar y obtener un video para insertar y luego copie el código generado para el enlace insertado.'}}"><img
                                                        src="{{asset('assets/admin/img/info-circle.svg')}}"
                                                        alt="public/img"></span>
                                            </span>
                                        </label>
                                        <label class="form-check form--check mr-2 mr-md-4">
                                            <input class="form-check-input" type="radio" value="video_content"
                                                name="banner_type" {{ $banner_type ? ($banner_type->value == 'video_content' ? 'checked' : '') : '' }}>
                                            <span class="form-check-label">
                                                {{'video'}}
                                            </span>
                                        </label>
                                        <label class="form-check form--check mr-2 mr-md-4">
                                            <input class="form-check-input" type="radio" value="image"
                                                name="banner_type" {{ $banner_type ? ($banner_type->value == 'image' ? 'checked' : '') : '' }}>
                                            <span class="form-check-label">
                                                {{'imagen'}}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 {{ $banner_type ? ($banner_type->value == 'image' ? '' : 'd-none') : '' }}"
                                id="image">
                                <label class="__upload-img aspect-615-350 d-block position-relative">
                                    <div class="img">
                                        <img class="onerror-image"
                                            src="{{\App\CentralLogics\Helpers::get_full_url('promotional_banner', $banner_image?->value ?? '', $banner_image?->storage[0]?->value ?? 'public', 'upload_placeholder')}}"
                                            data-onerror-image="{{ asset('assets/admin/img/upload-placeholder.png') }}"
                                            alt="">
                                    </div>

                                    <div class="">
                                        <input type="file" name="banner_image" hidden>
                                    </div>
                                    @if (isset($banner_image?->value))
                                        <span id="banner_image" class="remove_image_button dynamic-checkbox"
                                            data-id="banner_image" data-type="status"
                                            data-image-on="{{asset('assets/admin/img/modal')}}/mail-success.png"
                                            data-image-off="{{asset('assets/admin/img/modal')}}/mail-warning.png"
                                            data-title-on="{{'¡Importante!'}}"
                                            data-title-off="{{'¡Advertencia!'}}"
                                            data-text-on="<p>{{'¿Estás seguro de que quieres eliminar esta imagen?'}}</p>"
                                            data-text-off="<p>{{'¿Está seguro de que desea eliminar esta imagen?'}}</p>">
                                            <i class="tio-clear"></i></span>
                                    @endif
                                </label>
                                <div class="text-center mt-5">
                                    <h3 class="form-label d-block mt-2">
                                        {{'Tamaño de imagen mínimo 615 x 350 px'}}
                                    </h3>
                                    <p>{{'formato de imagen: jpg, png, jpeg | tamaño máximo: 2 MB'}}</p>

                                </div>
                            </div>

                            <div class="col-12 {{ $banner_type ? ($banner_type->value == 'video_content' ? '' : 'd-none') : '' }}"
                                id="video_content">

                                <div class="row">
                                    <div class="col-6">
                                        <h4 class="mb-3 text-capitalize d-flex align-items-center">
                                            {{'subir vídeo'}}</h4>
                                        <div class="uploadDnD">
                                            <div class="form-group inputDnD">
                                                <input type="file" name="banner_video_content"
                                                    class="form-control-file text--primary font-weight-bold read-url"
                                                    id="inputFile" accept=".mp4 ,.webm"
                                                    data-title="{{ 'Explorar archivo"' }}">
                                            </div>
                                        </div>

                                        <div class="mt-5 card px-3 py-2 d--none" id="progress-bar">
                                            <div class="d-flex flex-wrap align-items-center gap-3">
                                                <div class="">
                                                    <img width="24" src="{{asset('assets/admin/img/zip.png')}}"
                                                        alt="">
                                                </div>
                                                <div class="flex-grow-1 text-start">
                                                    <div
                                                        class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                        <span id="name_of_file" class="text-truncate fz-12"></span>
                                                        <span class="text-muted fz-12" id="progress-label">0%</span>
                                                    </div>
                                                    <progress id="uploadProgress" class="w-100" value="0"
                                                        max="100"></progress>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center mt-5">
                                            <h3 class="form-label d-block mt-2">
                                                {{'Tamaño del vídeo máx. 5 MB'}}
                                            </h3>
                                            <p>{{'Formato de vídeo: MP4'}}</p>

                                        </div>

                                    </div>
                                    {{-- {{dd($banner_video_content)}}--}}
                                    @if ($banner_video_content?->value)

                                    <div class="col-6">
                                        <h4 class="mb-3  ml-4 text-capitalize d-flex align-items-center">
                                            {{'Video'}}</h4>
                                        @php($extention = explode('.', $banner_video_content?->value))
                                        <video width="320" height="140" id="video-preview" controls>
                                            <source
                                                src="{{(count($banner_video_content?->storage) > 0 && $banner_video_content?->storage[0]?->value == 's3') ? $awsBaseURL . 'promotional_banner/video/' . $banner_video_content?->value : asset('storage/app/public/promotional_banner/video') . '/' . $banner_video_content?->value}}"
                                                type="video/{{ data_get($extention, 1, 'mp4') }}">
                                        </video>
                                    </div>
                                    @endif

                                </div>

                            </div>
                            <div class="col-12 {{ $banner_type ? ($banner_type->value == 'video' ? '' : 'd-none') : 'd-none' }}"
                                id="video">
                                <label for="banner_video"
                                    class="form-label">{{ 'URL del vídeo de YouTube' }}</label>
                                <input type="url" id="banner_video" name="banner_video"
                                    value="{{ $banner_video?->value }}" class="form-control"
                                    placeholder="{{ 'Ingrese la URL del vídeo de YouTube' }}">
                            </div>
                        </div>
                        <div class="btn--container justify-content-end mt-20">
                            <button type="reset" class="btn btn--reset">{{ 'Reiniciar' }}</button>
                            <button type="submit" class="btn btn--primary call-demo">{{ 'Ahorrar' }}</button>
                        </div>
                    </div>
                </div>
            </form>
            <form action="{{ route('admin.promotional-banner.video-content-store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <h5 class="card-title mb-3">
                    <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span>
                    <span>{{ 'Contenido de vídeo/imagen' }}</span>
                </h5>
                <div class="card mb-3">
                    <div class="card-body">
                        @if ($language)
                            <div class="lang_form default-form">
                                <div class="form-group">
                                    <label class="form-label">{{ 'contenido-1' }}</label>
                                    <div class="row g-3 __bg-F8F9FC-card">
                                        <div class="col-sm-6">
                                            <label for="content1_title" class="form-label">{{ 'Título' }}
                                                ({{ 'por defecto' }})<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <input type="text" id="content1_title" maxlength="80" name="content1_title[]"
                                                value="{{ $content1_title?->getRawOriginal('value') }}" class="form-control"
                                                placeholder="{{ 'Ej: Introduzca el título' }}">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="content1_subtitle"
                                                class="form-label">{{ 'Subtítulo' }}
                                                ({{ 'por defecto' }})<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 240 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <input type="text" id="content1_subtitle" maxlength="240"
                                                name="content1_subtitle[]"
                                                value="{{ $content1_subtitle?->getRawOriginal('value') }}"
                                                class="form-control" placeholder="{{ 'Ej: introduzca el subtítulo' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ 'contenido-2' }}</label>
                                    <div class="row g-3 __bg-F8F9FC-card">
                                        <div class="col-sm-6">
                                            <label for="content2_title" class="form-label">{{ 'Título' }}
                                                ({{ 'por defecto' }})<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <input type="text" id="content2_title" maxlength="80" name="content2_title[]"
                                                value="{{ $content2_title?->getRawOriginal('value') }}" class="form-control"
                                                placeholder="{{ 'Ej: Introduzca el título' }}">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="content2_subtitle"
                                                class="form-label">{{ 'Subtítulo' }}
                                                ({{ 'por defecto' }})<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 240 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <input id="content2_subtitle" type="text" maxlength="240"
                                                name="content2_subtitle[]"
                                                value="{{ $content2_subtitle?->getRawOriginal('value') }}"
                                                class="form-control" placeholder="{{ 'Ej: introduzca el subtítulo' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ 'contenido-3' }}</label>
                                    <div class="row g-3 __bg-F8F9FC-card">
                                        <div class="col-sm-6">
                                            <label for="content3_title" class="form-label">{{ 'Título' }}
                                                ({{ 'por defecto' }})<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <input id="content3_title" type="text" maxlength="80" name="content3_title[]"
                                                value="{{ $content3_title?->getRawOriginal('value') }}" class="form-control"
                                                placeholder="{{ 'Ej: Introduzca el título' }}">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="content3_subtitle"
                                                class="form-label">{{ 'Subtítulo' }}
                                                ({{ 'por defecto' }})<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 240 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <input type="text" id="content3_subtitle" maxlength="240"
                                                name="content3_subtitle[]"
                                                value="{{ $content3_subtitle?->getRawOriginal('value') }}"
                                                class="form-control" placeholder="{{ 'Ej: introduzca el subtítulo' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                            @foreach (json_decode($language) as $lang)
                                                <?php
                                if (isset($content1_title->translations) && count($content1_title->translations)) {
                                    $content1_title_translate = [];
                                    foreach ($content1_title->translations as $t) {
                                        if ($t->locale == $lang && $t->key == 'content1_title') {
                                            $content1_title_translate[$lang]['value'] = $t->value;
                                        }
                                    }
                                }
                                if (isset($content2_title->translations) && count($content2_title->translations)) {
                                    $content2_title_translate = [];
                                    foreach ($content2_title->translations as $t) {
                                        if ($t->locale == $lang && $t->key == 'content2_title') {
                                            $content2_title_translate[$lang]['value'] = $t->value;
                                        }
                                    }
                                }
                                if (isset($content3_title->translations) && count($content3_title->translations)) {
                                    $content3_title_translate = [];
                                    foreach ($content3_title->translations as $t) {
                                        if ($t->locale == $lang && $t->key == 'content3_title') {
                                            $content3_title_translate[$lang]['value'] = $t->value;
                                        }
                                    }
                                }
                                if (isset($content1_subtitle->translations) && count($content1_subtitle->translations)) {
                                    $content1_subtitle_translate = [];
                                    foreach ($content1_subtitle->translations as $t) {
                                        if ($t->locale == $lang && $t->key == 'content1_subtitle') {
                                            $content1_subtitle_translate[$lang]['value'] = $t->value;
                                        }
                                    }
                                }
                                if (isset($content2_subtitle->translations) && count($content2_subtitle->translations)) {
                                    $content2_subtitle_translate = [];
                                    foreach ($content2_subtitle->translations as $t) {
                                        if ($t->locale == $lang && $t->key == 'content2_subtitle') {
                                            $content2_subtitle_translate[$lang]['value'] = $t->value;
                                        }
                                    }
                                }
                                if (isset($content3_subtitle->translations) && count($content3_subtitle->translations)) {
                                    $content3_subtitle_translate = [];
                                    foreach ($content3_subtitle->translations as $t) {
                                        if ($t->locale == $lang && $t->key == 'content3_subtitle') {
                                            $content3_subtitle_translate[$lang]['value'] = $t->value;
                                        }
                                    }
                                }
                                                            ?>
                                                <div class="d-none lang_form" id="{{ $lang }}-form">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ 'contenido-1' }}</label>
                                                        <div class="row g-3 __bg-F8F9FC-card">
                                                            <div class="col-sm-6">
                                                                <label for="content1_title{{$lang}}" class="form-label">{{ 'Título' }}
                                                                    ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                                        data-toggle="tooltip" data-placement="right"
                                                                        data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                                        <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                                                    </span></label>
                                                                <input type="text" id="content1_title{{$lang}}" maxlength="80"
                                                                    name="content1_title[]"
                                                                    value="{{ $content1_title_translate[$lang]['value'] ?? '' }}"
                                                                    class="form-control" placeholder="{{ 'Ej: Introduzca el título' }}">
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <label for="content1_subtitle{{$lang}}"
                                                                    class="form-label">{{ 'Subtítulo' }}
                                                                    ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                                        data-toggle="tooltip" data-placement="right"
                                                                        data-original-title="{{ 'Escribe el título dentro de 240 caracteres.' }}">
                                                                        <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                                                    </span></label>
                                                                <input type="text" id="content1_subtitle{{$lang}}" maxlength="240"
                                                                    name="content1_subtitle[]"
                                                                    value="{{ $content1_subtitle_translate[$lang]['value'] ?? '' }}"
                                                                    class="form-control" placeholder="{{ 'Ej: introduzca el subtítulo' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">{{ 'contenido-2' }}</label>
                                                        <div class="row g-3 __bg-F8F9FC-card">
                                                            <div class="col-sm-6">
                                                                <label for="content2_title{{$lang}}" class="form-label">{{ 'Título' }}
                                                                    ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                                        data-toggle="tooltip" data-placement="right"
                                                                        data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                                        <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                                                    </span></label>
                                                                <input type="text" id="content2_title{{$lang}}" maxlength="80"
                                                                    name="content2_title[]"
                                                                    value="{{ $content2_title_translate[$lang]['value'] ?? '' }}"
                                                                    class="form-control" placeholder="{{ 'Ej: Introduzca el título' }}">
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <label for="content2_subtitle{{$lang}}"
                                                                    class="form-label">{{ 'Subtítulo' }}
                                                                    ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                                        data-toggle="tooltip" data-placement="right"
                                                                        data-original-title="{{ 'Escribe el título dentro de 240 caracteres.' }}">
                                                                        <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                                                    </span></label>
                                                                <input type="text" id="content2_subtitle{{$lang}}" maxlength="240"
                                                                    name="content2_subtitle[]"
                                                                    value="{{ $content2_subtitle_translate[$lang]['value'] ?? '' }}"
                                                                    class="form-control" placeholder="{{ 'Ej: introduzca el subtítulo' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label">{{ 'contenido-3' }}</label>
                                                        <div class="row g-3 __bg-F8F9FC-card">
                                                            <div class="col-sm-6">
                                                                <label for="content3_title{{$lang}}" class="form-label">{{ 'Título' }}
                                                                    ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                                        data-toggle="tooltip" data-placement="right"
                                                                        data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                                        <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                                                    </span></label>
                                                                <input type="text" id="content3_title{{$lang}}" maxlength="80"
                                                                    name="content3_title[]"
                                                                    value="{{ $content3_title_translate[$lang]['value'] ?? '' }}"
                                                                    class="form-control" placeholder="{{ 'Ej: Introduzca el título' }}">
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <label for="content3_subtitle{{$lang}}"
                                                                    class="form-label">{{ 'Subtítulo' }}
                                                                    ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                                        data-toggle="tooltip" data-placement="right"
                                                                        data-original-title="{{ 'Escribe el título dentro de 240 caracteres.' }}">
                                                                        <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                                                    </span></label>
                                                                <input type="text" maxlength="240" id="content3_subtitle{{$lang}}"
                                                                    name="content3_subtitle[]"
                                                                    value="{{ $content3_subtitle_translate[$lang]['value'] ?? '' }}"
                                                                    class="form-control" placeholder="{{ 'Ej: introduzca el subtítulo' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                            @endforeach
                        @endif
                        <div class="btn--container justify-content-end mt-20">
                            <button type="reset" class="btn btn--reset">{{ 'Reiniciar' }}</button>
                            <button type="submit" class="btn btn--primary call-demo">{{ 'Ahorrar' }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<form id="banner_image_form" action="{{ route('admin.remove_image') }}" method="post">
    @csrf
    <input type="hidden" name="id" value="{{  $banner_image?->id}}">
    <input type="hidden" name="model_name" value="ModuleWiseBanner">
    <input type="hidden" name="image_path" value="promotional_banner">
    <input type="hidden" name="field_name" value="value">
</form>
@endsection
@push('script_2')
    <script src="{{asset('assets/admin/js/view-pages/other-banners.js')}}"></script>
    <script>
        "use strict";
        const input = document.getElementById('inputFile');
        const video = document.getElementById('video-preview');
        const videoSource = document.createElement('source');

        input.addEventListener('change', function () {
            const files = this.files || [];

            if (!files.length) return;

            const reader = new FileReader()
            video.innerHTML = ""
            input.setAttribute('data-title', files[0].name);
            reader.onload = function (e) {
                videoSource.setAttribute('src', e.target.result);
                video.appendChild(videoSource);
                video.load();
                video.play();
            };

            reader.onprogress = function (e) {
                console.log('progress: ', Math.round((e.loaded * 100) / e.total));
            };

            reader.readAsDataURL(files[0]);
        });
    </script>
@endpush