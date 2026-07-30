@extends('layouts.vendor.app')

@section('title', 'historial recolecciones efectivo')

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{asset('assets/admin/img/image_90.png')}}" alt="public">
                        </div>
                        <span>
                            {{'historial recolecciones efectivo'}}
                        </span>
                    </h2>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <?php
        $wallet = \App\Models\StoreWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
        if(isset($wallet)==false){
            \Illuminate\Support\Facades\DB::table('store_wallets')->insert([
                'vendor_id'=>\App\CentralLogics\Helpers::get_vendor_id(),
                'created_at'=>now(),
                'updated_at'=>now()
            ]);
            $wallet = \App\Models\StoreWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
        }
        ?>
        @include('vendor-views.wallet.partials._balance_data',['wallet'=>$wallet])
        <div class="card-body p-0 mt-3">
            <div class="table-responsive">
                <table id="datatable"
                       class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
                                    "order": [],
                                    "orderCellsTop": true,
                                    "paging":false
                                }' >
                    <thead class="thead-light">
                    <tr>
                        <th>{{ 'SL' }}</th>
                        <th>{{ 'identificación del pedido' }}</th>
                        <th>{{ 'Repartidor' }}</th>
                        <th>{{ 'Total de entradas' }}</th>
                        <th>{{ 'Deuda Amortizada' }}</th>
                        <th>{{ 'Efectivo recibido' }}</th>
                        <th>{{ 'Tiempo de pago' }}</th>
                        <th>{{ 'estado' }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($history as $k=>$wr)
                        <?php
                        $food_cost = $wr->order ? max(0.0, $wr->order->order_amount - $wr->order->delivery_charge - ($wr->order->dm_tips ?? 0)) : 0.0;
                        $debt_amortized = max(0.0, $food_cost - $wr['amount_paid']);
                        ?>
                        <tr>
                            <td scope="row">{{$k+$history->firstItem()}}</td>
                            <td>
                                <a href="{{ route('vendor.order.details', [$wr['order_id']]) }}" class="text-primary font-weight-bold">
                                    #{{ $wr['order_id'] }}
                                </a>
                            </td>
                            <td>
                                @if($wr->delivery_man)
                                    <span class="d-block font-weight-bold">{{ $wr->delivery_man->f_name }} {{ $wr->delivery_man->l_name }}</span>
                                    <small class="text-muted">{{ $wr->delivery_man->phone }}</small>
                                @else
                                    <span class="text-muted">{{ 'N / A' }}</span>
                                @endif
                            </td>
                            <td>{{ \App\CentralLogics\Helpers::format_currency($food_cost) }}</td>
                            <td>
                                @if($debt_amortized > 0)
                                    <span class="text-danger font-weight-bold">- {{ \App\CentralLogics\Helpers::format_currency($debt_amortized) }}</span>
                                @else
                                    <span class="text-muted">{{ \App\CentralLogics\Helpers::format_currency(0.0) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-success font-weight-bold">{{ \App\CentralLogics\Helpers::format_currency($wr['amount_paid']) }}</span>
                            </td>
                            <td>
                                <span class="d-block">{{ \App\CentralLogics\Helpers::time_date_format($wr['created_at'])}}</span>
                            </td>
                            <td>
                                <label class="badge badge-soft-success">{{'recibió'}}</label>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(count($history) === 0)
                    <div class="empty--data">
                        <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                        <h5>
                            {{'no se encontraron datos'}}
                        </h5>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-footer pt-0 border-0">
            {{$history->links()}}
        </div>
    </div>
@endsection
@push('script_2')
@endpush
