@extends('layouts.admin.app')

@section('title', 'Tootli Protector — Detalle de Disputa #DISP-' . str_pad($dispute->id, 5, '0', STR_PAD_LEFT))

@push('css_or_js')
<style>
    .protector-header { background: linear-gradient(135deg, #063826 0%, #0E4D34 100%); color: #fff; border-radius: 10px; padding: 20px 24px; margin-bottom: 24px; }
    .evidence-thumb { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid #e7eaf3; cursor: pointer; transition: transform 0.2s; }
    .evidence-thumb:hover { transform: scale(1.05); }
    .resolution-card { border: 2px dashed #0E4D34; background: #f4fbf7; border-radius: 10px; }
</style>
@endpush

@section('content')
<div class="content container-fluid">

    {{-- Header --}}
    <div class="page-header mb-3">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item"><a class="breadcrumb-link" href="{{ route('admin.tootli-protector.disputes.index') }}">Tootli Protector</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Caso #DISP-{{ str_pad($dispute->id, 5, '0', STR_PAD_LEFT) }}</li>
                    </ol>
                </nav>
                <h1 class="page-header-title">
                    <i class="tio-shield-check mr-2 text-success"></i>
                    Expediente de Disputa #DISP-{{ str_pad($dispute->id, 5, '0', STR_PAD_LEFT) }}
                </h1>
            </div>
            <div class="col-sm-auto">
                <a href="{{ route('admin.tootli-protector.disputes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="tio-chevron-left mr-1"></i> Volver a la lista
                </a>
            </div>
        </div>
    </div>

    @php
        $isService = ($dispute->disputable_type === 'App\Models\ServiceJob');
        $amount = 0;
        if ($isService && $dispute->disputable) {
            $amount = $dispute->disputable->acceptedBid ? $dispute->disputable->acceptedBid->price : $dispute->disputable->budget;
        } elseif ($dispute->disputable) {
            $amount = $dispute->disputable->total_amount ?? $dispute->disputable->amount;
        }
    @endphp

    <div class="row g-3">
        {{-- Columna Izquierda: Información del Caso y Evidencias --}}
        <div class="col-lg-8">

            {{-- Resumen del Problema Reportado --}}
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="tio-report-problem mr-2 text-danger"></i>
                        Reclamo del Demandante
                    </h5>
                    <span class="badge badge-soft-danger font-weight-bold">
                        Motivo: {{ $dispute->reason }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted text-uppercase mb-1 font-size-xs">Descripción Detallada del Problema:</h6>
                        <div class="p-3 bg-light rounded text-dark" style="white-space: pre-wrap; font-size: 14px; line-height: 1.6;">{{ $dispute->description }}</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="p-2 border rounded">
                                <small class="text-muted d-block font-weight-bold text-uppercase font-size-xs">Solución Requerida por el Cliente:</small>
                                <span class="font-weight-bold text-dark font-size-md">
                                    @if($dispute->requested_solution === 'full_refund')
                                        <i class="tio-money-vs text-danger mr-1"></i> Reembolso Total (100%)
                                    @elseif($dispute->requested_solution === 'partial_refund')
                                        <i class="tio-money text-warning mr-1"></i> Reembolso Parcial Acordado
                                    @else
                                        <i class="tio-wrench text-info mr-1"></i> Corrección o Finalización del Trabajo
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 border rounded">
                                <small class="text-muted d-block font-weight-bold text-uppercase font-size-xs">Fecha de Apertura:</small>
                                <span class="font-weight-bold text-dark font-size-md">
                                    <i class="tio-calendar text-muted mr-1"></i> {{ $dispute->created_at->format('d/m/Y \a \l\a\s H:i \h\r\s') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Evidencias Fotográficas --}}
                    <div>
                        <h6 class="text-muted text-uppercase mb-2 font-size-xs">Evidencias Adjuntadas ({{ count($dispute->evidence_photos ?? []) }} fotos):</h6>
                        @if(!empty($dispute->evidence_photos) && is_array($dispute->evidence_photos))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($dispute->evidence_full_urls as $photoUrl)
                                    <a href="{{ $photoUrl }}" target="_blank" title="Ver evidencia en tamaño completo">
                                        <img src="{{ $photoUrl }}" class="evidence-thumb mr-2 mb-2" alt="Evidencia de disputa">
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0 font-italic">El usuario no adjuntó fotografías adicionales en esta disputa.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Detalle del Servicio / Transacción Protegida --}}
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="tio-receipt mr-2 text-primary"></i>
                        @if($isService)
                            Información del Trabajo de Servicio Contratado
                        @else
                            Información del Producto y Transacción P2P
                        @endif
                    </h5>
                    <span class="badge badge-soft-info">
                        {{ $isService ? 'Trabajo #' . $dispute->disputable_id : 'Transacción #' . $dispute->disputable_id }}
                    </span>
                </div>
                <div class="card-body">
                    @if($isService && $dispute->disputable)
                        @php($job = $dispute->disputable)
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <span class="text-muted d-block font-size-xs text-uppercase">Título del Trabajo:</span>
                                <span class="font-weight-bold text-dark">{{ $job->title }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted d-block font-size-xs text-uppercase">Categoría:</span>
                                <span class="badge badge-soft-dark">{{ $job->category->name ?? 'General' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted d-block font-size-xs text-uppercase">Estado del Servicio:</span>
                                <span class="badge badge-soft-warning">{{ $job->status }}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted d-block font-size-xs text-uppercase">Detalle del requerimiento:</span>
                            <p class="text-muted mb-0 bg-light p-2 rounded">{{ $job->description }}</p>
                        </div>
                        @if($job->acceptedBid)
                            <div class="p-3 border rounded bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 font-weight-bold text-dark">Oferta Aceptada — {{ $job->acceptedBid->store->name ?? 'Prestador' }}</h6>
                                        <small class="text-muted">{{ $job->acceptedBid->notes ?? 'Sin observaciones adicionales' }}</small>
                                    </div>
                                    <div class="text-right">
                                        <span class="h4 text-success font-weight-bold mb-0">${{ number_format($job->acceptedBid->price, 2) }}</span>
                                        <small class="d-block text-muted">Precio pactado</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @elseif($dispute->disputable)
                        @php($tx = $dispute->disputable)
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <span class="text-muted d-block font-size-xs text-uppercase">Concepto / Producto:</span>
                                <span class="font-weight-bold text-dark">{{ $tx->item_name ?? 'Producto acordado' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted d-block font-size-xs text-uppercase">Monto Acordado:</span>
                                <span class="font-weight-bold text-dark">${{ number_format($tx->amount, 2) }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted d-block font-size-xs text-uppercase">Comisión Protección:</span>
                                <span class="font-weight-bold text-success">${{ number_format($tx->protection_fee, 2) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Formulario de Resolución / Arbitraje --}}
            @if(in_array($dispute->status, ['open', 'under_review']))
                <div class="card shadow-sm border-0 resolution-card">
                    <div class="card-header bg-transparent border-bottom">
                        <h5 class="card-title mb-0 text-success font-weight-bold">
                            <i class="tio-gavel mr-1"></i> Dictamen y Resolución Final de Mediación
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.tootli-protector.disputes.resolve', $dispute->id) }}" method="POST" id="resolveDisputeForm">
                            @csrf

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark">Selecciona el Veredicto:</label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="custom-control custom-radio custom-control-inline border rounded p-3 w-100 bg-white">
                                            <input type="radio" id="res_buyer" name="resolution" value="refund_buyer" class="custom-control-input" checked onchange="togglePartialAmount(false)">
                                            <label class="custom-control-label text-dark font-weight-bold" for="res_buyer">
                                                Reembolso 100% al Comprador
                                                <small class="d-block text-muted font-weight-normal mt-1">Los fondos retenidos se regresan a la billetera del cliente.</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="custom-control custom-radio custom-control-inline border rounded p-3 w-100 bg-white">
                                            <input type="radio" id="res_seller" name="resolution" value="payout_seller" class="custom-control-input" onchange="togglePartialAmount(false)">
                                            <label class="custom-control-label text-dark font-weight-bold" for="res_seller">
                                                Liberar 100% al Prestador / Vendedor
                                                <small class="d-block text-muted font-weight-normal mt-1">Se determina que el servicio o producto fue entregado conforme.</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="custom-control custom-radio custom-control-inline border rounded p-3 w-100 bg-white">
                                            <input type="radio" id="res_partial" name="resolution" value="partial_refund" class="custom-control-input" onchange="togglePartialAmount(true)">
                                            <label class="custom-control-label text-dark font-weight-bold" for="res_partial">
                                                Acuerdo Parcial / Split
                                                <small class="d-block text-muted font-weight-normal mt-1">Se reembolsa una fracción al cliente y la diferencia al prestador.</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3 d-none" id="partialAmountContainer">
                                <label class="font-weight-bold text-dark">Monto a reembolsar al comprador ($):</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                    <input type="number" step="0.01" max="{{ $amount }}" min="1" name="refund_amount" id="refund_amount" class="form-control" placeholder="Ej. {{ number_format($amount / 2, 2) }}">
                                </div>
                                <small class="text-muted">El restante se transferirá automáticamente a la contraparte.</small>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-dark">Fundamento y Notas de la Resolución (Obligatorio):</label>
                                <textarea name="resolution_notes" class="form-control" rows="4" required placeholder="Explica detalladamente las razones por las cuales se determinó esta resolución (se registrará en el historial de arbitraje)..."></textarea>
                            </div>

                            <div class="text-right">
                                <button type="button" class="btn btn-outline-secondary mr-2" data-toggle="modal" data-target="#underReviewModal">
                                    <i class="tio-time mr-1"></i> Mantener en Revisión
                                </button>
                                <button type="submit" class="btn btn-success px-4" onclick="return confirm('¿Estás seguro de emitir este dictamen final? Se aplicarán las transferencias monetarias de inmediato.');">
                                    <i class="tio-checkmark-circle mr-1"></i> Aplicar Dictamen Final
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                {{-- Caso ya Dictaminado o Cancelado --}}
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0 text-success font-weight-bold">
                            <i class="tio-checkmark-circle mr-1"></i> Expediente Cerrado y Resuelto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <span class="text-muted d-block font-size-xs text-uppercase">Dictamen:</span>
                                <span class="badge badge-soft-success font-size-md p-1 px-2 font-weight-bold">{{ $dispute->status }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted d-block font-size-xs text-uppercase">Dictaminado Por:</span>
                                <span class="font-weight-bold text-dark">{{ $dispute->resolver->f_name ?? 'Administrador Tootli' }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted d-block font-size-xs text-uppercase">Fecha de Resolución:</span>
                                <span class="font-weight-bold text-dark">{{ $dispute->resolved_at ? $dispute->resolved_at->format('d/m/Y H:i') : '—' }}</span>
                            </div>
                        </div>

                        <div class="mb-0">
                            <span class="text-muted d-block font-size-xs text-uppercase mb-1">Notas Oficiales del Dictamen:</span>
                            <div class="p-3 bg-white border rounded text-dark" style="white-space: pre-wrap;">{{ $dispute->resolution_notes ?? 'Sin observaciones registradas.' }}</div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- Columna Derecha: Participantes y Fondos en Custodia --}}
        <div class="col-lg-4">

            {{-- Fondos en Custodia Escrow --}}
            <div class="card mb-3 shadow-sm border-success">
                <div class="card-body text-center py-4">
                    <span class="badge badge-soft-success font-weight-bold mb-2">
                        <i class="tio-lock mr-1"></i> Tootli Escrow Activo
                    </span>
                    <h2 class="text-dark font-weight-bold mb-1">${{ number_format($amount, 2) }}</h2>
                    <small class="text-muted d-block">Fondos en custodia garantizada</small>
                </div>
            </div>

            {{-- Partes Involucradas --}}
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-light py-3">
                    <h5 class="card-title mb-0">Partes Involucradas</h5>
                </div>
                <div class="card-body">
                    {{-- Demandante --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge badge-soft-danger">Demandante</span>
                            <small class="text-muted">Cliente / Comprador</small>
                        </div>
                        @if($dispute->claimant)
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md avatar-circle mr-3">
                                    <img class="avatar-img" src="{{ asset('storage/app/public/profile/' . $dispute->claimant->image) }}"
                                         onerror="this.src='{{ asset('assets/admin/img/160x160/img1.jpg') }}'" alt="Avatar">
                                </div>
                                <div>
                                    <h6 class="mb-0 font-weight-bold text-dark">{{ $dispute->claimant->f_name }} {{ $dispute->claimant->l_name }}</h6>
                                    <small class="text-muted d-block"><i class="tio-call mr-1"></i> {{ $dispute->claimant->phone }}</small>
                                    <small class="text-muted d-block"><i class="tio-email mr-1"></i> {{ $dispute->claimant->email }}</small>
                                </div>
                            </div>
                        @endif
                    </div>

                    <hr>

                    {{-- Contraparte --}}
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge badge-soft-primary">Contraparte</span>
                            <small class="text-muted">{{ $isService ? 'Prestador Aliado' : 'Vendedor P2P' }}</small>
                        </div>
                        @if($dispute->defendant)
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md avatar-circle mr-3">
                                    <img class="avatar-img" src="{{ asset('storage/app/public/profile/' . $dispute->defendant->image) }}"
                                         onerror="this.src='{{ asset('assets/admin/img/160x160/img1.jpg') }}'" alt="Avatar">
                                </div>
                                <div>
                                    <h6 class="mb-0 font-weight-bold text-dark">{{ $dispute->defendant->f_name }} {{ $dispute->defendant->l_name }}</h6>
                                    <small class="text-muted d-block"><i class="tio-call mr-1"></i> {{ $dispute->defendant->phone }}</small>
                                    <small class="text-muted d-block"><i class="tio-email mr-1"></i> {{ $dispute->defendant->email }}</small>
                                </div>
                            </div>
                        @elseif($isService && $dispute->disputable && $dispute->disputable->acceptedBid && $dispute->disputable->acceptedBid->store)
                            @php($store = $dispute->disputable->acceptedBid->store)
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md avatar-circle mr-3">
                                    <img class="avatar-img" src="{{ asset('storage/app/public/store/' . $store->logo) }}"
                                         onerror="this.src='{{ asset('assets/admin/img/160x160/img2.jpg') }}'" alt="Avatar">
                                </div>
                                <div>
                                    <h6 class="mb-0 font-weight-bold text-dark">{{ $store->name }}</h6>
                                    <small class="text-muted d-block"><i class="tio-call mr-1"></i> {{ $store->phone }}</small>
                                    <small class="text-muted d-block"><i class="tio-email mr-1"></i> {{ $store->email }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- Modal Cambiar Estado a Bajo Revisión --}}
<div class="modal fade" id="underReviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.tootli-protector.disputes.update-status', $dispute->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Actualizar Estado a Mediación / Revisión</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" value="under_review">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Nota de seguimiento interna (opcional):</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Ej. Se solicitó al prestador fotos complementarias de la entrega del trabajo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Seguimiento</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('script_2')
<script>
    function togglePartialAmount(isPartial) {
        const container = document.getElementById('partialAmountContainer');
        const input = document.getElementById('refund_amount');
        if (isPartial) {
            container.classList.remove('d-none');
            input.required = true;
        } else {
            container.classList.add('d-none');
            input.required = false;
        }
    }
</script>
@endpush
@endsection
