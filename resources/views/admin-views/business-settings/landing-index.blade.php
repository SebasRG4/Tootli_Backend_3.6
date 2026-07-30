@extends('layouts.admin.app')

@section('title', 'página de destino')


@section('content')
    <?php
    use Illuminate\Support\Facades\File;

    $filePath = resource_path('views/layouts/landing/custom/index.blade.php');

    $custom_file = File::exists($filePath);
    ?>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mr-3">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/business.png') }}" class="w--26" alt="">
                </span>
                <span>
                    {{ 'configuración de negocios' }}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.nav-menu')
        </div>
        <!-- End Page Header -->
        @php($config = \App\CentralLogics\Helpers::get_business_settings('landing_page'))
        @php($landing_integration_type = \App\CentralLogics\Helpers::get_business_data('landing_integration_type'))
        @php($redirect_url = \App\CentralLogics\Helpers::get_business_data('landing_page_custom_url'))
        <div class="card mb-3">
            <div class="card-body">
                <div
                    class="maintenance-mode-toggle-bar d-flex flex-wrap justify-content-between border rounded align-items-center p-2">
                    <h5 class="text-capitalize m-0">
                        {{ 'página de inicio predeterminada del administrador' }}
                        <i class="tio-info-outined" data-toggle="tooltip"
                            title="{{ 'Puede activar o desactivar la página de inicio proporcionada por el sistema.' }}"></i>
                    </h5>
                    <label class="toggle-switch toggle-switch-sm">
                        <input type="checkbox" class="status toggle-switch-input landing-page"
                            {{ isset($config) && $config ? 'checked' : '' }}>
                        <span class="toggle-switch-label text mb-0">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!--  -->
        <div class="card">
            <div class="card-header flex-wrap border-0">
                <h3 class="card-title">
                    {{ '¿Quiere integrar su propia página de destino personalizada?' }}

                </h3>
                <div class="text--primary d-flex align-items-center gap-3 font-weight-bolder cursor-pointer"
                    data-toggle="modal" data-target="#read-instructions">
                    <span class="mr-2">{{ 'Leer instrucciones' }}</span>
                    <div class="ripple-animation">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"
                            fill="none" class="svg replaced-svg">
                            <path
                                d="M9.00033 9.83268C9.23644 9.83268 9.43449 9.75268 9.59449 9.59268C9.75449 9.43268 9.83421 9.2349 9.83366 8.99935V5.64518C9.83366 5.40907 9.75366 5.21463 9.59366 5.06185C9.43366 4.90907 9.23588 4.83268 9.00033 4.83268C8.76421 4.83268 8.56616 4.91268 8.40616 5.07268C8.24616 5.23268 8.16644 5.43046 8.16699 5.66602V9.02018C8.16699 9.25629 8.24699 9.45074 8.40699 9.60352C8.56699 9.75629 8.76477 9.83268 9.00033 9.83268ZM9.00033 13.166C9.23644 13.166 9.43449 13.086 9.59449 12.926C9.75449 12.766 9.83421 12.5682 9.83366 12.3327C9.83366 12.0966 9.75366 11.8985 9.59366 11.7385C9.43366 11.5785 9.23588 11.4988 9.00033 11.4993C8.76421 11.4993 8.56616 11.5793 8.40616 11.7393C8.24616 11.8993 8.16644 12.0971 8.16699 12.3327C8.16699 12.5688 8.24699 12.7668 8.40699 12.9268C8.56699 13.0868 8.76477 13.1666 9.00033 13.166ZM9.00033 17.3327C7.84755 17.3327 6.76421 17.1138 5.75033 16.676C4.73644 16.2382 3.85449 15.6446 3.10449 14.8952C2.35449 14.1452 1.76088 13.2632 1.32366 12.2493C0.886437 11.2355 0.667548 10.1521 0.666992 8.99935C0.666992 7.84657 0.885881 6.76324 1.32366 5.74935C1.76144 4.73546 2.35505 3.85352 3.10449 3.10352C3.85449 2.35352 4.73644 1.7599 5.75033 1.32268C6.76421 0.88546 7.84755 0.666571 9.00033 0.666016C10.1531 0.666016 11.2364 0.884905 12.2503 1.32268C13.2642 1.76046 14.1462 2.35407 14.8962 3.10352C15.6462 3.85352 16.24 4.73546 16.6778 5.74935C17.1156 6.76324 17.3342 7.84657 17.3337 8.99935C17.3337 10.1521 17.1148 11.2355 16.677 12.2493C16.2392 13.2632 15.6456 14.1452 14.8962 14.8952C14.1462 15.6452 13.2642 16.2391 12.2503 16.6768C11.2364 17.1146 10.1531 17.3332 9.00033 17.3327ZM9.00033 15.666C10.8475 15.666 12.4206 15.0168 13.7195 13.7185C15.0184 12.4202 15.6675 10.8471 15.667 8.99935C15.667 7.15213 15.0178 5.57907 13.7195 4.28018C12.4212 2.98129 10.8481 2.33213 9.00033 2.33268C7.1531 2.33268 5.58005 2.98185 4.28116 4.28018C2.98227 5.57852 2.3331 7.15157 2.33366 8.99935C2.33366 10.8466 2.98283 12.4196 4.28116 13.7185C5.57949 15.0174 7.15255 15.6666 9.00033 15.666Z"
                                fill="currentColor"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <form id="theme_form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <label class="text-capitalize form-label form--label mb-3">
                        {{ 'Integre su página de destino a través de' }}
                        <i class="tio-info-outined" data-toggle="tooltip"
                            title="{{ 'Puede cargar su página de destino mediante URL o carga de archivos' }}"></i>
                    </label>
                    <div class="mb-30">
                        <div class="resturant-type-group border d-inline-flex">
                            <label class="form-check form--check mr-2 mr-md-4">
                                <input class="form-check-input" type="radio" value="url" name="landing_integration_via"
                                    {{ $landing_integration_type == 'url' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ 'URL' }}
                                </span>
                            </label>
                            <label class="form-check form--check mr-2 mr-md-4">
                                <input class="form-check-input" type="radio" value="file_upload"
                                    name="landing_integration_via"
                                    {{ $landing_integration_type == 'file_upload' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ 'carga de archivos' }}
                                </span>
                            </label>
                            <label class="form-check form--check mr-2 mr-md-4">
                                <input class="form-check-input" type="radio" value="none" name="landing_integration_via"
                                    {{ $landing_integration_type == 'none' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    {{ 'ninguno' }}
                                </span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-30">
                        <div class="__input-tab {{ $landing_integration_type == 'url' ? 'active' : '' }}" id="url">
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group mb-0 pb-2">
                                    <label for="redirect_url" class="form-label text-capitalize">
                                        {{ 'URL de la página de destino' }}
                                    </label>
                                    <input type="text"
                                        placeholder="{{ 'Ej: https://6ammart-web.6amtech.com/' }}"
                                        class="form-control h--45px" id="redirect_url" name="redirect_url" value="{{ $redirect_url }}">
                                </div>
                            </div>
                        </div>
                        <div class="__input-tab {{ $landing_integration_type == 'file_upload' ? 'active' : '' }}"
                            id="file_upload">
                            <div class="__bg-F8F9FC-card">
                                <div class="form-group mb-0 pb-2">
                                    <div class="row g-3">
                                        <div class="col-sm-6 col-lg-5 col-xl-4 col-xxl-3">
                                            <!-- Drag & Drop Upload -->
                                            <div class="uploadDnD">
                                                <div class="form-group mb-0 inputDnD bg-white rounded">
                                                    <input type="file" name="file_upload"
                                                        class="form-control-file text--primary font-weight-bold read-file"
                                                        id="inputFile"  accept=".zip"
                                                        data-title="Drag & drop file or Browse file">
                                                </div>
                                            </div>

                                            <div class="mt-5 card px-3 py-2 d--none" id="progress-bar">
                                                <div class="d-flex flex-wrap align-items-center gap-3">
                                                    <div class="">
                                                        <img width="24"
                                                            src="{{ asset('assets/admin/img/zip.png') }}"
                                                            alt="">
                                                    </div>
                                                    <div class="flex-grow-1 text-start">
                                                        <div
                                                            class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                            <span id="name_of_file" class="text-truncate fz-12"></span>
                                                            <span class="text-muted fz-12" id="progress-label">0%</span>
                                                        </div>
                                                        <progress id="uploadProgress" class="w-100" value="0"
                                                            max="100"></progress>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-5 col-xl-4 col-xxl-9">
                                            <div class="pl-sm-5">
                                                <h3 class="mb-3 d-flex">{{ 'instrucciones' }}</h3>
                                                <ul class="pl-3 d-flex flex-column gap-2 instructions-list mb-0">
                                                    <li>
                                                        {{ 'Cargue el contenido como un único archivo ZIP y el nombre del archivo debe ser' }}
                                                        <b>index.blade.php</b>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            @if ($custom_file)
                            <div class="row g-1 g-sm-2 mt-2">
                                <div class="col-6 col-md-4 col-xxl-3">
                                    <div class="card theme-card">
                                        <div class="card-body d-flex justify-content-between">
                                            <h3>
                                                index.blade.php
                                            </h3>

                                            <a class="btn action-btn btn--danger btn-outline-danger border-0 form-alert"  href="javascript:"
                                               data-id="index_page"
                                               data-message="{{ '¿Quieres eliminar esta página de índice?' }}" title="{{'eliminar página de índice'}}"><i class="tio-delete-outlined"></i>
                                        </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            @endif
                        </div>
                        <div class="__input-tab {{ $landing_integration_type == 'none' ? 'active' : '' }}" id="none">
                            <div class="__bg-F8F9FC-card">

                                @if (isset($config) && $config)
                                    <div class="text-center max-w-595 mx-auto py-4">
                                        <img src="{{ asset('assets/admin/img/landing-icon-2.png') }}"
                                            class="mb-3" alt="">
                                        <p class="m-0">
                                            {{ 'Actualmente estás utilizando el tema de página de destino de administrador predeterminado de 6amMart.' }}
                                            <a href="{{ route('home') }}"
                                                class="text--primary text-underline">{{ 'Visitar la página de destino' }}</a>
                                        </p>
                                    </div>
                                @else
                                    <div class="text-center max-w-487 mx-auto py-4">
                                        <img src="{{ asset('assets/admin/img/landing-icon-2.png') }}"
                                            class="mb-3" alt="">
                                        <p class="m-0">
                                            {{ 'No tienes ninguna página de destino empresarial para mostrar. Si el usuario busca la URL de la página de destino, verá la página 404.' }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-20">
                        <button type="reset" id="reset_btn" class="btn btn--reset">{{ 'Reiniciar' }}</button>
                        <button type="button"  class="btn btn--primary {{ getEnvMode() == 'demo' ? 'call-demo' : 'zip-upload' }}" id="update_setting">
                            {{ 'Guardar información' }}</button>
                    </div>
                </div>

        </form>
        <form action="{{route('admin.business-settings.delete-custom-landing-page')}}" method="post" id="index_page">
            @csrf @method('delete')
        </form>

    </div>

    <div class="modal fade" id="read-instructions">
        <div class="modal-dialog status-warning-modal max-w-842">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body px-4 px-md-5 pb-5 pt-0">
                    <div class="single-item-slider owl-carousel">
                        <div class="item">
                            <div class="mb-20">
                                <div class="text-center">
                                    <img src="{{ asset('assets/admin/img/read-instructions.png') }}"
                                        alt="" class="mb-20">
                                    <h5 class="modal-title">
                                        {{ 'Si desea configurar su propia página de destino, siga las instrucciones a continuación' }}
                                    </h5>
                                </div>
                                <ol type="1">
                                    <li>
                                        {{ 'Puede agregar su página de destino personalizada a través de URL o cargar un archivo ZIP de la página de destino.' }}
                                    </li>
                                    <li>
                                        {{ 'Si desea utilizar la opción URL. Simplemente aloje su página de destino, copie la URL de la página y haga clic en guardar información.' }}
                                    </li>
                                    <li>
                                        {{ 'Si desea cargar el archivo de código fuente de su página de destino.' }}

                                        <div class="ms-2 mt-1">
                                            {{ 'a. Crea un archivo html llamado' }} <b
                                                class="bg--4 text--primary-2">index.blade.php</b>
                                            {{ 'e inserte el código de diseño de su página de destino y cree un archivo zip.' }}

                                        </div>
                                        <div class="ms-2 mt-1">
                                            {{ 'b. cargue el archivo zip en la sección de carga de archivos y haga clic en guardar información.' }}
                                        </div>
                                    </li>
                                </ol>
                            </div>
                        </div>

                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="slide-counter"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script_2')
            <script src="{{asset('assets/admin/js/view-pages/business-settings-landing-page.js')}}"></script>
            <script href="{{ asset('assets/admin/vendor/swiper/swiper-bundle.min.js') }}"></script>


    <script>

        "use strict";

        $(document).ready(function() {
            $('.landing-page').on('click', function(event) {
                event.preventDefault();
                @if (env('APP_MODE') == 'demo')
                toastr.warning('Sorry! You can not change landing page in demo!');
                @else
                Swal.fire({
                    title: '{{ isset($config) && $config ? '¿Quiere desactivar la página de inicio de administración predeterminada?' : '¿Quiere activar la página de inicio de administración predeterminada?' }}',
                    text: '{{ isset($config) && $config ? 'Si está deshabilitada, la página de destino no será visible para nadie.' : 'Si está habilitado, la página de destino será visible para todos' }}',
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#00868F',
                    cancelButtonText: '{{ 'No' }}',
                    confirmButtonText: '{{ 'Sí' }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.get({
                            url: '{{ route('admin.landing-page') }}',
                            contentType: false,
                            processData: false,
                            beforeSend: function() {
                                $('#loading').show();
                            },
                            success: function(data) {
                                toastr.success(data.message);
                                location.reload();
                            },
                            complete: function() {
                                $('#loading').hide();
                            },
                        });
                    }
                })
                @endif

            });

            $('.zip-upload').on('click', function(){
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                let formData = new FormData(document.getElementById('theme_form'));
                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.business-settings.update-landing-setup') }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        let xhr = new window.XMLHttpRequest();
                        if ($('#inputFile').val()) {
                            $('#progress-bar').show();
                        }

                        xhr.upload.addEventListener("progress", function(e) {
                            if (e.lengthComputable) {
                                let percentage = Math.round((e.loaded * 100) / e.total);
                                $("#uploadProgress").val(percentage);
                                $("#progress-label").text(percentage + "%");
                            }
                        }, false);

                        return xhr;
                    },
                    beforeSend: function() {
                        $('#update_setting').attr('disabled');
                    },
                    success: function(response) {
                        if (response.status === 'error') {
                            $('#progress-bar').hide();
                            toastr.error(response.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        } else if (response.status === 'success') {
                            toastr.success(response.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                            location.reload();
                        }
                    },
                    complete: function() {
                        $('#update_setting').removeAttr('disabled');
                    },
                });

            });

        });

        $('#reset_btn').click(function() {
            $('.uploadDnD').empty().append(`<div class="form-group mb-0 inputDnD bg-white rounded">
                                                        <input type="file" name="file_upload" class="form-control-file text--primary font-weight-bold read-file "
                                                        id="inputFile"  accept=".zip" data-title="Drag & drop file or Browse file">
                                                    </div>`)
            $(`.__input-tab`).removeClass('active')
            $(`#{{ $landing_integration_type }}`).addClass('active')
        })


    </script>
@endpush
