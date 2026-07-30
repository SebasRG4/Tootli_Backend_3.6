@extends('layouts.admin.app')

@section('title','Lista de campañas')


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h1 class="page-header-title">
                    <span class="page-header-icon">
                        <img src="{{asset('assets/admin/img/campaign.png')}}" class="w--26" alt="">
                    </span>
                    <span>
                        {{'campaña'}}
                    </span>
                </h1>
                    <a class="btn btn--primary" href="{{route('admin.campaign.add-new', 'item')}}">
                        <i class="tio-add-circle"></i> {{'agregar nueva campaña'}}
                    </a>
            </div>
        </div>
        <!-- End Page Header -->
        <!-- Card -->
        <div class="card">
            <div class="card-header border-0 py-2">
                <div class="search--button-wrapper">
                    <h5 class="card-title">{{'lista de campaña'}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$campaigns->total()}}</span></h5>
                    <form class="search-form min--270">

                        <!-- Search -->
                        <div class="input-group input--group">
                            <input id="datatableSearch" type="search" value="{{ request()?->search ?? null }}" name="search" class="form-control" placeholder="{{ 'Ej: Título de la campaña...' }}" aria-label="{{'buscar aquí'}}">
                            <button type="submit" class="btn btn--secondary">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                        <!-- End Search -->
                    </form>

                    @if(request()->get('search'))
                    <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{'reiniciar'}}</button>
                    @endif


                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40" href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#usersExportDropdown",
                                    "type": "css-animation"
                                }'>
                            <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                        </a>

                        <div id="usersExportDropdown"
                            class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                            <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                            <a id="export-excel" class="dropdown-item" href="
                                {{ route('admin.campaign.item_campaign_export', ['type' => 'excel', request()->getQueryString()]) }}
                                ">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                    alt="Image Description">
                                {{ 'sobresalir' }}
                            </a>
                            <a id="export-csv" class="dropdown-item" href="
                            {{ route('admin.campaign.item_campaign_export', ['type' => 'csv', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                    src="{{ asset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                    alt="Image Description">
                                .{{ 'csv' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Table -->
                <div class="table-responsive datatable-custom">
                    <table id="columnSearchDatatable"
                        class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                        data-hs-datatables-options='{
                            "order": [],
                            "orderCellsTop": true,
                            "paging":false
                        }'>
                        <thead class="thead-light">
                        <tr>
                            <th class="border-0">{{'SL'}}</th>
                            <th class="border-0" >{{'título'}}</th>
                            <th class="border-0" >{{'fecha'}}</th>
                            <th class="border-0" >{{'tiempo'}}</th>
                            <th class="border-0" >{{'precio'}}</th>
                            <th class="border-0 text-center">{{'estado'}}</th>
                            <th class="border-0 text-center">{{'acción'}}</th>
                        </tr>

                        </thead>

                        <tbody id="set-rows">
                        @foreach($campaigns as $key=>$campaign)
                            <tr>
                                <td>{{$key+$campaigns->firstItem()}}</td>
                                <td>
                                    <a href="{{route('admin.campaign.view',['item',$campaign->id])}}" title="{{ $campaign['title'] }}" class="d-block text-body" >{{Str::limit($campaign['title'],25,'...')}}</a>
                                </td>
                                <td>
                                    <span class="bg-gradient-light text-dark">{{$campaign->start_date? \App\CentralLogics\Helpers::date_format($campaign?->start_date).'-'.  \App\CentralLogics\Helpers::date_format($campaign?->end_date): 'N/A'}}</span>
                                </td>
                                <td>
                                    <span class="bg-gradient-light text-dark">{{$campaign->start_time? \App\CentralLogics\Helpers::time_format($campaign?->start_time).'-'.  \App\CentralLogics\Helpers::time_format($campaign?->end_time): 'N/A'}}</span>
                                </td>
                                <td>{{\App\CentralLogics\Helpers::format_currency($campaign->price)}}</td>
                                <td>
                                    <div class="d-flex flex-wrap justify-content-center">
                                        <label class="toggle-switch toggle-switch-sm" for="campaignCheckbox{{$campaign->id}}">
                                            <input type="checkbox"  class="toggle-switch-input  dynamic-checkbox"
                                            data-id="campaignCheckbox{{$campaign->id}}"
                                            data-type="status"
                                            data-image-on="{{ asset('assets/admin/img/modal/basic_campaign_on.png') }}"
                                            data-image-off="{{ asset('assets/admin/img/modal/basic_campaign_off.png') }}"
                                            data-title-on="{{ '¡Activando la campaña!' }}"
                                            data-title-off="{{ '¡Apagando la campaña!' }}"
                                            data-text-on="<p>{{ 'Activado el sitio web y las aplicaciones del cliente. ¿Está seguro de que desea activar la campaña que ya está inactiva?' }}</p>"
                                            data-text-off="<p>{{ 'Desactivado el sitio web y las aplicaciones del cliente. ¿Estás seguro de que deseas desactivar la campaña que ya está activa?' }}</p>"
                                            id="campaignCheckbox{{$campaign->id}}" {{$campaign->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </td>

                                <form action="{{route('admin.campaign.status',['item',$campaign['id'],$campaign->status?0:1])}}"
                                    method="get" id="campaignCheckbox{{$campaign->id}}_form">
                                    </form>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn action-btn btn--primary btn-outline-primary"
                                            href="{{route('admin.campaign.edit',['item',$campaign['id']])}}" title="{{'editar campaña'}}"><i class="tio-edit"></i>
                                        </a>
                                        <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                            data-id="campaign-{{$campaign['id']}}" data-message="{{ '¿Quieres eliminar este elemento?' }}" title="{{'eliminar campaña'}}"><i class="tio-delete-outlined"></i>
                                        </a>
                                        <form action="{{route('admin.campaign.delete-item',[$campaign['id']])}}"
                                                    method="post" id="campaign-{{$campaign['id']}}">
                                            @csrf @method('delete')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                    @if(count($campaigns) !== 0)
                    <hr>
                    @endif
                    <div class="page-area">
                        {!! $campaigns->links() !!}
                    </div>
                    @if(count($campaigns) === 0)
                    <div class="empty--data">
                        <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                        <h5>
                            {{'no se encontraron datos'}}
                        </h5>
                    </div>
                    @endif
                <!-- End Table -->
            </div>
        </div>
        <!-- End Card -->
    </div>

@endsection

@push('script_2')
    <script>
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
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
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>

@endpush
