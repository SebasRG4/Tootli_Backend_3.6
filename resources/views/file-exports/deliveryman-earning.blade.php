<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'lista de ganancias del repartidor' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'información del repartidor' }}</th>
                <th></th>
                <th>
                    {{ 'nombre'  }}- {{ $data['dm']->f_name.' '.$data['dm']->l_name}}
                    <br>
                    {{ 'teléfono'  }}- {{ $data['dm']->phone}}
                    <br>
                    {{ 'correo electrónico'  }}- {{ $data['dm']->email}}
                    <br>
                    {{ 'orden total'  }}- {{ $data['dm']->order_count }}
                    <br>
                    {{ 'ganancia total'  }}- {{$data['dm']->wallet->total_earning}}

                </th>
                <th></th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ 'Criterios de filtrado' }}</th>
                <th></th>
                <th>
                    {{ 'fecha'  }}- {{ $data['date'] ??'N / A' }}

                </th>
                <th></th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{'identificación del pedido'}}</th>
            <th>{{'fecha'}}</th>
            {{-- <th>{{'distancia'}}</th> --}}
            <th>{{'tarifa de entrega ganada'}}</th>
            <th>{{'consejos'}}</th>
            <th>{{'ganancia total'}}</th>
        </thead>
        <tbody>
        @foreach($data['earnings'] as $key => $earning)
            <tr>
                <td>{{ $key+1}}</td>
                <td>
                    {{ $earning->order_id }}
                </td>
                <td>
                    {{ \App\CentralLogics\Helpers::date_format($earning->created_at ) }}
                </td>
                {{-- <td>
                    {{ $earning->order->distance }} km
                </td> --}}
                <td>{{ \App\CentralLogics\Helpers::format_currency($earning->original_delivery_charge) }}</td>
                <td>{{ \App\CentralLogics\Helpers::format_currency($earning->dm_tips) }}</td>
                <td>{{ \App\CentralLogics\Helpers::format_currency($earning->original_delivery_charge + $earning->dm_tips) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
