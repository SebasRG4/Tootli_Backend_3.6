@extends('layouts.admin.app')

@section('title', 'agregar nueva misión')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/condition.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'agregar nueva misión'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.mission.store')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="title">{{'título'}}</label>
                                <input type="text" name="title" id="title" class="form-control"
                                    placeholder="{{'Ej: completar 10 pedidos'}}" required
                                    maxlength="191">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="zone_id">{{'zona'}}</label>
                                <select name="zone_id" id="zone_id" class="form-control js-select2-custom">
                                    <option value="" selected>{{'todas las zonas'}}</option>
                                    @foreach($zones as $zone)
                                        <option value="{{$zone->id}}">{{$zone->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label"
                                    for="target_orders">{{'órdenes objetivo'}}</label>
                                <input type="number" name="target_orders" id="target_orders" class="form-control"
                                    placeholder="{{'ejemplo: 10'}}" required min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="reward_amount">{{'cantidad de recompensa'}}
                                    ({{\App\CentralLogics\Helpers::currency_symbol()}})</label>
                                <input type="number" step="0.01" name="reward_amount" id="reward_amount"
                                    class="form-control" placeholder="{{'ejemplo: 100'}}" required min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="start_date">{{'fecha de inicio'}}</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label" for="end_date">{{'fecha de finalización'}}</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="input-label" for="description">{{'descripción'}}
                            ({{'opcional'}})</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="submit" class="btn btn--primary">{{'entregar'}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection