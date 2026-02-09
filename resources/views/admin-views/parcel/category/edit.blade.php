@extends('layouts.admin.app')

@section('title', translate('messages.update_parcel_category'))


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/edit.png') }}" class="w--26" alt="">
                </span>
                <span>
                    {{ translate('messages.update_parcel_category') }}
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
                                            id="default-link">{{ translate('messages.default') }}</a>
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
                                        <label class="input-label" for="default_name">{{ translate('messages.name') }}
                                            ({{ translate('messages.default') }})</label>
                                        <input type="text" name="name[]" id="default_name" class="form-control"
                                            placeholder="{{ translate('messages.new_food') }}"
                                            value="{{ $parcel_category?->getRawOriginal('name') }}">
                                    </div>
                                    <input type="hidden" name="lang[]" value="default">
                                    <div class="form-group">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.short_description') }}
                                            ({{ translate('messages.default') }})</label>
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
                                                for="{{ $lang }}_name">{{ translate('messages.name') }}
                                                ({{ strtoupper($lang) }})</label>
                                            <input type="text" name="name[]" id="{{ $lang }}_name"
                                                class="form-control" placeholder="{{ translate('messages.new_food') }}"
                                                value="{{ $translate[$lang]['name'] ?? '' }}">
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{ translate('messages.short_description') }}
                                                ({{ strtoupper($lang) }})</label>
                                            <textarea type="text" name="description[]" class="form-control ckeditor">{!! $translate[$lang]['description'] ?? '' !!}</textarea>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div id="default-form">
                                    <div class="form-group">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.name') }} (EN)</label>
                                        <input type="text" name="name[]" class="form-control"
                                            placeholder="{{ translate('messages.new_food') }}"
                                            value="{{ $parcel_category['name'] }}" required>
                                    </div>
                                    <input type="hidden" name="lang[]" value="en">
                                    <div class="form-group">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.short_description') }}</label>
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
                                    {{ translate('messages.image') }}
                                    <small class="text-danger">* ( {{ translate('messages.ratio') }} 200x200 )</small>
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
                                        for="customFileEg1">{{ translate('messages.choose_file') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label
                                    class="input-label text-capitalize">{{ translate('messages.per_km_shipping_charge') }}</label>
                                <input type="number" step=".01" min="0"
                                    placeholder="{{ translate('messages.per_km_shipping_charge') }}" class="form-control"
                                    name="parcel_per_km_shipping_charge"
                                    value="{{ $parcel_category->parcel_per_km_shipping_charge }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label
                                    class="input-label text-capitalize">{{ translate('messages.minimum_shipping_charge') }}</label>
                                <input type="number" step=".01" min="0"
                                    placeholder="{{ translate('messages.minimum_shipping_charge') }}"
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
                                        {{ translate('messages.buy_and_deliver') }}
                                        <small class="text-danger"> * ( {{ translate('messages.activate_if_you_want_to_buy_and_deliver') }} )</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                        @if ($categoryWiseTax)
                                <div class="col-6">
                                    <span
                                        class="mb-2 d-block title-clr fw-normal">{{ translate('Select Tax Rate') }}</span>
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
                            <h5 class="mb-3">{{ translate('messages.service_options') }}</h5>
                            <div id="options-container">
                                @if($parcel_category->options)
                                    @foreach($parcel_category->options as $key => $option)
                                        <div class="card mb-3 option-row" id="option-row-{{$key}}">
                                            <div class="card-header d-flex justify-content-between">
                                                <h6>{{ translate('Option') }} #{{$key+1}}</h6>
                                                <button type="button" class="btn btn-danger btn-sm remove-option" data-id="{{$key}}"><i class="tio-delete"></i></button>
                                            </div>
                                            <div class="card-body">
                                                <input type="hidden" name="options[{{$key}}][id]" value="{{$option->id}}">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        @if ($language)
                                                            <div class="lang_form" id="default-form-{{$key}}">
                                                                <div class="form-group">
                                                                    <label class="input-label">{{ translate('messages.title') }} ({{ translate('messages.default') }})</label>
                                                                    <input type="text" name="options[{{$key}}][title][]" class="form-control" value="{{$option->getRawOriginal('title')}}" placeholder="{{ translate('messages.title') }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="input-label">{{ translate('messages.description') }} ({{ translate('messages.default') }})</label>
                                                                    <input type="text" name="options[{{$key}}][description][]" class="form-control" value="{{$option->getRawOriginal('description')}}" placeholder="{{ translate('messages.description') }}">
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
                                                                        <label class="input-label">{{ translate('messages.title') }} ({{ strtoupper($lang) }})</label>
                                                                        <input type="text" name="options[{{$key}}][title][]" class="form-control" value="{{ $translate[$lang]['title'] ?? '' }}" placeholder="{{ translate('messages.title') }}">
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="input-label">{{ translate('messages.description') }} ({{ strtoupper($lang) }})</label>
                                                                        <input type="text" name="options[{{$key}}][description][]" class="form-control" value="{{ $translate[$lang]['description'] ?? '' }}" placeholder="{{ translate('messages.description') }}">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div id="default-form-{{$key}}">
                                                                <div class="form-group">
                                                                    <label class="input-label">{{ translate('messages.title') }} (EN)</label>
                                                                    <input type="text" name="options[{{$key}}][title][]" class="form-control" value="{{$option->title}}" placeholder="{{ translate('messages.title') }}">
                                                                </div>
                                                                <input type="hidden" name="lang[]" value="en">
                                                                <div class="form-group">
                                                                    <label class="input-label">{{ translate('messages.description') }} (EN)</label>
                                                                    <input type="text" name="options[{{$key}}][description][]" class="form-control" value="{{$option->description}}" placeholder="{{ translate('messages.description') }}">
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label class="input-label">{{ translate('messages.charge_multiplier') }}</label>
                                                            <input type="number" step="0.1" name="options[{{$key}}][charge_multiplier]" class="form-control" value="{{$option->charge_multiplier}}">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="input-label">{{ translate('messages.base_price') }} <small class="text-muted">(Hybrid Pricing)</small></label>
                                                            <input type="number" step="0.01" name="options[{{$key}}][base_price]" class="form-control" value="{{$option->base_price ?? 0}}" placeholder="0.00">
                                                            <small class="text-info">Base price + (Distance × Per Km Rate). Leave 0 for distance-only pricing.</small>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="input-label">{{ translate('messages.service_type') }}</label>
                                                            <select name="options[{{$key}}][service_type]" class="form-control">
                                                                <option value="custom" {{$option->service_type=='custom'?'selected':''}}>{{ translate('messages.custom') }}</option>
                                                                <option value="deliver_now" {{$option->service_type=='deliver_now'?'selected':''}}>{{ translate('messages.deliver_now') }}</option>
                                                                <option value="schedule" {{$option->service_type=='schedule'?'selected':''}}>{{ translate('messages.schedule') }}</option>
                                                                <option value="truck" {{$option->service_type=='truck'?'selected':''}}>{{ translate('messages.truck') }}</option>
                                                                <option value="end_of_day" {{$option->service_type=='end_of_day'?'selected':''}}>{{ translate('messages.end_of_day') }}</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="input-label">{{ translate('messages.tag') }}</label>
                                                            <input type="text" name="options[{{$key}}][tag]" class="form-control" value="{{$option->tag}}" placeholder="e.g. Save 40%">
                                                        </div>
                                                         <div class="form-group">
                                                            <label class="input-label">{{ translate('messages.icon') }}</label>
                                                            <input type="file" name="options[{{$key}}][icon]" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-success" id="add-option-btn"><i class="tio-add"></i> {{ translate('messages.add_option') }}</button>
                        </div>
                        <div class="col-12">
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn"
                                    class="btn btn--reset">{{ translate('messages.reset') }}</button>
                                <button type="submit"
                                    class="btn btn--primary">{{ translate('messages.update') }}</button>
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
                        <h6>{{ translate('Option') }} #${optionCount+1}</h6>
                        <button type="button" class="btn btn-danger btn-sm remove-option" data-id="${optionCount}"><i class="tio-delete"></i></button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                @if ($language)
                                    <div class="lang_form" id="default-form-${optionCount}">
                                        <div class="form-group">
                                            <label class="input-label">{{ translate('messages.title') }} ({{ translate('messages.default') }})</label>
                                            <input type="text" name="options[${optionCount}][title][]" class="form-control" placeholder="{{ translate('messages.title') }}">
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                        <div class="form-group">
                                            <label class="input-label">{{ translate('messages.description') }} ({{ translate('messages.default') }})</label>
                                            <input type="text" name="options[${optionCount}][description][]" class="form-control" placeholder="{{ translate('messages.description') }}">
                                        </div>
                                    </div>
                                    @foreach (json_decode($language) as $lang)
                                        <div class="d-none lang_form" id="{{ $lang }}-form-${optionCount}">
                                            <div class="form-group">
                                                <label class="input-label">{{ translate('messages.title') }} ({{ strtoupper($lang) }})</label>
                                                <input type="text" name="options[${optionCount}][title][]" class="form-control" placeholder="{{ translate('messages.title') }}">
                                            </div>
                                            <div class="form-group">
                                                <label class="input-label">{{ translate('messages.description') }} ({{ strtoupper($lang) }})</label>
                                                <input type="text" name="options[${optionCount}][description][]" class="form-control" placeholder="{{ translate('messages.description') }}">
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div id="default-form-${optionCount}">
                                        <div class="form-group">
                                            <label class="input-label">{{ translate('messages.title') }} (EN)</label>
                                            <input type="text" name="options[${optionCount}][title][]" class="form-control" placeholder="{{ translate('messages.title') }}">
                                        </div>
                                        <input type="hidden" name="lang[]" value="en">
                                        <div class="form-group">
                                            <label class="input-label">{{ translate('messages.description') }} (EN)</label>
                                            <input type="text" name="options[${optionCount}][description][]" class="form-control" placeholder="{{ translate('messages.description') }}">
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="input-label">{{ translate('messages.charge_multiplier') }}</label>
                                    <input type="number" step="0.1" name="options[${optionCount}][charge_multiplier]" class="form-control" value="1.0">
                                </div>
                                <div class="form-group">
                                    <label class="input-label">{{ translate('messages.base_price') }} <small class="text-muted">(Hybrid Pricing)</small></label>
                                    <input type="number" step="0.01" name="options[${optionCount}][base_price]" class="form-control" value="0.00" placeholder="0.00">
                                    <small class="text-info">Base price + (Distance × Per Km Rate). Leave 0 for distance-only pricing.</small>
                                </div>
                                <div class="form-group">
                                    <label class="input-label">{{ translate('messages.service_type') }}</label>
                                    <select name="options[${optionCount}][service_type]" class="form-control">
                                        <option value="custom">{{ translate('messages.custom') }}</option>
                                        <option value="deliver_now">{{ translate('messages.deliver_now') }}</option>
                                        <option value="schedule">{{ translate('messages.schedule') }}</option>
                                        <option value="truck">{{ translate('messages.truck') }}</option>
                                        <option value="end_of_day">{{ translate('messages.end_of_day') }}</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="input-label">{{ translate('messages.tag') }}</label>
                                    <input type="text" name="options[${optionCount}][tag]" class="form-control" placeholder="e.g. Save 40%">
                                </div>
                                <div class="form-group">
                                    <label class="input-label">{{ translate('messages.icon') }}</label>
                                    <input type="file" name="options[${optionCount}][icon]" class="form-control">
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
