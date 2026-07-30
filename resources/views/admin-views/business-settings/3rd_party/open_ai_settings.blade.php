@extends('layouts.admin.app')

@section('title', 'Configuración')

@section('3rd_party')
    active
@endsection
@section('openAI')
    active
@endsection

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-robot"></i>
                </span>
                <span>{{ 'Configuración abierta AI' }}
                </span>
            </h1>
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 mt-4 __gap-12px">
                <div class="js-nav-scroller hs-nav-scroller-horizontal mt-2">
                    <!-- Nav -->
                    <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
                        <li class="nav-item">
                            <a class="nav-link   {{ Request::is('admin/business-settings/open-ai') ? 'active' : '' }}"
                                href="{{ route('admin.business-settings.openAI') }}"
                                aria-disabled="true">{{ 'Configuración de IA' }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/business-settings/open-ai-settings') ? 'active' : '' }}"
                                href="{{ route('admin.business-settings.openAISettings') }}"
                                aria-disabled="true">{{ 'Configuración de IA' }}</a>
                        </li>
                    </ul>
                    <!-- End Nav -->
                </div>
            </div>
        </div>
        <!-- End Page Header -->


        <div class="col-12">

            <div class="card mt-2">
                <div class="card-header card-header-shadow">
                    <h5 class="card-title">
                        <span>
                            <span class="page-header-icon">
                                <i class="tio-robot"></i>
                            </span>
                            {{ 'Límites de los proveedores sobre el uso de IA' }}
                        </span>

                    </h5>
                </div>

                <form action="{{ route('admin.business-settings.openAISettingsUpdate') }}" method="post">
                    @csrf
                    @method('put')
                    <div class="card-body">
                        <div class="py-2">
                            <div class="row g-3 align-items-end">

                                <div class="align-self-center  col-4">
                                    <div class="text-left">
                                        <h4 class="align-items-center">
                                            <span>
                                                {{ 'Generación de datos por secciones' }}
                                            </span>
                                        </h4>
                                        <p>
                                            {{ 'Establezca cuántas veces la IA puede generar datos para cada elemento del panel o aplicación del proveedor.' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="card __bg-F8F9FC-card text-left">
                                        <div class="card-body">
                                            <div class="form-group mb-0">
                                                <label class="input-label" for="section_wise_ai_limit">
                                                    {{ 'Límite de generación de datos por sección' }}
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input id="section_wise_ai_limit" type="number" min="0" required
                                                    max="99999999999" class="form-control" name="section_wise_ai_limit"
                                                    value="{{ $data['section_wise_ai_limit'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="align-self-center  col-4">
                                    <div class="text-left">
                                        <h4 class="align-items-center">
                                            <span>
                                                {{ 'Generación de datos basada en imágenes.' }}
                                            </span>
                                        </h4>
                                        <p>
                                            {{ 'Establezca cuántas veces la IA puede generar datos a partir de la carga de una imagen' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="card __bg-F8F9FC-card text-left">
                                        <div class="card-body">
                                            <div class="form-group mb-0">
                                                <label class="input-label" for="image_upload_limit_for_ai">
                                                    {{ 'Límite de generación de carga de imágenes' }}
                                                     <span class="text-danger">*</span>
                                                </label>
                                                <input id="image_upload_limit_for_ai" type="number" min="0" required
                                                    max="99999999999" class="form-control" name="image_upload_limit_for_ai"
                                                    value="{{ $data['image_upload_limit_for_ai'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="mb-4 mt-4 col-12">
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn"
                                    class="btn btn--reset location-reload">{{ 'Reiniciar' }}</button>
                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}" id="submit"
                                    class="btn btn--primary call-demo">{{ 'Guardar información' }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

