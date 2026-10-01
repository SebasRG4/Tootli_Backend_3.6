{{--
  _products_grid.blade.php  — Grid de productos para el POS Distribuidora
  Variables esperadas: $products (paginado de Item), $store
--}}
@if($products->count() > 0)
    <div class="dp-products">
        @foreach($products as $product)
            @php
                $price    = \App\CentralLogics\Helpers::item_price_for_context($product, 'direct');
                $discount = \App\CentralLogics\Helpers::product_discount_calculate($product, $price, $product->store)['discount_amount'];
                $finalPrice = max(0, $price - $discount);
            @endphp
            <div class="dp-product-card" data-product-id="{{ $product->id }}">
                <img src="{{ $product->image_full_url }}"
                     onerror="this.src='{{ asset('assets/admin/img/100x100/2.png') }}'"
                     alt="{{ $product->name }}">
                <div class="dp-pc-body">
                    <div class="dp-pc-name">{{ Str::limit($product->name, 32) }}</div>
                    @if($discount > 0)
                        <div class="dp-pc-discount">{{ \App\CentralLogics\Helpers::format_currency($price) }}</div>
                    @endif
                    <div class="dp-pc-price">{{ \App\CentralLogics\Helpers::format_currency($finalPrice) }}</div>
                </div>
                <div class="dp-add-btn">
                    <button data-dp-add="{{ $product->id }}" style="background:none;border:none;color:#fff;font-size:1.2rem;font-weight:700;cursor:pointer;padding:0;">＋ Agregar</button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Paginación --}}
    <div class="dp-pagination mt-3">
        {{ $products->links() }}
    </div>
@else
    <div class="text-center text-muted py-5">
        <i class="tio-search" style="font-size:2rem;"></i>
        <p class="mt-2">No se encontraron productos.</p>
    </div>
@endif
