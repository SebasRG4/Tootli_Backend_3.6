@extends('layouts.admin.app')

@section('title','repartidor negado')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title"><i class="tio-filter-list"></i> {{'repartidor negado'}}</h1>
            <div class="page-header-select-wrapper">
                @if(!isset(auth('admin')->user()->zone_id))
                <div class="col-sm-auto min--240">
                    <select name="zone_id" class="form-control js-select2-custom set-filter"
                    data-filter="zone_id"
                            data-url="{{ url()->full() }}">
                        <option value="all">{{ 'Todas las Zonas' }}</option>
                        @foreach(\App\Models\Zone::orderBy('name')->get() as $z)
                            <option
                                value="{{$z['id']}}" {{isset($zone) && $zone->id == $z['id']?'selected':''}}>
                                {{$z['name']}}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
                        <!-- Nav -->
                        <ul class="nav nav-tabs mb-3 border-0 nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.users.delivery-man.new') }}"   aria-disabled="true">{{'repartidor pendiente'}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('admin.users.delivery-man.deny') }}"  aria-disabled="true">{{'repartidor negado'}}</a>
                            </li>
                        </ul>
                        <!-- End Nav -->
                    </div>
                </div>
            </div>
        </div>

        <!-- End Page Header -->
        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2 border-0">
                <div class="search--button-wrapper">
                    <h5 class="card-title">
                        {{'lista de repartidor'}} <span class="badge badge-soft-dark ml-2" id="itemCount">{{$deliveryMen->total()}}</span>
                    </h5>
                    <form class="search-form">
                            <div class="input-group input--group">
                                <input  type="search" name="search_by" class="form-control"
                                placeholder="{{'ej: buscar repartidor, correo electrónico o teléfono'}}" aria-label="{{'buscar'}}" value="{{request()?->search_by}}" >
                                <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                            </div>
                        </form>
                        @if(request()->get('search_by'))
                        <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{'reiniciar'}}</button>
                        @endif
                </div>
            </div>
            <!-- End Header -->

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
                    <tr>
                        <th class="border-0 text-capitalize">{{'SL'}}</th>
                        <th class="border-0 text-capitalize">{{'nombre'}}</th>
                        <th class="border-0 text-capitalize">{{'información de contacto'}}</th>
                        <th class="border-0 text-capitalize">{{'zona'}}</th>
                        <th class="border-0 text-capitalize">{{'tipo de trabajo'}}</th>
                        <th class="border-0 text-capitalize">{{'fecha de solicitud de unión'}}</th>
                        <th class="border-0 text-center text-capitalize">{{'acción'}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($deliveryMen as $key=>$dm)
                        <tr>
                            <td>{{$key+$deliveryMen->firstItem()}}</td>
                            <td>
                                <a class="table-rest-info" href="{{route('admin.users.delivery-man.preview',[$dm['id']])}}">
                                    <img class="onerror-image" data-onerror-image="{{asset('assets/admin/img/160x160/img1.jpg')}}"
                                    src="{{ $dm['image_full_url'] }}"
                                    alt="{{$dm['f_name']}} {{$dm['l_name']}}">
                                    <div class="info">
                                        <h5 class="text-hover-primary mb-0">{{$dm['f_name'].' '.$dm['l_name']}}</h5>

                                    </div>
                                </a>
                            </td>
                            <td>
                                <a class="deco-none" href="tel:{{$dm['phone']}}">{{$dm['phone']}}</a>
                            </td>
                            <td>
                                @if($dm->zone)
                                <label class="text--title font-medium mb-0">{{$dm->zone->name}}</label>
                                @else
                                <label class="text--title font-medium mb-0">{{'zona eliminada'}}</label>
                                @endif
                            </td>
                            <td>
                                {{ $dm->earning ==  1 ?  'persona de libre dedicación'  : 'basado en salario'}}
                            </td>
                            <td>
                                {{\App\CentralLogics\Helpers::time_date_format($dm->created_at )   }}

                            </td>
                            <td>
                                @if($dm->application_status == 'approved')

                                @else
                                <div class="col-md-12">
                                    <div class="btn--container justify-content-center">
                                        <a class="btn action-btn btn--primary btn-outline-primary request-alert" data-toggle="tooltip" data-placement="top"
                                        data-original-title="{{ 'aprobar' }}"
                                            data-url="{{route('admin.users.delivery-man.application',[$dm['id'],'approved'])}}" data-message="{{'quieres aprobar esta solicitud'}}"
                                            href="javascript:"><i class="tio-done font-weight-bold"></i> </a>
                                            <a class="btn action-btn btn--primary btn-outline-primary"  data-toggle="tooltip" data-placement="top" data-original-title="{{ 'editar' }}" href="{{route('admin.users.delivery-man.edit',[$dm['id']])}}" ><i class="tio-edit"></i>
                                            </a>
                                        @if($dm->application_status !='denied')
                                        <a class="btn action-btn btn--danger btn-outline-danger request-alert" data-toggle="tooltip" data-placement="top"
                                        data-original-title="{{ 'denegar' }}" data-url="{{route('admin.users.delivery-man.application',[$dm['id'],'denied'])}}" data-message="{{'quieres rechazar esta solicitud'}}"
                                            href="javascript:"><i class="tio-clear font-weight-bold"></i></a>
                                        @endif
                                    </div>
                                </div>

                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
                @if(count($deliveryMen) !== 0)
                <hr>
                @endif
                <div class="page-area">
                    {!! $deliveryMen->links() !!}
                </div>
                @if(count($deliveryMen) === 0)
                <div class="empty--data">
                    <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                    <h5>
                        {{'no se encontraron datos'}}
                    </h5>
                </div>
                @endif
            <!-- End Table -->
        </div>
        <!-- End Card -->
    </div>

@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/deliveryman-new-denied-list.js"></script>
    <script>
        "use strict";
        function request_alert(url, message) {
            Swal.fire({
                title: '{{'¿está seguro?'}}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{'No'}}',
                confirmButtonText: '{{'Sí'}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }

    </script>
@endpush
