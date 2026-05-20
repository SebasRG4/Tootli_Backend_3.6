<?php

namespace App\Console\Commands;

use App\Models\DeliveryMan;
use App\CentralLogics\Helpers;
use Illuminate\Console\Command;

class SendNightlyDebtReminders extends Command
{
    protected $signature = 'delivery:nightly-debt-reminders';
    protected $description = 'Send friendly nightly push notifications at 10 PM to all delivery men who have pending cash in hand.';

    public function handle()
    {
        $deliveryMen = DeliveryMan::whereHas('wallet', function($query) {
                $query->where('collected_cash', '>', 0);
            })
            ->get();

        foreach ($deliveryMen as $dm) {
            $debt = $dm->wallet ? $dm->wallet->collected_cash : 0.0;
            if ($debt <= 0) {
                continue;
            }

            $fcm_token = $dm->fcm_token;
            if ($fcm_token) {
                $data = [
                    'title' => '📝 Cierre de Día - Tootli 💚',
                    'description' => 'Tienes ' . Helpers::format_currency($debt) . ' pendientes por depositar. Cada depósito nos ayuda a seguir creciendo y generar más oportunidades para todos 💚',
                    'order_id' => '',
                    'image' => '',
                    'type' => 'debt_reminder',
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
            }
            
            $this->info("Nightly reminder sent to DM ID: {$dm->id} - Debt: {$debt}");
        }

        return Command::SUCCESS;
    }
}
