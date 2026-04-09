<div class="row g-3 mb-auto pos-grill-product-grid">
    @foreach ($products as $product)
        <div class="order--item-box item-box">
            @include('vendor-views.pos._single_product', [
                'product' => $product,
                'store' => $store_data,
            ])
        </div>
    @endforeach
</div>
@if (count($products) >= 13)
    <hr>
@endif
<div class="page-area mt-2">
    {!! $products->links() !!}
</div>
@if (count($products) === 0)
    <div class="search--no-found">
        <img src="{{ asset('assets/admin/img/search-icon.png') }}" alt="img">
        <p>
            {{ translate('messages.no_products_on_store_pos_search') }}
        </p>
    </div>
@endif
