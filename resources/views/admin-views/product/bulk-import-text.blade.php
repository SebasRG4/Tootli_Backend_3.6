@extends('layouts.admin.app')

@section('title', 'Carga Rápida de Menú')

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{ asset('assets/admin/img/items.png') }}" class="w--22" alt="">
                </span>
                <span>Carga Rápida de Menú (Texto a Productos)</span>
            </h1>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.item.bulk-import-text-process') }}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="input-label">Seleccionar Tienda / Restaurante</label>
                            <select name="store_id" id="store_id" class="form-control js-select2-custom" required>
                                <option value="" selected disabled>Seleccione una tienda</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="input-label">Categoría Destino</label>
                            <select name="category_id" id="category_id" class="form-control js-select2-custom" required>
                                <option value="" selected disabled>Seleccione una categoría</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="input-label">Disponible Desde</label>
                            <input type="time" name="available_time_starts" class="form-control" value="00:00">
                        </div>
                        <div class="col-md-2">
                            <label class="input-label">Disponible Hasta</label>
                            <input type="time" name="available_time_ends" class="form-control" value="23:59">
                        </div>

                        <div class="col-12 mt-3">
                            <div class="card bg-light border">
                                <div class="card-body">
                                    <h5 class="card-title text-primary"><i class="tio-info-outined"></i> Variaciones Comunes (Se aplicarán a todos los productos)</h5>
                                    <div id="common_variations">
                                        <div class="row g-2 variation-row">
                                            <div class="col-md-3">
                                                <input type="text" name="var_name[]" class="form-control" placeholder="Nombre (Ej: Salsa)">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" name="var_options[]" class="form-control" placeholder="Opciones y Precios (Ej: Verde:0, Roja:10, Habanero:15)">
                                            </div>
                                            <div class="col-md-3">
                                                <select name="var_type[]" class="form-control">
                                                    <option value="single">Selección Única</option>
                                                    <option value="multi">Selección Múltiple</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" onclick="addVariationRow()" class="btn btn-sm btn-outline-primary mt-2">+ Añadir otra variación común</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="input-label">Imagen para todo este lote (Opcional)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">* Si seleccionas una imagen, se aplicará a todos los productos de esta lista.</small>
                        </div>

                        <div class="col-12">
                            <label class="input-label mt-3 text-primary"><b>Pegar Menú (Nombre $Precio)</b></label>
                            <textarea name="bulk_text" class="form-control" rows="10" placeholder="Ejemplo:
Chilaquiles sencillos $50
Chilaquiles pollo $90" required></textarea>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-4">
                        <button type="submit" class="btn btn--primary btn-lg">Procesar e Importar Todo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        function addVariationRow() {
            let row = `
                <div class="row g-2 variation-row mt-2">
                    <div class="col-md-3">
                        <input type="text" name="var_name[]" class="form-control" placeholder="Nombre (Ej: Salsa)">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="var_options[]" class="form-control" placeholder="Opciones y Precios (Ej: Verde:0, Roja:10)">
                    </div>
                    <div class="col-md-3">
                        <select name="var_type[]" class="form-control">
                            <option value="single">Selección Única</option>
                            <option value="multi">Selección Múltiple</option>
                        </select>
                    </div>
                </div>`;
            $('#common_variations').append(row);
        }

        $('#store_id').on('change', function() {
            let storeId = $(this).val();
            // Cargar categorías según el módulo de la tienda
            $.get({
                url: '{{ url('/') }}/admin/item/get-categories?parent_id=0',
                dataType: 'json',
                success: function(data) {
                    $('#category_id').empty().append('<option value="" selected disabled>Seleccione una categoría</option>');
                    $.each(data, function(index, value) {
                        $('#category_id').append('<option value="' + value.id + '">' + value.text + '</option>');
                    });
                }
            });
        });
    </script>
@endpush
