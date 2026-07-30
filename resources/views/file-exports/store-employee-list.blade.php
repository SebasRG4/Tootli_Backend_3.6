<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'lista de empleados' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'Analítica' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'empleado total'  }}- {{ $data['employees']->count() }}
                    <br>
                    {{ 'empleado activo'  }}- {{ $data['employees']->where('status',1)->count() }}
                    <br>
                    {{ 'empleado inactivo'  }}- {{ $data['employees']->where('status',0)->count() }}
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
                    {{ 'Contenido de la barra de búsqueda'  }}- {{ $data['search'] ??'N / A' }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{'imagen del empleado'}}</th>
            <th>{{'nombre de pila'}}</th>
            <th>{{'apellido'}}</th>
            <th>{{'teléfono'}}</th>
            <th>{{'correo electrónico'}}</th>
            <th>{{'role'}}</th>
            {{-- <th>{{'zona'}}</th> --}}
            <th>{{'fecha de incorporación'}}</th>
        </thead>
        <tbody>
        @foreach($data['employees'] as $key => $employee)
        <tr>
            <td>{{$key+1}}</td>
            <td></td>
            <td>{{  $employee['f_name']  }}</td>
            <td>{{  $employee['l_name']  }}</td>
            <td>{{  $employee['phone']  }}</td>
            <td>{{  $employee['email']  }}</td>
            <td>{{  $employee->role?$employee->role['name']:'rol eliminado'  }}</td>
            {{-- <td>{{  $employee->zones?->name  }}</td> --}}
            <td>
                {{date('Y-m-d '.config('timeformat'),strtotime($employee->created_at))}}
            </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
