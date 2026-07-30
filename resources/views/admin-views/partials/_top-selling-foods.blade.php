<div class="card-header border-0 order-header-shadow">
    <h5 class="card-title d-flex justify-content-between">
        <span>{{ 'más vendidos' }} @if (Config::get('module.current_module_type') == 'food')
                {{ 'alimentos' }}
            @else
                {{ 'Productos' }}
            @endif
        </span>
    </h5>
    @php($params = session('dash_params'))
    @if ($params['zone_id'] != 'all')
        @php($zone_name = \App\Models\Zone::where('id', $params['zone_id'])->first()->name)
    @else
        @php($zone_name = 'todo')
    @endif
    <a href="{{ route('admin.item.list') }}" class="fz-12px font-medium text-006AE5">{{ 'ver todo' }}</a>
</div>

<div class="card-body">

    @if (count($top_sell) > 0)
        <div class="top--selling">
            @foreach ($top_sell as $key => $item)
                <a class="grid--card" href="{{ route('admin.item.view', [$item['id']]) }}">
                    <img class="initial--28 onerror-image"
                        src="{{ $item['image_full_url'] ?? asset('assets/admin/img/placeholder-2.png') }}"
                        data-onerror-image="{{ asset('assets/admin/img/placeholder-2.png') }}"
                        alt="{{ $item->name }} image">
                    <div class="cont pt-2" title="{{ $item?->name }}">
                        <span class="fz--13">{{ Str::limit($item['name'], 20, '...') }}</span>
                    </div>
                    <div class="ml-auto">
                        <span class="badge badge-soft">
                            {{ 'vendido' }} : {{ $item['order_count'] }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <!-- <div class="empty--data">
            <img src="{{ asset('assets/admin/svg/illustrations/empty-state.svg') }}" alt="public">
            <h5>
                {{ 'no se encontraron datos' }}
            </h5>
        </div> -->
        <div class="empty--data d-flex flex-column align-items-center justify-content-center h-100 w-100">
            <img src="{{ asset('assets/admin/img/no-items.png') }}" alt="public">
            <h5 class="secondary-clr">
                {{ 'No hay artículos disponibles' }}
            </h5>
        </div>
    @endif
</div>

<script src="{{ asset('assets/admin') }}/js/view-pages/common.js"></script>
