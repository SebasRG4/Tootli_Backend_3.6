<div class="row">
    <div class="col-lg-12 text-center ">
        <h1> {{ Config::get('module.current_module_type') == 'food' ? 'Lista de alimentos' : 'Lista de artículos' }}
        </h1>
    </div>
    <div class="col-lg-12">

        <table>
            <thead>
                <tr>
                    <th>{{ 'Criterios de filtrado' }}</th>
                    <th></th>
                    <th></th>
                    <th>
                        {{ 'Nombre de la tienda' }}: {{ $data['store_name'] }}
                        <br>
                        {{ 'Zona' }}: {{ $data['zone'] }}
                        <br>
                        {{ 'Artículos totales' }}: {{ $data['data']->count() }}
                        @if (!(isset($data['sub_tab']) && ($data['sub_tab'] == 'pending-items' || $data['sub_tab'] == 'rejected-items')))
                            <br>
                            {{ 'Artículos activos' }}: {{ $data['data']->where('status', 1)->count() }}
                            <br>
                            {{ 'Artículos inactivos' }}: {{ $data['data']->where('status', 0)->count() }}
                        @endif
                        <br>
                        {{ 'Contenido de la barra de búsqueda' }}: {{ $data['search'] ?? 'N / A' }}
                    </th>
                    <th> </th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>{{ 'SL' }}</th>
                    <th>{{ 'Imagen' }}</th>
                    <th>{{ 'Nombre del artículo' }}</th>
                    <th>{{ 'Descripción' }}</th>
                    <th>{{ 'Nombre de categoría' }}</th>
                    <th>{{ 'Nombre de subcategoría' }}</th>
                    @if (Config::get('module.current_module_type') == 'food')
                        <th>{{ 'Tipo de comida' }}</th>
                    @else
                        <th>{{ 'Existencias disponibles' }} </th>
                    @endif
                    <th>{{ 'Precio' }}</th>
                    <th>{{ 'Variaciones disponibles' }} </th>
                    @if (Config::get('module.current_module_type') == 'food')
                        <th>{{ 'Complementos disponibles' }} </th>
                    @else
                        <th>{{ 'Unidad de artículo' }}</th>
                    @endif
                    <th>{{ 'Descuento' }} </th>
                    <th>{{ 'Tipo de descuento' }} </th>
                    <th>{{ 'Disponible desde' }} </th>
                    <th>{{ 'Disponible hasta' }} </th>
                    <th>{{ 'Etiquetas' }} </th>
                    <th>{{ 'Estado' }} </th>
                    @if ($data['productWiseTax'])
                        <th class="border-0 w--1">{{ 'IVA/Impuesto' }}</th>
                    @endif
            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $item)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td> &nbsp;</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->description }}</td>
                        <td>
                            {{ \App\CentralLogics\Helpers::get_category_name($item->category_ids) }}
                        </td>
                        <td>
                            {{ \App\CentralLogics\Helpers::get_sub_category_name($item->category_ids) ?? 'N / A' }}
                        </td>
                        @if (Config::get('module.current_module_type') == 'food')
                            <td> {{ $item->veg == 1 ? 'vegetales' : 'No vegetariano' }}</td>
                        @else
                            <td>{{ $item->stock }}</td>
                        @endif
                        <td>
                            {{ \App\CentralLogics\Helpers::format_currency($item->price) }}
                        </td>
                        <td>
                            @if (Config::get('module.current_module_type') == 'food')
                                {{ \App\CentralLogics\Helpers::get_food_variations($item->food_variations) == '  ' ? 'N / A' : \App\CentralLogics\Helpers::get_food_variations($item->food_variations) }}
                            @else
                                {{ \App\CentralLogics\Helpers::get_attributes($item->choice_options) == '  ' ? 'N / A' : \App\CentralLogics\Helpers::get_attributes($item->choice_options) }}
                            @endif
                        </td>

                        <td>
                            @if (Config::get('module.current_module_type') == 'food')
                                {{ \App\CentralLogics\Helpers::get_addon_data($item->add_ons) == 0 ? 'N / A' : \App\CentralLogics\Helpers::get_addon_data($item->add_ons) }}
                            @else
                                {{ $item?->unit?->unit ?? 'N / A' }}
                            @endif
                        </td>
                        <td>{{ $item->discount == 0 ? 'N / A' : $item->discount }}</td>
                        <td>{{ $item->discount_type }}</td>
                        <td>{{ Config::get('module.current_module_type') != 'grocery' ? \Carbon\Carbon::parse($item->available_time_starts)->format('H:i A') : 'N / A' }}
                        </td>
                        <td>{{ Config::get('module.current_module_type') != 'grocery' ? \Carbon\Carbon::parse($item->available_time_ends)->format('H:i A') : 'N / A' }}
                        </td>


                        @if (isset($data['sub_tab']) && ($data['sub_tab'] == 'pending-items' || $data['sub_tab'] == 'rejected-items'))
                            <td>
                                @php($tagids = json_decode($item?->tag_ids) ?? [])
                                @php($tags = \App\Models\Tag::whereIn('id', $tagids)->get('tag'))
                                @forelse($tags as $c)
                                {{ $c->tag . ',' }} @empty {{ 'N / A' }}
                                @endforelse

                            </td>
                            <td> {{ $item->is_rejected == 1 ? 'Rechazado' : 'Pendiente' }}</td>
                        @else
                            <td>
                                @forelse ($item->tags as $c)
                                {{ $c->tag . ',' }} @empty {{ 'N / A' }}
                                @endforelse
                            </td>
                            <td> {{ $item->status == 1 ? 'Activo' : 'Inactivo' }}</td>
                        @endif

                        @if ($data['productWiseTax'])
                            <td>
                                <span class="d-block font-size-sm text-body">

                                    @forelse ($item?->taxVats?->pluck('tax.name', 'tax.tax_rate')->toArray() as $key => $tax)
                                        <br>
                                        <span> {{ $tax }} : <span class="font-bold">
                                                ({{ $key }}%)
                                            </span> </span>
                                        <br>
                                    @empty
                                        <span> {{ 'sin impuestos' }} </span>
                                    @endforelse
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
