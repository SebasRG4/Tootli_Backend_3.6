{{--
  _cart.blade.php — Carrito del POS Distribuidora
  Usa la sesión 'dist_cart'
--}}
@php
    $cart      = session()->get('dist_cart', collect([]));
    $items     = $cart->filter(fn($i) => is_array($i));
    $subtotal  = 0;
    $discounts = 0;
    $discSess  = session()->get('dist_discount', ['amount' => 0, 'type' => 'amount']);

    foreach ($items as $item) {
        $subtotal  += $item['price'] * $item['quantity'];
        $discounts += ($item['discount'] ?? 0) * $item['quantity'];
    }

    $extraDiscount = 0;
    if (($discSess['type'] ?? 'amount') === 'percent') {
        $extraDiscount = (($subtotal - $discounts) * $discSess['amount']) / 100;
    } else {
        $extraDiscount = (float) ($discSess['amount'] ?? 0);
    }
    $total = max(0, round($subtotal - $discounts - $extraDiscount, 2));
@endphp

@if($items->isEmpty())
    <div class="text-center text-muted py-4">
        <i class="tio-shopping-cart" style="font-size:2rem;"></i>
        <p class="mt-2" style="font-size:.85rem;">Agrega productos para comenzar</p>
    </div>
@else
    @foreach($items as $key => $item)
        <div class="dp-cart-item">
            <img src="{{ $item['image_full_url'] }}"
                 onerror="this.src='{{ asset('assets/admin/img/100x100/2.png') }}'"
                 alt="{{ $item['name'] }}">
            <div class="dp-ci-info">
                <div class="dp-ci-name">{{ Str::limit($item['name'], 22) }}</div>
                <div class="dp-ci-price">
                    {{ \App\CentralLogics\Helpers::format_currency($item['price']) }}
                    @if(($item['discount'] ?? 0) > 0)
                        <small style="color:#EF4444;"> −{{ \App\CentralLogics\Helpers::format_currency($item['discount']) }}</small>
                    @endif
                </div>
            </div>
            <div class="dp-ci-qty">
                <button data-dp-qty-minus="{{ $key }}">−</button>
                <input data-dp-qty-key="{{ $key }}" type="number" min="1"
                       max="{{ $item['maximum_cart_quantity'] ?? 999999 }}"
                       value="{{ $item['quantity'] }}">
                <button data-dp-qty-plus="{{ $key }}">+</button>
            </div>
            <button class="dp-ci-remove" data-dp-remove="{{ $key }}">
                <i class="tio-delete-outlined"></i>
            </button>
        </div>
    @endforeach

    {{-- Totales --}}
    <div class="dp-totals">
        <dl>
            <dt>Subtotal <span>{{ \App\CentralLogics\Helpers::format_currency($subtotal) }}</span></dt>
            @if($discounts > 0)
                <dt>Desc. producto <span style="color:#EF4444;">−{{ \App\CentralLogics\Helpers::format_currency($discounts) }}</span></dt>
            @endif
            @if($extraDiscount > 0)
                <dt>Desc. adicional <span style="color:#EF4444;">−{{ \App\CentralLogics\Helpers::format_currency($extraDiscount) }}</span></dt>
            @endif
        </dl>
        <div class="dp-total-row">
            <span style="font-weight:700; font-size:1rem;">Total</span>
            <strong style="font-size:1.25rem; color:var(--dp-primary);" id="dp-cart-total">
                {{ \App\CentralLogics\Helpers::format_currency($total) }}
            </strong>
        </div>
        {{-- Descuento adicional del cajero --}}
        <div style="margin-top:8px; text-align:right;">
            <button type="button" data-toggle="modal" data-target="#dp-discount-modal"
                    style="background:none;border:1.5px dashed var(--dp-border);border-radius:8px;padding:4px 12px;font-size:.78rem;color:var(--dp-muted);cursor:pointer;">
                ✏️ Descuento adicional
            </button>
        </div>
    </div>
@endif

{{-- Modal descuento --}}
<div class="modal fade" id="dp-discount-modal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Descuento adicional</h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('vendor.distributor-pos.discount') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="input-label">Cantidad</label>
                        <input type="number" name="discount" class="form-control" min="0" step="0.01"
                               value="{{ $discSess['amount'] ?? 0 }}">
                    </div>
                    <div class="form-group">
                        <label class="input-label">Tipo</label>
                        <select name="type" class="form-control">
                            <option value="amount" {{ ($discSess['type'] ?? 'amount') === 'amount' ? 'selected' : '' }}>Monto ($)</option>
                            <option value="percent" {{ ($discSess['type'] ?? 'amount') === 'percent' ? 'selected' : '' }}>Porcentaje (%)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn--primary btn-block">Aplicar</button>
                </form>
            </div>
        </div>
    </div>
</div>
