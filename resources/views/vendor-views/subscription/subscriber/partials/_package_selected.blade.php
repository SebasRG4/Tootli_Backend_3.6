<div class="">
    <div>
        <div class="text-center mb-4 pb-2">

            @if ($store_subscription?->package_id ==  $package->id)
            <h2 class="modal-title">{{'Renovar plan de suscripción'}}</h2>
            @else
            <h2 class="modal-title">{{'Cambiar a un nuevo plan de suscripción'}}</h2>
            @endif

        </div>
        <div class="change-plan-wrapper align-items-center">
            @if ($store_business_model == 'commission'  )
            <div class="__plan-item">
                <div class="inner-div">
                    <div class="text-center">
                        <h3 class="title">{{ 'comisión'  }}</h3>
                        <h2 class="price">{{  $admin_commission }} %</h2>
                        {{-- <div class="day-count">{{ $store_subscription?->package?->validity }} {{ 'días' }}</div> --}}
                    </div>
                </div>
            </div>
            <!-- Plan Seperator Arrow -->
            <div class="plan-seperator-arrow mx-auto">
                <img src="{{asset('assets/admin/img/exchange.svg')}}" alt="" class="w-100">
            </div>
            <!-- Plan Seperator Arrow -->

            @elseif(!in_array($store_business_model,['commission','none']))

            <div class="__plan-item {{ !$store_subscription  || $store_subscription?->package_id ==  $package->id ?  'active' : '' }}">
                <div class="inner-div">
                    <div class="text-center">
                        <h3 class="title">{{ $store_subscription?->package?->package_name  }}</h3>
                        <h2 class="price">{{  \App\CentralLogics\Helpers::format_currency($store_subscription?->package?->price) }}</h2>
                        <div class="day-count">{{ $store_subscription?->package?->validity }} {{ 'días' }}</div>
                    </div>
                </div>
            </div>
                @if ( $store_subscription?->package_id !=  $package->id )
                <!-- Plan Seperator Arrow -->
                <div class="plan-seperator-arrow mx-auto">
                <img src="{{asset('assets/admin/img/exchange.svg')}}" alt="" class="w-100">
                </div>
                <!-- Plan Seperator Arrow -->
                @endif
            @endif


            @if ($store_subscription?->package_id !==  $package->id || $store_business_model == 'commission' )

            <div class="__plan-item active">
                <div class="inner-div">
                    <div class="text-center">
                        <h3 class="title">{{$package->package_name }}</h3>
                        <h2 class="price">{{ \App\CentralLogics\Helpers::format_currency($package?->price) }}</h2>
                        <div class="day-count">{{ $package?->validity }} {{ 'días' }}</div>
                    </div>
                </div>
            </div>

            @endif
        </div>


        <div class="mb-2 mb-lg-3 subscription__plan-info-wrapper bg-ECEEF1 rounded-20">
            <div class="row g-3">
                <div class="col-md-{{ $pending_bill > 0 ? '3' :'4' }}">
                    <div class="subscription__plan-info">
                        <div class="info">
                            {{ 'Validez' }}
                        </div>
                        <h4 class="subtitle">{{ $package?->validity }} {{ 'días' }}</h4>
                    </div>
                </div>
                <div class="col-md-{{ $pending_bill > 0 ? '3' :'4' }}">
                    <div class="subscription__plan-info">
                        <div class="info">
                            {{ 'Precio' }}
                        </div>
                        <h4 class="subtitle">{{ \App\CentralLogics\Helpers::format_currency($package?->price) }}</h4>
                    </div>
                </div>
                @if ($pending_bill)
                <div class="col-md-3">
                    <div class="subscription__plan-info">
                        <div class="info">
                            {{ 'factura pendiente' }}
                        </div>
                        <h4 class="subtitle">{{ \App\CentralLogics\Helpers::format_currency($pending_bill) }}</h4>
                    </div>
                </div>

                @endif
                <div class="col-md-{{ $pending_bill > 0 ? '3' :'4' }}">
                    <div class="subscription__plan-info">
                        <div class="info">
                            {{ 'Estado de la factura' }}
                        </div> <h4 class="subtitle">  {{  $store_business_model != 'commission' &&  $store_subscription?->package_id ==  $package->id ? 'Renovar' :  'Migrar al nuevo plan' }}  </h4> </div>
                </div>
            </div>
        </div>
        @if (data_get($cash_backs,'back_amount') > 0 )
        <div class="mb-2 mb-lg-3 subscription__plan-info-wrapper bg--10 rounded-20 py-2">
            <div class="row g-3">
            <div class="col-auto">
                <i class="tio-notice"></i>
                    {{ 'obtendrás' }}  {{ \App\CentralLogics\Helpers::format_currency(data_get($cash_backs,'back_amount')) }} {{ 'a tu billetera por permanecer' }}  {{ data_get($cash_backs,'days') }} {{ 'plan de suscripción de días' }}
                </div>
            </div>
        </div>
        @endif
        <form action="{{ route('vendor.subscriptionackage.packageBuy') }}" method="post">
            @csrf
            @method('POST')
                <input type="hidden" value="{{ $package->id }}" name="package_id">
                <input type="hidden" value="{{ $store_id }}" name="store_id">
                <input type="hidden" value="{{ $store_subscription?->package_id ==  $package->id ? 'renew' : 'payment' }}" name="type">




        <h4 class="mb-4">{{ 'Pagar en línea' }} <span class="font-regular text-body">({{ 'Forma más rápida y segura de pagar la factura' }})</span></h4>
        <div class="row g-3">
            @if ($balance > 0)

            <div class="col-md-6">
                <label class="payment-item">
                    <input type="radio" {{ $balance >= $package?->price ? '' :'disabled'  }} value="wallet"  class="d-none" name="payment_gateway">
                    <div  data-toggle="tooltip" data-placement="bottom" title="{{$balance >= $package?->price ? 'pagar el importe a través de billetera' : '¡No tienes saldo suficiente en tu billetera! por favor agregue dinero a su billetera para comprar los paquetes' }}"  class="payment-item-inner">
                        <div class="check">
                            <img src="{{asset('assets/admin/img/check-1.png')}}" class="uncheck" alt="">
                            <img src="{{asset('assets/admin/img/check-2.png')}}" class="check" alt="">
                        </div>
                        <span>{{ 'billetera' }}</span>
                        <span class="ml-auto" >{{ \App\CentralLogics\Helpers::format_currency($balance) }} </span>
                    </div>
                </label>
            </div>
            @endif


            @foreach ($payment_methods as $item)

            <div class="col-md-6">
                <label class="payment-item">
                    <input type="radio" class="d-none" value="{{ $item['gateway'] }}" name="payment_gateway">
                    <div class="payment-item-inner">
                        <div class="check">
                            <img src="{{asset('assets/admin/img/check-1.png')}}" class="uncheck" alt="">
                            <img src="{{asset('assets/admin/img/check-2.png')}}" class="check" alt="">
                        </div>
                        <span>{{ $item['gateway_title'] }}</span>
                        <img class="ml-auto"
                            src="{{ \App\CentralLogics\Helpers::get_full_url('payment_modules/gateway_image',$item['gateway_image'],$item['storage'] ?? 'public') }}"
                        width="30" alt="">
                    </div>
                </label>
            </div>

            @endforeach

        </div>
        <div class="btn--container justify-content-end mt-20">
            <button type="reset" data-dismiss="modal" class="btn btn--reset">{{ 'Cancelar' }}</button>
            @if ( $store_business_model != 'commission' && $store_subscription?->package_id ==  $package->id)
            <button type="submit" class="btn btn--primary">{{ 'Renovar plan de suscripción' }}</button>
            @else
            <button type="submit" class="btn btn--primary">{{ 'Plan de cambio' }}</button>
            @endif
        </div>
    </div>
</form>
</div>
