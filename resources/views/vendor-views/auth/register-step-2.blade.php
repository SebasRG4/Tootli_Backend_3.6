@extends('layouts.landing.app')
@section('title', translate('messages.vendor_registration'))
@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/toastr.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/view-pages/vendor-registration.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/landing/css/select2.min.css') }}" />
@endpush
@section('content')
    <section class="m-0 py-5">
        <div class="container">
            <!-- Page Header -->
            <div class="section-header">
                <h2 class="title mb-2">{{ 'Restaurante/Tienda' }} <span
                        class="text--base">{{ 'Registro' }}</span></h2>
            </div>

            <!-- End Page Header -->

            <!-- Stepper -->
            <div class="stepper">
                <div class="stepper-item active">
                    <div class="step-name">{{ 'Información General' }}</div>
                </div>
                <div class="stepper-item active">
                    <div class="step-name">{{ 'Plan de Negocios' }}</div>
                </div>
                <div class="stepper-item">
                    <div class="step-name">{{ 'Completado' }}</div>
                </div>
            </div>
            <!-- Stepper -->


            <form action="{{ route('restaurant.business_plan') }}" class="reg-form js-validate" method="post">
                @csrf
                <input type="hidden" name="store_id" value="{{ $store_id }}">
                <div class="card __card mb-3">
                    <div class="card-header border-0">
                        <h5 class="card-title text-center">
                            {{ 'Elige tu Plan de Negocios' }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            @if (\App\CentralLogics\Helpers::commission_check())
                                <div class="col-sm-6">
                                    <label class="plan-check-item pb-3 pb-sm-0">
                                        <input id="commission-base" type="radio" name="business_plan" value="commission-base" class="d-none"
                                            checked>
                                        <div class="plan-check-item-inner">
                                            <div class="d-flex gap-3 justify-content-between align-items-center mb-10">
                                                <h5 class="mb-0">{{ 'Basado en Comisión' }}</h5>
                                                <span class="checkmark">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                        <path
                                                            d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <p>
                                                {{ 'El negocio pagará' }} {{ $admin_commission }}%
                                                {{ 'de comisión a' }} {{ $business_name }}
                                                {{ 'por cada pedido. Tendrás acceso a todas las funciones del panel, la app y podrás interactuar con los clientes.' }}
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            @endif
                            <div class="col-sm-6">
                                <label class="plan-check-item">
                                    <input id="subscription-base" type="radio" name="business_plan" value="subscription-base" class="d-none">
                                    <div class="plan-check-item-inner">
                                        <div class="d-flex gap-3 justify-content-between align-items-center mb-10">
                                            <h5 class="mb-0">{{ 'Basado en Suscripción' }}</h5>
                                            <span class="checkmark">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                    <path
                                                        d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0" />
                                                </svg>
                                            </span>
                                        </div>
                                        <p>
                                            {{ 'Opera tu negocio comprando paquetes de suscripción. Tendrás acceso a las funciones del panel y app según tu paquete.' }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div id="subscription-plan">
                            <br>
                            <div class="card-header px-0 m-0 border-0">
                                <h5 class="card-title text-center">
                                    {{ 'Elige un Paquete de Suscripción' }}
                                </h5>
                            </div>
                            <div class="plan-slider owl-theme owl-carousel owl-refresh">

                                @forelse ($packages as $key=> $package)
                                    <label
                                        class="__plan-item {{ (count($packages) > 4 && $key == 2) || (count($packages) < 5 && $key == 1) ? 'active' : '' }} ">
                                        <input type="radio" name="package_id" value="{{ $package->id }}" class="d-none">
                                        <div class="inner-div">
                                            <div class="text-center">

                                                <h3 class="title">{{ $package->package_name }}</h3>
                                                <h2 class="price">
                                                    {{ \App\CentralLogics\Helpers::format_currency($package->price) }}</h2>
                                                <div class="day-count">{{ $package->validity }}
                                                    {{ 'días' }}</div>
                                            </div>
                                            <ul class="info">

                                                @if ($package->pos)
                                                    <li>
                                                        <img src="{{ asset('assets/landing/img/check-1.svg') }}"
                                                            class="check" alt="">
                                                        <img src="{{ asset('assets/landing/img/check-2.svg') }}"
                                                            class="check-white" alt=""> <span>
                                                            {{ 'Punto de Venta (POS)' }} </span>
                                                    </li>
                                                @endif
                                                @if ($package->mobile_app)
                                                    <li>
                                                        <img src="{{ asset('assets/landing/img/check-1.svg') }}"
                                                            class="check" alt="">
                                                        <img src="{{ asset('assets/landing/img/check-2.svg') }}"
                                                            class="check-white" alt=""> <span>
                                                            {{ 'App Móvil' }} </span>
                                                    </li>
                                                @endif
                                                @if ($package->chat)
                                                    <li>
                                                        <img src="{{ asset('assets/landing/img/check-1.svg') }}"
                                                            class="check" alt="">
                                                        <img src="{{ asset('assets/landing/img/check-2.svg') }}"
                                                            class="check-white" alt=""> <span>
                                                            {{ 'Opciones de Chat' }} </span>
                                                    </li>
                                                @endif
                                                @if ($package->review)
                                                    <li>
                                                        <img src="{{ asset('assets/landing/img/check-1.svg') }}"
                                                            class="check" alt="">
                                                        <img src="{{ asset('assets/landing/img/check-2.svg') }}"
                                                            class="check-white" alt=""> <span>
                                                            {{ 'Sección de Reseñas' }} </span>
                                                    </li>
                                                @endif
                                                @if ($package->self_delivery)
                                                    <li>
                                                        <img src="{{ asset('assets/landing/img/check-1.svg') }}"
                                                            class="check" alt="">
                                                        <img src="{{ asset('assets/landing/img/check-2.svg') }}"
                                                            class="check-white" alt=""> <span>
                                                            {{ 'Repartidores Propios' }} </span>
                                                    </li>
                                                @endif
                                                @if ($package->max_order == 'unlimited')
                                                    <li>
                                                        <img src="{{ asset('assets/landing/img/check-1.svg') }}"
                                                            class="check" alt="">
                                                        <img src="{{ asset('assets/landing/img/check-2.svg') }}"
                                                            class="check-white" alt=""> <span>
                                                            {{ isset($module) && $module == 'rental' ?  'Viajes Ilimitados' :'Pedidos Ilimitados' }} </span>
                                                    </li>
                                                @else
                                                    <li>
                                                        <img src="{{ asset('assets/landing/img/check-1.svg') }}"
                                                            class="check" alt="">
                                                        <img src="{{ asset('assets/landing/img/check-2.svg') }}"
                                                            class="check-white" alt=""> <span>
                                                            {{ $package->max_order }} {{ isset($module) && $module == 'rental' ?  'Viajes' : 'Pedidos' }}
                                                        </span>
                                                    </li>
                                                @endif
                                                @if ($package->max_product == 'unlimited')
                                                    <li>
                                                        <img src="{{ asset('assets/landing/img/check-1.svg') }}"
                                                            class="check" alt="">
                                                        <img src="{{ asset('assets/landing/img/check-2.svg') }}"
                                                            class="check-white" alt=""> <span>
                                                            {{ 'Subidas Ilimitadas' }} </span>
                                                    </li>
                                                @else
                                                    <li>
                                                        <img src="{{ asset('assets/landing/img/check-1.svg') }}"
                                                            class="check" alt="">
                                                        <img src="{{ asset('assets/landing/img/check-2.svg') }}"
                                                            class="check-white" alt=""> <span>
                                                            {{ $package->max_product }}
                                                            {{ 'Subidas' }} </span>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </label>

                                @empty
                                @endforelse

                            </div>
                        </div>
                        <div class="text-end pt-5 d-flex flex-wrap justify-content-end gap-3">
                            {{-- <button type="button" class="cmn--btn btn--secondary shadow-none rounded-md border-0 outline-0">{{ 'Atrás'
                                }}</button> --}}
                            <button type="submit"
                                class="cmn--btn rounded-md border-0 outline-0">{{ 'Siguiente' }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

@endsection
@push('script_2')
    <script>
        $('.plan-slider').owlCarousel({
            loop: false,
            margin: 30,
            responsiveClass: true,
            nav: false,
            dots: false,
            items: 3,
            // center: true,
            // autoplay:true,
            // autoplayTimeout:2500,
            // autoplayHoverPause:true,
            startPosition: 1,

            responsive: {
                0: {
                    items: 1.1,
                    margin: 10,
                },
                375: {
                    items: 1.3,
                    margin: 30,
                },
                576: {
                    items: 1.7,
                },
                768: {
                    items: 2.2,
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
    </script>

    <script>
        $(window).on('load', function() {
            $('input[name="business_plan"]').each(function() {
                if ($(this).is(':checked')) {
                    if ($(this).val() == 'subscription-base') {
                        $('#subscription-plan').show()
                    } else {
                        $('#subscription-plan').hide()
                    }
                }
            })
            $('input[name="package_id"]').each(function() {
                if ($(this).is(':checked')) {
                    $(this).closest('.__plan-item').addClass('active')
                }
            })
        })
        $('input[name="business_plan"]').on('change', function() {
            if ($(this).val() == 'subscription-base') {
                $('#subscription-plan').slideDown()
            } else {
                $('#subscription-plan').slideUp()
            }
        })
        $('input[name="package_id"]').on('change', function() {
            $('input[name="package_id"]').each(function() {
                $(this).closest('.__plan-item').removeClass('active')
            })
            $(this).closest('.__plan-item').addClass('active')
        })
    </script>
@endpush
