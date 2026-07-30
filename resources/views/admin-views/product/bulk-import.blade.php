@extends('layouts.admin.app')

@section('title','Importación masiva de artículos')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('assets/admin/css/tags-input.min.css') }}" rel="stylesheet">
@endpush

@section('content')
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
        <!-- Content Row -->
        <div class="card">
            <div class="card-body">
                <div class="export-steps-2">
                    <div class="row g-4">
                        <div class="col-sm-6 col-lg-4">
                            <div class="export-steps-item-2 h-100">
                                <div class="top">
                                    <div>
                                        <h3 class="fs-20">{{'Paso 1'}}</h3>
                                        <div>
                                            {{'Descargar archivo Excel'}}
                                        </div>
                                    </div>
                                    <img src="{{asset('assets/admin/img/bulk-import-1.png')}}" alt="">
                                </div>
                                <h4>{{ 'Instrucción' }}</h4>
                                <ul class="m-0 pl-4">
                                    <li>
                                        {{ 'Descargue el archivo de formato y rellénelo con los datos adecuados.' }}
                                    </li>
                                    <li>
                                        {{ 'Puede descargar el archivo de ejemplo para comprender cómo se deben completar los datos.' }}
                                    </li>
                                    <li>
                                        {{ 'Tienes que subir el archivo de Excel.' }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="export-steps-item-2 h-100">
                                <div class="top">
                                    <div>
                                        <h3 class="fs-20">{{'Paso 2'}}</h3>
                                        <div>
                                            {{'Haga coincidir los datos de la hoja de cálculo según las instrucciones'}}
                                        </div>
                                    </div>
                                    <img src="{{asset('assets/admin/img/bulk-import-2.png')}}" alt="">
                                </div>
                                  <h4>{{ 'Instrucción' }}</h4>
                                <ul class="m-0 pl-4">
                                    <li>
                                        {{ 'Complete los datos según el formato y validaciones.' }}
                                    </li>
                                    <li>
                                        {{ 'Puede obtener la identificación del módulo de identificación de la tienda y la identificación de la unidad de su lista; ingrese las identificaciones correctas.' }}
                                    </li>
                                    <li>
                                        {{ 'Para el artículo de comercio electrónico, la hora de inicio y finalización será 00:00:00 y 23:59:59' }}
                                    </li>
                                    <li>
                                        {{ 'Si desea crear un producto con variación, simplemente cree variaciones desde la sección generar variación a continuación y haga clic en generar valor.' }}
                                    </li>
                                    <li>
                                        {{ 'Copie el valor y pegue la variación del nombre de la columna del archivo de la hoja de cálculo en la fila del producto seleccionado.' }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="export-steps-item-2 h-100">
                                <div class="top">
                                    <div>
                                        <h3 class="fs-20">{{'Paso 3'}}</h3>
                                        <div>
                                            {{'Validar datos y completar la importación.'}}
                                        </div>
                                    </div>
                                    <img src="{{asset('assets/admin/img/bulk-import-3.png')}}" alt="">
                                </div>
                                  <h4>{{ 'Instrucción' }}</h4>
                                <ul class="m-0 pl-4">
                                    <li>
                                       {{ 'En la sección de carga de archivos de Excel, primero seleccione la opción de carga.' }}
                                    </li>
                                    <li>
                                       {{ 'Sube tu archivo en formato .xls, .xlsx.' }}
                                    </li>
                                    <li>
                                       {{ 'Finalmente haga clic en el botón cargar.' }}
                                    </li>
                                    <li>
                                       {{ 'Puede cargar las imágenes de su producto en la carpeta del producto desde la galería y copiar la ruta de la imagen.' }}
                                    </li>
                                    <li>
                                       {{ 'El nombre del archivo de imagen debe tener 30 caracteres.' }}
                                    </li>


                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center pb-4">
                    <h3 class="mb-3 export--template-title font-regular">{{'descargar plantilla de hoja de cálculo'}}</h3>
                    <div class="btn--container justify-content-center export--template-btns">
                        @if($module_type== 'food')
                        <a href="{{asset('assets/foods_bulk_format.xlsx')}}" download="" class="btn btn--primary btn-outline-primary">{{'Con datos actuales'}}</a>
                        @else
                        <a href="{{asset('assets/items_bulk_format.xlsx')}}" download="" class="btn btn--primary btn-outline-primary">{{'Con datos actuales'}}</a>
                            @endif
                        <a href="{{asset('assets/items_bulk_format_nodata.xlsx')}}" download="" class="btn btn--primary">{{'Sin ningún dato'}}</a>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <form class="product-form" id="import_form" action="{{route('admin.item.bulk-import')}}" method="POST"
                enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="button" id="btn_value">
            <div class="card mt-2 rest-part">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <h5 class="text-capitalize mb-3">{{ 'Seleccione el tipo de carga de datos' }}</h5>
                            <div class="module-radio-group border rounded">
                                <label class="form-check form--check">
                                    <input class="form-check-input "   value="import" type="radio" name="upload_type" checked>
                                    <span class="form-check-label py-20">
                                        {{ 'Cargar nuevos datos' }}
                                    </span>
                                </label>
                                <label class="form-check form--check">
                                    <input class="form-check-input " value="update" type="radio" name="upload_type">
                                    <span class="form-check-label py-20">
                                        {{ 'Actualizar datos existentes' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <h5 class="text-capitalize mb-3">{{ 'Importar archivo de elementos' }}</h5>
                            <label class="uploadDnD d-block">
                                <div class="form-group inputDnD input_image input_image_edit position-relative">
                                    <div class="upload-text">
                                        <div>
                                            <img src="{{asset('assets/admin/img/bulk-import-3.png')}}" alt="">
                                        </div>
                                        <div class="filename">{{'Deben ser archivos de Excel usando nuestra plantilla de Excel anterior'}}</div>
                                    </div>
                                    <input type="file" name="products_file" class="form-control-file text--primary font-weight-bold action-upload-section-dot-area" id="products_file">
                                </div>
                            </label>

                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button id="reset_btn" type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="button" class="btn btn--primary update_or_import">{{'Subir'}}</button>
                    </div>
                </div>
            </div>
        </form>
        <form action="javascript:" method="post" id="item_form" enctype="multipart/form-data">
            <div id="food_variation_section" style="display: none">
                <div class="card mt-2 rest-part">
                    <div class="card-header">
                        <h5 class="card-title">
                            <span>{{ 'generador de variaciones de alimentos' }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-12">
                                <div id="add_new_option">
                                </div>
                                <br>
                                <div class="mt-2 text-center">
                                    <a class="btn btn--primary"
                                        id="add_new_option_button">{{ 'agregar nueva variación' }}</a>
                                </div> <br><br>
                            </div>
                        </div>
                        <div class="btn--container justify-content-end mb-3">
                            <button type="submit" class="btn btn--warning">{{'Generar valor'}}</button>
                        </div>
                        <textarea name="" id="food_variation_outpot" class="form-control" rows="5" readonly></textarea>
                        <div class="btn--container justify-content-end mt-2 mb-2">
                            <button type="reset" class="btn btn--reset">{{'Reiniciar'}}</button>
                        </div>
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
    <script src="{{asset('assets/admin')}}/js/view-pages/product-import.js"></script>
<script>
    "use strict";
    $('.update_or_import').on("click", function () {
    let upload_type = $('input[name="upload_type"]:checked').val();
    myFunction(upload_type)
});
$('#reset_btn').click(function(){
    $('#products_file').val('');
    $('.filename').text('{{'Deben ser archivos de Excel usando nuestra plantilla de Excel anterior'}}');
})
    $(".action-upload-section-dot-area").on("change", function () {
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = () => {
                let imgName = this.files[0].name;
                $(this).closest(".uploadDnD").find('.filename').text(imgName);
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

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
                                            <div class="d-flex align-items-center justify-content-between pt-2">
                                            <div class="form-check form--check">
                                                <input class="form-check-input" id="options[` + count + `][required]" name="options[` +
                count + `][required]" type="checkbox">
                                                <label for="options[` + count + `][required]" class="m-0">{{ 'Requerido' }}</label>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-outline-danger btn-sm delete_input_button"
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
                            <div class="__bg-F8F9FC-card border rounded p-3 pb-0 mt-3">
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
                                <div id="add_new_button_` + count +
                `">
                                   <button type="button" class="text-success bg-transparent border-0 p-0 add_new_row_button" data-count="`+
                count +`" > <i class="tio-add-square"></i> {{ 'Agregar nueva opción' }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

            $("#add_new_option").append(add_option_view);
        });
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
            '<div class="row gy-1"><div class="col-sm-3"><input type="hidden" name="choice_no[]" value="' + i +
            '"><input type="text" class="form-control" name="choice[]" value="' + n +
            '" placeholder="{{ 'título de elección' }}" readonly></div><div class="col-sm-9"><input type="text" class="form-control combination_update" name="choice_options_' +
            i +
            '[]" placeholder="{{ 'ingrese los valores de elección' }}" data-role="tagsinput"></div></div>'
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
            url: "{{ route('admin.item.variant-combination') }}",
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
            url: '{{ route('admin.item.variation-generate') }}',
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
                    $('#variation_output').val(data.variation)
                    $('#choice_output').val(data.choice_options)
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
            url: '{{ route('admin.item.food-variation-generate') }}',
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

    function myFunction(data) {
        Swal.fire({
        title: '{{ '¿Está seguro?' }}' ,
        text: "{{ 'Tú quieres' }}" +data + " {{ 'Datos.' }}",
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
