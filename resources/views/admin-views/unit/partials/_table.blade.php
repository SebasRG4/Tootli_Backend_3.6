@foreach($units as $key=>$unit)
<tr>
    <td>{{$key+1}}</td>
    <td>
    <span class="d-block font-size-sm text-body">
        {{Str::limit($unit['unit'],20,'...')}}
    </span>
    </td>
    <td>
        <div class="btn--container justify-content-center">
            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.unit.edit',[$unit['id']])}}" title="{{'editar'}}"><i class="tio-edit"></i>
            </a>
            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="unit-{{$unit['id']}}" data-message="{{ '¿Quieres eliminar esta unidad?' }}" title="{{'borrar'}}"><i class="tio-delete-outlined"></i>
            </a>
            <form action="{{route('admin.unit.destroy',[$unit['id']])}}"
                    method="post" id="unit-{{$unit['id']}}">
                @csrf @method('delete')
            </form>
        </div>
    </td>
</tr>
@endforeach
