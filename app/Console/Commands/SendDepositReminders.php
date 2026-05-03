<?php

namespace App\Console\Commands;

use App\Models\DeliveryMan;
use App\Models\DeliveryManWallet;
use App\Models\BusinessSetting;
use App\CentralLogics\Helpers;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendDepositReminders extends Command
{
    protected $signature = 'delivery:send-deposit-reminders';
    protected $description = 'Send push notifications to delivery men who have exceeded the maximum time without depositing cash.';

    public function handle()
    {
        $maxTime = (int)(BusinessSetting::where('key', 'max_time_without_deposit_minutes')->first()?->value ?? 120);
        
        // Repartidores con deuda > 0 que no han depositado en $maxTime minutos
        $deliveryMen = DeliveryMan::whereHas('wallet', function($query) {
                $query->where('collected_cash', '>', 0);
            })
            ->where(function($query) use ($maxTime) {
                $query->where('last_deposit_at', '<', Carbon::now()->subMinutes($maxTime))
                      ->orWhereNull('last_deposit_at');
            })
            ->get();

        foreach ($deliveryMen as $dm) {
            $wallet = DeliveryManWallet::where('delivery_man_id', $dm->id)->first();
            $debt = $wallet->collected_cash;

            $fcm_token = $dm->fcm_token;
            if ($fcm_token) {
                $data = [
                    'title' => translate('messages.deposit_required'),
                    'description' => translate('messages.you_have') . ' ' . Helpers::format_currency($debt) . ' ' . translate('messages.cash_in_hand_please_deposit_soon'),
                    'type' => 'deposit_reminder',
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
            }
            
            $this->info("Reminder sent to DM ID: {$dm->id} - Debt: {$debt}");
        }

        return Command::SUCCESS;
    }
}
