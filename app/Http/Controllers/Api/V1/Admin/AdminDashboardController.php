<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\WithdrawRequest;
use App\Models\ProvideDMEarning;
use App\Models\OrderTransaction;

class AdminDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $admin = \App\Models\Admin::where('auth_token', $request->bearerToken())->first();
        if (!$admin || $admin->role_id != 1) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'Unauthorized']]], 401);
        }

        $today = Carbon::now();

        // 1. Pedidos que necesiten atencion especial
        // Let's assume pending, delayed, refund_requested or canceled
        $special_attention_orders = Order::whereIn('order_status', ['pending', 'failed', 'canceled', 'refund_requested'])
            ->with(['customer', 'store', 'delivery_man'])
            ->latest()
            ->take(10)
            ->get();

        // 2. Solicitudes de pago de repartidores
        // Usually ProvideDMEarning or withdraw requests
        $payment_requests = WithdrawRequest::where('approved', 0)->with('vendor', 'delivery_man')->latest()->take(10)->get();

        // 3. Ganancia total del dia
        $daily_profit = OrderTransaction::whereDate('created_at', $today)
            ->sum(\Illuminate\Support\Facades\DB::raw('admin_commission + admin_expense - delivery_fee_comission'));

        // 4. Notificacion de pedidos nuevos
        $new_orders = Order::whereDate('created_at', $today)->where('order_status', 'pending')
            ->with(['customer', 'store'])
            ->latest()
            ->take(10)
            ->get();
            
        $new_orders_count = Order::whereDate('created_at', $today)->where('order_status', 'pending')->count();

        return response()->json([
            'special_attention_orders' => $special_attention_orders,
            'payment_requests' => $payment_requests,
            'daily_profit' => $daily_profit,
            'new_orders' => $new_orders,
            'new_orders_count' => $new_orders_count
        ], 200);
    }
}
