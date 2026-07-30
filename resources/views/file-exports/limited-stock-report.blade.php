<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'informe de stock limitado' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'Criterios de búsqueda' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'zona'}} - {{ $data['zone']??'todo' }}
                    <br>
                    {{ 'Negocio'}} - {{ $data['store']??'todo' }}
                    <br>
                    {{ 'Contenido de la barra de búsqueda'  }}- {{ $data['search'] ??'N / A' }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{'imagen del artículo'}}</th>
            <th>{{'nombre del artículo'}}</th>
            <th>{{ 'stock actual' }}</th>
            <th>{{ 'nombre de categoría' }}</th>
            <th>{{'unidad'}}</th>
            <th>{{'variación'}}</th>
            <th>{{'precio'}}</th>
            <th>{{'nombre de la tienda'}}</th>
            <th>{{'nombre del módulo'}}</th>
        </thead>
        <tbody>
        @foreach($data['items'] as $key => $item)
            <tr>
                <td>{{ $key+1}}</td>
                <td></td>
                <td>{{$item['name']}}</td>
                <td>
                    @if ($item->module->module_type != 'food')
                    {{ $item->stock }}
                    @endif
                </td>
                <td>
                    {{ \App\CentralLogics\Helpers::get_category_name($item->category_ids) }}
                </td>
                <td>{{ $item?->unit?->unit ?? 'N / A' }}</td>
                <td>
                    @if ($item->module->module_type == 'food')
                    {{ \App\CentralLogics\Helpers::get_food_variations($item->food_variations) == "  "  ? 'N / A': \App\CentralLogics\Helpers::get_food_variations($item->food_variations) }}
                    @else
                    {{ \App\CentralLogics\Helpers::get_attributes($item->choice_options) == "  "  ? 'N / A': \App\CentralLogics\Helpers::get_attributes($item->choice_options) }}
                    @endif
                </td>
                <td>
                    {{ \App\CentralLogics\Helpers::format_currency($item->price) }}
                </td>
                <td>
                    @if($item->store)
                    {{ $item->store->name }}
                    @else
                    {{'tienda eliminada'}}
                    @endif
                </td>
                <td>
                    {{ $item->module->module_name }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
