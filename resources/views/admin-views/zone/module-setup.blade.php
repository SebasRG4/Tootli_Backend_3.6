@extends('layouts.admin.app')

@section('title', translate('Zone Wise Module Setup'))

@push('css_or_js')
@endpush

@section('content')

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mb-1">
                <span>
                    {{ translate('Connect_Module_With') }} {{ $zone->name }}
                </span>
            </h1>
            <p class="fs-14">
                {{ translate('Here_you_connect_your_modules_&_setup_the_delivery_charges_for_this_zone.') }}
            </p>
        </div>
        <!-- End Page Header -->
        <form action="{{ route('admin.business-settings.zone.module-update', $zone->id) }}" method="post"
              id="zone_form">
            @csrf

            <div class="col-md-12 mb-2">
                <div class="card">
                    <div class="card-body">

                        <div class="row g-3 align-items-end">

                            <div class="col-sm-5 col-md-4">
                                <h3 for="">{{ translate('Select Payment Method') }} </h3>
                                @if (data_get($cash_on_delivery, 'status') != 1 && data_get($digital_payment, 'status') != 1 && $offline_payment != 1)
                                    <div
                                        class="danger-notes-bg px-2 py-2 rounded fz-11  gap-2 align-items-center d-flex ">
                                        <img src="{{ asset('assets/admin/img/Icon.svg') }}" alt="">
                                        <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="">
                                        <span>
                                            {{ translate('Must enable at least one payment method from your 3rd party payment settings.') }}
                                        </span>
                                    </div>
                                @else
                                    <div class="bg--4 px-2 py-2 rounded fz-11  gap-2 align-items-center d-flex ">
                                        <img src="{{ asset('assets/admin/img/Icon.svg') }}" alt="">

                                        <span>
                                            {{ translate('Must select at least one payment method.') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="col-sm-7 col-md-8">
                                <div class="justify-content-around d-flex border h-auto flex-wrap form-control max-w-420 ml-auto">

                                    @if (data_get($cash_on_delivery, 'status') == 1)
                                        <div class="form-check form-check-inline mx-4  ">
                                            <input class="mx-2 form-check-input" type="checkbox"
                                                   {{ $zone->cash_on_delivery == 1 ? 'checked' : '' }} id="cash_on_delivery"
                                                   value="1" name="cash_on_delivery">
                                            <label class=" form-check-label"
                                                   for="cash_on_delivery">{{ translate('Cash on Delivery') }}</label>
                                        </div>
                                    @endif
                                    @if (data_get($digital_payment, 'status') == 1)
                                        <div class="form-check form-check-inline mx-4  ">
                                            <input class="mx-2 form-check-input"
                                                   {{ $zone->digital_payment == 1 ? 'checked' : '' }} type="checkbox"
                                                   id="digital_payment" value="1" name="digital_payment">
                                            <label class=" form-check-label"
                                                   for="digital_payment">{{ translate('Digital Payment') }}</label>
                                        </div>
                                    @endif
                                    @if ($offline_payment == 1)
                                        <div class="form-check form-check-inline mx-4  ">
                                            <input class="mx-2 form-check-input" type="checkbox"
                                                   {{ $zone->offline_payment == 1 ? 'checked' : '' }} id="offline_payment"
                                                   value="1" name="offline_payment">
                                            <label class=" form-check-label"
                                                   for="offline_payment">{{ translate('Offline Payment') }}</label>
                                        </div>
                                    @endif

                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mb-2">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label class="input-label"
                                   for="exampleFormControlSelect1">{{ translate('Choose_Business_Module_To_Connect') }}
                                <span
                                    class="input-label-secondary"></span></label>
                            <select name="module_id[]" id="choice_modules" required
                                    class="form-control js-select2-custom"
                                    multiple="multiple">

                                @php($modules = \App\Models\Module::get(['id', 'module_name','module_type']))
                                @php($selected_modules = count($zone->modules) > 0 ? $zone->modules->pluck('id')->toArray() : [])
                                @foreach ($modules as $module)
                                    <option value="{{ $module['id'] }}"
                                        {{ in_array($module['id'], $selected_modules) ? 'selected' : '' }}>
                                        {{ $module['module_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            @if (count($selected_modules) > 0)
                <div class="col-md-12 mb-2 mt-3">
                    <h4 class="m-0">{{ translate('Delivery_Charge_Setup') }}</h4>
                </div>
                <div class="col-md-12 mb-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <h5 class="m-0">{{ translate('Multi_store_delivery_extra_title') }}</h5>
                                <span class="badge badge-soft-secondary">{{ translate('Multi_store_delivery_extra_global_badge') }}</span>
                            </div>
                            <p class="fs-12 text-muted mb-3">{{ translate('Multi_store_delivery_extra_hint') }}</p>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check form--check mb-0">
                                        <input type="hidden" name="multi_store_delivery_extra_status" value="0">
                                        <input class="form-check-input" type="checkbox" value="1"
                                               id="multi_store_delivery_extra_status"
                                               name="multi_store_delivery_extra_status"
                                            {{ (string) old('multi_store_delivery_extra_status', $multi_store_delivery_extra_status ?? '0') === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label"
                                               for="multi_store_delivery_extra_status">{{ translate('Multi_store_delivery_extra_enable') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group mb-0">
                                        <label class="input-label text-capitalize fs-14"
                                               for="multi_store_delivery_extra_amount">
                                            {{ translate('Multi_store_delivery_extra_amount_label') }}
                                            ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                        </label>
                                        <input type="number" class="form-control" id="multi_store_delivery_extra_amount"
                                               name="multi_store_delivery_extra_amount" step=".01" min="0"
                                               placeholder="{{ translate('messages.Ex:10') }}"
                                               value="{{ old('multi_store_delivery_extra_amount', $multi_store_delivery_extra_amount ?? '0') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if (count($modules) > 0)
                @foreach ($modules as $module)
                    @php($pivot = \App\Models\ModuleZone::where('zone_id', $zone->id)->where('module_id', $module->id)->first())
                    @if ($module->module_type == 'parcel')
                        <div class="col-md-12 mb-2" id="module_{{ $module->id }}">
                            <div class="module-row card view-details-container overflow-hidden">
                                <a href="#0"
                                   class="card-header border-0 view-btn d-flex align-items-center justify-content-between flex-wrap gap-1">
                                    <h5 class="m-0">{{ $module->module_name }} {{ translate('Module') }}</h5>
                                    <i class="tio-chevron-down fs-24 text-title"></i>
                                </a>
                                <div class="card-body view-details border-top">
                                    <div
                                        class="bg-opacity-primary-10 rounded py-2 px-3 d-flex flex-wrap gap-1 align-items-center">
                                        <div class="gap-1 d-flex align-items-center">
                                            <i class="tio-light-on theme-clr-dark fs-16"></i>
                                            <p class="m-0 fs-12">
                                                {{ translate('To Setup parcel module delivery charge please visit') }}
                                                <a
                                                    href="#0"
                                                    class="font-semibold text-title">{{ translate('Parcel Module > Delivery Setup') }}</a>
                                                {{ translate('page.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" value="distance"
                               name="module_data[{{ $module->id }}][delivery_charge_type]">
                        <input type="hidden" name="module_data[{{ $module->id }}][fixed_shipping_charge]"
                               value="{{ $pivot?->fixed_shipping_charge ?? 0 }}">
                        <input type="hidden" name="module_data[{{ $module->id }}][per_km_shipping_charge]"
                               value="{{ $pivot?->per_km_shipping_charge ?? 0 }}">
                        <input type="hidden" name="module_data[{{ $module->id }}][minimum_shipping_charge]"
                               value="{{ $pivot?->minimum_shipping_charge ?? 0 }}">
                        <input type="hidden" name="module_data[{{ $module->id }}][maximum_shipping_charge]"
                               value="{{ $pivot?->maximum_shipping_charge ?? 0 }}">
                        <input type="hidden" name="module_data[{{ $module->id }}][maximum_cod_order_amount]"
                               value="{{ $pivot?->maximum_cod_order_amount ?? 0 }}">
                    @elseif ($module->module_type == 'rental' && addon_published_status('Rental'))
                        <div class="col-md-12 mb-2" id="module_{{ $module->id }}">
                            <div class="module-row card view-details-container overflow-hidden">
                                <a href="#0"
                                   class="card-header border-0 view-btn d-flex align-items-center justify-content-between flex-wrap gap-1">
                                    <h5 class="m-0">{{ $module->module_name }}</h5>
                                    <i class="tio-chevron-down fs-24 text-title"></i>
                                </a>
                                <div class="card-body view-details border-top">
                                    <div
                                        class="bg-opacity-primary-10 rounded py-2 px-3 d-flex flex-wrap gap-1 align-items-center">
                                        <div class="gap-1 d-flex align-items-center">
                                            <i class="tio-light-on theme-clr-dark fs-16"></i>
                                            <p class="m-0 fs-12">
                                                {{ translate('Rental module doesn’t support delivery charges. You can set trip fare per vehicle from:') }}
                                                <a href="#0"
                                                   class="font-semibold text-title">{{ translate('Rental Module > Vehicle Management > Vehicle Setup > List.') }}</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" value="distance"
                               name="module_data[{{ $module->id }}][delivery_charge_type]">
                        <input type="hidden" name="module_data[{{ $module->id }}][fixed_shipping_charge]"
                               value="{{ $pivot?->fixed_shipping_charge ?? 0 }}">
                        <input type="hidden" name="module_data[{{ $module->id }}][per_km_shipping_charge]"
                               value="{{ $pivot?->per_km_shipping_charge ?? 0 }}">
                        <input type="hidden" name="module_data[{{ $module->id }}][minimum_shipping_charge]"
                               value="{{ $pivot?->minimum_shipping_charge ?? 0 }}">
                        <input type="hidden" name="module_data[{{ $module->id }}][maximum_shipping_charge]"
                               value="{{ $pivot?->maximum_shipping_charge ?? 0 }}">
                        <input type="hidden" name="module_data[{{ $module->id }}][maximum_cod_order_amount]"
                               value="{{ $pivot?->maximum_cod_order_amount ?? 0 }}">
                    @else
                        <div class="col-md-12 mb-2" id="module_{{ $module->id }}">
                            <div class="module-row card view-details-container overflow-hidden">
                                <a href="#0"
                                   class="card-header border-0 view-btn d-flex align-items-center justify-content-between flex-wrap gap-1">
                                    <h5 class="m-0">{{ $module->module_name }} {{ translate('Module') }}</h5>
                                    <i class="tio-chevron-down fs-24 text-title"></i>
                                </a>
                                <div class="card-body view-details border-top">
                                    <div class="row gy-1">
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label
                                                    class="input-label text-capitalize fs-14 d-flex alig-items-center line--limit-1">
                                                    {{ translate('messages.Choose_Delivery_Charge_Type') }} <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <div
                                                    class="d-flex align-items-center rounded py-2 px-3 border h-cus-456px">
                                                    <label class="form-check form--check mr-2 mr-md-4">
                                                        <input class="form-check-input delivery-type-radio" type="radio"
                                                               value="fixed"
                                                               name="module_data[{{ $module->id }}][delivery_charge_type]"
                                                            {{ $pivot?->delivery_charge_type == 'fixed' ? 'checked' : '' }}>
                                                        <span
                                                            class="form-check-label">{{ translate('messages.Fixed_Amount') }}</span>
                                                    </label>
                                                    <label class="form-check form--check mr-2 mr-md-4">
                                                        <input class="form-check-input delivery-type-radio" type="radio"
                                                               value="distance"
                                                               name="module_data[{{ $module->id }}][delivery_charge_type]"
                                                            {{ $pivot?->delivery_charge_type != 'fixed' ? 'checked' : '' }}>
                                                        <span
                                                            class="form-check-label">{{ translate('messages.Distance_Wise') }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4 fixed-charge-field">
                                            <div class="form-group mb-0">
                                                <label
                                                    class="input-label text-capitalize fs-14 d-flex alig-items-center line--limit-1">
                                                    {{ translate('messages.Amount') }}
                                                    ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="number" class="form-control"
                                                       name="module_data[{{ $module->id }}][fixed_shipping_charge]"
                                                       step=".01" min="0"
                                                       placeholder="{{ translate('messages.Ex:10') }}"
                                                       value="{{ $pivot?->fixed_shipping_charge }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4 distance-charge-field">
                                            <div class="form-group mb-0">
                                                <label
                                                    class="input-label text-capitalize fs-14 d-flex alig-items-center line--limit-1">
                                                    {{ translate('messages.Per_km_delivery_charge') }}
                                                    ({{ \App\CentralLogics\Helpers::currency_symbol() }}) <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <input type="number" class="form-control"
                                                       name="module_data[{{ $module->id }}][per_km_shipping_charge]"
                                                       step=".01" min="0"
                                                       placeholder="{{ translate('messages.Ex:10') }}"
                                                       value="{{ $pivot?->per_km_shipping_charge }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4 distance-charge-field">
                                            <div class="form-group mb-0">
                                                <label
                                                    class="input-label text-capitalize fs-14 d-flex alig-items-center line--limit-1">
                                                    {{ translate('messages.Minimum_delivery_charge') }}
                                                    ({{ \App\CentralLogics\Helpers::currency_symbol() }}) <span
                                                        class="text-danger">*</span>
                                                </label>
                                                <input type="number" step=".01" min="0" class="form-control"
                                                       name="module_data[{{ $module->id }}][minimum_shipping_charge]"
                                                       placeholder="{{ translate('messages.Ex:10') }}"
                                                       value="{{ $pivot?->minimum_shipping_charge }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4 distance-charge-field">
                                            <div class="form-group mb-0">
                                                <label
                                                    class="input-label text-capitalize fs-14 d-flex alig-items-center line--limit-1">
                                                    {{ translate('messages.Maximum_delivery_charge') }}
                                                    ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                </label>
                                                <input type="number" step=".01" min="0" class="form-control"
                                                       name="module_data[{{ $module->id }}][maximum_shipping_charge]"
                                                       placeholder="{{ translate('messages.Ex:10') }}"
                                                       value="{{ $pivot?->maximum_shipping_charge }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4 ">
                                            <div class="form-group mb-0">
                                                <label
                                                    class="input-label text-capitalize fs-14 d-flex alig-items-center line--limit-1">
                                                    {{ translate('messages.Maximum_cod_order_amount') }}
                                                    ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                </label>
                                                <input type="number" step=".01" min="0" class="form-control"
                                                       name="module_data[{{ $module->id }}][maximum_cod_order_amount]"
                                                       placeholder="{{ translate('messages.Ex:10') }}"
                                                       value="{{ $pivot?->maximum_cod_order_amount }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-4 ">
                                            <div class="form-group mb-0">
                                                <label
                                                    class="input-label text-capitalize fs-14 d-flex alig-items-center line--limit-1">
                                                    {{ translate('messages.max_delivery_radius') }} (km)
                                                </label>
                                                <input type="number" step=".01" min="0" class="form-control"
                                                       name="module_data[{{ $module->id }}][max_delivery_radius]"
                                                       placeholder="{{ translate('messages.Ex:10') }}"
                                                       value="{{ $pivot?->max_delivery_radius }}">
                                            </div>
                                        </div>
                                        @if ($module->module_type == 'ecommerce')
                                        <div class="col-md-6 col-lg-4 ">
                                            <div class="form-group mb-0">
                                                <label
                                                    class="input-label text-capitalize fs-14 d-flex alig-items-center line--limit-1">
                                                    {{ translate('Cargo extra pedidos grandes') }}
                                                    ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                </label>
                                                <input type="number" step=".01" min="0" class="form-control"
                                                       name="module_data[{{ $module->id }}][large_order_surcharge]"
                                                       placeholder="{{ translate('messages.Ex:120') }}"
                                                       value="{{ $pivot?->large_order_surcharge ?? 0 }}">
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label class="input-label text-capitalize fs-14 d-flex alig-items-center line--limit-1">
                                                    {{ translate('Delivery Grid') }}
                                                </label>
                                                <a href="{{ route('admin.business-settings.zone.grid-config', [$zone->id, $module->id]) }}" class="btn btn-outline-primary btn-sm btn-block h-cus-456px d-flex align-items-center justify-content-center">
                                                    <i class="tio-map mr-1"></i> {{ translate('Configure_Grid') }}
                                                </a>
                                            </div>
                                        </div>
                                        {{-- ── Programa de Envío Gratis por Módulo/Zona ─────────────────────── --}}
                                        <div class="col-12 mt-3">
                                            <div class="border rounded p-3" style="border-color: #0ea5e9 !important; background: rgba(14,165,233,0.04);">
                                                <div class="d-flex align-items-center gap-2 mb-3">
                                                    <span class="badge badge-info px-2 py-1" style="font-size:12px; background-color: #0ea5e9; color: #fff;">
                                                        <i class="tio-shopping-cart-add mr-1"></i> Envío Gratis con Umbral (AOV)
                                                    </span>
                                                    <span class="text-muted fs-12">
                                                        Define un monto de compra mínimo global para que el envío sea gratuito para todas las tiendas de este módulo en esta zona.
                                                    </span>
                                                </div>
                                                <div class="row gy-2 align-items-end">
                                                    <div class="col-md-6 col-lg-3">
                                                        <div class="form-group mb-0">
                                                            <label class="d-flex justify-content-between switch toggle-switch-sm text-dark mb-1" for="free_shipping_enabled_{{ $module->id }}">
                                                                <span>Activar envío gratis</span>
                                                                <input type="hidden" name="module_data[{{ $module->id }}][free_shipping_enabled]" value="0">
                                                                <input type="checkbox" class="toggle-switch-input"
                                                                       name="module_data[{{ $module->id }}][free_shipping_enabled]"
                                                                       id="free_shipping_enabled_{{ $module->id }}"
                                                                       value="1" {{ $pivot?->free_shipping_enabled ? 'checked' : '' }}
                                                                       onchange="document.getElementById('free_shipping_fields_{{ $module->id }}').style.display = this.checked ? 'flex' : 'none'">
                                                                <span class="toggle-switch-label">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div id="free_shipping_fields_{{ $module->id }}" class="row gy-2" style="display: {{ $pivot?->free_shipping_enabled ? 'flex' : 'none' }}">
                                                            <div class="col-md-6 col-lg-4">
                                                                <div class="form-group mb-0">
                                                                    <label class="input-label fs-14" for="free_shipping_threshold_{{ $module->id }}">
                                                                        Monto mínimo para envío gratis ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                                    </label>
                                                                    <input type="number" id="free_shipping_threshold_{{ $module->id }}"
                                                                           name="module_data[{{ $module->id }}][free_shipping_threshold]"
                                                                           step="1" min="0" class="form-control"
                                                                           placeholder="399"
                                                                           value="{{ $pivot?->free_shipping_threshold ?? 399 }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 col-lg-4">
                                                                <div class="form-group mb-0">
                                                                    <label class="input-label fs-14" for="store_shipping_contribution_{{ $module->id }}">
                                                                        Contribución de la tienda ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                                    </label>
                                                                    <input type="number" id="store_shipping_contribution_{{ $module->id }}"
                                                                           name="module_data[{{ $module->id }}][store_shipping_contribution]"
                                                                           step="1" min="0" class="form-control"
                                                                           placeholder="20"
                                                                           value="{{ $pivot?->store_shipping_contribution ?? 0 }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 col-lg-4 d-flex align-items-end">
                                                                <div class="alert alert-soft-info w-100 mb-0 py-2 px-3 small">
                                                                    💡 <strong>Ejemplo:</strong> Compra de ${{ $pivot?->free_shipping_threshold ?? 399 }}+ → Envío gratis.<br>
                                                                    Tienda aporta: ${{ $pivot?->store_shipping_contribution ?? 20 }} |
                                                                    Tootli: ${{ max(0, 40 - ($pivot?->store_shipping_contribution ?? 20)) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ── Tarifas Tootli Direct (opcionales, sobreescriben las regulares para pedidos POS) ── --}}
                                        <div class="col-12 mt-3">
                                            <div class="border rounded p-3" style="border-color: #3ab44a !important; background: rgba(58,180,74,0.04);">
                                                <div class="d-flex align-items-center gap-2 mb-3">
                                                    <span class="badge badge-success px-2 py-1" style="font-size:12px;">
                                                        <i class="tio-delivery-front mr-1"></i> Tootli Direct
                                                    </span>
                                                    <span class="text-muted fs-12">
                                                        Tarifas específicas para envíos POS Tootli Direct.
                                                        Si se dejan vacías, se usan las tarifas regulares de arriba.
                                                    </span>
                                                </div>
                                                <div class="row gy-2">
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label text-capitalize fs-14">
                                                                Tipo de tarifa Tootli Direct
                                                            </label>
                                                            <div class="d-flex align-items-center rounded py-2 px-3 border h-cus-456px">
                                                                <label class="form-check form--check mr-2 mr-md-4">
                                                                    <input class="form-check-input td-type-radio" type="radio"
                                                                           value="fixed"
                                                                           name="module_data[{{ $module->id }}][td_delivery_charge_type]"
                                                                        {{ $pivot?->td_delivery_charge_type == 'fixed' ? 'checked' : '' }}>
                                                                    <span class="form-check-label">Fija</span>
                                                                </label>
                                                                <label class="form-check form--check mr-2 mr-md-4">
                                                                    <input class="form-check-input td-type-radio" type="radio"
                                                                           value="distance"
                                                                           name="module_data[{{ $module->id }}][td_delivery_charge_type]"
                                                                        {{ $pivot?->td_delivery_charge_type == 'distance' ? 'checked' : '' }}>
                                                                    <span class="form-check-label">Por km</span>
                                                                </label>
                                                                <label class="form-check form--check">
                                                                    <input class="form-check-input td-type-radio" type="radio"
                                                                           value=""
                                                                           name="module_data[{{ $module->id }}][td_delivery_charge_type]"
                                                                        {{ !$pivot?->td_delivery_charge_type ? 'checked' : '' }}>
                                                                    <span class="form-check-label text-muted">Usar regular</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Fija --}}
                                                    <div class="col-md-6 col-lg-4 td-fixed-field {{ $pivot?->td_delivery_charge_type != 'fixed' ? 'd-none' : '' }}">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label text-capitalize fs-14">
                                                                Monto fijo ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                            </label>
                                                            <input type="number" step=".01" min="0" class="form-control"
                                                                   name="module_data[{{ $module->id }}][td_fixed_shipping_charge]"
                                                                   placeholder="Ej: 40"
                                                                   value="{{ $pivot?->td_fixed_shipping_charge }}">
                                                        </div>
                                                    </div>

                                                    {{-- Por km --}}
                                                    <div class="col-md-6 col-lg-4 td-distance-field {{ $pivot?->td_delivery_charge_type != 'distance' ? 'd-none' : '' }}">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label fs-14">
                                                                Tarifa por km ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                            </label>
                                                            <input type="number" step=".01" min="0" class="form-control"
                                                                   name="module_data[{{ $module->id }}][td_per_km_shipping_charge]"
                                                                   placeholder="Ej: 8"
                                                                   value="{{ $pivot?->td_per_km_shipping_charge }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-lg-4 td-distance-field {{ $pivot?->td_delivery_charge_type != 'distance' ? 'd-none' : '' }}">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label fs-14">
                                                                Mínimo ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                            </label>
                                                            <input type="number" step=".01" min="0" class="form-control"
                                                                   name="module_data[{{ $module->id }}][td_minimum_shipping_charge]"
                                                                   placeholder="Ej: 30"
                                                                   value="{{ $pivot?->td_minimum_shipping_charge }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-lg-4 td-distance-field {{ $pivot?->td_delivery_charge_type != 'distance' ? 'd-none' : '' }}">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label fs-14">
                                                                Máximo ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                            </label>
                                                            <input type="number" step=".01" min="0" class="form-control"
                                                                   name="module_data[{{ $module->id }}][td_maximum_shipping_charge]"
                                                                   placeholder="Ej: 80"
                                                                   value="{{ $pivot?->td_maximum_shipping_charge }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

            <div class="col-md-12">
                <div class="btn--container mt-3 justify-content-end">
                    <button id="reset_btn" type="reset" class="btn btn--reset">{{ translate('messages.Reset') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('messages.Save Information') }}</button>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
    <script src="{{ asset('assets/admin') }}/js/tags-input.min.js"></script>
    <script>
        "use strict";

        $(document).ready(function () {
            function toggleModuleSections() {
                let selectedModules = $('#choice_modules').val() || [];

                $('[id^="module_"]').addClass('d-none');

                selectedModules.forEach(function (moduleId) {
                    $('#module_' + moduleId).removeClass('d-none');
                });
            }

            toggleModuleSections();

            $('#choice_modules').on('change', function () {
                toggleModuleSections();
            });

            function toggleChargeFields(moduleContainer) {
                const selectedType = moduleContainer.find('input.delivery-type-radio:checked').val();

                if (selectedType === 'fixed') {
                    moduleContainer.find('.fixed-charge-field').removeClass('d-none');
                    moduleContainer.find('.distance-charge-field').addClass('d-none');
                } else {
                    moduleContainer.find('.fixed-charge-field').addClass('d-none');
                    moduleContainer.find('.distance-charge-field').removeClass('d-none');
                }
            }

            $('[id^="module_"]').each(function () {
                const moduleContainer = $(this);

                toggleChargeFields(moduleContainer);

                moduleContainer.find('input.delivery-type-radio').on('change', function () {
                    toggleChargeFields(moduleContainer);
                });
            });

            // Toggle Tootli Direct specific fields
            function toggleTdChargeFields(moduleContainer) {
                const selected = moduleContainer.find('input.td-type-radio:checked').val();
                if (selected === 'fixed') {
                    moduleContainer.find('.td-fixed-field').removeClass('d-none');
                    moduleContainer.find('.td-distance-field').addClass('d-none');
                } else if (selected === 'distance') {
                    moduleContainer.find('.td-fixed-field').addClass('d-none');
                    moduleContainer.find('.td-distance-field').removeClass('d-none');
                } else {
                    moduleContainer.find('.td-fixed-field').addClass('d-none');
                    moduleContainer.find('.td-distance-field').addClass('d-none');
                }
            }

            $('[id^="module_"]').each(function () {
                const moduleContainer = $(this);
                toggleTdChargeFields(moduleContainer);
                moduleContainer.find('input.td-type-radio').on('change', function () {
                    toggleTdChargeFields(moduleContainer);
                });
            });
        });

    </script>
@endpush
