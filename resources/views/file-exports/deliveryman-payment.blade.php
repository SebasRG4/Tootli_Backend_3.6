<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'pagos del repartidor' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'criterios de filtrado' }} -</th>
                <th></th>
                <th></th>
                <th>

                    {{ 'Contenido de la barra de búsqueda'  }}- {{ $data['search'] ??'N / A' }}

                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ 'SL' }}</th>
                <th>{{ 'identificación de transacción' }}</th>
                <th>{{ 'proporcionado st' }}</th>
                <th>{{ 'monto del pago' }}</th>
                <th>{{ 'nombre del repartidor' }}</th>
                <th>{{ 'teléfono' }}</th>
                <th>{{ 'método de pago' }}</th>
                <th>{{ 'referencias' }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['dm_earnings'] as $key => $at)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{$at->id}}</td>
                <td>{{$at->created_at->format('Y-m-d '.config('timeformat'))}}</td>
                <td>{{$at['amount']}}</td>
                <td>
                    @if($at->delivery_man)
                    {{$at->delivery_man->f_name.' '.$at->delivery_man->l_name}}
                    @else
                    {{'repartidor eliminado'}}
                    @endif
                </td>
                <td>
                    @if($at->delivery_man)
                    {{$at->delivery_man->phone}}
                    @else
                    {{'repartidor eliminado'}}
                    @endif
                </td>
                <td>{{translate($at->method)}}</td>
                @if(  $at['ref'] == 'delivery_man_wallet_adjustment_full')
                    <td>{{ 'billetera ajustada' }}</td>
                @elseif( $at['ref'] == 'delivery_man_wallet_adjustment_partial')
                    <td>{{ 'cartera ajustada parcialmente' }}</td>
                @else
                    <td>{{$at['ref']}}</td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
