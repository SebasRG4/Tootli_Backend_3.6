<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminAbastosController extends Controller
{
    private function getAdmin(Request $request): ?Admin
    {
        return Admin::where('auth_token', $request->bearerToken())->first();
    }

    /**
     * GET /api/v1/admin/abastos/orders
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

        $query = Order::with(['store'])->where('is_abastos', 1);

        if ($status) {
            if ($status === 'pending') {
                $query->where('order_status', 'pending');
            } elseif ($status === 'processing') {
                $query->whereIn('order_status', ['confirmed', 'processing', 'handover']);
            } elseif ($status === 'delivered') {
                $query->where('order_status', 'delivered');
            } elseif ($status === 'canceled') {
                $query->where('order_status', 'canceled');
            } else {
                $query->where('order_status', $status);
            }
        }

        $orders = $query->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'total_size' => intval($orders->total()),
            'limit' => intval($limit),
            'offset' => intval($offset),
            'orders' => $orders->items(),
        ], 200);
    }

    /**
     * GET /api/v1/admin/abastos/orders/details?order_id=X
     */
    public function details(Request $request)
    {
        $admin = $this->getAdmin($request);
        if (!$admin) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'No autorizado']]], 401);
        }

        $order = Order::with(['store', 'details'])->where('is_abastos', 1)->find($request->order_id);

        if (!$order) {
            return response()->json(['errors' => [['code' => 'order-001', 'message' => 'Pedido no encontrado']]], 404);
        }

        return response()->json($order, 200);
    }

    /**
     * PUT /api/v1/admin/abastos/orders/update-status
     */
    public function updateStatus(Request $request)
    {
        $admin = $this->getAdmin($request);
        if (!$admin) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'No autorizado']]], 401);
        }

        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'status' => 'required|in:pending,accepted,confirmed,processing,handover,picked_up,delivered,canceled,failed'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order = Order::where('is_abastos', 1)->find($request->order_id);
        if (!$order) {
            return response()->json(['errors' => [['code' => 'order-001', 'message' => 'Pedido no encontrado']]], 404);
        }

        $order->order_status = $request->status;
        if ($request->status === 'delivered') {
            $order->payment_status = 'paid';
        }
        $order->save();

        return response()->json([
            'message' => 'Estado del pedido de insumos actualizado exitosamente',
            'order' => $order
        ], 200);
    }
}
