<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'repartidor retirar transacciones' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'criterios de filtrado' }} -</th>
                <th></th>
                <th></th>
                <th> 
                    {{ 'estado de la solicitud'  }}- {{  $data['request_status']?translate($data['request_status']):'todo' }}
                    <br>
                    {{ 'Contenido de la barra de búsqueda'  }}- {{ $data['search'] ??'N / A' }}

                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ 'SL' }}</th>
                <th>{{ 'tiempo de solicitud' }}</th>
                <th>{{ 'cantidad solicitada' }}</th>
                <th>{{ 'nombre del repartidor' }}</th>
                <th>{{ 'teléfono' }}</th>
                <th>{{ 'correo electrónico' }}</th>
                <th>{{ 'número de cuenta bancaria' }}</th>
                <th>{{ 'estado de la solicitud' }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['withdraw_requests'] as $key => $wr)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{date('Y-m-d '.config('timeformat'),strtotime($wr->created_at))}}</td>
                <td>{{$wr['amount']}}</td>
                <td>
                    @if($wr->deliveryman)
                    {{ $wr->deliveryman->f_name }} {{ $wr->deliveryman->l_name }}
                    @else
                    {{'repartidor eliminado!' }}
                    @endif
                </td>
                <td>{{$wr->deliveryman->phone}}</td>
                <td>{{$wr->deliveryman->email}}</td>
                <td>{{$wr->deliveryman && $wr->deliveryman->account_no ? $wr->deliveryman->account_no : 'No Data found'}}</td>
                <td>
                    @if($wr->approved==0)
                        {{ 'Pendiente' }}
                    @elseif($wr->approved==1)
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
