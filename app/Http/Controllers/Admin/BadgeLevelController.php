<?php

namespace App\Http\Controllers\Admin;

use App\Models\DmBadgeLevel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;

class BadgeLevelController extends Controller
{
    public function index()
    {
        $levels = DmBadgeLevel::orderBy('level_index')->get();
        return view('admin-views.badges.levels', compact('levels'));
    }

    public function edit($id)
    {
        $level = DmBadgeLevel::findOrFail($id);
        return view('admin-views.badges.level-edit', compact('level'));
    }

    public function update(Request $request, $id)
    {
        $level = DmBadgeLevel::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:100',
            'sub_level'   => 'required|string|max:10',
            'xp_required' => 'required|integer|min:0',
            'color_from'  => 'required|string|max:7',
            'color_to'    => 'required|string|max:7',
        ]);

        $level->update($request->only(['name', 'sub_level', 'xp_required', 'color_from', 'color_to']));

        Toastr::success('Nivel actualizado exitosamente.');
        return redirect()->route('admin.badge-levels.list');
    }

    /**
     * Reordenar niveles por drag-and-drop (AJAX).
     * Recibe: {"ids": [3, 1, 2]} → aplica level_index = posición
     */
    public function updateOrder(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        foreach ($request->ids as $index => $id) {
            DmBadgeLevel::where('id', $id)->update(['level_index' => $index + 1]);
        }
        return response()->json(['success' => true]);
    }
}
