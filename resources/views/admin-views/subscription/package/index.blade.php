@extends('layouts.admin.app')

@section('title','Suscripción')

@section('subscription_index')
active
@endsection

@push('css_or_js')

@endpush

@section('content')



        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center py-2">
                    <div class="col-sm mb-2 mb-sm-0">
                        <div class="d-flex align-items-center">
                            <img src="{{asset('assets/admin/img/store.png')}}" width="24" alt="img">
                            <div class="w-0 flex-grow pl-2">
                                <h1 class="page-header-title mb-0">{{'Lista de paquetes de suscripción'}} <span class="badge badge-soft-dark ml-2">{{ $packages->total() > 0 ? $packages->total() : ''  }}</span></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (addon_published_status('Rental'))
            <!-- Nav Menus -->
            <ul class="nav nav-tabs border-0 nav--tabs nav--pills mb-4">
                <li class="nav-item">
                    <a class="nav-link {{ request()->module != 1 ? 'active' : '' }}   " href="{{ route('admin.business-settings.subscriptionackage.index')  }}">{{ 'Todo el módulo' }}</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->module == 1 ?'active' : '' }} " href="{{ route('admin.business-settings.subscriptionackage.index',['module'=> true])  }}">{{ 'Módulo de Alquiler' }}</a>
                </li>
            </ul>
            @endif

                @if ($packages->total() > 0 || request()->has('search'))

                <div class="card mb-20">
                    <div class="card-header border-0">
                        <div class="w-100 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <h3 class="text--title card-title">{{ 'Descripción general' }}</h3>
                                <div>{{ 'Ver resumen de todas las ganancias de los paquetes' }}</div>
                            </div>
                            <div class="status-filter-wrap m-0">
                                <div class="statistics-btn-grp">
                                    <label>
                                        <input type="radio" name="statistics" value="all" {{ !request()?->statistics || request()?->statistics == 'all'  ? 'checked' : '' }}  class="order_stats_update  set-filter"
                        data-filter="statistics"
                                data-url="{{ url()->full() }}"  hidden="" >
                                        <span>{{ 'Todo' }}</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="statistics" value="this_year"  {{ request()?->statistics == 'this_year'  ? 'checked' : '' }} class="order_stats_update  set-filter"
                        data-filter="statistics"
                                data-url="{{ url()->full() }}"  hidden="" >
                                        <span>{{ 'este año' }}</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="statistics" value="this_month" {{ request()?->statistics == 'this_month'  ? 'checked' : '' }} class="order_stats_update  set-filter"
                        data-filter="statistics"
                                data-url="{{ url()->full() }}"  hidden="">
                                        <span>{{ 'este mes' }}</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="statistics" value="this_week" {{ request()?->statistics == 'this_week'  ? 'checked' : '' }} class="order_stats_update  set-filter"
                        data-filter="statistics"
                                data-url="{{ url()->full() }}"  hidden="">
                                        <span>{{ 'Esta semana' }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="w-100">
                            <div class="owl-theme owl-carousel created-package-slider">

                                @foreach ($package_sell_count as $key =>  $item)

                                <div class="created-package-slide-item">
                                    @if ($key%2 == 0)
                                    <a class="__card-1 h-100 __bg-1" href="#">
                                        <img src="{{asset('assets/admin/img/plan/basic.png')}}" class="icon" alt="report/new">
                                    @elseif ($key%3 == 1)
                                    <a class="__card-1 h-100 __bg-4" href="#">
                                        <img src="{{asset('assets/admin/img/plan/standard.png')}}" class="icon" alt="report/new">
                                    @else
                                    <a class="__card-1 h-100 __bg-8" href="#">
                                        <img src="{{asset('assets/admin/img/plan/pro.png')}}" class="icon" alt="report/new">
                                    @endif
                                        <h6 class="subtitle">{{ $item->package_name }} </h6>
                                        <h3 class="title">{{  \App\CentralLogics\Helpers::format_currency($item->transactions_sum_paid_amount)  }}</h3>
                                    </a>
                                </div>

                                @endforeach


                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header border-0 px-3 py-2">
                        <div class="search--button-wrapper justify-content-end">
                            <form class="search-form">
                                <!-- Search -->
                                <div class="input--group input-group input-group-merge input-group-flush">
                                    <input type="hidden" value="{{ request()->module }}"  name="module"  >
                                    <input class="form-control" value="{{ request()?->search }}" type="search" placeholder="{{ 'Buscar por nombre' }}" name="search">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                            <!-- Static Export Button -->
                            <div class="hs-unfold">
                                <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn font--sm"
                                    href="javascript:;"
                                    data-hs-unfold-options="{
                                        &quot;target&quot;: &quot;#usersExportDropdown&quot;,
                                        &quot;type&quot;: &quot;css-animation&quot;
                                    }"
                                    data-hs-unfold-target="#usersExportDropdown" data-hs-unfold-invoker="">
                                    <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                                </a>

                                <div id="usersExportDropdown"
                                    class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right hs-unfold-content-initialized hs-unfold-css-animation animated hs-unfold-reverse-y hs-unfold-hidden">

                                    <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                                    <a id="export-excel" class="dropdown-item"
                                        href="{{ route('admin.business-settings.subscriptionackage.packageExport', ['export_type' => 'excel', request()->getQueryString()]) }}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ asset('assets/admin/svg/components/excel.svg') }}"
                                            alt="Image Description">
                                        {{ 'sobresalir' }}
                                    </a>
                                    <a id="export-csv" class="dropdown-item"
                                        href="{{ route('admin.business-settings.subscriptionackage.packageExport', ['export_type' => 'csv', request()->getQueryString()]) }}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ asset('assets/admin/svg/components/placeholder-csv-format.svg') }}"
                                            alt="Image Description">
                                        .{{ 'csv' }}
                                    </a>

                                </div>
                            </div>
                            <a href="{{ route('admin.business-settings.subscriptionackage.create',[ 'module' => request()->module == 1 ? 'rental' : 'all' ]) }}" class="btn btn--primary border-0"><i class="tio-add"></i> {{'Agregar paquete de suscripción'}}</a>
                            <!-- Static Export Button -->
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless middle-align __txt-14px">
                                <thead class="thead-light white--space-false">
                                    <th class="border-top border-bottom text-center"> {{ 'SL' }}</th>
                                    <th class="border-top border-bottom">{{'Nombre del paquete'}}</th>
                                    <th class="border-top border-bottom"><div class="text-title">{{'Precios'}}</div> </th>
                                    <th class="border-top border-bottom">{{'duración' }}</th>
                                    <th class="border-top border-bottom text-center">{{'Suscriptor actual'}} </th>
                                    <th class="border-top border-bottom">{{'estado'}}</th>
                                    <th class="border-top border-bottom text-center">{{'comportamiento'}}</th>
                                </thead>
                                <tbody>

                                    @foreach ($packages as $key => $package)
                                    <tr>
                                        <td class="text-center"> {{$key+$packages->firstItem()}}</td>
                                        <td>
                                            <div title="{{ $package->package_name }}" class="text-title">  <a class="text-dark" href="{{route('admin.business-settings.subscriptionackage.show',[$package['id']])}}">{{ Str::limit($package->package_name, 20, '...')   }}</a> </div>
                                        </td>
                                        <td>
                                            <div class="w--120px text-title text-right pr-5">{{ \App\CentralLogics\Helpers::format_currency($package->price) }}</div>
                                        </td>
                                        <td>
                                            <div class="text-title">{{$package->validity}} {{ 'días' }}</div>
                                        </td>
                                        <td>
                                            <div class="text-title text-center">{{$package->current_subscribers_count ?? 0}}</div>
                                        </td>
                                        <td>
                                                <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$package->id}}">
                                                    <input type="checkbox" data-url="{{route('admin.business-settings.subscriptionackage.status',[$package->id,$package->status?0:1])}}" data-message="{{'¿Quieres activar este paquete?'}}"
                                                    class="toggle-switch-input {{$package->status?'status_change_alert':'status_change_alert_reenable'}}  " data-package_id="{{$package->id}}" data-package_name="{{$package->package_name}}" id="stocksCheckbox{{$package->id}}" {{$package->status?'checked':''}}>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn action-btn btn--primary btn-outline-primary" href="{{ route('admin.business-settings.subscriptionackage.edit',$package->id) }}">
                                                    <i class="tio-edit"></i>
                                                </a>
                                                <a class="btn action-btn btn--warning btn-outline-warning" href="{{route('admin.business-settings.subscriptionackage.show',[$package['id']])}}">
                                                    <i class="tio-invisible"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(count($packages) !== 0)
                        <hr>
                        @endif
                        <div class="page-area">
                            {!! $packages->withQueryString()->links() !!}
                        </div>
                        @if(count($packages) === 0)
                        <div class="empty--data">
                            <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                            <h5>
                                {{'no se encontraron datos'}}
                            </h5>
                        </div>
                        @endif
                    </div>
                </div>

                @else

                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="max-w-542 mx-auto py-sm-5 py-4">
                            <img class="mb-4" src="{{asset('assets/admin/img/empty-subscription.svg')}}" alt="img">
                            <h4 class="mb-3">{{'Crear plan de suscripción'}}</h4>
                            @if ( request()->module == 1)
                                <p class="mb-4">
                                    {{'Agregue nuevos paquetes de suscripción a la lista. Para que los Proveedores tengan más opciones de unirse al negocio para lograr crecimiento y éxito.'}}<br>
                                </p>
                                <a href="{{ route('admin.business-settings.subscriptionackage.create',[ 'module' => 'rental']) }}" class="btn btn--primary border-0"><i class="tio-add"></i> {{'Agregar paquete de suscripción'}}</a>
                            @else
                                <p class="mb-4">
                                    {{'Agregue nuevos paquetes de suscripción a la lista. Para que las Tiendas tengan más opciones de unirse al negocio para lograr crecimiento y éxito.'}}<br>
                                </p>
                                <a href="{{ route('admin.business-settings.subscriptionackage.create',[ 'module' => 'all' ]) }}" class="btn btn--primary border-0"><i class="tio-add"></i> {{'Agregar paquete de suscripción'}}</a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif


    </div>

<!-- Button trigger modal -->
<div class="modal fade" id="status-chage-deactive">
    <div class="modal-dialog modal-dialog-centered status-warning-modal">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <div class="modal-body pb-5 pt-0">
                <div class="max-349 mx-auto mb-20">
                    <div>
                        <div class="text-center">
                            <img src="{{asset('assets/admin/img/subscription-plan/package-status-disable.png')}}" class="mb-20">
                            <h5 class="modal-title" id="toggle-title"></h5>
                        </div>
                        <div class="text-center" id="toggle-message">
                            <h3>{{ '¿Estás seguro de que quieres salir del estado?' }}</h3>
                            @if ( request()->module == 1)
                            <p>{{ 'Estás a punto de desactivar un paquete de suscripción. Tiene la opción de cambiar todos los planes de proveedores o permitir que los proveedores realicen cambios. Elija una de las siguientes opciones para continuar.' }}</p>
                        </div>
                    </div>
                    <div class="btn--container justify-content-center">
                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="{{ 'Los proveedores estarán suscritos hasta que caduque su paquete.' }}"  id="status_change_now" class="btn btn-outline-primary min-w-120" >
                            {{'Permitir que el proveedor cambie'}}
                        </a>


                            @else

                            <p>{{ 'Estás a punto de desactivar un paquete de suscripción. Tiene la opción de cambiar los planes de todas las tiendas o permitir que las tiendas realicen cambios. Elija una de las siguientes opciones para continuar.' }}</p>
                        </div>
                    </div>
                    <div class="btn--container justify-content-center">
                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="{{ 'Las tiendas estarán suscritas hasta que caduque su paquete.' }}"  id="status_change_now" class="btn btn-outline-primary min-w-120" >
                            {{'Permitir que la tienda cambie'}}
                        </a>

                            @endif





                        <button type="button"  class="btn btn--primary min-w-120  shift_package"  data-dismiss="modal" >{{'Cambiar plan'}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Button trigger modal -->
<div class="modal fade" id="status-chage-active">
    <div class="modal-dialog modal-dialog-centered status-warning-modal">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <div class="modal-body pb-5 pt-0">
                <div class="max-349 mx-auto mb-20">
                    <div>
                        <div class="text-center">
                            <img src="{{asset('assets/admin/img/subscription-plan/tick.png')}}" class="mb-20">
                            <h5 class="modal-title" id="toggle-title"></h5>
                        </div>
                        <div class="text-center" id="toggle-message">
                            <h3>{{ '¿Está seguro de que desea activar el estado?' }}</h3>
                            @if ( request()->module == 1)
                            <p>{{ 'Este paquete estará disponible para los proveedores.' }}</p>

                            @else

                            <p>{{ 'Este paquete estará disponible para las tiendas.' }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="button"  class="btn btn--cancel min-w-120 "  data-dismiss="modal" >{{'Cerca'}}</button>
                        <a href="#"  id="status_change_now2" class="btn btn--primary  min-w-120" >
                            {{'Activo ahora'}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shift_package">
    <div class="modal-dialog modal-dialog-centered status-warning-modal">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <form action="{{ route('admin.business-settings.subscriptionackage.switchPlan') }}" method="post">
                @csrf
                <input type="hidden" name="turn_off_package_id" id="turn_off_package_id">
            <div class="modal-body pb-5 pt-0">
                <div class="max-349 mx-auto mb-20">
                    <div>
                        <div class="text-center">
                            <img src="{{asset('assets/admin/img/subscription-plan/package-status-disable.png')}}" class="mb-20">
                            <h5 class="modal-title" id="toggle-title"></h5>
                        </div>
                        <div class="text-center" id="toggle-message">
                            <h3>{{ 'Cambiar el plan de negocios existente.' }}</h3>
                            <div class="form-group">
                                <label class="input-label text-capitalize"> <span  id="package_name"  class="badge badge-secondary"></span> </label>
                                <label class="input-label text-capitalize mt-2 mb-2">{{ 'Seleccionar plan de negocios' }} </label>
                                    <select class="form-control js-select2-custom  " name="package_id">
                                        <option value="" selected > {{'seleccione un paquete' }}</option>
                                        <option value="commission"  > {{'Base de comisiones' }}</option>
                                        @foreach ($packages as $key => $package)
                                        @if ($package->status == 1)
                                            <option class="show_all" id="package_{{ $package->id }}" value="{{ $package->id }}"> {{$package->package_name }}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-center">

                        <button type="submit"  class="btn btn--primary min-w-120 ">{{'Cambiar y girar el estado'}}</button>
                    </div>
                </div>
            </div>
        </form>
        </div>
    </div>
</div>

@endsection

@push('script_2')
<script>
     "use strict";
        $(document).on("click", ".status_change_alert", function () {
            let url = $(this).data('url');
            let package_name = $(this).data('package_name');
            $('.show_all').removeAttr("hidden");
            $('#package_'+$(this).data('package_id')).attr("hidden","true");
            $('#status_change_now').attr("href",url);
            $('#turn_off_package_id').val($(this).data('package_id')) ;
            $('#package_name').text(package_name);

            status_change_alert(url,event)
        });
        $(document).on("click", ".status_change_alert_reenable", function (e) {
            e.preventDefault();
            let url = $(this).data('url');
            $('#status_change_now2').attr("href",url);
            $('#status-chage-active').modal('show');
        });



        $(document).on("click", ".shift_package", function () {
            $('#status-chage-deactive').modal('hide');
            $('#shift_package').modal('show');
        });

        function status_change_alert(url, e) {
            e.preventDefault();
            $('#status-chage-deactive').modal('show');
        }
        $(document).on("ready",  function () {
            $('.js-select2-custom').select2({
                templateResult: function(option) {
                    if(option.element && (option.element).hasAttribute('hidden')){
                        return null;
                    }
                        return option.text;
                    }
            });
        });


        $('.created-package-slider').owlCarousel({
            loop: false,
            margin: 30,
            nav:false,
            autoWidth:true,
        })

</script>
@endpush

