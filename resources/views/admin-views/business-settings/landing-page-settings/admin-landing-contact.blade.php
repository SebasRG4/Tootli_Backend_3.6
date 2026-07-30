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
    @php($contact_us_title=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','admin_landing_page')->where('key','contact_us_title')->first())
    @php($contact_us_sub_title=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','admin_landing_page')->where('key','contact_us_sub_title')->first())
    @php($contact_us_image=\App\Models\DataSetting::withoutGlobalScope('translate')->where('type','admin_landing_page')->where('key','contact_us_image')->first())
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
            <form action="{{ route('admin.business-settings.admin-landing-page-settings', 'contact-us-section') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                @if ($language)
                                <div class="col-md-12 lang_form default-form">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="contact_us_title" class="form-label">{{'Título'}} ({{ 'por defecto' }})<span
                                        class="form-label-secondary" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                            alt="">
                                    </span>
                                                <span class="form-label-secondary text-danger"
                                                      data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ 'Requerido.'}}"> *
                                                </span></label>
                                <input required id="contact_us_title" type="text" maxlength="20" name="contact_us_title[]" value="{{ $contact_us_title?->getRawOriginal('value') }}" class="form-control" placeholder="{{'Ej: Contáctenos'}}">
                                        </div>
                                        <div class="col-12">
                                            <label for="contact_us_sub_title" class="form-label">{{'Subtítulo'}} ({{ 'por defecto' }})<span
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
                                <input required id="contact_us_sub_title" type="text" maxlength="80" name="contact_us_sub_title[]" value="{{ $contact_us_sub_title?->getRawOriginal('value') }}" class="form-control" placeholder="{{'Ej: ¿Alguna pregunta o comentario? ¡Solo escríbenos un mensaje!'}}">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                    @foreach(json_decode($language) as $lang)
                                    <?php
                                    if(isset($contact_us_title->translations)&&count($contact_us_title->translations)){
                                            $contact_us_title_translate = [];
                                            foreach($contact_us_title->translations as $t)
                                            {
                                                if($t->locale == $lang && $t->key=='contact_us_title'){
                                                    $contact_us_title_translate[$lang]['value'] = $t->value;
                                                }
                                            }

                                        }
                                    if(isset($contact_us_sub_title->translations)&&count($contact_us_sub_title->translations)){
                                            $contact_us_sub_title_translate = [];
                                            foreach($contact_us_sub_title->translations as $t)
                                            {
                                                if($t->locale == $lang && $t->key=='contact_us_sub_title'){
                                                    $contact_us_sub_title_translate[$lang]['value'] = $t->value;
                                                }
                                            }

                                        }
                                        ?>
                                    <div class="col-md-12 d-none lang_form" id="{{$lang}}-form1">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="contact_us_title{{$lang}}" class="form-label">{{'Título'}} ({{strtoupper($lang)}})<span
                                        class="form-label-secondary" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                            alt="">
                                    </span></label>
                                <input id="contact_us_title{{$lang}}" type="text" maxlength="20" name="contact_us_title[]" value="{{ $contact_us_title_translate[$lang]['value']??'' }}" class="form-control" placeholder="{{'Ej: Contáctenos'}}">
                                            </div>
                                            <div class="col-12">
                                                <label for="contact_us_sub_title{{$lang}}" class="form-label">{{'Subtítulo'}} ({{strtoupper($lang)}})<span
                                        class="form-label-secondary" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                            alt="">
                                    </span></label>
                                <input id="contact_us_sub_title{{$lang}}" type="text" maxlength="80" name="contact_us_sub_title[]" value="{{ $contact_us_sub_title_translate[$lang]['value']??'' }}" class="form-control" placeholder="{{'Ej: ¿Alguna pregunta o comentario? ¡Solo escríbenos un mensaje!'}}">
                                            </div>
                                        </div>
                                    </div>
                                        <input type="hidden" name="lang[]" value="{{$lang}}">
                                    @endforeach
                                @else
                                <div class="col-md-12">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="contact_us_title" class="form-label">{{'Título'}}<span
                                        class="form-label-secondary" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                            alt="">
                                    </span></label>
                                <input id="contact_us_title" type="text" maxlength="20" name="contact_us_title[]" class="form-control" placeholder="{{'Ej: Contáctenos'}}">
                                        </div>
                                        <div class="col-12">
                                            <label for="contact_us_sub_title" class="form-label">{{'Subtítulo'}}<span
                                        class="form-label-secondary" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                            alt="">
                                    </span></label>
                                <input id="contact_us_sub_title" type="text" maxlength="80" name="contact_us_sub_title[]" class="form-control" placeholder="{{'Ej: ¿Alguna pregunta o comentario? ¡Solo escríbenos un mensaje!'}}">
                                        </div>
                                    </div>
                                </div>
                                    <input type="hidden" name="lang[]" value="default">
                                @endif
                            </div>
                            <div class="col-md-6">
                                    <label class="form-label d-block mb-3">
                                        {{ 'Bandera' }}  <span class="text--primary">(size: 6:1)</span>
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
                                            <img
                                            src="{{\App\CentralLogics\Helpers::get_full_url('contact_us_image', $contact_us_image?->value?? '', $contact_us_image?->storage[0]?->value ?? 'public','upload_image_4')}}"

                                          class="vertical-img mw-100 onerror-image" alt="contact_us_image" data-onerror-image="{{asset("assets/admin/img/upload-4.png")}}">
                                        </div>
                                          <input accept="{{IMAGE_EXTENSION}}" class="upload-file__input single_file_input" type="file"  name="image" hidden="">
                                          @if (isset($contact_us_image['value']))
                                            <span id="contact_image" class="remove_image_button remove-image dynamic-checkbox"
                                                  data-id="contact_image"
                                                  data-image-off="{{ asset('assets/admin/img/delete-confirmation.png') }}"
                                                  data-title="{{'¡Advertencia!'}}"
                                                  data-text="<p>{{'¿Estás seguro de que deseas eliminar esta imagen?'}}</p>"
                                            > <i class="tio-clear"></i></span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                        </div>
                    </div>
                </div>
                <h5 class="card-title mb-3 mt-3">
                    <span class="card-header-icon mr-2"><i class="tio-poi"></i></span> <span>{{'Apertura y cierre de oficinas'}}</span>
                </h5>
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6 col-lg-3">
                                @php($opening_time = \App\Models\BusinessSetting::where('key', 'opening_time')->first())
                                <label for="opening_time" class="form-label">{{'Hora de inicio'}}</label>
                                <input  type="time" value="{{ $opening_time ? $opening_time->value: '' }}" name="opening_time" class="form-control" id="opening_time">
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                 @php($closing_time = \App\Models\BusinessSetting::where('key', 'closing_time')->first())
                                <label for="closing_time" class="form-label">{{'Hora de finalización'}}</label>
                                <input type="time" value="{{ $closing_time ? $closing_time->value: '' }}" name="closing_time" class="form-control" id="closing_time">
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                @php($opening_day = \App\Models\BusinessSetting::where('key', 'opening_day')->first())
                                @php($opening_day = $opening_day ? $opening_day->value : '')
                                <label for="opening_day" class="form-label">{{'Día de inicio'}}</label>
                                <select id="opening_day" name="opening_day" class="form-control">
                                    <option value="saturday" {{ $opening_day == 'saturday' ? 'selected' : '' }}>
                                        {{ 'sábado' }}
                                    </option>
                                    <option value="sunday" {{ $opening_day == 'sunday' ? 'selected' : '' }}>
                                        {{ 'domingo' }}
                                    </option>
                                    <option value="monday" {{ $opening_day == 'monday' ? 'selected' : '' }}>
                                        {{ 'lunes' }}
                                    </option>
                                    <option value="tuesday" {{ $opening_day == 'tuesday' ? 'selected' : '' }}>
                                        {{ 'martes' }}
                                    </option>
                                    <option value="wednesday" {{ $opening_day == 'wednesday' ? 'selected' : '' }}>
                                        {{ 'miércoles' }}
                                    </option>
                                    <option value="thrusday" {{ $opening_day == 'thrusday' ? 'selected' : '' }}>
                                        {{ 'jueves' }}
                                    </option>
                                    <option value="friday" {{ $opening_day == 'friday' ? 'selected' : '' }}>
                                        {{ 'viernes' }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                @php($closing_day = \App\Models\BusinessSetting::where('key', 'closing_day')->first())
                                @php($closing_day = $closing_day ? $closing_day->value : '')
                                <label for="closing_day" class="form-label">{{'Día final'}}</label>
                                <select id="closing_day" name="closing_day" class="form-control">
                                    <option value="saturday" {{ $closing_day == 'saturday' ? 'selected' : '' }}>
                                        {{ 'sábado' }}
                                    </option>
                                    <option value="sunday" {{ $closing_day == 'sunday' ? 'selected' : '' }}>
                                        {{ 'domingo' }}
                                    </option>
                                    <option value="monday" {{ $closing_day == 'monday' ? 'selected' : '' }}>
                                        {{ 'lunes' }}
                                    </option>
                                    <option value="tuesday" {{ $closing_day == 'tuesday' ? 'selected' : '' }}>
                                        {{ 'martes' }}
                                    </option>
                                    <option value="wednesday" {{ $closing_day == 'wednesday' ? 'selected' : '' }}>
                                        {{ 'miércoles' }}
                                    </option>
                                    <option value="thrusday" {{ $closing_day == 'thrusday' ? 'selected' : '' }}>
                                        {{ 'Jueves' }}
                                    </option>
                                    <option value="friday" {{ $closing_day == 'friday' ? 'selected' : '' }}>
                                        {{ 'viernes' }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="btn--container justify-content-end mt-20">
                    <button type="reset" class="btn btn--reset mb-2">{{'Reiniciar'}}</button>
                    <button type="submit"   class="btn btn--primary mb-2">{{'Guardar información'}}</button>
                </div>
            </form>
            <form  id="contact_image_form" action="{{ route('admin.remove_image') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{  $contact_us_image?->id}}" >
                <input type="hidden" name="model_name" value="DataSetting" >
                <input type="hidden" name="image_path" value="contact_us_image" >
                <input type="hidden" name="field_name" value="value" >
            </form>

        </div>
    </div>
</div>
    <!-- How it Works -->
    @include('admin-views.business-settings.landing-page-settings.partial.how-it-work')
@endsection
