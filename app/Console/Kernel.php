<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\SendNightlyDebtReminders::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('spei:cancel-expired')->everyMinute();
        $schedule->command('dm:recalculate-tiers')->dailyAt('03:15');
        $schedule->command('delivery:send-deposit-reminders')->everyThirtyMinutes();
        $schedule->command('delivery:nightly-debt-reminders')->dailyAt('22:00');
        $schedule->command('order:incentivize')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
