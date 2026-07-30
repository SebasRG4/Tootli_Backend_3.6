@foreach($employees as $k=>$employee)
<tr>
    <th scope="row">{{$k+1}}</th>
    <td class="text-capitalize">{{$employee['f_name']}} {{$employee['l_name']}}</td>
    <td >
        {{$employee['email']}}
    </td>
    <td>{{$employee['phone']}}</td>
    <td>{{$employee->role?$employee->role['name']:'rol eliminado'}}</td>
    <td>
        @if (auth('admin')->id()  != $employee['id'])
        <div class="btn--container justify-content-center">
            <a class="btn action-btn btn--primary btn-outline-primary"
                href="{{route('admin.users.employee.edit',[$employee['id']])}}" title="{{'editar empleado'}}"><i class="tio-edit"></i>
            </a>
            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="employee-{{$employee['id']}}" data-message="{{'Quiere eliminar este rol'}}" title="{{'eliminar empleado'}}"><i class="tio-delete-outlined"></i>
            </a>
        </div>
        <form action="{{route('admin.users.employee.delete',[$employee['id']])}}"
                method="post" id="employee-{{$employee['id']}}">
            @csrf @method('delete')
        </form>
        @endif
    </td>
</tr>
@endforeach
