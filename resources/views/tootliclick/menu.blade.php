<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $store->name }} | Menú Digital TootliClick</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            border-radius: 24px 24px 0 0;
            padding: 32px 24px;
            animation: slideUp 0.3s ease-out;
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
                                <button class="add-btn" onclick="addToCart({{ $item->id }}, '{{ $item->name }}', {{ $item->display_price }})">
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

    <!-- Checkout Modal -->
    <div id="checkoutModal" class="modal" onclick="closeCheckout(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <h2 class="modal-title">Finalizar Pedido</h2>
            
            <div class="input-group">
                <label>Tu Nombre</label>
                <input type="text" id="custName" placeholder="Ej. Juan Pérez">
            </div>

            <div class="input-group">
                <label>Dirección (Opcional si es para recoger)</label>
                <textarea id="custAddress" rows="2" placeholder="Calle, número y colonia..."></textarea>
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

    <script>
        let cart = [];

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
            const summaryDiv = document.getElementById('orderSummary');
            let html = '<strong>Tu Pedido:</strong><br>';
            cart.forEach(item => {
                html += `${item.qty}x ${item.name} - $${(item.price * item.qty).toFixed(2)}<br>`;
            });
            const totalPrice = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            html += `<br><strong>Total: $${totalPrice.toFixed(2)}</strong>`;
            summaryDiv.innerHTML = html;
            
            document.getElementById('checkoutModal').classList.add('open');
        }

        function closeCheckout(event) {
            if (!event || event.target.id === 'checkoutModal' || event.target.tagName === 'BUTTON') {
                document.getElementById('checkoutModal').classList.remove('open');
            }
        }

        function sendToWhatsApp() {
            const name = document.getElementById('custName').value;
            const address = document.getElementById('custAddress').value;
            
            if (!name) {
                alert('Por favor, ingresa tu nombre.');
                return;
            }

            let message = `*Nuevo Pedido desde TootliClick*%0A%0A`;
            message += `*Cliente:* ${name}%0A`;
            if (address) message += `*Dirección:* ${address}%0A`;
            message += `%0A*Detalle del Pedido:*%0A`;
            
            cart.forEach(item => {
                message += `• ${item.qty}x ${item.name} ($${(item.price * item.qty).toFixed(2)})%0A`;
            });
            
            const totalPrice = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            message += `%0A*Total: $${totalPrice.toFixed(2)}*%0A`;
            message += `%0A_Pedido generado desde tootli.mx_`;

            const phone = '{{ $store->phone }}'.replace(/\D/g, '');
            const whatsappUrl = `https://wa.me/${phone}?text=${message}`;
            
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
