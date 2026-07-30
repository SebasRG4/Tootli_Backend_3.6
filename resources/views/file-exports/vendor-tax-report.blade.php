<div class="row">
    <div class="col-lg-12 text-center ">
        <h1>{{ 'Informe de IVA del proveedor' }}</h1>
    </div>
    <div class="col-lg-12">



        <table>
            <thead>
                <tr>
                    <th>{{ 'Resumen' }}</th>
                    <th></th>
                    <th></th>
                    <th>

                        @if (isset($data['summary']))
                            {{-- <br>
                            {{ 'pedidos totales' }} - {{ $data['summary']->total_orders ??0 }} --}}
                            <br>
                            {{ 'monto total del pedido' }} - {{ \App\CentralLogics\Helpers::format_currency($data['summary']->total_order_amount ?? 0) }}
                            <br>
                            {{ 'impuesto total' }} - {{\App\CentralLogics\Helpers::format_currency($data['summary']->total_tax ?? 0) }}
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
                    <th class="border-0">{{ 'identificación del pedido' }}</th>
                    <th class="border-0">{{ 'monto del pedido' }}</th>
                    <th class="border-0">{{ 'tipo de impuesto' }}</th>
                    <th class="border-0">{{ 'monto del impuesto' }}</th>
            </thead>
            <tbody>
                @foreach ($data['orders'] as $key => $order)
                    <tr>
                        <td>
                            {{ $key + 1 }}
                        </td>
                        <td>
                            #{{ $order->id }}
                        </td>
                        <td>
                            {{ \App\CentralLogics\Helpers::format_currency($order->order_amount) }}
                        </td>
                        <td>
                            {{ translate($order?->tax_type ?? 'order_wise') }}
                        </td>
                        <td>
                                        <?php
                                        if ($order?->tax_type == 'category_wise') {
                                            $tax_type = 'category_tax';
                                        } elseif ($order?->tax_type == 'product_wise') {
                                            $tax_type = 'product_tax';
                                        } else {
                                            $tax_type = 'order_wise';
                                        }

                                        $taxLabels = [
                                            'basic' => translate($tax_type),
                                            'tax_on_packaging_charge' => 'Cargo por embalaje',
                                        ];

                                        $groupedByTaxOn = $order->orderTaxes->groupBy('tax_on');
                                        $totalTaxAmount = $order->orderTaxes->sum('tax_amount');
                                        ?>

                                        <div class="d-flex flex-column gap-1">
                                            @if (count($order->orderTaxes) > 0)
                                                <div class="fw-bold">
                                                    {{ 'Impuesto total' }}:
                                                    {{ \App\CentralLogics\Helpers::format_currency($totalTaxAmount) }}
                                                </div>, <br>

                                                @foreach ($groupedByTaxOn as $taxOn => $taxGroup)
                                                    @if (isset($taxLabels[$taxOn]))
                                                        <div class="mt-2 text-capitalize fw-semibold">
                                                            {{ $taxLabels[$taxOn] }}:</div> <br>

                                                        @php

                                                            $taxByName = $taxGroup
                                                                ->groupBy('tax_name')
                                                                ->map(function ($group) {
                                                                    return $group->sum('tax_amount');
                                                                });
                                                        @endphp

                                                        @foreach ($taxByName as $name => $amount)
                                                            <div class="d-flex fz-11 gap-3 align-items-center">
                                                                <span>{{ $name }} :</span>
                                                                <span>{{ \App\CentralLogics\Helpers::format_currency($amount) }}</span>
                                                            </div> <br>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            @else
                                                <div class="d-flex fz-14 gap-3 align-items-center title-clr">
                                                    {{ 'Monto del impuesto:' }} <span>
                                                        {{ \App\CentralLogics\Helpers::format_currency($order->total_tax_amount) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
