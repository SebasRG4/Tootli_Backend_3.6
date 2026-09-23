@extends('layouts.admin.app')

@section('title', 'Tootli Wallet — Retiros a Cuentas Bancarias (SPEI)')

@push('css_or_js')
<style>
    .badge-status-pending { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; font-weight: 600; }
    .badge-status-approved { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; font-weight: 600; }
    .badge-status-transferred { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; font-weight: 600; }
    .badge-status-rejected { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; font-weight: 600; }
    .badge-status-cancelled { background: #f5f5f5; color: #616161; border: 1px solid #e0e0e0; font-weight: 600; }
    .spei-header { background: linear-gradient(135deg, #0b2545 0%, #134074 100%); color: #fff; border-radius: 10px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(11, 37, 69, 0.15); }
    .stat-card-clean { border-radius: 10px; border: 1px solid #e7eaf3; box-shadow: 0 2px 6px rgba(0,0,0,0.02); transition: all 0.2s ease; }
    .stat-card-clean:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.06); }
</style>
@endpush

@section('content')
<div class="content container-fluid">

    {{-- Banner de Encabezado Fintech --}}
    <div class="spei-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge badge-pill badge-info text-white font-weight-bold px-3 py-1 mr-2">
                        <i class="tio-account-square-outlined mr-1"></i> Tootli Wallet Fintech
                    </span>
                    <span class="text-white-50 text-sm">Dispersión de Fondos a Clientes (SPEI / CLABE)</span>
                </div>
                <h2 class="text-white mb-1 font-weight-bold">Centro de Retiros Bancarios SPEI</h2>
                <p class="text-white-50 mb-0">
                    Gestiona las solicitudes de transferencia bancaria de saldos generados por ventas con Tootli Protector, reembolsos de disputas y saldos a favor de clientes.
                </p>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <div class="d-inline-block text-left bg-white text-dark p-3 rounded shadow-sm">
                    <small class="text-muted d-block font-weight-bold text-uppercase">Por dispersar</small>
                    <span class="h2 text-warning font-weight-bold mb-0">${{ number_format($counters['pending_amount'], 2) }}</span>
                    <span class="text-xs text-muted ml-1">MXN ({{ $counters['pending'] }} pendientes)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Métricas Rápidas --}}
    <div class="row g-2 mb-4">
        <div class="col-sm-6 col-lg-3">
            <a class="card stat-card-clean card-hover-shadow h-100 text-decoration-none" href="{{ route('admin.customer-withdraw.index', ['status' => 'pending']) }}">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted text-uppercase mb-1">Pendientes de Dispersión</h6>
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="h2 font-weight-bold text-warning mb-0">{{ $counters['pending'] }}</span>
                            <span class="d-block text-xs text-muted">${{ number_format($counters['pending_amount'], 2) }} MXN</span>
                        </div>
                        <div class="col-4 text-right">
                            <span class="btn btn-icon btn-soft-warning rounded-circle">
                                <i class="tio-time font-size-xl"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a class="card stat-card-clean card-hover-shadow h-100 text-decoration-none" href="{{ route('admin.customer-withdraw.index', ['status' => 'approved']) }}">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted text-uppercase mb-1">En Proceso / Aprobados</h6>
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="h2 font-weight-bold text-primary mb-0">{{ $counters['approved'] }}</span>
                            <span class="d-block text-xs text-muted">Listos para banca en línea</span>
                        </div>
                        <div class="col-4 text-right">
                            <span class="btn btn-icon btn-soft-primary rounded-circle">
                                <i class="tio-checkmark-circle-outlined font-size-xl"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a class="card stat-card-clean card-hover-shadow h-100 text-decoration-none" href="{{ route('admin.customer-withdraw.index', ['status' => 'transferred']) }}">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted text-uppercase mb-1">Transferidos (SPEI)</h6>
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="h2 font-weight-bold text-success mb-0">{{ $counters['transferred'] }}</span>
                            <span class="d-block text-xs text-muted">${{ number_format($counters['transferred_amount'], 2) }} MXN</span>
                        </div>
                        <div class="col-4 text-right">
                            <span class="btn btn-icon btn-soft-success rounded-circle">
                                <i class="tio-send font-size-xl"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a class="card stat-card-clean card-hover-shadow h-100 text-decoration-none" href="{{ route('admin.customer-withdraw.index', ['status' => 'rejected']) }}">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted text-uppercase mb-1">Rechazados / Reembolsados</h6>
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="h2 font-weight-bold text-danger mb-0">{{ $counters['rejected'] }}</span>
                            <span class="d-block text-xs text-muted">Saldo devuelto al cliente</span>
                        </div>
                        <div class="col-4 text-right">
                            <span class="btn btn-icon btn-soft-danger rounded-circle">
                                <i class="tio-clear-circle-outlined font-size-xl"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Tarjeta Principal con Tabla y Filtros --}}
    <div class="card">
        <div class="card-header border-0 py-3">
            <div class="row align-items-center flex-grow-1">
                <div class="col-md-6 mb-2 mb-md-0">
                    {{-- Pestañas de Estado --}}
                    <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                        <a href="{{ route('admin.customer-withdraw.index', ['status' => 'all']) }}"
                           class="btn btn-xs {{ $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Todos ({{ $counters['all'] }})
                        </a>
                        <a href="{{ route('admin.customer-withdraw.index', ['status' => 'pending']) }}"
                           class="btn btn-xs {{ $status === 'pending' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-secondary' }}">
                            Pendientes ({{ $counters['pending'] }})
                        </a>
                        <a href="{{ route('admin.customer-withdraw.index', ['status' => 'approved']) }}"
                           class="btn btn-xs {{ $status === 'approved' ? 'btn-info text-white' : 'btn-outline-secondary' }}">
                            Aprobados ({{ $counters['approved'] }})
                        </a>
                        <a href="{{ route('admin.customer-withdraw.index', ['status' => 'transferred']) }}"
                           class="btn btn-xs {{ $status === 'transferred' ? 'btn-success' : 'btn-outline-secondary' }}">
                            Transferidos ({{ $counters['transferred'] }})
                        </a>
                        <a href="{{ route('admin.customer-withdraw.index', ['status' => 'rejected']) }}"
                           class="btn btn-xs {{ $status === 'rejected' ? 'btn-danger' : 'btn-outline-secondary' }}">
                            Rechazados ({{ $counters['rejected'] }})
                        </a>
                    </div>
                </div>

                {{-- Barra de Búsqueda --}}
                <div class="col-md-6 text-md-right">
                    <form action="{{ route('admin.customer-withdraw.index') }}" method="GET" class="d-inline-flex" style="gap: 8px;">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Buscar por ID, cliente, CLABE..." value="{{ $search }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="tio-search"></i>
                                </button>
                            </div>
                        </div>
                        @if(!empty($search))
                            <a href="{{ route('admin.customer-withdraw.index', ['status' => $status]) }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        {{-- Tabla de Solicitudes --}}
        <div class="table-responsive datatable-custom">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                    <tr>
                        <th># Folio</th>
                        <th>Cliente</th>
                        <th>Banco & Cuenta CLABE</th>
                        <th>Monto a Transferir</th>
                        <th>Estado</th>
                        <th>Rastreo SPEI</th>
                        <th>Fecha Solicitud</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdraws as $withdraw)
                    <tr>
                        <td>
                            <a href="{{ route('admin.customer-withdraw.show', $withdraw->id) }}" class="font-weight-bold text-primary">
                                #SPEI-{{ str_pad($withdraw->id, 5, '0', STR_PAD_LEFT) }}
                            </a>
                        </td>
                        <td>
                            @if($withdraw->user)
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm avatar-circle mr-2">
                                        <img class="avatar-img" src="{{ $withdraw->user->image_full_url }}" onerror="this.src='{{ asset('public/assets/admin/img/160x160/img1.jpg') }}'">
                                    </div>
                                    <div>
                                        <span class="d-block font-weight-bold text-dark">
                                            {{ $withdraw->user->f_name }} {{ $withdraw->user->l_name }}
                                        </span>
                                        <small class="text-muted">{{ $withdraw->user->phone }}</small>
                                        @if($withdraw->user->identity_verified === 'approved')
                                            <span class="badge badge-soft-success ml-1" title="Identidad Verificada vía MetaMap">
                                                <i class="tio-checkmark-circle"></i> KYC Verificado
                                            </span>
                                        @else
                                            <span class="badge badge-soft-secondary ml-1" title="Sin verificación de identidad">
                                                KYC Básico
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Usuario no disponible</span>
                            @endif
                        </td>
                        <td>
                            @if($withdraw->bankAccount)
                                <div>
                                    <span class="font-weight-bold text-dark d-block">
                                        <i class="tio-bank mr-1 text-primary"></i> {{ $withdraw->bankAccount->bank_name }}
                                    </span>
                                    <small class="text-muted d-block">Titular: {{ $withdraw->bankAccount->account_holder }}</small>
                                    <span class="font-family-monospace font-size-sm text-dark font-weight-bold">
                                        {{ $withdraw->bankAccount->masked_clabe }}
                                    </span>
                                </div>
                            @else
                                <span class="text-muted">Cuenta no disponible</span>
                            @endif
                        </td>
                        <td>
                            <span class="font-weight-bold text-dark font-size-md">
                                ${{ number_format($withdraw->amount, 2) }} <small class="text-muted">MXN</small>
                            </span>
                            @if($withdraw->fee > 0)
                                <small class="d-block text-muted">Comisión: ${{ number_format($withdraw->fee, 2) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($withdraw->status === 'pending')
                                <span class="badge badge-status-pending px-2 py-1">
                                    <i class="tio-time mr-1"></i> Pendiente
                                </span>
                            @elseif($withdraw->status === 'approved')
                                <span class="badge badge-status-approved px-2 py-1">
                                    <i class="tio-checkmark-circle-outlined mr-1"></i> Aprobado (En Proceso)
                                </span>
                            @elseif($withdraw->status === 'transferred')
                                <span class="badge badge-status-transferred px-2 py-1">
                                    <i class="tio-send mr-1"></i> Transferido
                                </span>
                            @elseif($withdraw->status === 'rejected')
                                <span class="badge badge-status-rejected px-2 py-1">
                                    <i class="tio-clear-circle mr-1"></i> Rechazado
                                </span>
                            @elseif($withdraw->status === 'cancelled')
                                <span class="badge badge-status-cancelled px-2 py-1">
                                    <i class="tio-close mr-1"></i> Cancelado
                                </span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($withdraw->spei_tracking_key))
                                <span class="badge badge-soft-info font-family-monospace" title="Clave de rastreo Banxico">
                                    {{ $withdraw->spei_tracking_key }}
                                </span>
                            @else
                                <span class="text-muted font-size-sm">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="d-block text-dark">{{ $withdraw->created_at ? $withdraw->created_at->format('d/m/Y') : '—' }}</span>
                            <small class="text-muted">{{ $withdraw->created_at ? $withdraw->created_at->format('H:i') : '' }} hrs</small>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.customer-withdraw.show', $withdraw->id) }}" class="btn btn-sm btn-white border shadow-none" title="Ver Detalles y Dispersar">
                                <i class="tio-visible mr-1 text-primary"></i> Ver / Procesar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <img class="mb-3" src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}" alt="Image" style="width: 7rem;">
                            <p class="mb-0 text-muted">No se encontraron solicitudes de retiro bancario.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($withdraws->hasPages())
        <div class="card-footer border-0">
            <div class="d-flex justify-content-center justify-content-sm-end">
                {!! $withdraws->appends(['status' => $status, 'search' => $search])->links() !!}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
