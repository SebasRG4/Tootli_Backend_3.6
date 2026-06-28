@extends('layouts.admin.app')

@section('title', 'Detalle de Pedido Tootli Abastos #' . $order->id)

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{ route('admin.abastos.order.list', ['all']) }}">
                                    Pedidos Abastos
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Detalle del Pedido</li>
                        </ol>
                    </nav>

                    <div class="d-sm-flex align-items-sm-center">
                        <h1 class="page-header-title">Pedido #{{ $order->id }}</h1>
                        <span class="badge badge-soft-success ml-sm-3 text-capitalize">
                            Tootli Abastos
                        </span>
                        <span class="ml-2 ml-sm-3">
                            <i class="tio-date-range"></i> {{ \App\CentralLogics\Helpers::date_format($order->created_at) }} {{ \App\CentralLogics\Helpers::time_format($order->created_at) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row" id="printableArea">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <!-- Card -->
                <div class="card mb-3">
                    <!-- Header -->
                    <div class="card-header border-0 align-items-center">
                        <h4 class="card-header-title">
                            Detalles del Pedido de Insumos
                            <span class="badge badge-soft-dark rounded-circle ml-1">{{ $order->details->count() }}</span>
                        </h4>
                    </div>
                    <!-- End Header -->

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Detalle del Producto</th>
                                    <th>Precio</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($order->details as $detail)
                                    @if (isset($detail->item))
                                        <tr>
                                            <td>
                                                <div class="media">
                                                    <div class="avatar avatar-xl mr-3">
                                                        <img class="img-fluid rounded onerror-image"
                                                            src="{{ $detail->item->image_full_url }}"
                                                            data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                                            alt="Image Description">
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="text-hover-primary mb-0">{{ $detail->item->name }}</h5>
                                                        @if (isset($detail->variation) && count(json_decode($detail->variation, true)) > 0)
                                                            <div class="font-size-sm text-body">
                                                                <strong>Variación:</strong>
                                                                @foreach(json_decode($detail->variation, true)[0] as $key => $val)
                                                                    @if ($key != 'price')
                                                                        <span>{{ $key }} : {{ $val }}</span>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ \App\CentralLogics\Helpers::format_currency($detail->price) }}
                                            </td>
                                            <td class="text-center">
                                                {{ $detail->quantity }}
                                            </td>
                                            <td class="text-right">
                                                {{ \App\CentralLogics\Helpers::format_currency($detail->price * $detail->quantity) }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- End Table -->

                    <hr class="my-0">

                    <div class="card-body">
                        <div class="row justify-content-md-end">
                            <div class="col-md-9 col-lg-8">
                                <dl class="row text-sm-right">
                                    <dt class="col-sm-6 text-muted">Subtotal:</dt>
                                    <dd class="col-sm-6 font-bold">
                                        {{ \App\CentralLogics\Helpers::format_currency($order->order_amount - $order->total_tax_amount) }}
                                    </dd>
                                    <dt class="col-sm-6 text-muted">IVA (16%):</dt>
                                    <dd class="col-sm-6 font-bold">
                                        {{ \App\CentralLogics\Helpers::format_currency($order->total_tax_amount) }}
                                    </dd>
                                    <dt class="col-sm-6 text-muted">Total:</dt>
                                    <dd class="col-sm-6 font-bold text-success fz-18px">
                                        {{ \App\CentralLogics\Helpers::format_currency($order->order_amount) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card mb-3">
                    <div class="card-header border-0">
                        <h4 class="card-header-title">Estado del Pedido</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.abastos.order.status-update', [$order->id]) }}" method="post" id="status-form">
                            @csrf
                            <div class="form-group">
                                <label for="status">Cambiar Estado:</label>
                                <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                                    <option value="pending" {{ $order->order_status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="confirmed" {{ $order->order_status == 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                                    <option value="processing" {{ $order->order_status == 'processing' ? 'selected' : '' }}>En Preparación</option>
                                    <option value="handover" {{ $order->order_status == 'handover' ? 'selected' : '' }}>Listo para Entrega</option>
                                    <option value="delivered" {{ $order->order_status == 'delivered' ? 'selected' : '' }}>Entregado</option>
                                    <option value="canceled" {{ $order->order_status == 'canceled' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>
                        </form>

                        <div class="mt-3">
                            <span class="font-semibold text-dark">Método de Pago:</span>
                            <span class="text-capitalize text-muted ml-2">
                                @if($order->payment_method == 'cash_on_delivery')
                                    Pago en Entrega
                                @elseif($order->payment_method == 'wallet')
                                    Descontado de Saldo
                                @else
                                    {{ str_replace('_', ' ', $order->payment_method) }}
                                @endif
                            </span>
                        </div>
                        <div class="mt-2">
                            <span class="font-semibold text-dark">Estado de Pago:</span>
                            @if ($order->payment_status == 'paid')
                                <span class="badge badge-soft-success ml-2">Pagado</span>
                            @else
                                <span class="badge badge-soft-danger ml-2">Pendiente</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Buyer Store Info Card -->
                <div class="card mb-3">
                    <div class="card-header border-0">
                        <h4 class="card-header-title">Tienda Compradora (Cliente)</h4>
                    </div>
                    <div class="card-body">
                        @if ($order->store)
                            <div class="media align-items-center">
                                <div class="avatar avatar-circle mr-3">
                                    <img class="avatar-img onerror-image"
                                        src="{{ $order->store->logo_full_url }}"
                                        data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                                        alt="Logo">
                                </div>
                                <div class="media-body">
                                    <span class="text-body font-bold text-hover-primary d-block">
                                        {{ $order->store->name }}
                                    </span>
                                    <span class="d-block text-muted fz-12px">Tienda ID: #{{ $order->store->id }}</span>
                                    <span class="d-block text-muted fz-12px">Teléfono: {{ $order->store->phone }}</span>
                                    <span class="d-block text-muted fz-12px">Dirección: {{ $order->store->address }}</span>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">Tienda no disponible</div>
                        @endif
                    </div>
                </div>

                <!-- Seller Store Info Card -->
                <div class="card mb-3">
                    <div class="card-header border-0">
                        <h4 class="card-header-title">Tienda Vendedora (Tootli Abastos)</h4>
                    </div>
                    <div class="card-body">
                        @if ($order->user_id && $order->user_id != $order->store_id && $order->seller_store)
                            <div class="media align-items-center mb-3">
                                <div class="avatar avatar-circle mr-3">
                                    <img class="avatar-img onerror-image"
                                        src="{{ $order->seller_store->logo_full_url }}"
                                        data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                                        alt="Logo">
                                </div>
                                <div class="media-body">
                                    <span class="text-body font-bold text-hover-primary d-block">
                                        {{ $order->seller_store->name }}
                                    </span>
                                    <span class="d-block text-muted fz-12px">Tienda ID: #{{ $order->seller_store->id }}</span>
                                    <span class="d-block text-muted fz-12px">Teléfono: {{ $order->seller_store->phone }}</span>
                                    <span class="d-block text-muted fz-12px">Dirección: {{ $order->seller_store->address }}</span>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning py-2 mb-3">
                                <i class="tio-warning-outined"></i> Tienda vendedora no asignada. Este pedido se muestra en el Home del comprador.
                            </div>
                        @endif

                        <!-- Formulario de Asignación / Cambio de Tienda Vendedora -->
                        <form action="{{ route('admin.abastos.order.assign-seller-store', [$order->id]) }}" method="post">
                            @csrf
                            <div class="form-group mb-0">
                                <label for="store_id" class="fz-12px font-semibold text-dark">Asignar/Cambiar Tienda Vendedora:</label>
                                <div class="input-group">
                                    <select name="store_id" id="store_id" class="form-control form-control-sm" required>
                                        <option value="" disabled selected>Seleccione una tienda...</option>
                                        @foreach($groceryStores as $gStore)
                                            <option value="{{ $gStore->id }}" {{ ($order->store_id == $gStore->id || (isset($order->seller_store) && $order->seller_store->id == $gStore->id)) ? 'selected' : '' }}>
                                                {{ $gStore->name }} (ID: #{{ $gStore->id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-sm btn-primary">Asignar</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Customer Info Card -->
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="card-header-title">Comprador (Representante)</h4>
                    </div>
                    <div class="card-body">
                        @if ($order->store && $order->store->vendor)
                            <div class="media align-items-center">
                                <div class="avatar avatar-circle mr-3">
                                    <img class="avatar-img onerror-image"
                                        src="{{ $order->store->vendor->image_full_url }}"
                                        data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                                        alt="Foto">
                                </div>
                                <div class="media-body">
                                    <span class="text-body font-bold text-hover-primary d-block">
                                        {{ $order->store->vendor->f_name }} {{ $order->store->vendor->l_name }}
                                    </span>
                                    <span class="d-block text-muted fz-12px">Teléfono: {{ $order->store->vendor->phone }}</span>
                                    <span class="d-block text-muted fz-12px">Email: {{ $order->store->vendor->email }}</span>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">Información no disponible</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
