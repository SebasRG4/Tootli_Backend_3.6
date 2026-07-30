@extends('layouts.vendor.app')

@section('title', request()?->type == 'pending' ?  'lista de anuncios pendientes' : 'Lista de anuncios')
@section('advertisement')
active
@endsection

@if (request()?->type == 'pending')

@section('advertisement_pending_list')

@else
@section('advertisement_list')

@endif


active
@endsection

@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">



@if ($total_adds == 0)




<h1 class="page-header-title mb-3">{{ 'Lista de anuncios' }}</h1>

<div class="card">
    <div class="card-body">
        <div class="text-center max-w-700 mx-auto pt-5">
            <img src="{{asset('assets/admin/img/advertisement-list.png')}}" class="mw-100 mb-3" alt="">
            <h4 class="mb-2">{{ 'Lista de anuncios' }}</h4>
            <p class="mb-4">{{ '¡Oh, oh! Aún no has creado ningún anuncio' }}!</p>
            <div class="pb-4">
                <a href="{{ route('vendor.advertisement.create') }}" class="btn btn--primary">{{ 'Crear anuncios' }}</a>
            </div>
            <hr>
            <div class="max-w-471 mx-auto fs-12 py-4">
                {{ 'Por' }} <strong>{{ 'Creando publicidad' }}</strong> {{ 'puede mostrar sus artículos o tiendas a un público más amplio a través de campañas publicitarias específicas.' }}
            </div>
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
        <a href="{{ route('vendor.advertisement.create') }}" class="btn btn-primary">  <i class="tio-add"></i> {{ 'Nuevo anuncio' }}</a>
    </div>
    <!-- Title -->


    <div class="card">

        <div class="card-header py-2 border-0">
            <div class="search--button-wrapper">
            <h5 class="card-title"></h5>
            <form >
                <!-- Search -->
                @if (request()?->type == 'pending')
                <input type="hidden" name="type" value="pending">
                @endif
                <div class="input--group input-group input-group-merge input-group-flush">

                    <input id="datatableSearch" type="search" name="search"  value="{{ request()?->search ?? null }}"  class="form-control" placeholder="{{ 'Buscar por ID de anuncios' }}" aria-label="{{'buscar aquí'}}">
                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                </div>
                <!-- End Search -->
            </form>
            @if (request()?->type != 'pending')
            <div class="select-item min-250">
                <select name="subscription_list" class="form-control js-select2-custom set-filter"
                data-url="{{url()->full()}}" data-filter="ads_type">
                    <option  value="all">{{'Todos los anuncios'}}</option>
                    <option {{ request()?->ads_type =='running'?'selected':''}} value="running">{{'correr'}} </option>
                    <option {{request()?->ads_type =='approved'?'selected':''}} value="approved">{{'aprobado'}} </option>
                    <option {{request()?->ads_type =='expired'?'selected':''}} value="expired">{{'venció'}} </option>
                    <option {{request()?->ads_type =='denied'?'selected':''}} value="denied">{{'denegado'}} </option>
                </select>
            </div>
            @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">
                <table class="font-size-sm table table-borderless table-thead-bordered table-nowrap table-align-middle card-table min-h-225px">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ 'SL' }}</th>
                            <th >{{'ID de anuncios'}}</th>
                            <th >{{'Tipo de anuncios'}}</th>
                            <th >{{'Título del anuncio'}}</th>
                            <th >{{'Duración'}}</th>
                            <th >{{'Estado'}}</th>
                            <th >{{'Acción'}}</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($adds as $key=> $add)

                        <tr>

                            <td>{{$key+$adds->firstItem()}}</td>
                            <td><a href="{{ route('vendor.advertisement.show',$add->id) }}">{{ $add->id }}</a> </td>
                            <td>{{ translate($add?->add_type) }}</td>
                            <td>
                                {{  Str::limit($add?->title, 20, '...') }}
                            </td>

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
                                <div class="dropdown dropdown-2">
                                    <button type="button" class="bg-transparent border rounded px-2 py-1 title-color" data-toggle="dropdown" aria-expanded="false">
                                        <i class="tio-more-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu" dir="ltr">
                                        <a class="dropdown-item d-flex gap-2 align-items-center" href="{{ route('vendor.advertisement.show',$add->id) }}">
                                            <i class="tio-visible-outlined"></i>
                                            {{ 'Ver anuncios' }}
                                        </a>

                                        @if ($add->active == 0 || in_array($add->status ,['pending']))
                                        <a class="dropdown-item d-flex gap-2 align-items-center" href="{{ route('vendor.advertisement.edit',$add->id) }}">
                                            <i class="tio-edit"></i>
                                            {{ 'Editar y volver a enviar anuncios' }}
                                            </a>

                                            @else
                                            <a class="dropdown-item d-flex gap-2 align-items-center new-dynamic-submit-model" href="#"

                                                id="data-edit-{{ $add->id }}"
                                                data-id="data-edit-{{ $add->id }}"

                                                data-title="{{'¿Quieres editar?'}}"
                                                data-text="<p>{{'Su anuncio se está publicando. Si edita este anuncio, aparecerá como pendiente y deberá ser aprobado por el administrador. Después de la aprobación, volverá a funcionar.'}}</p>"
                                                data-image="{{asset('assets/admin/img/modal/package-status-disable.png')}}"
                                                data-type="resume"
                                                data-btn_class = "btn-primary"
                                                data-success_btn_text = "{{ 'Sí, editar' }}"


                                                >
                                                <i class="tio-edit"></i>
                                                {{ 'Editar anuncios' }}
                                            </a>
                                            <form  id="data-edit-{{ $add->id }}_form" action="{{ route('vendor.advertisement.edit',$add->id) }}" method="get">
                                            </form>
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

                                            <form  id="data-add-{{ $add->id }}_form" action="{{ route('vendor.advertisement.status',['status' => 'approved' ,'id' => $add->id]) }}" method="get">
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

                                            <form  id="data-add-{{ $add->id }}_form" action="{{ route('vendor.advertisement.status',['status' => 'paused' ,'id' => $add->id]) }}" method="get">
                                                @csrf
                                                @method('get')
                                                <input type="hidden"  name="pause_note" id="data-add-{{ $add?->id }}_note">
                                                <input type="hidden"  name="status" value="paused">
                                                <input type="hidden"  name="id" value="{{ $add->id }}">
                                            </form>
                                            @endif

                                        <a class="dropdown-item d-flex gap-2 align-items-center" href="{{ route('vendor.advertisement.copyAdd', $add->id) }}" >
                                            <i class="tio-copy"></i>
                                            {{ 'Copiar anuncios' }}
                                            </a>

                                        <a class="dropdown-item d-flex gap-2 align-items-center new-dynamic-submit-model"
                                        id="delete-add-{{ $add->id }}"
                                            data-id="delete-add-{{ $add->id }}"
                                            @if ($add->status == 'approved' && $add->active == 1)
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
                                            <form  id="delete-add-{{ $add->id }}_form" action="{{ route('vendor.advertisement.destroy',$add->id) }}" method="post">
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
</div>

<div class="modal fade" id="created-sucessful-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="tio-clear fs-24"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
                <div class="text-center max-w-700 mx-auto">
                    <img src="{{asset('assets/admin/img/created.png')}}" class="mw-100 mb-4" alt="">
                    <h4 class="mb-2">{{ '¡Anuncio creado exitosamente!' }}</h4>
                    <p class="mb-4 fs-12 mx-auto max-w-520">{{ '¡Felicitaciones por crear su anuncio! Ahora está a la espera de aprobación. Para finalizar el proceso y hacer arreglos de pago, comuníquese con nuestro'}} <a class="text--underline" href="mailto:{{\App\CentralLogics\Helpers::get_settings('email_address')  }}">{{ 'Administrador directamente.' }}</a>
                   {{   'Esperamos poder ayudarle a aumentar su visibilidad y llegar a más clientes.' }}</p>
                    <div class="pb-4">
                        <a href="#" data-dismiss="modal"  class="btn btn--primary">{{ 'Bueno' }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif



@endsection

@push('script_2')
<script>
    @if (request()?->has('new_ad'))
    $('#created-sucessful-modal').modal('show')
        var url = new URL(window.location.href);
        var searchParams = new URLSearchParams(url.search);
        searchParams.delete('new_ad');
        var newUrl = url.origin + url.pathname + '?' + searchParams.toString();
        if (!searchParams.toString()) {
            newUrl = url.origin + url.pathname;
        }
        window.history.replaceState(null, '', newUrl);
    @endif

</script>
@endpush
