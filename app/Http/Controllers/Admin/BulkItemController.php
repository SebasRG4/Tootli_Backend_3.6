<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use App\Models\Store;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class BulkItemController extends Controller
{
    public function index()
    {
        $stores = Store::orderBy('name')->get(['id', 'name']);
        return view('admin-views.product.bulk-import-text', compact('stores'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'category_id' => 'required',
            'bulk_text' => 'required',
        ]);

        $lines = explode("\n", $request->bulk_text);
        $store = Store::find($request->store_id);
        $module_id = $store->module_id;
        $items_count = 0;

        $common_variations = [];
        if ($request->has('var_name')) {
            foreach ($request->var_name as $index => $v_name) {
                if (empty($v_name) || empty($request->var_options[$index])) continue;
                
                $options = [];
                $opt_parts = explode(',', $request->var_options[$index]);
                foreach ($opt_parts as $opt_part) {
                    $p = explode(':', $opt_part);
                    $options[] = [
                        'label' => trim($p[0]),
                        'optionPrice' => isset($p[1]) ? floatval(trim($p[1])) : 0
                    ];
                }

                $common_variations[] = [
                    'name' => $v_name,
                    'type' => $request->var_type[$index],
                    'min' => ($request->var_type[$index] == 'multi' ? 1 : 0),
                    'max' => ($request->var_type[$index] == 'multi' ? count($options) : 1),
                    'required' => 'off',
                    'values' => $options
                ];
            }
        }

        // Subir imagen por lote si existe
        $batch_image = null;
        if ($request->hasFile('image')) {
            $batch_image = Helpers::upload('product/', 'png', $request->file('image'));
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^(.*?)\$?(\d+(?:\.\d+)?)\s*$/', $line, $matches)) {
                $name = trim($matches[1], " -");
                $price = floatval($matches[2]);

                if (!empty($name) && $price > 0) {
                    $item = new Item();
                    $item->name = $name;
                    $item->price = $price;
                    $item->store_id = $request->store_id;
                    $item->module_id = $module_id;
                    $item->category_id = $request->category_id;
                    $item->category_ids = json_encode([['id' => $request->category_id, 'position' => 1]]);
                    $item->status = 1;
                    $item->is_approved = 1;
                    $item->description = $name; 
                    $item->slug = Str::slug($name) . '-' . rand(100, 999);
                    
                    // Horarios
                    $item->available_time_starts = $request->available_time_starts ?? '00:00:00';
                    $item->available_time_ends = $request->available_time_ends ?? '23:59:59';

                    // Imagen por lote
                    $item->image = $batch_image;

                    // Variaciones Masivas
                    $item->food_variations = json_encode($common_variations);
                    
                    // Otros campos técnicos
                    $item->variations = json_encode([]);
                    $item->add_ons = json_encode([]);
                    $item->attributes = json_encode([]);
                    $item->choice_options = json_encode([]);
                    $item->images = json_encode([]);
                    $item->unit_id = 1; 
                    $item->tax = 0;
                    $item->discount = 0;
                    $item->discount_type = 'amount';
                    
                    $item->save();

                    // Traducciones obligatorias
                    Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Item', data_id: $item->id, data_value: $name);
                    
                    $items_count++;
                }
            }
        }

        Toastr::success($items_count . ' productos agregados correctamente.');
        return back();
    }
}
