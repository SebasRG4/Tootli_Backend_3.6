@extends('layouts.vendor.app')

@section('title', 'POS Distribuidora — ' . $store->name)

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ── Variables ──────────────────────────────────────────────────── */
        :root {
            --dp-primary:   #2563EB;
            --dp-accent:    #10B981;
            --dp-warn:      #F59E0B;
            --dp-danger:    #EF4444;
            --dp-bg:        #F1F5F9;
            --dp-surface:   #FFFFFF;
            --dp-border:    #E2E8F0;
            --dp-text:      #1E293B;
            --dp-muted:     #64748B;
            --dp-radius:    12px;
            --dp-shadow:    0 2px 12px rgba(0,0,0,.08);
        }
        body { font-family: 'Inter', sans-serif; background: var(--dp-bg); }

        /* ── Layout ─────────────────────────────────────────────────────── */
        .dp-shell { display: flex; height: calc(100vh - 64px); overflow: hidden; gap: 0; }
        .dp-left  { flex: 1 1 0; min-width: 0; display: flex; flex-direction: column; padding: 16px; gap: 12px; overflow-y: auto; }
        .dp-right { width: 380px; flex-shrink: 0; display: flex; flex-direction: column; background: var(--dp-surface); border-left: 1px solid var(--dp-border); overflow-y: auto; }

        /* ── Top bar ────────────────────────────────────────────────────── */
        .dp-topbar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .dp-topbar h1 { font-size: 1.1rem; font-weight: 700; color: var(--dp-text); margin: 0; }
        .dp-topbar .dp-badge { background: linear-gradient(135deg,#2563EB,#7C3AED); color:#fff; font-size:.75rem; padding:3px 10px; border-radius:20px; font-weight:600; }
        .dp-topbar-actions { margin-left: auto; display: flex; gap: 8px; }

        /* ── Search & categories ──────────────────────────────────────────── */
        .dp-search { position: relative; }
        .dp-search input { width: 100%; border: 1.5px solid var(--dp-border); border-radius: 8px; padding: 8px 16px 8px 40px; font-size:.9rem; outline:none; transition: border .2s; }
        .dp-search input:focus { border-color: var(--dp-primary); }
        .dp-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--dp-muted); }

        .dp-cats { display: flex; gap: 8px; flex-wrap: wrap; }
        .dp-cat-pill { background: var(--dp-surface); border: 1.5px solid var(--dp-border); border-radius: 20px; padding: 4px 14px; font-size:.82rem; cursor:pointer; transition:.15s; color:var(--dp-text); }
        .dp-cat-pill:hover, .dp-cat-pill.active { background: var(--dp-primary); color:#fff; border-color:var(--dp-primary); }

        /* ── Product grid ────────────────────────────────────────────────── */
        .dp-products { display: grid; grid-template-columns: repeat(auto-fill, minmax(155px, 1fr)); gap: 12px; }
        .dp-product-card { background: var(--dp-surface); border-radius: var(--dp-radius); border: 1.5px solid var(--dp-border); cursor: pointer; transition: .18s; overflow:hidden; }
        .dp-product-card:hover { border-color:var(--dp-primary); box-shadow: 0 4px 16px rgba(37,99,235,.15); transform:translateY(-2px); }
        .dp-product-card img { width:100%; height:110px; object-fit:cover; }
        .dp-product-card .dp-pc-body { padding: 8px 10px; }
        .dp-product-card .dp-pc-name { font-size:.82rem; font-weight:600; color:var(--dp-text); line-height:1.3; margin-bottom:4px; }
        .dp-product-card .dp-pc-price { font-size:.88rem; font-weight:700; color:var(--dp-primary); }
        .dp-product-card .dp-pc-discount { font-size:.75rem; color:var(--dp-muted); text-decoration:line-through; }
        .dp-product-card .dp-add-btn { display:flex; align-items:center; justify-content:center; background:var(--dp-primary); color:#fff; border-radius:0 0 var(--dp-radius) var(--dp-radius); padding:6px; font-size:.82rem; font-weight:600; gap:4px; }

        /* ── Right panel: customer lookup ────────────────────────────────── */
        .dp-section { padding: 16px; border-bottom: 1px solid var(--dp-border); }
        .dp-section-title { font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--dp-muted); margin-bottom:10px; }

        .dp-phone-row { display:flex; gap:8px; }
        .dp-phone-row input { flex:1; border:1.5px solid var(--dp-border); border-radius:8px; padding:8px 12px; font-size:.9rem; outline:none; }
        .dp-phone-row input:focus { border-color:var(--dp-primary); }
        .dp-phone-row button { background:var(--dp-primary); color:#fff; border:none; border-radius:8px; padding:8px 14px; font-size:.82rem; font-weight:600; cursor:pointer; }

        .dp-customer-card { background: linear-gradient(135deg, #EFF6FF, #DBEAFE); border:1.5px solid #BFDBFE; border-radius:10px; padding:12px; margin-top:10px; display:none; }
        .dp-customer-card.visible { display:block; }
        .dp-customer-name { font-weight:700; color:var(--dp-text); font-size:.95rem; }
        .dp-customer-phone { font-size:.82rem; color:var(--dp-muted); }
        .dp-points-badge { display:inline-flex; align-items:center; gap:4px; background:var(--dp-accent); color:#fff; border-radius:20px; padding:3px 12px; font-size:.8rem; font-weight:700; margin-top:6px; }

        .dp-register-box { background:#FFF7ED; border:1.5px solid #FED7AA; border-radius:10px; padding:12px; margin-top:10px; display:none; }
        .dp-register-box.visible { display:block; }
        .dp-register-box input { width:100%; border:1.5px solid var(--dp-border); border-radius:8px; padding:7px 10px; font-size:.85rem; margin-bottom:8px; outline:none; }
        .dp-register-box button { width:100%; background:var(--dp-accent); color:#fff; border:none; border-radius:8px; padding:8px; font-size:.85rem; font-weight:600; cursor:pointer; }

        /* ── Cart ────────────────────────────────────────────────────────── */
        .dp-cart-list { flex:1; overflow-y:auto; }
        .dp-cart-item { display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid var(--dp-border); }
        .dp-cart-item img { width:42px; height:42px; object-fit:cover; border-radius:6px; flex-shrink:0; }
        .dp-ci-info { flex:1; min-width:0; }
        .dp-ci-name { font-size:.82rem; font-weight:600; color:var(--dp-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .dp-ci-price { font-size:.8rem; color:var(--dp-muted); }
        .dp-ci-qty { display:flex; align-items:center; gap:6px; }
        .dp-ci-qty button { width:24px; height:24px; border-radius:6px; border:1.5px solid var(--dp-border); background:var(--dp-bg); font-weight:700; cursor:pointer; font-size:.9rem; }
        .dp-ci-qty input { width:36px; text-align:center; border:1.5px solid var(--dp-border); border-radius:6px; font-size:.85rem; padding:2px 0; }
        .dp-ci-remove { background:none; border:none; color:var(--dp-danger); cursor:pointer; font-size:.9rem; }

        /* ── Totals & payment ────────────────────────────────────────────── */
        .dp-totals { padding:12px 16px; border-top: 1px solid var(--dp-border); }
        .dp-totals dl { display:flex; flex-direction:column; gap:6px; }
        .dp-totals dt { display:flex; justify-content:space-between; font-size:.85rem; color:var(--dp-muted); }
        .dp-totals dt strong { color:var(--dp-text); font-size:.95rem; font-weight:700; }
        .dp-totals .dp-total-row { display:flex; justify-content:space-between; align-items:center; padding-top:8px; border-top:1.5px solid var(--dp-border); margin-top:4px; }

        .dp-payment-methods { padding:12px 16px; }
        .dp-payment-methods label { display:flex; align-items:center; gap:8px; padding:8px 10px; border:1.5px solid var(--dp-border); border-radius:8px; cursor:pointer; margin-bottom:6px; font-size:.85rem; font-weight:500; }
        .dp-payment-methods input[type=radio]:checked + span { color:var(--dp-primary); }
        .dp-payment-methods label:has(input:checked) { border-color:var(--dp-primary); background:#EFF6FF; }

        .dp-points-redeem { padding:0 16px 12px; }
        .dp-points-redeem input[type=range] { width:100%; accent-color:var(--dp-accent); }
        .dp-points-earned-badge { background:#ECFDF5; border:1px solid #A7F3D0; border-radius:8px; padding:8px 12px; font-size:.82rem; color:#065F46; font-weight:600; margin:0 16px 8px; display:none; }
        .dp-points-earned-badge.visible { display:block; }

        .dp-checkout-btn { margin:8px 16px 16px; background:linear-gradient(135deg,#2563EB,#7C3AED); color:#fff; border:none; border-radius:10px; padding:14px; font-size:1rem; font-weight:700; cursor:pointer; width:calc(100% - 32px); transition:.2s; }
        .dp-checkout-btn:hover { opacity:.9; transform:translateY(-1px); box-shadow:0 6px 20px rgba(37,99,235,.3); }
        .dp-checkout-btn:disabled { opacity:.5; cursor:not-allowed; transform:none; }

        .dp-clear-btn { margin:0 16px 8px; border:1.5px solid var(--dp-border); background:transparent; border-radius:10px; padding:10px; font-size:.85rem; font-weight:600; cursor:pointer; width:calc(100% - 32px); color:var(--dp-muted); }

        /* ── Success receipt modal ────────────────────────────────────────── */
        .dp-receipt { text-align:center; padding:24px; }
        .dp-receipt .dp-receipt-icon { font-size:3rem; margin-bottom:12px; }
        .dp-receipt .dp-receipt-folio { font-size:1.1rem; font-weight:700; color:var(--dp-text); }
        .dp-receipt .dp-receipt-total { font-size:2rem; font-weight:800; color:var(--dp-primary); margin:8px 0; }
        .dp-receipt .dp-receipt-change { background:#F0FDF4; border-radius:8px; padding:8px 16px; font-size:.9rem; color:#166534; font-weight:600; margin:8px 0; display:none; }
        .dp-receipt .dp-receipt-points { background:#ECFDF5; border-radius:8px; padding:8px 16px; font-size:.9rem; color:#065F46; font-weight:600; margin:8px 0; display:none; }

        /* ── Pagination & quick tweaks ────────────────────────────────────── */
        .dp-pagination { padding:8px 0; text-align:center; }
        .dp-pagination .pagination { justify-content:center; }
        @media(max-width:900px){
            .dp-shell { flex-direction:column; height:auto; }
            .dp-right { width:100%; border-left:none; border-top:1px solid var(--dp-border); }
        }
    </style>
@endpush

@section('content')
@php
    $currSymbol = \App\CentralLogics\Helpers::currency_symbol();
    $formatCur  = fn($v) => \App\CentralLogics\Helpers::format_currency($v);
@endphp

<section class="section-content">
    <div class="dp-shell">

        {{-- ────────────────── LEFT: productos ──────────────────── --}}
        <div class="dp-left">

            {{-- Topbar --}}
            <div class="dp-topbar">
                <div>
                    <h1>📦 {{ $store->name }}</h1>
                    <span style="font-size:.8rem; color:var(--dp-muted)">{{ now()->translatedFormat('l j \d\e F, Y') }}</span>
                </div>
                <span class="dp-badge">POS Distribuidora</span>
                <div class="dp-topbar-actions">
                    <a href="{{ route('vendor.distributor-pos.sales') }}" class="btn btn-outline-primary btn-sm">
                        <i class="tio-receipt"></i> Ventas
                    </a>
                    <a href="{{ route('vendor.distributor-pos.settings') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="tio-settings"></i> Config
                    </a>
                </div>
            </div>

            {{-- Búsqueda --}}
            <form id="dp-search-form" autocomplete="off">
                <div class="dp-search">
                    <span class="dp-search-icon"><i class="tio-search"></i></span>
                    <input id="dp-search-input" type="search" name="keyword" value="{{ $keyword }}"
                           placeholder="Buscar producto o escanear código...">
                </div>
            </form>

            {{-- Categorías --}}
            <div class="dp-cats">
                <button class="dp-cat-pill {{ $category === 0 ? 'active' : '' }}" data-cat="">Todos</button>
                @foreach($categories as $cat)
                    <button class="dp-cat-pill {{ $category === $cat->id ? 'active' : '' }}"
                            data-cat="{{ $cat->id }}">{{ Str::limit($cat->name, 22) }}</button>
                @endforeach
            </div>

            {{-- Grid de productos --}}
            <div id="dp-products-container">
                @include('vendor-views.distributor-pos._products_grid', ['products' => $products])
            </div>
        </div>

        {{-- ────────────────── RIGHT: carrito + cobro ──────────── --}}
        <div class="dp-right">

            {{-- Cliente --}}
            <div class="dp-section">
                <div class="dp-section-title">🙋 Cliente (opcional)</div>

                <div class="dp-phone-row">
                    <input id="dp-phone" type="tel" placeholder="Número de teléfono" inputmode="tel">
                    <button id="dp-phone-search-btn" type="button">Buscar</button>
                </div>

                {{-- Cliente encontrado --}}
                <div id="dp-customer-found" class="dp-customer-card">
                    <div class="dp-customer-name" id="dp-cust-name"></div>
                    <div class="dp-customer-phone" id="dp-cust-phone"></div>
                    <div class="dp-points-badge">
                        <i class="tio-star"></i>
                        <span id="dp-cust-points">0</span> pts
                    </div>
                    <input type="hidden" id="dp-cust-id">
                    <input type="hidden" id="dp-cust-max-redeem" value="0">
                    <input type="hidden" id="dp-cust-point-value" value="1">
                    <button class="btn btn-xs btn-outline-secondary mt-2" id="dp-remove-customer">
                        <i class="tio-clear"></i> Quitar
                    </button>
                </div>

                {{-- No encontrado → registrar --}}
                <div id="dp-register-box" class="dp-register-box">
                    <p style="font-size:.82rem; color:#92400E; margin-bottom:8px;">
                        📵 Cliente no registrado con este número.
                    </p>
                    <input id="dp-new-name" type="text" placeholder="Nombre del cliente">
                    <button id="dp-register-btn" type="button">✅ Registrar y seleccionar</button>
                </div>
            </div>

            {{-- Carrito --}}
            <div class="dp-cart-list" id="dp-cart-container">
                @include('vendor-views.distributor-pos._cart', ['config' => $config])
            </div>

            {{-- Puntos a canjear (solo si hay cliente) --}}
            <div class="dp-points-redeem" id="dp-points-redeem-section" style="display:none;">
                <div class="dp-section-title" style="margin-bottom:6px;">💎 Canjear puntos</div>
                <input type="range" id="dp-redeem-slider" min="0" max="0" step="0.5" value="0">
                <div style="font-size:.8rem; color:var(--dp-muted); text-align:center;">
                    <span id="dp-redeem-pts-label">0</span> pts =
                    <strong id="dp-redeem-pesos-label" style="color:var(--dp-accent)">$0.00</strong>
                </div>
            </div>

            {{-- Badge puntos a ganar --}}
            <div id="dp-points-earned-badge" class="dp-points-earned-badge">
                🌟 El cliente ganará <strong id="dp-pts-earn-count">0</strong> pts en esta compra
            </div>

            {{-- Método de pago --}}
            <div class="dp-payment-methods">
                <div class="dp-section-title" style="margin-bottom:8px;">💳 Método de pago</div>
                <label><input type="radio" name="dp-payment" value="cash" checked> <span>💵 Efectivo</span></label>
                <label><input type="radio" name="dp-payment" value="card"> <span>💳 Tarjeta</span></label>
                <label><input type="radio" name="dp-payment" value="transfer"> <span>🏦 Transferencia</span></label>
                {{-- Campo efectivo recibido --}}
                <div id="dp-cash-field" style="margin-top:8px;">
                    <input id="dp-cash-received" type="number" step="0.01" min="0"
                           class="form-control form-control-sm" placeholder="Efectivo recibido ($)">
                </div>
            </div>

            {{-- Botones --}}
            <button class="dp-checkout-btn" id="dp-place-sale-btn" disabled>
                💰 Cobrar — <span id="dp-btn-total">$0.00</span>
            </button>
            <button class="dp-clear-btn" id="dp-clear-cart-btn">🗑 Limpiar carrito</button>
        </div>
    </div>
</section>

{{-- Modal receipt --}}
<div class="modal fade" id="dp-receipt-modal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-xl">
            <div class="modal-body p-0">
                <div class="dp-receipt">
                    <div class="dp-receipt-icon">✅</div>
                    <div class="dp-receipt-folio" id="rcp-folio">DST-000-00001</div>
                    <div class="dp-receipt-total" id="rcp-total">$0.00</div>
                    <div id="rcp-change" class="dp-receipt-change">💵 Cambio: <strong id="rcp-change-val">$0.00</strong></div>
                    <div id="rcp-points" class="dp-receipt-points">🌟 Puntos ganados: <strong id="rcp-points-val">0</strong></div>
                    <button class="btn btn--primary btn-block mt-3" data-dismiss="modal">Nueva venta</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script')
<script>
const ROUTES = {
    products:         '{{ route("vendor.distributor-pos.products-grid") }}',
    addToCart:        '{{ route("vendor.distributor-pos.add-to-cart") }}',
    updateQty:        '{{ route("vendor.distributor-pos.update-quantity") }}',
    removeItem:       '{{ route("vendor.distributor-pos.remove-from-cart") }}',
    emptyCart:        '{{ route("vendor.distributor-pos.empty-cart") }}',
    cartItems:        '{{ route("vendor.distributor-pos.cart-items") }}',
    discount:         '{{ route("vendor.distributor-pos.discount") }}',
    lookupCustomer:   '{{ route("vendor.distributor-pos.lookup-customer") }}',
    registerCustomer: '{{ route("vendor.distributor-pos.register-customer") }}',
    checkoutSummary:  '{{ route("vendor.distributor-pos.checkout-summary") }}',
    placeSale:        '{{ route("vendor.distributor-pos.place-sale") }}',
};
const CSRF  = document.querySelector('meta[name="csrf-token"]').content;
const $     = id => document.getElementById(id);
const qsa   = sel => document.querySelectorAll(sel);
const fmt   = v => parseFloat(v||0).toLocaleString('es-MX', {style:'currency',currency:'MXN'});

// ── Estado ────────────────────────────────────────────────────────────────
let currentCatId = '';
let currentKeyword = '';
let cartCount = 0;
let pointsToRedeem = 0;

// ── AJAX helpers ──────────────────────────────────────────────────────────
async function post(url, body = {}) {
    const r = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(body),
    });
    return r.json();
}
async function get(url) {
    const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
    return r.json();
}

// ── Cargar productos (grid via AJAX) ─────────────────────────────────────
async function loadProducts(page = 1) {
    const url = new URL(ROUTES.products, location.origin);
    if (currentCatId)    url.searchParams.set('category_id', currentCatId);
    if (currentKeyword)  url.searchParams.set('keyword', currentKeyword);
    url.searchParams.set('page', page);
    const data = await get(url.toString());
    if (data.success) $('dp-products-container').innerHTML = data.html;
}

// Categoría
document.querySelectorAll('.dp-cat-pill').forEach(btn => {
    btn.addEventListener('click', () => {
        qsa('.dp-cat-pill').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentCatId = btn.dataset.cat || '';
        loadProducts();
    });
});

// Búsqueda
let searchTimer;
$('dp-search-input').addEventListener('input', e => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentKeyword = e.target.value.trim();
        loadProducts();
    }, 350);
});

// ── Añadir al carrito ────────────────────────────────────────────────────
document.addEventListener('click', async e => {
    const btn = e.target.closest('[data-dp-add]');
    if (!btn) return;
    btn.disabled = true;
    btn.innerHTML = '⏳';
    const data = await post(ROUTES.addToCart, {
        id: btn.dataset.dpAdd,
        quantity: 1,
    });
    btn.disabled = false;
    btn.innerHTML = '＋';
    if (data.success) await refreshCart();
});

// ── Carrito ───────────────────────────────────────────────────────────────
async function refreshCart() {
    const data = await post(ROUTES.cartItems);
    if (data.success) {
        $('dp-cart-container').innerHTML = data.html;
        bindCartEvents();
        await refreshSummary();
    }
}

function bindCartEvents() {
    // Cantidad
    document.querySelectorAll('[data-dp-qty-key]').forEach(input => {
        input.addEventListener('change', async () => {
            await post(ROUTES.updateQty, { key: input.dataset.dpQtyKey, quantity: input.value });
            await refreshCart();
        });
    });
    document.querySelectorAll('[data-dp-qty-plus]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const inp = btn.closest('.dp-ci-qty').querySelector('input');
            inp.value = parseInt(inp.value) + 1;
            await post(ROUTES.updateQty, { key: btn.dataset.dpQtyPlus, quantity: inp.value });
            await refreshCart();
        });
    });
    document.querySelectorAll('[data-dp-qty-minus]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const inp = btn.closest('.dp-ci-qty').querySelector('input');
            const newVal = Math.max(1, parseInt(inp.value) - 1);
            inp.value = newVal;
            await post(ROUTES.updateQty, { key: btn.dataset.dpQtyMinus, quantity: newVal });
            await refreshCart();
        });
    });
    // Eliminar
    document.querySelectorAll('[data-dp-remove]').forEach(btn => {
        btn.addEventListener('click', async () => {
            await post(ROUTES.removeItem, { key: btn.dataset.dpRemove });
            await refreshCart();
        });
    });
}

// Vaciar
$('dp-clear-cart-btn').addEventListener('click', async () => {
    if (!confirm('¿Vaciar el carrito?')) return;
    await post(ROUTES.emptyCart);
    await refreshCart();
    clearCustomer();
});

// ── Resumen de cobro ──────────────────────────────────────────────────────
async function refreshSummary() {
    const custId  = $('dp-cust-id') ? $('dp-cust-id').value : '';
    const maxRed  = parseFloat($('dp-cust-max-redeem')?.value || '0');
    const ptVal   = parseFloat($('dp-cust-point-value')?.value || '1');

    const data = await post(ROUTES.checkoutSummary, {
        customer_id:      custId,
        points_to_redeem: pointsToRedeem,
    });

    // Actualizar slider
    const section = $('dp-points-redeem-section');
    if (custId && maxRed > 0) {
        section.style.display = 'block';
        const slider = $('dp-redeem-slider');
        slider.max   = maxRed;
        slider.value = Math.min(pointsToRedeem, maxRed);
        updateRedeemUI(slider.value, ptVal);
    } else {
        section.style.display = 'none';
    }

    // Puntos ganados badge
    const badge = $('dp-points-earned-badge');
    if (data.points_earned > 0 && custId) {
        $('dp-pts-earn-count').textContent = data.points_earned;
        badge.classList.add('visible');
    } else {
        badge.classList.remove('visible');
    }

    // Botón
    const total = data.total_after_points || 0;
    $('dp-btn-total').textContent = fmt(total);
    $('dp-place-sale-btn').disabled = (total <= 0 && data.subtotal <= 0);
}

function updateRedeemUI(pts, ptVal) {
    $('dp-redeem-pts-label').textContent = parseFloat(pts).toFixed(2);
    $('dp-redeem-pesos-label').textContent = fmt(pts * ptVal);
}

// Slider
document.addEventListener('input', e => {
    if (e.target.id !== 'dp-redeem-slider') return;
    const ptVal = parseFloat($('dp-cust-point-value')?.value || '1');
    pointsToRedeem = parseFloat(e.target.value);
    updateRedeemUI(pointsToRedeem, ptVal);
    refreshSummary();
});

// Método de pago → mostrar/ocultar campo efectivo
document.addEventListener('change', e => {
    if (e.target.name !== 'dp-payment') return;
    $('dp-cash-field').style.display = e.target.value === 'cash' ? 'block' : 'none';
});

// ── Buscar cliente ────────────────────────────────────────────────────────
$('dp-phone-search-btn').addEventListener('click', lookupCustomer);
$('dp-phone').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); lookupCustomer(); } });

async function lookupCustomer() {
    const phone = $('dp-phone').value.trim();
    const data  = await post(ROUTES.lookupCustomer, { phone });

    if (data.anonymous) { clearCustomer(); return; }

    $('dp-register-box').classList.remove('visible');

    if (data.found) {
        showCustomer(data);
    } else {
        // No encontrado → mostrar registro
        $('dp-register-box').classList.add('visible');
    }
}

function showCustomer(data) {
    $('dp-cust-id').value           = data.id;
    $('dp-cust-max-redeem').value   = data.max_redeem || 0;
    $('dp-cust-point-value').value  = data.point_value || 1;
    $('dp-cust-name').textContent   = data.name;
    $('dp-cust-phone').textContent  = data.phone;
    $('dp-cust-points').textContent = (data.points_balance || 0).toFixed(2);
    $('dp-customer-found').classList.add('visible');
    $('dp-register-box').classList.remove('visible');
    pointsToRedeem = 0;
    refreshSummary();
}

function clearCustomer() {
    $('dp-cust-id').value          = '';
    $('dp-cust-max-redeem').value  = '0';
    $('dp-cust-point-value').value = '1';
    $('dp-customer-found').classList.remove('visible');
    $('dp-register-box').classList.remove('visible');
    $('dp-phone').value = '';
    pointsToRedeem = 0;
    $('dp-points-redeem-section').style.display = 'none';
    $('dp-points-earned-badge').classList.remove('visible');
    refreshSummary();
}

$('dp-remove-customer').addEventListener('click', clearCustomer);

// Registrar nuevo cliente
$('dp-register-btn').addEventListener('click', async () => {
    const phone = $('dp-phone').value.trim();
    const name  = $('dp-new-name').value.trim();
    if (!name) { alert('Escribe el nombre del cliente.'); return; }
    const data = await post(ROUTES.registerCustomer, { phone, name });
    if (data.success) {
        $('dp-register-box').classList.remove('visible');
        showCustomer(data);
    } else {
        alert(data.errors ? Object.values(data.errors).flat().join('\n') : 'Error al registrar.');
    }
});

// ── Procesar venta ────────────────────────────────────────────────────────
$('dp-place-sale-btn').addEventListener('click', async () => {
    const paymentMethod = document.querySelector('input[name="dp-payment"]:checked')?.value || 'cash';
    const cashReceived  = parseFloat($('dp-cash-received')?.value || '0');

    $('dp-place-sale-btn').disabled = true;
    $('dp-place-sale-btn').textContent = '⏳ Procesando...';

    const data = await post(ROUTES.placeSale, {
        payment_method:   paymentMethod,
        points_to_redeem: pointsToRedeem,
        cash_received:    cashReceived,
    });

    $('dp-place-sale-btn').disabled = false;
    $('dp-place-sale-btn').innerHTML = '💰 Cobrar — <span id="dp-btn-total">$0.00</span>';

    if (data.success) {
        // Mostrar receipt modal
        $('rcp-folio').textContent = data.folio;
        $('rcp-total').textContent = fmt(data.total);
        if (data.change_given > 0) {
            $('rcp-change').style.display = 'block';
            $('rcp-change-val').textContent = fmt(data.change_given);
        } else {
            $('rcp-change').style.display = 'none';
        }
        if (data.points_earned > 0) {
            $('rcp-points').style.display = 'block';
            $('rcp-points-val').textContent = data.points_earned;
        } else {
            $('rcp-points').style.display = 'none';
        }
        $('#dp-receipt-modal').modal('show');
        clearCustomer();
        await refreshCart();
    } else {
        alert(data.error || 'Error al procesar la venta.');
    }
});

// Al cerrar receipt → refrescar resumen
document.addEventListener('hidden.bs.modal', e => {
    if (e.target.id === 'dp-receipt-modal') refreshSummary();
});

// ── Init ──────────────────────────────────────────────────────────────────
bindCartEvents();
refreshSummary();
</script>
@endpush
