@extends('layouts.admin.app')

@section('title','Importación masiva de complementos')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/addon.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{'importación masiva de complementos'}}
                </span>
            </h1>
        </div>

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
                                        {{ 'Complete los datos según el formato.' }}
                                    </li>
                                    <li>
                                        {{ 'Puede obtener la identificación de la tienda de su lista, ingrese las identificaciones correctas'}}
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


                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center pb-4">
                    <h3 class="mb-3 export--template-title font-regular">{{'descargar plantilla de hoja de cálculo'}}</h3>
                    <div class="btn--container justify-content-center export--template-btns">

                        <a href="{{asset('assets/addons_bulk_format.xlsx')}}" download="" class="btn btn--primary btn-outline-primary">{{ 'Plantilla con datos existentes' }}</a>
                        <a href="{{asset('assets/addons_bulk_format_nodata.xlsx')}}" download="" class="btn btn--primary">{{ 'Plantilla sin datos' }}</a>

                    </div>
                </div>
            </div>
        </div>

    <form class="product-form" id="import_form"  method="POST"
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
                    <h5 class="text-capitalize mb-3">{{ 'Importar archivo de complementos' }}</h5>
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
</div>
@endsection

@push('script_2')
<script src="{{asset('assets/admin')}}/js/view-pages/addon-import-export.js"></script>
<script>
    "use strict";
    $('#reset_btn').click(function(){
    $('#products_file').val('');
    $('.filename').text('{{'Deben ser archivos de Excel usando nuestra plantilla de Excel anterior'}}');
})

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

    $('.update_or_import').on('click',function (e){
        e.preventDefault();
        let upload_type = $('input[name="upload_type"]:checked').val();
        let form = document.getElementById('import_form');
        if (upload_type === 'update') {
            form.action = '{{ route('admin.addon.bulk-update') }}';
        } else {
            form.action = '{{ route('admin.addon.bulk-import') }}';
        }
        myFunction(upload_type)
    });


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


</script>
@endpush
