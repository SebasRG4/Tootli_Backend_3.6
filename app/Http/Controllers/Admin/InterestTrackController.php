<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterestTrack;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InterestTrackController extends Controller
{
    public function list(Request $request)
    {
        $interest_tracks = InterestTrack::with('user')->latest()->paginate(config('default_pagination'));
        
        // Group by module for stats
        $stats = InterestTrack::select('module_name')
            ->selectRaw('count(*) as total')
            ->groupBy('module_name')
            ->orderBy('total', 'desc')
            ->get();

        return view('admin-views.interest-track.list', compact('interest_tracks', 'stats'));
    }

    public function delete($id)
    {
        $track = InterestTrack::findOrFail($id);
        $track->delete();
        
        return response()->json(['success' => true]);
    }
}
