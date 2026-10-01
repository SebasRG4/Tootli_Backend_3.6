@extends('layouts.admin.app')

@section('title', 'Ventas — POS Distribuidora')

@section('content')
    @php($currSym = \App\CentralLogics\Helpers::currency_symbol())
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title">🧾 Historial de Ventas — Distribuidora</h1>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.distributor-pos.index') }}" class="btn btn--primary">
                        <i class="tio-arrow-backward mr-1"></i> Volver al POS
                    </a>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form class="row g-2 align-items-end" method="GET">
                    <div class="col-sm-4">
                        <label class="input-label">Folio</label>
                        <input type="text" name="folio" class="form-control form-control-sm"
                               placeholder="DST-001-00001" value="{{ request('folio') }}">
                    </div>
                    <div class="col-sm-4">
                        <label class="input-label">Fecha</label>
                        <input type="date" name="date" class="form-control form-control-sm"
                               value="{{ request('date', now()->toDateString()) }}">
                    </div>
                    <div class="col-sm-4">
                        <button type="submit" class="btn btn--primary btn-sm">Filtrar</button>
                        <a href="{{ route('admin.distributor-pos.sales') }}" class="btn btn-outline-secondary btn-sm ml-1">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Método</th>
                                <th class="text-right">Subtotal</th>
                                <th class="text-right">Descuento</th>
                                <th class="text-right">Total</th>
                                <th class="text-center">Pts ganados</th>
                                <th class="text-center">Pts canjeados</th>
                                <th>Cajero</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                <tr>
                                    <td><strong class="text-primary">{{ $sale->folio }}</strong></td>
                                    <td>
                                        @if($sale->customer)
                                            <a href="{{ route('admin.distributor-pos.customer-detail', $sale->customer->id) }}"
                                               class="text-body">
                                                {{ $sale->customer->name }}<br>
                                                <small class="text-muted">{{ $sale->customer->phone }}</small>
                                            </a>
                                        @else
                                            <span class="text-muted">— Anónimo</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $icons = ['cash'=>'💵','card'=>'💳','transfer'=>'🏦','points_redemption'=>'🎯','mixed'=>'🔀']; @endphp
                                        {{ $icons[$sale->payment_method] ?? '' }}
                                        {{ ucfirst($sale->payment_method) }}
                                    </td>
                                    <td class="text-right">{{ \App\CentralLogics\Helpers::format_currency($sale->subtotal) }}</td>
                                    <td class="text-right text-danger">
                                        @if($sale->discount_amount > 0) -{{ \App\CentralLogics\Helpers::format_currency($sale->discount_amount) }} @else — @endif
                                    </td>
                                    <td class="text-right font-weight-bold">{{ \App\CentralLogics\Helpers::format_currency($sale->total) }}</td>
                                    <td class="text-center">
                                        @if($sale->points_earned > 0)
                                            <span class="badge badge-success">+{{ $sale->points_earned }}</span>
                                        @else — @endif
                                    </td>
                                    <td class="text-center">
                                        @if($sale->points_redeemed > 0)
                                            <span class="badge badge-warning">{{ $sale->points_redeemed }}</span>
                                        @else — @endif
                                    </td>
                                    <td><small>{{ $sale->created_by ?? '—' }}</small></td>
                                    <td><small>{{ $sale->created_at->format('d/m/Y H:i') }}</small></td>
                                    <td>
                                        @php $badge = ['completed'=>'success','cancelled'=>'danger','refunded'=>'warning']; @endphp
                                        <span class="badge badge-{{ $badge[$sale->status] ?? 'secondary' }}">
                                            {{ ucfirst($sale->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.distributor-pos.print-receipt', $sale->id) }}" target="_blank"
                                           class="btn btn-outline-info btn-xs py-1 px-2" title="Imprimir Ticket">
                                            <i class="tio-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-5">No hay ventas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $sales->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
