
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{'Lista de zonas'}}
    </h1></div>
    <div class="col-lg-12">

    <table>
        <thead>
            <tr>
                <th>{{ 'Criterios de filtrado' }}</th>
                <th></th>
                <th>

                    {{ 'Contenido de la barra de búsqueda'  }}: {{ $data['search'] ?? 'N / A' }}

                </th>
                <th> </th>
                </tr>


        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{ 'Nombre de zona' }}</th>
            <th>{{ 'ID de zona' }}</th>
            <th>{{ 'Tiendas totales' }}</th>
            <th>{{ 'Total de repartidores' }}</th>
            <th>{{ 'Pago Digital' }}</th>
            <th>{{ 'Contra reembolso' }}</th>
            <th>{{ 'Estado' }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $addon)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $addon->name }}</td>
        <td>{{ $addon->id }}</td>
        <td>
            {{ $addon->stores_count }}
        </td>
        <td>

            {{ $addon->deliverymen_count }}
        </td>
        <td>{{ $addon?->digital_payment == 1 ? 'Sí' : 'No' }}</td>
        <td>{{ $addon?->cash_on_delivery == 1 ? 'Sí' : 'No' }}</td>
        <td>{{ $addon?->status == 1 ? 'Activo' : 'Inactivo' }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
