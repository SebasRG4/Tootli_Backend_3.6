@extends('layouts.admin.app')

@section('title', 'Tootli Direct — Trials Sandbox')

@push('css_or_js')
<style>
    .trial-badge-active   { background: #28a745; color: #fff; }
    .trial-badge-inactive { background: #6c757d; color: #fff; }
    .trial-badge-expired  { background: #dc3545; color: #fff; }
    .trial-badge-used     { background: #fd7e14; color: #fff; }
    .progress-thin { height: 6px; }
    #store-suggestions { position: absolute; z-index: 999; width: 100%; background: #fff;
        border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 6px 6px; max-height: 220px; overflow-y: auto; }
    .suggestion-item { padding: 8px 12px; cursor: pointer; font-size: 13px; }
    .suggestion-item:hover { background: #f8f9fa; }
</style>
@endpush

@section('content')
<div class="content container-fluid">

    {{-- Header --}}
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">
                    <i class="tio-delivery-front mr-2 text-primary"></i>
                    Tootli Direct — Trials Sandbox
                </h1>
                <p class="text-muted mb-0">
                    Otorga órdenes de prueba gratuitas a restaurantes y tiendas para que experimenten Tootli Direct.
                    Mientras tienen órdenes de trial, se les trata como suscritos (sin recargo).
                </p>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- ── Panel izquierdo: Otorgar trial ─────────────────────────────── --}}
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="tio-add-circle-outlined mr-1 text-success"></i>
                        Otorgar trial
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.subscriptionackage.tootli-direct.grant') }}" method="POST">
                        @csrf

                        {{-- Tienda --}}
                        <div class="form-group position-relative">
                            <label class="font-weight-bold">Restaurante / Tienda <span class="text-danger">*</span></label>
                            <input type="hidden" name="store_id" id="store_id_input">
                            <input type="text" id="store-search-input" class="form-control"
                                placeholder="Buscar por nombre..."
                                autocomplete="off"
                                required>
                            <div id="store-suggestions"></div>
                            <small class="text-muted">Solo muestra módulos Food y Grocery.</small>
                        </div>

                        {{-- Cantidad de órdenes --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Órdenes de prueba <span class="text-danger">*</span></label>
                            <input type="number" name="granted_orders" class="form-control"
                                min="1" max="500" value="10" required>
                            <small class="text-muted">Cuántas órdenes a domicilio puede hacer sin recargo.</small>
                        </div>

                        {{-- Vencimiento --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Vence el (opcional)</label>
                            <input type="date" name="expires_at" class="form-control"
                                min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            <small class="text-muted">Si se deja vacío, no vence por fecha.</small>
                        </div>

                        {{-- Notas --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Notas internas</label>
                            <textarea name="notes" class="form-control" rows="2"
                                placeholder="Ej: prueba mes de abril, campaña X..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success btn-block">
                            <i class="tio-gift-outlined mr-1"></i> Otorgar trial
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Panel derecho: Lista de trials ────────────────────────────── --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">Trials registrados</h5>
                    <form method="GET" class="d-flex" style="gap: 6px;">
                        <input type="text" name="search" value="{{ $search }}"
                            class="form-control form-control-sm" placeholder="Buscar tienda..."
                            style="width: 200px;">
                        <button class="btn btn-sm btn-primary">Buscar</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Tienda</th>
                                    <th>Órdenes</th>
                                    <th>Vence</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($trials as $trial)
                                @php
                                    $used     = $trial->used_orders;
                                    $granted  = $trial->granted_orders;
                                    $pct      = $granted > 0 ? round($used / $granted * 100) : 0;
                                    $expired  = $trial->expires_at && $trial->expires_at->isPast();
                                    $depleted = $used >= $granted;
                                    if (!$trial->is_active)      $badge = ['inactive', 'Desactivado'];
                                    elseif ($expired)            $badge = ['expired',  'Vencido'];
                                    elseif ($depleted)           $badge = ['used',     'Agotado'];
                                    else                         $badge = ['active',   'Activo'];
                                @endphp
                                <tr>
                                    <td class="text-muted small">{{ $trial->id }}</td>
                                    <td>
                                        <span class="font-weight-bold">{{ $trial->store->name ?? '—' }}</span>
                                        @if($trial->notes)
                                            <br><small class="text-muted">{{ Str::limit($trial->notes, 40) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $used }} / {{ $granted }}</div>
                                        <div class="progress progress-thin mt-1" style="width: 90px;">
                                            <div class="progress-bar {{ $depleted ? 'bg-danger' : 'bg-success' }}"
                                                style="width: {{ $pct }}%"></div>
                                        </div>
                                    </td>
                                    <td class="small">
                                        @if($trial->expires_at)
                                            <span class="{{ $expired ? 'text-danger' : 'text-muted' }}">
                                                {{ $trial->expires_at->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">Sin vencimiento</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge trial-badge-{{ $badge[0] }} px-2 py-1 rounded">
                                            {{ $badge[1] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($trial->is_active && !$expired && !$depleted)
                                            <form action="{{ route('admin.subscriptionackage.tootli-direct.deactivate', $trial->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('¿Desactivar este trial?')">
                                                @csrf
                                                <button class="btn btn-xs btn-soft-danger">
                                                    <i class="tio-block"></i> Desactivar
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No hay trials registrados aún.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($trials->hasPages())
                <div class="card-footer">
                    {{ $trials->links() }}
                </div>
                @endif
            </div>
        </div>

    </div>{{-- /row --}}
</div>
@endsection

@push('script_2')
<script>
    const searchInput  = document.getElementById('store-search-input');
    const storeIdInput = document.getElementById('store_id_input');
    const suggestions  = document.getElementById('store-suggestions');
    let debounce;

    searchInput.addEventListener('input', function () {
        clearTimeout(debounce);
        const q = this.value.trim();
        if (q.length < 2) { suggestions.innerHTML = ''; return; }
        debounce = setTimeout(() => {
            fetch(`{{ route('admin.subscriptionackage.tootli-direct.search-stores') }}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    suggestions.innerHTML = '';
                    if (!data.length) {
                        suggestions.innerHTML = '<div class="suggestion-item text-muted">Sin resultados</div>';
                        return;
                    }
                    data.forEach(store => {
                        const div = document.createElement('div');
                        div.className = 'suggestion-item';
                        div.textContent = store.name + (store.address ? ' — ' + store.address : '');
                        div.addEventListener('click', () => {
                            searchInput.value  = store.name;
                            storeIdInput.value = store.id;
                            suggestions.innerHTML = '';
                        });
                        suggestions.appendChild(div);
                    });
                });
        }, 300);
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('#store-search-input') && !e.target.closest('#store-suggestions')) {
            suggestions.innerHTML = '';
        }
    });
</script>
@endpush
