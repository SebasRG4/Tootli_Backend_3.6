
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{'Lista de módulos'}}
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
            <th>{{ 'nombre' }}</th>
            <th>{{ 'identificación del módulo' }}</th>
            <th>{{ 'tipo de módulo empresarial' }}</th>
            <th>{{ 'tiendas totales' }}</th>
            <th>{{ 'Estado' }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $addon)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $addon->module_name }}</td>
        <td>{{ $addon->id }}</td>
        <td>
            {{ translate($addon->module_type) }}
        </td>
        <td>
            {{ $addon->stores_count }}
        </td>

        <td>{{ $addon?->status == 1 ? 'Activo' : 'Inactivo' }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
