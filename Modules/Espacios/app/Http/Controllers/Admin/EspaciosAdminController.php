<?php

namespace Modules\Espacios\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Espacios\Models\EspacioListing;
use Modules\Espacios\Models\EspacioAmenity;
use App\Models\Store;
use Brian2694\Toastr\Facades\Toastr;

class EspaciosAdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = EspacioListing::with('store');

        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
        }

        $listings = $query->latest()->paginate(15);
        return view('espacios::admin.index', compact('listings', 'search'));
    }

    public function create()
    {
        $stores = Store::active()->get();
        $amenities = EspacioAmenity::all();
        return view('espacios::admin.create', compact('stores', 'amenities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:casa,departamento,habitacion,oficina,sala_eventos,bodega,otro',
            'address' => 'required|string',
            'city' => 'required|string',
            'price_per_night' => 'required|numeric|min:0',
            'max_guests' => 'nullable|integer|min:1',
            'num_rooms' => 'nullable|integer|min:0',
            'num_bathrooms' => 'nullable|integer|min:0',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
            'cancellation_policy' => 'nullable|string',
            'house_rules' => 'nullable|string',
            'safety_property' => 'nullable|string',
            'cover_image' => 'nullable|image',
            'images.*' => 'nullable|image'
        ]);

        $listing = new EspacioListing();
        $listing->fill($request->except(['amenities', 'images', 'cover_image', '_token']));
        
        // Manejo de imagen principal
        if ($request->hasFile('cover_image')) {
            $path = \App\CentralLogics\Helpers::upload('espacios/', 'png', $request->file('cover_image'));
            $listing->cover_image = $path;
        }
        
        $listing->save();

        if ($request->has('amenities')) {
            $listing->amenities()->sync($request->amenities);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $imgPath = \App\CentralLogics\Helpers::upload('espacios/gallery/', 'png', $img);
                $listing->images()->create([
                    'image_path' => $imgPath,
                    'sort_order' => 0
                ]);
            }
        }

        Toastr::success('Espacio creado exitosamente');
        return redirect()->route('admin.espacios.index');
    }

    public function edit($id)
    {
        $listing = EspacioListing::with('images')->findOrFail($id);
        $stores = Store::active()->get();
        $amenities = EspacioAmenity::all();
        
        return view('espacios::admin.edit', compact('listing', 'stores', 'amenities'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:casa,departamento,habitacion,oficina,sala_eventos,bodega,otro',
            'address' => 'required|string',
            'city' => 'required|string',
            'price_per_night' => 'required|numeric|min:0',
            'max_guests' => 'nullable|integer|min:1',
            'num_rooms' => 'nullable|integer|min:0',
            'num_bathrooms' => 'nullable|integer|min:0',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
            'cancellation_policy' => 'nullable|string',
            'house_rules' => 'nullable|string',
            'safety_property' => 'nullable|string',
            'cover_image' => 'nullable|image',
            'images.*' => 'nullable|image'
        ]);

        $listing = EspacioListing::findOrFail($id);
        $listing->fill($request->except(['amenities', 'images', 'cover_image', '_token', '_method']));
        
        if ($request->hasFile('cover_image')) {
            $path = \App\CentralLogics\Helpers::upload('espacios/', 'png', $request->file('cover_image'));
            $listing->cover_image = $path;
        }

        $listing->save();

        if ($request->has('amenities')) {
            $listing->amenities()->sync($request->amenities);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $imgPath = \App\CentralLogics\Helpers::upload('espacios/gallery/', 'png', $img);
                $listing->images()->create([
                    'image_path' => $imgPath,
                    'sort_order' => 0
                ]);
            }
        }

        Toastr::success('Espacio actualizado exitosamente');
        return redirect()->route('admin.espacios.index');
    }

    public function status(Request $request)
    {
        $listing = EspacioListing::findOrFail($request->id);
        $listing->status = $request->status;
        $listing->save();
        
        Toastr::success('Estado cambiado exitosamente');
        return back();
    }

    public function destroy($id)
    {
        $listing = EspacioListing::findOrFail($id);
        $listing->delete();

        Toastr::success('Espacio eliminado exitosamente');
        return redirect()->route('admin.espacios.index');
    }
}
