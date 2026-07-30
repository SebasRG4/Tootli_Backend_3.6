<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'informes de ventas de la tienda' }}</h1></div>
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
                <th>{{ 'Analítica' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'venta bruta'  }}- {{ \App\CentralLogics\Helpers::number_format_short($data['orders']->sum('order_amount')) }}
                    <br>
                    {{ 'impuesto total'  }}- {{ \App\CentralLogics\Helpers::number_format_short($data['orders']->sum('total_tax_amount')) }}
                    <br>
                    {{ 'comisión total'  }}- {{ \App\CentralLogics\Helpers::number_format_short($data['orders']->sum('transaction_sum_admin_commission')+$data['orders']->sum('transaction_sum_delivery_fee_comission')-$data['orders']->sum('transaction_sum_admin_expense')) }}
                    <br>
                    {{ 'ganancia total de la tienda'  }}- {{ \App\CentralLogics\Helpers::number_format_short($data['orders']->sum('transaction_sum_store_amount')) }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{'imagen del producto'}}</th>
            <th>{{ 'Nombre del producto' }}</th>
            <th>{{ 'Variaciones disponibles' }}</th>
            <th>{{ 'Cantidad vendida' }}</th>
            <th>
                {{ 'Venta bruta' }}</th>
            <th>
                {{ 'Descuento otorgado' }}</th>
        </thead>
        <tbody>
        @foreach($data['items'] as $key => $item)
        <tr>
            <td>{{$key+1}}</td>
            <td></td>
            <td>{{  $item['name']  }}</td>
            <td>
                @if ($item->module->module_type == 'food')
                {{ \App\CentralLogics\Helpers::get_food_variations($item->food_variations) == "  "  ? 'N / A': \App\CentralLogics\Helpers::get_food_variations($item->food_variations) }}
                @else
                {{ \App\CentralLogics\Helpers::get_attributes($item->choice_options) == "  "  ? 'N / A': \App\CentralLogics\Helpers::get_attributes($item->choice_options) }}
                @endif
            </td>
            <td>
                {{ $item->orders_sum_quantity ?? 0 }}
            </td>
            <td>
                {{\App\CentralLogics\Helpers::format_currency($item->orders_sum_price) }}
            </td>
            <td>
                {{ \App\CentralLogics\Helpers::format_currency($item->total_discount) }}
            </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
