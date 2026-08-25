<?php
$order = App\Models\Order::with('store')->find(5); // Was canceled, but still has store and zone info
$excludeDm = $order->delivery_man_id;
$deliveryMen = App\Models\DeliveryMan::where('zone_id', $order->store->zone_id)->when($order->dm_vehicle_id && optional($order->module)->module_type == 'taxi', function ($query) use ($order) {
    $query->where(function ($q) use ($order) {
        $q->where('vehicle_id', $order->dm_vehicle_id)->orWhereNull('vehicle_id');
    });
})->where('id', '!=', $excludeDm)->available()->active()->get();
echo "\n====DELIVERY_MEN_5_FIXED====\n";
echo json_encode($deliveryMen->pluck('id'));
echo "\n====END====\n";
