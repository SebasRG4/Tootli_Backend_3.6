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

    public function dailyReport(Request $request)
    {
        $admin = \App\Models\Admin::where('auth_token', $request->bearerToken())->first();
        if (!$admin || $admin->role_id != 1) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => 'Unauthorized']]], 401);
        }

        $today = Carbon::now();

        // 1. Sales metrics
        $total_sales = Order::whereDate('created_at', $today)
            ->where('order_status', '!=', 'canceled')
            ->sum('order_amount');

        $admin_commission = OrderTransaction::whereDate('created_at', $today)
            ->sum('admin_commission');

        $admin_expense = OrderTransaction::whereDate('created_at', $today)
            ->sum('admin_expense');

        $delivery_fee_commission = OrderTransaction::whereDate('created_at', $today)
            ->sum('delivery_fee_comission');

        $store_amount = OrderTransaction::whereDate('created_at', $today)
            ->sum('store_amount');

        $delivery_man_amount = OrderTransaction::whereDate('created_at', $today)
            ->sum(\Illuminate\Support\Facades\DB::raw('original_delivery_charge + dm_tips'));

        $additional_charge = OrderTransaction::whereDate('created_at', $today)
            ->sum('additional_charge');

        $delivery_fees = OrderTransaction::whereDate('created_at', $today)
            ->sum('original_delivery_charge');

        $admin_net_income = $admin_commission + $admin_expense + $delivery_fee_commission;

        // 2. Order Funnel
        $total_orders = Order::whereDate('created_at', $today)->count();
        $delivered_orders = Order::whereDate('created_at', $today)->where('order_status', 'delivered')->count();
        $pending_orders = Order::whereDate('created_at', $today)->where('order_status', 'pending')->count();
        $processing_orders = Order::whereDate('created_at', $today)
            ->whereIn('order_status', ['accepted', 'confirmed', 'processing', 'handover', 'picked_up'])
            ->count();
        $canceled_orders = Order::whereDate('created_at', $today)->where('order_status', 'canceled')->count();

        // 3. Module Breakdown
        $module_breakdown = Order::whereDate('created_at', $today)
            ->where('order_status', '!=', 'canceled')
            ->select('module_id', \Illuminate\Support\Facades\DB::raw('count(*) as count, sum(order_amount) as sales'))
            ->groupBy('module_id')
            ->with('module')
            ->get()
            ->map(function($item) {
                return [
                    'module_id' => $item->module_id,
                    'module_name' => $item->module ? $item->module->module_name : 'Otro',
                    'count' => intval($item->count),
                    'sales' => floatval($item->sales),
                ];
            });

        // 4. Top Stores of the Day
        $top_stores = Order::whereDate('created_at', $today)
            ->where('order_status', '!=', 'canceled')
            ->select('store_id', \Illuminate\Support\Facades\DB::raw('count(*) as count, sum(order_amount) as sales'))
            ->groupBy('store_id')
            ->orderBy('sales', 'desc')
            ->take(5)
            ->with('store')
            ->get()
            ->map(function($item) {
                return [
                    'store_name' => $item->store ? $item->store->name : 'Tienda Desconocida',
                    'count' => intval($item->count),
                    'sales' => floatval($item->sales),
                ];
            });

        // 5. Top Delivery Men of the Day
        $top_delivery_men = Order::whereDate('created_at', $today)
            ->where('order_status', 'delivered')
            ->whereNotNull('delivery_man_id')
            ->select('delivery_man_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('delivery_man_id')
            ->orderBy('count', 'desc')
            ->take(5)
            ->with('delivery_man')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->delivery_man ? ($item->delivery_man->f_name . ' ' . $item->delivery_man->l_name) : 'Repartidor',
                    'count' => intval($item->count),
                ];
            });

        return response()->json([
            'sales' => [
                'total_sales' => floatval($total_sales),
                'admin_commissions' => floatval($admin_net_income),
                'delivery_fees' => floatval($delivery_fees),
                'store_commission' => floatval($admin_commission),
                'service_charge' => floatval($admin_expense),
                'delivery_fee_commission' => floatval($delivery_fee_commission),
                'store_net_income' => floatval($store_amount),
                'delivery_man_net_income' => floatval($delivery_man_amount),
                'additional_charge' => floatval($additional_charge),
            ],
            'orders' => [
                'total' => intval($total_orders),
                'delivered' => intval($delivered_orders),
                'pending' => intval($pending_orders),
                'processing' => intval($processing_orders),
                'canceled' => intval($canceled_orders),
            ],
            'module_breakdown' => $module_breakdown,
            'top_stores' => $top_stores,
            'top_delivery_men' => $top_delivery_men,
        ], 200);
    }
}
