<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'informes de pedidos de la tienda' }}</h1></div>
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
                    {{ 'pedidos totales'  }}- {{ $data['total_orders'] }}
                    <br>
                    {{ 'monto total del pedido'  }}- {{ $data['total_order_amount'] }}
                    <br>
                    {{ 'pedido cancelado'  }}- {{ $data['total_canceled_count'] }}
                    <br>
                    {{ 'pedidos completados'  }}- {{ $data['total_delivered_count'] }}
                    <br>
                    {{ 'pedidos incompletos'  }}- {{ $data['total_ongoing_count'] }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{ 'identificación del pedido' }}</th>
            <th>{{ 'fecha del pedido' }}</th>
            <th>{{ 'nombre del cliente' }}</th>
            <th>{{ 'nombre de la tienda' }}</th>
            <th>{{ 'cantidad total' }}</th>
            <th>{{ 'estado de pago' }}</th>
            <th>{{ 'cantidad descontada' }}</th>
            <th>{{ 'impuesto' }}</th>
            <th>{{ 'cargo de entrega' }}</th>
        </thead>
        <tbody>
            @foreach($data['orders'] as $key => $order)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $order->id }}</td>
                <td><div>
                    {{ date('d M Y', strtotime($order['created_at'])) }}
                </div>
                <br>
                <div>
                    {{ date(config('timeformat'), strtotime($order['created_at'])) }}
                </div></td>
                <td>
                    @if ($order->customer)
                        {{ $order->customer['f_name'] . ' ' . $order->customer['l_name'] }}
                    @else
                        {{ 'extraviado' }}
                    @endif
                </td>
                <td>
                    @if($order->store)
                        {{$order->store->name}}
                    @else
                        {{ 'extraviado' }}
                    @endif
                </td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']) }}</td>
                <td>{{ translate($order->payment_status) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount']  + $order['ref_bonus_amount'] +  $order['store_discount_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['total_tax_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['delivery_charge']) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
