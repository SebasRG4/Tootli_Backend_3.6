@extends('layouts.admin.app')

@section('title', 'bandera')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/banner.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'agregar nuevo banner'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form id="banner_form" class="custom-validation" data-ajax="true">

                            <div class="row g-3">
                                <div class="col-lg-6">
                                    @if ($language)
                                        <ul class="nav nav-tabs mb-3 border-0">
                                            <li class="nav-item">
                                                <a class="nav-link lang_link active" href="#"
                                                    id="default-link">{{'por defecto'}}</a>
                                            </li>
                                            @foreach ($language as $lang)
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link" href="#"
                                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="lang_form" id="default-form">
                                            <div class="form-group error-wrapper">
                                                <label class="input-label" for="default_title">{{ 'título' }}
                                                    (Default)
                                                </label>
                                                <input type="text" name="title[]" id="default_title" class="form-control"
                                                    placeholder="{{ 'nueva pancarta' }}" required>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                        @foreach ($language as $lang)
                                            <div class="d-none lang_form" id="{{ $lang }}-form">
                                                <div class="form-group error-wrapper">
                                                    <label class="input-label"
                                                        for="{{ $lang }}_title">{{ 'título' }}
                                                        ({{ strtoupper($lang) }})
                                                    </label>
                                                    <input type="text" name="title[]" id="{{ $lang }}_title" class="form-control"
                                                        placeholder="{{ 'nueva pancarta' }}">
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                            </div>
                                        @endforeach
                                    @else
                                        <div id="default-form">
                                            <div class="form-group error-wrapper">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'título' }}
                                                    ({{ 'por defecto' }})</label>
                                                <input type="text" name="title[]" class="form-control"
                                                    placeholder="{{ 'nueva pancarta' }}" required>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                    @endif
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="title">{{'zona'}}</label>
                                        <select name="zone_id" id="zone" class="form-control js-select2-custom" required>
                                            <option disabled selected>---{{'seleccionar'}}---</option>
                                            @foreach($zones as $zone)
                                                @if(isset(auth('admin')->user()->zone_id))
                                                    @if(auth('admin')->user()->zone_id == $zone->id)
                                                        <option value="{{$zone->id}}" selected>{{$zone->name}}</option>
                                                    @endif
                                                @else
                                                    <option value="{{$zone['id']}}">{{$zone['name']}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group error-wrapper">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{'tipo de banner'}}</label>
                                        <select name="banner_type" id="banner_type" class="form-control">
                                            <option value="store_wise">{{'tienda sabia'}}</option>
                                            <option value="item_wise">{{'artículo sabio'}}</option>
                                            <option value="default">{{'por defecto'}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="grid_type">Grid Type (Delivery Type)</label>
                                        <select name="grid_type" id="grid_type" class="form-control">
                                            <option value="">Todos</option>
                                            <option value="minutes">Minutes (Fast)</option>
                                            <option value="standard">Standard</option>
                                            <option value="next_day">Next Day</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0 error-wrapper" id="store_wise">
                                        <label class="input-label"
                                            for="exampleFormControlSelect1">{{'Negocio'}}<span
                                                class="input-label-secondary"></span></label>
                                        <select name="store_id" id="store_id" class="js-data-example-ajax form-control"
                                            title="{{'seleccionar tienda'}}">
                                            <option disabled selected>---{{'seleccionar tienda'}}---</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0 error-wrapper" id="item_wise">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{'seleccionar elemento'}}</label>
                                        <select name="item_id" id="choice_item" class="form-control js-select2-custom"
                                            placeholder="{{'seleccionar elemento'}}">

                                        </select>
                                    </div>
                                    <div class="form-group mb-0 error-wrapper" id="default">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{'enlace predeterminado'}}({{ 'opcional' }})</label>
                                        <input type="text" name="default_link" class="form-control"
                                            placeholder="{{'enlace predeterminado'}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="error-wrapper">
                                        <div class="h-100 d-flex flex-column">
                                            <label
                                                class="mt-auto mb-0 d-block text-center">{{'imagen de banner'}}
                                                <small class="text-danger">* ( {{'relación'}} 3:1
                                                    )</small></label>
                                            <div class="text-center py-3 my-auto">
                                                <img class="img--vertical" id="viewer"
                                                    src="{{asset('assets/admin/img/900x400/img1.jpg')}}"
                                                    alt="banner image" />
                                            </div>
                                            <div class="custom-file">
                                                <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                                    accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                                    required>
                                                <label class="custom-file-label"
                                                    for="customFileEg1">{{'elegir archivo'}}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <div class="btn--container justify-content-end">
                                        <button type="reset" id="reset_btn"
                                            class="btn btn--reset">{{'reiniciar'}}</button>
                                        <button type="submit"
                                            class="btn btn--primary">{{'entregar'}}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">
                                {{'lista de pancartas'}}<span class="badge badge-soft-dark ml-2"
                                    id="itemCount">{{$banners->count()}}</span>
                            </h5>
                            <form class="search-form">
                                <!-- Search -->
                                <div class="input-group input--group">
                                    <input id="datatableSearch" type="search" value="{{ request()->get('search') ?? '' }}"
                                        name="search" class="form-control"
                                        placeholder="{{'buscar por título'}}"
                                        aria-label="{{'buscar aquí'}}">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                            @if(request()->get('search'))
                                <button type="reset" class="btn btn--primary ml-2 location-reload-to-base"
                                    data-url="{{url()->full()}}">{{'reiniciar'}}</button>
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
                                        "search": "#datatableSearch",
                                        "entries": "#datatableEntries",
                                        "isResponsive": false,
                                        "isShowPaging": false,
                                        "paging": false
                                    }'>
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0">{{ 'SL' }}</th>
                                    <th class="border-0">{{'título'}}</th>
                                    <th class="border-0">{{'tipo'}}</th>
                                    <th class="border-0 text-center">{{'presentado'}} <span
                                            class="input-label-secondary" data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{'Si activa/desactiva esta función, afectará al sitio web y a la aplicación del usuario.'}}"><img
                                                src="{{asset('assets/admin/img/info-circle.svg')}}" alt="public/img"></span>
                                    </th>
                                    <th class="border-0 text-center">{{'estado'}}</th>
                                    <th class="border-0 text-center">{{'acción'}}</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                                @foreach($banners as $key => $banner)
                                    <tr>
                                        <td>{{$key + $banners->firstItem()}}</td>
                                        <td>
                                            <span class="media align-items-center">
                                                <img class="img--ratio-3 w-auto h--50px rounded mr-2 onerror-image"
                                                    src="{{ $banner['image_full_url'] }}"
                                                    data-onerror-image="{{asset('assets/admin/img/900x400/img1.jpg')}}"
                                                    alt="{{$banner->name}} image">
                                                <div class="media-body">
                                                    <h5 title="{{ $banner['title'] }}" class="text-hover-primary mb-0">
                                                        {{Str::limit($banner['title'], 25, '...')}}</h5>
                                                </div>
                                            </span>
                                            <span class="d-block font-size-sm text-body">

                                            </span>
                                        </td>
                                        <td>{{translate('messages.' . $banner['type'])}}</td>

                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <label class="toggle-switch toggle-switch-sm"
                                                    for="featuredCheckbox{{$banner->id}}">
                                                    <input type="checkbox" data-id="featuredCheckbox{{$banner->id}}"
                                                        data-type="status"
                                                        data-image-on="{{ asset('assets/admin/img/modal/basic_campaign_on.png') }}"
                                                        data-image-off="{{ asset('assets/admin/img/modal/basic_campaign_off.png') }}"
                                                        data-title-on="{{ '¡Activándolo como se muestra!' }}"
                                                        data-title-off="{{ '¡Apagando como aparece!' }}"
                                                        data-text-on="<p>{{ 'Si activa esta característica, el banner promocional se mostrará en el sitio web y en la aplicación del usuario con la tienda o el artículo.' }}</p>"
                                                        data-text-off="<p>{{ 'Si desactiva esta función, el banner promocional no se mostrará en el sitio web ni en la aplicación del usuario.' }}</p>"
                                                        class="toggle-switch-input  dynamic-checkbox"
                                                        id="featuredCheckbox{{$banner->id}}" {{$banner->featured ? 'checked' : ''}}>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <form action="{{route('admin.banner.featured', [$banner['id'], $banner->featured ? 0 : 1])}}"
                                            method="get" id="featuredCheckbox{{$banner->id}}_form">
                                        </form>

                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <label class="toggle-switch toggle-switch-sm"
                                                    for="statusCheckbox{{$banner->id}}">
                                                    <input type="checkbox" data-id="statusCheckbox{{$banner->id}}"
                                                        data-type="status"
                                                        data-image-on="{{ asset('assets/admin/img/modal/basic_campaign_on.png') }}"
                                                        data-image-off="{{ asset('assets/admin/img/modal/basic_campaign_off.png') }}"
                                                        data-title-on="{{ '¡Activando Banner!' }}"
                                                        data-title-off="{{ '¡Apagando el banner!' }}"
                                                        data-text-on="<p>{{ 'Si activa este estado, se mostrará en el sitio web y la aplicación del usuario.' }}</p>"
                                                        data-text-off="<p>{{ 'Si desactiva este estado, no se mostrará en el sitio web ni en la aplicación del usuario.' }}</p>"
                                                        class="toggle-switch-input  dynamic-checkbox"
                                                        id="statusCheckbox{{$banner->id}}" {{$banner->status ? 'checked' : ''}}>
                                                    <span class="toggle-switch-label">
                                                        <span class="toggle-switch-indicator"></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>

                                        <form action="{{route('admin.banner.status', [$banner['id'], $banner->status ? 0 : 1])}}"
                                            method="get" id="statusCheckbox{{$banner->id}}_form">
                                        </form>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn action-btn btn--primary btn-outline-primary"
                                                    href="{{route('admin.banner.edit', [$banner['id']])}}"
                                                    title="{{'editar banner'}}"><i class="tio-edit"></i>
                                                </a>
                                                <a class="btn action-btn btn--danger btn-outline-danger form-alert"
                                                    href="javascript:" data-id="banner-{{$banner['id']}}"
                                                    data-message="{{ '¿Quieres eliminar este banner?' }}"><i
                                                        class="tio-delete-outlined"></i>
                                                </a>
                                                <form action="{{route('admin.banner.delete', [$banner['id']])}}" method="post"
                                                    id="banner-{{$banner['id']}}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
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
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/banner-index.js"></script>
    <script>
        "use strict";
        var module_id = {{Config::get('module.current_module_id')}};

        function get_items() {
            var nurl = '{{url('/')}}/admin/item/get-items?module_id=' + module_id;

            if (!Array.isArray(zone_id)) {
                nurl += '&zone_id=' + zone_id;
            }

            $.get({
                url: nurl,
                dataType: 'json',
                success: function (data) {
                    $('#choice_item').empty().append(data.options);
                }
            });
        }

        $(document).on('ready', function () {

            module_id = {{Config::get('module.current_module_id')}};
            get_items();

            $('.js-data-example-ajax').select2({
                ajax: {
                    url: '{{url('/')}}/admin/store/get-stores',
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            zone_ids: [zone_id],
                            page: params.page,
                            module_id: module_id
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    __port: function (params, success, failure) {
                        var $request = $.ajax(params);

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    }
                }
            });

        });

        $('#banner_form').on('submit', function (e) {
            e.preventDefault();

            let $form = $(this);
            if (!$form.valid()) {
                return false;
            }

            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: "{{route('admin.banner.store')}}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (var i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('{{'banner agregado exitosamente'}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{route("admin.banner.add-new")}}';
                        }, 2000);
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 413) {
                        toastr.error('La imagen es demasiado pesada. Por favor, sube una imagen más ligera (máx. 2MB).', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        for (var i = 0; i < xhr.responseJSON.errors.length; i++) {
                            toastr.error(xhr.responseJSON.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error al guardar el banner. Es posible que el archivo sea muy pesado o exista un problema de conexión.');
                    }
                }
            });
        });



        $('#reset_btn').click(function () {
            $('#module_select').val(null).trigger('change');
            $('#zone').val(null).trigger('change');
            $('#store_id').val(null).trigger('change');
            $('#choice_item').val(null).trigger('change');
            $('#viewer').attr('src', '{{asset('assets/admin/img/900x400/img1.jpg')}}');
        })
    </script>
@endpush