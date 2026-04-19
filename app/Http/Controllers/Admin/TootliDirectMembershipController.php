<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountTransaction;
use App\Models\Store;
use App\Models\StoreWallet;
use App\Models\StoreTootliDirectMembership;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TootliDirectMembershipController extends Controller
{
    /** Lista de membresías con búsqueda */
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $memberships = StoreTootliDirectMembership::with(['store'])
            ->when($search, fn($q) => $q->whereHas('store', fn($s) =>
                $s->where('name', 'like', "%$search%")
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin-views.tootli-direct.memberships', compact('memberships', 'search'));
    }

    /** Activar nueva membresía Tootli Direct para una tienda */
    public function activate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id'      => 'required|exists:stores,id',
            'validity_days' => 'required|integer|min:1|max:365',
            'fee'           => 'required|numeric|min:0',
            'notes'         => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());
            return back()->withInput();
        }

        $store = Store::with('vendor.wallet')->findOrFail($request->store_id);

        DB::transaction(function () use ($request, $store) {
            // Desactivar membresías activas previas de esta tienda
            StoreTootliDirectMembership::where('store_id', $store->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $startsAt  = Carbon::now();
            $expiresAt = $startsAt->copy()->addDays((int) $request->validity_days);
            $fee       = (float) $request->fee;

            // Crear nueva membresía
            StoreTootliDirectMembership::create([
                'store_id'      => $store->id,
                'activated_by'  => Auth::id(),
                'validity_days' => (int) $request->validity_days,
                'fee'           => $fee,
                'starts_at'     => $startsAt,
                'expires_at'    => $expiresAt,
                'is_active'     => true,
                'notes'         => $request->notes,
            ]);

            // Descontar fee de la billetera del restaurante (si hay monto)
            if ($fee > 0) {
                $wallet = StoreWallet::firstOrNew(['vendor_id' => $store->vendor_id]);
                $oldBalance = $wallet->balance ?? 0;
                $wallet->total_withdrawn = ($wallet->total_withdrawn ?? 0) + $fee;
                $wallet->save();

                // Registrar transacción contable
                $tx = new AccountTransaction();
                $tx->from_type       = 'store';
                $tx->from_id         = $store->vendor_id;
                $tx->created_by      = 'admin';
                $tx->method          = 'tootli_direct_subscription';
                $tx->ref             = 'membership_' . $store->id;
                $tx->amount          = $fee;
                $tx->current_balance = $oldBalance;
                $tx->type            = 'tootli_direct_fee';
                $tx->save();
            }
        });

        Toastr::success("Membresía Tootli Direct activada para {$store->name} por {$request->validity_days} días.");
        return back();
    }

    /** Desactivar una membresía */
    public function deactivate($id)
    {
        $membership = StoreTootliDirectMembership::findOrFail($id);
        $membership->update(['is_active' => false]);
        Toastr::info('Membresía Tootli Direct desactivada.');
        return back();
    }

    /** API autocomplete para buscar tiendas (food/grocery) */
    public function searchStores(Request $request)
    {
        $q = $request->input('q', '');

        $stores = \DB::table('stores')
            ->join('modules', 'stores.module_id', '=', 'modules.id')
            ->where('stores.name', 'like', "%$q%")
            ->whereIn('modules.module_type', ['food', 'grocery'])
            ->select('stores.id', 'stores.name', 'stores.address')
            ->limit(15)
            ->get();

        return response()->json($stores);
    }
}
