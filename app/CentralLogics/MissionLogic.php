<?php

namespace App\CentralLogics;

use App\Models\Mission;
use App\Models\DeliveryMan;
use App\Models\DeliveryManWallet;
use App\Models\AccountTransaction;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;

class MissionLogic
{
    /**
     * Increment mission progress for a delivery man after an order is delivered.
     */
    public static function increment_mission_progress($order)
    {
        $dm_id = $order->delivery_man_id;
        $zone_id = $order->zone_id;

        if (!$dm_id)
            return;

        // Find active missions for this zone (or global missions if zone_id is null)
        $active_missions = Mission::where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where(function ($query) use ($zone_id) {
                $query->where('zone_id', $zone_id)
                    ->orWhereNull('zone_id');
            })
            ->get();

        foreach ($active_missions as $mission) {
            // Update or create progress record
            $progress = DB::table('mission_delivery_man')
                ->where('mission_id', $mission->id)
                ->where('delivery_man_id', $dm_id)
                ->first();

            if (!$progress) {
                DB::table('mission_delivery_man')->insert([
                    'mission_id' => $mission->id,
                    'delivery_man_id' => $dm_id,
                    'current_count' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $current_count = 1;
                $is_completed = false;
            } else {
                if ($progress->is_completed)
                    continue;

                $current_count = $progress->current_count + 1;
                $is_completed = ($current_count >= $mission->target_orders);

                DB::table('mission_delivery_man')
                    ->where('id', $progress->id)
                    ->update([
                        'current_count' => $current_count,
                        'is_completed' => $is_completed,
                        'completed_at' => $is_completed ? now() : null,
                        'updated_at' => now(),
                    ]);
            }

            // If mission just completed, process payment
            if ($is_completed) {
                self::process_mission_payout($mission, $dm_id, $order->id);
            }
        }
    }

    /**
     * Credit the delivery man's wallet for completing a mission.
     */
    public static function process_mission_payout($mission, $dm_id, $order_id)
    {
        $dm = DeliveryMan::find($dm_id);
        $reward = $mission->reward_amount;

        if ($reward <= 0)
            return;

        DB::beginTransaction();
        try {
            $wallet = DeliveryManWallet::firstOrNew(['delivery_man_id' => $dm_id]);
            $wallet->total_earning += $reward;
            $wallet->save();

            // Log the transaction
            $transaction = new AccountTransaction();
            $transaction->from_type = 'admin';
            $transaction->from_id = 1; // Assuming admin ID 1
            $transaction->to_type = 'deliveryman';
            $transaction->to_id = $dm_id;
            $transaction->method = 'mission_reward';
            $transaction->ref = "Mission ID: {$mission->id}, Goal Order ID: {$order_id}";
            $transaction->amount = $reward;
            $transaction->current_balance = $wallet->total_earning;
            $transaction->type = 'credit';
            $transaction->save();

            DB::commit();

            // Send Push Notification
            $fcm_token = $dm->fcm_token;
            if ($fcm_token) {
                $notification_data = [
                    'title' => '¡Misión Completada!',
                    'description' => "Has ganado " . Helpers::format_currency($reward) . " por completar la misión: {$mission->title}",
                    'order_id' => $order_id,
                    'image' => '',
                    'type' => 'mission_completion',
                ];
                Helpers::send_push_notif_to_device($fcm_token, $notification_data);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            info("Error processing mission payout: " . $e->getMessage());
        }
    }

    /**
     * Get active missions with progress for a delivery man.
     */
    public static function get_dm_missions($dm_id, $zone_id)
    {
        $active_missions = Mission::where('status', 1)
            ->where('end_date', '>=', now())
            ->where(function ($query) use ($zone_id) {
                $query->where('zone_id', $zone_id)
                    ->orWhereNull('zone_id');
            })
            ->get();

        return $active_missions->map(function ($mission) use ($dm_id) {
            $progress = DB::table('mission_delivery_man')
                ->where('mission_id', $mission->id)
                ->where('delivery_man_id', $dm_id)
                ->first();

            $mission->setAttribute('current_progress', $progress ? (int) $progress->current_count : 0);
            $mission->setAttribute('is_completed', $progress ? (bool) $progress->is_completed : false);
            return $mission;
        });
    }
}
