
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{'Lista de notificaciones push'}}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
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
            <th>{{ 'SL' }}</th>
            <th>{{ 'Título de la notificación' }}</th>
            <th>{{ 'Creado en' }}</th>
            <th>{{ 'Descripción' }}</th>
            <th>{{ 'Imagen' }}</th>
            <th>{{ 'Zona' }}</th>
            <th>{{ 'Usuarios objetivo' }}</th>
        </thead>
        <tbody>
        @foreach($data['data'] as $key => $coupon)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $coupon->title }}</td>
        <td>{{ \Carbon\Carbon::parse($coupon->created_at)->format('d M Y') }}</td>
        <td>{{ $coupon->description }}</td>
            <td></td>
        {{-- <td>{{ $coupon->image ?? 'N / A' }}</td> --}}
        <td>{{ $coupon?->zone?->name ??  'Todo' }}</td>

        <td>{{ translate($coupon->tergat) }}</td>


            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
