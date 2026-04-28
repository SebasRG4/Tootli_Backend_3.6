@extends('layouts.admin.app')

@section('title', translate('Organizar Menú'))

@push('css_or_js')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    <style>
        .sortable-item {
            background: #fff;
            border: 1px solid #eee;
            margin-bottom: 8px;
            padding: 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.2s;
        }
        .sortable-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .handle {
            cursor: grab;
            padding: 5px 10px;
            background: #f1f1f1;
            border-radius: 4px;
            color: #888;
        }
        .handle:active {
            cursor: grabbing;
        }
        .sortable-placeholder {
            border: 2px dashed #ccc;
            background: #f9f9f9;
            height: 50px;
            margin-bottom: 8px;
            border-radius: 8px;
        }
        .category-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #e7eaf3;
        }
        .category-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #334257;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 20px;
            color: #1e2022;
            padding-bottom: 10px;
            border-bottom: 2px solid #377dff;
            display: inline-block;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon"><i class="tio-format-bullets"></i></span>
                {{ translate('Organizar Menú (TootliClick)') }}
            </h1>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.item.reorder') }}" method="GET">
                    <div class="row align-items-end g-2">
                        <div class="col-sm-8">
                            <label class="input-label">{{ translate('Selecciona un Restaurante') }}</label>
                            <select name="store_id" class="form-control js-select2-custom" onchange="this.form.submit()">
                                <option value="">--- {{ translate('Seleccionar') }} ---</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ isset($selected_store) && $selected_store->id == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <button type="submit" class="btn btn-primary btn-block">{{ translate('Cargar Configuración') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(isset($selected_store))
            <div class="alert alert-soft-info mb-4">
                <i class="tio-info-outined mr-1"></i>
                {{ translate('Usa el icono gris de la izquierda para arrastrar y reordenar tanto categorías como productos.') }}
            </div>

            <!-- ORGANIZAR CATEGORÍAS -->
            <div class="mb-5">
                <div class="section-title">{{ translate('1. Orden de Categorías') }}</div>
                <div class="sortable-categories" id="category-list">
                    @foreach($categories as $category)
                        <div class="sortable-item" data-id="{{ $category->id }}" style="border-left: 5px solid #377dff;">
                            <div class="handle category-handle">
                                <i class="tio-move-up-down"></i>
                            </div>
                            <div class="flex-grow-1">
                                <span class="font-weight-bold" style="font-size: 1.1rem;">{{ $category->name }}</span>
                            </div>
                            <div class="badge badge-soft-info">
                                {{ translate('Categoría') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- ORGANIZAR PRODUCTOS -->
            <div class="mb-5">
                <div class="section-title">{{ translate('2. Orden de Productos por Categoría') }}</div>
                @foreach($categories as $category)
                    <div class="category-container">
                        <div class="category-title">
                            <i class="tio-folder-opened"></i> {{ $category->name }}
                        </div>
                        <div class="sortable-products" data-category-id="{{ $category->id }}">
                            @foreach($items_by_category[$category->id] as $item)
                                <div class="sortable-item" data-id="{{ $item->id }}">
                                    <div class="handle product-handle">
                                        <i class="tio-move-up-down"></i>
                                    </div>
                                    <img src="{{ $item->image_full_url }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;" onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'">
                                    <div class="flex-grow-1">
                                        <span class="font-weight-bold">{{ $item->name }}</span>
                                        <div class="text-muted small">${{ number_format($item->price, 2) }}</div>
                                    </div>
                                    <div class="badge badge-soft-secondary">
                                        ID: {{ $item->id }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('script_2')
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            // Sortable para Categorías
            $("#category-list").sortable({
                placeholder: "sortable-placeholder",
                handle: ".category-handle",
                update: function(event, ui) {
                    let order = [];
                    $(this).children(".sortable-item").each(function() {
                        order.push($(this).data('id'));
                    });

                    $.ajax({
                        url: "{{ route('admin.item.update-category-reorder') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            order: order
                        },
                        success: function(response) {
                            toastr.success(response.message);
                        }
                    });
                }
            });

            // Sortable para Productos
            $(".sortable-products").sortable({
                placeholder: "sortable-placeholder",
                handle: ".product-handle",
                update: function(event, ui) {
                    let order = [];
                    $(this).children(".sortable-item").each(function() {
                        order.push($(this).data('id'));
                    });

                    $.ajax({
                        url: "{{ route('admin.item.update-reorder') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            order: order
                        },
                        success: function(response) {
                            toastr.success(response.message);
                        }
                    });
                }
            });
        });
    </script>
@endpush
