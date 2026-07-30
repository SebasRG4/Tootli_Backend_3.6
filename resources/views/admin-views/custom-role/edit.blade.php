@extends('layouts.admin.app')
@section('title','Editar rol')
@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Heading -->
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/edit.png')}}" class="w--26" alt="">
            </span>
            <span>
                {{'Rol del empleado'}}
            </span>
        </h1>
    </div>
    <!-- Page Heading -->
    <!-- Content Row -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{route('admin.users.custom-role.update',[$role['id']])}}" method="post">
                        @csrf
                        @if($language)
                            <ul class="nav nav-tabs mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                    href="#"
                                    id="default-link">{{'por defecto'}}</a>
                                </li>
                                @foreach ($language as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                            href="#"
                                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="lang_form" id="default-form">
                                <div class="form-group">
                                    <label class="input-label" for="default_title">{{'nombre del rol'}} ({{'por defecto'}}) <span class="form-label-secondary text-danger"
                                        data-toggle="tooltip" data-placement="right"
                                        data-original-title="{{ 'Requerido.'}}"> *
                                        </span>
                                 </label>
                                    <input type="text" name="name[]" id="default_title" class="form-control" placeholder="{{'ejemplo de nombre de rol'}}" value="{{$role?->getRawOriginal('name')}}"  >
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            </div>
                            @foreach($language as $lang)
                                <?php
                                    if(count($role['translations'])){
                                        $translate = [];
                                        foreach($role['translations'] as $t)
                                        {
                                            if($t->locale == $lang && $t->key=="name"){
                                                $translate[$lang]['name'] = $t->value;
                                            }
                                        }
                                    }
                                ?>
                                <div class="d-none lang_form" id="{{$lang}}-form">
                                    <div class="form-group">
                                        <label class="input-label" for="{{$lang}}_title">{{'nombre del rol'}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" id="{{$lang}}_title" class="form-control" placeholder="{{'ejemplo de nombre de rol'}}" value="{{$translate[$lang]['name']??''}}"  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                </div>
                            @endforeach
                        @else
                        <div id="default-form">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{'nombre del rol'}} ({{ 'por defecto' }})</label>
                                <input type="text" name="name[]" class="form-control" placeholder="{{'ejemplo de nombre de rol'}}" value="{{$role['name']}}" maxlength="100">
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                        </div>
                        @endif

                        <div class="d-flex flex-wrap select--all-checkes">
                            <h5 class="input-label m-0 text-capitalize">{{'Permiso de actualización'}} : </h5>
                            <div class="check-item pb-0 w-auto">
                                <div class="form-group form-check form--check m-0 ml-2">
                                    <input type="checkbox" name="modules[]" value="account" class="form-check-input" id="select-all">
                                    <label class="form-check-label ml-2" for="select-all">{{ 'Seleccionar todo' }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="check--item-wrapper">
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="collect_cash" class="form-check-input"
                                           id="collect_cash"  {{in_array('collect_cash',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="collect_cash">{{'recoger dinero en efectivo'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="addon" class="form-check-input"
                                           id="addon"  {{in_array('addon',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="addon">{{'Añadir'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="attribute" class="form-check-input"
                                           id="attribute"  {{in_array('attribute',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="attribute">{{'atributo'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="advertisement" class="form-check-input"
                                           id="advertisement"  {{in_array('advertisement',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="advertisement">{{'anuncio'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="banner" class="form-check-input"
                                           id="banner"  {{in_array('banner',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="banner">{{'bandera'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="campaign" class="form-check-input"
                                           id="campaign"  {{in_array('campaign',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="campaign">{{'campaña'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="category" class="form-check-input"
                                           id="category"  {{in_array('category',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="category">{{'categoría'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="coupon" class="form-check-input"
                                           id="coupon"  {{in_array('coupon',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="coupon">{{'cupón'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="cashback" class="form-check-input"
                                           id="cashback"  {{in_array('cashback',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="cashback">{{'reembolso'}}</label>
                                </div>
                            </div>

                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="customer_management" class="form-check-input"
                                           id="customer_management"  {{in_array('customer_management',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="customer_management">{{'gestión de clientes'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="deliveryman" class="form-check-input"
                                           id="deliveryman"  {{in_array('deliveryman',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="deliveryman">{{'Repartidor'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="disbursement" class="form-check-input"
                                           id="disbursement"  {{in_array('disbursement',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="disbursement">{{'desembolso'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="provide_dm_earning" class="form-check-input"
                                           id="provide_dm_earning"  {{in_array('provide_dm_earning',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="provide_dm_earning">{{'proporcionar ganancias dm'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="employee" class="form-check-input"
                                           id="employee"  {{in_array('employee',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="employee">{{'Empleado'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="item" class="form-check-input"
                                           id="item"  {{in_array('item',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="item">{{'Producto'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="notification" class="form-check-input"
                                           id="notification"  {{in_array('notification',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="notification">{{'notificación push'}} </label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="order" class="form-check-input"
                                           id="order"  {{in_array('order',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="order">{{'Pedido'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="store" class="form-check-input"
                                           id="store"  {{in_array('store',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="store">{{'Negocio'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="report" class="form-check-input"
                                            id="report"  {{in_array('report',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="report">{{'informe'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="settings" class="form-check-input"
                                           id="settings"  {{in_array('settings',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="settings">{{'Configuración'}}</label>
                                </div>
                            </div>

                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="withdraw_list" class="form-check-input"
                                            id="withdraw_list"  {{in_array('withdraw_list',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="withdraw_list">{{'lista de retiro'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="zone" class="form-check-input"
                                           id="zone"  {{in_array('zone',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="zone">{{'zona'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="module" class="form-check-input"
                                           id="module_system"  {{in_array('module',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="module_system">{{'módulo'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="parcel" class="form-check-input"
                                           id="parcel"  {{in_array('parcel',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="parcel">{{'parcela'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="pos" class="form-check-input"
                                           id="pos"  {{in_array('pos',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="pos">{{'posición'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="unit" class="form-check-input"
                                           id="unit"  {{in_array('unit',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="unit">{{'unidad'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="subscription" class="form-check-input"
                                           id="subscription"  {{in_array('subscription',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="subscription">{{'suscripción'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="brand" class="form-check-input"
                                           id="brand"  {{in_array('brand',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="brand">{{'marca'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="common_condition" class="form-check-input"
                                           id="common_condition"  {{in_array('common_condition',(array)json_decode($role['modules']))?'checked':''}}>
                                    <label class="form-check-label qcont text-dark" for="common_condition">{{'condición común'}}</label>
                                </div>
                            </div>
                        </div>
                        @if (addon_published_status('Rental'))
                            <div class="pt-5">
                                <h4>{{'Rol de alquiler'}}</h4>
                            </div>
                            <div class="check--item-wrapper">
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="trip" class="form-check-input"
                                               id="trip" {{in_array('trip',(array)json_decode($role['modules']))?'checked':''}}>
                                        <label class="form-check-label qcont text-dark" for="trip">{{'Viaje'}}</label>
                                    </div>
                                </div>
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="promotion" class="form-check-input"
                                               id="promotion" {{in_array('promotion',(array)json_decode($role['modules']))?'checked':''}}>
                                        <label class="form-check-label qcont text-dark" for="promotion">{{'Promoción'}}</label>
                                    </div>
                                </div>
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="vehicle" class="form-check-input"
                                               id="vehicle" {{in_array('vehicle',(array)json_decode($role['modules']))?'checked':''}}>
                                        <label class="form-check-label qcont text-dark" for="vehicle">{{'Vehículo'}}</label>
                                    </div>
                                </div>
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="provider" class="form-check-input"
                                               id="provider" {{in_array('provider',(array)json_decode($role['modules']))?'checked':''}}>
                                        <label class="form-check-label qcont text-dark" for="provider">{{'Proveedor'}}</label>
                                    </div>
                                </div>
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="driver" class="form-check-input"
                                               id="driver" {{in_array('driver',(array)json_decode($role['modules']))?'checked':''}}>
                                        <label class="form-check-label qcont text-dark" for="driver">{{'Conductor'}}</label>
                                    </div>
                                </div>
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="download_app" class="form-check-input"
                                               id="download_app" {{in_array('download_app',(array)json_decode($role['modules']))?'checked':''}}>
                                        <label class="form-check-label qcont text-dark" for="download_app">{{'Descargar aplicación'}}</label>
                                    </div>
                                </div>
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="rental_report" class="form-check-input"
                                               id="rental_report" {{in_array('rental_report',(array)json_decode($role['modules']))?'checked':''}}>
                                        <label class="form-check-label qcont text-dark" for="rental_report">{{'Informe'}}</label>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="btn--container justify-content-end mt-4">
                            <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                            <button type="submit" class="btn btn--primary">{{'actualizar'}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/custom-role-index.js"></script>
@endpush
