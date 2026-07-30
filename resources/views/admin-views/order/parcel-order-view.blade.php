@extends('layouts.admin.app')

@section('title', 'Detalles del pedido')


@section('content')

<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">
                    <span class="page-header-icon">
                        <img src="{{ asset('assets/admin/img/shopping-basket.png') }}" class="w--20" alt="">
                    </span>
                    <span>
                        {{ 'Detalles del paquete' }}
                    </span>

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


                            </h1>
                            <span class="mt-2 d-block d-flex align-items-center __gap-5px">
                                <i class="tio-date-range"></i>
                                {{ date('d M Y ' . config('timeformat'), strtotime($order['created_at'])) }}
                            </span>

                            @if ($order->schedule_at && $order->scheduled)
                                <h6 class="text-capitalize d-flex align-items-center __gap-5px">
                                    <span>{{ 'programado en' }}</span>
                                    <span>:</span> <label
                                        class="fz--10 badge badge-soft-warning">{{ date('d M Y ' . config('timeformat'), strtotime($order['schedule_at'])) }}</label>
                                </h6>
                            @endif
                            @if ($order->parcel_pickup_at)
                                <h6 class="text-capitalize d-flex align-items-center __gap-5px">
                                    <span>{{ 'recogida en' }}</span>
                                    <span>:</span> <label
                                        class="fz--10 badge badge-soft-info">{{ $order->parcel_pickup_at }}</label>
                                </h6>
                            @endif
                            @if ($order->parcel_delivery_at)
                                <h6 class="text-capitalize d-flex align-items-center __gap-5px">
                                    <span>{{ 'entrega en' }}</span>
                                    <span>:</span> <label
                                        class="fz--10 badge badge-soft-info">{{ $order->parcel_delivery_at }}</label>
                                </h6>
                            @endif
                            @if ($order->coupon)
                                <h6 class="text-capitalize d-flex align-items-center __gap-5px">
                                    <span>{{ 'cupón' }}</span>
                                    <span>:</span> <label class="fz--10 badge badge-soft-primary">{{ $order->coupon_code }}
                                        ({{ translate('messages.' . $order->coupon->coupon_type) }})</label>
                                </h6>
                            @endif
                            <div class="hs-unfold mt-1">
                                <h5>
                                    <button
                                        class="btn py-1 px-2 order--details-btn-sm btn--primary btn-outline-primary btn--sm font-regular d-flex align-items-center __gap-5px"
                                        data-toggle="modal" data-target="#locationModal"><i class="tio-poi"></i>
                                        {{ 'mostrar ubicaciones en el mapa' }}</button>
                                </h5>
                            </div>
                            @if ($order['delivery_instruction'])
                                <div class="__bg-FAFAFA fs-12 rounded p-10px mt-2 mb-3">
                                    <strong class="text-title">{{ 'instrucción de entrega' }}
                                        :</strong> {{ $order['delivery_instruction'] }}
                                </div>
                                <!-- New Note -->
                            @endif

                            <!-- New Note -->
                            @if (
                                    $order->parcelCancellation?->return_fee > 0 &&
                                    !in_array($order->parcelCancellation?->cancel_by, ['deliveryman', 'admin_for_deliveryman'])
                                )
                                <div
                                    class="bg-danger-5 p-10px rounded d-flex align-items-center justify-content-between gap-1 mt-3">
                                    <span
                                        class="text-title text-capitalize fs-12">{{ 'El cliente pagará el paquete y la tarifa de devolución.' }}</span>
                                    <h4 class="m-0 text-title text-nowrap">
                                        {{ \App\CentralLogics\Helpers::format_currency($order->parcelCancellation?->return_fee + $order->order_amount) }}
                                    </h4>
                                </div>
                            @endif
                            <!-- New Note End -->

                            @if ($order['unavailable_item_note'])
                                <h6 class="w-100 badge-soft-warning mt-3 p-1 rounded">
                                    <span class="text-dark">
                                        {{ 'nota de artículo no disponible del pedido' }} :
                                    </span>
                                    {{ $order['unavailable_item_note'] }}
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
                            <a class="btn btn--primary print--btn font-regular d-flex align-items-center __gap-5px"
                                href={{ route('admin.order.generate-invoice', [$order['id']]) }}>
                                <i class="tio-print mr-sm-1"></i>
                                <span>{{ 'imprimir factura' }}</span>
                            </a>
                        </div>
                    </div>
                    <div class="order-invoice-right mt-3 mt-sm-0">
                        <div class="btn--container flex-wrap ml-auto align-items-end justify-content-end">


                            <a class="btn btn--primary print--btn font-regular py-2 px-3 d-none d-sm-block" href={{ route('admin.order.generate-invoice', [$order['id']]) }}>
                                <i class="tio-print mr-sm-1"></i>
                                <span>{{ 'imprimir factura' }}</span>
                            </a>
                        </div>
                        <div class="text-right mt-3 order-invoice-right-contents text-capitalize">
                            <h6>
                                <span>{{ 'estado' }}</span> <span>:</span>
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
                                        {{ translate(str_replace('_', ' ', $order['order_status'])) }}
                                    </span>
                                @endif
                            </h6>
                            <h6 class="text-capitalize">
                                <span>{{ 'método de pago' }}</span> <span>:</span>
                                <span>{{ translate(str_replace('_', ' ', $order['payment_method'])) }}</span>
                            </h6>

                            <!-- offline_payment -->
                            @if ($order?->offline_payments)
                                <span>{{ 'Verificación de pago' }}</span> <span>:</span>
                                @if ($order?->offline_payments->status == 'pending')
                                    <span class="badge badge-soft-info ml-2 ml-sm-3 text-capitalize">
                                        {{ 'Pendiente' }}
                                    </span>
                                @elseif ($order?->offline_payments->status == 'verified')
                                    <span class="badge badge-soft-success ml-2 ml-sm-3 text-capitalize">
                                        {{ 'verificado' }}
                                    </span>
                                @elseif ($order?->offline_payments->status == 'denied')
                                    <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                                        {{ 'denegado' }}
                                    </span>
                                @endif

                                @foreach (json_decode($order->offline_payments->payment_info) as $key => $item)
                                    @if ($key != 'method_id')
                                        <h6 class="">
                                            <div class="d-flex justify-content-sm-end text-capitalize">
                                                <span class="title-color">{{ translate($key) }} :</span>
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
                                    class="fz--10 badge badge-soft-primary m-0">{{ translate(str_replace('_', ' ', $order['order_type'])) }}</label>
                            </h6>
                            <h6 class="text-capitalize">
                                <span>{{ 'Pagado por' }}</span>
                                <span>:</span> <label
                                    class="fz--10 badge badge-soft-secondary m-0">{{ translate($order->charge_payer) }}</label>
                            </h6>
                            <h6>
                                <span>{{ 'estado de pago' }}</span> <span>:</span>
                                @if ($order['payment_status'] == 'paid')
                                    <span class="badge badge-soft-success ml-sm-3">
                                        {{ 'pagado' }}
                                    </span>
                                @elseif ($order['payment_status'] == 'partially_paid')
                                    @if ($order->payments()->where('payment_status', 'unpaid')->exists())
                                        <strong class="text-danger">{{ 'parcialmente pagado' }}</strong>
                                    @else
                                        <strong class="text-success">{{ 'pagado' }}</strong>
                                    @endif
                                @else
                                    <strong class="text-danger">{{ 'no pagado' }}</strong>
                                @endif

                            </h6>
                        </div>
                    </div>
                </div>
                <!-- End Header -->

                <!-- Body -->
                <div class="card-body px-0">

                    <div class="mx-3">
                        <div class="media align-items-center cart--media pb-2">
                            <div class="avatar avatar-xl mr-3"
                                title="{{ $order->parcel_category ? $order->parcel_category->name : 'categoría de paquete no encontrada' }}">
                                <img class="img-fluid onerror-image"
                                    src="{{ $order->parcel_category?->image_full_url ?? asset('assets/admin/img/160x160/img2.jpg') }}"
                                    data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}">
                            </div>
                            <div class="media-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <strong>
                                            {{ Str::limit($order->parcel_category ? $order->parcel_category->name : 'categoría de paquete no encontrada', 25, '...') }}</strong><br>
                                        <div class="font-size-sm text-body">
                                            <span>{{ $order->parcel_category ? $order->parcel_category->description : 'categoría de paquete no encontrada' }}</span>
                                        </div>
                                    </div>

                                    <div class="col col-md-2 align-self-center">
                                        <h6>{{ 'distancia' }}</h6>
                                        <span>{{ $order->distance }} {{ 'kilómetros' }}</span>
                                    </div>
                                    <div class="col col-md-1 align-self-center">

                                    </div>

                                    <div class="col col-md-3 align-self-center text-right">
                                        <h6>{{ 'cargo de entrega' }}</h6>
                                        <span>{{ \App\CentralLogics\Helpers::format_currency($order['delivery_charge']) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-2">
                    </div>



                    <div class="row justify-content-md-end mb-3 mt-4 mx-0">
                        <div class="col-12">
                            <dl class="row text-right px-3">

                                @if (($order->tax_status == 'excluded' && $order['total_tax_amount'] > 0) || $order->tax_status == null)
                                    <dt class="col-6 col-sm-8 p-0 font-regular">{{ 'iva/impuesto' }}:
                                    </dt>
                                    <dd class="col-6 col-sm-4 p-0 text-right">
                                        +
                                        {{ \App\CentralLogics\Helpers::format_currency($order['total_tax_amount']) }}

                                    </dd>
                                @endif

                                <dt class="col-6 col-sm-8 p-0 font-regular">
                                    {{ 'consejos de repartidor' }}
                                </dt>
                                <dd class="col-6 col-sm-4 p-0">
                                    + {{ \App\CentralLogics\Helpers::format_currency($order['dm_tips']) }}</dd>
                                <dt class="col-6 col-sm-8 p-0 font-regular text-truncate">
                                    {{ \App\CentralLogics\Helpers::get_business_data('additional_charge_name') ?? (\App\CentralLogics\Helpers::get_business_data('additional_charge_name') ?? 'cargo adicional') }}
                                </dt>

                                <dd class="col-6 col-sm-4 p-0">
                                    + {{ \App\CentralLogics\Helpers::format_currency($order['additional_charge']) }}
                                </dd>

                                @if(($order['parcel_item_estimated_price'] ?? 0) > 0)
                                    <dt class="col-6 col-sm-8 p-0 font-regular text-truncate">
                                        {{ 'precio estimado del artículo' ?? 'Precio Estimado del Artículo' }}:
                                    </dt>
                                    <dd class="col-6 col-sm-4 p-0">
                                        + {{ \App\CentralLogics\Helpers::format_currency($order['parcel_item_estimated_price']) }}
                                    </dd>
                                @endif

                                @if(($order['parcel_declared_value'] ?? 0) > 0)
                                    <dt class="col-6 col-sm-8 p-0 font-regular text-truncate">
                                        {{ 'valor declarado del paquete' ?? 'Valor Declarado' }}:
                                    </dt>
                                    <dd class="col-6 col-sm-4 p-0">
                                        {{ \App\CentralLogics\Helpers::format_currency($order['parcel_declared_value']) }}
                                    </dd>
                                @endif

                                @if(($order['parcel_insurance_fee'] ?? 0) > 0)
                                    <dt class="col-6 col-sm-8 p-0 font-regular text-truncate">
                                        {{ 'tarifa de seguro de paquetería' ?? 'Seguro del Paquete' }}:
                                    </dt>
                                    <dd class="col-6 col-sm-4 p-0">
                                        + {{ \App\CentralLogics\Helpers::format_currency($order['parcel_insurance_fee']) }}
                                    </dd>
                                @endif

                                <dt class="col-12"><hr class="my-2"></dt>

                                <dt class="col-6 col-sm-8 p-0 fs-16">
                                    <div class="d-flex align-items-center gap-2 justify-content-end">
                                        {{ 'total' }}
                                        {{ $order->tax_status == 'included' ? '(' . 'IVA incluido' . ')' : '' }}

                                        @if (in_array($order->parcelCancellation?->cancel_by, ['deliveryman', 'admin_for_deliveryman']))
                                            <span class="form-label-secondary text-danger d-flex" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ 'No se cobrará ninguna tarifa de envío si el repartidor cancela el pedido.' }}"><img
                                                    src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                    alt="Veg/non-veg toggle"> </span>
                                        @endif

                                        </span>
                                        @if ($order->parcelCancellation?->return_fee > 0 && $order->charge_payer != 'receiver')
                                            @if ($order->payment_method != 'cash_on_delivery')
                                                @if ($order->payment_status == 'paid')
                                                    <span class="badge border-0 fs-10 badge-soft-success">
                                                        {{ 'Pagado' }}
                                                    </span>
                                                @else
                                                    <span class="badge border-0 fs-10 badge-soft-danger">
                                                        {{ 'Pendiente' }}
                                                    </span>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </dt>
                                <dd class="col-6 col-sm-4 p-0 font-semibold text-title">
                                    {{ \App\CentralLogics\Helpers::format_currency($order['delivery_charge'] + $order['total_tax_amount'] + $order['dm_tips'] + $order['additional_charge'] - $order['coupon_discount_amount'] - $order['ref_bonus_amount'] + ($order['parcel_insurance_fee'] ?? 0) + ($order['parcel_item_estimated_price'] ?? 0)) }}
                                </dd>
                                @if ($order->parcelCancellation?->return_fee > 0)

                                    <dt class="col-6 col-sm-8 p-0 fs-16">
                                        <div
                                            class="d-flex fs-12 font-regular color-222324CC align-items-center gap-2 justify-content-end">
                                            {{ 'tarifa de devolución' }}
                                            @if ($order?->parcelCancellation?->return_fee_payment_status == 'paid')
                                                <span class="badge border-0 fs-10 badge-soft-success">
                                                    {{ 'Pagado' }}
                                                </span>
                                            @else
                                                <span class="badge border-0 fs-10 badge-soft-danger">
                                                    {{ 'Pendiente' }}
                                                </span>
                                            @endif
                                        </div>
                                        @if (
                                                $order->parcelCancellation?->return_fee > 0 &&
                                                !in_array($order->parcelCancellation?->cancel_by, ['deliveryman', 'admin_for_deliveryman'])
                                            )
                                            <hr>
                                        @endif
                                    </dt>
                                    <dd class="col-6 col-sm-4 p-0">
                                         <div class="fs-14 text-title">
                                             {{ \App\CentralLogics\Helpers::format_currency($order?->parcelCancellation?->return_fee) }}
                                         </div>
                                         @if (
                                                 $order->parcelCancellation?->return_fee > 0 &&
                                                 !in_array($order->parcelCancellation?->cancel_by, ['deliveryman', 'admin_for_deliveryman'])
                                             )
                                             <hr>
                                         @endif
                                     </dd>
                                 @endif

                                 @if (
                                         $order->parcelCancellation?->return_fee > 0 &&
                                         !in_array($order->parcelCancellation?->cancel_by, ['deliveryman', 'admin_for_deliveryman'])
                                     )
                                     <dt class="col-6 col-sm-8 p-0 fs-16">
                                         <div
                                             class="d-flex fs-16 font-semibold font-regular text-title align-items-center gap-2 justify-content-end">
                                             {{ 'Subtotal' }}

                                             @if ($order?->parcelCancellation?->return_fee_payment_status == 'paid')
                                                 <span class="badge border-0 fs-10 badge-soft-success">
                                                     {{ 'Pagado' }}
                                                 </span>
                                             @else
                                                 <span class="badge border-0 fs-10 badge-soft-danger">
                                                     {{ 'Pendiente' }}
                                                 </span>
                                             @endif


                                         </div>
                                     </dt>
                                     <dd class="col-6 col-sm-4 p-0">
                                         <div class="fs-16 text-title font-semibold">
                                             {{ \App\CentralLogics\Helpers::format_currency($order['delivery_charge'] + $order['total_tax_amount'] + $order['dm_tips'] + $order['additional_charge'] - $order['coupon_discount_amount'] - $order['ref_bonus_amount'] + ($order['parcel_insurance_fee'] ?? 0) + ($order['parcel_item_estimated_price'] ?? 0) + ($order?->parcelCancellation?->return_fee ?? 0)) }}
                                         </div>
                                     </dd>
                                 @endif





                                @if ($order?->payments)
                                    @foreach ($order?->payments as $payment)
                                        @if ($payment->payment_status == 'paid')
                                            @if ($payment->payment_method == 'cash_on_delivery')
                                                <dt class="col-6 col-sm-8 p-0 font-regular">
                                                    {{ 'Pagado en efectivo' }}
                                                    ({{ 'BACALAO' }})
                                                    :
                                                </dt>
                                            @else
                                                <dt class="col-6 col-sm-8 p-0 font-regular">
                                                    {{ 'Pagado por' }}
                                                    {{ translate($payment->payment_method) }} :
                                                </dt>
                                            @endif
                                        @else
                                            <dt class="col-6 col-sm-8 p-0 font-regular">{{ 'Monto adeudado' }}
                                                ({{ $payment->payment_method == 'cash_on_delivery' ? 'BACALAO' : translate($payment->payment_method) }})
                                                :</dt>
                                        @endif
                                        <dd class="col-6 col-sm-4 p-0 text-right">
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

        <div class="col-lg-4 order-print-area-right">

            @if (
                !in_array($order['order_status'], [
                    'refund_requested',
                    'refunded',
                    'refund_request_canceled',
                    'delivered',
                    'canceled',

                    'returned',
                ])
            )

            <div class="card mb-2">
                <div class="card-body">



                    <h5 class="card-title mb-10px text-start fw-medium fs-12 d-flex align-items-center gap-1">
                        <img class="svg" src="{{ asset('assets/admin/img/icons/shop-bag.svg') }}"
                            alt="{{ 'imagen' }}">
                        {{ 'Estado del paquete' }}

                        @if ($order?->parcelCancellation?->is_refunded == 1)
                            <span class='ml-2 badge badge-soft-primary'>
                                {{ 'Reintegrado' }}
                            </span>
                        @endif

                    </h5>
                    @if ($order?->offline_payments?->status == 'denied' && $order?->offline_payments?->note)
                        <div class="mb-15 text-left rounded badge badge-soft-danger py-2 px-3">
                            <h2 class="fs-12 text-danger font-weight-semibold mb-1">
                                {{ '# Nota denegada:' }}
                            </h2>
                            <p class="fs-12 mb-0 text-body text-break font-weight-medium">
                                {{ $order?->offline_payments?->note }}
                            </p>
                        </div>
                    @endif




                    @if ($order->is_unpaid_order)
                    <div class="text-center bg-light2 rounded p-xxl-20 p-3">
                        <h4 class="text-danger fs-14px fw-medium mb-2">
                            {{ '¡El pago falló!' }}
                        </h4>
                        @php($isCashOnDelivery = App\CentralLogics\Helpers::get_business_settings('cash_on_delivery')['status'] ?? false)
                        @php($isZoneCashOnDelivery = $order?->zone->cash_on_delivery)
                                @if ($isCashOnDelivery && $isZoneCashOnDelivery)
                                    <p class="fs-12 text-dark mb-20">
                                        {{ 'No se pudo procesar el pago del cliente. Por favor cambie a COD.' }}
                                    </p>
                                @endif
                                <div class="btn--container justify-content-center">
                                    @if ($isCashOnDelivery && $isZoneCashOnDelivery)
                                        <button type="button" class="btn btn--primary btn-sm form-alert"
                                            data-id="order-{{ $order['id'] }}" data-cancel-btn="{{ 'Cancelar' }}"
                                            data-confirm-btn="{{ 'Confirmar' }}"
                                            data-image-url="{{ asset('assets/admin/img/tughrik.png') }}"
                                            data-title="{{ '¿Cambiar a pago contra reembolso?' }}"
                                            data-message="{{ 'El pago digital del cliente ha fallado. Antes de cambiar este pedido a Pago contra reembolso (COD), confirme el problema de pago con el cliente para evitar malentendidos.' }}">
                                            {{ 'Cambiar a COD' }}</button>
                                        <form action="{{ route('admin.order.switch_to_cod', [$order['id']]) }}" method="post"
                                            id="order-{{ $order['id'] }}">
                                            @csrf
                                        </form>
                                    @endif

                                    <button type="button"
                                        class="btn btn-outline-secondary  trigger-reason offcanvas-trigger {{ $order['order_status'] == 'canceled' ? 'disabled' : '' }} {{ $order['order_status'] == 'canceled' ? 'active' : '' }}"
                                        data-target="#percel-cancellation_offcanvas">{{ 'Cancelar pedido' }}</button>

                                </div>

                            </div>
                        @else
                    @if ($order?->payment_method == 'offline_payment' && !in_array($order->order_status, ['canceled']))
                            <div class="bg-light2 rounded p-xxl-20 p-3">
                                <div class="card-body p-0 text-center">
                                    <h2 class="fs-14 fw-medium mb-3">
                                        {{ $order?->offline_payments?->status == 'verified' ? 'Pago verificado' : 'Verificación de pago' }}
                                    </h2>

                                    @if ($order?->offline_payments?->status == 'pending')
                                            <p class="text-danger fs-12 mb-20">
                                                {{ 'Verifique el pago antes de confirmar el pedido.' }}
                                            </p>
                                            <div class="btn--container justify-content-center">
                                                <button type="button" class="btn btn--primary btn-sm" data-toggle="modal"
                                                    data-target="#verifyViewModal">{{ 'Verificar pago' }}</button>





                                                <button type="button"
                                                    class="btn btn-outline-secondary  trigger-reason offcanvas-trigger {{ $order['order_status'] == 'canceled' ? 'disabled' : '' }} {{ $order['order_status'] == 'canceled' ? 'active' : '' }}"
                                                    data-target="#percel-cancellation_offcanvas">{{ 'Cancelar pedido' }}</button>


                                            </div>
                                            {{--
                                        </div> --}}
                                    @elseif($order?->offline_payments?->status == 'verified')
                                    <div class="btn--container justify-content-center">
                                        <button type="button" class="btn btn--primary btn-sm" data-toggle="modal"
                                            data-target="#verifyViewModal">{{ 'Detalles de pago' }}</button>
                                    </div>
                                @elseif($order?->offline_payments?->status == 'denied')
                                    <div class="btn--container justify-content-center">
                                        <button type="button" class="btn btn--primary btn-sm" data-toggle="modal"
                                            data-target="#verifyViewModal">{{ 'Vuelva a verificar la verificación' }}</button>
                                        <button type="button"
                                            class="btn btn-outline-secondary  trigger-reason offcanvas-trigger {{ $order['order_status'] == 'canceled' ? 'disabled' : '' }} {{ $order['order_status'] == 'canceled' ? 'active' : '' }}"
                                            data-target="#percel-cancellation_offcanvas">{{ 'Cancelar pedido' }}</button>

                                    </div>
                                @elseif(!$order?->offline_payments)
                                    <p class="text-danger fs-12 mb-20">
                                        {{ 'Verifique el pago antes de confirmar el pedido.' }}
                                    </p>
                                    <div class="btn--container justify-content-center">
                                        <button type="button" class="btn btn--primary btn-sm" data-toggle="modal"
                                            data-target="#verifyViewModal">{{ 'Verificar pago' }}</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                @if (
                        $order->payment_method != 'offline_payment' ||
                        ($order?->offline_payments && $order?->offline_payments?->status == 'verified')
                    )
                    @if (
                            !in_array($order['order_status'], [
                                'refund_requested',
                                'refunded',
                                'refund_request_canceled',
                                'delivered',
                                'canceled',
                                'failed',
                                'returned',
                            ])
                        )

                        <div class="hs-unfold w-100">
                            <div class="dropdown">
                                <button @disabled(
                                    in_array($order['order_status'], [
                                        'refund_requested',
                                        'refunded',
                                        'refund_request_canceled',
                                        'delivered',
                                        'returned',
                                    ])
                                ) {{ $order['order_status'] == 'canceled' && $order?->parcelCancellation && $order->parcelCancellation->before_pickup == 1 ? 'disabled' : '' }}
                                    class="form-control h--45px dropdown-toggle d-flex justify-content-between align-items-center w-100"
                                    type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    <?php
                        $message = match ($order['order_status']) {
                            'pending' => 'Pendiente',
                            'confirmed' => 'confirmado',
                            'accepted' => 'confirmado',
                            'processing' => 'tratamiento',
                            'handover' => 'confirmado',
                            'picked_up' => 'En Camino de Entrega',
                            'delivered' => 'Entregado',
                            'canceled' => 'Cancelado',
                            'returned' => 'regresó',
                            default => 'estado',
                        };
                                                        ?>
                                    {{ $message }}
                                </button>

                                <div class="dropdown-menu text-capitalize" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item {{ $order['order_status'] == 'pending' ? 'active' : '' }} {{ $order['order_status'] == 'canceled' ? 'disabled' : '' }} route-alert"
                                        data-url="{{ route('admin.order.status', ['id' => $order['id'], 'order_status' => 'pending']) }}"
                                        data-message="{{ '¿Cambiar estado a pendiente?' }}"
                                        href="javascript:">{{ 'Pendiente' }}</a>
                                    <a class="dropdown-item {{ in_array($order['order_status'], ['accepted', 'confirmed', 'handover']) ? 'active' : '' }} route-alert {{ $order['order_status'] == 'canceled' ? 'disabled' : '' }}"
                                        data-url="{{ route('admin.order.status', ['id' => $order['id'], 'order_status' => 'confirmed']) }}"
                                        data-message="{{ '¿Cambiar estado a confirmado?' }}"
                                        href="javascript:">{{ 'confirmado' }}</a>

                                    <a class="dropdown-item {{ $order['order_status'] == 'picked_up' ? 'active' : '' }} route-alert {{ $order['order_status'] == 'canceled' ? 'disabled' : '' }}"
                                        data-url="{{ route('admin.order.status', ['id' => $order['id'], 'order_status' => 'picked_up']) }}"
                                        data-message="{{ '¿Cambiar estado a listo para entrega?' }}"
                                        href="javascript:">{{ 'En Camino de Entrega' }}</a>
                                    <a class="dropdown-item {{ $order['order_status'] == 'delivered' ? 'active' : '' }} route-alert {{ $order['order_status'] == 'canceled' ? 'disabled' : '' }}"
                                        data-url="{{ route('admin.order.status', ['id' => $order['id'], 'order_status' => 'delivered']) }}"
                                        data-message="{{ '¿Cambiar el estado a entregado (el estado del pago se pagará si no es así)?' }}"
                                        href="javascript:">{{ 'Entregado' }}</a>
                                    <a class="dropdown-item trigger-reason offcanvas-trigger {{ $order['order_status'] == 'canceled' ? 'disabled' : '' }} {{ $order['order_status'] == 'canceled' ? 'active' : '' }}"
                                        data-target="#percel-cancellation_offcanvas">{{ 'Cancelado' }}</a>
                                    @if (
                                            $order['order_status'] == 'canceled' &&
                                            $order?->parcelCancellation &&
                                            $order->parcelCancellation->before_pickup == 0
                                        )
                                        <a class="dropdown-item route-alert"
                                            data-url="{{ route('admin.order.parcelReturn', ['id' => $order['id'], 'order_status' => 'returned']) }}"
                                            data-message="{{ '¿Devolver el paquete?' }}"
                                            href="javascript:">{{ 'paquete de devolución' }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (
                            !in_array($order['order_status'], [
                                'refund_requested',
                                'refunded',
                                'refund_request_canceled',
                                'delivered',
                                'canceled',
                                'failed',
                                'returned',
                            ]) && $order->delivery_man_id == null
                        )
                        <div class="w-100 text-center mt-3">
                            <button type="button" class="btn btn--primary w-100" data-toggle="modal" data-target="#myModal"
                                data-lat='21.03' data-lng='105.85'>
                                {{ 'asignar al repartidor manualmente' }}
                            </button>
                        </div>
                    @endif

                    @if ($order?->parcelCancellation && $order?->parcelCancellation?->is_delivery_charge_refundable == 1)
                        @if ($order?->parcelCancellation?->is_refunded == 0)
                            <div class="w-100 text-center mt-3">
                                <button type="button" class="btn btn--primary w-100" data-toggle="modal"
                                    data-target="#manually_parcel_amount_refund">
                                    {{ 'Reembolso manual al usuario' }}
                                </button>
                            </div>
                        @endif
                    @endif
                @endif

                @endif
            </div>
        </div>
        @endif

        @if ($order->parcelCancellation)

            <div class="card mb-2">
                <!-- Canceled New -->
                <div class="card-body">
                    @if ($order->parcelCancellation?->return_otp != null)
                        <div class="__bg-FAFAFA p-2 rounded d-flex align-items-center justify-content-between gap-1">
                            <span class="text-title fs-12">{{ 'Paquete devuelto OTP' }}</span>
                            <h3 class="m-0 text-title text-nowrap">{{ $order->parcelCancellation?->return_otp }}
                            </h3>
                        </div>
                    @endif
                    <ul class="delivery--information-single mt-3 ">
                        <li>
                            <span class="name">{{ 'Cancelado por' }} </span>
                            <span class="info"> {{ translate($order->canceled_by) }} </span>
                        </li>

                    </ul>
                    @if ($order->parcelCancellation?->return_fee > 0)
                        <div
                            class="bg-FF40401A p-10px text-capitalize rounded d-flex align-items-center justify-content-between gap-1 mt-3">

                            @if (
                                    $order->charge_payer == 'receiver' &&
                                    !in_array($order->parcelCancellation?->cancel_by, ['deliveryman', 'admin_for_deliveryman'])
                                )
                                <span class="text-title fs-12">{{ 'El cliente pagará tanto el paquete como la tarifa de devolución.' }}</span>
                                <h4 class="m-0 text-title text-nowrap">
                                    {{ \App\CentralLogics\Helpers::format_currency($order->parcelCancellation?->return_fee + $order->order_amount) }}
                                </h4>
                            @else
                                <span class="text-title fs-12">{{ 'El cliente pagará la tarifa de devolución.' }}</span>
                                <h4 class="m-0 text-title text-nowrap">
                                    {{ \App\CentralLogics\Helpers::format_currency($order->parcelCancellation?->return_fee) }}
                                </h4>
                            @endif

                        </div>
                    @endif
                    <div class="p-10px __bg-FAFAFA mt-3">
                        @if (is_array($order->parcelCancellation?->reason) && count($order->parcelCancellation?->reason) > 0)
                            <div class="fs-12">
                                <span class="text-title font-medium">{{ 'Cancelar motivo' }}</span> <br>
                                <ul>
                                    @foreach ($order->parcelCancellation?->reason as $reason)
                                        <li class="mr-1">{{ $reason }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if ($order->parcelCancellation?->note)
                            <div class="fs-12 mt-3">
                                <span class="text-title font-medium">{{ 'Comentario' }}</span> <br>
                                <p class="ml-2"> {{ $order->parcelCancellation?->note }} </p>
                            </div>
                        @endif
                    </div>
                    @if ($order->parcelCancellation?->before_pickup === 0)
                        <div class="mt-3 d-flex gap-2 text-title mt-3">
                            <i class="tio-calendar-month mt-1"></i>
                            <div class="fs-12 text-title">
                                {{ 'Fecha y hora estimadas de regreso:' }} <span>
                                    {{ $order->parcelCancellation?->set_return_date == 0 ? 'Aún no configurado' : \App\CentralLogics\Helpers::time_date_format($order->parcelCancellation?->return_date) }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        @if ($order->delivery_man)
        <div class="card mt-2">
            <div class="card-body">
                <h5 class="card-title mb-10px d-flex flex-wrap align-items-center">
                    <span class="card-header-icon">
                        <i class="tio-user"></i>
                    </span>
                    <span>{{ 'Repartidor' }}</span>
                    @if (
                            !in_array($order['order_status'], [
                                'refund_requested',
                                'refunded',
                                'refund_request_canceled',
                                'delivered',
                                'canceled',
                                'returned',
                            ])
                        )
                        <a type="button" href="#myModal" class="text--base fs-12 font-midium cursor-pointer ml-auto"
                            data-toggle="modal" data-target="#myModal">
                            {{ 'cambiar' }}
                        </a>
                    @endif
                </h5>
                <a class="media align-items-center deco-none customer--information-single __bg-FAFAFA rounded p-10px mb-10px"
                    href="{{ !$order?->store?->sub_self_delivery ? route('admin.users.delivery-man.preview', [$order->delivery_man['id']]) : '#' }}">
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
                @php($address = $order->dm_last_location)
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-1 font-regular">{{ 'última ubicación' }}</h5>
                </div>
                @if (isset($address))
                    <span class="d-block">
                        <a target="_blank" class="base--clr fs-12"
                            href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $address['latitude'] }}+{{ $address['longitude'] }}">
                            <i class="tio-map color-222324CC"></i> {{ $address['location'] }}<br>
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
                    <h5 class="card-title mb-10px">
                        <span class="card-header-icon">
                            <i class="tio-user"></i>
                        </span>
                        <span>{{ 'información del cliente' }}</span>
                    </h5>

                    <a class="media align-items-center deco-none customer--information-single __bg-FAFAFA rounded p-10px mb-10px"
                        href="{{ route('admin.users.customer.view', [$order->customer['id']]) }}">
                        <div class="avatar avatar-circle">
                            <img class="avatar-img onerror-image"
                                data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                                src="{{ $order->customer->image_full_url }}" alt="Image Description">
                        </div>
                        <div class="media-body">
                            <span class="fz--14px text--title font-semibold text-hover-primary d-block">
                                {{ $order->customer['f_name'] . ' ' . $order->customer['l_name'] }}
                            </span>
                            <span>{{ $order->customer->orders_count }} {{ 'Pedidos' }}</span>
                            <span class="text--title font-semibold d-flex align-items-center">
                                <i class="tio-call-talking-quiet mr-2"></i>
                                <span>{{ $order->customer['phone'] }}</span>
                            </span>
                            <span class="text--title d-flex align-items-center">
                                <i class="tio-email mr-2"></i> <span>{{ $order->customer['email'] }}</span>
                            </span>
                        </div>
                    </a>
                @elseif($order->is_guest)
                    <span class="badge badge-soft-success py-2 d-block qcont mb-3">
                        {{ 'Usuario invitado' }}
                    </span>
                @else
                    <span class="badge badge-soft-danger py-2 d-block qcont">
                        {{ 'Cliente no encontrado!' }}
                    </span>
                @endif


            </div>
        </div>




        <!-- Dlivery Info Card -->
        <div class="card mb-2 mt-2">
            <div class="card-body">
                @if ($order->delivery_address)
                @php($address = json_decode($order->delivery_address, true))
                <div class="d-flex justify-content-between align-items-center mb-10px">
                    <h5 class="card-title">
                        <span class="card-header-icon">
                            <i class="tio-user"></i>
                        </span>
                        <span>{{ 'remitente' }}</span>
                    </h5>

                </div>
                @if (isset($address))

                    <div class="delivery--information-single __bg-FAFAFA p-10px rounded mb-10px">
                        <span class="name">{{ 'nombre' }}</span>
                        <span class="info">{{ data_get($address, 'contact_person_name', 'N / A') }}</span>
                        <span class="name">{{ 'contacto' }}</span>
                        <a class="deco-none info"
                            href="tel:{{ data_get($address, 'contact_person_number', 'N / A') }}">
                            {{ data_get($address, 'contact_person_number', 'N / A') }}</a>
                        @if (data_get($address, 'house') != '')
                            <span class="name">{{ 'Casa' }}</span> <span
                                class="info">{{ data_get($address, 'house', 'N / A') }}</span>
                        @endif
                        @if (data_get($address, 'floor') != '')
                            <span class="name">{{ 'Piso' }}</span> <span
                                class="info">{{ data_get($address, 'floor', 'N / A') }}</span>
                        @endif

                        @if (data_get($address, 'road') != '')
                            <span class="name">{{ 'Camino' }}</span> <span
                                class="info">{{ data_get($address, 'road', 'N / A') }}</span>
                        @endif

                        @if (isset($address['address']))
                            @if (data_get($address, 'latitude', null) && data_get($address, 'longitude', null))
                                <a target="_blank" class="d-flex align-items-center base--clr fs-12"
                                    href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $address['latitude'] }}+{{ $address['longitude'] }}">
                                    <i class="tio-poi color-222324CC"></i>{{ $address['address'] }}
                                </a>
                            @else
                                <i class="tio-poi color-222324CC"></i>{{ $address['address'] }}
                            @endif
                        @endif
                    </div>

                @endif
                @endif
                <!-- Polish Version-->
                @if ($order->receiver_details)
                <hr>
                @php($receiver_details = $order->receiver_details)
                <h5 class="card-title mb-10px">
                    <span class="card-header-icon">
                        <i class="tio-user"></i>
                    </span>
                    <span>{{ 'información del receptor' }}</span>
                </h5>
                @if (isset($receiver_details))
                    <span class="delivery--information-single __bg-FAFAFA p-10px mb-10px rounded">
                        <span class="name">{{ 'nombre' }}</span>
                        <span class="info">{{ $receiver_details['contact_person_name'] }}</span>
                        <span class="name">{{ 'contacto' }}</span>
                        <a class="deco-none info d-flex" href="tel:{{ $receiver_details['contact_person_number'] }}">
                            {{ $receiver_details['contact_person_number'] }}</a>
                        @if (data_get($receiver_details, 'floor') != '')
                            <span class="name">{{ 'Piso' }}</span> <span
                                class="info">{{ data_get($receiver_details, 'floor', 'N / A') }}</span>
                        @endif
                        @if (data_get($receiver_details, 'house') != '')
                            <span class="name">{{ 'Casa' }}</span> <span
                                class="info">{{ data_get($receiver_details, 'house', 'N / A') }}</span>
                        @endif

                        @if (data_get($receiver_details, 'road') != '')
                            <span class="name">{{ 'Camino' }}</span> <span
                                class="info">{{ data_get($receiver_details, 'road', 'N / A') }}</span>
                        @endif

                @endif
                    @if (isset($receiver_details['address']))
                        @if (isset($receiver_details['latitude']) && isset($receiver_details['longitude']))
                            <a class="base--clr fs-12 d-flex" target="_blank"
                                href="http://maps.google.com/maps?z=12&t=m&q=loc:{{ $receiver_details['latitude'] }}+{{ $receiver_details['longitude'] }}">
                                <i class="tio-poi color-222324CC"></i>{{ $receiver_details['address'] }}
                            </a>
                        @else
                            <i class="tio-poi color-222324CC"></i>{{ $receiver_details['address'] }}
                        @endif


                    @endif
                </span>
                @endif
            </div>
        </div>





        <!-- Customer Card -->
        @php($data = isset($order->order_proof) ? json_decode($order->order_proof, true) : [])
        @if (in_array($order->order_status, ['handover', 'delivered', 'picked_up']) || ($data != null && count($data) > 0))
        <!-- order proof -->
        <div class="card mb-2 mt-2">
            <div class="card-header border-0 mb-10px text-center pb-0">
                <h5 class="m-0 fs-14 color-222324CC">{{ 'prueba de entrega' }} </h5>
                @if (in_array($order->order_status, ['handover', 'delivered', 'picked_up']))
                    <button class="btn btn-outline-primary btn-sm px-3 py-1 fs-14" data-toggle="modal"
                        data-target=".order-proof-modal"> {{ 'agregar' }} </button>
                @endif
            </div>
            <div class="card-body pt-0">
                @if ($data)
                <div class="__bg-FAFAFA p-10px rounded">
                    <label class="input-label" for="order_proof">{{ 'imagen' }} :
                    </label>
                    <div class="row g-1">
                        @foreach ($data as $key => $img)
                        @php($img = is_array($img) ? $img : ['img' => $img, 'storage' => 'public'])
                        <div class="col-3">
                            <img class="img__aspect-1 rounded border w-100 onerror-image" data-toggle="modal"
                                data-target="#imagemodal{{ $key }}"
                                data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                src="{{ \App\CentralLogics\Helpers::get_full_url('order', $img['img'], $img['storage']) }}">
                        </div>
                        <div class="modal fade" id="imagemodal{{ $key }}" tabindex="-1" role="dialog"
                            aria-labelledby="order_proof_{{ $key }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="order_proof_{{ $key }}">
                                            {{ 'imagen de prueba de pedido' }}
                                        </h4>
                                        <button type="button" class="close" data-dismiss="modal"><span
                                                aria-hidden="true">&times;</span><span
                                                class="sr-only">{{ 'Cancelar' }}</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="{{ \App\CentralLogics\Helpers::get_full_url('order', $img['img'], $img['storage']) }}"
                                            class="initial--22 w-100">
                                    </div>
                                    @php($storage = $img['storage'] ?? 'public')
                                    @php($file = $storage == 's3' ? base64_encode('order/' . $img['img']) : base64_encode('public/order/' . $img['img']))
                                    <div class="modal-footer">
                                        <a class="btn btn-primary"
                                            href="{{ route('admin.file-manager.download', [$file, $storage]) }}"><i
                                                class="tio-download"></i>
                                            {{ 'descargar' }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        @php($receiptPhotos = $order->parcel_receipt_photos_full_url)
        @if ($receiptPhotos && count($receiptPhotos) > 0)
        <!-- receipt photos -->
        <div class="card mb-2 mt-2">
            <div class="card-header border-0 mb-10px text-center pb-0">
                <h5 class="m-0 fs-14 color-222324CC">{{ 'recibo de compra' ?? 'Comprobante de Compra' }} </h5>
            </div>
            <div class="card-body pt-0">
                <div class="__bg-FAFAFA p-10px rounded">
                    <div class="row g-1">
                        @foreach ($receiptPhotos as $key => $imgUrl)
                        <div class="col-3">
                            <img class="img__aspect-1 rounded border w-100 onerror-image" data-toggle="modal"
                                data-target="#receiptmodal{{ $key }}"
                                data-onerror-image="{{ asset('assets/admin/img/160x160/img2.jpg') }}"
                                src="{{ $imgUrl }}">
                        </div>
                        <div class="modal fade" id="receiptmodal{{ $key }}" tabindex="-1" role="dialog"
                            aria-labelledby="receipt_photo_{{ $key }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="receipt_photo_{{ $key }}">
                                            {{ 'recibo de compra' ?? 'Comprobante de Compra' }}
                                        </h4>
                                        <button type="button" class="close" data-dismiss="modal"><span
                                                aria-hidden="true">&times;</span><span
                                                class="sr-only">{{ 'Cancelar' }}</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="{{ $imgUrl }}" class="initial--22 w-100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
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
                <h5 class="modal-title" id="refund_cancelation_note_l">
                    {{ 'agregar nota de rechazo de pedido' }}
                </h5>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ 'cerca' }}</button>
                <button type="submit" class="btn btn-danger">{{ 'Confirmar rechazo de pedido' }}
                </button>
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
                <h5 class="modal-title h4" id="mySmallModalLabel">{{ 'añadir código de referencia' }}
                </h5>
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
                <h5 class="modal-title h4" id="mySmallModalLabel">{{ 'agregar comprobante de entrega' }}
                </h5>
                <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal"
                    aria-label="Close">
                    <i class="tio-clear tio-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.order.add-order-proof', [$order['id']]) }}" method="post"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="flex-grow-1 mx-auto">
                        <div class="d-flex flex-wrap __gap-12px __new-coba" id="coba">
                            @php($proof = isset($order->order_proof) ? json_decode($order->order_proof, true) : 0)
                            @if ($proof)

                            @foreach ($proof as $key => $photo)
                            @php($photo = is_array($photo) ? $photo : ['img' => $photo, 'storage' => 'public'])
                            <div class="spartan_item_wrapper min-w-176px max-w-176px">
                                <img class="img--square"
                                    src="{{ \App\CentralLogics\Helpers::get_full_url('order', $photo['img'], $photo['storage']) }}"
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
                                    value="{{ isset($address['house']) ? $address['house'] : '' }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                {{ 'Piso' }}
                            </label>
                            <div class="col-md-10 js-form-message">
                                <input type="text" class="form-control" name="floor"
                                    value="{{ isset($address['floor']) ? $address['floor'] : '' }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                {{ 'Camino' }}
                            </label>
                            <div class="col-md-10 js-form-message">
                                <input type="text" class="form-control" name="road"
                                    value="{{ isset($address['road']) ? $address['road'] : '' }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
                                {{ 'DIRECCIÓN' }}
                            </label>
                            <div class="col-md-10 js-form-message">
                                <input type="text" class="form-control" name="address" value="{{ $address['address'] }}">
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
                                            src="{{ $dm['image_full_url'] }}" alt="{{ $dm['name'] }}">
                                        {{ $dm['name'] }}
                                    </span>

                                    <a class="btn btn-primary btn-xs float-right add-delivery-man"
                                        data-id="{{ $dm['id'] }}">{{ 'asignar' }}</a>
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

                        @if (optional($order->offline_payments)->status === 'verified')
                            <span class="badge badge-soft-success mt-3 mb-3">
                                {{ 'verificado' }}
                            </span>
                        @endif
                    </h2>

                    @unless (optional($order->offline_payments)->status === 'verified')
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
                                    @if ($order->is_guest)
                                    @php($customer_details = json_decode($order['delivery_address'], true))

                                        <div class="d-flex align-items-center gap-2">
                                            <span class="customer-namekey">{{ 'Nombre' }}</span>:
                                            <span class="text-dark">
                                                {{ $customer_details['contact_person_name'] }}</span>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <span class="customer-namekey">{{ 'Teléfono' }}</span>:
                                            <span class="text-dark">
                                                {{ $customer_details['contact_person_number'] }}</span>
                                        </div>
                                    @elseif($order->customer)
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="customer-namekey">{{ 'Nombre' }}</span>:
                                            <span class="text-dark"> <a class="text-dark text text-capitalize"
                                                    href="{{ route('admin.customer.view', [$order['user_id']]) }}">
                                                    {{ $order->customer['f_name'] . ' ' . $order->customer['l_name'] }}
                                                </a> </span>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <span class="customer-namekey">{{ 'Teléfono' }}</span>:
                                            <span class="text-dark">{{ $order->customer['phone'] }} </span>
                                        </div>
                                    @else
                                    <label
                                        class="badge badge-danger">{{ 'datos de cliente no válidos' }}</label>
                                    @endif

                                </div>
                            </div>
                            @if ($order?->offline_payments)
                                <div class="bg-white p-3 rounded h-100 w-100">
                                    <div class="">
                                        <h4 class="mb-3 fs-16">{{ 'Información de pago' }}
                                        </h4>
                                        <div class="row g-1">
                                            @foreach (json_decode($order?->offline_payments?->payment_info ?? '[]') as $key => $item)
                                                @if ($key != 'method_id')
                                                    <div class="col-sm-12">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="namekey"> {{ translate($key) }}</span>:
                                                            <span class="text-dark text-break">{{ $item }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        <div class="d-flex flex-column gap-2 mt-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="namekey">{{ 'Nota al cliente' }}</span>:
                                                <span
                                                    class="text-dark text-break">{{ $order->offline_payments?->customer_note ?? 'N / A' }}
                                                </span>
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
                                                <span class="namekey"> {{ 'Método de pago' }}</span>:
                                                <span class="text-dark text-break">{{ 'N / A' }}
                                                </span>
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
                            <button type="button" class="btn btn--reset offline_payment_cancelation_note" data-toggle="modal"
                                data-target="#offline_payment_cancelation_note" data-id="{{ $order['id'] }}"
                                class="btn btn--reset">{{ 'El pago no se recibió' }}</button>
                        @elseif ($order?->offline_payments?->status == 'denied')
                            <button type="button"
                                data-url="{{ route('admin.order.offline_payment', ['id' => $order['id'], 'verify' => 'switched_to_cod']) }}"
                                data-message="{{ 'Realizar el pago cambiado a bacalao para este pedido' }}"
                                class="btn btn--reset route-alert">{{ 'Cambiado a COD' }}</button>
                        @endif
                        @if ($order?->offline_payments)
                            <button type="button"
                                data-url="{{ route('admin.order.offline_payment', ['id' => $order['id'], 'verify' => 'yes']) }}"
                                data-message="{{ 'Realizar el pago verificado para este pedido' }}"
                                class="btn btn--primary route-alert">{{ 'Sí, pago recibido' }}</button>
                        @else
                            <button type="button" class="btn btn--primary btn-sm form-alert" data-id="order-{{ $order['id'] }}"
                                data-cancel-btn="{{ 'Cancelar' }}"
                                data-confirm-btn="{{ 'Confirmar' }}"
                                data-image-url="{{ asset('assets/admin/img/tughrik.png') }}"
                                data-title="{{ '¿Cambiar a pago contra reembolso?' }}"
                                data-message="{{ 'El pago fuera de línea del cliente falló. Antes de cambiar este pedido a Pago contra reembolso (COD), confirme el problema de pago con el cliente para evitar malentendidos.' }}">
                                {{ 'Cambiar a COD' }}
                            </button>
                            <form action="{{ route('admin.order.switch_to_cod', [$order['id']]) }}" method="post"
                                id="order-{{ $order['id'] }}">
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
                <button type="button" class="close min-w-28 rounded-circle border bg-modal-btn" data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.order.offline_payment') }}" method="get">
                <div class="modal-body">
                    <div class="cont mb-4 text-center pb-xxl-1">
                        <img width="60px" height="60px" src="{{ asset('assets/admin/img/delete-confirmation.png') }}"
                            alt="public" class="mb-20">
                        <h3 class="mb-xl-2 mb-1">
                            {{ '¿Estás seguro de que no se recibió el pago?' }}
                        </h3>
                        <p class="mb-0 fs-14 max-w-420 mx-auto">
                            {{ 'Inserte una nota denegada para esta solicitud de pago para informar al cliente.' }}
                        </p>
                    </div>
                    <div class="bg-light2 p-3 rounded">
                        <label class="form-label">
                            {{ 'Nota denegada' }}
                            <span class="custom-tooltip" data-title="payment request to inform the customer ">
                                <i class="tio-info text-muted"></i>
                            </span>
                        </label>
                        <input type="hidden" name="id" value="{{ $order->id }}">
                        <textarea type="text" rows="1" maxlength="100" required class="form-control" name="note"
                            value="{{ old('note') }}"
                            placeholder="{{ 'ID de transacción no coincide' }}"></textarea>
                        <span class="text-right text-counting color-A7A7A7 d-block mt-1">0/100</span>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn--reset h-40px min-w-120px py-2 fs-14"
                        data-dismiss="modal">{{ 'cerca' }}</button>
                    <button type="submit"
                        class="btn btn-primary h-40px min-w-120px py-2 fs-14">{{ 'Confirmar rechazo' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endif







<!-- End Modal -->
<div class="modal fade" id="manually_parcel_amount_refund" tabindex="-1" role="dialog"
    aria-labelledby="offline_payment_cancelation_note_l" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="">
                    {{ 'Reembolso del importe del paquete' }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.order.parcelRefund') }}" method="post">
                    @csrf
                    @method('put')
                    <input type="hidden" name="id" value="{{ $order->id }}">
                    <input type="number" min="0" step="0.0001" max="{{ round($order->order_amount, 2) }}" required
                        class="form-control" name="refund_amount" value="{{ $order->order_amount }}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ 'cerca' }}</button>
                <button type="submit"
                    class="btn btn--danger btn-outline-danger">{{ 'Confirmar reembolso' }}
                </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Parcel cancellation Offcanvas -->
<div id="percel-cancellation_offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between">
    <form action="{{ route('admin.order.CancelParcel') }}" method="post">
        <div>
            @method('put')
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <div
                class="custom-offcanvas-header bg--secondary d-flex justify-content-between align-items-center px-3 py-3">
                <h3 class="mb-0">{{ 'Cancelación de paquete' }}</h2>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary text-dark offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;</button>
            </div>
            <div class="custom-offcanvas-body p-20">
                <div class="mb-20">
                    <label for="" class="text-title fs-14 mb-2">
                        {{ 'Entrega cancelada desde' }} <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex align-items-center gap-4 border rounded py-2 px-3">
                        <div class="custom-control custom-radio w-100">
                            <input type="radio"
                                data-cancellation_type="{{ in_array($order->order_status, ['picked_up', 'delivered']) ? 'after_pickup' : 'before_pickup' }}"
                                data-url="{{ route('admin.order.parcelCancellationReason') }}" id="customer_er"
                                name="delivery_cancelled_by" class="custom-control-input" value="customer" checked>
                            <label class="custom-control-label text-capitalize"
                                for="customer_er">{{ 'Cliente' }}</label>
                        </div>
                        <div class="custom-control custom-radio w-100">
                            <input type="radio" id="delivery"
                                data-cancellation_type="{{ in_array($order->order_status, ['picked_up', 'delivered']) ? 'after_pickup' : 'before_pickup' }}"
                                data-url="{{ route('admin.order.parcelCancellationReason') }}"
                                name="delivery_cancelled_by" class="custom-control-input" value="deliveryman">
                            <label class="custom-control-label text-capitalize"
                                for="delivery">{{ 'repartidor' }}</label>
                        </div>
                    </div>
                </div>
                <div class="mb-20 pb-2">
                    <h4 class="mb-10px">{{ 'Por favor seleccione el motivo de la cancelación' }}</h4>
                    <div id="data-view"> </div>
                </div>
                <div>
                    <h4 class="mb-10px">{{ 'Comentario' }}</h4>
                    <textarea name="note" data-target="#char-count" class="form-control char-counter" maxlength="100"
                        placeholder="{{ 'Escriba aquí el motivo de su cancelación...' }}" rows="3"></textarea>
                    <span id="char-count" class="text-right color-A7A7A7 d-block mt-1">0/100</span>
                </div>
            </div>
        </div>
        <div class="offcanvas-footer p-3 d-flex align-items-center justify-content-center gap-3">
            <button type="button"
                class="btn w-100 btn--reset offcanvas-close">{{ 'Continuar entrega' }}</button>
            <button type="submit" class="btn w-100 btn--primary">{{ 'Entregar' }}</button>
        </div>
    </form>
</div>
<div id="offcanvasOverlay" class="offcanvas-overlay"></div>
@endsection

@push('script_2')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}&libraries=places,marker&v=3.61">
        </script>
    <script>
        $(document).on('click', 'input[name="delivery_cancelled_by"], .trigger-reason', function () {
            let $input;

            if ($(this).is('input[name="delivery_cancelled_by"]')) {
                $input = $(this);
            } else {
                $input = $('input[name="delivery_cancelled_by"]:checked');
            }

            if ($input.length) {
                let type = $input.val();
                let url = $input.data('url');
                let cancellation_type = $input.data('cancellation_type');
                fetch_data(type, url, cancellation_type);
            }
        });

        function fetch_data(type, url, cancellation_type) {
            $.ajax({
                url: url,
                type: "get",
                data: {
                    user_type: type,
                    cancellation_type: cancellation_type

                },
                beforeSend: function () {
                    $('#data-view').empty();
                    $('#loading').show()
                },
                success: function (data) {
                    $("#data-view").append(data.view);
                },
                complete: function () {
                    $('#loading').hide()
                }
            })
        }

        $('.js-select2-custom').each(function () {
            var select2 = $.HSCore.components.HSSelect2.init($(this));
        });
        initCharCounter();

        $('.add-delivery-man').on('click', function () {
            id = $(this).data('id');
            $.ajax({
                type: "GET",
                url: '{{ url('/') }}/admin/order/add-delivery-man/{{ $order['id'] }}/' + id,
                success: function (data) {
                    location.reload();
                    console.log(data)
                    toastr.success('Successfully added', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                error: function (response) {
                    console.log(response);
                    toastr.error(response.responseJSON.message, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        })

        function last_location_view() {
            toastr.warning('Only available when order is out for delivery!', {
                CloseButton: true,
                ProgressBar: true
            });
        }

        var deliveryMan = <?php echo json_encode($deliveryMen); ?>;
        var map = null;
        const mapId = "{{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}"
        var myLatlng = new google.maps.LatLng({{ $address['latitude'] }}, {{ $address['longitude'] }});
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
                        position: point,
                        map: map,
                        title: deliveryMan[i].location,
                        content: activeIconContent
                    });
                    dmMarkers[deliveryMan[i].id] = marker;
                    google.maps.event.addListener(marker, 'click', (function (marker, i) {
                        return function () {
                            infowindow.setContent(
                                "<div style='float:left'><img style='max-height:40px;wide:auto;' src='" +
                                deliveryMan[i].image_link +
                                "'></div><div style='float:right; padding: 10px;'><b>" + deliveryMan[i]
                                    .name + "</b><br/> " + deliveryMan[i].location + "</div>");
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

                    // Create a marker for each place.
                    markers.push(
                        new google.maps.marker.AdvancedMarkerElement({
                            map,
                            title: place.name,
                            position: place.geometry.location,
                        })
                    );

                    if (place.geometry.viewport) {
                        // Only geocodes have viewport.
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });


        }

        $(document).ready(function () {
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
                }).then(function (r) { return r.json(); }).then(function (j) {
                    if (!j || !j.ok || j.lat == null || j.lng == null) return;
                    var la = parseFloat(j.lat);
                    var ln = parseFloat(j.lng);
                    if (!isFinite(la) || !isFinite(ln)) return;
                    orderDmMarker.position = new google.maps.LatLng(la, ln);
                }).catch(function () {});
            }

            // Re-init map before show modal
            $('#myModal').on('shown.bs.modal', function (event) {
                initMap();
                var button = $(event.relatedTarget);
                $("#dmassign-map").css("width", "100%");
                $("#map_canvas").css("width", "100%");
            });

            // Trigger map resize event after modal shown
            $('#myModal').on('shown.bs.modal', function () {
                initializeGMap();
                google.maps.event.trigger(map, "resize");
                map.setCenter(myLatlng);
            });

            // Address change modal modal shown
            $('#shipping-address-modal').on('shown.bs.modal', function () {
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
                        position: new google.maps.LatLng({{ $address['latitude'] }},
                                    {{ $address['longitude'] }}),
                        map: map,
                        title: "{{ $order->customer->f_name }} {{ $order->customer->l_name }}",
                        content: activeIconContent
                    });

                    google.maps.event.addListener(marker, 'click', (function (marker) {
                        return function () {
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
                            position: new google.maps.LatLng({{ $order->dm_last_location['latitude'] }},
                                        {{ $order->dm_last_location['longitude'] }}),
                            map: map,
                            title: "{{ $order->delivery_man->f_name }} {{ $order->delivery_man->l_name }}",
                            content: activeIconContent
                        });

                        google.maps.event.addListener(dmmarker, 'click', (function (dmmarker) {
                            return function () {
                                infowindow.setContent(
                                    "<div style='float:left'><img style='max-height:40px;wide:auto;' src='{{ $order?->delivery_man?->image_full_url ?? asset('assets/admin/img/160x160/img1.jpg') }}'></div> <div style='float:right; padding: 10px;'><b>{{ $order->delivery_man->f_name }} {{ $order->delivery_man->l_name }}</b><br /> {{ $order->dm_last_location['location'] }}</div>"
                                );
                                infowindow.open(map, dmmarker);
                            }
                        })(dmmarker));
                        locationbounds.extend(dmmarker.position);
                        orderDmMarker = dmmarker;
                    @endif


                    @if (isset($receiver_details))
                        var Receivermarker = new google.maps.marker.AdvancedMarkerElement({
                            position: new google.maps.LatLng({{ $receiver_details['latitude'] }},
                                        {{ $receiver_details['longitude'] }}),
                            map: map,
                            title: "{{ Str::limit($receiver_details['contact_person_name'], 15, '...') }}",
                            // icon: "{{ asset('assets/admin/img/restaurant_map.png') }}"
                        });

                        google.maps.event.addListener(Receivermarker, 'click', (function (Receivermarker) {
                            return function () {
                                infowindow.open(map, Receivermarker);
                            }
                        })(Receivermarker));
                        locationbounds.extend(Receivermarker.position);
                    @endif

                google.maps.event.addListenerOnce(map, 'idle', function () {
                    map.fitBounds(locationbounds);
                });
            }

            // Re-init map before show modal + posición en vivo del repartidor
            $('#locationModal').on('shown.bs.modal', function (event) {
                initializegLocationMap();
                stopOrderDmLivePoll();
                @if ($order->delivery_man)
                tickOrderDmLocation();
                orderDmLivePoll = setInterval(tickOrderDmLocation, 5000);
                @endif
            });
            $('#locationModal').on('hidden.bs.modal', function () {
                stopOrderDmLivePoll();
                orderDmMarker = null;
            });


            $('.dm_list').on('click', function () {
                var id = $(this).data('id');
                map.panTo(dmMarkers[id].position);
                map.setZoom(13);
                dmMarkers[id].setAnimation(google.maps.Animation.BOUNCE);
                window.setTimeout(() => {
                    dmMarkers[id].setAnimation(null);
                }, 3);
            });
        })
    </script>

    <script src="{{ asset('assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script type="text/javascript">
        $(function () {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'order_proof[]',
                maxCount: 6 -
                        {{ $order->order_proof && is_array($order->order_proof) ? count(json_decode($order->order_proof)) : 0 }},
                rowHeight: '176px !important',
                groupClassName: 'spartan_item_wrapper min-w-176px max-w-176px',
                maxFileSize: '',
                placeholderImage: {
                    image: "{{ asset('assets/admin/img/upload-img.png') }}",
                    width: '176px'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error(
                        "{{ 'Por favor ingrese solo archivos tipo png o jpg' }}", {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error("{{ 'tamaño de archivo demasiado grande' }}", {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });
    </script>
@endpush