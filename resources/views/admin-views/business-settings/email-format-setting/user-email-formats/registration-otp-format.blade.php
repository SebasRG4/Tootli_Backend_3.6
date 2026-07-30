@extends('layouts.admin.app')

@section('title', 'plantilla de correo electrónico')
@push('css_or_js')
<link rel="stylesheet" href="{{asset('assets/admin/css/view-pages/email-templates.css')}}">
@endpush


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center __gap-15px">
                <h1 class="page-header-title mr-3 mb-0">
                    <span class="page-header-icon">
                        <img src="{{ asset('assets/admin/img/email-setting.png') }}" class="w--26" alt="">
                    </span>
                    <span>
                        {{ 'Plantillas de correo electrónico' }}
                    </span>
                </h1>
                @include('admin-views.business-settings.email-format-setting.partials.email-template-options')
            </div>
            @include('admin-views.business-settings.email-format-setting.partials.user-email-template-setting-links')
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active">
                <div class="card mb-3">
                    @php($mail_status=\App\Models\BusinessSetting::where('key','registration_otp_mail_status_user')->first()?->value ??  '0' )
                    <div class="card-body">
                        <div class="maintenance-mode-toggle-bar d-flex flex-wrap justify-content-between border rounded align-items-center p-2">
                            <h5 class="text-capitalize m-0 text--primary pl-2">
                                {{'¿Enviar correo en \'OTP de registro\'?'}}
                        <span class="form-label-secondary text--primary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Los clientes recibirán un correo electrónico automatizado con una OTP para confirmar su registro.' }}">
                                    <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                </span>
                            </h5>
                            <label class="toggle-switch toggle-switch-sm">
                                <input type="checkbox" class="status toggle-switch-input dynamic-checkbox"
                                       data-id="mail-status"
                                       data-type="status"
                                       data-image-on='{{asset('assets/admin/img/modal')}}/place-order-on.png'
                                       data-image-off="{{asset('assets/admin/img/modal')}}/place-order-off.png"
                                       data-title-on="{{'¿Quiere habilitar el correo de registro de usuario?'}}"
                                       data-title-off="{{'¿Quiere desactivar el correo de registro de usuario?'}}"
                                       data-text-on="<p>{{'Si está habilitado, los Clientes recibirán OTP en su correo para confirmar el registro.'}}</p>"
                                       data-text-off="<p>{{'Si está deshabilitado, los Clientes no recibirán ningún correo electrónico al registrarse en la OTP.'}}</p>"
                                       id="mail-status" {{$mail_status == '1'?'checked':''}}>

                                <span class="toggle-switch-label text mb-0">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                        <form action="{{route('admin.business-settings.email-status',['user','registration-otp',$mail_status == '1'?0:1])}}" method="get" id="mail-status_form">
                        </form>
                    </div>
                </div>
                @php($data=\App\Models\EmailTemplate::where('type','user')->where('email_type', 'registration_otp')->first())
                @php($template= $template ?? $data?->email_template ?? 4)
                <form action="{{ route('admin.business-settings.email-setup', ['user','registration-otp']) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card border-0">
                        <div class="card-body">
                            <div class="email-format-wrapper">
                                <div class="left-content">
                                    <div class="d-inline-block">
                                        @include('admin-views.business-settings.email-format-setting.partials.email-template-section')
                                    </div>
                                    <div class="card">
                                        <div class="card-body">
                                            @include('admin-views.business-settings.email-format-setting.templates.email-format-'.$template)
                                        </div>
                                    </div>
                                </div>
                                <div class="right-content">
                                    <div class="d-flex flex-wrap justify-content-between __gap-15px mt-2 mb-5">
                                        @php($data=\App\Models\EmailTemplate::withoutGlobalScope('translate')->where('type','user')->where('email_type', 'registration_otp')->first())
                                        @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                                        @php($language = $language->value ?? null)
                                        @php($defaultLang = str_replace('_', '-', app()->getLocale()))
                                        @if($language)
                                            <ul class="nav nav-tabs m-0 border-0">
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
                                        <div class="d-flex justify-content-end">
                                            <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center py-1" type="button" data-toggle="modal" data-target="#instructions">
                                                <strong class="mr-2">{{'Leer instrucciones'}}</strong>
                                                <div class="blinkings">
                                                    <i class="tio-info-outined"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-3">
                                            {{'Icono'}}
                                        </h5>
                                        <label class="custom-file">
                                            <input type="file" name="icon" id="mail-icon" class="custom-file-input" accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                            <span class="custom-file-label">{{ 'Elija archivo' }}</span>
                                        </label>
                                    </div>
                                    <br>
                                    <div>
                                        <h5 class="card-title mb-3">
                                            <img src="{{asset('assets/admin/img/pointer.png')}}" class="mr-2" alt="">
                                            {{'Contenido del encabezado'}}
                                        </h5>
                                        @if ($language)
                                            <div class="__bg-F8F9FC-card default-form lang_form" id="default-form">
                                                <div class="form-group">
                                                    <label class="form-label">{{'Título principal'}}({{ 'por defecto' }})</label>
                                                    <input type="text" name="title[]" value="{{ $data?->getRawOriginal('title') }}" data-id="mail-title" placeholder="Order has been placed successfully !" class="form-control">
                                                </div>
                                                <div class="form-group mb-0">
                                                    <label class="form-label">
                                                        {{ 'Mensaje del cuerpo del correo' }}({{ 'por defecto' }})

                                                    </label>
                                                    <textarea class="form-control" id="ckeditor" data-id="mail-body" name="body[]">
                                                        {!! $data?->getRawOriginal('body') !!}
                                                    </textarea>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            @foreach(json_decode($language) as $lang)
                                            <?php
                                            if($data && count($data['translations'])){
                                                $translate = [];
                                                foreach($data['translations'] as $t)
                                                {
                                                    if($t->locale == $lang && $t->key=="title"){
                                                        $translate[$lang]['title'] = $t->value;
                                                    }
                                                    if($t->locale == $lang && $t->key=="body"){
                                                        $translate[$lang]['body'] = $t->value;
                                                    }
                                                }
                                            }
                                                ?>
                                                <div class="__bg-F8F9FC-card d-none lang_form" id="{{$lang}}-form">
                                                    <div class="form-group">
                                                        <label class="form-label">{{'Título principal'}}({{strtoupper($lang)}})</label>
                                                        <input type="text" name="title[]" placeholder="Order has been placed successfully !" class="form-control" value="{{$translate[$lang]['title']??''}}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="form-label">
                                                            {{ 'Mensaje del cuerpo del correo' }}({{strtoupper($lang)}})

                                                        </label>
                                                        <textarea class="ckeditor form-control" name="body[]">
                                                           {!! $translate[$lang]['body']??'' !!}
                                                        </textarea>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                            @endforeach
                                        @else
                                            <div class="__bg-F8F9FC-card default-form">
                                                <div class="form-group">
                                                    <label class="form-label">{{'Título principal'}}</label>
                                                    <input type="text" name="title[]" placeholder="Order has been placed successfully !" class="form-control">
                                                </div>
                                                <div class="form-group mb-0">
                                                    <label class="form-label">
                                                        {{ 'Mensaje del cuerpo del correo' }}

                                                    </label>
                                                    <textarea class="ckeditor form-control" name="body[]">
                                                      {{ 'Hola sabrina' }},
                                                    </textarea>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        @endif

                                    </div>
                                    <br>
                                    <div>
                                        <h5 class="card-title mb-3">
                                            <img src="{{asset('assets/admin/img/pointer.png')}}" class="mr-2" alt="">
                                            {{'Contenido del pie de página'}}
                                        </h5>
                                        <div class="__bg-F8F9FC-card">
                                                @if ($language)
                                                        <div class="form-group lang_form default-form">
                                                            <label class="form-label">
                                                                {{'Texto de la sección'}}({{ 'por defecto' }})
                                                            </label>
                                                            <input type="text" data-id="mail-footer" name="footer_text[]"  placeholder="{{ 'Por favor contáctenos para cualquier consulta; Siempre estaremos felices de ayudar.' }}"class="form-control" value="{{ $data?->getRawOriginal('footer_text') }}">
                                                        </div>
                                                    @foreach(json_decode($language) as $lang)
                                                    <?php
                                                    if($data && count($data['translations'])){
                                                        $translate = [];
                                                        foreach($data['translations'] as $t)
                                                        {
                                                            if($t->locale == $lang && $t->key=="footer_text"){
                                                                $translate[$lang]['footer_text'] = $t->value;
                                                            }
                                                        }
                                                        }
                                                        ?>
                                                        <div class="form-group d-none lang_form" id="{{$lang}}-form2">
                                                            <label class="form-label">
                                                                {{'Texto de la sección'}}({{strtoupper($lang)}})
                                                            </label>
                                                            <input type="text" name="footer_text[]"  placeholder="{{ 'Por favor contáctenos para cualquier consulta; Siempre estaremos felices de ayudar.' }}"class="form-control" value="{{ $translate[$lang]['footer_text']??'' }}">
                                                        </div>
                                                    @endforeach
                                                @else
                                                <div class="form-group">
                                                    <label class="form-label">
                                                        {{'Texto de la sección'}}

                                                    </label>
                                                    <input type="text" placeholder="{{ 'Por favor contáctenos para cualquier consulta; Siempre estaremos felices de ayudar.' }}"class="form-control" name="footer_text[]" value="">
                                                </div>
                                                @endif
                                              @include('admin-views.business-settings.email-format-setting.partials.social-media-and-footer-section')
                                            <div class="form-group mb-0">
                                                @if ($language)
                                                        <div class="form-group lang_form default-form">
                                                            <label class="form-label">
                                                                {{'Contenido protegido por derechos de autor'}}({{ 'por defecto' }})
                                                            </label>
                                                            <input type="text" data-id="mail-copyright" name="copyright_text[]"  placeholder="{{ 'Ej: Copyright 2023 6amMart. Todos los derechos reservados' }}" class="form-control" value="{{ $data?->getRawOriginal('copyright_text') }}">
                                                        </div>
                                                    @foreach(json_decode($language) as $lang)
                                                    <?php
                                           $translate = [];
                                           if($data && count($data['translations'])){
                                                        foreach($data['translations'] as $t)
                                                        {
                                                            if($t->locale == $lang && $t->key=="copyright_text"){
                                                                $translate[$lang]['copyright_text'] = $t->value;
                                                            }
                                                        }
                                                        }
                                                        ?>
                                                        <div class="form-group d-none lang_form" id="{{$lang}}-form3">
                                                            <label class="form-label">
                                                                {{'Contenido protegido por derechos de autor'}}({{strtoupper($lang)}})
                                                            </label>
                                                            <input type="text" name="copyright_text[]"  placeholder="{{ 'Ej: Copyright 2023 6amMart. Todos los derechos reservados' }}" class="form-control" value="{{ $translate[$lang]['copyright_text']??'' }}">
                                                        </div>
                                                    @endforeach
                                                @else
                                                <div class="form-group">
                                                    <label class="form-label">
                                                        {{'Contenido protegido por derechos de autor'}}

                                                    </label>
                                                    <input type="text" placeholder="{{ 'Ej: Copyright 2023 6amMart. Todos los derechos reservados' }}" class="form-control" name="copyright_text[]" value="">
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="btn--container justify-content-end mt-20">
                                        <button type="reset" id="reset_btn" class="btn btn--reset">{{'Reiniciar'}}</button>
                                        <button type="submit" class="btn btn--primary">{{'Ahorrar'}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>



            </div>
        </div>


        <!-- Instructions Modal -->
@include('admin-views.business-settings.email-format-setting.partials.email-template-instructions')

    </div>

@endsection
@push('script_2')
    <!-- Email Template-->
    <script src="{{asset('assets/admin/ckeditor/ckeditor.js')}}"></script>
    <script src="{{asset('assets/admin/js/view-pages/email-templates.js')}}"></script>
    <!-- Email Template End-->
@endpush
