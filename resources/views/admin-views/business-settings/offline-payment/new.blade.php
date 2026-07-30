@extends('layouts.admin.app')
@section('title', 'agregar método de pago sin conexión')

@push('css_or_js')

@endpush

@section('content')
    <!-- Main Content -->

    <div class="content container-fluid">
        <!-- Page Title -->
        <div class="mb-0 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">

                {{'Agregar método de pago sin conexión'}}
            </h2>
        </div>

                    <form action="{{ route('admin.business-settings.offline.store') }}" method="POST">
                        @csrf
                        <div class="d-flex justify-content-end mb-3 mt-3">
                            <div class="text--primary-2 d-flex flex-wrap align-items-center " id="bkashInfoModalButton">
                                    {{ 'Vista en sección' }}
                                <div class="ml-2 blinkings">
                                    <i class="tio-info-outined"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex  justify-content-between">
                                <div class="d-flex align-items-center gap-2">

                                    <img width="25" src="{{asset('assets/admin/img/payment-card.png')}}" alt="">
                                    <h4 class="page-title mt-2">{{'información de pago'}}</h4>
                                </div>
                                <button class="btn btn--primary" id="add-more-field-payment">
                                    <i class="tio-add"></i> {{ 'Agregar nuevo campo' }}
                                </button>
                            </div>
                            <div class="card-body">

                                <div class="row">
                                    <div class="col-xl-4 col-sm-6">
                                        <div class="form-group">
                                            <label for="method_name" class="title_color">{{ 'Nombre del método de pago' }}</label>
                                            <input type="text" class="form-control text-break" id="method_name" placeholder="{{ 'ej: bkash' }}" name="method_name" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-3" id="custom-field-section-payment"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mb-3 mt-4">
                            <div class="d-flex gap-2 justify-content-end text-primary fw-bold" id="paymentInfoModalButton">
                                {{ 'Vista en sección' }}
                                <div class="ml-2 blinkings">
                                    <i class="tio-info-outined"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-2">
                                <img width="25" src="{{asset('assets/admin/img/payment-card-fill.png')}}" alt="">
                                <h4 class="page-title mt-2">{{'Información requerida del cliente'}}</h4>
                                </div>
                                <button class="btn btn--primary" id="add-more-field-customer">
                                    <i class="tio-add"></i> {{ 'Agregar nuevo campo' }}
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-xl-4 col-sm-6">
                                        <label for="payment_note">{{'Nota de pago'}} </label>
                                        <div class="form-floating">
                                            <textarea class="form-control" name="payment_note" id="payment_note"
                                                placeholder="{{ 'Ej: Compañía ABC' }}"  disabled></textarea>
                                        </div>
                                    </div>
                                </div>

                                    <div class="customer-input-fields-section" id="custom-field-section-customer"></div>
                            </div>
                        </div>

                        <!-- BUTTON -->
                        <div class="btn--container justify-content-end mt-20">
                            <button type="reset" class="btn btn--secondary">{{'Reiniciar'}}</button>
                            <button type="submit" class="btn btn--primary demo_check">{{'Entregar'}}</button>
                        </div>
                    </form>
                </div>


    <!-- End Main Content -->
    <!-- Section View Modal -->
    <div class="modal fade" id="sectionViewModal" tabindex="-1" aria-labelledby="sectionViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-end border-0">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
            <div class="modal-body">
            <div class="d-flex align-items-center flex-column gap-3 text-center">
                <h3>{{'Pago sin conexión'}}
                </h3>
                <img width="100" src="{{asset('assets/admin/img/offline_payment.png')}}" alt="">
                <p class="text-muted">{{'Esta vista es desde la aplicación del usuario.'}} <br class="d-none d-sm-block"> {{'Así verá el cliente en la aplicación'}}</p>
            </div>

            <div class="rounded p-4 mt-3" id="offline_payment_top_part">
                <div class="card border-primary">
                    <div class="card-body">
                <div class="d-flex justify-content-between gap-2 mb-3">
                    <h4 id="payment_modal_method_name"><span></span></h4>
                    <div class="text-primary d-flex align-items-center gap-2">
                        {{'Pagar en esta cuenta'}}
                        <img width="25" src="{{asset('assets/admin/img/tick.png')}}" alt="">
                    </div>
                </div>

                <div class="d-flex text-wrap flex-column gap-2" id="methodNameDisplay"> </div>
                <div class="d-flex text-wrap flex-column gap-2" id="displayDataDiv"> </div>
            </div>
            </div>
        </div>

            <div class="rounded p-4 mt-3 mt-4" id="offline_payment_bottom_part">
                <h2 class="text-center mb-4">{{'Cantidad'}} : xxx</h2>

                <h4 class="mb-3">{{'Información de pago'}}</h4>
                <div class="d-flex flex-column gap-3 mb-3" id="customer-info-display-div">

                </div>
                <div class="d-flex flex-column gap-3">
                    <textarea name="payment_note" id="payment_note" class="form-control"
                        readonly rows="10" placeholder="Note"></textarea>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>



















@endsection


@push('script_2')

    <script src="{{asset('assets/admin/js/view-pages/offline-payment.js')}}"></script>

    <script>
        "use strict";
        jQuery(document).ready(function ($) {
            let counter = 0;
            let counterPayment = 0;

            $('#add-more-field-customer').on('click', function (event) {
                if(counter < 14) {
                    event.preventDefault();

                    $('#custom-field-section-customer').append(
                        `<div id="field-row-customer--${counter}" class="field-row-customer">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{'nombre del campo de entrada'}} *</label>
                                        <input type="text" class="form-control" name="customer_input[${counter}]"
                                        placeholder="{{ 'ex' }}: {{ 'pago por' }}" value="" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{'marcador de posición'}} *</label>
                                        <input type="text" class="form-control" name="customer_placeholder[${counter}]"
                                        placeholder="{{ 'ex' }}: {{ 'Introduzca el nombre' }}" value="" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between gap-2">
                                            <div class="form-check text-start mb-3">
                                            <input class="form-check-input" type="checkbox" value="1" name="is_required[${counter}]" id="flexCheckDefault__${counter}" checked>
                                            <label class="form-check-label" for="flexCheckDefault__${counter}">
                                                {{'se requiere ?'}}
                                            </label>
                                        </div>
                                        <span class="btn action-btn btn--danger btn-outline-danger remove-field"  data-id="${counter}" style="cursor: pointer;">
                                            <i class="tio-delete-outlined"></i>
                                        </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>`
                    );

                    $(".js-select").select2();

                    counter++;
                } else {
                    Swal.fire({
                        title: '{{'Máximo alcanzado'}}',
                        confirmButtonText: '{{'OK'}}',
                    });
                }
            })

            $('#add-more-field-payment').on('click', function (event) {
                if(counterPayment < 14) {
                    event.preventDefault();

                    $('#custom-field-section-payment').append(
                        `<div id="field-row-payment--${counterPayment}" class="field-row-payment">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                <div class="form-group">
                                    <label class="title_color">{{ 'Título' }}</label>
                                    <input type="text" name="input_name[]" class="form-control" placeholder="{{ 'ex' }}: {{ 'Nombre del banco' }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="input_data" class="title_color">{{ 'Datos' }}</label>
                                    <input type="text" name="input_data[]" class="form-control" placeholder="{{ 'ex' }}: {{ 'banco abc' }}" required>
                                </div>
                            </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                    <div class="d-flex justify-content-end">
                                        <span class="btn action-btn btn--danger btn-outline-danger remove-field-payment" data-id="${counterPayment}"  style="cursor: pointer;">
                                            <i class="tio-delete-outlined"></i>
                                        </span>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>`
                    );

                    $(".js-select").select2();

                    counterPayment++;
                } else {
                    Swal.fire({
                        title: '{{'Máximo alcanzado'}}',
                        confirmButtonText: '{{'OK'}}',
                    });
                }
            })

            $('form').on('reset', function () {
                if(counter > 1) {
                    $('#custom-field-section-payment').html("");
                    $('#custom-field-section-customer').html("");
                    $('#method_name').val("");
                    $('#payment_note').val("");
                }

                counter = 1;
            })

            $(document).on('click', '.remove-field-payment', function () {
                let fieldRowId=  $(this).data('id');
                $( `#field-row-payment--${fieldRowId}` ).remove();
                counterPayment--;

            });
            $(document).on('click', '.remove-field', function () {
                let fieldRowId=  $(this).data('id');
                $( `#field-row-customer--${fieldRowId}` ).remove();
                counter--;

            });
        });

    </script>


@endpush
