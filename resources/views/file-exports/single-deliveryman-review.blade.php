<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'lista de revisión del repartidor' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'información del repartidor' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'nombre'  }}- {{ $data['dm']->f_name.' '.$data['dm']->l_name}}
                    <br>
                    {{ 'teléfono'  }}- {{ $data['dm']->phone}}
                    <br>
                    {{ 'correo electrónico'  }}- {{ $data['dm']->email}}
                    <br>
                    {{ 'calificación total'  }}- {{ count($data['dm']->rating)}}
                    <br>
                    {{ 'revisión promedio'  }}- {{count($data['dm']->rating)>0?number_format($data['dm']->rating[0]->average, 1, '.', ' '):0}}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            {{-- <tr>
                <th>{{ 'Criterios de búsqueda' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'Contenido de la barra de búsqueda'  }}- {{ $data['search'] ??'N / A' }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr> --}}
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{'identificación del pedido'}}</th>
            <th>{{'nombre del cliente'}}</th>
            <th>{{'nombre de la tienda'}}</th>
            <th>{{'clasificación'}}</th>
            <th>{{'revisar'}}</th>
        </thead>
        <tbody>
        @foreach($data['reviews'] as $key => $review)
            <tr>
                <td>{{ $key+1}}</td>
                <td>
                    {{ $review->order_id }}
                </td>
                <td>
                    @if ($review->customer)
                        {{$review->customer?$review->customer->f_name:""}} {{$review->customer?$review->customer->l_name:""}}
                    @else
                        {{'cliente no encontrado'}}
                    @endif
                </td>
                <td>
                    {{$review->order?->store?->name}}
                </td>
                <td>{{ $review->rating }}</td>
                <td>{{ $review->comment }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
