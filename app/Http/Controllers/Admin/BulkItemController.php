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

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Regex para extraer nombre y precio (Busca el último símbolo $ o número al final)
            // Ejemplo: "Chilaquiles sencillos $50" -> Group 1: Chilaquiles sencillos, Group 2: 50
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
                    
                    // Campos por defecto para evitar errores de count() o null
                    $item->variations = json_encode([]);
                    $item->food_variations = json_encode([]);
                    $item->add_ons = json_encode([]);
                    $item->attributes = json_encode([]);
                    $item->choice_options = json_encode([]);
                    $item->unit_id = 1; // Default unit
                    
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
