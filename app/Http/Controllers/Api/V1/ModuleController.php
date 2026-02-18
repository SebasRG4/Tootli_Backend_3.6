<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Zone;
use Illuminate\Http\Request;


class ModuleController extends Controller
{

    public function index(Request $request)
    {
        if ($request->hasHeader('zoneId')) {
            $zone_id = json_decode($request->header('zoneId'), true);

            $zone_id = is_array($zone_id) ? $zone_id : [$zone_id];

            $modules = Module::with('zones')
                ->withCount([
                    'items',
                    'stores' => function ($query) use ($zone_id) {
                        $query->whereIn('zone_id', $zone_id);
                    }
                ])
                ->whereHas('zones', function ($query) use ($zone_id) {
                    $query->whereIn('zone_id', $zone_id);
                })
                ->active()
                ->orderBy('order', 'asc')
                ->get();
        } else {
            $modules = Module::with('zones')->withCount([
                'items',
                'stores' => function ($query) use ($request) {
                    $query->when($request->zone_id, function ($q) use ($request) {
                        $q->where('zone_id', $request->zone_id);
                    });
                }
            ])
                ->when($request->zone_id, function ($query) use ($request) {
                    $query->whereHas('zones', function ($query) use ($request) {
                        $query->where('zone_id', $request->zone_id);
                    })->notParcel();
                })
                ->active()
                ->orderBy('order', 'asc')
                ->get();
        }

        $latitude = $request->header('latitude');
        $longitude = $request->header('longitude');
        $hexagon_id = null;
        if ($latitude && $longitude) {
            $hexagon_id = \App\CentralLogics\H3Helper::latLngToHex((float) $latitude, (float) $longitude);
        }

        // Batch: fetch ALL grid rules for this hexagon in 1 query instead of N
        $grid_rules_by_module = collect();
        if ($hexagon_id) {
            $all_zone_ids = $modules->flatMap(function ($m) {
                return $m->zones->pluck('id');
            })->unique()->values()->toArray();

            if (!empty($all_zone_ids)) {
                $grid_rules_by_module = \DB::table('delivery_grids')
                    ->whereIn('zone_id', $all_zone_ids)
                    ->where('hexagon_id', $hexagon_id)
                    ->where('is_active', true)
                    ->get()
                    ->groupBy('module_id');
            }
        }

        $modules = $modules->map(function ($item) use ($hexagon_id, $grid_rules_by_module) {
            $item->has_coverage_status = true;
            $item->fast_delivery_status = false;
            $item->current_hexagon_id = $hexagon_id;

            if ($hexagon_id && $grid_rules_by_module->has($item->id)) {
                $zone_ids = $item->zones->pluck('id')->toArray();
                $grid_rule = $grid_rules_by_module->get($item->id)
                    ->whereIn('zone_id', $zone_ids)
                    ->first();

                if ($grid_rule) {
                    if ($grid_rule->delivery_type == 'no_coverage') {
                        $item->has_coverage_status = false;
                    } elseif ($grid_rule->delivery_type == 'minutes') {
                        $item->fast_delivery_status = true;
                    }
                }
            }
            return $item;
        });

        return response()->json($modules);
    }
}
