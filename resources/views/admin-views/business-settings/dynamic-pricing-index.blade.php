@extends('layouts.admin.app')

@section('title', 'Configuración de precios dinámicos')

@section('content')
    <div class="content">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/business.png') }}" class="w--26" alt="">
                </span>
                <span>
                    {{ 'entornos empresariales' }}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.nav-menu')
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.business-settings.update-dynamic-pricing') }}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-3">
                                <div>
                                    <h4 class="mb-1">{{ 'estado de precios dinámicos' }}</h4>
                                    <p class="mb-0 fs-12">
                                        {{ 'Habilite o deshabilite los multiplicadores de aumento de precios según la demanda en tiempo real.' }}
                                    </p>
                                </div>
                                <label class="toggle-switch toggle-switch-sm">
                                    <input type="checkbox" name="status" class="toggle-switch-input" {{ $config['status'] ? 'checked' : '' }}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="tio-trending-up mr-1"></i> {{ 'Umbrales de sobretensión' }}
                                    </h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addThreshold()">
                                        <i class="tio-add"></i> {{ 'Agregar umbral' }}
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table
                                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>{{ 'Ratio de Demanda (Pedidos / Repartidores)' }}</th>
                                                    <th>{{ 'Multiplicador (x)' }}</th>
                                                    <th class="text-center">{{ 'Acción' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="thresholds-table">
                                                @foreach($config['thresholds'] as $index => $threshold)
                                                    <tr>
                                                        <td>
                                                            <input type="number" step="0.1" name="ratio[]" class="form-control"
                                                                value="{{ $threshold['ratio'] }}" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.1" name="multiplier[]"
                                                                class="form-control" value="{{ $threshold['multiplier'] }}"
                                                                required>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                                onclick="removeRow(this)">
                                                                <i class="tio-delete"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3 text-right">
                            <button type="submit"
                                class="btn btn-primary">{{ 'guardar información' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Algorithm Suggestions -->
        <div class="card bg-info-light">
            <div class="card-body">
                <h5 class="text-info"><i class="tio-info-outined mr-1"></i> {{ 'Sugerencias de algoritmos' }}</h5>
                <p class="fs-12 mb-0">Basado en la demanda reciente, se sugiere mantener un multiplicador de 1.3x cuando el
                    ratio supere 2.0 y 1.5x cuando supere 3.0 para balancear la flota de repartidores.</p>
            </div>
        </div>
    </div>

    <script>
        function addThreshold() {
            let html = `
                <tr>
                    <td><input type="number" step="0.1" name="ratio[]" class="form-control" placeholder="Ratio" required></td>
                    <td><input type="number" step="0.1" name="multiplier[]" class="form-control" placeholder="Multiplier" required></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)">
                            <i class="tio-delete"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#thresholds-table').append(html);
        }

        function removeRow(btn) {
            $(btn).closest('tr').remove();
        }
    </script>
@endsection