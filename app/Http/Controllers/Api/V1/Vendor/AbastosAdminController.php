<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;

class AbastosAdminController extends Controller
{
    private function isAbastosAdmin($vendor)
    {
        $abastos_store = Store::withoutGlobalScopes()
            ->whereHas('module', function ($q) {
                $q->where('module_type', 'grocery');
            })
            ->first();

        return $abastos_store && $abastos_store->vendor_id == $vendor->id;
    }

    public function get_received_orders(Request $request)
    {
        $vendor = $request['vendor'];
        if (!$this->isAbastosAdmin($vendor)) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => 'No autorizado']]], 403);
        }

        $orders = Order::where('is_abastos', 1)
            ->with(['store.vendor', 'details'])
            ->orderByDesc('id')
            ->paginate($request->get('limit', 15), ['*'], 'page', $request->get('offset', 1));

        $data = [
            'total_size' => $orders->total(),
            'limit' => $request->get('limit', 15),
            'offset' => $request->get('offset', 1),
            'orders' => Helpers::order_data_formatting($orders->items(), true)
        ];
        return response()->json($data);
    }

    public function search_freelance(Request $request)
    {
        $vendor = $request['vendor'];
        if (!$this->isAbastosAdmin($vendor)) return response()->json(['errors' => [['code' => 'unauthorized', 'message' => 'No autorizado']]], 403);
        
        $order = Order::where('is_abastos', 1)->find($request->order_id);
        if(!$order) return response()->json(['errors' => [['code' => 'order', 'message' => 'No encontrado']]], 404);

        $order->order_type = 'delivery';
        $order->delivery_man_id = null;
        $order->save();

        // Normally, pushing notifications to zone deliverymen
        \App\CentralLogics\Helpers::send_order_notification($order);

        return response()->json(['message' => 'Buscando repartidor freelance']);
    }

    public function self_delivery(Request $request)
    {
        $vendor = $request['vendor'];
        if (!$this->isAbastosAdmin($vendor)) return response()->json(['errors' => [['code' => 'unauthorized', 'message' => 'No autorizado']]], 403);
        
        $order = Order::where('is_abastos', 1)->find($request->order_id);
        if(!$order) return response()->json(['errors' => [['code' => 'order', 'message' => 'No encontrado']]], 404);

        $order->order_type = 'take_away'; // Treated as self delivery if admin takes it
        $order->delivery_man_id = null;
        $order->save();

        return response()->json(['message' => 'Autoentrega configurada']);
    }

    public function assign_deliveryman(Request $request)
    {
        $vendor = $request['vendor'];
        if (!$this->isAbastosAdmin($vendor)) return response()->json(['errors' => [['code' => 'unauthorized', 'message' => 'No autorizado']]], 403);
        
        $order = Order::where('is_abastos', 1)->find($request->order_id);
        if(!$order) return response()->json(['errors' => [['code' => 'order', 'message' => 'No encontrado']]], 404);

        $order->order_type = 'delivery';
        $order->delivery_man_id = $request->delivery_man_id;
        $order->save();

        $dm = \App\Models\DeliveryMan::find($request->delivery_man_id);
        if($dm) {
            \App\CentralLogics\Helpers::send_deliveryman_notification($order, $dm);
        }

        return response()->json(['message' => 'Repartidor asignado']);
    }
}
