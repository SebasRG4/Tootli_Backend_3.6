<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ParcelCategory;
use Illuminate\Http\Request;

class ParcelCategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $base = function () {
                return ParcelCategory::when(config('module.current_module_data'), function ($query) {
                })
                    ->with(['options'])
                    ->active();
            };

            if ($request->boolean('structured')) {
                $buy = $base()->where('buy_and_deliver', 1)->get();
                $pickup = $base()->where(function ($q) {
                    $q->where('buy_and_deliver', 0)->orWhereNull('buy_and_deliver');
                })->get();

                return response()->json([
                    'buy_and_deliver' => Helpers::parcel_category_data_formatting($buy, true),
                    'pickup_and_deliver' => Helpers::parcel_category_data_formatting($pickup, true),
                ], 200);
            }

            $parcel_categories = $base()->get();
            $parcel_categories = Helpers::parcel_category_data_formatting($parcel_categories, true);

            return response()->json($parcel_categories, 200);
        } catch (\Exception $e) {
            info($e->getMessage());
            return response()->json([], 200);
        }
    }
}
