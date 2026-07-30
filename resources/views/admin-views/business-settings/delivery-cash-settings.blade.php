@extends('layouts.admin.app')

@section('title', 'Configuración efectiva')

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/wallet.png')}}" class="w--26" alt="">
                </span>
                <span>
                    Control de Efectivo y Asignación
                </span>
            </h1>
        </div>

        @php($dm_max_cash_in_hand = \App\Models\BusinessSetting::where(['key' => 'dm_max_cash_in_hand'])->first())
        @php($high_value_threshold = \App\Models\BusinessSetting::where(['key' => 'high_value_threshold'])->first())
        @php($max_time_without_deposit_minutes = \App\Models\BusinessSetting::where(['key' => 'max_time_without_deposit_minutes'])->first())
        @php($high_value_strategy = \App\Models\BusinessSetting::where(['key' => 'high_value_strategy'])->first())
        @php($admin_whatsapp_number = \App\Models\BusinessSetting::where(['key' => 'admin_whatsapp_number'])->first())

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">
                    <span class="card-header-icon">
                        <i class="tio-settings"></i>
                    </span>
                    <span>Parámetros de Control de Efectivo</span>
                </h5>
            </div>
            <div class="card-body">
                <form action="{{route('admin.business-settings.update-cash-settings')}}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label">Límite Global de Efectivo en Mano</label>
                                <input type="number" name="dm_max_cash_in_hand" class="form-control" 
                                       value="{{$dm_max_cash_in_hand ? $dm_max_cash_in_hand->value : 350}}" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label">Umbral de Alto Ticket ($)</label>
                                <input type="number" name="high_value_threshold" class="form-control" 
                                       value="{{$high_value_threshold ? $high_value_threshold->value : 700}}" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label">Tiempo Máximo sin Depósito (minutos)</label>
                                <input type="number" name="max_time_without_deposit_minutes" class="form-control" 
                                       value="{{$max_time_without_deposit_minutes ? $max_time_without_deposit_minutes->value : 120}}" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label">Estrategia para Alto Valor</label>
                                <select name="high_value_strategy" class="form-control">
                                    <option value="only_card" {{($high_value_strategy && $high_value_strategy->value == 'only_card') ? 'selected' : ''}}>Solo Tarjeta</option>
                                    <option value="relaxed_cash" {{($high_value_strategy && $high_value_strategy->value == 'relaxed_cash') ? 'selected' : ''}}>Efectivo Relajado (Asignar aunque exceda)</option>
                                    <option value="assign_any" {{($high_value_strategy && $high_value_strategy->value == 'assign_any') ? 'selected' : ''}}>Asignar a cualquiera</option>
                                    <option value="notify_admin" {{($high_value_strategy && $high_value_strategy->value == 'notify_admin') ? 'selected' : ''}}>Notificar Admin y Esperar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label">Número de WhatsApp del Admin (Alertas)</label>
                                <input type="text" name="admin_whatsapp_number" class="form-control" 
                                       value="{{$admin_whatsapp_number ? $admin_whatsapp_number->value : '+527297706434'}}" placeholder="+521234567890">
                                <small class="text-muted">Incluir código de país (ej. +52 para México)</small>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-3">
                        <button type="submit" class="btn btn--primary">Guardar Configuración</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <span class="card-header-icon">
                        <i class="tio-whatsapp"></i>
                    </span>
                    <span>Prueba de Notificaciones WhatsApp (LabsMobile)</span>
                </h5>
            </div>
            <div class="card-body">
                <form action="{{route('admin.business-settings.test-whatsapp')}}" method="post">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label class="input-label">Número para Pruebas</label>
                                <input type="text" name="admin_phone" class="form-control" 
                                       value="{{$admin_whatsapp_number ? $admin_whatsapp_number->value : '+527297706434'}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn--info">
                                <i class="tio-send"></i> Enviar Mensaje de Prueba
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
