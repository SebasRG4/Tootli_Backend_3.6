@extends('layouts.admin.app')

@section('title','Actualizar repartidor')


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header  mb-15 mt-2">
            <h1 class="page-header-title mb-0 fs-24 text-break">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/edit.png')}}" class="w--26" alt="">
                </span>
                <span>{{'actualizar repartidor'}}</span>
            </h1>
        </div>
        <!-- End Page Header -->

        <form class="validate-form global-ajax-form" action="{{route('admin.users.delivery-man.update',[$deliveryMan['id']])}}" method="post"
                enctype="multipart/form-data">
            @csrf
            <div class="card mb-20">
                <div class="card-header">
                   <div>
                        <h3 class="mb-1">
                            {{ 'Información básica' }}
                        </h3>
                        <p class="mb-0 fs-12">
                            {{ 'Aquí configura toda la información comercial.' }}
                        </p>
                   </div>                    
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="shadow-sm p-xxl-20 p-3 bg-white h-100">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="form-group mb-0">
                                            <label class="input-label" for="exampleFormControlInput1">{{'nombre de pila'}} <span class="form-label-secondary text-danger"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Requerido.'}}"> *
                                                </span>
                                                    </label>
                                                            <input type="text" value="{{$deliveryMan['f_name']}}" name="f_name"
                                                                    class="form-control" placeholder="{{'nombre de pila'}}"
                                                                    required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label" for="exampleFormControlInput1">{{'apellido'}} <span class="form-label-secondary text-danger"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Requerido.'}}"> *
                                                </span>
                                                    </label>
                                                            <input type="text" value="{{$deliveryMan['l_name']}}" name="l_name"
                                                                    class="form-control" placeholder="{{'apellido'}}"
                                                                    required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label" for="exampleFormControlInput1">{{'correo electrónico'}} <span class="form-label-secondary text-danger"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Requerido.'}}"> *
                                                </span>
                                                    </label>
                                                            <input type="email" value="{{$deliveryMan['email']}}" name="email" class="form-control"
                                                                    placeholder="{{ 'Ex:' }} ex@example.com"
                                                                    required>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label" for="exampleFormControlInput1">{{'tipo de repartidor'}} <span class="form-label-secondary text-danger"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Requerido.'}}"> *
                                                </span>
                                                    </label>
                                                            <select name="earning" class="form-control  js-select2-custom" required>
                                                                <option value="1" {{$deliveryMan->earning?'selected':''}}>{{'persona de libre dedicación'}}</option>
                                                                <option value="0" {{$deliveryMan->earning?'':'selected'}}>{{'basado en salario'}}</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-12">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label">{{'tipo de servicio'}} <span class="form-label-secondary text-danger" data-toggle="tooltip" data-placement="right" data-original-title="{{ 'Requerido.' }}"> *</span></label>
                                                            <div class="resturant-type-group d-flex align-items-center gap-3 border rounded p-2">
                                                                <label class="form-check form--check mr-2 mr-md-4">
                                                                    <input class="form-check-input" type="checkbox" name="can_deliver" value="1" {{$deliveryMan->can_deliver?'checked':''}} id="can_deliver">
                                                                    <span class="form-check-label">
                                                                        {{'Repartidor'}}
                                                                    </span>
                                                                </label>
                                                                <label class="form-check form--check mr-2 mr-md-4">
                                                                    <input class="form-check-input" type="checkbox" name="can_drive_taxi" value="1" {{$deliveryMan->can_drive_taxi?'checked':''}} id="can_drive_taxi">
                                                                    <span class="form-check-label">
                                                                        {{'taxista'}}
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-6 taxi-info" style="display: {{$deliveryMan->can_drive_taxi?'block':'none'}};">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label">{{'número de licencia de taxi'}}</label>
                                                            <input type="text" name="taxi_license_number" class="form-control" value="{{$deliveryMan->taxi_license_number}}" placeholder="{{'Ej: 123456789'}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 taxi-info" style="display: {{$deliveryMan->can_drive_taxi?'block':'none'}};">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label">{{'caducidad de la licencia de taxi'}}</label>
                                                            @php($expiry = $deliveryMan->taxi_license_expiry instanceof \Carbon\Carbon ? $deliveryMan->taxi_license_expiry->format('Y-m-d') : $deliveryMan->taxi_license_expiry)
                                                            <input type="date" name="taxi_license_expiry" class="form-control" value="{{$expiry}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6 taxi-info" style="display: {{$deliveryMan->can_drive_taxi?'block':'none'}};">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label">{{'taxi esta verificado'}}</label>
                                                            <div class="d-flex align-items-center">
                                                                <label class="toggle-switch toggle-switch-sm mr-2" for="taxi_is_verified">
                                                                    <input type="checkbox" class="toggle-switch-input" name="taxi_is_verified" id="taxi_is_verified" value="1" {{$deliveryMan->taxi_is_verified?'checked':''}}>
                                                                    <span class="toggle-switch-label">
                                                                        <span class="toggle-switch-indicator"></span>
                                                                    </span>
                                                                </label>
                                                                <span class="mb-0">{{'verificar servicio de taxi'}}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Módulos habilitados --}}
                                                    @if(isset($modules) && $modules->count() > 0)
                                                    <div class="col-sm-12">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label d-block mb-1">
                                                                {{'Módulos habilitados'}}
                                                                <small class="text-muted font-weight-normal">— Módulos que puede atender este repartidor. Si no marcas ninguno, tendrá acceso a todos.</small>
                                                            </label>
                                                            <div class="border rounded p-3 bg-soft-secondary">
                                                                <div class="row g-2">
                                                                    @foreach($modules as $module)
                                                                    <div class="col-sm-4 col-6">
                                                                        <label class="form-check form--check mb-0 d-flex align-items-center gap-2">
                                                                            <input class="form-check-input mt-0" type="checkbox"
                                                                                   name="modules[]"
                                                                                   value="{{ $module->id }}"
                                                                                   id="module_edit_{{ $module->id }}"
                                                                                   {{ isset($selectedModuleIds) && in_array($module->id, $selectedModuleIds) ? 'checked' : '' }}>
                                                                            <span class="form-check-label">
                                                                                {{ $module->module_name }}
                                                                                <span class="badge badge-soft-secondary badge-sm ms-1">{{ $module->module_type }}</span>
                                                                            </span>
                                                                        </label>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="col-sm-6">
                                                        <div class="form-group mb-0">
                                                            <label class="input-label" for="exampleFormControlInput1">{{'Zona de entrega'}} <span class="form-label-secondary text-danger"
                                                                data-toggle="tooltip" data-placement="right"
                                                                data-original-title="{{ 'Requerido.'}}"> 
                                                                  <i class="tio-info text-muted"></i>
                                                                *
                                                                </span>
                                                            </label>
                                            <select name="zone_id" class="form-control  js-select2-custom">
                                            @foreach(\App\Models\Zone::all() as $zone)
                                                @if(isset(auth('admin')->user()->zone_id))
                                                    @if(auth('admin')->user()->zone_id == $zone->id)
                                                        <option value="{{$zone->id}}" {{$zone->id == $deliveryMan->zone_id?'selected':''}}>{{$zone->name}}</option>
                                                    @endif
                                                @else
                                                <option value="{{$zone->id}}" {{$zone->id == $deliveryMan->zone_id?'selected':''}}>{{$zone->name}}</option>
                                                @endif
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group m-0">
                                            <label class="input-label" for="exampleFormControlInput1">{{'Tipo de vehículo'}}<span class="form-label-secondary text-danger"
                                                data-toggle="tooltip" data-placement="right"
                                                data-original-title="{{ 'Requerido.'}}"> *
                                                </span>
                                            </label>
                                            <select name="vehicle_id" class="form-control js-select2-custom h--45px">
                                                <option value="" readonly="true" hidden="true">{{ 'seleccionar vehículo' }}</option>
                                            @foreach(\App\Models\DMVehicle::where('status',1)->get(['id','type']) as $v)
                                                <option value="{{$v->id}}" {{$v->id == $deliveryMan->vehicle_id?'selected':''}}>{{$v->type}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="bg-light2 rounded h-100 d-center">
                                <div class="text-center">
                                    <div class="mb-1">
                                        <h4 class="mb-1">{{ 'Imagen del repartidor' }} <span class="text-danger">*</span> </h4>
                                    </div>
                                    <div class="mx-auto text-center">
                                        @include('admin-views.partials._image-uploader', [
                                                'id' => 'image-input',
                                                'name' => 'image',
                                                'ratio' => '1:1',
                                                'isRequired' => false,
                                                'existingImage' => $deliveryMan['image_full_url'] ?? null,
                                                'imageExtension' => IMAGE_EXTENSION,
                                                'imageFormat' => IMAGE_FORMAT,
                                                'maxSize' => MAX_FILE_SIZE,
                                                ])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                   <div>
                        <h3 class="mb-1">
                            {{ 'Configuración general' }}
                        </h3>
                        <p class="mb-0 fs-12">
                            {{ 'Aquí puede administrar la configuración de tiempo para que coincida con los criterios de su negocio.' }}
                        </p>
                   </div> 
                </div>
                <div class="card-body">
                    <div class="shadow-sm p-xxl-20 p-xl-3 p-2 bg-white mb-20">
                        <div class="mb-20">
                            <h4 class="mb-1">
                                {{ 'Información de identidad' }}
                            </h4>
                            <p class="mb-0 fs-12">
                                {{ 'Configure la zona horaria y el formato de su empresa desde aquí' }}
                            </p>
                        </div> 
                        <div class="bg-light2 rounded p-xxl-20 p-xl-3 p-3 mb-20">
                            <div class="row g-3">
                                <div class="col-sm-6 col-lg-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="exampleFormControlInput1">{{'tipo de identidad'}}<span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Requerido.'}}"> *
                                            </span>
                                        </label>
                                        <select name="identity_type" class="form-control  js-select2-custom">
                                            <option
                                                value="passport" {{$deliveryMan['identity_type']=='passport'?'selected':''}}>
                                                {{'pasaporte'}}
                                            </option>
                                            <option
                                                value="driving_license" {{$deliveryMan['identity_type']=='driving_license'?'selected':''}}>
                                                {{'carnet de conducir'}}
                                            </option>
                                            <option value="nid" {{$deliveryMan['identity_type']=='nid'?'selected':''}}>{{'nid'}}
                                            </option>
                                            <option
                                                value="store_id" {{$deliveryMan['identity_type']=='store_id'?'selected':''}}>
                                                {{'identificación de la tienda'}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="exampleFormControlInput1">{{'numero de identidad'}}<span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Requerido.'}}"> *
                                            </span>
                                        </label>
                                        <input type="text" name="identity_number" value="{{$deliveryMan['identity_number']}}"
                                                class="form-control"
                                                placeholder="{{ 'Ex:' }} DH-23434-LS"
                                                required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-light2 rounded p-xxl-20 p-xl-3 p-3 mb-20">
                            <div class="mb-0">
                                <h4 class="mb-1">
                                    {{ 'Actualizar imagen de identidad' }} <span class="text-danger">*</span>
                                </h4>
                                <p class="mb-0 fs-12">
                                    {{ 'Jpg, jpeg, png, gif, webp. Menos de 2 MB' }} <span class="text-dark">(2:1)</span>
                                </p>
                            </div>
                            <div class="identity_documnet_body multiple_coba-img tabs-slide-wrap position-relative">
                                <div class="tabs-inner pt-3 d-flex gap-3 identity_documnet_wrap" id="coba">
                                    @php($identity_images = json_decode($deliveryMan['identity_image'], true) ?? [])
                                    @foreach($identity_images as $key => $img)
                                        <div class="spartan_item_wrapper size--md existing_image" id="existing_image_{{ $key }}">
                                            <div style="position: relative;">
                                                <label class="file_upload" style="width: 100%; height: 100px; border: 2px dashed #ddd; border-radius: 3px; cursor: pointer; text-align: center; overflow: hidden; padding: 5px; margin-top: 5px; margin-bottom : 5px; position : relative; display: flex; align-items: center; margin: auto; justify-content: center; flex-direction: column;">
                                                    <div class="spartan_item_loader" data-spartanindexloader="0" style=" position: absolute; width: 100%; height: 100px; background: rgba(255,255,255, 0.7); z-index: 22; text-align: center; align-items: center; margin: auto; justify-content: center; flex-direction: column; display : none; font-size : 1.7em; color: #CECECE"><i class="fas fa-sync fa-spin"></i></div>
                                                       <img class="img--100 rounded border" style="width: 100%; margin: 0px auto; vertical-align: middle;" src="{{ $deliveryMan['identity_image_full_url'][$key] }}">
                                                       <a href="javascript:void(0)" style="right: 3px; top: 3px; background: transparent; border-radius: 3px; width: 30px; height: 30px; line-height: 30px; text-align: center; text-decoration: none; color: rgb(255, 7, 0); position: absolute !important;" data-key="{{ $key }}" data-img="{{ is_array($img) ? $img['img'] : $img }}" class="spartan_remove_row remove-existing-image-btn"><i class="tio-add-to-trash"></i></a>
                                                    </div>
                                                </label>
                                                
                                        </div>
                                    @endforeach
                                </div>
                                <div class="arrow-area">
                                    <div class="button-prev align-items-center">
                                        <button type="button"
                                            class="btn btn-click-prev mr-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                            <i class="tio-chevron-left fs-24"></i>
                                        </button>
                                    </div>
                                    <div class="button-next align-items-center pt-5">
                                        <button type="button"
                                            class="btn btn-click-next ml-auto border-0 btn-primary rounded-circle fs-12 p-2 d-center">
                                            <i class="tio-chevron-right fs-24"></i>
                                        </button>
                                    </div>
                                </div>
                            </div> 
                        </div>

                    </div>
                    <div class="shadow-sm p-xxl-20 p-xl-3 p-2 bg-white">
                        <div class="mb-20">
                            <h4 class="mb-1">
                                {{ 'Información de la cuenta' }}
                            </h4>
                            <p class="mb-0 fs-12">
                                {{ 'Configure la zona horaria y el formato de su empresa desde aquí' }}
                            </p>
                        </div> 
                        <div class="bg-light2 rounded p-xxl-20 p-xl-3 p-3">
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="exampleFormControlInput1">{{'teléfono'}}<span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ 'Requerido.'}}"> *
                                            </span>
                                </label>
                                        <input type="tel" id="phone" name="phone" value="{{$deliveryMan['phone']}}" class="form-control"
                                                placeholder="{{ 'Ex:' }} 017********"
                                                required>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="js-form-message form-group mb-0">
                                        <label class="input-label" for="signupSrPassword">{{'Contraseña'}}
                                            <span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
                                    data-original-title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"><i class="tio-info text-muted"></i></span>
                                        </label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control" name="password" id="signupSrPassword"                                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"
                                            placeholder="{{ translate('messages.password_length_placeholder', ['length' => '8+']) }}"
                                            aria-label="8+ characters required"
                                            data-msg="Your password is invalid. Please try again."
                                            data-hs-toggle-password-options='{
                                            "target": [".js-toggle-password-target-1"],
                                            "defaultClass": "tio-hidden-outlined",
                                            "showClass": "tio-visible-outlined",
                                            "classChangeTarget": ".js-toggle-passowrd-show-icon-1"
                                            }'>
                                            <div class="js-toggle-password-target-1 input-group-append">
                                                <a class="input-group-text" href="javascript:;">
                                                    <i class="js-toggle-passowrd-show-icon-1 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="js-form-message form-group mb-0">
                                        <label class="input-label" for="signupSrConfirmPassword">{{'confirmar Contraseña'}}</label>
                                        <div class="input-group input-group-merge">
                                        <input type="password" class="js-toggle-password form-control" name="confirmPassword" id="signupSrConfirmPassword" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"
                                        placeholder="{{ translate('messages.password_length_placeholder', ['length' => '8+']) }}"
                                        aria-label="8+ characters required"
                                                data-msg="Password does not match the confirm password."
                                                data-hs-toggle-password-options='{
                                                "target": [".js-toggle-password-target-2"],
                                                "defaultClass": "tio-hidden-outlined",
                                                "showClass": "tio-visible-outlined",
                                                "classChangeTarget": ".js-toggle-passowrd-show-icon-2"
                                                }'>
                                            <div class="js-toggle-password-target-2 input-group-append">
                                                <a class="input-group-text" href="javascript:;">
                                                <i class="js-toggle-passowrd-show-icon-2 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="btn--container justify-content-end mt-20">
                <button type="reset" id="reset_btn" class="btn btn--reset min-w-120px">{{'reiniciar'}}</button>
                    <button type="submit" class="btn btn--primary min-w-120px"><i class="tio-save"></i> {{'Guardar información'}}</button>
            </div>
        </form>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('assets/admin/js/spartan-multi-image-picker.js')}}"></script>
<script>
    "use strict";
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
        $(function () {
            initSpatanImagePicker();
        });

        function initSpatanImagePicker() {
            let existingImages = $("#coba .existing_image").detach();

            let newCoba = $('<div class="tabs-inner pt-3 d-flex gap-3 identity_documnet_wrap" id="coba"></div>');
            
            $("#coba").replaceWith(newCoba);
            
            newCoba.append(existingImages);

            let existingCount = existingImages.length;
            let maxCount = 5 - existingCount;
            console.log('Existing: ' + existingCount + ', Max: ' + maxCount);

            if (maxCount > 0) {
                $("#coba").spartanMultiImagePicker({
                    fieldName: 'identity_image[]',
                    maxCount: maxCount,
                    rowHeight: '100px',
                    groupClassName: 'spartan_item_wrapper size--md',
                    maxFileSize: {{ MAX_FILE_SIZE }} * 1024 * 1024,
                    placeholderImage: {
                        image: '{{asset('assets/admin/img/400x400/coba-placeholder.png')}}',
                        width: '100%'
                    },
                    dropFileLabel: "Drop Here",
                    onAddRow: function (index, file) {
                        // Handle logic after adding new image if needed
                    },
                    onRenderedPreview: function (index) {

                    },
                    onRemoveRow: function (index) {

                    },
                    onExtensionErr: function (index, file) {
                        toastr.error('Please only input png or jpg type file', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    },
                    onSizeErr: function (index, file) {
                        toastr.error('File size too big', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                });
            }
        }

        $(document).on('click', '.remove-existing-image-btn', function(){
            let key = $(this).data('key');
            let img = $(this).data('img');
            $('#existing_image_' + key).remove();
            $('form').append('<input type="hidden" name="delete_identity_image[]" value="' + img + '">');
            initSpatanImagePicker();
        });
    </script>
    <script>
        $('#can_drive_taxi').on('change', function() {
            if ($(this).is(':checked')) {
                $('.taxi-info').show();
                $('input[name="taxi_license_number"]').attr('required', true);
                $('input[name="taxi_license_expiry"]').attr('required', true);
            } else {
                $('.taxi-info').hide();
                $('input[name="taxi_license_number"]').removeAttr('required');
                $('input[name="taxi_license_expiry"]').removeAttr('required');
            }
        });
        
        // Initial check for validation
        if ($('#can_drive_taxi').is(':checked')) {
            $('input[name="taxi_license_number"]').attr('required', true);
            $('input[name="taxi_license_expiry"]').attr('required', true);
        }
    </script>
@endpush
