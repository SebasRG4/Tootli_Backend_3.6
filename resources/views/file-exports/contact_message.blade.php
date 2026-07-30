<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'Mensajes de contacto' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'Análisis de mensajes' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'Total'  }}: {{ $data->count() }}


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
            <th>{{ 'Nombre' }}</th>
            <th>{{ 'Correo electrónico' }}</th>
            <th>{{ 'Sujeto' }}</th>
            <th>{{ 'Mensaje' }}</th>
            <th>{{ 'Responder' }}</th>
            <th>{{ 'Visto' }}</th>
            <th>{{ 'Creado en' }} </th>
        </thead>
        <tbody>
        @foreach($data as $key => $message)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $message->name }}</td>
        <td>{{ $message->email }}</td>
        <td>{{ $message->subject }}</td>
        <td>{{ $message->message }}</td>
        <td>{{ $message->reply ?? 'N / A' }}</td>
        <td>{{ $message->seen == 0 ? 'invisible' : 'visto' }}</td>
        <td>{{  \App\CentralLogics\Helpers::time_date_format($message->created_at)}}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
