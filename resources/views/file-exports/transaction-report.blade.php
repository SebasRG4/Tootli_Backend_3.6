<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'informe de transacciones de pedidos' }}</h1></div>
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
                <th>{{ 'Análisis de transacciones' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'Transacciones completadas'  }}- {{ $data['delivered'] ??'N / A' }}
                    <br>
                    {{ 'Transacciones reembolsadas'  }}- {{ $data['canceled'] ??'N / A' }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ 'Análisis de ganancias' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'Ganancias de administrador'  }} - {{ $data['admin_earned'] ??'N / A' }}
                    <br>
                    {{ 'Ganancias de la tienda'  }} - {{ $data['store_earned'] ??'N / A' }}
                    <br>
                    {{ 'Ganancias del repartidor'  }} - {{ $data['deliveryman_earned'] ??'N / A' }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ 'título del informe de la puerta de enlace de ecartpay' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'encabezado de tarjeta guardada ecartpay' }}:
                    {{ 'tarifas totales de entrada de ecartpay' }}
                    — {{ \App\CentralLogics\Helpers::format_currency($data['ecartpayFeesCardTotal'] ?? 0) }}
                    <br>
                    {{ 'encabezado de tarjeta guardada ecartpay' }}:
                    {{ 'red de administración de ecartpay después de la puerta de enlace' }}
                    — {{ \App\CentralLogics\Helpers::format_currency($data['adminNetAfterEcartpayCard'] ?? 0) }}
                    <br>
                    {{ 'título ecartpay spei' }}:
                    {{ 'tarifas totales de entrada de ecartpay' }}
                    — {{ \App\CentralLogics\Helpers::format_currency($data['ecartpayFeesSpeiTotal'] ?? 0) }}
                    <br>
                    {{ 'título ecartpay spei' }}:
                    {{ 'red de administración de ecartpay después de la puerta de enlace' }}
                    — {{ \App\CentralLogics\Helpers::format_currency($data['adminNetAfterEcartpaySpei'] ?? 0) }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{ 'identificación del pedido' }}</th>
            <th>{{ 'Negocio' }}</th>
            <th>{{ 'nombre del cliente' }}</th>
            <th>{{ 'importe total del artículo' }}</th>
            <th>{{ 'descuento del artículo' }}</th>
            <th>{{ 'cupón de descuento' }}</th>
            <th>{{ 'descuento por referencia' }}</th>
            <th>{{ 'cantidad descontada' }}</th>
            <th>{{ 'iva/impuesto' }}</th>
            <th>{{ 'cargo de entrega' }}</th>
            <th>{{ 'monto del pedido' }}</th>
            <th>{{ 'descuento de administrador' }}</th>
            <th>{{ 'descuento de tienda' }}</th>
            <th>{{ 'comisión administrativa' }}</th>
            <th>{{ \App\CentralLogics\Helpers::get_business_data('additional_charge_name')??'cargo adicional' }}</th>
            <th>{{ 'cantidad de embalaje adicional' }}</th>
            <th>{{ 'comisión sobre el cargo de entrega' }}</th>
            <th>{{ 'ingresos netos administrativos' }}</th>
            <th>{{ 'ingresos netos de la tienda' }}</th>
            <th>{{ 'cantidad recibida por' }}</th>
            <th>{{ 'método de pago' }}</th>
            <th>{{ 'estado de pago' }}</th>
        </thead>
        <tbody>
        @foreach($data['order_transactions'] as $key => $ot)
            <tr>
                <td>{{ $key+1}}</td>
                <td>{{ $ot->order_id }}</td>
                <td>
                    @if($ot->order->store)
                        {{Str::limit($ot->order->store->name,25,'...')}}
                    @else
                        {{ 'orden de paquete' }}
                    @endif
                </td>
                <td>
                    @if ($ot->order->customer)
                        {{  $ot->order->customer['f_name'] . ' ' . $ot->order->customer['l_name']  }}
                    @else
                        {{ 'extraviado' }}
                    @endif
                </td>
                {{-- total_item_amount --}}
                <td>{{ \App\CentralLogics\Helpers::format_currency($ot->order['order_amount'] - $ot->additional_charge - $ot->order['dm_tips']-$ot->order['delivery_charge'] - $ot['tax'] + $ot->order['coupon_discount_amount'] + $ot->order['store_discount_amount']   +$ot->order['flash_admin_discount_amount'] + $ot->order['flash_store_discount_amount'] + $ot->order['ref_bonus_amount'] - $ot->order['extra_packaging_amount']) }}</td>


                {{-- item_discount --}}
                <td>{{ \App\CentralLogics\Helpers::format_currency($ot->order->details()->sum(DB::raw('discount_on_item * quantity')) + $ot->order['flash_admin_discount_amount'] +$ot->order['flash_store_discount_amount']) }}</td>

                <td>{{ \App\CentralLogics\Helpers::format_currency($ot->order['coupon_discount_amount']) }}</td>
                <td>{{ \App\CentralLogics\Helpers::format_currency($ot->order['ref_bonus_amount']) }}</td>
                {{-- discounted_amount --}}
                <td>  {{ \App\CentralLogics\Helpers::number_format_short($ot->order['coupon_discount_amount'] + $ot->order['store_discount_amount']+$ot->order['flash_store_discount_amount']+$ot->order['flash_admin_discount_amount'] +$ot->order['ref_bonus_amount']) }}</td>

                <td>{{ \App\CentralLogics\Helpers::format_currency($ot->tax) }}</td>
                <td>{{ \App\CentralLogics\Helpers::format_currency($ot->delivery_charge) }}</td>
                <td>{{ \App\CentralLogics\Helpers::format_currency($ot->order_amount) }}</td>
                {{-- admin_discount --}}
                <td>{{ \App\CentralLogics\Helpers::format_currency($ot->admin_expense) }}</td>
                {{-- store_discount --}}
                <td>{{ \App\CentralLogics\Helpers::format_currency($ot->discount_amount_by_store+$ot->order['flash_store_discount_amount']) }}</td>
                {{-- admin_commission --}}
                <td>{{ \App\CentralLogics\Helpers::format_currency(($ot->admin_commission + $ot->admin_expense) - $ot->delivery_fee_comission -$ot->additional_charge - $ot->order['flash_admin_discount_amount']) }}</td>

                <td>{{ \App\CentralLogics\Helpers::format_currency(($ot->additional_charge)) }}</td>
                <td>{{ \App\CentralLogics\Helpers::format_currency(($ot->extra_packaging_amount)) }}</td>
                <td>{{ \App\CentralLogics\Helpers::format_currency($ot->delivery_fee_comission) }}</td>
                {{-- admin_net_income --}}
                <td>{{ \App\CentralLogics\Helpers::format_currency(($ot->admin_commission  - $ot->order['flash_admin_discount_amount'])) }}</td>
                <td>{{ \App\CentralLogics\Helpers::format_currency($ot->store_amount -($ot?->order?->order_type == 'parcel' ? 0: $ot->tax)) }}</td>
                @if ($ot->received_by == 'admin')
                    <td>{{ 'administración' }}</td>
                @elseif ($ot->received_by == 'deliveryman')
                    <td>
                        <div>{{ 'Repartidor' }}</div>
                        <div>
                            @if (isset($ot->delivery_man) && $ot->delivery_man->earning == 1)
                                {{'independiente'}}
                            @elseif (isset($ot->delivery_man) && $ot->delivery_man->earning == 0 && $ot->delivery_man->type == 'restaurant_wise')
                                {{'restaurante'}}
                            @elseif (isset($ot->delivery_man) && $ot->delivery_man->earning == 0 && $ot->delivery_man->type == 'zone_wise')
                                {{'administración'}}
                            @endif
                        </div>
                    </td>
                @elseif ($ot->received_by == 'store')
                    <td>{{ 'Negocio' }}</td>
                @endif
                <td>
                        {{ translate(str_replace('_', ' ', $ot->order['payment_method'])) }}
                </td>
                <td>
                    @if ($ot->status)
                        {{'Reembolsado'}}
                    @else
                        {{'terminado'}}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
