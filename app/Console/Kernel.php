<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\UpdateContestStatus',
        'App\Console\Commands\VerifyUserContest',
        'App\Console\Commands\UpdateLeagueStatus'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('verify:contest')->everyMinute()->evenInMaintenanceMode();
        $schedule->command('batting:updateContestStatus')->everyMinute()->evenInMaintenanceMode();
        $schedule->command('batting:updateLeagueStatus')->everyMinute()->evenInMaintenanceMode();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands() {
        require base_path('routes/console.php');
    }

}
