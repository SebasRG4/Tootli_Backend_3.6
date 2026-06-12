@extends('layouts.admin.app')

@section('title', translate('Organizar Menú - Vista Móvil'))

@push('css_or_js')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    <style>
        /* Modern Mockup Frame Styles */
        .mockup-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 0;
            background: radial-gradient(circle, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            box-shadow: inset 0 0 20px rgba(0,0,0,0.05);
            border: 1px solid #e7eaf3;
        }

        .phone-mockup {
            width: 380px;
            height: 780px;
            background: #000;
            border-radius: 50px;
            padding: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            position: relative;
            border: 4px solid #333;
        }

        /* Phone Screen inside Mockup */
        .phone-screen {
            width: 100%;
            height: 100%;
            background: #fdfdfd;
            border-radius: 40px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
            font-family: 'Inter', 'Roboto', sans-serif;
            user-select: none;
        }

        /* Top Notch / Dynamic Island */
        .phone-notch {
            position: absolute;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            width: 110px;
            height: 30px;
            background: #000;
            border-radius: 20px;
            z-index: 100;
        }

        /* Simulated Status Bar */
        .status-bar {
            height: 44px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 0 24px 8px;
            font-size: 12px;
            font-weight: 600;
            color: #000;
            z-index: 99;
            background: transparent;
            position: absolute;
            width: 100%;
        }

        .status-bar-icons {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        /* Banner section */
        .store-banner-wrapper {
            height: 200px;
            position: relative;
            background-size: cover;
            background-position: center;
            background-color: #eee;
        }

        .banner-overlay-icons {
            position: absolute;
            top: 50px;
            left: 15px;
            right: 15px;
            display: flex;
            justify-content: space-between;
            z-index: 10;
        }

        .overlay-icon-btn {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            color: #333;
            font-size: 16px;
        }

        /* Store details overlapping card */
        .store-info-card {
            background: #fff;
            margin: -40px 15px 15px;
            border-radius: 20px;
            padding: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.06);
            z-index: 5;
            position: relative;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .store-logo-circle {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 3px solid #fff;
            margin: -45px auto 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            background-size: cover;
            background-position: center;
            background-color: #ddd;
        }

        .store-name-title {
            font-size: 18px;
            font-weight: 700;
            color: #0d1b2a;
            margin-bottom: 5px;
            line-height: 1.2;
        }

        .store-stats-row {
            display: flex;
            justify-content: space-around;
            font-size: 11px;
            color: #7f8c8d;
            margin-top: 10px;
            border-top: 1px solid #f2f2f2;
            padding-top: 8px;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stat-value {
            font-weight: 700;
            color: #2c3e50;
            font-size: 12px;
        }

        /* Horizontal drag & drop Categories */
        .categories-tab-bar {
            padding: 0 10px 5px;
            border-bottom: 1px solid #f0f0f0;
            background: #fff;
            z-index: 10;
        }

        .categories-draggable-list {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 5px 0;
            list-style: none;
            margin: 0;
            scrollbar-width: none;
        }
        .categories-draggable-list::-webkit-scrollbar {
            display: none;
        }

        .category-tab-item {
            padding: 8px 16px;
            background: #f8f9fa;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: #7f8c8d;
            white-space: nowrap;
            cursor: grab;
            border: 1.5px solid transparent;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .category-tab-item:active {
            cursor: grabbing;
        }

        .category-tab-item.active {
            background: #eef9f2;
            color: #039f3f;
            border-color: #039f3f;
        }

        .category-drag-placeholder {
            width: 60px;
            background: rgba(3, 159, 63, 0.1);
            border: 1.5px dashed #039f3f;
            border-radius: 20px;
        }

        /* Product items list */
        .products-scroll-area {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #fafafa;
        }

        .products-list-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .product-card-mockup {
            background: #fff;
            border-radius: 16px;
            padding: 12px;
            display: flex;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.04);
            cursor: grab;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        .product-card-mockup:active {
            cursor: grabbing;
        }
        .product-card-mockup:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        .product-info-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .product-title-text {
            font-size: 14px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .product-desc-text {
            font-size: 11px;
            color: #95a5a6;
            line-height: 1.3;
            margin-bottom: 6px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price-text {
            font-size: 13px;
            font-weight: 800;
            color: #2c3e50;
        }

        .product-image-wrapper {
            position: relative;
            width: 75px;
            height: 75px;
        }

        .product-img-circle {
            width: 75px;
            height: 75px;
            border-radius: 12px;
            object-fit: cover;
            background-color: #eee;
        }

        .add-mock-btn {
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            color: #039f3f;
            border: 1px solid #039f3f;
            border-radius: 8px;
            padding: 2px 10px;
            font-size: 10px;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            white-space: nowrap;
        }

        .product-drag-placeholder {
            border: 2px dashed #039f3f;
            background: rgba(3, 159, 63, 0.03);
            border-radius: 16px;
            height: 95px;
        }

        /* Guide Card style */
        .instruction-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e7eaf3;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon"><i class="tio-android-phone-video"></i></span>
                {{ translate('Organizar Menú (Vista Móvil)') }}
            </h1>
            <p class="page-header-text">
                {{ translate('Reordena las categorías y productos arrastrándolos directamente en el mockup celular. Los cambios se guardarán automáticamente.') }}
            </p>
        </div>

        <div class="row g-3">
            <!-- Selector de tienda / Instrucciones -->
            <div class="col-lg-5 col-xl-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <form action="{{ route('admin.item.reorder') }}" method="GET" id="store-select-form">
                            <label class="input-label font-weight-bold text-dark">{{ translate('Selecciona un Restaurante') }}</label>
                            <select name="store_id" class="form-control js-select2-custom" onchange="this.form.submit()">
                                <option value="">--- {{ translate('Seleccionar') }} ---</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ isset($selected_store) && $selected_store->id == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="card instruction-card">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="tio-info-outined mr-1"></i> {{ translate('¿Cómo funciona?') }}</h5>
                        <ul class="list-unstyled text-dark font-size-sm mb-0" style="line-height: 1.6;">
                            <li class="mb-2">
                                <span class="badge badge-soft-info mr-1">1</span>
                                {{ translate('Selecciona un restaurante para cargar su menú.') }}
                            </li>
                            <li class="mb-2">
                                <span class="badge badge-soft-info mr-1">2</span>
                                {{ translate('Arrastra las pestañas verdes horizontales en el celular para reordenar las categorías.') }}
                            </li>
                            <li class="mb-2">
                                <span class="badge badge-soft-info mr-1">3</span>
                                {{ translate('Selecciona cualquier pestaña de categoría para cargar sus respectivos productos.') }}
                            </li>
                            <li>
                                <span class="badge badge-soft-info mr-1">4</span>
                                {{ translate('Arrastra los platos verticalmente dentro de la lista para cambiar su orden de visualización.') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Columna del Mockup Celular -->
            <div class="col-lg-7 col-xl-8">
                @if(isset($selected_store))
                    <div class="mockup-container">
                        <div class="phone-mockup">
                            <div class="phone-notch"></div>
                            
                            <div class="phone-screen">
                                <!-- Status bar simulation -->
                                <div class="status-bar">
                                    <span>12:57</span>
                                    <div class="status-bar-icons">
                                        <i class="tio-wifi"></i>
                                        <i class="tio-battery-half"></i>
                                    </div>
                                </div>

                                <!-- Cover Banner -->
                                <div class="store-banner-wrapper" style="background-image: url('{{ $selected_store->cover_photo_full_url }}');">
                                    <div class="banner-overlay-icons">
                                        <div class="overlay-icon-btn"><i class="tio-chevron-left"></i></div>
                                        <div class="d-flex gap-2">
                                            <div class="overlay-icon-btn"><i class="tio-heart"></i></div>
                                            <div class="overlay-icon-btn"><i class="tio-share"></i></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Restaurant info card -->
                                <div class="store-info-card">
                                    <div class="store-logo-circle" style="background-image: url('{{ $selected_store->logo_full_url }}');"></div>
                                    <h4 class="store-name-title">{{ $selected_store->name }}</h4>
                                    
                                    <div class="store-stats-row">
                                        <div class="stat-item">
                                            <span class="stat-value">4.4</span>
                                            <span>Clasificación</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-value">1.7 km</span>
                                            <span>Distancia</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-value">{{ $selected_store->delivery_time ?? '25-50 min' }}</span>
                                            <span>Tiempo</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Categories Horizontal Bar -->
                                <div class="categories-tab-bar">
                                    <ul class="categories-draggable-list" id="category-list">
                                        @foreach($categories as $index => $category)
                                            <li class="category-tab-item {{ $index === 0 ? 'active' : '' }}" 
                                                data-id="{{ $category->id }}"
                                                onclick="switchCategory({{ $category->id }}, this)">
                                                <i class="tio-menu-hamburger mr-1 font-size-sm text-muted"></i>
                                                {{ $category->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <!-- Products Scrollable List -->
                                <div class="products-scroll-area">
                                    @foreach($categories as $index => $category)
                                        <div class="products-list-container category-products-section {{ $index === 0 ? '' : 'd-none' }}" 
                                             id="category-section-{{ $category->id }}" 
                                             data-category-id="{{ $category->id }}">
                                            @forelse($items_by_category[$category->id] as $item)
                                                <div class="product-card-mockup" data-id="{{ $item->id }}">
                                                    <div class="product-info-col">
                                                        <div class="product-title-text">{{ $item->name }}</div>
                                                        <div class="product-desc-text">{{ $item->description ?? '' }}</div>
                                                        <div class="product-price-text">${{ number_format($item->price, 2) }}</div>
                                                    </div>
                                                    <div class="product-image-wrapper">
                                                        <img class="product-img-circle" src="{{ $item->image_full_url }}" onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'">
                                                        <div class="add-mock-btn">Agregar</div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-4 text-muted small">{{ translate('No hay productos en esta categoría.') }}</div>
                                            @endforelse
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm text-center py-5">
                        <div class="card-body">
                            <img src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}" style="width: 150px; margin-bottom: 20px;">
                            <h4>{{ translate('Por favor, selecciona un restaurante para empezar a organizar el menú.') }}</h4>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            // Sortable para Categorías (Horizontal)
            $("#category-list").sortable({
                placeholder: "category-drag-placeholder",
                axis: "x",
                containment: "parent",
                tolerance: "pointer",
                update: function(event, ui) {
                    let order = [];
                    $(this).children(".category-tab-item").each(function() {
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

            // Sortable para Productos (Vertical)
            $(".products-list-container").sortable({
                placeholder: "product-drag-placeholder",
                axis: "y",
                update: function(event, ui) {
                    let order = [];
                    $(this).children(".product-card-mockup").each(function() {
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

        // Alternar visualización de categorías
        function switchCategory(categoryId, element) {
            // No cambiar si estamos arrastrando
            if ($(element).hasClass('ui-sortable-helper')) return;

            // Cambiar pestaña activa
            $('.category-tab-item').removeClass('active');
            $(element).addClass('active');

            // Mostrar sección de productos correspondiente
            $('.category-products-section').addClass('d-none');
            $('#category-section-' + categoryId).removeClass('d-none');
        }
    </script>
@endpush
