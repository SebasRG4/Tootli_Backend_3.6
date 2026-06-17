<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use App\CentralLogics\Helpers;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class AbastosOrderController extends Controller
{
    public function scheduleIndex(Request $request)
    {
        $store = Store::withoutGlobalScopes()->where('name', 'like', '%Abastos%')->first();
        if (!$store) {
            Toastr::error('No se encontró la tienda proveedora Tootli Abastos.');
            return redirect()->route('admin.dashboard');
        }

        return view('admin-views.order.abastos-schedule', compact('store'));
    }

    public function updateDeliveryTime(Request $request)
    {
        $request->validate([
            'minimum_delivery_time' => 'required|numeric|min:1',
            'maximum_delivery_time' => 'required|numeric|min:1|gte:minimum_delivery_time',
            'delivery_time_type' => 'required|in:min,hours,days',
        ], [
            'maximum_delivery_time.gte' => 'El tiempo máximo debe ser mayor o igual al mínimo.'
        ]);

        $store = Store::withoutGlobalScopes()->where('name', 'like', '%Abastos%')->firstOrFail();
        $store->delivery_time = $request->minimum_delivery_time . '-' . $request->maximum_delivery_time . ' ' . $request->delivery_time_type;
        $store->save();

        Toastr::success('Horario/Tiempo de entrega de Tootli Abastos actualizado correctamente');
        return back();
    }
    public function list($status, Request $request)
    {
        $search = $request->get('search');
        $query = Order::with(['store.vendor'])->where('is_abastos', 1);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('store', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status !== 'all') {
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

        $orders = $query->orderBy('id', 'DESC')->paginate(config('default_pagination') ?? 15);
        $total = $orders->total();

        // Status counts for tabs
        $statusCounts = [
            'all'        => Order::where('is_abastos', 1)->count(),
            'pending'    => Order::where('is_abastos', 1)->where('order_status', 'pending')->count(),
            'processing' => Order::where('is_abastos', 1)->whereIn('order_status', ['confirmed', 'processing', 'handover'])->count(),
            'delivered'  => Order::where('is_abastos', 1)->where('order_status', 'delivered')->count(),
            'canceled'   => Order::where('is_abastos', 1)->where('order_status', 'canceled')->count(),
        ];

        return view('admin-views.order.abastos-list', compact('orders', 'status', 'total', 'statusCounts'));
    }

    public function details($id)
    {
        $order = Order::with([
            'details',
            'store.vendor',
            'details.item' => function ($query) {
                $query->withoutGlobalScope(\App\Scopes\StoreScope::class);
            }
        ])->where('is_abastos', 1)->findOrFail($id);

        return view('admin-views.order.abastos-view', compact('order'));
    }

    public function statusUpdate(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,accepted,confirmed,processing,handover,picked_up,delivered,canceled,failed'
        ]);

        $order = Order::with(['store.vendor'])->where('is_abastos', 1)->findOrFail($id);
        $order->order_status = $request->status;

        if ($request->status === 'delivered') {
            $order->payment_status = 'paid';
        }

        $order->save();

        // Send push notification to vendor
        if ($order->store && $order->store->vendor && $order->store->vendor->firebase_token) {
            $statusLabels = [
                'pending'    => 'Pendiente',
                'confirmed'  => 'Confirmado',
                'processing' => 'En Preparación',
                'handover'   => 'Listo para Entrega',
                'delivered'  => 'Entregado',
                'canceled'   => 'Cancelado',
            ];
            $statusLabel = $statusLabels[$request->status] ?? $request->status;
            $notification_data = [
                'title'  => 'Tootli Abastos: Pedido Actualizado',
                'body'   => "Tu pedido #{$order->id} ha cambiado a: {$statusLabel}",
                'order_id' => $order->id,
                'type'   => 'abastos_order_status',
            ];
            try {
                Helpers::send_push_notif_to_device($order->store->vendor->firebase_token, $notification_data);
            } catch (\Exception $e) {
                // Fail silently - notifications are non-critical
            }
        }

        Toastr::success('Estado del pedido de insumos actualizado exitosamente');
        return back();
    }
}
