@extends('layouts.admin.app')

@section('title','nuevas solicitudes de unión')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title"><i class="tio-filter-list"></i> {{'nuevas solicitudes de unión'}}</h1>
            <div class="page-header-select-wrapper">
                @if(!isset(auth('admin')->user()->zone_id))
                <div class="col-sm-auto min--240">
                    <select name="zone_id" class="form-control js-select2-custom set-filter" data-filter="zone_id"
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
                                <a class="nav-link active" href="{{ route('admin.users.delivery-man.new') }}"   aria-disabled="true">{{'repartidor pendiente'}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.users.delivery-man.deny') }}"  aria-disabled="true">{{'repartidor negado'}}</a>
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
                    <form  class="search-form">
                            <div class="input-group input--group">
                                <input id="datatableSearch_" type="search" id="search" name="search_by" class="form-control"
                                        placeholder="{{'ej: DM nombre correo electrónico o teléfono'}}" value="{{request()?->search_by}}" aria-label="{{'buscar'}}" >
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
                                    src="{{$dm['image_full_url']}}"
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
                                    <div class="btn--container justify-content-end">
                                        <a class="btn action-btn btn--primary btn-outline-primary request-alert" data-toggle="tooltip" data-placement="top"
                                        data-original-title="{{ 'aprobar' }}" data-url="{{route('admin.users.delivery-man.application',[$dm['id'],'approved'])}}" data-message="{{'quieres aprobar esta solicitud'}}"
                                            href="javascript:"><i class="tio-done font-weight-bold"></i> </a>
                                        <a class="btn action-btn btn--primary btn-outline-primary"  data-toggle="tooltip" data-placement="top" data-original-title="{{ 'editar' }}" href="{{route('admin.users.delivery-man.edit',[$dm['id']])}}" ><i class="tio-edit"></i>
                                        </a>
                                        <button type="button" class="btn action-btn btn--secondary btn-outline-secondary js-registration-revision-btn" data-toggle="tooltip" data-placement="top" data-original-title="{{ 'solicitar revisión de registro' }}" data-url="{{ route('admin.users.delivery-man.request-registration-revision', $dm['id']) }}">
                                            <i class="tio-comment"></i>
                                        </button>
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

        <div class="modal fade" id="registrationRevisionModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form id="registrationRevisionForm" method="post" action="">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">{{ 'solicitar revisión de registro' }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <label class="input-label">{{ 'etiqueta de mensaje de revisión de registro' }}</label>
                            <small class="d-block text-muted mb-2">{{ 'sugerencia de mensaje de revisión de registro' }}</small>
                            <textarea name="revision_message" class="form-control" rows="4" required maxlength="2000" placeholder="{{ 'etiqueta de mensaje de revisión de registro' }}"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ 'Cancelar' }}</button>
                            <button type="submit" class="btn btn--primary">{{ 'entregar' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/deliveryman-new-denied-list.js"></script>
    <script>
        "use strict";
        $(document).on('click', '.js-registration-revision-btn', function () {
            var url = $(this).data('url');
            $('#registrationRevisionForm').attr('action', url);
            $('#registrationRevisionForm').find('textarea[name="revision_message"]').val('');
            $('#registrationRevisionModal').modal('show');
        });
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

