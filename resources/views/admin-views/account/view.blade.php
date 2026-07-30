@extends('layouts.admin.app')
@section('title','Información de transacción de cuenta')
@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/report.png')}}" class="w--22" alt="">
            </span>
            <span>
                {{'información de transacción de cuenta'}}
            </span>
        </h1>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="h3 mb-0  ">{{$account_transaction->from_type == 'store'?'Negocio':'información del repartidor'}}</h3>
                </div>
                <div class="card-body">
                    <div class="col-md-8 mt-2">
                        <h4>{{'nombre'}}: {{$account_transaction->from_type == 'store' ?($account_transaction->store? $account_transaction->store->name : 'tienda eliminada!'):($account_transaction->deliveryman? $account_transaction->deliveryman->f_name.' '.$account_transaction->deliveryman->l_name : 'Repartidor no encontrado')}}</h4>
                        <h6>{{'teléfono'}}  : {{$account_transaction->from_type == 'store'?($account_transaction->store ? $account_transaction->store->phone : 'tienda eliminada!'):($account_transaction->deliveryman ? $account_transaction->deliveryman->phone : 'Repartidor no encontrado')}}</h6>
                        <h6>{{'efectivo en mano'}} : {{\App\CentralLogics\Helpers::format_currency($account_transaction->from_type == 'store' ? ($account_transaction->store ? $account_transaction->store->vendor->wallet->collected_cash : 0): ($account_transaction->deliveryman ? $account_transaction->deliveryman->wallet->collected_cash : 0))}}</h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="h3 mb-0  ">{{'información de transacción'}} </h3>
                </div>
                <div class="card-body">
                    <h6>{{'cantidad'}} : {{\App\CentralLogics\Helpers::format_currency($account_transaction->amount)}}</h6>
                    <h6 class="text-capitalize">{{'tiempo'}} : {{$account_transaction->created_at->format('Y-m-d '.config('timeformat'))}}</h6>
                    <h6>{{'método'}} : {{$account_transaction->method}}</h6>
                    <h6>{{'referencia'}} : {{$account_transaction->ref}}</h6>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script')

@endpush
