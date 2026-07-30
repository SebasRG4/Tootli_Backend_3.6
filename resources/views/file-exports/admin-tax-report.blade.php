<div class="row">
    <div class="col-lg-12 text-center ">
        <h1> {{ 'Informe de impuestos administrativo' }}</h1>
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
                    <th class="border-0">{{ 'Fuente de ingresos' }}</th>
                    <th class="border-0">{{ 'Ingresos totales' }}</th>
                    <th class="border-0">{{ 'Impuesto total' }}</th>
            </thead>
            <tbody>
                @php
                    $count = 1;
                @endphp
                @foreach ($data['taxData'] as $key => $item)
                    <tr>
                        <td>
                            {{ $count++ }}

                        </td>
                        <td>
                            {{ translate($key) }}
                        </td>
                        <td>

                            {{ \App\CentralLogics\Helpers::format_currency($item['total_base_amount']) }}
                        </td>
                        <td>
                            @php
                                $totalTaxAmount = collect($item['taxes'] ?? [])
                                    ->flatten(1)
                                    ->sum('total_tax_amount');
                                $totalTax = collect($item['taxes'] ?? [])
                                    ->flatten(1)
                                    ->sum('tax_rate');
                            @endphp
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex fz-14 gap-3 align-items-center title-clr">
                                    {{ 'Total' }} ({{ $totalTax }}%): <span>
                                        {{ \App\CentralLogics\Helpers::format_currency($totalTaxAmount) }}</span>
                                </div>,<br>

                                @foreach ($item['taxes'] as $taxName => $taxItems)
                                    @foreach ($taxItems as $tax)
                                        <div class="d-flex fz-11 gap-3 align-items-center">
                                            {{ $taxName }} ({{ $tax['tax_rate'] }}%) :
                                            <span>{{ \App\CentralLogics\Helpers::format_currency($tax['total_tax_amount']) }}</span>
                                        </div>,<br>
                                    @endforeach
                                @endforeach

                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
