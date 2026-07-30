
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{'Lista de pedidos de la tienda'}}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ 'Detalles de la tienda' }}</th>
                <th></th>
                <th>
                    {{ 'Nombre de la tienda'  }}: {{ $data['store'] ?? 'N / A' }}
                    <br>
                    {{ 'Zona'  }}: {{ $data['zone'] ?? 'N / A' }}
                    <br>
                    {{ 'Orden total'  }}: {{ $data['data']->count() ?? 'N / A' }}
                </th>
                <th> </th>
            </tr>


            <tr>
                <th></th>
                <th></th>
                <th>
                    {{ 'Orden programada'  }}: {{ $data['data']->where('scheduled', '1')->count() ?? 'N / A' }}
                </th>
                <th>
                    {{ 'Orden pendiente'  }}: {{ $data['data']->where('order_status' ,'pending')->count() ?? 'N / A' }}
                </th>
                <th>
                    {{ 'Orden entregada'  }}: {{ $data['data']->where('order_status' ,'delivered')->count() ?? 'N / A' }}
                </th>
                <th>
                    {{ 'Orden cancelada'  }}: {{ $data['data']->where('order_status' ,'canceled')->count() ?? 'N / A' }}
                </th>
                <th>
                    {{ 'Orden reembolsada'  }}: {{ $data['data']->where('order_status' ,'refunded')->count() ?? 'N / A' }}
                </th>
                <th> </th>
            </tr>


        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{ 'ID de pedido' }}</th>
            <th>{{ 'Fecha del pedido' }}</th>
            <th>{{ 'Nombre del cliente' }}</th>
            <th>{{ 'Nombre de la tienda' }}</th>
            <th>{{ 'Artículos totales' }}</th>
            <th>{{ 'Precio del artículo' }}</th>
            <th>{{ 'Descuento del artículo' }}</th>
            <th>{{ 'Cupón de descuento' }}</th>
            <th>{{ 'Monto descontado' }}</th>
            <th>{{ 'IVA/Impuesto' }}</th>
            <th>{{ 'Monto total' }}</th>
            <th>{{ 'Estado de pago' }}</th>
            <th>{{ 'Estado del pedido' }}</th>
            <th>{{ 'Tipo de orden' }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $order)
            <tr>
                <td>{{ $loop->index+1}}</td>
                <td>{{ $order->id}}</td>
                <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d '.config('timeformat')) ??  'N / A' }}</td>
                <td>{{  $order?->customer ?  $order?->customer?->f_name.' '.$order?->customer?->l_name  : 'extraviado'  }}</td>
                <td>{{ $order?->store?->name }}</td>
                <td>{{$order->details->count() }}</td>
                <td> {{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']-$order['dm_tips']-$order['total_tax_amount']-$order['delivery_charge']+$order['coupon_discount_amount'] + $order['store_discount_amount']) }}
                </td>
                <td> {{ \App\CentralLogics\Helpers::number_format_short($order->details->sum('discount_on_item')) }} </td>
                <td> {{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount']) }}</td>
                <td> {{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount'] + $order['store_discount_amount']) }}</td>
                <td> {{ \App\CentralLogics\Helpers::number_format_short($order['total_tax_amount']) }}</td>
                <td> {{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']) }}</td>
                <td>{{translate($order->payment_status)}}</td>
                <td> {{ translate($order->order_status)}}</td>
                <td> {{ translate($order->order_type)}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
