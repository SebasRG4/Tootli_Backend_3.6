<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreTootliDirectTrial;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TootliDirectTrialController extends Controller
{
    /** Lista de trials activos + formulario para otorgar nuevos */
    public function index(Request $request)
    {
        $search  = $request->input('search', '');
        $trials  = StoreTootliDirectTrial::with(['store'])
            ->when($search, fn($q) => $q->whereHas('store', fn($s) =>
                $s->where('name', 'like', "%$search%")
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin-views.tootli-direct.trials', compact('trials', 'search'));
    }

    /** Otorgar trial a una tienda */
    public function grant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id'       => 'required|exists:stores,id',
            'granted_orders' => 'required|integer|min:1|max:500',
            'notes'          => 'nullable|string|max:500',
            'expires_at'     => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());
            return back()->withInput();
        }

        StoreTootliDirectTrial::create([
            'store_id'       => $request->store_id,
            'granted_orders' => $request->granted_orders,
            'used_orders'    => 0,
            'granted_by'     => Auth::id(),
            'notes'          => $request->notes,
            'expires_at'     => $request->expires_at ?: null,
            'is_active'      => true,
        ]);

        $store = Store::find($request->store_id);
        Toastr::success("Trial Tootli Direct otorgado a {$store->name}: {$request->granted_orders} órdenes.");
        return back();
    }

    /** Desactivar un trial */
    public function deactivate($id)
    {
        $trial = StoreTootliDirectTrial::findOrFail($id);
        $trial->update(['is_active' => false]);
        Toastr::info('Trial desactivado.');
        return back();
    }

    /** API para buscar tiendas (autocomplete) */
    public function searchStores(Request $request)
    {
        $q      = $request->input('q', '');
        $stores = Store::withoutGlobalScopes()
            ->where('name', 'like', "%$q%")
            ->whereIn('module_type', ['food', 'grocery'])
            ->select('id', 'name', 'address')
            ->limit(15)
            ->get();

        return response()->json($stores);
    }
}
