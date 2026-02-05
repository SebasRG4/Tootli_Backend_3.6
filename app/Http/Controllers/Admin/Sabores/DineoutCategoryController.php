<?php

namespace App\Http\Controllers\Admin\Sabores;

use App\Http\Controllers\Controller;
use App\Models\DineoutCategory;
use App\Models\Store;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;

class DineoutCategoryController extends Controller
{
    /**
     * Display a listing of dineout categories.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $categories = DineoutCategory::when($search, function ($query) use ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        })
            ->orderBy('position')
            ->paginate(config('default_pagination'));

        return view('admin-views.sabores.dineout-categories.index', compact('categories', 'search'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('admin-views.sabores.dineout-categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:dineout_categories,name',
            'image' => 'required|string|max:50',
            'position' => 'nullable|integer|min:0',
        ]);

        $maxPosition = DineoutCategory::max('position') ?? 0;

        DineoutCategory::create([
            'name' => $request->name,
            'image' => $request->image,
            'position' => $request->position ?? ($maxPosition + 1),
            'status' => true,
        ]);

        Toastr::success(translate('messages.category_created_successfully'));
        return redirect()->route('admin.sabores.dineout-categories.index');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit($id)
    {
        $category = DineoutCategory::findOrFail($id);
        return view('admin-views.sabores.dineout-categories.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = DineoutCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:dineout_categories,name,' . $id,
            'image' => 'required|string|max:50',
            'position' => 'nullable|integer|min:0',
        ]);

        $category->update([
            'name' => $request->name,
            'image' => $request->image,
            'position' => $request->position ?? $category->position,
        ]);

        Toastr::success(translate('messages.category_updated_successfully'));
        return redirect()->route('admin.sabores.dineout-categories.index');
    }

    /**
     * Toggle the status of the specified category.
     */
    public function toggleStatus($id)
    {
        $category = DineoutCategory::findOrFail($id);
        $category->status = !$category->status;
        $category->save();

        Toastr::success(translate('messages.status_updated_successfully'));
        return redirect()->back();
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = DineoutCategory::findOrFail($id);

        // First, detach all stores from this category
        $category->stores()->detach();

        $category->delete();

        Toastr::success(translate('messages.category_deleted_successfully'));
        return redirect()->route('admin.sabores.dineout-categories.index');
    }

    /**
     * Show the store assignment page for a category.
     */
    public function stores($id)
    {
        $category = DineoutCategory::with('stores')->findOrFail($id);

        // Get restaurants that are in the 'restaurant' module type
        $moduleId = \App\Models\Module::where('module_type', 'food')->first()?->id;

        $availableStores = Store::when($moduleId, function ($query) use ($moduleId) {
            $query->where('module_id', $moduleId);
        })
            ->where('status', 1)
            ->whereNotIn('id', $category->stores->pluck('id'))
            ->orderBy('name')
            ->get();

        $assignedStores = $category->stores()->orderBy('name')->paginate(10);

        return view('admin-views.sabores.dineout-categories.stores', compact('category', 'availableStores', 'assignedStores'));
    }

    /**
     * Assign a store to the category.
     */
    public function assignStore(Request $request, $id)
    {
        $category = DineoutCategory::findOrFail($id);

        $request->validate([
            'store_id' => 'required|exists:stores,id',
        ]);

        // Check if store is already assigned
        if (!$category->stores()->where('store_id', $request->store_id)->exists()) {
            $category->stores()->attach($request->store_id);
            Toastr::success(translate('messages.store_assigned_successfully'));
        } else {
            Toastr::warning(translate('messages.store_already_assigned'));
        }

        return redirect()->back();
    }

    /**
     * Remove a store from the category.
     */
    public function removeStore($categoryId, $storeId)
    {
        $category = DineoutCategory::findOrFail($categoryId);
        $category->stores()->detach($storeId);

        Toastr::success(translate('messages.store_removed_successfully'));
        return redirect()->back();
    }
}
