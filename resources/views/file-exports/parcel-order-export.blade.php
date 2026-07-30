<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate($data['status']) }} {{ 'Lista de pedidos de paquetes' }}</h1></div>
    <div class="col-lg-12">
    <table>
        <thead>
            <tr>
                <th>{{ 'criterios de filtrado' }} -</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'estado del pedido'}} : {{ translate($data['status']) }}
                    @if ($data['search'])
                    <br>
                    {{ 'contenido de la barra de búsqueda'}} : {{ $data['search'] }}
                    @endif
                    @if ($data['zones'])
                    <br>
                    {{ 'zonas'}} : {{ $data['zones'] }}
                    @endif

                    @if ($data['type'])
                    <br>
                    {{ 'tipo de orden'}} : {{ translate($data['type']) }}
                    @endif
                    @if ($data['from'])
                    <br>
                    {{ 'de'}} : {{ $data['from']?Carbon\Carbon::parse($data['from'])->format('d M Y'):'' }}
                    @endif
                    @if ($data['to'])
                    <br>
                    {{ 'a'}} : {{ $data['to']?Carbon\Carbon::parse($data['to'])->format('d M Y'):'' }}
                    @endif

                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ 'SL' }}</th>
                <th>{{ 'identificación del pedido' }}</th>
                <th>{{ 'Fecha' }}</th>
                <th>{{ 'categoría de paquete' }}</th>
                <th>{{ 'nombre del cliente' }}</th>
                <th>{{ 'cupón de descuento' }}</th>
                <th>{{ 'cantidad descontada' }}</th>
                <th>{{ 'impuesto' }}</th>
                <th>{{ 'cantidad total' }}</th>
                <th>{{ 'estado de pago' }}</th>
                <th>{{ 'Pago por' }}</th>
                <th>{{ 'Método de pago' }}</th>
                <th>{{ 'estado del pedido' }}</th>
                <th>{{ 'tipo de orden' }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['orders'] as $key => $order)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $order->id }}</td>

                <td>{{ \App\CentralLogics\Helpers::time_date_format($order->created_at) }}</td>
                <td>
                    <div>{{Str::limit($order->parcel_category?$order->parcel_category->name:'extraviado',20,'...')}}</div>
            </td>
                <td>
                    @if ($order->customer)
                        {{ $order->customer['f_name'] . ' ' . $order->customer['l_name'] }}
                    @else
                        {{ 'extraviado' }}
                    @endif
                </td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount'] + $order['store_discount_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['total_tax_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']) }}</td>
                <td>{{ translate($order->payment_status) }}</td>
                <td>{{ translate($order->charge_payer) }}</td>
                <td>{{ translate($order->payment_method) }}</td>
                <td>{{ translate($order->order_status) }}</td>
                <td>{{ translate($order->order_type) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
