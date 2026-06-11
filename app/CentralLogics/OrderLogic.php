<?php

namespace App\CentralLogics;

use App\Models\User;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Store;
use App\Models\Vendor;
use App\Models\AdminWallet;
use App\Models\DeliveryMan;
use App\Models\StoreWallet;
use Illuminate\Support\Str;
use App\Models\OrderPayment;
use App\Models\BusinessSetting;
use App\Models\OrderTransaction;
use App\Models\DeliveryManWallet;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\CustomerLogic;
use App\Models\DeliverymanReferralHistory;
use App\Models\ParcelCancellation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Modules\Rental\Entities\PartialPayment;

class OrderLogic
{
    // Constantes de configuración de tarifas Tootli
    // Constantes de configuración de tarifas Tootli
    const TOOTLI_BASE_SHIPPING_FEE = 25.0;      // Envío base cliente
    const TOOTLI_BASE_DM_PAY = 20.0;            // Base neta repartidor
    const TOOTLI_TIER1_KM_LIMIT = 3.5;          // Límite zona 1 (3.5 km)
    const TOOTLI_TIER2_KM_LIMIT = 6.5;          // Límite zona 2 (6.5 km)
    const TOOTLI_TIER1_RATE = 4.0;              // Precio km extra zona 1 (hasta 3.5 km)
    const TOOTLI_TIER2_RATE = 6.0;              // Precio km extra zona 2 (3.5 - 6.5 km)
    const TOOTLI_TIER3_RATE = 8.5;              // Precio km extra zona 3 (6.5 - 8.0 km)
    const TOOTLI_LONG_DISTANCE_BONUS = 20.0;    // Bono de viaje largo (> 6.5 km)

    public static function get_progressive_fee_settings()
    {
        try {
            $setting = \App\Models\BusinessSetting::where('key', 'progressive_delivery_fees')->first();
            if (!$setting) {
                $defaults = [
                    'base_fee' => self::TOOTLI_BASE_SHIPPING_FEE,
                    'tier1_km_limit' => self::TOOTLI_TIER1_KM_LIMIT,
                    'tier2_km_limit' => self::TOOTLI_TIER2_KM_LIMIT,
                    'tier2_rate' => self::TOOTLI_TIER2_RATE,
                    'tier3_rate' => self::TOOTLI_TIER3_RATE,
                    'long_distance_bonus' => self::TOOTLI_LONG_DISTANCE_BONUS
                ];
                
                \App\Models\BusinessSetting::updateOrCreate(
                    ['key' => 'progressive_delivery_fees'],
                    ['value' => json_encode($defaults)]
                );
                
                return $defaults;
            }
            
            $decoded = json_decode($setting->value, true);
            if (is_array($decoded)) {
                return array_merge([
                    'base_fee' => self::TOOTLI_BASE_SHIPPING_FEE,
                    'tier1_km_limit' => self::TOOTLI_TIER1_KM_LIMIT,
                    'tier2_km_limit' => self::TOOTLI_TIER2_KM_LIMIT,
                    'tier2_rate' => self::TOOTLI_TIER2_RATE,
                    'tier3_rate' => self::TOOTLI_TIER3_RATE,
                    'long_distance_bonus' => self::TOOTLI_LONG_DISTANCE_BONUS
                ], $decoded);
            }
        } catch (\Throwable $e) {
            // Fallback safe in case database connection fails or table doesn't exist
        }
        
        return [
            'base_fee' => self::TOOTLI_BASE_SHIPPING_FEE,
            'tier1_km_limit' => self::TOOTLI_TIER1_KM_LIMIT,
            'tier2_km_limit' => self::TOOTLI_TIER2_KM_LIMIT,
            'tier2_rate' => self::TOOTLI_TIER2_RATE,
            'tier3_rate' => self::TOOTLI_TIER3_RATE,
            'long_distance_bonus' => self::TOOTLI_LONG_DISTANCE_BONUS
        ];
    }

    public static function calculate_progressive_distance_fee($distance, $per_km_shipping_charge = null, $minimum_shipping_charge = null, $maximum_shipping_charge = null)
    {
        $distance = max(0.0, (float)$distance);
        $settings = self::get_progressive_fee_settings();
        
        $base_fee = ($minimum_shipping_charge !== null && (float)$minimum_shipping_charge > 0) ? (float)$minimum_shipping_charge : (float) $settings['base_fee'];
        $tier1_km_limit = (float) $settings['tier1_km_limit'];
        $tier2_km_limit = (float) $settings['tier2_km_limit'];
        
        $tier2_rate = ($per_km_shipping_charge !== null && (float)$per_km_shipping_charge > 0) ? (float)$per_km_shipping_charge : (float) $settings['tier2_rate'];
        $tier3_rate = ($per_km_shipping_charge !== null && (float)$per_km_shipping_charge > 0) ? round((float)$per_km_shipping_charge * 1.4166, 2) : (float) $settings['tier3_rate'];
        
        $long_distance_bonus = (float) $settings['long_distance_bonus'];
        
        $fee = 0.0;
        
        if ($distance <= $tier1_km_limit) {
            $fee = $base_fee;
        } elseif ($distance <= $tier2_km_limit) {
            // Entre tier1_km_limit y tier2_km_limit: se cobra base + km extras en Tier 2
            $fee = $base_fee + (($distance - $tier1_km_limit) * $tier2_rate);
        } else {
            // Más de tier2_km_limit: se cobra base + km de Tier 2 + km extras en Tier 3 + Bono
            $tier2_distance = $tier2_km_limit - $tier1_km_limit;
            $fee = $base_fee 
                 + ($tier2_distance * $tier2_rate) 
                 + (($distance - $tier2_km_limit) * $tier3_rate) 
                 + $long_distance_bonus;
        }

        $fee = ceil((float) $fee);
        
        if ($maximum_shipping_charge !== null && (float)$maximum_shipping_charge > 0 && $fee > (float)$maximum_shipping_charge) {
            $fee = (float)$maximum_shipping_charge;
        }

        return $fee;
    }

    public static function gen_unique_id()
    {
        return rand(1000, 9999) . '-' . Str::random(5) . '-' . time();
    }

    /**
     * Evaluate whether a store qualifies for free shipping on a given cart subtotal.
     *
     * Returns an array with:
     *   - is_free               (bool)   Whether the delivery should be free for the customer
     *   - store_contribution    (float)  Amount the store absorbs
     *   - tootli_contribution   (float)  Amount Tootli absorbs (original_charge - store_contribution)
     *   - free_delivery_by      (string|null)  'vendor' | 'admin' | 'hybrid' | null
     */
    public static function calculate_free_shipping($store, float $cart_subtotal): array
    {
        if (! $store) {
            return [
                'is_free'             => false,
                'store_contribution'  => 0.0,
                'tootli_contribution' => 0.0,
                'free_delivery_by'    => null,
            ];
        }

        $module_zone = \DB::table('module_zone')
            ->where('zone_id', $store->zone_id)
            ->where('module_id', $store->module_id)
            ->first();

        if (! $module_zone || ! $module_zone->free_shipping_enabled) {
            return [
                'is_free'             => false,
                'store_contribution'  => 0.0,
                'tootli_contribution' => 0.0,
                'free_delivery_by'    => null,
            ];
        }

        $threshold = (float) ($module_zone->free_shipping_threshold ?? 0);

        if ($threshold > 0 && $cart_subtotal < $threshold) {
            return [
                'is_free'             => false,
                'store_contribution'  => 0.0,
                'tootli_contribution' => 0.0,
                'free_delivery_by'    => null,
            ];
        }

        $store_contribution = (float) ($module_zone->store_shipping_contribution ?? 0);

        $by = 'admin'; // Tootli absorbs 100%
        if ($store_contribution > 0) {
            $by = 'hybrid'; // Both absorb a portion
        }

        return [
            'is_free'             => true,
            'store_contribution'  => $store_contribution,
            'tootli_contribution' => 0.0, // resolved at transaction time from original_delivery_charge
            'free_delivery_by'    => $by,
        ];
    }

    public static function track_order($order_id)
    {
        return Helpers::order_data_formatting(Order::with(['details', 'delivery_man.rating'])->where(['id' => $order_id])->first(), false);
    }

    public static function updated_order_calculation($order)
    {
        return true;
    }
    public static function create_transaction($order, $received_by = false, $status = null)
    {
        $type = $order->order_type;
        $dm_tips_manage_status = BusinessSetting::where('key', 'dm_tips_status')->first()->value;
        $admin_subsidy = 0;
        $amount_admin = 0;
        $store_d_amount = 0;
        $admin_coupon_discount_subsidy = 0;
        $store_subsidy = 0;
        $store_coupon_discount_subsidy = 0;
        $store_discount_amount = 0;
        $flash_admin_discount_amount = 0;
        $flash_store_discount_amount = 0;
        $comission_on_store_amount = 0;
        $ref_bonus_amount = 0;
        $subscription_mode = 0;
        $commission_percentage = 0;
        $store_amount = 0;

        // Calcular bono por espera (repartidor) / multa (restaurante)
        // Premio: $1.00 por cada 10 min de espera a partir del minuto 10.
        // Al cumplir exactamente 10 min → $1.00, 20 min → $2.00, etc.
        $wait_time_bonus = 0;
        if ($type != 'parcel' && $order->handover && $order->picked_up) {
            try {
                $handoverTime = Carbon::parse($order->handover);
                $pickedUpTime = Carbon::parse($order->picked_up);
                $waitMinutes = $handoverTime->diffInMinutes($pickedUpTime);
                if ($waitMinutes >= 10) {
                    $wait_time_bonus = floor($waitMinutes / 10) * 1.00;
                }
            } catch (\Throwable $e) {
                \Log::error("Error calculating wait time bonus/penalty: " . $e->getMessage());
            }
        }


        $store = $order?->store;
        $store_sub = $order?->store?->store_sub;
        // free delivery by admin
        if ($order->free_delivery_by == 'admin') {
            $admin_subsidy = $order->original_delivery_charge;
            Helpers::expenseCreate(amount: $order->original_delivery_charge, type: 'free_delivery', datetime: now(), created_by: $order->free_delivery_by, order_id: $order->id);
        }
        // free delivery by store
        if ($order->free_delivery_by == 'vendor') {
            $store_subsidy = $order->original_delivery_charge;
            Helpers::expenseCreate(amount: $order->original_delivery_charge, type: 'free_delivery', datetime: now(), created_by: $order->free_delivery_by, order_id: $order->id, store_id: $order->store->id);
        }
        // free delivery hybrid (store + Tootli share the cost)
        if ($order->free_delivery_by == 'hybrid') {
            $store_contribution = (float) ($order->store_shipping_contribution ?? 0);
            $tootli_contribution = max(0.0, (float) $order->original_delivery_charge - $store_contribution);
            // Store absorbs its share
            if ($store_contribution > 0) {
                $store_subsidy = $store_contribution;
                Helpers::expenseCreate(amount: $store_contribution, type: 'free_delivery', datetime: now(), created_by: 'vendor', order_id: $order->id, store_id: $order->store->id);
            }
            // Tootli absorbs the remainder
            if ($tootli_contribution > 0) {
                $admin_subsidy = $tootli_contribution;
                Helpers::expenseCreate(amount: $tootli_contribution, type: 'free_delivery', datetime: now(), created_by: 'admin', order_id: $order->id);
            }
        }
        // coupon discount by Admin
        if ($order->coupon_created_by == 'admin') {
            $admin_coupon_discount_subsidy = $order->coupon_discount_amount;
            Helpers::expenseCreate(amount: $admin_coupon_discount_subsidy, type: 'coupon_discount', datetime: now(), created_by: $order->coupon_created_by, order_id: $order->id);
        }
        // 1st order discount by Admin
        if ($order->ref_bonus_amount > 0) {
            $ref_bonus_amount = $order->ref_bonus_amount;
            Helpers::expenseCreate(amount: $ref_bonus_amount, type: 'referral_discount', datetime: now(), created_by: 'admin', order_id: $order->id);
        }
        // coupon discount by store
        if ($order->coupon_created_by == 'vendor') {
            $store_coupon_discount_subsidy = $order->coupon_discount_amount;
            Helpers::expenseCreate(amount: $store_coupon_discount_subsidy, type: 'coupon_discount', datetime: now(), created_by: $order->coupon_created_by, order_id: $order->id, store_id: $order->store->id);
        }

        if ($order?->cashback_history) {
            self::cashbackToWallet($order);
        }

        if ($type == 'parcel') {
            $comission = \App\Models\BusinessSetting::where('key', 'parcel_commission_dm')->first();
            $dm_tips = $dm_tips_manage_status ? $order->dm_tips : 0;
            $comission = isset($comission) ? $comission->value : 0;
            $order_amount = $order->order_amount - $dm_tips - $order->additional_charge - $order->extra_packaging_amount - $order->total_tax_amount;
            $dm_commission = $comission ? ($order_amount / 100) * $comission : 0;
            $comission_amount = $order_amount - $dm_commission;
        } else {
            $is_tootli_direct = ($order->order_type === 'direct')
                || (bool) ($order->tootli_direct ?? false);
            $comission = isset($order->store->comission) == null ? \App\Models\BusinessSetting::where('key', 'admin_commission')->first()->value : $order->store->comission;
            if ($is_tootli_direct) {
                $direct_food = BusinessSetting::where('key', 'tootli_direct_food_commission')->first();
                $comission = $direct_food !== null ? (float) $direct_food->value : 0;
            }
            $dm_tips = $dm_tips_manage_status ? $order->dm_tips : 0;
            // $order_amount = $order->order_amount - $order->delivery_charge - $order->total_tax_amount - $dm_tips;

            if ($order->store_discount_amount > 0 && $order->discount_on_product_by == 'vendor') {
                if ($store->store_business_model == 'subscription' && isset($store_sub)) {
                    $store_d_amount = $order->store_discount_amount;
                    Helpers::expenseCreate(amount: $store_d_amount, type: 'discount_on_product', datetime: now(), created_by: 'vendor', order_id: $order->id, store_id: $order->store->id);
                } else {
                    $amount_admin = $comission ? ($order->store_discount_amount / 100) * $comission : 0;
                    $store_d_amount = $order->store_discount_amount - $amount_admin;
                    Helpers::expenseCreate(amount: $store_d_amount, type: 'discount_on_product', datetime: now(), created_by: 'vendor', order_id: $order->id, store_id: $order->store->id);
                    Helpers::expenseCreate(amount: $amount_admin, type: 'discount_on_product', datetime: now(), created_by: 'admin', order_id: $order->id);
                }
            }

            if ($order->store_discount_amount > 0 && $order->discount_on_product_by == 'admin') {
                $store_discount_amount = $order->store_discount_amount;
                Helpers::expenseCreate(amount: $store_discount_amount, type: 'discount_on_product', datetime: now(), created_by: 'admin', order_id: $order->id);
            }

            if ($order->flash_admin_discount_amount > 0) {
                $flash_admin_discount_amount = $order->flash_admin_discount_amount;
                Helpers::expenseCreate(amount: $flash_admin_discount_amount, type: 'flash_sale_discount', datetime: now(), created_by: 'admin', order_id: $order->id);
            }

            if ($order->flash_store_discount_amount > 0) {
                $flash_store_discount_amount = $order->flash_store_discount_amount;
                Helpers::expenseCreate(amount: $flash_store_discount_amount, type: 'flash_sale_discount', datetime: now(), created_by: 'vendor', order_id: $order->id, store_id: $order->store->id);
            }


            $order_amount = $order->order_amount - $order->additional_charge - $order->extra_packaging_amount - $order->delivery_charge - $order->total_tax_amount - $dm_tips + $flash_admin_discount_amount + $order->coupon_discount_amount + $store_discount_amount + $flash_store_discount_amount + $ref_bonus_amount;
            // comission in delivery charge (Tootli Directo usa porcentaje propio)
            if ($is_tootli_direct) {
                $direct_del = BusinessSetting::where('key', 'tootli_direct_delivery_commission')->first();
                $delivery_charge_comission_percentage = $direct_del !== null ? (float) $direct_del->value : 0;
            } else {
                $delivery_charge_comission = BusinessSetting::where('key', 'delivery_charge_comission')->first();
                $delivery_charge_comission_percentage = $delivery_charge_comission ? $delivery_charge_comission->value : 0;
            }
            $base_for_commission = min(self::TOOTLI_BASE_SHIPPING_FEE, (float)$order->original_delivery_charge);
            $comission_on_delivery = $delivery_charge_comission_percentage * ($base_for_commission / 100);

            if ($order->store->sub_self_delivery) {
                $comission_on_actual_delivery_fee = 0;
            } else {

                $comission_on_actual_delivery_fee = ($order->original_delivery_charge > 0) ? $comission_on_delivery : 0;
            }

            if ($order->free_delivery_by == 'admin') {
                if ($order->store->sub_self_delivery) {
                    $comission_on_actual_delivery_fee = 0;
                    $store_amount = $order->original_delivery_charge ?? 0;
                } else {
                    $comission_on_actual_delivery_fee = ($order->original_delivery_charge > 0) ? $comission_on_delivery : 0;
                }
            }

            //final comission (Directo: comisión comida según admin aunque la tienda sea suscripción)
            if ($store->store_business_model == 'subscription' && isset($store_sub) && ! $is_tootli_direct) {
                $comission_on_store_amount = 0;
                $subscription_mode = 1;
                $commission_percentage = 0;
            } else {
                $comission_on_store_amount = ($comission ? ($order_amount / 100) * $comission : 0);
                $subscription_mode = 0;
                $commission_percentage = $comission;
            }

            $comission_amount = $comission_on_store_amount + $comission_on_actual_delivery_fee;
            $dm_commission = $order->original_delivery_charge - $comission_on_actual_delivery_fee + ($order->incentive_amount ?? 0);
            if ($wait_time_bonus > 0) {
                $dm_commission += $wait_time_bonus;
            }
        }
        $store_amount = $store_amount + $order_amount + $order->total_tax_amount + $order->extra_packaging_amount - $comission_on_store_amount - $store_coupon_discount_subsidy - $flash_store_discount_amount;

        // Tootli Direct + POS: tarjeta en entrega (terminal Tootli). El restaurante absorbe el fee de tarjeta
        // (card_net ya lo refleja); el neto dispersado menos el envío cobrado al cliente = abono al restaurante.
        if ($type != 'parcel'
            && ($order->tootli_direct ?? false)
            && $order->payment_method === 'card_tootli_direct'
            && is_array($order->pos_payment_meta)
            && isset($order->pos_payment_meta['card_net_amount'])
        ) {
            $meta = $order->pos_payment_meta;
            $cardNet = (float) $meta['card_net_amount'];
            $gross = (float) ($meta['card_gross_amount'] ?? $order->order_amount);
            $del = (float) ($order->delivery_charge ?? 0);
            $store_amount = round(max(0.0, $cardNet - $del), 2);
            $cardProcessingMargin = round(max(0.0, $gross - $cardNet), 2);
            $delFeeComission = isset($comission_on_actual_delivery_fee) ? (float) $comission_on_actual_delivery_fee : 0.0;
            // Comisión plataforma: recorte sobre envío + diferencial bruto/neto (pasarela / fee tarjeta). No se suma comisión % comida aparte.
            $comission_amount = $delFeeComission + $cardProcessingMargin;
            $comission_on_store_amount = 0.0;
            $subscription_mode = 0;
            $commission_percentage = 0;
        }

        // Tootli Direct + efectivo contra entrega a domicilio: el repartidor cobra el total; el restaurante recibe
        // comida + impuestos (total del pedido menos el envío). Comisión Tootli solo sobre el envío (p. ej. 20%).
        if ($type != 'parcel'
            && ($order->tootli_direct ?? false)
            && $order->order_type === 'delivery'
            && $order->payment_method === 'cash_on_delivery'
        ) {
            $del = (float) ($order->delivery_charge ?? 0);
            $store_amount = round(max(0.0, (float) $order->order_amount - $del), 2);
            $delFeeComission = isset($comission_on_actual_delivery_fee) ? (float) $comission_on_actual_delivery_fee : 0.0;
            $comission_amount = $delFeeComission;
            $comission_on_store_amount = 0.0;
            $subscription_mode = 0;
            $commission_percentage = 0;
        }

        // Tootli Direct + pagado en restaurante (domicilio): la comida ya la cobró el local fuera del flujo Tootli.
        // No abonar total_earning por comida. Solo comisión Tootli sobre envío; el envío se descuenta de la billetera
        // del restaurante en StoreWallet para que Tootli liquide al repartidor (dm_commission = envío − comisión).
        if ($type != 'parcel'
            && ($order->tootli_direct ?? false)
            && $order->order_type === 'delivery'
            && $order->payment_method === 'paid_at_restaurant'
        ) {
            $store_amount = 0.0;
            $delFeeComission = isset($comission_on_actual_delivery_fee) ? (float) $comission_on_actual_delivery_fee : 0.0;
            $comission_amount = $delFeeComission;
            $comission_on_store_amount = 0.0;
            $subscription_mode = 0;
            $commission_percentage = 0;
        }

        // Aplicar penalización al restaurante por espera tardía
        if ($type != 'parcel' && $wait_time_bonus > 0) {
            $store_amount = max(0.0, $store_amount - $wait_time_bonus);
        }

        try {
            OrderTransaction::insert([
                'vendor_id' => $type == 'parcel' ? null : $order->store->vendor->id,
                'delivery_man_id' => $order->delivery_man_id,
                'order_id' => $order->id,
                'order_amount' => $order->order_amount,
                'store_amount' => $type == 'parcel' ? 0 : $store_amount,
                // 'store_amount'=>$type=='parcel' ? 0 : $order_amount + $order->total_tax_amount - $comission_on_store_amount,
                'admin_commission' => $comission_amount + $order->additional_charge - $admin_subsidy - $admin_coupon_discount_subsidy - $ref_bonus_amount - $store_discount_amount - ($order->incentive_amount ?? 0),
                'delivery_charge' => $order->delivery_charge,
                'original_delivery_charge' => $dm_commission,
                'tax' => $order->total_tax_amount,
                'received_by' => $received_by ? $received_by : 'admin',
                'zone_id' => $order->zone_id,
                'module_id' => $order->module_id,
                'admin_expense' => $admin_subsidy + $admin_coupon_discount_subsidy + $store_discount_amount + $flash_admin_discount_amount + $amount_admin + $ref_bonus_amount,
                'store_expense' => $store_subsidy + $store_coupon_discount_subsidy + $flash_store_discount_amount,
                'status' => $status,
                'dm_tips' => $dm_tips,
                'created_at' => now(),
                'updated_at' => now(),
                'delivery_fee_comission' => isset($comission_on_actual_delivery_fee) ? $comission_on_actual_delivery_fee : 0,
                'discount_amount_by_store' => $store_coupon_discount_subsidy + $store_d_amount + $store_subsidy,
                'additional_charge' => $order->additional_charge,
                'extra_packaging_amount' => $order->extra_packaging_amount,
                'ref_bonus_amount' => $order->ref_bonus_amount,
                // for store business model
                'is_subscribed' => $subscription_mode,
                'commission_percentage' => $commission_percentage,
            ]);
            $adminWallet = AdminWallet::firstOrNew(
                ['admin_id' => Admin::where('role_id', 1)->first()->id]
            );

            $adminWallet->total_commission_earning = $adminWallet->total_commission_earning + $comission_amount + $order->additional_charge - $admin_subsidy - $admin_coupon_discount_subsidy - $store_discount_amount - $flash_admin_discount_amount - $ref_bonus_amount - ($order->incentive_amount ?? 0);

            if ($type != 'parcel') {
                $vendorWallet = StoreWallet::firstOrNew(
                    ['vendor_id' => $order->store->vendor->id]
                );
                if ($order->store->sub_self_delivery) {
                    if (! (($order->tootli_direct ?? false) && $order->payment_method === 'paid_at_restaurant' && $order->order_type === 'delivery')) {
                        $vendorWallet->total_earning = $vendorWallet->total_earning + $order->delivery_charge + $dm_tips;
                    }
                } else {
                    $adminWallet->delivery_charge = $adminWallet->delivery_charge + $order->delivery_charge;
                }
                // $vendorWallet->total_earning = $vendorWallet->total_earning+($order_amount + $order->total_tax_amount - $comission_on_store_amount);
                $vendorWallet->total_earning = $vendorWallet->total_earning + $store_amount;

                // Tootli Direct: el restaurante absorbe el costo de entrega no cubierto por el cliente
                $tootliNetCost = 0.0;
                if ($order->tootli_direct && !$order->store->sub_self_delivery) {
                    $tootliNetCost = max(0.0,
                        (float)($order->original_delivery_charge ?? 0)
                        - (float)($order->delivery_charge ?? 0)
                    );
                    if ($tootliNetCost > 0) {
                        $vendorWallet->total_withdrawn = ($vendorWallet->total_withdrawn ?? 0) + $tootliNetCost;
                    }
                }

                // Pagado en restaurante: el envío cobrado en tienda se descuenta de la billetera Tootli del local para liquidar al repartidor (no es “ganancia” en sistema).
                if (($order->tootli_direct ?? false)
                    && $order->payment_method === 'paid_at_restaurant'
                    && $order->order_type === 'delivery'
                    && ! $order->store->sub_self_delivery
                ) {
                    $passDel = (float) ($order->delivery_charge ?? 0);
                    if ($passDel > 0) {
                        $vendorWallet->total_withdrawn = ($vendorWallet->total_withdrawn ?? 0) + $passDel;
                    }
                }
            }
            if ($order->delivery_man && ($type == 'parcel' || ($order->store && !$order->store->sub_self_delivery))) {
                $dmWallet = DeliveryManWallet::firstOrNew(
                    ['delivery_man_id' => $order->delivery_man_id]
                );
                if ($order->delivery_man->earning == 1) {
                    $dmWallet->total_earning = $dmWallet->total_earning + $dm_commission + $dm_tips;
                } else {
                    $adminWallet->total_commission_earning = $adminWallet->total_commission_earning + $dm_commission + $dm_tips;
                }
            } else {
                $adminWallet->total_commission_earning = $adminWallet->total_commission_earning + $dm_commission + $dm_tips;
            }

            try {
                DB::beginTransaction();
                $unpaid_payment = OrderPayment::where('payment_status', 'unpaid')->where('order_id', $order->id)->first()?->payment_method;
                $unpaid_pay_method = 'digital_payment';
                if ($unpaid_payment) {
                    $unpaid_pay_method = $unpaid_payment;
                }
                $is_dm_collect = in_array($order->payment_method, ['cash_on_delivery', 'card_on_delivery'], true)
                    || in_array($unpaid_pay_method, ['cash_on_delivery', 'card_on_delivery'], true);
                // Tootli Direct: comida cobrada en tienda; no registrar cobro digital/efectivo del cliente vía app.
                $skip_customer_collection_ledgers = $type != 'parcel'
                    && $order->payment_method === 'paid_at_restaurant'
                    && (bool) ($order->tootli_direct ?? false);

                if (! $skip_customer_collection_ledgers) {
                    if ($received_by == 'admin') {
                        $digitalCollected = $order->order_amount - $order->partially_paid_amount;
                        if ($order->payment_method === 'card_tootli_direct'
                            && is_array($order->pos_payment_meta)
                            && isset($order->pos_payment_meta['card_net_amount'])) {
                            $digitalCollected = (float) $order->pos_payment_meta['card_net_amount'] - (float) ($order->partially_paid_amount ?? 0);
                        }
                        $adminWallet->digital_received = $adminWallet->digital_received + $digitalCollected;
                    } else if ($received_by == 'store' && $type != 'parcel' && $is_dm_collect) {
                        $store_over_flow = true;
                        $vendorWallet->collected_cash = $vendorWallet->collected_cash + ($order->order_amount - $order->partially_paid_amount);
                    } else if ($received_by == false) {
                        $adminWallet->manual_received = $adminWallet->manual_received + ($order->order_amount - $order->partially_paid_amount);
                    } else if ($received_by == 'deliveryman' && $order->delivery_man && $order->delivery_man->type == 'zone_wise') {
                        $dmWallet->collected_cash = $dmWallet->collected_cash + ($order->order_amount - $order->partially_paid_amount);
                        $dm_over_flow = true;
                    }

                    if (isset($store_over_flow)) {
                        self::create_account_transaction_for_collect_cash(old_collected_cash: $vendorWallet->collected_cash, from_type: 'store', from_id: $order->store->vendor->id, amount: $order->order_amount - $order->partially_paid_amount, order_id: $order->id);
                    }
                    if (isset($dm_over_flow)) {
                        self::create_account_transaction_for_collect_cash(old_collected_cash: $dmWallet->collected_cash, from_type: 'deliveryman', from_id: $order->delivery_man_id, amount: $order->order_amount - $order->partially_paid_amount, order_id: $order->id);
                    }
                }

                $adminWallet->save();
                if ($type != 'parcel') {
                    $vendorWallet->save();

                    // Registrar cobro Tootli Direct absorbido por el restaurante
                    if (isset($tootliNetCost) && $tootliNetCost > 0) {
                        $tx = new AccountTransaction();
                        $tx->from_type       = 'store';
                        $tx->from_id         = $order->store->vendor->id;
                        $tx->created_by      = 'admin';
                        $tx->method          = 'tootli_direct_delivery';
                        $tx->ref             = $order->id;
                        $tx->amount          = $tootliNetCost;
                        $tx->current_balance = $vendorWallet->balance;
                        $tx->type            = 'tootli_direct_fee';
                        $tx->save();
                    }
                    // Envío pagado en tienda: descuento en billetera para fondo repartidor (Tootli Direct)
                    if (($order->tootli_direct ?? false)
                        && $order->payment_method === 'paid_at_restaurant'
                        && $order->order_type === 'delivery'
                        && ! $order->store->sub_self_delivery
                        && (float) ($order->delivery_charge ?? 0) > 0
                    ) {
                        $tx = new AccountTransaction();
                        $tx->from_type       = 'store';
                        $tx->from_id         = $order->store->vendor->id;
                        $tx->created_by      = 'admin';
                        $tx->method          = 'tootli_direct_paid_at_restaurant_delivery_pass';
                        $tx->ref             = $order->id;
                        $tx->amount          = (float) $order->delivery_charge;
                        $tx->current_balance = $vendorWallet->balance;
                        $tx->type            = 'tootli_direct_fee';
                        $tx->save();
                    }

                    // Registrar multa al restaurante por espera tardía en restaurante
                    if (isset($wait_time_bonus) && $wait_time_bonus > 0) {
                        $tx = new AccountTransaction();
                        $tx->from_type       = 'store';
                        $tx->from_id         = $order->store->vendor->id;
                        $tx->created_by      = 'admin';
                        $tx->method          = 'restaurant_wait_penalty';
                        $tx->ref             = $order->id;
                        $tx->amount          = $wait_time_bonus;
                        $tx->current_balance = $vendorWallet->balance;
                        $tx->type            = 'penalty';
                        $tx->save();
                    }
                }
                if (isset($dmWallet)) {
                    self::auto_wallet_adjustment($dmWallet);
                    $dmWallet->save();

                    // Registrar bono al repartidor por espera tardía en restaurante
                    if (isset($wait_time_bonus) && $wait_time_bonus > 0) {
                        $tx = new AccountTransaction();
                        $tx->from_type       = 'deliveryman';
                        $tx->from_id         = $order->delivery_man_id;
                        $tx->created_by      = 'admin';
                        $tx->method          = 'deliveryman_wait_bonus';
                        $tx->ref             = $order->id;
                        $tx->amount          = $wait_time_bonus;
                        $tx->current_balance = $dmWallet->balance;
                        $tx->type            = 'earning';
                        $tx->save();
                    }
                }

                self::update_unpaid_order_payment(order_id: $order->id, payment_method: $order->payment_method);

                DB::commit();

                if ($order->delivery_man_id && $order->delivery_man->earning == 1) {
                    $deliveryMan = $order->delivery_man;
                    if ($deliveryMan->ref_by && $deliveryMan->orders()->whereIn('order_status', ['delivered'])->count() == 0) {
                        self::deliverymanReferalTransaction(deliveryManId: $order->delivery_man_id, referType: 'referrerBonus', reference: $order->id, referrerId: $deliveryMan->ref_by);
                        self::deliverymanReferalTransaction(deliveryManId: $deliveryMan->ref_by, referType: 'referral', reference: $order->id, referrerId: $order->delivery_man_id);
                    }
                }
                if ($order->is_guest == 0) {
                    $ref_status = BusinessSetting::where('key', 'ref_earning_status')->first()->value;
                    if (isset($order->customer->ref_by) && $order->customer->order_count == 0 && $ref_status == 1) {
                        $ref_code_exchange_amt = BusinessSetting::where('key', 'ref_earning_exchange_rate')->first()->value;
                        $referar_user = User::where('id', $order->customer->ref_by)->first();
                        $refer_wallet_transaction = CustomerLogic::create_wallet_transaction($referar_user->id, $ref_code_exchange_amt, 'referrer', $order->customer->phone);

                        $notification_data = [
                            'title' => translate('messages.Congratulation'),
                            'description' => translate('You have received') . ' ' . Helpers::format_currency($ref_code_exchange_amt) . ' ' . translate('in your wallet as') . ' ' . $order?->customer?->f_name . ' ' . $order?->customer?->l_name . ' ' . translate('you referred completed thier first order'),
                            'order_id' => 1,
                            'image' => '',
                            'type' => 'referral_code',
                        ];

                        if (Helpers::getNotificationStatusData('customer', 'customer_referral_bonus_earning', 'push_notification_status') && $referar_user?->cm_firebase_token) {
                            Helpers::send_push_notif_to_device($referar_user?->cm_firebase_token, $notification_data);
                            DB::table('user_notifications')->insert([
                                'data' => json_encode($notification_data),
                                'user_id' => $referar_user?->id,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }


                        try {
                            Helpers::add_fund_push_notification($referar_user->id);
                            if (config('mail.status') && Helpers::get_mail_status('add_fund_mail_status_user') == '1' && Helpers::getNotificationStatusData('customer', 'customer_add_fund_to_wallet', 'mail_status')) {
                                Mail::to($referar_user->email)->send(new \App\Mail\AddFundToWallet($refer_wallet_transaction));
                            }
                        } catch (\Exception $ex) {
                            info($ex->getMessage());
                        }
                    }

                    $create_loyalty_point_transaction = CustomerLogic::create_loyalty_point_transaction($order->user_id, $order->id, $order->order_amount, 'order_place');
                    if ($create_loyalty_point_transaction > 0) {
                        $notification_data = [
                            'title' => translate('messages.Congratulation'),
                            'description' => translate('You_have_received') . ' ' . $create_loyalty_point_transaction . ' ' . translate('points_as_loyalty_point'),
                            'order_id' => $order->id,
                            'image' => '',
                            'type' => 'loyalty_point',
                        ];

                        if (Helpers::getNotificationStatusData('customer', 'customer_loyalty_point_earning', 'push_notification_status') && $order->customer?->cm_firebase_token) {
                            Helpers::send_push_notif_to_device($order->customer?->cm_firebase_token, $notification_data);
                            DB::table('user_notifications')->insert([
                                'data' => json_encode($notification_data),
                                'user_id' => $order->user_id,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                info($e->getMessage());
                return false;
            }
        } catch (\Exception $e) {
            info($e->getMessage());
            return false;
        }

        return true;
    }

    public static function create_transaction_parcel_cancel($order, $received_by = false)
    {
        $dm_tips_manage_status = BusinessSetting::where('key', 'dm_tips_status')->first()->value;
        $admin_subsidy = 0;
        $admin_coupon_discount_subsidy = 0;
        $store_discount_amount = 0;
        $flash_admin_discount_amount = 0;
        $ref_bonus_amount = 0;

        $return_fee = $order?->parcelCancellation?->return_fee ?? 0;
        // free delivery by admin
        if ($order->free_delivery_by == 'admin') {
            $admin_subsidy = $order->original_delivery_charge;
            Helpers::expenseCreate(amount: $order->original_delivery_charge, type: 'free_delivery', datetime: now(), created_by: $order->free_delivery_by, order_id: $order->id);
        }

        // coupon discount by Admin
        if ($order->coupon_created_by == 'admin') {
            $admin_coupon_discount_subsidy = $order->coupon_discount_amount;
            Helpers::expenseCreate(amount: $admin_coupon_discount_subsidy, type: 'coupon_discount', datetime: now(), created_by: $order->coupon_created_by, order_id: $order->id);
        }

        $comission = \App\Models\BusinessSetting::where('key', 'parcel_commission_dm')->first();
        $dm_tips = $dm_tips_manage_status ? $order->dm_tips : 0;
        $comission = isset($comission) ? $comission->value : 0;
        $order_amount = $order->order_amount - $dm_tips - $order->additional_charge - $order->total_tax_amount;

        $dm_commission = $comission ? ($order_amount / 100) * $comission : 0;
        $comission_amount = $order_amount - $dm_commission;


        DB::beginTransaction();

        $order->order_status = 'returned';
        $order->payment_status = 'paid';
        $order->save();

        $order->parcelCancellation->return_fee_payment_status = 'paid';
        $order->parcelCancellation->save();


        try {

            $adminWallet = AdminWallet::firstOrNew(
                ['admin_id' => Admin::where('role_id', 1)->first()->id]
            );

            $adminWallet->total_commission_earning = $adminWallet->total_commission_earning + $comission_amount + $order->additional_charge - $admin_subsidy - $admin_coupon_discount_subsidy - $store_discount_amount - $flash_admin_discount_amount - $ref_bonus_amount;


            if ($order->delivery_man) {
                $dmWallet = DeliveryManWallet::firstOrNew(
                    ['delivery_man_id' => $order->delivery_man_id]
                );
                if ($order->delivery_man->earning == 1) {
                    $dmWallet->total_earning = $dmWallet->total_earning + $dm_commission + $dm_tips + $return_fee;
                } else {
                    $adminWallet->total_commission_earning = $adminWallet->total_commission_earning + $dm_commission + $dm_tips + $return_fee;
                }
            } else {
                $adminWallet->total_commission_earning = $adminWallet->total_commission_earning + $dm_commission + $dm_tips + $return_fee;
            }

            if ($received_by == 'admin') {
                $adminWallet->digital_received = $adminWallet->digital_received + ($order->order_amount - $order->partially_paid_amount);
            } else if ($received_by == false) {
                $adminWallet->manual_received = $adminWallet->manual_received + ($order->order_amount - $order->partially_paid_amount);
            } else if ($received_by == 'deliveryman' && $order->delivery_man && $order->delivery_man->type == 'zone_wise') {
                $dmWallet->collected_cash = $dmWallet->collected_cash + ($order->order_amount - $order->partially_paid_amount);
                $dm_over_flow = true;
            }

            $adminWallet->save();

            OrderTransaction::insert([
                'vendor_id' => null,
                'delivery_man_id' => $order->delivery_man_id,
                'order_id' => $order->id,
                'order_amount' => $order->order_amount,
                'store_amount' => 0,
                'admin_commission' => $comission_amount + $order->additional_charge - $admin_subsidy - $admin_coupon_discount_subsidy - $ref_bonus_amount - $store_discount_amount,
                'delivery_charge' => $order->delivery_charge,
                'original_delivery_charge' => $dm_commission,
                'tax' => $order->total_tax_amount,
                'received_by' => $received_by ? $received_by : 'admin',
                'zone_id' => $order->zone_id,
                'module_id' => $order->module_id,
                'admin_expense' => $admin_subsidy + $admin_coupon_discount_subsidy + $store_discount_amount + $flash_admin_discount_amount + $ref_bonus_amount,
                'store_expense' => 0,
                'status' => null,
                'dm_tips' => $dm_tips,
                'created_at' => now(),
                'updated_at' => now(),
                'delivery_fee_comission' => 0,
                'discount_amount_by_store' => 0,
                'additional_charge' => $order->additional_charge,
                'extra_packaging_amount' => $order->extra_packaging_amount ?? 0,
                'ref_bonus_amount' => $order->ref_bonus_amount ?? 0,
                // for store business model
                'is_subscribed' => 0,
                'commission_percentage' => $comission,
            ]);



            if ($order->parcelCancellation->return_date) {
                $returnDate = Carbon::parse($order->parcelCancellation->return_date);
                if ($returnDate->isPast() && isset($dmWallet)) {
                    $dmWallet->collected_cash = $dmWallet->collected_cash + $order->parcelCancellation->dm_penalty_fee ?? 0;
                }
            }

            if (isset($dmWallet)) {
                self::auto_wallet_adjustment($dmWallet);
                $dmWallet->save();
            }



            if (isset($dm_over_flow)) {
                self::create_account_transaction_for_collect_cash(old_collected_cash: $dmWallet->collected_cash, from_type: 'deliveryman', from_id: $order->delivery_man_id, amount: $order->order_amount - $order->partially_paid_amount, order_id: $order->id);
            }

            self::update_unpaid_order_payment(order_id: $order->id, payment_method: $order->payment_method);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            info($e->getMessage());
            return false;
        }

        return true;
    }

    public static function refund_before_delivered($order)
    {
        $adminWallet = AdminWallet::firstOrNew(
            ['admin_id' => Admin::where('role_id', 1)->first()->id]
        );
        if (in_array($order->payment_method, ['cash_on_delivery', 'card_on_delivery'], true)) {
            return false;
        }
        if (($order->payment_status == "paid")) {

            $adminWallet->digital_received = $adminWallet->digital_received - $order->order_amount;
            $adminWallet->save();
            if (BusinessSetting::where('key', 'wallet_add_refund')->first()->value == 1 && $order->is_guest == 0) {
                CustomerLogic::create_wallet_transaction($order->user_id, $order->order_amount, 'order_refund', $order->id);
            }
        } elseif (($order->payment_status == "partially_paid")) {

            $adminWallet->digital_received = $adminWallet->digital_received - $order->partially_paid_amount;
            $adminWallet->save();
            if (BusinessSetting::where('key', 'wallet_add_refund')->first()->value == 1 && $order->is_guest == 0) {
                CustomerLogic::create_wallet_transaction($order->user_id, $order->partially_paid_amount, 'order_refund', $order->id);
            }
        }
        return true;
    }

    public static function refund_order($order)
    {
        $order_transaction = $order->transaction;
        if ($order_transaction == null || $order->store == null) {
            return false;
        }
        $received_by = $order_transaction->received_by;

        $adminWallet = AdminWallet::firstOrNew(
            ['admin_id' => Admin::where('role_id', 1)->first()->id]
        );

        $vendorWallet = StoreWallet::firstOrNew(
            ['vendor_id' => $order->store->vendor->id]
        );

        $adminWallet->total_commission_earning = $adminWallet->total_commission_earning - $order_transaction->admin_commission + $order_transaction->delivery_fee_comission;

        $vendorWallet->total_earning = $vendorWallet->total_earning - $order_transaction->store_amount;

        $refund_amount = $order->order_amount - $order->additional_charge - $order->extra_packaging_amount;

        $status = 'refunded_with_delivery_charge';
        if ($order->order_status == 'delivered' || $order->order_status == 'refund_requested') {
            $refund_amount = $order->order_amount - $order->additional_charge - $order->extra_packaging_amount - $order->delivery_charge - $order->dm_tips;
            $status = 'refunded_without_delivery_charge';
        } else {
            $adminWallet->delivery_charge = $adminWallet->delivery_charge - $order_transaction->delivery_charge;
        }
        try {
            DB::beginTransaction();
            $partially_paid = OrderPayment::whereIn('payment_method', ['cash_on_delivery', 'card_on_delivery'])->where('order_id', $order->id)->exists() ?? false;

            if ($partially_paid) {
                $refund_amount = $refund_amount - $order->partially_paid_amount;
            }
            if ($received_by == 'admin') {
                if ($order->delivery_man_id && ! in_array($order->payment_method, ['cash_on_delivery', 'card_on_delivery'], true)) {
                    $adminWallet->digital_received = $adminWallet->digital_received - $refund_amount;
                } else {
                    $adminWallet->manual_received = $adminWallet->manual_received - $refund_amount;
                }
            } else if ($received_by == 'store') {
                $vendorWallet->collected_cash = $vendorWallet->collected_cash - $refund_amount;
            }

            $order_transaction->status = $status;
            $order_transaction->save();
            $adminWallet->save();
            $vendorWallet->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            info($e->getMessage());
            return false;
        }
        return true;
    }

    public static function create_order_payment($order_id, $amount, $payment_status, $payment_method)
    {
        $payment = new OrderPayment();
        $payment->order_id = $order_id;
        $payment->amount = $amount;
        $payment->payment_status = $payment_status;
        $payment->payment_method = $payment_method;
        if ($payment->save()) {
            return true;
        }

        return false;
    }

    public static function update_unpaid_order_payment($order_id, $payment_method)
    {
        $payment = OrderPayment::where('payment_status', 'unpaid')->where('order_id', $order_id)->first();
        if ($payment) {
            $payment->payment_status = 'paid';
            if ($payment_method != 'partial_payment') {
                $payment->payment_method = $payment_method;
            }
            if ($payment->save()) {
                return true;
            }

            return false;
        }
        return true;
    }

    public static function update_unpaid_trip_payment($trip_id, $payment_method)
    {
        $payment = PartialPayment::where('payment_status', 'unpaid')->where('trip_id', $trip_id)->first();
        if ($payment) {
            $payment->payment_status = 'paid';
            if ($payment_method != 'partial_payment') {
                $payment->payment_method = $payment_method;
            }
            $payment->save();
        }
        return true;
    }

    public static function auto_wallet_adjustment(\App\Models\DeliveryManWallet $wallet)
    {
        $wallet_earning = round($wallet->total_earning - ($wallet->total_withdrawn + $wallet->pending_withdraw), 8);
        $adj_amount = $wallet->collected_cash - $wallet_earning;

        if ($wallet->collected_cash <= 0 || $wallet_earning <= 0) {
            return;
        }

        if ($adj_amount > 0) {
            $wallet->total_withdrawn = $wallet->total_withdrawn + $wallet_earning;
            $wallet->collected_cash = $wallet->collected_cash - $wallet_earning;

            $data = [
                'delivery_man_id' => $wallet->delivery_man_id,
                'amount' => $wallet_earning,
                'ref' => 'delivery_man_wallet_adjustment_partial',
                'method' => 'adjustment',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        } else {
            $data = [
                'delivery_man_id' => $wallet->delivery_man_id,
                'amount' => $wallet->collected_cash,
                'ref' => 'delivery_man_wallet_adjustment_full',
                'method' => 'adjustment',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $wallet->total_withdrawn = $wallet->total_withdrawn + $wallet->collected_cash;
            $wallet->collected_cash = 0;
        }

        \Illuminate\Support\Facades\DB::table('provide_d_m_earnings')->insert($data);
    }

    public static function create_account_transaction_for_collect_cash($old_collected_cash, $from_type, $from_id, $amount, $order_id)
    {
        $account_transaction = new AccountTransaction();
        $account_transaction->from_type = $from_type;
        $account_transaction->from_id = $from_id;
        $account_transaction->created_by = $from_type;
        $account_transaction->method = 'cash_collection';
        $account_transaction->ref = $order_id;
        $account_transaction->amount = $amount ?? 0;
        $account_transaction->current_balance = $old_collected_cash ?? 0;
        $account_transaction->type = 'cash_in';
        $account_transaction->save();


        if ($from_type == 'store') {
            $vendor = Vendor::find($from_id);
            $Payable_Balance = $vendor?->wallet?->collected_cash > 0 ? 1 : 0;
            $cash_in_hand_overflow = BusinessSetting::where('key', 'cash_in_hand_overflow_store')->first()?->value;
            $cash_in_hand_overflow_store_amount = BusinessSetting::where('key', 'cash_in_hand_overflow_store_amount')->first()?->value;

            if ($Payable_Balance == 1 && $cash_in_hand_overflow && $vendor?->wallet?->balance < 0 && $cash_in_hand_overflow_store_amount <= abs($vendor?->wallet?->collected_cash)) {
                $rest = Store::where('vendor_id', $vendor->id)->first();
                $rest->status = 0;
                $rest->save();
            }
        } elseif ($from_type == 'deliveryman') {
            $cash_in_hand_overflow = BusinessSetting::where('key', 'cash_in_hand_overflow_delivery_man')->first()?->value;
            $cash_in_hand_overflow_delivery_man = BusinessSetting::where('key', 'dm_max_cash_in_hand')->first()?->value;
            // $val=  $cash_in_hand_overflow_delivery_man - (($cash_in_hand_overflow_delivery_man * 10)/100);

            $dm = DeliveryMan::find($from_id);
            $wallet_balance = $dm?->wallet?->total_earning - ($dm?->wallet?->total_withdrawn + $dm?->wallet?->pending_withdraw + $dm?->wallet?->collected_cash);
            $over_flow_balance = $dm?->wallet?->collected_cash;
            $Payable_Balance = $over_flow_balance > 0 ? 1 : 0;
            if ($Payable_Balance == 1 && $cash_in_hand_overflow && $wallet_balance < 0 && $cash_in_hand_overflow_delivery_man < abs($over_flow_balance)) {
                $dm->status = 0;
                // $dm->auth_token = null;
                $dm->save();
            }
        }
        return true;
    }


    public static function cashbackToWallet($order)
    {

        $refer_wallet_transaction = CustomerLogic::create_wallet_transaction($order?->cashback_history?->user_id, $order?->cashback_history?->calculated_amount, 'CashBack', $order->id);
        if ($refer_wallet_transaction != false) {
            Helpers::expenseCreate(amount: $order?->cashback_history?->calculated_amount, type: 'CashBack', datetime: now(), created_by: 'admin', order_id: $order->id);
            $order?->cashback_history?->cashBack?->increment('total_used');

            $notification_data = [
                'title' => translate('messages.Congratulation_you_have_received') . ' ' . $order?->cashback_history?->calculated_amount . ' ' . translate('cashback'),
                'description' => translate('The_cashback_amount_successfully_added_to_your_wallet'),
                'order_id' => $order->id,
                'image' => '',
                'type' => 'cashback',
            ];

            if ($order->customer?->cm_firebase_token && Helpers::getNotificationStatusData('customer', 'customer_cashback', 'push_notification_status')) {
                Helpers::send_push_notif_to_device($order->customer?->cm_firebase_token, $notification_data);
                DB::table('user_notifications')->insert([
                    'data' => json_encode($notification_data),
                    'user_id' => $order->customer?->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        return true;
    }

    public static function makeValidationForParcelReturn($request, $order)
    {
        $validationError = match (true) {
            !$order => [
                'code' => 'order',
                'message' => translate('messages.order_not_found'),
                'status_code' => 403,
            ],
            $order->order_type != 'parcel' => [
                'code' => 'parcel',
                'message' => translate('messages.Only_parcel_order_can_be_returned'),
                'status_code' => 403,
            ],
            $order->order_status != 'canceled' => [
                'code' => 'parcel',
                'message' => translate('messages.You_can_return_only_canceled_parcel_orders'),
                'status_code' => 403,
            ],
            !$order->parcelCancellation => [
                'code' => 'order',
                'message' => translate('messages.You_have_not_requested_for_parcel_return'),
                'status_code' => 403,
            ],
            $order->parcelCancellation->return_otp && $order->parcelCancellation->return_otp != $request->return_otp => [
                'code' => 'order',
                'message' => translate('messages.Invalid_return_otp'),
                'status_code' => 403,
            ],

            default => null,
        };

        if ($validationError) {
            return $validationError;
        }

        return null;
    }


    public static function cancelParcelOrder($order, $cancel_by, $request)
    {
        if (in_array($order->order_status, ['canceled', 'delivered', 'returned'])) {
            return ['status_code' => 403, 'code' => 'complete_order', 'message' => translate('messages.you_can_not_cancel_a_completed_order')];
        }
        $code = 'success';
        $msg = translate('Parcel_canceled_successfully');
        $parcel_cancellation_basic_setup = Helpers::get_business_settings('parcel_cancellation_basic_setup');

        $return_fee_status = $parcel_cancellation_basic_setup['return_fee_status'] ?? 0;
        $return_fee = $parcel_cancellation_basic_setup['return_fee'] ?? 0;
        $do_not_charge_return_fee_on_deliveryman_cancel = $parcel_cancellation_basic_setup['do_not_charge_return_fee_on_deliveryman_cancel'] ?? 0;

        $orderOldStatus = $order->order_status;
        $deliveryManId = $order->delivery_man_id;
        $order->order_status = 'canceled';
        $order->canceled = now();
        $order->canceled_by = $cancel_by;
        $order->save();

        if ($deliveryManId) {
            $dm = DeliveryMan::find($deliveryManId);
            if ($dm) {
                $dm->current_orders = $dm->current_orders > 1 ? $dm->current_orders - 1 : 0;
                $dm->save();
            }
        }

        $parcelCancellation = ParcelCancellation::where('order_id', $order->id)->firstOrNew();
        $parcelCancellation->order_id = $order->id;
        $parcelCancellation->cancel_by = $cancel_by;
        $parcelCancellation->note = $request->note ?? null;
        $parcelCancellation->reason = json_encode($request->reason);

        if (in_array($orderOldStatus, ['picked_up'])) {
            $parcelCancellation->before_pickup = 0;
            $parcelCancellation->return_otp = random_int(1000, 9999);

            if ($return_fee_status == 1 && $return_fee > 0) {
                if ((in_array($cancel_by, ['deliveryman', 'admin_for_deliveryman']) && $do_not_charge_return_fee_on_deliveryman_cancel == 1)) {
                    $parcelCancellation->return_fee = 0;
                } else {
                    $chargeAmount = $order['delivery_charge'] + $order['total_tax_amount'] + $order['additional_charge'] - $order['coupon_discount_amount'] - $order['ref_bonus_amount'];
                    $parcelCancellation->return_fee = ($chargeAmount * $return_fee) / 100;
                }
            }

            $parcel_return_time_fee = Helpers::get_business_settings('parcel_return_time_fee');
            $parcel_return_time_fee_status = $parcel_return_time_fee['status'] ?? 0;
            $return_fee_for_dm = $parcel_return_time_fee['return_fee_for_dm'] ?? 0;

            if ($parcel_return_time_fee_status == 1 && $return_fee_for_dm > 0) {
                $parcelCancellation->dm_penalty_fee = $return_fee_for_dm;
                $parcelCancellation->return_date = now()->addDays((int) $parcel_return_time_fee['parcel_return_time'] ?? 1);
            }
        } else {
            if ($order->payment_status == 'paid' && $order->is_guest == 0) {
                if (Helpers::get_business_settings('wallet_status') == 1 && Helpers::get_business_settings('wallet_add_refund') == 1) {
                    $refunded = self::refund_before_delivered($order);
                    if ($refunded) {
                        self::parcelRefundNotification($order, true);
                    }
                } else {
                    $parcelCancellation->is_delivery_charge_refundable = 1;
                    $code = 'wallet_failed';
                    $msg = translate('messages.Parcel_canceled_successfully_contact_admin_for_refund');
                }
            } elseif ($order->payment_status == 'paid' && $order->is_guest == 1) {
                $code = 'wallet_failed';
                $msg = translate('messages.Parcel_canceled_successfully_contact_admin_for_refund');
                $parcelCancellation->is_delivery_charge_refundable = 1;
            }
        }

        $parcelCancellation->save();
        Helpers::send_order_notification($order);

        return ['status_code' => 200, 'code' => $code, 'message' => $msg];
    }

    public static function deliveryManCancelParcelTransaction($order)
    {

        $return_fee = $order?->parcelCancellation?->return_fee ?? 0;
        DB::beginTransaction();

        $order->order_status = 'returned';
        $order->save();

        $order->parcelCancellation->return_fee_payment_status = 'paid';

        if ($order->payment_status == 'paid' && $order->is_guest == 0) {
            if (Helpers::get_business_settings('wallet_status') == 1 && Helpers::get_business_settings('wallet_add_refund') == 1) {
                $refunded = self::refund_before_delivered($order);
                if ($refunded) {
                    self::parcelRefundNotification($order, true);
                }
            } else {
                $order->parcelCancellation->is_delivery_charge_refundable = 1;
            }
        } elseif ($order->payment_status == 'paid' && $order->is_guest == 1) {
            $order->parcelCancellation->is_delivery_charge_refundable = 1;
        }

        $order->parcelCancellation->save();

        try {

            $adminWallet = AdminWallet::firstOrNew(
                ['admin_id' => Admin::where('role_id', 1)->first()->id]
            );

            if ($order->delivery_man) {
                $dmWallet = DeliveryManWallet::firstOrNew(
                    ['delivery_man_id' => $order->delivery_man_id]
                );
                if ($order->delivery_man->earning == 1) {
                    $dmWallet->total_earning = $dmWallet->total_earning + $return_fee;
                } else {
                    $adminWallet->total_commission_earning = $adminWallet->total_commission_earning + $return_fee;
                }
            } else {
                $adminWallet->total_commission_earning = $adminWallet->total_commission_earning + $return_fee;
            }

            $adminWallet->save();

            if ($order->parcelCancellation->return_date) {
                $returnDate = Carbon::parse($order->parcelCancellation->return_date);
                if ($returnDate->isPast() && isset($dmWallet)) {
                    $dmWallet->collected_cash = $dmWallet->collected_cash + $order->parcelCancellation->dm_penalty_fee ?? 0;
                }
            }



            if (isset($dmWallet)) {
                $dmWallet->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            info($e->getMessage());
            return false;
        }

        return true;

    }

    public static function parcelRefundNotification($order, $wallet = true)
    {
        try {
            if (Helpers::getNotificationStatusData('customer', 'customer_refund_request_approval', 'push_notification_status') && $order?->customer?->cm_firebase_token) {
                $data = [
                    'title' => translate('messages.order_refunded'),
                    'description' => $wallet ? translate('Your Parcel\'s delivery charge has been refunded to your wallet') : translate('Your Parcel\'s delivery charge has been marked as Refunded'),
                    'order_id' => $order->id,
                    'image' => '',
                    'type' => 'order_status',
                    'order_status' => $order->order_status,
                ];
                Helpers::send_push_notif_to_device($order?->customer?->cm_firebase_token, $data);
                DB::table('user_notifications')->insert([
                    'data' => json_encode($data),
                    'user_id' => $order->user_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            // if(config('mail.status') && $order?->customer?->email && Helpers::get_mail_status('refund_order_mail_status_user') == '1'  &&  Helpers::getNotificationStatusData('customer','customer_refund_request_approval','mail_status') ){
            //     Mail::to($order->customer->email)->send(new \App\Mail\RefundedOrderMail($order->id));
            // }
        } catch (\Throwable $th) {
            info($th->getMessage());
        }
        return true;
    }


    public static function deliverymanReferalTransaction($deliveryManId, $referType, $referrerId, $reference)
    {

        $settings = array_column(BusinessSetting::whereIn('key', ['dm_referal_status', 'dm_referal_amount', 'dm_referal_bonus'])->get()->toArray(), 'value', 'key');

        if (data_get($settings, 'dm_referal_status') != 1) {
            return ['status_code' => 403, 'code' => 'Referal', 'message' => translate('referal_option_is_not_enabled')];
        } elseif ($referType == 'referral' && data_get($settings, 'dm_referal_amount') <= 0) {
            return ['status_code' => 403, 'code' => 'Referal', 'message' => translate('referal_option_is_not_enabled')];
        } elseif ($referType == 'referrerBonus' && data_get($settings, 'dm_referal_bonus') <= 0) {
            return ['status_code' => 403, 'code' => 'Referal', 'message' => translate('referal_option_is_not_enabled')];
        }


        $deliveryMan = DeliveryMan::find($deliveryManId);
        if (!$deliveryMan) {
            return ['status_code' => 403, 'code' => 'wallet', 'message' => translate('delivery_man_not_found')];
        } elseif ($deliveryMan->earning != 1) {
            return ['status_code' => 403, 'code' => 'Referal', 'message' => translate('wallet_not_enabled')];
        }

        $referralHistory = new DeliverymanReferralHistory();
        $referralHistory->delivery_man_id = $deliveryMan->id;


        $amount = $referType == 'referrerBonus' ? data_get($settings, 'dm_referal_bonus', 0) : data_get($settings, 'dm_referal_amount', 0);

        $referralHistory->amount = $amount;

        $referralHistory->referrer_id = $referrerId;
        $referralHistory->refer_type = $referType;
        $referralHistory->reference = $reference;
        $referralHistory->transaction_id = Str::uuid();

        $dmWallet = DeliveryManWallet::firstOrNew(['delivery_man_id' => $deliveryMan->id]);
        $dmWallet->total_earning = $dmWallet->total_earning + $amount;

        try {
            DB::beginTransaction();
            $referralHistory->save();
            $referralHistory->transaction_id = Helpers::generate_transaction_id($referralHistory);
            $referralHistory->save();
            $dmWallet->save();
            DB::commit();

        } catch (\Exception $exception) {
            info(["line___{$exception->getLine()}", $exception->getMessage()]);
            DB::rollback();
            return ['status_code' => 403, 'code' => 'loyalty_point', 'message' => translate('messages.something_went_wrong')];
        }

        try {
            $data = [
                'title' => translate('Referal Bonus'),
                'description' => translate('Congratulations! You have received a referal bonus of ') . Helpers::format_currency($amount),
                'data_id' => $referralHistory->id,
                'image' => '',
                'type' => 'deliveryman_referral',
            ];
            if (Helpers::getNotificationStatusData('deliveryman', 'deliveryman_referral_bonus', 'push_notification_status') && $deliveryMan->fcm_token) {
                Helpers::send_push_notif_to_device($deliveryMan->fcm_token, $data);
                DB::table('user_notifications')->insert([
                    'data' => json_encode($data),
                    'delivery_man_id' => $deliveryMan->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

        } catch (\Exception $exception) {
            info(["line___{$exception->getLine()}", $exception->getMessage()]);
        }
        return true;
    }

    public static function apply_price_adjustment(Order $order, $actual_price)
    {
        if ($order->order_type != 'parcel' || !in_array($order->adjustment_status, ['none', 'approved'])) {
            return false;
        }

        $estimated_price = $order->parcel_item_estimated_price;
        $diff = $actual_price - $estimated_price;

        $order->actual_parcel_item_price = $actual_price;
        $order->order_amount = $order->order_amount + $diff;
        $order->adjustment_status = 'adjusted';
        $order->save();

        if ($diff < 0) {
            // Refund to wallet
            if ($order->is_guest == 0 && $order->user_id) {
                CustomerLogic::create_wallet_transaction($order->user_id, abs($diff), 'order_refund', $order->id);

                // Send notification
                $notification_data = [
                    'title' => translate('messages.price_adjustment_refund'),
                    'description' => translate('An amount of') . ' ' . Helpers::format_currency(abs($diff)) . ' ' . translate('has been refunded to your wallet due to price difference in your parcel order.'),
                    'order_id' => $order->id,
                    'image' => '',
                    'type' => 'order_status',
                ];

                if ($order->customer->cm_firebase_token && Helpers::getNotificationStatusData('customer', 'customer_order_notification', 'push_notification_status')) {
                    Helpers::send_push_notif_to_device($order->customer->cm_firebase_token, $notification_data);
                    DB::table('user_notifications')->insert([
                        'data' => json_encode($notification_data),
                        'user_id' => $order->customer->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        } elseif ($diff > 0) {
            $user = $order->customer;
            $msg = "";
            if ($user && $user->wallet_balance >= $diff) {
                // Deduct from wallet
                CustomerLogic::create_wallet_transaction($order->user_id, $diff, 'order_charge', $order->id);
                $msg = translate('Your parcel order cost more than estimated. An extra charge of') . ' ' . Helpers::format_currency($diff) . ' ' . translate('has been deducted from your wallet.');
            } else {
                // Create unpaid payment for the difference
                $unpaid_payment = new OrderPayment();
                $unpaid_payment->order_id = $order->id;
                $unpaid_payment->amount = $diff;
                $unpaid_payment->payment_status = 'unpaid';
                $unpaid_payment->payment_method = $order->payment_method == 'wallet' ? 'digital_payment' : $order->payment_method;
                $unpaid_payment->save();

                $msg = translate('Your parcel order cost more than estimated. Please pay the difference of') . ' ' . Helpers::format_currency($diff) . ' ' . translate('via digital payment.');
            }

            // Send notification for extra charge
            if ($order->is_guest == 0 && $order->user_id) {
                $notification_data = [
                    'title' => translate('messages.price_adjustment_extra_charge'),
                    'description' => $msg,
                    'order_id' => $order->id,
                    'image' => '',
                    'type' => 'order_status',
                ];

                if ($order->customer->cm_firebase_token && Helpers::getNotificationStatusData('customer', 'customer_order_notification', 'push_notification_status')) {
                    Helpers::send_push_notif_to_device($order->customer->cm_firebase_token, $notification_data);
                    DB::table('user_notifications')->insert([
                        'data' => json_encode($notification_data),
                        'user_id' => $order->customer->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        return true;
    }

    public static function propose_parcel_price(Order $order, $price)
    {
        if ($order->order_type != 'parcel' || !in_array($order->order_status, ['accepted', 'confirmed', 'processing', 'handover'])) {
            return false;
        }

        $order->proposed_parcel_item_price = $price;
        $order->adjustment_status = 'pending_approval';
        $order->save();

        // Notify customer
        $notification_data = [
            'title' => translate('messages.price_proposal_for_your_parcel'),
            'description' => translate('Delivery man has proposed a price of') . ' ' . Helpers::format_currency($price) . ' ' . translate('for the items in your parcel order. Please approve or reject.'),
            'order_id' => $order->id,
            'image' => '',
            'type' => 'order_status',
        ];

        if ($order->customer->cm_firebase_token && Helpers::getNotificationStatusData('customer', 'customer_order_notification', 'push_notification_status')) {
            Helpers::send_push_notif_to_device($order->customer->cm_firebase_token, $notification_data);
            DB::table('user_notifications')->insert([
                'data' => json_encode($notification_data),
                'user_id' => $order->customer->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return true;
    }

    public static function update_parcel_price_approval(Order $order, $status)
    {
        if ($order->adjustment_status != 'pending_approval') {
            return false;
        }

        if ($status == 'approved') {
            $order->adjustment_status = 'approved';
            $order->save();

            // Apply price adjustment logic
            self::apply_price_adjustment($order, $order->proposed_parcel_item_price);

            // Notify DM
            if ($order?->delivery_man?->fcm_token) {
                $notification_data = [
                    'title' => translate('messages.price_approved'),
                    'description' => translate('Customer has approved the proposed price for order') . ' #' . $order->id . '. You can proceed to buy and pick up.',
                    'order_id' => $order->id,
                    'image' => '',
                    'type' => 'order_status',
                ];
                Helpers::send_push_notif_to_device($order->delivery_man->fcm_token, $notification_data);
            }
        } elseif ($status == 'rejected') {
            $order->adjustment_status = 'rejected';

            // Charge cancellation fee (Total Delivery Charge)
            $fee = $order->delivery_charge;
            $refund_amount = $order->order_amount - $fee;

            if ($order->payment_status == 'paid' && $order->is_guest == 0) {
                // Refund order_amount - fee to wallet
                CustomerLogic::create_wallet_transaction($order->user_id, $refund_amount, 'order_refund', $order->id);
            }

            $order->order_status = 'canceled';
            $order->canceled = now();
            $order->canceled_by = 'customer';
            $order->cancellation_reason = 'Price rejected by customer';
            $order->save();

            // Notify DM
            if ($order?->delivery_man?->fcm_token) {
                $notification_data = [
                    'title' => translate('messages.price_rejected'),
                    'description' => translate('Customer has rejected the proposed price for order') . ' #' . $order->id . '. The order has been canceled.',
                    'order_id' => $order->id,
                    'image' => '',
                    'type' => 'order_status',
                ];
                Helpers::send_push_notif_to_device($order->delivery_man->fcm_token, $notification_data);
            }
        }

        return true;
    }

    /**
     * Encola asignación de repartidor para el motor Go (misma cola que PlaceNewOrder).
     * No lanza excepción: fallos de Redis no deben revertir la orden ya guardada.
     */
    public static function assign_delivery_man(int $orderId): bool
    {
        try {
            $order = Order::query()->find($orderId);
            if (! $order || $order->order_type !== 'delivery') {
                return false;
            }

            $payload = json_encode([
                'type' => 'assign_delivery',
                'data' => [
                    'order_id' => $order->id,
                    'store_id' => $order->store_id,
                    'zone_id'  => $order->zone_id,
                    'attempt'  => 1,
                ],
            ]);

            \Illuminate\Support\Facades\Redis::rpush('tootli:go_jobs', $payload);
            info('[GoWorker] assign_delivery encolado desde POS API para orden #'.$order->id);

            return true;
        } catch (\Throwable $e) {
            info('[OrderLogic::assign_delivery_man] '.$e->getMessage());

            return false;
        }
    }

    public static function dm_net_earning($order)
    {
        if ($order->store && $order->store->sub_self_delivery) {
            return (float) ($order->original_delivery_charge);
        }

        $delivery_charge = (float) $order->original_delivery_charge;
        
        if ($delivery_charge < self::TOOTLI_BASE_SHIPPING_FEE) {
            // Si la tarifa cobrada es menor a la base, pagamos el 80% de la tarifa cobrada (ej. multi-tienda secundarias)
            $net_earning = $delivery_charge * (self::TOOTLI_BASE_DM_PAY / self::TOOTLI_BASE_SHIPPING_FEE);
        } else {
            $surcharge = $delivery_charge - self::TOOTLI_BASE_SHIPPING_FEE;
            $net_earning = self::TOOTLI_BASE_DM_PAY + $surcharge;
        }

        return (float) ($net_earning + ($order->incentive_amount ?? 0));
    }

    public static function calculate_order_incentive($order, $level)
    {
        if ($level <= 0) return 0;

        // 1. Calcular comisión de envío del administrador (máximo sobre el envío base)
        $comission = BusinessSetting::where('key', 'delivery_charge_comission')->first();
        $comission_percentage = $comission ? $comission->value : 0;
        if ($order->tootli_direct ?? false) {
            $direct_del = BusinessSetting::where('key', 'tootli_direct_delivery_commission')->first();
            $comission_percentage = $direct_del !== null ? (float) $direct_del->value : 0;
        }
        $base_for_commission = min(self::TOOTLI_BASE_SHIPPING_FEE, (float)$order->original_delivery_charge);
        $admin_delivery_commission = $comission_percentage * ($base_for_commission / 100);

        // 2. Calcular comisión de tienda del administrador
        $store_commission = 0.0;
        if ($order->order_type == 'parcel') {
            $comission_parcel = BusinessSetting::where('key', 'parcel_commission_dm')->first();
            $comission_parcel = isset($comission_parcel) ? $comission_parcel->value : 0;
            $order_amount = $order->order_amount - $order->dm_tips - $order->additional_charge - $order->extra_packaging_amount - $order->total_tax_amount;
            $dm_commission = $comission_parcel ? ($order_amount / 100) * $comission_parcel : 0;
            $store_commission = $order_amount - $dm_commission;
        } else {
            $comission_store = isset($order->store->comission) == null ? BusinessSetting::where('key', 'admin_commission')->first()->value : $order->store->comission;
            $order_amount = $order->order_amount - $order->additional_charge - $order->extra_packaging_amount - $order->delivery_charge - $order->total_tax_amount - $order->dm_tips + ($order->flash_admin_discount_amount ?? 0) + $order->coupon_discount_amount + ($order->store_discount_amount ?? 0) + ($order->flash_store_discount_amount ?? 0) + ($order->ref_bonus_amount ?? 0);
            $store_commission = $comission_store ? ($order_amount / 100) * $comission_store : 0;
        }

        // 3. Ganancia Bruta del Administrador en la Orden
        $admin_gross_earnings = $admin_delivery_commission + (float)$order->additional_charge + $store_commission;

        // 4. Establecer el Tope de Subsidio para conservar al menos $5.0 MXN neta de ganancia
        $max_subsidized_incentive = max(0.0, $admin_gross_earnings - 5.0);

        // 5. Determinar el incentivo según el nivel
        $calculated_incentive = 0.0;
        if ($level == 1) {
            $calculated_incentive = $admin_delivery_commission;
        } elseif ($level == 2) {
            // Level 2 = Comisión de Envío + $15.00 extra por espera prolongada
            $calculated_incentive = $admin_delivery_commission + 15.0;
        }

        // Aplicar el tope
        $actual_incentive = min($calculated_incentive, $max_subsidized_incentive);

        return round((float) max(0.0, $actual_incentive), 2);
    }

    public static function process_cash_on_pickup($order)
    {
        // Solo aplica si el pedido es de tipo delivery y pagado en efectivo (COD)
        if ($order->payment_method !== 'cash_on_delivery' || $order->order_type !== 'delivery') {
            return;
        }

        // Si ya se registró el pago, no hacer nada (prevenir doble ejecución)
        $already_paid = \App\Models\RepartidorPagoTiendaEfectivo::where('order_id', $order->id)->exists();
        if ($already_paid) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            $dmWallet = DeliveryManWallet::firstOrCreate(['delivery_man_id' => $order->delivery_man_id]);
            $storeWallet = StoreWallet::firstOrCreate(['vendor_id' => $order->store->vendor_id]);

            // El costo de alimentos que el repartidor entrega al restaurante en efectivo:
            // Food cost = order_amount - delivery_charge (sin comisiones del driver ni del servicio de Tootli)
            // Nota: Por el momento no se tienen impuestos ni cargos adicionales en la ecuación
            $food_cost = (float)($order->order_amount - $order->delivery_charge - ($order->dm_tips ?? 0));
            if ($food_cost <= 0) {
                return;
            }

            // HÍBRIDO + LIQUIDACIÓN DIRECTA: Si la tienda tiene deuda acumulada (balance negativo), se le descuenta de lo que el repartidor le va a pagar en efectivo
            $store_debt = ($storeWallet->balance < 0) ? abs($storeWallet->balance) : 0.0;
            $cash_to_pay = (float) max(0.0, $food_cost - $store_debt);

            // HÍBRIDO: Si el repartidor no tiene suficiente efectivo en mano para el pago neto, omitir este descuento y tratar como COD tradicional
            if ($dmWallet->collected_cash < $cash_to_pay) {
                return;
            }

            // 1. Restar el efectivo neto pagado de collected_cash del repartidor
            $dmWallet->collected_cash = (float) max(0.0, $dmWallet->collected_cash - $cash_to_pay);
            $dmWallet->save();

            // 2. Sumar el efectivo recibido al restaurante en su collected_cash
            // Al aumentar collected_cash de la tienda por $cash_to_pay, su balance digital se reduce en esa misma cantidad física recibida,
            // y la deuda de comisiones previas queda saldada digitalmente al 100% de forma limpia.
            $storeWallet->collected_cash = (float)($storeWallet->collected_cash + $cash_to_pay);
            $storeWallet->save();

            // 3. Registrar la transacción en nuestra nueva tabla
            \App\Models\RepartidorPagoTiendaEfectivo::create([
                'order_id' => $order->id,
                'delivery_man_id' => $order->delivery_man_id,
                'store_id' => $order->store_id,
                'amount_paid' => $cash_to_pay,
                'verified_by_store' => true,
            ]);
        });
    }
}
