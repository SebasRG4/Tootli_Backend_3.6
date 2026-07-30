
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{'lista de paquetes de suscripción'}}
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
            <th>{{ 'Nombre del paquete' }}</th>
            <th>{{ 'Precio' }}</th>
            <th>{{ 'Duración' }}</th>
            <th>{{ 'Suscriptor actual' }}</th>
            <th>{{ 'Estado' }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $package)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $package->package_name }}</td>
        <td>
            {{ \App\CentralLogics\Helpers::format_currency($package->price) }}
        </td>
        <td>{{$package->validity}} {{ 'días' }}</td>
        <td>{{$package->current_subscribers_count ?? 0}}</td>
        <td>{{$package->status == 1 ? 'Activar' : 'Inactivar' }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
