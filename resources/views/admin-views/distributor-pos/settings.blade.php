@extends('layouts.admin.app')

@section('title', 'Configuración — POS Distribuidora')

@section('content')
    @php($store = $store ?? \App\CentralLogics\Helpers::get_store_data())
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title">⚙️ Configuración POS Distribuidora</h1>
                    <p class="text-muted mb-0">Configura cashback, valor de puntos y reglas de canje para <strong>{{ $store->name }}</strong>.</p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.distributor-pos.index') }}" class="btn btn--primary">
                        <i class="tio-arrow-backward mr-1"></i> Ir al POS
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Parámetros del sistema de puntos</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.distributor-pos.settings.save') }}" method="POST">
                            @csrf

                            {{-- Activar / desactivar --}}
                            <div class="form-group row align-items-center">
                                <label class="col-sm-5 col-form-label font-weight-semibold">POS Distribuidora activo</label>
                                <div class="col-sm-7">
                                    <label class="toggle-switch toggle-switch-sm d-flex align-items-center">
                                        <input type="checkbox" name="distributor_pos_enabled" value="1"
                                               {{ $config->distributor_pos_enabled ? 'checked' : '' }}>
                                        <span class="toggle-switch-label"></span>
                                        <span class="toggle-switch-label-content ml-2">
                                            {{ $config->distributor_pos_enabled ? 'Habilitado' : 'Deshabilitado' }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <hr>

                            {{-- Tasa de cashback --}}
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label font-weight-semibold" for="cashback_rate">
                                    % Cashback por compra
                                    <small class="d-block text-muted font-weight-normal">Ej: 5 = 5% del total → puntos</small>
                                </label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="number" name="distributor_cashback_rate" id="cashback_rate"
                                               class="form-control" min="0" max="100" step="0.01"
                                               value="{{ $config->distributor_cashback_rate ?? 0 }}">
                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Valor del punto --}}
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label font-weight-semibold" for="point_value">
                                    Valor de 1 punto
                                    <small class="d-block text-muted font-weight-normal">Ej: 1 = 1 punto = $1 MXN al canjear</small>
                                </label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                        <input type="number" name="distributor_point_value" id="point_value"
                                               class="form-control" min="0.0001" step="0.0001"
                                               value="{{ $config->distributor_point_value ?? 1 }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Mínimo para canjear --}}
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label font-weight-semibold" for="min_redemption">
                                    Mínimo de puntos para canjear
                                    <small class="d-block text-muted font-weight-normal">El cliente debe tener al menos este saldo</small>
                                </label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="number" name="distributor_min_redemption" id="min_redemption"
                                               class="form-control" min="0" step="0.5"
                                               value="{{ $config->distributor_min_redemption ?? 10 }}">
                                        <div class="input-group-append"><span class="input-group-text">pts</span></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Máximo % pagable con puntos --}}
                            <div class="form-group row">
                                <label class="col-sm-5 col-form-label font-weight-semibold" for="max_redemption_pct">
                                    % máximo del total pagable con puntos
                                    <small class="d-block text-muted font-weight-normal">Ej: 50 = el cliente puede pagar hasta el 50% con puntos</small>
                                </label>
                                <div class="col-sm-7">
                                    <div class="input-group">
                                        <input type="number" name="distributor_max_redemption_pct" id="max_redemption_pct"
                                               class="form-control" min="0" max="100" step="1"
                                               value="{{ $config->distributor_max_redemption_pct ?? 50 }}">
                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Preview lógica --}}
                            <div class="alert alert-soft-info" style="font-size:.85rem;">
                                <strong>📊 Ejemplo con la configuración actual:</strong><br>
                                Si el cliente compra <strong>$200</strong>, gana
                                <strong>{{ round(200 * ($config->distributor_cashback_rate ?? 0) / 100, 2) }} pts</strong>
                                ({{ $config->distributor_cashback_rate ?? 0 }}%).<br>
                                Con <strong>{{ $config->distributor_min_redemption ?? 10 }} pts</strong> puede canjear
                                = <strong>${{ round(($config->distributor_min_redemption ?? 10) * ($config->distributor_point_value ?? 1), 2) }}</strong> en su próxima compra.
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn--primary px-5">
                                    <i class="tio-save mr-1"></i> Guardar configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
