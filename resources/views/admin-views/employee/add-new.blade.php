@extends('layouts.admin.app')
@section('title','Agregar empleado')
@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Heading -->
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/role.png')}}" class="w--26" alt="">
            </span>
            <span>
                {{'agregar nuevo empleado'}}
            </span>
        </h1>
    </div>
    <!-- Content Row -->
    <form action="{{route('admin.users.employee.add-new')}}" method="post" enctype="multipart/form-data" class="js-validate">
        @csrf
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">
                    <span class="card-header-icon">
                        <i class="tio-user"></i>
                    </span>
                    <span>{{'información general'}}</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="input-label qcont" for="fname">{{'nombre de pila'}}<span class="form-label-secondary text-danger"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Requerido.'}}"> *
                                                        </span>
                                                    </label>
                                <input type="text" name="f_name" class="form-control" id="fname"
                                    placeholder="{{'nombre de pila'}}" value="{{old('f_name')}}" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="input-label qcont" for="lname">{{'apellido'}}<span class="form-label-secondary text-danger"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Requerido.'}}"> *
                                                        </span>
                                                     </label>
                                <input type="text" name="l_name" class="form-control" id="lname" value="{{old('l_name')}}"
                                    placeholder="{{'apellido'}}" value="{{old('name')}}">
                            </div>
                            <div class="col-sm-6">
                                <div >
                                    <label class="input-label" for="title">{{'zona'}}<span class="form-label-secondary text-danger"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Requerido.'}}"> *
                                                        </span>
                                                                    </label>
                                    <select name="zone_id" id="zone_id" class="form-control js-select2-custom">
                                        @if(!isset(auth('admin')->user()->zone_id))
                                        <option value="" {{!isset($e->zone_id)?'selected':''}}>{{'todo'}}</option>
                                        @endif
                                        @foreach($zones as $zone)
                                            <option value="{{$zone['id']}}">{{$zone['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div >
                                    <label class="input-label qcont" for="role_id">{{'Role'}}<span class="form-label-secondary text-danger"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Requerido.'}}"> *
                                                        </span>
                                            </label>
                                    <select class="form-control js-select2-custom w-100" name="role_id" id="role_id" required>
                                        <option value="" selected disabled>{{'seleccione Rol'}}</option>
                                        @foreach($roles as $role)
                                            <option value="{{$role->id}}">{{$role->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="input-label qcont" for="phone">{{'teléfono'}}<span class="form-label-secondary text-danger"
                                                        data-toggle="tooltip" data-placement="right"
                                                        data-original-title="{{ 'Requerido.'}}"> *
                                                        </span>
                                    </label>
                                <input type="number" name="phone" value="{{old('phone')}}" class="form-control" id="phone"
                                        placeholder="{{ 'Ex:' }} +88017********" required>
                                </div>
                            </div>
                        </div>
                    <div class="col-md-4">
                        <label class="h-100 d-flex flex-column">
                            <div class="text-center input-label qcont py-3 my-auto">
                                {{ 'Imagen del empleado' }} <small  class="text-danger">* ( {{ 'relación' }} 1:1 )</small>

                            </div>
                            <div class="text-center py-3 my-auto">
                                <img class="img--100" id="viewer"
                                src="{{asset('assets/admin/img/400x400/img2.jpg')}}" alt="Employee thumbnail"/>
                            </div>
                            <div class="custom-file">
                                <input type="file" name="image" id="customFileUpload" class="custom-file-input"
                                    accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" value="{{old('image')}}" required>
                                <div class="custom-file-label">{{'elegir archivo'}}</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <span class="card-header-icon">
                        <i class="tio-user"></i>
                    </span>
                    <span>{{'información de cuenta'}}</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="input-label qcont" for="email">{{'correo electrónico'}} <span class="form-label-secondary text-danger"
                            data-toggle="tooltip" data-placement="right"
                            data-original-title="{{ 'Requerido.'}}"> *
                            </span>
</label>
                        <input type="email" name="email" value="{{old('email')}}" class="form-control" id="email"
                                placeholder="{{ 'Ex:' }} ex@gmail.com" required>
                    </div>
                    <div class="col-md-4">
                        <div class="js-form-message form-group mb-0">
                            <label class="input-label" for="signupSrPassword">{{'Contraseña'}}<span class="form-label-secondary" data-toggle="tooltip" data-placement="top"
        data-original-title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"><img src="{{ asset('assets/admin/img/info-circle.svg') }}" alt="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"></span> <span class="form-label-secondary text-danger"
                            data-toggle="tooltip" data-placement="top"
                            data-original-title="{{ 'Requerido.'}}"> *
                            </span> </label>

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
                    <div class="col-md-4">
                        <div class="js-form-message form-group mb-0">
                            <label class="input-label" for="signupSrConfirmPassword">{{'confirmar Contraseña'}} <span class="form-label-secondary text-danger"
                            data-toggle="tooltip" data-placement="right"
                            data-original-title="{{ 'Requerido.'}}"> *
                            </span> </label>
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
        <div class="btn--container justify-content-end mt-4">
            <button type="reset" id="reset_btn" class="btn btn--reset">{{'reiniciar'}}</button>
            <button type="submit" class="btn btn--primary">{{'entregar'}}</button>
        </div>
    </form>
</div>
@endsection

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/employee.js"></script>
<script>
    "use strict";
    $(document).on('ready', function () {
        // INITIALIZATION OF SHOW PASSWORD
        // =======================================================
        $('.js-toggle-password').each(function () {
            new HSTogglePassword(this).init()
        });


        // INITIALIZATION OF FORM VALIDATION
        // =======================================================
        $('.js-validate').each(function() {
            $.HSCore.components.HSValidation.init($(this), {
                rules: {
                    confirmPassword: {
                        equalTo: '#signupSrPassword'
                    }
                }
            });
        });
    });
        $('#reset_btn').click(function(){
            $('#viewer').attr('src', "{{ asset('assets/admin/img/400x400/img2.jpg') }}");
            $('#customFileUpload').val(null);
            $('#zone_id').val(null).trigger('change');
            $('#role_id').val(null).trigger('change');
        })
    </script>
@endpush
