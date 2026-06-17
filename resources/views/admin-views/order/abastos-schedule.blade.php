@extends('layouts.admin.app')

@section('title', 'Horarios de Entrega de Tootli Abastos')

@push('css_or_js')
    <!-- Custom styles if any -->
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-xl-10 col-md-9 col-sm-8 mb-3 mb-sm-0">
                    <h1 class="page-header-title text-capitalize m-0">
                        <span class="page-header-icon">
                            <img src="{{ asset('assets/admin/img/store.png') }}" class="w--26" alt="">
                        </span>
                        <span>
                            Tootli Abastos: Horarios y Franjas de Entrega
                        </span>
                    </h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->

        <div class="row g-3">
            <!-- Delivery Time Setup Card -->
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <span class="card-header-icon">
                                <i class="tio-delivery"></i>
                            </span>
                            <span>Tiempo Aproximado de Entrega</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.abastos.schedule.update-delivery-time') }}" method="post">
                            @csrf
                            @php
                                $delivery_time = $store->delivery_time ?? '1-2 days';
                                $parts = explode('-', $delivery_time);
                                $min_time = isset($parts[0]) ? (int)$parts[0] : 1;
                                $max_part = isset($parts[1]) ? $parts[1] : '2 days';
                                $max_parts = explode(' ', trim($max_part));
                                $max_time = isset($max_parts[0]) ? (int)$max_parts[0] : 2;
                                $time_type = isset($max_parts[1]) ? trim($max_parts[1]) : 'days';
                            @endphp

                            <div class="form-group">
                                <label class="input-label text-capitalize">Rango de Entrega Estimado</label>
                                <div class="input-group">
                                    <input type="number" name="minimum_delivery_time" class="form-control" placeholder="Min: 1" value="{{ $min_time }}" min="1" required data-toggle="tooltip" data-placement="top" title="Tiempo Mínimo">
                                    <input type="number" name="maximum_delivery_time" class="form-control" placeholder="Max: 2" value="{{ $max_time }}" min="1" required data-toggle="tooltip" data-placement="top" title="Tiempo Máximo">
                                    <select name="delivery_time_type" class="form-control text-capitalize" required>
                                        <option value="min" {{ $time_type == 'min' ? 'selected' : '' }}>Minutos</option>
                                        <option value="hours" {{ $time_type == 'hours' ? 'selected' : '' }}>Horas</option>
                                        <option value="days" {{ $time_type == 'days' ? 'selected' : '' }}>Días</option>
                                    </select>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    Este rango de entrega se mostrará a los vendedores en la pantalla de detalles de sus pedidos de insumos.
                                </small>
                            </div>

                            <div class="btn--container justify-content-end mt-4">
                                <button type="submit" class="btn btn--primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Daily Schedule (Time Slots) Card -->
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <span class="card-header-icon">
                                <i class="tio-clock"></i>
                            </span>
                            <span>Franjas Horarias Diarias (Recepción/Entrega)</span>
                        </h5>
                    </div>
                    <div class="card-body" id="schedule">
                        @include('admin-views.vendor.view.partials._schedule', ['store' => $store])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create schedule modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ translate('messages.Create Schedule') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="javascript:" method="post" id="add-schedule">
                        @csrf
                        <input type="hidden" name="day" id="day_id_input">
                        <input type="hidden" name="store_id" value="{{ $store->id }}">
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">{{ translate('messages.Start time') }}:</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">{{ translate('messages.End time') }}:</label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                        <div class="btn--container justify-content-end">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">{{ translate('messages.Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";

        $(document).ready(function () {
            $('#exampleModal').on('show.bs.modal', function (event) {
                let button = $(event.relatedTarget);
                let day_name = button.data('day');
                let day_id = button.data('dayid');
                let modal = $(this);
                modal.find('.modal-title').text('{{ translate('messages.Create Schedule For ') }} ' + day_name);
                modal.find('.modal-body input[name=day]').val(day_id);
            });
        });

        $(document).on('click', '.delete-schedule', function () {
            let route = $(this).data('url');
            Swal.fire({
                title: '{{ translate('Want_to_delete_this_schedule?') }}',
                text: '{{ translate('If_you_select_Yes,_the_time_schedule_will_be_deleted') }}',
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#00868F',
                cancelButtonText: '{{ translate('messages.no') }}',
                confirmButtonText: '{{ translate('messages.yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.get({
                        url: route,
                        beforeSend: function () {
                            $('#loading').show();
                        },
                        success: function (data) {
                            if (data.errors) {
                                for (let i = 0; i < data.errors.length; i++) {
                                    toastr.error(data.errors[i].message, {
                                        CloseButton: true,
                                        ProgressBar: true
                                    });
                                }
                            } else {
                                $('#schedule').empty().html(data.view);
                                toastr.success('{{ translate('messages.Schedule removed successfully') }}', {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            }
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            toastr.error('{{ translate('messages.Schedule not found') }}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        },
                        complete: function () {
                            $('#loading').hide();
                        },
                    });
                }
            })
        });

        $('#add-schedule').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.store.add-schedule') }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        $('#schedule').empty().html(data.view);
                        $('#exampleModal').modal('hide');
                        toastr.success('{{ translate('messages.Schedule added successfully') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    toastr.error(XMLHttpRequest.responseText, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });
    </script>
@endpush
