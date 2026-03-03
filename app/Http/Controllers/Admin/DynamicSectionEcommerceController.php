<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\DynamicSectionEcommerce;
use App\Models\Store;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class DynamicSectionEcommerceController extends Controller
{
    /**
     * List all sections for the current module.
     */
    public function index(Request $request)
    {
        $sections = DynamicSectionEcommerce::byModule(Config::get('module.current_module_id'))
            ->withCount('stores')
            ->orderBy('priority')
            ->paginate(config('default_pagination'));

        $stores = Store::where('module_id', Config::get('module.current_module_id'))
            ->where('status', 1)
            ->select('id', 'name')
            ->get();

        return view('admin-views.dynamic-section-ecommerce.index', compact('sections', 'stores'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $stores = Store::where('module_id', Config::get('module.current_module_id'))
            ->where('status', 1)
            ->select('id', 'name')
            ->get();

        return view('admin-views.dynamic-section-ecommerce.create', compact('stores'));
    }

    /**
     * Store a new section.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'stores' => 'nullable|array',
            'stores.*' => 'exists:stores,id',
        ]);

        $section = new DynamicSectionEcommerce();
        $section->module_id = Config::get('module.current_module_id');
        $section->priority = DynamicSectionEcommerce::byModule(Config::get('module.current_module_id'))->max('priority') + 1;
        $section->status = $request->has('status') ? 1 : 0;

        if ($request->hasFile('image')) {
            $section->image = Helpers::upload('dynamic_section_ecommerce/', 'png', $request->file('image'));
        }

        $section->save();

        if ($request->has('stores') && is_array($request->stores)) {
            $storesWithPriority = [];
            foreach ($request->stores as $index => $storeId) {
                $storesWithPriority[$storeId] = ['priority' => $index];
            }
            $section->stores()->attach($storesWithPriority);
        }

        Toastr::success(translate('messages.section_created_successfully'));
        return redirect()->route('admin.dynamic-section-ecommerce.index');
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $section = DynamicSectionEcommerce::with('stores')->findOrFail($id);

        $stores = Store::where('module_id', Config::get('module.current_module_id'))
            ->where('status', 1)
            ->select('id', 'name')
            ->get();

        $selectedStores = $section->stores->pluck('id')->toArray();

        return view('admin-views.dynamic-section-ecommerce.edit', compact('section', 'stores', 'selectedStores'));
    }

    /**
     * Update an existing section.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'stores' => 'nullable|array',
            'stores.*' => 'exists:stores,id',
        ]);

        $section = DynamicSectionEcommerce::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($section->image) {
                Storage::disk(Helpers::getDisk())->delete('dynamic_section_ecommerce/' . $section->image);
            }
            $section->image = Helpers::upload('dynamic_section_ecommerce/', 'png', $request->file('image'));
        }

        $section->status = $request->has('status') ? 1 : 0;
        $section->save();

        if ($request->has('stores') && is_array($request->stores)) {
            $storesWithPriority = [];
            foreach ($request->stores as $index => $storeId) {
                $storesWithPriority[$storeId] = ['priority' => $index];
            }
            $section->stores()->sync($storesWithPriority);
        } else {
            $section->stores()->detach();
        }

        Toastr::success(translate('messages.section_updated_successfully'));
        return redirect()->route('admin.dynamic-section-ecommerce.index');
    }

    /**
     * Toggle status.
     */
    public function status(Request $request)
    {
        $section = DynamicSectionEcommerce::findOrFail($request->id);
        $section->status = !$section->status;
        $section->save();

        Toastr::success(translate('messages.status_updated'));
        return back();
    }

    /**
     * Delete a section.
     */
    public function destroy($id)
    {
        $section = DynamicSectionEcommerce::findOrFail($id);

        if ($section->image) {
            Storage::disk(Helpers::getDisk())->delete('dynamic_section_ecommerce/' . $section->image);
        }

        $section->stores()->detach();
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
            DynamicSectionEcommerce::where('id', $sectionId)->update(['priority' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
