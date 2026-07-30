
@foreach($items as $key=>$item)
    <div class="col-12">
        <div class="card mb-3">
            <!-- Body -->
            <div class="card-body ml-2">
                <div class="table-responsive">
                    <div class="min-width-720">
                    <div class="d-flex">
                        <div>
                            <img class="avatar avatar-xxl avatar-4by3 onerror-image aspect-ratio-1 h-unset"

                            src="{{ $item['image_full_url'] ?? asset('assets/admin/img/160x160/img2.jpg') }}"
                                data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                alt="Image Description">
                        </div>
                        <div class="col-10">
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0 ml-4">{{ $item?->getRawOriginal('name') }} </h4>
                                <div>
                                    <a target="_blank" href="{{ route('admin.item.edit',['id' => $item->id , 'product_gellary' => true ]) }}" class="btn btn--sm btn-outline-primary">
                                            {{ 'utilizar la información de este producto' }}
                                    </a>
                                </div>
                            </div>
                            <table class="table table-borderless table-thead-bordered m-0">
                                <tbody>
                                    <tr>
                                        <td class="px-4 max-w--220px product-gallery-info">
                                            <h6 class="m-0 text-capitalize">{{ 'Información general' }}</h6>
                                        </td>
                                        <td class="px-4 product-gallery-info">
                                            <h6 class="m-0 text-capitalize">{{ 'Variaciones disponibles' }}</h6>
                                        </td>
                                        <td>
                                            <h6 class="m-0 text-capitalize">{{ 'etiquetas' }}</h6>
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 max-w--220px product-gallery-info">
                                            <span class="d-block mb-1">
                                                <span>{{ 'Categoría' }}</span>
                                                <span>:</span>
                                                <strong>{{ Str::limit(($item?->category?->parent ? $item?->category?->parent?->name : $item?->category?->name )  ?? 'descategorizar'
                                                    , 20, '...') }}</strong>
                                            </span>
                                            <span class="d-block mb-1">
                                                <span>{{ 'Subcategoría' }}</span>
                                                <span>:</span>
                                                <strong>{{ Str::limit(($item?->category?->name )  ?? 'descategorizar'
                                                    , 20, '...') }}</strong>
                                            </span>
                                            @if ($item->module->module_type == 'grocery')
                                            <span class="d-block mb-1">
                                                <span>{{ 'es organico' }}</span>
                                                <span>:</span>
                                                <strong> {{  $item->organic == 1 ?  'Sí' : 'No' }}</strong>
                                            </span>
                                            @endif
                                            @if ($item->module->module_type == 'food')
                                            <span class="d-block mb-1">
                                                <span>{{ 'tipo de artículo' }} : </span>
                                                <span>:</span>
                                                <strong> {{  $item->veg == 1 ?  'verduras' : 'no vegetariano' }}</strong>
                                            </span>
                                            @else
                                                @if ($item?->unit)
                                                <span class="d-block mb-1">
                                                    <span>{{ 'Unidad' }} : </span>
                                                    <span>:</span>
                                                    <strong> {{ $item?->unit?->unit  }}</strong>
                                                </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-4 product-gallery-info">
                                            @if ($item->module->module_type == 'food')
                                                @if ($item->food_variations && is_array(json_decode($item['food_variations'], true)))
                                                    @foreach (json_decode($item->food_variations, true) as $variation)
                                                        @if (isset($variation['price']))
                                                            <span class="d-block mb-1 text-capitalize">
                                                                <strong>
                                                                    {{ 'actualice las variaciones de alimentos.' }}
                                                                </strong>
                                                            </span>
                                                        @break

                                                    @else
                                                        <span class="d-block text-capitalize">
                                                            <strong>
                                                                {{ $variation['name'] }} -
                                                            </strong>
                                                            @if ($variation['type'] == 'multi')
                                                                {{ 'selección múltiple' }}
                                                            @elseif($variation['type'] == 'single')
                                                                {{ 'selección única' }}
                                                            @endif
                                                            @if ($variation['required'] == 'on')
                                                                - ({{ 'requerido' }})
                                                            @endif
                                                        </span>

                                                        @if ($variation['min'] != 0 && $variation['max'] != 0)
                                                            ({{ 'Selección mínima' }}: {{ $variation['min'] }} -
                                                            {{ 'selección máxima' }}: {{ $variation['max'] }})
                                                        @endif

                                                        @if (isset($variation['values']))
                                                            @foreach ($variation['values'] as $value)
                                                                <span class="d-block text-capitalize">
                                                                    <span>{{ $value['label'] }}</span> <span>:</span>
                                                                    <strong>{{ \App\CentralLogics\Helpers::format_currency($value['optionPrice']) }}</strong>
                                                                </span>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                @endforeach
                                            @endif
                                        @else
                                            @if ($item->variations && is_array(json_decode($item['variations'], true)))
                                                @foreach (json_decode($item['variations'], true) as $variation)
                                                    <span class="d-block mb-1 text-capitalize">
                                                        <span>{{ $variation['type'] }} </span>
                                                        <span>:</span>
                                                        <strong>{{ \App\CentralLogics\Helpers::format_currency($variation['price']) }}</strong>
                                                    </span>
                                                @endforeach
                                            @endif
                                    </td>
                                    @endif

                                        <td>
                                                @foreach($item->tags as $c) {{ $c->tag }}{{ !$loop->last ? ',' : '.'}} @endforeach
                                        </td>

                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                </tr>
                            </tbody>
                            </table>
                        </div>
                        </div>
                        <div>
                            <h6> {{ 'descripción' }}:</h6>
                            <P class="m-0"> {{ $item?->getRawOriginal('description') }}</P>
                        </div>
                    </div>
                </div>


            </div>
            <!-- End Body -->
        </div>
    </div>
@endforeach
<script src="{{asset('assets/admin')}}/js/view-pages/common.js"></script>
