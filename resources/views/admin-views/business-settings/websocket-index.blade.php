@extends('layouts.admin.app')

@section('title', 'configuración de socket web')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mr-3">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/business.png') }}" class="w--26" alt="">
                </span>
                <span>
                    {{'configuración de negocios'}}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.nav-menu')
        </div>
        <!-- Page Header -->

        <!-- End Page Header -->
        <form action="{{ route('admin.business-settings.update-websocket') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row g-2">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6 mt-5">
                                    @php($websocket = \App\Models\BusinessSetting::where('key', 'websocket_status')->first())
                                    @php($websocket = $websocket ? $websocket->value : 0)
                                    <div class="form-group mb-0">
                                        <label
                                            class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    {{ 'enchufe web' }}
                                                </span>
                                                <span class="form-label-secondary text-danger d-flex"
                                                    data-toggle="tooltip" data-placement="right"
                                                    data-original-title="{{ 'Si WebSocket está habilitado, configure el servidor en consecuencia para una funcionalidad óptima.'}}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'alternar websocket' }}"> *
                                                </span>
                                            </span>
                                            <input type="checkbox"
                                                   data-id="websocket"
                                                   data-type="toggle"
                                                   data-image-on="{{ asset('assets/admin/img/modal/schedule-on.png') }}"
                                                   data-image-off="{{ asset('assets/admin/img/modal/schedule-off.png') }}"
                                                   data-title-on="{{'Quiere habilitar'}} <strong>{{'¿Socket web?'}}</strong>"
                                                   data-title-off="{{'Quiere deshabilitar'}} <strong>{{'¿Socket web?'}}</strong>'"
                                                   data-text-on="<p>{{ 'Si habilita esto, websocket registrará la última ubicación de Deliveryyman.' }}</p>"
                                                   data-text-off="<p>{{ 'Si desactiva esto, la última ubicación de Deliveryyman se registrará de forma predeterminada.' }}</p>"
                                                   class="status toggle-switch-input dynamic-checkbox-toggle"
                                                   value="1"
                                                name="websocket_status" id="websocket"
                                                {{ $websocket == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    @php($websocket_url = \App\Models\BusinessSetting::where('key', 'websocket_url')->first())
                                    <div class="form-group mb-0">
                                        <label class="form-label"
                                            for="websocket_url">{{ 'URL del socket web' }}</label>
                                        <input type="text" id="websocket_url" name="websocket_url" value="{{ $websocket_url->value ?? '' }}"
                                            class="form-control" placeholder="{{ 'Ej: ws://178.128.117.0' }}"
                                            required>
                                    </div>
                                </div>
                                <div class="col-6">
                                @php($websocket_port = \App\Models\BusinessSetting::where('key', 'websocket_port')->first())
                                    <div class="form-group mb-0">
                                        <label class="form-label"
                                            for="websocket_port">{{ 'puerto websocket' }}</label>
                                        <input id="websocket_port" type="number" value="{{ $websocket_port->value ?? '' }}" name="websocket_port"
                                            class="form-control" placeholder="{{ 'Ej: 6001' }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end">
                                <button type="reset" id="reset_btn" class="btn btn--reset">{{ 'reiniciar' }}</button>
                                <button type="submit" id="submit" class="btn btn--primary">{{ 'guardar información' }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
