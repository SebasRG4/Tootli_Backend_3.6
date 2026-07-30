@extends('layouts.admin.app')

@section('title','desembolso')

@push('css_or_js')

@endpush

@section('content')


<div class="content container-fluid">
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{asset('assets/admin/img/report/new/disburstment.png')}}" class="w--22" alt="">
            </span>
            <span>{{ 'Desembolso del repartidor' }}</span>
        </h1>
        <ul class="nav nav-tabs mb-4 border-0 pt-2">
            <li class="nav-item">
                <a class="nav-link {{ $status == 'all'?'active':'' }}" href="{{ route('admin.transactions.dm-disbursement.list', ['status' => 'all']) }}" >{{ 'todo' }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'pending'?'active':'' }}" href="{{ route('admin.transactions.dm-disbursement.list', ['status' => 'pending']) }}">{{ 'Pendiente' }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'processing'?'active':'' }}" href="{{ route('admin.transactions.dm-disbursement.list', ['status' => 'processing']) }}">{{ 'tratamiento' }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'completed'?'active':'' }}" href="{{ route('admin.transactions.dm-disbursement.list', ['status' => 'completed']) }}">{{ 'terminado' }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'partially_completed'?'active':'' }}" href="{{ route('admin.transactions.dm-disbursement.list', ['status' => 'partially_completed']) }}">{{ 'parcialmente completado' }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status == 'canceled'?'active':'' }}" href="{{ route('admin.transactions.dm-disbursement.list', ['status' => 'canceled']) }}">{{ 'Cancelado' }}</a>
            </li>
        </ul>
    </div>
    <!-- Reports -->
    <div class="d-flex flex-column gap-2">
        @foreach($disbursements as $disbursement)
            <div class="card">
                <div class="card-header border-0 flex-wrap justify-content-between gap-4">
                    <div class="left">
                        <h3 class="m-0 font-bold">{{ $disbursement->title }}
                            @if($disbursement->status=='pending')
                                <label class="badge badge-soft-primary">{{ 'Pendiente' }}</label>
                            @elseif($disbursement->status=='completed')
                                <label class="badge badge-soft-success">{{ 'Terminado' }}</label>
                            @elseif($disbursement->status=='partially_completed')
                                <label class="badge badge-soft-info">{{ 'parcialmente completado' }}</label>
                            @else
                                <label class="badge badge-soft-danger">{{ 'Cancelado' }}</label>
                            @endif
                        </h3>
                        <span>{{ 'creado en' }} {{ \App\CentralLogics\Helpers::time_date_format($disbursement->created_at) }}</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="d-flex flex-wrap align-items-center mr-2">
                            <span>{{ 'cantidad total' }}</span> <span class="mx-2">:</span> <h3 class="m-0">{{\App\CentralLogics\Helpers::format_currency($disbursement['total_amount'])}}</h3>
                        </div>
                        <div>
                            <a href="{{ route('admin.transactions.dm-disbursement.view', ['id' => $disbursement->id]) }}" class="btn btn--primary">{{ 'ver detalles' }}</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @if (count($disbursements) === 0)
          
                <div class="empty--data">
                     <img src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}" alt="public">
                    <h5>
                        {{'no se encontraron datos'}}
                    </h5>
                </div>
            @endif
    </div>
    <div class="page-area px-4 pb-3">
        <div class="d-flex align-items-center justify-content-end">
            <div>
                {!!$disbursements->links()!!}
            </div>
        </div>
    </div>

</div>



@endsection

@push('script_2')

@endpush
