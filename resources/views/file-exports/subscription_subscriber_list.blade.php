<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'lista de suscriptores' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'criterios de filtrado' }} -</th>
                <th></th>
                <th></th>
                <th>

                    {{ 'zona'}} - {{ $data['zone'] }}


                    <br>
                    {{ 'filtrar'  }}- {{  translate($data['filter']) }}

                    <br>
                    {{ 'Contenido de la barra de búsqueda'  }}- {{ $data['search'] ??'N / A' }}

                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th class="border-top px-4 border-bottom text-center">{{ 'SL' }}</th>
                <th class="border-top px-4 border-bottom"> {{ 'Información de la tienda' }}  </th>
                <th class="border-top px-4 border-bottom"> {{ 'Nombre del paquete actual' }} </th>
                <th class="border-top px-4 border-bottom"> {{ 'Precio del paquete' }}  </th>
                <th class="border-top px-4 border-bottom"> {{ 'Fecha de vencimiento' }}  </th>
                <th class="border-top px-4 border-bottom text-center"> {{ 'Suscripción total utilizada' }}  </th>
                <th class="border-top px-4 border-bottom text-center"> {{ 'es prueba' }}  </th>
                <th class="border-top px-4 border-bottom text-center"> {{ 'es cancelar' }}  </th>
                <th class="border-top px-4 border-bottom text-center">{{ 'Estado' }} </th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['data'] as $k=> $subscriber)
            <tr>

                    <td class=" text-center">{{ $k + 1 }}</td>
                    <td >
                        {{ $subscriber->name }}
                    </td>
                    <td >
                        <div>{{ $subscriber?->store_sub_update_application?->package?->package_name }}</div>
                    </td>
                    <td >
                        <div class="text-title">{{  \App\CentralLogics\Helpers::format_currency($subscriber?->store_sub_update_application?->package?->price) }}</div>
                    </td>
                    <td >
                        <div class="text-title">{{  \App\CentralLogics\Helpers::date_format($subscriber?->store_sub_update_application?->expiry_date_parsed) }}</div>
                    </td>
                    <td >
                        <div class="text-title pl-3">{{ $subscriber?->store_all_sub_trans_count }}</div>
                    </td>
                    <td class="px-4">
                        <div class="text-title pl-3">
                            @if ($subscriber?->store_sub_update_application?->is_trial)
                            <span class="badge badge-pill badge-info">{{  'Sí' }}</span>

                            @else
                            <span class="badge badge-pill badge-success">{{  'No' }}</span>
                            @endif

                    </div>
                    <td class="px-4">
                        <div class="text-title pl-3">
                            @if ($subscriber?->store_sub_update_application?->is_canceled)
                            <span class="badge badge-pill badge-warning">{{  'Sí' }}</span>

                            @else
                            <span class="badge badge-pill badge-success">{{  'No' }}</span>
                            @endif

                    </div>
                    <td class=" text-center">
                        <div>
                            @if($subscriber?->status == 0 &&  $subscriber?->vendor?->status == 0)
                            <span class="badge badge-soft-info">{{ 'Aprobación pendiente' }}</span>
                            {{-- @elseif ($subscriber?->store_sub_update_application?->is_canceled == 1)
                            <span class="badge badge-soft-warning">{{ 'Cancelado' }}</span> --}}
                            @elseif($subscriber?->store_sub_update_application?->status == 0)
                            <span class="badge badge-soft-danger">{{ 'Venció' }}</span>
                            @elseif($subscriber?->store_sub_update_application?->status == 1)
                            <span class="badge badge-soft-success">{{ 'Activo' }}</span>
                            @endif
                        </div>
                    </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
