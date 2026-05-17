@extends('layouts.landing.app')
@section('title', translate('messages.vendor_registration'))
@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/toastr.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/view-pages/vendor-registration.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/landing/css/select2.min.css') }}"/>
@endpush
@section('content')
    <section class="m-0 py-5">
        <div class="container">
            <!-- Page Header -->
            <div class="section-header">
                <h2 class="title mb-2">{{ 'Restaurante/Tienda' }} <span class="text--base">{{'Registro'}}</span></h2>
            </div>
            <!-- End Page Header -->

            <!-- Stepper -->
                <div class="stepper">
                    <div style class="stepper-item active">
                        <div class="step-name">{{ 'Información General' }}</div>
                    </div>
                    <div class="stepper-item active">
                        <div class="step-name">{{ 'Plan de Negocios' }}</div>
                    </div>
                    <div  class="stepper-item active">
                        <div class="step-name  {{  isset($payment_status) && $payment_status == 'fail' ? 'text-danger' : '' }}">{{ 'Completado' }}</div>
                    </div>
                </div>
            <!-- Stepper -->


            <div class="reg-form js-validate">
                <div class="card __card mb-3">
                    <div class="card-header border-0 pb-0 text-center pt-5">
                            @if ( isset($payment_status) && $payment_status == 'fail')
                            <img src="{{asset('assets/landing/img/Failed.gif')}}" width="40" alt="" class="mb-4">
                            <h5 class="card-title text-center">
                                {{ '¡Transacción Fallida!' }}
                            </h5>
                            @else
                            <img src="{{asset('assets/landing/img/Success.gif')}}" width="40" alt="" class="mb-4">
                            <h5 class="card-title text-center">
                                {{ '¡Felicidades!' }}
                            </h5>

                            @endif


                    </div>
                    <div class="card-body p-4 pb-5">
                        <div class="register-congrats-txt">
                            @if (isset($type) && $type == 'commission')
                            {{ 'Has optado por nuestro plan basado en comisiones. El administrador revisará los detalles y activará tu cuenta en breve. Para explorar el sitio:' }}
                            <a href="{{ route('home',['new_user'=> true]) }}" class="text-base font-bold">{{ 'Visita aquí' }}</a>

                            @elseif( isset($payment_status) && $payment_status == 'fail')
                            {{ 'Lo sentimos, tu transacción no pudo ser completada. Por favor, elige otro método de pago.' }}
                            <a href="{{ route('restaurant.back',['store_id' => $store_id ?? null]) }}" class="text-base font-bold">{{ 'Inténtalo de nuevo' }}</a>
                            @else
                            {{ '¡Gracias por tu suscripción! Tu pago se procesó exitosamente. Ten en cuenta que tu suscripción se activará una vez que sea aprobada por nuestro equipo. Para explorar el sitio:' }}
                            <a href="{{ route('home',['new_user'=> true]) }}" class="text-base font-bold">{{ 'Visita aquí' }}</a>
                            @endif

                        </div>

                        {{-- @if (! (isset($payment_status) && $payment_status == 'fail'))
                        <div class="text-center py-2">
                            {{ translate('or') }}
                        </div>
                        <div class="text-center">
                            <a href="{{ route('home',['new_user'=> true]) }}" class="text-base font-bold">{{ translate('Continue to Home Page') }}</a>
                        </div>
                        @endif --}}
                    </div>
                </div>
            </div>
        </div>
    </section>

    @endsection
    @push('script_2')
    <script>
        @if (! (isset($payment_status) && $payment_status == 'fail'))
        document.addEventListener("DOMContentLoaded", function() {
            var homeLink = document.getElementById('home-link');
            var newUrl = "{{ route('home',['new_user'=> true]) }}";
            homeLink.setAttribute('href', newUrl);
        });
        @endif
    </script>
    @endpush
