@extends('layouts.admin.app')

@section('title', 'Pedidos de Tootli Abastos')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-xl-10 col-md-9 col-sm-8 mb-3 mb-sm-0">
                    <h1 class="page-header-title text-capitalize m-0">
                        <span class="page-header-icon">
                            <img src="{{ asset('assets/admin/img/order.png') }}" class="w--26" alt="">
                        </span>
                        <span>
                            Tootli Abastos: Pedidos {{ translate('messages.' . $status) }}
                            <span class="badge badge-soft-dark ml-2">{{ $total }}</span>
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2 border-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 8px;">
                    <!-- Status Filter Tabs -->
                    <ul class="nav nav-tabs border-0" style="gap: 4px;">
                        @php
                            $tabDefs = [
                                'all'        => ['Todos',        'secondary'],
                                'pending'    => ['Pendientes',   'warning'],
                                'processing' => ['En Proceso',   'info'],
                                'delivered'  => ['Entregados',   'success'],
                                'canceled'   => ['Cancelados',   'danger'],
                            ];
                        @endphp
                        @foreach ($tabDefs as $tabKey => [$tabLabel, $tabClass])
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ $status === $tabKey ? 'active' : '' }}"
                                   href="{{ route('admin.abastos.order.list', [$tabKey]) }}"
                                   style="padding: 6px 14px;">
                                    {{ $tabLabel }}
                                    <span class="badge badge-soft-{{ $tabClass }} ml-1">
                                        {{ $statusCounts[$tabKey] ?? 0 }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Search -->
                    <div class="d-flex">
                        <form class="search-form min--260">
                            <div class="input-group input--group">
                                <input id="datatableSearch_" type="search" name="search" class="form-control h--40px"
                                    placeholder="Buscar por ID o Tienda" value="{{ request()->query('search') }}" aria-label="Buscar">
                                <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                            </div>
                        </form>
                        @if (request()->query('search'))
                            <a href="{{ route('admin.abastos.order.list', [$status]) }}" class="btn btn--primary ml-2">Reset</a>
                        @endif
                    </div>
                </div>
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table fz--14px">
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0">#</th>
                            <th class="border-0">ID de Pedido</th>
                            <th class="border-0">Fecha de Pedido</th>
                            <th class="border-0">Tienda / Vendedor</th>
                            <th class="text-center border-0">Items</th>
                            <th class="border-0">Monto Total</th>
                            <th class="text-center border-0">Estado de Pago</th>
                            <th class="text-center border-0">Estado del Pedido</th>
                            <th class="text-center border-0">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($orders as $key => $order)
                            <tr>
                                <td>{{ $key + $orders->firstItem() }}</td>
                                <td>
                                    <a class="text-body font-bold" href="{{ route('admin.abastos.order.details', [$order->id]) }}">
                                        #{{ $order->id }}
                                    </a>
                                </td>
                                <td>
                                    <div>{{ \App\CentralLogics\Helpers::date_format($order->created_at) }}</div>
                                    <div class="d-block text-uppercase fz-12px text-muted">
                                        {{ \App\CentralLogics\Helpers::time_format($order->created_at) }}
                                    </div>
                                </td>
                                <td>
                                    @if ($order->store)
                                        <div>
                                            <a class="text--title font-semibold" href="{{ route('admin.store.view', $order->store_id) }}">
                                                {{ $order->store->name }}
                                            </a>
                                        </div>
                                        @if($order->store && $order->store->vendor)
                                            <div class="fz-12px text-muted">{{ $order->store->vendor->f_name }} {{ $order->store->vendor->l_name }}</div>
                                        @endif
                                    @else
                                        <div class="text-muted">Tienda no encontrada</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $order->details->count() }}
                                </td>
                                <td>
                                    <div class="font-bold">
                                        {{ \App\CentralLogics\Helpers::format_currency($order->order_amount) }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($order->payment_status == 'paid')
                                        <span class="badge badge-soft-success">Pagado</span>
                                    @else
                                        <span class="badge badge-soft-danger">Pendiente</span>
                                    @endif
                                </td>
                                <td class="text-center text-capitalize">
                                    @if ($order->order_status == 'pending')
                                        <span class="badge badge-soft-info">Pendiente</span>
                                    @elseif ($order->order_status == 'confirmed')
                                        <span class="badge badge-soft-info">Confirmado</span>
                                    @elseif ($order->order_status == 'processing')
                                        <span class="badge badge-soft-warning">En Preparación</span>
                                    @elseif ($order->order_status == 'handover')
                                        <span class="badge badge-soft-warning">Listo para Entrega</span>
                                    @elseif ($order->order_status == 'delivered')
                                        <span class="badge badge-soft-success">Entregado</span>
                                    @elseif ($order->order_status == 'canceled')
                                        <span class="badge badge-soft-danger">Cancelado</span>
                                    @else
                                        <span class="badge badge-soft-secondary">{{ str_replace('_', ' ', $order->order_status) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn--container justify-content-center">
                                        <a class="btn btn-sm btn--warning btn-outline-warning action-btn"
                                            href="{{ route('admin.abastos.order.details', [$order->id]) }}">
                                            <i class="tio-invisible"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty--data">
                                        <img src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}" alt="Sin datos">
                                        <h5>No se encontraron pedidos de Tootli Abastos</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- End Table -->

            @if (count($orders) !== 0)
                <hr class="my-0">
                <div class="page-area px-4 pb-3">
                    <div class="d-flex align-items-center justify-content-end">
                        {!! $orders->withQueryString()->links() !!}
                    </div>
                </div>
            @endif
        </div>
        <!-- End Card -->
    </div>
@endsection
