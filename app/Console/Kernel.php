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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Run persist-data command every hour to ensure data persistence
        $schedule->command('app:persist-data')
            ->hourly()
            ->name('persist-data-hourly')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Data persistence check completed successfully');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Data persistence check failed');
            });

        // Run daily at 2:00 AM with full verification and export
        $schedule->command('app:persist-data --verify --export-csv')
            ->dailyAt('02:00')
            ->name('persist-data-daily-full')
            ->timezone('Asia/Bahrain')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Daily data persistence full check completed successfully');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Daily data persistence full check failed');
            });

        // Optional: Run immediately after deployment to verify data integrity
        // Uncomment the line below if you want to run this after each deployment
        // $schedule->command('app:persist-data')->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
