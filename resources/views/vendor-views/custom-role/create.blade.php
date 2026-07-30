@extends('layouts.vendor.app')
@section('title','Crear rol')
@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Heading -->
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/role.png')}}" class="w--26" alt="">
            </span>
            <span>
                {{'rol personalizado'}}
            </span>
        </h1>
    </div>
    <!-- Page Heading -->
    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
    @php($language = $language->value ?? null)
    <!-- Content Row -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
                <span class="card-header-icon">
                    <i class="tio-document-text-outlined"></i>
                </span>
                <span>{{'formulario de rol'}}</span>
            </h5>
        </div>
        <div class="card-body">
            <form action="{{route('vendor.custom-role.create')}}" method="post">
                @csrf
                @if ($language)
                        <ul class="nav nav-tabs mb-4">
                            <li class="nav-item">
                                <a class="nav-link lang_link active"
                                href="#"
                                id="default-link">{{'por defecto'}}</a>
                            </li>
                            @foreach (json_decode($language) as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link"
                                        href="#"
                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                </li>
                            @endforeach
                        </ul>
                            <div class="form-group lang_form" id="default-form">
                                <label class="input-label" for="name">{{'nombre del rol'}} ({{ 'por defecto' }})</label>
                                <input type="text" id="name" name="name[]" class="form-control" placeholder="{{'ejemplo de nombre de rol'}}" maxlength="191"  >
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                                @foreach(json_decode($language) as $lang)
                                    <div class="form-group d-none lang_form" id="{{$lang}}-form">
                                        <label class="input-label" for="name{{$lang}}">{{'nombre del rol'}} ({{strtoupper($lang)}})</label>
                                        <input type="text" id="name{{$lang}}" name="name[]" class="form-control" placeholder="{{'ejemplo de nombre de rol'}}" maxlength="191"  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                            @else
                                <div class="form-group">
                                    <label class="input-label" for="name">{{'nombre del rol'}}</label>
                                    <input type="text" id="name" name="name" class="form-control" placeholder="{{'ejemplo de nombre de rol'}}" value="{{old('name')}}" required maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif

                <div class="bg-light rounded p-3 mb-20">
                    <div class="d-flex align-items-center mr-2 flex-wrap justify-content-between select--all-checkes">
                        <h5 class="input-label m-0 text-capitalize">{{'Permiso sabio del módulo'}}</h5>
                        <div class="check-item p-2 d-flex align-items-center gap-2 pb-0 w-auto cursor-pointer">
                            <label for="select-all" class="fs-14 text-nowrap font-semibold text-title m-0">{{ 'Permiso para todos los módulos' }}</label>
                            <div class="form-group form-check form--check m-0 ml-2">
                                <input type="checkbox" value="" class="form-check-input rounded position-relative rounded" id="select-all">
                            </div>
                        </div>
                    </div>
                    <div class="check--item-wrapper d-inline">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="bg-white rounded select-subwrapper">
                                    <div class="border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap flex-xxl-nowrap gap-1">
                                        <h5 class="m-0 font-medium">{{ 'Gerencia General' }}</h5>
                                        <div class="check-item p-2 d-flex align-items-center gap-2 pb-0 w-auto cursor-pointer">
                                            <label for="select-allsub-1" class="fs-14 text-title m-0">{{ 'Seleccionar todo' }}</label>
                                            <div class="form-group form-check form--check m-0 ml-2">
                                                <input type="checkbox" name="" value="" class="form-check-input rounded position-relative rounded check-all" id="select-allsub-1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex flex-wrap module-wise-gap">
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="dashboard" class="form-check-input rounded"
                                                        id="dashboard">
                                                    <label class="form-check-label qcont text-dark" for="dashboard">{{'Panel de Control'}}</label>
                                                </div>
                                            </div>
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="profile" class="form-check-input rounded"
                                                        id="profile">
                                                    <label class="form-check-label qcont text-dark" for="profile">{{'Perfil'}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white rounded select-subwrapper">
                                    <div class="border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap flex-xxl-nowrap gap-1">
                                        <h5 class="m-0 font-medium">{{ 'Gestión de pedidos' }}</h5>
                                        <div class="check-item p-2 d-flex align-items-center gap-2 pb-0 w-auto cursor-pointer">
                                            <label for="select-allsub-2" class="fs-14 text-title m-0">{{ 'Seleccionar todo' }}</label>
                                            <div class="form-group form-check form--check m-0 ml-2">
                                                <input type="checkbox" name="" value="" class="form-check-input rounded position-relative rounded check-all" id="select-allsub-2">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex flex-wrap module-wise-gap">
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="order" class="form-check-input rounded"
                                                        id="order">
                                                    <label class="form-check-label qcont text-dark" for="order">{{'Todos los pedidos'}}</label>
                                                </div>
                                            </div>
                                            @if (\App\CentralLogics\Helpers::employee_module_permission_check('pos'))
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="pos" class="form-check-input rounded"
                                                        id="pos">
                                                    <label class="form-check-label qcont text-dark" for="pos">{{'Punto de Venta (POS)'}}</label>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white rounded select-subwrapper">
                                    <div class="border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap flex-xxl-nowrap gap-1">
                                        <h5 class="m-0 font-medium">{{ 'Gestión de artículos' }}</h5>
                                        <div class="check-item p-2 d-flex align-items-center gap-2 pb-0 w-auto cursor-pointer">
                                            <label for="select-allsub-3" class="fs-14 text-title m-0">{{ 'Seleccionar todo' }}</label>
                                            <div class="form-group form-check form--check m-0 ml-2">
                                                <input type="checkbox" name="" value="" class="form-check-input rounded position-relative rounded check-all" id="select-allsub-3">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex flex-lg-nowrap flex-wrap module-wise-gap">
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="item" class="form-check-input rounded"
                                                        id="item">
                                                    <label class="form-check-label qcont text-dark" for="item">{{'Elementos'}}</label>
                                                </div>
                                            </div>
                                            @if (config('module.'.\App\CentralLogics\Helpers::get_store_data()->module->module_type)['add_on'])
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="addon" class="form-check-input rounded"
                                                        id="addon">
                                                    <label class="form-check-label qcont text-dark" for="addon">{{'Complementos'}}</label>
                                                </div>
                                            </div>
                                            @endif
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="category" class="form-check-input rounded"
                                                        id="category">
                                                    <label class="form-check-label qcont text-dark" for="category">{{'Categorías'}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white rounded select-subwrapper">
                                    <div class="border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap flex-xxl-nowrap gap-1">
                                        <h5 class="m-0 font-medium">{{ 'Sección de Comercialización' }}</h5>
                                        <div class="check-item p-2 d-flex align-items-center gap-2 pb-0 w-auto cursor-pointer">
                                            <label for="select-allsub-4" class="fs-14 text-title m-0">{{ 'Seleccionar todo' }}</label>
                                            <div class="form-group form-check form--check m-0 ml-2">
                                                <input type="checkbox" name="" value="" class="form-check-input rounded position-relative rounded check-all" id="select-allsub-4">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex flex-lg-nowrap flex-wrap module-wise-gap">
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="campaign" class="form-check-input rounded"
                                                        id="campaign">
                                                    <label class="form-check-label qcont text-dark" for="campaign">{{'Campaña'}}</label>
                                                </div>
                                            </div>
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="coupon" class="form-check-input rounded"
                                                        id="coupon">
                                                    <label class="form-check-label qcont text-dark" for="coupon">{{'Cupón'}}</label>
                                                </div>
                                            </div>
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="banner" class="form-check-input rounded"
                                                        id="banner">
                                                    <label class="form-check-label qcont text-dark" for="banner">{{'Bandera'}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white rounded select-subwrapper">
                                    <div class="border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap flex-xxl-nowrap gap-1">
                                        <h5 class="m-0 font-medium">{{ 'Gestión de publicidad' }}</h5>
                                        <div class="check-item p-2 d-flex align-items-center gap-2 pb-0 w-auto cursor-pointer">
                                            <label for="select-allsub-5" class="fs-14 text-title m-0">{{ 'Seleccionar todo' }}</label>
                                            <div class="form-group form-check form--check m-0 ml-2">
                                                <input type="checkbox" name="" value="" class="form-check-input rounded position-relative rounded check-all" id="select-allsub-5">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex flex-wrap module-wise-gap">
                                                <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="advertisement" class="form-check-input rounded"
                                                        id="advertisement">
                                                    <label class="form-check-label qcont text-dark" for="advertisement">{{'Nuevo anuncio'}}</label>
                                                </div>
                                            </div>
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="advertisement_list" class="form-check-input rounded"
                                                        id="advertisement_list">
                                                    <label class="form-check-label qcont text-dark" for="advertisement_list">{{'Lista de anuncios'}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if (\App\CentralLogics\Helpers::get_store_data()->sub_self_delivery)
                            <div class="col-md-6">
                                <div class="bg-white rounded select-subwrapper">
                                    <div class="border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap flex-xxl-nowrap gap-1">
                                        <h5 class="m-0 font-medium">{{ 'Gestión del repartidor' }}</h5>
                                        <div class="check-item p-2 d-flex align-items-center gap-2 pb-0 w-auto cursor-pointer">
                                            <label for="select-allsub-10" class="fs-14 text-title m-0">{{ 'Seleccionar todo' }}</label>
                                            <div class="form-group form-check form--check m-0 ml-2">
                                                <input type="checkbox" name="" value="" class="form-check-input rounded position-relative rounded check-all" id="select-allsub-10">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex flex-wrap module-wise-gap">
                                                <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="deliveryman" class="form-check-input rounded"
                                                        id="deliveryman">
                                                    <label class="form-check-label qcont text-dark" for="deliveryman">{{'Nuevo repartidor'}}</label>
                                                </div>
                                            </div>
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="deliveryman_list" class="form-check-input rounded"
                                                        id="deliveryman_list">
                                                    <label class="form-check-label qcont text-dark" for="deliveryman_list">{{'Lista de repartidor'}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <div class="bg-white rounded select-subwrapper">
                                    <div class="border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap flex-xxl-nowrap gap-1">
                                        <h5 class="m-0 font-medium">{{ 'Gestión de billetera' }}</h5>
                                        <div class="check-item p-2 d-flex align-items-center gap-2 pb-0 w-auto cursor-pointer">
                                            <label for="select-allsub-6" class="fs-14 text-title m-0">{{ 'Seleccionar todo' }}</label>
                                            <div class="form-group form-check form--check m-0 ml-2">
                                                <input type="checkbox" name="" value="" class="form-check-input rounded position-relative rounded check-all" id="select-allsub-6">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex flex-wrap module-wise-gap">
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="wallet" class="form-check-input rounded"
                                                        id="wallet">
                                                    <label class="form-check-label qcont text-dark" for="wallet">{{'Mi billetera'}}</label>
                                                </div>
                                            </div>
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="wallet_method" class="form-check-input rounded"
                                                        id="wallet_method">
                                                    <label class="form-check-label qcont text-dark" for="wallet_method">{{'Método de billetera'}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white rounded select-subwrapper">
                                    <div class="border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap flex-xxl-nowrap gap-1">
                                        <h5 class="m-0 font-medium">{{ 'Gestión de empleados' }}</h5>
                                        <div class="check-item p-2 d-flex align-items-center gap-2 pb-0 w-auto cursor-pointer">
                                            <label for="select-allsub-7" class="fs-14 text-title m-0">{{ 'Seleccionar todo' }}</label>
                                            <div class="form-group form-check form--check m-0 ml-2">
                                                <input type="checkbox" name="" value="" class="form-check-input rounded position-relative rounded check-all" id="select-allsub-7">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex flex-wrap module-wise-gap">
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="role" class="form-check-input rounded"
                                                        id="role">
                                                    <label class="form-check-label qcont text-dark" for="role">{{'Gestión de roles'}}</label>
                                                </div>
                                            </div>
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="employee" class="form-check-input rounded"
                                                        id="employee">
                                                    <label class="form-check-label qcont text-dark" for="employee">{{'Todos los empleados'}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white rounded select-subwrapper">
                                    <div class="border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap flex-xxl-nowrap gap-1">
                                        <h5 class="m-0 font-medium">{{ 'Sección de informe' }}</h5>
                                        <div class="check-item p-2 d-flex align-items-center gap-2 pb-0 w-auto cursor-pointer">
                                            <label for="select-allsub-8" class="fs-14 text-title m-0">{{ 'Seleccionar todo' }}</label>
                                            <div class="form-group form-check form--check m-0 ml-2">
                                                <input type="checkbox" name="" value="" class="form-check-input rounded position-relative rounded check-all" id="select-allsub-8">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex flex-wrap module-wise-gap">
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="expense_report" class="form-check-input rounded"
                                                            id="expense_report">
                                                    <label class="form-check-label qcont text-dark" for="expense_report">{{'Informe de gastos'}}</label>
                                                </div>
                                            </div>
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="disbursement_report" class="form-check-input rounded"
                                                        id="disbursement_report">
                                                    <label class="form-check-label qcont text-dark" for="disbursement_report">{{'Informe de desembolso'}}</label>
                                                </div>
                                            </div>
                                            <div class="check-item p-2">
                                                <div class="form-group form-check form--check m-0">
                                                    <input type="checkbox" name="modules[]" value="vat_report" class="form-check-input rounded"
                                                        id="vat_report">
                                                    <label class="form-check-label qcont text-dark" for="vat_report">{{'Informe de IVA'}}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 check--item-wrapper d-inline">
                        <div class="bg-white rounded select-subwrapper">
                            <div class="border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap flex-xxl-nowrap gap-1">
                                <h5 class="m-0 font-medium">{{ 'Gestión Empresarial' }}</h5>
                                <div class="check-item p-2 d-flex align-items-center gap-2 pb-0 w-auto cursor-pointer">
                                    <label for="select-allsub-9" class="fs-14 text-title m-0">{{ 'Seleccionar todo' }}</label>
                                    <div class="form-group form-check form--check m-0 ml-2">
                                        <input type="checkbox" name="" value="" class="form-check-input rounded position-relative rounded check-all" id="select-allsub-9">
                                    </div>
                                </div>
                            </div>
                            <div class="p-3">
                                <div class="m-0 row g-3">
                                    <div class="col-xxl-2 col-xl-3 col-lg-3 col-md-3 col-sm-4">
                                        <div class="check-item p-2">
                                            <div class="form-group form-check form--check m-0">
                                                <input type="checkbox" name="modules[]" value="store_setup" class="form-check-input rounded"
                                                        id="store_setup">
                                                <label class="form-check-label text-nowrap qcont text-dark" for="store_setup">{{'Configuración de la tienda'}}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-2 col-xl-3 col-lg-3 col-md-3 col-sm-4">
                                        <div class="check-item p-2">
                                            <div class="form-group form-check form--check m-0">
                                                <input type="checkbox" name="modules[]" value="notification_setup" class="form-check-input rounded"
                                                    id="notification_setup">
                                                <label class="form-check-label text-nowrap qcont text-dark" for="notification_setup">{{'Configuración de notificación'}}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-2 col-xl-3 col-lg-3 col-md-3 col-sm-4">
                                        <div class="check-item p-2">
                                            <div class="form-group form-check form--check m-0">
                                                <input type="checkbox" name="modules[]" value="my_shop" class="form-check-input rounded"
                                                    id="my_shop">
                                                <label class="form-check-label text-nowrap qcont text-dark" for="my_shop">{{'MI tienda'}}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-2 col-xl-3 col-lg-3 col-md-3 col-sm-4">
                                        <div class="check-item p-2">
                                            <div class="form-group form-check form--check m-0">
                                                <input type="checkbox" name="modules[]" value="business_plan" class="form-check-input rounded"
                                                    id="business_plan">
                                                <label class="form-check-label text-nowrap qcont text-dark" for="business_plan">{{'plan de negocios'}}</label>
                                            </div>
                                        </div>
                                    </div>
                                    @if (\App\CentralLogics\Helpers::employee_module_permission_check('reviews'))
                                    <div class="col-xxl-2 col-xl-3 col-lg-3 col-md-3 col-sm-4">
                                            <div class="check-item p-2">
                                            <div class="form-group form-check form--check m-0">
                                                <input type="checkbox" name="modules[]" value="reviews" class="form-check-input rounded"
                                                    id="reviews">
                                                <label class="form-check-label text-nowrap qcont text-dark" for="reviews">{{'Reseñas'}}</label>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="col-xxl-2 col-xl-3 col-lg-3 col-md-3 col-sm-4">
                                        <div class="check-item p-2">
                                            <div class="form-group form-check form--check m-0">
                                                <input type="checkbox" name="modules[]" value="chat" class="form-check-input rounded"
                                                    id="chat">
                                                <label class="form-check-label text-nowrap qcont text-dark" for="chat">{{'Charlar'}}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="btn--container justify-content-end mt-4">
                    <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                    <button type="submit" class="btn btn--primary">{{'entregar'}}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header border-0">
            <div class="search--button-wrapper">
                <h5 class="card-title">
                    <span class="card-header-icon">
                        <i class="tio-document-text-outlined"></i>
                    </span>
                    <span>
                        {{'tabla de roles'}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$rl->total()}}</span>
                    </span>
                </h5>
                <form  class="search-form min--250">
                    <!-- Search -->
                    <div class="input-group input--group">
                        <input  value="{{request()?->search ?? ''}}" type="search" name="search" class="form-control" placeholder="{{'rol de búsqueda'}}" aria-label="{{'buscar'}}">
                        <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                    </div>
                    <!-- End Search -->
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">
                <table id="columnSearchDatatable"
                        class="table table-borderless table-thead-bordered table-align-middle card-table"
                        data-hs-datatables-options='{
                            "order": [],
                            "orderCellsTop": true,
                            "paging":false
                        }'>
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0 w-50px">{{'sl#'}}</th>
                            <th class="border-0 w-50px">{{'nombre del rol'}}</th>
                            <th class="border-0 w-100px">{{'módulos'}}</th>
                            <th class="border-0 w-50px">{{'creado en'}}</th>
                            <th class="border-0 w-50px text-center">{{'acción'}}</th>
                        </tr>
                    </thead>
                    <tbody  id="set-rows">
                    @foreach($rl as $k=>$r)
                        <tr>
                            <td >{{$k+$rl->firstItem()}}</td>
                            <td>{{Str::limit($r['name'],20,'...')}}</td>
                            <td class="text-capitalize">
                                @if($r['modules']!=null)
                                    @foreach((array)json_decode($r['modules']) as $key=>$m)

                                    @if ($m == 'bank_info')
                                    {{'perfil'}}
                                    @else
                                    {{translate(str_replace('_',' ',$m))}}
                                    @endif


                                    {{  !$loop->last ? ',' : '.'}}
                                    @endforeach
                                @endif
                            </td>
                            <td>{{date('d-M-y',strtotime($r['created_at']))}}</td>
                            <td>
                                <div class="btn--container justify-content-center">
                                    <a class="btn action-btn btn--primary btn-outline-primary"
                                        href="{{route('vendor.custom-role.edit',[$r['id']])}}" title="{{'editar rol'}}"><i class="tio-edit"></i>
                                    </a>
                                    <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                       data-id="role-{{$r['id']}}" data-message="{{'Quiere eliminar este rol'}}"
                                         title="{{'eliminar rol'}}"><i class="tio-delete-outlined"></i>
                                    </a>
                                </div>
                                <form action="{{route('vendor.custom-role.delete',[$r['id']])}}"
                                        method="post" id="role-{{$r['id']}}">
                                    @csrf @method('delete')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($rl) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    <table>
                        <tfoot>
                        {!! $rl->links() !!}
                        </tfoot>
                    </table>
                </div>
                @if(count($rl) === 0)
                <div class="empty--data">
                    <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                    <h5>
                        {{'no se encontraron datos'}}
                    </h5>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script>
$(document).ready(function () {
    // Global select all
    $('#select-all').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('input[type="checkbox"][name="modules[]"]').prop('checked', isChecked);
        $('.check-all').prop('checked', isChecked);
    });

    // Group-wise select all
    $('.check-all').on('change', function () {
        const container = $(this).closest('.select-subwrapper');
        const isChecked = $(this).is(':checked');
        container.find('input[type="checkbox"][name="modules[]"]').prop('checked', isChecked);

        // Update global select-all status
        updateGlobalSelectAll();
    });

    // Individual checkbox change
    $('input[type="checkbox"][name="modules[]"]').on('change', function () {
        const container = $(this).closest('.select-subwrapper');
        const allInGroup = container.find('input[type="checkbox"][name="modules[]"]').length;
        const checkedInGroup = container.find('input[type="checkbox"][name="modules[]"]:checked').length;

        // Set group select-all checkbox based on state
        container.find('.check-all').prop('checked', allInGroup === checkedInGroup);

        // Update global select-all status
        updateGlobalSelectAll();
    });

    // Function to update global select-all checkbox
    function updateGlobalSelectAll() {
        const all = $('input[type="checkbox"][name="modules[]"]').length;
        const checked = $('input[type="checkbox"][name="modules[]"]:checked').length;
        $('#select-all').prop('checked', all === checked);
    }
});
</script>
@endpush


