@extends('layouts.admin.app')

@section('title','Actualizar banner')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/edit.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'banner de actualización'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form data-ajax="true" id="banner_form" class="custom-validation">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    @if($language)
                                        <ul class="nav nav-tabs mb-4">
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
                                            <div class="form-group error-wrapper">
                                                <label class="input-label" for="default_title">{{'título'}} ({{'por defecto'}})</label>
                                                <input type="text" name="title[]" id="default_title" class="form-control" placeholder="{{'nueva pancarta'}}" required value="{{$banner?->getRawOriginal('title')}}">
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        </div>
                                        @foreach($language as $lang)
                                            <?php
                                                if(count($banner['translations'])){
                                                    $translate = [];
                                                    foreach($banner['translations'] as $t)
                                                    {
                                                        if($t->locale == $lang && $t->key=="title"){
                                                            $translate[$lang]['title'] = $t->value;
                                                        }
                                                    }
                                                }
                                            ?>
                                            <div class="d-none lang_form" id="{{$lang}}-form">
                                                <div class="form-group error-wrapper">
                                                    <label class="input-label" for="{{$lang}}_title">{{'título'}} ({{strtoupper($lang)}})</label>
                                                    <input type="text" name="title[]" id="{{$lang}}_title" class="form-control" placeholder="{{'nueva pancarta'}}" value="{{$translate[$lang]['title']??''}}">
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{$lang}}">
                                            </div>
                                        @endforeach
                                    @else
                                    <div id="default-form">
                                        <div class="form-group error-wrapper">
                                            <label class="input-label" for="exampleFormControlInput1">{{'título'}} ({{ 'por defecto' }})</label>
                                            <input type="text" name="title[]" class="form-control" placeholder="{{'nueva pancarta'}}" value="{{$banner['title']}}" maxlength="100">
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                    @endif
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="title">{{'zona'}}</label>
                                        <select name="zone_id" id="zone" class="form-control js-select2-custom">
                                            <option  disabled selected>---{{'seleccionar'}}---</option>
                                            @foreach($zones as $zone)
                                                @if(isset(auth('admin')->user()->zone_id))
                                                    @if(auth('admin')->user()->zone_id == $zone->id)
                                                        <option value="{{$zone['id']}}" {{$zone->id == $banner->zone_id?'selected':''}}>{{$zone['name']}}</option>
                                                    @endif
                                                @else
                                                <option value="{{$zone['id']}}" {{$zone->id == $banner->zone_id?'selected':''}}>{{$zone['name']}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="exampleFormControlInput1">{{'tipo de banner'}}</label>
                                        <select name="banner_type" id="banner_type" class="form-control">
                                            <option value="store_wise" {{$banner->type == 'store_wise'? 'selected':'' }}>{{'tienda sabia'}}</option>
                                            <option value="item_wise" {{$banner->type == 'item_wise'? 'selected':'' }}>{{'artículo sabio'}}</option>
                                            <option value="default" {{$banner->type == 'default'? 'selected':'' }}>{{'por defecto'}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group error-wrapper">
                                        <label class="input-label" for="grid_type">Grid Type (Delivery Type)</label>
                                        <select name="grid_type" id="grid_type" class="form-control">
                                            <option value="" {{$banner->grid_type == null ? 'selected' : ''}}>Todos</option>
                                            <option value="minutes" {{$banner->grid_type == 'minutes' ? 'selected' : ''}}>Minutes (Fast)</option>
                                            <option value="standard" {{$banner->grid_type == 'standard' ? 'selected' : ''}}>Standard</option>
                                            <option value="next_day" {{$banner->grid_type == 'next_day' ? 'selected' : ''}}>Next Day</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0 error-wrapper" id="store_wise">
                                        <label class="input-label" for="exampleFormControlSelect1">{{'Negocio'}}<span
                                                class="input-label-secondary"></span></label>
                                        <select name="store_id" id="store_id" class="js-data-example-ajax" id="resturant_ids"  title="Select Restaurant">
                                        @if($banner->type=='store_wise')
                                        @php($store = \App\Models\Store::where('id', $banner->data)->first())
                                            @if($store)
                                            <option value="{{$store->id}}" selected>{{$store->name}}</option>
                                            @endif
                                        @endif
                                        </select>
                                    </div>
                                    <div class="form-group mb-0 error-wrapper" id="item_wise">
                                        <label class="input-label" for="exampleFormControlInput1">{{'seleccionar elemento'}}</label>
                                        <select name="item_id" id="choice_item" class="form-control js-select2-custom" placeholder="{{'seleccionar elemento'}}">

                                        </select>
                                    </div>
                                    <div class="form-group mb-0 error-wrapper" id="default">
                                        <label class="input-label" for="exampleFormControlInput1">{{'enlace predeterminado'}}</label>
                                        <input type="text" name="default_link" class="form-control" value="{{ $banner->default_link }}" placeholder="{{'enlace predeterminado'}}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="error-wrapper">
                                        <div class="h-100 d-flex flex-column">
                                            <label class="mt-auto mb-0 d-block text-center">
                                                {{'imagen de banner'}}
                                                <small class="text-danger">* ( {{'relación'}} 900x300 )</small>
                                            </label>
                                            <div class="text-center py-3 my-auto">
                                                <img class="img--vertical onerror-image" id="viewer" data-onerror-image="{{asset('assets/admin/img/900x400/img1.jpg')}}" src="{{ $banner['image_full_url'] }}"
                                                alt="banner image"/>
                                            </div>
                                            <div class="custom-file">
                                                <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                                    accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                <label class="custom-file-label" for="customFileEg1">{{'elegir archivo'}}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <div class="btn--container justify-content-end">
                                        <button type="reset" id="reset_btn" class="btn btn--reset">{{'reiniciar'}}</button>
                                        <button type="submit" class="btn btn--primary">{{'actualizar'}}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/banner-edit.js"></script>
    <script>
        "use strict";

        var zone_id = {{$banner->zone_id}};

        var module_id = {{$banner->module_id}};

        function get_items()
        {
            var nurl = '{{url('/')}}/admin/item/get-items?module_id='+module_id;

            if(!Array.isArray(zone_id))
            {
                nurl += '&zone_id='+zone_id;
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
            banner_type_change('{{$banner->type}}');
            if($('#banner_type').val() !== 'item_wise' ){
                get_items();
            }
            $('#zone').on('change', function(){
                if($(this).val())
                {
                    zone_id = $(this).val();
                    // get_items();
                }
                else
                {
                    zone_id = true;
                }
            });

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



            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });


        @if($banner->type == 'item_wise')
        getRequest('{{url('/')}}/admin/item/get-items?module_id={{$banner->module_id}}&zone_id={{$banner->zone_id}}&data[]={{$banner->data}}','choice_item');
        @endif
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
                url: "{{route('admin.banner.update', [$banner['id']])}}",
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
                        toastr.success("{{'banner actualizado exitosamente'}}", {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = "{{route('admin.banner.add-new')}}";
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
                        toastr.error('Error al actualizar el banner. Es posible que el archivo sea muy pesado o exista un problema de conexión.');
                    }
                }
            });
        });
    </script>
@endpush
