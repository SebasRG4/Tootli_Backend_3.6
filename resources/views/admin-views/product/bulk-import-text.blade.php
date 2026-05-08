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
                        <div class="col-md-6">
                            <label class="input-label">Seleccionar Tienda / Restaurante</label>
                            <select name="store_id" id="store_id" class="form-control js-select2-custom" required>
                                <option value="" selected disabled>Seleccione una tienda</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="input-label">Categoría Destino</label>
                            <select name="category_id" id="category_id" class="form-control js-select2-custom" required>
                                <option value="" selected disabled>Seleccione una categoría</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="input-label">Pegar Menú (Ejemplo: Plato $Precio)</label>
                            <textarea name="bulk_text" class="form-control" rows="15" placeholder="Pega aquí el texto, por ejemplo:
- Chilaquiles sencillos $50
- Chilaquiles pollo $90
Refresco $40" required></textarea>
                            <small class="text-muted mt-2 d-block">
                                * El sistema detectará automáticamente el nombre y el precio final de cada línea.
                            </small>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-4">
                        <button type="submit" class="btn btn--primary">Procesar e Importar Todo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
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
