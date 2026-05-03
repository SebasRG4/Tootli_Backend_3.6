@extends('layouts.admin.app')

@section('title', translate('Configuración de Efectivo'))

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

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.business-settings.update-cash-settings')}}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label">Límite Global de Efectivo en Mano</label>
                                <input type="number" name="dm_max_cash_in_hand" class="form-control" 
                                       value="{{$dm_max_cash_in_hand ? $dm_max_cash_in_hand->value : 500}}" required>
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
                    </div>
                    <div class="btn--container justify-content-end">
                        <button type="submit" class="btn btn--primary">Guardar Configuración</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
