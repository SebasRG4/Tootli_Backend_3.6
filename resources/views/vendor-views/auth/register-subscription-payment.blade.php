@extends('layouts.landing.app')
@section('title', 'registro de proveedores')
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


            <form class="reg-form js-validate" action="{{ route('restaurant.payment') }}" method="post">
                @csrf
                @method('post')
                <input type="hidden" name="store_id" value="{{ $store_id }}" >
                <input type="hidden" name="package_id" value="{{ $package_id }}" >
                <div class="card __card mb-3 pt-4">
                    <div class="card-header border-0">
                        <h5 class="card-title text-center">
                            {{ 'Realice el pago de su plan comercial' }}
                        </h5>
                    </div>
                    <div class="card-body p-4 pt-0">

                        <?php
                        if( data_get($free_trial_settings, 'subscription_free_trial_type') == 'year'){
                                $trial_period =data_get($free_trial_settings, 'subscription_free_trial_days') > 0 ? data_get($free_trial_settings, 'subscription_free_trial_days')  / 365 : 0;
                            } else if( data_get($free_trial_settings, 'subscription_free_trial_type') == 'month'){
                                $trial_period =data_get($free_trial_settings, 'subscription_free_trial_days') > 0 ? data_get($free_trial_settings, 'subscription_free_trial_days')  / 30 : 0;
                            } else{
                                $trial_period =data_get($free_trial_settings, 'subscription_free_trial_days') > 0 ? data_get($free_trial_settings, 'subscription_free_trial_days') : 0 ;
                            }
                        ?>
                        @if (data_get($free_trial_settings,'subscription_free_trial_status') == 1 && data_get($free_trial_settings,'subscription_free_trial_days') > 0 )
                            <label class="payment-item">
                                <input type="radio" class="d-none" checked value="free_trial" name="payment">
                                <div class="payment-item-inner justify-content-center">
                                    <div class="check">
                                        {{-- <img src="{{asset('assets/admin/img/check-1.png')}}" class="uncheck" alt=""> --}}
                                        <img src="{{asset('assets/admin/img/check-2.png')}}" class="check" alt="">
                                    </div>
                                    <span>{{ 'Continuar con' }} {{ $trial_period }}  {{ data_get($free_trial_settings, 'subscription_free_trial_type') }} {{ 'Prueba Gratuita' }}</span>
                                </div>
                            </label>
                        @endif


                        <br>
                        <br>
                        <h6 class="text-16 mb-4">{{ 'Pagar en línea' }} <span class="font-regular text-body">({{ 'Forma más rápida y segura de pagar la factura' }})</span></h6>
                        <div class="row g-3">


                            @foreach ($payment_methods as $item)
                            <div class="col-md-6 col-lg-4">
                                <label class="payment-item">
                                    <input type="radio" class="d-none" value="{{ $item['gateway'] }}" name="payment">
                                    <div class="payment-item-inner">
                                        <div class="check">
                                            <img src="{{asset('assets/admin/img/check-1.png')}}" class="uncheck" alt="">
                                            <img src="{{asset('assets/admin/img/check-2.png')}}" class="check" alt="">
                                        </div>
                                        <span>{{ $item['gateway_title'] }}</span>
                                        <img class="ms-auto" height="30"


                                            src="{{ \App\CentralLogics\Helpers::get_full_url('payment_modules/gateway_image',$item['gateway_image'],$item['storage'] ?? 'public') }}"


                                        width="30" alt="">
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-end pt-5 d-flex flex-wrap justify-content-end gap-3">
                            {{-- <a  href="{{ route('restaurant.back',['store_id' => $store_id] ) }}" type="button" class="cmn--btn btn--secondary shadow-none rounded-md border-0 outline-0">{{ 'Atrás'
                                }}</a> --}}
                            <button type="submit" class="cmn--btn rounded-md border-0 outline-0">{{ 'Siguiente'
                                }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @endsection
    @push('script_2')
    @endpush
