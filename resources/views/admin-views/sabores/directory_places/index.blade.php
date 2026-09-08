@extends('layouts.admin.app')

@section('title', 'Lugares de Directorio')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">Lugares (Sin Dueño) <span class="badge badge-soft-dark ml-2">{{$places->total()}}</span></h1>
            </div>
            <div class="col-sm-auto">
                <a class="btn btn-primary" href="{{route('admin.sabores.directory-places.create')}}">
                    <i class="tio-add"></i> Agregar nuevo lugar
                </a>
            </div>
        </div>
    </div>

    <div class="row gx-2 gx-lg-3">
        <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
            <div class="card">
                <div class="card-header border-0">
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group input-group-merge input-group-flush">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="tio-search"></i>
                                </div>
                            </div>
                            <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="Buscar lugar" aria-label="Search" value="{{$search}}" required>
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                    </form>
                </div>
                
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Logo</th>
                                <th>Zona</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($places as $key=>$place)
                            <tr>
                                <td>{{$places->firstItem()+$key}}</td>
                                <td>
                                    <span class="d-block font-size-sm text-body">
                                        {{$place['name']}}
                                    </span>
                                </td>
                                <td>
                                    <div style="height: 50px; width: 50px; overflow: hidden; border-radius: 50%">
                                        <img src="{{asset('storage/app/public/store')}}/{{$place['logo']}}" style="width: 100%; object-fit: cover" onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'">
                                    </div>
                                </td>
                                <td>
                                    {{$place->zone ? $place->zone->name : 'N/A'}}
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-white" href="{{route('admin.sabores.directory-places.edit',[$place['id']])}}" title="Editar"><i class="tio-edit"></i>
                                    </a>
                                    <a class="btn btn-sm btn-white text-danger" href="javascript:" onclick="form_alert('place-{{$place['id']}}','¿Deseas eliminar este lugar?')" title="Eliminar"><i class="tio-delete-outlined"></i>
                                    </a>
                                    <form action="{{route('admin.sabores.directory-places.delete',[$place['id']])}}" method="post" id="place-{{$place['id']}}">
                                        @csrf @method('delete')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <hr>
                    <table>
                        <tfoot>
                        {!! $places->links() !!}
                        </tfoot>
                    </table>
                    @if(count($places) == 0)
                    <div class="text-center p-4">
                        <img class="mb-3" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="Image Description" style="width: 7rem;">
                        <p class="mb-0">No hay datos</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
