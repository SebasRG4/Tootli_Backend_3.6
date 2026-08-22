<?php

namespace App\Http\Controllers\Admin;

use App\Models\Badge;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;

class BadgeController extends Controller
{
    public function index(Request $request)
    {
        $key    = explode(' ', $request->input('search', ''));
        $badges = Badge::when($request->filled('search'), function ($q) use ($key) {
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('title', 'like', "%{$value}%")
                          ->orWhere('key',   'like', "%{$value}%");
                    }
                });
            })
            ->orderBy('sort_order')
            ->paginate(config('default_pagination', 25));

        return view('admin-views.badges.index', compact('badges'));
    }

    public function create()
    {
        return view('admin-views.badges.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key'             => 'required|string|max:100|unique:badges,key',
            'title'           => 'required|string|max:191',
            'description'     => 'nullable|string',
            'icon'            => 'required|string|max:100',
            'color_hex'       => 'required|string|max:7',
            'icon_color_hex'  => 'required|string|max:7',
            'condition_type'  => 'required|in:trips,rating,streak,tips,night_trips,weekend_trips,earnings,food_deliveries,perfect_week',
            'condition_value' => 'required|integer|min:1',
            'xp_reward'       => 'required|integer|min:0',
            'sort_order'      => 'nullable|integer|min:0',
        ]);

        Badge::create([
            'key'             => $request->key,
            'title'           => $request->title,
            'description'     => $request->description,
            'icon'            => $request->icon,
            'color_hex'       => $request->color_hex,
            'icon_color_hex'  => $request->icon_color_hex,
            'condition_type'  => $request->condition_type,
            'condition_value' => $request->condition_value,
            'xp_reward'       => $request->xp_reward,
            'sort_order'      => $request->sort_order ?? 0,
            'status'          => true,
        ]);

        Toastr::success('Insignia creada exitosamente.');
        return redirect()->route('admin.badges.list');
    }

    public function edit($id)
    {
        $badge = Badge::findOrFail($id);
        return view('admin-views.badges.edit', compact('badge'));
    }

    public function update(Request $request, $id)
    {
        $badge = Badge::findOrFail($id);

        $request->validate([
            'key'             => 'required|string|max:100|unique:badges,key,' . $id,
            'title'           => 'required|string|max:191',
            'description'     => 'nullable|string',
            'icon'            => 'required|string|max:100',
            'color_hex'       => 'required|string|max:7',
            'icon_color_hex'  => 'required|string|max:7',
            'condition_type'  => 'required|in:trips,rating,streak,tips,night_trips,weekend_trips,earnings,food_deliveries,perfect_week',
            'condition_value' => 'required|integer|min:1',
            'xp_reward'       => 'required|integer|min:0',
            'sort_order'      => 'nullable|integer|min:0',
        ]);

        $badge->update([
            'key'             => $request->key,
            'title'           => $request->title,
            'description'     => $request->description,
            'icon'            => $request->icon,
            'color_hex'       => $request->color_hex,
            'icon_color_hex'  => $request->icon_color_hex,
            'condition_type'  => $request->condition_type,
            'condition_value' => $request->condition_value,
            'xp_reward'       => $request->xp_reward,
            'sort_order'      => $request->sort_order ?? $badge->sort_order,
        ]);

        Toastr::success('Insignia actualizada exitosamente.');
        return redirect()->route('admin.badges.list');
    }

    public function status(Request $request)
    {
        $badge         = Badge::findOrFail($request->id);
        $badge->status = $request->status;
        $badge->save();

        return response()->json(['message' => 'Estado actualizado.']);
    }

    public function destroy(Request $request)
    {
        Badge::findOrFail($request->id)->delete();

        Toastr::success('Insignia eliminada exitosamente.');
        return back();
    }
}
