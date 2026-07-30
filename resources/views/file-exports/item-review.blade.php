
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{'Lista de revisión'}}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ 'Criterios de filtrado' }}</th>
                <th></th>
                <th>
                    {{ 'Almacenar'  }}: {{ $data['store'] ?? 'Todo' }}
                    <br>

                    @if (isset($data['category']) )
                    {{ 'Categoría'  }}: {{ $data['category'] ?? 'Todo' }}
                    <br>
                    @endif

                    {{ 'Reseñas totales'  }}: {{ $data['data']->count() ?? 'Todo' }}
                    <br>
                    {{ 'Contenido de la barra de búsqueda'  }}: {{ $data['search'] ?? 'N / A' }}

                </th>
                <th> </th>
                </tr>


        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{ 'Nombre del artículo' }}</th>
            <th>{{ 'ID de pedido' }}</th>
            <th>{{ 'Nombre del cliente' }}</th>
            <th>{{ 'Nombre de la tienda' }}</th>
            <th>{{ 'Clasificación' }}</th>
            <th>{{ 'Revisar' }}</th>
            <th>{{ 'Estado' }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $review)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $review?->item?->name }}</td>
        <td> {{$review->order_id}}</td>
        <td>
            {{ $review?->customer ?  $review?->customer?->f_name .' '.$review?->customer?->l_name  : 'Cliente no encontrado'}}
        </td>
        <td>{{ $review?->item?->store?->name ?? 'tienda eliminada' }}</td>
        <td> {{$review->rating}}</td>
        <td>{{$review->comment}}</td>
        <td>{{ $review->status == 1 ? 'activo' : 'inactivo' }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
