@foreach($roles as $k=>$role)
    <tr>
        <td scope="row">{{$k+1}}</td>
        <td>{{$role['name']}}</td>
        <td class="text-capitalize">
            @if($role['modules']!=null)
                @foreach((array)json_decode($role['modules']) as $key=>$module)
                    {{str_replace('_',' ',$module)}},
                @endforeach
            @endif
        </td>
        <td>{{date('d-M-y',strtotime($role['created_at']))}}</td>
        <td>
            <div class="btn--container justify-content-center">
                <a class="btn action-btn btn--primary btn-outline-primary"
                    href="{{route('admin.users.custom-role.edit',[$role['id']])}}" title="{{'editar rol'}}"><i class="tio-edit"></i>
                </a>
                <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="role-{{$role['id']}}" data-message="{{'Quiere eliminar este rol'}}"
                   title="{{'eliminar rol'}}"><i class="tio-delete-outlined"></i>
                </a>
            </div>
            <form action="{{route('admin.users.custom-role.delete',[$role['id']])}}"
                    method="post" id="role-{{$role['id']}}">
                @csrf @method('delete')
            </form>
        </td>
    </tr>
@endforeach
