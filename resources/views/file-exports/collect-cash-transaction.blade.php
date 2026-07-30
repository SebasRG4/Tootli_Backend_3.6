<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'cobrar transacciones en efectivo' }}</h1></div>
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
                <th>{{ 'tiempo de transacción' }}</th>
                <th>{{ 'cantidad cobrada' }}</th>
                <th>{{ 'recogido de' }}</th>
                <th>{{ 'tipo de usuario' }}</th>
                <th>{{ 'teléfono' }}</th>
                <th>{{ 'correo electrónico' }}</th>
                <th>{{ 'método de pago' }}</th>
                <th>{{ 'referencias' }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['account_transactions'] as $key => $at)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{$at->id}}</td>
                <td>{{$at->created_at->format('Y-m-d '.config('timeformat'))}}</td>
                <td>{{$at['amount']}}</td>
                <td>
                    @if($at->store)
                    {{ $at->store->name}}
                    @elseif($at->deliveryman)
                    {{ $at->deliveryman->f_name }} {{ $at->deliveryman->l_name }}
                    @else
                        {{'extraviado'}}
                    @endif
                </td>
                <td>{{translate($at['from_type'])}}</td>
                <td>
                    @if($at->store)
                    {{ $at->store->phone}}
                    @elseif($at->deliveryman)
                    {{ $at->deliveryman->phone }}
                    @else
                        {{'extraviado'}}
                    @endif
                </td>
                <td>
                    @if($at->store)
                    {{ $at->store->email}}
                    @elseif($at->deliveryman)
                    {{ $at->deliveryman->email }}
                    @else
                        {{'extraviado'}}
                    @endif
                </td>
                <td>{{translate($at->method)}}</td>
                <td>{{$at['ref']}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
