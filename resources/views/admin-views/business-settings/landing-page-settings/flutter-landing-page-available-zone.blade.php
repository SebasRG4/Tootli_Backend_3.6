@extends('layouts.admin.app')

@section('title','página de inicio web de aleteo')

@section('content')

<div class="content container-fluid">
    <div class="page-header pb-0">
        <div class="d-flex flex-wrap justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/flutter.png')}}" class="w--15" alt="">
                </span>
                <span>
                    {{ 'página de inicio web de aleteo' }}
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
            @include('admin-views.business-settings.landing-page-settings.top-menu-links.flutter-landing-page-links')
        </div>
    </div>
    @php($available_zone_title=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','flutter_landing_page')->where('key','available_zone_title')->first())
    @php($available_zone_short_description=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','flutter_landing_page')->where('key','available_zone_short_description')->first())
    @php($available_zone_image=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','flutter_landing_page')->where('key','available_zone_image')->first())
    @php($available_zone_status=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','flutter_landing_page')->where('key','available_zone_status')->first())
    @php($available_zone_status = $available_zone_status ? $available_zone_status->value : 0)
    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
    @php($language = $language->value ?? null)
    @php($defaultLang = str_replace('_', '-', app()->getLocale()))

    <form id="zone-setup-form" action="{{ route('admin.business-settings.flutter-landing-page-settings', 'available-zone-section') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        {{ 'Para ver una lista de todas las zonas activas en su página de inicio de Flutter' }} <br class="d-none d-md-inline-block"> {{ 'Habilitar el'}} <strong>{{ '`Zonas disponibles`' }}</strong> {{'característica' }}
                    </div>
                    <div class="col-sm-6">
                        <label
                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1 text--primary">
                                                    {{'zona disponible' }}
                                                </span>
                                            </span>
                            <input type="checkbox"
                                   data-id="available_zone_status"
                                   data-type="toggle"
                                   data-image-on="{{ asset('assets/admin/img/modal/dm-tips-on.png') }}"
                                   data-image-off="{{ asset('assets/admin/img/modal/dm-tips-off.png') }}"
                                   data-title-on="<strong>{{ '¿Quieres habilitar la zona disponible?' }}</strong>"
                                   data-title-off="<strong>{{ '¿Quieres desactivar la zona disponible?' }}</strong>"
                                   data-text-on="<p>{{ 'Si habilita esto, la sección de zona disponible será visible.' }}</p>"
                                   data-text-off="<p>{{ 'Si desactiva esto, la sección de zona disponible no será visible.' }}</p>"
                                   class="status toggle-switch-input dynamic-checkbox-toggle"
                                   value="1"
                                   name="available_zone_status" id="available_zone_status"
                                {{ $available_zone_status == 1 ? 'checked' : '' }}>
                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card shadow--card-2">
                    <div class="card-body">
                        @if($language)
                            <ul class="nav nav-tabs mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                       href="#"
                                       id="default-link">{{ 'Por defecto' }}</a>
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
                        @if ($language)
                            <div class="lang_form"
                                 id="default-form">
                                <div class="form-group">
                                    <label class="input-label"
                                           for="default_title">{{ 'título' }}
                                        ({{ 'Por defecto' }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Escribe el título dentro de 50 caracteres.' }}">
                                                <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                            </span>
                                    </label>
                                    <input type="text" name="available_zone_title[]" maxlength="50" id="default_title"
                                           class="form-control" placeholder="{{ 'título' }}" value="{{$available_zone_title?->getRawOriginal('value')}}"
                                    >
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                <div class="form-group mb-0">
                                    <label class="input-label"
                                           for="exampleFormControlInput1">{{ 'breve descripción' }} ({{ 'por defecto' }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Escriba la breve descripción dentro de 200 caracteres.' }}">
                                                <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                            </span></label>
                                    <textarea type="text" name="available_zone_short_description[]" maxlength="200" placeholder="{{'breve descripción'}}" class="form-control min-h-90px ckeditor">{{$available_zone_short_description?->getRawOriginal('value')}}</textarea>
                                </div>
                            </div>
                            @foreach (json_decode($language) as $lang)
                                    <?php
                                    if(isset($available_zone_title->translations)&&count($available_zone_title->translations)){
                                        $available_zone_title_translate = [];
                                        foreach($available_zone_title->translations as $t)
                                        {
                                            if($t->locale == $lang && $t->key=='available_zone_title'){
                                                $available_zone_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }

                                    }
                                    if(isset($available_zone_short_description->translations)&&count($available_zone_short_description->translations)){
                                        $available_zone_short_description_translate = [];
                                        foreach($available_zone_short_description->translations as $t)
                                        {
                                            if($t->locale == $lang && $t->key=='available_zone_short_description'){
                                                $available_zone_short_description_translate[$lang]['value'] = $t->value;
                                            }
                                        }

                                    }
                                    ?>
                                <div class="d-none lang_form"
                                     id="{{ $lang }}-form">
                                    <div class="form-group">
                                        <label class="input-label"
                                               for="{{ $lang }}_title">{{ 'título' }}
                                            ({{ strtoupper($lang) }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Escribe el título dentro de 50 caracteres.' }}">
                                                <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                            </span>
                                        </label>
                                        <input type="text" name="available_zone_title[]" maxlength="50" id="{{ $lang }}_title"
                                               class="form-control" value="{{ $available_zone_title_translate[$lang]['value']??'' }}" placeholder="{{ 'título' }}">
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                               for="exampleFormControlInput1">{{ 'breve descripción' }} ({{ strtoupper($lang) }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Escriba la breve descripción dentro de 200 caracteres.' }}">
                                                <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                            </span></label>
                                        <textarea type="text" name="available_zone_short_description[]" maxlength="200" placeholder="{{'breve descripción'}}" class="form-control min-h-90px ckeditor">{{ $available_zone_short_description_translate[$lang]['value']??'' }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div id="default-form">
                                <div class="form-group">
                                    <label class="input-label"
                                           for="exampleFormControlInput1">{{ 'título' }} ({{ 'por defecto' }})</label>
                                    <input type="text" name="available_zone_title[]" class="form-control"
                                           placeholder="{{ 'título' }}" >
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                <div class="form-group mb-0">
                                    <label class="input-label"
                                           for="exampleFormControlInput1">{{ 'breve descripción' }}
                                    </label>
                                    <textarea type="text" name="available_zone_short_description[]" placeholder="{{'breve descripción'}}" class="form-control min-h-90px ckeditor"></textarea>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div>
                            <div class="d-flex justify-content-center">
                                <label class="text-dark d-block mb-4">
                                    <strong>{{ 'Imagen relacionada' }}</strong>
                                    <small class="text-danger">* {{ '(Relación 1:1)' }}</small>
                                </label>
                            </div>
                            <div class="d-flex justify-content-center">
                                <label class="text-center position-relative">
                                    <img class="img--110 min-height-170px min-width-170px onerror-image image--border" id="viewer"
                                         data-onerror-image="{{ asset('assets/admin/img/upload.png') }}"
                                         src="{{\App\CentralLogics\Helpers::get_full_url('available_zone_image', $available_zone_image?->value?? '', $available_zone_image?->storage[0]?->value ?? 'public','upload_image')}}"
                                         alt="logo image" />
                                    <div class="icon-file-group">
                                        <div class="icon-file">
                                            <i class="tio-edit"></i>
                                            <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                                   accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" >
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card shadow-none border-0 bg-soft-danger">
                    <div class="card-body d-flex">
                        <i class="mr-2 mt-3 text-danger tio-info-outined"></i>
                        <p class="fs-15 text-dark m-0">
                            <strong>{{ 'Nota:' }}</strong> {{ 'Personalice la sección agregando un título, una breve descripción e imágenes en el' }} <a href="{{ route('admin.business-settings.zone.home') }}" target="_blank" class="text--underline text-006AE5">{{ 'Configuración de zona' }}</a> {{ 'sección. Todas las zonas creadas se mostrarán automáticamente en la página de inicio de Flutter. Las zonas se basarán en el nombre para mostrar de la zona.' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="btn--container justify-content-end">
                    <button class="btn btn--reset" type="reset">{{'reiniciar'}}</button>
                    <button class="btn btn--primary" type="submit">{{'Guardar información'}}</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- How it Works -->
@include('admin-views.business-settings.landing-page-settings.partial.how-it-work-react')
@endsection
@push('script_2')
    <script>
        // Form on reset
        const prevImage = $('#viewer').attr('src');
        $('#zone-setup-form').on('reset', function(){
            $('#customFileEg1').val(null);
            $('#viewer').attr('src', prevImage);
        })

        function readURL(input, viewer) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();

                reader.onload = function (e) {
                    $('#'+viewer).attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this, 'viewer');
        });
    </script>
@endpush


