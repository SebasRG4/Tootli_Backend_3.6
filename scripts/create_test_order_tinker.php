<?php
/**
 * Script para crear un pedido de prueba desde Laravel Tinker.
 *
 * Uso:
 *   php artisan tinker --execute="require 'scripts/create_test_order_tinker.php'; createTestOrder(5, 1, 1);"
 */

function createTestOrder(int $deliveryManId = 5, int $userId = 1, int $storeId = 1, int $addressId = 1): void
{
    echo "🚀 Creando pedido de prueba...\n";

    // 1. Verificar que el repartidor existe y está activo
    $deliveryMan = App\Models\DeliveryMan::find($deliveryManId);
    if (!$deliveryMan) {
        echo "❌ Repartidor #{$deliveryManId} no encontrado.\n";
        return;
    }
    echo "✅ Repartidor: {$deliveryMan->f_name} {$deliveryMan->l_name} (ID: {$deliveryManId})\n";
    echo "   Firebase Token: " . ($deliveryMan->firebase_token ?: 'N/A') . "\n";
    echo "   Active Status: " . ($deliveryMan->active_status ? 'Activo' : 'Inactivo') . "\n";

    // 2. Verificar usuario cliente
    $user = App\Models\User::find($userId);
    if (!$user) {
        echo "❌ Usuario #{$userId} no encontrado.\n";
        return;
    }
    echo "✅ Cliente: {$user->f_name} {$user->l_name} (ID: {$userId})\n";

    // 3. Verificar tienda
    $store = App\Models\Store::find($storeId);
    if (!$store) {
        echo "❌ Tienda #{$storeId} no encontrada.\n";
        return;
    }
    echo "✅ Tienda: {$store->name} (ID: {$storeId})\n";

    // 4. Verificar dirección (toma de la base de datos)
    $address = App\Models\CustomerAddress::find($addressId);
    if (!$address) {
        $address = App\Models\CustomerAddress::where('user_id', $userId)->first();
        $addressId = $address ? $address->id : null;
    }

    if (!$address) {
        echo "❌ No se encontró ninguna dirección para el usuario #{$userId}.\n";
        return;
    }

    echo "✅ Dirección: {$address->address}\n";
    echo "   Lat: {$address->latitude} | Lng: {$address->longitude}\n";

    // 5. Crear el pedido
    $order = new App\Models\Order();
    $order->user_id = $userId;
    $order->store_id = $storeId;
    $order->order_amount = 150.00;
    $order->coupon_discount_amount = 0;
    $order->order_status = 'pending';
    $order->payment_status = 'paid';
    $order->payment_method = 'cash';
    $order->order_type = 'delivery';
    $order->delivery_address_id = $addressId;
    $order->module_id = $store->module_id ?? 1;
    $order->zone_id = $store->zone_id ?? 1;
    $order->delivery_charge = 35.00;
    $order->original_delivery_charge = 35.00;

    // === IMPORTANTE: Guardar las coordenadas en el pedido ===
    $order->delivery_address = json_encode([
        'contact_person_name'   => $address->contact_person_name ?? ($user->f_name . ' ' . $user->l_name),
        'contact_person_number' => $address->contact_person_number ?? $user->phone,
        'address_type'          => $address->address_type ?? 'home',
        'address'               => $address->address,
        'floor'                 => $address->floor ?? '',
        'road'                  => $address->road ?? '',
        'house'                 => $address->house ?? '',
        'longitude'             => (string) $address->longitude,
        'latitude'              => (string) $address->latitude,
    ]);

    $order->save();

    echo "\n📦 Pedido #{$order->id} creado (estado: pending)\n";

    // 6. Crear detalles del pedido (items)
    $detail = new App\Models\OrderDetail();
    $detail->order_id = $order->id;
    $detail->item_id = 1;
    $detail->item_details = json_encode([
        'name'  => 'Tacos al Pastor',
        'price' => 100.00,
    ]);
    $detail->quantity = 1;
    $detail->price = 100.00;
    $detail->tax_amount = 16.00;
    $detail->discount_on_item = 0;
    $detail->save();

    echo "   Item agregado: Tacos al Pastor x1\n";

    // 7. Asignar repartidor
    $order->delivery_man_id = $deliveryManId;
    $order->order_status = 'confirmed'; // o 'pending' si quieres probar peticiones entrantes
    $order->save();

    echo "✅ Repartidor asignado (estado: confirmed)\n";

    // 8. Intentar enviar FCM
    if ($deliveryMan->firebase_token) {
        echo "\n📲 Enviando FCM al repartidor...\n";
        try {
            if (class_exists('\Fcm')) {
                \Fcm::sendToDevice($deliveryMan->firebase_token, [
                    'notification' => [
                        'title' => 'Nuevo pedido #' . $order->id,
                        'body'  => 'Tienes una nueva solicitud de entrega',
                    ],
                    'data' => [
                        'type'     => 'new_order',
                        'order_id' => (string) $order->id,
                        'title'    => 'Nuevo pedido #' . $order->id,
                        'body'     => 'Tienes una nueva solicitud de entrega',
                    ],
                ]);
                echo "✅ FCM enviado via Fcm::sendToDevice\n";
            } else {
                echo "⚠️  No se encontró librería FCM. El backend debería dispararlo automáticamente.\n";
            }
        } catch (\Exception $e) {
            echo "⚠️  Error enviando FCM: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⚠️  Repartidor sin firebase_token. No se puede enviar FCM.\n";
    }

    // 9. Resumen
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🎉 PEDIDO CREADO EXITOSAMENTE\n";
    echo str_repeat("=", 50) . "\n";
    echo "   Pedido ID:     {$order->id}\n";
    echo "   Cliente ID:    {$userId}\n";
    echo "   Tienda ID:     {$storeId}\n";
    echo "   Repartidor ID: {$deliveryManId}\n";
    echo "   Estado:        {$order->order_status}\n";
    echo "   Dirección:     {$address->address}\n";
    echo "   Lat/Lng:       {$address->latitude}, {$address->longitude}\n";
    echo "   Total:         \$150.00\n";
    echo str_repeat("=", 50) . "\n";
}