<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'transacciones de retiro de tienda' }}</h1></div>
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
                <th>{{ 'nombre de la tienda' }}</th>
                <th>{{ 'nombre del propietario' }}</th>
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
                    @if($wr->vendor)
                    {{ $wr->vendor->stores[0]->name }}
                    @else
                    {{'tienda eliminada!' }}
                    @endif
                </td>
                <td>{{$wr->vendor->f_name}} {{$wr->vendor->l_name}}</td>
                <td>{{$wr->vendor->phone}}</td>
                <td>{{$wr->vendor->email}}</td>
                <td>{{$wr->vendor && $wr->vendor->account_no ? $wr->vendor->account_no : 'No Data found'}}</td>
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
