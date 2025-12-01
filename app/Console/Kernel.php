<?php

namespace App\Console;

use App\Console\Commands\SendScheduledInspections;
use App\Console\Commands\SendWeeklyReport;
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
        SendScheduledInspections::class,
        SendWeeklyReport::class,
    ];

    /**
     * Define the application's command schedule.
     */

    protected function schedule(Schedule $schedule): void
    {
        // 点検依頼メールを毎週月曜8時に送信
        $schedule->command('inspections:send-scheduled')
            ->mondays()
            ->at('8:00');

        $schedule->command('inspections:send-weekly-report')
            ->mondays()
            ->at('9:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
