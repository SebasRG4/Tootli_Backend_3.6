<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'informe de pedido' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'criterios de filtrado' }} -</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'módulo'}} - {{ $data['module']?translate($data['module']):'todo' }}
                    <br>
                    {{ 'zona'}} - {{ $data['zone']??'todo' }}
                    <br>
                    {{ 'Negocio'}} - {{ $data['store']??'todo' }}
                    <br>
                    {{ 'Cliente'}} - {{ $data['customer']??'todo' }}
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
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ 'SL' }}</th>
                <th>{{ 'identificación del pedido' }}</th>
                <th>{{ 'nombre del cliente' }}</th>
                <th>{{ 'nombre de la tienda' }}</th>
                <th>{{ 'precio del artículo' }}</th>
                <th>{{ 'descuento del artículo' }}</th>
                <th>{{ 'cupón de descuento' }}</th>
                <th>{{ 'descuento por referencia' }}</th>
                <th>{{ 'cantidad descontada' }}</th>
                <th>{{  \App\CentralLogics\Helpers::get_business_data('additional_charge_name')??'cargo adicional'  }}</th>
                <th>{{ 'cantidad de embalaje adicional' }}</th>
                <th>{{ 'impuesto' }}</th>
                <th>{{ 'cantidad total' }}</th>
                <th>{{ 'estado de pago' }}</th>
                <th>{{ 'tipo de orden' }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['orders'] as $key => $order)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $order->id }}</td>
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
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['order_amount'] - $order->additional_charge -$order['dm_tips']-$order['total_tax_amount']-$order['delivery_charge']+$order['coupon_discount_amount'] + $order['store_discount_amount'] + $order['ref_bonus_amount'] - $order['extra_packaging_amount'] +$order['flash_admin_discount_amount'] +$order['flash_store_discount_amount'] ) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order->details()->sum(DB::raw('discount_on_item * quantity')) + $order['flash_admin_discount_amount'] +$order['flash_store_discount_amount'] ) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['ref_bonus_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount'] + $order['store_discount_amount'] + $order['ref_bonus_amount'] ) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['additional_charge']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['extra_packaging_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['total_tax_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']) }}</td>
                <td>{{ translate($order->payment_status) }}</td>
                <td>{{ translate($order->order_type) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
