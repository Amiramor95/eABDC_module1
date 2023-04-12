<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\NotifyExtensionRequest::class,
        \App\Console\Commands\cessationAction::class,
        \App\Console\Commands\revokeAction::class,
        \App\Console\Commands\suspendAction::class,
        \App\Console\Commands\unsuspendAction::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('extension:notify')->daily();
        $filepath = 'cron_log.log';
        $schedule->command('auto:cease')
        ->timezone('Asia/Kuala_Lumpur')
        ->daily()
        ->appendOutputTo('$filepath');
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
