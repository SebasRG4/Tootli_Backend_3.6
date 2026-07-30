<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'informe del artículo' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'Criterios de búsqueda' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'módulo'}} - {{ $data['module']?translate($data['module']):'todo' }}
                    <br>
                    {{ 'zona'}} - {{ $data['zone']??'todo' }}
                    <br>
                    {{ 'Negocio'}} - {{ $data['store']??'todo' }}
                    @if ($data['from'])
                    <br>
                    {{ 'de'}} - {{ $data['from']?Carbon\Carbon::parse($data['from'])->format('d M Y'):'' }}
                    @endif
                    @if ($data['to'])
                    <br>
                    {{ 'a'}} - {{ $data['to']?Carbon\Carbon::parse($data['to'])->format('d M Y'):'' }}
                    @endif
                    <br>
                    {{ 'filtrar'  }}- {{  translate($data['filter']) }}
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
            <th>{{'módulo'}}</th>
            <th>{{'nombre de la tienda'}}</th>
            <th>{{'existencias'}}</th>
            <th>{{'recuento total de pedidos'}}</th>
            <th>{{'precio unitario'}}</th>
            <th>{{'cantidad total vendida'}}</th>
            <th>{{'descuento total otorgado'}}</th>
            <th>{{'valor promedio de venta'}}</th>
            <th>{{'calificaciones totales dadas'}}</th>
            <th>{{'calificaciones promedio'}}</th>
        </thead>
        <tbody>
        @foreach($data['items'] as $key => $item)
            <tr>
                <td>{{ $key+1}}</td>
                <td></td>
                <td>{{$item['name']}}</td>
                <td>
                    {{ $item->module->module_name }}
                </td>
                <td>
                    @if($item->store)
                    {{ $item->store->name }}
                    @else
                    {{'tienda eliminada'}}
                    @endif
                </td>
                <td>
                    {{$item->module->module_type == 'food'? 'N / A' : $item->stock}}
                </td>
                <td>
                    {{$item->orders_sum_quantity ?? 0}}
                </td>
                <td>
                    {{ \App\CentralLogics\Helpers::format_currency($item->price) }}
                </td>
                <td>
                    {{ \App\CentralLogics\Helpers::format_currency($item->orders_sum_price) }}
                </td>
                <td>
                    {{ \App\CentralLogics\Helpers::format_currency($item->total_discount) }}
                </td>
                <td>
                    {{ $item->orders_count>0? \App\CentralLogics\Helpers::format_currency(($item->orders_sum_price-$item->total_discount)/($item->orders_sum_quantity ?? 0) ) :0 }}
                </td>
                <td>{{ $item->rating_count }}</td>
                <td>{{ round($item->avg_rating,1) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
