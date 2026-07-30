@extends('layouts.admin.app')

@section('title', 'configuración de negocios')


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mr-3">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/business.png') }}" class="w--26" alt="">
                </span>
                <span>
                    {{ 'entornos empresariales' }}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.nav-menu')
        </div>
        <!-- End Page Header -->
        <form action="{{ route('admin.business-settings.update-order') }}" method="post" enctype="multipart/form-data">
            @csrf
            @php($name = \App\Models\BusinessSetting::where('key', 'business_name')->first())

            <div class="row g-3">
                @php($default_location = \App\Models\BusinessSetting::where('key', 'default_location')->first())
                @php($default_location = $default_location->value ? json_decode($default_location->value, true) : 0)
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="py-2">
                                <div class="row g-3 align-items-end">
                                    <div class="col-sm-6 col-lg-4">
                                        @php($odc = \App\Models\BusinessSetting::where('key', 'order_delivery_verification')->first())
                                        @php($odc = $odc ? $odc->value : 0)
                                        <div class="form-group mb-0">

                                            <label
                                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                                <span class="pr-1 d-flex align-items-center switch--label">
                                                    <span class="line--limit-1">
                                                        {{ 'verificación de entrega del pedido' }}
                                                    </span>
                                                    <span class="form-label-secondary text-danger d-flex"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Cuando un repartidor llega para la entrega, los Clientes recibirán un código de verificación de 4 dígitos en la sección de detalles del pedido en la Aplicación del Cliente y deberán proporcionar el código al repartidor para verificar el pedido.' }}"><img
                                                            src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="{{ 'alternar variación de orden' }}">
                                                    </span>
                                                </span>
                                                <input type="checkbox"
                                                       data-id="odc1"
                                                       data-type="toggle"
                                                       data-image-on="{{ asset('assets/admin/img/modal/order-delivery-verification-on.png') }}"
                                                       data-image-off="{{ asset('assets/admin/img/modal/order-delivery-verification-off.png') }}"
                                                       data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Verificación de entrega?' }}</strong>"
                                                       data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Verificación de entrega?' }}</strong>"
                                                       data-text-on="<p>{{ 'Si habilita esto, el repartidor debe verificar el pedido durante la entrega mediante un código de verificación de 4 dígitos.' }}</p>"
                                                       data-text-off="<p>{{ 'Si desactivas esto, el repartidor entregará el pedido y actualizará el estado. No necesita verificar el pedido con ningún código.' }}</p>"
                                                       class="status toggle-switch-input dynamic-checkbox-toggle"

                                                       value="1"
                                                    name="odc" id="odc1" {{ $odc == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label text">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-4">
                                        @php($prescription_order_status = \App\Models\BusinessSetting::where('key', 'prescription_order_status')->first())

                                        @php($prescription_order_status = $prescription_order_status ? $prescription_order_status->value : 0)
                                        <div class="form-group mb-0">

                                            <label
                                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                                <span class="pr-1 d-flex align-items-center switch--label">
                                                    <span class="line--limit-1">
                                                        {{ 'Realizar pedido con receta' }}
                                                    </span>
                                                    <span class="form-label-secondary text-danger d-flex"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Con esta función, los clientes pueden realizar un pedido cargando una receta. Las tiendas pueden habilitar/deshabilitar esta función desde la configuración de la tienda si es necesario.' }}"><img
                                                            src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="{{ 'estado del pedido de prescripción' }}"> </span>
                                                </span>
                                                <input type="checkbox"
                                                       data-id="prescription_order_status"
                                                       data-type="toggle"
                                                       data-image-on="{{ asset('assets/admin/img/modal/prescription-on.png') }}"
                                                       data-image-off="{{ asset('assets/admin/img/modal/prescription-off.png') }}"
                                                       data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Realizar pedido con receta?' }}</strong>"
                                                       data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Realizar pedido con receta?' }}</strong>"
                                                       data-text-on="<p>{{ 'Si habilita esto, los clientes pueden realizar un pedido simplemente cargando sus recetas en el módulo de Farmacia desde la aplicación del cliente o el sitio web. Las tiendas pueden habilitar/deshabilitar esta función desde la configuración de la tienda si es necesario.' }}</p>"
                                                       data-text-off="<p>{{ 'Si está deshabilitada, esta función estará oculta en la aplicación del cliente, el sitio web y la aplicación y el panel de la tienda.' }}</p>"
                                                       class="status toggle-switch-input dynamic-checkbox-toggle"
                                                       value="1"
                                                    name="prescription_order_status" id="prescription_order_status"
                                                    {{ $prescription_order_status == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label text">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-4">
                                        @php($home_delivery_status = \App\Models\BusinessSetting::where('key', 'home_delivery_status')->first())

                                        @php($home_delivery_status = $home_delivery_status ? $home_delivery_status->value : 0)
                                        <div class="form-group mb-0">
                                            <label
                                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                                <span class="pr-1 d-flex align-items-center switch--label">
                                                    <span class="line--limit-1">
                                                        {{ 'Entrega a domicilio' }}
                                                    </span>
                                                    <span class="form-label-secondary text-danger d-flex"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Si habilita esta función, los clientes pueden elegir "Entrega a domicilio" y recibir el producto en su ubicación preferida.' }}"><img
                                                            src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="{{ 'Entrega a domicilio' }}"></span>
                                                </span>
                                                <input type="checkbox"
                                                       data-id="home_delivery"
                                                       data-type="toggle"
                                                       data-image-on="{{ asset('assets/admin/img/modal/home-delivery-on.png') }}"
                                                       data-image-off="{{ asset('assets/admin/img/modal/home-delivery-off.png') }}"
                                                       data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Entrega a domicilio?' }}</strong>"
                                                       data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Entrega a domicilio?' }}</strong>"
                                                       data-text-on="<p>{{ 'Si habilita esto, los clientes pueden usar la opción de entrega a domicilio durante el pago desde la aplicación del cliente o el sitio web.' }}</p>"
                                                       data-text-off="<p>{{ 'Si desactiva esto, la función de entrega a domicilio se ocultará de la aplicación y el sitio web del cliente.' }}</p>"
                                                       class="status toggle-switch-input dynamic-checkbox-toggle"
                                                       name ="home_delivery_status" id="home_delivery" value="1"
                                               {{ $home_delivery_status == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label text">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-4">
                                        @php($takeaway_status = \App\Models\BusinessSetting::where('key', 'takeaway_status')->first())

                                        @php($takeaway_status = $takeaway_status ? $takeaway_status->value : 0)
                                        <div class="form-group mb-0">
                                            <label
                                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                                <span class="pr-1 d-flex align-items-center switch--label">
                                                    <span class="line--limit-1">
                                                        {{ 'Llevar' }}
                                                    </span>
                                                    <span class="form-label-secondary text-danger d-flex"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Si habilita esta función, los clientes pueden realizar un pedido y solicitar "comida para llevar" o "recogida automática" en las tiendas.' }}"><img
                                                            src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="{{ 'Entrega a domicilio' }}"></span>
                                                </span>
                                                <input type="checkbox"
                                                       data-id="take_away"
                                                       data-type="toggle"
                                                       data-image-on="{{ asset('assets/admin/img/modal/takeaway-on.png') }}"
                                                       data-image-off="{{ asset('assets/admin/img/modal/takeaway-off.png') }}"
                                                       data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿La función para llevar?' }}</strong>"
                                                       data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿La función para llevar?' }}</strong>"
                                                       data-text-on="<p>{{ 'Si habilita esto, los clientes pueden usar la función Para llevar durante el pago desde la aplicación del cliente o el sitio web.' }}</p>"
                                                       data-text-off="<p>{{ 'Si desactiva esto, la función Comida para llevar se ocultará de la aplicación del cliente o del sitio web.' }}</p>"
                                                       class="status toggle-switch-input dynamic-checkbox-toggle"
                                                       name="takeaway_status" value="1" id="take_away" {{ $takeaway_status == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label text">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-lg-4">
                                        @php($schedule_order = \App\Models\BusinessSetting::where('key', 'schedule_order')->first())
                                        @php($schedule_order = $schedule_order ? $schedule_order->value : 0)
                                        <div class="form-group mb-0">
                                            <label
                                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                                <span class="pr-1 d-flex align-items-center switch--label">
                                                    <span class="line--limit-1">
                                                        {{ 'Orden de programación' }}
                                                    </span>
                                                    <span class="form-label-secondary text-danger d-flex"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Con esta función, los clientes pueden elegir su horario de entrega preferido. Los clientes pueden seleccionar un horario de entrega lo antes posible o una fecha específica (dentro de los 2 días posteriores al pedido).'}}"><img
                                                            src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                            alt="{{ 'alternancia de variación del cliente' }}">
                                                    </span>
                                                </span>
                                                <input type="checkbox"
                                                       data-id="schedule_order"
                                                       data-type="toggle"
                                                       data-image-on="{{ asset('assets/admin/img/modal/schedule-on.png') }}"
                                                       data-image-off="{{ asset('assets/admin/img/modal/schedule-off.png') }}"
                                                       data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Orden programada?' }}</strong>"
                                                       data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Orden programada?' }}</strong>"
                                                       data-text-on="<p>{{ 'Si habilita esto, los clientes pueden elegir un cronograma de entrega adecuado durante el proceso de pago.' }}</p>"
                                                       data-text-off="<p>{{ 'Si desactiva esto, la función Pedido programado estará oculta.' }}</p>"
                                                       class="status toggle-switch-input dynamic-checkbox-toggle"
                                                       value="1"
                                                    name="schedule_order" id="schedule_order"
                                                    {{ $schedule_order == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label text">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-lg-4">
                                        @php($schedule_order_slot_duration = \App\Models\BusinessSetting::where('key', 'schedule_order_slot_duration')->first())
                                        @php($schedule_order_slot_duration_time_format = \App\Models\BusinessSetting::where('key', 'schedule_order_slot_duration_time_format')->first())
                                        <div class="form-group mb-0">
                                            <label class="input-label text-capitalize d-flex alig-items-center"
                                                for="schedule_order_slot_duration">


                                                <span class="pr-1 d-flex align-items-center switch--label">
                                                    <span class="line--limit-1">
                                                        {{ 'Intervalo de tiempo para la entrega programada' }}
                                                    </span>
                                                    <span class="form-label-secondary text-danger"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Al activar esta función, los clientes pueden elegir el horario de entrega adecuado según un intervalo de 30 minutos o 1 hora establecido por el administrador.' }}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'Entrega a domicilio' }}"></span>
                                                </span>
                                            </label>
                                            <div class="d-flex">
                                                <input type="number" name="schedule_order_slot_duration" class="form-control mr-3"
                                                id="schedule_order_slot_duration"
                                                value="{{ $schedule_order_slot_duration?->value ? $schedule_order_slot_duration_time_format?->value == 'hour' ? $schedule_order_slot_duration?->value /60 : $schedule_order_slot_duration?->value: 0 }}"
                                                min="0" required>
                                                <select   name="schedule_order_slot_duration_time_format" class="custom-select form-control w-90px">
                                                    <option  value="min" {{ $schedule_order_slot_duration_time_format?->value == 'min'? 'selected' : '' }}>{{ 'mín.' }}</option>
                                                    <option  value="hour" {{ $schedule_order_slot_duration_time_format?->value == 'hour'? 'selected' : ''}}>{{ 'Hora' }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-lg-4 mt-4 mb-4 access_product_approval">
                                        @php($order_confirmation_model = \App\Models\BusinessSetting::where('key', 'order_confirmation_model')->first())
                                        @php($order_confirmation_model = $order_confirmation_model ? $order_confirmation_model->value : 'deliveryman')
                                        <div class="form-group mb-0">
                                            <label class="input-label text-capitalize d-flex alig-items-center">
                                                <span class="line--limit-1">{{ '¿Quién confirmará el pedido?' }}
                                                    <span class="form-label-secondary" data-toggle="tooltip"
                                                          data-placement="right"
                                                          data-original-title="{{ 'Después de realizar un pedido de un cliente, el administrador puede definir quién confirmará el pedido primero: ¿el repartidor o la tienda? Por ejemplo, si elige "Repartidor", el repartidor cercano confirmará el pedido y lo reenviará a la tienda relacionada para procesarlo. Funciona al revés si eliges "Tienda".' }}">
                                                        <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                                    </span>
                                                </span>
                                            </label>
                                            <div class="resturant-type-group border">
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input" type="radio" value="store"
                                                           name="order_confirmation_model" id="order_confirmation_model" {{ $order_confirmation_model == 'store' ? 'checked' : '' }}>
                                                    <span class="form-check-label">
                                                        {{ 'Negocio' }}
                                                    </span>
                                                </label>
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input" type="radio" value="deliveryman"
                                                           name="order_confirmation_model" id="order_confirmation_model2" {{ $order_confirmation_model == 'deliveryman' ? 'checked' : '' }}>
                                                    <span class="form-check-label">
                                                        {{ 'Repartidor' }}
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-lg-4 mt-4 mb-4 access_product_approval">
                                        @php($admin_order_notification = \App\Models\BusinessSetting::where('key', 'admin_order_notification')->first())
                                        @php($admin_order_notification = $admin_order_notification ? $admin_order_notification->value : 0)
                                        <div class="form-group mb-0">

                                            <label
                                                class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                        <span class="pr-1 d-flex align-items-center switch--label">
                                            <span class="line--limit-1">
                                                {{ 'Notificación de pedido para administrador' }}
                                            </span>
                                            <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip"
                                                  data-placement="right"
                                                  data-original-title="{{ 'El administrador recibirá una notificación emergente con sonidos para cualquier pedido realizado por los clientes.' }}"><img
                                                    src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="{{ 'alternancia de variación del cliente' }}"> *
                                            </span>
                                        </span>
                                                <input type="checkbox" data-id="aon1" data-type="toggle"
                                                       data-image-on="{{ asset('assets/admin/img/modal/order-notification-on.png') }}"
                                                       data-image-off="{{ asset('assets/admin/img/modal/order-notification-off.png') }}"
                                                       data-title-on="{{ 'Quiere habilitar' }} <strong>{{ '¿Notificación de pedido para el administrador?' }}</strong>"
                                                       data-title-off="{{ 'Quiere deshabilitar' }} <strong>{{ '¿Notificación de pedido para el administrador?' }}</strong>"
                                                       data-text-on="<p>{{ 'Si habilita esto, el administrador recibirá una notificación por cada pedido realizado.' }}</p>"
                                                       data-text-off="<p>{{ 'Si desactiva esto, el administrador NO recibirá una notificación por cada pedido realizado.' }}</p>"
                                                       class="status toggle-switch-input dynamic-checkbox-toggle" value="1"
                                                       name="admin_order_notification" id="aon1" {{ $admin_order_notification == 1 ? 'checked' : '' }}>
                                                <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-4 mb-4 access_product_approval">
                                        @php($order_notification_type = \App\Models\BusinessSetting::where('key', 'order_notification_type')->first())
                                        <div class="form-group mb-0">
                                            <label class="input-label text-capitalize d-flex alig-items-center"><span
                                                    class="line--limit-1">{{ 'Tipo de notificación de pedido' }}
                                            <span class="form-label-secondary" data-toggle="tooltip"
                                                  data-placement="right"
                                                  data-original-title="{{ 'Para Firebase, se enviará una única notificación en tiempo real al realizar el pedido, sin repetición. Para la opción Manual, las notificaciones aparecerán en intervalos de 10 segundos hasta que se vea el pedido.' }}">
                                                <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                            </span>
                                        </span>
                                            </label>
                                            <div class="resturant-type-group border">
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input" type="radio" value="firebase"
                                                           name="order_notification_type" {{ $order_notification_type ? ($order_notification_type->value == 'firebase' ? 'checked' : '') : '' }}>
                                                    <span class="form-check-label">
                                                {{'base de fuego'}}
                                            </span>
                                                </label>
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input" type="radio" value="manual"
                                                           name="order_notification_type" {{ $order_notification_type ? ($order_notification_type->value == 'manual' ? 'checked' : '') : '' }}>
                                                    <span class="form-check-label">
                                                {{'manual'}}
                                            </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    @php($extra_packaging_data = \App\Models\BusinessSetting::where('key', 'extra_packaging_data')->first()?->value ?? '')
                                    @php($extra_packaging_data =json_decode($extra_packaging_data , true))
                                    <div class="mb-3 access_product_approval">

                                        <label class="mb-2 input-label text-capitalize d-flex alig-items-center" for=""> <img src="{{ asset('assets/admin/img/icon-park_ad-product.png') }}" alt=""
                                            class="card-header-icon align-self-center mr-1">{{ 'Habilitar cargo de embalaje adicional' }}

                                            <span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Después de guardar la información, los vendedores tendrán la opción de ofrecer un cargo de embalaje adicional al cliente.' }}"><img
                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                alt="{{ 'Cargo adicional por embalaje' }}"></span>

                                        </label>
                                        <div class="justify-content-between border form-control">
                                            @foreach (config('module.module_type') as $key => $value)
                                            @if ($value != 'parcel' && $value != 'rental')
                                            <div class="form-check form-check-inline mx-4  ">
                                                <input class="mx-2 form-check-input" type="checkbox" {{  data_get($extra_packaging_data,$value,null) == 1 ? 'checked' :'' }} id="inlineCheckbox{{$key}}" value="1" name="{{ $value }}">
                                                <label class=" form-check-label" for="inlineCheckbox{{$key}}">{{ translate($value) }}</label>
                                            </div>
                                            @endif
                                            @endforeach

                                        </div>
                                    </div>
                                </div>

                                <div class="__bg-F8F9FC-card p-0 mt-4">
                                    @php($admin_free_delivery_status = \App\Models\BusinessSetting::where('key', 'admin_free_delivery_status')->first())

                                    <div class="border-bottom d-flex justify-content-between p-3">
                                        <h4 class="card-title m-0 text--title">{{'Opción de entrega gratuita'}}</h4>
                                        <label class="form-label d-flex justify-content-between text-capitalize mb-1"
                                               for="admin_free_delivery_status">

                                    <span class="toggle-switch toggle-switch-sm pr-sm-3">
                                        <input type="checkbox" data-id="admin_free_delivery_status" data-type="toggle"
                                               data-image-on="{{ asset('assets/admin/img/modal/free-delivery-on.png') }}"
                                               data-image-off="{{ asset('assets/admin/img/modal/free-delivery-off.png') }}"
                                               data-title-on="<strong>{{ '¿Quieres habilitar la opción de entrega gratuita?' }}</strong>"
                                               data-title-off="<strong>{{ '¿Quiere desactivar la opción de envío gratuito?' }}</strong>"
                                               class="status toggle-switch-input dynamic-checkbox-toggle"
                                               name="admin_free_delivery_status" id="admin_free_delivery_status" value="1"
                                            {{ $admin_free_delivery_status?->value ? 'checked' : '' }}>
                                        <span class="toggle-switch-label text mb-0"><span
                                                class="toggle-switch-indicator"></span></span>
                                    </span>
                                        </label>
                                    </div>


                                    <div class="card-body">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-sm-6 col-lg-6">


                                                @php($free_delivery_over = \App\Models\BusinessSetting::where('key', 'free_delivery_over')->first())
                                                @php($admin_free_delivery_option = \App\Models\BusinessSetting::where('key', 'admin_free_delivery_option')->first())
                                                {{--                                        @dd($admin_free_delivery_status?->value)--}}

                                                <div class="form-group mb-0">
                                                    <label
                                                        class="input-label text-capitalize d-flex alig-items-center add_text_mute {{ $admin_free_delivery_status?->value ? '' : 'text-muted' }} "><span
                                                            class="line--limit-1">{{ 'Elija la opción de entrega gratuita' }}
                                                </span>
                                                    </label>
                                                    <div class="resturant-type-group border bg-white">
                                                        <label class="form-check form--check">
                                                            <input class="form-check-input radio-trigger" type="radio" {{ $admin_free_delivery_status?->value ? '' : 'disabled' }}
                                                            value="free_delivery_to_all_store"
                                                                   name="admin_free_delivery_option" {{ $admin_free_delivery_option?->value == 'free_delivery_to_all_store' ? 'checked' : '' }}>
                                                            <span class="form-check-label">
                                                        {{'Establecer entrega gratuita para todas las tiendas.'}}
                                                    </span>
                                                        </label>
                                                        <label class="form-check form--check">
                                                            <input
                                                                class="form-check-input radio-trigger"
                                                                type="radio" {{ $admin_free_delivery_status?->value ? '' : 'disabled' }} value="free_delivery_by_order_amount"
                                                                name="admin_free_delivery_option" {{ $admin_free_delivery_option?->value == 'free_delivery_by_order_amount' || $admin_free_delivery_option?->value == null ? 'checked' : '' }}>
                                                            <span class="form-check-label">
                                                        {{'Establecer criterios específicos'}}
                                                    </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>



                                            <div id="show_free_delivery_over"
                                                 class="col-sm-6 col-lg-6 {{ $admin_free_delivery_option?->value == 'free_delivery_by_order_amount' || $admin_free_delivery_option?->value == null ? '' : 'd-none' }}">
                                                <div class="form-group mb-0">
                                                    <label
                                                        class="form-label d-flex justify-content-between text-capitalize mb-1 add_text_mute {{ $admin_free_delivery_status?->value ? '' : 'text-muted' }} "
                                                        for="">
                                                <span
                                                    class="line--limit-1">{{ 'entrega gratuita sobre' }}
                                                    ({{ \App\CentralLogics\Helpers::currency_symbol() }}) <small
                                                        class="text-danger"><span class="form-label-secondary"
                                                                                  data-toggle="tooltip" data-placement="right"
                                                                                  data-original-title="{{ 'Establezca un valor mínimo de pedido para la entrega gratuita automatizada. Si se excede el monto mínimo, la tarifa de envío se deduce de la comisión del administrador y se agrega a los gastos del administrador.' }}"><img
                                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                alt="{{ 'mensaje de sobreentrega gratis' }}"></span>
                                                        *</small></span>

                                                    </label>

                                                    <input type="number" name="free_delivery_over" class="form-control"
                                                           id="free_delivery_over" placeholder="{{ 'Ej: 10' }}"
                                                           value="{{ $free_delivery_over ? $free_delivery_over->value : 0 }}"
                                                           min="1" step=".01" {{ $admin_free_delivery_option?->value == 'free_delivery_by_order_amount' ? 'required' : '' }} {{ $admin_free_delivery_status?->value ? '' : 'readonly' }}>
                                                </div>
                                            </div>
                                            <div id="show_text_for_all_store_free_delivery"
                                                 class="col-sm-6 col-lg-6 {{ $admin_free_delivery_option?->value == 'free_delivery_to_all_store' ? '' : ' d-none' }}">
                                                <div class="alert fs-13 alert-primary-light text-dark mb-0  mt-md-0 add_text_mute text-muted"
                                                     role="alert">
                                                    <img src="{{ asset('assets/admin/img/lnfo_light.png') }}" alt="">
                                                    {{'El envío gratuito está activo para todas las tiendas. El coste del envío gratuito es'}}
                                                    <strong>{{ 'Administración' }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="btn--container justify-content-end mt-20 footer-sticky-insider">
                                    <button type="reset" class="btn btn--reset">{{ 'reiniciar' }}</button>
                                    <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                        class="btn btn--primary call-demo">{{ 'guardar información' }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

            <div class="mt-4">
                <h4 class="card-title mb-3">
                    <i class="tio-document-text-outlined mr-1"></i>
                    {{'Mensajes de cancelación de pedidos'}}
                </h4>
                <div class="card">
                    <div class="card-body">
                <form action="{{ route('admin.business-settings.order-cancel-reasons.store') }}" method="post">
                    @csrf
                        @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
                        @php($language = $language->value ?? null)
                        @php($defaultLang = str_replace('_', '-', app()->getLocale()))
                        @if ($language)
                        <div class="js-nav-scroller tabs-slide-wrap tabs-slide-space position-relative hs-nav-scroller-horizontal">
                            <ul class="nav nav-tabs tabs-inner nav--tabs mb-4 border-0">
                                <li class="nav-item">
                                    <a class="nav-link lang_link1 active" href="#"
                                        id="default-link1">{{ 'Por defecto' }}</a>
                                </li>
                                @foreach (json_decode($language) as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link1" href="#"
                                            id="{{ $lang }}-link1">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="arrow-area">
                                <div class="button-prev align-items-center">
                                    <button type="button"
                                        class="btn btn-click-prev mr-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                        <i class="tio-chevron-left fs-24"></i>
                                    </button>
                                </div>
                                <div class="button-next align-items-center">
                                    <button type="button"
                                        class="btn btn-click-next ml-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                        <i class="tio-chevron-right fs-24"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-sm-6 lang_form1 default-form1">
                                <label for="order_cancellation" class="form-label">{{ 'Motivo de cancelación del pedido' }}
                                    ({{ 'por defecto' }})</label>
                                <input type="text" class="form-control h--45px" name="reason[]"
                                    id="order_cancellation" placeholder="{{ 'Ej: el artículo está roto' }}">
                                <input type="hidden" name="lang[]" value="default">
                            </div>
                            @if ($language)
                                @foreach (json_decode($language) as $lang)
                                    <div class="col-sm-6 d-none lang_form1" id="{{ $lang }}-form1">
                                        <label for="order_cancellation{{$lang}}" class="form-label">{{ 'Motivo de cancelación del pedido' }}
                                            ({{ strtoupper($lang) }})</label>
                                        <input type="text" class="form-control h--45px" name="reason[]"
                                            id="order_cancellation{{$lang}}" placeholder="{{ 'Ej: el artículo está roto' }}">
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    </div>
                                @endforeach
                            @endif
                            <div class="col-sm-6">
                                <label for="user_type" class="form-label d-flex">
                                    <span class="line--limit-1">{{ 'Tipo de usuario' }} </span>
                                    <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip"
                                        data-placement="right"
                                        data-original-title="{{ 'Cuando este campo está activo, el usuario puede cancelar un pedido con el motivo adecuado.' }}"><img
                                            src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                            alt="{{ 'estado del pedido de prescripción' }}"></span>
                                </label>
                                <select id="user_type" name="user_type" class="form-control h--45px" required>
                                    <option value="">{{ 'seleccione el tipo de usuario' }}</option>
                                    <option value="admin">{{ 'administración' }}</option>
                                    <option value="store">{{ 'Negocio' }}</option>
                                    <option value="customer">{{ 'Cliente' }}</option>
                                    <option value="deliveryman">{{ 'Repartidor' }}</option>
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="exempt_strike_review" id="exempt_strike_review_new" value="1">
                                    <label class="form-check-label" for="exempt_strike_review_new">
                                        {{ 'motivo de cancelación de pedido revisión de huelga exenta' }}
                                        <span class="text-muted d-block small">{{ 'pedido cancelar motivo exento huelga revisión ayuda' }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            {{ '*Los usuarios no pueden cancelar un pedido si el administrador no especifica una causa de cancelación, aunque vean la opción "Cancelar pedido". Por lo tanto, el administrador DEBE proporcionar un motivo de cancelación de pedido adecuado y seleccionar el usuario relacionado.'}}
                       </div>
                        <div class="btn--container justify-content-end mt-3 mb-4">
                            <button type="reset" class="btn btn--reset">{{ 'reiniciar' }}</button>
                            <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                class="btn btn--primary call-demo">{{ 'Entregar' }}</button>
                        </div>
                    </form>
                        <div class="card">
                            <div class="card-body mb-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-md-0 mb-3">
                                    <div class="mx-1">
                                        <h5 class="form-label mb-4">
                                            {{ 'lista de motivos de cancelación de pedido' }}
                                        </h5>
                                    </div>
                                    <div class="my-2">
                                        <select id="type" name="type" class="form-control h--45px set-filter" data-url="{{ url()->full() }}" data-filter="type">
                                            <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>{{ 'todo usuario' }}</option>
                                            <option value="admin" {{ request('type') == 'admin' ? 'selected' : '' }}>{{ 'administración' }}</option>
                                            <option value="store" {{ request('type') == 'store' ? 'selected' : '' }}>{{ 'Negocio' }}</option>
                                            <option value="customer" {{ request('type') == 'customer' ? 'selected' : '' }}>{{ 'Cliente' }}</option>
                                            <option value="deliveryman" {{ request('type') == 'deliveryman' ? 'selected' : '' }}>{{ 'Repartidor' }}</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Table -->
                                <div class="card-body p-0">
                                    <div class="table-responsive datatable-custom">
                                        <table id="columnSearchDatatable"
                                            class="table table-borderless table-thead-bordered table-align-middle"
                                            data-hs-datatables-options='{
                                        "isResponsive": false,
                                        "isShowPaging": false,
                                        "paging":false,
                                    }'>
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="border-0">{{ 'SL' }}</th>
                                                    <th class="border-0">{{ 'Razón' }}</th>
                                                    <th class="border-0">{{ 'tipo' }}</th>
                                                    <th class="border-0">{{ 'columna de revisión de huelga de motivo de cancelación de pedido' }}</th>
                                                    <th class="border-0">{{ 'estado' }}</th>
                                                    <th class="border-0 text-center">{{ 'acción' }}</th>
                                                </tr>
                                            </thead>

                                            <tbody id="table-div">
                                                @foreach ($reasons as $key => $reason)
                                                    <tr>
                                                        <td>{{ $key + $reasons->firstItem() }}</td>

                                                        <td>
                                                            <span class="d-block font-size-sm text-body" title="{{ $reason->reason }}">
                                                                {{ Str::limit($reason->reason, 25, '...') }}
                                                            </span>
                                                        </td>
                                                        <td>{{ Str::title($reason->user_type) }}</td>
                                                        <td>
                                                            @if(($reason->exempt_strike_review ?? false) && $reason->user_type === 'deliveryman')
                                                                <span class="badge badge-soft-secondary">{{ 'motivo de cancelación de pedido revisión de huelga exenta' }}</span>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <label class="toggle-switch toggle-switch-sm"
                                                                for="stocksCheckbox{{ $reason->id }}">
                                                                <input type="checkbox"
                                                                       data-url="{{ route('admin.business-settings.order-cancel-reasons.status', [$reason['id'], $reason->status ? 0 : 1]) }}"
                                                                    class="toggle-switch-input redirect-url"
                                                                    id="stocksCheckbox{{ $reason->id }}"
                                                                    {{ $reason->status ? 'checked' : '' }}>
                                                                <span class="toggle-switch-label">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label>
                                                        </td>

                                                        <td>
                                                            <div class="btn--container justify-content-center">

                                                                <a class="btn btn-sm btn--primary btn-outline-primary action-btn edit-reason"
                                                    title="{{ 'editar' }}"
                                                    data-toggle="modal"
                                                    data-target="#add_update_reason_{{ $reason->id }}"><i
                                                        class="tio-edit"></i>
                                                </a>


                                                                <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                                                    href="javascript:"
                                                                   data-id="order-cancellation-reason-{{ $reason['id'] }}"
                                                                   data-message="{{ 'Si desea eliminar este motivo, confirme su decisión.' }}"
                                                                    title="{{ 'borrar' }}">
                                                                    <i class="tio-delete-outlined"></i>
                                                                </a>
                                                                <form
                                                                    action="{{ route('admin.business-settings.order-cancel-reasons.destroy', $reason['id']) }}"
                                                                    method="post" id="order-cancellation-reason-{{ $reason['id'] }}">
                                                                    @csrf @method('delete')
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- Modal -->
                                                    <div class="modal fade" id="add_update_reason_{{$reason->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="exampleModalLabel">{{ 'motivo de cancelación del pedido' }}
                                                                        {{ 'Actualizar' }}</label></h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                    <form action="{{ route('admin.business-settings.order-cancel-reasons.update') }}" method="post">
                                                                <div class="modal-body">
                                                                        @csrf
                                                                        @method('put')

                                                                        @php($reason=  \App\Models\OrderCancelReason::withoutGlobalScope('translate')->with('translations')->find($reason->id))
                                                                        @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                                                                    @php($language = $language->value ?? null)
                                                                    @php($defaultLang = str_replace('_', '-', app()->getLocale()))
                                                                    <ul class="nav nav-tabs nav--tabs mb-3 border-0">
                                                                        <li class="nav-item">
                                                                            <a class="nav-link update-lang_link add_active active"
                                                                            href="#"
                                                                            id="default-link">{{ 'Por defecto' }}</a>
                                                                        </li>
                                                                        @if($language)
                                                                        @foreach (json_decode($language) as $lang)
                                                                            <li class="nav-item">
                                                                                <a class="nav-link update-lang_link"
                                                                                    href="#"
                                                                                   data-reason-id="{{$reason->id}}"
                                                                                    id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                                            </li>
                                                                        @endforeach
                                                                        @endif
                                                                    </ul>
                                                                        <input type="hidden" name="reason_id"  value="{{$reason->id}}" />

                                                                        <div class="form-group mb-3 add_active_2  update-lang_form" id="default-form_{{$reason->id}}">
                                                                            <label for="reason" class="form-label">{{'Motivo de cancelación del pedido'}} ({{'por defecto'}}) </label>
                                                                            <input id="reason" class="form-control" name='reason[]' value="{{$reason?->getRawOriginal('reason')}}" type="text">
                                                                            <input type="hidden" name="lang1[]" value="default">
                                                                        </div>
                                                                                        @if($language)
                                                                                            @forelse(json_decode($language) as $lang)
                                                                                            <?php
                                                                                                if($reason?->translations){
                                                                                                    $translate = [];
                                                                                                    foreach($reason?->translations as $t)
                                                                                                    {
                                                                                                        if($t->locale == $lang && $t->key=="reason"){
                                                                                                            $translate[$lang]['reason'] = $t->value;
                                                                                                        }
                                                                                                    }
                                                                                                }

                                                                                                ?>
                                                                                                <div class="form-group mb-3 d-none update-lang_form" id="{{$lang}}-langform_{{$reason->id}}">
                                                                                                    <label for="reason{{$lang}}" class="form-label">{{'Motivo de cancelación del pedido'}} ({{strtoupper($lang)}})</label>
                                                                                                    <input id="reason{{$lang}}" class="form-control" name='reason[]' placeholder="{{ 'Ej: el artículo está roto' }}" value="{{ $translate[$lang]['reason'] ?? null }}"  type="text">
                                                                                                    <input type="hidden" name="lang1[]" value="{{$lang}}">
                                                                                                </div>
                                                                                                @empty
                                                                                                @endforelse
                                                                                                @endif

                                                                        <select name="user_type"  class="form-control h--45px"
                                                                            required>
                                                                            <option value="">{{ 'seleccione el tipo de usuario' }}</option>
                                                                            <option {{ $reason->user_type == 'admin' ? 'selected': '' }} value="admin">{{ 'administración' }}</option>
                                                                            <option {{ $reason->user_type == 'store' ? 'selected': '' }} value="store">{{ 'Negocio' }}</option>
                                                                            <option {{ $reason->user_type == 'customer' ? 'selected': '' }} value="customer">{{ 'Cliente' }}</option>
                                                                            <option {{ $reason->user_type == 'deliveryman' ? 'selected': '' }} value="deliveryman">{{ 'Repartidor' }}</option>
                                                                        </select>
                                                                        <div class="form-check mt-3">
                                                                            <input class="form-check-input" type="checkbox" name="exempt_strike_review" id="exempt_strike_review_{{ $reason->id }}" value="1" {{ ($reason->exempt_strike_review ?? false) && $reason->user_type === 'deliveryman' ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="exempt_strike_review_{{ $reason->id }}">
                                                                                {{ 'motivo de cancelación de pedido revisión de huelga exenta' }}
                                                                                <span class="text-muted d-block small">{{ 'pedido cancelar motivo exento huelga revisión ayuda' }}</span>
                                                                            </label>
                                                                        </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ 'Cerca' }}</button>
                                                                    <button type="submit" class="btn btn-primary">{{ 'Guardar cambios' }}</button>
                                                                </div>
                                                                    </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- End Table -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h4 class="card-title mb-3">
                    <i class="tio-time mr-1"></i>
                    {{'ventanas de tiempo de entrega'}}
                </h4>
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.business-settings.delivery-time-window.store') }}" method="post">
                            @csrf
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <label class="form-label">{{ 'nombre' }}</label>
                                    <input type="text" class="form-control h--45px" name="name" required placeholder="{{ 'Ej: mañana, tarde, noche' }}">
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">{{ 'hora de inicio' }}</label>
                                    <input type="time" class="form-control h--45px" name="start_time" required>
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label">{{ 'tiempo final' }}</label>
                                    <input type="time" class="form-control h--45px" name="end_time" required>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end mt-3 mb-4">
                                <button type="reset" class="btn btn--reset">{{ 'reiniciar' }}</button>
                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}" class="btn btn--primary call-demo">{{ 'Entregar' }}</button>
                            </div>
                        </form>

                        @php($time_windows = \App\CentralLogics\Helpers::get_business_settings('delivery_time_windows') ?? [])
                        <div class="card mt-4">
                            <div class="card-body mb-3">
                                <h5 class="form-label mb-4">
                                    {{ 'lista de ventanas de tiempo de entrega' }}
                                </h5>
                                <div class="card-body p-0">
                                    <div class="table-responsive datatable-custom">
                                        <table class="table table-borderless table-thead-bordered table-align-middle">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="border-0">{{ 'SL' }}</th>
                                                    <th class="border-0">{{ 'nombre' }}</th>
                                                    <th class="border-0">{{ 'hora de inicio' }}</th>
                                                    <th class="border-0">{{ 'tiempo final' }}</th>
                                                    <th class="border-0">{{ 'estado' }}</th>
                                                    <th class="border-0 text-center">{{ 'acción' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($time_windows as $key => $window)
                                                    <tr>
                                                        <td>{{ $key + 1 }}</td>
                                                        <td>
                                                            <span class="font-weight-semibold">{{ $window['name'] }}</span>
                                                        </td>
                                                        <td>{{ date('h:i A', strtotime($window['start_time'])) }}</td>
                                                        <td>{{ date('h:i A', strtotime($window['end_time'])) }}</td>
                                                        <td>
                                                            <label class="toggle-switch toggle-switch-sm" for="windowCheckbox{{ $window['id'] }}">
                                                                <input type="checkbox"
                                                                       data-url="{{ route('admin.business-settings.delivery-time-window.status', [$window['id'], isset($window['status']) && $window['status'] ? 0 : 1]) }}"
                                                                       class="toggle-switch-input redirect-url"
                                                                       id="windowCheckbox{{ $window['id'] }}"
                                                                       {{ isset($window['status']) && $window['status'] ? 'checked' : '' }}>
                                                                <span class="toggle-switch-label">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <div class="btn--container justify-content-center">
                                                                <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                                                   title="{{ 'editar' }}"
                                                                   data-toggle="modal"
                                                                   data-target="#edit_window_{{ $window['id'] }}">
                                                                    <i class="tio-edit"></i>
                                                                </a>
                                                                <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert"
                                                                   href="javascript:"
                                                                   data-id="delivery-time-window-{{ $window['id'] }}"
                                                                   data-message="{{ 'Si desea eliminar esta ventana de tiempo, confirme su decisión.' }}"
                                                                   title="{{ 'borrar' }}">
                                                                    <i class="tio-delete-outlined"></i>
                                                                </a>
                                                                <form action="{{ route('admin.business-settings.delivery-time-window.destroy', $window['id']) }}"
                                                                      method="post" id="delivery-time-window-{{ $window['id'] }}">
                                                                    @csrf @method('delete')
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <!-- Edit Modal -->
                                                    <div class="modal fade" id="edit_window_{{ $window['id'] }}" tabindex="-1" role="dialog" aria-labelledby="editWindowModalLabel{{ $window['id'] }}" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="editWindowModalLabel{{ $window['id'] }}">{{ 'editar ventana de tiempo de entrega' }}</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <form action="{{ route('admin.business-settings.delivery-time-window.update') }}" method="post">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="id" value="{{ $window['id'] }}">
                                                                        <div class="form-group mb-3">
                                                                            <label class="form-label">{{ 'nombre' }}</label>
                                                                            <input type="text" class="form-control" name="name" value="{{ $window['name'] }}" required>
                                                                        </div>
                                                                        <div class="form-group mb-3">
                                                                            <label class="form-label">{{ 'hora de inicio' }}</label>
                                                                            <input type="time" class="form-control" name="start_time" value="{{ date('H:i', strtotime($window['start_time'])) }}" required>
                                                                        </div>
                                                                        <div class="form-group mb-3">
                                                                            <label class="form-label">{{ 'tiempo final' }}</label>
                                                                            <input type="time" class="form-control" name="end_time" value="{{ date('H:i', strtotime($window['end_time'])) }}" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ 'Cerca' }}</button>
                                                                        <button type="submit" class="btn btn-primary">{{ 'Guardar cambios' }}</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
    </div>
    <!-- Modal -->


    <div class="modal fade" id="confirmation_modal_free_delivery_by_order_amount" tabindex="-1" role="dialog"
         aria-labelledby="modalLabel" aria-hidden="true">
        <div class=" modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img src="{{asset('assets/admin/img/subscription-plan/package-status-disable.png')}}"
                                     class="mb-20">

                                <h5 class="modal-title"></h5>
                            </div>
                            <div class="text-center">
                                <h3> {{ '¿Quiere “establecer criterios específicos” activos?' }}</h3>
                                <div>
                                    <p>{{ '¿Está seguro de activar "Establecer criterios específicos"? Si activa este cargo de envío no se agregará al pedido cuando el cliente realice un pedido superior al monto de “Entrega gratuita superior”.' }}
                                    </p>
                                </div>
                            </div>



                            <div class="btn--container justify-content-center">
                                <button data-dismiss="modal"
                                        class="btn btn-soft-secondary min-w-120">{{'Cancelar'}}</button>
                                <button data-dismiss="modal" type="button" id="confirmBtn_free_delivery_by_order_amount"
                                        class="btn btn--primary min-w-120">{{'Sí'}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="confirmation_modal_free_delivery_to_all_store" tabindex="-1" role="dialog"
         aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog-centered modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img src="{{asset('assets/admin/img/subscription-plan/package-status-disable.png')}}"
                                     class="mb-20">

                                <h5 class="modal-title"></h5>
                            </div>
                            <div class="text-center">
                                <h3> {{ '¿Quiere una “entrega gratuita para todas las tiendas” activa?' }}</h3>
                                <div>
                                    <p>{{ '¿Estás seguro de activar “Pedido con entrega gratuita para todas las tiendas”? Si activa esto, no se agregará ningún cargo de envío al pedido y se le agregará el costo.' }}
                                    </p>
                                </div>
                            </div>
                            <div class="btn--container justify-content-center">
                                <button data-dismiss="modal"
                                        class="btn btn-soft-secondary min-w-120">{{'Cancelar'}}</button>
                                <button data-dismiss="modal" type="button" id="confirmBtn_free_delivery_to_all_store"
                                        class="btn btn--primary min-w-120">{{'Sí'}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{asset('assets/admin/js/view-pages/business-settings-order-page.js')}}"></script>

    <script>
        "use strict";
        $(document).ready(function () {
            let selectedRadio = null;

            // Function to update field validation based on selected option and status
            function updateFieldValidation() {
                const isEnabled = $('#admin_free_delivery_status').is(':checked');
                const selectedValue = $('input[name="admin_free_delivery_option"]:checked').val();

                if (!isEnabled) {
                    // When disabled, remove validation and make readonly
                    $('#free_delivery_over').removeAttr('required').prop('readonly', true);
                    $('.radio-trigger').prop('disabled', true);
                } else {
                    // When enabled, set validation based on selected radio
                    $('.radio-trigger').prop('disabled', false);

                    if (selectedValue === 'free_delivery_by_order_amount') {
                        $('#show_free_delivery_over').removeClass('d-none');
                        $('#show_text_for_all_store_free_delivery').addClass('d-none');
                        $('#free_delivery_over').prop('readonly', false).prop('required', true);
                    } else if (selectedValue === 'free_delivery_to_all_store') {
                        $('#show_free_delivery_over').addClass('d-none');
                        $('#show_text_for_all_store_free_delivery').removeClass('d-none');
                        $('#free_delivery_over').val('').prop('required', false).prop('readonly', true);
                    }
                }

                // Update text-muted classes
                if (isEnabled) {
                    $('.add_text_mute').removeClass('text-muted');
                } else {
                    $('.add_text_mute').addClass('text-muted');
                }
            }

            // Handle radio button clicks
            $(".radio-trigger").on("click", function (event) {
                event.preventDefault();
                selectedRadio = this;
                let selectedValue = $(this).val();

                if (selectedValue === 'free_delivery_to_all_store') {
                    $("#confirmation_modal_free_delivery_to_all_store").modal("show");
                } else {
                    $("#confirmation_modal_free_delivery_by_order_amount").modal("show");
                }
            });

            // Handle confirmation for "free delivery to all store"
            $("#confirmBtn_free_delivery_to_all_store").on("click", function () {
                if (selectedRadio) {
                    selectedRadio.checked = true;
                    updateFieldValidation();
                }
                $("#confirmation_modal_free_delivery_to_all_store").modal("hide");
            });

            // Handle confirmation for "free delivery by order amount"
            $("#confirmBtn_free_delivery_by_order_amount").on("click", function () {
                if (selectedRadio) {
                    selectedRadio.checked = true;
                    updateFieldValidation();
                }
                $("#confirmation_modal_free_delivery_by_order_amount").modal("hide");
            });

            // Handle toggle switch change - using multiple event listeners to catch all scenarios
            $('#admin_free_delivery_status').on('change', function() {
                // Use setTimeout to ensure this runs after any other handlers
                setTimeout(function() {
                    updateFieldValidation();
                }, 100);
            });

            // Also listen for click events on the toggle
            $('#admin_free_delivery_status').on('click', function() {
                setTimeout(function() {
                    updateFieldValidation();
                }, 100);
            });

            // Listen for changes on the parent toggle switch span (in case the event bubbles from there)
            $('.toggle-switch-input').on('change', function() {
                setTimeout(function() {
                    updateFieldValidation();
                }, 100);
            });

            // Initialize validation state on page load
            setTimeout(function() {
                updateFieldValidation();
            }, 200);
        });
    </script>
@endpush
