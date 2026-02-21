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
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Record OBS overlay daily at 5:00 AM
        // Auto-detects local server when running (no --environment needed)
        $schedule->command('obs:record')
            ->dailyAt('05:00')
            ->name('obs-overlay-record-daily')
            ->timezone('Asia/Bahrain')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/obs-overlay.log'))
            ->onSuccess(function (): void {
                \Illuminate\Support\Facades\Log::info('OBS overlay recording completed successfully');
            })
            ->onFailure(function (): void {
                \Illuminate\Support\Facades\Log::error('OBS overlay recording failed');
            });

        // Run persist-data command every hour to ensure data persistence
        $schedule->command('app:persist-data')
            ->hourly()
            ->name('persist-data-hourly')
            ->withoutOverlapping()
            ->onSuccess(function (): void {
                \Illuminate\Support\Facades\Log::info('Data persistence check completed successfully');
            })
            ->onFailure(function (): void {
                \Illuminate\Support\Facades\Log::error('Data persistence check failed');
            });

        // Export all callers to encrypted CSV daily at 2:00 AM
        $schedule->command('callers:export --encrypt=true')
            ->dailyAt('02:00')
            ->name('callers-export-daily')
            ->timezone('Asia/Bahrain')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/exports.log'))
            ->onSuccess(function (): void {
                \Illuminate\Support\Facades\Log::info('Daily callers export completed successfully');
            })
            ->onFailure(function (): void {
                \Illuminate\Support\Facades\Log::error('Daily callers export failed');
            });

        // Run daily at 2:00 AM with full verification and export
        $schedule->command('app:persist-data --verify --export-csv')
            ->dailyAt('02:00')
            ->name('persist-data-daily-full')
            ->timezone('Asia/Bahrain')
            ->withoutOverlapping()
            ->onSuccess(function (): void {
                \Illuminate\Support\Facades\Log::info('Daily data persistence full check completed successfully');
            })
            ->onFailure(function (): void {
                \Illuminate\Support\Facades\Log::error('Daily data persistence full check failed');
            });

        // Optional: Run immediately after deployment to verify data integrity
        // Uncomment the line below if you want to run this after each deployment
        // $schedule->command('app:persist-data')->onOneServer();

        // Send daily selected names email at 9:00 AM (after typical show time)
        $schedule->command('app:send:daily-selected-emails')
            ->dailyAt('09:00')
            ->name('daily-selected-emails')
            ->timezone('Asia/Bahrain')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/daily-selected-emails.log'))
            ->onSuccess(function (): void {
                \Illuminate\Support\Facades\Log::info('Daily selected names email sent successfully');
            })
            ->onFailure(function (): void {
                \Illuminate\Support\Facades\Log::error('Daily selected names email failed');
            });
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
