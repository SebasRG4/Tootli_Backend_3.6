@extends('layouts.admin.app')

@section('title', 'Detalle de Credencial Carpool')

@push('css_or_js')
<style>
    .document-preview-card {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 16px;
        background: #f9fafb;
        text-align: center;
        transition: border-color 0.2s;
    }
    .document-preview-card:hover {
        border-color: #00c9a7;
    }
    .document-img-fluid {
        max-height: 480px;
        width: 100%;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        cursor: pointer;
        transition: transform 0.2s;
    }
    .document-img-fluid:hover {
        transform: scale(1.02);
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title d-flex align-items-center">
                    <a class="btn btn-icon btn-sm btn-ghost-secondary rounded-circle mr-2"
                        href="{{ route('admin.taxi.carpool.verifications.index') }}" title="Regresar al listado">
                        <i class="tio-arrow-backward"></i>
                    </a>
                    <span>Detalle de Validación de Credencial</span>
                </h1>
                <span class="text-muted ml-5">Revisión de documentos de seguridad para Carpool</span>
            </div>
            <div class="col-sm-auto">
                <a href="{{ route('admin.taxi.carpool.verifications.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="tio-list mr-1"></i> Ver todas las solicitudes
                </a>
            </div>
        </div>
    </div>
    <!-- End Page Header -->

    <div class="row">
        <!-- User & Status Column -->
        <div class="col-lg-4 mb-3 mb-lg-0">
            <!-- User Info Card -->
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-header bg-soft-primary">
                    <h5 class="card-title mb-0 text-primary">
                        <i class="tio-user mr-1"></i> Datos del Solicitante
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="avatar avatar-xl avatar-circle mb-3 mx-auto">
                        <img class="avatar-img"
                            src="{{ \App\CentralLogics\Helpers::get_full_url('profile', $verification->user?->image ?? '', $verification->user?->storage?->first()?->value ?? 'public', 'profile') }}"
                            data-onerror-image="{{ asset('assets/admin/img/160x160/img1.jpg') }}"
                            alt="{{ $verification->user?->f_name }}">
                    </div>
                    <h4 class="mb-1 font-weight-bold">{{ $verification->user?->f_name }} {{ $verification->user?->l_name }}</h4>
                    <p class="text-muted font-size-sm mb-2">
                        <i class="tio-call-talking mr-1"></i>{{ $verification->user?->phone ?? 'Sin teléfono' }}
                    </p>
                    <p class="text-muted font-size-sm mb-3">
                        <i class="tio-email mr-1"></i>{{ $verification->user?->email ?? 'Sin correo' }}
                    </p>

                    <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom">
                        <strong>Estado Actual:</strong>
                        @if ($verification->verification_status === 'approved')
                            <span class="badge badge-success py-1 px-2 font-size-sm"><i class="tio-checkmark-circle mr-1"></i> Aprobado</span>
                        @elseif ($verification->verification_status === 'pending')
                            <span class="badge badge-warning py-1 px-2 font-size-sm"><i class="tio-time mr-1"></i> Pendiente de Revisión</span>
                        @elseif ($verification->verification_status === 'rejected')
                            <span class="badge badge-danger py-1 px-2 font-size-sm"><i class="tio-clear-circle mr-1"></i> Rechazado</span>
                        @else
                            <span class="badge badge-secondary py-1 px-2">{{ $verification->verification_status }}</span>
                        @endif
                    </div>

                    @if($verification->verification_status === 'rejected' && $verification->rejection_reason)
                        <div class="alert alert-soft-danger text-left mt-3 mb-0">
                            <strong>Motivo de rechazo:</strong>
                            <p class="mb-0 font-size-sm">{{ $verification->rejection_reason }}</p>
                        </div>
                    @endif

                    @if($verification->verified_at)
                        <div class="text-muted font-size-sm mt-2 text-left">
                            <i class="tio-date-range mr-1"></i> Verificado el: <strong>{{ $verification->verified_at->format('d/m/Y h:i A') }}</strong>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Institution Info Card -->
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-header bg-soft-success">
                    <h5 class="card-title mb-0 text-success">
                        <i class="tio-school mr-1"></i> Institución o Centro
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted d-block font-size-sm">Nombre de la Institución:</span>
                        <h5 class="font-weight-bold text-dark mb-0">
                            {{ $verification->organization?->name ?? 'Institución no catalogada (Especial)' }}
                        </h5>
                    </div>

                    @if($verification->organization)
                        <div class="mb-3">
                            <span class="text-muted d-block font-size-sm">Tipo de Entidad:</span>
                            <span class="badge {{ $verification->organization->type == 'university' ? 'badge-soft-success' : 'badge-soft-info' }} text-capitalize">
                                {{ $verification->organization->type == 'university' ? 'Universidad / Plantel Educativo' : 'Zona Corporativa / Parque Industrial' }}
                            </span>
                        </div>

                        @if($verification->organization->address)
                            <div class="mb-3">
                                <span class="text-muted d-block font-size-sm">Ubicación / Campus:</span>
                                <span class="font-size-sm">{{ $verification->organization->address }}</span>
                            </div>
                        @endif
                    @endif

                    <div class="mb-3">
                        <span class="text-muted d-block font-size-sm">Tipo de Documento Presentado:</span>
                        <span class="badge badge-soft-dark text-capitalize">
                            @if($verification->document_type == 'credencial')
                                🪪 Credencial Escolar
                            @elseif($verification->document_type == 'tira_materias')
                                📄 Tira de Materias Oficial
                            @elseif($verification->document_type == 'gafete')
                                🏷️ Gafete Laboral
                            @elseif($verification->document_type == 'constancia')
                                📋 Constancia de Estudios
                            @else
                                {{ $verification->document_type }}
                            @endif
                        </span>
                    </div>

                    @if($verification->document_number)
                        <div class="mb-3">
                            <span class="text-muted d-block font-size-sm">Matrícula o No. de Documento:</span>
                            <span class="font-weight-bold font-size-base text-dark">{{ $verification->document_number }}</span>
                        </div>
                    @endif

                    @if($verification->institutional_email)
                        <div class="mb-3">
                            <span class="text-muted d-block font-size-sm">Correo Institucional:</span>
                            <span class="text-primary font-weight-bold">{{ $verification->institutional_email }}</span>
                        </div>
                    @endif

                    <div>
                        <span class="text-muted d-block font-size-sm">Fecha de Envío:</span>
                        <span class="font-size-sm">{{ $verification->created_at->format('d/m/Y h:i A') }}</span>
                    </div>
                </div>
            </div>

            <!-- AI Audit Card -->
            <div class="card mb-3 shadow-sm border-0" style="border-left: 4px solid #00c9a7 !important;">
                <div class="card-header bg-soft-info d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-dark">
                        <i class="tio-cpu text-info mr-1"></i> Auditoría Inteligente por IA
                    </h5>
                    @if($verification->ai_verified)
                        <span class="badge {{ $verification->verification_status === 'approved' ? 'badge-success' : 'badge-warning' }}">
                            {{ round(($verification->ai_confidence_score ?? 0) * 100) }}% confianza
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    @if($verification->ai_extracted_data)
                        @php($ai = $verification->ai_extracted_data)
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted font-size-sm">Veredicto IA:</span>
                            @if(!empty($ai['is_approved']))
                                <span class="badge badge-soft-success font-weight-bold">✅ Aprobación Automática</span>
                            @else
                                <span class="badge badge-soft-warning font-weight-bold">⚠️ Requiere Revisión</span>
                            @endif
                        </div>
                        <div class="mb-2">
                            <span class="text-muted font-size-sm d-block">Nombre leído en el documento:</span>
                            <strong class="text-dark">{{ $ai['extracted_name'] ?? 'No detectado' }}</strong>
                            @if(isset($ai['name_match_score']))
                                <span class="badge badge-soft-primary ml-1">{{ $ai['name_match_score'] }}% coincidencia</span>
                            @endif
                        </div>
                        <div class="mb-2">
                            <span class="text-muted font-size-sm d-block">Institución leída:</span>
                            <strong class="text-dark">{{ $ai['extracted_institution'] ?? 'No detectada' }}</strong>
                        </div>
                        @if(!empty($ai['extracted_matricula']))
                            <div class="mb-2">
                                <span class="text-muted font-size-sm d-block">Matrícula leída:</span>
                                <code>{{ $ai['extracted_matricula'] }}</code>
                            </div>
                        @endif
                        @if(!empty($ai['extracted_validity']))
                            <div class="mb-2">
                                <span class="text-muted font-size-sm d-block">Vigencia / Ciclo escolar:</span>
                                <span>{{ $ai['extracted_validity'] }}</span>
                            </div>
                        @endif
                        @if(!empty($verification->ai_review_notes))
                            <div class="mt-3 p-2 bg-light rounded font-size-sm text-muted">
                                <strong>Notas del auditor IA:</strong>
                                <p class="mb-0 mt-1">{{ $verification->ai_review_notes }}</p>
                            </div>
                        @endif
                    @elseif(!empty($verification->ai_review_notes))
                        <div class="p-3 bg-soft-warning rounded border border-warning">
                            <div class="d-flex align-items-center mb-1">
                                <i class="tio-warning-outlined text-warning mr-2 font-size-lg"></i>
                                <strong class="text-dark">Estado del Servicio de IA:</strong>
                            </div>
                            <p class="mb-0 font-size-sm text-dark">{{ $verification->ai_review_notes }}</p>
                        </div>
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="tio-time display-4 d-block mb-1"></i>
                            <span class="font-size-sm">Sin auditoría de IA registrada o solicitud enviada previamente.</span>
                        </div>
                    @endif

                    @if($verification->id_card_image)
                        <div class="mt-3 pt-3 border-top text-center">
                            <form action="{{ route('admin.taxi.carpool.verifications.reanalyze-ai', $verification->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-info btn-sm btn-block">
                                    <i class="tio-refresh mr-1"></i> Re-ejecutar Auditoría con IA
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Approval / Rejection Actions -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Acciones de Verificación</h5>

                    @if($verification->verification_status !== 'approved')
                        <form action="{{ route('admin.taxi.carpool.verifications.update-status', $verification->id) }}" method="POST" class="mb-2">
                            @csrf
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="btn btn-success btn-block py-2">
                                <i class="tio-checkmark mr-1"></i> Aprobar Verificación
                            </button>
                        </form>
                    @endif

                    @if($verification->verification_status !== 'rejected')
                        <button type="button" class="btn btn-danger btn-block py-2 mb-2"
                            onclick="openRejectModal('{{ route('admin.taxi.carpool.verifications.update-status', $verification->id) }}', '{{ $verification->user?->f_name }}')">
                            <i class="tio-clear mr-1"></i> Rechazar Documento
                        </button>
                    @endif

                    @if($verification->verification_status !== 'pending')
                        <form action="{{ route('admin.taxi.carpool.verifications.update-status', $verification->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="btn btn-outline-secondary btn-block btn-sm">
                                <i class="tio-time mr-1"></i> Regresar a Pendiente
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Document Preview Column -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="tio-document-text mr-1 text-primary"></i> Fotografías del Documento Oficial
                    </h5>
                    <span class="font-size-sm text-muted">Haz clic en cualquier imagen para abrir en alta resolución</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Frente / Anverso -->
                        <div class="col-md-6 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-dark mb-0">1. Anverso / Frente</h6>
                                @if($verification->id_card_image)
                                    <a href="{{ $verification->id_card_image_url }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                        <i class="tio-open-in-new mr-1"></i> Pantalla completa
                                    </a>
                                @endif
                            </div>

                            <div class="document-preview-card">
                                @if($verification->id_card_image)
                                    <img src="{{ $verification->id_card_image_url }}"
                                        class="document-img-fluid"
                                        alt="Frente Credencial"
                                        onclick="openImageModal('{{ $verification->id_card_image_url }}', 'Frente de Credencial / Tira de Materias')">
                                @else
                                    <div class="py-5 text-muted">
                                        <i class="tio-image-outlined display-4 d-block mb-2"></i>
                                        <span>No se adjuntó fotografía frontal</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Reverso / Sello -->
                        <div class="col-md-6 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold text-dark mb-0">2. Reverso / Sello de Vigencia</h6>
                                @if($verification->id_card_back_image)
                                    <a href="{{ $verification->id_card_back_image_url }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                        <i class="tio-open-in-new mr-1"></i> Pantalla completa
                                    </a>
                                @endif
                            </div>

                            <div class="document-preview-card">
                                @if($verification->id_card_back_image)
                                    <img src="{{ $verification->id_card_back_image_url }}"
                                        class="document-img-fluid"
                                        alt="Reverso Credencial"
                                        onclick="openImageModal('{{ $verification->id_card_back_image_url }}', 'Reverso / Sello de Vigencia')">
                                @else
                                    <div class="py-5 text-muted">
                                        <i class="tio-image-outlined display-4 d-block mb-2"></i>
                                        <span>No se adjuntó fotografía de reverso</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Guidance Alert -->
                    <div class="alert alert-soft-info d-flex align-items-start mt-2">
                        <i class="tio-info-outined font-size-lg mr-2 mt-1"></i>
                        <div class="font-size-sm">
                            <strong>Criterios de Validación de Seguridad:</strong>
                            <ul class="mb-0 pl-3 mt-1">
                                <li>Verifica que el nombre completo coincida con el perfil del usuario Tootli.</li>
                                <li>Verifica que la credencial o tira de materias cuente con sello o vigencia del ciclo escolar en curso.</li>
                                <li>En caso de ser ilegible o vencida, rechaza la solicitud seleccionando el motivo correspondiente para que el estudiante pueda volver a subirla corregida.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Previsualización de Fotografía en Grande -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title text-white" id="imagePreviewModalTitle">Fotografía de Credencial</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-3 bg-light">
                <img id="previewModalImageSrc" src="" class="img-fluid rounded" style="max-height: 80vh; object-fit: contain;" alt="Documento">
            </div>
            <div class="modal-footer py-2 d-flex justify-content-between">
                <a id="downloadImageBtn" href="#" target="_blank" class="btn btn-sm btn-primary">
                    <i class="tio-download mr-1"></i> Ver tamaño original
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
@endsection

@push('script_2')
<script>
    function openImageModal(imgUrl, title) {
        $('#previewModalImageSrc').attr('src', imgUrl);
        $('#imagePreviewModalTitle').text(title);
        $('#downloadImageBtn').attr('href', imgUrl);
        $('#imagePreviewModal').modal('show');
    }

    function openRejectModal(actionUrl, userName) {
        $('#rejectForm').attr('action', actionUrl);
        $('#rejectUserName').text(userName);
        $('#rejection_reason').val('');
        $('#rejectModal').modal('show');
    }
</script>
@endpush
