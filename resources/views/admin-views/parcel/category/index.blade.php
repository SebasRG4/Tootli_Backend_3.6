@extends('layouts.admin.app')

@section('title', 'categoría de paquete')


@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/parcel.png')}}" class="w--26" alt="">
            </span>
            <span>
                {{'categoría de paquete'}}
            </span>
        </h1>
    </div>
    <!-- End Page Header -->

    <div class="card">
        <div class="card-body">
            <form action="{{route('admin.parcel.category.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
                    @php($language = $language->value ?? null)
                    @php($defaultLang = str_replace('_', '-', app()->getLocale()))
                    @if($language)
                        <div class="col-12">
                            <ul class="nav nav-tabs mb-3 border-0">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active" href="#"
                                        id="default-link">{{'por defecto'}}</a>
                                </li>
                                @foreach (json_decode($language) as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link" href="#"
                                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="col-md-6">
                        @if ($language)
                            <div class="lang_form" id="default-form">
                                <div class="form-group">
                                    <label class="input-label" for="default_name">{{'nombre'}}
                                        ({{ 'por defecto' }})</label>
                                    <input type="text" name="name[]" id="default_name" class="form-control"
                                        placeholder="{{'nuevo artículo'}}">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                <div class="form-group">
                                    <label class="input-label" for="description">{{'breve descripción'}}
                                        ({{ 'por defecto' }})</label>
                                    <textarea type="text" name="description[]" class="form-control ckeditor"></textarea>
                                </div>
                            </div>
                            @foreach(json_decode($language) as $lang)
                                <div class="d-none lang_form" id="{{$lang}}-form">
                                    <div class="form-group">
                                        <label class="input-label" for="{{$lang}}_name">{{'nombre'}}
                                            ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" id="{{$lang}}_name" class="form-control"
                                            placeholder="{{'nuevo artículo'}}">
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">
                                    <div class="form-group">
                                        <label class="input-label" for="description">{{'breve descripción'}}
                                            ({{strtoupper($lang)}})</label>
                                        <textarea type="text" name="description[]" class="form-control ckeditor"></textarea>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div id="default-form">
                                <div class="form-group">
                                    <label class="input-label" for="exampleFormControlInput1">{{'nombre'}}
                                        ({{ 'por defecto' }})</label>
                                    <input type="text" name="name[]" class="form-control"
                                        placeholder="{{'nuevo artículo'}}" required>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                                <div class="form-group">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{'breve descripción'}}</label>
                                    <textarea type="text" name="description[]" class="form-control ckeditor"></textarea>
                                </div>
                            </div>
                        @endif
                        {{-- <div class="form-group mb-0">
                            <label class="input-label">{{'módulo'}}</label>
                            <select name="module_id" id="module_id" required class="form-control js-select2-custom"
                                data-placeholder="{{'seleccionar módulo'}}">
                                <option value="" selected disabled>{{'seleccionar módulo'}}</option>
                                @foreach(\App\Models\Module::parcel()->get() as $module)
                                <option value="{{$module->id}}">{{$module->module_name}}</option>
                                @endforeach
                            </select>
                        </div> --}}
                        <input name="position" value="0" class="initial-hidden">
                    </div>
                    <div class="col-md-6">
                        <div class="h-100 d-flex flex-column">
                            <label class="text-center d-block mt-auto">
                                {{'imagen'}}
                                <small class="text-danger">* ( {{'relación'}} 200x200)</small>
                            </label>
                            <div class="text-center py-3 my-auto">
                                <img class="img--120" id="viewer" src="{{asset('assets/admin/img/900x400/img1.jpg')}}"
                                    alt="image" />
                            </div>
                            <div class="custom-file">
                                <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                    accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                <label class="custom-file-label"
                                    for="customFileEg1">{{'elegir archivo'}}</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label
                                class="input-label text-capitalize">{{'costo de envío por km'}}</label>
                            <input type="number" step=".01" min="0"
                                placeholder="{{'costo de envío por km'}}" class="form-control"
                                name="parcel_per_km_shipping_charge">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label
                                class="input-label text-capitalize">{{'cargo mínimo de envío'}}</label>
                            <input type="number" step=".01" min="0"
                                placeholder="{{'cargo mínimo de envío'}}" class="form-control"
                                name="parcel_minimum_shipping_charge">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="toggle-switch toggle-switch-sm mr-2" for="buy_and_deliver">
                                <input type="checkbox" class="toggle-switch-input" name="buy_and_deliver"
                                    id="buy_and_deliver" value="1">
                                <span class="toggle-switch-label">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                                <span class="toggle-switch-content">
                                    {{ 'comprar y entregar' }}
                                    <small class="text-danger"> * (
                                        {{ 'actívalo si quieres comprar y entregar' }} )</small>
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
                                value="0">
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
                                value="0">
                            <small class="text-muted">El usuario paga el mayor entre (tasa × valor declarado) y esta tarifa mínima.</small>
                        </div>
                    </div>
                    {{-- ────────────────────────────────────────────────────────────── --}}
                    @if ($categoryWiseTax)
                        <div class="col-md-6">

                            <span class="mb-2 d-block title-clr fw-normal">{{ 'Seleccionar tasa impositiva' }}</span>
                            <select name="tax_ids[]" id="tax__rate" class="form-control js-select2-custom"
                                multiple="multiple" required placeholder="Type & Select Tax Rate">
                                @foreach ($taxVats as $taxVat)
                                    <option value="{{ $taxVat->id }}"> {{ $taxVat->name }}
                                        ({{ $taxVat->tax_rate }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-12">
                        <div class="btn--container justify-content-end">
                            <button type="reset" id="reset_btn"
                                class="btn btn--reset">{{'reiniciar'}}</button>
                            <button type="submit"
                                class="btn btn--primary">{{'Agregar categoría de parcela'}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header py-2 border-0">
            <div class="search--button-wrapper">
                <h5 class="card-title">
                    {{'lista de categorías de parcelas'}}
                    <span class="badge badge-soft-dark ml-2" id="itemCount">{{$parcel_categories->total()}}</span>
                </h5>

            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">
                <table id="columnSearchDatatable" class="table table-borderless table-thead-bordered table-align-middle"
                    data-hs-datatables-options='{
                            "isResponsive": false,
                            "isShowPaging": false,
                            "paging":false,
                        }'>
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0">{{ 'SL' }}</th>
                            <th class="border-0">{{'identificación'}}</th>
                            <th class="border-0">{{'nombre'}}</th>
                            <th class="border-0">{{ 'tipo de servicio de paquetería' }}</th>
                            <th class="border-0">{{'módulo'}}</th>
                            <th class="border-0">{{'estado'}}</th>
                            <th class="border-0 text-center">{{'recuento de pedidos'}}</th>
                            <th class="border-0 text-center">{{'costo de envío por km'}}</th>
                            <th class="border-0 text-center">{{'cargo mínimo de envío'}}</th>
                            @if ($categoryWiseTax)
                                <th class="border-0 ">{{ 'IVA/Impuesto' }}</th>
                            @endif
                            <th class="border-0 text-center">{{'acción'}}</th>
                        </tr>
                    </thead>

                    <tbody id="table-div">
                        @foreach($parcel_categories as $key => $category)
                            <tr>
                                <td>{{$key + $parcel_categories->firstItem()}}</td>
                                <td>{{$category->id}}</td>
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{Str::limit($category['name'], 20, '...')}}
                                    </span>
                                </td>
                                <td>
                                    @if($category->buy_and_deliver)
                                        <span class="badge badge-soft-info">{{ 'tipo de paquete comprar y entregar' }}</span>
                                    @else
                                        <span class="badge badge-soft-primary">{{ 'recogida y entrega del tipo de paquete' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{Str::limit($category->module->module_name, 15, '...')}}
                                    </span>
                                </td>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$category->id}}">
                                        <input type="checkbox"
                                            data-url="{{route('admin.parcel.category.status', [$category['id'], $category->status ? 0 : 1])}}"
                                            class="toggle-switch-input redirect-url" id="stocksCheckbox{{$category->id}}"
                                            {{$category->status ? 'checked' : ''}}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <div class="text-center">
                                        {{$category->orders_count}}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        {{$category->parcel_per_km_shipping_charge ? \App\CentralLogics\Helpers::format_currency($category->parcel_per_km_shipping_charge) : 'N/A'}}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        {{$category->parcel_minimum_shipping_charge ? \App\CentralLogics\Helpers::format_currency($category->parcel_minimum_shipping_charge) : 'N/A'}}
                                    </div>
                                </td>
                                @if ($categoryWiseTax)
                                    <td>
                                        <span class="d-block font-size-sm text-body">
                                            @forelse ($category?->taxVats?->pluck('tax.name', 'tax.tax_rate')->toArray() as $key => $tax)
                                                <span> {{ $tax }} : <span class="font-bold">
                                                        ({{ $key }}%)
                                                    </span> </span>
                                                <br>
                                            @empty
                                                <span> {{ 'sin impuestos' }} </span>
                                            @endforelse
                                        </span>
                                    </td>
                                @endif
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <a class="btn action-btn btn--primary btn-outline-primary"
                                            href="{{route('admin.parcel.category.edit', [$category['id']])}}"
                                            title="{{'editar categoría'}}"><i class="tio-edit"></i>
                                        </a>
                                        <a class="btn action-btn btn--danger btn-outline-danger form-alert"
                                            href="javascript:" data-id="category-{{$category['id']}}"
                                            data-message="{{ 'Quiere eliminar esta categoría' }}"
                                            title="{{'eliminar categoría'}}"><i
                                                class="tio-delete-outlined"></i>
                                        </a>
                                        <form action="{{route('admin.parcel.category.destroy', [$category['id']])}}"
                                            method="post" id="category-{{$category['id']}}">
                                            @csrf @method('delete')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if(count($parcel_categories) !== 0)
            <hr>
        @endif
        <div class="page-area">
            {!! $parcel_categories->links() !!}
        </div>
        @if(count($parcel_categories) === 0)
            <div class="empty--data">
                <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                <h5>
                    {{'no se encontraron datos'}}
                </h5>
            </div>
        @endif
    </div>

</div>

@endsection

@push('script_2')
    <script>
        "use strict";
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================

            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        function readURL(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });

        $(".lang_link").click(function (e) {
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);
            console.log(lang);
            $("#" + lang + "-form").removeClass('d-none');
            if (lang == '{{$defaultLang}}') {
                $(".from_part_2").removeClass('d-none');
            }
            else {
                $(".from_part_2").addClass('d-none');
            }
        });

        $('#reset_btn').click(function () {
            $('#module_id').val(null).trigger('change');
            $('#viewer').attr('src', "{{asset('assets/admin/img/900x400/img1.jpg')}}");
        })
    </script>
@endpush