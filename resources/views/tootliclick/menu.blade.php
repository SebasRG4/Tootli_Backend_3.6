<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $store->name }} | Menú Digital TootliClick</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        :root {
            --primary: #00B9BD;
            --secondary: #005555;
            --bg-light: #F8FAFA;
            --text-dark: #1A1A1A;
            --text-muted: #666;
            --white: #FFFFFF;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --radius: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.5;
            padding-bottom: 100px;
        }

        /* Header & Cover */
        .header {
            position: relative;
            height: 220px;
            background-image: url('{{ $store->cover_photo_full_url }}');
            background-size: cover;
            background-position: center;
        }

        .header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.6));
        }

        .store-info {
            position: relative;
            background: var(--white);
            margin: -60px 20px 20px;
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
            z-index: 10;
        }

        .store-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--white);
            margin: -74px auto 16px;
            background: var(--white);
            object-fit: cover;
            box-shadow: var(--shadow);
        }

        .store-name {
            font-size: 24px;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 4px;
        }

        .store-address {
            font-size: 14px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        /* Categories Navigation */
        .categories-nav {
            position: sticky;
            top: 0;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 16px 20px;
            white-space: nowrap;
            overflow-x: auto;
            z-index: 100;
            border-bottom: 1px solid #EEE;
            scrollbar-width: none;
        }

        .categories-nav::-webkit-scrollbar {
            display: none;
        }

        .category-chip {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 10px;
            background: #EEE;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .category-chip.active {
            background: var(--primary);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(0, 185, 189, 0.3);
        }

        /* Menu Items */
        .menu-section {
            padding: 24px 20px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--secondary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #EEE;
        }

        .items-grid {
            display: grid;
            gap: 16px;
        }

        .item-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 16px;
            display: flex;
            gap: 16px;
            box-shadow: var(--shadow);
            transition: transform 0.2s ease;
        }

        .item-card:active {
            transform: scale(0.98);
        }

        .item-image {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            object-fit: cover;
            background: #F0F0F0;
        }

        .item-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .item-name {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .item-desc {
            font-size: 13px;
            color: var(--text-muted);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .item-price {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
        }

        .add-btn {
            background: var(--primary);
            color: var(--white);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
            font-size: 18px;
        }

        /* Cart Bar */
        .cart-bar {
            position: fixed;
            bottom: 24px;
            left: 20px;
            right: 20px;
            background: var(--secondary);
            height: 64px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            color: var(--white);
            box-shadow: 0 8px 32px rgba(0, 85, 85, 0.4);
            transform: translateY(150%);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 1000;
            cursor: pointer;
        }

        .cart-bar.visible {
            transform: translateY(0);
        }

        .cart-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cart-count {
            background: var(--primary);
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .cart-total {
            font-weight: 700;
            font-size: 18px;
        }

        .view-cart-btn {
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Modal */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            display: none;
            align-items: flex-end;
        }

        .modal.open {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            width: 100%;
            max-width: 500px;
            border-radius: 24px 24px 0 0;
            padding: 32px 24px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease-out;
            -webkit-overflow-scrolling: touch;
        }

        .variation-group {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #EEE;
        }

        .variation-group:last-child {
            border-bottom: none;
        }

        .variation-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .variation-badge {
            font-size: 10px;
            background: var(--bg-light);
            padding: 4px 8px;
            border-radius: 4px;
            color: var(--text-muted);
        }

        .variation-option {
            display: flex;
            align-items: center;
            padding: 12px 0;
            cursor: pointer;
        }

        .variation-option input {
            margin-right: 12px;
            width: 20px;
            height: 20px;
            accent-color: var(--primary);
        }

        .option-label {
            flex: 1;
            font-size: 14px;
        }

        .option-price {
            font-weight: 600;
            color: var(--primary);
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .modal-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 24px;
            text-align: center;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-muted);
        }

        .input-group input, .input-group textarea {
            width: 100%;
            padding: 16px;
            background: var(--bg-light);
            border: 1px solid #EEE;
            border-radius: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
        }

        .whatsapp-btn {
            background: #25D366;
            color: var(--white);
            width: 100%;
            padding: 18px;
            border-radius: 14px;
            border: none;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 12px;
            cursor: pointer;
        }

        .location-btn {
            background: var(--bg-light);
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 12px;
            border-radius: 10px;
            width: 100%;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
            cursor: pointer;
        }

        .order-type-selector {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .order-type-option {
            flex: 1;
            padding: 12px;
            border: 1px solid #EEE;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .order-type-option.active {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

    <div class="header"></div>

    <div class="store-info">
        <img src="{{ $store->logo_full_url }}" alt="{{ $store->name }}" class="store-logo">
        <h1 class="store-name">{{ $store->name }}</h1>
        <div class="store-address">
            <i class="fas fa-map-marker-alt"></i>
            {{ $store->address }}
        </div>
    </div>

    @if($categories->count() > 0)
    <div class="categories-nav">
        @foreach($categories as $category)
            <a href="#cat-{{ $category->id }}" class="category-chip {{ $loop->first ? 'active' : '' }}" onclick="setActiveCategory(this)">
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    @foreach($items_by_category as $cat_id => $data)
        <section id="cat-{{ $cat_id }}" class="menu-section">
            <h2 class="section-title">{{ $data['category']->name }}</h2>
            <div class="items-grid">
                @foreach($data['items'] as $item)
                    <div class="item-card">
                        @if($item->image_full_url)
                        <img src="{{ $item->image_full_url }}" alt="{{ $item->name }}" class="item-image">
                        @endif
                        <div class="item-content">
                            <div>
                                <h3 class="item-name">{{ $item->name }}</h3>
                                <p class="item-desc">{{ $item->description }}</p>
                            </div>
                            <div class="item-footer">
                                <span class="item-price">${{ number_format($item->display_price, 2) }}</span>
                                <button class="add-btn" onclick="checkItemDetails({{ $item->id }})">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
    @else
    <div class="empty-state">
        <i class="fas fa-utensils fa-3x" style="margin-bottom: 16px; opacity: 0.2;"></i>
        <p>No hay artículos disponibles en este momento.</p>
    </div>
    @endif

    <!-- Cart Floating Bar -->
    <div id="cartBar" class="cart-bar" onclick="openCheckout()">
        <div class="cart-info">
            <div id="cartCount" class="cart-count">0</div>
            <div id="cartTotal" class="cart-total">$0.00</div>
        </div>
        <div class="view-cart-btn">
            Ver Pedido
            <i class="fas fa-chevron-right"></i>
        </div>
    </div>

    <!-- Variations Modal -->
    <div id="variationModal" class="modal" onclick="closeVariation(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                <h2 id="vItemName" style="font-size: 20px; font-weight: 700;">Producto</h2>
                <button onclick="closeVariationModal()" style="border: none; background: none; font-size: 20px; color: #999;"><i class="fas fa-times"></i></button>
            </div>
            <p id="vItemDesc" style="font-size: 14px; color: var(--text-muted); margin-bottom: 24px;"></p>
            
            <div id="variationContent">
                <!-- Variations injected here -->
            </div>

            <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #EEE;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <span style="font-weight: 600;">Total del producto:</span>
                    <span id="vItemTotal" style="font-size: 20px; font-weight: 700; color: var(--primary);">$0.00</span>
                </div>
                <button onclick="addSelectedToCart()" class="checkout-btn">Agregar al pedido</button>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div id="checkoutModal" class="modal" onclick="closeCheckout(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <h2 class="modal-title">Finalizar Pedido</h2>
            
            <div class="input-group">
                <label>Tu Nombre</label>
                <input type="text" id="custName" placeholder="Ej. Juan Pérez">
            </div>

            <div class="input-group">
                <label>Tu Teléfono WhatsApp</label>
                <input type="tel" id="custPhone" placeholder="Ej. 9991234567">
            </div>

            <div class="input-group">
                <label>Tipo de Entrega</label>
                <div class="order-type-selector">
                    <div id="typeDelivery" class="order-type-option active" onclick="setOrderType('delivery')">A Domicilio</div>
                    <div id="typePickup" class="order-type-option" onclick="setOrderType('pickup')">Pasar a Recoger</div>
                </div>
            </div>

            <div id="deliveryFields">
                <div class="input-group">
                    <button type="button" class="location-btn w-100 mb-3" onclick="getLocation()" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 15px; border-radius: 12px; border: 2px dashed var(--primary); color: var(--primary); background: rgba(255, 111, 0, 0.05); cursor: pointer; font-weight: 600;">
                        <i class="fas fa-location-arrow"></i>
                        <span id="locText">Compartir mi ubicación GPS</span>
                    </button>
                </div>

                @if(isset($store->tootliclick_settings['colonias']) && count($store->tootliclick_settings['colonias']) > 0)
                <div class="input-group">
                    <label>Selecciona tu Colonia (Para costo de envío)</label>
                    <select id="selColonia" class="form-control" onchange="setColonia(this.value)" style="width: 100%; padding: 16px; border-radius: 12px; background: var(--bg-light); border: 1px solid #EEE;">
                        <option value="">Selecciona una colonia...</option>
                        @foreach($store->tootliclick_settings['colonias'] as $col)
                        <option value="{{ $col['name'] }}" data-price="{{ $col['price'] }}">{{ $col['name'] }} (${{ number_format($col['price'], 2) }})</option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div class="input-group">
                    <label>Dirección Detallada (Calle, Núm, Cruzamientos)</label>
                    <textarea id="custAddress" rows="2" placeholder="Ej: Calle 50 x 25 y 27, casa color azul..."></textarea>
                </div>
            </div>

            <div id="orderSummary" style="margin-bottom: 24px; padding: 16px; background: var(--bg-light); border-radius: 12px; font-size: 14px;">
                <!-- Summary injected here -->
            </div>

            <button class="whatsapp-btn" onclick="sendToWhatsApp()">
                <i class="fab fa-whatsapp"></i>
                Enviar Pedido por WhatsApp
            </button>
            <button style="width: 100%; background: none; border: none; color: var(--text-muted); margin-top: 16px; font-weight: 600;" onclick="closeCheckout()">Regresar al Menú</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        const allItems = {
            @foreach($items_by_category as $catId => $data)
                @foreach($data['items'] as $item)
                    "{{ $item->id }}": @json($item),
                @endforeach
            @endforeach
        };
        
        let cart = [];
        let orderType = 'delivery';
        let coordinates = null;
        let shippingFee = 0;
        let selectedColonia = '';

        // Store configuration for shipping
        const storeLat = {{ $store->latitude ?? 0 }};
        const storeLng = {{ $store->longitude ?? 0 }};
        const minShipping = {{ $store->minimum_shipping_charge ?? 0 }};
        const perKmCharge = {{ $store->per_km_shipping_charge ?? 0 }};
        const maxShipping = {{ $store->maximum_shipping_charge ?? 0 }};
        
        // Extended settings
        const tcSettings = @json($store->tootliclick_settings ?? []);
        const minFreeOrder = tcSettings.free_delivery_min_amount || 0;

        function setOrderType(type) {
            orderType = type;
            document.querySelectorAll('.order-type-option').forEach(opt => opt.classList.remove('active'));
            if (type === 'delivery') {
                document.getElementById('typeDelivery').classList.add('active');
                document.getElementById('deliveryFields').style.display = 'block';
            } else {
                document.getElementById('typeDelivery').classList.remove('active'); // Fixed potentially missing line
                document.getElementById('typePickup').classList.add('active');
                document.getElementById('deliveryFields').style.display = 'none';
                shippingFee = 0;
                selectedColonia = '';
            }
            updateSummary();
        }

        function setColonia(name) {
            selectedColonia = name;
            if (name) {
                const option = document.querySelector(`#selColonia option[value="${name}"]`);
                shippingFee = parseFloat(option.dataset.price);
            } else {
                shippingFee = 0;
            }
            calculateShipping(); // Still run for minFreeOrder logic
            updateSummary();
        }

        function getLocation() {
            if (navigator.geolocation) {
                const btn = document.getElementById('locText');
                const btnParent = btn.parentElement;
                btn.innerHTML = 'Obteniendo ubicación...';
                
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        coordinates = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        btn.innerHTML = 'Ubicación GPS compartida';
                        btnParent.style.borderColor = '#25D366';
                        btnParent.style.color = '#25D366';
                        btnParent.style.background = 'rgba(37, 211, 102, 0.05)';
                        
                        calculateShipping();
                        updateSummary();
                        toastr.success('Ubicación capturada correctamente');
                    },
                    (error) => {
                        btn.innerHTML = 'Compartir mi ubicación GPS';
                        toastr.error('No se pudo obtener la ubicación. Activa el GPS de tu celular.');
                    }
                );
            } else {
                alert('Tu navegador no soporta geolocalización.');
            }
        }

        function calculateShipping() {
            const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            
            // Si supera el mínimo de compra para envío gratis
            if (minFreeOrder > 0 && subtotal >= minFreeOrder) {
                shippingFee = 0;
                return;
            }

            if (orderType === 'delivery') {
                if (selectedColonia) {
                    // Ya está seteado en setColonia
                    return;
                }
                
                if (coordinates && storeLat && storeLng) {
                    const distance = getDistance(storeLat, storeLng, coordinates.lat, coordinates.lng);
                    let fee = minShipping + (distance * perKmCharge);
                    if (maxShipping > 0 && fee > maxShipping) fee = maxShipping;
                    shippingFee = fee;
                }
            } else {
                shippingFee = 0;
            }
        }

        function getDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        let currentItem = null;
        let selectedVariations = {};
        let selectedAddons = [];

        function checkItemDetails(itemId) {
            const item = allItems[itemId];
            if (!item) return;

            const hasFoodVars = item.food_variations && (typeof item.food_variations === 'string' ? JSON.parse(item.food_variations) : item.food_variations).length > 0;
            const hasAddons = item.addons && item.addons.length > 0;

            if (hasFoodVars || hasAddons) {
                openVariationModal(item);
            } else {
                addToCart(item.id, item.name, item.display_price);
            }
        }

        function openVariationModal(item) {
            currentItem = item;
            selectedVariations = {};
            selectedAddons = [];
            
            document.getElementById('vItemName').innerText = item.name;
            document.getElementById('vItemDesc').innerText = item.description || '';
            
            const content = document.getElementById('variationContent');
            content.innerHTML = '';

            // Food Variations (Food Module)
            if (item.food_variations) {
                const vars = typeof item.food_variations === 'string' ? JSON.parse(item.food_variations) : item.food_variations;
                vars.forEach((v, index) => {
                    const group = document.createElement('div');
                    group.className = 'variation-group';
                    
                    const title = document.createElement('div');
                    title.className = 'variation-title';
                    title.innerHTML = `<span>${v.name}</span> <span class="variation-badge">${v.required === 'on' ? 'OBLIGATORIO' : 'OPCIONAL'}</span>`;
                    group.appendChild(title);

                    v.values.forEach((opt, optIndex) => {
                        const option = document.createElement('div');
                        option.className = 'variation-option';
                        const inputId = `var_${index}_${optIndex}`;
                        const inputType = (v.type === 'single' || v.max === '1') ? 'radio' : 'checkbox';
                        const inputName = `var_${index}`;
                        
                        option.innerHTML = `
                            <input type="${inputType}" id="${inputId}" name="${inputName}" 
                                value="${opt.label}" 
                                data-price="${opt.optionPrice}"
                                onchange="updateVariationTotal()">
                            <label class="option-label" for="${inputId}">${opt.label}</label>
                            <span class="option-price">+ $${parseFloat(opt.optionPrice).toFixed(2)}</span>
                        `;
                        group.appendChild(option);
                    });
                    content.appendChild(group);
                });
            }

            // Addons
            if (item.addons && item.addons.length > 0) {
                const group = document.createElement('div');
                group.className = 'variation-group';
                
                const title = document.createElement('div');
                title.className = 'variation-title';
                title.innerHTML = `<span>Adicionales</span> <span class="variation-badge">OPCIONAL</span>`;
                group.appendChild(title);

                item.addons.forEach((addon, aIndex) => {
                    const option = document.createElement('div');
                    option.className = 'variation-option';
                    const inputId = `addon_${aIndex}`;
                    
                    option.innerHTML = `
                        <input type="checkbox" id="${inputId}" value="${addon.name}" 
                            data-price="${addon.price}"
                            onchange="updateVariationTotal()">
                        <label class="option-label" for="${inputId}">${addon.name}</label>
                        <span class="option-price">+ $${parseFloat(addon.price).toFixed(2)}</span>
                    `;
                    group.appendChild(option);
                });
                content.appendChild(group);
            }

            document.getElementById('variationModal').style.display = 'flex';
            updateVariationTotal();
        }

        function closeVariationModal() {
            document.getElementById('variationModal').style.display = 'none';
        }

        function closeVariation(e) {
            if (e.target.id === 'variationModal') closeVariationModal();
        }

        function updateVariationTotal() {
            if (!currentItem) return;
            let total = parseFloat(currentItem.display_price);
            
            // Variaciones de comida
            const inputs = document.querySelectorAll('#variationContent input:checked');
            inputs.forEach(input => {
                total += parseFloat(input.dataset.price || 0);
            });

            document.getElementById('vItemTotal').innerText = `$${total.toFixed(2)}`;
        }

        function addSelectedToCart() {
            if (!currentItem) return;

            // Validar requeridos
            if (currentItem.food_variations) {
                const vars = JSON.parse(currentItem.food_variations);
                for (let i = 0; i < vars.length; i++) {
                    if (vars[i].required === 'on') {
                        const checked = document.querySelectorAll(`input[name="var_${i}"]:checked`);
                        if (checked.length === 0) {
                            alert(`Por favor selecciona una opción para: ${vars[i].name}`);
                            return;
                        }
                    }
                }
            }

            const selections = [];
            let extraPrice = 0;

            const inputs = document.querySelectorAll('#variationContent input:checked');
            inputs.forEach(input => {
                selections.push(input.value);
                extraPrice += parseFloat(input.dataset.price || 0);
            });

            const finalPrice = parseFloat(currentItem.display_price) + extraPrice;
            const displayName = currentItem.name + (selections.length > 0 ? ` (${selections.join(', ')})` : '');

            // Añadir al carrito con nombre descriptivo
            const cartItem = {
                id: currentItem.id,
                name: displayName,
                price: finalPrice,
                qty: 1,
                original_name: currentItem.name,
                selections: selections
            };

            // Para TootliClick, si tiene selecciones diferentes, lo tratamos como item separado
            const key = cartItem.name;
            const index = cart.findIndex(item => item.name === key);
            
            if (index > -1) {
                cart[index].qty++;
            } else {
                cart.push(cartItem);
            }

            updateCartUI();
            closeVariationModal();
            toastr.success('Agregado al pedido');
        }

        function addToCart(id, name, price) {
            const index = cart.findIndex(i => i.id === id);
            if (index > -1) {
                cart[index].qty += 1;
            } else {
                cart.push({ id, name, price, qty: 1 });
            }
            updateUI();
            
            // Subtle animation
            const bar = document.getElementById('cartBar');
            bar.style.transform = 'scale(1.05)';
            setTimeout(() => bar.style.transform = 'scale(1)', 100);
        }

        function updateUI() {
            const totalQty = cart.reduce((acc, item) => acc + item.qty, 0);
            const totalPrice = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            
            document.getElementById('cartCount').innerText = totalQty;
            document.getElementById('cartTotal').innerText = '$' + totalPrice.toFixed(2);
            
            const bar = document.getElementById('cartBar');
            if (totalQty > 0) {
                bar.classList.add('visible');
            } else {
                bar.classList.remove('visible');
            }
        }

        function setActiveCategory(el) {
            document.querySelectorAll('.category-chip').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
        }

        function openCheckout() {
            updateSummary();
            document.getElementById('checkoutModal').classList.add('open');
        }

        function updateSummary() {
            const summaryDiv = document.getElementById('orderSummary');
            let html = '<div style="background: var(--bg-light); padding: 15px; border-radius: 12px; margin-bottom: 15px;">';
            html += '<strong>Tu Pedido:</strong><br>';
            cart.forEach(item => {
                html += `<div style="display: flex; justify-content: space-between; margin-top: 5px;">
                            <span>${item.qty}x ${item.name}</span>
                            <span>$${(item.price * item.qty).toFixed(2)}</span>
                         </div>`;
            });
            html += '</div>';
            
            const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            
            html += `<div style="padding: 0 5px;">`;
            if (orderType === 'delivery') {
                html += `<div style="display: flex; justify-content: space-between;"><span>Subtotal:</span><span>$${subtotal.toFixed(2)}</span></div>`;
                html += `<div style="display: flex; justify-content: space-between; color: var(--primary); font-weight: 600;">
                            <span>Envío:</span>
                            <span>${shippingFee > 0 ? '$' + shippingFee.toFixed(2) : (minFreeOrder > 0 && subtotal >= minFreeOrder ? 'GRATIS' : '$0.00')}</span>
                         </div>`;
            }

            const total = subtotal + shippingFee;
            html += `<div style="display: flex; justify-content: space-between; margin-top: 10px; padding-top: 10px; border-top: 2px solid #EEE; font-size: 18px; font-weight: 800;">
                        <span>Total:</span>
                        <span>$${total.toFixed(2)}</span>
                     </div>`;
            html += `</div>`;
            summaryDiv.innerHTML = html;
        }

        function closeCheckout(event) {
            if (!event || event.target.id === 'checkoutModal' || event.target.tagName === 'BUTTON') {
                document.getElementById('checkoutModal').classList.remove('open');
            }
        }

        function sendToWhatsApp() {
            const name = document.getElementById('custName').value;
            const phoneCust = document.getElementById('custPhone').value;
            const address = document.getElementById('custAddress').value;
            
            if (!name || !phoneCust) {
                alert('Por favor, ingresa tu nombre y teléfono.');
                return;
            }

            if (orderType === 'delivery' && !address && !coordinates) {
                alert('Por favor, ingresa tu dirección o comparte tu ubicación.');
                return;
            }

            let message = `*Nuevo Pedido desde TootliClick*%0A%0A`;
            message += `*Cliente:* ${name}%0A`;
            message += `*Teléfono:* ${phoneCust}%0A`;
            message += `*Tipo:* ${orderType === 'delivery' ? 'A Domicilio' : 'Pasar a Recoger'}%0A`;
            
            if (orderType === 'delivery') {
                if (selectedColonia) message += `*Colonia:* ${selectedColonia}%0A`;
                if (address) message += `*Dirección:* ${address}%0A`;
                if (coordinates) {
                    message += `*Ubicación GPS:* https://www.google.com/maps?q=${coordinates.lat},${coordinates.lng}%0A`;
                }
                if (shippingFee > 0) {
                    message += `*Costo de Envío:* $${shippingFee.toFixed(2)}%0A`;
                } else if (minFreeOrder > 0) {
                    const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
                    if (subtotal >= minFreeOrder) {
                        message += `*Envío:* GRATIS (Compra > $${minFreeOrder})%0A`;
                    }
                }
            }

            message += `%0A*Detalle del Pedido:*%0A`;
            
            cart.forEach(item => {
                message += `• ${item.qty}x ${item.name} ($${(item.price * item.qty).toFixed(2)})%0A`;
            });
            
            const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            const totalPrice = subtotal + shippingFee;
            message += `%0A*Total: $${totalPrice.toFixed(2)}*%0A`;
            
            // Link for Restaurant Admin (Tootli Directo)
            const adminBaseUrl = '{{ route("vendor.pos.tc-init") }}';
            const adminParams = `?tc_name=${encodeURIComponent(name)}&tc_phone=${encodeURIComponent(phoneCust)}&tc_address=${encodeURIComponent(address)}&tc_lat=${coordinates?coordinates.lat:''}&tc_lng=${coordinates?coordinates.lng:''}`;
            
            message += `%0A--------------------------%0A`;
            message += `*SOLO RESTAURANTE (Tootli Directo):*%0A`;
            message += `${adminBaseUrl}${adminParams}%0A`;
            message += `--------------------------%0A`;
            
            message += `%0A_Pedido generado desde tootli.mx_`;

            const storePhone = '{{ $store->phone }}'.replace(/\D/g, '');
            const whatsappUrl = `https://wa.me/${storePhone}?text=${message}`;
            
            window.open(whatsappUrl, '_blank');
        }

        // Auto-detect scroll for categories
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('.menu-section');
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 120) {
                    current = section.getAttribute('id');
                }
            });

            document.querySelectorAll('.category-chip').forEach(chip => {
                chip.classList.remove('active');
                if (chip.getAttribute('href') === `#${current}`) {
                    chip.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
