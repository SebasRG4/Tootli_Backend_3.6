
<div class="row g-3">
    <?php

    $disbursement_type = \App\Models\BusinessSetting::where('key' , 'disbursement_type')->first()->value ?? 'manual';
    $min_amount_to_pay_store = \App\Models\BusinessSetting::where('key' , 'min_amount_to_pay_store')->first()->value ?? 0;

    $wallet_earning =  round($wallet->total_earning - ($wallet->total_withdrawn + $wallet->pending_withdraw) , 8);

    if($wallet->balance > 0 && $wallet->collected_cash > 0 ){
        $adjust_able = true;
    } elseif($wallet->collected_cash != 0 && $wallet_earning !=  0 ){
        $adjust_able = true;
    } elseif($wallet->balance ==  $wallet_earning  ){
        $adjust_able = false;
    }
    else{
        $adjust_able = false;
    }

    $digital_payment = App\CentralLogics\Helpers::get_business_settings('digital_payment');
    $digital_payment  = $digital_payment['status'];

    ?>

    @if($adjust_able ==  true  || ($disbursement_type ==  'manual' && $wallet->balance > 0) || $wallet->balance < 0 || ( $wallet->collected_cash > 0 && $min_amount_to_pay_store <= $wallet->collected_cash ))
            <?php
            $col_size = true;
            ?>
    @endif



    <!-- Store Wallet Balance -->
    <div class="col-md-12">
        <div class="row g-3">
            @if($wallet->balance < 0)
            <div class="col-12">
                <div class="alert alert-danger d-flex align-items-center gap-2 m-0 p-3 rounded" style="border-left: 5px solid #dc3545; background-color: #f8d7da; color: #721c24; display: flex; align-items: center;">
                    <i class="tio-money" style="font-size: 1.5rem; margin-right: 8px;"></i>
                    <div>
                        <strong>{{ 'le debes a tootli' }}:</strong>
                        <span>{{ 'debes' }} <strong style="font-weight: 800; color: #dc3545;">{{\App\CentralLogics\Helpers::format_currency(abs($wallet->balance))}}</strong> {{ 'a tootli se te descontara de tu proximo pedido' }}.</span>
                    </div>
                </div>
            </div>
            @endif
            <!-- Panding Withdraw Card Example -->
            <div class="col-sm-{{ isset($col_size) == true ? '3' :'4' }}">
                <div class="resturant-card shadow--card-2" >
                    <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->collected_cash)}}</h4>

                    <div class="d-flex gap-1 align-items-center">
                                    <span class="subtitle">{{'Efectivo en mano'}}
                                    </span>

                        <span class="form-label-secondary text-danger d-flex"
                              data-toggle="tooltip" data-placement="right"
                              data-original-title="{{ 'El importe total que has recibido del cliente en efectivo (contra reembolso)'}}"><img
                                src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                alt="{{ 'Tome una fotografía para completar la entrega' }}"> </span>
                        <img class="resturant-icon" src="{{asset('assets/admin/img/transactions/image_total89.png')}}" alt="public">

                    </div>
                </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-sm-{{ isset($col_size)  == true ? '3' :'4' }}">
                <div class="resturant-card shadow--card-2">
                    <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->balance > 0 ? $wallet->balance: 0 )}}</h4>
                    <span class="subtitle">{{'retirar saldo capaz'}}</span>
                    <img class="resturant-icon" src="{{asset('assets/admin/img/transactions/image_w_balance.png')}}" alt="public">
                </div>
            </div>
            <!-- Pending Requests Card Example -->
            <div class="col-sm-{{ isset($col_size) == true ? '6' :'4' }}">
                <div class="resturant-card shadow--card-2">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>

                            @if ($wallet->balance > 0)
                                <h4 class="title">{{\App\CentralLogics\Helpers::format_currency(abs($wallet_earning))}}</h4>


                                @if( $wallet->balance ==  $wallet_earning )

                                    <span class="subtitle">{{ 'Saldo Retirable' }}</span>
                                @else
                                    <span class="subtitle">{{ 'Balance' }}
                                            <small>{{'Sin ajustar'}} </small>
                                        </span>
                                @endif

                            @else
                                <h4 class="title">{{\App\CentralLogics\Helpers::format_currency(abs($wallet->collected_cash))}}</h4>
                                <span class="subtitle">{{  'Saldo a pagar'}}</span>

                            @endif


                        </div>

                        @if($wallet->balance > 0  &&  $wallet->balance > $wallet->collected_cash  )
                            <div class="d-flex gap-2 flex-wrap">
                                @if ($adjust_able ==  true )
                                    <a class="btn btn--primary d-flex gap-1 align-items-center text-nowrap"  href="javascript:" data-toggle="modal" data-target="#Adjust_wallet">{{'Ajustar con billetera'}}

                                        <span class="form-label-secondary d-flex"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Ajuste el saldo retirable y el saldo no ajustado con su billetera (efectivo en mano) o haga clic en "Solicitar retiro"'}}">
                                        <i class="tio-info-outined"> </i>

                                        </span>

                                    </a>
                                @endif

                                @if ($disbursement_type ==  'manual'  )
                                    <a  href="javascript:"

                                       @if(count($withdrawal_methods) !== 0 )
                                           class="btn btn--primary d-flex gap-1 align-items-center text-nowrap"
                                       data-toggle="modal" data-target="#balance-modal"
                                        @else
                                            class="btn btn--primary d-flex gap-1 align-items-center text-nowrap withdrawal-methods-disable"
                                        data-message="{{'Los métodos de retiro no están disponibles'}}"
                                       @endif
                                    >{{'solicitar retiro'}}

                                        <span class="form-label-secondary  d-flex"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Como tiene más "Saldo retirable" que "Dinero en efectivo", debe solicitar el retiro al Administrador.'}}">
                                            <i class="tio-info-outined"> </i> </span>
                                    </a>
                                @endif
                            </div>
                        @elseif($wallet->balance < 0 ||  ($wallet->collected_cash > 0 && $wallet->collected_cash  > $wallet->balance )     )
                            <div class="d-flex gap-2 flex-wrap">

                                @if ($adjust_able ==  true )
                                    <a class="btn btn--primary d-flex gap-1 align-items-center text-nowrap"  href="javascript:" data-toggle="modal" data-target="#Adjust_wallet">{{'Ajustar con billetera'}}

                                        <span class="form-label-secondary  d-flex"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Como tiene más "efectivo en mano" que "saldo retirable", debe pagarle al administrador.'}}"> <i class="tio-info-outined"> </i> </span> </span>
                                    </a>
                                @endif

                                @if ($min_amount_to_pay_store <= $wallet->collected_cash )
                                    <a
                                    @if ( $digital_payment != 1)
                                    class="btn btn--secondary d-flex gap-1 align-items-center text-nowrap payment-warning"  href="javascript:"

                                    @else

                                    class="btn btn--primary d-flex gap-1 align-items-center text-nowrap"  href="javascript:"
                                    data-toggle="modal" data-target="#payment_model"
                                    @endif

                                    >{{'Paga ahora'}}

                                        <span class="form-label-secondary  d-flex"
                                              data-toggle="tooltip" data-placement="right"
                                              data-original-title="{{ 'Ajuste el saldo pagadero y retirable con su billetera (efectivo en mano) o haga clic en "Pagar ahora".'}}"> <i class="tio-info-outined"> </i> </span> </span></a>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="col-md-12">
        <div class="row g-3">
            <!-- Panding Withdraw Card Example -->
            <div class="col-sm-4">
                <div class="resturant-card  bg--3" >
                    <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->pending_withdraw)}}</h4>
                    <span class="subtitle">{{'pendiente de retiro'}}</span>
                    <img class="resturant-icon" src="{{asset('assets/admin/img/transactions/image_pending.png')}}" alt="public">
                </div>
            </div>

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-sm-4">
                <div class="resturant-card  bg--2">
                    <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->total_withdrawn)}}</h4>
                    <span class="subtitle">{{'Total Retirado'}}</span>
                    <img class="resturant-icon" src="{{asset('assets/admin/img/transactions/image_withdaw.png')}}" alt="public">
                </div>
            </div>


            <!-- Pending Requests Card Example -->
            <div class="col-sm-4">
                <div class="resturant-card  bg--1">
                    <h4 class="title">{{\App\CentralLogics\Helpers::format_currency($wallet->total_earning)}}</h4>
                    <span class="subtitle">{{'ganancia total'}}</span>
                    <img class="resturant-icon" src="{{asset('assets/admin/img/transactions/image_total89.png')}}" alt="public">
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="balance-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    {{'retirar solicitud'}}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="btn btn--circle btn-soft-danger text-danger"><i class="tio-clear"></i></span>
                </button>
            </div>

            <form id="withdraw_form" action="{{route('vendor.wallet.withdraw-request')}}" method="post">
                <div class="modal-body">
                    @csrf
                    <div class="">
                        <select class="form-control" id="withdraw_method" name="withdraw_method" required>
                            <option value="" selected disabled>{{'Seleccione el método de retiro'}}</option>
                            @foreach($withdrawal_methods as $item)
                                <option value="{{$item['id']}}">{{$item['method_name']}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="" id="method-filed__div">
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="form-label">{{'cantidad'}}:</label>
                        <input type="number" name="amount"  step="0.01"
                               value="{{abs($wallet->balance)}}"
                               class="form-control h--45px" id="" min="1" max="{{abs($wallet->balance)}}">
                    </div>
                </div>
                <div class="modal-footer pt-0 border-0">
                    <button type="button" class="btn btn--reset" data-dismiss="modal">{{'Cancelar'}}</button>
                    <button type="submit"  id="set_disable" id="submit_button" class="btn btn--primary">{{'Entregar'}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"  aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{'Nota'}}:  </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>
            <div class="modal-body">

                <div class="form-group">
                    <p  id="hiddenValue"> </p>
                </div>
            </div>
            <div class="modal-footer">
                <button id="reset_btn" type="reset" data-dismiss="modal" class="btn btn-secondary" >{{ 'Cerca' }} </button>
            </div>
        </div>
    </div>
</div>
<!-- Content Row -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">

                <ul class="nav nav-tabs page-header-tabs pb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('vendor-panel/wallet') ?'active':''}}"  href="{{ route('vendor.wallet.index') }}">{{'retirar solicitud'}}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link  {{Request::is('vendor-panel/wallet/wallet-payment-list') ?'active':''}}" href="{{route('vendor.wallet.wallet_payment_list')}}"  aria-disabled="true">{{'Historial de pagos'}}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link  {{Request::is('vendor-panel/wallet/disbursement-list') ?'active':''}}" href="{{route('vendor.wallet.getDisbursementList')}}"  aria-disabled="true">{{'Próximos pagos'}}</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link  {{Request::is('vendor-panel/wallet/cash-on-pickup-history') ?'active':''}}" href="{{route('vendor.wallet.cash_on_pickup_history')}}">{{'historial recolecciones efectivo'}}</a>
                    </li>
                </ul>

            </div>
