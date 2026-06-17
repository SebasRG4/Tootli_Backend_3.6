@extends('layouts.admin.app')

@section('title', 'Configuración de Envío de Tootli Abastos')

@push('css_or_js')
    <!-- Custom styles if any -->
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-xl-10 col-md-9 col-sm-8 mb-3 mb-sm-0">
                    <h1 class="page-header-title text-capitalize m-0">
                        <span class="page-header-icon">
                            <img src="{{ asset('assets/admin/img/store.png') }}" class="w--26" alt="">
                        </span>
                        <span>
                            Tootli Abastos: Configuración de Envío por Grid
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Info Banner -->
        <div class="card bg--secondary border-0 mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="text-primary fs-24">
                        <i class="tio-info-outined"></i>
                    </div>
                    <div>
                        <h5 class="card-title text-primary font-weight-bold mb-1">Información de Envíos Dinámicos</h5>
                        <p class="card-text text-muted mb-0">
                            Las tarifas de envío y montos mínimos para envío gratis se aplican de forma dinámica a cada pedido de Tootli Abastos. 
                            El sistema detecta automáticamente la celda del mapa de rejilla hexagonal (Hex Grid) donde se ubica el restaurante del vendedor y aplica las reglas correspondientes (Minutos, Estándar/Horas, o Día Siguiente).
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.abastos.shipping-setup.update') }}" method="post">
            @csrf

            <div class="row g-3">
                <!-- Minutes Delivery Card -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5 class="card-title m-0">
                                <span class="card-header-icon text-success">
                                    <i class="tio-flash"></i>
                                </span>
                                <strong>Entrega en Minutos</strong>
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-4">Se aplica cuando el vendedor se encuentra dentro de un hexágono pintado con tipo de entrega "Minutes".</p>
                            
                            <div class="form-group">
                                <label class="input-label font-weight-bold">Costo Base de Envío ($)</label>
                                <input type="number" step="0.01" min="0" name="abastos_shipping_fee_minutes" 
                                    class="form-control" placeholder="Ej: 50" 
                                    value="{{ \App\CentralLogics\Helpers::get_business_settings('abastos_shipping_fee_minutes') ?? 50.00 }}" required>
                            </div>

                            <div class="form-group mb-0">
                                <label class="input-label font-weight-bold">Compra Mínima para Envío Gratis ($)</label>
                                <input type="number" step="0.01" min="0" name="abastos_free_shipping_min_minutes" 
                                    class="form-control" placeholder="Ej: 299" 
                                    value="{{ \App\CentralLogics\Helpers::get_business_settings('abastos_free_shipping_min_minutes') ?? 299.00 }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hours / Standard Delivery Card -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5 class="card-title m-0">
                                <span class="card-header-icon text-info">
                                    <i class="tio-time"></i>
                                </span>
                                <strong>Entrega en Horas (Estándar)</strong>
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-4">Se aplica cuando el vendedor se encuentra dentro de un hexágono pintado con tipo de entrega "Standard".</p>
                            
                            <div class="form-group">
                                <label class="input-label font-weight-bold">Costo Base de Envío ($)</label>
                                <input type="number" step="0.01" min="0" name="abastos_shipping_fee_standard" 
                                    class="form-control" placeholder="Ej: 50" 
                                    value="{{ \App\CentralLogics\Helpers::get_business_settings('abastos_shipping_fee_standard') ?? 50.00 }}" required>
                            </div>

                            <div class="form-group mb-0">
                                <label class="input-label font-weight-bold">Compra Mínima para Envío Gratis ($)</label>
                                <input type="number" step="0.01" min="0" name="abastos_free_shipping_min_standard" 
                                    class="form-control" placeholder="Ej: 299" 
                                    value="{{ \App\CentralLogics\Helpers::get_business_settings('abastos_free_shipping_min_standard') ?? 299.00 }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Day Delivery Card -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5 class="card-title m-0">
                                <span class="card-header-icon text-primary">
                                    <i class="tio-calendar"></i>
                                </span>
                                <strong>Entrega al Día Siguiente</strong>
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-4">Se aplica cuando el vendedor se encuentra dentro de un hexágono pintado con tipo de entrega "Next Day".</p>
                            
                            <div class="form-group">
                                <label class="input-label font-weight-bold">Costo Base de Envío ($)</label>
                                <input type="number" step="0.01" min="0" name="abastos_shipping_fee_next_day" 
                                    class="form-control" placeholder="Ej: 50" 
                                    value="{{ \App\CentralLogics\Helpers::get_business_settings('abastos_shipping_fee_next_day') ?? 50.00 }}" required>
                            </div>

                            <div class="form-group mb-0">
                                <label class="input-label font-weight-bold">Compra Mínima para Envío Gratis ($)</label>
                                <input type="number" step="0.01" min="0" name="abastos_free_shipping_min_next_day" 
                                    class="form-control" placeholder="Ej: 299" 
                                    value="{{ \App\CentralLogics\Helpers::get_business_settings('abastos_free_shipping_min_next_day') ?? 299.00 }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="col-12 mt-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        @if(isset($store) && $store->zone_id && $store->module_id)
                            <a href="{{ route('admin.business-settings.zone.grid-config', ['zone_id' => $store->zone_id, 'module_id' => $store->module_id]) }}" 
                               class="btn btn-outline-primary" target="_blank">
                                <i class="tio-map"></i> Ver / Configurar Rejilla Hexagonal (Mapa)
                            </a>
                        @endif
                        <div class="btn--container justify-content-end">
                            <button type="submit" class="btn btn--primary">
                                <i class="tio-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
