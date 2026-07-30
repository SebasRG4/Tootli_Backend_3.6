<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{ Config::get('module.current_module_type')== 'food' ?  'Lista de campañas alimentarias' : 'Lista de campañas de artículos' }}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ 'Criterios de filtrado' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'Módulo'  }}: {{ $module_name }}
                    <br>
                    {{ 'Contenido de la barra de búsqueda'  }}: {{ $search ??'N / A' }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>


        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{ 'Nombre del artículo' }}</th>
            <th>{{ 'Descripción' }}</th>
            <th>{{ 'Nombre de categoría' }}</th>
            <th>{{ 'Nombre de subcategoría' }}</th>
            <th>{{ 'Unidad de artículo' }}</th>
            <th>{{ 'Precio' }}</th>
            <th>{{ 'Variaciones disponibles' }} </th>
            <th>{{ 'Descuento' }} </th>
            <th>{{ 'Tipo de descuento' }} </th>
            @if (Config::get('module.current_module_type') != 'food')
            <th>{{ 'Existencias disponibles' }} </th>
            @endif


            <th>{{ 'Fecha de inicio' }} </th>
            <th>{{ 'Fecha de finalización' }} </th>
            <th>{{ 'Hora de inicio diaria' }} </th>
            <th>{{ 'Hora de finalización diaria' }} </th>
            <th>{{ 'Nombre de la tienda' }} </th>
        </thead>
        <tbody>
        @foreach($data as $key => $campaign)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $campaign->title }}</td>
        <td>{{ $campaign->description }}</td>
        <td>
            {{ \App\CentralLogics\Helpers::get_category_name($campaign->category_ids) }}
        </td>
        <td>
        {{ \App\CentralLogics\Helpers::get_sub_category_name($campaign->category_ids) ?? 'N / A'  }}
        </td>

        <td>{{ $campaign?->unit?->unit ?? 'N / A' }}</td>
        <td>
            {{ \App\CentralLogics\Helpers::format_currency($campaign->price) }}
        </td>
        <td>
            @if (Config::get('module.current_module_type') == 'food')
            {{ \App\CentralLogics\Helpers::get_food_variations($campaign->food_variations) == "  "  ? 'N / A': \App\CentralLogics\Helpers::get_food_variations($campaign->food_variations) }}
            @else
            {{ \App\CentralLogics\Helpers::get_attributes($campaign->choice_options) == "  "  ? 'N / A': \App\CentralLogics\Helpers::get_attributes($campaign->choice_options) }}
            @endif
        </td>
        <td>{{ $campaign->discount }}</td>
        <td>{{ $campaign->discount_type }}</td>


        @if (Config::get('module.current_module_type') != 'food')
            <td>{{ $campaign->stock }}</td>
        @endif

        <td>{{ $campaign->start_date->format('d M Y') }}</td>
        <td>{{ $campaign->end_date->format('d M Y') }}</td>
        <td>{{ \Carbon\Carbon::parse($campaign->start_time)->format("H:i A") }}</td>
        <td>{{ \Carbon\Carbon::parse($campaign->end_time)->format("H:i A") }}</td>
        <td>{{ $campaign?->store?->name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
