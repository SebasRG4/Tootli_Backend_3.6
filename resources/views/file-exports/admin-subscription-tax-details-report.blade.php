<div class="row">
    <div class="col-lg-12 text-center ">
        <h1>{{ translate($data['taxSource']) }} {{ 'Informe de detalles de impuestos' }}</h1>
    </div>
    <div class="col-lg-12">



        <table>
            <thead>
                <tr>
                    <th>{{ 'Criterios de búsqueda' }}</th>
                    <th></th>
                    <th></th>
                    <th>

                        
                           <br>
                        {{ 'monto total del impuesto' }} - {{\App\CentralLogics\Helpers::format_currency($data['total_tax_amount']) ?? 0 }}
                        <br>
                        {{ 'cantidad total' }} - {{ \App\CentralLogics\Helpers::format_currency($data['total_amount']) }}

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

                        {{-- {{ 'Contenido de la barra de búsqueda' }}- {{ $data['search'] ?? 'N / A' }} --}}
                        <br>

                    </th>
                    <th> </th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th class="border-0">{{ 'SL' }}</th>
                    <th class="border-0">{{ 'ID de transacción' }}</th>
                    <th class="border-0">{{ 'Cantidad' }}</th>
                    <th class="border-0">{{ 'Monto del impuesto' }}</th>
            </thead>
            <tbody>
                @foreach ($data['taxData'] as $key => $item)
                    <tr>
                        <td>
                            {{ $key + 1 }}
                        </td>
                        <td>
                            {{ $item->id }}
                        </td>
                        <td>
                            {{ \App\CentralLogics\Helpers::format_currency($item->paid_amount) }}
                        </td>

                        <td>
                            @php
                                $taxSummary = collect($item['calculated_taxes']);
                                $totalTaxRate = $taxSummary->sum('tax_rate');
                                $totalTaxAmount = $taxSummary->sum('tax_amount');
                            @endphp

                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex fz-14 gap-3 align-items-center title-clr">
                                    {{ 'Total' }} ({{ $totalTaxRate }}%):
                                    <span>
                                        {{ \App\CentralLogics\Helpers::format_currency($totalTaxAmount) }}</span>
                                </div>,
                                <br>
                                @foreach ($item['calculated_taxes'] as $taxItems)
                                    <div class="d-flex fz-11 gap-3 align-items-center">
                                        {{ $taxItems['tax_name'] }} ({{ $taxItems['tax_rate'] }}%) :
                                        <span>{{ \App\CentralLogics\Helpers::format_currency($taxItems['tax_amount']) }}</span>
                                    </div>, <br>
                                @endforeach

                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
