@extends('layouts.admin.app')

@section('title', 'configuración del cliente')

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mr-3">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/user-edit.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'configuración del cliente'}}
                </span>
            </h1>
        </div>
        <!-- Page Header -->

        <!-- End Page Header -->
        <form action="{{ route('admin.users.customer.update-settings') }}" method="post" enctype="multipart/form-data"
            id="update-settings">
            @csrf
            <div class="row g-2">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6 col-lg-4">
                                    <div class="form-group mb-0">
                                        <label
                                            class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                            for="customer_wallet">
                                            <span class="pr-2">{{ 'billetera del cliente' }} :</span>
                                            <input type="checkbox" class="toggle-switch-input section_visibility"
                                                data-id="customer_wallet" name="customer_wallet"
                                                id="customer_wallet" value="1" data-section="wallet-section"
                                                {{ isset($data['wallet_status']) && $data['wallet_status'] == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-lg-4">
                                    <div class="form-group mb-0">
                                        <label
                                            class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                            for="customer_loyalty_point">
                                            <span class="pr-2">{{ 'punto de fidelización del cliente' }} :</span>
                                            <input type="checkbox" class="toggle-switch-input section_visibility"
                                                data-id="customer_loyalty_point" name="customer_loyalty_point"
                                                id="customer_loyalty_point" data-section="loyalty-point-section" value="1"
                                                {{ isset($data['loyalty_point_status']) && $data['loyalty_point_status'] == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-4">
                                    <div class="form-group mb-0">
                                        <label
                                            class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control">
                                            <span
                                                class="pr-2">{{ 'ganancias por referencias de clientes' }} :</span>
                                            <input type="checkbox" class="toggle-switch-input section_visibility"
                                                data-id="ref_earning_status"
                                                name="ref_earning_status" id="ref_earning_status"
                                                data-section="referrer-earning" value="1"
                                                {{ isset($data['ref_earning_status']) && $data['ref_earning_status'] == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 wallet-section">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon">
                                    <i class="tio-settings-outlined"></i>
                                </span>
                                <span>
                                    {{'configuración de billetera'}}
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group mb-0">
                                        <label
                                            class="toggle-switch toggle-switch-sm d-flex justify-content-between border border-secondary rounded px-4 form-control"
                                            for="refund_to_wallet">
                                            <span class="pr-2">{{ 'reembolso a la billetera' }}<span
                                                    class="input-label-secondary"
                                                    data-toggle="tooltip" data-placement="right" data-original-title="{{ 'sugerencia de reembolso a la billetera' }}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'mostrar ocultar menú de comida' }}"></span> :</span>
                                            <input type="checkbox" class="toggle-switch-input" name="refund_to_wallet"
                                                id="refund_to_wallet" value="1"
                                                {{ isset($data['wallet_add_refund']) && $data['wallet_add_refund'] == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 loyalty-point-section">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon">
                                    <i class="tio-settings-outlined"></i>
                                </span>
                                <span>
                                    {{ 'configuración de puntos de fidelidad del cliente' }}
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="loyalty_point_exchange_rate">{{ translate('messages.point_to_currency_exchange_rate', ['currency' => \App\CentralLogics\Helpers::currency_code()]) }}</label>
                                        <input type="number" class="form-control" name="loyalty_point_exchange_rate"
                                            value="{{ $data['loyalty_point_exchange_rate'] ?? '0' }}">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="intem_purchase_point">{{ 'punto de compra del artículo' }}
                                            <small class="text-danger"><span class="input-label-secondary"
                                                    data-toggle="tooltip" data-placement="right" data-original-title="{{ 'sugerencia de punto de compra del artículo' }}"><img
                                                        src="{{ asset('assets/admin/img/info-circle.svg') }}"
                                                        alt="{{ 'sugerencia de punto de compra del artículo' }}"></span> *</small>
                                        </label>
                                        <input type="number" class="form-control" name="item_purchase_point" step=".01"
                                            value="{{ $data['loyalty_point_item_purchase_point'] ?? '0' }}">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="intem_purchase_point">{{ 'punto mínimo para transferir' }}</label>
                                        <input type="number" class="form-control" name="minimun_transfer_point" min="0"
                                            value="{{ $data['loyalty_point_minimum_point'] ?? '0' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 referrer-earning">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon">
                                    <i class="tio-settings-outlined"></i>
                                </span>
                                <span>
                                    {{ 'configuración de referencia del cliente' }}
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="referrer_earning_exchange_rate">{{ translate('messages.referrer_to_currency', ['currency' => \App\CentralLogics\Helpers::currency_code()]) }}</label>
                                            <input type="number" class="form-control" name="ref_earning_exchange_rate"
                                            value="{{ $data['ref_earning_exchange_rate'] ?? '0' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="btn--container justify-content-end">
                        <button type="reset" id="reset_btn" class="btn btn--reset">{{ 'reiniciar' }}</button>
                        <button type="submit" id="submit" class="btn btn--primary">{{ 'entregar' }}</button>
                    </div>
                </div>
            </div>
            </form>
        <!-- End Table -->
    </div>
@endsection
@push('script_2')
    <script src="{{asset('assets/admin')}}/js/view-pages/customer-wallet-settings.js"></script>
    <script>
        "use strict";
        $(document).on('ready', function() {
            @if (isset($data['wallet_status']) && $data['wallet_status'] != 1)
            $('.wallet-section').hide();
            @endif
            @if (isset($data['loyalty_point_status']) && $data['loyalty_point_status'] != 1)
            $('.loyalty-point-section').hide();
            @endif
            @if (isset($data['ref_earning_status']) && $data['ref_earning_status'] != 1)
            $('.referrer-earning').hide();
            @endif

            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));
            $('#column1_search').on('keyup', function() {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });


            $('#column3_search').on('change', function() {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });
        });
        $('#add_fund').on('submit', function(e) {

            e.preventDefault();
            let formData = new FormData(this);

            Swal.fire({
                title: '{{ '¿está seguro?' }}',
                text: '{{ 'quieres agregar fondos' }}' + $('#amount').val() +
                    ' {{ \App\CentralLogics\Helpers::currency_code() . ' ' . 'a' }} ' + $(
                        '#customer option:selected').text() + '{{ 'a la billetera' }}',
                type: 'info',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: 'primary',
                cancelButtonText: '{{ 'No' }}',
                confirmButtonText: '{{ 'enviar' }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.post({
                        url: '{{ route('admin.users.customer.wallet.add-fund') }}',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(data) {
                            if (data.errors) {
                                for (let i = 0; i < data.errors.length; i++) {
                                    toastr.error(data.errors[i].message, {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                                }
                            } else {
                                toastr.success(
                                    '{{ 'fondo agregado exitosamente' }}', {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                            }
                        }
                    });
                }
            })
        })
        </script>
@endpush
