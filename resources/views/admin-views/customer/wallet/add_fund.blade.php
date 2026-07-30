@extends('layouts.admin.app')

@section('title','agregar fondo')

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mr-3">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/money.png')}}" class="w--26" alt="">
                </span>
                <span>
                     {{'agregar fondo'}}
                </span>
            </h1>
        </div>
        <!-- Page Header -->
        <div class="card gx-2 gx-lg-3">
            <div class="card-body">
                <form action="{{route('admin.users.customer.wallet.add-fund')}}" method="post" enctype="multipart/form-data" id="add_fund">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <div class="form-group">
                                <label class="input-label" for="customer">{{'Cliente'}}
                                    <span class="form-label-secondary text-danger"
                                          data-toggle="tooltip" data-placement="right"
                                          data-original-title="{{ 'Requerido.'}}"> *
                            </span>
                                </label>
                                <select id='customer' name="customer_id" data-placeholder="{{'seleccionar cliente por nombre o teléfono'}}" class="js-data-example-ajax form-control" required>

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-12">
                            <div class="form-group">
                                <label class="input-label" for="amount">{{'cantidad'}} {{ \App\CentralLogics\Helpers::currency_symbol() }}
                                    <span class="form-label-secondary text-danger"
                                          data-toggle="tooltip" data-placement="right"
                                          data-original-title="{{ 'Requerido.'}}"> *
                            </span>
                                </label>
                                <input type="number" placeholder="{{'Ej: 50'}}" class="form-control" name="amount" min="0" id="amount" step=".01" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="reference">{{'referencia'}} <small>({{'opcional'}})</small></label>

                                <input type="text" placeholder="{{ 'Ej: 123' }}" class="form-control" name="reference" id="reference">
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button type="reset" id="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="submit" id="submit" class="btn btn--primary">{{'entregar'}}</button>
                    </div>
                </form>
            </div>
            <!-- End Table -->
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });


            $('#column3_search').on('change', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>

    <script>

        $('#add_fund').on('submit', function (e) {

            e.preventDefault();
            var formData = new FormData(this);

            Swal.fire({
                title: '{{'¿está seguro?'}}',
                text: '{{'quieres agregar fondos'}} '+$('#amount').val()+' {{\App\CentralLogics\Helpers::currency_code().' '.'a'}} '+$('#customer option:selected').text()+'{{'a la billetera'}}',
                type: 'info',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: 'primary',
                cancelButtonText: '{{'No'}}',
                confirmButtonText: '{{'agregar al fondo'}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.post({
                        url: '{{route('admin.users.customer.wallet.add-fund')}}',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        beforeSend: function() {
                            $('#loading').show();
                        },
                        success: function (data) {
                            if (data.errors) {
                                for (var i = 0; i < data.errors.length; i++) {
                                    toastr.error(data.errors[i].message, {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                                }
                            } else {
                                $('#loading').hide();
                                toastr.success('{{'fondo agregado exitosamente'}}', {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                                setTimeout(function () {
                                    window.location.reload();
                                }, 2000);

                            }
                        }
                    });
                }
            })
        })

        $('.js-data-example-ajax').select2({
            ajax: {
                url: '{{route('admin.users.customer.select-list')}}',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                    results: data
                    };
                },
                __port: function (params, success, failure) {
                    var $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });
    </script>
@endpush
