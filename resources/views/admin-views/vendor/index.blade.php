@extends('layouts.admin.app')

@section('title', 'agregar nombre de la tienda')



@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/store.png') }}" class="w--26" alt="">
                </span>
                <span>
                    {{ 'agregar nueva tienda' }}
                </span>
            </h1>
        </div>

        @php($language = \App\CentralLogics\Helpers::get_business_settings('language'))
        <!-- End Page Header -->
         <form class="validate-form global-ajax-form" action="{{ route('admin.store.store') }}" method="post" enctype="multipart/form-data">
            <div class="card mb-20">
                <div class="card-header">
                    <div class="mb-0">
                        <h3 class="mb-1">
                            {{ 'Información básica' }}
                        </h3>
                        <p class="mb-0 fs-12">
                            {{ 'Aquí configura toda la información comercial.' }}
                        </p>
                    </div>
                </div>
                <div class="card-body p-xxl-20 p-3">
                    <div class="row g-3">
                        <div class="col-lg-7">
                            <div class="shadow-sm p-xxl-20 p-sm-3 p-0">
                                <div class="bg-light2 rounded p-3 mb-20">
                                    @if ($language)
                                        <ul class="nav nav-tabs mb-4">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active" href="#"
                                                    id="default-link">{{ 'Por defecto' }}</a>
                                            </li>
                                            @foreach ($language as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link" href="#"
                                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if ($language)
                                        <div class="lang_form" id="default-form">
                                            <div class="form-group error-wrapper">
                                                <label class="input-label" for="default_name">{{ 'Nombre comercial' }}
                                                    ({{ 'Por defecto' }}) <span
                                                        class="form-label-secondary text-danger" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Requerido.' }}"> *
                                                    </span>
                                                </label>
                                                <input type="text" name="name[]" id="default_name" class="form-control"
                                                    placeholder="{{ 'Nombre comercial' }}" required>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            <div class="form-group mb-0 error-wrapper">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'Dirección comercial' }}
                                                    ({{ 'por defecto' }})<span
                                                        class="form-label-secondary text-danger" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Agregue la dirección oficial de su tienda para conocer la ubicación y la entrega precisas.' }}"> *
                                                        <i class="tio-info text-muted"></i>
                                                    </span>




                                                </label>
                                                <textarea required type="text" id="address" name="address[]" placeholder="{{ 'Dirección comercial' }}"
                                                    class="form-control min-h-90px"></textarea>

                                            </div>
                                        </div>
                                        @foreach ($language as $lang)
                                            <div class="d-none lang_form" id="{{ $lang }}-form">
                                                <div class="form-group error-wrapper">
                                                    <label class="input-label"
                                                        for="{{ $lang }}_name">{{ 'Nombre comercial' }}
                                                        ({{ strtoupper($lang) }})
                                                    </label>
                                                    <input type="text" name="name[]" id="{{ $lang }}_name"
                                                        class="form-control" placeholder="{{ 'Nombre comercial' }}">
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                <div class="form-group mb-0 error-wrapper">
                                                    <label class="input-label"
                                                        for="exampleFormControlInput1">{{ 'Dirección comercial' }}
                                                        ({{ strtoupper($lang) }}) <span
                                                        class="form-label-secondary text-danger" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Agregue la dirección oficial de su tienda para conocer la ubicación y la entrega precisas.' }}">
                                                        <i class="tio-info text-muted"></i>
                                                    </span></label>
                                                    <textarea type="text" name="address[]" placeholder="{{ 'Dirección comercial' }}"
                                                        class="form-control min-h-90px"></textarea>
                                                </div>
                                            </div>
                                        @endforeach

                                    @endif
                                </div>
                                <div class="form-group error-wrapper">
                                    <label class="input-label"
                                        for="choice_zones">{{ 'Zona de negocios' }}<span
                                            class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'seleccionar zona para mapa' }}"></span>
                                        <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Requerido.' }}"> *
                                        </span>
                                    </label>
                                    <select name="zone_id" id="choice_zones" required
                                        class="form-control js-select2-custom"
                                        data-placeholder="{{ 'seleccionar zona' }}">
                                        <option value="" selected disabled>
                                            {{ 'seleccionar zona' }}</option>
                                        @foreach (\App\Models\Zone::active()->get(['id', 'name']) as $zone)
                                            @if (isset(auth('admin')->user()->zone_id))
                                                @if (auth('admin')->user()->zone_id == $zone->id)
                                                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                                @endif
                                            @else
                                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group error-wrapper">
                                    <label class="input-label" for="tags">{{ 'etiquetas' }}</label>
                                    <select name="tags[]" id="tags" class="form-control js-select2-custom" multiple="multiple" data-placeholder="{{ 'seleccionar etiquetas' }}">
                                        @foreach (\App\Models\Tag::all() as $tag)
                                            <option value="{{ $tag->tag }}">{{ $tag->tag }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="position-relative">
                                    <label class="input-label"
                                        for="tax">{{ 'Tiempo de entrega estimado (tiempo mínimo y máximo)' }}
                                        <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'Requerido.' }}"> *
                                        </span></label>

                                    <div class="floating--date-inner time-delivery-vendor bg-white rounded border d-flex align-items-center">
                                        <div class="item error-wrapper w-100">
                                            <input id="minimum_delivery_time" type="number"
                                                name="minimum_delivery_time" class="form-control w-100 h--45px border-0 outline-0"
                                                placeholder="{{ 'Ex :' }} 30"
                                                pattern="^[0-9]{2}$" required
                                                value="{{ old('minimum_delivery_time') }}">
                                        </div>
                                        <div class="item error-wrapper border-left w-100">
                                            <input id="maximum_delivery_time" type="number"
                                                name="maximum_delivery_time" class="form-control w-100 h--45px border-0 outline-0"
                                                placeholder="{{ 'Ex :' }} 60"
                                                pattern="[0-9]{2}" required
                                                value="{{ old('maximum_delivery_time') }}">
                                        </div>
                                        <div class="item smaller">
                                            <select name="delivery_time_type" id="delivery_time_type"
                                                class="custom-select min-w-90 bg-light2 h--45px border-0 outline-0">
                                                <option value="min">{{ 'minutos' }}
                                                </option>
                                                <option value="hours">{{ 'horas' }}
                                                </option>
                                                <option value="days">{{ 'días' }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="bg-light2 rounded p-xxl-20 p-3">
                                <div class="mb-15">
                                    <h4 class="mb-1">
                                        {{ 'Establecer ubicación comercial en el mapa' }}
                                    </h4>
                                    <p class="mb-0 fs-12">
                                        {{ 'Marque la ubicación exacta de la empresa para ayudar a los clientes a encontrarla fácilmente.' }}
                                    </p>
                                </div>
                                <div class="map-for-vndor map_custom-controls position-relative">
                                    <input id="pac-input" class="controls rounded initial-8" title="{{'busca tu ubicación aquí'}}" type="text" placeholder="{{'buscar aquí'}}"/>
                                   <div id="map"></div>


                                    <div class="d-flex bg-white align-items-center gap-1 laglng-controller">
                                                <div id="latlng" class="d-flex">
                                                    <input type="text" id="latitude" name="latitude" class="border-0 p-0 m-0 text-center outline-0"
                                                placeholder="{{ 'Ex:' }} -94.22213"
                                                value="{{ old('latitude') }}" readonly>
                                                    <span class="text-gray1">|</span>
                                                    <input type="text" name="longitude" class="border-0 p-0 m-0 text-center outline-0"
                                                placeholder="{{ 'Ex:' }} 103.344322" id="longitude"
                                                value="{{ old('longitude') }}" readonly>
                                                </div>
                                    </div>
                                    <div class="d-flex bg-white align-items-center gap-1 laglng-controller mt-2">
                                        <div class="d-flex gap-3 px-3 py-2 border rounded">
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="toggle-switch toggle-switch-sm mb-0">
                                                    <input type="checkbox" name="allow_minutes" class="toggle-switch-input" checked>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                                <span class="fs-12">{{ 'Minutos' }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="toggle-switch toggle-switch-sm mb-0">
                                                    <input type="checkbox" name="allow_standard" class="toggle-switch-input" checked>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                                <span class="fs-12">{{ 'Normal' }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="toggle-switch toggle-switch-sm mb-0">
                                                    <input type="checkbox" name="allow_next_day" class="toggle-switch-input" checked>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                                <span class="fs-12">{{ 'día siguiente' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                   <div id="outOfZone" class="map-alert bg-dark d-flex align-items-center rounded-8 py-2 px-2 fs-12 text-white mb-2">
                                        <img class="" src="{{asset('assets/admin/img/icons/warning-cus.png')}}" alt="img"> {{ 'Por favor coloque el marcador dentro de las zonas disponibles.' }}
                                   </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-20">
                <div class="card-header">
                    <div class="mb-0">
                        <h3 class="mb-1">
                            {{ 'Ubicaciones de tiendas (varias ubicaciones)' }}
                        </h3>
                        <p class="mb-0 fs-12">
                            {{ 'Agregue varias ubicaciones físicas para esta tienda.' }}
                        </p>
                    </div>
                </div>
                <div class="card-body p-xxl-20 p-3">
                    <div class="table-responsive">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ 'DIRECCIÓN' }}</th>
                                    <th>{{ 'latitud' }}</th>
                                    <th>{{ 'longitud' }}</th>
                                    <th class="text-center">{{ 'Minutos' }}</th>
                                    <th class="text-center">{{ 'Normal' }}</th>
                                    <th class="text-center">{{ 'día siguiente' }}</th>
                                    <th class="text-center">{{ 'acción' }}</th>
                                </tr>
                            </thead>
                            <tbody id="location_table_body">
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn--primary" onclick="add_location_row()">
                            <i class="tio-add"></i> {{ 'Agregar nueva ubicación' }}
                        </button>
                    </div>
                </div>
            </div>
            <!-- Location Map Modal -->
            <div class="modal fade" id="locationMapModal" tabindex="-1" role="dialog" aria-labelledby="locationMapModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="locationMapModalLabel">{{ 'Elegir ubicación' }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <input id="location-pac-input" class="form-control" type="text" placeholder="{{'buscar aquí'}}"/>
                                </div>
                                <div class="col-12">
                                    <div id="location_map_canvas" style="height: 400px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ 'Cerca' }}</button>
                            <button type="button" class="btn btn-primary" onclick="confirm_location_selection()">{{ 'Confirmar ubicación' }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-20">
                <div class="card-header">
                    <div class="mb-0">
                        <h3 class="mb-1">
                            {{ 'Configuración general' }}
                        </h3>
                        <p class="mb-0 fs-12">
                            {{ 'Configure la configuración general de todos sus negocios' }}
                        </p>
                    </div>
                </div>
                <div class="card-body p-xxl-20 p-3">
                    <div class="shadow-sm p-xxl-20 p-sm-3 p-0 mb-20">
                        <div class="mb-15">
                            <h4 class="mb-1">
                                {{ 'Logotipo y portadas comerciales' }}
                            </h4>
                            <p class="mb-0 fs-12">
                                {{ 'Formato: Jpg, jpeg, png, gif, webp. Menos de 2 MB' }}
                            </p>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="bg-light2 rounded p-20">
                                    <div class="mb-15 text-center">
                                        <h4 class="mb-0">{{ 'Cobertura empresarial' }} <span class="text-danger">*</span></h4>
                                    </div>
                                    <div class="mx-auto text-center error-wrapper">
                                        <div class="upload-file_custom ratio-2-1 h-100px">
                                            <input type="file" name="cover_photo"
                                                    class="upload-file__input single_file_input"
                                                    accept=".webp, .jpg, .jpeg, .png, .gif" required>
                                            <label class="upload-file__wrapper w-100 h-100 m-0">
                                                <div class="upload-file-textbox text-center" style="">
                                                    <img width="22" class="svg"
                                                            src="{{asset('assets/admin/img/document-upload.svg')}}"
                                                            alt="img">
                                                    <h6
                                                        class="mt-1 color-656566 fw-medium fs-10 lh-base text-center">
                                                        <span class="theme-clr">{{ 'Agregar imagen' }}</span>
                                                        <br class="mb-1">
                                                         {{ 'Relación (2:1)' }}
                                                    </h6>
                                                </div>
                                                <img class="upload-file-img" loading="lazy" src=""
                                                        data-default-src="" alt="" style="display: none;">
                                            </label>
                                            <div class="overlay">
                                                <div
                                                    class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                    <button type="button"
                                                            class="btn btn-outline-info icon-btn view_btn">
                                                        <i class="tio-invisible"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-outline-info icon-btn edit_btn">
                                                        <i class="tio-edit"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light2 rounded p-20">
                                    <div class="mb-15 text-center">
                                        <h4 class="mb-0">{{ 'Logotipo de empresa' }} <span class="text-danger">*</span></h4>
                                    </div>
                                    <div class="mx-auto text-center error-wrapper">
                                        <div class="upload-file_custom ratio-1 h-100px">
                                            <input type="file" name="logo"
                                                    class="upload-file__input single_file_input"
                                                    accept=".webp, .jpg, .jpeg, .png, .gif" required>
                                            <label class="upload-file__wrapper w-100 h-100 m-0">
                                                <div class="upload-file-textbox text-center" style="">
                                                    <img width="22" class="svg"
                                                            src="{{asset('assets/admin/img/document-upload.svg')}}"
                                                            alt="img">
                                                    <h6
                                                        class="mt-1 color-656566 fw-medium fs-10 lh-base text-center">
                                                        <span class="theme-clr">{{ 'Agregar imagen' }}</span>
                                                        <br class="mb-1">
                                                       {{ 'Relación (1:1)' }}
                                                    </h6>
                                                </div>
                                                <img class="upload-file-img" loading="lazy" src=""
                                                        data-default-src="" alt="" style="display: none;">
                                            </label>
                                            <div class="overlay">
                                                <div
                                                    class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                    <button type="button"
                                                            class="btn btn-outline-info icon-btn view_btn">
                                                        <i class="tio-invisible"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-outline-info icon-btn edit_btn">
                                                        <i class="tio-edit"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="shadow-sm p-xxl-20 p-sm-3 p-0 mb-20">
                        <div class="mb-20">
                            <h4 class="mb-1">
                                {{ 'Información del propietario del negocio' }}
                            </h4>
                            <p class="mb-0 fs-12">
                                {{ 'Configura la información de tu negocio' }}
                            </p>
                        </div>
                        <div class="bg-light2 rounded p-xxl-20 p-3">
                            <div class="row g-3">
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0 error-wrapper">
                                        <label class="input-label" for="f_name">{{ 'nombre de pila' }}
                                            <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ 'Requerido.' }}"> *
                                            </span></label>
                                        <input type="text" name="f_name" class="form-control"
                                            placeholder="{{ 'nombre de pila' }}"
                                            value="{{ old('f_name') }}" required>

                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0 error-wrapper">
                                        <label class="input-label"
                                            for="l_name">{{ 'apellido' }}<span
                                                class="form-label-secondary text-danger" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ 'Requerido.' }}"> *
                                            </span></label>
                                        <input type="text" name="l_name" class="form-control"
                                            placeholder="{{ 'apellido' }}"
                                            value="{{ old('l_name') }}" required>

                                    </div>

                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0 error-wrapper">
                                        <label class="input-label" for="phone">{{ 'teléfono' }}<span
                                                class="form-label-secondary text-danger" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ 'Requerido.' }}"> *
                                            </span></label>
                                        <input type="tel" id="phone" name="phone" class="form-control"
                                            placeholder="{{ 'Ex:' }} 017********" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="shadow-sm p-xxl-20 p-sm-3 p-0 mb-20">
                        <div class="mb-20">
                            <h4 class="mb-1">
                                {{ 'Información de la cuenta' }}
                            </h4>
                            <p class="mb-0 fs-12">
                                {{ 'Configure las credenciales de su cuenta' }}
                            </p>
                        </div>
                        <div class="bg-light2 rounded p-xxl-20 p-3">
                            <div class="row g-3">
                                <div class="col-md-4 col-12">
                                    <div class="form-group mb-0 error-wrapper">
                                        <label class="input-label" for="email">{{ 'correo electrónico' }}<span
                                                class="form-label-secondary text-danger" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ 'Requerido.' }}"> *
                                            </span></label>
                                        <input type="email" name="email" class="form-control"
                                            placeholder="{{ 'Ex:' }} ex@example.com"
                                            value="{{ old('email') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="form-group error-wrapper mb-0">
                                        <label class="input-label"
                                            for="signupSrPassword">{{ 'Contraseña' }}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"><img
                                                    src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"></span>
                                            <span class="form-label-secondary text-danger" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ 'Requerido.' }}"> *
                                            </span></label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control"
                                                name="password" id="signupSrPassword"
                                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                                title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"
                                                placeholder="{{ translate('messages.password_length_placeholder', ['length' => '8+']) }}"
                                                aria-label="8+ characters required" required
                                                data-msg="Your password is invalid. Please try again."
                                                data-hs-toggle-password-options='{
                                            "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                            "defaultClass": "tio-hidden-outlined",
                                            "showClass": "tio-visible-outlined",
                                            "classChangeTarget": ".js-toggle-passowrd-show-icon-1"
                                            }'>
                                            <div class="js-toggle-password-target-1 input-group-append">
                                                <a class="input-group-text" href="javascript:">
                                                    <i class="js-toggle-passowrd-show-icon-1 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="form-group error-wrapper mb-0">
                                        <label class="input-label"
                                            for="signupSrConfirmPassword">{{ 'confirmar Contraseña' }}<span
                                                class="form-label-secondary text-danger" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ 'Requerido.' }}"> *
                                            </span></label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control"
                                                name="confirmPassword" id="signupSrConfirmPassword"
                                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                                title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"
                                                placeholder="{{ translate('messages.password_length_placeholder', ['length' => '8+']) }}"
                                                aria-label="8+ characters required" required
                                                data-msg="Password does not match the confirm password."
                                                data-hs-toggle-password-options='{
                                                    "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                                    "defaultClass": "tio-hidden-outlined",
                                                    "showClass": "tio-visible-outlined",
                                                    "classChangeTarget": ".js-toggle-passowrd-show-icon-2"
                                                    }'>
                                            <div class="js-toggle-password-target-2 input-group-append">
                                                <a class="input-group-text" href="javascript:">
                                                    <i class="js-toggle-passowrd-show-icon-2 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="shadow-sm p-xxl-20 p-sm-3 p-0">
                        <div class="mb-20">
                            <h4 class="mb-1">
                                {{ 'NIF empresarial' }}
                            </h4>
                            <p class="mb-0 fs-12">
                                {{ 'Configure su TIN comercial' }}
                            </p>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-8 col-xxl-9">
                                <div class="bg-light2 rounded p-xxl-20 p-3 h-100">
                                    <div class="form-group  error-wrapper">
                                        <label class="input-label mb-2 d-block title-clr fw-normal"
                                            for="exampleFormControlInput1">{{ 'Número de Identificación del Contribuyente (TIN)' }}
                                        </label>
                                        <input type="text" name="tin"
                                            placeholder="{{ 'Escriba su número de identificación del contribuyente (TIN)' }}"
                                            class="form-control">
                                    </div>
                                    <div class="form-group mb-0  error-wrapper">
                                        <label class="input-label mb-2 d-block title-clr fw-normal"
                                            for="exampleFormControlInput1">{{ 'Fecha de vencimiento' }} </label>
                                        <input type="date"  name="tin_expire_date" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-xxl-3">
                                <div class="bg-light2 rounded p-xxl-20 p-3 h-100 single-document-uploaderwrap">
                                    <div class="d-flex align-items-center gap-1 justify-content-center text-center mb-20">
                                        <div>
                                            <h4 class="mb-1 fz--14px">{{ 'Certificado TIN' }}</h4>
                                            <p class="fz-12px mb-0">
                                                {{ 'pdf, documento, jpg. Tamaño del archivo: máximo 2 MB' }}</p>
                                        </div>
                                        <!-- <div class="d-flex gap-3 align-items-center">
                                            <button type="button" id="doc_edit_btn"
                                                class="w-30px h-30 rounded d-flex align-items-center justify-content-center btn--primary btn px-3 icon-btn">
                                                <i class="tio-edit"></i>
                                            </button>
                                        </div> -->
                                    </div>
                                    <div class="form-group max-w-280 mx-auto error-wrapper position-relative">
                                        <button type="button" id="doc_edit_btn"
                                            class="w-30px h-30 rounded d-flex align-items-center justify-content-center btn--primary btn px-3 icon-btn position-absolute edit__icon-fortin ">
                                            <i class="tio-edit"></i>
                                            
                                        </button>
                                        
                                        <div id="file-assets"
                                            data-picture-icon="{{ asset('assets/admin/img/picture.svg') }}"
                                            data-document-icon="{{ asset('assets/admin/img/document.svg') }}"
                                            data-blank-thumbnail="{{ asset('assets/admin/img/picture.svg') }}">
                                        </div>
                                        <!-- Upload box -->
                                        <div class="d-flex justify-content-center mb-2" id="pdf-container">
                                            <div class="document-upload-wrapper" id="doc-upload-wrapper">
                                                <input type="file" name="tin_certificate_image"
                                                    class="document_input" accept=".doc, .pdf, .jpg, .png, .jpeg"
                                                    data-max-size="2mb">
                                                <div class="textbox">
                                                    <img width="40" height="40" class="svg"
                                                        src="{{ asset('assets/admin/img/doc-uploaded.png') }}"
                                                        alt="">
                                                    <p class="fs-12 mb-0 px-1 text-center">
                                                        {{ 'Seleccione un archivo o' }} <span
                                                            class="font-semibold">{{ 'Arrastrar y soltar' }}</span>
                                                        {{ 'aquí' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn--container justify-content-end mt-4">
                <button type="reset" id="reset_btn"
                    class="btn btn--reset">{{ 'reiniciar' }}</button>
                <button type="submit" id="submitButton"
                    class="btn btn--primary"><i class="tio-save"></i> {{ 'Guardar información' }}</button>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
    @php($default_location =  \App\CentralLogics\Helpers::get_business_settings('default_location') ?? '')

    <script>
        const getAllModules ="{{ route('restaurant.get-all-modules') }}";
         const getModuleType ="{{ route('restaurant.get-module-type') }}";
         const checkModuleTypeUrl ="{{ route('restaurant.check-module-type') }}";
        const estimatedPickupText =
        "{{ 'Tiempo estimado de recogida' }} <span class='text-danger'>*</span>";
        const approxDeliveryText =
        "{{ 'tiempo de entrega aproximado' }} <span class='text-danger'>*</span>";

        window.mapConfig = {
            mapApiKey: "{{ \App\CentralLogics\Helpers::get_business_settings('map_api_key') }}",
            defaultLocation: {!! json_encode($default_location) !!},
            oldLat: parseFloat("{{ old('latitude') }}"),
            oldLng: parseFloat("{{ old('longitude') }}"),
            oldZoneId: "{{ old('zone_id') }}",
            oldAddress: @json(old('address.0')),
            translations: {
                selectedLocation: "{{ 'Ubicación seleccionada' }}",
                clickMap: "{{ '¡Haga clic en el mapa dentro del área marcada en rojo para obtener Lat/Lng!' }}",
                selectZone: "{{ 'Seleccione zona en el menú desplegable' }}",
                geolocationError: "{{ 'Error: Su navegador no admite geolocalización.' }}",
                outOfZone: "{{ 'fuera de cobertura' }}",
            },
            urls: {
                zoneCoordinates: "{{ route('admin.zone.get-coordinates', ['id' => ':coordinatesZoneId']) }}",
                zoneGetZone: "{{ route('admin.zone.get-zone') }}",
            }
        };
    </script>


    <script src="{{ asset('assets/admin/js/file-preview/pdf.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/file-preview/pdf-worker.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/file-preview/add-multiple-document-upload.js') }}"></script>
    <script src="{{ asset('assets/admin/js/view-pages/map-functionality.js') }}"></script>

    <script src="{{ asset('assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ \App\CentralLogics\Helpers::get_business_settings('map_api_key') }}&libraries=drawing,places,marker,geometry&v=3.61&language={{ str_replace('_', '-', app()->getLocale()) }}&callback=initMap"
        async defer>
    </script>

    <script>
        "use strict";

        $(document).on('ready', function() {
            $('#tags').select2({
                tags: true,
                tokenSeparators: [',']
            });
            @if (isset(auth('admin')->user()->zone_id))
                $('#choice_zones').trigger('change');
            @endif
        });


        $('#reset_btn').click(function() {
             $('#choice_zones').val(null).trigger('change');
            zonePolygon.setMap(null);
            $('#latitude').val(null);
            $('#longitude').val(null);
        })

        let location_row_count = 0;
        function add_location_row() {
            let html = `
                <tr>
                    <td>
                        <input type="text" name="locations[${location_row_count}][address]" id="location_address_${location_row_count}" class="form-control" placeholder="{{ 'DIRECCIÓN' }}" required>
                    </td>
                    <td>
                        <input type="number" step="any" name="locations[${location_row_count}][latitude]" id="location_lat_${location_row_count}" class="form-control" placeholder="{{ 'latitud' }}" required>
                    </td>
                    <td>
                        <input type="number" step="any" name="locations[${location_row_count}][longitude]" id="location_lng_${location_row_count}" class="form-control" placeholder="{{ 'longitud' }}" required>
                    </td>
                    <td class="text-center">
                        <label class="toggle-switch toggle-switch-sm">
                            <input type="checkbox" name="locations[${location_row_count}][allow_minutes]" class="toggle-switch-input" checked>
                            <span class="toggle-switch-label">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </td>
                    <td class="text-center">
                        <label class="toggle-switch toggle-switch-sm">
                            <input type="checkbox" name="locations[${location_row_count}][allow_standard]" class="toggle-switch-input" checked>
                            <span class="toggle-switch-label">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </td>
                    <td class="text-center">
                        <label class="toggle-switch toggle-switch-sm">
                            <input type="checkbox" name="locations[${location_row_count}][allow_next_day]" class="toggle-switch-input" checked>
                            <span class="toggle-switch-label">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                        </label>
                    </td>
                    <td>
                        <div class="btn--container justify-content-center">
                            <button type="button" class="btn btn-outline-primary icon-btn" onclick="open_location_map(${location_row_count})">
                                <i class="tio-map"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger icon-btn" onclick="remove_location_row(this)">
                                <i class="tio-delete-outlined"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            $('#location_table_body').append(html);
            location_row_count++;
        }

        function remove_location_row(button) {
            $(button).closest('tr').remove();
        }

        let subLocationMap = null;
        let subLocationMarker = null;
        let subLocationCurrentIndex = null;
        let subLocationGeocoder = null;

        function open_location_map(index) {
            subLocationCurrentIndex = index;
            $('#locationMapModal').modal('show');
            
            setTimeout(function() {
                if (subLocationMap === null) {
                    initSubLocationMap();
                } else {
                    google.maps.event.trigger(subLocationMap, "resize");
                    resetSubLocationMarker();
                }
            }, 500);
        }

        function initSubLocationMap() {
            const { defaultLocation } = window.mapConfig;
            let center = {
                lat: Number(defaultLocation ? defaultLocation.lat : 23.757989),
                lng: Number(defaultLocation ? defaultLocation.lng : 90.360587)
            };

            subLocationMap = new google.maps.Map(document.getElementById("location_map_canvas"), {
                zoom: 13,
                center: center,
            });

            subLocationGeocoder = new google.maps.Geocoder();

            subLocationMarker = new google.maps.Marker({
                position: center,
                map: subLocationMap,
                draggable: true
            });

            const input = document.getElementById("location-pac-input");
            const searchBox = new google.maps.places.SearchBox(input);

            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();
                if (places.length == 0) return;
                
                const place = places[0];
                if (!place.geometry || !place.geometry.location) return;

                subLocationMarker.setPosition(place.geometry.location);
                subLocationMap.setCenter(place.geometry.location);
            });

            subLocationMap.addListener("click", (e) => {
                subLocationMarker.setPosition(e.latLng);
            });

            resetSubLocationMarker();
        }

        function resetSubLocationMarker() {
            let lat = $(`#location_lat_${subLocationCurrentIndex}`).val();
            let lng = $(`#location_lng_${subLocationCurrentIndex}`).val();

            if (lat && lng) {
                let pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
                subLocationMarker.setPosition(pos);
                subLocationMap.setCenter(pos);
            }
        }

        function confirm_location_selection() {
            let pos = subLocationMarker.getPosition();
            $(`#location_lat_${subLocationCurrentIndex}`).val(pos.lat());
            $(`#location_lng_${subLocationCurrentIndex}`).val(pos.lng());

            subLocationGeocoder.geocode({ location: pos }, function (results, status) {
                if (status === 'OK' && results[0]) {
                    $(`#location_address_${subLocationCurrentIndex}`).val(results[0].formatted_address);
                }
                $('#locationMapModal').modal('hide');
            });
        }
    </script>

@endpush
