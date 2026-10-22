<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\DeliveryMan;
use App\Models\Module;
use App\Models\Order;
use App\Models\ParcelCategory;
use App\Models\Store;
use App\Models\User;
use App\Models\Zone;
use App\CentralLogics\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExpressDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $deliveryMen = DeliveryMan::active()->orderBy('f_name')->get();
        $stores = Store::active()->select('id', 'name', 'phone', 'address', 'latitude', 'longitude', 'zone_id')->get();
        $zones = Zone::active()->get();
        
        $default_location = BusinessSetting::where('key', 'default_location')->first();
        $default_location = $default_location?->value ? json_decode($default_location->value, true) : null;
        $map_api_key = BusinessSetting::where('key', 'map_api_key')->first()?->value ?? '';

        $parcelCategory = ParcelCategory::where('status', 1)->first() ?? ParcelCategory::first();
        $perKmCharge = $parcelCategory?->parcel_per_km_shipping_charge 
            ?? (float)(BusinessSetting::where('key', 'parcel_per_km_shipping_charge')->first()?->value ?? 15);
        $minShippingCharge = $parcelCategory?->parcel_minimum_shipping_charge 
            ?? (float)(BusinessSetting::where('key', 'parcel_minimum_shipping_charge')->first()?->value ?? 35);

        return view('admin-views.express-delivery.index', compact(
            'deliveryMen',
            'stores',
            'zones',
            'default_location',
            'map_api_key',
            'perKmCharge',
            'minShippingCharge'
        ));
    }

    public function calculateFee(Request $request)
    {
        $request->validate([
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'delivery_lat' => 'required|numeric',
            'delivery_lng' => 'required|numeric',
        ]);

        $distance = Helpers::get_distance(
            (float) $request->pickup_lat,
            (float) $request->pickup_lng,
            (float) $request->delivery_lat,
            (float) $request->delivery_lng
        );
        $distance = round(max(0.1, $distance), 2);

        $zone = Helpers::getCoordinatesZone($request->pickup_lat, $request->pickup_lng)
            ?? Helpers::getCoordinatesZone($request->delivery_lat, $request->delivery_lng)
            ?? Zone::where('status', 1)->first();

        $parcelCategory = ParcelCategory::where('status', 1)->first();
        $perKm = $parcelCategory?->parcel_per_km_shipping_charge 
            ?? (float)(BusinessSetting::where('key', 'parcel_per_km_shipping_charge')->first()?->value ?? 15);
        $minCharge = $parcelCategory?->parcel_minimum_shipping_charge 
            ?? (float)(BusinessSetting::where('key', 'parcel_minimum_shipping_charge')->first()?->value ?? 35);

        $calculatedFee = max($minCharge, round($distance * $perKm, 2));

        return response()->json([
            'success' => true,
            'distance' => $distance,
            'delivery_fee' => $calculatedFee,
            'zone_name' => $zone?->name ?? 'General',
            'zone_id' => $zone?->id,
        ]);
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'pickup_name' => 'required|string|max:191',
            'pickup_phone' => 'required|string|max:30',
            'pickup_address' => 'required|string|max:500',
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',

            'receiver_name' => 'required|string|max:191',
            'receiver_phone' => 'required|string|max:30',
            'receiver_address' => 'required|string|max:500',
            'receiver_latitude' => 'required|numeric',
            'receiver_longitude' => 'required|numeric',

            'item_price' => 'nullable|numeric|min:0',
            'delivery_fee' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash_on_delivery,digital_payment,offline_payment',
            'charge_payer' => 'required|in:receiver,sender',
            'delivery_man_id' => 'nullable|integer',
            'item_description' => 'nullable|string|max:255',
            'order_note' => 'nullable|string|max:500',
            'delivery_instruction' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                Toastr::error($error);
            }
            return back()->withInput();
        }

        try {
            DB::beginTransaction();

            // Buscar o crear usuario receptor para asociar la orden
            $cleanPhone = preg_replace('/[^0-9]/', '', $request->receiver_phone);
            $customer = User::where('phone', $request->receiver_phone)
                ->orWhere('phone', $cleanPhone)
                ->first();

            if (!$customer) {
                $customer = new User();
                $nameParts = explode(' ', trim($request->receiver_name), 2);
                $customer->f_name = $nameParts[0] ?? 'Cliente';
                $customer->l_name = $nameParts[1] ?? 'WhatsApp';
                $customer->phone = $request->receiver_phone;
                $customer->email = ($cleanPhone ?: Str::random(8)) . '@tootli.com';
                $customer->password = bcrypt(Str::random(12));
                $customer->login_medium = 'manual';
                $customer->ref_code = Helpers::generate_referer_code($customer);
                $customer->save();
            }

            // Distancia
            $distance = Helpers::get_distance(
                (float) $request->pickup_latitude,
                (float) $request->pickup_longitude,
                (float) $request->receiver_latitude,
                (float) $request->receiver_longitude
            );
            $distance = round(max(0.1, $distance), 2);

            // Zona
            $zone = Helpers::getCoordinatesZone($request->pickup_latitude, $request->pickup_longitude)
                ?? Helpers::getCoordinatesZone($request->receiver_latitude, $request->receiver_longitude)
                ?? Zone::where('status', 1)->first();

            // Módulo
            $module = Module::where('module_type', 'parcel')->first() 
                ?? Module::where('module_type', 'food')->first() 
                ?? Module::first();

            // Categoría de paquetería
            $parcelCategory = ParcelCategory::where('status', 1)->first() ?? ParcelCategory::first();

            $itemPrice = (float) ($request->item_price ?? 0);
            $deliveryFee = (float) $request->delivery_fee;
            
            // Total de la orden según quién pague el flete
            $orderAmount = ($request->charge_payer === 'sender') ? $itemPrice : ($itemPrice + $deliveryFee);

            // Generar ID único
            $lastOrder = Order::latest()->first();
            $orderId = $lastOrder ? ($lastOrder->id + 1) : 100001;
            while (Order::find($orderId)) {
                $orderId++;
            }

            $pickupAddress = [
                'contact_person_name' => $request->pickup_name,
                'contact_person_number' => $request->pickup_phone,
                'address_type' => 'pickup',
                'address' => $request->pickup_address,
                'latitude' => (string) $request->pickup_latitude,
                'longitude' => (string) $request->pickup_longitude,
                'zone_id' => $zone?->id,
            ];

            $receiverDetails = [
                'contact_person_name' => $request->receiver_name,
                'contact_person_number' => $request->receiver_phone,
                'address_type' => 'delivery',
                'address' => $request->receiver_address,
                'latitude' => (string) $request->receiver_latitude,
                'longitude' => (string) $request->receiver_longitude,
                'zone_id' => $zone?->id,
            ];

            $order = new Order();
            $order->id = $orderId;
            $order->user_id = $customer->id;
            $order->is_guest = 0;
            $order->order_type = 'parcel';
            $order->order_status = 'pending';
            $order->payment_status = ($request->payment_method === 'cash_on_delivery') ? 'unpaid' : 'paid';
            $order->payment_method = $request->payment_method;
            $order->zone_id = $zone?->id;
            $order->module_id = $module?->id;
            $order->parcel_category_id = $parcelCategory?->id;
            $order->distance = $distance;
            $order->delivery_charge = $deliveryFee;
            $order->original_delivery_charge = $deliveryFee;
            $order->parcel_item_estimated_price = $itemPrice;
            $order->order_amount = $orderAmount;
            $order->charge_payer = $request->charge_payer ?? 'receiver';
            $order->delivery_address = json_encode($pickupAddress);
            $order->receiver_details = $receiverDetails;
            
            $noteParts = ['Envío Express WhatsApp'];
            if ($request->item_description) {
                $noteParts[] = 'Pedido: ' . $request->item_description;
            }
            if ($request->order_note) {
                $noteParts[] = $request->order_note;
            }
            $order->order_note = implode(' | ', $noteParts);
            $order->delivery_instruction = $request->delivery_instruction;
            
            $order->otp = rand(1000, 9999);
            $order->pending = now();
            $order->schedule_at = now();
            $order->checked = 1;
            $order->created_at = now();
            $order->updated_at = now();
            $order->save();

            // Asignación de repartidor si se especificó
            if ($request->delivery_man_id && $request->delivery_man_id > 0) {
                $deliveryman = DeliveryMan::find($request->delivery_man_id);
                if ($deliveryman) {
                    $order->delivery_man_id = $deliveryman->id;
                    $order->order_status = 'accepted';
                    $order->accepted = now();
                    $order->save();

                    $deliveryman->increment('current_orders');
                    $deliveryman->increment('assigned_order_count');

                    // Notificación push directa al repartidor seleccionado
                    if ($deliveryman->fcm_token) {
                        $pushData = [
                            'title' => 'Nuevo Pedido Express Asignado',
                            'description' => 'Recoger en: ' . $request->pickup_name . ' para ' . $request->receiver_name,
                            'order_id' => $order->id,
                            'image' => '',
                            'type' => 'order_status',
                            'order_type' => 'parcel_order',
                        ];
                        Helpers::send_push_notif_to_device($deliveryman->fcm_token, $pushData);

                        DB::table('user_notifications')->insert([
                            'data' => json_encode($pushData),
                            'delivery_man_id' => $deliveryman->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            } else {
                // Notificar a todos los repartidores activos de la zona
                Helpers::send_order_notification($order);
            }

            DB::commit();

            Toastr::success('¡Envío Express #' . $order->id . ' creado y mandado a repartidores exitosamente!');
            return redirect()->route('admin.order.details', [$order->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            info('Error al crear envío express: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            Toastr::error('Error al procesar el envío: ' . $e->getMessage());
            return back()->withInput();
        }
    }
}
