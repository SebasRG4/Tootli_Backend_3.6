@extends('layouts.admin.app')

@section('title', translate('TootliClick Settings'))

@push('css_or_js')
    <!-- Custom CSS for dynamic rows if needed -->
@endpush

@section('content')
    <div class="content container-fluid">
        @include('admin-views.vendor.view.partials._header')

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="tio-settings-outlined"></i>
                    {{ translate('Configuración de Menú Digital TootliClick') }}
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.store.tootliclick-settings-update', $store->id) }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{ translate('Habilitar Envío por Zonas/Colonias') }}</label>
                                <input type="checkbox" name="shipping_enabled" value="1" {{ (isset($store->tootliclick_settings['shipping_enabled']) && $store->tootliclick_settings['shipping_enabled']) ? 'checked' : '' }}>
                                <small class="text-muted d-block">{{ translate('Si se habilita, el cliente deberá seleccionar una colonia de la lista para ver el costo de envío.') }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{ translate('Mínimo de compra para envío GRATIS') }}</label>
                                <input type="number" step="0.01" name="free_delivery_min_amount" class="form-control" value="{{ $store->tootliclick_settings['free_delivery_min_amount'] ?? 0 }}">
                                <small class="text-muted">{{ translate('Coloca 0 para no ofrecer envío gratis.') }}</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label class="input-label d-flex justify-content-between align-items-center">
                            {{ translate('Colonias y Costos de Envío') }}
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addColoniaRow()">
                                <i class="tio-add"></i> {{ translate('Agregar Colonia') }}
                            </button>
                        </label>
                        
                        <div id="colonias-container">
                            @if(isset($store->tootliclick_settings['colonias']) && count($store->tootliclick_settings['colonias']) > 0)
                                @foreach($store->tootliclick_settings['colonias'] as $index => $col)
                                    <div class="row mb-2 colonia-row">
                                        <div class="col-md-7">
                                            <input type="text" name="colonia_name[]" class="form-control" placeholder="Nombre de la Colonia" value="{{ $col['name'] }}">
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" step="0.01" name="colonia_price[]" class="form-control" placeholder="Costo" value="{{ $col['price'] }}">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-outline-danger" onclick="$(this).closest('.colonia-row').remove()">
                                                <i class="tio-delete"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="row mb-2 colonia-row">
                                    <div class="col-md-7">
                                        <input type="text" name="colonia_name[]" class="form-control" placeholder="Nombre de la Colonia">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" step="0.01" name="colonia_price[]" class="form-control" placeholder="Costo">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger" onclick="$(this).closest('.colonia-row').remove()">
                                            <i class="tio-delete"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn--primary">{{ translate('Guardar Configuración') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    function addColoniaRow() {
        const html = `
            <div class="row mb-2 colonia-row">
                <div class="col-md-7">
                    <input type="text" name="colonia_name[]" class="form-control" placeholder="Nombre de la Colonia">
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" name="colonia_price[]" class="form-control" placeholder="Costo">
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger" onclick="$(this).closest('.colonia-row').remove()">
                        <i class="tio-delete"></i>
                    </button>
                </div>
            </div>
        `;
        $('#colonias-container').append(html);
    }
</script>
@endpush
