<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'lista de revisión del repartidor' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
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
                </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{'nombre del repartidor'}}</th>
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
                <td>{{$review->delivery_man->f_name.' '.$review->delivery_man->l_name}}</td>
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
