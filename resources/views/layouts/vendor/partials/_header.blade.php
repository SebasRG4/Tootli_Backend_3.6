<div id="headerMain" class="d-none">
    <header id="header"
            class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered">
        <div class="navbar-nav-wrap">
            <div class="navbar-nav-wrap-content-left  d-xl-none">
                <!-- Navbar Vertical Toggle -->
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-3">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                       data-placement="right" title="Collapse"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                       data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                       data-toggle="tooltip" data-placement="right" title="Expand"></i>
                </button>
                <!-- End Navbar Vertical Toggle -->
            </div>

            <!-- Secondary Content -->
            <div class="navbar-nav-wrap-content-right">
                <!-- Navbar -->
                <ul class="navbar-nav align-items-center flex-row">
                    <li class="nav-item max-sm-m-0 w-xxl-200px ml-auto mr-2 flex-grow-0">
                        <button type="button" id="modalOpener" class="title-color bg--secondary border-0 rounded justify-content-between w-100 align-items-center py-2 px-2 px-md-3 d-flex gap-1" data-toggle="modal" data-target="#staticBackdrop">
                            <div class="align-items-center d-flex flex-grow-1 gap-1 justify-content-between">
                                <span class="align-items-center d-none d-xxl-flex gap-2 text-muted">{{'Buscar o'}}

                                    <span class="bg-E7E6E8 border ctrlplusk d-md-block d-none font-bold fs-12 fw-bold lh-1 ms-1 px-1 rounded text-muted">Ctrl+K</span>

                                </span>
                                <img width="14" src="{{asset('assets/admin/img/new-img/search.svg')}}" class="svg" alt="">
                            </div>
                        </button>
                    </li>
                    <li class="nav-item ml-3 max-sm-m-0">
                        <div class="hs-unfold">
                            <div>
                                @php($local = session()->has('vendor_local')?session('vendor_local'):null)
                                @php($lang = \App\Models\BusinessSetting::where('key', 'system_language')->first())
                                @if ($lang)
                                <div
                                    class="topbar-text dropdown disable-autohide text-capitalize d-flex">
                                    <a class="topbar-link dropdown-toggle d-flex align-items-center title-color"
                                    href="#" data-toggle="dropdown">
                                            @foreach(json_decode($lang['value'],true) as $data)
                                                @if($data['code']==$local)
                                                    <i class="tio-globe"></i> {{$data['code']}}

                                                @elseif(!$local &&  $data['default'] == true)
                                                    <i class="tio-globe"></i> {{$data['code']}}
                                                @endif
                                            @endforeach
                                    </a>
                                    <ul class="dropdown-menu lang-menu">
                                        @foreach(json_decode($lang['value'],true) as $key =>$data)
                                            @if($data['status']==1)
                                                <li>
                                                    <a class="dropdown-item py-1"
                                                        href="{{route('vendor.lang',[$data['code']])}}">
                                                        <span class="text-capitalize">{{$data['code']}}</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </div>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block mr-4">
                        <!-- Notification -->
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-icon btn-soft-secondary rounded-circle"
                               href="{{route('vendor.message.list')}}">
                                <i class="tio-messages-outlined"></i>
                                @php($message=\App\Models\Conversation::whereUser(\App\CentralLogics\Helpers::get_loggedin_user()->id)->where('unread_message_count','>','0')->count())
                                @if($message!=0)
                                    <span class="btn-status btn-sm-status btn-status-danger"></span>
                                @endif
                            </a>
                        </div>
                        <!-- End Notification -->
                    </li>



                    <li class="nav-item">
                        <!-- Account -->
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper" href="javascript:;"
                               data-hs-unfold-options='{
                                     "target": "#accountNavbarDropdown",
                                     "type": "css-animation"
                                   }'>
                                <div class="cmn--media right-dropdown-icon d-flex align-items-center">
                                    <div class="media-body pl-0 pr-2">
                                        <span class="card-title h5 text-right">
                                            {{\App\CentralLogics\Helpers::get_loggedin_user()->f_name}}
                                            {{\App\CentralLogics\Helpers::get_loggedin_user()->l_name}}
                                        </span>
                                        <span class="card-text">{{\App\CentralLogics\Helpers::get_loggedin_user()->email}}</span>
                                    </div>
                                    <div class="avatar avatar-sm avatar-circle">
                                        <img class="avatar-img  onerror-image aspect-1-1"  data-onerror-image="{{asset('assets/admin/img/160x160/img1.jpg')}}"
                                        src="{{ \App\CentralLogics\Helpers::get_loggedin_user()->toArray()['image_full_url'] }}"
                                            alt="Image Description">
                                        <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                    </div>
                                </div>
                            </a>

                            <div id="accountNavbarDropdown"
                                 class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account min--240">
                                <div class="dropdown-item-text">
                                    <div class="media align-items-center">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img  onerror-image aspect-1-1 "  data-onerror-image="{{asset('assets/admin/img/160x160/img1.jpg')}}"
                                            src="{{ \App\CentralLogics\Helpers::get_loggedin_user()->toArray()['image_full_url'] }}"
                                                 alt="Owner image">
                                        </div>
                                        <div class="media-body">
                                            <span class="card-title h5">{{\App\CentralLogics\Helpers::get_loggedin_user()->f_name}} {{\App\CentralLogics\Helpers::get_loggedin_user()->l_name}}</span>
                                            <span class="card-text">{{\App\CentralLogics\Helpers::get_loggedin_user()->email}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="{{route('vendor.profile.view')}}">
                                    <span class="text-truncate pr-2" title="Settings">{{'Configuración'}}</span>
                                </a>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item log-out" >
                                    <span class="text-truncate pr-2 log-out" title="Sign out">{{'desconectar'}}</span>
                                </a>
                            </div>
                        </div>
                        <!-- End Account -->
                    </li>
                </ul>
                <!-- End Navbar -->
            </div>
            <!-- End Secondary Content -->
        </div>
    </header>
</div>
<div class="modal fade removeSlideDown" id="staticBackdrop" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered max-w-520">
        <div class="modal-content modal-content__search border-0">
            <div class="d-flex flex-column gap-3 rounded-20 bg-card py-2 px-3">
                <div class="d-flex gap-2 align-items-center position-relative">
                    <form class="flex-grow-1" id="searchForm" action="{{ route('vendor.search.routing') }}">
                        @csrf
                        <div class="d-flex align-items-center global-search-container">
                            <input autocomplete="off" class="form-control flex-grow-1 rounded-10 search-input" id="searchInput" maxlength="255" name="search" type="search" placeholder="{{ 'Buscar por palabra clave' }}" aria-label="Search" autofocus>
                        </div>
                    </form>
                    <div class="position-absolute right-0 pr-2">
                        <button class="border-0 rounded px-2 py-1" type="button" data-dismiss="modal">{{ 'ESC' }}</button>
                    </div>
                </div>

                <div class="min-h-350">
                    <div class="search-result" id="searchResults">
                        <div class="text-center text-muted py-5">{{'Parece que aún no has buscado.'}}.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="headerFluid" class="d-none"></div>
<div id="headerDouble" class="d-none"></div>
<?php
$wallet = \App\Models\StoreWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
$Payable_Balance = $wallet?->collected_cash  > 0 ? 1: 0;

$cash_in_hand_overflow=  \App\Models\BusinessSetting::where('key' ,'cash_in_hand_overflow_store')->first()?->value;
$cash_in_hand_overflow_store_amount =  \App\Models\BusinessSetting::where('key' ,'cash_in_hand_overflow_store_amount')->first()?->value;
$val= (string) ($cash_in_hand_overflow_store_amount - (($cash_in_hand_overflow_store_amount * 10)/100));

    $store_data=\App\CentralLogics\Helpers::get_store_data();
    $store_data->load(['translations','orders','storage','storeConfig','module']);
    // ->loadCount([
    //     'orders as total_orders',
    //     'orders as canceled_orders' => function ($query) {
    //         $query->where('order_status', 'canceled');
    //     }
    // ]);
    $subscription_deadline_warning_days =  \App\Models\BusinessSetting::where('key','subscription_deadline_warning_days')->first()?->value ?? 7;
    $subscription_deadline_warning_message =  \App\Models\BusinessSetting::where('key','subscription_deadline_warning_message')->first()?->value ?? null;


        // if ($store_data->canceled_orders > 0 && $store_data?->module?->module_type == 'rental' && addon_published_status('Rental') ) {
        //     $store_data['cancellation_rate']= (($store_data->canceled_orders / $store_data->total_orders) * 100) ;
        // }

?>

{{-- @if (data_get($store_data,'cancellation_rate')  >= \App\CentralLogics\Helpers::get_business_settings('order_cancelation_rate_warning_limit') && data_get($store_data,'cancellation_rate')  <= \App\CentralLogics\Helpers::get_business_settings('order_cancelation_rate_block_limit') && $store_data?->module?->module_type == 'rental' && addon_published_status('Rental') )

    <div class="alert __alert-2 alert-warning m-0 py-1 px-2" role="alert">
        <img class="rounded mr-1"  width="25" src="{{ asset('assets/admin/img/header_warning.png') }}" alt="">
        <div class="cont">
            <h4 class="m-0">{{ '¡Atenciones!' }} </h4>
            {{ 'Su tasa de cancelación es cada vez mayor. Si la tasa de cancelación alcanza el 20%, su cuenta se suspenderá automáticamente.' }}
        </div>
    </div>
    @elseif(data_get($store_data,'cancellation_rate')  >= \App\CentralLogics\Helpers::get_business_settings('order_cancelation_rate_block_limit') && $store_data?->module?->module_type == 'rental' && addon_published_status('Rental') )


    <div class="alert __alert-2 alert-warning m-0 py-1 px-2" role="alert">
        <img class="rounded mr-1"  width="25" src="{{ asset('assets/admin/img/header_warning.png') }}" alt="">
        <div class="cont">
            <h4 class="m-0">{{ 'Atención, por favor' }} </h4>
            {{ 'Su cuenta ha sido suspendida debido a una alta tasa de cancelación. Contactar con el administrador.' }}
        </div>
    </div>
@endif --}}


@if ($Payable_Balance == 1 &&  $cash_in_hand_overflow &&  $wallet?->balance < 0 &&  $val <=  abs($wallet?->collected_cash)  )
    <div class="alert __alert-2 alert-warning m-0 py-1 px-2" role="alert">
        <img class="rounded mr-1"  width="25" src="{{ asset('assets/admin/img/header_warning.png') }}" alt="">
        <div class="cont">
            <h4 class="m-0">{{ 'Atención, por favor' }} </h4>
            {{ 'El monto de efectivo en mano está a punto de exceder el límite. Por favor pague el monto adeudado. Si el límite excede, su cuenta será suspendida.' }}
        </div>
    </div>
@endif

@if ($Payable_Balance == 1 &&  $cash_in_hand_overflow &&  $wallet?->balance < 0 &&  $cash_in_hand_overflow_store_amount < $wallet?->collected_cash)
    <div class="alert __alert-2 alert-warning m-0 py-1 px-2" role="alert">
        <img class="mr-1"  width="25" src="{{ asset('assets/admin/img/header_warning.png') }}" alt="">
        <div class="cont">
            <h4 class="m-0">{{ 'Atención, por favor' }} </h4>{{ 'Se ha superado el límite de cantidad de efectivo en mano. Su cuenta ahora está suspendida. Pague el monto adeudado para recibir nuevamente solicitudes de nuevos pedidos.' }}<a href="{{ route('vendor.wallet.index') }}" class="alert-link"> &nbsp; {{ 'pagar lo debido' }}</a>
        </div>
    </div>
@endif









@if ( !in_array($store_data->store_business_model, ['none','commission']) && !Request::is('vendor-panel/subscription/*') )

        <?php
            $pers=10;
            if($store_data?->store_sub){
                $validity=$store_data?->store_sub?->validity;
                    $remaining_days= Carbon\Carbon::now()->diffInDays($store_data?->store_sub?->expiry_date_parsed->format('Y-m-d'), false);
                    $pers=  $validity-$remaining_days > 0  ? (($validity-$remaining_days) /$validity) *100 : 1;
                    $pers=  439.6 * $pers / 100;
            }
        ?>
        @if ($store_data?->store_sub?->is_trial == 0 && $store_data?->store_sub?->expiry_date_parsed && $store_data?->store_sub->expiry_date_parsed->subDays((int) $subscription_deadline_warning_days)->isBefore(now()) && Request::is('vendor-panel'))

                <!--Always in header Renew -->
                <div class="renew-badge mb-20" id="renew-badge">
                    <div class="renew-content d-flex align-items-center">

                        <img src="{{asset('assets/admin/img/timer.svg')}}" alt="">
                        <div class="txt">
                            {{ $subscription_deadline_warning_message != null ?  $subscription_deadline_warning_message : 'Tu suscripción finalizará pronto. Por favor renueve para continuar con el acceso' }}
                        </div>
                    </div>
                    <div>
                        <a href="{{route('vendor.subscriptionackage.subscriberDetail',['renew_now' => true])}}" class="btn btn--danger">{{ 'Renovar' }}</a>
                    </div>
                </div>



        @elseif ( Session::get('subscription_renew_close_btn') !== true && $store_data?->store_sub?->is_trial == 0  && $store_data?->store_sub?->expiry_date_parsed && $store_data?->store_sub->expiry_date_parsed->subDays( (int) $subscription_deadline_warning_days)->isBefore(now()) && !Request::is('vendor-panel'))


                <div class="renew-badge mb-20 hide-warning" id="renew-badge">
                    <div class="renew-content d-flex align-items-center">

                        <img src="{{asset('assets/admin/img/timer.svg')}}" alt="">
                        <div class="txt">
                            {{ $subscription_deadline_warning_message != null ?  $subscription_deadline_warning_message : 'Tu suscripción finalizará pronto. Por favor renueve para continuar con el acceso' }}
                        </div>
                    </div>
                    <div>
                        @if ($store_data?->store_sub?->is_canceled == 1)
                        <a href="{{route('vendor.subscriptionackage.subscriberDetail',['open_plans' => true])}}" class="btn btn--danger">{{ 'Cambiar suscripción' }}</a>
                        @else

                        <a href="{{route('vendor.subscriptionackage.subscriberDetail',['renew_now' => true])}}" class="btn btn--danger">{{ 'Renovar' }}</a>

                        @endif
                        <button  data-id="subscription_renew_close_btn" id="hide-warning"  class="btn btn-sm btn-primary add-to-session" >{{ 'recuérdamelo más tarde' }}</button>
                    </div>
                </div>
                <!-- Renew -->


        @endif
        @if ( Session::get('subscription_free_trial_close_btn') !== true && $store_data?->store_sub?->status == 1 && $store_data?->store_sub?->is_trial == 1 && $store_data?->store_sub?->is_canceled == 0)

        <div class="free-trial trial success-bg">
            <div class="inner-div">
                <div class="left">
                    <img src="{{asset('assets/admin/img/icon-puck.svg')}}" alt="">
                    <div class="left-content">
                        <h6>{{ 'Obtenga la mejor experiencia de negocio de servicios bajo demanda' }}</h6>
                        <div>{{ 'Gestione su negocio bajo demanda con la plataforma más popular' }}</div>
                    </div>
                </div>
                <div class="right">
                    <a href="#" class="btn btn-2">
                        <span class="circle-progress-container">
                            <svg width="40" viewBox="0 0 160 160">
                                <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff20" stroke-width="12px"></circle>
                                <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff" stroke-width="12px" stroke-dasharray="439.6px" stroke-dashoffset="{{ $pers }}px"></circle>
                            </svg>
                            {{ Carbon\Carbon::now()->diffInDays($store_data?->store_sub?->expiry_date_parsed->format('Y-m-d'), false) }}
                        </span>
                        {{'Días restantes en la prueba gratuita'}}
                    </a>
                    <a href="{{route('vendor.subscriptionackage.subscriberDetail' ,['open_plans' => true])}}" class="btn btn-light">{{ 'Elija el plan de suscripción' }} <i class="tio-arrow-forward"></i></a>
                </div>

                <button type="button" data-id="subscription_free_trial_close_btn" class="trial-close add-to-session ">
                    <i class="tio-clear-circle"></i>
                </button>
            </div>
        </div>
        @elseif ($store_data?->store_sub == null && $store_data?->store_sub_update_application?->is_trial == 1)



        <div class="modal fade show trial-ended-modal" id="free-trial-modal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="trial-ended-modal-wrapper">
                            <div class="trial-ended-modal-content align-self-center">
                                <h3 class="title">{{ 'Su prueba gratuita ha finalizado' }}</h3>
                                <p class="mb-4">
                                    {{ 'Compre un plan de suscripción o comuníquese con el administrador para liquidar el pago y desbloquear el acceso al servicio.' }}
                                </p>
                                <a href="{{route('vendor.subscriptionackage.subscriberDetail' ,['open_plans' => true])}}" class="btn btn--primary">{{ 'Elija el plan de suscripción' }} <i class="tio-arrow-forward"></i></a>
                                <div class="blocked-subscription mt-5">
                                    <img src="{{asset('assets/admin/img/WarningOctagon.svg')}}" alt="">
                                    <span>{{ 'Todo el acceso al servicio ha sido bloqueado debido a que no hay suscripción activa.' }}</span>
                                </div>
                            </div>
                            <div class="trial-ended-modal-img d-none d-md-block">
                                <img src="{{asset('assets/admin/img/trial-ended-bg.png')}}" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="free-trial trial danger-bg">
            <div class="inner-div">
                <div class="left">
                    <img src="{{asset('assets/admin/img/timer-2.svg')}}" alt="">
                    <div class="left-content">
                        <h6>{{ 'La prueba gratuita ha finalizado' }}</h6>
                        <div>{{ 'Obtén un plan de suscripción para continuar con tu negocio' }}</div>
                    </div>
                </div>
                <div class="right">
                    <a href="{{route('vendor.subscriptionackage.subscriberDetail' ,['open_plans' => true])}}" class="btn btn-light">{{ 'Elija el plan de suscripción' }} <i class="tio-arrow-forward"></i></a>
                </div>
            </div>
        </div>
        @elseif ( Session::get('subscription_cancel_close_btn') !== true &&  $store_data?->store_sub  && $store_data?->store_sub?->is_canceled == 1)
        <div class="free-trial trial danger-bg">
            <div class="inner-div">
                <div class="left">
                    <img src="{{asset('assets/admin/img/timer-2.svg')}}" alt="">
                    <div class="left-content">
                        <h6>{{ 'Su suscripción ha sido cancelada por' }} {{ $store_data?->store_sub?->canceled_by == 'admin' ? translate($store_data?->store_sub?->canceled_by) : 'Tú mismo' }}</h6>
                        <div>{{ 'No puedes consumir tu suscripción después' }} {{ \App\CentralLogics\Helpers::date_format($store_data?->store_sub?->expiry_date_parsed) }}</div>
                    </div>
                </div>
                <div class="right">
                    <a href="" class="btn btn-2">
                        <span class="circle-progress-container">
                            <svg width="40" viewBox="0 0 160 160">
                                <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff20" stroke-width="12px"></circle>
                                <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff" stroke-width="12px" stroke-dasharray="439.6px" stroke-dashoffset="{{ $pers }}px"></circle>
                            </svg>
                            {{ Carbon\Carbon::now()->diffInDays($store_data?->store_sub?->expiry_date_parsed->format('Y-m-d'), false) }}
                        </span>
                        {{'Días restantes en esta suscripción'}}
                    </a>
                    <a href="{{route('vendor.subscriptionackage.subscriberDetail' ,['open_plans' => true])}}" class="btn btn-light">{{ 'Cambiar plan de suscripción' }} <i class="tio-arrow-forward"></i></a>
                </div>

                <button type="button" data-id="subscription_cancel_close_btn" class="trial-close add-to-session ">
                    <i class="tio-clear-circle"></i>
                </button>
            </div>
        </div>
        @elseif ( Session::get('subscription_plan_update_close_btn') !== true &&  $store_data?->store_sub  && $store_data?->store_sub?->package?->status != 1)
        <div class="free-trial trial danger-bg">
            <div class="inner-div">
                <div class="left">
                    <img src="{{asset('assets/admin/img/timer-2.svg')}}" alt="">
                    <div class="left-content">
                        <h6>{{ 'El administrador ha desactivado su paquete de suscripción actual.' }} </h6>
                        <div>{{ 'No puedes renovar este paquete después' }} {{ \App\CentralLogics\Helpers::date_format($store_data?->store_sub?->expiry_date_parsed) }}. {{ 'Para continuar con su suscripción, elija otro paquete.'  }}</div>
                    </div>
                </div>
                <div class="right">
                    <a href="" class="btn btn-2">
                        <span class="circle-progress-container">
                            <svg width="40" viewBox="0 0 160 160">
                                <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff20" stroke-width="12px"></circle>
                                <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff" stroke-width="12px" stroke-dasharray="439.6px" stroke-dashoffset="{{ $pers }}px"></circle>
                            </svg>
                            {{ Carbon\Carbon::now()->diffInDays($store_data?->store_sub?->expiry_date_parsed->format('Y-m-d'), false) }}
                        </span>
                        {{'Días restantes en esta suscripción'}}
                    </a>
                    <a href="{{route('vendor.subscriptionackage.subscriberDetail' ,['open_plans' => true])}}" class="btn btn-light">{{ 'Cambiar plan de suscripción' }} <i class="tio-arrow-forward"></i></a>
                </div>

                <button type="button" data-id="subscription_plan_update_close_btn" class="trial-close add-to-session ">
                    <i class="tio-clear-circle"></i>
                </button>
            </div>
        </div>

        @elseif ($store_data?->store_sub == null)
        <div class="free-trial trial danger-bg">
            <div class="inner-div">
                <div class="left">
                    <img src="{{asset('assets/admin/img/timer-2.svg')}}" alt="">
                    <div class="left-content">
                        <h6>{{ 'Su suscripción ha caducado el' }} {{  \App\CentralLogics\Helpers::date_format($store_data?->store_sub_update_application?->expiry_date_parsed) }} </h6>
                        <div>{{ 'Compre un plan de suscripción o comuníquese con el administrador para liquidar el pago y desbloquear el acceso al servicio.' }} </div>
                    </div>
                </div>
                <div class="right">

                    <a href="{{route('vendor.subscriptionackage.subscriberDetail' ,['open_plans' => true])}}" class="btn btn-light">{{ 'Cambiar/renovar plan de suscripción' }} <i class="tio-arrow-forward"></i></a>
                </div>
            </div>
        </div>

        @endif

@endif



<script>
    document.addEventListener('DOMContentLoaded', function () {
                $(document).on('click', '.log-out', function () {
                Swal.fire({
                title: '{{ '¿Quieres cerrar sesión?' }}',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonColor: '#FC6A57',
                cancelButtonColor: '#363636',
                confirmButtonText: `{{ 'Sí'}}`,
                cancelButtonText: `{{ 'Cancelar'}}`,
                }).then((result) => {
                if (result.value) {
                location.href='{{route('logout')}}';
                }
            })
        });
                $(document).on('click', '.add-to-session', function () {
                    var session_data = $(this).data("id");
                    $.ajax({
                        url: '{{ route('vendor.subscriptionackage.addToSession') }}',
                        method: 'POST',
                        data: {
                            value: session_data,
                            _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {

                            }
                        });
                });
                $(document).on('click', '#hide-warning', function () {
                $('.hide-warning').hide();
                });


    });


</script>
