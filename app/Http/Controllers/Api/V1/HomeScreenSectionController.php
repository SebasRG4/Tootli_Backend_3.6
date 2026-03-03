<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HomeScreenSection;
use App\Models\Module;
use Illuminate\Http\Request;

class HomeScreenSectionController extends Controller
{
    public function index(Request $request)
    {
        $moduleId = $request->header('moduleId');

        if (!$moduleId) {
            return response()->json(['sections' => []], 200);
        }

        $sections = HomeScreenSection::where('module_id', $moduleId)
            ->ordered()
            ->get(['key', 'priority', 'status']);

        return response()->json(['sections' => $sections], 200);
    }
}
