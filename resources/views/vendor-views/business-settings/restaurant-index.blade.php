@extends('layouts.vendor.app')

@section('title', 'Configuración')



@section('content')
    <div class="content container-fluid config-inline-remove-class">
        <!-- Page Heading -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/config.png') }}" class="w--30" alt="">
                </span>
                <span>
                    {{ 'configuración de la tienda' }}
                </span>
            </h1>
        </div>
        <!-- Page Heading -->
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="d-flex flex-row justify-content-between align-items-center">
                    <h4 class="card-title align-items-center d-flex">
                        <img src="{{ asset('assets/admin/img/store.png') }}" class="w--20 mr-1" alt="">
                        <span>{{ 'título de la tienda temporalmente cerrada' }}</span>
                    </h4>
                    <label class="switch toggle-switch-lg m-0" for="restaurant-open-status">
                        <input type="checkbox" id="restaurant-open-status"
                            class="toggle-switch-input restaurant-open-status" {{ $store->active ? '' : 'checked' }}>
                        <span class="toggle-switch-label">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </div>
            </div>
        </div>


        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">
                    <span class="card-header-icon">
                        <i class="tio-settings-outlined"></i>
                    </span>
                    <span>
                        {{ 'configuración de la tienda' }}
                    </span>
                </h5>
            </div>
            <form action="" method="get">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                            <div class="">
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                    for="schedule_order">
                                    <span class="pr-2">{{ 'orden programada' }}<span
                                            class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Cuando está habilitado, el propietario de la tienda puede recibir pedidos programados de los clientes.' }}"><img
                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                alt="{{ 'sugerencia de orden programada' }}"></span></span>
                                    <input type="checkbox" class="toggle-switch-input redirect-url "
                                        data-url="{{ route('vendor.business-settings.toggle-settings', [$store->id, $store->schedule_order ? 0 : 1, 'schedule_order']) }}"
                                        id="schedule_order" {{ $store->schedule_order ? 'checked' : '' }}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                            <div class="">
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                    for="delivery">
                                    <span class="pr-2">{{ 'entrega' }}<span
                                            class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Cuando está habilitado, los clientes pueden realizar pedidos de entrega a domicilio desde esta tienda.' }}"><img
                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                alt="{{ 'sugerencia de entrega a domicilio' }}"></span></span>
                                    <input type="checkbox" name="delivery" class="toggle-switch-input redirect-url "
                                        data-url="{{ route('vendor.business-settings.toggle-settings', [$store->id, $store->delivery ? 0 : 1, 'delivery']) }}"
                                        id="delivery" {{ $store->delivery ? 'checked' : '' }}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                            <div class="">
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                    for="take_away">
                                    <span class="pr-2 text-capitalize">{{ 'llevar' }}<span
                                            class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Cuando está habilitado, los clientes pueden realizar pedidos para llevar en esta tienda.' }}"><img
                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                alt="{{ 'quitar pista' }}"></span></span>
                                    <input type="checkbox" class="toggle-switch-input redirect-url "
                                        data-url="{{ route('vendor.business-settings.toggle-settings', [$store->id, $store->take_away ? 0 : 1, 'take_away']) }}"
                                        id="take_away" {{ $store->take_away ? 'checked' : '' }}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        @if ($store->module->module_type == 'pharmacy')
                            @php($prescription_order_status = \App\Models\BusinessSetting::where('key', 'prescription_order_status')->first())
                            @php($prescription_order_status = $prescription_order_status ? $prescription_order_status->value : 0)
                            @if ($prescription_order_status)
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                                    <div class="">
                                        <label
                                            class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                            for="prescription_order">
                                            <span
                                                class="pr-2 text-capitalize">{{ 'orden de prescripción' }}:</span>
                                            <input type="checkbox" class="toggle-switch-input redirect-url"
                                                data-url="{{ route('vendor.business-settings.toggle-settings', [$store->id, $store->prescription_order ? 0 : 1, 'prescription_order']) }}"
                                                id="prescription_order" {{ $store->prescription_order ? 'checked' : '' }}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endif
                        @if ($store->sub_self_delivery == 1)
                            <div class="col-lg-4 col-sm-6">
                                <div class="  m-0">
                                    <label
                                        class="toggle-switch toggle-switch-sm d-flex justify-content-between border rounded px-3 form-control"
                                        for="free_delivery">
                                        <span class="pr-2">
                                            {{ 'entrega gratuita' }}
                                            <span data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Si esta opción está activada, los clientes obtendrán envío gratuito.' }}"
                                                class="input-label-secondary"><img
                                                    src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="i"></span>
                                        </span>
                                        <input type="checkbox" name="free_delivery"
                                            class="toggle-switch-input redirect-url"
                                            data-url="{{ route('vendor.business-settings.toggle-settings', [$store->id, $store->free_delivery ? 0 : 1, 'free_delivery']) }}"
                                            id="free_delivery" {{ $store->free_delivery ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @endif
                        @if ($toggle_veg_non_veg && config('module.' . $store->module->module_type)['veg_non_veg'])
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                                <div class="">
                                    <label
                                        class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                        for="veg">
                                        <span class="pr-2 text-capitalize">{{ 'verduras' }}</span>
                                        <input type="checkbox" class="toggle-switch-input redirect-url"
                                            data-url="{{ route('vendor.business-settings.toggle-settings', [$store->id, $store->veg ? 0 : 1, 'veg']) }}"
                                            id="veg" {{ $store->veg ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                                <div class="">
                                    <label
                                        class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                        for="non_veg">
                                        <span class="pr-2 text-capitalize">{{ 'no vegetariano' }}</span>
                                        <input type="checkbox" class="toggle-switch-input redirect-url"
                                            data-url="{{ route('vendor.business-settings.toggle-settings', [$store->id, $store->non_veg ? 0 : 1, 'non_veg']) }}"
                                            id="non_veg" {{ $store->non_veg ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @endif
                        @if (config('module.' . $store->module->module_type)['cutlery'])
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                                <div class="">
                                    <label
                                        class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                        for="cutlery">
                                        <span class="pr-2 text-capitalize">{{ 'cuchillería' }}</span>
                                        <input type="checkbox" class="toggle-switch-input redirect-url"
                                            data-url="{{ route('vendor.business-settings.toggle-settings', [$store->id, $store->cutlery ? 0 : 1, 'cutlery']) }}"
                                            id="cutlery" {{ $store->cutlery ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @endif
                        @if (config('module.' . $store->module->module_type)['halal'])
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12">
                                <div class="">
                                    <label
                                        class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                        for="halal_tag_status">
                                        <span class="pr-2 text-capitalize">{{ 'estado de la etiqueta halal' }}

                                            <span data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Si está habilitado, los clientes pueden ver la etiqueta halal en el producto.' }}"
                                                class="input-label-secondary"><img
                                                    src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="i"></span>

                                        </span>
                                        <input type="checkbox" class="toggle-switch-input redirect-url"
                                            data-url="{{ route('vendor.business-settings.toggle-settings', [$store->id, $store->storeConfig?->halal_tag_status ? 0 : 1, 'halal_tag_status']) }}"
                                            id="halal_tag_status"
                                            {{ $store->storeConfig?->halal_tag_status ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">
                    <span class="card-header-icon">
                        <i class="tio-settings-outlined"></i>
                    </span>
                    <span>
                        {{ 'Almacenar configuración básica' }}
                    </span>
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('vendor.business-settings.update-setup', [$store['id']]) }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row align-items-end g-2">

                        <div class=" col-md-4">
                            <label class="input-label text-capitalize"
                                for="minimum_order">{{ 'cantidad mínima de pedido' }}<span
                                    class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ 'Especifique el monto mínimo de pedido requerido para los clientes al realizar pedidos en esta tienda.' }}"><img
                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                        alt="{{ 'pista de autoentrega' }}"></span></label>
                            <input type="number" id="minimum_order" name="minimum_order" step="0.01" min="0"
                                max="999999999" class="form-control" placeholder="100"
                                value="{{ $store->minimum_order > 0 ? $store->minimum_order : '' }}">
                        </div>
                        @if (config('module.' . $store->module->module_type)['order_place_to_schedule_interval'])
                            <div class=" col-md-4">
                                <label class="input-label text-capitalize"
                                    for="order_place_to_schedule_interval">{{ 'tiempo mínimo de procesamiento' }}<span
                                        class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ 'advertencia de tiempo mínimo de procesamiento' }}"><img
                                            src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                            alt="{{ 'advertencia de tiempo mínimo de procesamiento' }}"></span></label>
                                <input type="text" id="order_place_to_schedule_interval"
                                    name="order_place_to_schedule_interval" class="form-control"
                                    value="{{ $store->order_place_to_schedule_interval }}">
                            </div>
                        @endif
                        <div class=" col-md-4">
                            <label class="input-label text-capitalize"
                                for="minimum_delivery_time">{{ 'tiempo de entrega aproximado' }}<span
                                    class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ 'Establecer el tiempo total para entregar los productos.' }}"><img
                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                        alt="{{ 'Establecer el tiempo total para entregar los productos.' }}"></span></label>
                            <div class="input-group">
                                <input type="number" id="minimum_delivery_time" name="minimum_delivery_time"
                                    class="form-control" placeholder="Min: 10"
                                    value="{{ explode('-', $store->delivery_time)[0] }}"
                                    title="{{ 'tiempo mínimo de entrega' }}">
                                <input type="number" name="maximum_delivery_time" class="form-control"
                                    placeholder="Max: 20"
                                    value="{{ explode(' ', explode('-', $store->delivery_time)[1])[0] }}"
                                    title="{{ 'tiempo máximo de entrega' }}">
                                <select name="delivery_time_type" class="form-control text-capitalize" required>
                                    <option value="min"
                                        {{ explode(' ', explode('-', $store->delivery_time)[1])[1] == 'min' ? 'selected' : '' }}>
                                        {{ 'minutos' }}</option>
                                    <option value="hours"
                                        {{ explode(' ', explode('-', $store->delivery_time)[1])[1] == 'hours' ? 'selected' : '' }}>
                                        {{ 'horas' }}</option>
                                    <option value="days"
                                        {{ explode(' ', explode('-', $store->delivery_time)[1])[1] == 'days' ? 'selected' : '' }}>
                                        {{ 'días' }}</option>
                                </select>
                            </div>
                        </div>

                        @if ($store->sub_self_delivery)
                            <div class="col-sm-4 col-12">
                                <div class=" ">
                                    <label class="input-label text-capitalize"
                                        for="minimum_shipping_charge">{{ 'cargo mínimo de envío' }}
                                        ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                    </label>
                                    <input type="number" id="minimum_shipping_charge" min="0" max="99999999.99"
                                        step="0.01" name="minimum_delivery_charge" class="form-control shipping_input"
                                        value="{{ $store?->minimum_shipping_charge ?? '' }}">
                                </div>
                            </div>

                            <div class="col-sm-4 col-12">
                                <div class="">
                                    <label class="input-label text-capitalize"
                                        for="per_km_delivery_charge">{{ 'costo de entrega por km' }}
                                        ({{ \App\CentralLogics\Helpers::currency_symbol() }})</label>
                                    <input type="number" id="per_km_delivery_charge" name="per_km_delivery_charge"
                                        step="0.01" min="0" max="999999999" class="form-control"
                                        placeholder="100" value="{{ $store->per_km_shipping_charge ?? '0' }}">
                                </div>
                            </div>
                            <div class="col-sm-4 col-12">
                                <div class="">
                                    <label class="input-label text-capitalize"
                                        for="maximum_shipping_charge">{{ 'cargo máximo de entrega' }}
                                        ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Agregará un límite al cargo total de envío.' }}"
                                            class="input-label-secondary"><img
                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                alt="{{ 'cargo máximo de entrega' }}"></span>
                                    </label>
                                    <input type="number" id="maximum_shipping_charge" name="maximum_shipping_charge"
                                        step="0.01" min="0" max="999999999" class="form-control"
                                        placeholder="10000" value="{{ $store->maximum_shipping_charge ?? '' }}">
                                </div>
                            </div>
                        @endif

                        @if ($store->module->module_type != 'food')
                            <div class="col-sm-4 col-12">
                                <div class="">
                                    <label class="input-label text-capitalize"
                                        for="minimum_stock_for_warning">{{ 'Stock mínimo para aviso' }}
                                        <span data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Cuando el stock de un producto alcance su valor mínimo que hayas fijado, recibirás un aviso para actualizar el stock. Además, estos productos aparecerán en la lista de existencias bajas del administrador.' }}"
                                            class="input-label-secondary"><img
                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                alt="{{ 'Stock mínimo para aviso' }}"></span>
                                    </label>
                                    <input type="number" id="minimum_stock_for_warning" name="minimum_stock_for_warning"
                                        min="0" max="999999999" class="form-control"
                                        placeholder="{{ 'Ej: 5' }}"
                                        value="{{ $store?->storeConfig?->minimum_stock_for_warning ?? '' }}">
                                </div>
                            </div>
                        @endif

                        <div class="col-sm-{{ $store->module->module_type != 'food' ? '4' : '6' }} col-12">
                            <div class="">
                                <label class="d-flex justify-content-between switch toggle-switch-sm text-dark"
                                    for="gst_status">
                                    <span>{{ 'IVA' }} <span class="form-label-secondary"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Si GST está habilitado, el número de GST se mostrará en la factura.' }}"><img
                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                alt="{{ 'estado gst' }}"></span></span>
                                    <input type="checkbox" class="toggle-switch-input" name="gst_status" id="gst_status"
                                        value="1" {{ $store->gst_status ? 'checked' : '' }}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <input type="text" id="gst" name="gst" class="form-control"
                                    value="{{ $store->gst_code }}" {{ isset($store->gst_status) ? '' : 'readonly' }}>
                            </div>
                        </div>

                        @php($extra_packaging_data = \App\Models\BusinessSetting::where('key', 'extra_packaging_data')->first()?->value ?? '')
                        @php($extra_packaging_data = json_decode($extra_packaging_data, true))
                        @if (!empty($extra_packaging_data) && $extra_packaging_data[$store->module->module_type] == '1')
                            <div class="col-sm-{{ $store->module->module_type != 'food' ? '4' : '6' }}">
                                <div class="">
                                    <label class="d-flex justify-content-between switch toggle-switch-sm text-dark"
                                        for="extra_packaging_status">
                                        <span>{{ 'importe del cargo de embalaje adicional' }} <span
                                                class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Al habilitar el estado, el cliente tendrá la opción de elegir un cargo de embalaje adicional al realizar el pedido. para oferta de paquete adicional' }}"><img
                                                    src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="{{ 'Al habilitar el estado, el cliente tendrá la opción de elegir un cargo de embalaje adicional al realizar el pedido. para oferta de paquete adicional' }}"></span></span>
                                        <input type="checkbox" data-id="extra_packaging_status" data-type="status"
                                            data-image-on="{{ asset('assets/admin/img/modal/schedule-on.png') }}"
                                            data-image-off="{{ asset('assets/admin/img/modal/schedule-off.png') }}"
                                            data-title-on="{{ '¿Quieres habilitar un estado de embalaje adicional para este restaurante?' }}"
                                            data-title-off="{{ '¿Quieres desactivar el estado del embalaje adicional para este restaurante?' }}"
                                            data-text-on="<p>{{ 'Si está habilitado, los clientes deben pagar un cargo de embalaje adicional al realizar el pedido.' }}"
                                            data-text-off="<p>{{ 'Si está deshabilitado, los clientes no tienen que pagar un cargo adicional por embalaje en el pedido.' }}</p>"
                                            class="toggle-switch-input dynamic-checkbox-toggle"
                                            name="extra_packaging_status" value="1" id="extra_packaging_status"
                                            {{ $store->storeConfig?->extra_packaging_status == 1 ? 'checked' : '' }}>

                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                    <input type="number" id="extra_packaging_amount" name="extra_packaging_amount"
                                        step="0.01" min="0" max="9999999999" class="form-control"
                                        placeholder="100"
                                        {{ $store->storeConfig?->extra_packaging_status == 1 ? 'required' : 'readonly' }}
                                        value="{{ $store->storeConfig?->extra_packaging_amount }}">
                                </div>
                            </div>
                        @endif

                        {{-- ── Programa de Envío Gratis (Modelo Híbrido) ─────────────────────── --}}
                        <div class="col-12 mt-3">
                            <div class="border border-primary rounded p-3" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                                <h6 class="font-weight-bold mb-3 text-primary">
                                    <i class="tio-shopping-cart-add mr-1"></i>
                                    {{ 'Programa de Envío Gratis' }}
                                    <span class="badge badge-soft-primary ml-2">Nuevo</span>
                                </h6>
                                <p class="text-muted small mb-3">
                                    {{ 'Al activar esta opción, los clientes que superen el monto mínimo recibirán envío gratis. El costo se divide entre tu tienda y Tootli.' }}
                                </p>
                                <div class="row g-2 align-items-end">
                                    <div class="col-sm-4">
                                        <label class="d-flex justify-content-between switch toggle-switch-sm text-dark mb-2"
                                            for="free_shipping_enabled">
                                            <span>{{ 'Activar envío gratis con umbral' }}</span>
                                            <input type="checkbox" class="toggle-switch-input"
                                                name="free_shipping_enabled" id="free_shipping_enabled"
                                                value="1" {{ $store->free_shipping_enabled ? 'checked' : '' }}
                                                onchange="document.getElementById('free_shipping_fields').style.display = this.checked ? 'flex' : 'none'">
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="col-12">
                                        <div id="free_shipping_fields" class="row g-2" style="display: {{ $store->free_shipping_enabled ? 'flex' : 'none' }}">
                                            <div class="col-sm-4">
                                                <label class="input-label" for="free_shipping_threshold">
                                                    {{ 'Monto mínimo para envío gratis' }}
                                                    ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                    <span data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'El cliente debe gastar al menos este monto para obtener envío gratis. Recomendado: $399' }}"
                                                        class="input-label-secondary"><img
                                                            src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="i"></span>
                                                </label>
                                                <input type="number" id="free_shipping_threshold"
                                                    name="free_shipping_threshold" step="1" min="0"
                                                    max="999999" class="form-control"
                                                    placeholder="399"
                                                    value="{{ $store->free_shipping_threshold ?? 399 }}">
                                            </div>
                                            <div class="col-sm-4">
                                                <label class="input-label" for="store_shipping_contribution">
                                                    {{ 'Tu aporte al envio gratis' }}
                                                    ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                    <span data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Cuánto aportas tú cuando se aplica el envío gratis. El resto lo absorbe Tootli. Recomendado: $20' }}"
                                                        class="input-label-secondary"><img
                                                            src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="i"></span>
                                                </label>
                                                <input type="number" id="store_shipping_contribution"
                                                    name="store_shipping_contribution" step="1" min="0"
                                                    max="999" class="form-control"
                                                    placeholder="20"
                                                    value="{{ $store->store_shipping_contribution ?? 0 }}">
                                            </div>
                                            <div class="col-sm-4 d-flex align-items-end">
                                                <div class="alert alert-soft-info w-100 mb-0 py-2 px-3 small">
                                                    💡 <strong>Ejemplo:</strong> Cliente gasta ${{ $store->free_shipping_threshold ?? 399 }}+
                                                    → Envío gratis.<br>
                                                    Tu aporte: ${{ $store->store_shipping_contribution ?? 20 }} |
                                                    Tootli: ${{ max(0, 40 - ($store->store_shipping_contribution ?? 20)) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- ──────────────────────────────────────────────────────────────────── --}}

                        <div class="col-12">
                            <div class="btn--container mt-3 justify-content-end">
                                <button type="reset" class="btn btn--reset">{{ 'reiniciar' }}</button>
                                <button type="submit"
                                    class="btn btn--primary">{{ 'actualizar' }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">
                    <span class="card-header-icon">
                        <img class="w--22" src="{{ asset('assets/admin/img/store.png') }}" alt="">
                    </span>
                    <span class="p-md-1"> {{ 'almacenar metadatos' }}</span>
                </h5>
            </div>
            @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
            @php($language = $language->value ?? null)
            @php($defaultLang = 'en')
            <div class="card-body">
                <form action="{{ route('vendor.business-settings.update-meta-data', [$store['id']]) }}" method="post"
                    enctype="multipart/form-data" class="col-12">
                    @csrf
                    <div class="row g-2">
                        <div class="col-lg-6">
                            <div class="card shadow--card-2">
                                <div class="card-body">
                                    @if ($language)
                                        <ul class="nav nav-tabs mb-4">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active" href="#"
                                                    id="default-link">{{ 'Por defecto' }}</a>
                                            </li>
                                            @foreach (json_decode($language) as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link" href="#"
                                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if ($language)
                                        <div class="lang_form" id="default-form">
                                            <div class=" ">
                                                <label class="input-label"
                                                    for="default_title">{{ 'metatítulo' }}
                                                    ({{ 'Por defecto' }})
                                                    <span class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Este título aparece en las pestañas del navegador, en los resultados de búsqueda y en las vistas previas de enlaces. Utilice un título breve, claro y centrado en palabras clave (recomendado: entre 50 y 60 caracteres).' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span>
                                                </label>
                                                <input type="text" name="meta_title[]" id="default_title"
                                                    class="form-control" maxlength="60"
                                                    placeholder="{{ 'metatítulo' }}"
                                                    value="{{ $store->getRawOriginal('meta_title') }}">
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            <div class="mt-2">
                                                <label class="input-label"
                                                    for="meta_description">{{ 'meta descripción' }}
                                                    ({{ 'por defecto' }})
                                                    <span class="form-label-secondary" data-toggle="tooltip"
                                                        data-placement="right"
                                                        data-original-title="{{ 'Un breve resumen que aparece debajo del título de su página en los resultados de búsqueda. Manténgalo atractivo y relevante (recomendado: 120 a 160 caracteres).' }}">
                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="">
                                                    </span>
                                                </label>
                                                <textarea type="text" maxlength="160" id="meta_description" name="meta_description[]"
                                                    placeholder="{{ 'meta descripción' }}" class="form-control min-h-90px ckeditor">{{ $store->getRawOriginal('meta_description') }}</textarea>
                                            </div>
                                        </div>
                                        @foreach (json_decode($language) as $lang)
                                            <?php
                                            if (count($store['translations'])) {
                                                $translate = [];
                                                foreach ($store['translations'] as $t) {
                                                    if ($t->locale == $lang && $t->key == 'meta_title') {
                                                        $translate[$lang]['meta_title'] = $t->value;
                                                    }
                                                    if ($t->locale == $lang && $t->key == 'meta_description') {
                                                        $translate[$lang]['meta_description'] = $t->value;
                                                    }
                                                }
                                            }
                                            ?>
                                            <div class="d-none lang_form" id="{{ $lang }}-form">
                                                <div class=" ">
                                                    <label class="input-label"
                                                        for="{{ $lang }}_title">{{ 'metatítulo' }}
                                                        ({{ strtoupper($lang) }})
                                                        <span class="form-label-secondary" data-toggle="tooltip"
                                                            data-placement="right"
                                                            data-original-title="{{ 'Este título aparece en las pestañas del navegador, en los resultados de búsqueda y en las vistas previas de enlaces. Utilice un título breve, claro y centrado en palabras clave (recomendado: entre 50 y 60 caracteres).' }}">
                                                            <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                alt="">
                                                        </span>
                                                    </label>
                                                    <input type="text" name="meta_title[]" maxlength="60"
                                                        id="{{ $lang }}_title" class="form-control"
                                                        value="{{ $translate[$lang]['meta_title'] ?? '' }}"
                                                        placeholder="{{ 'metatítulo' }}">
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                <div class="mt-2">
                                                    <label class="input-label"
                                                        for="meta_description{{ $lang }}">{{ 'meta descripción' }}
                                                        ({{ strtoupper($lang) }})
                                                        <span class="form-label-secondary" data-toggle="tooltip"
                                                            data-placement="right"
                                                            data-original-title="{{ 'Un breve resumen que aparece debajo del título de su página en los resultados de búsqueda. Manténgalo atractivo y relevante (recomendado: 120 a 160 caracteres).' }}">
                                                            <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                alt="">
                                                        </span>
                                                    </label>
                                                    <textarea maxlength="160" id="meta_description{{ $lang }}" type="text" name="meta_description[]"
                                                        placeholder="{{ 'meta descripción' }}" class="form-control min-h-90px ckeditor">{{ $translate[$lang]['meta_description'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div id="default-form">
                                            <div class=" ">
                                                <label class="input-label"
                                                    for="meta_title">{{ 'metatítulo' }}
                                                    ({{ 'por defecto' }})</label>
                                                <input type="text" id="meta_title" name="meta_title[]"
                                                    class="form-control"
                                                    placeholder="{{ 'metatítulo' }}">
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            <div class="">
                                                <label class="input-label"
                                                    for="meta_description">{{ 'meta descripción' }}
                                                </label>
                                                <textarea type="text" id="meta_description" name="meta_description[]"
                                                    placeholder="{{ 'meta descripción' }}" class="form-control min-h-90px ckeditor"></textarea>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card shadow--card-2">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <span class="card-header-icon mr-1"><i class="tio-dashboard"></i></span>
                                        <span>{{ 'almacenar metaimagen' }}</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-center flex-wrap flex-sm-nowrap __gap-12px">
                                        <label class="__custom-upload-img mr-lg-5">
                                            <label class="form-label">
                                                {{ 'metaimagen' }} <span
                                                    class="text--primary">({{ '2:1' }})</span>
                                                <span class="form-label-secondary" data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-original-title="{{ 'Esta imagen se utiliza como miniatura de vista previa cuando el enlace de la página se comparte en redes sociales o plataformas de mensajería.' }}">
                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="">
                                                </span>
                                            </label>
                                            <div class="text-center">
                                                <img class="img--110 min-height-170px min-width-170px onerror-image"
                                                    id="viewer"
                                                    data-onerror-image="{{ asset('assets/admin/img/upload.png') }}"
                                                    src="{{ $store->meta_image_full_url }}"
                                                    alt="{{ 'metaimagen' }}" />
                                            </div>
                                            <input type="file" name="meta_image" id="customFileEg1"
                                                class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        </label>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="text-center">
                                            <small>{{ 'Sube una imagen rectangular (tamaño recomendado: 800×400 px, formato: JPG o PNG)' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="justify-content-end btn--container">
                                <button type="submit" class="btn btn--primary">{{ 'guardar cambios' }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @if (!config('module.' . $store->module->module_type)['always_open'])
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">
                        <span class="card-header-icon">
                            <i class="tio-date-range"></i>
                        </span>
                        <span>
                            {{ 'horario diario' }}
                        </span>
                    </h5>
                </div>
                <div class="card-body" id="schedule">
                    @include('vendor-views.business-settings.partials._schedule', $store)
                </div>
            </div>
        @endif
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ 'Crear horario para' }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="javascript:" method="post" id="add-schedule">
                            @csrf
                            <input type="hidden" name="day" id="day_id_input">
                            <div class=" ">
                                <label for="recipient-name"
                                    class="col-form-label">{{ 'Hora de inicio' }}:</label>
                                <input type="time" id="recipient-name" class="form-control" name="start_time"
                                    required>
                            </div>
                            <div class=" ">
                                <label for="message-text"
                                    class="col-form-label">{{ 'Hora de finalización' }}:</label>
                                <input type="time" id="message-text" class="form-control" name="end_time" required>
                            </div>
                            <div class="btn--container mt-4 justify-content-end">
                                <button type="reset" class="btn btn--reset">{{ 'reiniciar' }}</button>
                                <button type="submit"
                                    class="btn btn--primary">{{ 'Entregar' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create schedule modal -->

@endsection

@push('script_2')
    <script>
        "use strict";

        $(document).on('click', '.restaurant-open-status', function(event) {


            event.preventDefault();
            Swal.fire({
                title: '{{ '¿está seguro?' }}',
                text: '{{ $store->active ? 'quieres cerrar temporalmente esta tienda' : 'quieres abrir esta tienda' }}',
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#00868F',
                cancelButtonText: '{{ 'No' }}',
                confirmButtonText: '{{ 'Sí' }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {

                    $.get({
                        url: '{{ route('vendor.business-settings.update-active-status') }}',
                        contentType: false,
                        processData: false,
                        beforeSend: function() {
                            $('#loading').show();
                        },
                        success: function(data) {
                            toastr.success(data.message);
                        },
                        complete: function() {
                            location.reload();
                            $('#loading').hide();
                        },
                    });
                } else {
                    event.checked = !event.checked;
                }
            })

        });



        $(document).on('click', '.delete-schedule', function() {
            let route = $(this).data('url');
            Swal.fire({
                title: '{{ '¿Quieres eliminar este horario?' }}',
                text: '{{ 'Si selecciona Sí, se eliminará el horario.' }}',
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#00868F',
                cancelButtonText: '{{ 'No' }}',
                confirmButtonText: '{{ 'Sí' }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.get({
                        url: route,
                        beforeSend: function() {
                            $('#loading').show();
                        },
                        success: function(data) {
                            if (data.errors) {
                                for (let i = 0; i < data.errors.length; i++) {
                                    toastr.error(data.errors[i].message, {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                                }
                            } else {
                                $('#schedule').empty().html(data.view);
                                toastr.success(
                                    '{{ 'Programación eliminada exitosamente' }}', {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                            }
                        },
                        error: function() {
                            toastr.error('{{ 'Horario no encontrado' }}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        },
                        complete: function() {
                            $('#loading').hide();
                        },
                    });
                }
            })
        });


        function readURL(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();

                reader.onload = function(e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#customFileEg1").change(function() {
            readURL(this);
        });

        $(document).on('ready', function() {
            $("#gst_status").on('change', function() {
                if ($("#gst_status").is(':checked')) {
                    $('#gst').removeAttr('readonly');
                } else {
                    $('#gst').attr('readonly', true);
                }
            });
        });

        $('#exampleModal').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);
            let day_name = button.data('day');
            let day_id = button.data('dayid');
            let modal = $(this);
            modal.find('.modal-title').text('{{ 'Crear horario para' }} ' + day_name);
            modal.find('.modal-body input[name=day]').val(day_id);
        })

        $('#add-schedule').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('vendor.business-settings.add-schedule') }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        $('#schedule').empty().html(data.view);
                        $('#exampleModal').modal('hide');
                        toastr.success('{{ 'Programa agregado exitosamente' }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function(XMLHttpRequest) {
                    toastr.error(XMLHttpRequest.responseText, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush
