<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'informes resumidos de la tienda' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'Criterios de búsqueda' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'filtrar'  }}- {{  translate($data['filter']) }}
                    <br>
                    {{ 'Contenido de la barra de búsqueda'  }}- {{ $data['search'] ??'N / A' }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
            <tr>
                <th>{{ 'Analítica' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'nueva tienda registrada'  }}- {{ $data['new_stores'] ??'N / A' }}
                    <br>
                    {{ 'pedidos totales'  }}- {{ $data['orders'] ??'N / A' }}
                    <br>
                    {{ 'monto total del pedido'  }}- {{ $data['total_order_amount'] ??'N / A' }}
                    <br>
                    {{ 'pedidos completados'  }}- {{ $data['total_delivered'] ??'N / A' }}
                    <br>
                    {{ 'pedidos incompletos'  }}- {{ $data['total_ongoing'] ??'N / A' }}
                    <br>
                    {{ 'pedidos cancelados'  }}- {{ $data['total_canceled'] ??'N / A' }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <th>{{ 'Estadísticas de pago' }}</th>
                <th></th>
                <th></th>
                <th>
                    {{ 'pagos en efectivo'  }} - {{ $data['cash_payments'] ??'N / A' }}
                    <br>
                    {{ 'pagos digitales'  }} - {{ $data['digital_payments'] ??'N / A' }}
                    <br>
                    {{ 'pagos de billetera'  }} - {{ $data['wallet_payments'] ??'N / A' }}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{'nombre de la tienda'}}</th>
            <th>{{'Orden total'}}</th>
            <th>{{'Orden total entregada'}}</th>
            <th>{{'Monto total'}}</th>
            <th>{{'Tasa de finalización'}}</th>
            <th>{{'Tarifa continua'}}</th>
            <th>{{'Tasa de cancelación'}}</th>
            <th>{{'Solicitudes de reembolso total'}}</th>
            <th>{{'Solicitudes de reembolso pendientes'}}</th>
        </thead>
        <tbody>
        @foreach($data['stores'] as $key => $store)
        @php($delivered = $store->orders->where('order_status', 'delivered')->count())
        @php($canceled = $store->orders->where('order_status', 'canceled')->count())
        @php($refunded = $store->orders->where('order_status', 'refunded')->count())
        @php($refund_requested = $store->orders->whereNotNull('refund_requested')->count())
        <tr>
            <td>{{$key+1}}</td>
            <td>
                {{  $store->name  }}
            </td>
            <td>
                {{ $store->orders->count() }}
            </td>
            <td>
                {{ $delivered }}
            </td>
            <td>
                {{\App\CentralLogics\Helpers::number_format_short($store->orders->where('order_status','delivered')->sum('order_amount'))}}
            </td>
            <td>
                {{ ($store->orders->count() > 0 && $delivered > 0)? number_format((100*$delivered)/$store->orders->count(), config('round_up_to_digit')): 0 }}%
            </td>
            <td>
                {{ ($store->orders->count() > 0 && $delivered > 0)? number_format((100*($store->orders->count()-($delivered+$canceled)))/$store->orders->count(), config('round_up_to_digit')): 0 }}%
            </td>
            <td>
                {{ ($store->orders->count() > 0 && $canceled > 0)? number_format((100*$canceled)/$store->orders->count(), config('round_up_to_digit')): 0 }}%
            </td>
            <td>
                {{ $refunded }}
            </td>
            <td>
                {{ $refund_requested }}
            </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
