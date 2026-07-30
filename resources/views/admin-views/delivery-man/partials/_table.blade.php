@foreach($delivery_men as $key=>$dm)
<tr>
    <td>{{$key+1}}</td>
        <td>
            <a class="table-rest-info" href="{{route('admin.users.delivery-man.preview',[$dm['id']])}}">
                <img class="onerror-image" data-onerror-image="{{asset('assets/admin/img/160x160/img1.jpg')}}"
                src="{{ $dm['image_full_url'] }}"
                alt="{{$dm['f_name']}} {{$dm['l_name']}}">
                <div class="info">
                    <h5 class="text-hover-primary mb-0">{{$dm['f_name'].' '.$dm['l_name']}}</h5>
                    <span class="d-block text-body">
                        <span class="rating">
                        <i class="tio-star"></i> {{count($dm->rating)>0?number_format($dm->rating[0]->average, 1, '.', ' '):0}}
                        </span>
                    </span>
                </div>
            </a>
        </td>
    <td>
        <a class="deco-none" href="tel:{{$dm['phone']}}">{{$dm['phone']}}</a>
    </td>
    <td>
        @if($dm->zone)
        <label class="text--title font-medium mb-0">{{$dm->zone->name}}</label>
        @else
        <label class="text--title font-medium mb-0">{{'zona eliminada'}}</label>
        @endif
    </td>
    <td>
        <a class="deco-none">{{count($dm['orders'])}}</a>
    </td>
    <td>
        <div>
            {{'órdenes actualmente asignadas'}} : {{$dm->current_orders}}
        </div>
        <div>
            {{'estado activo'}} :
            @if($dm->application_status == 'approved')
                @if($dm->active)
                <strong class="text-capitalize text-primary">{{'en línea'}}</strong>
                @else
                <strong class="text-capitalize text-secondary">{{'desconectado'}}</strong>
                @endif
            @elseif ($dm->application_status == 'denied')
                <strong class="text-capitalize text-danger">{{'denegado'}}</strong>
            @else
                <strong class="text-capitalize text-info">{{'Pendiente'}}</strong>
            @endif
        </div>
    </td>
    <td>
        <div class="btn--container justify-content-center">
            <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.users.delivery-man.edit',[$dm['id']])}}" title="{{'editar'}}"><i class="tio-edit"></i>
                </a>
            <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="delivery-man-{{$dm['id']}}" data-message="{{ '¿Quieres eliminar a este repartidor?' }}" title="{{'borrar'}}"><i class="tio-delete-outlined"></i>
            </a>
            <form action="{{route('admin.users.delivery-man.delete',[$dm['id']])}}" method="post" id="delivery-man-{{$dm['id']}}">
                @csrf @method('delete')
            </form>
        </div>
    </td>
</tr>
@endforeach
<script src="{{asset('assets/admin')}}/js/view-pages/common.js"></script>