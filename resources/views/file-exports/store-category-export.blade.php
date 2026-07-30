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
                    <th>{{ 'SL' }}</th>
                    <th>{{ 'Nombre de categoría' }}</th>
                    <th>{{ 'ID de categoría' }}</th>
                    {{-- <th>{{ 'Módulo' }}</th> --}}
                    <th>{{ 'prioridad' }}</th>
                    @if ($data['categoryWiseTax'])
                        <th class="border-0 w--1">{{ 'IVA/Impuesto' }}</th>
                    @endif
                    <th>{{ 'Estado' }}</th>

            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $category)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->id }}</td>
                        {{-- <td>{{ $category?->module?->module_name }}</td> --}}
                        @php
                            $return_value = match ($category->priority) {
                                0 => 'normal',
                                1 => 'medio',
                                2 => 'alto',
                            };
                        @endphp
                        <td>{{ $return_value }}</td>
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
