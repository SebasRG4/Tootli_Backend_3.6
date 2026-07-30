@extends('layouts.admin.app')

@section('title', 'configuración del repartidor')


@section('content')
@php use App\CentralLogics\Helpers; @endphp
    <div class="content">
        <form action="{{ route('admin.business-settings.update-dm') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex align-items-center justify-content-between gap-1 w-100">
                        <h1 class="page-header-title mr-3">
                            <span class="page-header-icon">
                                <img src="{{ asset('assets/admin/img/business.png') }}" class="w--26" alt="">
                            </span>
                            <span>
                                {{'configuración de negocios'}}
                            </span>
                        </h1>
                        @if (!(Request::is('admin/business-settings/language') || Request::is('admin/business-settings/business-setup/refund-settings') || Request::is('admin/business-settings/business-setup/automated-message')))
                        <div class="d-flex flex-wrap justify-content-end align-items-center flex-grow-1">
                            <div class="blinkings active">
                                <i class="tio-info-outined"></i>
                                <div class="business-notes">
                                    <h6><img src="{{asset('assets/admin/img/notes.png')}}" alt=""> {{'Nota'}}</h6>
                                    <div>
                                        @if (Request::is('admin/business-settings/business-setup/refund-settings'))
                                        {{ '*Si el administrador habilita el "Modo de solicitud de reembolso", los clientes pueden solicitar un reembolso.' }}
                                        @else
                                        {{'No olvide hacer clic en el botón "Guardar información" a continuación para guardar los cambios.'}}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @include('admin-views.business-settings.partials.nav-menu')
                </div>
                <!-- Page Header -->
        
                <!-- End Page Header -->
           
                <div class="row g-2">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="rounded p-xxl-20 p-3 bg-light2">
    
                                    <div class="row g-3">
                                        <div class="col-sm-6 col-lg-4">
                                            @php($dm_tips_status = Helpers::get_business_settings('dm_tips_status'))
                                            <div class="form-group mb-0">
                                                <span class="d-flex align-items-center mb-2">
                                                    <span class="text-dark pr-1">
                                                        {{ 'Consejos para el repartidor' }}
                                                    </span>
                                                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'El cliente puede dar propinas al repartidor durante el pago desde la aplicación y el sitio web del cliente. A partir de esto, el administrador no tiene comisión.' }}">
                                                        <i class="tio-info text-light-gray"></i>
                                                    </span>
                                                </span>
                                                <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                                    <span class="line--limit-1 switch--label">
                                                        {{ 'Estado' }}
                                                    </span>
                                                    <input type="checkbox"
                                                           data-id="dm_tips_status"
                                                           data-type="toggle"
                                                           data-image-on="{{ asset('assets/admin/img/modal/dm-tips-on.png') }}"
                                                           data-image-off="{{ asset('assets/admin/img/modal/dm-tips-off.png') }}"
                                                           data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Consejos para la función Deliveryman?' }}</strong>"
                                                           data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Consejos para la función Deliveryman?' }}</strong>"
                                                           data-text-on="<p>{{ 'Si habilita esto, los clientes pueden darle propinas a un repartidor durante el pago.' }}</p>"
                                                           data-text-off="<p>{{ 'Si desactiva esto, la función Consejos para el repartidor se ocultará de la aplicación del cliente y del sitio web.' }}</p>"
                                                           class="status toggle-switch-input dynamic-checkbox-toggle"
                                                            value="1"
                                                        name="dm_tips_status" id="dm_tips_status"
                                                        {{ $dm_tips_status == '1' ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4">
                                            @php($show_dm_earning = Helpers::get_business_settings('show_dm_earning')  )
                                            <div class="form-group mb-0">
                                                <span class="d-flex align-items-center mb-2">
                                                    <span class="text-dark pr-1">
                                                        {{ 'Mostrar ganancias en la aplicación' }}
                                                    </span>
                                                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Con esta función, los repartidores pueden ver sus ganancias en un pedido específico mientras lo aceptan.' }}">
                                                        <i class="tio-info text-light-gray"></i>
                                                    </span>
                                                </span>
                                                <label
                                                    class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                                    <span class="pr-1 d-flex align-items-center switch--label">
                                                        <span class="line--limit-1">
                                                            {{ 'Estado' }}
                                                        </span>
                                                    </span>
                                                    <input type="checkbox"
                                                           data-id="show_dm_earning"
                                                           data-type="toggle"
                                                           data-image-on="{{ asset('assets/admin/img/modal/show-earning-in-apps-on.png') }}"
                                                           data-image-off="{{ asset('assets/admin/img/modal/show-earning-in-apps-off.png') }}"
                                                           data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Mostrar ganancias en la aplicación?' }}</strong>"
                                                           data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Mostrar ganancias en la aplicación?' }}</strong>"
                                                           data-text-on="<p>{{ 'Si habilita esto, los repartidores pueden ver sus ganancias por solicitud de pedido desde la página Detalles del pedido en la aplicación Deliveryman.' }}</p>"
                                                           data-text-off="<p>{{ 'Si desactiva esto, la función se ocultará de la aplicación Deliveryman.' }}</p>"
                                                           class="status toggle-switch-input dynamic-checkbox-toggle"
    
                                                           value="1"
                                                        name="show_dm_earning" id="show_dm_earning"
                                                        {{ $show_dm_earning == 1 ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4">
    
                                            @php($toggle_dm_registration =   Helpers::get_business_settings('toggle_dm_registration') )
                                            <div class="form-group mb-0">
                                                <span class="d-flex align-items-center mb-2">
                                                    <span class="text-dark pr-1">
                                                        {{ 'dm auto registro' }}
                                                    </span>
                                                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Con esta función, los repartidores pueden registrarse desde la aplicación del cliente, el sitio web o la aplicación del repartidor o la página de inicio del administrador. El administrador recibirá una notificación por correo electrónico y podrá aceptar o rechazar la solicitud.' }}">
                                                        <i class="tio-info text-light-gray"></i>
                                                    </span>
                                                </span>
                                                <label
                                                    class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                                    <span class="pr-1 d-flex align-items-center switch--label">
                                                        <span class="line--limit-1">
                                                            {{ 'Estado' }}
                                                        </span>
                                                    </span>
                                                    <input type="checkbox"
                                                           data-id="dm_self_registration1"
                                                           data-type="toggle"
                                                           data-image-on="{{ asset('assets/admin/img/modal/dm-self-reg-on.png') }}"
                                                           data-image-off="{{ asset('assets/admin/img/modal/dm-self-reg-off.png') }}"
                                                           data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Autorregistro del repartidor?' }}</strong>"
                                                           data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Autorregistro del repartidor?' }}</strong>"
                                                           data-text-on="<p>{{ 'Si habilita esto, los usuarios pueden registrarse como repartidores desde la aplicación del cliente, el sitio web o la aplicación del repartidor o la página de inicio del administrador.' }}</p>"
                                                           data-text-off="<p>{{ 'Si desactiva esto, esta función se ocultará de la aplicación del cliente, el sitio web o la aplicación del repartidor o la página de inicio del administrador.' }}</p>"
                                                           class="status toggle-switch-input dynamic-checkbox-toggle"
    
                                                           value="1"
                                                        name="toggle_dm_registration" id="dm_self_registration1"
                                                        {{ $toggle_dm_registration == 1 ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4">
                                            @php($dm_maximum_orders =  Helpers::get_business_settings('dm_maximum_orders')   )
                                            <div class="form-group mb-0">
                                                <label class="form-label text-capitalize"
                                                    for="dm_maximum_orders">
                                                    <div class="d-flex align-items-center">
                                                        <span class="line--limit-1 flex-grow pr-1">{{ 'Límite máximo de orden asignado' }} </span>
                                                        <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Establezca el límite máximo de pedidos que un repartidor puede aceptar a la vez.' }}">
                                                            <i class="tio-info text-light-gray"></i>
                                                        </span>
                                                    </div>
                                                </label>
                                                <input type="number" name="dm_maximum_orders" class="form-control"
                                                    id="dm_maximum_orders" min="1"
                                                    value="{{ $dm_maximum_orders ?? 1 }}" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4">
                                            @php($canceled_by_deliveryman = Helpers::get_business_settings('canceled_by_deliveryman'))
                                            <div class="form-group mb-0">
                                                <label class="input-label text-capitalize d-flex align-items-center"><span
                                                        class="line--limit-1 pr-1">{{ '¿Puede un repartidor cancelar un pedido?' }}</span>
                                                    <span class="form-label-secondary"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'El administrador puede habilitar/deshabilitar la opción de cancelación de pedidos del repartidor en la aplicación respectiva.' }}"><i class="tio-info text-light-gray"></i></span></label>
                                                <div class="resturant-type-group border">
                                                    <label class="form-check form--check mr-2 mr-md-4">
                                                        <input class="form-check-input" type="radio" value="1"
                                                            name="canceled_by_deliveryman" id="canceled_by_deliveryman"
                                                            {{ $canceled_by_deliveryman == 1 ? 'checked' : '' }}>
                                                        <span class="form-check-label">
                                                            {{ 'Sí' }}
                                                        </span>
                                                    </label>
                                                    <label class="form-check form--check mr-2 mr-md-4">
                                                        <input class="form-check-input" type="radio" value="0"
                                                            name="canceled_by_deliveryman" id="canceled_by_deliveryman2"
                                                            {{ $canceled_by_deliveryman == 0 ? 'checked' : '' }}>
                                                        <span class="form-check-label">
                                                            {{ 'No' }}
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4">
                                            @php($dm_picture_upload_status = Helpers::get_business_settings('dm_picture_upload_status'))
                                            <div class="form-group mb-0">
                                                <span class="d-flex align-items-center mb-2">
                                                    <span class="text-dark pr-1">
                                                        {{ 'Tome una fotografía para completar la entrega' }}
                                                    </span>
                                                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Si está habilitado, los repartidores verán una opción para tomar fotografías de los productos entregados cuando deslicen la diapositiva de confirmación de entrega.' }}">
                                                        <i class="tio-info text-light-gray"></i>
                                                    </span>
                                                </span>
                                                <label
                                                    class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                                    <span class="pr-1 d-flex align-items-center switch--label">
                                                        <span class="line--limit-1">
                                                            {{ 'Estado' }}
                                                        </span>
                                                    </span>
                                                    <input type="checkbox"
                                                           data-id="dm_picture_upload_status"
                                                           data-type="toggle"
                                                           data-image-on="{{ asset('assets/admin/img/modal/dm-self-reg-on.png') }}"
                                                           data-image-off="{{ asset('assets/admin/img/modal/dm-self-reg-off.png') }}"
                                                           data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Cargar la imagen antes de completarla?' }}</strong>"
                                                           data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Cargar la imagen antes de completarla?' }}</strong>"
                                                           data-text-on="<p>{{ 'Si habilita esto, el repartidor puede cargar la prueba del pedido antes de la entrega del mismo.' }}</p>"
                                                           data-text-off="<p>{{ 'Si desactiva esto, esta función se ocultará de la aplicación del repartidor.' }}</p>"
                                                           class="status toggle-switch-input dynamic-checkbox-toggle"
                                                           value="1"
                                                        name="dm_picture_upload_status" id="dm_picture_upload_status"
                                                        {{ $dm_picture_upload_status == 1 ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
    
    
    
    
                                        <div class="col-sm-6 col-lg-4">
                                            @php($cash_in_hand_overflow = Helpers::get_business_settings('cash_in_hand_overflow_delivery_man'))
                                            <div class="form-label  mb-0 ">
                                                <span class="d-flex align-items-center mb-2">
                                                    <span class="text-dark pr-1">
                                                        {{ 'Desbordamiento de efectivo en mano' }}
                                                    </span>
                                                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Si está habilitado, el sistema suspenderá automáticamente a los repartidores cuando se exceda su límite de "efectivo en mano".' }}">
                                                        <i class="tio-info text-light-gray"></i>
                                                    </span>
                                                </span>
                                                <label
                                                    class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                                    <span class="pr-1 d-flex align-items-center switch--label">
                                                        <span class="line--limit-1">
                                                            {{ 'Estado' }}
                                                        </span>
                                                    </span>
                                                    <input type="checkbox"
                                                           data-id="cash_in_hand_overflow"
                                                           data-type="toggle"
                                                           data-image-on="{{ asset('assets/admin/img/modal/show-earning-in-apps-on.png') }}"
                                                           data-image-off="{{ asset('assets/admin/img/modal/show-earning-in-apps-off.png') }}"
                                                           data-title-on="{{ 'Quiere habilitar' }} <strong>{{ 'Desbordamiento de efectivo en mano' }}</strong>?"
                                                           data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ 'Desbordamiento de efectivo en mano' }}</strong>?"
                                                           data-text-on="<p>{{ 'Si está habilitado, los repartidores deben proporcionar ellos mismos el dinero recogido.' }}</p>"
                                                           data-text-off="<p>{{ 'Si están discapacitados, los repartidores no tienen que proporcionar ellos mismos el dinero recogido.' }}</p>"
                                                           class="status toggle-switch-input dynamic-checkbox-toggle"
                                                           value="1"
                                                           name="cash_in_hand_overflow_delivery_man" id="cash_in_hand_overflow"
                                                        {{ $cash_in_hand_overflow == 1 ? 'checked' : '' }}>
                                                    <span class="toggle-switch-label text">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4">
                                            @php($dm_max_cash_in_hand =  Helpers::get_business_settings('dm_max_cash_in_hand') )
                                            <div class="form-label mb-0">
                                                <label class="d-flex text-capitalize"
                                                       for="dm_max_cash_in_hand">
                                                    <span class="line--limit-1">
                                                        {{'Repartidor Máximo efectivo en mano'}} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                    </span>
                                                    <span data-toggle="tooltip" data-placement="right" data-original-title="{{'El repartidor no puede aceptar ningún pedido cuando se excede el límite de efectivo en mano y debe depositar el monto al administrador antes de aceptar nuevos pedidos.'}}" class="input-label-secondary"><i class="tio-info text-light-gray"></i></span>
                                                </label>
                                                <input type="number" name="dm_max_cash_in_hand" class="form-control"
                                                       id="dm_max_cash_in_hand" min="0" step=".001"
                                                       value="{{ $dm_max_cash_in_hand ?? '' }}" {{ $cash_in_hand_overflow  == 1 ? 'required' : 'readonly' }} >
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4">
                                            @php($dm_max_cash_in_hand_total_block =  Helpers::get_business_settings('dm_max_cash_in_hand_total_block') )
                                            <div class="form-label mb-0">
                                                <label class="d-flex text-capitalize"
                                                       for="dm_max_cash_in_hand_total_block">
                                                    <span class="line--limit-1">
                                                        Delivery Man Maximum Cash in Hand Total Block ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                                    </span>
                                                    <span data-toggle="tooltip" data-placement="right" data-original-title="El límite máximo de efectivo antes de que se bloqueen todas las órdenes (incluidas las pagadas en línea)." class="input-label-secondary"><i class="tio-info text-light-gray"></i></span>
                                                </label>
                                                <input type="number" name="dm_max_cash_in_hand_total_block" class="form-control"
                                                       id="dm_max_cash_in_hand_total_block" min="0" step=".001"
                                                       value="{{ $dm_max_cash_in_hand_total_block ?? '' }}" {{ $cash_in_hand_overflow  == 1 ? 'required' : 'readonly' }} >
                                            </div>
                                        </div>
    
    
    
                                        <div class="col-sm-6 col-lg-4">
                                            @php($min_amount_to_pay_dm = Helpers::get_business_settings('min_amount_to_pay_dm')  )
                                            <div class="form-label mb-0">
                                                <label class="text-capitalize"
                                                       for="min_amount_to_pay_dm">
                                                    <span>
                                                        {{ 'Monto mínimo a pagar' }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})
    
                                                    </span>
    
                                                    <span class="form-label-secondary"
                                                          data-toggle="tooltip" data-placement="right"
                                                          data-original-title="{{ 'Ingrese el monto mínimo en efectivo que los repartidores pueden pagar' }}"><i class="tio-info text-light-gray"></i></span>
                                                </label>
                                                <input type="number" name="min_amount_to_pay_dm" class="form-control"
                                                       id="min_amount_to_pay_dm" min="0" step=".001"
                                                       value="{{ $min_amount_to_pay_dm ?? '' }}"  {{ $cash_in_hand_overflow  == 1 ? 'required' : 'readonly' }} >
                                            </div>
                                        </div>
    
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        @php($dm_loyality_point_status = Helpers::get_business_settings('dm_loyality_point_status')  )
                        @php($dm_loyality_point_per_order = Helpers::get_business_settings('dm_loyality_point_per_order')  )
                        @php($dm_loyality_point_conversion_rate = Helpers::get_business_settings('dm_loyality_point_conversion_rate')  )
                        @php($dm_min_loyality_point_to_convert = Helpers::get_business_settings('dm_min_loyality_point_to_convert')  )
    
                        <div class="card mt-20 card-container">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between gap-2 flex-sm-nowrap flex-wrap">
                                    <div>
                                        <h4 class="mb-1">{{'Punto de fidelización'}}</h4>
                                        <p class="fs-12 m-0">{{'Si está habilitado, los repartidores ganarán una cierta cantidad de puntos por cada entrega exitosa.'}}</p>
                                    </div>
                                    <div class="d-flex flex-sm-nowrap flex-wrap justify-content-end justify-content-end align-items-center gap-3">
                                        <div class="view_toggle_btn fz--14px info-dark cursor-pointer text-decoration-underline font-semibold d-flex align-items-center gap-1">
                                            {{ 'vista' }}
                                            <i class="tio-chevron-down fs-22"></i>
                                        </div>
                                        <div class="mb-0">
                                            <label class="toggle-switch toggle-switch-sm mb-0">
                                                <input type="checkbox" data-type="toggle" class="status toggle-switch-input" name="dm_loyality_point_status" id="dm_loyality_point_status" value="1" {{ $dm_loyality_point_status == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label text mb-0">
                                                    <span
                                                        class="toggle-switch-indicator">
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-details-body {{ !$dm_loyality_point_status ? 'd-none' : '' }} ">
                                    <div class="bg-light2  rounded p-xxl-20 p-3 mt-20">
                                        <div class="row g-3">
                                            <div class="col-sm-6 col-lg-4">
                                                <div class="form-group mb-0">
                                                    <label class="form-label text-capitalize" for="dm_loyality_point_per_order">
                                                        <div class="d-flex align-items-center">
                                                            <span class="line--limit-1 flex-grow pr-1">{{ 'Puntos de fidelidad ganados por pedido' }} </span>
                                                        </div>
                                                    </label>
                                                    <input type="number" name="dm_loyality_point_per_order" class="form-control" min="0"   max="9999999999"  id="dm_loyality_point_per_order" placeholder="1" value="{{ $dm_loyality_point_per_order ?? ''}}" {{ $dm_loyality_point_status == 1 ? 'required':'readonly' }}>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-lg-4">
                                                <div class="form-group mb-0">
                                                    <label class="form-label text-capitalize" for="dm_loyality_point_conversion_rate">
                                                        <div class="d-flex align-items-center">
                                                            <span class="line--limit-1 flex-grow pr-1">{{ \App\CentralLogics\Helpers::currency_symbol() }} {{ '1,00 equivalente a puntos' }} </span>
                                                        </div>
                                                    </label>
                                                    <input type="number" name="dm_loyality_point_conversion_rate"  min="0" max="999999999"  class="form-control" id="dm_loyality_point_conversion_rate" placeholder="100" value="{{ $dm_loyality_point_conversion_rate ?? ''}}" {{ $dm_loyality_point_status == 1 ? 'required':'readonly' }}>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-lg-4">
                                                <div class="form-group mb-0">
                                                    <label class="form-label text-capitalize" for="dm_min_loyality_point_to_convert">
                                                        <div class="d-flex align-items-center">
                                                            <span class="line--limit-1 flex-grow pr-1">{{ 'Punto mínimo requerido para convertir' }} </span>
                                                        </div>
                                                    </label>
                                                    <input type="number" name="dm_min_loyality_point_to_convert" min="0" max="999999999"  class="form-control" id="dm_min_loyality_point_to_convert" placeholder="200" value="{{ $dm_min_loyality_point_to_convert ?? '' }}" {{ $dm_loyality_point_status == 1 ? 'required':'readonly' }}>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
    
    
                        @php($dm_referal_status = Helpers::get_business_settings('dm_referal_status')  )
                        @php($dm_referal_amount = Helpers::get_business_settings('dm_referal_amount')  )
                        @php($dm_referal_bonus = Helpers::get_business_settings('dm_referal_bonus')  )
    
                        <div class="card mt-20 card-container">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between gap-2 flex-sm-nowrap flex-wrap">
                                    <div>
                                        <h4 class="mb-1">{{'Configuración de ganancias por referencia de repartidor'}}</h4>
                                        <p class="fs-12 m-0">{{'Permita que los conductores recomienden su aplicación a amigos y familiares utilizando un código único y obtengan recompensas.'}}</p>
                                    </div>
                                    <div class="d-flex flex-sm-nowrap flex-wrap justify-content-end justify-content-end align-items-center gap-3">
                                        <div class="view_toggle_btn fz--14px info-dark cursor-pointer text-decoration-underline font-semibold d-flex align-items-center gap-1">
                                            {{ 'vista' }}
                                            <i class="tio-chevron-down fs-22"></i>
                                        </div>
                                        <div class="mb-0">
                                            <label class="toggle-switch toggle-switch-sm mb-0">
                                                <input type="checkbox" data-type="toggle" class="status toggle-switch-input" name="dm_referal_status" id="dm_referal_status" value="1" {{ $dm_referal_status == 1 ? 'checked' : '' }} >
                                                <span class="toggle-switch-label text mb-0">
                                                    <span
                                                        class="toggle-switch-indicator">
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-details-body {{ !$dm_referal_status ? 'd-none' : '' }}">
                                    <div class="bg-light2 d-flex flex-column gap-4 rounded p-xxl-20 p-3 mt-20">
                                        <div class="row g-3">
                                            <div class="col-md-6 col-lg-4">
                                                <div>
                                                    <h4 class="mb-1">{{'Quién comparte el código'}}</h4>
                                                    <p class="fs-12 m-0">{{'Establezca el monto de recompensa que los conductores ganarán por cada recomendación exitosa. La recompensa se entregará a la persona que utilice el código de referencia durante el registro y complete su primer pedido.'}}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-8">
                                                <div class="bg-white rounded p-xxl-20 p-2">
                                                    <div class="form-group mb-0">
                                                        <label class="form-label text-capitalize" for="dm_referal_amount">
                                                            <div class="d-flex align-items-center">
                                                                <span class="line--limit-1 flex-grow pr-1">{{ 'Ganancia por referencia' }} ({{ \App\CentralLogics\Helpers::currency_symbol() }})  <span class="text-danger">*</span> </span>
                                                            </div>
                                                        </label>
                                                        <input type="number" name="dm_referal_amount"   min="0" max="999999999" step="0.001" class="form-control " id="dm_referal_amount" placeholder="100" value="{{ $dm_referal_amount??'' }}" {{ $dm_referal_status ? 'required' : 'readonly' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6 col-lg-4">
                                                <div>
                                                    <h4 class="mb-1">{{'¿Quién usa el código?'}}</h4>
                                                    <p class="fs-12 m-0">{{'Establezca el monto de recompensa que reciben los conductores al registrarse con un código de referencia y completar el primer pedido'}}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-8">
                                                <div class="bg-white rounded p-xxl-20 p-2">
                                                    <div class="form-group mb-0">
                                                        <label class="form-label text-capitalize" for="dm_referal_bonus">
                                                            <div class="d-flex align-items-center">
                                                                <span class="line--limit-1 flex-grow pr-1">{{ 'Bono en billetera' }} ({{ \App\CentralLogics\Helpers::currency_symbol() }}) <span class="text-danger">*</span> </span>
                                                            </div>
                                                        </label>
                                                        <input type="number" name="dm_referal_bonus" min="0" max="999999999" step="0.001" class="form-control " id="dm_referal_bonus" placeholder="100" value="{{ $dm_referal_bonus  ?? ''}}" {{ $dm_referal_status ? 'required' : 'readonly' }}>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php($incentive_status = Helpers::get_business_settings('incentive_status'))
                        @php($incentive_profit_share_ratio = Helpers::get_business_settings('incentive_profit_share_ratio'))
                        @php($incentive_min_bonus_value = Helpers::get_business_settings('incentive_min_bonus_value'))

                        <div class="card mt-20 card-container">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between gap-2 flex-sm-nowrap flex-wrap">
                                    <div>
                                        <h4 class="mb-1">{{'Configuración del acelerador de incentivos y recompensas'}}</h4>
                                        <p class="fs-12 m-0">{{'Administre la proporción de participación en las ganancias para bonificaciones de conductores y misiones.'}}</p>
                                    </div>
                                    <div class="d-flex flex-sm-nowrap flex-wrap justify-content-end justify-content-end align-items-center gap-3">
                                        <div class="view_toggle_btn fz--14px info-dark cursor-pointer text-decoration-underline font-semibold d-flex align-items-center gap-1">
                                            {{ 'vista' }}
                                            <i class="tio-chevron-down fs-22"></i>
                                        </div>
                                        <div class="mb-0">
                                            <label class="toggle-switch toggle-switch-sm mb-0">
                                                <input type="checkbox" data-type="toggle" class="status toggle-switch-input" name="incentive_status" id="incentive_status" value="1" {{ $incentive_status == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label text mb-0">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-details-body {{ !$incentive_status ? 'd-none' : '' }}">
                                    <div class="bg-light2 rounded p-xxl-20 p-3 mt-20">
                                        <div class="row g-3">
                                            <div class="col-sm-6 col-lg-6">
                                                <div class="form-group mb-0">
                                                    <label class="form-label text-capitalize" for="incentive_profit_share_ratio">
                                                        <div class="d-flex align-items-center">
                                                            <span class="line--limit-1 flex-grow pr-1">{{ 'Relación de participación en las ganancias (%)' }} </span>
                                                            <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Porcentaje del beneficio neto (Comisiones - Descuentos) destinado a incentivos a los conductores. Recomendado: 25%.' }}">
                                                                <i class="tio-info text-light-gray"></i>
                                                            </span>
                                                        </div>
                                                    </label>
                                                    <input type="number" name="incentive_profit_share_ratio" class="form-control" min="1" max="100" id="incentive_profit_share_ratio" placeholder="25" value="{{ $incentive_profit_share_ratio ?? 25 }}" {{ $incentive_status == 1 ? 'required' : 'readonly' }}>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-lg-6">
                                                <div class="form-group mb-0">
                                                    <label class="form-label text-capitalize" for="incentive_min_bonus_value">
                                                        <div class="d-flex align-items-center">
                                                            <span class="line--limit-1 flex-grow pr-1">{{ 'Valor mínimo de bonificación' }} ({{ \App\CentralLogics\Helpers::currency_symbol() }}) </span>
                                                            <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'La cantidad mínima que debe alcanzar un incentivo para ser mostrado al conductor.' }}">
                                                                <i class="tio-info text-light-gray"></i>
                                                            </span>
                                                        </div>
                                                    </label>
                                                    <input type="number" name="incentive_min_bonus_value" class="form-control" min="0" step="0.01" id="incentive_min_bonus_value" placeholder="2.00" value="{{ $incentive_min_bonus_value ?? 2.00 }}" {{ $incentive_status == 1 ? 'required' : 'readonly' }}>
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
                        <button type="reset" id="reset_btn" class="btn min-w-120px btn--reset location-reload">{{ 'reiniciar' }}</button>
                        <button type="submit" id="submit" class="btn min-w-120px btn--primary">{{ 'guardar información' }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script_2')

    <script>
        "use strict";
        $(document).on('ready', function () {

            function toggleFields(checkbox, fields) {
                if ($(checkbox).is(':checked')) {
                    $(fields).attr('required', true).removeAttr('readonly');
                } else {
                    $(fields).attr('required', false).attr('readonly', true);
                }
            }

            $('#dm_referal_status').on('change', function () {
                toggleFields(this, '#dm_referal_amount, #dm_referal_bonus');
            }).trigger('change');

            $('#dm_loyality_point_status').on('change', function () {
                toggleFields(this, '#dm_loyality_point_per_order, #dm_loyality_point_conversion_rate, #dm_min_loyality_point_to_convert');
            }).trigger('change');

            $('#incentive_status').on('change', function () {
                toggleFields(this, '#incentive_profit_share_ratio, #incentive_min_bonus_value');
            }).trigger('change');

        });

    </script>
@endpush
