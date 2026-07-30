
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{translate($data['is_provider'] ? 'provider_Withdraw_Transactions' : 'Store_Withdraw_Transactions')}}
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
            <th>{{ 'Solicitud creada en' }}</th>
            <th>{{ 'Monto solicitado' }}</th>
            <th>{{ 'Estado' }}</th>
        </thead>
        <tbody>
        @foreach($data['data'] as $key => $tr)
            <tr>
                <td>{{ $loop->index+1}}</td>
                <td>{{ $tr?->created_at->format('Y-m-d '.config('timeformat')) ??  'N / A' }}</td>
                <td> {{ \App\CentralLogics\Helpers::format_currency($tr->amount) }}</td>
                <td>
                    @if($tr->approved==0)
                    {{ 'Pendiente' }}
                    @elseif($tr->approved==1)
                    {{ 'aprobado' }}
                    @else
                    {{ 'denegado' }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
