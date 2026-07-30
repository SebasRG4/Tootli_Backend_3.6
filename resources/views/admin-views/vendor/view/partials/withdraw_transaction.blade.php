<div>
    <div class="table-responsive">
        <table id="datatable" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
            <thead class="thead-light">
                <tr>
                    <th class="border-0">{{'sl#'}}</th>
                    <th class="border-0">{{'creado en'}}</th>
                    <th class="border-0">{{'cantidad'}}</th>
                    <th class="border-0">{{'estado'}}</th>
                    <th class="border-0">{{'acción'}}</th>
                </tr>
            </thead>
            <tbody>
            @php($withdraw_transaction = \App\Models\WithdrawRequest::where('vendor_id', $store->vendor->id)->paginate(25))
            @foreach($withdraw_transaction as $k=>$wt)
                <tr>
                    <td scope="row">{{$k+$withdraw_transaction->firstItem()}}</td>
                    <td>{{date('Y-m-d '.config('timeformat'), strtotime($wt->created_at))}}</td>
                    <td>{{\App\CentralLogics\Helpers::format_currency($wt->amount)}}</td>
                    <td>
                        @if($wt->approved==0)
                            <label class="badge badge-primary">{{ 'Pendiente' }}</label>
                        @elseif($wt->approved==1)
                            <label class="badge badge-success">{{ 'aprobado' }}</label>
                        @else
                            <label class="badge badge-danger">{{ 'denegado' }}</label>
                        @endif
                    </td>
                    <td>
                        <a href="{{route('admin.store.withdraw_view',[$wt['id'],$store->vendor['id']])}}"
                            class="btn btn--warning action-btn btn-outline-warning"><i class="tio-visible"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@if(count($withdraw_transaction) !== 0)
<hr>
@endif
<div class="page-area">
    {!! $withdraw_transaction->links() !!}
</div>
@if(count($withdraw_transaction) === 0)
<div class="empty--data">
    <img src="{{asset('assets/admin/svg/illustrations/sorry.svg')}}" alt="public">
    <h5>
        {{'no se encontraron datos'}}
    </h5>
</div>
@endif
