@extends('layouts.admin.app')

@section('title','ventas flash')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/condition.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'configuración de venta flash'}}
                </span>
            </h1>
        </div>
        @php($language=\App\Models\BusinessSetting::where('key','language')->first())
        @php($language = $language->value ?? null)
        @php($defaultLang = str_replace('_', '-', app()->getLocale()))
        <!-- End Page Header -->
        <div class="row g-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.flash-sale.store')}}" method="post">
                            @csrf
                            @if ($language)
                                    <ul class="nav nav-tabs mb-3 border-0">
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
                                    <div class="row">
                                        <div class="col-12">

                                            <div class="lang_form" id="default-form">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="default_title">{{ 'título' }}
                                                        ({{'por defecto'}})
                                                    </label>
                                                    <input type="text" name="title[]" id="default_title"
                                                        class="form-control" maxlength="100" placeholder="{{ 'Ej: nueva venta flash' }}"
                                                    >
                                                </div>
                                                <input type="hidden" name="lang[]" value="default">
                                            </div>
                                        @foreach (json_decode($language) as $lang)
                                            <div class="d-none lang_form"
                                                id="{{ $lang }}-form">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="{{ $lang }}_title">{{ 'título' }}
                                                        ({{ strtoupper($lang) }})
                                                    </label>
                                                    <input type="text" maxlength="100" name="title[]" id="{{ $lang }}_title"
                                                        class="form-control" placeholder="{{ 'Ej: nueva venta flash' }}">
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                            </div>
                                        @endforeach
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="default_title">{{ 'portador de descuento' }}
                                                    <span class="form-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Defina el monto del costo que desea asumir para esta Venta Flash. El monto total del costo debe ser 100.' }}">
                                                        <img src="{{asset('assets/admin/img/info-circle.svg')}}" alt="">
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="row g-3 __bg-F8F9FC-card">
                                                <div class="col-sm-6">
                                                    <label class="form-label">{{ 'administración' }}(%)</label>
                                                <input type="number"  min=".01" step="0.001" max="100" name="admin_discount_percentage"
                                                        value=""
                                                        class="form-control" id="adminDiscount"
                                                        placeholder="{{ 'Ej: 50' }}" required>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="form-label">{{ 'dueño de la tienda' }}(%)</label>
                                                <input type="number"  min=".01" step="0.001" max="100" name="vendor_discount_percentage"
                                                        value=""
                                                        class="form-control" id="storeDiscount"
                                                        placeholder="{{ 'Ej: 50' }}" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="default_title">{{ 'validez' }}
                                                </label>
                                            </div>
                                            <div class="row g-3 __bg-F8F9FC-card">
                                                <div class="col-6">
                                                    <div>
                                                        <label class="input-label" for="title">{{'fecha de inicio'}}</label>
                                                        <input type="datetime-local" id="from" class="form-control" required="" name="start_date">
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div>
                                                        <label class="input-label" for="title">{{'fecha de finalización'}}</label>
                                                        <input type="datetime-local" id="to" class="form-control" required="" name="end_date">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            @endif
                            <div class="btn--container justify-content-end mt-5">
                                <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                                <button type="submit" class="btn btn--primary">{{'entregar'}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">
                                {{'lista de venta flash'}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$flash_sales->total()}}</span>
                            </h5>
                            <form  class="search-form">
                                <!-- Search -->

                                <div class="input-group input--group">
                                    <input id="datatableSearch_" value="{{ request()?->search ?? null }}" type="search" name="search" class="form-control"
                                            placeholder="{{'Ej: título de venta flash'}}" aria-label="Search" >
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                            @if(request()->get('search'))
                            <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{'reiniciar'}}</button>
                            @endif

                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true,
                                 "paging":false
                               }'>
                            <thead class="thead-light">
                            <tr class="text-center">
                                <th class="border-0">{{'SL'}}</th>
                                <th class="border-0">{{'título'}}</th>
                                <th class="border-0">{{'duración'}}</th>
                                <th class="border-0">{{'productos activos'}}</th>
                                <th class="border-0">{{'publicar'}}</th>
                                <th class="border-0">{{'acción'}}</th>
                            </tr>

                            </thead>

                            <tbody id="set-rows">
                            @foreach($flash_sales as $key=>$flash_sale)
                                <tr>
                                    <td class="text-center">
                                        <span  class="mr-3">
                                            {{$key+$flash_sales->firstItem()}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span title="{{$flash_sale['title']}}" class="font-size-sm text-body mr-3">
                                            {{Str::limit($flash_sale['title'],20,'...')}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="bg-gradient-light text-dark">{{$flash_sale->start_date?$flash_sale->start_date->format('d/M/Y'). ' - ' .$flash_sale->end_date->format('d/M/Y'): 'N/A'}}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-size-sm text-body mr-3">
                                            {{ $flash_sale->activeProducts->count()}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <label class="toggle-switch toggle-switch-sm" for="is_publish-{{$flash_sale['id']}}">
                                            <input type="checkbox" class="toggle-switch-input dynamic-checkbox" {{$flash_sale->is_publish?'checked':''}}
                                                    data-id="is_publish-{{$flash_sale['id']}}"
                                                   data-type="status"
                                                   data-image-on='{{asset('assets/admin/img/modal')}}/zone-status-on.png'
                                                   data-image-off="{{asset('assets/admin/img/modal')}}/zone-status-off.png"
                                                   data-title-on="{{'¿Quieres publicar esta venta flash?'}}"
                                                   data-title-off="{{'¿Quieres ocultar esta venta flash?'}}"
                                                   data-text-on="<p>{{'Si publica esta venta flash, los clientes pueden ver todas las tiendas y productos disponibles en esta venta flash desde la aplicación y el sitio web del cliente. otras ventas flash se desactivarán.'}}</p>"
                                                   data-text-off="<p>{{'Si oculta esta venta flash, los clientes NO verán todas las tiendas y productos disponibles en esta venta flash desde la aplicación y el sitio web del cliente.'}}</p>"
                                                   id="is_publish-{{$flash_sale['id']}}">
                                            <span class="toggle-switch-label mx-auto">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        <form action="{{route('admin.flash-sale.publish',[$flash_sale['id'],$flash_sale->is_publish?0:1])}}" method="get" id="is_publish-{{$flash_sale['id']}}_form">
                                        </form>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn p-2 btn--primary btn-outline-primary" href="{{route('admin.flash-sale.add-product',[$flash_sale['id']])}}" title="{{'agregar producto'}}"><i class="tio-add"></i>{{ 'Añadir nuevo producto' }}
                                            </a>
                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.flash-sale.edit',[$flash_sale['id']])}}" title="{{'editar'}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="flash_sale-{{$flash_sale['id']}}" data-message="{{ '¿Quieres eliminar esta venta flash?' }}" title="{{'borrar'}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.flash-sale.delete',[$flash_sale['id']])}}"
                                                    method="post" id="flash_sale-{{$flash_sale['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(count($flash_sales) !== 0)
                    <hr>
                    @endif
                    <div class="page-area">
                        {!! $flash_sales->links() !!}
                    </div>
                    @if(count($flash_sales) === 0)
                    <div class="empty--data">
                        <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                        <h5>
                            {{'no se encontraron datos'}}
                        </h5>
                    </div>
                    @endif
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/flash-sale-index.js"></script>
@endpush
