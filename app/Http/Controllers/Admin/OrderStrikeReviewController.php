<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryIncidentType;
use App\Models\Order;
use App\Models\OrderAuditEvent;
use App\Models\OrderStrikeReviewQueue;
use App\Services\DeliveryStrike\RecordDeliveryManStrikeAction;
use App\Services\OrderAudit\OrderAuditLogger;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class OrderStrikeReviewController extends Controller
{
    public function index(Request $request): View
    {
        $queue = OrderStrikeReviewQueue::query()
            ->with(['order', 'deliveryMan', 'cancelReason'])
            ->where('status', OrderStrikeReviewQueue::STATUS_PENDING)
            ->orderBy('created_at')
            ->paginate(config('default_pagination'));

        $incidentTypes = DeliveryIncidentType::query()->where('active', true)->orderBy('sort_order')->get();

        return view('admin-views.order.strike-review-queue', compact('queue', 'incidentTypes'));
    }

    public function dismiss(Request $request, int $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'admin_note' => 'nullable|string|max:2000',
        ]);
        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());

            return back();
        }

        $row = OrderStrikeReviewQueue::query()->findOrFail($id);
        if ($row->status !== OrderStrikeReviewQueue::STATUS_PENDING) {
            Toastr::error(translate('messages.order_strike_review_not_pending'));

            return back();
        }

        DB::transaction(function () use ($row, $request) {
            $row->update([
                'status' => OrderStrikeReviewQueue::STATUS_DISMISSED,
                'reviewed_by_admin_id' => auth('admin')->id(),
                'reviewed_at' => now(),
                'admin_note' => $request['admin_note'],
            ]);
            $order = Order::query()->find($row->order_id);
            if ($order) {
                OrderAuditLogger::log($order, 'admin', auth('admin')->id(), OrderAuditEvent::EVENT_STRIKE_REVIEW_DISMISSED, [
                    'queue_id' => $row->id,
                    'delivery_man_id' => $row->delivery_man_id,
                    'admin_note' => $request['admin_note'],
                ]);
            }
        });

        Toastr::success(translate('messages.updated_successfully'));

        return redirect()->route('admin.order.strike-review-queue.index');
    }

    public function recordStrike(Request $request, int $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'delivery_incident_type_id' => 'required|exists:delivery_incident_types,id',
            'admin_note' => 'nullable|string|max:2000',
            'delivery_suspended_until' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());

            return back();
        }

        $row = OrderStrikeReviewQueue::query()->findOrFail($id);
        if ($row->status !== OrderStrikeReviewQueue::STATUS_PENDING) {
            Toastr::error(translate('messages.order_strike_review_not_pending'));

            return back();
        }

        try {
            DB::transaction(function () use ($row, $request) {
                $notes = $request['admin_note'] ?? $row->cancellation_detail;
                $result = app(RecordDeliveryManStrikeAction::class)->run(
                    (int) $row->delivery_man_id,
                    (int) $request['delivery_incident_type_id'],
                    (int) $row->order_id,
                    $notes,
                    auth('admin')->id(),
                    $request['delivery_suspended_until'],
                );

                $row->update([
                    'status' => OrderStrikeReviewQueue::STATUS_STRIKE_RECORDED,
                    'delivery_man_strike_event_id' => $result['event']->id,
                    'reviewed_by_admin_id' => auth('admin')->id(),
                    'reviewed_at' => now(),
                    'admin_note' => $request['admin_note'],
                ]);

                $order = Order::query()->find($row->order_id);
                if ($order) {
                    OrderAuditLogger::log($order, 'admin', auth('admin')->id(), OrderAuditEvent::EVENT_STRIKE_FROM_REVIEW, [
                        'queue_id' => $row->id,
                        'strike_event_id' => $result['event']->id,
                        'delivery_incident_type_id' => (int) $request['delivery_incident_type_id'],
                    ]);
                }
            });
        } catch (\InvalidArgumentException) {
            Toastr::error(translate('messages.dm_strike_order_not_for_dm'));

            return back();
        }

        Toastr::success(translate('messages.updated_successfully'));

        return redirect()->route('admin.order.strike-review-queue.index');
    }
}
