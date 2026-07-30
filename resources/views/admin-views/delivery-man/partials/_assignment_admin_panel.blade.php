@if (!empty($dmAssignmentSnapshot))
    <div class="border rounded p-xxl-20 p-3 mt-20">
        <h5 class="mb-3 fs-16 fw-bold">{{ 'título de la instantánea de la tarea dm' }}</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="bg-light2 rounded p-3 h-100">
                    <h6 class="fs-13 text-muted mb-2">{{ 'etiqueta de nivel de asignación dm' }}</h6>
                    <p class="mb-0 fs-14 font-weight-bold">{{ $dmAssignmentSnapshot['tier'] }}</p>
                    <p class="mb-0 fs-11 text-muted">
                        @if (($dmAssignmentSnapshot['tier_source'] ?? 'auto') === 'manual')
                            {{ 'manual de fuente de nivel dm' }}
                        @else
                            {{ 'fuente de nivel dm automática' }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-light2 rounded p-3 h-100">
                    <h6 class="fs-13 text-muted mb-2">{{ 'asignación dm máxima concurrente' }}</h6>
                    <p class="mb-0 fs-14 font-weight-bold">{{ $dmAssignmentSnapshot['max_concurrent_effective'] }}</p>
                    <p class="mb-0 fs-11 text-muted">{{ translate('messages.dm_assignment_concurrent_detail', ['g' => $dmAssignmentSnapshot['max_concurrent_global'], 't' => $dmAssignmentSnapshot['max_concurrent_tier']]) }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-light2 rounded p-3 h-100">
                    <h6 class="fs-13 text-muted mb-2">{{ 'dm asignación pedidos actuales' }}</h6>
                    <p class="mb-0 fs-14 font-weight-bold">{{ $dmAssignmentSnapshot['current_orders'] }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-light2 rounded p-3 h-100">
                    <h6 class="fs-13 text-muted mb-2">{{ 'dm asignación efectivo en mano' }}</h6>
                    <p class="mb-0 fs-14 font-weight-bold">{{ \App\CentralLogics\Helpers::format_currency($dmAssignmentSnapshot['collected_cash']) }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-light2 rounded p-3 h-100">
                    <h6 class="fs-13 text-muted mb-2">{{ 'dm asignación max efectivo' }}</h6>
                    <p class="mb-0 fs-14 font-weight-bold">{{ \App\CentralLogics\Helpers::format_currency($dmAssignmentSnapshot['max_cash_effective']) }}</p>
                </div>
            </div>
            @if ($dmAssignmentSnapshot['max_order_value_cod'] !== null)
                <div class="col-md-6">
                    <div class="bg-light2 rounded p-3 h-100">
                        <h6 class="fs-13 text-muted mb-2">{{ 'dm asignación orden máxima bacalao' }}</h6>
                        <p class="mb-0 fs-14 font-weight-bold">{{ \App\CentralLogics\Helpers::format_currency($dmAssignmentSnapshot['max_order_value_cod']) }}</p>
                    </div>
                </div>
            @endif
            @if (!empty($dmAssignmentSnapshot['tier_reason']))
                <div class="col-12">
                    <p class="mb-0 fs-12 text-muted"><strong>{{ 'Nota' }}:</strong> {{ $dmAssignmentSnapshot['tier_reason'] }}</p>
                </div>
            @endif
            <div class="col-12">
                <span class="badge badge-soft-{{ $dmAssignmentSnapshot['account_suspended'] ? 'danger' : 'success' }}">
                    {{ $dmAssignmentSnapshot['account_suspended'] ? 'suspendido' : 'Activo' }}
                </span>
            </div>
            @isset($dmAssignmentSnapshot['strike_rolling_weight'])
                <div class="col-12 mt-2">
                    <h6 class="fs-14 mb-2">{{ 'título del panel de huelga dm' }}</h6>
                    <p class="fs-12 text-muted mb-1">
                        {{ translate('messages.dm_strike_rolling_detail', [
                            'w' => $dmAssignmentSnapshot['strike_rolling_weight'],
                            'd' => $dmAssignmentSnapshot['strike_window_days'] ?? 90,
                            't' => $dmAssignmentSnapshot['strike_threshold'] ?? 12,
                        ]) }}
                    </p>
                    <span class="badge badge-soft-{{ !empty($dmAssignmentSnapshot['strike_assignment_blocked']) ? 'danger' : 'success' }}">
                        {{ !empty($dmAssignmentSnapshot['strike_assignment_blocked']) ? 'insignia bloqueada de huelga dm' : 'insignia dm strike ok' }}
                    </span>
                    @if (!empty($dmAssignmentSnapshot['delivery_suspended_until_display']))
                        <p class="fs-12 mb-0 mt-1">
                            {{ 'huelga dm suspendida hasta etiqueta' }}:
                            {{ $dmAssignmentSnapshot['delivery_suspended_until_display'] }}
                        </p>
                    @endif
                </div>
            @endisset
        </div>

        @isset($dmAssignmentSnapshot['strike_rolling_weight'])
            <form method="post" action="{{ route('admin.users.delivery-man.update-strike-suspension', $deliveryMan->id) }}" class="mt-3 border-top pt-3">
                @csrf
                <h6 class="fs-14 mb-2">{{ 'título del formulario de suspensión de huelga dm' }}</h6>
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="input-label">{{ 'huelga dm suspendida hasta el campo' }}</label>
                        <input type="datetime-local" name="delivery_suspended_until" class="form-control"
                            value="{{ optional($dmAssignmentSnapshot['delivery_suspended_until_display'] ?? null)?->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn--secondary">{{ 'entregar' }}</button>
                    </div>
                </div>
            </form>
            @if (isset($strikeIncidentTypes) && $strikeIncidentTypes->isNotEmpty())
                <form method="post" action="{{ route('admin.users.delivery-man.store-strike-event', $deliveryMan->id) }}" class="mt-3 border-top pt-3">
                    @csrf
                    <h6 class="fs-14 mb-2">{{ 'título del evento de registro de huelga dm' }}</h6>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="input-label">{{ 'tipo de ataque dm' }}</label>
                            <select name="delivery_incident_type_id" class="form-control" required>
                                @foreach ($strikeIncidentTypes as $it)
                                    <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="input-label">{{ 'Pedido' }} ID</label>
                            <input type="number" name="order_id" class="form-control" min="1" placeholder="{{ 'opcional' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="input-label">{{ 'huelga dm suspendida hasta el campo' }}</label>
                            <input type="datetime-local" name="delivery_suspended_until" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="input-label">{{ 'Nota' }}</label>
                            <textarea name="notes" class="form-control" rows="2" maxlength="2000"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn--primary">{{ 'entregar' }}</button>
                        </div>
                    </div>
                </form>
            @endif
        @endisset

        <form method="post" action="{{ route('admin.users.delivery-man.update-tier', $deliveryMan->id) }}" class="mt-4 border-top pt-4">
            @csrf
            <h6 class="fs-14 mb-3">{{ 'título del formulario de administración de nivel dm' }}</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="input-label">{{ 'etiqueta de nivel de asignación dm' }}</label>
                    <select name="dm_tier" class="form-control" required>
                        @foreach (['new', 'standard', 'pro', 'restricted'] as $opt)
                            <option value="{{ $opt }}" @selected(($deliveryMan->dm_tier ?? 'standard') === $opt)>
                                {{ translate('messages.dm_tier_label_' . $opt) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="input-label">{{ 'campo de origen de nivel dm' }}</label>
                    <select name="dm_tier_source" class="form-control" required>
                        <option value="auto" @selected(($deliveryMan->dm_tier_source ?? 'auto') === 'auto')>
                            {{ 'fuente de nivel dm automática' }}</option>
                        <option value="manual" @selected(($deliveryMan->dm_tier_source ?? 'auto') === 'manual')>
                            {{ 'manual de fuente de nivel dm' }}</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="input-label">{{ 'campo de motivo de nivel dm' }}</label>
                    <textarea name="dm_tier_reason" class="form-control" rows="2"
                        placeholder="{{ 'marcador de posición de motivo de nivel dm' }}">{{ $deliveryMan->dm_tier_reason }}</textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn--primary">{{ 'entregar' }}</button>
                </div>
            </div>
        </form>
    </div>
@endif

@if (isset($dmAdminAuditLogs) && $dmAdminAuditLogs->isNotEmpty())
    <div class="border rounded p-xxl-20 p-3 mt-20">
        <h5 class="mb-3 fs-16 fw-bold">{{ 'título de auditoría de administrador de tarea dm' }}</h5>
        <div class="table-responsive">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                    <tr>
                        <th>{{ 'auditoría de asignación dm cuando' }}</th>
                        <th>{{ 'acción de auditoría de asignación dm' }}</th>
                        <th>{{ 'administrador de auditoría de tareas dm' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dmAdminAuditLogs as $log)
                        <tr>
                            <td class="fs-12">{{ $log->created_at }}</td>
                            <td class="fs-12">
                                @if ($log->action === \App\Models\DeliveryManAdminAuditLog::ACTION_DM_SUSPEND)
                                    {{ 'suspensión de acción de auditoría de asignación dm' }}
                                @elseif($log->action === \App\Models\DeliveryManAdminAuditLog::ACTION_DM_UNSUSPEND)
                                    {{ 'acción de auditoría de asignación dm suspendida' }}
                                @elseif($log->action === \App\Models\DeliveryManAdminAuditLog::ACTION_DM_TIER_MANUAL)
                                    {{ 'nivel de acción de auditoría de asignación de dm' }}
                                @elseif($log->action === \App\Models\DeliveryManAdminAuditLog::ACTION_DM_STRIKE_RECORDED)
                                    {{ 'huelga de acción de auditoría de asignación dm' }}
                                @elseif($log->action === \App\Models\DeliveryManAdminAuditLog::ACTION_DM_STRIKE_SUSPENSION_SET)
                                    {{ 'dm asignación auditoría acción huelga suspensión' }}
                                @elseif($log->action === \App\Models\DeliveryManAdminAuditLog::ACTION_DM_STRIKE_APPEAL_RESOLVED)
                                    {{ 'dm asignación auditoría acción huelga apelación' }}
                                @else
                                    {{ $log->action }}
                                @endif
                            </td>
                            <td class="fs-12">
                                @if ($log->relationLoaded('admin') && $log->admin)
                                    {{ trim(($log->admin->f_name ?? '') . ' ' . ($log->admin->l_name ?? '')) ?: $log->admin->email }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
