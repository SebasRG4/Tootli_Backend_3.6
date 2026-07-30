@extends('layouts.vendor.app')

@section('title','bandera')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/fi_9752284.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'Configuración del banner'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{ route('vendor.banner.store') }}" method="POST" enctype="multipart/form-data" class="custom-validation">
                    @csrf
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 d-flex justify-content-end">

                                    <div class="blinkings">
                                        <strong class="mr-2">{{'instrucciones'}}</strong>
                                        <div>
                                            <i class="tio-info-outined"></i>
                                        </div>
                                        <div class="business-notes">
                                            <h6><img src="{{asset('assets/admin/img/notes.png')}}" alt=""> {{'Nota'}}</h6>
                                            <div>
                                                {{'El cliente verá pancartas en la página de detalles de su tienda en el sitio web y en las aplicaciones de usuario.'}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group error-wrapper">

                                        <label for="title" class="form-label">{{'Título del banner'}}</label>
                                        <input id="title" type="text" name="title" class="form-control" placeholder="{{'título aquí...'}}" required>
                                    </div>
                                    <div class="form-group error-wrapper">

                                        <label for="default_link" class="form-label">{{'URL de redirección/enlace'}}</label>
                                        <input id="default_link" type="url" name="default_link" class="form-control" placeholder="{{'Ingrese la URL'}}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                        <h3 class="form-label d-block mb-2">
                                                {{'Subir banner'}}
                                            </h3>
                                    <label class="upload-img-3 m-0 d-block error-wrapper">
                                        <div class="img">
                                            <img src="{{asset('assets/admin/img/upload-4.png')}}" id="viewer"  class="vertical-img mw-100 vertical" alt="">
                                        </div>
                                            <input type="file" name="image"  hidden required>
                                    </label>
                                    <h3 class="form-label d-block mt-2">
                                        {{'Proporción de imagen de banner 3:1'}}
                                    </h3>
                                    <p>{{'formato de imagen: jpg, png, jpeg | tamaño máximo: 2 MB'}}</p>
                                </div>
                                <div class="col-sm-6">
                                </div>
                            </div>
                            <div class="btn--container justify-content-end mt-20">
                                <button type="reset" id="reset_btn" class="btn btn--reset">{{'Reiniciar'}}</button>
                                <button type="submit" class="btn btn--primary">{{'Entregar'}}</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">
                                {{'lista de pancartas'}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$banners->count()}}</span>
                            </h5>
                            <form id="search-form" class="search-form">
                                <!-- Search -->
                                <div class="input-group input--group">
                                    <input id="datatableSearch" type="search" name="search" class="form-control" placeholder="{{'buscar por título'}}" aria-label="{{'buscar aquí'}}" value="{{ request()->search }}">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                "order": [],
                                "orderCellsTop": true,
                                "search": "#datatableSearch",
                                "entries": "#datatableEntries",
                                "isResponsive": false,
                                "isShowPaging": false,
                                "paging": false
                               }'
                               >
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">{{ 'SL' }}</th>
                                    <th class="border-0">{{'título'}}</th>
                                    <th class="border-0">{{'imagen de banner'}}</th>
                                    <th class="border-0">{{'Enlace de redirección'}}</th>
                                    <th class="border-0 text-center">{{'estado'}}</th>
                                    <th class="border-0 text-center">{{'acción'}}</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($banners as $key=>$banner)
                                <tr>
                                    <td>{{$key+$banners->firstItem()}}</td>
                                    <td><h5 class="text-hover-primary mb-0">{{Str::limit($banner['title'], 25, '...')}}</h5></td>
                                    <td>
                                        <span class="media align-items-center">
                                            <img class="img--ratio-3 w-auto h--50px rounded mr-2 onerror-image" src="{{ $banner['image_full_url']}}"
                                                 data-onerror-image="{{asset('assets/admin/img/900x400/img1.jpg')}}"
                                                  alt="{{$banner->name}} image">
                                        </span>
                                    </td>
                                    <td><a href="{{ $banner->default_link }}"> {{Str::limit($banner['default_link'], 60, '...')}}</a></td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <label class="toggle-switch toggle-switch-sm" for="statusCheckbox{{$banner->id}}">
                                            <input type="checkbox"
                                                   data-url="{{route('vendor.banner.status_update',[$banner['id'],$banner->status?0:1])}}"
                                                   class="toggle-switch-input redirect-url" id="statusCheckbox{{$banner->id}}" {{$banner->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('vendor.banner.edit',[$banner['id']])}}" title="{{'editar banner'}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:"
                                               data-id="banner-{{$banner['id']}}"
                                               data-message="{{ '¿Quieres eliminar este banner?' }}"
                                                title="{{'eliminar banner'}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('vendor.banner.delete',[$banner['id']])}}"
                                                        method="post" id="banner-{{$banner['id']}}">
                                                    @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        @if(count($banners) !== 0)
                        <hr>
                        @endif
                        <div class="page-area">
                            {!! $banners->links() !!}
                        </div>
                        @if(count($banners) === 0)
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
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
        <script>
            "use strict";
            $('#reset_btn').click(function(){
                $('#viewer').attr('src','{{asset('assets/admin/img/upload-4.png')}}');
            })
        </script>

@endpush
