<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DmReward;
use App\Models\DmRewardDiscount;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class DmRewardController extends Controller
{
    public function index()
    {
        $rewards = DmReward::with('discounts')->latest()->paginate(config('default_pagination'));
        return view('admin-views.delivery-man.rewards.index', compact('rewards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string',
            'short_discount' => 'nullable|string'
        ]);

        $reward = DmReward::create($request->only('title', 'description', 'icon', 'short_discount'));
        Toastr::success('Reward added successfully!');
        return back();
    }

    public function status(Request $request)
    {
        $reward = DmReward::findOrFail($request->id);
        $reward->status = $request->status;
        $reward->save();
        Toastr::success('Status updated!');
        return back();
    }

    public function destroy($id)
    {
        $reward = DmReward::findOrFail($id);
        $reward->delete();
        Toastr::success('Reward deleted successfully!');
        return back();
    }

    // Discounts
    public function store_discount(Request $request)
    {
        $request->validate([
            'dm_reward_id' => 'required|exists:dm_rewards,id',
            'title' => 'required|string',
            'description' => 'required|string',
            'value' => 'required|string',
        ]);

        DmRewardDiscount::create($request->only('dm_reward_id', 'title', 'description', 'value'));
        Toastr::success('Discount added successfully!');
        return back();
    }
    
    public function destroy_discount($id)
    {
        $discount = DmRewardDiscount::findOrFail($id);
        $discount->delete();
        Toastr::success('Discount deleted successfully!');
        return back();
    }
}
