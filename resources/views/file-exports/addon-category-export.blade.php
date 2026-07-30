<div class="row">
    <div class="col-lg-12 text-center ">
        <h1> {{ 'Lista de categorías' }}
        </h1>
    </div>
    <div class="col-lg-12">

        <table>
            <thead>
                <tr>
                    <th>{{ 'Criterios de filtrado' }}</th>
                    <th></th>
                    <th>
                        {{ 'Contenido de la barra de búsqueda' }}: {{ $data['search'] ?? 'N / A' }}

                    </th>
                    <th> </th>
                </tr>


                <tr>
                    <th class="border-0">{{ 'SL' }}</th>
                    <th class="border-0">{{ 'identificación' }}</th>
                    <th class="">{{ 'Nombre de categoría' }}</th>
                    @if ($data['categoryWiseTax'])

                    <th class="border-0 w--1">{{ 'IVA/Impuesto' }}</th>
                    @endif
                    <th class="border-0 text-center">{{ 'estado' }}</th>

            </thead>

            <tbody>
                @foreach ($data['data'] as $key => $category)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $category->id }}</td>
                        <td>
                            {{ Str::limit($category['name'], 20, '...') }}

                        </td>
                        @if ($data['categoryWiseTax'])

                        <td>
                            <span class="d-block font-size-sm text-body">

                                @forelse ($category?->taxVats?->pluck('tax.name', 'tax.tax_rate')->toArray() as $key => $item)
                                    <br>
                                    <span> {{ $item }} : <span class="font-bold">
                                            ({{ $key }}%)
                                        </span> </span>
                                    <br>
                                @empty
                                    <span> {{ 'sin impuestos' }} </span>
                                @endforelse
                            </span>
                        </td>
                        @endif

                        <td>{{ $category->status == 1 ? 'Activo' : 'Inactivo' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
