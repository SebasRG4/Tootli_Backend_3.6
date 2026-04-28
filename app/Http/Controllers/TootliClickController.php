<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;

class TootliClickController extends Controller
{
    public function index($slug)
    {
        $store = Store::where('slug', $slug)->active()->first();

        if (!$store) {
            abort(404, 'Restaurante no encontrado o inactivo.');
        }

        // Obtener categorías que tienen artículos en este restaurante
        // Nota: Se usa 'products' en lugar de 'items' por la relación en el modelo Category
        $categories = Category::whereHas('products', function ($query) use ($store) {
            $query->where('store_id', $store->id)
                  ->where('status', 1)
                  ->where('is_approved', 1);
        })->where('position', 0)->orderBy('priority', 'desc')->get();

        // Obtener artículos agrupados por categoría
        $items_by_category = [];
        foreach ($categories as $category) {
            $items = Item::where('store_id', $store->id)
                ->where('category_id', $category->id)
                ->where('status', 1)
                ->where('is_approved', 1)
                ->get()
                ->map(function($item) {
                    // Lógica de precio: usar menu_price si existe, de lo contrario usar price
                    $item->display_price = ($item->menu_price > 0) ? $item->menu_price : $item->price;
                    return $item;
                });
            
            if ($items->count() > 0) {
                $items_by_category[$category->id] = [
                    'category' => $category,
                    'items' => $items
                ];
            }
        }

        return view('tootliclick.menu', compact('store', 'categories', 'items_by_category'));
    }
}
