
<div class="row">
    <div class="col-lg-12 text-center "><h1 > {{'Lista de categorías'}}
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
            <th>{{ 'ID de categoría' }}</th>
            <th>{{ 'Categoría principal' }}</th>
            <th>{{ 'Subcategoría' }}</th>

        </thead>
        <tbody>
        @foreach($data['data'] as $key => $category)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $category->id }}</td>
        <td> {{$category->parent?$category->parent['name']:'categoría eliminada'}}
            <td>{{ $category->name }}</td>


            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
