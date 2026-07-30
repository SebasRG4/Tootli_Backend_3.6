<div class="row">
    <div class="col-lg-12 text-center ">
        <h1>{{ 'Informes de IVA del proveedor' }}</h1>
    </div>
    <div class="col-lg-12">



        <table>
            <thead>
                <tr>
                    <th>{{ 'Criterios de búsqueda' }}</th>
                    <th></th>
                    <th></th>
                    <th>

                        @if (isset($data['summary']))
                            <br>
                            {{ 'pedidos totales' }} - {{  \App\CentralLogics\Helpers::format_currency($data['summary']->total_orders ??0) }}
                            <br>
                            {{ 'monto total del pedido' }} - {{  \App\CentralLogics\Helpers::format_currency($data['summary']->total_order_amount ??0) }}
                            <br>
                            {{ 'impuesto total' }} - {{  \App\CentralLogics\Helpers::format_currency($data['summary']->total_tax ??0) }}
                        @endif
                        @if ($data['from'])
                            <br>
                            {{ 'de' }} -
                            {{ $data['from'] ? Carbon\Carbon::parse($data['from'])->format('d M Y') : '' }}
                        @endif
                        @if ($data['to'])
                            <br>
                            {{ 'a' }} -
                            {{ $data['to'] ? Carbon\Carbon::parse($data['to'])->format('d M Y') : '' }}
                        @endif
                        <br>

                        {{ 'Contenido de la barra de búsqueda' }}- {{ $data['search'] ?? 'N / A' }}
                        <br>

                    </th>
                    <th> </th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th class="border-0">{{ 'SL' }}</th>
                    <th class="border-0">{{ 'Información del proveedor' }}</th>
                    <th class="border-0">{{ 'Orden total' }}</th>
                    <th class="border-0">{{ 'Monto total del pedido' }}</th>
                    <th class="border-0">{{ 'Monto del impuesto' }}</th>
            </thead>
            <tbody>
                @foreach ($data['stores'] as $key => $store)
                    <tr>
                        <td>
                            {{ $key +1 }}
                        </td>
                        <td>
                            <span class="fz-14 title-clr">
                                {{ $store->store_name }}
                                <span class="fz-11 d-block">{{ $store->store_phone }}</span>
                            </span>
                        </td>
                        <td>
                            {{ $store->total_orders }}
                        </td>
                        <td>
                            {{ \App\CentralLogics\Helpers::format_currency($store->total_order_amount) }}
                        </td>
                         <td>
                                        @php($sum_tax_amount=collect($store->tax_data)->sum('total_tax_amount'))

                                        <div class="d-flex flex-column gap-1">
                                            @if ($store->store_total_tax_amount - $sum_tax_amount > 0)
                                            <div class="d-flex fz-14 gap-3 align-items-center title-clr">
                                              {{ 'Impuesto total:' }} <span>
                                                    {{ \App\CentralLogics\Helpers::format_currency($store->store_total_tax_amount - $sum_tax_amount) }}</span>
                                            </div> <br>
                                            @endif
                                            @if ($sum_tax_amount > 0 )
                                            <div class="d-flex fz-14 gap-3 align-items-center title-clr">
                                                {{ 'Suma de Impuestos:' }} <span>
                                                    {{ \App\CentralLogics\Helpers::format_currency($sum_tax_amount) }}</span>
                                            </div><br>
                                            @foreach ($store->tax_data as $tax)
                                                <div class="d-flex fz-11 gap-3 align-items-center">
                                                    {{ $tax['tax_name'] }}:
                                                    <span>{{ \App\CentralLogics\Helpers::format_currency($tax['total_tax_amount']) }}
                                                    </span>
                                                </div> <br>
                                            @endforeach

                                            @endif
                                        </div>
                                    </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
