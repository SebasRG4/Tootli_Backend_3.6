<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Order;
use App\Models\DeliveryMan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminOrderController extends Controller
{
    private function getAdmin(Request $request): ?Admin
    {
        return Admin::where('auth_token', $request->bearerToken())->first();
    }

    /**
     * GET /api/v1/admin/order/list
     * Query orders with various filters:
     * - status: 'pending', 'accepted', 'processing', 'handover', 'picked_up', 'delivered', 'canceled', 'failed', 'offline_payment'
     * - search_key: term to search order_id, customer name, phone, etc.
     */
    public function list(Request $request)
    {
        $admin = $this->getAdmin($request);
        if (!$admin) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'No autorizado']]], 401);
        }

        $limit = $request->get('limit', 15);
        $offset = $request->get('offset', 1);
        $status = $request->get('status');
        $searchKey = $request->get('search_key');

        $query = Order::with(['customer', 'store', 'delivery_man']);

        // Filtrar por estado
        if ($status) {
            if ($status === 'offline_payment') {
                // Pedidos que requieren revisión de pago fuera de línea
                $query->where('payment_method', 'offline_payment')
                      ->whereIn('order_status', ['pending', 'failed']);
            } elseif ($status === 'pending') {
                $query->where('order_status', 'pending');
            } elseif ($status === 'accepted') {
                $query->where('order_status', 'accepted');
            } elseif ($status === 'processing') {
                $query->whereIn('order_status', ['confirmed', 'processing', 'handover']);
            } elseif ($status === 'picked_up') {
                $query->where('order_status', 'picked_up');
            } elseif ($status === 'delivered') {
                $query->where('order_status', 'delivered');
            } elseif ($status === 'canceled') {
                $query->where('order_status', 'canceled');
            } else {
                $query->where('order_status', $status);
            }
        }

        // Búsqueda
        if ($searchKey) {
            $query->where(function ($q) use ($searchKey) {
                $q->where('id', 'like', "%{$searchKey}%")
                  ->orWhereHas('customer', function ($q2) use ($searchKey) {
                      $q2->where('f_name', 'like', "%{$searchKey}%")
                         ->orWhere('l_name', 'like', "%{$searchKey}%")
                         ->orWhere('phone', 'like', "%{$searchKey}%");
                  });
            });
        }

        $orders = $query->orderBy('id', 'DESC')
            ->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'total_size' => intval($orders->total()),
            'limit' => intval($limit),
            'offset' => intval($offset),
            'orders' => $orders->items(),
        ], 200);
    }

    /**
     * GET /api/v1/admin/order/details?order_id=X
     */
    public function details(Request $request)
    {
        $admin = $this->getAdmin($request);
        if (!$admin) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'No autorizado']]], 401);
        }

        $order = Order::with(['customer', 'store', 'delivery_man', 'details', 'offline_payments'])
            ->find($request->order_id);

        if (!$order) {
            return response()->json(['errors' => [['code' => 'order-001', 'message' => 'Pedido no encontrado']]], 404);
        }

        return response()->json($order, 200);
    }

    /**
     * POST /api/v1/admin/order/update-status
     * Body: { order_id: X, status: 'accepted' | 'processing' | 'delivered' | 'canceled' | 'confirmed' ... }
     */
    public function updateStatus(Request $request)
    {
        $admin = $this->getAdmin($request);
        if (!$admin) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'No autorizado']]], 401);
        }

        $request->validate([
            'order_id' => 'required',
            'status' => 'required|in:pending,accepted,confirmed,processing,handover,picked_up,delivered,canceled,failed'
        ]);

        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json(['errors' => [['code' => 'order-001', 'message' => 'Pedido no encontrado']]], 404);
        }

        $order->order_status = $request->status;
        if ($request->status == 'delivered') {
            $order->payment_status = 'paid';
        }
        $order->save();

        // Enviar push a cliente y repartidor si corresponde
        try {
            $customerFcm = $order->customer?->cm_firebase_token;
            if ($customerFcm) {
                Helpers::send_push_notif_to_device($customerFcm, [
                    'title' => 'Actualización de Pedido',
                    'description' => 'Tu pedido #' . $order->id . ' ahora está ' . trans('messages.' . $request->status),
                    'order_id' => $order->id,
                    'image' => '',
                    'type' => 'order_status',
                ]);
            }
            if ($order->delivery_man?->fcm_token) {
                Helpers::send_push_notif_to_device($order->delivery_man->fcm_token, [
                    'title' => 'Actualización de Pedido',
                    'description' => 'El pedido #' . $order->id . ' asignado ha cambiado de estado.',
                    'order_id' => $order->id,
                    'image' => '',
                    'type' => 'order_status',
                ]);
            }
        } catch (\Exception $e) {}

        return response()->json(['message' => 'Estado del pedido actualizado exitosamente', 'order' => $order], 200);
    }

    /**
     * GET /api/v1/admin/order/delivery-men?order_id=X
     * Returns list of delivery men that are active and can be assigned.
     */
    public function getAvailableDeliveryMen(Request $request)
    {
        $admin = $this->getAdmin($request);
        if (!$admin) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'No autorizado']]], 401);
        }

        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json(['errors' => [['code' => 'order-001', 'message' => 'Pedido no encontrado']]], 404);
        }

        // Cargar todos los repartidores aprobados con su última ubicación conocida
        $deliveryMen = DeliveryMan::where('application_status', 'approved')
            ->with('last_location')
            ->get(['id', 'f_name', 'l_name', 'phone', 'image', 'type', 'active', 'current_orders']);

        $deliveryMen->map(function($dm) {
            $dm->latitude = $dm->last_location ? $dm->last_location->latitude : null;
            $dm->longitude = $dm->last_location ? $dm->last_location->longitude : null;
            unset($dm->last_location);
            return $dm;
        });

        return response()->json($deliveryMen, 200);
    }

    /**
     * POST /api/v1/admin/order/assign-delivery-man
     * Body: { order_id: X, delivery_man_id: Y }
     */
    public function assignDeliveryMan(Request $request)
    {
        $admin = $this->getAdmin($request);
        if (!$admin) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'No autorizado']]], 401);
        }

        $request->validate([
            'order_id' => 'required',
            'delivery_man_id' => 'required'
        ]);

        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json(['errors' => [['code' => 'order-001', 'message' => 'Pedido no encontrado']]], 404);
        }

        $deliveryMan = DeliveryMan::find($request->delivery_man_id);
        if (!$deliveryMan) {
            return response()->json(['errors' => [['code' => 'deliveryman-001', 'message' => 'Repartidor no encontrado']]], 404);
        }

        $order->delivery_man_id = $deliveryMan->id;
        // Si estaba pendiente, al asignar repartidor suele cambiar a 'accepted' o 'confirmed'
        if ($order->order_status == 'pending') {
            $order->order_status = 'accepted';
        }
        $order->save();

        // Enviar push al repartidor
        try {
            if ($deliveryMan->fcm_token) {
                Helpers::send_push_notif_to_device($deliveryMan->fcm_token, [
                    'title' => 'Nuevo pedido asignado',
                    'description' => 'Se te ha asignado el pedido #' . $order->id,
                    'order_id' => $order->id,
                    'image' => '',
                    'type' => 'order_request',
                ]);
            }
        } catch (\Exception $e) {}

        return response()->json(['message' => 'Repartidor asignado exitosamente', 'order' => $order], 200);
    }
}
