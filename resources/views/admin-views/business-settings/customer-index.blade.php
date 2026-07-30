@extends('layouts.admin.app')

@section('title', 'configuración del cliente')

@push('css_or_js')
@endpush

@section('content')
    <div class="content">
        <form action="{{ route('admin.customer.update-settings') }}" method="post" enctype="multipart/form-data"
            id="update-settings">
            @csrf
            <div class="container-fluid">
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
           
                <div class="row g-3">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header card-header-shadow">
                                <h5 class="card-title d-flex align-items-center">
                                    <img src="{{ asset('assets/admin/img/ic_round-campaign.png') }}" alt=""
                                        class="card-header-icon align-self-center mr-1">
                                    <span>{{ 'Configuración del cliente' }}</span>
                                    <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ 'Aquí, los clientes pueden almacenar el monto de su pedido reembolsado, las ganancias por referencias y los puntos de fidelidad.' }}">
                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}" alt="{{ 'mostrar ocultar menú de comida' }}">
                                    </span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="py-2">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control">
                                                    <span class="pr-2">{{ 'Cartera del cliente' }}
                                                        <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                                              data-original-title="{{ 'Con esta función, los clientes pueden tener billeteras virtuales en su cuenta a través de la aplicación para clientes y el sitio web. También pueden ganar (mediante referencias, reembolsos, puntos de fidelidad o reembolsos) y comprar con el monto de la billetera.' }}">
                                                            <img src="{{ asset('assets/admin/img/info-circle.svg') }}" alt="{{ 'mostrar ocultar menú de comida' }}">
                                                        </span>
                                                    </span>
                                                    <input type="checkbox" data-id="wallet_status" data-type="toggle"
                                                        data-image-on="{{ asset('assets/admin/img/modal/wallet-on.png') }}"
                                                        data-image-off="{{ asset('assets/admin/img/modal/wallet-off.png') }}"
                                                        data-title-on="{{ 'Quiere habilitar' }} <strong>{{ 'la función Monedero?' }}</strong>"
                                                        data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ 'la función Monedero?' }}</strong>"
                                                        data-text-on="<p>{{ 'Si habilita esto, los Clientes pueden ver y usar la opción Monedero desde su perfil en la Aplicación y el Sitio web del Cliente.' }}</p>"
                                                        data-text-off="<p>{{ 'Si desactiva esto, la función Wallet se ocultará de la aplicación y el sitio web del cliente.' }}</p>"
                                                        class="status toggle-switch-input dynamic-checkbox-toggle"
                                                        name="customer_wallet" id="wallet_status" value="1"
                                                        {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
    
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label
                                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'text-muted' }}">
                                                    <span
                                                        class="pr-2">{{ 'El cliente puede ganar y comprar a partir de referencias' }}</span>
                                                    <input {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'disabled' }}
                                                     type="checkbox" data-id="ref_earning_status" data-type="toggle"
                                                        data-image-on="{{ asset('assets/admin/img/modal/referral-on.png') }}"
                                                        data-image-off="{{ asset('assets/admin/img/modal/referral-off.png') }}"
                                                        data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Ganancias por referencias?' }}</strong>"
                                                        data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Ganancias por referencias?' }}</strong>"
                                                        data-text-on="<p>{{ 'Si habilita esto, los clientes pueden ganar puntos al recomendar a otros para que se registren y compren en su empresa.' }}</p>"
                                                        data-text-off="<p>{{ 'Si desactiva esto, la función de obtención de referencias se ocultará de la aplicación y el sitio web del cliente.' }}</p>"
                                                        class="status toggle-switch-input dynamic-checkbox-toggle "
                                                        name="ref_earning_status" id="ref_earning_status"
                                                        data-section="referrer-earning" value="1"
                                                        {{ isset($data['ref_earning_status']) && $data['ref_earning_status'] == 1 ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
    
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label
                                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'text-muted' }}">
                                                    <span class="pr-2">{{ 'reembolso a la billetera' }}<span
                                                            class="input-label-secondary" data-toggle="tooltip"
                                                            data-placement="right"
                                                            data-original-title="{{ 'Si está habilitado, los Clientes recibirán automáticamente el monto reembolsado en sus billeteras. Pero si está deshabilitado, el administrador manejará la solicitud de reembolso en su conveniente canal de transacción.' }}"><img
                                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                alt="{{ 'mostrar ocultar menú de comida' }}"></span></span>
                                                    <input type="checkbox"
                                                    {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'disabled' }}
                                                    data-id="refund_to_wallet" data-type="toggle"
                                                        data-image-on="{{ asset('assets/admin/img/modal/refund-on.png') }}"
                                                        data-image-off="{{ asset('assets/admin/img/modal/refund-off.png') }}"
                                                        data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Función de reembolso a Wallet?' }}</strong>"
                                                        data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Función de reembolso a Wallet?' }}</strong>"
                                                        data-text-on="<p>{{ 'Si habilita esto, los Clientes recibirán automáticamente el monto reembolsado en sus billeteras.' }}</p>"
                                                        data-text-off="<p>{{ 'Si desactiva esto, el administrador manejará la solicitud de reembolso en su conveniente canal de transacciones.' }}</p>"
                                                        class="status toggle-switch-input dynamic-checkbox-toggle "
                                                        name="refund_to_wallet" id="refund_to_wallet" value="1"
                                                        {{ isset($data['wallet_add_refund']) && $data['wallet_add_refund'] == 1 ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
    
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label
                                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'text-muted' }}">
                                                    <span class="pr-2">{{ 'el cliente puede agregar fondos a la billetera' }}
                                                        <span class="input-label-secondary" data-toggle="tooltip"
                                                            data-placement="right"
                                                            data-original-title="{{ 'Con esta función, los clientes pueden agregar fondos a la billetera si el módulo de pago está disponible.' }}">
                                                            <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                alt="{{ 'agregar estado del fondo' }}">
                                                        </span>
                                                    </span>
                                                    <input {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'disabled' }}
                                                    type="checkbox" data-id="add_fund_status" data-type="toggle"
                                                        data-image-on="{{ asset('assets/admin/img/modal/wallet-on.png') }}"
                                                        data-image-off="{{ asset('assets/admin/img/modal/wallet-off.png') }}"
                                                        data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Agregar fondos a la función Wallet?' }}</strong>"
                                                        data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Agregar fondos a la función Wallet?' }}</strong>"
                                                        data-text-on="<p>{{ 'Si habilita esto, los clientes pueden agregar fondos a la billetera usando el módulo de pago' }}</p>"
                                                        data-text-off="<p>{{ 'Si desactiva esto, agregar fondos a la billetera se ocultará en la aplicación y el sitio web del cliente.' }}</p>"
                                                        class="status toggle-switch-input dynamic-checkbox-toggle "
                                                        name="add_fund_status" id="add_fund_status" value="1"
                                                        {{ isset($data['add_fund_status']) && $data['add_fund_status'] == 1 ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
    
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label
                                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                                    for="customer_loyalty_point">
                                                    <span
                                                        class="pr-2">{{ 'El cliente puede ganar puntos de fidelidad' }}</span>
                                                    <input type="checkbox" data-id="customer_loyalty_point"
                                                        data-type="toggle"
                                                        data-image-on="{{ asset('assets/admin/img/modal/loyalty-on.png') }}"
                                                        data-image-off="{{ asset('assets/admin/img/modal/loyalty-off.png') }}"
                                                        data-title-on="{{ 'Quiere habilitar' }} <strong>{{ 'Punto de fidelización' }}</strong>"
                                                        data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ 'Punto de fidelización' }}</strong>"
                                                        data-text-on="<p>{{ 'El cliente verá la opción de puntos de fidelidad en la configuración de su perfil y podrá ganar y convertir este punto en dinero de billetera.' }}</p>"
                                                        data-text-off="<p>{{ 'El cliente no verá la opción de puntos de fidelidad en la configuración de su perfil' }}</p>"
                                                        class="status toggle-switch-input dynamic-checkbox-toggle"
                                                        name="customer_loyalty_point" id="customer_loyalty_point"
                                                        data-section="loyalty-point-section" value="1"
                                                        {{ isset($data['loyalty_point_status']) && $data['loyalty_point_status'] == 1 ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
    
                                        <div class="col-sm-6 col-lg-4">
                                            @php($vnv = \App\Models\BusinessSetting::where('key', 'toggle_veg_non_veg')->first())
                                            @php($vnv = $vnv ? $vnv->value : 0)
                                            <div class="form-group mb-0">
    
                                                <label
                                                    class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{ 'Preferencia alimentaria del cliente' }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip"
                                                      data-placement="right"
                                                      data-original-title="{{ 'Si esta función está activa, los clientes pueden filtrar los alimentos según sus preferencias desde la aplicación del cliente o el sitio web.' }}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'vegetales no vegetales' }}"> * </span>
                                            </span>
                                                    <input type="checkbox" data-id="vnv1" data-type="toggle"
                                                           data-image-on="{{ asset('assets/admin/img/modal/veg-on.png') }}"
                                                           data-image-off="{{ asset('assets/admin/img/modal/veg-off.png') }}"
                                                           data-title-on="{{ 'Quiere habilitar el' }} <strong>{{ '¿Función \'vegetal/no vegetariana\'?' }}</strong>"
                                                           data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ 'la función vegetariana/no vegetariana?' }}</strong>"
                                                           data-text-on="<p>{{ 'Si habilita esto, los clientes pueden filtrar los alimentos eligiendo alimentos de la función Vegetal/No vegetariano.' }}</p>"
                                                           data-text-off="<p>{{ 'Si desactiva esto, la función Vegetal/No vegetariano se ocultará en la aplicación y el sitio web del cliente.' }}</p>"
                                                           class="status toggle-switch-input dynamic-checkbox-toggle" value="1"
                                                           name="vnv" id="vnv1" {{ $vnv == 1 ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                                </label>
                                            </div>
                                        </div>
    
                                        <div class="col-sm-6 col-lg-4">
                                            @php($guest_checkout_status = \App\Models\BusinessSetting::where('key', 'guest_checkout_status')->first())
                                            @php($guest_checkout_status = $guest_checkout_status ? $guest_checkout_status->value : 0)
                                            <div class="form-group mb-0">
                                                <label
                                                    class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{'pago de invitado' }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip"
                                                      data-placement="right"
                                                      data-original-title="{{ 'Si está habilitado, los clientes no tienen que iniciar sesión mientras revisan los pedidos.'}}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'alternancia de variación del cliente' }}"> *
                                                </span>
                                            </span>
                                                    <input type="checkbox" data-id="guest_checkout_status" data-type="toggle"
                                                           data-image-on="{{ asset('assets/admin/img/modal/dm-tips-on.png') }}"
                                                           data-image-off="{{ asset('assets/admin/img/modal/dm-tips-off.png') }}"
                                                           data-title-on="<strong>{{ '¿Quieres habilitar el pago como invitado?' }}</strong>"
                                                           data-title-off="<strong>{{ '¿Quieres desactivar el pago como invitado?' }}</strong>"
                                                           data-text-on="<p>{{ 'Si habilita esto, el pago como invitado será visible cuando el cliente no haya iniciado sesión.' }}</p>"
                                                           data-text-off="<p>{{ 'Si desactiva esto, el pago como invitado no será visible cuando el cliente no haya iniciado sesión.' }}</p>"
                                                           class="status toggle-switch-input dynamic-checkbox-toggle" value="1"
                                                           name="guest_checkout_status" id="guest_checkout_status" {{ $guest_checkout_status == 1 ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                                </label>
                                            </div>
                                        </div>
    
                                        <div class="col-sm-6 col-lg-4">
                                            @php($country_picker_status = \App\Models\BusinessSetting::where('key', 'country_picker_status')->first())
                                            @php($country_picker_status = $country_picker_status ? $country_picker_status->value : 0)
                                            <div class="form-group mb-0">
                                                <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                                    <span class="pr-1 d-flex align-items-center switch--label">
                                                        <span class="line--limit-1">
                                                            {{'selector de país' }}
                                                        </span>
                                                        <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip"
                                                              data-placement="right"
                                                              data-original-title="{{ 'Si habilita esta opción, en todos los teléfonos ningún campo mostrará una lista de selección de países.'}}"><img
                                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                alt="{{ 'alternancia de variación del cliente' }}">
                                                        </span>
                                                    </span>
                                                    <input type="checkbox" data-id="country_picker_status" data-type="toggle"
                                                           data-image-on="{{ asset('assets/admin/img/modal/mail-success.png') }}"
                                                           data-image-off="{{ asset('assets/admin/img/modal/mail-warning.png') }}"
                                                           data-title-on="<strong>{{ '¿Quieres habilitar el selector de países?' }}</strong>"
                                                           data-title-off="<strong>{{ '¿Quieres desactivar el selector de países?' }}</strong>"
                                                           data-text-on="<p>{{ 'Si habilita esto, el usuario puede seleccionar el país desde el selector de países.' }}</p>"
                                                           data-text-off="<p>{{ 'Si desactiva esto, el usuario no podrá seleccionar el país en el selector de países; se seleccionará el país predeterminado' }}</p>"
                                                           class="status toggle-switch-input dynamic-checkbox-toggle" value="1"
                                                           name="country_picker_status" id="country_picker_status" {{ $country_picker_status == 1 ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <div class="card mt-2">
                            <div class="card-header card-header-shadow">
                                <h5 class="card-title">
                                    <img src="{{ asset('assets/admin/img/loyalty.png') }}" alt=""
                                        class="card-header-icon align-self-center mr-1">
                                    <span>{{ 'Configuración de puntos de fidelización de clientes' }}</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="py-2">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label class="input-label" for="loyalty_point_exchange_rate">1
                                                    {{ \App\CentralLogics\Helpers::currency_code() }}
                                                    {{ 'cantidad de puntos equivalente' }}</label>
                                                <input {{ isset($data['loyalty_point_status']) && $data['loyalty_point_status'] == 1 ? 'required' : 'readonly' }}
                                                id="loyalty_point_exchange_rate" type="number" class="form-control" name="loyalty_point_exchange_rate" step=".001" min="0"
                                                    value="{{ $data['loyalty_point_exchange_rate'] ?? '0' }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label class="input-label" for="item_purchase_point">
                                                    {{ 'Puntos de fidelidad ganados por pedido' }} (%)
                                                    <small class="text-danger">
                                                        <span class="input-label-secondary"
                                                            data-toggle="tooltip" data-placement="right"
                                                            data-original-title="{{ 'En cada compra, este porcentaje del monto se agregará como punto de fidelidad en su cuenta.' }}"><img
                                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                alt="{{ 'En cada compra, este porcentaje del monto se agregará como punto de fidelidad en su cuenta.' }}">
                                                        </span>*
                                                    </small>
                                                </label>
                                                <input {{ isset($data['loyalty_point_status']) && $data['loyalty_point_status'] == 1 ? 'required' : 'readonly' }} id="item_purchase_point"
                                                       type="number" class="form-control" name="item_purchase_point" step=".001" min="0" value="{{ $data['loyalty_point_item_purchase_point'] ?? '0' }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="form-group mb-0">
                                                <label class="input-label" for="minimum_transfer_point">
                                                    {{ 'Punto mínimo requerido para convertir' }}
                                                </label>
                                                <input {{ isset($data['loyalty_point_status']) && $data['loyalty_point_status'] == 1 ? 'required' : 'readonly' }} id="minimum_transfer_point"
                                                       type="number" class="form-control" name="minimun_transfer_point" min="0" step=".001" value="{{ $data['loyalty_point_minimum_point'] ?? '0' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-2">
                            <div class="card-header card-header-shadow">
                                <h5 class="card-title">
                                    <img src="{{ asset('assets/admin/img/loyalty.png') }}" alt=""
                                        class="card-header-icon align-self-center mr-1">
                                    <span>
                                        {{ 'Configuración de ganancias por referencias de clientes' }}
                                    </span>
                                    <span class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ 'Los clientes existentes pueden compartir un código de referencia con otros para ganar un bono por recomendación. Para ello, el nuevo usuario DEBE registrarse utilizando el código de referencia y realizar su primera compra.' }}">
                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                            alt="{{ 'mostrar ocultar menú de comida' }}">
                                    </span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="py-2">
                                    <div class="row g-3 align-items-end">
    
                                        <div class="align-self-center  col-4">
                                            <div class="text-left">
                                                <h4 class="align-items-center">
                                                    <img src="{{ asset('assets/admin/img/referral.png') }}"
                                                        alt="" class="card-header-icon align-self-center mr-1">
                                                    <span>
                                                        {{ 'Quién comparte el código' }}
                                                    </span>
                                                </h4>
                                                <p>
                                                    {{ 'Los clientes recibirán recompensas de saldo de billetera por compartir su código de referencia con amigos, quienes usan el código al registrarse y completar su primer pedido.' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-8">
                                            <div class="card __bg-F8F9FC-card text-left">
                                                <div class="card-body">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label" for="ref_earning_exchange_rate">
                                                            {{ 'Ganancia por referencia' }}
                                                            {{ \App\CentralLogics\Helpers::currency_code() }}
                                                        </label>
                                                        <input {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'readonly' }}
                                                        id="ref_earning_exchange_rate" type="number" step=".001" min="0" max="99999999999"
                                                            class="form-control" name="ref_earning_exchange_rate"
                                                            value="{{ $data['ref_earning_exchange_rate'] ?? '0' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row g-3 align-items-end">
                                        <div class="align-self-center col-4 text-center">
                                            <div class="text-left">
    
                                                <h4 class="align-items-center">
                                                    <img src="{{ asset('assets/admin/img/Who_Use_the_code.png') }}"
                                                        alt="" class="card-header-icon align-self-center mr-1">
                                                    <span>
                                                        {{ '¿Quién usa el código?' }}
                                                    </span>
                                                </h4>
                                                <p>
                                                    {{ 'Al aplicar el código de referencia durante el registro y al realizar su primera compra, los clientes disfrutarán de un descuento por tiempo limitado.' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-8">
                                            <div class="card __bg-F8F9FC-card text-left">
                                                <div class="card-body">
                                                    <div>
                                                        <div class="form-group mb-0">
                                                            <label
                                                                class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'text-muted' }}">
                                                                <span
                                                                    class="pr-2">{{ 'El cliente obtendrá un descuento en el primer pedido.' }}
                                                                    <span class="input-label-secondary" data-toggle="tooltip"
                                                                        data-placement="right"
                                                                        data-original-title="{{ 'Configure descuentos para usuarios recién registrados que se registren con un código de referencia. Personalice el tipo y el monto del descuento para incentivar las referencias y fomentar la participación de los usuarios.' }}">
                                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                            alt="{{ 'mostrar ocultar menú de comida' }}">
                                                                    </span>
                                                                </span>
                                                                <input {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? '' : 'disabled' }}
                                                                type="checkbox" data-id="new_customer_discount_status"
                                                                    data-type="toggle"
                                                                    data-image-on="{{ asset('assets/admin/img/modal/basic_campaign_on.png') }}"
                                                                    data-image-off="{{ asset('assets/admin/img/modal/basic_campaign_off.png') }}"
                                                                    data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Descuento para nuevos clientes?' }}</strong>"
                                                                    data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Descuento para nuevos clientes?' }}</strong>"
                                                                    data-text-on="<p>{{ 'Si habilita esto, los clientes obtendrán un descuento en el primer pedido.' }}</p>"
                                                                    data-text-off="<p>{{ 'mo. Si desactiva esto, los clientes no obtendrán ningún descuento en el primer pedido.' }}</p>"
                                                                    class="status toggle-switch-input dynamic-checkbox-toggle "
                                                                    name="new_customer_discount_status"
                                                                    id="new_customer_discount_status" value="1"
                                                                    {{ data_get($data, 'new_customer_discount_status') == 1 ? 'checked' : '' }}>
                                                                <span class="toggle-switch-label text">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label>
                                                        </div>
    
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-8 mt-3">
                                                            <div class="form-group mb-0">
                                                                <label class="input-label" for="new_customer_discount_amount">
                                                                    {{ 'Cantidad de descuento' }}
    
                                                                    <span class="{{  data_get($data, 'new_customer_discount_amount_type') != 'amount'  ? '': 'd-none' }} " id="percentage">(%)</span>
                                                                    <span  class=" {{  data_get($data, 'new_customer_discount_amount_type') == 'amount' ? '': 'd-none' }} " id='cuttency_symbol'>({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                                    </span>
    
    
                                                                    <span
                                                                        class="input-label-secondary" data-toggle="tooltip"
                                                                        data-placement="right"
                                                                        data-original-title="{{ 'Ingrese el valor del descuento para registros de nuevos usuarios basados ​​en referencias.' }}">
                                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                            alt="{{ 'mostrar ocultar menú de comida' }}">
                                                                    </span>
                                                                </label>
                                                                <input id="new_customer_discount_amount" type="number" step=".001" min="0"
                                                                {{  isset($data['wallet_status']) && $data['wallet_status'] == 1 && data_get($data, 'new_customer_discount_status') == 1 ? 'required' : 'readonly' }}
                                                                    class="form-control" name="new_customer_discount_amount" max='{{  data_get($data, 'new_customer_discount_amount_type') != 'amount'  ? '100': '9999999999' }}'
                                                                    value="{{data_get($data, 'new_customer_discount_amount') ?? '0' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-4  mt-3">
                                                            <div class="form-group mb-0">
                                                                <select   name="new_customer_discount_amount_type"  class="form-control mt-5"  id="new_customer_discount_amount_type"
                                                                {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 && data_get($data, 'new_customer_discount_status') == 1 ? 'required' : 'disabled' }}
    
                                                                >
                                                                    <option {{ data_get($data, 'new_customer_discount_amount_type') == 'percentage' ? "selected": '' }} value="percentage">{{'porcentaje'}} (%)</option>
                                                                    <option {{ data_get($data, 'new_customer_discount_amount_type') == 'amount' ? "selected": '' }}  value="amount">{{'cantidad'}} {{ \App\CentralLogics\Helpers::currency_symbol() }}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
    
                                                        <div class="col-8 mt-3">
                                                            <div class="form-group mb-0">
                                                                <label class="input-label" for="new_customer_discount_amount_validity">
                                                                    {{ 'validez' }}
                                                                    <span class="input-label-secondary" data-toggle="tooltip"
                                                                        data-placement="right"
                                                                        data-original-title="{{ 'Establece cuánto tiempo permanece activo el descuento después del registro.' }}">
                                                                        <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                            alt="{{ 'mostrar ocultar menú de comida' }}">
                                                                    </span>
                                                                </label>
                                                                <input id="new_customer_discount_amount_validity" type="number" step="1" min="0" max="999"
                                                                {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 && data_get($data, 'new_customer_discount_status') == 1 ? 'required' : 'readonly' }}
                                                                    class="form-control" name="new_customer_discount_amount_validity"
                                                                    value="{{ data_get($data, 'new_customer_discount_amount_validity') ?? '0' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-4 mt-3">
                                                            <div class="form-group  mb-0">
                                                                <select name="new_customer_discount_validity_type" class="form-control mt-5" id="new_customer_discount_validity_type"  {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 &&  data_get($data, 'new_customer_discount_status') == 1 ? 'required' : 'disabled' }}>
                                                                    <option {{ data_get($data, 'new_customer_discount_validity_type') == 'day' ? "selected": '' }} value="day">{{'día'}}</option>
                                                                    <option {{ data_get($data, 'new_customer_discount_validity_type') == 'month' ? "selected": '' }}  value="month">{{'mes'}} </option>
                                                                    <option {{ data_get($data, 'new_customer_discount_validity_type') == 'year' ? "selected": '' }}  value="year">{{'año'}} </option>
                                                                </select>
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
                </div>
            </div>
            <div class="mt-0 footer-sticky">
                <div class="container-fluid">
                    <div class="btn--container justify-content-end py-3">
                        <button type="reset" id="reset_btn"
                            class="btn btn--reset location-reload">{{ 'reiniciar' }}</button>
                        <button type="submit" id="submit"
                            class="btn btn--primary">{{ 'Guardar información' }}</button>
                    </div>
                </div>
            </div>
        </form>
        <!-- End Table -->
    </div>
@endsection

@push('script_2')
<script>
    "use strict";

    $('#new_customer_discount_amount_type').on('change', function() {
        if($('#new_customer_discount_amount_type').val() == 'amount')
        {
            $('#percentage').addClass('d-none');
            $('#cuttency_symbol').removeClass('d-none');
            $('#new_customer_discount_amount').attr('max',99999999999);

        }
        else
        {
            $('#percentage').removeClass('d-none');
            $('#cuttency_symbol').addClass('d-none');
            $('#new_customer_discount_amount').attr('max',100);

        }
    });

</script>
@endpush
