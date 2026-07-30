<table id="columnSearchDatatable"
        class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
        data-hs-datatables-options='{
        "order": [],
        "orderCellsTop": true,

        "entries": "#datatableEntries",
        "isResponsive": false,
        "isShowPaging": false,
        "pagination": "datatablePagination"
        }'>
    <thead class="thead-light">
        <tr>
            <th class="border-0">{{'SL'}}</th>
            <th class="border-0">{{'título de bonificación'}}</th>
            <th class="border-0">{{'información de bonificación'}}</th>
            <th class="border-0">{{'monto del bono'}}</th>
            <th class="border-0">{{'comenzó el'}}</th>
            <th class="border-0">{{'caduca el'}}</th>
            <th class="border-0">{{'estado'}}</th>
            <th class="border-0 text-center">{{'acción'}}</th>
        </tr>
    </thead>

    <tbody id="set-rows">
        @foreach($bonuses as $key=>$bonus)
        <tr>
            <td>{{$key+1}}</td>
            <td>
<span class="d-block font-size-sm text-body">
                                    {{Str::limit($bonus['title'],25,'...')}}
                                    </span>
            </td>
            <td>{{ 'cantidad mínima agregada' }} -    {{\App\CentralLogics\Helpers::format_currency($bonus['minimum_add_amount'])}} <br>
                {{ 'bonificación máxima' }} - {{\App\CentralLogics\Helpers::format_currency($bonus['maximum_bonus_amount'])}}</td>
            <td>{{$bonus->bonus_type == 'amount'?\App\CentralLogics\Helpers::format_currency($bonus['bonus_amount']): $bonus['bonus_amount'].' (%)'}}</td>
            <td>{{ \Carbon\Carbon::parse($bonus->start_date)->format('d M Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($bonus->end_date)->format('d M Y') }}</td>
            <td>
                <label class="toggle-switch toggle-switch-sm" for="bonusCheckbox{{$bonus->id}}">
                    <input type="checkbox" data-url="{{route('admin.users.customer.wallet.bonus.status',[$bonus['id'],$bonus->status?0:1])}}" class="toggle-switch-input redirect-url" id="bonusCheckbox{{$bonus->id}}" {{$bonus->status?'checked':''}}>
                    <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                </label>
            </td>
            <td>
                <div class="btn--container justify-content-center">

                    <a class="btn action-btn btn--primary btn-outline-primary" href="{{route('admin.users.customer.wallet.bonus.update',[$bonus['id']])}}" title="{{'bono de edición'}}"><i class="tio-edit"></i>
                    </a>
                    <a class="btn action-btn btn--danger btn-outline-danger form-alert" href="javascript:" data-id="bonus-{{$bonus['id']}}" data-message="{{ '¿Quieres eliminar este bono?' }}" title="{{'eliminar bono'}}"><i class="tio-delete-outlined"></i>
                    </a>
                    <form action="{{route('admin.users.customer.wallet.bonus.delete',[$bonus['id']])}}"
                          method="post" id="bonus-{{$bonus['id']}}">
                        @csrf @method('delete')
                    </form>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<hr>
<table>
    <tfoot>

    </tfoot>
</table>
