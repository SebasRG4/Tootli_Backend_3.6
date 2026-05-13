<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InterestTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InterestController extends Controller
{
    public function track(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module_name' => 'required|string',
            'module_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        }

        try {
            InterestTrack::create([
                'user_id' => $request->user() ? $request->user()->id : null,
                'module_id' => $request->module_id,
                'module_name' => $request->module_name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json(['message' => 'Interest tracked successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to track interest', 'error' => $e->getMessage()], 500);
        }
    }
}
