
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{'Lista de revisión inteligente de la tienda'}}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ 'Detalles de la tienda' }}</th>
                <th></th>
                <th>
                    {{ 'Nombre de la tienda'  }}- {{ $data['store_name'] ?? 'Todo' }}
                    <br>
                    {{ 'ID de tienda'  }}- {{ $data['store_id'] ?? 'Todo' }}
                    <br>

                    {{ 'Clasificación'  }}- {{ $data['rating']?? 'Todo' }}
                    <br>
                    {{ 'Reseñas'  }}- {{ $data['total_reviews'] ?? 'Todo' }}
                </th>
                <th> </th>
                </tr>


        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{'ID de revisión'}}</th>
            <th>{{ 'Nombre del artículo' }}</th>
            <th>{{ 'ID de pedido' }}</th>
            <th>{{ 'Nombre del cliente' }}</th>
            <th>{{ 'Clasificación' }}</th>
            <th>{{ 'Revisar' }}</th>
            <th >{{'respuesta de la tienda'}}</th>
            <th>{{ 'Estado' }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $review)

            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{$review?->review_id}}</td>
        <td>{{ $review?->item?->name }}</td>
        <td> {{$review->order_id}}</td>
        <td>
            {{$review?->customer ? $review?->customer?->f_name .' '.$review?->customer?->l_name  : 'Cliente no encontrado'}}
        </td>
        <td> {{$review->rating}}</td>
        <td>{{$review->comment}}</td>
        <td>{{ $review?->reply ?? 'no dado' }}</td>
        <td>{{ $review->status == 1 ? 'activo' : 'inactivo' }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
