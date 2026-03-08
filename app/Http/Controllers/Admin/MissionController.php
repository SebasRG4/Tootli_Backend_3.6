<?php

namespace App\Http\Controllers\Admin;

use App\Models\Mission;
use App\Models\Zone;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;

class MissionController extends Controller
{
    public function index(Request $request)
    {
        $key = explode(' ', $request['search']);
        $missions = Mission::with('zone')
            ->when(isset($key), function ($q) use ($key) {
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('title', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.mission.index', compact('missions'));
    }

    public function create()
    {
        $zones = Zone::active()->get();
        return view('admin-views.mission.create', compact('zones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:191',
            'target_orders' => 'required|integer|min:1',
            'reward_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $mission = new Mission();
        $mission->title = $request->title;
        $mission->description = $request->description;
        $mission->target_orders = $request->target_orders;
        $mission->reward_amount = $request->reward_amount;
        $mission->start_date = $request->start_date;
        $mission->end_date = $request->end_date;
        $mission->zone_id = $request->zone_id;
        $mission->status = 1;
        $mission->save();

        Toastr::success(translate('messages.mission_added_successfully'));
        return redirect()->route('admin.mission.list');
    }

    public function edit($id)
    {
        $mission = Mission::findOrFail($id);
        $zones = Zone::active()->get();
        return view('admin-views.mission.edit', compact('mission', 'zones'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|max:191',
            'target_orders' => 'required|integer|min:1',
            'reward_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $mission = Mission::findOrFail($id);
        $mission->title = $request->title;
        $mission->description = $request->description;
        $mission->target_orders = $request->target_orders;
        $mission->reward_amount = $request->reward_amount;
        $mission->start_date = $request->start_date;
        $mission->end_date = $request->end_date;
        $mission->zone_id = $request->zone_id;
        $mission->save();

        Toastr::success(translate('messages.mission_updated_successfully'));
        return redirect()->route('admin.mission.list');
    }

    public function status(Request $request)
    {
        $mission = Mission::findOrFail($request->id);
        $mission->status = $request->status;
        $mission->save();

        Toastr::success(translate('messages.mission_status_updated_successfully'));
        return back();
    }

    public function delete(Request $request)
    {
        $mission = Mission::findOrFail($request->id);
        $mission->delete();

        Toastr::success(translate('messages.mission_deleted_successfully'));
        return back();
    }
}
