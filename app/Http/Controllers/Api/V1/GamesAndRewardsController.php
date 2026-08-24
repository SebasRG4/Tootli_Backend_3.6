<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DmReward;
use App\Models\DmGame;
use Illuminate\Http\Request;

class GamesAndRewardsController extends Controller
{
    public function get_games_and_rewards(Request $request)
    {
        $rewards = DmReward::with('discounts')->where('status', 1)->get();
        
        $now = now();
        $games = DmGame::where('status', 1)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->get();

        return response()->json([
            'rewards' => $rewards,
            'games' => $games,
        ], 200);
    }
}
