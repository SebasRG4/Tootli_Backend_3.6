<div>
    <div class="table-responsive">
        <table id="datatable"
            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
            <thead class="thead-light">
                <tr>
                    <th class="border-0">{{'SL'}}</th>
                    <th class="border-0">{{'recibido en'}}</th>
                    <th class="border-0">{{'saldo antes de la transacción'}}</th>
                    <th class="border-0">{{'cantidad'}}</th>
                    <th class="border-0">{{'referencia'}}</th>
                    {{-- <th class="border-0">{{'acción'}}</th> --}}
                </tr>
            </thead>
            <tbody>
            @php($account_transaction = \App\Models\AccountTransaction::where('from_type', 'store')->where('type', 'collected')->where('from_id', $store->vendor->id)->paginate(25))
            @foreach($account_transaction as $k=>$at)
                <tr>
                    <td>{{$k+$account_transaction->firstItem()}}</td>
                    <td>{{$at->created_at->format('Y-m-d '.config('timeformat'))}}</td>
                    <td>{{\App\CentralLogics\Helpers::format_currency($at['current_balance'])}}</td>
                    <td>{{\App\CentralLogics\Helpers::format_currency($at['amount'])}}</td>
                    <td>{{translate($at['ref'])}}</td>
                    <td>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@if(count($account_transaction) !== 0)
<hr>
@endif
<div class="page-area">
    {!! $account_transaction->links() !!}
</div>
@if(count($account_transaction) === 0)
<div class="empty--data">
    <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
    <h5>
        {{'no se encontraron datos'}}
    </h5>
</div>
@endif
