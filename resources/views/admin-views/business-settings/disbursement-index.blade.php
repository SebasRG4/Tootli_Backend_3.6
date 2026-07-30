@extends('layouts.admin.app')

@section('title', 'Configuración de desembolso')


@section('content')
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
    @php($disbursement_type = \App\Models\BusinessSetting::where('key', 'disbursement_type')->first())
    @php($disbursement_type = $disbursement_type ? $disbursement_type->value : 'manual')
    @php($store_disbursement_command = \App\Models\BusinessSetting::where('key', 'store_disbursement_command')->first())
    @php($store_disbursement_command = $store_disbursement_command ? $store_disbursement_command->value : '')
    @php($dm_disbursement_command = \App\Models\BusinessSetting::where('key', 'dm_disbursement_command')->first())
    @php($dm_disbursement_command = $dm_disbursement_command ? $dm_disbursement_command->value : '')
    <!-- Page Header -->

    <!-- End Page Header -->
    <form action="{{ route('admin.business-settings.update-disbursement') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row g-2">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        @if($disbursement_type == 'automated')
                            <div class="mb-3 text-right">
                                <button type="button" class="btn btn--primary" data-toggle="modal" data-target="#myModal">{{ 'Verificar dependencias' }}</button>
                            </div>
                        @endif
                        <div class="row g-3 mb-2">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="input-label text-capitalize d-flex align-items-center"><span
                                            class="line--limit-1">{{ 'Tipo de solicitud de desembolso'}}</span>
                                        <span class="form-label-secondary"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Elija Solicitudes de desembolso manuales o automatizadas. En el modo Automatizado, las solicitudes de retiro para desembolso se generan automáticamente; En el modo Manual, las tiendas deben solicitar retiros manualmente.' }}"><img
                                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                alt="{{ 'Tipo de solicitud de desembolso' }}"></span>
                                    </label>
                                    <div class="restaurant-type-group border">
                                        <label class="form-check form--check mr-2 mr-md-4">
                                            <input class="form-check-input" type="radio" value="manual"
                                                   name="disbursement_type" id="disbursement_type"
                                                {{ $disbursement_type == 'manual' ? 'checked' : '' }}>
                                            <span class="form-check-label">
                                                    {{ 'manual' }}
                                                </span>
                                        </label>
                                        <label class="form-check form--check mr-2 mr-md-4">
                                            <input class="form-check-input" type="radio" value="automated"
                                                   name="disbursement_type" id="disbursement_type2"
                                                {{ $disbursement_type == 'automated' ? 'checked' : '' }}>
                                            <span class="form-check-label">
                                                    {{ 'automatizado' }}
                                                </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 automated_disbursement_section {{ $disbursement_type == 'manual' ? 'd-none' : '' }}">
                                @php($system_php_path = \App\Models\BusinessSetting::where('key', 'system_php_path')->first())
                                @php($system_php_path = $system_php_path ? $system_php_path->value : '')
                                <div class="form-group lang_form default-form">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="system_php_path" class="form-label text-capitalize m-0">
                                            {{'Ruta PHP del sistema'}}
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{'Ubicación predeterminada donde está instalado el ejecutable PHP en el servidor.'}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                        </label>
                                    </div>
                                    <input id="system_php_path" type="text" placeholder="{{'Ejemplo: /usr/bin/php'}}" class="form-control h--45px" min="1" name="system_php_path" value="{{ $system_php_path }}" required>
                                </div>
                            </div>
                            <div class="col-12 automated_disbursement_section {{ $disbursement_type == 'manual' ? 'd-none' : '' }} ">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label">{{'Panel de tienda'}}</label>
                                        <div class="__bg-F8F9FC-card">
                                            <div class="row">
                                                @php($store_disbursement_time_period = \App\Models\BusinessSetting::where('key', 'store_disbursement_time_period')->first())
                                                @php($store_disbursement_time_period = $store_disbursement_time_period ? $store_disbursement_time_period->value : 1)
                                                <div class='{{ $store_disbursement_time_period=='weekly'?'col-6':'col-12' }}' id="store_time_period_section">
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label for="store_disbursement_time_period" class="form-label text-capitalize m-0">
                                                                {{'Crear desembolsos'}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{'Elija cómo se generará la solicitud de desembolso: Mensual, Semanal o Diaria.'}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <select  name="store_disbursement_time_period" id="store_disbursement_time_period" class="form-control" required>
                                                            <option value="daily" {{ $store_disbursement_time_period=='daily'?'selected':'' }}>
                                                                {{ 'a diario' }}
                                                            </option>
                                                            <option value="weekly" {{ $store_disbursement_time_period=='weekly'?'selected':'' }}>
                                                                {{ 'semanalmente' }}
                                                            </option>
                                                            <option value="monthly" {{ $store_disbursement_time_period=='monthly'?'selected':'' }}>
                                                                {{ 'mensual' }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class='col-6 {{ $store_disbursement_time_period=='weekly'?'':'d-none' }}' id="store_week_day_section">
                                                    @php($store_disbursement_week_start = \App\Models\BusinessSetting::where('key', 'store_disbursement_week_start')->first())
                                                    @php($store_disbursement_week_start = $store_disbursement_week_start ? $store_disbursement_week_start->value : 'saturday')
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label for="store_disbursement_week_start" class="form-label text-capitalize m-0">
                                                                {{'Inicio de semana'}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{'Elija cuándo comienza la semana para la nueva solicitud de desembolso. Esta sección solo aparecerá cuando se seleccione el desembolso semanal.'}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <select name="store_disbursement_week_start" id="store_disbursement_week_start" class="form-control" required>
                                                            <option value="saturday" {{ $store_disbursement_week_start == 'saturday'?'selected':'' }}>
                                                                {{ 'sábado' }}
                                                            </option>
                                                            <option value="sunday" {{ $store_disbursement_week_start == 'sunday'?'selected':'' }}>
                                                                {{ 'domingo' }}
                                                            </option>
                                                            <option value="monday" {{ $store_disbursement_week_start == 'monday'?'selected':'' }}>
                                                                {{ 'lunes' }}
                                                            </option>
                                                            <option value="tuesday" {{ $store_disbursement_week_start == 'tuesday'?'selected':'' }}>
                                                                {{ 'martes' }}
                                                            </option>
                                                            <option value="wednesday" {{ $store_disbursement_week_start == 'wednesday'?'selected':'' }}>
                                                                {{ 'miércoles' }}
                                                            </option>
                                                            <option value="thursday" {{ $store_disbursement_week_start == 'thursday'?'selected':'' }}>
                                                                {{ 'jueves' }}
                                                            </option>
                                                            <option value="friday" {{ $store_disbursement_week_start == 'friday'?'selected':'' }}>
                                                                {{ 'viernes' }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class='col-6'>
                                                    @php($store_disbursement_create_time = \App\Models\BusinessSetting::where('key', 'store_disbursement_create_time')->first())
                                                    @php($store_disbursement_create_time = $store_disbursement_create_time ? $store_disbursement_create_time->value : 1)
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label for="store_disbursement_create_time" class="form-label text-capitalize m-0">
                                                                {{'Crear tiempo'}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{'Defina cuándo se generará automáticamente la nueva solicitud de desembolso.'}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <input type="time" id="store_disbursement_create_time" placeholder="{{'Ej: 7'}}" class="form-control h--45px" name="store_disbursement_create_time" value="{{ $store_disbursement_create_time }}" required>
                                                    </div>
                                                </div>
                                                <div class='col-6'>
                                                    @php($store_disbursement_min_amount = \App\Models\BusinessSetting::where('key', 'store_disbursement_min_amount')->first())
                                                    @php($store_disbursement_min_amount = $store_disbursement_min_amount ? $store_disbursement_min_amount->value : 'saturday')
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label for="store_disbursement_min_amount" class="form-label text-capitalize m-0">
                                                                {{'Cantidad Mínima'}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{'Ingrese el monto mínimo para ser elegible para generar una solicitud de desembolso automático.'}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <input id="store_disbursement_min_amount" type="number" placeholder="{{'Ej: 100'}}" class="form-control h--45px" min="1" name="store_disbursement_min_amount" value="{{ $store_disbursement_min_amount }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            @php($store_disbursement_waiting_time = \App\Models\BusinessSetting::where('key', 'store_disbursement_waiting_time')->first())
                                            @php($store_disbursement_waiting_time = $store_disbursement_waiting_time ? $store_disbursement_waiting_time->value : '')
                                            <div class="form-group lang_form default-form">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label for="store_disbursement_waiting_time" class="form-label text-capitalize m-0">
                                                        {{'Días necesarios para completar el desembolso'}}
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{'Ingrese el número de días en los cuales se completará el desembolso.'}}">
                                                                <i class="tio-info-outined"></i>
                                                            </span>
                                                    </label>
                                                </div>
                                                <input id="store_disbursement_waiting_time" type="number" placeholder="{{'Ej: 7'}}" min="1" class="form-control h--45px" name="store_disbursement_waiting_time" value="{{ $store_disbursement_waiting_time }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        @php($dm_disbursement_time_period = \App\Models\BusinessSetting::where('key', 'dm_disbursement_time_period')->first())
                                        @php($dm_disbursement_time_period = $dm_disbursement_time_period ? $dm_disbursement_time_period->value : '')
                                        <label class="form-label">{{'repartidor'}}</label>
                                        <div class="__bg-F8F9FC-card">
                                            <div class="row">
                                                <div class='{{ $dm_disbursement_time_period=='weekly'?'col-6':'col-12' }}' id="dm_time_period_section">
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label for="dm_disbursement_time_period" class="form-label text-capitalize m-0">
                                                                {{'Crear desembolsos'}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{'Elija cómo se generará la solicitud de desembolso: Mensual, Semanal o Diaria.'}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <select name="dm_disbursement_time_period" id="dm_disbursement_time_period" class="form-control" required>
                                                            <option value="daily" {{ $dm_disbursement_time_period=='daily'?'selected':'' }}>
                                                                {{ 'a diario' }}
                                                            </option>
                                                            <option value="weekly" {{ $dm_disbursement_time_period=='weekly'?'selected':'' }}>
                                                                {{ 'semanalmente' }}
                                                            </option>
                                                            <option value="monthly" {{ $dm_disbursement_time_period=='monthly'?'selected':'' }}>
                                                                {{ 'mensual' }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                @php($dm_disbursement_week_start = \App\Models\BusinessSetting::where('key', 'dm_disbursement_week_start')->first())
                                                @php($dm_disbursement_week_start = $dm_disbursement_week_start ? $dm_disbursement_week_start->value : 'saturday')
                                                <div class='col-6 {{ $dm_disbursement_time_period=='weekly'?'':'d-none' }}' id="dm_week_day_section">
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label for="dm_disbursement_week_start" class="form-label text-capitalize m-0">
                                                                {{'Inicio de semana'}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{'Elija cuándo comienza la semana para la nueva solicitud de desembolso. Esta sección solo aparecerá cuando se seleccione el desembolso semanal.'}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <select name="dm_disbursement_week_start" id="dm_disbursement_week_start" class="form-control" required>
                                                            <option value="saturday" {{ $dm_disbursement_week_start == 'saturday'?'selected':'' }}>
                                                                {{ 'sábado' }}
                                                            </option>
                                                            <option value="sunday" {{ $dm_disbursement_week_start == 'sunday'?'selected':'' }}>
                                                                {{ 'domingo' }}
                                                            </option>
                                                            <option value="monday" {{ $dm_disbursement_week_start == 'monday'?'selected':'' }}>
                                                                {{ 'lunes' }}
                                                            </option>
                                                            <option value="tuesday" {{ $dm_disbursement_week_start == 'tuesday'?'selected':'' }}>
                                                                {{ 'martes' }}
                                                            </option>
                                                            <option value="wednesday" {{ $dm_disbursement_week_start == 'wednesday'?'selected':'' }}>
                                                                {{ 'miércoles' }}
                                                            </option>
                                                            <option value="thursday" {{ $dm_disbursement_week_start == 'thursday'?'selected':'' }}>
                                                                {{ 'jueves' }}
                                                            </option>
                                                            <option value="friday" {{ $dm_disbursement_week_start == 'friday'?'selected':'' }}>
                                                                {{ 'viernes' }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class='col-6'>
                                                    @php($dm_disbursement_create_time = \App\Models\BusinessSetting::where('key', 'dm_disbursement_create_time')->first())
                                                    @php($dm_disbursement_create_time = $dm_disbursement_create_time ? $dm_disbursement_create_time->value : 1)
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label for="dm_disbursement_create_time" class="form-label text-capitalize m-0">
                                                                {{'Crear tiempo'}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{'Defina cuándo se generará automáticamente la nueva solicitud de desembolso.'}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <input  id="dm_disbursement_create_time" type="time" placeholder="{{'Ej: 7'}}" class="form-control h--45px" name="dm_disbursement_create_time" value="{{ $dm_disbursement_create_time }}" required>
                                                    </div>
                                                </div>
                                                <div class='col-6'>
                                                    @php($dm_disbursement_min_amount = \App\Models\BusinessSetting::where('key', 'dm_disbursement_min_amount')->first())
                                                    @php($dm_disbursement_min_amount = $dm_disbursement_min_amount ? $dm_disbursement_min_amount->value : 'saturday')
                                                    <div class="form-group lang_form default-form">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label for="dm_disbursement_min_amount" class="form-label text-capitalize m-0">
                                                                {{'Cantidad Mínima'}}
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{'Ingrese el monto mínimo para ser elegible para generar una solicitud de desembolso automático.'}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <input id="dm_disbursement_min_amount" type="number" placeholder="{{'Ej: 100'}}" class="form-control h--45px" min="1" name="dm_disbursement_min_amount" value="{{ $dm_disbursement_min_amount }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            @php($dm_disbursement_waiting_time = \App\Models\BusinessSetting::where('key', 'dm_disbursement_waiting_time')->first())
                                            @php($dm_disbursement_waiting_time = $dm_disbursement_waiting_time ? $dm_disbursement_waiting_time->value : '')
                                            <div class="form-group lang_form default-form">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label for="dm_disbursement_waiting_time" class="form-label text-capitalize m-0">
                                                        {{'Días necesarios para completar el desembolso'}}
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{'Ingrese el número de días en los cuales se completará el desembolso.'}}">
                                                                <i class="tio-info-outined"></i>
                                                            </span>
                                                    </label>
                                                </div>
                                                <input id="dm_disbursement_waiting_time" type="number" min="1" placeholder="{{'Ej: 7'}}" class="form-control h--45px" name="dm_disbursement_waiting_time" value="{{ $dm_disbursement_waiting_time }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn--container justify-content-end">
                            <button type="reset" id="reset_btn" class="btn btn--reset location-reload">{{ 'reiniciar' }}</button>
                            <button type="submit" id="submit" class="btn btn--primary">{{ 'guardar información' }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal" id="myModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-center">{{ 'Comando Cron para desembolso' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                            <span class="text--base">
                                {{'En algunas configuraciones de servidor, es posible que la función ejecutiva en PHP no esté habilitada, lo que limita su capacidad para crear trabajos cron mediante programación. Un trabajo cron es una tarea programada que automatiza procesos repetitivos en su servidor. Sin embargo, si la función ejecutiva está deshabilitada, puede configurar trabajos cron manualmente usando los siguientes comandos'}}:
                            </span>
                    </div>
                    <label for="storeDisbursementCommand" class="form-label text-capitalize">
                        {{'Almacenar comando cron'}}
                    </label>
                    <div class="input--group input-group mb-3">
                        <input type="text" value="{{ $store_disbursement_command }}" class="form-control" id="storeDisbursementCommand" readonly>
                        <button class="btn btn-primary copy-btn copy-to-clipboard" data-id="storeDisbursementCommand">{{ 'Copiar' }}</button>
                    </div>
                    <label for="dmDisbursementCommand" class="form-label text-capitalize">
                        {{'Comando Cron del repartidor'}}
                    </label>
                    <div class="input--group input-group">
                        <input type="text" value="{{ $dm_disbursement_command }}" class="form-control"  id="dmDisbursementCommand" readonly>
                        <button class="btn btn-primary copy-btn copy-to-clipboard" data-id="dmDisbursementCommand">{{ 'Copiar' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
@push('script_2')
    <script src="{{asset('assets/admin/js/view-pages/disbursement.js')}}"></script>
    @php($flag = session('disbursement_exec'))
    <script>
        "use strict";
        $(document).on('ready', function() {
            @if ($disbursement_type == 'manual')
            $('.automated_disbursement_section').hide();
            @endif

            @if (isset($flag) && $flag)
            $('#myModal').modal('show');
            @endif
        });

    </script>
@endpush
