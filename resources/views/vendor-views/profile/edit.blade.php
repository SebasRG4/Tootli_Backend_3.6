@extends('layouts.vendor.app')

@section('title','configuración de perfil')

@push('css_or_js')
<link href="{{asset('assets/back-end/css/croppie.css')}}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <!-- Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{'Configuración'}}</h1>
                </div>

                <div class="col-sm-auto">
                    <a class="btn btn-primary" href="{{route('seller.dashboard')}}">
                        <i class="tio-home mr-1"></i> {{'Panel de Control'}}
                    </a>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- End Page Header -->

        <div class="row">
            <div class="col-lg-3">
                <!-- Navbar -->
                <div class="navbar-vertical navbar-expand-lg mb-3 mb-lg-5">
                    <!-- Navbar Toggle -->
                    <button type="button" class="navbar-toggler btn btn-block btn-white mb-3"
                            aria-label="Toggle navigation" aria-expanded="false" aria-controls="navbarVerticalNavMenu"
                            data-toggle="collapse" data-target="#navbarVerticalNavMenu">
                <span class="d-flex justify-content-between align-items-center">
                  <span class="h5 mb-0"> {{'Menú de navegación'}} </span>

                  <span class="navbar-toggle-default">
                    <i class="tio-menu-hamburger"></i>
                  </span>

                  <span class="navbar-toggle-toggled">
                    <i class="tio-clear"></i>
                  </span>
                </span>
                    </button>
                    <!-- End Navbar Toggle -->

                    <div id="navbarVerticalNavMenu" class="collapse navbar-collapse">
                        <!-- Navbar Nav -->
                        <ul id="navbarSettings"
                            class="js-sticky-block js-scrollspy navbar-nav navbar-nav-lg nav-tabs card card-navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link active" href="javascript:" id="generalSection">
                                    <i class="tio-user-outlined nav-icon"></i>{{'Información básica'}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:" id="passwordSection">
                                    <i class="tio-lock-outlined nav-icon"></i> {{'Contraseña'}}
                                </a>
                            </li>
                        </ul>
                        <!-- End Navbar Nav -->
                    </div>
                </div>
                <!-- End Navbar -->
            </div>

            <div class="col-lg-9">
                <form action="{{route('seller.profile.update')}}" method="post" enctype="multipart/form-data" id="seller-profile-form">
                @csrf
                <!-- Card -->
                    <div class="card mb-3 mb-lg-5" id="generalDiv">
                        <!-- Profile Cover -->
                        <div class="profile-cover">
                            <div class="profile-cover-img-wrapper"></div>
                        </div>
                        <!-- End Profile Cover -->

                        <!-- Avatar -->
                        <label
                            class="avatar avatar-xxl avatar-circle avatar-border-lg avatar-uploader profile-cover-avatar"
                            for="avatarUploader">
                            <img id="viewer"
                                 data-onerror-image="{{asset('assets/back-end/img/160x160/img1.jpg')}}"
                                 class="avatar-img onerror-image"
                                 src="{{ $data->image_full_url }}"
                                 alt="Image">
                        </label>
                        <!-- End Avatar -->
                    </div>
                    <!-- End Card -->

                    <!-- Card -->
                    <div class="card mb-3 mb-lg-5">
                        <div class="card-header">
                            <h2 class="card-title h4">{{'Información básica'}}</h2>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <!-- Form -->
                            <!-- Form Group -->
                            <div class="row form-group">
                                <label for="firstNameLabel" class="col-sm-3 col-form-label input-label">{{'nombre completo'}}  <i
                                        class="tio-help-outlined text-body ml-1" data-toggle="tooltip"
                                        data-placement="top"
                                        title="{{ 'Nombre para mostrar' }}"></i></label>

                                <div class="col-sm-9 row">
                                    <div class="col-md-6">
                                        <label for="name">First {{'Nombre'}}  <span class="text-danger">*</span></label>
                                        <input type="text" name="f_name" value="{{$data->f_name}}" class="form-control" id="name"
                                               required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="name">{{'Apellido'}}  <span class="text-danger">*</span></label>
                                        <input type="text" name="l_name" value="{{$data->l_name}}" class="form-control" id="name"
                                               required>
                                    </div>
                                </div>
                            </div>
                            <!-- End Form Group -->

                            <!-- Form Group -->
                            <div class="row form-group">
                                <label for="phoneLabel" class="col-sm-3 col-form-label input-label">{{'Teléfono'}} <span
                                        class="input-label-secondary">({{ 'Opcional' }})</span></label>

                                <div class="col-sm-9">
                                    <input type="tel" class="js-masked-input form-control" name="phone" id="phoneLabel"
                                           placeholder="+x(xxx)xxx-xx-xx" aria-label="+(xxx)xx-xxx-xxxxx"
                                           value="{{$data->phone}}"
                                           data-hs-mask-options='{
                                           "template": "+(880)00-000-00000"
                                         }'>
                                </div>
                            </div>
                            <!-- End Form Group -->

                            <div class="row form-group">
                                <label for="newEmailLabel" class="col-sm-3 col-form-label input-label">{{'Correo electrónico'}}</label>

                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="email" id="newEmailLabel"
                                           value="{{$data->email}}"
                                           placeholder="{{'introduce una nueva dirección de correo electrónico'}}" aria-label="{{'introduce una nueva dirección de correo electrónico'}}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 col-form-label">
                                </div>
                                <div class="form-group col-md-9" id="select-img">
                                    <div class="custom-file">
                                        <input type="file" name="image" id="customFileUpload" class="custom-file-input"
                                            accept=".webp, .jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileUpload">{{'subir imagen'}}</label>
                                    </div>
                                </div>
                                </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" data-id="seller-profile-form" data-message="{{ 'Quiere actualizar la información del vendedor' }}" class="btn btn-primary {{env('APP_MODE')!='demo'?'form-alert':'call-demo'}}">{{ 'Guardar cambios' }}</button>
                            </div>

                            <!-- End Form -->
                        </div>
                        <!-- End Body -->
                    </div>
                    <!-- End Card -->
                </form>

                <!-- Card -->
                <div id="passwordDiv" class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-title">{{'Cambia tu contraseña'}}</h4>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <!-- Form -->
                        <form id="changePasswordForm" action="{{route('seller.profile.settings-password')}}" method="post"
                              enctype="multipart/form-data">
                        @csrf

                        <!-- Form Group -->
                            <div class="row form-group">
                                <label for="newPassword" class="col-sm-3 col-form-label input-label"> {{'Nueva contraseña'}}<span class="form-label-secondary" data-toggle="tooltip" data-placement="right"
        data-original-title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"><img src="{{ asset('assets/admin/img/info-circle.svg') }}" alt="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"></span></label>

                                <div class="col-sm-9">
                                    <input type="password" class="js-pwstrength form-control" name="password"
                                           id="newPassword" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"
                                           placeholder="{{ translate('messages.password_length_placeholder', ['length' => '8+']) }}"
                                           aria-label="8+ characters required"
                                           data-hs-pwstrength-options='{
                                           "ui": {
                                             "container": "#changePasswordForm",
                                             "viewports": {
                                               "progress": "#passwordStrengthProgress",
                                               "verdict": "#passwordStrengthVerdict"
                                             }
                                           }
                                         }'>

                                    <p id="passwordStrengthVerdict" class="form-text mb-2"></p>

                                    <div id="passwordStrengthProgress"></div>
                                </div>
                            </div>
                            <!-- End Form Group -->

                            <!-- Form Group -->
                            <div class="row form-group">
                                <label for="confirmNewPasswordLabel" class="col-sm-3 col-form-label input-label"> {{'Confirmar Contraseña'}} </label>

                                <div class="col-sm-9">
                                    <div class="mb-3">
                                        <input type="password" class="form-control" name="confirm_password"
                                               id="confirmNewPasswordLabel" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="{{ 'Debe contener al menos un número y una letra y símbolo mayúscula y minúscula, y al menos 8 o más caracteres' }}"
                                               placeholder="{{ translate('messages.password_length_placeholder', ['length' => '8+']) }}"
                                               aria-label="8+ characters required">
                                    </div>
                                </div>
                            </div>
                            <!-- End Form Group -->

                            <div class="d-flex justify-content-end">
                                <button type="button" data-id="changePasswordForm" data-message="{{'quiero actualizar la contraseña'}}" class="btn btn-primary {{env('APP_MODE')!='demo'?'form-alert':'call-demo'}}">{{'Guardar cambios'}}</button>
                            </div>
                        </form>
                        <!-- End Form -->
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
        </div>
        <!-- End Row -->
    </div>
    <!-- End Content -->
       <!--modal-->

@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/vendor/profile-edit.js"></script>
@endpush

