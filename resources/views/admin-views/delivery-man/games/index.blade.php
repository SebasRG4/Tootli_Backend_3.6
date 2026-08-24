@extends('layouts.admin.app')

@section('title','Juegos Programados')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <i class="tio-gamepad"></i>
            </span>
            <span>Programar Juegos Nativos</span>
        </h1>
    </div>

    <div class="row g-2">
        <div class="col-sm-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{route('admin.delivery-man.games.store')}}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 col-12">
                                <div class="form-group">
                                    <label class="input-label" for="title">Título del Juego</label>
                                    <input type="text" name="title" class="form-control" placeholder="Ej. Ruleta de Descuentos" required>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="form-group">
                                    <label class="input-label" for="game_type">Tipo de Juego</label>
                                    <select name="game_type" class="form-control" required>
                                        <option value="roulette">Ruleta</option>
                                        <option value="scratch_card">Raspadito</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 col-6">
                                <div class="form-group">
                                    <label class="input-label" for="start_date">Fecha Inicio</label>
                                    <input type="datetime-local" name="start_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-2 col-6">
                                <div class="form-group">
                                    <label class="input-label" for="end_date">Fecha Fin</label>
                                    <input type="datetime-local" name="end_date" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="input-label" for="description">Descripción / Instrucciones</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="ads_enabled" name="ads_enabled" checked>
                                <label class="custom-control-label" for="ads_enabled">Habilitar Anuncios (Monetización)</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Programar Juego</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-12 mt-3">
            <div class="card">
                <div class="card-header border-0">
                    <h5 class="card-title">Juegos Programados</h5>
                </div>
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Anuncios</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($games as $k=>$game)
                            <tr>
                                <td>{{$k+1}}</td>
                                <td>{{$game['title']}}</td>
                                <td>{{$game['game_type']}}</td>
                                <td>{{$game['start_date']}}</td>
                                <td>{{$game['end_date']}}</td>
                                <td>{{$game['ads_enabled'] ? 'Sí' : 'No'}}</td>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox" class="toggle-switch-input" onclick="location.href='{{route('admin.delivery-man.games.status',[$game['id'],$game->status?0:1])}}'" {{$game->status?'checked':''}}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-white text-danger" href="javascript:" onclick="form_alert('game-{{$game['id']}}','¿Eliminar juego?')"><i class="tio-delete-outlined"></i></a>
                                    <form action="{{route('admin.delivery-man.games.delete',[$game['id']])}}" method="post" id="game-{{$game['id']}}">
                                        @csrf @method('delete')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
