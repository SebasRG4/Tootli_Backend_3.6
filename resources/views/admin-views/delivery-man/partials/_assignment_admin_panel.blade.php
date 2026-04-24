@if (!empty($dmAssignmentSnapshot))
    <div class="border rounded p-xxl-20 p-3 mt-20">
        <h5 class="mb-3 fs-16 fw-bold">{{ translate('messages.dm_assignment_snapshot_title') }}</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="bg-light2 rounded p-3 h-100">
                    <h6 class="fs-13 text-muted mb-2">{{ translate('messages.dm_assignment_tier_label') }}</h6>
                    <p class="mb-0 fs-14 font-weight-bold">{{ $dmAssignmentSnapshot['tier'] }}</p>
                    <p class="mb-0 fs-11 text-muted">
                        @if (($dmAssignmentSnapshot['tier_source'] ?? 'auto') === 'manual')
                            {{ translate('messages.dm_tier_source_manual') }}
                        @else
                            {{ translate('messages.dm_tier_source_auto') }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-light2 rounded p-3 h-100">
                    <h6 class="fs-13 text-muted mb-2">{{ translate('messages.dm_assignment_max_concurrent') }}</h6>
                    <p class="mb-0 fs-14 font-weight-bold">{{ $dmAssignmentSnapshot['max_concurrent_effective'] }}</p>
                    <p class="mb-0 fs-11 text-muted">{{ translate('messages.dm_assignment_concurrent_detail', ['g' => $dmAssignmentSnapshot['max_concurrent_global'], 't' => $dmAssignmentSnapshot['max_concurrent_tier']]) }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-light2 rounded p-3 h-100">
                    <h6 class="fs-13 text-muted mb-2">{{ translate('messages.dm_assignment_current_orders') }}</h6>
                    <p class="mb-0 fs-14 font-weight-bold">{{ $dmAssignmentSnapshot['current_orders'] }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-light2 rounded p-3 h-100">
                    <h6 class="fs-13 text-muted mb-2">{{ translate('messages.dm_assignment_cash_in_hand') }}</h6>
                    <p class="mb-0 fs-14 font-weight-bold">{{ \App\CentralLogics\Helpers::format_currency($dmAssignmentSnapshot['collected_cash']) }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-light2 rounded p-3 h-100">
                    <h6 class="fs-13 text-muted mb-2">{{ translate('messages.dm_assignment_max_cash') }}</h6>
                    <p class="mb-0 fs-14 font-weight-bold">{{ \App\CentralLogics\Helpers::format_currency($dmAssignmentSnapshot['max_cash_effective']) }}</p>
                </div>
            </div>
            @if ($dmAssignmentSnapshot['max_order_value_cod'] !== null)
                <div class="col-md-6">
                    <div class="bg-light2 rounded p-3 h-100">
                        <h6 class="fs-13 text-muted mb-2">{{ translate('messages.dm_assignment_max_order_cod') }}</h6>
                        <p class="mb-0 fs-14 font-weight-bold">{{ \App\CentralLogics\Helpers::format_currency($dmAssignmentSnapshot['max_order_value_cod']) }}</p>
                    </div>
                </div>
            @endif
            @if (!empty($dmAssignmentSnapshot['tier_reason']))
                <div class="col-12">
                    <p class="mb-0 fs-12 text-muted"><strong>{{ translate('messages.Note') }}:</strong> {{ $dmAssignmentSnapshot['tier_reason'] }}</p>
                </div>
            @endif
            <div class="col-12">
                <span class="badge badge-soft-{{ $dmAssignmentSnapshot['account_suspended'] ? 'danger' : 'success' }}">
                    {{ $dmAssignmentSnapshot['account_suspended'] ? translate('messages.suspended') : translate('messages.Active') }}
                </span>
            </div>
            @isset($dmAssignmentSnapshot['strike_rolling_weight'])
                <div class="col-12 mt-2">
                    <h6 class="fs-14 mb-2">{{ translate('messages.dm_strike_panel_title') }}</h6>
                    <p class="fs-12 text-muted mb-1">
                        {{ translate('messages.dm_strike_rolling_detail', [
                            'w' => $dmAssignmentSnapshot['strike_rolling_weight'],
                            'd' => $dmAssignmentSnapshot['strike_window_days'] ?? 90,
                            't' => $dmAssignmentSnapshot['strike_threshold'] ?? 12,
                        ]) }}
                    </p>
                    <span class="badge badge-soft-{{ !empty($dmAssignmentSnapshot['strike_assignment_blocked']) ? 'danger' : 'success' }}">
                        {{ !empty($dmAssignmentSnapshot['strike_assignment_blocked']) ? translate('messages.dm_strike_blocked_badge') : translate('messages.dm_strike_ok_badge') }}
                    </span>
                    @if (!empty($dmAssignmentSnapshot['delivery_suspended_until_display']))
                        <p class="fs-12 mb-0 mt-1">
                            {{ translate('messages.dm_strike_suspended_until_label') }}:
                            {{ $dmAssignmentSnapshot['delivery_suspended_until_display'] }}
                        </p>
                    @endif
                </div>
            @endisset
        </div>

        @isset($dmAssignmentSnapshot['strike_rolling_weight'])
            <form method="post" action="{{ route('admin.users.delivery-man.update-strike-suspension', $deliveryMan->id) }}" class="mt-3 border-top pt-3">
                @csrf
                <h6 class="fs-14 mb-2">{{ translate('messages.dm_strike_suspension_form_title') }}</h6>
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="input-label">{{ translate('messages.dm_strike_suspended_until_field') }}</label>
                        <input type="datetime-local" name="delivery_suspended_until" class="form-control"
                            value="{{ optional($dmAssignmentSnapshot['delivery_suspended_until_display'] ?? null)?->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn--secondary">{{ translate('submit') }}</button>
                    </div>
                </div>
            </form>
            @if (isset($strikeIncidentTypes) && $strikeIncidentTypes->isNotEmpty())
                <form method="post" action="{{ route('admin.users.delivery-man.store-strike-event', $deliveryMan->id) }}" class="mt-3 border-top pt-3">
                    @csrf
                    <h6 class="fs-14 mb-2">{{ translate('messages.dm_strike_record_event_title') }}</h6>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="input-label">{{ translate('messages.dm_strike_type') }}</label>
                            <select name="delivery_incident_type_id" class="form-control" required>
                                @foreach ($strikeIncidentTypes as $it)
                                    <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="input-label">{{ translate('messages.order') }} ID</label>
                            <input type="number" name="order_id" class="form-control" min="1" placeholder="{{ translate('messages.optional') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="input-label">{{ translate('messages.dm_strike_suspended_until_field') }}</label>
                            <input type="datetime-local" name="delivery_suspended_until" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="input-label">{{ translate('messages.Note') }}</label>
                            <textarea name="notes" class="form-control" rows="2" maxlength="2000"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
                        </div>
                    </div>
                </form>
            @endif
        @endisset

        <form method="post" action="{{ route('admin.users.delivery-man.update-tier', $deliveryMan->id) }}" class="mt-4 border-top pt-4">
            @csrf
            <h6 class="fs-14 mb-3">{{ translate('messages.dm_tier_admin_form_title') }}</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="input-label">{{ translate('messages.dm_assignment_tier_label') }}</label>
                    <select name="dm_tier" class="form-control" required>
                        @foreach (['new', 'standard', 'pro', 'restricted'] as $opt)
                            <option value="{{ $opt }}" @selected(($deliveryMan->dm_tier ?? 'standard') === $opt)>
                                {{ translate('messages.dm_tier_label_' . $opt) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="input-label">{{ translate('messages.dm_tier_source_field') }}</label>
                    <select name="dm_tier_source" class="form-control" required>
                        <option value="auto" @selected(($deliveryMan->dm_tier_source ?? 'auto') === 'auto')>
                            {{ translate('messages.dm_tier_source_auto') }}</option>
                        <option value="manual" @selected(($deliveryMan->dm_tier_source ?? 'auto') === 'manual')>
                            {{ translate('messages.dm_tier_source_manual') }}</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="input-label">{{ translate('messages.dm_tier_reason_field') }}</label>
                    <textarea name="dm_tier_reason" class="form-control" rows="2"
                        placeholder="{{ translate('messages.dm_tier_reason_placeholder') }}">{{ $deliveryMan->dm_tier_reason }}</textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
                </div>
            </div>
        </form>
    </div>
@endif

@if (isset($dmAdminAuditLogs) && $dmAdminAuditLogs->isNotEmpty())
    <div class="border rounded p-xxl-20 p-3 mt-20">
        <h5 class="mb-3 fs-16 fw-bold">{{ translate('messages.dm_assignment_admin_audit_title') }}</h5>
        <div class="table-responsive">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                    <tr>
                        <th>{{ translate('messages.dm_assignment_audit_when') }}</th>
                        <th>{{ translate('messages.dm_assignment_audit_action') }}</th>
                        <th>{{ translate('messages.dm_assignment_audit_admin') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dmAdminAuditLogs as $log)
                        <tr>
                            <td class="fs-12">{{ $log->created_at }}</td>
                            <td class="fs-12">
                                @if ($log->action === \App\Models\DeliveryManAdminAuditLog::ACTION_DM_SUSPEND)
                                    {{ translate('messages.dm_assignment_audit_action_suspend') }}
                                @elseif($log->action === \App\Models\DeliveryManAdminAuditLog::ACTION_DM_UNSUSPEND)
                                    {{ translate('messages.dm_assignment_audit_action_unsuspend') }}
                                @elseif($log->action === \App\Models\DeliveryManAdminAuditLog::ACTION_DM_TIER_MANUAL)
                                    {{ translate('messages.dm_assignment_audit_action_tier') }}
                                @elseif($log->action === \App\Models\DeliveryManAdminAuditLog::ACTION_DM_STRIKE_RECORDED)
                                    {{ translate('messages.dm_assignment_audit_action_strike') }}
                                @elseif($log->action === \App\Models\DeliveryManAdminAuditLog::ACTION_DM_STRIKE_SUSPENSION_SET)
                                    {{ translate('messages.dm_assignment_audit_action_strike_suspension') }}
                                @elseif($log->action === \App\Models\DeliveryManAdminAuditLog::ACTION_DM_STRIKE_APPEAL_RESOLVED)
                                    {{ translate('messages.dm_assignment_audit_action_strike_appeal') }}
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
