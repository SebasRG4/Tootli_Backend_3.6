@foreach ($orders as $key => $order)
<tr class="status-{{ $order['order_status'] }} class-all">
    <td class="">
        {{ $key + 1 }}
    </td>
    <td class="table-column-pl-0">
        <a
            href="{{ route('admin.order.details', ['id' => $order['id'],'module_id'=>$order['module_id']]) }}">{{ $order['id'] }}</a>
    </td>
    <td  class="text-capitalize">
        @if($order->store)
            {{Str::limit($order->store->name,25,'...')}}
        @else
            <label class="badge badge-danger">{{ 'inválido' }}
        @endif
    </td>
    <td>
        @if($order->is_guest)
        @php($customer_details = json_decode($order['delivery_address'],true))
        <strong>{{$customer_details['contact_person_name']}}</strong>
        <div>{{$customer_details['contact_person_number']}}</div>

        @elseif ($order->customer)
        <a class="text-body text-capitalize"
            href="{{ route('admin.users.customer.view', [$order['user_id']]) }}">
            <strong>{{ $order->customer['f_name'] . ' ' . $order->customer['l_name'] }}</strong>
        </a>
        @else
            <label class="badge badge-danger">{{ 'inválido' }}
                {{ 'Cliente' }}
                {{ 'datos' }}</label>
        @endif
    </td>
    <td>
        <div class="text-right mw--85px">
            <div>
                {{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']-$order['dm_tips']-$order['total_tax_amount']-$order['delivery_charge']+$order['coupon_discount_amount'] + $order['store_discount_amount']) }}
            </div>
            @if ($order->payment_status == 'paid')
                <strong class="text-success">
                    {{ 'pagado' }}
                </strong>
            @else
                <strong class="text-danger">
                    {{ 'no pagado' }}
                </strong>
            @endif
        </div>
    </td>
    <td class="text-center mw--85px">
        {{ \App\CentralLogics\Helpers::number_format_short($order->details->sum('discount_on_item')) }}
    </td>
    <td class="text-center mw--85px">
        {{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount']) }}
    </td>
    <td class="text-center mw--85px">
        {{ \App\CentralLogics\Helpers::number_format_short($order['coupon_discount_amount'] + $order['store_discount_amount']) }}
    </td>
    <td class="text-center mw--85px white-space-nowrap">
        {{ \App\CentralLogics\Helpers::number_format_short($order['total_tax_amount']) }}
    </td>
    <td class="text-center mw--85px">
        {{ \App\CentralLogics\Helpers::number_format_short($order['delivery_charge']) }}
    </td>
    <td>
        <div class="text-right mw--85px">
            <div>
                {{ \App\CentralLogics\Helpers::number_format_short($order['order_amount']) }}
            </div>
            @if ($order->payment_status == 'paid')
                <strong class="text-success">
                    {{ 'pagado' }}
                </strong>
            @else
                <strong class="text-danger">
                    {{ 'no pagado' }}
                </strong>
            @endif
        </div>
    </td>
    <td class="text-center mw--85px text-capitalize">
        {{isset($order->transaction) ? $order->transaction->received_by : 'aún no recibido'}}
    </td>
    <td class="text-center mw--85px text-capitalize">
            {{ translate(str_replace('_', ' ', $order['payment_method'])) }}
    </td>
    <td class="text-center mw--85px text-capitalize">
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
            @elseif($order['order_status']=='picked_up')
                <span class="badge badge-soft-warning">
                  {{'En Camino de Entrega'}}
                </span>
            @elseif($order['order_status']=='delivered')
                <span class="badge badge-soft-success">
                  {{'Entregado'}}
                </span>
            @elseif($order['order_status']=='failed')
                <span class="badge badge-soft-danger">
                  {{'pago fallido'}}
                </span>
            @elseif($order['order_status']=='handover')
                <span class="badge badge-soft-danger">
                  {{'Entregar'}}
                </span>
            @elseif($order['order_status']=='canceled')
                <span class="badge badge-soft-danger">
                  {{'Cancelado'}}
                </span>
            @elseif($order['order_status']=='accepted')
                <span class="badge badge-soft-danger">
                  {{'aceptado'}}
                </span>
            @else
                <span class="badge badge-soft-danger">
                  {{str_replace('_',' ',$order['order_status'])}}
                </span>
            @endif

    </td>


    <td>
        <div class="btn--container justify-content-center">
            <a class="ml-2 btn btn-sm btn--warning btn-outline-warning action-btn"
                href="{{ route('admin.order.details', ['id' => $order['id'],'module_id'=>$order['module_id']]) }}">
                <i class="tio-invisible"></i>
            </a>
            <a class="ml-2 btn btn-sm btn--primary btn-outline-primary action-btn"
                href="{{ route('admin.transactions.order.generate-invoice', ['id' => $order['id']]) }}">
                <i class="tio-print"></i>
            </a>
        </div>
    </td>
</tr>
@endforeach
