<?php

namespace App\Console;

use App\Console\Commands\SendScheduledInspections;
use App\Console\Commands\SendWeeklyReport; // ★★★★★ 今回追加したコマンドを読み込み ★★★★★
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
        SendWeeklyReport::class, // ★★★★★ 今回追加したコマンドを登録 ★★★★★
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 点検依頼メールを毎週月曜8時に送信
        $schedule->command('inspections:send-scheduled')
            ->mondays()
            ->at('08:00');

        // ★★★★★ 週次レポートを毎週月曜9時に送信するスケジュールを追加 ★★★★★
        $schedule->command('inspections:send-weekly-report')
            ->mondays()
            ->at('09:00');
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
