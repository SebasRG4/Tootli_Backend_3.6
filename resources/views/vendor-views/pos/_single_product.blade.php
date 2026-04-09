@php
    $descRaw = $product->description ?? ($product['description'] ?? '');
    $desc = Str::limit(trim(strip_tags((string) $descRaw)), 72);
    $rating = number_format((float) ($product->avg_rating ?? $product['avg_rating'] ?? 0), 1);
@endphp
<div class="pos-grill-card product-card card quick-View border-0 shadow-sm h-100" data-id="{{ $product->id }}">
    <div class="pos-grill-card-image-wrap">
        <img src="{{ $product['image_full_url'] }}"
            data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
            class="pos-grill-card-img onerror-image w-100 h-100 object-cover" alt="">
    </div>
    <div class="pos-grill-card-body">
        <div class="pos-grill-card-title-row">
            <span class="pos-grill-card-title">{{ Str::limit($product['name'], 28) }}</span>
            <span class="pos-grill-card-price">{{ \App\CentralLogics\Helpers::format_currency(\App\CentralLogics\Helpers::pos_base_unit_after_discount($product, (bool) session('pos_tootli_direct'))) }}</span>
        </div>
        <p class="pos-grill-card-desc {{ $desc === '' ? 'pos-grill-card-desc--empty' : '' }}">{{ $desc !== '' ? $desc : '—' }}</p>
        <div class="pos-grill-card-footer">
            <span class="pos-grill-card-rating"><i class="tio-star"></i> {{ $rating }}</span>
            <span class="pos-grill-card-add-btn" role="button" tabindex="0">{{ translate('messages.add_to_cart') }}</span>
        </div>
    </div>
</div>
