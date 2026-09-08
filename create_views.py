import os

base_dir = "resources/views/admin-views/sabores/directory_places"

index_content = """@extends('layouts.admin.app')

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
"""

create_content = """@extends('layouts.admin.app')
@section('title', 'Agregar Lugar de Directorio')
@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">Agregar Lugar (Sin dueño)</h1>
    </div>
    <form action="{{route('admin.sabores.directory-places.store')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">Información del lugar</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label" for="name">Nombre</label>
                            <input type="text" name="name" class="form-control" placeholder="Nombre" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label" for="zone_id">Zona</label>
                            <select name="zone_id" id="zone_id" class="form-control" required>
                                <option value="" selected disabled>Seleccionar Zona</option>
                                @foreach(\App\Models\Zone::all() as $zone)
                                    <option value="{{$zone->id}}">{{$zone->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="input-label" for="directory_description">Descripción para turismo</label>
                            <textarea name="directory_description" class="form-control" rows="4" placeholder="Breve descripción del lugar..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label" for="latitude">Latitud</label>
                            <input type="text" name="latitude" class="form-control" placeholder="Ej. 19.4326" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label" for="longitude">Longitud</label>
                            <input type="text" name="longitude" class="form-control" placeholder="Ej. -99.1332" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label">Logo (1:1)</label>
                            <input type="file" name="logo" class="form-control-file" accept=".jpg, .png, .jpeg" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label">Foto de portada (2:1)</label>
                            <input type="file" name="cover_photo" class="form-control-file" accept=".jpg, .png, .jpeg">
                        </div>
                    </div>
                </div>
                <div class="btn--container mt-4">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
"""

edit_content = """@extends('layouts.admin.app')
@section('title', 'Editar Lugar de Directorio')
@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">Editar Lugar (Sin dueño)</h1>
    </div>
    <form action="{{route('admin.sabores.directory-places.update', [$place->id])}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">Información del lugar</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label" for="name">Nombre</label>
                            <input type="text" name="name" value="{{$place->name}}" class="form-control" placeholder="Nombre" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label" for="zone_id">Zona</label>
                            <select name="zone_id" id="zone_id" class="form-control" required>
                                @foreach(\App\Models\Zone::all() as $zone)
                                    <option value="{{$zone->id}}" {{$place->zone_id == $zone->id ? 'selected' : ''}}>{{$zone->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="input-label" for="directory_description">Descripción para turismo</label>
                            <textarea name="directory_description" class="form-control" rows="4">{{$place->directory_description}}</textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label" for="latitude">Latitud</label>
                            <input type="text" name="latitude" value="{{$place->latitude}}" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label" for="longitude">Longitud</label>
                            <input type="text" name="longitude" value="{{$place->longitude}}" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label">Logo (Dejar vacío para no cambiar)</label>
                            <input type="file" name="logo" class="form-control-file" accept=".jpg, .png, .jpeg">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label">Foto de portada (Dejar vacío para no cambiar)</label>
                            <input type="file" name="cover_photo" class="form-control-file" accept=".jpg, .png, .jpeg">
                        </div>
                    </div>
                </div>
                <div class="btn--container mt-4">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
"""

with open(os.path.join(base_dir, "index.blade.php"), "w") as f:
    f.write(index_content)
with open(os.path.join(base_dir, "create.blade.php"), "w") as f:
    f.write(create_content)
with open(os.path.join(base_dir, "edit.blade.php"), "w") as f:
    f.write(edit_content)
    
print("Created views")
