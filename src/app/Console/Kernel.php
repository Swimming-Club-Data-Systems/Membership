<?php

namespace App\Console;

use App\Console\Commands\UpdateMeilisearchDocuments;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // CLean up telescope records
        $schedule->command('telescope:prune')->daily();

        // CLean up prune able models
        $schedule->command('model:prune')->daily();

        // Update Meilisearch records
        $schedule->command(UpdateMeilisearchDocuments::class)->everyFifteenMinutes();

        // Back up data
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->at('01:30');

        $schedule->command('horizon:snapshot')->everyFiveMinutes();
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
