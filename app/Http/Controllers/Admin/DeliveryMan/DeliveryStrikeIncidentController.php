<?php

namespace App\Http\Controllers\Admin\DeliveryMan;

use App\Http\Controllers\BaseController;
use App\Models\DeliveryIncidentType;
use App\Models\DeliveryManAdminAuditLog;
use App\Models\DeliveryManStrikeEvent;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class DeliveryStrikeIncidentController extends BaseController
{
    /**
     * {@inheritdoc}
     *
     * Punto de entrada requerido por ControllerInterface; redirige al listado de tipos.
     */
    public function index(?Request $request): View|Collection|LengthAwarePaginator|null
    {
        return $this->incidentTypesIndex();
    }

    public function incidentTypesIndex(): View
    {
        $types = DeliveryIncidentType::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin-views.delivery-man.strike.incident-types-index', compact('types'));
    }

    public function incidentTypeStore(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:64|unique:delivery_incident_types,code',
            'name' => 'required|string|max:191',
            'weight' => 'required|integer|min:0|max:32767',
            'generates_strike' => 'nullable|boolean',
            'active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:32767',
        ]);
        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());

            return back()->withInput();
        }

        DeliveryIncidentType::query()->create([
            'code' => $request['code'],
            'name' => $request['name'],
            'weight' => (int) $request['weight'],
            'generates_strike' => $request->boolean('generates_strike', true),
            'active' => $request->boolean('active', true),
            'sort_order' => (int) ($request['sort_order'] ?? 0),
        ]);

        Toastr::success(translate('messages.updated_successfully'));

        return redirect()->route('admin.users.delivery-man.strike.incident-types.index');
    }

    public function incidentTypeUpdate(Request $request, int|string $id): RedirectResponse
    {
        $type = DeliveryIncidentType::query()->findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'weight' => 'required|integer|min:0|max:32767',
            'generates_strike' => 'nullable|boolean',
            'active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:32767',
        ]);
        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());

            return back()->withInput();
        }

        $type->update([
            'name' => $request['name'],
            'weight' => (int) $request['weight'],
            'generates_strike' => $request->boolean('generates_strike', true),
            'active' => $request->boolean('active', true),
            'sort_order' => (int) ($request['sort_order'] ?? 0),
        ]);

        Toastr::success(translate('messages.updated_successfully'));

        return redirect()->route('admin.users.delivery-man.strike.incident-types.index');
    }

    public function appealsIndex(): View
    {
        $appeals = DeliveryManStrikeEvent::query()
            ->where('appeal_status', DeliveryManStrikeEvent::APPEAL_PENDING)
            ->with(['deliveryMan', 'incidentType', 'order'])
            ->orderBy('appealed_at')
            ->paginate(config('default_pagination'));

        return view('admin-views.delivery-man.strike.appeals-index', compact('appeals'));
    }

    public function appealResolve(Request $request, int|string $id): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'resolution' => 'required|in:accepted,rejected',
        ]);
        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());

            return back();
        }

        $event = DeliveryManStrikeEvent::query()->whereKey($id)->firstOrFail();
        if ($event->appeal_status !== DeliveryManStrikeEvent::APPEAL_PENDING) {
            Toastr::error(translate('messages.dm_strike_appeal_not_pending'));

            return back();
        }

        $resolution = $request['resolution'] === 'accepted'
            ? DeliveryManStrikeEvent::APPEAL_ACCEPTED
            : DeliveryManStrikeEvent::APPEAL_REJECTED;

        $event->update([
            'appeal_status' => $resolution,
            'appeal_resolved_at' => now(),
            'appeal_resolved_by_admin_id' => auth('admin')->id(),
        ]);

        if ($event->delivery_man_id) {
            DeliveryManAdminAuditLog::log(
                deliveryManId: (int) $event->delivery_man_id,
                action: DeliveryManAdminAuditLog::ACTION_DM_STRIKE_APPEAL_RESOLVED,
                adminId: auth('admin')->id(),
                meta: [
                    'strike_event_id' => (int) $event->id,
                    'resolution' => $resolution,
                    'ip' => $request->ip(),
                ],
            );
        }

        Toastr::success(translate('messages.updated_successfully'));

        return redirect()->route('admin.users.delivery-man.strike.appeals.index');
    }
}
