@extends('layouts.admin.app')

@section('title', 'Insignias de Repartidor')

@push('css_or_js')
<style>
    .badge-color-dot {
        display: inline-block;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-header-title">
            <span class="page-header-icon">
                <img src="{{ asset('assets/admin/img/condition.png') }}" class="w--26" alt="">
            </span>
            <span>Insignias de Repartidor</span>
        </h1>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2 border-0">
                    <div class="search--button-wrapper">
                        <h5 class="card-title">
                            Lista de insignias
                            <span class="badge badge-soft-dark ml-2" id="itemCount">{{ $badges->total() }}</span>
                        </h5>
                        <form action="javascript:" id="search-form" class="search-form">
                            <div class="input-group input--group">
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                    placeholder="Ej: nombre de insignia" aria-label="Search"
                                    value="{{ request('search') }}">
                                <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>
                            </div>
                        </form>
                        <div class="btn-group ml-2">
                            <a href="{{ route('admin.badges.add') }}" class="btn btn--primary">
                                <i class="tio-add"></i> Nueva insignia
                            </a>
                            <a href="{{ route('admin.badge-levels.list') }}" class="btn btn--secondary">
                                <i class="tio-layers"></i> Gestionar niveles
                            </a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr class="text-center">
                                <th class="border-0">#</th>
                                <th class="border-0">Insignia</th>
                                <th class="border-0">Clave</th>
                                <th class="border-0">Condición</th>
                                <th class="border-0">XP</th>
                                <th class="border-0">Colores</th>
                                <th class="border-0">Estado</th>
                                <th class="border-0">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($badges as $key => $badge)
                            <tr>
                                <td class="text-center">{{ $key + $badges->firstItem() }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-color-dot" style="background: {{ $badge->color_hex }}; border-color: {{ $badge->icon_color_hex }}"></span>
                                        <div>
                                            <div class="font-weight-bold text-dark">{{ $badge->title }}</div>
                                            <small class="text-muted">{{ Str::limit($badge->description, 50) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><code>{{ $badge->key }}</code></td>
                                <td class="text-center">
                                    <span class="badge badge-soft-info">{{ $badge->condition_type }}</span>
                                    <span class="ml-1 font-weight-bold">{{ $badge->condition_value }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-soft-warning">+{{ $badge->xp_reward }} XP</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge-color-dot mr-1" style="background: {{ $badge->color_hex }}"></span>
                                    <span class="badge-color-dot" style="background: {{ $badge->icon_color_hex }}"></span>
                                </td>
                                <td class="text-center">
                                    <label class="toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="checkbox" class="toggle-switch-input badge-status-toggle"
                                            data-id="{{ $badge->id }}"
                                            {{ $badge->status ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.badges.edit', $badge->id) }}"
                                        class="btn btn-sm btn--warning">
                                        <i class="tio-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn--danger delete-badge-btn"
                                        data-id="{{ $badge->id }}" data-title="{{ $badge->title }}">
                                        <i class="tio-delete"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <img src="{{ asset('assets/admin/img/empty.png') }}" class="mb-3" width="80" alt="">
                                    <p class="text-muted">No hay insignias registradas</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    {{ $badges->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteBadgeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="tio-delete mr-2"></i>Eliminar insignia</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar la insignia <strong id="deleteBadgeTitle"></strong>?
                <br><small class="text-muted">Esta acción no se puede deshacer.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--secondary" data-dismiss="modal">Cancelar</button>
                <form id="deleteBadgeForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="id" id="deleteBadgeId">
                    <button type="submit" class="btn btn--danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    // Toggle status
    $(document).on('change', '.badge-status-toggle', function () {
        const id     = $(this).data('id');
        const status = $(this).is(':checked') ? 1 : 0;
        $.post('{{ route("admin.badges.status") }}', {
            _token: '{{ csrf_token() }}',
            id,
            status,
        }).fail(() => {
            this.checked = !this.checked;
            toastr.error('Error al actualizar el estado.');
        });
    });

    // Delete badge
    $(document).on('click', '.delete-badge-btn', function () {
        const id    = $(this).data('id');
        const title = $(this).data('title');
        $('#deleteBadgeId').val(id);
        $('#deleteBadgeTitle').text(title);
        $('#deleteBadgeForm').attr('action', '{{ route("admin.badges.delete", 999999) }}'.replace('999999', id));
        $('#deleteBadgeModal').modal('show');
    });

    // Search
    $('#search-form').on('submit', function (e) {
        e.preventDefault();
        const search = $('#datatableSearch_').val();
        window.location.href = '{{ route("admin.badges.list") }}?search=' + encodeURIComponent(search);
    });
</script>
@endpush
