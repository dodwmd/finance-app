<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    #[\Override]
    protected function schedule(Schedule $schedule): void
    {
        // Process recurring transactions daily at midnight
        $schedule->command('app:process-recurring-transactions')
            ->daily()
            ->at('00:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/recurring-transactions.log'));
    }

    /**
     * Register the commands for the application.
     */
    #[\Override]
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
