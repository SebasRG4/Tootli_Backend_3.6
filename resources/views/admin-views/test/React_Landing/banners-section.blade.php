@extends('layouts.admin.app')

@section('title','nueva pagina')

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">

@endpush

@section('content')
<div class="content container-fluid">
    <div class="page-header pb-0">
        <div class="d-flex flex-wrap justify-content-between">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/landing.png')}}" class="w--20" alt="">
                </span>
                <span>
                    {{ 'reaccionar página de inicio' }}
                </span>
            </h1>
            <div class="text--primary-2 py-1 d-flex flex-wrap align-items-center" type="button" data-toggle="modal" data-target="#how-it-works">
                <strong class="mr-2">{{'¡Mira cómo funciona!'}}</strong>
                <div>
                    <i class="tio-info-outined"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-20 mt-2">
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            @include('admin-views.business-settings.landing-page-settings.top-menu-links.react-landing-page-links')
        </div>
    </div>
    <div class="card py-3 px-xxl-4 px-3 mb-20">
        <div class="d-flex flex-sm-nowrap flex-wrap gap-3 align-items-center justify-content-between">
            <div class="">
                <h3 class="mb-1">{{ 'Sección de pancartas' }}</h3>
                <p class="mb-0 gray-dark fs-12">
                    {{ 'Vea cómo se verá su sección de banner ante los clientes.' }}
                </p>
            </div>
            <div class="max-w-300px ml-sm-auto">
                <button type="button" class="btn btn-outline-primary py-2 fs-12 px-3 offcanvas-trigger" data-target="#banner_section">
                    <i class="tio-invisible"></i> {{ 'Vista previa de la sección' }}
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-4">
                <h3 class="mb-1">{{ 'Bandera' }}</h3>
                <p class="mb-0 fs-12 gray-dark">{{ 'Sube una imagen que represente tu marca y haga que los usuarios quieran realizar pedidos de inmediato.' }}</p>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <div class="bg--secondary h-100 rounded p-md-4 p-3">
                        <div class="text-center py-2">
                            <div class="mb-4">
                                <h5 class="mb-1">{{ 'Subir imagen de banner' }}</h5>
                                <p class="mb-0 fs-12 gray-dark">{{ 'Sube tu imagen de banner' }}</p>
                            </div>
                            <div class="mx-auto text-center">
                                <div class="upload-file_custom">
                                    <input type="file" name="image" class="upload-file__input single_file_input"
                                            accept=".webp, .jpg, .jpeg, .png, .gif" required>
                                    <label class="upload-file__wrapper ratio-8-1 m-0">
                                        <div class="upload-file-textbox text-center">
                                            <img width="22" class="svg" src="{{asset('assets/admin/img/document-upload.svg')}}" alt="img">
                                            <h6 class="mt-1 color-656566 fw-medium fs-10 lh-base text-center">
                                                <span class="theme-clr">Click to upload</span>
                                                <br>
                                                Or drag and drop
                                            </h6>
                                        </div>
                                        <img class="upload-file-img" loading="lazy" src="" data-default-src="" alt="" style="display: none;">
                                    </label>
                                    <div class="overlay">
                                        <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                            <button type="button" class="btn btn-outline-info icon-btn view_btn">
                                                <i class="tio-invisible"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info icon-btn edit_btn">
                                                <i class="tio-edit"></i>
                                            </button>
                                            <button type="button" class="remove_btn btn icon-btn">
                                                <i class="tio-delete text-danger"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="fs-10 text-center mb-0 mt-lg-4 mt-3">
                                {{ 'JPG, JPEG, PNG, Gif Tamaño de imagen: Máximo 5 MB'}} <span class="font-medium text-title">{{ '(8:1)'}}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn--container justify-content-end mt-20">
                <button type="reset" class="btn btn--reset mb-2">{{'Reiniciar'}}</button>
                <button type="submit"   class="btn btn--primary mb-2">{{'Ahorrar'}}</button>
            </div>
        </div>
    </div>  
</div>

<!-- Section View Offcanvas here -->
<div id="banner_section" class="custom-offcanvas offcanvas-750 d-flex flex-column justify-content-between">
    <form action="{{ route('taxvat.store') }}" method="post">
        <div>
            <div class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
                <div class="py-1">
                    <h3 class="mb-0 line--limit-1">{{ 'Vista previa de la sección del banner' }}</h3>
                </div>
                <button type="button" class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary text-dark offcanvas-close fz-15px p-0"aria-label="Close">
                    &times;
                </button>
            </div>
            <div class="custom-offcanvas-body custom-offcanvas-body-100  p-20">
               <section class="common-section-view bg-white border rounded-10">
                    <div class="container p-0">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="banner-thumb w-100 h-100 rounded-10">
                                    <img height="80" src="{{ asset('assets/admin/img/400x400/react-landing-banner.png') }}" alt="" class="rounded-10">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </form>
</div>
<div id="offcanvasOverlay" class="offcanvas-overlay"></div>
<!-- Section View Offcanvas end -->
@endsection

@push('script_2')

@endpush