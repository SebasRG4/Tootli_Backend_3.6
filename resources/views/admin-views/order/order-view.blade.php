@extends('layouts.admin.app')

@section('title', 'Detalles del pedido')

@push('css_or_js')

    <style type="text/css" media="print">
  .addon-quantity-input {
    display: none;
}
.visibility-visible {
    display: flex !important;
}

    </style>
@endpush




@section('content')
    <?php
    $deliverman_tips = 0;
    $campaign_order = isset($order?->details[0]?->item_campaign_id )  ? true : false;
    $reasons=\App\Models\OrderCancelReason::where('status', 1)->where('user_type' ,'admin' )->get();
    $tax_included =0;
    $max_processing_time = $order->store?explode('-', $order->store['delivery_time'])[0]:0;
    ?>
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">
                        <span class="page-header-icon">
                            <img src="{{ asset('assets/admin/img/shopping-basket.png') }}" class="w--20"
                                 alt="">
                        </span>
                        <span>
                            {{ 'detalles del pedido' }} <span
                                class="badge badge-soft-dark rounded-circle ml-1">{{ $order->details->count() }}</span>
                        </span>
                        <input type="hidden" value="{{ $order?->distance }}" name="distance">
                    </h1>
                </div>

                <div class="col-sm-auto">
                    <a class="btn-icon btn-sm btn-soft-secondary rounded-circle mr-1"
                       href="{{ route('admin.order.details', [$order['id'] - 1]) }}" data-toggle="tooltip"
                       data-placement="top" title="{{ 'Orden anterior' }}">
                        <i class="tio-chevron-left"></i>
                    </a>
                    <a class="btn-icon btn-sm btn-soft-secondary rounded-circle"
                       href="{{ route('admin.order.details', [$order['id'] + 1]) }}" data-toggle="tooltip"
                       data-placement="top" title="{{ 'Próximo pedido' }}">
                        <i class="tio-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- Page Header -->



        @php
            $refund_amount = $order->order_amount - $order->delivery_charge - $order->dm_tips;
        @endphp
        <div class="row flex-xl-nowrap" id="printableArea">
            <div class="col-lg-8 order-print-area-left">
                <!-- Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header border-0 align-items-start flex-wrap">
                        <div class="order-invoice-left d-flex d-sm-block justify-content-between">
                            <div>
                                <h1 class="page-header-title d-flex align-items-center __gap-5px">
                                    {{ 'Pedido' }} #{{ $order['id'] }}
                                    @if ($campaign_order)
                                        <span class="badge badge-soft-success ml-sm-3">
                                            {{ 'orden de campaña' }}
                                        </span>
                                    @endif
                                    @if (!empty($order->tootli_direct))
                                        <span class="badge badge-soft-dark ml-sm-3">{{ 'insignia de pedido directo de tootli' }}</span>
                                    @endif
                                    @if ($order->edited)
                                        <span class="badge badge-soft-dark ml-sm-3">
                                            {{ 'editado' }}
                                        </span>
                                    @endif
                                </h1>
                                <span class="mt-2 d-block d-flex align-items-center __gap-5px">
                                    <i class="tio-date-range"></i>
                                    {{ date('d M Y ' . config('timeformat'), strtotime($order['created_at'])) }}
                                </span>

                                <h6 class="mt-2 pt-1 mb-2 fw-medium d-flex align-items-center __gap-5px">
                                    <i class="tio-shop"></i>
                                    <span>{{ 'Negocio' }}</span> <span>:</span> <span
                                        class="badge text-body bg-light2 py-1 px-2 font-weight-normal">{{ Str::limit($order->store ? $order->store->name : 'tienda eliminada!', 25, '...') }}</span>
                                </h6>
                                @if ($order->schedule_at && $order->scheduled)
                                    <h6 class="text-capitalize d-flex align-items-center __gap-5px">
                                        <span>{{ 'programado en' }}</span>
                                        <span>:</span> <label
                                            class="fz--10 badge badge-soft-warning">{{ date('d M Y ' . config('timeformat'), strtotime($order['schedule_at'])) }}</label>
                                    </h6>
                                    @if ($order->delivery_time_window)
                                        <h6 class="text-capitalize d-flex align-items-center __gap-5px">
                                            <span>{{ 'ventana de tiempo de entrega' }}</span>
                                            <span>:</span> <label class="fz--10 badge badge-soft-info">{{ $order->delivery_time_window }}</label>
                                        </h6>
                                        <div class="info-notes-bg px-3 color-222324CC py-2 rounded fs-12 gap-2 mt-2" style="background-color: #ffeeba; border: 1px solid #ffeeba; color: #856404; width: 100%;">
                                            ⚠️ <strong>{{ 'advertencia de orden programada' }}:</strong>
                                            {{ 'prepárate para estar listo por' }} <strong>{{ date('h:i A', strtotime($order->schedule_at)) }}</strong> {{ 'a más tardar' }}.
                                        </div>
                                    @endif
                                @endif
                                @if ($order->coupon)
                                    <h6 class="text-capitalize d-flex align-items-center __gap-5px"><span>{{ 'cupón' }}</span>
                                        <span>:</span> <label class="fz--10 badge badge-soft-primary">{{ $order->coupon_code }}
                                            ({{ translate('messages.' . $order->coupon->coupon_type) }})</label>
                                    </h6>
                                @endif
                                <div class="hs-unfold mt-1">
                                    <h5>
                                        <button
                                            class="btn order--details-btn-sm btn--primary btn-outline-primary btn--sm font-regular d-flex align-items-center __gap-5px"
                                            data-toggle="modal" data-target="#locationModal"><i class="tio-poi"></i>
                                            {{ 'mostrar ubicaciones en el mapa' }}</button>
                                    </h5>
                                </div>
                                @if($order['cancellation_reason'])
                                    <h6 class="text-capitalize my-2 ml-2">
                                        <span class="text-danger">{{ 'Cancelado por' }} :</span>
                                        {{ $order['canceled_by'] }}
                                    </h6>
                                    <h6 class=" my-2 ml-2">
                                        <span class="text-danger">{{ 'motivo de cancelación del pedido' }} :</span>
                                        {{ $order['cancellation_reason'] }}
                                    </h6>
                                @endif
                                @if ($order['unavailable_item_note'])
                                    <h6 class="w-100 badge-soft-warning py-1 px-2 rounded">
                                        <span class="text-dark">
                                            {{ 'nota de artículo no disponible del pedido' }} :
                                        </span>
                                        {{ $order['unavailable_item_note'] }}
                                    </h6>
                                @endif
                                @if ($order['delivery_instruction'])
                                    <h6 class="w-100 badge-soft-warning py-1 px-2 rounded">
                                        <span class="text-dark">
                                            {{ 'instrucción de entrega del pedido' }} :
                                        </span>
                                        {{ $order['delivery_instruction'] }}
                                    </h6>
                                @endif
                                @if ($order['order_note'])
                                    <h6>
                                        {{ 'nota de pedido' }} :
                                        {{ $order['order_note'] }}
                                    </h6>
                                @endif

                                @if ($order['bring_change_amount'] > 0)
                                <div class="info-notes-bg px-3 color-222324CC py-2 rounded fs-12  gap-2 mt-2">
                                    {{ 'por favor trae' }} <strong class="text-title"> {{ \App\CentralLogics\Helpers::format_currency($order['bring_change_amount'])   }}</strong> {{ 'en cambio al realizar la entrega' }}.
                                </div>
                                @endif
                            </div>
                            <div class="d-sm-none">
                                <a class="btn btn--primary print--btn font-regular d-flex align-items-center __gap-5px"
                                   href={{ route('admin.order.generate-invoice', [$order['id']]) }}>
                                    <i class="tio-print mr-sm-1"></i> <span>{{ 'imprimir factura' }}</span>
                                </a>
                            </div>
                        </div>
                        <div class="order-invoice-right mt-3 mt-sm-0">
                            <div class="btn--container ml-auto align-items-center justify-content-end">

                                @if ( !$editing && in_array($order->order_status, ['pending', 'confirmed', 'processing', 'accepted']) &&
                                        isset($order->store) && !$campaign_order &&
                                        $order->prescription_order == 0 && count($order?->payments) == 0 && $order?->ref_bonus_amount == 0 && $order?->flash_admin_discount_amount == 0 && ($order->payment_method == 'cash_on_delivery'))
                                    <button class="btn btn-sm btn--danger btn-outline-danger font-regular edit-order" type="button">
                                        <i class="tio-edit"></i> {{ 'editar' }}
                                    </button>
                                @endif

                                <form action="{{ route('admin.order.apply-debt', ['id' => $order['id']]) }}" method="POST" class="d-none d-sm-block">
                                    @csrf
                                    <input type="hidden" name="type" value="full">
                                    <button type="submit" class="btn btn-sm btn--danger font-regular" onclick="return confirm('¿Confirmas aplicar la deuda por el monto COMPLETO al usuario?');">
                                        🚨 Monto completo
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn--warning font-regular d-none d-sm-block ml-2" data-toggle="modal" data-target="#customDebtModal">
                                    🚨 Monto personalizado
                                </button>

                                <a class="btn btn--primary print--btn font-regular d-none d-sm-block ml-2"
                                   href={{ route('admin.order.generate-invoice', [$order['id']]) }}>
                                    <i class="tio-print mr-sm-1"></i> <span>{{ 'imprimir factura' }}</span>
                                </a>
                            </div>
                            <div class="text-right mt-3 order-invoice-right-contents text-capitalize">
                                <h6>
                                    <span>{{ 'estado' }}</span> <span>:</span>
                                    @if ($order['order_status'] == 'pending')
                                        <span class="badge bg-opacity-theme-10 font-weight-normal theme-clr ml-2 ml-sm-3 text-capitalize">
                                            {{ 'Pendiente' }}
                                        </span>
                                    @elseif($order['order_status'] == 'confirmed')
                                        <span class="badge badge-soft-info ml-2 ml-sm-3 text-capitalize">
                                            {{ 'confirmado' }}
                                        </span>
                                    @elseif($order['order_status'] == 'processing')
                                        <span class="badge badge-soft-warning ml-2 ml-sm-3 text-capitalize">
                                            {{ 'tratamiento' }}
                                        </span>
                                    @elseif($order['order_status'] == 'picked_up')
                                        <span class="badge badge-soft-warning ml-2 ml-sm-3 text-capitalize">
                                            {{ 'En Camino de Entrega' }}
                                        </span>
                                    @elseif($order['order_status'] == 'delivered')
                                        <span class="badge badge-soft-success ml-2 ml-sm-3 text-capitalize">
                                            {{ 'Entregado' }}
                                        </span>
                                    @elseif($order['order_status'] == 'failed')
                                        <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                                            {{ 'pago fallido' }}
                                        </span>
                                    @elseif($order['order_status'] == 'partial_delivered')
                                        <span class="badge badge-soft-primary ml-2 ml-sm-3 text-capitalize">
                                            {{ 'entrega parcial' }}
                                        </span>
                                    @else
                                        <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                                            {{ translate(str_replace('_', ' ', $order['order_status'])) }}
                                        </span>
                                    @endif
                                </h6>
                                @php
                                    $has_pending_minutes_items = false;
                                    foreach($order->details as $detail) {
                                        $item_details = json_decode($detail->item_details, true);
                                        if(isset($item_details['delivery_time_type']) && $item_details['delivery_time_type'] == 'minutes' && $detail->delivery_status == 'pending') {
                                            $has_pending_minutes_items = true;
                                            break;
                                        }
                                    }
                                @endphp
                                @if($has_pending_minutes_items && in_array($order->order_status, ['processing', 'picked_up', 'handover', 'confirmed']))
                                    <div class="mt-2 text-right">
                                        <form action="{{ route('admin.order.partial-delivery', [$order['id']]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn--primary">
                                                {{ 'entregar minutos artículos' }}
                                            </button>
                                        </form>
                                    </div>
                                @endif
                                <h6 class="text-capitalize">
                                    <span>{{ 'método de pago' }}</span> <span>:</span>
                                    <span>{{ translate(str_replace('_', ' ', $order['payment_method'])) }}</span>
                                </h6>
                                
                                @if($order->otp)
                                    <h6 class="text-danger mt-2">
                                        <span>Código de Verificación OTP</span> <span>:</span>
                                        <strong>{{ $order->otp }}</strong>
                                        <br>
                                        <small class="text-muted text-lowercase" style="font-size: 11px;">Código de verificación, no compartirlo si no es necesario</small>
                                    </h6>
                                @endif

                                <!-- offline_payment -->
                                @if($order?->offline_payments)
                                    <span>{{ 'Verificación de pago' }}</span> <span>:</span>
                                    @if ($order?->offline_payments?->status == 'pending')
                                        <span class="badge bg-opacity-theme-10 font-weight-normal theme-clr ml-2 ml-sm-3 text-capitalize">
                                                {{ 'Pendiente' }}
                                            </span>
                                    @elseif ($order?->offline_payments?->status == 'verified')
                                        <span class="badge badge-soft-success ml-2 ml-sm-3 text-capitalize">
                                                {{ 'verificado' }}
                                            </span>
                                    @elseif ($order?->offline_payments?->status == 'denied')
                                        <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                                                {{ 'denegado' }}
                                            </span>
                                    @endif

                                    @foreach (json_decode($order->offline_payments->payment_info) as $key=>$item)
                                        @if ($key != 'method_id')
                                            <h6 class="">
                                                <div class="d-flex justify-content-sm-end text-capitalize mt-2">
                                                    <span class="title-color">{{translate($key)}} :</span>
                                                    <strong>{{ $item }}</strong>
                                                </div>
                                            </h6>
                                        @endif
                                    @endforeach
                                @endif

                                <h6 class="">
                                    @if ($order['transaction_reference'] == null)
                                        <span>{{ 'código de referencia' }}</span> <span>:</span>
                                        <button class="btn btn-outline-primary btn-sm py-half fs-12" data-toggle="modal"
                                                data-target=".bd-example-modal-sm">
                                            {{ 'agregar' }}
                                        </button>
                                    @else
                                        <span>{{ 'código de referencia' }}</span> <span>:</span>
                                        <span>{{ $order['transaction_reference'] }}</span>
                                    @endif
                                </h6>

                                <h6 class="text-capitalize">
                                    <span>{{ 'Tipo de orden' }}</span>
                                    <span>:</span> <label
                                        class="fz--10 badge text-body bg-light2 py-1 px-2 font-weight-normal m-0">{{ translate(str_replace('_', ' ', $order['order_type'])) }}</label>
                                </h6>
                                <h6>
                                    <span>{{ 'estado de pago' }}</span> <span>:</span>
                                    @if ($order['payment_status'] == 'paid')
                                        <span class="badge badge-soft-success ml-sm-3">
                                            {{ 'pagado' }}
                                        </span>
                                    @elseif ($order['payment_status'] == 'partially_paid')

                                        @if ($order->payments()->where('payment_status','unpaid')->exists())
                                            <strong class="text-danger">{{ 'parcialmente pagado' }}</strong>
                                        @else
                                            <strong class="text-success">{{ 'pagado' }}</strong>
                                        @endif
                                    @else
                                        <strong class="text-danger">{{ 'no pagado' }}</strong>
                                    @endif

                                </h6>
                                @if ($order->store && $order->store->module->module_type == 'food')
                                    <h6>
                                        <span>{{ 'cuchillería' }}</span> <span>:</span>
                                        @if ($order['cutlery'] == '1')
                                            <span class="badge badge-soft-success ml-sm-3">
                                            {{ 'Sí' }}
                                        </span>
                                        @else
                                            <span class="badge badge-soft-danger ml-sm-3">
                                            {{ 'No' }}
                                        </span>
                                        @endif

                                    </h6>
                                @endif
                                @if ($order->order_attachment)
                                    @php
                                        $order_images = json_decode($order->order_attachment,true);
                                    @endphp
                                    <h5 class="text-dark">
                                        <span>{{ 'prescripción' }}</span> <span>:</span>
                                    </h5>
                                    <div class="d-flex flex-wrap flex-md-row-reverse" style="gap:15px">
                                        @foreach ($order_images as $key => $item)
                                            @php($item = is_array($item)?$item:['img'=>$item,'storage'=>'public'])
                                            <div>
                                                <button class="btn w-100 px-0" data-toggle="modal"
                                                        data-target="#prescriptionimagemodal{{ $key }}"
                                                        title="{{ 'archivo adjunto de pedido' }}">
                                                    <div class="gallary-card ml-auto">
                                                        <img  src="{{\App\CentralLogics\Helpers::get_full_url('order', $item['img'], $item['storage']??'public') }}"
                                                              alt="{{ 'prescripción' }}"
                                                              class="initial--22 object-cover">
                                                    </div>
                                                </button>
                                            </div>
                                            <div class="modal fade" id="prescriptionimagemodal{{ $key }}" tabindex="-1"
                                                 role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title" id="myModalLabel">
                                                                {{ 'prescripción' }}</h4>
                                                            <button type="button" class="close"
                                                                    data-dismiss="modal"><span
                                                                    aria-hidden="true">&times;</span><span
                                                                    class="sr-only">{{ 'Cancelar' }}</span></button>
                                                        </div>
                                                        <div class="modal-body scroll-bar">
                                                            <img  src="{{\App\CentralLogics\Helpers::get_full_url('order', $item['img'], $item['storage']??'public') }}"
                                                                  class="initial--22 w-100">
                                                        </div>
                                                        @php($storage = $item['storage']??'public')
                                                        @php($file = $storage == 's3'?base64_encode('order/' . $item['img']):base64_encode('public/order/' . $item['img']))
                                                        <div class="modal-footer">
                                                            <a class="btn btn-primary"
                                                               href="{{ route('admin.file-manager.download', [$file,$storage]) }}"><i
                                                                    class="tio-download"></i>
                                                                {{ 'descargar' }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    <div class="card-body px-0">
                        <!-- item cart -->
                        @if ($editing && !$campaign_order)
                            <hr>
                            <div class="row  px-4 py-5">
                                <div class="col-12">
                                    <div class="row justify-content-end">
                                        <div class="col-sm-6">
                                            <form id="search-form">
                                                <!-- Search -->
                                                <div class="input-group input--group">
                                                    <input id="datatableSearch" type="search"
                                                           value="{{ $keyword ? $keyword : '' }}" name="search"
                                                           class="form-control h--45px" placeholder="Search here"
                                                           aria-label="Search here">
                                                    <button class="btn btn--secondary h--45px"><i
                                                            class="tio-search"></i></button>
                                                </div>
                                                <!-- End Search -->
                                            </form>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="input-group header-item w-100">
                                                <select name="category" id="category"
                                                        class="form-control js-select2-custom mx-1 set-category-filter"
                                                        title="{{ 'seleccionar categoría' }}">
                                                    <option value="">{{ 'todas las categorias' }}
                                                    </option>
                                                    @foreach ($categories as $item)
                                                        <option value="{{ $item->id }}"
                                                            {{ $category == $item->id ? 'selected' : '' }}>
                                                            {{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-5" id="items">
                                    <div class="row g-3 mb-auto justify-content-center">
                                        @foreach ($products as $product)
                                            <div class="order--item-box item-box">
                                                @include('admin-views.order.partials._single_product', [
                                                    'product' => $product,
                                                    'store_data' => $order->store,
                                                ])
                                                {{-- <hr class="d-sm-none"> --}}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-12">
                                    {!! $products->withQueryString()->links() !!}
                                </div>
                            </div>
                        @endif


                                <?php
                                $coupon = null;
                                $total_addon_price = 0;
                                $product_price = 0;
                                if ($order->prescription_order == 1) {
                                    $product_price = $order['order_amount'] - $order['delivery_charge'] - $order['total_tax_amount'] - $order['dm_tips'] - $order['additional_charge'] + $order['store_discount_amount'];
                                    if($order->tax_status == 'included'){
                                        $product_price += $order['total_tax_amount'];
                                    }
                                }
                                $store_discount_amount = 0;
                                $admin_flash_discount_amount = $order['flash_admin_discount_amount'];
                                $ref_bonus_amount = $order['ref_bonus_amount'];
                                $extra_packaging_amount = $order['extra_packaging_amount'];
                                $store_flash_discount_amount = $order['flash_store_discount_amount'];
                                $additional_charge = $order['additional_charge'];
                                $del_c = $order['delivery_charge'];
                                if ($editing) {
                                    $del_c = $order['original_delivery_charge'];
                                }
                                if ($order->coupon_code) {
                                    $coupon = \App\Models\Coupon::where(['code' => $order['coupon_code']])->first();
                                    if ($editing && $coupon->coupon_type == 'free_delivery') {
                                        $del_c = 0;
                                        $coupon = null;
                                    }
                                }
                                $details = $order->details;
                                if ($editing) {
                                    $details = session('order_cart');
                                } else {
                                    foreach ($details as $key => $item) {
                                        $details[$key]->status = true;
                                    }
                                }
                                ?>
                            <div class="table-responsive pb-0">
                                <table
                                    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table dataTable no-footer mb-0">
                                    <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">{{ '#' }}</th>
                                        <th class="border-0">{{ 'detalles del artículo' }}</th>
                                        @if ($order->store && $order->store->module->module_type == 'food')
                                            <th class="border-0">{{ 'complementos' }}</th>
                                        @endif
                                        <th class="text-right  border-0">{{ 'precio' }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($details as $key => $detail)
                                        @if (isset($detail->item_id) && $detail->status)
                                                <?php
                                                if (!$editing) {
                                                    $detail->item = json_decode($detail->item_details, true);
                                                }
                                                $product = \App\Models\Item::where(['id' => data_get($detail->item,'id')])->first();
                                                        if(!$product){
                                                            $detail->item = json_decode($detail->item_details, true);
                                                        }

                                                ?>

                                            <tr>
                                                <td>
                                                    <!-- Static Count Number -->
                                                    <div>
                                                        {{ $key + 1 }}
                                                    </div>
                                                    <!-- Static Count Number -->
                                                </td>
                                                <td>
                                                    <div class="media media--sm">
                                                        @if ($editing)
                                                            <div class="avatar avatar-lg mr-3 cursor-pointer quick-view-cart-item" data-key="{{ $key }}"
                                                                 title="{{ 'haga clic para editar este elemento' }}">
                                                                    <span
                                                                        class="avatar-status avatar-lg-status avatar-status-dark"><i
                                                                            class="tio-edit"></i></span>
                                                                <img class="img-fluid rounded aspect-ratio-1 onerror-image"
                                                                     src="{{ $product?->image_full_url ??asset('assets/admin/img/100x100/2.png') }}"
                                                                     data-onerror-image="{{ asset('assets/admin/img/100x100/2.png') }}"
                                                                     alt="Image Description">
                                                            </div>
                                                        @else
                                                            <a class="avatar avatar-lg mr-3"
                                                               href="{{ route('admin.item.view', [$detail->item['id'],'module_id' => $order->module_id]) }}">
                                                                <img class="img-fluid rounded aspect-ratio-1 onerror-image"
                                                                     src="{{ $product?->image_full_url ?? asset('assets/admin/img/100x100/2.png') }}"
                                                                     data-onerror-image="{{ asset('assets/admin/img/100x100/2.png') }}"
                                                                     alt="Image Description">
                                                            </a>
                                                        @endif
                                                        <div class="media-body">
                                                            <div>
                                                                <strong class="line--limit-1 card-text font-medium">
                                                                    {{ $detail->item['name'] }}</strong>
                                                                <h6 class="card-text font-regular">
                                                                    {{ $detail['quantity'] }} x
                                                                    {{ \App\CentralLogics\Helpers::format_currency($detail['price']) }}
                                                                </h6>
                                                                @if ($order->store && $order->store->module->module_type == 'food')
                                                                    @if (isset($detail['variation']) ? json_decode($detail['variation'], true) : [])
                                                                        @foreach (json_decode($detail['variation'], true) as $variation)
                                                                            @if (isset($variation['name']) && isset($variation['values']))
                                                                                <span class="d-block text-capitalize">
                                                                                        <strong>
                                                                                            {{ $variation['name'] }} -
                                                                                        </strong>
                                                                                    </span>
                                                                                @foreach ($variation['values'] as $value)
                                                                                    <span
                                                                                        class="d-block text-capitalize">
                                                                                            &nbsp; &nbsp;
                                                                                            {{ $value['label'] }} :
                                                                                            <strong>{{ \App\CentralLogics\Helpers::format_currency($value['optionPrice']) }}</strong>
                                                                                        </span>
                                                                                @endforeach
                                                                            @else
                                                                                @if (isset(json_decode($detail['variation'], true)[0]))
                                                                                    <strong><u>
                                                                                            {{ 'Variación' }}
                                                                                            : </u></strong>
                                                                                    @foreach (json_decode($detail['variation'], true)[0] as $key1 => $variation)
                                                                                        <div
                                                                                            class="font-size-sm text-body">
                                                                                                <span>{{ $key1 }}
                                                                                                    : </span>
                                                                                            <span
                                                                                                class="font-weight-bold">{{ $variation }}</span>
                                                                                        </div>
                                                                                    @endforeach
                                                                                @endif
                                                                                {{-- @break --}}
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                @else
                                                                    @if (count(json_decode($detail['variation'], true)) > 0)
                                                                        <strong><u>{{ 'variación' }}
                                                                                :
                                                                            </u></strong>
                                                                    <?php
                                                                        $detailsVariation = isset(json_decode($detail['variation'], true)[0]) ? json_decode($detail['variation'], true)[0] : json_decode($detail['variation'], true);
                                                                    ?>
                                                                        @foreach ($detailsVariation as $key1 => $variation)
                                                                            @if ($key1 != 'stock' || ($order->store && config('module.' . $order->store->module->module_type)['stock']))
                                                                                <div class="font-size-sm text-body">
                                                                                        <span>{{ $key1 }} :
                                                                                        </span>
                                                                                    <span class="font-weight-bold">
                                                                                        {{ Str::limit(implode(', ', (array) $variation), 15, '...') }}
                                                                                    </span>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                @endif

                                                                @if(isset($detail->item['delivery_time_type']))
                                                                    <div class="mt-1 d-flex gap-1 align-items-center">
                                                                        <span class="badge badge-soft-info text-capitalize">
                                                                            {{ 'tipo de entrega' }}: {{ translate('messages.'.$detail->item['delivery_time_type']) }}
                                                                        </span>
                                                                        @if(isset($detail->item['store_delivery_time']))
                                                                            <span class="text-muted fs-12">
                                                                                ({{ $detail->item['store_delivery_time'] }} {{ $detail->item['delivery_time_type'] == 'minutes' ? 'mín.' : '' }})
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                @endif

                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                @if ($order->store && $order->store->module->module_type == 'food')
                                                    <td>
                                                        <div>
                                                            @foreach (json_decode($detail['add_ons'], true) as $key2 => $addon)
                                                                @if ($key2 == 0)
                                                                    <strong><u>{{ 'complementos' }} :
                                                                        </u></strong>
                                                                @endif
                                                                <div class="font-size-sm text-body">
                                                                        <span>{{ Str::limit($addon['name'], 20, '...') }}
                                                                            : </span>
                                                                    <span class="font-weight-bold">
                                                                            {{ $addon['quantity'] }} x
                                                                            {{ \App\CentralLogics\Helpers::format_currency($addon['price']) }}
                                                                        </span>
                                                                </div>
                                                                @php($total_addon_price += $addon['price'] * $addon['quantity'])
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                @endif
                                                <td class="text-right">
                                                    <div class="text-right">
                                                        @php($amount = $detail['price'] * $detail['quantity'])
                                                        <h5>{{ \App\CentralLogics\Helpers::format_currency($amount) }}</h5>
                                                    </div>
                                                    <div class="text-right mt-1">
                                                        @if($detail->delivery_status == 'delivered')
                                                            <span class="badge badge-soft-success">{{ 'Entregado' }}</span>
                                                        @else
                                                            <span class="badge badge-soft-warning">{{ 'Pendiente' }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>

                                            @php($product_price += $amount)

                                            @php($store_discount_amount += $detail['discount_on_item']  * ( $detail['discount_on_product_by'] == 'store_discount' ? 1 :$detail['quantity']  ))
                                            <!-- End Media -->


                                        @elseif(isset($detail->item_campaign_id) && $detail->status)
                                                <?php
                                                if (!$editing) {
                                                    $detail->campaign = json_decode($detail->item_details, true);
                                                }
                                                $campaign = \App\Models\ItemCampaign::where(['id' => $detail->campaign['id']])->first();
                                                ?>
                                            <tr>
                                                <td>
                                                    <!-- Static Count Number -->
                                                    <div>
                                                        {{ $key + 1 }}
                                                    </div>
                                                    <!-- Static Count Number -->
                                                </td>
                                                <td>
                                                    <div class="media media--sm">
                                                        @if ($editing)
                                                            <div class="avatar avatar-xl mr-3  cursor-pointer quick-view-cart-item" data-key="{{ $key }}"
                                                                 title="{{ 'haga clic para editar este elemento' }}">
                                                                    <span
                                                                        class="avatar-status avatar-lg-status avatar-status-dark"><i
                                                                            class="tio-edit"></i></span>
                                                                    <img class="img-fluid rounded onerror-image"
                                                                        src="{{ $campaign?->image_full_url ?? asset('assets/admin/img/900x400/img1.jpg') }}"
                                                                        data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                                                        alt="Image Description">
                                                                </div>
                                                            @else
                                                                <a class="avatar avatar-xl mr-3"
                                                                    href="{{ route('admin.campaign.view', ['item', $detail->campaign['id']]) }}">
                                                                    <img class="img-fluid rounded onerror-image"
                                                                        src="{{ $campaign?->image_full_url ?? asset('assets/admin/img/900x400/img1.jpg') }}"
                                                                        data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                                                        alt="Image Description">
                                                                </a>
                                                            @endif

                                                        <div class="media-body">
                                                            <div>
                                                                <strong
                                                                    class="line--limit-1">{{ Str::limit($detail->campaign['name'], 20, '...') }}</strong>

                                                                <h6>
                                                                    {{ $detail['quantity'] }} x
                                                                    {{ \App\CentralLogics\Helpers::format_currency($detail['price']) }}
                                                                </h6>
                                                                @if ($order->store && $order->store->module->module_type == 'food')
                                                                    @if (isset($detail['variation']) ? json_decode($detail['variation'], true) : [])
                                                                        @foreach (json_decode($detail['variation'], true) as $variation)
                                                                            @if (isset($variation['name']) && isset($variation['values']))
                                                                                <span class="d-block text-capitalize">
                                                                                        <strong>
                                                                                            {{ $variation['name'] }} -
                                                                                        </strong>
                                                                                    </span>
                                                                                @foreach ($variation['values'] as $value)
                                                                                    <span
                                                                                        class="d-block text-capitalize">
                                                                                            &nbsp; &nbsp;
                                                                                            {{ $value['label'] }} :
                                                                                            <strong>{{ \App\CentralLogics\Helpers::format_currency($value['optionPrice']) }}</strong>
                                                                                        </span>
                                                                                @endforeach
                                                                            @else
                                                                                @if (isset(json_decode($detail['variation'], true)[0]))
                                                                                    <strong><u>
                                                                                            {{ 'Variación' }}
                                                                                            : </u></strong>
                                                                                    @foreach (json_decode($detail['variation'], true)[0] as $key1 => $variation)
                                                                                        <div
                                                                                            class="font-size-sm text-body">
                                                                                                <span>{{ $key1 }}
                                                                                                    : </span>
                                                                                            <span
                                                                                                class="font-weight-bold">{{ $variation }}</span>
                                                                                        </div>
                                                                                    @endforeach
                                                                                @endif
                                                                                {{-- @break --}}
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                @else
                                                                    @if (count(json_decode($detail['variation'], true)) > 0)
                                                                        <strong><u>{{ 'variación' }}
                                                                                :</u></strong>
                                                                        @foreach (json_decode($detail['variation'], true)[0] as $key1 => $variation)
                                                                            @if ($key1 != 'stock' || ($order->store && config('module.' . $order->store->module->module_type)['stock']))
                                                                                <div class="font-size-sm text-body">
                                                                                        <span>{{ $key1 }} :
                                                                                        </span>
                                                                                    <span
                                                                                        class="font-weight-bold">{{ Str::limit($variation, 15, '...') }}</span>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                @if ($order->store && $order->store->module->module_type == 'food')
                                                    <td>
                                                        <div>
                                                            @foreach (json_decode($detail['add_ons'], true) as $key2 => $addon)
                                                                @if ($key2 == 0)
                                                                    <strong><u>{{ 'complementos' }} :
                                                                        </u></strong>
                                                                @endif
                                                                <div class="font-size-sm text-body">
                                                                        <span>{{ Str::limit($addon['name'], 20, '...') }}
                                                                            : </span>
                                                                    <span class="font-weight-bold">
                                                                            {{ $addon['quantity'] }} x
                                                                            {{ \App\CentralLogics\Helpers::format_currency($addon['price']) }}
                                                                        </span>
                                                                </div>
                                                                @php($total_addon_price += $addon['price'] * $addon['quantity'])
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                @endif
                                                <td class="text-right">
                                                    <div>
                                                        @php($amount = $detail['price'] * $detail['quantity'])
                                                        <h5>{{ \App\CentralLogics\Helpers::format_currency($amount) }}
                                                        </h5>
                                                    </div>
                                                </td>
                                            </tr>

                                            @php($product_price += $amount)

                                            @php($store_discount_amount += $detail['discount_on_item'] *  ( $detail['discount_on_product_by'] == 'store_discount' ?  1:$detail['quantity'] ))
                                            <!-- End Media -->

                                        @endif
                                    @endforeach
                                    </tbody>

                                </table>
                            </div>
                                <?php
                                $coupon_discount_amount = $order['coupon_discount_amount'];
                                $old_store_discount_amount =0;
                                $total_price = $product_price + $total_addon_price - $store_discount_amount - $coupon_discount_amount - $admin_flash_discount_amount - $ref_bonus_amount - $store_flash_discount_amount - $extra_packaging_amount;

                                $total_tax_amount = $order['total_tax_amount'];
                                if($order->tax_status == 'included'){
                                    $total_tax_amount=0;
                                }
                                $deliverman_tips = $order['dm_tips'];

                                if ($editing) {

                                    $store_discount = \App\CentralLogics\Helpers::get_store_discount($order->store);
                                    if (isset($store_discount)) {
                                        if ($product_price + $total_addon_price < $store_discount['min_purchase']) {
                                            $store_discount_amount = 0;
                                        }

                                        if ($store_discount_amount > $store_discount['max_discount'] && $store_discount_amount > $store_discount['max_discount']) {
                                            $old_store_discount_amount = $store_discount_amount;
                                            $store_discount_amount = $store_discount['max_discount'];
                                        }
                                      $store_discount_amount=  max($store_discount_amount,$old_store_discount_amount);
                                    }

                                    $coupon_discount_amount = $coupon ? \App\CentralLogics\CouponLogic::get_discount($coupon, $product_price + $total_addon_price - $store_discount_amount ) : $order['coupon_discount_amount'];

                                    $tax_amount = session()->get('edit_tax_amount');
                                    $total_price = $product_price + $total_addon_price - $store_discount_amount - $coupon_discount_amount;

                                    $total_tax_amount = $tax_amount;

                                    $total_tax_amount = round($total_tax_amount, 2);

                                    $tax_included = session()->get('edit_tax_included');
                                    if ($tax_included ==  1){
                                        $total_tax_amount=0;
                                    }

                                    $store_discount_amount = round($store_discount_amount, 2);

                                    if ($order?->store?->free_delivery) {
                                        $del_c = 0;
                                    }

                                    $free_delivery_over = \App\Models\BusinessSetting::where('key', 'free_delivery_over')->first()->value;
                                    if (isset($free_delivery_over)) {
                                        if ($free_delivery_over <= $product_price + $total_addon_price - $coupon_discount_amount - $store_discount_amount) {
                                            $del_c = 0;
                                        }
                                    }
                                    if ($order->order_type == 'take_away') {
                                        $del_c = 0;
                                    }
                                } else {
                                    $store_discount_amount = $order['store_discount_amount'];
                                }

                                ?>

                        <div class="mx-3">
                            <hr>
                        </div>
                        <div class="row justify-content-md-end mb-3 mt-4 mx-0">
                            <div class="col-md-12">
                                <dl class="row text-right">

                                    <dt class="col-6 color-8a8a8a fs-12">{{ 'precio de los artículos' }}:</dt>
                                    <dd class="col-6 text-dark fs-14">
                                        {{ \App\CentralLogics\Helpers::format_currency($product_price) }}</dd>
                                    @if ($order->store && $order->store->module->module_type == 'food')
                                        <dt class="col-6 color-8a8a8a fs-12">{{ 'costo adicional' }}:</dt>
                                        <dd class="col-6 text-dark fs-14">
                                            {{ \App\CentralLogics\Helpers::format_currency($total_addon_price) }}
                                            <hr>
                                        </dd>
                                    @endif

                                    <dt class="col-6 color-8a8a8a fs-12">{{ 'total parcial' }}
                                        @if ($order->tax_status == 'included' ||  $tax_included ==  1)
                                            ({{ 'IVA incluido' }})
                                        @endif
                                        :</dt>
                                    <dd class="col-6 text-dark fs-14">
                                        {{ \App\CentralLogics\Helpers::format_currency($product_price + $total_addon_price) }}
                                    </dd>
                                    <dt class="col-6 color-8a8a8a fs-12">{{ 'descuento' }}:</dt>
                                    <dd class="col-6 text-dark fs-14">
                                        - {{ \App\CentralLogics\Helpers::format_currency($store_discount_amount + $admin_flash_discount_amount  + $store_flash_discount_amount) }}
                                    </dd>



                                    <dt class="col-6 color-8a8a8a fs-12">{{ 'cupón de descuento' }}:</dt>
                                    <dd class="col-6 text-dark fs-14">
                                        - {{ \App\CentralLogics\Helpers::format_currency($coupon_discount_amount) }}
                                    </dd>
                                        @if ($ref_bonus_amount > 0)
                                            <dt class="col-6 color-8a8a8a fs-12">{{ 'Descuento por recomendación' }}:</dt>
                                            <dd class="col-6 text-dark fs-14">
                                                - {{ \App\CentralLogics\Helpers::format_currency($ref_bonus_amount) }}
                                            </dd>
                                        @endif
                                        @if ($order->tax_status == 'excluded' && $total_tax_amount > 0 || $order->tax_status == null  )
                                            {{-- @php($tax_a=0) --}}
                                            <dt class="col-6 color-8a8a8a fs-12">{{ 'iva/impuesto' }}:</dt>
                                            <dd class="col-6 text-right text-dark fs-14">
                                                +
                                                {{ \App\CentralLogics\Helpers::format_currency($total_tax_amount) }}
                                            </dd>

                                        @endif

                                         <dt class="col-6 color-8a8a8a fs-12">{{ 'tarifa de entrega' }}
                                             @if ($order->free_delivery_by == 'admin')
                                             <i class="tio-info-outined" data-toggle="tooltip" title="{{ 'La tarifa de envío es aplicable y será cubierta por el administrador.' }}"></i>

                                             @elseif ($order->free_delivery_by == 'vendor')
                                             <i class="tio-info-outined" data-toggle="tooltip" title="{{ 'La tarifa de envío es aplicable y será cubierta por el Proveedor.' }}"></i>
                                             @endif
                                                 :</dt>
                                         <dd class="col-6 text-dark fs-14">
                                             + {{ \App\CentralLogics\Helpers::format_currency($del_c) }}

                                         </dd>
                                    <dt class="col-6 color-8a8a8a fs-12">{{ 'consejos de repartidor' }}</dt>
                                    <dd class="col-6 text-dark fs-14">
                                        + {{ \App\CentralLogics\Helpers::format_currency($deliverman_tips) }}</dd>
                                    @if($order->additional_charge == 15)
                                         <dt class="col-6 color-8a8a8a fs-12">{{ 'carga multitienda' }}</dt>
                                     @else
                                         <dt class="col-6 color-8a8a8a fs-12">{{ \App\CentralLogics\Helpers::get_business_data('additional_charge_name')??'cargo adicional' }}</dt>
                                     @endif

                                     <dd class="col-6 text-dark fs-14">
                                         + {{ \App\CentralLogics\Helpers::format_currency($order->additional_charge) }}</dd>

                                    @if ($extra_packaging_amount > 0)
                                        <dt class="col-6 color-8a8a8a fs-12">{{ 'Cantidad de embalaje adicional' }}:</dt>
                                        <dd class="col-6 text-dark fs-14">
                                            + {{ \App\CentralLogics\Helpers::format_currency($extra_packaging_amount) }}
                                        </dd>
                                    @endif

                                    <div class="col-12 border-bottom pb-3 mb-3"></div>
                                    <dt class="col-6 text-dar text-bold fs-16">{{ 'total' }} {{ $order->tax_status == 'included' ? '('.'IVA incluido'.')'  :'' }} : </dt>
                                    <dd class="col-6 text-dark font-weight-bolder fs-16">

                                        {{ \App\CentralLogics\Helpers::format_currency($product_price + $del_c + $total_tax_amount + $total_addon_price + $deliverman_tips + $additional_charge - $coupon_discount_amount - $store_discount_amount - $admin_flash_discount_amount - $store_flash_discount_amount - $ref_bonus_amount +$extra_packaging_amount )  }}
                                    </dd>
                                    @if ($order?->payments)
                                        @foreach ($order?->payments as $payment)
                                            @if ($payment->payment_status == 'paid')
                                                @if ( $payment->payment_method == 'cash_on_delivery')

                                                    <dt class="col-6 color-8a8a8a fs-12">{{ 'Pagado en efectivo' }} ({{  'BACALAO'}}) :</dt>
                                                @else

                                                    <dt class="col-6 text-dark fs-14">{{ 'Pagado por' }} {{  translate($payment->payment_method)}} :</dt>
                                                @endif
                                            @else

                                                <dt class="col-6 color-8a8a8a fs-12">{{ 'Monto adeudado' }} ({{  $payment->payment_method == 'cash_on_delivery' ?  'BACALAO' : translate($payment->payment_method) }}) :</dt>
                                            @endif
                                            <dd class="col-6 text-right text-dark fs-14">
                                                {{ \App\CentralLogics\Helpers::format_currency($payment->amount) }}
                                            </dd>
                                        @endforeach
                                    @endif
                                </dl>
                                <!-- End Row -->
                            </div>
                            @if ($editing)
                                <div class="col-12">
                                    <div class="btn--container justify-content-end">
                                        <button class="btn btn-sm btn--reset cancel-edit-order" type="button" >{{ 'Cancelar' }}</button>
                                        <button class="btn btn-sm btn--primary submit-edit-order" type="button">{{ 'entregar' }}</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <!-- End Row -->
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4 order-print-area-right">
                @if ($order->order_status == 'canceled')

                    <div class="card mb-3">


                        <div class="card-body pt-2">

                            <ul class="delivery--information-single mt-3">
                                <li>
                                    <span class=" badge badge-soft-danger "> {{ 'Cancelar motivo' }} :</span>
                                    <span class="info">  {{ $order->cancellation_reason }} </span>
                                </li>
                                <hr class="w-100">
                                <li>
                                    <span class="name">{{ 'Cancelar nota' }} </span>
                                    <span class="info">  {{ $order->cancellation_note ?? 'N / A'}} </span>
                                </li>
                                <li>
                                    <span class="name">{{ 'Cancelado por' }} </span>
                                    <span class="info">  {{ translate($order->canceled_by) }} </span>
                                </li>
                                @if ($order->payment_status == 'paid' || $order->payment_status == 'partially_paid' )
                                    @if ( $order?->payments)
                                        @php( $pay_infos =$order->payments()->where('payment_status','paid')->get())
                                        @foreach ($pay_infos as $pay_info)
                                            <li>
                                                <span class="name">{{ 'Monto pagado por' }} {{ translate($pay_info->payment_method) }} </span>
                                                <span class="info">  {{ \App\CentralLogics\Helpers::format_currency($pay_info->amount)  }} </span>
                                            </li>
                                        @endforeach
                                    @else
                                        <li>
                                            <span class="name">{{ 'Monto pagado por' }} {{ translate($order->payment_method) }} </span>
                                            <span class="info ">  {{ \App\CentralLogics\Helpers::format_currency($order->order_amount)  }} </span>
                                        </li>
                                    @endif
                                @endif

                                @if ($order->payment_status == 'paid' || $order->payment_status == 'partially_paid')
                                    @if ( $order?->payments)
                                        @php( $amount =$order->payments()->where('payment_status','paid')->sum('amount'))
                                        <li>
                                            <span class="name">{{ 'Monto devuelto a la billetera' }} </span>
                                            <span class="info">  {{ \App\CentralLogics\Helpers::format_currency($amount)  }} </span>
                                        </li>
                                    @else
                                        <li>
                                            <span class="name">{{ 'Monto devuelto a la billetera' }} </span>
                                            <span class="info">  {{ \App\CentralLogics\Helpers::format_currency($order->order_amount)  }} </span>
                                        </li>
                                    @endif
                                @endif


                            </ul>
                        </div>
                    </div>

                @endif

                @include('admin-views.order.partials._order_audit_strike')

                @php($refund = \App\Models\BusinessSetting::where(['key' => 'refund_active_status'])->first())

                @if (!empty($order->refund))
                    @if (
                        $order->order_status == 'refund_requested' ||
                            $order->order_status == 'refunded' ||
                            $order->order_status == 'refund_request_canceled')
                        <div class="card mb-2">
                            <div class="card-header border-0 d-block text-center pb-0">
                                <h4 class="m-0">{{ 'Solicitud de reembolso' }} </h4>
                                <span>
                                    {{ date('d M Y ' . config('timeformat'), strtotime($order->refund->created_at)) }}
                                </span>

                                @if ($order->order_status == 'refund_requested')
                                    <span
                                        class="badge __badge badge-primary __badge-abs">{{ 'Pendiente' }}</span>
                                @elseif($order->order_status == 'refunded')
                                    <span
                                        class="badge __badge badge-info __badge-abs">{{ 'Reembolsado' }}</span>
                                @elseif($order->refund->order_status == 'refund_request_canceled')
                                    <span
                                        class="badge __badge-pill badge-danger __badge-abs">{{ 'rechazado' }}</span>
                                @endif

                            </div>
                            <div class="card-body pt-2">
                                <label class="input-label"
                                       for="exampleFormControlInput1">{{ 'imagen' }} : </label>
                                <div class="row g-3">
                                    @php($data = isset($order->refund->image) ? json_decode($order->refund->image, true) : 0)
                                    @if ($data)
                                        @foreach ($data as $key => $img)
                                            @php($img = is_array($img)?$img:['img'=>$img,'storage'=>'public'])
                                            <div class="col-3">
                                                <img class="img__aspect-1 rounded border w-100 onerror-image" data-toggle="modal"
                                                     data-target="#imagemodal{{ $key }}"
                                                     data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                                     src="{{ \App\CentralLogics\Helpers::get_full_url('refund',$img['img'],$img['storage']) }}">
                                            </div>
                                            <div class="modal fade" id="imagemodal{{ $key }}" tabindex="-1"
                                                 role="dialog" aria-labelledby="myModalLabel{{ $key }}"
                                                 aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"
                                                                id="myModalLabel{{ $key }}">
                                                                {{ 'Imagen de reembolso' }}</h4>
                                                            <button type="button" class="close"
                                                                    data-dismiss="modal"><span
                                                                    aria-hidden="true">&times;</span><span
                                                                    class="sr-only">{{ 'Cancelar' }}</span></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <img
                                                                src="{{ \App\CentralLogics\Helpers::get_full_url('refund',$img['img'],$img['storage']) }}"

                                                                class="initial--22 w-100">
                                                        </div>
                                                        @php($storage = $img['storage']??'public')
                                                        @php($file = $storage == 's3'?base64_encode('refund/' . $img['img']):base64_encode('public/refund/' . $img['img']))
                                                        <div class="modal-footer">
                                                            <a class="btn btn-primary"
                                                               href="{{ route('admin.file-manager.download', [$file,$storage]) }}"><i
                                                                    class="tio-download"></i>
                                                                {{ 'descargar' }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="col-3">
                                            <img class="img__aspect-1 rounded border w-100 onerror-image"
                                                 data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                                 src="{{ asset('assets/admin/img/160x160/img2.jpg') }}">
                                        </div>
                                    @endif
                                </div>
                                <hr>


                                <ul class="delivery--information-single mt-3">
                                    <li>
                                        <span class="name">{{ 'Razón' }} </span>
                                        <span class="info"> {{ $order->refund->customer_reason }} </span>
                                    </li>
                                    <li>
                                        <span class="name">{{ 'cantidad' }} </span>
                                        <span class="info"> {{ $order->refund->refund_amount }}</span>
                                    </li>
                                    <li>
                                        <span class="name">{{ 'Método' }} </span>
                                        <span class="info"> {{ $order->refund->refund_method }}</span>
                                    </li>
                                    <li>
                                        <span class="name"> {{ 'Estado' }} </span>
                                        <span class="info"> {{ $order->refund->refund_status }}</span>
                                    </li>
                                    <li>
                                        <span class="name"> {{ 'Nota de administrador' }} </span>
                                        <span class="info"> {{ $order->refund->admin_note ?? 'No Note' }}</span>
                                    </li>
                                    <li>
                                        <span class="name"> {{ 'Nota al cliente' }} </span>
                                        <span class="info"> {{ $order->refund->customer_note ?? 'No Note' }}</span>
                                    </li>
                                    <hr class="w-100">
                                </ul>
                                @if ($order->store)
                                    <div class="btn--container refund--btn">
                                        @if (
                                            (($refund && $refund->value == true) || $order->order_status == 'refund_requested') &&
                                                $order->payment_status == 'paid' &&
                                                $order->order_status != 'refunded')
                                            <button class="btn btn--primary btn--sm route-alert"
                                                    data-url="{{ route('admin.order.status', ['id' => $order['id'],'order_status' => 'refunded',
                                            ]) }}" data-message="{{ 'desea reembolsar este pedido\', [\'cantidad\' => $monto del reembolso. \'\' . \App\CentralLogics\Helpers::código de moneda()]) }}" data-title="{{ traducir(\'¿está seguro de que desea reembolsar?' }}"
                                            ><i
                                                    class="tio-money"></i> <span
                                                    class="ml-1">{{ 'Reembolso' }}</span> </button>
                                        @endif
                                        @if ($order->order_status == 'refund_requested' )
                                            <button type="button" class="btn btn--danger btn-outline-danger"
                                                    data-toggle="modal" data-target="#refund_cancelation_note">
                                                <i class="tio-money"></i> <span
                                                    class="ml-1">{{ 'Cancelar reembolso' }}</span> </button>
                                        @endif
                                    </div>

                                @endif
                            </div>
                        </div>
                    @endif
                @endif



                @if ( !in_array($order->order_status, ['refund_requested', 'refunded', 'refund_request_canceled', 'delivered','canceled']) )
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-10px text-start fw-medium fs-12 d-flex align-items-center gap-1">
                                 <img class="svg" src="{{asset('assets/admin/img/icons/shop-bag.svg')}}" alt="{{'imagen'}}">
                                {{ 'configuración del pedido' }}
                            </h5>
                            @if ($order?->offline_payments?->status == 'denied')
                            <div class="mb-15 text-left rounded badge badge-soft-danger py-2 px-3">
                                <h2 class="fs-12 text-danger font-weight-semibold mb-1">
                                    {{ '# Nota denegada:' }}
                                </h2>
                                <p class="fs-12 mb-0 text-body text-break font-weight-medium"> {{  $order?->offline_payments?->note }}</p>
                            </div>
                            @endif
                            <div class="">
                                @if($order->is_unpaid_order)
                                    <div class="text-center bg-light2 rounded p-xxl-20 p-3">
                                        <h4 class="text-danger fs-14px fw-medium mb-2">{{ '¡El pago falló!' }}</h4>
                                        @php($isCashOnDelivery = App\CentralLogics\Helpers::get_business_settings('cash_on_delivery')['status'] ?? false)
                                        @php($isZoneCashOnDelivery = $order?->zone->cash_on_delivery)
                                        @if($isCashOnDelivery && $isZoneCashOnDelivery)
                                            <p class="fs-12 text-dark mb-20">{{ 'No se pudo procesar el pago del cliente. Por favor cambie a COD.' }}</p>
                                        @endif
                                        <div class="btn--container justify-content-center">
                                            @if($isCashOnDelivery && $isZoneCashOnDelivery)
                                            <button type="button" class="btn btn--primary btn-sm form-alert"
                                                    data-id="order-{{$order['id']}}"
                                                    data-cancel-btn="{{ 'Cancelar' }}"
                                                    data-confirm-btn="{{ 'Confirmar' }}"
                                                    data-image-url="{{ asset('assets/admin/img/tughrik.png') }}"
                                                    data-title="{{ '¿Cambiar a pago contra reembolso?' }}"
                                                    data-message="{{ 'El pago digital del cliente ha fallado. Antes de cambiar este pedido a Pago contra reembolso (COD), confirme el problema de pago con el cliente para evitar malentendidos.' }}">
                                                {{ 'Cambiar a COD' }}</button>
                                            <form action="{{route('admin.order.switch_to_cod',[$order['id']])}}"
                                              method="post" id="order-{{$order['id']}}">
                                            @csrf
                                            </form>
                                            @endif
                                            <button type="button" data-toggle="modal" data-target="#offline_payment_cancel_orders" class="btn btn-outline-secondary">{{ 'Cancelar pedido' }}</button>

                                        </div>

                                    </div>
                                @else
                                    @if($order?->payment_method == 'offline_payment' && !in_array($order->order_status, ['canceled']))
                                        <div class="bg-light2 rounded p-xxl-20 p-3">
                                            <div class="card-body p-0 text-center">
                                                <h2 class="fs-14 fw-medium mb-3">
                                                    {{ $order?->offline_payments?->status == 'verified'?'Pago verificado':'Verificación de pago' }}
                                                </h2>

                                                @if ($order?->offline_payments?->status == 'pending')
                                                    <p class="text-danger fs-12 mb-20"> {{ 'Verifique el pago antes de confirmar el pedido.' }}</p>
                                                    <div class="btn--container justify-content-center">
                                                        <button  type="button" class="btn btn--primary btn-sm" data-toggle="modal" data-target="#verifyViewModal" >{{ 'Verificar pago' }}</button>

                                                        <button type="button" data-toggle="modal" data-target="#offline_payment_cancel_orders" class="btn btn-outline-secondary">{{ 'Cancelar pedido' }}</button>
                                                    </div>
                                                    </div>


                                                @elseif($order?->offline_payments?->status == 'verified')
                                                    <div class="btn--container justify-content-center">
                                                        <button  type="button" class="btn btn--primary btn-sm" data-toggle="modal" data-target="#verifyViewModal" >{{ 'Detalles de pago' }}</button>
                                                    </div>
                                                @elseif($order?->offline_payments?->status == 'denied')
                                                    <div class="btn--container justify-content-center">
                                                        <button  type="button" class="btn btn--primary btn-sm" data-toggle="modal" data-target="#verifyViewModal" >{{ 'Vuelva a verificar la verificación' }}</button>
                                                        <button type="button" data-toggle="modal" data-target="#offline_payment_cancel_orders" class="btn btn-outline-secondary">{{ 'Cancelar pedido' }}</button>

                                                    </div>
                                                @elseif(!$order?->offline_payments)
                                                    <p class="text-danger fs-12 mb-20"> {{ 'Verifique el pago antes de confirmar el pedido.' }}</p>
                                                    <div class="btn--container justify-content-center">
                                                        <button  type="button" class="btn btn--primary btn-sm" data-toggle="modal" data-target="#verifyViewModal" >{{ 'Verificar pago' }}</button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    @if ($order->payment_method != 'offline_payment' || ($order?->offline_payments && $order?->offline_payments?->status == 'verified'))
                                        @if ( !in_array($order->order_status, [ 'refunded', 'refund_request_canceled']))
                                            <div class="hs-unfold w-100 mt-3">
                                                <div class="dropdown">
                                                    <button
                                                        class="form-control h--45px dropdown-toggle d-flex justify-content-between align-items-center w-100"
                                                        type="button" id="dropdownMenuButton" data-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false">
                                                            <?php
                                                            $message= match($order['order_status']){
                                                                'pending' => 'Pendiente',
                                                                'confirmed' => 'confirmado',
                                                                'accepted' => 'aceptado',
                                                                'processing' => 'tratamiento',
                                                                'handover' => 'Entregar',
                                                                'picked_up' => 'En Camino de Entrega',
                                                                'delivered' => 'Entregado',
                                                                'canceled' => 'Cancelado',
                                                                default => 'estado' ,
                                                            };
                                                            ?>
                                                        {{ $message }}
                                                    </button>
                                                    @php($order_delivery_verification = (bool) \App\Models\BusinessSetting::where(['key' => 'order_delivery_verification'])->first()->value)
                                                    <div class="dropdown-menu text-capitalize" aria-labelledby="dropdownMenuButton">
                                                        <a class="dropdown-item {{ $order['order_status'] == 'pending' ? 'active' : '' }} route-alert"
                                                        data-url="{{ route('admin.order.status', ['id' => $order['id'], 'order_status' => 'pending']) }}" data-message="{{ '¿Cambiar estado a pendiente?' }}"
                                                        href="javascript:">{{ 'Pendiente' }}</a>
                                                        <a class="dropdown-item {{ $order['order_status'] == 'confirmed' ? 'active' : '' }} route-alert"
                                                        data-url="{{ route('admin.order.status', ['id' => $order['id'], 'order_status' => 'confirmed']) }}" data-message="{{ '¿Cambiar estado a confirmado?' }}"
                                                        href="javascript:">{{ 'confirmado' }}</a>
                                                        @if ($order->order_type != 'parcel')
                                                            @if ($order->store && $order->store->module->module_type == 'food')
                                                                <a class="dropdown-item {{ $order['order_status'] == 'processing' ? 'active' : '' }} order_status_change_alert" data-url="{{ route('admin.order.status', ['id' => $order['id'], 'order_status' => 'processing']) }}" data-message="{{ '¿Cambiar estado a cocinar?' }}" data-processing={{ $max_processing_time }}
                                                                href="javascript:">{{ 'tratamiento' }}</a>
                                                            @else
                                                                <a class="dropdown-item {{ $order['order_status'] == 'processing' ? 'active' : '' }} route-alert"
                                                                data-url="{{ route('admin.order.status', ['id' => $order['id'], 'order_status' => 'processing']) }}" data-message="{{ '¿Cambiar estado a procesamiento?' }}"
                                                                href="javascript:">{{ 'tratamiento' }}</a>
                                                            @endif
                                                            <a class="dropdown-item {{ $order['order_status'] == 'handover' ? 'active' : '' }} route-alert"
                                                            data-url="{{ route('admin.order.status', ['id' => $order['id'], 'order_status' => 'handover']) }}" data-message="{{ '¿Cambiar estado a entrega?' }}"
                                                            href="javascript:">{{ 'Entregar' }}</a>
                                                        @endif
                                                        <a class="dropdown-item {{ $order['order_status'] == 'picked_up' ? 'active' : '' }} route-alert"
                                                        data-url="{{ route('admin.order.status', ['id' => $order['id'], 'order_status' => 'picked_up']) }}" data-message="{{ '¿Cambiar estado a listo para entrega?' }}"
                                                        href="javascript:">{{ 'En Camino de Entrega' }}</a>
                                                        <a class="dropdown-item {{ $order['order_status'] == 'delivered' ? 'active' : '' }} route-alert"
                                                        data-url="{{ route('admin.order.status', ['id' => $order['id'], 'order_status' => 'delivered']) }}" data-message="{{ '¿Cambiar el estado a entregado (el estado del pago se pagará si no es así)?' }}"
                                                        href="javascript:">{{ 'Entregado' }}</a>
                                                        <a class="dropdown-item {{ $order['order_status'] == 'canceled' ? 'active' : '' }} canceled-status">{{ 'Cancelado' }}</a>
                                                    </div>

                                                </div>
                                            </div>
                                        @endif
                                        @if (!in_array($order->order_status, [ 'refunded','delivered', 'canceled']) &&  ( !$order->delivery_man && $order['order_type'] != 'take_away' && (($order->store && !$order?->store?->sub_self_delivery))))
                                            <div class="w-100 text-center mt-3">
                                                <button type="button" class="btn btn--primary w-100" data-toggle="modal"
                                                        data-target="#myModal" data-lat='21.03' data-lng='105.85'>
                                                    {{ 'asignar al repartidor manualmente' }}
                                                </button>
                                            </div>
                                        @endif
                                        @if (!in_array($order->order_status, ['refunded','delivered', 'canceled']))
                                            <div class="w-100 text-center mt-3">
                                                <button type="button" class="btn btn-danger w-100" data-toggle="modal" data-target="#advanced_cancel_order_modal">
                                                    <i class="tio-clear-circle-outlined"></i> {{ 'Cancelar Orden (Soporte Avanzado)' }}
                                                </button>
                                            </div>
                                        @endif
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($order->delivery_man && $order['order_type'] != 'take_away' && $order->store)
                    <div class="card mt-2">
                        <div class="card-body">
                            <h5 class="card-title text-dark mb-3 d-flex flex-wrap align-items-center">
                                <span class="card-header-icon">
                                    <i class="tio-user"></i>
                                </span>
                                <span>{{ 'Repartidor' }}</span>


                                @if ($order?->store?->sub_self_delivery)
                                    &nbsp; ({{ 'Negocio' }})
                                @endif

                                @if (!isset($order->delivered) && !$order?->store?->sub_self_delivery)
                                    <a type="button" href="#myModal" class="text--base cursor-pointer ml-auto"
                                       data-toggle="modal" data-target="#myModal">
                                        {{ 'cambiar' }}
                                    </a>
                                @endif
                            </h5>
                            <div class="bg-light2 p-10px rounded mb-10px">
                                <a class="media align-items-center deco-none customer--information-single"
                                   href="{{ !$order?->store?->sub_self_delivery ?  route('admin.users.delivery-man.preview', [$order->delivery_man['id']]) : '#' }}">
                                    <div class="avatar avatar-circle">
                                        <img class="avatar-img onerror-image"
                                             data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                                             src="{{ $order->delivery_man?->image_full_url ?? asset('assets/admin/img/160x160/img1.jpg') }}"
                                             alt="Image Description">
                                    </div>
                                    <div class="media-body">
                                        <span
                                            class="text-body d-block text-hover-primary mb-1">{{ $order->delivery_man['f_name'] . ' ' . $order->delivery_man['l_name'] }}</span>

                                        <span class="text--title font-semibold d-flex align-items-center">
                                            <i class="tio-shopping-basket-outlined mr-2"></i>
                                            {{ $order->delivery_man->orders_count }}
                                            {{ 'pedidos entregados' }}
                                        </span>

                                        <span class="text--title font-semibold d-flex align-items-center">
                                            <i class="tio-call-talking-quiet mr-2"></i>
                                            {{ $order->delivery_man['phone'] }}
                                        </span>

                                        <span class="text--title font-semibold d-flex align-items-center">
                                            <i class="tio-email-outlined mr-2"></i>
                                            {{ $order->delivery_man['email'] }}
                                        </span>

                                    </div>
                                </a>
                            </div>
                            @php($address = $order->dm_last_location)
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="text-dark">{{ 'última ubicación' }}</h5>
                            </div>
                            @if (isset($address))
                                <span class="d-block">
                                    <a target="_blank"
                                       href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $address['latitude'] }}+{{ $address['longitude'] }}">
                                        <i class="tio-map"></i> {{ $address['location'] }}<br>
                                    </a>
                                </span>
                            @else
                                <span class="d-block text-lowercase qcont">
                                    {{ 'ubicación no encontrada' }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="card mt-2">
                    <div class="card-body pt-3">
                        @if ($order->customer && $order->is_guest == 0)
                            <h5 class="card-title text-dark mb-3">
                                <span class="card-header-icon">
                                    <i class="tio-user"></i>
                                </span>
                                <span>{{ 'información del cliente' }}</span>
                            </h5>
                            <div class="bg-light2 p-10px rounded mb-10px">
                                <a class="media align-items-center deco-none customer--information-single"
                                   href="{{ route('admin.users.customer.view', [$order->customer['id']]) }}">
                                    <div class="avatar avatar-circle">
                                        <img class="avatar-img onerror-image"
                                             data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                                             src="{{ $order->customer->image_full_url }}"
                                             alt="Image Description">
                                    </div>
                                    <div class="media-body">
                                        <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                            {{ $order->customer['f_name'] . ' ' . $order->customer['l_name'] }}
                                        </span>
                                        <span>{{ $order->customer->orders_count }} {{ 'Pedidos' }}</span>
                                        <span class="text--title font-semibold d-flex align-items-center">
                                            <i class="tio-call-talking-quiet mr-2"></i> <span>{{ $order->customer['phone'] }}</span>
                                        </span>
                                        <span class="text--title d-flex align-items-center">
                                            <i class="tio-email mr-2"></i> <span>{{ $order->customer['email'] }}</span>
                                        </span>
                                    </div>
                                </a>
                            </div>


                        @elseif($order->is_guest)
                            <span class="badge badge-soft-success py-2 d-block qcont">
                                {{ 'Usuario invitado' }}
                            </span>

                        @else
                            <span class="badge badge-soft-danger py-2 d-block qcont">
                                {{ 'Cliente no encontrado!' }}
                            </span>
                        @endif
                        @if ($order->receiver_details)
                            @php($receiver_details = $order->receiver_details)
                            <h5 class="card-title mt-3">
                                    <span class="card-header-icon">
                                        <i class="tio-user"></i>
                                    </span>
                                <span>{{ 'información del receptor' }}</span>
                            </h5>
                            @if (isset($receiver_details))
                                <span class="delivery--information-single mt-3">
                                        <span class="name">{{ 'nombre' }}</span>
                                        <span class="info">{{ $receiver_details['contact_person_name'] }}</span>
                                        <span class="name">{{ 'contacto' }}</span>
                                        <a class="deco-none info d-flex"
                                           href="tel:{{ $receiver_details['contact_person_number'] }}">
                                            {{ $receiver_details['contact_person_number'] }}</a>
                                            @if (data_get($receiver_details,'floor') != '')
                                                <span class="name">{{ 'Piso' }}</span> <span
                                                class="info">{{ data_get($receiver_details,'floor', 'N / A')  }}</span>
                                            @endif
                                            @if ( data_get($receiver_details,'house') != '')
                                                    <span class="name">{{ 'Casa' }}</span> <span
                                                    class="info">{{data_get($receiver_details,'house', 'N / A') }}</span>
                                            @endif

                                            @if ( data_get($receiver_details,'road') != '')
                                                    <span class="name">{{ 'Camino' }}</span> <span
                                                    class="info">{{ data_get($receiver_details,'road', 'N / A') }}</span>
                                            @endif

                                        <hr class="w-100">

                                        @if (isset($receiver_details['address']))
                                        @if (isset($receiver_details['latitude']) && isset($receiver_details['longitude']))
                                            <a class="mt-2 d-flex" target="_blank"
                                               href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $receiver_details['latitude'] }}+{{ $receiver_details['longitude'] }}">
                                                    <i class="tio-poi"></i>{{ $receiver_details['address'] }}
                                                </a>
                                        @else
                                            <i class="tio-poi"></i>{{ $receiver_details['address'] }}
                                        @endif
                                    @endif
                                    </span>
                            @endif
                        @endif

                        @if ($order->delivery_address)
                            @php($address = json_decode($order->delivery_address, true))
                            <div class="bg-light2 p-10px rounded mb-10px">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title text-dark">
                                        <span class="card-header-icon">
                                            <i class="tio-user"></i>
                                        </span>
                                        <span>{{ 'información de entrega' }}</span>
                                    </h5>
                                    @if ($order->order_status != 'delivered' && $order['partially_paid_amount'] == 0)
                                        @if (isset($address))
                                            <a class="link d-flex" data-toggle="modal" data-target="#shipping-address-modal"
                                               href="javascript:"><i class="tio-edit"></i></a>
                                        @endif
                                    @endif
                                </div>
                                @if (isset($address))
                                    <div class="delivery--information-single mt-3">
                                        <span class="name">{{ 'nombre' }}</span>
                                        <span class="info">{{ data_get($address,'contact_person_name', 'N / A') }}</span>
                                        <span class="name">{{ 'contacto' }}</span>
                                        <a class="deco-none info" href="tel:{{ data_get($address,'contact_person_number', 'N / A')  }}">
                                            {{ data_get($address,'contact_person_number', 'N / A') }}</a>
                                                @if ( data_get($address,'house') != '')
                                                    <span class="name">{{ 'Casa' }}</span> <span
                                                    class="info">{{data_get($address,'house', 'N / A') }}</span>
                                                @endif
                                                @if (data_get($address,'floor') != '')
                                                    <span class="name">{{ 'Piso' }}</span> <span
                                                    class="info">{{ data_get($address,'floor', 'N / A')  }}</span>
                                                @endif

                                                @if ( data_get($address,'road') != '')
                                                    <span class="name">{{ 'Camino' }}</span> <span
                                                    class="info">{{ data_get($address,'road', 'N / A') }}</span>
                                                @endif

                                        <div>
                                            @if (isset($address['address']))
                                                @if ( data_get($address,'latitude', null) &&  data_get($address,'longitude', null))
                                                    <a target="_blank" class="d-flex align-items-center mt-2"
                                                       href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $address['latitude'] }}+{{ $address['longitude'] }}">
                                                        <i class="tio-poi"></i>{{ $address['address'] }}
                                                    </a>
                                                @else
                                                    <i class="tio-poi"></i>{{ $address['address'] }}
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card mt-2">
                    <div class="card-body pt-3">
                        <h5 class="card-title text-dark mb-3">
                            <span class="card-header-icon">
                                <i class="tio-settings"></i>
                            </span>
                            <span>{{ 'Entrega Fallida / Retorno' }}</span>
                        </h5>
                        <form action="{{ route('admin.order.status') }}" method="get" class="d-flex flex-column gap-2">
                            <input type="hidden" name="id" value="{{ $order->id }}">
                            <input type="hidden" name="order_status" value="{{ $order->order_status }}">
                            
                            <div class="form-group mb-2">
                                <label class="input-label font-semibold">{{ 'Acción si no es recibido' }}</label>
                                <select name="failed_delivery_action" class="custom-select bg-white">
                                    <option value="return" {{ $order->failed_delivery_action == 'return' ? 'selected' : '' }}>{{ 'Volver a la tienda' }}</option>
                                    <option value="donation" {{ $order->failed_delivery_action == 'donation' ? 'selected' : '' }}>{{ 'Donación' }}</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-2">
                                <label class="input-label font-semibold">{{ 'Instrucciones Especiales' }}</label>
                                <textarea name="failed_delivery_instruction" class="form-control" rows="3" placeholder="{{ 'Instrucciones para el repartidor si la entrega falla...' }}">{{ $order->failed_delivery_instruction }}</textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">
                                <i class="tio-save mr-1"></i> {{ 'Guardar configuración' }}
                            </button>
                        </form>

                        @if ($order->order_status == 'returned' && ($order->failed_delivery_action ?? 'return') == 'return' && $order->return_otp)
                            <div class="alert alert-soft-warning border-warning mt-3 text-center mb-0">
                                <h6 class="alert-heading mb-1 text-warning"><i class="tio-lock"></i> {{ 'PIN para la Tienda' }}</h6>
                                <span class="font-semibold" style="font-size: 1.6rem; letter-spacing: 2px; color: #b7791f;">{{ $order->return_otp }}</span>
                                <p class="text-xs text-muted mt-1 mb-0" style="font-size: 0.75rem;">{{ 'Proporciona este código a la tienda para confirmar la recepción del retorno.' }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Customer Card -->
                @php($data = isset($order->order_proof) ? json_decode($order->order_proof, true) : [])
                @if ( in_array($order->order_status, [ 'handover', 'delivered', 'picked_up']) || ($data != null && count($data) > 0) )
                    <!-- order proof -->
                    <div class="card mb-2 mt-2">
                        <div class="card-header border-0 text-center pb-0">
                            <h4 class="m-0">{{ 'prueba de entrega' }} </h4>
                            @if ( in_array($order->order_status, [ 'handover', 'delivered', 'picked_up']) )
                                <button class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target=".order-proof-modal">  {{ 'agregar' }}  </button>
                            @endif
                        </div>
                        <div class="card-body pt-2">
                            @if ($data)
                                <label class="input-label"
                                       for="order_proof">{{ 'imagen' }} : </label>
                                <div class="row g-3">
                                    @foreach ($data as $key => $img)
                                        @php($img = is_array($img)?$img:['img'=>$img,'storage'=>'public'])
                                        <div class="col-3">
                                            <img class="img__aspect-1 rounded border w-100 onerror-image" data-toggle="modal"
                                                 data-target="#imagemodal{{ $key }}"
                                                 data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                                 src="{{\App\CentralLogics\Helpers::get_full_url('order',$img['img'],$img['storage']) }}">
                                        </div>
                                        <div class="modal fade" id="imagemodal{{ $key }}" tabindex="-1"
                                             role="dialog" aria-labelledby="order_proof_{{ $key }}"
                                             aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"
                                                            id="order_proof_{{ $key }}">
                                                            {{ 'imagen de prueba de pedido' }}</h4>
                                                        <button type="button" class="close"
                                                                data-dismiss="modal"><span
                                                                aria-hidden="true">&times;</span><span
                                                                class="sr-only">{{ 'Cancelar' }}</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <img src="{{\App\CentralLogics\Helpers::get_full_url('order',$img['img'],$img['storage']) }}"
                                                             class="initial--22 w-100">
                                                    </div>
                                                    @php($storage = $img['storage'] ?? 'public')
                                                    @php($file = $storage == 's3'?base64_encode('order/' . $img['img']):base64_encode('public/order/' . $img['img']))
                                                    <div class="modal-footer">
                                                        <a class="btn btn-primary"
                                                           href="{{ route('admin.file-manager.download', [$file,$storage]) }}"><i
                                                                class="tio-download"></i>
                                                            {{ 'descargar' }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($order->store)
                    <!-- Restaurant Card -->
                    <div class="card mt-2">
                        <!-- Body -->
                        <div class="card-body">
                            <h5 class="card-title text-dark mb-3">
                                <span class="card-header-icon">
                                    <i class="tio-user"></i>
                                </span>
                                <span>{{ 'almacenar información' }}</span>
                            </h5>
                            <div class="bg-light2 p-10px rounded mb-10px">
                                <a class="media align-items-center deco-none resturant--information-single"
                                   href="{{ route('admin.store.view', [$order->store['id'],'module_id' => $order->module_id]) }}">
                                    <div class="avatar avatar-circle">
                                        <img class="avatar-img w-75px onerror-image"
                                             data-onerror-image="{{ asset('assets/admin/img/100x100/1.png') }}"
                                             src="{{$order?->store?->logo_full_url ?? asset('assets/admin/img/100x100/1.png')  }}"
                                             alt="Image Description">
                                    </div>
                                    <div class="media-body">
                                        <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                            {{ $order->store['name'] }}
                                        </span>
                                        <span>{{ $order->store->orders_count }} {{ 'Pedidos' }}</span>
                                        <span class="text--title font-semibold d-flex align-items-center">
                                            <i class="tio-call-talking-quiet mr-2"></i>{{ $order->store['phone'] }}
                                        </span>
                                        <span class="text--title d-flex align-items-center">
                                            <i class="tio-email mr-2"></i>{{ $order->store['email'] }}
                                        </span>
                                    </div>
                                </a>
                            </div>
                            <span class="d-block">
                                <a target="_blank" class="d-flex align-items-center __gap-5px" href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $order->store['latitude'] }}+{{ $order->store['longitude'] }}">
                                    <i class="tio-poi"></i> <span>{{ $order->store['address'] }}</span><br>
                                </a>
                            </span>
                        </div>
                        <!-- End Body -->
                    </div>
                    <!-- End Card -->
                @endif
            </div>
        </div>
        <!-- End Row -->
    </div>

    <!-- Modal -->
    <div class="modal fade" id="refund_cancelation_note" tabindex="-1" role="dialog"
         aria-labelledby="refund_cancelation_note_l" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="refund_cancelation_note_l">{{ 'agregar nota de rechazo de pedido' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.refund.order_refund_rejection') }}" method="post">
                        @method('PUT')
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <input type="text" class="form-control" name="admin_note" value="{{ old('admin_note') }}"
                               placeholder="Fake Order">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{  'cerca' }}</button>
                    <button type="submit" class="btn btn-danger">{{ 'Confirmar rechazo de pedido' }} </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="mySmallModalLabel">{{ 'añadir código de referencia' }}</h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal"
                            aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>

                <form action="{{ route('admin.order.add-payment-ref-code', [$order['id']]) }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <!-- Input Group -->
                        <div class="form-group">
                            <input type="text" name="transaction_reference" class="form-control"
                                   placeholder="{{ 'Ex:' }} Code123" required>
                        </div>
                        <!-- End Input Group -->
                        <div class="text-right">
                            <button class="btn btn--primary">{{ 'entregar' }}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- End Modal -->
    <!-- Modal -->
    <div class="modal fade order-proof-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="mySmallModalLabel">{{ 'agregar comprobante de entrega' }}</h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal"
                            aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>

                <form action="{{ route('admin.order.add-order-proof', [$order['id']]) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="flex-grow-1 mx-auto">
                            <div class="d-flex flex-wrap __gap-12px __new-coba" id="coba">
                                @php($proof = isset($order->order_proof) ? json_decode($order->order_proof, true) : 0)
                                @if ($proof)

                                    @foreach ($proof as $key => $photo)
                                        @php($photo = is_array($photo)?$photo:['img'=>$photo,'storage'=>'public'])
                                        <div class="spartan_item_wrapper min-w-176px max-w-176px">
                                            <img class="img--square"
                                                 src="{{\App\CentralLogics\Helpers::get_full_url('order',$photo['img'],$photo['storage']) }}"
                                                 alt="order image">
                                            <div class="pen spartan_remove_row"><i class="tio-edit"></i></div>
                                            <a href="{{ route('admin.order.remove-proof-image', ['id' => $order['id'], 'name' => $photo['img']]) }}"
                                               class="spartan_remove_row"><i class="tio-add-to-trash"></i></a>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="text-right mt-2">
                            <button class="btn btn--primary">{{ 'entregar' }}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- End Modal -->

    <!-- Modal -->
    <div id="shipping-address-modal" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalTopCoverTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <!-- Header -->
                <div class="modal-top-cover bg-dark text-center">
                    <figure class="position-absolute right-0 bottom-0 left-0 mb--1">
                        <svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                             viewBox="0 0 1920 100.1">
                            <path fill="#fff" d="M0,0c0,0,934.4,93.4,1920,0v100.1H0L0,0z" />
                        </svg>
                    </figure>

                    <div class="modal-close">
                        <button type="button" class="btn btn-icon btn-sm btn-ghost-light" data-dismiss="modal"
                                aria-label="Close">
                            <svg width="16" height="16" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor"
                                      d="M11.5,9.5l5-5c0.2-0.2,0.2-0.6-0.1-0.9l-1-1c-0.3-0.3-0.7-0.3-0.9-0.1l-5,5l-5-5C4.3,2.3,3.9,2.4,3.6,2.6l-1,1 C2.4,3.9,2.3,4.3,2.5,4.5l5,5l-5,5c-0.2,0.2-0.2,0.6,0.1,0.9l1,1c0.3,0.3,0.7,0.3,0.9,0.1l5-5l5,5c0.2,0.2,0.6,0.2,0.9-0.1l1-1 c0.3-0.3,0.3-0.7,0.1-0.9L11.5,9.5z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- End Header -->

                <div class="modal-top-cover-icon">
                    <span class="icon icon-lg icon-light icon-circle icon-centered shadow-soft">
                        <i class="tio-location-search"></i>
                    </span>
                </div>

                @if (isset($address))
                    <form action="{{ route('admin.order.update-shipping', [$order['id']]) }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{ 'tipo' }}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="address_type"
                                           value="{{ $address['address_type'] }}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{ 'contacto' }}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="contact_person_number"
                                           value="{{ $address['contact_person_number'] }}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{ 'nombre' }}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="contact_person_name"
                                           value="{{ $address['contact_person_name'] }}" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{ 'Casa' }}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="house"
                                           value="{{ isset($address['house']) ? $address['house'] : '' }}" >
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{ 'Piso' }}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="floor"
                                           value="{{ isset($address['floor']) ? $address['floor'] : '' }}" >
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{ 'Camino' }}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="road"
                                           value="{{ isset($address['road']) ? $address['road'] : '' }}" >
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{ 'DIRECCIÓN' }}
                                </label>
                                <div class="col-md-10 js-form-message">
                                    <input type="text" class="form-control" name="address"
                                           value="{{ $address['address'] }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{ 'latitud' }}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="latitude" id="latitude"
                                           value="{{ $address['latitude'] }}">
                                </div>
                                <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                    {{ 'longitud' }}
                                </label>
                                <div class="col-md-4 js-form-message">
                                    <input type="text" class="form-control" name="longitude" id="longitude"
                                           value="{{ $address['longitude'] }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <input id="pac-input" class="controls rounded initial-8"
                                       title="{{ 'busca tu ubicación aquí' }}" type="text"
                                       placeholder="{{ 'buscar aquí' }}" />
                                <div class="mb-2 h-200px" id="map"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn--reset"
                                    data-dismiss="modal">{{ 'cerca' }}</button>
                            <button type="submit" class="btn btn--primary">{{ 'guardar cambios' }}</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <!--Dm assign Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">{{ 'asignar repartidor' }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5 my-2">
                            <ul class="list-group overflow-auto initial--23">
                                @foreach ($deliveryMen as $dm)
                                    <li class="list-group-item">
                                        <span class="dm_list" role='button' data-id="{{ $dm['id'] }}">
                                            <img class="avatar avatar-sm avatar-circle mr-1 onerror-image"
                                                 data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                                                 src="{{$dm['image_full_url'] }}"
                                                 alt="{{ $dm['name'] }}">
                                            {{ $dm['name'] }}
                                        </span>

                                        <a class="btn btn-primary btn-xs float-right add-delivery-man" data-id="{{ $dm['id'] }}">{{ $order->delivery_man ? 'reasignar' : 'asignar' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-7 modal_body_map">
                            <div class="location-map" id="dmassign-map">
                                <div class="initial--24" id="map_canvas"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <!--Show locations on map Modal -->
    <div class="modal fade" id="locationModal" tabindex="-1" role="dialog" aria-labelledby="locationModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="locationModalLabel">{{ 'datos de ubicación' }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 modal_body_map">
                            <div class="location-map" id="location-map">
                                <div class="initial--25" id="location_map_canvas"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <div class="modal fade" id="quick-view" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" id="quick-view-modal">

            </div>
        </div>
    </div>



    @if ($order?->payment_method == 'offline_payment')
        <div class="modal fade" id="verifyViewModal" tabindex="-1" aria-labelledby="verifyViewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-end  border-0 pt-3 px-3">
                        <button type="button" class="close border rounded-circle bg-modal-btn" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                    <div class="modal-body pt-0">
                        <div class="d-flex align-items-center flex-column gap-1 mb-xxl-5 mb-4 text-center">

                            <h2 class="mb-0">
                                {{ 'Verificación de pago' }}

                                @if(optional($order->offline_payments)->status === 'verified')
                                    <span class="badge badge-soft-success mt-3 mb-3">
                                        {{ 'verificado' }}
                                    </span>
                                @endif
                            </h2>

                            @unless(optional($order->offline_payments)->status === 'verified')
                                <p class="text-danger mb-0 mt-0">
                                    {{ 'Por favor verifique y verifique la información de pago antes de confirmar el pedido.' }}
                                </p>
                            @endunless

                        </div>


                        <div class="card border-0">
                            <div class="bg-light2 p-xxl-20 p-3 rounded">
                                <div class="adjust-information-payment flex-md-nowrap flex-wrap">
                                    <div class="bg-white p-3 rounded h-100 w-100">
                                        <h4 class="mb-3 fs-16">{{ 'información del cliente' }}</h4>
                                        <div class="d-flex flex-column gap-2">
                                            @if($order->is_guest)
                                                @php($customer_details = json_decode($order['delivery_address'],true))

                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="customer-namekey">{{'Nombre'}}</span>:
                                                    <span class="text-dark"> {{$customer_details['contact_person_name']}}</span>
                                                </div>

                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="customer-namekey">{{'Teléfono'}}</span>:
                                                    <span class="text-dark">  {{$customer_details['contact_person_number']}}</span>
                                                </div>

                                            @elseif($order->customer)
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="customer-namekey">{{'Nombre'}}</span>:
                                                    <span class="text-dark"> <a class="text-dark text text-capitalize" href="{{route('admin.customer.view',[$order['user_id']])}}"> {{$order->customer['f_name'].' '.$order->customer['l_name']}}  </a>  </span>
                                                </div>

                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="customer-namekey">{{'Teléfono'}}</span>:
                                                    <span class="text-dark">{{$order->customer['phone']}}  </span>
                                                </div>

                                            @else
                                                <label class="badge badge-danger">{{'datos de cliente no válidos'}}</label>
                                            @endif

                                        </div>
                                    </div>
                                    @if($order?->offline_payments)
                                    <div class="bg-white p-3 rounded h-100 w-100">
                                        <div class="">
                                            <h4 class="mb-3 fs-16">{{ 'Información de pago' }}</h4>
                                            <div class="row g-1">
                                                @foreach (json_decode($order?->offline_payments?->payment_info ?? '[]') as $key=>$item)
                                                    @if ($key != 'method_id')
                                                        <div class="col-sm-12">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="namekey"> {{translate($key)}}</span>:
                                                                <span class="text-dark text-break">{{ $item }}</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>

                                            <div class="d-flex flex-column gap-2 mt-4">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="namekey">{{'Nota al cliente'}}</span>:
                                                    <span class="text-dark text-break">{{$order->offline_payments?->customer_note ?? 'N / A'}} </span>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    @else
                                        <div class="bg-white p-3 rounded h-100 w-100">
                                            <h4 class="mb-3 fs-16">{{ 'Información de pago' }}</h4>
                                            <div class="row g-1">
                                                <div class="col-sm-12">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="namekey"> {{'Método de pago'}}</span>:
                                                        <span class="text-dark text-break">{{'N / A'}} </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if ($order?->offline_payments?->status != 'verified')
                            <div class="btn--container justify-content-end mt-xxl-5 mt-4 pt-xxl-1">
                                @if ($order?->offline_payments?->status != 'denied')
                                    <button type="button" class="btn btn--reset offline_payment_cancelation_note" data-toggle="modal" data-target="#offline_payment_cancelation_note" data-id="{{ $order['id'] }}" class="btn btn--reset">{{'El pago no se recibió'}}</button>
                                @elseif ($order?->offline_payments?->status == 'denied')
                                    <button type="button" data-url="{{ route('admin.order.offline_payment', [ 'id' => $order['id'], 'verify' => 'switched_to_cod', ]) }}" data-message="{{ 'Realizar el pago cambiado a bacalao para este pedido' }}" class="btn btn--reset route-alert">{{'Cambiado a COD'}}</button>
                                @endif
                                @if($order?->offline_payments)
                                    <button type="button" data-url="{{ route('admin.order.offline_payment', [ 'id' => $order['id'], 'verify' => 'yes', ]) }}" data-message="{{ 'Realizar el pago verificado para este pedido' }}" class="btn btn--primary route-alert">{{'Sí, pago recibido'}}</button>
                                @else
                                        <button type="button" class="btn btn--primary btn-sm form-alert"
                                                data-id="order-{{$order['id']}}"
                                                data-cancel-btn="{{ 'Cancelar' }}"
                                                data-confirm-btn="{{ 'Confirmar' }}"
                                                data-image-url="{{ asset('assets/admin/img/tughrik.png') }}"
                                                data-title="{{ '¿Cambiar a pago contra reembolso?' }}"
                                                data-message="{{ 'El pago fuera de línea del cliente falló. Antes de cambiar este pedido a Pago contra reembolso (COD), confirme el problema de pago con el cliente para evitar malentendidos.' }}">
                                            {{ 'Cambiar a COD' }}
                                        </button>
                                    <form action="{{route('admin.order.switch_to_cod',[$order['id']])}}"
                                          method="post" id="order-{{$order['id']}}">
                                        @csrf
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="offline_payment_cancelation_note" tabindex="-1" role="dialog"
             aria-labelledby="offline_payment_cancelation_note_l" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-600" role="document">
                <div class="modal-content">
                    <div class="modal-header px-2 pt-2">
                        <button type="button" class="close min-w-28 rounded-circle border bg-modal-btn" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.order.offline_payment') }}" method="get">
                        <div class="modal-body">
                            <div class="cont mb-4 text-center pb-xxl-1">
                                <img width="60px" height="60px" src="{{asset('assets/admin/img/delete-confirmation.png')}}" alt="public" class="mb-20">
                                <h3 class="mb-xl-2 mb-1">
                                    {{'¿Estás seguro de que no se recibió el pago?'}}
                                </h3>
                                <p class="mb-0 fs-14 max-w-420 mx-auto">
                                    {{'Inserte una nota denegada para esta solicitud de pago para informar al cliente.'}}
                                </p>
                            </div>
                            <div class="bg-light2 p-3 rounded">
                                <label class="form-label">
                                    {{'Nota denegada'}}
                                    <span class="custom-tooltip" data-title="payment request to inform the customer ">
                                        <i class="tio-info text-muted"></i>
                                    </span>
                                </label>
                                <input type="hidden" name="id" value="{{ $order->id }}">
                                <textarea type="text" rows="1" maxlength="100" required class="form-control" name="note" value="{{ old('note') }}"
                                    placeholder="{{ 'ID de transacción no coincide' }}"></textarea>
                                <span class="text-right text-counting color-A7A7A7 d-block mt-1">0/100</span>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-2">
                            <button type="button" class="btn btn--reset h-40px min-w-120px py-2 fs-14" data-dismiss="modal">{{  'cerca' }}</button>
                            <button type="submit" class="btn btn-primary h-40px min-w-120px py-2 fs-14">{{ 'Confirmar rechazo' }} </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @endif

        <div class="modal fade" id="advanced_cancel_order_modal" tabindex="-1" role="dialog" aria-labelledby="advanced_cancel_order_modal_label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-600" role="document">
                <div class="modal-content">
                    <div class="modal-header px-2 pt-2">
                        <button type="button" class="close min-w-28 rounded-circle border bg-modal-btn" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.order.cancel-advanced', ['id' => $order['id']]) }}" method="post">
                        @csrf
                        <div class="modal-body text-left">
                            <h3 class="mb-3 text-center text-danger">{{ 'Cancelar Orden (Controles Avanzados)' }}</h3>
                            <p class="text-center text-muted mb-4">{{ 'Selecciona las reglas financieras y operativas que se aplicarán a esta cancelación.' }}</p>
                            
                            <div class="form-group mb-3 custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="strike_deliveryman" name="strike_deliveryman" value="1">
                                <label class="custom-control-label" for="strike_deliveryman"><strong>{{ 'Huelga para el repartidor' }}</strong></label>
                            </div>
                            
                            <div class="form-group mb-3 custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="debt_to_customer" name="debt_to_customer" value="1">
                                <label class="custom-control-label" for="debt_to_customer"><strong>{{ 'Cobrar deuda al cliente' }}</strong> ({{ \App\CentralLogics\Helpers::format_currency($order->order_amount) }})</label>
                            </div>

                            <div class="form-group mb-3 custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="debt_to_deliveryman" name="debt_to_deliveryman" value="1">
                                <label class="custom-control-label" for="debt_to_deliveryman"><strong>{{ 'Cobrar deuda al repartidor' }}</strong> ({{ \App\CentralLogics\Helpers::format_currency($order->order_amount) }})</label>
                            </div>
                            
                            <div class="form-group mb-3 custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="tootli_absorbs_loss" name="tootli_absorbs_loss" value="1">
                                <label class="custom-control-label" for="tootli_absorbs_loss"><strong>{{ 'Tootli absorbe la pérdida' }}</strong></label>
                                <small class="d-block text-muted">{{ 'Se marcará como "Pedido pagado a restaurante pero dinero no obtenido".' }}</small>
                            </div>
                            
                            <div class="form-group mb-3 custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="pay_delivery_fee" name="pay_delivery_fee" value="1">
                                <label class="custom-control-label" for="pay_delivery_fee"><strong>{{ 'Pagar tarifa de envío al repartidor' }}</strong></label>
                            </div>
                            
                            <div class="form-group mb-3 custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="return_to_store" name="return_to_store" value="1">
                                <label class="custom-control-label" for="return_to_store"><strong>{{ 'Regresar pedido a la tienda' }}</strong></label>
                                <small class="d-block text-muted">{{ 'El estado de la orden cambiará a "returned" y se notificará al restaurante.' }}</small>
                            </div>
                            
                            <div class="form-group mb-3 mt-4">
                                <label for="cancellation_note">{{ 'Nota de Cancelación (opcional)' }}</label>
                                <textarea class="form-control" name="cancellation_note" id="cancellation_note" rows="3"></textarea>
                            </div>

                        </div>
                        <div class="modal-footer border-0 pt-2">
                            <button type="button" class="btn btn--reset h-40px min-w-120px py-2 fs-14" data-dismiss="modal">{{ 'cerca' }}</button>
                            <button type="submit" class="btn btn-danger h-40px min-w-120px py-2 fs-14">{{ 'Confirmar Cancelación' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="offline_payment_cancel_orders" tabindex="-1" role="dialog"
             aria-labelledby="offline_payment_cancel_orders" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-600" role="document">
                <div class="modal-content">
                    <div class="modal-header px-2 pt-2">
                        <button type="button" class="close min-w-28 rounded-circle border bg-modal-btn" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.order.status') }}" method="get">
                        <input type="hidden" name="id" value="{{ $order['id'] }}">
                        <input type="hidden" name="order_status" value="canceled">
                        <div class="modal-body">
                            <div class="cont mb-4 text-center pb-xxl-1">
                                <img width="60px" height="60px" src="{{asset('assets/admin/img/offlice-cancel-orders.png')}}" alt="public" class="mb-20">
                                <h3 class="mb-xl-2 mb-1">
                                    {{'¿Cancelar este pedido?'}}
                                </h3>
                                <p class="mb-0 fs-14 max-w-420 mx-auto">
                                    {{'Por favor, póngase en contacto con el cliente para realizar el pedido de forma permanente.'}}
                                </p>
                            </div>
                            <div class="bg-light2 p-3 rounded">
                                <label class="form-label">
                                    {{'Seleccione el motivo de cancelación'}}
                                </label><br>
                                <select name="reason" class="bg-white custom-select" id="">
                                    <option value="">{{ 'seleccione el motivo' }}</option>
                                    @foreach ($reasons as $r)
                                        <option value="{{ $r->reason }}">{{ $r->reason }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer d-flex gap-3 flex-nowrap pb-4 mb-2 justify-content-center border-0 pt-2">
                            <button type="button" class="btn btn--reset h-40px min-w-120px w-100 py-2 fs-14" data-dismiss="modal">{{  'Mantener el orden' }}</button>
                            <button type="submit" class="btn btn-primary h-40px min-w-120px w-100 py-2 fs-14">{{ 'Sí, cancelar pedido' }} </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Custom Debt Modal -->
        <div class="modal fade" id="customDebtModal" tabindex="-1" role="dialog" aria-labelledby="customDebtModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('admin.order.apply-debt', ['id' => $order['id']]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="custom">
                        <div class="modal-header border-0 pb-1">
                            <h5 class="modal-title" id="customDebtModalLabel">Aplicar monto de deuda personalizado</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center py-3">
                            <i class="tio-money-vs text-warning" style="font-size: 52px;"></i>
                            <p class="mb-3 mt-2 text-dark">
                                Ingresa el monto exacto de deuda que deseas aplicar a la billetera de este usuario. El saldo del usuario se reducirá en esta cantidad.
                            </p>
                            <input type="number" step="0.01" min="0.01" name="custom_amount" class="form-control text-center" placeholder="Ej. 150.50" required>
                        </div>
                        <div class="modal-footer pb-4 pt-0 border-0 justify-content-center">
                            <button type="button" class="btn btn-secondary min-w-120px" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary min-w-120px">Aplicar Deuda</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection

@push('script_2')
    <script>
        $(document).on("click", ".addon-quantity-input-toggle", function (event) {
            let cb = $(event.target);
            if (cb.is(":checked")) {
                cb.siblings(".addon-quantity-input").css({ visibility: "visible" });
            } else {
                cb.siblings(".addon-quantity-input").css({ visibility: "hidden" });
            }
        });
        $(document).on("click", ".decrease-button", function () {
            let addonId = $(this).data("id");
            let addon_quantity_input = $('input[name="addon-quantity' + addonId + '"]');
            let currentValue = parseInt(addon_quantity_input.val(), 10);
            if (currentValue > 1) {
                addon_quantity_input.val(currentValue - 1);
                getVariantPrice();
            }
        });
        $(document).on("click", ".increase-button", function () {
            let addonId = $(this).data("id");
            let addon_quantity_input = $('input[name="addon-quantity' + addonId + '"]');
            let currentValue = parseInt(addon_quantity_input.val(), 10);
            addon_quantity_input.val(currentValue + 1);
            getVariantPrice();
        });
        $('#search-form').on('submit', function(e) {
            e.preventDefault();
            var keyword = $('#datatableSearch').val();
            var nurl = new URL('{!! url()->full() !!}');
            nurl.searchParams.set('keyword', keyword);
            location.href = nurl;
        });

        $('.set-category-filter').on('change', function() {
            let id = $(this).val();
            var nurl = new URL('{!! url()->full() !!}');
            nurl.searchParams.set('category_id', id);
            location.href = nurl;
        })

        $('.addon_quantity_input_toggle').on('change', function(event) {
            addon_quantity_input_toggle(event);
        })

        function addon_quantity_input_toggle(e) {
            var cb = $(e.target);
            if (cb.is(":checked")) {
                cb.siblings('.addon-quantity-input').css({
                    'visibility': 'visible'
                });
            } else {
                cb.siblings('.addon-quantity-input').css({
                    'visibility': 'hidden'
                });
            }
        }

        $('.quick-view-cart-item').on('click',function (){
            let key = $(this).data('key');
            $.get({
                url: '{{ route('admin.order.quick-view-cart-item') }}',
                dataType: 'json',
                data: {
                    key: key,
                    order_id: '{{ $order->id }}',
                },
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    $('#quick-view').modal('show');
                    $('#quick-view-modal').empty().html(data.view);
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        })

        $('.quick-view').on('click',function (){
            let product_id = $(this).data('product-id');
            quickView(product_id);
        })

        function quickView(product_id) {
            $.get({
                url: '{{ route('admin.order.quick-view') }}',
                dataType: 'json',
                data: {
                    product_id: product_id,
                    order_id: '{{ $order->id }}',
                },
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    console.log("success...")
                    $('#quick-view').modal('show');
                    $('#quick-view-modal').empty().html(data.view);
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        }

        function cartQuantityInitialize() {
            $('.btn-number').click(function(e) {
                e.preventDefault();

                var fieldName = $(this).attr('data-field');
                var type = $(this).attr('data-type');
                var input = $("input[name='" + fieldName + "']");
                var currentVal = parseInt(input.val());

                if (!isNaN(currentVal)) {
                    if (type == 'minus') {

                        if (currentVal > input.attr('min')) {
                            input.val(currentVal - 1).change();
                        }
                        if (parseInt(input.val()) == input.attr('min')) {
                            $(this).attr('disabled', true);
                        }

                    } else if (type == 'plus') {

                        if (currentVal < input.attr('max')) {
                            input.val(currentVal + 1).change();
                        }
                        if (parseInt(input.val()) == input.attr('max')) {
                            $(this).attr('disabled', true);
                        }

                    }
                } else {
                    input.val(0);
                }
            });

            $('.input-number').focusin(function() {
                $(this).data('oldValue', $(this).val());
            });

            $('.input-number').change(function() {

                minValue = parseInt($(this).attr('min'));
                maxValue = parseInt($(this).attr('max'));
                valueCurrent = parseInt($(this).val());

                var name = $(this).attr('name');
                if (valueCurrent >= minValue) {
                    $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Cart',
                        text: 'Sorry, the minimum value was reached'
                    });
                    $(this).val($(this).data('oldValue'));
                }
                if (valueCurrent <= maxValue) {
                    $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Cart',
                        text: 'Sorry, stock limit exceeded.'
                    });
                    $(this).val($(this).data('oldValue'));
                }
            });
            $(".input-number").keydown(function(e) {
                // Allow: backspace, delete, tab, escape, enter and .
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                    // Allow: Ctrl+A
                    (e.keyCode == 65 && e.ctrlKey === true) ||
                    // Allow: home, end, left, right
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    // let it happen, don't do anything
                    return;
                }
                // Ensure that it is a number and stop the keypress
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
        }

        function getVariantPrice() {
            if ($('#add-to-cart-form input[name=quantity]').val() > 0) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: '{{ route('admin.item.variant-price') }}',
                    data: $('#add-to-cart-form').serializeArray(),
                    success: function(data) {
                        $('#add-to-cart-form #chosen_price_div').removeClass('d-none');
                        $('#add-to-cart-form #chosen_price_div #chosen_price').html(data.price);
                    }
                });
            }
        }


        $(document).on('click', '.update_order_item', function () {


            update_order_item();
        })

        function update_order_item(form_id = 'add-to-cart-form') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.order.add-to-cart') }}',
                data: $('#' + form_id).serializeArray(),
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    if (data.data == 1) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cart',
                            text: "{{ 'producto ya agregado en el carrito' }}"
                        });
                        return false;
                    } else if (data.data == 0) {
                        toastr.success('{{ 'El producto ha sido añadido al carrito.' }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        location.reload();
                        return false;
                    } else if (data.data == 'variation_error') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cart',
                            text: data.message
                        });
                        return false;
                    }
                    $('.call-when-done').click();

                    toastr.success('{{ 'pedido actualizado exitosamente' }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    location.reload();
                },
                complete: function() {
                    $('#loading').hide();
                }
            });
        }


        $(document).on('click', '.removeFromCart', function () {
            let key = $(this).data('key');
            removeFromCart(key);
        })

        function removeFromCart(key) {
            Swal.fire({
                title: '{{ '¿está seguro?' }}',
                text: '{{ 'desea eliminar este artículo del pedido' }}',
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ 'No' }}',
                confirmButtonText: '{{ 'Sí' }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.post('{{ route('admin.order.remove-from-cart') }}', {
                        _token: '{{ csrf_token() }}',
                        key: key,
                        order_id: '{{ $order->id }}'
                    }, function(data) {
                        if (data.errors) {
                            for (var i = 0; i < data.errors.length; i++) {
                                toastr.error(data.errors[i].message, {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            }
                        } else {
                            toastr.success(
                                '{{ 'El artículo ha sido eliminado del carrito.' }}', {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            location.reload();
                        }

                    });
                }
            })

        }

        $('.edit-order').on('click',function (){
            Swal.fire({
                title: '{{ '¿está seguro?' }}',
                text: '{{ 'quieres editar este pedido' }}',
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ 'No' }}',
                confirmButtonText: '{{ 'Sí' }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = '{{ route('admin.order.edit', $order->id) }}';
                }
            })
        })

        $('.cancel-edit-order').on('click',function (){
            Swal.fire({
                title: '{{ '¿está seguro?' }}',
                text: '{{ 'quieres cancelar la edición' }}',
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ 'No' }}',
                confirmButtonText: '{{ 'Sí' }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = '{{ route('admin.order.edit', $order->id) }}?cancle=true';
                }
            })
        })

        $('.submit-edit-order').on('click',function (){
            Swal.fire({
                title: '{{ '¿está seguro?' }}',
                text: '{{ 'desea enviar todos los cambios para este pedido' }}',
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ 'No' }}',
                confirmButtonText: '{{ 'Sí' }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = '{{ route('admin.order.update', $order->id) }}';
                }
            })
        })
    </script>

    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}&libraries=places,marker&v=3.61">
    </script>
    <script>
        // INITIALIZATION OF SELECT2
        // =======================================================
        $('.js-select2-custom').each(function () {
            var select2 = $.HSCore.components.HSSelect2.init($(this));
        });

        $('.add-delivery-man').on('click',function (){
            id = $(this).data('id');
            $.ajax({
                type: "GET",
                url: '{{ url('/') }}/admin/order/add-delivery-man/{{ $order['id'] }}/' + id,
                success: function(data) {
                    location.reload();
                    console.log(data)
                    toastr.success('Successfully added', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                error: function(response) {
                    console.log(response);
                    toastr.error(response.responseJSON.message, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        })

        $('.order_status_change_alert').on('click', function (){
            let route = $(this).data('url');
            let message = $(this).data('message');
            let processing = $(this).data('processing');
            order_status_change_alert(route, message, processing);
        })

        function order_status_change_alert(route, message, processing = false) {
            if (processing) {
                Swal.fire({
                    //text: message,
                    title: '{{ 'Está seguro ?' }}',
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#FC6A57',
                    cancelButtonText: '{{ 'Cancelar' }}',
                    confirmButtonText: '{{ 'entregar' }}',
                    inputPlaceholder: "{{ 'Ingrese el tiempo de procesamiento' }}",
                    input: 'text',
                    html: message + '<br/>'+'<label>{{ 'Ingrese el tiempo de procesamiento en minutos' }}</label>',
                    inputValue: processing,
                    preConfirm: (processing_time) => {
                        location.href = route + '&processing_time=' + processing_time;
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                })
            } else {
                Swal.fire({
                    title: '{{ 'Está seguro ?' }}',
                    text: message,
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#FC6A57',
                    cancelButtonText: '{{ 'No' }}',
                    confirmButtonText: '{{ 'Sí' }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        location.href = route;
                    }
                })
            }
        }


        function last_location_view() {
            toastr.warning('Only available when order is out for delivery!', {
                CloseButton: true,
                ProgressBar: true
            });
        }
        $(document).ready(function () {
            // Event handler for 'canceled-status' click

        });
    </script>
    <script>
        var deliveryMan = <?php echo json_encode($deliveryMen); ?>;
        var map = null;
        const mapId = "{{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}"
        @if ($order->order_type == 'parcel')
        var myLatlng = new google.maps.LatLng({{ $address['latitude'] }}, {{ $address['longitude'] }});
        @else
        @php($default_location = App\CentralLogics\Helpers::get_business_settings('default_location'))
        var myLatlng = new google.maps.LatLng(
            {{ isset($order->store) ? $order->store->latitude : (isset($default_location) ? $default_location['lat'] : 0) }},
            {{ isset($order->store) ? $order->store->longitude : (isset($default_location['lng']) ? $default_location['lng'] : 0) }}
        );
        @endif
        var dmbounds = new google.maps.LatLngBounds(null);
        var locationbounds = new google.maps.LatLngBounds(null);
        var dmMarkers = [];
        dmbounds.extend(myLatlng);
        locationbounds.extend(myLatlng);
        var myOptions = {
            center: myLatlng,
            zoom: 13,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapId: mapId,

            panControl: true,
            mapTypeControl: false,
            panControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            zoomControl: true,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle.LARGE,
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            scaleControl: false,
            streetViewControl: false,
            streetViewControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            }
        };

        function initializeGMap() {

            map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

            var infowindow = new google.maps.InfoWindow();
            @if ($order->store)
            var activeIconContent = document.createElement("img");
                activeIconContent.src = "{{ asset('assets/admin/img/restaurant_map.png') }}";
                activeIconContent.alt = "Active DM";
                activeIconContent.style.width = '100%';
                activeIconContent.style.height = '100%';
                activeIconContent.style.borderRadius = '50%';
            var Restaurantmarker = new google.maps.marker.AdvancedMarkerElement({
                map: map,
                position: new google.maps.LatLng({{ $order->store->latitude }},
                    {{ $order->store->longitude }}),
                title: "{{ Str::limit($order?->store?->name, 15, '...') }}",
                content: activeIconContent,
            });

            google.maps.event.addListener(Restaurantmarker, 'click', (function(Restaurantmarker) {
                return function() {

                    infowindow.setContent(
                        "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ $order?->store?->logo_full_url ?? asset('assets/admin/img/160x160/img1.jpg') }}'></div><div class='text-break' style='float:right; padding: 10px;'><b>{{ Str::limit($order?->store?->name, 15, '...') }}</b><br /> {{ $order->store->address }}</div>"
                    );

                    infowindow.open(map, Restaurantmarker);
                }
            })(Restaurantmarker));
            @endif

            map.fitBounds(dmbounds);
            for (var i = 0; i < deliveryMan.length; i++) {
                if (deliveryMan[i].lat) {
                    // var contentString = "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ asset('storage/app/public/delivery-man') }}/"+deliveryMan[i].image+"'></div><div style='float:right; padding: 10px;'><b>"+deliveryMan[i].name+"</b><br/> "+deliveryMan[i].location+"</div>";
                    var point = new google.maps.LatLng(deliveryMan[i].lat, deliveryMan[i].lng);
                    dmbounds.extend(point);
                    map.fitBounds(dmbounds);
                    var activeIconContent = document.createElement("img");
                activeIconContent.src = "{{ asset('assets/admin/img/delivery_boy_map.png') }}";
                activeIconContent.alt = "Active DM";
                activeIconContent.style.width = '100%';
                activeIconContent.style.height = '100%';
                activeIconContent.style.borderRadius = '50%';
                    var marker = new google.maps.marker.AdvancedMarkerElement({
                        map: map,
                        position: point,
                        title: deliveryMan[i].location,
                        content: activeIconContent,
                    });
                    dmMarkers[deliveryMan[i].id] = marker;
                    google.maps.event.addListener(marker, 'click', (function(marker, i) {
                        return function() {
                            infowindow.setContent(
                                "<div style='float:left'><img style='max-height:40px;wide:auto;' src='"+ deliveryMan[i].image_link +"'></div><div style='float:right; padding: 10px;'><b>" + deliveryMan[i].name + "</b><br/> " + deliveryMan[i].location + "</div>");
                            infowindow.open(map, marker);
                        }
                    })(marker, i));
                }

            };
        }

        function initMap() {
            let map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: {
                    lat: {{ isset($order->store) ? $order->store->latitude : '23.757989' }},
                    lng: {{ isset($order->store) ? $order->store->longitude : '90.360587' }}
                },
                mapId: mapId,
            });

            let zonePolygon = null;

            //get current location block
            let infoWindow = new google.maps.InfoWindow();
            // Try HTML5 geolocation.
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        myLatlng = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                        infoWindow.setPosition(myLatlng);
                        infoWindow.setContent("Location found.");
                        infoWindow.open(map);
                        map.setCenter(myLatlng);
                    },
                    () => {
                        handleLocationError(true, infoWindow, map.getCenter());
                    }
                );
            } else {
                // Browser doesn't support Geolocation
                handleLocationError(false, infoWindow, map.getCenter());
            }
            //-----end block------
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            let markers = [];
            const bounds = new google.maps.LatLngBounds();
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }
                // Clear out the old markers.
                markers.forEach((marker) => {
                    marker.setMap(null);
                });
                markers = [];
                // For each place, get the icon, name and location.
                places.forEach((place) => {
                    if (!place.geometry || !place.geometry.location) {
                        console.log("Returned place contains no geometry");
                        return;
                    }
                    console.log(place.geometry.location);
                    if (!google.maps.geometry.poly.containsLocation(
                        place.geometry.location,
                        zonePolygon
                    )) {
                        toastr.error('{{ 'fuera de cobertura' }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        return false;
                    }

                    document.getElementById('latitude').value = place.geometry.location.lat();
                    document.getElementById('longitude').value = place.geometry.location.lng();
                    markers.push(
                        new google.maps.marker.AdvancedMarkerElement({
                            map,
                            title: place.name,
                            position: place.geometry.location,
                        })
                    );

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });
            @if ($order->store)
            $.get({
                url: '{{ url('/') }}/admin/zone/get-coordinates/{{ $order->store->zone_id }}',
                dataType: 'json',
                success: function(data) {
                    zonePolygon = new google.maps.Polygon({
                        paths: data.coordinates,
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: 'white',
                        fillOpacity: 0,
                    });
                    zonePolygon.setMap(map);
                    zonePolygon.getPaths().forEach(function(path) {
                        path.forEach(function(latlng) {
                            bounds.extend(latlng);
                            map.fitBounds(bounds);
                        });
                    });
                    map.setCenter(data.center);
                    google.maps.event.addListener(zonePolygon, 'click', function(mapsMouseEvent) {
                        infoWindow.close();
                        // Create a new InfoWindow.
                        infoWindow = new google.maps.InfoWindow({
                            position: mapsMouseEvent.latLng,
                            content: JSON.stringify(mapsMouseEvent.latLng.toJSON(), null,
                                2),
                        });
                        var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                        var coordinates = JSON.parse(coordinates);

                        document.getElementById('latitude').value = coordinates['lat'];
                        document.getElementById('longitude').value = coordinates['lng'];
                        infoWindow.open(map);
                    });
                },
            });
            @endif

        }

        $(document).ready(function() {
            var orderDmMarker = null;
            var orderDmLivePoll = null;
            var tdOrderDmLiveUrl = @json(route('admin.order.delivery-man-live-location', $order->id));

            function stopOrderDmLivePoll() {
                if (orderDmLivePoll) {
                    clearInterval(orderDmLivePoll);
                    orderDmLivePoll = null;
                }
            }

            function tickOrderDmLocation() {
                if (!orderDmMarker || !tdOrderDmLiveUrl) return;
                fetch(tdOrderDmLiveUrl, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function(r) { return r.json(); }).then(function(j) {
                    if (!j || !j.ok || j.lat == null || j.lng == null) return;
                    var la = parseFloat(j.lat);
                    var ln = parseFloat(j.lng);
                    if (!isFinite(la) || !isFinite(ln)) return;
                    orderDmMarker.position = new google.maps.LatLng(la, ln);
                }).catch(function() {});
            }

            // Re-init map before show modal
            $('#myModal').on('shown.bs.modal', function(event) {
                initMap();
                var button = $(event.relatedTarget);
                $("#dmassign-map").css("width", "100%");
                $("#map_canvas").css("width", "100%");
            });

            // Trigger map resize event after modal shown
            $('#myModal').on('shown.bs.modal', function() {
                initializeGMap();
                google.maps.event.trigger(map, "resize");
                map.setCenter(myLatlng);
            });

            // Address change modal modal shown
            $('#shipping-address-modal').on('shown.bs.modal', function() {
                initMap();
                // google.maps.event.trigger(map, "resize");
                // map.setCenter(myLatlng);
            });


            function initializegLocationMap() {
                orderDmMarker = null;
                map = new google.maps.Map(document.getElementById("location_map_canvas"), myOptions);

                var infowindow = new google.maps.InfoWindow();

                @if ($order->customer && isset($address))
                var activeIconContent = document.createElement("img");
                activeIconContent.src = "{{ asset('assets/admin/img/customer_location.png') }}";
                activeIconContent.alt = "Active DM";
                activeIconContent.style.width = '100%';
                activeIconContent.style.height = '100%';
                activeIconContent.style.borderRadius = '50%';
                var marker = new google.maps.marker.AdvancedMarkerElement({
                    map: map,
                    position: new google.maps.LatLng({{ $address['latitude'] }},
                        {{ $address['longitude'] }}),
                    title: "{{ $order->customer->f_name }} {{ $order->customer->l_name }}",
                    content: activeIconContent,
                });

                google.maps.event.addListener(marker, 'click', (function(marker) {
                    return function() {
                        infowindow.setContent(
                            "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ $order?->customer?->image_full_url ?? asset('assets/admin/img/160x160/img1.jpg') }}'></div><div style='float:right; padding: 10px;'><b>{{ $order->customer->f_name }} {{ $order->customer->l_name }}</b><br />{{ $address['address'] }}</div>"
                        );
                        infowindow.open(map, marker);
                    }
                })(marker));
                locationbounds.extend(marker.position);
                @endif
                @if ($order->delivery_man && $order->dm_last_location)
                var activeIconContent = document.createElement("img");
                activeIconContent.src = "{{ asset('assets/admin/img/delivery_boy_map.png') }}";
                activeIconContent.alt = "Active DM";
                activeIconContent.style.width = '100%';
                activeIconContent.style.height = '100%';
                activeIconContent.style.borderRadius = '50%';
                var dmmarker = new google.maps.marker.AdvancedMarkerElement({
                    map: map,
                    position: new google.maps.LatLng({{ $order->dm_last_location['latitude'] }},
                        {{ $order->dm_last_location['longitude'] }}),
                    title: "{{ $order->delivery_man->f_name }} {{ $order->delivery_man->l_name }}",
                    content: activeIconContent,
                });

                google.maps.event.addListener(dmmarker, 'click', (function(dmmarker) {
                    return function() {
                        infowindow.setContent(
                            "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ $order?->delivery_man?->image_full_url ?? asset('assets/admin/img/160x160/img1.jpg') }}'></div> <div style='float:right; padding: 10px;'><b>{{ $order->delivery_man->f_name }} {{ $order->delivery_man->l_name }}</b><br /> {{ $order->dm_last_location['location'] }}</div>"
                        );
                        infowindow.open(map, dmmarker);
                    }
                })(dmmarker));
                locationbounds.extend(dmmarker.position);
                orderDmMarker = dmmarker;
                @endif

                @if ($order->store)
                var activeIconContent = document.createElement("img");
                activeIconContent.src = "{{ asset('assets/admin/img/restaurant_map.png') }}";
                activeIconContent.style.width = '25px';
                activeIconContent.alt = "Active DM";
                activeIconContent.style.width = '100%';
                activeIconContent.style.height = '100%';
                activeIconContent.style.borderRadius = '50%';
                var Retaurantmarker = new google.maps.marker.AdvancedMarkerElement({
                    map: map,
                    position: new google.maps.LatLng({{ $order->store->latitude }},
                        {{ $order->store->longitude }}),
                    title: "{{ Str::limit($order?->store?->name, 15, '...') }}",
                    content:activeIconContent,
                });

                google.maps.event.addListener(Retaurantmarker, 'click', (function(Retaurantmarker) {
                    return function() {
                        infowindow.setContent(
                            "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ $order?->store?->logo_full_url ?? asset('assets/admin/img/100x100/1.png') }}'></div> <div style='float:right; padding: 10px;'><b>{{ Str::limit($order?->store?->name, 15, '...') }}</b><br /> {{ $order->store->address }}</div>"
                        );
                        infowindow.open(map, Retaurantmarker);
                    }
                })(Retaurantmarker));
                locationbounds.extend(Retaurantmarker.position);
                @endif


                google.maps.event.addListenerOnce(map, 'idle', function() {
                    map.fitBounds(locationbounds);
                });
            }

            // Re-init map before show modal + posición en vivo del repartidor
            $('#locationModal').on('shown.bs.modal', function(event) {
                initializegLocationMap();
                stopOrderDmLivePoll();
                @if ($order->delivery_man)
                tickOrderDmLocation();
                orderDmLivePoll = setInterval(tickOrderDmLocation, 5000);
                @endif
            });
            $('#locationModal').on('hidden.bs.modal', function() {
                stopOrderDmLivePoll();
                orderDmMarker = null;
            });


            $('.dm_list').on('click', function() {
                var id = $(this).data('id');
                map.panTo(dmMarkers[id].position);
                map.setZoom(13);
            });
        })
    </script>

    <script src="{{ asset('assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script type="text/javascript">
        $(function() {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'order_proof[]',
                maxCount: 6-{{ ($order->order_proof && is_array($order->order_proof))?count(json_decode($order->order_proof)):0 }},
                rowHeight: '176px !important',
                groupClassName: 'spartan_item_wrapper min-w-176px max-w-176px',
                maxFileSize: '',
                placeholderImage: {
                    image: "{{ asset('assets/admin/img/upload-img.png') }}",
                    width: '176px'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function(index, file) {

                },
                onRenderedPreview: function(index) {

                },
                onRemoveRow: function(index) {

                },
                onExtensionErr: function(index, file) {
                    toastr.error(
                        "{{ 'Por favor ingrese solo archivos tipo png o jpg' }}", {
                            CloseButton: true,
                            ProgressBar: true
                        });
                },
                onSizeErr: function(index, file) {
                    toastr.error("{{ 'tamaño de archivo demasiado grande' }}", {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });
    </script>
@endpush
