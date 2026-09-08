<?php

namespace App\Http\Controllers\Admin\Sabores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Vendor;
use App\Models\Zone;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Config;

class DirectoryPlaceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $places = Store::withoutGlobalScope(\App\Scopes\DirectoryScope::class)
            ->where('is_directory_only', 1)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(config('default_pagination'));

        return view('admin-views.sabores.directory_places.index', compact('places', 'search'));
    }

    public function create()
    {
        return view('admin-views.sabores.directory_places.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:191',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'zone_id' => 'required',
            'logo' => 'required|image|max:2048',
            'cover_photo' => 'nullable|image|max:2048',
        ]);

        $vendor = Vendor::firstOrCreate(
            ['email' => 'directory@tootli.com'],
            [
                'f_name' => 'Directory',
                'l_name' => 'System',
                'phone' => '0000000000',
                'password' => bcrypt(Str::random(16)),
            ]
        );

        $store = new Store;
        $store->name = $request->name;
        $store->phone = '00' . rand(10000000, 99999999);
        $store->email = 'dir_' . uniqid() . '@tootli.com';
        if ($request->has('logo')) {
            $store->logo = Helpers::upload('store/', 'png', $request->file('logo'));
        }
        if ($request->has('cover_photo')) {
            $store->cover_photo = Helpers::upload('store/cover/', 'png', $request->file('cover_photo'));
        }
        $store->address = $request->address ?? '';
        $store->latitude = $request->latitude;
        $store->longitude = $request->longitude;
        $store->vendor_id = $vendor->id;
        $store->zone_id = $request->zone_id;
        $store->tax = 0;
        $store->is_directory_only = 1;
        $store->directory_description = $request->directory_description;
        $store->sabores_map_emoji = $request->sabores_map_emoji;
        $store->module_id = Config::get('module.current_module_id');
        
        $store->save();

        Toastr::success('Lugar de directorio creado exitosamente!');
        return redirect()->route('admin.sabores.directory-places.index');
    }

    public function edit($id)
    {
        $place = Store::withoutGlobalScope(\App\Scopes\DirectoryScope::class)
            ->where('is_directory_only', 1)
            ->findOrFail($id);

        return view('admin-views.sabores.directory_places.edit', compact('place'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:191',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'zone_id' => 'required',
            'logo' => 'nullable|image|max:2048',
            'cover_photo' => 'nullable|image|max:2048',
        ]);

        $store = Store::withoutGlobalScope(\App\Scopes\DirectoryScope::class)
            ->where('is_directory_only', 1)
            ->findOrFail($id);

        $store->name = $request->name;
        $store->address = $request->address ?? '';
        $store->latitude = $request->latitude;
        $store->longitude = $request->longitude;
        $store->zone_id = $request->zone_id;
        $store->directory_description = $request->directory_description;
        $store->sabores_map_emoji = $request->sabores_map_emoji;
        
        if ($request->has('logo')) {
            $store->logo = Helpers::update('store/', $store->logo, 'png', $request->file('logo'));
        }
        
        if ($request->has('cover_photo')) {
            $store->cover_photo = Helpers::update('store/cover/', $store->cover_photo, 'png', $request->file('cover_photo'));
        }

        $store->save();

        Toastr::success('Lugar de directorio actualizado exitosamente!');
        return redirect()->route('admin.sabores.directory-places.index');
    }

    public function destroy($id)
    {
        $store = Store::withoutGlobalScope(\App\Scopes\DirectoryScope::class)
            ->where('is_directory_only', 1)
            ->findOrFail($id);
            
        $store->delete();
        
        Toastr::success('Lugar de directorio eliminado!');
        return back();
    }
}
