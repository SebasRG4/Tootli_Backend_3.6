@extends('layouts.admin.app')

@section('title', 'título de la cola de revisión de huelga de pedido')

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">{{ 'título de la cola de revisión de huelga de pedido' }}</h1>
        </div>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ 'Pedido' }}</th>
                                <th>{{ 'Repartidor' }}</th>
                                <th>{{ 'Cancelar motivo' }}</th>
                                <th>{{ 'detalle de revisión de huelga de pedido' }}</th>
                                <th>{{ 'ordenar huelga revisar evidencia' }}</th>
                                <th>{{ 'comportamiento' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($queue as $q)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.order.details', $q->order_id) }}">#{{ $q->order_id }}</a>
                                    </td>
                                    <td>
                                        @if ($q->deliveryMan)
                                            <a href="{{ route('admin.users.delivery-man.preview', $q->delivery_man_id) }}">
                                                {{ trim(($q->deliveryMan->f_name ?? '') . ' ' . ($q->deliveryMan->l_name ?? '')) ?: ('#' . $q->delivery_man_id) }}
                                            </a>
                                        @else
                                            #{{ $q->delivery_man_id }}
                                        @endif
                                    </td>
                                    <td class="fs-12">
                                        {{ $q->cancelReason?->reason ?? 'dm cancelar motivo legado' }}
                                    </td>
                                    <td class="fs-12" style="max-width:220px;">{{ \Illuminate\Support\Str::limit($q->cancellation_detail, 120) }}</td>
                                    <td class="fs-12">
                                        @if (!empty($q->evidence['lat']) && !empty($q->evidence['lng']))
                                            GPS: {{ $q->evidence['lat'] }}, {{ $q->evidence['lng'] }}<br>
                                        @endif
                                        @if (!empty($q->evidence['photos']))
                                            {{ count($q->evidence['photos']) }} {{ 'Fotos de revisión de huelga de pedidos' }}
                                        @endif
                                        @if (!empty($q->evidence['audio']))
                                            {{ 'audio de revisión de huelga de pedidos' }}
                                        @endif
                                    </td>
                                    <td>
                                        <form method="post" action="{{ route('admin.order.strike-review-queue.dismiss', $q->id) }}" class="mb-2">
                                            @csrf
                                            <input type="hidden" name="admin_note" value="">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ 'orden huelga revisión desestimar' }}</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn--primary" data-toggle="modal" data-target="#strikeModal{{ $q->id }}">
                                            {{ 'huelga de orden revisión huelga récord' }}
                                        </button>
                                        <div class="modal fade" id="strikeModal{{ $q->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="post" action="{{ route('admin.order.strike-review-queue.record-strike', $q->id) }}">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ 'huelga de orden revisión huelga récord' }} #{{ $q->order_id }}</h5>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label>{{ 'tipo de ataque dm' }}</label>
                                                                <select name="delivery_incident_type_id" class="form-control" required>
                                                                    @foreach ($incidentTypes as $t)
                                                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ 'Nota' }}</label>
                                                                <textarea name="admin_note" class="form-control" rows="2" maxlength="2000">{{ $q->cancellation_detail }}</textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ 'huelga dm suspendida hasta el campo' }}</label>
                                                                <input type="datetime-local" name="delivery_suspended_until" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ 'Cancelar' }}</button>
                                                            <button type="submit" class="btn btn--primary">{{ 'entregar' }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">{{ 'no se encontraron datos' }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($queue->hasPages())
                <div class="card-footer">{{ $queue->links() }}</div>
            @endif
        </div>
    </div>
@endsection
