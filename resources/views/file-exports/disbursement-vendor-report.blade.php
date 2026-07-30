
<div class="row">
    @php($address = \App\Models\BusinessSetting::where(['key' => 'address'])->first()->value)
    <table>
        <thead>
            <tr>

                <th>
                    {{ 'Informe de desembolso' }}
                </th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th>

                </th>
            </tr>
            <tr>

                <th>{{ 'criterios de filtrado' }} -</th>
                <th></th>
                <th>
                    <br>
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
                    <br>
                    {{ 'estado'  }}: {{ $data['status'] ?? 'N / A' }}

                </th>
                <th></th>
                <th></th>
                <th>

                </th>
            </tr>
            <tr>

                <th>
                {{ 'Desembolsos pendientes' }} - {{ $data['pending'] ?? 'N / A' }}
                </th>
                <th></th>
                <th>{{ 'Desembolsos completados' }} - {{ $data['completed'] ?? 'N / A' }}
                </th>
                <th></th>
                <th>{{ 'Transacciones canceladas' }} - {{ $data['canceled'] ?? 'N / A' }}
                </th>
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
