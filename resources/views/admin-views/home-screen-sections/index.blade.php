@extends('layouts.admin.app')

@section('title', 'secciones de la pantalla de inicio')

@push('css_or_js')
    <style>
        .sortable-row {
            cursor: grab;
            transition: background 0.2s;
        }

        .sortable-row:active {
            cursor: grabbing;
        }

        .drag-handle {
            cursor: grab;
            color: #9CA3AF;
            font-size: 18px;
            padding: 0 8px;
        }

        .drag-handle:hover {
            color: #4B5563;
        }

        .ui-sortable-helper {
            background: #fff !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
            border-radius: 6px;
        }

        .ui-sortable-placeholder {
            height: 55px;
            background: #E8F5E9 !important;
            border: 2px dashed #4CAF50;
            visibility: visible !important;
        }

        .section-key-badge {
            font-family: monospace;
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 4px;
            background: #F3F4F6;
            color: #6B7280;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/banner.png')}}" class="w--26" alt="">
                </span>
                <span>{{'secciones de la pantalla de inicio'}}</span>
            </h1>
            <p class="text-muted mt-1">
                {{'arrastre secciones para cambiar el orden'}}
            </p>
        </div>

        <!-- Sections List -->
        <div class="card">
            <div class="card-header border-0 py-2">
                <h5 class="card-title">
                    {{'secciones'}}
                    <span class="badge badge-soft-dark ml-2">{{count($sections)}}</span>
                </h5>
                <small class="text-muted"><i class="tio-drag"></i> {{'arrastrar para reordenar'}}</small>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0" style="width: 50px;"></th>
                                <th class="border-0" style="width: 60px;">{{'SL'}}</th>
                                <th class="border-0">{{'sección'}}</th>
                                <th class="border-0">{{'llave'}}</th>
                                <th class="border-0 text-center">{{'estado'}}</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-sections">
                            @foreach($sections as $key => $section)
                                <tr class="sortable-row" data-id="{{$section->id}}">
                                    <td>
                                        <span class="drag-handle"><i class="tio-drag"></i></span>
                                    </td>
                                    <td class="sl-number">{{$key + 1}}</td>
                                    <td>
                                        <span class="font-weight-semibold">{{translate('messages.' . $section->title)}}</span>
                                    </td>
                                    <td>
                                        <span class="section-key-badge">{{$section->key}}</span>
                                    </td>
                                    <td class="text-center">
                                        <label class="toggle-switch toggle-switch-sm">
                                            <input type="checkbox" class="toggle-switch-input change-status"
                                                data-url="{{route('admin.home-screen-sections.status', $section->id)}}"
                                                {{$section->status ? 'checked' : ''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function () {
            // Drag & drop sortable
            $('#sortable-sections').sortable({
                handle: '.drag-handle',
                placeholder: 'ui-sortable-placeholder',
                axis: 'y',
                update: function (event, ui) {
                    let sections = [];
                    $('#sortable-sections tr.sortable-row').each(function () {
                        sections.push($(this).data('id'));
                    });

                    $.ajax({
                        url: "{{ route('admin.home-screen-sections.priority') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            sections: sections
                        },
                        success: function (response) {
                            toastr.success("{{'pedido actualizado exitosamente'}}");
                            // Update SL numbers
                            $('#sortable-sections tr.sortable-row').each(function (index) {
                                $(this).find('.sl-number').text(index + 1);
                            });
                        },
                        error: function () {
                            toastr.error("{{'no se pudo actualizar el pedido'}}");
                            location.reload();
                        }
                    });
                }
            });

            // Status toggle
            $('.change-status').on('change', function () {
                let url = $(this).data('url');
                $.get(url, function () {
                    toastr.success("{{'estado actualizado'}}");
                });
            });
        });
    </script>
@endpush