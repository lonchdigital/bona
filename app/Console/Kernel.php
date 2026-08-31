<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Needs a cron calling `php artisan schedule:run` every minute;
        // without one this simply never fires and nothing else breaks.
        $schedule->command('wishlist:prune-guests')->dailyAt('04:15');
        $schedule->command('logs:prune')->dailyAt('04:30')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
