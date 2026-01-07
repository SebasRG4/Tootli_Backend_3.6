<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\DynamicSection;
use App\Models\Item;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class DynamicSectionController extends Controller
{
    /**
     * Display a listing of dynamic sections.
     */
    public function index(Request $request)
    {
        $sections = DynamicSection::module(Config::get('module.current_module_id'))
            ->withCount('items')
            ->orderBy('priority')
            ->paginate(config('default_pagination'));

        return view('admin-views.dynamic-section.index', compact('sections'));
    }

    /**
     * Show the form for creating a new section.
     */
    public function create()
    {
        $items = Item::where('module_id', Config::get('module.current_module_id'))
            ->where('status', 1)
            ->select('id', 'name', 'image')
            ->get();

        return view('admin-views.dynamic-section.create', compact('items'));
    }

    /**
     * Store a newly created section.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'items' => 'nullable|array',
            'items.*' => 'exists:items,id',
        ]);

        $section = new DynamicSection();
        $section->title = $request->title;
        $section->subtitle = $request->subtitle;
        $section->module_id = Config::get('module.current_module_id');
        $section->priority = DynamicSection::module(Config::get('module.current_module_id'))->max('priority') + 1;
        $section->status = $request->has('status') ? 1 : 0;

        if ($request->hasFile('background_image')) {
            $section->background_image = Helpers::upload('dynamic_section/', 'png', $request->file('background_image'));
        }

        $section->save();

        // Attach items
        if ($request->has('items') && is_array($request->items)) {
            $itemsWithPriority = [];
            foreach ($request->items as $index => $itemId) {
                $itemsWithPriority[$itemId] = ['priority' => $index];
            }
            $section->items()->attach($itemsWithPriority);
        }

        Toastr::success(translate('messages.section_created_successfully'));
        return redirect()->route('admin.dynamic-section.add-new');
    }

    /**
     * Show the form for editing the section.
     */
    public function edit($id)
    {
        $section = DynamicSection::with('items')->findOrFail($id);

        $items = Item::where('module_id', Config::get('module.current_module_id'))
            ->where('status', 1)
            ->select('id', 'name', 'image')
            ->get();

        $selectedItems = $section->items->pluck('id')->toArray();

        return view('admin-views.dynamic-section.edit', compact('section', 'items', 'selectedItems'));
    }

    /**
     * Update the specified section.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'items' => 'nullable|array',
            'items.*' => 'exists:items,id',
        ]);

        $section = DynamicSection::findOrFail($id);
        $section->title = $request->title;
        $section->subtitle = $request->subtitle;

        if ($request->hasFile('background_image')) {
            // Delete old image using Storage facade
            if ($section->background_image) {
                Storage::disk(Helpers::getDisk())->delete('dynamic_section/' . $section->background_image);
            }
            $section->background_image = Helpers::upload('dynamic_section/', 'png', $request->file('background_image'));
        }

        $section->save();

        // Sync items
        if ($request->has('items') && is_array($request->items)) {
            $itemsWithPriority = [];
            foreach ($request->items as $index => $itemId) {
                $itemsWithPriority[$itemId] = ['priority' => $index];
            }
            $section->items()->sync($itemsWithPriority);
        } else {
            $section->items()->detach();
        }

        Toastr::success(translate('messages.section_updated_successfully'));
        return redirect()->route('admin.dynamic-section.add-new');
    }

    /**
     * Toggle section status.
     */
    public function status(Request $request)
    {
        $section = DynamicSection::findOrFail($request->id);
        $section->status = !$section->status;
        $section->save();

        Toastr::success(translate('messages.status_updated'));
        return back();
    }

    /**
     * Remove the specified section.
     */
    public function destroy($id)
    {
        $section = DynamicSection::findOrFail($id);

        // Delete background image using Storage facade
        if ($section->background_image) {
            Storage::disk(Helpers::getDisk())->delete('dynamic_section/' . $section->background_image);
        }

        $section->items()->detach();
        $section->delete();

        Toastr::success(translate('messages.section_deleted_successfully'));
        return back();
    }

    /**
     * Update priority order.
     */
    public function priority(Request $request)
    {
        $sections = $request->input('sections', []);

        foreach ($sections as $index => $sectionId) {
            DynamicSection::where('id', $sectionId)->update(['priority' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
