@extends('layouts.admin.app')
@section('title','Suscripción a la tienda')
@section('subscriberList')
active
@endsection
@push('css_or_js')

@endpush

@section('content')

    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center py-2">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-start">
                        <img src="{{asset('assets/admin/img/store.png')}}" width="24" alt="img">
                        <div class="w-0 flex-grow pl-2">
                            <h1 class="page-header-title">{{ $store->name }} {{'Suscripción'}} &nbsp; &nbsp;
                                @if($store?->status == 0 &&  $store?->vendor?->status == 0)
                                <span class=" badge badge-pill badge-info">  &nbsp; {{ 'Aprobación pendiente' }}  &nbsp; </span>
                                @elseif($store?->store_sub_update_application?->status == 0)
                                <span class=" badge badge-pill badge-danger">  &nbsp; {{ 'Venció' }}  &nbsp; </span>
                                @elseif ($store?->store_sub_update_application?->is_canceled == 1)
                                <span class=" badge badge-pill badge-warning">  &nbsp; {{ 'Cancelado' }}  &nbsp; </span>
                                @elseif($store?->store_sub_update_application?->status == 1)
                                <span class=" badge badge-pill badge-success">  &nbsp; {{ 'Activo' }}  &nbsp; </span>
                                @endif
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="js-nav-scroller hs-nav-scroller-horizontal mb-4">
            <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
                <li class="nav-item">
                    <a href="" class="nav-link active">{{ 'Detalles de suscripción' }} </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.business-settings.subscriptionackage.subscriberTransactions',$store->id) }}" class="nav-link">{{ 'Actas' }}</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.business-settings.subscriptionackage.subscriberWalletTransactions',$store->id) }}" class="nav-link">{{ 'Reembolsos de suscripción' }}</a>
                </li>
            </ul>
        </div>
        <div class="card mb-20">
            <div class="card-header border-0 align-items-center">
                <h4 class="card-title align-items-center gap-2">
                    <span class="card-header-icon">
                        <img src="{{asset('assets/admin/img/store-3.png')}}" alt="">
                    </span>
                    <span class="text-title">{{ 'Información de la tienda' }}</span>
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="resturant--info-address">
                                    <div class="logo">
                                        <a href="{{route('admin.store.view', $store->id)}}">

                                            <img class="onerror-image"
                                            src="{{ $store->logo_full_url ?? asset('assets/admin/img/100x100/1.png') }}">
                                    </div>
                                        </a>
                                    <ul class="address-info list-unstyled list-unstyled-py-3 text-dark">
                                        <li>
                                            <h5 class="name">
                                                {{ $store->name }}
                                            </h5>
                                        </li>

                                        <li>
                                            <i class="tio-call-talking nav-icon"></i>
                                            <span class="pl-1">
                                                <a href="tel:{{ $store->phone }}">
                                                    {{ $store->phone }}
                                                </a>
                                            </span>
                                        </li>
                                        <li>
                                            <i class="tio-email nav-icon"></i>
                                            <span class="pl-1">
                                                <a href="mailto:{{ $store->email }}">
                                                    {{ $store->email }}
                                                </a>
                                            </span>
                                        </li>
                                        <li>
                                            <i class="tio-city nav-icon"></i>
                                            <span class="pl-1">
                                                {{ $store->address }}
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="resturant--info-address">
                                    <ul class="address-info list-unstyled list-unstyled-py-3 text-dark pl-0">
                                        <li>
                                            <h5 class="name">
                                                {{ 'Información del propietario' }}
                                            </h5>
                                        </li>
                                        <li>
                                            <h5 class="name text-title">
                                                {{ $store?->vendor?->f_name  .' '. $store?->vendor?->l_name}}
                                            </h5>
                                        </li>
                                        <li>
                                            <i class="tio-call-talking nav-icon"></i>
                                            <span class="pl-1">
                                               <a href="tel:{{ $store?->vendor?->phone}}">
                                                {{ $store?->vendor?->phone}}
                                               </a>
                                            </span>
                                        </li>
                                        <li>
                                            <i class="tio-email nav-icon"></i>
                                            <span class="pl-1">
                                            <a href="mailto: {{ $store?->vendor?->email}}"></a>
                                            {{ $store?->vendor?->email}}
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-20">
            <div class="card-header border-0 align-items-center">
                <h4 class="card-title align-items-center gap-2">
                    <span class="card-header-icon">
                        <img src="{{asset('assets/admin/img/billing.png')}}" alt="">
                    </span>
                    <span class="text-title">{{ 'Facturación' }}</span>
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6 col-lg-4">
                        <a class="__card-2 __bg-1 flex-row align-items-center gap-4" href="#">
                            <img src="{{asset('assets/admin/img/expiring.png')}}" alt="report/new" class="w-60px">
                            <div class="w-0 flex-grow-1 py-md-3">
                                <span class="text-body">{{ 'Fecha de vencimiento' }}</span>
                                <h4 class="title m-0">{{  \App\CentralLogics\Helpers::date_format($store?->store_sub_update_application?->expiry_date_parsed) }}</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="__card-2 __bg-8 flex-row align-items-center gap-4" href="#">
                            <img src="{{asset('assets/admin/img/total-bill.png')}}" alt="report/new" class="w-60px">
                            <div class="w-0 flex-grow-1 py-md-3">
                                <span class="text-body">{{ 'Factura Total' }}</span>
                                <h4 class="title m-0">{{  \App\CentralLogics\Helpers::format_currency($store?->store_sub_update_application?->package?->price * ($store?->store_sub_update_application?->total_package_renewed + 1) ) }}</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <a class="__card-2 __bg-4 flex-row align-items-center gap-4" href="#">
                            <img src="{{asset('assets/admin/img/number.png')}}" alt="report/new" class="w-60px">
                            <div class="w-0 flex-grow-1 py-md-3">
                                <span class="text-body">{{ 'Número de usos' }}</span>
                                <h4 class="title m-0">{{ $store?->store_sub_update_application?->total_package_renewed + 1 }}</h4>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header border-0 align-items-center">
                <h4 class="card-title align-items-center gap-2">
                    <span class="card-header-icon">
                        <img width="25" src="{{asset('assets/admin/img/subscription-plan/subscribed-user.png')}}" alt="">
                    </span>
                    <span>{{ 'Descripción general del paquete' }}</span>
                </h4>
            </div>
            <div class="card-body pt-0 px-0">
                <div class="__bg-F8F9FC-card __plan-details">
                    <div class="d-flex flex-wrap flex-md-nowrap justify-content-between __plan-details-top">
                        <div class="left">
                            <h3 class="name">{{ $store?->store_sub_update_application?->package?->package_name }}</h3>
                            <div class="font-medium text--title">{{ $store?->store_sub_update_application?->package?->text }}</div>
                        </div>
                        <h3 class="right">{{ \App\CentralLogics\Helpers::format_currency($store?->store_sub_update_application?->last_transcations?->price) }} /<small class="font-medium text--title">{{ $store?->store_sub_update_application?->last_transcations?->validity }} {{ 'Días' }}</small></h3>
                    </div>


                    <div class="check--grid-wrapper mt-3 max-w-850px">


                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{asset('assets/admin/img/subscription-plan/check.png')}}" alt="">
                                @if ( $store?->store_sub_update_application?->max_order == 'unlimited' )
                                <span class="form-check-label text-dark">{{ $store?->module->module_type == 'rental' && addon_published_status('Rental') ? 'viajes ilimitados' : 'pedidos ilimitados' }}</span>
                                @else
                                <span class="form-check-label text-dark"> {{ $store?->store_sub_update_application?->package?->max_order }} {{
                                   $store?->module->module_type == 'rental' && addon_published_status('Rental') ? 'viajes' :  'Órdenes' }} <small>({{ $store?->store_sub_update_application?->max_order }} {{ 'izquierda' }}) </small> </span>
                                @endif
                            </div>
                        </div>

                        @if ($store?->module->module_type != 'rental')

                        <div>
                            <div class="d-flex align-items-center gap-2">
                                @if ( $store?->store_sub_update_application?->pos == 1 )
                                <img src="{{asset('assets/admin/img/subscription-plan/check.png')}}" alt="">
                                @else
                                <img src="{{asset('assets/admin/img/subscription-plan/check-1.png')}}" alt="">
                                @endif
                                <span class="form-check-label text-dark">{{ 'punto de venta' }}</span>
                            </div>
                        </div>
                        @endif

                        <div>
                            <div class="d-flex align-items-center gap-2">
                                @if ( $store?->store_sub_update_application?->mobile_app == 1 )
                                <img src="{{asset('assets/admin/img/subscription-plan/check.png')}}" alt="">
                                @else
                                <img src="{{asset('assets/admin/img/subscription-plan/check-1.png')}}" alt="">
                                @endif
                                <span class="form-check-label text-dark">{{ 'Aplicación móvil' }}</span>
                            </div>
                        </div>
                        @if ($store?->module->module_type != 'rental')

                        <div>
                            <div class="d-flex align-items-center gap-2">
                                @if ( $store?->store_sub_update_application?->self_delivery == 1 )
                                <img src="{{asset('assets/admin/img/subscription-plan/check.png')}}" alt="">
                                @else
                                <img src="{{asset('assets/admin/img/subscription-plan/check-1.png')}}" alt="">
                                @endif
                                <span class="form-check-label text-dark">{{ 'autoentrega' }}</span>
                            </div>
                        </div>
                        @endif

                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{asset('assets/admin/img/subscription-plan/check.png')}}" alt="">
                                @if ( $store?->store_sub_update_application?->max_product == 'unlimited' )
                                <span class="form-check-label text-dark">{{ $store?->module->module_type == 'rental' && addon_published_status('Rental') ? 'Subida ilimitada' : 'Carga ilimitada de artículos'
                                    }}</span>
                                @else
                                <span class="form-check-label text-dark"> {{ $store?->store_sub_update_application?->max_product }} {{$store?->module->module_type == 'rental' && addon_published_status('Rental') ? 'Subir' :
                                    'Subir producto' }} <small>({{ $store?->store_sub_update_application?->max_product  - $store->items_count > 0 ? $store?->store_sub_update_application?->max_product  - $store->items_count : 0 }} {{ 'izquierda' }}) </small></span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <div class="d-flex align-items-center gap-2">
                                @if ( $store?->store_sub_update_application?->review == 1 )
                                <img src="{{asset('assets/admin/img/subscription-plan/check.png')}}" alt="">
                                @else
                                <img src="{{asset('assets/admin/img/subscription-plan/check-1.png')}}" alt="">
                                @endif
                                <span class="form-check-label text-dark">{{ 'revisar' }}</span>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex align-items-center gap-2">
                                @if ( $store?->store_sub_update_application?->chat == 1 )
                                <img src="{{asset('assets/admin/img/subscription-plan/check.png')}}" alt="">
                                @else
                                <img src="{{asset('assets/admin/img/subscription-plan/check-1.png')}}" alt="">
                                @endif
                                <span class="form-check-label text-dark">{{ 'charlar' }}</span>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="btn--container justify-content-end mt-20">
                    @if ( $store?->store_sub_update_application?->is_canceled == 0 && $store?->store_sub_update_application?->status == 1  )
                        <button type="button"  data-url="{{route('admin.business-settings.subscriptionackage.cancelSubscription',$store?->id)}}" data-message="{{'Si cancelas la suscripción, después'}} {{  Carbon\Carbon::now()->diffInDays($store?->store_sub_update_application?->expiry_date_parsed->format('Y-m-d'), false); }} {{ 'días el proveedor ya no podrá administrar el negocio antes de suscribirse a un nuevo plan.' }}"
                        class="btn btn--danger text-white status_change_alert">{{ 'Cancelar suscripción' }}</button>
                    @endif

                    <button type="button" data-toggle="modal" data-target="#plan-modal" class="btn btn--primary">{{ 'Cambiar / Renovar Plan de Suscripción' }}</button>

                </div>
            </div>
        </div>


        <div class="modal fade show" id="plan-modal">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header px-3 pt-3">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                    
                    <div class="modal-body px-4 pt-0">
                        <div>
                            <div class="text-center">
                                <h2 class="modal-title">{{ 'Cambiar plan de suscripción' }}</h2>
                            </div>
                            <div class="text-center text-14 mb-4 pb-3">
                               {{ '¡Renueva o cambia tu plan para obtener una mejor experiencia!' }}
                            </div>
                            <div class="plan-slider owl-theme owl-carousel owl-refresh">
                                @if (\App\CentralLogics\Helpers::commission_check())

                                <div class="__plan-item hover {{ $store->store_business_model == 'commission'  ? 'active' : ''}} ">
                                    <div class="inner-div">
                                        <div class="text-center">
                                            <h3 class="title">{{ 'Base de comisiones' }}</h3>
                                            <h2 class="price">{{  $store->comission > 0 ?  $store->comission :  $admin_commission }}%</h2>
                                        </div>
                                        <div class="py-5 mt-4">
                                            <div class="info-text text-center">
                                            {{ 'La tienda pagará' }} {{  $store->comission > 0 ?  $store->comission :  $admin_commission }}% {{ 'comisión a' }} {{ $business_name }} {{ 'de cada pedido. Obtendrá acceso a todas las funciones y opciones en el panel de la tienda, la aplicación y la interacción con el usuario.' }}
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            @if ($store->store_business_model == 'commission')
                                            <button type="button" class="btn btn--secondary">{{ 'Plan actual' }}</button>
                                            @else

                                            @php
                                            $cash_backs= \App\CentralLogics\Helpers::calculateSubscriptionRefundAmount(store:$store ,return_data:true);
                                            @endphp

                                            <button type="button" data-url="{{route('admin.business-settings.subscriptionackage.switchToCommission',$store->id)}}" data-message="{{'Quiere migrar a la comisión.'}} {{ data_get($cash_backs,'back_amount') > 0  ?  'obtendrás'.' '. \App\CentralLogics\Helpers::format_currency(data_get($cash_backs,'back_amount')) .' '.'a tu billetera por permanecer' .' '.data_get($cash_backs,'days').' '.'plan de suscripción de días' : '' }}"  class="btn btn--primary shift_to_commission">{{ 'Cambio en este plan' }}</button>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                                @endif

                                @forelse ($packages as $key => $package)
                                <div class="__plan-item hover {{ $store?->store_sub_update_application?->package_id == $package->id  && $store->store_business_model != 'commission'  ? 'active' : ''}}">
                                    <div class="inner-div">
                                        <div class="text-center">
                                            <h3 class="title">{{ $package->package_name }}</h3>
                                            <h2 class="price">{{ \App\CentralLogics\Helpers::format_currency($package->price)}}</h2>
                                            <div class="day-count">{{ $package->validity }} {{ 'días' }}</div>
                                        </div>
                                        <ul class="info">

                                            @if ($package->pos)
                                            <li>
                                                <i class="tio-checkmark-circle"></i> <span>  {{ 'punto de venta' }} </span>
                                            </li>
                                            @endif
                                            @if ($package->mobile_app)
                                            <li>
                                                <i class="tio-checkmark-circle"></i> <span>  {{ 'aplicación móvil' }} </span>
                                            </li>
                                            @endif
                                            @if ($package->chat)
                                            <li>
                                                <i class="tio-checkmark-circle"></i> <span>  {{ 'opciones de chat' }} </span>
                                            </li>
                                            @endif
                                            @if ($package->review)
                                            <li>
                                                <i class="tio-checkmark-circle"></i> <span>  {{ 'sección de revisión' }} </span>
                                            </li>
                                            @endif
                                            @if ($package->self_delivery)
                                            <li>
                                                <i class="tio-checkmark-circle"></i> <span>  {{ 'autoentrega' }} </span>
                                            </li>
                                            @endif
                                            @if ($package->max_order == 'unlimited')
                                            <li>
                                                <i class="tio-checkmark-circle"></i> <span>  {{ $store?->module->module_type == 'rental' && addon_published_status('Rental') ? 'viajes ilimitados' :  'Pedidos ilimitados' }} </span>
                                            </li>
                                            @else
                                            <li>
                                                <i class="tio-checkmark-circle"></i> <span>  {{ $package->max_order }} {{ $store?->module->module_type == 'rental' && addon_published_status('Rental') ? 'viajes' :  'Órdenes' }} </span>
                                            </li>
                                            @endif
                                            @if ($package->max_product == 'unlimited')
                                            <li>
                                                <i class="tio-checkmark-circle"></i> <span>  {{ 'Cargas ilimitadas' }} </span>
                                            </li>
                                            @else
                                            <li>
                                                <i class="tio-checkmark-circle"></i> <span>  {{ $package->max_product }} {{ 'cargas' }} </span>
                                            </li>
                                            @endif

                                        </ul>
                                        <div class="text-center">
                                            {{-- <button type="button" class="btn btn--primary" data-dismiss="modal" data-toggle="modal" data-target="#shift-modal">Shift in this plan</button> --}}
                                            @if ( $store?->store_business_model != 'commission'  && $store?->store_sub_update_application?->package_id == $package->id)
                                            <button data-id="{{ $package->id }}"  data-url="{{route('admin.business-settings.subscriptionackage.packageView',[$package->id,$store->id ])}}"
                                                data-target="#package_detail" id="package_detail" type="button" class="btn btn--warning text-white renew-btn package_detail">{{ 'Renovar' }}</button>
                                            @else
                                            <button data-id="{{ $package->id }}" data-url="{{route('admin.business-settings.subscriptionackage.packageView',[$package->id,$store->id ])}}"
                                                data-target="#package_detail" id="package_detail" type="button" class="btn btn--primary shift-btn package_detail">{{ 'Cambio en este plan' }}</button>
                                            @endif


                                        </div>
                                    </div>
                                </div>
                                @empty

                                @endforelse
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- subscription Plan Modal 2 -->
    <div class="modal fade __modal" id="subscription-renew-modal">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body px-4 pt-0">
                    <div class="data_package" id="data_package">
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="product_warning">
        <div class="modal-dialog modal-dialog-centered status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img src="{{asset('assets/admin/img/subscription-plan/package-status-disable.png')}}" class="mb-20">
                                <h5 class="modal-title" ></h5>
                            </div>
                            <div class="text-center">
                                <h3>{{ '¿Está seguro de que desea cambiarse a este plan?' }}</h3>
                                <p>{{ 'Estás a punto de bajar de categoría tu plan. Después de suscribirte a este plan, tu plan más antiguo' }} <span id="disable_item_count"></span> {{ 'Los artículos se desactivarán.' }} </p>
                            </div>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button  id="continue_btn" class="btn btn-outline-primary min-w-120" data-dismiss="modal" >
                                {{'Continuar'}}
                            </button>
                            <button  class="btn btn--primary min-w-120  shift_package"  id="back_to_planes" data-dismiss="modal" >{{'Volver'}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('script_2')
    <script>
        $('.plan-slider').owlCarousel({

            loop: false,
            margin: 30,
            responsiveClass:true,
            nav:false,
            dots:false,
            items: 3,
            center: true,
            // autoplay:true,
            // autoplayTimeout:2500,
            // autoplayHoverPause:true,
            startPosition: '{{ $index }}',

            responsive:{
                0: {
                    items:1.1,
                    margin: 10,
                },
                375: {
                    items:1.3,
                    margin: 30,
                },
                576: {
                    items:1.7,
                },
                768: {
                    items:2.2,
                    margin: 40,
                },
                992: {
                    items: 3,
                    margin: 40,
                },
                1200: {
                    items: 4,
                    margin: 40,
                }
            }
        })

        "use strict";
            $('.status_change_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            status_change_alert(url, message, event)
        })

        function status_change_alert(url, message, e) {
            e.preventDefault();
            Swal.fire({
                title: '{{ '¿Está seguro?' }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ 'No' }}',
                confirmButtonText: '{{ 'Sí' }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.post({
                        url: url,
                        data: {
                            id: '{{ $store->id }}',
                            subscription_id:'{{ $store?->store_sub_update_application?->id }}',
                        },
                        beforeSend: function () {
                            $('#loading').show()
                        },
                        success: function (data) {
                            toastr.success('{{ 'Canceló exitosamente la suscripción' }}!');
                        },
                        complete: function () {
                            $('#loading').hide();
                            location.reload();
                        }
                    });
                }
            })
        }

        $('.shift_to_commission').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            shift_to_commission(url, message, event)
        })

        function shift_to_commission(url, message, e) {
            e.preventDefault();
            Swal.fire({
                title: '{{ '¿Está seguro?' }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ 'No' }}',
                confirmButtonText: '{{ 'Sí' }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.post({
                        url: url,
                        data: {
                            id: '{{ $store->id }}',
                        },
                        beforeSend: function () {
                            $('#loading').show()
                        },
                        success: function (data) {
                            toastr.success('{{ 'Cambiado exitosamente a comisión' }}!');
                        },
                        complete: function () {
                            $('#loading').hide();
                            location.reload();
                        }
                    });
                }
            })
        }

        $(document).on('click', '.package_detail', function () {
            var url = $(this).attr('data-url');
            $.ajax({
                url: url,
                method: 'get',
                beforeSend: function() {
                            $('#loading').show();
                            $('#plan-modal').modal('hide')
                            },
                success: function(data){
                    $('#data_package').html(data.view);
                    if(data.disable_item_count !== null && data.disable_item_count > 0){
                        $('#product_warning').modal('show')
                        $('#disable_item_count').text(data.disable_item_count)
                    } else{
                        $('#subscription-renew-modal').modal('show')
                    }
                },
                complete: function() {
                        $('#loading').hide();
                    },
            });
        });

        $(document).on('click', '#continue_btn', function () {
            $('#subscription-renew-modal').modal('show')
        });

        $(document).on('click', '#back_to_planes', function () {
            $('#plan-modal').modal('show')
        });


    </script>
@endpush

