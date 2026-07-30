@extends('layouts.admin.app')

@section('title','Mensajes de contacto')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
            <!-- Page Title -->
            <div class="mb-3">
                <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                    <img width="20" src="{{asset('assets/back-end/img/message.png')}}" alt="">
                    {{'todas las listas de mensajes'}}
                </h2>
            </div>
            <!-- End Page Title -->
        <!-- End Page Header -->
        <div class="row g-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">
                                {{'listas de mensajes'}} <span class="badge badge-soft-dark ml-2" id="itemCount">{{$contacts->total()}}</span>
                            </h5>
                            <form class="search-form">
                                <div class="input-group input--group">
                                    <input  type="search" name="search" class="form-control"
                                    placeholder="{{'ej: buscar por nombre, correo electrónico o asunto'}}" aria-label="{{'buscar'}}" value="{{request()?->search}}" >
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                            </form>
                           @if(request()->get('search'))
                                <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{'reiniciar'}}</button>
                                @endif


                            <!-- Unfold -->
                            <div class="hs-unfold mr-2">
                                <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40"
                                   href="javascript:"
                                   data-hs-unfold-options='{
                                                        "target": "#usersExportDropdown",
                                                        "type": "css-animation"
                                                    }'>
                                    <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                                </a>

                                <div id="usersExportDropdown"
                                     class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                                    <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                                    <a id="export-excel" class="dropdown-item"
                                       href="{{route('admin.users.contact.exportList', ['type'=>'excel',request()->getQueryString()])}}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                             src="{{ asset('assets/admin/svg/components/excel.svg') }}"
                                             alt="Image Description">
                                        {{ 'sobresalir' }}
                                    </a>
                                    <a id="export-csv" class="dropdown-item"
                                       href="{{route('admin.users.contact.exportList', ['type'=>'csv',request()->getQueryString()])}}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                             src="{{ asset('assets/admin/svg/components/placeholder-csv-format.svg') }}"
                                             alt="Image Description">
                                        .{{ 'csv' }}
                                    </a>
                                </div>
                            </div>
                            <!-- End Unfold -->


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
                                <th class="border-0">{{'nombre'}}</th>
                                <th class="border-0">{{'correo electrónico'}}</th>
                                <th class="border-0">{{'sujeto'}}</th>
                                <th class="border-0">{{'Visto/no visto'}}</th>
                                <th class="border-0">{{'acción'}}</th>
                            </tr>

                            </thead>

                            <tbody id="set-rows">
                            @foreach($contacts as $key=>$contact)
                                <tr>
                                    <td class="text-center">
                                        <span class="mr-3">
                                            {{$key+1}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-size-sm text-body mr-3">
                                            {{Str::limit($contact['name'],20,'...')}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-size-sm text-body mr-3">
                                            {{$contact['email']}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="font-size-sm text-body mr-3 white--space-initial max-w-180px mx-auto">
                                            {{Str::limit($contact['subject'],40,'...')}}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-size-sm text-body mr-3">
                                            @if($contact->seen==1)
                                            <label class="badge badge-soft-success mb-0">{{'Visto'}}</label>
                                        @else
                                            <label class="badge badge-soft-info mb-0">{{'Aún no visto'}}</label>
                                        @endif
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.users.contact.contact-view',[$contact['id']])}}" title="{{'editar'}}"><i class="tio-invisible"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="contact-{{$contact['id']}}" data-message="{{ '¿Quieres eliminar este mensaje?' }}" title="{{'borrar'}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.users.contact.contact-delete',[$contact['id']])}}"
                                                    method="post" id="contact-{{$contact['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(count($contacts) !== 0)
                    <hr>
                    @endif
                    <div class="page-area">
                        {!! $contacts->links() !!}
                    </div>
                    @if(count($contacts) === 0)
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
    <script src="{{asset('assets/admin')}}/js/view-pages/contact-index.js"></script>
@endpush
