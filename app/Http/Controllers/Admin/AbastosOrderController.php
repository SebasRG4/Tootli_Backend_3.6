<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class AbastosOrderController extends Controller
{
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

        return view('admin-views.order.abastos-list', compact('orders', 'status', 'total'));
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

        $order = Order::where('is_abastos', 1)->findOrFail($id);
        $order->order_status = $request->status;

        if ($request->status === 'delivered') {
            $order->payment_status = 'paid';
        }

        $order->save();

        Toastr::success('Estado del pedido de insumos actualizado exitosamente');
        return back();
    }
}
