@extends('layouts.admin.app')

@section('title', 'Seguimiento de Interés')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <i class="tio-chart-bar-4"></i>
                        </div>
                        Seguimiento de Interés en Módulos Próximos
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Stats Section -->
        <div class="row g-3 mb-3">
            @foreach($stats as $stat)
            <div class="col-sm-6 col-md-4 col-xl-3">
                <div class="card card-body h-100 justify-content-center text-center">
                    <h6 class="text-capitalize mb-2">{{ $stat->module_name }}</h6>
                    <div class="h2 mb-0">{{ $stat->total }}</div>
                    <div class="text-muted text-sm">Usuarios Interesados</div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">Listado de Intereses</h5>
                        </div>
                    </div>
                    <div class="table-responsive datatable-custom">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>SL</th>
                                    <th>Módulo</th>
                                    <th>Usuario</th>
                                    <th>IP</th>
                                    <th>Navegador / App</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>

                            <tbody>
                            @foreach($interest_tracks as $key => $track)
                                <tr>
                                    <td>{{$interest_tracks->firstItem()+$key}}</td>
                                    <td>
                                        <span class="d-block font-size-sm text-body">
                                            {{$track['module_name']}} (ID: {{$track['module_id']}})
                                        </span>
                                    </td>
                                    <td>
                                        @if($track->user)
                                            <a href="{{route('admin.users.customer.view',[$track['user_id']])}}">
                                                {{$track->user['f_name']}} {{$track->user['l_name']}}
                                            </a>
                                        @else
                                            <span class="badge badge-soft-info">Invitado</span>
                                        @endif
                                    </td>
                                    <td>{{$track['ip_address']}}</td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{$track['user_agent']}}">
                                            {{$track['user_agent']}}
                                        </span>
                                    </td>
                                    <td>{{ date('d M Y, h:i a', strtotime($track['created_at'])) }}</td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--danger btn-outline-danger" href="javascript:"
                                                onclick="form_alert('interest-{{$track['id']}}','¿Desea eliminar este registro?')" title="Eliminar">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.users.interest-track.delete',[$track['id']])}}"
                                                    method="post" id="interest-{{$track['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($interest_tracks) === 0)
                        <div class="empty--data">
                            <img src="{{asset('/assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                            <h5>No hay registros de interés aún</h5>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        {!! $interest_tracks->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
