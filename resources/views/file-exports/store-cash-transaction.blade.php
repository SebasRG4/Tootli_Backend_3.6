
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{translate($data['is_provider'] ? 'provider_Cash_Transactions' : 'Store_Cash_Transactions')}}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ 'Criterios de filtrado' }}</th>
                <th></th>
                <th>
                    {{ 'Contenido de la barra de búsqueda'  }}: {{ $data['search'] ?? 'N / A' }}
                </th>
                <th> </th>
                </tr>


        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{ 'ID de transacción' }}</th>
            <th>{{ 'Tiempo de transacción' }}</th>
            <th>{{ 'Saldo antes de la transacción' }}</th>
            <th>{{ 'Monto de la transacción' }}</th>
            <th>{{ 'Referencia' }}</th>
            <th>{{ 'Método de pago' }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $tr)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $tr->id }}</td>
        <td>{{ $tr?->created_at->format('Y-m-d '.config('timeformat')) ??  'N / A' }}</td>
        <td>
            {{ \App\CentralLogics\Helpers::format_currency($tr->current_balance) }}
        </td>
        <td>
            {{ \App\CentralLogics\Helpers::format_currency($tr->amount) }}
        </td>
        <td>{{ $tr->ref ??  'N / A' }}</td>
        <td>{{ $tr->method ??  'N / A' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
