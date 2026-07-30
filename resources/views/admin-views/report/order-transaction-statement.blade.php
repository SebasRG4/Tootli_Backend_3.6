<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{'declaración de transacción de pedido'}}</title>
    <meta http-equiv="Content-Type" content="text/html;"/>
    <meta charset="UTF-8">
    <style media="all">
        * {
            margin: 0;
            padding: 0;
            line-height: 1.3;
            font-family: sans-serif;
            color: #333542;
        }


        /* IE 6 */
        * html .footer {
            position: absolute;
            top: expression((0-(footer.offsetHeight)+(document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight)+(ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop))+'px');
        }

        body {
            font-size: .75rem;
        }

        img {
            max-width: 100%;
        }

        .customers {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }
        table {
            width: 100%;
        }

        table thead th {
            padding: 8px;
            font-size: 11px;
            text-align: start;
        }

        table tbody th,
        table tbody td {
            padding: 8px;
            font-size: 11px;
        }

        table.fz-12 thead th {
            font-size: 12px;
        }

        table.fz-12 tbody th,
        table.fz-12 tbody td {
            font-size: 12px;
        }

        table.customers thead th {
            background-color: #0177CD;
            color: #fff;
        }

        table.customers tbody th,
        table.customers tbody td {
            background-color: #FAFCFF;
        }

        table.calc-table th {
            text-align: start;
        }

        table.calc-table td {
            text-align: end;
        }
        table.calc-table td.text-left {
            text-align: start;
        }

        .table-total {
            font-family: Arial, Helvetica, sans-serif;
        }


        .text-left {
            text-align: start !important;
        }

        .pb-2 {
            padding-bottom: 8px !important;
        }

        .pb-3 {
            padding-bottom: 16px !important;
        }

        .text-right {
            text-align: end;
        }

        .content-position {
            padding: 15px 40px;
        }

        .content-position-y {
            padding: 0px 40px;
        }

        .text-white {
            color: white !important;
        }

        .bs-0 {
            border-spacing: 0;
        }
        .text-center {
            text-align: center;
        }
        .mb-1 {
            margin-bottom: 4px !important;
        }
        .mb-2 {
            margin-bottom: 8px !important;
        }
        .mb-4 {
            margin-bottom: 24px !important;
        }
        .mb-30 {
            margin-bottom: 30px !important;
        }
        .px-10 {
            padding-inline-start: 10px;
            padding-inline-end: 10px;
        }
        .fz-14 {
            font-size: 14px;
        }
        .fz-12 {
            font-size: 12px;
        }
        .fz-10 {
            font-size: 10px;
        }
        .font-normal {
            font-weight: 400;
        }
        .border-dashed-top {
            border-top: 1px dashed #ddd;
        }
        .font-weight-bold {
            font-weight: 700;
        }
        .bg-light {
            background-color: #F7F7F7;
        }
        .py-30 {
            padding-top: 30px;
            padding-bottom: 30px;
        }
        .py-4 {
            padding-top: 24px;
            padding-bottom: 24px;
        }
        .d-flex {
            display: flex;
        }
        .gap-2 {
            gap: 8px;
        }
        .flex-wrap {
            flex-wrap: wrap;
        }
        .align-items-center {
            align-items: center;
        }
        .justify-content-center {
            justify-content: center;
        }
        a {
            color: rgba(0, 128, 245, 1);
        }
        .p-1 {
            padding: 4px !important;
        }
        .h2 {
            font-size: 1.5em;
            margin-block-start: 0.83em;
            margin-block-end: 0.83em;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
            font-weight: bold;
        }

        .h4 {
            margin-block-start: 1.33em;
            margin-block-end: 1.33em;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
            font-weight: bold;
        }

    </style>
</head>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<body>
<div class="first">
    <table class="bs-0 mb-30 px-10">
        <tr>
            <th class="content-position-y text-left">
                <h2>{{'declaración de transacción de pedido'}}</h2>
                <p class="fz-14">{{'fecha'}} : {{ date('d M Y ' . config('timeformat'), strtotime(now())) }}</p>
                <h4 class="text-uppercase mb-1 fz-14">
                    {{'declaración'}}: #{{ $order_transaction->order->id }}
                </h4>
            </th>
            <th class="content-position-y text-right">
                <img height="50" src="{{asset("/storage/app/public/business/$company_web_logo")}}" alt="">
            </th>
        </tr>
    </table>
</div>
<div class="">
    <section>
        <table class="content-position-y fz-12">
            <tr>
                <td class="p-1">
                    <table>
                        <tr>
                            <td>
                                <div class="">
                                    <p class="fz-14">{{'fecha'}} :
                                        {{ date('d M Y ' . config('timeformat'), strtotime($order_transaction->order['created_at'])) }}
                                    </p>
                                    @if ($order_transaction->order->store)
                                        <p class="fz-14" style="margin-top: 6px; margin-bottom:0px;">{{'Negocio'}} : {{$order_transaction->order->store->name}}</p>
                                        @else
                                        <p class="fz-14" style="margin-top: 6px; margin-bottom:0px;">{{'tienda no encontrada'}}</p>
                                    @endif
                                    @if (isset($order_transaction->order->customer) )
                                        <p class="fz-14" style=" margin-top: 6px; margin-bottom:0px;">{{'Cliente'}} : {{$order_transaction->order->customer['f_name'] . ' ' . $order_transaction->order->customer['l_name']}}</p>
                                    @endif
                                </div>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>

                <td>
                    <table>
                        <tr>
                            <td class="text-right">
                                <p class="fz-14">{{'entregado por'}} : {{ucfirst($order_transaction->received_by)}}
                                    @if ($order_transaction->received_by == 'deliveryman')
                                            @if (isset($order_transaction->delivery_man) && $order_transaction->delivery_man->earning == 1)
                                                <br><small>{{'Independiente'}}</small>
                                            @elseif (isset($order_transaction->delivery_man) && $order_transaction->delivery_man->earning == 0 && $order_transaction->delivery_man->type == 'restaurant_wise')
                                            <br><small>{{'Restaurante'}}</small>
                                            @elseif (isset($order_transaction->delivery_man) && $order_transaction->delivery_man->earning == 0 && $order_transaction->delivery_man->type == 'zone_wise')
                                            <br><small>{{'Administración'}}</small>
                                            @endif
                                        </div>
                                    @endif
                                </p>
                                <p class="fz-14">{{'método de pago'}} : {{ translate(str_replace('_', ' ', $order_transaction->order['payment_method'])) }}</p>
                                <p class="fz-14">{{'estado de pago'}} : {{$order_transaction->status ? 'Reembolsado' : 'terminado'}}</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>


    </section>
</div>

<br>

<div class="">
    <div class="content-position-y">
        <table class="customers bs-0">
            <thead>
                <tr>
                    <th style="background-color: #107980 important">{{'SL'}}</th>
                    <th style="background-color: #107980 important">{{'detalles'}}</th>
                    <th style="background-color: #107980 important">{{'cantidad'}}</th>
                </tr>
            </thead>
            @php
            @endphp
            <tbody>
                <tr>
                    <td>1</td>
                    <td>{{'importe total del artículo'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->order['order_amount'] - $order_transaction->additional_charge - $order_transaction->order['dm_tips']-$order_transaction->order['delivery_charge'] - $order_transaction['tax']  + $order_transaction->order['coupon_discount_amount'] + $order_transaction->order['store_discount_amount']+$order_transaction->order['flash_admin_discount_amount']  +$order_transaction->order['flash_store_discount_amount'] + $order_transaction->order['ref_bonus_amount'] - $order_transaction->order['extra_packaging_amount']) }}</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>{{'descuento del artículo'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->order->details()->sum(DB::raw('discount_on_item * quantity')) + $order_transaction->order['flash_admin_discount_amount'] +$order_transaction->order['flash_store_discount_amount']) }}</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>{{'descuento total del cupón'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->order['coupon_discount_amount']) }}</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>{{'descuento por referencia'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->order['ref_bonus_amount']) }}</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td>{{'importe total descontado'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->order['coupon_discount_amount'] + $order_transaction->order['store_discount_amount']+$order_transaction->order['ref_bonus_amount'] +$order_transaction->order['flash_admin_discount_amount'] +$order_transaction->order['flash_store_discount_amount']) }}</td>
                </tr>
                <tr>
                    <td>6</td>
                    <td>{{'total iva/impuesto'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->tax) }}</td>
                </tr>
                <tr>
                    <td>7</td>
                    <td>{{'cargo total de entrega'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->delivery_charge) }}</td>
                </tr>
                <tr>
                    <td>8</td>
                    <td>{{\App\CentralLogics\Helpers::get_business_data('additional_charge_name')??'cargo adicional'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->additional_charge) }}</td>
                </tr>
                <tr>
                    <td>9</td>
                    <td>{{'cantidad de embalaje adicional'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->extra_packaging_amount) }}</td>
                </tr>

                <tr>
                    <td>10</td>
                    <td>{{'monto total del pedido'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->order_amount) }}</td>
                </tr>
            </tbody>
        </table>
        <br><br><br>
        <table class="customers bs-0">
            <thead>
                <tr>
                    <th style="background-color: transparent !important; color: #333542">{{'información adicional'}}</th>
                    <th style="background-color: transparent !important; color: #333542">{{'totales'}}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{'descuento de administrador'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->admin_expense) }}</td>
                </tr>
                <tr>
                    <td>{{'descuento de tienda'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->discount_amount_by_store+$order_transaction->order['flash_store_discount_amount']) }}</td>
                    {{-- <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->order->store_discount_amount) }}</td> --}}
                </tr>
                <tr>
                    <td>{{'comisión administrativa'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency(($order_transaction->admin_commission + $order_transaction->admin_expense) - $order_transaction->delivery_fee_comission -$order_transaction->additional_charge  - $order_transaction->order['flash_admin_discount_amount']) }}</td>
                </tr>
                <tr>
                    <td>{{'ingresos netos administrativos'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->admin_commission-$order_transaction->order['flash_admin_discount_amount']) }}</td>
                </tr>
                <tr>
                    <td>{{'ingresos netos de la tienda'}}</td>
                    <td>{{ \App\CentralLogics\Helpers::format_currency($order_transaction->store_amount - ($order_transaction?->order?->order_type == 'parcel' ? 0: $order_transaction->tax)) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<br>
<br>

<div class="row">
    <section>
        <table class="">
            <tr>
                <th class="fz-12 font-normal pb-3">
                    {{'Si necesita ayuda o tiene comentarios o sugerencias sobre nuestro sitio, usted'}} <br /> {{'puede enviarnos un correo electrónico a'}} <a href="mailto:({{ $company_email }})">{{ $company_email }}</a>
                </th>
            </tr>
            <tr>
                <th class="content-position-y bg-light py-4">
                    <div class="d-flex justify-content-center gap-2">
                        <div class="mb-2">
                            <i class="fa fa-phone"></i>
                            {{'teléfono'}}
                            : {{ $company_phone }}
                        </div>
                        <div class="mb-2">
                            <i class="fa fa-envelope" aria-hidden="true"></i>
                            {{'correo electrónico'}}
                            : {{$company_email}}
                        </div>
                    </div>
                    <div class="mb-2">
                        {{url('/')}}
                    </div>
                    <div>
                        &copy; {{$company_name}}. <span
                    class="d-none d-sm-inline-block">{{$footer_text}}</span>
                    </div>
                </th>
            </tr>
        </table>
    </section>
</div>

</body>
</html>
