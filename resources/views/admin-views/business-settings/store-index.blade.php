@extends('layouts.admin.app')

@section('title', 'configuración de la tienda')


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mr-3">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/business.png') }}" class="w--26" alt="">
                </span>
                <span>
                    {{ 'configuración de negocios' }}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.nav-menu')
        </div>
        <form action="{{ route('admin.business-settings.update-store') }}" method="post" enctype="multipart/form-data">
            @csrf
            @php($name = \App\Models\BusinessSetting::where('key', 'business_name')->first())

            <div class="row g-3">
                @php($default_location = \App\Models\BusinessSetting::where('key', 'default_location')->first())
                @php($default_location = $default_location->value ? json_decode($default_location->value, true) : 0)
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-4 col-sm-6">
                                    @php($canceled_by_store = \App\Models\BusinessSetting::where('key', 'canceled_by_store')->first())
                                    @php($canceled_by_store = $canceled_by_store ? $canceled_by_store->value : 0)
                                    <div class="form-group mb-0">
                                        <label class="input-label text-capitalize d-flex alig-items-center"><span
                                                class="line--limit-1">{{ '¿Puede un proveedor cancelar un pedido?' }}
                                            </span><span class="input-label-secondary text--title" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ 'El administrador puede habilitar/deshabilitar la opción de cancelación de pedidos del Proveedor.' }}">
                                                <i class="tio-info-outined"></i>
                                            </span></label>
                                        <div class="restaurant-type-group border">
                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input" type="radio" value="1"
                                                    name="canceled_by_store" id="canceled_by_store"
                                                    {{ $canceled_by_store == 1 ? 'checked' : '' }}>
                                                <span class="form-check-label">
                                                    {{ 'Sí' }}
                                                </span>
                                            </label>
                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input" type="radio" value="0"
                                                    name="canceled_by_store" id="canceled_by_store2"
                                                    {{ $canceled_by_store == 0 ? 'checked' : '' }}>
                                                <span class="form-check-label">
                                                    {{ 'No' }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    @php($store_self_registration = \App\Models\BusinessSetting::where('key', 'toggle_store_registration')->first())
                                    @php($store_self_registration = $store_self_registration ? $store_self_registration->value : 0)
                                    <div class="form-group mb-0">

                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{ 'Autorregistro de proveedores' }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Un proveedor puede enviar una solicitud de registro a través de su proveedor o cliente.' }}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'autorregistro de proveedor' }}"> *
                                                </span>
                                            </span>
                                            <input type="checkbox"
                                                   data-id="store_self_registration1"
                                                   data-type="toggle"
                                                   data-image-on="{{ asset('assets/admin/img/modal/store-self-reg-on.png') }}"
                                                   data-image-off="{{ asset('assets/admin/img/modal/store-self-reg-off.png') }}"
                                                   data-title-on=""
                                                   data-title-off=""
                                                   data-text-on="<p>{{ 'Si habilita esto, los proveedores pueden registrarse automáticamente desde la aplicación o el sitio web del proveedor o del cliente.' }}</p>"
                                                   data-text-off="<p>{{ 'Si desactiva esto, la función Autorregistro de proveedor se ocultará de la aplicación, el sitio web o la página de inicio del administrador del proveedor o del cliente.' }}</p>"
                                                   class="status toggle-switch-input dynamic-checkbox-toggle"
                                                   value="1"
                                                name="store_self_registration" id="store_self_registration1"
                                                {{ $store_self_registration == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-4">
                                    @php($product_gallery = \App\Models\BusinessSetting::where('key', 'product_gallery')->first()?->value ?? 0)
                                    <div class="form-group mb-0">
                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{'Galería de productos' }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Si habilita esto, cualquier proveedor puede duplicar el producto y crear un nuevo producto usando esto.'}}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'Galería de productos' }}"> *
                                                </span>
                                            </span>
                                            <input type="checkbox"

                                                   data-id="product_gallery"
                                                   data-type="toggle"
                                                   data-image-on="{{ asset('assets/admin/img/modal/store-reg-on.png') }}"
                                                   data-image-off="{{ asset('assets/admin/img/modal/store-reg-off.png') }}"
                                                   data-title-on="<strong>{{'¿Quieres habilitar la galería de productos?'}}</strong>"
                                                   data-title-off="<strong>{{'¿Quieres desactivar la galería de productos?'}}</strong>"
                                                   data-text-on="<p>{{ 'Si habilita esto, puede crear productos duplicados.' }}</p>"
                                                   data-text-off="<p>{{ 'Si desactiva esto, no podrá crear productos duplicados.' }}</p>"
                                                   class="status toggle-switch-input dynamic-checkbox-toggle"
                                                   value="1"
                                                name="product_gallery" id="product_gallery"
                                                {{ $product_gallery == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4 {{ $product_gallery == 1 ? ' ' : 'd-none' }}  access_all_products">
                                    @php($access_all_products = \App\Models\BusinessSetting::where('key', 'access_all_products')->first()?->value ?? 0)
                                    <div class="form-group mb-0">
                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{'acceder a todos los productos' }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Si habilita esto, los proveedores pueden acceder a todos los productos de otros proveedores.'}}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'acceder a todos los productos' }}"> *
                                                </span>
                                            </span>
                                            <input type="checkbox"

                                                   data-id="access_all_products"
                                                   data-type="toggle"
                                                   data-image-on="{{ asset('assets/admin/img/modal/store-reg-on.png') }}"
                                                   data-image-off="{{ asset('assets/admin/img/modal/store-reg-off.png') }}"
                                                   data-title-on="<strong>{{'¿Quieres habilitar el acceso a todos los productos?'}}</strong>"
                                                   data-title-off="<strong>{{'¿Quieres desactivar el acceso a todos los productos?'}}</strong>"
                                                   data-text-on="<p>{{ 'Si habilita esto, los proveedores pueden acceder a todos los productos de otros proveedores disponibles.' }}</p>"
                                                   data-text-off="<p>{{ 'Si desactiva esto, los proveedores no podrán acceder a todos los productos de otros proveedores.' }}</p>"
                                                   class="status toggle-switch-input dynamic-checkbox-toggle"
                                                   value="1"
                                                name="access_all_products" id="access_all_products"
                                                {{ $access_all_products == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    @php($product_approval = \App\Models\BusinessSetting::where('key', 'product_approval')->first()?->value ?? 0)
                                    <div class="form-group mb-0">
                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{'Necesita aprobación para productos' }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Si está habilitada, esta opción requerirá la aprobación del administrador para que los productos se muestren en el lado del usuario.'}}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'alternar verificación de cliente' }}"> *
                                                </span>
                                            </span>
                                            <input type="checkbox"
                                                   data-id="product_approval"
                                                   data-type="toggle"
                                                   data-image-on="{{ asset('assets/admin/img/modal/store-reg-on.png') }}"
                                                   data-image-off="{{ asset('assets/admin/img/modal/store-reg-off.png') }}"
                                                   data-title-on="<strong>{{'¿Quiere habilitar la aprobación del producto?'}}</strong>"
                                                   data-title-off="<strong>{{'¿Quiere desactivar la aprobación del producto?'}}</strong>"
                                                   data-text-on="<p>{{ 'Si habilita esto, la opción requerirá la aprobación del administrador para que los productos se muestren en el lado del usuario.' }}</p>"
                                                   data-text-off="<p>{{ 'Si desactiva esto, los productos se mostrarán en el lado del usuario sin la aprobación del administrador.' }}</p>"
                                                   class="status toggle-switch-input dynamic-checkbox-toggle"
                                                   value="1"
                                                name="product_approval" id="product_approval"
                                                {{ $product_approval == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6">
                                    @php($store_review_reply = \App\Models\BusinessSetting::where('key', 'store_review_reply')->first())
                                    @php($store_review_reply = $store_review_reply ? $store_review_reply->value : 0)
                                    <div class="form-group mb-0">

                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{ 'El proveedor puede responder a la revisión' }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                      data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ 'Si está habilitado, los proveedores pueden interactuar activamente con los clientes respondiendo a las reseñas dejadas para sus pedidos.' }}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'respuesta a la reseña de la tienda' }}">
                                                </span>
                                            </span>
                                            <input type="checkbox"

                                                   data-id="store_review_reply1"
                                                   data-type="toggle"
                                                   data-image-on="{{ asset('assets/admin/img/modal/store-self-reg-on.png') }}"
                                                   data-image-off="{{ asset('assets/admin/img/modal/store-self-reg-off.png') }}"
                                                   data-title-on="{{ '¿Quiere permitir que el proveedor de opciones responda?' }}"
                                                   data-title-off="{{ '¿Quiere desactivar la opción de respuesta del proveedor?' }}"
                                                   data-text-on="<p>{{ 'Si está habilitado, los proveedores pueden interactuar activamente con los clientes respondiendo a las reseñas dejadas sobre sus pedidos.' }}</p>"
                                                   data-text-off="<p>{{ 'Si está deshabilitado, un proveedor no puede responder a una reseña.' }}</p>"
                                                   class="toggle-switch-input dynamic-checkbox-toggle"

                                                   value="1"
                                                   name="store_review_reply" id="store_review_reply1"
                                                {{ $store_review_reply == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @php($product_approval_datas = \App\Models\BusinessSetting::where('key', 'product_approval_datas')->first()?->value ?? '')
                            @php($product_approval_datas =json_decode($product_approval_datas , true))
                            <div class="mt-4  mb-4 access_product_approval">
                                <label class="mb-2 input-label text-capitalize d-flex alig-items-center" for=""> {{ 'Necesita aprobación cuando' }}</label>
                                <div class="justify-content-between border form-control">
                                    <div class="form-check form-check-inline mx-4  ">
                                        <input class="mx-2 form-check-input" type="checkbox" {{  data_get($product_approval_datas,'Add_new_product',null) == 1 ? 'checked' :'' }} id="inlineCheckbox1" value="1" name="Add_new_product" {{  $product_approval == 1 ? ' ' : 'disabled'}}>
                                        <label class=" form-check-label" for="inlineCheckbox1">{{ 'Añadir nuevo producto' }}</label>
                                    </div>
                                    <div class="form-check form-check-inline mx-4  ">
                                        <input class="mx-2 form-check-input" type="checkbox"  {{  data_get($product_approval_datas,'Update_product_price',null) == 1 ? 'checked' :'' }} id="inlineCheckbox2" value="1" name="Update_product_price" {{  $product_approval == 1 ? ' ' : 'disabled'}}>
                                        <label class=" form-check-label" for="inlineCheckbox2">{{ 'Actualizar precio del producto' }}</label>
                                    </div>
                                    <div class="form-check form-check-inline mx-4  ">
                                        <input class="mx-2 form-check-input" type="checkbox" {{  data_get($product_approval_datas,'Update_product_variation',null) == 1 ? 'checked' :'' }}  id="inlineCheckbox3" value="1" name="Update_product_variation" {{  $product_approval == 1 ? ' ' : 'disabled'}}>
                                        <label class=" form-check-label" for="inlineCheckbox3">{{ 'Actualizar variación del producto' }}</label>
                                    </div>
                                    <div class="form-check form-check-inline mx-4  ">
                                        <input class="mx-2 form-check-input" type="checkbox"  {{  data_get($product_approval_datas,'Update_anything_in_product_details',null) == 1 ? 'checked' :'' }} id="inlineCheckbox4" value="1" name="Update_anything_in_product_details" {{  $product_approval == 1 ? ' ' : 'disabled'}}>
                                        <label class=" form-check-label" for="inlineCheckbox4">{{ 'Actualizar cualquier cosa en los detalles del producto.' }}</label>
                                    </div>
                                </div>
                            </div>



                            <div class="row g-3 align-items-end">
                            <div class="col-lg-4 col-sm-6">
                                @php($cash_in_hand_overflow = \App\Models\BusinessSetting::where('key', 'cash_in_hand_overflow_store')->first())
                                @php($cash_in_hand_overflow = $cash_in_hand_overflow ? $cash_in_hand_overflow->value : '')
                                <div class="form-group mb-0">

                                    <label
                                        class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{ 'Desbordamiento de efectivo en mano' }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                      data-toggle="tooltip" data-placement="right"
                                                      data-original-title="{{ 'Si está habilitado, el sistema suspenderá automáticamente a los proveedores cuando se exceda su límite de "efectivo en mano".' }}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'efectivo en mano desbordamiento' }}"> *
                                                </span>
                                            </span>
                                        <input type="checkbox"
                                               data-id="cash_in_hand_overflow"
                                               data-type="toggle"
                                               data-image-on="{{ asset('assets/admin/img/modal/show-earning-in-apps-on.png') }}"
                                               data-image-off="{{ asset('assets/admin/img/modal/show-earning-in-apps-off.png') }}"
                                               data-title-on="{{'Quiere habilitar'}} <strong>{{'Desbordamiento de efectivo en mano'}}</strong>"
                                               data-title-off="{{'Quiere deshabilitar'}} <strong>{{'Desbordamiento de efectivo en mano'}}</strong> "
                                               data-text-on="<p>{{ 'Si está habilitado, los proveedores deben proporcionar el efectivo recaudado por sí mismos.' }}</p>"
                                               data-text-off="<p>{{ 'Si están discapacitados, los proveedores no tienen que proporcionar ellos mismos el efectivo recaudado.' }}</p>"
                                               class="status toggle-switch-input dynamic-checkbox-toggle"
                                                value="1"
                                               name="cash_in_hand_overflow_store" id="cash_in_hand_overflow"
                                            {{ $cash_in_hand_overflow == 1 ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                    </label>
                                </div>
                            </div>





                            <div class="col-lg-4 col-sm-6">
                                @php($cash_in_hand_overflow_store_amount = \App\Models\BusinessSetting::where('key', 'cash_in_hand_overflow_store_amount')->first())
                                <div class="form-group mb-0">
                                    <label class=" input-label text-capitalize"
                                           for="cash_in_hand_overflow_store_amount">
                                            <span>
                                                {{ 'Cantidad máxima para tener efectivo en mano' }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                            </span>

                                        <span class="form-label-secondary"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Ingrese el monto máximo en efectivo que pueden retener los proveedores. Si este número excede, los proveedores serán suspendidos y no recibirán ningún pedido.' }}"><img
                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                alt="{{ 'dm cancelar sugerencia de pedido' }}"></span>
                                    </label>
                                    <input type="number" name="cash_in_hand_overflow_store_amount" class="form-control"
                                           id="cash_in_hand_overflow_store_amount" min="0" step=".001"
                                           value="{{ $cash_in_hand_overflow_store_amount ? $cash_in_hand_overflow_store_amount->value : '' }}"  {{ $cash_in_hand_overflow  == 1 ? 'required' : 'readonly' }} >
                                </div>
                            </div>


                            <div class="col-lg-4 col-sm-6">
                                @php($min_amount_to_pay_store = \App\Models\BusinessSetting::where('key', 'min_amount_to_pay_store')->first())
                                <div class="form-group mb-0">
                                    <label class=" input-label text-capitalize"
                                           for="min_amount_to_pay_store">
                                            <span>
                                                {{ 'Monto mínimo a pagar' }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})

                                            </span>

                                        <span class="form-label-secondary"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Ingrese el monto mínimo en efectivo que los proveedores pueden pagar' }}"><img
                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                alt="{{ 'dm cancelar sugerencia de pedido' }}"></span>
                                    </label>
                                    <input type="number" name="min_amount_to_pay_store" class="form-control"
                                           id="min_amount_to_pay_store" min="0" step=".001"
                                           value="{{ $min_amount_to_pay_store ? $min_amount_to_pay_store->value : '' }}"  {{ $cash_in_hand_overflow  == 1 ? 'required' : 'readonly' }} >
                                </div>
                            </div>
                            </div>
                            
                            <div class="btn--container justify-content-end mt-20">
                                <button type="reset" class="btn btn--reset">{{ 'reiniciar' }}</button>
                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                    class="btn btn--primary call-demo">{{ 'guardar información' }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </form>
    </div>

@endsection

