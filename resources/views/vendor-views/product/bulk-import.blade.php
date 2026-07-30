@extends('layouts.vendor.app')

@section('title','Importación masiva de artículos')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{asset('assets/admin/css/tags-input.min.css')}}" rel="stylesheet">
@endpush

@section('content')
@php($store_data=\App\CentralLogics\Helpers::get_store_data())
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/items.png')}}" class="w--22" alt="">
                </span>
                <span>
                    {{'artículos de importación masiva'}}
                </span>
            </h1>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="export-steps style-2">
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{'PASO 1'}}</h5>
                            <p>
                                {{'Descargar archivo Excel'}}
                            </p>
                        </div>
                    </div>
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{'PASO 2'}}</h5>
                            <p>
                                {{'Haga coincidir los datos de la hoja de cálculo según las instrucciones'}}
                            </p>
                        </div>
                    </div>
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{'PASO 3'}}</h5>
                            <p>
                                {{'Validar datos y completar la importación.'}}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="jumbotron pt-1 mb-0 pb-4 bg-white">
                    <h3>{{ 'Instrucciones' }} : </h3>
                    <p>{{ '1. Descargue el archivo de formato y complételo con los datos adecuados.' }}</p>

                    <p>{{ '2. Puede descargar el archivo de ejemplo para comprender cómo se deben completar los datos.' }}</p>

                    <p>{{ '3. Una vez que haya descargado y completado el archivo de formato, cárguelo en el formulario a continuación y envíelo.' }}</p>
                    <p>{{ '4. Puede obtener la identificación de la tienda, la identificación del módulo y la identificación de la unidad de su lista; ingrese las identificaciones correctas.' }}</p>

                    <p>{{ '5. Para el artículo de comercio electrónico, la hora de inicio y finalización será 00:00:00 y 23:59:59' }}</p>

                    <p>{{ '6. Puede cargar las imágenes de su producto en la carpeta del producto desde la galería y copiar la ruta de la imagen.' }}</p>
                    <p>{{ '7. El nombre del archivo de imagen debe tener 30 caracteres.' }}</p>

                </div>
                <div class="text-center pb-4">
                    <h3 class="mb-3 export--template-title">{{'descargar plantilla de hoja de cálculo'}}</h3>
                    <div class="btn--container justify-content-center export--template-btns">

                        @if ($store_data->module->module_type == 'food')
                            <a href="{{asset('assets/restaurant_panel/foods_bulk_format.xlsx')}}" download="" class="btn btn-dark">{{'plantilla con datos existentes'}}</a>
                        @else
                            <a href="{{asset('assets/restaurant_panel/items_bulk_format.xlsx')}}" download="" class="btn btn-dark">{{'plantilla con datos existentes'}}</a>
                        @endif

                        <a href="{{asset('assets/restaurant_panel/items_bulk_format_nodata.xlsx')}}" download="" class="btn btn-dark">{{'plantilla sin datos'}}</a>
                    </div>
                </div>
            </div>
        </div>
        <form class="product-form" id="import_form" action="{{route('vendor.item.bulk-import')}}" method="POST"
                enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="button" id="btn_value">
            <div class="card mt-2 rest-part">
                <div class="card-body">
                    <h4 class="mb-3">{{'importar archivo de elementos'}}</h4>
                    <div class="custom-file custom--file">
                        <input type="file" name="products_file" class="form-control" id="products_file">
                        <label class="custom-file-label" for="products_file">{{ 'Elija archivo' }}</label>
                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button id="reset_btn" type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="submit" name="button" value="update" class="btn btn--warning submit_btn">{{'actualizar'}}</button>
                        <button type="submit" name="button" value="import" class="btn btn--primary submit_btn">{{'Importar'}}</button>
                    </div>
                </div>
            </div>
        </form>

        <form action="javascript:" method="post" id="item_form" enctype="multipart/form-data">
            <div id="food_variation_section" style="display: none">
                <div class="card mt-2 rest-part">
                    <div class="card-header">
                        <h5 class="card-title">
                            {{-- <span class="card-header-icon">
                                <i class="tio-canvas-text"></i>
                            </span> --}}
                            <span>{{ 'generador de variaciones de alimentos' }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-12">
                                <div id="add_new_option">
                                </div>
                                <br>
                                <div class="mt-2">
                                    <a class="btn btn-outline-success"
                                        id="add_new_option_button">{{ 'agregar nueva variación' }}</a>
                                </div> <br><br>
                            </div>
                        </div>
                        <div class="btn--container justify-content-end mb-3">
                            <button type="submit" class="btn btn--primary">{{'generar'}}</button>
                        </div>
                        <textarea name="" id="food_variation_outpot" class="form-control" rows="5" readonly></textarea>
                    </div>
                </div>
            </div>
        </form>
<br>
        <form action="javascript:" method="post" id="item_form_2" enctype="multipart/form-data">
            <div id="attribute_section" style="display: none">
                <h4 class="mb-3">{{'Generar variación'}}</h4>
                <div class="card card mt-2 rest-part">
                    <div class="card-header border-0 p-0">
                        <div class="alert w-100 alert-soft-primary alert-dismissible fade show d-flex m-0" role="alert">
                            <div>
                                <img src="{{asset('assets/admin/img/icons/intel.png')}}" width="22" alt="">
                            </div>
                            <div class="w-0 flex-grow-1 pl-3">
                                <strong>{{ '¡Atención!' }}</strong>
                              {{ 'Debe generar variaciones desde este generador si desea agregar variaciones a sus productos. Debe copiar desde el archivo específico y pegarlo en la columna específica en su hoja de Excel. De lo contrario, podría obtener un error 500 si intercambia o ingresa datos no válidos. Y si desea dejarlo vacío, debe ingresar una matriz vacía [ ].' }}
                            </div>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <label class="input-label m-0">{{ 'atributo' }}<span class="input-label-secondary"></span></label>
                            <button type="submit" class="btn btn--primary">{{'generar valor'}}</button>
                        </div>
                        <div class="row g-2">
                            <div class="col-lg-6">
                                <div class="form-group mb-0">
                                    <select name="attribute_id[]" id="choice_attributes"
                                        class="form-control js-select2-custom" multiple="multiple">
                                        @foreach (\App\Models\Attribute::orderBy('name')->get() as $attribute)
                                            <option value="{{ $attribute['id'] }}">{{ $attribute['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="customer_choice_options pt-3" id="customer_choice_options">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="variant_combination" id="variant_combination">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="">{{ 'variante generada' }} <span class="form-label-secondary text-danger " data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Este campo es para variación generada. cópielos y péguelos en una hoja de Excel' }} "><img src="{{ asset('assets/admin/img/info-circle.svg') }}" alt="Veg non veg"> * </span></label>
                                <textarea name="" id="variation_output" class="form-control" rows="5" readonly></textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="">{{ 'Opción de elección generada' }} <span class="form-label-secondary text-danger " data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Se requiere la opción de elección si está utilizando una variación del producto.' }}"><img src="{{ asset('assets/admin/img/info-circle.svg') }}" alt="Veg non veg"> * </span></label>
                                <textarea name="" id="choice_output" class="form-control" rows="5" readonly></textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="">{{ 'Campo de atributos generados' }} <span class="form-label-secondary text-danger " data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Los atributos son obligatorios si utiliza una variación del producto.' }}"><img src="{{ asset('assets/admin/img/info-circle.svg') }}" alt="Veg non veg"> * </span></label>
                                <textarea name="" id="attributes" class="form-control" rows="5" readonly></textarea>
                            </div>
                        </div>

                        <div class="btn--container justify-content-end mt-2 mb-2">
                            <button type="reset" class="btn btn--reset">{{'Reiniciar'}}</button>
                        </div>


                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script_2')
    <script src="{{ asset('assets/admin') }}/js/tags-input.min.js"></script>
    <script>
        "use strict";
        let count = 0;
        let countRow = 0;
        let element = 0;
        $(document).ready(function() {
            @if($module_type== 'food')
            $('#food_variation_section').show();
            $('#attribute_section').hide();
            @else
            $('#food_variation_section').hide();
            $('#attribute_section').show();
            @endif
            $("#add_new_option_button").click(function(e) {
                count++;
                let add_option_view = `
                <div class="card view_new_option mb-2" >
                    <div class="card-header">
                        <label for="" id=new_option_name_` + count + `> {{ 'agregar nuevo' }}</label>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-lg-3 col-md-6">
                                <label for="">{{ 'nombre' }}</label>
                                 <input required name=options[` + count +
                    `][name] class="form-control new_option_name" type="text" data-count="`+
                    count +`">
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="form-group">
                                    <label class="input-label text-capitalize d-flex alig-items-center"><span class="line--limit-1">{{ 'tipo de selección' }} </span>
                                    </label>
                                    <div class="resturant-type-group border">
                                        <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input show_min_max" data-count="`+count+`" type="radio" value="multi"
                                                name="options[` + count + `][type]" id="type` + count +
                    `" checked
                                                >
                                                <span class="form-check-label">
                                                    {{ 'Selección múltiple' }}
                    </span>
                </label>

                <label class="form-check form--check mr-2 mr-md-4">
                    <input class="form-check-input hide_min_max" data-count="`+count+`" type="radio" value="single"
                                                name="options[` + count + `][type]" id="type` + count +
                    `"
                                                >
                                                <span class="form-check-label">
                                                    {{ 'Selección única' }}
                    </span>
                </label>
        </div>
    </div>
    </div>
    <div class="col-12 col-lg-6">
    <div class="row g-2">
        <div class="col-sm-6 col-md-4">
            <label for="">{{ 'mín.' }}</label>
                                        <input id="min_max1_` + count + `" required  name="options[` + count + `][min]" class="form-control" type="number" min="1">
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label for="">{{ 'máx.' }}</label>
                                        <input id="min_max2_` + count + `"   required name="options[` + count + `][max]" class="form-control" type="number" min="1">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="d-md-block d-none">&nbsp;</label>
                                            <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <input id="options[` + count + `][required]" name="options[` +
                    count + `][required]" type="checkbox">
                                                <label for="options[` + count + `][required]" class="m-0">{{ 'Requerido' }}</label>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-danger btn-sm delete_input_button"
                                                    title="{{ 'Borrar' }}">
                                                    <i class="tio-add-to-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="option_price_` + count + `" >
                            <div class="border rounded p-3 pb-0 mt-3">
                                <div  id="option_price_view_` + count + `">
                                    <div class="row g-3 add_new_view_row_class mb-3">
                                        <div class="col-md-4 col-sm-6">
                                            <label for="">{{ 'Nombre de la opción' }}</label>
                                            <input class="form-control" required type="text" name="options[` +
                    count +
                    `][values][0][label]" id="">
                                        </div>
                                        <div class="col-md-4 col-sm-6">
                                            <label for="">{{ 'Precio adicional' }}</label>
                                            <input class="form-control" required type="number" min="0" step="0.01" name="options[` +
                    count + `][values][0][optionPrice]" id="">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3 p-3 mr-1 d-flex "  id="add_new_button_` + count +
                    `">
                                   <button type="button" class="btn btn--primary btn-outline-primary add_new_row_button" data-count="`+
                    count +`" >{{ 'Agregar nueva opción' }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                $("#add_new_option").append(add_option_view);
            });
        });

        function show_min_max(data) {
            $('#min_max1_' + data).removeAttr("readonly");
            $('#min_max2_' + data).removeAttr("readonly");
            $('#min_max1_' + data).attr("required", "true");
            $('#min_max2_' + data).attr("required", "true");
            $('#min_max1_' + data).attr("min", "1");
            $('#min_max2_' + data).attr("min", "2");
        }

        function hide_min_max(data) {
            $('#min_max1_' + data).val(null).trigger('change');
            $('#min_max2_' + data).val(null).trigger('change');
            $('#min_max1_' + data).attr("readonly", "true");
            $('#min_max2_' + data).attr("readonly", "true");
            $('#min_max1_' + data).removeAttr("required");
            $('#min_max2_' + data).removeAttr("required");
            $('#min_max1_' + data).removeAttr("min");
            $('#min_max2_' + data).removeAttr("min");
        }

        $(document).on('change', '.show_min_max', function () {
            let data = $(this).data('count');
            show_min_max(data);
        });

        $(document).on('change', '.hide_min_max', function () {
            let data = $(this).data('count');
            hide_min_max(data);
        });




        function new_option_name(value, data) {
            $("#new_option_name_" + data).empty();
            $("#new_option_name_" + data).text(value)
            console.log(value);
        }

        function removeOption(e) {
            element = $(e);
            element.parents('.view_new_option').remove();
        }

        $(document).on('click', '.delete_input_button', function () {
            let e = $(this);
            removeOption(e);
        });

        function deleteRow(e) {
            element = $(e);
            element.parents('.add_new_view_row_class').remove();
        }

        $(document).on('click', '.deleteRow', function () {
            let e = $(this);
            deleteRow(e);
        });


        function add_new_row_button(data) {
            let maxIndex = -1;
            $('#option_price_view_' + data).find('input[name^="options[' + data + '][values]"]').each(function() {
                let name = $(this).attr('name');
                let match = name.match(/options\[\d+\]\[values\]\[(\d+)\]/);
                if (match) {
                    let index = parseInt(match[1], 10);
                    if (index > maxIndex) {
                        maxIndex = index;
                    }
                }
            });
            countRow = maxIndex + 1;
            let add_new_row_view = `
        <div class="row add_new_view_row_class mb-3 position-relative pt-3 pt-sm-0">
            <div class="col-md-4 col-sm-5">
                    <label for="">{{ 'Nombre de la opción' }}</label>
                    <input class="form-control" required type="text" name="options[` + data + `][values][` +
                countRow + `][label]" id="">
                </div>
                <div class="col-md-4 col-sm-5">
                    <label for="">{{ 'Precio adicional' }}</label>
                    <input class="form-control"  required type="number" min="0" step="0.01" name="options[` +
                data +
                `][values][` + countRow + `][optionPrice]" id="">
                </div>
                <div class="col-sm-2 max-sm-absolute">
                    <label class="d-none d-sm-block">&nbsp;</label>
                    <div class="mt-1">
                        <button type="button" class="btn btn-danger btn-sm deleteRow"
                            title="{{ 'Borrar' }}">
                            <i class="tio-add-to-trash"></i>
                        </button>
                    </div>
            </div>
        </div>`;
            $('#option_price_view_' + data).append(add_new_row_view);

        }

        $(document).on('click', '.add_new_row_button', function () {
            let data = $(this).data('count');
            add_new_row_button(data);
        });

        $(document).on('keyup', '.new_option_name', function () {
            let data = $(this).data('count');
            let value = $(this).val();
            new_option_name(value, data);
        });

        $('#choice_attributes').on('change', function() {
            $('#customer_choice_options').html(null);
            $('#variant_combination').html(null);
            $.each($("#choice_attributes option:selected"), function() {
                if ($(this).val().length > 50) {
                    toastr.error(
                        '{{ 'validation.max.string\', [\'atributo\' => traducir(\'variación', 'max' => '50']) }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    return false;
                }
                add_more_customer_choice_option($(this).val(), $(this).text());
            });
        });

        function add_more_customer_choice_option(i, name) {
        let n = name;

        $('#customer_choice_options').append(
            `<div class="__choos-item"><div><input type="hidden" name="choice_no[]" value="${i}"><input type="text" class="form-control d-none" name="choice[]" value="${n}" placeholder="{{ 'título de elección' }}" readonly> <label class="form-label">${n}</label> </div><div><input type="text" class="form-control combination_update" name="choice_options_${i}[]" placeholder="{{ 'ingrese los valores de elección' }}" data-role="tagsinput"></div></div>`
        );
        $("input[data-role=tagsinput], select[multiple][data-role=tagsinput]").tagsinput();
    }

        function combination_update() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: "POST",
                url: "{{ route('vendor.item.variant-combination') }}",
                data: $('#item_form_2').serialize() + '&stock=' + true,
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    $('#loading').hide();
                    $('#variant_combination').html(data.view);
                    if (data.length < 1) {
                        $('input[name="current_stock"]').attr("readonly", false);
                    }
                }
            });
        }

        $(document).on('change', '.combination_update', function () {
            combination_update();
        });

        $('#item_form_2').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('vendor.item.variation-generate') }}',
                data: $('#item_form_2').serialize(),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    $('#loading').hide();
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                    $('#variation_output').val(data.variation),
                    $('#choice_output').val(data.choice_options),
                    $('#attributes').val(data.attributes)
                    }
                }
            });
        });

        $('#item_form').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('vendor.item.food-variation-generate') }}',
                data: $('#item_form').serialize(),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    $('#loading').hide();
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        $('#food_variation_outpot').val(data.variation)
                    }
                }
            });
        });

        $('#reset_btn').click(function(){
            $('#bulk__import').val(null);
        })


        $(document).on("click", ".submit_btn", function(e){
            e.preventDefault();
            let data = $(this).val();
            myFunction(data)
        });


        function myFunction(data) {
            Swal.fire({
                title: '{{ '¿Está seguro?' }}' ,
                text: "{{ 'Tú quieres' }}" +data,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{'No'}}',
                confirmButtonText: '{{'Sí'}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $('#btn_value').val(data);
                    $("#import_form").submit();
                }
            })
        }
    </script>
@endpush
