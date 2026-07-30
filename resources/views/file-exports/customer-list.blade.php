<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'lista de clientes' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'Análisis de clientes' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'Cliente total'  }}: {{ $data['customers']->count() }}
                    <br>
                    {{ 'Cliente activo'  }}: {{ $data['customers']->where('status',1)->count() }}
                    <br>
                    {{ 'Cliente inactivo'  }}: {{ $data['customers']->where('status',0)->count() }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
            <tr>
                <th>{{ 'Criterios de búsqueda' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'Contenido de la barra de búsqueda'  }}: {{ $data['search'] ??'N / A' }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ 'Criterios de filtrado' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'Estado del cliente'  }}: {{ $data['filter'] ?translate($data['filter']):'todo' }}
                    <br>
                    {{ 'Ordenar por'  }}: {{ $data['order_wise'] ??'N / A' }}
                    <br>
                    {{ 'Mostrar límite'  }}: {{ $data['show_limit'] ??'N / A' }}
                    <br>
                    {{ 'Rango de fechas del pedido'  }}: {{ $data['order_date'] ??'N / A' }}
                    <br>
                    {{ 'Unirse al rango de fechas'  }}: {{ $data['join_date'] ??'N / A' }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{ 'nombre de pila' }}</th>
            <th>{{ 'apellido' }}</th>
            <th>{{ 'teléfono' }}</th>
            <th>{{ 'correo electrónico' }}</th>
            <th>{{ 'dirección guardada' }}</th>
            <th>{{ 'pedidos totales' }}</th>
            <th>{{ 'monto total de la billetera' }} </th>
            <th>{{ 'puntos totales de fidelidad' }} </th>
            <th>{{ 'estado' }} </th>
        </thead>
        <tbody>
        @foreach($data['customers'] as $key => $customer)
            <tr>
        <td>{{ $key+1}}</td>
        <td>{{ $customer['f_name'] }}</td>
        <td>{{ $customer['l_name'] }}</td>
        <td>{{ $customer['phone'] }}</td>
        <td>{{ $customer['email'] }}</td>
        <td>
            @foreach($customer->addresses as $address)
            <br>
            {{$address['address']}}
            @endforeach
        </td>
        <td>{{ $customer['order_count'] }}</td>
        <td>{{ $customer['wallet_balance'] }}</td>
        <td>{{ $customer['loyalty_point'] }}</td>
        <td>{{ $customer->status ? 'Activo' : 'Inactivo' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
