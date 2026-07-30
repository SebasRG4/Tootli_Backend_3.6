@extends('layouts.admin.app')

@section('title','Suscripción')

@section('subscription_index')
active
@endsection
@push('css_or_js')

@endpush

@section('content')

<div class="content container-fluid">
    <div class="page-header">
        <div class="row align-items-center py-2">
            <div class="col-sm">
                <div class="d-flex align-items-start">
                    <img src="{{asset('assets/admin/img/store.png')}}" width="24" alt="img">
                    <div class="w-0 flex-grow pl-2">
                        <h1 class="page-header-title">{{'Paquete de suscripción'}}</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="js-nav-scroller hs-nav-scroller-horizontal mb-4">
        <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
            <li class="nav-item">
                <a href="#" class="nav-link active">{{ 'Detalles del paquete' }}</a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.business-settings.subscriptionackage.transaction',$subscriptionackage->id) }}" class="nav-link">{{ 'Actas' }}</a>
            </li>
        </ul>
    </div>


    <div class="card mb-20">
        <div class="card-header border-0">
            <div class="w-100 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h3 class="text--title card-title">{{ 'Descripción general' }}</h3>
                    <div>{{ 'Ver resumen de todos los paquetes' }}</div>
                </div>
                <div class="status-filter-wrap m-0">
                    <div class="statistics-btn-grp">
                        <label>
                            <input type="radio" name="statistics" value="all" class="order_stats_update" hidden="" checked>
                            <span>{{ 'Todo' }}</span>
                        </label>
                        <label>
                            <input type="radio" name="statistics" value="this_year" class="order_stats_update" hidden="">
                            <span>{{ 'Este Año' }}</span>
                        </label>
                        <label>
                            <input type="radio" name="statistics" value="this_month" class="order_stats_update" hidden="">
                            <span>{{ 'Este Mes' }}</span>
                        </label>
                        <label>
                            <input type="radio" name="statistics" value="this_week" class="order_stats_update" hidden="">
                            <span>{{ 'Esta Semana' }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div id="set_data">
            @include('admin-views.subscription.package.partial._over-view-data',['over_view_data'=>$over_view_data])
        </div>

    </div>




    <div class="card __billing-subscription mb-3">
        <div class="card-header border-0 align-items-center">
            <h4 class="card-title align-items-center gap-2">
                <span class="card-header-icon">
                    <img width="25" src="{{asset('assets/admin/img/subscription-plan/subscribed-user.png')}}" alt="">
                </span>
                <span>{{ 'Detalles del paquete' }}</span>
            </h4>
            <div class="d-flex gap-2 align-items-center justify-content-center">
                <label class="toggle-switch toggle-switch-sm"> {{ 'Estado' }}:&nbsp;
                    <input type="checkbox"  data-package_id="{{$subscriptionackage->id}}" data-package_name="{{$subscriptionackage->package_name}}" data-url="{{route('admin.business-settings.subscriptionackage.status',[$subscriptionackage->id,$subscriptionackage->status?0:1])}}" class="toggle-switch-input {{$subscriptionackage->status?'status_change_alert':'status_change_alert_reenable'}}" {{$subscriptionackage->status?'checked':''}}>
                    <span class="toggle-switch-label">
                        <span class="toggle-switch-indicator"></span>
                    </span>
                </label>
                <div>
                    <a class="btn btn--primary py-2" href="{{ route('admin.business-settings.subscriptionackage.edit',$subscriptionackage->id) }}" title="Edit Package"><i class="tio-edit"> </i> {{ 'Editar' }}</a>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="__bg-F8F9FC-card __plan-details">
                <div class="d-flex flex-wrap flex-md-nowrap justify-content-between __plan-details-top">
                    <div class="left">
                        <h3 class="name">{{ $subscriptionackage->package_name }}</h3>
                        <div class="font-medium text--title">{{ $subscriptionackage->text }}</div>
                    </div>
                    <h3 class="right">{{\App\CentralLogics\Helpers::format_currency($subscriptionackage->price) }}
                        /<small class="font-medium text--title">{{ $subscriptionackage->validity }} {{
                            'días' }}</small></h3>
                </div>

                <div class="check--grid-wrapper mt-3 max-w-850px">


                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{asset('assets/admin/img/subscription-plan/check.png')}}" alt="">
                            @if ( $subscriptionackage->max_order == 'unlimited' )
                            <span class="form-check-label text-dark">{{ $subscriptionackage->module_type == 'rental' ? 'viajes ilimitados' : 'pedidos ilimitados' }}</span>
                            @else
                            <span class="form-check-label text-dark"> {{ $subscriptionackage->max_order }} {{
                                  $subscriptionackage->module_type == 'rental' ? 'viajes' : 'Órdenes' }}</span>
                            @endif
                        </div>
                    </div>

                    @if ( $subscriptionackage->module_type !== 'rental')

                    <div>
                        <div class="d-flex align-items-center gap-2">
                            @if ( $subscriptionackage->pos == 1 )
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
                            @if ( $subscriptionackage->mobile_app == 1 )
                            <img src="{{asset('assets/admin/img/subscription-plan/check.png')}}" alt="">
                            @else
                            <img src="{{asset('assets/admin/img/subscription-plan/check-1.png')}}" alt="">
                            @endif
                            <span class="form-check-label text-dark">{{ 'Aplicación móvil' }}</span>
                        </div>
                    </div>
                    @if ( $subscriptionackage->module_type !== 'rental')

                    <div>
                        <div class="d-flex align-items-center gap-2">
                            @if ( $subscriptionackage->self_delivery == 1 )
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
                            @if ( $subscriptionackage->max_product == 'unlimited' )
                            <span class="form-check-label text-dark">{{  $subscriptionackage->module_type == 'rental' ? 'Subida ilimitada' : 'Carga ilimitada de artículos'
                                }}</span>
                            @else
                            <span class="form-check-label text-dark"> {{ $subscriptionackage->max_product }} {{
                               $subscriptionackage->module_type == 'rental' ? 'Carga de vehículos' : 'Subir producto' }}</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="d-flex align-items-center gap-2">
                            @if ( $subscriptionackage->review == 1 )
                            <img src="{{asset('assets/admin/img/subscription-plan/check.png')}}" alt="">
                            @else
                            <img src="{{asset('assets/admin/img/subscription-plan/check-1.png')}}" alt="">
                            @endif
                            <span class="form-check-label text-dark">{{ 'revisar' }}</span>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex align-items-center gap-2">
                            @if ( $subscriptionackage->chat == 1 )
                            <img src="{{asset('assets/admin/img/subscription-plan/check.png')}}" alt="">
                            @else
                            <img src="{{asset('assets/admin/img/subscription-plan/check-1.png')}}" alt="">
                            @endif
                            <span class="form-check-label text-dark">{{ 'charlar' }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>



<!-- Button trigger modal -->
<div class="modal fade" id="status-chage-deactive">
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
                            <h5 class="modal-title" id="toggle-title"></h5>
                        </div>
                        <div class="text-center" id="toggle-message">
                            <h3>{{ '¿Estás seguro de que quieres salir del estado?' }}</h3>
                            <p>{{ 'Estás a punto de desactivar un paquete de suscripción. Tiene la opción de cambiar los planes de todas las tiendas o permitir que las tiendas realicen cambios. Elija una de las siguientes opciones para continuar.' }}</p>
                        </div>
                    </div>
                    <div class="btn--container justify-content-center">
                        <a href="#" data-toggle="tooltip" data-placement="bottom" title="{{ 'Las tiendas estarán suscritas hasta que caduque su paquete.' }}"  id="status_change_now" class="btn btn-outline-primary min-w-120" >
                            {{'Permitir que la tienda cambie'}}
                        </a>
                        <button type="button"  class="btn btn--primary min-w-120  shift_package"  data-dismiss="modal" >{{'Cambiar plan'}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Button trigger modal -->
<div class="modal fade" id="status-chage-active">
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
                            <img src="{{asset('assets/admin/img/subscription-plan/tick.png')}}" class="mb-20">
                            <h5 class="modal-title" id="toggle-title"></h5>
                        </div>
                        <div class="text-center" id="toggle-message">
                            <h3>{{ '¿Está seguro de que desea activar el estado?' }}</h3>
                            <p>{{ 'Este paquete estará disponible para las tiendas.' }}</p>
                        </div>
                    </div>
                    <div class="btn--container justify-content-center">
                        <button type="button"  class="btn btn--cancel min-w-120 "  data-dismiss="modal" >{{'Cerca'}}</button>
                        <a href="#"  id="status_change_now2" class="btn btn--primary  min-w-120" >
                            {{'Activo ahora'}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shift_package">
    <div class="modal-dialog modal-dialog-centered status-warning-modal">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true" class="tio-clear"></span>
                </button>
            </div>
            <form action="{{ route('admin.business-settings.subscriptionackage.switchPlan') }}" method="post">
                @csrf
                <input type="hidden" name="turn_off_package_id" id="turn_off_package_id">
            <div class="modal-body pb-5 pt-0">
                <div class="max-349 mx-auto mb-20">
                    <div>
                        <div class="text-center">
                            <img src="{{asset('assets/admin/img/subscription-plan/package-status-disable.png')}}" class="mb-20">
                            <h5 class="modal-title" id="toggle-title"></h5>
                        </div>
                        <div class="text-center" id="toggle-message">
                            <h3>{{ 'Cambiar el plan de negocios existente.' }}</h3>
                            <div class="form-group">
                                <label class="input-label text-capitalize"> <span  id="package_name"  class="badge badge-secondary"></span> </label>
                                <label class="input-label text-capitalize mt-2 mb-2">{{ 'Seleccionar plan de negocios' }} </label>
                                    <select class="form-control js-select2-custom  " name="package_id">
                                        <option value="" selected > {{'seleccione un paquete' }}</option>
                                        <option value="commission"  > {{'Base de comisiones' }}</option>
                                        @foreach ($packages as $key => $package)
                                        @if ($package->status == 1 && $subscriptionackage->id != $package->id)
                                            <option class="show_all" id="package_{{ $package->id }}" value="{{ $package->id }}"> {{$package->package_name }}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-center">

                        <button type="submit"  class="btn btn--primary min-w-120 ">{{'Cambiar y girar el estado'}}</button>
                    </div>
                </div>
            </div>
        </form>
        </div>
    </div>
</div>




@endsection

@push('script_2')
<script>
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
                location.href=url;
            }
        })
    }


    $(document).on('click', '.order_stats_update', function () {
        $.get({
            url: '{{route('admin.business-settings.subscriptionackage.overView',$subscriptionackage->id)}}',
            dataType: 'json',
            data: {
                type: $(this).val(),
            },
            beforeSend: function () {
                $('#loading').show();
            },
            success: function (data) {
                $('#set_data').empty().html(data.view);
            },
            complete: function () {
                $('#loading').hide();
            },
        });
    });


        $(document).on("click", ".status_change_alert", function () {
            let url = $(this).data('url');
            let package_name = $(this).data('package_name');
            $('.show_all').removeAttr("hidden");
            $('#package_'+$(this).data('package_id')).attr("hidden","true");
            $('#status_change_now').attr("href",url);
            $('#turn_off_package_id').val($(this).data('package_id')) ;
            $('#package_name').text(package_name);

            status_change_alert(url,event)
        });
        $(document).on("click", ".status_change_alert_reenable", function (e) {
            e.preventDefault();
            let url = $(this).data('url');
            $('#status_change_now2').attr("href",url);
            // $('#status-chage-deactive').modal('hide');
            $('#status-chage-active').modal('show');
        });



        $(document).on("click", ".shift_package", function () {
            $('#status-chage-deactive').modal('hide');
            $('#shift_package').modal('show');
        });

        function status_change_alert(url, e) {
            e.preventDefault();
            $('#status-chage-deactive').modal('show');
        }
        $(document).on("ready",  function () {
            $('.js-select2-custom').select2({
                templateResult: function(option) {
                    if(option.element && (option.element).hasAttribute('hidden')){
                        return null;
                    }
                        return option.text;
                    }
            });
        });




</script>
@endpush
