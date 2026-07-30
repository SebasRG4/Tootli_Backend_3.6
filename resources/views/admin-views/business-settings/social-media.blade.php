@extends('layouts.admin.app')
@section('title', 'redes sociales')
@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{ asset('assets/admin/css/croppie.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title mr-3">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/social.png')}}" class="w--26" alt="">
                </span>
                <span>
                     {{'redes sociales'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->
        <div class="card mb-3">
            <div class="card-body">
                <form class="text-left" action="javascript:">
                    @csrf
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name" class="form-label">{{ 'nombre' }}</label>
                                <select class="form-control w-100" name="name" id="name">
                                    <option>---{{ 'seleccionar' }}---</option>
                                    <option value="instagram">{{ 'Instagram' }}</option>
                                    <option value="facebook">{{ 'Facebook' }}</option>
                                    <option value="twitter">{{ 'Gorjeo' }}</option>
                                    <option value="linkedin">{{ 'LinkedIn' }}</option>
                                    <option value="pinterest">{{ 'Pinterest' }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="hidden" id="id">
                                <label for="link"
                                    class="form-label {{ Session::get('direction') === 'rtl' ? 'mr-1' : '' }}">{{ 'enlace de redes sociales' }}</label>
                                <input type="text" name="link" class="form-control" id="link"
                                    placeholder="{{ 'enlace de redes sociales' }}" required>
                            </div>
                            <div class="col-md-12">
                                <input type="hidden" id="id">
                            </div>

                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset">{{ 'reiniciar' }}</button>
                        <button id="add" class="btn btn--primary">{{ 'ahorrar' }}</button>
                        <a href="javascript:" id="update" class="initial-hidden btn btn--primary">{{ 'actualizar' }}</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0" scope="col">{{ 'SL' }}</th>
                                <th class="border-0" scope="col">{{ 'nombre' }}</th>
                                <th class="border-0" scope="col">{{ 'enlace' }}</th>
                                <th class="border-0" scope="col">{{ 'estado' }}</th>
                                <th class="border-0" scope="col">{{ 'acción' }}</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script_2')
    <script>
        "use strict";
        fetch_social_media();

        function fetch_social_media() {

            $.ajax({
                url: "{{ route('admin.business-settings.social-media.fetch') }}",
                method: 'GET',
                success: function(data) {
                    if (data.length !== 0) {
                        let html = '';
                        for (let count = 0; count < data.length; count++) {
                            html += '<tr>';
                            html += '<td class="column_name" data-column_name="sl" data-id="' + data[count].id +
                                '">' + (count + 1) + '</td>';
                            html += '<td class="column_name" data-column_name="name" data-id="' + data[count]
                                .id + '">' + data[count].name + '</td>';
                            html += '<td class="column_name" data-column_name="slug" data-id="' + data[count]
                                .id + '">' + data[count].link + '</td>';
                            html += `<td class="column_name" data-column_name="status" data-id="${data[count].id}">
                            <label class="toggle-switch toggle-switch-sm" for="${data[count].id}">
                                    <input type="checkbox" class="toggle-switch-input status" id="${data[count].id}" ${data[count].status === 1 ? "checked" : ""}>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                        </td>`;
                            html += '<td><a type="button" class="btn btn--primary btn-outline-primary edit action-btn" id="' + data[
                                count].id + '"><i class="tio-edit"></i></a> </td></tr>';
                        }
                        $('tbody').html(html);
                    }
                }
            });
        }

        $('#add').on('click', function() {
            // $('#add').attr("disabled", true);
            let name = $('#name').val();
            let link = $('#link').val();
            if (name === "") {
                toastr.error('{{ 'Se requieren redes sociales' }}.');
                return false;
            }
            if (link === "") {
                toastr.error('{{ 'Se requieren redes sociales' }}.');
                return false;
            }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('admin.business-settings.social-media.store') }}",
                method: 'POST',

                data: {
                    name: name,
                    link: link
                },
                success: function(response) {
                    if (response.error === 1) {
                        toastr.error('{{ 'las redes sociales existen' }}');
                    } else {
                        toastr.success('{{ 'redes sociales insertadas' }}.');
                    }
                    $('#name').val('');
                    $('#link').val('');
                    fetch_social_media();
                }
            });
        });
        $(document).on('click', '.edit', function() {
            $('#update').show();
            $('#add').hide();
            let id = $(this).attr("id");
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ url('admin/business-settings/pages/social-media') }}/" + id,
                method: 'GET',
                success: function(data) {
                    $(window).scrollTop(0);
                    $('#id').val(data.id);
                    $('#name').val(data.name);
                    $('#link').val(data.link);
                    fetch_social_media()
                }
            });
        });

        $('#update').on('click', function() {
            $('#update').attr("disabled", true);
            let id = $('#id').val();
            let name = $('#name').val();
            let link = $('#link').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ url('admin/business-settings/pages/social-media') }}/" + id,
                method: 'PUT',
                data: {
                    id: id,
                    name: name,
                    link: link,
                },
                success: function() {
                    $('#name').val('');
                    $('#link').val('');

                    toastr.success('{{ 'redes sociales actualizadas' }}');
                    $('#update').hide();
                    $('#add').show();
                    fetch_social_media();

                }
            });
            $('#save').hide();
        });
        $(document).on('click', '.delete', function() {
            let id = $(this).attr("id");
            if (confirm("{{ '¿Estás seguro de que quieres eliminar?' }}?")) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ url('admin/business-settings/social-media/destroy') }}/" + id,
                    method: 'POST',
                    data: {
                        id: id
                    },
                    success: function() {
                        fetch_social_media();
                        toastr.success('{{ 'redes sociales eliminadas' }}.');
                    }
                });
            }
        });

        $(document).on('change', '.status', function() {
            let id = $(this).attr("id");
            let status;
            if ($(this).prop("checked") === true) {
                 status = 1;
            } else if ($(this).prop("checked") === false) {
                 status = 0;
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('admin.business-settings.social-media.status-update') }}",
                method: 'get',
                data: {
                    id: id,
                    status: status
                },
                success: function() {
                    toastr.success('{{ 'estado actualizado' }}');
                }
            });
        });
    </script>
@endpush
