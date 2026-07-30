@extends('layouts.vendor.app')

@section('title','Agregar nuevo repartidor')

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/deliveryman.png')}}" class="w--30" alt="">
                </span>
                <span>
                    {{'agregar nuevo repartidor'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <form  action="javascript:" method="post" enctype="multipart/form-data" id="deliaveryman_form" class="js-validate">
            @csrf
            <div class="row g-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="tio-user"></i> {{'información general'}}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4 col-sm-6">
                                    <div>
                                        <label class="input-label" for="f_name">{{'nombre de pila'}}</label>
                                        <input id="f_name"  type="text"  name="f_name" class="form-control" placeholder="{{'nombre de pila'}}"
                                                required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div>
                                        <label class="input-label" for="l_name">{{'apellido'}}</label>
                                        <input id="l_name"  type="text" name="l_name" class="form-control" placeholder="{{'apellido'}}"
                                                required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="email">{{'correo electrónico'}}</label>
                                        <input id="email"  type="email" name="email" class="form-control" placeholder="{{ 'Ex:' }} ex@example.com"
                                                required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div>
                                        <label class="input-label" for="identity_type">{{'tipo de identidad'}}</label>
                                        <select name="identity_type" id="identity_type" class="form-control">
                                            <option value="passport">{{'pasaporte'}}</option>
                                            <option value="driving_license">{{'carnet de conducir'}}</option>
                                            <option value="nid">{{'nid'}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div>
                                        <label class="input-label" for="identity_number">{{'numero de identidad'}}</label>
                                        <input type="text" id="identity_number" name="identity_number" class="form-control"
                                                placeholder="{{ 'Ex:' }} DH-23434-LS"
                                                required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="form-label m-0">{{'imagen de identidad'}}
                            <small class="text-danger">* {{'(Relación 190x120)'}}</small></h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="form-group">
                                <div>
                                    <div class="btn--container" id="coba"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="form-label m-0">{{'imagen del repartidor'}}
                            <small class="text-danger">* ( {{'relación'}} 1:1 )</small></h5>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="text-center my-auto py-3">
                                <img class="img--100" id="viewer" src="{{asset('assets/admin/img/400x400/img2.jpg')}}" alt="delivery-man image"/>
                            </div>
                            <div class="custom-file">
                                <input type="file" name="image" id="customFileEg1" class="custom-file-input read-url"
                                        accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                <label class="custom-file-label" for="customFileEg1">{{'elegir archivo'}}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="tio-user"></i> {{'información de cuenta'}}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4 col-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label" for="phone">{{'teléfono'}}</label>
                                        <div class="input-group">
                                            <input type="tel" name="phone" id="phone" placeholder="{{ 'Ex:' }} 017********" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="js-form-message form-group mb-0">
                                        <label class="input-label" for="signupSrPassword">{{'Contraseña'}} <span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
        data-original-title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"><img src="{{ asset('assets/admin/img/info-circle.svg') }}" alt="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"></span></label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control" name="password" id="signupSrPassword" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"
                                            placeholder="{{ translate('messages.password_length_placeholder', ['length' => '8+']) }}"
                                            aria-label="8+ characters required" required
                                            data-msg="Your password is invalid. Please try again."
                                            data-hs-toggle-password-options='{
                                            "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                            "defaultClass": "tio-hidden-outlined",
                                            "showClass": "tio-visible-outlined",
                                            "classChangeTarget": ".js-toggle-passowrd-show-icon-1"
                                            }'>
                                            <div class="js-toggle-password-target-1 input-group-append">
                                                <a class="input-group-text" href="javascript:">
                                                    <i class="js-toggle-passowrd-show-icon-1 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="js-form-message form-group mb-0">
                                        <label class="input-label" for="signupSrConfirmPassword">{{'confirmar Contraseña'}}</label>
                                        <div class="input-group input-group-merge">
                                        <input type="password" class="js-toggle-password form-control" name="confirmPassword" id="signupSrConfirmPassword" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"
                                        placeholder="{{ translate('messages.password_length_placeholder', ['length' => '8+']) }}"
                                        aria-label="8+ characters required" required
                                                data-msg="Password does not match the confirm password."
                                                data-hs-toggle-password-options='{
                                                "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                                "defaultClass": "tio-hidden-outlined",
                                                "showClass": "tio-visible-outlined",
                                                "classChangeTarget": ".js-toggle-passowrd-show-icon-2"
                                                }'>
                                            <div class="js-toggle-password-target-2 input-group-append">
                                                <a class="input-group-text" href="javascript:">
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
                <div class="col-12">
                    <div class="btn--container justify-content-end">
                        <button type="reset" id="reset_btn" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="submit" class="btn btn--primary">{{'entregar'}}</button>
                    </div>
                </div>
            </div>

        </form>
    </div>

@endsection

@push('script_2')

<script src="{{asset('assets/admin/js/spartan-multi-image-picker.js')}}"></script>
<script type="text/javascript">
    "use strict";
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

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
            $("#coba").spartanMultiImagePicker({
                fieldName: 'identity_image[]',
                maxCount: 5,
                rowHeight: '120px',
                groupClassName: '',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{asset('assets/admin/img/400x400/img2.jpg')}}',
                    width: '100%'
                },
                dropFileLabel: "{{'Caer aquí'}}",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function () {
                    toastr.error('{{'Por favor ingrese solo archivos tipo png o jpg'}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function () {
                    toastr.error('{{'Tamaño de archivo demasiado grande'}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });

        $('#deliaveryman_form').on('submit', function () {
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('vendor.delivery-man.store')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else if(data.message){
                        toastr.success(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{route('vendor.delivery-man.list')}}';
                        }, 2000);
                    }
                }
            });
        });

        $('#reset_btn').click(function(){
            $('#viewer').attr('src','{{asset('assets/admin/img/400x400/img2.jpg')}}');
            $("#coba").empty().spartanMultiImagePicker({
            fieldName: 'identity_image[]',
            maxCount: 5,
            rowHeight: '120px',
            groupClassName: 'col-6 spartan_item_wrapper size--md',
            maxFileSize: '',
            placeholderImage: {
                image: '{{asset('assets/admin/img/400x400/img2.jpg')}}',
                width: '100%'
            },
            dropFileLabel: "{{'Caer aquí'}}",
            onAddRow: function (index, file) {

            },
            onRenderedPreview: function (index) {

            },
            onRemoveRow: function (index) {

            },
            onExtensionErr: function () {
                toastr.error('{{'Por favor ingrese solo archivos tipo png o jpg'}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            onSizeErr: function () {
                toastr.error('{{'tamaño de archivo demasiado grande'}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        });
        })
    </script>
@endpush
