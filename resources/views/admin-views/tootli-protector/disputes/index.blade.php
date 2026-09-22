@extends('layouts.admin.app')

@section('title', 'Tootli Protector — Panel de Disputas')

@push('css_or_js')
<style>
    .badge-status-open { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; font-weight: 600; }
    .badge-status-review { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; font-weight: 600; }
    .badge-status-resolved { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; font-weight: 600; }
    .badge-status-cancelled { background: #f5f5f5; color: #616161; border: 1px solid #e0e0e0; font-weight: 600; }
    .protector-header { background: linear-gradient(135deg, #063826 0%, #0E4D34 100%); color: #fff; border-radius: 10px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(14, 77, 52, 0.15); }
    .stat-card-clean { border-radius: 10px; border: 1px solid #e7eaf3; box-shadow: 0 2px 6px rgba(0,0,0,0.02); transition: all 0.2s ease; }
    .stat-card-clean:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.06); }
</style>
@endpush

@section('content')
<div class="content container-fluid">

    {{-- Banner de Encabezado Tootli Protector --}}
    <div class="protector-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge badge-pill badge-warning text-dark font-weight-bold px-3 py-1 mr-2">
                        <i class="tio-shield-check mr-1"></i> Garantía Tootli Protector
                    </span>
                    <span class="text-white-50 text-sm">Escrow & Centro de Resolución</span>
                </div>
                <h2 class="text-white mb-1 font-weight-bold">Centro de Disputas y Arbitraje</h2>
                <p class="text-white-50 mb-0">
                    Gestiona mediaciones entre clientes, prestadores de servicios y compras de productos protegidos. Evalúa evidencias y dictamina reembolsos o liberaciones de fondos.
                </p>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <div class="d-inline-block text-left bg-white text-dark p-3 rounded shadow-sm">
                    <small class="text-muted d-block font-weight-bold text-uppercase">Casos que requieren atención</small>
                    <span class="h2 text-danger font-weight-bold mb-0">{{ $counts['open'] + $counts['under_review'] }}</span>
                    <span class="text-xs text-muted ml-1">pendientes</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Métricas Rápidas --}}
    <div class="row g-2 mb-4">
        <div class="col-sm-6 col-lg-3">
            <a class="card stat-card-clean card-hover-shadow h-100 text-decoration-none" href="{{ route('admin.tootli-protector.disputes.index', ['status' => 'all']) }}">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted text-uppercase mb-1">Total Disputas</h6>
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="h2 font-weight-bold text-dark mb-0">{{ $counts['all'] }}</span>
                        </div>
                        <div class="col-4 text-right">
                            <span class="btn btn-icon btn-soft-secondary rounded-circle">
                                <i class="tio-layers font-size-xl"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a class="card stat-card-clean card-hover-shadow h-100 text-decoration-none" href="{{ route('admin.tootli-protector.disputes.index', ['status' => 'open']) }}">
                <div class="card-body">
                    <h6 class="card-subtitle text-danger text-uppercase mb-1">Abiertas / Nuevas</h6>
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="h2 font-weight-bold text-danger mb-0">{{ $counts['open'] }}</span>
                        </div>
                        <div class="col-4 text-right">
                            <span class="btn btn-icon btn-soft-danger rounded-circle">
                                <i class="tio-alert-triangle font-size-xl"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a class="card stat-card-clean card-hover-shadow h-100 text-decoration-none" href="{{ route('admin.tootli-protector.disputes.index', ['status' => 'under_review']) }}">
                <div class="card-body">
                    <h6 class="card-subtitle text-primary text-uppercase mb-1">En Revisión</h6>
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="h2 font-weight-bold text-primary mb-0">{{ $counts['under_review'] }}</span>
                        </div>
                        <div class="col-4 text-right">
                            <span class="btn btn-icon btn-soft-primary rounded-circle">
                                <i class="tio-refresh font-size-xl"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a class="card stat-card-clean card-hover-shadow h-100 text-decoration-none" href="{{ route('admin.tootli-protector.disputes.index', ['status' => 'resolved']) }}">
                <div class="card-body">
                    <h6 class="card-subtitle text-success text-uppercase mb-1">Dictaminadas</h6>
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="h2 font-weight-bold text-success mb-0">{{ $counts['resolved'] }}</span>
                        </div>
                        <div class="col-4 text-right">
                            <span class="btn btn-icon btn-soft-success rounded-circle">
                                <i class="tio-checkmark-circle font-size-xl"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Tarjeta Principal con Filtros y Tabla --}}
    <div class="card shadow-sm border-0">
        <div class="card-header py-3 flex-wrap gap-3 justify-content-between align-items-center">
            {{-- Tabs de Estado --}}
            <ul class="nav nav-tabs card-header-tabs border-bottom-0">
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" href="{{ route('admin.tootli-protector.disputes.index', ['status' => 'all', 'type' => $type, 'search' => $search]) }}">
                        Todos <span class="badge badge-soft-dark badge-pill ml-1">{{ $counts['all'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'open' ? 'active' : '' }}" href="{{ route('admin.tootli-protector.disputes.index', ['status' => 'open', 'type' => $type, 'search' => $search]) }}">
                        Abiertas <span class="badge badge-soft-danger badge-pill ml-1">{{ $counts['open'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'under_review' ? 'active' : '' }}" href="{{ route('admin.tootli-protector.disputes.index', ['status' => 'under_review', 'type' => $type, 'search' => $search]) }}">
                        En Mediación <span class="badge badge-soft-primary badge-pill ml-1">{{ $counts['under_review'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'resolved' ? 'active' : '' }}" href="{{ route('admin.tootli-protector.disputes.index', ['status' => 'resolved', 'type' => $type, 'search' => $search]) }}">
                        Resueltas <span class="badge badge-soft-success badge-pill ml-1">{{ $counts['resolved'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'cancelled' ? 'active' : '' }}" href="{{ route('admin.tootli-protector.disputes.index', ['status' => 'cancelled', 'type' => $type, 'search' => $search]) }}">
                        Canceladas <span class="badge badge-soft-secondary badge-pill ml-1">{{ $counts['cancelled'] }}</span>
                    </a>
                </li>
            </ul>

            {{-- Formulario de Búsqueda y Filtro de Tipo --}}
            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('admin.tootli-protector.disputes.index') }}" method="GET" class="d-flex align-items-center">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <select name="type" class="custom-select custom-select-sm mr-2" onchange="this.form.submit()">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Todos los Tipos</option>
                        <option value="service" {{ $type === 'service' ? 'selected' : '' }}>Servicios Profesionales</option>
                        <option value="transaction" {{ $type === 'transaction' ? 'selected' : '' }}>Compras P2P</option>
                    </select>

                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="tio-search"></i>
                            </div>
                        </div>
                        <input type="search" name="search" class="form-control form-control-sm" placeholder="Buscar por ID, nombre..." value="{{ $search }}">
                        @if(!empty($search))
                            <div class="input-group-append">
                                <a href="{{ route('admin.tootli-protector.disputes.index', ['status' => $status, 'type' => $type]) }}" class="btn btn-sm btn-light">
                                    <i class="tio-clear"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabla de Disputas --}}
        <div class="table-responsive datatable-custom">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                    <tr>
                        <th>Caso / ID</th>
                        <th>Tipo</th>
                        <th>Demandante</th>
                        <th>Contraparte</th>
                        <th>Motivo / Solución</th>
                        <th>Monto Custodia</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disputes as $dispute)
                        @php
                            $isService = ($dispute->disputable_type === 'App\Models\ServiceJob');
                            $amount = 0;
                            if ($isService && $dispute->disputable) {
                                $amount = $dispute->disputable->acceptedBid ? $dispute->disputable->acceptedBid->price : $dispute->disputable->budget;
                            } elseif ($dispute->disputable) {
                                $amount = $dispute->disputable->total_amount ?? $dispute->disputable->amount;
                            }
                        @endphp
                        <tr>
                            <td>
                                <a class="font-weight-bold text-dark" href="{{ route('admin.tootli-protector.disputes.show', $dispute->id) }}">
                                    #DISP-{{ str_pad($dispute->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                                <small class="d-block text-muted">
                                    Ref: {{ $isService ? 'Job #' . $dispute->disputable_id : 'Tx #' . $dispute->disputable_id }}
                                </small>
                            </td>

                            <td>
                                @if($isService)
                                    <span class="badge badge-soft-info p-1 px-2 font-weight-normal">
                                        <i class="tio-wrench mr-1"></i> Servicio
                                    </span>
                                @else
                                    <span class="badge badge-soft-success p-1 px-2 font-weight-normal">
                                        <i class="tio-shopping-basket mr-1"></i> Compra P2P
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($dispute->claimant)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs avatar-circle mr-2">
                                            <img class="avatar-img" src="{{ asset('storage/app/public/profile/' . $dispute->claimant->image) }}"
                                                 onerror="this.src='{{ asset('assets/admin/img/160x160/img1.jpg') }}'" alt="Avatar">
                                        </div>
                                        <div>
                                            <span class="d-block font-weight-medium text-dark">{{ $dispute->claimant->f_name }} {{ $dispute->claimant->l_name }}</span>
                                            <small class="text-muted">{{ $dispute->claimant->phone }}</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">No disponible</span>
                                @endif
                            </td>

                            <td>
                                @if($dispute->defendant)
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <span class="d-block font-weight-medium text-dark">{{ $dispute->defendant->f_name }} {{ $dispute->defendant->l_name }}</span>
                                            <small class="text-muted">{{ $dispute->defendant->phone }}</small>
                                        </div>
                                    </div>
                                @elseif($isService && $dispute->disputable && $dispute->disputable->acceptedBid && $dispute->disputable->acceptedBid->store)
                                    <div>
                                        <span class="d-block font-weight-medium text-dark">{{ $dispute->disputable->acceptedBid->store->name }}</span>
                                        <small class="badge badge-soft-primary">Prestador Aliado</small>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td>
                                <span class="d-block text-truncate font-weight-medium" style="max-width: 220px;" title="{{ $dispute->reason }}">
                                    {{ $dispute->reason }}
                                </span>
                                <small class="text-muted">
                                    Pide: 
                                    @if($dispute->requested_solution === 'full_refund')
                                        <span class="text-danger font-weight-bold">Reembolso 100%</span>
                                    @elseif($dispute->requested_solution === 'partial_refund')
                                        <span class="text-warning font-weight-bold">Reembolso parcial</span>
                                    @else
                                        <span>Corrección</span>
                                    @endif
                                </small>
                            </td>

                            <td>
                                <span class="font-weight-bold text-dark">
                                    ${{ number_format($amount, 2) }}
                                </span>
                                <small class="d-block text-muted">Retenido en Escrow</small>
                            </td>

                            <td>
                                @if($dispute->status === 'open')
                                    <span class="badge badge-status-open px-2 py-1">Abierta</span>
                                @elseif($dispute->status === 'under_review')
                                    <span class="badge badge-status-review px-2 py-1">En Revisión</span>
                                @elseif($dispute->status === 'resolved_buyer_refund')
                                    <span class="badge badge-status-resolved px-2 py-1">Reembolsado Comprador</span>
                                @elseif($dispute->status === 'resolved_seller_payout')
                                    <span class="badge badge-status-resolved px-2 py-1">Pagado a Prestador</span>
                                @elseif($dispute->status === 'resolved_partial')
                                    <span class="badge badge-status-resolved px-2 py-1">Acuerdo Parcial</span>
                                @elseif($dispute->status === 'cancelled')
                                    <span class="badge badge-status-cancelled px-2 py-1">Cancelada</span>
                                @else
                                    <span class="badge badge-soft-secondary px-2 py-1">{{ $dispute->status }}</span>
                                @endif
                            </td>

                            <td>
                                <span class="text-muted">{{ $dispute->created_at->format('d/m/Y H:i') }}</span>
                            </td>

                            <td class="text-center">
                                <a href="{{ route('admin.tootli-protector.disputes.show', $dispute->id) }}" class="btn btn-sm btn-outline-primary px-3">
                                    <i class="tio-edit mr-1"></i> Mediar Caso
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <img src="{{ asset('assets/admin/svg/illustrations/sorry.svg') }}" alt="Empty" style="width: 80px;" class="mb-3">
                                <h5 class="text-muted">No se encontraron disputas registradas</h5>
                                <p class="text-muted text-sm">Todas las transacciones y servicios protegidos se encuentran en orden.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($disputes->hasPages())
            <div class="card-footer py-2">
                <div class="d-flex justify-content-center justify-content-sm-end">
                    {!! $disputes->links() !!}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
