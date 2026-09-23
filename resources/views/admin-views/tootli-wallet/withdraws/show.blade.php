@extends('layouts.admin.app')

@section('title', 'Detalle de Solicitud de Retiro SPEI #' . str_pad($withdraw->id, 5, '0', STR_PAD_LEFT))

@push('css_or_js')
<style>
    .clabe-box {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 16px;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
        font-size: 1.25rem;
        letter-spacing: 2px;
        color: #0f172a;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .badge-status-pending { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; font-weight: 600; }
    .badge-status-approved { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; font-weight: 600; }
    .badge-status-transferred { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; font-weight: 600; }
    .badge-status-rejected { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; font-weight: 600; }
    .badge-status-cancelled { background: #f5f5f5; color: #616161; border: 1px solid #e0e0e0; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="content container-fluid">

    {{-- Encabezado y Navegación --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-no-gutter mb-1">
                    <li class="breadcrumb-item"><a class="breadcrumb-link" href="{{ route('admin.customer-withdraw.index') }}">Retiros SPEI</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalle de Solicitud #{{ str_pad($withdraw->id, 5, '0', STR_PAD_LEFT) }}</li>
                </ol>
            </nav>
            <h1 class="page-header-title d-flex align-items-center">
                <span>Retiro Bancario #SPEI-{{ str_pad($withdraw->id, 5, '0', STR_PAD_LEFT) }}</span>
                <span class="ml-3">
                    @if($withdraw->status === 'pending')
                        <span class="badge badge-status-pending px-3 py-1 font-size-sm">
                            <i class="tio-time mr-1"></i> Pendiente de Dispersión
                        </span>
                    @elseif($withdraw->status === 'approved')
                        <span class="badge badge-status-approved px-3 py-1 font-size-sm">
                            <i class="tio-checkmark-circle-outlined mr-1"></i> Aprobado (En Proceso)
                        </span>
                    @elseif($withdraw->status === 'transferred')
                        <span class="badge badge-status-transferred px-3 py-1 font-size-sm">
                            <i class="tio-send mr-1"></i> Transferido vía SPEI
                        </span>
                    @elseif($withdraw->status === 'rejected')
                        <span class="badge badge-status-rejected px-3 py-1 font-size-sm">
                            <i class="tio-clear-circle mr-1"></i> Rechazado (Reembolsado)
                        </span>
                    @elseif($withdraw->status === 'cancelled')
                        <span class="badge badge-status-cancelled px-3 py-1 font-size-sm">
                            <i class="tio-close mr-1"></i> Cancelado por Cliente
                        </span>
                    @endif
                </span>
            </h1>
        </div>
        <div>
            <a href="{{ route('admin.customer-withdraw.index') }}" class="btn btn-outline-secondary">
                <i class="tio-chevron-left mr-1"></i> Volver a Retiros
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Columna Izquierda: Datos Bancarios y Acciones de Dispersión --}}
        <div class="col-lg-8">

            {{-- Tarjeta de Cuenta Bancaria Destino --}}
            <div class="card mb-4 border-primary shadow-sm">
                <div class="card-header bg-soft-primary">
                    <h5 class="card-title text-primary mb-0 font-weight-bold">
                        <i class="tio-bank mr-2"></i> Cuenta Bancaria de Destino (SPEI)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase d-block font-weight-bold">Institución Financiera</small>
                            <span class="h4 text-dark font-weight-bold mb-0">
                                {{ $withdraw->bankAccount->bank_name ?? 'No disponible' }}
                            </span>
                            <span class="badge badge-soft-info ml-2">Código Banxico: {{ $withdraw->bankAccount->bank_code ?? '---' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase d-block font-weight-bold">Titular de la Cuenta</small>
                            <span class="h4 text-dark font-weight-bold mb-0">
                                {{ $withdraw->bankAccount->account_holder ?? 'No disponible' }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted text-uppercase d-block font-weight-bold mb-1">
                            CLABE Interbancaria (18 Dígitos Estándar Banxico)
                        </small>
                        <div class="clabe-box">
                            <span id="clabeText">{{ $fullClabe ?? $withdraw->bankAccount->masked_clabe }}</span>
                            @if($fullClabe)
                            <button type="button" class="btn btn-sm btn-primary ml-3" onclick="copyClabe()">
                                <i class="tio-copy mr-1"></i> Copiar CLABE
                            </button>
                            @endif
                        </div>
                        <small class="text-success mt-1 d-block font-weight-bold">
                            <i class="tio-checkmark-circle mr-1"></i> Validada mediante algoritmo Módulo 10 de Banco de México y encriptada con AES-256.
                        </small>
                    </div>

                    <div class="row text-muted font-size-sm">
                        <div class="col-sm-6">
                            <i class="tio-lock mr-1"></i> Cuenta registrada el: <strong>{{ $withdraw->bankAccount->created_at ? $withdraw->bankAccount->created_at->format('d/m/Y H:i') : '—' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <i class="tio-shield mr-1"></i> Período de protección de 24h:
                            @if($withdraw->bankAccount->is_in_cooling_off)
                                <span class="text-warning font-weight-bold">Activo (Tiempo restante: {{ $withdraw->bankAccount->cooling_off_remaining_minutes }} min)</span>
                            @else
                                <span class="text-success font-weight-bold">Completado</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tarjeta de Desglose de Fondos --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0 font-weight-bold">
                        <i class="tio-dollar-outlined mr-2"></i> Desglose de Fondos de la Transferencia
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-sm-4 text-center border-right">
                            <small class="text-muted text-uppercase d-block">Monto Solicitado</small>
                            <span class="h2 text-dark font-weight-bold mb-0">${{ number_format($withdraw->amount, 2) }}</span>
                            <small class="text-muted d-block">MXN</small>
                        </div>
                        <div class="col-sm-4 text-center border-right">
                            <small class="text-muted text-uppercase d-block">Comisión por Retiro</small>
                            <span class="h2 text-muted font-weight-bold mb-0">${{ number_format($withdraw->fee, 2) }}</span>
                            <small class="text-muted d-block">Sin comisión (Tootli Free)</small>
                        </div>
                        <div class="col-sm-4 text-center">
                            <small class="text-muted text-uppercase d-block font-weight-bold text-success">Monto Neto a Dispersar</small>
                            <span class="h1 text-success font-weight-bold mb-0">${{ number_format($withdraw->net_amount, 2) }}</span>
                            <small class="text-muted d-block">Total en cuenta destino</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Acciones según Estado --}}
            @if(in_array($withdraw->status, ['pending', 'approved']))
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0 font-weight-bold">
                        <i class="tio-settings-outlined mr-2"></i> Procesamiento y Dispersión SPEI
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted font-size-sm mb-4">
                        Copie la CLABE interbancaria anterior y realice la transferencia desde el portal de banca empresarial de Tootli. Una vez emitida, ingrese la <strong>Clave de Rastreo SPEI</strong> de Banxico para notificar al cliente.
                    </p>

                    {{-- Formulario para Registrar Transferencia SPEI Exitosa --}}
                    <form action="{{ route('admin.customer-withdraw.mark-transferred', $withdraw->id) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <div class="form-group">
                            <label class="input-label font-weight-bold">
                                Clave de Rastreo SPEI / Folio Banxico <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="spei_tracking_key" class="form-control form-control-lg font-family-monospace" placeholder="Ej: 20260922123456789012345" required>
                            <small class="text-muted">Esta clave permite al cliente consultar el comprobante oficial CEP en Banxico.</small>
                        </div>

                        <div class="form-group">
                            <label class="input-label font-weight-bold">
                                Comprobante de Transferencia Bancaria (Opcional - PDF / JPG / PNG)
                            </label>
                            <div class="custom-file">
                                <input type="file" name="spei_proof" class="custom-file-input" id="speiProofInput" accept=".pdf,image/*">
                                <label class="custom-file-label" for="speiProofInput">Seleccionar archivo...</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#rejectModal">
                                <i class="tio-clear-circle mr-1"></i> Rechazar y Devolver Saldo
                            </button>

                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="tio-send mr-1"></i> Confirmar Transferencia SPEI Exitosa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @elseif($withdraw->status === 'transferred')
            <div class="card mb-4 border-success">
                <div class="card-header bg-soft-success">
                    <h5 class="card-title text-success mb-0 font-weight-bold">
                        <i class="tio-checkmark-circle mr-2"></i> Transferencia SPEI Completada Exitosamente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <small class="text-muted text-uppercase d-block font-weight-bold">Clave de Rastreo Banxico (SPEI)</small>
                            <span class="h3 font-family-monospace text-dark font-weight-bold d-block mt-1">
                                {{ $withdraw->spei_tracking_key }}
                            </span>
                            <small class="text-muted d-block mt-2">
                                Procesado por: <strong>{{ $withdraw->processor->f_name ?? 'Administrador' }} {{ $withdraw->processor->l_name ?? '' }}</strong> el {{ $withdraw->processed_at ? $withdraw->processed_at->format('d/m/Y H:i') : '' }} hrs.
                            </small>
                        </div>
                        <div class="col-md-5 text-md-right mt-3 mt-md-0">
                            @if($withdraw->spei_proof_url)
                                <a href="{{ asset('storage/app/public/customer_withdraws/' . $withdraw->spei_proof_url) }}" target="_blank" class="btn btn-primary">
                                    <i class="tio-download-to mr-1"></i> Ver Comprobante Bancario
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @elseif($withdraw->status === 'rejected')
            <div class="card mb-4 border-danger">
                <div class="card-header bg-soft-danger">
                    <h5 class="card-title text-danger mb-0 font-weight-bold">
                        <i class="tio-clear-circle mr-2"></i> Solicitud de Retiro Rechazada
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold text-dark">Motivo del Rechazo:</h6>
                    <p class="text-danger bg-light p-3 rounded border">
                        {{ $withdraw->rejection_reason }}
                    </p>
                    <div class="alert alert-soft-success d-flex align-items-center mb-0">
                        <i class="tio-checkmark-circle font-size-xl mr-2 text-success"></i>
                        <span>El monto íntegro de <strong>${{ number_format($withdraw->amount, 2) }} MXN</strong> fue reembolsado automáticamente a la billetera Tootli del cliente.</span>
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- Columna Derecha: Información del Cliente y Auditoría --}}
        <div class="col-lg-4">

            {{-- Tarjeta del Cliente --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0 font-weight-bold">
                        <i class="tio-user-outlined mr-2"></i> Información del Cliente
                    </h5>
                </div>
                <div class="card-body">
                    @if($withdraw->user)
                    <div class="text-center mb-3">
                        <div class="avatar avatar-xl avatar-circle mb-2 mx-auto">
                            <img class="avatar-img" src="{{ $withdraw->user->image_full_url }}" onerror="this.src='{{ asset('public/assets/admin/img/160x160/img1.jpg') }}'">
                        </div>
                        <h4 class="mb-0 font-weight-bold">{{ $withdraw->user->f_name }} {{ $withdraw->user->l_name }}</h4>
                        <span class="text-muted font-size-sm">{{ $withdraw->user->email }}</span>
                    </div>

                    <ul class="list-unstyled mb-0 font-size-sm">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Teléfono:</span>
                            <span class="font-weight-bold text-dark">{{ $withdraw->user->phone }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Verificación KYC (MetaMap):</span>
                            <span>
                                @if($withdraw->user->identity_verified === 'approved')
                                    <span class="badge badge-success">
                                        <i class="tio-checkmark-circle mr-1"></i> Aprobado
                                    </span>
                                @else
                                    <span class="badge badge-warning text-dark">
                                        Básico (Sin verificar)
                                    </span>
                                @endif
                            </span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Saldo Actual en Billetera:</span>
                            <span class="font-weight-bold text-success font-size-md">
                                ${{ number_format($withdraw->user->wallet_balance, 2) }} MXN
                            </span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Cliente desde:</span>
                            <span class="text-dark">{{ $withdraw->user->created_at ? $withdraw->user->created_at->format('d/m/Y') : '—' }}</span>
                        </li>
                    </ul>
                    @else
                    <p class="text-muted mb-0">Información de cliente no disponible.</p>
                    @endif
                </div>
            </div>

            {{-- Tarjeta de Auditoría y Seguridad --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0 font-weight-bold">
                        <i class="tio-security-on mr-2"></i> Auditoría y Seguridad
                    </h5>
                </div>
                <div class="card-body font-size-sm">
                    @php $audit = $withdraw->audit_metadata ?? []; @endphp
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Dirección IP:</span>
                            <span class="font-family-monospace font-weight-bold text-dark">{{ $audit['ip'] ?? '—' }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Saldo previo al retiro:</span>
                            <span class="font-weight-bold text-dark">${{ number_format($audit['balance_before'] ?? 0, 2) }} MXN</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Saldo posterior:</span>
                            <span class="font-weight-bold text-dark">${{ number_format($audit['balance_after'] ?? 0, 2) }} MXN</span>
                        </li>
                        <li class="py-2">
                            <span class="text-muted d-block mb-1">Dispositivo / Agente:</span>
                            <small class="text-muted text-break">{{ $audit['user_agent'] ?? 'Aplicación Móvil Tootli' }}</small>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modal de Rechazo y Devolución de Fondos --}}
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.customer-withdraw.reject', $withdraw->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold text-white">
                        <i class="tio-warning-outlined mr-2"></i> Rechazar Solicitud de Retiro
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-soft-warning mb-3">
                        <i class="tio-info-outined mr-1"></i>
                        Al rechazar esta solicitud, los <strong>${{ number_format($withdraw->amount, 2) }} MXN</strong> serán <strong>reembolsados inmediatamente</strong> a la billetera del cliente.
                    </div>
                    <div class="form-group">
                        <label class="input-label font-weight-bold">
                            Motivo del Rechazo <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason" class="form-control" rows="4" placeholder="Ej: La cuenta CLABE fue rechazada por el banco receptor debido a cuenta inactiva o titular no coincidente..." required minlength="5"></textarea>
                        <small class="text-muted">Este motivo será visible para el usuario en su historial de la aplicación.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger font-weight-bold">
                        <i class="tio-clear-circle mr-1"></i> Confirmar Rechazo y Reembolsar Saldo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
<script>
    function copyClabe() {
        const clabe = document.getElementById('clabeText').innerText.trim();
        navigator.clipboard.writeText(clabe).then(() => {
            toastr.success('CLABE ' + clabe + ' copiada al portapapeles.');
        }).catch(err => {
            toastr.error('Error al copiar la CLABE.');
        });
    }

    // Display filename on custom-file-input
    document.getElementById('speiProofInput')?.addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : 'Seleccionar archivo...';
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
</script>
@endpush
@endsection
