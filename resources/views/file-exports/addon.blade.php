<div class="row">
    <div class="col-lg-12 text-center ">
        <h1> {{ 'Lista de complementos' }}
        </h1>
    </div>
    <div class="col-lg-12">

        <table>
            <thead>
                <tr>
                    <th>{{ 'Criterios de filtrado' }}</th>
                    <th></th>
                    <th>
                        {{ 'Almacenar' }}: {{ $data['store'] ?? 'N / A' }}
                        <br>
                        {{ 'Contenido de la barra de búsqueda' }}: {{ $data['search'] ?? 'N / A' }}

                    </th>
                    <th> </th>
                </tr>


                <tr>
                    <th>{{ 'SL' }}</th>
                    <th>{{ 'Nombre del complemento' }}</th>
                    <th>{{ 'Precio' }}</th>
                    <th>{{ 'Nombre de la tienda' }}</th>


                    @if ($data['productWiseTax'])
                        <th class="border-0 w--1">{{ 'IVA/Impuesto' }}</th>
                    @endif

            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $addon)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $addon->name }}</td>
                        <td>
                            {{ \App\CentralLogics\Helpers::format_currency($addon->price) }}
                        </td>
                        <td>{{ $addon?->store?->name ?? 'N / A' }}</td>


                        @if ($data['productWiseTax'])
                            <td>
                                <span class="d-block font-size-sm text-body">

                                    @forelse ($addon?->taxVats?->pluck('tax.name', 'tax.tax_rate')->toArray() as $key => $item)
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

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
