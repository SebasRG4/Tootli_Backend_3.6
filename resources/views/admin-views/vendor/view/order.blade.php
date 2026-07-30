@extends('layouts.admin.app')

@section('title',$store->name."'s ".'Pedidos')

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{asset('assets/admin/css/croppie.css')}}" rel="stylesheet">

@endpush

@section('content')
<div class="content container-fluid">
    @include('admin-views.vendor.view.partials._header',['store'=>$store])
    <!-- Page Heading -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="order">
            <div class="row pt-2">
                <div class="col-md-12">
                    <div class="resturant-card-navbar">

                        <div class="order-info-item filter-on-click" data-type='all_orders' data-filter="filter" data-url="{{route('admin.store.view', ['store'=>$store->id, 'tab'=> 'order'])}}">
                            <div class="order-info-icon">
                                <img src="{{asset('assets/admin/img/navbar/all.png')}}" alt="public">
                            </div>
                            <h6 class="card-subtitle">{{'todo'}}<span class="amount text--primary">{{\App\Models\Order::where('store_id', $store->id)->StoreOrder()->count()}}</span></h6>
                        </div>
                        <span class="order-info-seperator"></span>
                        <div class="order-info-item filter-on-click"  data-filter="filter"  data-type="scheduled_orders" data-url="{{route('admin.store.view', ['store'=>$store->id, 'tab'=> 'order'])}}"  >
                            <div class="order-info-icon">
                                <img src="{{asset('assets/admin/img/navbar/schedule.png')}}" alt="public">
                            </div>
                            <h6 class="card-subtitle">{{'programado'}}<span class="amount text--warning">{{\App\Models\Order::Scheduled()->where('store_id', $store->id)->StoreOrder()->count()}}</span></h6>
                        </div>
                        <span class="order-info-seperator"></span>
                        <div class="order-info-item filter-on-click"   data-filter="filter"  data-type="pending_orders" data-url="{{route('admin.store.view', ['store'=>$store->id, 'tab'=> 'order'])}}" >
                            <div class="order-info-icon">
                                <img src="{{asset('assets/admin/img/navbar/pending.png')}}" alt="public">
                            </div>
                            <h6 class="card-subtitle">{{'Pendiente'}}<span class="amount text--info">{{\App\Models\Order::where(['order_status'=>'pending','store_id'=>$store->id])->StoreOrder()->OrderScheduledIn(30)->count()}}</span></h6>
                        </div>
                        <span class="order-info-seperator"></span>
                        <div class="order-info-item filter-on-click"  data-filter="filter"  data-type="delivered_orders" data-url="{{route('admin.store.view', ['store'=>$store->id, 'tab'=> 'order'])}}" >
                            <div class="order-info-icon">
                                <img src="{{asset('assets/admin/img/navbar/delivered.png')}}" alt="public">
                            </div>
                            <h6 class="card-subtitle">{{'Entregado'}}<span class="amount text--success">{{\App\Models\Order::where(['order_status'=>'delivered', 'store_id'=>$store->id])->StoreOrder()->count()}}</span></h6>
                        </div>
                        <span class="order-info-seperator"></span>
                        <div class="order-info-item filter-on-click"  data-filter="filter"  data-type="canceled_orders" data-url="{{route('admin.store.view', ['store'=>$store->id, 'tab'=> 'order'])}}" >
                            <div class="order-info-icon">
                                <img src="{{asset('assets/admin/img/navbar/cancel.png')}}" alt="public">
                            </div>
                            <h6 class="card-subtitle">{{'Cancelado'}}<span class="amount text--success">{{\App\Models\Order::where(['order_status'=>'canceled', 'store_id'=>$store->id])->StoreOrder()->count()}}</span></h6>
                        </div>

                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card w-100">

                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">
                            </h5>
                            <form class="search-form">
                                <!-- Search -->

                                <div class="input-group input--group">
                                    <input id="datatableSearch_" type="search" value="{{ request()->search ?? null }}" name="search" class="form-control"
                                            placeholder="{{'ej: identificación del pedido'}}" aria-label="Search">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                                <!-- End Search -->
                            </form>
                            @if(request()->get('search'))
                            <button type="reset" class="btn btn--primary ml-2 location-reload-to-base" data-url="{{url()->full()}}">{{'reiniciar'}}</button>
                            @endif
                            <!-- Unfold -->
                            <div class="hs-unfold mr-2">
                                <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle min-height-40" href="javascript:;"
                                    data-hs-unfold-options='{
                                            "target": "#usersExportDropdown",
                                            "type": "css-animation"
                                        }'>
                                    <i class="tio-download-to mr-1"></i> {{ 'exportar' }}
                                </a>

                                <div id="usersExportDropdown"
                                    class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                                    <span class="dropdown-header">{{ 'opciones de descarga' }}</span>
                                    <a id="export-excel" class="dropdown-item" href="{{route('admin.order.store-export', ['type'=>'excel', 'store_id'=>$store->id , request()->getQueryString()])}}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ asset('assets/admin') }}/svg/components/excel.svg"
                                            alt="Image Description">
                                        {{ 'sobresalir' }}
                                    </a>
                                    <a id="export-csv" class="dropdown-item" href="{{route('admin.order.store-export', ['type'=>'csv', 'store_id'=>$store->id , request()->getQueryString() ])}}">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                            src="{{ asset('assets/admin') }}/svg/components/placeholder-csv-format.svg"
                                            alt="Image Description">
                                        .{{ 'csv' }}
                                    </a>

                                </div>
                            </div>
                            <!-- End Unfold -->
                        </div>
                    </div>
                        <div class="card-body p-0">
                            <!-- Table -->
                            <div class="table-responsive datatable-custom">
                                <table id="datatable"
                                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                    data-hs-datatables-options='{
                                    "columnDefs": [{
                                        "targets": [0],
                                        "orderable": false
                                    }],
                                    "order": [],
                                    "info": {
                                    "totalQty": "#datatableWithPaginationInfoTotalQty"
                                    },
                                    "search": "#datatableSearch",
                                    "entries": "#datatableEntries",
                                    "pageLength": 25,
                                    "isResponsive": false,
                                    "isShowPaging": false,
                                    "pagination": "datatablePagination"
                                }'>
                                    <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">
                                            {{'SL'}}
                                        </th>
                                        <th class="table-column-pl-0 border-0">{{'Pedido'}}</th>
                                        <th class="border-0">{{'fecha'}}</th>
                                        <th class="border-0">{{'Cliente'}}</th>
                                        <th class="border-0">{{'estado de pago'}}</th>
                                        <th class="border-0">{{'total'}}</th>
                                        <th class="border-0 text-center">{{'estado del pedido'}}</th>
                                        <th class="border-0 text-center">{{'comportamiento'}}</th>
                                    </tr>
                                    </thead>

                                    <tbody id="set-rows">
                                    @foreach($orders as $key=>$order)

                                        <tr class="status-{{$order['order_status']}} class-all">
                                            <td class="">
                                                {{$key+ $orders->firstItem()}}
                                            </td>
                                            <td class="table-column-pl-0">
                                                <a href="{{route('admin.order.details',['id'=>$order['id']])}}">{{$order['id']}}</a>
                                            </td>
                                            <td>
                                                <div>
                                                    {{ \App\CentralLogics\Helpers::date_format($order['created_at']) }}
                                                </div>
                                                <div class="d-block text-uppercase">
                                                    {{ \App\CentralLogics\Helpers::time_format($order['created_at']) }}

                                                </div>
                                            </td>
                                            <td>
                                                @if($order->is_guest)
                                                @php($customer_details = json_decode($order['delivery_address'],true))
                                                <strong>{{$customer_details['contact_person_name']}}</strong>
                                                <div>{{$customer_details['contact_person_number']}}</div>

                                                @elseif($order->customer)
                                                <div>
                                                    <a title="{{$order->customer['f_name'].' '.$order->customer['l_name']}}" class="text-body text-capitalize"
                                                    href="{{route('admin.customer.view',[$order['user_id']])}}">
                                                        <div>
                                                            {{$order->customer['f_name'].' '.$order->customer['l_name']}}
                                                        </div>
                                                    </a>
                                                    <a href="tel:{{$order->customer['phone']}}">
                                                        <div>
                                                            {{$order->customer['phone']}}
                                                        </div>
                                                    </a>
                                                </div>
                                                @else
                                                    <label class="badge badge-danger">{{'datos de cliente no válidos'}}</label>
                                                @endif
                                            </td>
                                            <td>
                                                @if($order->payment_status=='paid')
                                                    <span class="badge badge-soft-success">
                                                    {{'pagado'}}
                                                    </span>
                                                @else
                                                    <span class="badge badge-soft-danger">
                                                    {{'no pagado'}}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{\App\CentralLogics\Helpers::format_currency($order['order_amount'])}}</td>
                                            <td class="text-capitalize text-center">
                                                @if($order['order_status']=='pending')
                                                    <span class="badge badge-soft-info">
                                                    {{'Pendiente'}}
                                                    </span>
                                                @elseif($order['order_status']=='confirmed')
                                                    <span class="badge badge-soft-info">
                                                    {{'confirmado'}}
                                                    </span>
                                                @elseif($order['order_status']=='processing')
                                                    <span class="badge badge-soft-warning">
                                                    {{'tratamiento'}}
                                                    </span>
                                                @elseif($order['order_status']=='out_for_delivery')
                                                    <span class="badge badge-soft-warning">
                                                    {{'En Camino de Entrega'}}
                                                    </span>
                                                @elseif($order['order_status']=='delivered')
                                                    <span class="badge badge-soft-success">
                                                    {{'Entregado'}}
                                                    </span>
                                                @else
                                                    <span class="badge badge-soft-danger">
                                                    {{str_replace('_',' ',$order['order_status'])}}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn--container justify-content-center">
                                                    <a class="btn action-btn btn--warning btn-outline-warning" href="{{route('admin.order.details',['id'=>$order['id']])}}"><i class="tio-visible"></i></a>
                                                    <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.order.generate-invoice',['id'=>$order['id']])}}"><i class="tio-print"></i></a>
                                                </div>
                                            </td>
                                        </tr>

                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- End Table -->

                            @if(count($orders) !== 0)
                            <hr>
                            @endif
                            <div class="page-area">
                                {!! $orders->withQueryString()->links() !!}
                            </div>
                            @if(count($orders) === 0)
                            <div class="empty--data">
                                <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
                                <h5>
                                    {{'no se encontraron datos'}}
                                </h5>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
    <!-- Page level plugins -->
    <script>
        "use strict";
        // Call the dataTables jQuery plugin
        $(document).ready(function () {
            $('#dataTable').DataTable();

            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('change', function () {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function () {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });

            $('#search-form').on('submit', function () {
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.order.store-search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#set-rows').html(data.view);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
        });

        $(".filter-on-click").on("click", function () {
    const type = $(this).data('type');
    const url = $(this).data('url');
    const filter_by = $(this).data('filter');
    let nurl = new URL(url);
    nurl.searchParams.delete('page');
    nurl.searchParams.set(filter_by, type);
    location.href = nurl;
});
    </script>
@endpush
