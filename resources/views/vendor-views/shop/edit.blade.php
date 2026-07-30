@php
    $vendorData = \App\CentralLogics\Helpers::get_store_data();
    $title = $vendorData?->module_type == 'rental' && addon_published_status('Rental') ? 'Provider' : 'Store';
@endphp
@extends('layouts.vendor.app')
@section('title',translate('messages.edit_' . $title))
@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{asset('assets/admin')}}/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
     <!-- Custom styles for this page -->
     <link href="{{asset('assets/admin/css/croppie.css')}}" rel="stylesheet">
     <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@section('content')
    <!-- Content Row -->
    <div class="content container-fluid">
        <div class="page-header">
            <h2 class="page-header-title text-capitalize">
                <img class="w--26" src="{{asset('assets/admin/img/store.png')}}" alt="public">
                <span>
                    {{'editar \'.$título.\' información'}}
                </span>
            </h1>
        </div>
        @php($language=\App\Models\BusinessSetting::where('key','language')->first())
        @php($language = $language->value ?? null)
        @php($defaultLang = 'en')
        <form action="{{route('vendor.shop.update')}}" method="post"
                enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                @if($language)
                            <ul class="nav nav-tabs mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                    href="#"
                                    id="default-link">{{ 'Por defecto' }}</a>
                                </li>
                                @foreach (json_decode($language) as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                            href="#"
                                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                            @endif
                            <div class="col-12">
                                    @if ($language)
                                    <div class="lang_form"
                                    id="default-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="default_name">{{ 'nombre' }}
                                                ({{ 'Por defecto' }})
                                            </label>
                                            <input type="text" name="name[]" id="default_name"
                                                class="form-control" placeholder="{{ '\'.$título.\' nombre' }}" value="{{$shop->getRawOriginal('name')}}"

                                                 >
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                        <div class="form-group mb-0">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ 'DIRECCIÓN' }} ({{ 'por defecto' }})</label>
                                            <textarea type="text" name="address[]" placeholder="{{'\'.$title)}}" class="form-control min-h-90px ckeditor">{{$shop->getRawOriginal(\'dirección'}}</textarea>
                                        </div>
                                    </div>
                                        @foreach (json_decode($language) as $lang)
                                        <?php
                                            if(count($shop['translations'])){
                                                $translate = [];
                                                foreach($shop['translations'] as $t)
                                                {
                                                    if($t->locale == $lang && $t->key=="name"){
                                                        $translate[$lang]['name'] = $t->value;
                                                    }
                                                    if($t->locale == $lang && $t->key=="address"){
                                                        $translate[$lang]['address'] = $t->value;
                                                    }
                                                }
                                            }
                                        ?>
                                            <div class="d-none lang_form"
                                                id="{{ $lang }}-form">
                                                <div class="form-group">
                                                    <label class="input-label"
                                                        for="{{ $lang }}_name">{{ 'nombre' }}
                                                        ({{ strtoupper($lang) }})
                                                    </label>
                                                    <input type="text" name="name[]" id="{{ $lang }}_name"
                                                        class="form-control" value="{{ $translate[$lang]['name']??'' }}" placeholder="{{ 'nombre de la tienda' }}"
                                                         >
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                <div class="form-group mb-0">
                                                    <label class="input-label"
                                                        for="exampleFormControlInput1">{{ 'DIRECCIÓN' }} ({{ strtoupper($lang) }})</label>
                                                    <textarea type="text" name="address[]" placeholder="{{'Negocio'}}" class="form-control min-h-90px ckeditor">{{ $translate[$lang]['address']??'' }}</textarea>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div id="default-form">
                                            <div class="form-group">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'nombre' }} ({{ 'por defecto' }})</label>
                                                <input type="text" name="name[]" class="form-control"
                                                    placeholder="{{ 'nombre de la tienda' }}" required>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            <div class="form-group mb-0">
                                                <label class="input-label"
                                                    for="exampleFormControlInput1">{{ 'DIRECCIÓN' }}
                                                </label>
                                                <textarea type="text" name="address[]" placeholder="{{'Negocio'}}" class="form-control min-h-90px ckeditor"></textarea>
                                            </div>
                                        </div>
                                    @endif
                                    {{-- <div class="form-group">
                                        <label for="name">{{'nombre de la tienda'}} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{$shop->name}}" class="form-control" id="name"
                                                required>
                                    </div> --}}
                                    <div class="form-group mt-2">
                                        <label for="name">{{'número de contacto'}}<span class="text-danger">*</span></label>
                                        <input type="text" name="contact" value="{{$shop->phone}}" class="form-control" id="name"
                                                required>
                                    </div>
                                </div>
                                {{-- <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address">{{'DIRECCIÓN'}}<span class="text-danger">*</span></label>
                                        <textarea type="text" rows="4" name="address" value="" class="form-control" id="address"
                                                required>{{$shop->address}}</textarea>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title font-regular">
                                {{'subir logotipo'}}
                            </h5>
                        </div>
                        <div class="card-body d-flex flex-column pt-0">
                            <div class="text-center my-auto py-4 py-xl-5">
                                <img class="store-banner onerror-image" id="viewer"
                                data-onerror-image="{{asset('assets/admin/img/image-place-holder.png')}}"
                                src="{{ $shop->logo_full_url }}" alt="Product thumbnail"/>
                            </div>
                            <div class="custom-file">
                                <input type="file" name="image" id="customFileUpload" class="custom-file-input"
                                    accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                <label class="custom-file-label" for="customFileUpload">{{'elegir archivo'}}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title font-regular">
                                {{'subir foto de portada'}} <span class="text-danger">({{'relación'}} 2:1)</span>
                            </h5>
                        </div>
                        <div class="card-body d-flex flex-column pt-0">
                            <div class="text-center my-auto py-4 py-xl-5">
                                <img class="store-banner onerror-image" id="coverImageViewer"
                                data-onerror-image="{{asset('assets/admin/img/restaurant_cover.jpg')}}"
                                src="{{ $shop->cover_photo_full_url }}" alt="Product thumbnail"/>
                            </div>
                            <div class="custom-file">
                                <input type="file" name="photo" id="coverImageUpload" class="custom-file-input"
                                    accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                <label class="custom-file-label" for="coverImageUpload">{{'elegir archivo'}}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 justify-content-end btn--container">
                <a class="btn btn--danger text-capitalize" href="{{route('vendor.shop.view')}}">{{'Cancelar'}}</a>
                <button type="submit" class="btn btn--primary text-capitalize" id="btn_update">{{'actualizar'}}</button>
            </div>
        </form>
    </div>
@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/vendor/shop-edit.js"></script>
@endpush
