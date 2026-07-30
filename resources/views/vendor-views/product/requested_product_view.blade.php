@extends('layouts.vendor.app')

@section('title', 'Vista previa del artículo')

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between">
                <h1 class="page-header-title text-break">
                    <span class="page-header-icon">
                        <img src="{{ asset('assets/admin/img/temp_pro.png') }}" class="w--22" alt="">
                    </span>
                    <span>{{ 'Detalles del producto' }}</span>
                </h1>

            </div>
        </div>
        <!-- End Page Header -->

        <div class="card mb-3">
            <!-- Body -->
            <div class="card-body">
                <div class="row flex-wrap">
                    <div>
                        <div class="d-flex flex-wrap align-items-center food--media position-relative mr-4">
                            <img class="avatar avatar-xxl avatar-4by3 onerror-image"
                            src="{{ $product['image_full_url'] }}"
                                 data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                alt="Image Description">
                                @if ($product['is_rejected'] == 1 )
                                <div class="reject-info"> {{ 'Su artículo ha sido rechazado' }}</div>
                                @else
                                <div class="pending-info"> {{ 'Este artículo está bajo revisión' }}</div>
                                @endif
                        </div>
                    </div>
                    <div class="w-70 flex-grow">
                        @php($language = \App\Models\BusinessSetting::where('key', 'language')->first()?->value ?? null)
                        @php($defaultLang = str_replace('_', '-', app()->getLocale()))
                        <div class="d-flex flex-wrap gap-2 justify-content-between">
                            @if ($language)
                            <ul class="nav nav-tabs border-0 mb-3">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active" href="#"
                                        id="default-link">{{ 'por defecto' }}</a>
                                </li>
                                @foreach (json_decode($language) as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link" href="#"
                                        id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                                <div class="d-flex flex-wrap gap-2 align-items-start">
                                    <a class="btn btn--sm btn-outline-danger form-alert" href="javascript:"
                                    data-id="food-{{$product['id']}}" data-message="{{ '¿Quieres eliminar este elemento?' }}" title="{{'eliminar elemento'}}">{{ 'Borrar' }} <i class="tio-delete-outlined"></i>
                                    </a>
                                    <a href="{{ route('vendor.item.edit', [$product['id'],'temp_product' => true]) }}" class="btn btn--sm btn-outline-primary">
                                        <i class="tio-edit"></i>  {{ 'editar y volver a enviar' }}
                                    </a>
                                <form action="{{route('vendor.item.delete',[$product['id']])}}"
                                        method="post" id="food-{{$product['id']}}">
                                    @csrf @method('delete')
                                    <input type="hidden" value="1" name="temp_product" >
                                </form>


                                </div>
                            </div>

                        <div class="lang_form" id="default-form">
                            <h2 class="mt-3">{{ $product?->getRawOriginal('name') }} </h2>
                            <h6> {{ 'descripción' }}:</h6>
                            <P> {{ $product?->getRawOriginal('description') }}</P>
                        </div>

                        @foreach (json_decode($language) as $lang)
                                    <?php
                                    if (count($product['translations'])) {
                                        $translate = [];
                                        foreach ($product['translations'] as $t) {
                                            if ($t->locale == $lang && $t->key == 'name') {
                                                $translate[$lang]['name'] = $t->value;
                                            }
                                            if ($t->locale == $lang && $t->key == 'description') {
                                                $translate[$lang]['description'] = $t->value;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="d-none lang_form" id="{{ $lang }}-form">
                                        <h2>{{ $translate[$lang]['name'] ?? '' }} </h2>
                                        <h6> {{ 'descripción' }}:</h6>
                                        <P> {!! $translate[$lang]['description'] ?? '' !!}</P>
                                    </div>
                        @endforeach
                    </div>
                </div>


            </div>
            <!-- End Body -->
        </div>

    <!-- Description Card Start -->
    <div class="card mb-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th class="px-4 border-0">
                                <h4 class="m-0 text-capitalize">{{ 'Información general' }}</h4>
                            </th>
                            <th class="px-4 border-0">
                                <h4 class="m-0 text-capitalize">{{ 'información de precios' }}</h4>
                            </th>

                            @if (in_array($product->module->module_type ,['food','grocery']))
                            <th class="px-4 border-0">
                                <h4 class="m-0 text-capitalize">{{ 'Nutrición' }}</h4>
                            </th>
                            <th class="px-4 border-0">
                                <h4 class="m-0 text-capitalize">{{ 'Alergia' }}</h4>
                            </th>

                        @endif
                        @if (in_array($product->module->module_type ,['pharmacy']))
                            <th class="px-4 border-0">
                                <h4 class="m-0 text-capitalize">{{ 'Nombre genérico' }}</h4>
                            </th>
                        @endif
                            <th class="px-4 border-0">
                                <h4 class="m-0 text-capitalize">{{ 'Variaciones disponibles' }}</h4>
                            </th>
                            @if ($product->module->module_type == 'food')
                                <th class="px-4 border-0">
                                    <h4 class="m-0 text-capitalize">{{ 'complementos' }}</h4>
                                </th>
                            @endif
                            <th class="px-4 border-0">
                                <h4 class="m-0 text-capitalize">{{ 'etiquetas' }}</h4>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-4 max-w--220px">
                                <span class="d-block mb-1">
                                    <span>{{ 'Almacenar' }} : </span>
                                    <strong>{{ $product?->store?->name }}</strong>
                                </span>
                                <span class="d-block mb-1">
                                    <span>{{ 'Categoría' }} : </span>
                                    <strong>{{ Str::limit(($product?->category?->parent ? $product?->category?->parent?->name : $product?->category?->name )  ?? 'descategorizar'
                                        , 20, '...') }}</strong>
                                </span>

                                <span class="d-block mb-1">
                                    <span>{{ 'Subcategoría' }} : </span>
                                    <strong>{{ Str::limit(( $product?->category?->parent?->name ? $product?->category?->name : '---' )
                                        , 20, '...') }}</strong>
                                </span>

                                @if ($product->module->module_type == 'grocery')
                                <span class="d-block mb-1">
                                    <span>{{ 'es organico' }} : </span>
                                    <strong> {{  $product->organic == 1 ?  'Sí' : 'No' }}</strong>
                                </span>
                                @endif
                                @if ($product->module->module_type == 'food')
                                <span class="d-block mb-1">
                                    <span>{{ 'tipo de artículo' }} : </span>
                                    <strong> {{  $product->veg == 1 ?  'verduras' : 'no vegetariano' }}</strong>
                                </span>
                                @else
                                <span class="d-block mb-1">
                                    <span>{{ 'existencias totales' }} : </span>
                                    <strong> {{  $product->stock  }}</strong>
                                </span>

                                    @if ($product?->unit)
                                    <span class="d-block mb-1">
                                        <span>{{ 'Unidad' }} : </span>
                                        <strong> {{ $product?->unit?->unit  }}</strong>
                                    </span>
                                    @endif
                                @endif
                                @if (config('module.' . $product->module->module_type)['item_available_time'])
                                <span class="d-block mb-1">
                                    {{ 'comienza el tiempo disponible' }} :
                                    <strong>{{ date(config('timeformat'), strtotime($product['available_time_starts'])) }}</strong>
                                </span>
                                <span class="d-block mb-1">
                                    {{ 'finaliza el tiempo disponible' }} :
                                    <strong>{{ date(config('timeformat'), strtotime($product['available_time_ends'])) }}</strong>
                                </span>
                            @endif
                            </td>
                            <td class="px-4">
                                <span class="d-block mb-1">
                                    <span>{{ 'Precio unitario' }} : </span>
                                    <strong>{{ \App\CentralLogics\Helpers::format_currency($product['price']) }}</strong>
                                </span>
                                <span class="d-block mb-1">
                                    <span>{{ 'cantidad descontada' }} :</span>
                                    <strong>{{ \App\CentralLogics\Helpers::format_currency(\App\CentralLogics\Helpers::discount_calculate($product, $product['price'])) }}</strong>
                                </span>
                                <span class="d-block mb-1">
                                    <span>{{ 'descuento' }} :</span>
                                    <strong> {{ $product->discount_type == 'percent' ? $product->discount .'%' :  \App\CentralLogics\Helpers::format_currency($product['discount']) }} </strong>
                                </span>



                            </td>

                            @php($product_nutritions = $product?->nutrition_ids ? \App\Models\Nutrition::whereIn('id', json_decode($product?->nutrition_ids))->pluck('nutrition') : [])
                            @php($product_allergies = $product?->allergy_ids ?\App\Models\Allergy::whereIn('id', json_decode($product?->allergy_ids))->pluck('allergy') : [])

                            @if (in_array($product->module->module_type ,['food','grocery']))
                            <td class="px-4 product-gallery-info">

                                    @foreach($product_nutritions as $nutrition)
                                        {{$nutrition}}{{ !$loop->last ? ',' : '.'}}
                                    @endforeach

                            </td>
                            <td class="px-4 product-gallery-info">
                                    @foreach($product_allergies as $allergy)
                                        {{$allergy}}{{ !$loop->last ? ',' : '.'}}
                                    @endforeach

                            </td>
                            @endif
                            @if (in_array($product->module->module_type ,['pharmacy']))
                                <td class="px-4 product-gallery-info">
                                    {{ \App\Models\GenericName::where('id', json_decode($product?->generic_ids))->first()?->generic_name }}
                                </td>
                            @endif




                            <td class="px-4">
                                @if ($product->module->module_type == 'food')
                                    @if ($product->food_variations && is_array(json_decode($product['food_variations'], true)))
                                        @foreach (json_decode($product->food_variations, true) as $variation)
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
                                                        &nbsp; &nbsp; {{ $value['label'] }} :
                                                        <strong>{{ \App\CentralLogics\Helpers::format_currency($value['optionPrice']) }}</strong>
                                                    </span>
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach
                                @endif
                            @else
                                @if ($product->variations && is_array(json_decode($product['variations'], true)))
                                    @foreach (json_decode($product['variations'], true) as $variation)
                                        <span class="d-block mb-1 text-capitalize">
                                            {{ $variation['type'] }} :
                                            {{ \App\CentralLogics\Helpers::format_currency($variation['price']) }}
                                        </span>
                                    @endforeach
                                @endif
                        </td>
                        @endif
                        @if ($product->module->module_type == 'food')
                            <td class="px-4">
                                {{-- @if (config('module.' . $product->module->module_type)['add_on']) --}}
                                    @foreach (\App\Models\AddOn::whereIn('id', json_decode($product['add_ons'], true))->get() as $addon)
                                        <span class="d-block mb-1 text-capitalize">
                                            {{ $addon['name'] }} :
                                            {{ \App\CentralLogics\Helpers::format_currency($addon['price']) }}
                                        </span>
                                    @endforeach
                                {{-- @endif --}}
                            </td>
                        @endif

                        @php( $tags =\App\Models\Tag::whereIn('id',json_decode($product?->tag_ids) )->get('tag'))
                            <td>
                                @foreach($tags as $c) {{$c->tag}}{{ !$loop->last ? ',' : '.'}} @endforeach
                            </td>

                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Description Card End -->

</div>
@endsection

@push('script_2')
<script>
    "use strict";
        function request_alert(url, message) {
            Swal.fire({
                title: '{{'¿está seguro?'}}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{'No'}}',
                confirmButtonText: '{{'Sí'}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }

    function cancelled_status(route, message, processing = false) {
            Swal.fire({
                    //text: message,
                    title: '{{ 'Está seguro ?' }}',
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#FC6A57',
                    cancelButtonText: '{{ 'Cancelar' }}',
                    confirmButtonText: '{{ 'entregar' }}',
                    inputPlaceholder: "{{ 'Introduce un motivo' }}",
                    input: 'text',
                    html: message + '<br/>'+'<label>{{ 'Introduce un motivo' }}</label>',
                    inputValue: processing,
                    preConfirm: (note) => {
                        location.href = route + '&note=' + note;
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                })
        }
</script>
@endpush
