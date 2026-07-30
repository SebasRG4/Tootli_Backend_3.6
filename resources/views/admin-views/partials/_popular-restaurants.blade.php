<div class="card-header border-0 order-header-shadow">
    <h5 class="card-title d-flex justify-content-between">
        {{ 'más popular' }} @if (Config::get('module.current_module_type') == 'food')
            {{ 'restaurantes' }}
        @else
            {{ 'Negocios' }}
        @endif
    </h5>
    @php($params = session('dash_params'))
    @if ($params['zone_id'] != 'all')
        @php($zone_name = \App\Models\Zone::where('id', $params['zone_id'])->first()->name)
    @else
        @php($zone_name = 'todo')
    @endif
    <a href="{{ route('admin.store.list') }}" class="fz-12px font-medium text-006AE5">{{ 'ver todo' }}</a>

</div>

<div class="card-body">

    @if (count($popular) > 0)
        <ul class="most-popular">
            @foreach ($popular as $key => $item)
                <li class="cursor-pointer redirect-url" data-url="{{ route('admin.store.view', $item->store_id) }}">
                    <div class="img-container">
                        <img class="onerror-image"
                            data-onerror-image="{{ asset('assets/admin/img/100x100/1.png') }}"
                            src="{{ $item->store['logo_full_url'] ?? asset('assets/admin/img/100x100/1.png') }}"
                            alt="{{ 'Negocio' }}" title="{{ $item?->store?->name }}">
                        <a href="{{ route('admin.store.view', $item->store_id) }}">
                            <span class="ml-2" title="{{ $item?->store?->name }}">
                                {{ Str::limit($item->store->name ?? 'tienda eliminada!', 20, '...') }}
                            </span>
                        </a>
                    </div>
                    <div>
                        <span class="text-FF6D6D">{{ $item['count'] }} <i class="tio-heart"></i></span>
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <!-- <div class="empty--data">
            <img src="{{ asset('assets/admin/svg/illustrations/empty-state.svg') }}" alt="public">
            <h5>
                {{ 'no se encontraron datos' }}
            </h5>
        </div> -->
        <div class="empty--data d-flex flex-column align-items-center justify-content-center h-100 w-100">
            <img src="{{ asset('assets/admin/img/no-store.png') }}" alt="public">
            <h5 class="secondary-clr">
                {{ 'No hay tiendas disponibles' }}
            </h5>
        </div>
    @endif


</div>
<script src="{{ asset('assets/admin') }}/js/view-pages/common.js"></script>
