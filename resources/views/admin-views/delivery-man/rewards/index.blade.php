@extends('layouts.admin.app')

@section('title','Juegos y Recompensas')

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <i class="tio-gift"></i>
            </span>
            <span>Gestión de Recompensas</span>
        </h1>
    </div>

    <div class="row g-2">
        <div class="col-sm-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{route('admin.delivery-man.rewards.store')}}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 col-12">
                                <div class="form-group">
                                    <label class="input-label" for="title">Título</label>
                                    <input type="text" name="title" class="form-control" placeholder="Ej. Autolavados" required>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="form-group">
                                    <label class="input-label" for="icon">Icono (Ej. car_wash)</label>
                                    <input type="text" name="icon" class="form-control" placeholder="Identificador del icono">
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="form-group">
                                    <label class="input-label" for="short_discount">Descuento Corto</label>
                                    <input type="text" name="short_discount" class="form-control" placeholder="Ej. Hasta 25% off">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="input-label" for="description">Descripción</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Recompensa</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-12 mt-3">
            <div class="card">
                <div class="card-header border-0">
                    <h5 class="card-title">Lista de Recompensas</h5>
                </div>
                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Título</th>
                                <th>Descripción</th>
                                <th>Descuento</th>
                                <th>Descuentos Específicos</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($rewards as $k=>$reward)
                            <tr>
                                <td>{{$k+1}}</td>
                                <td>{{$reward['title']}}</td>
                                <td>{{Str::limit($reward['description'], 30)}}</td>
                                <td>{{$reward['short_discount']}}</td>
                                <td>
                                    <ul>
                                        @foreach($reward->discounts as $discount)
                                            <li>{{$discount->title}} - {{$discount->value}} 
                                                <a href="javascript:" onclick="form_alert('discount-{{$discount['id']}}','¿Estás seguro?')" class="text-danger"><i class="tio-delete-outlined"></i></a>
                                                <form action="{{route('admin.delivery-man.rewards.discount.delete',[$discount['id']])}}" method="post" id="discount-{{$discount['id']}}">
                                                    @csrf @method('delete')
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                    
                                    <!-- Form to add discount -->
                                    <form action="{{route('admin.delivery-man.rewards.discount.store')}}" method="post" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="dm_reward_id" value="{{$reward->id}}">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" name="title" placeholder="Lugar" required>
                                            <input type="text" class="form-control" name="value" placeholder="Descuento" required>
                                            <input type="text" class="form-control" name="description" placeholder="Desc" required>
                                            <button class="btn btn-primary" type="submit">+</button>
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox" class="toggle-switch-input" onclick="location.href='{{route('admin.delivery-man.rewards.status',[$reward['id'],$reward->status?0:1])}}'" {{$reward->status?'checked':''}}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-white text-danger" href="javascript:" onclick="form_alert('reward-{{$reward['id']}}','¿Eliminar recompensa?')"><i class="tio-delete-outlined"></i></a>
                                    <form action="{{route('admin.delivery-man.rewards.delete',[$reward['id']])}}" method="post" id="reward-{{$reward['id']}}">
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
