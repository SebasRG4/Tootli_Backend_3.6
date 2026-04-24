@if (isset($orderStrikeReviewItems) && $orderStrikeReviewItems->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ translate('messages.order_strike_review_for_order') }}</h5>
        </div>
        <div class="card-body">
            @foreach ($orderStrikeReviewItems as $q)
                <div class="border rounded p-3 mb-2">
                    <p class="mb-1 fs-12">
                        <span class="badge badge-soft-{{ $q->status === \App\Models\OrderStrikeReviewQueue::STATUS_PENDING ? 'warning' : ($q->status === \App\Models\OrderStrikeReviewQueue::STATUS_DISMISSED ? 'secondary' : 'success') }}">
                            {{ $q->status }}
                        </span>
                        @if ($q->cancelReason)
                            — {{ $q->cancelReason->reason }}
                        @endif
                    </p>
                    @if ($q->cancellation_detail)
                        <p class="fs-12 text-muted mb-1">{{ $q->cancellation_detail }}</p>
                    @endif
                    @if (!empty($q->evidence))
                        <p class="fs-11 mb-0">
                            @if (!empty($q->evidence['lat']) && !empty($q->evidence['lng']))
                                GPS: {{ $q->evidence['lat'] }}, {{ $q->evidence['lng'] }}<br>
                            @endif
                            @if (!empty($q->evidence['photos']))
                                @foreach ($q->evidence['photos'] as $ph)
                                    @php($disk = $ph['storage'] ?? config('filesystems.default'))
                                    @php($url = \Illuminate\Support\Facades\Storage::disk($disk)->url('order-cancel/'.($ph['img'] ?? '')))
                                    <a href="{{ $url }}" target="_blank" rel="noopener">{{ translate('messages.order_strike_review_photo_link') }}</a><br>
                                @endforeach
                            @endif
                            @if (!empty($q->evidence['audio']['img']))
                                @php($adisk = $q->evidence['audio']['storage'] ?? config('filesystems.default'))
                                @php($aurl = \Illuminate\Support\Facades\Storage::disk($adisk)->url('order-cancel-audio/'.$q->evidence['audio']['img']))
                                <a href="{{ $aurl }}" target="_blank" rel="noopener">{{ translate('messages.order_strike_review_audio') }}</a>
                            @endif
                        </p>
                    @endif
                    @if ($q->status === \App\Models\OrderStrikeReviewQueue::STATUS_PENDING && isset($strikeIncidentTypes))
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn--primary" data-toggle="modal" data-target="#orderStrikeModal{{ $q->id }}">
                                {{ translate('messages.order_strike_review_record_strike') }}
                            </button>
                        </div>
                        <div class="modal fade" id="orderStrikeModal{{ $q->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="post" action="{{ route('admin.order.strike-review-queue.record-strike', $q->id) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ translate('messages.order_strike_review_record_strike') }}</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>{{ translate('messages.dm_strike_type') }}</label>
                                                <select name="delivery_incident_type_id" class="form-control" required>
                                                    @foreach ($strikeIncidentTypes as $t)
                                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>{{ translate('messages.Note') }}</label>
                                                <textarea name="admin_note" class="form-control" rows="2" maxlength="2000">{{ $q->cancellation_detail }}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>{{ translate('messages.dm_strike_suspended_until_field') }}</label>
                                                <input type="datetime-local" name="delivery_suspended_until" class="form-control">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('messages.cancel') }}</button>
                                            <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

@if (isset($orderAuditEvents) && $orderAuditEvents->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">{{ translate('messages.order_audit_timeline_title') }}</h5>
        </div>
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                @foreach ($orderAuditEvents as $ev)
                    <li class="mb-2 pb-2 border-bottom">
                        <span class="fs-11 text-muted">{{ $ev->created_at }}</span>
                        <span class="badge badge-soft-dark ml-1">{{ $ev->event_type }}</span>
                        <span class="fs-11 text-muted">({{ $ev->actor_type }} #{{ $ev->actor_id ?? '—' }})</span>
                        @if (!empty($ev->payload))
                            <pre class="fs-11 mb-0 mt-1 bg-light p-2 rounded" style="white-space:pre-wrap;max-height:200px;overflow:auto;">{{ json_encode($ev->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
