<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InspectionRequest;
use App\Models\User;
use App\Mail\WeeklyReportMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendWeeklyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspections:send-weekly-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a weekly inspection progress report to administrators.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for inspection reports to send...');

        // レポート対象となる、先週の月曜日の日付を計算
        $reportWeekStart = Carbon::today()->subWeek()->startOfWeek(Carbon::MONDAY);

        // 先週の点検依頼を取得
        $inspectionRequest = InspectionRequest::with('records')
            ->where('target_week_start', $reportWeekStart->format('Y-m-d'))
            ->first();

        if (!$inspectionRequest) {
            $this->info('No inspection request found for last week. Nothing to send.');
            return 0; // 正常終了
        }

        // ダッシュボードと同様のロジックで進捗率を計算
        $allRecords = $inspectionRequest->records;
        $total = $allRecords->count();
        $completed = $allRecords->where('status', '点検済み')->count();
        $progress_rate = ($total > 0) ? round(($completed / $total) * 100) : 0;

        $stats = [
            'total' => $total,
            'completed' => $completed,
            'progress_rate' => $progress_rate,
            'results' => [
                '正常' => $allRecords->where('result', '正常')->count(),
                '要確認' => $allRecords->where('result', '要確認')->count(),
                '異常' => $allRecords->where('result', '異常')->count(),
            ],
        ];

        // 送信先となる管理者ユーザーをすべて取得
        $admins = User::where('is_admin', true)->get();

        if ($admins->isEmpty()) {
            $this->warn('No administrators found. Report was not sent.');
            return 1; // 警告終了
        }

        $this->info("Sending report for week of {$reportWeekStart->format('Y-m-d')} to {$admins->count()} admin(s).");

        // 各管理者にメールを送信
        foreach ($admins as $admin) {
            if ($admin->email) {
                Mail::to($admin->email)->send(new WeeklyReportMail($inspectionRequest, $stats));
                $this->line(" - Sent to: {$admin->email}");
            }
        }

        $this->info('Weekly reports sent successfully.');
        return 0;
    }
}
