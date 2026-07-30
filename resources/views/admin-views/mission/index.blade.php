@extends('layouts.admin.app')

@section('title', 'misiones de conductor')

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/condition.png')}}" class="w--26" alt="">
                </span>
                <span>
                    {{'misiones de conductor'}}
                </span>
            </h1>
        </div>
        <!-- End Page Header -->

        <div class="row g-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h5 class="card-title">
                                {{'lista de misiones'}}<span class="badge badge-soft-dark ml-2"
                                    id="itemCount">{{$missions->total()}}</span>
                            </h5>
                            <form action="javascript:" id="search-form" class="search-form">
                                <div class="input-group input--group">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                                        placeholder="{{'ej: título de la misión'}}" aria-label="Search">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                                </div>
                            </form>
                            <a href="{{route('admin.mission.add')}}" class="btn btn--primary ml-2"><i class="tio-add"></i>
                                {{'agregar nueva misión'}}</a>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr class="text-center">
                                    <th class="border-0">{{'SL'}}</th>
                                    <th class="border-0">{{'título'}}</th>
                                    <th class="border-0">{{'órdenes objetivo'}}</th>
                                    <th class="border-0">{{'premio'}}</th>
                                    <th class="border-0">{{'duración'}}</th>
                                    <th class="border-0">{{'zona'}}</th>
                                    <th class="border-0">{{'estado'}}</th>
                                    <th class="border-0">{{'acción'}}</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                                @foreach($missions as $key => $mission)
                                    <tr>
                                        <td class="text-center">{{$key + $missions->firstItem()}}</td>
                                        <td class="text-center">
                                            <span class="d-block font-size-sm text-body">
                                                {{Str::limit($mission['title'], 25, '...')}}
                                            </span>
                                        </td>
                                        <td class="text-center">{{$mission['target_orders']}}</td>
                                        <td class="text-center">
                                            {{\App\CentralLogics\Helpers::format_currency($mission['reward_amount'])}}</td>
                                        <td class="text-center">
                                            {{$mission->start_date->format('d/M/Y')}} - {{$mission->end_date->format('d/M/Y')}}
                                        </td>
                                        <td class="text-center">
                                            {{$mission->zone ? $mission->zone->name : 'todas las zonas'}}
                                        </td>
                                        <td class="text-center">
                                            <label class="toggle-switch toggle-switch-sm" for="status-{{$mission['id']}}">
                                                <input type="checkbox" class="toggle-switch-input status-change-alert"
                                                    id="status-{{$mission['id']}}"
                                                    data-url="{{route('admin.mission.status', [$mission['id'], $mission->status ? 0 : 1])}}"
                                                    data-message="{{$mission->status ? '¿Quieres desactivar esta misión?' : '¿Quieres habilitar esta misión?'}}"
                                                    {{$mission->status ? 'checked' : ''}}>
                                                <span class="toggle-switch-label mx-auto">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="btn--container justify-content-center">
                                                <a class="btn action-btn btn--primary btn-outline-primary"
                                                    href="{{route('admin.mission.edit', [$mission['id']])}}"
                                                    title="{{'editar'}}"><i class="tio-edit"></i>
                                                </a>
                                                <a class="btn action-btn btn--danger btn-outline-danger form-alert"
                                                    href="javascript:" data-id="mission-{{$mission['id']}}"
                                                    data-message="{{ '¿Quieres eliminar esta misión?' }}"
                                                    title="{{'borrar'}}"><i class="tio-delete-outlined"></i>
                                                </a>
                                                <form action="{{route('admin.mission.delete', [$mission['id']])}}" method="post"
                                                    id="mission-{{$mission['id']}}">
                                                    @csrf @method('delete')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(count($missions) !== 0)
                        <hr>
                    @endif
                    <div class="page-area">
                        {!! $missions->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        $(document).on('ready', function () {
            // initialization of datatables
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#search-form').on('submit', function () {
                var formData = new FormData(this);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.post({
                    url: '{{route('admin.mission.list')}}',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $('#loading').show();
                    },
                    success: function (data) {
                        $('#set-rows').html(data.view);
                        $('#itemCount').html(data.count);
                        $('.page-area').hide();
                    },
                    complete: function () {
                        $('#loading').hide();
                    },
                });
            });
        });

        $(".status-change-alert").click(function () {
            let url = $(this).data('url');
            let message = $(this).data('message');
            confirm_alert(url, message);
        });

        function confirm_alert(url, message) {
            Swal.fire({
                title: '{{'¿Está seguro?'}}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{'No'}}',
                confirmButtonText: '{{'Sí'}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }
    </script>
@endpush