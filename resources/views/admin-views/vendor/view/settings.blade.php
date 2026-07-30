@extends('layouts.admin.app')

@section('title',$store->name."'s ".'Configuración')

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{asset('assets/admin/css/croppie.css')}}" rel="stylesheet">

@endpush

@section('content')
<div class="content container-fluid">
    @include('admin-views.vendor.view.partials._header',['store'=>$store])
    <!-- Page Heading -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="vendor">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <span class="card-header-icon">
                            <img class="w--22" src="{{asset('assets/admin/img/store.png')}}" alt="">
                        </span>
                        <span class="p-md-1"> {{'configuración de la tienda'}}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group mb-0">
                                <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control" for="item_section">
                                <span class="pr-2">{{'gestionar la configuración del artículo'}}<span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'Cuando está deshabilitada, la función de administración de artículos se ocultará del panel de proveedores y de la aplicación de la tienda.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="{{'mostrar ocultar menú de comida'}}"></span></span>
                                    <input type="checkbox"
                                            data-id="item_section"
                                           data-type="status"
                                           data-image-on="{{ asset('assets/admin/img/status-ons.png') }}"
                                           data-image-off="{{ asset('assets/admin/img/off-danger.png') }}"
                                           data-title-on="{{ '¿Quieres encender?' }}"
                                           data-title-off="{{ '¿Quieres apagar?' }}"
                                           data-text-on="<p>{{ 'Esto habilitará la función para el proveedor.' }}"
                                           data-text-off="<p>{{ 'Esto deshabilitará la función para el proveedor.' }}</p>"
                                           class="toggle-switch-input dynamic-checkbox"
                                           id="item_section"

                                       {{$store->item_section?'checked':''}}>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <form action="{{route('admin.store.toggle-settings',[$store->id,$store->item_section?0:1, 'item_section'])}}"  method="get" id="item_section_form">
                                </form>
                            </div>
                        </div>
                        @if ($store->store_business_model == 'commission')

                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group mb-0">
                                <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control" for="reviews_section">
                                <span class="pr-2">{{'Mostrar reseñas en el panel de proveedores'}}<span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'Cuando está habilitado, los propietarios de tiendas pueden ver los comentarios de los clientes en el panel de proveedores y en la aplicación de la tienda.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="{{'mostrar ocultar menú de comida'}}"></span> </span>
                                    <input type="checkbox"
                                         data-id="reviews_section"
                                           data-type="status"
                                           data-image-on="{{ asset('assets/admin/img/status-ons.png') }}"
                                           data-image-off="{{ asset('assets/admin/img/off-danger.png') }}"
                                           data-title-on="{{ '¿Quieres encender?' }}"
                                           data-title-off="{{ '¿Quieres apagar?' }}"
                                           data-text-on="<p>{{ 'Esto habilitará la función para el proveedor.' }}"
                                           data-text-off="<p>{{ 'Esto deshabilitará la función para el proveedor.' }}</p>"
                                           class="toggle-switch-input dynamic-checkbox"
                                           id="reviews_section"

                                       {{$store->reviews_section?'checked':''}}>
                                    <span class="toggle-switch-label text">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <form action="{{route('admin.store.toggle-settings',[$store->id,$store->reviews_section?0:1, 'reviews_section'])}}"  method="get" id="reviews_section_form">
                                </form>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group mb-0">
                                <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control" for="pos_system">
                                <span class="pr-2 text-capitalize">{{'incluir POS en el panel de proveedores'}}<span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'Activar o Desactivar Punto de Venta (POS) en el panel de la tienda.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="{{'sugerencia del sistema pos'}}"></span></span>
                                    <input type="checkbox"
                                            data-id="pos_system"
                                           data-type="status"
                                           data-image-on="{{ asset('assets/admin/img/status-ons.png') }}"
                                           data-image-off="{{ asset('assets/admin/img/off-danger.png') }}"
                                           data-title-on="{{ '¿Quieres encender?' }}"
                                           data-title-off="{{ '¿Quieres apagar?' }}"
                                           data-text-on="<p>{{ 'Esto habilitará la función para el proveedor.' }}"
                                           data-text-off="<p>{{ 'Esto deshabilitará la función para el proveedor.' }}</p>"
                                           class="toggle-switch-input dynamic-checkbox"
                                           id="pos_system"


                                    {{$store->pos_system?'checked':''}}>

                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                 <form action="{{route('admin.store.toggle-settings',[$store->id,$store->pos_system?0:1, 'pos_system'])}}"  method="get" id="pos_system_form">
                                </form>
                            </div>
                        </div>
                        @endif

                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group mb-0">
                                <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control" for="schedule_order">
                                <span class="pr-2">{{'orden programada'}}<span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'Cuando está habilitado, el propietario de la tienda puede recibir pedidos programados de los clientes.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="{{'sugerencia de orden programada'}}"></span></span>
                                    <input type="checkbox"
                                      data-id="schedule_order"
                                           data-type="status"
                                           data-image-on="{{ asset('assets/admin/img/status-ons.png') }}"
                                           data-image-off="{{ asset('assets/admin/img/off-danger.png') }}"
                                           data-title-on="{{ '¿Quieres encender?' }}"
                                           data-title-off="{{ '¿Quieres apagar?' }}"
                                           data-text-on="<p>{{ 'Esto habilitará la función para el proveedor.' }}"
                                           data-text-off="<p>{{ 'Esto deshabilitará la función para el proveedor.' }}</p>"
                                           class="toggle-switch-input dynamic-checkbox"
                                           id="schedule_order"
                                    {{$store->schedule_order?'checked':''}}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                 <form action="{{route('admin.store.toggle-settings',[$store->id,$store->schedule_order?0:1, 'schedule_order'])}}"  method="get" id="schedule_order_form">
                                     </form>
                            </div>
                        </div>
                        @if ($store->store_business_model == 'commission')

                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group mb-0">
                                <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control" for="self_delivery_system">
                                <span class="pr-2 text-capitalize">{{'Entrega gestionada por la tienda'}}<span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'Cuando esta opción está habilitada, las tiendas deberán entregar los pedidos utilizando sus propios repartidores. Además, las tiendas tendrán la opción de agregar sus propios repartidores desde el panel de la tienda.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="{{'pista de autoentrega'}}"></span></span>
                                    <input type="checkbox"
                                            data-id="self_delivery_system"
                                           data-type="status"
                                           data-image-on="{{ asset('assets/admin/img/status-ons.png') }}"
                                           data-image-off="{{ asset('assets/admin/img/off-danger.png') }}"
                                           data-title-on="{{ '¿Quieres encender?' }}"
                                           data-title-off="{{ '¿Quieres apagar?' }}"
                                           data-text-on="<p>{{ 'Esto habilitará la función para el proveedor.' }}"
                                           data-text-off="<p>{{ 'Esto deshabilitará la función para el proveedor.' }}</p>"
                                           class="toggle-switch-input dynamic-checkbox"
                                           id="self_delivery_system"

                                      {{$store->self_delivery_system?'checked':''}}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                 <form action="{{route('admin.store.toggle-settings',[$store->id,$store->self_delivery_system?0:1, 'self_delivery_system'])}}"  method="get" id="self_delivery_system_form">
                                     </form>
                            </div>
                        </div>
                        @endif
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group mb-0">
                                <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control" for="delivery">
                                    <span class="pr-2">{{'entrega a domicilio'}}<span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'Cuando está habilitado, los clientes pueden realizar pedidos de entrega a domicilio desde esta tienda.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="{{'sugerencia de entrega a domicilio'}}"></span></span>
                                    <input type="checkbox"

                                            data-id="delivery"
                                           data-type="status"
                                           data-image-on="{{ asset('assets/admin/img/status-ons.png') }}"
                                           data-image-off="{{ asset('assets/admin/img/off-danger.png') }}"
                                           data-title-on="{{ '¿Quieres encender?' }}"
                                           data-title-off="{{ '¿Quieres apagar?' }}"
                                           data-text-on="<p>{{ 'Esto habilitará la función para el proveedor.' }}"
                                           data-text-off="<p>{{ 'Esto deshabilitará la función para el proveedor.' }}</p>"
                                           class="toggle-switch-input dynamic-checkbox"
                                           id="delivery"
                                   {{$store->delivery?'checked':''}}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>

                                 <form action="{{route('admin.store.toggle-settings',[$store->id,$store->delivery?0:1, 'delivery'])}}"  method="get" id="delivery_form">
                                     </form>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group mb-0">
                                <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control" for="take_away">
                                <span class="pr-2 text-capitalize">{{'llevar'}}<span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'Cuando está habilitado, los clientes pueden realizar pedidos para llevar en esta tienda.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="{{'quitar pista'}}"></span></span>
                                    <input type="checkbox"

                                         data-id="take_away"
                                           data-type="status"
                                           data-image-on="{{ asset('assets/admin/img/status-ons.png') }}"
                                           data-image-off="{{ asset('assets/admin/img/off-danger.png') }}"
                                           data-title-on="{{ '¿Quieres encender?' }}"
                                           data-title-off="{{ '¿Quieres apagar?' }}"
                                           data-text-on="<p>{{ 'Esto habilitará la función para el proveedor.' }}"
                                           data-text-off="<p>{{ 'Esto deshabilitará la función para el proveedor.' }}</p>"
                                           class="toggle-switch-input dynamic-checkbox"
                                           id="take_away" {{$store->take_away?'checked':''}}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                  <form action="{{route('admin.store.toggle-settings',[$store->id,$store->take_away?0:1, 'take_away'])}}"  method="get" id="take_away_form">
                                     </form>
                            </div>
                        </div>
                        @if ($store->module->module_type == 'grocery' || $store->module->module_type == 'food')
                        <div class="col-xl-4 col-md-4 col-sm-6">
                            <div class="form-group mb-0">
                                <label
                                    class="toggle-switch toggle-switch-sm d-flex justify-content-between border  rounded px-3 form-control"
                                    for="halal_tag_status">
                                <span class="pr-2 d-flex">
                                    <span class="line--limit-1">
                                        {{'estado de la etiqueta halal'}}
                                    </span>
                                    <span data-toggle="tooltip" data-placement="right"
                                          data-original-title='{{'Si está habilitado, los clientes pueden ver la etiqueta halal en el producto.'}}'
                                          class="input-label-secondary">
                                        <img src="{{asset('assets/admin/img/info-circle.svg')}}">
                                    </span>
                                </span>
                                    <input type="checkbox"
                                           data-id="halal_tag_status"
                                           data-type="status"
                                           data-image-on="{{ asset('assets/admin/img/status-ons.png') }}"
                                           data-image-off="{{ asset('assets/admin/img/off-danger.png') }}"
                                           data-title-on="{{ '¿Quieres encender?' }}"
                                           data-title-off="{{ '¿Quieres apagar?' }}"
                                           data-text-on="<p>{{ 'Esto habilitará la función para el proveedor.' }}"
                                           data-text-off="<p>{{ 'Esto deshabilitará la función para el proveedor.' }}</p>"
                                           class="toggle-switch-input dynamic-checkbox"
                                           id="halal_tag_status" {{$store->storeConfig?->halal_tag_status == 1?'checked':''}}>
                                    <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                                </label>
                                <form
                                    action="{{route('admin.store.toggle-settings',[$store->id,$store->storeConfig?->halal_tag_status?0:1, 'halal_tag_status'])}}"
                                    method="get" id="halal_tag_status_form">
                                </form>
                            </div>
                        </div>
                        @endif
                        @if ($store->module->module_type == 'pharmacy')
                        @php($prescription_order_status = \App\Models\BusinessSetting::where('key', 'prescription_order_status')->first())
                        @php($prescription_order_status = $prescription_order_status ? $prescription_order_status->value : 0)
                            @if ($prescription_order_status)
                                <div class="col-sm-6 col-lg-4">
                                    <div class="form-group mb-0">
                                        <label class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control" for="prescription_order">
                                        <span class="pr-2 text-capitalize">{{'orden de prescripción'}}<span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'sugerencia de orden de prescripción'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="{{'sugerencia de orden de prescripción'}}"></span></span>
                                            <input type="checkbox"
                                            data-id="prescription_order"
                                           data-type="status"
                                           data-image-on="{{ asset('assets/admin/img/status-ons.png') }}"
                                           data-image-off="{{ asset('assets/admin/img/off-danger.png') }}"
                                           data-title-on="{{ '¿Quieres encender?' }}"
                                           data-title-off="{{ '¿Quieres apagar?' }}"
                                           data-text-on="<p>{{ 'Esto habilitará la función para el proveedor.' }}"
                                           data-text-off="<p>{{ 'Esto deshabilitará la función para el proveedor.' }}</p>"
                                           class="toggle-switch-input dynamic-checkbox"

                                            id="prescription_order"


                                              {{$store->prescription_order?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <form  action="{{route('admin.store.toggle-settings',[$store->id,$store->prescription_order?0:1, 'prescription_order'])}}"  method="get"  id="prescription_order_form">
                                </form>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="row g-3 mt-3">
                        <form action="{{route('admin.store.update-settings',[$store['id']])}}" method="post"
                            enctype="multipart/form-data" class="col-12">
                            @csrf
                            <div class="row">
                                @if ($toggle_veg_non_veg && config('module.'.$store->module->module_type)['veg_non_veg'])
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-group">
                                            <label class="form-label">{{'tipo de tienda'}}<span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'Define el tipo de comida que esta tienda puede vender.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="{{'sugerencia de orden programada'}}"></span></label>
                                            <div class="resturant-type-group border rounded px-3 d-flex flex-wrap min--h-45px">
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input" type="radio" name="veg_non_veg" value="veg" {{$store->veg && !$store->non_veg?'checked':''}}>
                                                    <span class="form-check-label">
                                                        {{'verduras'}}
                                                    </span>
                                                </label>
                                                <label class="form-check form--check mr-2 mr-md-4">
                                                    <input class="form-check-input" type="radio" name="veg_non_veg" value="non_veg" {{!$store->veg && $store->non_veg?'checked':''}}>
                                                    <span class="form-check-label">
                                                        {{'no vegetariano'}}
                                                    </span>
                                                </label>
                                                <label class="form-check form--check">
                                                    <input class="form-check-input" type="radio" name="veg_non_veg" value="both" {{$store->veg && $store->non_veg?'checked':''}}>
                                                    <span class="form-check-label">
                                                        {{'ambos'}}
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="form-group col-sm-6 col-lg-4">
                                    <label class="input-label text-capitalize">{{'cantidad mínima de pedido'}}<span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'Especifique el monto mínimo de pedido requerido para los clientes al realizar pedidos en esta tienda.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="{{'pista de autoentrega'}}"></span></label>
                                    <input type="number" name="minimum_order" step="0.01" min="0" max="999999999" class="form-control" placeholder="100" value="{{$store->minimum_order>0?$store->minimum_order:''}}">
                                </div>
                                @if (config('module.'.$store->module->module_type)['order_place_to_schedule_interval'])
                                <div class="form-group col-sm-6 col-lg-4">
                                    <label class="input-label text-capitalize" for="maximum_delivery_time">{{'tiempo mínimo de procesamiento'}}<span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'Establezca el tiempo total para procesar el pedido después de la confirmación del mismo.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="{{'Establezca el tiempo total para procesar el pedido después de la confirmación del mismo.'}}"></span></label>
                                    <input type="text" name="order_place_to_schedule_interval" class="form-control" value="{{$store->order_place_to_schedule_interval}}">
                                </div>
                                @endif
                                <div class="form-group col-sm-6 col-lg-4">
                                    <label class="input-label text-capitalize" for="maximum_delivery_time">{{'tiempo de entrega aproximado'}}<span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{'Establecer el tiempo total para entregar los productos.'}}"><img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="{{'Establecer el tiempo total para entregar los productos.'}}"></span></label>
                                    <div class="input-group">
                                        <input type="number" name="minimum_delivery_time" class="form-control" placeholder="Min: 10" value="{{explode('-',$store->delivery_time)[0]}}" data-toggle="tooltip" data-placement="top" data-original-title="{{'tiempo mínimo de entrega'}}">
                                        <input type="number" name="maximum_delivery_time" class="form-control" placeholder="Max: 20" value="{{explode(' ',explode('-',$store->delivery_time)[1])[0]}}" data-toggle="tooltip" data-placement="top" data-original-title="{{'tiempo máximo de entrega'}}">
                                        <select name="delivery_time_type" class="form-control text-capitalize" id="" required>
                                            <option value="min" {{explode(' ',explode('-',$store->delivery_time)[1])[1]=='min'?'selected':''}}>{{'minutos'}}</option>
                                            <option value="hours" {{explode(' ',explode('-',$store->delivery_time)[1])[1]=='hours'?'selected':''}}>{{'horas'}}</option>
                                            <option value="days" {{explode(' ',explode('-',$store->delivery_time)[1])[1]=='days'?'selected':''}}>{{'días'}}</option>
                                        </select>
                                    </div>
                                </div>



                                <div class="col-12">
                                    <div class="justify-content-end btn--container">
                                        <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                                        <button type="submit" class="btn btn--primary">{{'guardar cambios'}}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @if (!config('module.'.$store->module->module_type)['always_open'])
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">
                        <span class="card-header-icon"><i class="tio-clock"></i></span>
                        <span class="p-md-1">{{'horario diario'}}</span>
                    </h5>
                </div>
                <div class="card-body" id="schedule">
                    @include('admin-views.vendor.view.partials._schedule', $store)
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create schedule modal -->

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{'Crear horario'}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="javascript:" method="post" id="add-schedule">
                    @csrf
                    <input type="hidden" name="day" id="day_id_input">
                    <input type="hidden" name="store_id" value="{{$store->id}}">
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">{{'Hora de inicio'}}:</label>
                        <input type="time" class="form-control" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label for="message-text" class="col-form-label">{{'Hora de finalización'}}:</label>
                        <input type="time" class="form-control" name="end_time" required>
                    </div>
                    <button type="submit" class="btn btn-primary">{{'Entregar'}}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script_2')
    <!-- Page level plugins -->
    <script>
        "use strict";
        // Call the dataTables jQuery plugin
        $(document).ready(function () {
            $('#dataTable').DataTable();

            $('#exampleModal').on('show.bs.modal', function (event) {
                let button = $(event.relatedTarget);
                let day_name = button.data('day');
                let day_id = button.data('dayid');
                let modal = $(this);
                modal.find('.modal-title').text('{{'Crear horario para'}} ' + day_name);
                modal.find('.modal-body input[name=day]').val(day_id);
            })

            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('change', function () {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function () {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });


        });


        $(document).on('click', '.delete-schedule', function () {
            let route = $(this).data('url');
            Swal.fire({
                title: '<?php echo e('¿Quieres eliminar este horario?'); ?>',
                text: '<?php echo e('Si selecciona Sí, se eliminará el horario.'); ?>',
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#00868F',
                cancelButtonText: '<?php echo e('No'); ?>',
                confirmButtonText: '<?php echo e('Sí'); ?>',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.get({
                        url: route,
                        beforeSend: function () {
                            $('#loading').show();
                        },
                        success: function (data) {
                            if (data.errors) {
                                for (let i = 0; i < data.errors.length; i++) {
                                    toastr.error(data.errors[i].message, {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                                }
                            } else {
                                $('#schedule').empty().html(data.view);
                                toastr.success('<?php echo e('Programación eliminada exitosamente'); ?>', {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            }
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            toastr.error('<?php echo e('Horario no encontrado'); ?>', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        },
                        complete: function () {
                            $('#loading').hide();
                        },
                    });
                }
            })
        });

        $('#add-schedule').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.store.add-schedule')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
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
                        toastr.success('{{'Programa agregado exitosamente'}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    toastr.error(XMLHttpRequest.responseText, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush
