@extends('layouts.admin.app')

@section('title', 'página de inicio web de aleteo')

@section('content')

    <div class="content container-fluid">
        <div class="page-header pb-0">
            <div class="d-flex flex-wrap justify-content-between">
                <h1 class="page-header-title">
                    <span class="page-header-icon">
                        <img src="{{ asset('assets/admin/img/flutter.png') }}" class="w--15" alt="">
                    </span>
                    <span>
                        {{ 'página de inicio web de aleteo' }}
                    </span>
                </h1>
                <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center" type="button" data-toggle="modal"
                    data-target="#how-it-works">
                    <strong class="mr-2">{{ '¡Mira cómo funciona!' }}</strong>
                    <div>
                        <i class="tio-info-outined"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-4 mt-2">
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                @include('admin-views.business-settings.landing-page-settings.top-menu-links.flutter-landing-page-links')
            </div>
        </div>
        @php($join_seller_flutter_status = \App\Models\DataSetting::withoutGlobalScope('translate')->where('type', 'flutter_landing_page')->where('key', 'join_seller_flutter_status')->first()?->value)
        @php($join_DM_flutter_status = \App\Models\DataSetting::withoutGlobalScope('translate')->where('type', 'flutter_landing_page')->where('key', 'join_DM_flutter_status')->first()?->value)


        @php($join_seller_title = \App\Models\DataSetting::withoutGlobalScope('translate')->where('type', 'flutter_landing_page')->where('key', 'join_seller_title')->first())
        @php($join_seller_sub_title = \App\Models\DataSetting::withoutGlobalScope('translate')->where('type', 'flutter_landing_page')->where('key', 'join_seller_sub_title')->first())
        @php($join_seller_button_name = \App\Models\DataSetting::withoutGlobalScope('translate')->where('type', 'flutter_landing_page')->where('key', 'join_seller_button_name')->first())
        @php($join_delivery_man_title = \App\Models\DataSetting::withoutGlobalScope('translate')->where('type', 'flutter_landing_page')->where('key', 'join_delivery_man_title')->first())
        @php($join_delivery_man_sub_title = \App\Models\DataSetting::withoutGlobalScope('translate')->where('type', 'flutter_landing_page')->where('key', 'join_delivery_man_sub_title')->first())
        @php($join_delivery_man_button_name = \App\Models\DataSetting::withoutGlobalScope('translate')->where('type', 'flutter_landing_page')->where('key', 'join_delivery_man_button_name')->first())

        @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
        @php($language = $language->value ?? null)
        @php($defaultLang = str_replace('_', '-', app()->getLocale()))
        @if ($language)
            <ul class="nav nav-tabs mb-4 border-0">
                <li class="nav-item">
                    <a class="nav-link lang_link active" href="#"
                        id="default-link">{{ 'por defecto' }}</a>
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
                <form action="{{ route('admin.business-settings.flutter-landing-page-settings', 'join-seller') }}" method="post" id="join_seller_flutter_status_form">
                    @csrf
                    <input type="hidden" name="join_seller_flutter_status" value="{{ $join_seller_flutter_status ?? 0 }}">
                </form>

                <form action="{{ route('admin.business-settings.flutter-landing-page-settings', 'join-seller') }}"
                    method="POST">
                    @csrf

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-3 mt-3">
                                <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span>
                                <span>{{ 'Únase como sección de vendedor' }}</span>
                            </h5>

                            <label class="toggle-switch justify-content-end  rounded">
                                <input type="checkbox" data-id="join_seller_flutter_status" data-type="status"
                                    data-image-on="{{ asset('assets/admin/img/modal/seller-app-on.png') }}"
                                    data-image-off="{{ asset('assets/admin/img/modal/seller-app-off.png') }}"
                                    data-title-on="<strong>{{ '¿Quieres habilitar la sección Unirse como vendedor?' }}</strong>"
                                    data-title-off="<strong>{{ '¿Quieres desactivar la sección Unirse como vendedor?' }}</strong>"
                                    data-text-on="<p>{{ 'Si habilita esto, la sección Únase como vendedor será visible.' }}</p>"
                                    data-text-off="<p>{{ 'Si desactiva esto, la sección Unirse como vendedor no será visible.' }}</p>"
                                    class="status toggle-switch-input dynamic-checkbox" value="1"
                                    name="" id="join_seller_flutter_status"
                                    {{ $join_seller_flutter_status == 1 ? 'checked' : '' }}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                        <div class="card-body {{ $join_seller_flutter_status != 1 ? 'd-none' : '' }}">
                            @if ($language)
                                <div class="row g-3 lang_form default-form">
                                    <div class="col-sm-6">
                                        <label for="join_seller_title" class="form-label">{{ 'Título' }}
                                            ({{ 'por defecto' }})<span class="form-label-secondary"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <input type="text" id="join_seller_title" maxlength="20"
                                            name="join_seller_title[]" class="form-control"
                                            value="{{ $join_seller_title?->getRawOriginal('value') ?? '' }}"
                                            placeholder="{{ 'título aquí...' }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="join_seller_button_name"
                                            class="form-label">{{ 'Nombre del botón' }}
                                            ({{ 'por defecto' }})<span class="form-label-secondary"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 15 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <input id="join_seller_button_name" type="text" maxlength="15"
                                            name="join_seller_button_name[]" class="form-control"
                                            value="{{ $join_seller_button_name?->getRawOriginal('value') ?? '' }}"
                                            placeholder="{{ 'nombre del botón aquí...' }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="join_seller_sub_title" class="form-label">{{ 'Subtítulo' }}
                                            ({{ 'por defecto' }})<span class="form-label-secondary"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 60 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <textarea id="join_seller_sub_title" placeholder="{{ 'subtítulo aquí...' }}" maxlength="60"
                                            name="join_seller_sub_title[]" class="form-control" rows="2">{{ $join_seller_sub_title?->getRawOriginal('value') ?? '' }}</textarea>
                                    </div>

                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                @foreach (json_decode($language) as $lang)
                                    <?php
                                    if (isset($join_seller_title->translations) && count($join_seller_title->translations)) {
                                        $join_seller_title_translate = [];
                                        foreach ($join_seller_title->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'join_seller_title') {
                                                $join_seller_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    if (isset($join_seller_sub_title->translations) && count($join_seller_sub_title->translations)) {
                                        $join_seller_sub_title_translate = [];
                                        foreach ($join_seller_sub_title->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'join_seller_sub_title') {
                                                $join_seller_sub_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    if (isset($join_seller_button_name->translations) && count($join_seller_button_name->translations)) {
                                        $join_seller_button_name_translate = [];
                                        foreach ($join_seller_button_name->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'join_seller_button_name') {
                                                $join_seller_button_name_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="row g-3 d-none lang_form" id="{{ $lang }}-form">
                                        <div class="col-sm-6">
                                            <label for="join_seller_title{{ $lang }}"
                                                class="form-label">{{ 'Título' }}
                                                ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <input type="text" id="join_seller_title{{ $lang }}"
                                                maxlength="20" name="join_seller_title[]" class="form-control"
                                                value="{{ $join_seller_title_translate[$lang]['value'] ?? '' }}"
                                                placeholder="{{ 'título aquí...' }}">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="join_seller_button_name{{ $lang }}"
                                                class="form-label">{{ 'Nombre del botón' }}
                                                ({{ strtoupper($lang) }})
                                                <span class="form-label-secondary" data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 15 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <input id="join_seller_button_name{{ $lang }}" type="text"
                                                maxlength="15" name="join_seller_button_name[]" class="form-control"
                                                value="{{ $join_seller_button_name_translate[$lang]['value'] ?? '' }}"
                                                placeholder="{{ 'nombre del botón aquí...' }}">
                                        </div>

                                        <div class="col-sm-6">
                                            <label for="join_seller_sub_title{{ $lang }}"
                                                class="form-label">{{ 'Subtítulo' }}
                                                ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 60 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <textarea id="join_seller_sub_title{{ $lang }}" type="text"
                                                placeholder="{{ 'subtítulo aquí...' }}" maxlength="60" name="join_seller_sub_title[]"
                                                class="form-control" rows="2">{{ $join_seller_sub_title_translate[$lang]['value'] ?? '' }}</textarea>
                                        </div>

                                    </div>
                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                @endforeach
                            @else
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label for="join_seller_title" class="form-label">{{ 'Título' }}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <input type="text" id="join_seller_title" maxlength="20"
                                            name="join_seller_title[]" class="form-control"
                                            placeholder="{{ 'título aquí...' }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="join_seller_button_name"
                                            class="form-label">{{ 'Nombre del botón' }}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 15 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <input id="join_seller_button_name" type="text" maxlength="15"
                                            name="join_seller_button_name[]" class="form-control"
                                            placeholder="{{ 'nombre del botón aquí...' }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="join_seller_sub_title"
                                            class="form-label">{{ 'Subtítulo' }}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 60 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <textarea id="join_seller_sub_title" value="join_seller_sub_title" maxlength="60" name="join_seller_sub_title[]"
                                            class="form-control" placeholder="{{ 'subtítulo aquí...' }}" rows="2"></textarea>
                                    </div>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif
                            <div class="btn--container justify-content-end mt-20">
                                <button type="reset" class="btn btn--reset mb-2">{{ 'Reiniciar' }}</button>
                                <button type="submit" class="btn btn--primary mb-2">{{ 'Ahorrar' }}</button>
                            </div>
                        </div>
                    </div>
                </form>


                <form action="{{ route('admin.business-settings.flutter-landing-page-settings', 'join-delivery') }}" method="post" id="join_DM_flutter_status_form">
                    @csrf
                    <input type="hidden" name="join_DM_flutter_status" value="{{ $join_DM_flutter_status ?? 0 }}">
                </form>

                <form action="{{ route('admin.business-settings.flutter-landing-page-settings', 'join-delivery') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mt-4 card">
                        <div class="card-header">
                            <h5 class="card-title mb-3 mt-3">
                                <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span>
                                <span>{{ 'Únete como Sección Repartidor' }}</span>
                            </h5>

                            <label class="toggle-switch justify-content-end  rounded">
                                <input type="checkbox" data-id="join_DM_flutter_status" data-type="status"
                                    data-image-on="{{ asset('assets/admin/img/modal/home-delivery-on.png') }}"
                                    data-image-off="{{ asset('assets/admin/img/modal/home-delivery-off.png') }}"
                                    data-title-on="<strong>{{ '¿Quieres habilitar la sección Unirse como repartidor?' }}</strong>"
                                    data-title-off="<strong>{{ '¿Quieres desactivar la sección Unirse como repartidor?' }}</strong>"
                                    data-text-on="<p>{{ 'Si habilita esto, la sección Unirse como repartidor será visible.' }}</p>"
                                    data-text-off="<p>{{ 'Si desactiva esto, la sección Unirse como repartidor no será visible.' }}</p>"
                                    class="status toggle-switch-input dynamic-checkbox" value="1"
                                    name="" id="join_DM_flutter_status"
                                    {{ $join_DM_flutter_status == 1 ? 'checked' : '' }}>
                                <span class="toggle-switch-label text">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                        <div class="card-body {{ $join_DM_flutter_status != 1 ? 'd-none' : '' }}">

                            @if ($language)
                                <div class="row g-3 lang_form default-form">
                                    <div class="col-sm-6">
                                        <label for="join_delivery_man_title" class="form-label">{{ 'Título' }}
                                            ({{ 'por defecto' }})<span class="form-label-secondary"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <input type="text" id="join_delivery_man_title" maxlength="20"
                                            name="join_delivery_man_title[]" class="form-control"
                                            value="{{ $join_delivery_man_title?->getRawOriginal('value') ?? '' }}"
                                            placeholder="{{ 'título aquí...' }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="join_delivery_man_button_name"
                                            class="form-label">{{ 'Nombre del botón' }}
                                            ({{ 'por defecto' }})<span class="form-label-secondary"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 15 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <input id="join_delivery_man_button_name" type="text" maxlength="15"
                                            name="join_delivery_man_button_name[]" class="form-control"
                                            value="{{ $join_delivery_man_button_name?->getRawOriginal('value') ?? '' }}"
                                            placeholder="{{ 'nombre del botón aquí...' }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="join_delivery_man_sub_title"
                                            class="form-label">{{ 'Subtítulo' }}
                                            ({{ 'por defecto' }})<span class="form-label-secondary"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 60 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <textarea id="join_delivery_man_sub_title" placeholder="{{ 'subtítulo aquí...' }}"
                                            maxlength="60" name="join_delivery_man_sub_title[]" class="form-control" rows="2">{{ $join_delivery_man_sub_title?->getRawOriginal('value') ?? '' }}</textarea>
                                    </div>


                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                @foreach (json_decode($language) as $lang)
                                    <?php
                                    if (isset($join_delivery_man_title->translations) && count($join_delivery_man_title->translations)) {
                                        $join_delivery_man_title_translate = [];
                                        foreach ($join_delivery_man_title->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'join_delivery_man_title') {
                                                $join_delivery_man_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    if (isset($join_delivery_man_sub_title->translations) && count($join_delivery_man_sub_title->translations)) {
                                        $join_delivery_man_sub_title_translate = [];
                                        foreach ($join_delivery_man_sub_title->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'join_delivery_man_sub_title') {
                                                $join_delivery_man_sub_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    if (isset($join_delivery_man_button_name->translations) && count($join_delivery_man_button_name->translations)) {
                                        $join_delivery_man_button_name_translate = [];
                                        foreach ($join_delivery_man_button_name->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'join_delivery_man_button_name') {
                                                $join_delivery_man_button_name_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="row g-3 d-none lang_form" id="{{ $lang }}-form1">
                                        <div class="col-sm-6">
                                            <label for="join_delivery_man_title{{ $lang }}"
                                                class="form-label">{{ 'Título' }}
                                                ({{ strtoupper($lang) }})
                                                <span class="form-label-secondary" data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <input type="text" id="join_delivery_man_title{{ $lang }}"
                                                maxlength="20" name="join_delivery_man_title[]" class="form-control"
                                                value="{{ $join_delivery_man_title_translate[$lang]['value'] ?? '' }}"
                                                placeholder="{{ 'título aquí...' }}">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="join_delivery_man_button_name{{ $lang }}"
                                                class="form-label">{{ 'Nombre del botón' }}
                                                ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 15 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <input type="text" id="join_delivery_man_button_name{{ $lang }}"
                                                maxlength="15" name="join_delivery_man_button_name[]"
                                                class="form-control"
                                                value="{{ $join_delivery_man_button_name_translate[$lang]['value'] ?? '' }}"
                                                placeholder="{{ 'nombre del botón aquí...' }}">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="join_delivery_man_sub_title{{ $lang }}"
                                                class="form-label">{{ 'Subtítulo' }}
                                                ({{ strtoupper($lang) }})<span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Escribe el título dentro de 60 caracteres.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span></label>
                                            <textarea id="join_delivery_man_sub_title{{ $lang }}"
                                                placeholder="{{ 'subtítulo aquí...' }}" maxlength="60" name="join_delivery_man_sub_title[]"
                                                class="form-control" rows="2">{{ $join_delivery_man_sub_title_translate[$lang]['value'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                @endforeach
                            @else
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label for="join_delivery_man_title"
                                            class="form-label">{{ 'Título' }}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <input id="join_delivery_man_title" type="text" maxlength="20"
                                            name="join_delivery_man_title[]" class="form-control"
                                            placeholder="{{ 'título aquí...' }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="join_delivery_man_sub_title"
                                            class="form-label">{{ 'Subtítulo' }}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 60 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <input id="join_delivery_man_sub_title" type="text" maxlength="60"
                                            name="join_delivery_man_sub_title[]" class="form-control"
                                            placeholder="{{ 'subtítulo aquí...' }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="join_delivery_man_button_name"
                                            class="form-label">{{ 'Nombre del botón' }}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 15 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <input id="join_delivery_man_button_name" type="text" maxlength="15"
                                            name="join_delivery_man_button_name[]" class="form-control"
                                            placeholder="{{ 'nombre del botón aquí...' }}">
                                    </div>

                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif
                            <div class="btn--container justify-content-end mt-20">
                                <button type="reset" class="btn btn--reset mb-2">{{ 'Reiniciar' }}</button>
                                <button type="submit" class="btn btn--primary mb-2">{{ 'Ahorrar' }}</button>
                            </div>
                        </div>
                    </div>
                </form>


            </div>
        </div>

        <!-- How it Works -->
        @include('admin-views.business-settings.landing-page-settings.partial.how-it-work-flutter')
    </div>

@endsection
