@extends('layouts.admin.app')

@section('title', 'Tootli Direct — Membresías')

@push('css_or_js')
<style>
    .badge-active   { background: #28a745; color: #fff; }
    .badge-inactive { background: #6c757d; color: #fff; }
    .badge-expired  { background: #dc3545; color: #fff; }
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
                    Tootli Direct — Membresías de Pago
                </h1>
                <p class="text-muted mb-0">
                    Activa o desactiva suscripciones Tootli Direct para restaurantes y tiendas.
                    La tarifa se descuenta automáticamente de la billetera del comercio.
                </p>
            </div>
            <div class="col-sm-auto">
                <a href="{{ route('admin.business-settings.tootli-direct.trials') }}"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="tio-gift mr-1"></i> Ver Trials / Sandbox
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- ── Formulario nueva membresía ── --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h5 class="card-title mb-0">
                        <i class="tio-add-circle-outlined mr-1 text-success"></i>
                        Activar Membresía
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.business-settings.tootli-direct.memberships.activate') }}"
                          method="POST" id="activateForm">
                        @csrf

                        {{-- Búsqueda de tienda --}}
                        <div class="form-group position-relative">
                            <label class="form-label font-weight-bold">
                                Restaurante / Tienda
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="storeSearch" class="form-control"
                                   placeholder="Buscar por nombre..." autocomplete="off">
                            <input type="hidden" name="store_id" id="storeId">
                            <div id="store-suggestions"></div>
                            <small class="text-muted">Solo módulos Comida y Tienda.</small>
                        </div>

                        {{-- Días de vigencia --}}
                        <div class="form-group">
                            <label class="form-label font-weight-bold">
                                Días de vigencia <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="validity_days" class="form-control"
                                   value="{{ old('validity_days', 30) }}" min="1" max="365" required>
                            <small class="text-muted">La membresía expira automáticamente al cumplirse este plazo.</small>
                        </div>

                        {{-- Tarifa a descontar --}}
                        <div class="form-group">
                            <label class="form-label font-weight-bold">
                                Tarifa mensualidad ({{ \App\CentralLogics\Helpers::currency_symbol() }})
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="fee" step="0.01" class="form-control"
                                   value="{{ old('fee', 0) }}" min="0" required>
                            <small class="text-muted">
                                Se descuenta de la billetera del comercio al activar.
                                Coloca 0 si es gratuita (promoción, etc.).
                            </small>
                        </div>

                        {{-- Notas --}}
                        <div class="form-group">
                            <label class="form-label">Notas (opcional)</label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="Ej: Promoción lanzamiento Q2...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="alert alert-warning py-2 small">
                            <i class="tio-info-outined mr-1"></i>
                            Si ya existe una membresía activa para esta tienda, será desactivada
                            y reemplazada por la nueva.
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" id="submitBtn" disabled>
                            <i class="tio-checkmark-circle mr-1"></i> Activar Membresía
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Tabla de membresías ── --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">Historial de Membresías</h5>
                        </div>
                        <div class="col-auto">
                            <form method="GET" class="d-flex gap-2">
                                <input type="text" name="search" class="form-control form-control-sm"
                                       placeholder="Buscar tienda..." value="{{ $search }}">
                                <button class="btn btn-sm btn-outline-primary">Buscar</button>
                                @if($search)
                                    <a href="{{ route('admin.business-settings.tootli-direct.memberships') }}"
                                       class="btn btn-sm btn-outline-secondary">Limpiar</a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tienda</th>
                                    <th>Tarifa</th>
                                    <th>Inicio</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($memberships as $m)
                                @php
                                    $isValid = $m->is_active && $m->expires_at->isFuture();
                                    $daysLeft = $isValid ? now()->diffInDays($m->expires_at, false) : 0;
                                    if ($isValid) {
                                        $badgeClass = 'badge-active';
                                        $badgeLabel = "Activa ({$daysLeft}d restantes)";
                                    } elseif (!$m->is_active) {
                                        $badgeClass = 'badge-inactive';
                                        $badgeLabel = 'Desactivada';
                                    } else {
                                        $badgeClass = 'badge-expired';
                                        $badgeLabel = 'Vencida';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $m->store->name ?? '—' }}</strong>
                                        @if($m->notes)
                                            <br><small class="text-muted">{{ Str::limit($m->notes, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ \App\CentralLogics\Helpers::format_currency($m->fee) }}
                                    </td>
                                    <td>
                                        <span class="small">{{ $m->starts_at->format('d/m/Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="small">{{ $m->expires_at->format('d/m/Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1 small">
                                            {{ $badgeLabel }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($isValid)
                                            <form method="POST"
                                                  action="{{ route('admin.business-settings.tootli-direct.memberships.deactivate', $m->id) }}"
                                                  onsubmit="return confirm('¿Desactivar esta membresía?')">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="tio-clear"></i> Desactivar
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No hay membresías registradas.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($memberships->hasPages())
                    <div class="card-footer">
                        {{ $memberships->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div><!-- /row -->
</div><!-- /container-fluid -->
@endsection

@push('script_2')
<script>
(function () {
    const searchInput  = document.getElementById('storeSearch');
    const storeIdInput = document.getElementById('storeId');
    const suggestions  = document.getElementById('store-suggestions');
    const submitBtn    = document.getElementById('submitBtn');
    const searchUrl    = "{{ route('admin.business-settings.tootli-direct.memberships.search-stores') }}";

    let debounceTimer;

    searchInput.addEventListener('input', function () {
        const q = this.value.trim();
        storeIdInput.value = '';
        submitBtn.disabled = true;
        clearTimeout(debounceTimer);

        if (q.length < 2) {
            suggestions.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`${searchUrl}?q=${encodeURIComponent(q)}`)
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
                        div.innerHTML = `<strong>${store.name}</strong>
                            <br><small class="text-muted">${store.address ?? ''}</small>`;
                        div.addEventListener('click', () => {
                            searchInput.value  = store.name;
                            storeIdInput.value = store.id;
                            suggestions.innerHTML = '';
                            submitBtn.disabled = false;
                        });
                        suggestions.appendChild(div);
                    });
                });
        }, 300);
    });

    // Cerrar sugerencias al hacer click fuera
    document.addEventListener('click', e => {
        if (!e.target.closest('#activateForm')) {
            suggestions.innerHTML = '';
        }
    });
})();
</script>
@endpush
