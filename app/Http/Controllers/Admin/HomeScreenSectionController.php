<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeScreenSection;
use App\Models\Module;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class HomeScreenSectionController extends Controller
{
    public function index()
    {
        $module = Module::where('module_type', 'grocery')->first();

        if (!$module) {
            Toastr::error(translate('messages.grocery_module_not_found'));
            return back();
        }

        $sections = HomeScreenSection::where('module_id', $module->id)
            ->ordered()
            ->get();

        return view('admin-views.home-screen-sections.index', compact('sections'));
    }

    public function priority(Request $request)
    {
        $sections = $request->input('sections', []);

        foreach ($sections as $index => $sectionId) {
            HomeScreenSection::where('id', $sectionId)->update(['priority' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function status($id)
    {
        $section = HomeScreenSection::findOrFail($id);
        $section->status = !$section->status;
        $section->save();

        Toastr::success(translate('messages.status_updated'));
        return back();
    }
}
