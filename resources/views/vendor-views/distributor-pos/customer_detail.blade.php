@extends('layouts.vendor.app')

@section('title', 'Cliente — ' . $customer->name)

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-header-title">👤 {{ $customer->name }}</h1>
                    <p class="text-muted mb-0">📞 {{ $customer->phone }}</p>
                </div>
                <div class="col-auto d-flex gap-2">
                    <a href="{{ route('vendor.distributor-pos.index') }}" class="btn btn--primary btn-sm">POS</a>
                    <a href="{{ route('vendor.distributor-pos.sales') }}" class="btn btn-outline-secondary btn-sm">Ventas</a>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Tarjeta resumen --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#EFF6FF,#DBEAFE); border-radius:16px;">
                    <div class="card-body text-center py-4">
                        <div style="font-size:3rem;">🎖️</div>
                        <h2 style="font-size:2.5rem; font-weight:800; color:#1E40AF; margin:0;">
                            {{ number_format($customer->points_balance, 2) }}
                        </h2>
                        <p class="text-muted mb-1">Puntos disponibles</p>
                        <hr>
                        <small class="text-muted">Total comprado</small>
                        <div class="font-weight-bold text-dark">
                            {{ \App\CentralLogics\Helpers::format_currency($customer->total_spent) }}
                        </div>
                        <small class="text-muted mt-2 d-block">1 pto = {{ \App\CentralLogics\Helpers::format_currency($config->distributor_point_value ?? 1) }}</small>
                    </div>
                </div>

                {{-- Ajuste manual --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="mb-0">✏️ Ajuste manual de puntos</h6>
                    </div>
                    <div class="card-body">
                        <form id="adjust-form">
                            <div class="form-group mb-2">
                                <label class="input-label">Puntos (+ / −)</label>
                                <input type="number" id="adj-points" class="form-control form-control-sm"
                                       step="0.5" placeholder="Ej: 10 o -5">
                            </div>
                            <div class="form-group mb-2">
                                <label class="input-label">Motivo</label>
                                <input type="text" id="adj-desc" class="form-control form-control-sm"
                                       placeholder="Corrección, bono, etc.">
                            </div>
                            <button type="submit" class="btn btn--primary btn-sm btn-block">Aplicar ajuste</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                {{-- Historial de puntos --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="mb-0">💎 Historial de puntos</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th class="text-right">Puntos</th>
                                        <th class="text-right">Saldo antes</th>
                                        <th class="text-right">Saldo después</th>
                                        <th>Descripción</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($history as $tx)
                                        <tr>
                                            <td>
                                                @php $icons=['earned'=>'🌟','redeemed'=>'🎯','adjusted'=>'✏️','expired'=>'⏳']; @endphp
                                                {{ $icons[$tx->type] ?? '' }}
                                                {{ ucfirst($tx->type) }}
                                            </td>
                                            <td class="text-right font-weight-bold {{ $tx->points > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $tx->points > 0 ? '+' : '' }}{{ number_format($tx->points, 2) }}
                                            </td>
                                            <td class="text-right text-muted">{{ number_format($tx->balance_before, 2) }}</td>
                                            <td class="text-right">{{ number_format($tx->balance_after, 2) }}</td>
                                            <td><small>{{ $tx->description }}</small></td>
                                            <td><small>{{ $tx->created_at->format('d/m/y H:i') }}</small></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-3">Sin transacciones.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($history->hasPages())
                        <div class="card-footer">{{ $history->links() }}</div>
                    @endif
                </div>

                {{-- Últimas ventas --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="mb-0">🧾 Últimas compras</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Folio</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center">Pts ganados</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sales as $sale)
                                        <tr>
                                            <td class="text-primary font-weight-bold">{{ $sale->folio }}</td>
                                            <td class="text-right">{{ \App\CentralLogics\Helpers::format_currency($sale->total) }}</td>
                                            <td class="text-center">
                                                @if($sale->points_earned > 0)
                                                    <span class="badge badge-success">+{{ $sale->points_earned }}</span>
                                                @else — @endif
                                            </td>
                                            <td><small>{{ $sale->created_at->format('d/m/y H:i') }}</small></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">Sin compras.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($sales->hasPages())
                        <div class="card-footer">{{ $sales->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
document.getElementById('adjust-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const points = document.getElementById('adj-points').value;
    const desc   = document.getElementById('adj-desc').value;
    if (!points) { alert('Ingresa los puntos.'); return; }

    const r = await fetch('{{ route("vendor.distributor-pos.adjust-points") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            customer_id:  {{ $customer->id }},
            points:       parseFloat(points),
            description:  desc,
        }),
    });
    const data = await r.json();
    if (data.success) {
        alert('Ajuste aplicado. Nuevo saldo: ' + data.new_balance + ' pts');
        location.reload();
    } else {
        alert(JSON.stringify(data.errors || data.message || 'Error'));
    }
});
</script>
@endpush
