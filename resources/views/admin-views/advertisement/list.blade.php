@extends('layouts.admin.app')

@section('title', 'Lista de anuncios')
@section('advertisement')
active
@endsection
@section('advertisement_list')
active
@endsection

@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid overflow-hidden">



    @if ($ads_count == 0)




    <h1 class="page-header-title mb-3">{{ 'Lista de anuncios' }}</h1>

    <div class="card">
        <div class="card-body">
            <div class="text-center max-w-700 mb-10 mt-10 mx-auto pt-5">
                <img src="{{asset('assets/admin/img/advertisement-list.png')}}" class="mw-100 mb-3" alt="">
                <h4 class="mb-2">{{ 'Lista de anuncios' }}</h4>
                <p class="mb-4">{{ 'Cree un anuncio para su público objetivo, ya que aún no se ha creado ninguno.' }}</p>
                {{-- <div class="pb-4">
                    <a href="{{ route('vendor.advertisement.create') }}" class="btn btn--primary">{{ 'Crear anuncios' }}</a>
                </div> --}}
                {{-- <hr>
                <div class="max-w-471 mx-auto fs-12 py-4">
                    {{ 'Por' }} <strong>{{ 'Creando publicidad' }}</strong> {{ 'puede mostrar sus artículos o tiendas a un público más amplio a través de campañas publicitarias específicas.' }}
                </div> --}}
            </div>
        </div>
    </div>



    @else




    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-header-title d-flex align-items-center gap-2">
            <img src="{{asset('assets/admin/img/advertisement.png')}}" alt="">
            {{ 'Lista de anuncios' }}
            <span class="badge badge-soft-dark ml-2">{{ $adds->total() }}</span>
        </h1>
        <a href="{{ route('admin.advertisement.create') }}" class="btn btn-primary">  <i class="tio-add"></i> {{ 'Nuevo anuncio' }}</a>
    </div>
    <!-- Title -->


    <div class="card">

        <div class="card-header py-2 border-0">
            <div class="search--button-wrapper">
            <h5 class="card-title"></h5>
            <form >
                <!-- Search -->
                <div class="input--group input-group input-group-merge input-group-flush">
                    <input id="datatableSearch" type="search" name="search"  value="{{ request()?->search ?? null }}"  class="form-control" placeholder="{{ 'Buscar por ID de anuncios o nombre de tienda' }}" aria-label="{{'buscar aquí'}}">
                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                </div>
                <!-- End Search -->
            </form>
            <div class="select-item min-w-135px">
                <select name="subscription_list" class="form-control js-select2-custom set-filter"
                data-url="{{url()->full()}}" data-filter="ads_type">
                    <option  value="all">{{'Todos los anuncios'}}</option>
                    <option {{ request()?->ads_type =='running'?'selected':''}} value="running">{{'correr'}} </option>
                    <option {{request()?->ads_type =='paused'?'selected':''}} value="paused">{{'pausado'}} </option>
                    <option {{request()?->ads_type =='approved'?'selected':''}} value="approved">{{'aprobado'}} </option>
                    <option {{request()?->ads_type =='expired'?'selected':''}} value="expired">{{'venció'}} </option>
                </select>
            </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">
                <table class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table min-h-225px">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ 'SL' }}</th>
                            <th >{{'ID de anuncios'}}</th>
                            <th >{{'Título del anuncio'}}</th>
                            <th >{{'Información de la tienda'}}</th>
                            <th >{{'Tipo de anuncios'}}</th>
                            <th >{{'Duración'}}</th>
                            <th >{{'Estado'}}</th>
                            <th >{{'Prioridad'}}</th>
                            <th >{{'Acción'}}</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($adds as $key=> $add)

                        <tr>

                            <td>{{$key+$adds->firstItem()}}</td>
                            <td> <a href="{{ route('admin.advertisement.show',$add->id) }}"> {{ $add->id }}</a></td>
                            <td>{{ Str::limit($add->title, 20) }}</td>
                            <td>
                                @if($add->store)
                                <a class="media align-items-center text-body" href="{{route('admin.store.view', $add?->store_id)}}">
                                    <img class="avatar avatar-lg mr-3" src="{{ $add->store['logo_full_url'] ?? asset('assets/admin/img/100x100/food-default-image.png') }}" alt="">
                                    <div class="media-body">
                                        <h5 class="mb-0">{{ $add?->store?->name }}</h5>
                                        <small class="text-body">{{ $add?->store?->email }}</small>
                                    </div>
                                </a>
                                @else
                                    <span class="badge badge-soft-info">{{ 'Publicidad mundial' }}</span>
                                @endif
                            </td>

                            <td>{{ translate($add?->add_type) }}</td>
                            <td>
                                {{  \App\CentralLogics\Helpers::date_format($add->start_date) }} - <br> {{  \App\CentralLogics\Helpers::date_format($add->end_date) }}
                            </td>
                            <td>
                                @if ($add->status == 'approved' && $add->active == 1 )
                                <label class="badge badge-soft-primary rounded-pill">{{ 'correr' }}</label>
                                @elseif ($add->status == 'approved' && $add->active == 2 )
                                <label class="badge badge-soft-success rounded-pill">{{ 'aprobado' }}</label>
                                @elseif ($add->status == 'paused' && $add->active == 1 )
                                <label class="badge badge-soft-warning rounded-pill">{{ 'pausado' }}</label>
                                @elseif (in_array($add->status ,['denied','expired'] ))
                                <label class="badge badge-soft-danger rounded-pill">{{ translate($add->status) }}</label>
                                @elseif ($add->active == 0)
                                <label class="badge badge-soft-secondary rounded-pill">{{ 'Venció' }}</label>
                                @else
                                <label class="badge badge-soft-info rounded-pill">{{ translate($add->status) }}</label>
                                @endif

                            </td>
                            <td>
                                @if ( in_array($add->status ,['denied','expired']) || $add->active == 0)
                                <div class="d-flex align-items-center gap-2 ml-3" data-toggle="tooltip" title="{{ 'Los anuncios caducados y denegados no tienen prioridad.' }}">
                                    <span>{{  'N / A' }}</span> <img src="{{asset('assets/admin/img/na.png')}}" alt="">
                                </div>
                                @else

                                <select id="select_option_{{ $add->id }}" data-priority_old_value="{{ $add?->priority }}" data-prority_id="{{ $add->id }}" class="form-control w-70px p-0 h-30px js-select2-custom update-priority">
                                    <option value="{{ $add?->priority == null ||  $add?->priority == 0 ?  '' : $add?->priority }}">{{ $add?->priority == null ||  $add?->priority == 0 ?  'N / A' : $add?->priority }} </option>
                                    @for ($i = 1; $i <= $total_adds; $i++)
                                    @if ($add?->priority != $i )
                                    <option value="{{ $i }}">{{ $i }}</option>
                                    @endif
                                    @endfor
                                    @if ( $add?->priority !== null)
                                        <option value="">{{  'N / A' }} </option>
                                    @endif
                                </select>
                                @endif



                            </td>
                            <td>
                                <div class="dropdown dropdown-2">
                                    <button type="button" class="bg-transparent border rounded px-2 py-1 title-color" data-toggle="dropdown" aria-expanded="false">
                                        <i class="tio-more-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu" dir="ltr">
                                        <a class="dropdown-item d-flex gap-2 align-items-center" href="{{ route('admin.advertisement.show',$add->id) }}">
                                            <i class="tio-visible-outlined"></i>
                                            {{ 'Ver anuncios' }}
                                        </a>

                                        @if ($add->active == 0)
                                        <a class="dropdown-item d-flex gap-2 align-items-center" href="{{ route('admin.advertisement.edit',$add->id) }}">
                                            <i class="tio-edit"></i>
                                            {{ 'Editar y volver a enviar anuncios' }}
                                            </a>

                                            @else
                                            <a class="dropdown-item d-flex gap-2 align-items-center" href="{{ route('admin.advertisement.edit',$add->id) }}">
                                                <i class="tio-edit"></i>
                                                {{ 'Editar anuncios' }}
                                            </a>
                                        @endif




                                        @if($add->status == 'paused')
                                            <a class="dropdown-item d-flex gap-2 align-items-center new-dynamic-submit-model"


                                            id="data-add-{{ $add->id }}"
                                            data-id="data-add-{{ $add->id }}"

                                            data-title="{{'¿Está seguro de que desea reanudar la solicitud?'}}"
                                            data-text="<p>{{'Este anuncio se publicará nuevamente y se mostrará en la aplicación y los sitios web del usuario.'}}</p>"
                                            data-image="{{asset('assets/admin/img/modal/resume.png')}}"
                                            data-type="resume"
                                            data-btn_class = "btn-primary"


                                            href="#">
                                                <i class="tio-pause-circle"></i>
                                                {{ 'Reanudar anuncios' }}
                                            </a>

                                            <form  id="data-add-{{ $add->id }}_form" action="{{ route('admin.advertisement.status',['status' => 'approved' ,'id' => $add->id]) }}" method="get">
                                                @csrf
                                                @method('get')
                                                <input type="hidden"  name="status" value="approved">
                                                <input type="hidden"  name="id" value="{{ $add->id }}">
                                            </form>




                                        @elseif($add->status == 'approved' && $add->active == 1)
                                        <a class="dropdown-item d-flex gap-2 align-items-center new-dynamic-submit-model"
                                        id="data-add-{{ $add->id }}"
                                        data-id="data-add-{{ $add->id }}"
                                        data-title="{{'¿Está seguro de que desea pausar la solicitud?'}}"
                                        data-text="<p>{{'Este anuncio se detendrá y no se mostrará en la aplicación ni en los sitios web del usuario.'}}</p>"
                                        data-image="{{asset('assets/admin/img/modal/pause.png')}}"
                                        data-type="pause"

                                        href="#">
                                            <i class="tio-pause-circle"></i>
                                            {{ 'Pausar anuncios' }}
                                            </a>

                                            <form  id="data-add-{{ $add->id }}_form" action="{{ route('admin.advertisement.status',['status' => 'paused' ,'id' => $add->id]) }}" method="get">
                                                @csrf
                                                @method('get')
                                                <input type="hidden"  name="pause_note" id="data-add-{{ $add?->id }}_note">
                                                <input type="hidden"  name="status" value="paused">
                                                <input type="hidden"  name="id" value="{{ $add->id }}">
                                            </form>
                                        @endif





                                        <a class="dropdown-item d-flex gap-2 align-items-center" href="{{ route('admin.advertisement.copyAdd', $add->id) }}" >
                                            <i class="tio-copy"></i>
                                            {{ 'Copiar anuncios' }}
                                            </a>


                                        <a class="dropdown-item d-flex gap-2 align-items-center new-dynamic-submit-model"
                                        id="delete-add-{{ $add->id }}"
                                            data-id="delete-add-{{ $add->id }}"
                                            @if ($add->status != 'paused' && $add->active == 1)
                                                data-title="{{'No puedes eliminar el anuncio.'}}"
                                                data-text="<p>{{'Este anuncio se está publicando actualmente. Para eliminar este anuncio de la lista, reanude primero el anuncio. Una vez actualizado el estado, puede continuar con la eliminación.'}}</p>"
                                                data-image="{{asset('assets/admin/img/modal/package-status-disable.png')}}"
                                                data-type="warning"
                                            @else
                                                data-type="delete"
                                                data-title="{{'Confirmar la eliminación de anuncios'}}"
                                                data-text="<p>{{'Eliminar este anuncio lo eliminará permanentemente. ¿Estás seguro de que quieres continuar?'}}</p>"
                                                data-image="{{asset('assets/admin/img/modal/delete-icon.png')}}"
                                            @endif
                                            >
                                            <i class="tio-delete"></i>
                                            {{ 'Eliminar anuncios' }}
                                            </a>
                                            <form  id="delete-add-{{ $add->id }}_form" action="{{ route('admin.advertisement.destroy',$add->id) }}" method="post">
                                                @csrf
                                                @method('delete')
                                            </form>



                                    </ul>
                                </div>
                            </td>
                        </tr>

                        @endforeach
                    </tbody>
                </table>
                @if(count($adds) === 0)
                <div class="empty--data">
                    <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                    <h5>
                        {{'no se encontraron datos'}}
                    </h5>
                </div>
                @endif
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        <div>
                            {!! $adds->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endif


</div>


<div class="modal fade" id="priority-update-modal">
    <div class="modal-dialog modal-dialog-centered status-warning-modal">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <div class="modal-body pb-5 pt-0">
                <form action="{{ route('admin.advertisement.priority') }}" method="get">
                <div class="max-349 mx-auto mb-20">
                    <div>
                        <div class="text-center">
                            <img src="{{asset('assets/admin/img/modal/package-status-disable.png')}}" class="mb-20">
                            <h5 class="modal-title" id="toggle-title"></h5>
                        </div>
                        <div class="text-center" id="toggle-message">
                            <h3 >{{ '¿Está seguro de que desea la prioridad de este anuncio?' }}</h3>
                        </div>
                        <input id="update_priority_value"   name="priority_value" type="hidden">
                        <input id="update_priority_id" name="priority_id" type="hidden">
                        <input id="update_priority_old_value"  type="hidden">
                        </div>

                    <div class="btn--container justify-content-center mt-3">
                        <button data-dismiss="modal" type="reset" id="reset_btn" class="btn btn--cancel" >{{'Ahora no'}}</button>
                        <button type="sbmit" class="btn btn-primary min-w-120">{{'Sí'}}</button>
                    </div>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>





@endsection

@push('script_2')
<script>
    $(document).ready(function() {


    $('.update-priority').on('change', function() {


        let update_priority_value = $(this).val();
        let update_priority_old_value = $(this).data('priority_old_value');
        let update_priority_id = $(this).data('prority_id');

        $('#update_priority_value').val(update_priority_value)
        $('#update_priority_old_value').val(update_priority_old_value)
        $('#update_priority_id').val(update_priority_id)
        $('#priority-update-modal').modal('show')
    });
    $('#reset_btn').on('click', function() {

        $('#update_priority_id').val()

        $('#select_option_'+$('#update_priority_id').val()).val( $('#update_priority_old_value').val()
        ).trigger('change');

    });




});

</script>
@endpush
