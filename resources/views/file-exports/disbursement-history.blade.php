
<div class="row">
    @php($address = \App\Models\BusinessSetting::where(['key' => 'address'])->first()->value)
    <table>
        <thead>
            <tr>

                <th>
                    {{ 'Lista de desembolsos' }}
                </th>
                <th></th>
                <th></th>
                <th>
                    @if($data['type'] == 'store')
                        {{ translate($data['is_provider'] ? 'Provider' : 'Store') }} - {{ $data['store'] }}
                    @else
                        {{ 'repartidor' }} - {{ $data['delivery_man'] }}
                    @endif
                </th>
                <th></th>
                <th>

                </th>
            </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{ 'identificación' }}</th>
            <th>{{ 'creado en' }}</th>
            <th>{{ 'cantidad' }}</th>
            <th>{{ 'Método de pago' }}</th>
            <th>{{ 'estado' }}</th>

        </thead>
        <tbody>
        @foreach($data['disbursements'] as $key => $disb)
            <tr>
        <td>{{ $loop->index+1}}</td>
        <td>{{ $disb['disbursement_id'] }}</td>
        <td>{{ \App\CentralLogics\Helpers::time_date_format($disb['created_at']) }}</td>
        <td>
            {{\App\CentralLogics\Helpers::format_currency($disb['disbursement_amount'])}}
        </td>
        <td>
            <div class="name">{{'método de pago'}} : {{$disb->withdraw_method->method_name}}</div>
            @forelse(json_decode($disb->withdraw_method->method_fields, true) as $key=> $item)
            <br>
                <div>
                    <span>{{  translate($key) }}</span>
                    <span>:</span>
                    <span class="name">{{$item}}</span>
                </div>

            @empty

            @endforelse
        </td>
        <td>{{ $disb['status'] }}</td>

            </tr>
        @endforeach
        </tbody>
    </table>
</div>
