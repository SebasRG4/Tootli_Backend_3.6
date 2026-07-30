@php
    $isRental = $data['is_rental'] == 1 ? 'Provider' : 'Store';
    $isVehicle = $data['is_rental'] == 1 ? 'vehicle' : 'item';
    $isTrip = $data['is_rental'] == 1 ? 'trip' : 'order';
@endphp
<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ translate($isRental.'_List') }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>




        <tr>

            <th>{{ 'Total \'.$isRental) }} - {{ $datos[\'datos\']->count() ?? traducir(\'N/A' }} </th>
            <th></th>
            <th></th>
            <th> {{ 'Activo \'.$isRental) }} - {{ $data[\'data\']->where(\'status\',1)->count() ?? traducir(\'N/A' }} </th>
            <th></th>
            <th></th>
            <th> {{ 'Inactivo \'.$isRental) }} - {{ $data[\'data\']->where(\'status\',0)->count() ?? traducir(\'N/A' }} </th>
            <th></th>
            <th></th>
            <th> {{ 'Recién incorporado' }} - {{ $data['data']->where('created_at', '>=', now()->subDays(30)->toDateTimeString())->count() ?? 'N / A' }} </th>
            <th></th>

        </tr>
            <tr>
                <th>{{ 'Criterios de filtrado' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'zona'}} - {{ $data['zone']??'todo' }}

                    <br>
                    {{ 'Módulo'}} - {{ $data['module']??'todo' }}

                    <br>
                    {{ 'Contenido de la barra de búsqueda'  }}- {{ $data['search'] ??'N / A' }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{translate($isRental.'_ID')}}</th>
            <th>{{ translate($isRental.'_Logo') }}</th>
            <th>{{ translate($isRental.'_Name') }}</th>
            <th>{{ 'Calificaciones' }}</th>
            <th>  {{ 'Información del propietario' }}</th>
            <th>   {{ 'DIRECCIÓN' }}</th>
            <th> {{ 'Total \'.$esVehículo.\'s' }}</th>
            <th> {{ 'Total \'.$isTrip.\'s' }}</th>
            <th>{{ 'Presentado ?' }}</th>
            <th>{{ 'Estado' }}</th>
        </thead>
        <tbody>
        @foreach($data['data'] as $key => $store)
        <tr>
            <td>{{$key+1}}</td>
            <td>{{  $store['id']  }}</td>
            <td>&nbsp;</td>
            <td>{{  $store['name']  }}</td>
            <td>
                @if($isRental == 'Provider')
                    {{ number_format($store->vehicle_reviews->avg('rating')) }}
                @else
                    @php($store_reviews = \App\CentralLogics\StoreLogic::calculate_store_rating($store['rating']))
                    {{ number_format($store_reviews['rating'], 1)}}
                @endif

            </td>
            <td> {{ $store->vendor->f_name .' '  .$store->vendor->l_name   }}
                        <br>
                    {{ $store->vendor->phone  }}
            </td>
            <td> {{ $store->address }} </td>
            <td>
                @if($isRental == 'Provider')
                    {{ count($store->vehicles) }}
                @else
                    {{ $store->items_count }}
                @endif
            </td>
            <td>
                @if($isRental == 'Provider')
                    {{ count($store->trips) }}
                @else
                    {{ $store->orders()->StoreOrder()->count() }}
                @endif
            </td>
            <td>
                {{ $store->featured == 1 ? 'Sí' : 'No' }}
            </td>
            <td>
                {{ $store->status == 1 ? 'Activo' : 'Inactivo' }}
            </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
