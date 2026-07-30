
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{'Lista de atributos'}}
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
            <th>{{ 'Nombre del atributo' }}</th>
            <th>{{ 'IDENTIFICACIÓN' }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $attribute)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $attribute->name }}</td>
        <td>{{ $attribute->id }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
