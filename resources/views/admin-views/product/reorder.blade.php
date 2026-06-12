@extends('layouts.admin.app')

@section('title', 'Organizar Menú - Vista Móvil')

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
            width: 385px;
            height: 800px;
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
            height: 160px;
            position: relative;
            background-size: cover;
            background-position: center;
            background-color: #eee;
            flex-shrink: 0;
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
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            color: #333;
            font-size: 14px;
        }

        /* Store details overlapping card */
        .store-info-card {
            background: #fff;
            margin: -30px 15px 10px;
            border-radius: 20px;
            padding: 12px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.06);
            z-index: 5;
            position: relative;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.05);
            flex-shrink: 0;
        }

        .store-logo-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid #fff;
            margin: -35px auto 5px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            background-size: cover;
            background-position: center;
            background-color: #ddd;
        }

        .store-name-title {
            font-size: 16px;
            font-weight: 700;
            color: #0d1b2a;
            margin-bottom: 3px;
            line-height: 1.2;
        }

        .store-stats-row {
            display: flex;
            justify-content: space-around;
            font-size: 10px;
            color: #7f8c8d;
            margin-top: 8px;
            border-top: 1px solid #f2f2f2;
            padding-top: 6px;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stat-value {
            font-weight: 700;
            color: #2c3e50;
            font-size: 11px;
        }

        /* Promotions Section (Horizontal Widget) */
        .promotions-section {
            padding: 10px 15px 5px;
            background: #fff;
            flex-shrink: 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .promotions-title {
            font-size: 13px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .promotions-slider {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 8px;
            scrollbar-width: none;
        }

        .promotions-slider::-webkit-scrollbar {
            display: none;
        }

        .promo-card {
            flex: 0 0 120px;
            background: #fff;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .promo-image-wrapper {
            position: relative;
            height: 80px;
            width: 100%;
        }

        .promo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .promo-badge {
            position: absolute;
            top: 6px;
            left: 6px;
            background: #ef233c;
            color: #fff;
            font-size: 8px;
            font-weight: 800;
            padding: 1px 5px;
            border-radius: 4px;
            text-transform: uppercase;
            z-index: 2;
        }

        .promo-info {
            padding: 6px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .promo-name {
            font-size: 10px;
            font-weight: 700;
            color: #2c3e50;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .promo-price-row {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .promo-price {
            font-size: 10px;
            font-weight: 800;
            color: #2c3e50;
        }

        .promo-old-price {
            font-size: 8px;
            color: #95a5a6;
            text-decoration: line-through;
        }

        /* Horizontal drag & drop Categories */
        .categories-tab-bar {
            padding: 0 10px 5px;
            border-bottom: 1px solid #f0f0f0;
            background: #fff;
            z-index: 10;
            flex-shrink: 0;
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
            padding: 6px 12px;
            background: #f8f9fa;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
            color: #7f8c8d;
            white-space: nowrap;
            cursor: grab;
            border: 1.5px solid transparent;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
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
            border-radius: 16px;
        }

        /* Subcategories horizontal chips bar */
        .subcategories-bar {
            background: #fff;
            padding: 4px 10px 8px;
            border-bottom: 1px solid #f2f2f2;
            z-index: 10;
            flex-shrink: 0;
        }

        .subcategory-chips-row {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            scrollbar-width: none;
            padding: 2px 0;
        }

        .subcategory-chips-row::-webkit-scrollbar {
            display: none;
        }

        .subcategory-chip {
            padding: 4px 10px;
            background: #f1f2f6;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            color: #57606f;
            white-space: nowrap;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .subcategory-chip.active {
            background: #039f3f;
            color: #fff;
        }

        /* Product items list - Continuous scroll */
        .products-scroll-area {
            flex: 1;
            overflow-y: auto;
            padding: 10px 15px;
            background: #fafafa;
        }

        .category-section {
            margin-bottom: 25px;
        }

        .category-section-title {
            font-size: 14px;
            font-weight: 700;
            color: #2c3e50;
            margin: 10px 0 8px;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #eaeaea;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .subcategory-group {
            margin-bottom: 15px;
        }

        .subcategory-title-header {
            font-size: 12px;
            font-weight: 600;
            color: #7f8c8d;
            margin: 8px 0 6px;
            padding-left: 6px;
            border-left: 2.5px solid #039f3f;
        }

        .products-list-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-height: 20px; /* needed for sortable empty states */
        }

        .product-card-mockup {
            background: #fff;
            border-radius: 12px;
            padding: 10px;
            display: flex;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            border: 1px solid rgba(0,0,0,0.03);
            cursor: grab;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        .product-card-mockup:active {
            cursor: grabbing;
        }
        .product-card-mockup:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .product-info-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .product-title-text {
            font-size: 13px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .product-desc-text {
            font-size: 10px;
            color: #95a5a6;
            line-height: 1.3;
            margin-bottom: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price-row {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .product-price-text {
            font-size: 12px;
            font-weight: 800;
            color: #2c3e50;
        }

        .product-old-price-text {
            font-size: 10px;
            color: #95a5a6;
            text-decoration: line-through;
        }

        .product-image-wrapper {
            position: relative;
            width: 65px;
            height: 65px;
        }

        .product-img-circle {
            width: 65px;
            height: 65px;
            border-radius: 10px;
            object-fit: cover;
            background-color: #eee;
        }

        .add-mock-btn {
            position: absolute;
            bottom: -6px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            color: #039f3f;
            border: 1px solid #039f3f;
            border-radius: 6px;
            padding: 1px 8px;
            font-size: 9px;
            font-weight: 700;
            box-shadow: 0 2px 5px rgba(0,0,0,0.06);
            white-space: nowrap;
        }

        .product-drag-placeholder {
            border: 2px dashed #039f3f;
            background: rgba(3, 159, 63, 0.02);
            border-radius: 12px;
            height: 85px;
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
                Organizar Menú (Vista Móvil)
            </h1>
            <p class="page-header-text">
                Reordena las categorías y productos arrastrándolos directamente en el mockup celular. Los cambios se guardarán automáticamente.
            </p>
        </div>

        <div class="row g-3">
            <!-- Selector de tienda / Instrucciones -->
            <div class="col-lg-5 col-xl-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <form action="{{ route('admin.item.reorder') }}" method="GET" id="store-select-form">
                            <label class="input-label font-weight-bold text-dark">Selecciona un Restaurante</label>
                            <select name="store_id" class="form-control js-select2-custom" onchange="this.form.submit()">
                                <option value="">--- Seleccionar ---</option>
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
                        <h5 class="card-title text-primary"><i class="tio-info-outined mr-1"></i> ¿Cómo funciona?</h5>
                        <ul class="list-unstyled text-dark font-size-sm mb-0" style="line-height: 1.6;">
                            <li class="mb-2">
                                <span class="badge badge-soft-info mr-1">1</span>
                                Selecciona un restaurante para cargar su menú.
                            </li>
                            <li class="mb-2">
                                <span class="badge badge-soft-info mr-1">2</span>
                                Arrastra las pestañas horizontales de categorías para cambiar su orden en la app.
                            </li>
                            <li class="mb-2">
                                <span class="badge badge-soft-info mr-1">3</span>
                                Haz clic en una categoría para desplazarte automáticamente a su sección o usa la barra de subcategorías para filtrar de forma ágil.
                            </li>
                            <li>
                                <span class="badge badge-soft-info mr-1">4</span>
                                Reordena los platos arrastrándolos verticalmente dentro de su respectiva sección.
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

                                <!-- Promociones Horizontal Widget -->
                                <div class="promotions-section">
                                    <h5 class="promotions-title">
                                        <i class="tio-gift text-danger"></i> Promociones
                                    </h5>
                                    <div class="promotions-slider">
                                        @forelse($promotional_items as $promo_item)
                                            @php
                                                $discount_label = '';
                                                if ($promo_item->discount > 0) {
                                                    if ($promo_item->discount_type == 'percent') {
                                                        $discount_label = number_format($promo_item->discount, 0) . '% DCTO';
                                                    } else {
                                                        $discount_label = '$' . number_format($promo_item->discount, 0);
                                                    }
                                                } elseif ($promo_item->is_promotional == 1) {
                                                    $discount_label = 'PROMO';
                                                }

                                                $has_discount = $promo_item->discount > 0;
                                                $current_price = $promo_item->price;
                                                $old_price = null;
                                                if ($has_discount) {
                                                    if ($promo_item->discount_type == 'percent') {
                                                        $discount_amount = ($promo_item->price * $promo_item->discount) / 100;
                                                        $current_price = $promo_item->price - $discount_amount;
                                                        $old_price = $promo_item->price;
                                                    } else {
                                                        $current_price = $promo_item->price - $promo_item->discount;
                                                        $old_price = $promo_item->price;
                                                    }
                                                }
                                            @endphp
                                            <div class="promo-card">
                                                @if($discount_label)
                                                    <div class="promo-badge">{{ $discount_label }}</div>
                                                @endif
                                                <div class="promo-image-wrapper">
                                                    <img class="promo-img" src="{{ $promo_item->image_full_url }}" onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'">
                                                </div>
                                                <div class="promo-info">
                                                    <div class="promo-name">{{ $promo_item->name }}</div>
                                                    <div class="promo-price-row">
                                                        <span class="promo-price">${{ number_format($current_price, 2) }}</span>
                                                        @if($old_price)
                                                            <span class="promo-old-price">${{ number_format($old_price, 2) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-3 text-muted w-100 font-size-sm">No hay promociones activas</div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Categories Horizontal Bar -->
                                <div class="categories-tab-bar">
                                    <ul class="categories-draggable-list" id="category-list">
                                        @foreach($categories as $index => $category)
                                            <li class="category-tab-item {{ $index === 0 ? 'active' : '' }}" 
                                                data-id="{{ $category->id }}"
                                                onclick="scrollToCategory({{ $category->id }}, this)">
                                                <i class="tio-menu-hamburger mr-1 font-size-sm text-muted" style="cursor: grab;"></i>
                                                {{ $category->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <!-- Subcategories Horizontal Bar -->
                                <div class="subcategories-bar" id="subcategories-bar-container">
                                    @foreach($categories as $index => $category)
                                        @if(isset($subcategories_by_category[$category->id]) && count($subcategories_by_category[$category->id]) > 0)
                                            <div class="subcategory-chips-row {{ $index === 0 ? '' : 'd-none' }}" id="subcategories-for-{{ $category->id }}">
                                                <div class="subcategory-chip active" data-id="all-{{ $category->id }}" onclick="scrollToCategorySection({{ $category->id }}, 'all')">
                                                    Todos
                                                </div>
                                                @foreach($subcategories_by_category[$category->id] as $subcat)
                                                    <div class="subcategory-chip" data-id="{{ $subcat->id }}" onclick="scrollToCategorySection({{ $category->id }}, {{ $subcat->id }})">
                                                        {{ $subcat->name }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Products Continuous Scroll Area -->
                                <div class="products-scroll-area" id="products-scroll-area">
                                    @foreach($categories as $category)
                                        <div class="category-section" id="category-section-{{ $category->id }}" data-category-id="{{ $category->id }}">
                                            <h5 class="category-section-title">
                                                <span>{{ $category->name }}</span>
                                                <small class="text-muted" style="font-size: 10px;">Arrastra platos aquí</small>
                                            </h5>

                                            @if(isset($subcategories_by_category[$category->id]) && count($subcategories_by_category[$category->id]) > 0)
                                                @php
                                                    $subcat_ids = $subcategories_by_category[$category->id]->pluck('id')->toArray();
                                                    $has_subcat_groups = false;
                                                @endphp

                                                @foreach($subcategories_by_category[$category->id] as $subcat)
                                                    @php
                                                        $subcat_items = $items_by_category[$category->id]->filter(function($item) use ($subcat) {
                                                            return $item->category_id == $subcat->id;
                                                        });
                                                    @endphp

                                                    @if(count($subcat_items) > 0)
                                                        @php $has_subcat_groups = true; @endphp
                                                        <div class="subcategory-group" id="subcategory-group-{{ $subcat->id }}" data-subcategory-id="{{ $subcat->id }}">
                                                            <div class="subcategory-title-header">{{ $subcat->name }}</div>
                                                            <div class="products-list-container" data-category-id="{{ $category->id }}" data-subcategory-id="{{ $subcat->id }}">
                                                                @foreach($subcat_items as $item)
                                                                    @php
                                                                        $has_discount = $item->discount > 0;
                                                                        $current_price = $item->price;
                                                                        $old_price = null;
                                                                        if ($has_discount) {
                                                                            if ($item->discount_type == 'percent') {
                                                                                $discount_amount = ($item->price * $item->discount) / 100;
                                                                                $current_price = $item->price - $discount_amount;
                                                                                $old_price = $item->price;
                                                                            } else {
                                                                                $current_price = $item->price - $item->discount;
                                                                                $old_price = $item->price;
                                                                            }
                                                                        }
                                                                        $discount_label = '';
                                                                        if ($item->discount > 0) {
                                                                            if ($item->discount_type == 'percent') {
                                                                                $discount_label = number_format($item->discount, 0) . '% DCTO';
                                                                            } else {
                                                                                $discount_label = '$' . number_format($item->discount, 0);
                                                                            }
                                                                        } elseif ($item->is_promotional == 1) {
                                                                            $discount_label = 'PROMO';
                                                                        }
                                                                    @endphp
                                                                    <div class="product-card-mockup" data-id="{{ $item->id }}">
                                                                        @if($discount_label)
                                                                            <div class="promo-badge">{{ $discount_label }}</div>
                                                                        @endif
                                                                        <div class="product-info-col">
                                                                            <div class="product-title-text">{{ $item->name }}</div>
                                                                            <div class="product-desc-text">{{ $item->description ?? '' }}</div>
                                                                            <div class="product-price-row">
                                                                                <span class="product-price-text">${{ number_format($current_price, 2) }}</span>
                                                                                @if($old_price)
                                                                                    <span class="product-old-price-text">${{ number_format($old_price, 2) }}</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="product-image-wrapper">
                                                                            <img class="product-img-circle" src="{{ $item->image_full_url }}" onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'">
                                                                            <div class="add-mock-btn">Agregar</div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach

                                                @php
                                                    $uncategorized_items = $items_by_category[$category->id]->filter(function($item) use ($subcat_ids) {
                                                        return !in_array($item->category_id, $subcat_ids);
                                                    });
                                                @endphp

                                                @if(count($uncategorized_items) > 0)
                                                    <div class="subcategory-group" id="subcategory-group-uncat-{{ $category->id }}">
                                                        <div class="products-list-container" data-category-id="{{ $category->id }}" data-subcategory-id="">
                                                            @foreach($uncategorized_items as $item)
                                                                @php
                                                                    $has_discount = $item->discount > 0;
                                                                    $current_price = $item->price;
                                                                    $old_price = null;
                                                                    if ($has_discount) {
                                                                        if ($item->discount_type == 'percent') {
                                                                            $discount_amount = ($item->price * $item->discount) / 100;
                                                                            $current_price = $item->price - $discount_amount;
                                                                            $old_price = $item->price;
                                                                        } else {
                                                                            $current_price = $item->price - $item->discount;
                                                                            $old_price = $item->price;
                                                                        }
                                                                    }
                                                                    $discount_label = '';
                                                                    if ($item->discount > 0) {
                                                                        if ($item->discount_type == 'percent') {
                                                                            $discount_label = number_format($item->discount, 0) . '% DCTO';
                                                                        } else {
                                                                            $discount_label = '$' . number_format($item->discount, 0);
                                                                        }
                                                                    } elseif ($item->is_promotional == 1) {
                                                                        $discount_label = 'PROMO';
                                                                    }
                                                                @endphp
                                                                <div class="product-card-mockup" data-id="{{ $item->id }}">
                                                                    @if($discount_label)
                                                                        <div class="promo-badge">{{ $discount_label }}</div>
                                                                    @endif
                                                                    <div class="product-info-col">
                                                                        <div class="product-title-text">{{ $item->name }}</div>
                                                                        <div class="product-desc-text">{{ $item->description ?? '' }}</div>
                                                                        <div class="product-price-row">
                                                                            <span class="product-price-text">${{ number_format($current_price, 2) }}</span>
                                                                            @if($old_price)
                                                                                <span class="product-old-price-text">${{ number_format($old_price, 2) }}</span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    <div class="product-image-wrapper">
                                                                        <img class="product-img-circle" src="{{ $item->image_full_url }}" onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'">
                                                                        <div class="add-mock-btn">Agregar</div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if(!$has_subcat_groups && count($uncategorized_items) == 0)
                                                    <div class="text-center py-4 text-muted small">No hay productos en esta categoría.</div>
                                                @endif
                                            @else
                                                <div class="products-list-container" data-category-id="{{ $category->id }}">
                                                    @forelse($items_by_category[$category->id] as $item)
                                                        @php
                                                            $has_discount = $item->discount > 0;
                                                            $current_price = $item->price;
                                                            $old_price = null;
                                                            if ($has_discount) {
                                                                if ($item->discount_type == 'percent') {
                                                                    $discount_amount = ($item->price * $item->discount) / 100;
                                                                    $current_price = $item->price - $discount_amount;
                                                                    $old_price = $item->price;
                                                                } else {
                                                                    $current_price = $item->price - $item->discount;
                                                                    $old_price = $item->price;
                                                                }
                                                            }
                                                            $discount_label = '';
                                                            if ($item->discount > 0) {
                                                                if ($item->discount_type == 'percent') {
                                                                    $discount_label = number_format($item->discount, 0) . '% DCTO';
                                                                } else {
                                                                    $discount_label = '$' . number_format($item->discount, 0);
                                                                }
                                                            } elseif ($item->is_promotional == 1) {
                                                                $discount_label = 'PROMO';
                                                            }
                                                        @endphp
                                                        <div class="product-card-mockup" data-id="{{ $item->id }}">
                                                            @if($discount_label)
                                                                <div class="promo-badge">{{ $discount_label }}</div>
                                                            @endif
                                                            <div class="product-info-col">
                                                                <div class="product-title-text">{{ $item->name }}</div>
                                                                <div class="product-desc-text">{{ $item->description ?? '' }}</div>
                                                                <div class="product-price-row">
                                                                    <span class="product-price-text">${{ number_format($current_price, 2) }}</span>
                                                                    @if($old_price)
                                                                        <span class="product-old-price-text">${{ number_format($old_price, 2) }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="product-image-wrapper">
                                                                <img class="product-img-circle" src="{{ $item->image_full_url }}" onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'">
                                                                <div class="add-mock-btn">Agregar</div>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="text-center py-4 text-muted small">No hay productos en esta categoría.</div>
                                                    @endforelse
                                                </div>
                                            @endif
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
                            <h4>Por favor, selecciona un restaurante para empezar a organizar el menú.</h4>
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
        var isScrollingProgrammatically = false;

        $(document).ready(function() {
            // Sortable para Categorías (Horizontal)
            $("#category-list").sortable({
                placeholder: "category-drag-placeholder",
                axis: "x",
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

            // Scroll monitor to sync active categories and subcategories
            $('#products-scroll-area').on('scroll', function() {
                if (isScrollingProgrammatically) return;

                var scrollArea = $(this);
                var containerTop = scrollArea.offset().top;
                var activeCategoryId = null;

                // Find which category section is currently viewable at the top of the container
                $('.category-section').each(function() {
                    var section = $(this);
                    var sectionTop = section.offset().top - containerTop;
                    
                    // If the top of the category section is near or above the top of the viewport
                    if (sectionTop <= 30) {
                        activeCategoryId = section.data('category-id');
                    }
                });

                if (activeCategoryId) {
                    // Update active category tab
                    $('.category-tab-item').removeClass('active');
                    var activeTab = $('.category-tab-item[data-id="' + activeCategoryId + '"]');
                    activeTab.addClass('active');

                    // Scroll category tab bar to center the active category tab
                    var tabBar = $('.categories-draggable-list');
                    if (tabBar.length && activeTab.length) {
                        var scrollLeft = activeTab.offset().left - tabBar.offset().left + tabBar.scrollLeft() - (tabBar.width() / 2) + (activeTab.width() / 2);
                        tabBar.stop().animate({ scrollLeft: scrollLeft }, 150);
                    }

                    // Show correct subcategories chips row
                    $('.subcategory-chips-row').addClass('d-none');
                    var subcatRow = $('#subcategories-for-' + activeCategoryId);
                    if (subcatRow.length) {
                        subcatRow.removeClass('d-none');
                    }

                    // Synchronize active subcategory chip based on scroll position within that category
                    var activeSubcatId = 'all-' + activeCategoryId;
                    $('#category-section-' + activeCategoryId + ' .subcategory-group').each(function() {
                        var subGroup = $(this);
                        var groupTop = subGroup.offset().top - containerTop;
                        if (groupTop <= 40) {
                            activeSubcatId = subGroup.data('subcategory-id');
                        }
                    });

                    // Set active chip
                    if (subcatRow.length) {
                        subcatRow.find('.subcategory-chip').removeClass('active');
                        subcatRow.find('.subcategory-chip[data-id="' + activeSubcatId + '"]').addClass('active');
                    }
                }
            });
        });

        // Click to scroll to category section
        function scrollToCategory(categoryId, element) {
            if ($(element).hasClass('ui-sortable-helper')) return;

            $('.category-tab-item').removeClass('active');
            $(element).addClass('active');

            // Show corresponding subcategory chips row
            $('.subcategory-chips-row').addClass('d-none');
            var subcatRow = $('#subcategories-for-' + categoryId);
            if (subcatRow.length) {
                subcatRow.removeClass('d-none');
                subcatRow.find('.subcategory-chip').removeClass('active');
                subcatRow.find('.subcategory-chip[data-id="all-' + categoryId + '"]').addClass('active');
            }

            isScrollingProgrammatically = true;
            var container = $('#products-scroll-area');
            var target = $('#category-section-' + categoryId);
            
            if (target.length) {
                container.stop().animate({
                    scrollTop: target.offset().top - container.offset().top + container.scrollTop()
                }, 400, function() {
                    isScrollingProgrammatically = false;
                });
            } else {
                isScrollingProgrammatically = false;
            }
        }

        // Scroll to subcategory or category top
        function scrollToCategorySection(categoryId, subcatId) {
            var container = $('#products-scroll-area');
            var target;

            if (subcatId === 'all') {
                target = $('#category-section-' + categoryId);
            } else {
                target = $('#subcategory-group-' + subcatId);
            }

            // Set active class on clicked subcategory chip
            var subcatRow = $('#subcategories-for-' + categoryId);
            if (subcatRow.length) {
                subcatRow.find('.subcategory-chip').removeClass('active');
                subcatRow.find('.subcategory-chip[data-id="' + subcatId + '"]').addClass('active');
            }

            if (target && target.length) {
                isScrollingProgrammatically = true;
                container.stop().animate({
                    scrollTop: target.offset().top - container.offset().top + container.scrollTop()
                }, 400, function() {
                    isScrollingProgrammatically = false;
                });
            }
        }
    </script>
@endpush
