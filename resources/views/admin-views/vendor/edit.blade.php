@extends('layouts.admin.app')

@section('title', 'Update restaurant info')
@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/edit.png') }}" class="w--26" alt="">
                </span>
                <span>{{ 'actualizar tienda' }}</span>
            </h1>
        </div>
        @php
            $delivery_time_start = preg_match('([0-9]+[\-][0-9]+\s[min|hours|days])', $store->delivery_time ?? '')
                ? explode('-', $store->delivery_time)[0]
                : 10;
            $delivery_time_end = preg_match('([0-9]+[\-][0-9]+\s[min|hours|days])', $store->delivery_time ?? '')
                ? explode(' ', explode('-', $store->delivery_time)[1])[0]
                : 30;
            $delivery_time_type = preg_match('([0-9]+[\-][0-9]+\s[min|hours|days])', $store->delivery_time ?? '')
                ? explode(' ', explode('-', $store->delivery_time)[1])[1]
                : 'min';
        @endphp
        @php($language = \App\CentralLogics\Helpers::get_business_settings('language'))

        <!-- End Page Header -->

        <form class="validate-form global-ajax-form" action="{{ route('admin.store.update', [$store['id']]) }}" enctype="multipart/form-data" method="post">
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
                                                    ({{ 'Por defecto' }}) <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="name[]" id="default_name" class="form-control"
                                                    placeholder="{{ 'nombre de la tienda' }}"
                                                    value="{{ $store->getRawOriginal('name') }}" required>


                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            <div class="form-group mb-0 error-wrapper">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'Dirección comercial' }}
                                                    ({{ 'por defecto' }})  <span class="text-danger">*</span>
                                                </label>
                                                <textarea required type="text" name="address[]" rows="1" placeholder="{{ 'Negocio' }}" required
                                                    class="form-control min-h-90px">{{ $store->getRawOriginal('address') }}</textarea>

                                            </div>
                                        </div>
                                        @foreach ($language as $lang)
                                            <?php
                                            if (count($store['translations'])) {
                                                $translate = [];
                                                foreach ($store['translations'] as $t) {
                                                    if ($t->locale == $lang && $t->key == 'name') {
                                                        $translate[$lang]['name'] = $t->value;
                                                    }
                                                    if ($t->locale == $lang && $t->key == 'address') {
                                                        $translate[$lang]['address'] = $t->value;
                                                    }
                                                }
                                            }
                                            ?>
                                            <div class="d-none lang_form" id="{{ $lang }}-form">
                                                <div class="form-group error-wrapper">
                                                    <label class="input-label"
                                                        for="{{ $lang }}_name">{{ 'Nombre comercial' }}
                                                        ({{ strtoupper($lang) }})
                                                    </label>
                                                    <input type="text" name="name[]" id="{{ $lang }}_name"
                                                        class="form-control" value="{{ $translate[$lang]['name'] ?? '' }}"
                                                        placeholder="{{ 'nombre de la tienda' }}">

                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                <div class="form-group mb-0 error-wrapper">
                                                    <label class="input-label"
                                                        for="exampleFormControlInput1">{{ 'Dirección comercial' }}
                                                        ({{ strtoupper($lang) }})</label>
                                                    <textarea type="text" name="address[]" rows="1"  placeholder="{{ 'Negocio' }}"
                                                        class="form-control min-h-90px">{{ $translate[$lang]['address'] ?? '' }}</textarea>

                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div id="default-form">
                                            <div class="form-group error-wrapper">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'nombre' }}
                                                    ({{ 'por defecto' }})</label>
                                                <input type="text" name="name[]" class="form-control"
                                                    placeholder="{{ 'nombre de la tienda' }}" required>

                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            <div class="form-group mb-0 error-wrapper">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'DIRECCIÓN' }}
                                                </label>
                                                <textarea type="text" name="address[]" rows="1"  placeholder="{{ 'Negocio' }}"
                                                    class="form-control "></textarea>

                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group error-wrapper">
                                    <label class="input-label" for="choice_zones">{{ 'Zona de negocios' }}
                                        <span class="form-label-secondary" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ 'seleccionar zona para mapa' }}">
                                           <!-- <i class="tio-info text-muted"></i> -->
                                        </span> <span class="text-danger">*</span>
                                    </label>
                                    <select name="zone_id" id="choice_zones"
                                        data-placeholder="{{ 'seleccionar zona' }}"
                                        class="form-control js-select2-custom get_zone_data">
                                        @foreach (\App\Models\Zone::active()->get(['id', 'name']) as $zone)
                                            @if (isset(auth('admin')->user()->zone_id))
                                                @if (auth('admin')->user()->zone_id == $zone->id)
                                                    <option value="{{ $zone->id }}"
                                                        {{ $store->zone_id == $zone->id ? 'selected' : '' }}>
                                                        {{ $zone->name }}</option>
                                                @endif
                                            @else
                                                <option value="{{ $zone->id }}"
                                                    {{ $store->zone_id == $zone->id ? 'selected' : '' }}>
                                                    {{ $zone->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>

                                </div>
                                <div class="form-group error-wrapper">
                                    <label class="input-label" for="tags">{{ 'etiquetas' }}</label>
                                    <select name="tags[]" id="tags" class="form-control js-select2-custom" multiple="multiple" data-placeholder="{{ 'seleccionar etiquetas' }}">
                                        @foreach (\App\Models\Tag::all() as $tag)
                                            <option value="{{ $tag->tag }}" {{ $store->tags->contains('tag', $tag->tag) ? 'selected' : '' }}>{{ $tag->tag }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="position-relative">
                                    <label class="input-label"
                                        for="tax">{{ 'Tiempo de entrega estimado (tiempo mínimo y máximo)' }}  <span class="text-danger">*</span>
                                    </label>

                                    <div class="floating--date-inner time-delivery-vendor bg-white rounded border d-flex align-items-center">
                                        <div class="item error-wrapper w-100">
                                            <input id="minimum_delivery_time" type="number"
                                                name="minimum_delivery_time"
                                                value="{{ $delivery_time_start }}"
                                                class="form-control  w-100 h--45px border-0 outline-0"
                                                placeholder="{{ 'Ex :' }} 30"
                                                pattern="^[0-9]{2}$" required
                                                value="{{ old('minimum_delivery_time') }}">

                                        </div>
                                        <div class="item error-wrapper border-left w-100">
                                            <input id="maximum_delivery_time" type="number"
                                                name="maximum_delivery_time"
                                                value="{{ $delivery_time_end }}"
                                                class="form-control w-100 h--45px border-0 outline-0"
                                                placeholder="{{ 'Ex :' }} 60"
                                                pattern="[0-9]{2}" required
                                                value="{{ old('maximum_delivery_time') }}">

                                        </div>
                                        <div class="item smaller">
                                            <select name="delivery_time_type" id="delivery_time_type"
                                                class="custom-select min-w-90 bg-light2 h--45px border-0 outline-0">
                                                <option value="min"
                                                    {{ $delivery_time_type == 'min' ? 'selected' : '' }}>
                                                    {{ 'minutos' }}</option>
                                                <option value="hours"
                                                    {{ $delivery_time_type == 'hours' ? 'selected' : '' }}>
                                                    {{ 'horas' }}</option>
                                                <option value="days"
                                                    {{ $delivery_time_type == 'days' ? 'selected' : '' }}>
                                                    {{ 'días' }}</option>
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
                                                value="{{$store->latitude }}" readonly>
                                                    <span class="text-gray1">|</span>
                                                    <input type="text" name="longitude" class="border-0 p-0 m-0 text-center outline-0"
                                                placeholder="{{ 'Ex:' }} 103.344322" id="longitude"
                                                value="{{ $store->longitude }}" readonly>
                                                </div>
                                    </div>
                                    <div class="d-flex bg-white align-items-center gap-1 laglng-controller mt-2">
                                        <div class="d-flex gap-3 px-3 py-2 border rounded">
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="toggle-switch toggle-switch-sm mb-0">
                                                    <input type="checkbox" name="allow_minutes" class="toggle-switch-input" {{ $store->allow_minutes ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                                <span class="fs-12">{{ 'Minutos' }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="toggle-switch toggle-switch-sm mb-0">
                                                    <input type="checkbox" name="allow_standard" class="toggle-switch-input" {{ $store->allow_standard ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                                <span class="fs-12">{{ 'Normal' }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="toggle-switch toggle-switch-sm mb-0">
                                                    <input type="checkbox" name="allow_next_day" class="toggle-switch-input" {{ $store->allow_next_day ? 'checked' : '' }}>
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
                            {{ 'Detalles del restaurante' }}
                        </h3>
                        <p class="mb-0 fs-12">
                            {{ 'Información extra para la página del restaurante.' }}
                        </p>
                    </div>
                </div>
                <div class="card-body p-xxl-20 p-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="input-label" for="average_ticket">{{ 'Costo promedio del boleto' }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                <input type="number" name="average_ticket" id="average_ticket" step="0.01" min="0" class="form-control" placeholder="100" value="{{ $store->average_ticket }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="input-label" for="cuisine_names">{{ 'Nombres de cocina' }}</label>
                                <input type="text" name="cuisine_names" id="cuisine_names" class="form-control" placeholder="{{ 'Ej: japonés, asiático' }}" value="{{ is_array($store->cuisine_names) ? implode(', ', $store->cuisine_names) : $store->cuisine_names }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                             <div class="form-group mb-0">
                                <label class="input-label" for="serves_alcohol">{{ 'Sirve alcohol' }}</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="serves_alcohol" class="custom-control-input" id="serves_alcohol" {{ $store->serves_alcohol ? 'checked' : '' }} value="1">
                                    <label class="custom-control-label" for="serves_alcohol"></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                             <div class="form-group mb-0">
                                <label class="input-label" for="tootli_lana">{{ 'Acepta tootli lana' }}</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="tootli_lana" class="custom-control-input" id="tootli_lana" {{ (is_null($store->tootli_lana) || $store->tootli_lana) ? 'checked' : '' }} value="1">
                                    <label class="custom-control-label" for="tootli_lana"></label>
                                </div>
                            </div>
                        </div>
                        @if($store->module->module_type == 'food')
                        <div class="col-md-4">
                             <div class="form-group mb-0">
                                <label class="input-label" for="exclude_from_sabores">{{ 'Excluir de Sabores de la Ciudad' }}</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="exclude_from_sabores" class="custom-control-input" id="exclude_from_sabores" {{ $store->exclude_from_sabores ? 'checked' : '' }} value="1">
                                    <label class="custom-control-label" for="exclude_from_sabores"></label>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="col-12">
                             <div class="form-group mb-0">
                                <label class="input-label">{{ 'Imágenes de infraestructura' }} ({{ 'Relación 1:1' }})</label>
                                <div class="row" id="infrastructure_images"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-20">
                <div class="card-header">
                    <div class="mb-0">
                        <h3 class="mb-1">
                            {{ 'Configuración de TootliClick' }}
                        </h3>
                        <p class="mb-0 fs-12">
                            {{ 'Configura tu menú de pedidos directos (WhatsApp).' }}
                        </p>
                    </div>
                </div>
                <div class="card-body p-xxl-20 p-3">
                    @php($tc_settings = $store->tootliclick_settings ?? [])
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="input-label">{{ 'Habilitar métodos de pago' }}</label>
                            <div class="d-flex flex-wrap gap-4 border rounded p-3">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="toggle-switch toggle-switch-sm mb-0">
                                        <input type="checkbox" name="tc_payment_cash" class="toggle-switch-input" {{ isset($tc_settings['payment_methods']['cash']) && $tc_settings['payment_methods']['cash'] ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                    <span class="fs-14">{{ 'Efectivo' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <label class="toggle-switch toggle-switch-sm mb-0">
                                        <input type="checkbox" name="tc_payment_card" class="toggle-switch-input" {{ isset($tc_settings['payment_methods']['card']) && $tc_settings['payment_methods']['card'] ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                    <span class="fs-14">{{ 'Tarjeta' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <label class="toggle-switch toggle-switch-sm mb-0">
                                        <input type="checkbox" name="tc_payment_transfer" class="toggle-switch-input" {{ isset($tc_settings['payment_methods']['transfer']) && $tc_settings['payment_methods']['transfer'] ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                    <span class="fs-14">{{ 'transferencia' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-3" id="transfer_details_div" style="display: {{ isset($tc_settings['payment_methods']['transfer']) && $tc_settings['payment_methods']['transfer'] ? 'block' : 'none' }};">
                            <label class="input-label" for="tc_transfer_details">{{ 'Detalles para Transferencia' }}</label>
                            <textarea name="tc_transfer_details" id="tc_transfer_details" class="form-control" rows="3" placeholder="{{ 'Ej: Banco, Clabe, Beneficiario...' }}">{{ $tc_settings['transfer_details'] ?? '' }}</textarea>
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
                                @foreach($store->locations as $key => $location)
                                    <tr>
                                        <td>
                                            <input type="text" name="locations[{{ $key }}][address]" id="location_address_{{ $key }}" class="form-control" value="{{ $location->address }}" required>
                                            <input type="hidden" name="locations[{{ $key }}][id]" value="{{ $location->id }}">
                                        </td>
                                        <td>
                                            <input type="number" step="any" name="locations[{{ $key }}][latitude]" id="location_lat_{{ $key }}" class="form-control" value="{{ $location->latitude }}" required>
                                        </td>
                                        <td>
                                            <input type="number" step="any" name="locations[{{ $key }}][longitude]" id="location_lng_{{ $key }}" class="form-control" value="{{ $location->longitude }}" required>
                                        </td>
                                        <td class="text-center">
                                            <label class="toggle-switch toggle-switch-sm">
                                                <input type="checkbox" name="locations[{{ $key }}][allow_minutes]" class="toggle-switch-input" {{ $location->allow_minutes ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            <label class="toggle-switch toggle-switch-sm">
                                                <input type="checkbox" name="locations[{{ $key }}][allow_standard]" class="toggle-switch-input" {{ $location->allow_standard ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            <label class="toggle-switch toggle-switch-sm">
                                                <input type="checkbox" name="locations[{{ $key }}][allow_next_day]" class="toggle-switch-input" {{ $location->allow_next_day ? 'checked' : '' }}>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <button type="button" class="btn btn-outline-success icon-btn" onclick="save_location(this)" title="{{ 'Guardar ubicación' }}">
                                                    <i class="tio-save"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary icon-btn" onclick="open_location_map({{ $key }})">
                                                    <i class="tio-map"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger icon-btn" onclick="remove_location_row(this)">
                                                    <i class="tio-delete-outlined"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
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
                            {{ 'Aquí puede administrar la configuración de tiempo para que coincida con los criterios de su negocio.' }}
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
                                                    accept=".webp, .jpg, .jpeg, .png, .gif" {{ $store->cover_photo ? '' : 'required' }}>
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
                                                <img class="upload-file-img" loading="lazy" src="{{ $store->cover_photo_full_url ?? asset('assets/admin/img/upload-img.png') }}"
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
                                                    accept=".webp, .jpg, .jpeg, .png, .gif" {{ $store->logo ? '' : 'required' }}>
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
                                                <img class="upload-file-img" loading="lazy" src="{{ $store->logo_full_url ?? asset('assets/admin/img/upload-img.png') }}"
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
                    
                    <!-- Sabores Event Settings -->
                    <div class="shadow-sm p-xxl-20 p-sm-3 p-0 mb-20">
                        <div class="mb-20">
                            <h4 class="mb-1 text-primary">
                                <i class="tio-calendar"></i> {{ 'Configuración del evento Sabores' }}
                            </h4>
                            <p class="mb-0 fs-12">
                                {{ 'Organiza un evento para este restaurante en Sabores de la Ciudad.' }}
                            </p>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="event_title">{{ 'Título del evento' }}</label>
                                    <input type="text" name="event_title" class="form-control" id="event_title" 
                                           value="{{ old('event_title', $store->event_title) }}"
                                           placeholder="{{ 'por ejemplo, Taco Fest, Noche de Jazz en Vivo' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="input-label" for="event_date">{{ 'Fecha del evento' }}</label>
                                    <input type="date" name="event_date" class="form-control" id="event_date" 
                                           value="{{ old('event_date', $store->event_date ? \Carbon\Carbon::parse($store->event_date)->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="input-label mb-2">{{ 'Etiqueta adhesiva del mapa del evento (se recomienda fondo PNG transparente)' }}</label>
                                <div class="custom-file">
                                    <input type="file" name="event_image" id="eventFile" class="custom-file-input"
                                           accept=".jpg, .png, .jpeg, .gif, .bmp, .webp|image/*">
                                    <label class="custom-file-label" for="eventFile">{{'Elija archivo'}}</label>
                                </div>
                                <div class="mt-2">
                                    <img id="viewerEvent" src="{{ $store->event_image ? $store->event_image_full_url : asset('assets/admin/img/400x400/img2.jpg') }}" alt="Event Sticker Image" class="img-thumbnail" style="max-height: 150px; object-fit: cover;">
                                </div>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label class="input-label mb-2">{{ 'Foto de fondo de tarjeta de evento' }}</label>
                                <div class="custom-file">
                                    <input type="file" name="event_card_image" id="eventCardFile" class="custom-file-input"
                                           accept=".jpg, .png, .jpeg, .gif, .bmp, .webp|image/*">
                                    <label class="custom-file-label" for="eventCardFile">{{'Elija archivo'}}</label>
                                </div>
                                <div class="mt-2">
                                    <img id="viewerEventCard" src="{{ $store->event_card_image ? $store->event_card_image_full_url : asset('assets/admin/img/400x400/img2.jpg') }}" alt="Event Card Image" class="img-thumbnail" style="max-height: 150px; object-fit: cover;">
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
                                {{ 'Configure la zona horaria y el formato de su empresa desde aquí' }}
                            </p>
                        </div>
                        <div class="bg-light2 rounded p-xxl-20 p-3">
                            <div class="row g-3">
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0 error-wrapper">
                                        <label class="input-label"
                                            for="f_name">{{ 'nombre de pila' }}  <span class="text-danger">*</span></label>
                                        <input type="text" name="f_name" class="form-control"
                                            placeholder="{{ 'nombre de pila' }}"
                                            value="{{ $store->vendor->f_name }}" required>

                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0 error-wrapper">
                                        <label class="input-label"
                                            for="l_name">{{ 'apellido' }}  <span class="text-danger">*</span></label>
                                        <input type="text" name="l_name" class="form-control"
                                            placeholder="{{ 'apellido' }}"
                                            value="{{ $store->vendor->l_name }}" required>

                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6 error-wrapper">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="phone">{{ 'teléfono' }}  <span class="text-danger">*</span></label>
                                        <input type="text" id="phone" name="phone" class="form-control"
                                            placeholder="{{ 'Ex:' }} 017********"
                                            value="{{ $store->vendor->phone }}" required>

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
                                {{ 'Configure la zona horaria y el formato de su empresa desde aquí' }}
                            </p>
                        </div>
                        <div class="bg-light2 rounded p-xxl-20 p-3">
                            <div class="row g-3">
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0 error-wrapper">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ 'correo electrónico' }}  <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control"
                                            placeholder="{{ 'Ex:' }} ex@example.com"
                                            value="{{ $store->email }}" required>

                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="js-form-message form-group mb-0 error-wrapper">
                                        <label class="input-label"
                                            for="signupSrPassword">{{ 'Contraseña' }}<span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"></span></label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control"
                                                name="password" id="signupSrPassword"
                                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                                title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"
                                                placeholder="{{ translate('messages.password_length_placeholder', ['length' => '8+']) }}"
                                                aria-label="8+ characters required"
                                                data-msg="Your password is invalid. Please try again."
                                                data-hs-toggle-password-options='{
                                            "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                            "defaultClass": "tio-hidden-outlined",
                                            "showClass": "tio-visible-outlined",
                                            "classChangeTarget": ".js-toggle-passowrd-show-icon-1"
                                            }'>
                                            <div class="js-toggle-password-target-1 input-group-append">
                                                <a class="input-group-text" href="javascript:;">
                                                    <i class="js-toggle-passowrd-show-icon-1 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="js-form-message form-group mb-0 error-wrapper">
                                        <label class="input-label"
                                            for="signupSrConfirmPassword">{{ 'confirmar Contraseña' }}</label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control"
                                                name="confirmPassword" id="signupSrConfirmPassword"
                                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                                title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"
                                                placeholder="{{ translate('messages.password_length_placeholder', ['length' => '8+']) }}"
                                                aria-label="8+ characters required"
                                                data-msg="Password does not match the confirm password."
                                                data-hs-toggle-password-options='{
                                                    "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                                    "defaultClass": "tio-hidden-outlined",
                                                    "showClass": "tio-visible-outlined",
                                                    "classChangeTarget": ".js-toggle-passowrd-show-icon-2"
                                                    }'>
                                            <div class="js-toggle-password-target-2 input-group-append">
                                                <a class="input-group-text" href="javascript:;">
                                                    <i class="js-toggle-passowrd-show-icon-2 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="shadow-sm p-xxl-20 p-sm-3 p-0">
                            <div class="mb-20">
                                <h3 class="mb-1">{{ 'NIF empresarial' }}</h3>
                                {{-- <p class="fz-12px mb-0">{{'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'}}</p> --}}
                            </div>
                            <div class="row g-3">
                                <div class="col-md-8 col-xxl-9">
                                    <div class="bg-light2 rounded p-20 h-100">
                                        <div class="form-group error-wrapper">
                                            <label class="input-label mb-2 d-block title-clr fw-normal"
                                                for="exampleFormControlInput1">{{ 'Número de Identificación del Contribuyente (TIN)' }}
                                            </label>
                                            <input type="text" name="tin"
                                                placeholder="{{ 'Escriba su número de identificación del contribuyente (TIN)' }}"
                                                class="form-control" value="{{ $store->tin }}">
                                        </div>
                                        <div class="form-group mb-0 error-wrapper">
                                            <label class="input-label mb-2 d-block title-clr fw-normal"
                                                for="exampleFormControlInput1">{{ 'Fecha de vencimiento' }} </label>
                                            <input type="date" name="tin_expire_date" class="form-control"
                                                value="{{ $store->tin_expire_date }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-xxl-3">
                                    <div class="bg-light2 rounded p-20 h-100 single-document-uploaderwrap">
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
                                        <div class="error-wrapper max-w-280 mx-auto position-relative">
                                            <button type="button" id="doc_edit_btn"
                                                class="w-30px h-30 rounded d-flex align-items-center justify-content-center btn--primary btn px-3 icon-btn position-absolute edit__icon-fortin">
                                                <i class="tio-edit"></i>
                                            </button>
                                            <div id="file-assets"
                                                data-picture-icon="{{ asset('assets/admin/img/picture.svg') }}"
                                                data-document-icon="{{ asset('assets/admin/img/document.svg') }}"
                                                data-blank-thumbnail="{{ asset('assets/admin/img/picture.svg') }}">
                                            </div>
                                            <div class="d-flex justify-content-center" id="pdf-container">
                                                <div class="document-upload-wrapper d-none" id="doc-upload-wrapper">
                                                    <input type="file" name="tin_certificate_image"
                                                        class="document_input" accept=".doc, .pdf, .jpg, .png, .jpeg">
                                                    <div class="textbox">
                                                        <img width="40" height="40" class="svg"
                                                            src="{{ asset('assets/admin/img/doc-uploaded.png') }}"
                                                            alt="">
                                                        <p class="fs-12 mb-0">
                                                            {{ 'Seleccione un archivo o' }} <span
                                                                class="font-semibold">{{ 'Arrastrar y soltar' }}</span>
                                                            {{ 'aquí' }}</p>
                                                    </div>
                                                </div>
                                                <div class="pdf-single" data-file-name="${file.name}"
                                                    data-file-url="{{ $store->tin_certificate_image_full_url ?? asset('assets/admin/img/upload-cloud.png') }}">
                                                    <div class="pdf-frame">
                                                        @php($imgPath = $store->tin_certificate_image_full_url ?? asset('assets/admin/img/upload-cloud.png'))
                                                        @if (Str::endsWith($imgPath, ['.pdf', '.doc', '.docx']))
                                                            @php($imgPath = asset('assets/admin/img/document.svg'))
                                                        @endif
                                                        <img class="pdf-thumbnail-alt" src="{{ $imgPath }}"
                                                            alt="File Thumbnail">
                                                    </div>
                                                    <div class="overlay">
                                                        <div class="pdf-info">
                                                            @if (Str::endsWith($imgPath, ['.pdf', '.doc', '.docx']))
                                                                <img src="{{ asset('assets/admin/img/document.svg') }}"
                                                                    width="34" alt="File Type Logo">
                                                            @else
                                                                <img src="{{ asset('assets/admin/img/picture.svg') }}"
                                                                    width="34" alt="File Type Logo">
                                                            @endif
                                                            <div class="file-name-wrapper">
                                                                <span
                                                                    class="file-name js-filename-truncate">{{ $store->tin_certificate_image }}</span>
                                                                <span
                                                                    class="opacity-50">{{ 'Haga clic para ver el archivo' }}</span>
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
                    </div>
                    @if (request()->pending == 1)
                        <input type="hidden" name="approve_vendor" value="1">
                    @endif
                </div>
            </div>

             <div class="btn--container justify-content-end mt-4">
                <button type="reset" id="reset_btn" class="btn btn--reset min-w-120px">{{ 'reiniciar' }}</button>
                <button type="submit" id="submitButton" class="btn btn--primary min-w-120px"><i class="tio-save"></i> {{ request()->pending == 1 ? 'Actualizar y aprobar' : 'Guardar información' }}</button>
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
            oldLat: parseFloat("{{ $store->latitude }}"),
            oldLng: parseFloat("{{ $store->longitude }}"),
            oldZoneId: "{{ $store->zone_id }}",
            oldAddress: @json($store->address),
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
    <script src="{{ asset('assets/admin/js/file-preview/edit-multiple-document-upload.js') }}"></script>
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

            $("#infrastructure_images").spartanMultiImagePicker({
                fieldName: 'infrastructure_images[]',
                maxCount: 10,
                rowHeight: '120px',
                groupClassName: 'col-6 col-sm-4 col-md-3 col-lg-2',
                maxFileSize: '',
                placeholderImage: {
                    image: "{{ asset('assets/admin/img/400x400/img2.jpg') }}",
                    width: '100%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function(index, file) {

                },
                onRenderedPreview: function(index) {

                },
                onRemoveRow: function(index) {

                },
                onExtensionErr: function(index, file) {
                    toastr.error('{{ 'Por favor ingrese solo archivos tipo png o jpg' }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function(index, file) {
                    toastr.error('{{ 'tamaño de archivo demasiado grande' }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        function removeInfrastructureImage(index, key) {
            $('#infrastructure_image_' + index).remove();
            let removedImageKeys = $('#removedImageKeys').val();
            if (removedImageKeys === '') {
                removedImageKeys = key;
            } else {
                removedImageKeys += ',' + key;
            }
            $('#removedImageKeys').val(removedImageKeys);
        }


        $('#reset_btn').click(function() {
            $('#choice_zones').val(null).trigger('change');
            $('#latitude').val(null);
            $('#longitude').val(null);
        })

        $('input[name="tc_payment_transfer"]').change(function() {
            if($(this).is(':checked')) {
                $('#transfer_details_div').show();
            } else {
                $('#transfer_details_div').hide();
            }
        });

        function save_location(btn) {
        let row = $(btn).closest('tr');
        let id = row.find('input[name*="[id]"]').val();
        let address = row.find('input[name*="[address]"]').val();
        let latitude = row.find('input[name*="[latitude]"]').val();
        let longitude = row.find('input[name*="[longitude]"]').val();
        let allow_minutes = row.find('input[name*="[allow_minutes]"]').is(':checked') ? 1 : 0;
        let allow_standard = row.find('input[name*="[allow_standard]"]').is(':checked') ? 1 : 0;
        let allow_next_day = row.find('input[name*="[allow_next_day]"]').is(':checked') ? 1 : 0;

        if (!address || !latitude || !longitude) {
            toastr.error('{{ 'llenar todos los campos requeridos' }}');
            return;
        }

        let url = id ? '{{ route("admin.store.location.update", ["id" => ":id"]) }}'.replace(':id', id) : '{{ route("admin.store.location.store") }}';
        let formData = {
            _token: '{{ csrf_token() }}',
            store_id: '{{ $store->id }}',
            address: address,
            latitude: latitude,
            longitude: longitude,
            allow_minutes: allow_minutes,
            allow_standard: allow_standard,
            allow_next_day: allow_next_day
        };

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            beforeSend: function() {
                $(btn).attr('disabled', true);
            },
            success: function(data) {
                if (data.id) {
                    row.find('input[name*="[id]"]').val(data.id);
                }
                toastr.success(data.message);
            },
            error: function(data) {
                if (data.responseJSON && data.responseJSON.errors) {
                    data.responseJSON.errors.forEach(err => toastr.error(err.message));
                } else {
                    toastr.error(data.responseJSON.message || '{{ 'error' }}');
                }
            },
            complete: function() {
                $(btn).attr('disabled', false);
            }
        });
    }

    function remove_location_row(btn) {
        let row = $(btn).closest('tr');
        let id = row.find('input[name*="[id]"]').val();

        Swal.fire({
            title: '{{'¿está seguro?'}}',
            text: '{{'quieres eliminar esta ubicación'}}',
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#FC6A57',
            cancelButtonText: '{{'No'}}',
            confirmButtonText: '{{'Sí'}}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                if (id) {
                    $.ajax({
                        url: '{{ route("admin.store.location.delete", ["id" => ":id"]) }}'.replace(':id', id),
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            row.remove();
                            toastr.success(data.message);
                        },
                        error: function(data) {
                            toastr.error(data.responseJSON.message || '{{ 'error' }}');
                        }
                    });
                } else {
                    row.remove();
                }
            }
        });
    }

    function add_location_row() {
        let key = $('#location_table_body tr').length;
        let html = `
            <tr>
                <td>
                    <input type="text" name="locations[${key}][address]" id="location_address_${key}" class="form-control" placeholder="{{ 'DIRECCIÓN' }}" required>
                    <input type="hidden" name="locations[${key}][id]" value="">
                </td>
                <td>
                    <input type="number" step="any" name="locations[${key}][latitude]" id="location_lat_${key}" class="form-control" placeholder="{{ 'Latitud' }}" required>
                </td>
                <td>
                    <input type="number" step="any" name="locations[${key}][longitude]" id="location_lng_${key}" class="form-control" placeholder="{{ 'Longitud' }}" required>
                </td>
                <td class="text-center">
                    <label class="toggle-switch toggle-switch-sm">
                        <input type="checkbox" name="locations[${key}][allow_minutes]" class="toggle-switch-input" checked>
                        <span class="toggle-switch-label">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="toggle-switch toggle-switch-sm">
                        <input type="checkbox" name="locations[${key}][allow_standard]" class="toggle-switch-input" checked>
                        <span class="toggle-switch-label">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </td>
                <td class="text-center">
                    <label class="toggle-switch toggle-switch-sm">
                        <input type="checkbox" name="locations[${key}][allow_next_day]" class="toggle-switch-input" checked>
                        <span class="toggle-switch-label">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </td>
                <td>
                    <div class="btn--container justify-content-center">
                        <button type="button" class="btn btn-outline-success icon-btn" onclick="save_location(this)" title="{{ 'Guardar ubicación' }}">
                            <i class="tio-save"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary icon-btn" onclick="open_location_map(${key})">
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

        function readEventURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#viewerEvent').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#eventFile").change(function () {
            readEventURL(this);
        });

        function readEventCardURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#viewerEventCard').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#eventCardFile").change(function () {
            readEventCardURL(this);
        });
    </script>
@endpush
