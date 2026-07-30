@extends('layouts.admin.app')

@section('title','atributos')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/attribute.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'agregar nuevo atributo'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row g-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.attribute.store')}}" method="post">
                            @csrf
                            @if ($language)
                                    <ul class="nav nav-tabs mb-3 border-0">
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
                                            <label class="input-label"
                                                for="default_title">{{ 'nombre' }}
                                                ({{'por defecto'}}) <span class="form-label-secondary text-danger"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Requerido.'}}"> *
                                                </span>

                                            </label>
                                            <input type="text" name="name[]" id="default_title"
                                                class="form-control" placeholder="{{ 'ej: nuevo atributo' }}"
                                            >
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                        @foreach ($language as $lang)
                                            <div class="d-none lang_form"
                                                id="{{ $lang }}-form">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="{{ $lang }}_title">{{ 'nombre' }}
                                                        ({{ strtoupper($lang) }})
                                                    </label>
                                                    <input type="text" name="name[]" id="{{ $lang }}_title"
                                                        class="form-control" placeholder="{{ 'ej: nuevo atributo' }}">
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                            </div>
                                        @endforeach
                                    @else
                                        <div id="default-form">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'nombre' }} ({{ 'por defecto' }})</label>
                                                <input type="text" name="name[]" class="form-control"
                                                    placeholder="{{ 'ej: nuevo atributo' }}">
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                    @endif
                            <div class="btn--container justify-content-end">
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
                                {{'lista de atributos'}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$attributes->total()}}</span>
                            </h5>
                            <form  class="search-form">
                                <!-- Search -->

                                <div class="input-group input--group">
                                    <input id="datatableSearch_" value="{{ request()?->search ?? null }}" type="search" name="search" class="form-control"
                                            placeholder="{{'ej: nombre del atributo'}}" aria-label="Search" >
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                            @if(request()->get('search'))
                            <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{'reiniciar'}}</button>
                            @endif


                            <!-- Unfold -->
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
                                    <a id="export-excel" class="dropdown-item" href="{{route('admin.attribute.export-attributes', ['type'=>'excel' , request()->getQueryString() ])}}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                            alt="Image Description">
                                        {{ 'sobresalir' }}
                                    </a>
                                    <a id="export-csv" class="dropdown-item" href="{{route('admin.attribute.export-attributes', ['type'=>'csv' , request()->getQueryString() ])}}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ asset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
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
                                <th class="border-0">{{'IDENTIFICACIÓN'}}</th>
                                <th class="border-0">{{'nombre'}}</th>
                                <th class="border-0">{{'acción'}}</th>
                            </tr>

                            </thead>

                            <tbody id="set-rows">
                            @foreach($attributes as $key=>$attribute)
                                <tr>
                                    <td class="text-center">
                                        <span class="mr-3">
                                            {{$key+$attributes->firstItem()}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="mr-3">
                                            {{$attribute['id']}}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span title="{{ $attribute['name'] }}" class="font-size-sm text-body mr-3">
                                            {{Str::limit($attribute['name'],20,'...')}}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.attribute.edit',[$attribute['id']])}}" title="{{'editar'}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="attribute-{{$attribute['id']}}" data-message="{{ '¿Quieres eliminar este atributo?' }}" title="{{'borrar'}}"><i class="tio-delete-outlined"></i></a>
                                            <form action="{{route('admin.attribute.delete',[$attribute['id']])}}"
                                                    method="post" id="attribute-{{$attribute['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(count($attributes) !== 0)
                    <hr>
                    @endif
                    <div class="page-area">
                        {!! $attributes->links() !!}
                    </div>
                    @if(count($attributes) === 0)
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
    <script src="{{asset('assets/admin')}}/js/view-pages/attribute-index.js"></script>
    <script>
        "use strict";

        $(".lang_link").click(function(e){
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            console.log(lang);
            $("#"+lang+"-form").removeClass('d-none');
        })
    </script>
@endpush
