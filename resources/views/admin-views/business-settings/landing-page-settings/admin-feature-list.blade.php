@extends('layouts.admin.app')

@section('title', 'página de inicio del administrador')

@section('content')
    <div class="content container-fluid">
        <div class="page-header pb-0">
            <div class="d-flex flex-wrap justify-content-between">
                <h1 class="page-header-title">
                    <span class="page-header-icon">
                        <img src="{{ asset('assets/admin/img/landing.png') }}" class="w--20" alt="">
                    </span>
                    <span>
                        {{ 'páginas de inicio de administración' }}
                    </span>
                </h1>
                <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center" type="button" data-toggle="modal"
                    data-target="#how-it-works">
                    <strong class="mr-2">{{ 'Cómo funciona el entorno' }}</strong>
                    <div>
                        <i class="tio-info-outined"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-4 mt-2">
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                @include('admin-views.business-settings.landing-page-settings.top-menu-links.admin-landing-page-links')
            </div>
        </div>
        @php($feature_title = \App\Models\DataSetting::withoutGlobalScope('translate')->where('type','admin_landing_page')->where('key', 'feature_title')->first())
        @php($feature_short_description = \App\Models\DataSetting::withoutGlobalScope('translate')->where('type','admin_landing_page')->where('key', 'feature_short_description')->first())
        @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
        @php($language = $language->value ?? null)

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
                <form action="{{ route('admin.business-settings.admin-landing-page-settings', 'feature-title') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <h5 class="card-title mb-3">
                        <span class="card-header-icon mr-2"><i class="tio-settings-outlined"></i></span>
                        <span>{{ 'Título de la característica y breve descripción' }}</span>
                    </h5>
                    <div class="card mb-3">
                        <div class="card-body">

                            @if ($language)
                                <div class="row g-3 lang_form default-form">
                                    <div class="col-sm-6">
                                        <label for="feature_title" class="form-label">{{ 'Título' }}
                                            ({{ 'por defecto' }})<span class="form-label-secondary"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span>
                                            <span class="form-label-secondary text-danger"
                                                  data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ 'Requerido.'}}"> *
                                                </span></label>
                                        <input id="feature_title" type="text" maxlength="80" name="feature_title[]"
                                            value="{{ $feature_title?->getRawOriginal('value') }}" class="form-control"
                                            placeholder="{{ 'Ej: características notables que puedes contar' }}" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="feature_short_description" class="form-label">{{ 'Breve descripción' }}
                                            ({{ 'por defecto' }})<span class="form-label-secondary"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 240 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span>
                                            <span class="form-label-secondary text-danger"
                                                  data-toggle="tooltip" data-placement="right"
                                                  data-original-title="{{ 'Requerido.'}}"> *
                                                </span></label>
                                        <input id="feature_short_description" type="text" maxlength="240" name="feature_short_description[]"
                                            value="{{ $feature_short_description?->getRawOriginal('value') }}" class="form-control"
                                            placeholder="{{ 'Ej: repleto de características excepcionales...' }}" required>
                                    </div>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                @foreach (json_decode($language) as $lang)
                                    <?php
                                    if (isset($feature_title->translations) && count($feature_title->translations)) {
                                        $feature_title_translate = [];
                                        foreach ($feature_title->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'feature_title') {
                                                $feature_title_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    if (isset($feature_short_description->translations) && count($feature_short_description->translations)) {
                                        $feature_short_description_translate = [];
                                        foreach ($feature_short_description->translations as $t) {
                                            if ($t->locale == $lang && $t->key == 'feature_short_description') {
                                                $feature_short_description_translate[$lang]['value'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="row g-3 d-none lang_form" id="{{ $lang }}-form">
                                        <div class="col-sm-6">
                                            <label for="feature_title{{ $lang }}" class="form-label">{{ 'Título' }}
                                                ({{ strtoupper($lang) }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                            </span></label>
                                        <input id="feature_title{{ $lang }}" type="text"  maxlength="80" name="feature_title[]"
                                                value="{{ $feature_title_translate[$lang]['value'] ?? '' }}"
                                                class="form-control"
                                                placeholder="{{ 'Ej: características notables que puedes contar' }}">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="feature_short_description{{ $lang }}" class="form-label">{{ 'Breve descripción' }}
                                                ({{ strtoupper($lang) }})<span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Escribe el título dentro de 240 caracteres.' }}">
                                                <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                            </span></label>
                                        <input type="text" id="feature_short_description{{ $lang }}" maxlength="240" name="feature_short_description[]"
                                                value="{{ $feature_short_description_translate[$lang]['value'] ?? '' }}"
                                                class="form-control"
                                                placeholder="{{ 'Ej: repleto de características excepcionales...' }}">
                                        </div>
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                                @endforeach
                            @else
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label for="feature_title" class="form-label">{{ 'Título' }}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <input id="feature_title" type="text" maxlength="80" name="feature_title[]" class="form-control"
                                            placeholder="{{ 'Ej: características notables que puedes contar' }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="feature_short_description" class="form-label">{{ 'Breve descripción' }}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Escribe el título dentro de 240 caracteres.' }}">
                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="">
                                            </span></label>
                                        <input id="feature_short_description" type="text" maxlength="240" name="feature_short_description[]"
                                            class="form-control"
                                            placeholder="{{ 'Ej: repleto de características excepcionales...' }}">
                                    </div>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif
                            <div class="btn--container justify-content-end mt-20">
                                <button type="reset" class="btn btn--reset mb-2">{{ 'Reiniciar' }}</button>
                                <button type="submit"
                                    class="btn btn--primary mb-2">{{ 'Ahorrar' }}</button>
                            </div>
                        </div>
                    </div>
                </form>
                <form action="{{ route('admin.business-settings.admin-landing-page-settings', 'feature-list') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-4">
                                @if ($language)
                                    <div class="col-md-6 lang_form default-form">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="title" class="form-label">{{ 'Título' }}
                                                    ({{ 'por defecto' }})<span
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
                                                <input id="title" type="text" maxlength="20" name="title[]" class="form-control"
                                                    placeholder="{{ 'Ej: compras' }}" required>
                                            </div>
                                            <div class="col-12">
                                                <label for="sub_title" class="form-label">{{ 'Subtítulo' }}
                                                    ({{ 'por defecto' }})<span
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
                                                <input id="sub_title" type="text" maxlength="80" name="sub_title[]"
                                                    class="form-control"
                                                    placeholder="{{ 'Ej: la mejor experiencia de compra' }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="lang[]" value="default">
                                    @foreach (json_decode($language) as $lang)
                                        <div class="col-md-6 d-none lang_form" id="{{ $lang }}-form1">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label for="title{{ $lang }}" class="form-label">{{ 'Título' }}
                                                        ({{ strtoupper($lang) }})<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 20 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input id="title{{ $lang }}" type="text" maxlength="20" name="title[]" class="form-control"
                                                        placeholder="{{ 'Ej: compras' }}">
                                                </div>
                                                <div class="col-12">
                                                    <label for="sub_title{{ $lang }}" class="form-label">{{ 'Subtítulo' }}
                                                        ({{ strtoupper($lang) }})<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 80 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input id="sub_title{{ $lang }}" type="text" maxlength="80" name="sub_title[]" class="form-control"
                                                        placeholder="{{ 'Ej: la mejor experiencia de compra' }}">
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    @endforeach
                                @else
                                    <div class="col-md-6">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="title" class="form-label">{{ 'Título' }}<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 50 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input id="title" type="text" maxlength="50" name="title[]" class="form-control"
                                                    placeholder="{{ 'Ej: compras' }}">
                                            </div>
                                            <div class="col-12">
                                                <label for="sub_title" class="form-label">{{ 'Subtítulo' }}<span
                                                        class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Escribe el título dentro de 50 caracteres.' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span></label>
                                                <input id="sub_title" type="text" maxlength="50" name="sub_title[]"
                                                    class="form-control"
                                                    placeholder="{{ 'Ej: la mejor experiencia de compra' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="lang[]" value="default">
                                @endif

                                <div class="col-md-6">
                                    <label class="form-label d-block mb-3">
                                        {{ 'Imagen' }} <span class="text--primary">{{'(tamaño: 1:1)'}}</span>
                                        <span class="form-label-secondary text-danger"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Requerido.'}}"> *
                                                </span>
                                        <div class="fs-12 opacity-70">
                                            {{ translate(IMAGE_FORMAT.' ' . 'Less Than 2MB') }}
                                        </div>
                                    </label>
                                    <label class="upload-img-3 m-0">
                                        <div class="img">
                                            <img src="{{ asset('assets/admin/img/aspect-1.png') }}"
                                            data-onerror-image="{{ asset('assets/admin/img/aspect-1.png') }}"
                                                alt="image" class="img__aspect-1 min-w-187px max-w-187px onerror-image">
                                        </div>
                                        <input class="upload-file__input single_file_input" accept="{{IMAGE_EXTENSION}}" type="file" name="image" hidden>
                                    </label>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end mt-20">
                                <button type="reset" class="btn btn--reset mb-2">{{ 'Reiniciar' }}</button>
                                <button type="submit" class="btn btn--primary mb-2">{{ 'Agregar' }}</button>
                            </div>
                        </div>
                    </div>
                </form>
                @php($features = \App\Models\AdminFeature::all())
                <div class="card">
                    <div class="card-header py-2">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">{{ 'Lista de características' }}

                            </h5>

                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Table -->
                        <div class="table-responsive datatable-custom">
                            <table id="columnSearchDatatable"
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                data-hs-datatables-options='{
                                    "order": [],
                                    "orderCellsTop": true,
                                    "paging":false

                                }'>
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">{{ 'SL' }}</th>
                                        <th class="border-0">{{ 'Título' }}</th>
                                        <th class="border-0">{{ 'Subtítulo' }}</th>
                                        <th class="border-0">{{ 'Imagen' }}</th>
                                        <th class="border-0">{{ 'Estado' }}</th>
                                        <th class="text-center border-0">{{ 'acción' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($features as $key => $feature)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>
                                                <div class="text--title">
                                                    {{ $feature->title }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="d-block font-size-sm text-body">
                                                    {{ $feature->sub_title }}

                                            </td>
                        <td>
                            <img  src="{{ $feature?->image_full_url ?? asset('assets/admin/img/upload-3.png') }}"

                                class="__size-105 onerror-image"  data-onerror-image="{{ asset('assets/admin/img/upload-3.png') }}" alt="image">
                        </td>
                        <td>
                            <label class="toggle-switch toggle-switch-sm">
                                <input type="checkbox"

                                    data-id="status-{{ $feature->id }}"
                                    data-type="toggle"
                                    data-image-on="{{ asset('assets/admin/img/modal/feature-list-on.png') }}"
                                    data-image-off="{{ asset('assets/admin/img/modal/feature-list-off.png') }}"
                                    data-title-on="{{ 'Al encender' }} <strong>{{ 'Sección de lista de funciones' }}"
                                    data-title-off="{{ 'Al apagar' }} <strong>{{ 'Sección de lista de funciones' }}"
                                    data-text-on="<p>{{ 'La lista de funciones está habilitada. Ahora puedes acceder a sus características y funcionalidades.' }}</p>"
                                    data-text-off="<p>{{ 'La lista de funciones estará deshabilitada. Puede habilitarlo en la configuración para acceder a sus características y funcionalidades.' }}</p>"
                                    class="status toggle-switch-input dynamic-checkbox"


                                    {{ $feature->status ? 'checked' : '' }}>
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                            <form
                                action="{{ route('admin.business-settings.feature-status', [$feature->id, $feature->status ? 0 : 1]) }}"
                                method="get" id="status-{{ $feature->id }}_form">
                            </form>
                        </td>

                        <td>
                            <div class="btn--container justify-content-center">
                                <a class="btn action-btn btn--primary btn-outline-primary"
                                    href="{{ route('admin.business-settings.feature-edit', [$feature['id']]) }}">
                                    <i class="tio-edit"></i>
                                </a>
                                <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"

                                   data-id="banner-{{ $feature['id'] }}"
                                   data-message="{{ '¿Quieres eliminar este banner?' }}"

                                    title="{{ 'eliminar banner' }}"><i
                                        class="tio-delete-outlined"></i>
                                </a>
                                <form action="{{ route('admin.business-settings.feature-delete', [$feature['id']]) }}"
                                    method="post" id="banner-{{ $feature['id'] }}">
                                    @csrf @method('delete')
                                </form>
                            </div>
                        </td>
                        </tr>
                        @endforeach
                        </tbody>
                        </table>

                    </div>
                    <!-- End Table -->
                </div>
                @if (count($features) === 0)
                    <div class="empty--data">
                        <img src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                        <h5>
                            {{ 'no se encontraron datos' }}
                        </h5>
                    </div>
                @endif
            </div>


        </div>
    </div>
    </div>
    @include('admin-views.business-settings.landing-page-settings.partial.how-it-work')
@endsection

