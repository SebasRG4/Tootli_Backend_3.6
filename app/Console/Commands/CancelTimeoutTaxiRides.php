<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Taxi\Models\TaxiRide;
use App\Services\FirebaseService;

class CancelTimeoutTaxiRides extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taxi:cancel-timeout-rides';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Automatically cancel pending taxi rides that have been searching for a driver for more than 45 seconds and notify the customer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeoutThreshold = now()->subSeconds(45);

        // Fetch pending taxi rides older than 45 seconds
        $expiredRides = TaxiRide::where('status', 'pending')
            ->where('created_at', '<=', $timeoutThreshold)
            ->get();

        if ($expiredRides->isEmpty()) {
            return Command::SUCCESS;
        }

        foreach ($expiredRides as $ride) {
            $this->info("Cancelling expired ride ID: {$ride->id}");

            // Cancel the ride
            $ride->cancel('system', 'timeout');

            // Send search timeout notification with custom sound
            try {
                FirebaseService::sendSearchTimeoutNotification($ride);
            } catch (\Exception $e) {
                $this->error("Failed to send timeout notification for ride ID {$ride->id}: {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }
}
