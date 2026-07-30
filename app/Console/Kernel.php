<?php

namespace App\Console;

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
        // Apply salary revisions every day at midnight
        $schedule->command('salary:apply-revisions')->dailyAt('00:00');

        // Auto-generate payslips for previous month on the 6th of each month at 2:00 AM
        $schedule->command('payslip:generate-monthly')->monthlyOn(6, '02:00');

        // Automatically clock out employees every minute based on shift end time + auto_clock_out_time
        $schedule->command('auto:clock-out')->everyMinute();
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
