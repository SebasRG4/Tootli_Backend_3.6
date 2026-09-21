@extends('layouts.admin.app')

@section('title', 'Validación de Credenciales Carpool')

@push('css_or_js')
<style>
    .credential-thumb {
        width: 60px;
        height: 42px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e7eaf3;
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .credential-thumb:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .preview-modal-img {
        max-height: 75vh;
        width: auto;
        max-width: 100%;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }
    .stat-card {
        border-radius: 12px;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header pb-2">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title d-flex align-items-center">
                    <span class="page-header-icon mr-2">
                        <i class="tio-user-big text-primary"></i>
                    </span>
                    <span>Validación de Credenciales de Comunidad</span>
                </h1>
                <p class="text-muted mb-0">Revisa y valida las credenciales y tiras de materias de estudiantes y colaboradores para Carpool seguro.</p>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <!-- Stat Cards -->
    <div class="row gx-2 gx-lg-3 mb-4">
        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card card-sm stat-card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-md avatar-soft-primary rounded-circle mr-3">
                        <i class="tio-users-switch avatar-icon"></i>
                    </div>
                    <div>
                        <span class="d-block font-size-sm text-muted">Total Solicitudes</span>
                        <h3 class="card-title mb-0 font-weight-bold">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card card-sm stat-card border-0 shadow-sm border-left-warning" style="border-left: 4px solid #f5ca99 !important;">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-md avatar-soft-warning rounded-circle mr-3">
                        <i class="tio-time avatar-icon"></i>
                    </div>
                    <div>
                        <span class="d-block font-size-sm text-muted">Pendientes de Revisar</span>
                        <h3 class="card-title mb-0 text-warning font-weight-bold">{{ $stats['pending'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card card-sm stat-card border-0 shadow-sm border-left-success" style="border-left: 4px solid #00c9a7 !important;">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-md avatar-soft-success rounded-circle mr-3">
                        <i class="tio-checkmark-circle avatar-icon"></i>
                    </div>
                    <div>
                        <span class="d-block font-size-sm text-muted">Aprobados / Verificados</span>
                        <h3 class="card-title mb-0 text-success font-weight-bold">{{ $stats['approved'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card card-sm stat-card border-0 shadow-sm border-left-danger" style="border-left: 4px solid #ed4c78 !important;">
                <div class="card-body d-flex align-items-center">
                    <div class="avatar avatar-md avatar-soft-danger rounded-circle mr-3">
                        <i class="tio-clear-circle avatar-icon"></i>
                    </div>
                    <div>
                        <span class="d-block font-size-sm text-muted">Rechazados</span>
                        <h3 class="card-title mb-0 text-danger font-weight-bold">{{ $stats['rejected'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Stat Cards -->

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <!-- Header / Filters -->
        <div class="card-header py-3 flex-wrap gap-2">
            <form action="{{ route('admin.taxi.carpool.verifications.index') }}" method="GET" class="w-100">
                <div class="row align-items-center">
                    <!-- Search Field -->
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="input-group input-group-merge input-group-flush">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="tio-search"></i></div>
                            </div>
                            <input id="datatableSearch_" type="search" name="search" class="form-control"
                                placeholder="Buscar por usuario, teléfono, matrícula o escuela..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2 mb-2 mb-md-0">
                        <select name="status" class="form-control js-select2-custom" onchange="this.form.submit()">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Todos los estados</option>
                            <option value="pending" {{ request('status') == 'pending' || !request('status') ? 'selected' : '' }}>⏳ Pendientes</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>✅ Aprobados</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>❌ Rechazados</option>
                        </select>
                    </div>

                    <!-- Document Type Filter -->
                    <div class="col-md-2 mb-2 mb-md-0">
                        <select name="document_type" class="form-control js-select2-custom" onchange="this.form.submit()">
                            <option value="">Tipo de documento</option>
                            <option value="credencial" {{ request('document_type') == 'credencial' ? 'selected' : '' }}>Credencial Escolar</option>
                            <option value="tira_materias" {{ request('document_type') == 'tira_materias' ? 'selected' : '' }}>Tira de Materias</option>
                            <option value="gafete" {{ request('document_type') == 'gafete' ? 'selected' : '' }}>Gafete Laboral</option>
                            <option value="constancia" {{ request('document_type') == 'constancia' ? 'selected' : '' }}>Constancia de Estudios</option>
                        </select>
                    </div>

                    <!-- Organization Filter -->
                    <div class="col-md-3 mb-2 mb-md-0">
                        <select name="organization_id" class="form-control js-select2-custom" onchange="this.form.submit()">
                            <option value="">Todas las instituciones</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}" {{ request('organization_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Submit / Reset -->
                    <div class="col-md-1 d-flex justify-content-end">
                        <a href="{{ route('admin.taxi.carpool.verifications.index') }}" class="btn btn-sm btn-ghost-secondary" title="Limpiar filtros">
                            <i class="tio-clear"></i>
                        </a>
                        <button type="submit" class="btn btn-sm btn-primary ml-1" title="Filtrar">
                            <i class="tio-filter"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <!-- End Header / Filters -->

        <!-- Table -->
        <div class="table-responsive datatable-custom">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Usuario / Solicitante</th>
                        <th>Institución / Escuela</th>
                        <th>Documento & Matrícula</th>
                        <th>Foto Credencial</th>
                        <th>Fecha de Envío</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($verifications as $key => $v)
                    <tr>
                        <td>{{ $verifications->firstItem() + $key }}</td>

                        <!-- Usuario -->
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-circle mr-3">
                                    <img class="avatar-img"
                                        src="{{ \App\CentralLogics\Helpers::get_full_url('profile', $v->user?->image ?? '', $v->user?->storage?->first()?->value ?? 'public', 'profile') }}"
                                        data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                                        alt="{{ $v->user?->f_name }}">
                                </div>
                                <div>
                                    <h5 class="text-hover-primary mb-0 font-weight-bold">
                                        {{ $v->user?->f_name }} {{ $v->user?->l_name }}
                                    </h5>
                                    <span class="d-block font-size-sm text-muted">
                                        <i class="tio-call-talking mr-1"></i>{{ $v->user?->phone ?? 'Sin teléfono' }}
                                    </span>
                                    @if($v->institutional_email)
                                        <span class="d-block font-size-sm text-primary">
                                            <i class="tio-email mr-1"></i>{{ $v->institutional_email }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Institución -->
                        <td>
                            <div>
                                <span class="font-weight-bold text-dark">
                                    {{ $v->organization?->name ?? 'Institución no listada' }}
                                </span>
                                @if($v->organization)
                                    <div class="mt-1">
                                        <span class="badge {{ $v->organization->type == 'university' ? 'badge-soft-success' : 'badge-soft-info' }} font-size-xs">
                                            <i class="{{ $v->organization->type == 'university' ? 'tio-school' : 'tio-briefcase' }} mr-1"></i>
                                            {{ $v->organization->type == 'university' ? 'Universidad / Escuela' : 'Zona de Trabajo' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </td>

                        <!-- Documento & Matrícula -->
                        <td>
                            <div>
                                <span class="badge badge-soft-dark text-capitalize font-weight-bold">
                                    @if($v->document_type == 'credencial')
                                        🪪 Credencial Escolar
                                    @elseif($v->document_type == 'tira_materias')
                                        📄 Tira de Materias
                                    @elseif($v->document_type == 'gafete')
                                        🏷️ Gafete Laboral
                                    @elseif($v->document_type == 'constancia')
                                        📋 Constancia Oficial
                                    @else
                                        {{ $v->document_type ?? 'Documento' }}
                                    @endif
                                </span>
                                @if($v->document_number)
                                    <div class="mt-1 font-size-sm text-muted">
                                        <strong>Matrícula:</strong> <span class="badge badge-light">{{ $v->document_number }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>

                        <!-- Miniatura Fotografía Credencial -->
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($v->id_card_image)
                                    <div class="text-center">
                                        <img src="{{ $v->id_card_image_url }}"
                                            class="credential-thumb mr-1"
                                            alt="Frente Credencial"
                                            title="Click para ver anverso en grande"
                                            onclick="openImageModal('{{ $v->id_card_image_url }}', 'Frente - {{ $v->user?->f_name }} {{ $v->user?->l_name }}')">
                                        <small class="d-block text-muted font-size-xs mt-1">Frente</small>
                                    </div>
                                @endif

                                @if($v->id_card_back_image)
                                    <div class="text-center ml-1">
                                        <img src="{{ $v->id_card_back_image_url }}"
                                            class="credential-thumb"
                                            alt="Reverso Credencial"
                                            title="Click para ver reverso en grande"
                                            onclick="openImageModal('{{ $v->id_card_back_image_url }}', 'Reverso / Sello - {{ $v->user?->f_name }} {{ $v->user?->l_name }}')">
                                        <small class="d-block text-muted font-size-xs mt-1">Reverso</small>
                                    </div>
                                @endif

                                @if(!$v->id_card_image && !$v->id_card_back_image)
                                    <span class="badge badge-soft-secondary py-1 px-2">Sin imagen</span>
                                @endif
                            </div>
                        </td>

                        <!-- Fecha de Envío -->
                        <td>
                            <span class="font-size-sm text-dark">{{ $v->created_at->format('d/m/Y') }}</span>
                            <small class="d-block text-muted">{{ $v->created_at->format('h:i A') }}</small>
                        </td>

                        <!-- Estado -->
                        <td>
                            @if($v->verification_status === 'approved')
                                <span class="badge badge-soft-success py-1 px-2">
                                    <i class="tio-checkmark-circle mr-1"></i> Aprobado
                                </span>
                            @elseif($v->verification_status === 'pending')
                                <span class="badge badge-soft-warning py-1 px-2">
                                    <i class="tio-time mr-1"></i> Pendiente
                                </span>
                            @elseif($v->verification_status === 'rejected')
                                <span class="badge badge-soft-danger py-1 px-2" title="{{ $v->rejection_reason }}">
                                    <i class="tio-clear-circle mr-1"></i> Rechazado
                                </span>
                            @else
                                <span class="badge badge-soft-secondary py-1 px-2">{{ $v->verification_status }}</span>
                            @endif

                            @if($v->ai_verified)
                                <div class="mt-1">
                                    <span class="badge badge-soft-info py-0 px-1 font-size-xs" title="Auditado automáticamente por Gemini Vision: {{ round(($v->ai_confidence_score ?? 0) * 100) }}% confianza">
                                        🤖 IA: {{ round(($v->ai_confidence_score ?? 0) * 100) }}%
                                    </span>
                                </div>
                            @endif
                        </td>

                        <!-- Acciones -->
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <!-- Ver detalles -->
                                <a class="btn btn-sm btn-white text-primary"
                                    href="{{ route('admin.taxi.carpool.verifications.show', $v->id) }}"
                                    title="Ver detalle y fotos en alta resolución">
                                    <i class="tio-visible"></i>
                                </a>

                                <!-- Aprobar rápido -->
                                @if($v->verification_status !== 'approved')
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                        title="Aprobar credencial"
                                        onclick="confirmQuickApprove('{{ route('admin.taxi.carpool.verifications.update-status', $v->id) }}', '{{ $v->user?->f_name }}')">
                                        <i class="tio-checkmark"></i>
                                    </button>
                                @endif

                                <!-- Rechazar rápido -->
                                @if($v->verification_status !== 'rejected')
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        title="Rechazar credencial"
                                        onclick="openRejectModal('{{ route('admin.taxi.carpool.verifications.update-status', $v->id) }}', '{{ $v->user?->f_name }}')">
                                        <i class="tio-clear"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-center">
                                <img class="mb-3" src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}" alt="Sin resultados" style="width: 7rem;">
                                <p class="mb-0 text-muted">No se encontraron solicitudes de verificación con los filtros seleccionados.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- End Table -->

        <!-- Card Footer -->
        <div class="card-footer">
            <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                        <span class="mr-2 text-muted font-size-sm">Mostrando:</span>
                        <span class="font-weight-bold">{{ $verifications->firstItem() ?? 0 }} - {{ $verifications->lastItem() ?? 0 }}</span>
                        <span class="ml-1 text-muted font-size-sm">de {{ $verifications->total() }} registros</span>
                    </div>
                </div>
                <div class="col-sm-auto">
                    {!! $verifications->links() !!}
                </div>
            </div>
        </div>
        <!-- End Card Footer -->
    </div>
    <!-- End Main Card -->
</div>

<!-- Modal Previsualización de Fotografía en Grande -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title text-white" id="imagePreviewModalTitle">Fotografía de Credencial</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-3 bg-light">
                <img id="previewModalImageSrc" src="" class="preview-modal-img img-fluid" alt="Credencial">
            </div>
            <div class="modal-footer py-2 d-flex justify-content-between">
                <a id="downloadImageBtn" href="#" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="tio-download mr-1"></i> Abrir en tamaño completo
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rechazar Verificación -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="rejectForm" action="" method="POST">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white"><i class="tio-clear-circle mr-1"></i> Rechazar Verificación</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Indica el motivo por el cual se rechaza el documento de <strong id="rejectUserName"></strong>:</p>
                    <div class="form-group">
                        <label class="input-label" for="rejection_reason">Motivo de Rechazo:</label>
                        <select class="form-control mb-2" onchange="document.getElementById('rejection_reason').value = this.value">
                            <option value="">-- Seleccionar motivo común --</option>
                            <option value="La fotografía es borrosa o ilegible.">Fotografía borrosa o ilegible</option>
                            <option value="La credencial se encuentra vencida o fuera de vigencia.">Credencial vencida / fuera de vigencia</option>
                            <option value="El nombre en la credencial no coincide con el perfil registrado.">Nombre no coincide con el perfil</option>
                            <option value="El documento no corresponde a una credencial oficial o tira de materias.">Documento no válido</option>
                            <option value="Falta la fotografía del reverso o sello de vigencia.">Falta reverso o sello de vigencia</option>
                        </select>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3"
                            placeholder="Escribe el motivo detallado para el usuario..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Rechazo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario Oculto para Aprobar Rápido -->
<form id="quickApproveForm" action="" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="status" value="approved">
</form>
@endsection

@push('script_2')
<script>
    function openImageModal(imgUrl, title) {
        $('#previewModalImageSrc').attr('src', imgUrl);
        $('#imagePreviewModalTitle').text(title);
        $('#downloadImageBtn').attr('href', imgUrl);
        $('#imagePreviewModal').modal('show');
    }

    function confirmQuickApprove(actionUrl, userName) {
        Swal.fire({
            title: '¿Aprobar credencial de ' + userName + '?',
            text: "El usuario quedará verificado como miembro oficial de su comunidad y podrá usar el Carpool seguro.",
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00c9a7',
            cancelButtonColor: '#secondary',
            confirmButtonText: 'Sí, Aprobar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                var form = $('#quickApproveForm');
                form.attr('action', actionUrl);
                form.submit();
            }
        });
    }

    function openRejectModal(actionUrl, userName) {
        $('#rejectForm').attr('action', actionUrl);
        $('#rejectUserName').text(userName);
        $('#rejection_reason').val('');
        $('#rejectModal').modal('show');
    }
</script>
@endpush
