<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'informes de gastos' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'Criterios de búsqueda' }}</th>
                <th></th>
                <th></th>
                <th>
                    @if(isset($data['module']))
                    {{ 'módulo'}} - {{ $data['module']?translate($data['module']):'todo' }}
                    <br>
                    @endif

                    {{ 'zona'}} - {{ $data['zone']??'todo' }}
                    <br>
                    {{ (isset($data['module_type']) && $data['module_type'] == 'rental')?'proveedor':'proveedor'}} - {{ $data['store']??'todo' }}
                    @if (!isset($data['type']) )
                    <br>
                    {{ 'Cliente'}} - {{ $data['customer']??'todo' }}
                    @endif
                    @if ($data['from'])
                    <br>
                    {{ 'de'}} - {{ $data['from']?Carbon\Carbon::parse($data['from'])->format('d M Y'):'' }}
                    @endif
                    @if ($data['to'])
                    <br>
                    {{ 'a'}} - {{ $data['to']?Carbon\Carbon::parse($data['to'])->format('d M Y'):'' }}
                    @endif
                    <br>
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
            <th>{{ 'SL' }}</th>
            @if (isset($data['module_type']))
            <th>{{$data['module_type'] == 'rental'? 'identificación del viaje' : 'identificación del pedido' }}</th>
            @elseif(addon_published_status('Rental'))
                <th>{{ 'identificación del pedido' }}</th>
                <th>{{ 'identificación del viaje' }}</th>
            @endif
            <th>{{'Fecha y hora'}}</th>
            <th>{{ 'Tipo de gasto' }}</th>
            <th>{{ 'Nombre del cliente' }}</th>
            <th>{{'monto del gasto'}}</th>
        </thead>
        <tbody>
        @foreach($data['expenses'] as $key => $exp)
            <tr>
                <td>{{ $key+1}}</td>
                @if (isset($data['module_type']))
                    <td>
                        @if ($exp->order && $data['module_type'] != 'rental')
                            {{ $exp['order_id'] }}
                        @elseif ($exp->trip && $data['module_type'] == 'rental')
                            {{ $exp['trip_id'] }}
                        @endif
                    </td>
                @elseif(addon_published_status('Rental'))
                    <td>{{ $exp['order_id'] }}</td>
                    <td>{{ $exp['trip_id'] }}</td>
                @endif
                <td>
                    {{date('Y-m-d '.config('timeformat'),strtotime($exp->created_at))}}
                </td>
                <td>{{'{$exp[\'tipo\']}'}}</td>
                <td class="text-center">
                    @if ($exp->order)

                    @if($exp->order?->is_guest)
                    @php($customer_details = json_decode($exp->order['delivery_address'],true))
                    <strong>{{$customer_details['contact_person_name']}}</strong>

                    @elseif($exp->order?->customer)

                    {{$exp->order?->customer['f_name'].' '.$exp->order?->customer['l_name']}}
                    @else
                        <label
                            class="badge badge-danger">{{'datos de cliente no válidos'}}</label>
                    @endif

                    @elseif($exp->trip)
                    @if ($exp?->trip?->customer)

                        {{ $exp?->trip?->customer?->fullName }}

                        @elseif($exp?->trip?->user_info['contact_person_name'])
                            <div class="font-medium">
                                {{$exp?->trip?->user_info['contact_person_name'] }}
                            </div>
                        @else
                            {{ 'Usuario invitado' }}
                        @endif


                    @elseif ($exp['type'] == 'add_fund_bonus')
                    {{ $exp->user->f_name.' '.$exp->user->l_name }}
                    @else
                    <label class="badge badge-danger">{{'datos de cliente no válidos'}}</label>

                    @endif
                </td>
                <td>{{\App\CentralLogics\Helpers::format_currency($exp['amount'])}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
