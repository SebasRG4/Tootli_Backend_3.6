<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'lista de repartidor' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'Criterios de búsqueda' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'zona'}} - {{ $data['zone']??'todo' }}
                    <br>
                    {{ 'Contenido de la barra de búsqueda'  }}- {{ $data['search'] ??'N / A' }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
            <tr>
                <th>{{ 'Analítica' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'repartidor total'  }}- {{ $data['delivery_men']->count() }}
                    <br>
                    {{ 'repartidor activo'  }}- {{ $data['delivery_men']->where('status',1)->count()}}
                    <br>
                    {{ 'repartidor inactivo'  }}- {{ $data['delivery_men']->where('status',0)->count() }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{'imagen'}}</th>
            <th>{{ 'nombre de pila' }}</th>
            <th>{{ 'apellido' }}</th>
            <th>{{ 'teléfono' }}</th>
            <th>{{ 'correo electrónico' }}</th>
            <th>{{ 'tipo de repartidor' }}</th>
            <th>{{ 'total completado' }}</th>
            <th>{{ 'pedidos en ejecución totales' }}</th>
            <th>{{ 'estado' }}</th>
            <th>{{ 'zona' }}</th>
            <th>{{ 'tipo de vehículo' }}</th>
            <th>{{ 'tipo de identidad' }}</th>
            <th>{{ 'numero de identidad' }}</th>
        </thead>
        <tbody>
        @foreach($data['delivery_men'] as $key => $item)
        <tr>
            <td>{{$key+1}}</td>
            <td></td>
            <td>{{  $item['f_name']  }}</td>
            <td>{{  $item['l_name']  }}</td>
            <td>{{  $item['phone']  }}</td>
            <td>{{  $item['email']  }}</td>
            <td>{{ $item->earning?'persona de libre dedicación':'basado en salario' }}</td>
            <td>{{ $item['order_count'] }}</td>
            <td>{{ $item['current_orders'] }}</td>
            <td>{{ $item->active?'en línea':'desconectado' }}</td>
            <td>{{ $item->zone?$item->zone->name:'' }}</td>
            <td>{{ $item->vehicle?$item->vehicle->type:'' }}</td>
            <td>{{ translate($item->identity_type) }}</td>
            <td>{{ $item->identity_number }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
