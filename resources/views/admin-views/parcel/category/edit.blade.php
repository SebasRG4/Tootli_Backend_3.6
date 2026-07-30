@extends('layouts.admin.app')

@section('title', 'actualizar categoría de paquete')


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/edit.png') }}" class="w--26" alt="">
                </span>
                <span>
                    {{ 'actualizar categoría de paquete' }}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.parcel.category.update', [$parcel_category['id']]) }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        @method('PUT')
                        @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
                        @php($language = $language->value ?? null)
                        @php($defaultLang = str_replace('_', '-', app()->getLocale()))
                        <div class="col-lg-12">
                            @if ($language)
                                @php($defaultLang = json_decode($language)[0])
                                <ul class="nav nav-tabs mb-4">
                                    <li class="nav-item">
                                        <a class="nav-link lang_link active" href="#"
                                            id="default-link">{{ 'por defecto' }}</a>
                                    </li>
                                    @foreach (json_decode($language) as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link lang_link" href="#"
                                                id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="col-lg-6">
                            @if ($language)
                                <div class="lang_form" id="default-form">
                                    <div class="form-group">
                                        <label class="input-label" for="default_name">{{ 'nombre' }}
                                            ({{ 'por defecto' }})</label>
                                        <input type="text" name="name[]" id="default_name" class="form-control"
                                            placeholder="{{ 'comida nueva' }}"
                                            value="{{ $parcel_category?->getRawOriginal('name') }}">
                                    </div>
                                    <input type="hidden" name="lang[]" value="default">
                                    <div class="form-group">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ 'breve descripción' }}
                                            ({{ 'por defecto' }})</label>
                                        <textarea type="text" name="description[]" class="form-control ckeditor">{!! $parcel_category?->getRawOriginal('description') !!}</textarea>
                                    </div>
                                </div>
                                @foreach (json_decode($language) as $lang)
                                    <?php
                                    if (count($parcel_category['translations'])) {
                                        $translate = [];
                                        foreach ($parcel_category['translations'] as $t) {
                                            if ($t->locale == $lang && $t->key == 'name') {
                                                $translate[$lang]['name'] = $t->value;
                                            }
                                            if ($t->locale == $lang && $t->key == 'description') {
                                                $translate[$lang]['description'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="d-none lang_form" id="{{ $lang }}-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="{{ $lang }}_name">{{ 'nombre' }}
                                                ({{ strtoupper($lang) }})</label>
                                            <input type="text" name="name[]" id="{{ $lang }}_name"
                                                class="form-control" placeholder="{{ 'comida nueva' }}"
                                                value="{{ $translate[$lang]['name'] ?? '' }}">
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ 'breve descripción' }}
                                                ({{ strtoupper($lang) }})</label>
                                            <textarea type="text" name="description[]" class="form-control ckeditor">{!! $translate[$lang]['description'] ?? '' !!}</textarea>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div id="default-form">
                                    <div class="form-group">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ 'nombre' }} (EN)</label>
                                        <input type="text" name="name[]" class="form-control"
                                            placeholder="{{ 'comida nueva' }}"
                                            value="{{ $parcel_category['name'] }}" required>
                                    </div>
                                    <input type="hidden" name="lang[]" value="en">
                                    <div class="form-group">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ 'breve descripción' }}</label>
                                        <textarea type="text" name="description[]" class="form-control ckeditor">{!! $parcel_category['description'] !!}</textarea>
                                    </div>
                                </div>
                            @endif
                            @if ($parcel_category->position == 0)
                            @endif
                        </div>
                        <div class="col-lg-6">
                            <div class="h-100 d-flex flex-column">
                                <label class="mb-0 mt-auto d-block text-center">
                                    {{ 'imagen' }}
                                    <small class="text-danger">* ( {{ 'relación' }} 200x200 )</small>
                                </label>
                                <div class="text-center py-3 my-auto">
                                    <img class="img--130 onerror-image" id="viewer"
                                        src="{{ $parcel_category['image_full_url'] }}"
                                        data-onerror-image="{{ asset('assets/admin/img/400x400/img2.jpg') }}" />
                                </div>
                                <div class="custom-file">
                                    <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                        accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label"
                                        for="customFileEg1">{{ 'elegir archivo' }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label
                                    class="input-label text-capitalize">{{ 'costo de envío por km' }}</label>
                                <input type="number" step=".01" min="0"
                                    placeholder="{{ 'costo de envío por km' }}" class="form-control"
                                    name="parcel_per_km_shipping_charge"
                                    value="{{ $parcel_category->parcel_per_km_shipping_charge }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label
                                    class="input-label text-capitalize">{{ 'cargo mínimo de envío' }}</label>
                                <input type="number" step=".01" min="0"
                                    placeholder="{{ 'cargo mínimo de envío' }}"
                                    class="form-control" name="parcel_minimum_shipping_charge"
                                    value="{{ $parcel_category->parcel_minimum_shipping_charge }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="toggle-switch toggle-switch-sm mr-2" for="buy_and_deliver">
                                    <input type="checkbox" class="toggle-switch-input" name="buy_and_deliver" id="buy_and_deliver" value="1" {{$parcel_category->buy_and_deliver?'checked':''}}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                    <span class="toggle-switch-content">
                                        {{ 'comprar y entregar' }}
                                        <small class="text-danger"> * ( {{ 'actívalo si quieres comprar y entregar' }} )</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                        {{-- ── Seguro del paquete (Rappi Favor-style) ── --}}
                        <div class="col-12"><hr><h6 class="mb-3 text-primary"><i class="tio-verified mr-1"></i>{{ 'seguro de paquetería' ?? 'Seguro del Paquete' }}</h6></div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label text-capitalize">{{ 'porcentaje de tasa de seguro' ?? 'Tasa de Seguro (%)' }}</label>
                                <input type="number" step="0.01" min="0" max="100"
                                    placeholder="Ej: 2 (significa 2% del valor declarado)"
                                    class="form-control"
                                    name="insurance_rate_percentage"
                                    value="{{ $parcel_category->insurance_rate_percentage ?? 0 }}">
                                <small class="text-muted">Porcentaje del valor declarado que se cobra como seguro. Ej: 2 = 2%.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label text-capitalize">{{ 'tarifa mínima de seguro' ?? 'Tarifa Mínima de Seguro' }}</label>
                                <input type="number" step="0.01" min="0"
                                    placeholder="Ej: 10 (tarifa mínima en pesos)"
                                    class="form-control"
                                    name="min_insurance_fee"
                                    value="{{ $parcel_category->min_insurance_fee ?? 0 }}">
                                <small class="text-muted">El usuario paga el mayor entre (tasa × valor declarado) y esta tarifa mínima.</small>
                            </div>
                        </div>
                        {{-- ────────────────────────────────────────────────────────────── --}}

                        @if ($categoryWiseTax)
                                <div class="col-6">
                                    <span
                                        class="mb-2 d-block title-clr fw-normal">{{ 'Seleccionar tasa impositiva' }}</span>
                                    <select name="tax_ids[]" required id=""
                                        class="form-control js-select2-custom" multiple="multiple"
                                        placeholder="Type & Select Tax Rate">
                                        @foreach ($taxVats as $taxVat)
                                            <option {{ in_array($taxVat->id, $taxVatIds) ? 'selected' : '' }}
                                                value="{{ $taxVat->id }}"> {{ $taxVat->name }}
                                                ({{ $taxVat->tax_rate }}%)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                        @endif
                        <div class="col-12">
                            <hr>
                            <h5 class="mb-3">{{ 'opciones de servicio' }}</h5>
                            <div id="options-container">
                                @if($parcel_category->options)
                                    @foreach($parcel_category->options as $key => $option)
                                        <div class="card mb-3 option-row" id="option-row-{{$key}}">
                                            <div class="card-header d-flex justify-content-between">
                                                <h6>{{ 'Opción' }} #{{$key+1}}</h6>
                                                <button type="button" class="btn btn-danger btn-sm remove-option" data-id="{{$key}}"><i class="tio-delete"></i></button>
                                            </div>
                                            <div class="card-body">
                                                <input type="hidden" name="options[{{$key}}][id]" value="{{$option->id}}">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        @if ($language)
                                                            <div class="lang_form" id="default-form-{{$key}}">
                                                                <div class="form-group">
                                                                    <label class="input-label">{{ 'título' }} ({{ 'por defecto' }})</label>
                                                                    <input type="text" name="options[{{$key}}][title][]" class="form-control" value="{{$option->getRawOriginal('title')}}" placeholder="{{ 'título' }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="input-label">{{ 'descripción' }} ({{ 'por defecto' }})</label>
                                                                    <input type="text" name="options[{{$key}}][description][]" class="form-control" value="{{$option->getRawOriginal('description')}}" placeholder="{{ 'descripción' }}">
                                                                </div>
                                                            </div>
                                                            @foreach (json_decode($language) as $lang)
                                                                <?php
                                                                if (count($option['translations'])) {
                                                                    $translate = [];
                                                                    foreach ($option['translations'] as $t) {
                                                                        if ($t->locale == $lang && $t->key == 'title') {
                                                                            $translate[$lang]['title'] = $t->value;
                                                                        }
                                                                        if ($t->locale == $lang && $t->key == 'description') {
                                                                            $translate[$lang]['description'] = $t->value;
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                                <div class="d-none lang_form" id="{{ $lang }}-form-{{$key}}">
                                                                    <div class="form-group">
                                                                        <label class="input-label">{{ 'título' }} ({{ strtoupper($lang) }})</label>
                                                                        <input type="text" name="options[{{$key}}][title][]" class="form-control" value="{{ $translate[$lang]['title'] ?? '' }}" placeholder="{{ 'título' }}">
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="input-label">{{ 'descripción' }} ({{ strtoupper($lang) }})</label>
                                                                        <input type="text" name="options[{{$key}}][description][]" class="form-control" value="{{ $translate[$lang]['description'] ?? '' }}" placeholder="{{ 'descripción' }}">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div id="default-form-{{$key}}">
                                                                 <div class="form-group">
                                                                    <label class="input-label">{{ 'título' }} (EN)</label>
                                                                    <input type="text" name="options[{{$key}}][title][]" class="form-control" value="{{$option->title}}" placeholder="{{ 'título' }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="input-label">{{ 'descripción' }} (EN)</label>
                                                                    <input type="text" name="options[{{$key}}][description][]" class="form-control" value="{{$option->description}}" placeholder="{{ 'descripción' }}">
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="input-label">{{ 'multiplicador de carga' }}</label>
                                                            <input type="number" step="0.1" name="options[{{$key}}][charge_multiplier]" class="form-control" value="{{$option->charge_multiplier}}">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="input-label">{{ 'precio base' }} <small class="text-muted">(Hybrid Pricing)</small></label>
                                                            <input type="number" step="0.01" name="options[{{$key}}][base_price]" class="form-control" value="{{$option->base_price ?? 0}}" placeholder="0.00">
                                                            <small class="text-info">Base price + (Distance × Per Km Rate). Leave 0 for distance-only pricing.</small>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="input-label">{{ 'tipo de servicio' }}</label>
                                                            <select name="options[{{$key}}][service_type]" class="form-control">
                                                                <option value="custom" {{$option->service_type=='custom'?'selected':''}}>{{ 'costumbre' }}</option>
                                                                <option value="deliver_now" {{$option->service_type=='deliver_now'?'selected':''}}>{{ 'entregar ahora' }}</option>
                                                                <option value="schedule" {{$option->service_type=='schedule'?'selected':''}}>{{ 'cronograma' }}</option>
                                                                <option value="truck" {{$option->service_type=='truck'?'selected':''}}>{{ 'camión' }}</option>
                                                                <option value="end_of_day" {{$option->service_type=='end_of_day'?'selected':''}}>{{ 'final del dia' }}</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="input-label">{{ 'etiqueta' }}</label>
                                                            <input type="text" name="options[{{$key}}][tag]" class="form-control" value="{{$option->tag}}" placeholder="e.g. Save 40%">
                                                        </div>
                                                         <div class="form-group">
                                                            <label class="input-label">{{ 'icono' }}</label>
                                                            <div class="custom-file">
                                                                <input type="file" name="options[{{$key}}][icon]" 
                                                                    id="option-icon-{{$key}}"
                                                                    class="custom-file-input option-icon-input" 
                                                                    data-preview="viewer-option-{{$key}}"
                                                                    accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                                <label class="custom-file-label" for="option-icon-{{$key}}">{{ 'elegir archivo' }}</label>
                                                            </div>
                                                            <div class="text-center mt-2">
                                                                <img class="img--100 onerror-image" id="viewer-option-{{$key}}"
                                                                    src="{{ $option->image_full_url }}"
                                                                    data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-success" id="add-option-btn"><i class="tio-add"></i> {{ 'agregar opción' }}</button>
                        </div>
                        <div class="col-12">
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn"
                                    class="btn btn--reset">{{ 'reiniciar' }}</button>
                                <button type="submit"
                                    class="btn btn--primary">{{ 'actualizar' }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";

        function readURL(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();

                reader.onload = function(e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function() {
            readURL(this);
        });

        let optionCount = {{ $parcel_category->options ? count($parcel_category->options) : 0 }};

        $('#add-option-btn').click(function() {
            let html = `
                <div class="card mb-3 option-row" id="option-row-${optionCount}">
                    <div class="card-header d-flex justify-content-between">
                        <h6>{{ 'Opción' }} #${optionCount+1}</h6>
                        <button type="button" class="btn btn-danger btn-sm remove-option" data-id="${optionCount}"><i class="tio-delete"></i></button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                @if ($language)
                                    <div class="lang_form" id="default-form-${optionCount}">
                                        <div class="form-group">
                                            <label class="input-label">{{ 'título' }} ({{ 'por defecto' }})</label>
                                            <input type="text" name="options[${optionCount}][title][]" class="form-control" placeholder="{{ 'título' }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="input-label">{{ 'descripción' }} ({{ 'por defecto' }})</label>
                                            <input type="text" name="options[${optionCount}][description][]" class="form-control" placeholder="{{ 'descripción' }}">
                                        </div>
                                    </div>
                                    @foreach (json_decode($language) as $lang)
                                        <div class="d-none lang_form" id="{{ $lang }}-form-${optionCount}">
                                            <div class="form-group">
                                                <label class="input-label">{{ 'título' }} ({{ strtoupper($lang) }})</label>
                                                <input type="text" name="options[${optionCount}][title][]" class="form-control" placeholder="{{ 'título' }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="input-label">{{ 'descripción' }} ({{ strtoupper($lang) }})</label>
                                                <input type="text" name="options[${optionCount}][description][]" class="form-control" placeholder="{{ 'descripción' }}">
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div id="default-form-${optionCount}">
                                        <div class="form-group">
                                            <label class="input-label">{{ 'título' }} (EN)</label>
                                            <input type="text" name="options[${optionCount}][title][]" class="form-control" placeholder="{{ 'título' }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="input-label">{{ 'descripción' }} (EN)</label>
                                            <input type="text" name="options[${optionCount}][description][]" class="form-control" placeholder="{{ 'descripción' }}">
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ 'multiplicador de carga' }}</label>
                                    <input type="number" step="0.1" name="options[${optionCount}][charge_multiplier]" class="form-control" value="1.0">
                                </div>
                                <div class="form-group">
                                    <label class="input-label">{{ 'precio base' }} <small class="text-muted">(Hybrid Pricing)</small></label>
                                    <input type="number" step="0.01" name="options[${optionCount}][base_price]" class="form-control" value="0.00" placeholder="0.00">
                                    <small class="text-info">Base price + (Distance × Per Km Rate). Leave 0 for distance-only pricing.</small>
                                </div>
                                <div class="form-group">
                                    <label class="input-label">{{ 'tipo de servicio' }}</label>
                                    <select name="options[${optionCount}][service_type]" class="form-control">
                                        <option value="custom">{{ 'costumbre' }}</option>
                                        <option value="deliver_now">{{ 'entregar ahora' }}</option>
                                        <option value="schedule">{{ 'cronograma' }}</option>
                                        <option value="truck">{{ 'camión' }}</option>
                                        <option value="end_of_day">{{ 'final del dia' }}</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="input-label">{{ 'etiqueta' }}</label>
                                    <input type="text" name="options[${optionCount}][tag]" class="form-control" placeholder="e.g. Save 40%">
                                </div>
                                <div class="form-group">
                                    <label class="input-label">{{ 'icono' }}</label>
                                    <div class="custom-file">
                                        <input type="file" name="options[${optionCount}][icon]" 
                                            id="option-icon-${optionCount}"
                                            class="custom-file-input option-icon-input" 
                                            data-preview="viewer-option-${optionCount}"
                                            accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="option-icon-${optionCount}">{{ 'elegir archivo' }}</label>
                                    </div>
                                    <div class="text-center mt-2">
                                        <img class="img--100 onerror-image" id="viewer-option-${optionCount}"
                                            src="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                            data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#options-container').append(html);
            
            // Sync current language view for new row
            let activeLang = $(".lang_link.active").attr('id');
            if(activeLang){
                let lang = activeLang.substring(0, activeLang.length - 5);
                $("#" + lang + "-form-" + optionCount).removeClass('d-none');
                if(lang !== 'default'){
                     $("#default-form-" + optionCount).addClass('d-none');
                }
            }
            
            optionCount++;
        });

        $(document).on('click', '.remove-option', function() {
            let id = $(this).data('id');
            $('#option-row-' + id).remove();
        });

        $(document).on('change', '.option-icon-input', function() {
            let input = this;
            let previewId = $(this).data('preview');
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#' + previewId).attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        });

        // Update existing logic to handle dynamic forms translation switching
        $(".lang_link").click(function(e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            
            // Show main form
            $("#" + lang + "-form").removeClass('d-none');
            
            // Show option forms
            $('[id^="' + lang + '-form-"]').removeClass('d-none');
        });

        $('#reset_btn').click(function() {
            $('#module_id').val("{{ $parcel_category->module_id }}").trigger('change');
            $('#viewer').attr('src',
                "{{ asset('storage/app/public/parcel_category') }}/{{ $parcel_category['image'] }}");
        })
    </script>
@endpush
