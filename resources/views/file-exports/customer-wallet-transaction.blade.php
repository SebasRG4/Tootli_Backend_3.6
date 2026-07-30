<div class="row">
    <div class="col-lg-12 text-center "><h1 >{{ 'Historial de transacciones de billetera' }}</h1></div>
    <div class="col-lg-12">



    <table>
        <thead>
            <tr>
                <th>{{ 'Criterios de búsqueda' }}</th>
                <th></th>
                <th>
                    @if ($data['from'])
                    <br>
                    {{ 'de'}} - {{ $data['from']?Carbon\Carbon::parse($data['from'])->format('d M Y'):'' }}
                    @endif
                    @if ($data['to'])
                    <br>
                    {{ 'a'}} - {{ $data['to']?Carbon\Carbon::parse($data['to'])->format('d M Y'):'' }}
                    @endif
                    <br>
                    {{ 'tipo de transacción'  }}- {{  $data['transaction_type']?translate($data['transaction_type']):'Todo' }}
                    <br>
                    {{ 'Clientes'  }}- {{  $data['customer']??'Todo' }}

                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
                </tr>
                @php
                $credit = $data['data'][0]->total_credit;
                $debit = $data['data'][0]->total_debit;
                $balance = $credit - $debit;
            @endphp
            <tr>
                <th>{{ 'Analítica' }}</th>
                <th></th>
                <th>
                    {{ 'débito'  }}- {{\App\CentralLogics\Helpers::format_currency($debit)}}
                    <br>
                    {{ 'crédito'  }}- {{\App\CentralLogics\Helpers::format_currency($credit)}}
                    <br>
                    {{ 'balance'  }}- {{\App\CentralLogics\Helpers::format_currency($balance)}}
                </th>
                <th> </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        <tr>
            <th>{{ 'SL' }}</th>
            <th>{{'identificación de transacción'}}</th>
            <th>{{'fecha de transacción'}}</th>
            <th>{{'Cliente'}}</th>
            <th>{{'crédito'}}</th>
            <th>{{'débito'}}</th>
            <th>{{'balance'}}</th>
            <th>{{'tipo de transacción'}}</th>
            <th>{{'referencia'}}</th>
        </thead>
        <tbody>
        @foreach($data['transactions'] as $key => $wt)
        <tr>
            <td>{{$key+1}}</td>
            <td>{{$wt->transaction_id}}</td>
            <td>
                {{date('d-m-Y',strtotime($wt['created_at']))}}
            </td>
            <td>{{ $wt->user?$wt->user->f_name.' '.$wt->user->l_name:'extraviado' }}</td>
            <td>{{$wt->credit}}</td>
            <td>{{$wt->debit}}</td>
            <td>{{$wt->balance}}</td>
            <td>
                {{ translate('messages.'.$wt->transaction_type)}}
            </td>
            <td>{{$wt->reference}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
