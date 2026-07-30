@extends('layouts.admin.app')

@section('title', 'comercio electrónico secciones dinámicas')

@push('css_or_js')
    <link href="{{asset('assets/admin/css/select2.min.css')}}" rel="stylesheet" />
    <style>
        .sortable-row {
            cursor: grab;
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
                <span>{{'comercio electrónico secciones dinámicas'}}</span>
            </h1>
        </div>

        <!-- Create Form -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">
                    <span class="card-header-icon"><i class="tio-add-circle"></i></span>
                    <span>{{'agregar nueva sección'}}</span>
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.dynamic-section-ecommerce.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <!-- Image Upload -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{'imagen de banner'}} <span
                                        class="text-danger">*</span></label>
                                <label class="__upload-img aspect-4-1 m-auto d-block">
                                    <div class="img">
                                        <img class="onerror-image" id="viewer-create"
                                            src="{{asset('assets/admin/img/upload-placeholder.png')}}"
                                            data-onerror-image="{{asset('assets/admin/img/upload-placeholder.png')}}"
                                            alt="">
                                    </div>
                                    <input type="file" name="image" accept="image/*" required hidden
                                        onchange="document.getElementById('viewer-create').src = window.URL.createObjectURL(this.files[0])">
                                </label>
                                <p class="text-center mt-2 text-muted">{{'proporción recomendada 4 1'}}</p>
                            </div>
                        </div>

                        <!-- Store Select -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">{{'tiendas selectas'}}</label>
                                <select name="stores[]" class="form-control select2-stores" multiple
                                    data-placeholder="{{'tiendas selectas'}}">
                                    @foreach($stores as $store)
                                        <option value="{{$store->id}}">{{$store->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status Toggle -->
                            <div class="form-group mt-3">
                                <label class="toggle-switch toggle-switch-sm">
                                    <input type="checkbox" class="toggle-switch-input" name="status" checked>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                    <span class="ml-2">{{'activo'}}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="btn--container justify-content-end">
                        <button type="reset" class="btn btn--reset">{{'reiniciar'}}</button>
                        <button type="submit" class="btn btn--primary">{{'entregar'}}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sections List -->
        <div class="card">
            <div class="card-header border-0 py-2">
                <h5 class="card-title">
                    {{'lista de secciones'}}
                    <span class="badge badge-soft-dark ml-2">{{$sections->total()}}</span>
                </h5>
                <small class="text-muted"><i class="tio-drag"></i> {{'arrastrar para reordenar'}}</small>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-thead-bordered table-align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0" style="width: 50px;"></th>
                                <th class="border-0">{{'SL'}}</th>
                                <th class="border-0">{{'imagen'}}</th>
                                <th class="border-0">{{'Negocios'}}</th>
                                <th class="border-0">{{'estado'}}</th>
                                <th class="border-0 text-center">{{'acción'}}</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-sections">
                            @forelse($sections as $key => $section)
                                <tr class="sortable-row" data-id="{{$section->id}}">
                                    <td>
                                        <span class="drag-handle"><i class="tio-drag"></i></span>
                                    </td>
                                    <td>{{$sections->firstItem() + $key}}</td>
                                    <td>
                                        @if($section->image_full_url)
                                            <img src="{{$section->image_full_url}}" alt="section image"
                                                style="height: 60px; width: 120px; object-fit: cover; border-radius: 6px;">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-info">
                                            {{$section->stores_count}} {{'Negocios'}}
                                        </span>
                                    </td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm">
                                            <input type="checkbox" class="toggle-switch-input change-status"
                                                data-url="{{route('admin.dynamic-section-ecommerce.status', $section->id)}}"
                                                {{$section->status ? 'checked' : ''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn action-btn btn--primary btn-outline-primary"
                                                href="{{route('admin.dynamic-section-ecommerce.edit', $section->id)}}"
                                                title="{{'editar'}}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn action-btn btn--danger btn-outline-danger delete-btn"
                                                href="javascript:"
                                                data-url="{{route('admin.dynamic-section-ecommerce.delete', $section->id)}}"
                                                title="{{'borrar'}}">
                                                <i class="tio-delete-outlined"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <img src="{{asset('assets/admin/img/empty-table.png')}}" alt="" class="mb-3"
                                            style="width:100px;">
                                        <p class="text-muted">{{'no se encontraron datos'}}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="page-area mt-3">
                    {!! $sections->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{asset('assets/admin/js/select2.min.js')}}"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2-stores').select2({
                placeholder: "{{'tiendas selectas'}}",
                allowClear: true
            });

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
                        url: "{{ route('admin.dynamic-section-ecommerce.priority') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            sections: sections
                        },
                        success: function (response) {
                            toastr.success("{{'pedido actualizado exitosamente'}}");
                            // Update SL numbers
                            $('#sortable-sections tr.sortable-row').each(function (index) {
                                $(this).find('td:nth-child(2)').text(index + 1);
                            });
                        },
                        error: function () {
                            toastr.error("{{'no se pudo actualizar el pedido'}}");
                            location.reload();
                        }
                    });
                }
            });

            $('.change-status').on('change', function () {
                let url = $(this).data('url');
                $.get(url, function () {
                    toastr.success("{{'estado actualizado'}}");
                });
            });

            $('.delete-btn').on('click', function () {
                let url = $(this).data('url');
                Swal.fire({
                    title: "{{'¿está seguro?'}}",
                    text: "{{'no podrás revertir esto'}}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#FC6A57',
                    cancelButtonColor: '#363636',
                    confirmButtonText: "{{'si borrarlo'}}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: { _token: '{{csrf_token()}}' },
                            success: function () { location.reload(); }
                        });
                    }
                });
            });
        });
    </script>
@endpush