@extends('layouts.admin.app')

@section('title', $store->name . "'s " . 'Configuración')

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{ asset('assets/admin/css/croppie.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        @include('admin-views.vendor.view.partials._header', ['store' => $store])
        <!-- Page Heading -->
        <div class="tab-content">
            <div class="tab-pane fade show active" id="vendor">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <span class="card-header-icon">
                                <img class="w--22" src="{{ asset('assets/admin/img/store.png') }}" alt="">
                            </span>
                            <span class="p-md-1"> {{ 'almacenar metadatos' }}</span>
                        </h5>
                    </div>
                    @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
                    @php($language = $language->value ?? null)
                    @php($defaultLang = 'en')
                    <div class="card-body">
                        <form action="{{ route('admin.store.update-meta-data', [$store['id']]) }}" method="post"
                            enctype="multipart/form-data" class="col-12">
                            @csrf
                            <div class="row g-2">
                                <div class="col-lg-6">
                                    <div class="card shadow--card-2">
                                        <div class="card-body">
                                            @if ($language)
                                                <ul class="nav nav-tabs mb-4">
                                                    <li class="nav-item">
                                                        <a class="nav-link lang_link active" href="#"
                                                            id="default-link">{{ 'Por defecto' }}</a>
                                                    </li>
                                                    @foreach (json_decode($language) as $lang)
                                                        <li class="nav-item">
                                                            <a class="nav-link lang_link" href="#"
                                                                id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            @if ($language)
                                                <div class="lang_form" id="default-form">
                                                    <div class="form-group">
                                                        <label class="input-label"
                                                            for="default_title">{{ 'metatítulo' }}
                                                            ({{ 'Por defecto' }})
                                                            <span class="form-label-secondary" data-toggle="tooltip"
                                                                data-placement="right"
                                                                data-original-title="{{ 'Este título aparece en las pestañas del navegador, en los resultados de búsqueda y en las vistas previas de enlaces. Utilice un título breve, claro y centrado en palabras clave (recomendado: entre 50 y 60 caracteres).' }}">
                                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                    alt="">
                                                            </span>
                                                        </label>
                                                        <input type="text" name="meta_title[]" id="default_title"
                                                            maxlength="60" class="form-control"
                                                            placeholder="{{ 'metatítulo' }}"
                                                            value="{{ $store->getRawOriginal('meta_title') }}">
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label"
                                                            for="exampleFormControlInput1">{{ 'meta descripción' }}
                                                            ({{ 'por defecto' }})
                                                            <span class="form-label-secondary" data-toggle="tooltip"
                                                                data-placement="right"
                                                                data-original-title="{{ 'Un breve resumen que aparece debajo del título de su página en los resultados de búsqueda. Manténgalo atractivo y relevante (recomendado: 120 a 160 caracteres).' }}">
                                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                    alt="">
                                                            </span></label>
                                                        <textarea type="text" maxlength="160" name="meta_description[]"
                                                            placeholder="{{ 'meta descripción' }}" class="form-control min-h-90px ckeditor">{{ $store->getRawOriginal('meta_description') }}</textarea>
                                                    </div>
                                                </div>
                                                @foreach (json_decode($language) as $lang)
                                                    <?php
                                                    if (count($store['translations'])) {
                                                        $translate = [];
                                                        foreach ($store['translations'] as $t) {
                                                            if ($t->locale == $lang && $t->key == 'meta_title') {
                                                                $translate[$lang]['meta_title'] = $t->value;
                                                            }
                                                            if ($t->locale == $lang && $t->key == 'meta_description') {
                                                                $translate[$lang]['meta_description'] = $t->value;
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <div class="d-none lang_form" id="{{ $lang }}-form">
                                                        <div class="form-group">
                                                            <label class="input-label"
                                                                for="{{ $lang }}_title">{{ 'metatítulo' }}
                                                                ({{ strtoupper($lang) }})
                                                                <span class="form-label-secondary" data-toggle="tooltip"
                                                                    data-placement="right"
                                                                    data-original-title="{{ 'Este título aparece en las pestañas del navegador, en los resultados de búsqueda y en las vistas previas de enlaces. Utilice un título breve, claro y centrado en palabras clave (recomendado: entre 50 y 60 caracteres).' }}">
                                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                        alt="">
                                                                </span>
                                                            </label>
                                                            <input type="text" name="meta_title[]"
                                                                id="{{ $lang }}_title" maxlength="60"
                                                                class="form-control"
                                                                value="{{ $translate[$lang]['meta_title'] ?? '' }}"
                                                                placeholder="{{ 'metatítulo' }}">
                                                        </div>
                                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label"
                                                                for="exampleFormControlInput1">{{ 'meta descripción' }}
                                                                ({{ strtoupper($lang) }})
                                                                <span class="form-label-secondary" data-toggle="tooltip"
                                                                    data-placement="right"
                                                                    data-original-title="{{ 'Un breve resumen que aparece debajo del título de su página en los resultados de búsqueda. Manténgalo atractivo y relevante (recomendado: 120 a 160 caracteres).' }}">
                                                                    <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                        alt="">
                                                                </span></label>
                                                            <textarea type="text" maxlength="160" name="meta_description[]"
                                                                placeholder="{{ 'meta descripción' }}" class="form-control min-h-90px ckeditor">{{ $translate[$lang]['meta_description'] ?? '' }}</textarea>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div id="default-form">
                                                    <div class="form-group">
                                                        <label class="input-label"
                                                            for="exampleFormControlInput1">{{ 'metatítulo' }}
                                                            ({{ 'por defecto' }})</label>
                                                        <input type="text" name="meta_title[]" class="form-control"
                                                            placeholder="{{ 'metatítulo' }}">
                                                    </div>
                                                    <input type="hidden" name="lang[]" value="default">
                                                    <div class="form-group mb-0">
                                                        <label class="input-label"
                                                            for="exampleFormControlInput1">{{ 'meta descripción' }}
                                                        </label>
                                                        <textarea type="text" name="meta_description[]" placeholder="{{ 'meta descripción' }}"
                                                            class="form-control min-h-90px ckeditor"></textarea>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card shadow--card-2">
                                        <div class="card-header">
                                            <h5 class="card-title">
                                                <span class="card-header-icon mr-1"><i class="tio-dashboard"></i></span>
                                                <span>{{ 'almacenar metaimagen' }}</span>
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-center flex-wrap flex-sm-nowrap __gap-12px">
                                                <label class="__custom-upload-img mr-lg-5">
                                                    <div class="position-relative">
                                                        <label class="form-label">
                                                            {{ 'metaimagen' }} <span
                                                                class="text--primary">({{ '2:1' }})</span>
                                                            <span class="form-label-secondary" data-toggle="tooltip"
                                                                data-placement="right"
                                                                data-original-title="{{ 'Esta imagen se utiliza como miniatura de vista previa cuando el enlace de la página se comparte en redes sociales o plataformas de mensajería.' }}">
                                                                <img src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                                    alt="">
                                                            </span></label>

                                                        <div class="text-center">
                                                            <img class="img--110 min-height-170px min-width-170px onerror-image"
                                                                id="viewer"
                                                                data-onerror-image="{{ asset('assets/admin/img/upload.png') }}"
                                                                src="{{ $store->meta_image_full_url ?? asset('assets/admin/img/upload.png') }}"
                                                                alt="{{ 'metaimagen' }}" />
                                                        </div>
                                                        <input type="file" name="meta_image" id="customFileEg1"
                                                            class="custom-file-input"
                                                            accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">

                                                        @if (isset($store->meta_image))
                                                            <span id="earning_delivery_img"
                                                                class="remove_image_button mt-4 dynamic-checkbox"
                                                                data-id="earning_delivery_img" data-type="status"
                                                                data-image-on='{{ asset('assets/admin/img/modal') }}/mail-success.png'
                                                                data-image-off="{{ asset('assets/admin/img/modal') }}/mail-warning.png"
                                                                data-title-on="{{ '¡Importante!' }}"
                                                                data-title-off="{{ '¡Advertencia!' }}"
                                                                data-text-on="<p>{{ '¿Estás seguro de que quieres eliminar esta imagen?' }}</p>"
                                                                data-text-off="<p>{{ '¿Está seguro de que desea eliminar esta imagen?' }}</p>">
                                                                <i class="tio-clear"></i></span>
                                                        @endif
                                                    </div>
                                                </label>
                                            </div>
                                             <div class="d-flex justify-content-center">
                            <div class="text-center">
                                <small>{{ 'Sube una imagen rectangular (tamaño recomendado: 800×400 px, formato: JPG o PNG)' }}</small>
                            </div>
                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="justify-content-end btn--container">
                                        <button type="submit"
                                            class="btn btn--primary">{{ 'guardar cambios' }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form id="earning_delivery_img_form" action="{{ route('admin.remove_image') }}" method="post">
        @csrf
        <input type="hidden" name="id" value="{{ $store?->id }}">
        <input type="hidden" name="model_name" value="Store">
        <input type="hidden" name="image_path" value="store">
        <input type="hidden" name="field_name" value="meta_image">
    </form>
@endsection

@push('script_2')
    <script>
        function readURL(input, viewer) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();

                reader.onload = function(e) {
                    $('#' + viewer).attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function() {
            readURL(this, 'viewer');
        });

        $("#coverImageUpload").change(function() {
            readURL(this, 'coverImageViewer');
        });
    </script>
@endpush
