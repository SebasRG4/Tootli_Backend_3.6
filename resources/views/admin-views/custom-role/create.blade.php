@extends('layouts.admin.app')
@section('title','rol personalizado')

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/role.png')}}" class="w--26" alt="">
            </span>
            <span>
                {{'Rol del empleado'}}
            </span>
        </h1>
    </div>
    <!-- End Page Header -->
    <!-- Content Row -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{route('admin.users.custom-role.create')}}" method="post">
                        @csrf
                        @if ($language)
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
                            <div class="form-group lang_form" id="default-form">
                                <label class="input-label" for="exampleFormControlInput1">{{'nombre del rol'}} ({{ 'por defecto' }}) <span class="form-label-secondary text-danger"
                                    data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ 'Requerido.'}}"> *
                                    </span>
                                </label>
                                <input type="text" name="name[]" class="form-control" placeholder="{{'ejemplo de nombre de rol'}}" maxlength="191">
                            </div>
                            <input type="hidden" name="lang[]" value="default">
                                @foreach($language as $lang)
                                    <div class="form-group d-none lang_form" id="{{$lang}}-form">
                                        <label class="input-label" for="exampleFormControlInput1">{{'nombre del rol'}} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" class="form-control" placeholder="{{'ejemplo de nombre de rol'}}" maxlength="191">
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                @endforeach
                            @else
                                <div class="form-group">
                                    <label class="input-label" for="exampleFormControlInput1">{{'nombre del rol'}}</label>
                                    <input type="text" name="name" class="form-control" placeholder="{{'ejemplo de nombre de rol'}}" value="{{old('name')}}" maxlength="191">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif

                        <div class="d-flex flex-wrap select--all-checkes">
                            <h5 class="input-label m-0 text-capitalize">{{'Establecer permiso'}} : </h5>
                            <div class="check-item pb-0 w-auto">
                                <div class="form-group form-check form--check m-0 ml-2">
                                    <input type="checkbox" name="modules[]" value="collect_cash" class="form-check-input" id="select-all">
                                    <label class="form-check-label ml-2" for="select-all">{{ 'seleccionar todo' }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="check--item-wrapper">
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="collect_cash" class="form-check-input"
                                           id="collect_cash">
                                    <label class="form-check-label qcont text-dark" for="collect_cash">{{'recoger efectivo'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="addon" class="form-check-input"
                                           id="addon">
                                    <label class="form-check-label qcont text-dark" for="addon">{{'Añadir'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="attribute" class="form-check-input"
                                           id="attribute">
                                    <label class="form-check-label qcont text-dark" for="attribute">{{'atributo'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="advertisement" class="form-check-input"
                                           id="advertisement">
                                    <label class="form-check-label qcont text-dark" for="advertisement">{{'anuncio'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="banner" class="form-check-input"
                                           id="banner">
                                    <label class="form-check-label qcont text-dark" for="banner">{{'bandera'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="campaign" class="form-check-input"
                                           id="campaign">
                                    <label class="form-check-label qcont text-dark" for="campaign">{{'campaña'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="category" class="form-check-input"
                                           id="category">
                                    <label class="form-check-label qcont text-dark" for="category">{{'categoría'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="coupon" class="form-check-input"
                                           id="coupon">
                                    <label class="form-check-label qcont text-dark" for="coupon">{{'cupón'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="cashback" class="form-check-input"
                                           id="cashback">
                                    <label class="form-check-label qcont text-dark" for="cashback">{{'reembolso'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="customer_management" class="form-check-input"
                                           id="customer_management">
                                    <label class="form-check-label qcont text-dark" for="customer_management">{{'gestión de clientes'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="deliveryman" class="form-check-input"
                                           id="deliveryman">
                                    <label class="form-check-label qcont text-dark" for="deliveryman">{{'Repartidor'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="disbursement" class="form-check-input"
                                           id="disbursement">
                                    <label class="form-check-label qcont text-dark" for="disbursement">{{'desembolso'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="provide_dm_earning" class="form-check-input"
                                           id="provide_dm_earning">
                                    <label class="form-check-label qcont text-dark" for="provide_dm_earning">{{'proporcionar ganancias dm'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="employee" class="form-check-input"
                                           id="employee">
                                    <label class="form-check-label qcont text-dark" for="employee">{{'Empleado'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="item" class="form-check-input"
                                           id="item">
                                    <label class="form-check-label qcont text-dark" for="item">{{'Producto'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="notification" class="form-check-input"
                                           id="notification">
                                    <label class="form-check-label qcont text-dark" for="notification">{{'notificación'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="order" class="form-check-input"
                                           id="order">
                                    <label class="form-check-label qcont text-dark" for="order">{{'Pedido'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="store" class="form-check-input"
                                           id="store">
                                    <label class="form-check-label qcont text-dark" for="store">{{'Negocio'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="report" class="form-check-input"
                                            id="report">
                                    <label class="form-check-label qcont text-dark" for="report">{{'informe'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="settings" class="form-check-input"
                                           id="settings">
                                    <label class="form-check-label qcont text-dark" for="settings">{{'Configuración'}}</label>
                                </div>
                            </div>

                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="withdraw_list" class="form-check-input"
                                            id="withdraw_list">
                                    <label class="form-check-label qcont text-dark" for="withdraw_list">{{'lista de retiro'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="zone" class="form-check-input"
                                           id="zone">
                                    <label class="form-check-label qcont text-dark" for="zone">{{'zona'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="module" class="form-check-input"
                                           id="module_system">
                                    <label class="form-check-label qcont text-dark" for="module_system">{{'módulo'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="parcel" class="form-check-input"
                                           id="parcel">
                                    <label class="form-check-label qcont text-dark" for="parcel">{{'parcela'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="pos" class="form-check-input"
                                           id="pos">
                                    <label class="form-check-label qcont text-dark" for="pos">{{'posición'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="unit" class="form-check-input"
                                           id="unit">
                                    <label class="form-check-label qcont text-dark" for="unit">{{'unidad'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="subscription" class="form-check-input"
                                           id="subscription">
                                    <label class="form-check-label qcont text-dark" for="subscription">{{'suscripción'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="brand" class="form-check-input"
                                           id="brand">
                                    <label class="form-check-label qcont text-dark" for="brand">{{'marca'}}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" name="modules[]" value="common_condition" class="form-check-input"
                                           id="common_condition">
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
                                               id="trip">
                                        <label class="form-check-label qcont text-dark" for="trip">{{'Viaje'}}</label>
                                    </div>
                                </div>
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="promotion" class="form-check-input"
                                               id="promotion">
                                        <label class="form-check-label qcont text-dark" for="promotion">{{'Promoción'}}</label>
                                    </div>
                                </div>
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="vehicle" class="form-check-input"
                                               id="vehicle">
                                        <label class="form-check-label qcont text-dark" for="vehicle">{{'Vehículo'}}</label>
                                    </div>
                                </div>
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="provider" class="form-check-input"
                                               id="provider">
                                        <label class="form-check-label qcont text-dark" for="provider">{{'Proveedor'}}</label>
                                    </div>
                                </div>
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="driver" class="form-check-input"
                                               id="driver">
                                        <label class="form-check-label qcont text-dark" for="driver">{{'Conductor'}}</label>
                                    </div>
                                </div>
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="download_app" class="form-check-input"
                                               id="download_app">
                                        <label class="form-check-label qcont text-dark" for="download_app">{{'Descargar aplicación'}}</label>
                                    </div>
                                </div>
                                <div class="check-item">
                                    <div class="form-group form-check form--check">
                                        <input type="checkbox" name="modules[]" value="rental_report" class="form-check-input"
                                               id="rental_report">
                                        <label class="form-check-label qcont text-dark" for="rental_report">{{'Informe'}}</label>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="btn--container justify-content-end mt-4">
                            <button type="reset" id="reset-btn" class="btn btn--reset">{{'reiniciar'}}</button>
                            <button type="submit" class="btn btn--primary">{{'entregar'}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-0 py-2">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">
                            {{'tabla de roles'}} <span class="badge badge-soft-dark ml-2" id="itemCount">{{$roles->total()}}</span>
                        </h5>
                        <form class="search-form min--200">
                            <!-- Search -->
                            <div class="input-group input--group">
                                <input id="datatableSearch_" type="search" name="search"  value="{{request()?->search}}" class="form-control" placeholder="{{'ej: nombre del rol de búsqueda'}}" aria-label="Search">
                                <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                            </div>
                            <!-- End Search -->
                        </form>
                        @if(request()->get('search'))
                        <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{'reiniciar'}}</button>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="role--table table table-borderless table-thead-bordered table-align-middle card-table"
                               data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true,
                                 "paging":false
                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th scope="col" class="border-0">{{'SL'}}</th>
                                <th scope="col" class="border-0">{{'nombre del rol'}}</th>
                                <th scope="col" class="border-0">{{'Permisos'}}</th>
                                <th scope="col" class="border-0">{{'creado en'}}</th>
                                <th scope="col" class="border-0 text-center">{{'acción'}}</th>
                            </tr>
                            </thead>
                            <tbody  id="set-rows">
                            @foreach($roles as $k=>$role)
                                <tr>
                                    <td scope="row">{{$k+$roles->firstItem()}}</td>
                                    <td title="{{ $role['name'] }}" >{{Str::limit($role['name'],25,'...')}}</td>
                                    <td class="text-capitalize">
                                        @if($role['modules']!=null)
                                            @foreach((array)json_decode($role['modules']) as $key=>$module)
                                                {{translate(str_replace('_',' ',$module))}}

                                                {{  !$loop->last ? ',' : '.'}}
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        <div class="create-date">
                                            {{\App\CentralLogics\Helpers::date_format($role['created_at'])}}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary"
                                                href="{{route('admin.users.custom-role.edit',[$role['id']])}}" title="{{'editar rol'}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="role-{{$role['id']}}" data-message="{{'Quiere eliminar este rol'}}"
                                               title="{{'eliminar rol'}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                        </div>
                                        <form action="{{route('admin.users.custom-role.delete',[$role['id']])}}"
                                                method="post" id="role-{{$role['id']}}">
                                            @csrf @method('delete')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(count($roles) !== 0)
                    <hr>
                    @endif
                    <div class="page-area">
                        {!! $roles->links() !!}
                    </div>
                    @if(count($roles) === 0)
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
</div>
@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/custom-role-index.js"></script>


@endpush

