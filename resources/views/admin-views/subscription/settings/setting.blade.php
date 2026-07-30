@extends('layouts.admin.app')

@section('title','Suscripción')

@section('subscription_settings')
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
                            <h1 class="page-header-title">{{'Configuración de suscripción'}}</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header border-0 align-items-center">
                <div class="w-100 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h3 class="text--title card-title">{{ 'Oferta de prueba gratuita' }}</h3>
                        <div>{{ 'Puede ofrecer a los proveedores una prueba gratuita para experimentar el sistema en general.' }}</div>
                    </div>
                    <label class="toggle-switch toggle-switch-sm"> {{ 'Estado' }}:&nbsp;
                        <input type="checkbox" data-url="{{route('admin.business-settings.subscriptionackage.trialStatus')}}" data-title="{{ data_get($settings, 'subscription_free_trial_status') != 1 ? '¿Estás seguro de habilitar la opción de prueba gratuita?' : '¿Estás seguro de desactivar la opción de prueba gratuita?' }}"
                        data-message="{{ data_get($settings, 'subscription_free_trial_status') != 1 ? 'Si está habilitado, la tienda puede experimentar los servicios sin costo por un tiempo limitado.' : 'Si está deshabilitada, la tienda no podrá obtener la experiencia sin ningún plan de negocios.' }}"
                        class="toggle-switch-input status_change_alert" {{ data_get($settings, 'subscription_free_trial_status') == 1?'checked':''}} >
                        <span class="toggle-switch-label">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                </div>
            </div>
                <?php
                    if( data_get($settings, 'subscription_free_trial_type') == 'year'){
                        $trial_period =data_get($settings, 'subscription_free_trial_days') > 0 ? data_get($settings, 'subscription_free_trial_days')  / 365 : 0;
                    } else if( data_get($settings, 'subscription_free_trial_type') == 'month'){
                        $trial_period =data_get($settings, 'subscription_free_trial_days') > 0 ? data_get($settings, 'subscription_free_trial_days')  / 30 : 0;
                    } else{
                        $trial_period =data_get($settings, 'subscription_free_trial_days') > 0 ? data_get($settings, 'subscription_free_trial_days') : null ;
                    }
                ?>
            <div class="card-body py-2">
                <div class="card mb-2">
                    <div class="card-body">
                        <form action="{{ route('admin.business-settings.subscriptionackage.settingUpdate') }}" method="post">
                            @csrf
                            @method("post")
                            <div class="row g-3">
                                <div class="col-sm-6 col-lg-4 col-xl-5">
                                    <div class="pr-xl-4">
                                        <label class="form-label">{{ 'Período de prueba gratuito' }}</label>
                                        <input type="number" required min="0" value="{{ $trial_period    }}" max="999" class="form-control" name="subscription_free_trial_days" placeholder="120">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4 col-xl-3 mr-auto">
                                    <label class="form-label d-none d-sm-block">&nbsp;</label>
                                    <select name="subscription_free_trial_type" class="form-control">
                                        <option {{ data_get($settings, 'subscription_free_trial_type') == 'day' ?'selected':''}} value="day" >{{ 'Día' }}</option>
                                        <option {{ data_get($settings, 'subscription_free_trial_type') == 'month' ?'selected':''}} value="month" >{{ 'Mes' }}</option>
                                        {{-- <option {{ data_get($settings, 'subscription_free_trial_type') == 'year' ?'selected':''}} value="year" >{{ 'Año' }}</option> --}}
                                    </select>
                                </div>
                                <div class="col-lg-4 col-xl-2">
                                    <label class="form-label d-none d-lg-block">&nbsp;</label>
                                    <button type="submit" class="btn px-xl-5 btn--primary w-100 h--45px">{{ 'Entregar' }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <div class="card ">
            <div class="card-header border-0 align-items-center">
                <div class="w-100 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h3 class="text--title card-title">{{ 'Mostrar advertencia de fecha límite' }}</h3>
                        <div>{{ 'Seleccione el número de días antes de que se muestre la advertencia con una cuenta regresiva hasta el final de todos los paquetes' }}</div>
                    </div>
                </div>
            </div>
            <div class="card-body pt-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.business-settings.subscriptionackage.settingUpdate') }}" method="post">
                            @csrf
                            @method("post")
                            <div class="row g-3">
                                <div class="col-sm-6 col-lg-4 col-xl-5">
                                    <div class="pr-xl-4">
                                        <label class="form-label">{{ 'Seleccionar días' }}</label>
                                        <input type="number" required name="subscription_deadline_warning_days" value="{{ data_get($settings, 'subscription_deadline_warning_days') ?? ' '  }}" min="1" max="99999999"  class="form-control" placeholder="120">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4 col-xl-5">
                                    <div class="pr-xl-4">
                                        <label class="form-label">{{ 'Escriba mensaje' }}</label>
                                        <input type="text" name="subscription_deadline_warning_message" value="{{ data_get($settings, 'subscription_deadline_warning_message')   }}" class="form-control" maxlength="254" placeholder="{{ 'Tu suscripción finalizará pronto.' }} " required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-xl-2">
                                    <label class="form-label d-none d-lg-block">&nbsp;</label>
                                    <button type="submit" class="btn px-xl-5 btn--primary w-100 h--45px">{{ 'Entregar' }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-header border-0 align-items-center">
                <div class="w-100 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h3 class="text--title card-title">{{ 'Restricción de devolución de dinero' }}</h3>
                        <div>{{ 'Configure el monto después del cual, si alguna tienda cambia o migra el plan de suscripción, no se le devolverá ningún dinero.' }}</div>
                    </div>
                </div>
            </div>
            <div class="card-body pt-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.business-settings.subscriptionackage.settingUpdate') }}" method="post">
                            @csrf
                            @method("post")
                            <div class="row g-3">
                                <div class="col-sm-6 col-lg-8 col-xl-10">
                                    <div class="pr-xl-4">
                                        <label class="form-label">{{ 'Seleccione el tiempo de uso de la suscripción' }} (%)</label>
                                        <input type="number" required name="subscription_usage_max_time" value="{{ data_get($settings, 'subscription_usage_max_time') ?? ' '  }}" min="1" max="99"  class="form-control" placeholder="120">
                                    </div>
                                </div>

                                <div class="col-lg-4 col-xl-2">
                                    <label class="form-label d-none d-lg-block">&nbsp;</label>
                                    <button type="submit" class="btn px-xl-5 btn--primary w-100 h--45px">{{ 'Entregar' }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>




@endsection

@push('script_2')
<script>
    "use strict";
        $('.status_change_alert').on('click', function (event) {
        let title = $(this).data('title');
        let url = $(this).data('url');
        let message = $(this).data('message');
        status_change_alert(title,url, message, event)
    })

    function status_change_alert(title,url, message, e) {
        e.preventDefault();
        Swal.fire({
            title: title,
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
                location.href=url;
            }
        })
    }
</script>
@endpush

