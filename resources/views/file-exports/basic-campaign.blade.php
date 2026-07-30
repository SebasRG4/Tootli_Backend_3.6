<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'Lista de campaña básica' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'Análisis de mensajes' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'Campaña Total'  }}: {{ $data->count() }}
                    <br>
                    {{ 'Actualmente en ejecución'  }}: {{ $data->where('status',1)->count() }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
            <tr>
                <th>{{ 'Criterios de búsqueda' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'Contenido de la barra de búsqueda'  }}: : {{ $search ??'N / A' }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{ 'Nombre de la campaña' }}</th>
            <th>{{ 'Descripción' }}</th>
            <th>{{ 'Fecha de inicio' }}</th>
            <th>{{ 'Fecha de finalización' }}</th>
            <th>{{ 'Hora de inicio diaria' }}</th>
            <th>{{ 'Hora de finalización diaria' }}</th>
            <th>{{ 'Total de tiendas unidas' }} </th>
        </thead>
        <tbody>
        @foreach($data as $key => $campaign)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $campaign->title }}</td>
        <td>{{ $campaign->description }}</td>
        <td>{{ $campaign->start_date->format('d M Y') }}</td>
        <td>{{ $campaign->end_date->format('d M Y') }}</td>
        <td>{{ \Carbon\Carbon::parse($campaign->start_time)->format("H:i A") }}</td>
        <td>{{ \Carbon\Carbon::parse($campaign->end_time)->format("H:i A") }}</td>
        <td>{{ $campaign->stores->count() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
