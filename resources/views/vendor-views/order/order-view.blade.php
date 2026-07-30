@extends('layouts.vendor.app')

@section('title', 'Detalles del pedido')


@section('content')
    <?php

    $tax_included =0;
    if (count($order->details) > 0) {
        $campaign_order = isset($order?->details[0]?->item_campaign_id ) ? true : false;
    }
    $max_processing_time = explode('-', $order['store']['delivery_time'])[0];
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
                    </h1>
                </div>

                <div class="col-sm-auto">
                    <a class="btn btn-icon btn-sm btn-soft-secondary rounded-circle mr-1"
                        href="{{ route('vendor.order.details', [$order['id'] - 1]) }}" data-toggle="tooltip"
                        data-placement="top" title="Previous order">
                        <i class="tio-chevron-left"></i>
                    </a>
                    <a class="btn btn-icon btn-sm btn-soft-secondary rounded-circle"
                        href="{{ route('vendor.order.details', [$order['id'] + 1]) }}" data-toggle="tooltip"
                        data-placement="top" title="Next order">
                        <i class="tio-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row" id="printableArea">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <!-- Card -->
                <div class="card mb-3 mb-lg-5">
                    <!-- Header -->
                    <div class="card-header border-0 align-items-start flex-wrap">
                        <div class="order-invoice-left d-flex d-sm-flex justify-content-between">
                            <div>
                                <h1 class="page-header-title">
                                    {{ 'Pedido' }} #{{ $order['id'] }}
                                    @if (!empty($order->tootli_direct))
                                        <span class="badge badge-soft-dark ml-sm-3">{{ 'insignia de pedido directo de tootli' }}</span>
                                    @endif
                                    @if ($order->edited)
                                        <span class="badge badge-soft-danger ml-sm-3">
                                            {{ 'editado' }}
                                        </span>
                                    @endif
                                </h1>
                                <span class="mt-2 d-block">
                                    <i class="tio-date-range"></i>
                                    {{ date('d M Y ' . config('timeformat'), strtotime($order['created_at'])) }}
                                </span>
                                @if ($order->schedule_at && $order->scheduled)
                                    <h6 class="text-capitalize">
                                        {{ 'programado en' }}
                                        : <label
                                            class="fz--10 badge badge-soft-warning">{{ date('d M Y ' . config('timeformat'), strtotime($order['schedule_at'])) }}</label>
                                    </h6>
                                    @if ($order->delivery_time_window)
                                        <h6 class="text-capitalize">
                                            {{ 'ventana de tiempo de entrega' }}
                                            : <label class="fz--10 badge badge-soft-info">{{ $order->delivery_time_window }}</label>
                                        </h6>
                                        <div class="info-notes-bg px-3 color-222324CC py-2 rounded fs-12 gap-2 mt-2" style="background-color: #ffeeba; border: 1px solid #ffeeba; color: #856404;">
                                            ⚠️ <strong>{{ 'advertencia de orden programada' }}:</strong>
                                            {{ 'prepárate para estar listo por' }} <strong>{{ date('h:i A', strtotime($order->schedule_at)) }}</strong> {{ 'a más tardar' }}.
                                        </div>
                                    @endif
                                @endif
                                @if($order['cancellation_reason'])
                                <h6>
                                    <span class="text-danger">{{ 'motivo de cancelación del pedido' }} :</span>
                                    {{ $order['cancellation_reason'] }}
                                </h6>
                                @endif

                           
                                <!-- New Note -->
                                @if ($order['bring_change_amount'] > 0)
                                <div class="info-notes-bg px-3 color-222324CC py-2 rounded fs-12  gap-2 mt-2">
                                    {{ 'por favor trae' }} <strong class="text-title"> {{  \App\CentralLogics\Helpers::format_currency($order['bring_change_amount']) }}</strong> {{ 'en cambio al realizar la entrega' }}.
                                </div>
                                @endif
                                <!-- New Note End -->

                                <!-- Cash on Pickup Banner for Vendor -->
                                @if ($order['payment_method'] == 'cash_on_delivery' && $order['order_type'] == 'delivery' && $order->cash_on_pickup_amount > 0)
                                <div class="info-notes-bg px-3 color-222324CC py-2 rounded fs-12 gap-2 mt-2" style="background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404;">
                                    <i class="tio-money-clean"></i>
                                    <strong>{{ 'pago en recoleccion' }}:</strong>
                                    {{ 'recibirías' }}
                                    <strong class="text-title" style="color: #856404;"> {{ \App\CentralLogics\Helpers::format_currency($order->cash_on_pickup_amount) }} </strong>
                                    {{ 'en efectivo del repartidor al recoger el pedido' }}.
                                </div>
                                @endif
                                @if ($order['unavailable_item_note'])
                                    <h6 class="w-100 badge-soft-warning p-1 rounded mt-2">
                                        <span class="text-dark">
                                            {{ 'nota de artículo no disponible del pedido' }} :
                                        </span>
                                        {{ $order['unavailable_item_note'] }}
                                    </h6>
                                @endif
                                @if ($order['delivery_instruction'])
                                    <h6 class="w-100 badge-soft-warning p-1 rounded mt-2">
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
                            </div>
                            <div class="d-sm-none">
                                <a class="btn btn--primary print--btn font-regular"
                                    href={{ route('vendor.order.generate-invoice', [$order['id']]) }}>
                                    <i class="tio-print mr-sm-1"></i> <span>{{ 'imprimir factura' }}</span>
                                </a>
                            </div>
                        </div>


                        <div class="order-invoice-right mt-3 mt-sm-0">
                            <div class="btn--container ml-auto align-items-center justify-content-end">
                                <a class="btn btn--primary print--btn font-regular d-none d-sm-block"
                                    href={{ route('vendor.order.generate-invoice', [$order['id']]) }}>
                                    <i class="tio-print mr-sm-1"></i> <span>{{ 'imprimir factura' }}</span>
                                </a>
                            </div>
                            <div class="text-right mt-3 order-invoice-right-contents text-capitalize">
                                <h6>
                                    {{ 'estado de pago' }} :
                                    @if ($order['payment_status'] == 'paid')
                                        <span class="badge badge-soft-success ml-sm-3">
                                            {{ 'pagado' }}
                                        </span>
                                        @elseif ($order['payment_status'] == 'partially_paid')

                                        @if ($order->payments()->where('payment_status','unpaid')->exists())
                                        <span class="text-danger">{{ 'parcialmente pagado' }}</span>
                                        @else
                                        <span class="text-success">{{ 'pagado' }}</span>
                                        @endif
                                    @else
                                        <span class="badge badge-soft-danger ml-sm-3">
                                            {{ 'no pagado' }}
                                        </span>
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
                                <h6 class="text-capitalize">
                                    {{ 'método de pago' }} :
                                    {{ translate(str_replace('_', ' ', $order['payment_method'])) }}
                                </h6>
                                @if ($order['transaction_reference'])
                                    <h6 class="">
                                        {{ 'código de referencia' }} :
                                        <button class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                            data-target=".bd-example-modal-sm">
                                            {{ 'agregar' }}
                                        </button>
                                    </h6>
                                @endif
                                <h6 class="text-capitalize">{{ 'tipo de orden' }}
                                    : <label
                                        class="fz--10 badge m-0 badge-soft-primary">
                                        {{ $order['order_type'] === 'dine_in'
                                            ? 'En restaurante'
                                            : ($order['order_type'] === 'take_away'
                                                ? 'Llevar'
                                                : (!empty($order->tootli_direct) ? 'Domicilio (Tootli Directo)' : 'entrega a domicilio')) }}
                                    </label>
                                </h6>
                                @php
                                    $posMeta = is_array($order->pos_payment_meta)
                                        ? $order->pos_payment_meta
                                        : (is_string($order->pos_payment_meta)
                                            ? json_decode($order->pos_payment_meta, true)
                                            : null);
                                @endphp
                                @if (is_array($posMeta) && isset($posMeta['card_gross_amount']) && $posMeta['card_gross_amount'] !== null)
                                    <h6>{{ 'Monto cobrado' }}:
                                        {{ \App\CentralLogics\Helpers::format_currency((float) $posMeta['card_gross_amount']) }}
                                    </h6>
                                    <h6>{{ 'Monto después de la comisión' }}:
                                        {{ \App\CentralLogics\Helpers::format_currency((float) ($posMeta['card_net_amount'] ?? 0)) }}
                                    </h6>
                                @endif
                                <h6>
                                    {{ 'estado del pedido' }} :
                                    @if ($order['order_status'] == 'pending')
                                        <span class="badge badge-soft-info ml-2 ml-sm-3 text-capitalize">
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
                                    @else
                                        <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                                            {{ str_replace('_', ' ', $order['order_status']) }}
                                        </span>
                                    @endif
                                </h6>
                                @if ($order->order_attachment)
                                        @php
                                            $order_images = json_decode($order->order_attachment,true);
                                        @endphp
                                    {{-- @if (is_array($order_images)) --}}
                                        <h5 class="text-dark">
                                            {{ 'prescripción' }}:
                                        </h5>
                                        <div class="d-flex flex-wrap flex-md-row-reverse __gap-15px" >
                                            @foreach ($order_images as $key => $item)
                                            @php($item = is_array($item)?$item:['img'=>$item,'storage'=>'public'])
                                                <div>
                                                    <button class="btn w-100 px-0" data-toggle="modal"
                                                        data-target="#prescriptionimagemodal{{ $key }}"
                                                        title="{{ 'archivo adjunto de pedido' }}">
                                                        <div class="gallary-card ml-auto">
                                                            <img  src="{{\App\CentralLogics\Helpers::get_full_url('order',$item['img'],$item['storage']) }}"
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
                                                            <div class="modal-body">
                                                                <img src="{{\App\CentralLogics\Helpers::get_full_url('order',$item['img'],$item['storage']) }}"
                                                                    class="initial--22 w-100" alt="image">
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
                        <?php
                        $total_addon_price = 0;
                        $product_price = 0;
                        $store_discount_amount = 0;
                        $admin_flash_discount_amount = $order['flash_admin_discount_amount'];
                        $ref_bonus_amount = $order['ref_bonus_amount'];
                        $extra_packaging_amount = $order['extra_packaging_amount'];
                        $store_flash_discount_amount = $order['flash_store_discount_amount'];

                        if ($order->prescription_order == 1) {
                            $product_price = $order['order_amount'] - $order['delivery_charge'] - $order['total_tax_amount'] - $order['dm_tips'] - $order['additional_charge'] + $order['store_discount_amount'];
                            if($order->tax_status == 'included'){
                                $product_price += $order['total_tax_amount'];
                            }
                        }

                        $total_addon_price = 0;
                        ?>
                        <div class="table-responsive">
                            <table
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table dataTable no-footer mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">{{ '#' }}</th>
                                        <th class="border-0">{{ 'detalles del artículo' }}</th>
                                        @if ($order->store->module->module_type == 'food')
                                            <th class="border-0">{{ 'complementos' }}</th>
                                        @endif
                                        <th class="text-right  border-0">{{ 'precio' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->details as $key => $detail)
                                        @if (isset($detail->item_id))
                                            @php($detail->item = json_decode($detail->item_details, true))
                                            @php($product = \App\Models\Item::where(['id' => $detail->item['id']])->first())
                                            <!-- Media -->
                                            <tr>
                                                <td>
                                                    <div>
                                                        {{ $key + 1 }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="media media--sm">
                                                        <a class="avatar avatar-xl mr-3"
                                                            href="{{ route('vendor.item.view', $detail->item['id']) }}">
                                                            <img class="img-fluid rounded onerror-image"
                                                            src="{{ $product->image_full_url  ?? asset('assets/admin/img/160x160/img2.jpg') }}"
                                                                 data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                                                alt="Image Description">
                                                        </a>
                                                        <div class="media-body">
                                                            <div>
                                                                <strong
                                                                    class="line--limit-1">{{ Str::limit($detail->item['name'], 25, '...') }}</strong>
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
                                                                                    <span class="d-block text-capitalize">
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
                                                @if ($order->store->module->module_type == 'food')
                                                    <td>
                                                        <div>
                                                            @foreach (json_decode($detail['add_ons'], true) as $key2 => $addon)
                                                                @if ($key2 == 0)
                                                                    <strong><u>{{ 'complementos' }} :
                                                                        </u></strong>
                                                                @endif
                                                                <div class="font-size-sm text-body">
                                                                    <span>{{ Str::limit($addon['name'], 25, '...') }} :
                                                                    </span>
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
                                                <td>
                                                    <div class="text-right">
                                                        @php($amount = $detail['price'] * $detail['quantity'])
                                                        <h5>{{ \App\CentralLogics\Helpers::format_currency($amount) }}</h5>
                                                    </div>
                                                </td>
                                            </tr>
                                            @php($product_price += $amount)
                                            @php($store_discount_amount += $detail['discount_on_item'] * $detail['quantity'])
                                            <!-- End Media -->
                                        @elseif(isset($detail->item_campaign_id))
                                            @php($detail->campaign = json_decode($detail->item_details, true))
                                            @php($campaign = \App\Models\ItemCampaign::where(['id' => $detail->campaign['id']])->first())
                                            <!-- Media -->
                                            <tr>
                                                <td>
                                                    <div>
                                                        {{ $key + 1 }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="media media--sm">
                                                        <div class="avatar avatar-xl mr-3">
                                                            <img class="img-fluid onerror-image"
                                                            src="{{$campaign?->image_full_url ?? asset('assets/admin/img/160x160/img2.jpg') }}"

                                                                 data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                                                alt="Image Description">
                                                        </div>
                                                        <div class="media-body">
                                                            <div>
                                                                <strong
                                                                    class="line--limit-1">{{ Str::limit($detail->campaign['name'], 25, '...') }}</strong>

                                                                <h6>
                                                                    {{ $detail['quantity'] }} x
                                                                    {{ \App\CentralLogics\Helpers::format_currency($detail['price']) }}
                                                                </h6>

                                                                @if (count(json_decode($detail['variation'], true)) > 0)
                                                                    <strong><u>{{ 'variación' }} :
                                                                        </u></strong>
                                                                    @foreach (json_decode($detail['variation'], true)[0] as $key1 => $variation)
                                                                        @if ($key1 != 'stock')
                                                                            <div class="font-size-sm text-body">
                                                                                <span>{{ $key1 }} : </span>
                                                                                <span
                                                                                    class="font-weight-bold">{{ Str::limit($variation, 25, '...') }}</span>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                @endif

                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                @if ($order->store->module->module_type == 'food')
                                                    <td>
                                                        @foreach (json_decode($detail['add_ons'], true) as $key2 => $addon)
                                                            @if ($key2 == 0)
                                                                <strong><u>{{ 'complementos' }} :
                                                                    </u></strong>
                                                            @endif
                                                            <div class="font-size-sm text-body">
                                                                <span>{{ Str::limit($addon['name'], 20, '...') }} : </span>
                                                                <span class="font-weight-bold">
                                                                    {{ $addon['quantity'] }} x
                                                                    {{ \App\CentralLogics\Helpers::format_currency($addon['price']) }}
                                                                </span>
                                                            </div>
                                                            @php($total_addon_price += $addon['price'] * $addon['quantity'])
                                                        @endforeach
                                                    </td>
                                                @endif
                                                <td>
                                                    <div class="text-right">
                                                        @php($amount = $detail['price'] * $detail['quantity'])
                                                        <h5>{{ \App\CentralLogics\Helpers::format_currency($amount) }}</h5>
                                                    </div>
                                                </td>
                                            </tr>
                                            @php($product_price += $amount)
                                            @php($store_discount_amount += $detail['discount_on_item'] * $detail['quantity'])
                                            <!-- End Media -->
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mx-3">
                            <hr>
                        </div>
                        <?php

                        $coupon_discount_amount = $order['coupon_discount_amount'];

                        $total_price = $product_price + $total_addon_price - $store_discount_amount - $coupon_discount_amount - $admin_flash_discount_amount -$ref_bonus_amount -$extra_packaging_amount -$store_flash_discount_amount;

                        $total_tax_amount = $order['total_tax_amount'];
                        if($order->tax_status == 'included'){
                                $total_tax_amount=0;
                            }
                        $tax_included = \App\Models\BusinessSetting::where(['key'=>'tax_included'])->first() ?  \App\Models\BusinessSetting::where(['key'=>'tax_included'])->first()->value : 0;

                        $store_discount_amount = $order['store_discount_amount'];

                        ?>
                        <div class="row justify-content-md-end mb-3 mx-0 mt-4">
                            <div class="col-md-9 col-lg-8">
                                <dl class="row text-right">
                                    <dt class="col-6">{{ 'precio de los artículos' }}:</dt>
                                    <dd class="col-6">{{ \App\CentralLogics\Helpers::format_currency($product_price) }}
                                    </dd>
                                    @if ($order->store->module->module_type == 'food')
                                        <dt class="col-6">{{ 'costo adicional' }}:</dt>

                                        <dd class="col-6">
                                            {{ \App\CentralLogics\Helpers::format_currency($total_addon_price) }}
                                            <hr>
                                        </dd>
                                    @endif

                                    <dt class="col-6">{{ 'total parcial' }}
                                        @if ($order->tax_status == 'included' ||  $tax_included ==  1)
                                        ({{ 'IVA incluido' }})
                                        @endif
                                        :</dt>

                                    <dd class="col-6">
                                        @if ($order->prescription_order == 1 && in_array($order['order_status'],['pending','confirmed','processing','accepted']))
                                            <button class="btn btn-sm" type="button" data-toggle="modal"
                                                data-target="#edit-order-amount"><i class="tio-edit"></i></button>
                                        @endif
                                        {{ \App\CentralLogics\Helpers::format_currency($product_price + $total_addon_price) }}
                                    </dd>
                                    <dt class="col-6">{{ 'descuento' }}:</dt>
                                    <dd class="col-6">
                                        @if ($order->prescription_order == 1 && in_array($order['order_status'],['pending','confirmed','processing','accepted']))
                                            <button class="btn btn-sm" type="button" data-toggle="modal"
                                                data-target="#edit-discount-amount"><i class="tio-edit"></i></button>
                                        @endif
                                        - {{ \App\CentralLogics\Helpers::format_currency($store_discount_amount + $admin_flash_discount_amount  +$store_flash_discount_amount) }}
                                    </dd>



                                    <dt class="col-6">{{ 'cupón de descuento' }}:</dt>
                                    <dd class="col-6">
                                        - {{ \App\CentralLogics\Helpers::format_currency($coupon_discount_amount) }}</dd>

                                    @if ($ref_bonus_amount > 0)
                                    <dt class="col-6">{{ 'Descuento por recomendación' }}:</dt>
                                    <dd class="col-6">
                                        - {{ \App\CentralLogics\Helpers::format_currency($ref_bonus_amount) }}</dd>

                                    @endif

                                    @if ($order->tax_status == 'excluded' || $order->tax_status == null  )
                                    <dt class="col-sm-6">{{ 'iva/impuesto' }}:</dt>
                                    <dd class="col-sm-6">
                                        +
                                        {{ \App\CentralLogics\Helpers::format_currency($total_tax_amount) }}
                                    </dd>
                                    @endif
                                    <dt class="col-6">{{ 'consejos de repartidor' }}</dt>
                                    <dd class="col-6">
                                        + {{ \App\CentralLogics\Helpers::format_currency($order->dm_tips) }}</dd>
                                    <dt class="col-6">{{ 'tarifa de entrega' }}:</dt>
                                    <dd class="col-6">
                                        @php($del_c = $order['delivery_charge'])
                                        + {{ \App\CentralLogics\Helpers::format_currency($del_c) }}
                                        <hr>
                                    </dd>
                                    <dt class="col-6">{{ \App\CentralLogics\Helpers::get_business_data('additional_charge_name')??'cargo adicional' }}:</dt>
                                    <dd class="col-6">
                                        @php($additional_charge = $order['additional_charge'])
                                        + {{ \App\CentralLogics\Helpers::format_currency($additional_charge) }}
                                    </dd>
                                    @if ($extra_packaging_amount > 0)
                                    <dt class="col-6">{{ 'Cantidad de embalaje adicional' }}:</dt>
                                    <dd class="col-6">
                                        + {{ \App\CentralLogics\Helpers::format_currency($extra_packaging_amount) }}</dd>
                                    @endif
                                    @if ($order['partially_paid_amount'] > 0)

                                    <dt class="col-6">{{ 'cantidad parcialmente pagada' }}:</dt>
                                    <dd class="col-6">
                                        @php($partially_paid_amount = $order['partially_paid_amount'])
                                            {{ \App\CentralLogics\Helpers::format_currency($partially_paid_amount) }}
                                    </dd>
                                    <dt class="col-6">{{ 'monto adeudado' }}:</dt>
                                    @if ($order['payment_method'] == 'partial_payment')

                                    <dd class="col-6">
                                            {{ \App\CentralLogics\Helpers::format_currency($order->order_amount-$partially_paid_amount) }}
                                    </dd>
                                    @else
                                    <dd class="col-6">
                                            {{ \App\CentralLogics\Helpers::format_currency(0) }}
                                    </dd>
                                    @endif
                                    @endif

                                    <dt class="col-6">{{ 'total' }}:</dt>
                                    <dd class="col-6">
                                        {{ \App\CentralLogics\Helpers::format_currency($product_price + $del_c + $total_tax_amount + $total_addon_price + $additional_charge - $coupon_discount_amount - $store_discount_amount - $admin_flash_discount_amount  - $ref_bonus_amount + $extra_packaging_amount-$store_flash_discount_amount + $order->dm_tips) }}
                                    </dd>
                                    @if ($order?->payments)
                                        @foreach ($order?->payments as $payment)
                                            @if ($payment->payment_status == 'paid')
                                                @if ( $payment->payment_method == 'cash_on_delivery')

                                                <dt class="col-sm-6">{{ 'Pagado en efectivo' }} ({{  'BACALAO'}}) :</dt>
                                                @else

                                                <dt class="col-sm-6">{{ 'Pagado por' }} {{  translate($payment->payment_method)}} :</dt>
                                                @endif
                                            @else

                                            <dt class="col-sm-6">{{ 'Monto adeudado' }} ({{  $payment->payment_method == 'cash_on_delivery' ?  'BACALAO' : translate($payment->payment_method) }}) :</dt>
                                            @endif
                                        <dd class="col-sm-6">
                                            {{ \App\CentralLogics\Helpers::format_currency($payment->amount) }}
                                        </dd>
                                        @endforeach
                                    @endif
                                </dl>
                                <!-- End Row -->
                            </div>
                        </div>
                        <!-- End Row -->
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>

            <div class="col-lg-4">
                <!-- Card -->
                @if ($order->order_status != 'refund_requested' &&
                    $order->order_status != 'refunded' &&
                    $order->order_status != 'delivered')
                    <div class="card mb-2">
                        <!-- Header -->
                        <div class="card-header justify-content-center text-center px-0 mx-4">
                            <h5 class="card-header-title text-capitalize">
                                <span>{{ 'configuración del pedido' }}</span>
                            </h5>
                        </div>
                        <!-- End Header -->

                        <!-- Body -->

                        <div class="card-body">
                            <!-- Order Status Flow Starts -->
                            @php($order_delivery_verification = (bool) \App\Models\BusinessSetting::where(['key' => 'order_delivery_verification'])->first()->value)
                            <div class="mb-4">
                                <div class="row g-1">
                                    <div class="{{ config('canceled_by_store') ? 'col-6' : 'col-12' }}">
                                        <a class="btn btn--primary w-100 fz--13 px-2 {{ $order['order_status'] == 'pending' ? '' : 'd-none' }} route-alert"
                                           data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'confirmed']) }}"
                                           data-message="{{ '¿Confirmar este pedido?' }}"
                                            href="javascript:">{{ 'confirmar este pedido' }}</a>
                                    </div>
                                    @if (config('canceled_by_store'))
                                        <div class="col-6">
                                            <a class="btn btn--danger w-100 fz--13 px-2 cancelled-status {{ $order['order_status'] == 'pending' ? '' : 'd-none' }}"
                                               >{{ 'Cancelar pedido' }}</a>
                                        </div>
                                    @endif
                                </div>
                                    @if ($order->store && $order->store->module->module_type == 'food')
                                        <a class="btn btn--primary w-100 order-status-change-alert {{ $order['order_status'] == 'confirmed' || $order['order_status'] == 'accepted' ? '' : 'd-none' }}"

                                           data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'processing']) }}"
                                           data-message="{{ '¿Cambiar estado a cocinar?' }}"
                                           data-verification="false"
                                           data-processing-time="{{ $max_processing_time }}"
                                           href="javascript:">{{ 'proceder para el procesamiento' }}</a>
                                    @else
                                    <a class="btn btn--primary w-100 route-alert  {{ $order['order_status'] == 'confirmed' || $order['order_status'] == 'accepted' ? '' : 'd-none' }}"
                                       data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'processing']) }}"
                                       data-message="{{ 'proceder para el procesamiento' }}"
                                    href="javascript:">{{ 'proceder para el procesamiento' }}</a>
                                    @endif
                                <a class="btn btn--primary w-100 route-alert {{ $order['order_status'] == 'processing' ? '' : 'd-none' }}"
                                   data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'handover']) }}"
                                   data-message="{{ 'prepararse para la entrega' }}"
                                    href="javascript:">{{ 'prepararse para la entrega' }}</a>
                                 @if($order['order_status'] == 'handover'|| ($order['order_status'] == 'picked_up' && $order->store->sub_self_delivery == 1))
                                    <a class="btn  w-100
                                    {{ (in_array($order['order_type'], ['take_away', 'dine_in'], true) || $order->store->sub_self_delivery == 1)  ?  'btn--primary order-status-change-alert'  :  'btn--secondary  self-delivery-warning' }} "
                                       data-url="{{ route('vendor.order.status', ['id' => $order['id'], 'order_status' => 'delivered']) }}"
                                       data-message="{{ '¿Cambiar el estado a entregado (el estado del pago se pagará si no es así)?' }}"
                                       data-verification="{{ $order_delivery_verification ? 'true' : 'false' }}"
                                        href="javascript:">{{ 'hacer entregado' }}</a>
                                 @endif

                            </div>
                        </div>

                        <!-- End Body -->
                    </div>
                @endif
                <!-- End Card -->
                @if ($order->order_status == 'canceled')
                <ul class="delivery--information-single mt-3">
                    <li>
                        <span class=" badge badge-soft-danger "> {{ 'Cancelar motivo' }} :</span>
                        <span class="info">  {{ $order->cancellation_reason }} </span>
                    </li>

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
                <hr class="w-100">
            @endif
                @if (!in_array($order['order_type'], ['take_away', 'dine_in'], true))
                    <!-- Card -->
                    <div class="card mb-2">
                        <!-- Header -->
                        <div class="card-header">
                            <h4 class="card-header-title">
                                <span class="card-header-icon"><i class="tio-user"></i></span>
                                <span>{{ 'repartidor' }}</span>
                            </h4>
                        </div>
                        <!-- End Header -->

                        <!-- Body -->
                        <div class="card-body">
                            @if ($order->delivery_man)
                                <div class="media align-items-center customer--information-single" href="javascript:">
                                    <div class="avatar avatar-circle">
                                        <img class="avatar-img onerror-image"
                                             data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                                             src="{{ $order->delivery_man->image_full_url }}"
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
                                </div>

                                @if (!in_array($order['order_type'], ['take_away', 'dine_in'], true))
                                    <hr>
                                    @php($address = $order->dm_last_location)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5>{{ 'última ubicación' }}</h5>
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
                                @endif
                            @else
                                <span class="badge badge-soft-danger py-2 d-block qcont">
                                    {{ 'repartidor no encontrado' }}
                                </span>
                            @endif
                        </div>
                        <!-- End Body -->
                    </div>
                @endif
                <!-- End Card -->

                <!-- order proof -->
                <div class="card mb-2 mt-2">
                    <div class="card-header border-0 text-center pb-0">
                        <h4 class="m-0">{{ 'prueba de entrega' }} </h4>
                        @if ($order['store']['sub_self_delivery'])

                        <button class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                            data-target=".order-proof-modal">
                                            {{ 'agregar' }}
                                        </button>
                        @endif
                    </div>
                    @php($data = isset($order->order_proof) ? json_decode($order->order_proof, true) : 0)
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
                                             src="{{\App\CentralLogics\Helpers::get_full_url('order',$img['img'],$img['storage']) }}"
                                             alt="image">
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
                                                        class="initial--22 w-100" alt="img">
                                                </div>
                                                @php($storage = $img['storage']??'public')
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

                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title">
                            <span class="card-header-icon"><i class="tio-user"></i></span>
                            <span>{{ 'Cliente' }}</span>
                        </h4>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->
                    @if ($order->customer)
                        <div class="card-body">

                            <div class="media align-items-center customer--information-single" href="javascript:">
                                <div class="avatar avatar-circle">
                                    <img class="avatar-img onerror-image "
                                         data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                                         src="{{ $order->customer->image_full_url }}"
                                        alt="Image Description">
                                </div>
                                <div class="media-body">
                                    <span
                                        class="text-body d-block text-hover-primary mb-1">{{ $order->customer['f_name'] . ' ' . $order->customer['l_name'] }}</span>

                                    <span class="text--title font-semibold d-flex align-items-center">
                                        <i class="tio-shopping-basket-outlined mr-2"></i>
                                        {{ $order->customer->orders_count }}
                                        {{ 'pedidos entregados' }}
                                    </span>

                                    <span class="text--title font-semibold d-flex align-items-center">
                                        <i class="tio-call-talking-quiet mr-2"></i>
                                        {{ $order->customer['phone'] }}
                                    </span>

                                    <span class="text--title font-semibold d-flex align-items-center">
                                        <i class="tio-email-outlined mr-2"></i>
                                        {{ $order->customer['email'] }}
                                    </span>

                                </div>
                            </div>
                            <hr>




                            @if ($order->delivery_address)
                                @php($address = json_decode($order->delivery_address, true))
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>{{ 'información de entrega' }}</h5>
                                </div>
                                @if (isset($address))
                                    <span class="delivery--information-single d-block">
                                        <div class="d-flex">
                                            <span class="name">{{ 'nombre' }}:</span>
                                            <span class="info">{{ $address['contact_person_name'] }}</span>
                                        </div>
                                        <div class="d-flex">
                                            <span class="name">{{ 'contacto' }}:</span>
                                            <a class="info deco-none"
                                                href="tel:{{ $address['contact_person_number'] }}">
                                                {{ $address['contact_person_number'] }}</a>
                                        </div>
                                        <div class="d-flex">
                                            <span class="name">{{ 'Piso' }}:</span>
                                            <span
                                                class="info">{{ isset($address['floor']) ? $address['floor'] : '' }}</span>
                                        </div>

                                        <div class="d-flex mb-2">
                                            <span class="name">{{ 'Casa' }}:</span>
                                            <span
                                                class="info">{{ isset($address['house']) ? $address['house'] : '' }}</span>
                                        </div>
                                        <div class="d-flex">
                                            <span class="name">{{ 'Camino' }}:</span>
                                            <span
                                                class="info">{{ isset($address['road']) ? $address['road'] : '' }}</span>
                                        </div>
                                        @if (!in_array($order['order_type'], ['take_away', 'dine_in'], true) && isset($address['address']))
                                            @if (isset($address['latitude']) && isset($address['longitude']))
                                                <a target="_blank"
                                                    href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $address['latitude'] }}+{{ $address['longitude'] }}">
                                                    <i class="tio-map"></i>{{ $address['address'] }}<br>
                                                </a>
                                            @else
                                                <i class="tio-map"></i>{{ $address['address'] }}<br>
                                            @endif
                                        @endif
                                    </span>
                                @endif
                            @endif
                        </div>

                    @elseif($order->is_guest)
                        <div class="card-body">
                            <span class="badge badge-soft-success py-2 mb-2 d-block qcont">
                                {{ 'Usuario invitado' }}
                            </span>
                            @if ($order->delivery_address)
                            @php($address = json_decode($order->delivery_address, true))
                            <div class="d-flex justify-content-between align-items-center">
                                <h5>{{ 'información de entrega' }}</h5>
                            </div>
                            @if (isset($address))
                                <span class="delivery--information-single d-block">
                                    <div class="d-flex">
                                        <span class="name">{{ 'nombre' }}:</span>
                                        <span class="info">{{ $address['contact_person_name'] }}</span>
                                    </div>
                                    <div class="d-flex">
                                        <span class="name">{{ 'contacto' }}:</span>
                                        <a class="info deco-none"
                                            href="tel:{{ $address['contact_person_number'] }}">
                                            {{ $address['contact_person_number'] }}</a>
                                    </div>
                                    <div class="d-flex">
                                        <span class="name">{{ 'Piso' }}:</span>
                                        <span
                                            class="info">{{ isset($address['floor']) ? $address['floor'] : '' }}</span>
                                    </div>

                                    <div class="d-flex mb-2">
                                        <span class="name">{{ 'Casa' }}:</span>
                                        <span
                                            class="info">{{ isset($address['house']) ? $address['house'] : '' }}</span>
                                    </div>

                                    <div class="d-flex">
                                        <span class="name">{{ 'Camino' }}:</span>
                                        <span
                                            class="info">{{ isset($address['road']) ? $address['road'] : '' }}</span>
                                    </div>

                                    @if (!in_array($order['order_type'], ['take_away', 'dine_in'], true) && isset($address['address']))
                                    <hr>
                                        @if (isset($address['latitude']) && isset($address['longitude']))
                                            <a target="_blank"
                                                href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $address['latitude'] }}+{{ $address['longitude'] }}">
                                                <i class="tio-map"></i>{{ $address['address'] }}<br>
                                            </a>
                                        @else
                                            <i class="tio-map"></i>{{ $address['address'] }}<br>
                                        @endif
                                    @endif
                                </span>
                            @endif
                        @endif

                        </div>
                    @else
                        <div class="card-body">
                            <span class="badge badge-soft-danger py-2 d-block qcont">
                                {{ 'Cliente no encontrado!' }}
                            </span>
                        </div>
                    @endif
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
        </div>
        <!-- End Row -->
    </div>



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

                <form action="{{ route('vendor.order.add-order-proof', [$order['id']]) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <!-- Input Group -->
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
                                                <a href="{{ route('vendor.order.remove-proof-image', ['id' => $order['id'], 'name' => $photo]) }}"
                                                    class="spartan_remove_row"><i class="tio-add-to-trash"></i></a>
                                            </div>
                                        @endforeach
                                @endif
                            </div>
                        </div>
                        <!-- End Input Group -->
                        <div class="text-right mt-2">
                            <button class="btn btn--primary">{{ 'entregar' }}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- End Modal -->

    <div class="modal fade" id="edit-order-amount" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ 'actualizar el monto del pedido' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('vendor.order.update-order-amount') }}" method="POST" class="row">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div class="form-group col-12">
                            <label for="order_amount">{{ 'monto del pedido' }}</label>
                            <input id="order_amount" type="number" class="form-control" name="order_amount" min="0"
                                value="{{ round($order['order_amount'] - $order['total_tax_amount']  - $order['additional_charge'] -  $order['delivery_charge'] + $order['store_discount_amount'] - $order['dm_tips'] ,6) }}" step=".01">
                        </div>

                        <div class="form-group col-sm-12">
                            <button class="btn btn-sm btn-primary"
                                type="submit">{{ 'entregar' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="edit-discount-amount" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ 'actualizar importe de descuento' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('vendor.order.update-discount-amount') }}" method="POST" class="row">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div class="form-group col-12">
                            <label for="discount_amount">{{ 'monto de descuento' }}</label>
                            <input type="number" id="discount_amount" class="form-control" name="discount_amount" min="0"
                                value="{{ $order['store_discount_amount'] }}" step=".01">
                        </div>

                        <div class="form-group col-sm-12">
                            <button class="btn btn-sm btn-primary"
                                type="submit">{{ 'entregar' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- End Content -->


@endsection
@push('script_2')
    <script src="{{ asset('assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script type="text/javascript">
        "use strict";


        $('.self-delivery-warning').on('click',function (event ){
            event.preventDefault();
            toastr.info(
                "{{ 'La autoentrega está deshabilitada' }}", {
                    CloseButton: true,
                    ProgressBar: true
                });
        });



        $('.cancelled-status').on('click',function (){
            Swal.fire({
                title: '{{ '¿está seguro?' }}',
                text: '{{ '¿Cambiar estado a cancelado?' }}',
                type: 'warning',
                html:
                    `   <select class="form-control js-select2-custom mx-1" name="reason" id="reason">
                    @foreach ($reasons as $r)
                    <option value="{{ $r->reason }}">
                            {{ $r->reason }}
                    </option>
                    @endforeach

                    </select>`,
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ 'No' }}',
                confirmButtonText: '{{ 'Sí' }}',
                reverseButtons: true,
                onOpen: function () {
                    $('.js-select2-custom').select2({
                        minimumResultsForSearch: 5,
                        width: '100%',
                        placeholder: "Select Reason",
                        language: "en",
                    });
                }
            }).then((result) => {
                if (result.value) {
                    let reason = document.getElementById('reason').value;
                    location.href = '{!! route('vendor.order.status', ['id' => $order['id'],'order_status' => 'canceled']) !!}&reason='+reason,'{{ '¿Cambiar estado a cancelado?' }}';
                }
            })

        });

        $('.order-status-change-alert').on('click',function (){
            let route = $(this).data('url');
            let message = $(this).data('message');
            let verification = $(this).data('verification');
            let processing = $(this).data('processing-time') ?? false;

            if (verification) {
                Swal.fire({
                    title: '{{ 'Ingrese el código de verificación del pedido' }}',
                    input: 'text',
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    cancelButtonColor: 'default',
                    confirmButtonColor: '#FC6A57',
                    confirmButtonText: '{{ 'entregar' }}',
                    showLoaderOnConfirm: true,
                    preConfirm: (otp) => {
                        location.href = route + '&otp=' + otp;
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                })
            } else if (processing) {
                Swal.fire({
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

        });

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
                onExtensionErr: function() {
                    toastr.error(
                        "{{ 'Por favor ingrese solo archivos tipo png o jpg' }}", {
                            CloseButton: true,
                            ProgressBar: true
                        });
                },
                onSizeErr: function() {
                    toastr.error("{{ 'tamaño de archivo demasiado grande' }}", {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });
    </script>
@endpush
