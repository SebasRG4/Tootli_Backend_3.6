<div class="row">
    <div class="col-lg-12 text-center ">
        <h1> {{ 'Lista de motivos de cancelación de paquetes' }}
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
                    <th class="fs-14 text-title font-semibold top-border-table">
                        {{ 'SL' }}
                    </th>
                    <th class="fs-14 text-title font-semibold top-border-table">
                        {{ 'razón' }}
                    </th>
                    <th class="fs-14 text-title font-semibold top-border-table">
                        {{ 'tipo de cancelación' }}
                    </th>
                    <th class="fs-14 text-title font-semibold top-border-table">
                        {{ 'tipo de usuario' }}
                    </th>
                    <th class="fs-14 text-title font-semibold top-border-table">
                        {{ 'estado' }}
                    </th>

                </tr>

            </thead>
            <tbody>
                @foreach ($data['data'] as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td class="p-3">
                            <div class="max-w-700 fs-14 title-clr font-medium min-w-140">
                                {{ Str::limit($item->reason, 25, '...') }}
                            </div>
                        </td>
                        <td class="p-3 fs-14 title-clr font-medium min-w-140">
                            {{ translate($item->cancellation_type) }}</td>
                        <td class="p-3 fs-14 title-clr font-regular min-w-140">{{ translate($item->user_type) }}
                        </td>
                        <td class="p-3">
                            @if ($item->status == 1)
                                <span class="badge badge-soft-success fs-12">{{ 'Activo' }}</span>
                            @else
                                <span class="badge badge-soft-danger fs-12">{{ 'Inactivo' }}</span>
                            @endif
                        </td>

                    </tr>
                @endforeach



            </tbody>
        </table>
    </div>
</div>
