<!-- Header -->
<div class="card-header">
    <h5 class="card-header-title text-capitalize">
        <i class="tio-star"></i> {{'artículos mejor valorados'}}
    </h5>
    <a href="{{ route('vendor.item.list') }}" class="fz-12px font-medium text-006AE5">{{ 'ver todo' }}</a>

</div>
<!-- End Header -->

<!-- Body -->
<div class="card-body">
    @if (count($most_rated_items) > 0)
    <div class="row g-2">
        @foreach($most_rated_items as $key=>$item)
        <div class="col-md-4 col-6">
            <div class="grid-card top--rated-food pb-4 cursor-pointer redirect-url"
                 data-url="{{route('vendor.item.view',[$item['id']])}}">
                <div class="text-center">
                    <img class="rounded onerror-image" src="{{ $item['image_full_url'] }}"
                    data-onerror-image="{{asset('assets/admin/img/100x100/2.png')}}" alt="{{Str::limit($item->name??'¡Artículo eliminado!',20,'...')}}">
                </div>

                <div class="text-center mt-3">
                    <h5 class="name m-0 mb-1">{{Str::limit($item->name??'¡Artículo eliminado!',20,'...')}}</h5>
                    <div class="rating">
                        <span class="text-warning"><i class="tio-star"></i> {{round($item['avg_rating'],1)}}</span>
                        <span class="text--title">({{$item['rating_count']}}  {{ 'opiniones' }})</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

    </div>

    @else
    <div class="empty--data">
        <img src="{{ asset('assets/admin/svg/illustrations/empty-state.svg') }}" alt="public">
        <h5>
            {{ 'no se encontraron datos' }}
        </h5>
    </div>

    @endif
</div>
<!-- End Body -->
<script src="{{asset('assets/admin')}}/js/view-pages/common.js"></script>
