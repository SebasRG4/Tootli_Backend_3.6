<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DmGame;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class DmGameController extends Controller
{
    public function index()
    {
        $games = DmGame::latest()->paginate(config('default_pagination'));
        return view('admin-views.delivery-man.games.index', compact('games'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'game_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'ads_enabled' => 'boolean'
        ]);

        DmGame::create([
            'title' => $request->title,
            'description' => $request->description,
            'game_type' => $request->game_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'ads_enabled' => $request->has('ads_enabled') ? 1 : 0
        ]);
        
        Toastr::success('Game scheduled successfully!');
        return back();
    }

    public function status(Request $request)
    {
        $game = DmGame::findOrFail($request->id);
        $game->status = $request->status;
        $game->save();
        Toastr::success('Status updated!');
        return back();
    }

    public function destroy($id)
    {
        $game = DmGame::findOrFail($id);
        $game->delete();
        Toastr::success('Game deleted successfully!');
        return back();
    }
}
