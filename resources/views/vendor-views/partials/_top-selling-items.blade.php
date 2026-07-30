<!-- Header -->
<div class="card-header">
    <h5 class="card-header-title text-capitalize">
        <i class="tio-align-to-top"></i> {{'artículos más vendidos'}}
    </h5>
    <a href="{{ route('vendor.item.list') }}" class="fz-12px font-medium text-006AE5">{{ 'ver todo' }}</a>

</div>
<!-- End Header -->

<!-- Body -->
<div class="card-body">
    @if (count($top_sell) > 0)
    <div class="row g-2">
        @foreach($top_sell as $key=>$item)
            <div class="col-md-4 col-sm-6 initial--27 redirect-url"
                 data-url="{{route('vendor.item.view',[$item['id']])}}">
                <div class="grid-card">
                    <label class="label_1 text-center">{{'vendido'}} : {{$item['order_count']}}</label>
                    <img class="initial--28 onerror-image"
                    src="{{ $item['image_full_url'] }}"
                         data-onerror-image="{{asset('assets/admin/img/placeholder-2.png')}}"
                         alt="{{$item->name}} image">
                    <div class="text-center mt-2">
                        <span class="fz--13">{{Str::limit($item['name'],20,'...')}}</span>
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
