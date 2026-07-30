@extends('layouts.admin.app')

@section('title', 'módulos de negocios')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/module.png')}}" alt="">
                </span>
                <span>
                    {{'lista de módulos de negocios'}}
                </span>
            </h1>
            <div class="text--primary-2 d-flex flex-wrap align-items-center" type="button" data-toggle="modal"
                data-target="#warning-status-modal">
                <strong class="mr-2">{{'Cómo funciona'}}</strong>
                <div class="blinkings">
                    <i class="tio-info-outined"></i>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <!-- Header -->
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper">
                    <form class="search-form mr-auto">
                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch" name="search" type="search" class="form-control"
                                placeholder="{{'ej: módulo de búsqueda por nombre'}}"
                                aria-label="{{'buscar aquí'}}" value="{{request()->query('search')}}">
                            <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                            @if(request()->get('search'))
                                <button type="reset" class="btn btn--primary ml-2 location-reload-to-base"
                                    data-url="{{url()->full()}}">{{'reiniciar'}}</button>
                            @endif
                        </div>
                        <!-- End Search -->
                    </form>




                    <div>
                        <select id="module_type" name="module_type" class="form-control h--45px set-filter"
                            data-url="{{ url()->full() }}" data-filter="module_type">
                            <option value="all" {{ request('module_type') == 'all' ? 'selected' : '' }}>
                                {{ 'todo tipo de módulo' }}</option>
                            @foreach (config('module.module_type') as $key)
                                <option class="" value="{{$key}}" {{ request('module_type') == $key ? 'selected' : '' }}>
                                    {{translate($key)}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40"
                            href="javascript:;" data-hs-unfold-options='{
                                        "target": "#usersExportDropdown",
                                        "type": "css-animation"
                                    }'>
                            <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                            <a id="export-excel" class="dropdown-item"
                                href="{{route('admin.business-settings.module.export', ['type' => 'excel', request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/excel.svg" alt="Image Description">
                                {{ 'sobresalir' }}
                            </a>
                            <a id="export-csv" class="dropdown-item"
                                href="{{route('admin.business-settings.module.export', ['type' => 'csv', request()->getQueryString()])}}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ 'csv' }}
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('admin.business-settings.module.create') }}" class="btn btn--primary">+
                        {{'Agregar nuevo módulo'}}</a>
                    <!-- End Unfold -->
                </div>
                <!-- End Row -->
            </div>
            <!-- End Header -->
            <div class="card-body p-0">
                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable" class="table table-borderless table-thead-bordered table-align-middle"
                        data-hs-datatables-options='{
                                "isResponsive": false,
                                "isShowPaging": false,
                                "paging":false,
                            }'>
                        <thead class="thead-light border-0">
                            <tr>
                                <th class="border-0 pl-4 w--05">{{'SL'}}</th>
                                <th class="border-0 w--1">{{'identificación del módulo'}}</th>
                                <th class="border-0 w--2">{{'nombre'}}</th>
                                <th class="border-0 w--2">{{'tipo de módulo empresarial'}}</th>
                                <th class="border-0 text-center w--2">{{'Pedido'}}</th>
                                <th class="border-0 text-center w--2">{{'proveedores totales'}}</th>
                                <th class="border-0 w--1">{{'estado'}}</th>
                                <th class="border-0 text-center w--15">{{'acción'}}</th>
                            </tr>
                        </thead>

                        <tbody id="table-div">
                            @foreach($modules as $key => $module)
                                @if(($module->module_type == 'rental' && addon_published_status('Rental') == 1) || $module->module_type != 'rental')
                                                    <tr>
                                                        <td class="pl-4">{{$key + $modules->firstItem()}}</td>
                                                        <td>{{$module->id}}</td>
                                                        <td>
                                                            <span class="d-block font-size-sm text-body">
                                                                {{Str::limit(translate($module['module_name']), 20, '...')}}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="d-block font-size-sm text-body text-capitalize">
                                                                {{Str::limit(translate($module['module_type']), 20, '...')}}
                                                            </span>
                                                        </td>
                                <td class="text-center">
                                    <input type="number" class="form-control update-module-order"
                                           data-id="{{$module->id}}" value="{{$module->order}}">
                                </td>
                                                        <td class="text-center">
                                                            {{$module->stores->filter(function ($store) {
                                        return $store->vendor && $store->vendor->status == 1;
                                    })->count()}}
                                                        </td>
                                                        <td>
                                                            <label class="toggle-switch toggle-switch-sm" for="status-{{$module->id}}">
                                                                <input type="checkbox" class="toggle-switch-input dynamic-checkbox"
                                                                    data-id="status-{{$module->id}}" data-type="status"
                                                                    data-image-on='{{asset('assets/admin/img/modal')}}/module-on.png'
                                                                    data-image-off="{{asset('assets/admin/img/modal')}}/module-off.png"
                                                                    data-title-on="{{'¿Quieres activar esto?'}} <strong>{{'¿Módulo Empresarial?'}}</strong>"
                                                                    data-title-off="'{{'Quiero desactivar esto'}} <strong>{{'¿Módulo Empresarial?'}}</strong>"
                                                                    data-text-on="<p>{{'Si activa este módulo empresarial, todas sus características y funcionalidades estarán disponibles y accesibles para todos los usuarios.'}}</p>"
                                                                    data-text-off="<p>{{'Si desactiva este módulo empresarial, todas sus características y funcionalidades quedarán deshabilitadas y ocultas para los usuarios.'}}</p>"
                                                                    class="toggle-switch-input" id="status-{{$module->id}}"
                                                                    {{$module->status ? 'checked' : ''}}>
                                                                <span class="toggle-switch-label">
                                                                    <span class="toggle-switch-indicator"></span>
                                                                </span>
                                                            </label>
                                                            <form
                                                                action="{{route('admin.business-settings.module.status', [$module['id'], $module->status ? 0 : 1])}}"
                                                                method="get" id="status-{{$module->id}}_form">
                                                            </form>
                                                        </td>
                                                        <td>
                                                            <div class="btn--container justify-content-center">
                                                                <a class="btn action-btn btn--primary btn-outline-primary"
                                                                    href="{{route('admin.business-settings.module.edit', [$module['id']])}}"
                                                                    title="{{'editar Módulo de Negocios'}}"><i class="tio-edit"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer page-area pt-0 border-0">
                <!-- Pagination -->
                <div class="d-flex justify-content-center justify-content-sm-end">
                    <!-- Pagination -->
                    {!! $modules->links() !!}
                </div>
                <!-- End Pagination -->
                @if(count($modules) === 0)
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


    <div class="modal fade" id="warning-status-modal">
        <div class="modal-dialog modal-lg warning-status-modal">
            <div class="modal-content">
                <div class="modal-header pb-0">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="single-item-slider owl-carousel">
                    <div class="item">
                        <div class="modal-header pt-0">
                            <h2 class="modal-title">{{'¿Cómo funciona?'}}</h2>
                        </div>
                        <div class="modal-body">
                            <div class="how-it-works">
                                <div class="item">
                                    <img src="{{asset('assets/admin/img/how/how1.png')}}"
                                        class="h-60px object-contain object-left" alt="">
                                    <h2 class="serial">{{ '1' }}</h2>
                                    <h5>{{ 'Crear módulo empresarial' }}</h5>
                                    <p>
                                        {{ 'Para crear un nuevo módulo comercial, vaya a: "Configuración del módulo" → "Agregar módulo comercial".'}}
                                    </p>
                                </div>
                                <div class="item">
                                    <img src="{{asset('assets/admin/img/how/how2.png')}}"
                                        class="h-60px object-contain object-left" alt="">
                                    <h2 class="serial">{{ '2' }}</h2>
                                    <h5>{{ 'Agregar módulo a la zona' }}</h5>
                                    <p>
                                        {{ 'Vaya a "Configuración de zona" → "Lista de zonas comerciales" → "Configuración de zona" → Elija método de pago → Agregar módulo comercial a la zona con parámetros.' }}
                                    </p>
                                </div>
                                <div class="item mw-100">
                                    <img src="{{asset('assets/admin/img/how/how3.png')}}"
                                        class="h-60px object-contain object-left" alt="">
                                    <h2 class="serial">{{ '3' }}</h2>
                                    <h5>{{ 'Crear tiendas' }}</h5>
                                    <p>
                                        {{ 'Seleccione su módulo en la sección Módulo, haga clic en → \'Administración de tienda\' → \'Agregar tienda\' → Agregar detalles de tienda y seleccione Zona para integrar Módulo + Zona + Tienda.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="modal-body py-0">
                            <div class="text-center ">
                                <h3 class="modal-title mb-3">
                                    {{'Vaya a configuración y seleccione el módulo para esta zona'}}</h3>
                                <p class="txt">
                                    {{'De lo contrario, esta zona no funcionará correctamente y mostrará cualquier cosa en esta zona.'}}
                                </p>
                            </div>
                            <img src="{{asset('assets/admin/img/zone-settings-popup-arro.gif')}}" alt="admin/img"
                                class="w-100 h-unset">
                        </div>
                    </div>
                    <div class="item px-xl-4">
                        <div class="d-flex align-items-center">
                            <div class="col-sm-4 text-14">
                                <h4>{{'Cerciorarse'}}</h4>
                                <p>
                                    {{'Todos los detalles de su módulo deben estar bien estructurados. Porque esos detalles se muestran dinámicamente en la página de destino de tu negocio.'}}
                                </p>
                            </div>
                            <div class="col-sm-8">
                                <img src="{{asset('assets/admin/img/module2.png')}}" alt="admin/img" class="w-100 h-unset">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center pb-5">
                    <div class="slide-counter"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================

            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
        $('#search-form').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.business-settings.module.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('.page-area').hide();
                    $('#table-div').html(data.view);
                    $('#itemCount').html(data.count);
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });

        $(document).on('change', '.update-module-order', function () {
            let id = $(this).data('id');
            let order = $(this).val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.business-settings.module.update-order')}}',
                data: {
                    id: id,
                    order: order
                },
                success: function (data) {
                    toastr.success('{{'pedido actualizado exitosamente'}}');
                },
                error: function () {
                    toastr.error('{{'la actualización falló'}}');
                }
            });
        });
    </script>
@endpush