<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'lista de pedidos de clientes' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'información del cliente' }} -</th>
                <th></th>
                <th></th>
                <th> 
                    {{ 'identificación del cliente'}} : {{ translate($data['customer_id']) }}
                    <br>
                    {{ 'nombre'}} : {{ $data['customer_name'] }}
                    <br>
                    {{ 'teléfono'}} : {{ $data['customer_phone'] }}
                    <br>
                    {{ 'correo electrónico'}} : {{ $data['customer_email'] }}
                    <br>
                    {{ 'pedidos totales'}} : {{ $data['orders']->count() }}

                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ 'SL' }}</th>
                <th>{{ 'identificación del pedido' }}</th>
                <th>{{ 'nombre de la tienda' }}</th>
                <th>{{ 'precio del artículo' }}</th>
                <th>{{ 'descuento del artículo' }}</th>
                <th>{{ 'cupón de descuento' }}</th>
                <th>{{ 'cantidad descontada' }}</th>
                <th>{{ 'impuesto' }}</th>
                <th>{{ 'cantidad total' }}</th>
                <th>{{ 'estado de pago' }}</th>
                <th>{{ 'estado del pedido' }}</th>
                <th>{{ 'tipo de orden' }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['orders'] as $key => $order)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $order->id }}</td>
                <td>
                    @if($order->store)
                        {{$order->store->name}}
                    @else
                        {{ 'extraviado' }}
                    @endif
                </td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']-$order['dm_tips']-$order['total_tax_amount']-$order['delivery_charge']+$order['coupon_discount_amount'] + $order['store_discount_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order->details->sum('discount_on_item')) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount'] + $order['store_discount_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['total_tax_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']) }}</td>
                <td>{{ translate($order->payment_status) }}</td>
                <td>{{ translate($order->order_status) }}</td>
                <td>{{ translate($order->order_type) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
